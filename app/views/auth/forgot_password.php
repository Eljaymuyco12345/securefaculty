<?php
$smtpConfigured = $smtpConfigured ?? false;
$csrfToken = AuthService::getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="color-scheme" content="light dark">
    <title>SecureFaculty | Forgot Password (OTP Recovery)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        (function() {
            var theme = localStorage.getItem('securefaculty_theme');
            if (!theme) {
                theme = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
            }
            if (theme === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        darkCanvas: '#081225',
                        darkCard: '#111c36',
                        darkBorder: '#1e293b'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif; }
        .grid-bg {
            background-color: #f7fafc;
            background-image: linear-gradient(to right, #e2e8f0 1px, transparent 1px), linear-gradient(to bottom, #e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
        }
        html.dark .grid-bg {
            background-color: #081225;
            background-image: linear-gradient(to right, rgba(255,255,255,0.04) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.04) 1px, transparent 1px);
        }
    </style>
</head>
<body class="grid-bg min-h-screen flex items-center justify-center p-4 text-slate-800 dark:text-slate-100">
    <div class="w-full max-w-md bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl p-6 sm:p-8 space-y-5">
        <div class="text-center space-y-1">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-2 border border-blue-200 dark:border-blue-800">
                <i class="fa-solid fa-envelope-open-text text-xl"></i>
            </div>
            <h1 class="text-lg font-extrabold text-slate-900 dark:text-white">Password Recovery (6-Digit OTP)</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Enter your registered email or CICI ID to receive a 6-digit verification code.
            </p>
        </div>

        <div id="fp-alert" class="hidden p-3 rounded-lg text-xs"></div>

        <!-- Step 1: Request 6-Digit OTP -->
        <form id="fpStep1Form" onsubmit="handleRequestOtp(event)" class="space-y-4 text-xs">
            <div>
                <label for="fp_identifier" class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Registered Email Address or Username
                </label>
                <input type="text" id="fp_identifier" required autofocus placeholder="e.g. mikhail.aguilar@isufst.edu.ph or mikhail"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" id="fp-btn-step1"
                    class="w-full py-2.5 px-5 rounded-full bg-blue-600 hover:bg-blue-500 text-white font-bold uppercase tracking-wide shadow-md transition-all cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Send 6-Digit OTP</span>
            </button>
        </form>

        <!-- Step 2: Verify OTP & Set New Password -->
        <form id="fpStep2Form" onsubmit="handleVerifyReset(event)" class="hidden space-y-3.5 text-xs">
            <div>
                <label for="fp_otp" class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    6-Digit OTP Code
                </label>
                <input type="text" id="fp_otp" required maxlength="6" pattern="\d{6}" inputmode="numeric" placeholder="000000"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-center font-mono text-lg tracking-[0.4em] font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="fp_new_pass" class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    New Password (min. 6 characters)
                </label>
                <input type="password" id="fp_new_pass" required minlength="6" autocomplete="new-password"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="fp_confirm_pass" class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Confirm New Password
                </label>
                <input type="password" id="fp_confirm_pass" required minlength="6" autocomplete="new-password"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" id="fp-btn-step2"
                    class="w-full py-2.5 px-5 rounded-full bg-[#1da055] hover:bg-[#188c49] text-white font-bold uppercase tracking-wide shadow-md transition-all cursor-pointer flex items-center justify-center gap-2">
                <i class="fa-solid fa-check-double"></i>
                <span>Verify OTP &amp; Reset Password</span>
            </button>
            <button type="button" onclick="backToStep1()" class="w-full text-center text-[11px] text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer pt-1">
                &larr; Request a new OTP code
            </button>
        </form>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
            <a href="login.php" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">
                &larr; Back to Sign In
            </a>
            <span class="text-[10px] text-slate-400">Expires in 15 mins</span>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?= json_encode($csrfToken) ?>;

        function showFpAlert(message, type) {
            const box = document.getElementById('fp-alert');
            if (!box) return;
            box.textContent = message;
            box.className = 'p-3 rounded-lg text-xs border ';
            if (type === 'success') {
                box.className += 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300';
            } else {
                box.className += 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300';
            }
            box.classList.remove('hidden');
        }

        function backToStep1() {
            document.getElementById('fpStep2Form')?.classList.add('hidden');
            document.getElementById('fpStep1Form')?.classList.remove('hidden');
        }

        async function handleRequestOtp(e) {
            e.preventDefault();
            const btn = document.getElementById('fp-btn-step1');
            const identifier = document.getElementById('fp_identifier').value.trim();
            if (btn) btn.disabled = true;

            try {
                const res = await fetch('api/forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                    body: JSON.stringify({ action: 'request_otp', identifier, csrf_token: CSRF_TOKEN })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    showFpAlert(data.message, 'success');
                    document.getElementById('fpStep1Form')?.classList.add('hidden');
                    document.getElementById('fpStep2Form')?.classList.remove('hidden');
                    document.getElementById('fp_otp')?.focus();
                } else {
                    showFpAlert(data.message || 'Unable to send OTP.', 'error');
                }
            } catch (err) {
                showFpAlert('Network error while contacting server.', 'error');
            } finally {
                if (btn) btn.disabled = false;
            }
        }

        async function handleVerifyReset(e) {
            e.preventDefault();
            const btn = document.getElementById('fp-btn-step2');
            const identifier = document.getElementById('fp_identifier').value.trim();
            const otp = document.getElementById('fp_otp').value.trim();
            const newPassword = document.getElementById('fp_new_pass').value;
            const confirmPassword = document.getElementById('fp_confirm_pass').value;

            if (newPassword !== confirmPassword) {
                showFpAlert('New password and confirmation do not match.', 'error');
                return;
            }

            if (btn) btn.disabled = true;
            try {
                const res = await fetch('api/forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                    body: JSON.stringify({
                        action: 'verify_reset',
                        identifier,
                        otp,
                        new_password: newPassword,
                        confirm_password: confirmPassword,
                        csrf_token: CSRF_TOKEN
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    showFpAlert(data.message, 'success');
                    setTimeout(() => { window.location.href = 'login.php'; }, 1800);
                } else {
                    showFpAlert(data.message || 'OTP verification failed.', 'error');
                }
            } catch (err) {
                showFpAlert('Network error while resetting password.', 'error');
            } finally {
                if (btn) btn.disabled = false;
            }
        }
    </script>
</body>
</html>
