<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require '../db_connection.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (strlen($new_password) < 12) {
        $error = "La nueva contraseña debe tener al menos 12 caracteres.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Las nuevas contraseñas no coinciden.";
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password_hash FROM Users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password_hash'])) {
            // Update password
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $pdo->prepare("UPDATE Users SET password_hash = ? WHERE id = ?");
            $update_stmt->execute([$new_hash, $_SESSION['admin_id']]);
            $message = "Contraseña actualizada correctamente.";
        } else {
            $error = "La contraseña actual es incorrecta.";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .password-form {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="admin-header">
            <h2>Cambiar Contraseña</h2>
            <div class="admin-nav">
                <a href="panel.php" class="nav-link">← Volver al Panel</a>
                <a href="logout.php" class="nav-link logout">Cerrar Sesión</a>
            </div>
        </div>

        <div class="password-form">
            <?php if ($message): ?>
                <div class="mensaje"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <label>Contraseña Actual:</label>
                <input type="password" name="current_password" required
                    style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

                <label>Nueva Contraseña (mín. 12 caracteres):</label>
                <input type="password" name="new_password" required minlength="12"
                    style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

                <label>Confirmar Nueva Contraseña:</label>
                <input type="password" name="confirm_password" required minlength="12"
                    style="width: 100%; padding: 8px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px;">

                <button type="submit" class="btn-update" style="width: 100%;">Cambiar Contraseña</button>
            </form>
        </div>
    </div>
</body>

</html>