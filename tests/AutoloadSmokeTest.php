<?php

namespace Tests;

use Google\Service\Drive;
use PHPUnit\Framework\TestCase;

/**
 * Confirms the PSR-4 autoload map declared in composer.json
 * (Google\Service\ -> src) resolves correctly for the extracted Drive
 * service, and that the service + its resources can be instantiated.
 */
final class AutoloadSmokeTest extends TestCase
{
    public function testDriveServiceClassExists(): void
    {
        $this->assertTrue(class_exists(Drive::class));
    }

    public function testDriveServiceCanBeInstantiated(): void
    {
        $client = new \Google\Client();
        $drive = new Drive($client);

        $this->assertInstanceOf(Drive::class, $drive);
    }

    public function testDriveScopeConstantsAreDefined(): void
    {
        $this->assertSame(
            'https://www.googleapis.com/auth/drive.readonly',
            Drive::DRIVE_READONLY
        );
        $this->assertSame(
            'https://www.googleapis.com/auth/drive',
            Drive::DRIVE
        );
    }

    /**
     * @dataProvider resourceClassProvider
     */
    public function testResourceClassesExist(string $class): void
    {
        $this->assertTrue(
            class_exists($class),
            "Expected resource class {$class} to exist"
        );
    }

    public static function resourceClassProvider(): array
    {
        return [
            'About' => ['Google\\Service\\Drive\\Resource\\About'],
            'Files' => ['Google\\Service\\Drive\\Resource\\Files'],
            'Permissions' => ['Google\\Service\\Drive\\Resource\\Permissions'],
            'Revisions' => ['Google\\Service\\Drive\\Resource\\Revisions'],
            'Comments' => ['Google\\Service\\Drive\\Resource\\Comments'],
            'Replies' => ['Google\\Service\\Drive\\Resource\\Replies'],
            'Changes' => ['Google\\Service\\Drive\\Resource\\Changes'],
            'Channels' => ['Google\\Service\\Drive\\Resource\\Channels'],
            'Drives' => ['Google\\Service\\Drive\\Resource\\Drives'],
            'Apps' => ['Google\\Service\\Drive\\Resource\\Apps'],
            'Operations' => ['Google\\Service\\Drive\\Resource\\Operations'],
            'Approvals' => ['Google\\Service\\Drive\\Resource\\Approvals'],
            'Accessproposals' => ['Google\\Service\\Drive\\Resource\\Accessproposals'],
            'Teamdrives' => ['Google\\Service\\Drive\\Resource\\Teamdrives'],
        ];
    }

    public function testNoCrossServiceNamespaceReferences(): void
    {
        $pattern = '/Google\\\\Service\\\\[A-Za-z0-9_]+/';
        $allowed = [
            'Google\\Service\\Drive',
            'Google\\Service\\Resource',
            'Google\\Service\\Exception',
        ];

        $root = __DIR__ . '/../src';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            preg_match_all($pattern, $content, $matches);

            foreach (array_unique($matches[0]) as $match) {
                $this->assertContains(
                    $match,
                    $allowed,
                    "Unexpected cross-service reference '{$match}' found in {$file->getPathname()}"
                );
            }
        }
    }

    public function testNoFilenameExceedsPsr4ShimThreshold(): void
    {
        $root = __DIR__ . '/../src/Drive';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $this->assertLessThanOrEqual(
                139,
                strlen($file->getFilename()),
                "{$file->getFilename()} exceeds the 139-char PSR-4 autoload-shim threshold"
            );
        }
    }
}
