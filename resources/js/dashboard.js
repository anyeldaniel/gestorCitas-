document.addEventListener("DOMContentLoaded", () => {
    const formTrabajador = document.getElementById("form-registro-trabajador");

    if (formTrabajador) {
        formTrabajador.addEventListener("submit", (e) => {
            e.preventDefault();
            console.log("Procesando registro de empleado de forma limpia...");
        });
    }

    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("btn-eliminar-trabajador")) {
            const trabajadorId = e.target.getAttribute("data-id");
            if (confirm("¿Está seguro de que desea dar de baja a este trabajador?")) {
                e.target.closest(".tarjeta-admision").remove();
                console.log(`Trabajador eliminado: ${trabajadorId}`);
            }
        }
    });
});