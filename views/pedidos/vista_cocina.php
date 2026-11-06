<?php require __DIR__ . '/../layout/header.php'; ?>

<h2>Vista de cocina</h2>
<p>Pedidos confirmados pendientes de preparación (RF4.9/RF4.10).</p>

<?php foreach ($pedidos as $pedidoCocina): ?>
    <div class="tarjeta-pedido-cocina">
        <h3>#<?= (int) $pedidoCocina['id'] ?> — <?= htmlspecialchars($pedidoCocina['mesa_o_cliente']) ?></h3>
        <p>Estado: <strong><?= htmlspecialchars($pedidoCocina['estado']) ?></strong></p>
        <ul>
            <?php foreach ($pedidoCocina['lineas'] as $linea): ?>
                <li><?= (int) $linea['cantidad'] ?> × <?= htmlspecialchars($linea['producto_nombre']) ?>
                    <?= $linea['variante_nombre'] ? '(' . htmlspecialchars($linea['variante_nombre']) . ')' : '' ?></li>
            <?php endforeach; ?>
        </ul>

        <?php if ($pedidoCocina['estado'] === 'pendiente'): ?>
            <form method="post" action="index.php?ruta=pedido_en_preparacion" class="inline">
                <input type="hidden" name="pedido_id" value="<?= (int) $pedidoCocina['id'] ?>">
                <button type="submit" class="boton">Empezar a preparar</button>
            </form>
        <?php elseif ($pedidoCocina['estado'] === 'en_preparacion'): ?>
            <form method="post" action="index.php?ruta=pedido_listo" class="inline">
                <input type="hidden" name="pedido_id" value="<?= (int) $pedidoCocina['id'] ?>">
                <button type="submit" class="boton">Marcar como listo</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if (empty($pedidos)): ?>
    <p>No hay pedidos pendientes en este momento.</p>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
