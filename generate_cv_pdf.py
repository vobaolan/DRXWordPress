import os
from reportlab.lib.pagesizes import A4
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, HRFlowable
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib import colors
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

pdfmetrics.registerFont(TTFont('Arial', 'C:/Windows/Fonts/arial.ttf'))
pdfmetrics.registerFont(TTFont('Arial-Bold', 'C:/Windows/Fonts/arialbd.ttf'))
pdfmetrics.registerFont(TTFont('Arial-Italic', 'C:/Windows/Fonts/ariali.ttf'))

cv_path = "D:/DRXWordPress/CV_VoBaoLan.pdf"
doc = SimpleDocTemplate(
    cv_path,
    pagesize=A4,
    leftMargin=36,
    rightMargin=36,
    topMargin=32,
    bottomMargin=32
)

name_style = ParagraphStyle(
    'NameStyle',
    fontName='Arial-Bold',
    fontSize=18,
    leading=22,
    textColor=colors.HexColor('#0F172A'),
    alignment=1
)

title_style = ParagraphStyle(
    'TitleStyle',
    fontName='Arial-Bold',
    fontSize=11,
    leading=15,
    textColor=colors.HexColor('#0052FF'),
    alignment=1
)

contact_style = ParagraphStyle(
    'ContactStyle',
    fontName='Arial',
    fontSize=8.5,
    leading=12,
    textColor=colors.HexColor('#475569'),
    alignment=1
)

section_title = ParagraphStyle(
    'SectionTitle',
    fontName='Arial-Bold',
    fontSize=10.5,
    leading=14,
    textColor=colors.HexColor('#0052FF'),
    spaceBefore=8,
    spaceAfter=4
)

body_style = ParagraphStyle(
    'BodyStyle',
    fontName='Arial',
    fontSize=8.5,
    leading=12.5,
    textColor=colors.HexColor('#1E293B')
)

body_bold = ParagraphStyle(
    'BodyBold',
    fontName='Arial-Bold',
    fontSize=8.5,
    leading=12.5,
    textColor=colors.HexColor('#0F172A')
)

story = []

# Header
story.append(Paragraph("VÕ BẢO LÂN", name_style))
story.append(Paragraph("WORDPRESS & FRONTEND DEVELOPER | E-COMMERCE SPECIALIST", title_style))
story.append(Spacer(1, 3))
story.append(Paragraph("TP. Hồ Chí Minh | Email: baolan.vo@gmail.com | SĐT: (+84) 090 xxx xxxx | GitHub: github.com/vobaolan | Portfolio: drxwordpress.local", contact_style))
story.append(Spacer(1, 6))
story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor('#CBD5E1'), spaceBefore=0, spaceAfter=6))

# Summary
story.append(Paragraph("TÓM TẮT NĂNG LỰC (PROFESSIONAL SUMMARY)", section_title))
story.append(Paragraph(
    "Lập trình viên WordPress & Frontend định hướng chuyên sâu phát triển hệ thống Thương mại Điện tử (WooCommerce). "
    "Thành thạo xây dựng Custom Theme tối ưu chuẩn SEO, kiến trúc giỏ hàng AJAX thời gian thực, bảo toàn cổng điều hướng, "
    "tối ưu hóa hiệu năng toàn diện (WPO) đạt 96/100 Google Lighthouse và tích hợp Trợ lý Chatbot AI đa kênh (RAG). "
    "Tư duy giải quyết vấn đề mạch lạc, nắm vững quy chuẩn bảo mật WordPress và văn hóa làm việc với Git/CI-CD.",
    body_style
))

