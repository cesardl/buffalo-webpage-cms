<?php

declare(strict_types=1);

use Buffalo\Domain\Producto;

use function Buffalo\View\e;

/** @var list<Producto> $productos */
/** @var array<int, string> $clases */
/** @var string $token */

ob_start();
?>
<table style="text-align: right; width: 100%;">
    <tr>
        <td style="text-align: left"><h3>Mantenimiento de veh&iacute;culos</h3></td>
        <td style="font-weight: bold;"><a href="/administrator/index.php?param=1">Cerrar sesi&oacute;n</a></td>
    </tr>
    <tr><td colspan="2"><hr/></td></tr>
</table>
<div id="result"></div>
<table class="table table-striped mt-3">
    <thead class="thead-dark">
    <tr>
        <th>Clase</th>
        <th>Modelo</th>
        <th>Descripci&oacute;n</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($productos as $producto) { ?>
        <tr id="fila-<?= (int) $producto->id ?>">
            <td><?= e($clases[$producto->masterId] ?? '') ?></td>
            <td><?= e($producto->modelo) ?></td>
            <td><?= e($producto->descripcion) ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<?php
$contenido = (string) ob_get_clean();
$titulo = 'Bienvenido';
require __DIR__ . '/../layout.php';
