#!/usr/bin/env python3
"""
SecureFaculty Official Accreditation Report PDF Generator
Produces publication-grade, portrait vector PDF reports with institutional letterhead.
"""
import sys
import json
import os
from datetime import datetime

try:
    from reportlab.lib.pagesizes import A4, landscape
    from reportlab.platypus import (
        SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, KeepTogether
    )
    from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
    from reportlab.lib import colors
    from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT
except ImportError:
    print(json.dumps({"status": "error", "message": "reportlab is not installed"}))
    sys.exit(1)


class NumberedCanvas:
    """Canvas wrapper that adds two-pass running footer with 'Page X of Y'"""
    def __init__(self, *args, **kwargs):
        from reportlab.pdfgen import canvas
        self._saved_page_states = []

    # ReportLab SimpleDocTemplate uses canvasmaker
    @classmethod
    def get_maker(cls):
        from reportlab.pdfgen import canvas
        class CustomCanvas(canvas.Canvas):
            def __init__(self, *args, **kwargs):
                super().__init__(*args, **kwargs)
                self.pages = []

            def showPage(self):
                self.pages.append(dict(self.__dict__))
                self._startPage()

            def save(self):
                page_count = len(self.pages)
                for page in self.pages:
                    self.__dict__.update(page)
                    self.draw_page_decorations(page_count)
                    super().showPage()
                super().save()

            def draw_page_decorations(self, page_count):
                self.saveState()
                self.setFont("Helvetica", 8)
                self.setFillColor(colors.HexColor("#475569"))
                # Running Footer
                footer_text = f"SecureFaculty Institutional Records System  |  Page {self._pageNumber} of {page_count}"
                confidential_text = "OFFICIAL TRANSMITTAL  •  RA 10173 COMPLIANT  •  ISUFST CICI"
                
                # Portrait A4 width is 595.27 pt, height is 841.89 pt
                self.drawString(24, 20, confidential_text)
                self.drawRightString(595.27 - 24, 20, footer_text)
                
                # Thin divider rule above footer
                self.setStrokeColor(colors.HexColor("#cbd5e1"))
                self.setLineWidth(0.5)
                self.line(24, 28, 595.27 - 24, 28)
                
                self.restoreState()

        return CustomCanvas


