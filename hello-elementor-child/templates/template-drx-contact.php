<?php
/**
 * Template Name: DRX Contact Us
 * Template Post Type: page
 * 
 * Template trang Liên Hệ & Hỗ Trợ Khách Hàng DRX Store
 */

get_header();
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

<main style="max-width: 1200px; margin: 0 auto; padding: 60px 24px 100px;">
    <div style="text-align: center; margin-bottom: 60px;">
        <span style="font-size: 12px; font-weight: 700; color: var(--accent); letter-spacing: 2px; text-transform: uppercase;"><?php _e('DRX CUSTOMER CARE', 'hello-elementor-child'); ?></span>
        <h1 style="font-family: var(--font-heading); font-size: 38px; font-weight: 900; margin: 12px 0; color: var(--text-primary); text-transform: uppercase;">
            <?php _e('CONTACT & SUPPORT', 'hello-elementor-child'); ?>
        </h1>
        <p style="color: var(--text-secondary); max-width: 540px; margin: 0 auto; font-size: 15px;">
            <?php _e('Chúng tôi sẵn sàng giải đáp mọi thắc mắc về đơn hàng, vận chuyển quốc tế và kích thước trang phục thi đấu.', 'hello-elementor-child'); ?>
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: start;">
        <!-- CONTACT INFO -->
        <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: 20px; padding: 40px;">
            <h2 style="font-family: var(--font-heading); font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 24px;">
                <?php _e('GLOBAL HEADQUARTERS', 'hello-elementor-child'); ?>
            </h2>

            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;"><?php _e('Trụ sở chính (Seoul, Hàn Quốc)', 'hello-elementor-child'); ?></h4>
                <p style="font-size: 14px; color: var(--text-primary); line-height: 1.6; margin: 0;">
                    DRX Esports Center, Gangnam-gu, Seoul, Republic of Korea.
                </p>
            </div>

            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;"><?php _e('Email Hỗ Trợ Khách Hàng', 'hello-elementor-child'); ?></h4>
                <p style="font-size: 14px; font-weight: 600; color: var(--accent); margin: 0;">support@drxglobal.com</p>
            </div>

            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;"><?php _e('Thời Gian Làm Việc', 'hello-elementor-child'); ?></h4>
                <p style="font-size: 14px; color: var(--text-primary); margin: 0;">Thứ 2 – Thứ 6: 09:00 - 18:00 (KST)</p>
            </div>

            <div style="padding-top: 24px; border-top: 1px solid var(--border-color);">
                <h4 style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px;"><?php _e('Mạng Xã Hội Chính Thức', 'hello-elementor-child'); ?></h4>
                <div style="display: flex; gap: 12px;">
                    <a href="https://www.instagram.com/drxglobal/" target="_blank" style="padding: 10px 18px; background: #fff; border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); text-decoration: none; font-size: 13px; font-weight: 700;">Instagram</a>
                    <a href="https://x.com/DRX_LCK" target="_blank" style="padding: 10px 18px; background: #fff; border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); text-decoration: none; font-size: 13px; font-weight: 700;">Twitter (X)</a>
                    <a href="https://www.facebook.com/DRXGlobal/" target="_blank" style="padding: 10px 18px; background: #fff; border: 1px solid var(--border-color); border-radius: 10px; color: var(--text-primary); text-decoration: none; font-size: 13px; font-weight: 700;">Facebook</a>
                </div>
            </div>
        </div>

        <!-- CONTACT FORM -->
        <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
            <h2 style="font-family: var(--font-heading); font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 24px;">
                <?php _e('GỬI TIN NHẮN CHO DRX', 'hello-elementor-child'); ?>
            </h2>

            <form onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã liên hệ! Đội ngũ DRX Store sẽ phản hồi trong vòng 24 giờ.');">
                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">Họ và Tên</label>
                    <input type="text" required placeholder="Nguyễn Văn A" style="width: 100%; padding: 14px 16px; border: 2px solid var(--border-color); border-radius: 10px; font-size: 14px; font-weight: 500; outline: none;">
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">Địa Chỉ Email</label>
                    <input type="email" required placeholder="example@gmail.com" style="width: 100%; padding: 14px 16px; border: 2px solid var(--border-color); border-radius: 10px; font-size: 14px; font-weight: 500; outline: none;">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 6px;">Nội Dung Cần Hỗ Trợ</label>
                    <textarea rows="4" required placeholder="Nhập câu hỏi hoặc thông tin về đơn hàng của bạn..." style="width: 100%; padding: 14px 16px; border: 2px solid var(--border-color); border-radius: 10px; font-size: 14px; font-weight: 500; outline: none; resize: vertical;"></textarea>
                </div>

                <button type="submit" class="btn-buy-now" style="width: 100%; justify-content: center; padding: 16px; font-size: 14px; font-weight: 700; letter-spacing: 1px; border-radius: 12px;">
                    GỬI YÊU CẦU HỖ TRỢ
                </button>
            </form>
        </div>
    </div>
</main>

<?php
get_footer();
