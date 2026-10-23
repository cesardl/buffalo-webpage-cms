<?php

declare(strict_types=1);

use Buffalo\Domain\Producto;

use function Buffalo\View\e;

/** @var list<Producto> $productos */
/** @var array<int, string> $clases */
/** @var string $token */

// Four extra spaces on every line, from the if block the legacy page
// rendered inside.
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="shortcut icon" type="image/jpg" href="favicon.ico"/>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
              integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh"
              crossorigin="anonymous">
        <title>Bienvenido</title>
    </head>
    <body>
    <div class="container pt-4">
        <table style="text-align: right; width: 100%;">
            <tr>
                <td style="text-align: left">
                    <h3>Mantenimiento de veh&iacute;culos</h3>
                </td>
                <td style="font-weight: bold;">
                    <a href="index.php?param=1">Cerrar sesi&oacute;n</a>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr/>
                </td>
            </tr>
            <tr>
                <td colspan="2"><a href="formProducto.php?action=N">Nuevo producto</a></td>
            </tr>
        </table>
        <div id="result"></div>
        <table class="table table-striped mt-3">
            <thead class="thead-dark">
            <tr>
                <th>Clase</th>
                <th>Modelo</th>
                <th>Descripci&oacute;n</th>
                <th colspan="4" style="text-align: center;">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $producto) {
                $id = (string) $producto->id;
                $modelo = e($producto->modelo);
                ?>
                <tr id="fila-<?= e($id) ?>">
                    <?php
                    echo '<td>' . e($clases[$producto->masterId] ?? '') . '</td>';
                    echo '<td>' . $modelo . '</td>';
                    echo '<td>' . e($producto->descripcion) . '</td>';
                    echo "<td><a href='formProducto.php?accion=E&id_producto={$id}&id_master={$producto->masterId}&mod_prod={$modelo}'>Editar</a></td>";
                    echo "<td><a href='formDetalles.php?id_producto={$id}&mod_prod={$modelo}'>Detalles</a></td>";
                    echo "<td><a href='formColores.php?id_producto={$id}&mod_prod={$modelo}'>Colores</a></td>";
                    echo "<td><a href='#{$id}' class='a_delete'>Eliminar</a></td>";
                    ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
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
    <script type="text/javascript">
        $(document).ready(function () {
            $('.a_delete').click(function () {
                if (confirm('Seguro que desea eliminar este vehiculo?')) {
                    var href = $(this).attr('href');
                    var id_product = href.substr(1);
                    $.post("VehiculoDelete.php", {
                        id_producto: id_product,
                        _token: "<?= e($token) ?>"
                    }, function (data) {
                        $("#result").html(data);
                        $("#fila-" + id_product).remove();
                    })
                }
            });

            $('#result').click(function () {
                $(this).hide();
            });
        });
    </script>
    </body>
    </html>
