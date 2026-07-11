# apiclient-drive-service

A slim, auto-synced, **Drive-only** subset of
[`google/apiclient-services`](https://github.com/googleapis/google-api-php-client-services).

> **Unofficial.** Not affiliated with, endorsed by, or supported by Google. See [NOTICE](NOTICE).

## Why

`google/apiclient-services` ships generated PHP classes for **200+ Google APIs** in a single
package — 200MB+ on disk — even if your project only ever talks to Google Drive. This package
extracts just the `Google\Service\Drive` classes (~670 KB) and keeps them in sync with upstream
automatically, on a weekly schedule, with backward-compatibility checking before anything is
published.

## Install

```bash
composer require fojlerabbirabib/apiclient-drive-service
```

This package requires `google/apiclient` for the base `Google\Client` / HTTP / auth layer — the
same core library the official service catalog depends on.

> Don't require this alongside `google/apiclient-services` — both provide the same
> `Google\Service\Drive` namespace and will conflict. Composer will refuse the combination
> rather than let them collide silently at runtime.

## Usage

```php
use Google\Client;
use Google\Service\Drive;

$client = new Client();
$client->setAuthConfig('/path/to/credentials.json');
$client->addScope(Drive::DRIVE_READONLY);

$drive = new Drive($client);
$files = $drive->files->listFiles();
```

## How this stays up to date

A weekly GitHub Action pulls only the Drive-relevant files from upstream (via a scoped
`git sparse-checkout`, not a full clone), checks for breaking API changes with
[Roave/BackwardCompatibilityCheck](https://github.com/Roave/BackwardCompatibilityCheck), and:

- **Auto-publishes** if nothing breaking is detected (PATCH for cosmetic-only changes, MINOR for
  additive ones).
- **Opens a pull request for manual review** if a breaking change is detected, instead of
  publishing automatically.

See [CHANGELOG.md](CHANGELOG.md) for the exact upstream commit each release was synced from.

## Versioning

Standard SemVer, decided automatically by the sync pipeline:

| Change | Bump |
|---|---|
| No breaking changes, no new public API surface | PATCH |
| No breaking changes, new public API added | MINOR |
| Breaking change (removed/renamed class, method, property; incompatible signature) | MAJOR — held for manual review, not auto-published |

## License

Apache-2.0, same as upstream. See [LICENSE](LICENSE) and [NOTICE](NOTICE).
