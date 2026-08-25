<?php
/**
 * Tùy biến WooCommerce cho DRX Official Store
 * 
 * Tính năng:
 * - Lưu trường "Tên / ID thêu áo" (Custom ID) vào giỏ hàng & đơn hàng
 * - AJAX Quick View Modal lấy thông tin chi tiết sản phẩm
 * - AJAX Thêm vào giỏ hàng kèm Custom ID
 * - AJAX Tra cứu đơn hàng (Track Order) theo Số điện thoại & Mã đơn
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * 1. Thêm trường Custom ID vào Cart Item Data
 */
function drx_add_custom_id_to_cart_item_data($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['drx_custom_id']) && !empty(trim($_POST['drx_custom_id']))) {
        $cart_item_data['drx_custom_id'] = sanitize_text_field(trim($_POST['drx_custom_id']));
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'drx_add_custom_id_to_cart_item_data', 10, 3);

/**
 * 2. Hiển thị Custom ID trong Giỏ hàng & Trang Checkout
 */
function drx_display_custom_id_in_cart($item_data, $cart_item) {
    if (isset($cart_item['drx_custom_id']) && !empty($cart_item['drx_custom_id'])) {
        $item_data[] = array(
            'key'     => __('Tên may trên áo (Custom ID)', 'hello-elementor-child'),
            'value'   => esc_html($cart_item['drx_custom_id']),
            'display' => '<span class="drx-badge-custom-id">ID: ' . esc_html($cart_item['drx_custom_id']) . '</span>'
        );
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'drx_display_custom_id_in_cart', 10, 2);

/**
 * 3. Lưu Custom ID vào Meta của Order Item khi đặt hàng thành công
 */
function drx_save_custom_id_to_order_items($item, $cart_item_key, $values, $order) {
    if (isset($values['drx_custom_id']) && !empty($values['drx_custom_id'])) {
        $item->add_meta_data(__('Tên may trên áo', 'hello-elementor-child'), $values['drx_custom_id'], true);
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'drx_save_custom_id_to_order_items', 10, 4);

/**
 * 4. AJAX Endpoint: Lấy dữ liệu chi tiết sản phẩm cho Quick View Modal
 */
function drx_ajax_get_product_details() {
    check_ajax_referer('drx_store_nonce', 'security');

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    if (!$product_id) {
        wp_send_json_error(array('message' => 'Invalid Product ID'));
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array('message' => 'Product not found'));
    }

    $images = array();
    $main_img_id = $product->get_image_id();
    if ($main_img_id) {
        $images[] = wp_get_attachment_image_url($main_img_id, 'full');
    }
    $gallery_ids = $product->get_gallery_image_ids();
    foreach ($gallery_ids as $g_id) {
        $images[] = wp_get_attachment_image_url($g_id, 'full');
    }
    if (empty($images)) {
        $images[] = wc_placeholder_img_src();
    }

    // Kiểm tra xem sản phẩm có cho phép nhập Custom ID may áo không
    $desc = $product->get_description();
    $short_desc = $product->get_short_description();
    $allow_custom_id = (strpos($desc, '[ALLOW_CUSTOM_ID]') !== false) || (strpos($short_desc, '[ALLOW_CUSTOM_ID]') !== false);

    // Lấy danh sách biến thể nếu là Variable Product
    $variants_data = array();
    $colors = array();
    $sizes = array();

    if ($product->is_type('variable')) {
        $available_variations = $product->get_available_variations();
        foreach ($available_variations as $var) {
            $v_id = $var['variation_id'];
            $v_obj = wc_get_product($v_id);
            $v_img = $var['image']['src'] ? array($var['image']['src']) : $images;

            $c_val = isset($var['attributes']['attribute_pa_color']) ? $var['attributes']['attribute_pa_color'] : (isset($var['attributes']['attribute_color']) ? $var['attributes']['attribute_color'] : '');
            $s_val = isset($var['attributes']['attribute_pa_size']) ? $var['attributes']['attribute_pa_size'] : (isset($var['attributes']['attribute_size']) ? $var['attributes']['attribute_size'] : '');

            if ($c_val && !in_array($c_val, $colors)) $colors[] = strtoupper($c_val);
            if ($s_val && !in_array($s_val, $sizes)) $sizes[] = strtoupper($s_val);

            $variants_data[] = array(
                'id'       => $v_id,
                'price'    => $v_obj ? (float)$v_obj->get_price() : (float)$var['display_price'],
                'stock'    => $var['is_in_stock'] ? 99 : 0,
                'color'    => strtoupper($c_val),
                'size'     => strtoupper($s_val),
                'images'   => $v_img
            );
        }
    } else {
        $variants_data[] = array(
            'id'       => $product_id,
            'price'    => (float)$product->get_price(),
            'stock'    => $product->is_in_stock() ? 99 : 0,
            'color'    => '',
            'size'     => '',
            'images'   => $images
        );
    }

    $response = array(
        'id'               => $product_id,
        'name'             => $product->get_name(),
        'price'            => (float)$product->get_price(),
        'regular_price'    => (float)$product->get_regular_price(),
        'price_html'       => $product->get_price_html(),
        'description'      => $desc,
        'allow_custom_id'  => $allow_custom_id,
        'images'           => $images,
        'colors'           => $colors,
        'sizes'            => $sizes,
        'variants'         => $variants_data,
    );

    wp_send_json_success($response);
}
add_action('wp_ajax_drx_get_product_details', 'drx_ajax_get_product_details');
add_action('wp_ajax_nopriv_drx_get_product_details', 'drx_ajax_get_product_details');

/**
 * 5. AJAX Endpoint: Thêm vào giỏ hàng trực tiếp từ Quick View Modal
 */
function drx_ajax_add_to_cart() {
    check_ajax_referer('drx_store_nonce', 'security');

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
    $custom_id = isset($_POST['custom_id']) ? sanitize_text_field(trim($_POST['custom_id'])) : '';

    $cart_item_data = array();
    if (!empty($custom_id)) {
        $cart_item_data['drx_custom_id'] = $custom_id;
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }

    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);

    if ($passed_validation) {
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, array(), $cart_item_data);
        if ($cart_item_key) {
            $cart_count = WC()->cart->get_cart_contents_count();
            $cart_total = WC()->cart->get_cart_total();
            
            // Lấy danh sách items giỏ hàng để cập nhật Cart Drawer
            $items = array();
            foreach (WC()->cart->get_cart() as $key => $item) {
                $_prod = $item['data'];
                $items[] = array(
                    'key'          => $key,
                    'product_name' => $_prod->get_name(),
                    'price'        => wc_price($_prod->get_price()),
                    'quantity'     => $item['quantity'],
                    'subtotal'     => wc_price($_prod->get_price() * $item['quantity']),
                    'image'        => wp_get_attachment_image_url($_prod->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src(),
                    'custom_id'    => isset($item['drx_custom_id']) ? $item['drx_custom_id'] : ''
                );
            }

            wp_send_json_success(array(
                'cart_count' => $cart_count,
                'cart_total' => $cart_total,
                'items'      => $items,
                'checkout_url' => wc_get_checkout_url()
            ));
        }
    }

    wp_send_json_error(array('message' => 'Không thể thêm sản phẩm vào giỏ hàng.'));
}
add_action('wp_ajax_drx_add_to_cart', 'drx_ajax_add_to_cart');
add_action('wp_ajax_nopriv_drx_add_to_cart', 'drx_ajax_add_to_cart');

