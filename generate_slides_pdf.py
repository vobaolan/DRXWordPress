import os
from reportlab.lib.pagesizes import A4, landscape
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, HRFlowable
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib import colors
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

pdfmetrics.registerFont(TTFont('Arial', 'C:/Windows/Fonts/arial.ttf'))
pdfmetrics.registerFont(TTFont('Arial-Bold', 'C:/Windows/Fonts/arialbd.ttf'))
pdfmetrics.registerFont(TTFont('Arial-Italic', 'C:/Windows/Fonts/ariali.ttf'))

pdf_path = "D:/DRXWordPress/SlideBaoCao_Nhom1.pdf"
doc = SimpleDocTemplate(
    pdf_path,
    pagesize=landscape(A4),
    leftMargin=40,
    rightMargin=40,
    topMargin=35,
    bottomMargin=35
)

# Styles
title_slide_title = ParagraphStyle(
    'TitleSlideTitle',
    fontName='Arial-Bold',
    fontSize=24,
    leading=30,
    textColor=colors.HexColor('#0052FF'),
    alignment=1
)

title_slide_sub = ParagraphStyle(
    'TitleSlideSub',
    fontName='Arial',
    fontSize=13,
    leading=18,
    textColor=colors.HexColor('#334155'),
    alignment=1
)

title_slide_meta = ParagraphStyle(
    'TitleSlideMeta',
    fontName='Arial',
    fontSize=11,
    leading=16,
    textColor=colors.HexColor('#64748B'),
    alignment=1
)

slide_header = ParagraphStyle(
    'SlideHeader',
    fontName='Arial-Bold',
    fontSize=18,
    leading=22,
    textColor=colors.HexColor('#0F172A'),
    spaceAfter=4
)

slide_sub = ParagraphStyle(
    'SlideSub',
    fontName='Arial',
    fontSize=10.5,
    leading=14,
    textColor=colors.HexColor('#0052FF'),
    spaceAfter=12
)

card_title = ParagraphStyle(
    'CardTitle',
    fontName='Arial-Bold',
    fontSize=12,
    leading=15,
    textColor=colors.HexColor('#0052FF')
)

card_body = ParagraphStyle(
    'CardBody',
    fontName='Arial',
    fontSize=9.5,
    leading=14,
    textColor=colors.HexColor('#1E293B')
)

table_th = ParagraphStyle('TTH', fontName='Arial-Bold', fontSize=10, leading=13, textColor=colors.white, alignment=1)
table_td = ParagraphStyle('TTD', fontName='Arial', fontSize=9, leading=12, textColor=colors.HexColor('#1E293B'), alignment=1)
table_td_left = ParagraphStyle('TTDL', fontName='Arial', fontSize=9, leading=12, textColor=colors.HexColor('#1E293B'))
table_td_green = ParagraphStyle('TTDG', fontName='Arial-Bold', fontSize=9, leading=12, textColor=colors.HexColor('#16A34A'), alignment=1)
table_td_red = ParagraphStyle('TTDR', fontName='Arial-Bold', fontSize=9, leading=12, textColor=colors.HexColor('#DC2626'), alignment=1)

story = []

# ==================== SLIDE 1: TRANG BÌA ====================
story.append(Spacer(1, 40))
story.append(Paragraph("TRƯỜNG ĐẠI HỌC VĂN LANG — KHOA CÔNG NGHỆ VÀ THIẾT KẾ", title_slide_meta))
story.append(Spacer(1, 10))
story.append(Paragraph("BÁO CÁO TỔNG KẾT ĐỒ ÁN CUỐI KỲ CMS & E-COMMERCE", title_slide_sub))
story.append(Spacer(1, 15))
story.append(Paragraph("DRX OFFICIAL ESPORTS STORE & PLATFORM", title_slide_title))
story.append(Spacer(1, 15))
story.append(HRFlowable(width="60%", thickness=2, color=colors.HexColor('#0052FF'), spaceBefore=0, spaceAfter=20))
story.append(Paragraph("<b>Nhóm thực hiện:</b> Nhóm 1 | <b>Sinh viên:</b> Võ Bảo Lân", title_slide_meta))
story.append(Paragraph("<b>Học phần:</b> Phát triển Hệ thống CMS & TMĐT (VLSC.V6) | <b>Giảng viên hướng dẫn:</b> Bộ môn TMĐT", title_slide_meta))
story.append(PageBreak())

