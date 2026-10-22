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

snap() {
  local name="$1" method="$2" path="$3" data="${4:-}" jar="${5:-$JAR}"
  local body result
  body="$(mktemp)"
  if [ "$method" = POST ]; then
    result="$(curl -sS -b "$jar" -c "$jar" -o "$body" \
      -w '%{http_code} %{redirect_url}' -X POST -d "$data" "$BASE$path")"
  else
    result="$(curl -sS -b "$jar" -c "$jar" -o "$body" \
      -w '%{http_code} %{redirect_url}' "$BASE$path")"
  fi
  { printf '# %s %s -> %s\n' "$method" "$path" "$result"; cat "$body"; } > "$OUT/$name.snap"
  rm -f "$body"
  printf '  %-34s %s\n' "$name" "$result"
}

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
