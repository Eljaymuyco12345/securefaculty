<?php
/**
 * DocumentController Micro-Unit Test
 * Tests DocumentController class declaration, routing contract, and permission delegation
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/DocumentController.php';

class DocumentControllerTest extends TestCase {
    public function testClassStructureAndInheritance(): void {
        $ref = new ReflectionClass('DocumentController');
        $this->assertTrue($ref->isSubclassOf('BaseController'), "DocumentController must inherit from BaseController");
        $this->assertTrue($ref->hasMethod('view'), "DocumentController must provide view() method");

        $viewMethod = $ref->getMethod('view');
        $this->assertTrue($viewMethod->isPublic(), "view() method must be public");
    }

    public function testViewNonExistentRecordReturns404(): void {
        AuthService::initSession();
        AuthService::attempt('dean', 'admin123');

        $result = CertificateService::retrieveDocument(-99999, 1, true);
        $this->assertFalse($result['success']);
        $this->assertEquals(404, $result['code']);
    }

    public function testPythonPdfConversionEngineIntegration(): void {
        $controller = new class extends BaseController {
            public function getPy(): string { return $this->getPythonBinary(); }
        };
        $pythonBin = $controller->getPy();

        $tempDocx = TEMP_DIR . DIRECTORY_SEPARATOR . 'test_integration_' . bin2hex(random_bytes(4)) . '.docx';
        $tempPdf = TEMP_DIR . DIRECTORY_SEPARATOR . 'test_integration_' . bin2hex(random_bytes(4)) . '.pdf';

        $createCmd = sprintf(
            '%s -c "import docx; d = docx.Document(); d.add_heading(\'Docx to PDF Test\', 0); d.save(r\'%s\')"',
            escapeshellcmd($pythonBin),
            $tempDocx
        );
        shell_exec($createCmd);

        $this->assertTrue(file_exists($tempDocx), "Fixture docx must be created");

        $convertCmd = sprintf(
            '%s %s --convert-to-pdf %s --out %s 2>&1',
            escapeshellcmd($pythonBin),
            escapeshellarg(PYTHON_ENGINE_PATH),
            escapeshellarg($tempDocx),
            escapeshellarg($tempPdf)
        );
        shell_exec($convertCmd);

        $this->assertTrue(file_exists($tempPdf), "Converted PDF must exist");
        $this->assertGreaterThan(500, filesize($tempPdf), "Converted PDF must be non-empty");

        $header = file_get_contents($tempPdf, false, null, 0, 5);
        $this->assertEquals('%PDF-', $header, "Converted file must start with PDF header %PDF-");

        if (file_exists($tempDocx)) @unlink($tempDocx);
        if (file_exists($tempPdf)) @unlink($tempPdf);
    }

    public function testResolveDeliveryPayloadSupportsDocxAndPdfForDocxOrigin(): void {
        $controller = new DocumentController();
        $sampleDocxPath = __DIR__ . '/../../../sample_certificate_anjo_cabalum.docx';
        $this->assertTrue(file_exists($sampleDocxPath), "Sample DOCX fixture must exist");
        $docxBytes = file_get_contents($sampleDocxPath);

        $cert = [
            'id' => 42,
            'file_type' => 'docx',
            'original_filename' => 'faculty_award.docx',
            'title' => 'Faculty Award'
        ];

        // 1. Download as original DOCX
        $docxRes = $controller->resolveDeliveryPayload($cert, $docxBytes, true, 'docx');
        $this->assertTrue($docxRes['success']);
        $this->assertEquals('DOCX', $docxRes['format_label']);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $docxRes['mime_type']);
        $this->assertEquals('faculty_award.docx', $docxRes['filename']);
        $this->assertEquals('attachment', $docxRes['disposition']);
        $this->assertEquals($docxBytes, $docxRes['bytes'], "Original DOCX bytes must be preserved untouched");

        // 2. Download/view as JIT-converted PDF
        $pdfRes = $controller->resolveDeliveryPayload($cert, $docxBytes, true, 'pdf');
        $this->assertTrue($pdfRes['success']);
        $this->assertEquals('PDF', $pdfRes['format_label']);
        $this->assertEquals('application/pdf', $pdfRes['mime_type']);
        $this->assertEquals('faculty_award.pdf', $pdfRes['filename']);
        $this->assertEquals('%PDF-', substr($pdfRes['bytes'], 0, 5), "PDF delivery must produce valid PDF header");
    }

    public function testResolveDeliveryPayloadRejectsDocxForPdfOrigin(): void {
        $controller = new DocumentController();
        $cert = [
            'id' => 99,
            'file_type' => 'pdf',
            'original_filename' => 'scanned_cert.pdf',
            'title' => 'Scanned Cert'
        ];

        $res = $controller->resolveDeliveryPayload($cert, '%PDF-1.4 dummy', true, 'docx');
        $this->assertFalse($res['success'], "PDF-origin records must reject DOCX download");
        $this->assertEquals(400, $res['code']);
    }
}
