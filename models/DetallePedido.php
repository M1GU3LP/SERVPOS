<?php
require_once __DIR__ . '/../config/db.php';

// RF4.2-RF4.6: lineas de producto dentro de un pedido.
class DetallePedido
{
    public static function agregar(int $pedidoId, int $productoId, ?int $varianteId, int $cantidad, float $precioUnitario): int
    {
        $pdo = obtenerConexion();
        $subtotal = $cantidad * $precioUnitario;

        $stmt = $pdo->prepare(
            'INSERT INTO detalle_pedido (pedido_id, producto_id, variante_id, cantidad, precio_unitario, subtotal)
             VALUES (:pedido_id, :producto_id, :variante_id, :cantidad, :precio, :subtotal)'
        );
        $stmt->execute([
            'pedido_id' => $pedidoId,
            'producto_id' => $productoId,
            'variante_id' => $varianteId,
            'cantidad' => $cantidad,
            'precio' => $precioUnitario,
            'subtotal' => $subtotal,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function actualizarCantidad(int $detalleId, int $cantidad): void
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare(
            'UPDATE detalle_pedido SET cantidad = :cantidad, subtotal = :cantidad * precio_unitario WHERE id = :id'
        );
        $stmt->execute(['cantidad' => $cantidad, 'id' => $detalleId]);
    }

    public static function eliminar(int $detalleId): void
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('DELETE FROM detalle_pedido WHERE id = :id');
        $stmt->execute(['id' => $detalleId]);
    }

    public static function obtenerPorId(int $detalleId): ?array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM detalle_pedido WHERE id = :id');
        $stmt->execute(['id' => $detalleId]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    public static function obtenerPorPedido(int $pedidoId): array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT d.*, p.nombre AS producto_nombre, v.nombre AS variante_nombre
             FROM detalle_pedido d
             JOIN productos p ON p.id = d.producto_id
             LEFT JOIN variantes_producto v ON v.id = d.variante_id
             WHERE d.pedido_id = :pedido_id
             ORDER BY d.id'
        );
        $stmt->execute(['pedido_id' => $pedidoId]);

        return $stmt->fetchAll();
    }
}
