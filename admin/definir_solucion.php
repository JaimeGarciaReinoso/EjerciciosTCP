<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/../db_connection.php';

$mensaje = '';
$exercise_id = (int) ($_GET['id'] ?? 0);
if ($exercise_id === 0)
    die("Error: Sin ID.");

// Fetch congestion control flag
$cc_flag = $pdo->query("SELECT congestion_control FROM EnunTCP WHERE ExerciseID=$exercise_id")->fetchColumn();
$has_cc = ($cc_flag == 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $post_to_null = function ($val) {
            return (empty($val) && $val !== '0') ? null : $val;
        };
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM Exercises WHERE ExerciseID = ?")->execute([$exercise_id]);
        $pdo->prepare("DELETE FROM TcpState WHERE ExerciseID = ?")->execute([$exercise_id]);

        for ($x = 1; $x <= 15; $x++) {
            // Client fields
            $c_sn = $post_to_null($_POST["c$x-sn"]);
            $c_an = $post_to_null($_POST["c$x-an"]);
            $c_syn = $post_to_null($_POST["c$x-syn"]);
            $c_ack = $post_to_null($_POST["c$x-ack"]);
            $c_fin = $post_to_null($_POST["c$x-fin"]);
            $c_w = $post_to_null($_POST["c$x-w"]);
            $c_mss = $post_to_null($_POST["c$x-mss"]);
            $c_datalen = $post_to_null($_POST["c$x-datalen"]);
            $c_cwnd = $has_cc ? $post_to_null($_POST["c$x-cwnd"]) : null;
            $c_mode = $has_cc ? $post_to_null($_POST["c$x-mode"]) : null;

            $client = [$c_sn, $c_an, $c_syn, $c_ack, $c_fin, $c_w, $c_mss, $c_datalen];

            // Server fields
            $s_sn = $post_to_null($_POST["s$x-sn"]);
            $s_an = $post_to_null($_POST["s$x-an"]);
            $s_syn = $post_to_null($_POST["s$x-syn"]);
            $s_ack = $post_to_null($_POST["s$x-ack"]);
            $s_fin = $post_to_null($_POST["s$x-fin"]);
            $s_w = $post_to_null($_POST["s$x-w"]);
            $s_mss = $post_to_null($_POST["s$x-mss"]);
            $s_datalen = $post_to_null($_POST["s$x-datalen"]);
            $s_cwnd = $has_cc ? $post_to_null($_POST["s$x-cwnd"]) : null;
            $s_mode = $has_cc ? $post_to_null($_POST["s$x-mode"]) : null;

            $server = [$s_sn, $s_an, $s_syn, $s_ack, $s_fin, $s_w, $s_mss, $s_datalen];

            if (
                !empty(array_filter($client, function ($v) {
                    return $v !== null;
                }))
            ) {
                $pdo->prepare("INSERT INTO Segments (SN,AN,SYN,ACK,FIN,W,MSS,datalen) VALUES (?,?,?,?,?,?,?,?)")->execute($client);
                $pdo->prepare("INSERT INTO Exercises (ExerciseID,SegmentID,Sender,TicID) VALUES (?,?,?,?)")->execute([$exercise_id, $pdo->lastInsertId(), 0, $x]);
            }
            // Save Client State if exists
            if ($c_cwnd !== null || $c_mode !== null) {
                $pdo->prepare("INSERT INTO TcpState (ExerciseID, TicID, Sender, cwnd, tcp_mode) VALUES (?,?,?,?,?)")->execute([$exercise_id, $x, 0, $c_cwnd, $c_mode]);
            }

            if (
                !empty(array_filter($server, function ($v) {
                    return $v !== null;
                }))
            ) {
                $pdo->prepare("INSERT INTO Segments (SN,AN,SYN,ACK,FIN,W,MSS,datalen) VALUES (?,?,?,?,?,?,?,?)")->execute($server);
                $pdo->prepare("INSERT INTO Exercises (ExerciseID,SegmentID,Sender,TicID) VALUES (?,?,?,?)")->execute([$exercise_id, $pdo->lastInsertId(), 1, $x]);
            }
            // Save Server State if exists
            if ($s_cwnd !== null || $s_mode !== null) {
                $pdo->prepare("INSERT INTO TcpState (ExerciseID, TicID, Sender, cwnd, tcp_mode) VALUES (?,?,?,?,?)")->execute([$exercise_id, $x, 1, $s_cwnd, $s_mode]);
            }
        }
        $pdo->commit();
        $mensaje = "¡Solución guardada!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensaje = "Error: " . $e->getMessage();
    }
}

