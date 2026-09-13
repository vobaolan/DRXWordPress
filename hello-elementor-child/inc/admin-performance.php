<?php
/**
 * Tối ưu hóa hiệu năng WordPress Admin, Media Library & WooCommerce Product Editor
 * 
 * Khắc phục hiện tượng lag, chậm khi chỉnh sửa sản phẩm và xử lý hình ảnh trên Localhost.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. Tăng giới hạn bộ nhớ Memory Limit và thời gian xử lý ảnh
 */
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '300');

/**
 * 2. Giảm tải số lượng kích thước ảnh trung gian (Sub-sizes) không cần thiết
 * Khi upload/chỉnh sửa ảnh, WP mặc định tạo ra 10-15 phiên bản kích cỡ gây nghẽn ổ đĩa.
 */
add_filter('intermediate_image_sizes_advanced', function($sizes) {
    unset($sizes['1536x1536']); // Bỏ kích thước siêu lớn 1536px
    unset($sizes['2048x2048']); // Bỏ kích thước siêu lớn 2048px
    return $sizes;
});

// Giới hạn ngưỡng kích thước ảnh tối đa để tránh đơ trình duyệt khi crop/scale
add_filter('big_image_size_threshold', function() {
    return 2000;
});

// Nén nhẹ chất lượng JPEG để render và lưu ảnh cực nhanh
add_filter('jpeg_quality', function() {
    return 85;
});

/**
 * 3. Kiểm soát nhịp đập Heartbeat API (giảm từ 15s xuống 60s để tránh chiếm luồng PHP)
 */
add_filter('heartbeat_settings', function($settings) {
    $settings['interval'] = 60; // 60 giây / 1 lần thay vì 15 giây
    return $settings;
});

/**
 * 4. Rút ngắn thời gian chờ timeout các kết nối HTTP ra ngoài (api.wordpress.org, elementor...)
 * Tránh trường hợp Localhost bị treo 30 giây khi mạng chập chờn
 */
add_filter('http_request_timeout', function($timeout) {
    return 3; // Timeout tối đa 3s trên localhost
});

/**
 * 5. Tắt thông báo kiểm tra phiên bản ngầm gây lag khi lưu bài
 */
add_filter('pre_site_transient_update_core', '__return_null');
add_filter('pre_site_transient_update_plugins', '__return_null');
add_filter('pre_site_transient_update_themes', '__return_null');

/**
 * 6. Tối ưu giao diện Edit Product trong Admin
 */
add_action('admin_head', function() {
    echo '<style>
        .elementor-ai-btn, .e-ai-button, .elementor-ai-context-menu { opacity: 0.8; }
        .postbox { box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    </style>';
});
