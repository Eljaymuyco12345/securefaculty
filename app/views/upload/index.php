<?php
$isDean = $isDean ?? false;
$facultyList = $facultyList ?? [];
$activeNav = $activeNav ?? 'pipeline';
$pageTitle = $pageTitle ?? 'Smart Pipeline - H-I-T-L Validation Loop';
require_once __DIR__ . '/../layouts/layout_start.php';
?>

<!-- Title Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
    <div>
        <h1 class="text-base font-extrabold text-slate-800 dark:text-white tracking-tight">
            <?= $isDean ? 'Administrative Ingestion Gateway' : 'Faculty Document Ingestion' ?>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            <?= $isDean ? 'Department-Wide Academic Document Ingestion & Institutional Ledger Clearance' : 'Faculty Personal Portfolio & Credential Archiving' ?>
        </p>
    </div>
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-600 dark:text-slate-300 bg-white dark:bg-[#111c36] border border-slate-200 dark:border-slate-800 px-3 py-1 rounded-full shadow-sm">
            <span class="w-2 h-2 rounded-full <?= $isDean ? 'bg-indigo-500' : 'bg-emerald-500' ?>"></span>
            Clearance: <strong><?= $isDean ? 'Dean / Administrative Oversight' : 'Faculty Member Access' ?></strong>
        </span>
    </div>
</div>

<!-- Hidden file input -->
<input type="file" id="file-input" multiple accept=".jpg,.jpeg,.png,.pdf,.docx,.zip" class="hidden">

<!-- Main Container matching Figure 4.4: SMART PIPELINE - METADATA EXTRACTION LOOP (H-I-T-L Validation) -->
<div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-5">

    <!-- Title Header of Card matching Figure 4.4 -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pb-2 border-b border-slate-100 dark:border-slate-800/60">
        <div>
            <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-slate-100 tracking-wider uppercase inline-block bg-slate-100 dark:bg-slate-800/80 px-3 py-1 rounded-lg border border-slate-200 dark:border-slate-700">
                SMART PIPELINE - METADATA EXTRACTION LOOP (H-I-T-L Validation)
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Multi-stage pipeline: Ingestion &rarr; Rasterization &rarr; OCR Extraction &rarr; H-I-T-L Verification &rarr; AES-256 Vault</p>
        </div>
        <button type="button" onclick="openProgressModal()"
            class="px-3 py-1.5 rounded-lg border border-blue-300 dark:border-blue-800/60 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-bars-progress text-blue-600 dark:text-blue-400"></i>
            <span>Stage Progress Telemetry</span>
        </button>
    </div>

    <!-- 4-Stage Horizontal Pipeline Header (Interactive & Live Animated Workflow) -->
    <div>
        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 mb-1.5 px-1">
            <span class="font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Pipeline Execution Stages:</span>
            <span class="text-[10px] text-blue-600 dark:text-blue-400 italic">Click any stage below to inspect telemetry details</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 text-center text-xs" id="pipeline-stepper">
            <!-- Stage 1 (Active Default - Waiting for Upload) -->
            <button type="button" id="step-1" onclick="handleStageClick(1)"
                class="p-2.5 rounded-lg bg-blue-50 dark:bg-blue-950/40 border-2 border-blue-500 text-blue-800 dark:text-blue-200 font-bold transition-all text-left flex items-center justify-between shadow-sm cursor-pointer">
                <div>
                    <span class="text-[10px] text-blue-600 dark:text-blue-400 block font-mono">Stage 1 (Ready)</span>
                    <strong class="text-xs text-blue-900 dark:text-blue-100">Dynamic Optimization</strong>
                </div>
                <i class="fa-solid fa-compress text-blue-600 dark:text-blue-400 text-sm"></i>
            </button>

            <!-- Stage 2 -->
            <button type="button" id="step-2" onclick="handleStageClick(2)"
                class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/40 hover:bg-slate-100 dark:hover:bg-slate-800/50 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-medium transition-all text-left flex items-center justify-between group shadow-sm cursor-pointer">
                <div>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-mono">Stage 2</span>
                    <strong class="text-xs group-hover:text-blue-600 dark:group-hover:text-blue-400">Cloud Handshake</strong>
                </div>
                <i class="fa-solid fa-cloud-arrow-up text-slate-400 dark:text-slate-500 group-hover:text-blue-600 dark:group-hover:text-blue-400 text-sm"></i>
            </button>

            <!-- Stage 3 -->
            <button type="button" id="step-3" onclick="handleStageClick(3)"
                class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/40 hover:bg-slate-100 dark:hover:bg-slate-800/50 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-medium transition-all text-left flex items-center justify-between group shadow-sm cursor-pointer">
                <div>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-mono">Stage 3</span>
                    <strong class="text-xs group-hover:text-blue-600 dark:group-hover:text-blue-400">H-I-T-L Validation</strong>
                </div>
                <i class="fa-solid fa-user-check text-slate-400 dark:text-slate-500 group-hover:text-blue-600 dark:group-hover:text-blue-400 text-sm"></i>
            </button>

            <!-- Stage 4 -->
            <button type="button" id="step-4" onclick="handleStageClick(4)"
                class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/40 hover:bg-slate-100 dark:hover:bg-slate-800/50 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-medium transition-all text-left flex items-center justify-between group shadow-sm cursor-pointer">
                <div>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-mono">Stage 4</span>
                    <strong class="text-xs group-hover:text-blue-600 dark:group-hover:text-blue-400">Cryptographic Encryption</strong>
                </div>
                <i class="fa-solid fa-shield-halved text-slate-400 dark:text-slate-500 group-hover:text-blue-600 dark:group-hover:text-blue-400 text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Collapsible Technical Detail Banner for Clicked Stage -->
    <div id="stage-info-box" class="p-3 bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/50 rounded-xl text-xs text-slate-700 dark:text-slate-300 hidden animate-in fade-in duration-200">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2">
                <i id="stage-info-icon" class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 text-base"></i>
                <div>
                    <strong id="stage-info-title" class="text-slate-900 dark:text-white text-xs">Stage Architecture Details</strong>
                    <p id="stage-info-desc" class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5"></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="openProgressModal()" class="text-[11px] font-bold text-blue-700 dark:text-blue-400 hover:underline">
                    View Live Telemetry Modal &rarr;
                </button>
                <button type="button" onclick="document.getElementById('stage-info-box').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Pipeline Flow Visual Guidance Banner -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/40 dark:to-indigo-950/40 border border-blue-200/80 dark:border-blue-900/50 rounded-xl p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">1</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">START HERE: Choose a Certificate, Document, or ZIP Archive</span>
        </div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono flex items-center gap-1.5 flex-wrap">
            <span class="text-blue-700 dark:text-blue-400 font-semibold">1. Upload/Unpack</span>
            <span>&rarr;</span>
            <span class="text-indigo-700 dark:text-indigo-400 font-semibold">2. OCR Extract</span>
            <span>&rarr;</span>
            <span class="text-emerald-700 dark:text-emerald-400 font-semibold">3. Validate Fields</span>
            <span>&rarr;</span>
            <span class="text-purple-700 dark:text-purple-400 font-semibold">4. AES-256 Vault</span>
        </div>
    </div>

    <!-- Ingestion Trigger Banner with Obvious Action Buttons -->
    <div id="upload-banner"
        class="p-6 rounded-xl border-2 border-dashed border-blue-400 dark:border-blue-700/60 bg-blue-50/40 dark:bg-blue-950/20 hover:bg-blue-50/70 dark:hover:bg-blue-950/40 text-center transition-all group">
        <div class="max-w-md mx-auto space-y-3">
            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 mx-auto flex items-center justify-center text-xl group-hover:scale-110 transition-transform shadow-inner">
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">Select or Drag & Drop Documents Here</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Supports PDF, DOCX, PNG, JPG, &amp; <strong>ZIP Archives</strong> (Batch auto-unpack &amp; OCR)</p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                <button type="button" onclick="document.getElementById('file-input').click()"
                    class="px-4 py-2 rounded-lg bg-[#0d2342] hover:bg-[#14335e] text-white font-bold text-xs shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                    <i class="fa-regular fa-folder-open"></i>
                    <span>Browse Document / ZIP Archive</span>
                </button>
                <button type="button" onclick="loadSampleDemoCertificate()"
                    class="px-3.5 py-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-700 font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                    <span>Load Demo Sample</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Side-by-Side 3-Column Work Area matching Figure 4.4 (HIDDEN until file uploaded) -->
    <div id="work-area" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start hidden">

        <!-- Left Panel: REFERENCE RAW EXTRACTION (JSON Strings) matching Figure 4.4 (4 cols) -->
        <div class="lg:col-span-4 bg-slate-100/90 dark:bg-[#0c1830] rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 space-y-2">
            <div class="text-center pb-2 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-tight">
                    REFERENCE RAW EXTRACTION<br>
                    <span class="text-[10px] font-normal text-slate-500 dark:text-slate-400">(JSON Strings)</span>
                </h3>
            </div>

            <div class="flex items-center justify-between text-[11px] font-mono text-slate-600 dark:text-slate-400">
                <span>Raw data Console:</span>
                <span id="ocrConfidenceBadge" class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                    Confidence: 98.4%
                </span>
            </div>

            <!-- Raw JSON String Console Box matching Figure 4.4 -->
            <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-300 dark:border-slate-700 p-3 font-mono text-[11px] text-slate-400 dark:text-slate-300 shadow-inner h-60 overflow-y-auto whitespace-pre-wrap italic" id="rawConsole">Awaiting document ingestion...

Upload a file above to extract metadata.</div>

            <!-- Lock Compliance Badge matching Figure 4.4 -->
            <div class="pt-2 text-center text-[10px] text-slate-600 dark:text-slate-400 flex items-center justify-center gap-1.5 font-medium">
                <i class="fa-solid fa-lock text-blue-600 dark:text-blue-400 text-xs"></i>
                <span>Data Privacy Act Compliance (Human Validation)</span>
            </div>
        </div>

        <!-- Center Panel: SUGGESTED CORRECTION PATH (H-I-T-L Verification) matching Figure 4.4 (5 cols) -->
        <div class="lg:col-span-5 bg-white dark:bg-[#0c1830] rounded-xl border border-slate-300 dark:border-slate-800 p-4 shadow-sm space-y-3" id="hitl-panel">
            <div class="text-center pb-2 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-tight">
                    SUGGESTED CORRECTION PATH<br>
                    <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">(H-I-T-L Verification)</span>
                </h3>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Resolve duplicates. Select and merge correct data.</p>
            </div>

            <form id="hitlForm" class="space-y-2.5 text-xs">
                <input type="hidden" id="temp_filename" value="">
                <input type="hidden" id="original_name" value="">
                <input type="hidden" id="file_type" value="">
                <input type="hidden" id="file_size" value="0">
                <input type="hidden" id="raw_text" value="">
                <input type="hidden" id="file_hash" value="">

                <!-- Duplicate Document Warning Banner -->
                <div id="duplicate-warning-box" class="hidden p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs space-y-2">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 mt-0.5 text-sm flex-shrink-0"></i>
                        <div class="flex-1">
                            <strong class="font-bold text-amber-950 dark:text-amber-100 block" id="duplicate-warning-title">Potential Duplicate Detected</strong>
                            <p class="text-[11px] text-amber-800 dark:text-amber-200 leading-snug mt-0.5" id="duplicate-warning-msg"></p>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 pt-1.5 border-t border-amber-200 dark:border-amber-800/80 cursor-pointer">
                        <input type="checkbox" id="field_force_duplicate" class="rounded text-amber-600 focus:ring-amber-500">
                        <span class="text-[11px] font-semibold text-amber-900 dark:text-amber-200">Override duplicate warning and archive as distinct version</span>
                    </label>
                </div>

                <?php if ($isDean): ?>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Primary Faculty Record (Dean Oversight):</label>
                    <select id="form-faculty-id"
                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-slate-100 font-medium focus:outline-none focus:border-blue-500">
                        <?php foreach ($facultyList as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= $f['id'] == $currentUser['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['full_name']) ?> (<?= htmlspecialchars($f['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Title:</label>
                    <input type="text" id="field_title" value="" placeholder="Extracted document title will appear here"
                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-slate-100 font-medium focus:outline-none focus:border-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Certificate / Reference No.:</label>
                        <input type="text" id="field_cert_number" value="" placeholder="e.g. CERT-2025-001 (Optional)"
                            class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-slate-100 font-medium focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">Date Issued (on Certificate):</label>
                            <span id="date-detect-status" class="text-[9px] font-semibold text-slate-400"></span>
                        </div>
                        <input type="date" id="field_date" value=""
                            class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-slate-100 font-medium focus:outline-none focus:border-blue-500 transition-colors">
                        <div id="date-warning-msg" class="hidden mt-1 p-2 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-[11px] text-amber-900 dark:text-amber-200 flex items-start gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"></i>
                            <span><strong>Issued Date Unverified:</strong> Could not detect authentic date on document. Please verify the physical certificate and specify the date before approving.</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Issuing Authority:</label>
                    <input type="text" id="field_issuer" value="" placeholder="Extracted issuing authority will appear here"
                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-slate-100 font-medium focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Category:</label>
                    <select id="field_category"
                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-slate-100 font-medium focus:outline-none focus:border-blue-500">
                        <option value="Research Publication" selected>Research Publication</option>
                        <option value="Instructional Material">Instructional Material / Course Module</option>
                        <option value="Seminar">Seminar / Webinar</option>
                        <option value="Training">Training / Bootcamp</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Award">Award / Recognition</option>
                        <option value="Academic Milestone">Academic Milestone / Appointment</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Collaborators / Co-Authors (M:N Mapping):</label>
                    <select id="form-coauthors" multiple
                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-slate-100 font-medium focus:outline-none focus:border-blue-500 h-16">
                        <?php foreach ($facultyList as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5">Hold Ctrl (Windows) / Cmd (Mac) to select multiple co-authors</p>
                </div>

                <!-- Action Buttons matching Figure 4.4 -->
                <div class="pt-2 flex items-center gap-2">
                    <button type="button" onclick="submitHitlApproval()"
                        class="flex-1 py-2 px-3 rounded-lg bg-[#1da055] hover:bg-[#188c49] text-white font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Approve & Merge</span>
                    </button>
                    <button type="button" onclick="rejectHitl()"
                        class="py-2 px-4 rounded-lg bg-[#d9383a] hover:bg-[#c02e30] text-white font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Reject</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Panel: AES-256 Engine & Traceable Audit Logs matching Figure 4.4 (3 cols) -->
        <div class="lg:col-span-3 space-y-3 text-xs">

            <!-- AES-256 Box matching Figure 4.4 -->
            <div class="p-3 bg-slate-100 dark:bg-[#0c1830] rounded-xl border border-slate-300 dark:border-slate-800 text-center" id="aes-box">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-[11px] block flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-lock text-amber-500 text-xs"></i>
                    <span>AES-256 Encryption Engine</span>
                </span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 block">Verification Path (on IMAGE 4)</span>
            </div>

            <!-- Traceable Audit Logs Table matching Figure 4.4 -->
            <div class="bg-white dark:bg-[#0c1830] rounded-xl border border-slate-300 dark:border-slate-800 p-3 shadow-sm space-y-2">
                <div class="flex items-center justify-between pb-1 border-b border-slate-200 dark:border-slate-800">
                    <span class="font-bold text-[11px] text-slate-900 dark:text-white">Traceable Audit Logs</span>
                    <i class="fa-solid fa-lock text-blue-600 dark:text-blue-400 text-xs"></i>
                </div>

                <div class="space-y-1.5 text-[10px] font-mono text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between border-b border-slate-100 dark:border-slate-800 pb-1">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Timestamp</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">User ID</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-rose-600 dark:text-rose-400">Rejected Raw Action</span>
                        <span>2026-05-07 09:15:22</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-rose-600 dark:text-rose-400">Rejected User Action</span>
                        <span>2026-05-07 11:30:10</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-emerald-600 dark:text-emerald-400">Validated User Report</span>
                        <span>2026-05-07 11:30:10</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Batch ZIP Review Work Area (Revealed when a ZIP archive is ingested) -->
    <div id="batch-work-area" class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-300 dark:border-slate-800 p-4 sm:p-5 shadow-sm space-y-4 hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-file-zipper text-indigo-600 dark:text-indigo-400"></i>
                    <span>BATCH ARCHIVE PROCESSING (ZIP Ingestion)</span>
                    <span id="batch-id-badge" class="text-[10px] font-mono px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">Batch #0</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Extracted documents from the uploaded archive ready for human verification and AES-256 vault committal.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="openProgressModal()"
                    class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-gauge-high text-blue-600 dark:text-blue-400"></i>
                    <span>Telemetry Modal</span>
                </button>
                <button type="button" id="btn-batch-commit-all" onclick="commitEntireBatch()"
                    class="px-4 py-1.5 rounded-lg bg-[#1da055] hover:bg-[#188c49] text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    <span>Approve &amp; Encrypt All Records</span>
                </button>
            </div>
        </div>

        <!-- Batch Metrics Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 block font-mono">Total Files</span>
                <strong id="batch-total-files" class="text-base font-extrabold text-slate-900 dark:text-white block mt-0.5">0</strong>
                <span class="text-[10px] text-slate-500 dark:text-slate-400">Unpacked from ZIP</span>
            </div>
            <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60">
                <span class="text-[10px] uppercase font-bold text-emerald-600 dark:text-emerald-400 block font-mono">OCR Extracted</span>
                <strong id="batch-extracted-count" class="text-base font-extrabold text-emerald-800 dark:text-emerald-300 block mt-0.5">0</strong>
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400">Metadata ready</span>
            </div>
            <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60">
                <span class="text-[10px] uppercase font-bold text-amber-600 dark:text-amber-400 block font-mono">Duplicate Alerts</span>
                <strong id="batch-duplicates-count" class="text-base font-extrabold text-amber-800 dark:text-amber-300 block mt-0.5">0</strong>
                <span class="text-[10px] text-amber-700 dark:text-amber-300">Flagged for review</span>
            </div>
            <div class="p-3 rounded-lg bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60">
                <span class="text-[10px] uppercase font-bold text-rose-600 dark:text-rose-400 block font-mono">Failed Files</span>
                <strong id="batch-failed-count" class="text-base font-extrabold text-rose-800 dark:text-rose-300 block mt-0.5">0</strong>
                <span class="text-[10px] text-rose-700 dark:text-rose-300">Unreadable or unsupported</span>
            </div>
        </div>

        <!-- Batch Records Table -->
        <div class="border border-slate-200 dark:border-slate-800 rounded-lg overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 text-[11px] font-bold">
                    <tr>
                        <th class="p-2.5">#</th>
                        <th class="p-2.5">File Name</th>
                        <th class="p-2.5">Extracted Title</th>
                        <th class="p-2.5">Cert No.</th>
                        <th class="p-2.5">Date Issued</th>
                        <th class="p-2.5">Category</th>
                        <th class="p-2.5">Duplicate / Status</th>
                        <th class="p-2.5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="batch-table-body" class="divide-y divide-slate-100 dark:divide-slate-800 text-[11px]">
                    <!-- Dynamically populated rows -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Stage Progress & Live Telemetry Modal (Requirements 2, 3, 4) -->
<div id="stage-progress-modal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden animate-in fade-in duration-200">
    <div class="bg-white dark:bg-[#111c36] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-2xl w-full p-5 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 flex items-center justify-center text-base">
                    <i class="fa-solid fa-bars-progress"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Certificate Pipeline Execution Telemetry</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400" id="modal-subtext">Live stage checklist and cryptographic processing monitor</p>
                </div>
            </div>
            <button type="button" onclick="closeProgressModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Overall Progress Section -->
        <div class="space-y-1.5 p-3.5 bg-slate-50 dark:bg-slate-900/70 rounded-xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                    <span>Overall Progress:</span>
                    <span id="modal-status-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-950/50 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">Active</span>
                </span>
                <span id="modal-overall-percent" class="font-mono font-black text-blue-700 dark:text-blue-400 text-sm">0%</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                <div id="modal-progress-bar" class="bg-gradient-to-r from-blue-600 to-indigo-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-0.5">
                <span id="modal-current-task" class="truncate max-w-xs font-mono">Stage: Ready for file ingestion</span>
                <span id="modal-items-counter" class="font-mono font-semibold">0 / 0 items processed</span>
            </div>
        </div>

        <!-- 5-Stage Step-by-Step Checklist (Requirement 2 & 3) -->
        <div class="space-y-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block px-1">Pipeline Checkpoints:</span>

            <!-- Stage 1 -->
            <div id="modal-stage-1" class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1830] flex items-center justify-between text-xs transition-all">
                <div class="flex items-center gap-2.5">
                    <span id="modal-icon-1" class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-[11px] font-bold">1</span>
                    <div>
                        <strong class="text-slate-800 dark:text-slate-200 block text-xs">Stage 1 — Ingestion &amp; Archive Unpack</strong>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">MIME validation, format verification, ZIP unpack</span>
                    </div>
                </div>
                <span id="modal-badge-1" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Pending</span>
            </div>

            <!-- Stage 2 -->
            <div id="modal-stage-2" class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1830] flex items-center justify-between text-xs transition-all">
                <div class="flex items-center gap-2.5">
                    <span id="modal-icon-2" class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-[11px] font-bold">2</span>
                    <div>
                        <strong class="text-slate-800 dark:text-slate-200 block text-xs">Stage 2 — Rasterization &amp; Image Optimization</strong>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Dynamic downscaling under 5MB, targeted PDF rasterization</span>
                    </div>
                </div>
                <span id="modal-badge-2" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Pending</span>
            </div>

            <!-- Stage 3 -->
            <div id="modal-stage-3" class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1830] flex items-center justify-between text-xs transition-all">
                <div class="flex items-center gap-2.5">
                    <span id="modal-icon-3" class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-[11px] font-bold">3</span>
                    <div>
                        <strong class="text-slate-800 dark:text-slate-200 block text-xs">Stage 3 — OCR Metadata &amp; Date Normalization</strong>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Document title, certificate number, ISO YYYY-MM-DD issued date</span>
                    </div>
                </div>
                <span id="modal-badge-3" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Pending</span>
            </div>

            <!-- Stage 4 -->
            <div id="modal-stage-4" class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1830] flex items-center justify-between text-xs transition-all">
                <div class="flex items-center gap-2.5">
                    <span id="modal-icon-4" class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-[11px] font-bold">4</span>
                    <div>
                        <strong class="text-slate-800 dark:text-slate-200 block text-xs">Stage 4 — Duplicate Screening &amp; H-I-T-L Validation</strong>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">SHA-256 fingerprint collision check &amp; Human-in-the-Loop review</span>
                    </div>
                </div>
                <span id="modal-badge-4" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Pending</span>
            </div>

            <!-- Stage 5 -->
            <div id="modal-stage-5" class="p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1830] flex items-center justify-between text-xs transition-all">
                <div class="flex items-center gap-2.5">
                    <span id="modal-icon-5" class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-[11px] font-bold">5</span>
                    <div>
                        <strong class="text-slate-800 dark:text-slate-200 block text-xs">Stage 5 — Cryptographic Archiving (AES-256)</strong>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">AES-256-CBC encryption at rest &amp; audit ledger committal</span>
                    </div>
                </div>
                <span id="modal-badge-5" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">Pending</span>
            </div>
        </div>

        <!-- Telemetry Numbers Grid -->
        <div class="grid grid-cols-4 gap-2 text-center text-xs pt-1">
            <div class="p-2 rounded bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-mono">Total</span>
                <strong id="modal-count-total" class="text-slate-900 dark:text-white font-bold">1</strong>
            </div>
            <div class="p-2 rounded bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60">
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block font-mono">Success</span>
                <strong id="modal-count-success" class="text-emerald-700 dark:text-emerald-300 font-bold">0</strong>
            </div>
            <div class="p-2 rounded bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/60">
                <span class="text-[10px] text-rose-600 dark:text-rose-400 block font-mono">Failed</span>
                <strong id="modal-count-failed" class="text-rose-700 dark:text-rose-300 font-bold">0</strong>
            </div>
            <div class="p-2 rounded bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/60">
                <span class="text-[10px] text-indigo-600 dark:text-indigo-400 block font-mono">Elapsed</span>
                <strong id="modal-count-elapsed" class="text-indigo-700 dark:text-indigo-300 font-bold">0.0s</strong>
            </div>
        </div>

        <!-- Collapsible Error & Warning Inspector (Requirement 3) -->
        <div id="modal-error-box" class="hidden p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs space-y-1.5">
            <div class="flex items-center justify-between text-rose-800 dark:text-rose-300 font-bold">
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-circle-exclamation"></i> Telemetry Alerts &amp; Errors:</span>
                <span id="modal-error-badge" class="text-[10px] px-1.5 py-0.2 rounded bg-rose-200 dark:bg-rose-900/60 text-rose-900 dark:text-rose-200">0 errors</span>
            </div>
            <div id="modal-error-list" class="max-h-28 overflow-y-auto font-mono text-[11px] text-rose-700 dark:text-rose-300 space-y-1">
            </div>
        </div>

        <!-- Modal Actions -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-slate-800 text-xs">
            <button type="button" onclick="toggleErrorInspector()" id="btn-toggle-errors"
                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold flex items-center gap-1">
                <i class="fa-solid fa-list-check"></i>
                <span id="lbl-toggle-errors">Inspect Error Log (0)</span>
            </button>
            <button type="button" onclick="closeProgressModal()"
                class="px-4 py-2 rounded-lg bg-[#0d2342] hover:bg-[#14335e] text-white font-bold transition-all shadow-sm cursor-pointer">
                Close Telemetry
            </button>
        </div>
    </div>
