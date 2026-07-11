#!/usr/bin/env bash
#
# sync-from-upstream.sh — fetch the Drive-only slice from
# googleapis/google-api-php-client-services and stage it into this package.
#
# Mechanism (verified against the live upstream repo): a blobless, depth-1
# partial clone plus a NON-CONE sparse-checkout fetches only src/Drive.php,
# src/Drive/, and LICENSE (~670 KB) instead of the full 200 MB+ monorepo.
# Cone mode is intentionally avoided — it would also pull the 200+ sibling
# top-level service files that sit next to Drive.php in upstream's src/.
#
# Three guards run before anything is staged; any failure exits non-zero so
# the caller (the sync workflow) cannot ship a broken snapshot. Our own
# composer.json is deliberately NOT overwritten — only synced source and
# LICENSE are.
#
# Env:
#   UPSTREAM_REPO  override the upstream URL
#   SYNC_WORKDIR   override the clone location (default: temp dir, auto-removed)
#   KEEP_WORKDIR   set to any non-empty value to retain the clone for debugging
#
# Stdout's last line is the upstream commit SHA, for the changelog/PR body.
set -euo pipefail

UPSTREAM_REPO="${UPSTREAM_REPO:-https://github.com/googleapis/google-api-php-client-services.git}"
PKG_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORKDIR="${SYNC_WORKDIR:-$(mktemp -d -t drive-sync-XXXXXX)}"

if [ -z "${KEEP_WORKDIR:-}" ]; then
  trap 'rm -rf "$WORKDIR"' EXIT
fi

echo "==> Fetching Drive-only slice from upstream into $WORKDIR"
git clone --filter=blob:none --no-checkout --depth 1 "$UPSTREAM_REPO" "$WORKDIR"
cd "$WORKDIR"
git sparse-checkout init --no-cone
git sparse-checkout set '/src/Drive.php' '/src/Drive/**' '/LICENSE'
git checkout main

UPSTREAM_SHA="$(git rev-parse HEAD)"
UPSTREAM_TAG="$(git describe --tags --abbrev=0 2>/dev/null || echo none)"
echo "==> Upstream HEAD: $UPSTREAM_SHA (nearest tag: $UPSTREAM_TAG)"

echo "==> Guard 1/3 — no cross-service namespace references"
# Anchored ($): a real sibling service like Google\Service\DriveActivity must NOT
# be filtered out as a substring of Google\Service\Drive.
if grep -rEo "Google\\\\Service\\\\[A-Za-z0-9_]+" "$WORKDIR/src/Drive.php" "$WORKDIR/src/Drive" \
    | grep -vE "Google\\\\Service\\\\Drive$" \
    | grep -vE "Google\\\\Service\\\\(Resource|Exception)$"; then
  echo "::error::Drive now references another service's namespace — needs manual review." >&2
  exit 1
fi

echo "==> Guard 2/3 — no filename exceeds the 139-char PSR-4 shim threshold"
LONG="$(find "$WORKDIR/src/Drive" -name '*.php' -printf '%f\n' | awk 'length($0) > 139')"
if [ -n "$LONG" ]; then
  echo "::error::A Drive class name now exceeds 139 chars — reintroduce the autoload shim:" >&2
  printf '%s\n' "$LONG" >&2
  exit 1
fi

echo "==> Guard 3/3 — non-empty extraction"
test -s "$WORKDIR/src/Drive.php"
COUNT="$(find "$WORKDIR/src/Drive" -name '*.php' | wc -l)"
if [ "$COUNT" -lt 50 ]; then
  echo "::error::Extraction yielded only $COUNT files (expected 50+) — upstream may have restructured." >&2
  exit 1
fi
echo "    $COUNT model/resource files fetched."

echo "==> Staging synced source + LICENSE into $PKG_ROOT"
rsync -a --delete --exclude='.gitkeep' "$WORKDIR/src/Drive/" "$PKG_ROOT/src/Drive/"
cp "$WORKDIR/src/Drive.php" "$PKG_ROOT/src/Drive.php"
cp "$WORKDIR/LICENSE" "$PKG_ROOT/LICENSE"

echo "==> Sync complete. Upstream SHA:"
echo "$UPSTREAM_SHA"
