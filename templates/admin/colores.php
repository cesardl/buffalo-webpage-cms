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
/** @var string $titulo */
/** @var string $menuActivo */
/** @var string $usuario */
/** @var int $nuevas */

ob_start();
?>
<h1 class="titulo-pagina">Colores de <?= e($modelo) ?></h1>
<p class="bajada">
    Cada fila es una combinación. Deje el segundo color vacío si es de un solo tono.
    <a href="vehiculos.php">Volver a vehículos</a>
</p>

<section class="tarjeta">
    <form id="formColores" method="POST" action="actionColor.php" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= e($token) ?>"/>
        <input type="hidden" id="id_producto" name="id_producto" value="<?= e((string) $productoId) ?>"/>

        <?php foreach ($filas as $i => $fila) { ?>
            <div class="row align-items-end mb-3">
                <div class="col-md-2">
                    <label for="color_1_<?= $i ?>">Combinación <?= $i + 1 ?></label>
                    <input type="hidden" name="id<?= $i ?>" value="<?= e((string) ($fila->id ?? 0)) ?>"/>
                </div>
                <div class="col-md-5">
                    <select id="color_1_<?= $i ?>" name="color_1_<?= $i ?>" class="form-select">
                        <option value="0">Seleccione</option>
                        <?= opcionesColor($palette, $fila->principal?->id) ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <select name="color_2_<?= $i ?>" class="form-select" aria-label="Segundo color de la combinación <?= $i + 1 ?>">
                        <option value="0">Sin segundo color</option>
                        <?= opcionesColor($palette, $fila->secundario?->id) ?>
                    </select>
                </div>
            </div>
        <?php } ?>

        <div class="barra-acciones">
            <input id="btn_acept" type="submit" name="name" value="Guardar colores" class="btn btn-primary"/>
            <a class="btn btn-link" href="vehiculos.php">Cancelar</a>
        </div>
    </form>
</section>
<?php
$contenido = (string) ob_get_clean();

require __DIR__ . '/_consola.php';
