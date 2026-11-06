<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../models/Usuario.php';

// RF2.1-RF2.6: inicio de sesion, mantenimiento y cierre de sesion.
class AuthController
{
    public static function iniciarSesionSiCorresponde(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // RF2.3/RNF4.4: exige autenticacion y controla expiracion por inactividad (RNF4.5).
    public static function exigirSesion(): array
    {
        self::iniciarSesionSiCorresponde();

        if (empty($_SESSION['usuario_id'])) {
            self::redirigir('index.php?ruta=login');
        }

        $inactividad = time() - ($_SESSION['ultima_actividad'] ?? time());
        if ($inactividad > MINUTOS_EXPIRACION_SESION * 60) {
            self::cerrarSesion();
            self::redirigir('index.php?ruta=login&expirada=1');
        }

        $_SESSION['ultima_actividad'] = time();

        return [
            'id' => (int) $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'rol' => $_SESSION['usuario_rol'],
        ];
    }

    // RF2.3/RF8.7: restringe el acceso a los roles permitidos para una accion.
    public static function exigirRol(array $usuarioSesion, array $rolesPermitidos): void
    {
        if (!in_array($usuarioSesion['rol'], $rolesPermitidos, true)) {
            http_response_code(403);
            echo 'No tiene permisos para acceder a esta sección.';
            exit;
        }
    }

    public static function manejarLogin(): void
    {
        self::iniciarSesionSiCorresponde();

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = trim($_POST['correo'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            // RF2.2: validacion de credenciales contra los datos registrados.
            $usuario = Usuario::verificarCredenciales($correo, $password);

            if ($usuario === null) {
                // RF2.4: no se indica cual credencial fue incorrecta.
                $error = 'Usuario o contraseña incorrectos.';
            } else {
                $_SESSION['usuario_id'] = (int) $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['ultima_actividad'] = time();

                self::redirigir('index.php?ruta=pedidos');
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public static function cerrarSesion(): void
    {
        self::iniciarSesionSiCorresponde();
        $_SESSION = [];
        session_destroy();
    }

    public static function manejarLogout(): void
    {
        self::cerrarSesion();
        self::redirigir('index.php?ruta=login');
    }

    private static function redirigir(string $destino): void
    {
        header("Location: {$destino}");
        exit;
    }
}