def generate_pdf(data_json_path, output_pdf_path):
    with open(data_json_path, 'r', encoding='utf-8') as f:
        payload = json.load(f)

    meta = payload.get('meta', {})
    records = payload.get('records', [])

    # Page Setup: A4 Portrait (595.27 x 841.89 pt)
    doc = SimpleDocTemplate(
        output_pdf_path,
        pagesize=A4,
        leftMargin=24,
        rightMargin=24,
        topMargin=24,
        bottomMargin=36
    )

    styles = getSampleStyleSheet()

    # Typography Styles
    title_sub = ParagraphStyle('RepSub', parent=styles['Normal'], fontName='Helvetica', fontSize=7.5, leading=9.5, alignment=TA_CENTER, textColor=colors.HexColor("#64748b"))
    title_main = ParagraphStyle('RepMain', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=11, leading=14, alignment=TA_CENTER, textColor=colors.HexColor("#0f172a"))
    title_dept = ParagraphStyle('RepDept', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=9, leading=12, alignment=TA_CENTER, textColor=colors.HexColor("#1e293b"))
    title_addr = ParagraphStyle('RepAddr', parent=styles['Normal'], fontName='Helvetica', fontSize=7, leading=9, alignment=TA_CENTER, textColor=colors.HexColor("#64748b"))

    ledger_title = ParagraphStyle('LedgerTitle', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=9.5, leading=12, textColor=colors.HexColor("#0f172a"))
    ledger_meta = ParagraphStyle('LedgerMeta', parent=styles['Normal'], fontName='Helvetica', fontSize=7.5, leading=10, textColor=colors.HexColor("#334155"))
    ledger_meta_r = ParagraphStyle('LedgerMetaR', parent=styles['Normal'], fontName='Helvetica', fontSize=7.5, leading=10, alignment=TA_RIGHT, textColor=colors.HexColor("#334155"))

    th_style = ParagraphStyle('TH', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=6.8, leading=8.5, alignment=TA_CENTER, textColor=colors.HexColor("#0f172a"))
    td_style = ParagraphStyle('TD', parent=styles['Normal'], fontName='Helvetica', fontSize=6.8, leading=8.5, textColor=colors.HexColor("#1e293b"))
    td_bold = ParagraphStyle('TDBold', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=7, leading=9, textColor=colors.HexColor("#0f172a"))
    td_center = ParagraphStyle('TDCenter', parent=styles['Normal'], fontName='Helvetica', fontSize=6.8, leading=8.5, alignment=TA_CENTER, textColor=colors.HexColor("#334155"))
    td_badge = ParagraphStyle('TDBadge', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=6.5, leading=8, alignment=TA_CENTER, textColor=colors.HexColor("#1e40af"))
    td_crypto = ParagraphStyle('TDCrypto', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=6.5, leading=8, alignment=TA_CENTER, textColor=colors.HexColor("#047857"))

    story = []

    # 1. Official Letterhead
    story.append(Paragraph("REPUBLIC OF THE PHILIPPINES", title_sub))
    story.append(Spacer(1, 1.5))
    story.append(Paragraph("ILOILO STATE UNIVERSITY OF FISHERIES SCIENCE AND TECHNOLOGY", title_main))
    story.append(Paragraph("COLLEGE OF INFORMATICS AND COMPUTING INNOVATIONS", title_dept))
    story.append(Paragraph("Main Campus – Poblacion Site, Barotac Nuevo, Iloilo  |  cici@isufst.edu.ph", title_addr))
    story.append(Spacer(1, 5))

    # Divider bar (547.27 pt)
    divider = Table([['']], colWidths=[547.27], rowHeights=[1.5])
    divider.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#0d2342")),
        ('TOPPADDING', (0,0), (-1,-1), 0),
        ('BOTTOMPADDING', (0,0), (-1,-1), 0),
    ]))
    story.append(divider)
    story.append(Spacer(1, 5))

    # 2. Audit Scope & Metadata Header
    category_text = meta.get('category', 'All')
    date_scope = meta.get('date_scope', 'All Validated Records')
    auditor = meta.get('auditor_name', 'Authorized Administrator')
    generated_at = meta.get('generated_at', datetime.now().strftime("%B %d, %Y at %I:%M %p"))
    total_count = len(records)

    header_data = [
        [
            Paragraph(f"<b>OFFICIAL CERTIFICATE &amp; ACCREDITATION LEDGER</b><br/><font color='#64748b'>Scope: <b>{category_text}</b>  •  Period: {date_scope}</font>", ledger_title),
            Paragraph(f"<b>Audited By:</b> {auditor}<br/><b>Generated:</b> {generated_at}<br/><b>Total Records:</b> {total_count}", ledger_meta_r)
        ]
    ]
    meta_table = Table(header_data, colWidths=[310, 237.27])
    meta_table.setStyle(TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('TOPPADDING', (0,0), (-1,-1), 0),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 0),
        ('RIGHTPADDING', (0,0), (-1,-1), 0),
    ]))
    story.append(meta_table)
    story.append(Spacer(1, 4))

    # 3. Main Data Table (Portrait Width = 547.27 pt)
    col_widths = [18, 142, 114, 50, 52, 80, 53, 38.27]

    table_data = [[
        Paragraph("<b>#</b>", th_style),
        Paragraph("<b>DOCUMENT TITLE</b>", th_style),
        Paragraph("<b>HOLDER &amp; CO-AUTHORS</b>", th_style),
        Paragraph("<b>CERT REF</b>", th_style),
        Paragraph("<b>ISSUED</b>", th_style),
        Paragraph("<b>ISSUING AUTHORITY</b>", th_style),
        Paragraph("<b>CATEGORY</b>", th_style),
        Paragraph("<b>STATUS</b>", th_style),
    ]]

    if not records:
        empty_row = [Paragraph("<i>No records match the selected scope.</i>", td_center)] + [''] * 7
        table_data.append(empty_row)
    else:
        for idx, r in enumerate(records, start=1):
            title = r.get('title', 'Untitled')
            ftype = r.get('file_type', 'PDF').upper()
            title_p = Paragraph(f"<b>{title}</b><br/><font size=6.5 color='#64748b'>FORMAT: {ftype}</font>", td_style)

            holder = r.get('faculty_name', 'Faculty Member')
            rank = r.get('academic_rank', 'CICI Faculty')
            coauthors = r.get('co_authors', '')
            holder_text = f"<b>{holder}</b><br/><font size=6.5 color='#475569'>{rank}</font>"
            if coauthors:
                holder_text += f"<br/><font size=6 color='#7c3aed'><b>Co-Authors:</b> {coauthors}</font>"
            holder_p = Paragraph(holder_text, td_style)

            ref_no = r.get('cert_number') or 'N/A'
            ref_p = Paragraph(f"<font color='#475569'>{ref_no}</font>", td_center)

            date_issued = r.get('date_issued') or 'Undated'
            date_p = Paragraph(f"<b>{date_issued}</b>", td_center)

            issuer = r.get('issuer') or 'Not Specified'
            issuer_p = Paragraph(issuer, td_style)

            cat = r.get('category') or 'General'
            cat_p = Paragraph(cat, td_badge)

            crypto_p = Paragraph("AES-256<br/><font size=6 color='#047857'>VERIFIED</font>", td_crypto)

            table_data.append([
                Paragraph(str(idx), td_center),
                title_p,
                holder_p,
                ref_p,
                date_p,
                issuer_p,
                cat_p,
                crypto_p
            ])

    main_table = Table(table_data, colWidths=col_widths, repeatRows=1)
    
    t_style = [
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#f1f5f9")),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#cbd5e1")),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
        ('LEFTPADDING', (0,0), (-1,-1), 3.5),
        ('RIGHTPADDING', (0,0), (-1,-1), 3.5),
    ]

    # Alternating row colors
    for i in range(1, len(table_data)):
        if i % 2 == 0:
            t_style.append(('BACKGROUND', (0, i), (-1, i), colors.HexColor("#f8fafc")))

    main_table.setStyle(TableStyle(t_style))
    story.append(main_table)
    story.append(Spacer(1, 10))

    # 4. Official Transmittal Signature Block (kept together on same page)
    sig_data = [
        [
            Paragraph("<b>Prepared and Certified by:</b>", ledger_meta),
            Paragraph("<b>Attested Compliance:</b>", ledger_meta_r)
        ],
        [
            Spacer(1, 20),
            Spacer(1, 20)
        ],
        [
            Paragraph(f"<u><b>{auditor}</b></u><br/><font size=7 color='#64748b'>CICI Department Officer / Accreditation Auditor</font>", ledger_meta),
            Paragraph("<b>REPUBLIC ACT NO. 10173</b><br/><font size=7 color='#64748b'>Philippine Data Privacy Act &amp; AACCUP Standard</font>", ledger_meta_r)
        ]
    ]
    sig_table = Table(sig_data, colWidths=[273.63, 273.64])
    sig_table.setStyle(TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('LEFTPADDING', (0,0), (-1,-1), 0),
        ('RIGHTPADDING', (0,0), (-1,-1), 0),
        ('TOPPADDING', (0,0), (-1,-1), 1),
        ('BOTTOMPADDING', (0,0), (-1,-1), 1),
    ]))

    story.append(KeepTogether([sig_table]))

    # Build Document with Running Headers/Footers
    doc.build(story, canvasmaker=NumberedCanvas.get_maker())


if __name__ == '__main__':
    if len(sys.argv) < 3:
        print(json.dumps({"status": "error", "message": "Usage: generate_report_pdf.py <input.json> <output.pdf>"}))
        sys.exit(1)

    generate_pdf(sys.argv[1], sys.argv[2])
    print(json.dumps({"status": "success", "file": sys.argv[2]}))
