<?php require __DIR__ . '/../layout/header.php'; ?>

<h2>Inventario de productos</h2>
<a class="boton" href="index.php?ruta=productos_crear">Registrar nuevo producto</a>

<?php if (!empty($productosBajoStock)): ?>
    <!-- RF3.8: identificacion de productos con existencias bajas -->
    <div class="mensaje-alerta">
        <strong>Productos con stock bajo:</strong>
        <ul>
            <?php foreach ($productosBajoStock as $producto): ?>
                <li><?= htmlspecialchars($producto['nombre']) ?> — quedan <?= (int) $producto['stock_actual'] ?> (mínimo <?= (int) $producto['stock_minimo'] ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<table class="tabla">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Precio</th>
            <th>Stock actual</th>
            <th>Stock mínimo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productos as $producto): ?>
            <tr class="<?= $producto['stock_actual'] < $producto['stock_minimo'] ? 'fila-alerta' : '' ?>">
                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                <td><?= htmlspecialchars($producto['categoria'] ?? '') ?></td>
                <td>$<?= number_format((float) $producto['precio'], 0, ',', '.') ?></td>
                <td><?= (int) $producto['stock_actual'] ?></td>
                <td><?= (int) $producto['stock_minimo'] ?></td>
                <td>
                    <a href="index.php?ruta=productos_editar&id=<?= (int) $producto['id'] ?>">Editar</a>
                    <form method="post" action="index.php?ruta=productos_desactivar" class="inline">
                        <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">
                        <button type="submit" class="boton-enlace">Desactivar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>
