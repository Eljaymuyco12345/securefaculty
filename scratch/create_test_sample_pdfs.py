"""
Generate realistic sample PDFs for testing accreditation fields & OCR auto-detection:
1. sample_scopus_research_publication.pdf (Research Publication, International, Scopus Indexed)
2. sample_national_training_certificate.pdf (Training/Workshop, National, Iloilo City)
"""
import os
from reportlab.lib.pagesizes import letter, landscape
from reportlab.lib import colors
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, HRFlowable
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY, TA_LEFT

def create_research_publication_pdf(filename):
    doc = SimpleDocTemplate(
        filename,
        pagesize=letter,
        leftMargin=40,
        rightMargin=40,
        topMargin=40,
        bottomMargin=40
    )
    styles = getSampleStyleSheet()

    journal_header_style = ParagraphStyle(
        'JournalHeader',
        fontName='Helvetica-Bold',
        fontSize=15,
        leading=18,
        textColor=colors.HexColor('#0f2b48'),
        alignment=TA_CENTER
    )
    sub_header_style = ParagraphStyle(
        'SubHeader',
        fontName='Helvetica',
        fontSize=10,
        leading=13,
        textColor=colors.HexColor('#4b5563'),
        alignment=TA_CENTER
    )
    cert_title_style = ParagraphStyle(
        'CertTitle',
        fontName='Helvetica-Bold',
        fontSize=20,
        leading=24,
        textColor=colors.HexColor('#1e3a8a'),
        alignment=TA_CENTER
    )
    body_style = ParagraphStyle(
        'Body',
        fontName='Helvetica',
        fontSize=11,
        leading=16,
        textColor=colors.HexColor('#1f2937'),
        alignment=TA_JUSTIFY
    )
    paper_title_style = ParagraphStyle(
        'PaperTitle',
        fontName='Helvetica-Bold',
        fontSize=13,
        leading=17,
        textColor=colors.HexColor('#111827'),
        alignment=TA_CENTER
    )
    meta_style = ParagraphStyle(
        'Meta',
        fontName='Helvetica',
        fontSize=10,
        leading=14,
        textColor=colors.HexColor('#374151')
    )

    story = []

    # Journal Masthead
    story.append(Paragraph("INTERNATIONAL JOURNAL OF ADVANCED COMPUTING & INFORMATICS", journal_header_style))
    story.append(Spacer(1, 4))
    story.append(Paragraph("A Peer-Reviewed, Refereed International Research Journal | ISSN: 2319-7641<br/><b>Indexed in Scopus (Elsevier) &amp; Web of Science (WoS Master List)</b>", sub_header_style))
    story.append(Spacer(1, 10))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor('#1e3a8a'), spaceAfter=15))

    story.append(Spacer(1, 10))
    story.append(Paragraph("CERTIFICATE OF PUBLICATION", cert_title_style))
    story.append(Spacer(1, 6))
    story.append(Paragraph("Certificate Reference: <b>IJACI-2026-SCOPUS-8842</b>", sub_header_style))
    story.append(Spacer(1, 15))

    story.append(Paragraph(
        "This is to officially certify that the original research manuscript titled:",
        body_style
    ))
    story.append(Spacer(1, 10))

    # Paper Title Box
    title_box = Table(
        [[Paragraph("<b>“A SECURE CRYPTOGRAPHIC RECORDS MANAGEMENT AND ACCREDITATION ARCHITECTURE FOR PHILIPPINE STATE UNIVERSITIES”</b>", paper_title_style)]],
        colWidths=[520]
    )
    title_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f0f4f8')),
        ('BORDER', (0,0), (-1,-1), 1, colors.HexColor('#cbd5e1')),
        ('TOPPADDING', (0,0), (-1,-1), 10),
        ('BOTTOMPADDING', (0,0), (-1,-1), 10),
        ('LEFTPADDING', (0,0), (-1,-1), 15),
        ('RIGHTPADDING', (0,0), (-1,-1), 15),
    ]))
    story.append(title_box)
    story.append(Spacer(1, 15))

    story.append(Paragraph(
        "Authored by <b>Dr. Arlene S. Poblador, Prof. Levy Mikhail B. Aguilar, and Prof. Rae Jan B. Corneja</b> "
        "has been rigorously peer-reviewed by the International Editorial Board and officially published in:",
        body_style
    ))
    story.append(Spacer(1, 12))

    meta_table_data = [
        [Paragraph("<b>Publication Journal:</b>", meta_style), Paragraph("International Journal of Advanced Computing &amp; Informatics", meta_style)],
        [Paragraph("<b>Indexing Status:</b>", meta_style), Paragraph("<b>Scopus Indexed</b> (CiteScore: 4.8) &amp; Peer-Reviewed", meta_style)],
        [Paragraph("<b>Publication Scope:</b>", meta_style), Paragraph("<b>International</b> (Global Circulation)", meta_style)],
        [Paragraph("<b>Volume &amp; Issue:</b>", meta_style), Paragraph("Volume 14, Issue 3, pp. 102–118", meta_style)],
        [Paragraph("<b>Publication Date:</b>", meta_style), Paragraph("<b>October 15, 2026</b>", meta_style)],
        [Paragraph("<b>Issuing Publisher:</b>", meta_style), Paragraph("Global Science Publishing Group, Tokyo, Japan", meta_style)],
        [Paragraph("<b>Place Issued:</b>", meta_style), Paragraph("Tokyo, Japan", meta_style)]
    ]
    meta_table = Table(meta_table_data, colWidths=[150, 370])
    meta_table.setStyle(TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 4),
        ('RIGHTPADDING', (0,0), (-1,-1), 4),
        ('LINEBELOW', (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
    ]))
    story.append(meta_table)
    story.append(Spacer(1, 30))

    # Signatures
    sig_data = [
        [
            Paragraph("<b>Dr. Kenjiro Takahashi</b><br/>Editor-in-Chief, IJACI<br/>Tokyo, Japan", ParagraphStyle('Sig1', fontName='Helvetica', fontSize=9, leading=12, alignment=TA_CENTER)),
            Paragraph("<b>Prof. David Miller, Ph.D.</b><br/>International Advisory Chair<br/>Scopus Editorial Liaison", ParagraphStyle('Sig2', fontName='Helvetica', fontSize=9, leading=12, alignment=TA_CENTER))
        ]
    ]
    sig_table = Table(sig_data, colWidths=[260, 260])
    story.append(sig_table)

    doc.build(story)
    print(f"Created: {filename}")


