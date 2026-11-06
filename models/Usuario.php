<?php
require_once __DIR__ . '/../config/db.php';

// RF1.1-RF1.7: registro, consulta, modificacion y desactivacion de usuarios.
class Usuario
{
    public ?int $id = null;
    public string $nombre = '';
    public string $correo = '';
    public string $rol = '';
    public bool $activo = true;

    public static function crear(string $nombre, string $correo, string $password, string $rol): int
    {
        $pdo = obtenerConexion();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (nombre, correo, password_hash, rol) VALUES (:nombre, :correo, :hash, :rol)'
        );
        $stmt->execute([
            'nombre' => $nombre,
            'correo' => $correo,
            'hash' => $hash,
            'rol' => $rol,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function existeCorreo(string $correo): bool
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE correo = :correo');
        $stmt->execute(['correo' => $correo]);

        return $stmt->fetch() !== false;
    }

    public static function obtenerPorCorreo(string $correo): ?array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo = :correo');
        $stmt->execute(['correo' => $correo]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    public static function obtenerPorId(int $id): ?array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    public static function obtenerTodos(): array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->query('SELECT id, nombre, correo, rol, activo, creado_en FROM usuarios ORDER BY nombre');

        return $stmt->fetchAll();
    }

    public static function actualizar(int $id, string $nombre, string $correo, string $rol): void
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare(
            'UPDATE usuarios SET nombre = :nombre, correo = :correo, rol = :rol WHERE id = :id'
        );
        $stmt->execute([
            'nombre' => $nombre,
            'correo' => $correo,
            'rol' => $rol,
            'id' => $id,
        ]);
    }

    public static function actualizarPassword(int $id, string $password): void
    {
        $pdo = obtenerConexion();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => $hash, 'id' => $id]);
    }

    public static function establecerActivo(int $id, bool $activo): void
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('UPDATE usuarios SET activo = :activo WHERE id = :id');
        $stmt->execute(['activo' => $activo ? 1 : 0, 'id' => $id]);
    }

    public static function verificarCredenciales(string $correo, string $password): ?array
    {
        $usuario = self::obtenerPorCorreo($correo);

        if ($usuario === null || !(int) $usuario['activo']) {
            return null;
        }

        if (!password_verify($password, $usuario['password_hash'])) {
            return null;
        }

        return $usuario;
    }
}
