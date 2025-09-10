<?php
// Si el usuario ya está logueado, redirigirlo al dashboard
session_start();
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - CAP Junín</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tus estilos personalizados (incluye los nuevos estilos del login) -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- FAVICON -->
    <link rel="icon" type="images/png" href="assets/images/favicon.png">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="login-page">
    
    <main>
        <div class="card login-card">
            <div class="card-body">
                
                <div class="text-center mb-4">
                    <img class="mb-3" src="assets/images/logo.png" alt="Logo CAP Junín" width="80">
                    <h5 class="card-title mb-2">Comisión Técnica de Desarrollo Urbano y Habilitaciones Urbanas del CAP Junín</h5>
                </div>

                <form action="login_process.php" method="POST">
                    
                    <?php
                    // Mostrar mensaje de error si existe, con estilos de Bootstrap
                    if (isset($_GET['error'])) {
                        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_GET['error']) . '</div>';
                    }
                    ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Email</label> <!-- CAMBIADO -->
                        <input type="email" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                  <div class="mb-3 d-flex justify-content-center">
        <div class="g-recaptcha" data-sitekey="6LebcbMrAAAAAFa2EQq6bJc_4mbDYJNgiAYmeRno"></div>
    </div>

                    <div class="d-grid mt-4">
                        <button class="btn btn-primary btn-lg" type="submit">Ingresar</button>
                    </div>

                    <div class="text-center mt-4">
                       <a href="solicitar_reset.php">¿Olvidaste tu contraseña?</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>