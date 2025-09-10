<?php
// Iniciar la sesión en cada página que lo necesite
session_start();

// Si la variable de sesión 'loggedin' no existe o no es verdadera,
// significa que el usuario no ha iniciado sesión.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirigir al usuario a la página de login (index.php)
    header("location: index.php");
    exit;
}
?>