def create_training_certificate_pdf(filename):
    doc = SimpleDocTemplate(
        filename,
        pagesize=landscape(letter),
        leftMargin=40,
        rightMargin=40,
        topMargin=35,
        bottomMargin=35
    )

    header_style = ParagraphStyle(
        'OrgHeader',
        fontName='Helvetica-Bold',
        fontSize=13,
        leading=16,
        textColor=colors.HexColor('#1e293b'),
        alignment=TA_CENTER
    )
    sub_org_style = ParagraphStyle(
        'SubOrg',
        fontName='Helvetica',
        fontSize=10,
        leading=13,
        textColor=colors.HexColor('#64748b'),
        alignment=TA_CENTER
    )
    cert_word_style = ParagraphStyle(
        'CertWord',
        fontName='Helvetica-Bold',
        fontSize=24,
        leading=28,
        textColor=colors.HexColor('#0369a1'),
        alignment=TA_CENTER
    )
    subtitle_style = ParagraphStyle(
        'CertSub',
        fontName='Helvetica',
        fontSize=11,
        leading=14,
        textColor=colors.HexColor('#475569'),
        alignment=TA_CENTER
    )
    recipient_style = ParagraphStyle(
        'Recipient',
        fontName='Helvetica-Bold',
        fontSize=18,
        leading=22,
        textColor=colors.HexColor('#0f172a'),
        alignment=TA_CENTER
    )
    body_style = ParagraphStyle(
        'BodyLandscape',
        fontName='Helvetica',
        fontSize=11,
        leading=17,
        textColor=colors.HexColor('#334155'),
        alignment=TA_CENTER
    )
    training_title_style = ParagraphStyle(
        'TrainingTitle',
        fontName='Helvetica-Bold',
        fontSize=13,
        leading=17,
        textColor=colors.HexColor('#0369a1'),
        alignment=TA_CENTER
    )

    story = []
    story.append(Paragraph("REPUBLIC OF THE PHILIPPINES<br/><b>COMMISSION ON HIGHER EDUCATION (CHED) REGION VI</b>", header_style))
    story.append(Spacer(1, 3))
    story.append(Paragraph("In Cooperation with Western Visayas State Universities and Colleges Association", sub_org_style))
    story.append(Spacer(1, 12))
    story.append(Paragraph("CERTIFICATE OF PARTICIPATION", cert_word_style))
    story.append(Spacer(1, 3))
    story.append(Paragraph("Certificate No: <b>CHED-RO6-NAT-2026-0492</b>", subtitle_style))
    story.append(Spacer(1, 15))

    story.append(Paragraph("This is proudly awarded to", subtitle_style))
    story.append(Spacer(1, 5))
    story.append(Paragraph("DR. ARLENE S. POBLADOR", recipient_style))
    story.append(Spacer(1, 2))
    story.append(HRFlowable(width="50%", thickness=1, color=colors.HexColor('#0284c7'), spaceAfter=10))

    story.append(Paragraph(
        "for actively participating and successfully completing the intensive three-day national program:",
        body_style
    ))
    story.append(Spacer(1, 8))

    training_box = Table(
        [[Paragraph("<b>“NATIONAL ADVANCED TRAINING-WORKSHOP ON OUTCOMES-BASED EDUCATION (OBE) AND CHED/AACCUP ACCREDITATION COMPLIANCE FOR SUC FACULTY”</b>", training_title_style)]],
        colWidths=[660]
    )
    training_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f0f9ff')),
        ('BORDER', (0,0), (-1,-1), 1, colors.HexColor('#bae6fd')),
        ('TOPPADDING', (0,0), (-1,-1), 8),
        ('BOTTOMPADDING', (0,0), (-1,-1), 8),
    ]))
    story.append(training_box)
    story.append(Spacer(1, 12))

    story.append(Paragraph(
        "Conducted as a <b>National Scope</b> academic capability training on <b>November 10, 2026</b>.<br/>"
        "Place Issued and Venue: <b>Grand Xing Imperial Hotel, Iloilo City, Philippines</b>.",
        body_style
    ))
    story.append(Spacer(1, 25))

    sig_data = [
        [
            Paragraph("<b>DR. RAUL C. ALVAREZ JR., CESO III</b><br/>Regional Director, CHED Regional Office VI", ParagraphStyle('Sig1', fontName='Helvetica', fontSize=9, leading=12, alignment=TA_CENTER)),
            Paragraph("<b>DR. VICTOR C. NAVARRO</b><br/>National Accreditation Focal Person", ParagraphStyle('Sig2', fontName='Helvetica', fontSize=9, leading=12, alignment=TA_CENTER))
        ]
    ]
    sig_table = Table(sig_data, colWidths=[350, 350])
    story.append(sig_table)

    doc.build(story)
    print(f"Created: {filename}")

if __name__ == "__main__":
    create_research_publication_pdf("sample_scopus_research_paper.pdf")
    create_training_certificate_pdf("sample_national_training_certificate.pdf")
