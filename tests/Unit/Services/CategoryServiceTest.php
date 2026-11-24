<?php
/**
 * CategoryService Micro-Unit Test
 * Tests centralized academic categories, accreditation area mappings,
 * scope options, indexing tiers, badges, and alias normalization.
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Services/CategoryService.php';

class CategoryServiceTest extends TestCase {
    /**
     * Test mapping from category to AACCUP / CHED accreditation areas
     */
    public function testGetAccreditationArea(): void {
        // Research categories -> Area V
        $this->assertEquals('Area V: Research', CategoryService::getAccreditationArea('Research Publication'));
        $this->assertEquals('Area V: Research', CategoryService::getAccreditationArea('research publication'));
        $this->assertEquals('Area V: Research', CategoryService::getAccreditationArea('Research Presentation'));
        $this->assertEquals('Area V: Research', CategoryService::getAccreditationArea('conference presentation'));

        // Instructional Material -> Area III
        $this->assertEquals('Area III: Curriculum & Instruction', CategoryService::getAccreditationArea('Instructional Material'));
        $this->assertEquals('Area III: Curriculum & Instruction', CategoryService::getAccreditationArea('instructional materials'));
        $this->assertEquals('Area III: Curriculum & Instruction', CategoryService::getAccreditationArea('course module'));

        // Faculty development categories -> Area VIII
        $this->assertEquals('Area VIII: Faculty Development', CategoryService::getAccreditationArea('Seminar'));
        $this->assertEquals('Area VIII: Faculty Development', CategoryService::getAccreditationArea('Training'));
        $this->assertEquals('Area VIII: Faculty Development', CategoryService::getAccreditationArea('Workshop'));
        $this->assertEquals('Area VIII: Faculty Development', CategoryService::getAccreditationArea('Award'));
        $this->assertEquals('Area VIII: Faculty Development', CategoryService::getAccreditationArea('Academic Milestone'));
        $this->assertEquals('Area VIII: Faculty Development', CategoryService::getAccreditationArea('Other'));
        $this->assertEquals('Area VIII: Faculty Development', CategoryService::getAccreditationArea('Unrecognized Category'));

        // Test inferAccreditationArea alias
        if (method_exists('CategoryService', 'inferAccreditationArea')) {
            $this->assertEquals('Area V: Research', CategoryService::inferAccreditationArea('Research Publication'));
            $this->assertEquals('Area III: Curriculum & Instruction', CategoryService::inferAccreditationArea('Instructional Material'));
        }
    }

    /**
     * Test geographic scope options and badge resolution
     */
    public function testGetScopeOptions(): void {
        $options = CategoryService::getScopeOptions();
        $this->assertTrue(is_array($options));
        $this->assertArrayHasKey('Institutional', $options);
        $this->assertArrayHasKey('Regional', $options);
        $this->assertArrayHasKey('National', $options);
        $this->assertArrayHasKey('International', $options);

        // Test getScopeBadge
        if (method_exists('CategoryService', 'getScopeBadge')) {
            $intlBadge = CategoryService::getScopeBadge('International');
            $this->assertEquals('International', $intlBadge['label']);
            $this->assertStringContains('purple', $intlBadge['class']);

            $natlBadge = CategoryService::getScopeBadge('National');
            $this->assertEquals('National', $natlBadge['label']);
            $this->assertStringContains('blue', $natlBadge['class']);

            $regBadge = CategoryService::getScopeBadge('Regional');
            $this->assertEquals('Regional', $regBadge['label']);
            $this->assertStringContains('amber', $regBadge['class']);

            $instBadge = CategoryService::getScopeBadge('Institutional');
            $this->assertEquals('Institutional', $instBadge['label']);
        }
    }

    /**
     * Test journal indexing tiers and badge resolution
     */
    public function testGetIndexingOptions(): void {
        $options = CategoryService::getIndexingOptions();
        $this->assertTrue(is_array($options));
        $this->assertArrayHasKey('None', $options);
        $this->assertArrayHasKey('Peer-Reviewed', $options);
        $this->assertArrayHasKey('CHED Recognized', $options);
        $this->assertArrayHasKey('Scopus', $options);
        $this->assertArrayHasKey('Web of Science', $options);

        // Test getIndexingBadge
        if (method_exists('CategoryService', 'getIndexingBadge')) {
            $scopusBadge = CategoryService::getIndexingBadge('Scopus');
            $this->assertEquals('Scopus', $scopusBadge['label']);
            $this->assertStringContains('orange', $scopusBadge['class']);

            $wosBadge = CategoryService::getIndexingBadge('Web of Science');
            $this->assertEquals('Web of Science', $wosBadge['label']);
            $this->assertStringContains('rose', $wosBadge['class']);

            $chedBadge = CategoryService::getIndexingBadge('CHED Recognized');
            $this->assertEquals('CHED Recognized', $chedBadge['label']);
            $this->assertStringContains('emerald', $chedBadge['class']);

            $peerBadge = CategoryService::getIndexingBadge('Peer-Reviewed');
            $this->assertEquals('Peer-Reviewed', $peerBadge['label']);

            $noneBadge = CategoryService::getIndexingBadge('None');
            $this->assertEquals('None', $noneBadge['label']);
        }
    }

    /**
     * Test category keys and normalization
     */
    public function testCategoryNormalizationAndHelpers(): void {
        $keys = CategoryService::getKeys();
        $this->assertContains('Research Publication', $keys);
        $this->assertContains('Instructional Material', $keys);
        $this->assertContains('Seminar', $keys);
        $this->assertContains('Training', $keys);
        $this->assertContains('Workshop', $keys);
        $this->assertContains('Award', $keys);
        $this->assertContains('Academic Milestone', $keys);
        $this->assertContains('Other', $keys);

        // Normalize aliases
        $this->assertEquals('Research Publication', CategoryService::normalize('research'));
        $this->assertEquals('Seminar', CategoryService::normalize('webinar'));
        $this->assertEquals('Training', CategoryService::normalize('bootcamp'));
        $this->assertEquals('Other', CategoryService::normalize('other: curriculum document'));

        // Label and icon helpers
        $this->assertTrue(!empty(CategoryService::getLabel('Research Publication')));
        $this->assertTrue(!empty(CategoryService::getShortLabel('Instructional Material')));
        $this->assertStringContains('fa-', CategoryService::getIcon('Award'));
        $this->assertStringContains('bg-', CategoryService::getBadgeClass('Seminar'));
    }
}
