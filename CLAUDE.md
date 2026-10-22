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

All uploads go through `administrator/util/Upload.php` (`upload_image()` / `upload_pdf()`). It checks the extension against an allowlist *and* the bytes on disk (`getimagesize()` for images, the `%PDF-` magic for PDFs), caps size at 5 MB, and writes under a generated name — the submitted filename is never reused. Images land in `images/principal/`, `images/zoom/` or `images/detalle/`, PDFs in `ficha_tecnica/`. The DB stores the web-relative path; on success the function returns that path, on failure an integer index into the caller's `$messages` array — hence the `is_numeric($foto_detalle)` checks. Uploaded media is committed to the repo.

## Known hazards (confirm before "fixing")

These are pervasive and intentional-by-neglect in this legacy code. Don't start a sweeping rewrite unprompted; do not introduce *new* instances.

- **Two guard mechanisms.** `form*.php` still carry their own inline `$_SESSION['user']` check; every other admin entry point calls `require_admin()` or `require_admin_ajax()` from `administrator/util/Auth.php`. Phase 4 collapses both into one router guard. Any new admin endpoint must call a guard — nothing enforces that yet.
- **SQL injection everywhere.** Every query interpolates request values unescaped, including the login (`UsuarioDAO::getUsuario`) and every `id_producto` in the public pages. Passwords are stored and compared in plaintext.
- **Fixed-size forms.** Colors are hardcoded to 4 rows and details to 6, duplicated between the `form*` page and its `action*` handler. Changing the count means editing both.
- `formProducto.php` reads `$_GET['accion']`, but `bienvenido.php` links "Nuevo producto" as `?action=N` — the new-product path works only because anything other than `'E'` falls through to the insert branch.
- `mysql_set_charset("UTF-8")` in `Connection.php` is not a valid MySQL charset name (should be `utf8`); the tables are `utf8`.

## Tests

There is no unit-test suite. What exists is a golden master: `tests/golden/*.snap` holds the exact output of all 32 read-only routes, captured from the running container.

- `./tests/capture-golden.sh [base-url]` rewrites the snapshots. Only run it when a change is *meant* to alter output, and review the diff.
- `./tests/verify-golden.sh [base-url]` recaptures and diffs, exiting non-zero on any drift. Run it after every change.

Snapshots are marked `-text` in `.gitattributes` because the served HTML contains CRLF; letting git normalise them makes every check fail on a fresh clone for reasons unrelated to the code.

The `action*.php` endpoints are covered only signed out, where they exit before touching the database. Their successful write paths are not pinned by anything yet — that needs integration tests.
