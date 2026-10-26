<?php
/**
 * CertificateModel Micro-Unit Test
 * Tests CertificateModel date normalization algorithm, anti-hallucination rules, and model delegations
 */

require_once __DIR__ . '/../../TestCase.php';
require_once __DIR__ . '/../../../app/Models/CertificateModel.php';

class CertificateModelTest extends TestCase {
    // -------------------------------------------------------------------------
    // Date Normalization & Anti-Hallucination Guard Unit Tests
    // -------------------------------------------------------------------------

    public function testNormalizeDateStandardIso(): void {
        $this->assertEquals("2026-05-07", CertificateModel::normalizeDate("2026-05-07"));
        $this->assertEquals("2023-10-24", CertificateModel::normalizeDate("2023/10/24"));
        $this->assertEquals("2024-02-29", CertificateModel::normalizeDate("2024.02.29"));
    }

    public function testNormalizeDateUsSlashFormat(): void {
        $this->assertEquals("2023-10-24", CertificateModel::normalizeDate("10/24/2023"));
        $this->assertEquals("2026-01-05", CertificateModel::normalizeDate("01/05/2026"));
    }

    public function testNormalizeDateDayGreaterTwelveFormat(): void {
        // When first token > 12, it must be detected as Day (DD/MM/YYYY)
        $this->assertEquals("2022-12-25", CertificateModel::normalizeDate("25/12/2022"));
        $this->assertEquals("2025-07-31", CertificateModel::normalizeDate("31-07-2025"));
    }

    public function testNormalizeDatePhilippineAcademicPhrasing(): void {
        $this->assertEquals("2022-12-15", CertificateModel::normalizeDate("15th day of December, 2022"));
        $this->assertEquals("2021-06-01", CertificateModel::normalizeDate("1st day of June 2021"));
        $this->assertEquals("2024-03-22", CertificateModel::normalizeDate("22nd day of March, 2024"));
        $this->assertEquals("2023-08-03", CertificateModel::normalizeDate("3rd day of August, 2023"));
        $this->assertEquals("2022-12-15", CertificateModel::normalizeDate("15 December 2022"));
    }

    public function testNormalizeDateMonthNameFirst(): void {
        $this->assertEquals("2023-10-24", CertificateModel::normalizeDate("October 24, 2023"));
        $this->assertEquals("2025-01-15", CertificateModel::normalizeDate("January 15, 2025"));
    }

    public function testNormalizeDateEmptyAndWhitespaceYieldsNull(): void {
        $this->assertNull(CertificateModel::normalizeDate(""), "Empty date string must return NULL");
        $this->assertNull(CertificateModel::normalizeDate("   "), "Whitespace string must return NULL");
        $this->assertNull(CertificateModel::normalizeDate("\t \n "), "Control chars must return NULL");
        $this->assertNull(CertificateModel::normalizeDate(null), "Null input must return NULL");
    }

    public function testNormalizeDateUnparseableGarbageYieldsNull(): void {
        $this->assertNull(CertificateModel::normalizeDate("not_a_valid_date"));
        $this->assertNull(CertificateModel::normalizeDate("certificate_issued_recently"));
        $this->assertNull(CertificateModel::normalizeDate("abc-def-ghij"));
    }

    public function testNormalizeDateOutOfRangeHistoricalYearYieldsNull(): void {
        $this->assertNull(CertificateModel::normalizeDate("1820-05-12"), "Year 1820 is outside acceptable academic threshold");
        $this->assertNull(CertificateModel::normalizeDate("1900-01-01"), "Year 1900 is outside acceptable academic threshold");
    }

    public function testNormalizeDateOutOfRangeFutureYearYieldsNull(): void {
        $this->assertNull(CertificateModel::normalizeDate("2150-01-01"), "Year 2150 is outside acceptable threshold");
        $this->assertNull(CertificateModel::normalizeDate("2200-12-31"), "Year 2200 is outside acceptable threshold");
    }

    // -------------------------------------------------------------------------
    // Model Delegation & Query Method Unit Tests
    // -------------------------------------------------------------------------

    public function testGetAvailableIssuedYearsReturnsArray(): void {
        $years = CertificateModel::getAvailableIssuedYears();
        $this->assertTrue(is_array($years), "Years must be an array");
        foreach ($years as $year) {
            $this->assertTrue(is_numeric($year), "Year must be numeric: {$year}");
            $this->assertTrue((int)$year >= 1950 && (int)$year <= 2099, "Year {$year} must be in valid range");
        }
    }

    public function testGetQuotaStatusReturnsExpectedSchema(): void {
        $quota = CertificateModel::getQuotaStatus();
        $this->assertTrue(is_array($quota));
        $this->assertArrayHasKey('monthly_limit', $quota);
        $this->assertArrayHasKey('current_usage', $quota);
        $this->assertArrayHasKey('remaining', $quota);
        $this->assertArrayHasKey('percentage', $quota);
        $this->assertGreaterThan(0, $quota['monthly_limit']);
    }

    public function testGetDepartmentMetricsReturnsExpectedSchema(): void {
        $metrics = CertificateModel::getDepartmentMetrics();
        $this->assertTrue(is_array($metrics));
        $this->assertArrayHasKey('total_records', $metrics);
        $this->assertArrayHasKey('total_faculty', $metrics);
        $this->assertArrayHasKey('monthly_records', $metrics);
        $this->assertArrayHasKey('vault_size_bytes', $metrics);
        $this->assertArrayHasKey('categories', $metrics);
    }

    public function testFindNonExistentReturnsNull(): void {
        $this->assertNull(CertificateModel::find(-99999), "Non-existent certificate ID must return null");
    }

    public function testFindByHashNonExistentReturnsNull(): void {
        $this->assertNull(CertificateModel::findByHash("non_existent_hash_00000000000000000000000000000000"));
    }

    public function testIsAuthorOrCoAuthorForNonExistentReturnsFalse(): void {
        $this->assertFalse(CertificateModel::isAuthorOrCoAuthor(-999, -999));
    }
}
