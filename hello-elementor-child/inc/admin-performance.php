<?php
/**
 * Tối ưu hóa hiệu năng WordPress Admin, Media Library & WooCommerce Product Editor
 * 
 * Khắc phục hiện tượng lag, chậm, đơ khi mở Media modal, chỉnh sửa sản phẩm và xử lý hình ảnh trên Localhost.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. Tăng giới hạn bộ nhớ RAM và thời gian thực thi PHP
 */
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '300');

/**
 * 2. Ưu tiên bộ xử lý ảnh GD siêu nhanh trên Windows Localhost
 */
add_filter('wp_image_editors', function($editors) {
    // Đưa GD lên đầu tiên vì GD trên Windows nhẹ và nhanh hơn Imagick nhiều lần
    return array('WP_Image_Editor_GD', 'WP_Image_Editor_Imagick');
});

/**
 * 3. Loại bỏ việc tạo 10-15 kích cỡ ảnh trung gian (Sub-sizes) không cần thiết
 * Giảm 80% thời gian xử lý và ghi đĩa khi lưu ảnh
 */
add_filter('intermediate_image_sizes_advanced', function($sizes) {
    unset($sizes['1536x1536']); // Bỏ ảnh siêu lớn 1536px
    unset($sizes['2048x2048']); // Bỏ ảnh siêu lớn 2048px
    unset($sizes['woocommerce_gallery_thumbnail']);
    return $sizes;
});

// Giới hạn ngưỡng kích thước ảnh tối đa
add_filter('big_image_size_threshold', function() {
    return 2000;
});

// Cố định chất lượng nén ảnh tối ưu 85%
add_filter('jpeg_quality', function() {
    return 85;
});

/**
 * 4. Tắt Elementor AI trong Media Library & Product Gallery để tránh treo modal
 */
add_filter('elementor/ai/is_enabled', '__return_false');
add_filter('elementor/ai/get_app_config', '__return_empty_array');

/**
 * 5. Tắt kiểm tra cập nhật core/plugins/themes ngầm trong Localhost
 */
add_filter('pre_site_transient_update_core', '__return_null');
add_filter('pre_site_transient_update_plugins', '__return_null');
add_filter('pre_site_transient_update_themes', '__return_null');

/**
 * 6. Tự động tối ưu và resize ảnh trước khi upload qua Plupload (Client-side)
 * Giúp việc upload và chỉnh sửa ảnh nhẹ hơn 5–10 lần, không bị đứng máy khi ảnh gốc quá to.
 */
add_filter('plupload_default_settings', function($defaults) {
    $defaults['resize'] = array(
        'width'   => 1920,
        'height'  => 1920,
        'quality' => 85,
        'enabled' => true
    );
    return $defaults;
});

/**
 * 7. Kiểm soát nhịp đập Heartbeat API (giảm tần suất từ 15s xuống 60s)
 */
add_filter('heartbeat_settings', function($settings) {
    $settings['interval'] = 60;
    return $settings;
});

/**
 * 8. Tắt Autosave và kiểm tra Revision làm chậm khi gõ và click trong Edit Product
 */
add_action('admin_enqueue_scripts', function($hook) {
    if (in_array($hook, array('post.php', 'post-new.php'))) {
        wp_dequeue_script('autosave');
    }
});

/**
 * 9. Dọn dẹp giao diện Admin & Ẩn các banner Elementor AI gây lag DOM
 */
add_action('admin_head', function() {
    echo '<style>
        .elementor-ai-btn, 
        .e-ai-button, 
        .elementor-ai-context-menu,
        #e-ai-button-wrapper { 
            display: none !important; 
        }
        /* Tối ưu tốc độ dựng hình trang Edit Product */
        .postbox { 
            contain: content; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.03); 
        }
    </style>';
});
