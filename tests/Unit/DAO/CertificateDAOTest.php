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

    public function testDateIssuedRangeFilterAccuracy(): void {
        $uniqueTag = 'DATE_FILTER_TEST_' . bin2hex(random_bytes(4));

        // 1. Target: In-range certificate (2026-05-15)
        $id1 = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => "Target In-Range Certificate {$uniqueTag}",
            'date_issued' => '2026-05-15',
            'category' => 'Research Publication',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'target.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'target_' . time() . rand(1, 99999))
        ]);
        $this->createdCertIds[] = $id1;

        // 2. Earlier: Out-of-range certificate (2025-11-20)
        $id2 = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => "Earlier Out-of-Range Certificate {$uniqueTag}",
            'date_issued' => '2025-11-20',
            'category' => 'Research Publication',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'earlier.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'earlier_' . time() . rand(1, 99999))
        ]);
        $this->createdCertIds[] = $id2;

        // 3. Later: Out-of-range certificate (2026-09-10)
        $id3 = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => "Later Out-of-Range Certificate {$uniqueTag}",
            'date_issued' => '2026-09-10',
            'category' => 'Research Publication',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'later.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'later_' . time() . rand(1, 99999))
        ]);
        $this->createdCertIds[] = $id3;

        // Verification A: Range May 2026 (2026-05-01 to 2026-05-31)
        $results = CertificateDAO::getAll('All', $uniqueTag, null, '2026-05-01', '2026-05-31');
        $resultIds = array_column($results, 'id');
        $this->assertContains($id1, $resultIds, "Target certificate (2026-05-15) must be included in May 2026 range");
        $this->assertFalse(in_array($id2, $resultIds, true), "Earlier certificate (2025-11-20) must be excluded from May 2026 range");
        $this->assertFalse(in_array($id3, $resultIds, true), "Later certificate (2026-09-10) must be excluded from May 2026 range");

        // Verification B: Exact single-day boundary match (2026-05-15 to 2026-05-15)
        $boundaryResults = CertificateDAO::getAll('All', $uniqueTag, null, '2026-05-15', '2026-05-15');
        $boundaryIds = array_column($boundaryResults, 'id');
        $this->assertContains($id1, $boundaryIds, "Boundary match on exact issued date must succeed");
        $this->assertFalse(in_array($id2, $boundaryIds, true), "Earlier date must not match boundary");
        $this->assertFalse(in_array($id3, $boundaryIds, true), "Later date must not match boundary");

        // Verification C: Range for previous year (2025-01-01 to 2025-12-31)
        $pastResults = CertificateDAO::getAll('All', $uniqueTag, null, '2025-01-01', '2025-12-31');
        $pastIds = array_column($pastResults, 'id');
        $this->assertContains($id2, $pastIds, "Earlier certificate (2025-11-20) must be returned in 2025 range");
        $this->assertFalse(in_array($id1, $pastIds, true), "Target certificate must not appear in 2025 range");
        $this->assertFalse(in_array($id3, $pastIds, true), "Later certificate must not appear in 2025 range");
    }

    public function testCreateCertificateWithAccreditationFields(): void {
        $hash = hash('sha256', 'accred_test_' . microtime(true) . rand(1, 99999));
        $certData = [
            'faculty_id' => 2, // Mikhail
            'title' => 'Accreditation Compliant Research Study',
            'issuer' => 'ISUFST Research Center',
            'date_issued' => '2026-06-20',
            'category' => 'Research Publication',
            'scope' => 'International',
            'indexing' => 'Scopus',
            'place_issued' => 'Tokyo, Japan',
            'authors' => 'Prof. Levy Mikhail B. Aguilar, Prof. Rae Jan B. Corneja',
            'accreditation_area' => 'Area V: Research',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'tokyo_research_study.pdf',
            'file_type' => 'pdf',
            'file_size' => 4096,
            'file_hash' => $hash,
            'cert_number' => 'ACCRED-2026-001',
            'ocr_raw_text' => 'Full study text for accreditation review',
            'ocr_confidence' => 0.99,
            'status' => 'approved',
            'processing_status' => 'completed'
        ];

        $certId = CertificateDAO::create($certData, [3]);
        $this->createdCertIds[] = $certId;
        $this->assertGreaterThan(0, $certId);

        $found = CertificateDAO::find($certId);
        $this->assertNotNull($found);
        $this->assertEquals($certData['title'], $found['title']);
        $this->assertEquals('International', $found['scope']);
        $this->assertEquals('Scopus', $found['indexing']);
        $this->assertEquals('Tokyo, Japan', $found['place_issued']);
        $this->assertEquals('Prof. Levy Mikhail B. Aguilar, Prof. Rae Jan B. Corneja', $found['authors']);
        $this->assertEquals('Area V: Research', $found['accreditation_area']);
    }

    public function testFilterByScopeInGetForFacultyAndGetAll(): void {
        $uniqueTag = 'SCOPE_FILTER_' . bin2hex(random_bytes(4));

        // Create International certificate
        $idIntl = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => "International Conference {$uniqueTag}",
            'date_issued' => '2026-07-01',
            'category' => 'Research Publication',
            'scope' => 'International',
            'indexing' => 'Scopus',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'intl.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'intl_' . time() . rand(1, 99999))
        ]);
        $this->createdCertIds[] = $idIntl;

        // Create National certificate
        $idNatl = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => "National Symposium {$uniqueTag}",
            'date_issued' => '2026-07-02',
            'category' => 'Seminar',
            'scope' => 'National',
            'indexing' => 'None',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'natl.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'natl_' . time() . rand(1, 99999))
        ]);
        $this->createdCertIds[] = $idNatl;

        // Create Regional certificate
        $idReg = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => "Regional Workshop {$uniqueTag}",
            'date_issued' => '2026-07-03',
            'category' => 'Workshop',
            'scope' => 'Regional',
            'indexing' => 'None',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'reg.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'reg_' . time() . rand(1, 99999))
        ]);
        $this->createdCertIds[] = $idReg;

        // 1. Test CertificateDAO::getForFaculty() with scope = 'International'
        $facultyIntl = CertificateDAO::getForFaculty(2, ['scope' => 'International', 'search' => $uniqueTag]);
        $facultyIntlIds = array_column($facultyIntl, 'id');
        $this->assertContains($idIntl, $facultyIntlIds, "International cert must be returned when filtering for faculty by scope=International");
        $this->assertFalse(in_array($idNatl, $facultyIntlIds, true), "National cert must be excluded");
        $this->assertFalse(in_array($idReg, $facultyIntlIds, true), "Regional cert must be excluded");

        // 2. Test CertificateDAO::getForFaculty() with scope = 'National'
        $facultyNatl = CertificateDAO::getForFaculty(2, ['scope' => 'National', 'search' => $uniqueTag]);
        $facultyNatlIds = array_column($facultyNatl, 'id');
        $this->assertContains($idNatl, $facultyNatlIds);
        $this->assertFalse(in_array($idIntl, $facultyNatlIds, true));

        // 3. Test CertificateDAO::getAll() with scope = 'Regional' (via array filter)
        $allReg = CertificateDAO::getAll(['scope' => 'Regional', 'search' => $uniqueTag]);
        $allRegIds = array_column($allReg, 'id');
        $this->assertContains($idReg, $allRegIds, "Regional cert must be returned in getAll()");
        $this->assertFalse(in_array($idIntl, $allRegIds, true));
        $this->assertFalse(in_array($idNatl, $allRegIds, true));

        // 4. Test CertificateDAO::getAll() with positional parameter scope = 'International'
        $allIntlPositional = CertificateDAO::getAll('All', $uniqueTag, null, null, null, null, 'International');
        $allIntlIds = array_column($allIntlPositional, 'id');
        $this->assertContains($idIntl, $allIntlIds);
        $this->assertFalse(in_array($idNatl, $allIntlIds, true));
    }

    public function testFilterByIndexingInGetForFacultyAndGetAll(): void {
        $uniqueTag = 'INDEX_FILTER_' . bin2hex(random_bytes(4));

        // Create Scopus indexed record
        $idScopus = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => "Scopus Journal Article {$uniqueTag}",
            'date_issued' => '2026-08-01',
            'category' => 'Research Publication',
            'scope' => 'International',
            'indexing' => 'Scopus',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'scopus.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'scopus_' . time() . rand(1, 99999))
        ]);
        $this->createdCertIds[] = $idScopus;

        // Create Web of Science indexed record
        $idWos = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => "WoS Journal Article {$uniqueTag}",
            'date_issued' => '2026-08-02',
            'category' => 'Research Publication',
            'scope' => 'International',
            'indexing' => 'Web of Science',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'wos.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'wos_' . time() . rand(1, 99999))
        ]);
        $this->createdCertIds[] = $idWos;

        // Create Peer-Reviewed record
        $idPeer = CertificateDAO::create([
            'faculty_id' => 2,
            'title' => "Refereed Article {$uniqueTag}",
            'date_issued' => '2026-08-03',
            'category' => 'Research Publication',
            'scope' => 'National',
            'indexing' => 'Peer-Reviewed',
            'file_path' => $this->tempEncFile,
            'original_filename' => 'peer.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'file_hash' => hash('sha256', 'peer_' . time() . rand(1, 99999))
        ]);
        $this->createdCertIds[] = $idPeer;

        // 1. Test CertificateDAO::getForFaculty() with indexing = 'Scopus'
        $facultyScopus = CertificateDAO::getForFaculty(2, ['indexing' => 'Scopus', 'search' => $uniqueTag]);
        $scopusIds = array_column($facultyScopus, 'id');
        $this->assertContains($idScopus, $scopusIds, "Scopus record must be found in getForFaculty()");
        $this->assertFalse(in_array($idWos, $scopusIds, true), "WoS record must be excluded");
        $this->assertFalse(in_array($idPeer, $scopusIds, true), "Peer-reviewed record must be excluded");

        // 2. Test CertificateDAO::getForFaculty() with indexing = 'Web of Science'
        $facultyWos = CertificateDAO::getForFaculty(2, ['indexing' => 'Web of Science', 'search' => $uniqueTag]);
        $wosIds = array_column($facultyWos, 'id');
        $this->assertContains($idWos, $wosIds);
        $this->assertFalse(in_array($idScopus, $wosIds, true));

        // 3. Test CertificateDAO::getAll() with indexing = 'Peer-Reviewed' (via array filter)
        $allPeer = CertificateDAO::getAll(['indexing' => 'Peer-Reviewed', 'search' => $uniqueTag]);
        $allPeerIds = array_column($allPeer, 'id');
        $this->assertContains($idPeer, $allPeerIds, "Peer-reviewed record must be found in getAll()");
        $this->assertFalse(in_array($idScopus, $allPeerIds, true));
        $this->assertFalse(in_array($idWos, $allPeerIds, true));

        // 4. Test CertificateDAO::getAll() with positional parameter indexing = 'Scopus'
        $allScopusPositional = CertificateDAO::getAll('All', $uniqueTag, null, null, null, null, null, 'Scopus');
        $allScopusIds = array_column($allScopusPositional, 'id');
        $this->assertContains($idScopus, $allScopusIds);
        $this->assertFalse(in_array($idWos, $allScopusIds, true));
    }
}
