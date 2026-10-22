<?php

include_once 'util/Auth.php';
include_once 'util/Upload.php';
include_once 'domain/Master.php';
include_once 'domain/Producto.php';
include_once 'bussinessLogic/ProductoBL.php';

require_admin();

$messages = array('No es un archivo valido',
    'Ya existe es un archivo con ese nombre',
    'Tranferencia de imagen falló.');

$title = $_POST['title'];
$id_producto = $_POST['id_producto'];
$modelo = trim($_POST['modelo']);
$descripcion = trim($_POST['descripcion']);
$foto_detalle = $_POST['v_imagen'];
$foto_zoom1 = $_POST['v_zoom1'];
$foto_zoom2 = $_POST['v_zoom2'];
$foto_zoom3 = $_POST['v_zoom3'];
$foto_zoom4 = $_POST['v_zoom4'];
$ficha_tecnica = $_POST['v_fichtec'];
$id_master = $_POST['master'];
?>

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
    <h3><?php echo $title ?></h3>
    <?php
    if (!empty($_FILES['imagen']['size'])) {
        $foto_detalle = upload_image($_FILES['imagen'], 'images/principal');
    }
    if (!empty($_FILES['zoom1']['size'])) {
        $foto_zoom1 = upload_image($_FILES['zoom1'], 'images/zoom');
    }
    if (!empty($_FILES['zoom2']['size'])) {
        $foto_zoom2 = upload_image($_FILES['zoom2'], 'images/zoom');
    }
    if (!empty($_FILES['zoom3']['size'])) {
        $foto_zoom3 = upload_image($_FILES['zoom3'], 'images/zoom');
    }
    if (!empty($_FILES['zoom4']['size'])) {
        $foto_zoom4 = upload_image($_FILES['zoom4'], 'images/zoom');
    }
    if (empty($ficha_tecnica)) {
        $ficha_tecnica = upload_pdf($_FILES['fichtec'], 'ficha_tecnica');
    }

    if (is_numeric($foto_detalle)) {
        echo "No se ha podido cargar la imagen {$_FILES['imagen']['name']}<br>$messages[$foto_detalle]";
        echo "<a href='index.php'>Regresar</a>";
    } else if (is_numeric($foto_zoom1)) {
        echo "No se ha podido cargar la imagen {$_FILES['zoom1']['name']}<br>$messages[$foto_zoom1]";
        echo "<a href='index.php'>Regresar</a>";
    } else if (is_numeric($foto_zoom2)) {
        echo "No se ha podido cargar la imagen {$_FILES['zoom2']['name']}<br>$messages[$foto_zoom2]";
        echo "<a href='index.php'>Regresar</a>";
    } else if (is_numeric($foto_zoom3)) {
        echo "No se ha podido cargar la imagen {$_FILES['zoom3']['name']}<br>$messages[$foto_zoom3]";
        echo "<a href='index.php'>Regresar</a>";
    } else if (is_numeric($foto_zoom4)) {
        echo "No se ha podido cargar la imagen {$_FILES['zoom4']['name']}<br>$messages[$foto_zoom4]";
        echo "<a href='index.php'>Regresar</a>";
    } else if (is_numeric($ficha_tecnica)) {
        echo "No se ha podido cargar el archivo";
        echo "<a href='index.php'>Regresar</a>";
    } else {
        $producto = new Producto();
        $producto->setId_producto($id_producto);
        $producto->setModelo($modelo);
        $producto->setDescripcion($descripcion);
        $producto->setFoto_principal($foto_detalle);
        $producto->setFoto_zoom_1($foto_zoom1);
        $producto->setFoto_zoom_2($foto_zoom2);
        $producto->setFoto_zoom_3($foto_zoom3);
        $producto->setFoto_zoom_4($foto_zoom4);
        $producto->setFicha_tecnica($ficha_tecnica);

        $master = new Master();
        $master->setId_master($id_master);
        $producto->setMaster($master);

        $dao = new ProductoBL();
        if ($id_producto == 0) {
            echo '<h2>' . $dao->insertProducto($producto) . '</h2><br>';
        } else {
            echo '<h2>' . $dao->updateProducto($producto) . '</h2><br>';
        }
    }
    ?>
    <a href='bienvenido.php'>&lt;&lt; Regresar</a>
</div>
</body>
</html>
