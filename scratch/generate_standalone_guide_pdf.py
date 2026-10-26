#!/usr/bin/env python3
"""
Generates the official SecureFaculty Standalone One-Click Deployment Guide PDF.
Zero-Docker, Zero-XAMPP Native Windows Setup.
A crisp, 2-page institutional manual for client computers.
"""

import os
import sys
from reportlab.lib.pagesizes import A4
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, HRFlowable
)
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT, TA_JUSTIFY
from reportlab.pdfgen import canvas


class NumberedCanvas(canvas.Canvas):
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
            self.draw_decorations(page_count)
            super().showPage()
        super().save()

    def draw_decorations(self, page_count):
        self.saveState()
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#64748b"))

        # Running Footer
        footer_left = "ISUFST College of Informatics and Computing Innovations  •  SecureFaculty Operations Manual"
        footer_right = f"Page {self._pageNumber} of {page_count}"

        # A4 width = 595.27, height = 841.89
        self.drawString(36, 22, footer_left)
        self.drawRightString(595.27 - 36, 22, footer_right)

        # Divider line
        self.setStrokeColor(colors.HexColor("#cbd5e1"))
        self.setLineWidth(0.5)
        self.line(36, 32, 595.27 - 36, 32)

        self.restoreState()


def build_guide_pdf(output_path):
    doc = SimpleDocTemplate(
        output_path,
        pagesize=A4,
        leftMargin=36,
        rightMargin=36,
        topMargin=32,
        bottomMargin=42
    )

    styles = getSampleStyleSheet()

    # Custom Typography Styles
    inst_header = ParagraphStyle(
        'InstHeader', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=9.5, leading=12.5,
        alignment=TA_CENTER, textColor=colors.HexColor("#0f172a")
    )
    inst_sub = ParagraphStyle(
        'InstSub', parent=styles['Normal'],
        fontName='Helvetica', fontSize=7.5, leading=10.5,
        alignment=TA_CENTER, textColor=colors.HexColor("#475569")
    )
    doc_title = ParagraphStyle(
        'DocTitle', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=14.5, leading=18,
        alignment=TA_CENTER, textColor=colors.HexColor("#1e3a8a")
    )
    doc_subtitle = ParagraphStyle(
        'DocSub', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=9.5, leading=13,
        alignment=TA_CENTER, textColor=colors.HexColor("#0284c7")
    )
    badge_style = ParagraphStyle(
        'Badge', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=7.5, leading=9.5,
        alignment=TA_CENTER, textColor=colors.HexColor("#065f46")
    )
    section_h1 = ParagraphStyle(
        'SecH1', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=10.5, leading=14,
        textColor=colors.HexColor("#0f172a"), keepWithNext=True
    )
    body_p = ParagraphStyle(
        'BodyP', parent=styles['Normal'],
        fontName='Helvetica', fontSize=8.5, leading=12,
        textColor=colors.HexColor("#334155")
    )
    code_block = ParagraphStyle(
        'CodeBlock', parent=styles['Normal'],
        fontName='Courier-Bold', fontSize=8, leading=11,
        textColor=colors.HexColor("#38bdf8")
    )
    callout_text = ParagraphStyle(
        'CalloutText', parent=styles['Normal'],
        fontName='Helvetica', fontSize=8, leading=11,
        textColor=colors.HexColor("#1e293b")
    )
    table_cell = ParagraphStyle(
        'TableCell', parent=styles['Normal'],
        fontName='Helvetica', fontSize=8, leading=10.5,
        textColor=colors.HexColor("#1e293b")
    )
    table_header = ParagraphStyle(
        'TableHead', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=8, leading=10.5,
        textColor=colors.white
    )

    story = []

    # =========================================================================
    # PAGE 1: SETUP & ONE-CLICK EXECUTION
    # =========================================================================

    # 1. Institutional Letterhead Header
    story.append(Paragraph("ILOILO STATE UNIVERSITY OF FISHERIES SCIENCE AND TECHNOLOGY", inst_header))
    story.append(Paragraph("COLLEGE OF INFORMATICS AND COMPUTING INNOVATIONS (CICI)", inst_header))
    story.append(Paragraph("Tiwi Campus, Barotac Nuevo, Iloilo | Capstone Project Research Group", inst_sub))
    story.append(Spacer(1, 6))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor("#1e3a8a"), spaceAfter=8))

    # 2. Document Title Banner
    story.append(Paragraph("SECUREFACULTY: STANDALONE ONE-CLICK SETUP", doc_title))
    story.append(Paragraph("Zero-Docker & Zero-XAMPP Native Windows Deployment Guide", doc_subtitle))
    story.append(Spacer(1, 6))

    # Metadata Banner Box
    meta_content = [[
        Paragraph("<b>DOCUMENT:</b> Operations Guide", badge_style),
        Paragraph("<b>ENVIRONMENT:</b> Native Windows 10/11", badge_style),
        Paragraph("<b>DATABASE:</b> Embedded SQLite Engine", badge_style),
    ]]
    meta_table = Table(meta_content, colWidths=[174, 174, 175])
    meta_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#ecfdf5")),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor("#10b981")),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('TOPPADDING', (0,0), (-1,-1), 3),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3),
    ]))
    story.append(meta_table)
    story.append(Spacer(1, 10))

    # Overview Callout
    overview_html = (
        "<b>Summary:</b> This official guide allows you to run the complete SecureFaculty system directly on any "
        "client Windows computer without Docker, without WSL2, and without installing database servers like MySQL "
        "or XAMPP. Utilizing native Windows PHP and the zero-configuration embedded SQLite engine, the portal boots "
        "instantly in <b>under 3 seconds</b>."
    )
    overview_box = Table([[Paragraph(overview_html, callout_text)]], colWidths=[523])
    overview_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f8fafc")),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor("#cbd5e1")),
        ('PADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(overview_box)
    story.append(Spacer(1, 10))

    # SECTION 1: System Prerequisites
    story.append(Paragraph("1. System Prerequisites (Pre-installed on Client PC)", section_h1))
    story.append(Paragraph("The host computer only requires standard native PHP and Python runtimes:", body_p))
    story.append(Spacer(1, 4))

    prereq_data = [
        [Paragraph("Runtime Component", table_header), Paragraph("Required Version", table_header), Paragraph("Verification Command", table_header)],
        [Paragraph("<b>PHP for Windows</b>", table_cell), Paragraph("PHP 8.0, 8.1, 8.2, or 8.3", table_cell), Paragraph("<font color='#0284c7'>php -v</font>", table_cell)],
        [Paragraph("<b>Python for Windows</b>", table_cell), Paragraph("Python 3.10, 3.11, 3.12, or 3.13", table_cell), Paragraph("<font color='#0284c7'>python --version</font>", table_cell)],
    ]
    prereq_table = Table(prereq_data, colWidths=[160, 180, 183])
    prereq_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#1e3a8a")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#cbd5e1")),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('PADDING', (0,0), (-1,-1), 4),
    ]))
    story.append(prereq_table)
    story.append(Spacer(1, 10))

    # SECTION 2: Python Libraries
    story.append(Paragraph("2. Install Required Python Libraries", section_h1))
    story.append(Paragraph("Open Command Prompt or PowerShell in the project folder and run:", body_p))
    story.append(Spacer(1, 4))

    cmd_box = Table([[
        Paragraph("pip install -r requirements.txt<br/><font color='#94a3b8'># Installs: pillow (images), pypdf (PDF parsing), python-docx, requests, reportlab (PDF engine)</font>", code_block)
    ]], colWidths=[523])
    cmd_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#0f172a")),
        ('PADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(cmd_box)
    story.append(Spacer(1, 10))

    # SECTION 3: Step-by-Step Configuration
    story.append(Paragraph("3. Configure Embedded SQLite (Single-Line Change)", section_h1))
    story.append(Paragraph("Configure the database driver to use self-initializing SQLite instead of MySQL:", body_p))
    story.append(Spacer(1, 4))

    step1_content = [
        [
            Paragraph("<b>Step A</b>", ParagraphStyle('Stp', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=8, textColor=colors.HexColor("#1e3a8a"))),
            Paragraph("Open <b><code>app/config.php</code></b> in any text editor (Notepad, VS Code).", body_p)
        ],
        [
            Paragraph("<b>Step B</b>", ParagraphStyle('Stp', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=8, textColor=colors.HexColor("#1e3a8a"))),
            Paragraph("Locate <b>Line 21</b> and set the database driver to <code>'sqlite'</code>:", body_p)
        ]
    ]
    step_table = Table(step1_content, colWidths=[60, 463])
    step_table.setStyle(TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2),
    ]))
    story.append(step_table)
    story.append(Spacer(1, 4))

    cfg_box = Table([[
        Paragraph(
            "<font color='#94a3b8'>// Line 21 in app/config.php</font><br/>"
            "<b><font color='#38bdf8'>define</font></b>(<font color='#a5f3fc'>'DB_DRIVER'</font>, <font color='#fde047'>'sqlite'</font>);",
            code_block
        )
    ]], colWidths=[523])
    cfg_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#0f172a")),
        ('PADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(cfg_box)
    story.append(Spacer(1, 4))
    story.append(Paragraph(
        "<i>* Note: SQLite requires zero database server setup. The database file <b><code>storage/securefaculty.sqlite</code></b> "
        "and all tables/schemas are created automatically on the very first HTTP request.</i>",
        ParagraphStyle('Note', parent=styles['Normal'], fontName='Helvetica-Oblique', fontSize=7.5, leading=10, textColor=colors.HexColor("#64748b"))
    ))
    story.append(Spacer(1, 10))

    # SECTION 4: One-Click Startup
    story.append(Paragraph("4. Launch the System (One-Click)", section_h1))
    story.append(Paragraph("Start the web portal using either method:", body_p))
    story.append(Spacer(1, 4))

    launch_data = [
        [
            Paragraph("<b>Method A (One-Click)</b>", ParagraphStyle('MA', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=8.5, textColor=colors.HexColor("#065f46"))),
            Paragraph("Double-click <b><code>start_securefaculty.bat</code></b> in the root project folder.", body_p)
        ],
        [
            Paragraph("<b>Method B (Command Line)</b>", ParagraphStyle('MB', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=8.5, textColor=colors.HexColor("#1e3a8a"))),
            Paragraph("Run in terminal: <code>python run_server.py</code>", body_p)
        ]
    ]
    launch_table = Table(launch_data, colWidths=[140, 383])
    launch_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (0,0), colors.HexColor("#f0fdf4")),
        ('BACKGROUND', (0,1), (0,1), colors.HexColor("#f8fafc")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#cbd5e1")),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('PADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(launch_table)
    story.append(Spacer(1, 10))

    # SECTION 5: Web Access URL
    story.append(Paragraph("5. Open Portal in Web Browser", section_h1))
    story.append(Paragraph("Open Google Chrome or Microsoft Edge and navigate to:", body_p))
    story.append(Spacer(1, 4))

    url_box = Table([[
        Paragraph(
            "<font size='10.5'><b>Live Web URL:</b> &nbsp; <font color='#1e3a8a'><b><u>http://localhost:8000/</u></b></font> &nbsp; (or <code>http://127.0.0.1:8000/</code>)</font>",
            body_p
        )
    ]], colWidths=[523])
    url_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#eff6ff")),
        ('BOX', (0,0), (-1,-1), 1.5, colors.HexColor("#3b82f6")),
        ('PADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(url_box)

    # PAGE BREAK TO PAGE 2
    story.append(PageBreak())

    # =========================================================================
    # PAGE 2: EVALUATION ACCOUNTS, FEATURE VERIFICATION & TROUBLESHOOTING
    # =========================================================================

    story.append(Paragraph("SECUREFACULTY: EVALUATION PROTOCOLS & TROUBLESHOOTING", doc_title))
    story.append(Paragraph("Account Credentials, Verification Checklist, and System Diagnostics", doc_subtitle))
    story.append(Spacer(1, 6))
    story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#1e3a8a"), spaceAfter=10))

    # SECTION 6: Evaluation Accounts
    story.append(Paragraph("6. Pre-Configured Demonstration Accounts", section_h1))
    story.append(Paragraph("Log in using the pre-seeded credentials with role-based access control (RBAC):", body_p))
    story.append(Spacer(1, 4))

    acct_data = [
        [Paragraph("Role / Clearance", table_header), Paragraph("Username", table_header), Paragraph("Password", table_header), Paragraph("Permissions & Scoped Features", table_header)],
        [
            Paragraph("<b>Dean (Admin)</b>", table_cell),
            Paragraph("<b>dean</b>", table_cell),
            Paragraph("<b>admin123</b>", table_cell),
            Paragraph("Administrative clearance, centralized dashboard, user registry, compliance audit ledger, department-wide vault", table_cell)
        ],
        [
            Paragraph("Faculty Member", table_cell),
            Paragraph("<b>mikhail</b>", table_cell),
            Paragraph("<b>faculty123</b>", table_cell),
            Paragraph("Personal encrypted vault, co-authored publications, certificate ingestion pipeline", table_cell)
        ],
        [
            Paragraph("Faculty Member", table_cell),
            Paragraph("<b>raejan</b>", table_cell),
            Paragraph("<b>faculty123</b>", table_cell),
            Paragraph("Personal encrypted vault, document ingestion gateway, co-authorship badges", table_cell)
        ],
        [
            Paragraph("Faculty Member", table_cell),
            Paragraph("<b>eljay</b>", table_cell),
            Paragraph("<b>faculty123</b>", table_cell),
            Paragraph("Personal encrypted vault, publication archive, multi-author research records", table_cell)
        ],
    ]
    acct_table = Table(acct_data, colWidths=[95, 70, 75, 283])
    acct_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#0f172a")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#cbd5e1")),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor("#f8fafc")]),
        ('PADDING', (0,0), (-1,-1), 4.5),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    story.append(acct_table)
    story.append(Spacer(1, 12))

    # SECTION 7: Feature Verification Walkthrough
    story.append(Paragraph("7. Demonstration & Feature Verification Checklist", section_h1))
    story.append(Paragraph("Perform these tests during evaluation to demonstrate key system capabilities:", body_p))
    story.append(Spacer(1, 4))

    demo_steps = [
        [
            Paragraph("<b>Feature</b>", table_header),
            Paragraph("Navigation Path", table_header),
            Paragraph("Expected Behavior & Compliance Standard", table_header)
        ],
        [
            Paragraph("<b>Smart Pipeline</b>", table_cell),
            Paragraph("Click <b>Ingestion Pipeline</b> in nav", table_cell),
            Paragraph("Click 'Load Demo Sample' -> Stage 1 to 4 telemetry displays OCR metadata extraction & H-I-T-L validation.", table_cell)
        ],
        [
            Paragraph("<b>AES-256 Vault</b>", table_cell),
            Paragraph("Click <b>Digital Vault</b>", table_cell),
            Paragraph("Zero plaintext storage at rest. Clicking 'Decrypt & View' recovers binary payload in volatile RAM.", table_cell)
        ],
        [
            Paragraph("<b>Co-Authorship</b>", table_cell),
            Paragraph("Search for 'SECUREFACULTY'", table_cell),
            Paragraph("Attributed to Mikhail, Rae Jan, and Eljay simultaneously with interactive co-author distinction badges.", table_cell)
        ],
        [
            Paragraph("<b>Audit Ledger</b>", table_cell),
            Paragraph("Log in as Dean -> <b>Audit Trail</b>", table_cell),
            Paragraph("Immutable compliance ledger tracking IP, timestamp, role, and actions in full compliance with RA 10173.", table_cell)
        ],
        [
            Paragraph("<b>Portrait A4 PDF</b>", table_cell),
            Paragraph("Click <b>Accreditation Report</b> -> PDF", table_cell),
            Paragraph("Downloads official standalone A4 Portrait PDF with university letterhead, borders, and running footers.", table_cell)
        ]
    ]
    demo_table = Table(demo_steps, colWidths=[100, 140, 283])
    demo_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#1e3a8a")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#cbd5e1")),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor("#f8fafc")]),
        ('PADDING', (0,0), (-1,-1), 4),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(demo_table)
    story.append(Spacer(1, 12))

    # SECTION 8: Troubleshooting Quick Reference
    story.append(Paragraph("8. Troubleshooting & Windows Diagnostics", section_h1))
    diag_items = (
        "• <b>'php' or 'python' is not recognized:</b> Re-run the installer and check <i>'Add to PATH'</i>, or restart Command Prompt.<br/>"
        "• <b>Port 8000 already in use:</b> Launch on a different port: <code>php -S 127.0.0.1:8080 router.php</code> and browse to <code>http://localhost:8080/</code>.<br/>"
        "• <b>SQLite PDO extension missing:</b> In <code>php.ini</code>, ensure <code>extension=pdo_sqlite</code> and <code>extension=sqlite3</code> are uncommented.<br/>"
        "• <b>OpenSSL extension missing:</b> In <code>php.ini</code>, ensure <code>extension=openssl</code> is uncommented for AES-256 vault encryption.<br/>"
        "• <b>ReportLab PDF compilation:</b> Ensure ReportLab is installed: <code>python -c \"import reportlab; print('ReportLab OK')\"</code>."
    )
    diag_box = Table([[Paragraph(diag_items, callout_text)]], colWidths=[523])
    diag_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#fef2f2")),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor("#f87171")),
        ('PADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(diag_box)
    story.append(Spacer(1, 10))

    # Institutional Sign-off Footer
    signoff = Paragraph(
        "<b>ISUFST College of Informatics and Computing Innovations (CICI)</b><br/>"
        "Barotac Nuevo Campus, Iloilo, Philippines  •  Capstone Project Research & Development<br/>"
        "Compliant with Republic Act No. 10173 (Philippine Data Privacy Act of 2012)",
        ParagraphStyle('Signoff', parent=styles['Normal'], fontName='Helvetica', fontSize=7, leading=9.5, alignment=TA_CENTER, textColor=colors.HexColor("#64748b"))
    )
    story.append(signoff)

    # Build Document
    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"[OK] Successfully built: {output_path}")


if __name__ == '__main__':
    target = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'SecureFaculty_Standalone_Setup_Guide.pdf')
    build_guide_pdf(target)
