<?php

declare(strict_types=1);

use Buffalo\Catalogo\Entrada;

use function Buffalo\View\e;

/** @var Entrada $entrada */
/** @var string $dev_modal */

/*
 * Built with echo rather than HTML mode, because the legacy modalProduct.php
 * was, and the whitespace inside those string literals is part of the output
 * this has to reproduce.
 *
 * Everything is escaped here. The original escaped only the two descriptions
 * and interpolated the model, the title and the image paths raw. Against the
 * current data that makes no difference -- no row holds <, >, &, " or ' -- so
 * the bytes still match, but the equivalence is a property of the data, not
 * of the two implementations.
 */

$producto = $entrada->producto;

echo "<div id=\"" . e($dev_modal) . "\">
        <div id=\"basic-modal-content\">
                <div class=\"product_detalle_1_tituclase txt_prodtitu_40p\">" . e($producto->modelo) . "</div>
                <div class=\"product_detalle_1_master\">
                <div class=\"product_detalle_1_masterimg\"><img src=\"" . e($producto->fotoPrincipal) . "\" width=\"369\" height=\"252\" /></div>
                <div class=\"product_detalle_1_masterdetalle txt_prod_12p\">" . e($producto->descripcion) . "</div>
            </div>";
echo "<div class=\"product_detalle_1_caract\">";

foreach ($entrada->detalles as $detalle) {
    echo "<!-----empieza detalle------>
                <div class=\"product_detalle_1_caract1\">
                        <div class=\"product_detalle_1_foto1\"><img src=\"" . e($detalle->fotoDetalle) . "\" width=\"88\" height=\"89\" /></div>
                    <div class=\"product_detalle_1_detalle1\">
                        <div class=\"product_detalle_1_det_subti1 txt_prod_12p\">" . e($detalle->titulo) . "</div>
                        <div class=\"product_detalle_1_det_descrip1 txt_prod_12p\">" . e($detalle->descripcion) . "</div>
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
