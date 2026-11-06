<?php require __DIR__ . '/../layout/header.php'; ?>

<h2>Editar usuario</h2>

<?php if (!empty($errores)): ?>
    <ul class="mensaje-error">
        <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?ruta=usuarios_editar">
    <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">

    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

    <label for="correo">Correo</label>
    <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>

    <label for="password">Nueva contraseña (opcional)</label>
    <input type="password" id="password" name="password" minlength="8">

    <label for="rol">Rol</label>
    <select id="rol" name="rol" required>
        <?php foreach (['propietario', 'gerente', 'cajero'] as $rol): ?>
            <option value="<?= $rol ?>" <?= $usuario['rol'] === $rol ? 'selected' : '' ?>><?= ucfirst($rol) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Guardar cambios</button>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
