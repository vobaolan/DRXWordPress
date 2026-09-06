import os
from reportlab.lib.pagesizes import A4
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, HRFlowable
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib import colors
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

# Đăng ký font tiếng Việt Arial từ Windows
pdfmetrics.registerFont(TTFont('Arial', 'C:/Windows/Fonts/arial.ttf'))
pdfmetrics.registerFont(TTFont('Arial-Bold', 'C:/Windows/Fonts/arialbd.ttf'))

pdf_path = "D:/DRXWordPress/BaoCao_ABTesting_Nhom1.pdf"
try:
    if os.path.exists(pdf_path):
        os.remove(pdf_path)
except Exception:
    pdf_path = "D:/DRXWordPress/BaoCao_ABTesting_Nhom1_Moi.pdf"
doc = SimpleDocTemplate(
    pdf_path,
    pagesize=A4,
    leftMargin=36,
    rightMargin=36,
    topMargin=36,
    bottomMargin=36
)

styles = getSampleStyleSheet()

header_style = ParagraphStyle(
    'HeaderStyle',
    fontName='Arial-Bold',
    fontSize=13,
    leading=16,
    textColor=colors.HexColor('#0F172A'),
    alignment=1
)

sub_header_style = ParagraphStyle(
    'SubHeaderStyle',
    fontName='Arial',
    fontSize=9,
    leading=13,
    textColor=colors.HexColor('#64748B'),
    alignment=1
)

h1_style = ParagraphStyle(
    'H1Style',
    fontName='Arial-Bold',
    fontSize=10.5,
    leading=14,
    textColor=colors.HexColor('#0052FF'),
    spaceBefore=10,
    spaceAfter=5
)

body_style = ParagraphStyle(
    'BodyStyle',
    fontName='Arial',
    fontSize=9,
    leading=13.5,
    textColor=colors.HexColor('#1E293B')
)

table_header_style = ParagraphStyle(
    'TableHeaderStyle',
    fontName='Arial-Bold',
    fontSize=9,
    leading=12,
    textColor=colors.white,
    alignment=1
)

table_cell_style = ParagraphStyle(
    'TableCellStyle',
    fontName='Arial',
    fontSize=8.5,
    leading=11.5,
    textColor=colors.HexColor('#1E293B')
)

table_cell_center = ParagraphStyle(
    'TableCellCenter',
    fontName='Arial',
    fontSize=8.5,
    leading=11.5,
    textColor=colors.HexColor('#1E293B'),
    alignment=1
)

table_cell_bold_green = ParagraphStyle(
    'TableCellBoldGreen',
    fontName='Arial-Bold',
    fontSize=8.5,
    leading=11.5,
    textColor=colors.HexColor('#16A34A'),
    alignment=1
)

table_cell_bold_red = ParagraphStyle(
    'TableCellBoldRed',
    fontName='Arial-Bold',
    fontSize=8.5,
    leading=11.5,
    textColor=colors.HexColor('#DC2626'),
    alignment=1
)

story = []

# Header
story.append(Paragraph("TRƯỜNG ĐẠI HỌC VĂN LANG — KHOA CÔNG NGHỆ VÀ THIẾT KẾ", sub_header_style))
story.append(Paragraph("HỌC PHẦN: PHÁT TRIỂN HỆ THỐNG CMS VÀ THƯƠNG MẠI ĐIỆN TỬ (VLSC.V6)", sub_header_style))
story.append(Spacer(1, 4))
story.append(Paragraph("BÁO CÁO ĐỐI CHIẾU KẾT QUẢ TỐI ƯU HIỆU SUẤT (A/B TESTING)", header_style))
story.append(Paragraph("Dự án: Website DRX Store | Nhóm thực hiện: <b>Nhóm 1</b>", sub_header_style))
story.append(Spacer(1, 6))
story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor('#0052FF'), spaceBefore=0, spaceAfter=8))

# Phần 1: Bảng A/B Testing
story.append(Paragraph("1. BẢNG SỐ LIỆU ĐỐI CHIẾU A/B TESTING (DESKTOP)", h1_style))
story.append(Paragraph("Kết quả đo bằng công cụ Google Lighthouse / PageSpeed Insights trước và sau khi tối ưu:", body_style))
story.append(Spacer(1, 6))

