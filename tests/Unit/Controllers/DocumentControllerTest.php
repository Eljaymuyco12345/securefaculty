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
}
