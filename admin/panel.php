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

// Handle AJAX order update
if (isset($_POST['update_order'])) {
    header('Content-Type: application/json');
    $order = json_decode($_POST['order'], true);
    if (is_array($order)) {
        try {
            $pdo->beginTransaction();
            foreach ($order as $position => $id) {
                $stmt = $pdo->prepare("UPDATE menu_ejercicios SET orden = ? WHERE id = ?");
                $stmt->execute([$position + 1, (int) $id]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
    exit;
}

try {
    if (isset($_POST['add_enunciado'])) {
        $congestion = isset($_POST['congestion_control']) ? 1 : 0;
        // Auto-calculate ExerciseID
        $stmtMax = $pdo->query("SELECT MAX(ExerciseID) FROM EnunTCP");
        $nextId = $stmtMax->fetchColumn() + 1;

        $sql = "INSERT INTO EnunTCP (ExerciseID, ExerciseNum, ExercisePart, EnunTextES, EnunTextEN, congestion_control) VALUES (?, ?, ?, '', '', ?)";
        $pdo->prepare($sql)->execute([(int) $nextId, (int) $_POST['ExerciseNum'], (int) $_POST['ExercisePart'], $congestion]);
        $mensaje = "Éxito: Enunciado añadido (ID $nextId).";
    }
    if (isset($_POST['add_menu'])) {
        $link = empty($_POST['link_id']) ? NULL : (int) $_POST['link_id'];
        $part = empty($_POST['part_num']) ? NULL : (int) $_POST['part_num'];
        $orden = (int) $_POST['orden'];

        // Smart Ordering Logic
        if (!empty($_POST['parent_id'])) {
            $parentId = (int) $_POST['parent_id'];
            // Find parent's order
            $stmt = $pdo->prepare("SELECT orden FROM menu_ejercicios WHERE id = ?");
            $stmt->execute([$parentId]);
            $parentOrder = $stmt->fetchColumn();

            if ($parentOrder) {
                // Find the insertion point.
                // We want to insert AFTER the parent and any subsequent 'parte' items.
                // We look for the first item AFTER parent that is NOT a 'parte' (or end of list).
                $sqlFindNext = "SELECT orden FROM menu_ejercicios WHERE orden > ? AND tipo != 'parte' ORDER BY orden ASC LIMIT 1";
                $stmtNext = $pdo->prepare($sqlFindNext);
                $stmtNext->execute([$parentOrder]);
                $nextMajorItemOrder = $stmtNext->fetchColumn();

                if ($nextMajorItemOrder) {
                    $orden = $nextMajorItemOrder;
                    // Shift items down to make space
                    $pdo->prepare("UPDATE menu_ejercicios SET orden = orden + 1 WHERE orden >= ?")->execute([$orden]);
                } else {
                    // No next major item, insert at end
                    $stmtMax = $pdo->query("SELECT MAX(orden) FROM menu_ejercicios");
                    $orden = $stmtMax->fetchColumn() + 1;
                }
            }
        }

        $sql = "INSERT INTO menu_ejercicios (orden, tipo, clave_idioma, link_id, part_num, habilitado) VALUES (?, ?, ?, ?, ?, 1)";
        $pdo->prepare($sql)->execute([$orden, $_POST['tipo'], $_POST['clave_idioma'], $link, $part]);
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
    $items_menu = $pdo->query("
        SELECT m.*, e.EnunTextES 
        FROM menu_ejercicios m 
        LEFT JOIN EnunTCP e ON m.link_id = e.ExerciseID 
        ORDER BY m.orden ASC
    ")->fetchAll();
    $max_order = 0;
    foreach ($items_menu as $im) {
        if ($im['orden'] > $max_order)
            $max_order = $im['orden'];
    }
    $next_order = $max_order + 1;
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

                    <button type="button" id="btn-show-add-menu" class="btn-add-inline">➕ Añadir Elemento</button>

                    <div id="add-menu-form-container" class="menu-item-card add-form-card" style="display:none;">
                        <form method="POST" style="width: 100%; display: grid; gap: 10px;">
                            <input type="hidden" name="add_menu" value="1">
                            <div class="form-row">
                                <label>Tipo:
                                    <select name="tipo" id="add-menu-tipo">
                                        <option value="parte">Parte</option>
                                        <option value="ejercicio">Ejercicio</option>
                                        <option value="categoria">Categoría</option>
                                    </select>
                                </label>
                                <label id="parent-select-container">Bajo Ejercicio:
                                    <select name="parent_id">
                                        <option value="">-- Ninguno (Raíz) --</option>
                                        <?php foreach ($items_menu as $im): ?>
                                            <?php if ($im['tipo'] == 'ejercicio' || $im['tipo'] == 'categoria'): ?>
                                                <option value="<?php echo $im['id']; ?>">
                                                    <?php echo htmlspecialchars(isset($langArray_es[$im['clave_idioma']]) ? strip_tags($langArray_es[$im['clave_idioma']]) : $im['clave_idioma']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Orden: <input type="number" name="orden" value="<?php echo $next_order; ?>"
                                        class="input-small" required></label>
                            </div>
                            <div class="form-row">
                                <label>Clave Idioma: <input type="text" name="clave_idioma" required
                                        placeholder="ej: ex_1_title"></label>
                                <label>Enunciado (Link):
                                    <select name="link_id">
                                        <option value="">-- Seleccionar --</option>
                                        <?php foreach ($items_enunciados as $ie): ?>
                                            <option value="<?php echo $ie['ExerciseID']; ?>">
                                                ID <?php echo $ie['ExerciseID']; ?> -
                                                <?php echo htmlspecialchars(substr(strip_tags($ie['EnunTextES']), 0, 30)); ?>...
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label id="part-num-container">Nº Parte: <input type="number" name="part_num"
                                        placeholder="1, 2..."></label>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-update">Guardar</button>
                                <button type="button" id="btn-cancel-add-menu" class="btn-cancel">Cancelar</button>
                            </div>
                        </form>
                    </div>

                    <div id="menu-list" class="menu-list">
                        <?php foreach ($items_menu as $i):
                            $txt = isset($langArray_es[$i['clave_idioma']]) ? strip_tags($langArray_es[$i['clave_idioma']]) : '<span class="text-error">!CLAVE?</span>';
                            $typeBadge = match ($i['tipo']) {
                                'parte' => 'badge-type',
                                'ejercicio' => 'badge-success',
                                default => 'badge-lang'
                            };
                            ?>
                            <div class="menu-item-card" data-id="<?php echo $i['id']; ?>">
                                <div class="menu-item-handle">☰</div>
                                <div class="menu-item-details">
                                    <div class="menu-item-info">
                                        <span class="menu-item-label">Tipo</span>
                                        <span class="badge <?php echo $typeBadge; ?>"><?php echo ucfirst($i['tipo']); ?></span>
                                    </div>
                                    <div class="menu-item-info">
                                        <span class="menu-item-label">Texto (ES)</span>
                                        <span class="menu-item-value">
                                            <?php echo $txt; ?>
                                            <?php if ($i['tipo'] == 'parte' && !empty($i['part_num'])): ?>
                                                <span class="badge badge-type" style="font-size: 0.8em; margin-left: 5px;">Parte
                                                    <?php echo $i['part_num']; ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <small style="color:#94a3b8"><?php echo $i['clave_idioma']; ?></small>
                                        <?php if (!empty($i['EnunTextES'])): ?>
                                            <div class="menu-item-snippet">
                                                <?php echo htmlspecialchars(substr(strip_tags($i['EnunTextES']), 0, 60)) . '...'; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="menu-item-info">
                                        <span class="menu-item-label">Link ID</span>
                                        <span class="menu-item-value"><?php echo $i['link_id'] ?: '-'; ?></span>
                                    </div>
                                    <div class="menu-item-info">
                                        <span class="menu-item-label">Estado</span>
                                        <span
                                            class="badge <?php echo $i['habilitado'] ? 'badge-success' : 'badge-disabled'; ?>">
                                            <?php echo $i['habilitado'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="menu-item-actions">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="item_id" value="<?php echo $i['id']; ?>">
                                        <button type="submit" name="delete_item_menu" class="btn-delete"
                                            onclick="return confirm('¿Borrar?');" title="Borrar">🗑️</button>
                                    </form>
                                    <?php if ($i['tipo'] == 'parte' && $i['link_id']): ?>
                                        <a href="panel.php?editar_id=<?php echo $i['link_id']; ?>" class="btn-edit"
                                            title="Editar Enunciado">✏️</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button id="save-order-btn" class="btn-update" style="display:none; margin-top: 20px; width: 100%;">💾
                        Guardar Nuevo Orden</button>
                </div>
            </div>



            <div class="admin-form">
                <h3 class="collapsible-header">Gestionar Enunciados <span class="toggle-icon">+</span></h3>
                <div class="collapsible-content collapsed">
                    <?php
                    // Group enunciados by ExerciseNum
                    $enunciados_grouped = [];
                    foreach ($items_enunciados as $e) {
                        $enunciados_grouped[$e['ExerciseNum']][] = $e;
                    }
                    ksort($enunciados_grouped);
                    ?>

                    <?php foreach ($enunciados_grouped as $num => $parts): ?>
                        <div style="margin-bottom: 10px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                            <h4 class="collapsible-header"
                                style="background: #f8fafc; margin: 0; padding: 10px 15px; font-size: 0.95em;">
                                Ejercicio <?php echo $num; ?>
                                <span style="float: right; color: #64748b; font-size: 0.8em;"><?php echo count($parts); ?>
                                    partes</span>
                                <span class="toggle-icon" style="float: right; margin-right: 10px;">+</span>
                            </h4>
                            <div class="collapsible-content collapsed">
                                <div class="table-wrapper" style="border: none;">
                                    <table class="item-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Parte</th>
                                                <th>Preview</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($parts as $e): ?>
                                                <tr>
                                                    <td><?php echo $e['ExerciseID']; ?></td>
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

                                <div style="padding: 10px;">
                                    <button type="button" class="btn-add-inline btn-show-add-part"
                                        data-target="form-part-<?php echo $num; ?>"
                                        style="margin-bottom: 0; font-size: 0.9em; padding: 6px 12px;">➕ Añadir Parte</button>

                                    <div id="form-part-<?php echo $num; ?>" class="menu-item-card add-form-card"
                                        style="display:none; margin-top: 10px;">
                                        <form method="POST" style="width: 100%; display: grid; gap: 10px;">
                                            <input type="hidden" name="add_enunciado" value="1">
                                            <input type="hidden" name="ExerciseNum" value="<?php echo $num; ?>">
                                            <div class="form-row">
                                                <label>Nº Parte: <input type="number" name="ExercisePart"
                                                        value="<?php echo count($parts) + 1; ?>" required></label>
                                                <label
                                                    style="flex-direction: row; align-items: center; gap: 10px; margin-top: 25px;">
                                                    <input type="checkbox" name="congestion_control" style="margin: 0;">
                                                    Control de Congestión
                                                </label>
                                            </div>
                                            <div class="form-actions">
                                                <button type="submit" class="btn-update">Guardar</button>
                                                <button type="button" class="btn-cancel btn-cancel-add-part"
                                                    data-target="form-part-<?php echo $num; ?>">Cancelar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Formulario de añadir enunciado integrado arriba -->

            <!-- Formulario de añadir integrado arriba -->
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Collapsible Blocks
            const headers = document.querySelectorAll('.collapsible-header');
            headers.forEach(header => {
                header.addEventListener('click', function () {
                    const content = this.nextElementSibling;
                    content.classList.toggle('collapsed');
                    const icon = this.querySelector('.toggle-icon');
                    icon.textContent = content.classList.contains('collapsed') ? '+' : '-';
                });
            });

            // WYSIWYG Menu Editor
            const menuList = document.getElementById('menu-list');
            const saveBtn = document.getElementById('save-order-btn');

            if (menuList) {
                new Sortable(menuList, {
                    animation: 150,
                    handle: '.menu-item-handle',
                    ghostClass: 'sortable-ghost',
                    onEnd: function () {
                        saveBtn.style.display = 'block';
                    }
                });

                saveBtn.addEventListener('click', function () {
                    const order = [];
                    menuList.querySelectorAll('.menu-item-card').forEach((item, index) => {
                        order.push(item.getAttribute('data-id'));
                    });

                    fetch('panel.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'update_order=1&order=' + JSON.stringify(order)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert('Orden guardado correctamente');
                                saveBtn.style.display = 'none';
                            } else {
                                alert('Error al guardar: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error de conexión');
                        });
                });
            }

            // Inline Add Form Toggle
            const btnShowAdd = document.getElementById('btn-show-add-menu');
            const btnCancelAdd = document.getElementById('btn-cancel-add-menu');
            const formContainer = document.getElementById('add-menu-form-container');

            if (btnShowAdd && formContainer) {
                btnShowAdd.addEventListener('click', function () {
                    formContainer.style.display = 'block';
                    btnShowAdd.style.display = 'none';
                });
            }

            if (btnCancelAdd && formContainer) {
                btnCancelAdd.addEventListener('click', function () {
                    formContainer.style.display = 'none';
                    btnShowAdd.style.display = 'block';
                });
            }

            // Contextual Add Part Form Toggle
            document.querySelectorAll('.btn-show-add-part').forEach(btn => {
                btn.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const form = document.getElementById(targetId);
                    if (form) {
                        form.style.display = 'block';
                        this.style.display = 'none';
                    }
                });
            });

            document.querySelectorAll('.btn-cancel-add-part').forEach(btn => {
                btn.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const form = document.getElementById(targetId);
                    if (form) {
                        form.style.display = 'none';
                        // Find the show button for this form
                        const showBtn = document.querySelector(`.btn-show-add-part[data-target="${targetId}"]`);
                        if (showBtn) showBtn.style.display = 'inline-block';
                    }
                });
            });

            // Toggle Fields based on Type
            const typeSelect = document.getElementById('add-menu-tipo');
            const parentContainer = document.getElementById('parent-select-container');
            const partNumContainer = document.getElementById('part-num-container');

            if (typeSelect) {
                function toggleFields() {
                    if (typeSelect.value === 'parte') {
                        if (parentContainer) parentContainer.style.display = 'flex';
                        if (partNumContainer) partNumContainer.style.display = 'flex';
                    } else {
                        if (parentContainer) parentContainer.style.display = 'none';
                        if (partNumContainer) partNumContainer.style.display = 'none';
                    }
                }
                typeSelect.addEventListener('change', toggleFields);
                toggleFields(); // Init
            }
        });
    </script>
</body>

</html>