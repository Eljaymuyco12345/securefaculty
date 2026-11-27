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
$scope = $scope ?? ($_GET['scope'] ?? 'All');
$viewMode = $viewMode ?? 'table';
$folderFilter = $folderFilter ?? 'all';
$folders = $folders ?? [];
$currentFolder = $currentFolder ?? null;
$availableYears = $availableYears ?? [];
$facultyMembers = $facultyMembers ?? [];
$allCategories = $allCategories ?? ['All', 'Modules & IMs', 'Research', 'Seminars', 'Trainings', 'Workshops', 'Awards', 'Milestones', 'Other'];
$targetFacultyId = $targetFacultyId ?? null;
$activeNav = $activeNav ?? 'certificates';
$pageTitle = $pageTitle ?? ($isDean ? 'Institutional Department Vault' : 'Faculty Personal Digital Vault');

if (!function_exists('renderScopeBadge')) {
    function renderScopeBadge(?string $scope): string {
        $s = trim($scope ?? '');
        $sl = strtolower($s);
        if (str_contains($sl, 'internat')) {
            return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800" title="Accreditation Scope: International"><i class="fa-solid fa-earth-americas text-[9px] text-indigo-600 dark:text-indigo-400"></i><span>International</span></span>';
        } elseif (str_contains($sl, 'nation')) {
            return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800" title="Accreditation Scope: National"><i class="fa-solid fa-flag text-[9px] text-emerald-600 dark:text-emerald-400"></i><span>National</span></span>';
        } elseif (str_contains($sl, 'region')) {
            return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800" title="Accreditation Scope: Regional"><i class="fa-solid fa-map-location-dot text-[9px] text-amber-600 dark:text-amber-400"></i><span>Regional</span></span>';
        } else {
            return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700" title="Accreditation Scope: Institutional"><i class="fa-solid fa-building-columns text-[9px] text-slate-500 dark:text-slate-400"></i><span>Institutional</span></span>';
        }
    }
}

if (!function_exists('renderIndexingBadge')) {
    function renderIndexingBadge(?string $indexing, ?string $category = null): string {
        $idx = trim($indexing ?? '');
        $idxL = strtolower($idx);
        if (str_contains($idxL, 'scopus')) {
            return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700 shadow-xs" title="Indexing: Scopus Indexed"><i class="fa-solid fa-certificate text-[9px] text-amber-600 dark:text-amber-400"></i><span>Scopus Indexed</span></span>';
        } elseif (str_contains($idxL, 'web of science') || str_contains($idxL, 'wos')) {
            return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800" title="Indexing: Web of Science"><i class="fa-solid fa-flask text-[9px] text-purple-600 dark:text-purple-400"></i><span>WoS</span></span>';
        } elseif (str_contains($idxL, 'peer')) {
            return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-teal-50 dark:bg-teal-950/70 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800" title="Indexing: Peer-Reviewed"><i class="fa-solid fa-check-double text-[9px] text-teal-600 dark:text-teal-400"></i><span>Peer-Reviewed</span></span>';
        }
        return '';
    }
}

if (!function_exists('resolveAaccupArea')) {
    function resolveAaccupArea(?string $area, ?string $category = null): string {
        if (!empty($area) && trim($area) !== '') return trim($area);
        $cat = strtolower(trim($category ?? ''));
        if (str_contains($cat, 'research')) return 'Area V: Research';
        if (str_contains($cat, 'instructional') || str_contains($cat, 'module')) return 'Area III: Curriculum and Instruction';
        if (str_contains($cat, 'seminar') || str_contains($cat, 'training') || str_contains($cat, 'workshop') || str_contains($cat, 'award') || str_contains($cat, 'milestone')) return 'Area VIII: Faculty Development';
        return 'Area VIII: Faculty Development';
    }
}

if (!function_exists('getFolderColorClasses')) {
    function getFolderColorClasses(string $color): array {
        return match (strtolower(trim($color))) {
            'emerald', 'green' => [
                'icon' => 'text-emerald-500 dark:text-emerald-400',
                'bg' => 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-200 dark:border-emerald-800/70',
                'badge' => 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300',
            ],
            'amber', 'yellow' => [
                'icon' => 'text-amber-500 dark:text-amber-400',
                'bg' => 'bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-800/70',
                'badge' => 'bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300',
            ],
            'rose', 'red' => [
                'icon' => 'text-rose-500 dark:text-rose-400',
                'bg' => 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-800/70',
                'badge' => 'bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300',
            ],
            'purple', 'violet' => [
                'icon' => 'text-violet-500 dark:text-violet-400',
                'bg' => 'bg-violet-50 dark:bg-violet-950/50 border-violet-200 dark:border-violet-800/70',
                'badge' => 'bg-violet-100 dark:bg-violet-900/60 text-violet-700 dark:text-violet-300',
            ],
            'cyan' => [
                'icon' => 'text-cyan-500 dark:text-cyan-400',
                'bg' => 'bg-cyan-50 dark:bg-cyan-950/50 border-cyan-200 dark:border-cyan-800/70',
                'badge' => 'bg-cyan-100 dark:bg-cyan-900/60 text-cyan-700 dark:text-cyan-300',
            ],
            'indigo' => [
                'icon' => 'text-indigo-500 dark:text-indigo-400',
                'bg' => 'bg-indigo-50 dark:bg-indigo-950/50 border-indigo-200 dark:border-indigo-800/70',
                'badge' => 'bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300',
            ],
            'teal' => [
                'icon' => 'text-teal-500 dark:text-teal-400',
                'bg' => 'bg-teal-50 dark:bg-teal-950/50 border-teal-200 dark:border-teal-800/70',
                'badge' => 'bg-teal-100 dark:bg-teal-900/60 text-teal-700 dark:text-teal-300',
            ],
            'slate', 'gray' => [
                'icon' => 'text-slate-500 dark:text-slate-400',
                'bg' => 'bg-slate-100 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700',
                'badge' => 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
            ],
            default => [
                'icon' => 'text-blue-500 dark:text-blue-400',
                'bg' => 'bg-blue-50 dark:bg-blue-950/50 border-blue-200 dark:border-blue-800/70',
                'badge' => 'bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300',
            ],
        };
    }
}

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
        <?php $folderQuerySuffix = ($folderFilter !== 'all') ? '&folder_id=' . urlencode((string)$folderFilter) : ''; ?>
        <div class="inline-flex rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111c36] p-0.5 shadow-sm">
            <a href="vault.php?view=table&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&issued_year=<?= urlencode($issuedYear) ?><?= ($scope && $scope !== 'All') ? '&scope=' . urlencode($scope) : '' ?><?= $isDean && $targetFacultyId ? '&faculty_id=' . $targetFacultyId : '' ?><?= $folderQuerySuffix ?>"
               class="px-2.5 py-1 rounded text-xs font-bold transition-all flex items-center gap-1.5 <?= $viewMode === 'table' ? 'bg-[#0d2342] text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' ?>"
               title="Table List View">
                <i class="fa-solid fa-table-list"></i>
                <span class="hidden sm:inline">Table View</span>
            </a>
            <a href="vault.php?view=grid&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&issued_year=<?= urlencode($issuedYear) ?><?= ($scope && $scope !== 'All') ? '&scope=' . urlencode($scope) : '' ?><?= $isDean && $targetFacultyId ? '&faculty_id=' . $targetFacultyId : '' ?><?= $folderQuerySuffix ?>"
               class="px-2.5 py-1 rounded text-xs font-bold transition-all flex items-center gap-1.5 <?= $viewMode === 'grid' ? 'bg-[#0d2342] text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' ?>"
               title="Card Grid View">
                <i class="fa-solid fa-grip"></i>
                <span class="hidden sm:inline">Grid View</span>
            </a>
        </div>

        <!-- Direct Link to Accreditation Report & PDF -->
        <a href="export.php?category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>&issued_year=<?= urlencode($issuedYear) ?><?= ($scope && $scope !== 'All') ? '&scope=' . urlencode($scope) : '' ?><?= $isDean && $targetFacultyId ? '&faculty_id=' . $targetFacultyId : '' ?>"
           class="px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 via-rose-500 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-rose-600/30 ring-2 ring-rose-400/40 hover:ring-rose-300 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-file-pdf text-sm animate-pulse"></i>
            <span>Official Report &amp; PDF</span>
        </a>

        <a href="upload.php" class="px-3.5 py-1.5 bg-[#1da055] hover:bg-[#188c49] text-white rounded-lg font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
            <span>Ingest Document</span>
        </a>
    </div>
