<?php
// Obtenemos el nombre del archivo de la página actual (ej: "dashboard.php") para saber qué enlace resaltar.
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="bg-light border-right" id="sidebar-wrapper">
    <div class="sidebar-heading text-center py-4">
        <a href="dashboard.php">
            <img src="assets/images/logo.png" alt="Logo CAP Junín" width="100">
        </a>
    </div>
    <div class="list-group list-group-flush">
        
        <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Dashboard</span>
        </a>
        
        <?php // Mostrar "Nuevo Expediente" solo a los usuarios de comisión
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'comision') : ?>
            <a href="nuevo_expediente.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'nuevo_expediente.php' || $current_page == 'editar_expediente.php') ? 'active' : ''; ?>">
                <i class="fas fa-plus fa-fw me-3"></i><span>Nuevo Expediente</span>
            </a>
        <?php endif; ?>

        <a href="mis_sesiones.php" class="list-group-item list-group-item-action <?php echo in_array($current_page, ['mis_sesiones.php', 'ver_sesion.php', 'ver_expediente.php']) ? 'active' : ''; ?>">
            <i class="fas fa-folder-open fa-fw me-3"></i><span>Sesiones Comisiones</span>
        </a>

        <a href="busqueda_avanzada.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'busqueda_avanzada.php') ? 'active' : ''; ?>">
            <i class="fas fa-search fa-fw me-3"></i><span>Búsqueda Avanzada</span>
        </a>

        <a href="liquidaciones.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'liquidaciones.php') ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar fa-fw me-3"></i><span>Liquidaciones</span>
        </a>

        <a href="estadisticas.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'estadisticas.php') ? 'active' : ''; ?>">
            <i class="fas fa-chart-line fa-fw me-3"></i><span>Estadísticas</span>
        </a>
        
        <?php // Menú solo para Administradores
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'administrador') : ?>
            <hr class="sidebar-divider my-2">
            
            <a href="gestionar_usuarios.php" class="list-group-item list-group-item-action <?php echo in_array($current_page, ['gestionar_usuarios.php', 'editar_usuario.php']) ? 'active' : ''; ?>">
                <i class="fas fa-users-cog fa-fw me-3"></i><span>Gestionar Usuarios</span>
            </a>
            
            <a href="conf_pagos.php" class="list-group-item list-group-item-action <?php echo ($current_page == 'conf_pagos.php') ? 'active' : ''; ?>">
                <i class="fas fa-credit-card fa-fw me-3"></i><span>Conf. Pagos</span>
            </a>
        <?php endif; ?>
    </div>
</div>
<!-- /#sidebar-wrapper -->

<!-- Page Content -->
<div id="page-content-wrapper">
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container-fluid">
            <!-- Este botón es para mostrar/ocultar el menú en móviles, lo haremos funcional después -->
            <button class="btn btn-primary d-md-none" id="menu-toggle">Menú</button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user me-2"></i>Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="mi_perfil.php">Mi Perfil</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">Cerrar Sesión</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-4">