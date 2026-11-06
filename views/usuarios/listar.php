<?php require __DIR__ . '/../layout/header.php'; ?>

<h2>Usuarios</h2>
<a class="boton" href="index.php?ruta=usuarios_crear">Registrar nuevo usuario</a>

<table class="tabla">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                <td><?= htmlspecialchars($usuario['correo']) ?></td>
                <td><?= htmlspecialchars($usuario['rol']) ?></td>
                <td><?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?></td>
                <td>
                    <a href="index.php?ruta=usuarios_editar&id=<?= (int) $usuario['id'] ?>">Editar</a>
                    <?php if ($usuario['activo']): ?>
                        <form method="post" action="index.php?ruta=usuarios_desactivar" class="inline">
                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                            <button type="submit" class="boton-enlace">Desactivar</button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="index.php?ruta=usuarios_activar" class="inline">
                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                            <button type="submit" class="boton-enlace">Activar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>
