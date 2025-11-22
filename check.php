<html>

<head>
	<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body>
	<nav class="navbar">
		<h1>TCP Exercises - Results</h1>
	</nav>

	<div class="container">
		<div class="card">
			<?php
			// Use centralized database connection
			require 'db_connection.php';
			$conn = $pdo; // check.php uses $conn variable
			
			// Fetch congestion control flag
			$stmt = $conn->prepare("SELECT congestion_control FROM EnunTCP WHERE ExerciseID = :eid");
			$stmt->execute([':eid' => $_POST["ExerciseID"]]);
			$has_cc = ($stmt->fetchColumn() == 1);

			// Fetch Solution Data (Robust Loop 1-15)
			$arraySeg = array();

			for ($t = 1; $t <= 15; $t++) {
				foreach ([0, 1] as $sender) { // 0 = Client, 1 = Server
					// 1. Fetch Segment Data
					$segStmt = $conn->prepare("
						SELECT s.* 
						FROM Exercises e 
						JOIN Segments s ON e.SegmentID = s.ID 
						WHERE e.ExerciseID = :eid AND e.TicID = :tic AND e.Sender = :sender
					");
					$segStmt->execute([':eid' => $_POST["ExerciseID"], ':tic' => $t, ':sender' => $sender]);
					$s = $segStmt->fetch(PDO::FETCH_OBJ);

					// 2. Fetch State Data
					$stateStmt = $conn->prepare("
						SELECT cwnd, tcp_mode 
						FROM TcpState 
						WHERE ExerciseID = :eid AND TicID = :tic AND Sender = :sender
					");
					$stateStmt->execute([':eid' => $_POST["ExerciseID"], ':tic' => $t, ':sender' => $sender]);
					$state = $stateStmt->fetch(PDO::FETCH_OBJ);

					// 3. Prepare Values
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

					// 4. Add to Array (Respecting check.php's specific order)
					if ($sender == 0) { // Client
						// Order: CWND, Mode, SN, AN, SYN, ACK, FIN, W, MSS, DataLen
						$arraySeg[] = array($cwnd, $mode, $sn, $an, $syn, $ack, $fin, $w, $mss, $datalen);
					} else { // Server
						// Order: SN, AN, SYN, ACK, FIN, W, MSS, DataLen, CWND, Mode
						$arraySeg[] = array($sn, $an, $syn, $ack, $fin, $w, $mss, $datalen, $cwnd, $mode);
					}
				}
			}

			//Load received FORM as an array
			$postArray = array();

			for ($x = 1; $x <= 15; $x++) {
				// Helper to get POST val or NULL
				$getVal = function ($key) {
					if (!isset($_POST[$key]) || $_POST[$key] === "") {
						return "NULL";
					}
					return $_POST[$key];
				};

				// Client Row
				$c_cwnd = $getVal("c$x-cwnd");
				$c_mode = $getVal("c$x-mode");
				$c_sn = $getVal("c$x-sn");
				$c_an = $getVal("c$x-an");
				$c_syn = $getVal("c$x-syn");
				$c_ack = $getVal("c$x-ack");
				$c_fin = $getVal("c$x-fin");
				$c_w = $getVal("c$x-w");
				$c_mss = $getVal("c$x-mss");
				$c_datalen = $getVal("c$x-datalen");

				// Order: CWND, Mode, SN, AN, SYN, ACK, FIN, W, MSS, DataLen
				$postArray[] = array($c_cwnd, $c_mode, $c_sn, $c_an, $c_syn, $c_ack, $c_fin, $c_w, $c_mss, $c_datalen);

				// Server Row
				$s_sn = $getVal("s$x-sn");
				$s_an = $getVal("s$x-an");
				$s_syn = $getVal("s$x-syn");
				$s_ack = $getVal("s$x-ack");
				$s_fin = $getVal("s$x-fin");
				$s_w = $getVal("s$x-w");
				$s_mss = $getVal("s$x-mss");
				$s_datalen = $getVal("s$x-datalen");
				$s_cwnd = $getVal("s$x-cwnd");
				$s_mode = $getVal("s$x-mode");

				// Order: SN, AN, SYN, ACK, FIN, W, MSS, DataLen, CWND, Mode
				$postArray[] = array($s_sn, $s_an, $s_syn, $s_ack, $s_fin, $s_w, $s_mss, $s_datalen, $s_cwnd, $s_mode);
			}

			//Compare FORM and answer arrays
			$ticAux = 0;
			$senderAux = 0;

			echo "<table>
	<tr class=\"header-row\">";
			if ($has_cc) {
				echo "<th class=\"top-header separator-right\" colspan=\"2\">Estado del Cliente</th>";
			}
			echo "<th class=\"top-header\" colspan=\"8\">Segmento del Cliente</th>
		<td></td>
		<th class=\"top-header\" colspan=\"8\">Segmento del Servidor</th>";
			if ($has_cc) {
				echo "<th class=\"top-header separator-left\" colspan=\"2\">Estado del Servidor</th>";
			}
			echo "</tr>
	<tr>";
			if ($has_cc) {
				echo "<th class=\"bottom-header\">CWND</th>
		<th class=\"bottom-header separator-right\">Mode</th>";
			}
			echo "<th class=\"bottom-header\">SN</th>
		<th class=\"bottom-header\">AN</th>
		<th class=\"bottom-header\">SYN</th>
		<th class=\"bottom-header\">ACK</th>
		<th class=\"bottom-header\">FIN</th>
		<th class=\"bottom-header\">W</th>
		<th class=\"bottom-header\">MSS</th>
		<th class=\"bottom-header\">Data Len</th>
		<td></td>
		<th class=\"bottom-header\">SN</th>
		<th class=\"bottom-header\">AN</th>
		<th class=\"bottom-header\">SYN</th>
		<th class=\"bottom-header\">ACK</th>
		<th class=\"bottom-header\">FIN</th>
		<th class=\"bottom-header\">W</th>
		<th class=\"bottom-header\">MSS</th>
		<th class=\"bottom-header\">Data Len</th>";
			if ($has_cc) {
				echo "<th class=\"bottom-header separator-left\">CWND</th>
		<th class=\"bottom-header\">Mode</th>";
			}
			echo "</tr>
	<tr>";

			$errors = 0;

			while ($ticAux < 15) {
				for ($i = 0; $i < 10; $i++) {
					// Skip CWND/Mode if not congestion control
					if (!$has_cc) {
						if ($senderAux == 0 && ($i == 0 || $i == 1))
							continue;
						if ($senderAux == 1 && ($i == 8 || $i == 9))
							continue;
					}

					// Add separator classes to data cells
					$class = "";
					if ($senderAux == 0 && $i == 1)
						$class = " class=\"separator-right\""; // Client Mode
					if ($senderAux == 1 && $i == 8)
						$class = " class=\"separator-left\"";  // Server CWND
			
					// Determine if input is state
					$inputClass = "";
					if (($senderAux == 0 && $i < 2) || ($senderAux == 1 && $i >= 8)) {
						$inputClass = " class=\"state-input\"";
					}

					// Helper to compare values (handling decimals for CWND)
					$userVal = $postArray[2 * $ticAux + $senderAux][$i];
					$dbVal = $arraySeg[2 * $ticAux + $senderAux][$i];
					$isEqual = false;

					// Check if it's CWND (Client: index 0, Server: index 8)
					if (($senderAux == 0 && $i == 0) || ($senderAux == 1 && $i == 8)) {
						// Loose comparison for numbers (handles 2.5 == 2.50)
						if (is_numeric($userVal) && is_numeric($dbVal)) {
							$isEqual = (float) $userVal == (float) $dbVal;
						} else {
							$isEqual = $userVal == $dbVal;
						}
					} else {
						$isEqual = $userVal == $dbVal;
					}

					if ($senderAux == 0) {
						if ($errors >= 3) {
							echo "<td$class><input$inputClass disabled id=\"yellow\" type=\"text\" side=\"client\" size=\"5\" value=\"";
						} elseif (!$isEqual) {
							echo "<td$class><input$inputClass disabled id=\"red\" type=\"text\" side=\"client\" size=\"5\" value=\"";
							$errors++;
						} else
							echo "<td$class><input$inputClass disabled id=\"green\" type=\"text\" side=\"client\" size=\"5\" value=\"";
					} else {
						if ($errors >= 3) {
							echo "<td$class><input$inputClass disabled id=\"yellow\" type=\"text\" side=\"server\" size=\"5\" value=\"";
						} elseif (!$isEqual) {
							echo "<td$class><input$inputClass disabled id=\"red\" type=\"text\" side=\"server\" size=\"5\" value=\"";
							$errors++;
						} else
							echo "<td$class><input$inputClass disabled id=\"green\" type=\"text\" side=\"server\" size=\"5\" value=\"";
					}
					if ($postArray[2 * $ticAux + $senderAux][$i] == "NULL") {
						$value = "";
					} else {
						$value = htmlspecialchars($postArray[2 * $ticAux + $senderAux][$i], ENT_QUOTES, 'UTF-8');
					}
					echo $value . "\"></td>\n";
				}
				$senderAux = ($senderAux + 1) % 2;
				if ($senderAux == 0) {
					$ticAux = $ticAux + 1;
					echo "</tr><tr>\n";
				} else {
					$segStmt->execute([':eid' => $_POST["ExerciseID"], ':tic' => $t, ':sender' => $sender]);
					$s = $segStmt->fetch(PDO::FETCH_OBJ);

					// 2. Fetch State Data
					$stateStmt = $conn->prepare("
						SELECT cwnd, tcp_mode 
						FROM TcpState 
						WHERE ExerciseID = :eid AND TicID = :tic AND Sender = :sender
					");
					$stateStmt->execute([':eid' => $_POST["ExerciseID"], ':tic' => $t, ':sender' => $sender]);
					$state = $stateStmt->fetch(PDO::FETCH_OBJ);

					// 3. Prepare Values
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

					// 4. Add to Array (Respecting check.php's specific order)
					if ($sender == 0) { // Client
						// Order: CWND, Mode, SN, AN, SYN, ACK, FIN, W, MSS, DataLen
						$arraySeg[] = array($cwnd, $mode, $sn, $an, $syn, $ack, $fin, $w, $mss, $datalen);
					} else { // Server
						// Order: SN, AN, SYN, ACK, FIN, W, MSS, DataLen, CWND, Mode
						$arraySeg[] = array($sn, $an, $syn, $ack, $fin, $w, $mss, $datalen, $cwnd, $mode);
					}
				}
			}

			//Load received FORM as an array
			$postArray = array();

			for ($x = 1; $x <= 15; $x++) {
				// Helper to get POST val or NULL
				$getVal = function ($key) {
					if (!isset($_POST[$key]) || $_POST[$key] === "") {
						return "NULL";
					}
					return $_POST[$key];
				};

				// Client Row
				$c_cwnd = $getVal("c$x-cwnd");
				$c_mode = $getVal("c$x-mode");
				$c_sn = $getVal("c$x-sn");
				$c_an = $getVal("c$x-an");
				$c_syn = $getVal("c$x-syn");
				$c_ack = $getVal("c$x-ack");
				$c_fin = $getVal("c$x-fin");
				$c_w = $getVal("c$x-w");
				$c_mss = $getVal("c$x-mss");
				$c_datalen = $getVal("c$x-datalen");

				// Order: CWND, Mode, SN, AN, SYN, ACK, FIN, W, MSS, DataLen
				$postArray[] = array($c_cwnd, $c_mode, $c_sn, $c_an, $c_syn, $c_ack, $c_fin, $c_w, $c_mss, $c_datalen);

				// Server Row
				$s_sn = $getVal("s$x-sn");
				$s_an = $getVal("s$x-an");
				$s_syn = $getVal("s$x-syn");
				$s_ack = $getVal("s$x-ack");
				$s_fin = $getVal("s$x-fin");
				$s_w = $getVal("s$x-w");
				$s_mss = $getVal("s$x-mss");
				$s_datalen = $getVal("s$x-datalen");
				$s_cwnd = $getVal("s$x-cwnd");
				$s_mode = $getVal("s$x-mode");

				// Order: SN, AN, SYN, ACK, FIN, W, MSS, DataLen, CWND, Mode
				$postArray[] = array($s_sn, $s_an, $s_syn, $s_ack, $s_fin, $s_w, $s_mss, $s_datalen, $s_cwnd, $s_mode);
			}

			//Compare FORM and answer arrays
			$ticAux = 0;
			$senderAux = 0;

			echo "<table>
	<tr class=\"header-row\">";
			if ($has_cc) {
				echo "<th class=\"top-header separator-right\" colspan=\"2\">Estado del Cliente</th>";
			}
			echo "<th class=\"top-header\" colspan=\"8\">Segmento del Cliente</th>
		<td></td>
		<th class=\"top-header\" colspan=\"8\">Segmento del Servidor</th>";
			if ($has_cc) {
				echo "<th class=\"top-header separator-left\" colspan=\"2\">Estado del Servidor</th>";
			}
			echo "</tr>
	<tr>";
			if ($has_cc) {
				echo "<th class=\"bottom-header\">CWND</th>
		<th class=\"bottom-header separator-right\">Mode</th>";
			}
			echo "<th class=\"bottom-header\">SN</th>
		<th class=\"bottom-header\">AN</th>
		<th class=\"bottom-header\">SYN</th>
		<th class=\"bottom-header\">ACK</th>
		<th class=\"bottom-header\">FIN</th>
		<th class=\"bottom-header\">W</th>
		<th class=\"bottom-header\">MSS</th>
		<th class=\"bottom-header\">Data Len</th>
		<td></td>
		<th class=\"bottom-header\">SN</th>
		<th class=\"bottom-header\">AN</th>
		<th class=\"bottom-header\">SYN</th>
		<th class=\"bottom-header\">ACK</th>
		<th class=\"bottom-header\">FIN</th>
		<th class=\"bottom-header\">W</th>
		<th class=\"bottom-header\">MSS</th>
		<th class=\"bottom-header\">Data Len</th>";
			if ($has_cc) {
				echo "<th class=\"bottom-header separator-left\">CWND</th>
		<th class=\"bottom-header\">Mode</th>";
			}
			echo "</tr>
	<tr>";

			$errors = 0;

			while ($ticAux < 15) {
				for ($i = 0; $i < 10; $i++) {
					// Skip CWND/Mode if not congestion control
					if (!$has_cc) {
						if ($senderAux == 0 && ($i == 0 || $i == 1))
							continue;
						if ($senderAux == 1 && ($i == 8 || $i == 9))
							continue;
					}

					// Add separator classes to data cells
					$class = "";
					if ($senderAux == 0 && $i == 1)
						$class = " class=\"separator-right\""; // Client Mode
					if ($senderAux == 1 && $i == 8)
						$class = " class=\"separator-left\"";  // Server CWND
			
					// Determine if input is state
					$inputClass = "";
					if (($senderAux == 0 && $i < 2) || ($senderAux == 1 && $i >= 8)) {
						$inputClass = " class=\"state-input\"";
					}

					// Helper to compare values (handling decimals for CWND)
					$userVal = $postArray[2 * $ticAux + $senderAux][$i];
					$dbVal = $arraySeg[2 * $ticAux + $senderAux][$i];
					$isEqual = false;

					// Check if it's CWND (Client: index 0, Server: index 8)
					if (($senderAux == 0 && $i == 0) || ($senderAux == 1 && $i == 8)) {
						// Loose comparison for numbers (handles 2.5 == 2.50)
						if (is_numeric($userVal) && is_numeric($dbVal)) {
							$isEqual = (float) $userVal == (float) $dbVal;
						} else {
							$isEqual = $userVal == $dbVal;
						}
					} else {
						$isEqual = $userVal == $dbVal;
					}

					if ($senderAux == 0) {
						if ($errors >= 3) {
							echo "<td$class><input$inputClass disabled id=\"yellow\" type=\"text\" side=\"client\" size=\"5\" value=\"";
						} elseif (!$isEqual) {
							echo "<td$class><input$inputClass disabled id=\"red\" type=\"text\" side=\"client\" size=\"5\" value=\"";
							$errors++;
						} else
							echo "<td$class><input$inputClass disabled id=\"green\" type=\"text\" side=\"client\" size=\"5\" value=\"";
					} else {
						if ($errors >= 3) {
							echo "<td$class><input$inputClass disabled id=\"yellow\" type=\"text\" side=\"server\" size=\"5\" value=\"";
						} elseif (!$isEqual) {
							echo "<td$class><input$inputClass disabled id=\"red\" type=\"text\" side=\"server\" size=\"5\" value=\"";
							$errors++;
						} else
							echo "<td$class><input$inputClass disabled id=\"green\" type=\"text\" side=\"server\" size=\"5\" value=\"";
					}
					if ($postArray[2 * $ticAux + $senderAux][$i] == "NULL") {
						$value = "";
					} else {
						$value = htmlspecialchars($postArray[2 * $ticAux + $senderAux][$i], ENT_QUOTES, 'UTF-8');
					}
					echo $value . "\"></td>\n";
				}
				$senderAux = ($senderAux + 1) % 2;
				if ($senderAux == 0) {
					$ticAux = $ticAux + 1;
					echo "</tr><tr>\n";
				} else {
					echo "<td class=\"ticktemplate\"></td>\n";
				}
			}
			echo "</tr> </table>";

			$lang = (isset($_POST['langID']) && $_POST['langID'] === 'en') ? 'en' : 'es';
			if ($lang == 'en') {
				include("locale/en.php");
			} else {
				include("locale/es.php");
			}

			echo "<div style='margin-top: 2rem;'>";
			if ($errors == 0)
				echo "<div class='result-banner result-success'><h2>" . $langArray['correct_answer'] . "</h2></div>";
			elseif ($errors == 1)
				echo "<div class='result-banner result-warning'><h3>" . $langArray['one_error'] . "</h3></div>";
			elseif ($errors < 3)
				echo "<div class='result-banner result-warning'><h3>" . str_replace('{n}', $errors, $langArray['n_errors']) . "</h3></div>";
			else
				echo "<div class='result-banner result-error'><h3>" . $langArray['many_errors'] . "</h3></div>";
			echo "</div>";
			?>

			<p style="text-align: center; margin-top: 2rem;">
				<a href="index.php?langID=<?php echo htmlspecialchars($lang); ?>">←
					<?php echo $langArray['back_to_index']; ?></a>
			</p>
		</div> <!-- End card -->
	</div> <!-- End container -->
	<script src="tcp.js?v=<?php echo time(); ?>"></script>
</body>

</html>
