<?php
require_once __DIR__ . '/../config/db.php';

// RF3.1-RF3.9: registro, consulta y modificacion de productos e inventario.
class Producto
{
    public static function crear(string $nombre, ?string $descripcion, ?string $categoria, float $precio, int $stockInicial, int $stockMinimo): int
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare(
            'INSERT INTO productos (nombre, descripcion, categoria, precio, stock_actual, stock_minimo)
             VALUES (:nombre, :descripcion, :categoria, :precio, :stock, :stock_minimo)'
        );
        $stmt->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'categoria' => $categoria,
            'precio' => $precio,
            'stock' => $stockInicial,
            'stock_minimo' => $stockMinimo,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function existeNombre(string $nombre): bool
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT id FROM productos WHERE nombre = :nombre AND activo = 1');
        $stmt->execute(['nombre' => $nombre]);

        return $stmt->fetch() !== false;
    }

    public static function obtenerPorId(int $id): ?array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    public static function obtenerDisponibles(): array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->query('SELECT * FROM productos WHERE activo = 1 ORDER BY nombre');

        return $stmt->fetchAll();
    }

    public static function obtenerConBajoStock(): array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->query(
            'SELECT * FROM productos WHERE activo = 1 AND stock_actual < stock_minimo ORDER BY stock_actual ASC'
        );

        return $stmt->fetchAll();
    }

    public static function actualizar(int $id, string $nombre, ?string $descripcion, ?string $categoria, float $precio, int $stockMinimo): void
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare(
            'UPDATE productos SET nombre = :nombre, descripcion = :descripcion, categoria = :categoria,
                precio = :precio, stock_minimo = :stock_minimo WHERE id = :id'
        );
        $stmt->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'categoria' => $categoria,
            'precio' => $precio,
            'stock_minimo' => $stockMinimo,
            'id' => $id,
        ]);
    }

    public static function desactivar(int $id): void
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('UPDATE productos SET activo = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    // RF3.6/RF3.7: ingreso o salida manual de productos del inventario.
    public static function registrarMovimiento(int $productoId, string $tipo, int $cantidad, int $usuarioId, ?string $motivo): void
    {
        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad debe ser mayor que cero.');
        }

        $pdo = obtenerConexion();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('SELECT stock_actual FROM productos WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $productoId]);
            $producto = $stmt->fetch();

            if ($producto === false) {
                throw new RuntimeException('El producto no existe.');
            }

            $delta = $tipo === 'entrada' ? $cantidad : -$cantidad;
            $nuevoStock = (int) $producto['stock_actual'] + $delta;

            if ($nuevoStock < 0) {
                throw new RuntimeException('No hay suficiente existencia disponible.');
            }

            $pdo->prepare('UPDATE productos SET stock_actual = :stock WHERE id = :id')
                ->execute(['stock' => $nuevoStock, 'id' => $productoId]);

            $pdo->prepare(
                'INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, usuario_id, motivo)
                 VALUES (:producto_id, :tipo, :cantidad, :usuario_id, :motivo)'
            )->execute([
                'producto_id' => $productoId,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'usuario_id' => $usuarioId,
                'motivo' => $motivo,
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function obtenerMovimientos(int $productoId): array
    {
        $pdo = obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT m.*, u.nombre AS usuario_nombre FROM movimientos_inventario m
             JOIN usuarios u ON u.id = m.usuario_id
             WHERE m.producto_id = :producto_id ORDER BY m.creado_en DESC'
        );
        $stmt->execute(['producto_id' => $productoId]);

        return $stmt->fetchAll();
    }
}
