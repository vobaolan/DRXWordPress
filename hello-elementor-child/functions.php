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

