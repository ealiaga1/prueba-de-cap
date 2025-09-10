<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connection.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// 1. Validar parámetros
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['tipo'])) {
    echo '<div class="alert alert-danger">Parámetros no válidos.</div>';
    require_once 'includes/footer.php';
    exit;
}
$id_expediente = intval($_GET['id']);
$tipo_expediente_short = $_GET['tipo'];

// 2. Obtener todos los datos del expediente y su sesión
$expediente = null;
$sesion = null;

if ($tipo_expediente_short == 'edif') {
    $sql = "SELECT * FROM expedientes_edificaciones WHERE id = ?";
    $tipo_expediente_full = 'edificaciones';
} elseif ($tipo_expediente_short == 'hab') {
    $sql = "SELECT * FROM expedientes_habilitaciones WHERE id = ?";
    $tipo_expediente_full = 'habilitaciones_urbanas';
} else {
    echo '<div class="alert alert-danger">Tipo de expediente no reconocido.</div>';
    require_once 'includes/footer.php';
    exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_expediente);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $expediente = $result->fetch_assoc();
    
    // Ahora obtenemos los datos de la sesión
    $stmt_sesion = $conn->prepare("SELECT * FROM sesiones WHERE id = ?");
    $stmt_sesion->bind_param("i", $expediente['id_sesion']);
    $stmt_sesion->execute();
    $sesion = $stmt_sesion->get_result()->fetch_assoc();
} else {
     echo '<div class="alert alert-danger">No se encontró el expediente.</div>';
    require_once 'includes/footer.php';
    exit;
}

// 3. Preparar los datos para el formulario
// Convertimos las cadenas de texto de los checkboxes en arrays para que sea fácil marcarlos en el form
$presentacion_array = explode(',', $expediente['presentacion']);
$tipo_obra_array = isset($expediente['tipo_obra']) ? explode(',', $expediente['tipo_obra']) : [];
$usos_array = explode(',', $expediente['usos']);


// 4. Incluir el formulario correspondiente
if ($tipo_expediente_full == 'edificaciones') {
    // La variable $expediente, $sesion, y los arrays estarán disponibles dentro del form
    include('forms/form_edificaciones.php'); 
} elseif ($tipo_expediente_full == 'habilitaciones_urbanas') {
    // Faltaría adaptar el form_habilitaciones.php para la edición
    include('forms/form_habilitaciones.php');
}

require_once 'includes/footer.php';
?>