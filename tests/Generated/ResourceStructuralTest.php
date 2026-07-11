<?php

declare(strict_types=1);

namespace Tests\Generated;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Exercises every generated resource class under src/Drive/Resource/ by
 * invoking each of its own public API methods (get, list, create, etc.).
 *
 * Generated resource method bodies are uniformly `build $params; merge
 * optParams; return $this->call(...)`. The stub Google\Service\Resource::call()
 * (see tests/Stubs) returns a dummy model so the body runs end-to-end without
 * an HTTP/auth stack — covering the generated lines without testing Google's
 * real request-building logic (which lives in google/apiclient core).
 */
final class ResourceStructuralTest extends TestCase
{
    /**
     * @dataProvider resourceClasses
     */
    public function testEveryResourceMethodIsCallable(string $class): void
    {
        $this->assertTrue(class_exists($class), "Expected class {$class} to exist and be autoloadable");

        // The stub Resource constructor accepts any args, so a bare `new` works.
        $obj = new $class();
        $reflect = new ReflectionClass($class);

        $invoked = 0;
        foreach ($reflect->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || $method->isConstructor()) {
                continue;
            }
            // Only methods declared on THIS class — skip inherited call()/__construct().
            if ($method->getDeclaringClass()->getName() !== ltrim($class, '\\')) {
                continue;
            }
            $name = $method->getName();
            if (str_starts_with($name, '__')) {
                continue;
            }

            $args = [];
            foreach ($method->getParameters() as $param) {
                if ($param->isOptional()) {
                    break; // let remaining params use their defaults
                }
                $args[] = self::dummyValue($param);
            }

            $obj->$name(...$args);
            $invoked++;
        }

        $this->assertGreaterThan(0, $invoked, "{$class} declared no own public methods to exercise");
    }

    /** @return array<string, array{string}> */
    public static function resourceClasses(): array
    {
        $dir = __DIR__ . '/../../src/Drive/Resource';
        $classes = [];
        foreach ((array) glob($dir . '/*.php') as $file) {
            $base = basename($file, '.php');
            $classes[$base] = ['Google\\Service\\Drive\\Resource\\' . $base];
        }
        self::assertNotEmpty($classes, 'No resource classes discovered under src/Drive/Resource');
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

        return 'test-value';
    }
}
