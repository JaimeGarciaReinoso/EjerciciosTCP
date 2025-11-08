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
  $conn = new PDO("mysql:host=$servername;dbname=$db;charset=utf8", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  //echo "Connected successfully<br>";
} catch(PDOException $e) {
  echo "Connection to the database failed: " . $e->getMessage();
}

//$ex stores all SegmentID, sender and tic of the exercise
$id = $_GET['id'];
$search = "SELECT * FROM EnunTCP WHERE ExerciseID=".$id;
$ex = $conn->prepare($search);
$ex->setFetchMode(PDO::FETCH_OBJ);
$ex->execute();
$result = $ex->fetch();



echo "<h2> <b>Ejercicio " .$result->ExerciseNum ." - Parte " .$result->ExercisePart." </b></h2>
	<p> ".$result->EnunText." </p>


<form action=\"check.php\" method=\"POST\">
	<input type=\"hidden\" id=\"ExerciseID\" name=\"ExerciseID\" value=\"" . $id . " \">

<table>
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
";

	for ($x=1;$x<=15;$x++) {
			echo "
	<tr>
		<td><input type=\"text\" side=\"client\" name=\"c".$x."-sn\" size=\"5\" ></td>
		<td><input type=\"text\" side=\"client\" name=\"c".$x."-an\" size=\"5\" ></td>
		<td><input type=\"text\" side=\"client\" name=\"c".$x."-syn\" size=\"1\" ></td>
		<td><input type=\"text\" side=\"client\" name=\"c".$x."-ack\" size=\"1\" ></td>
		<td><input type=\"text\" side=\"client\" name=\"c".$x."-fin\" size=\"1\" ></td>
		<td><input type=\"text\" side=\"client\" name=\"c".$x."-w\" size=\"5\" ></td>
		<td><input type=\"text\" side=\"client\" name=\"c".$x."-mss\" size=\"5\" ></td>
		<td><input type=\"text\" side=\"client\" name=\"c".$x."-datalen\" size=\"5\" ></td>
		<td class=\"ticktemplate\"></td>
		<td><input type=\"text\" side=\"server\" name=\"s".$x."-sn\" size=\"5\" ></td>
		<td><input type=\"text\" side=\"server\" name=\"s".$x."-an\" size=\"5\" ></td>
		<td><input type=\"text\" side=\"server\" name=\"s".$x."-syn\" size=\"1\" ></td>
		<td><input type=\"text\" side=\"server\" name=\"s".$x."-ack\" size=\"1\" ></td>
		<td><input type=\"text\" side=\"server\" name=\"s".$x."-fin\" size=\"1\" ></td>
		<td><input type=\"text\" side=\"server\" name=\"s".$x."-w\" size=\"5\" ></td>
		<td><input type=\"text\" side=\"server\" name=\"s".$x."-mss\" size=\"5\" ></td>
		<td><input type=\"text\" side=\"server\" name=\"s".$x."-datalen\" size=\"5\" ><br></td>
	</tr>
";
	}
?>

<input type="submit">
</form>

</table>
<script src="tcp.js"></script>
</body>
</html>