</div>

<!-- Script handling interactive stages, upload, live telemetry modal, batch archives, and H-I-T-L verification -->
<script>
    // Stage Explanations for Interactive Stepper
    const stageDetails = {
        1: {
            title: "Stage 1: Dynamic Optimization & Ingestion Gate",
            desc: "The Python engine inspects incoming assets (JPG, PNG, PDF, DOCX, ZIP). If an image exceeds the 5MB API payload limit, it dynamically downscales and recompresses the file. ZIP archives are securely unpacked with path traversal guards.",
            icon: "fa-compress"
        },
        2: {
            title: "Stage 2: Cloud Handshake & Quota Tracking",
            desc: "An outbound HTTPS encrypted payload is transmitted to the cloud OCR engine (OCR.space API). Response headers are captured in real-time to synchronize the Dean's 25,000 monthly quota monitor while ensuring sensitive credentials remain safe.",
            icon: "fa-cloud-arrow-up"
        },
        3: {
            title: "Stage 3: Human-in-the-Loop (H-I-T-L) Validation",
            desc: "Extracted strings are presented in the Suggested Correction Path form. The faculty member validates the Title, Date Issued (normalized to ISO YYYY-MM-DD), Cert Number, Authority, and Category before committing data.",
            icon: "fa-user-check"
        },
        4: {
            title: "Stage 4: Cryptographic Archiving (AES-256)",
            desc: "Upon approval, the file is encoded using AES-256-CBC with a randomized 16-byte initialization vector (IV) and SHA-256 tamper-proof fingerprinting before storage in the vault.",
            icon: "fa-shield-halved"
        }
    };

    // Telemetry & Modal State Tracker
    const telemetryState = {
        currentStage: 1,
        totalItems: 1,
        processedItems: 0,
        successItems: 0,
        failedItems: 0,
        startTime: null,
        timerInterval: null,
        errors: [],
        batchItems: []
    };

    function handleStageClick(stageNum) {
        showStageInfo(stageNum);
    }

    function showStageInfo(stageNum) {
        const box = document.getElementById('stage-info-box');
        const title = document.getElementById('stage-info-title');
        const desc = document.getElementById('stage-info-desc');
        const icon = document.getElementById('stage-info-icon');

        const info = stageDetails[stageNum];
        title.textContent = info.title;
        desc.textContent = info.desc;
        icon.className = `fa-solid ${info.icon} text-blue-600 text-base`;
        box.classList.remove('hidden');
    }

    function setDateDetectionStatus(dateVal) {
        const statusEl = document.getElementById('date-detect-status');
        const warnBox = document.getElementById('date-warning-msg');
        const dateInput = document.getElementById('field_date');
        if (!statusEl || !warnBox || !dateInput) return;

        if (dateVal && dateVal.trim() !== '') {
            statusEl.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400 font-bold">✓ OCR Extracted</span>';
            warnBox.classList.add('hidden');
            dateInput.classList.remove('border-amber-500', 'bg-amber-50/20');
        } else {
            statusEl.innerHTML = '<span class="text-amber-600 dark:text-amber-400 font-bold">⚠️ Unverified</span>';
            warnBox.classList.remove('hidden');
            dateInput.classList.add('border-amber-500', 'bg-amber-50/20');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dateIn = document.getElementById('field_date');
        if (dateIn) {
            dateIn.addEventListener('input', function() {
                setDateDetectionStatus(this.value);
            });
        }
    });

    function setActiveStep(activeNum) {
        telemetryState.currentStage = activeNum;
        for (let i = 1; i <= 4; i++) {
            const btn = document.getElementById(`step-${i}`);
            if (!btn) continue;
            const subtext = btn.querySelector('span');
            const title = btn.querySelector('strong');

            if (i === activeNum) {
                btn.className = "p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-500 text-emerald-800 dark:text-emerald-200 font-bold transition-all text-left flex items-center justify-between shadow-sm cursor-pointer";
                if (subtext) {
                    subtext.className = "text-[10px] text-emerald-600 dark:text-emerald-400 block font-mono";
                    subtext.textContent = `Stage ${i} (Active)`;
                }
                if (title) title.className = "text-xs text-emerald-900 dark:text-emerald-100";
            } else if (i < activeNum) {
                btn.className = "p-2.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-300 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 font-medium transition-all text-left flex items-center justify-between shadow-sm cursor-pointer";
                if (subtext) {
                    subtext.className = "text-[10px] text-emerald-600 dark:text-emerald-400 block font-mono";
                    subtext.textContent = `Stage ${i} (Done)`;
                }
                if (title) title.className = "text-xs text-emerald-800 dark:text-emerald-200";
            } else {
                btn.className = "p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900/40 hover:bg-slate-100 dark:hover:bg-slate-800/50 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-medium transition-all text-left flex items-center justify-between group shadow-sm cursor-pointer";
                if (subtext) {
                    subtext.className = "text-[10px] text-slate-400 dark:text-slate-500 block font-mono";
                    subtext.textContent = `Stage ${i}`;
                }
                if (title) title.className = "text-xs text-slate-700 dark:text-slate-300";
            }
        }
        updateModalStageBadges(activeNum);
    }

    // Modal Opening & Closing
    function openProgressModal() {
        document.getElementById('stage-progress-modal').classList.remove('hidden');
    }

    function closeProgressModal() {
        document.getElementById('stage-progress-modal').classList.add('hidden');
    }

    function startTelemetryTimer() {
        telemetryState.startTime = Date.now();
        if (telemetryState.timerInterval) clearInterval(telemetryState.timerInterval);
        telemetryState.timerInterval = setInterval(() => {
            const elapsed = ((Date.now() - telemetryState.startTime) / 1000).toFixed(1);
            const el = document.getElementById('modal-count-elapsed');
            if (el) el.textContent = `${elapsed}s`;
        }, 200);
    }

    function stopTelemetryTimer() {
        if (telemetryState.timerInterval) {
            clearInterval(telemetryState.timerInterval);
            telemetryState.timerInterval = null;
        }
    }

    function updateModalProgress(percent, taskName) {
        const pBar = document.getElementById('modal-progress-bar');
        const pTxt = document.getElementById('modal-overall-percent');
        const pTask = document.getElementById('modal-current-task');
        const pCounter = document.getElementById('modal-items-counter');

        if (pBar) pBar.style.width = `${Math.min(100, Math.max(0, percent))}%`;
        if (pTxt) pTxt.textContent = `${Math.round(percent)}%`;
        if (pTask) pTask.textContent = taskName;
        if (pCounter) pCounter.textContent = `${telemetryState.processedItems} / ${telemetryState.totalItems} items processed`;

        document.getElementById('modal-count-total').textContent = telemetryState.totalItems;
        document.getElementById('modal-count-success').textContent = telemetryState.successItems;
        document.getElementById('modal-count-failed').textContent = telemetryState.failedItems;
    }

    function updateModalStageBadges(activeStage) {
        for (let i = 1; i <= 5; i++) {
            const row = document.getElementById(`modal-stage-${i}`);
            const badge = document.getElementById(`modal-badge-${i}`);
            const icon = document.getElementById(`modal-icon-${i}`);
            if (!row || !badge || !icon) continue;

            if (i < activeStage) {
                row.className = "p-2.5 rounded-lg border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50/40 dark:bg-emerald-950/30 flex items-center justify-between text-xs transition-all";
                badge.className = "text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300";
                badge.textContent = "Completed";
                icon.className = "w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold";
                icon.innerHTML = '<i class="fa-solid fa-check"></i>';
            } else if (i === activeStage) {
                row.className = "p-2.5 rounded-lg border-2 border-blue-500 bg-blue-50 dark:bg-blue-950/40 flex items-center justify-between text-xs transition-all shadow-sm";
                badge.className = "text-[10px] font-bold px-2 py-0.5 rounded bg-blue-600 text-white animate-pulse";
                badge.textContent = "In Progress";
                icon.className = "w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold";
                icon.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i>';
            } else {
                row.className = "p-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0c1830] flex items-center justify-between text-xs transition-all";
                badge.className = "text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400";
                badge.textContent = "Pending";
                icon.className = "w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center text-[11px] font-bold";
                icon.textContent = i;
            }
        }
    }

    function addTelemetryError(msg) {
        telemetryState.errors.push(msg);
        telemetryState.failedItems++;
        const badge = document.getElementById('modal-error-badge');
        const lbl = document.getElementById('lbl-toggle-errors');
        const list = document.getElementById('modal-error-list');

        if (badge) badge.textContent = `${telemetryState.errors.length} alert(s)`;
        if (lbl) lbl.textContent = `Inspect Alerts & Errors (${telemetryState.errors.length})`;
        if (list) {
            const p = document.createElement('div');
            p.className = "p-1.5 rounded bg-rose-100/60 border border-rose-200 text-rose-900";
            p.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-600 mr-1"></i> ${escapeHtml(msg)}`;
            list.appendChild(p);
        }
        document.getElementById('modal-error-box').classList.remove('hidden');
    }

    function toggleErrorInspector() {
        const box = document.getElementById('modal-error-box');
        box.classList.toggle('hidden');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Demo Sample Loader
    function loadSampleDemoCertificate() {
        const banner = document.getElementById('upload-banner');
        telemetryState.totalItems = 1;
        telemetryState.processedItems = 0;
        telemetryState.successItems = 0;
        telemetryState.failedItems = 0;
        telemetryState.errors = [];
        document.getElementById('modal-error-list').innerHTML = '';
        document.getElementById('modal-error-box').classList.add('hidden');
        startTelemetryTimer();

        setActiveStep(1);
        updateModalProgress(20, 'Stage 1: Dynamic Optimization & Rasterization...');

        banner.innerHTML = `
            <div class="flex items-center justify-center gap-2 text-xs font-semibold text-blue-600 py-3">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Stage 1: Dynamic Optimization &amp; Targeted Rasterization for "Certificate_Recognition_2026.pdf"...</span>
            </div>
        `;

        setTimeout(() => {
            setActiveStep(2);
            updateModalProgress(50, 'Stage 2: OCR Cloud Engine Transmission & Text Extraction...');
            banner.innerHTML = `
                <div class="flex items-center justify-center gap-2 text-xs font-semibold text-indigo-600 py-3">
                    <i class="fa-solid fa-cloud-arrow-up animate-pulse text-base"></i>
                    <span>Stage 2: Outbound HTTPS Cloud Handshake with OCR Engine...</span>
                </div>
            `;
        }, 600);

        setTimeout(() => {
            setActiveStep(3);
            telemetryState.processedItems = 1;
            telemetryState.successItems = 1;
            stopTelemetryTimer();
            updateModalProgress(75, 'Stage 3: Metadata Ready for Human-in-the-Loop Validation');

            banner.innerHTML = `
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-center">
                    <div class="text-xs font-bold text-emerald-800 dark:text-emerald-200 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                        <span>Stage 3 Active: Metadata Extracted from "Certificate_Recognition_2026.pdf"!</span>
                    </div>
                    <p class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-1">Review the Suggested Correction Path below, adjust fields if necessary, then click <strong>Approve &amp; Merge</strong>.</p>
                </div>
            `;

            // Reveal work area
            document.getElementById('batch-work-area').classList.add('hidden');
            document.getElementById('work-area').classList.remove('hidden');

            const consoleObj = {
                "Title": "Certificate of Recognition - National Research Conference",
                "CertNo": "CERT-2026-CHED-04",
                "Date": "2026-05-07",
                "Issuer": "Commission on Higher Education (CHED)",
                "Type": "Research Publication"
            };
            document.getElementById('rawConsole').textContent = JSON.stringify(consoleObj, null, 2);

            // Populate form fields
            document.getElementById('temp_filename').value = 'sample_demo_cert.pdf';
            document.getElementById('original_name').value = 'Certificate_Recognition_2026.pdf';
            document.getElementById('file_type').value = 'pdf';
            document.getElementById('file_size').value = '245760';
            document.getElementById('raw_text').value = 'This is to certify that faculty has actively participated in the National Research Conference held at CICI.';

            document.getElementById('field_title').value = consoleObj.Title;
            document.getElementById('field_cert_number').value = consoleObj.CertNo;
            document.getElementById('field_date').value = consoleObj.Date;
            document.getElementById('field_issuer').value = consoleObj.Issuer;
            document.getElementById('field_category').value = consoleObj.Type;
            setDateDetectionStatus(consoleObj.Date);

            document.getElementById('duplicate-warning-box').classList.add('hidden');
            document.getElementById('hitl-panel').scrollIntoView({ behavior: 'smooth' });
        }, 1200);
    }

    // Live Upload & Batch Processor
    document.getElementById('file-input').addEventListener('change', function(e) {
        const files = e.target.files;
        if (!files || files.length === 0) return;

        const banner = document.getElementById('upload-banner');
        const formData = new FormData();
        const isMultiple = files.length > 1;

        telemetryState.totalItems = files.length;
        telemetryState.processedItems = 0;
        telemetryState.successItems = 0;
        telemetryState.failedItems = 0;
        telemetryState.errors = [];
        document.getElementById('modal-error-list').innerHTML = '';
        document.getElementById('modal-error-box').classList.add('hidden');
        startTelemetryTimer();

        setActiveStep(1);
        updateModalProgress(15, `Stage 1: Pre-flight inspection & upload (${files.length} document${files.length > 1 ? 's' : ''})...`);

        if (isMultiple) {
            banner.innerHTML = `
                <div class="flex items-center justify-center gap-2 text-xs font-semibold text-blue-600 py-3">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Stage 1: Compiling ${files.length} documents into unified batch pipeline...</span>
                </div>
            `;
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
        } else {
            const file = files[0];
            const isZip = file.name.toLowerCase().endsWith('.zip');
            banner.innerHTML = `
                <div class="flex items-center justify-center gap-2 text-xs font-semibold text-blue-600 py-3">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Stage 1: ${isZip ? 'Unpacking ZIP archive & pre-screening certificates' : 'Optimizing & rasterizing ' + file.name}...</span>
                </div>
            `;
            formData.append('file', file);
        }

        setTimeout(() => {
            setActiveStep(2);
            updateModalProgress(45, 'Stage 2: Outbound HTTPS Handshake with OCR Engine...');
            banner.innerHTML = `
                <div class="flex items-center justify-center gap-2 text-xs font-semibold text-indigo-600 py-3">
                    <i class="fa-solid fa-cloud-arrow-up animate-pulse text-base"></i>
                    <span>Stage 2: Outbound HTTPS Cloud Handshake with OCR Engine...</span>
                </div>
            `;
        }, 700);

        fetch('api/upload_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            stopTelemetryTimer();

            if (data.status !== 'success' && data.status !== 'batch_success') {
                throw new Error(data.message || 'Pipeline ingestion failed');
            }

            // Check if returned as Batch Archive (ZIP)
            if (data.is_batch) {
                handleBatchUploadResponse(data);
                return;
            }

            // Single Document Workflow
            setActiveStep(3);
            telemetryState.processedItems = 1;
            telemetryState.successItems = 1;
            updateModalProgress(75, 'Stage 3: Metadata extracted, awaiting H-I-T-L approval');

            banner.innerHTML = `
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-center">
                    <div class="text-xs font-bold text-emerald-800 dark:text-emerald-200 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400"></i>
                        <span>Stage 3 Active: Extraction Complete for "${data.original_name}"!</span>
                    </div>
                    <p class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-1">Review the Suggested Correction Path below, adjust fields if necessary, then click <strong>Approve &amp; Merge</strong> to encrypt with AES-256.</p>
                </div>
            `;

            document.getElementById('batch-work-area').classList.add('hidden');
            document.getElementById('work-area').classList.remove('hidden');

            const consoleObj = {
                "Title": data.metadata.title,
                "Date": data.metadata.date_issued,
                "Issuer": data.metadata.issuer,
                "Type": data.metadata.category
            };
            document.getElementById('rawConsole').textContent = JSON.stringify(consoleObj, null, 2);

            // Populate form
            document.getElementById('temp_filename').value = data.temp_filename;
            document.getElementById('original_name').value = data.original_name;
            document.getElementById('file_type').value = data.file_type;
            document.getElementById('file_size').value = data.file_size;
            document.getElementById('raw_text').value = data.raw_text;
            document.getElementById('file_hash').value = data.file_hash || '';

            document.getElementById('field_title').value = data.metadata.title;
            document.getElementById('field_date').value = data.metadata.date_issued || '';
            document.getElementById('field_issuer').value = data.metadata.issuer;
            document.getElementById('field_category').value = data.metadata.category;
            document.getElementById('field_cert_number').value = data.metadata.cert_number || '';
            setDateDetectionStatus(data.metadata.date_issued);

            // Handle duplicate warning
            const dupBox = document.getElementById('duplicate-warning-box');
            const forceCheckbox = document.getElementById('field_force_duplicate');
            if (forceCheckbox) forceCheckbox.checked = false;

            if (data.duplicate_warning) {
                dupBox.classList.remove('hidden');
                document.getElementById('duplicate-warning-title').textContent = data.duplicate_warning.type === 'exact_file'
                    ? 'Identical Document Found in Vault'
                    : 'Matching Title & Issued Date Found';
                document.getElementById('duplicate-warning-msg').textContent = data.duplicate_warning.message;
                addTelemetryError(data.duplicate_warning.message);
            } else {
                dupBox.classList.add('hidden');
            }

            document.getElementById('hitl-panel').scrollIntoView({ behavior: 'smooth' });
        })
        .catch(err => {
            stopTelemetryTimer();
            banner.innerHTML = `<span class="text-xs font-bold text-rose-600 dark:text-rose-400">Ingestion error: ${escapeHtml(err.message)}</span>`;
            setActiveStep(1);
            addTelemetryError(err.message);
            openProgressModal();
        });
    });

    // Batch Review UI Handler
    function handleBatchUploadResponse(data) {
        telemetryState.batchItems = data.items || [];
        telemetryState.totalItems = data.total || telemetryState.batchItems.length;
        telemetryState.processedItems = data.successful || 0;
        telemetryState.successItems = data.successful || 0;
        telemetryState.failedItems = data.failed || 0;

        setActiveStep(3);
        updateModalProgress(80, `Batch unzipped: ${telemetryState.successItems} files ready for H-I-T-L approval`);

        const banner = document.getElementById('upload-banner');
        banner.innerHTML = `
            <div class="p-3 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-xl text-center">
                <div class="text-xs font-bold text-indigo-900 dark:text-indigo-200 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-file-zipper text-indigo-600 dark:text-indigo-400"></i>
                    <span>Batch Archive Ingested: ${data.total} Documents Unpacked &amp; Extracted</span>
                </div>
                <p class="text-[11px] text-indigo-700 dark:text-indigo-300 mt-1">Review the batch items below, inspect any duplicate flags, then click <strong>Approve &amp; Encrypt All Records</strong>.</p>
            </div>
        `;

        document.getElementById('work-area').classList.add('hidden');
        const batchArea = document.getElementById('batch-work-area');
        batchArea.classList.remove('hidden');

        document.getElementById('batch-id-badge').textContent = `Batch #${data.batch_id || 1}`;
        document.getElementById('batch-total-files').textContent = data.total;
        document.getElementById('batch-extracted-count').textContent = data.successful;

        let dupCount = 0;
        const tbody = document.getElementById('batch-table-body');
        tbody.innerHTML = '';

        telemetryState.batchItems.forEach((item, idx) => {
            if (item.duplicate_warning) dupCount++;

            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors";
            tr.innerHTML = `
                <td class="p-2.5 font-mono text-slate-400 dark:text-slate-500 font-bold">${idx + 1}</td>
                <td class="p-2.5 font-medium text-slate-800 dark:text-slate-200">
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid ${item.file_type === 'pdf' ? 'fa-file-pdf text-rose-500' : 'fa-file-image text-blue-500'}"></i>
                        <span class="truncate max-w-[140px]" title="${escapeHtml(item.original_name)}">${escapeHtml(item.original_name)}</span>
                    </div>
                </td>
                <td class="p-2.5 font-bold text-slate-900 dark:text-white max-w-xs truncate" title="${escapeHtml(item.metadata?.title || 'Untitled')}">
                    ${escapeHtml(item.metadata?.title || 'Untitled Document')}
                </td>
                <td class="p-2.5 font-mono text-slate-600 dark:text-slate-400">${escapeHtml(item.metadata?.cert_number || '—')}</td>
                <td class="p-2.5 font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">${escapeHtml(item.metadata?.date_issued || '—')}</td>
                <td class="p-2.5">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        ${escapeHtml(item.metadata?.category || 'Seminar')}
                    </span>
                </td>
                <td class="p-2.5">
                    ${item.duplicate_warning ? `
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-800 flex items-center gap-1 w-fit" title="${escapeHtml(item.duplicate_warning.message)}">
                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Duplicate
                        </span>
                    ` : `
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-800 flex items-center gap-1 w-fit">
                            <i class="fa-solid fa-check text-[9px]"></i> Clean
                        </span>
                    `}
                </td>
                <td class="p-2.5 text-center">
                    <button type="button" onclick="loadBatchItemIntoHitl(${idx})"
                        class="px-2.5 py-1 rounded bg-[#0d2342] hover:bg-[#14335e] text-white text-[10px] font-bold shadow-sm transition-all cursor-pointer">
                        Review
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('batch-duplicates-count').textContent = dupCount;
        document.getElementById('batch-failed-count').textContent = data.failed || 0;

        batchArea.scrollIntoView({ behavior: 'smooth' });
    }

    function loadBatchItemIntoHitl(idx) {
        const item = telemetryState.batchItems[idx];
        if (!item) return;

        document.getElementById('temp_filename').value = item.temp_filename;
        document.getElementById('original_name').value = item.original_name;
        document.getElementById('file_type').value = item.file_type;
        document.getElementById('file_size').value = item.file_size;
        document.getElementById('raw_text').value = item.raw_text || '';
        document.getElementById('file_hash').value = item.file_hash || '';

        document.getElementById('field_title').value = item.metadata?.title || '';
        document.getElementById('field_cert_number').value = item.metadata?.cert_number || '';
        document.getElementById('field_date').value = item.metadata?.date_issued || '';
        document.getElementById('field_issuer').value = item.metadata?.issuer || '';
        document.getElementById('field_category').value = item.metadata?.category || 'Seminar';

        const dupBox = document.getElementById('duplicate-warning-box');
        if (item.duplicate_warning) {
            dupBox.classList.remove('hidden');
            document.getElementById('duplicate-warning-title').textContent = 'Batch Duplicate Alert';
            document.getElementById('duplicate-warning-msg').textContent = item.duplicate_warning.message;
        } else {
            dupBox.classList.add('hidden');
        }

        document.getElementById('work-area').classList.remove('hidden');
        document.getElementById('hitl-panel').scrollIntoView({ behavior: 'smooth' });
    }

    // Sequentially Commit All Batch Records to AES-256 Vault
    async function commitEntireBatch() {
        if (!telemetryState.batchItems || telemetryState.batchItems.length === 0) return;

        const btn = document.getElementById('btn-batch-commit-all');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Encrypting Batch...';

        openProgressModal();
        setActiveStep(4);
        startTelemetryTimer();

        let committed = 0;
        let errors = 0;

        for (let i = 0; i < telemetryState.batchItems.length; i++) {
            const item = telemetryState.batchItems[i];
            const percent = 80 + Math.round(((i + 1) / telemetryState.batchItems.length) * 20);
            updateModalProgress(percent, `Stage 4: AES-256-CBC Encrypting "${item.original_name}" (${i+1}/${telemetryState.batchItems.length})...`);

            const payload = {
                temp_filename: item.temp_filename,
                original_name: item.original_name,
                file_type: item.file_type,
                file_size: item.file_size,
                raw_text: item.raw_text || '',
                title: item.metadata?.title || item.original_name,
                cert_number: item.metadata?.cert_number || '',
                date_issued: item.metadata?.date_issued || '',
                issuer: item.metadata?.issuer || 'Not Specified',
                category: item.metadata?.category || 'Seminar',
                force_duplicate: 1, // In batch committal, allow existing items to be archived as versions
                csrf_token: window.CSRF_TOKEN || ''
            };

            const facSelect = document.getElementById('form-faculty-id');
            if (facSelect) payload.faculty_id = parseInt(facSelect.value);

            try {
                const res = await fetch('api/commit_record.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.CSRF_TOKEN || ''
                    },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if (result.status === 'success') {
                    committed++;
                } else {
                    errors++;
                    addTelemetryError(`File "${item.original_name}": ${result.message}`);
                }
            } catch (err) {
                errors++;
                addTelemetryError(`Network error on "${item.original_name}": ${err.message}`);
            }
        }

        stopTelemetryTimer();
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Batch Vaulted';

        updateModalProgress(100, `Batch Archiving Complete: ${committed} records encrypted`);
        updateModalStageBadges(5);

        showSystemAlert({
            title: 'Batch Cryptographic Archive Complete',
            message: `Successfully encrypted and vaulted ${committed} certificate records using AES-256-CBC.${errors > 0 ? ` (${errors} alerts logged)` : ''}`,
            type: errors > 0 ? 'warning' : 'success',
            buttonText: 'View Institutional Vault',
            onOk: function() {
                window.location.href = 'vault.php';
            }
        });
    }

    // Submit Single Document Approval
    function submitHitlApproval() {
        const title = document.getElementById('field_title').value.trim();
        const issuer = document.getElementById('field_issuer').value.trim();
        const certNumber = document.getElementById('field_cert_number').value.trim();
        const dateIssued = document.getElementById('field_date').value;
        const forceDup = document.getElementById('field_force_duplicate')?.checked ? 1 : 0;

        if (!title || !issuer) {
            showSystemAlert({
                title: 'Validation Incomplete',
                message: 'Both the Document Title and Issuing Authority are mandatory before cryptographic committal.',
                type: 'warning',
                buttonText: 'Complete Fields'
            });
            return;
        }

        if (!dateIssued) {
            showSystemAlert({
                title: 'Mandatory Issued Date Required',
                message: 'The authentic Issued Date stated on the certificate is required for accreditation ledger integrity. Please verify the physical document and provide the date.',
                type: 'warning',
                buttonText: 'Provide Date'
            });
            document.getElementById('field_date').focus();
            return;
        }

        const tempFile = document.getElementById('temp_filename').value;
        const payload = {
            temp_filename: tempFile || 'sample.docx',
            original_name: document.getElementById('original_name').value || 'Certificate_Sample.docx',
            file_type: document.getElementById('file_type').value || 'docx',
            file_size: parseInt(document.getElementById('file_size').value) || 1024,
            raw_text: document.getElementById('raw_text').value || '',
            file_hash: document.getElementById('file_hash').value || '',
            title: title,
            cert_number: certNumber,
            date_issued: dateIssued,
            issuer: issuer,
            category: document.getElementById('field_category').value,
            force_duplicate: forceDup,
            csrf_token: window.CSRF_TOKEN || ''
        };

        const facSelect = document.getElementById('form-faculty-id');
        if (facSelect) {
            payload.faculty_id = parseInt(facSelect.value);
        }

        const coSelect = document.getElementById('form-coauthors');
        if (coSelect) {
            payload.co_authors = Array.from(coSelect.selectedOptions).map(o => parseInt(o.value));
        }

        // Transition to Stage 4: Cryptographic Encryption
        setActiveStep(4);
        openProgressModal();
        startTelemetryTimer();
        updateModalProgress(85, `Stage 4: AES-256 Encrypting & Committing "${payload.title}"...`);

        fetch('api/commit_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.CSRF_TOKEN || ''
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            return { httpStatus: res.status, data: data };
        })
        .then(wrap => {
            stopTelemetryTimer();
            const res = wrap.data;

            if (wrap.httpStatus === 409 || res.status === 'duplicate_detected') {
                // Duplicate intercepted by server
                closeProgressModal();
                const dupBox = document.getElementById('duplicate-warning-box');
                dupBox.classList.remove('hidden');
                document.getElementById('duplicate-warning-title').textContent = 'Duplicate Document Policy Intercept';
                document.getElementById('duplicate-warning-msg').textContent = res.message;
                addTelemetryError(res.message);
                setActiveStep(3);

                showSystemAlert({
                    title: 'Duplicate Document Intercepted',
                    message: res.message + '\n\nIf you still wish to archive this document as an alternate version, check the override checkbox in the form and submit again.',
                    type: 'warning',
                    buttonText: 'Review Form'
                });
                return;
            }

            if (res.status === 'success') {
                closeProgressModal();
                updateModalProgress(100, `Stage 5: Archival complete & audit logged`);
                updateModalStageBadges(5);

                showSystemAlert({
                    title: 'Record Approved & Archived!',
                    message: 'The document has been securely encrypted with AES-256-CBC and committed to the repository. The audit ledger has been updated.',
                    type: 'success',
                    buttonText: 'View in My Vault',
                    onOk: function() {
                        window.location.href = 'vault.php';
                    }
                });
            } else {
                showSystemAlert({
                    title: 'Committal Error',
                    message: res.message || 'Failed to archive record.',
                    type: 'error',
                    buttonText: 'Close'
                });
                addTelemetryError(res.message);
                setActiveStep(3);
            }
        })
        .catch(err => {
            stopTelemetryTimer();
            showSystemAlert({
                title: 'Network Communication Error',
                message: err.message,
                type: 'error',
                buttonText: 'Understood'
            });
            addTelemetryError(err.message);
            setActiveStep(3);
        });
    }

    function rejectHitl() {
        showSystemConfirm({
            title: 'Discard Extracted Metadata?',
            message: 'Are you sure you wish to discard this ingestion? The temporary snapshot will be purged from the conversion cache.',
            type: 'warning',
            confirmText: 'Yes, Discard',
            cancelText: 'Keep Editing',
            onConfirm: function() {
                window.location.reload();
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/layout_end.php'; ?>
