<?php
$records = $records ?? [];
$isDean = $isDean ?? false;
$category = $category ?? 'All';
$search = $search ?? '';
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';
$issuedYear = $issuedYear ?? '';
$rawIds = $rawIds ?? '';
$activeNav = $activeNav ?? 'records';
$pageTitle = $pageTitle ?? 'Institutional Accreditation Report & Certificate Ledger';
require_once __DIR__ . '/../layouts/layout_start.php';
?>

<!-- Print Style Optimization for Physical Hardcopy Printing (Requirement 6) -->
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm 6mm 10mm 6mm;
        }
        header, aside, footer, #reportControls, #main-sidebar, nav, .no-print, [onclick*="toggleSidebar"] {
            display: none !important;
        }
        html, body {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-size: 7.5pt !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            overflow: visible !important;
            height: auto !important;
            width: 100% !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            overflow: visible !important;
            width: 100% !important;
        }
        .overflow-x-auto {
            overflow: visible !important;
            display: block !important;
            width: 100% !important;
        }
        .report-sheet {
            background-color: #ffffff !important;
            color: #000000 !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .report-sheet * {
            color-scheme: light !important;
            color: #000000 !important;
        }
        .report-sheet table {
            width: 100% !important;
            border-collapse: collapse !important;
            page-break-inside: auto !important;
            table-layout: auto !important;
        }
        .report-sheet thead {
            display: table-header-group !important; /* Repeats table header across pages */
        }
        .report-sheet thead tr {
            background-color: #f1f5f9 !important;
        }
        .report-sheet tbody {
            display: table-row-group !important;
        }
        .report-sheet tr {
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }
        .report-sheet th, .report-sheet td {
            border: 1px solid #94a3b8 !important;
            padding: 3.5px 4.5px !important;
            color: #000000 !important;
            background-color: transparent !important;
            font-size: 7.5pt !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }
        .print-signature-block {
            page-break-inside: avoid !important;
        }
        .print-signature-block p {
            color: #000000 !important;
        }
    }
</style>

<!-- Controls & Actions (Hidden during physical printing) -->
<div id="reportControls" class="space-y-3">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs pb-2 border-b border-slate-200 dark:border-slate-800 gap-3">
        <div>
            <h1 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">Institutional Records &amp; Accreditation Ledger</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">AACCUP / CHED Compliance Audit Readiness &amp; Official Departmental Archive Report</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="vault.php"
               class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Vault</span>
            </a>
            <a href="export.php?format=pdf<?= $category !== 'All' ? '&category=' . urlencode($category) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $dateFrom ? '&date_from=' . urlencode($dateFrom) : '' ?><?= $dateTo ? '&date_to=' . urlencode($dateTo) : '' ?><?= $issuedYear ? '&issued_year=' . urlencode($issuedYear) : '' ?><?= !empty($rawIds) ? '&ids=' . urlencode($rawIds) : '' ?>"
               class="px-3.5 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                <i class="fa-solid fa-file-pdf text-sm"></i>
                <span>Download PDF File</span>
            </a>
            <a href="export.php?format=csv<?= $category !== 'All' ? '&category=' . urlencode($category) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $dateFrom ? '&date_from=' . urlencode($dateFrom) : '' ?><?= $dateTo ? '&date_to=' . urlencode($dateTo) : '' ?><?= $issuedYear ? '&issued_year=' . urlencode($issuedYear) : '' ?><?= !empty($rawIds) ? '&ids=' . urlencode($rawIds) : '' ?>"
               class="px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                <i class="fa-solid fa-file-csv text-sm"></i>
                <span>Download CSV (Excel)</span>
            </a>
            <button onclick="window.print()"
                    class="px-4 py-2 rounded-lg bg-[#0d2342] hover:bg-[#14335e] text-white font-bold text-xs flex items-center gap-1.5 shadow-sm transition-all cursor-pointer">
                <i class="fa-solid fa-print text-sm"></i>
                <span>Print Sheet</span>
            </button>
        </div>
    </div>

    <!-- Active Scope Summary Pill Bar -->
    <div class="bg-white dark:bg-[#111c36] p-3 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-slate-500 dark:text-slate-400 font-semibold">Active Filter Scope:</span>
            <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-bold border border-blue-200 dark:border-blue-800">
                Category: <?= htmlspecialchars($category) ?>
            </span>
            <?php if ($dateFrom || $dateTo): ?>
                <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-200 dark:border-emerald-800 font-mono">
                    Issued Date: <?= htmlspecialchars($dateFrom ?: 'Any') ?> &rarr; <?= htmlspecialchars($dateTo ?: 'Present') ?>
                </span>
            <?php elseif ($issuedYear): ?>
                <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-200 dark:border-indigo-800 font-mono">
                    Issued Year: <?= htmlspecialchars($issuedYear) ?>
                </span>
            <?php else: ?>
                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium border border-slate-200 dark:border-slate-700">
                    All Historical Issued Dates
                </span>
            <?php endif; ?>

            <?php if (!empty($rawIds)): ?>
                <span class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 font-bold border border-purple-200 dark:border-purple-800 font-mono">
                    Manual Selection: <?= count($records) ?> records
                </span>
            <?php endif; ?>
        </div>

        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
            Auditable Records in Scope: <strong class="text-slate-800 dark:text-white"><?= count($records) ?></strong>
        </div>
    </div>
