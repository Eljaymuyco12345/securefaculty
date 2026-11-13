<?php
$error = $error ?? null;
$msg = $msg ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="view-transition" content="same-origin">
    <meta name="color-scheme" content="light dark">
    <title>SecureFaculty | One-Gate Login Portal</title>
    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Instant anti-FOUC theme detector -->
    <script>
        (function() {
            var theme = localStorage.getItem('securefaculty_theme');
            if (!theme) {
                theme = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
            }
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <!-- Tailwind CSS CDN with class-based dark mode -->
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
        :root {
            color-scheme: light dark;
            accent-color: oklch(0.55 0.19 255);
        }
        html.dark {
            color-scheme: dark;
            accent-color: oklch(0.68 0.17 250);
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            font-kerning: normal;
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            line-height: 1.5;
        }
        h1, h2, h3 {
            text-wrap: balance;
        }
        p {
            text-wrap: pretty;
        }
        input:not([type="range"]):user-invalid {
            border-color: oklch(0.62 0.21 25) !important;
            box-shadow: 0 0 0 2px oklch(0.62 0.21 25 / 0.2);
        }
        button:not(:disabled):active {
            transform: scale(0.98);
        }
        .grid-bg {
            background-color: #f7fafc;
            background-image: 
                linear-gradient(to right, #e2e8f0 1px, transparent 1px),
                linear-gradient(to bottom, #e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
        }
        html.dark .grid-bg {
            background-color: #081225;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
        }
        .card-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }
        html.dark .card-shadow {
            box-shadow: 0 14px 35px -5px rgba(0, 0, 0, 0.6), 0 10px 14px -6px rgba(0, 0, 0, 0.4);
        }
        /* Comprehensive Dark Mode Input Safeguards */
        html.dark input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]),
        html.dark select,
        html.dark textarea {
            color: #f1f5f9 !important;
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: #64748b !important;
        }
        html.dark input:-webkit-autofill,
        html.dark input:-webkit-autofill:hover,
        html.dark input:-webkit-autofill:focus,
        html.dark select:-webkit-autofill,
        html.dark textarea:-webkit-autofill {
            -webkit-text-fill-color: #f1f5f9 !important;
            -webkit-box-shadow: 0 0 0px 1000px #0f172a inset !important;
            border-color: #334155 !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        html.dark input[type="date"],
        html.dark input[type="checkbox"],
        html.dark input[type="radio"] {
            color-scheme: dark;
        }
    </style>
</head>
<body class="min-h-screen flex bg-slate-100 dark:bg-[#081225] overflow-x-hidden antialiased transition-colors duration-200">

    <!-- Left Navy Accent Panel matching Figure 4.2 -->
    <aside class="w-16 sm:w-28 md:w-36 lg:w-44 bg-[#0d2342] dark:bg-[#060e1d] select-none flex flex-col justify-between items-center py-6 border-r border-[#08182f] dark:border-slate-900 flex-shrink-0">
        <div class="flex flex-col items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600/30 border border-blue-400/40 flex items-center justify-center text-white text-lg shadow-inner">
                <i class="fa-solid fa-lock text-blue-300"></i>
            </div>
            <span class="text-[11px] font-bold tracking-wider text-slate-300 uppercase text-center hidden sm:block">ISUFST<br><span class="text-blue-400 font-normal">CICI PORTAL</span></span>
        </div>
        <div class="text-[10px] text-slate-400 text-center uppercase tracking-widest hidden sm:block">
            <span>RA 10173</span><br>
            <span class="text-slate-400 font-mono">2026-v1</span>
        </div>
    </aside>

    <!-- Main Grid Workspace matching Figure 4.2 -->
    <main class="flex-1 grid-bg flex flex-col justify-center items-center p-4 sm:p-8 relative">

        <!-- Top Right Controls: Theme Switcher & Environment Pill -->
        <div class="absolute top-4 right-6 flex items-center gap-2">
            <button type="button" onclick="toggleTheme()" id="themeToggleBtn" aria-label="Toggle visual theme"
                class="p-2 rounded-full bg-white/90 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 shadow-sm transition-all cursor-pointer">
                <i class="fa-solid fa-moon dark:hidden text-xs"></i>
                <i class="fa-solid fa-sun hidden dark:inline text-xs text-amber-400"></i>
            </button>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/90 dark:bg-slate-800/90 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-medium shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                XAMPP / Localhost
            </span>
        </div>

        <!-- Center Login Card matching Figure 4.2 -->
        <div class="w-full max-w-[460px] relative">

            <!-- The Card -->
            <div class="bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200/90 dark:border-slate-800 card-shadow p-6 sm:p-9 relative">

                <!-- Header: Padlock + Title -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center gap-2 mb-2">
                        <i class="fa-solid fa-lock text-blue-600 dark:text-blue-400 text-lg"></i>
                        <span class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">SecureFaculty</span>
                    </div>
                    <h2 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 tracking-wider uppercase">
                        ONE-GATE LOGIN PORTAL
                    </h2>
                </div>

                <!-- Notifications -->
                <?php if ($error === 'invalid_credentials'): ?>
                    <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                        <span>Authentication failed. Invalid username or security credentials.</span>
                    </div>
                <?php elseif ($error === 'deactivated'): ?>
                    <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-user-slash text-amber-600"></i>
                        <span>Account Access Suspended: This faculty account has been deactivated by the Dean.</span>
                    </div>
                <?php elseif ($error === 'rate_limited'): ?>
                    <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-rose-500"></i>
                        <span>Security Lockout: Multiple failed login attempts detected. Access is temporarily paused for 5 minutes to protect account credentials.</span>
                    </div>
                <?php elseif ($error === 'empty_fields'): ?>
                    <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                        <span>Please enter both username and password.</span>
                    </div>
                <?php elseif ($msg === 'logged_out'): ?>
                    <div class="mb-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-blue-500"></i>
                        <span>Secure session closed. Audit ledger updated.</span>
                    </div>
                <?php elseif ($msg === 'auth_required'): ?>
                    <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-lock text-amber-600"></i>
                        <span>Authentication Required: Please sign in to access this portal resource.</span>
                    </div>
                <?php elseif ($error === 'csrf_violation'): ?>
                    <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-rose-500"></i>
                        <span>Security Violation: Invalid or expired CSRF security token. Please try again.</span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="login.php" method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthService::getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Username (CICI ID)
                        </label>
                        <input type="text" id="username" name="username" required autofocus
                            placeholder="ISUFST2026-01"
                            class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-slate-900 text-xs font-medium transition-all">
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                placeholder="••••••••••"
                                class="w-full pl-3.5 pr-12 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-slate-900 text-xs font-medium transition-all">
                            <button type="button" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility"
                                    class="touch-target absolute inset-y-0 right-0 px-3 w-11 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                <i id="passwordEyeIcon" class="fa-regular fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Green Pill Button matching Figure 4.2 -->
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-2.5 px-6 rounded-full bg-[#1da055] hover:bg-[#188c49] text-white font-bold text-xs uppercase tracking-wide shadow-md shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span>SECURE ACCESS (Bcrypt Authenticated)</span>
                        </button>
                    </div>

                    <!-- Footer Note with Blue Padlock matching Figure 4.2 -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                        <span>Compliant with RA 10173 - Authentication data is Bcrypt hashed.</span>
                        <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-lock text-[10px]"></i>
                        </div>
                    </div>

                </form>

            </div>

            <!-- Institutional System Footer -->
            <p class="text-center font-medium text-[11px] tracking-wide text-slate-400 dark:text-slate-500 mt-4">
                ISUFST CICI • Systematic Records Management Portal
            </p>

        </div>

    </main>

    <script>
        function toggleTheme() {
            var current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            if (next === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('securefaculty_theme', next);
        }

        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('passwordEyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.className = 'fa-regular fa-eye-slash text-xs text-blue-600';
            } else {
                passInput.type = 'password';
                icon.className = 'fa-regular fa-eye text-xs';
            }
        }
    </script>
</body>
</html>
