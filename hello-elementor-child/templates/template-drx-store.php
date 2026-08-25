<?php
/**
 * Template Name: DRX Official Store
 * Template Post Type: page
 * 
 * Template trang chủ DRX Store tích hợp WooCommerce, Marquee và Quick View Modal
 */

get_header();

// Lấy danh sách sản phẩm WooCommerce
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
        $main_img = wp_get_attachment_image_url($product->get_image_id(), 'medium_large') ?: wc_placeholder_img_src();
        
        $gallery_ids = $product->get_gallery_image_ids();
        $hover_img = !empty($gallery_ids) ? wp_get_attachment_image_url($gallery_ids[0], 'medium_large') : '';

        $products_list[] = array(
            'id'        => $p_id,
            'name'      => $product->get_name(),
            'price'     => (float)$product->get_price(),
            'price_html'=> $product->get_price_html(),
            'img_main'  => $main_img,
            'img_hover' => $hover_img,
            'categories'=> implode(', ', $cats),
            'cat_slugs' => implode(' ', $cat_slugs),
        );
    }
    wp_reset_postdata();
}

$cart_count = class_exists('WooCommerce') ? WC()->cart->get_cart_contents_count() : 0;
?>

<!-- DRX STORE HEADER -->
<header class="drx-header">
    <div class="drx-logo">
        <a href="<?php echo esc_url(home_url('/')); ?>">
            <img src="https://teamdrx.vercel.app/thumbnail/20260727/aa447560a8495.png" alt="DRX Official Store">
        </a>
    </div>
    <div class="drx-header-links">
        <a href="javascript:void(0)" class="drx-open-track-trigger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            <?php _e('Track Order', 'hello-elementor-child'); ?>
        </a>
        <a href="javascript:void(0)" class="drx-open-cart-trigger">
            <?php _e('Cart', 'hello-elementor-child'); ?> 
            (<span class="drx-cart-count"><?php echo esc_html($cart_count); ?></span>)
        </a>
    </div>
</header>

