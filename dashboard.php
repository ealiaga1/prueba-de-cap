<?php
// ---- INCLUDES Y VERIFICACIÓN DE SESIÓN ----
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php'; // Necesitamos la conexión a la BBDD

// ---- LÓGICA PHP PARA OBTENER ESTADÍSTICAS ----

// Base de la consulta para filtrar por usuario si no es administrador
$user_filter_condition = "";
if ($_SESSION['user_role'] != 'administrador') {
    $user_id = $_SESSION['user_id'];
    // Filtramos los expedientes que pertenecen a sesiones creadas por el usuario logueado
    $user_filter_condition = " WHERE s.id_usuario = $user_id";
}

// 1. Total Expedientes
$query_total_edif = "SELECT COUNT(*) as total FROM expedientes_edificaciones ee JOIN sesiones s ON ee.id_sesion = s.id" . $user_filter_condition;
$query_total_hab = "SELECT COUNT(*) as total FROM expedientes_habilitaciones eh JOIN sesiones s ON eh.id_sesion = s.id" . $user_filter_condition;
$total_edif = $conn->query($query_total_edif)->fetch_assoc()['total'];
$total_hab = $conn->query($query_total_hab)->fetch_assoc()['total'];
$total_expedientes = $total_edif + $total_hab;

// 2. Dictámenes (Edificaciones)
$query_dict_edif = "SELECT dictamen, COUNT(*) as count FROM expedientes_edificaciones ee JOIN sesiones s ON ee.id_sesion = s.id" . $user_filter_condition . " GROUP BY dictamen";
$result_dict_edif = $conn->query($query_dict_edif);
$dictamenes_edif = ['conforme' => 0, 'no conforme' => 0, 'conforme con observaciones' => 0];
while($row = $result_dict_edif->fetch_assoc()) {
    if (isset($dictamenes_edif[$row['dictamen']])) {
        $dictamenes_edif[$row['dictamen']] = $row['count'];
    }
}

// 3. Dictámenes (Habilitaciones)
$query_dict_hab = "SELECT dictamen, COUNT(*) as count FROM expedientes_habilitaciones eh JOIN sesiones s ON eh.id_sesion = s.id" . $user_filter_condition . " GROUP BY dictamen";
$result_dict_hab = $conn->query($query_dict_hab);
$dictamenes_hab = ['C' => 0, 'CO' => 0, 'R' => 0, 'A' => 0];
while($row = $result_dict_hab->fetch_assoc()) {
     if (isset($dictamenes_hab[$row['dictamen']])) {
        $dictamenes_hab[$row['dictamen']] = $row['count'];
    }
}

// Consolidamos los dictámenes
$total_conformes = $dictamenes_edif['conforme'] + $dictamenes_hab['C'];
$total_no_conformes = $dictamenes_edif['no conforme'] + $dictamenes_hab['CO']; // Agrupamos 'no conforme' y 'con observaciones'
$total_otros = $dictamenes_edif['conforme con observaciones'] + $dictamenes_hab['R'] + $dictamenes_hab['A'];


// 4. Monto Recaudado
$query_monto = "SELECT SUM(p.monto) as total FROM pagos p" .
               " JOIN ( " .
               "   SELECT id, id_sesion FROM expedientes_edificaciones " .
               "   UNION ALL " .
               "   SELECT id, id_sesion FROM expedientes_habilitaciones " .
               " ) exp ON p.id_expediente = exp.id" .
               " JOIN sesiones s ON exp.id_sesion = s.id" . $user_filter_condition;

$monto_recaudado = $conn->query($query_monto)->fetch_assoc()['total'] ?? 0;


// 5. Actividad Reciente (Últimas 5 sesiones)
$user_filter_sesiones = "";
if ($_SESSION['user_role'] != 'administrador') {
    $user_id = $_SESSION['user_id'];
    $user_filter_sesiones = " WHERE s.id_usuario = $user_id";
}
$query_sesiones = "SELECT s.*, 
                    (SELECT COUNT(*) FROM expedientes_edificaciones WHERE id_sesion = s.id) + 
                    (SELECT COUNT(*) FROM expedientes_habilitaciones WHERE id_sesion = s.id) as total_expedientes 
                   FROM sesiones s " . $user_filter_sesiones . " ORDER BY s.fecha_sesion DESC, s.id DESC LIMIT 5";
$sesiones_recientes = $conn->query($query_sesiones);


// ---- INCLUDES DE LA VISTA ----
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!-- ======================================================= -->
<!-- AÑADIR ESTE BLOQUE PARA MOSTRAR MENSAJES DE ALERTA      -->
<!-- ======================================================= -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php 
    // Limpiamos el mensaje para que no se muestre de nuevo al recargar
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
?>
<?php endif; ?>
<!-- ======================================================= -->
<!-- FIN DEL BLOQUE DE MENSAJES                            -->
<!-- ======================================================= -->

<!-- Contenido específico de la página del Dashboard -->
<h1 class="mt-4">Panel de Control Global</h1>
<p>Resumen de actividad y estadísticas en tiempo real.</p>

<!-- Fila de Tarjetas de Estadísticas -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h5 class="card-title">Expedientes Totales</h5>
                <p class="fs-1 fw-bold"><?php echo $total_expedientes; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h5 class="card-title">Dictamen Conforme</h5>
                <p class="fs-1 fw-bold text-success"><?php echo $total_conformes; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h5 class="card-title">Dictamen No Conforme</h5>
                <p class="fs-1 fw-bold text-danger"><?php echo $total_no_conformes; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h5 class="card-title">Monto Recaudado</h5>
                <p class="fs-1 fw-bold text-primary">S/ <?php echo number_format($monto_recaudado, 2); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Fila de Gráfico y Actividad Reciente -->
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="card-title">Distribución de Dictámenes</h5>
                <canvas id="dictamenesChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="card-title">Actividad Reciente</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nº Sesión</th>
                                <th>Fecha</th>
                                <th>Expedientes</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sesiones_recientes->num_rows > 0): ?>
                                <?php while($sesion = $sesiones_recientes->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($sesion['numero_sesion']); ?></strong></td>
                                        <td><?php echo date("d/m/Y", strtotime($sesion['fecha_sesion'])); ?></td>
                                        <td><?php echo $sesion['total_expedientes']; ?></td>
                                        <td><a href="#">Ver / Editar</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay sesiones recientes.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT PARA INICIALIZAR EL GRÁFICO -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('dictamenesChart');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Conforme', 'No Conforme', 'Otros'],
            datasets: [{
                label: 'Dictámenes',
                // Pasamos los datos de PHP a JavaScript
                data: [
                    <?php echo $total_conformes; ?>,
                    <?php echo $total_no_conformes; ?>,
                    <?php echo $total_otros; ?>
                ],
                backgroundColor: [
                    'rgba(25, 135, 84, 0.7)',  // Verde
                    'rgba(220, 53, 69, 0.7)',  // Rojo
                    'rgba(255, 193, 7, 0.7)'   // Amarillo
                ],
                borderColor: [
                    'rgba(25, 135, 84, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
        }
    });
});
</script>

<?php
// ---- INCLUIR PIE DE PÁGINA ----
require_once 'includes/footer.php';
?>