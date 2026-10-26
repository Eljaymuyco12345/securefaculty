<?php
/**
 * VaultController
 * Manages Personal Vault and Institutional Department Vault operations
 * Controller Layer
 */

require_once __DIR__ . '/BaseController.php';

class VaultController extends BaseController {
    public function index(): void {
        AuthService::requireLogin();
        $currentUser = AuthService::user();
        $isDean = AuthService::isDean();

        // Handle single or bulk deletion with CSRF Protection
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['action'])) {
            $this->handleDelete($currentUser, $isDean);
            return;
        }

        // Strict Date Issued Filtering (NOT upload date)
        $category = $_GET['category'] ?? 'All';
        $search = trim($_GET['search'] ?? '');
        $targetFacultyId = !empty($_GET['faculty_id']) ? (int)$_GET['faculty_id'] : null;
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $issuedYear = trim($_GET['issued_year'] ?? '');
        $viewMode = $_GET['view'] ?? 'table';

        $profile = UserModel::getProfile($currentUser['id']);

        $availableYears = CertificateModel::getAvailableIssuedYears();

        $facultyMembers = $isDean ? UserModel::getFacultyList() : [];

        if ($isDean) {
            $certificates = CertificateModel::getAll($category, $search, $targetFacultyId, $dateFrom, $dateTo, $issuedYear);
        } else {
            $filters = [
                'category' => $category,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'issued_year' => $issuedYear
            ];
            $certificates = CertificateModel::getForFaculty($currentUser['id'], $filters);
        }

        $allCategories = ['All', 'Modules & IMs', 'Research', 'Seminars', 'Trainings', 'Workshops', 'Awards', 'Milestones'];

        $this->renderView('vault/index.php', [
            'currentUser' => $currentUser,
            'isDean' => $isDean,
            'category' => $category,
            'search' => $search,
            'targetFacultyId' => $targetFacultyId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'issuedYear' => $issuedYear,
            'viewMode' => $viewMode,
            'availableYears' => $availableYears,
            'facultyMembers' => $facultyMembers,
            'certificates' => $certificates,
            'records' => $certificates,
            'allCategories' => $allCategories,
            'profile' => $profile,
            'pageTitle' => $isDean ? 'Institutional Department Vault' : 'Faculty Personal Digital Vault',
            'activeNav' => 'certificates'
        ]);
    }

    private function handleDelete(array $currentUser, bool $isDean): void {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!AuthService::verifyCsrfToken($csrfToken)) {
            AuditLogger::log('CSRF_VIOLATION_BLOCKED', 'Blocked unauthorized deletion attempt in Vault', $currentUser['id']);
            $this->redirect('vault.php?error=csrf_violation');
        }

        if ($_POST['action'] === 'delete') {
            $deleteId = (int)($_POST['certificate_id'] ?? 0);
            if ($deleteId > 0) {
                $cert = CertificateModel::find($deleteId);
                $isPrimary = $cert && ((int)$cert['faculty_id'] === (int)$currentUser['id']);
                $deleted = CertificateModel::delete($deleteId, $currentUser['id'], $isDean);
                if ($deleted) {
                    if (!$isDean && !$isPrimary) {
                        AuditLogger::log('DISASSOCIATE_COAUTHOR', "Faculty '{$currentUser['full_name']}' removed co-authorship association from certificate #{$deleteId}", $deleteId);
                        $this->redirect('vault.php?msg=coauthor_removed');
                    } else {
                        AuditLogger::log('DELETE_DOCUMENT', "Deleted certificate record #{$deleteId} and purged encrypted payload", $deleteId);
                        $this->redirect('vault.php?msg=deleted');
                    }
                } else {
                    AuditLogger::log('UNAUTHORIZED_ACTION', "Failed or unauthorized deletion attempt for certificate #{$deleteId}", $deleteId);
                    $this->redirect('vault.php?error=unauthorized_delete');
                }
            }
        } elseif ($_POST['action'] === 'bulk_delete') {
            $rawIds = $_POST['selected_ids'] ?? '';
            $selectedIds = array_filter(array_map('intval', explode(',', $rawIds)));
            if (!empty($selectedIds)) {
                $deletedCount = CertificateModel::bulkDelete($selectedIds, $currentUser['id'], $isDean);
                if ($deletedCount > 0) {
                    AuditLogger::log('BULK_DELETE_DOCUMENTS', "Bulk deleted {$deletedCount} certificates", null);
                    $this->redirect('vault.php?msg=bulk_deleted&count=' . $deletedCount);
                } else {
                    AuditLogger::log('UNAUTHORIZED_ACTION', "Failed or unauthorized bulk deletion attempt", null);
                    $this->redirect('vault.php?error=unauthorized_delete');
                }
            }
        }

        $this->redirect('vault.php');
    }
}
