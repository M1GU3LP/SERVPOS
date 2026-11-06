<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Iniciar sesión - SERVPOS</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="pagina-login">
    <div class="tarjeta-login">
        <img src="public/img/logo.png" alt="Logo" class="logo-login" onerror="this.style.display='none'">
        <h1>SERVPOS</h1>
        <!-- RNF3.8: mensaje de bienvenida al usuario -->
        <p class="bienvenida">Bienvenido a SERVPOS, el punto de venta de <?= htmlspecialchars(NOMBRE_EMPRESA) ?>.</p>

        <?php if (!empty($_GET['expirada'])): ?>
            <p class="mensaje-aviso">Su sesión expiró por inactividad. Ingrese nuevamente.</p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="index.php?ruta=login">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" required autofocus>

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>
