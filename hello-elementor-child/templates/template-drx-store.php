<?php
/**
 * Template Name: DRX Official Store
 * Template Post Type: page
 * 
 * Template trang chủ DRX Store - Chuẩn 100% theo D:\DRX\store.html
 */

get_header();

// Helper lấy ảnh sản phẩm an toàn tuyệt đối (không bao giờ bị mất ảnh hay trắng ô)
function drx_store_get_image($product, $is_hover = false) {
    if (!$product) return 'https://teamdrx.vercel.app/thumbnail/20260727/aa447560a8495.png';
    $p_id = $product->get_id();
    
    if (!$is_hover) {
        $img_id = $product->get_image_id();
        if ($img_id) {
            $src = wp_get_attachment_image_url($img_id, 'large') ?: wp_get_attachment_image_url($img_id, 'full');
            if (!empty($src)) return $src;
        }
        $meta_img = get_post_meta($p_id, '_drx_product_image_url', true);
        if (!empty($meta_img)) return $meta_img;
        return 'https://teamdrx.vercel.app/thumbnail/20260727/aa447560a8495.png';
    } else {
        $gallery_ids = $product->get_gallery_image_ids();
        if (!empty($gallery_ids)) {
            $src = wp_get_attachment_image_url($gallery_ids[0], 'large') ?: wp_get_attachment_image_url($gallery_ids[0], 'full');
            if (!empty($src)) return $src;
        }
        return '';
    }
}

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

<!-- DRX HEADER (D:\DRX\store.html) -->
<header>
    <div class="logo">
        <a href="<?php echo esc_url(home_url('/')); ?>">
            <img src="https://teamdrx.vercel.app/thumbnail/20260727/aa447560a8495.png" alt="DRX Official Store">
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

<main>
    <!-- TRENDING NOW SECTION (Option B from D:\DRX\store.html) -->
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
                // Duplicate 2 lần để cuộn vô tận
                for ($loop = 0; $loop < 2; $loop++) :
                    foreach ($marquee_items as $m_item) : ?>
                        <div class="marquee-item" onclick="openProduct('<?php echo esc_js($m_item['id']); ?>')">
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
    </div>
    <?php endif; ?>

    <!-- SHOP LAYOUT (D:\DRX\store.html) -->
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
            <div class="store-search-container" style="margin-bottom: 24px; position: relative;">
                <input type="text" id="searchInput" class="store-search-input" placeholder="<?php esc_attr_e('Search products...', 'hello-elementor-child'); ?>" onkeyup="handleSearch(this.value)" autocomplete="off">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
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
                                <div class="product-price">USD <?php echo number_format($prod['price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">
                        <?php _e('Chưa có sản phẩm nào trong cửa hàng.', 'hello-elementor-child'); ?>
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
            <img src="https://teamdrx.vercel.app/thumbnail/20260727/aa447560a8495.png" alt="DRX Logo" class="f-logo">
            <p>DRX Official Store. Equip yourself with the latest authentic gear and join the unbreakable legacy of our champions.</p>
        </div>
        <div class="footer-links">
            <h4>Customer Care</h4>
            <ul>
                <li><a href="javascript:void(0)" onclick="openTrackOrder()">Track Your Order</a></li>
                <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact Us</a></li>
                <li><a href="#">Shipping Policy</a></li>
                <li><a href="#">Returns & Exchanges</a></li>
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

<!-- MODAL: TRACK ORDER (D:\DRX\store.html) -->
<div class="modal-overlay" id="trackOrderModal">
    <div class="modal-content" style="max-width: 480px; flex-direction: column; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(16px); border: 1px solid rgba(0,0,0,0.08); border-radius: 20px; padding: 40px; box-shadow: 0 24px 48px rgba(0,0,0,0.12);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px;">
            <div>
                <h2 style="font-family: var(--font-heading); font-size: 24px; font-weight: 700; margin: 0; color: var(--text-primary); letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: var(--accent); color: white; border-radius: 10px; box-shadow: 0 8px 16px rgba(0,82,255,0.25);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </span>
                    <?php _e('Track Order', 'hello-elementor-child'); ?>
                </h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 8px; font-weight: 500;"><?php _e('Securely view your purchase history.', 'hello-elementor-child'); ?></p>
            </div>
            <button class="close-btn" onclick="closeTrackOrder()" style="position: static; font-size: 24px;">&times;</button>
        </div>
        
        <div id="trackFormArea">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;"><?php _e('Phone Number', 'hello-elementor-child'); ?></label>
                <div style="position: relative;">
                    <input type="text" id="trackPhone" placeholder="+84 987 654 321" style="width: 100%; padding: 16px 16px 16px 44px; border: 2px solid #E2E8F0; border-radius: 12px; font-family: inherit; font-size: 15px; font-weight: 500; color: var(--text-primary); transition: all 0.3s; background: #fff; outline: none;">
                    <svg style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94A3B8;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                </div>
            </div>
            <div style="margin-bottom: 32px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;"><?php _e('Order ID', 'hello-elementor-child'); ?></label>
                <div style="position: relative;">
                    <input type="text" id="trackOrderId" placeholder="DRX-..." style="width: 100%; padding: 16px 16px 16px 44px; border: 2px solid #E2E8F0; border-radius: 12px; font-family: var(--font-heading); font-size: 15px; font-weight: 700; color: var(--text-primary); text-transform: uppercase; transition: all 0.3s; background: #fff; outline: none; letter-spacing: 1px;">
                    <svg style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94A3B8;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
            </div>
            
            <div id="trackErrorMsg" style="display: none; padding: 12px 16px; background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 8px; color: #EF4444; font-size: 13px; font-weight: 500; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                <span id="trackErrorText"></span>
            </div>

            <button class="btn-buy-now" id="btnTrackOrder" onclick="executeTrackOrder()" style="width: 100%; margin: 0; justify-content: center; border-radius: 12px; padding: 16px; font-size: 14px; letter-spacing: 1px; background: var(--text-primary); color: white; border: none; cursor: pointer; font-weight: 700;">
                <?php _e('VERIFY & TRACK', 'hello-elementor-child'); ?>
            </button>
        </div>

        <div id="trackResultArea" style="display: none;">
            <div id="trackStatusCard" style="padding: 24px; background: #fff; border: 2px solid #E2E8F0; border-radius: 16px; margin-bottom: 24px;">
                <!-- Result injected here -->
            </div>
            <button onclick="resetTrackOrder()" style="width: 100%; padding: 14px; font-size: 13px; font-weight: 600; color: var(--text-secondary); background: transparent; border: 2px solid #E2E8F0; border-radius: 12px; cursor: pointer;">
                <?php _e('Search Another Order', 'hello-elementor-child'); ?>
            </button>
        </div>
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
        <div style="text-align:center; padding: 40px; color: var(--text-muted);"><?php _e('Your cart is currently empty.', 'hello-elementor-child'); ?></div>
    </div>
    <div class="cart-footer">
        <div class="cart-total-row">
            <span><?php _e('Total:', 'hello-elementor-child'); ?></span>
            <span id="cartTotal">$0.00</span>
        </div>
        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn-primary-checkout"><?php _e('Proceed to Checkout', 'hello-elementor-child'); ?></a>
    </div>
</div>

<?php
get_footer();
