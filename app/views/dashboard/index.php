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
        <h1 class="text-lg font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Dean's Dashboard</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Centralized Administrative Oversight & Real-Time Cloud Quota Monitor</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="users.php"
           class="px-3 py-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-users-gear"></i>
            <span>User Management</span>
        </a>
        <a href="export.php"
           class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-print text-blue-600 dark:text-blue-400"></i>
            <span>Accreditation Report</span>
        </a>
        <button type="button" onclick="resetDashboardFilters()"
                class="px-3.5 py-1.5 rounded-lg border border-[#0d2342] dark:border-slate-700 bg-[#0d2342] dark:bg-slate-800 text-xs font-bold text-white hover:bg-[#14335e] dark:hover:bg-slate-700 transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-rotate-left text-xs"></i>
            <span>Reset Dashboard</span>
        </button>
    </div>
</div>

<!-- Permanent Institutional Department Metrics Banner -->
<div id="institutionalOverviewPanel">
    <div class="bg-gradient-to-r from-[#0d2342] to-[#1e447b] dark:from-[#09152a] dark:to-[#132c54] text-white rounded-2xl p-5 shadow-md border border-transparent dark:border-slate-800">
        <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <div>
                <h3 class="text-sm font-bold tracking-tight">ISUFST CICI Institutional Repository Telemetry</h3>
                <p class="text-[11px] text-blue-200 mt-0.5">Accreditation Readiness Overview & Storage Allocation</p>
            </div>
            <span class="inline-flex items-center gap-1.5 text-[11px] bg-white/10 text-blue-200 px-3 py-1 rounded-full font-mono border border-white/10">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Live Repository Status</span>
            </span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
            <div class="bg-white/10 rounded-xl p-3">
                <span class="text-blue-200 text-[10px] uppercase font-bold block">Active Faculty</span>
                <strong class="text-xl font-black block mt-0.5"><?= $metrics['total_faculty'] ?> Members</strong>
                <span class="text-[10px] text-emerald-300 flex items-center gap-1 mt-1"><i class="fa-solid fa-check"></i> 100% RBAC Covered</span>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
                <span class="text-blue-200 text-[10px] uppercase font-bold block">Archived Assets</span>
                <strong class="text-xl font-black block mt-0.5"><?= $metrics['total_records'] ?> Records</strong>
                <span class="text-[10px] text-blue-300 mt-1 block">AES-256 Encrypted</span>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
                <span class="text-blue-200 text-[10px] uppercase font-bold block">Encrypted Storage</span>
                <strong class="text-xl font-black block mt-0.5"><?= $metrics['vault_size_mb'] ?> MB</strong>
                <span class="text-[10px] text-blue-300 mt-1 block">Volatile RAM Decryption</span>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
                <span class="text-blue-200 text-[10px] uppercase font-bold block">OCR Cloud Quota</span>
                <strong class="text-xl font-black block mt-0.5"><?= number_format($quota['remaining']) ?></strong>
                <span class="text-[10px] text-emerald-300 mt-1 block"><?= $quota['percentage'] ?>% Used / 25,000</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Grid Layout (Table Left, Widgets Right) matching Figure 4.3 -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-4">

    <!-- LEFT: Central Audit Ledger Table (8 cols) -->
    <div class="xl:col-span-8 bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col overflow-hidden transition-colors">

        <!-- Sub-header matching Figure 4.3 -->
        <div class="p-3 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xs font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                <span>REAL-TIME SYSTEM QUOTA MONITOR</span>
                <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 uppercase">Live Sync</span>
            </h2>
            <span class="inline-flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Data Sovereignty: Original Records Remain On-Premise
            </span>
        </div>

        <!-- Search & Filter Controls matching Figure 4.3 -->
        <div class="p-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/60 flex flex-wrap items-center gap-2 text-xs">
            <div class="flex-1 min-w-[180px] relative">
                <i class="fa-solid fa-magnifying-glass text-[10px] text-slate-400 absolute left-2.5 top-2.5"></i>
                <input type="text" id="dashboardSearchInput" onkeyup="applyDashboardLiveFilter()"
                       placeholder="Filter by keyword, actor, record, or IP..."
                       class="w-full pl-7 pr-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-all">
            </div>
            <select id="recordStatusSelect" onchange="applyDashboardLiveFilter()"
                    class="py-1.5 px-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-700 dark:text-slate-200 cursor-pointer focus:outline-none focus:border-blue-500">
                <option value="ALL">All Records</option>
                <option value="APPROVED">Approved Actions</option>
                <option value="REJECTED">Rejected Actions</option>
                <option value="REPORT">Audit Reports</option>
            </select>
        </div>

        <!-- Data Table matching Figure 4.3 (5 columns, NO Status) -->
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left text-xs border-collapse" id="dashboardTable">
                <thead>
                    <tr class="bg-slate-100/80 dark:bg-slate-900/90 text-slate-600 dark:text-slate-300 font-bold text-[11px] border-b border-slate-200 dark:border-slate-800">
                        <th class="py-2.5 px-3 whitespace-nowrap cursor-pointer hover:text-blue-600 dark:hover:text-blue-400" onclick="sortTableByTimestamp()">
                            Timestamp
                            <i class="fa-solid fa-sort text-[8px] text-slate-400 ml-0.5"></i>
                        </th>
                        <th class="py-2.5 px-3">User ID</th>
                        <th class="py-2.5 px-3">Action Taken</th>
                        <th class="py-2.5 px-3">Affected Faculty Record</th>
                        <th class="py-2.5 px-3">Source IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-[11px]" id="dashboardTableBody">
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
                            <td class="py-2 px-3 text-slate-600 dark:text-slate-400 font-mono whitespace-nowrap"><?= htmlspecialchars($row['created_at']) ?></td>
                            <td class="py-2 px-3 font-bold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($uid) ?></td>
                            <td class="py-2 px-3 text-slate-800 dark:text-slate-200 font-medium"><?= htmlspecialchars($actionLabel) ?></td>
                            <td class="py-2 px-3 text-slate-700 dark:text-slate-300"><?= htmlspecialchars($faculty) ?></td>
                            <td class="py-2 px-3 text-slate-500 dark:text-slate-400 font-mono"><?= htmlspecialchars($row['ip_address']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Empty state for search filters -->
            <div id="noMatchingRowsNotice" class="hidden p-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                <i class="fa-regular fa-folder-open text-2xl block mb-2 text-slate-300 dark:text-slate-600"></i>
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
                <?php if ($page > 1): ?>
                    <a href="dashboard.php?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                       class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">&lt;</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= min($totalPages, 4); $i++): ?>
                    <a href="dashboard.php?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                       class="px-1.5 py-0.5 rounded <?= $i === $page ? 'bg-blue-600 text-white font-bold' : 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' ?> cursor-pointer"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="dashboard.php?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                       class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer">&gt;</a>
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
            <h3 class="text-xs font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-3">
                QUOTA MONITOR
            </h3>

            <!-- Gauge + Stats side by side matching Figure 4.3 -->
            <div class="flex items-center gap-4">
                <!-- Circular Gauge -->
                <div class="relative w-28 h-28 flex-shrink-0">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-100 dark:text-slate-800/80" stroke-width="3.5" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-[#1e58a8] dark:text-blue-500" stroke-dasharray="<?= min(100, max(5, $quota['percentage'])) ?>, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xl font-black text-slate-900 dark:text-slate-100"><?= $quota['percentage'] ?>%</span>
                        <span class="text-[8px] text-slate-400 dark:text-slate-500 font-semibold uppercase">API Usage</span>
                    </div>
                </div>

                <!-- Right-side stats text matching Figure 4.3 -->
                <div class="text-left space-y-1.5">
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-snug font-medium">
                        Real-Time Sync<br>with Cloud Engine<br>Quota Status
                    </p>
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200">
                        API Quota Remaining:
                    </p>
                    <p class="text-xs font-black text-[#1e58a8] dark:text-blue-400">
                        <?= number_format($quota['remaining']) ?> / <?= number_format($quota['monthly_limit']) ?>
                        <i class="fa-solid fa-arrow-left text-[8px] text-slate-400 ml-0.5"></i>
                    </p>
                </div>
            </div>

            <!-- Green Secure Button -->
            <div class="mt-3">
                <button type="button" onclick="showQuotaSecureDetails()"
                        class="w-full py-2 px-3 rounded-full bg-[#1da055] hover:bg-[#188c49] text-white font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-check text-[11px]"></i>
                    <span>Quota Secure (Click to Inspect)</span>
                </button>
            </div>
        </div>

        <!-- ADVANCED SEARCH WIDGET matching Figure 4.3 -->
        <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm text-xs space-y-3 transition-colors">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-extrabold text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                    ADVANCED SEARCH
                </h3>
                <button type="button" onclick="clearAdvancedFilters()" class="text-[10px] text-blue-600 dark:text-blue-400 hover:underline">
                    Clear Tags
                </button>
            </div>

            <!-- Faculty Name -->
            <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Faculty Name</label>
                <input type="text" id="advFacultyName" placeholder="Autocomplete input (e.g. Dr. Aaliyah)"
                    class="w-full px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Date Range & Action -->
            <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Date Range & Action</label>
                <div class="flex items-center gap-2">
                    <input type="date" id="advDate" value="2026-05-07"
                        class="flex-1 px-2.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-blue-500">
                    <select id="advActionSelect" class="px-2 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:border-blue-500">
                        <option value="">Audit Action</option>
                        <option value="APPROVED">Approve</option>
                        <option value="REJECTED">Reject</option>
                        <option value="REPORT">Report</option>
                    </select>
                </div>
            </div>

            <!-- Two-column tag pills matching Figure 4.3 -->
            <div class="grid grid-cols-2 gap-x-3 gap-y-1">
                <div class="space-y-1">
                    <button type="button" onclick="toggleTag(this, 'cat')" data-val="Research"
                            class="adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Research Publication
                    </button>
                    <button type="button" onclick="toggleTag(this, 'cat')" data-val="Service"
                            class="adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Service
                    </button>
                    <button type="button" onclick="toggleTag(this, 'cat')" data-val="Training"
                            class="adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Training
                    </button>
                    <button type="button" onclick="toggleTag(this, 'cat')" data-val="Education"
                            class="adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Education
                    </button>
                </div>
                <div class="space-y-1">
                    <button type="button" onclick="toggleTag(this, 'act')" data-val="Approve"
                            class="adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Approve
                    </button>
                    <button type="button" onclick="toggleTag(this, 'act')" data-val="Reject"
                            class="adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Reject
                    </button>
                    <button type="button" onclick="toggleTag(this, 'act')" data-val="Create"
                            class="adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Create
                    </button>
                    <button type="button" onclick="toggleTag(this, 'act')" data-val="Delete"
                            class="adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Delete
                    </button>
                </div>
            </div>

            <!-- Search Button -->
            <button type="button" onclick="executeAdvancedSearch()"
                    class="w-full py-2 bg-[#1e58a8] hover:bg-[#18488a] text-white rounded-lg font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                <span>Execute Filtered Search</span>
            </button>
        </div>

        <!-- CONVERSION GATE ACTIVITY matching Figure 4.3 -->
        <div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 p-3 shadow-sm transition-colors">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="fa-solid fa-bolt text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100">Conversion Gate Activity</h4>
                        <p class="text-[10px] text-slate-400 dark:text-slate-400"><?= $conversionCount ?> file(s) currently staged</p>
                    </div>
                </div>
                <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">Optimal</span>
            </div>
        </div>

    </div>

