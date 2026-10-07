<?php
// CriptoSim - Copia de ejemplo de la configuracion de conexion.
// En el repositorio, config/conexion.php NO se sube (tiene las
// credenciales reales de desarrollo). Para dejarlo funcionando:
//   1. Copie este archivo como conexion.php
//   2. Rellene los valores de su servidor MySQL
// La base se crea importando database.sql de la raiz.

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'su_contrasena_aqui');
define('DB_NAME', 'criptosim');
define('DB_PORT', 3306);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conexion->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Error de conexion a la base de datos: ' . $e->getMessage());
}