# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Static marketing site plus a PHP product administrator for Buffalo Motors (motorcycles, mototaxis, cargo vans). UI text, domain names and identifiers are Spanish — keep new code Spanish to match (`producto`, `vehiculo`, `clase`, `ficha_tecnica`).

## The port in progress

Two runtimes coexist on purpose. The legacy app (repository root + `administrator/`) still serves everything and is the behavioural reference; the PHP 8.3 app under `src/` + `public/` is being built up beside it and currently serves only a health check. Nothing has been ported yet.

| | legacy | target |
|---|---|---|
| Dockerfile | `Dockerfile` | `Dockerfile.php8` |
| runtime | PHP 5.6 + `ext/mysql` | PHP 8.3 + PDO |
| docroot | repository root | `public/` only |
| port used here | 8092 | 8093 |

The target image serves `public/` and nothing else, so no legacy file, `src/` class or `vendor/` path is reachable from it. Database settings come from `BUFFALO_DB_HOST`, `BUFFALO_DB_PORT`, `BUFFALO_DB_NAME`, `BUFFALO_DB_USER` and `BUFFALO_DB_PASS` — `Buffalo\Config` throws naming every missing one rather than falling back to a default.

```
docker build -f Dockerfile.php8 -t buffalo-webpage-php8:local .
docker run -d --name buffalo-webpage-php8 -p 8093:80 -e BUFFALO_DB_HOST=... -e BUFFALO_DB_PORT=... -e BUFFALO_DB_NAME=... -e BUFFALO_DB_USER=... -e BUFFALO_DB_PASS=... buffalo-webpage-php8:local
```

### Porting status

Every route the golden master covers is now served by the PHP 8 app as well. The legacy app is still the reference and still runs; nothing has been deleted.

`./tests/verify-golden.sh http://localhost:8093` compares the replacement against the snapshots. Ten routes differ, and every differing line is one of three deliberate things:

- a hidden `_token` field on each ported form, and the token added to the dashboard's delete call;
- on `formProducto.php?action=N` only, the disappearance of two `mysql_fetch_assoc()`/`mysql_num_rows()` warnings. The "Nuevo producto" link passes no `id_master`, so the legacy query runs `WHERE id_master =`, errors, and prints the warnings into the page. The port does not reproduce that.

Nothing else differs. If a fourth category ever appears, it is a regression.

Line endings are normalised before comparing, and the reason and its cost are documented at the top of `verify-golden.sh`.

The three public listings were three files differing by one hardcoded vehicle id; they are one controller, one template and one table of ids now. Output is byte-identical.

Escaping changed: the legacy pages interpolated model names, titles and image paths raw and escaped only two description fields. Everything is escaped now. That makes no difference to the current bytes because no row holds `<`, `>`, `&`, `"` or `'` — the equivalence is a property of the data, not of the two implementations.

### Routing and auth

`public/index.php` builds a `Container`, hands `RouteTable::all()` to the `Router` and dispatches. Routes carry the legacy URLs (`/administrator/index.php` and friends) so the two applications can be compared route for route at cutover; Apache reaches them with `FallbackResource`, since none exist as files under `public/`.

Two things the legacy app got wrong by omission are now structural:

- **Access is a required argument on every `Route`**, with no default, so a route cannot be added without someone choosing `Public`, `Admin` or `AdminApi`. `Admin` redirects to the login page, `AdminApi` returns 403 — a redirect to an HTML form would otherwise be injected into the page that made the XHR call.
- **Every POST needs a valid CSRF token**, including the public login. No controller performs either check, so no controller can forget one.

`bin/migrate-passwords.php` converts `usuario` to InnoDB, widens `passwd` from varchar(50) (too narrow for bcrypt's 60 characters) to 255, and hashes anything still in clear text. It is idempotent. Both applications share the table, so the legacy `UsuarioDAO` was taught to verify hashes too, with a clear-text fallback so restoring an old dump does not lock the admin out.

**One thing to carry into the cutover:** CSRF means a ported form carries a hidden `_token` field the legacy snapshot does not have, so those routes will not be byte-identical. That diff is intended, and should be reviewed field by field rather than regenerated blindly.

### What exists in `src/` so far

`Domain/` holds immutable `final readonly` value objects, and `Repository/` holds one interface per aggregate with a `Pdo*` implementation beside it. Callers depend on the interface and receive a `PDO` they did not create, so nothing below the HTTP layer decides where the connection comes from any more.

Two translations live in the repositories on purpose, so nothing above them inherits a database quirk:

- `colorproducto.id_color_1/2` are NOT NULL and store `0` for "no colour". `PdoColorRepository` maps that to `null`, and uses `LEFT JOIN` so a single-colour combination is not dropped entirely.
- `producto.foto_zoom_1..4` are positional. `Producto::$fotosZoom` always holds exactly four entries, empty ones included, because the admin form edits them by position; `fotosZoomPresentes()` is what a gallery wants.

Every listing query carries an explicit `ORDER BY`, in both the new repositories and the legacy DAOs. None of them used to, and the order they happened to return was not stable — writing to `producto` was enough to reorder the admin dashboard. The order chosen everywhere is the one the pages already displayed, so this pinned existing behaviour rather than changing it.

Composer is only there for PSR-4 autoloading and PHPUnit; the application has no runtime dependencies, and adding one should need a reason. There is no PHP or Composer on the host — run both through a container.

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
- **SQL injection in the legacy queries.** Every legacy query still interpolates request values unescaped, including every `id_producto` in the public pages. The login is the exception: `UsuarioDAO::getUsuario` escapes the username and verifies the password in PHP. The new `src/Repository` code uses prepared statements throughout.
- **Fixed-size forms.** Colors are hardcoded to 4 rows and details to 6, duplicated between the `form*` page and its `action*` handler. Changing the count means editing both.
- `formProducto.php` reads `$_GET['accion']`, but `bienvenido.php` links "Nuevo producto" as `?action=N` — the new-product path works only because anything other than `'E'` falls through to the insert branch.
- `mysql_set_charset("UTF-8")` in `Connection.php` is not a valid MySQL charset name (should be `utf8`); the tables are `utf8`.

## Tests

Two layers, and they cover different things.

**PHPUnit** covers the new `src/` code only. `tests/Unit` needs nothing; `tests/Integration` skips itself unless `BUFFALO_DB_*` is set, so the suite is runnable with no database.

```
docker run --rm -v "$(pwd -W):/app" -w /app buffalo-webpage-php8:local vendor/bin/phpunit
```

**The golden master** covers the legacy app, which has no unit tests and will not get any — it is being replaced, not tested into shape: `tests/golden/*.snap` holds the exact output of all 32 read-only routes, captured from the running container.

- `./tests/capture-golden.sh [base-url]` rewrites the snapshots. Only run it when a change is *meant* to alter output, and review the diff.
- `./tests/verify-golden.sh [base-url]` recaptures and diffs, exiting non-zero on any drift. Run it after every change.

Snapshots are marked `-text` in `.gitattributes` because the served HTML contains CRLF; letting git normalise them makes every check fail on a fresh clone for reasons unrelated to the code.

The `action*.php` endpoints are covered only signed out, where they exit before touching the database. Their successful write paths are not pinned by anything yet — that needs integration tests.
