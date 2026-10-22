<?php
include_once 'util/Auth.php';
require_admin();
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
    <?php
    include_once 'domain/Color.php';
    include_once 'domain/ColorProducto.php';
    include_once 'domain/Producto.php';
    include_once 'bussinessLogic/ColorBL.php';

    $id_producto = $_POST['id_producto'];

    for ($i = 0; $i < 4; $i++) {
        $id = $_POST["id$i"];
        $id_color1 = $_POST["color_1_$i"];
        $id_color2 = $_POST["color_2_$i"];

        $colorProducto = new ColorProducto();
        $colorProducto->setId($id);

        $color_1 = new Color();
        $color_1->setId_color($id_color1);
        $colorProducto->setColor_1($color_1);

        $color_2 = new Color();
        $color_2->setId_color($id_color2);
        $colorProducto->setColor_2($color_2);

        $producto = new Producto();
        $producto->setId_producto($id_producto);
        $colorProducto->setProducto($producto);

        $coloresProducto[] = $colorProducto;
    }

    $bl = new ColorBL();
    $bl->insertOrUpdates($coloresProducto);
    ?>
    <a href='bienvenido.php'>&lt;&lt; Regresar</a>
</div>
</body>
</html>
