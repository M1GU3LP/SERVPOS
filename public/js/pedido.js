// RF4.3: al cambiar el producto seleccionado, se recargan sus tamaños/variantes disponibles.
document.addEventListener('DOMContentLoaded', function () {
    const selectProducto = document.getElementById('producto_id');
    const selectVariante = document.getElementById('variante_id');

    if (!selectProducto || !selectVariante || typeof variantesPorProducto === 'undefined') {
        return;
    }

    function actualizarVariantes() {
        const variantes = variantesPorProducto[selectProducto.value] || [];

        selectVariante.innerHTML = '<option value="">Sin variante</option>';

        variantes.forEach(function (variante) {
            const opcion = document.createElement('option');
            opcion.value = variante.id;
            opcion.textContent = variante.nombre + ' (+$' + Number(variante.precio_adicional).toLocaleString('es-CO') + ')';
            selectVariante.appendChild(opcion);
        });
    }

    selectProducto.addEventListener('change', actualizarVariantes);
    actualizarVariantes();
});
