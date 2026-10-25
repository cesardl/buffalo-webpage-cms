/*
 * The panel's two charts.
 *
 * The figures come from a <script type="application/json"> block the page
 * renders, parsed rather than interpolated into executable JavaScript.
 */
(function () {

    const bloque = document.getElementById('datos-panel');

    if (bloque === null || typeof Chart === 'undefined') {
        return;
    }

    const datos = JSON.parse(bloque.textContent);

    Chart.defaults.font.family = 'Roboto, Arial, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#5F6368';

    // The catalogue is small, so a bar for one model is a stub. The row's
    // total is drawn past the end of the stack, where it reads whatever the
    // scale is doing. The last dataset's bar always ends at the stack total,
    // even where its own value is zero, which is what this hangs off.
    const totales = {
        id: 'totales',
        afterDatasetsDraw(chart) {
            const ultima = chart.getDatasetMeta(chart.data.datasets.length - 1);
            const ctx = chart.ctx;

            ctx.save();
            ctx.font = '500 12px Roboto, Arial, sans-serif';
            ctx.fillStyle = '#202124';
            ctx.textBaseline = 'middle';

            ultima.data.forEach(function (barra, fila) {
                ctx.fillText(String(datos.vehiculos.totales[fila]), barra.x + 8, barra.y);
            });

            ctx.restore();
        }
    };

    new Chart(document.getElementById('grafico-vehiculos'), {
        type: 'bar',
        data: {
            labels: datos.vehiculos.etiquetas,
            datasets: datos.vehiculos.series
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            layout: {padding: {right: 24}},
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {precision: 0},
                    grid: {color: '#F1F3F4'},
                    border: {display: false}
                },
                y: {
                    stacked: true,
                    grid: {display: false},
                    border: {display: false}
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {usePointStyle: true, pointStyle: 'rect', boxWidth: 10, boxHeight: 10, padding: 12}
                },
                tooltip: {
                    callbacks: {
                        label: function (contexto) {
                            const modelos = contexto.parsed.x;

                            return contexto.dataset.label + ': ' + modelos
                                + (modelos === 1 ? ' modelo' : ' modelos');
                        }
                    },
                    filter: function (contexto) {
                        return contexto.parsed.x > 0;
                    }
                }
            }
        },
        plugins: [totales]
    });

    new Chart(document.getElementById('grafico-contactos'), {
        type: 'doughnut',
        data: {
            labels: ['Nuevas', 'Atendidas'],
            datasets: [{
                // Red is the half that still wants something; the handled
                // half recedes into grey.
                data: [datos.contactos.nuevas, datos.contactos.atendidas],
                backgroundColor: ['#C5221F', '#BDC1C6'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '72%',
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {usePointStyle: true, pointStyle: 'circle', boxWidth: 8, boxHeight: 8, padding: 12}
                }
            }
        }
    });
})();
