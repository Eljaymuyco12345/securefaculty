<?php
/**
 * AuthServiceInterface
 * Contract for Authentication, Session Lifecycle, and RBAC Authorization
 * ISUFST CICI Systematic Records Management Portal
 */

interface AuthServiceInterface {
    public static function initSession(): void;
    public static function getCsrfToken(): string;
    public static function verifyCsrfToken(?string $token): bool;
    public static function attempt(string $username, string $password): bool;
    public static function check(): bool;
    public static function user(): ?array;
    public static function isDean(): bool;
    public static function requireLogin(): void;
    public static function requireDean(): void;
    public static function logout(): void;
}
