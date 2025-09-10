<?php
// Incluimos los archivos base
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// --- 1. OBTENER Y VALIDAR EL ID DE LA SESIÓN ---

// Verificamos si se proporcionó un ID en la URL y si es un número.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Si no es válido, mostramos un error y terminamos la ejecución.
    echo '<div class="alert alert-danger">ID de sesión no válido.</div>';
    require_once 'includes/footer.php';
    exit;
}
$id_sesion = intval($_GET['id']);


// --- 2. CONSULTAR LOS DATOS DE LA SESIÓN Y SUS EXPEDIENTES ---

// A. Obtener los datos generales de la sesión.
$query_sesion = $conn->prepare("SELECT s.*, u.nombre_completo as nombre_usuario FROM sesiones s JOIN usuarios u ON s.id_usuario = u.id WHERE s.id = ?");
$query_sesion->bind_param("i", $id_sesion);
$query_sesion->execute();
$result_sesion = $query_sesion->get_result();

if ($result_sesion->num_rows === 0) {
    // Si no se encuentra la sesión, mostramos un error.
    echo '<div class="alert alert-danger">No se encontró la sesión solicitada.</div>';
    require_once 'includes/footer.php';
    exit;
}
$sesion = $result_sesion->fetch_assoc();


// B. Obtener los expedientes de Edificaciones de esta sesión.
$query_edif = $conn->prepare("SELECT * FROM expedientes_edificaciones WHERE id_sesion = ?");
$query_edif->bind_param("i", $id_sesion);
$query_edif->execute();
$result_edif = $query_edif->get_result();


// C. Obtener los expedientes de Habilitaciones Urbanas de esta sesión.
$query_hab = $conn->prepare("SELECT * FROM expedientes_habilitaciones WHERE id_sesion = ?");
$query_hab->bind_param("i", $id_sesion);
$query_hab->execute();
$result_hab = $query_hab->get_result();

?>

<!-- --- 3. MOSTRAR LA INFORMACIÓN EN LA PÁGINA --- -->

<div class="d-flex justify-content-between align-items-center">
    <h1 class="mt-4">Detalle de la Sesión</h1>
    <a href="mis_sesiones.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver al Listado
    </a>
</div>
<hr>

<!-- Card con los datos de la sesión -->
<div class="card mb-4">
    <div class="card-header fw-bold">
        <i class="fas fa-info-circle me-2"></i>Datos Generales de la Sesión
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <p><strong>Nº Sesión:</strong> <?php echo htmlspecialchars($sesion['numero_sesion']); ?></p>
                <p><strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($sesion['fecha_sesion'])); ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>Provincia:</strong> <?php echo htmlspecialchars($sesion['provincia']); ?></p>
                <p><strong>Distrito:</strong> <?php echo htmlspecialchars($sesion['distrito']); ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>Delegado:</strong> <?php echo htmlspecialchars($sesion['delegado']); ?></p>
                <p><strong>Usuario Creador:</strong> <?php echo htmlspecialchars($sesion['nombre_usuario']); ?></p>
            </div>
        </div>
    </div>
</div>


<!-- Listado de Expedientes de Edificaciones (si existen) -->
<?php if ($result_edif->num_rows > 0): ?>
<div class="card mb-4">
    <div class="card-header fw-bold bg-light">
        <i class="fas fa-building me-2"></i>Expedientes de Edificaciones
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Nº Expediente</th>
                        <th>Fecha Ingreso</th>
                        <th>Administrado</th>
                        <th>Proyectista</th>
                        <th>Dictamen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($exp = $result_edif->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($exp['numero_expediente']); ?></td>
                        <td><?php echo date("d/m/Y", strtotime($exp['fecha_ingreso'])); ?></td>
                        <td><?php echo htmlspecialchars($exp['administrado']); ?></td>
                        <td><?php echo htmlspecialchars($exp['proyectista_responsable']); ?></td>
                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($exp['dictamen']); ?></span></td>
                        <td>
                            <!-- Estos enlaces aún no funcionarán -->
                            <a href="ver_expediente.php?tipo=edif&id=<?php echo $exp['id']; ?>" class="btn btn-xs btn-outline-primary">Ver Detalle Completo</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- Listado de Expedientes de Habilitaciones (si existen) -->
<?php if ($result_hab->num_rows > 0): ?>
<div class="card mb-4">
    <div class="card-header fw-bold bg-light">
        <i class="fas fa-road me-2"></i>Expedientes de Habilitaciones Urbanas
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                 <thead>
                    <tr>
                        <th>Nº Expediente</th>
                        <th>Fecha Ingreso</th>
                        <th>Propietario</th>
                        <th>Proyectista</th>
                        <th>Dictamen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($exp = $result_hab->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($exp['numero_expediente']); ?></td>
                        <td><?php echo date("d/m/Y", strtotime($exp['fecha_ingreso'])); ?></td>
                        <td><?php echo htmlspecialchars($exp['propietario']); ?></td>
                        <td><?php echo htmlspecialchars($exp['proyectista_responsable']); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($exp['dictamen']); ?></span></td>
                        <td>
                             <!-- Estos enlaces aún no funcionarán -->
                            <a href="ver_expediente.php?tipo=hab&id=<?php echo $exp['id']; ?>" class="btn btn-xs btn-outline-primary">Ver Detalle Completo</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>


<?php
// Incluimos el pie de página
require_once 'includes/footer.php';
?>