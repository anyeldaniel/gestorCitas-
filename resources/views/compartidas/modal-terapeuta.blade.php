<!-- COMPONENTE MODAL REUTILIZABLE: Gestión de Especialistas --> 
 <dialog id="modal-terapeuta" class="modal-zen">
    <header class="modal-header">
        <h2 id="modal-titulo">Registrar Nuevo Especialista</h2>
        <button type="button" class="btn-cerrar-modal" onclick="cerrarModal()">&times;</button>
    </header>

    <form id="form-terapeuta" autocomplete="off" method="POST" action="{{ route('admin.create-trabajador') }}" class="modal-form" enctype="multipart/form-file">
        @csrf
        
        <fieldset class="campo-formulario">
            <label for="terapeuta_foto">Foto de Perfil (Opcional)</label>
            <div class="campo-foto-previsualizacion">
                <div id="previsualizacion-avatar-terapeuta" class="avatar-preview">TF</div>
                <input type="file" id="terapeuta_foto" name="foto" accept="image/*" onchange="previsualizarImagen(this, 'previsualizacion-avatar-terapeuta')">
            </div>
        </fieldset>

        <fieldset class="campo-formulario">
            <label for="nombre">Nombre Completo</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej. Alana Ramos">
        </fieldset>

        <fieldset class="campo-formulario">
            <label for="telefono">Número de Teléfono</label>
            <input type="text" id="telefono" name="telefono" required placeholder="Ej. 0412-456-7890">
        </fieldset>
        <!-- Correo Electrónico para Autenticación -->
        <fieldset class="campo-formulario">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" required placeholder="ejemplo@thebeautyroom.com">
        </fieldset>

        <fieldset class="campo-formulario">
            <label>Especialidades</label>
            <div id="wrapper-especialidades-lista" class="contenedor-especialidades-dinamicas">
                <div class="fila-especialidad">
                    <input type="text" class="input-especialidad-item" placeholder="Ej. Rejuvenecimiento Facial" required>
                    <button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button>
                </div>
            </div>
            <input type="hidden" id="especialidades_hidden" name="especialidades">
        </fieldset>

        <fieldset class="campo-formulario">
            <label for="descripcion">Biografía / Descripción Breve</label>
            <textarea id="descripcion" name="descripcion" rows="3" placeholder="Resumen de la experiencia y tratamientos que maneja..."></textarea>
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

<dialog id="modal-ver-terapeuta" class="modal-zen">
    <header class="modal-header">
        <h2>Información Detallada del Terapeuta</h2>
        <button type="button" class="btn-cerrar-modal" onclick="document.getElementById('modal-ver-terapeuta').close()">&times;</button>
    </header>
    <div class="modal-form">
        <div style="display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
            <div id="view-terapeuta-foto" class="avatar-preview" style="width:70px; height:70px; font-size:1.5rem;"></div>
            <div>
                <h3 id="view-terapeuta-nombre" style="margin:0; font-size:1.3rem; color:var(--color-texto-oscuro);"></h3>
                <p style="margin:0.2rem 0 0 0; font-size:0.9rem;">Rol: <span id="view-terapeuta-rol" class="tag-rol especialista">Especialista</span></p>
            </div>
        </div>
        
        <div style="display:flex; flex-direction:column; gap:0.8rem; margin-top:0.5rem;">
            <div><strong>Contacto Telefónico:</strong> <span id="view-terapeuta-telefono"></span></div>
            <div><strong>Correo Electrónico:</strong> <span id="view-terapeuta-email"></span></div>
            <div>
                <strong>Especialidades Especiales:</strong>
                <div id="view-terapeuta-especialidades" class="contenedor-tags-especialidades"></div>
            </div>
            <div><strong>Descripción / Biografía:</strong><p id="view-terapeuta-descripcion" style="margin:0.25rem 0; background:#f8fafc; padding:0.5rem; border-radius:0.375rem; font-size:0.9rem; color:#475569;"></p></div>
        </div>

        <footer class="modal-acciones" style="margin-top:1rem; border-top:1px solid #e2e8f0; padding-top:1rem;">
            <button type="button" class="btn-zen btn-secundario" onclick="document.getElementById('modal-ver-terapeuta').close()">Regresar</button>
            <div id="view-acciones-admin" style="display:flex; gap:0.5rem;">
                </div>
        </footer>
    </div>
</dialog>

<dialog id="modal-confirmacion-custom" class="modal-alerta-custom">
    <div class="alerta-contenido">
        <h3 id="confirm-alerta-titulo">¿Confirmar acción?</h3>
        <p id="confirm-alerta-mensaje">¿Estás seguro de que deseas continuar con esta acción?</p>
        <div class="alerta-acciones">
            <button type="button" id="btn-confirm-cancelar" class="btn-zen btn-secundario" style="padding: 0.4rem 1rem;">Cancelar</button>
            <button type="button" id="btn-confirm-aceptar" class="btn-zen btn-baja" style="padding: 0.4rem 1rem; background-color: var(--color-error);">Eliminar</button>
        </div>
    </div>
</dialog>