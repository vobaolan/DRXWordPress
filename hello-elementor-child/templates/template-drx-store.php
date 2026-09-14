<?php
/**
 * Template Name: DRX Official Store
 * Template Post Type: page
 * 
 * Template trang chủ DRX Store - Chuẩn 100% theo D:\DRX\store.html
 */

get_header();

// Các hàm helper drx_store_get_image, drx_normalize_vnd_price, drx_format_price được định nghĩa chuẩn tại inc/woocommerce-custom.php

// Truy vấn sản phẩm WooCommerce
$args = array(
    'post_type'      => 'product',
    'posts_per_page' => 40,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC'
);
$products_query = new WP_Query($args);
$products_list = array();

if ($products_query->have_posts()) {
    while ($products_query->have_posts()) {
        $products_query->the_post();
        global $product;
        if (!$product) continue;

        $p_id = $product->get_id();
        $cats = wp_get_post_terms($p_id, 'product_cat', array('fields' => 'names'));
        $cat_slugs = wp_get_post_terms($p_id, 'product_cat', array('fields' => 'slugs'));
        
        $main_img = drx_store_get_image($product, false);
        $hover_img = drx_store_get_image($product, true);
        $raw_price = drx_normalize_vnd_price($product->get_price());

        // Bỏ qua các sản phẩm ghost/rác có giá 0đ hoặc không hợp lệ
        if ($raw_price <= 0) {
            continue;
        }

        $legacy_mock_names = array(
            'DRX 2026 VALORANT ROSTER KEYCHAIN SET',
            'DRX LOGO SNAPBACK CAP',
            'DRX X STEELSERIES GAMING MOUSEPAD',
            'DRX ROSTER ACRYLIC STAND',
            'DRX LIGHTSTICK V2 ESPORTS EDITION',
            'DRX PRO GAMING BACKPACK',
            'DRX ESPORTS BANDANA & WRISTBAND COMBO',
            'DRX WORLDS COMMEMORATIVE COIN SET',
            'DRX UNBREAKABLE DRAGON TUMBLER 750ML',
            'DRX X LOGITECH G PRO WIRELESS DRX EDITION',
            'DRX X LOGITECH MECHANICAL KEYBOARD',
            'DRX 2026 OFFICIAL HOODIE (BLACK)',
            'DRX 2026 OVERSIZED GRAPHIC TEE',
            'DRX CHAMPIONS BOMBER JACKET',
            'DRX X PUMA LIMITED EDITION JERSEY',
            'DRX CASUAL POLO SHIRT (NAVY)',
            'DRX X MONSTER ENERGY COLLAB TEE',
            '26 S2 AUTHENTIC WINDBREAKER JACKET',
            '26 S2 AUTHENTIC TRACK PANTS',
            '26 S2 AUTHENTIC T-SHIRT HOME (WHITE)',
            '26 S2 AUTHENTIC T-SHIRT AWAY (NAVY)',
            'DRX ESPORTS PRO ARM SLEEVE (PAIR)'
        );
        if (in_array(strtoupper(trim($product->get_name())), array_map('strtoupper', $legacy_mock_names))) {
            continue;
        }

        $products_list[] = array(
            'id'        => $p_id,
            'name'      => $product->get_name(),
            'price'     => $raw_price,
            'price_html'=> drx_format_price($raw_price),
            'img_main'  => $main_img,
            'img_hover' => $hover_img,
            'categories'=> implode(', ', $cats),
            'cat_slugs' => implode(' ', $cat_slugs),
        );
    }
    wp_reset_postdata();
}

$cart_count = class_exists('WooCommerce') ? WC()->cart->get_cart_contents_count() : 0;
$custom_logo_id = get_theme_mod('custom_logo');
$site_logo_url = $custom_logo_id ? wp_get_attachment_image_url($custom_logo_id, 'full') : 'https://teamdrx.vercel.app/thumbnail/20260727/aa447560a8495.png';
?>

<!-- DRX HEADER (D:\DRX\store.html) -->
<header class="drx-custom-header">
    <div class="logo">
        <a href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo esc_url($site_logo_url); ?>" alt="<?php bloginfo('name'); ?>">
        </a>
    </div>
    <div class="header-links">
        <a href="javascript:void(0)" onclick="openTrackOrder()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            <?php _e('Track Order', 'hello-elementor-child'); ?>
        </a>
        <a href="javascript:void(0)" onclick="openCart()">
            <?php _e('Cart', 'hello-elementor-child'); ?> (<span id="cartCount"><?php echo esc_html($cart_count); ?></span>)
        </a>
    </div>
</header>

<!-- TRENDING NOW SECTION: 100% FULL-WIDTH OUTSIDE MAIN (Giống hệt Hình 1) -->
<?php if (!empty($products_list)) : ?>
<div class="trending-section" id="trendingSection">
    <div class="trending-header">
        <h2 class="trending-title"><?php _e('Trending Now', 'hello-elementor-child'); ?></h2>
        <p class="trending-sub"><?php _e('Equip yourself with the most sought-after gear.', 'hello-elementor-child'); ?></p>
    </div>
    <div class="marquee-container" id="marqueeContainer">
        <div class="marquee-track" id="marqueeTrack">
            <?php 
            $marquee_items = array_slice($products_list, 0, 8);
            // Duplicate 2 lần để cuộn vô tận mượt mà
            for ($loop = 0; $loop < 2; $loop++) :
                foreach ($marquee_items as $m_item) : ?>
                    <div class="marquee-item" onclick="openProduct('<?php echo esc_js($m_item['id']); ?>')">
                        <img src="<?php echo esc_url($m_item['img_main']); ?>" alt="<?php echo esc_attr($m_item['name']); ?>" draggable="false">
                        <div class="marquee-info">
                            <div class="marquee-title"><?php echo esc_html($m_item['name']); ?></div>
                            <div class="marquee-price"><?php echo drx_format_price($m_item['price']); ?></div>
                        </div>
                    </div>
                <?php endforeach;
            endfor; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SHOP MAIN CONTAINER (D:\DRX\store.html) -->
