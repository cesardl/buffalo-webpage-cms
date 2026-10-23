<?php

declare(strict_types=1);

use function Buffalo\View\e;

/** @var string $titulo */
/** @var string $contenido */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/jpg" href="favicon.ico"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <title><?= e($titulo) ?></title>
</head>
<body>
<div class="container pt-4">
    <?= $contenido ?>
</div>
</body>
</html>
