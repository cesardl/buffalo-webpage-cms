<?php

declare(strict_types=1);

use Buffalo\Domain\DetalleProducto;

use function Buffalo\View\e;

/** @var string $modelo */
/** @var int $productoId */
/** @var list<DetalleProducto> $filas */
/** @var string $token */
/** @var string $titulo */
/** @var string $menuActivo */
/** @var string $usuario */
/** @var int $nuevas */

ob_start();
?>
<h1 class="titulo-pagina">Detalles de <?= e($modelo) ?></h1>
<p class="bajada">
    Las viñetas que acompañan al modelo en el sitio.
    <a href="vehiculos.php">Volver a vehículos</a>
</p>

<section class="tarjeta">
    <form method="POST" action="actionDetalleProductoCtrl.php" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= e($token) ?>"/>
        <input type="hidden" id="id_producto" name="id_producto" value="<?= e((string) $productoId) ?>"/>

        <div class="row">
            <?php foreach ($filas as $i => $detalle) { ?>
                <div class="col-lg-6">
                    <fieldset class="grupo">
                        <legend>Detalle <?= $i + 1 ?></legend>
                        <input type="hidden" name="id_det_prod<?= $i ?>" value="<?= e((string) ($detalle->id ?? 0)) ?>"/>

                        <div class="mb-3">
                            <label for="titulo<?= $i ?>">Título</label>
                            <input type="text" id="titulo<?= $i ?>" name="titulo<?= $i ?>" class="form-control"
                                   value="<?= e($detalle->titulo) ?>"/>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion<?= $i ?>">Descripción</label>
                            <textarea id="descripcion<?= $i ?>" name="descripcion<?= $i ?>" rows="3"
                                      class="form-control"><?= e($detalle->descripcion) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="imagen<?= $i ?>">Imagen</label>
                            <input type="hidden" name="v_imagen<?= $i ?>" value="<?= e($detalle->fotoDetalle) ?>"/>
                            <input type="file" id="imagen<?= $i ?>" name="imagen<?= $i ?>" class="form-control"/>
                            <?php if ($detalle->fotoDetalle !== '') { ?>
                                <img src="../<?= e($detalle->fotoDetalle) ?>" class="miniatura mt-2" alt="Imagen del detalle"/>
                            <?php } ?>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="delete<?= $i ?>" name="delete<?= $i ?>[]"/>
                            <label class="form-check-label" for="delete<?= $i ?>">Eliminar este detalle</label>
                        </div>
                    </fieldset>
                </div>
            <?php } ?>
        </div>

        <div class="barra-acciones">
            <input type="submit" value="Guardar detalles" name="aceptar" class="btn btn-primary"/>
            <a class="btn btn-link" href="vehiculos.php">Cancelar</a>
        </div>
    </form>
</section>
<?php
$contenido = (string) ob_get_clean();

require __DIR__ . '/_consola.php';
