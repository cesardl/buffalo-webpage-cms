<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once 'util/Connection.php';
include_once 'domain/DetalleProducto.php';

/**
 * Description of MasterProductoDAO
 *
 * @author Cesardl
 */
class DetalleProductoDAO {

    public function getDetallesProductos($id_producto) {
        $conexion = new Connection();
        $query = "SELECT id_detalle_producto, titulo, foto_detalle, descripcion, id_producto
                FROM detalleproducto WHERE id_producto = $id_producto
                ORDER BY id_detalle_producto";
        $result = mysql_query($query, $conexion->getConnection());
        $total_registros = mysql_num_rows($result);

        if ($total_registros > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $detalleProducto = new DetalleProducto();
                $detalleProducto->setId_detalle_producto($row['id_detalle_producto']);
                $detalleProducto->setTitulo($row['titulo']);
                $detalleProducto->setFoto_detalle($row['foto_detalle']);
                $detalleProducto->setDescripcion($row['descripcion']);
                $detalleProducto->setId_producto($row['id_producto']);

                $detallesProducto[] = $detalleProducto;
            }
        }
        return $detallesProducto;
    }

    public function insert($detalleProducto) {
        $conexion = new Connection();
        $query = "INSERT INTO detalleproducto(titulo,foto_detalle,descripcion,id_producto) 
            VALUES('{$detalleProducto->getTitulo()}',
            '{$detalleProducto->getFoto_detalle()}',
            '" . ($detalleProducto->getDescripcion()) . "',
            '{$detalleProducto->getId_producto()}' );";

        $result = mysql_query($query, $conexion->getConnection());

        if (isset($result)) {
            return $result;
        } else {
            return -1;
        }
    }

    public function update($detalleProducto) {
        $conexion = new Connection();
        $query = "UPDATE detalleproducto SET
            titulo='{$detalleProducto->getTitulo()}', 
            foto_detalle='{$detalleProducto->getFoto_detalle()}', 
            descripcion='" . ($detalleProducto->getDescripcion()) . "' 
            WHERE id_detalle_producto= {$detalleProducto->getId_detalle_producto()}";

        $result = mysql_query($query, $conexion->getConnection());

        if (isset($result)) {
            return $result;
        } else {
            return -1;
        }
    }

    public function deleteDetalleProducto($id_detalle_producto) {
        $conexion = new Connection();
        $query = "DELETE FROM detalleproducto WHERE id_detalle_producto = $id_detalle_producto";

        $result = mysql_query($query, $conexion->getConnection());

        if (isset($result)) {
            return $result;
        } else {
            return -1;
        }
    }

}

?>
