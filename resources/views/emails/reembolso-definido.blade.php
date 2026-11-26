<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importe de reembolso definido</title>
</head>
<body style="margin: 0; padding: 24px; background: #f6f3eb; color: #333; font-family: Arial, sans-serif;">
    <main style="max-width: 600px; margin: 0 auto; padding: 32px; border-radius: 12px; background: #fff;">
        <h1 style="color: #8b5e3c;">Tu reembolso ya tiene un importe definido</h1>
        <p>Hola {{ $pago->cita->cliente->nombre ?? 'cliente' }},</p>
        <p>
            Para la cita #{{ $pago->cita_id }} de
            <strong>{{ $pago->cita->servicio->nombre_servicio ?? 'tu servicio' }}</strong>,
            se registró un reembolso de
            <strong>${{ number_format((float) $pago->monto_reembolso, 2) }}</strong>.
        </p>
        <p>El reembolso está pendiente de procesarse. Este correo confirma el importe, no la transferencia.</p>
        @if($pago->cita->motivo_cancelacion)
            <p><strong>Motivo de cancelación:</strong> {{ $pago->cita->motivo_cancelacion }}</p>
        @endif
        <p style="color: #777;">The Beauty Room</p>
    </main>
</body>
</html>
