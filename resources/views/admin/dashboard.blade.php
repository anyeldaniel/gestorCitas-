@extends('layouts.app')
 
@section('title', 'Dashboard Administrativo - Syncrostyle')
@push('styles')
 
  @vite(['resources/css/terapeutas.css'])
@endpush
 
@section('content')
<main class="modulo-vista dashboard-admin">
    <header class="encabezado-modulo">
 
      <h1>Panel de Control Administrativo</h1>
 
      <p>Gestione el personal de "The Beauty
Room", supervise el estado del negocio y controle los accesos de la
plataforma.</p>
 
  </header>
 
    <section
class="tarjetas-resumen-admin">
        <article
class="tarjeta-kpi">
            <small>Citas
Totales</small>
            
<h2>1,240</h2>
        </article>
 
        <article
class="tarjeta-kpi">
            
<small>Especialistas Activos</small>
            
<h2>8</h2>
        </article>
 
        <article
class="tarjeta-kpi kpi-ingresos">
            
<small>Ingresos Estimados</small>
            
<h2>$3,450</h2>
        </article>
    </section>
 
    <div
class="contenedor-doble-columnas">
        
        <section
class="lista-admisiones">
            <header
class="encabezado-registro" style="display: flex;
justify-content: space-between; align-items: center; flex-wrap: wrap; gap:
0.5rem;">
                <h2>Equipo de
Recepción</h2>
 
              <button
type="button" class="btn-terapeuta-agregar"
onclick="abrirModalRecepcionista()">
                
    + Agregar recepcionista
                
</button>
            </header>
 
            <section
id="contenedor-recepcionistas" class="grid-personal"
style="display:flex; flex-direction:column; gap:1rem;
min-height:150px;">
                <article
class="tarjeta-admision tarjeta-recepcionista-item"
data-id="2" data-correo="carlos@thebeautyroom.com"
data-telefono="0414-123-4567">
 
                  <figure
class="terapeuta-foto-contenedor" style="margin-bottom:0.5rem;
display:flex; justify-content:center;">
                
        <div class="foto-avatar-simulado"
style="width:50px; height:50px; border-radius:50%; background:#cbd5e1;
display:flex; align-items:center; justify-content:center; font-weight:bold;
overflow:hidden;">CM</div>
                
    </figure>
                
    <header class="info-cliente"
style="text-align:center;">
                
        <h3>Carlos
Mendoza</h3>
 
                    
<p>Rol: <span class="tag-rol
recepcionista">Recepcionista</span></p>
 
                  </header>
                
    <footer style="display: flex; gap: 0.5rem; padding: 0;
background: none; border: none; margin-top: auto;
justify-content:center;">
                
        <button type="button"
class="btn-zen" onclick="editarRecepcionista(2)"
style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1;
padding: 0.35rem 0.75rem; font-size: 0.85rem; border-radius: 0.375rem;
font-weight: 600; cursor: pointer;">Editar</button>
                
        <button type="button"
class="btn-zen btn-baja"
onclick="eliminarRecepcionista(2)">Baja</button>
                
    </footer>
                
</article>
            </section>
 
            <div
class="paginador-ui">
                
<button type="button" id="prev-recepcionistas"
class="btn-paginacion">&laquo; Ant</button>
                <span
id="info-recepcionistas" class="info-paginacion">Pág. 1
de 1</span>
 
              <button
type="button" id="next-recepcionistas"
class="btn-paginacion">Sig &raquo;</button>
            </div>
        </section>
 
        <section
class="lista-admisiones">
            <header
class="encabezado-registro" style="display: flex;
justify-content: space-between; align-items: center; flex-wrap: wrap; gap:
0.5rem;">
                <h2>Especialistas
Registrados</h2>
 
              <button
type="button" class="btn-terapeuta-agregar"
onclick="abrirModalAgregar()">
                
    + Agregar
especialista
 
              </button>
 
          </header>
 
          
 
          <section
id="contenedor-trabajadores" class="grid-personal"
style="display:flex; flex-direction:column; gap:1rem;">
 
              <article
class="tarjeta-admision tarjeta-terapeuta" data-id="1"
data-especialidades="Rejuvenecimiento Facial, Peeling Químico, Masajes
Relajantes">
 
                  <figure
class="terapeuta-foto-contenedor" style="margin-bottom:0.5rem;
display:flex; justify-content:center;">
                
        <div class="foto-avatar-simulado"
style="width:50px; height:50px; border-radius:50%; background:#cbd5e1;
display:flex; align-items:center; justify-content:center; font-weight:bold;
overflow:hidden;">AR</div>
                
    </figure>
                
    <header class="info-cliente"
style="text-align:center;">
                
        <h3>Dra.
Alana Ramos</h3>
 
                    
<p>Rol: <span class="tag-rol
especialista">Especialista</span></p>
 
                    
<div class="contenedor-tags-especialidades"
style="justify-content:center;">
 
                    
    <span class="tag-especialidad">Rejuvenecimiento