// Fetch existing solution data
$arraySeg = [];

for ($t = 1; $t <= 15; $t++) {
    foreach ([0, 1] as $sender) { // 0 = Client, 1 = Server
        // 1. Fetch Segment Data
        $segStmt = $pdo->prepare("
            SELECT s.* 
            FROM Exercises e 
            JOIN Segments s ON e.SegmentID = s.ID 
            WHERE e.ExerciseID = :eid AND e.TicID = :tic AND e.Sender = :sender
        ");
        $segStmt->execute([':eid' => $exercise_id, ':tic' => $t, ':sender' => $sender]);
        $s = $segStmt->fetch(PDO::FETCH_OBJ);

        // 2. Fetch State Data
        $stateStmt = $pdo->prepare("
            SELECT cwnd, tcp_mode 
            FROM TcpState 
            WHERE ExerciseID = :eid AND TicID = :tic AND Sender = :sender
        ");
        $stateStmt->execute([':eid' => $exercise_id, ':tic' => $t, ':sender' => $sender]);
        $state = $stateStmt->fetch(PDO::FETCH_OBJ);

        // 3. Prepare Values
        $sn = $s ? $s->SN : 'NULL';
        $an = $s ? $s->AN : 'NULL';
        $syn = $s ? $s->SYN : 'NULL';
        $ack = $s ? $s->ACK : 'NULL';
        $fin = $s ? $s->FIN : 'NULL';
        $w = $s ? $s->W : 'NULL';
        $mss = ($s && !empty($s->MSS)) ? $s->MSS : 'NULL';
        $datalen = $s ? $s->datalen : 'NULL';

        $cwnd = ($state && !empty($state->cwnd)) ? $state->cwnd : 'NULL';
        $mode = ($state && !empty($state->tcp_mode)) ? $state->tcp_mode : 'NULL';

        // 4. Add to Array (Order: SN, AN, SYN, ACK, FIN, W, MSS, datalen, cwnd, tcp_mode)
        $arraySeg[] = [$sn, $an, $syn, $ack, $fin, $w, $mss, $datalen, $cwnd, $mode];
    }
}

$enun = $pdo->query("SELECT EnunTextES FROM EnunTCP WHERE ExerciseID=$exercise_id")->fetchColumn();
function gv($arr, $t, $f)
{
    $v = $arr[$t][$f] ?? 'NULL';
    $val = ($v === 'NULL' || is_null($v)) ? '' : $v;
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Solución</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
</head>

<body>

    <nav class="navbar">
        <h1>TCP Exercises</h1>
    </nav>

    <div class="container">
        <div class="card">
            <div class="panel-header">
                <h2>Solución Ej: <?php echo $exercise_id; ?>
                    <?php if ($has_cc)
                        echo "<span style='font-size:0.6em; background:#e0f2fe; color:#0284c7; padding:2px 6px; border-radius:4px;'>Congestion Control</span>"; ?>
                </h2><a href="panel.php">Volver</a>
            </div>
            <?php if ($mensaje)
                echo "<p class='mensaje'>$mensaje</p>"; ?>
            <div class="admin-form">
                <h3>Enunciado</h3>
                <div class="enunciado-preview"><?php echo $enun; ?></div>
            </div>
            <form class="admin-form" method="POST">
                <input type="hidden" name="ExerciseID" value="<?php echo $exercise_id; ?>"><input type="hidden"
                    name="langID" value="es">
                <table>
                    <tr class="header-row">
                        <th class="top-header separator-right" colspan="<?php echo $has_cc ? 2 : 0; ?>"
                            style="<?php echo $has_cc ? '' : 'display:none'; ?>">Estado del Cliente</th>
                        <th class="top-header" colspan="8">Segmento del Cliente</th>
                        <td></td>
                        <th class="top-header" colspan="8">Segmento del Servidor</th>
                        <th class="top-header separator-left" colspan="<?php echo $has_cc ? 2 : 0; ?>"
                            style="<?php echo $has_cc ? '' : 'display:none'; ?>">Estado del Servidor</th>
                    </tr>
                    <tr>
                        <?php if ($has_cc): ?>
                            <th class="bottom-header">CWND</th>
                            <th class="bottom-header separator-right">Mode</th>
                        <?php endif; ?>
                        <th class="bottom-header">SN</th>
                        <th class="bottom-header">AN</th>
                        <th class="bottom-header">SYN</th>
                        <th class="bottom-header">ACK</th>
                        <th class="bottom-header">FIN</th>
                        <th class="bottom-header">W</th>
                        <th class="bottom-header">MSS</th>
                        <th class="bottom-header">Data Len</th>
                        <td></td>
                        <th class="bottom-header">SN</th>
                        <th class="bottom-header">AN</th>
                        <th class="bottom-header">SYN</th>
                        <th class="bottom-header">ACK</th>
                        <th class="bottom-header">FIN</th>
                        <th class="bottom-header">W</th>
                        <th class="bottom-header">MSS</th>
                        <th class="bottom-header">Data Len</th>
                        <?php if ($has_cc): ?>
                            <th class="bottom-header separator-left">CWND</th>
                            <th class="bottom-header">Mode</th>
                        <?php endif; ?>
                    </tr>
                    <?php for ($x = 1; $x <= 15; $x++):
                        $c = ($x - 1) * 2;
                        $s = ($x - 1) * 2 + 1;
                        $np = "pattern='[0-9]+(\.[0-9]+)?'";
                        $bp = "pattern='[01]?'";

                        // Indices in arraySeg (based on [SN, AN, SYN, ACK, FIN, W, MSS, datalen, cwnd, tcp_mode])
                        // SN=0, AN=1, SYN=2, ACK=3, FIN=4, W=5, MSS=6, LEN=7, CWND=8, MODE=9
                        ?>
                        <tr>
                            <?php if ($has_cc): ?>
                                <td><input type="text" <?php echo $np ?> class="state-input" side="client"
                                        name="c<?php echo $x ?>-cwnd" size="5" value="<?php echo gv($arraySeg, $c, 8) ?>"></td>
                                <td class="separator-right">
                                    <select class="state-input" side="client" name="c<?php echo $x ?>-mode">
                                        <option value=""></option>
                                        <option value="SS" <?php if (gv($arraySeg, $c, 9) == 'SS')
                                            echo 'selected'; ?>>SS</option>
                                        <option value="CA" <?php if (gv($arraySeg, $c, 9) == 'CA')
                                            echo 'selected'; ?>>CA</option>
                                        <option value="FR" <?php if (gv($arraySeg, $c, 9) == 'FR')
                                            echo 'selected'; ?>>FR</option>
                                    </select>
                                </td>
                            <?php endif; ?>

                            <td><input type="text" <?php echo $np ?> side="client" name="c<?php echo $x ?>-sn" size="5"
                                    value="<?php echo gv($arraySeg, $c, 0) ?>"></td>
                            <td><input type="text" <?php echo $np ?> side="client" name="c<?php echo $x ?>-an" size="5"
                                    value="<?php echo gv($arraySeg, $c, 1) ?>"></td>
                            <td><input type="text" <?php echo $bp ?> side="client" name="c<?php echo $x ?>-syn" size="1"
                                    value="<?php echo gv($arraySeg, $c, 2) ?>"></td>
                            <td><input type="text" <?php echo $bp ?> side="client" name="c<?php echo $x ?>-ack" size="1"
                                    value="<?php echo gv($arraySeg, $c, 3) ?>"></td>
                            <td><input type="text" <?php echo $bp ?> side="client" name="c<?php echo $x ?>-fin" size="1"
                                    value="<?php echo gv($arraySeg, $c, 4) ?>"></td>
                            <td><input type="text" <?php echo $np ?> side="client" name="c<?php echo $x ?>-w" size="5"
                                    value="<?php echo gv($arraySeg, $c, 5) ?>"></td>
                            <td><input type="text" <?php echo $np ?> side="client" name="c<?php echo $x ?>-mss" size="5"
                                    value="<?php echo gv($arraySeg, $c, 6) ?>"></td>
                            <td><input type="text" <?php echo $np ?> side="client" name="c<?php echo $x ?>-datalen" size="5"
                                    value="<?php echo gv($arraySeg, $c, 7) ?>"></td>

                            <td class="ticktemplate"></td>

                            <td><input type="text" <?php echo $np ?> side="server" name="s<?php echo $x ?>-sn" size="5"
                                    value="<?php echo gv($arraySeg, $s, 0) ?>"></td>
                            <td><input type="text" <?php echo $np ?> side="server" name="s<?php echo $x ?>-an" size="5"
                                    value="<?php echo gv($arraySeg, $s, 1) ?>"></td>
                            <td><input type="text" <?php echo $bp ?> side="server" name="s<?php echo $x ?>-syn" size="1"
                                    value="<?php echo gv($arraySeg, $s, 2) ?>"></td>
                            <td><input type="text" <?php echo $bp ?> side="server" name="s<?php echo $x ?>-ack" size="1"
                                    value="<?php echo gv($arraySeg, $s, 3) ?>"></td>
                            <td><input type="text" <?php echo $bp ?> side="server" name="s<?php echo $x ?>-fin" size="1"
                                    value="<?php echo gv($arraySeg, $s, 4) ?>"></td>
                            <td><input type="text" <?php echo $np ?> side="server" name="s<?php echo $x ?>-w" size="5"
                                    value="<?php echo gv($arraySeg, $s, 5) ?>"></td>
                            <td><input type="text" <?php echo $np ?> side="server" name="s<?php echo $x ?>-mss" size="5"
                                    value="<?php echo gv($arraySeg, $s, 6) ?>"></td>
                            <td><input type="text" <?php echo $np ?> side="server" name="s<?php echo $x ?>-datalen" size="5"
                                    value="<?php echo gv($arraySeg, $s, 7) ?>"></td>

                            <?php if ($has_cc): ?>
                                <td class="separator-left"><input type="text" <?php echo $np ?> class="state-input"
                                        side="server" name="s<?php echo $x ?>-cwnd" size="5"
                                        value="<?php echo gv($arraySeg, $s, 8) ?>"></td>
                                <td>
                                    <select class="state-input" side="server" name="s<?php echo $x ?>-mode">
                                        <option value=""></option>
                                        <option value="SS" <?php if (gv($arraySeg, $s, 9) == 'SS')
                                            echo 'selected'; ?>>SS</option>
                                        <option value="CA" <?php if (gv($arraySeg, $s, 9) == 'CA')
                                            echo 'selected'; ?>>CA</option>
                                        <option value="FR" <?php if (gv($arraySeg, $s, 9) == 'FR')
                                            echo 'selected'; ?>>FR</option>
                                    </select>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endfor; ?>
                </table><button type="submit">Guardar Solución</button>
            </form>
        </div>
    </div>
    <script src="../tcp.js?v=<?php echo time(); ?>"></script>
</body>

</html>