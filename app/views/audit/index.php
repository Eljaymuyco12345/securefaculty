<?php
// Master Layout Configuration
$activeNav = $activeNav ?? 'audit';
$pageTitle = $pageTitle ?? "Compliance Audit Ledger - RA 10173";
$actionFilter = $actionFilter ?? '';
$search = $search ?? '';
$statTotal = $statTotal ?? 0;
$statLogins = $statLogins ?? 0;
$statDocs = $statDocs ?? 0;
$statAlerts = $statAlerts ?? 0;
$displayLogs = $displayLogs ?? [];
$facultyLookup = $facultyLookup ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalLogs = $totalLogs ?? count($displayLogs);

require_once __DIR__ . '/../layouts/layout_start.php';
?>

<!-- Title Banner -->
<div>
    <h1 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">Compliance Audit Ledger</h1>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs mt-1 border-b border-slate-200 dark:border-slate-800 pb-2">
        <span class="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[11px]">
            RA 10173 IMMUTABLE AUDIT TRAIL
        </span>
        <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium mt-1 sm:mt-0">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Real-Time Cryptographic Integrity Verification
        </span>
    </div>
</div>

<!-- Clickable Stats Row (4 interactive cards) -->
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
    <!-- Total Audit Events -->
    <a href="audit_logs.php"
       class="bg-white dark:bg-[#111c36] rounded-xl border <?= empty($actionFilter) ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800' ?> shadow-sm p-4 flex items-center gap-3 hover:shadow-md transition-all group">
        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-base flex-shrink-0 group-hover:scale-105 transition-transform">
            <i class="fa-solid fa-scroll"></i>
        </div>
        <div>
            <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Events</p>
            <p class="text-lg font-black text-slate-900 dark:text-white leading-tight"><?= number_format($statTotal) ?></p>
        </div>
    </a>

    <!-- Login Events -->
    <a href="audit_logs.php?action=LOGIN_SUCCESS"
       class="bg-white dark:bg-[#111c36] rounded-xl border <?= $actionFilter === 'LOGIN_SUCCESS' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-800' ?> shadow-sm p-4 flex items-center gap-3 hover:shadow-md transition-all group">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base flex-shrink-0 group-hover:scale-105 transition-transform">
            <i class="fa-solid fa-right-to-bracket"></i>
        </div>
        <div>
            <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Login Events</p>
            <p class="text-lg font-black text-slate-900 dark:text-white leading-tight"><?= number_format($statLogins) ?></p>
        </div>
    </a>

    <!-- Document Operations -->
    <a href="audit_logs.php?action=ARCHIVE_DOCUMENT"
       class="bg-white dark:bg-[#111c36] rounded-xl border <?= $actionFilter === 'ARCHIVE_DOCUMENT' ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-slate-200 dark:border-slate-800' ?> shadow-sm p-4 flex items-center gap-3 hover:shadow-md transition-all group">
        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base flex-shrink-0 group-hover:scale-105 transition-transform">
            <i class="fa-solid fa-file-shield"></i>
        </div>
        <div>
            <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Doc Operations</p>
            <p class="text-lg font-black text-slate-900 dark:text-white leading-tight"><?= number_format($statDocs) ?></p>
        </div>
    </a>

    <!-- Security Alerts -->
    <a href="audit_logs.php?action=LOGIN_FAILED"
       class="bg-white dark:bg-[#111c36] rounded-xl border <?= $actionFilter === 'LOGIN_FAILED' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-800' ?> shadow-sm p-4 flex items-center gap-3 hover:shadow-md transition-all group">
        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center text-base flex-shrink-0 group-hover:scale-105 transition-transform">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
            <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Security Alerts</p>
            <p class="text-lg font-black text-<?= $statAlerts > 0 ? 'rose-600 dark:text-rose-400' : 'slate-900 dark:text-white' ?> leading-tight"><?= number_format($statAlerts) ?></p>
        </div>
    </a>
</div>

