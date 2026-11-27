<?php
/**
 * DateHelper
 * Enforces a single, consistent ISO 8601 date format across the entire SecureFaculty system:
 * - Dates: YYYY-MM-DD (e.g., 2026-11-27)
 * - Timestamps: YYYY-MM-DD HH:MM:SS (e.g., 2026-11-27 16:02:00)
 */

class DateHelper {
    public const DATE_FORMAT = 'Y-m-d';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Format any date string into strict YYYY-MM-DD, or return $fallback if empty/invalid.
     */
    public static function formatDate(?string $rawDate, string $fallback = 'Undated'): string {
        if ($rawDate === null || trim($rawDate) === '') {
            return $fallback;
        }
        $trimmed = trim($rawDate);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $trimmed, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }
        if (class_exists('CertificateModel')) {
            $norm = CertificateModel::normalizeDate($trimmed);
            if ($norm !== null) {
                return $norm;
            }
        }
        $ts = strtotime($trimmed);
        return $ts !== false ? date(self::DATE_FORMAT, $ts) : $trimmed;
    }

    /**
     * Format any timestamp string into strict YYYY-MM-DD HH:MM:SS.
     */
    public static function formatDateTime(?string $rawDateTime, string $fallback = '—'): string {
        if ($rawDateTime === null || trim($rawDateTime) === '') {
            return $fallback;
        }
        $trimmed = trim($rawDateTime);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $trimmed)) {
            return $trimmed;
        }
        $ts = strtotime($trimmed);
        return $ts !== false ? date(self::DATETIME_FORMAT, $ts) : $trimmed;
    }
}
