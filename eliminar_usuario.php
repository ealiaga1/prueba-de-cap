<?php
session_start();
require_once 'includes/db_connection.php';

// Triple seguridad: logueado, admin y con un ID válido
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] != 'administrador') {
die("Acceso denegado.");
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
header("Location: gestionar_usuarios.php");
exit;
}
$id_a_eliminar = intval($_GET['id']);

// ¡¡¡VERIFICACIÓN DE SEGURIDAD CRÍTICA!!!
// Impedir que un administrador elimine su propia cuenta.
if ($id_a_eliminar == $_SESSION['user_id']) {
$_SESSION['message'] = "Error: No puede eliminar su propia cuenta de administrador.";
$_SESSION['message_type'] = "danger";
header("Location: gestionar_usuarios.php");
exit;
}

// Proceder con la eliminación
$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_a_eliminar);

if ($stmt->execute()) {
$_SESSION['message'] = "Usuario eliminado con éxito.";
$_SESSION['message_type'] = "success";
} else {
$_SESSION['message'] = "Error al eliminar el usuario.";
$_SESSION['message_type'] = "danger";
}

header("Location: gestionar_usuarios.php");
exit;
?>