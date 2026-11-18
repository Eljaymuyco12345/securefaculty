<?php
$conversionCount = 0;
$tempDir = TEMP_DIR;
if (is_dir($tempDir)) {
    $conversionCount = count(array_diff(scandir($tempDir), ['.', '..', '.gitkeep', '.htaccess']));
}
$activeNav = $activeNav ?? 'dashboard';
$pageTitle = $pageTitle ?? "Dean's Dashboard - Quota Monitor";
$quota = $quota ?? ['current_usage' => 0, 'monthly_limit' => 25000, 'percentage' => 0, 'remaining' => 25000];
$metrics = $metrics ?? ['total_certificates' => 0, 'total_faculty' => 0, 'total_storage_mb' => 0];
$displayRows = $displayRows ?? [];
$totalRows = $totalRows ?? count($displayRows);
$totalPages = $totalPages ?? 1;
$page = $page ?? 1;
$search = $search ?? '';
$actionFilter = $actionFilter ?? '';

require_once __DIR__ . '/../layouts/layout_start.php';
?>

<!-- Title Banner matching Figure 4.3 -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">Dean's Dashboard</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Centralized Administrative Oversight & Real-Time Cloud Quota Monitor</p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="backup.php"
           title="Plan B: Generate and download an instant database snapshot for disaster recovery"
           class="px-3.5 py-2 rounded-lg border border-emerald-300 dark:border-emerald-800/80 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-sm font-bold transition-all shadow-sm flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-database text-emerald-600 dark:text-emerald-400"></i>
            <span>Plan B DB Backup</span>
        </a>
        <a href="users.php"
           class="px-3.5 py-2 rounded-lg border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-sm font-bold transition-all shadow-sm flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-users-gear"></i>
            <span>User Management</span>
        </a>
        <a href="export.php"
           class="px-4 py-2 rounded-lg bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white text-sm font-bold transition-all shadow-md shadow-rose-600/25 ring-2 ring-rose-400/30 hover:ring-rose-300 flex items-center gap-2 cursor-pointer transform hover:-translate-y-0.5 active:translate-y-0">
            <i class="fa-solid fa-file-pdf text-sm"></i>
            <span>Accreditation PDF Ledger</span>
        </a>
        <button type="button" onclick="resetDashboardFilters()"
                class="px-4 py-2 rounded-lg border border-[#0d2342] dark:border-slate-700 bg-[#0d2342] dark:bg-slate-800 text-sm font-bold text-white hover:bg-[#14335e] dark:hover:bg-slate-700 transition-all shadow-sm flex items-center gap-2 cursor-pointer">
            <i class="fa-solid fa-rotate-left text-sm"></i>
            <span>Reset Dashboard</span>
        </button>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
<div class="mt-4 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm flex items-center justify-between shadow-sm">
    <div class="flex items-center gap-2.5">
        <i class="fa-solid fa-circle-exclamation text-rose-600 dark:text-rose-400 text-base"></i>
        <div>
            <span class="font-bold">System / Backup Alert:</span>
            <span><?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>
    <a href="dashboard.php" class="text-rose-600 dark:text-rose-400 hover:underline font-bold text-xs px-2.5 py-1 rounded border border-rose-300 dark:border-rose-700 bg-white dark:bg-rose-900/40">Dismiss</a>
</div>
<?php endif; ?>

