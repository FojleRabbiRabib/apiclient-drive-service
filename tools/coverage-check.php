<?php
/**
 * Fail the build if total line coverage is below a threshold.
 *
 * PHPUnit 10 has no native minimum-coverage percentage gate, so this reads
 * the Clover XML report that phpunit emits (--coverage-clover / the
 * <coverage><report><clover> config) and exits non-zero when the covered-
 * statement ratio falls short.
 *
 * Usage: php tools/coverage-check.php [clover-file] [threshold-percent]
 * Defaults: build/clover.xml, 95.
 */

declare(strict_types=1);

$input = $argv[1] ?? 'build/clover.xml';
$threshold = (float) ($argv[2] ?? 95);

if (! is_file($input)) {
    fwrite(STDERR, "Clover report not found at {$input} (run phpunit with coverage first).\n");
    exit(2);
}

$xml = @simplexml_load_file($input);
if ($xml === false) {
    fwrite(STDERR, "Could not parse {$input} as XML.\n");
    exit(2);
}

// Clover layout: <coverage><project><metrics statements="" coveredstatements=""/></project></coverage>
$metrics = $xml->project->metrics ?? null;
if ($metrics === null) {
    fwrite(STDERR, "No <project><metrics> element found in {$input}.\n");
    exit(2);
}

$statements = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];

if ($statements === 0) {
    fwrite(STDERR, "Clover reports zero statements — nothing to measure.\n");
    exit(2);
}

$percent = ($covered / $statements) * 100;

printf(
    "Coverage: %.2f%% (%d/%d statements) — threshold %.2f%%\n",
    $percent,
    $covered,
    $statements,
    $threshold
);

if ($percent < $threshold) {
    fwrite(STDERR, sprintf("FAIL: coverage %.2f%% is below the %.2f%% threshold.\n", $percent, $threshold));
    exit(1);
}

echo "OK: coverage meets the threshold.\n";
exit(0);
