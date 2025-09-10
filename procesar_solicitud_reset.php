<?php
// FORZAR ERRORES PARA DEPURACIÓN
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db_connection.php';
require_once 'includes/mailer_config.php';

// --- INCLUSIÓN DE CLASES DE PHPMailer CON RUTAS CORREGIDAS ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'lib/PHPMailer/Exception.php';
require 'lib/PHPMailer/PHPMailer.php';
require 'lib/PHPMailer/SMTP.php';
// -----------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token = bin2hex(random_bytes(32));
        $expiry_date = date('Y-m-d H:i:s', time() + 3600);
        $stmt_update = $conn->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt_update->bind_param("sss", $token, $expiry_date, $email);
        $stmt_update->execute();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(SMTP_USERNAME, 'Sistema CAP Junín');
            $mail->addAddress($email);

            $reset_link = "https://capjunin.net.pe/reset_password.php?token=" . $token;
            $mail->isHTML(true);
            $mail->Subject = 'Restablecimiento de Contraseña - Sistema CAP Junín';
            $mail->Body    = "Hola,<br><br>" .
                           "Hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:<br>" .
                           "<a href='{$reset_link}'>Restablecer mi contraseña</a><br><br>" .
                           "Si no solicitaste esto, puedes ignorar este correo.<br>" .
                           "Este enlace expirará en 1 hora.<br><br>" .
                           "Saludos,<br>Equipo de Sistema CAP Junín";
            $mail->AltBody = "Hola,\n\nPara restablecer tu contraseña, copia y pega el siguiente enlace en tu navegador:\n{$reset_link}\n\nEste enlace expirará en 1 hora.";

            $mail->send();
            $_SESSION['message'] = "Si tu email está registrado, recibirás un enlace para restablecer tu contraseña.";
            $_SESSION['message_type'] = "success";

        } catch (Exception $e) {
            // Mostramos el error detallado para saber qué pasa
            $_SESSION['message'] = "No se pudo enviar el correo de recuperación. Error: {$mail->ErrorInfo}";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Si tu email está registrado, recibirás un enlace para restablecer tu contraseña.";
        $_SESSION['message_type'] = "success";
    }
    
    header("Location: solicitar_reset.php");
    exit;
}
?>