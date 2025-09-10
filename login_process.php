<?php
session_start();
require_once 'includes/db_connection.php';

// Verificamos que los datos del formulario fueron enviados
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- VERIFICACIÓN DE RECAPTCHA (VERSIÓN CON cURL) ---
    if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
        $secretKey = "6LebcbMrAAAAAGt7uWPWh9VArza2e6ZOe1N404OI";
        $postData = http_build_query(['secret' => $secretKey, 'response' => $_POST['g-recaptcha-response'], 'remoteip' => $_SERVER['REMOTE_ADDR']]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $verifyResponse = curl_exec($ch);
        curl_close($ch);
        $responseData = json_decode($verifyResponse);
        
        if (!$responseData->success) {
            header("Location: index.php?error=La verificación reCAPTCHA ha fallado.");
            exit;
        }
    } else {
        header("Location: index.php?error=Por favor, complete la verificación reCAPTCHA.");
        exit;
    }

    // --- LÓGICA DE LOGIN ---
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nombre_completo, password, rol, tipo_comision, cap FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Guardar datos del usuario en la sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre_completo'];
            $_SESSION['user_role'] = $user['rol'];
            $_SESSION['user_commission'] = $user['tipo_comision'];
            $_SESSION['user_cap'] = $user['cap'];
            $_SESSION['loggedin'] = true;

            // Redirigir al dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            header("Location: index.php?error=Email o contraseña incorrectos.");
            exit;
        }
    } else {
        header("Location: index.php?error=Email o contraseña incorrectos.");
        exit;
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: index.php");
    exit;
}
?>