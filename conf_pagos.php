<?php
require_once 'includes/auth_check.php';
if ($_SESSION['user_role'] != 'administrador') { die("Acceso denegado."); }
require_once 'includes/db_connection.php';

// Lógica para guardar los cambios si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $configs = $_POST['config'];
    $stmt = $conn->prepare("UPDATE configuracion_liquidaciones SET porcentaje_delegado = ?, porcentaje_cap = ? WHERE id = ?");
    foreach ($configs as $id => $values) {
        $delegado = floatval($values['delegado']);
        $cap = floatval($values['cap']);
        // Validación simple para asegurar que suman 100
        if (($delegado + $cap) == 100.00) {
            $stmt->bind_param("ddi", $delegado, $cap, $id);
            $stmt->execute();
        }
    }
    $_SESSION['message'] = "Configuración guardada con éxito.";
    $_SESSION['message_type'] = "success";
    header("Location: conf_pagos.php");
    exit;
}

// Obtener la configuración actual de la base de datos
$result = $conn->query("SELECT * FROM configuracion_liquidaciones ORDER BY id");
$configuraciones = $result->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<h1 class="mt-4">Configuración de Liquidaciones</h1>
<p>Establece los porcentajes de pago para el Delegado y el CAP según el tipo de dictamen.</p>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
<?php endif; ?>

<div class="card">
    <div class="card-header fw-bold">Porcentajes de Distribución de Pagos</div>
    <div class="card-body">
        <form action="conf_pagos.php" method="POST">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Tipo de Dictamen</th>
                        <th width="25%">% para Delegado</th>
                        <th width="25%">% para CAP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($configuraciones as $config): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($config['dictamen_label']); ?></strong></td>
                        <td>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" class="form-control" name="config[<?php echo $config['id']; ?>][delegado]" value="<?php echo htmlspecialchars($config['porcentaje_delegado']); ?>" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </td>
                        <td>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" class="form-control" name="config[<?php echo $config['id']; ?>][cap]" value="<?php echo htmlspecialchars($config['porcentaje_cap']); ?>" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Guardar Configuración</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>