</div>

<!-- Report Sheet Container (Dark Themed on Screen, Clean Paper on Print) -->
<div class="report-sheet bg-white dark:bg-[#111c36] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-9 space-y-6 mt-3 transition-colors">

    <!-- Official Institutional Letterhead -->
    <div class="text-center border-b-2 border-slate-900 dark:border-slate-700 pb-4">
        <p class="text-[10px] font-serif uppercase tracking-widest text-slate-500 dark:text-slate-400">Republic of the Philippines</p>
        <h2 class="text-base font-bold uppercase font-serif text-slate-900 dark:text-white tracking-wide mt-0.5">
            Iloilo State University of Fisheries Science and Technology
        </h2>
        <h3 class="text-xs font-semibold uppercase text-slate-800 dark:text-slate-200">
            College of Informatics and Computing Innovations
        </h3>
        <p class="text-[10px] text-slate-400 dark:text-slate-500">Main Campus – Poblacion Site, Barotac Nuevo, Iloilo | cici@isufst.edu.ph</p>
    </div>

    <!-- Audit Metadata Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800 pb-3">
        <div>
            <h4 class="font-extrabold text-slate-900 dark:text-white uppercase">OFFICIAL CERTIFICATE &amp; ACCREDITATION LEDGER</h4>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                Category Scope: <strong class="text-slate-800 dark:text-slate-200"><?= htmlspecialchars($category) ?></strong> &bull;
                <?php if ($dateFrom || $dateTo): ?>
                    Issued Range: <?= htmlspecialchars($dateFrom ?: 'Beginning') ?> to <?= htmlspecialchars($dateTo ?: 'Current') ?>
                <?php elseif ($issuedYear): ?>
                    Issued Year: <?= htmlspecialchars($issuedYear) ?>
                <?php else: ?>
                    All Validated Issued Dates
                <?php endif; ?>
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 italic mt-0.5">Note: Date filters strictly evaluate the authentic Issued Date printed on each document, not system upload timestamps.</p>
        </div>
        <div class="text-right text-[11px] mt-2 sm:mt-0 font-mono text-slate-600 dark:text-slate-400">
            <p><strong>Audited By:</strong> <?= htmlspecialchars($currentUser['full_name']) ?></p>
            <p><strong>Generated On:</strong> <?= date('F d, Y \a\t h:i A') ?></p>
            <p><strong>Total Records:</strong> <?= count($records) ?></p>
        </div>
    </div>

    <!-- Table with Repeating Headers Across Pages -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border border-slate-200 dark:border-slate-800">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold border-b border-slate-200 dark:border-slate-800 text-[10px] uppercase tracking-wider">
                    <th class="p-2 border-r border-slate-200 dark:border-slate-800 w-10 text-center">#</th>
                    <th class="p-2 border-r border-slate-200 dark:border-slate-800">Document / Certificate Title</th>
                    <th class="p-2 border-r border-slate-200 dark:border-slate-800">Certificate Holder</th>
                    <th class="p-2 border-r border-slate-200 dark:border-slate-800">Cert No. / Ref</th>
                    <th class="p-2 border-r border-slate-200 dark:border-slate-800">Date Issued</th>
                    <th class="p-2 border-r border-slate-200 dark:border-slate-800">Issuing Authority</th>
                    <th class="p-2 border-r border-slate-200 dark:border-slate-800">Category</th>
                    <th class="p-2 text-center">Cryptographic Integrity</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-[11px]">
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400 dark:text-slate-500">No institutional records match the selected scope criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $idx => $r): ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-mono text-center"><?= $idx + 1 ?></td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 font-bold text-slate-900 dark:text-white max-w-xs">
                                <?= htmlspecialchars($r['title']) ?>
                                <span class="text-[9px] text-slate-400 dark:text-slate-500 font-mono block"><?= strtoupper(htmlspecialchars($r['file_type'])) ?></span>
                            </td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 text-slate-800 dark:text-slate-200">
                                <?php
                                    $isLead = ((int)$r['faculty_id'] === (int)$currentUser['id']);
                                    $isCoAuth = !$isDean && !$isLead;
                                ?>
                                <strong><?= htmlspecialchars($r['faculty_name'] ?? $currentUser['full_name']) ?></strong>
                                <?php if ($isCoAuth): ?>
                                    <span class="text-[9px] text-purple-700 dark:text-purple-300 font-bold block">(Primary Uploader &bull; You are Co-Author)</span>
                                <?php elseif (!$isDean): ?>
                                    <span class="text-[9px] text-blue-700 dark:text-blue-300 font-bold block">(Lead Author &bull; You)</span>
                                <?php endif; ?>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block"><?= htmlspecialchars($r['academic_rank'] ?? 'CICI Faculty') ?></span>
                                <?php if (!empty($r['co_authors'])): ?>
                                    <span class="text-[9.5px] text-purple-600 dark:text-purple-400 block mt-0.5 font-medium">
                                        <i class="fa-solid fa-users text-[8.5px]"></i> Co-Authors: <?= htmlspecialchars($r['co_authors']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                <?= !empty($r['cert_number']) ? htmlspecialchars($r['cert_number']) : '<span class="text-slate-400 dark:text-slate-600 italic">N/A</span>' ?>
                            </td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 font-mono text-slate-800 dark:text-slate-200 font-semibold whitespace-nowrap">
                                <?= htmlspecialchars($r['date_issued']) ?>
                            </td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-300 max-w-[140px] truncate">
                                <?= htmlspecialchars($r['issuer']) ?>
                            </td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 whitespace-nowrap">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                    <?= htmlspecialchars($r['category']) ?>
                                </span>
                            </td>
                            <td class="p-2 text-center text-emerald-700 dark:text-emerald-300 font-semibold text-[10px] whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/60">
                                    <i class="fa-solid fa-lock text-[8px]"></i>
                                    <span>AES-256</span>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Official Signatures Block (Page-break protected) -->
    <div class="print-signature-block mt-8 pt-6 grid grid-cols-2 gap-8 text-xs text-slate-700 dark:text-slate-300">
        <div>
            <p class="text-slate-400 dark:text-slate-500 mb-8">Prepared and Certified by:</p>
            <p class="font-bold border-t border-slate-300 dark:border-slate-700 pt-1 inline-block min-w-[220px] text-slate-900 dark:text-white">
                <?= htmlspecialchars($currentUser['full_name']) ?>
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">CICI Department Officer / Auditor</p>
        </div>
        <div class="text-right">
            <p class="text-slate-400 dark:text-slate-500 mb-8">Attested Compliance:</p>
            <p class="font-bold border-t border-slate-300 dark:border-slate-700 pt-1 inline-block min-w-[220px] text-slate-900 dark:text-white">
                REPUBLIC ACT NO. 10173
            </p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Data Privacy Act &amp; AACCUP Quality Standard</p>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/layout_end.php'; ?>
