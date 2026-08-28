<?php
/**
 * Hello Elementor Child - DRX Official Store Theme Functions
 * 
 * Học phần: Phát triển hệ thống CMS và Thương mại điện tử (VLSC.V6)
 * Đồ án: DRX Esports Official Store & Roster Hub
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// 1. Nạp các module PHP cốt lõi từ thư mục inc/
require_once get_stylesheet_directory() . '/inc/cpt-lookbook.php';
require_once get_stylesheet_directory() . '/inc/meta-boxes.php';
require_once get_stylesheet_directory() . '/inc/woocommerce-custom.php';

/**
 * 2. Enqueue Styles và Scripts
 */
function drx_theme_enqueue_scripts() {
    // Enqueue Theme cha (Hello Elementor)
    wp_enqueue_style('hello-elementor-parent-style', get_template_directory_uri() . '/style.css');

    // Enqueue Google Fonts: Orbitron (Headings) & Inter (Body)
    wp_enqueue_style(
        'drx-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700;900&display=swap',
        array(),
        null
    );

    $theme_version = time();

    // Enqueue DRX Core CSS & Components CSS
    wp_enqueue_style(
        'drx-core-style',
        get_stylesheet_directory_uri() . '/assets/css/drx-core.css',
        array('hello-elementor-parent-style'),
        $theme_version
    );

    wp_enqueue_style(
        'drx-components-style',
        get_stylesheet_directory_uri() . '/assets/css/drx-components.css',
        array('drx-core-style'),
        $theme_version
    );

    // Enqueue DRX JavaScript
    wp_enqueue_script(
        'drx-store-script',
        get_stylesheet_directory_uri() . '/assets/js/drx-store.js',
        array('jquery'),
        $theme_version,
        true
    );

    wp_enqueue_script(
        'drx-track-order-script',
        get_stylesheet_directory_uri() . '/assets/js/drx-track-order.js',
        array('jquery'),
        $theme_version,
        true
    );

    // Truyền biến AJAX và Nonce bảo mật sang JavaScript
    wp_localize_script('drx-store-script', 'drx_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('drx_store_nonce'),
        'cart_url' => wc_get_cart_url(),
        'checkout_url' => wc_get_checkout_url()
    ));
}
add_action('wp_enqueue_scripts', 'drx_theme_enqueue_scripts', 20);

/**
 * 3. Khai báo Theme Support cho WooCommerce & WordPress hiện đại
 */
function drx_theme_setup() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
}
add_action('after_setup_theme', 'drx_theme_setup');

/**
 * 4. Tự động thêm thẻ nhúng Chatbot AI vào chân trang (Buổi 13)
 */
function drx_inject_chatbot_embed_code() {
    ?>
    <!-- DRX AI Chatbot Integration -->
    <script>
        // Placeholder cấu hình Chatbot AI (Tidio / Chatbase)
        window.drxChatbotConfig = {
            brandName: "DRX Official Store",
            themeColor: "#0052FF",
            greetingMessage: "Chào bạn! Shop có thể hỗ trợ gì về áo đấu và merchandise DRX hôm nay?"
        };
    </script>
    <?php
}
add_action('wp_footer', 'drx_inject_chatbot_embed_code', 99);

/**
 * 5. Tắt Header & Footer & Page Title mặc định của theme cha Hello Elementor
 */
add_filter('hello_elementor_display_header_footer', '__return_false');
add_filter('hello_elementor_page_title', '__return_false');

/**
 * 6. Tự động cấu hình Shipping Zones & Payment Gateways (Nhiệm vụ Buổi 11)
 */