# ==================== SLIDE 2: TỔNG QUAN DỰ ÁN & KIẾN TRÚC DỮ LIỆU ====================
story.append(Paragraph("1. TỔNG QUAN DỰ ÁN & KIẾN TRÚC HỆ THỐNG", slide_header))
story.append(Paragraph("Dự án xây dựng nền tảng E-Commerce chính thức cho tổ chức Esports DRX (Hàn Quốc)", slide_sub))

card1_content = [
    [Paragraph("<b>Mục tiêu & Yêu cầu cốt lõi</b>", card_title)],
    [Paragraph("• Xây dựng trải nghiệm mua sắm Esports chuyên nghiệp, hiện đại chuẩn giao diện DRX.<br/>• Tích hợp phân hệ bán hàng WooCommerce tùy biến sâu với giỏ hàng trượt (Cart Drawer).<br/>• Hỗ trợ dịch vụ may in tên cá nhân hóa <b>Custom Embroidered ID</b> (miễn phí).<br/>• Tính năng tra cứu tiến độ vận đơn thời gian thực <b>Real-time Order Tracking</b>.", card_body)]
]
t_c1 = Table(card1_content, colWidths=[360])
t_c1.setStyle(TableStyle([
    ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#F8FAFC')),
    ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#CBD5E1')),
    ('TOPPADDING', (0,0), (-1,-1), 8),
    ('BOTTOMPADDING', (0,0), (-1,-1), 8),
    ('LEFTPADDING', (0,0), (-1,-1), 12),
    ('RIGHTPADDING', (0,0), (-1,-1), 12),
]))

card2_content = [
    [Paragraph("<b>Kiến trúc Dữ liệu & Theme</b>", card_title)],
    [Paragraph("• <b>Theme Engine:</b> Hello Elementor Child với cấu trúc CSS Token & AJAX Endpoint độc lập.<br/>• <b>WooCommerce Products:</b> Quản lý 23+ sản phẩm với biến thể Size (S/M/L/XL), màu sắc, tiền tệ VNĐ.<br/>• <b>Custom Fields & Metadata:</b> Lưu trữ Custom ID thêu trên từng Item trong Cart & Order.<br/>• <b>Shipping Zones:</b> Cấu hình 2 vùng vận chuyển (Nội thành 30k, Toàn quốc 50k).", card_body)]
]
t_c2 = Table(card2_content, colWidths=[360])
t_c2.setStyle(TableStyle([
    ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#F8FAFC')),
    ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#CBD5E1')),
    ('TOPPADDING', (0,0), (-1,-1), 8),
    ('BOTTOMPADDING', (0,0), (-1,-1), 8),
    ('LEFTPADDING', (0,0), (-1,-1), 12),
    ('RIGHTPADDING', (0,0), (-1,-1), 12),
]))

slide2_table = Table([[t_c1, t_c2]], colWidths=[375, 375])
slide2_table.setStyle(TableStyle([('VALIGN', (0,0), (-1,-1), 'TOP')]))
story.append(slide2_table)
story.append(PageBreak())

# ==================== SLIDE 3: TỐI ƯU HIỆU NĂNG WPO (A/B TESTING) ====================
story.append(Paragraph("2. TỐI ƯU HÓA HIỆU NĂNG WPO & CORE WEB VITALS", slide_header))
story.append(Paragraph("Kết quả đối chiếu A/B Testing đo lường thực tế qua Google Lighthouse Desktop", slide_sub))

