<?php

declare(strict_types=1);

use Buffalo\Panel\Resumen;
use Buffalo\Panel\TipoConClases;

use function Buffalo\View\e;

/** @var Resumen $resumen */
/** @var string $titulo */
/** @var string $menuActivo */
/** @var string $usuario */
/** @var int $nuevas */

/*
 * Colour carries the hierarchy: the hue is the vehicle type, the tint is the
 * class within it. A reader can tell which family a segment belongs to
 * without going to the legend first.
 */
$familias = [
    ['#C5221F', '#E8554F', '#F08A86', '#F6B8B5'],
    ['#B06000', '#D98A2B', '#E8B06B', '#F2D0A3'],
    ['#3C4043', '#80868B', '#BDC1C6', '#DADCE0'],
];

$etiquetas = [];
$series = [];
$totales = [];

foreach ($resumen->tipos as $i => $tipo) {
    $etiquetas[] = $tipo->tipo;
    $totales[] = $tipo->total();
    $familia = $familias[$i % count($familias)];

    foreach ($tipo->clases as $j => $clase) {
        // One dataset per class, carrying its count only in its own type's
        // row: that is what makes a stacked bar a hierarchy rather than a
        // pile of unrelated numbers.
        $valores = array_fill(0, count($resumen->tipos), 0);
        $valores[$i] = $clase->modelos;

        $series[] = [
            'label' => $clase->clase,
            'data' => $valores,
            'backgroundColor' => $familia[$j % count($familia)],
        ];
    }
}

$datos = [
    'vehiculos' => [
        'etiquetas' => $etiquetas,
        'series' => $series,
        'totales' => $totales,
    ],
    'contactos' => [
        'nuevas' => $resumen->nuevas,
        'atendidas' => $resumen->atendidas,
    ],
];

$plural = static fn (int $n, string $uno, string $varios): string
    => $n . ' ' . ($n === 1 ? $uno : $varios);

ob_start();
?>
<h1 class="titulo-pagina">Panel</h1>
<p class="bajada">El estado del catálogo y de las solicitudes que llegan por el formulario.</p>

<div class="rejilla">
    <section class="tarjeta">
        <h2 class="tarjeta-titulo">Vehículos por tipo y clase</h2>
        <p class="tarjeta-nota">
            <?= e($plural($resumen->totalModelos(), 'modelo', 'modelos')) ?>
            en <?= e($plural($resumen->totalClases(), 'clase', 'clases')) ?>
        </p>
        <div class="lienzo-grafico">
            <canvas id="grafico-vehiculos"></canvas>
        </div>
    </section>

    <div class="columna">
        <div class="kpis">
            <section class="tarjeta kpi es-nuevas">
                <div class="kpi-cifra"><?= e((string) $resumen->nuevas) ?></div>
                <div class="kpi-etiqueta">Nuevas</div>
                <div class="kpi-detalle">Esperan respuesta</div>
            </section>
            <section class="tarjeta kpi">
                <div class="kpi-cifra"><?= e((string) $resumen->atendidas) ?></div>
                <div class="kpi-etiqueta">Atendidas</div>
                <div class="kpi-detalle">Ya respondidas</div>
            </section>
        </div>

        <section class="tarjeta">
            <h2 class="tarjeta-titulo">Solicitudes de contacto</h2>
            <p class="tarjeta-nota">
                <?php if ($resumen->totalSolicitudes() === 0) { ?>
                    Todavía no llega ninguna.
                <?php } else { ?>
                    <a href="contactos.php">Ver la bandeja</a>
                <?php } ?>
            </p>
            <div class="lienzo-grafico es-donut">
                <canvas id="grafico-contactos"></canvas>
                <div class="donut-centro">
                    <span class="donut-total"><?= e((string) $resumen->totalSolicitudes()) ?></span>
                    <span class="donut-pie">en total</span>
                </div>
            </div>
        </section>
    </div>
</div>

<script type="application/json" id="datos-panel"><?= json_encode(
    $datos,
    JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
) ?></script>
<?php
$contenido = (string) ob_get_clean();

// Chart.js only loads where there is a chart.
$pie = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.6.1/dist/chart.umd.min.js"
        integrity="sha384-Emf5ZVIUiarAdhkgtqK+5/Z5qIvM6QoVrS+qAVtfyZVuD5lIHrac0me485+Antp0"
        crossorigin="anonymous"></script>
<script src="scritps/panel.js"></script>
HTML;

require __DIR__ . '/_consola.php';
