<?php
/**
 * Minimal structural stubs for the google/apiclient base classes that the
 * generated Drive service/model classes extend or type-hint against.
 *
 * These exist ONLY so tests/Generated and tests/AutoloadSmokeTest.php can
 * instantiate and reflect over the generated code without a real HTTP/auth
 * stack. They intentionally do not implement real behavior (no HTTP calls,
 * no auth) — do not reuse these outside the test suite.
 *
 * Loaded only when vendor/autoload.php (the real google/apiclient) is not
 * available — see tests/bootstrap.php.
 */

namespace Google {

    class Client
    {
        public function getConfig()
        {
            return [];
        }
    }

    #[\AllowDynamicProperties]
    class Model implements \ArrayAccess
    {
        protected $modelData = [];

        public function offsetExists($offset): bool
        {
            return isset($this->modelData[$offset]);
        }

        public function offsetGet($offset): mixed
        {
            return $this->modelData[$offset] ?? null;
        }

        public function offsetSet($offset, $value): void
        {
            $this->modelData[$offset] = $value;
        }

        public function offsetUnset($offset): void
        {
            unset($this->modelData[$offset]);
        }
    }

    class Collection extends Model implements \Countable, \Iterator
    {
        protected $collection_key = 'items';
        private $position = 0;

        public function current(): mixed
        {
            $items = $this->{$this->collection_key} ?? [];
            return $items[$this->position] ?? null;
        }

        public function key(): mixed
        {
            return $this->position;
        }

        public function next(): void
        {
            $this->position++;
        }

        public function rewind(): void
        {
            $this->position = 0;
        }

        public function valid(): bool
        {
            $items = $this->{$this->collection_key} ?? [];
            return isset($items[$this->position]);
        }

        public function count(): int
        {
            $items = $this->{$this->collection_key} ?? [];
            return count($items);
        }
    }

    class Service
    {
        // Mirror the public surface of Google\Service (google/apiclient core)
        // so generated service constructors can assign these without triggering
        // PHP 8.2+ dynamic-property deprecations.
        public $batchPath;
        public $rootUrl;
        public $rootUrlTemplate;
        public $version;
        public $servicePath;
        public $serviceName;
        public $availableScopes;
        public $resource;
        public $client;

        public function __construct($clientOrConfig = [])
        {
            $this->client = $clientOrConfig;
        }

        public function getClient()
        {
            return $this->client;
        }
    }
}

namespace Google\Service {

    abstract class Resource
    {
        public function __construct(...$args)
        {
        }

        /**
         * Stand-in for Google\Service\Resource::call(). The real method builds
         * an HTTP request via the client; this stub just returns a new instance
         * of the expected model (or null) so generated resource method bodies —
         * which are `return $this->call(...)` — can be exercised for coverage
         * without an HTTP/auth stack.
         */
        public function call($name, $arguments, $expectedClass = null)
        {
            if ($expectedClass !== null && class_exists((string) $expectedClass)) {
                return new $expectedClass();
            }

            return null;
        }
    }

    class Exception extends \Exception
    {
    }
}