wpo_table_data = [
    [Paragraph("<b>Chỉ số đo lường (Metrics)</b>", table_th), Paragraph("<b>Trước tối ưu</b>", table_th), Paragraph("<b>Sau tối ưu</b>", table_th), Paragraph("<b>Mức độ cải thiện</b>", table_th), Paragraph("<b>Giải pháp kỹ thuật</b>", table_th)],
    [Paragraph("<b>Điểm Performance Score</b>", table_td_left), Paragraph("74 / 100", table_td_red), Paragraph("96 / 100", table_td_green), Paragraph("+22 điểm (+29.7%)", table_td_green), Paragraph("Vào vùng Xanh xuất sắc", table_td)],
    [Paragraph("<b>LCP (Largest Contentful Paint)</b>", table_td_left), Paragraph("3.2 giây", table_td_red), Paragraph("0.9 giây", table_td_green), Paragraph("Giảm 2.3s (-71.8%)", table_td_green), Paragraph("Nén ảnh WebP Squoosh (75-80)", table_td)],
    [Paragraph("<b>FCP (First Contentful Paint)</b>", table_td_left), Paragraph("0.6 giây", table_td), Paragraph("0.4 giây", table_td_green), Paragraph("Giảm 0.2s (-33.3%)", table_td_green), Paragraph("Minify CSS/JS, loại bỏ chặn hiển thị", table_td)],
    [Paragraph("<b>CLS (Layout Shift)</b>", table_td_left), Paragraph("0.00", table_td), Paragraph("0.00", table_td_green), Paragraph("0.00 (Ổn định)", table_td_green), Paragraph("Khung kích thước ảnh chuẩn", table_td)],
    [Paragraph("<b>Thời gian phản hồi (TTFB)</b>", table_td_left), Paragraph("1.51 giây", table_td_red), Paragraph("0.15 giây", table_td_green), Paragraph("Giảm 1.36s (-90%)", table_td_green), Paragraph("Bật Page Caching tĩnh WP-Optimize", table_td)],
    [Paragraph("<b>Dung lượng trang trung bình</b>", table_td_left), Paragraph("3.8 MB", table_td_red), Paragraph("640 KB", table_td_green), Paragraph("Tiết kiệm ~83% Data", table_td_green), Paragraph("Lazy Load + nén tài nguyên", table_td)]
]
t_wpo = Table(wpo_table_data, colWidths=[180, 100, 95, 140, 235])
t_wpo.setStyle(TableStyle([
    ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#0052FF')),
    ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor('#CBD5E1')),
    ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor('#F8FAFC')]),
    ('TOPPADDING', (0,0), (-1,-1), 5),
    ('BOTTOMPADDING', (0,0), (-1,-1), 5),
    ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
]))
story.append(t_wpo)
story.append(PageBreak())

# ==================== SLIDE 4: BẢO MẬT & CHATBOT AI RAG ====================
story.append(Paragraph("3. BẢO MẬT HỆ THỐNG & TÍCH HỢP TRỢ LÝ CHATBOT AI", slide_header))
story.append(Paragraph("Đảm bảo an toàn dữ liệu 3-2-1 và tự động hóa chăm sóc khách hàng 24/7", slide_sub))

card3_content = [
    [Paragraph("<b>Bảo mật & Sao lưu UpdraftPlus (Chuẩn 3-2-1)</b>", card_title)],
    [Paragraph("• <b>Nguyên tắc sao lưu 3-2-1:</b> Giữ 3 bản sao, trên 2 phương tiện, 1 bản lưu Off-site.<br/>• <b>Google Drive Sync:</b> Tích hợp UpdraftPlus tự động đẩy Database & Files lên Google Drive định kỳ.<br/>• <b>Bảo vệ Form & AJAX:</b> Cơ chế Nonce & Safe URL ngăn chặn CSRF, bảo toàn port điều hướng.<br/>• <b>Bảo mật WordPress:</b> Phân quyền chặt chẽ wp-content, vô hiệu hóa file editing.", card_body)]
]
t_c3 = Table(card3_content, colWidths=[360])
t_c3.setStyle(TableStyle([
    ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#F8FAFC')),
    ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#CBD5E1')),
    ('TOPPADDING', (0,0), (-1,-1), 8),
    ('BOTTOMPADDING', (0,0), (-1,-1), 8),
    ('LEFTPADDING', (0,0), (-1,-1), 12),
    ('RIGHTPADDING', (0,0), (-1,-1), 12),
]))

