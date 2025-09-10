<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// --- 1. OBTENER FILTROS DE LA URL ---
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
$tipo_comision = $_GET['tipo_comision'] ?? 'todos';
$search_proyectista = $_GET['search_proyectista'] ?? ''; // <-- NUEVA LÍNEA

// --- 2. CONSTRUIR CLÁUSULAS WHERE PARA LAS CONSULTAS ---
$where_clauses = ["exp.fecha_ingreso BETWEEN '{$conn->real_escape_string($fecha_inicio)}' AND '{$conn->real_escape_string($fecha_fin)}'"];
if (!empty($search_proyectista)) {
    $escaped_proyectista = $conn->real_escape_string($search_proyectista);
    $where_clauses[] = "exp.proyectista_responsable LIKE '%{$escaped_proyectista}%'";
}
if ($tipo_comision == 'edificaciones') {
    $where_clauses_edif = $where_clauses;
    $where_clauses_hab = ["1=0"];
} elseif ($tipo_comision == 'habilitaciones_urbanas') {
    $where_clauses_edif = ["1=0"];
    $where_clauses_hab = $where_clauses;
} else {
    $where_clauses_edif = $where_clauses_hab = $where_clauses;
}
$where_sql_edif = "WHERE " . implode(' AND ', $where_clauses_edif);
$where_sql_hab = "WHERE " . implode(' AND ', $where_clauses_hab);

// --- 3. EJECUTAR CONSULTAS PARA LOS GRÁFICOS ---

// GRÁFICO 1: Expedientes por Mes
$sql_tendencia = "(SELECT DATE_FORMAT(exp.fecha_ingreso, '%Y-%m') as mes, COUNT(*) as total FROM expedientes_edificaciones exp $where_sql_edif GROUP BY mes)
                  UNION ALL
                  (SELECT DATE_FORMAT(exp.fecha_ingreso, '%Y-%m') as mes, COUNT(*) as total FROM expedientes_habilitaciones exp $where_sql_hab GROUP BY mes)";
$result_tendencia = $conn->query("SELECT mes, SUM(total) as total_mes FROM ($sql_tendencia) as t GROUP BY mes ORDER BY mes ASC");
$tendencia_labels = []; $tendencia_data = [];
while ($row = $result_tendencia->fetch_assoc()) {
    $tendencia_labels[] = $row['mes'];
    $tendencia_data[] = $row['total_mes'];
}

// GRÁFICO 2: Distribución de Dictámenes
$sql_dictamen = "(SELECT dictamen, COUNT(*) as total FROM expedientes_edificaciones exp $where_sql_edif GROUP BY dictamen)
                 UNION ALL
                 (SELECT dictamen, COUNT(*) as total FROM expedientes_habilitaciones exp $where_sql_hab GROUP BY dictamen)";
$result_dictamen = $conn->query("SELECT dictamen, SUM(total) as total_dictamen FROM ($sql_dictamen) as t WHERE dictamen IS NOT NULL AND dictamen != '' GROUP BY dictamen");
$dictamen_labels = []; $dictamen_data = [];
while ($row = $result_dictamen->fetch_assoc()) {
    $dictamen_labels[] = $row['dictamen'];
    $dictamen_data[] = $row['total_dictamen'];
}

// GRÁFICO 3: Top 5 Provincias
$sql_provincias = "(SELECT s.provincia, COUNT(*) as total FROM expedientes_edificaciones exp JOIN sesiones s ON exp.id_sesion = s.id $where_sql_edif GROUP BY s.provincia)
                   UNION ALL
                   (SELECT s.provincia, COUNT(*) as total FROM expedientes_habilitaciones exp JOIN sesiones s ON exp.id_sesion = s.id $where_sql_hab GROUP BY s.provincia)";
$result_provincias = $conn->query("SELECT provincia, SUM(total) as total_provincia FROM ($sql_provincias) as t GROUP BY provincia ORDER BY total_provincia DESC LIMIT 5");
$provincia_labels = []; $provincia_data = [];
while ($row = $result_provincias->fetch_assoc()) {
    $provincia_labels[] = $row['provincia'];
    $provincia_data[] = $row['total_provincia'];
}

// GRÁFICO 4: Top 5 Proyectistas más Activos
$sql_proyectistas = "(SELECT proyectista_responsable, COUNT(*) as total FROM expedientes_edificaciones exp $where_sql_edif GROUP BY proyectista_responsable)
                     UNION ALL
                     (SELECT proyectista_responsable, COUNT(*) as total FROM expedientes_habilitaciones exp $where_sql_hab GROUP BY proyectista_responsable)";
$result_proyectistas = $conn->query("SELECT proyectista_responsable, SUM(total) as total_proyectista FROM ($sql_proyectistas) as t WHERE proyectista_responsable != '' GROUP BY proyectista_responsable ORDER BY total_proyectista DESC LIMIT 5");
$proyectista_labels = []; $proyectista_data = [];
while ($row = $result_proyectistas->fetch_assoc()) {
    $proyectista_labels[] = $row['proyectista_responsable'];
    $proyectista_data[] = $row['total_proyectista'];
}

