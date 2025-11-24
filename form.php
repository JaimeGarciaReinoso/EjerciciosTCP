<html>

<head>
	<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body>

	<nav class="navbar">
		<h1><?php echo (isset($_GET['langID']) && $_GET['langID'] === 'en') ? 'TCP Exercises' : 'Ejercicios TCP'; ?>
		</h1>
	</nav>

	<div class="container">
		<div class="card">

			<?php
			// Use centralized database connection
			require 'db_connection.php';
			$conn = $pdo; // form.php uses $conn variable
			
			//$ex stores all SegmentID, sender and tic of the exercise
			$id = (int) ($_GET['id'] ?? 0);
			$lang = (isset($_GET['langID']) && $_GET['langID'] === 'en') ? 'en' : 'es';
			$textColumn = ($lang == 'en') ? 'EnunTextEN' : 'EnunTextES';

			$search = "SELECT ExerciseNum, ExercisePart, $textColumn as EnunText, congestion_control FROM EnunTCP WHERE ExerciseID=" . $id;
			$ex = $conn->prepare($search);
			$ex->setFetchMode(PDO::FETCH_OBJ);
			$ex->execute();
			$result = $ex->fetch();

			if (!$result) {
				echo "<h2>Error: Exercise not found (ID: $id)</h2>";
			} else {
				$has_cc = ($result->congestion_control == 1);

				echo "<h2> <b>" . ($lang == 'en' ? 'Exercise ' : 'Ejercicio ') . $result->ExerciseNum . " - " . ($lang == 'en' ? 'Part ' : 'Parte ') . $result->ExercisePart . " </b></h2>
	            <p> " . $result->EnunText . " </p>";
			}
			?>

			<form action="check.php" method="POST">
				<input type="hidden" id="ExerciseID" name="ExerciseID" value="<?php echo $id; ?>">
				<input type="hidden" name="langID" value="<?php echo htmlspecialchars($lang); ?>">

				<div style="text-align: center; margin-bottom: 15px;">
					<input type="submit"
						value="<?php echo ($lang === 'en') ? 'Check Answer' : 'Comprobar Respuesta'; ?>"
						class="btn-solve">
				</div>

				<table>
					<tr class="header-row">
						<th class="top-header separator-right"
							colspan="<?php echo (isset($has_cc) && $has_cc) ? 2 : 0; ?>"
							style="<?php echo (isset($has_cc) && $has_cc) ? '' : 'display:none'; ?>">
							<?php echo ($lang === 'en') ? 'Client State' : 'Estado del Cliente'; ?>
						</th>
						<th class="top-header" colspan="8">
							<?php echo ($lang === 'en') ? 'Client Segment' : 'Segmento del Cliente'; ?></th>
						<td></td>
						<th class="top-header" colspan="8">
							<?php echo ($lang === 'en') ? 'Server Segment' : 'Segmento del Servidor'; ?></th>
						<th class="top-header separator-left"
							colspan="<?php echo (isset($has_cc) && $has_cc) ? 2 : 0; ?>"
							style="<?php echo (isset($has_cc) && $has_cc) ? '' : 'display:none'; ?>">
							<?php echo ($lang === 'en') ? 'Server State' : 'Estado del Servidor'; ?>
						</th>
					</tr>
					<tr>
						<?php if (isset($has_cc) && $has_cc): ?>
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
						<?php if (isset($has_cc) && $has_cc): ?>
							<th class="bottom-header separator-left"><?php echo ($lang === 'en') ? 'CWND' : 'VC'; ?></th>
							<th class="bottom-header"><?php echo ($lang === 'en') ? 'Mode' : 'Modo'; ?></th>
						<?php endif; ?>
					</tr>

					<?php
					for ($x = 1; $x <= 15; $x++) {
						// Regla para números (vacío o cualquier dígito) -> Se usa en MSS
						$num_pattern = "pattern=\"[0-9]*\" title=\"Solo números o vacío\"";
						// Regla para decimales (CWND)
						$decimal_pattern = "pattern=\"[0-9]+(\.[0-9]+)?\" title=\"Número entero o decimal (usando punto)\"";
						// Regla para bits (vacío, 0, o 1)
						$bit_pattern = "pattern=\"[01]?\" title=\"Solo 0, 1 o vacío\"";

						echo "<tr>";

						if (isset($has_cc) && $has_cc) {
							echo "
                            <td><input type=\"text\" $decimal_pattern class=\"state-input\" side=\"client\" name=\"c" . $x . "-cwnd\" size=\"5\" ></td>
                            <td class=\"separator-right\">
                                <select class=\"state-input\" side=\"client\" name=\"c" . $x . "-mode\">
                                    <option value=\"\"></option>
                                    <option value=\"SS\">SS</option>
                                    <option value=\"CA\">CA</option>
                                    <option value=\"FR\">FR</option>
                                </select>
                            </td>";
						}

						echo "
                        <td><input type=\"text\" $num_pattern side=\"client\" name=\"c" . $x . "-sn\" size=\"5\" ></td>
                        <td><input type=\"text\" $num_pattern side=\"client\" name=\"c" . $x . "-an\" size=\"5\" ></td>
                        <td><input type=\"text\" $bit_pattern side=\"client\" name=\"c" . $x . "-syn\" size=\"1\" ></td>
                        <td><input type=\"text\" $bit_pattern side=\"client\" name=\"c" . $x . "-ack\" size=\"1\" ></td>
                        <td><input type=\"text\" $bit_pattern side=\"client\" name=\"c" . $x . "-fin\" size=\"1\" ></td>
                        <td><input type=\"text\" $num_pattern side=\"client\" name=\"c" . $x . "-w\" size=\"5\" ></td>
                        <td><input type=\"text\" $num_pattern side=\"client\" name=\"c" . $x . "-mss\" size=\"5\" ></td> 
                        <td><input type=\"text\" $num_pattern side=\"client\" name=\"c" . $x . "-datalen\" size=\"5\" ></td>
                        <td class=\"ticktemplate\"></td>
                        <td><input type=\"text\" $num_pattern side=\"server\" name=\"s" . $x . "-sn\" size=\"5\" ></td>
                        <td><input type=\"text\" $num_pattern side=\"server\" name=\"s" . $x . "-an\" size=\"5\" ></td>
                        <td><input type=\"text\" $bit_pattern side=\"server\" name=\"s" . $x . "-syn\" size=\"1\" ></td>
                        <td><input type=\"text\" $bit_pattern side=\"server\" name=\"s" . $x . "-ack\" size=\"1\" ></td>
                        <td><input type=\"text\" $bit_pattern side=\"server\" name=\"s" . $x . "-fin\" size=\"1\" ></td>
                        <td><input type=\"text\" $num_pattern side=\"server\" name=\"s" . $x . "-w\" size=\"5\" ></td>
                        <td><input type=\"text\" $num_pattern side=\"server\" name=\"s" . $x . "-mss\" size=\"5\" ></td> 
                        <td><input type=\"text\" $num_pattern side=\"server\" name=\"s" . $x . "-datalen\" size=\"5\" ></td>";

						if (isset($has_cc) && $has_cc) {
							echo "
                            <td class=\"separator-left\"><input type=\"text\" $decimal_pattern class=\"state-input\" side=\"server\" name=\"s" . $x . "-cwnd\" size=\"5\" ></td>
                            <td>
                                <select class=\"state-input\" side=\"server\" name=\"s" . $x . "-mode\">
                                    <option value=\"\"></option>
                                    <option value=\"SS\">SS</option>
                                    <option value=\"CA\">CA</option>
                                    <option value=\"FR\">FR</option>
                                </select>
                            </td>";
						}
						echo "</tr>";
					}
					?>
				</table>
			</form>

		</div> <!-- End card -->
	</div> <!-- End container -->
	<script src="tcp.js?v=<?php echo time(); ?>"></script>
</body>

</html>