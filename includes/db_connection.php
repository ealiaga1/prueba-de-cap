<?php
// Configuración de la base de datos para el servidor de capjunin.net.pe

// 1. Reemplaza 'nombre_del_host' con el host de tu base de datos.
//    A menudo es 'localhost', pero tu hosting podría darte una dirección IP o un dominio diferente.
define('DB_HOST', 'localhost'); // <-- VERIFICA ESTO CON TU HOSTING

// 2. Reemplaza 'nombre_de_usuario_db' con el usuario que creaste en cPanel.
define('DB_USER', 'u717685572_ucapjunin'); // <-- REEMPLAZAR

// 3. Reemplaza 'tu_contraseña_segura' con la contraseña que creaste.
define('DB_PASS', '1324MMa1240$'); // <-- REEMPLAZAR

// 4. Reemplaza 'nombre_de_la_db' con el nombre de la base de datos que creaste.
define('DB_NAME', 'u717685572_capjunin'); // <-- REEMPLAZAR


// --- El resto del código es igual ---

// Crear la conexión usando MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Establecer el juego de caracteres a UTF-8
$conn->set_charset("utf8mb4");

// Verificar la conexión
if ($conn->connect_error) {
    // Si hay un error, detener la ejecución y mostrar el error
    die("Error de conexión: " . $conn->connect_error);
}
?>