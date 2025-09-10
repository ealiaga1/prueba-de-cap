<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// --- 1. VALIDAR LOS PARÁMETROS DE LA URL ---
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['tipo'])) {
    echo '<div class="alert alert-danger">Parámetros no válidos.</div>';
    require_once 'includes/footer.php';
    exit;
}
$id_expediente = intval($_GET['id']);
$tipo_expediente = $_GET['tipo'];


// --- 2. CONSULTAR LA BASE DE DATOS SEGÚN EL TIPO DE EXPEDIENTE ---
$expediente = null;

if ($tipo_expediente == 'edif') {
    $sql = "SELECT exp.*, s.numero_sesion, s.fecha_sesion, s.provincia, s.distrito, s.delegado 
            FROM expedientes_edificaciones exp 
            JOIN sesiones s ON exp.id_sesion = s.id 
            WHERE exp.id = ?";
} elseif ($tipo_expediente == 'hab') {
    $sql = "SELECT exp.*, s.numero_sesion, s.fecha_sesion, s.provincia, s.distrito, s.delegado 
            FROM expedientes_habilitaciones exp 
            JOIN sesiones s ON exp.id_sesion = s.id 
            WHERE exp.id = ?";
} else {
    echo '<div class="alert alert-danger">Tipo de expediente no reconocido.</div>';
    require_once 'includes/footer.php';
    exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_expediente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">No se encontró el expediente solicitado.</div>';
    require_once 'includes/footer.php';
    exit;
}
$expediente = $result->fetch_assoc();


// --- 3. CONSULTAR LOS PAGOS ASOCIADOS A ESTE EXPEDIENTE ---
$tipo_db = ($tipo_expediente == 'edif') ? 'edificacion' : 'habilitacion';
$query_pagos = $conn->prepare("SELECT * FROM pagos WHERE id_expediente = ? AND tipo_expediente = ?");
$query_pagos->bind_param("is", $id_expediente, $tipo_db);
$query_pagos->execute();
$result_pagos = $query_pagos->get_result();

?>

<!-- --- 4. MOSTRAR TODA LA INFORMACIÓN --- -->

<div class="d-flex justify-content-between align-items-center">
    <h1 class="mt-4">Detalle Completo del Expediente</h1>
    <a href="editar_expediente.php?tipo=<?php echo $tipo_expediente; ?>&id=<?php echo $id_expediente; ?>" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Editar Expediente
        </a>
    <a href="ver_sesion.php?id=<?php echo $expediente['id_sesion']; ?>" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Volver a la Sesión</a>
</div>
<p class="text-muted">Expediente N°: <?php echo htmlspecialchars($expediente['numero_expediente']); ?></p>
<hr>

