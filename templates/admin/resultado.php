<?php

declare(strict_types=1);

use function Buffalo\View\e;

/** @var string $encabezado */
/** @var list<string> $mensajes */
/** @var string $volver where the link goes back to */
/** @var string $titulo */
/** @var string $menuActivo */
/** @var string $usuario */
/** @var int $nuevas */

ob_start();
?>
<h1 class="titulo-pagina"><?= e($encabezado) ?></h1>

<section class="tarjeta">
    <?php foreach ($mensajes as $mensaje) { ?>
        <p><?= e($mensaje) ?></p>
    <?php } ?>

    <div class="barra-acciones">
        <a class="btn btn-primary" href="<?= e($volver) ?>">Continuar</a>
    </div>
</section>
<?php
$contenido = (string) ob_get_clean();

require __DIR__ . '/_consola.php';
