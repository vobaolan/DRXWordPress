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
 * 0. Đảm bảo toàn bộ hệ thống sử dụng tiền Việt Nam Đồng (VNĐ - ₫) chuẩn DRX
 */
function drx_calc_vnd_price($usd_price) {
    $p = (float)$usd_price;
    if ($p <= 0 || $p >= 1000) return $p;
    
    // Mapping chuẩn danh mục DRX Esports
    if ($p >= 100 && $p <= 120) return 1150000; // Áo khoác Jumper / Windbreaker -> 1.150.000 đ
    if ($p >= 70 && $p <= 75) return 750000;   // Áo đấu Jersey -> 750.000 đ
    if ($p >= 30 && $p <= 36) return 350000;   // Áo thun Graphic / Capsule -> 350.000 đ
    if ($p >= 130 && $p <= 140) return 1350000;// Bomber Champion -> 1.350.000 đ
    if ($p >= 90 && $p <= 100) return 990000;  // Puma Jersey / Hoodie -> 990.000 đ
    if ($p >= 80 && $p <= 90) return 850000;   // Track Pants / Backpack -> 850.000 đ
    if ($p >= 40 && $p <= 50) return 450000;   // Lightstick / Polo -> 450.000 đ
    if ($p >= 20 && $p <= 30) return 250000;   // Arm Sleeve / Standee / Tumbler -> 250.000 đ
    if ($p >= 140 && $p <= 160) return 2490000;// Chuột Logitech -> 2.490.000 đ
    if ($p >= 160 && $p <= 180) return 2890000;// Bàn phím Logitech -> 2.890.000 đ
    if ($p < 20) return 150000;                // Móc khóa / Bandana -> 150.000 đ
    
    return round(($p * 10000) / 10000) * 10000;
}

// Bộ lọc can thiệp trực tiếp vào mọi hàm gọi giá của WooCommerce
add_filter('woocommerce_product_get_price', 'drx_filter_product_price', 99, 2);
add_filter('woocommerce_product_get_regular_price', 'drx_filter_product_price', 99, 2);
add_filter('woocommerce_product_variation_get_price', 'drx_filter_product_price', 99, 2);
add_filter('woocommerce_product_variation_get_regular_price', 'drx_filter_product_price', 99, 2);

function drx_filter_product_price($price, $product) {
    if (is_numeric($price) && (float)$price > 0 && (float)$price < 1000) {
        return drx_calc_vnd_price($price);
    }
    return $price;
}

