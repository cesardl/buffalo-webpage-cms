<?php

declare(strict_types=1);

use Buffalo\Domain\Color;
use Buffalo\Domain\ColorProducto;

use function Buffalo\View\e;
use function Buffalo\View\opcionesColor;

/** @var string $modelo */
/** @var int $productoId */
/** @var list<ColorProducto> $filas */
/** @var list<Color> $palette */
/** @var string $token */

// The legacy page rendered inside an else block, so every line of its output
// carries four extra spaces. Reproduced so the comparison stays byte-level.
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" type="image/jpg" href="favicon.ico"/>
        <link type="text/css" href="style/style.css" rel="stylesheet" media="screen"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
              integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
              crossorigin="anonymous">
        <title>Formulario de colores</title>
    </head>
    <body>
    <div class="container pt-4">
        <div class="row">
            <div class="col"><h3>Seleccione las combinaciones de colores del <?= e($modelo) ?></h3></div>
            <div class="col-2 tdLabel">
                <a href="bienvenido.php">&lt;&lt; Volver</a>
            </div>
        </div>
        <form id="formColores" method="POST" action="actionColor.php" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= e($token) ?>"/>
            <input type="hidden" id="id_producto" name="id_producto" value="<?= e((string) $productoId) ?>"/>
            <?php foreach ($filas as $i => $fila) { ?>
                <div class="row my-2">
                    <div class="col-2 tdLabel">
                        Color <?= $i + 1 ?>
                        <input type="hidden" name="id<?= $i ?>"
                               value="<?= e((string) ($fila->id ?? 0)) ?>"/>
                    </div>
                    <div class="col">
                        <select name="color_1_<?= $i ?>" class="form-select">
                            <option value="0">[Seleccione]</option>
                            <?= opcionesColor($palette, $fila->principal?->id) ?>
                        </select>
                    </div>
                    <div class="col">
                        <select name="color_2_<?= $i ?>" class="form-select">
                            <option value="0">[Seleccione si es necesario]</option>
                            <?= opcionesColor($palette, $fila->secundario?->id) ?>
                        </select>
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="row my-2">
                <div class="col" style="text-align: center;">
                    <input id="btn_acept" type="submit" name="name" value="Aceptar" class="btn btn-primary"/>
                </div>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"
            integrity="sha384-fgGyf7Mo7DURSOMnOy7ed+dkq5Job205Gnzu6QIg0BOHKaqt4D76Dt8VlDCzcMHV"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
    </body>
    </html>
