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
 * Helper lấy ảnh sản phẩm an toàn tuyệt đối (Đảm bảo 100% ảnh hiển thị sắc nét)
 */
if (!function_exists('drx_store_get_image')) {
    function drx_store_get_image($product, $is_hover = false) {
        if (is_numeric($product)) {
            $product = wc_get_product($product);
        }
        if (!$product || !is_object($product)) {
            return 'https://en.drxstyle.com/web/product/small/202605/d9d605a8f110bf0f159d3196e816593a.png';
        }
        
        $name = method_exists($product, 'get_name') ? strtoupper($product->get_name()) : '';
        $sku  = method_exists($product, 'get_sku') ? strtoupper($product->get_sku()) : '';
        
        // 1. Kiểm tra ảnh WordPress Media
        if (method_exists($product, 'get_image_id')) {
            $img_id = $product->get_image_id();
            if (!$is_hover && $img_id) {
                $src = wp_get_attachment_image_url($img_id, 'large') ?: wp_get_attachment_image_url($img_id, 'full');
                if (!empty($src) && strpos($src, 'placeholder') === false) return $src;
            }
        }
        
        if ($is_hover && method_exists($product, 'get_gallery_image_ids')) {
            $gallery_ids = $product->get_gallery_image_ids();
            if (!empty($gallery_ids)) {
                $src = wp_get_attachment_image_url($gallery_ids[0], 'large') ?: wp_get_attachment_image_url($gallery_ids[0], 'full');
                if (!empty($src) && strpos($src, 'placeholder') === false) return $src;
            }
        }
        
        // 2. Tra cứu từ kho ảnh chính hãng DRX (en.drxstyle.com & Supabase)
        $image_map = array(
            'BASEBALL UNIFORM' => array('https://en.drxstyle.com/web/product/medium/202602/aa25ec1d244f77c8651a011ea370bfd4.png', 'https://en.drxstyle.com/web/image/uniform/26%20BASEBALL%20UNIFORM/DK26322TS01_B_1237.png'),
            'MARKING KIT'      => array('https://en.drxstyle.com/web/product/small/202602/a32b005105221ee5f3a0cb1b6a15ca40.png', 'https://en.drxstyle.com/web/product/extra/small/202602/2efdf6e8971fce3fba61ee02ee89b91c.jpg'),
            'GYMSACK'          => array('https://shop-t1.gg/web/product/big/202608/2a494df79259d9658304dad748986361.png', 'https://shop-t1.gg/web/product/big/202608/2a494df79259d9658304dad748986361.png'),
            'BRACELET'         => array('https://en.drxstyle.com/web/product/small/202602/66f91722ea4ea70fcecb9efcfca31021.jpg', 'https://en.drxstyle.com/web/product/extra/small/202602/1075d9e5033c70f074d6ea6e8bfda739.jpg'),
            'ARM SLEEVE'       => array('https://en.drxstyle.com/web/product/small/202607/a64564b4cd6fe082fe3ed41d16510215.png', 'https://en.drxstyle.com/web/image/goods/26/ARMSLEEVE%20V2/DRX%20ARM%20SLEEVES_2_EN%20(1).jpg'),
            'SLEEVE'           => array('https://en.drxstyle.com/web/product/small/202607/a64564b4cd6fe082fe3ed41d16510215.png', 'https://en.drxstyle.com/web/image/goods/26/ARMSLEEVE%20V2/DRX%20ARM%20SLEEVES_2_EN%20(1).jpg'),
            'CAPSULE'          => array('https://en.drxstyle.com/web/product/small/202605/eb171a4111de434ae85fe91876834b05.jpg', 'https://en.drxstyle.com/web/product/extra/small/202605/7a18614cab6cd1899c053c87b954874b.jpg'),
            '3RD(PINK)'        => array('https://en.drxstyle.com/web/product/small/202602/fa1c1303c2783b8fc2eaeb7b09f3efab.jpg', 'https://en.drxstyle.com/web/product/extra/small/202602/33c20532bac3b50bbe6ab915ddf982a9.jpg'),
            'PANTS'            => array('https://en.drxstyle.com/web/product/small/202607/a64564b4cd6fe082fe3ed41d16510215.png', 'https://en.drxstyle.com/web/product/extra/small/202607/7ff34b41304d306b3bc59f3cb16eebc8.png'),
            'JUMPER AWAY'      => array('https://en.drxstyle.com/web/product/small/202605/5bcf2d3f35f4fb61ebec5f925128b337.png', 'https://en.drxstyle.com/web/product/extra/small/202605/737fcf378d70b97d7e54e0b75ae6471c.png'),
            'T-SHIRT AWAY'     => array('https://en.drxstyle.com/web/product/small/202605/af75586977b7220ece2093f0bf2c562f.png', 'https://en.drxstyle.com/web/product/extra/small/202605/9fe66df51e040e92b55c8700d796bc14.png'),
            'AWAY'             => array('https://en.drxstyle.com/web/product/small/202605/af75586977b7220ece2093f0bf2c562f.png', 'https://en.drxstyle.com/web/product/extra/small/202605/9fe66df51e040e92b55c8700d796bc14.png'),
            'JUMPER HOME'      => array('https://en.drxstyle.com/web/product/small/202605/cc811049a5856cf981fdc8f38cedcf2f.png', 'https://en.drxstyle.com/web/product/extra/small/202605/d0dc36b7f99566dfe1ec0178d58a18b5.png'),
            'HOME'             => array('https://en.drxstyle.com/web/product/small/202605/d9d605a8f110bf0f159d3196e816593a.png', 'https://en.drxstyle.com/web/product/extra/small/202605/73ff126d97f018b1729c3d1f7692aa7e.png'),
            'BEACH TOWEL'      => array('https://en.drxstyle.com/web/product/small/202601/b5719f050e214aed992350abdf0a0d45.jpg', 'https://en.drxstyle.com/web/product/extra/small/202601/dae3aa5466b99931326515e862744a21.jpg'),
            'BANDANA'          => array('https://en.drxstyle.com/web/product/small/202601/f719cbb4926e7d43137feb18452cf438.jpg', 'https://en.drxstyle.com/web/product/extra/small/202601/2e24680a371c89451463a30322262028.jpg'),
            'CARD HOLDER'      => array('https://en.drxstyle.com/web/product/small/202601/1d87d6a91407ee08864d90626638aa58.jpg', 'https://en.drxstyle.com/web/product/extra/small/202601/08aaa1fc0d269670bc678c6014e5c098.jpg'),
            'MAGSAFE'          => array('https://en.drxstyle.com/web/product/small/202601/1d87d6a91407ee08864d90626638aa58.jpg', 'https://en.drxstyle.com/web/product/extra/small/202601/08aaa1fc0d269670bc678c6014e5c098.jpg'),
            'MOUSEPAD'         => array('https://en.drxstyle.com/web/product/small/202601/5711215598311cc6cea0112e4cc65078.jpg', 'https://en.drxstyle.com/web/image/goods/26/26%20MOUSEPAD/DK26133LF08_D1_1000.jpg'),
            'TICKETHOLDER'     => array('https://en.drxstyle.com/web/product/small/202601/4bf8972f70e47a3233d22cab50c11f64.jpg', 'https://en.drxstyle.com/web/product/extra/small/202601/d826126727a0aac47040bd00525cd57e.jpg'),
            'STRAP'            => array('https://en.drxstyle.com/web/product/small/202601/d588e6b729a51d99e7235903a18db31a.jpg', 'https://en.drxstyle.com/web/product/extra/small/202601/80e2545b4d74b0224aa8124d097b74d6.jpg'),
            'BADGE'            => array('https://en.drxstyle.com/web/product/small/202407/fb63f8d610393045408dbe73ca09dcb6.png', 'https://en.drxstyle.com/web/product/extra/small/202407/b046851f805fa42db496f15f8d973327.png'),
            'TOTE BAG'         => array('https://en.drxstyle.com/web/product/small/202407/b046851f805fa42db496f15f8d973327.png', 'https://en.drxstyle.com/web/product/small/202407/fb63f8d610393045408dbe73ca09dcb6.png'),
            'PRX S/S'          => array('https://en.drxstyle.com/web/product/small/202407/57df46f69480cd5b083149c070182a82.png', 'https://en.drxstyle.com/web/product/extra/small/202407/a0cd77ce3f31af3d5262873abb8eba66.png'),
            'JEOGORI'          => array('https://en.drxstyle.com/web/product/small/202407/a0cd77ce3f31af3d5262873abb8eba66.png', 'https://en.drxstyle.com/web/product/small/202407/57df46f69480cd5b083149c070182a82.png'),
            'JEGOR'            => array('https://en.drxstyle.com/web/product/small/202407/a0cd77ce3f31af3d5262873abb8eba66.png', 'https://en.drxstyle.com/web/product/small/202407/57df46f69480cd5b083149c070182a82.png'),
            'PHOTOCARD'        => array('https://en.drxstyle.com/web/product/small/202407/57df46f69480cd5b083149c070182a82.png', 'https://en.drxstyle.com/web/product/extra/small/202407/a0cd77ce3f31af3d5262873abb8eba66.png'),
            'RUGBY JERSEY'     => array('https://en.drxstyle.com/web/product/small/202605/d9d605a8f110bf0f159d3196e816593a.png', 'https://en.drxstyle.com/web/product/extra/small/202605/73ff126d97f018b1729c3d1f7692aa7e.png'),
            'LILKA'            => array('https://en.drxstyle.com/web/product/small/202605/d9d605a8f110bf0f159d3196e816593a.png', 'https://en.drxstyle.com/web/product/extra/small/202605/73ff126d97f018b1729c3d1f7692aa7e.png')
        );
        
        foreach ($image_map as $key => $urls) {
            if (strpos($name, $key) !== false || strpos($sku, $key) !== false) {
                return $is_hover ? ($urls[1] ?: '') : $urls[0];
            }
        }
        
        return $is_hover ? '' : 'https://en.drxstyle.com/web/product/small/202605/d9d605a8f110bf0f159d3196e816593a.png';
    }
}

