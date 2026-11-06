<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/DetallePedido.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/VarianteProducto.php';
require_once __DIR__ . '/AuthController.php';

// RF4.1-RF4.10: toma de pedidos, disponible para los tres roles (el cajero es quien mas lo usa).
class PedidoController
{
    public static function listar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $pedidos = Pedido::obtenerActivos();
        require __DIR__ . '/../views/pedidos/listar.php';
    }

    // RF4.1: crear un nuevo pedido.
    public static function crear(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $mesaOCliente = trim($_POST['mesa_o_cliente'] ?? '');

        if ($mesaOCliente === '') {
            header('Location: index.php?ruta=pedidos&error=' . urlencode('Debe indicar la mesa o el cliente.'));
            exit;
        }

        $pedidoId = Pedido::crear($mesaOCliente, $sesion['id']);
        header('Location: index.php?ruta=pedido_detalle&id=' . $pedidoId);
        exit;
    }

    // RF4.2-RF4.10: pantalla de detalle para armar y confirmar el pedido.
    public static function detalle(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $id = (int) ($_GET['id'] ?? 0);
        $pedido = Pedido::obtenerPorId($id);

        if ($pedido === null) {
            header('Location: index.php?ruta=pedidos');
            exit;
        }

        $lineas = DetallePedido::obtenerPorPedido($id);
        $productosDisponibles = Producto::obtenerDisponibles();

        // RF4.3: variantes disponibles por producto, usadas por el selector dinámico en la vista.
        $variantesPorProducto = [];
        foreach ($productosDisponibles as $producto) {
            $variantesPorProducto[$producto['id']] = VarianteProducto::obtenerPorProducto((int) $producto['id']);
        }

        $error = $_GET['error'] ?? null;

        require __DIR__ . '/../views/pedidos/detalle.php';
    }

    // RF4.2/RF4.3: agregar un producto (con variante opcional) al pedido.
    public static function agregarProducto(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $varianteId = !empty($_POST['variante_id']) ? (int) $_POST['variante_id'] : null;
        $cantidad = (int) ($_POST['cantidad'] ?? 0);

        self::exigirPedidoEditable($pedidoId);

        $producto = Producto::obtenerPorId($productoId);

        if ($producto === null || $cantidad <= 0) {
            self::redirigirADetalle($pedidoId, 'Seleccione un producto válido y una cantidad mayor a cero.');
        }

        $precioUnitario = (float) $producto['precio'];
        if ($varianteId !== null) {
            $variante = VarianteProducto::obtenerPorId($varianteId);
            if ($variante !== null) {
                $precioUnitario += (float) $variante['precio_adicional'];
            }
        }

        DetallePedido::agregar($pedidoId, $productoId, $varianteId, $cantidad, $precioUnitario);
        Pedido::recalcularTotal($pedidoId); // RF4.6

        self::redirigirADetalle($pedidoId);
    }

    // RF4.4: modificar la cantidad de una linea antes de confirmar.
    public static function actualizarCantidad(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $detalleId = (int) ($_POST['detalle_id'] ?? 0);
        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        $cantidad = (int) ($_POST['cantidad'] ?? 0);

        self::exigirPedidoEditable($pedidoId);

        if ($cantidad > 0) {
            DetallePedido::actualizarCantidad($detalleId, $cantidad);
            Pedido::recalcularTotal($pedidoId);
        }

        self::redirigirADetalle($pedidoId);
    }

    // RF4.5: eliminar un producto del pedido antes de confirmarlo.
    public static function eliminarLinea(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $detalleId = (int) ($_POST['detalle_id'] ?? 0);
        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);

        self::exigirPedidoEditable($pedidoId);

        DetallePedido::eliminar($detalleId);
        Pedido::recalcularTotal($pedidoId);

        self::redirigirADetalle($pedidoId);
    }

    // RF4.8/RF4.9: confirmar el pedido, descontar inventario y enviarlo a cocina.
    public static function confirmar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);

        try {
            Pedido::confirmar($pedidoId, $sesion['id']);
            header('Location: index.php?ruta=pedidos');
            exit;
        } catch (RuntimeException $e) {
            self::redirigirADetalle($pedidoId, $e->getMessage());
        }
    }

    public static function cancelar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        Pedido::cancelar($pedidoId);

        header('Location: index.php?ruta=pedidos');
        exit;
    }

    // RF4.9/RF4.10: vista simple del area de cocina con los pedidos pendientes.
    public static function vistaCocina(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $pedidos = Pedido::obtenerParaCocina();

        foreach ($pedidos as &$pedido) {
            $pedido['lineas'] = DetallePedido::obtenerPorPedido((int) $pedido['id']);
        }
        unset($pedido);

        require __DIR__ . '/../views/pedidos/vista_cocina.php';
    }

    public static function marcarEnPreparacion(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        Pedido::cambiarEstado($pedidoId, 'en_preparacion');

        header('Location: index.php?ruta=cocina');
        exit;
    }

    public static function marcarListo(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        Pedido::cambiarEstado($pedidoId, 'listo'); // RF7.4 (notificacion simple vista en la mesa)

        header('Location: index.php?ruta=cocina');
        exit;
    }

    // Cierra el ciclo del pedido una vez entregado en mesa (fuera de alcance: cobro/pago).
    public static function marcarEntregado(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_TODOS);

        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        Pedido::cambiarEstado($pedidoId, 'confirmado');

        header('Location: index.php?ruta=pedidos');
        exit;
    }

    private static function exigirPedidoEditable(int $pedidoId): void
    {
        $pedido = Pedido::obtenerPorId($pedidoId);

        if ($pedido === null || $pedido['estado'] !== 'borrador') {
            self::redirigirADetalle($pedidoId, 'El pedido ya fue confirmado y no se puede modificar.');
        }
    }

    private static function redirigirADetalle(int $pedidoId, ?string $error = null): void
    {
        $destino = 'index.php?ruta=pedido_detalle&id=' . $pedidoId;
        if ($error !== null) {
            $destino .= '&error=' . urlencode($error);
        }
        header("Location: {$destino}");
        exit;
    }
}
