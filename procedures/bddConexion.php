<?php
$usuario = 'root';
$clave = '';
$db = 'mediportal';
$host = 'localhost';

$mysqli = new mysqli($host, $usuario, $clave, $db);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

?>