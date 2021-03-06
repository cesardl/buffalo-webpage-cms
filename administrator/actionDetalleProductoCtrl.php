<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" type="image/jpg" href="favicon.ico"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh"
          crossorigin="anonymous">
    <title>Operaci&oacute;n realizada</title>
</head>
<body>
<div class="container pt-4">
    <?php
    include_once 'domain/DetalleProducto.php';
    include_once 'bussinessLogic/DetalleProductoBL.php';

    $messages = array('No es un archivo valido', 'Ya existe es un archivo con ese nombre', 'Tranferencia de imagen falló.');

    $id_producto = $_POST['id_producto'];

    $bl = new DetalleProductoBL();

    for ($i = 0; $i < 6; $i++) {
        $delete = $_POST["delete$i"];
        $id_detalle_producto = $_POST["id_det_prod$i"];
        if (count($delete) != 0) {
            if ($id_detalle_producto != 0) {
                $bl->deleteDetalleProducto($id_detalle_producto);
            }
        } else {
            $descripcion = trim($_POST["descripcion$i"]);
            if (strlen($descripcion) != 0) {
                $detalleProducto = new DetalleProducto();
                $detalleProducto->setId_detalle_producto(trim($id_detalle_producto));
                $detalleProducto->setTitulo(strtoupper(trim($_POST["titulo$i"])));
                $detalleProducto->setDescripcion($descripcion);

                $foto_detalle = $_POST["v_imagen$i"];
                $nombre_archivo = $_FILES["imagen$i"]['name'];
                $tipo_archivo = $_FILES["imagen$i"]['type'];
                $tamano_archivo = $_FILES["imagen$i"]['size'];
                $imagen_temp = $_FILES["imagen$i"]['tmp_name'];
                if ($tamano_archivo != 0) {
                    $foto_detalle = uploadPhoto($tipo_archivo, $tamano_archivo, $nombre_archivo, $imagen_temp);
                    if (is_numeric($foto_detalle)) {
                        echo "No se ha podido cargar la imagen $nombre_archivo<br>$messages[$foto_detalle]";
                        echo "Intente carga la imagen de nuevo<br>";
                    }
                }

                $detalleProducto->setFoto_detalle($foto_detalle);
                $detalleProducto->setId_producto($id_producto);

                $detallesProducto[] = $detalleProducto;
            }
        }
    }

    echo '<br><h2>Total a procesar: ' . count($detallesProducto) . ' vehiculo(s)</h2><br>';

    $bl->insertOrUpdates($detallesProducto);
    ?>
    <a href='bienvenido.php'>&lt;&lt; Regresar</a>
</div>
</body>
</html>

<?php
function uploadPhoto($tipo_archivo, $tamano_archivo, $nombre_archivo, $imagen_temp)
{
    if ($tamano_archivo == 0) {
        return '';
    } else {
        if (!((strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpg") ||
                strpos($tipo_archivo, "jpeg") || strpos($tipo_archivo, "png")) && ($tamano_archivo < 5000000))) {
            return 0;
        } else {
            $ruta = "../images/detalle/" . $nombre_archivo;
            $path = 'images/detalle/' . $nombre_archivo;
            if (is_uploaded_file($imagen_temp)) {
                move_uploaded_file($imagen_temp, $ruta);
                if (file_exists($ruta)) {
                    return $path;
                } else {
                    return 1;
                }
            } else {
                return 2;
            }
        }
    }
}

?>

