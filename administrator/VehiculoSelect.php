<?php

include_once 'util/Auth.php';
include_once 'bussinessLogic/VehiculoBL.php';

require_admin_ajax();

$id_producto = $_POST['veh_id'];

echo '<option value="0">[Seleccione]</option>';
if ($id_producto != 0) {
    $vehiculoBL = new VehiculoBL();
    $masters = $vehiculoBL->getMastersByVehiculo($id_producto);
    for ($i = 0; $i < count($masters); $i++) {
        echo "<option value=\"{$masters[$i]->getId_master()}\">{$masters[$i]->getClase()}</option>";
    }
}