<!-- Permanent Institutional Department Metrics Banner -->
<div id="institutionalOverviewPanel">
    <div class="bg-gradient-to-r from-[#0d2342] to-[#1e447b] dark:from-[#09152a] dark:to-[#132c54] text-white rounded-xl p-4 sm:p-5 shadow-sm border border-transparent dark:border-slate-800">
        <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <div>
                <h3 class="text-sm sm:text-base font-extrabold tracking-tight">ISUFST CICI Institutional Repository Telemetry</h3>
                <p class="text-xs text-blue-200 mt-0.5">Accreditation Readiness Overview & Storage Allocation</p>
            </div>
            <span class="inline-flex items-center gap-2 text-xs bg-white/10 text-blue-200 px-3 py-1 rounded-full font-mono border border-white/10">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-semibold">Live Repository Status</span>
            </span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3.5 text-sm">
            <div class="stagger-item bg-white/10 rounded-xl p-3" style="--stagger-i: 0">
                <span class="text-blue-200 text-[11px] uppercase font-bold tracking-wider block">Active Faculty</span>
                <strong class="text-lg sm:text-xl font-black tabular-nums block mt-0.5"><?= $metrics['total_faculty'] ?> Members</strong>
                <span class="text-xs text-emerald-300 flex items-center gap-1.5 mt-1 font-medium"><i class="fa-solid fa-check"></i> 100% RBAC Covered</span>
            </div>
            <div class="stagger-item bg-white/10 rounded-xl p-3" style="--stagger-i: 1">
                <span class="text-blue-200 text-[11px] uppercase font-bold tracking-wider block">Archived Assets</span>
                <strong class="text-lg sm:text-xl font-black tabular-nums block mt-0.5"><?= $metrics['total_records'] ?> Records</strong>
                <span class="text-xs text-blue-300 mt-1 block font-medium">AES-256 Encrypted</span>
            </div>
            <div class="stagger-item bg-white/10 rounded-xl p-3" style="--stagger-i: 2">
                <span class="text-blue-200 text-[11px] uppercase font-bold tracking-wider block">Encrypted Storage</span>
                <strong class="text-lg sm:text-xl font-black tabular-nums block mt-0.5"><?= $metrics['vault_size_mb'] ?> MB</strong>
                <span class="text-xs text-blue-300 mt-1 block font-medium">Volatile RAM Decryption</span>
            </div>
            <div class="stagger-item bg-white/10 rounded-xl p-3" style="--stagger-i: 3">
                <span class="text-blue-200 text-[11px] uppercase font-bold tracking-wider block">OCR Cloud Quota</span>
                <strong class="text-lg sm:text-xl font-black tabular-nums block mt-0.5"><?= number_format($quota['remaining']) ?></strong>
                <span class="text-xs text-emerald-300 tabular-nums mt-1 block font-medium"><?= $quota['percentage'] ?>% Used / 25,000</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Grid Layout (Table Left, Widgets Right) matching Figure 4.3 -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-4">

    <!-- LEFT: Central Audit Ledger Table (8 cols) -->
    <div class="xl:col-span-8 bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden transition-colors">

        <!-- Sub-header matching Figure 4.3 -->
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                <span>REAL-TIME SYSTEM QUOTA MONITOR</span>
                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 uppercase">Live Sync</span>
            </h2>
            <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Data Sovereignty: Original Records Remain On-Premise
            </span>
        </div>

        <!-- Search & Filter Controls: Full Database Server-Side Search -->
        <form id="dashboardSearchForm" method="GET" action="dashboard.php" class="p-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/60 flex flex-wrap items-center gap-2.5 text-sm">
            <input type="hidden" name="action" value="<?= htmlspecialchars($actionFilter) ?>">
            <input type="hidden" name="faculty_id" value="<?= htmlspecialchars((string)($facultyId ?? '')) ?>">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? '') ?>">
            <div class="flex-1 min-w-[200px] relative">
                <i class="fa-solid fa-magnifying-glass text-xs text-slate-400 absolute left-3 top-3"></i>
                <input type="text" name="search" id="dashboardSearchInput" value="<?= htmlspecialchars($search) ?>"
                       placeholder="Filter across entire database by keyword, faculty, record, or IP..."
                       class="w-full pl-8 pr-8 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-all">
                <?php if (!empty($search)): ?>
                    <a href="dashboard.php?action=<?= urlencode($actionFilter) ?>&faculty_id=<?= urlencode((string)($facultyId ?? '')) ?>&category=<?= urlencode($category ?? '') ?>"
                       class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="Clear keyword search">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </a>
                <?php endif; ?>
            </div>
            <button type="submit" class="px-3.5 py-2 bg-[#0d2342] dark:bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-[#14335e] dark:hover:bg-blue-500 transition-colors flex items-center gap-1.5 cursor-pointer shadow-sm">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                <span>Search</span>
            </button>
        </form>

        <!-- Data Table matching Figure 4.3 (5 columns, NO Status) -->
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left text-sm border-collapse" id="dashboardTable">
                <thead>
                    <tr class="bg-slate-100/80 dark:bg-slate-900/90 text-slate-700 dark:text-slate-200 font-extrabold text-xs uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 px-3.5 whitespace-nowrap cursor-pointer hover:text-blue-600 dark:hover:text-blue-400" onclick="sortTableByTimestamp()">
                            Timestamp
                            <i class="fa-solid fa-sort text-[10px] text-slate-400 ml-1"></i>
                        </th>
                        <th class="py-3 px-3.5">User ID</th>
                        <th class="py-3 px-3.5">Action Taken</th>
                        <th class="py-3 px-3.5">Affected Faculty Record</th>
                        <th class="py-3 px-3.5">Source IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm" id="dashboardTableBody">
                    <?php if (empty($displayRows)): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-regular fa-folder-open text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                                <span>No audit transactions match the specified filter criteria across the database.</span>
                                <div class="mt-3">
                                    <a href="dashboard.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 text-xs font-bold hover:underline">
                                        <i class="fa-solid fa-arrows-rotate text-xs"></i>
                                        <span>Refresh Ledger</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($displayRows as $row): ?>
                            <?php
                                $uid = ($row['role'] ?? 'faculty') === 'dean'
                                    ? 'ISUFST001'
                                    : 'ISUFST' . str_pad($row['user_id'] ?? 2, 3, '0', STR_PAD_LEFT);

                                $actionRaw = $row['action'] ?? '';
                                $actionLabel = ucwords(strtolower(str_replace('_', ' ', $actionRaw)));
                                $faculty = $row['faculty_display'] ?? 'System / Institutional';
                            ?>
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors table-row-item"
                                data-action="<?= strtoupper($actionRaw) ?>"
                                data-text="<?= strtolower($uid . ' ' . $actionLabel . ' ' . $faculty . ' ' . ($row['ip_address'] ?? '')) ?>">
                                <td class="py-2.5 px-3.5 text-slate-600 dark:text-slate-400 font-mono text-xs whitespace-nowrap"><?= htmlspecialchars($row['created_at']) ?></td>
                                <td class="py-2.5 px-3.5 font-bold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($uid) ?></td>
                                <td class="py-2.5 px-3.5 text-slate-800 dark:text-slate-200 font-semibold"><?= htmlspecialchars($actionLabel) ?></td>
                                <td class="py-2.5 px-3.5 text-slate-700 dark:text-slate-300 font-medium"><?= htmlspecialchars($faculty) ?></td>
                                <td class="py-2.5 px-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs"><?= htmlspecialchars($row['ip_address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer matching Figure 4.3 -->
        <div class="p-2.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex flex-wrap items-center justify-between gap-2 text-[10px] text-slate-500 dark:text-slate-400">
            <div>
                <span class="font-semibold text-slate-700 dark:text-slate-200" id="rowCountDisplay"><?= number_format($totalRows) ?> records</span>
                <span class="ml-1">Traceable Accountability: RA 10173 and DPA Compliance</span>
                <?php if ($totalPages > 1): ?>
                    <span class="ml-1 text-slate-400">(Page <?= $page ?> of <?= $totalPages ?>)</span>
                <?php endif; ?>
            </div>

            <!-- Pagination matching Figure 4.3 -->
            <div class="flex items-center gap-0.5 font-mono">
                <?php
                    $actionParam = !empty($actionFilter) ? '&action=' . urlencode($actionFilter) : '';
                    $searchParam = $search !== '' ? '&search=' . urlencode($search) : '';
                    $facParam = $facultyId ? '&faculty_id=' . urlencode((string)$facultyId) : '';
                    $catParam = !empty($category) ? '&category=' . urlencode($category) : '';
                    $pageParam = $searchParam . $actionParam . $facParam . $catParam;

                    $startPage = max(1, min($page - 1, max(1, $totalPages - 3)));
                    $endPage = min($totalPages, max(1, $startPage + 3));
                    if ($endPage - $startPage < 3 && $startPage > 1) {
                        $startPage = max(1, $endPage - 3);
                    }
                ?>
                <?php if ($page > 1): ?>
                    <a href="dashboard.php?page=<?= $page - 1 ?><?= $pageParam ?>"
                       class="pagination-btn px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer"
                       title="Previous Page">&lt;</a>
                <?php endif; ?>
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="dashboard.php?page=<?= $i ?><?= $pageParam ?>"
                       class="pagination-btn px-1.5 py-0.5 rounded <?= $i === $page ? 'bg-blue-600 text-white font-bold' : 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?> cursor-pointer"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="dashboard.php?page=<?= $page + 1 ?><?= $pageParam ?>"
                       class="pagination-btn px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer"
                       title="Next Page">&gt;</a>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Data Sovereignty: Original Records Remain On-Premise</span>
            </div>
        </div>

    </div>

    <!-- RIGHT: Widgets Column (4 cols) matching Figure 4.3 -->
    <div class="xl:col-span-4 space-y-4">

        <!-- QUOTA MONITOR WIDGET matching Figure 4.3 -->
        <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm relative overflow-hidden transition-colors">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-3">
                QUOTA MONITOR
            </h3>

            <!-- Gauge + Stats side by side matching Figure 4.3 -->
            <div class="flex items-center gap-4">
                <!-- Circular Gauge -->
                <div class="relative w-24 h-24 flex-shrink-0">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-100 dark:text-slate-800/80" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-[#1e58a8] dark:text-blue-500" stroke-dasharray="<?= min(100, max(5, $quota['percentage'])) ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-lg font-black text-slate-900 dark:text-slate-100"><?= $quota['percentage'] ?>%</span>
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 font-bold uppercase">API Usage</span>
                    </div>
                </div>

                <!-- Right-side stats text matching Figure 4.3 -->
                <div class="text-left space-y-1">
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-snug font-medium">
                        Real-Time Sync<br>with Cloud Engine<br>Quota Status
                    </p>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200">
                        API Quota Remaining:
                    </p>
                    <p class="text-sm font-black text-[#1e58a8] dark:text-blue-400">
                        <?= number_format($quota['remaining']) ?> / <?= number_format($quota['monthly_limit']) ?>
                        <i class="fa-solid fa-arrow-left text-[10px] text-slate-400 ml-1"></i>
                    </p>
                </div>
            </div>

            <!-- Green Secure Button -->
            <div class="mt-3">
                <button type="button" onclick="showQuotaSecureDetails()"
                        class="w-full py-2 px-3 rounded-full bg-[#1da055] hover:bg-[#188c49] text-white font-bold text-sm shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span>Quota Secure (Click to Inspect)</span>
                </button>
            </div>
        </div>

        <!-- ADVANCED SEARCH WIDGET (User-Friendly Filter Assistant) -->
        <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm text-sm space-y-3.5 transition-colors">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                            Ledger Filter Assistant
                        </h3>
                        <p class="text-[10px] text-slate-400">Search &amp; filter entire audit history across all pages</p>
                    </div>
                </div>
                <a href="dashboard.php" title="Refresh ledger and clear active filters"
                   class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-arrows-rotate text-[11px]"></i>
                    <span>Refresh</span>
                </a>
            </div>

            <!-- Filter Step 1: Faculty Member / Actor -->
            <div>
                <label for="advFacultySelect" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-tie text-indigo-500 text-xs"></i>
                    <span>1. Faculty Member / Actor:</span>
                </label>
                <select id="advFacultySelect" onchange="submitDashboardFilter({faculty_id: this.value})"
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-slate-100 font-medium focus:outline-none focus:border-blue-500">
                    <option value="">-- All Faculty Members &amp; System Actors --</option>
                    <?php foreach ($facultyList as $fac): ?>
                        <option value="<?= $fac['id'] ?>" <?= ($facultyId === (int)$fac['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($fac['full_name']) ?> (<?= htmlspecialchars($fac['role'] ?? 'faculty') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Step 2: Event Type / Action -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-bolt text-amber-500 text-xs"></i>
                    <span>2. Transaction Event Type:</span>
                </label>
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" onclick="submitDashboardFilter({action: ''})"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all <?= empty($actionFilter) ? 'bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        <i class="fa-solid fa-list-check text-[10px]"></i>
                        <span class="truncate">All Events</span>
                    </button>
                    <button type="button" onclick="submitDashboardFilter({action: 'LOGIN'})"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all <?= ($actionFilter === 'LOGIN') ? 'bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        <i class="fa-solid fa-key text-[10px] text-emerald-500"></i>
                        <span class="truncate">Logins</span>
                    </button>
                    <button type="button" onclick="submitDashboardFilter({action: 'VIEW_DOCUMENT'})"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all <?= ($actionFilter === 'VIEW_DOCUMENT') ? 'bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        <i class="fa-solid fa-eye text-[10px] text-blue-500"></i>
                        <span class="truncate">Document Views</span>
                    </button>
                    <button type="button" onclick="submitDashboardFilter({action: 'DOWNLOAD_DOCUMENT'})"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all <?= ($actionFilter === 'DOWNLOAD_DOCUMENT') ? 'bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        <i class="fa-solid fa-download text-[10px] text-indigo-500"></i>
                        <span class="truncate">Downloads</span>
                    </button>
                    <button type="button" onclick="submitDashboardFilter({action: 'OCR_EXTRACT'})"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all <?= ($actionFilter === 'OCR_EXTRACT') ? 'bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        <i class="fa-solid fa-cloud-arrow-up text-[10px] text-amber-500"></i>
                        <span class="truncate">OCR Ingests</span>
                    </button>
                    <button type="button" onclick="submitDashboardFilter({action: 'EXPORT_REPORT'})"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all <?= ($actionFilter === 'EXPORT_REPORT') ? 'bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        <i class="fa-solid fa-file-pdf text-[10px] text-rose-500"></i>
                        <span class="truncate">PDF Reports</span>
                    </button>
                </div>
            </div>

            <!-- Filter Step 3: Academic Category Focus -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-layer-group text-blue-500 text-xs"></i>
                    <span>3. Academic Category Focus:</span>
                </label>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="submitDashboardFilter({category: ''})"
                            class="cat-pill px-2.5 py-1 rounded-md text-xs font-semibold border transition-all <?= empty($category) ? 'bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                        All
                    </button>
                    <?php foreach (CategoryService::CATEGORIES as $ckey => $cinfo): ?>
                        <button type="button" onclick="submitDashboardFilter({category: '<?= htmlspecialchars($ckey) ?>'})"
                                class="cat-pill px-2.5 py-1 rounded-md text-xs font-medium border transition-all flex items-center gap-1 <?= ($category === $ckey) ? 'bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400 font-semibold' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">
                            <i class="<?= $cinfo['icon'] ?> text-[10px]"></i>
                            <span><?= htmlspecialchars($cinfo['short_label']) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Active Status Feedback Banner -->
            <?php
                $filterTitle = 'All Transactions';
                if (!empty($actionFilter)) {
                    $filterTitle = match($actionFilter) {
                        'LOGIN' => 'Logins',
                        'VIEW_DOCUMENT' => 'Views',
                        'DOWNLOAD_DOCUMENT' => 'Downloads',
                        'OCR_EXTRACT' => 'OCR Ingests',
                        'EXPORT_REPORT' => 'PDF Reports',
                        default => htmlspecialchars($actionFilter)
                    };
                }
                if (!empty($category)) {
                    $filterTitle .= ' • ' . (CategoryService::CATEGORIES[$category]['short_label'] ?? htmlspecialchars($category));
                }
                if ($facultyId) {
                    foreach ($facultyList as $f) {
                        if ((int)$f['id'] === $facultyId) {
                            $filterTitle .= ' • ' . htmlspecialchars($f['full_name']);
                            break;
                        }
                    }
                }
            ?>
            <div class="p-2.5 rounded-lg bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/70 dark:border-blue-900/50 flex items-center justify-between text-xs">
                <span class="text-slate-600 dark:text-slate-300 font-medium">
                    Active: <strong id="activeFilterSummary" class="text-blue-700 dark:text-blue-300"><?= $filterTitle ?></strong>
                </span>
                <span id="rowCountDisplay" class="font-mono font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-800">
                    <?= number_format($totalRows) ?> in database
                </span>
            </div>

            <p class="text-[11px] text-slate-400 dark:text-slate-500 italic">
                <i class="fa-solid fa-circle-info mr-1"></i> Filtering queries all historical transactions across the entire university database.
            </p>
        </div>

        <!-- CONVERSION GATE ACTIVITY matching Figure 4.3 -->
        <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 shadow-sm transition-colors">
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="fa-solid fa-bolt text-base"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">Conversion Gate Activity</h4>
                        <p class="text-xs text-slate-400 dark:text-slate-400"><?= $conversionCount ?> file(s) currently staged</p>
                    </div>
                </div>
                <span class="text-xs font-mono text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded border border-emerald-200 dark:border-emerald-800">Optimal</span>
            </div>
        </div>

    </div>

</div>

<!-- Interactive Dashboard Scripts -->
<script>
    const currentFilterParams = {
        search: <?= json_encode($search) ?>,
        action: <?= json_encode($actionFilter) ?>,
        faculty_id: <?= json_encode((string)($facultyId ?? '')) ?>,
        category: <?= json_encode($category ?? '') ?>
    };

    function submitDashboardFilter(overrides) {
        overrides = overrides || {};
        const searchInput = document.getElementById('dashboardSearchInput');
        const facSelect = document.getElementById('advFacultySelect');

        const search = (overrides.search !== undefined) ? overrides.search : (searchInput ? searchInput.value.trim() : currentFilterParams.search);
        const action = (overrides.action !== undefined) ? overrides.action : currentFilterParams.action;
        const facultyId = (overrides.faculty_id !== undefined) ? overrides.faculty_id : (facSelect ? facSelect.value : currentFilterParams.faculty_id);
        const category = (overrides.category !== undefined) ? overrides.category : currentFilterParams.category;

        const params = new URLSearchParams();
        params.set('page', '1');
        if (search) params.set('search', search);
        if (action) params.set('action', action);
        if (facultyId) params.set('faculty_id', facultyId);
        if (category) params.set('category', category);

        window.location.href = 'dashboard.php?' + params.toString();
    }

    function toggleInstitutionalOverview() {
        const panel = document.getElementById('institutionalOverviewPanel');
        const text = document.getElementById('overviewToggleText');
        if (panel && text) {
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                text.textContent = 'Hide Overview';
            } else {
                panel.classList.add('hidden');
                text.textContent = 'Institutional Overview';
            }
        }
    }

    function resetDashboardFilters() {
        window.location.href = 'dashboard.php';
    }

    function showQuotaSecureDetails() {
        showSystemAlert({
            title: 'Cloud OCR API Quota Compliant',
            message: 'Real-time telemetry reports <?= number_format($quota['remaining']) ?> of <?= number_format($quota['monthly_limit']) ?> operations available this cycle (<?= $quota['percentage'] ?>% consumed). Original sensitive records remain safely stored on-premise.',
            type: 'success',
            buttonText: 'Close Telemetry'
        });
    }

    function sortTableByTimestamp() {
        const executeSort = function() {
            const tbody = document.getElementById('dashboardTableBody');
            const rows = Array.from(tbody.querySelectorAll('.table-row-item'));
            rows.reverse();
            rows.forEach(r => tbody.appendChild(r));
        };
        if (typeof runWithViewTransition === 'function') {
            runWithViewTransition(executeSort);
        } else {
            executeSort();
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/layout_end.php'; ?>
