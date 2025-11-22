<?php
// 1. Producción: Intentar cargar desde el directorio padre (fuera de public_html)
// Esto es lo más seguro: el archivo no es accesible vía web.
if (file_exists(__DIR__ . '/../credencialesDB.env')) {
    require __DIR__ . '/../credencialesDB.env';
}
// 2. Desarrollo: Fallback al directorio actual
elseif (file_exists(__DIR__ . '/credencialesDB.env')) {
    require __DIR__ . '/credencialesDB.env';
} else {
    die("Error: No se encontró el archivo de configuración credencialesDB.env. Asegúrate de subirlo un nivel por encima de public_html (producción) o en la raíz (desarrollo).");
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$db;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>