<main class="shop-main-wrapper">
    <div class="shop-layout">
        <!-- SIDEBAR CATEGORY -->
        <aside class="shop-sidebar">
            <h2><?php _e('Store', 'hello-elementor-child'); ?></h2>
            <hr>
            <ul class="category-list">
                <li><a class="active" onclick="filterCat(null, this)"><?php _e('ALL', 'hello-elementor-child'); ?></a></li>
                <li><a onclick="filterCat('RELEASE', this)"><?php _e('RELEASE', 'hello-elementor-child'); ?></a></li>
                <li><a onclick="filterCat('UNIFORM', this)"><?php _e('UNIFORM', 'hello-elementor-child'); ?></a></li>
                <li><a onclick="filterCat('TEAM-KIT', this)"><?php _e('TEAM-KIT', 'hello-elementor-child'); ?></a></li>
                <li><a onclick="filterCat('COLABORATION', this)"><?php _e('COLABORATION', 'hello-elementor-child'); ?></a></li>
            </ul>
        </aside>
        
        <!-- SHOP CONTENT -->
        <section class="shop-content">
            <!-- SEARCH BAR -->
            <div class="store-search-container" style="margin-bottom: 28px; position: relative; width: 100%;">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 2;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="searchInput" class="store-search-input" placeholder="<?php esc_attr_e('Search products...', 'hello-elementor-child'); ?>" onkeyup="handleSearch(this.value)" autocomplete="off" style="width: 100%; padding: 14px 20px 14px 50px !important; border: 1.5px solid #E2E8F0; border-radius: 12px; font-size: 14.5px; font-family: var(--font-sans); outline: none; background: #FFFFFF; color: #0F172A; box-shadow: 0 2px 8px rgba(0,0,0,0.02); transition: all 0.25s ease;">
            </div>

            <!-- PRODUCT GRID -->
            <div class="product-grid" id="productGrid">
                <?php if (!empty($products_list)) : ?>
                    <?php foreach ($products_list as $prod) : ?>
                        <div class="product-card" onclick="openProduct('<?php echo esc_js($prod['id']); ?>')" data-cat="<?php echo esc_attr($prod['categories'] . ' ' . $prod['cat_slugs']); ?>" data-name="<?php echo esc_attr($prod['name']); ?>">
                            <div class="product-img-wrap <?php echo !empty($prod['img_hover']) ? 'has-hover-img' : ''; ?>">
                                <img src="<?php echo esc_url($prod['img_main']); ?>" class="product-img img-main" alt="<?php echo esc_attr($prod['name']); ?>">
                                <?php if (!empty($prod['img_hover'])) : ?>
                                    <img src="<?php echo esc_url($prod['img_hover']); ?>" class="product-img img-hover" alt="<?php echo esc_attr($prod['name']); ?> (Hover)">
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php echo esc_html($prod['name']); ?></h3>
                                <div class="product-price"><?php echo drx_format_price($prod['price']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">
                        <?php _e('No products found in the store.', 'hello-elementor-child'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<!-- FOOTER (D:\DRX\store.html) -->
<footer class="store-footer">
    <div class="footer-content">
        <div class="footer-brand">
            <img src="<?php echo esc_url($site_logo_url); ?>" alt="<?php bloginfo('name'); ?>" class="f-logo">
            <p>DRX Official Store. Equip yourself with the latest authentic gear and join the unbreakable legacy of our champions.</p>
        </div>
        <div class="footer-links">
            <h4>Customer Care</h4>
            <ul>
                <li><a href="javascript:void(0)" onclick="openTrackOrder()"><?php _e('Track Your Order', 'hello-elementor-child'); ?></a></li>
                <li><a href="javascript:void(0)" onclick="openContactModal()"><?php _e('Contact Us', 'hello-elementor-child'); ?></a></li>
                <li><a href="javascript:void(0)" onclick="openShippingModal()"><?php _e('Shipping Policy', 'hello-elementor-child'); ?></a></li>
                <li><a href="javascript:void(0)" onclick="openReturnsModal()"><?php _e('Returns & Exchanges', 'hello-elementor-child'); ?></a></li>
            </ul>
        </div>
        <div class="footer-social">
            <h4>Stay Connected</h4>
            <div class="social-icons">
                <a href="https://www.instagram.com/drxglobal/" target="_blank" aria-label="Instagram"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
                <a href="https://x.com/DRX_LCK" target="_blank" aria-label="Twitter/X"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l11.733 16h4.267l-11.733 -16z"></path><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"></path></svg></a>
                <a href="https://www.facebook.com/DRXGlobal/" target="_blank" aria-label="Facebook"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3.6l.4-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="copyright">&copy; <?php echo date('Y'); ?> DRX Official Store. All Rights Reserved.</div>
        <div class="legal-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </div>
</footer>

<!-- MODAL: VARIANT SELECTOR / QUICK VIEW -->
<div class="modal-overlay" id="variantModal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeVariantModal()">&times;</button>
        <div class="m-left">
            <img id="m_img" src="" style="width: 100%; height: 100%; max-height: 480px; object-fit: contain; margin-top: auto;">
            <div id="m_thumbnails" class="thumbnail-container" style="margin-bottom: 24px; margin-top: 16px;"></div>
        </div>
        <div class="m-right" id="m_right_content">
            <!-- Dynamic injected via AJAX -->
        </div>
    </div>
</div>

<!-- MODAL: TRACK ORDER (Tra cứu trạng thái đơn hàng) -->
<div class="modal-overlay" id="trackOrderModal">
    <div class="modal-content" style="max-width: 650px; flex-direction: column; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(16px); border: 1px solid rgba(0,0,0,0.08); border-radius: 20px; padding: 36px; box-shadow: 0 24px 48px rgba(0,0,0,0.15); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="font-family: var(--font-heading); font-size: 24px; font-weight: 800; margin: 0; color: var(--text-primary); letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; background: var(--accent); color: white; border-radius: 10px; box-shadow: 0 8px 16px rgba(0,82,255,0.25);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    </span>
                    <?php _e('Track Order', 'hello-elementor-child'); ?>
                </h2>
                <p style="font-size: 13.5px; color: var(--text-secondary); margin-top: 6px; font-weight: 500;"><?php _e('Tra cứu trạng thái và lộ trình giao hàng tức thì.', 'hello-elementor-child'); ?></p>
            </div>
            <button class="close-btn" onclick="closeTrackOrder()" style="position: static; font-size: 24px;">&times;</button>
        </div>
        
        <div id="trackFormArea">
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px;">
                    <?php _e('MÃ ĐƠN HÀNG (ORDER NUMBER)', 'hello-elementor-child'); ?> <span style="color:#EF4444;">*</span>
                </label>
                <div style="position: relative;">
                    <input type="text" id="trackOrderId" placeholder="Ví dụ: 143, #143 hoặc DRX-143" style="width: 100%; padding: 15px 16px 15px 44px; border: 2px solid #E2E8F0; border-radius: 12px; font-family: inherit; font-size: 15px; font-weight: 600; color: var(--text-primary); transition: all 0.3s; background: #fff; outline: none; box-sizing: border-box;">
                    <svg style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94A3B8;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px;">
                    <?php _e('SỐ ĐIỆN THOẠI HOẶC EMAIL', 'hello-elementor-child'); ?> <span style="font-weight:400; color:#94A3B8; text-transform:none;">(Tùy chọn đối chiếu)</span>
                </label>
                <div style="position: relative;">
                    <input type="text" id="trackPhone" placeholder="Ví dụ: 0987654321 hoặc volan258@gmail.com" style="width: 100%; padding: 15px 16px 15px 44px; border: 2px solid #E2E8F0; border-radius: 12px; font-family: inherit; font-size: 15px; font-weight: 500; color: var(--text-primary); transition: all 0.3s; background: #fff; outline: none; box-sizing: border-box;">
                    <svg style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94A3B8;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                </div>
            </div>
            
            <div id="trackErrorMsg" style="display: none; padding: 12px 16px; background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 10px; color: #EF4444; font-size: 13.5px; font-weight: 500; margin-bottom: 20px; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span id="trackErrorText"></span>
            </div>

            <button class="btn-buy-now" id="btnTrackOrder" onclick="executeTrackOrder()" style="width: 100%; margin: 0; justify-content: center; border-radius: 12px; padding: 16px; font-size: 14.5px; letter-spacing: 1px; background: #0052FF; color: white; border: none; cursor: pointer; font-weight: 800; box-shadow: 0 8px 20px rgba(0,82,255,0.3); transition: all 0.25s;">
                <?php _e('TRA CỨU ĐƠN HÀNG', 'hello-elementor-child'); ?>
            </button>
        </div>

        <div id="trackResultArea" style="display: none;">
            <div id="trackStatusCard" style="padding: 24px; background: #fff; border: 1.5px solid #E2E8F0; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <!-- Result injected dynamically here -->
            </div>
            <button onclick="resetTrackOrder()" style="width: 100%; padding: 14px; font-size: 13.5px; font-weight: 700; color: #0052FF; background: #F1F5F9; border: 1.5px solid #E2E8F0; border-radius: 12px; cursor: pointer; transition: all 0.2s;">
                <?php _e('Tra cứu đơn hàng khác', 'hello-elementor-child'); ?>
            </button>
        </div>
    </div>
</div>

<!-- MODAL: CONTACT US -->
<div class="modal-overlay" id="contactUsModal">
    <div class="modal-content" style="max-width: 600px; flex-direction: column; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(16px); border: 1px solid rgba(0,0,0,0.08); border-radius: 20px; padding: 36px; box-shadow: 0 24px 48px rgba(0,0,0,0.15); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="font-family: var(--font-heading); font-size: 24px; font-weight: 800; margin: 0; color: var(--text-primary); letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; background: #0052FF; color: white; border-radius: 10px; box-shadow: 0 8px 16px rgba(0,82,255,0.25);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    </span>
                    <?php _e('Contact DRX Support', 'hello-elementor-child'); ?>
                </h2>
                <p style="font-size: 13.5px; color: var(--text-secondary); margin-top: 6px; font-weight: 500;"><?php _e('Chúng tôi luôn sẵn sàng hỗ trợ bạn về sản phẩm và đơn hàng.', 'hello-elementor-child'); ?></p>
            </div>
            <button class="close-btn" onclick="closeContactModal()" style="position: static; font-size: 24px;">&times;</button>
        </div>

        <!-- Contact Channels Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px;">
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">HOTLINE HỖ TRỢ</div>
                <div style="font-weight: 800; font-size: 15px; color: #0052FF; margin-top: 4px;">0987 654 321</div>
                <div style="font-size: 11px; color: #10B981; margin-top: 2px;">● 8:30 - 22:00 hàng ngày</div>
            </div>
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">EMAIL CHÍNH THỨC</div>
                <div style="font-weight: 800; font-size: 14px; color: #0F172A; margin-top: 4px;">support@drxstyle.vn</div>
                <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Phản hồi trong 30 phút</div>
            </div>
        </div>

        <!-- Quick AI Assistance Button -->
        <div style="background: linear-gradient(135deg, #EFF6FF, #DBEAFE); border: 1px solid #BFDBFE; border-radius: 14px; padding: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; background: #0052FF; color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 12px;">AI</div>
                <div>
                    <div style="font-weight: 700; font-size: 13.5px; color: #1E3A8A;">Cần giải đáp thắc mắc ngay lập tức?</div>
                    <div style="font-size: 12px; color: #3B82F6;">Hỏi trực tiếp Trợ lý ảo DRX AI 24/7</div>
                </div>
            </div>
            <button onclick="closeContactModal(); toggleDrxChat();" style="padding: 8px 16px; background: #0052FF; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 12.5px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,82,255,0.25); white-space: nowrap;">
                Chat ngay
            </button>
        </div>

        <!-- Location & Showroom Info -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px; margin-bottom: 24px;">
            <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase;">SHOWROOM & ĐỊA CHỈ TRẢI NGHIỆM</div>
            <div style="font-weight: 700; font-size: 13.5px; color: #0F172A; margin-top: 4px;">DRX Official Store Hub - TP. Hồ Chí Minh</div>
            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Thời gian mở cửa: Thứ 2 - Chủ Nhật (08:30 - 22:00)</div>
        </div>

        <button onclick="closeContactModal()" style="width: 100%; padding: 14px; background: #F1F5F9; color: #0F172A; border: 1.5px solid #CBD5E1; border-radius: 12px; font-weight: 700; font-size: 13.5px; cursor: pointer; transition: all 0.2s;">
            ĐÃ HIỂU & ĐÓNG
        </button>
    </div>
</div>

<!-- MODAL: SHIPPING POLICY -->
<div class="modal-overlay" id="shippingPolicyModal">
    <div class="modal-content" style="max-width: 650px; flex-direction: column; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(16px); border: 1px solid rgba(0,0,0,0.08); border-radius: 20px; padding: 36px; box-shadow: 0 24px 48px rgba(0,0,0,0.15); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="font-family: var(--font-heading); font-size: 24px; font-weight: 800; margin: 0; color: var(--text-primary); letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; background: #0052FF; color: white; border-radius: 10px; box-shadow: 0 8px 16px rgba(0,82,255,0.25);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    </span>
                    <?php _e('Shipping Policy', 'hello-elementor-child'); ?>
                </h2>
                <p style="font-size: 13.5px; color: var(--text-secondary); margin-top: 6px; font-weight: 500;"><?php _e('Quy định biểu phí & thời gian giao nhận toàn quốc.', 'hello-elementor-child'); ?></p>
            </div>
            <button class="close-btn" onclick="closeShippingModal()" style="position: static; font-size: 24px;">&times;</button>
        </div>

        <!-- Biểu phí & Thời gian -->
        <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; overflow: hidden; margin-bottom: 20px;">
            <div style="padding: 14px 18px; background: #F1F5F9; font-weight: 800; font-size: 13px; color: #0F172A; text-transform: uppercase; letter-spacing: 0.5px;">
                BIỂU PHÍ & THỜI GIAN GIAO HÀNG
            </div>
            <div style="padding: 16px 18px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 700; font-size: 14px; color: #0F172A;">Khu vực Nội thành TP. Hồ Chí Minh</div>
                    <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Thời gian nhận hàng: <strong>1 - 2 ngày làm việc</strong></div>
                </div>
                <div style="font-weight: 800; font-size: 15px; color: #0052FF;">30.000 ₫</div>
            </div>
            <div style="padding: 16px 18px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-weight: 700; font-size: 14px; color: #0F172A;">Khu vực Toàn quốc (Hà Nội, Cần Thơ, Đà Nẵng,...)</div>
                    <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Thời gian nhận hàng: <strong>2 - 4 ngày làm việc</strong></div>
                </div>
                <div style="font-weight: 800; font-size: 15px; color: #0052FF;">50.000 ₫</div>
            </div>
            <div style="padding: 14px 18px; background: #ECFDF5; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-weight: 700; font-size: 13.5px; color: #065F46;">Đơn hàng từ 2.000.000 ₫ trở lên</div>
                <div style="font-weight: 900; font-size: 14px; color: #059669; text-transform: uppercase;">MIỄN PHÍ SHIP</div>
            </div>
        </div>

        <!-- 3 Cam kết cốt lõi -->
        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
            <div style="display: flex; gap: 12px; align-items: flex-start; padding: 12px 14px; background: #fff; border: 1px solid #E2E8F0; border-radius: 12px;">
                <div style="width: 28px; height: 28px; background: #10B981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0;">✓</div>
                <div style="font-size: 13px; color: #334155; line-height: 1.45;">
                    <strong style="color: #0F172A;">Hỗ trợ Đồng kiểm 100%:</strong> Quý khách được quyền mở hộp kiểm tra đúng mẫu mã, màu sắc, kích cỡ trước khi thanh toán tiền cho nhân viên giao hàng.
                </div>
            </div>
            <div style="display: flex; gap: 12px; align-items: flex-start; padding: 12px 14px; background: #fff; border: 1px solid #E2E8F0; border-radius: 12px;">
                <div style="width: 28px; height: 28px; background: #0052FF; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0;">★</div>
                <div style="font-size: 13px; color: #334155; line-height: 1.45;">
                    <strong style="color: #0F172A;">Đóng gói chuẩn DRX Esports:</strong> Mỗi chiếc áo đều được đặt trong túi zip niêm phong chống ẩm mốc và hộp carton chịu lực cao cấp chống biến dạng khi vận chuyển.
                </div>
            </div>
        </div>

        <button onclick="closeShippingModal()" style="width: 100%; padding: 14px; background: #F1F5F9; color: #0F172A; border: 1.5px solid #CBD5E1; border-radius: 12px; font-weight: 700; font-size: 13.5px; cursor: pointer; transition: all 0.2s;">
            ĐÃ HIỂU & ĐÓNG
        </button>
    </div>
</div>

<!-- MODAL: RETURNS & EXCHANGES -->
<div class="modal-overlay" id="returnsModal">
    <div class="modal-content" style="max-width: 650px; flex-direction: column; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(16px); border: 1px solid rgba(0,0,0,0.08); border-radius: 20px; padding: 36px; box-shadow: 0 24px 48px rgba(0,0,0,0.15); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="font-family: var(--font-heading); font-size: 24px; font-weight: 800; margin: 0; color: var(--text-primary); letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; background: #0052FF; color: white; border-radius: 10px; box-shadow: 0 8px 16px rgba(0,82,255,0.25);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 4v6h6"></path><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                    </span>
                    <?php _e('Returns & Exchanges', 'hello-elementor-child'); ?>
                </h2>
                <p style="font-size: 13.5px; color: var(--text-secondary); margin-top: 6px; font-weight: 500;"><?php _e('Chính sách đổi size & hoàn tiền uy tín trong vòng 7 ngày.', 'hello-elementor-child'); ?></p>
            </div>
            <button class="close-btn" onclick="closeReturnsModal()" style="position: static; font-size: 24px;">&times;</button>
        </div>

        <!-- 3 Bước đổi trả nhanh -->
        <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
            <div style="font-size: 12px; font-weight: 800; color: #64748B; text-transform: uppercase; margin-bottom: 14px; letter-spacing: 0.5px;">QUY TRÌNH ĐỔI TRẢ 3 BƯỚC ĐƠN GIẢN</div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; text-align: center;">
                <div style="background: #fff; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0;">
                    <div style="width: 28px; height: 28px; background: #0052FF; color: white; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">1</div>
                    <div style="font-weight: 700; font-size: 12.5px; color: #0F172A;">Liên hệ CSKH</div>
                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Qua Hotline hoặc Chat AI</div>
                </div>
                <div style="background: #fff; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0;">
                    <div style="width: 28px; height: 28px; background: #0052FF; color: white; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">2</div>
                    <div style="font-weight: 700; font-size: 12.5px; color: #0F172A;">Gửi sản phẩm</div>
                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Bưu tá đến nhận tận nhà</div>
                </div>
                <div style="background: #fff; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0;">
                    <div style="width: 28px; height: 28px; background: #10B981; color: white; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">3</div>
                    <div style="font-weight: 700; font-size: 12.5px; color: #0F172A;">Nhận hàng mới</div>
                    <div style="font-size: 11px; color: #64748B; margin-top: 2px;">Đổi size mới sau 2-3 ngày</div>
                </div>
            </div>
        </div>

        <!-- Điều kiện đổi trả -->
        <div style="font-size: 13px; color: #334155; line-height: 1.6; margin-bottom: 24px;">
            <div style="font-weight: 800; font-size: 13.5px; color: #0F172A; margin-bottom: 8px;">ĐIỀU KIỆN ÁP DỤNG:</div>
            <ul style="padding-left: 20px; margin: 0 0 14px 0;">
                <li>Thời hạn đổi trả trong vòng <strong>07 ngày</strong> kể từ ngày quý khách nhận được kiện hàng.</li>
                <li>Sản phẩm phải còn <strong>nguyên tem, nhãn mác, túi zip niêm phong</strong> và chưa qua giặt tẩy hay sử dụng.</li>
                <li>Hỗ trợ <strong>đổi size hoàn toàn miễn phí</strong>. Quý khách chỉ thanh toán cước phí gửi hàng 1 chiều.</li>
                <li>Hoàn tiền 100% đối với trường hợp hàng bị lỗi may mặc hoặc gửi nhầm mẫu mã.</li>
            </ul>
        </div>

        <button onclick="closeReturnsModal()" style="width: 100%; padding: 14px; background: #0052FF; color: white; border: none; border-radius: 12px; font-weight: 800; font-size: 13.5px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,82,255,0.25);">
            ĐÃ HIỂU & ĐÓNG
        </button>
    </div>
</div>

<!-- CART SIDEBAR DRAWER (D:\DRX\store.html) -->
<div class="cart-sidebar-overlay" id="cartOverlay" onclick="closeCart()"></div>
<div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
        <h3><?php _e('Cart', 'hello-elementor-child'); ?></h3>
        <button class="cart-close" onclick="closeCart()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
    <div class="cart-body" id="cartBody">
        <?php
        if (class_exists('WooCommerce') && WC()->cart && !WC()->cart->is_empty()) :
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                $_prod = isset($cart_item['data']) ? $cart_item['data'] : null;
                if (!$_prod || !$_prod->exists() || empty($cart_item['quantity'])) continue;

                $img = wp_get_attachment_image_url($_prod->get_image_id(), 'thumbnail');
                if (!$img) {
                    $img = drx_store_get_image($_prod, false);
                }
                $item_size = isset($cart_item['drx_size']) ? $cart_item['drx_size'] : (isset($cart_item['variation']['attribute_pa_size']) ? $cart_item['variation']['attribute_pa_size'] : '');
                $custom_id = isset($cart_item['drx_custom_id']) ? $cart_item['drx_custom_id'] : '';
                ?>
                <div class="cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>">
                    <img src="<?php echo esc_url($img ?: wc_placeholder_img_src()); ?>" class="cart-item-img" alt="<?php echo esc_attr($_prod->get_name()); ?>">
                    <div class="cart-item-details">
                        <div class="cart-item-title"><?php echo esc_html($_prod->get_name()); ?></div>
                        <?php if (!empty($item_size)) : ?>
                            <div class="cart-item-meta" style="color: #475569; font-size: 12px; font-weight: 600; margin-top: 2px;">Size: <?php echo esc_html($item_size); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($custom_id)) : ?>
                            <div class="cart-item-meta" style="color: var(--accent); font-size: 12px; font-weight: 600; margin-top: 2px;">Custom ID: <?php echo esc_html($custom_id); ?></div>
                        <?php endif; ?>
                        <div class="cart-item-bottom">
                            <span style="font-size: 13px; color: var(--text-muted);">Qty: <?php echo esc_html($cart_item['quantity']); ?></span>
                            <span style="font-weight: 700; color: var(--accent); font-size: 14px;"><?php echo WC()->cart->get_product_subtotal($_prod, $cart_item['quantity']); ?></span>
                        </div>
                    </div>
                    <button class="cart-item-remove" onclick="removeCartItem('<?php echo esc_js($cart_item_key); ?>')" title="Xóa sản phẩm" style="background:none; border:none; color:#94A3B8; cursor:pointer; font-size:20px; line-height:1; padding:4px 8px; border-radius:4px; transition:color 0.2s;">&times;</button>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="cart-empty" style="text-align:center; padding: 40px; color: var(--text-muted);"><?php _e('Your cart is currently empty.', 'hello-elementor-child'); ?></div>
        <?php endif; ?>
    </div>
    <div class="cart-footer">
        <div class="cart-total-row">
            <span><?php _e('Total:', 'hello-elementor-child'); ?></span>
            <span id="cartTotal"><?php echo (class_exists('WooCommerce') && WC()->cart) ? WC()->cart->get_cart_total() : '0 ₫'; ?></span>
        </div>
        <a href="<?php echo esc_url(drx_get_safe_checkout_url()); ?>" class="btn-primary-checkout"><?php _e('PROCEED TO CHECKOUT', 'hello-elementor-child'); ?></a>
    </div>
</div>

<!-- DRX NATIVE AI CHATBOT WIDGET (GIAO DIỆN TRẮNG - XANH ELECTRIC SIÊU ĐẸP) -->
<div id="drxChatWidgetWrap" style="position: fixed; bottom: 24px; right: 24px; z-index: 99990; font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', Roboto, sans-serif;">
    <!-- Nút tròn mở Chatbot -->
    <button id="drxChatToggleBtn" onclick="toggleDrxChat()" style="display: flex; align-items: center; gap: 10px; padding: 12px 20px; background: #0052FF; color: #ffffff; border: none; border-radius: 50px; cursor: pointer; box-shadow: 0 10px 25px rgba(0,82,255,0.35); font-size: 14px; font-weight: 700; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); outline: none;">
        <span style="display: flex; align-items: center; justify-content: center; width: 26px; height: 26px; background: rgba(255,255,255,0.2); border-radius: 50%;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </span>
        <span>Chat với DRX AI</span>
    </button>

    <!-- Khung cửa sổ Chatbot (Tone Trắng - Xanh DRX) -->
    <div id="drxChatBox" style="display: none; position: fixed; bottom: 85px; right: 24px; width: 400px; height: 580px; max-width: calc(100vw - 32px); max-height: calc(100vh - 110px); background: #ffffff; border-radius: 20px; box-shadow: 0 20px 50px rgba(15,23,42,0.15), 0 0 0 1px #E2E8F0; overflow: hidden; flex-direction: column; animation: drxPopUp 0.3s ease;">
        <!-- Header khung chat (Trắng viền Xanh) -->
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #ffffff; border-bottom: 1px solid #EEF2F6;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="position: relative; width: 38px; height: 38px; background: #0052FF; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 13px; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(0,82,255,0.25);">
                    DRX
                    <span style="position: absolute; bottom: -2px; right: -2px; width: 10px; height: 10px; background: #10B981; border: 2px solid #ffffff; border-radius: 50%;"></span>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 15px; color: #0F172A; display: flex; align-items: center; gap: 6px;">
                        DRX AI Assistant
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#0052FF"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    </div>
                    <div style="font-size: 11px; color: #10B981; font-weight: 600;">Sẵn sàng hỗ trợ 24/7</div>
                </div>
            </div>
            <button onclick="toggleDrxChat()" style="background: #F1F5F9; border: none; color: #64748B; width: 30px; height: 30px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">&times;</button>
        </div>

        <!-- Chat Body (Khu vực tin nhắn) -->
        <div id="drxChatMessages" style="flex: 1; padding: 18px 16px; overflow-y: auto; background: #F8FAFC; display: flex; flex-direction: column; gap: 14px;">
            <!-- Tin nhắn chào mừng -->
            <div class="drx-bot-msg" style="display: flex; gap: 10px; align-items: flex-start;">
                <div style="width: 28px; height: 28px; background: #0052FF; border-radius: 8px; color: white; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">AI</div>
                <div style="background: #ffffff; padding: 12px 16px; border-radius: 4px 16px 16px 16px; border: 1px solid #E2E8F0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); font-size: 13.5px; line-height: 1.5; color: #1E293B;">
                    Chào anh/chị! Em là <b>Trợ lý AI của DRX Store</b>. Em có thể hỗ trợ anh/chị tư vấn bảng size, so sánh áo đấu, thông tin vận chuyển và chính sách đổi trả ạ! 💙
                </div>
            </div>

            <!-- Gợi ý câu hỏi nhanh -->
            <div id="drxQuickPrompts" style="display: flex; flex-wrap: wrap; gap: 6px; margin-left: 38px;">
                <button type="button" onclick="sendQuickPrompt('Áo T-Shirt và Áo khoác Windbreaker khác nhau thế nào, cái nào hợp đi học hơn?')" style="background: #ffffff; border: 1px solid #CBD5E1; color: #0052FF; padding: 6px 12px; border-radius: 14px; font-size: 11.5px; font-weight: 600; cursor: pointer; transition: all 0.2s;">🔍 So sánh áo T-Shirt & Áo khoác</button>
                <button type="button" onclick="sendQuickPrompt('Nếu mua về mặc không vừa size thì đổi trả trong bao lâu, có mất phí không?')" style="background: #ffffff; border: 1px solid #CBD5E1; color: #0052FF; padding: 6px 12px; border-radius: 14px; font-size: 11.5px; font-weight: 600; cursor: pointer; transition: all 0.2s;">🔄 Chính sách đổi trả</button>
                <button type="button" onclick="sendQuickPrompt('Mình ở Cần Thơ đặt hôm nay thì bao giờ nhận được, phí ship bao nhiêu và có được kiểm tra hàng không?')" style="background: #ffffff; border: 1px solid #CBD5E1; color: #0052FF; padding: 6px 12px; border-radius: 14px; font-size: 11.5px; font-weight: 600; cursor: pointer; transition: all 0.2s;">🚚 Phí ship & Giao hàng</button>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="drxTypingIndicator" style="display: none; padding: 6px 20px 6px 54px; background: #F8FAFC; font-size: 11px; color: #64748B; font-style: italic;">
            DRX AI đang soạn câu trả lời...
        </div>

        <!-- Input Area (Khu vực nhập tin nhắn) -->
        <div style="padding: 12px 16px; background: #ffffff; border-top: 1px solid #EEF2F6; display: flex; align-items: center; gap: 10px;">
            <input type="text" id="drxChatInput" placeholder="Nhập câu hỏi cho DRX AI..." onkeypress="handleDrxChatKey(event)" style="flex: 1; padding: 12px 16px; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 13.5px; outline: none; background: #F8FAFC; color: #0F172A; transition: border 0.2s;" onfocus="this.style.borderColor='#0052FF'; this.style.background='#fff';" onblur="this.style.borderColor='#E2E8F0'; this.style.background='#F8FAFC';">
            <button id="drxSendBtn" onclick="handleDrxSend()" style="width: 42px; height: 42px; background: #0052FF; color: white; border: none; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,82,255,0.25);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
window.DRX_STORE_CATALOG = <?php echo json_encode(array_values($products_list)); ?>;

function openContactModal() {
    var modal = document.getElementById('contactUsModal');
    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
    }
}
function closeContactModal() {
    var modal = document.getElementById('contactUsModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        var msg = document.getElementById('contactSuccessMsg');
        if (msg) msg.style.display = 'none';
    }
}

