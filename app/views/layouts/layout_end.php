<?php
/**
 * Shared Master Layout End
 * Includes bottom caption, settings modal, system confirmation/alert modals, and scripts
 */
?>
            </main>

            <!-- Standard System Footer -->
            <!-- Standard System Footer -->
            <footer class="py-3 px-6 bg-white dark:bg-[#0c1830] border-t border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2 flex-shrink-0 transition-colors">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700 dark:text-slate-200">SecureFaculty</span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span>ISUFST CICI Systematic Records Management</span>
                </div>
                <div class="flex items-center gap-3 text-[11px] text-slate-400 dark:text-slate-500">
                    <span>RA 10173 (Data Privacy Act) Compliant</span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span>AES-256 Encrypted Vault</span>
                </div>
            </footer>

        </div>

    </div>

    <!-- 1. System Preferences & Settings Modal -->
    <div id="settings-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden" onclick="if(event.target===this)closeSettingsModal()">
        <div class="bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-gear text-slate-600 dark:text-slate-400 text-sm"></i>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">System Preferences & Theming</h3>
                </div>
                <button type="button" onclick="closeSettingsModal()" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <!-- Theme Mode Setting -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80">
                    <span class="font-semibold text-slate-700 dark:text-slate-200 block mb-1.5">Appearance & Color Scheme:</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="setTheme('light')"
                                class="py-2 px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold flex items-center justify-center gap-2 transition-all">
                            <i class="fa-solid fa-sun text-amber-500"></i>
                            <span>Light Mode</span>
                        </button>
                        <button type="button" onclick="setTheme('dark')"
                                class="py-2 px-3 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold flex items-center justify-center gap-2 transition-all">
                            <i class="fa-solid fa-moon text-blue-400"></i>
                            <span>Dark Mode</span>
                        </button>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80">
                    <span class="font-semibold text-slate-700 dark:text-slate-200 block">Database Engine:</span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5"><?= DB_DRIVER === 'sqlite' ? 'SQLite Active (<code class="text-blue-600 dark:text-blue-400 font-mono">securefaculty.sqlite</code>)' : 'MySQL Production Engine (' . htmlspecialchars(DB_NAME) . ')' ?>. Dual-compatible architecture.</p>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80">
                    <span class="font-semibold text-slate-700 dark:text-slate-200 block">Cryptographic Layer:</span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">AES-256-CBC with randomized IVs and Just-in-Time Decryption to memory.</p>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80">
                    <span class="font-semibold text-slate-700 dark:text-slate-200 block">Active User & One-Gate Switcher:</span>
                    <p class="text-[10px] text-slate-400 mb-2">Instantly switch between Dean and Faculty test profiles for system evaluation:</p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="login.php" onclick="event.preventDefault(); switchUser('dean', 'admin123')"
                           class="py-2 px-3 text-center rounded-lg bg-[#0d2342] hover:bg-[#14335e] text-white font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-user-shield text-xs"></i>
                            <span>Dean Profile</span>
                        </a>
                        <a href="login.php" onclick="event.preventDefault(); switchUser('mikhail', 'faculty123')"
                           class="py-2 px-3 text-center rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-user-graduate text-xs"></i>
                            <span>Faculty Profile</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-right">
                <button type="button" onclick="closeSettingsModal()" class="px-4 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- 2. System Universal Alert & Validation Modal (Replaces browser alert()) -->
    <div id="system-alert-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-sm w-full p-6 text-center space-y-4 animate-in fade-in zoom-in-95">
            <div id="alert-icon-container" class="w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl shadow-inner">
                <!-- Icon injected dynamically -->
            </div>

            <div>
                <h3 id="alert-modal-title" class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight"></h3>
                <p id="alert-modal-message" class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed"></p>
            </div>

            <div class="pt-2">
                <button type="button" id="alert-modal-button"
                    class="w-full py-2 px-4 rounded-xl font-bold text-xs shadow-md transition-all">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- 3. System Universal Confirmation Modal (Replaces browser confirm()) -->
    <div id="system-confirm-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95">
            <div class="flex items-start gap-3.5">
                <div id="confirm-icon-box" class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0">
                    <!-- Icon injected dynamically -->
                </div>
                <div>
                    <h3 id="confirm-modal-title" class="text-sm font-bold text-slate-900 dark:text-slate-100">Confirm Action</h3>
                    <p id="confirm-modal-message" class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed"></p>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2 text-xs">
                <button type="button" id="confirm-modal-cancel"
                    class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold transition-all">
                    Cancel
                </button>
                <button type="button" id="confirm-modal-proceed"
                    class="px-4 py-2 rounded-xl font-bold text-white shadow-md transition-all">
                    Proceed
                </button>
            </div>
        </div>
    </div>

    <!-- Global Modal Controller Script -->
    <script>
        function toggleSidebar(open) {
            const sidebar = document.getElementById('main-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (!sidebar) return;
            const isCurrentlyHidden = sidebar.classList.contains('-translate-x-full');
            const shouldOpen = (open !== undefined) ? open : isCurrentlyHidden;

            if (shouldOpen) {
                sidebar.classList.remove('-translate-x-full');
                if (backdrop) backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                if (backdrop) backdrop.classList.add('hidden');
            }
        }

        function toggleNotifications() {
            const drop = document.getElementById('notifications-dropdown');
            const userDrop = document.getElementById('user-dropdown');
            if (userDrop) userDrop.classList.add('hidden');
            if (drop) drop.classList.toggle('hidden');
        }

        function toggleUserDropdown() {
            const userDrop = document.getElementById('user-dropdown');
            const notifDrop = document.getElementById('notifications-dropdown');
            if (notifDrop) notifDrop.classList.add('hidden');
            if (userDrop) userDrop.classList.toggle('hidden');
        }

        function showDeanRestrictedModal(featureName) {
            showSystemAlert({
                title: 'Dean Clearance Required',
                message: `Access to "${featureName}" is restricted to academic administrative officers (Dean). You are currently logged in with a Faculty profile. You can explore your Personal Vault or switch test profiles in Settings.`,
                type: 'warning',
                buttonText: 'Understood'
            });
        }

        function openSettingsModal() {
            document.getElementById('settings-modal').classList.remove('hidden');
        }
        function closeSettingsModal() {
            document.getElementById('settings-modal').classList.add('hidden');
        }

        function switchUser(u, p) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'login.php';
            const userIn = document.createElement('input');
            userIn.name = 'username';
            userIn.value = u;
            form.appendChild(userIn);
            const passIn = document.createElement('input');
            passIn.name = 'password';
            passIn.value = p;
            form.appendChild(passIn);
            document.body.appendChild(form);
            form.submit();
        }

        /**
         * Show custom styled System Alert Modal
         */
        function showSystemAlert(opts = {}) {
            const modal = document.getElementById('system-alert-modal');
            const titleEl = document.getElementById('alert-modal-title');
            const msgEl = document.getElementById('alert-modal-message');
            const iconBox = document.getElementById('alert-icon-container');
            const btn = document.getElementById('alert-modal-button');

            const type = opts.type || 'info';
            titleEl.textContent = opts.title || (type === 'success' ? 'Operation Successful' : (type === 'error' ? 'Error Encountered' : 'System Validation'));
            msgEl.innerHTML = opts.message || '';

            if (type === 'success') {
                iconBox.className = 'w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl bg-emerald-100 text-emerald-600';
                iconBox.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
                btn.className = 'w-full py-2 px-4 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20';
            } else if (type === 'error') {
                iconBox.className = 'w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl bg-rose-100 text-rose-600';
                iconBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
                btn.className = 'w-full py-2 px-4 rounded-xl font-bold text-xs bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20';
            } else if (type === 'warning') {
                iconBox.className = 'w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl bg-amber-100 text-amber-600';
                iconBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i>';
                btn.className = 'w-full py-2 px-4 rounded-xl font-bold text-xs bg-amber-600 hover:bg-amber-500 text-white shadow-md shadow-amber-600/20';
            } else {
                iconBox.className = 'w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl bg-blue-100 text-blue-600';
                iconBox.innerHTML = '<i class="fa-solid fa-circle-info"></i>';
                btn.className = 'w-full py-2 px-4 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-500 text-white shadow-md shadow-blue-600/20';
            }

            btn.textContent = opts.buttonText || 'Understood';

            btn.onclick = function() {
                modal.classList.add('hidden');
                if (typeof opts.onOk === 'function') {
                    opts.onOk();
                }
            };

            modal.classList.remove('hidden');
        }

        /**
         * Show custom styled System Confirmation Modal
         */
        function showSystemConfirm(opts = {}) {
            const modal = document.getElementById('system-confirm-modal');
            const titleEl = document.getElementById('confirm-modal-title');
            const msgEl = document.getElementById('confirm-modal-message');
            const iconBox = document.getElementById('confirm-icon-box');
            const cancelBtn = document.getElementById('confirm-modal-cancel');
            const proceedBtn = document.getElementById('confirm-modal-proceed');

            titleEl.textContent = opts.title || 'Confirm Action';
            msgEl.innerHTML = opts.message || 'Are you sure you wish to proceed with this operation?';

            const type = opts.type || 'danger';
            if (type === 'danger') {
                iconBox.className = 'w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg';
                iconBox.innerHTML = '<i class="fa-regular fa-trash-can"></i>';
                proceedBtn.className = 'px-4 py-2 rounded-xl font-bold text-white bg-rose-600 hover:bg-rose-500 text-xs shadow-md shadow-rose-600/20';
            } else if (type === 'warning') {
                iconBox.className = 'w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg';
                iconBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
                proceedBtn.className = 'px-4 py-2 rounded-xl font-bold text-white bg-amber-600 hover:bg-amber-500 text-xs shadow-md shadow-amber-600/20';
            } else {
                iconBox.className = 'w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg';
                iconBox.innerHTML = '<i class="fa-solid fa-shield-halved"></i>';
                proceedBtn.className = 'px-4 py-2 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-500 text-xs shadow-md shadow-blue-600/20';
            }

            cancelBtn.textContent = opts.cancelText || 'Cancel';
            proceedBtn.textContent = opts.confirmText || 'Proceed';

            cancelBtn.onclick = function() {
                modal.classList.add('hidden');
                if (typeof opts.onCancel === 'function') opts.onCancel();
            };

            proceedBtn.onclick = function() {
                modal.classList.add('hidden');
                if (typeof opts.onConfirm === 'function') opts.onConfirm();
            };

            modal.classList.remove('hidden');
        }

        // Global Uniform Theme Controller
        function toggleTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            setTheme(isDark ? 'light' : 'dark');
        }

        function setTheme(theme) {
            const meta = document.querySelector('meta[name="color-scheme"]');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('securefaculty_theme', 'dark');
                if (meta) meta.setAttribute('content', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('securefaculty_theme', 'light');
                if (meta) meta.setAttribute('content', 'light');
            }
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: theme } }));
        }

        // Listen for OS theme preference changes dynamically if no explicit user override
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('securefaculty_theme')) {
                    setTheme(e.matches ? 'dark' : 'light');
                }
            });
        }

        // Seamless Polyfill: Intercept any standard browser alert() and render our styled modal
        window.alert = function(message) {
            showSystemAlert({ message: message });
        };

        // Close dropdowns on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#notifications-dropdown') && !e.target.closest('[onclick="toggleNotifications()"]')) {
                const drop = document.getElementById('notifications-dropdown');
                if (drop) drop.classList.add('hidden');
            }
            if (!e.target.closest('#user-dropdown') && !e.target.closest('[onclick="toggleUserDropdown()"]')) {
                const drop = document.getElementById('user-dropdown');
                if (drop) drop.classList.add('hidden');
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSettingsModal();
                document.getElementById('system-alert-modal')?.classList.add('hidden');
                document.getElementById('system-confirm-modal')?.classList.add('hidden');
                document.getElementById('notifications-dropdown')?.classList.add('hidden');
                document.getElementById('user-dropdown')?.classList.add('hidden');
                toggleSidebar(false);
            }
        });

        // Automatic check for unauthorized_role parameter
        <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized_role'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showSystemAlert({
                title: 'Access Clearance Required',
                message: 'You attempted to access an administrative screen (Dean Dashboard / Audit Ledger) which requires Dean privileges. You have been safely returned to your Personal Vault.',
                type: 'warning',
                buttonText: 'Continue to My Vault'
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
