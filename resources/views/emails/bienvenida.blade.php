<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a The Beauty Room</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f6f3eb; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333333;">

    <!-- Contenedor principal con el fondo beige de tus tarjetas -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f6f3eb; padding: 40px 20px;">
        <tr>
            <td align="center">
                
                <!-- Tarjeta central blanca -->
                <table role="presentation" width="100%" max-width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                    
                    <!-- IMAGEN OPCIONAL: Si quieres poner la foto de la mujer, descomenta esto y pon la URL de tu imagen -->
                    <!-- 
                    <tr>
                        <td align="center" style="padding: 0;">
                            <img src="URL_DE_TU_IMAGEN_AQUI.jpg" alt="Un momento para ti" width="100%" style="display: block; max-width: 600px; height: auto; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        </td>
                    </tr>
                    -->

                    <!-- Encabezado con el Logo (idéntico a tu captura) -->
                    <tr>
                        <td align="center" style="padding: 45px 20px 20px 20px;">
                            <div style="font-size: 24px; color: #c3a677; margin-bottom: 15px;">✦</div>
                            <div style="font-size: 13px; letter-spacing: 4px; font-weight: 600; color: #c3a677; text-transform: uppercase;">
                                The Beauty Room
                            </div>
                        </td>
                    </tr>

                    <!-- Título principal -->
                    <tr>
                        <td align="center" style="padding: 0 20px 25px 20px;">
                            <h1 style="margin: 0; font-family: 'Georgia', serif; font-size: 34px; font-weight: 400; color: #1a1a1a;">
                                Bienvenido a bordo
                            </h1>
                        </td>
                    </tr>

                    <!-- Cuerpo del mensaje -->
                    <tr>
                        <td style="padding: 0 45px 35px 45px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #555555; text-align: center;">
                                ¡Hola, <strong style="color: #1a1a1a;">{{ $user->name }}</strong>!
                            </p>
                            
                            <p style="margin: 0; font-size: 15px; line-height: 1.7; color: #666666; text-align: center;">
                                Te damos la bienvenida a <strong>The Beauty Room</strong>. Tu cuenta ha sido creada exitosamente y ya puedes comenzar a disfrutar de un momento para ti.
                            </p>

                            <!-- Botón estilo "Iniciar Sesión" (Color oscuro exacto) -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 35px auto 0 auto;">
                                <tr>
                                    <td align="center" style="border-radius: 6px; background-color: #2c2c2c;">
                                        <a href="{{ url('/login') }}" target="_blank" style="display: inline-block; padding: 15px 35px; font-size: 14px; font-weight: bold; color: #ffffff; text-decoration: none; letter-spacing: 1px; text-transform: uppercase;">
                                            Iniciar Sesión
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Separador -->
                    <tr>
                        <td align="center" style="padding: 0 45px;">
                            <hr style="border: none; border-top: 1px solid #eaeaea; margin: 0;">
                        </td>
                    </tr>

                    <!-- Pie de página con patrocinio destacado -->
                    <tr>
                        <td align="center" style="padding: 25px 45px 45px 45px;">
                            <p style="margin: 0 0 15px 0; font-size: 12px; color: #999999;">
                                Este es un correo automático, por favor no respondas a este mensaje.
                            </p>
                            
                            <!-- Texto de Syncrostile destacado con el color tierra de tus tarjetas -->
                            <div style="font-size: 11px; color: #888888; letter-spacing: 1px; text-transform: uppercase;">
                                Sistema de Gestión Operativo <br>
                                <strong style="color: #8b5a2b; font-size: 13px; letter-spacing: 2px;">SYNCROSTILE</strong>
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>