function openShippingModal() {
    var modal = document.getElementById('shippingPolicyModal');
    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
    }
}
function closeShippingModal() {
    var modal = document.getElementById('shippingPolicyModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}

function openReturnsModal() {
    var modal = document.getElementById('returnsModal');
    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
    }
}
function closeReturnsModal() {
    var modal = document.getElementById('returnsModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}


// Click outside overlay to close modal
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList && e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        e.target.style.display = 'none';
    }
});

function toggleDrxChat() {
    var box = document.getElementById('drxChatBox');
    if (box.style.display === 'none' || box.style.display === '') {
        box.style.display = 'flex';
        document.getElementById('drxChatInput').focus();
    } else {
        box.style.display = 'none';
    }
}

function handleDrxChatKey(e) {
    if (e.key === 'Enter') {
        handleDrxSend();
    }
}

function sendQuickPrompt(txt) {
    document.getElementById('drxChatInput').value = txt;
    handleDrxSend();
}

function handleDrxSend() {
    var input = document.getElementById('drxChatInput');
    var query = input.value.trim();
    if (!query) return;

    // 1. Thêm tin nhắn của User
    appendDrxUserMsg(query);
    input.value = '';
    
    // 2. Hiện typing indicator
    var typing = document.getElementById('drxTypingIndicator');
    typing.style.display = 'block';
    scrollDrxChat();

    // 3. Xử lý phản hồi AI theo dữ liệu RAG chuẩn DRX Store
    setTimeout(function() {
        var reply = getDrxAiResponse(query);
        typing.style.display = 'none';
        appendDrxBotMsg(reply);
        scrollDrxChat();
    }, 500);
}

