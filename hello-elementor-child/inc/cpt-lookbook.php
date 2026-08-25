<?php
/**
 * Đăng ký Custom Post Type: Bộ sưu tập / Lookbook Thời trang (drx_lookbook)
 * Đăng ký Taxonomy: Mùa phát hành (lookbook_season)
 * Phục vụ chuyên sâu cho Shop Quần Áo & Thời Trang DRX Official Store
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

function drx_register_lookbook_cpt() {
    // 1. Đăng ký Taxonomy: Mùa phát hành (lookbook_season)
    $tax_labels = [
        'name'              => _x('Mùa phát hành', 'taxonomy general name', 'hello-elementor-child'),
        'singular_name'     => _x('Mùa phát hành', 'taxonomy singular name', 'hello-elementor-child'),
        'search_items'      => __('Tìm kiếm Mùa phát hành', 'hello-elementor-child'),
        'all_items'         => __('Tất cả Mùa phát hành', 'hello-elementor-child'),
        'parent_item'       => __('Mùa cha', 'hello-elementor-child'),
        'parent_item_colon' => __('Mùa cha:', 'hello-elementor-child'),
        'edit_item'         => __('Chỉnh sửa Mùa', 'hello-elementor-child'),
        'update_item'       => __('Cập nhật Mùa', 'hello-elementor-child'),
        'add_new_item'      => __('Thêm Mùa Mới', 'hello-elementor-child'),
        'new_item_name'     => __('Tên Mùa Mới (VD: Spring/Summer 2026)', 'hello-elementor-child'),
        'menu_name'         => __('Mùa phát hành', 'hello-elementor-child'),
    ];

    $tax_args = [
        'hierarchical'      => true,
        'labels'            => $tax_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'season'],
        'show_in_rest'      => true,
    ];

    register_taxonomy('lookbook_season', ['drx_lookbook'], $tax_args);

    // 2. Đăng ký CPT: Bộ sưu tập / Lookbook Thời trang (drx_lookbook)
    $cpt_labels = [
        'name'                  => _x('Bộ sưu tập Lookbook', 'Post Type General Name', 'hello-elementor-child'),
        'singular_name'         => _x('Lookbook', 'Post Type Singular Name', 'hello-elementor-child'),
        'menu_name'             => __('Lookbook Thời Trang', 'hello-elementor-child'),
        'name_admin_bar'        => __('Lookbook', 'hello-elementor-child'),
        'archives'              => __('Danh mục Lookbook', 'hello-elementor-child'),
        'attributes'            => __('Thuộc tính Lookbook', 'hello-elementor-child'),
        'all_items'             => __('Tất cả Bộ sưu tập', 'hello-elementor-child'),
        'add_new_item'          => __('Thêm Bộ sưu tập Mới', 'hello-elementor-child'),
        'add_new'               => __('Thêm Mới', 'hello-elementor-child'),
        'new_item'              => __('Bộ sưu tập Mới', 'hello-elementor-child'),
        'edit_item'             => __('Chỉnh sửa Bộ sưu tập', 'hello-elementor-child'),
        'update_item'           => __('Cập nhật Bộ sưu tập', 'hello-elementor-child'),
        'view_item'             => __('Xem Lookbook', 'hello-elementor-child'),
        'search_items'          => __('Tìm kiếm Lookbook', 'hello-elementor-child'),
    ];

    $cpt_args = [
        'label'                 => __('Bộ sưu tập Lookbook', 'hello-elementor-child'),
        'description'           => __('Các bộ sưu tập thời trang, đồng phục và phong cách của DRX Store', 'hello-elementor-child'),
        'labels'                => $cpt_labels,
        'supports'              => ['title', 'editor', 'thumbnail', 'excerpt'],
        'taxonomies'            => ['lookbook_season'],
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-format-gallery',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    ];

    register_post_type('drx_lookbook', $cpt_args);
}
add_action('init', 'drx_register_lookbook_cpt', 0);