/**
 * 6. AJAX Endpoint: Tra cứu đơn hàng (Track Order)
 */
function drx_ajax_track_order() {
    check_ajax_referer('drx_store_nonce', 'security');

    $phone = isset($_POST['phone']) ? sanitize_text_field(trim($_POST['phone'])) : '';
    $order_id_input = isset($_POST['order_id']) ? sanitize_text_field(trim($_POST['order_id'])) : '';

    if (empty($phone) || empty($order_id_input)) {
        wp_send_json_error(array('message' => 'Vui lòng nhập đầy đủ Số điện thoại và Mã đơn hàng.'));
    }

    // Tách mã số thực tế (nếu nhập DRX-12345 hoặc #12345)
    $clean_order_id = preg_replace('/[^0-9]/', '', $order_id_input);
    if (!$clean_order_id) {
        wp_send_json_error(array('message' => 'Mã đơn hàng không hợp lệ.'));
    }

    $order = wc_get_order((int)$clean_order_id);
    if (!$order) {
        wp_send_json_error(array('message' => 'Không tìm thấy đơn hàng với mã số này trong hệ thống.'));
    }

    // So sánh số điện thoại đặt hàng
    $billing_phone = preg_replace('/[^0-9]/', '', $order->get_billing_phone());
    $input_phone_digits = preg_replace('/[^0-9]/', '', $phone);

    if ($billing_phone !== $input_phone_digits && !str_ends_with($billing_phone, $input_phone_digits)) {
        wp_send_json_error(array('message' => 'Số điện thoại không khớp với thông tin đơn hàng này.'));
    }

    // Thu thập danh sách sản phẩm trong đơn hàng
    $items = array();
    foreach ($order->get_items() as $item_id => $item) {
        $custom_id_meta = $item->get_meta(__('Tên may trên áo', 'hello-elementor-child'));
        $items[] = array(
            'product_name' => $item->get_name(),
            'qty'          => $item->get_quantity(),
            'total'        => wc_price($item->get_total()),
            'custom_id'    => $custom_id_meta ?: ''
        );
    }

    $status_label = wc_get_order_status_name($order->get_status());
    $status_slug = $order->get_status();

    wp_send_json_success(array(
        'order_id'    => 'DRX-' . $order->get_id(),
        'date'        => $order->get_date_created()->date('d/m/Y H:i'),
        'status'      => strtoupper($status_label),
        'status_slug' => $status_slug,
        'total'       => wc_price($order->get_total()),
        'items'       => $items
    ));
}
add_action('wp_ajax_drx_track_order', 'drx_ajax_track_order');
add_action('wp_ajax_nopriv_drx_track_order', 'drx_ajax_track_order');