function appendDrxUserMsg(txt) {
    var area = document.getElementById('drxChatMessages');
    var div = document.createElement('div');
    div.style.cssText = "display: flex; justify-content: flex-end;";
    div.innerHTML = '<div style="background: #0052FF; color: white; padding: 11px 16px; border-radius: 16px 16px 4px 16px; max-width: 80%; font-size: 13.5px; line-height: 1.45; box-shadow: 0 4px 12px rgba(0,82,255,0.2);">' + escapeHtml(txt) + '</div>';
    area.appendChild(div);
}

function appendDrxBotMsg(txt) {
    var area = document.getElementById('drxChatMessages');
    var div = document.createElement('div');
    div.className = 'drx-bot-msg';
    div.style.cssText = "display: flex; gap: 10px; align-items: flex-start;";
    div.innerHTML = '<div style="width: 28px; height: 28px; background: #0052FF; border-radius: 8px; color: white; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">AI</div>' +
                    '<div style="background: #ffffff; padding: 12px 16px; border-radius: 4px 16px 16px 16px; border: 1px solid #E2E8F0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); font-size: 13.5px; line-height: 1.5; color: #1E293B; max-width: 84%;">' + txt + '</div>';
    area.appendChild(div);
}

function scrollDrxChat() {
    var area = document.getElementById('drxChatMessages');
    area.scrollTop = area.scrollHeight;
}

