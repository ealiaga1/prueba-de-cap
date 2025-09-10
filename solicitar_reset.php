<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - CAP Junín</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body class="login-page">
    <main>
        <div class="card login-card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img class="mb-3" src="assets/images/logo.png" alt="Logo CAP Junín" width="80">
                    <h5 class="card-title">Recuperar Contraseña</h5>
                    <p class="text-muted">Introduce tu email y te enviaremos un enlace para restablecer tu contraseña.</p>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>" role="alert">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                <?php endif; ?>

                <form action="procesar_solicitud_reset.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Registrado</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Enviar Enlace de Recuperación</button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="index.php">Volver a Inicio de Sesión</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>