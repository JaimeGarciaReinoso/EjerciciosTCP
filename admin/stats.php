<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require '../db_connection.php';

// Handle Cleanup Action
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cleanup'])) {
    try {
        // Delete records older than 1 year
        $stmt = $pdo->prepare("DELETE FROM ExerciseStats WHERE timestamp < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
        $stmt->execute();
        $deleted = $stmt->rowCount();
        $message = "Se han eliminado $deleted registros antiguos (más de 1 año).";
    } catch (PDOException $e) {
        $message = "Error al limpiar datos: " . $e->getMessage();
    }
}

// Fetch Summary Stats
$totalAttempts = $pdo->query("SELECT COUNT(*) FROM ExerciseStats")->fetchColumn();
$totalCorrect = $pdo->query("SELECT COUNT(*) FROM ExerciseStats WHERE is_correct = 1")->fetchColumn();
$successRate = $totalAttempts > 0 ? round(($totalCorrect / $totalAttempts) * 100, 1) : 0;

// Handle Filter
$filterExerciseId = isset($_GET['filter_exercise']) ? $_GET['filter_exercise'] : 'all';
$whereClause = "";
$params = [];

if ($filterExerciseId !== 'all' && is_numeric($filterExerciseId)) {
    $whereClause = "WHERE exercise_id = ?";
    $params[] = $filterExerciseId;
}

// Fetch Daily Stats (Last 30 days)
$dailySql = "
    SELECT DATE(timestamp) as date, COUNT(*) as total, SUM(is_correct) as correct 
    FROM ExerciseStats 
    $whereClause
    GROUP BY DATE(timestamp) 
    ORDER BY date DESC LIMIT 30
";
$dailyStmt = $pdo->prepare($dailySql);
$dailyStmt->execute($params);
$dailyStats = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'exercise';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';

$allowedSorts = [
    'exercise' => 'e.ExerciseNum',
    'part' => 'e.ExercisePart',
    'attempts' => 'attempts',
    'correct' => 'correct',
    'rate' => 'rate',
    'errors' => 'avg_errors'
];

if (!array_key_exists($sort, $allowedSorts)) {
    $sort = 'exercise';
}

// Determine SQL order
$sqlOrder = $allowedSorts[$sort];
if ($sort === 'exercise') {
    // Secondary sort for exercise
    $sqlOrder = "e.ExerciseNum $order, e.ExercisePart ASC";
} elseif ($sort === 'rate') {
    // Rate is calculated, so we sort by correct/attempts
    $sqlOrder = "CASE WHEN COUNT(s.id) > 0 THEN SUM(s.is_correct)/COUNT(s.id) ELSE 0 END $order";
} else {
    $sqlOrder = "$sqlOrder $order";
}

