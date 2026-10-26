<?php

declare(strict_types=1);

use Buffalo\Domain\Master;
use Buffalo\Domain\Producto;
use Buffalo\Domain\Vehiculo;

use function Buffalo\View\e;

/** @var string $encabezado */
/** @var Producto|null $producto */
/** @var int $masterId */
/** @var int $vehiculoId */
/** @var list<Vehiculo> $vehiculos */
/** @var list<Master> $masters */
/** @var int $zoomSlots */
/** @var string $token */
/** @var string $titulo */
/** @var string $menuActivo */
/** @var string $usuario */
/** @var int $nuevas */

/*
 * The ids here are a contract with scritps/funciones_producto.js, which
 * binds to #vehiculo, #master, #modelo, #v_imagen, #imagen, #descripcion,
 * #btn_acept and #formProducto. The names are a contract with
 * GuardarProductoController. Neither moves when the look does.
 */
$zoom = static fn (int $slot): string => $producto?->fotosZoom[$slot - 1] ?? '';

ob_start();
?>
<h1 class="titulo-pagina"><?= e($encabezado) ?></h1>
<p class="bajada"><a href="vehiculos.php">Volver a vehículos</a></p>

<section class="tarjeta">
    <form id="formProducto" method="POST" action="actionProductoCtrl.php" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= e($token) ?>"/>
        <input type="hidden" id="id_producto" name="id_producto" value="<?= e((string) ($producto->id ?? 0)) ?>"/>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="vehiculo">Tipo de vehículo</label>
                <select id="vehiculo" name="vehiculo" class="form-select">
                    <option value="0">Seleccione</option>
                    <?php foreach ($vehiculos as $vehiculo) { ?>
                        <option value="<?= e((string) $vehiculo->id) ?>"<?= $vehiculo->id === $vehiculoId ? ' selected' : '' ?>><?= e($vehiculo->nombre) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="master">Clase</label>
                <select id="master" name="master" class="form-select">
                    <option value="0">Seleccione</option>
                    <?php foreach ($masters as $master) { ?>
                        <option value="<?= e((string) $master->id) ?>"<?= $master->id === $masterId ? ' selected' : '' ?>><?= e($master->clase) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="modelo">Modelo</label>
                <input type="text" id="modelo" name="modelo" class="form-control"
                       value="<?= e($producto->modelo ?? '') ?>"/>
            </div>
        </div>

        <fieldset class="grupo">
            <legend>Fotografías</legend>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="imagen">Foto principal</label>
                    <input type="hidden" id="v_imagen" name="v_imagen" value="<?= e($producto->fotoPrincipal ?? '') ?>"/>
                    <input type="file" class="form-control" id="imagen" name="imagen">
                    <?php if (($producto->fotoPrincipal ?? '') !== '') { ?>
                        <img src="../<?= e($producto->fotoPrincipal) ?>" class="miniatura mt-2" alt="Foto principal"/>
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <?php for ($slot = 1; $slot <= $zoomSlots; $slot++) { ?>
                    <div class="col-md-3 mb-3">
                        <label for="zoom<?= $slot ?>">Zoom <?= $slot ?></label>
                        <input type="hidden" id="v_zoom<?= $slot ?>" name="v_zoom<?= $slot ?>" value="<?= e($zoom($slot)) ?>"/>
                        <input type="file" class="form-control" id="zoom<?= $slot ?>" name="zoom<?= $slot ?>"/>
                        <?php if ($zoom($slot) !== '') { ?>
                            <img src="../<?= e($zoom($slot)) ?>" class="miniatura mt-2" alt="Zoom <?= $slot ?>"/>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </fieldset>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="fichtec">Ficha técnica</label>
                <input type="hidden" name="v_fichtec" value="<?= e($producto->fichaTecnica ?? '') ?>"/>
                <input type="file" class="form-control" id="fichtec" name="fichtec"/>
                <?php if (($producto->fichaTecnica ?? '') !== '') { ?>
                    <p class="kpi-detalle mt-2">Ya tiene una ficha cargada.</p>
                <?php } ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="4" class="form-control"><?= e($producto->descripcion ?? '') ?></textarea>
        </div>

        <div class="barra-acciones">
            <input id="btn_acept" type="button" name="name" value="Guardar producto" class="btn btn-primary"/>
            <a class="btn btn-link" href="vehiculos.php">Cancelar</a>
        </div>
    </form>
</section>
<?php
$contenido = (string) ob_get_clean();

$pie = <<<'HTML'
<script src="scritps/funciones_producto.js"></script>
HTML;

require __DIR__ . '/_consola.php';
