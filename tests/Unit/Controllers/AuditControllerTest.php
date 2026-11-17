<?php
/**
 * AuditController Micro-Unit Test
 * Tests AuditController ledger rendering, statistics aggregation, and compliance reporting
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/AuditController.php';

class TestableAuditController extends AuditController {
    public ?string $renderedView = null;
    public array $renderedData = [];

    protected function renderView(string $viewFile, array $data = []): void {
        $this->renderedView = $viewFile;
        $this->renderedData = $data;
    }
}

class AuditControllerTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        AuthService::initSession();
        $_SESSION = [];
    }

    public function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testIndexRendersAuditViewForDean(): void {
        AuthService::attempt('dean', 'admin123');

        $controller = new TestableAuditController();
        $_GET['format'] = 'html';
        $controller->index();

        $this->assertEquals('audit/index.php', $controller->renderedView);
        $this->assertArrayHasKey('displayLogs', $controller->renderedData);
        $this->assertArrayHasKey('totalLogs', $controller->renderedData);
        $this->assertArrayHasKey('statTotal', $controller->renderedData);
        $this->assertArrayHasKey('statLogins', $controller->renderedData);
        $this->assertArrayHasKey('statDocs', $controller->renderedData);
        $this->assertArrayHasKey('statAlerts', $controller->renderedData);
        $this->assertArrayHasKey('facultyLookup', $controller->renderedData);
        $this->assertEquals('audit', $controller->renderedData['activeNav']);
    }

    public function testAuditPdfGeneratorIntegration(): void {
        $controller = new class extends BaseController {
            public function getPy(): string { return $this->getPythonBinary(); }
        };
        $pythonBin = $controller->getPy();

        $tempJson = TEMP_DIR . DIRECTORY_SEPARATOR . 'test_audit_' . bin2hex(random_bytes(4)) . '.json';
        $tempPdf = TEMP_DIR . DIRECTORY_SEPARATOR . 'test_audit_' . bin2hex(random_bytes(4)) . '.pdf';

        $payload = [
            'meta' => [
                'action_filter' => 'All Events',
                'search' => '',
                'auditor_name' => 'Dr. Arlene S. Poblador',
                'generated_at' => date('F d, Y \a\t h:i A')
            ],
            'records' => [
                [
                    'id' => 1,
                    'created_at' => '2026-11-15 17:21:53',
                    'user_id' => 'ISUFST001',
                    'username' => 'dean',
                    'role' => 'DEAN',
                    'action' => 'LOGIN_SUCCESS',
                    'faculty_display' => 'Dr. Arlene S. Poblador',
                    'details' => 'Test session login',
                    'ip_address' => '127.0.0.1',
                    'integrity' => 'SHA-256 Validated'
                ]
            ]
        ];
        file_put_contents($tempJson, json_encode($payload));

        $generatorScript = BASE_DIR . DIRECTORY_SEPARATOR . 'python_engine' . DIRECTORY_SEPARATOR . 'generate_audit_pdf.py';
        $cmd = sprintf(
            '%s %s %s %s 2>&1',
            escapeshellcmd($pythonBin),
            escapeshellarg($generatorScript),
            escapeshellarg($tempJson),
            escapeshellarg($tempPdf)
        );
        shell_exec($cmd);

        $this->assertTrue(file_exists($tempPdf), "Audit report PDF must be generated");
        $this->assertGreaterThan(500, filesize($tempPdf), "Audit report PDF must not be empty");

        $header = file_get_contents($tempPdf, false, null, 0, 5);
        $this->assertEquals('%PDF-', $header, "Generated audit ledger must start with %PDF-");

        if (file_exists($tempJson)) @unlink($tempJson);
        if (file_exists($tempPdf)) @unlink($tempPdf);
    }
}
