<?php
session_start();
require_once 'includes/db_connection.php';

// 1. Validar que el token venga en la URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    // Si no hay token, no podemos continuar. Mostramos un mensaje genérico.
    die("Enlace no válido.");
}
$token = $_GET['token'];

// 2. Verificar si el token es válido y no ha expirado
// Buscamos un usuario que tenga este token y cuya fecha de expiración sea futura
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

// Si la consulta no devuelve ninguna fila, el token es inválido o expiró
if ($result->num_rows === 0) {
    // Es importante mostrar un mensaje claro al usuario
    die("El enlace de restablecimiento no es válido o ha expirado. Por favor, solicita uno nuevo desde la página de inicio de sesión.");
}

// Si llegamos aquí, el token es válido y podemos mostrar el formulario.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - CAP Junín</title>
    
    <!-- Estilos de Bootstrap y personalizados -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body class="login-page">
    <main>
        <div class="card login-card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img class="mb-3" src="assets/images/logo.png" alt="Logo CAP Junín" width="80">
                    <h5 class="card-title">Establecer Nueva Contraseña</h5>
                </div>

                <form action="procesar_nuevo_password.php" method="POST">
                    <!-- Campo oculto para enviar el token al siguiente script -->
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password_confirm" id="password_confirm" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Guardar Nueva Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>