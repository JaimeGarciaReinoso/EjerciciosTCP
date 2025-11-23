<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/../db_connection.php';

$mensaje = '';
$modo_edicion_enunciado = false;
$ejercicio_a_editar = null;

try {
    if (isset($_POST['add_enunciado'])) {
        $sql = "INSERT INTO EnunTCP (ExerciseID, ExerciseNum, ExercisePart, EnunTextES, EnunTextEN, congestion_control) VALUES (?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([(int) $_POST['ExerciseID'], (int) $_POST['ExerciseNum'], (int) $_POST['ExercisePart'], $_POST['EnunTextES'], $_POST['EnunTextEN'], isset($_POST['congestion_control']) ? 1 : 0]);
        $mensaje = "Éxito: Enunciado ID {$_POST['ExerciseID']} añadido.";
    }
    if (isset($_POST['add_menu'])) {
        $link = empty($_POST['link_id']) ? NULL : (int) $_POST['link_id'];
        $part = empty($_POST['part_num']) ? NULL : (int) $_POST['part_num'];
        $sql = "INSERT INTO menu_ejercicios (orden, tipo, clave_idioma, link_id, part_num, habilitado) VALUES (?, ?, ?, ?, ?, 1)";
        $pdo->prepare($sql)->execute([(int) $_POST['orden'], $_POST['tipo'], $_POST['clave_idioma'], $link, $part]);
        $mensaje = "Éxito: Item de menú añadido.";
    }
    if (isset($_POST['update_item_menu'])) {
        $link = empty($_POST['link_id']) ? NULL : (int) $_POST['link_id'];
        $part = empty($_POST['part_num']) ? NULL : (int) $_POST['part_num'];
        $hab = isset($_POST['habilitado']) ? 1 : 0;
        $sql = "UPDATE menu_ejercicios SET orden=?, clave_idioma=?, link_id=?, part_num=?, habilitado=? WHERE id=?";
        $pdo->prepare($sql)->execute([(int) $_POST['orden'], $_POST['clave_idioma'], $link, $part, $hab, (int) $_POST['item_id']]);
        $mensaje = "Éxito: Item actualizado.";
    }
    if (isset($_POST['delete_item_menu'])) {
        $pdo->prepare("DELETE FROM menu_ejercicios WHERE id=?")->execute([(int) $_POST['item_id']]);
        $mensaje = "Éxito: Item borrado.";
    }
    if (isset($_POST['delete_enunciado'])) {
        $ex_id = (int) $_POST['ExerciseID'];
        $pdo->prepare("DELETE FROM menu_ejercicios WHERE link_id=?")->execute([$ex_id]);
        $pdo->prepare("DELETE FROM Exercises WHERE ExerciseID=?")->execute([$ex_id]);
        $pdo->prepare("DELETE FROM EnunTCP WHERE ExerciseID=?")->execute([$ex_id]);
        $mensaje = "Éxito: Enunciado y referencias borrados.";
    }
    if (isset($_POST['update_enunciado'])) {
        $sql = "UPDATE EnunTCP SET ExerciseNum=?, ExercisePart=?, EnunTextES=?, EnunTextEN=?, congestion_control=? WHERE ExerciseID=?";
        $pdo->prepare($sql)->execute([(int) $_POST['ExerciseNum'], (int) $_POST['ExercisePart'], $_POST['EnunTextES'], $_POST['EnunTextEN'], isset($_POST['congestion_control']) ? 1 : 0, (int) $_POST['ExerciseID']]);
        $mensaje = "Éxito: Enunciado actualizado.";
    }
} catch (PDOException $e) {
    $mensaje = "Error: " . $e->getMessage();
}

if (isset($_GET['editar_id'])) {
    $modo_edicion_enunciado = true;
    $stmt = $pdo->prepare("SELECT * FROM EnunTCP WHERE ExerciseID=?");
    $stmt->execute([(int) $_GET['editar_id']]);
    $ejercicio_a_editar = $stmt->fetch();
}

