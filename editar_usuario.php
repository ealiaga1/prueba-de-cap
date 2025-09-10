<?php
require_once 'includes/auth_check.php';
// Doble seguridad para asegurar que solo un admin pueda acceder
if ($_SESSION['user_role'] != 'administrador') { 
    die("Acceso denegado."); 
}
require_once 'includes/db_connection.php';

// Validar que se reciba un ID numérico, si no, redirigir.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { 
    header("Location: gestionar_usuarios.php"); 
    exit; 
}
$id_usuario = $_GET['id'];

// Obtener los datos del usuario que se va a editar
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Si no se encuentra un usuario con ese ID, redirigir.
if ($result->num_rows === 0) { 
    header("Location: gestionar_usuarios.php"); 
    exit; 
}
$user = $result->fetch_assoc();

// Incluir la cabecera y el menú lateral
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<h1 class="mt-4">Editar Usuario</h1>
<p>Modifique los datos del usuario. Deje la contraseña en blanco para no cambiarla.</p>

<div class="card">
    <div class="card-body">
        <form action="procesar_usuario.php" method="POST">
            <!-- Campo oculto para enviar el ID del usuario que se está editando -->
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($user['id']); ?>">
            
            <div class="mb-3">
                <label for="nombre_completo" class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" name="nombre_completo" value="<?php echo htmlspecialchars($user['nombre_completo']); ?>" required>
            </div>
            
            <!-- ======================================================= -->
            <!--          NUEVO CAMPO AÑADIDO PARA EL Nº CAP             -->
            <!-- ======================================================= -->
            <div class="mb-3">
                <label for="cap" class="form-label">Nº CAP (Opcional)</label>
                <input type="text" class="form-control" name="cap" value="<?php echo htmlspecialchars($user['cap']); ?>">
            </div>
            <!-- ======================================================= -->

            <div class="mb-3">
                <label for="email" class="form-label">Email (Usuario)</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Nueva Contraseña</label>
                <input type="password" class="form-control" name="password" autocomplete="new-password">
                <div class="form-text">Deje este campo en blanco para no cambiar la contraseña actual.</div>
            </div>
            <div class="mb-3">
                <label for="tipo_comision" class="form-label">Comisión Asignada</label>
                <select name="tipo_comision" class="form-select" required>
                    <option value="edificaciones" <?php echo ($user['tipo_comision'] == 'edificaciones') ? 'selected' : ''; ?>>Edificaciones</option>
                    <option value="habilitaciones_urbanas" <?php echo ($user['tipo_comision'] == 'habilitaciones_urbanas') ? 'selected' : ''; ?>>Habilitaciones Urbanas</option>
                </select>
            </div>
             <div class="mb-3">
                <label for="rol" class="form-label">Rol del Usuario</label>
                <select name="rol" class="form-select" required>
                    <option value="comision" <?php echo ($user['rol'] == 'comision') ? 'selected' : ''; ?>>Comisión</option>
                    <option value="administrador" <?php echo ($user['rol'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="gestionar_usuarios.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>