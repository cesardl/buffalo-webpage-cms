<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'domain/Master.php';
include_once 'domain/Producto.php';
include_once 'util/Connection.php';

/**
 * Description of ProductoDAO
 *
 * @author Cesardl
 */
class ProductoDAO {

    public function getProductos() {
        $conexion = new Connection();
        $query = "SELECT id_producto, modelo, foto_principal, descripcion, foto_zoom_1, foto_zoom_2, 
            foto_zoom_3, foto_zoom_4, ficha_tecnica, master.id_master AS id_master, 
            master.clase AS clase, master.id_vehiculo AS id_vehiculo FROM producto
            INNER JOIN master ON producto.id_master = master.id_master
            ORDER BY producto.id_master, producto.id_producto";
        $result = mysql_query($query, $conexion->getConnection());
        $total_registros = mysql_num_rows($result);

        if ($total_registros > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $producto = new Producto();
                $producto->setId_producto($row['id_producto']);
                $producto->setModelo($row['modelo']);
                $producto->setFoto_principal($row['foto_principal']);
                $producto->setDescripcion($row['descripcion']);
                $producto->setFoto_zoom_1($row['foto_zoom_1']);
                $producto->setFoto_zoom_2($row['foto_zoom_2']);
                $producto->setFoto_zoom_3($row['foto_zoom_3']);
                $producto->setFoto_zoom_4($row['foto_zoom_4']);
                $producto->setFicha_tecnica($row['ficha_tecnica']);

                $master = new Master();
                $master->setId_master($row['id_master']);
                $master->setClase($row['clase']);
                $master->setId_vehiculo($row['id_vehiculo']);
                $producto->setMaster($master);

                $productos[] = $producto;
            }
        }
        return $productos;
    }

    public function getProductoById($id_producto) {
        $conexion = new Connection();
        $query = "select id_producto, modelo, foto_principal, foto_zoom_1, foto_zoom_2, 
            descripcion, foto_zoom_3, foto_zoom_4, ficha_tecnica, id_master 
            from producto where id_producto=$id_producto";
        $result = mysql_query($query, $conexion->getConnection());

        $row = mysql_fetch_assoc($result);

        $producto = new Producto();
        $producto->setId_producto($row['id_producto']);
        $producto->setModelo($row['modelo']);
        $producto->setFoto_principal($row['foto_principal']);
        $producto->setFoto_zoom_1($row['foto_zoom_1']);
        $producto->setFoto_zoom_2($row['foto_zoom_2']);
        $producto->setFoto_zoom_3($row['foto_zoom_3']);
        $producto->setFoto_zoom_4($row['foto_zoom_4']);
        $producto->setDescripcion($row['descripcion']);
        $producto->setFicha_tecnica($row['ficha_tecnica']);

        $master = new Master();
        $master->setId_master($row['id_master']);
        $producto->setMaster($master);

        return $producto;
    }

    public function insertProducto($producto) {
        $conexion = new Connection();
        $master = $producto->getMaster();
        $query = "INSERT INTO producto (modelo, foto_principal, foto_zoom_1,
            foto_zoom_2, foto_zoom_3, foto_zoom_4, descripcion, ficha_tecnica, id_master) 
            VALUES ('{$producto->getModelo()}','{$producto->getFoto_principal()}',
            '{$producto->getFoto_zoom_1()}','{$producto->getFoto_zoom_2()}',
            '{$producto->getFoto_zoom_3()}','{$producto->getFoto_zoom_4()}',
            '" . ($producto->getDescripcion()) . "','{$producto->getFicha_tecnica()}',
            {$master->getId_master()})";

        $result = mysql_query($query, $conexion->getConnection());

        if (isset($result)) {
            return $result;
        } else {
            return -1;
        }
    }

    public function updateProducto($producto) {
        $conexion = new Connection();
        $master = $producto->getMaster();

        $query = "UPDATE producto SET modelo = '{$producto->getModelo()}', 
            descripcion = '" . ($producto->getDescripcion()) . "', 
            foto_principal = '{$producto->getFoto_principal()}',
            foto_zoom_1 = '{$producto->getFoto_zoom_1()}', 
            foto_zoom_2 = '{$producto->getFoto_zoom_2()}', 
            foto_zoom_3 = '{$producto->getFoto_zoom_3()}', 
            foto_zoom_4 = '{$producto->getFoto_zoom_4()}', 
            ficha_tecnica = '{$producto->getFicha_tecnica()}', 
            id_master = '{$master->getId_master()}' 
            WHERE id_producto = {$producto->getId_producto()}";

        $result = mysql_query($query, $conexion->getConnection());

        if (isset($result)) {
            return $result;
        } else {
            return -1;
        }
    }

    public function deleteProducto($id_producto) {
        $conexion = new Connection();
        $query = "DELETE FROM producto WHERE id_producto = $id_producto";

        $result = mysql_query($query, $conexion->getConnection());

        if (isset($result)) {
            return $result;
        } else {
            return -1;
        }
    }

}

?>
