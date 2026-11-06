<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/VarianteProducto.php';
require_once __DIR__ . '/AuthController.php';

// RF3.1-RF3.9: gestion de productos e inventario, restringida a propietario y gerente.
class ProductoController
{
    public static function listar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $productos = Producto::obtenerDisponibles();
        $productosBajoStock = Producto::obtenerConBajoStock(); // RF3.8

        require __DIR__ . '/../views/productos/listar.php';
    }

    public static function mostrarFormularioCrear(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $errores = [];
        require __DIR__ . '/../views/productos/crear.php';
    }

    public static function crear(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
        $categoria = trim($_POST['categoria'] ?? '') ?: null;
        $precio = (float) ($_POST['precio'] ?? 0);
        $stockInicial = (int) ($_POST['stock_inicial'] ?? 0);
        $stockMinimo = (int) ($_POST['stock_minimo'] ?? 0);

        $errores = self::validar($nombre, $precio, $stockInicial, $stockMinimo);

        if (count($errores) === 0) {
            $productoId = Producto::crear($nombre, $descripcion, $categoria, $precio, $stockInicial, $stockMinimo);

            // RF4.3: variantes opcionales ingresadas junto con el producto.
            $variantesNombre = $_POST['variante_nombre'] ?? [];
            $variantesPrecio = $_POST['variante_precio'] ?? [];
            foreach ($variantesNombre as $indice => $nombreVariante) {
                $nombreVariante = trim($nombreVariante);
                if ($nombreVariante !== '') {
                    VarianteProducto::crear($productoId, $nombreVariante, (float) ($variantesPrecio[$indice] ?? 0));
                }
            }

            header('Location: index.php?ruta=productos');
            exit;
        }

        require __DIR__ . '/../views/productos/crear.php';
    }

    public static function mostrarFormularioEditar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $id = (int) ($_GET['id'] ?? 0);
        $producto = Producto::obtenerPorId($id);

        if ($producto === null) {
            header('Location: index.php?ruta=productos');
            exit;
        }

        $variantes = VarianteProducto::obtenerPorProducto($id);
        $movimientos = Producto::obtenerMovimientos($id);
        $errores = [];

        require __DIR__ . '/../views/productos/editar.php';
    }

    public static function editar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
        $categoria = trim($_POST['categoria'] ?? '') ?: null;
        $precio = (float) ($_POST['precio'] ?? 0);
        $stockMinimo = (int) ($_POST['stock_minimo'] ?? 0);

        $errores = self::validar($nombre, $precio, 0, $stockMinimo, validarStockInicial: false);

        if (count($errores) === 0) {
            Producto::actualizar($id, $nombre, $descripcion, $categoria, $precio, $stockMinimo);
            header('Location: index.php?ruta=productos');
            exit;
        }

        $producto = ['id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion, 'categoria' => $categoria, 'precio' => $precio, 'stock_minimo' => $stockMinimo];
        $variantes = VarianteProducto::obtenerPorProducto($id);
        $movimientos = Producto::obtenerMovimientos($id);
        require __DIR__ . '/../views/productos/editar.php';
    }

    public static function desactivar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $id = (int) ($_POST['id'] ?? 0);
        Producto::desactivar($id);

        header('Location: index.php?ruta=productos');
        exit;
    }

    // RF3.6/RF3.7: ingreso o salida manual de existencias.
    public static function registrarMovimiento(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        $cantidad = (int) ($_POST['cantidad'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '') ?: null;

        if (in_array($tipo, ['entrada', 'salida'], true) && $cantidad > 0) {
            try {
                Producto::registrarMovimiento($productoId, $tipo, $cantidad, $sesion['id'], $motivo);
            } catch (RuntimeException $e) {
                header('Location: index.php?ruta=productos_editar&id=' . $productoId . '&error=' . urlencode($e->getMessage()));
                exit;
            }
        }

        header('Location: index.php?ruta=productos_editar&id=' . $productoId);
        exit;
    }

    private static function validar(string $nombre, float $precio, int $stockInicial, int $stockMinimo, bool $validarStockInicial = true): array
    {
        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre del producto es obligatorio.';
        } elseif (Producto::existeNombre($nombre)) {
            $errores[] = 'Ya existe un producto activo con ese nombre.';
        }

        if ($precio <= 0) {
            $errores[] = 'El precio debe ser mayor que cero.';
        }

        if ($validarStockInicial && $stockInicial < 0) {
            $errores[] = 'La cantidad inicial no puede ser negativa.';
        }

        if ($stockMinimo < 0) {
            $errores[] = 'El stock mínimo no puede ser negativo.';
        }

        return $errores;
    }
}
