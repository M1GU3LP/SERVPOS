# SERVPOS — Manual de programador

Sistema de punto de venta para **San Juan Comidas Rápidas**. Este manual sigue la misma estructura que el manual de referencia entregado por el cliente (*Manual de programador - ReguiQuick Bike Store*), adaptado a los módulos reales de SERVPOS: **Usuarios y roles, Inventario y Pedidos** (MVP correspondiente a DRF1–DRF4 del documento de requerimientos).
URL repositorio: https://github.com/M1GU3LP/SERVPOS.git

## 1. Introducción

Para los futuros programadores que trabajen en SERVPOS —una aplicación web para gestionar usuarios, inventario y pedidos de un restaurante de comida rápida— este documento sirve de guía técnica. Cubre la estructura, los módulos implementados, las reglas de negocio aplicadas y las pautas de instalación y despliegue.

Pagos, informes de ventas/ganancias, comunicación dedicada cocina-mesas y administración remota (DRF5–DRF8 del documento de requerimientos) **no están implementados en esta versión** y quedan como trabajo futuro.

## 2. Entorno de desarrollo

- **Servidor local:** XAMPP o Laragon (Apache + MySQL).
- **PHP:** versión 8.0 o superior. No se usa ningún framework; el enrutamiento, la seguridad de sesión y el acceso a datos están escritos a mano.
- **MySQL:** versión 5.7 o superior, motor InnoDB (soporta transacciones y llaves foráneas).
- **Editor recomendado:** Visual Studio Code con extensiones de PHP.
- **Navegador:** cualquiera (RNF5.5) — se probó en Chrome/Edge.
- **Git:** control de versiones del código fuente.

### Instalación del entorno

1. Instalar XAMPP y copiar la carpeta `SERVPOS/` dentro de `C:/xampp/htdocs/` (o la ruta equivalente de tu servidor).
2. Iniciar los módulos Apache y MySQL desde el panel de XAMPP.
3. Abrir `http://localhost/phpmyadmin`, crear la base de datos (o dejar que el script la cree) e importar `database/servpos_db.sql`.
4. Si tu usuario/contraseña de MySQL no son los de XAMPP por defecto (`root` sin contraseña), configura variables de entorno antes de iniciar Apache: `SERVPOS_DB_HOST`, `SERVPOS_DB_PORT`, `SERVPOS_DB_NAME`, `SERVPOS_DB_USER`, `SERVPOS_DB_PASS` (ver `config/db.php`).
5. Abrir `http://localhost/SERVPOS/index.php` — debe aparecer la pantalla de inicio de sesión (RNF3.8).
6. Iniciar sesión con el usuario semilla: **propietario@sanjuancomidas.com / Propietario123**.

También puede levantarse sin XAMPP con el servidor embebido de PHP, estando dentro de la carpeta `SERVPOS/`:

```
php -S localhost:8000
```

## 3. Estructura del proyecto

Patrón MVC manual (Modelo-Vista-Controlador), igual que el proyecto de referencia:

```
SERVPOS/
├── config/        # Conexión PDO (db.php) y constantes de roles/empresa (constantes.php)
├── controllers/   # Logica de control: AuthController, UsuarioController, ProductoController, PedidoController
├── models/        # Clases de datos con PDO y prepared statements: Usuario, Producto, VarianteProducto,
│                  #  MovimientoInventario, Pedido, DetallePedido
├── views/         # Plantillas HTML + PHP, organizadas por módulo (auth/, usuarios/, productos/, pedidos/, layout/)
├── public/        # CSS, JS e imágenes servidas al navegador
├── database/      # Script SQL de creación + datos semilla
└── index.php      # Front controller: enruta cada solicitud (?ruta=...) al controlador correspondiente
```

Cada módulo (usuarios, productos, pedidos) tiene su propio controlador, su(s) modelo(s) y sus vistas, igual que en el manual de referencia.

## 4. Ejemplo de controlador (PHP)

Archivo: `controllers/ProductoController.php` (fragmento)