</div>

<!-- Folders & Breadcrumb Bar -->
<div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3 shadow-sm space-y-3 transition-colors">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <!-- Breadcrumb -->
        <nav aria-label="Vault Folder Breadcrumb" class="flex items-center gap-1.5 text-xs font-semibold flex-wrap">
            <a href="vault.php?view=<?= urlencode($viewMode) ?>&folder_id=all<?= $category !== 'All' ? '&category=' . urlencode($category) : '' ?>"
               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg transition-all <?= $folderFilter === 'all' ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400' ?>">
                <i class="fa-solid fa-vault text-blue-600 dark:text-blue-400 text-[11px]"></i>
                <span>All Vault Files</span>
            </a>
            <?php if ($currentFolder): ?>
                <?php $cfColors = getFolderColorClasses($currentFolder['color'] ?? 'blue'); ?>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400"></i>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border <?= $cfColors['bg'] ?> text-slate-900 dark:text-white font-bold">
                    <i class="fa-solid fa-folder-open <?= $cfColors['icon'] ?> text-xs"></i>
                    <span><?= htmlspecialchars($currentFolder['name']) ?></span>
                </span>
            <?php elseif ($folderFilter === 'unfiled'): ?>
                <i class="fa-solid fa-chevron-right text-[9px] text-slate-400"></i>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 font-bold">
                    <i class="fa-regular fa-folder text-xs"></i>
                    <span>Unfiled Only</span>
                </span>
            <?php endif; ?>
        </nav>

        <!-- Quick Actions: Unfiled Only Pill & + New Folder -->
        <div class="flex items-center gap-2">
            <?php
                $unfiledToggleUrl = 'vault.php?view=' . urlencode($viewMode) . '&folder_id=' . ($folderFilter === 'unfiled' ? 'all' : 'unfiled');
                if ($category !== 'All') $unfiledToggleUrl .= '&category=' . urlencode($category);
                if ($isDean && $targetFacultyId) $unfiledToggleUrl .= '&faculty_id=' . $targetFacultyId;
            ?>
            <a href="<?= $unfiledToggleUrl ?>"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border transition-all <?= $folderFilter === 'unfiled' ? 'bg-amber-500 text-white border-amber-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-amber-50 dark:hover:bg-amber-950/40 hover:border-amber-300 dark:hover:border-amber-700' ?>">
                <i class="fa-regular fa-folder text-[11px]"></i>
                <span>Unfiled Only</span>
            </a>

            <button type="button" onclick="openFolderModal('create')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0d2342] dark:bg-blue-600 hover:bg-[#14335e] dark:hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition-all cursor-pointer">
                <i class="fa-solid fa-folder-plus text-[11px]"></i>
                <span>+ New Folder</span>
            </button>
        </div>
    </div>

    <?php if ($folderFilter === 'all'): ?>
        <?php if (!empty($folders)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                <?php foreach ($folders as $folder): ?>
                    <?php
                        $fId = (int)$folder['id'];
                        $fName = $folder['name'] ?? 'Untitled Folder';
                        $fColor = $folder['color'] ?? 'blue';
                        $fCount = (int)($folder['item_count'] ?? 0);
                        $fClasses = getFolderColorClasses($fColor);
                        $folderOpenUrl = 'vault.php?folder_id=' . $fId . '&view=' . urlencode($viewMode);
                        if ($category !== 'All') $folderOpenUrl .= '&category=' . urlencode($category);
                        if ($isDean && $targetFacultyId) $folderOpenUrl .= '&faculty_id=' . $targetFacultyId;
                    ?>
                    <div class="group flex items-center justify-between gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-800/80 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-sm transition-all">
                        <a href="<?= $folderOpenUrl ?>" class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div class="w-9 h-9 rounded-lg border <?= $fClasses['bg'] ?> flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-folder <?= $fClasses['icon'] ?> text-base"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" title="<?= htmlspecialchars($fName) ?>">
                                    <?= htmlspecialchars($fName) ?>
                                </div>
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold tabular-nums <?= $fClasses['badge'] ?> mt-0.5">
                                    <?= $fCount ?> <?= $fCount === 1 ? 'file' : 'files' ?>
                                </span>
                            </div>
                        </a>
                        <div class="flex items-center gap-1 flex-shrink-0 opacity-80 group-hover:opacity-100 transition-opacity">
                            <button type="button"
                                    onclick="openFolderModal('rename', <?= $fId ?>, <?= htmlspecialchars(json_encode($fName), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($fColor), ENT_QUOTES, 'UTF-8') ?>)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/60 transition-colors cursor-pointer"
                                    title="Rename Folder">
                                <i class="fa-solid fa-pen text-[11px]"></i>
                            </button>
                            <button type="button"
                                    onclick="confirmDeleteFolder(<?= $fId ?>, <?= htmlspecialchars(json_encode($fName), ENT_QUOTES, 'UTF-8') ?>, <?= $fCount ?>)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/60 transition-colors cursor-pointer"
                                    title="Delete Folder">
                                <i class="fa-regular fa-trash-can text-[11px]"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                <span class="flex items-center gap-1.5">
                    <i class="fa-regular fa-folder-open text-slate-400"></i>
                    <span>No virtual folders yet. Create a folder to organize your certificates (1-level folders).</span>
                </span>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Category Tabs Filter -->
<div class="flex flex-wrap items-center justify-between gap-2 bg-white dark:bg-[#111c36] p-2 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm text-xs transition-colors">
    <div class="flex flex-wrap gap-1.5">
        <?php
        $tabs = [
            'All' => 'All Categories',
            'Research Publication' => 'Research Publication',
            'Instructional Material' => 'Instructional Material / Course Module',
            'Seminar' => 'Seminar / Webinar',
            'Training' => 'Training / Bootcamp',
            'Workshop' => 'Workshop',
            'Award' => 'Award / Recognition',
            'Academic Milestone' => 'Academic Milestone / Appointment',
            'Other' => 'Other / Custom'
        ];
        foreach ($tabs as $key => $label):
            $isActive = ($category === $key);
            $tabUrl = 'vault.php?view=' . urlencode($viewMode) . '&category=' . urlencode($key);
            if ($isDean && $targetFacultyId) $tabUrl .= '&faculty_id=' . $targetFacultyId;
            if ($search) $tabUrl .= '&search=' . urlencode($search);
            if ($dateFrom) $tabUrl .= '&date_from=' . urlencode($dateFrom);
            if ($dateTo) $tabUrl .= '&date_to=' . urlencode($dateTo);
            if ($issuedYear) $tabUrl .= '&issued_year=' . urlencode($issuedYear);
            if ($scope && $scope !== 'All') $tabUrl .= '&scope=' . urlencode($scope);
            if ($folderFilter !== 'all') $tabUrl .= '&folder_id=' . urlencode((string)$folderFilter);
        ?>
            <a href="<?= $tabUrl ?>"
               class="px-3 py-1.5 rounded-lg text-xs transition-all <?= $isActive ? 'bg-[#0d2342] dark:bg-blue-600 text-white font-bold shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
        $activeFilterCount = ($search !== '' ? 1 : 0) + ($dateFrom !== '' ? 1 : 0) + ($dateTo !== '' ? 1 : 0) + ($issuedYear !== '' ? 1 : 0) + (($scope ?? 'All') !== 'All' ? 1 : 0) + ($isDean && $targetFacultyId ? 1 : 0);
    ?>
    <button type="button" onclick="toggleVaultFilters()" id="vaultFilterToggleBtn" aria-expanded="true" aria-controls="vaultFilterDisclosure"
            class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-[11px] flex items-center gap-1.5 transition-all cursor-pointer">
        <i class="fa-solid fa-sliders text-[10px] text-blue-600 dark:text-blue-400"></i>
        <span>Date &amp; Search Filters</span>
        <?php if ($activeFilterCount > 0): ?>
            <span class="px-1.5 py-0.2 rounded-full bg-blue-600 text-white text-[9px] font-bold tabular-nums"><?= $activeFilterCount ?></span>
        <?php endif; ?>
        <i id="vaultFilterChevron" class="fa-solid fa-chevron-up text-[9px] text-slate-400 transition-transform duration-200"></i>
    </button>
</div>

<!-- Date Issued & Multi-Field Filter Bar with Progressive Disclosure (#19 Progressive disclosure) -->
<div id="vaultFilterDisclosure" class="disclosure-panel">
    <div>
        <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 sm:p-4 shadow-sm space-y-3 transition-colors">
            <form method="GET" action="vault.php" class="space-y-3">
        <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
        <?php if ($category !== 'All'): ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
        <?php endif; ?>
        <?php if ($folderFilter !== 'all'): ?>
            <input type="hidden" name="folder_id" value="<?= htmlspecialchars((string)$folderFilter) ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-2.5 text-xs items-end">
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

            <!-- Scope Filter Dropdown -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                    <i class="fa-solid fa-earth-americas text-indigo-500 mr-1"></i>Scope / Level:
                </label>
                <select name="scope"
                        class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-200 font-medium focus:outline-none focus:border-blue-500">
                    <option value="All" <?= ($scope ?? 'All') === 'All' ? 'selected' : '' ?>>All Scopes</option>
                    <option value="International" <?= ($scope ?? '') === 'International' ? 'selected' : '' ?>>International</option>
                    <option value="National" <?= ($scope ?? '') === 'National' ? 'selected' : '' ?>>National</option>
                    <option value="Regional" <?= ($scope ?? '') === 'Regional' ? 'selected' : '' ?>>Regional</option>
                    <option value="Institutional" <?= ($scope ?? '') === 'Institutional' ? 'selected' : '' ?>>Institutional</option>
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
                <input type="text" id="vaultSearchInput" name="search" value="<?= htmlspecialchars($search) ?>"
                       oninput="filterVaultRecordsLive(this.value)"
                       placeholder="Live search title, cert no, issuer..."
                       class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-1.5">
                <button type="submit"
                        class="flex-1 py-1.5 px-3 rounded-lg bg-[#0d2342] dark:bg-slate-800 hover:bg-[#14335e] dark:hover:bg-slate-700 text-white font-bold text-xs shadow-sm border border-transparent dark:border-slate-700 transition-all flex items-center justify-center gap-1 cursor-pointer">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>Apply</span>
                </button>
                <?php if ($search || $dateFrom || $dateTo || $issuedYear || ($scope && $scope !== 'All') || ($isDean && $targetFacultyId)): ?>
                    <a href="vault.php?view=<?= urlencode($viewMode) ?><?= $category !== 'All' ? '&category=' . urlencode($category) : '' ?><?= $folderFilter !== 'all' ? '&folder_id=' . urlencode((string)$folderFilter) : '' ?>"
                       class="py-1.5 px-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-rose-600 dark:text-rose-400 font-bold text-xs transition-all flex items-center justify-center cursor-pointer"
                       title="Reset Filters">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-slate-800">
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-emerald-600 dark:text-emerald-400 text-xs"></i>
                <span class="font-medium text-slate-600 dark:text-slate-300">Strict Date Filtering: Evaluates certificate's actual <strong>Issued Date</strong> (normalized ISO YYYY-MM-DD), not system upload timestamp.</span>
            </span>
            <span class="font-mono tabular-nums text-slate-600 dark:text-slate-300">Matching Records: <strong><?= count($records) ?></strong></span>
        </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Toolbar (Requirement 9) -->
<div id="bulk-toolbar" class="hidden bg-slate-900 text-white rounded-xl p-3 shadow-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs animate-in fade-in duration-200">
    <div class="flex items-center gap-3">
        <label class="flex items-center gap-2 font-bold cursor-pointer">
            <input type="checkbox" id="bulk-select-all" onchange="toggleSelectAll(this)" class="rounded border-slate-500 dark:border-slate-600 dark:bg-slate-800 text-blue-600 accent-blue-600 focus:ring-blue-500 w-4 h-4">
            <span id="bulk-selection-count">0 items selected</span>
        </label>
        <span class="text-white/40">|</span>
        <span class="text-slate-300 text-[11px]">Select records for batch actions</span>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" onclick="openBulkMoveModal()"
                class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs transition-all flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-folder-open"></i>
            <span>Move to Folder</span>
        </button>
        <button type="button" onclick="exportSelected()"
                class="px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 via-rose-500 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-rose-600/40 ring-2 ring-rose-400/40 hover:ring-rose-300 transition-all flex items-center gap-2 cursor-pointer active:scale-95">
            <i class="fa-solid fa-file-pdf text-sm animate-pulse"></i>
            <span>Export Selected (PDF)</span>
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
    <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-12 text-center shadow-sm space-y-3 transition-colors">
        <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 mx-auto flex items-center justify-center text-2xl">
            <i class="fa-regular fa-folder-open"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">No cryptographic records found</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                <?= $search || $dateFrom || $dateTo || $issuedYear ? 'No records match the selected Issued Date range or keyword.' : 'Ingest your seminars, publications, or certificates via Upload & Scan to securely archive them.' ?>
            </p>
        </div>
        <div class="pt-2 flex items-center justify-center gap-2">
            <?php if ($search || $dateFrom || $dateTo || $issuedYear): ?>
                <a href="vault.php?view=<?= urlencode($viewMode) ?><?= $folderFilter !== 'all' ? '&folder_id=' . urlencode((string)$folderFilter) : '' ?>" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
                    Reset Date &amp; Keyword Filters
                </a>
            <?php endif; ?>
            <a href="upload.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#0d2342] dark:bg-blue-600 hover:bg-[#14335e] dark:hover:bg-blue-500 text-white text-xs font-bold shadow-sm transition-all">
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
                            <input type="checkbox" onchange="toggleSelectAll(this)" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-blue-600 accent-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
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
                <tbody id="vaultTableBody" class="divide-y divide-slate-100 dark:divide-slate-800 text-[11px]">
                    <?php foreach ($records as $idx => $cert): ?>
                        <?php
                            $isOwner = ((int)$cert['faculty_id'] === (int)$currentUser['id']);
                            $isCoAuthor = !$isDean && !$isOwner;
                        ?>
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors group <?= $isCoAuthor ? 'bg-purple-50/30 dark:bg-purple-950/10' : '' ?>">
                            <td class="p-3 text-center">
                                <input type="checkbox" name="record_select" value="<?= $cert['id'] ?>" onchange="updateSelectionBar()" class="record-checkbox rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-blue-600 accent-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
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
                                <button type="button"
                                        onclick="openDocPreview(<?= (int)$cert['id'] ?>, <?= htmlspecialchars(json_encode($cert['title']), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['issuer'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['date_issued'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['cert_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['scope'] ?? 'Institutional'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['indexing'] ?? 'None'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['place_issued'] ?? ($cert['venue'] ?? '')), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode(resolveAaccupArea($cert['accreditation_area'] ?? '', $cert['category'] ?? '')), ENT_QUOTES, 'UTF-8') ?>)"
                                        class="text-left hover:text-blue-600 dark:hover:text-blue-400 font-semibold transition-colors line-clamp-1 cursor-pointer"
                                        title="Preview <?= htmlspecialchars($cert['title']) ?>">
                                    <?= htmlspecialchars($cert['title']) ?>
                                </button>
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
                            <td class="p-3 text-slate-600 dark:text-slate-400 max-w-[150px]">
                                <div class="truncate font-medium" title="<?= htmlspecialchars($cert['issuer']) ?>"><?= htmlspecialchars($cert['issuer']) ?></div>
                                <?php $place = !empty($cert['place_issued']) ? $cert['place_issued'] : (!empty($cert['venue']) ? $cert['venue'] : ''); ?>
                                <?php if ($place): ?>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5 truncate" title="Venue / Place: <?= htmlspecialchars($place) ?>">
                                        <i class="fa-solid fa-location-dot text-rose-500 text-[9px] flex-shrink-0"></i>
                                        <span><?= htmlspecialchars($place) ?></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold inline-flex items-center gap-1.5 <?= CategoryService::getBadgeClass($cert['category'] ?? '') ?>">
                                        <i class="<?= CategoryService::getIcon($cert['category'] ?? '') ?> text-[10px]"></i>
                                        <span><?= htmlspecialchars(CategoryService::getShortLabel($cert['category'] ?? '')) ?></span>
                                    </span>
                                    <div class="flex items-center gap-1 flex-wrap">
                                        <?= renderScopeBadge($cert['scope'] ?? 'Institutional') ?>
                                        <?= renderIndexingBadge($cert['indexing'] ?? '', $cert['category'] ?? '') ?>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold">
                                    <i class="fa-solid fa-lock text-[8px]"></i>
                                    <span>AES-256</span>
                                </span>
                            </td>
                            <td class="p-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button"
                                            onclick="openDocPreview(<?= (int)$cert['id'] ?>, <?= htmlspecialchars(json_encode($cert['title']), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['issuer'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['date_issued'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['cert_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['scope'] ?? 'Institutional'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['indexing'] ?? 'None'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['place_issued'] ?? ($cert['venue'] ?? '')), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode(resolveAaccupArea($cert['accreditation_area'] ?? '', $cert['category'] ?? '')), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="px-2.5 py-1 rounded bg-[#0d2342] dark:bg-slate-800 hover:bg-[#14335e] dark:hover:bg-slate-700 text-white text-[10px] font-bold inline-flex items-center gap-1 shadow-sm border border-transparent dark:border-slate-700 transition-all cursor-pointer"
                                            title="Preview Document In-App">
                                        <i class="fa-regular fa-eye"></i>
                                        <span>View</span>
                                    </button>
                                    <a href="view_document.php?id=<?= $cert['id'] ?>&download=1"
                                       class="touch-target p-1 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs transition-all"
                                       title="Volatile Download">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    <button type="button"
                                            onclick="openMoveModal([<?= (int)$cert['id'] ?>], <?= htmlspecialchars(json_encode($cert['title']), ENT_QUOTES, 'UTF-8') ?>, <?= !empty($cert['folder_id']) ? (int)$cert['folder_id'] : 'null' ?>)"
                                            class="touch-target p-1 rounded bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-950/60 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 text-xs transition-all cursor-pointer"
                                            title="Move to Folder">
                                        <i class="fa-solid fa-folder-open"></i>
                                    </button>
                                    <button type="button" onclick="confirmDelete(<?= (int)$cert['id'] ?>, <?= htmlspecialchars(json_encode($cert['title']), ENT_QUOTES, 'UTF-8') ?>, <?= $isCoAuthor ? 'true' : 'false' ?>, <?= htmlspecialchars(json_encode($cert['faculty_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="touch-target p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/60 <?= $isCoAuthor ? 'text-purple-500 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300' : 'text-slate-400 hover:text-rose-600 dark:hover:text-rose-400' ?> transition-colors cursor-pointer"
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
    <div id="vaultGridBody" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php foreach ($records as $cardIdx => $cert): ?>
            <?php
                $isOwner = ((int)$cert['faculty_id'] === (int)$currentUser['id']);
                $isCoAuthor = !$isDean && !$isOwner;
            ?>
            <div style="--stagger-i: <?= (int)$cardIdx ?>" class="stagger-item bg-white dark:bg-[#111c36] rounded-xl border <?= $isCoAuthor ? 'border-purple-200 dark:border-purple-900/60 ring-1 ring-purple-100 dark:ring-purple-900/30' : 'border-slate-200 dark:border-slate-800' ?> p-4 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                <div>
                    <!-- Card Header -->
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <input type="checkbox" name="record_select" value="<?= $cert['id'] ?>" onchange="updateSelectionBar()" class="record-checkbox rounded border-slate-300 dark:border-slate-600 dark:bg-slate-800 text-blue-600 accent-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            <span class="px-2.5 py-1 rounded-md text-xs font-bold inline-flex items-center gap-1.5 <?= CategoryService::getBadgeClass($cert['category'] ?? '') ?>">
                                <i class="<?= CategoryService::getIcon($cert['category'] ?? '') ?> text-[10px]"></i>
                                <span><?= htmlspecialchars(CategoryService::getShortLabel($cert['category'] ?? '')) ?></span>
                            </span>
                            <?= renderScopeBadge($cert['scope'] ?? 'Institutional') ?>
                            <?= renderIndexingBadge($cert['indexing'] ?? '', $cert['category'] ?? '') ?>
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

                    <!-- Place Issued / Venue -->
                    <?php $place = !empty($cert['place_issued']) ? $cert['place_issued'] : (!empty($cert['venue']) ? $cert['venue'] : ''); ?>
                    <?php if ($place): ?>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 truncate" title="Venue / Place: <?= htmlspecialchars($place) ?>">
                            <i class="fa-solid fa-location-dot text-[10px] text-rose-500"></i>
                            <span>Venue / Place: <strong class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($place) ?></strong></span>
                        </p>
                    <?php endif; ?>

                    <!-- Date Issued -->
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 font-mono">
                        <i class="fa-regular fa-calendar-check text-[10px] text-blue-600"></i>
                        <span>Date Issued: <strong class="text-slate-800 dark:text-slate-200"><?= htmlspecialchars($cert['date_issued']) ?></strong></span>
                    </p>
                </div>

                <!-- Card Actions matching Figure D.2 -->
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <button type="button"
                            onclick="openDocPreview(<?= (int)$cert['id'] ?>, <?= htmlspecialchars(json_encode($cert['title']), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['issuer'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['date_issued'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['category'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['cert_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['scope'] ?? 'Institutional'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['indexing'] ?? 'None'), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cert['place_issued'] ?? ($cert['venue'] ?? '')), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode(resolveAaccupArea($cert['accreditation_area'] ?? '', $cert['category'] ?? '')), ENT_QUOTES, 'UTF-8') ?>)"
                            class="px-2.5 py-1 rounded bg-[#0d2342] dark:bg-slate-800 hover:bg-[#14335e] dark:hover:bg-slate-700 text-white text-[11px] font-bold flex items-center gap-1.5 transition-all shadow-sm cursor-pointer"
                            title="Preview Document In-App">
                        <i class="fa-regular fa-eye text-[10px]"></i>
                        <span>Decrypt &amp; View</span>
                    </button>

                    <div class="flex items-center gap-1.5">
                        <a href="view_document.php?id=<?= $cert['id'] ?>&download=1"
                           class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs transition-all"
                           title="Secure Volatile Download">
                            <i class="fa-solid fa-download"></i>
                        </a>

                        <button type="button"
                                onclick="openMoveModal([<?= (int)$cert['id'] ?>], <?= htmlspecialchars(json_encode($cert['title']), ENT_QUOTES, 'UTF-8') ?>, <?= !empty($cert['folder_id']) ? (int)$cert['folder_id'] : 'null' ?>)"
                                class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-950/60 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 text-xs transition-all cursor-pointer"
                                title="Move to Folder">
                            <i class="fa-solid fa-folder-open"></i>
                        </button>

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
    function toggleVaultFilters() {
        const panel = document.getElementById('vaultFilterDisclosure');
        const btn = document.getElementById('vaultFilterToggleBtn');
        const chevron = document.getElementById('vaultFilterChevron');
        if (!panel) return;
        const isCollapsed = panel.classList.toggle('is-collapsed');
        if (btn) btn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
        if (chevron) chevron.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
    }

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
            title: 'Delete Selected Documents?',
            message: `Are you sure you want to delete <strong>${ids.length} selected document(s)</strong> from the vault?`,
            type: 'danger',
            confirmText: `Delete ${ids.length} Documents`,
            cancelText: 'Cancel',
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
                title: 'Remove from Vault?',
                message: `Remove <strong>"${title}"</strong> from your personal vault? The original file will remain in the lead author's vault.`,
                type: 'warning',
                confirmText: 'Remove from Vault',
                cancelText: 'Cancel',
                onConfirm: function() {
                    executeDeletePost(id);
                }
            });
        } else {
            showSystemConfirm({
                title: 'Delete Document?',
                message: `Are you sure you want to permanently delete <strong>"${title}"</strong>? This will remove the file from the vault.`,
                type: 'danger',
                confirmText: 'Delete Document',
                cancelText: 'Cancel',
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

    // --- In-App PDF Document Preview Controller ---
    function openDocPreview(id, title, issuer, date, category, certNo, scope, indexing, venue, aaccupArea) {
        const dialog = document.getElementById('docPreviewModal');
        const iframe = document.getElementById('docPreviewIframe');
        const spinner = document.getElementById('docPreviewSpinner');
        const titleEl = document.getElementById('docPreviewTitle');
        const metaEl = document.getElementById('docPreviewMeta');
        const accProfileEl = document.getElementById('docPreviewAccreditationProfile');
        const newTabLink = document.getElementById('docPreviewNewTab');
        const downloadLink = document.getElementById('docPreviewDownload');

        if (!dialog || !iframe) return;

        if (titleEl) titleEl.textContent = title || 'Document Preview';
        if (metaEl) {
            let metaHtml = `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">${escapeDocHtml(category || 'Record')}</span>`;
            if (issuer) {
                metaHtml += `<span class="flex items-center gap-1"><i class="fa-solid fa-building-columns text-[9px] text-slate-400"></i> ${escapeDocHtml(issuer)}</span>`;
            }
            if (date) {
                const isResearch = (category === 'Research Publication');
                const dateLabelText = isResearch ? 'Published: ' : 'Issued: ';
                metaHtml += `<span class="flex items-center gap-1 font-mono" title="${isResearch ? 'Publication Date' : 'Date Issued'}"><i class="fa-regular fa-calendar-check text-[9px] text-emerald-500"></i> ${dateLabelText}${escapeDocHtml(date)}</span>`;
            }
            if (certNo) {
                metaHtml += `<span class="font-mono text-slate-400 text-[10px]">#${escapeDocHtml(certNo)}</span>`;
            }
            metaEl.innerHTML = metaHtml;
        }

        // Display full Accreditation Profile (AACCUP Area, Scope, Indexing, Venue / Place Issued)
        if (accProfileEl) {
            let accHtml = '';
            
            // 1. AACCUP Area
            const areaVal = aaccupArea || (category === 'Research Publication' ? 'Area V: Research' : (category === 'Instructional Material' ? 'Area III: Curriculum and Instruction' : 'Area VIII: Faculty Development'));
            accHtml += `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10.5px] font-bold bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800" title="AACCUP Evaluation Area"><i class="fa-solid fa-award text-indigo-500 text-[10px]"></i><span>${escapeDocHtml(areaVal)}</span></span>`;

            // 2. Scope / Classification Badge
            const scopeVal = scope || 'Institutional';
            let scopeClass = 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700';
            let scopeIcon = 'fa-building-columns';
            const sL = scopeVal.toLowerCase();
            if (sL.includes('internat')) {
                scopeClass = 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800';
                scopeIcon = 'fa-earth-americas text-indigo-500';
            } else if (sL.includes('nation')) {
                scopeClass = 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
                scopeIcon = 'fa-flag text-emerald-500';
            } else if (sL.includes('region')) {
                scopeClass = 'bg-amber-50 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800';
                scopeIcon = 'fa-map-location-dot text-amber-500';
            }
            accHtml += `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10.5px] font-bold ${scopeClass} border" title="Accreditation Scope / Classification"><i class="fa-solid ${scopeIcon} text-[10px]"></i><span>Scope: ${escapeDocHtml(scopeVal)}</span></span>`;

            // 3. Indexing / Refereed Status Badge (for Research Publication or indexed documents)
            if (indexing && indexing !== 'None' && indexing !== 'N/A') {
                let idxClass = 'bg-teal-50 dark:bg-teal-950/70 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800';
                let idxIcon = 'fa-check-double text-teal-500';
                let idxText = indexing;
                const iL = indexing.toLowerCase();
                if (iL.includes('scopus')) {
                    idxClass = 'bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border-amber-300 dark:border-amber-700';
                    idxIcon = 'fa-certificate text-amber-500';
                    idxText = 'Scopus Indexed';
                } else if (iL.includes('web of science') || iL.includes('wos')) {
                    idxClass = 'bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800';
                    idxIcon = 'fa-flask text-purple-500';
                    idxText = 'WoS';
                }
                accHtml += `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10.5px] font-bold ${idxClass} border" title="Journal Indexing / Refereed Status"><i class="fa-solid ${idxIcon} text-[10px]"></i><span>${escapeDocHtml(idxText)}</span></span>`;
            }

            // 4. Venue / Place Issued
            if (venue && venue !== 'None' && venue !== 'N/A') {
                accHtml += `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10.5px] font-medium bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800" title="Venue / Place Issued"><i class="fa-solid fa-location-dot text-rose-500 text-[10px]"></i><span>${escapeDocHtml(venue)}</span></span>`;
            }

            accProfileEl.innerHTML = accHtml;
        }

        const docUrl = 'view_document.php?id=' + encodeURIComponent(id);
        const downloadUrl = 'view_document.php?id=' + encodeURIComponent(id) + '&download=1';

        if (newTabLink) newTabLink.href = docUrl;
        if (downloadLink) downloadLink.href = downloadUrl;

        if (spinner) spinner.style.display = 'flex';
        iframe.src = docUrl;

        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            dialog.setAttribute('open', '');
        }
        document.documentElement.classList.add('overflow-hidden');
    }

    function closeDocPreview() {
        const dialog = document.getElementById('docPreviewModal');
        const iframe = document.getElementById('docPreviewIframe');
        if (!dialog) return;

        if (iframe) iframe.src = 'about:blank';
        if (typeof dialog.close === 'function') {
            dialog.close();
        } else {
            dialog.removeAttribute('open');
        }
        document.documentElement.classList.remove('overflow-hidden');
    }

    function escapeDocHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dialog = document.getElementById('docPreviewModal');
        if (dialog) {
            dialog.addEventListener('click', function(e) {
                const rect = dialog.getBoundingClientRect();
                const isInDialog = (
                    rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
                    rect.left <= e.clientX && e.clientX <= rect.left + rect.width
                );
                if (!isInDialog) {
                    closeDocPreview();
                }
            });
            dialog.addEventListener('cancel', function() {
                closeDocPreview();
            });
        }
    });
