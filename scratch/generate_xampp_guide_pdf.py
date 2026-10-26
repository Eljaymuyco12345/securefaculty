#!/usr/bin/env python3
"""
Generates the official SecureFaculty Full System Setup & Deployment Guide PDF.
Standard Local Production Setup: XAMPP (Apache + MySQL / MariaDB + PHP 8.2) & Python Engine (No Docker).
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
        footer_left = "ISUFST College of Informatics and Computing Innovations  •  SecureFaculty Deployment Guide"
        footer_right = f"Page {self._pageNumber} of {page_count}"

        # A4 width = 595.27, height = 841.89
        self.drawString(36, 20, footer_left)
        self.drawRightString(595.27 - 36, 20, footer_right)

        # Divider line
        self.setStrokeColor(colors.HexColor("#cbd5e1"))
        self.setLineWidth(0.5)
        self.line(36, 29, 595.27 - 36, 29)

        self.restoreState()


def build_xampp_guide_pdf(output_path):
    doc = SimpleDocTemplate(
        output_path,
        pagesize=A4,
        leftMargin=36,
        rightMargin=36,
        topMargin=28,
        bottomMargin=38
    )

    styles = getSampleStyleSheet()

    # Custom Typography Styles
    inst_header = ParagraphStyle(
        'InstHeader', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=9, leading=12,
        alignment=TA_CENTER, textColor=colors.HexColor("#0f172a")
    )
    inst_sub = ParagraphStyle(
        'InstSub', parent=styles['Normal'],
        fontName='Helvetica', fontSize=7.5, leading=10,
        alignment=TA_CENTER, textColor=colors.HexColor("#475569")
    )
    doc_title = ParagraphStyle(
        'DocTitle', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=14, leading=17,
        alignment=TA_CENTER, textColor=colors.HexColor("#1e3a8a")
    )
    doc_subtitle = ParagraphStyle(
        'DocSub', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=9, leading=12,
        alignment=TA_CENTER, textColor=colors.HexColor("#0284c7")
    )
    badge_style = ParagraphStyle(
        'Badge', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=7.5, leading=9.5,
        alignment=TA_CENTER, textColor=colors.HexColor("#065f46")
    )
    section_h1 = ParagraphStyle(
        'SecH1', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=10, leading=13,
        textColor=colors.HexColor("#0f172a"), keepWithNext=True
    )
    body_p = ParagraphStyle(
        'BodyP', parent=styles['Normal'],
        fontName='Helvetica', fontSize=8, leading=11,
        textColor=colors.HexColor("#334155")
    )
    code_block = ParagraphStyle(
        'CodeBlock', parent=styles['Normal'],
        fontName='Courier-Bold', fontSize=7.5, leading=10.5,
        textColor=colors.HexColor("#38bdf8")
    )
    code_inline = ParagraphStyle(
        'CodeInline', parent=styles['Normal'],
        fontName='Courier-Bold', fontSize=7.5, leading=10,
        textColor=colors.HexColor("#0f172a")
    )
    callout_text = ParagraphStyle(
        'CalloutText', parent=styles['Normal'],
        fontName='Helvetica', fontSize=7.5, leading=10.5,
        textColor=colors.HexColor("#1e293b")
    )
    table_cell = ParagraphStyle(
        'TableCell', parent=styles['Normal'],
        fontName='Helvetica', fontSize=7.5, leading=10,
        textColor=colors.HexColor("#1e293b")
    )
    table_header = ParagraphStyle(
        'TableHead', parent=styles['Normal'],
        fontName='Helvetica-Bold', fontSize=7.5, leading=10,
        textColor=colors.white
    )

    story = []

    # =========================================================================
    # PAGE 1: PREREQUISITES, XAMPP CONFIGURATION & DATABASE IMPORT
    # =========================================================================

    # 1. Institutional Letterhead Header
    story.append(Paragraph("ILOILO STATE UNIVERSITY OF FISHERIES SCIENCE AND TECHNOLOGY", inst_header))
    story.append(Paragraph("COLLEGE OF INFORMATICS AND COMPUTING INNOVATIONS (CICI)", inst_header))
    story.append(Paragraph("Tiwi Campus, Barotac Nuevo, Iloilo | Capstone Project Research Group", inst_sub))
    story.append(Spacer(1, 4))
    story.append(HRFlowable(width="100%", thickness=1.2, color=colors.HexColor("#1e3a8a"), spaceAfter=6))

    # 2. Document Title Banner
    story.append(Paragraph("SECUREFACULTY: FULL SYSTEM DEPLOYMENT GUIDE", doc_title))
    story.append(Paragraph("Standard Local Production Setup: XAMPP (Apache + MySQL) & Python Engine (No Docker)", doc_subtitle))
    story.append(Spacer(1, 4))

    # Metadata Banner Box
    meta_content = [[
        Paragraph("<b>DOCUMENT:</b> Host Deployment Manual", badge_style),
        Paragraph("<b>WEB STACK:</b> Apache 2.4 + PHP 8.2 (XAMPP)", badge_style),
        Paragraph("<b>DATABASE:</b> MySQL / MariaDB (InnoDB)", badge_style),
    ]]
    meta_table = Table(meta_content, colWidths=[174, 174, 175])
    meta_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#ecfdf5")),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor("#10b981")),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('TOPPADDING', (0,0), (-1,-1), 2.5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2.5),
    ]))
    story.append(meta_table)
    story.append(Spacer(1, 8))

    # Overview Callout
    overview_html = (
        "<b>Operational Scope:</b> This guide outlines the exact, foolproof procedure to install and run the complete "
        "SecureFaculty systematic records management portal directly on a client machine using standard <b>XAMPP (Apache & MySQL)</b> "
        "and <b>Python 3.10+</b>. No Docker or virtualization is required. All data is persisted in a high-performance relational "
        "MySQL/MariaDB database with strict foreign keys, multi-author indexes, and AES-256 vault encryption."
    )
    overview_box = Table([[Paragraph(overview_html, callout_text)]], colWidths=[523])
    overview_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f8fafc")),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor("#cbd5e1")),
        ('PADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(overview_box)
    story.append(Spacer(1, 8))

    # SECTION 1: Software Requirements & Exact Versions
    story.append(Paragraph("1. Software Requirements & Exact Versions Needed", section_h1))
    story.append(Paragraph("Download and install the following two components on the client computer:", body_p))
    story.append(Spacer(1, 3))

    sw_data = [
        [Paragraph("Software Component", table_header), Paragraph("Exact Tested Version", table_header), Paragraph("Included Modules", table_header), Paragraph("Official Download Source", table_header)],
        [
            Paragraph("<b>XAMPP for Windows</b>", table_cell),
            Paragraph("<b>8.2.12</b> (or 8.1.x – 8.2.x)", table_cell),
            Paragraph("Apache 2.4.58, PHP 8.2.12, MariaDB 10.4.32, phpMyAdmin", table_cell),
            Paragraph("apachefriends.org/download.html", table_cell)
        ],
        [
            Paragraph("<b>Python for Windows</b>", table_cell),
            Paragraph("<b>3.11.x</b> or <b>3.12.x</b> (64-bit)", table_cell),
            Paragraph("pip, Pillow, PyPDF, python-docx, ReportLab 4.0+", table_cell),
            Paragraph("python.org/downloads/windows", table_cell)
        ],
    ]
    sw_table = Table(sw_data, colWidths=[120, 110, 173, 120])
    sw_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#1e3a8a")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#cbd5e1")),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('PADDING', (0,0), (-1,-1), 3.5),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    story.append(sw_table)
    story.append(Spacer(1, 4))
    story.append(Paragraph(
        "<b>CRITICAL INSTALLATION FLAG:</b> When installing Python, you <u>MUST</u> check the box: <b>\"Add python.exe to PATH\"</b> "
        "at the bottom of the first setup window. This enables PHP and the web server to invoke the conversion engine.",
        ParagraphStyle('Crit', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=7.5, leading=10, textColor=colors.HexColor("#b91c1c"))
    ))
    story.append(Spacer(1, 8))

    # SECTION 2: Codebase Location
    story.append(Paragraph("2. Project Directory Placement", section_h1))
    story.append(Paragraph("Copy or clone the entire SecureFaculty project folder into the XAMPP web root directory:", body_p))
    story.append(Spacer(1, 3))

    path_box = Table([[
        Paragraph(
            "<b>Target Directory:</b> &nbsp; <font color='#38bdf8'><b>C:\\xampp\\htdocs\\securefaculty</b></font><br/>"
            "<font color='#94a3b8'>Ensure folders 'storage\\vault' and 'storage\\temp' exist inside this directory.</font>",
            code_block
        )
    ]], colWidths=[523])
    path_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#0f172a")),
        ('PADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(path_box)
    story.append(Spacer(1, 8))

    # SECTION 3: PHP Configuration
    story.append(Paragraph("3. PHP Configuration (php.ini)", section_h1))
    story.append(Paragraph("Open <b><code>C:\\xampp\\php\\php.ini</code></b> (or click <b>Config -> PHP (php.ini)</b> in XAMPP Control Panel):", body_p))
    story.append(Spacer(1, 3))

    php_box = Table([[
        Paragraph(
            "<font color='#94a3b8'>// 1. Verify these extensions are enabled (remove the leading ';' semicolon if present):</font><br/>"
            "<font color='#38bdf8'>extension=pdo_mysql</font> &nbsp;&nbsp;&nbsp;&nbsp; <font color='#64748b'># Required for MySQL/MariaDB database</font><br/>"
            "<font color='#38bdf8'>extension=openssl</font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font color='#64748b'># Required for AES-256-CBC cryptographic vault</font><br/>"
            "<font color='#38bdf8'>extension=fileinfo</font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font color='#64748b'># Required for server-side MIME type validation</font><br/>"
            "<font color='#38bdf8'>extension=curl</font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font color='#64748b'># Required for OCR.space cloud API failover</font><br/>"
            "<font color='#38bdf8'>extension=mbstring</font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font color='#64748b'># Multibyte character encoding</font><br/>"
            "<font color='#38bdf8'>extension=gd</font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font color='#64748b'># Image thumbnail and preview support</font><br/>"
            "<font color='#94a3b8'>// 2. Set upload limits for large academic manuscripts & certificates:</font><br/>"
            "<font color='#fde047'>upload_max_filesize = 50M</font> &nbsp;&nbsp;&nbsp;&nbsp; <font color='#fde047'>post_max_size = 50M</font> &nbsp;&nbsp;&nbsp;&nbsp; <font color='#fde047'>memory_limit = 256M</font>",
            code_block
        )
    ]], colWidths=[523])
    php_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#0f172a")),
        ('PADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(php_box)
    story.append(Spacer(1, 8))

    # SECTION 4: Apache Configuration
    story.append(Paragraph("4. Apache URL Rewrite Configuration (httpd.conf)", section_h1))
    story.append(Paragraph("In <b><code>C:\\xampp\\apache\\conf\\httpd.conf</code></b>, ensure mod_rewrite is enabled for clean URLs:", body_p))
    story.append(Spacer(1, 3))

    apache_box = Table([[
        Paragraph(
            "<font color='#94a3b8'>// Ensure mod_rewrite is enabled (uncomment line by removing '#'):</font><br/>"
            "<font color='#38bdf8'>LoadModule rewrite_module modules/mod_rewrite.so</font><br/>"
            "<font color='#94a3b8'>// In Directory \"C:/xampp/htdocs\", verify AllowOverride is set to All:</font><br/>"
            "<font color='#fde047'>AllowOverride All</font>",
            code_block
        )
    ]], colWidths=[523])
    apache_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#0f172a")),
        ('PADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(apache_box)
    story.append(Spacer(1, 8))

    # SECTION 5: Database Setup & Schema Import
    story.append(Paragraph("5. MySQL Database Setup & Schema Import (MariaDB)", section_h1))
    story.append(Paragraph("Import the pre-configured database schema with bcrypt credentials and sample research publication:", body_p))
    story.append(Spacer(1, 3))

    db_steps = [
        [
            Paragraph("<b>Step 5.1</b>", ParagraphStyle('StpA', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=7.5, textColor=colors.HexColor("#1e3a8a"))),
            Paragraph("Open <b>XAMPP Control Panel</b> and click <b>Start</b> for both <b>Apache</b> and <b>MySQL</b>.", body_p)
        ],
        [
            Paragraph("<b>Step 5.2</b>", ParagraphStyle('StpB', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=7.5, textColor=colors.HexColor("#1e3a8a"))),
            Paragraph("Open browser and go to: <b><code>http://localhost/phpmyadmin/</code></b>", body_p)
        ],
        [
            Paragraph("<b>Step 5.3</b>", ParagraphStyle('StpC', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=7.5, textColor=colors.HexColor("#1e3a8a"))),
            Paragraph("Click the <b>Import</b> tab in the top navigation bar.", body_p)
        ],
        [
            Paragraph("<b>Step 5.4</b>", ParagraphStyle('StpD', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=7.5, textColor=colors.HexColor("#1e3a8a"))),
            Paragraph("Click <b>Choose File</b>, select <b><code>C:\\xampp\\htdocs\\securefaculty\\sql\\schema.sql</code></b>, and click <b>Import</b> (or <b>Go</b>).", body_p)
        ],
    ]
    db_step_table = Table(db_steps, colWidths=[55, 468])
    db_step_table.setStyle(TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 1.5),
    ]))
    story.append(db_step_table)
    story.append(Spacer(1, 3))
    story.append(Paragraph(
        "<i>* CLI Alternative: Open CMD and execute: <code>C:\\xampp\\mysql\\bin\\mysql -u root < C:\\xampp\\htdocs\\securefaculty\\sql\\schema.sql</code></i>",
        ParagraphStyle('CliNote', parent=styles['Normal'], fontName='Helvetica-Oblique', fontSize=7, leading=9.5, textColor=colors.HexColor("#64748b"))
    ))

    # PAGE BREAK TO PAGE 2
    story.append(PageBreak())

    # =========================================================================
    # PAGE 2: PYTHON LIBRARIES, ACCESS, ACCOUNTS, DEMO & DIAGNOSTICS
    # =========================================================================

    story.append(Paragraph("SECUREFACULTY: ACCESS, CREDENTIALS & VERIFICATION", doc_title))
    story.append(Paragraph("Python Dependencies, Portal URLs, Demonstration Accounts, and System Checks", doc_subtitle))
    story.append(Spacer(1, 4))
    story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor("#1e3a8a"), spaceAfter=8))

    # SECTION 6: Python Dependencies Installation
    story.append(Paragraph("6. Install Python Dependencies (Run Once in Terminal)", section_h1))
    story.append(Paragraph("Open Command Prompt or PowerShell inside <b><code>C:\\xampp\\htdocs\\securefaculty</code></b> and run:", body_p))
    story.append(Spacer(1, 3))

    pip_box = Table([[
        Paragraph(
            "<font color='#38bdf8'>pip install -r requirements.txt</font><br/>"
            "<font color='#94a3b8'># Or manually: pip install pillow>=10.0 pypdf>=4.0 python-docx>=1.0 requests>=2.28 reportlab>=4.0</font>",
            code_block
        )
    ]], colWidths=[523])
    pip_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#0f172a")),
        ('PADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(pip_box)
    story.append(Spacer(1, 8))

    # SECTION 7: Web Access URL
    story.append(Paragraph("7. Accessing SecureFaculty in Web Browser", section_h1))
    story.append(Paragraph("Once Apache and MySQL are running in XAMPP, open your browser and navigate to:", body_p))
    story.append(Spacer(1, 3))

    url_table = Table([[
        Paragraph(
            "<font size='9.5'><b>Official Application URL:</b> &nbsp; "
            "<a href='http://localhost/securefaculty/'><font color='#1e3a8a'><b><u>http://localhost/securefaculty/</u></b></font></a> &nbsp; "
            "(or <code>http://127.0.0.1/securefaculty/</code>)</font>",
            body_p
        )
    ]], colWidths=[523])
    url_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#eff6ff")),
        ('BOX', (0,0), (-1,-1), 1.5, colors.HexColor("#3b82f6")),
        ('PADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(url_table)
    story.append(Spacer(1, 8))

    # SECTION 8: Demonstration Accounts Table
    story.append(Paragraph("8. Pre-Configured Demonstration Accounts (RBAC)", section_h1))
    story.append(Paragraph("The database comes pre-seeded with Dean administrative clearance and faculty test profiles:", body_p))
    story.append(Spacer(1, 3))

    acct_data = [
        [Paragraph("Role / Clearance", table_header), Paragraph("Username", table_header), Paragraph("Password", table_header), Paragraph("Full Name & Position", table_header), Paragraph("Permissions & Workspace Scope", table_header)],
        [
            Paragraph("<b>Dean (Admin)</b>", table_cell),
            Paragraph("<b>dean</b>", code_inline),
            Paragraph("<b>admin123</b>", code_inline),
            Paragraph("Dr. Arlene S. Poblador<br/><i>Dean, CICI</i>", table_cell),
            Paragraph("Full clearance, administrative dashboard, user registry, compliance audit ledger, department vault", table_cell)
        ],
        [
            Paragraph("Faculty Member", table_cell),
            Paragraph("<b>mikhail</b>", code_inline),
            Paragraph("<b>faculty123</b>", code_inline),
            Paragraph("Prof. Levy Mikhail B. Aguilar<br/><i>Assistant Professor II</i>", table_cell),
            Paragraph("Personal encrypted vault, document ingestion gateway, primary author on Capstone publication", table_cell)
        ],
        [
            Paragraph("Faculty Member", table_cell),
            Paragraph("<b>raejan</b>", code_inline),
            Paragraph("<b>faculty123</b>", code_inline),
            Paragraph("Prof. Rae Jan B. Corneja<br/><i>Instructor I</i>", table_cell),
            Paragraph("Personal encrypted vault, co-author attribution badges on joint research publication", table_cell)
        ],
        [
            Paragraph("Faculty Member", table_cell),
            Paragraph("<b>eljay</b>", code_inline),
            Paragraph("<b>faculty123</b>", code_inline),
            Paragraph("Prof. Eljay Muyco<br/><i>Instructor I</i>", table_cell),
            Paragraph("Personal encrypted vault, co-author attribution badges on joint research publication", table_cell)
        ],
    ]
    acct_table = Table(acct_data, colWidths=[80, 55, 60, 130, 198])
    acct_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#0f172a")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#cbd5e1")),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.white, colors.HexColor("#f8fafc")]),
        ('PADDING', (0,0), (-1,-1), 3.5),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    story.append(acct_table)
    story.append(Spacer(1, 8))

    # SECTION 9: Feature Demonstration Checklist
    story.append(Paragraph("9. Key Feature Demonstration Checklist", section_h1))
    story.append(Paragraph("Test these core workflows to demonstrate the system during capstone defense or client review:", body_p))
    story.append(Spacer(1, 3))

    features = [
        [
            Paragraph("<b>Smart Pipeline</b>", table_header),
            Paragraph("<b>Digital Vault (AES-256)</b>", table_header),
            Paragraph("<b>Audit Trail (RA 10173)</b>", table_header),
            Paragraph("<b>Accreditation Report</b>", table_header)
        ],
        [
            Paragraph("Click <b>Ingestion Pipeline</b> -> 'Load Demo Sample' -> Stage 1 to 4 telemetry displays OCR extraction & H-I-T-L validation.", table_cell),
            Paragraph("Zero plaintext on disk. Clicking 'Decrypt & View' recovers payload directly in volatile RAM.", table_cell),
            Paragraph("Log in as Dean -> <b>Audit Trail</b>. Immutable compliance ledger tracking IPs, users, and actions.", table_cell),
            Paragraph("Click <b>Accreditation Report</b> -> PDF. Generates publication-grade A4 Portrait PDF report with letterhead.", table_cell)
        ]
    ]
    feat_table = Table(features, colWidths=[130, 131, 131, 131])
    feat_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor("#1e3a8a")),
        ('BOX', (0,0), (-1,-1), 0.5, colors.HexColor("#cbd5e1")),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#e2e8f0")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [colors.HexColor("#f8fafc")]),
        ('PADDING', (0,0), (-1,-1), 4),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(feat_table)
    story.append(Spacer(1, 8))

    # SECTION 10: Troubleshooting Quick Reference
    story.append(Paragraph("10. Troubleshooting Quick Reference", section_h1))
    troubles = (
        "• <b>Apache Port 80 / 443 Conflict:</b> If Apache fails to start, click <b>Config -> Service & Port Settings</b> in XAMPP and change the Main Port to <code>8080</code>. Access via <code>http://localhost:8080/securefaculty/</code>.<br/>"
        "• <b>MySQL Port 3306 Conflict:</b> If another MySQL service is running, stop it via Windows Services (<code>services.msc</code>) or use the XAMPP MySQL service.<br/>"
        "• <b>'python' command not recognized:</b> Re-run Python installer, select <i>'Modify'</i>, and check <i>'Add Python to environment variables'</i>.<br/>"
        "• <b>HTTP 404 on clean URLs (/dashboard, /vault):</b> Verify in <code>httpd.conf</code> that <code>LoadModule rewrite_module</code> is enabled and <code>AllowOverride All</code> is set."
    )
    trouble_box = Table([[Paragraph(troubles, callout_text)]], colWidths=[523])
    trouble_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#fef2f2")),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor("#f87171")),
        ('PADDING', (0,0), (-1,-1), 5),
    ]))
    story.append(trouble_box)
    story.append(Spacer(1, 6))

    # Institutional Sign-off Footer
    signoff = Paragraph(
        "<b>ISUFST College of Informatics and Computing Innovations (CICI)</b><br/>"
        "Barotac Nuevo Campus, Iloilo, Philippines  •  Capstone Project Research & Development<br/>"
        "Compliant with Republic Act No. 10173 (Philippine Data Privacy Act of 2012)",
        ParagraphStyle('Signoff', parent=styles['Normal'], fontName='Helvetica', fontSize=7, leading=9, alignment=TA_CENTER, textColor=colors.HexColor("#64748b"))
    )
    story.append(signoff)

    # Build Document
    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"[OK] Successfully built: {output_path}")


if __name__ == '__main__':
    target = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'SecureFaculty_XAMPP_MySQL_Setup_Guide.pdf')
    build_xampp_guide_pdf(target)
