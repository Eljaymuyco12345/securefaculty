<?php
/**
 * CertificateDAO Micro-Unit Test
 * Tests CertificateDAO persistence operations, co-authorship relationships,
 * duplicate detection, quota management, and multi-user deletion policies.
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/DAO/CertificateDAO.php';

class CertificateDAOTest extends TestCase {
    private array $createdCertIds = [];
    private string $tempEncFile = '';

    public function setUp(): void {
        parent::setUp();
        $this->tempEncFile = 'test_cert_' . bin2hex(random_bytes(8)) . '.enc';
        file_put_contents(VAULT_DIR . DIRECTORY_SEPARATOR . $this->tempEncFile, "DUMMY_ENCRYPTED_DATA");
    }

    public function tearDown(): void {
        foreach ($this->createdCertIds as $id) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
            $stmt->execute([$id]);
        }
        $this->createdCertIds = [];

        $vaultPath = VAULT_DIR . DIRECTORY_SEPARATOR . $this->tempEncFile;
        if (file_exists($vaultPath)) {
            @unlink($vaultPath);
        }
        parent::tearDown();
    }

    public function testCreateAndFindCertificate(): void {
        $hash = hash('sha256', 'test_data_' . microtime(true));
        $certData = [
            'faculty_id' => 2, // Mikhail
            'title' => 'Unit Test Advanced Cryptographic Systems',
            'issuer' => 'ISUFST Testing Authority',
            'date_issued' => '2026-04-15',
            'category' => 'Research',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'crypto_systems.pdf',
            'file_type' => 'pdf',
            'file_size' => 2048,
            'file_hash' => $hash,
            'cert_number' => 'CERT-UNIT-' . rand(1000, 9999),
            'ocr_raw_text' => 'Sample OCR text for certificate',
            'ocr_confidence' => 0.98,
            'status' => 'approved',
            'processing_status' => 'completed'
        ];

        // Author is Mikhail (2), Co-author is Rae Jan (3)
        $certId = CertificateDAO::create($certData, [3]);
        $this->createdCertIds[] = $certId;
        $this->assertGreaterThan(0, $certId);

        $found = CertificateDAO::find($certId);
        $this->assertNotNull($found);
        $this->assertEquals($certData['title'], $found['title']);
        $this->assertEquals($certData['issuer'], $found['issuer']);
        $this->assertEquals($certData['date_issued'], $found['date_issued']);
        $this->assertEquals('pdf', $found['file_type']);

        // Find by SHA-256 Hash
        $byHash = CertificateDAO::findByHash($hash);
        $this->assertNotNull($byHash);
        $this->assertEquals($certId, (int)$byHash['id']);

        // Author and Co-author checks
        $this->assertTrue(CertificateDAO::isAuthorOrCoAuthor($certId, 2), "Primary author must return true");
        $this->assertTrue(CertificateDAO::isAuthorOrCoAuthor($certId, 3), "Co-author must return true");
        $this->assertFalse(CertificateDAO::isAuthorOrCoAuthor($certId, 99999), "Unrelated user must return false");
    }

    public function testFindDuplicateByTitleAndDate(): void {
        $title = 'Duplicate Check Certificate ' . rand(100, 999);
        $date = '2026-03-10';

        $certId = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => $title,
            'issuer' => 'ISUFST Test Dept',
            'date_issued' => $date,
            'category' => 'Seminar',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'dup_test.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', $title)
        ]);
        $this->createdCertIds[] = $certId;

        $dup = CertificateDAO::findDuplicateByTitleAndDate(2, $title, $date);
        $this->assertNotNull($dup);
        $this->assertEquals($certId, (int)$dup['id']);

        // Different date returns null
        $dupDiffDate = CertificateDAO::findDuplicateByTitleAndDate(2, $title, '2025-01-01');
        $this->assertNull($dupDiffDate);
    }

    public function testGetByIdsWithFacultyScoping(): void {
        $certId = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => 'Scoped Cert Test',
            'issuer' => 'ISUFST',
            'date_issued' => '2026-02-01',
            'category' => 'Workshop',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'scoped.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'scoped_' . time())
        ], [3]); // Co-author is 3
        $this->createdCertIds[] = $certId;

        // Query by author (2)
        $certs2 = CertificateDAO::getByIds([$certId], 2);
        $this->assertCount(1, $certs2);

        // Query by co-author (3)
        $certs3 = CertificateDAO::getByIds([$certId], 3);
        $this->assertCount(1, $certs3);

        // Query by unauthorized faculty (4 or non-author)
        $certsOther = CertificateDAO::getByIds([$certId], 99999);
        $this->assertCount(0, $certsOther, "Non-author must not receive scoped record");
    }

    public function testCoAuthorDisassociationDeletionPolicy(): void {
        $certId = CertificateDAO::create([
            'faculty_id' => 2, // Primary author
            'title' => 'Disassociation Policy Test',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'disassoc.pdf',
            'file_type' => 'pdf',
            'file_size' => 512,
            'file_hash' => hash('sha256', 'disassoc_' . time())
        ], [3]); // Co-author 3
        $this->createdCertIds[] = $certId;

        // Co-author 3 calls delete
        $coAuthorDeleteOk = CertificateDAO::delete($certId, 3, false);
        $this->assertTrue($coAuthorDeleteOk);

        // Record must still exist in DB
        $stillExists = CertificateDAO::find($certId);
        $this->assertNotNull($stillExists, "Certificate must remain in vault when co-author deletes");

        // Co-author mapping must be removed
        $this->assertFalse(CertificateDAO::isAuthorOrCoAuthor($certId, 3), "Co-author association must be removed");
        // Primary author must remain associated
        $this->assertTrue(CertificateDAO::isAuthorOrCoAuthor($certId, 2), "Primary author must remain associated");
    }

    public function testPrimaryAuthorFullPurgeDeletionPolicy(): void {
        $vaultFile = VAULT_DIR . DIRECTORY_SEPARATOR . $this->tempEncFile;
        $certId = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => 'Full Purge Test',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'purge.pdf',
            'file_type' => 'pdf',
            'file_size' => 512,
            'file_hash' => hash('sha256', 'purge_' . time())
        ], [3]);

        $this->assertTrue(file_exists($vaultFile));

        // Primary author 2 calls delete
        $delOk = CertificateDAO::delete($certId, 2, false);
        $this->assertTrue($delOk);

        // Record must be gone
        $this->assertNull(CertificateDAO::find($certId), "Certificate record must be purged");
        // Encrypted file on disk must be unlinked
        $this->assertFalse(file_exists($vaultFile), "Encrypted file on disk must be unlinked");
    }

    public function testQuotaIncrement(): void {
        $initial = CertificateDAO::getQuotaStatus();
        CertificateDAO::incrementQuota();
        $after = CertificateDAO::getQuotaStatus();

        $this->assertEquals($initial['current_usage'] + 1, $after['current_usage'], "Usage count must increment by 1");
    }
}
