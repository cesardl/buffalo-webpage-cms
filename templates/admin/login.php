<?php

declare(strict_types=1);

use Buffalo\View\Template;

use function Buffalo\View\e;

/** @var int $error */
/** @var bool $despedida */
/** @var string $token */
/** @var Template $this */

ob_start();
?>
<form class="form-horizontal" action="/administrator/bienvenido.php" method="POST">
    <input type="hidden" name="_token" value="<?= e($token) ?>"/>
    <?php if ($error === 1) { ?>
        <div class="alert alert-warning">Error al ingresar usuario o contrase&ntilde;a.</div>
    <?php } elseif ($error === 2) { ?>
        <div class="alert alert-danger">
            Usted no tiene permisos para acceder a esta p&aacute;gina.<br>Por favor registrese.
        </div>
    <?php } ?>
    <?php if ($despedida) { ?>
        <div class="alert alert-info">Gracias por utilizar el administrador.</div>
    <?php } ?>
    <div class="form-group">
        <label class="control-label col-sm-2" for="user">Usuario</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="user" name="user" placeholder="Ingrese el usuario"/>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-sm-2" for="passwd">Contrase&ntilde;a</label>
        <div class="col-sm-10">
            <input type="password" class="form-control" id="passwd" name="passwd"
                   placeholder="Ingrese el password"/>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <input type="submit" id="btn_ingresar" value="Aceptar" class="btn btn-primary"/>
        </div>
    </div>
</form>
<?php
$contenido = (string) ob_get_clean();
$titulo = 'Motos Buffalo';
require __DIR__ . '/../layout.php';
