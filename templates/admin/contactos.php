<?php

declare(strict_types=1);

use Buffalo\Domain\Contacto;

use function Buffalo\View\e;

/** @var list<Contacto> $solicitudes */
/** @var string $filtro 'nuevas', 'atendidas' or 'todas' */
/** @var string $titulo */
/** @var string $menuActivo */
/** @var string $usuario */
/** @var int $nuevas */

/*
 * The dates arrive as MySQL wrote them, in the database server's timezone.
 * They are reformatted for reading and never reinterpreted, because parsing
 * them into PHP's timezone would quietly shift every one of them.
 */
$fecha = static function (?string $valor): string {
    if ($valor === null || $valor === '') {
        return '';
    }

    [$dia, $hora] = array_pad(explode(' ', $valor), 2, '');
    [$a, $m, $d] = array_pad(explode('-', $dia), 3, '');

    return sprintf('%s/%s/%s %s', $d, $m, $a, substr($hora, 0, 5));
};

$pestana = static fn (string $clave): string => $filtro === $clave ? ' is-active' : '';

ob_start();
?>
<h1 class="titulo-pagina">Solicitudes de contacto</h1>
<p class="bajada">Lo que llega por el formulario del sitio.</p>

<section class="tarjeta">
    <nav class="filtros" aria-label="Filtro">
        <a class="filtro<?= $pestana('nuevas') ?>" href="contactos.php?estado=nuevas">Nuevas</a>
        <a class="filtro<?= $pestana('atendidas') ?>" href="contactos.php?estado=atendidas">Atendidas</a>
        <a class="filtro<?= $pestana('todas') ?>" href="contactos.php?estado=todas">Todas</a>
    </nav>

    <?php if ($solicitudes === []) { ?>
        <p class="vacio">
            <?php if ($filtro === 'atendidas') { ?>
                Nada atendido todavía. Las solicitudes que resuelva aparecerán aquí.
            <?php } elseif ($filtro === 'nuevas') { ?>
                No hay solicitudes esperando. Buen momento.
            <?php } else { ?>
                Todavía no hay solicitudes. Las que lleguen por el formulario aparecerán aquí.
            <?php } ?>
        </p>
    <?php } else { ?>
        <table class="tabla">
            <thead>
            <tr>
                <th>Recibida</th>
                <th>Nombre</th>
                <th>Ciudad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($solicitudes as $solicitud) { ?>
                <tr>
                    <td><?= e($fecha($solicitud->fechaRegistro)) ?></td>
                    <td><?= e($solicitud->nombre) ?></td>
                    <td><?= e($solicitud->ciudad) ?></td>
                    <td>
                        <span class="chip<?= $solicitud->esNueva() ? ' es-nueva' : '' ?>">
                            <?= $solicitud->esNueva() ? 'Nueva' : 'Atendida' ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a href="contacto.php?id=<?= e((string) $solicitud->id) ?>">Ver</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</section>
<?php
$contenido = (string) ob_get_clean();

require __DIR__ . '/_consola.php';
