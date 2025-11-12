<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<a href="index.php?langID=es">Español</a>
<a href="index.php?langID=en">English</a>

<?php

       // SECURITY: lang must be a valid language
		  $allowed_langs = ['es', 'en'];
		  $lang = $_GET['langID'] ?? 'es';

		  if (!in_array($lang, $allowed_langs)) {
    		$lang = 'es';
		  }

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
