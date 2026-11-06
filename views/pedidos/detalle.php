<?php require __DIR__ . '/../layout/header.php'; ?>

<h2>Pedido #<?= (int) $pedido['id'] ?> — <?= htmlspecialchars($pedido['mesa_o_cliente']) ?></h2>
<p>Estado: <strong><?= htmlspecialchars($pedido['estado']) ?></strong></p>

<?php if (!empty($error)): ?>
    <p class="mensaje-error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($pedido['estado'] === 'borrador'): ?>
    <!-- RF4.2/RF4.3: agregar productos disponibles, con tamaño/variante opcional -->
    <form method="post" action="index.php?ruta=pedido_agregar_producto" class="formulario-agregar-producto">
        <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">

        <label for="producto_id">Producto</label>
        <select id="producto_id" name="producto_id" required>
            <?php foreach ($productosDisponibles as $producto): ?>
                <option value="<?= (int) $producto['id'] ?>">
                    <?= htmlspecialchars($producto['nombre']) ?> — $<?= number_format((float) $producto['precio'], 0, ',', '.') ?>
                    (stock: <?= (int) $producto['stock_actual'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="variante_id">Tamaño / variante (opcional)</label>
        <select id="variante_id" name="variante_id">
            <option value="">Sin variante</option>
        </select>

        <label for="cantidad">Cantidad</label>
        <input type="number" id="cantidad" name="cantidad" min="1" value="1" required>

        <button type="submit">Agregar al pedido</button>
    </form>
<?php endif; ?>

<table class="tabla">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Variante</th>
            <th>Cantidad</th>
            <th>Precio unitario</th>
            <th>Subtotal</th>
            <?php if ($pedido['estado'] === 'borrador'): ?><th>Acciones</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lineas as $linea): ?>
            <tr>
                <td><?= htmlspecialchars($linea['producto_nombre']) ?></td>
                <td><?= htmlspecialchars($linea['variante_nombre'] ?? '') ?></td>
                <td><?= (int) $linea['cantidad'] ?></td>
                <td>$<?= number_format((float) $linea['precio_unitario'], 0, ',', '.') ?></td>
                <td>$<?= number_format((float) $linea['subtotal'], 0, ',', '.') ?></td>
                <?php if ($pedido['estado'] === 'borrador'): ?>
                <td>
                    <!-- RF4.4: modificar cantidad -->
                    <form method="post" action="index.php?ruta=pedido_actualizar_cantidad" class="inline">
                        <input type="hidden" name="detalle_id" value="<?= (int) $linea['id'] ?>">
                        <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                        <input type="number" name="cantidad" min="1" value="<?= (int) $linea['cantidad'] ?>" class="cantidad-corta">
                        <button type="submit" class="boton-enlace">Actualizar</button>
                    </form>
                    <!-- RF4.5: eliminar producto del pedido -->
                    <form method="post" action="index.php?ruta=pedido_eliminar_linea" class="inline">
                        <input type="hidden" name="detalle_id" value="<?= (int) $linea['id'] ?>">
                        <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                        <button type="submit" class="boton-enlace">Eliminar</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- RF4.6: total calculado automaticamente en el servidor -->
<h3>Total: $<?= number_format((float) $pedido['total'], 0, ',', '.') ?></h3>

<?php if ($pedido['estado'] === 'borrador'): ?>
    <!-- RF4.8: confirmar y registrar el pedido -->
    <form method="post" action="index.php?ruta=pedido_confirmar" class="inline">
        <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
        <button type="submit" class="boton">Confirmar pedido</button>
    </form>
    <form method="post" action="index.php?ruta=pedido_cancelar" class="inline">
        <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
        <button type="submit" class="boton-enlace">Cancelar pedido</button>
    </form>
<?php endif; ?>

<a href="index.php?ruta=pedidos">Volver a pedidos</a>

<script>
    // RF4.3: opciones de variante segun el producto seleccionado (llenadas desde PHP, sin AJAX).
    const variantesPorProducto = <?= json_encode($variantesPorProducto ?? [], JSON_FORCE_OBJECT) ?>;
</script>
<script src="public/js/pedido.js"></script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
