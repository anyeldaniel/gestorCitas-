document.addEventListener("DOMContentLoaded", () => {
    const formServicios = document.getElementById("form-servicios-spa");

    if (formServicios) {
        formServicios.addEventListener("submit", (e) => {
            e.preventDefault();
            console.log("Guardando el nuevo servicio del Spa de forma organizada...");
        });
    }

    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("btn-eliminar-servicio")) {
            const servicioId = e.target.getAttribute("data-id");
            if (confirm("¿Seguro que desea remover este servicio del catálogo público?")) {
                e.target.closest(".tarjeta-admision").remove();
                console.log(`Servicio removido: ${servicioId}`);
            }
        }
    });
});