</script>

<!-- Native Modern Web Document Preview Modal -->
<style>
#docPreviewModal {
    display: none;
    overscroll-behavior: contain;
}
#docPreviewModal[open] {
    display: flex;
}
#docPreviewModal::backdrop {
    background: rgba(2, 6, 23, 0.85);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
</style>

<dialog id="docPreviewModal" aria-labelledby="docPreviewTitle" class="fixed inset-0 m-auto p-0 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0c1830] text-slate-800 dark:text-slate-100 shadow-2xl max-w-6xl w-[92vw] sm:w-[96vw] h-[90dvh] sm:h-[90vh] max-h-[90dvh] sm:max-h-[90vh] flex-col overflow-hidden outline-none z-50">
    <!-- Header -->
    <div class="px-4 sm:px-5 py-3 sm:py-3.5 bg-slate-50 dark:bg-[#111c36] border-b border-slate-200 dark:border-slate-800 flex flex-wrap sm:flex-nowrap items-center justify-between gap-2.5 sm:gap-3 flex-shrink-0">
        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700/80 p-1 flex items-center justify-center flex-shrink-0 shadow-sm">
                <img src="assets/images/isufst_logo.png" alt="ISUFST Official Seal" class="max-w-full max-h-full object-contain">
            </div>
            <div class="min-w-0 flex-1">
                <h3 id="docPreviewTitle" class="text-xs sm:text-base font-extrabold text-slate-900 dark:text-white truncate">Document Preview</h3>
                <div id="docPreviewMeta" class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 flex-wrap mt-0.5">
                    <!-- Badges populated dynamically via openDocPreview -->
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-2 flex-shrink-0 w-full sm:w-auto justify-between sm:justify-start">
            <a id="docPreviewNewTab" href="#" target="_blank" rel="noopener noreferrer"
               class="px-3 py-1.5 min-h-[38px] sm:min-h-0 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm"
               title="Open in a separate browser tab if needed">
                <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                <span class="inline">Open in Tab</span>
            </a>
            <div class="flex items-center gap-1.5">
                <a id="docPreviewDownload" href="#"
                   class="px-3.5 py-1.5 min-h-[38px] sm:min-h-0 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm active:scale-95 cursor-pointer"
                   title="Download Decrypted PDF Copy">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Download PDF</span>
                </a>
                <button type="button" onclick="closeDocPreview()"
                        class="p-2 min-w-[38px] min-h-[38px] flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all text-base cursor-pointer"
                        title="Close Preview (Esc)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Accreditation Profile Bar -->
    <div id="docPreviewAccreditationProfile" class="px-4 sm:px-5 py-2.5 bg-slate-100/90 dark:bg-[#0c1830] border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-2 text-xs flex-shrink-0">
        <!-- Badges populated dynamically via openDocPreview -->
    </div>

    <!-- Viewer Body -->
    <div class="relative flex-1 w-full h-full min-h-0 bg-slate-900 overflow-hidden">
        <div id="docPreviewSpinner" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/80 text-slate-300 z-10 space-y-3">
            <div class="w-10 h-10 border-4 border-rose-500/20 border-t-rose-500 rounded-full animate-spin"></div>
            <p class="text-xs font-mono text-slate-300">Decrypting AES-256 payload &amp; rendering PDF...</p>
        </div>
        <iframe id="docPreviewIframe" src="" class="w-full h-full border-0 rounded-b-2xl bg-slate-900" title="Secure In-App PDF Preview" onload="const sp = document.getElementById('docPreviewSpinner'); if(sp) sp.style.display='none';"></iframe>
    </div>
