<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reembolso pendiente de definir</title>
</head>
<body style="margin: 0; padding: 24px; background: #f6f3eb; color: #333; font-family: Arial, sans-serif;">
    <main style="max-width: 600px; margin: 0 auto; padding: 32px; border-radius: 12px; background: #fff;">
        <h1 style="color: #8b5e3c;">Se requiere definir un reembolso</h1>
        <p>El trabajador asignado canceló una cita con un pago aprobado. Admin o recepción debe indicar el importe del reembolso.</p>
        <p>
            <strong>Cita:</strong> #{{ $cita->id }}<br>
            <strong>Cliente:</strong> {{ $cita->cliente->nombre ?? 'No disponible' }}<br>
            <strong>Servicio:</strong> {{ $cita->servicio->nombre_servicio ?? 'No disponible' }}<br>
            <strong>Motivo:</strong> {{ $cita->motivo_cancelacion }}
        </p>
    </main>
</body>
</html>