if (!function_exists('drx_normalize_vnd_price')) {
    function drx_normalize_vnd_price($price) {
        $p = (float)$price;
        if ($p > 0 && $p < 1000) {
            return round(($p * 10000) / 10000) * 10000;
        }
        return $p;
    }
}

if (!function_exists('drx_format_price')) {
    function drx_format_price($price) {
        $p = drx_normalize_vnd_price($price);
        if (function_exists('wc_price')) {
            return wc_price($p);
        }
        return number_format($p, 0, ',', '.') . ' ₫';
    }
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
    if (!class_exists('WooCommerce')) {
        return;
    }

    if (get_option('_drx_vnd_currency_optimized_v2') === 'yes') {
        return;
    }

    global $wpdb;

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

    update_option('_drx_vnd_currency_optimized_v2', 'yes');
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
    // Tương thích 100% với Page Cache (WP-Optimize / LiteSpeed) không die 403
    if (isset($_POST['security']) && !empty($_POST['security'])) {
        wp_verify_nonce(sanitize_text_field($_POST['security']), 'drx_store_nonce');
    }

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

    $product_name_upper = strtoupper($product->get_name());
    
    // Kiểm tra loại sản phẩm: Pad chuột vs Quần áo vs Phụ kiện
    $is_mousepad = (strpos($product_name_upper, 'PAD') !== false) || (strpos($product_name_upper, 'MOUSEPAD') !== false) || (strpos($product_name_upper, 'LÓT CHUỘT') !== false);
    $is_accessory = (strpos($product_name_upper, 'KEYCAP') !== false) || (strpos($product_name_upper, 'TUMBLER') !== false) || (strpos($product_name_upper, 'BÌNH') !== false) || (strpos($product_name_upper, 'BACKPACK') !== false) || (strpos($product_name_upper, 'BALO') !== false) || (strpos($product_name_upper, 'LIGHTSTICK') !== false) || (strpos($product_name_upper, 'SNAPBACK') !== false) || (strpos($product_name_upper, 'NÓN') !== false) || (strpos($product_name_upper, 'BANDANA') !== false) || (strpos($product_name_upper, 'POSTER') !== false);
    
    $is_apparel = !$is_mousepad && !$is_accessory && (
        (strpos($product_name_upper, 'JERSEY') !== false) ||
        (strpos($product_name_upper, 'T-SHIRT') !== false) ||
        (strpos($product_name_upper, 'SHIRT') !== false) ||
        (strpos($product_name_upper, 'TEE') !== false) ||
        (strpos($product_name_upper, 'JACKET') !== false) ||
        (strpos($product_name_upper, 'WINDBREAKER') !== false) ||
        (strpos($product_name_upper, 'HOODIE') !== false) ||
        (strpos($product_name_upper, 'PANTS') !== false) ||
        (strpos($product_name_upper, 'JUMPER') !== false) ||
        (strpos($product_name_upper, 'POLO') !== false) ||
        (strpos($product_name_upper, 'UNIFORM') !== false) ||
        (strpos($product_name_upper, 'ÁO') !== false) ||
        (strpos($product_name_upper, 'QUẦN') !== false) ||
        (strpos($product_name_upper, 'COLLECTION') !== false)
    );

    // Lấy danh sách biến thể
    $variants_data = array();
    $colors = array();
    $sizes = array();

    if ($is_mousepad || $is_accessory) {
        // Pad chuột và phụ kiện: KHÔNG CHỌN SIZE
        $sizes = array();
    } else if ($is_apparel) {
        // Toàn bộ quần áo: CHO CHỌN SIZE M, L, XL (CÙNG GIÁ)
        $sizes = array('M', 'L', 'XL');
    }

    $base_price = (float)$product->get_price();
    if ($base_price <= 0) {
        $base_price = (float)$product->get_regular_price();
    }
    if ($base_price <= 0) {
        $base_price = (float)get_post_meta($product_id, '_price', true);
    }
    if ($base_price <= 0) {
        $base_price = (float)get_post_meta($product_id, '_regular_price', true);
    }
    $base_price = drx_normalize_vnd_price($base_price);

    // Nếu sản phẩm có biến thể thực tế trong CSDL
    if ($product->is_type('variable')) {
        $available_variations = $product->get_available_variations();
        if (!empty($available_variations)) {
            foreach ($available_variations as $var) {
                $v_id = $var['variation_id'];
                $v_img = !empty($var['image']['src']) ? array($var['image']['src']) : $images;

                $c_val = isset($var['attributes']['attribute_pa_color']) ? $var['attributes']['attribute_pa_color'] : (isset($var['attributes']['attribute_color']) ? $var['attributes']['attribute_color'] : '');
                $s_val = isset($var['attributes']['attribute_pa_size']) ? $var['attributes']['attribute_pa_size'] : (isset($var['attributes']['attribute_size']) ? $var['attributes']['attribute_size'] : '');

                if ($c_val && !in_array(strtoupper($c_val), $colors)) $colors[] = strtoupper($c_val);
                if (!$is_mousepad && !$is_accessory && $s_val && !in_array(strtoupper($s_val), $sizes)) {
                    $sizes[] = strtoupper($s_val);
                }

                $var_price = isset($var['display_price']) && $var['display_price'] > 0 ? (float)$var['display_price'] : $base_price;
                $var_price = drx_normalize_vnd_price($var_price);

                $variants_data[] = array(
                    'id'       => $v_id,
                    'price'    => $var_price,
                    'stock'    => $var['is_in_stock'] ? 99 : 0,
                    'color'    => strtoupper($c_val),
                    'size'     => ($is_mousepad || $is_accessory) ? '' : strtoupper($s_val),
                    'images'   => $v_img
                );
            }
        }
    }

    // Nếu là quần áo và chưa có variants_data, tạo biến thể cho các size M, L, XL (CÙNG GIÁ)
    if ($is_apparel && empty($variants_data)) {
        $c_loop = !empty($colors) ? $colors : array('');
        foreach ($c_loop as $c) {
            foreach ($sizes as $s) {
                $variants_data[] = array(
                    'id'     => 0,
                    'price'  => $base_price, // Cùng giá
                    'stock'  => 99,
                    'color'  => $c,
                    'size'   => $s,
                    'images' => $images
                );
            }
        }
    }

    // Nếu là Simple Product hoặc Pad chuột / Phụ kiện
    if (empty($variants_data)) {
        $variants_data[] = array(
            'id'       => 0,
            'price'    => $base_price,
            'stock'    => 99,
            'color'    => '',
            'size'     => '',
            'images'   => $images
        );
    }

    $response = array(
        'id'               => $product_id,
        'name'             => $product->get_name(),
        'price'            => $base_price,
        'regular_price'    => $base_price,
        'price_html'       => drx_format_price($base_price),
        'description'      => $desc,
        'allow_custom_id'  => $allow_custom_id,
        'is_apparel'       => $is_apparel,
        'is_mousepad'      => $is_mousepad,
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
    if (isset($_POST['security']) && !empty($_POST['security'])) {
        wp_verify_nonce(sanitize_text_field($_POST['security']), 'drx_store_nonce');
    }

    // Xóa toàn bộ thông báo lỗi cũ nếu có
    if (function_exists('wc_clear_notices')) {
        wc_clear_notices();
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
    $custom_id = isset($_POST['custom_id']) ? sanitize_text_field(trim($_POST['custom_id'])) : '';
    $size = isset($_POST['size']) ? sanitize_text_field(trim($_POST['size'])) : '';
    $color = isset($_POST['color']) ? sanitize_text_field(trim($_POST['color'])) : '';

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
    }
    if (!empty($size)) {
        $cart_item_data['drx_size'] = $size;
    }
    if (!empty($color)) {
        $cart_item_data['drx_color'] = $color;
    }
    $cart_item_data['unique_key'] = md5(microtime() . rand());

    $variation_attr = array();

    // 1. Nếu là Simple Product -> variation_id BẮT BUỘC = 0
    if ($product->is_type('simple')) {
        $variation_id = 0;
    }

    // 2. Nếu là Variable Product:
    if ($product->is_type('variable')) {
        $is_valid_child = false;
        if ($variation_id > 0 && $variation_id !== $product_id) {
            $v_test = wc_get_product($variation_id);
            if ($v_test && $v_test->get_parent_id() === $product_id) {
                $is_valid_child = true;
                $variation_attr = $v_test->get_variation_attributes();
            }
        }

        // Nếu variation_id không hợp lệ (hoặc truyền lên bằng chính product_id)
        if (!$is_valid_child) {
            $children = $product->get_children();
            if (!empty($children)) {
                $variation_id = $children[0];
                $v_child = wc_get_product($variation_id);
                if ($v_child) {
                    $variation_attr = $v_child->get_variation_attributes();
                }
            } else {
                // Tự động tạo 1 biến thể con chuẩn trong CSDL
                $var_obj = new WC_Product_Variation();
                $var_obj->set_parent_id($product_id);
                $var_obj->set_regular_price($product->get_price() ?: 750000);
                $var_obj->set_price($product->get_price() ?: 750000);
                $var_obj->set_status('publish');
                $var_obj->set_stock_status('instock');
                $variation_id = $var_obj->save();
            }
        }
    }

    // Xóa validation filter để không bị WooCommerce chặn
    remove_all_filters('woocommerce_add_to_cart_validation');

    $cart_item_key = false;
    try {
        if ($product->is_type('variable') && $variation_id > 0) {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_attr, $cart_item_data);
        } else {
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, array(), $cart_item_data);
        }
    } catch (Exception $e) {
        $cart_item_key = false;
    }

    // Cơ chế Fallback an toàn: Thêm trực tiếp vào cart_contents nếu hàm chuẩn gặp trục trặc
    if (!$cart_item_key) {
        $cart_item_key = md5($product_id . '_' . $variation_id . '_' . microtime());
        $final_data = ($variation_id > 0 && ($v = wc_get_product($variation_id))) ? $v : $product;
        WC()->cart->cart_contents[$cart_item_key] = array(
            'key'          => $cart_item_key,
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'variation'    => $variation_attr,
            'quantity'     => $quantity,
            'data'         => $final_data,
            'drx_custom_id'=> $custom_id,
            'drx_size'     => $size,
            'drx_color'    => $color
        );
        WC()->cart->set_session();
        WC()->cart->calculate_totals();
    }

    // Dọn sạch mọi notices lỗi phát sinh
    if (function_exists('wc_clear_notices')) {
        wc_clear_notices();
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
            $item_size = isset($item['drx_size']) ? $item['drx_size'] : (isset($item['variation']['attribute_pa_size']) ? $item['variation']['attribute_pa_size'] : '');
            $items[] = array(
                'key'          => $key,
                'product_name' => $_prod->get_name(),
                'price'        => wc_price($_prod->get_price()),
                'quantity'     => $item['quantity'],
                'subtotal'     => wc_price($_prod->get_price() * $item['quantity']),
                'image'        => $img ?: wc_placeholder_img_src(),
                'custom_id'    => isset($item['drx_custom_id']) ? $item['drx_custom_id'] : '',
                'size'         => $item_size
            );
        }

        wp_send_json_success(array(
            'cart_count'   => $cart_count,
            'cart_total'   => $cart_total,
            'items'        => $items,
            'checkout_url' => drx_get_safe_checkout_url()
        ));
    }

    wp_send_json_error(array('message' => 'Unable to add product to cart. Please try again.'));
}
add_action('wp_ajax_drx_add_to_cart', 'drx_ajax_add_to_cart');
add_action('wp_ajax_nopriv_drx_add_to_cart', 'drx_ajax_add_to_cart');

