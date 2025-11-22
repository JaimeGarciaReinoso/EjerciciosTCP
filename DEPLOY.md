# Guía de Despliegue - Ejercicios TCP

Esta guía detalla los pasos para desplegar la aplicación "Ejercicios TCP" en un servidor web estándar con PHP, MySQL/MariaDB y phpMyAdmin.

## Requisitos Previos

*   **Servidor Web**: Apache o Nginx.
*   **PHP**: Versión 7.4 o superior (recomendado 8.0+).
    *   Extensión `pdo_mysql` habilitada.
*   **Base de Datos**: MySQL 5.7+ o MariaDB 10.3+.
*   **phpMyAdmin**: Instalado y accesible (opcional, pero recomendado para gestionar la BBDD).

## Paso 1: Preparar la Base de Datos

1.  Accede a **phpMyAdmin** o a tu cliente de base de datos preferido.
2.  Crea una nueva base de datos (por ejemplo, `tcp_exercises`).
    *   Cotejamiento (Collation) recomendado: `utf8mb4_unicode_ci` o `latin1_swedish_ci` (según tu configuración original).
3.  Crea un usuario de base de datos y asígnale todos los privilegios sobre la nueva base de datos.
4.  **Importar la Estructura y Datos**:
    *   En phpMyAdmin, selecciona la base de datos creada.
    *   Ve a la pestaña **Importar**.
    *   **Paso A (Estructura)**: Sube e importa el archivo `schema.sql`. Esto creará todas las tablas vacías.
    *   **Paso B (Datos)**: Si dispones de un archivo de datos SQL con los ejercicios, impórtalo a continuación para poblar las tablas. Si no, la aplicación funcionará pero sin ejercicios cargados.

## Paso 2: Subir los Archivos

Sube **ÚNICAMENTE** los siguientes archivos y carpetas al directorio público de tu servidor web (por ejemplo, `/var/www/html/` o `public_html`):

### Archivos y Carpetas Necesarios (en directorio público):
*   `admin/` (Carpeta completa)
*   `locale/` (Carpeta completa)
*   `check.php`
*   `db_connection.php`
*   `form.php`
*   `index.php`
*   `style.css`
*   `tcp.js`

### Archivos de Configuración (FUERA del directorio público):
*   `credencialesDB.env` (Súbelo un nivel por encima de `public_html` o `/var/www/html`)

### Archivos de Base de Datos:
*   `schema.sql` (Estructura de la base de datos - **PÚBLICO**)

### NO Subir (Archivos de desarrollo o PRIVADOS):
*   `SolucionesBBDD/`
*   `.git/`
*   `.DS_Store`
*   `docker-compose.yml`
*   `restore_data.sql`
*   `import_data.sql`
*   Cualquier archivo `.sql` que contenga datos sensibles (ej. volcados de base de datos completos).
*   `debug_*.php`
*   `form*.html` (Son versiones antiguas estáticas)
*   `index_old.html`
*   `LICENSE`
*   `*.svg` (Las flechas ya están incluidas dentro de `style.css`)

## Paso 3: Configurar la Conexión Segura

1.  Sube el archivo `credencialesDB.env` a un directorio **fuera** del acceso público (por ejemplo, en `/var/www/` si tu web está en `/var/www/html/`).
    *   *Nota*: `db_connection.php` está configurado para buscar este archivo automáticamente en el directorio padre (`../credencialesDB.env`).
2.  Edítalo con los datos de tu base de datos de producción:

```php
<?php
$servername = "localhost"; // O la IP de tu servidor de BBDD
$username = "tu_usuario_bbdd";
$password = "tu_contraseña_bbdd";
$db = "tcp_exercises"; // El nombre de la base de datos que creaste
?>
```

## Paso 4: Configurar Contraseña de Administrador

1.  Por seguridad, el archivo `admin/login.php` viene con un hash de contraseña falso. Debes generar uno nuevo.
2.  Ejecuta este comando en tu terminal (o usa una herramienta online de BCrypt) para generar el hash de tu contraseña deseada:
    ```bash
    php -r 'echo password_hash("TU_CONTRASEÑA_AQUI", PASSWORD_BCRYPT, ["cost" => 12]);'
    ```
3.  Copia el hash resultante (empieza por `$2y$12$...`).
4.  Edita el archivo `admin/login.php` en tu servidor y reemplaza `CHANGE_THIS_HASH_IN_PRODUCTION` con tu nuevo hash.

## Paso 5: Verificación

1.  Accede a la URL de tu sitio web (ej. `http://tuservidor.com/index.php`).
2.  Deberías ver la lista de ejercicios.
3.  Entra en un ejercicio y verifica que carga correctamente.
4.  Prueba a introducir valores y verificar que las flechas se dibujan (Azul para cliente, Roja para servidor).
5.  Accede al panel de administración en `/admin/login.php` e inicia sesión con tu nueva contraseña.

## Solución de Problemas comunes

*   **Error de conexión a la base de datos**: Verifica `credencialesDB.env`. Asegúrate de que el usuario tiene permisos.
*   **Error 404 en archivos estáticos**: Verifica que `style.css` y `tcp.js` están en la ruta correcta y que los permisos de archivo son legibles por el servidor web (generalmente `644` para archivos y `755` para directorios).
*   **Flechas no aparecen**: Limpia la caché de tu navegador (Ctrl+F5) para asegurar que carga la última versión de `tcp.js` y `style.css`.