</div>

<!-- Interactive Dashboard Scripts -->
<script>
    let selectedCategoryTag = '';
    let selectedActionTag = '';

    function toggleTag(btn, type) {
        if (type === 'cat') {
            document.querySelectorAll('.adv-tag[data-val="Research"], .adv-tag[data-val="Service"], .adv-tag[data-val="Training"], .adv-tag[data-val="Education"]').forEach(b => {
                b.className = "adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 text-slate-700 text-[10px] border border-slate-200 hover:bg-slate-200 transition-colors";
            });
            if (selectedCategoryTag === btn.dataset.val) {
                selectedCategoryTag = '';
            } else {
                selectedCategoryTag = btn.dataset.val;
                btn.className = "adv-tag w-full text-left px-2 py-1 rounded bg-blue-100 text-blue-800 font-bold text-[10px] border border-blue-300 transition-colors ring-1 ring-blue-400";
            }
        } else {
            document.querySelectorAll('.adv-tag[data-val="Approve"], .adv-tag[data-val="Reject"], .adv-tag[data-val="Create"], .adv-tag[data-val="Delete"]').forEach(b => {
                b.className = "adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 text-slate-700 text-[10px] border border-slate-200 hover:bg-slate-200 transition-colors";
            });
            if (selectedActionTag === btn.dataset.val) {
                selectedActionTag = '';
            } else {
                selectedActionTag = btn.dataset.val;
                btn.className = "adv-tag w-full text-left px-2 py-1 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px] border border-emerald-300 transition-colors ring-1 ring-emerald-400";
            }
        }
        applyDashboardLiveFilter();
    }

    function clearAdvancedFilters() {
        selectedCategoryTag = '';
        selectedActionTag = '';
        document.getElementById('advFacultyName').value = '';
        document.getElementById('advActionSelect').value = '';
        document.querySelectorAll('.adv-tag').forEach(b => {
            b.className = "adv-tag w-full text-left px-2 py-1 rounded bg-slate-100 text-slate-700 text-[10px] border border-slate-200 hover:bg-slate-200 transition-colors";
        });
        applyDashboardLiveFilter();
    }

    function executeAdvancedSearch() {
        const advName = document.getElementById('advFacultyName').value.trim();
        const advAct = document.getElementById('advActionSelect').value;
        applyDashboardLiveFilter();
        showSystemAlert({
            title: 'Filters Applied',
            message: `Filtered records by: ${advName || selectedCategoryTag || selectedActionTag || advAct || 'All criteria'}. Review the updated audit ledger.`,
            type: 'info',
            buttonText: 'View Table'
        });
    }

    function applyDashboardLiveFilter() {
        const query = (document.getElementById('dashboardSearchInput').value || '').toLowerCase();
        const status = document.getElementById('recordStatusSelect').value;
        const advName = (document.getElementById('advFacultyName')?.value || '').toLowerCase();
        const advAction = document.getElementById('advActionSelect')?.value || '';

        const rows = document.querySelectorAll('#dashboardTableBody .table-row-item');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowText = row.dataset.text || '';
            const action = row.dataset.action || '';

            let match = true;

            // Search query filter
            if (query && !rowText.includes(query)) match = false;
            if (advName && !rowText.includes(advName)) match = false;

            // Dropdown Status filter
            if (status !== 'ALL') {
                if (status === 'APPROVED' && !action.includes('APPROV')) match = false;
                if (status === 'REJECTED' && !action.includes('REJECT')) match = false;
                if (status === 'REPORT' && !action.includes('REPORT')) match = false;
            }

            // Tag filters
            if (selectedActionTag) {
                if (!action.toLowerCase().includes(selectedActionTag.toLowerCase())) match = false;
            }
            if (advAction) {
                if (!action.includes(advAction)) match = false;
            }

            if (match) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update count & notice
        document.getElementById('rowCountDisplay').textContent = `${visibleCount} records`;
        const notice = document.getElementById('noMatchingRowsNotice');
        if (visibleCount === 0) {
            notice.classList.remove('hidden');
        } else {
            notice.classList.add('hidden');
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
        const tbody = document.getElementById('dashboardTableBody');
        const rows = Array.from(tbody.querySelectorAll('.table-row-item'));
        rows.reverse();
        rows.forEach(r => tbody.appendChild(r));
    }
</script>

<?php require_once __DIR__ . '/../layouts/layout_end.php'; ?>
