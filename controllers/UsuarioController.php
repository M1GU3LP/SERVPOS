<?php
require_once __DIR__ . '/../config/constantes.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/AuthController.php';

// RF1.1-RF1.7: gestion de usuarios, restringida a propietario y gerente.
class UsuarioController
{
    public static function listar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $usuarios = Usuario::obtenerTodos();
        require __DIR__ . '/../views/usuarios/listar.php';
    }

    public static function mostrarFormularioCrear(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $errores = [];
        require __DIR__ . '/../views/usuarios/crear.php';
    }

    public static function crear(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $rol = $_POST['rol'] ?? '';

        $errores = self::validar($nombre, $correo, $password, $rol, esEdicion: false);

        if (count($errores) === 0) {
            Usuario::crear($nombre, $correo, $password, $rol);
            header('Location: index.php?ruta=usuarios');
            exit;
        }

        require __DIR__ . '/../views/usuarios/crear.php';
    }

    public static function mostrarFormularioEditar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $id = (int) ($_GET['id'] ?? 0);
        $usuario = Usuario::obtenerPorId($id);

        if ($usuario === null) {
            header('Location: index.php?ruta=usuarios');
            exit;
        }

        $errores = [];
        require __DIR__ . '/../views/usuarios/editar.php';
    }

    public static function editar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $rol = $_POST['rol'] ?? '';

        $errores = self::validar($nombre, $correo, null, $rol, esEdicion: true, idActual: $id);

        if (count($errores) === 0) {
            Usuario::actualizar($id, $nombre, $correo, $rol);

            $nuevaPassword = trim($_POST['password'] ?? '');
            if ($nuevaPassword !== '') {
                Usuario::actualizarPassword($id, $nuevaPassword);
            }

            header('Location: index.php?ruta=usuarios');
            exit;
        }

        $usuario = ['id' => $id, 'nombre' => $nombre, 'correo' => $correo, 'rol' => $rol];
        require __DIR__ . '/../views/usuarios/editar.php';
    }

    // RF1.6: desactivar en lugar de eliminar, conservando el historial de operaciones.
    public static function desactivar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $id = (int) ($_POST['id'] ?? 0);
        Usuario::establecerActivo($id, false);

        header('Location: index.php?ruta=usuarios');
        exit;
    }

    public static function activar(): void
    {
        $sesion = AuthController::exigirSesion();
        AuthController::exigirRol($sesion, ROLES_ADMINISTRACION);

        $id = (int) ($_POST['id'] ?? 0);
        Usuario::establecerActivo($id, true);

        header('Location: index.php?ruta=usuarios');
        exit;
    }

    // RF1.7: validacion de los datos obligatorios del usuario.
    private static function validar(string $nombre, string $correo, ?string $password, string $rol, bool $esEdicion, int $idActual = 0): array
    {
        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo es obligatorio y debe tener un formato válido.';
        } else {
            $existente = Usuario::obtenerPorCorreo($correo);
            if ($existente !== null && (int) $existente['id'] !== $idActual) {
                $errores[] = 'Ya existe un usuario registrado con ese correo.';
            }
        }

        if (!$esEdicion && ($password === null || strlen($password) < 8)) {
            $errores[] = 'La contraseña es obligatoria y debe tener al menos 8 caracteres.';
        }

        if (!in_array($rol, ROLES_TODOS, true)) {
            $errores[] = 'Debe seleccionar un rol válido.';
        }

        return $errores;
    }
}
