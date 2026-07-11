<?php
/**
 * Test bootstrap.
 *
 * Requires the Composer autoloader when present (gives us PHPUnit plus the
 * Google\Service\ -> src PSR-4 map declared in composer.json).
 *
 * Loads the structural stubs in tests/Stubs/ whenever the real google/apiclient
 * base classes (Google\Client, Google\Model, Google\Collection, Google\Service,
 * Google\Service\Resource, Google\Service\Exception) are absent. google/apiclient
 * is only a `suggest` here — it conflicts with google/apiclient-services, which
 * google/apiclient itself hard-requires — so it is NOT installed by composer
 * install, meaning the stubs are needed even with vendor/ present. The stubs
 * exist purely so the generated Drive classes can be instantiated and reflected
 * over; they do not replicate real HTTP/auth behavior.
 */

$composerAutoload = __DIR__ . '/../vendor/autoload.php';

if (file_exists($composerAutoload)) {
    require $composerAutoload;
}

if (!class_exists(\Google\Client::class)) {
    require __DIR__ . '/Stubs/google_apiclient_stubs.php';
}

spl_autoload_register(function ($class) {
    $prefix = 'Google\\Service\\';
    $baseDir = __DIR__ . '/../src/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
