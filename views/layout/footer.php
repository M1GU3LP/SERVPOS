    </main>

    <!-- RNF3.7: redes sociales de la empresa en el pie de pagina -->
    <footer class="pie-pagina">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(NOMBRE_EMPRESA) ?></p>
        <div class="redes-sociales">
            <a href="<?= htmlspecialchars(URL_FACEBOOK) ?>" target="_blank" rel="noopener">Facebook</a>
            <a href="<?= htmlspecialchars(URL_INSTAGRAM) ?>" target="_blank" rel="noopener">Instagram</a>
            <a href="<?= htmlspecialchars(URL_TWITTER) ?>" target="_blank" rel="noopener">Twitter</a>
        </div>
    </footer>
</body>
</html>
