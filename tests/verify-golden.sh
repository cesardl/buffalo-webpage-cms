#!/usr/bin/env bash
#
# Fails if any route's output has drifted from tests/golden/.
#
#   ./tests/verify-golden.sh                        # the legacy app
#   ./tests/verify-golden.sh http://localhost:8093  # the PHP 8 app
#
# Line endings are normalised on both sides before comparing. The legacy
# sources are CRLF, so everything they echo is CRLF; the ported templates are
# LF. That difference is an accident of the old files, not behaviour, and
# matching it would mean keeping the templates in CRLF forever.
#
# The cost is a blind spot: a CR that the port dropped from inside a database
# value would not be reported either. Those sit in description text, where
# they render as whitespace, so nothing visible depends on them -- but it is a
# real gap, not a free simplification.
set -euo pipefail

BASE="${1:-http://localhost:8092}"
HERE="$(cd "$(dirname "$0")" && pwd)"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

"$HERE/capture-golden.sh" "$BASE" "$WORK/actual" >/dev/null

strip_cr() {
  local from="$1" to="$2"
  mkdir -p "$to"
  for file in "$from"/*.snap; do
    tr -d '\r' < "$file" > "$to/$(basename "$file")"
  done
}

strip_cr "$HERE/golden" "$WORK/expected"
strip_cr "$WORK/actual" "$WORK/actual-lf"

if diff -ru "$WORK/expected" "$WORK/actual-lf"; then
  echo "OK  $(ls -1 "$HERE/golden"/*.snap | wc -l) routes match the golden master."
else
  echo
  echo "FAIL  output drifted from the golden master (diff above)."
  exit 1
fi
