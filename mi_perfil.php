<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

// Obtener los datos actuales del usuario logueado
$id_usuario = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nombre_completo, cap, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<h1 class="mt-4">Mi Perfil</h1>
<p>Desde aquí puede actualizar su información personal y cambiar su contraseña.</p>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
<?php endif; ?>

<div class="row">
    <!-- Columna para editar datos personales -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header fw-bold">Editar Información Personal</div>
            <div class="card-body">
                <form action="procesar_perfil.php" method="POST">
                    <input type="hidden" name="action" value="update_info">
                    <div class="mb-3">
                        <label for="nombre_completo" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre_completo" value="<?php echo htmlspecialchars($user['nombre_completo']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="cap" class="form-label">Nº CAP</label>
                        <input type="text" class="form-control" name="cap" value="<?php echo htmlspecialchars($user['cap']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (Usuario)</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Columna para cambiar la contraseña -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header fw-bold">Cambiar Contraseña</div>
            <div class="card-body">
                <form action="procesar_perfil.php" method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" name="password_actual" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password_nueva" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password_confirm" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>