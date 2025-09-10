<?php
require_once 'includes/auth_check.php';
// ¡Doble seguridad! Asegurarnos de que SOLO el admin pueda ver esta página.
if ($_SESSION['user_role'] != 'administrador') {
    die("Acceso denegado.");
}
require_once 'includes/db_connection.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Obtener todos los usuarios para la tabla
$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY nombre_completo");
?>

<h1 class="mt-4">Gestionar Usuarios del Sistema</h1>
<p>Desde aquí puede crear nuevos usuarios y ver los existentes.</p>

<div class="row">
    <!-- Columna del Formulario para Añadir -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header fw-bold">Añadir Nuevo Usuario</div>
            <div class="card-body">
                <form action="procesar_usuario.php" method="POST">
                    <div class="mb-3">
                        <label for="nombre_completo" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre_completo" required>
                    </div>
                     <div class="mb-3">
        <label for="cap" class="form-label">Nº CAP (Opcional)</label>
        <input type="text" class="form-control" name="cap">
    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (será su usuario)</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña Inicial</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_comision" class="form-label">Comisión Asignada</label>
                        <select name="tipo_comision" class="form-select" required>
                            <option value="edificaciones">Edificaciones</option>
                            <option value="habilitaciones_urbanas">Habilitaciones Urbanas</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="rol" class="form-label">Rol del Usuario</label>
                        <select name="rol" class="form-select" required>
                            <option value="comision">Comisión</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Columna de la Tabla de Usuarios -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-bold">Usuarios Registrados</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Nº CAP</th>
            <th>Email (Usuario)</th>
            <th>Comisión</th>
            <th>Rol</th>
            <th class="text-center">Acciones</th> <!-- NUEVA COLUMNA -->
        </tr>
    </thead>
    <tbody>
    <?php while($user = $usuarios->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
            <td><strong><?php echo htmlspecialchars($user['cap']); ?></strong></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo ucfirst(str_replace('_', ' ', $user['tipo_comision'])); ?></td>
            <td><span class="badge bg-<?php echo $user['rol'] == 'administrador' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($user['rol']); ?></span></td>
            
            <!-- NUEVA CELDA CON BOTONES -->
            <td class="text-center">
                <?php // Impedir que el admin se edite/elimine a sí mismo desde aquí
                if ($user['id'] != $_SESSION['user_id']): ?>
                    <a href="editar_usuario.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="eliminar_usuario.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar a este usuario? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                <?php else: ?>
                    <span class="text-muted">No disponible</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>