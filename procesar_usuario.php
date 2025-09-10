<?php
session_start();
require_once 'includes/db_connection.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] != 'administrador') {
    die("Acceso denegado.");
}
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Método no permitido.");
}

// =======================================================
// =============== LÓGICA DE ACTUALIZACIÓN ===============
// =======================================================
if (isset($_POST['id_usuario']) && !empty($_POST['id_usuario'])) {
    
    $id_usuario = intval($_POST['id_usuario']);
    $nombre_completo = $_POST['nombre_completo'];
    $cap = $_POST['cap'];
    $email = $_POST['email'];
    $tipo_comision = $_POST['tipo_comision'];
    $rol = $_POST['rol'];

    // Lógica para la contraseña: solo se actualiza si se proporciona una nueva.
    if (!empty($_POST['password'])) {
        $password_hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo = ?, cap = ?, email = ?, password = ?, tipo_comision = ?, rol = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nombre_completo, $cap, $email, $password_hashed, $tipo_comision, $rol, $id_usuario);
    } else {
        // Si no se proporciona contraseña, no se actualiza ese campo.
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo = ?, cap = ?, email = ?, tipo_comision = ?, rol = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nombre_completo, $cap, $email, $tipo_comision, $rol, $id_usuario);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Usuario actualizado con éxito.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error al actualizar el usuario.";
        $_SESSION['message_type'] = "danger";
    }

} 
// =======================================================
// ================= LÓGICA DE CREACIÓN ==================
// =======================================================
else {
    $nombre_completo = $_POST['nombre_completo'];
    $cap = $_POST['cap'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $tipo_comision = $_POST['tipo_comision'];
    $rol = $_POST['rol'];

    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $_SESSION['message'] = "Error: El email '$email' ya está registrado.";
        $_SESSION['message_type'] = "danger";
        header("Location: gestionar_usuarios.php");
        exit;
    }

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt_insert = $conn->prepare("INSERT INTO usuarios (nombre_completo, cap, email, password, tipo_comision, rol) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("ssssss", $nombre_completo, $cap, $email, $password_hashed, $tipo_comision, $rol);

    if ($stmt_insert->execute()) {
        $_SESSION['message'] = "Usuario creado con éxito.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error al crear el usuario.";
        $_SESSION['message_type'] = "danger";
    }
}

header("Location: gestionar_usuarios.php");
exit;
?>