    </main>

    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Kontakt</h3>
                <p><?php echo safe_html(get_content('footer', 'address', 'Bogø Idrætspark 1, 4773 Kalvebod')); ?></p>
                <p>
                    <a href="mailto:<?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogohallen.dk')); ?>">
                        <?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogohallen.dk')); ?>
                    </a>
                </p>
            </div>

            <div class="footer-section">
                <h3>Links</h3>
                <ul>
                    <li><a href="/vedtaegt.php">Vedtægt</a></li>
                    <li><a href="/kontakt.php">Kontakt os</a></li>
                    <li><a href="/bliv-sponsor.php">Bliv sponsor</a></li>
                    <?php $facebook_url = get_content('footer', 'facebook_url', ''); if ($facebook_url): ?>
                        <li><a href="<?php echo safe_html($facebook_url); ?>" target="_blank">Facebook</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-section">
                <h3>CVR</h3>
                <p><?php echo safe_html(get_content('footer', 'cvr', 'CVR: 12345678')); ?></p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Bogø Hallen. Alle rettigheder forbeholdt.</p>
        </div>
    </footer>

    <script src="/js/main.js"></script>
</body>
</html>
