<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>


<form name="langSelect" action="" method="get">
     <select name="langID" id="langID">
         <option>Select Language</option>
         <option value="en">English</option>
         <option value="es">Spanish</option>
    </select>

    <br><br>

    <button type="submit">Submit</button>
</form>


<?php

       $lang = $_GET['langID'] ?? 'en';

        include('locale/'. $lang . '.php');

       echo $langArray['header'];
       echo "<div class=\"tips\">";
       echo $langArray['notes'];
        echo "</div><br><div class=\"ex\">";
      echo $langArray['enun'];
       echo "</div><br><div class=\"authors\">";
       echo $langArray['authors'];
       echo "</div>";


?>


</body>

</html>
