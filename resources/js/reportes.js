document.addEventListener("DOMContentLoaded", () => {
    const canvasFlujo = document.getElementById('graficaFlujo');
    const canvasServicios = document.getElementById('graficaServicios');

    // Paleta de colores corporativa
    const colores = {
        principal: '#8b5e3c',      // Marrón Spa
        secundario: '#4f8eff',     // Azul
        acento1: '#36e8a0',        // Verde menta
        acento2: '#a259ff',        // Púrpura
        acento3: '#ff5a7d',        // Rosa
        texto: '#2d2a26',
        gris: '#7a6f68'
    };

    // Configuración global de Chart.js
    Chart.defaults.font.family = "'Instrument Sans', sans-serif";
    Chart.defaults.color = colores.gris;

    if (canvasFlujo) {
        new Chart(canvasFlujo.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
                datasets: [{
                    label: 'Pacientes Atendidos',
                    data: [140, 210, 185, 260],
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

    if (canvasServicios) {
        new Chart(canvasServicios.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Masajes', 'Limpieza Facial', 'Hidratación', 'Sauna'],
                datasets: [{
                    data: [40, 25, 20, 15],
                    backgroundColor: [
                        colores.secundario,
                        colores.acento1,
                        colores.acento2,
                        colores.acento3
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', // Hace el agujero central más grande (estilo premium)
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
                            label: function(context) {
                                return ` ${context.label}: ${context.parsed}%`;
                            }
                        }
                    }
                }
            }
        });
    }
});