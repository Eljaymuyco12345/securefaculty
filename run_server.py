import os
import sys
import subprocess
import shutil

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
PUBLIC_DIR = os.path.join(BASE_DIR, 'public')

# Find PHP executable across common Windows environments
php_exe = shutil.which('php')
if not php_exe:
    candidates = [
        r"C:\xampp\php\php.exe",
        r"C:\php\php.exe",
        os.path.expandvars(r"%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"),
        os.path.expandvars(r"%ProgramFiles%\PHP\php.exe"),
    ]
    for cand in candidates:
        if os.path.exists(cand):
            php_exe = cand
            break

if not php_exe:
    sys.exit(
        "Error: PHP executable not found.\n"
        "Please ensure PHP 8.0+ is installed:\n"
        "  - If using XAMPP, PHP is located at C:\\xampp\\php\\php.exe\n"
        "  - Or install via winget: 'winget install PHP.PHP.8.3'\n"
        "  - Or download from https://windows.php.net/download/ and add to PATH."
    )

print("=" * 65)
print(" SECUREFACULTY: SYSTEMATIC RECORDS MANAGEMENT PORTAL")
print(" CICI Department - ISUFST Main Campus")
print("=" * 65)
print(f"[*] PHP Executable: {php_exe}")
print(f"[*] Public Directory: {PUBLIC_DIR}")
print(f"[*] Python Bridge: {sys.executable}")
print("-" * 65)
print("Demo Evaluation Accounts:")
print("  - Dean / Administrator:  dean    / admin123")
print("  - Faculty Member:        mikhail / faculty123")
print("  - Faculty Member:        raejan  / faculty123")
print("  - Faculty Member:        eljay   / faculty123")
print("-" * 65)
print("Server URL: http://localhost:8000")
print("Press Ctrl+C to terminate the local server.")
print("=" * 65)

ROUTER_FILE = os.path.join(BASE_DIR, 'router.php')

# Launch PHP built-in web server with clean URL routing
cmd = [php_exe, "-S", "127.0.0.1:8000", ROUTER_FILE]
try:
    subprocess.run(cmd)
except KeyboardInterrupt:
    print("\nSecureFaculty server stopped.")
