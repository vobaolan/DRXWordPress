<?php
/**
 * Plugin Name: DRX Apparel Store - Data Architecture (Lookbook & Custom Specs)
 * Plugin URI: https://github.com/DRXEsports/DRXWordPress
 * Description: Plugin khởi tạo khung xương dữ liệu (Data Architecture) cho Shop Thời trang Quần áo DRX: CPT Bộ sưu tập Lookbook (drx_lookbook), Taxonomy Mùa phát hành (lookbook_season), Meta Box tùy chọn thêu tên áo (custom_id), chất liệu vải và thông số mẫu mặc.
 * Version: 1.1.0
 * Author: DRX Development Team (VLSC.V6)
 * Author URI: https://teamdrx.vercel.app/store
 * Text Domain: drx-data-architecture
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit; // Chặn truy cập trực tiếp
}

/* ==========================================================================
   1. ĐĂNG KÝ CUSTOM POST TYPE & TAXONOMY (drx_lookbook & lookbook_season)
   ========================================================================== */

function drx_plugin_register_apparel_cpt_taxonomies() {
    // 1.1 Đăng ký Taxonomy: Mùa phát hành (lookbook_season)
    $tax_labels = [
        'name'              => _x('Mùa phát hành', 'taxonomy general name', 'drx-data-architecture'),
        'singular_name'     => _x('Mùa phát hành', 'taxonomy singular name', 'drx-data-architecture'),
        'search_items'      => __('Tìm kiếm Mùa phát hành', 'drx-data-architecture'),
        'all_items'         => __('Tất cả Mùa phát hành', 'drx-data-architecture'),
        'parent_item'       => __('Mùa cha', 'drx-data-architecture'),
        'parent_item_colon' => __('Mùa cha:', 'drx-data-architecture'),
        'edit_item'         => __('Chỉnh sửa Mùa', 'drx-data-architecture'),
        'update_item'       => __('Cập nhật Mùa', 'drx-data-architecture'),
        'add_new_item'      => __('Thêm Mùa Mới', 'drx-data-architecture'),
        'new_item_name'     => __('Tên Mùa Mới (VD: Spring/Summer 2026)', 'drx-data-architecture'),
        'menu_name'         => __('Mùa phát hành', 'drx-data-architecture'),
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

    // 1.2 Đăng ký Custom Post Type: Lookbook Thời Trang (drx_lookbook)
    $cpt_labels = [
        'name'                  => _x('Bộ sưu tập Lookbook', 'Post Type General Name', 'drx-data-architecture'),
        'singular_name'         => _x('Lookbook', 'Post Type Singular Name', 'drx-data-architecture'),
        'menu_name'             => __('Lookbook Thời Trang', 'drx-data-architecture'),
        'name_admin_bar'        => __('Lookbook', 'drx-data-architecture'),
        'archives'              => __('Danh mục Lookbook', 'drx-data-architecture'),
        'attributes'            => __('Thuộc tính Lookbook', 'drx-data-architecture'),
        'all_items'             => __('Tất cả Bộ sưu tập', 'drx-data-architecture'),
        'add_new_item'          => __('Thêm Bộ sưu tập Mới', 'drx-data-architecture'),
        'add_new'               => __('Thêm Mới', 'drx-data-architecture'),
        'new_item'              => __('Bộ sưu tập Mới', 'drx-data-architecture'),
        'edit_item'             => __('Chỉnh sửa Bộ sưu tập', 'drx-data-architecture'),
        'update_item'           => __('Cập nhật Bộ sưu tập', 'drx-data-architecture'),
        'view_item'             => __('Xem Lookbook', 'drx-data-architecture'),
        'search_items'          => __('Tìm kiếm Lookbook', 'drx-data-architecture'),
    ];

    $cpt_args = [
        'label'                 => __('Bộ sưu tập Lookbook', 'drx-data-architecture'),
        'description'           => __('Các bộ sưu tập thời trang, đồng phục và phong cách của DRX Store', 'drx-data-architecture'),
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
add_action('init', 'drx_plugin_register_apparel_cpt_taxonomies', 0);


/* ==========================================================================
   2. KHỞI TẠO CUSTOM META BOX (LOOKBOOK & APPAREL SPECS)
   ========================================================================== */

function drx_plugin_add_apparel_meta_boxes() {
    add_meta_box(
        'drx_lookbook_details',
        __('Thông tin Bộ Sưu Tập Thời Trang (Lookbook Details)', 'drx-data-architecture'),
        'drx_plugin_render_lookbook_box',
        'drx_lookbook',
        'normal',
        'high'
    );

    add_meta_box(
        'drx_product_apparel_details',
        __('Thông số Quần Áo & Tùy Chọn May Thêu (DRX Apparel Specs)', 'drx-data-architecture'),
        'drx_plugin_render_apparel_product_box',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'drx_plugin_add_apparel_meta_boxes');

function drx_plugin_render_lookbook_box($post) {
    wp_nonce_field('drx_plugin_lookbook_action', 'drx_plugin_lookbook_nonce');

    $theme       = get_post_meta($post->ID, '_drx_lookbook_theme', true);
    $collab      = get_post_meta($post->ID, '_drx_collab_brand', true);
    $featured_id = get_post_meta($post->ID, '_drx_featured_product_id', true);
    $release_date= get_post_meta($post->ID, '_drx_release_date', true);

    $products = [];
    if (post_type_exists('product')) {
        $products = get_posts([
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);
    }
    ?>
    <style>
        .drx-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 10px; }
        .drx-meta-field { margin-bottom: 12px; }
        .drx-meta-field.full-width { grid-column: span 2; }
        .drx-meta-field label { display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b; }
        .drx-meta-field input[type="text"], 
        .drx-meta-field input[type="date"], 
        .drx-meta-field select { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .drx-meta-desc { font-size: 12px; color: #64748b; margin-top: 4px; }
    </style>

    <div class="drx-meta-grid">
        <div class="drx-meta-field">
            <label for="drx_lookbook_theme"><?php _e('Phong cách thiết kế (Theme / Style):', 'drx-data-architecture'); ?></label>
            <input type="text" id="drx_lookbook_theme" name="drx_lookbook_theme" value="<?php echo esc_attr($theme); ?>" placeholder="VD: Cyberpunk, Minimalist Sportswear, Retro Streetwear..." />
            <p class="drx-meta-desc"><?php _e('Định hướng phong cách chủ đạo của bộ sưu tập.', 'drx-data-architecture'); ?></p>
        </div>

        <div class="drx-meta-field">
            <label for="drx_collab_brand"><?php _e('Thương hiệu hợp tác (Collab Brand):', 'drx-data-architecture'); ?></label>
            <input type="text" id="drx_collab_brand" name="drx_collab_brand" value="<?php echo esc_attr($collab); ?>" placeholder="VD: DRX Studio, Puma, New Era, Logitech..." />
            <p class="drx-meta-desc"><?php _e('Đơn vị đồng thiết kế hoặc sản xuất bộ sưu tập.', 'drx-data-architecture'); ?></p>
        </div>

        <div class="drx-meta-field">
            <label for="drx_featured_product_id"><?php _e('Trang phục tâm điểm (Featured Apparel):', 'drx-data-architecture'); ?></label>
            <select id="drx_featured_product_id" name="drx_featured_product_id">
                <option value=""><?php _e('-- Chọn sản phẩm tâm điểm --', 'drx-data-architecture'); ?></option>
                <?php if (!empty($products)) : ?>
                    <?php foreach ($products as $p) : ?>
                        <option value="<?php echo esc_attr($p->ID); ?>" <?php selected($featured_id, $p->ID); ?>>
                            <?php echo esc_html($p->post_title); ?> (#<?php echo esc_html($p->ID); ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <p class="drx-meta-desc"><?php _e('Mẫu áo/quần chủ đạo đại diện cho Lookbook.', 'drx-data-architecture'); ?></p>
        </div>

        <div class="drx-meta-field">
            <label for="drx_release_date"><?php _e('Ngày mở bán chính thức (Release Date):', 'drx-data-architecture'); ?></label>
            <input type="date" id="drx_release_date" name="drx_release_date" value="<?php echo esc_attr($release_date); ?>" />
            <p class="drx-meta-desc"><?php _e('Thời gian mở bán đợt hàng đầu tiên.', 'drx-data-architecture'); ?></p>
        </div>
    </div>
    <?php
}

function drx_plugin_render_apparel_product_box($post) {
    wp_nonce_field('drx_plugin_product_apparel_action', 'drx_plugin_product_apparel_nonce');

    $allow_custom = get_post_meta($post->ID, '_drx_allow_custom_id', true);
    $fabric       = get_post_meta($post->ID, '_drx_fabric_material', true);
    $model_specs  = get_post_meta($post->ID, '_drx_model_specs', true);
    $care_guide   = get_post_meta($post->ID, '_drx_care_guide', true);
    ?>
    <div class="drx-meta-grid">
        <div class="drx-meta-field full-width" style="background:#FFF7ED; padding:12px; border-radius:8px; border:1px solid #FFEDD5;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; color:#9A3412;">
                <input type="checkbox" name="drx_allow_custom_id" value="yes" <?php checked($allow_custom, 'yes'); ?> />
                <strong>★ Cho phép khách hàng nhập Tên / ID để in may thêu lên áo (Custom ID)</strong>
            </label>
            <p class="drx-meta-desc" style="color:#C2410C; margin-left:24px;"><?php _e('Khi bật tùy chọn này, trang chi tiết và Quick View sẽ hiển thị ô nhập tên thêu riêng của khách hàng.', 'drx-data-architecture'); ?></p>
        </div>

        <div class="drx-meta-field">
            <label for="drx_fabric_material"><?php _e('Chất liệu vải (Fabric Material):', 'drx-data-architecture'); ?></label>
            <input type="text" id="drx_fabric_material" name="drx_fabric_material" value="<?php echo esc_attr($fabric); ?>" placeholder="VD: 100% CoolMax Polyester kháng khuẩn, Cotton 250gsm..." />
            <p class="drx-meta-desc"><?php _e('Thành phần cấu tạo và định lượng vải của trang phục.', 'drx-data-architecture'); ?></p>
        </div>

        <div class="drx-meta-field">
            <label for="drx_model_specs"><?php _e('Thông số người mẫu mặc mẫu (Model Specs):', 'drx-data-architecture'); ?></label>
            <input type="text" id="drx_model_specs" name="drx_model_specs" value="<?php echo esc_attr($model_specs); ?>" placeholder="VD: Mẫu nam 1m78, 68kg - Đang mặc Size L" />
            <p class="drx-meta-desc"><?php _e('Giúp người mua dễ dàng ướm kích cỡ áo phù hợp.', 'drx-data-architecture'); ?></p>
        </div>

        <div class="drx-meta-field full-width">
            <label for="drx_care_guide"><?php _e('Hướng dẫn giặt ủi & bảo quản đồ thể thao (Care Instructions):', 'drx-data-architecture'); ?></label>
            <input type="text" id="drx_care_guide" name="drx_care_guide" value="<?php echo esc_attr($care_guide); ?>" placeholder="VD: Giặt máy nước lạnh dưới 30°C, không ủi trực tiếp lên logo may thêu..." />
            <p class="drx-meta-desc"><?php _e('Lưu ý bảo quản giữ form áo bền đẹp.', 'drx-data-architecture'); ?></p>
        </div>
    </div>
    <?php
}

// Lưu dữ liệu
function drx_plugin_save_apparel_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['drx_plugin_lookbook_nonce']) && wp_verify_nonce($_POST['drx_plugin_lookbook_nonce'], 'drx_plugin_lookbook_action')) {
        if (current_user_can('edit_post', $post_id)) {
            if (isset($_POST['drx_lookbook_theme'])) update_post_meta($post_id, '_drx_lookbook_theme', sanitize_text_field($_POST['drx_lookbook_theme']));
            if (isset($_POST['drx_collab_brand'])) update_post_meta($post_id, '_drx_collab_brand', sanitize_text_field($_POST['drx_collab_brand']));
            if (isset($_POST['drx_featured_product_id'])) update_post_meta($post_id, '_drx_featured_product_id', intval($_POST['drx_featured_product_id']));
            if (isset($_POST['drx_release_date'])) update_post_meta($post_id, '_drx_release_date', sanitize_text_field($_POST['drx_release_date']));
        }
    }

    if (isset($_POST['drx_plugin_product_apparel_nonce']) && wp_verify_nonce($_POST['drx_plugin_product_apparel_nonce'], 'drx_plugin_product_apparel_action')) {
        if (current_user_can('edit_post', $post_id)) {
            $allow_custom = isset($_POST['drx_allow_custom_id']) ? 'yes' : 'no';
            update_post_meta($post_id, '_drx_allow_custom_id', $allow_custom);
            if (isset($_POST['drx_fabric_material'])) update_post_meta($post_id, '_drx_fabric_material', sanitize_text_field($_POST['drx_fabric_material']));
            if (isset($_POST['drx_model_specs'])) update_post_meta($post_id, '_drx_model_specs', sanitize_text_field($_POST['drx_model_specs']));
            if (isset($_POST['drx_care_guide'])) update_post_meta($post_id, '_drx_care_guide', sanitize_text_field($_POST['drx_care_guide']));
        }
    }
}
add_action('save_post', 'drx_plugin_save_apparel_meta');
