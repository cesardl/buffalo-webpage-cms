<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'domain/Master.php';
include_once 'domain/Vehiculo.php';
include_once 'util/Connection.php';

/**
 * Description of VehiculoDAO
 *
 * @author Cesardl
 */
class VehiculoDAO {

    public function getVehiculos() {
        $conexion = new Connection();
        $query = "SELECT id_vehiculo, nombre FROM vehiculo ORDER BY id_vehiculo";
        $result = mysql_query($query, $conexion->getConnection());
        $total_registros = mysql_num_rows($result);

        if ($total_registros > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $vehiculo = new Vehiculo();
                $vehiculo->setId_vehiculo($row['id_vehiculo']);
                $vehiculo->setNombre($row['nombre']);

                $vehiculos[] = $vehiculo;
            }
        }
        return $vehiculos;
    }

    public function getMasterById($id_master) {
        $conexion = new Connection();
        $query = "SELECT id_master, clase, id_vehiculo FROM master WHERE id_master = $id_master";
        $result = mysql_query($query, $conexion->getConnection());

        $row = mysql_fetch_assoc($result);

        $master = new Master();
        $master->setId_master($row['id_master']);
        $master->setClase($row['clase']);
        $master->setId_vehiculo($row['id_vehiculo']);

        return $master;
    }

    public function getMastersByVehiculo($id_vehiculo) {
        $conexion = new Connection();
        $query = "SELECT id_master, clase FROM master WHERE id_vehiculo = $id_vehiculo ORDER BY id_master";
        $result = mysql_query($query, $conexion->getConnection());
        $total_registros = mysql_num_rows($result);

        if ($total_registros > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $master = new Master();
                $master->setId_master($row['id_master']);
                $master->setClase($row['clase']);

                $masters[] = $master;
            }
        }
        return $masters;
    }

}

?>
