<?php
$isDean = $isDean ?? false;
$records = $records ?? $certificates ?? [];
$certificates = $certificates ?? $records;
$profile = $profile ?? [];
$category = $category ?? 'All';
$search = $search ?? '';
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';
$issuedYear = $issuedYear ?? '';
$viewMode = $viewMode ?? 'table';
$availableYears = $availableYears ?? [];
$facultyMembers = $facultyMembers ?? [];
$allCategories = $allCategories ?? ['All', 'Modules & IMs', 'Research', 'Seminars', 'Trainings', 'Workshops', 'Awards', 'Milestones'];
$targetFacultyId = $targetFacultyId ?? null;
$activeNav = $activeNav ?? 'certificates';
$pageTitle = $pageTitle ?? ($isDean ? 'Institutional Department Vault' : 'Faculty Personal Digital Vault');
require_once __DIR__ . '/../layouts/layout_start.php';
?>

<!-- Title Banner -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
    <div>
        <h1 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">
            <?= $isDean ? 'Institutional Repository & Faculty Vault' : 'Faculty Personal Digital Vault' ?>
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Cryptographically Isolated Academic Assets &bull; AES-256-CBC Encrypted
        </p>
    </div>
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-600 dark:text-slate-300 bg-white dark:bg-[#111c36] border border-slate-200 dark:border-slate-800 px-3 py-1 rounded-full shadow-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            Profile: <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong> (<?= htmlspecialchars($profile['academic_rank'] ?? ($isDean ? 'CICI Dean' : 'CICI Faculty')) ?>)
        </span>
    </div>
</div>

<!-- Header Profile Summary Box -->
<div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-xl <?= $isDean ? 'bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300' : 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300' ?> flex items-center justify-center text-xl flex-shrink-0 shadow-inner">
            <i class="<?= $isDean ? 'fa-solid fa-building-columns' : 'fa-solid fa-graduation-cap' ?>"></i>
        </div>
        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <span><?= htmlspecialchars($currentUser['full_name']) ?></span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    AES-256 Vault Active
                </span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                <?= htmlspecialchars($profile['academic_rank'] ?? ($isDean ? 'Department Dean' : 'Faculty')) ?> &bull; Employee ID: <?= htmlspecialchars($profile['employee_id'] ?? 'ISUFST-1001') ?> &bull; CICI Department
            </p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2.5 text-xs">
        <span class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-mono text-[11px]">
            Total Assets: <strong class="text-slate-900 dark:text-white"><?= count($records) ?></strong>
        </span>

        <!-- View Mode Switcher -->
        <div class="inline-flex rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111c36] p-0.5 shadow-sm">
            <a href="vault.php?view=table&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&issued_year=<?= urlencode($issuedYear) ?><?= $isDean && $targetFacultyId ? '&faculty_id=' . $targetFacultyId : '' ?>"
               class="px-2.5 py-1 rounded text-xs font-bold transition-all flex items-center gap-1.5 <?= $viewMode === 'table' ? 'bg-[#0d2342] text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' ?>"
               title="Table List View">
                <i class="fa-solid fa-table-list"></i>
                <span class="hidden sm:inline">Table View</span>
            </a>
            <a href="vault.php?view=grid&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&issued_year=<?= urlencode($issuedYear) ?><?= $isDean && $targetFacultyId ? '&faculty_id=' . $targetFacultyId : '' ?>"
               class="px-2.5 py-1 rounded text-xs font-bold transition-all flex items-center gap-1.5 <?= $viewMode === 'grid' ? 'bg-[#0d2342] text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' ?>"
               title="Card Grid View">
                <i class="fa-solid fa-grip"></i>
                <span class="hidden sm:inline">Grid View</span>
            </a>
        </div>

        <!-- Direct Link to Accreditation Report & PDF -->
        <a href="export.php?category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&issued_year=<?= urlencode($issuedYear) ?><?= $isDean && $targetFacultyId ? '&faculty_id=' . $targetFacultyId : '' ?>"
           class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-file-pdf text-rose-600 dark:text-rose-400"></i>
            <span>Official Report &amp; PDF</span>
        </a>

        <a href="upload.php" class="px-3.5 py-1.5 bg-[#1da055] hover:bg-[#188c49] text-white rounded-lg font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
            <span>Ingest Document</span>
        </a>
    </div>
</div>

<!-- Category Tabs Filter -->
<div class="flex flex-wrap items-center justify-between gap-2 bg-white dark:bg-[#111c36] p-2 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm text-xs transition-colors">
    <div class="flex flex-wrap gap-1.5">
        <?php
        $tabs = [
            'All' => 'All Categories',
            'Instructional Material' => 'Modules & IMs',
            'Research Publication' => 'Research',
            'Seminar' => 'Seminars',
            'Training' => 'Trainings',
            'Workshop' => 'Workshops',
            'Award' => 'Awards',
            'Academic Milestone' => 'Milestones'
        ];
        foreach ($tabs as $key => $label):
            $isActive = ($category === $key);
            $tabUrl = 'vault.php?view=' . urlencode($viewMode) . '&category=' . urlencode($key);
            if ($isDean && $targetFacultyId) $tabUrl .= '&faculty_id=' . $targetFacultyId;
            if ($search) $tabUrl .= '&search=' . urlencode($search);
            if ($dateFrom) $tabUrl .= '&date_from=' . urlencode($dateFrom);
            if ($dateTo) $tabUrl .= '&date_to=' . urlencode($dateTo);
            if ($issuedYear) $tabUrl .= '&issued_year=' . urlencode($issuedYear);
        ?>
            <a href="<?= $tabUrl ?>"
               class="px-3 py-1.5 rounded-lg text-xs transition-all <?= $isActive ? 'bg-[#0d2342] dark:bg-blue-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Date Issued & Multi-Field Filter Bar (Requirements 7 & 8) -->
<div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 sm:p-4 shadow-sm space-y-3 transition-colors">
    <form method="GET" action="vault.php" class="space-y-3">
        <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
        <?php if ($category !== 'All'): ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2.5 text-xs items-end">
            <!-- Date Issued From -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                    <i class="fa-regular fa-calendar-check text-blue-600 dark:text-blue-400 mr-1"></i>Issued From:
                </label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"
                       class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Date Issued To -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                    <i class="fa-regular fa-calendar-check text-blue-600 dark:text-blue-400 mr-1"></i>Issued To:
                </label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"
                       class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Issued Year (Quick Filter) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                    <i class="fa-regular fa-calendar text-blue-600 dark:text-blue-400 mr-1"></i>Issued Year:
                </label>
                <select name="issued_year"
                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 font-medium focus:outline-none focus:border-blue-500">
                    <option value="">All Available Years</option>
                    <?php foreach ($availableYears as $yr): ?>
                        <option value="<?= $yr ?>" <?= $issuedYear === (string)$yr ? 'selected' : '' ?>>
                            Year <?= $yr ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($isDean): ?>
            <!-- Faculty Member (Dean Oversight) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                    <i class="fa-solid fa-user-graduate text-indigo-500 mr-1"></i>Faculty Holder:
                </label>
                <select name="faculty_id"
                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 font-medium focus:outline-none focus:border-blue-500">
                    <option value="">All Faculty</option>
                    <?php foreach ($facultyMembers as $fm): ?>
                        <option value="<?= $fm['id'] ?>" <?= $targetFacultyId === (int)$fm['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($fm['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <div></div>
            <?php endif; ?>

            <!-- Keyword Search -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 mr-1"></i>Search Keyword:
                </label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Title, cert no, issuer..."
                       class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-1.5">
                <button type="submit"
                        class="flex-1 py-1.5 px-3 rounded-lg bg-[#0d2342] dark:bg-slate-800 hover:bg-[#14335e] dark:hover:bg-slate-700 text-white font-bold text-xs shadow-sm border border-transparent dark:border-slate-700 transition-all flex items-center justify-center gap-1 cursor-pointer">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>Apply</span>
                </button>
                <?php if ($search || $dateFrom || $dateTo || $issuedYear || ($isDean && $targetFacultyId)): ?>
                    <a href="vault.php?view=<?= urlencode($viewMode) ?><?= $category !== 'All' ? '&category=' . urlencode($category) : '' ?>"
                       class="py-1.5 px-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-rose-600 dark:text-rose-400 font-bold text-xs transition-all flex items-center justify-center cursor-pointer"
                       title="Reset Filters">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800">
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-emerald-600 text-xs"></i>
                <span class="font-medium text-slate-600">Strict Date Filtering: Evaluates certificate's actual <strong>Issued Date</strong> (normalized ISO YYYY-MM-DD), not system upload timestamp.</span>
            </span>
            <span class="font-mono text-slate-600">Matching Records: <strong><?= count($records) ?></strong></span>
        </div>
    </form>
</div>

<!-- Bulk Actions Toolbar (Requirement 9) -->
<div id="bulk-toolbar" class="hidden bg-slate-900 text-white rounded-xl p-3 shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs animate-in fade-in duration-200">
    <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 font-bold cursor-pointer">
            <input type="checkbox" id="bulk-select-all" onchange="toggleSelectAll(this)" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
            <span id="bulk-selection-count">0 items selected</span>
        </label>
        <span class="text-white/40">|</span>
        <span class="text-slate-300 text-[11px]">Select records for batch actions</span>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" onclick="exportSelected()"
                class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-print"></i>
            <span>Export Selected (PDF / Print)</span>
        </button>
        <button type="button" onclick="confirmBulkDelete()"
                class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs transition-all flex items-center gap-1.5 cursor-pointer">
            <i class="fa-regular fa-trash-can"></i>
            <span>Permanently Delete Selected</span>
        </button>
    </div>
</div>

<!-- Main Records Display: Table View or Grid View (Requirement 5) -->
<?php if (empty($records)): ?>
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center shadow-sm space-y-3">
        <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-2xl">
            <i class="fa-regular fa-folder-open"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-slate-800">No cryptographic records found</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                <?= $search || $dateFrom || $dateTo || $issuedYear ? 'No records match the selected Issued Date range or keyword.' : 'Ingest your seminars, publications, or certificates via the Smart Pipeline to securely archive them.' ?>
            </p>
        </div>
        <div class="pt-2 flex items-center justify-center gap-2">
            <?php if ($search || $dateFrom || $dateTo || $issuedYear): ?>
                <a href="vault.php?view=<?= urlencode($viewMode) ?>" class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition-all">
                    Reset Date &amp; Keyword Filters
                </a>
            <?php endif; ?>
            <a href="upload.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#0d2342] hover:bg-[#14335e] text-white text-xs font-bold shadow-sm transition-all">
                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                <span>Ingest Document Now</span>
            </a>
        </div>
    </div>
<?php elseif ($viewMode === 'table'): ?>
    <!-- Table View (Requirement 5 & 6) -->
    <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-bold">
                    <tr>
                        <th class="p-3 w-10 text-center">
                            <input type="checkbox" onchange="toggleSelectAll(this)" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                        </th>
                        <th class="p-3 w-12 text-slate-400 font-mono">#</th>
                        <th class="p-3">Document Title</th>
                        <th class="p-3"><?= $isDean ? 'Certificate Holder / Lead Author' : 'Authorship & Attribution' ?></th>
                        <th class="p-3">Cert No. / Ref</th>
                        <th class="p-3">Date Issued</th>
                        <th class="p-3">Issuing Authority</th>
                        <th class="p-3">Category</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-[11px]">
                    <?php foreach ($records as $idx => $cert): ?>
                        <?php
                            $isOwner = ((int)$cert['faculty_id'] === (int)$currentUser['id']);
                            $isCoAuthor = !$isDean && !$isOwner;
                        ?>
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors group <?= $isCoAuthor ? 'bg-purple-50/30 dark:bg-purple-950/10' : '' ?>">
                            <td class="p-3 text-center">
                                <input type="checkbox" name="record_select" value="<?= $cert['id'] ?>" onchange="updateSelectionBar()" class="record-checkbox rounded text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            </td>
                            <td class="p-3 font-mono text-slate-400 font-bold"><?= $idx + 1 ?></td>
                            <td class="p-3 font-bold text-slate-900 dark:text-slate-100 max-w-xs">
                                <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                    <?php if ($isCoAuthor): ?>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800" title="You are listed as a contributing co-author on this document">
                                            <i class="fa-solid fa-user-group text-[8px]"></i>
                                            <span>Co-Author Record</span>
                                        </span>
                                    <?php elseif (!$isDean): ?>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800" title="You uploaded this certificate as the primary author">
                                            <i class="fa-solid fa-user-check text-[8px]"></i>
                                            <span>Primary Author (My Upload)</span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <a href="view_document.php?id=<?= $cert['id'] ?>" target="_blank" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-1" title="<?= htmlspecialchars($cert['title']) ?>">
                                    <?= htmlspecialchars($cert['title']) ?>
                                </a>
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-mono mt-0.5">
                                    <span><?= strtoupper(htmlspecialchars($cert['file_type'])) ?></span>
                                    <span>&bull;</span>
                                    <span><?= round(($cert['file_size'] ?? 1024) / 1024) ?> KB</span>
                                </div>
                            </td>
                            <td class="p-3">
                                <?php if ($isDean): ?>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block"><?= htmlspecialchars($cert['faculty_name']) ?></span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 block"><?= htmlspecialchars($cert['academic_rank'] ?? 'CICI Faculty') ?></span>
                                    <?php if (!empty($cert['co_authors'])): ?>
                                        <span class="text-[9px] text-purple-600 dark:text-purple-400 font-medium block mt-0.5 truncate max-w-[150px]" title="Co-Authors: <?= htmlspecialchars($cert['co_authors']) ?>">
                                            <i class="fa-solid fa-users text-[8px] mr-1"></i>Co-Authors: <?= htmlspecialchars($cert['co_authors']) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php elseif ($isCoAuthor): ?>
                                    <div class="p-2 rounded-lg bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-800/60 space-y-0.5">
                                        <div class="text-[9.5px] font-bold text-purple-900 dark:text-purple-200 flex items-center gap-1">
                                            <i class="fa-solid fa-user-tag text-[9px] text-purple-600 dark:text-purple-400"></i>
                                            <span>Co-Author Record</span>
                                        </div>
                                        <div class="text-[10px] text-slate-600 dark:text-slate-300">
                                            <span class="text-slate-400 dark:text-slate-500 text-[9px] block">Uploaded by Lead Author:</span>
                                            <strong class="text-slate-800 dark:text-slate-200 block truncate max-w-[150px]" title="<?= htmlspecialchars($cert['faculty_name']) ?>"><?= htmlspecialchars($cert['faculty_name']) ?></strong>
                                        </div>
                                        <?php if (!empty($cert['co_authors'])): ?>
                                            <div class="text-[9px] text-purple-600 dark:text-purple-400 font-medium truncate pt-0.5 max-w-[150px]" title="All Co-Authors: <?= htmlspecialchars($cert['co_authors']) ?>">
                                                <i class="fa-solid fa-users text-[8px] mr-1"></i>All: <?= htmlspecialchars($cert['co_authors']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="space-y-0.5">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 block"><?= htmlspecialchars($currentUser['full_name']) ?> <span class="text-[9px] text-blue-600 dark:text-blue-400 font-bold">(You)</span></span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block"><?= htmlspecialchars($cert['academic_rank'] ?? ($profile['academic_rank'] ?? 'Lead Author / Uploader')) ?></span>
                                        <?php if (!empty($cert['co_authors'])): ?>
                                            <span class="text-[9px] text-purple-600 dark:text-purple-400 font-medium block mt-0.5 truncate max-w-[150px]" title="Co-Authors: <?= htmlspecialchars($cert['co_authors']) ?>">
                                                <i class="fa-solid fa-users text-[8px] mr-1"></i>+<?= htmlspecialchars($cert['co_authors']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 font-mono text-slate-600 dark:text-slate-400">
                                <?= !empty($cert['cert_number']) ? htmlspecialchars($cert['cert_number']) : '<span class="text-slate-300 dark:text-slate-600 italic">N/A</span>' ?>
                            </td>
                            <td class="p-3 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                <span class="font-bold text-blue-950 dark:text-blue-300"><?= htmlspecialchars($cert['date_issued']) ?></span>
                            </td>
                            <td class="p-3 text-slate-600 dark:text-slate-400 max-w-[140px] truncate" title="<?= htmlspecialchars($cert['issuer']) ?>">
                                <?= htmlspecialchars($cert['issuer']) ?>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                    <?= htmlspecialchars($cert['category']) ?>
                                </span>
                            </td>
                            <td class="p-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold">
                                    <i class="fa-solid fa-lock text-[8px]"></i>
                                    <span>AES-256</span>
                                </span>
                            </td>
                            <td class="p-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <a href="view_document.php?id=<?= $cert['id'] ?>" target="_blank"
                                       class="px-2.5 py-1 rounded bg-[#0d2342] dark:bg-slate-800 hover:bg-[#14335e] dark:hover:bg-slate-700 text-white text-[10px] font-bold inline-flex items-center gap-1 shadow-sm border border-transparent dark:border-slate-700 transition-all"
                                       title="Decrypt &amp; View">
                                        <i class="fa-regular fa-eye"></i>
                                        <span>View</span>
                                    </a>
                                    <a href="view_document.php?id=<?= $cert['id'] ?>&download=1"
                                       class="p-1 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs transition-all"
                                       title="Volatile Download">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    <button type="button" onclick="confirmDelete(<?= (int)$cert['id'] ?>, <?= htmlspecialchars(json_encode($cert['title']), ENT_QUOTES, 'UTF-8') ?>, <?= $isCoAuthor ? 'true' : 'false' ?>, <?= htmlspecialchars(json_encode($cert['faculty_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/60 <?= $isCoAuthor ? 'text-purple-500 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300' : 'text-slate-400 hover:text-rose-600 dark:hover:text-rose-400' ?> transition-colors cursor-pointer"
                                            title="<?= $isCoAuthor ? 'Remove from My Vault (Disassociate Co-Authorship)' : 'Permanently Delete Record' ?>">
                                        <i class="<?= $isCoAuthor ? 'fa-solid fa-user-minus' : 'fa-regular fa-trash-can' ?>"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <!-- Grid View -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php foreach ($records as $cert): ?>
            <?php
                $isOwner = ((int)$cert['faculty_id'] === (int)$currentUser['id']);
                $isCoAuthor = !$isDean && !$isOwner;
            ?>
            <div class="bg-white dark:bg-[#111c36] rounded-xl border <?= $isCoAuthor ? 'border-purple-200 dark:border-purple-900/60 ring-1 ring-purple-100 dark:ring-purple-900/30' : 'border-slate-200 dark:border-slate-800' ?> p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                <div>
                    <!-- Card Header -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <input type="checkbox" name="record_select" value="<?= $cert['id'] ?>" onchange="updateSelectionBar()" class="record-checkbox rounded text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                <?= htmlspecialchars($cert['category']) ?>
                            </span>
                            <?php if ($isCoAuthor): ?>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800 flex items-center gap-1">
                                    <i class="fa-solid fa-user-group text-[8px]"></i>
                                    <span>Co-Author Record</span>
                                </span>
                            <?php elseif (!$isDean): ?>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800 flex items-center gap-1">
                                    <i class="fa-solid fa-user-check text-[8px]"></i>
                                    <span>My Upload</span>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold uppercase border border-slate-200 dark:border-slate-700">
                                <?= htmlspecialchars($cert['file_type']) ?>
                            </span>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                <i class="fa-solid fa-lock text-[8px]"></i>
                                <span>AES-256</span>
                            </span>
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" title="<?= htmlspecialchars($cert['title']) ?>">
                        <?= htmlspecialchars($cert['title']) ?>
                    </h3>

                    <!-- Authorship Attribution Section -->
                    <?php if ($isCoAuthor): ?>
                        <div class="mt-2.5 p-2.5 rounded-lg bg-purple-50/90 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/70 text-[11px] space-y-1">
                            <div class="flex items-center justify-between text-purple-900 dark:text-purple-200 font-bold text-[10px]">
                                <span class="flex items-center gap-1">
                                    <i class="fa-solid fa-user-group text-[9px] text-purple-600 dark:text-purple-400"></i>
                                    <span>Co-Author Record</span>
                                </span>
                                <span class="text-[8.5px] bg-purple-200 dark:bg-purple-800 text-purple-800 dark:text-purple-200 px-1.5 py-0.2 rounded font-semibold uppercase">Contributing</span>
                            </div>
                            <div class="text-[10px] text-slate-600 dark:text-slate-300">
                                <span class="text-slate-500 dark:text-slate-400 text-[9px] block">Uploaded by Lead Author:</span>
                                <strong class="text-slate-800 dark:text-slate-200 block truncate" title="<?= htmlspecialchars($cert['faculty_name']) ?>"><?= htmlspecialchars($cert['faculty_name']) ?></strong>
                            </div>
                            <?php if (!empty($cert['co_authors'])): ?>
                                <p class="text-[9px] text-purple-700 dark:text-purple-400 font-medium truncate pt-0.5" title="All Co-Authors: <?= htmlspecialchars($cert['co_authors']) ?>">
                                    <i class="fa-solid fa-users text-[8px] mr-1"></i>Co-Authors: <?= htmlspecialchars($cert['co_authors']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-2 space-y-0.5">
                            <p class="text-[11px] text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-user-graduate text-[10px] text-indigo-400"></i>
                                <span>Lead Author: <strong class="text-slate-800 dark:text-slate-200"><?= htmlspecialchars($cert['faculty_name'] ?? $currentUser['full_name']) ?></strong></span>
                                <?php if (!$isDean && $isOwner): ?>
                                    <span class="text-[9px] font-bold text-blue-600 dark:text-blue-400">(You)</span>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($cert['co_authors'])): ?>
                                <p class="text-[10px] text-purple-600 dark:text-purple-400 flex items-center gap-1.5 truncate" title="Co-Authors: <?= htmlspecialchars($cert['co_authors']) ?>">
                                    <i class="fa-solid fa-users text-[9px]"></i>
                                    <span>Co-Authors: <?= htmlspecialchars($cert['co_authors']) ?></span>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Cert No. -->
                    <?php if (!empty($cert['cert_number'])): ?>
                        <p class="text-[10px] font-mono text-slate-500 dark:text-slate-400 mt-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-hashtag text-[9px] text-slate-400"></i>
                            <span>Ref: <?= htmlspecialchars($cert['cert_number']) ?></span>
                        </p>
                    <?php endif; ?>

                    <!-- Issuer -->
                    <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-building-columns text-[10px] text-slate-400"></i>
                        <span class="truncate"><?= htmlspecialchars($cert['issuer']) ?></span>
                    </p>

                    <!-- Date Issued -->
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 font-mono">
                        <i class="fa-regular fa-calendar-check text-[10px] text-blue-600"></i>
                        <span>Date Issued: <strong class="text-slate-800 dark:text-slate-200"><?= htmlspecialchars($cert['date_issued']) ?></strong></span>
                    </p>
                </div>

                <!-- Card Actions matching Figure D.2 -->
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <a href="view_document.php?id=<?= $cert['id'] ?>" target="_blank"
                       class="px-2.5 py-1 rounded bg-[#0d2342] dark:bg-slate-800 hover:bg-[#14335e] dark:hover:bg-slate-700 text-white text-[11px] font-bold flex items-center gap-1.5 transition-all shadow-sm">
                        <i class="fa-regular fa-eye text-[10px]"></i>
                        <span>Decrypt &amp; View</span>
                    </a>

                    <div class="flex items-center gap-1.5">
                        <a href="view_document.php?id=<?= $cert['id'] ?>&download=1"
                           class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs transition-all"
                           title="Secure Volatile Download">
                            <i class="fa-solid fa-download"></i>
                        </a>

                        <button type="button" onclick="confirmDelete(<?= (int)$cert['id'] ?>, <?= htmlspecialchars(json_encode($cert['title']), ENT_QUOTES, 'UTF-8') ?>, <?= $isCoAuthor ? 'true' : 'false' ?>, <?= htmlspecialchars(json_encode($cert['faculty_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)"
                                class="p-1.5 rounded hover:bg-rose-50 dark:hover:bg-rose-950/60 <?= $isCoAuthor ? 'text-purple-500 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300' : 'text-slate-400 hover:text-rose-600 dark:hover:text-rose-400' ?> transition-colors cursor-pointer"
                                title="<?= $isCoAuthor ? 'Remove from My Vault (Disassociate Co-Authorship)' : 'Permanently Delete Record' ?>">
                            <i class="<?= $isCoAuthor ? 'fa-solid fa-user-minus' : 'fa-regular fa-trash-can' ?>"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
    function updateSelectionBar() {
        const checkboxes = document.querySelectorAll('.record-checkbox:checked');
        const count = checkboxes.length;
        const toolbar = document.getElementById('bulk-toolbar');
        const counter = document.getElementById('bulk-selection-count');

        if (count > 0) {
            toolbar.classList.remove('hidden');
            counter.textContent = `${count} item${count > 1 ? 's' : ''} selected`;
        } else {
            toolbar.classList.add('hidden');
        }
    }

    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.record-checkbox');
        checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
        updateSelectionBar();
    }

    function getSelectedIds() {
        const checked = document.querySelectorAll('.record-checkbox:checked');
        return Array.from(checked).map(cb => cb.value);
    }

    function exportSelected() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        window.open(`export.php?ids=${ids.join(',')}`, '_blank');
    }

    function confirmBulkDelete() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        showSystemConfirm({
            title: 'Confirm Bulk Cryptographic Deletion',
            message: `Are you sure you wish to permanently remove ${ids.length} selected certificate record(s) from your ledger? Primary author files will have their encrypted payloads purged from disk, while co-authored files will disassociate your authorship without affecting other co-authors. All actions are immutably logged in the audit ledger.`,
            type: 'danger',
            confirmText: `Yes, Delete ${ids.length} Records`,
            onConfirm: function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'vault.php';
                const actIn = document.createElement('input');
                actIn.name = 'action';
                actIn.value = 'bulk_delete';
                form.appendChild(actIn);

                const idsIn = document.createElement('input');
                idsIn.name = 'selected_ids';
                idsIn.value = ids.join(',');
                form.appendChild(idsIn);

                const csrfIn = document.createElement('input');
                csrfIn.name = 'csrf_token';
                csrfIn.value = window.CSRF_TOKEN || '';
                form.appendChild(csrfIn);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function confirmDelete(id, title, isCoAuthor = false, leadAuthor = '') {
        if (isCoAuthor) {
            showSystemConfirm({
                title: 'Remove Co-Authored Document',
                message: `You are listed as a co-author on <strong>"${title}"</strong>.<br><br>Removing this record will <strong>remove it from your personal vault</strong>. The original uploaded document and cryptographic archive owned by lead author <strong>${leadAuthor || 'the uploader'}</strong> will <strong>NOT</strong> be deleted.`,
                type: 'warning',
                confirmText: 'Yes, Remove from My Vault',
                onConfirm: function() {
                    executeDeletePost(id);
                }
            });
        } else {
            showSystemConfirm({
                title: 'Confirm Cryptographic Removal',
                message: `Are you sure you wish to permanently remove "${title}" from the encrypted repository? This action purges the AES-256 ciphertext from disk and is logged in the compliance audit ledger.`,
                type: 'danger',
                confirmText: 'Yes, Permanently Delete',
                onConfirm: function() {
                    executeDeletePost(id);
                }
            });
        }
    }

    function executeDeletePost(id) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'vault.php';
        const actIn = document.createElement('input');
        actIn.name = 'action';
        actIn.value = 'delete';
        form.appendChild(actIn);

        const idIn = document.createElement('input');
        idIn.name = 'certificate_id';
        idIn.value = id;
        form.appendChild(idIn);

        const csrfIn = document.createElement('input');
        csrfIn.name = 'csrf_token';
        csrfIn.value = window.CSRF_TOKEN || '';
        form.appendChild(csrfIn);

        document.body.appendChild(form);
        form.submit();
    }

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showSystemAlert({
            title: 'Record Cryptographically Purged',
            message: 'The certificate record, ciphertext payload, and author associations have been successfully deleted from the encrypted storage vault.',
            type: 'success',
            buttonText: 'Understood'
        });
    });
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'coauthor_removed'): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showSystemAlert({
            title: 'Co-Authorship Disassociated',
            message: 'This collaborative record has been successfully removed from your personal vault. The primary author\'s original upload and repository archive remain intact.',
            type: 'success',
            buttonText: 'Understood'
        });
    });
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'bulk_deleted'): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showSystemAlert({
            title: 'Bulk Deletion Completed',
            message: 'Successfully purged <?= (int)($_GET['count'] ?? 0) ?> selected certificate records and their ciphertexts from the vault.',
            type: 'success',
            buttonText: 'Understood'
        });
    });
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'csrf_violation'): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showSystemAlert({
            title: 'Security Alert: CSRF Violation',
            message: 'The requested action was blocked due to an invalid or missing security verification token. Please refresh the page and try again.',
            type: 'error',
            buttonText: 'Understood'
        });
    });
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'unauthorized_delete'): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showSystemAlert({
            title: 'Deletion Denied',
            message: 'You do not have administrative or ownership permissions to delete this certificate record.',
            type: 'error',
            buttonText: 'Understood'
        });
    });
    <?php endif; ?>
</script>

<?php require_once __DIR__ . '/../layouts/layout_end.php'; ?>