<main>
    <!-- TRENDING NOW MARQUEE -->
    <?php if (!empty($products_list)) : ?>
    <section class="trending-section">
        <div class="trending-header">
            <h2 class="trending-title"><?php _e('Trending Now', 'hello-elementor-child'); ?></h2>
            <p class="trending-sub"><?php _e('Equip yourself with the most sought-after gear.', 'hello-elementor-child'); ?></p>
        </div>
        <div class="marquee-container" id="drxMarqueeContainer">
            <div class="marquee-track" id="drxMarqueeTrack">
                <?php 
                $marquee_items = array_slice($products_list, 0, 8);
                // Lặp 2 lần để tạo hiệu ứng cuộn vô tận (Seamless Infinite Scroll)
                for ($loop = 0; $loop < 2; $loop++) : 
                    foreach ($marquee_items as $m_item) : ?>
                        <div class="marquee-item drx-open-modal" data-product-id="<?php echo esc_attr($m_item['id']); ?>">
                            <img src="<?php echo esc_url($m_item['img_main']); ?>" alt="<?php echo esc_attr($m_item['name']); ?>" draggable="false">
                            <div class="marquee-info">
                                <div class="marquee-title"><?php echo esc_html($m_item['name']); ?></div>
                                <div class="marquee-price">USD <?php echo number_format($m_item['price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach;
                endfor; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SHOP LAYOUT -->
    <div class="drx-shop-layout">
        <!-- SIDEBAR -->
        <aside class="shop-sidebar">
            <h2><?php _e('Store', 'hello-elementor-child'); ?></h2>
            <hr>
            <ul class="category-list">
                <li><a href="#" class="active" data-cat="ALL"><?php _e('ALL', 'hello-elementor-child'); ?></a></li>
                <li><a href="#" data-cat="RELEASE"><?php _e('RELEASE', 'hello-elementor-child'); ?></a></li>
                <li><a href="#" data-cat="UNIFORM"><?php _e('UNIFORM', 'hello-elementor-child'); ?></a></li>
                <li><a href="#" data-cat="TEAM-KIT"><?php _e('TEAM-KIT', 'hello-elementor-child'); ?></a></li>
                <li><a href="#" data-cat="COLABORATION"><?php _e('COLABORATION', 'hello-elementor-child'); ?></a></li>
            </ul>
        </aside>

        <!-- PRODUCT CONTENT -->
        <section class="shop-content">
            <!-- SEARCH BAR -->
            <div class="store-search-container">
                <input type="text" id="drxSearchInput" class="store-search-input" placeholder="<?php esc_attr_e('Search products...', 'hello-elementor-child'); ?>" autocomplete="off">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>

            <!-- PRODUCT GRID -->
            <div class="product-grid" id="drxProductGrid">
                <?php if (!empty($products_list)) : ?>
                    <?php foreach ($products_list as $prod) : ?>
                        <div class="product-card" data-product-id="<?php echo esc_attr($prod['id']); ?>" data-cat="<?php echo esc_attr($prod['categories'] . ' ' . $prod['cat_slugs']); ?>">
                            <div class="product-img-wrap">
                                <img src="<?php echo esc_url($prod['img_main']); ?>" class="product-img img-main" alt="<?php echo esc_attr($prod['name']); ?>">
                                <?php if (!empty($prod['img_hover'])) : ?>
                                    <img src="<?php echo esc_url($prod['img_hover']); ?>" class="product-img img-hover" alt="<?php echo esc_attr($prod['name']); ?> (Hover)">
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-cat"><?php echo esc_html($prod['categories'] ?: 'DRX GEAR'); ?></div>
                                <h3 class="product-title"><?php echo esc_html($prod['name']); ?></h3>
                                <div class="product-price">USD <?php echo number_format($prod['price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--text-muted);">
                        <p><?php _e('Chưa có sản phẩm nào. Hãy nhập sản phẩm từ file CSV hoặc thêm sản phẩm trong WooCommerce.', 'hello-elementor-child'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<!-- FOOTER -->
<footer class="store-footer">
    <div class="footer-content">
        <div class="footer-brand">
            <img src="https://teamdrx.vercel.app/thumbnail/20260727/aa447560a8495.png" alt="DRX Logo" class="f-logo">
            <p>DRX Official Store. Equip yourself with the latest authentic gear and join the unbreakable legacy of our champions.</p>
        </div>
        <div class="footer-links">
            <h4>Customer Care</h4>
            <ul>
                <li><a href="javascript:void(0)" class="drx-open-track-trigger">Track Your Order</a></li>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Shipping Policy</a></li>
                <li><a href="#">Returns & Exchanges</a></li>
            </ul>
        </div>
        <div class="footer-social">
            <h4>Stay Connected</h4>
            <div style="display: flex; gap: 12px;">
                <a href="https://www.instagram.com/drxglobal/" target="_blank" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary);">IG</a>
                <a href="https://x.com/DRX_LCK" target="_blank" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary);">X</a>
                <a href="https://www.facebook.com/DRXGlobal/" target="_blank" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary);">FB</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div>&copy; <?php echo date('Y'); ?> DRX Official Store. All Rights Reserved.</div>
        <div style="display: flex; gap: 20px;">
            <a href="#" style="color: var(--text-muted); text-decoration: none;">Privacy Policy</a>
            <a href="#" style="color: var(--text-muted); text-decoration: none;">Terms of Service</a>
        </div>
    </div>
</footer>

<!-- MODAL: QUICK VIEW / VARIANT SELECTOR -->
<div class="modal-overlay" id="drxVariantModal">
    <div class="modal-content">
        <button type="button" class="close-btn" id="drxCloseModal">&times;</button>
        <div class="m-left">
            <img id="m_img" src="" alt="Product Detail" style="width: 100%; max-height: 420px; object-fit: contain; margin-top: auto;">
            <div id="m_thumbnails" class="thumbnail-container"></div>
        </div>
        <div class="m-right" id="m_right_content">
            <!-- Dynamic injected via AJAX -->
        </div>
    </div>
</div>

<!-- MODAL: TRACK ORDER -->
<div class="modal-overlay" id="drxTrackOrderModal">
    <div class="modal-content" style="max-width: 480px; flex-direction: column; padding: 36px; border-radius: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="font-family: var(--font-heading); font-size: 22px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: var(--accent); color: white; border-radius: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </span>
                    <?php _e('Tra Cứu Đơn Hàng', 'hello-elementor-child'); ?>
                </h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 6px;"><?php _e('Xem lịch sử mua hàng và lộ trình vận chuyển.', 'hello-elementor-child'); ?></p>
            </div>
            <button type="button" class="close-btn" id="drxCloseTrackModal" style="position: static; font-size: 24px;">&times;</button>
        </div>

        <div id="trackFormArea">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;"><?php _e('Số Điện Thoại Đặt Hàng', 'hello-elementor-child'); ?></label>
                <input type="text" id="trackPhoneInput" placeholder="0987654321" style="width: 100%; padding: 14px 16px; border: 2px solid var(--border-color); border-radius: 10px; font-size: 14px; font-weight: 600; outline: none;">
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;"><?php _e('Mã Đơn Hàng', 'hello-elementor-child'); ?></label>
                <input type="text" id="trackOrderIdInput" placeholder="DRX-12345 hoặc #12345" style="width: 100%; padding: 14px 16px; border: 2px solid var(--border-color); border-radius: 10px; font-size: 14px; font-weight: 700; font-family: var(--font-heading); outline: none; text-transform: uppercase;">
            </div>

            <div id="trackErrorMsg" style="display: none; padding: 10px 14px; background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 8px; color: #EF4444; font-size: 13px; margin-bottom: 16px;">
                <span id="trackErrorText"></span>
            </div>

            <button type="button" id="btnExecuteTrackOrder" class="btn-buy-now" style="width: 100%; justify-content: center; padding: 14px;">
                <?php _e('XÁC THỰC & TRA CỨU', 'hello-elementor-child'); ?>
            </button>
        </div>

        <div id="trackResultArea" style="display: none;">
            <div id="trackStatusCard" style="padding: 20px; background: #F8FAFC; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 20px;"></div>
            <button type="button" id="btnResetTrackOrder" style="width: 100%; padding: 12px; font-size: 13px; font-weight: 700; color: var(--text-secondary); background: transparent; border: 2px solid var(--border-color); border-radius: 10px; cursor: pointer;">
                <?php _e('Tra cứu đơn hàng khác', 'hello-elementor-child'); ?>
            </button>
        </div>
    </div>
</div>

<!-- CART SIDEBAR DRAWER -->
<div class="cart-sidebar-overlay" id="drxCartOverlay"></div>
<div class="cart-sidebar" id="drxCartSidebar">
    <div class="cart-header">
        <h3><?php _e('Giỏ Hàng Của Bạn', 'hello-elementor-child'); ?></h3>
        <button type="button" class="cart-close" id="drxCartClose">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
    <div class="cart-body" id="drxCartBody">
        <div class="cart-empty" style="text-align:center; padding: 40px; color: var(--text-muted);"><?php _e('Giỏ hàng của bạn đang trống.', 'hello-elementor-child'); ?></div>
    </div>
    <div class="cart-footer">
        <div class="cart-total-row">
            <span><?php _e('Tổng cộng:', 'hello-elementor-child'); ?></span>
            <span id="drxCartTotal">$0.00</span>
        </div>
        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn-primary-checkout"><?php _e('Tiến Hành Thanh Toán', 'hello-elementor-child'); ?></a>
    </div>
</div>

<?php
get_footer();