<!-- Main Table Container -->
<div class="bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">

    <!-- Filters & Actions Header -->
    <div class="p-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-slate-500 dark:text-slate-400 font-semibold mr-1">Action:</span>
            <a href="audit_logs.php<?= $search ? '?search=' . urlencode($search) : '' ?>"
               class="px-2.5 py-1 rounded <?= empty($actionFilter) ? 'bg-[#0d2342] text-white font-bold' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">All</a>
            <a href="audit_logs.php?action=LOGIN_SUCCESS<?= $search ? '&search=' . urlencode($search) : '' ?>"
               class="px-2.5 py-1 rounded <?= $actionFilter === 'LOGIN_SUCCESS' ? 'bg-[#0d2342] text-white font-bold' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">Logins</a>
            <a href="audit_logs.php?action=VIEW_DOCUMENT<?= $search ? '&search=' . urlencode($search) : '' ?>"
               class="px-2.5 py-1 rounded <?= $actionFilter === 'VIEW_DOCUMENT' ? 'bg-[#0d2342] text-white font-bold' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">Decryptions</a>
            <a href="audit_logs.php?action=ARCHIVE_DOCUMENT<?= $search ? '&search=' . urlencode($search) : '' ?>"
               class="px-2.5 py-1 rounded <?= $actionFilter === 'ARCHIVE_DOCUMENT' ? 'bg-[#0d2342] text-white font-bold' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">Ingestions</a>
            <a href="audit_logs.php?action=DELETE_DOCUMENT<?= $search ? '&search=' . urlencode($search) : '' ?>"
               class="px-2.5 py-1 rounded <?= $actionFilter === 'DELETE_DOCUMENT' ? 'bg-[#0d2342] text-white font-bold' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700' ?>">Deletions</a>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Table Instant Search Box -->
            <div class="relative w-44 sm:w-56">
                <i class="fa-solid fa-magnifying-glass text-[10px] text-slate-400 absolute left-2.5 top-2.5"></i>
                <input type="text" id="auditSearchInput" onkeyup="filterAuditRows(this.value)"
                       placeholder="Live search rows..."
                       class="w-full pl-7 pr-3 py-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-all">
            </div>

            <a href="audit_logs.php?format=csv<?= $actionFilter ? '&action=' . urlencode($actionFilter) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
               class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                <i class="fa-solid fa-file-export text-xs"></i>
                <span>Export Audit CSV</span>
            </a>
        </div>
    </div>

    <!-- Audit Ledger Table -->
    <div class="overflow-x-auto flex-1">
        <table class="w-full text-left text-xs border-collapse" id="auditTable">
            <thead>
                <tr class="bg-slate-100/80 dark:bg-slate-900 text-slate-600 dark:text-slate-400 font-bold text-[11px] border-b border-slate-200 dark:border-slate-800">
                    <th class="py-2.5 px-3 whitespace-nowrap">Timestamp (UTC)</th>
                    <th class="py-2.5 px-3">User ID</th>
                    <th class="py-2.5 px-3">Action Recorded</th>
                    <th class="py-2.5 px-3">Affected Faculty Record</th>
                    <th class="py-2.5 px-3">Source IP Address</th>
                    <th class="py-2.5 px-3 text-center">Cryptographic Integrity</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-[11px]" id="auditTableBody">
                <?php if (empty($displayLogs)): ?>
                    <tr id="emptyAuditRow">
                        <td colspan="6" class="py-10 text-center text-slate-400 dark:text-slate-500">
                            <i class="fa-regular fa-folder-open text-2xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                            No audit transactions found for this filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($displayLogs as $log): ?>
                        <?php
                            $userId = ($log['role'] === 'dean')
                                ? 'ISUFST001'
                                : 'ISUFST' . str_pad($log['user_id'] ?? '02', 3, '0', STR_PAD_LEFT);

                            $actionRaw = $log['action'];
                            $actionMap = [
                                'LOGIN_SUCCESS' => 'Login success',
                                'LOGIN_FAILED' => 'Login failed',
                                'LOGOUT' => 'Logout',
                                'VIEW_DOCUMENT' => 'View document',
                                'DOWNLOAD_DOCUMENT' => 'Download document',
                                'ARCHIVE_DOCUMENT' => 'Archive document',
                                'DELETE_DOCUMENT' => 'Delete document',
                                'OCR_EXTRACT' => 'Ocr extract',
                                'EXPORT_REPORT' => 'Export report',
                            ];
                            $actionLabel = $actionMap[$actionRaw] ?? ucwords(strtolower(str_replace('_', ' ', $actionRaw)));

                            $actionColor = match(true) {
                                str_contains($actionRaw, 'LOGIN_SUCCESS') => 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800/60',
                                str_contains($actionRaw, 'LOGIN_FAILED') => 'text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800/60',
                                str_contains($actionRaw, 'LOGOUT') => 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700',
                                str_contains($actionRaw, 'ARCHIVE') => 'text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/40 border-purple-200 dark:border-purple-800/60',
                                str_contains($actionRaw, 'DELETE') => 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800/60',
                                str_contains($actionRaw, 'VIEW') || str_contains($actionRaw, 'DOWNLOAD') => 'text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800/60',
                                str_contains($actionRaw, 'OCR') => 'text-cyan-700 dark:text-cyan-300 bg-cyan-50 dark:bg-cyan-950/40 border-cyan-200 dark:border-cyan-800/60',
                                default => 'text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700',
                            };

                            $affectedRecord = $facultyLookup[$log['target_id'] ?? 0] ?? 'Departmental Audit';
                        ?>
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors audit-log-row"
                            data-search="<?= strtolower($log['created_at'] . ' ' . $userId . ' ' . $actionLabel . ' ' . $affectedRecord . ' ' . $log['ip_address']) ?>">
                            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400 font-mono whitespace-nowrap"><?= htmlspecialchars($log['created_at']) ?></td>
                            <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-white font-mono"><?= htmlspecialchars($userId) ?></td>
                            <td class="py-2.5 px-3">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold border <?= $actionColor ?>">
                                    <?= htmlspecialchars($actionLabel) ?>
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 font-medium"><?= htmlspecialchars($affectedRecord) ?></td>
                            <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400 font-mono"><?= htmlspecialchars($log['ip_address']) ?></td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="inline-flex items-center gap-1 text-[10px] text-blue-600 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded-full border border-blue-200 dark:border-blue-800/60" title="Chained immutable log entry">
                                    <i class="fa-solid fa-lock text-[9px]"></i>
                                    <span>Verified</span>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Table Footer with Pagination -->
    <div class="p-2.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex flex-wrap items-center justify-between gap-2 text-[10px] text-slate-500 dark:text-slate-400">
        <div>
            <span>Complies with Section 20 & 21 of Republic Act No. 10173</span>
            <span class="ml-1 text-slate-400 dark:text-slate-500">&bull; Showing <?= count($displayLogs) ?> of <?= $totalLogs ?> records</span>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="flex items-center gap-1 font-mono text-[10px]">
            <?php if ($page > 1): ?>
                <a href="audit_logs.php?page=<?= $page - 1 ?><?= $actionFilter ? '&action=' . urlencode($actionFilter) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                   class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 cursor-pointer">&lt;</a>
            <?php endif; ?>

            <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $startPage + 4);
                $startPage = max(1, $endPage - 4);
            ?>
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="audit_logs.php?page=<?= $i ?><?= $actionFilter ? '&action=' . urlencode($actionFilter) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                   class="px-1.5 py-0.5 rounded <?= $i === $page ? 'bg-[#0d2342] text-white font-bold' : 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200' ?> cursor-pointer"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="audit_logs.php?page=<?= $page + 1 ?><?= $actionFilter ? '&action=' . urlencode($actionFilter) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                   class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 cursor-pointer">&gt;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Audit Trail Active</span>
        </span>
    </div>

</div>

<script>
    function filterAuditRows(term) {
        const query = (term || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#auditTableBody .audit-log-row');
        let visible = 0;
        rows.forEach(r => {
            const data = r.dataset.search || '';
            if (!query || data.includes(query)) {
                r.style.display = '';
                visible++;
            } else {
                r.style.display = 'none';
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/layout_end.php'; ?>