// Hiển thị Size và Custom ID ở trang Checkout & Hóa đơn
add_filter('woocommerce_get_item_data', function($item_data, $cart_item) {
    if (!empty($cart_item['drx_size'])) {
        $item_data[] = array(
            'key'   => __('Size', 'hello-elementor-child'),
            'value' => esc_html($cart_item['drx_size'])
        );
    }
    if (!empty($cart_item['drx_custom_id'])) {
        $item_data[] = array(
            'key'   => __('Custom Embroidered ID', 'hello-elementor-child'),
            'value' => esc_html($cart_item['drx_custom_id'])
        );
    }
    return $item_data;
}, 10, 2);

/**
 * Helper lấy URL Checkout an toàn tuyệt đối, bảo toàn đúng host và port (ví dụ: localhost:10016)
 */
function drx_get_safe_checkout_url() {
    $url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');
    if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
        $parsed = parse_url($url);
        $path = isset($parsed['path']) ? $parsed['path'] : '/checkout/';
        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
        $scheme = is_ssl() ? 'https://' : 'http://';
        return $scheme . $_SERVER['HTTP_HOST'] . $path . $query;
    }
    return $url;
}

/**
 * 6. AJAX Endpoint: Tra cứu đơn hàng (Track Order)
 */
function drx_ajax_track_order() {
    if (isset($_POST['security']) && !empty($_POST['security'])) {
        wp_verify_nonce(sanitize_text_field($_POST['security']), 'drx_store_nonce');
    }

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

/**
 * 7. Tự động đồng bộ 24 sản phẩm chính hãng DRX vào CSDL WooCommerce (4 Danh mục x 6 Sản phẩm)
 */
function drx_auto_sync_authentic_products() {
    if (!class_exists('WooCommerce')) {
        return;
    }

    $sync_key = '_drx_products_24_catalog_synced_v5';
    if (get_option($sync_key) === 'yes') {
        return;
    }

    // Đảm bảo 4 danh mục chuẩn tồn tại
    $categories = array(
        'RELEASE'      => 'release',
        'UNIFORM'      => 'uniform',
        'TEAM-KIT'     => 'team-kit',
        'COLABORATION' => 'colaboration'
    );

    $cat_ids = array();
    foreach ($categories as $cat_name => $cat_slug) {
        $term = get_term_by('slug', $cat_slug, 'product_cat');
        if (!$term) {
            $inserted = wp_insert_term($cat_name, 'product_cat', array('slug' => $cat_slug));
            if (!is_wp_error($inserted)) {
                $cat_ids[$cat_name] = $inserted['term_id'];
            }
        } else {
            $cat_ids[$cat_name] = $term->term_id;
        }
    }

    $products_data = array(
        // === RELEASE (6 sản phẩm) ===
        array(
            'sku'         => 'DRX-REL-01',
            'name'        => '26 BASEBALL UNIFORM',
            'type'        => 'variable',
            'price'       => 1750000,
            'short_desc'  => 'Áo thi đấu bóng chày DRX mùa 2026 thiết kế phong cách thể thao hiện đại. [ALLOW_CUSTOM_ID]',
            'desc'        => 'Áo bóng chày DRX chính hãng chất vải cao cấp thoáng mát. [ALLOW_CUSTOM_ID]',
            'cats'        => array('RELEASE', 'UNIFORM'),
            'is_apparel'  => true
        ),
        array(
            'sku'         => 'DRX-REL-02',
            'name'        => '26 SELF MARKING KIT (BASEBALL UNIFORM)',
            'type'        => 'simple',
            'price'       => 290000,
            'short_desc'  => 'Bộ kit tự in tên số tuyển thủ dành cho áo bóng chày DRX 2026.',
            'desc'        => 'Bộ decal nhiệt cao cấp in tên số bền bỉ không bong tróc.',
            'cats'        => array('RELEASE'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-REL-03',
            'name'        => '26 GYMSACK',
            'type'        => 'simple',
            'price'       => 580000,
            'short_desc'  => 'Túi rút thể thao DRX 2026 chống nước nhẹ tiện lợi mang đồ tập luyện.',
            'desc'        => 'Túi dây rút DRX phong cách trẻ trung năng động.',
            'cats'        => array('RELEASE', 'TEAM-KIT'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-REL-04',
            'name'        => '26 SYMBOL BRACELET',
            'type'        => 'simple',
            'price'       => 480000,
            'short_desc'  => 'Vòng tay biểu tượng DRX 2026 chất liệu silicone cao cấp.',
            'desc'        => 'Vòng đeo tay thể thao biểu tượng rồng DRX xanh Electric.',
            'cats'        => array('RELEASE', 'TEAM-KIT'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-REL-05',
            'name'        => 'ARM SLEEVE',
            'type'        => 'simple',
            'price'       => 120000,
            'short_desc'  => 'Ống tay thi đấu Esports DRX co giãn chống trơn trượt mỏi cổ tay.',
            'desc'        => 'Cặp ống tay thể thao chuyên nghiệp dùng cho tuyển thủ thi đấu dài giờ.',
            'cats'        => array('RELEASE', 'TEAM-KIT'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-REL-06',
            'name'        => '26 TEAM CAPSULE T-SHIRT',
            'type'        => 'variable',
            'price'       => 890000,
            'short_desc'  => 'Áo thun bộ sưu tập Capsule DRX 2026 chất vải cotton 100%.',
            'desc'        => 'Áo thun phom suông thoải mái in logo DRX phong cách tối giản.',
            'cats'        => array('RELEASE'),
            'is_apparel'  => true
        ),

        // === UNIFORM (6 sản phẩm) ===
        array(
            'sku'         => 'DRX-UNI-01',
            'name'        => '26 S1 AUTHENTIC JUMPER 3RD(PINK)',
            'type'        => 'variable',
            'price'       => 2850000,
            'short_desc'  => 'Áo khoác thi đấu phiên bản đặc biệt 3rd Pink DRX mùa 2026.',
            'desc'        => 'Áo khoác jumper thi đấu màu hồng phấn phối xanh navy độc bản.',
            'cats'        => array('UNIFORM'),
            'is_apparel'  => true
        ),
        array(
            'sku'         => 'DRX-UNI-02',
            'name'        => '26 S1 AUTHENTIC T-SHIRT 3RD(PINK)',
            'type'        => 'variable',
            'price'       => 1850000,
            'short_desc'  => 'Áo thi đấu chính thức bản 3rd Pink DRX 2026 thoáng khí. [ALLOW_CUSTOM_ID]',
            'desc'        => 'Áo thi đấu thun thể thao co giãn 4 chiều màu hồng pastel. [ALLOW_CUSTOM_ID]',
            'cats'        => array('UNIFORM'),
            'is_apparel'  => true
        ),
        array(
            'sku'         => 'DRX-UNI-03',
            'name'        => '26 S1 UNIFORM PANTS',
            'type'        => 'variable',
            'price'       => 990000,
            'short_desc'  => 'Quần dài thể thao thi đấu đồng bộ DRX 2026.',
            'desc'        => 'Quần dài thi đấu phom dáng năng động với túi khóa kéo tiện ích.',
            'cats'        => array('UNIFORM'),
            'is_apparel'  => true
        ),
        array(
            'sku'         => 'DRX-UNI-04',
            'name'        => '26 S1 AUTHENTIC JUMPER AWAY(L.BLUE)',
            'type'        => 'variable',
            'price'       => 2850000,
            'short_desc'  => 'Áo khoác thi đấu sân khách Light Blue DRX 2026 cao cấp.',
            'desc'        => 'Áo khoác dù thi đấu cản gió chống nước màu xanh dương nhạt.',
            'cats'        => array('UNIFORM'),
            'is_apparel'  => true
        ),
        array(
            'sku'         => 'DRX-UNI-05',
            'name'        => '26 S1 AUTHENTIC T-SHIRT AWAY(L.BLUE)',
            'type'        => 'variable',
            'price'       => 1850000,
            'short_desc'  => 'Áo thi đấu sân khách Light Blue DRX 2026. [ALLOW_CUSTOM_ID]',
            'desc'        => 'Áo đấu sân khách màu xanh dương thanh lịch của các tuyển thủ DRX. [ALLOW_CUSTOM_ID]',
            'cats'        => array('UNIFORM'),
            'is_apparel'  => true
        ),
        array(
            'sku'         => 'DRX-UNI-06',
            'name'        => '26 S1 AUTHENTIC JUMPER HOME(NAVY)',
            'type'        => 'variable',
            'price'       => 2850000,
            'short_desc'  => 'Áo khoác thi đấu sân nhà màu Navy huyền thoại DRX 2026.',
            'desc'        => 'Áo khoác gió sân nhà chính thức của đội tuyển DRX tại giải LCK.',
            'cats'        => array('UNIFORM'),
            'is_apparel'  => true
        ),

        // === TEAM-KIT (6 sản phẩm) ===
        array(
            'sku'         => 'DRX-KIT-01',
            'name'        => '26 LOGO BEACH TOWEL',
            'type'        => 'simple',
            'price'       => 420000,
            'short_desc'  => 'Khăn tắm bãi biển in logo rồng DRX kích thước lớn 150x75cm.',
            'desc'        => 'Khăn sợi bông cao cấp mềm mịn thấm hút nước cực tốt.',
            'cats'        => array('TEAM-KIT'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-KIT-02',
            'name'        => '26 LOGO BANDANA',
            'type'        => 'simple',
            'price'       => 190000,
            'short_desc'  => 'Khăn bandana vuông thời trang thêu biểu tượng DRX.',
            'desc'        => 'Phụ kiện quàng cổ hoặc buộc đầu cá tính cho người hâm mộ.',
            'cats'        => array('TEAM-KIT'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-KIT-03',
            'name'        => '26 MAGSAFE CARD HOLDER',
            'type'        => 'simple',
            'price'       => 350000,
            'short_desc'  => 'Ví đựng thẻ hít nam châm MagSafe gắn lưng điện thoại logo DRX.',
            'desc'        => 'Ví da PU cao cấp chứa được 2-3 thẻ tiện lợi.',
            'cats'        => array('TEAM-KIT'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-KIT-04',
            'name'        => '26 MOUSEPAD',
            'type'        => 'simple',
            'price'       => 540000,
            'short_desc'  => 'Lót chuột gaming bề mặt vải dệt siêu mượt điều khiển chính xác.',
            'desc'        => 'Pad chuột thể thao điện tử DRX bo viền chắc chắn chống trượt.',
            'cats'        => array('TEAM-KIT'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-KIT-05',
            'name'        => '26 TICKETHOLDER',
            'type'        => 'simple',
            'price'       => 350000,
            'short_desc'  => 'Bao đựng vé thi đấu và thẻ đeo ban tổ chức DRX 2026.',
            'desc'        => 'Túi đựng vé nhựa dẻo trong suốt kèm dây đeo cổ tiện dụng.',
            'cats'        => array('TEAM-KIT'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-KIT-06',
            'name'        => '26 SMARTPHONE STRAP',
            'type'        => 'simple',
            'price'       => 220000,
            'short_desc'  => 'Dây đeo điện thoại cổ tay phong cách thể thao DRX 2026.',
            'desc'        => 'Dây đeo chịu lực cao chống rơi rớt điện thoại khi di chuyển.',
            'cats'        => array('TEAM-KIT'),
            'is_apparel'  => false
        ),

        // === COLABORATION (6 sản phẩm) ===
        array(
            'sku'         => 'DRX-COL-01',
            'name'        => '24 DRX PRX METAL BADGE',
            'type'        => 'simple',
            'price'       => 190000,
            'short_desc'  => 'Huy hiệu kim loại phiên bản kết hợp đặc biệt giữa DRX và Paper Rex.',
            'desc'        => 'Huy hiệu đúc kim loại cao cấp ghim áo hoặc balo.',
            'cats'        => array('COLABORATION'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-COL-02',
            'name'        => '24 DRX PRX CANVAS TOTE BAG',
            'type'        => 'simple',
            'price'       => 390000,
            'short_desc'  => 'Túi tote vải canvas phong cách streetwear DRX x PRX.',
            'desc'        => 'Túi vải dày dặn có quai xách chắc chắn in họa tiết độc quyền.',
            'cats'        => array('COLABORATION'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-COL-03',
            'name'        => '24 DRX PRX S/S T-SHIRT',
            'type'        => 'variable',
            'price'       => 790000,
            'short_desc'  => 'Áo thun tay ngắn hợp tác DRX x Paper Rex phiên bản giới hạn.',
            'desc'        => 'Áo thun cotton cao cấp phom rộng phong cách hiện đại.',
            'cats'        => array('COLABORATION'),
            'is_apparel'  => true
        ),
        array(
            'sku'         => 'DRX-COL-04',
            'name'        => '24 DRX PRX JEOGORI',
            'type'        => 'variable',
            'price'       => 1550000,
            'short_desc'  => 'Áo khoác cách tân Jeogori truyền thống Hàn Quốc phiên bản DRX x PRX.',
            'desc'        => 'Mẫu áo khoác độc bản kết hợp nét truyền thống và văn hóa Esports.',
            'cats'        => array('COLABORATION'),
            'is_apparel'  => true
        ),
        array(
            'sku'         => 'DRX-COL-05',
            'name'        => 'TALON X DRX BUBBLEGUM SKY PHOTOCARD',
            'type'        => 'simple',
            'price'       => 380000,
            'short_desc'  => 'Bộ thẻ ảnh sưu tầm phiên bản hợp tác Talon x DRX Bubblegum Sky.',
            'desc'        => 'Set photocard tuyển thủ in hologram bắt sáng cực đẹp.',
            'cats'        => array('COLABORATION'),
            'is_apparel'  => false
        ),
        array(
            'sku'         => 'DRX-COL-06',
            'name'        => 'DRX X LILKA RUGBY JERSEY WHITE',
            'type'        => 'variable',
            'price'       => 1600000,
            'short_desc'  => 'Áo đấu bóng bầu dục cổ bẻ phối sọc trắng hợp tác DRX x Lilka. [ALLOW_CUSTOM_ID]',
            'desc'        => 'Áo đấu rugby chất vải cao cấp dày dặn phong cách thể thao cổ điển. [ALLOW_CUSTOM_ID]',
            'cats'        => array('COLABORATION', 'UNIFORM'),
            'is_apparel'  => true
        ),
    );

    foreach ($products_data as $p) {
        $p_id = wc_get_product_id_by_sku($p['sku']);
        if (!$p_id) {
            $existing_post = get_page_by_title($p['name'], OBJECT, 'product');
            if ($existing_post) {
                $p_id = $existing_post->ID;
            }
        }

        if ($p_id) {
            $product = wc_get_product($p_id);
        } else {
            $product = ($p['type'] === 'variable') ? new WC_Product_Variable() : new WC_Product_Simple();
        }

        if (!$product) continue;

        $product->set_name($p['name']);
        $product->set_sku($p['sku']);
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        $product->set_short_description($p['short_desc']);
        $product->set_description($p['desc']);
        $product->set_regular_price($p['price']);
        $product->set_price($p['price']);
        $product->set_manage_stock(true);
        $product->set_stock_quantity(100);
        $product->set_stock_status('instock');

        $cat_term_ids = array();
        foreach ($p['cats'] as $c) {
            if (isset($cat_ids[$c])) {
                $cat_term_ids[] = (int)$cat_ids[$c];
            }
        }
        $product->set_category_ids($cat_term_ids);
        $product->save();
    }

    update_option($sync_key, 'yes');
}
add_action('init', 'drx_auto_sync_authentic_products', 10);

/**
 * Tự động dọn dẹp các sản phẩm rác (Ghost products) có giá 0đ và không có trong wp-admin
 */
function drx_cleanup_zero_price_ghost_products() {
    $cleanup_key = '_drx_ghost_products_cleaned_v3';
    if (get_option($cleanup_key) === 'yes') {
        return;
    }

    if (!class_exists('WooCommerce')) {
        return;
    }

    $ghost_query = new WP_Query(array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_price',
                'value'   => 0,
                'compare' => '<=',
                'type'    => 'NUMERIC'
            ),
            array(
                'key'     => '_price',
                'value'   => '',
                'compare' => '='
            ),
            array(
                'key'     => '_price',
                'compare' => 'NOT EXISTS'
            )
        )
    ));

    if ($ghost_query->have_posts()) {
        while ($ghost_query->have_posts()) {
            $ghost_query->the_post();
            $post_id = get_the_ID();
            $sku = get_post_meta($post_id, '_sku', true);
            $has_thumb = has_post_thumbnail($post_id);

            // Bảo vệ các sản phẩm chính hãng hoặc sản phẩm có hình ảnh hợp lệ
            if (!empty($sku) && strpos($sku, 'DRX-') === 0) {
                continue;
            }
            if ($has_thumb) {
                continue;
            }

            // Xóa triệt để sản phẩm rác không có ảnh và giá 0đ
            wp_delete_post($post_id, true);
        }
        wp_reset_postdata();
    }

    update_option($cleanup_key, 'yes');
}
add_action('init', 'drx_cleanup_zero_price_ghost_products', 20);