function escapeHtml(text) {
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function getDrxAiResponse(q) {
    var s = q.toLowerCase().trim();

    // 1. Thanh toán 50% / Cọc / Trả trước / Trả nốt (Ưu tiên)
    if (s.includes('50%') || s.includes('cọc') || s.includes('trả nốt') || s.includes('trả trước') || s.includes('thanh toán trước') || (s.includes('thanh toán') && (s.includes('50') || s.includes('trước') || s.includes('nốt')))) {
        return "Dạ hiện tại DRX Store áp dụng 2 hình thức thanh toán chính thức: <b>Thanh toán khi nhận hàng (COD)</b> hoặc <b>Chuyển khoản ngân hàng 100%</b> khi đặt hàng.<br><br>Shop <b>chưa hỗ trợ hình thức đặt cọc hoặc trả trước 50%</b> nhận hàng trả nốt, mong anh/chị thông cảm giúp shop nhé ạ!";
    }

    // 2. Câu hỏi lạc đề
    if (s.includes('thời tiết') || s.includes('mưa') || s.includes('nắng') || s.includes('chính trị') || s.includes('viết bài') || s.includes('làm văn')) {
        return "Dạ câu hỏi này ngoài phạm vi tư vấn của DRX Store rồi ạ. Em xin phép được hỗ trợ anh/chị về các mẫu áo đấu chính hãng, bảng size, phụ kiện và chính sách mua sắm của <b>DRX Store</b> nhé ạ! 😊";
    }

    // 3. Tra cứu sản phẩm trong Catalog của Store (DYNAMIC PRODUCT SEARCH & RAG)
    var catalog = window.DRX_STORE_CATALOG || [];
    var matchedProducts = [];

    // Tách các từ khóa tìm kiếm
    var cleanQuery = s.replace(/áo|quần|nón|bình|lót chuột|pad|chuột|bàn phím|giá|bao nhiêu|bán|tiền|mua|ở đâu|còn không|size|màu|đang|cho mình hỏi|shop ơi|shop/g, ' ').trim();
    var queryTokens = cleanQuery.split(/\s+/).filter(function(t) { return t.length >= 2; });

    catalog.forEach(function(item) {
        var nameLower = item.name.toLowerCase();
        var catLower = (item.categories || '').toLowerCase();
        var score = 0;

        // Trùng cả cụm tên
        if (s.includes(nameLower) || nameLower.includes(cleanQuery)) {
            score += 10;
        }

        // So khớp từng từ khóa
        queryTokens.forEach(function(token) {
            if (nameLower.includes(token)) score += 3;
            if (catLower.includes(token)) score += 1;
        });

        // Đặc thù các mã sản phẩm HOT (pink, 3rd, lilka, baseball, marking, gymsack, bracelet, sleeve, capsule, towel, bandana, card holder, mousepad, ticketholder, strap, prx, jeogori, photocard, away, home, jumper, t-shirt)
        var specialKeywords = ['pink', '3rd', 'lilka', 'baseball', 'marking', 'gymsack', 'bracelet', 'sleeve', 'capsule', 'towel', 'bandana', 'magsafe', 'mousepad', 'ticketholder', 'strap', 'badge', 'tote', 'jeogori', 'photocard', 'rugby', 'jumper', 'pants', 't-shirt'];
        specialKeywords.forEach(function(kw) {
            if (s.includes(kw) && nameLower.includes(kw)) {
                score += 5;
            }
        });

        if (score > 0) {
            matchedProducts.push({ item: item, score: score });
        }
    });

    // Sắp xếp theo độ phù hợp cao nhất
    matchedProducts.sort(function(a, b) { return b.score - a.score; });

    // Nếu tìm thấy sản phẩm trùng khớp
    if (matchedProducts.length > 0) {
        if (matchedProducts.length === 1 || matchedProducts[0].score >= 8) {
            var p = matchedProducts[0].item;
            return `Dạ sản phẩm <b>${p.name}</b> chính hãng hiện đang được bán tại DRX Store với giá niêm yết là <b style="color:#0052FF; font-size:15px;">${p.price_html}</b> ạ! 💙<br><br>` +
                   `<div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; padding:12px; display:flex; gap:12px; align-items:center; margin:8px 0;">` +
                   `<img src="${p.img_main}" alt="${p.name}" style="width:60px; height:60px; object-fit:contain; border-radius:8px; background:#fff; border:1px solid #E2E8F0;">` +
                   `<div style="flex:1;">` +
                   `<div style="font-weight:700; font-size:13px; color:#0F172A;">${p.name}</div>` +
                   `<div style="font-weight:800; font-size:14px; color:#0052FF; margin-top:2px;">${p.price_html}</div>` +
                   `<div style="font-size:11px; color:#10B981; font-weight:600; margin-top:2px;">● Đang có sẵn hàng (Full size)</div>` +
                   `</div>` +
                   `</div>` +
                   `<button onclick="openProduct(${p.id}); toggleDrxChat();" style="display:inline-block; width:100%; margin-top:6px; padding:10px 14px; background:#0052FF; color:white; border:none; border-radius:8px; font-weight:700; font-size:12.5px; cursor:pointer; text-align:center; box-shadow:0 4px 12px rgba(0,82,255,0.25);">👉 Xem chi tiết & Đặt mua ngay</button>`;
        } else {
            // Hiển thị danh sách các sản phẩm liên quan khớp từ khóa
            var topMatches = matchedProducts.slice(0, 3);
            var listHtml = topMatches.map(function(m) {
                var p = m.item;
                return `<div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:10px; display:flex; gap:10px; align-items:center; margin-bottom:8px;">` +
                       `<img src="${p.img_main}" alt="${p.name}" style="width:50px; height:50px; object-fit:contain; border-radius:6px; background:#fff;">` +
                       `<div style="flex:1; min-width:0;">` +
                       `<div style="font-weight:700; font-size:12.5px; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${p.name}</div>` +
                       `<div style="font-weight:800; font-size:13px; color:#0052FF;">${p.price_html}</div>` +
                       `</div>` +
                       `<button onclick="openProduct(${p.id}); toggleDrxChat();" style="padding:6px 10px; background:#0052FF; color:white; border:none; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer; white-space:nowrap;">Xem</button>` +
                       `</div>`;
            }).join('');

            return `Dạ shop tìm thấy các mẫu sản phẩm liên quan đến câu hỏi của anh/chị như sau ạ: 💙<br><br>${listHtml}`;
        }
    }

    // 4. Tư vấn chọn size
    if (s.includes('size') || s.includes('chọn size') || s.includes('bảng size') || s.includes('chiều cao') || s.includes('cân nặng') || s.includes('kg') || s.includes('m7') || s.includes('m6') || s.includes('m8')) {
        return "Dạ bảng size áo đấu DRX chính hãng chuẩn form thể thao châu Á như sau ạ:<br><br>" +
               "• <b>Size S</b>: 1m55 - 1m65 (48kg - 56kg)<br>" +
               "• <b>Size M</b>: 1m65 - 1m72 (57kg - 65kg)<br>" +
               "• <b>Size L</b>: 1m72 - 1m78 (66kg - 75kg)<br>" +
               "• <b>Size XL</b>: 1m78 - 1m85 (76kg - 85kg)<br>" +
               "• <b>Size 2XL</b>: Trên 1m85 hoặc trên 85kg<br><br>" +
               "Nếu anh/chị muốn mặc rộng rãi (oversized style) có thể tăng lên 1 size nhé ạ!";
    }

    // 5. Chính sách đổi trả
    if (s.includes('đổi') || s.includes('hoàn tiền') || s.includes('không vừa') || s.includes('rộng') || s.includes('chật') || s.includes('đổi trả')) {
        return "Dạ shop hỗ trợ đổi size hoặc hoàn tiền trong vòng <b>7 ngày</b> kể từ khi nhận hàng ạ. Điều kiện: sản phẩm còn nguyên tem mác, chưa qua giặt tẩy hay sử dụng. Đổi size hoàn toàn miễn phí, anh/chị chỉ cần thanh toán phí ship chiều gửi về thôi ạ!";
    }

    // 6. Phí ship & Vận chuyển
    if (s.includes('ship') || s.includes('vận chuyển') || s.includes('giao hàng') || s.includes('bao lâu') || s.includes('kiểm tra') || s.includes('đồng kiểm') || s.includes('nhận được') || s.includes('cần thơ') || s.includes('hà nội')) {
        return "Dạ phí vận chuyển của DRX Store như sau ạ:<br><br>" +
               "• <b>Nội thành TP.HCM</b>: 30.000₫ (1-2 ngày nhận hàng)<br>" +
               "• <b>Toàn quốc (Hà Nội, Cần Thơ, Đà Nẵng,...)</b>: 50.000₫ (2-4 ngày nhận hàng)<br><br>" +
               "Đặc biệt, shop luôn hỗ trợ <b>đồng kiểm (kiểm tra hàng trước khi thanh toán)</b> nên anh/chị hoàn toàn yên tâm nhé ạ!";
    }

    // 7. Thêu / In tên Custom ID
    if (s.includes('in tên') || s.includes('thêu') || s.includes('custom') || s.includes('deft') || s.includes('faker') || s.includes('chovy')) {
        return "Dạ dịch vụ thêu/in tên tuyển thủ hoặc tên cá nhân lên áo đấu DRX là <b>hoàn toàn MIỄN PHÍ</b> ạ! Khi chọn áo trên web, anh/chị chỉ cần nhập tên vào ô <i>'Tên / ID thêu áo'</i> là shop sẽ in thêu sắc nét trước khi đóng gói gửi đi nhé!";
    }

    // 8. Tra cứu đơn hàng
    if (s.includes('tra cứu') || s.includes('track') || s.includes('mã đơn') || s.includes('đơn hàng của tôi')) {
        return "Dạ anh/chị có thể tự tra cứu đơn hàng trực tiếp bằng cách bấm vào nút <b>'Track Order'</b> trên thanh menu đầu trang, sau đó nhập Mã đơn hàng (ví dụ: #143) để xem tiến trình giao hàng ngay lập tức nhé ạ!";
    }

    // 9. Phương thức thanh toán
    if (s.includes('thanh toán') || s.includes('chuyển khoản') || s.includes('cod')) {
        return "Dạ DRX Store hỗ trợ 2 hình thức thanh toán chính thức: <b>Thanh toán khi nhận hàng (COD)</b> hoặc <b>Chuyển khoản ngân hàng 100%</b> khi đặt hàng trên website ạ!";
    }

    // 10. Danh mục / Tất cả sản phẩm
    if (s.includes('sản phẩm') || s.includes('danh mục') || s.includes('có gì') || s.includes('bán gì') || s.includes('áo') || s.includes('phụ kiện')) {
        return "Dạ hiện tại DRX Store có đầy đủ 4 bộ sưu tập chính hãng (24 sản phẩm) chuẩn 100% từ DRX Global:<br><br>" +
               "1. <b>RELEASE</b>: Áo Baseball Uniform, Túi Gymsack, Vòng tay Logo, Capsule,...<br>" +
               "2. <b>UNIFORM</b>: Áo đấu sân nhà/sân khách 2026, Áo T-Shirt 3RD Pink, Áo khoác Jumper, Quần Track Pants,...<br>" +
               "3. <b>TEAM-KIT</b>: Khăn Beach Towel, Pad chuột Gaming, Bandana, MagSafe Card Holder, Dây đeo Strap,...<br>" +
               "4. <b>COLABORATION</b>: Bộ sưu tập giới hạn DRX x PRX, DRX x Lilka Rugby Jersey, Talon Photocard,...<br><br>" +
               "Anh/chị muốn tìm hiểu chi tiết mẫu nào có thể nhắn tên sản phẩm để em báo giá và tư vấn size ngay nhé ạ!";
    }

    // Mặc định
    return "Dạ em đã ghi nhận câu hỏi của anh/chị. Anh/chị có thể nhập tên sản phẩm (ví dụ: <i>'áo 26 S1 Authentic T-Shirt 3RD Pink'</i>, <i>'áo Baseball'</i>, <i>'pad chuột'</i>) để em báo giá và hỗ trợ tư vấn ngay nhé ạ! 💙";
}
</script>

<style>
@keyframes drxPopUp {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
#drxChatToggleBtn:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 14px 30px rgba(0,82,255,0.45);
}
#drxQuickPrompts button:hover {
    background: #EEF2FF !important;
    border-color: #0052FF !important;
}
</style>

<?php
get_footer();

