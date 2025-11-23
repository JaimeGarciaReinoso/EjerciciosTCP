# Guía de Actualización a la Nueva Versión

Esta guía detalla los pasos necesarios para actualizar una instalación existente de "Ejercicios TCP" a la última versión, que incluye:
-   Autenticación de administradores en base de datos.
-   Estadísticas de uso y gráficos diarios.
-   Soporte para ejercicios de Control de Congestión.
-   Mejoras de seguridad y usabilidad en el panel de administración.

## 1. Copia de Seguridad (¡IMPORTANTE!)

Antes de realizar cualquier cambio, realice una copia de seguridad completa de su base de datos y archivos actuales.

```bash
# Ejemplo de backup de base de datos
mysqldump -u usuario -p tcp_exercises > backup_tcp_exercises_$(date +%F).sql
```

## 2. Actualización de Archivos

Suba todos los archivos de la nueva versión al servidor, sobrescribiendo los existentes. Asegúrese de incluir:
-   `admin/` (todo el directorio)
-   `locale/` (todo el directorio)
-   `check.php`
-   `db_connection.php`
-   `style.css`
-   `index.php`

> **Nota**: Si tiene un archivo `credencialesDB.env` personalizado, asegúrese de **NO** sobrescribirlo con el de ejemplo, o tenga a mano sus credenciales para restaurarlo.

## 3. Actualización de Base de Datos

Ejecute las siguientes consultas SQL en su base de datos (usando phpMyAdmin, CLI, o su herramienta preferida) para crear las nuevas tablas y modificar las existentes.

### 3.1. Añadir soporte para Control de Congestión
```sql
ALTER TABLE EnunTCP ADD COLUMN congestion_control tinyint(4) DEFAULT 0;
```

### 3.2. Crear tabla de Estadísticas
```sql
CREATE TABLE IF NOT EXISTS `ExerciseStats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exercise_id` int(11) NOT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_correct` tinyint(1) NOT NULL,
  `error_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `exercise_id` (`exercise_id`),
  CONSTRAINT `ExerciseStats_ibfk_1` FOREIGN KEY (`exercise_id`) REFERENCES `EnunTCP` (`ExerciseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.3. Crear tabla de Usuarios Administradores
```sql
CREATE TABLE IF NOT EXISTS `Users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3.4. Crear tabla de Estado TCP (TcpState)
Esta tabla es necesaria para la nueva lógica de verificación.

```sql
CREATE TABLE IF NOT EXISTS `TcpState` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ExerciseID` int(11) DEFAULT NULL,
  `TicID` int(11) DEFAULT NULL,
  `Sender` int(11) DEFAULT NULL,
  `cwnd` decimal(10,2) DEFAULT NULL,
  `tcp_mode` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## 4. Creación del Primer Usuario Administrador

El sistema antiguo usaba una contraseña hardcodeada. El nuevo sistema requiere un usuario en la base de datos.

Ejecute la siguiente consulta para crear un usuario `admin` con contraseña `admin` (¡CÁMBIELA INMEDIATAMENTE DESPUÉS DE ENTRAR!):

```sql
-- La contraseña es 'admin'
INSERT INTO `Users` (`username`, `password_hash`) VALUES
('admin', '$2y$10$ukp51PXm8ua90MqDbpxR/e7ZNwD3pbVk3W10gBGPzgRzKVu9tSi2m');
```
*Nota: El hash de arriba es un ejemplo simplificado. Para generar uno válido para 'admin', use:*
`$2y$10$e0MYzXyjpJS7Pd0RVvHwHe.i/2.1N.1.1.1.1.1.1.1.1.1.1` (Este no es real, mejor use el generador de PHP o entre con uno generado por usted).

**Mejor opción:** Si tiene acceso a PHP CLI, genere su propio hash:
```bash
php -r 'echo password_hash("su_contraseña_segura", PASSWORD_BCRYPT);'
```
Y use ese hash en el `INSERT`.

## 5. Verificación

1.  Acceda a `/admin/`.
2.  Debería ser redirigido al login.
3.  Ingrese con su usuario y contraseña.
4.  Vaya a "Cambiar Contraseña" y establezca una segura (mínimo 12 caracteres).
5.  Verifique que las secciones "Estadísticas" y "Usuarios" funcionan correctamente.
