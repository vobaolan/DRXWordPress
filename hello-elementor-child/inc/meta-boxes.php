<?php
/**
 * Đăng ký Custom Meta Box phục vụ Shop Quần Áo & Thời Trang DRX
 * - Meta Box Bộ Sưu Tập Lookbook (Phong cách, Thương hiệu collab, Áo tâm điểm)
 * - Meta Box Sản phẩm Thời trang (In thêu tên áo, Chất liệu vải, Số đo mẫu mặc)
 * - Bảo mật chuẩn: Nonce Token, Capability Check & Sanitization
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ==========================================================================
   1. ĐĂNG KÝ CÁC META BOX
   ========================================================================== */

function drx_add_apparel_meta_boxes() {
    // 1.1 Meta Box cho CPT Lookbook Thời Trang
    add_meta_box(
        'drx_lookbook_details',
        __('Thông tin Bộ Sưu Tập Thời Trang (Lookbook Details)', 'hello-elementor-child'),
        'drx_render_lookbook_meta_box',
        'drx_lookbook',
        'normal',
        'high'
    );

    // 1.2 Meta Box cho Sản Phẩm Quần Áo WooCommerce
    add_meta_box(
        'drx_product_apparel_details',
        __('Thông số Quần Áo & Tùy Chọn May Thêu (DRX Apparel Specs)', 'hello-elementor-child'),
        'drx_render_product_apparel_meta_box',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'drx_add_apparel_meta_boxes');


/* ==========================================================================
   2. RENDER GIAO DIỆN META BOX LOOKBOOK
   ========================================================================== */

function drx_render_lookbook_meta_box($post) {
    wp_nonce_field('drx_save_lookbook_meta_action', 'drx_lookbook_meta_nonce');

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
        .drx-box-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 10px; }
        .drx-box-field { margin-bottom: 12px; }
        .drx-box-field.full { grid-column: span 2; }
        .drx-box-field label { display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b; }
        .drx-box-field input[type="text"], 
        .drx-box-field input[type="date"], 
        .drx-box-field select, 
        .drx-box-field textarea { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .drx-box-desc { font-size: 12px; color: #64748b; margin-top: 4px; }
    </style>

    <div class="drx-box-grid">
        <div class="drx-box-field">
            <label for="drx_lookbook_theme"><?php _e('Phong cách thiết kế (Theme / Style):', 'hello-elementor-child'); ?></label>
            <input type="text" id="drx_lookbook_theme" name="drx_lookbook_theme" value="<?php echo esc_attr($theme); ?>" placeholder="VD: Cyberpunk, Minimalist Sportswear, Retro Streetwear..." />
            <p class="drx-box-desc"><?php _e('Định hướng phong cách chủ đạo của bộ sưu tập.', 'hello-elementor-child'); ?></p>
        </div>

        <div class="drx-box-field">
            <label for="drx_collab_brand"><?php _e('Thương hiệu / Đối tác hợp tác (Collab Brand):', 'hello-elementor-child'); ?></label>
            <input type="text" id="drx_collab_brand" name="drx_collab_brand" value="<?php echo esc_attr($collab); ?>" placeholder="VD: DRX Studio, Puma, New Era, Logitech..." />
            <p class="drx-box-desc"><?php _e('Đơn vị đồng thiết kế hoặc sản xuất bộ sưu tập.', 'hello-elementor-child'); ?></p>
        </div>

        <div class="drx-box-field">
            <label for="drx_featured_product_id"><?php _e('Trang phục tâm điểm (Featured Apparel):', 'hello-elementor-child'); ?></label>
            <select id="drx_featured_product_id" name="drx_featured_product_id">
                <option value=""><?php _e('-- Chọn sản phẩm tâm điểm --', 'hello-elementor-child'); ?></option>
                <?php if (!empty($products)) : ?>
                    <?php foreach ($products as $p) : ?>
                        <option value="<?php echo esc_attr($p->ID); ?>" <?php selected($featured_id, $p->ID); ?>>
                            <?php echo esc_html($p->post_title); ?> (#<?php echo esc_html($p->ID); ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <p class="drx-box-desc"><?php _e('Mẫu áo/quần chủ đạo đại diện cho Lookbook.', 'hello-elementor-child'); ?></p>
        </div>

        <div class="drx-box-field">
            <label for="drx_release_date"><?php _e('Ngày mở bán chính thức (Release Date):', 'hello-elementor-child'); ?></label>
            <input type="date" id="drx_release_date" name="drx_release_date" value="<?php echo esc_attr($release_date); ?>" />
            <p class="drx-box-desc"><?php _e('Thời gian mở bán đợt hàng đầu tiên.', 'hello-elementor-child'); ?></p>
        </div>
    </div>
    <?php
}


/* ==========================================================================
   3. RENDER GIAO DIỆN META BOX SẢN PHẨM QUẦN ÁO (WOOCOMMERCE)
   ========================================================================== */

function drx_render_product_apparel_meta_box($post) {
    wp_nonce_field('drx_save_product_apparel_action', 'drx_product_apparel_nonce');

    $allow_custom = get_post_meta($post->ID, '_drx_allow_custom_id', true);
    $fabric       = get_post_meta($post->ID, '_drx_fabric_material', true);
    $model_specs  = get_post_meta($post->ID, '_drx_model_specs', true);
    $care_guide   = get_post_meta($post->ID, '_drx_care_guide', true);
    ?>
    <div class="drx-box-grid">
        <div class="drx-box-field full" style="background:#FFF7ED; padding:12px; border-radius:8px; border:1px solid #FFEDD5;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; color:#9A3412;">
                <input type="checkbox" name="drx_allow_custom_id" value="yes" <?php checked($allow_custom, 'yes'); ?> />
                <strong>★ Cho phép khách hàng nhập Tên / ID để in may thêu lên áo (Custom ID)</strong>
            </label>
            <p class="drx-box-desc" style="color:#C2410C; margin-left:24px;"><?php _e('Khi bật tùy chọn này, trang chi tiết và Quick View sẽ hiển thị ô nhập tên thêu riêng của khách hàng.', 'hello-elementor-child'); ?></p>
        </div>

        <div class="drx-box-field">
            <label for="drx_fabric_material"><?php _e('Chất liệu vải (Fabric Material):', 'hello-elementor-child'); ?></label>
            <input type="text" id="drx_fabric_material" name="drx_fabric_material" value="<?php echo esc_attr($fabric); ?>" placeholder="VD: 100% CoolMax Polyester kháng khuẩn, Cotton 250gsm..." />
            <p class="drx-box-desc"><?php _e('Thành phần cấu tạo và định lượng vải của trang phục.', 'hello-elementor-child'); ?></p>
        </div>

        <div class="drx-box-field">
            <label for="drx_model_specs"><?php _e('Thông số người mẫu mặc mẫu (Model Specs):', 'hello-elementor-child'); ?></label>
            <input type="text" id="drx_model_specs" name="drx_model_specs" value="<?php echo esc_attr($model_specs); ?>" placeholder="VD: Mẫu nam 1m78, 68kg - Đang mặc Size L" />
            <p class="drx-box-desc"><?php _e('Giúp người mua dễ dàng ướm kích cỡ áo phù hợp.', 'hello-elementor-child'); ?></p>
        </div>

        <div class="drx-box-field full">
            <label for="drx_care_guide"><?php _e('Hướng dẫn giặt ủi & bảo quản đồ thể thao (Care Instructions):', 'hello-elementor-child'); ?></label>
            <input type="text" id="drx_care_guide" name="drx_care_guide" value="<?php echo esc_attr($care_guide); ?>" placeholder="VD: Giặt máy nước lạnh dưới 30°C, không ủi trực tiếp lên logo may thêu..." />
            <p class="drx-box-desc"><?php _e('Lưu ý bảo quản giữ form áo bền đẹp.', 'hello-elementor-child'); ?></p>
        </div>
    </div>
    <?php
}


/* ==========================================================================
   4. LƯU DỮ LIỆU VỚI KIỂM TRA NONCE VÀ SANITIZATION
   ========================================================================== */

function drx_save_apparel_meta_boxes_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 4.1 Lưu Meta Box Lookbook
    if (isset($_POST['drx_lookbook_meta_nonce']) && wp_verify_nonce($_POST['drx_lookbook_meta_nonce'], 'drx_save_lookbook_meta_action')) {
        if (current_user_can('edit_post', $post_id)) {
            if (isset($_POST['drx_lookbook_theme'])) {
                update_post_meta($post_id, '_drx_lookbook_theme', sanitize_text_field($_POST['drx_lookbook_theme']));
            }
            if (isset($_POST['drx_collab_brand'])) {
                update_post_meta($post_id, '_drx_collab_brand', sanitize_text_field($_POST['drx_collab_brand']));
            }
            if (isset($_POST['drx_featured_product_id'])) {
                update_post_meta($post_id, '_drx_featured_product_id', intval($_POST['drx_featured_product_id']));
            }
            if (isset($_POST['drx_release_date'])) {
                update_post_meta($post_id, '_drx_release_date', sanitize_text_field($_POST['drx_release_date']));
            }
        }
    }

    // 4.2 Lưu Meta Box Sản phẩm Quần Áo
    if (isset($_POST['drx_product_apparel_nonce']) && wp_verify_nonce($_POST['drx_product_apparel_nonce'], 'drx_save_product_apparel_action')) {
        if (current_user_can('edit_post', $post_id)) {
            $allow_custom = isset($_POST['drx_allow_custom_id']) ? 'yes' : 'no';
            update_post_meta($post_id, '_drx_allow_custom_id', $allow_custom);

            if (isset($_POST['drx_fabric_material'])) {
                update_post_meta($post_id, '_drx_fabric_material', sanitize_text_field($_POST['drx_fabric_material']));
            }
            if (isset($_POST['drx_model_specs'])) {
                update_post_meta($post_id, '_drx_model_specs', sanitize_text_field($_POST['drx_model_specs']));
            }
            if (isset($_POST['drx_care_guide'])) {
                update_post_meta($post_id, '_drx_care_guide', sanitize_text_field($_POST['drx_care_guide']));
            }
        }
    }
}
add_action('save_post', 'drx_save_apparel_meta_boxes_data');
