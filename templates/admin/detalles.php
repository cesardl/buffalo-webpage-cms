<?php

declare(strict_types=1);

use Buffalo\Domain\DetalleProducto;

use function Buffalo\View\e;

/** @var string $modelo */
/** @var int $productoId */
/** @var list<DetalleProducto> $filas */
/** @var string $token */

// Four extra spaces on every line, because the legacy page rendered inside an
// else block. The unclosed <h3> below is in the original too, and is kept so
// the comparison stays byte-level; closing it is a separate, visible change.
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
        <title>Formulario de detalles</title>
    </head>
    <body>
    <div class="container pt-4">
        <div class="row">
            <div class="col"><h3>Detalles del Vehiculo <?= e($modelo) ?></div>
            <div class="col-2 tdLabel">
                <a href="bienvenido.php">&lt;&lt; Volver</a>
            </div>
        </div>
        <form method="POST" action="actionDetalleProductoCtrl.php" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= e($token) ?>"/>
            <input type="hidden" id="id_producto" name="id_producto" value="<?= e((string) $productoId) ?>"/>
            <?php
            foreach ($filas as $i => $detalle) {
                if (!($i % 2)) {
                    echo '<div class="row my-2">';
                }
                ?>
                <div class="col">
                    <input type="hidden" name="id_det_prod<?= $i ?>"
                           value="<?= e((string) ($detalle->id ?? 0)) ?>"/>
                    <div class="form-row my-2">
                        <div class="col-2 tdLabel"><label for="titulo<?= $i ?>">T&iacute;tulo</label></div>
                        <div class="col"><input type="text" id="titulo<?= $i ?>" name="titulo<?= $i ?>"
                                                class="form-control"
                                                value="<?= e($detalle->titulo) ?>"/>
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2 tdLabel"><label
                                    for="descripcion<?= $i ?>">Descripci&oacute;n</label>
                        </div>
                        <div class="col"><textarea id="descripcion<?= $i ?>" name="descripcion<?= $i ?>"
                                                   class="form-control"
                                                   rows="3"><?= e($detalle->descripcion) ?></textarea>
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2 tdLabel">Im&aacute;gen</div>
                        <div class="col">
                            <input type="hidden" name="v_imagen<?= $i ?>"
                                   value="<?= e($detalle->fotoDetalle) ?>"/>
                            <input type="file" id="imagen<?= $i ?>" name="imagen<?= $i ?>"/>
                            <?php
                            if ($detalle->fotoDetalle !== '') {
                                echo '<img src="../' . e($detalle->fotoDetalle)
                                    . '" alt="Foto principal" style="width: 5%;" />';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2 tdLabel"><label for="delete<?= $i ?>">Eliminar</label></div>
                        <div class="col">
                            <input type="checkbox" id="delete<?= $i ?>" name="delete<?= $i ?>[]"/>
                        </div>
                    </div>
                </div>
                <?php
                if ($i % 2) {
                    echo '</div>';
                }
            }
            ?>
            <div class="row my-2">
                <div class="col" style="text-align: center;">
                    <input type="submit" value="Aceptar" name="aceptar" class="btn btn-primary"/>
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
    