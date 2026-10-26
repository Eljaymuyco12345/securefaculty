# SecureFaculty: Systematic Records Management Portal
**With OCR-Powered Metadata Extraction and Secure Data Archiving**  
*College of Informatics and Computing Innovations (CICI)*  
*Iloilo State University of Fisheries Science and Technology (ISUFST) – Main Campus*

---

## 📌 Capstone Project Overview
SecureFaculty is a web-based institutional records management portal engineered to modernize academic document archiving, eliminate the "Administrative Latency Gap", and maintain real-time audit-readiness for AACCUP and CHED accreditations.

### Key Architectural Highlights
* **One-Gate Authentication & Role-Based Access Control (RBAC)**: Centralized gateway with Bcrypt password hashing separating Dean/Admin oversight from Faculty vaults.
* **Smart Ingestion Pipeline & Conversion Gate**: Multi-format ingestion supporting JPG, PNG, multi-page PDF, DOCX, and ZIP batch archives.
* **Python-Powered Conversion Gate & OCR Engine**: High-fidelity metadata extraction (Document Title, Issuing Authority, Date of Issuance, and Category Classification) with real-time OCR API quota tracking.
* **Human-in-the-Loop Verification Path**: Side-by-side verification interface allowing faculty to validate and approve extracted strings prior to database committal.
* **AES-256 Data-at-Rest Encryption & Just-in-Time Decryption**: All stored certificates are scrambled with AES-256 on disk and decrypted exclusively in memory during authorized user sessions.
* **RA 10173 Compliance Audit Ledger**: Complete chronological audit trail (Who, What, When, IP, Action) satisfying Philippine Data Privacy Act standards.
* **Many-to-Many ($M:N$) Co-Authorship Mapping**: Allows single publications or certificates to link across multiple faculty co-authors with independent vault visibility and authorship disassociation.

---

## 🚀 Deployment & Quick Start

### 🐳 Option A: 1-Command Docker Setup (Recommended for Any Computer / OS)
Run on Windows, Mac, or Linux with zero configuration. Docker automatically provisions PHP 8.2, Apache, MariaDB, Python 3, Poppler, and Tesseract OCR.

1. **Clone the repository:**
   ```bash
   git clone https://github.com/tomatocultivator1988/securefaculty.git
   cd securefaculty
   ```
2. **Start the containers:**
   ```bash
   docker compose up -d
   ```
3. **Open your browser:**
   👉 **`http://localhost:8080/securefaculty/`** (or **`http://localhost:8080/`**)

*To stop the containers:*
```bash
docker compose down
```

---

### 🏛️ Option B: XAMPP Setup (Windows Local Machine)

1. **Clone / Place Project into XAMPP `htdocs`:**
   ```cmd
   mklink /J "C:\xampp\htdocs\securefaculty" "C:\Panoy SSD\Secure"
   ```
   *(or copy the folder directly into `C:\xampp\htdocs\securefaculty`)*
2. **Start Services:**
   - Open **XAMPP Control Panel**
   - Click **Start** on **Apache**
   - Click **Start** on **MySQL**
3. **Import Database Schema:**
   - Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
   - Create database: `securefaculty_db`
   - Import `sql/schema.sql` (pre-seeded with all default accounts, faculty profiles, and indexes)
4. **Open your browser:**
   👉 **`http://localhost/securefaculty/`**

---

### 💻 Option C: Standalone PHP CLI Built-in Server

If testing without Apache or Docker, run the built-in router:
```bash
php -S 127.0.0.1:8000 router.php
```
Access at: **`http://127.0.0.1:8000`**

---

## 🔑 Default Evaluation Accounts

| Role | Username | Password | Purpose |
| :--- | :--- | :--- | :--- |
| **Dean / Administrator** | `dean` | `admin123` | Institutional Quota Monitor, Master Archive, Audit Trail, Accreditation Export, User Governance |
| **Faculty Member** | `mikhail` | `faculty123` | Personal Vault, Batch Ingestion, Milestone Tracking, Co-Authorship |
| **Faculty Member** | `raejan` | `faculty123` | Personal Vault & Ingestion |
| **Faculty Member** | `eljay` | `faculty123` | Personal Vault & Ingestion |

---

## 📂 Project Directory Structure

```
Secure/
├── app/
│   ├── config.php                 # App settings, encryption key, OCR quota threshold
│   ├── bootstrap.php              # Central autoloader & PSR-4 class mapping
│   ├── Core/                      # Database connection factory (SQLite / MySQL)
│   ├── Controllers/               # MVC Controllers (Auth, Dashboard, Vault, Upload, Users, Export, Audit)
│   ├── Models/                    # Domain models (CertificateModel, UserModel)
│   ├── DAO/                       # Data Access Objects (CertificateDAO, UserDAO, AuditLogDAO)
│   ├── Services/                  # Business services (AuthService, CryptoService, CertificateService, AuditLogger)
│   ├── Interfaces/                # Architectural contracts
│   └── views/                     # Native PHP views & layouts (Dashboard, Vault, Upload, Users, Export, Audit)
├── python_engine/
│   └── conversion_gate.py         # Conversion Gate: Image optimizer, PDF/DOCX parser, OCR
├── public/
│   ├── index.php                  # One-Gate Login Portal
│   ├── login.php / logout.php     # Session endpoints
│   ├── dashboard.php              # Dean Command Center & Quota Monitor
│   ├── vault.php                  # Faculty Personal Digital Vault & Institutional Ledger
│   ├── upload.php                 # Smart Pipeline Ingestion Gateway (H-I-T-L Validation)
│   ├── view_document.php          # Just-in-Time Decryption streaming endpoint
│   ├── audit_logs.php             # Comprehensive Audit Trail explorer
│   ├── export.php                 # Official CSV & printable accreditation report
│   ├── users.php                  # User & Access Governance
│   └── api/
│       ├── upload_handler.php     # Upload bridge to Python Conversion Gate
│       ├── commit_record.php      # AES-256 archive & DB committal endpoint
│       └── user_handler.php       # User governance AJAX endpoint
├── storage/
│   ├── vault/                     # Encrypted (.enc) payloads on disk (AES-256-CBC)
│   ├── temp/                      # Staging area for conversions (Zero plaintext persistence)
│   └── securefaculty.sqlite       # Local database
├── sql/
│   └── schema.sql                 # MySQL schema for XAMPP deployment
├── bin/
│   └── cloudflared.exe            # Cloudflare Tunnel executable
├── scratch/                       # Automated regression and verification test suites
├── run_server.py                  # Server launcher
├── start_securefaculty.bat        # Windows batch launcher
└── SecureFacultyRevisedComplete.docx # Capstone manuscript
```

---

## 🌐 Clean Production URLs

The system utilizes an Apache `mod_rewrite` engine and standalone `router.php`, eliminating unsightly `.php` extensions and `/public/` subpaths:

| Destination | Clean Production Route |
| :--- | :--- |
| **Authentication Portal** | `http://localhost/securefaculty/` |
| **Dean's Overview Dashboard** | `http://localhost/securefaculty/dashboard` |
| **Personal Vault & Ledger** | `http://localhost/securefaculty/vault` |
| **Smart Ingestion Pipeline** | `http://localhost/securefaculty/upload` |
| **User Management Registry** | `http://localhost/securefaculty/users` |
| **Accreditation Export** | `http://localhost/securefaculty/export` |
| **RA 10173 Audit Ledger** | `http://localhost/securefaculty/audit` |