// Fetch Attempts by Exercise
$stmt = $pdo->query("
    SELECT 
        e.ExerciseID,
        e.ExerciseNum, 
        e.ExercisePart, 
        COUNT(s.id) as attempts, 
        SUM(s.is_correct) as correct,
        AVG(s.error_count) as avg_errors
    FROM EnunTCP e
    LEFT JOIN ExerciseStats s ON e.ExerciseID = s.exercise_id
    GROUP BY e.ExerciseID
    ORDER BY $sqlOrder
");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function for sort links
function sortLink($column, $label, $currentSort, $currentOrder)
{
    $newOrder = ($currentSort === $column && $currentOrder === 'desc') ? 'asc' : 'desc';
    $arrow = '';
    if ($currentSort === $column) {
        $arrow = $currentOrder === 'asc' ? ' ↑' : ' ↓';
    }
    // Preserve filter param
    $filter = isset($_GET['filter_exercise']) ? '&filter_exercise=' . urlencode($_GET['filter_exercise']) : '';
    return "<a href=\"?sort=$column&order=$newOrder$filter\" style=\"color: inherit; text-decoration: none;\">$label$arrow</a>";
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Estadísticas de Uso</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .progress-bar {
            background-color: #ecf0f1;
            border-radius: 4px;
            height: 8px;
            width: 100px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background-color: #2ecc71;
        }

        /* Chart Styles */
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .bar-chart {
            display: flex;
            height: 200px;
            gap: 10px;
            padding-top: 20px;
            align-items: stretch;
        }

        .bar-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            min-width: 40px;
            height: 100%;
            justify-content: flex-end;
        }

        .bar {
            width: 100%;
            background-color: #3498db;
            border-radius: 4px 4px 0 0;
            position: relative;
            transition: height 0.3s ease;
        }

        .bar.correct {
            background-color: #2ecc71;
            position: absolute;
            bottom: 0;
            width: 100%;
            z-index: 2;
        }

        .bar-wrapper {
            width: 30px;
            height: 80%;
            position: relative;
            display: flex;
            align-items: flex-end;
        }

        .bar-label {
            height: 30px;
            margin-top: 5px;
            font-size: 0.7em;
            color: #7f8c8d;
            transform: rotate(-45deg);
            white-space: nowrap;
            text-align: center;
        }

        .cleanup-section {
            margin-top: 40px;
            text-align: right;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="admin-header">
            <h2>Estadísticas de Uso</h2>
            <div class="admin-nav">
                <a href="panel.php" class="nav-link">← Volver al Panel</a>
                <a href="logout.php" class="nav-link logout">Cerrar Sesión</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalAttempts; ?></div>
                <div class="stat-label">Intentos Totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalCorrect; ?></div>
                <div class="stat-label">Soluciones Correctas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $successRate; ?>%</div>
                <div class="stat-label">Tasa de Éxito</div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
            <h3>Actividad Diaria (Últimos 30 días)</h3>
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <label>Filtrar por:</label>
                <select name="filter_exercise" onchange="this.form.submit()"
                    style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                    <option value="all">Todos los ejercicios</option>
                    <?php foreach ($exercises as $ex): ?>
                        <option value="<?php echo $ex['ExerciseID']; ?>" <?php echo $filterExerciseId == $ex['ExerciseID'] ? 'selected' : ''; ?>>
                            Ej <?php echo $ex['ExerciseNum']; ?> - Parte <?php echo $ex['ExercisePart']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="chart-container">
            <div class="bar-chart">
                <?php
                // Find max value for scaling
                $maxVal = 0;
                foreach ($dailyStats as $day) {
                    if ($day['total'] > $maxVal)
                        $maxVal = $day['total'];
                }
                if ($maxVal == 0)
                    $maxVal = 1; // Avoid division by zero
                
                foreach (array_reverse($dailyStats) as $day):
                    $heightTotal = ($day['total'] / $maxVal) * 100;
                    $heightCorrect = ($day['total'] > 0) ? ($day['correct'] / $day['total']) * 100 : 0;
                    // Calculate correct height relative to the max value, not the total bar
                    $heightCorrectAbs = ($day['correct'] / $maxVal) * 100;
                    ?>
                    <div class="bar-group">
                        <div class="bar-wrapper"
                            title="<?php echo $day['date'] . ': ' . $day['correct'] . '/' . $day['total']; ?>">
                            <div class="bar" style="height: <?php echo $heightTotal; ?>%"></div>
                            <div class="bar correct" style="height: <?php echo $heightCorrectAbs; ?>%"></div>
                        </div>
                        <div class="bar-label"><?php echo date('d/m', strtotime($day['date'])); ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($dailyStats)): ?>
                    <p style="width: 100%; text-align: center; color: #7f8c8d;">No hay datos recientes.</p>
                <?php endif; ?>
            </div>
        </div>

        <h3>Desglose por Ejercicio</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th><?php echo sortLink('exercise', 'Ejercicio', $sort, $order); ?></th>
                    <th><?php echo sortLink('part', 'Parte', $sort, $order); ?></th>
                    <th><?php echo sortLink('attempts', 'Intentos', $sort, $order); ?></th>
                    <th><?php echo sortLink('correct', 'Correctos', $sort, $order); ?></th>
                    <th><?php echo sortLink('rate', 'Tasa Éxito', $sort, $order); ?></th>
                    <th><?php echo sortLink('errors', 'Errores (Media)', $sort, $order); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exercises as $ex):
                    $rate = $ex['attempts'] > 0 ? round(($ex['correct'] / $ex['attempts']) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?php echo $ex['ExerciseNum']; ?></td>
                        <td><?php echo $ex['ExercisePart']; ?></td>
                        <td><?php echo $ex['attempts']; ?></td>
                        <td><?php echo $ex['correct']; ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $rate; ?>%"></div>
                                </div>
                                <?php echo $rate; ?>%
                            </div>
                        </td>
                        <td><?php echo $ex['attempts'] > 0 ? round($ex['avg_errors'], 1) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cleanup-section">
            <form method="POST"
                onsubmit="return confirm('¿Estás seguro? Esto borrará permanentemente los datos de uso de hace más de un año.');">
                <p style="display: inline-block; margin-right: 15px; color: #7f8c8d;">Mantenimiento de base de datos:
                </p>
                <button type="submit" name="cleanup" class="btn-danger">Borrar datos antiguos (> 1 año)</button>
            </form>
        </div>
    </div>
</body>

</html>