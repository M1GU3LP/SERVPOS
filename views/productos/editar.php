<?php require __DIR__ . '/../layout/header.php'; ?>

<h2>Editar producto</h2>

<?php if (!empty($_GET['error'])): ?>
    <p class="mensaje-error"><?= htmlspecialchars($_GET['error']) ?></p>
<?php endif; ?>

<?php if (!empty($errores)): ?>
    <ul class="mensaje-error">
        <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?ruta=productos_editar">
    <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">

    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>

    <label for="descripcion">Descripción</label>
    <input type="text" id="descripcion" name="descripcion" value="<?= htmlspecialchars($producto['descripcion'] ?? '') ?>">

    <label for="categoria">Categoría</label>
    <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($producto['categoria'] ?? '') ?>">

    <label for="precio">Precio</label>
    <input type="number" id="precio" name="precio" step="0.01" min="0.01" value="<?= htmlspecialchars((string) $producto['precio']) ?>" required>

    <label for="stock_minimo">Stock mínimo</label>
    <input type="number" id="stock_minimo" name="stock_minimo" min="0" value="<?= (int) $producto['stock_minimo'] ?>" required>

    <button type="submit">Guardar cambios</button>
</form>

<h3>Existencias</h3>
<p>Stock actual: <strong><?= (int) ($producto['stock_actual'] ?? 0) ?></strong></p>

<!-- RF3.6/RF3.7: ingreso o salida manual de productos del inventario -->
<form method="post" action="index.php?ruta=productos_movimiento" class="formulario-movimiento">
    <input type="hidden" name="producto_id" value="<?= (int) $producto['id'] ?>">

    <label for="tipo">Tipo de movimiento</label>
    <select id="tipo" name="tipo" required>
        <option value="entrada">Entrada</option>
        <option value="salida">Salida</option>
    </select>

    <label for="cantidad">Cantidad</label>
    <input type="number" id="cantidad" name="cantidad" min="1" required>

    <label for="motivo">Motivo</label>
    <input type="text" id="motivo" name="motivo" placeholder="Ej. Compra a proveedor">

    <button type="submit">Registrar movimiento</button>
</form>

<?php if (!empty($variantes)): ?>
    <h3>Tamaños / variantes</h3>
    <ul>
        <?php foreach ($variantes as $variante): ?>
            <li><?= htmlspecialchars($variante['nombre']) ?> (+$<?= number_format((float) $variante['precio_adicional'], 0, ',', '.') ?>)</li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (!empty($movimientos)): ?>
    <h3>Historial de movimientos</h3>
    <table class="tabla">
        <thead>
            <tr><th>Fecha</th><th>Tipo</th><th>Cantidad</th><th>Usuario</th><th>Motivo</th></tr>
        </thead>
        <tbody>
            <?php foreach ($movimientos as $movimiento): ?>
                <tr>
                    <td><?= htmlspecialchars($movimiento['creado_en']) ?></td>
                    <td><?= htmlspecialchars($movimiento['tipo']) ?></td>
                    <td><?= (int) $movimiento['cantidad'] ?></td>
                    <td><?= htmlspecialchars($movimiento['usuario_nombre']) ?></td>
                    <td><?= htmlspecialchars($movimiento['motivo'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
