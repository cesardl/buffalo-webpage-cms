<?php

include_once 'bussinessLogic/ProductoBL.php';

$id_producto = $_POST['id_producto'];

$bl = new ProductoBL();
$bl->deleteProducto($id_producto);

echo '<b>Se ha eliminado el vehiculo<b>';
