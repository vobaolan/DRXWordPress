<?php
/**
 * File: page.php
 * Theme: Hello Elementor Child - DRX Official Store
 * 
 * Đảm bảo mọi trang chính / trang chủ đều tự động nạp giao diện DRX Store chuẩn 100%.
 */

if (!defined('ABSPATH')) {
    exit;
}

$page_template = get_page_template_slug();

// 1. Nếu trang đã chọn 1 trong các template của DRX
if (!empty($page_template) && $page_template !== 'default') {
    $template_path = get_stylesheet_directory() . '/' . $page_template;
    if (file_exists($template_path)) {
        require $template_path;
        exit;
    }
}

// 2. Nếu là Trang chủ hoặc trang có tên chứa 'Home' / 'Store'
$page_slug = get_post_field('post_name', get_the_ID());
if (is_front_page() || is_home() || get_the_ID() == get_option('page_on_front') || strpos($page_slug, 'home') !== false || strpos($page_slug, 'store') !== false) {
    require get_stylesheet_directory() . '/templates/template-drx-store.php';
    exit;
}

// 3. Fallback cho các trang văn bản thông thường
get_header();
?>
<main class="shop-main-wrapper" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <?php
    while (have_posts()) :
        the_post();
        the_title('<h1 style="font-family: var(--font-heading); margin-bottom: 24px;">', '</h1>');
        the_content();
    endwhile;
    ?>
</main>
<?php
get_footer();
