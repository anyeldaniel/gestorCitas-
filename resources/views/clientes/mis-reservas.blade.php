@extends('layouts.app')

@section('title', 'Mis Reservas - The Beauty Room')

@section('content')
<div style="width: 100%; min-height: 80vh; background-color: #F4F1EB; padding: 40px 0;">
    <div style="width: 92%; max-width: 1400px; margin: 0 auto; font-family: 'Montserrat', sans-serif;">
        
        <header style="text-align: center; margin-bottom: 40px;">
            <h1 style="font-family: 'Playfair Display', serif; color: #2C352D; font-size: 38px; font-weight: 600; margin-bottom: 10px;">Mis Citas y Reservas</h1>
            <p style="color: #5C665E; font-size: 15px;">Consulta el estatus de tus tratamientos de bienestar y el historial de tus pagos.</p>
        </header>

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: "{{ session('success') }}", confirmButtonColor: '#5A6B5D' });
                });
            </script>
        @endif

        <!-- CONTENEDOR FLEX/GRID ESTRICTO PARA 3 COLUMNAS -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; align-items: stretch;">
            @forelse($citas as $cita)
                <div style="background: #FFFFFF; border: 1px solid #E5E2DC; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(44, 53, 45, 0.06); display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <div>
                        <!-- Cabecera de la Tarjeta -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #F4F1EB; padding-bottom: 14px; margin-bottom: 16px;">
                            <h3 style="margin: 0; font-family: 'Playfair Display', serif; color: #2C352D; font-size: 20px; font-weight: 600; line-height: 1.2;">
                                {{ $cita->servicio->nombre_servicio ?? 'Tratamiento Spa' }}
                            </h3>
                            <span style="padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
                                @if($cita->estado === 'confirmada') background: #E2ECE9; color: #3A463B;
                                @elseif($cita->estado === 'pendiente') background: #FCF3E3; color: #8A6D3B;
                                @else background: #FADBD8; color: #900C3F; @endif">
                                {{ ucfirst($cita->estado) }}
                            </span>
                        </div>

                        <!-- Detalles con fuente Montserrat -->
                        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: #5C665E; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>📅</span> <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>⏰</span> <strong>Hora:</strong> {{ \Carbon\Carbon::parse($cita->hora)->format('h:i A') }}
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>💆</span> <strong>Especialista:</strong> {{ $cita->trabajador->nombre ?? 'Asignado por el Spa' }}
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>💵</span> <strong>Costo Total:</strong> <span style="color: #2C352D; font-weight: 600;">${{ number_format($cita->monto_total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN DE PAGOS Y ACCIONES -->
                    <div style="background: #FAFAFA; padding: 14px; border-radius: 10px; border: 1px solid #EFECE6; font-size: 12px; display: flex; flex-direction: column; gap: 10px;">
                        <div>
                            <strong style="display: block; color: #2C352D; margin-bottom: 4px; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">Estatus de Pago</strong> 
                            @php
                                $totalPagado = $cita->pagos->where('estado_pago', 'aprobado')->sum('monto_anticipado');
                                $enRevision = $cita->pagos->where('estado_pago', 'revision')->sum('monto_anticipado');
                            @endphp
                            
                            @if($totalPagado >= $cita->monto_total)
                                <span style="color: #27ae60; font-weight: 600;">✓ ¡Pagado Completo (${{ number_format($totalPagado, 2) }})</span>
                            @elseif($totalPagado > 0)
                                <span style="color: #d35400; font-weight: 600;">Abonado: ${{ number_format($totalPagado, 2) }} (Resta: ${{ number_format($cita->monto_total - $totalPagado, 2) }})</span>
                            @elseif($enRevision > 0)
                                <span style="color: #2980b9; font-weight: 600;">⏳ En verificación (${{ number_format($enRevision, 2) }})</span>
                            @else
                                <span style="color: #c0392b; font-weight: 600;">⚠ Pendiente de pago</span>
                            @endif
                        </div>

                        @if($totalPagado < $cita->monto_total && $enRevision == 0)
                            <a href="{{ route('checkout.view', ['cita_id' => $cita->id]) }}" style="background: linear-gradient(135deg, #5A6B5D, #2C352D); color: #FFF; padding: 10px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(44, 53, 45, 0.15);">
                                Completar Pago
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: #FFFFFF; border-radius: 16px; border: 1px solid #E5E2DC;">
                    <p style="color: #5C665E; font-size: 16px; margin-bottom: 15px;">Aún no tienes citas agendadas en nuestro santuario.</p>
                    <a href="{{ route('catalogo') }}" style="display: inline-block; background: linear-gradient(135deg, #5A6B5D, #2C352D); color: #FFF; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">Explorar Catálogo Zen</a>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection