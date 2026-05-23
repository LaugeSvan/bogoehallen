<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/admin-functions.php';

init_session();

destroy_admin_session();

header('Location: /admin/login.php?logged_out=1');
exit;
