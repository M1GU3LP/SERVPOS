<?php
require_once __DIR__ . '/../config/db.php';

// RF4.3: tamaños o variantes de un producto.
class VarianteProducto
{
    public static function crear(int $productoId, string $nombre, float $precioAdicional): int
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare(
            'INSERT INTO variantes_producto (producto_id, nombre, precio_adicional) VALUES (:producto_id, :nombre, :precio)'
        );
        $stmt->execute([
            'producto_id' => $productoId,
            'nombre' => $nombre,
            'precio' => $precioAdicional,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function obtenerPorProducto(int $productoId): array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM variantes_producto WHERE producto_id = :producto_id ORDER BY precio_adicional');
        $stmt->execute(['producto_id' => $productoId]);

        return $stmt->fetchAll();
    }

    public static function obtenerPorId(int $id): ?array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM variantes_producto WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }
}
