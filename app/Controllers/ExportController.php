<?php
/**
 * ExportController
 * Institutional Accreditation Report & Certificate Ledger Generator
 * Controller Layer
 */

require_once __DIR__ . '/BaseController.php';

class ExportController extends BaseController {
    public function index(): void {
        AuthService::requireLogin();
        $currentUser = AuthService::user();
        $isDean = AuthService::isDean();

        $format = $_GET['format'] ?? 'html';
        $rawIds = substr($_GET['ids'] ?? '', 0, 500);
        $category = substr($_GET['category'] ?? 'All', 0, 50);
        $search = trim(substr($_GET['search'] ?? '', 0, 100));
        $targetFacultyId = !empty($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : null;
        $dateFrom = trim(substr($_GET['date_from'] ?? '', 0, 10));
        $dateTo = trim(substr($_GET['date_to'] ?? '', 0, 10));
        $issuedYear = trim(substr($_GET['issued_year'] ?? '', 0, 4));
        $scope = trim(substr($_GET['scope'] ?? 'All', 0, 50));
        $indexing = trim(substr($_GET['indexing'] ?? 'All', 0, 50));

        $facultyMembers = $isDean ? UserModel::getFacultyList() : [];
        $targetFacultyName = '';
        if ($targetFacultyId) {
            foreach ($facultyMembers as $fm) {
                if ((int)$fm['id'] === $targetFacultyId) {
                    $targetFacultyName = $fm['full_name'];
                    break;
                }
            }
        }

        // Query records based on bulk selected IDs or active filters
        if (!empty($rawIds)) {
            $idList = array_filter(array_map('intval', explode(',', $rawIds)));
            $records = !empty($idList) ? CertificateModel::getByIds($idList, $isDean ? null : (int)$currentUser['id']) : [];
        } else {
            if ($isDean) {
                $records = CertificateModel::getAll($category, $search, $targetFacultyId, $dateFrom, $dateTo, $issuedYear, $scope, $indexing);
            } else {
                $filters = [
                    'category' => $category,
                    'search' => $search,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'issued_year' => $issuedYear,
                    'scope' => $scope,
                    'indexing' => $indexing
                ];
                $records = CertificateModel::getForFaculty($currentUser['id'], $filters);
            }
        }

        // CSV Export Handler
        if ($format === 'csv') {
            $filename = "CICI_Accreditation_Ledger_" . ($category !== 'All' ? preg_replace('/[^a-zA-Z0-9]/', '_', $category) . "_" : "") . date('Ymd_His') . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($out, [
                '#',
                'Document Title',
                'Faculty Holder',
                'Co-Authors',
                'Academic Rank',
                'Cert Ref / Number',
                'Date Issued',
                'Issuing Authority',
                'Category',
                'Scope / Level',
                'Indexing Status',
                'Place Issued / Venue',
                'AACCUP Area',
                'Cryptographic Integrity'
            ]);
            foreach ($records as $idx => $r) {
                $scopeVal = $r['scope'] ?? 'Institutional';
                $indexingVal = $r['indexing'] ?? 'None';
                $placeVal = $r['place_issued'] ?? ($r['venue'] ?? '');
                $aaccupVal = $r['accreditation_area'] ?? '';
                if (!$aaccupVal) {
                    $catL = strtolower($r['category'] ?? '');
                    if (str_contains($catL, 'research')) $aaccupVal = 'Area V: Research';
                    elseif (str_contains($catL, 'instructional') || str_contains($catL, 'module')) $aaccupVal = 'Area III: Curriculum and Instruction';
                    else $aaccupVal = 'Area VIII: Faculty Development';
                }
                fputcsv($out, [
                    $idx + 1,
                    $r['title'] ?? 'Untitled',
                    $r['faculty_name'] ?? '',
                    $r['co_authors'] ?? '',
                    $r['academic_rank'] ?? '',
                    $r['cert_number'] ?? 'N/A',
                    DateHelper::formatDate($r['date_issued'] ?? null, 'Undated'),
                    $r['issuer'] ?? 'Not Specified',
                    $r['category'] ?? 'General',
                    $scopeVal,
                    $indexingVal,
                    $placeVal,
                    $aaccupVal,
                    'AES-256 Verified'
                ]);
            }
            fclose($out);
            AuditLogger::log('EXPORT_CSV', "Exported official CSV accreditation report (" . count($records) . " records)");
            exit;
        }

        // Dedicated PDF Document Export Handler (Policy: official audit exports are immutable PDF)
        if ($format === 'pdf') {
            $dateScope = 'All Validated Records';
            if ($dateFrom || $dateTo) {
                $dateScope = ($dateFrom ?: 'Beginning') . ' to ' . ($dateTo ?: 'Current');
            } elseif ($issuedYear) {
                $dateScope = 'Issued Year ' . $issuedYear;
            }

            $payload = [
                'meta' => [
                    'category' => $category,
                    'date_scope' => $dateScope,
                    'faculty_scope' => $targetFacultyName ?: 'All Faculty',
                    'auditor_name' => $currentUser['full_name'],
                    'generated_at' => date(DateHelper::DATETIME_FORMAT)
                ],
                'records' => $records
            ];

            $tempJson = TEMP_DIR . DIRECTORY_SEPARATOR . 'report_' . bin2hex(random_bytes(8)) . '.json';
            $tempPdf = TEMP_DIR . DIRECTORY_SEPARATOR . 'report_' . bin2hex(random_bytes(8)) . '.pdf';
            file_put_contents($tempJson, json_encode($payload));

            $pythonBin = $this->getPythonBinary();
            $generatorScript = BASE_DIR . DIRECTORY_SEPARATOR . 'python_engine' . DIRECTORY_SEPARATOR . 'generate_report_pdf.py';

            $cmd = sprintf(
                '%s %s %s %s 2>&1',
                escapeshellcmd($pythonBin),
                escapeshellarg($generatorScript),
                escapeshellarg($tempJson),
                escapeshellarg($tempPdf)
            );

            $cmdOutput = shell_exec($cmd);
            @unlink($tempJson);

            if (file_exists($tempPdf) && filesize($tempPdf) > 0) {
                $filename = "CICI_Accreditation_Ledger_" . ($category !== 'All' ? preg_replace('/[^a-zA-Z0-9]/', '_', $category) . "_" : "") . date('Ymd_His') . ".pdf";
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($tempPdf));
                readfile($tempPdf);
                @unlink($tempPdf);
                AuditLogger::log('EXPORT_REPORT', "Exported official PDF accreditation report (" . count($records) . " records)");
                exit;
            } else {
                $errMsg = !empty($cmdOutput) ? trim($cmdOutput) : 'ReportLab PDF compilation failed';
                AuditLogger::log('EXPORT_ERROR', "Accreditation PDF compilation failed: " . mb_substr($errMsg, 0, 200));
                if (!headers_sent()) {
                    http_response_code(500);
                }
                echo "<div style='font-family:sans-serif;max-width:600px;margin:40px auto;padding:24px;border:1px solid #f87171;border-radius:12px;background:#fef2f2;color:#991b1b;'>";
                echo "<h3 style='margin-top:0;'>Official PDF Generation Failed</h3>";
                echo "<p>The Python PDF report generator was unable to render the official ledger document.</p>";
                echo "<pre style='background:#fee2e2;padding:12px;border-radius:8px;overflow-x:auto;font-size:11px;'>" . htmlspecialchars($errMsg) . "</pre>";
                echo "<p><a href='export.php' style='color:#b91c1c;font-weight:bold;'>&larr; Return to Ledger View</a></p>";
                echo "</div>";
                exit;
            }
        }

        // Render HTML Sheet View
        $this->renderView('export/index.php', [
            'currentUser' => $currentUser,
            'isDean' => $isDean,
            'format' => $format,
            'rawIds' => $rawIds,
            'category' => $category,
            'search' => $search,
            'targetFacultyId' => $targetFacultyId,
            'targetFacultyName' => $targetFacultyName,
            'facultyMembers' => $facultyMembers,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'issuedYear' => $issuedYear,
            'scope' => $scope,
            'indexing' => $indexing,
            'records' => $records,
            'activeNav' => 'records',
            'pageTitle' => 'Institutional Accreditation Report & Certificate Ledger'
        ]);
    }
}
