# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Public product site and administrator for Buffalo Motors (motorcycles, mototaxis, cargo vans). UI text, domain names and identifiers are Spanish — keep new code Spanish to match (`producto`, `vehiculo`, `clase`, `ficha_tecnica`).

It began as a PHP 5.6 application using the removed `ext/mysql`, and was ported to PHP 8.3 over six steps on the `resucite-project` branch. The old application is gone; its behaviour survives as the golden master described below.

## Running it

No PHP or Composer on the host — everything runs through containers.

```
docker build -t buffalo-webpage:local .
docker run -d --name buffalo-webpage -p 8092:80 -e BUFFALO_DB_HOST=... -e BUFFALO_DB_PORT=... -e BUFFALO_DB_NAME=... -e BUFFALO_DB_USER=... -e BUFFALO_DB_PASS=... buffalo-webpage:local
```

`Buffalo\Config` throws naming every missing variable rather than defaulting, so a half-configured container fails at once instead of connecting somewhere unexpected.

`database/buffalo_data.sql` is the schema and seed. Import it with `--default-character-set=utf8mb4`, or the Spanish accents get double-encoded on the way in. Then run `bin/migrate-passwords.php`, which converts `usuario` to InnoDB, widens `passwd` to 255 (bcrypt needs 60) and hashes anything still in clear text. It is idempotent.

A PHP 5.6 client cannot complete a `caching_sha2_password` handshake, which mattered before the port and no longer does; PDO on 8.3 handles it.

## Architecture

```
public/index.php   front controller: Container -> RouteTable -> Router -> Response
public/            everything served directly: the five static pages, css, js, images, ficha_tecnica
src/Domain/        immutable final readonly value objects
src/Repository/    one interface per aggregate, with a Pdo* implementation beside it
src/Catalogo/      assembles what a public listing needs
src/Http/          Request, Response, Route, Router, Session, controllers
src/Security/      Authenticator
src/Upload/        FileValidator (pure, tested) + Uploader (needs a live request)
src/View/          Template and the escaping helpers
templates/         plain PHP templates
```

**URLs still end in `.php`** (`/productos_furgon.php`, `/administrator/index.php`). They are what is linked and bookmarked, and keeping them is what let the port be compared against the old application route for route. None exist as files, so Apache reaches them with `mod_rewrite` — `FallbackResource` does not work here, because for a path ending in `.php` the PHP handler claims the request and then fails to stat the script.

### Decisions worth not undoing

- **`Access` is a required argument on every `Route`**, with no default. The old application decided this per file and got it wrong on five endpoints, which were reachable with no session at all. `Admin` redirects to the login page; `AdminApi` returns 403, because redirecting an XHR call to an HTML form injects that form into the calling page as data.
- **Every POST needs a valid CSRF token**, the public login included. Exempting routes one at a time is how the session checks came to be missing.
- **Every listing query has an explicit `ORDER BY`.** None used to. The order they happened to return was not stable — writing to `producto` was enough to reorder the admin dashboard, which is how the golden master first caught it.
- **Uploads are checked by extension *and* by the bytes on disk, and stored under a generated name.** The old gate read the declared content type, which the caller picks, and kept the submitted filename; a script announced as an image landed in the web root executable.
- **The three public listings are one controller, one template and one table of vehicle ids.** They used to be three files differing by one hardcoded id.

### Database quirks translated at the repository boundary

- `colorproducto.id_color_1/2` are NOT NULL and store `0` for "no colour". `PdoColorRepository` maps that to `null` and uses `LEFT JOIN`, or a single-colour combination would vanish entirely.
- `producto.foto_zoom_1..4` are positional. `Producto::$fotosZoom` always holds four entries, empty ones included, because the admin form edits them by position; `fotosZoomPresentes()` is what a gallery wants.

### Data model

`vehiculo` (type) → `master` (class within a type) → `producto` (model) → `detalleproducto` (feature bullets, 6 slots in the UI) and `colorproducto` (4 one- or two-tone combinations, referencing `color`). `usuario` holds admin logins. The row counts are fixed in `FormDetallesController::FILAS` and `FormColoresController::FILAS`, read by both the form and the handler that receives it — they used to be separate literals in each.

## Tests

```
docker run --rm -v "$(pwd -W):/app" -w /app --network <net> -e BUFFALO_DB_... buffalo-webpage:local vendor/bin/phpunit
./tests/verify-golden.sh [base-url]
```

**PHPUnit** covers `src/`. `tests/Unit` needs nothing; `tests/Integration` skips itself without `BUFFALO_DB_*`, and its write tests run inside a transaction that is rolled back.

**The golden master** (`tests/golden/*.snap`) holds the exact output of all 32 read-only routes, captured from the PHP 5.6 application before it was retired and updated once, at the cutover, for three reviewed differences. `verify-golden.sh` recaptures and diffs byte for byte, exiting non-zero on drift; `capture-golden.sh` rewrites the snapshots and should only be run when a change is *meant* to alter output.

Two values are normalised at capture time because they vary per run and would otherwise make every comparison fail: the CSRF token becomes `CSRF_TOKEN`, and redirects are recorded relative to the base URL. Snapshots are marked `-text` in `.gitattributes` so git stores them byte for byte.

The `action*.php` endpoints are covered only signed out, where they exit before touching the database. Their successful write paths are exercised by hand, not pinned — that still wants integration tests.

## Known gaps

- **No CI.** Nothing runs the suite or the golden master automatically; both only run when someone runs them.
- **SQL injection is gone** (everything is a prepared statement) and **passwords are hashed**, but the admin password in the seed is still `root`/`root`. Change it outside the repository.
- The successful write paths of the three `Guardar*` controllers have no automated coverage.
- **Four image paths in the data point at files that do not exist**, so B15, B12, B10 and B9 show broken zoom thumbnails, as do B15's three detail bullets. The rows name `images/zoom/productos_img1_small.jpg` and `_img2_small.jpg`, and the same two names under `images/detalle/`.

  Do not fix this by copying `public/images/productos_img1_small.jpg` and `_img2_small.jpg` into those directories. It was tried, and it works in the sense that every path resolves — but those two files are photographs of a blue cargo furgon, and all four models are motorcycles. A broken image is a visible defect; a van in the motorcycle listing is a confident lie. It needs real photographs for those four models, which is a content decision, or the rows repointed at images that already exist.
