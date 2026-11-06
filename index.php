<?php
// Front controller: enruta cada solicitud al controlador correspondiente,
// igual que el "index.php ... enruta las solicitudes" descrito en el manual de programador.

require_once __DIR__ . '/config/constantes.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/ProductoController.php';
require_once __DIR__ . '/controllers/PedidoController.php';

$ruta = $_GET['ruta'] ?? 'pedidos';
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($ruta) {
    case 'login':
        AuthController::manejarLogin();
        break;

    case 'logout':
        AuthController::manejarLogout();
        break;

    // ---- Usuarios (RF1.x) ----
    case 'usuarios':
        UsuarioController::listar();
        break;
    case 'usuarios_crear':
        $metodo === 'POST' ? UsuarioController::crear() : UsuarioController::mostrarFormularioCrear();
        break;
    case 'usuarios_editar':
        $metodo === 'POST' ? UsuarioController::editar() : UsuarioController::mostrarFormularioEditar();
        break;
    case 'usuarios_desactivar':
        UsuarioController::desactivar();
        break;
    case 'usuarios_activar':
        UsuarioController::activar();
        break;

    // ---- Inventario (RF3.x) ----
    case 'productos':
        ProductoController::listar();
        break;
    case 'productos_crear':
        $metodo === 'POST' ? ProductoController::crear() : ProductoController::mostrarFormularioCrear();
        break;
    case 'productos_editar':
        $metodo === 'POST' ? ProductoController::editar() : ProductoController::mostrarFormularioEditar();
        break;
    case 'productos_desactivar':
        ProductoController::desactivar();
        break;
    case 'productos_movimiento':
        ProductoController::registrarMovimiento();
        break;

    // ---- Pedidos (RF4.x) ----
    case 'pedidos':
        PedidoController::listar();
        break;
    case 'pedido_crear':
        PedidoController::crear();
        break;
    case 'pedido_detalle':
        PedidoController::detalle();
        break;
    case 'pedido_agregar_producto':
        PedidoController::agregarProducto();
        break;
    case 'pedido_actualizar_cantidad':
        PedidoController::actualizarCantidad();
        break;
    case 'pedido_eliminar_linea':
        PedidoController::eliminarLinea();
        break;
    case 'pedido_confirmar':
        PedidoController::confirmar();
        break;
    case 'pedido_cancelar':
        PedidoController::cancelar();
        break;
    case 'pedido_entregado':
        PedidoController::marcarEntregado();
        break;
    case 'cocina':
        PedidoController::vistaCocina();
        break;
    case 'pedido_en_preparacion':
        PedidoController::marcarEnPreparacion();
        break;
    case 'pedido_listo':
        PedidoController::marcarListo();
        break;

    default:
        http_response_code(404);
        echo 'Página no encontrada.';
}
