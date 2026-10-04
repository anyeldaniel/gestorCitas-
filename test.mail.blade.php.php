<?php
$host = 'smtp.gmail.com';
$port = 587;
$timeout = 10;

$connection = @fsockopen($host, $port, $errno, $errstr, $timeout);

if (is_resource($connection)) {
    echo "¡Conexión exitosa a $host:$port!\n";
    fclose($connection);
} else {
    echo "Error: No se pudo conectar a $host:$port\n";
    echo "Código de error: $errno\n";
    echo "Mensaje: $errstr\n";
}