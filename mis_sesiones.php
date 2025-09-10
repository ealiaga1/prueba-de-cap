<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// --- 1. OBTENER TÉRMINOS DE BÚSQUEDA ---
$search_delegado = $_GET['search_delegado'] ?? '';
$search_expediente = $_GET['search_expediente'] ?? '';

// --- 2. CONSTRUIR LA CONSULTA SQL ---
$query_edif = "
    SELECT 
        s.id as sesion_id, s.numero_sesion, s.provincia, s.distrito,
        u.nombre_completo as registrado_por,
        exp.id as expediente_id, exp.numero_expediente, exp.dictamen,
        'Edificación' as comision, 'edif' as tipo_short, exp.administrado as propietario_o_administrado
    FROM expedientes_edificaciones exp
    JOIN sesiones s ON exp.id_sesion = s.id
    JOIN usuarios u ON s.id_usuario = u.id
";

$query_hab = "
    SELECT 
        s.id as sesion_id, s.numero_sesion, s.provincia, s.distrito,
        u.nombre_completo as registrado_por,
        exp.id as expediente_id, exp.numero_expediente, exp.dictamen,
        'Habilitación Urbana' as comision, 'hab' as tipo_short, exp.propietario as propietario_o_administrado
    FROM expedientes_habilitaciones exp
    JOIN sesiones s ON exp.id_sesion = s.id
    JOIN usuarios u ON s.id_usuario = u.id
";

// Unimos las dos consultas base
$query_base = "({$query_edif}) UNION ALL ({$query_hab})";

// La consulta final será una selección de la unión de ambas tablas
$final_query = "SELECT * FROM ({$query_base}) AS expedientes_unidos";

// Creamos un array para las condiciones WHERE
$where_clauses = [];

// Filtro por rol
if ($_SESSION['user_role'] != 'administrador') {
    // Necesitamos obtener los IDs de sesión del usuario
    $user_id = $_SESSION['user_id'];
    $sesiones_usuario_result = $conn->query("SELECT id FROM sesiones WHERE id_usuario = $user_id");
    $sesiones_usuario_ids = [];
    while($row = $sesiones_usuario_result->fetch_assoc()){
        $sesiones_usuario_ids[] = $row['id'];
    }
    if(!empty($sesiones_usuario_ids)){
        $where_clauses[] = "sesion_id IN (" . implode(',', $sesiones_usuario_ids) . ")";
    } else {
        $where_clauses[] = "sesion_id = 0"; // Para no mostrar nada si el usuario no tiene sesiones
    }
}

// Filtro por Delegado
if (!empty($search_delegado)) {
    // OBTENEMOS EL ID DE LA SESIÓN BASADO EN EL DELEGADO. ESTO ES UN CAMBIO IMPORTANTE.
    $escaped_delegado = $conn->real_escape_string($search_delegado);
    $sesiones_delegado_result = $conn->query("SELECT id FROM sesiones WHERE delegado LIKE '%{$escaped_delegado}%'");
    $sesiones_delegado_ids = [];
    while($row = $sesiones_delegado_result->fetch_assoc()){
        $sesiones_delegado_ids[] = $row['id'];
    }
    if(!empty($sesiones_delegado_ids)){
        $where_clauses[] = "sesion_id IN (" . implode(',', $sesiones_delegado_ids) . ")";
    } else {
        $where_clauses[] = "sesion_id = 0";
    }
}


// Filtro por Número de Expediente
if (!empty($search_expediente)) {
    $escaped_expediente = $conn->real_escape_string($search_expediente);
    $where_clauses[] = "numero_expediente = '{$escaped_expediente}'";
}

// Construimos el WHERE
if (!empty($where_clauses)) {
    $final_query .= " WHERE " . implode(' AND ', $where_clauses);
}

// El orden es CRUCIAL
$final_query .= " ORDER BY numero_sesion, provincia, distrito, expediente_id";
$result = $conn->query($final_query);

// --- 3. PROCESAR LOS RESULTADOS PARA AGRUPARLOS ---
$grupos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $key = $row['numero_sesion'] . '_' . $row['provincia'] . '_' . $row['distrito'];
        if (!isset($grupos[$key])) {
            $grupos[$key] = [
                'sesion_id' => $row['sesion_id'],
                'numero_sesion' => $row['numero_sesion'],
                'provincia' => $row['provincia'],
                'distrito' => $row['distrito'],
                'expedientes' => []
            ];
        }
        $grupos[$key]['expedientes'][] = $row;
    }
} else {
    // Si la consulta falla, esto nos dará una pista
    echo "Error en la consulta: " . $conn->error;
}
?>

<h1 class="mt-4">Reporte de Expedientes por Sesión</h1>
<p>Listado de expedientes agrupados por Nº de Sesión y Ubicación.</p>

<div class="card mb-4">
    <div class="card-header"><i class="fas fa-search me-1"></i> Buscador</div>
    <div class="card-body">
        <form action="mis_sesiones.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="search_delegado" class="form-label">Buscar por Delegado de Sesión</label>
                <input type="text" class="form-control" name="search_delegado" id="search_delegado" value="<?php echo htmlspecialchars($search_delegado); ?>" placeholder="Nombre del delegado...">
            </div>
            <div class="col-md-5">
                <label for="search_expediente" class="form-label">Buscar por Nº de Expediente</label>
                <input type="text" class="form-control" name="search_expediente" id="search_expediente" value="<?php echo htmlspecialchars($search_expediente); ?>" placeholder="Número exacto del expediente...">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($grupos)): ?>
    <div class="alert alert-info mt-4">No se encontraron expedientes que coincidan con los criterios de búsqueda.</div>
<?php else: ?>
    <?php foreach ($grupos as $grupo): ?>
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold">Sesión N°:</span> <?php echo htmlspecialchars($grupo['numero_sesion']); ?>
                    <span class="mx-3">|</span>
                    <span class="fw-bold">Provincia:</span> <?php echo htmlspecialchars($grupo['provincia']); ?>
                    <span class="mx-3">|</span>
                    <span class="fw-bold">Distrito:</span> <?php echo htmlspecialchars($grupo['distrito']); ?>
                </div>
                <div>
                    <!-- El botón de imprimir ahora apunta al ID de la sesión para ser más preciso -->
                    <a href="imprimir_sesion.php?numero_sesion=<?php echo urlencode($grupo['numero_sesion']); ?>&provincia=<?php echo urlencode($grupo['provincia']); ?>&distrito=<?php echo urlencode($grupo['distrito']); ?>" class="btn btn-sm btn-warning" target="_blank">
                        <i class="fas fa-print"></i> Imprimir este Grupo
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nº Expediente</th>
                                <th>Propietario / Administrado</th>
                                <th>Registrado por</th>
                                <th>Comisión</th>
                                <th>Dictamen</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupo['expedientes'] as $expediente): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($expediente['numero_expediente']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($expediente['propietario_o_administrado']); ?></td>
                                    <td><?php echo htmlspecialchars($expediente['registrado_por']); ?></td>
                                    <td><span class="badge bg-<?php echo $expediente['tipo_short'] == 'edif' ? 'primary' : 'info'; ?>"><?php echo htmlspecialchars($expediente['comision']); ?></span></td>
                                    <td><?php echo htmlspecialchars($expediente['dictamen']); ?></td>
                                    <td class="text-center">
                                        <a href="ver_expediente.php?tipo=<?php echo $expediente['tipo_short']; ?>&id=<?php echo $expediente['expediente_id']; ?>" class="btn btn-sm btn-outline-secondary" title="Ver Detalle del Expediente">
                                            <i class="fas fa-eye"></i> Ver Detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?>