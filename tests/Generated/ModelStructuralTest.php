<?php

declare(strict_types=1);

namespace Tests\Generated;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Exercises every generated model class under src/Drive/ by instantiating it
 * and round-tripping every declared property through its setter/getter pair.
 *
 * The generated classes are Google's; this test does not assert business
 * behavior — it asserts structural integrity: every class is instantiable,
 * every setter accepts a type-appropriate value without throwing, and every
 * getter returns exactly what its setter stored. This is what gives us
 * meaningful line coverage on generated code without hand-writing 93 files
 * of getter/setter tests.
 */
final class ModelStructuralTest extends TestCase
{
    /**
     * @dataProvider modelClasses
     */
    public function testEveryModelRoundTripsItsProperties(string $class): void
    {
        $this->assertTrue(class_exists($class), "Expected class {$class} to exist and be autoloadable");

        $obj = new $class();
        $reflect = new ReflectionClass($obj);

        // Phase 1 — drive a value through every setter.
        $setters = [];
        foreach ($reflect->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();
            if ($method->isStatic() || ! str_starts_with($name, 'set')) {
                continue;
            }
            $params = $method->getParameters();
            if ($params === []) {
                continue;
            }
            $value = self::dummyValue($params[0]);
            $obj->$name($value);
            $setters[substr($name, 3)] = $value;
        }

        // Phase 2 — read back through every getter; those whose setter ran
        // must return the exact value stored (pure-assignment setters).
        $exercised = 0;
        foreach ($reflect->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();
            if ($method->isStatic() || ! str_starts_with($name, 'get')) {
                continue;
            }
            $property = substr($name, 3);
            $result = $obj->$name();
            $exercised++;
            if (array_key_exists($property, $setters)) {
                $this->assertSame(
                    $setters[$property],
                    $result,
                    "{$class}::{$name}() did not return the value stored by set{$property}()"
                );
            }
        }

        $this->assertGreaterThan(0, $exercised, "{$class} exposed no getters to exercise");
    }

    /** @return array<string, array{string}> */
    public static function modelClasses(): array
    {
        $dir = __DIR__ . '/../../src/Drive';
        $classes = [];
        foreach ((array) glob($dir . '/*.php') as $file) {
            $base = basename($file, '.php');
            $classes[$base] = ['Google\\Service\\Drive\\' . $base];
        }
        self::assertNotEmpty($classes, 'No model classes discovered under src/Drive');
        return $classes;
    }

    private static function dummyValue(ReflectionParameter $param): mixed
    {
        $type = $param->getType();

        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            $class = $type->getName();
            return class_exists($class) ? new $class() : null;
        }

        if ($type instanceof ReflectionNamedType) {
            return match ($type->getName()) {
                'array' => [],
                'string' => 'test-value',
                'bool' => true,
                'int' => 1,
                'float' => 1.5,
                'mixed', 'null' => 'test-value',
                default => null,
            };
        }

        // No type hint — generated setters are plain assignments, so a scalar is safe.
        return 'test-value';
    }
}
