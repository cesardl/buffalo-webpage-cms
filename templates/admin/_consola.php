<?php

declare(strict_types=1);

use function Buffalo\View\e;

/** @var string $titulo */
/** @var string $contenido the page's body, already buffered */
/** @var string $menuActivo 'vehiculos', 'contactos', or '' on the panel */
/** @var string $usuario */
/** @var int $nuevas */

/*
 * The console's chrome: a bar, a menu, a canvas.
 *
 * Pages buffer their own body and require this file, which is the idiom
 * resultado.php already used with the old layout. $cabeza and $pie let a
 * page add to the head and the end of the body -- only the panel does, for
 * Chart.js.
 */
$cabeza = $cabeza ?? '';
$pie = $pie ?? '';

$activo = static fn (string $clave): string => $menuActivo === $clave ? ' is-active' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="../favicon.ico"/>
    <title><?= e($titulo) ?> · Buffalo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <link rel="stylesheet" href="style/consola.css">
<?= $cabeza ?>
</head>
<body class="consola">
<header class="barra">
    <a class="marca" href="bienvenido.php">
        <img src="../favicon.ico" width="20" height="20" alt="">
        <strong>Buffalo</strong> <span>Administración</span>
    </a>
    <div class="sesion">
        <?= e($usuario) ?>
        <a href="index.php?param=1">Salir</a>
    </div>
</header>
<nav class="menu" aria-label="Secciones">
    <a class="menu-item<?= $activo('vehiculos') ?>" href="vehiculos.php">Vehículos</a>
    <a class="menu-item<?= $activo('contactos') ?>" href="contactos.php">
        Solicitudes de contacto
        <?php if ($nuevas > 0) { ?>
            <span class="menu-contador"><?= e((string) $nuevas) ?></span>
        <?php } ?>
    </a>
</nav>
<main class="lienzo">
<?= $contenido ?>
</main>
<script src="https://code.jquery.com/jquery-4.0.0.min.js"
        integrity="sha384-fgGyf7Mo7DURSOMnOy7ed+dkq5Job205Gnzu6QIg0BOHKaqt4D76Dt8VlDCzcMHV"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
<?= $pie ?>
</body>
</html>