# Technical Skills
story.append(Paragraph("KỸ NĂNG CHUYÊN MÔN (TECHNICAL SKILLS)", section_title))
skills_data = [
    [Paragraph("<b>CMS & E-Commerce:</b>", body_bold), Paragraph("WordPress Core, WooCommerce Customization, Hook/Filter Architecture, ACF, Custom Post Types.", body_style)],
    [Paragraph("<b>Frontend Tech:</b>", body_bold), Paragraph("HTML5, CSS3, Tailwind CSS Token Architecture, JavaScript (ES6+), jQuery, AJAX, Responsive Design.", body_style)],
    [Paragraph("<b>Backend & Database:</b>", body_bold), Paragraph("PHP 8.x, MySQL Database Optimization, Safe Nonce Verification, REST API, Endpoint Handling.", body_style)],
    [Paragraph("<b>WPO & Security:</b>", body_bold), Paragraph("WebP Image Compression (Squoosh), Page Caching (WP-Optimize), Lazy Loading, Minification, UpdraftPlus 3-2-1 Backup.", body_style)],
    [Paragraph("<b>AI & DevOps:</b>", body_bold), Paragraph("Chatbase AI / RAG Integration, Git / GitHub Version Control, LocalWP, DNS & SSL Configuration.", body_style)],
]
t_skills = Table(skills_data, colWidths=[125, 400])
t_skills.setStyle(TableStyle([
    ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ('TOPPADDING', (0,0), (-1,-1), 1.5),
    ('BOTTOMPADDING', (0,0), (-1,-1), 1.5),
    ('LEFTPADDING', (0,0), (-1,-1), 0),
    ('RIGHTPADDING', (0,0), (-1,-1), 0),
]))
story.append(t_skills)

# Featured Projects
story.append(Paragraph("DỰ ÁN TIÊU BIỂU (FEATURED PROJECTS)", section_title))
story.append(Paragraph("<b>DRX Official Esports Store & Merchandise Platform</b> | <i>Role: Lead Developer</i> (2026)", body_bold))
project_bullets = [
    "• <b>Kiến trúc Theme:</b> Tự xây dựng Hello Elementor Child Theme với hệ thống Design Token chuẩn phong cách DRX Esports, hỗ trợ 23+ sản phẩm biến thể Size/Màu sắc và tiền tệ VNĐ.",
    "• <b>Tùy biến WooCommerce sâu:</b> Phát triển tính năng may in tên cá nhân <b>Custom Embroidered ID</b> miễn phí, giỏ hàng trượt <b>AJAX Cart Drawer</b> mượt mà và hệ thống tra cứu đơn hàng <b>Real-time Order Tracking</b>.",
    "• <b>Tối ưu hóa hiệu năng (WPO):</b> Nâng điểm Google Lighthouse từ <b>74 lên 96 điểm</b>, giảm LCP từ <b>3.2s xuống 0.9s</b> thông qua chuẩn nén WebP, Page Caching tĩnh và Minify CSS/JS.",
    "• <b>Tích hợp Chatbot AI:</b> Nhúng Trợ lý DRX AI thông minh (RAG) hỗ trợ tư vấn size, báo giá và giải đáp chính sách 24/7.",
    "• <b>Bảo mật & Sao lưu:</b> Triển khai quy trình sao lưu tự động đám mây <b>Google Drive</b> theo nguyên tắc an toàn dữ liệu 3-2-1."
]
for b in project_bullets:
    story.append(Paragraph(b, body_style))
    story.append(Spacer(1, 1.5))

# Education
story.append(Paragraph("HỌC VẤN (EDUCATION)", section_title))
story.append(Paragraph("<b>Trường Đại học Văn Lang</b> — Khoa Công nghệ và Thiết kế", body_bold))
story.append(Paragraph("Chuyên ngành: Phát triển Hệ thống CMS và Thương mại Điện tử | GPA Khá - Giỏi", body_style))

# Certifications & Soft Skills
story.append(Paragraph("CHỨNG CHỈ & KỸ NĂNG MỀM (CERTIFICATES & SOFT SKILLS)", section_title))
story.append(Paragraph("• <b>Chứng chỉ:</b> WordPress E-Commerce Master, Google Analytics & Web Performance Optimization.", body_style))
story.append(Paragraph("• <b>Kỹ năng mềm:</b> Tư duy hệ thống (System Thinking), Làm việc nhóm, Thuyết trình đồ án, Đọc hiểu tài liệu kỹ thuật tiếng Anh.", body_style))

doc.build(story)
print(f"CV generated successfully at {cv_path}")