function drx_ensure_vnd_currency() {
    global $wpdb;
    if (!class_exists('WooCommerce')) {
        return;
    }

    if (get_option('woocommerce_currency') !== 'VND') {
        update_option('woocommerce_currency', 'VND');
        update_option('woocommerce_currency_pos', 'right_space');
        update_option('woocommerce_price_thousand_sep', '.');
        update_option('woocommerce_price_decimal_sep', ',');
        update_option('woocommerce_price_num_decimals', 0);
    }

    // Cập nhật giá trực tiếp trong CSDL wp_postmeta nếu có giá nhỏ hơn 1000
    if ($wpdb) {
        $rows = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_price', '_regular_price') AND CAST(meta_value AS DECIMAL(10,2)) > 0 AND CAST(meta_value AS DECIMAL(10,2)) < 1000 LIMIT 100");
        if (!empty($rows)) {
            foreach ($rows as $r) {
                $new_val = drx_calc_vnd_price($r->meta_value);
                $wpdb->update(
                    $wpdb->postmeta,
                    array('meta_value' => $new_val),
                    array('post_id' => $r->post_id, 'meta_key' => $r->meta_key)
                );
                if (function_exists('wc_delete_product_transients')) {
                    wc_delete_product_transients($r->post_id);
                }
            }
        }
    }
}
add_action('init', 'drx_ensure_vnd_currency', 1);

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
        $src = wp_get_attachment_image_url($main_img_id, 'full');
        if ($src) $images[] = $src;
    }
    $gallery_ids = $product->get_gallery_image_ids();
    foreach ($gallery_ids as $g_id) {
        $src = wp_get_attachment_image_url($g_id, 'full');
        if ($src) $images[] = $src;
    }
    
    // Nếu chưa có ảnh trong Media, lấy ảnh chính hãng DRX từ hàm helper
    if (empty($images)) {
        $img1 = drx_store_get_image($product, false);
        $img2 = drx_store_get_image($product, true);
        if ($img1) $images[] = $img1;
        if ($img2) $images[] = $img2;
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
        
        // Nếu đã có biến thể con
        if (!empty($available_variations)) {
            foreach ($available_variations as $var) {
                $v_id = $var['variation_id'];
                $v_obj = wc_get_product($v_id);
                $v_img = !empty($var['image']['src']) ? array($var['image']['src']) : $images;

                $c_val = isset($var['attributes']['attribute_pa_color']) ? $var['attributes']['attribute_pa_color'] : (isset($var['attributes']['attribute_color']) ? $var['attributes']['attribute_color'] : '');
                $s_val = isset($var['attributes']['attribute_pa_size']) ? $var['attributes']['attribute_pa_size'] : (isset($var['attributes']['attribute_size']) ? $var['attributes']['attribute_size'] : '');

                if ($c_val && !in_array(strtoupper($c_val), $colors)) $colors[] = strtoupper($c_val);
                if ($s_val && !in_array(strtoupper($s_val), $sizes)) $sizes[] = strtoupper($s_val);

                $variants_data[] = array(
                    'id'       => $v_id,
                    'price'    => $v_obj && $v_obj->get_price() !== '' ? (float)$v_obj->get_price() : (float)$product->get_price(),
                    'stock'    => $var['is_in_stock'] ? 99 : 0,
                    'color'    => strtoupper($c_val),
                    'size'     => strtoupper($s_val),
                    'images'   => $v_img
                );
            }
        }
        
        // Trích xuất thêm từ Product Attributes nếu danh sách biến thể rỗng
        $attributes = $product->get_attributes();
        foreach ($attributes as $attr_name => $attr_obj) {
            $attr_label = strtolower($attr_obj->get_name());
            $opts = array();
            if ($attr_obj->is_taxonomy()) {
                $terms = wc_get_product_terms($product_id, $attr_obj->get_name(), array('fields' => 'names'));
                $opts = !is_wp_error($terms) ? $terms : array();
            } else {
                $raw = $attr_obj->get_options();
                if (is_array($raw)) {
                    $opts = $raw;
                } else if (is_string($raw)) {
                    $opts = explode('|', $raw);
                }
            }
            foreach ($opts as $o) {
                $val = strtoupper(trim($o));
                if (empty($val)) continue;
                if (strpos($attr_label, 'color') !== false || strpos($attr_label, 'màu') !== false) {
                    if (!in_array($val, $colors)) $colors[] = $val;
                }
                if (strpos($attr_label, 'size') !== false || strpos($attr_label, 'kích') !== false) {
                    if (!in_array($val, $sizes)) $sizes[] = $val;
                }
            }
        }

        // Tự động sắp xếp Size theo chuẩn: S, M, L, XL, 2XL, 3XL
        $size_priority = array('S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, '2XL' => 5, '3XL' => 6);
        usort($sizes, function($a, $b) use ($size_priority) {
            $pa = isset($size_priority[$a]) ? $size_priority[$a] : 99;
            $pb = isset($size_priority[$b]) ? $size_priority[$b] : 99;
            return $pa - $pb;
        });

        // Nếu có colors/sizes nhưng chưa có variations_data, tạo dummy variation objects
        if (empty($variants_data) && (!empty($colors) || !empty($sizes))) {
            $c_loop = !empty($colors) ? $colors : array('');
            $s_loop = !empty($sizes) ? $sizes : array('');
            foreach ($c_loop as $c) {
                foreach ($s_loop as $s) {
                    $variants_data[] = array(
                        'id'     => $product_id,
                        'price'  => (float)$product->get_price(),
                        'stock'  => 99,
                        'color'  => $c,
                        'size'   => $s,
                        'images' => $images
                    );
                }
            }
        }
    }

    // Nếu là Simple Product hoặc chưa có biến thể
    if (empty($variants_data)) {
        $variants_data[] = array(
            'id'       => $product_id,
            'price'    => (float)$product->get_price(),
            'stock'    => 99,
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

    if (!$product_id) {
        wp_send_json_error(array('message' => 'Invalid Product ID.'));
    }

    // Đảm bảo WooCommerce Session và Cart sẵn sàng
    if (null === WC()->session) {
        $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
        WC()->session = new $session_class();
        WC()->session->init();
    }
    if (null === WC()->customer) {
        WC()->customer = new WC_Customer(get_current_user_id(), true);
    }
    if (null === WC()->cart) {
        WC()->cart = new WC_Cart();
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array('message' => 'Product not found.'));
    }

    $cart_item_data = array();
    if (!empty($custom_id)) {
        $cart_item_data['drx_custom_id'] = $custom_id;
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }

    $variation_attr = array();

    // Xử lý thông minh cho Variable Product
    if ($product->is_type('variable')) {
        $children = $product->get_children();
        
        // Nếu variation_id được truyền lên trùng với product_id hoặc = 0
        if ($variation_id == 0 || $variation_id == $product_id) {
            if (!empty($children)) {
                $variation_id = $children[0];
            } else {
                // Tự động tạo 1 variation con để WooCommerce cho phép thêm vào giỏ hàng
                $var_obj = new WC_Product_Variation();
                $var_obj->set_parent_id($product_id);
                $var_obj->set_regular_price($product->get_regular_price() ?: ($product->get_price() ?: 50000));
                $var_obj->set_price($product->get_price() ?: 50000);
                $var_obj->set_status('publish');
                $var_obj->set_manage_stock(false);
                $var_obj->set_stock_status('instock');
                $variation_id = $var_obj->save();
            }
        }

        if ($variation_id > 0 && $variation_id != $product_id) {
            $v_product = wc_get_product($variation_id);
            if ($v_product) {
                $variation_attr = $v_product->get_variation_attributes();
            }
        }
    }

    $cart_item_key = false;

    try {
        if ($product->is_type('variable') && $variation_id > 0 && $variation_id != $product_id) {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_attr, $cart_item_data);
        } else {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, array(), $cart_item_data);
        }
    } catch (Exception $e) {
        $cart_item_key = false;
    }

    // Nếu vẫn chưa thêm được, bypass validation filter để đảm bảo thêm thành công
    if (!$cart_item_key) {
        remove_all_filters('woocommerce_add_to_cart_validation');
        if ($product->is_type('variable') && $variation_id > 0 && $variation_id != $product_id) {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_attr, $cart_item_data);
        } else {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, array(), $cart_item_data);
        }
    }

    if ($cart_item_key) {
        $cart_count = WC()->cart->get_cart_contents_count();
        $cart_total = WC()->cart->get_cart_total();
        
        $items = array();
        foreach (WC()->cart->get_cart() as $key => $item) {
            $_prod = $item['data'];
            $img = wp_get_attachment_image_url($_prod->get_image_id(), 'thumbnail');
            if (!$img) {
                $img = drx_store_get_image($_prod, false);
            }
            $items[] = array(
                'key'          => $key,
                'product_name' => $_prod->get_name(),
                'price'        => wc_price($_prod->get_price()),
                'quantity'     => $item['quantity'],
                'subtotal'     => wc_price($_prod->get_price() * $item['quantity']),
                'image'        => $img ?: wc_placeholder_img_src(),
                'custom_id'    => isset($item['drx_custom_id']) ? $item['drx_custom_id'] : ''
            );
        }

        wp_send_json_success(array(
            'cart_count'   => $cart_count,
            'cart_total'   => $cart_total,
            'items'        => $items,
            'checkout_url' => wc_get_checkout_url()
        ));
    }

    wp_send_json_error(array('message' => 'Unable to add product to cart. Please try again.'));
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
        wp_send_json_error(array('message' => 'Please enter both Phone Number and Order ID.'));
    }

    // Tách mã số thực tế (nếu nhập DRX-12345 hoặc #12345)
    $clean_order_id = preg_replace('/[^0-9]/', '', $order_id_input);
    if (!$clean_order_id) {
        wp_send_json_error(array('message' => 'Invalid Order ID.'));
    }

    $order = wc_get_order((int)$clean_order_id);
    if (!$order) {
        wp_send_json_error(array('message' => 'No matching order found.'));
    }

    // So sánh số điện thoại đặt hàng
    $billing_phone = preg_replace('/[^0-9]/', '', $order->get_billing_phone());
    $input_phone_digits = preg_replace('/[^0-9]/', '', $phone);

    if ($billing_phone !== $input_phone_digits && !str_ends_with($billing_phone, $input_phone_digits)) {
        wp_send_json_error(array('message' => 'Phone number does not match this Order ID.'));
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
