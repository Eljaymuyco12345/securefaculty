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
    <title>SecureFaculty | Log In</title>
    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            var zoom = localStorage.getItem('securefaculty_zoom');
            if (!zoom || zoom === '1.12') {
                zoom = '1.07';
                localStorage.setItem('securefaculty_zoom', '1.07');
            }
            if (window.innerWidth >= 1024) {
                document.documentElement.style.zoom = zoom;
            } else {
                document.documentElement.style.zoom = '1.0';
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
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
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
        html {
            font-size: 16px;
        }
        @media (min-width: 1024px) {
            html {
                zoom: 1.07;
            }
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.5;
        }
        input:not([type="range"]):user-invalid {
            border-color: oklch(0.62 0.21 25) !important;
            box-shadow: 0 0 0 2px oklch(0.62 0.21 25 / 0.2);
        }
        button:not(:disabled):active {
            transform: scale(0.99);
        }
        .right-checkered-bg {
            background-color: #0b1930;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.045) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.045) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        @media (min-width: 1024px) {
            .wave-panel-clip {
                clip-path: url(#rightWaveClip);
            }
        }
        /* Dark Mode Input Safeguards */
        html.dark input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]) {
            color: #f1f5f9 !important;
            background-color: #0d172a !important;
            border-color: #24334d !important;
        }
        html.dark input::placeholder {
            color: #64748b !important;
        }
        html.dark input:-webkit-autofill,
        html.dark input:-webkit-autofill:hover,
        html.dark input:-webkit-autofill:focus {
            -webkit-text-fill-color: #f1f5f9 !important;
            -webkit-box-shadow: 0 0 0px 1000px #0d172a inset !important;
            border-color: #24334d !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-[#081225] text-slate-900 dark:text-slate-100 antialiased overflow-x-hidden transition-colors duration-200">

    <main class="min-h-screen flex flex-col lg:flex-row">

        <!-- LEFT: Brand Header + Centered Login Form -->
        <section class="w-full lg:w-1/2 flex-1 flex flex-col justify-between p-6 sm:p-10 xl:p-14 bg-slate-50 dark:bg-[#081225] relative z-10">

            <!-- Top-Left Brand + Mobile Theme Toggle -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-800 p-2 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-center">
                        <img src="assets/images/isufst_logo.png" alt="ISUFST Seal" class="max-w-full max-h-full object-contain">
                    </div>
                    <div class="leading-tight">
                        <span class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white block">SecureFaculty</span>
                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">ISUFST &bull; CICI</span>
                    </div>
                </div>

                <button type="button" onclick="toggleTheme()" aria-label="Toggle visual theme"
                    class="lg:hidden p-2 min-w-[38px] min-h-[38px] flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                    <i class="fa-solid fa-moon dark:hidden text-xs"></i>
                    <i class="fa-solid fa-sun hidden dark:inline text-xs text-amber-400"></i>
                </button>
            </div>

            <!-- Semi-Centered Login Card -->
            <div class="my-auto py-8 w-full max-w-[400px] mx-auto">
                <div class="bg-white dark:bg-[#0f1b33] border border-slate-200 dark:border-slate-800 rounded-2xl p-7 sm:p-8 shadow-sm">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Log in</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Enter your CICI account credentials.</p>
                    </div>

                    <!-- Notifications -->
                    <?php if ($error === 'invalid_credentials'): ?>
                        <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500 shrink-0"></i>
                            <span>Invalid username or password.</span>
                        </div>
                    <?php elseif ($error === 'deactivated'): ?>
                        <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-user-slash text-amber-600 shrink-0"></i>
                            <span>This account has been deactivated by the Dean.</span>
                        </div>
                    <?php elseif ($error === 'rate_limited'): ?>
                        <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-rose-500 shrink-0"></i>
                            <span>Too many failed attempts. Please wait 5 minutes and try again.</span>
                        </div>
                    <?php elseif ($error === 'empty_fields'): ?>
                        <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0"></i>
                            <span>Please enter both username and password.</span>
                        </div>
                    <?php elseif ($msg === 'logged_out'): ?>
                        <div class="mb-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-blue-500 shrink-0"></i>
                            <span>You have been signed out.</span>
                        </div>
                    <?php elseif ($msg === 'session_expired'): ?>
                        <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-amber-600 shrink-0"></i>
                            <span>Session expired due to inactivity. Please log in again.</span>
                        </div>
                    <?php elseif ($msg === 'auth_required'): ?>
                        <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-lock text-amber-600 shrink-0"></i>
                            <span>Please log in to continue.</span>
                        </div>
                    <?php elseif ($error === 'csrf_violation'): ?>
                        <div class="mb-4 p-3 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-rose-500 shrink-0"></i>
                            <span>Session token expired. Please try again.</span>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form action="login.php" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(AuthService::getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                        <div>
                            <label for="username" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                Username
                            </label>
                            <input type="text" id="username" name="username" required autofocus
                                placeholder="ISUFST2026-01"
                                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-[#0d172a] border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-[#0d172a] text-sm transition-all">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Password
                                </label>
                                <a href="forgot_password.php" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                    Forgot password?
                                </a>
                            </div>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                    placeholder="••••••••••"
                                    class="w-full pl-3.5 pr-11 py-2.5 bg-slate-50 dark:bg-[#0d172a] border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-[#0d172a] text-sm transition-all">
                                <button type="button" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility"
                                        class="touch-target absolute inset-y-0 right-0 px-3 w-11 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                    <i id="passwordEyeIcon" class="fa-regular fa-eye text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pt-1">
                            <label for="agree_terms" class="flex items-start gap-2.5 text-xs text-slate-600 dark:text-slate-300 cursor-pointer select-none">
                                <input type="checkbox" id="agree_terms" name="agree_terms" value="1" required
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                <span>
                                    I agree to the
                                    <button type="button" onclick="openLegalModal('terms')" class="text-slate-900 dark:text-white font-semibold hover:underline cursor-pointer">Terms of Use</button>
                                    and
                                    <button type="button" onclick="openLegalModal('privacy')" class="text-slate-900 dark:text-white font-semibold hover:underline cursor-pointer">Privacy Policy</button>.
                                </span>
                            </label>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full py-2.5 px-4 rounded-lg bg-[#0d2342] hover:bg-[#14335e] dark:bg-blue-600 dark:hover:bg-blue-500 text-white font-semibold text-sm shadow-sm transition-colors cursor-pointer">
                                Log in
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quiet Left Footer -->
            <p class="text-xs text-slate-400 dark:text-slate-500">
                ISUFST CICI &bull; Faculty Records &amp; Accreditation Vault
            </p>
        </section>

        <!-- RIGHT: Curved Wave Divider + Faint Checkered ISUFST Fish Logo Panel -->
        <section class="hidden lg:flex lg:w-[54%] lg:-ml-16 right-checkered-bg wave-panel-clip text-white pl-24 pr-10 xl:pr-14 py-10 xl:py-14 flex-col justify-between relative z-20">

            <!-- Pronounced S-Curve Clip Path + Subtle Edge Highlight -->
            <svg class="pointer-events-none absolute inset-0 w-full h-full z-10" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <clipPath id="rightWaveClip" clipPathUnits="objectBoundingBox">
                        <path d="M 0.13,0 C 0.00,0.28 0.20,0.72 0.03,1 L 1,1 L 1,0 Z" />
                    </clipPath>
                </defs>
                <path d="M 13,0 C 0,28 20,72 3,100" fill="none" stroke="rgba(148, 163, 184, 0.22)" stroke-width="0.35" />
            </svg>

            <!-- Top-Right Theme Toggle -->
            <div class="relative z-10 flex justify-end">
                <button type="button" onclick="toggleTheme()" id="themeToggleBtn" aria-label="Toggle visual theme"
                    class="p-2 min-w-[38px] min-h-[38px] flex items-center justify-center rounded-lg border border-slate-700/80 bg-slate-900/40 text-slate-300 hover:bg-slate-800 transition-colors cursor-pointer">
                    <i class="fa-solid fa-moon dark:hidden text-xs"></i>
                    <i class="fa-solid fa-sun hidden dark:inline text-xs text-amber-400"></i>
                </button>
            </div>

            <!-- Centered ISUFST Fish Logo -->
            <div class="relative z-10 my-auto flex flex-col items-center text-center max-w-md mx-auto space-y-5">
                <img src="assets/images/isufst_logo.png" alt="ISUFST Logo"
                     class="w-64 xl:w-72 h-auto object-contain drop-shadow-2xl select-none">
                <div class="space-y-1.5">
                    <h1 class="text-2xl xl:text-3xl font-bold tracking-tight text-white">
                        Faculty Records &amp; Accreditation Vault
                    </h1>
                    <p class="text-sm text-slate-300/80">
                        College of Informatics and Computing Innovations
                    </p>
                </div>
            </div>

            <!-- Minimal Right Footer -->
            <div class="relative z-10 flex items-center justify-between text-xs text-slate-400">
                <span>Iloilo State University of Fisheries Science and Technology</span>
                <span class="font-mono text-slate-500">RA 10173</span>
            </div>
        </section>

    </main>

    <?php require_once __DIR__ . '/../layouts/legal_modals.php'; ?>

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
                icon.className = 'fa-regular fa-eye-slash text-xs text-blue-600 dark:text-blue-400';
            } else {
                passInput.type = 'password';
                icon.className = 'fa-regular fa-eye text-xs';
            }
        }
    </script>
</body>
</html>
