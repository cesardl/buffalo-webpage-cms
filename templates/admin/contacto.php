<?php

declare(strict_types=1);

use Buffalo\Domain\Contacto;

use function Buffalo\View\e;

/** @var Contacto $solicitud */
/** @var string $token */
/** @var string $titulo */
/** @var string $menuActivo */
/** @var string $usuario */
/** @var int $nuevas */

$fecha = static function (?string $valor): string {
    if ($valor === null || $valor === '') {
        return '';
    }

    [$dia, $hora] = array_pad(explode(' ', $valor), 2, '');
    [$a, $m, $d] = array_pad(explode('-', $dia), 3, '');

    return sprintf('%s/%s/%s %s', $d, $m, $a, substr($hora, 0, 5));
};

$dato = static function (string $etiqueta, string $valor): string {
    if ($valor === '') {
        return '';
    }

    return '<tr><th>' . e($etiqueta) . '</th><td>' . e($valor) . '</td></tr>';
};

ob_start();
?>
<h1 class="titulo-pagina"><?= e($solicitud->nombre) ?></h1>
<p class="bajada">
    Recibida el <?= e($fecha($solicitud->fechaRegistro)) ?>
    · <a href="contactos.php">Volver a la bandeja</a>
</p>

<div class="rejilla">
    <section class="tarjeta">
        <h2 class="tarjeta-titulo">Consulta</h2>
        <p class="tarjeta-nota">Tal como la escribió.</p>
        <p class="mensaje"><?= e($solicitud->comentarios) ?></p>

        <form class="barra-acciones" method="POST" action="actionContacto.php">
            <input type="hidden" name="_token" value="<?= e($token) ?>"/>
            <input type="hidden" name="id_contacto" value="<?= e((string) $solicitud->id) ?>"/>
            <?php if ($solicitud->esNueva()) { ?>
                <input type="hidden" name="estado" value="<?= e(Contacto::ATENDIDA) ?>"/>
                <button type="submit" class="btn btn-primary">Marcar como atendida</button>
            <?php } else { ?>
                <input type="hidden" name="estado" value="<?= e(Contacto::NUEVA) ?>"/>
                <button type="submit" class="btn btn-link">Reabrir</button>
            <?php } ?>
        </form>
    </section>

    <section class="tarjeta">
        <h2 class="tarjeta-titulo">Quién escribe</h2>
        <p class="tarjeta-nota">
            <span class="chip<?= $solicitud->esNueva() ? ' es-nueva' : '' ?>">
                <?= $solicitud->esNueva() ? 'Nueva' : 'Atendida' ?>
            </span>
        </p>
        <table class="tabla">
            <tbody>
            <tr>
                <th>Correo</th>
                <td><a href="mailto:<?= e($solicitud->email) ?>"><?= e($solicitud->email) ?></a></td>
            </tr>
            <?= $dato('Teléfono', $solicitud->telefono) ?>
            <?= $dato('Dirección', $solicitud->direccion) ?>
            <?= $dato('Ciudad', $solicitud->ciudad) ?>
            <?php if (!$solicitud->esNueva()) { ?>
                <tr>
                    <th>Atendida</th>
                    <td>
                        <?= e($fecha($solicitud->fechaAtencion)) ?>
                        <?php if ($solicitud->atendidoPor !== null) { ?>
                            por <?= e($solicitud->atendidoPor) ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </section>
</div>
<?php
$contenido = (string) ob_get_clean();

require __DIR__ . '/_consola.php';