$items_menu = [];
$items_enunciados = [];
$langArray_es = [];
if (!$modo_edicion_enunciado) {
    $items_menu = $pdo->query("SELECT * FROM menu_ejercicios ORDER BY orden ASC")->fetchAll();
    $items_enunciados = $pdo->query("SELECT ExerciseID, ExerciseNum, ExercisePart, EnunTextES FROM EnunTCP ORDER BY ExerciseID ASC")->fetchAll();
    $ruta_es = __DIR__ . '/../locale/es.php';
    if (file_exists($ruta_es)) {
        include $ruta_es;
        $langArray_es = $langArray;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Panel Admin</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="panel-container">
        <div class="panel-header">
            <h2>Panel de Administración</h2>
            <div class="admin-nav">
                <a href="stats.php" class="nav-link">📊 Estadísticas</a>
                <a href="users.php" class="nav-link">👥 Usuarios</a>
                <a href="change_password.php" class="nav-link">🔑 Cambiar Contraseña</a>
                <a href="logout.php" class="nav-link logout">Cerrar Sesión</a>
            </div>
        </div>
        <?php if ($mensaje): ?>
            <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p><?php endif; ?>

        <?php if ($modo_edicion_enunciado && $ejercicio_a_editar): ?>
            <div class="admin-form">
                <h3>Editando Enunciado (ID: <?php echo $ejercicio_a_editar['ExerciseID']; ?>)</h3>
                <form method="POST" action="panel.php">
                    <input type="hidden" name="update_enunciado" value="1">
                    <input type="hidden" name="ExerciseID" value="<?php echo $ejercicio_a_editar['ExerciseID']; ?>">
                    <label>Ejercicio Nº:</label><input type="number" name="ExerciseNum"
                        value="<?php echo $ejercicio_a_editar['ExerciseNum']; ?>" required>
                    <label>Parte Nº:</label><input type="number" name="ExercisePart"
                        value="<?php echo $ejercicio_a_editar['ExercisePart']; ?>" required>
                    <label>Texto (ES):</label><textarea name="EnunTextES"
                        rows="10"><?php echo htmlspecialchars($ejercicio_a_editar['EnunTextES']); ?></textarea>
                    <label>Texto (EN):</label><textarea name="EnunTextEN"
                        rows="10"><?php echo htmlspecialchars($ejercicio_a_editar['EnunTextEN']); ?></textarea>
                    <label><input type="checkbox" name="congestion_control" <?php if ($ejercicio_a_editar['congestion_control'])
                        echo 'checked'; ?>> Tiene Control de
                        Congestión</label>
                    <button type="submit">Guardar</button><a href="panel.php" class="btn-cancel">Cancelar</a>
                </form>
            </div>
        <?php elseif (!$modo_edicion_enunciado): ?>

            <div class="admin-form">
                <h3 class="collapsible-header">Gestionar Menú <span class="toggle-icon">+</span></h3>
                <div class="collapsible-content collapsed">
                    <div class="table-wrapper">
                        <table class="item-table">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Tipo</th>
                                    <th>Clave</th>
                                    <th>Texto (ES)</th>
                                    <th>ID Link</th>
                                    <th>Hab.</th>
                                    <th>Acciones</th>
                                    <th>Enunciado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items_menu as $i):
                                    $txt = isset($langArray_es[$i['clave_idioma']]) ? strip_tags($langArray_es[$i['clave_idioma']]) : '<span class="text-error">!CLAVE?</span>';
                                    ?>
                                    <form method="POST">
                                        <input type="hidden" name="item_id" value="<?php echo $i['id']; ?>">
                                        <tr>
                                            <td><input type="number" name="orden" value="<?php echo $i['orden']; ?>"
                                                    class="input-small"></td>
                                            <td><?php echo $i['tipo']; ?></td>
                                            <td><input type="text" name="clave_idioma"
                                                    value="<?php echo $i['clave_idioma']; ?>">
                                            </td>
                                            <td class="text-preview"><?php echo $txt; ?></td>
                                            <td><input type="number" name="link_id" value="<?php echo $i['link_id']; ?>"
                                                    class="input-small"></td>
                                            <td><input type="checkbox" name="habilitado" <?php if ($i['habilitado'])
                                                echo 'checked'; ?>></td>
                                            <td><button type="submit" name="update_item_menu"
                                                    class="btn-update">OK</button><button type="submit" name="delete_item_menu"
                                                    class="btn-delete" onclick="return confirm('¿Borrar?');">X</button></td>
                                            <td><?php if ($i['tipo'] == 'parte' && $i['link_id']): ?><a
                                                        href="panel.php?editar_id=<?php echo $i['link_id']; ?>"
                                                        class="btn-edit">Editar</a><?php else: ?>-<?php endif; ?></td>
                                        </tr>
                                        <input type="hidden" name="part_num" value="<?php echo $i['part_num']; ?>">
                                    </form>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



            <div class="admin-form">
                <h3 class="collapsible-header">Gestionar Enunciados (Archivo) <span class="toggle-icon">+</span></h3>
                <div class="collapsible-content collapsed">
                    <div class="table-wrapper">
                        <table class="item-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nº</th>
                                    <th>Parte</th>
                                    <th>Preview</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items_enunciados as $e): ?>
                                    <tr>
                                        <td><?php echo $e['ExerciseID']; ?></td>
                                        <td><?php echo $e['ExerciseNum']; ?></td>
                                        <td><?php echo $e['ExercisePart']; ?></td>
                                        <td class="text-preview">
                                            <?php echo htmlspecialchars(substr(strip_tags($e['EnunTextES']), 0, 50)); ?>...
                                        </td>
                                        <td><a href="panel.php?editar_id=<?php echo $e['ExerciseID']; ?>"
                                                class="btn-edit">Editar</a><a
                                                href="definir_solucion.php?id=<?php echo $e['ExerciseID']; ?>"
                                                class="btn-solve">Solución</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <form method="POST" class="admin-form">
                <h3 class="collapsible-header">Añadir Enunciado <span class="toggle-icon">+</span></h3>
                <div class="collapsible-content collapsed">
                    <input type="hidden" name="add_enunciado" value="1">
                    <label>ID:</label><input type="number" name="ExerciseID" required>
                    <label>Nº:</label><input type="number" name="ExerciseNum" required>
                    <label>Parte:</label><input type="number" name="ExercisePart" required>
                    <label><input type="checkbox" name="congestion_control"> Tiene Control de Congestión</label>
                    <button type="submit">Añadir</button>
                </div>
            </form>

            <form method="POST" class="admin-form">
                <h3 class="collapsible-header">Añadir a Menú <span class="toggle-icon">+</span></h3>
                <div class="collapsible-content collapsed">
                    <input type="hidden" name="add_menu" value="1">
                    <label>Orden:</label><input type="number" name="orden" required>
                    <label>Tipo:</label>
                    <select name="tipo">
                        <option value="parte">Parte</option>
                        <option value="ejercicio">Ejercicio</option>
                        <option value="categoria">Categoría</option>
                    </select>
                    <label>Clave:</label><input type="text" name="clave_idioma" required>
                    <label>Link ID:</label><input type="number" name="link_id">
                    <label>Nº Parte:</label><input type="number" name="part_num">
                    <button type="submit">Añadir</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const headers = document.querySelectorAll('.collapsible-header');
            headers.forEach(header => {
                header.addEventListener('click', function () {
                    const content = this.nextElementSibling;
                    content.classList.toggle('collapsed');
                    const icon = this.querySelector('.toggle-icon');
                    icon.textContent = content.classList.contains('collapsed') ? '+' : '-';
                });
            });
        });
    </script>
</body>

</html>