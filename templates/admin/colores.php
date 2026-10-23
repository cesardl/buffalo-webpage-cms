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
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
              integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh"
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
                        <select name="color_1_<?= $i ?>" class="custom-select">
                            <option value="0">[Seleccione]</option>
                            <?= opcionesColor($palette, $fila->principal?->id) ?>
                        </select>
                    </div>
                    <div class="col">
                        <select name="color_2_<?= $i ?>" class="custom-select">
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
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
            integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n"
            crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
            integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
            integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
            crossorigin="anonymous"></script>
    </body>
    </html>
