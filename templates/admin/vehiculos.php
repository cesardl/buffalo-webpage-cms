<?php

declare(strict_types=1);

use Buffalo\Domain\Producto;

use function Buffalo\View\e;

/** @var list<Producto> $productos */
/** @var array<int, string> $clases */
/** @var string $token */
/** @var string $titulo */
/** @var string $menuActivo */
/** @var string $usuario */
/** @var int $nuevas */

ob_start();
?>
<h1 class="titulo-pagina">Vehículos</h1>
<p class="bajada">Los modelos publicados en el sitio.</p>

<section class="tarjeta">
    <div class="barra-acciones es-cabecera">
        <a class="btn btn-primary" href="formProducto.php?action=N">Nuevo producto</a>
    </div>

    <div id="result"></div>

    <?php if ($productos === []) { ?>
        <p class="vacio">Todavía no hay productos. El primero se crea con «Nuevo producto».</p>
    <?php } else { ?>
        <table class="tabla" id="tabla-vehiculos" data-token="<?= e($token) ?>">
            <thead>
            <tr>
                <th>Clase</th>
                <th>Modelo</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $producto) {
                $id = (string) $producto->id;
                $modelo = e($producto->modelo);
                ?>
                <tr id="fila-<?= e($id) ?>">
                    <td><?= e($clases[$producto->masterId] ?? '') ?></td>
                    <td><?= $modelo ?></td>
                    <td><?= e($producto->descripcion) ?></td>
                    <td class="acciones">
                        <a href="formProducto.php?accion=E&id_producto=<?= e($id) ?>&id_master=<?= e((string) $producto->masterId) ?>&mod_prod=<?= $modelo ?>">Editar</a>
                        <a href="formDetalles.php?id_producto=<?= e($id) ?>&mod_prod=<?= $modelo ?>">Detalles</a>
                        <a href="formColores.php?id_producto=<?= e($id) ?>&mod_prod=<?= $modelo ?>">Colores</a>
                        <a href="#<?= e($id) ?>" class="a_delete">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</section>
<?php
$contenido = (string) ob_get_clean();

// After jQuery, which the shell loads at the end of the body. The token
// travels in a data attribute rather than interpolated into the script.
$pie = <<<'HTML'
<script src="scritps/vehiculos.js"></script>
HTML;

require __DIR__ . '/_consola.php';
