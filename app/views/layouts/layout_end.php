<?php
/**
 * Shared Master Layout End
 * Includes bottom caption, settings modal, system confirmation/alert modals, and scripts
 */
?>
            </main>

            <!-- Standard System Footer -->
            <footer class="py-3.5 px-6 safe-area-bottom bg-white dark:bg-[#0c1830] border-t border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-between text-sm text-slate-500 dark:text-slate-400 gap-2 flex-shrink-0 transition-colors">
                <div class="flex items-center gap-2.5">
                    <img src="assets/images/isufst_logo.png" alt="ISUFST" class="w-6 h-5 object-contain inline-block">
                    <span class="font-extrabold text-slate-800 dark:text-slate-200">SecureFaculty</span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span class="font-medium">ISUFST CICI Systematic Records Management</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 font-medium">
                    <span>RA 10173 (Data Privacy Act) Compliant</span>
                    <span class="text-slate-300 dark:text-slate-600">•</span>
                    <span>AES-256 Encrypted Vault</span>
                </div>
            </footer>

        </div>

    </div>

    <!-- 1. System Preferences & Settings Modal -->
    <div id="settings-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden" onclick="if(event.target===this)closeSettingsModal()">
        <div class="modal-card-anim bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-gear text-slate-600 dark:text-slate-400 text-sm"></i>
                    <h3 class="modal-title text-sm font-bold text-slate-900 dark:text-slate-100">System Preferences & Theming</h3>
                </div>
                <button type="button" onclick="closeSettingsModal()" aria-label="Close Settings Modal" class="touch-target text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <!-- Editable Profile Display Name (Dean / Faculty Self-Service) -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80 space-y-1.5">
                    <label for="self_display_name_input" class="font-semibold text-slate-700 dark:text-slate-200 block">
                        My Profile Display Name (<?= ($isDean ?? false) ? 'Dean Signatory' : 'Faculty Holder' ?>):
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="self_display_name_input" maxlength="150"
                               value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>"
                               placeholder="Enter your official name & honorifics..."
                               onkeydown="if(event.key==='Enter'){event.preventDefault();saveSelfDisplayName();}"
                               class="flex-1 px-2.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500">
                        <button type="button" id="btn_save_self_name" onclick="saveSelfDisplayName()"
                                class="px-3 py-1.5 rounded-lg bg-[#1da055] hover:bg-[#188c49] text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 cursor-pointer flex-shrink-0">
                            <i class="fa-solid fa-check text-[10px]"></i>
                            <span>Save Name</span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500">Updates your name across the header, vault, audit logs, and official PDF/Print signature blocks.</p>
                </div>

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

                <!-- Screen Zoom / Display Scale Setting -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80">
                    <span class="font-semibold text-slate-700 dark:text-slate-200 block mb-1.5 flex items-center justify-between">
                        <span>Display Scale &amp; Screen Zoom:</span>
                        <span id="currentZoomBadge" class="text-[10px] font-mono px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-bold">107% (Ergonomic)</span>
                    </span>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <button type="button" onclick="setSystemZoom('1.0')" id="zoomBtn_100"
                                class="py-2 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold flex flex-col items-center justify-center transition-all cursor-pointer">
                            <span class="font-bold">100%</span>
                            <span class="text-[9px] text-slate-400">Standard</span>
                        </button>
                        <button type="button" onclick="setSystemZoom('1.07')" id="zoomBtn_107"
                                class="py-2 px-2 rounded-lg border-2 border-blue-500 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-bold flex flex-col items-center justify-center transition-all shadow-sm cursor-pointer">
                            <span class="font-bold">107%</span>
                            <span class="text-[9px] text-blue-600 dark:text-blue-400 font-semibold">Ergonomic</span>
                        </button>
                        <button type="button" onclick="setSystemZoom('1.15')" id="zoomBtn_115"
                                class="py-2 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold flex flex-col items-center justify-center transition-all cursor-pointer">
                            <span class="font-bold">115%</span>
                            <span class="text-[9px] text-slate-400">Presentation</span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5">Enlarges typography and dashboard cards closer for clean readability without overflowing the screen.</p>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80">
                    <span class="font-semibold text-slate-700 dark:text-slate-200 block">Database Engine:</span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5"><?= DB_DRIVER === 'sqlite' ? 'SQLite Active (<code class="text-blue-600 dark:text-blue-400 font-mono">securefaculty.sqlite</code>)' : 'MySQL Production Engine (' . htmlspecialchars(DB_NAME) . ')' ?>. Dual-compatible architecture.</p>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80">
                    <span class="font-semibold text-slate-700 dark:text-slate-200 block">Cryptographic Layer:</span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">AES-256-CBC with randomized IVs and Just-in-Time Decryption to memory.</p>
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
    <div id="system-alert-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden" onclick="if(event.target===this){this.classList.add('hidden');if(typeof setBodyModalLock==='function')setBodyModalLock(false);}">
        <div class="modal-card-anim bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-sm w-full p-6 text-center space-y-4">
            <div id="alert-icon-container" class="optical-center w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl shadow-inner">
                <!-- Icon injected dynamically -->
            </div>

            <div>
                <h3 id="alert-modal-title" class="modal-title text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight"></h3>
                <p id="alert-modal-message" class="pretty-text prose-measure text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed"></p>
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
    <div id="system-confirm-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden" onclick="if(event.target===this){this.classList.add('hidden');if(typeof setBodyModalLock==='function')setBodyModalLock(false);}">
        <div class="modal-card-anim bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-start gap-3.5">
                <div id="confirm-icon-box" class="optical-center w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0">
                    <!-- Icon injected dynamically -->
                </div>
                <div>
                    <h3 id="confirm-modal-title" class="modal-title text-sm font-bold text-slate-900 dark:text-slate-100">Confirm Action</h3>
                    <p id="confirm-modal-message" class="pretty-text prose-measure text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed"></p>
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

    <!-- 5. Interactive System Tutorial Modal Dialog -->
    <div id="system-tutorial-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden" onclick="if(event.target===this)closeSystemTutorial()">
        <div class="modal-card-anim bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-[#0d2342] to-[#1e447b] text-white flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center shadow-md flex-shrink-0 overflow-hidden">
                        <img src="assets/images/isufst_logo.png" alt="ISUFST Logo" class="max-w-full max-h-full object-contain">
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold tracking-tight">SecureFaculty System Guide &amp; Walkthrough</h3>
                        <p class="text-xs text-blue-200">Interactive step-by-step tutorial for faculty &amp; administrators</p>
                    </div>
                </div>
                <button type="button" onclick="closeSystemTutorial()" aria-label="Close Tutorial" class="touch-target text-slate-300 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="px-4 pt-3 pb-2 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 overflow-x-auto flex gap-2 flex-shrink-0 touch-pan-x scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600" style="scrollbar-width: thin; -webkit-overflow-scrolling: touch;">
                <button type="button" onclick="switchTutorialTab('overview')" data-tab="overview"
                        class="tut-tab-btn whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-extrabold bg-[#0d2342] dark:bg-blue-600 text-white shadow-sm flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-compass"></i>
                    <span>Quick Overview</span>
                </button>
                <button type="button" onclick="switchTutorialTab('upload')" data-tab="upload"
                        class="tut-tab-btn whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-cloud-arrow-up text-amber-500"></i>
                    <span>Upload &amp; OCR</span>
                </button>
                <button type="button" onclick="switchTutorialTab('vault')" data-tab="vault"
                        class="tut-tab-btn whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-layer-group text-blue-500"></i>
                    <span>Vault &amp; 7 Categories</span>
                </button>
                <button type="button" onclick="switchTutorialTab('export')" data-tab="export"
                        class="tut-tab-btn whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-file-pdf text-rose-500"></i>
                    <span>Accreditation &amp; PDF</span>
                </button>
                <button type="button" onclick="switchTutorialTab('security')" data-tab="security"
                        class="tut-tab-btn whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center gap-2 transition-all">
                    <i class="fa-solid fa-shield-halved text-emerald-500"></i>
                    <span>Security &amp; Plan B</span>
                </button>
            </div>

            <!-- Tab Content Scrollable Area -->
            <div class="p-5 overflow-y-auto space-y-4 text-sm text-slate-700 dark:text-slate-300 flex-1">

                <!-- Tab 1: Overview -->
                <div id="tut-pane-overview" class="tut-content-pane space-y-4">
                    <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 space-y-2">
                        <div class="flex items-center gap-2 font-bold text-blue-900 dark:text-blue-200 text-sm">
                            <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400"></i>
                            <span>What is SecureFaculty?</span>
                        </div>
                        <p class="text-xs sm:text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                            SecureFaculty is ISUFST CICI's cryptographic faculty records and accreditation management system. It digitizes physical certificates, extracts academic metadata via cloud OCR, secures documents with AES-256 encryption, and produces audit-ready AACCUP/CHED PDF ledgers with cryptographic verification.
                        </p>
                    </div>

                    <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-users-gear text-indigo-500"></i>
                        <span>Understanding System Roles</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/60 dark:bg-indigo-950/30 space-y-1.5">
                            <div class="flex items-center gap-2 text-indigo-900 dark:text-indigo-200 font-bold text-xs">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                <span>Dean / Administrator</span>
                            </div>
                            <ul class="text-xs space-y-1 text-slate-600 dark:text-slate-400 list-disc list-inside">
                                <li>Access to centralized <strong>Dean's Dashboard</strong> &amp; Quota Monitor</li>
                                <li>Manages all department faculty accounts in <strong>User Management</strong></li>
                                <li>Oversees the complete <strong>Compliance Audit Ledger</strong></li>
                                <li>Downloads <strong>Plan B DB Backup</strong> snapshots</li>
                            </ul>
                        </div>
                        <div class="p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50/60 dark:bg-emerald-950/30 space-y-1.5">
                            <div class="flex items-center gap-2 text-emerald-900 dark:text-emerald-200 font-bold text-xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Faculty Member</span>
                            </div>
                            <ul class="text-xs space-y-1 text-slate-600 dark:text-slate-400 list-disc list-inside">
                                <li>Uploads &amp; extracts seminars, awards, &amp; publications</li>
                                <li>Views and organizes their isolated <strong>Personal Vault</strong></li>
                                <li>Selects co-authors for shared academic publications</li>
                                <li>Generates personalized accreditation reports for evaluation</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Upload & OCR -->
                <div id="tut-pane-upload" class="tut-content-pane hidden space-y-4">
                    <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 text-amber-900 dark:text-amber-200 text-xs flex items-start gap-2.5">
                        <i class="fa-solid fa-lightbulb text-amber-600 dark:text-amber-400 text-sm mt-0.5 flex-shrink-0"></i>
                        <span><strong>Tip:</strong> Supported formats include PDF, Word documents (.docx), and high-resolution images (.png, .jpg). Word files are automatically converted to clean PDF certificates on ingestion.</span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
                            <span class="w-6 h-6 rounded-full bg-[#0d2342] dark:bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">1</span>
                            <div>
                                <strong class="text-slate-900 dark:text-slate-100 block text-xs sm:text-sm">Stage Your Document</strong>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Click <strong>Upload &amp; Scan</strong> in the sidebar. Drag &amp; drop your academic asset or browse your local file system.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
                            <span class="w-6 h-6 rounded-full bg-[#0d2342] dark:bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">2</span>
                            <div>
                                <strong class="text-slate-900 dark:text-slate-100 block text-xs sm:text-sm">Automated OCR Extraction</strong>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">The system analyzes the document in seconds, extracting the <em>Document Title, Issued Date, Issuing Authority</em>, and <em>Certificate Number</em> automatically.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
                            <span class="w-6 h-6 rounded-full bg-[#0d2342] dark:bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">3</span>
                            <div>
                                <strong class="text-slate-900 dark:text-slate-100 block text-xs sm:text-sm">Human-in-the-Loop (HITL) Verification</strong>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Review the extracted fields, choose from the <strong>7 Standard Academic Categories</strong>, assign any co-authors, and click <strong>Commit to Vault</strong>.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="upload.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0d2342] dark:bg-blue-600 hover:bg-[#14335e] dark:hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition-all">
                            <span>Go to Upload &amp; Scan</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Tab 3: Vault & Categories -->
                <div id="tut-pane-vault" class="tut-content-pane hidden space-y-4">
                    <p class="text-xs sm:text-sm leading-relaxed">
                        Every document in the vault is standardized across <strong>7 official academic categories</strong> recognized by CHED &amp; AACCUP evaluators:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                        <?php foreach (CategoryService::CATEGORIES as $k => $c): ?>
                            <div class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-lg <?= $c['badge_bg'] ?> flex items-center justify-center flex-shrink-0">
                                    <i class="<?= $c['icon'] ?> text-xs"></i>
                                </span>
                                <div>
                                    <strong class="text-slate-900 dark:text-slate-100 block"><?= htmlspecialchars($c['short_label']) ?></strong>
                                    <span class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($c['label']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 space-y-1.5">
                        <strong class="text-blue-900 dark:text-blue-200 text-xs font-bold block flex items-center gap-1.5">
                            <i class="fa-solid fa-eye text-blue-600 dark:text-blue-400"></i>
                            <span>In-App Document Viewer</span>
                        </strong>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            You can inspect any uploaded certificate without opening new tabs. Click <strong>"View"</strong> on any record in the table or grid to load the full cryptographic certificate in a seamless overlay.
                        </p>
                    </div>

                    <div class="pt-2">
                        <a href="vault.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#0d2342] dark:bg-blue-600 hover:bg-[#14335e] dark:hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition-all">
                            <span>Open Digital Vault</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Tab 4: Accreditation & PDF -->
                <div id="tut-pane-export" class="tut-content-pane hidden space-y-4">
                    <p class="text-xs sm:text-sm leading-relaxed">
                        Accreditation Records enables one-click generation of official audit binders for AACCUP and CHED institutional visits.
                    </p>

                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
                            <span class="w-6 h-6 rounded-full bg-rose-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">1</span>
                            <div>
                                <strong class="text-slate-900 dark:text-slate-100 block text-xs sm:text-sm">Filter by Accreditation Scope</strong>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Select a category (e.g. <em>Research Publications</em>) or use Quick Range Presets (e.g. <em>Nov - Dec Late Year</em>).</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
                            <span class="w-6 h-6 rounded-full bg-rose-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">2</span>
                            <div>
                                <strong class="text-slate-900 dark:text-slate-100 block text-xs sm:text-sm">Cryptographic Ledger Export</strong>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Click the large red <strong>"Export Official PDF Ledger"</strong> button. The system generates an A4 portrait PDF with page numbers, SHA-256 seal, and official ISUFST letterhead.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="export.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 text-white font-bold text-xs shadow-md shadow-rose-600/30 transition-all">
                            <span>Open Accreditation Records</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Tab 5: Security & Plan B -->
                <div id="tut-pane-security" class="tut-content-pane hidden space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 space-y-1">
                            <strong class="text-slate-900 dark:text-slate-100 block font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-key text-blue-500"></i>
                                <span>AES-256-CBC Encryption</span>
                            </strong>
                            <p class="text-slate-500 dark:text-slate-400">All original documents stored in the vault are encrypted at rest with randomized IVs. No plaintext certificates reside on disk.</p>
                        </div>

                        <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 space-y-1">
                            <strong class="text-slate-900 dark:text-slate-100 block font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-clipboard-check text-emerald-500"></i>
                                <span>Immutable Audit Logging</span>
                            </strong>
                            <p class="text-slate-500 dark:text-slate-400">Compliant with RA 10173 (Data Privacy Act). Every single action (view, download, upload, export) is tracked with timestamp and source IP.</p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 space-y-1.5">
                        <strong class="text-emerald-900 dark:text-emerald-200 text-xs font-bold block flex items-center gap-1.5">
                            <i class="fa-solid fa-database text-emerald-600 dark:text-emerald-400"></i>
                            <span>Plan B Database Backup</span>
                        </strong>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            The Dean can generate and stream an instant database snapshot at any time by clicking <strong>"Plan B DB Backup"</strong>. This ensures full disaster recovery with zero data loss.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/40 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>ISUFST CICI Faculty Records Management</span>
                </div>
                <button type="button" onclick="closeSystemTutorial()" class="px-4 py-2 rounded-xl bg-[#0d2342] dark:bg-slate-800 hover:bg-[#14335e] dark:hover:bg-slate-700 text-white font-bold cursor-pointer transition-all shadow-sm">
                    Got it, close guide
                </button>
            </div>
        </div>
    </div>
    <script>
        /**
         * #20 Shared Element / View Transition Helper
         */
        function runWithViewTransition(callback) {
            if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.startViewTransition(function() {
                    callback();
                });
            } else {
                callback();
            }
        }
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

        function setBodyModalLock(isLocked) {
            if (isLocked) {
                document.body.classList.add('overflow-hidden');
            } else {
                setTimeout(() => {
                    const activeModals = document.querySelectorAll(
                        '#settings-modal:not(.hidden), #system-alert-modal:not(.hidden), #system-confirm-modal:not(.hidden), #stage-progress-modal:not(.hidden), #modal-create-user:not(.hidden), #modal-edit-user:not(.hidden), #modal-reset-pass:not(.hidden), #system-tutorial-modal:not(.hidden)'
                    );
                    if (activeModals.length === 0) {
                        document.body.classList.remove('overflow-hidden');
                    }
                }, 50);
            }
        }

        function openSystemTutorial(tabKey) {
            const modal = document.getElementById('system-tutorial-modal');
            if (!modal) return;
            if (tabKey) switchTutorialTab(tabKey);
            modal.classList.remove('hidden');
            if (typeof setBodyModalLock === 'function') setBodyModalLock(true);
        }

        function closeSystemTutorial() {
            const modal = document.getElementById('system-tutorial-modal');
            if (modal) modal.classList.add('hidden');
            if (typeof setBodyModalLock === 'function') setBodyModalLock(false);
        }

        function switchTutorialTab(tabKey) {
            document.querySelectorAll('.tut-tab-btn').forEach(btn => {
                if (btn.dataset.tab === tabKey) {
                    btn.className = 'tut-tab-btn whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-extrabold bg-[#0d2342] dark:bg-blue-600 text-white shadow-sm flex items-center gap-2 transition-all';
                } else {
                    btn.className = 'tut-tab-btn whitespace-nowrap px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center gap-2 transition-all';
                }
            });
            document.querySelectorAll('.tut-content-pane').forEach(pane => {
                if (pane.id === 'tut-pane-' + tabKey) {
                    pane.classList.remove('hidden');
                } else {
                    pane.classList.add('hidden');
                }
            });
        }

        function openSettingsModal() {
            setBodyModalLock(true);
            document.getElementById('settings-modal').classList.remove('hidden');
        }
        function closeSettingsModal() {
            document.getElementById('settings-modal').classList.add('hidden');
            setBodyModalLock(false);
        }

        function saveSelfDisplayName() {
            const input = document.getElementById('self_display_name_input');
            const btn = document.getElementById('btn_save_self_name');
            if (!input) return;
            const newName = input.value.trim();
            if (!newName) {
                showSystemAlert({
                    title: 'Name Required',
                    message: 'Please enter a valid display name before saving.',
                    type: 'warning'
                });
                return;
            }
            if (btn) btn.disabled = true;

            fetch('api/user_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.CSRF_TOKEN || ''
                },
                body: JSON.stringify({
                    action: 'update_self_name',
                    full_name: newName,
                    csrf_token: window.CSRF_TOKEN || ''
                })
            })
            .then(async r => {
                const data = await r.json().catch(() => ({}));
                if (r.status === 403) {
                    throw new Error(data.message || 'Session or CSRF security token has expired. Please refresh the page or sign in again.');
                }
                if (!r.ok) {
                    throw new Error(data.message || `Request failed with status code ${r.status}`);
                }
                return data;
            })
            .then(data => {
                if (btn) btn.disabled = false;
                if (data.status === 'success') {
                    closeSettingsModal();
                    showSystemAlert({
                        title: 'Display Name Updated',
                        message: `Your profile name has been updated to "${data.full_name || newName}".`,
                        type: 'success',
                        onOk: () => location.reload()
                    });
                } else {
                    showSystemAlert({
                        title: 'Update Failed',
                        message: data.message || 'Unable to update display name.',
                        type: 'error'
                    });
                }
            })
            .catch(err => {
                if (btn) btn.disabled = false;
                showSystemAlert({
                    title: 'Security / Session Error',
                    message: err.message,
                    type: 'error'
                });
            });
        }

        function switchUser(u, p) {
            fetch('api/user_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.CSRF_TOKEN || ''
                },
                body: JSON.stringify({
                    action: 'switch_profile',
                    target_user: u,
                    csrf_token: window.CSRF_TOKEN || ''
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success' && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    fallbackFormSubmit(u, p);
                }
            })
            .catch(() => {
                fallbackFormSubmit(u, p);
            });
        }

        function fallbackFormSubmit(u, p) {
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
            const csrfIn = document.createElement('input');
            csrfIn.name = 'csrf_token';
            csrfIn.value = window.CSRF_TOKEN || '';
            form.appendChild(csrfIn);
            document.body.appendChild(form);
            form.submit();
        }

        /**
         * Real-Time Offline System Monitor (Item 11 Option A)
         */
        function initNetworkMonitor() {
            const banner = document.getElementById('system-offline-banner');
            const msg = document.getElementById('offline-message');
            const icon = document.getElementById('offline-icon');
            if (!banner) return;

            function showOffline() {
                banner.className = 'fixed top-0 inset-x-0 z-[100] px-4 py-2 text-xs font-semibold shadow-md flex items-center justify-between bg-amber-500 text-slate-950 transition-all duration-300';
                if (icon) icon.className = 'fa-solid fa-wifi-slash text-sm mr-2';
                if (msg) msg.textContent = 'System Offline: Local AES-256 Vault & Document Viewing operational. Cloud OCR extraction paused.';
                banner.classList.remove('hidden');
            }

            function showOnline() {
                banner.className = 'fixed top-0 inset-x-0 z-[100] px-4 py-2 text-xs font-semibold shadow-md flex items-center justify-between bg-emerald-600 text-white transition-all duration-300';
                if (icon) icon.className = 'fa-solid fa-wifi text-sm mr-2';
                if (msg) msg.textContent = 'Network Restored: Online services and Cloud OCR reconnected.';
                banner.classList.remove('hidden');
                setTimeout(() => {
                    banner.classList.add('hidden');
                }, 3500);
            }

            window.addEventListener('offline', showOffline);
            window.addEventListener('online', showOnline);

            if (!navigator.onLine) {
                showOffline();
            }
        }
        initNetworkMonitor();

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
                iconBox.className = 'w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400';
                iconBox.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
                btn.className = 'w-full py-2 px-4 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20';
            } else if (type === 'error') {
                iconBox.className = 'w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400';
                iconBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
                btn.className = 'w-full py-2 px-4 rounded-xl font-bold text-xs bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20';
            } else if (type === 'warning') {
                iconBox.className = 'w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400';
                iconBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i>';
                btn.className = 'w-full py-2 px-4 rounded-xl font-bold text-xs bg-amber-600 hover:bg-amber-500 text-white shadow-md shadow-amber-600/20';
            } else {
                iconBox.className = 'w-14 h-14 rounded-full mx-auto flex items-center justify-center text-2xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400';
                iconBox.innerHTML = '<i class="fa-solid fa-circle-info"></i>';
                btn.className = 'w-full py-2 px-4 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-500 text-white shadow-md shadow-blue-600/20';
            }

            btn.textContent = opts.buttonText || 'Understood';

            btn.onclick = function() {
                modal.classList.add('hidden');
                setBodyModalLock(false);
                if (typeof opts.onOk === 'function') {
                    opts.onOk();
                }
            };

            setBodyModalLock(true);
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
                iconBox.className = 'w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg';
                iconBox.innerHTML = '<i class="fa-regular fa-trash-can"></i>';
                proceedBtn.className = 'px-4 py-2 rounded-xl font-bold text-white bg-rose-600 hover:bg-rose-500 text-xs shadow-md shadow-rose-600/20';
            } else if (type === 'warning') {
                iconBox.className = 'w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg';
                iconBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
                proceedBtn.className = 'px-4 py-2 rounded-xl font-bold text-white bg-amber-600 hover:bg-amber-500 text-xs shadow-md shadow-amber-600/20';
            } else {
                iconBox.className = 'w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg';
                iconBox.innerHTML = '<i class="fa-solid fa-shield-halved"></i>';
                proceedBtn.className = 'px-4 py-2 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-500 text-xs shadow-md shadow-blue-600/20';
            }

            cancelBtn.textContent = opts.cancelText || 'Cancel';
            proceedBtn.textContent = opts.confirmText || 'Proceed';

            cancelBtn.onclick = function() {
                modal.classList.add('hidden');
                setBodyModalLock(false);
                if (typeof opts.onCancel === 'function') opts.onCancel();
            };

            proceedBtn.onclick = function() {
                modal.classList.add('hidden');
                setBodyModalLock(false);
                if (typeof opts.onConfirm === 'function') opts.onConfirm();
            };

            setBodyModalLock(true);
            modal.classList.remove('hidden');
        }

        // Global Uniform Theme Controller
        function toggleTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            setTheme(isDark ? 'light' : 'dark');
        }

        // Global System Zoom Controller (+6% Ergonomic Default Scale)
        function setSystemZoom(scale) {
            document.documentElement.style.zoom = scale;
            localStorage.setItem('securefaculty_zoom', scale);
            updateZoomButtons(scale);
        }

        function updateZoomButtons(scale) {
            const s = String(scale || localStorage.getItem('securefaculty_zoom') || '1.07');
            const badge = document.getElementById('currentZoomBadge');
            if (badge) {
                if (s === '1.0' || s === '1') badge.textContent = '100% (Standard)';
                else if (s === '1.07') badge.textContent = '107% (Ergonomic)';
                else if (s === '1.15') badge.textContent = '115% (Presentation)';
                else badge.textContent = Math.round(parseFloat(s) * 100) + '%';
            }
            ['100', '107', '115'].forEach(val => {
                const btn = document.getElementById('zoomBtn_' + val);
                if (!btn) return;
                const target = val === '100' ? '1.0' : (val === '107' ? '1.07' : '1.15');
                const isSelected = (s === target) || (val === '100' && (s === '1' || s === '1.0'));
                if (isSelected) {
                    btn.className = 'py-2 px-2 rounded-lg border-2 border-blue-500 bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-bold flex flex-col items-center justify-center transition-all shadow-sm cursor-pointer';
                } else {
                    btn.className = 'py-2 px-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold flex flex-col items-center justify-center transition-all cursor-pointer';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var savedZoom = localStorage.getItem('securefaculty_zoom');
            if (!savedZoom || savedZoom === '1.12') {
                savedZoom = '1.07';
                localStorage.setItem('securefaculty_zoom', '1.07');
            }
            document.documentElement.style.zoom = savedZoom;
            updateZoomButtons(savedZoom);
        });

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
                closeChangePasswordModal();
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

    <!-- Change Password Modal (Self-Service for Faculty & Dean) -->
    <div id="change-password-modal" class="hidden fixed inset-0 z-[80] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#111c36] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="fa-solid fa-key text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Change Password</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Update your personal account credentials</p>
                    </div>
                </div>
                <button type="button" onclick="closeChangePasswordModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="changePasswordForm" onsubmit="submitChangePassword(event)" class="p-5 space-y-3.5 text-xs">
                <div id="cp-error-box" class="hidden p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs"></div>
                <div id="cp-success-box" class="hidden p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs"></div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Current Password</label>
                    <input type="password" id="cp_current" required autocomplete="current-password"
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">New Password (min. 6 characters)</label>
                    <input type="password" id="cp_new" required minlength="6" autocomplete="new-password"
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Confirm New Password</label>
                    <input type="password" id="cp_confirm" required minlength="6" autocomplete="new-password"
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:border-blue-500">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeChangePasswordModal()"
                            class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" id="cp-submit-btn"
                            class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-sm cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openChangePasswordModal() {
            const modal = document.getElementById('change-password-modal');
            const form = document.getElementById('changePasswordForm');
            const errBox = document.getElementById('cp-error-box');
            const okBox = document.getElementById('cp-success-box');
            if (form) form.reset();
            if (errBox) errBox.classList.add('hidden');
            if (okBox) okBox.classList.add('hidden');
            if (modal) modal.classList.remove('hidden');
        }

        function closeChangePasswordModal() {
            document.getElementById('change-password-modal')?.classList.add('hidden');
        }

        async function submitChangePassword(e) {
            e.preventDefault();
            const errBox = document.getElementById('cp-error-box');
            const okBox = document.getElementById('cp-success-box');
            const btn = document.getElementById('cp-submit-btn');
            errBox?.classList.add('hidden');
            okBox?.classList.add('hidden');

            const currentPassword = document.getElementById('cp_current').value;
            const newPassword = document.getElementById('cp_new').value;
            const confirmPassword = document.getElementById('cp_confirm').value;

            if (newPassword !== confirmPassword) {
                errBox.textContent = 'New password and confirmation do not match.';
                errBox.classList.remove('hidden');
                return;
            }

            if (btn) btn.disabled = true;
            try {
                const res = await fetch('api/change_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.CSRF_TOKEN || ''
                    },
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword,
                        confirm_password: confirmPassword,
                        csrf_token: window.CSRF_TOKEN || ''
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    okBox.textContent = data.message || 'Password updated successfully.';
                    okBox.classList.remove('hidden');
                    document.getElementById('changePasswordForm')?.reset();
                    setTimeout(() => closeChangePasswordModal(), 1400);
                } else {
                    errBox.textContent = data.message || 'Failed to update password.';
                    errBox.classList.remove('hidden');
                }
            } catch (err) {
                errBox.textContent = 'Network error while updating password.';
                errBox.classList.remove('hidden');
            } finally {
                if (btn) btn.disabled = false;
            }
        }
    </script>
</body>
</html>
