<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/DetallePedido.php';
require_once __DIR__ . '/MovimientoInventario.php';

// RF4.1-RF4.10: ciclo de vida de un pedido, desde su creacion hasta la confirmacion.
class Pedido
{
    public static function crear(string $mesaOCliente, int $usuarioId): int
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare(
            'INSERT INTO pedidos (mesa_o_cliente, usuario_id) VALUES (:mesa, :usuario_id)'
        );
        $stmt->execute([
            'mesa' => $mesaOCliente,
            'usuario_id' => $usuarioId,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function obtenerPorId(int $id): ?array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM pedidos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    public static function obtenerActivos(): array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->query(
            "SELECT p.*, u.nombre AS cajero_nombre FROM pedidos p
             JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.estado NOT IN ('confirmado', 'cancelado')
             ORDER BY p.creado_en"
        );

        return $stmt->fetchAll();
    }

    // RF4.9/RF4.10: lista de pedidos visible para el area de cocina.
    public static function obtenerParaCocina(): array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->query(
            "SELECT * FROM pedidos WHERE estado IN ('pendiente', 'en_preparacion') ORDER BY creado_en"
        );

        return $stmt->fetchAll();
    }

    public static function cambiarEstado(int $id, string $estado): void
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('UPDATE pedidos SET estado = :estado WHERE id = :id');
        $stmt->execute(['estado' => $estado, 'id' => $id]);
    }

    // RF4.6: recalcula el total en el servidor a partir de las lineas del pedido.
    public static function recalcularTotal(int $pedidoId): float
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(subtotal), 0) AS total FROM detalle_pedido WHERE pedido_id = :id');
        $stmt->execute(['id' => $pedidoId]);
        $total = (float) $stmt->fetchColumn();

        $pdo->prepare('UPDATE pedidos SET total = :total WHERE id = :id')
            ->execute(['total' => $total, 'id' => $pedidoId]);

        return $total;
    }

    // RF4.8/RF4.9: confirma el pedido, descuenta stock y lo envia a cocina, todo en una sola transaccion.
    public static function confirmar(int $pedidoId, int $usuarioId): void
    {
        $pdo = obtenerConexion();
        $pedido = self::obtenerPorId($pedidoId);

        if ($pedido === null || $pedido['estado'] !== 'borrador') {
            throw new RuntimeException('El pedido ya fue confirmado o no existe.');
        }

        $lineas = DetallePedido::obtenerPorPedido($pedidoId);

        if (count($lineas) === 0) {
            throw new RuntimeException('No se puede confirmar un pedido sin productos.');
        }

        $pdo->beginTransaction();

        try {
            foreach ($lineas as $linea) {
                $stmt = $pdo->prepare('SELECT stock_actual FROM productos WHERE id = :id FOR UPDATE');
                $stmt->execute(['id' => $linea['producto_id']]);
                $producto = $stmt->fetch();

                if ($producto === false || (int) $producto['stock_actual'] < (int) $linea['cantidad']) {
                    throw new RuntimeException("No hay suficiente existencia para \"{$linea['producto_nombre']}\".");
                }

                $nuevoStock = (int) $producto['stock_actual'] - (int) $linea['cantidad'];
                $pdo->prepare('UPDATE productos SET stock_actual = :stock WHERE id = :id')
                    ->execute(['stock' => $nuevoStock, 'id' => $linea['producto_id']]);

                MovimientoInventario::registrarVenta($pdo, (int) $linea['producto_id'], (int) $linea['cantidad'], $usuarioId);
            }

            $pdo->prepare("UPDATE pedidos SET estado = 'pendiente' WHERE id = :id")
                ->execute(['id' => $pedidoId]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function cancelar(int $id): void
    {
        self::cambiarEstado($id, 'cancelado');
    }
}
