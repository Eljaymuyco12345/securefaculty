<?php
/**
 * DateHelper Micro-Unit Test
 * Verifies unified YYYY-MM-DD and YYYY-MM-DD HH:MM:SS date formatting across the system
 */

require_once __DIR__ . '/../../TestCase.php';

class DateHelperTest extends TestCase {
    public function testFormatDateNormalizesToIsoYyyyMmDd(): void {
        $this->assertEquals('2026-11-27', DateHelper::formatDate('2026-11-27'));
        $this->assertEquals('2026-05-07', DateHelper::formatDate('05/07/2026'));
        $this->assertEquals('2022-12-15', DateHelper::formatDate('December 15, 2022'));
        $this->assertEquals('2026-11-27', DateHelper::formatDate('2026-11-27 15:30:00'));
        $this->assertEquals('Undated', DateHelper::formatDate(null));
        $this->assertEquals('Undated', DateHelper::formatDate('   '));
    }

    public function testFormatDateTimeNormalizesToYyyyMmDdHhMmSs(): void {
        $this->assertEquals('2026-11-27 16:02:00', DateHelper::formatDateTime('2026-11-27 16:02:00'));
        $this->assertEquals('2026-11-27 00:00:00', DateHelper::formatDateTime('2026-11-27'));
        $this->assertEquals('—', DateHelper::formatDateTime(null));
        $this->assertEquals('—', DateHelper::formatDateTime(''));
    }
}
