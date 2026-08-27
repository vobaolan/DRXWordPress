<?php
/**
 * Template Name: DRX Lookbook Showcase
 * Template Post Type: page
 * 
 * Template hiển thị các bộ sưu tập thời trang Lookbook theo mùa của DRX
 */

get_header();

$args = array(
    'post_type'      => 'drx_lookbook',
    'posts_per_page' => 20,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC'
);
$lookbook_query = new WP_Query($args);
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
        <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Store', 'hello-elementor-child'); ?></a>
        <a href="javascript:void(0)" class="drx-open-track-trigger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
            <?php _e('Track Order', 'hello-elementor-child'); ?>
        </a>
        <a href="javascript:void(0)" class="drx-open-cart-trigger">
            <?php _e('Cart', 'hello-elementor-child'); ?> 
            (<span class="drx-cart-count"><?php echo esc_html($cart_count); ?></span>)
        </a>
    </div>
</header>

<main style="max-width: 1400px; margin: 0 auto; padding: 40px 24px 80px;">
    <!-- HERO SECTION -->
    <div style="text-align: center; margin-bottom: 50px;">
        <span style="font-size: 12px; font-weight: 700; color: var(--accent); letter-spacing: 2px; text-transform: uppercase;"><?php _e('EDITORIAL & FASHION ARCHIVE', 'hello-elementor-child'); ?></span>
        <h1 style="font-family: var(--font-heading); font-size: 40px; font-weight: 900; margin: 12px 0; color: var(--text-primary); text-transform: uppercase; letter-spacing: 1px;">
            <?php _e('DRX LOOKBOOK COLLECTIONS', 'hello-elementor-child'); ?>
        </h1>
        <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto; font-size: 15px;">
            <?php _e('Khám phá phong cách thời trang thi đấu và streetwear đỉnh cao của các nhà vô địch thế giới DRX.', 'hello-elementor-child'); ?>
        </p>
    </div>

    <!-- LOOKBOOK GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 36px;">
        <?php if ($lookbook_query->have_posts()) : ?>
            <?php while ($lookbook_query->have_posts()) : $lookbook_query->the_post(); 
                $lb_id = get_the_ID();
                $theme = get_post_meta($lb_id, '_drx_lookbook_theme', true);
                $brand = get_post_meta($lb_id, '_drx_collab_brand', true);
                $rel_date = get_post_meta($lb_id, '_drx_release_date', true);
                $feat_p_id = get_post_meta($lb_id, '_drx_featured_product_id', true);
                $img_url = get_the_post_thumbnail_url($lb_id, 'large') ?: 'https://teamdrx.vercel.app/thumbnail/20260727/aa447560a8495.png';
                $seasons = wp_get_post_terms($lb_id, 'lookbook_season', array('fields' => 'names'));
            ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: transform 0.3s ease, border-color 0.3s ease;">
                    <div style="height: 380px; overflow: hidden; position: relative; background: #F8FAFC;">
                        <img src="<?php echo esc_url($img_url); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php if (!empty($seasons)) : ?>
                            <span style="position: absolute; top: 16px; left: 16px; background: rgba(0, 82, 255, 0.9); color: white; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 1px;">
                                <?php echo esc_html($seasons[0]); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div style="padding: 24px;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">
                            <?php echo esc_html($brand ?: 'DRX OFFICIAL'); ?>
                        </span>
                        <h2 style="font-size: 18px; font-weight: 700; margin: 8px 0 12px; color: var(--text-primary);">
                            <?php the_title(); ?>
                        </h2>
                        <?php if ($theme) : ?>
                            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
                                <strong><?php _e('Concept:', 'hello-elementor-child'); ?></strong> <?php echo esc_html($theme); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($feat_p_id && class_exists('WooCommerce')) : 
                            $f_prod = wc_get_product($feat_p_id);
                            if ($f_prod) : ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid var(--border-color);">
                                    <span style="font-size: 13px; font-weight: 600; color: var(--accent);">
                                        <?php echo esc_html($f_prod->get_name()); ?>
                                    </span>
                                    <span style="font-size: 14px; font-weight: 700; color: var(--text-primary);">
                                        <?php echo $f_prod->get_price_html(); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php else : ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--text-muted);">
                <p><?php _e('Chưa có bộ sưu tập Lookbook nào. Hãy vào WP Admin > Lookbook Thời Trang để đăng bài viết mới.', 'hello-elementor-child'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
