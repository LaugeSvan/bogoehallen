<?php
/**
 * Admin Panel Login
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/includes/admin-functions.php';

init_session();

// If already logged in, redirect to dashboard
if (is_admin_logged_in()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Sikkerhedstjekket mislykkedes. Prøv igen.';
    } else {
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Indtast både brugernavn og adgangskode.';
        } else {
            $auth = authenticate_user($username, $password);

            if ($auth['success']) {
                create_admin_session($auth['user']);
                header('Location: /admin/dashboard.php');
                exit;
            } else {
                $error = $auth['error'];
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bogø Hallen</title>
    <style>
        :root {
            --bg: #071a2a;
            --panel: rgba(13, 34, 55, 0.96);
            --surface: #102d46;
            --surface-soft: #15395d;
            --line: rgba(255, 255, 255, 0.08);
            --text: #edf3fa;
            --muted: #a8b7c8;
            --blue: #3C85BA;
            --green: #2B7B35;
            --navy: #002748;
            --danger: #d85b5b;
            --success: #2d8a4f;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: radial-gradient(circle at top, #0f2d46 0%, var(--bg) 55%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            color: var(--text);
        }

        .login-container {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 420px;
            padding: 32px 28px 24px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-header h1 {
            font-size: 28px;
            color: var(--text);
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        .login-header p {
            font-size: 14px;
            color: var(--muted);
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--muted);
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--line);
            border-radius: 10px;
            color: var(--text);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: var(--muted);
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: rgba(60, 133, 186, 0.6);
            box-shadow: 0 0 0 3px rgba(60, 133, 186, 0.14);
        }

        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 14px;
            border: 1px solid transparent;
        }

        .alert-danger {
            background: rgba(216, 91, 91, 0.12);
            color: #ffdfe1;
            border-color: rgba(216, 91, 91, 0.35);
        }

        .alert-success {
            background: rgba(45, 138, 79, 0.12);
            color: #d6fbe0;
            border-color: rgba(45, 138, 79, 0.28);
        }

        button {
            width: 100%;
            padding: 13px 16px;
            font-size: 15px;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, var(--blue) 0%, var(--navy) 100%);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 25px rgba(60, 133, 186, 0.18);
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 27px rgba(60, 133, 186, 0.26);
        }

        button:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Bogø Hallen</h1>
            <p>Log ind på administrationspanelet</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo safe_html($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo safe_html($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Brugernavn</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Adgangskode</label>
                <input type="password" id="password" name="password" required>
            </div>

            <?php echo csrf_input(); ?>

            <button type="submit">Log Ind</button>
        </form>

        <div class="login-footer">
            <p>© <?php echo date('Y'); ?> Bogø Hallen - Admin Panel</p>
        </div>
    </div>
</body>
</html>
