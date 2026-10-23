<?php

declare(strict_types=1);

use function Buffalo\View\e;

/** @var string $encabezado */
/** @var list<string> $mensajes */

ob_start();
?>
<h3><?= e($encabezado) ?></h3>
<?php foreach ($mensajes as $mensaje) { ?>
    <h2><?= e($mensaje) ?></h2><br>
<?php } ?>
<a href="bienvenido.php">&lt;&lt; Regresar</a>
<?php
$contenido = (string) ob_get_clean();
$titulo = 'Operación realizada';
require __DIR__ . '/../layout.php';
