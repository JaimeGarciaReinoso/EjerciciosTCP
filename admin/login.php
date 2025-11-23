<?php
session_start();
require '../db_connection.php';

$error = '';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: panel.php');
    exit;
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM Users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_id'] = $user['id'];
            header('Location: panel.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } catch (PDOException $e) {
        $error = 'Error de base de datos: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="login-container">
        <form method="POST">
            <h2>Acceso al Panel</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>

            <label>Usuario:</label>
            <input type="text" name="username" required autofocus>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>

</html>