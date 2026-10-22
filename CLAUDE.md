# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Static marketing site plus a PHP product administrator for Buffalo Motors (motorcycles, mototaxis, cargo vans). UI text, domain names and identifiers are Spanish — keep new code Spanish to match (`producto`, `vehiculo`, `clase`, `ficha_tecnica`).

## Stack and running it

No build step, no package manager, no test suite, no CI. Plain PHP files served from the document root; raw SQL via the **removed** `mysql_*` extension, so this runs only on **PHP 5.x** (it will fatal on PHP 7+). Bootstrap 4.4.1 / jQuery 3.4.1 come from CDNs in the admin; the public site uses the vendored jQuery 1.4.2 in `js/`.

To run locally: point a PHP 5 + MySQL stack (XAMPP/WAMP) at the repo root, then import `administrator/util/buffalo_data.sql` (schema + seed data, including the `root`/`root` admin user). Public site at `/index.html`, admin at `/administrator/index.php`.

DB credentials are hardcoded in `administrator/util/Connection.php` — edit that file to point at your local MySQL.

## Architecture

Two independent halves hitting the same database:

**Admin (`administrator/`)** — layered, by convention `domain/` → `dao/` → `bussinessLogic/` → page controllers:

- `domain/*.php` — anemic entities, private fields + `getX_y()`/`setX_y()` accessors mirroring column names. `Producto` holds a `Master`; `ColorProducto` holds two `Color`s and a `Producto`.
- `dao/*DAO.php` — the only place SQL lives. Each method news its own `Connection` (one connection per call, no pooling or transactions), runs `mysql_query`, and hydrates domain objects by hand.
- `bussinessLogic/*BL.php` — thin wrappers that own a DAO instance. They return *user-facing Spanish strings* for writes (`"Se inserto 1 producto"`), not booleans, and some `echo` directly (`ColorBL::insertOrUpdates`) — so BL methods are presentation-coupled, not reusable from a non-HTML context.
- Pages: `form*.php` render, `action*.php` / `Vehiculo*.php` handle POST. Both mix PHP and HTML in one file and `echo` their own full Bootstrap document.

**Public site (repo root)** — `*.html` are static. `productos_furgon.php`, `productos_motocicleta.php`, `productos_mototaxis.php` and `modalProduct.php` **bypass the domain/DAO/BL layers entirely**: they `include administrator/util/Connection.php` and run inline `mysql_query` against `master`/`producto`/`colorproducto`/`detalleproducto`. The three product pages are near-identical copies differing only by a hardcoded `id_vehiculo` (1 = Motocicletas, 2 = Mototaxis, 3 = Furgon); a change to one usually belongs in all three.

### Data model

`vehiculo` (vehicle type) → `master` (class/segment within a type) → `producto` (model) → `detalleproducto` (feature bullets, max 6 per product in the UI) and `colorproducto` (up to 4 one- or two-tone color pairs per product, referencing `color`). `usuario` holds admin logins.

### Include paths

DAO/BL files include each other with paths relative to the *entry script's* directory (`include_once 'dao/ProductoDAO.php'`, `include_once 'domain/Master.php'`). Those resolve only for scripts living in `administrator/`. A root-level page cannot include a BL or DAO — that is why the public pages talk to `Connection` directly. Keep new admin entry points in `administrator/`.

### Uploads

`actionProductoCtrl.php` and `actionDetalleProductoCtrl.php` each define their own local `uploadPhoto()` (duplicated, with slightly different signatures) that validates by MIME substring + 5 MB cap and `move_uploaded_file`s into `../images/principal/`, `../images/zoom/` or `../images/detalle/`. PDFs go to `../ficha_tecnica/`. The DB stores the web-relative path; on success the function returns the path string, on failure an integer index into a local `$messages` array — hence the `is_numeric($foto_detalle)` checks in callers. Uploaded media is committed to the repo.

## Known hazards (confirm before "fixing")

These are pervasive and intentional-by-neglect in this legacy code. Don't start a sweeping rewrite unprompted; do not introduce *new* instances.

- **SQL injection everywhere.** Every query interpolates request values unescaped, including the login (`UsuarioDAO::getUsuario`) and every `id_producto` in the public pages. Passwords are stored and compared in plaintext.
- **No auth on the action endpoints.** `form*.php` check `$_SESSION['user']`; `action*.php`, `VehiculoDelete.php` and `VehiculoSelect.php` do not — anyone can POST to them.
- **Fixed-size forms.** Colors are hardcoded to 4 rows and details to 6, duplicated between the `form*` page and its `action*` handler. Changing the count means editing both.
- `formProducto.php` reads `$_GET['accion']`, but `bienvenido.php` links "Nuevo producto" as `?action=N` — the new-product path works only because anything other than `'E'` falls through to the insert branch.
- `mysql_set_charset("UTF-8")` in `Connection.php` is not a valid MySQL charset name (should be `utf8`); the tables are `utf8`.
