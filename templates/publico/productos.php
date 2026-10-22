<?php

declare(strict_types=1);

use Buffalo\Catalogo\Clase;

use function Buffalo\View\e;

/** @var string $titulo */
/** @var list<Clase> $clases */

// Numbers the modal divs. The legacy page used a counter that advanced once
// per product across the whole page, and the ids are referenced by the inline
// onclick handlers, so the numbering has to match.
$contador = 1;
?>
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
                
      <div class="productos_in_titu txt_quietitu_20p">>&nbsp;&nbsp;<?= e($titulo) ?></div>
      <!--aki empieza-->
	  <?php
            foreach ($clases as $clase) {
                 $i_prod = 0;
                 foreach ($clase->entradas as $entrada) {
                     $producto = $entrada->producto;
         ?>
      <div class="productos_in_1">
        <div class="productos_in_1_datos">
        	<div class="productos_in_1_icono">
                    <?php if($i_prod == 0) {?>
                        <img src="images/ico_product.png" width="19" height="19" />
                     <?php }?>
                </div>
            <?php if($i_prod == 0) {?>
                <div class="product_in_1_subti_clase"><span class="txt_contacsubti_12p"><b><?= e($clase->master->clase) ?></b></span></div>
            <?php }?>
            <div class="product_in_serie"><span class="txt_contacsubti_12p">>&nbsp;&nbsp; <?= e($producto->modelo) ?></span></div>
            <div class="product_in_colores">
                <?php
                foreach ($entrada->combinaciones as $combinacion) {
                        if(!$combinacion->esBicolor()) {
                ?>
                        <div class="product_in_color1" style="background-color:#<?= e($combinacion->principal?->codigo) ?>;"></div>
                <?php } else { ?>
                        <div class="product_in_color2">
                                <div class="product_in_colortop" style="background-color:#<?= e($combinacion->principal?->codigo) ?>;"></div>
                                <div class="product_in_colorbottom" style="background-color:#<?= e($combinacion->secundario?->codigo) ?>;"></div>
                        </div>
                <?php }
                } ?>
            </div>
            <div class="product_in_btndescarga"><a href="<?= e($producto->fichaTecnica) ?>" target="_blank"><img src="images/btn_descarga_ficha.png" width="243" height="17" border="0" /></a></div>
        </div>
        <div class="product_in_1_fotos">
            <?php
            for ($j = 1; $j <= 4; $j++) {
                $ruta_foto_zoom = $producto->fotosZoom[$j - 1];
                if(!empty ($ruta_foto_zoom)) {
                    $dev_modal = "dev_modal_$contador$j";
                ?>
                <div class="product_in_1_foto1">
                     <div id="product_btn_zoom">
                         <div id="basic-modal"><a href='#' class="basic" onclick="showDevModal('<?= e($dev_modal) ?>')"><img src="images/btn_zoom.gif" width="60" height="18" border="0"  /></a></div>
                        <!-- modal content -->
                        <?php require __DIR__ . '/_modal.php'; ?>
                    </div>
                    <img src="<?= e($ruta_foto_zoom) ?>" width="88" height="89" />
                </div>
                <?php }
            }
            ?>
        </div>
      </div>
      <?php   $i_prod++;$contador++;/*FIN WHILE PRODUCTO*/}
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
