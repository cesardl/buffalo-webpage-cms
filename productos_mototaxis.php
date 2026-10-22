<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>BUFFALO</title>
<link href="css/buffaloestilo.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="css/scrollbar.css" />
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<!--<script type="text/javascript" src="js/function.js"></script>-->
<script type="text/javascript" src="js/jquery.cycle.all.min.js"></script>
<script type="text/javascript" src="js/jquery.scroll.js"></script>
</head>

<body>
<div id="index_cabecera">
  <div id="bg_cabecera">
  	<div id="index_cabecera_logo"><img src="images/logo_top.png" width="208" height="169" /></div>
    <div class="botonera">
      <div class="boton_home btn_cabecera_home"><a href="index.html"></a></div>
      <div class="boton_quienes btn_cabecera_quienes"><a href="quienes_somos.html"></a></div>
      <div class="boton_productos"><a href="productos.html"><img src="images/btn_productos_b.jpg" width="105" height="50" border="0" /></a></div>
      <div class="boton_ubicanos btn_cabecera_ubicanos"><a href="ubicanos.html"></a></div>
      <div class="boton_contactanos btn_cabecera_contactanos"><a href="contactanos.html"></a></div>
    </div>
  </div>
</div>
<div id="quienes_contenido">
	<div id="in_quienes">
	  <div class="ubicanos_bg">
      <div class="css-scrollbar simple">
                
      <div class="productos_in_titu txt_quietitu_20p">>&nbsp;&nbsp;Mototaxis</div>
      <!--aki empieza-->
	  <?php 
            include_once 'administrator/util/Connection.php';
            
            $connection = new Connection();
            $query = "SELECT master.id_master, master.clase FROM master 
                INNER JOIN producto ON producto.id_master = master.id_master
                WHERE master.id_vehiculo=2
                GROUP By master.id_master
                ORDER BY master.id_master";
            $result = mysql_query($query, $connection->getConnection());
            
            $row_counter = 1;
             while ($row = mysql_fetch_assoc($result)) {
                 $query_producto = "SELECT * from producto WHERE producto.id_master = {$row['id_master']} ORDER BY producto.id_producto";
                 $result_producto = mysql_query($query_producto, $connection->getConnection());
                 
                 $i_prod = 0;
                 while ($row_p = mysql_fetch_assoc($result_producto)) {
                     $id_producto = $row_p['id_producto'];
         ?>
      <div class="productos_in_1">
        <div class="productos_in_1_datos">
        	<div class="productos_in_1_icono">
                    <?php if($i_prod == 0) {?>
                        <img src="images/ico_product.png" width="19" height="19" />
                     <?php }?>
                </div>
            <?php if($i_prod == 0) {?>
                <div class="product_in_1_subti_clase"><span class="txt_contacsubti_12p"><b><?php echo $row['clase'] ?></b></span></div>
            <?php }?>
            <div class="product_in_serie"><span class="txt_contacsubti_12p">>&nbsp;&nbsp; <?php echo $row_p['modelo'] ?></span></div>
            <div class="product_in_colores">
                <?php
                $query_color = "SELECT 
                        IF(colorproducto.id_color_1 <>0, (SELECT c.codigo FROM color c WHERE c.id_color = colorproducto.id_color_1), 'X') AS color_1,
                        IF(colorproducto.id_color_2 <>0, (SELECT c.codigo FROM color c WHERE c.id_color = colorproducto.id_color_2), 'X') AS color_2
                        FROM colorproducto 
                        WHERE colorproducto.id_producto=$id_producto
                        ORDER BY colorproducto.id";
                        
                $result_color = mysql_query($query_color, $connection->getConnection());
                
                while ($row_c = mysql_fetch_assoc($result_color)) {
                        if($row_c['color_2'] == 'X') {
                ?>
                        <div class="product_in_color1" style="background-color:#<?php echo $row_c['color_1'] ?>;"></div>
                <?php } else { ?>
                        <div class="product_in_color2">
                                <div class="product_in_colortop" style="background-color:#<?php echo $row_c['color_1'] ?>;"></div>
                                <div class="product_in_colorbottom" style="background-color:#<?php echo $row_c['color_2'] ?>;"></div>
                        </div>
                <?php } 
                } ?>
            </div>
            <div class="product_in_btndescarga"><a href="<?php echo $row_p['ficha_tecnica'] ?>" target="_blank"><img src="images/btn_descarga_ficha.png" width="243" height="17" border="0" /></a></div>
        </div>
        <div class="product_in_1_fotos">
            <?php
            for ($j = 1; $j <= 4; $j++) {
                $ruta_foto_zoom = $row_p["foto_zoom_$j"];
                if(!empty ($ruta_foto_zoom)) {
                    $dev_modal = "dev_modal_$row_counter$j";
                ?>
                <div class="product_in_1_foto1">
                     <div id="product_btn_zoom">
                         <div id="basic-modal"><a href='#' class="basic" onclick="showDevModal('<?php echo $dev_modal ?>')"><img src="images/btn_zoom.gif" width="60" height="18" border="0"  /></a></div>
                        <!-- modal content -->
                        <?php
                            include_once 'modalProduct.php';
                            writeModal($dev_modal, $id_producto);
                        ?>
                    </div>
                    <img src="<?php echo $ruta_foto_zoom ?>" width="88" height="89" />
                </div>
                <?php }
            }
            ?>
        </div>
      </div>
      <?php   $i_prod++;$row_counter++;/*FIN WHILE PRODUCTO*/}
      /*FIN WHILE MASTER*/}
      ?>
      <!--aki se acaba-->
      
      </div>
      
      </div>
	</div>
</div>
<div id="all_pie_home">
	<div id="pie_home">
	  <div class="pie_megusta"></div>
	  <div class="pie_derechos"><span class="txt_pie_11p">BUFFALO del Perú 2011 - Todos los derechos reservados.</span></div>
	  <div class="pie_compartir"><span class="txt_piecom_10p">Compartir en:</span></div>
	  <div class="pie_face"><img src="images/pie_facebook.jpg" width="17" height="27" border="0" /></div>
      <div class="pie_face"><img src="images/pie_twitter.jpg" width="17" height="27" border="0" /></div>
	</div>
</div>
<!-- Load jQuery, SimpleModal and Basic JS files -->
<script type='text/javascript' src='js/jquery-1.4.2.min.js'></script>
<script type='text/javascript' src='js/jquery.simplemodal.js'></script>
<script type='text/javascript' src='js/basic.js'></script>
</body>
</html>
