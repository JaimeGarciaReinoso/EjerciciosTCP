<html>
	<head>
		<style>
			table, th {
			border: 1px solid black;
		}
		</style>
		<link rel="stylesheet" href="style.css">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	</head>
<body>
 <?php

require __DIR__ . '/credencialesDB.env';

try {
  $conn = new PDO("mysql:host=$servername;dbname=$db", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  //echo "Connected successfully<br>";
} catch(PDOException $e) {
  echo "Connection to the database failed: " . $e->getMessage();
}

//$ex stores all SegmentID, sender and tic of the exercise
$ex = $conn->prepare("SELECT * FROM Exercises WHERE ExerciseID= :eid");
$ex->setFetchMode(PDO::FETCH_OBJ);
$ex->execute(array(':eid' => $_POST["ExerciseID"]));

//echo "Exercise: " . $_POST["ExerciseID"] . " <br>";

//$arraySeg will include all segment information of the exercise and NULLs in case a <sender,tic> does not exist 
$arraySeg = array();
$tic = 1;
$sender = 0;

while ($row = $ex->fetch()){
	//tic=intval($row->TicID);
	//snd=intval($row->Sender);
	//ids=intval($row->SegmentID);

	$response = $conn->prepare('SELECT * FROM Segments WHERE ID= :sid');
	$response->setFetchMode(PDO::FETCH_OBJ);
	if ($response->execute(array(':sid' => $row->SegmentID)))
	{
		$seg = $response->fetch();
		//fill with NULLs in case segments do not exist in answers
		while (($tic != $row->TicID) || ($sender != $row->Sender)) {
			$arraySeg[] = array("NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL");
			$sender = ($sender + 1) % 2;
			if ($sender == 0) $tic = $tic + 1;
		}
		if (empty($seg->MSS))
			$seg->MSS="NULL";	
		$arraySeg[] = array($seg->SN,$seg->AN,$seg->SYN,$seg->ACK,$seg->FIN,$seg->W,$seg->MSS,$seg->datalen);
		$sender = ($sender + 1) % 2;
		if ($sender == 0) $tic = $tic + 1;
	}
	else 
		echo "ERROR";
	

}
//fill with NULL 'til tic 15, which is what we have in the forms
while ($tic < 16) {
	$arraySeg[] = array("NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL", "NULL");
	$sender = ($sender + 1) % 2;
	if ($sender == 0) $tic = $tic + 1;
}

//echo "ANSWER---------<br>";
//$tic = 0;
//$sender = 0;
//echo "Tic: $tic - Sender: $sender ::: ";
//foreach ($arraySeg as $seg) {
//	foreach ($seg as $field) {
//		echo $field . " ";
//	}
//	echo "<br>";
//	$sender = ($sender + 1) % 2;
//	if ($sender == 0) $tic = $tic + 1;
//	echo "Tic: $tic - Sender: $sender ::: ";
//}


//Load received FORM as an array
$aux = 0;
$exerciseIDAux = 0;
$postArray = array();
$auxArray = array();
foreach ($_POST as $key => $val) {
	if ($exerciseIDAux > 0) {
		$auxArray[] = $val;
		$aux = $aux + 1;
		if ($aux == 8) {
			$postArray[] = $auxArray;
			$auxArray = array();
			$aux = 0;
		}
	}
	else $exerciseIDAux = 1;	
}

//echo "Student's answer +++++++++++++++++ <br>";
//$tic = 0;
//$sender = 0;
//echo "Tic: $tic - Sender: $sender ::: ";
//foreach ($postArray as $seg) {
//	foreach ($seg as $field) {
//		echo $field . " ";
//	}
//	echo "<br>";
//	$sender = ($sender + 1) % 2;
//	if ($sender == 0) $tic = $tic + 1;
//	echo "Tic: $tic - Sender: $sender ::: ";
//}


//Compare FORM and answer arrays
//
$ticAux = 0;
$senderAux = 0;


echo "<table>
	<tr class=\"header-row\">
		<th class=\"top-header\" colspan=\"8\">Client</th>
		<td></td>
		<th class=\"top-header\" colspan=\"8\">Server</th>
	</tr>
	<tr>
		<th class=\"bottom-header\">SN</th>
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
		<th class=\"bottom-header\">Data Len</th>
	</tr>
	<tr>";

$errors=0;

while ($ticAux < 15) {
	for ($i = 0; $i < 8; $i++) {
	  if ($senderAux == 0) {
			if ($errors >=3) {
				echo "<td><input disabled id=\"yellow\" type=\"text\" side=\"client\" size=\"5\" value=\""; 
			} elseif ($postArray[2*$ticAux+$senderAux][$i] != $arraySeg[2*$ticAux+$senderAux][$i]) {
				echo "<td><input disabled id=\"red\" type=\"text\" side=\"client\" size=\"5\" value=\""; 
				$errors++;
			} else
				echo "<td><input disabled id=\"green\" type=\"text\" side=\"client\" size=\"5\" value=\""; 
		
	  } else {
			if ($errors >=3) {
				echo "<td><input disabled id=\"yellow\" type=\"text\" side=\"server\" size=\"5\" value=\""; 
			} elseif ($postArray[2*$ticAux+$senderAux][$i] != $arraySeg[2*$ticAux+$senderAux][$i]) {
				echo "<td><input disabled id=\"red\" type=\"text\" side=\"server\" size=\"5\" value=\""; 
				$errors++;
			} else
				echo "<td><input disabled id=\"green\" type=\"text\" side=\"server\" size=\"5\" value=\""; 
			}
		echo $postArray[2*$ticAux+$senderAux][$i] . "\"></td>\n";		
				//echo "Mismatch in tic = $ticAux, sender = $senderAux, field= $i | Student: " .  $postArray[2*$ticAux+$senderAux][$i] . " vs  Answer: " . $arraySeg[2*$ticAux+$senderAux][$i] . "<br>";
      
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
if ($errors == 0)
  echo "<h2>Congratulations! Everything is correct!</h2>";
elseif ($errors < 3)
  echo "<h3>Sorry, you have " . $errors . " error(s). <br>Try it again going back to the previous screen.</h3>";
else
  echo "<h3>You have three or more errors, so your answer is not completely corrected. <br>Try it again going back to the previous screen.</h3>";

?>
 
  <p>
    <h3>
      To go back to the main screen, click <a href="index.html">here</a>.
  </h3>
  </p>

<script src="tcp.js"></script>
</body>
</html>

