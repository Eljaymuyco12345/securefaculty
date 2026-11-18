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

        <!-- Search & Filter Controls matching Figure 4.3 -->
        <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/60 flex flex-wrap items-center gap-2.5 text-sm">
            <div class="flex-1 min-w-[200px] relative">
                <i class="fa-solid fa-magnifying-glass text-xs text-slate-400 absolute left-3 top-3"></i>
                <input type="text" id="dashboardSearchInput" onkeyup="applyDashboardLiveFilter()"
                       placeholder="Filter by keyword, actor, record, or IP..."
                       class="w-full pl-8 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-all">
            </div>
            <select id="recordStatusSelect" onchange="applyDashboardLiveFilter()"
                    class="py-2 px-3.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 cursor-pointer focus:outline-none focus:border-blue-500 font-medium">
                <option value="ALL">All Records</option>
                <option value="APPROVED">Approved Actions</option>
                <option value="REJECTED">Rejected Actions</option>
                <option value="REPORT">Audit Reports</option>
            </select>
        </div>

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
                    <?php foreach ($displayRows as $row): ?>
                        <?php
                            $uid = ($row['role'] ?? 'faculty') === 'dean'
                                ? 'ISUFST001'
                                : 'ISUFST' . str_pad($row['user_id'] ?? 2, 3, '0', STR_PAD_LEFT);

                            $actionRaw = $row['action'] ?? '';
                            $actionLabel = ucwords(strtolower(str_replace('_', ' ', $actionRaw)));
                            $faculty = $row['faculty_display'] ?? 'CICI Faculty';
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
                </tbody>
            </table>
            <!-- Empty state for search filters -->
            <div id="noMatchingRowsNotice" class="hidden p-8 text-center text-slate-400 dark:text-slate-500 text-sm">
                <i class="fa-regular fa-folder-open text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                No audit transactions match the specified filter criteria.
            </div>
        </div>

        <!-- Table Footer matching Figure 4.3 -->
        <div class="p-2.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex flex-wrap items-center justify-between gap-2 text-[10px] text-slate-500 dark:text-slate-400">
            <div>
                <span class="font-semibold text-slate-700 dark:text-slate-200" id="rowCountDisplay"><?= $totalRows ?> records</span>
                <span class="ml-1">Traceable Accountability: RA 10173 and DPA Compliance</span>
            </div>

            <!-- Pagination matching Figure 4.3 -->
            <div class="flex items-center gap-0.5 font-mono">
                <?php
                    $actionParam = !empty($actionFilter) ? '&action=' . urlencode($actionFilter) : '';
                    $searchParam = $search ? '&search=' . urlencode($search) : '';
                    $pageParam = $searchParam . $actionParam;
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
                        <p class="text-[10px] text-slate-400">Filter table records in real-time</p>
                    </div>
                </div>
                <button type="button" onclick="clearAdvancedFilters()" title="Reset all search filters"
                        class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 cursor-pointer">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                    <span>Reset</span>
                </button>
            </div>

            <!-- Filter Step 1: Faculty Member / Actor -->
            <div>
                <label for="advFacultySelect" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-tie text-indigo-500 text-xs"></i>
                    <span>1. Faculty Member / Actor:</span>
                </label>
                <select id="advFacultySelect" onchange="applyDashboardLiveFilter()"
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-800 dark:text-slate-100 font-medium focus:outline-none focus:border-blue-500">
                    <option value="">-- All Faculty Members &amp; System Actors --</option>
                    <?php
                        $uniqueFaculties = [];
                        foreach ($displayRows as $r) {
                            $fn = trim($r['faculty_display'] ?? '');
                            if ($fn !== '' && !in_array($fn, $uniqueFaculties, true)) {
                                $uniqueFaculties[] = $fn;
                            }
                        }
                        foreach ($uniqueFaculties as $fac):
                    ?>
                        <option value="<?= htmlspecialchars($fac) ?>"><?= htmlspecialchars($fac) ?></option>
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
                    <button type="button" onclick="toggleActionTag(this, '')" data-act=""
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400">
                        <i class="fa-solid fa-list-check text-[10px]"></i>
                        <span class="truncate">All Events</span>
                    </button>
                    <button type="button" onclick="toggleActionTag(this, 'LOGIN')" data-act="LOGIN"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-key text-[10px] text-emerald-500"></i>
                        <span class="truncate">Logins</span>
                    </button>
                    <button type="button" onclick="toggleActionTag(this, 'VIEW')" data-act="VIEW"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-eye text-[10px] text-blue-500"></i>
                        <span class="truncate">Document Views</span>
                    </button>
                    <button type="button" onclick="toggleActionTag(this, 'DOWNLOAD')" data-act="DOWNLOAD"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-download text-[10px] text-indigo-500"></i>
                        <span class="truncate">Downloads</span>
                    </button>
                    <button type="button" onclick="toggleActionTag(this, 'OCR')" data-act="OCR"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-cloud-arrow-up text-[10px] text-amber-500"></i>
                        <span class="truncate">OCR Ingests</span>
                    </button>
                    <button type="button" onclick="toggleActionTag(this, 'EXPORT')" data-act="EXPORT"
                            class="action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">
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
                    <button type="button" onclick="toggleCategoryPill(this, '')" data-cat=""
                            class="cat-pill px-2.5 py-1 rounded-md text-xs font-semibold border transition-all bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400">
                        All
                    </button>
                    <?php foreach (CategoryService::CATEGORIES as $ckey => $cinfo): ?>
                        <button type="button" onclick="toggleCategoryPill(this, '<?= htmlspecialchars($ckey) ?>')" data-cat="<?= htmlspecialchars($ckey) ?>"
                                class="cat-pill px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all flex items-center gap-1">
                            <i class="<?= $cinfo['icon'] ?> text-[10px]"></i>
                            <span><?= htmlspecialchars($cinfo['short_label']) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Active Status Feedback Banner -->
            <div class="p-2.5 rounded-lg bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/70 dark:border-blue-900/50 flex items-center justify-between text-xs">
                <span class="text-slate-600 dark:text-slate-300 font-medium">
                    Showing: <strong id="activeFilterSummary" class="text-blue-700 dark:text-blue-300">All Transactions</strong>
                </span>
                <span id="rowCountDisplay" class="font-mono font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-800">
                    <?= count($displayRows) ?> records
                </span>
            </div>

            <p class="text-[11px] text-slate-400 dark:text-slate-500 italic">
                <i class="fa-solid fa-circle-info mr-1"></i> The table on the left updates automatically as you click any filter above.
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
    let selectedCategoryTag = '';
    let selectedActionTag = '';

    function toggleActionTag(btn, action) {
        selectedActionTag = action;
        document.querySelectorAll('.action-pill').forEach(b => {
            b.className = 'action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700';
        });
        btn.className = 'action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400';
        applyDashboardLiveFilter();
    }

    function toggleCategoryPill(btn, cat) {
        selectedCategoryTag = cat;
        document.querySelectorAll('.cat-pill').forEach(b => {
            b.className = 'cat-pill px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all flex items-center gap-1';
        });
        btn.className = 'cat-pill px-2.5 py-1 rounded-md text-xs font-semibold border transition-all bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400 flex items-center gap-1';
        applyDashboardLiveFilter();
    }

    function clearAdvancedFilters() {
        selectedCategoryTag = '';
        selectedActionTag = '';
        const facSelect = document.getElementById('advFacultySelect');
        if (facSelect) facSelect.value = '';
        const statusSelect = document.getElementById('recordStatusSelect');
        if (statusSelect) statusSelect.value = 'ALL';
        const searchInput = document.getElementById('dashboardSearchInput');
        if (searchInput) searchInput.value = '';

        document.querySelectorAll('.action-pill').forEach((b, idx) => {
            if (idx === 0) {
                b.className = 'action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400';
            } else {
                b.className = 'action-pill w-full text-left px-2.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-all bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700';
            }
        });

        document.querySelectorAll('.cat-pill').forEach((b, idx) => {
            if (idx === 0) {
                b.className = 'cat-pill px-2.5 py-1 rounded-md text-xs font-semibold border transition-all bg-blue-100 dark:bg-blue-950/70 border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-200 ring-1 ring-blue-400 flex items-center gap-1';
            } else {
                b.className = 'cat-pill px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-all flex items-center gap-1';
            }
        });

        applyDashboardLiveFilter();
    }

    function applyDashboardLiveFilter() {
        const query = (document.getElementById('dashboardSearchInput')?.value || '').toLowerCase();
        const status = document.getElementById('recordStatusSelect')?.value || 'ALL';
        const advFaculty = (document.getElementById('advFacultySelect')?.value || '').toLowerCase();

        const rows = document.querySelectorAll('#dashboardTableBody .table-row-item');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowText = (row.dataset.text || '').toLowerCase();
            const action = (row.dataset.action || '').toUpperCase();

            let match = true;

            // Search query filter
            if (query && !rowText.includes(query)) match = false;

            // Faculty filter
            if (advFaculty && !rowText.includes(advFaculty)) match = false;

            // Dropdown Status filter
            if (status !== 'ALL') {
                if (status === 'APPROVED' && !action.includes('APPROV')) match = false;
                if (status === 'REJECTED' && !action.includes('REJECT')) match = false;
                if (status === 'REPORT' && !action.includes('REPORT') && !action.includes('EXPORT')) match = false;
            }

            // Action tag filter
            if (selectedActionTag) {
                if (!action.includes(selectedActionTag.toUpperCase())) match = false;
            }

            // Category tag filter
            if (selectedCategoryTag) {
                if (!rowText.includes(selectedCategoryTag.toLowerCase())) match = false;
            }

            if (match) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update counts and summaries
        const rowCountBadge = document.getElementById('rowCountDisplay');
        if (rowCountBadge) rowCountBadge.textContent = `${visibleCount} records`;

        const activeSummary = document.getElementById('activeFilterSummary');
        if (activeSummary) {
            const parts = [];
            if (advFaculty) parts.push(advFaculty);
            if (selectedActionTag) parts.push(selectedActionTag);
            if (selectedCategoryTag) parts.push(selectedCategoryTag);
            if (query) parts.push(`"${query}"`);
            activeSummary.textContent = parts.length > 0 ? parts.join(' • ') : 'All Transactions';
        }

        const notice = document.getElementById('noMatchingRowsNotice');
        if (notice) {
            if (visibleCount === 0) {
                notice.classList.remove('hidden');
            } else {
                notice.classList.add('hidden');
            }
        }
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

    // #17 Interactive Combobox Controller for Faculty Filter
    let activeComboboxIndex = -1;

    function openFacultyCombobox() {
        const listbox = document.getElementById('advFacultyListbox');
        const input = document.getElementById('advFacultyName');
        if (!listbox || !input) return;
        listbox.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
        filterFacultyCombobox(input.value);
    }

    function closeFacultyCombobox() {
        const listbox = document.getElementById('advFacultyListbox');
        const input = document.getElementById('advFacultyName');
        if (!listbox || !input) return;
        listbox.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
        activeComboboxIndex = -1;
    }

    function toggleFacultyCombobox() {
        const listbox = document.getElementById('advFacultyListbox');
        if (!listbox) return;
        if (listbox.classList.contains('hidden')) {
            openFacultyCombobox();
            document.getElementById('advFacultyName')?.focus();
        } else {
            closeFacultyCombobox();
        }
    }

    function filterFacultyCombobox(val) {
        const listbox = document.getElementById('advFacultyListbox');
        if (!listbox) return;
        const q = (val || '').toLowerCase().trim();
        const opts = Array.from(listbox.querySelectorAll('.combobox-option'));
        let anyVisible = false;
        opts.forEach(opt => {
            const text = (opt.dataset.value || '').toLowerCase();
            const isMatch = !q || text.includes(q);
            opt.style.display = isMatch ? '' : 'none';
            const check = opt.querySelector('.option-check');
            if (check) {
                check.classList.toggle('hidden', text !== q || !q);
            }
            if (isMatch) anyVisible = true;
        });
        activeComboboxIndex = -1;
        listbox.classList.toggle('hidden', !anyVisible);
    }

    function selectFacultyComboboxOption(val) {
        const input = document.getElementById('advFacultyName');
        if (!input) return;
        input.value = val;
        closeFacultyCombobox();
        applyDashboardLiveFilter();
    }

    function handleFacultyComboboxKey(e) {
        const listbox = document.getElementById('advFacultyListbox');
        if (!listbox) return;
        const visibleOpts = Array.from(listbox.querySelectorAll('.combobox-option')).filter(o => o.style.display !== 'none');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (listbox.classList.contains('hidden')) openFacultyCombobox();
            activeComboboxIndex = Math.min(activeComboboxIndex + 1, visibleOpts.length - 1);
            highlightComboboxOption(visibleOpts);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeComboboxIndex = Math.max(activeComboboxIndex - 1, 0);
            highlightComboboxOption(visibleOpts);
        } else if (e.key === 'Enter' && activeComboboxIndex >= 0 && visibleOpts[activeComboboxIndex]) {
            e.preventDefault();
            selectFacultyComboboxOption(visibleOpts[activeComboboxIndex].dataset.value);
        } else if (e.key === 'Escape') {
            closeFacultyCombobox();
        }
    }

    function highlightComboboxOption(visibleOpts) {
        visibleOpts.forEach((opt, i) => {
            if (i === activeComboboxIndex) {
                opt.classList.add('bg-blue-50', 'dark:bg-blue-950/60', 'font-bold');
                opt.scrollIntoView({ block: 'nearest' });
            } else {
                opt.classList.remove('bg-blue-50', 'dark:bg-blue-950/60', 'font-bold');
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#advFacultyComboboxWrapper')) {
            closeFacultyCombobox();
        }
    });
</script>

<?php require_once __DIR__ . '/../layouts/layout_end.php'; ?>
