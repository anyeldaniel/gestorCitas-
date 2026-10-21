document.addEventListener("DOMContentLoaded", () => {
    const canvasFlujo = document.getElementById('graficaFlujo');
    const canvasServicios = document.getElementById('graficaServicios');
    const form = document.getElementById('form-filtros-reporte');
    const metaUrl = document.querySelector('meta[name="reportes-url"]');
    const urlDatos = metaUrl ? metaUrl.content : '/admin/reportes/datos';

    let chartFlujo = null;
    let chartServicios = null;

    // Paleta de colores corporativa
    const colores = {
        principal: '#8b5e3c',
        secundario: '#4f8eff',
        acento1: '#36e8a0',
        acento2: '#a259ff',
        acento3: '#ff5a7d',
        texto: '#2d2a26',
        gris: '#7a6f68'
    };

    // Paleta dinámica para los servicios (se repite si hay más de 15)
    const paletaServicios = [
        '#4f8eff', '#36e8a0', '#a259ff', '#ff5a7d', '#f59e0b',
        '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#8b5cf6',
        '#ef4444', '#10b981', '#3b82f6', '#eab308', '#14b8a6'
    ];

    // Configuración global de Chart.js
    Chart.defaults.font.family = "'Instrument Sans', sans-serif";
    Chart.defaults.color = colores.gris;

    /**
     * Pide los datos al backend con el rango de fechas.
     */
    async function obtenerDatos(fechaInicio, fechaFin) {
        const params = new URLSearchParams({
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin
        });

        const res = await fetch(`${urlDatos}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!res.ok) {
            throw new Error('Error al obtener datos del servidor');
        }

        return await res.json();
    }

    /**
     * Gráfica de línea: flujo semanal de pacientes.
     */
    function renderFlujo(datos) {
        if (chartFlujo) {
            chartFlujo.destroy();
        }

        chartFlujo = new Chart(canvasFlujo.getContext('2d'), {
            type: 'line',
            data: {
                labels: datos.labels,
                datasets: [{
                    label: 'Pacientes Atendidos',
                    data: datos.data,
                    borderColor: colores.secundario,
                    backgroundColor: 'rgba(79, 142, 255, 0.08)',
                    borderWidth: 3,
                    pointBackgroundColor: colores.secundario,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#2d2a26',
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f0ece6', drawBorder: false },
                        ticks: { font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    }

    /**
     * Gráfica de dona: todos los servicios con su conteo de citas.
     */
    function renderServicios(datos) {
        if (chartServicios) {
            chartServicios.destroy();
        }

        const backgroundColors = datos.labels.map(
            (_, i) => paletaServicios[i % paletaServicios.length]
        );

        chartServicios = new Chart(canvasServicios.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: datos.labels,
                datasets: [{
                    data: datos.data,
                    backgroundColor: backgroundColors,
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#2d2a26',
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const valor = context.parsed;
                                const pct = total > 0 ? ((valor / total) * 100).toFixed(1) : 0;
                                return ` ${context.label}: ${valor} citas (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Carga ambas gráficas con el rango indicado.
     */
    async function cargarTodo(fechaInicio, fechaFin) {
        try {
            const datos = await obtenerDatos(fechaInicio, fechaFin);
            if (canvasFlujo) renderFlujo(datos.flujo);
            if (canvasServicios) renderServicios(datos.servicios);
        } catch (err) {
            console.error('Error cargando reportes:', err);
        }
    }

    // Carga inicial con las fechas del formulario
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    cargarTodo(fechaInicio, fechaFin);

    // Recargar al enviar el formulario (botón "Actualizar Gráficas")
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fi = document.getElementById('fecha_inicio').value;
            const ff = document.getElementById('fecha_fin').value;
            await cargarTodo(fi, ff);
        });
    }
});