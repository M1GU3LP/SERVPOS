<?php require __DIR__ . '/../layout/header.php'; ?>

<h2>Registrar nuevo producto</h2>

<?php if (!empty($errores)): ?>
    <ul class="mensaje-error">
        <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="index.php?ruta=productos_crear">
    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>

    <label for="descripcion">Descripción</label>
    <input type="text" id="descripcion" name="descripcion" value="<?= htmlspecialchars($_POST['descripcion'] ?? '') ?>">

    <label for="categoria">Categoría</label>
    <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($_POST['categoria'] ?? '') ?>">

    <label for="precio">Precio</label>
    <input type="number" id="precio" name="precio" step="0.01" min="0.01" required>

    <label for="stock_inicial">Cantidad inicial</label>
    <input type="number" id="stock_inicial" name="stock_inicial" min="0" value="0" required>

    <label for="stock_minimo">Stock mínimo (para alertas)</label>
    <input type="number" id="stock_minimo" name="stock_minimo" min="0" value="5" required>

    <fieldset>
        <legend>Tamaños o variantes (opcional)</legend>
        <div class="fila-variante">
            <input type="text" name="variante_nombre[]" placeholder="Nombre (ej. Grande)">
            <input type="number" name="variante_precio[]" step="0.01" placeholder="Precio adicional" value="0">
        </div>
        <div class="fila-variante">
            <input type="text" name="variante_nombre[]" placeholder="Nombre (ej. Pequeña)">
            <input type="number" name="variante_precio[]" step="0.01" placeholder="Precio adicional" value="0">
        </div>
    </fieldset>

    <button type="submit">Guardar</button>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
