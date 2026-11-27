<?php
/**
 * Env Micro-Unit Test
 * Verifies .env parsing, quoted values, comments, and non-overwrite behavior
 */

require_once __DIR__ . '/../../TestCase.php';

class EnvTest extends TestCase {
    private string $tempEnvPath;

    public function setUp(): void {
        parent::setUp();
        $this->tempEnvPath = TEMP_DIR . DIRECTORY_SEPARATOR . 'test_' . uniqid() . '.env';
    }

    public function tearDown(): void {
        if (file_exists($this->tempEnvPath)) {
            @unlink($this->tempEnvPath);
        }
        foreach (['ENV_TEST_PLAIN', 'ENV_TEST_QUOTED', 'ENV_TEST_SINGLE', 'ENV_TEST_COMMENT', 'ENV_TEST_EXISTING'] as $k) {
            putenv($k);
            unset($_ENV[$k], $_SERVER[$k]);
        }
        parent::tearDown();
    }

    public function testLoadParsesKeysQuotesCommentsAndRespectsExistingEnv(): void {
        putenv('ENV_TEST_EXISTING=from_os');
        $_ENV['ENV_TEST_EXISTING'] = 'from_os';

        $content = <<<ENV
# Full line comment
ENV_TEST_PLAIN=hello_world
ENV_TEST_QUOTED="SecureFaculty CICI Portal"
ENV_TEST_SINGLE='single_quoted_value'
ENV_TEST_COMMENT=unquoted_val # trailing comment
ENV_TEST_EXISTING=should_not_overwrite
ENV;
        file_put_contents($this->tempEnvPath, $content);

        $loaded = Env::load($this->tempEnvPath);
        $this->assertTrue($loaded);
        $this->assertEquals('hello_world', Env::get('ENV_TEST_PLAIN'));
        $this->assertEquals('SecureFaculty CICI Portal', Env::get('ENV_TEST_QUOTED'));
        $this->assertEquals('single_quoted_value', Env::get('ENV_TEST_SINGLE'));
        $this->assertEquals('unquoted_val', Env::get('ENV_TEST_COMMENT'));
        $this->assertEquals('from_os', Env::get('ENV_TEST_EXISTING'));
        $this->assertEquals('fallback_val', Env::get('ENV_TEST_MISSING', 'fallback_val'));
    }

    public function testLoadReturnsFalseForMissingFile(): void {
        $this->assertFalse(Env::load(TEMP_DIR . DIRECTORY_SEPARATOR . 'nonexistent_' . uniqid() . '.env'));
    }

    public function testConfigConstantsAreLoadedFromDotEnv(): void {
        $this->assertTrue(defined('AES_KEY_HEX'));
        $this->assertEquals(64, strlen(AES_KEY_HEX));
        $this->assertTrue(defined('OCR_API_KEY'));
        $this->assertNotEquals('', OCR_API_KEY);
        $this->assertTrue(defined('SMTP_HOST'));
        $this->assertEquals('smtp.gmail.com', SMTP_HOST);
    }
}