<!-- Datos de la Sesión y Expediente -->
<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header fw-bold"><i class="fas fa-info-circle me-2"></i>Datos de la Sesión</div>
            <div class="card-body">
                <p><strong>Nº Sesión:</strong> <?php echo htmlspecialchars($expediente['numero_sesion']); ?></p>
                <p><strong>Fecha Sesión:</strong> <?php echo date("d/m/Y", strtotime($expediente['fecha_sesion'])); ?></p>
                <p><strong>Ubicación:</strong> <?php echo htmlspecialchars($expediente['provincia'] . ' / ' . $expediente['distrito']); ?></p>
                <p><strong>Delegado:</strong> <?php echo htmlspecialchars($expediente['delegado']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header fw-bold"><i class="fas fa-file-alt me-2"></i>Datos del Expediente</div>
            <div class="card-body">
                <p><strong>Modalidad:</strong> <?php echo htmlspecialchars($expediente['modalidad']); ?></p>
                <p><strong>Fecha Ingreso:</strong> <?php echo date("d/m/Y", strtotime($expediente['fecha_ingreso'])); ?></p>
                <p><strong>Presentación:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($expediente['presentacion']); ?></span></p>
                
                <?php if ($tipo_expediente == 'edif'): ?>
                    <p><strong>Tipo de Obra:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($expediente['tipo_obra']); ?></span></p>
                <?php endif; ?>

                <p><strong>Usos:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($expediente['usos']); ?></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Datos específicos por tipo -->
<div class="card mb-4">
    <div class="card-header fw-bold"><i class="fas fa-tasks me-2"></i>Detalles de Revisión</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><p><strong>Fecha Revisión:</strong> <?php echo $expediente['fecha_revision'] ? date("d/m/Y", strtotime($expediente['fecha_revision'])) : 'N/A'; ?></p></div>
            <div class="col-md-4"><p><strong>Nº Revisión:</strong> <?php echo htmlspecialchars($expediente['numero_revision'] ?? 'N/A'); ?></p></div>
            <div class="col-md-4"><p><strong>Dictamen:</strong> <?php echo htmlspecialchars($expediente['dictamen'] ?? 'N/A'); ?></p></div>
            <div class="col-md-12">
                <?php if ($tipo_expediente == 'edif' && !empty($expediente['archivo_revision'])): ?>
                    <p><strong>Archivo Revisión:</strong> <a href="<?php echo htmlspecialchars($expediente['archivo_revision']); ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download me-2"></i>Descargar Archivo</a></p>
                <?php elseif ($tipo_expediente == 'hab' && !empty($expediente['archivo_vias'])): ?>
                    <p><strong>Archivo Vías:</strong> <a href="<?php echo htmlspecialchars($expediente['archivo_vias']); ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download me-2"></i>Descargar Archivo</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Datos del Inmueble/Terreno -->
<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header fw-bold">
                <?php echo ($tipo_expediente == 'edif') ? '<i class="fas fa-home me-2"></i>Datos del Inmueble' : '<i class="fas fa-map-marked-alt me-2"></i>Datos del Terreno'; ?>
            </div>
            <div class="card-body">
                <?php if ($tipo_expediente == 'edif'): ?>
                    <p><strong>Área Terreno:</strong> <?php echo htmlspecialchars($expediente['area_terreno']); ?> m²</p>
                    <p><strong>Área Techada:</strong> <?php echo htmlspecialchars($expediente['area_techada']); ?> m²</p>
                    <p><strong>Altura:</strong> <?php echo htmlspecialchars($expediente['altura_pisos']); ?> pisos / <?php echo htmlspecialchars($expediente['altura_metros']); ?> m</p>
                    <p><strong>Administrado:</strong> <?php echo htmlspecialchars($expediente['administrado']); ?></p>
                <?php else: // 'hab' ?>
                    <p><strong>Ubicación Predio:</strong> <?php echo htmlspecialchars($expediente['ubicacion_predio']); ?></p>
                    <p><strong>Propietario:</strong> <?php echo htmlspecialchars($expediente['propietario']); ?></p>
                    <p><strong>Área Terreno:</strong> <?php echo htmlspecialchars($expediente['area_terreno']); ?> m²</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header fw-bold"><i class="fas fa-user-tie me-2"></i>Datos del Proyectista</div>
            <div class="card-body">
                <p><strong>Profesional Responsable:</strong> <?php echo htmlspecialchars($expediente['proyectista_responsable']); ?></p>
                <p><strong>Nº CAP:</strong> <?php echo htmlspecialchars($expediente['cap_proyectista']); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Datos de Pago -->
<div class="card mb-4">
    <div class="card-header fw-bold"><i class="fas fa-dollar-sign me-2"></i>Comprobantes de Pago</div>
    <div class="card-body">
        <?php if ($result_pagos->num_rows > 0): ?>
            <ul class="list-group">
                <?php while($pago = $result_pagos->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Nº Credipago:</strong> <?php echo htmlspecialchars($pago['numero_credipago']); ?> | 
                            <strong>Monto:</strong> S/ <?php echo number_format($pago['monto'], 2); ?> | 
                            <strong>Fecha:</strong> <?php echo date("d/m/Y", strtotime($pago['fecha_pago'])); ?>
                        </div>
                        <?php if (!empty($pago['comprobante_ruta'])): ?>
                            <a href="<?php echo htmlspecialchars($pago['comprobante_ruta']); ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="fas fa-receipt me-2"></i>Ver Comprobante</a>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">No se registraron pagos para este expediente.</p>
        <?php endif; ?>
    </div>
</div>


<?php
require_once 'includes/footer.php';
?>