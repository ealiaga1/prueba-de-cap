<?php
session_start();
require_once 'includes/db_connection.php';

// Seguridad: Asegurarse de que el usuario esté logueado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("Acceso denegado.");
}
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['action'])) {
    header("Location: mi_perfil.php");
    exit;
}

$id_usuario = $_SESSION['user_id'];
$action = $_POST['action'];

// --- ACCIÓN: ACTUALIZAR INFORMACIÓN PERSONAL ---
if ($action === 'update_info') {
    $nombre_completo = $_POST['nombre_completo'];
    $cap = $_POST['cap'];
    $email = $_POST['email'];

    // Opcional: Verificar si el nuevo email ya está en uso por OTRO usuario
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt_check->bind_param("si", $email, $id_usuario);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $_SESSION['message'] = "Error: El email ya está en uso por otro usuario.";
        $_SESSION['message_type'] = "danger";
    } else {
        $stmt_update = $conn->prepare("UPDATE usuarios SET nombre_completo = ?, cap = ?, email = ? WHERE id = ?");
        $stmt_update->bind_param("sssi", $nombre_completo, $cap, $email, $id_usuario);
        
        if ($stmt_update->execute()) {
            $_SESSION['message'] = "Información actualizada con éxito.";
            $_SESSION['message_type'] = "success";
            // Actualizar el nombre en la sesión para que se refleje inmediatamente en el menú
            $_SESSION['user_name'] = $nombre_completo;
        } else {
            $_SESSION['message'] = "Error al actualizar la información.";
            $_SESSION['message_type'] = "danger";
        }
    }
}

// --- ACCIÓN: CAMBIAR CONTRASEÑA ---
elseif ($action === 'change_password') {
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirm = $_POST['password_confirm'];

    // Verificar que la nueva contraseña y su confirmación coincidan
    if ($password_nueva !== $password_confirm) {
        $_SESSION['message'] = "Error: La nueva contraseña y su confirmación no coinciden.";
        $_SESSION['message_type'] = "danger";
        header("Location: mi_perfil.php");
        exit;
    }

    // Obtener la contraseña actual hasheada de la base de datos
    $stmt_pass = $conn->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt_pass->bind_param("i", $id_usuario);
    $stmt_pass->execute();
    $result = $stmt_pass->get_result()->fetch_assoc();
    $hash_actual = $result['password'];

    // Verificar si la contraseña actual proporcionada es correcta
    if (password_verify($password_actual, $hash_actual)) {
        // Si es correcta, hashear la nueva contraseña y actualizarla
        $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $stmt_update = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt_update->bind_param("si", $nuevo_hash, $id_usuario);
        
        if ($stmt_update->execute()) {
            $_SESSION['message'] = "Contraseña cambiada con éxito.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error al cambiar la contraseña.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Error: La contraseña actual es incorrecta.";
        $_SESSION['message_type'] = "danger";
    }
}

// Redirigir de vuelta a la página de perfil
header("Location: mi_perfil.php");
exit;
?>