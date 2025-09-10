<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// --- 1. PREPARAR VARIABLES DE BÚSQUEDA Y PAGINACIÓN ---
$is_search_performed = isset($_GET['perform_search']);
$keywords = $_GET['keywords'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$provincia = $_GET['provincia'] ?? '';
$dictamen = $_GET['dictamen'] ?? '';
$tipo_expediente = $_GET['tipo_expediente'] ?? 'todos';

$resultados = [];

// --- 2. SI SE REALIZÓ UNA BÚSQUEDA, CONSTRUIR Y EJECUTAR LA CONSULTA ---
if ($is_search_performed) {
    
    // Array para las condiciones WHERE que se aplicarán a ambas tablas
    $where_clauses = [];

    if (!empty($keywords)) {
        $escaped_keywords = $conn->real_escape_string($keywords);
        $where_clauses[] = "(exp.numero_expediente LIKE '%{$escaped_keywords}%' OR exp.proyectista_responsable LIKE '%{$escaped_keywords}%' OR exp.cap_proyectista LIKE '%{$escaped_keywords}%')";
    }
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $where_clauses[] = "exp.fecha_ingreso BETWEEN '{$conn->real_escape_string($fecha_inicio)}' AND '{$conn->real_escape_string($fecha_fin)}'";
    }
    if (!empty($provincia)) {
        $where_clauses[] = "s.provincia = '{$conn->real_escape_string($provincia)}'";
    }
    if (!empty($dictamen)) {
        $where_clauses[] = "exp.dictamen = '{$conn->real_escape_string($dictamen)}'";
    }

    // Construir la parte WHERE de la consulta
    $where_sql = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : '';

    // Construir las dos partes de la consulta (Edificaciones y Habilitaciones)
    $sql_edif = "SELECT exp.id, exp.numero_expediente, exp.fecha_ingreso, exp.proyectista_responsable, exp.dictamen, 'edif' as tipo_short, 'Edificación' as tipo_nombre 
                 FROM expedientes_edificaciones exp JOIN sesiones s ON exp.id_sesion = s.id {$where_sql}";
    
    $sql_hab = "SELECT exp.id, exp.numero_expediente, exp.fecha_ingreso, exp.proyectista_responsable, exp.dictamen, 'hab' as tipo_short, 'Habilitación Urbana' as tipo_nombre 
                FROM expedientes_habilitaciones exp JOIN sesiones s ON exp.id_sesion = s.id {$where_sql}";

    // Unir las consultas según el filtro de tipo de expediente
    if ($tipo_expediente == 'edificaciones') {
        $final_sql = $sql_edif;
    } elseif ($tipo_expediente == 'habilitaciones') {
        $final_sql = $sql_hab;
    } else { // 'todos'
        $final_sql = "{$sql_edif} UNION ALL {$sql_hab}";
    }

    $final_sql .= " ORDER BY fecha_ingreso DESC";
    
    $result = $conn->query($final_sql);
    if($result) {
        $resultados = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<h1 class="mt-4">Búsqueda Avanzada de Expedientes</h1>
<p>Utilice los filtros para encontrar expedientes específicos en todo el sistema.</p>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-search me-1"></i> Filtros de Búsqueda</div>
    <div class="card-body">
        <form action="busqueda_avanzada.php" method="GET">
            <input type="hidden" name="perform_search" value="1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="keywords" class="form-label">Palabras Clave</label>
                    <input type="text" class="form-control" name="keywords" id="keywords" value="<?php echo htmlspecialchars($keywords); ?>" placeholder="Nº Expediente, Proyectista, Nº CAP...">
                </div>
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha Ingreso (Desde)</label>
                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha Ingreso (Hasta)</label>
                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                </div>
                <div class="col-md-4">
                    <label for="provincia" class="form-label">Provincia</label>
                    <select class="form-select" name="provincia" id="provincia">
                        <option value="">Cualquiera</option>
                        <option value="Huancayo" <?php echo ($provincia == 'Huancayo') ? 'selected' : ''; ?>>Huancayo</option>
                        <!-- Aquí puedes añadir el resto de provincias -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="dictamen" class="form-label">Dictamen</label>
                    <select class="form-select" name="dictamen" id="dictamen">
                        <option value="">Cualquiera</option>
                        <optgroup label="Edificaciones">
                            <option value="conforme" <?php echo ($dictamen == 'conforme') ? 'selected' : ''; ?>>Conforme</option>
                            <option value="no conforme" <?php echo ($dictamen == 'no conforme') ? 'selected' : ''; ?>>No Conforme</option>
                            <option value="conforme con observaciones" <?php echo ($dictamen == 'conforme con observaciones') ? 'selected' : ''; ?>>Conforme con Observaciones</option>
                        </optgroup>
                        <optgroup label="Habilitaciones Urbanas">
                            <option value="C" <?php echo ($dictamen == 'C') ? 'selected' : ''; ?>>C - Conforme</option>
                            <option value="CO" <?php echo ($dictamen == 'CO') ? 'selected' : ''; ?>>CO - Con Observaciones</option>
                            <option value="R" <?php echo ($dictamen == 'R') ? 'selected' : ''; ?>>R - Reconsideración</option>
                            <option value="A" <?php echo ($dictamen == 'A') ? 'selected' : ''; ?>>A - Apelación</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="tipo_expediente" class="form-label">Tipo de Expediente</label>
                    <select class="form-select" name="tipo_expediente" id="tipo_expediente">
                        <option value="todos" <?php echo ($tipo_expediente == 'todos') ? 'selected' : ''; ?>>Todos</option>
                        <option value="edificaciones" <?php echo ($tipo_expediente == 'edificaciones') ? 'selected' : ''; ?>>Solo Edificaciones</option>
                        <option value="habilitaciones" <?php echo ($tipo_expediente == 'habilitaciones') ? 'selected' : ''; ?>>Solo Habilitaciones Urbanas</option>
                    </select>
                </div>
            </div>
            <div class="text-end mt-3">
                <a href="busqueda_avanzada.php" class="btn btn-secondary">Limpiar Filtros</a>
                <button type="submit" class="btn btn-primary">Buscar Expedientes</button>
            </div>
        </form>
    </div>
</div>

<?php if ($is_search_performed): ?>
<div class="card mt-4">
    <div class="card-header"><i class="fas fa-list-ul me-1"></i> Resultados de la Búsqueda (<?php echo count($resultados); ?> encontrados)</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nº Expediente</th>
                        <th>Tipo</th>
                        <th>Fecha Ingreso</th>
                        <th>Proyectista</th>
                        <th>Dictamen</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($resultados)): ?>
                        <?php foreach ($resultados as $exp): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($exp['numero_expediente']); ?></strong></td>
                            <td><span class="badge bg-<?php echo $exp['tipo_short'] == 'edif' ? 'primary' : 'info'; ?>"><?php echo htmlspecialchars($exp['tipo_nombre']); ?></span></td>
                            <td><?php echo date("d/m/Y", strtotime($exp['fecha_ingreso'])); ?></td>
                            <td><?php echo htmlspecialchars($exp['proyectista_responsable']); ?></td>
                            <td><?php echo htmlspecialchars($exp['dictamen']); ?></td>
                            <td>
                                <a href="ver_expediente.php?tipo=<?php echo $exp['tipo_short']; ?>&id=<?php echo $exp['id']; ?>" class="btn btn-sm btn-outline-secondary">Ver Detalle</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No se encontraron expedientes con los criterios seleccionados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>