card4_content = [
    [Paragraph("<b>Trợ lý Chatbot AI DRX Assistant (RAG)</b>", card_title)],
    [Paragraph("• <b>Giao diện Tone Trắng - Xanh Electric:</b> Đồng bộ 100% với giao diện website.<br/>• <b>Cơ chế RAG:</b> Trả lời bám sát nguồn dữ liệu sản phẩm, bảng size, phí ship, chính sách đổi trả 7 ngày.<br/>• <b>Kiểm thử 5 kịch bản khó:</b> Xử lý trọn vẹn so sánh áo T-Shirt/Windbreaker, miễn phí in tên, từ chối câu hỏi thời tiết/lạc đề đúng System Prompt.<br/>• <b>Khả dụng 24/7:</b> Hỗ trợ phản hồi khách hàng ngay lập tức.", card_body)]
]
t_c4 = Table(card4_content, colWidths=[360])
t_c4.setStyle(TableStyle([
    ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#F8FAFC')),
    ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#CBD5E1')),
    ('TOPPADDING', (0,0), (-1,-1), 8),
    ('BOTTOMPADDING', (0,0), (-1,-1), 8),
    ('LEFTPADDING', (0,0), (-1,-1), 12),
    ('RIGHTPADDING', (0,0), (-1,-1), 12),
]))

slide4_table = Table([[t_c3, t_c4]], colWidths=[375, 375])
slide4_table.setStyle(TableStyle([('VALIGN', (0,0), (-1,-1), 'TOP')]))
story.append(slide4_table)
story.append(PageBreak())

# ==================== SLIDE 5: MINH CHỨNG & KẾT LUẬN ====================
story.append(Paragraph("4. TỔNG KẾT & LIÊN KẾT MINH CHỨNG DỰ ÁN", slide_header))
story.append(Paragraph("Hệ sinh thái hoàn chỉnh đã được triển khai và sẵn sàng bảo vệ đồ án", slide_sub))

demo_content = [
    [Paragraph("<b>Thông tin nghiệm thu đồ án</b>", card_title)],
    [Paragraph("• <b>Website Online:</b> <font color='#0052FF'><u>https://drxwordpress.local</u></font><br/>• <b>GitHub Repository (Public):</b> <font color='#0052FF'><u>https://github.com/vobaolan/DRXWordPress</u></font><br/>• <b>Bản sao lưu hệ thống:</b> File Backup toàn vẹn (.wpress / .zip) đã đóng gói.<br/>• <b>Hồ sơ năng lực:</b> CV cá nhân chuẩn ATS định hướng WordPress & Frontend Developer.", card_body)],
    [Spacer(1, 4)],
    [Paragraph("<b>CẢM ƠN QUÝ THẦY CÔ VÀ HỘI ĐỒNG ĐÃ LẮNG NGHE!</b>", ParagraphStyle('TY', fontName='Arial-Bold', fontSize=13, leading=17, textColor=colors.HexColor('#0052FF'), alignment=1))]
]
t_demo = Table(demo_content, colWidths=[730])
t_demo.setStyle(TableStyle([
    ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#F8FAFC')),
    ('BOX', (0,0), (-1,-1), 1.5, colors.HexColor('#0052FF')),
    ('TOPPADDING', (0,0), (-1,-1), 12),
    ('BOTTOMPADDING', (0,0), (-1,-1), 12),
    ('LEFTPADDING', (0,0), (-1,-1), 18),
    ('RIGHTPADDING', (0,0), (-1,-1), 18),
]))
story.append(t_demo)

doc.build(story)
print(f"Slide deck generated successfully at {pdf_path}")
