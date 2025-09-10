<?php
// Incluimos los archivos base
require_once 'includes/auth_check.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// --- BLOQUE DE MENSAJES DE SESIÓN ---
if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
endif;
// --- FIN DEL BLOQUE ---

// =======================================================
//        AÑADIR ESTE BLOQUE DE SEGURIDAD
// =======================================================
// Si el rol del usuario es 'administrador', le denegamos el acceso a esta página.
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'administrador') {
    echo '<h1 class="mt-4">Acceso Denegado</h1>';
    echo '<div class="alert alert-danger">Los administradores no tienen permiso para crear nuevos expedientes. Esta función está reservada para los usuarios de las comisiones.</div>';

    // Incluimos el pie de página y detenemos la ejecución del script.
    require_once 'includes/footer.php';
    exit; // Detiene la carga del resto de la página
}
// =======================================================
//       FIN DEL BLOQUE DE SEGURIDAD
// =======================================================

// Verificamos la comisión del usuario logueado
$comision_usuario = $_SESSION['user_commission'];

// Usamos un switch para decidir qué formulario incluir.
// Esto hace el código más limpio y escalable si hubiera más comisiones en el futuro.
switch ($comision_usuario) {
    case 'edificaciones':
        // Si el usuario es de la comisión de edificaciones, incluimos su formulario.
        include('forms/form_edificaciones.php');
        break;

    case 'habilitaciones_urbanas':
        // Si el usuario es de la comisión de habilitaciones urbanas, incluimos el suyo.
        include('forms/form_habilitaciones.php');
        break;

    default:
        // Si por alguna razón el usuario no tiene una comisión válida, mostramos un error.
        echo '<div class="alert alert-danger">Error: No tiene una comisión asignada.</div>';
        break;
}

// Incluimos el pie de página
require_once 'includes/footer.php';
?>
