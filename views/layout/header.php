<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SERVPOS - <?= htmlspecialchars(NOMBRE_EMPRESA) ?></title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <header class="encabezado">
        <!-- RNF3.6: logo de la empresa en la parte superior izquierda -->
        <div class="encabezado-izquierda">
            <img src="public/img/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'">
            <span class="nombre-empresa">SERVPOS</span>
        </div>

        <nav class="menu-principal">
            <a href="index.php?ruta=pedidos">Pedidos</a>
            <a href="index.php?ruta=cocina">Cocina</a>
            <?php if (in_array($sesion['rol'] ?? '', ROLES_ADMINISTRACION, true)): ?>
                <a href="index.php?ruta=productos">Inventario</a>
                <a href="index.php?ruta=usuarios">Usuarios</a>
            <?php endif; ?>
        </nav>

        <!-- RNF3.5: nombre de usuario y rol en la parte superior derecha -->
        <div class="encabezado-derecha">
            <?php if (!empty($sesion)): ?>
                <span class="usuario-actual"><?= htmlspecialchars($sesion['nombre']) ?> (<?= htmlspecialchars($sesion['rol']) ?>)</span>
                <form method="post" action="index.php?ruta=logout" class="inline">
                    <button type="submit" class="boton-enlace">Cerrar sesión</button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <main class="contenido">
