<dialog id="modal-servicio" class="modal-contenedor-principal">
    <article class="modal-tarjeta-interna">
        
        <header class="modal-cabecera-spa">
            <h2 id="modal-servicio-titulo" class="modal-titulo-principal">Registrar Nuevo Servicio</h2>
            <button type="button" class="btn-cerrar-redondo" onclick="cerrarModalServicio()">&times;</button>
        </header>

        <form id="form-servicio" 
              autocomplete="off" 
              method="POST" 
              action="{{ route('servicios.guardar') }}" 
              class="form-cuerpo-scrollable" 
              enctype="multipart/form-data">
            @csrf

            <fieldset class="campo-formulario-casilla bg-slate-50/40">
                <legend class="legend-etiqueta-zen">1. Identidad Visual</legend>
                <section class="seccion-alineada-centro">
                    <label class="label-formulario-zen text-center mb-2">Foto promocional del Tratamiento <span class="text-xs text-slate-400 font-normal">(Opcional)</span></label>
                    
                    <div class="contenedor-carga-foto">
                        <figure id="previsualizacion-avatar-servicio" class="avatar-previsualizacion-vacio">SZ</figure>
                        
                        <section class="controles-archivo">
                            <label for="servicio_foto" class="btn-seleccionar-archivo">
                                Seleccionar Imagen
                            </label>
                            <input type="file" id="servicio_foto" name="foto" accept="image/*" style="display: none;">
                            <p class="texto-ayuda-archivo">Formatos sugeridos: JPG, PNG.</p>
                        </section>
                    </div>
                </section>
            </fieldset>

            <fieldset class="campo-formulario-casilla">
                <legend class="legend-etiqueta-zen">2. Datos del Servicio</legend>
                <p class="grupo-input-vertical">
                    <label for="input_nombre" class="label-formulario-zen">Nombre del Servicio *</label>
                    <input type="text" id="input_nombre" name="nombre" required minlength="3" maxlength="255" class="input-estandar-zen" placeholder="Ej. Masaje Descontracturante Profundo">
                    <small class="texto-descripcion-input">Mínimo 3 caracteres, letras y espacios.</small>
                </p>
            </fieldset>

            <fieldset class="campo-formulario-casilla bg-slate-50/40">
                <legend class="legend-etiqueta-zen">3. Tarifas y Duración</legend>
                <section class="grid-tres-columnas-zen">
                    <p class="grupo-input-vertical">
                        <label for="input_precio" class="label-formulario-zen">Precio Base ($) *</label>
                        <input type="number" id="input_precio" name="precio" required min="1" step="0.01" class="input-estandar-zen" placeholder="Ej. 45.00">
                        <small class="texto-descripcion-input">Valor numérico en USD.</small>
                    </p>

                    <p class="grupo-input-vertical">
                        <label for="input_porcentaje" class="label-formulario-zen">Abono Requerido (%) *</label>
                        <input type="number" id="input_porcentaje" name="porcentaje_agendado" required min="0" max="100" class="input-estandar-zen" placeholder="Ej. 10">
                        <small class="texto-descripcion-input">Para reservar cita.</small>
                    </p>

                    <p class="grupo-input-vertical">
                        <label for="input_tiempo" class="label-formulario-zen">Tiempo Estimado *</label>
                        <select id="input_tiempo" name="tiempo_estimado" required class="select-estandar-zen">
                            <option value="30 min - 50 min">30 min - 50 min</option>
                            <option value="60 min - 90 min" selected>60 min - 90 min</option>
                            <option value="90 min - 120 min">90 min - 120 min</option>
                            <option value="120 min - 180 min">120 min - 180 min</option>
                        </select>
                    </p>
                </section>
            </fieldset>

            <fieldset class="campo-formulario-casilla">
                <legend class="legend-etiqueta-zen">4. Personal Asignado</legend>
                <section class="grupo-input-vertical">
                    <label class="label-formulario-zen">Seleccionar Especialistas Disponibles *</label>
                    
                    <nav class="lista-seleccion-scroll-zen">
                        @php
                            $personalActivo = $especialistas ?? $terapeutas ?? collect();
                        @endphp

                        @if($personalActivo->count() > 0)
                            @foreach($personalActivo as $personal)
                                <label class="tarjeta-checkbox-personal-zen">
                                    <input type="checkbox" name="especialistas[]" value="{{ $personal->id }}" checked class="checkbox-estandar-zen">
                                    <span class="nombre-personal-check-zen">{{ $personal->nombre }}</span>
                                </label>
                            @endforeach
                        @else
                            <p class="alerta-sistema-vacio-zen">⚠️ No se lograron mapear los especialistas. Verifica la variable enviada desde el Controller.</p>
                        @endif
                    </nav>
                    <small class="texto-descripcion-input">Marca los miembros del equipo capacitados para realizar este tratamiento.</small>
                </section>
            </fieldset>

            <fieldset class="campo-formulario-casilla bg-slate-50/40">
                <legend class="legend-etiqueta-zen">5. Propiedades Holísticas</legend>
                <p class="grupo-input-vertical">
                    <label for="input_descripcion" class="label-formulario-zen">Descripción del Tratamiento *</label>
                    <textarea id="input_descripcion" name="descripcion" rows="3" required class="textarea-estandar-zen" placeholder="Describe los beneficios corporales, aceites o técnicas incluidas..."></textarea>
                    <small class="texto-descripcion-input">Breve resumen clínico y comercial que se mostrará en el catálogo.</small>
                </p>
            </fieldset>

            <footer class="pie-formulario-fijo-zen">
                <button type="button" class="btn-secundario-zen" onclick="cerrarModalServicio()">Cancelar</button>
                <button type="submit" class="btn-primario-zen">Guardar Servicio</button>
            </footer>
        </form>
    </article>
