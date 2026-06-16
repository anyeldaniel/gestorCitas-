<!-- COMPONENTE MODAL REUTILIZABLE: Gestión de Especialistas --> 
<dialog id="modal-terapeuta" class="modal-zen">
    <header class="modal-header">
        <h2 id="modal-titulo">Registrar Nuevo Especialista</h2>
        <button type="button" class="btn-cerrar-modal" onclick="cerrarModal()">&times;</button>
    </header>

    <form id="form-terapeuta" autocomplete="off" class="modal-form">
        @csrf
        
        <fieldset class="campo-formulario">
            <label for="nombre">Nombre Completo</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej. Dra. Alana Ramos">
        </fieldset>

        <fieldset class="campo-formulario">
            <label for="especialidad">Especialidad Técnica</label>
            <input type="text" id="especialidad" name="especialidad" required placeholder="Ej. Dermatología Cosmética">
        </fieldset>

        <!-- Correo Electrónico para Autenticación -->
        <fieldset class="campo-formulario">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" required placeholder="ejemplo@thebeautyroom.com">
        </fieldset>

        <fieldset class="campo-formulario">
            <label for="descripcion">Biografía / Descripción Breve</label>
            <textarea id="descripcion" name="descripcion" rows="3" required placeholder="Resumen de la experiencia y tratamientos que maneja..."></textarea>
        </fieldset>

         <!-- SECCIÓN DE SEGURIDAD: Inputs adaptativos con iconos vectoriales dinámicos --> 
        <section id="campo-password" class="grupo-seguridad-modal">
            <fieldset class="campo-formulario campo-password-contenedor">
                <label for="password">Contraseña de Acceso</label>
                <div class="input-icono-wrapper">
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres">
                    <button type="button" class="btn-alternar-password" onclick="alternarVisibilidad('password', this)" aria-label="Mostrar contraseña">
                        <!-- Icono Ojo Abierto SVG -->
                        <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </fieldset>

            <fieldset class="campo-formulario campo-password-contenedor">
                <label for="password_confirmation">Confirmar Contraseña</label>
                <div class="input-icono-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repita la contraseña">
                    <button type="button" class="btn-alternar-password" onclick="alternarVisibilidad('password_confirmation', this)" aria-label="Mostrar contraseña">
                        <!-- Icono Ojo Abierto SVG -->
                        <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </fieldset>
        </section>

        <footer class="modal-acciones">
            <button type="button" class="btn-zen btn-secundario" onclick="cerrarModal()">Cancelar</button>
            <button type="submit" class="btn-zen btn-primario">Guardar Especialista</button>
        </footer>
    </form>
</dialog>