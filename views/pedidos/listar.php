<?php require __DIR__ . '/../layout/header.php'; ?>

<h2>Pedidos</h2>

<?php if (!empty($_GET['error'])): ?>
    <p class="mensaje-error"><?= htmlspecialchars($_GET['error']) ?></p>
<?php endif; ?>

<!-- RF4.1/RF4.7: crear un nuevo pedido asociado a una mesa o cliente -->
<form method="post" action="index.php?ruta=pedido_crear" class="formulario-nuevo-pedido">
    <label for="mesa_o_cliente">Mesa o cliente</label>
    <input type="text" id="mesa_o_cliente" name="mesa_o_cliente" placeholder="Ej. Mesa 4 / Juan Pérez" required>
    <button type="submit">Nuevo pedido</button>
</form>

<table class="tabla">
    <thead>
        <tr>
            <th>#</th>
            <th>Mesa / Cliente</th>
            <th>Cajero</th>
            <th>Estado</th>
            <th>Total</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?= (int) $pedido['id'] ?></td>
                <td><?= htmlspecialchars($pedido['mesa_o_cliente']) ?></td>
                <td><?= htmlspecialchars($pedido['cajero_nombre']) ?></td>
                <td><?= htmlspecialchars($pedido['estado']) ?></td>
                <td>$<?= number_format((float) $pedido['total'], 0, ',', '.') ?></td>
                <td>
                    <?php if ($pedido['estado'] === 'borrador'): ?>
                        <a href="index.php?ruta=pedido_detalle&id=<?= (int) $pedido['id'] ?>">Continuar</a>
                    <?php elseif ($pedido['estado'] === 'listo'): ?>
                        <form method="post" action="index.php?ruta=pedido_entregado" class="inline">
                            <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                            <button type="submit" class="boton-enlace">Marcar entregado</button>
                        </form>
                    <?php else: ?>
                        <span>En cocina</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>
