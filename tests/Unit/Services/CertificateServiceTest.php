<?php
/**
 * CertificateService Micro-Unit Test
 * Tests CertificateService orchestration, commit lifecycle, authorization, and temp file purges
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Services/CertificateService.php';

class CertificateServiceTest extends TestCase {
    private array $createdCerts = [];
    private string $tempStagedFile = '';

    public function setUp(): void {
        parent::setUp();
        $this->tempStagedFile = 'staged_test_' . bin2hex(random_bytes(6)) . '.pdf';
        file_put_contents(TEMP_DIR . DIRECTORY_SEPARATOR . $this->tempStagedFile, "%PDF-1.4 Unit Test Certificate File Content " . time());
    }

    public function tearDown(): void {
        $stagedPath = TEMP_DIR . DIRECTORY_SEPARATOR . $this->tempStagedFile;
        if (file_exists($stagedPath)) {
            @unlink($stagedPath);
        }

        foreach ($this->createdCerts as $id) {
            $cert = CertificateDAO::find($id);
            if ($cert && !empty($cert['file_path'])) {
                $encPath = VAULT_DIR . DIRECTORY_SEPARATOR . $cert['file_path'];
                if (file_exists($encPath)) @unlink($encPath);
            }
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
            $stmt->execute([$id]);
        }
        $this->createdCerts = [];
        parent::tearDown();
    }

    public function testCommitCertificateSuccess(): void {
        $input = [
            'temp_filename' => $this->tempStagedFile,
            'title' => 'Service Commit Test Cert',
            'issuer' => 'ISUFST Testing Board',
            'date_issued' => '2026-05-15',
            'category' => 'Training',
            'original_name' => 'service_test.pdf'
        ];

        $res = CertificateService::commitCertificate($input, 2, false);
        $this->assertTrue($res['success']);
        $this->assertArrayHasKey('certificate_id', $res);
        $this->assertArrayHasKey('file_hash', $res);

        $certId = (int)$res['certificate_id'];
        $this->createdCerts[] = $certId;

        // Verify temp file was unlinked (Zero Plaintext Persistence)
        $this->assertFalse(file_exists(TEMP_DIR . DIRECTORY_SEPARATOR . $this->tempStagedFile), "Staged plaintext file must be removed");

        // Verify retrieval for author (Mikhail, ID 2)
        $retrieve = CertificateService::retrieveDocument($certId, 2, false);
        $this->assertTrue($retrieve['success']);
        $this->assertStringContains("Unit Test Certificate File Content", $retrieve['bytes']);

        // Verify retrieval for Dean (ID 1)
        $retrieveDean = CertificateService::retrieveDocument($certId, 1, true);
        $this->assertTrue($retrieveDean['success']);

        // Verify retrieval blocked for unauthorized user (ID 99999)
        $retrieveOther = CertificateService::retrieveDocument($certId, 99999, false);
        $this->assertFalse($retrieveOther['success']);
        $this->assertEquals(403, $retrieveOther['code']);
    }

    public function testCommitCertificateMissingFileReturns404(): void {
        $res = CertificateService::commitCertificate([
            'temp_filename' => 'definitely_non_existent_file.pdf'
        ], 2, false);

        $this->assertFalse($res['success']);
        $this->assertEquals(404, $res['code']);
    }

    public function testRetrieveNonExistentDocumentReturns404(): void {
        $res = CertificateService::retrieveDocument(-99999, 1, true);
        $this->assertFalse($res['success']);
        $this->assertEquals(404, $res['code']);
    }

    public function testPurgeStaleTempFiles(): void {
        $staleFile = TEMP_DIR . DIRECTORY_SEPARATOR . 'stale_test_' . bin2hex(random_bytes(4)) . '.tmp';
        file_put_contents($staleFile, "STALE_CONTENT");
        // Backdate file modification time by 2 hours (7200s)
        touch($staleFile, time() - 7200);

        $purged = CertificateService::purgeStaleTempFiles(3600);
        $this->assertGreaterThan(0, $purged);
        $this->assertFalse(file_exists($staleFile), "Stale temp file must be unlinked");
    }
}
