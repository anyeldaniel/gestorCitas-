<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita pendiente de confirmación</title>
</head>
<body style="margin: 0; padding: 24px; background: #f6f3eb; color: #333; font-family: Arial, sans-serif;">
    {{-- Plantilla del aviso al cliente; recibe la cita y muestra su servicio, fecha y hora. --}}
    <main style="max-width: 600px; margin: 0 auto; padding: 32px; border-radius: 12px; background: #fff;">
        <h1 style="color: #8b5e3c;">Hola, {{ $cita->cliente->nombre }}</h1>
        <p>Hemos procesado la información de tu pago para la cita de <strong>{{ $cita->servicio->nombre_servicio ?? 'tu servicio' }}</strong>.</p>
        <p>Tu cita está <strong>pendiente de confirmación</strong>. El equipo de The Beauty Room verificará la información y te avisará cuando quede confirmada.</p>
        <p>
            <strong>Fecha:</strong> {{ $cita->fecha->format('d/m/Y') }}<br>
            <strong>Hora:</strong> {{ \Carbon\Carbon::parse($cita->hora)->format('h:i A') }}
        </p>
        <p style="color: #777;">The Beauty Room</p>
    </main>
</body>
</html>
