<?php
$lang = 'es';
if (isset($_GET['langID']) && $_GET['langID'] === 'en') {
    $lang = 'en';
}

if ($lang == 'en') {
    include("locale/en.php");
} else {
    include("locale/es.php");
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejercicios TCP</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>

<body>

    <nav class="navbar">
        <div class="nav-content">
            <h1>TCP Exercises</h1>
            <div class="lang-switch">
                <a href="index.php?langID=es" class="<?php echo $lang == 'es' ? 'active' : ''; ?>">ES</a> |
                <a href="index.php?langID=en" class="<?php echo $lang == 'en' ? 'active' : ''; ?>">EN</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="header-section">
                <?php echo $langArray['header']; ?>
            </div>

            <div class="notes-section">
                <?php echo $langArray['notes']; ?>
            </div>

            <div class="exercises-section">
                <?php
                require_once 'db_connection.php';

                try {
                    $stmt = $pdo->query("SELECT * FROM menu_ejercicios WHERE habilitado = 1 ORDER BY orden ASC");
                    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $current_level = 0;
                    // Map types to levels: categoria=1, ejercicio=2, parte=3
                
                    echo "<ul>"; // Root list
                
                    foreach ($items as $item) {
                        $title = isset($langArray[$item['clave_idioma']]) ? $langArray[$item['clave_idioma']] : $item['clave_idioma'];

                        if ($item['tipo'] == 'categoria') {
                            // Close previous levels if needed
                            if ($current_level == 3)
                                echo "</ul></li></ul></li>";
                            if ($current_level == 2)
                                echo "</ul></li>";
                            if ($current_level == 1)
                                echo ""; // Just sibling
                
                            echo "<li>" . $title . "</li><ul>";
                            $current_level = 2; // Next items will be exercises (level 2) inside this category
                        } elseif ($item['tipo'] == 'ejercicio') {
                            // Close previous levels if needed
                            if ($current_level == 3)
                                echo "</ul></li>";
                            if ($current_level == 2)
                                echo ""; // Just sibling
                
                            echo "<li>" . $title . "</li><ul>";
                            $current_level = 3; // Next items will be parts (level 3) inside this exercise
                        } elseif ($item['tipo'] == 'parte') {
                            // Parts are always leaves
                            $part_text = isset($langArray['part']) ? $langArray['part'] : 'Parte';
                            $text = $part_text . " " . $item['part_num'];
                            echo "<li><a href=\"form.php?langID=$lang&id=" . $item['link_id'] . "\">" . $text . "</a></li>";
                            $current_level = 3;
                        }
                    }

                    // Close remaining open tags
                    if ($current_level == 3)
                        echo "</ul></li></ul></li>";
                    if ($current_level == 2)
                        echo "</ul></li>";
                    echo "</ul>";

                } catch (PDOException $e) {
                    echo "<p class='error'>Error loading menu: " . $e->getMessage() . "</p>";
                    // Fallback to static if DB fails
                    echo $langArray['enun'];
                }
                ?>
            </div>

            <div class="authors-section">
                <?php echo $langArray['authors']; ?>
            </div>
        </div>
    </div>

</body>

</html>
