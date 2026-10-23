<?php

declare(strict_types=1);

use Buffalo\Domain\Master;
use Buffalo\Domain\Producto;
use Buffalo\Domain\Vehiculo;

use function Buffalo\View\e;

/** @var string $titulo */
/** @var Producto|null $producto */
/** @var int $masterId */
/** @var int $vehiculoId */
/** @var list<Vehiculo> $vehiculos */
/** @var list<Master> $masters */
/** @var int $zoomSlots */
/** @var string $token */

// Four extra spaces on every line, from the else block the legacy page
// rendered inside.
$zoom = static fn (int $slot): string => $producto?->fotosZoom[$slot - 1] ?? '';
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
        <title>Formulario de producto</title>
    </head>
    <body>
    <div class="container pt-4">
        <div class="row">
            <div class="col"><h3><?= e($titulo) ?></h3></div>
            <div class="col-2 tdLabel">
                <a href="bienvenido.php">&lt;&lt; Volver</a>
            </div>
        </div>
        <form id="formProducto" method="POST" action="actionProductoCtrl.php" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= e($token) ?>"/>
            <input type="hidden" id="id_producto" name="id_producto" value="<?= e((string) ($producto->id ?? 0)) ?>"/>
            <div class="row my-2">
                <div class="col-2"><label for="vehiculo">Tipo veh&iacute;culo</label></div>
                <div class="col">
                    <select id="vehiculo" name="vehiculo" class="form-select">
                        <option value="0">[Seleccione]</option>
                        <?php
                        foreach ($vehiculos as $vehiculo) {
                            $selected = $vehiculo->id === $vehiculoId ? 'selected' : '';
                            echo "<option {$selected} value=\"" . e((string) $vehiculo->id) . '">'
                                . e($vehiculo->nombre) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col">
                    <select id="master" name="master" class="form-select">
                        <option value="0">[Seleccione]</option>
                        <?php
                        foreach ($masters as $master) {
                            $selected = $master->id === $masterId ? 'selected' : '';
                            echo "<option {$selected} value=\"" . e((string) $master->id) . '">'
                                . e($master->clase) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row my-2">
                <div class="col-2"><label for="modelo">Modelo</label></div>
                <div class="col">
                    <input type="text" id="modelo" name="modelo"
                           class="form-control"
                           value="<?= e($producto->modelo ?? '') ?>"/>
                </div>
            </div>
            <div class="row my-2">
                <div class="col-2">Foto principal</div>
                <div class="col">
                    <input type="hidden" id="v_imagen" name="v_imagen"
                           value="<?= e($producto->fotoPrincipal ?? '') ?>"/>
                    <input type="file" class="form-control" id="imagen" name="imagen">
                </div>
                <div class="col-1">
                    <?php
                    if (($producto->fotoPrincipal ?? '') !== '') {
                        echo '<img src="../' . e($producto->fotoPrincipal)
                            . '" class="img-thumbnail" alt="Foto principal"/>';
                    }
                    ?>
                </div>
            </div>
<?php for ($slot = 1; $slot <= $zoomSlots; $slot++) { ?>
            <div class="row my-2">
                <div class="col-2">Foto Zoom <?= $slot ?></div>
                <div class="col">
                    <input type="hidden" id="v_zoom<?= $slot ?>" name="v_zoom<?= $slot ?>"
                           value="<?= e($zoom($slot)) ?>"/>
                    <input type="file" id="zoom<?= $slot ?>" name="zoom<?= $slot ?>"/>
                </div>
                <div class="col-1">
                    <?php
                    if ($zoom($slot) !== '') {
                        echo '<img src="../' . e($zoom($slot))
                            . "\" class=\"img-thumbnail\" alt=\"Zoom {$slot}\"/>";
                    }
                    ?>
                </div>
            </div>
<?php } ?>
            <div class="row my-2">
                <div class="col-2">Ficha t&eacute;cnica</div>
                <div class="col">
                    <input type="hidden" name="v_fichtec" value="<?= e($producto->fichaTecnica ?? '') ?>"/>
                    <input type="file" id="fichtec" name="fichtec"/>
                </div>
                <div class="col-1">
                    <?php
                    if (($producto->fichaTecnica ?? '') !== '') {
                        echo '<img src="images/pdf.png" alt="Ficha tecnica"/>';
                    }
                    ?>
                </div>
            </div>
            <div class="row my-2">
                <div class="col-2"><label for="descripcion">Descripci&oacute;n</label></div>
                <div class="col">
                    <textarea id="descripcion" name="descripcion" rows="5"
                              class="form-control"><?= e($producto->descripcion ?? '') ?></textarea>
                </div>
            </div>
            <div class="row my-4">
                <div class="col" style="text-align: center;">
                    <input id="btn_acept" type="button" name="name" value="Aceptar" class="btn btn-primary"/>
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
    <script type="text/javascript" src="scritps/funciones_producto.js"></script>
    </body>
    </html>
