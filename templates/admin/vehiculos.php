<?php

declare(strict_types=1);

use Buffalo\Domain\Producto;
use Buffalo\Panel\EstadoMedios;

use function Buffalo\View\e;

/** @var list<Producto> $productos */
/** @var array<int, string> $clases */
/** @var array<int, EstadoMedios> $medios */
/** @var string $token */
/** @var string $titulo */
/** @var string $menuActivo */
/** @var string $usuario */
/** @var int $nuevas */

/*
 * The description column is gone. It ran to a paragraph a row and pushed
 * everything that can be acted on off to the right; it is still one click
 * away under Editar. What replaces it answers the question the list is
 * actually for -- is this model ready to show on the site? -- in three
 * glyphs: its photo, its zoom gallery, its colour combinations.
 *
 * Recorded and present are different things, and the difference is worth a
 * colour: four seeded products name photographs that are not on the disk.
 */
$medio = static function (string $icono, string $estado, string $explicacion, string $valor = ''): string {
    return '<span class="medio ' . $estado . '" title="' . e($explicacion) . '">'
        . '<span class="material-symbols-outlined" aria-hidden="true">' . $icono . '</span>'
        . ($valor === '' ? '' : '<span class="medio-valor" aria-hidden="true">' . e($valor) . '</span>')
        . '<span class="solo-lectores">' . e($explicacion) . '</span>'
        . '</span>';
};

$accion = static function (string $url, string $icono, string $etiqueta, string $clase = ''): string {
    return '<a class="accion' . $clase . '" href="' . e($url) . '" title="' . e($etiqueta) . '"'
        . ' aria-label="' . e($etiqueta) . '">'
        . '<span class="material-symbols-outlined" aria-hidden="true">' . $icono . '</span></a>';
};

$plural = static fn (int $n, string $uno, string $varios): string => $n === 1 ? $uno : sprintf($varios, $n);

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
                <th>Medios</th>
                <th class="columna-acciones">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $producto) {
                $id = (string) $producto->id;
                $modelo = $producto->modelo;
                $estado = $medios[$producto->id] ?? null;
                ?>
                <tr id="fila-<?= e($id) ?>">
                    <td><?= e($clases[$producto->masterId] ?? '') ?></td>
                    <td><?= e($modelo) ?></td>
                    <td class="medios">
                        <?php
                        if ($estado === null) {
                            echo '';
                        } else {
                            if (!$estado->principalRegistrada) {
                                echo $medio('image', 'es-vacio', 'Sin foto principal');
                            } elseif ($estado->principalRota()) {
                                echo $medio(
                                    'broken_image',
                                    'es-roto',
                                    'La foto principal apunta a un archivo que no está en el servidor',
                                );
                            } else {
                                echo $medio('image', 'es-ok', 'Foto principal cargada');
                            }

                            $rotos = $estado->zoomsRotos();
                            $explicacion = $estado->zoomsRegistrados === 0
                                ? 'Sin fotos de zoom'
                                : sprintf(
                                    '%d de %d fotos de zoom',
                                    $estado->zoomsRegistrados,
                                    Producto::ZOOM_SLOTS,
                                );

                            if ($rotos > 0) {
                                $explicacion .= ', ' . $plural($rotos, '1 sin archivo', '%d sin archivo');
                            }

                            echo $medio(
                                'collections',
                                $rotos > 0 ? 'es-roto' : ($estado->zoomsRegistrados === 0 ? 'es-vacio' : 'es-ok'),
                                $explicacion,
                                $estado->zoomsRegistrados . '/' . Producto::ZOOM_SLOTS,
                            );

                            echo $medio(
                                'palette',
                                $estado->combinaciones === 0 ? 'es-vacio' : 'es-ok',
                                $estado->combinaciones === 0
                                    ? 'Sin combinaciones de color'
                                    : $plural($estado->combinaciones, '1 combinación de color', '%d combinaciones de color'),
                                (string) $estado->combinaciones,
                            );
                        }
                        ?>
                    </td>
                    <td class="acciones">
                        <?= $accion(
                            "formProducto.php?accion=E&id_producto={$id}&id_master={$producto->masterId}&mod_prod=" . rawurlencode($modelo),
                            'edit',
                            "Editar {$modelo}",
                        ) ?>
                        <?= $accion(
                            "formDetalles.php?id_producto={$id}&mod_prod=" . rawurlencode($modelo),
                            'format_list_bulleted',
                            "Detalles de {$modelo}",
                        ) ?>
                        <?= $accion(
                            "formColores.php?id_producto={$id}&mod_prod=" . rawurlencode($modelo),
                            'palette',
                            "Colores de {$modelo}",
                        ) ?>
                        <?= $accion("#{$id}", 'delete', "Eliminar {$modelo}", ' a_delete es-peligro') ?>
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
// travels on the table's data attribute rather than interpolated into the
// script.
$pie = <<<'HTML'
<script src="scritps/vehiculos.js"></script>
HTML;

require __DIR__ . '/_consola.php';
