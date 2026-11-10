<?php
/**
 * Shared Master Layout Start
 * Standardizes Navigation, Architecture, Theming, and Header across all pages
 */

if (!isset($currentUser)) {
    $currentUser = Auth::user();
}
$isDean = Auth::isDean();
$activeNav = $activeNav ?? 'dashboard';
$pageTitle = $pageTitle ?? 'SecureFaculty Portal';
$currentSearch = $_GET['search'] ?? '';
$searchAction = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="view-transition" content="same-origin">
    <title>SecureFaculty | <?= htmlspecialchars($pageTitle) ?></title>
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="<?= htmlspecialchars(Auth::getCsrfToken()) ?>">
    <!-- Google Fonts: Plus Jakarta Sans (High x-height UI Typeface) & JetBrains Mono (Tabular Numerals) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Inline synchronous theme initializer to eliminate FOUC (Flash of Unstyled Content) -->
    <script>
        window.CSRF_TOKEN = <?= json_encode(Auth::getCsrfToken()) ?>;
        (function() {
            var savedTheme = localStorage.getItem('securefaculty_theme');
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace']
                    },
                    colors: {
                        darkCanvas: '#081225',
                        darkCard: '#111c36',
                        darkBorder: '#1e293b'
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ==========================================================================
           20-POINT DESIGN ENGINEERING & MODERN WEB UI SYSTEM
           ========================================================================== */
        :root {
            color-scheme: light dark;
            accent-color: oklch(0.55 0.19 255);
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
            --oklch-surface-elevated: color-mix(in oklab, oklch(0.98 0.01 250), white 25%);
            --oklch-border-contrast: color-mix(in oklab, oklch(0.75 0.03 250), black 15%);
        }
        html.dark {
            color-scheme: dark;
            accent-color: oklch(0.68 0.17 250);
            scrollbar-color: #475569 #0f172a;
            --oklch-surface-elevated: color-mix(in oklab, oklch(0.22 0.04 255), black 15%);
            --oklch-border-contrast: color-mix(in oklab, oklch(0.42 0.05 255), white 18%);
        }
        ::selection {
            background-color: oklch(0.58 0.19 255 / 0.22);
            color: inherit;
        }
        html.dark ::selection {
            background-color: oklch(0.68 0.18 250 / 0.35);
            color: #f8fafc;
        }

        /* #1 Kerning, #5 Tracking, #7 x-height, #9 Leading */
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            font-kerning: normal;
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            line-height: 1.5;
            text-align: start;
            overflow-wrap: break-word;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        code, pre, .font-mono {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, monospace;
            font-variant-numeric: tabular-nums;
        }

        /* #3 Tabular Numbers: prevent layout jitter on counters, dates, timers, and table cells */
        .tabular-nums,
        td,
        .kpi-metric,
        [id*="counter"],
        [id*="timer"],
        [id*="percent"],
        [id*="count"],
        input[type="date"],
        input[type="number"] {
            font-variant-numeric: tabular-nums;
        }

        /* #12 Widow Prevention & #15 Line Length (Scoped per modern-web-guidance) */
        h1, h2, h3, h4, th, .modal-title {
            text-wrap: balance;
            letter-spacing: -0.015em;
        }
        p, li, blockquote, .pretty-text {
            text-wrap: pretty;
        }
        .prose-measure {
            max-width: 68ch;
        }

        /* #2 Optical Centre alignment for icons inside circular/square badges */
        .optical-center > i.fa-play,
        .optical-center > i.fa-angles-right,
        .optical-center > i.fa-arrow-right-from-bracket {
            transform: translateX(0.75px);
        }
        .optical-center > i.fa-cloud-arrow-up,
        .optical-center > i.fa-triangle-exclamation {
            transform: translateY(-0.5px);
        }

        /* #8 Touch Targets & #13 Safe Areas */
        .touch-target {
            position: relative;
        }
        .touch-target::after {
            content: "";
            position: absolute;
            inset: -6px;
        }
        @media (pointer: coarse) {
            .touch-target,
            header button,
            nav a,
            nav button {
                min-height: 44px;
                min-width: 44px;
            }
        }
        .safe-area-bottom {
            padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
        }
        .safe-area-sidebar {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* #10 Origin-Aware & #18 Interruptible Animations */
        .origin-top-right-menu {
            transform-origin: top right;
            transition: opacity 0.16s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.16s cubic-bezier(0.16, 1, 0.3, 1),
                        display 0.16s allow-discrete;
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        .origin-top-right-menu.hidden {
            opacity: 0;
            transform: scale(0.95) translateY(-4px);
        }
        .modal-card-anim {
            transform-origin: center center;
            transition: opacity 0.18s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        @starting-style {
            .origin-top-right-menu:not(.hidden) {
                opacity: 0;
                transform: scale(0.95) translateY(-4px);
            }
            [id*="modal"]:not(.hidden) .modal-card-anim {
                opacity: 0;
                transform: scale(0.96) translateY(6px);
            }
        }

        /* #14 Stagger Cascade Entry */
        @keyframes staggerFadeUp {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .stagger-item {
            animation: staggerFadeUp 0.26s cubic-bezier(0.16, 1, 0.3, 1) both;
            animation-delay: calc(min(var(--stagger-i, 0), 12) * 30ms);
        }

        /* #16 Tactile Affordance & User-Invalid Form Styling */
        button:not(:disabled):active,
        a.btn-tactile:active {
            transform: scale(0.98);
        }
        input:not([type="range"]):user-invalid,
        select:user-invalid,
        textarea:user-invalid {
            border-color: oklch(0.62 0.21 25) !important;
            box-shadow: 0 0 0 2px oklch(0.62 0.21 25 / 0.2);
        }

        /* #19 Progressive Disclosure Smooth Accordion */
        .disclosure-panel {
            display: grid;
            grid-template-rows: 1fr;
            transition: grid-template-rows 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                        opacity 0.2s ease;
            opacity: 1;
        }
        .disclosure-panel.is-collapsed {
            grid-template-rows: 0fr;
            opacity: 0;
            pointer-events: none;
        }
        .disclosure-panel > div {
            overflow: hidden;
        }

        /* #20 Shared Element View Transitions */
        #main-workspace-content {
            view-transition-name: workspace-content;
        }

        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-delay: 0ms !important;
                transition-duration: 0.01ms !important;
            }
        }

        .sidebar-navy {
            background-color: #0d2342;
        }
        .sidebar-hover:hover {
            background-color: #14335e;
            color: #ffffff;
        }
        .sidebar-active {
            background-color: #1a4278;
            color: #ffffff;
            font-weight: 700;
            box-shadow: inset 3px 0 0 0 #38bdf8;
        }
        /* Comprehensive Dark Mode Fallback Safeguards for Modals & Form Fields */
        html.dark [id*="modal"] > div.bg-white,
        html.dark .modal-content,
        html.dark .modal-card {
            background-color: #111c36 !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
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
        html.dark select option {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
        }
    </style>
</head>
<body class="bg-[#f0f4f8] dark:bg-[#081225] text-slate-800 dark:text-slate-100 min-h-screen flex flex-col font-sans antialiased">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebar-backdrop" onclick="toggleSidebar(false)"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity"></div>

    <div class="flex-1 flex overflow-hidden relative">

        <!-- Unified Left Navy Sidebar (Fixed & Consistent across ALL pages) -->
        <aside id="main-sidebar"
               class="fixed inset-y-0 left-0 z-50 w-64 lg:static lg:w-56 sidebar-navy safe-area-sidebar select-none text-slate-300 flex flex-col justify-between flex-shrink-0 border-r border-[#08182f] transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">
            <div>
                <!-- Brand / Logo matching Figure 4.3 -->
                <div class="h-14 px-4 flex items-center justify-between border-b border-[#14335e]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-600/30 border border-blue-400/40 flex items-center justify-center text-blue-300 text-sm shadow-inner">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <span class="font-bold text-sm tracking-tight text-white block leading-tight">SecureFaculty</span>
                            <span class="text-[9px] text-blue-300 font-mono tracking-wider uppercase block">ISUFST CICI</span>
                        </div>
                    </div>
                    <!-- Close button on mobile -->
                    <button type="button" onclick="toggleSidebar(false)" class="text-slate-400 hover:text-white lg:hidden p-1">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Role Badge Indicator -->
                <div class="px-3 pt-3 pb-1">
                    <div class="px-2.5 py-1.5 rounded-lg bg-[#14335e]/70 border border-[#1e447b] flex items-center justify-between text-[11px]">
                        <span class="text-slate-300 flex items-center gap-1.5 truncate">
                            <span class="w-2 h-2 rounded-full <?= $isDean ? 'bg-indigo-400' : 'bg-emerald-400' ?>"></span>
                            <strong class="text-white truncate"><?= $isDean ? 'Dean Officer' : 'Faculty Member' ?></strong>
                        </span>
                        <span class="text-[9px] font-mono uppercase px-1.5 py-0.2 rounded bg-[#0d2342] text-slate-300 border border-[#1e447b]">
                            <?= $isDean ? 'ADMIN' : 'USER' ?>
                        </span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="py-2 px-2 space-y-1 text-xs">

                    <?php if ($isDean): ?>
                        <div class="px-2.5 pt-2 pb-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                            Dean Administration
                        </div>

                        <!-- Dashboard -->
                        <a href="dashboard.php"
                           class="<?= $activeNav === 'dashboard' ? 'sidebar-active' : 'sidebar-hover' ?> flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 transition-all">
                            <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                            <span>Dean's Dashboard</span>
                        </a>

                        <!-- User Management -->
                        <a href="users.php"
                           class="<?= $activeNav === 'users' ? 'sidebar-active' : 'sidebar-hover' ?> flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 transition-all">
                            <i class="fa-solid fa-users-gear w-4 text-center"></i>
                            <span>User Management</span>
                        </a>

                        <!-- Audit Ledger -->
                        <a href="audit_logs.php"
                           class="<?= $activeNav === 'audit' ? 'sidebar-active' : 'sidebar-hover' ?> flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 transition-all">
                            <i class="fa-solid fa-clipboard-check w-4 text-center"></i>
                            <span>Compliance Audit</span>
                        </a>
                    <?php endif; ?>

                    <div class="px-2.5 pt-2 pb-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                        Document Repository
                    </div>

                    <!-- Personal Digital Vault -->
                    <a href="vault.php"
                       class="<?= $activeNav === 'certificates' ? 'sidebar-active' : 'sidebar-hover' ?> flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 transition-all">
                        <i class="fa-solid fa-certificate w-4 text-center"></i>
                        <span><?= $isDean ? 'Department Vault' : 'Personal Vault' ?></span>
                    </a>

                    <!-- Smart Pipeline -->
                    <a href="upload.php"
                       class="<?= $activeNav === 'pipeline' ? 'sidebar-active' : 'sidebar-hover' ?> flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 transition-all">
                        <i class="fa-solid fa-cloud-arrow-up w-4 text-center"></i>
                        <span>Smart Pipeline</span>
                    </a>

                    <!-- Records / Accreditation -->
                    <a href="export.php"
                       class="<?= $activeNav === 'records' ? 'sidebar-active' : 'sidebar-hover' ?> flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 transition-all">
                        <i class="fa-solid fa-folder-open w-4 text-center"></i>
                        <span>Accreditation Records</span>
                    </a>

                    <?php if (!$isDean): ?>
                        <div class="px-2.5 pt-3 pb-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                            Administrative Suite
                        </div>

                        <!-- Locked Dean Items with Informative Tooltip Modal -->
                        <button type="button" onclick="showDeanRestrictedModal('Dean Dashboard')"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-[#14335e]/50 transition-all text-xs text-left">
                            <span class="flex items-center gap-2.5">
                                <i class="fa-solid fa-chart-pie w-4 text-center"></i>
                                <span>Dean's Dashboard</span>
                            </span>
                            <i class="fa-solid fa-lock text-[10px] text-amber-400/80"></i>
                        </button>

                        <button type="button" onclick="showDeanRestrictedModal('User Management')"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-[#14335e]/50 transition-all text-xs text-left">
                            <span class="flex items-center gap-2.5">
                                <i class="fa-solid fa-users-gear w-4 text-center"></i>
                                <span>User Management</span>
                            </span>
                            <i class="fa-solid fa-lock text-[10px] text-amber-400/80"></i>
                        </button>

                        <button type="button" onclick="showDeanRestrictedModal('Compliance Audit Ledger')"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-[#14335e]/50 transition-all text-xs text-left">
                            <span class="flex items-center gap-2.5">
                                <i class="fa-solid fa-clipboard-check w-4 text-center"></i>
                                <span>Compliance Audit</span>
                            </span>
                            <i class="fa-solid fa-lock text-[10px] text-amber-400/80"></i>
                        </button>
                    <?php endif; ?>

                    <div class="px-2.5 pt-3 pb-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                        System Configuration
                    </div>

                    <!-- Settings -->
                    <a href="javascript:void(0)" onclick="openSettingsModal()"
                       class="<?= $activeNav === 'settings' ? 'sidebar-active' : 'sidebar-hover' ?> flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-300 transition-all">
                        <i class="fa-solid fa-gear w-4 text-center"></i>
                        <span>System Settings</span>
                    </a>
                </nav>
            </div>

            <!-- Bottom User Card & Sign Out matching Figure 4.3 -->
            <div class="p-3 border-t border-[#14335e] space-y-2">
                <a href="logout.php"
                   class="flex items-center justify-between px-3 py-2 rounded-lg bg-[#14335e]/60 hover:bg-[#14335e] text-xs text-slate-300 hover:text-white transition-all">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Sign Out</span>
                    </span>
                    <i class="fa-solid fa-angles-right text-[10px] text-slate-400"></i>
                </a>
            </div>
        </aside>

        <!-- Right Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- Unified Top Header Bar matching Figure 4.3 -->
            <header class="h-14 bg-white dark:bg-[#0c1830] border-b border-slate-200 dark:border-slate-800/80 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-sm flex-shrink-0 transition-colors">
                <div class="flex items-center gap-3">
                    <!-- Hamburger button on mobile -->
                    <button type="button" onclick="toggleSidebar(true)" aria-label="Open Navigation Menu"
                            class="text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 lg:hidden text-base p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition-colors">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <!-- Global Functional Search Bar -->
                    <form method="GET" action="<?= htmlspecialchars($searchAction) ?>" class="relative w-48 sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </div>
                        <input type="text" name="search" value="<?= htmlspecialchars($currentSearch) ?>"
                               placeholder="Search records, titles, issuers..."
                               class="w-full pl-8 pr-7 py-1.5 bg-slate-100 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/80 rounded-lg text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:bg-white dark:focus:bg-slate-900 focus:border-blue-500 transition-all">
                        <?php if (!empty($currentSearch)): ?>
                            <a href="<?= htmlspecialchars($searchAction) ?>" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 text-xs">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Right Header Actions matching Figure 4.3 -->
                <div class="flex items-center gap-2 sm:gap-3">

                    <!-- Theme Toggle Button (Light / Dark Mode) -->
                    <button type="button" onclick="toggleTheme()" id="themeToggleBtn" aria-label="Toggle Dark / Light Mode"
                            class="touch-target p-2 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors cursor-pointer"
                            title="Toggle Theme">
                        <i class="fa-solid fa-moon dark:hidden text-sm"></i>
                        <i class="fa-solid fa-sun hidden dark:inline text-sm text-amber-400"></i>
                    </button>

                    <!-- Interactive Notification Bell -->
                    <div class="relative">
                        <button type="button" onclick="toggleNotifications()" aria-label="View System Notifications"
                                class="touch-target text-slate-400 hover:text-slate-600 dark:text-slate-400 dark:hover:text-slate-200 p-2 rounded-lg text-sm hover:bg-slate-100 dark:hover:bg-slate-800/80 relative transition-colors">
                            <i class="fa-regular fa-bell"></i>
                            <span class="w-2 h-2 rounded-full bg-blue-600 absolute top-1.5 right-1.5 ring-2 ring-white dark:ring-[#0c1830]"></span>
                        </button>

                        <!-- Notification Dropdown Menu -->
                        <div id="notifications-dropdown"
                             class="hidden origin-top-right-menu absolute right-0 mt-2 w-80 bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl py-2 z-50 text-xs">
                            <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <span class="font-bold text-slate-900 dark:text-slate-100">System Telemetry & Alerts</span>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">Online</span>
                            </div>
                            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex gap-2.5">
                                    <i class="fa-solid fa-shield-halved text-blue-600 dark:text-blue-400 mt-0.5"></i>
                                    <div>
                                        <p class="font-semibold text-slate-800 dark:text-slate-200 text-[11px]">AES-256 Vault Active</p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Volatile memory decryption with zero plain disk exposure.</p>
                                    </div>
                                </div>
                                <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex gap-2.5">
                                    <i class="fa-solid fa-gauge-high text-indigo-600 dark:text-indigo-400 mt-0.5"></i>
                                    <div>
                                        <p class="font-semibold text-slate-800 dark:text-slate-200 text-[11px]">API Quota Monitored</p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 tabular-nums">25,000 threshold synchronized with cloud OCR engine.</p>
                                    </div>
                                </div>
                                <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex gap-2.5">
                                    <i class="fa-solid fa-clipboard-check text-emerald-600 dark:text-emerald-400 mt-0.5"></i>
                                    <div>
                                        <p class="font-semibold text-slate-800 dark:text-slate-200 text-[11px]">RA 10173 Audit Ledger</p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Real-time immutable logging active for all operations.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive User Profile Dropdown Pill matching Figure 4.3 -->
                    <div class="relative">
                        <button type="button" id="userMenuBtn" onclick="toggleUserDropdown()"
                                class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/80 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-all text-left">
                            <div class="w-8 h-8 rounded-full <?= $isDean ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300' ?> flex items-center justify-center font-bold text-xs shadow-inner">
                                <i class="<?= $isDean ? 'fa-solid fa-user-shield' : 'fa-solid fa-user-graduate' ?>"></i>
                            </div>
                            <div class="hidden sm:block text-left text-xs leading-tight">
                                <div class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1">
                                    <span><?= htmlspecialchars($currentUser['full_name']) ?></span>
                                    <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                </div>
                                <div class="text-[10px] text-slate-400 font-medium">
                                    <?= $isDean ? "Dean / Administrator" : "CICI Faculty Member" ?>
                                </div>
                            </div>
                        </button>

                        <!-- User Profile Dropdown Menu -->
                        <div id="user-dropdown"
                             class="hidden origin-top-right-menu absolute right-0 mt-2 w-64 bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl py-2 z-50 text-xs">
                            <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="font-bold text-slate-900 dark:text-slate-100 truncate"><?= htmlspecialchars($currentUser['full_name']) ?></p>
                                    <button type="button" onclick="openSettingsModal(); toggleUserDropdown(false); setTimeout(() => document.getElementById('self_display_name_input')?.focus(), 50);"
                                            title="Change Display Name"
                                            class="text-[10px] text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 flex-shrink-0 cursor-pointer">
                                        <i class="fa-solid fa-pen text-[9px]"></i>
                                        <span>Edit</span>
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono"><?= htmlspecialchars($currentUser['username']) ?> &bull; <?= htmlspecialchars($currentUser['department'] ?? 'CICI') ?></p>
                                <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded <?= $isDean ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' ?>">
                                    <?= $isDean ? 'Administrative Dean Access' : 'Faculty Access' ?>
                                </span>
                            </div>

                            <div class="py-1">
                                <?php if ($isDean): ?>
                                    <a href="vault.php" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                        <i class="fa-solid fa-building-columns w-4 text-indigo-500"></i>
                                        <span>Institutional Department Vault</span>
                                    </a>
                                    <a href="vault.php?faculty_id=<?= (int)$currentUser['id'] ?>" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                        <i class="fa-solid fa-user-check w-4 text-emerald-500"></i>
                                        <span>My Personal Records</span>
                                    </a>
                                    <a href="upload.php" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                        <i class="fa-solid fa-cloud-arrow-up w-4 text-slate-400"></i>
                                        <span>Ingest / Process Document</span>
                                    </a>
                                <?php else: ?>
                                    <a href="vault.php" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                        <i class="fa-solid fa-certificate w-4 text-emerald-500"></i>
                                        <span>My Personal Vault</span>
                                    </a>
                                    <a href="upload.php" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                        <i class="fa-solid fa-cloud-arrow-up w-4 text-slate-400"></i>
                                        <span>Ingest New Document</span>
                                    </a>
                                <?php endif; ?>
                                <button type="button" onclick="openSettingsModal(); toggleUserDropdown(false)"
                                        class="w-full flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors text-left">
                                    <i class="fa-solid fa-gear w-4 text-slate-400"></i>
                                    <span>Preferences & Profile Switch</span>
                                </button>
                            </div>

                            <div class="pt-1 border-t border-slate-100 dark:border-slate-800">
                                <a href="logout.php" class="flex items-center gap-2.5 px-4 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-slate-800/60 transition-colors font-medium">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-4"></i>
                                    <span>Sign Out</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Page Dynamic Body Container -->
            <main id="main-workspace-content" class="flex-1 p-4 sm:p-6 space-y-4">
