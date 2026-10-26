<?php
/**
 * BaseController Micro-Unit Test
 * Tests BaseController contract, architectural reflections, and helper signatures
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Controllers/BaseController.php';

class TestableConcreteController extends BaseController {
    public function callRenderView(string $view, array $data = []): void {
        $this->renderView($view, $data);
    }
}

class BaseControllerTest extends TestCase {
    public function testBaseControllerIsAbstractClass(): void {
        $ref = new ReflectionClass('BaseController');
        $this->assertTrue($ref->isAbstract(), "BaseController must be declared abstract");
    }

    public function testBaseControllerDeclaresProtectedMethods(): void {
        $ref = new ReflectionClass('BaseController');

        $this->assertTrue($ref->hasMethod('renderView'));
        $renderMethod = $ref->getMethod('renderView');
        $this->assertTrue($renderMethod->isProtected());

        $this->assertTrue($ref->hasMethod('jsonResponse'));
        $jsonMethod = $ref->getMethod('jsonResponse');
        $this->assertTrue($jsonMethod->isProtected());

        $this->assertTrue($ref->hasMethod('redirect'));
        $redirectMethod = $ref->getMethod('redirect');
        $this->assertTrue($redirectMethod->isProtected());
    }

    public function testRenderViewHandlesMissingViewSafely(): void {
        $controller = new TestableConcreteController();
        ob_start();
        $controller->callRenderView('non_existent_view_file_xyz.php', []);
        $output = ob_get_clean();

        $this->assertStringContains("View file not found", $output);
        if (!headers_sent()) {
            $this->assertEquals(500, http_response_code());
        }
    }
}