function drx_auto_configure_store_settings() {
    if (!class_exists('WooCommerce')) {
        return;
    }

    $configured = get_option('_drx_buoi11_configured_v1');
    if ($configured) {
        return;
    }

    // 1. Cấu hình Payment Gateway: COD (Thanh toán khi nhận hàng)
    $cod_settings = get_option('woocommerce_cod_settings', array());
    $cod_settings['enabled'] = 'yes';
    $cod_settings['title'] = 'Thanh toán khi nhận hàng (COD)';
    $cod_settings['description'] = 'Thanh toán bằng tiền mặt trực tiếp cho nhân viên giao hàng khi nhận sản phẩm.';
    $cod_settings['instructions'] = 'Vui lòng chuẩn bị đúng số tiền mặt khi nhân viên bưu tá giao hàng.';
    update_option('woocommerce_cod_settings', $cod_settings);

    // 2. Cấu hình Payment Gateway: BACS (Chuyển khoản ngân hàng)
    $bacs_settings = get_option('woocommerce_bacs_settings', array());
    $bacs_settings['enabled'] = 'yes';
    $bacs_settings['title'] = 'Chuyển khoản ngân hàng (Direct Bank Transfer)';
    $bacs_settings['description'] = 'Thực hiện thanh toán vào ngay tài khoản ngân hàng của chúng tôi. Vui lòng sử dụng Mã đơn hàng của bạn trong phần Nội dung thanh toán.';
    $bacs_settings['instructions'] = 'Đơn hàng sẽ được chuyển đi sau khi tiền đã chuyển vào tài khoản của chúng tôi.';
    $bacs_accounts = array(
        array(
            'account_name'   => 'DRX OFFICIAL STORE',
            'account_number' => '1029384756',
            'bank_name'      => 'MB Bank (Ngân hàng Quân Đội)',
            'sort_code'      => '',
            'iban'           => '',
            'bic'            => 'Chi nhánh TP. Hồ Chí Minh'
        )
    );
    $bacs_settings['accounts'] = $bacs_accounts;
    update_option('woocommerce_bacs_settings', $bacs_settings);
    update_option('woocommerce_bacs_accounts', $bacs_accounts);

    // 3. Cấu hình Shipping Zones: Nội thành (30.000 VNĐ) & Toàn quốc (50.000 VNĐ)
    if (class_exists('WC_Shipping_Zones')) {
        $zones = WC_Shipping_Zones::get_zones();
        $has_noi_thanh = false;
        $has_toan_quoc = false;

        foreach ($zones as $z) {
            if ($z['zone_name'] === 'Nội thành') $has_noi_thanh = true;
            if ($z['zone_name'] === 'Toàn quốc') $has_toan_quoc = true;
        }

        if (!$has_noi_thanh) {
            $zone_noi_thanh = new WC_Shipping_Zone();
            $zone_noi_thanh->set_zone_name('Nội thành');
            $zone_noi_thanh->set_zone_order(1);
            $zone_noi_thanh->add_location('VN:SG', 'state');
            $zone_noi_thanh->add_location('VN:HN', 'state');
            $zone_noi_thanh->save();

            $method_id = $zone_noi_thanh->add_shipping_method('flat_rate');
            $method = WC_Shipping_Zones::get_shipping_method($method_id);
            if ($method) {
                update_option($method->get_instance_option_key(), array(
                    'title'      => 'Giao hàng Tiêu chuẩn (Nội thành)',
                    'tax_status' => 'none',
                    'cost'       => '30000'
                ));
            }
        }

        if (!$has_toan_quoc) {
            $zone_toan_quoc = new WC_Shipping_Zone();
            $zone_toan_quoc->set_zone_name('Toàn quốc');
            $zone_toan_quoc->set_zone_order(2);
            $zone_toan_quoc->add_location('VN', 'country');
            $zone_toan_quoc->save();

            $method_id = $zone_toan_quoc->add_shipping_method('flat_rate');
            $method = WC_Shipping_Zones::get_shipping_method($method_id);
            if ($method) {
                update_option($method->get_instance_option_key(), array(
                    'title'      => 'Giao hàng Toàn quốc',
                    'tax_status' => 'none',
                    'cost'       => '50000'
                ));
            }
        }

        // Rest of the World (Vùng mặc định 50.000 VNĐ)
        $default_zone = new WC_Shipping_Zone(0);
        $def_methods = $default_zone->get_shipping_methods();
        if (empty($def_methods)) {
            $def_id = $default_zone->add_shipping_method('flat_rate');
            $def_method = WC_Shipping_Zones::get_shipping_method($def_id);
            if ($def_method) {
                update_option($def_method->get_instance_option_key(), array(
                    'title'      => 'Giao hàng Toàn quốc',
                    'tax_status' => 'none',
                    'cost'       => '50000'
                ));
            }
        }
    }

    update_option('_drx_buoi11_configured_v1', 'yes');
}
add_action('init', 'drx_auto_configure_store_settings', 5);


