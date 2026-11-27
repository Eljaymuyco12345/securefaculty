<?php
/**
 * FolderDAO Micro-Unit Test
 * Tests 1-level Virtual Folders CRUD, safe unlinking on delete,
 * single & bulk certificate moves, CertificateDAO folder filtering, and RBAC isolation.
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/DAO/FolderDAO.php';
require_once __DIR__ . '/../../../app/DAO/CertificateDAO.php';

class FolderDAOTest extends TestCase {
    private array $createdFolderIds = [];
    private array $createdCertIds = [];
    private array $tempEncFiles = [];

    public function tearDown(): void {
        $pdo = Database::getConnection();

        foreach ($this->createdCertIds as $id) {
            $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
            $stmt->execute([$id]);
        }
        $this->createdCertIds = [];

        foreach ($this->createdFolderIds as $id) {
            $stmt = $pdo->prepare("DELETE FROM vault_folders WHERE id = ?");
            $stmt->execute([$id]);
        }
        $this->createdFolderIds = [];

        foreach ($this->tempEncFiles as $encFile) {
            $vaultPath = VAULT_DIR . DIRECTORY_SEPARATOR . $encFile;
            if (file_exists($vaultPath)) {
                @unlink($vaultPath);
            }
        }
        $this->tempEncFiles = [];

        parent::tearDown();
    }

    private function createTestCertificate(int $facultyId, ?int $folderId = null, string $titleSuffix = ''): int {
        $encFile = 'test_folder_cert_' . bin2hex(random_bytes(8)) . '.enc';
        file_put_contents(VAULT_DIR . DIRECTORY_SEPARATOR . $encFile, "DUMMY_ENCRYPTED_DATA");
        $this->tempEncFiles[] = $encFile;

        $unique = bin2hex(random_bytes(6));
        $certId = CertificateDAO::create([
            'faculty_id' => $facultyId,
            'folder_id' => $folderId,
            'title' => 'Folder Unit Test Cert ' . $titleSuffix . ' ' . $unique,
            'issuer' => 'ISUFST Testing Authority',
            'date_issued' => '2026-05-01',
            'category' => 'Research',
            'file_path' => $encFile,
            'original_filename' => 'folder_test_' . $unique . '.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'folder_test_' . $unique . '_' . microtime(true)),
            'cert_number' => 'CERT-FLD-' . rand(1000, 9999),
            'status' => 'approved',
            'processing_status' => 'completed',
        ]);
        $this->createdCertIds[] = $certId;
        return $certId;
    }

    public function testCreateFindAndGetForUser(): void {
        $folderA = FolderDAO::create(2, 'Research 2026', 'emerald');
        $folderB = FolderDAO::create(2, 'Seminars', 'blue');
        $otherUserFolder = FolderDAO::create(3, 'Other Faculty Folder', 'amber');
        $this->createdFolderIds[] = $folderA;
        $this->createdFolderIds[] = $folderB;
        $this->createdFolderIds[] = $otherUserFolder;

        $this->assertGreaterThan(0, $folderA);
        $this->assertGreaterThan(0, $folderB);

        $found = FolderDAO::find($folderA);
        $this->assertNotNull($found);
        $this->assertEquals('Research 2026', $found['name']);
        $this->assertEquals('emerald', $found['color']);
        $this->assertEquals(2, (int)$found['user_id']);

        $this->createTestCertificate(2, $folderA, 'InA');

        $user2Folders = FolderDAO::getForUser(2);
        $ids = array_column($user2Folders, 'id');
        $this->assertTrue(in_array($folderA, $ids, true), 'User 2 folders must include folderA');
        $this->assertTrue(in_array($folderB, $ids, true), 'User 2 folders must include folderB');
        $this->assertFalse(in_array($otherUserFolder, $ids, true), 'User 2 folders must not include user 3 folder');

        foreach ($user2Folders as $row) {
            if ((int)$row['id'] === $folderA) {
                $this->assertEquals(1, (int)$row['item_count']);
            } elseif ((int)$row['id'] === $folderB) {
                $this->assertEquals(0, (int)$row['item_count']);
            }
        }
    }

    public function testRenameAndRbacIsolation(): void {
        $folderId = FolderDAO::create(2, 'Original Folder Name', 'blue');
        $this->createdFolderIds[] = $folderId;

        // Another faculty user (3) cannot rename user 2's folder
        $unauthorized = FolderDAO::rename($folderId, 3, 'Hacked Name', false);
        $this->assertFalse($unauthorized, 'Other faculty must not be able to rename another user folder');

        $stillOriginal = FolderDAO::find($folderId);
        $this->assertEquals('Original Folder Name', $stillOriginal['name']);

        // Owner (2) can rename
        $ownerRenamed = FolderDAO::rename($folderId, 2, 'Updated By Owner', false);
        $this->assertTrue($ownerRenamed);
        $this->assertEquals('Updated By Owner', FolderDAO::find($folderId)['name']);

        // Dean override can rename
        $deanRenamed = FolderDAO::rename($folderId, 1, 'Updated By Dean', true);
        $this->assertTrue($deanRenamed);
        $this->assertEquals('Updated By Dean', FolderDAO::find($folderId)['name']);
    }

    public function testDeleteSafelyUnfilesCertificatesAndRbacIsolation(): void {
        $folderId = FolderDAO::create(2, 'To Be Deleted', 'rose');
        $this->createdFolderIds[] = $folderId;

        $certId = $this->createTestCertificate(2, $folderId, 'PreservedOnDelete');

        // Another faculty user (3) cannot delete user 2's folder
        $unauthorizedDelete = FolderDAO::delete($folderId, 3, false);
        $this->assertFalse($unauthorizedDelete, 'Other faculty must not be able to delete another user folder');
        $this->assertNotNull(FolderDAO::find($folderId));

        // Owner (2) deletes folder
        $deleted = FolderDAO::delete($folderId, 2, false);
        $this->assertTrue($deleted);
        $this->assertNull(FolderDAO::find($folderId), 'Deleted folder must no longer exist');

        // Certificate must NOT be deleted; it must be safely unfiled (folder_id = NULL)
        $cert = CertificateDAO::find($certId);
        $this->assertNotNull($cert, 'Certificate inside deleted folder must not be deleted');
        $this->assertNull($cert['folder_id'], 'Certificate folder_id must be set to NULL on folder deletion');
    }

    public function testMoveCertificatesSingleBulkAndRbac(): void {
        $folderA = FolderDAO::create(2, 'Move Target A', 'blue');
        $folderB = FolderDAO::create(2, 'Move Target B', 'violet');
        $folderOtherUser = FolderDAO::create(3, 'User 3 Folder', 'amber');
        $this->createdFolderIds[] = $folderA;
        $this->createdFolderIds[] = $folderB;
        $this->createdFolderIds[] = $folderOtherUser;

        $cert1 = $this->createTestCertificate(2, null, 'M1');
        $cert2 = $this->createTestCertificate(2, null, 'M2');
        $certOtherUser = $this->createTestCertificate(3, null, 'M3');

        // Single move
        $movedSingle = FolderDAO::moveCertificates([$cert1], $folderA, 2, false);
        $this->assertEquals(1, $movedSingle);
        $this->assertEquals($folderA, (int)CertificateDAO::find($cert1)['folder_id']);

        // Bulk move (includes user 3's cert which must be ignored by RBAC)
        $movedBulk = FolderDAO::moveCertificates([$cert1, $cert2, $certOtherUser], $folderB, 2, false);
        $this->assertEquals(2, $movedBulk, 'Only user 2 owned certificates should be moved');
        $this->assertEquals($folderB, (int)CertificateDAO::find($cert1)['folder_id']);
        $this->assertEquals($folderB, (int)CertificateDAO::find($cert2)['folder_id']);
        $this->assertNull(CertificateDAO::find($certOtherUser)['folder_id']);

        // Cannot move user 2's cert into user 3's folder
        $movedToOthersFolder = FolderDAO::moveCertificates([$cert1], $folderOtherUser, 2, false);
        $this->assertEquals(0, $movedToOthersFolder);
        $this->assertEquals($folderB, (int)CertificateDAO::find($cert1)['folder_id']);

        // Unfile bulk move (null target)
        $unfiledCount = FolderDAO::moveCertificates([$cert1, $cert2], null, 2, false);
        $this->assertEquals(2, $unfiledCount);
        $this->assertNull(CertificateDAO::find($cert1)['folder_id']);
        $this->assertNull(CertificateDAO::find($cert2)['folder_id']);
    }

    public function testCertificateDaoFolderFiltering(): void {
        $folderId = FolderDAO::create(2, 'Filter Test Folder', 'teal');
        $this->createdFolderIds[] = $folderId;

        $certInFolder = $this->createTestCertificate(2, $folderId, 'FilteredIn');
        $certUnfiled = $this->createTestCertificate(2, null, 'FilteredUnfiled');

        // getForFaculty with numeric folder_id
        $inFolderFaculty = CertificateDAO::getForFaculty(2, ['folder_id' => $folderId]);
        $inFolderIds = array_map('intval', array_column($inFolderFaculty, 'id'));
        $this->assertTrue(in_array($certInFolder, $inFolderIds, true));
        $this->assertFalse(in_array($certUnfiled, $inFolderIds, true));

        // getForFaculty with 'unfiled'
        $unfiledFaculty = CertificateDAO::getForFaculty(2, ['folder_id' => 'unfiled']);
        $unfiledIds = array_map('intval', array_column($unfiledFaculty, 'id'));
        $this->assertTrue(in_array($certUnfiled, $unfiledIds, true));
        $this->assertFalse(in_array($certInFolder, $unfiledIds, true));

        // getAll with numeric folder_id
        $inFolderAll = CertificateDAO::getAll(['folder_id' => $folderId]);
        $inFolderAllIds = array_map('intval', array_column($inFolderAll, 'id'));
        $this->assertTrue(in_array($certInFolder, $inFolderAllIds, true));
        $this->assertFalse(in_array($certUnfiled, $inFolderAllIds, true));

        // getAll with 'unfiled'
        $unfiledAll = CertificateDAO::getAll(['folder_id' => 'unfiled']);
        $unfiledAllIds = array_map('intval', array_column($unfiledAll, 'id'));
        $this->assertTrue(in_array($certUnfiled, $unfiledAllIds, true));
        $this->assertFalse(in_array($certInFolder, $unfiledAllIds, true));
    }
}
