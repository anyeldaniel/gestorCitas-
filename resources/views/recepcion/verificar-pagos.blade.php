@extends('layouts.app')

@section('title', 'Historial y Verificación de Pagos - The Beauty Room')

@section('content')
<section class="modulo-vista" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto; font-family: 'Montserrat', sans-serif;">
    <header class="encabezado-modulo mb-8" style="text-align: center; margin-bottom: 30px;">
        <h1 style="font-family: 'Playfair Display', serif; color: #2C352D; font-size: 34px;">Historial y Verificación de Pagos</h1>
        <p style="color: #5C665E; font-size: 15px;">Monitorea todos los pagos reportados y aprueba de forma manual los abonos o transferencias.</p>
    </header>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ icon: 'success', title: '¡Éxito!', text: "{{ session('success') }}", confirmButtonColor: '#5A6B5D' });
            });
        </script>
    @endif

    <div style="background: #FFFFFF; border: 1px solid #E5E2DC; border-radius: 16px; padding: 25px; box-shadow: 0 10px 30px rgba(44, 53, 45, 0.05); overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: #5C665E;">
            <thead>
                <tr style="border-bottom: 2px solid #E5E2DC; color: #2C352D; font-family: 'Playfair Display', serif; font-size: 15px;">
                    <th style="padding: 14px;">ID Cita</th>
                    <th style="padding: 14px;">Cliente</th>
                    <th style="padding: 14px;">Tratamiento</th>
                    <th style="padding: 14px;">Método / Banco</th>
                    <th style="padding: 14px;">Referencia / Teléfono</th>
                    <th style="padding: 14px;">Monto</th>
                    <th style="padding: 14px; text-align: center;">Estatus</th>
                    <th style="padding: 14px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pagos as $pago)
                    <tr style="border-bottom: 1px solid #F4F1EB;">
                        <td style="padding: 14px; font-weight: 600; color: #2C352D;">#{{ $pago->cita_id }}</td>
                        <td style="padding: 14px;">{{ $pago->cita->cliente->nombre ?? 'N/D' }}</td>
                        <td style="padding: 14px;">{{ $pago->cita->servicio->nombre_servicio ?? 'N/D' }}</td>
                        <td style="padding: 14px; text-transform: uppercase;">{{ $pago->metodo_pago }} <br><small style="color: #888;">{{ $pago->banco_origen ?? 'N/A' }}</small></td>
                        <td style="padding: 14px;">{{ $pago->referencia ?? $pago->telefono_pago ?? 'N/A' }}</td>
                        <td style="padding: 14px; font-weight: 600; color: #2C352D;">${{ number_format($pago->monto_anticipado, 2) }}</td>
                        <td style="padding: 14px; text-align: center;">
                            <span style="padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase;
                                @if($pago->estado_pago === 'aprobado') background: #E2ECE9; color: #3A463B;
                                @elseif($pago->estado_pago === 'revision') background: #FCF3E3; color: #8A6D3B;
                                @else background: #FADBD8; color: #900C3F; @endif">
                                {{ ucfirst($pago->estado_pago) }}
                            </span>
                        </td>
                        <td style="padding: 14px; text-align: center;">
                            @if($pago->estado_pago !== 'aprobado')
                                <form action="{{ route('recepcion.aprobar.pago', $pago->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" style="background: linear-gradient(135deg, #5A6B5D, #2C352D); color: #FFF; border: none; padding: 7px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 11px; text-transform: uppercase;">Aprobar</button>
                                </form>
                            @else
                                <span style="color: #27ae60; font-weight: 600; font-size: 12px;">✓ Validado</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #5C665E;">No hay registros de pagos en el historial.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection