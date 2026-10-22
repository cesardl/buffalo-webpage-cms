<?php

include_once 'util/Auth.php';
include_once 'bussinessLogic/ProductoBL.php';

require_admin_ajax();

$id_producto = $_POST['id_producto'];

$bl = new ProductoBL();
$bl->deleteProducto($id_producto);

echo '<b>Se ha eliminado el vehiculo<b>';
