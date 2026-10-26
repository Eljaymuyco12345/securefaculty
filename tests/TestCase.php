<?php
/**
 * SecureFaculty Lightweight Micro-Unit Testing Framework
 * Provides robust assertions, lifecycle hooks (setUp/tearDown),
 * state isolation, and comprehensive failure diagnostic reporting.
 */

require_once __DIR__ . '/../app/bootstrap.php';

class ControllerTerminatedException extends Exception {
    public string $type;
    public $payload;
    public int $statusCode;

    public function __construct(string $type, $payload = null, int $statusCode = 200) {
        parent::__construct("Controller terminated via {$type}");
        $this->type = $type;
        $this->payload = $payload;
        $this->statusCode = $statusCode;
    }
}

abstract class TestCase {
    protected int $assertionCount = 0;
    protected array $savedSession = [];
    protected array $savedGet = [];
    protected array $savedPost = [];
    protected array $savedServer = [];
    protected array $savedFiles = [];

    public function setUp(): void {
        // Snapshot environment
        $this->savedSession = $_SESSION ?? [];
        $this->savedGet = $_GET ?? [];
        $this->savedPost = $_POST ?? [];
        $this->savedServer = $_SERVER ?? [];
        $this->savedFiles = $_FILES ?? [];
        $this->assertionCount = 0;
    }

    public function tearDown(): void {
        // Restore environment
        $_SESSION = $this->savedSession;
        $_GET = $this->savedGet;
        $_POST = $this->savedPost;
        $_SERVER = $this->savedServer;
        $_FILES = $this->savedFiles;
    }

    public static function beforeClass(): void {}
    public static function afterClass(): void {}

    public function getAssertionCount(): int {
        return $this->assertionCount;
    }

    protected function runControllerAction(callable $fn): void {
        try {
            $fn();
        } catch (ControllerTerminatedException $e) {
            // Simulated script termination
        }
    }

    // =========================================================================
    // ASSERTION SUITE
    // =========================================================================

    protected function assertTrue($condition, string $message = ''): void {
        $this->assertionCount++;
        if ($condition !== true) {
            $msg = $message ?: "Failed asserting that " . var_export($condition, true) . " is true.";
            throw new AssertionError($msg);
        }
    }

    protected function assertFalse($condition, string $message = ''): void {
        $this->assertionCount++;
        if ($condition !== false) {
            $msg = $message ?: "Failed asserting that " . var_export($condition, true) . " is false.";
            throw new AssertionError($msg);
        }
    }

    protected function assertEquals($expected, $actual, string $message = ''): void {
        $this->assertionCount++;
        if ($expected != $actual) {
            $msg = $message ?: "Failed asserting that two values are equal.\nExpected: " . var_export($expected, true) . "\nActual:   " . var_export($actual, true);
            throw new AssertionError($msg);
        }
    }

    protected function assertSame($expected, $actual, string $message = ''): void {
        $this->assertionCount++;
        if ($expected !== $actual) {
            $msg = $message ?: "Failed asserting that two variables reference identical values with identical types.\nExpected: " . var_export($expected, true) . "\nActual:   " . var_export($actual, true);
            throw new AssertionError($msg);
        }
    }

    protected function assertNotEquals($expected, $actual, string $message = ''): void {
        $this->assertionCount++;
        if ($expected == $actual) {
            $msg = $message ?: "Failed asserting that " . var_export($actual, true) . " does NOT equal expected.";
            throw new AssertionError($msg);
        }
    }

    protected function assertNotSame($expected, $actual, string $message = ''): void {
        $this->assertionCount++;
        if ($expected === $actual) {
            $msg = $message ?: "Failed asserting that " . var_export($actual, true) . " is NOT identical to expected.";
            throw new AssertionError($msg);
        }
    }

    protected function assertNull($actual, string $message = ''): void {
        $this->assertionCount++;
        if ($actual !== null) {
            $msg = $message ?: "Failed asserting that " . var_export($actual, true) . " is null.";
            throw new AssertionError($msg);
        }
    }

    protected function assertNotNull($actual, string $message = ''): void {
        $this->assertionCount++;
        if ($actual === null) {
            $msg = $message ?: "Failed asserting that value is not null.";
            throw new AssertionError($msg);
        }
    }

    protected function assertArrayHasKey($key, array $array, string $message = ''): void {
        $this->assertionCount++;
        if (!array_key_exists($key, $array)) {
            $msg = $message ?: "Failed asserting that array contains key '{$key}'.";
            throw new AssertionError($msg);
        }
    }

    protected function assertCount(int $expectedCount, $countable, string $message = ''): void {
        $this->assertionCount++;
        $actual = is_countable($countable) ? count($countable) : -1;
        if ($actual !== $expectedCount) {
            $msg = $message ?: "Failed asserting that count({$actual}) equals expected ({$expectedCount}).";
            throw new AssertionError($msg);
        }
    }

    protected function assertContains($needle, $haystack, string $message = ''): void {
        $this->assertionCount++;
        $found = false;
        if (is_array($haystack)) {
            $found = in_array($needle, $haystack, true);
        } elseif (is_string($haystack) && is_string($needle)) {
            $found = str_contains($haystack, $needle);
        }
        if (!$found) {
            $msg = $message ?: "Failed asserting that haystack contains needle: " . var_export($needle, true);
            throw new AssertionError($msg);
        }
    }

    protected function assertStringContains(string $needle, string $haystack, string $message = ''): void {
        $this->assertionCount++;
        if (!str_contains($haystack, $needle)) {
            $msg = $message ?: "Failed asserting that '{$haystack}' contains '{$needle}'.";
            throw new AssertionError($msg);
        }
    }

    protected function assertStringStartsWith(string $prefix, string $str, string $message = ''): void {
        $this->assertionCount++;
        if (!str_starts_with($str, $prefix)) {
            $msg = $message ?: "Failed asserting that '{$str}' starts with prefix '{$prefix}'.";
            throw new AssertionError($msg);
        }
    }

    protected function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void {
        $this->assertionCount++;
        if (!preg_match($pattern, $string)) {
            $msg = $message ?: "Failed asserting that '{$string}' matches pattern '{$pattern}'.";
            throw new AssertionError($msg);
        }
    }

    protected function assertGreaterThan($expected, $actual, string $message = ''): void {
        $this->assertionCount++;
        if (!($actual > $expected)) {
            $msg = $message ?: "Failed asserting that actual ({$actual}) is greater than expected ({$expected}).";
            throw new AssertionError($msg);
        }
    }

    protected function assertLessThan($expected, $actual, string $message = ''): void {
        $this->assertionCount++;
        if (!($actual < $expected)) {
            $msg = $message ?: "Failed asserting that actual ({$actual}) is less than expected ({$expected}).";
            throw new AssertionError($msg);
        }
    }

    protected function expectException(string $expectedClass, callable $fn, string $message = ''): void {
        $this->assertionCount++;
        try {
            $fn();
        } catch (Throwable $e) {
            if ($e instanceof $expectedClass) {
                return; // Expected exception caught
            }
            throw new AssertionError("Caught unexpected exception of type " . get_class($e) . " (expected {$expectedClass}): " . $e->getMessage());
        }
        $msg = $message ?: "Failed asserting that exception of type '{$expectedClass}' was thrown.";
        throw new AssertionError($msg);
    }
}
