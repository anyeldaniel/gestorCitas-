document.addEventListener("DOMContentLoaded", () => {
    const canvasFlujo = document.getElementById('graficaFlujo');
    const canvasServicios = document.getElementById('graficaServicios');

    if (canvasFlujo) {
        new Chart(canvasFlujo.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
                datasets: [{
                    label: 'Pacientes Atendidos',
                    data: [140, 210, 185, 260],
                    borderColor: '#4f8eff',
                    backgroundColor: 'rgba(79, 142, 255, 0.05)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    }

    if (canvasServicios) {
        new Chart(canvasServicios.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Masajes', 'Limpieza Facial', 'Hidratación', 'Sauna'],
                datasets: [{
                    data: [40, 25, 20, 15],
                    backgroundColor: ['#4f8eff', '#36e8a0', '#a259ff', '#ff5a7d'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true }
        });
    }
});