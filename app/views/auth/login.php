<?php
$error = $error ?? null;
$msg = $msg ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>SecureFaculty | One-Gate Login Portal</title>
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
    </style>
</head>
<body class="min-h-screen flex bg-slate-100 dark:bg-[#081225] overflow-x-hidden select-none transition-colors duration-200">

    <!-- Left Navy Accent Panel matching Figure 4.2 -->
    <aside class="w-16 sm:w-28 md:w-36 lg:w-44 bg-[#0d2342] dark:bg-[#060e1d] flex flex-col justify-between items-center py-6 border-r border-[#08182f] dark:border-slate-900 flex-shrink-0">
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
                <?php endif; ?>

                <!-- Login Form -->
                <form action="login.php" method="POST" class="space-y-4">

                    <!-- Username Field -->
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Username (CICI ID)
                        </label>
                        <input type="text" id="username" name="username" required
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
                                class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-slate-900 text-xs font-medium transition-all">
                            <button type="button" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                <i id="passwordEyeIcon" class="fa-regular fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Role Checkbox matching Figure 4.2 -->
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="is_dean_toggle" onchange="toggleDeanRole(this)"
                            class="w-4 h-4 text-emerald-600 rounded border-slate-300 dark:border-slate-700 focus:ring-emerald-500 dark:bg-slate-800">
                        <label for="is_dean_toggle" class="text-xs text-slate-600 dark:text-slate-400 font-medium cursor-pointer">
                            Dean/Administrator? (XAMPP Environment)
                        </label>
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

            <!-- Quick Demo Helper Pill matching Capstone Evaluation -->
            <div class="mt-4 text-center">
                <div class="inline-flex items-center gap-2 bg-white/80 dark:bg-[#111c36]/90 backdrop-blur-sm border border-slate-200 dark:border-slate-800 px-3 py-1.5 rounded-full shadow-sm text-[11px] text-slate-600 dark:text-slate-400">
                    <span class="font-semibold text-slate-700 dark:text-slate-300">Quick Test:</span>
                    <button type="button" onclick="fillCreds('dean', 'admin123', true)" class="text-blue-600 dark:text-blue-400 hover:underline font-medium cursor-pointer">Dean</button>
                    <span class="text-slate-300 dark:text-slate-600">|</span>
                    <button type="button" onclick="fillCreds('mikhail', 'faculty123', false)" class="text-emerald-600 dark:text-emerald-400 hover:underline font-medium cursor-pointer">Faculty (Mikhail)</button>
                    <span class="text-slate-300 dark:text-slate-600">|</span>
                    <button type="button" onclick="fillCreds('raejan', 'faculty123', false)" class="text-slate-600 dark:text-slate-300 hover:underline font-medium cursor-pointer">Faculty (Rae Jan)</button>
                </div>
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

        function fillCreds(user, pass, isDean) {
            document.getElementById('username').value = user;
            document.getElementById('password').value = pass;
            const cb = document.getElementById('is_dean_toggle');
            cb.checked = isDean;
        }

        function toggleDeanRole(cb) {
            if (cb.checked) {
                document.getElementById('username').value = 'dean';
                document.getElementById('password').value = 'admin123';
            } else {
                document.getElementById('username').value = 'mikhail';
                document.getElementById('password').value = 'faculty123';
            }
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