Facial</span>
 
                    
    <span class="tag-especialidad">Peeling
Químico</span>
                
        </div>
                
        <p class="terapeuta-telefono"
style="display:none;">123-456-7890</p>
                
        <p class="terapeuta-email"
style="display:none;">alana@thebeautyroom.com</p>
                
        <p
class="terapeuta-descripcion"
style="display:none;">Especialista en rejuvenecimiento facial,
peeling químico y masajes relajantes premium.</p>
 
                  </header>
                
    <footer style="display: flex; gap: 0.5rem; padding: 0;
background: none; border: none; margin-top: auto;
justify-content:center;">
                
        <button type="button"
class="btn-zen btn-ver-especialista"
onclick="verTerapeutaDetalle(1)">Ver Terapeuta</button>
                
        <button type="button"
class="btn-zen" onclick="editarTerapeuta(1)"
style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1;
padding: 0.35rem 0.75rem; font-size: 0.85rem; border-radius: 0.375rem;
font-weight: 600; cursor: pointer;">Editar</button>
                
        <button type="button"
class="btn-zen btn-baja"
onclick="eliminarTerapeuta(1)">Baja</button>
                
    </footer>
                
</article>
            </section>
 
            <div
class="paginador-ui">
                
<button type="button" id="prev-terapeutas"
class="btn-paginacion">&laquo; Ant</button>
                
<span id="info-terapeutas"
class="info-paginacion">Pág. 1 de 1</span>
                
<button type="button" id="next-terapeutas"
class="btn-paginacion">Sig &raquo;</button>
            </div>
        </section>
 
    </div>
 
    <dialog id="modal-recepcionista"
class="modal-zen">
        <header
class="modal-header">
            <h2
id="modal-recepcionista-titulo">Agregar Nuevo
Recepcionista</h2>
 
          <button type="button"
class="btn-cerrar-modal"
onclick="document.getElementById('modal-recepcionista').close()">&times;</button>
        </header>
        <form
id="form-recepcionista" autocomplete="off"
class="modal-form">
            <fieldset
class="campo-formulario">
 
              <label
for="recep_foto">Foto de Perfil (Opcional)</label>
 
              <div
class="campo-foto-previsualizacion">
 
                  <div
id="previsualizacion-avatar-recepcionista" class="avatar-preview">RE</div>
 
                  <input
type="file" accept="image/*"
onchange="previsualizarImagen(this,
'previsualizacion-avatar-recepcionista')">
                
</div>
            </fieldset>
 
            <fieldset
class="campo-formulario">
                
<label for="recep_name">Nombre Completo</label>
                
<input type="text" id="recep_name" required
placeholder="Ej. Carlos Mendoza">
            </fieldset>
 
            <fieldset
class="campo-formulario">
                
<label for="recep_phone">Teléfono de Contacto</label>
                
<input type="text" id="recep_phone" required
placeholder="Ej. 0414-111-2233">
            </fieldset>
 
            <fieldset
class="campo-formulario">
                
<label for="recep_email">Correo Electrónico</label>
                
<input type="email" id="recep_email" required
placeholder="carlos@thebeautyroom.com">
            </fieldset>
 
            <div id="error-password-recep" class="aviso-error-password" style="display: none; color: var(--color-error); font-size: 0.85rem; font-weight: 600; margin-bottom: -0.5rem;">
                Las contraseñas no coinciden. Por favor, verifíquelas.
            </div>

            <section id="recep-campo-password" class="grupo-seguridad-modal grid-dos-columnas">
                <fieldset class="campo-formulario campo-password-contenedor">
                    <label for="recep_password">Contraseña de Acceso</label>
                    <div class="input-icono-wrapper">
                        <input type="password" id="recep_password" placeholder="Mínimo 8 caracteres">
                        <button type="button" class="btn-alternar-password" onclick="alternarVisibilidad('recep_password', this)" aria-label="Mostrar contraseña">
                            <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                </fieldset>

                <fieldset class="campo-formulario campo-password-contenedor">
                    <label for="recep_password_confirmation">Confirmar Contraseña</label>
                    <div class="input-icono-wrapper">
                        <input type="password" id="recep_password_confirmation" placeholder="Repita la contraseña">
                        <button type="button" class="btn-alternar-password" onclick="alternarVisibilidad('recep_password_confirmation', this)" aria-label="Mostrar contraseña">
                            <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                </fieldset>
            </section>
 
            <footer
class="modal-acciones">
                
<button type="button" class="btn-zen btn-secundario"
onclick="document.getElementById('modal-recepcionista').close()">Cancelar</button>
                
<button type="submit" class="btn-zen
btn-primario">Guardar Recepcionista</button>
            </footer>
 
      </form>
 
  </dialog>
@include('compartidas.modal-terapeuta')
</main>
@endsection
@push('scripts')
    @vite(['resources/js/terapeutas.js',
'resources/js/dashboard.js'])
@endpush