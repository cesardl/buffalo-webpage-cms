#!/usr/bin/env bash
#
# Fails if any route's output has drifted from tests/golden/.
# Run after every refactor step:
#
#   ./tests/verify-golden.sh [base-url]
#
set -euo pipefail

BASE="${1:-http://localhost:8092}"
HERE="$(cd "$(dirname "$0")" && pwd)"
ACTUAL="$(mktemp -d)"
trap 'rm -rf "$ACTUAL"' EXIT

"$HERE/capture-golden.sh" "$BASE" "$ACTUAL" >/dev/null

if diff -ru "$HERE/golden" "$ACTUAL"; then
  echo "OK  $(ls -1 "$HERE/golden"/*.snap | wc -l) routes match the golden master."
else
  echo
  echo "FAIL  output drifted from the golden master (diff above)."
  exit 1
fi
