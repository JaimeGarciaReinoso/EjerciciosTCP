<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require '../db_connection.php';

$message = '';
$error = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Usuario y contraseña son obligatorios.";
    } elseif (strlen($password) < 12) {
        $error = "La contraseña debe tener al menos 12 caracteres.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO Users (username, password_hash) VALUES (:username, :hash)");
            $stmt->execute([':username' => $username, ':hash' => $hash]);
            $message = "Usuario creado correctamente.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "El nombre de usuario ya existe.";
            } else {
                $error = "Error al crear usuario: " . $e->getMessage();
            }
        }
    }
}

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $userId = $_POST['user_id'];

    // Prevent self-deletion
    if ($userId == $_SESSION['admin_id']) {
        $error = "No puedes eliminar tu propio usuario.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM Users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $message = "Usuario eliminado.";
        } catch (PDOException $e) {
            $error = "Error al eliminar usuario: " . $e->getMessage();
        }
    }
}

// Fetch Users
$users = $pdo->query("SELECT id, username, created_at FROM Users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .users-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .user-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .user-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }

        .user-item:last-child {
            border-bottom: none;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-meta {
            font-size: 0.85em;
            color: #7f8c8d;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.9em;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="admin-header">
            <h2>Gestión de Usuarios</h2>
            <div class="admin-nav">
                <a href="panel.php" class="nav-link">← Volver al Panel</a>
                <a href="logout.php" class="nav-link logout">Cerrar Sesión</a>
            </div>
        </div>

        <div class="users-container">
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="user-form">
                <h3>Añadir Nuevo Usuario</h3>
                <form method="POST" style="display: flex; gap: 10px; align-items: flex-end;">
                    <input type="hidden" name="action" value="add">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px;">Usuario</label>
                        <input type="text" name="username" required
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px;">Contraseña</label>
                        <input type="password" name="password" required
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <button type="submit"
                        style="padding: 9px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">Crear</button>
                </form>
            </div>

            <h3>Usuarios Existentes</h3>
            <div class="user-list">
                <?php foreach ($users as $user): ?>
                    <div class="user-item">
                        <div class="user-info">
                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                            <span class="user-meta">Creado: <?php echo $user['created_at']; ?></span>
                        </div>
                        <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                            <form method="POST" onsubmit="return confirm('¿Eliminar usuario?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn-danger btn-small">Eliminar</button>
                            </form>
                        <?php else: ?>
                            <span style="color: #7f8c8d; font-size: 0.9em;">(Tú)</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>