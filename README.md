# apiclient-drive-service

A slim, auto-synced, **Drive-only** subset of
[`google/apiclient-services`](https://github.com/googleapis/google-api-php-client-services).

> **Unofficial.** Not affiliated with, endorsed by, or supported by Google. See [NOTICE](NOTICE).

## Why

`google/apiclient-services` ships generated PHP classes for **200+ Google APIs** in a single
package — 200MB+ on disk — even if your project only ever talks to Google Drive. This package
extracts just the `Google\Service\Drive` classes (~670 KB) and keeps them in sync with upstream
automatically, on a weekly schedule, opening a pull request for review.

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
`git sparse-checkout`, not a full clone), runs the test suite and 95% coverage gate on the
synced code, and **opens a pull request for manual review** — it never auto-tags or publishes.
A daily staleness check fails loudly if the sync hasn't run successfully in 7 days.

> BC checking is **not** automated. Roave/BackwardCompatibilityCheck could not resolve the
> `google/apiclient` base classes (this package only `suggest`s them, and `conflict`s
> `google/apiclient-services`), so it emitted only spurious breaks. Review the sync PR's diff
> for breaking API surface before merging.

See [CHANGELOG.md](CHANGELOG.md) for the exact upstream commit each sync was based on.

## Versioning

Standard SemVer, decided manually when a sync PR is merged:

| Change | Bump |
|---|---|
| No breaking changes, no new public API surface | PATCH |
| No breaking changes, new public API added | MINOR |
| Breaking change (removed/renamed class, method, property; incompatible signature) | MAJOR |

## License

Apache-2.0, same as upstream. See [LICENSE](LICENSE) and [NOTICE](NOTICE).
