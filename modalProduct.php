<?php

include_once 'administrator/util/Connection.php';

function writeModal($dev_modal, $id_producto) {
    $connection = new Connection();
    $query = "SELECT modelo, foto_principal, descripcion 
        FROM producto WHERE id_producto = $id_producto";
    $result = mysql_query($query, $connection->getConnection());
    $row = mysql_fetch_assoc($result);
    echo "<div id=\"$dev_modal\">
        <div id=\"basic-modal-content\">
                <div class=\"product_detalle_1_tituclase txt_prodtitu_40p\">{$row['modelo']}</div>
                <div class=\"product_detalle_1_master\">
                <div class=\"product_detalle_1_masterimg\"><img src=\"{$row['foto_principal']}\" width=\"369\" height=\"252\" /></div>
                <div class=\"product_detalle_1_masterdetalle txt_prod_12p\">" . htmlspecialchars($row['descripcion']) . "</div>
            </div>";
    echo "<div class=\"product_detalle_1_caract\">";

    $query_detalle = "SELECT titulo, descripcion, foto_detalle FROM detalleproducto WHERE id_producto = $id_producto ORDER BY id_detalle_producto";
    $result_detalle = mysql_query($query_detalle, $connection->getConnection());

    while ($row_detalle = mysql_fetch_array($result_detalle)) {
        echo "<!-----empieza detalle------>
                <div class=\"product_detalle_1_caract1\">
                        <div class=\"product_detalle_1_foto1\"><img src=\"{$row_detalle['foto_detalle']}\" width=\"88\" height=\"89\" /></div>
                    <div class=\"product_detalle_1_detalle1\">
                        <div class=\"product_detalle_1_det_subti1 txt_prod_12p\">{$row_detalle['titulo']}</div>
                        <div class=\"product_detalle_1_det_descrip1 txt_prod_12p\">" . htmlspecialchars($row_detalle['descripcion']) . "</div>
                    </div>
                </div>
                <!-----termina detalle------>";
    }
    echo "</div>
        </div>

        <!-- preload the images -->
        <div style='display:none'>
            <img src='images/x.png' alt='x' />
        </div> </div>";
}

?>