</dialog>

<script>
    function filterVaultRecordsLive(term) {
        const query = (term || '').toLowerCase().trim();
        const tableBody = document.getElementById('vaultTableBody');
        if (tableBody) {
            tableBody.querySelectorAll('tr').forEach(row => {
                const text = (row.textContent || '').toLowerCase();
                row.style.display = (!query || text.includes(query)) ? '' : 'none';
            });
        }
        const gridBody = document.getElementById('vaultGridBody');
        if (gridBody) {
            Array.from(gridBody.children).forEach(card => {
                const text = (card.textContent || '').toLowerCase();
                card.style.display = (!query || text.includes(query)) ? '' : 'none';
            });
        }
    }
</script>

<!-- Create / Rename Folder Modal -->
<div id="folderModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="folderModalTitle">
    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-[#111c36] border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 dark:bg-[#0c1830] border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 id="folderModalTitle" class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-folder-plus text-blue-600 dark:text-blue-400"></i>
                <span>New Virtual Folder</span>
            </h3>
            <button type="button" onclick="closeFolderModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="folderModalForm" onsubmit="submitFolderModal(event)" class="p-5 space-y-4 text-xs">
            <input type="hidden" id="folderModalMode" value="create">
            <input type="hidden" id="folderModalId" value="">
            <div>
                <label for="folderModalName" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Folder Name</label>
                <input type="text" id="folderModalName" required maxlength="120" placeholder="e.g., AACCUP 2026 Area V, Scopus Publications"
                       class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-blue-500">
            </div>
            <div id="folderColorPickerWrap">
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Folder Color</label>
                <div class="flex flex-wrap gap-2">
                    <?php
                    $colorOptions = [
                        'blue' => 'bg-blue-500',
                        'emerald' => 'bg-emerald-500',
                        'amber' => 'bg-amber-500',
                        'rose' => 'bg-rose-500',
                        'violet' => 'bg-violet-500',
                        'indigo' => 'bg-indigo-500',
                        'cyan' => 'bg-cyan-500',
                        'teal' => 'bg-teal-500',
                        'slate' => 'bg-slate-500',
                    ];
                    foreach ($colorOptions as $cKey => $cBg):
                    ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="folder_color" value="<?= $cKey ?>" class="sr-only peer" <?= $cKey === 'blue' ? 'checked' : '' ?>>
                            <span class="w-7 h-7 rounded-full <?= $cBg ?> block ring-2 ring-transparent peer-checked:ring-slate-900 dark:peer-checked:ring-white peer-checked:ring-offset-2 dark:peer-checked:ring-offset-[#111c36] transition-all" title="<?= ucfirst($cKey) ?>"></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="folderModalError" class="hidden p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-[11px] font-semibold"></div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeFolderModal()" class="px-3.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="folderModalSubmitBtn" class="px-4 py-1.5 rounded-lg bg-[#0d2342] dark:bg-blue-600 hover:bg-[#14335e] dark:hover:bg-blue-500 text-white font-bold shadow-sm transition-colors cursor-pointer">
                    Create Folder
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Move Certificates to Folder Modal -->
<div id="moveFolderModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="moveFolderModalTitle">
    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-[#111c36] border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 dark:bg-[#0c1830] border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 id="moveFolderModalTitle" class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-folder-open text-blue-600 dark:text-blue-400"></i>
                <span>Move to Folder</span>
            </h3>
            <button type="button" onclick="closeMoveModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="moveFolderForm" onsubmit="submitMoveModal(event)" class="p-5 space-y-4 text-xs">
            <p id="moveFolderSubtitle" class="text-slate-600 dark:text-slate-300 text-[11px]"></p>
            <div>
                <label for="moveTargetFolderSelect" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">Destination Folder</label>
                <select id="moveTargetFolderSelect" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-medium focus:outline-none focus:border-blue-500">
                    <option value="">-- Unfiled (Remove from Folder) --</option>
                    <?php foreach ($folders as $f): ?>
                        <option value="<?= (int)$f['id'] ?>">
                            <?= htmlspecialchars($f['name']) ?> (<?= (int)($f['item_count'] ?? 0) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="moveFolderError" class="hidden p-2.5 rounded-lg bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-[11px] font-semibold"></div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeMoveModal()" class="px-3.5 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="moveFolderSubmitBtn" class="px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-sm transition-colors cursor-pointer">
                    Move Certificates
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let moveTargetCertIds = [];

    async function callFoldersApi(payload) {
        const res = await fetch('api/folders.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.CSRF_TOKEN || ''
            },
            body: JSON.stringify(Object.assign({}, payload, { csrf_token: window.CSRF_TOKEN || '' }))
        });
        const data = await res.json();
        if (!res.ok || data.status !== 'success') {
            throw new Error(data.message || 'Folder operation failed.');
        }
        return data;
    }

    function openFolderModal(mode, folderId = null, currentName = '', currentColor = 'blue') {
        const modal = document.getElementById('folderModal');
        const titleEl = document.getElementById('folderModalTitle');
        const modeEl = document.getElementById('folderModalMode');
        const idEl = document.getElementById('folderModalId');
        const nameEl = document.getElementById('folderModalName');
        const colorWrap = document.getElementById('folderColorPickerWrap');
        const submitBtn = document.getElementById('folderModalSubmitBtn');
        const errEl = document.getElementById('folderModalError');

        if (errEl) { errEl.classList.add('hidden'); errEl.textContent = ''; }
        modeEl.value = mode;
        idEl.value = folderId ? String(folderId) : '';
        nameEl.value = currentName || '';

        if (mode === 'rename') {
            titleEl.innerHTML = '<i class="fa-solid fa-pen text-blue-600 dark:text-blue-400"></i><span>Rename Folder</span>';
            submitBtn.textContent = 'Save Name';
            if (colorWrap) colorWrap.classList.add('hidden');
        } else {
            titleEl.innerHTML = '<i class="fa-solid fa-folder-plus text-blue-600 dark:text-blue-400"></i><span>New Virtual Folder</span>';
            submitBtn.textContent = 'Create Folder';
            if (colorWrap) colorWrap.classList.remove('hidden');
            const colorRadio = document.querySelector(`input[name="folder_color"][value="${currentColor || 'blue'}"]`);
            if (colorRadio) colorRadio.checked = true;
        }

        modal.classList.remove('hidden');
        setTimeout(() => nameEl.focus(), 50);
    }

    function closeFolderModal() {
        const modal = document.getElementById('folderModal');
        if (modal) modal.classList.add('hidden');
    }

    async function submitFolderModal(event) {
        event.preventDefault();
        const mode = document.getElementById('folderModalMode').value;
        const folderId = parseInt(document.getElementById('folderModalId').value || '0', 10);
        const name = (document.getElementById('folderModalName').value || '').trim();
        const colorInput = document.querySelector('input[name="folder_color"]:checked');
        const color = colorInput ? colorInput.value : 'blue';
        const errEl = document.getElementById('folderModalError');
        const submitBtn = document.getElementById('folderModalSubmitBtn');

        if (!name) {
            errEl.textContent = 'Please enter a folder name.';
            errEl.classList.remove('hidden');
            return;
        }

        submitBtn.disabled = true;
        try {
            if (mode === 'rename') {
                await callFoldersApi({ action: 'rename', folder_id: folderId, name });
            } else {
                await callFoldersApi({ action: 'create', name, color });
            }
            window.location.reload();
        } catch (err) {
            errEl.textContent = err.message;
            errEl.classList.remove('hidden');
            submitBtn.disabled = false;
        }
    }

    function confirmDeleteFolder(folderId, folderName, itemCount) {
        const safeName = escapeDocHtml(folderName);
        const note = itemCount > 0
            ? `The <strong>${itemCount} certificate(s)</strong> inside will be safely moved to <strong>Unfiled</strong> (no files are deleted).`
            : 'This empty folder will be removed.';
        showSystemConfirm({
            title: 'Delete Virtual Folder?',
            message: `Delete folder <strong>"${safeName}"</strong>? ${note}`,
            type: 'warning',
            confirmText: 'Delete Folder',
            cancelText: 'Cancel',
            onConfirm: async function() {
                try {
                    await callFoldersApi({ action: 'delete', folder_id: folderId });
                    window.location.reload();
                } catch (err) {
                    showSystemAlert({
                        title: 'Folder Deletion Failed',
                        message: err.message,
                        type: 'error',
                        buttonText: 'OK'
                    });
                }
            }
        });
    }

    function openMoveModal(certIds, label = '', currentFolderId = null) {
        moveTargetCertIds = Array.isArray(certIds) ? certIds.map(id => parseInt(id, 10)).filter(Boolean) : [];
        if (moveTargetCertIds.length === 0) return;

        const modal = document.getElementById('moveFolderModal');
        const subtitle = document.getElementById('moveFolderSubtitle');
        const select = document.getElementById('moveTargetFolderSelect');
        const errEl = document.getElementById('moveFolderError');

        if (errEl) { errEl.classList.add('hidden'); errEl.textContent = ''; }
        if (moveTargetCertIds.length === 1 && label) {
            subtitle.innerHTML = `Select destination folder for <strong>"${escapeDocHtml(label)}"</strong>.`;
        } else {
            subtitle.innerHTML = `Move <strong>${moveTargetCertIds.length} selected certificate(s)</strong> to:`;
        }
        select.value = currentFolderId ? String(currentFolderId) : '';
        modal.classList.remove('hidden');
    }

    function openBulkMoveModal() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        openMoveModal(ids);
    }

    function closeMoveModal() {
        const modal = document.getElementById('moveFolderModal');
        if (modal) modal.classList.add('hidden');
        moveTargetCertIds = [];
    }

    async function submitMoveModal(event) {
        event.preventDefault();
        if (moveTargetCertIds.length === 0) return;

        const select = document.getElementById('moveTargetFolderSelect');
        const errEl = document.getElementById('moveFolderError');
        const submitBtn = document.getElementById('moveFolderSubmitBtn');
        const rawVal = select.value;
        const folderId = rawVal ? parseInt(rawVal, 10) : null;

        submitBtn.disabled = true;
        try {
            await callFoldersApi({
                action: 'move_certificates',
                certificate_ids: moveTargetCertIds,
                folder_id: folderId
            });
            window.location.reload();
        } catch (err) {
            errEl.textContent = err.message;
            errEl.classList.remove('hidden');
            submitBtn.disabled = false;
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/layout_end.php'; ?>
