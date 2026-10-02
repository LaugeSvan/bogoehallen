<?php
$address = get_content('footer', 'address', 'Bogø Idrætspark 1, 4773 Kalvebod');
$map_query = get_content('footer', 'google_maps_query', $address);
$map_query = $map_query !== '' ? $map_query : $address;
$encoded_map_query = rawurlencode($map_query);
$bylaws_url = safe_navigation_url(get_content('footer', 'bylaws_url', '/vedtaegt.php'), '/vedtaegt.php');
$sponsor_url = safe_navigation_url(get_content('footer', 'sponsor_url', '/bliv-sponsor.php'), '/bliv-sponsor.php');
$contact_url = safe_navigation_url(get_content('footer', 'contact_url', '/kontakt.php'), '/kontakt.php');
$facebook_url = safe_navigation_url(get_content('footer', 'facebook_url', ''), '');
?>
    </main>

    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Kontakt</h3>
                <p><?php echo safe_html($address); ?></p>
                <p>
                    <a href="mailto:<?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogohallen.dk')); ?>">
                        <?php echo safe_html(get_content('footer', 'contact_email', 'kontakt@bogohallen.dk')); ?>
                    </a>
                </p>
            </div>

            <div class="footer-section">
                <h3>Links</h3>
                <ul>
                    <li><a href="<?php echo safe_html($bylaws_url); ?>">Vedtægt</a></li>
                    <li><a href="<?php echo safe_html($contact_url); ?>">Kontakt os</a></li>
                    <li><a href="<?php echo safe_html($sponsor_url); ?>">Bliv sponsor</a></li>
                    <li><a href="/admin/login.php">Admin</a></li>
                    <li><a href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo safe_html($encoded_map_query); ?>" target="_blank" rel="noopener noreferrer">Google Maps</a></li>
                    <?php if ($facebook_url): ?>
                        <li><a href="<?php echo safe_html($facebook_url); ?>" target="_blank" rel="noopener noreferrer">Facebook</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-section">
                <h3>CVR</h3>
                <p><?php echo safe_html(get_content('footer', 'cvr', 'CVR: 12345678')); ?></p>
            </div>
            <div class="footer-section footer-map">
                <h3>Find os</h3>
                <iframe
                    src="https://www.google.com/maps?q=<?php echo safe_html($encoded_map_query); ?>&amp;output=embed"
                    title="Kort over Bogø Hallen"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Bogø Hallen. Alle rettigheder forbeholdt.</p>
        </div>
    </footer>

    <script src="/js/main.js"></script>
</body>
</html>
