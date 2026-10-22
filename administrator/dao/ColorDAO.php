<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'domain/Color.php';
include_once 'domain/ColorProducto.php';
include_once 'util/Connection.php';

/**
 * Description of ColorDAO
 *
 * @author Cesardl
 */
class ColorDAO {

    public function getColores() {
        $conexion = new Connection();
        $query = "SELECT id_color, nombre, codigo FROM color ORDER BY id_color";
        $result = mysql_query($query, $conexion->getConnection());
        $total_registros = mysql_num_rows($result);

        if ($total_registros > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $color = new Color();
                $color->setId_color($row['id_color']);
                $color->setNombre($row['nombre']);
                $color->setCodigo($row['codigo']);

                $colores[] = $color;
            }
        }
        return $colores;
    }

    public function getColoresProductoByIdProducto($id_producto) {
        $conexion = new Connection();
        $query = "SELECT id, id_color_1, id_color_2, 
                producto.id_producto as p_id, producto.modelo as p_modelo
                FROM colorproducto 
                INNER JOIN producto on colorproducto.id_producto = producto.id_producto
                WHERE producto.id_producto = $id_producto
                ORDER BY colorproducto.id";

        $result = mysql_query($query, $conexion->getConnection());
        $total_registros = mysql_num_rows($result);

        if ($total_registros > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $colorProducto = new ColorProducto();
                $colorProducto->setId($row['id']);

                $color_1 = new Color();
                $color_1->setId_color($row['id_color_1']);
                $colorProducto->setColor_1($color_1);

                $color_2 = new Color();
                $color_2->setId_color($row['id_color_2']);
                $colorProducto->setColor_2($color_2);

                $producto = new Producto();
                $producto->setId_producto($row['p_id']);
                $producto->setModelo($row['p_modelo']);
                $colorProducto->setProducto($producto);

                $coloresProducto[] = $colorProducto;
            }
        }
        return $coloresProducto;
    }

    public function insert($colorproducto) {
        $conexion = new Connection();
        $query = "INSERT INTO colorproducto(id_producto,id_color_1,id_color_2) 
            VALUES({$colorproducto->getProducto()->getId_producto()},
            {$colorproducto->getColor_1()->getId_color()},
            {$colorproducto->getColor_2()->getId_color()} );";

        $result = mysql_query($query, $conexion->getConnection());

        if (isset($result)) {
            return $result;
        } else {
            return -1;
        }
    }

    public function update($colorproducto) {
        $conexion = new Connection();
        $query = "UPDATE colorproducto SET
            id_color_1={$colorproducto->getColor_1()->getId_color()}, 
            id_color_2={$colorproducto->getColor_2()->getId_color()} 
            WHERE id= {$colorproducto->getId()}";

        $result = mysql_query($query, $conexion->getConnection());

        if (isset($result)) {
            return $result;
        } else {
            return -1;
        }
    }

}

?>
