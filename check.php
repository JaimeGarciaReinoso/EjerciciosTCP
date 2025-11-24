<?php
session_start();
require 'db_connection.php';
$conn = $pdo;

// --- Rate Limiting Logic ---
if (isset($_POST['ExerciseID'])) {
	$eid = $_POST['ExerciseID'];

	// Initialize or Reset if switching exercises
	if (!isset($_SESSION['current_eid']) || $_SESSION['current_eid'] != $eid) {
		$_SESSION['current_eid'] = $eid;
		$_SESSION['attempts'] = 0;
		$_SESSION['last_attempt_time'] = 0;
	}

	$attempts = $_SESSION['attempts'];
	$lastTime = $_SESSION['last_attempt_time'];
	$currentTime = time();

	// Calculate required delay: max(0, (attempts - 1) * 10)
	$delay = max(0, ($attempts - 1) * 10);

	$timeSinceLast = $currentTime - $lastTime;

	if ($timeSinceLast < $delay) {
		$wait = $delay - $timeSinceLast;
		$lang = (isset($_POST['langID']) && $_POST['langID'] === 'en') ? 'en' : 'es';
		$msg = ($lang === 'en')
			? "Please wait $wait seconds before trying again."
			: "Por favor, espera $wait segundos antes de volver a intentar.";

		echo "<html><head><link rel='stylesheet' href='style.css'></head><body><div class='container'><div class='card'>";
		echo "<div class='result-banner result-error' style='background-color: #fff3cd; color: #856404; border-color: #ffeeba;'>";
		echo "<h2>⏳ " . $msg . "</h2>";
		echo "</div>";
		echo "<p style='text-align: center; margin-top: 20px;'><a href='javascript:history.back()' class='btn-solve'>Volver / Back</a></p>";
		echo "</div></div></body></html>";
		exit;
	}

	// Update history
	$_SESSION['attempts']++;
	$_SESSION['last_attempt_time'] = $currentTime;
}

// Fetch congestion control flag
$stmt = $conn->prepare("SELECT congestion_control FROM EnunTCP WHERE ExerciseID = :eid");
$stmt->execute([':eid' => $_POST["ExerciseID"]]);
$has_cc = ($stmt->fetchColumn() == 1);

// Fetch Solution Data
$arraySeg = array();
for ($t = 1; $t <= 15; $t++) {
	foreach ([0, 1] as $sender) {
		$segStmt = $conn->prepare("SELECT s.* FROM Exercises e JOIN Segments s ON e.SegmentID = s.ID WHERE e.ExerciseID = :eid AND e.TicID = :tic AND e.Sender = :sender");
		$segStmt->execute([':eid' => $_POST["ExerciseID"], ':tic' => $t, ':sender' => $sender]);
		$s = $segStmt->fetch(PDO::FETCH_OBJ);

		$stateStmt = $conn->prepare("SELECT cwnd, tcp_mode FROM TcpState WHERE ExerciseID = :eid AND TicID = :tic AND Sender = :sender");
		$stateStmt->execute([':eid' => $_POST["ExerciseID"], ':tic' => $t, ':sender' => $sender]);
		$state = $stateStmt->fetch(PDO::FETCH_OBJ);

		$sn = $s ? $s->SN : "NULL";
		$an = $s ? $s->AN : "NULL";
		$syn = $s ? $s->SYN : "NULL";
		$ack = $s ? $s->ACK : "NULL";
		$fin = $s ? $s->FIN : "NULL";
		$w = $s ? $s->W : "NULL";
		$mss = ($s && !empty($s->MSS)) ? $s->MSS : "NULL";
		$datalen = $s ? $s->datalen : "NULL";
		$cwnd = ($state && !empty($state->cwnd)) ? $state->cwnd : "NULL";
		$mode = ($state && !empty($state->tcp_mode)) ? $state->tcp_mode : "NULL";

		if ($sender == 0) {
			$arraySeg[] = array($cwnd, $mode, $sn, $an, $syn, $ack, $fin, $w, $mss, $datalen);
		} else {
			$arraySeg[] = array($sn, $an, $syn, $ack, $fin, $w, $mss, $datalen, $cwnd, $mode);
		}
	}
}

// Load POST data
$postArray = array();
for ($x = 1; $x <= 15; $x++) {
	$getVal = function ($key) {
		return (!isset($_POST[$key]) || $_POST[$key] === "") ? "NULL" : $_POST[$key]; };

	$postArray[] = array(
		$getVal("c$x-cwnd"),
		$getVal("c$x-mode"),
		$getVal("c$x-sn"),
		$getVal("c$x-an"),
		$getVal("c$x-syn"),
		$getVal("c$x-ack"),
		$getVal("c$x-fin"),
		$getVal("c$x-w"),
		$getVal("c$x-mss"),
		$getVal("c$x-datalen")
	);

	$postArray[] = array(
		$getVal("s$x-sn"),
		$getVal("s$x-an"),
		$getVal("s$x-syn"),
		$getVal("s$x-ack"),
		$getVal("s$x-fin"),
		$getVal("s$x-w"),
		$getVal("s$x-mss"),
		$getVal("s$x-datalen"),
		$getVal("s$x-cwnd"),
		$getVal("s$x-mode")
	);
}

