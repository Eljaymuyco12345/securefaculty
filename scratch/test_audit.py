import requests
import re
import json

BASE_URL = "http://127.0.0.1:8000"

def run_tests():
    print("=== STARTING AUTOMATED AUDIT TEST SUITE ===")
    
    # 1. Test unauthenticated redirects
    print("\n[1] Testing Unauthenticated Access Gates...")
    routes = ['dashboard.php', 'vault.php', 'upload.php', 'users.php', 'audit_logs.php', 'export.php']
    for r in routes:
        res = requests.get(f"{BASE_URL}/{r}", allow_redirects=False)
        assert res.status_code == 302, f"Expected 302 for {r}, got {res.status_code}"
        assert 'index.php' in res.headers.get('Location', ''), f"Expected redirect to index.php for {r}"
        print(f"  [OK] {r} correctly redirected (302)")

    # 2. Test Faculty Login and RBAC isolation
    print("\n[2] Testing Faculty Login & RBAC Isolation...")
    s_faculty = requests.Session()
    login_res = s_faculty.post(f"{BASE_URL}/login.php", data={'username': 'mikhail', 'password': 'faculty123'}, allow_redirects=False)
    assert login_res.status_code == 302 and 'vault.php' in login_res.headers.get('Location', ''), "Faculty login failed"
    print("  [OK] Mikhail logged in -> redirected to vault.php")

    # Faculty attempting Dean resources
    for dean_route in ['dashboard.php', 'users.php', 'audit_logs.php']:
        res = s_faculty.get(f"{BASE_URL}/{dean_route}", allow_redirects=False)
        assert res.status_code == 403, f"Expected 403 for faculty accessing {dean_route}, got {res.status_code}"
        print(f"  [OK] Faculty blocked from {dean_route} (403 Forbidden)")

    # 3. Test Dean Login and Access
    print("\n[3] Testing Dean Login & Access...")
    s_dean = requests.Session()
    login_res = s_dean.post(f"{BASE_URL}/login.php", data={'username': 'dean', 'password': 'admin123'}, allow_redirects=False)
    assert login_res.status_code == 302 and 'dashboard.php' in login_res.headers.get('Location', ''), "Dean login failed"
    print("  [OK] Dean logged in -> redirected to dashboard.php")

    res = s_dean.get(f"{BASE_URL}/dashboard.php")
    assert res.status_code == 200, "Dean failed to access dashboard.php"
    print("  [OK] Dean successfully accessed dashboard.php (200 OK)")

    # 4. Check CSRF on API commit
    print("\n[4] Testing CSRF Validation on Commit API...")
    res = s_dean.post(f"{BASE_URL}/api/commit_record.php", json={'title': 'Test'}, headers={'Content-Type': 'application/json'})
    assert res.status_code == 403, f"Expected 403 without CSRF token, got {res.status_code}"
    print("  [OK] /api/commit_record.php correctly rejected request without CSRF token (403)")

    # 5. Check Brute-Force Rate Limiting (Missing Feature Test)
    print("\n[5] Testing Login Rate Limiting / Brute Force Protection...")
    s_attacker = requests.Session()
    statuses = []
    for i in range(15):
        r = s_attacker.post(f"{BASE_URL}/login.php", data={'username': 'attacker_brute_test', 'password': f'wrong_{i}'}, allow_redirects=False)
        statuses.append(r.status_code)
    # Check if any request got 429 Too Many Requests or account lockout
    if 429 in statuses:
        print("  [OK] Rate limiting active: 429 received")
    else:
        print("  [WARN] VULNERABILITY FOUND: No rate limiting on login! 15 consecutive failed logins all returned 302.")

    # 6. Test Document Viewing Authorization (IDOR test)
    print("\n[6] Testing Document IDOR & Cryptographic Isolation...")
    # Faculty Mikhail is user 2. Let's find a certificate that does NOT belong to Mikhail and Mikhail is NOT co-author.
    # Certificate 3 belongs to Dean (user 1).
    res = s_faculty.get(f"{BASE_URL}/view_document.php?id=3")
    if res.status_code == 403:
        print("  [OK] Faculty blocked from viewing Dean's unshared certificate #3 (403 Access Denied)")
    else:
        print(f"  [WARN] VULNERABILITY FOUND: Faculty got status {res.status_code} accessing certificate #3!")

    # 7. Test Batch Commit Date Loss Bug
    print("\n[7] Testing Date Retention when force_duplicate=1 (Duplicate Override / Batch)...")
    # Fetch CSRF token from vault page
    vault_page = s_dean.get(f"{BASE_URL}/vault.php").text
    m_csrf = re.search(r'window\.CSRF_TOKEN\s*=\s*"([^"]+)"', vault_page)
    if not m_csrf:
        m_csrf = re.search(r'name="csrf-token"\s+content="([^"]+)"', vault_page)
    csrf_token = m_csrf.group(1) if m_csrf else ""
    print(f"  Dean CSRF token: {csrf_token[:8]}...")

    # Staging a demo certificate
    test_payload = {
        'temp_filename': 'sample_demo_audit_test.pdf',
        'original_name': 'audit_test_cert.pdf',
        'file_type': 'pdf',
        'file_size': 1024,
        'title': 'Audit Test Certificate',
        'issuer': 'CICI Testing Lab',
        'date_issued': '2024-05-15',
        'category': 'Seminar',
        'force_duplicate': 1,
        'csrf_token': csrf_token
    }
    commit_res = s_dean.post(f"{BASE_URL}/api/commit_record.php", json=test_payload, headers={'Content-Type': 'application/json'})
    print(f"  Commit response status: {commit_res.status_code}")
    print(f"  Commit raw text: {repr(commit_res.text[:300])}")
    try:
        commit_data = commit_res.json()
        print(f"  Commit response: {commit_data}")
        new_cert_id = commit_data.get('certificate_id')
        if new_cert_id:
            # Query database to check if date_issued was saved or lost!
            import sqlite3
            con = sqlite3.connect('storage/securefaculty.sqlite')
            row = con.execute("SELECT id, title, date_issued FROM certificates WHERE id = ?", (new_cert_id,)).fetchone()
            print(f"  Saved in DB: id={row[0]}, title='{row[1]}', date_issued='{row[2]}'")
            if row[2] is None or row[2] == '':
                print("  [FAIL] CRITICAL BUG CONFIRMED: date_issued was LOST (NULL/empty) when force_duplicate=1!")
            else:
                print(f"  [OK] date_issued was retained: {row[2]}")
            # Clean up test row
            con.execute("DELETE FROM certificates WHERE id = ?", (new_cert_id,))
            con.execute("DELETE FROM certificate_authors WHERE certificate_id = ?", (new_cert_id,))
            con.commit()
    except Exception as e:
        print(f"  Commit parsing failed: {e}")

    print("\n=== AUDIT SUITE COMPLETED ===")

if __name__ == '__main__':
    run_tests()
