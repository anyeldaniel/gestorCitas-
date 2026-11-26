<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita confirmada</title>
</head>
<body style="margin: 0; padding: 24px; background: #f6f3eb; color: #333; font-family: Arial, sans-serif;">
    {{-- Contenedor visual del correo para mantener el mensaje centrado y legible. --}}
    <main style="max-width: 600px; margin: 0 auto; padding: 32px; border-radius: 12px; background: #fff;">
        {{-- Saluda al cliente por su nombre y anuncia que la cita quedó confirmada. --}}
        <h1 style="color: #8b5e3c;">¡Tu cita está confirmada, {{ $cita->cliente->nombre }}!</h1>
        {{-- Muestra el servicio asociado a la cita; usa un texto alternativo si no existe. --}}
        <p>El equipo de The Beauty Room confirmó tu cita para <strong>{{ $cita->servicio->nombre_servicio ?? 'tu servicio' }}</strong>.</p>
        <p>
            {{-- Presenta la fecha de la cita en formato día/mes/año. --}}
            <strong>Fecha:</strong> {{ $cita->fecha->format('d/m/Y') }}<br>
            {{-- Presenta la hora de la cita en formato de 12 horas. --}}
            <strong>Hora:</strong> {{ \Carbon\Carbon::parse($cita->hora)->format('h:i A') }}
        </p>
        {{-- Cierra el mensaje con una frase de bienvenida al servicio. --}}
        <p>¡Te esperamos!</p>
        {{-- Identifica al negocio que envía el aviso. --}}
        <p style="color: #777;">The Beauty Room</p>
    </main>
</body>
</html>
