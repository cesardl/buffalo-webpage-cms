<?php

declare(strict_types=1);

use function Buffalo\View\e;

/** @var int $error */
/** @var bool $despedida */
/** @var string $token */

/*
 * Signed out, so no menu and no console shell: one card on the canvas.
 */
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="../favicon.ico"/>
    <title>Entrar · Buffalo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <link rel="stylesheet" href="style/consola.css">
</head>
<body class="acceso consola">
<main class="acceso-tarjeta">
    <div class="acceso-marca">
        <img src="../favicon.ico" width="24" height="24" alt="">
        <strong>Buffalo</strong> <span>Administración</span>
    </div>

    <?php if ($error === 1) { ?>
        <div class="alert alert-warning" role="alert">
            Usuario o contraseña incorrectos. Inténtelo de nuevo.
        </div>
    <?php } elseif ($error === 2) { ?>
        <div class="alert alert-warning" role="alert">
            Entre para ver esa página.
        </div>
    <?php } ?>

    <?php if ($despedida) { ?>
        <div class="alert alert-light" role="status">
            Sesión cerrada. Hasta la próxima.
        </div>
    <?php } ?>

    <form method="POST" action="bienvenido.php">
        <input type="hidden" name="_token" value="<?= e($token) ?>"/>

        <div class="mb-3">
            <label for="user">Usuario</label>
            <input type="text" class="form-control" id="user" name="user" autocomplete="username" autofocus/>
        </div>

        <div class="mb-3">
            <label for="passwd">Contraseña</label>
            <input type="password" class="form-control" id="passwd" name="passwd" autocomplete="current-password"/>
        </div>

        <div class="barra-acciones">
            <input type="submit" id="btn_ingresar" value="Entrar" class="btn btn-primary"/>
        </div>
    </form>
</main>
</body>
</html>