// KPIS (INDICADORES CLAVE): Áreas y Alturas
$sql_kpis = "
    SELECT 
        SUM(area_terreno_total) as total_terreno,
        SUM(area_techada_total) as total_techado,
        AVG(altura_pisos_promedio) as promedio_pisos
    FROM (
        (SELECT SUM(area_terreno) as area_terreno_total, SUM(area_techada) as area_techada_total, AVG(altura_pisos) as altura_pisos_promedio FROM expedientes_edificaciones exp $where_sql_edif)
        UNION ALL
        (SELECT SUM(area_terreno) as area_terreno_total, NULL as area_techada_total, NULL as altura_pisos_promedio FROM expedientes_habilitaciones exp $where_sql_hab)
    ) as kpi_union
";
$kpis = $conn->query($sql_kpis)->fetch_assoc();
$total_terreno = $kpis['total_terreno'] ?? 0;
$total_techado = $kpis['total_techado'] ?? 0;
$promedio_pisos = $kpis['promedio_pisos'] ?? 0;
?>

<div class="d-flex justify-content-between align-items-center">
    <h1 class="mt-4">Estadísticas y Reportes</h1>
    <button onclick="window.print();" class="btn btn-warning">
        <i class="fas fa-print me-2"></i>Imprimir Reporte
    </button>
</div>
<p>Análisis visual de los datos de expedientes registrados en el sistema.</p>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-filter me-1"></i> Filtros</div>
    <div class="card-body">
        <form action="estadisticas.php" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4"><label for="fecha_inicio" class="form-label">Desde</label><input type="date" class="form-control" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>"></div>
                <div class="col-md-4"><label for="fecha_fin" class="form-label">Hasta</label><input type="date" class="form-control" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>"></div>
                <div class="col-md-4">
                    <label for="tipo_comision" class="form-label">Comisión</label>
                    <select name="tipo_comision" class="form-select">
                        <option value="todos" <?php echo ($tipo_comision == 'todos') ? 'selected' : ''; ?>>Todas</option>
                        <option value="edificaciones" <?php echo ($tipo_comision == 'edificaciones') ? 'selected' : ''; ?>>Edificaciones</option>
                        <option value="habilitaciones_urbanas" <?php echo ($tipo_comision == 'habilitaciones_urbanas') ? 'selected' : ''; ?>>Habilitaciones Urbanas</option>
                    </select>
                </div>
                
                <div class="col-md-3">
            <label for="search_proyectista" class="form-label">Proyectista</label>
            <input type="text" class="form-control" name="search_proyectista" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($search_proyectista); ?>">
        </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-chart-line me-1"></i> Expedientes Ingresados por Mes</div>
            <div class="card-body"><canvas id="tendenciaChart" width="100%" height="50"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-chart-pie me-1"></i> Distribución de Dictámenes</div>
            <div class="card-body"><canvas id="dictamenChart" width="100%" height="50"></canvas></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-map-marked-alt me-1"></i> Top 5 Provincias con más Expedientes</div>
            <div class="card-body"><canvas id="provinciaChart" width="100%" height="50"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-user-tie me-1"></i> Top 5 Proyectistas con más Expedientes</div>
            <div class="card-body"><canvas id="proyectistaChart" width="100%" height="50"></canvas></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card bg-info text-white mb-4">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold"><?php echo number_format($total_terreno, 2); ?> m²</div>
                <p class="mb-0">Área Total de Terreno (Edif. + Hab.)</p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold"><?php echo number_format($total_techado, 2); ?> m²</div>
                <p class="mb-0">Área Total Techada (Solo Edificaciones)</p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card bg-warning text-dark mb-4">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold"><?php echo number_format($promedio_pisos, 1); ?></div>
                <p class="mb-0">Altura Promedio en Pisos (Solo Edif.)</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('tendenciaChart'), {
        type: 'line',
        data: { labels: <?php echo json_encode($tendencia_labels); ?>, datasets: [{ label: "Nº de Expedientes", data: <?php echo json_encode($tendencia_data); ?>, borderColor: 'rgba(75, 192, 192, 1)', backgroundColor: 'rgba(75, 192, 192, 0.2)', fill: true, tension: 0.1 }] }
    });
    new Chart(document.getElementById('dictamenChart'), {
        type: 'doughnut',
        data: { labels: <?php echo json_encode($dictamen_labels); ?>, datasets: [{ data: <?php echo json_encode($dictamen_data); ?>, backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6c757d', '#fd7e14', '#20c997'] }] }
    });
    new Chart(document.getElementById('provinciaChart'), {
        type: 'bar',
        data: { labels: <?php echo json_encode($provincia_labels); ?>, datasets: [{ label: 'Total Expedientes', data: <?php echo json_encode($provincia_data); ?>, backgroundColor: 'rgba(54, 162, 235, 0.6)', borderColor: 'rgba(54, 162, 235, 1)', borderWidth: 1 }] },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
    });
    new Chart(document.getElementById('proyectistaChart'), {
        type: 'bar',
        data: { labels: <?php echo json_encode($proyectista_labels); ?>, datasets: [{ label: 'Total Expedientes', data: <?php echo json_encode($proyectista_data); ?>, backgroundColor: 'rgba(255, 159, 64, 0.6)', borderColor: 'rgba(255, 159, 64, 1)', borderWidth: 1 }] },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>