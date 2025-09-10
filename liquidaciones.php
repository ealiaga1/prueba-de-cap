<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';

// --- 1. OBTENER LA CONFIGURACIÓN DE LIQUIDACIONES ---
$result_configs = $conn->query("SELECT dictamen_key, porcentaje_delegado, porcentaje_cap FROM configuracion_liquidaciones");
$configs = [];
while ($row = $result_configs->fetch_assoc()) {
    // Creamos un array asociativo para fácil acceso: 'conforme' => ['porcentaje_delegado' => 50.00, ...]
    $configs[$row['dictamen_key']] = $row;
}

// --- 2. OBTENER FILTROS (OPCIONAL, PARA FUTURAS MEJORAS) ---
// Por ahora, mostraremos todo. Podríamos añadir filtros de fecha como en estadísticas.

// --- 3. CONSTRUIR LA CONSULTA PRINCIPAL ---
$where_clauses = [];
// Filtro por rol para usuarios de comisión
if ($_SESSION['user_role'] != 'administrador') {
    $user_id = $_SESSION['user_id'];
    $where_clauses[] = "s.id_usuario = $user_id";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : '';

// Consulta que une expedientes (de ambos tipos) con sus sesiones y pagos
$sql = "
    SELECT 
        s.delegado,
        exp.numero_expediente,
        exp.dictamen,
        p.monto,
        'Edificación' as comision
    FROM expedientes_edificaciones exp
    JOIN sesiones s ON exp.id_sesion = s.id
    JOIN pagos p ON exp.id = p.id_expediente AND p.tipo_expediente = 'edificacion'
    $where_sql

    UNION ALL

    SELECT 
        s.delegado,
        exp.numero_expediente,
        exp.dictamen,
        p.monto,
        'Habilitación Urbana' as comision
    FROM expedientes_habilitaciones exp
    JOIN sesiones s ON exp.id_sesion = s.id
    JOIN pagos p ON exp.id = p.id_expediente AND p.tipo_expediente = 'habilitacion'
    $where_sql

    ORDER BY delegado, numero_expediente
";

$result_liquidaciones = $conn->query($sql);

// --- 4. PROCESAR LOS DATOS Y REALIZAR LOS CÁLCULOS ---
$liquidaciones = [];
$totales = ['monto_total' => 0, 'total_delegado' => 0, 'total_cap' => 0];

if ($result_liquidaciones) {
    while ($row = $result_liquidaciones->fetch_assoc()) {
        $dictamen_key = $row['dictamen'];
        $monto = floatval($row['monto']);

        // Obtener porcentajes de la configuración. Si no existe, usar 0.
        $porcentaje_delegado = $configs[$dictamen_key]['porcentaje_delegado'] ?? 0;
        $porcentaje_cap = $configs[$dictamen_key]['porcentaje_cap'] ?? 100;

        // Calcular los montos
        $monto_delegado = $monto * ($porcentaje_delegado / 100);
        $monto_cap = $monto * ($porcentaje_cap / 100);

        // Añadir al array de resultados
        $liquidaciones[] = [
            'delegado' => $row['delegado'],
            'numero_expediente' => $row['numero_expediente'],
            'comision' => $row['comision'],
            'dictamen' => $dictamen_key,
            'monto' => $monto,
            'monto_delegado' => $monto_delegado,
            'monto_cap' => $monto_cap
        ];

        // Sumar a los totales
        $totales['monto_total'] += $monto;
        $totales['total_delegado'] += $monto_delegado;
        $totales['total_cap'] += $monto_cap;
    }
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<h1 class="mt-4">Reporte de Liquidaciones</h1>
<p>Cálculo de la distribución de pagos según la configuración establecida.</p>

<div class="card">
    <div class="card-header fw-bold">
        <i class="fas fa-file-invoice-dollar me-2"></i>Detalle de Liquidaciones
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Delegado</th>
                        <th>Nº Expediente</th>
                        <th>Comisión</th>
                        <th>Dictamen</th>
                        <th class="text-end">Monto Total (S/.)</th>
                        <th class="text-end bg-info bg-opacity-25">Monto Delegado (S/.)</th>
                        <th class="text-end bg-success bg-opacity-25">Monto CAP (S/.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($liquidaciones)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay expedientes con pagos registrados para liquidar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($liquidaciones as $liq): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($liq['delegado']); ?></td>
                            <td><?php echo htmlspecialchars($liq['numero_expediente']); ?></td>
                            <td><?php echo htmlspecialchars($liq['comision']); ?></td>
                            <td><?php echo htmlspecialchars($liq['dictamen']); ?></td>
                            <td class="text-end"><?php echo number_format($liq['monto'], 2); ?></td>
                            <td class="text-end bg-info bg-opacity-10"><?php echo number_format($liq['monto_delegado'], 2); ?></td>
                            <td class="text-end bg-success bg-opacity-10"><?php echo number_format($liq['monto_cap'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="fw-bold">
                    <tr class="table-dark">
                        <td colspan="4" class="text-end">TOTALES GENERALES</td>
                        <td class="text-end"><?php echo number_format($totales['monto_total'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($totales['total_delegado'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($totales['total_cap'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>