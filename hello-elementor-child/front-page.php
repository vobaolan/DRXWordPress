<?php
/**
 * File: front-page.php
 * Theme: Hello Elementor Child - DRX Official Store
 * 
 * Tự động biến toàn bộ giao diện DRX Store thành Trang Chủ (Homepage) mặc định của WordPress.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Nạp trực tiếp nội dung giao diện DRX Store
require get_stylesheet_directory() . '/templates/template-drx-store.php';