```php
public static function registrarMovimiento(): void
{
    $sesion = AuthController::exigirSesion();
    AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

    $productoId = (int) ($_POST['producto_id'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $cantidad = (int) ($_POST['cantidad'] ?? 0);

    if (in_array($tipo, ['entrada', 'salida'], true) && $cantidad > 0) {
        Producto::registrarMovimiento($productoId, $tipo, $cantidad, $sesion['id'], $motivo);
    }

    header('Location: index.php?ruta=productos_editar&id=' . $productoId);
    exit;
}
```

## 5. Ejemplo de modelo (PHP)

Archivo: `models/Producto.php` (fragmento) — a diferencia del ejemplo de referencia, aquí se usa **PDO con parámetros bindeados** (nunca concatenación de strings) para evitar inyección SQL, y la actualización de stock ocurre dentro de una transacción:

```php
public static function registrarMovimiento(int $productoId, string $tipo, int $cantidad, int $usuarioId, ?string $motivo): void
{
    $pdo = obtenerConexion();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT stock_actual FROM productos WHERE id = :id FOR UPDATE');
    $stmt->execute(['id' => $productoId]);
    // ... valida stock, actualiza y registra el movimiento, o hace rollback si falla
}
```

## 6. Base de datos

Ver `database/servpos_db.sql` para el script completo. Tablas principales: `usuarios`, `productos`, `variantes_producto`, `movimientos_inventario`, `pedidos`, `detalle_pedido`. Las contraseñas se almacenan con `password_hash()` (bcrypt), nunca en texto plano (RF1.3, RNF4.4).

## 7. Reglas de negocio implementadas

- **Roles (RF1.2, RF2.3):** propietario, gerente y cajero. Solo propietario/gerente administran usuarios e inventario; los tres roles pueden tomar pedidos.
- **Sesión (RNF4.4, RNF4.5):** autenticación por usuario/contraseña; cierre automático tras 5 minutos de inactividad.
- **Inventario (RF3.5, RF3.8):** el stock nunca queda negativo; se identifican automáticamente los productos por debajo de su stock mínimo.
- **Pedidos (RF4.6, RF4.8):** el total se calcula siempre en el servidor; un pedido no puede confirmarse vacío ni modificarse después de confirmado. Al confirmar, se valida y descuenta stock dentro de una única transacción para evitar inconsistencias.

## 8. Pruebas

Áreas críticas a probar antes de publicar cambios:

- **Usuarios:** registro, roles, desactivación, que un usuario inactivo no pueda iniciar sesión.
- **Sesión:** expiración tras 5 minutos de inactividad, control de acceso por rol (un cajero no debe poder editar productos).
- **Inventario:** que el stock nunca quede negativo, que las alertas de stock bajo aparezcan correctamente.
- **Pedidos:** cálculo correcto del total con variantes, que no se pueda confirmar un pedido vacío, que confirmar descuente el stock exacto y lo refleje en la vista de cocina.

Herramientas sugeridas: DevTools del navegador, phpMyAdmin para inspeccionar datos, PHPUnit si se agregan pruebas automatizadas a los modelos.

## 9. Despliegue

1. Exportar `servpos_db.sql` actualizado desde el entorno local (phpMyAdmin → Exportar).
2. Subir los archivos del proyecto al servidor (carpeta pública del hosting).
3. Configurar las variables de entorno de conexión a base de datos en el servidor de producción (no reutilizar las credenciales de desarrollo).
4. Verificar permisos de escritura si se agregan subidas de archivos en una iteración futura.
5. Probar el flujo completo (login → inventario → pedido → confirmación) en producción antes de entregar al cliente.

## 10. Recomendaciones finales

- **Control de versiones:** Git con ramas `main`, `develop`, `feature/*`.
- **Seguridad:** todas las consultas usan PDO con parámetros bindeados; las contraseñas siempre van hasheadas. No reintroducir concatenación de SQL al extender el sistema.
- **Próximos módulos:** Pagos (DRF6), Informe de ventas/ganancias (DRF5), Comunicación cocina-mesas en tiempo real (DRF7) y Administración remota (DRF8), siguiendo el mismo patrón MVC de este proyecto.
- **Respaldos:** programar copias de seguridad periódicas de la base de datos (RNF4.1/RNF4.2).