$lang = (isset($_POST['langID']) && $_POST['langID'] === 'en') ? 'en' : 'es';
?>
<html>

<head>
	<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body>
	<nav class="navbar">
		<h1><?php echo ($lang === 'en') ? 'TCP Exercises - Results' : 'Ejercicios TCP - Resultados'; ?></h1>
	</nav>

	<div class="container">
		<div class="card">
			<table>
				<tr class="header-row">
					<?php if ($has_cc): ?>
						<th class="top-header separator-right" colspan="2">
							<?php echo ($lang === 'en') ? 'Client State' : 'Estado del Cliente'; ?></th>
					<?php endif; ?>
					<th class="top-header" colspan="8">
						<?php echo ($lang === 'en') ? 'Client Segment' : 'Segmento del Cliente'; ?></th>
					<td></td>
					<th class="top-header" colspan="8">
						<?php echo ($lang === 'en') ? 'Server Segment' : 'Segmento del Servidor'; ?></th>
					<?php if ($has_cc): ?>
						<th class="top-header separator-left" colspan="2">
							<?php echo ($lang === 'en') ? 'Server State' : 'Estado del Servidor'; ?></th>
					<?php endif; ?>
				</tr>
				<tr>
					<?php if ($has_cc): ?>
						<th class="bottom-header"><?php echo ($lang === 'en') ? 'CWND' : 'VC'; ?></th>
						<th class="bottom-header separator-right"><?php echo ($lang === 'en') ? 'Mode' : 'Modo'; ?></th>
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
						<th class="bottom-header separator-left"><?php echo ($lang === 'en') ? 'CWND' : 'VC'; ?></th>
						<th class="bottom-header"><?php echo ($lang === 'en') ? 'Mode' : 'Modo'; ?></th>
					<?php endif; ?>
				</tr>

				<?php
				$ticAux = 0;
				$senderAux = 0;
				$errors = 0;

				echo "<tr>";
				while ($ticAux < 15) {
					for ($i = 0; $i < 10; $i++) {
						if (!$has_cc) {
							if ($senderAux == 0 && ($i == 0 || $i == 1))
								continue;
							if ($senderAux == 1 && ($i == 8 || $i == 9))
								continue;
						}

						$class = "";
						if ($senderAux == 0 && $i == 1)
							$class = " class=\"separator-right\"";
						if ($senderAux == 1 && $i == 8)
							$class = " class=\"separator-left\"";

						$inputClass = "";
						if (($senderAux == 0 && $i < 2) || ($senderAux == 1 && $i >= 8)) {
							$inputClass = " class=\"state-input\"";
						}

						$userVal = $postArray[2 * $ticAux + $senderAux][$i];
						$dbVal = $arraySeg[2 * $ticAux + $senderAux][$i];
						$isEqual = false;

						if (($senderAux == 0 && $i == 0) || ($senderAux == 1 && $i == 8)) {
							if (is_numeric($userVal) && is_numeric($dbVal)) {
								$isEqual = (float) $userVal == (float) $dbVal;
							} else {
								$isEqual = $userVal == $dbVal;
							}
						} else {
							$isEqual = $userVal == $dbVal;
						}

						$colorId = "green";
						if ($errors >= 3) {
							$colorId = "yellow";
						} elseif (!$isEqual) {
							$colorId = "red";
							$errors++;
						}

						$value = ($userVal == "NULL") ? "" : $userVal;
						echo "<td$class><input$inputClass disabled id=\"$colorId\" type=\"text\" size=\"5\" value=\"$value\"></td>";
					}

					$senderAux = ($senderAux + 1) % 2;
					if ($senderAux == 0) {
						$ticAux++;
						echo "</tr><tr>\n";
					} else {
						echo "<td class=\"ticktemplate\"></td>\n";
					}
				}
				echo "</tr>";
				?>
			</table>

			<?php
			if ($lang == 'en') {
				include("locale/en.php");
			} else {
				include("locale/es.php");
			}

			// Log stats
			try {
				$logStmt = $conn->prepare("INSERT INTO ExerciseStats (exercise_id, is_correct, error_count) VALUES (:eid, :correct, :errors)");
				$logStmt->execute([':eid' => $eid, ':correct' => ($errors == 0 ? 1 : 0), ':errors' => $errors]);
			} catch (Exception $e) {
			}

			echo "<div style='margin-top: 2rem;'>";
			if ($errors == 0)
				echo "<div class='result-banner result-success'><h2>" . $langArray['correct_answer'] . "</h2></div>";
			elseif ($errors == 1)
				echo "<div class='result-banner result-error'><h2>" . $langArray['one_error'] . "</h2></div>";
			else
				echo "<div class='result-banner result-error'><h2>" . str_replace('{n}', $errors, $langArray['n_errors']) . "</h2></div>";
			echo "</div>";
			?>

			<p style="text-align: center; margin-top: 20px;">
				<a href="javascript:history.back()"
					class="btn-solve"><?php echo ($lang === 'en' ? 'Go Back' : 'Volver'); ?></a>
			</p>
			<p style="text-align: center; margin-top: 15px;">
				<a href="index.php"
					style="color: #0056b3; text-decoration: underline;"><?php echo $langArray['back_to_index']; ?></a>
			</p>
		</div>
	</div>
</body>

</html>