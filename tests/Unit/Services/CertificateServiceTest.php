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

    public function testCommitDocxNormalizesFileTypeAndFilenameToPdf(): void {
        $tempPdf = 'staged_docx_conv_' . bin2hex(random_bytes(6)) . '.pdf';
        file_put_contents(TEMP_DIR . DIRECTORY_SEPARATOR . $tempPdf, "%PDF-1.4 Converted Docx Test " . time());

        $input = [
            'temp_filename' => $tempPdf,
            'title' => 'Docx Normalized Certificate',
            'issuer' => 'CHED Philippines',
            'date_issued' => '2026-06-20',
            'category' => 'Award',
            'original_name' => 'Original_Faculty_Award.docx'
        ];

        $res = CertificateService::commitCertificate($input, 2, false);
        $this->assertTrue($res['success']);
        $certId = (int)$res['certificate_id'];
        $this->createdCerts[] = $certId;

        $cert = CertificateDAO::find($certId);
        $this->assertEquals('pdf', $cert['file_type'], "Committed file_type must be normalized to pdf");
        $this->assertEquals('Original_Faculty_Award.pdf', $cert['original_filename'], "Original filename must end in .pdf");
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

    public function testPurgeStaleTempFilesProtectsDemoAndHiddenFiles(): void {
        $demoFile = TEMP_DIR . DIRECTORY_SEPARATOR . 'sample_demo_fixture_test.pdf';
        file_put_contents($demoFile, "%PDF-1.4 DEMO FIXTURE");
        touch($demoFile, time() - 7200);

        CertificateService::purgeStaleTempFiles(3600);
        $this->assertTrue(file_exists($demoFile), "Sample demo fixtures must be shielded from automatic purging");
        @unlink($demoFile);
    }

    public function testCommitCertificateDuplicateHashInterception(): void {
        $dupFile = 'staged_dup_' . bin2hex(random_bytes(4)) . '.pdf';
        $dupPath = TEMP_DIR . DIRECTORY_SEPARATOR . $dupFile;
        file_put_contents($dupPath, "%PDF-1.4 Duplicate Test Content " . time());

        $input = [
            'temp_filename' => $dupFile,
            'title' => 'Original Cert Title',
            'issuer' => 'ISUFST',
            'date_issued' => '2026-01-10',
            'category' => 'Seminar',
            'original_name' => 'orig.pdf'
        ];

        $res1 = CertificateService::commitCertificate($input, 2, false);
        $this->assertTrue($res1['success']);
        $this->createdCerts[] = (int)$res1['certificate_id'];

        // Re-create the same file content to simulate duplicate upload attempt
        file_put_contents($dupPath, "%PDF-1.4 Duplicate Test Content " . time());
        $input2 = [
            'temp_filename' => $dupFile,
            'title' => 'Another Title',
            'original_name' => 'another.pdf'
        ];

        $res2 = CertificateService::commitCertificate($input2, 2, false);
        $this->assertFalse($res2['success']);
        $this->assertEquals(409, $res2['code']);
        $this->assertEquals('duplicate_detected', $res2['status']);
        @unlink($dupPath);
    }

    public function testCommitCertificateWithAccreditationFields(): void {
        $tempFile = 'staged_accred_' . bin2hex(random_bytes(6)) . '.pdf';
        file_put_contents(TEMP_DIR . DIRECTORY_SEPARATOR . $tempFile, "%PDF-1.4 Accred Commit Test " . time());

        $input = [
            'temp_filename' => $tempFile,
            'title' => 'Accreditation Commit Verification',
            'issuer' => 'Springer Nature',
            'date_issued' => '2026-07-20',
            'category' => 'Research Publication',
            'original_name' => 'springer_pub.pdf',
            'scope' => 'International',
            'indexing' => 'Scopus',
            'place_issued' => 'Berlin, Germany',
            'authors' => 'Prof. Mikhail Aguilar',
            'accreditation_area' => 'Area V: Research'
        ];

        $res = CertificateService::commitCertificate($input, 2, false);
        $this->assertTrue($res['success']);
        $certId = (int)$res['certificate_id'];
        $this->createdCerts[] = $certId;

        $cert = CertificateDAO::find($certId);
        $this->assertNotNull($cert);
        $this->assertEquals('International', $cert['scope']);
        $this->assertEquals('Scopus', $cert['indexing']);
        $this->assertEquals('Berlin, Germany', $cert['place_issued']);
        $this->assertEquals('Prof. Mikhail Aguilar', $cert['authors']);
        $this->assertEquals('Area V: Research', $cert['accreditation_area']);
    }

    public function testCommitCertificateInfersAccreditationAreaWhenEmpty(): void {
        $tempFile = 'staged_infer_' . bin2hex(random_bytes(6)) . '.pdf';
        file_put_contents(TEMP_DIR . DIRECTORY_SEPARATOR . $tempFile, "%PDF-1.4 Infer Test " . time());

        $input = [
            'temp_filename' => $tempFile,
            'title' => 'Inferred Area Test',
            'issuer' => 'ISUFST',
            'date_issued' => '2026-07-21',
            'category' => 'Instructional Material',
            'original_name' => 'module.pdf',
            'scope' => 'Institutional',
            'indexing' => 'None',
            'accreditation_area' => '' // Empty: should be inferred automatically
        ];

        $res = CertificateService::commitCertificate($input, 2, false);
        $this->assertTrue($res['success']);
        $certId = (int)$res['certificate_id'];
        $this->createdCerts[] = $certId;

        $cert = CertificateDAO::find($certId);
        $this->assertNotNull($cert);
        $this->assertEquals('Area III: Curriculum & Instruction', $cert['accreditation_area']);
    }
}
