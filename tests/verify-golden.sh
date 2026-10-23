#!/usr/bin/env bash
#
# Fails if any route's output has drifted from tests/golden/.
#
#   ./tests/verify-golden.sh [base-url]
#
# The comparison is byte for byte. It was briefly relaxed to ignore line
# endings, while the PHP 5.6 app and its replacement had to be compared
# against the same snapshots and the old sources were CRLF; with one
# application left there is nothing to paper over, so the blind spot that
# relaxation created is gone.
set -euo pipefail

BASE="${1:-http://localhost:8092}"
HERE="$(cd "$(dirname "$0")" && pwd)"
ACTUAL="$(mktemp -d)"
trap 'rm -rf "$ACTUAL"' EXIT

bash "$HERE/capture-golden.sh" "$BASE" "$ACTUAL" >/dev/null

if diff -ru "$HERE/golden" "$ACTUAL"; then
  echo "OK  $(ls -1 "$HERE/golden"/*.snap | wc -l) routes match the golden master."
else
  echo
  echo "FAIL  output drifted from the golden master (diff above)."
  exit 1
fi
