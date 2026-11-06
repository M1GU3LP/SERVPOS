<?php
require_once __DIR__ . '/../config/db.php';

// RF3.5: registro del movimiento tipo "venta" generado al confirmar un pedido.
// Usado desde Pedido::confirmar() dentro de la misma transaccion PDO.
class MovimientoInventario
{
    public static function registrarVenta(PDO $pdo, int $productoId, int $cantidad, int $usuarioId): void
    {
        $pdo->prepare(
            'INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, usuario_id, motivo)
             VALUES (:producto_id, "venta", :cantidad, :usuario_id, "Venta por pedido")'
        )->execute([
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'usuario_id' => $usuarioId,
        ]);
    }
}
