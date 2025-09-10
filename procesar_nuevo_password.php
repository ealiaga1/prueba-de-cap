<?php
session_start();
require_once 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        die("Las contraseñas no coinciden.");
    }

    // Volver a verificar el token por seguridad
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Actualizar la contraseña
        $new_password_hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Invalidar el token para que no se pueda volver a usar
        $stmt_update = $conn->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt_update->bind_param("si", $new_password_hashed, $user_id);
        $stmt_update->execute();

        $_SESSION['message'] = "Tu contraseña ha sido actualizada con éxito. Ya puedes iniciar sesión.";
        $_SESSION['message_type'] = "success";
        header("Location: index.php"); // Redirigir al login
        exit;
    } else {
        die("El enlace de restablecimiento no es válido o ha expirado.");
    }
}
?>