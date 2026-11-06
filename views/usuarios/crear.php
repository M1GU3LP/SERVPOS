<?php require __DIR__ . '/../layout/header.php'; ?>

<h2>Registrar nuevo usuario</h2>

<?php if (!empty($errores)): ?>
    <ul class="mensaje-error">
        <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?ruta=usuarios_crear">
    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>

    <label for="correo">Correo</label>
    <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>

    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" required minlength="8">

    <label for="rol">Rol</label>
    <select id="rol" name="rol" required>
        <option value="">Seleccione un rol</option>
        <option value="propietario">Propietario</option>
        <option value="gerente">Gerente</option>
        <option value="cajero">Cajero</option>
    </select>

    <button type="submit">Guardar</button>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
