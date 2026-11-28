<?php
/**
 * Shared Legal & Compliance Modals (Terms of Use & RA 10173 Privacy Policy)
 * Included in both login.php and layout_end.php so users can inspect policies before and after sign-in.
 */
?>
<!-- Privacy Policy (RA 10173) Modal -->
<div id="privacy-policy-modal"
     class="fixed inset-0 z-[95] hidden bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="privacy-policy-title"
     onclick="if (event.target === this) closeLegalModal('privacy')">
    <div class="w-full max-w-2xl max-h-[85vh] flex flex-col rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#111c36] shadow-2xl overflow-hidden text-slate-700 dark:text-slate-300">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0c1830]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-sm"></i>
                </div>
                <div>
                    <h3 id="privacy-policy-title" class="text-sm font-bold text-slate-900 dark:text-white">Data Privacy Notice (RA 10173)</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">ISUFST CICI &bull; SecureFaculty Records Management</p>
                </div>
            </div>
            <button type="button" onclick="closeLegalModal('privacy')"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition cursor-pointer"
                    aria-label="Close Privacy Policy">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs leading-relaxed">
            <p class="text-slate-600 dark:text-slate-300">
                In accordance with the <strong>Philippine Data Privacy Act of 2012 (Republic Act No. 10173)</strong>, Iloilo State University of Fisheries Science and Technology &ndash; College of Information and Computing Studies (ISUFST CICI) is committed to protecting the confidentiality, integrity, and security of faculty personal information and credential records processed within <strong>SecureFaculty</strong>.
            </p>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">1. Information We Collect</h4>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Account &amp; Profile Data:</strong> Full name, username, institutional email address, employee ID, department, academic rank, specialization, and optional profile photo.</li>
                    <li><strong>Faculty Credential Assets:</strong> Uploaded certificates, research publications, training records, and OCR-extracted metadata (title, issuer, date issued, venue, scope, and indexing).</li>
                    <li><strong>Security &amp; Audit Telemetry:</strong> Authentication events, document views, downloads, uploads, exports, timestamps, and source IP addresses.</li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">2. Purpose of Processing</h4>
                <p>
                    Collected data is processed strictly for official academic credential archiving, faculty portfolio management, AACCUP accreditation compliance reporting, and institutional security auditing.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">3. Security Safeguards</h4>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Encryption at Rest:</strong> Uploaded documents are encrypted with AES-256 and decrypted only Just-in-Time for authorized viewing or download.</li>
                    <li><strong>Access Governance:</strong> Passwords are Bcrypt-hashed, access is governed by Role-Based Access Control (Faculty vs. Dean/Administrator), and idle sessions expire automatically after 10 minutes of inactivity.</li>
                    <li><strong>Zero Plaintext Staging:</strong> Temporary OCR and conversion artifacts are purged immediately after vault commit.</li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">4. Access, Retention &amp; Data Subject Rights</h4>
                <p>
                    Only the record owner (including tagged co-authors where applicable) and authorized CICI Dean/Administrators may access archived records. Under RA 10173, you have the right to be informed, access your records, request rectification of inaccurate metadata, and request permanent deletion of your uploaded assets through the vault or your department administrator.
                </p>
            </div>
        </div>

        <div class="px-6 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0c1830] flex justify-end">
            <button type="button" onclick="closeLegalModal('privacy')"
                    class="px-4 py-1.5 rounded-lg bg-[#0d2342] dark:bg-blue-600 hover:bg-[#14335e] dark:hover:bg-blue-500 text-white text-xs font-bold transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Terms of Use Modal -->
<div id="terms-of-use-modal"
     class="fixed inset-0 z-[95] hidden bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="terms-of-use-title"
     onclick="if (event.target === this) closeLegalModal('terms')">
    <div class="w-full max-w-2xl max-h-[85vh] flex flex-col rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#111c36] shadow-2xl overflow-hidden text-slate-700 dark:text-slate-300">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0c1830]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-file-contract text-sm"></i>
                </div>
                <div>
                    <h3 id="terms-of-use-title" class="text-sm font-bold text-slate-900 dark:text-white">Terms of Use &amp; Acceptable Use Agreement</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">ISUFST CICI &bull; SecureFaculty Institutional Portal</p>
                </div>
            </div>
            <button type="button" onclick="closeLegalModal('terms')"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800 transition cursor-pointer"
                    aria-label="Close Terms of Use">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs leading-relaxed">
            <p class="text-slate-600 dark:text-slate-300">
                By accessing or using <strong>SecureFaculty</strong>, you agree to comply with the following institutional terms governing the ISUFST CICI Systematic Records Management Portal:
            </p>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">1. Authorized Institutional Access</h4>
                <p>
                    Access is restricted to authorized ISUFST CICI faculty members, Dean clearance officers, and designated system administrators. Sharing credentials, impersonating another user, or attempting to bypass Role-Based Access Control (RBAC) is strictly prohibited.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">2. Document Authenticity &amp; Accuracy</h4>
                <p>
                    You are solely responsible for ensuring that every certificate, publication, or credential uploaded to the vault is genuine, accurate, and free from tampering. OCR-extracted fields must be reviewed and verified before committing records to the vault.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">3. Confidentiality &amp; Unattended Terminals</h4>
                <p>
                    Decrypted documents downloaded for official use must be handled responsibly. Users must lock or sign out of shared or unattended workstations; as an added safeguard, inactive sessions automatically log out after 10 minutes.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-slate-100 mb-1">4. Immutable Audit Logging</h4>
                <p>
                    All system interactions&mdash;including sign-ins, failed login attempts, uploads, document previews, downloads, deletions, and accreditation exports&mdash;are logged in the RA 10173 Compliance Audit Ledger for institutional accountability.
                </p>
            </div>
        </div>

        <div class="px-6 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0c1830] flex justify-end">
            <button type="button" onclick="closeLegalModal('terms')"
                    class="px-4 py-1.5 rounded-lg bg-[#0d2342] dark:bg-blue-600 hover:bg-[#14335e] dark:hover:bg-blue-500 text-white text-xs font-bold transition cursor-pointer">
                I Understand
            </button>
        </div>
    </div>
</div>

<script>
    function openLegalModal(type) {
        const id = type === 'privacy' ? 'privacy-policy-modal' : 'terms-of-use-modal';
        const el = document.getElementById(id);
        if (el) el.classList.remove('hidden');
    }

    function closeLegalModal(type) {
        const id = type === 'privacy' ? 'privacy-policy-modal' : 'terms-of-use-modal';
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeLegalModal('privacy');
            closeLegalModal('terms');
        }
    });
</script>
