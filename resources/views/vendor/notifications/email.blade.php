<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - The Beauty Room</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f6f3eb; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333333; -webkit-font-smoothing: antialiased;">

    <!-- Contenedor principal -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f6f3eb; padding: 40px 20px;">
        <tr>
            <td align="center">
                
                <!-- Tarjeta central -->
                <table role="presentation" width="100%" max-width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                    
                    <!-- Encabezado con Logo -->
                    <tr>
                        <td align="center" style="padding: 45px 20px 20px 20px; text-align: center;">
                            <div style="font-size: 28px; color: #c3a677; margin-bottom: 12px;">✦</div>
                            <div style="font-size: 12px; letter-spacing: 4px; font-weight: 600; color: #c3a677; text-transform: uppercase;">
                                The Beauty Room
                            </div>
                        </td>
                    </tr>

                    <!-- Título (Centrado forzado con tabla) -->
                    <tr>
                        <td align="center" style="padding: 0 20px 20px 20px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" style="text-align: center;">
                                        <h1 style="margin: 0; font-family: 'Georgia', serif; font-size: 30px; font-weight: 400; color: #1a1a1a; text-align: center; width: 100%;">
                                            Restablece tu contraseña
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Cuerpo del mensaje -->
                    <tr>
                        <td align="center" style="padding: 0 45px 30px 45px; text-align: center;">
                            <p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.6; color: #555555; text-align: center;">
                                ¡Hola!
                            </p>
                            <p style="margin: 0 0 30px 0; font-size: 15px; line-height: 1.6; color: #666666; text-align: center;">
                                Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>The Beauty Room</strong>. Si fuiste tú, haz clic en el siguiente botón:
                            </p>

                            <!-- Botón de Acción -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto 30px auto;">
                                <tr>
                                    <td align="center" style="border-radius: 6px; background-color: #2c2c2c;">
                                        <a href="{{ $actionUrl }}" target="_blank" style="display: inline-block; padding: 16px 40px; font-size: 14px; font-weight: bold; color: #ffffff; text-decoration: none; letter-spacing: 1px; text-transform: uppercase;">
                                            Restablecer Contraseña
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 10px 0; font-size: 13px; line-height: 1.6; color: #888888; text-align: center;">
                                Este enlace expirará en {{ $expireInMinutes ?? 60 }} minutos.
                            </p>
                            <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #888888; text-align: center;">
                                Si no solicitaste un cambio de contraseña, puedes ignorar este correo.
                            </p>
                        </td>
                    </tr>

                    <!-- Separador -->
                    <tr>
                        <td align="center" style="padding: 0 45px;">
                            <hr style="border: none; border-top: 1px solid #eaeaea; margin: 0;">
                        </td>
                    </tr>

                    <!-- Pie de página -->
                    <tr>
                        <td align="center" style="padding: 25px 45px 35px 45px; text-align: center;">
                            
                            <!-- Caja para la URL larga -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td align="center" style="background-color: #fafafa; border: 1px solid #eeeeee; border-radius: 8px; padding: 15px; text-align: center;">
                                        <p style="margin: 0 0 8px 0; font-size: 11px; color: #999999; text-align: center;">
                                            Si tienes problemas para hacer clic en el botón, copia y pega esta URL en tu navegador:
                                        </p>
                                        <p style="margin: 0; font-size: 11px; color: #8b5e3c; text-align: center; word-break: break-all; line-height: 1.5;">
                                            <a href="{{ $actionUrl }}" style="color: #8b5e3c; text-decoration: none;">{{ $actionUrl }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Firma Syncrostile (Todo centrado) -->
                            <p style="margin: 0; font-size: 10px; color: #aaaaaa; letter-spacing: 1px; text-transform: uppercase; text-align: center;">
                                Sistema de Gestión Operativo <br>
                                <strong style="color: #8b5a2b; font-size: 11px; letter-spacing: 2px;">SYNCROSTILE</strong>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>