</dialog>


<dialog id="modal-ver-servicio" class="modal-contenedor-vista-lectura">
    <article class="modal-tarjeta-interna">
        
        <header class="modal-cabecera-spa">
            <h2 class="modal-titulo-vista">Ficha Técnica del Tratamiento</h2>
            <button type="button" class="btn-cerrar-redondo" onclick="document.getElementById('modal-ver-servicio').close()">&times;</button>
        </header>
        
        <section class="cuerpo-vista-scroll">
            <figure class="contenedor-foto-portada">
                <img id="view-servicio-foto" src="" alt="Vista previa del servicio" class="imagen-portada-ajustada">
            </figure>

            <header class="cabecera-detalle-servicio">
                <h3 id="view-servicio-nombre" class="titulo-servicio-vista"></h3>
                <p class="texto-categoria-linea">Línea de Tratamiento: <span class="badge-categoria-premium">Bienestar Premium</span></p>
            </header>

            <section class="grid-detalles-tecnicos">
                <p class="bloque-informacion-celda">
                    <span class="subtitulo-informacion">Precio de Lista:</span>
                    <span id="view-servicio-precio" class="valor-precio-destacado"></span>
                </p>
                
                <p class="bloque-informacion-celda">
                    <span class="subtitulo-informacion">Abono Requerido:</span>
                    <span class="texto-reserva-porcentaje"><span id="view-servicio-porcentaje" class="porcentaje-numero"></span>% para reserva</span>
                </p>
                
                <p class="bloque-informacion-celda col-span-full border-t border-slate-200/60 pt-2">
                    <span class="subtitulo-informacion">Duración Estimada de Sesión:</span>
                    <span id="view-servicio-tiempo" class="badge-tiempo-duracion"></span>
                </p>
                
                <nav class="bloque-informacion-celda col-span-full border-t border-slate-200/60 pt-2">
                    <span class="subtitulo-informacion mb-1.5">Especialistas Certificados:</span>
                    <section id="view-servicio-especialistas" class="flex flex-wrap gap-1.5"></section>
                </nav>

                <p class="bloque-informacion-celda col-span-full border-t border-slate-200/60 pt-2">
                    <span class="subtitulo-informacion mb-1">Beneficios y Propiedades:</span>
                    <span id="view-servicio-descripcion" class="caja-descripcion-lectura"></span>
                </p>
            </section>

            <footer class="flex justify-end pt-2">
                <button type="button" class="btn-vista-cerrar" onclick="document.getElementById('modal-ver-servicio').close()">Cerrar Ficha</button>
            </footer>
        </section>
    </article>
</dialog>


<dialog id="modal-confirmacion-custom" class="modal-contenedor-alerta-critica">
    <article class="cuerpo-alerta-centrado">
        <figure class="icono-alerta-advertencia">⚠️</figure>
        <h3 id="confirm-alerta-titulo" class="titulo-alerta-critica">¿Confirmar acción?</h3>
        <p id="confirm-alerta-mensaje" class="mensaje-alerta-descripcion"></p>
        
        <footer class="pie-alerta-botones">
            <button type="button" id="btn-confirm-cancelar" class="btn-cancelar-alerta">Cancelar</button>
            <button type="button" id="btn-confirm-aceptar" class="btn-aceptar-eliminar">Eliminar permanentemente</button>
        </footer>
    </article>
</dialog>