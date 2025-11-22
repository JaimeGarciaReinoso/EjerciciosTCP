<?php
session_start();
// PEGA AQUÍ EL HASH OBTENIDO EN EL PASO ANTERIOR
define('ADMIN_HASH', 'CHANGE_THIS_HASH_IN_PRODUCTION');

$error = '';
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: panel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], ADMIN_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: panel.php');
        exit;
    } else {
        $error = 'Contraseña incorrecta.';
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="login-container">
        <form method="POST">
            <h2>Acceso al Panel</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p><?php endif; ?>
            <label>Contraseña:</label><input type="password" name="password" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>

</html>