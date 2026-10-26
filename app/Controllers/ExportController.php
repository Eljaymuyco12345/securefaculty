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
        $rawIds = $_GET['ids'] ?? '';
        $category = $_GET['category'] ?? 'All';
        $search = trim($_GET['search'] ?? '');
        $targetFacultyId = !empty($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : null;
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $issuedYear = trim($_GET['issued_year'] ?? '');

        // Query records based on bulk selected IDs or active filters
        if (!empty($rawIds)) {
            $idList = array_filter(array_map('intval', explode(',', $rawIds)));
            $records = !empty($idList) ? CertificateModel::getByIds($idList, $isDean ? null : (int)$currentUser['id']) : [];
        } else {
            if ($isDean) {
                $records = CertificateModel::getAll($category, $search, $targetFacultyId, $dateFrom, $dateTo, $issuedYear);
            } else {
                $filters = [
                    'category' => $category,
                    'search' => $search,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'issued_year' => $issuedYear
                ];
                $records = CertificateModel::getForFaculty($currentUser['id'], $filters);
            }
        }

        // CSV Export Handler
        if ($format === 'csv') {
            $filename = "CICI_Accreditation_Ledger_" . ($category !== 'All' ? preg_replace('/[^a-zA-Z0-9]/', '_', $category) . "_" : "") . date('Ymd_His') . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, [
                'ID',
                'Faculty Member',
                'Co-Authors',
                'Academic Rank',
                'Employee ID',
                'Document Title',
                'Certificate / Reference No.',
                'Category',
                'Issuing Authority',
                'Date Issued (Certificate)',
                'File Type',
                'Security Status',
                'Date Ingested'
            ]);

            $sanitizeCell = function($val) {
                if (is_string($val) && strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@', "\t", "\r"])) {
                    return "'" . $val;
                }
                return $val;
            };

            foreach ($records as $r) {
                $row = [
                    $r['id'],
                    $r['faculty_name'] ?? $currentUser['full_name'],
                    !empty($r['co_authors']) ? $r['co_authors'] : 'None',
                    $r['academic_rank'] ?? 'CICI Faculty',
                    $r['employee_id'] ?? 'N/A',
                    $r['title'],
                    $r['cert_number'] ?? 'N/A',
                    $r['category'],
                    $r['issuer'],
                    $r['date_issued'],
                    strtoupper($r['file_type']),
                    'AES-256 Vault Encrypted',
                    $r['created_at']
                ];
                fputcsv($output, array_map($sanitizeCell, $row));
            }

            AuditLogger::log('EXPORT_REPORT', "Exported CSV accreditation report (" . count($records) . " records)");
            fclose($output);
            exit;
        }

        // Dedicated PDF Document Export Handler
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
                    'auditor_name' => $currentUser['full_name'],
                    'generated_at' => date('F d, Y \a\t h:i A')
                ],
                'records' => $records
            ];

            $tempJson = TEMP_DIR . DIRECTORY_SEPARATOR . 'report_' . bin2hex(random_bytes(8)) . '.json';
            $tempPdf = TEMP_DIR . DIRECTORY_SEPARATOR . 'report_' . bin2hex(random_bytes(8)) . '.pdf';
            file_put_contents($tempJson, json_encode($payload));

            $pythonBin = defined('PYTHON_BIN') ? PYTHON_BIN : 'python3';
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
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'issuedYear' => $issuedYear,
            'records' => $records,
            'activeNav' => 'records',
            'pageTitle' => 'Institutional Accreditation Report & Certificate Ledger'
        ]);
    }
}
