<?php
/**
 * Env
 * Lightweight, zero-dependency .env file loader and accessor.
 * Preserves existing OS/Docker environment variables without overwriting them.
 */

class Env {
    /**
     * Load key=value pairs from a .env file into $_ENV, $_SERVER, and putenv().
     */
    public static function load(string $filePath): bool {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return false;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return false;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (str_starts_with($trimmed, 'export ')) {
                $trimmed = trim(substr($trimmed, 7));
            }

            $eqPos = strpos($trimmed, '=');
            if ($eqPos === false) {
                continue;
            }

            $key = trim(substr($trimmed, 0, $eqPos));
            if ($key === '') {
                continue;
            }

            $rawValue = trim(substr($trimmed, $eqPos + 1));
            $value = self::parseValue($rawValue);

            // Never overwrite real OS / Docker environment variables
            if (getenv($key) === false && !array_key_exists($key, $_ENV)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }

        return true;
    }

    /**
     * Retrieve an environment variable with an optional default fallback.
     */
    public static function get(string $key, $default = null) {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        $envVal = getenv($key);
        if ($envVal !== false) {
            return $envVal;
        }
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }
        return $default;
    }

    private static function parseValue(string $raw): string {
        $len = strlen($raw);
        if ($len >= 2) {
            $first = $raw[0];
            $last = $raw[$len - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($raw, 1, -1);
            }
        }

        // Strip inline # comment for unquoted values
        $commentPos = strpos($raw, ' #');
        if ($commentPos !== false) {
            $raw = trim(substr($raw, 0, $commentPos));
        }

        return $raw;
    }
}
