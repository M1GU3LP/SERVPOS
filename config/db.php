<?php
// Conexion PDO a MySQL. Valores por defecto pensados para XAMPP/Laragon en local;
// se pueden sobreescribir con variables de entorno para despliegue (ver manual de programador).
function obtenerConexion(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = getenv('SERVPOS_DB_HOST') ?: '127.0.0.1';
        $puerto = getenv('SERVPOS_DB_PORT') ?: '3306';
        $nombreBd = getenv('SERVPOS_DB_NAME') ?: 'servpos_db';
        $usuario = getenv('SERVPOS_DB_USER') ?: 'root';
        $clave = getenv('SERVPOS_DB_PASS') ?: '';

        $dsn = "mysql:host={$host};port={$puerto};dbname={$nombreBd};charset=utf8mb4";

        $pdo = new PDO($dsn, $usuario, $clave, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}
