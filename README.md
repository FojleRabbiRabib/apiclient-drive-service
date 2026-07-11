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
composer require fojlerabbirabib/apiclient-drive-service google/apiclient
```

This package ships the Drive service classes; `google/apiclient` ships the `Google\Client` /
HTTP / auth base layer they extend. This package `provide`s `google/apiclient-services` and
`conflict`s the real one, so Composer installs `google/apiclient` **without** the 200MB full
service catalog — only the Drive classes ship.

> Don't also require `google/apiclient-services` directly — it registers the same
> `Google\Service\Drive` namespace from a different file and Composer will refuse the
> combination. If you need other Google services, use `google/apiclient-services` instead of
> this package.

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

## Use with masbug/flysystem-google-drive-ext

[`masbug/flysystem-google-drive-ext`](https://github.com/masbug/flysystem-google-drive-ext)
is a popular Flysystem adapter for Google Drive. It depends on `google/apiclient`,
which normally drags in the full 200MB `google/apiclient-services` catalog — even
though the adapter only uses Drive. Pair it with this package and the catalog
never gets installed.

Require both in one command so Composer sees this package's `provide` up front:

```bash
composer require masbug/flysystem-google-drive-ext fojlerabbirabib/apiclient-drive-service
```

That installs `masbug/flysystem-google-drive-ext` + `google/apiclient` + this slim
package, and excludes `google/apiclient-services` entirely (~200MB saved). The
adapter works unchanged — it uses `Google\Service\Drive\*` classes, which this
package ships at the same namespace.

> If you require `masbug/flysystem-google-drive-ext` alone first, Composer pulls
> the full catalog immediately; you'd then need a second
> `composer require fojlerabbirabib/apiclient-drive-service` to swap it out.
> Require both together to avoid downloading the 200MB at all.

The same pattern works for any package that depends on `google/apiclient` for
Drive only — just don't use it if the project also needs other Google services
(Sheets, Calendar, …), whose classes won't be present.

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
