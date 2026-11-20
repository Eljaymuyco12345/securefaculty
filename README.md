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
* **RA 10173 Compliance Audit Ledger**: Complete chronological audit trail (Who, What, When, IP, Action) satisfying Philippine Data Privacy Act standards with SHA-256 cryptographic seals.
* **Many-to-Many ($M:N$) Co-Authorship Mapping**: Allows single publications or certificates to link across multiple faculty co-authors with independent vault visibility and authorship disassociation.

---

## 💻 Full Setup Guide: Cloning to Another PC (Windows + XAMPP)

Follow this step-by-step checklist to set up and run the system on any new Windows computer.

### Step 1: Install Required Software
1. **XAMPP** (with PHP 8.1 or higher, Apache, and MySQL):
   * Download and install from [apachefriends.org](https://www.apachefriends.org/).
   * Default installation folder: `C:\xampp\`
2. **Python 3.10+**:
   * Download from [python.org](https://www.python.org/downloads/).
   * ⚠️ **IMPORTANT**: During installation, check the box **"Add python.exe to PATH"** before clicking Install.
3. **Git**:
   * Download and install from [git-scm.com](https://git-scm.com/).

---

### Step 2: Configure `php.ini` in XAMPP
Open `C:\xampp\php\php.ini` in a text editor (or click **Config -> PHP (php.ini)** in the XAMPP Control Panel) and verify/update these directives:

#### 1. Enable Required PHP Extensions
Search for each extension and remove the leading `;` if commented out:
```ini
extension=pdo_mysql
extension=pdo_sqlite
extension=sqlite3
extension=openssl
extension=mbstring
extension=curl
extension=fileinfo
extension=gd
```

#### 2. Increase Upload Limits for Multi-Page Certificate Scans & PDFs
```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 300
```

#### 3. Save and Restart Apache
In the XAMPP Control Panel, click **Stop** and then **Start** on Apache to apply the new configuration.

---

### Step 3: Install Required Python Libraries
Open PowerShell or Command Prompt as Administrator and run:
```bash
pip install pillow>=10.0.0 pypdf>=4.0.0 python-docx>=1.0.0 requests>=2.28.0 reportlab>=4.0.0
```
Verify Python is working in your terminal:
```bash
python --version
```

---

### Step 4: Clone the Repository into XAMPP
Open PowerShell or Command Prompt:
```bash
cd C:\xampp\htdocs
git clone https://github.com/tomatocultivator1988/testfork.git
cd testfork
```

---

### Step 5: Import the MySQL Database
1. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
2. Open your web browser and go to:
   ```
   http://localhost/phpmyadmin/
   ```
3. Click on the **SQL** tab at the top.
4. Open `C:\xampp\htdocs\testfork\sql\schema.sql` in Notepad, copy all text, paste it into the phpMyAdmin SQL query window, and click **Go**.
   * *This automatically provisions `securefaculty_db`, builds all relational tables, and seeds initial Dean and Faculty test accounts.*

---

### Step 6: Verify Storage Directories & Permissions
The system dynamically resolves storage paths relative to the project root (`app/config.php`), requiring no hardcoded drive letters. Confirm that the following directories exist (they are auto-created if missing):
* `C:\xampp\htdocs\testfork\storage\vault\` (stores encrypted AES-256 `.enc` documents)
* `C:\xampp\htdocs\testfork\storage\temp\` (stages temporary conversions during OCR)
* `C:\xampp\htdocs\testfork\storage\backups\` (stores encrypted system database snapshots)

---

### Step 7: (Optional) Set Root URL Auto-Redirect
If you want typing `http://localhost/` (or your Cloudflare Tunnel URL) to immediately load SecureFaculty instead of the default "Welcome to XAMPP" screen:

Edit `C:\xampp\htdocs\index.php` and replace its contents with:
```php
<?php
header('Location: /testfork/public/');
exit;
```

---

### Step 8: Run Built-In Verification Tests
In PowerShell, from the project root (`C:\xampp\htdocs\testfork`), run the automated test suites:

```powershell
# 1. Verify all PHP controllers, models, encryption, and DAOs:
php tests/run_all_unit_tests.php

# 2. Verify the Python OCR & Document Conversion engine:
python tests/run_python_unit_tests.py
```
*Both suites will report **100% Passed (0 Failures)**.*

---

### Step 9: Launch & Access SecureFaculty
Open your web browser and navigate to:
👉 **`http://localhost/testfork/public/`**

#### Default Login Credentials
| Role | Username | Password | Access Scope |
| :--- | :--- | :--- | :--- |
| **Dean (Admin)** | `dean` | `password` | Institutional Overview, All Faculty Vaults, Quota Monitoring, User Management, Full Audit Ledger, Backups |
| **Faculty Member** | `faculty` | `password` | Personal Digital Vault, Document Upload & OCR Ingestion, Co-Authorship Association, Export |
| **Faculty (Levy Aguilar)** | `laguilar` | `password` | Personal Vault |
| **Faculty (Rae Jan Corneja)** | `rcorneja` | `password` | Personal Vault |
| **Faculty (Eljay Muyco)** | `emuyco` | `password` | Personal Vault |

---

## 🐳 Alternative: 1-Command Docker Setup
For Linux, macOS, or Windows running Docker Desktop:
```bash
git clone https://github.com/tomatocultivator1988/testfork.git
cd testfork
docker compose up -d
```
Access via **`http://localhost:8080/testfork/`** (or **`http://localhost:8080/`**).

---

## 🌐 Cloudflare Tunnel Setup (Access Online Anywhere)
To securely host and share the system over the internet without port forwarding or static IP:
1. Install Cloudflared:
   ```powershell
   winget install --id Cloudflare.cloudflared
   ```
2. Start the tunnel pointing to XAMPP Apache:
   ```powershell
   cloudflared tunnel --url http://localhost:80
   ```
3. Copy the generated `https://<unique-name>.trycloudflare.com` URL and access SecureFaculty from any phone, laptop, or remote network.

---

## 🏛️ Directory Structure
```
testfork/
├── app/
│   ├── config.php                  # Central system configuration & credentials
│   ├── bootstrap.php               # Environment bootstrapping & OWASP headers
│   ├── Core/                       # Database connection factory (MySQL / SQLite Dual-Engine)
│   ├── Controllers/                # MVC Controllers (Auth, Dashboard, Vault, Upload, Users, Export, Audit)
│   ├── Models/                     # Domain models (CertificateModel, UserModel)
│   ├── DAO/                        # Data Access Objects (CertificateDAO, UserDAO, AuditLogDAO)
│   ├── Services/                   # Business services (AuthService, CryptoService, CertificateService, AuditLogger)
│   ├── Interfaces/                 # Architectural contracts
│   └── views/                      # Native PHP views & layouts (Dashboard, Vault, Upload, Users, Export, Audit)
├── python_engine/
│   └── conversion_gate.py          # Conversion Gate: Image optimizer, PDF/DOCX parser, OCR.space API
├── public/
│   ├── index.php                   # Modular Query Dispatcher & Front Controller
│   ├── login.php / logout.php      # Authentication endpoints
│   ├── dashboard.php               # Dean Command Center & Institutional Monitor
│   ├── vault.php                   # Personal Faculty Digital Vault & Ledger
│   ├── upload.php                  # Smart Ingestion Pipeline (Human-in-the-Loop Validation)
│   ├── view_document.php           # Just-in-Time Decryption streaming endpoint
│   ├── audit_logs.php              # Comprehensive RA 10173 Audit Trail explorer
│   ├── export.php                  # Official CSV & printable accreditation report
│   ├── users.php                   # User & Access Governance
│   └── api/                        # Ingestion, commit, and user AJAX endpoints
├── storage/
│   ├── vault/                      # Encrypted (.enc) payloads on disk (AES-256-CBC)
│   ├── temp/                       # Staging area for conversions (Zero plaintext persistence)
│   ├── backups/                    # Encrypted snapshot database dumps
│   └── securefaculty.sqlite        # SQLite local fallback database
├── sql/
│   └── schema.sql                  # MySQL schema for XAMPP deployment
├── tests/                          # 112 PHP micro-unit tests + 24 Python conversion tests
├── requirements.txt                # Python dependencies
└── README.md                       # Documentation & deployment guide
```

---

## 🛡️ Security Hardening & Audit Compliance

In accordance with institutional accreditation standards and Philippine Republic Act No. 10173 (Data Privacy Act of 2012), the SecureFaculty platform includes comprehensive security hardening against OWASP Top 10 vulnerabilities:

| Category | Vulnerability Mitigated | Technical Implementation |
| :--- | :--- | :--- |
| **CSRF Defense** | Cross-Site Request Forgery | Cryptographic synchronizer tokens (`AuthService::getCsrfToken()`) generated via `random_bytes(32)` and validated using timing-safe `hash_equals()` across all state-changing endpoints. |
| **Path Traversal** | Arbitrary File Deletion (`CWE-22`) | Sanitized vault file paths using `basename()` and validated canonical filesystem boundaries via `realpath()` prefix checking before executing file operations. |
| **XSS Mitigation** | Reflected / Stored Cross-Site Scripting | All dynamic inputs and view notifications are sanitized via `strip_tags()` and escaped with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. |
| **Upload DoS Guard** | Unrestricted Resource Consumption | Enforced strict server-side upload quotas (25 MiB single file limit, 10 MiB per-page image limit), zero-plaintext temp file lifecycle (`purgeStaleTempFiles`), and cryptographic magic-byte MIME inspection (`finfo`). |
| **Query Safety** | SQL Injection & ReDoS Wildcard Attacks | 100% parameterized PDO prepared statements with unique named parameter binding across all Data Access Objects. |
| **Session Integrity** | Session Fixation & Spoofing | Session ID regeneration (`session_regenerate_id(true)`) on successful authentication, strict session cookie settings (`HttpOnly`, `SameSite=Lax`, `Secure` when HTTPS). |
| **Audit Compliance** | Log Injection & Identity Tampering | Sanitized session context fields before committing to the immutable RA 10173 audit trail, backed by trusted-proxy IP resolution and SHA-256 cryptographic verification seals. |
| **Security Headers** | Clickjacking & MIME Confusion | Global OWASP headers dispatched at runtime (`X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`). |
