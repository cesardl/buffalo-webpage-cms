#!/usr/bin/env bash
#
# Snapshots the current output of every read-only route, so the PHP 8 rewrite
# can be diffed against what the PHP 5.6 app actually does today.
#
#   ./tests/capture-golden.sh [base-url] [out-dir]
#
# Each snapshot starts with a "# METHOD path -> status redirect" line, so a
# route that stops redirecting (or starts) shows up as a diff too.
#
# Only read-only routes are covered. The action*.php endpoints mutate the
# database, so they are pinned by integration tests instead, not by HTML diffs.
set -euo pipefail

BASE="${1:-http://localhost:8092}"
OUT="${2:-$(cd "$(dirname "$0")" && pwd)/golden}"

ADMIN_USER=root
ADMIN_PASS=root

JAR="$(mktemp)"
ANON="$(mktemp)"
trap 'rm -f "$JAR" "$ANON"' EXIT

mkdir -p "$OUT"
rm -f "$OUT"/*.snap

# The PHP 8 app requires a CSRF token on every POST; the legacy app has no
# such field and ignores the extra parameter. Sending it either way lets one
# harness drive both, which is the whole point of the comparison.
csrf_token() {
  local jar="$1"
  curl -s -b "$jar" -c "$jar" "$BASE/administrator/index.php"     | grep -oE 'name="_token" value="[^"]+"'     | grep -oE '[A-Za-z0-9]{16,}'     | head -1 || true   # no token on the legacy login page, and that is fine
}

snap() {
  local name="$1" method="$2" path="$3" data="${4:-}" jar="${5:-$JAR}"
  local body result token
  body="$(mktemp)"
  if [ "$method" = POST ]; then
    token="$(csrf_token "$jar")"
    if [ -n "$token" ]; then
      data="${data}&_token=${token}"
    fi
  fi
  if [ "$method" = POST ]; then
    result="$(curl -sS -b "$jar" -c "$jar" -o "$body" \
      -w '%{http_code} %{redirect_url}' -X POST -d "$data" "$BASE$path")"
  else
    result="$(curl -sS -b "$jar" -c "$jar" -o "$body" \
      -w '%{http_code} %{redirect_url}' "$BASE$path")"
  fi
  # Record the redirect relative to the base, so a snapshot taken from one
  # port can be compared against an app served on another.
  result="${result//$BASE/}"
  # The CSRF token is regenerated per session, so it would make every
  # snapshot differ from every capture. Replace it with a fixed stand-in:
  # what matters is that a token is present and in the right place, not
  # which one. No other 64-character hex string appears in the output
  # (the integrity attributes are base64).
  # perl, not sed: under MSYS (Git Bash) sed rewrites the file in text mode
  # and strips every CR, which silently removed them from the snapshots and
  # would have made this disagree with a Linux runner, where it does not.
  perl -pi -e 's/[a-f0-9]{64}/CSRF_TOKEN/g' "$body"
  # Apache writes its own body for a redirect, naming the host it is serving
  # and the version it is running. The status line above already records
  # where the redirect points, so the body only has to show that Apache
  # answered: anything identifying this particular server is normalised out.
  BASE="$BASE" perl -pi -e 's/\Q$ENV{BASE}\E//g; s{<address>[^<]*</address>}{<address>APACHE</address>}g' "$body"

  { printf '# %s %s -> %s\n' "$method" "$path" "$result"; cat "$body"; } > "$OUT/$name.snap"
  rm -f "$body"
  printf '  %-34s %s\n' "$name" "$result"
}

echo "Entry points"
snap entry-root           GET /                      '' "$ANON"
snap entry-administrator  GET /administrator         '' "$ANON"

echo "Public pages"
snap public-index         GET /index.html            '' "$ANON"
snap public-quienes       GET /quienes_somos.html    '' "$ANON"
snap public-productos     GET /productos.html        '' "$ANON"
snap public-ubicanos      GET /ubicanos.html         '' "$ANON"
snap public-contactanos   GET /contactanos.html      '' "$ANON"
snap public-motocicleta   GET /productos_motocicleta.php '' "$ANON"
snap public-furgon        GET /productos_furgon.php      '' "$ANON"
snap public-mototaxis     GET /productos_mototaxis.php   '' "$ANON"

echo "Admin, signed out (these pin the current redirect behaviour)"
snap admin-login          GET /administrator/index.php          '' "$ANON"
snap admin-login-err1     GET '/administrator/index.php?error=1' '' "$ANON"
snap admin-login-err2     GET '/administrator/index.php?error=2' '' "$ANON"
snap admin-guard-form     GET /administrator/formProducto.php   '' "$ANON"
snap admin-guard-detalles GET /administrator/formDetalles.php   '' "$ANON"
snap admin-guard-colores  GET /administrator/formColores.php    '' "$ANON"
snap admin-guard-home     GET /administrator/bienvenido.php     '' "$ANON"
snap admin-login-bad      POST /administrator/bienvenido.php 'user=nope&passwd=nope' "$ANON"

# These five were reachable without logging in until the guards landed. They
# are safe to capture signed out: each one exits before touching the database.
snap guard-action-producto POST /administrator/actionProductoCtrl.php        'id_producto=1' "$ANON"
snap guard-action-detalles POST /administrator/actionDetalleProductoCtrl.php 'id_producto=1' "$ANON"
snap guard-action-color    POST /administrator/actionColor.php               'id_producto=1' "$ANON"
snap guard-vehiculo-delete POST /administrator/VehiculoDelete.php            'id_producto=1' "$ANON"
snap guard-vehiculo-select POST /administrator/VehiculoSelect.php            'veh_id=1'      "$ANON"

echo "Admin, signed in"
# This POST both authenticates and renders the dashboard.
snap admin-dashboard      POST /administrator/bienvenido.php "user=$ADMIN_USER&passwd=$ADMIN_PASS"
snap admin-producto-edit  GET '/administrator/formProducto.php?accion=E&id_producto=1&id_master=1&mod_prod=B5'
snap admin-producto-new   GET '/administrator/formProducto.php?action=N'
snap admin-detalles-full  GET '/administrator/formDetalles.php?id_producto=1&mod_prod=B5'
snap admin-detalles-empty GET '/administrator/formDetalles.php?id_producto=4&mod_prod=B12'
snap admin-colores-four   GET '/administrator/formColores.php?id_producto=5&mod_prod=BF300'
snap admin-colores-one    GET '/administrator/formColores.php?id_producto=1&mod_prod=B5'

echo "Admin AJAX"
snap ajax-vehiculo-none   POST /administrator/VehiculoSelect.php 'veh_id=0'
snap ajax-vehiculo-moto   POST /administrator/VehiculoSelect.php 'veh_id=1'
snap ajax-vehiculo-taxi   POST /administrator/VehiculoSelect.php 'veh_id=2'
snap ajax-vehiculo-furgon POST /administrator/VehiculoSelect.php 'veh_id=3'

echo
echo "$(ls -1 "$OUT"/*.snap | wc -l) snapshots in $OUT"
