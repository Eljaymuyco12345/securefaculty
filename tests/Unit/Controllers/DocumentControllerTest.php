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
}