table_data = [
    [
        Paragraph("<b>Hạng mục</b>", table_header_style),
        Paragraph("<b>Trước tối ưu</b>", table_header_style),
        Paragraph("<b>Sau tối ưu</b>", table_header_style),
        Paragraph("<b>Mức cải thiện</b>", table_header_style),
        Paragraph("<b>Đánh giá</b>", table_header_style)
    ],
    [
        Paragraph("<b>Điểm Performance</b>", table_cell_style),
        Paragraph("74 / 100", table_cell_bold_red),
        Paragraph("96 / 100", table_cell_bold_green),
        Paragraph("+22 điểm", table_cell_bold_green),
        Paragraph("Vùng Xanh", table_cell_center)
    ],
    [
        Paragraph("<b>LCP (Thời gian tải phần tử chính)</b>", table_cell_style),
        Paragraph("3.2 giây", table_cell_bold_red),
        Paragraph("0.9 giây", table_cell_bold_green),
        Paragraph("Giảm 2.3 giây", table_cell_bold_green),
        Paragraph("Đạt chuẩn (< 2.5s)", table_cell_center)
    ],
    [
        Paragraph("<b>FCP (Thời gian hiển thị đầu)</b>", table_cell_style),
        Paragraph("0.6 giây", table_cell_center),
        Paragraph("0.4 giây", table_cell_bold_green),
        Paragraph("Giảm 0.2 giây", table_cell_bold_green),
        Paragraph("Tốt", table_cell_center)
    ],
    [
        Paragraph("<b>CLS (Độ lệch bố cục)</b>", table_cell_style),
        Paragraph("0.00", table_cell_center),
        Paragraph("0.00", table_cell_bold_green),
        Paragraph("0.00", table_cell_bold_green),
        Paragraph("Ổn định (< 0.1)", table_cell_center)
    ],
    [
        Paragraph("<b>Dung lượng trang trung bình</b>", table_cell_style),
        Paragraph("3.8 MB", table_cell_bold_red),
        Paragraph("640 KB", table_cell_bold_green),
        Paragraph("Giảm ~83%", table_cell_bold_green),
        Paragraph("Nhẹ và mượt", table_cell_center)
    ]
]

t = Table(table_data, colWidths=[160, 85, 80, 95, 100])
t.setStyle(TableStyle([
    ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#0052FF')),
    ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
    ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
    ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor('#CBD5E1')),
    ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor('#F8FAFC')]),
    ('TOPPADDING', (0, 0), (-1, -1), 5),
    ('BOTTOMPADDING', (0, 0), (-1, -1), 5),
]))
story.append(t)
story.append(Spacer(1, 10))

# Phần 2: Các hành động đã thực hiện (Ngắn gọn, tự nhiên, bớt văn mẫu)
story.append(Paragraph("2. CÁC HÀNH ĐỘNG TỐI ƯU ĐÃ THỰC HIỆN", h1_style))
actions = [
    "<b>Nén hình ảnh:</b> Dùng Squoosh.app chuyển toàn bộ ảnh sản phẩm sang định dạng <b>.webp</b> (mức chất lượng 75-80), giúp giảm dung lượng từ 2MB xuống còn khoảng 40-70KB mỗi ảnh mà hình vẫn nét.",
    "<b>Bật Page Cache:</b> Cài plugin <b>WP-Optimize</b> và bật tính năng lưu bộ nhớ đệm trang tĩnh (Page Cache), giúp máy chủ phản hồi nhanh hơn mà không cần xử lý lại PHP và MySQL mỗi lần mở web.",
    "<b>Nén mã nguồn (Minify):</b> Bật tính năng gộp và nén file CSS/JS trong plugin để giảm kích thước tệp tải về và tránh nghẽn khi tải trang.",
    "<b>Bật Lazy Load:</b> Trì hoãn tải các hình ảnh ở phía dưới, ưu tiên tải nhanh nội dung và banner ở đầu trang.",
    "<b>Dọn dẹp cơ sở dữ liệu:</b> Dùng tab Database của WP-Optimize để dọn sạch các bản lưu nháp tự động (revisions) và dữ liệu tạm hết hạn (transients)."
]

for act in actions:
    story.append(Paragraph(f"• {act}", body_style))
    story.append(Spacer(1, 3))

story.append(Spacer(1, 8))

# Phần 3: Nhận xét và đánh giá (Ngắn gọn, chân thực)
story.append(Paragraph("3. NHẬN XÉT VÀ ĐÁNH GIÁ KẾT QUẢ", h1_style))
conclusion_text = (
    "Sau khi tối ưu, website chạy nhanh và mượt hơn rõ rệt. Điểm Performance tăng từ <b>74 lên 96 điểm</b>, "
    "thời gian tải phần tử chính (LCP) giảm từ <b>3.2s xuống 0.9s</b> (đạt chuẩn dưới 2.5s của Google). "
    "Việc chuyển ảnh sang định dạng WebP và bật cache giúp giảm hơn 80% dung lượng trang, "
    "các thao tác xem chi tiết sản phẩm và đặt hàng đều diễn ra ổn định, không bị giật lag."
)
story.append(Paragraph(conclusion_text, body_style))

doc.build(story)
print(f"Updated PDF generated successfully at {pdf_path}")
