<?php
// 1. --- INICIALIZACIÓN Y SEGURIDAD ---
session_start();
require_once 'includes/db_connection.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    die("Acceso denegado. Por favor, inicie sesión.");
}
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Método no permitido.");
}

// 2. --- FUNCIÓN AUXILIAR PARA SUBIR ARCHIVOS ---
function handleFileUpload($file, $destinationFolder) {
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        return null;
    }
    $uploadDir = 'uploads/' . $destinationFolder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName = time() . '_' . uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    } else {
        return null;
    }
}

// 3. --- LÓGICA PRINCIPAL: DETERMINAR SI ES CREACIÓN O ACTUALIZACIÓN ---

// Si se envía un id_expediente, es una ACTUALIZACIÓN.
if (isset($_POST['id_expediente']) && !empty($_POST['id_expediente'])) {
    
    // =======================================================
    // =============== LÓGICA DE ACTUALIZACIÓN ===============
    // =======================================================
    $id_expediente = intval($_POST['id_expediente']);
    $id_sesion = intval($_POST['id_sesion']);
    $tipo_expediente = $_POST['tipo_expediente'];

    $conn->begin_transaction();
    try {
        // --- A. ACTUALIZAR LA TABLA `sesiones` ---
        $stmt_sesion = $conn->prepare("UPDATE sesiones SET fecha_sesion = ?, numero_sesion = ?, delegado = ?, cap_delegado = ?, provincia = ?, distrito = ? WHERE id = ?");
        $stmt_sesion->bind_param("ssssssi", $_POST['fecha_sesion'], $_POST['numero_sesion'], $_POST['delegado'], $_POST['cap_delegado'], $_POST['provincia'], $_POST['distrito'], $id_sesion);
        $stmt_sesion->execute();

        // --- B. ACTUALIZAR EL EXPEDIENTE (EDIFICACIÓN O HABILITACIÓN) ---
        if ($tipo_expediente == 'edificacion') {
            
            $stmt_old_file = $conn->prepare("SELECT archivo_revision FROM expedientes_edificaciones WHERE id = ?");
            $stmt_old_file->bind_param("i", $id_expediente);
            $stmt_old_file->execute();
            $old_file_path = $stmt_old_file->get_result()->fetch_assoc()['archivo_revision'];
            $new_file_path = handleFileUpload($_FILES['archivo_revision'], 'revisiones');
            $final_file_path = $new_file_path ?? $old_file_path;

            $presentacion_str = isset($_POST['presentacion']) ? implode(',', $_POST['presentacion']) : '';
            $tipo_obra_str = isset($_POST['tipo_obra']) ? implode(',', $_POST['tipo_obra']) : '';
            $usos_str = isset($_POST['usos']) ? implode(',', $_POST['usos']) : '';
            $reconsideracion = isset($_POST['reconsideracion']) ? 1 : 0;
            $apelacion = isset($_POST['apelacion']) ? 1 : 0;

            $stmt_exp = $conn->prepare("UPDATE expedientes_edificaciones SET modalidad = ?, numero_expediente = ?, fecha_ingreso = ?, presentacion = ?, tipo_obra = ?, usos = ?, fecha_revision = ?, numero_revision = ?, dictamen = ?, recurso_reconsideracion = ?, recurso_apelacion = ?, archivo_revision = ?, area_terreno = ?, area_techada = ?, altura_pisos = ?, altura_metros = ?, administrado = ?, proyectista_responsable = ?, cap_proyectista = ? WHERE id = ?");
            $stmt_exp->bind_param("ssssssisissddddsssi", $_POST['modalidad'], $_POST['numero_expediente'], $_POST['fecha_ingreso'], $presentacion_str, $tipo_obra_str, $usos_str, $_POST['fecha_revision'], $_POST['numero_revision'], $_POST['dictamen'], $reconsideracion, $apelacion, $final_file_path, $_POST['area_terreno'], $_POST['area_techada'], $_POST['altura_pisos'], $_POST['altura_metros'], $_POST['administrado'], $_POST['proyectista_responsable'], $_POST['cap_proyectista'], $id_expediente);
            $stmt_exp->execute();
            $redirect_tipo = 'edif';

        } elseif ($tipo_expediente == 'habilitacion') {
            
            $stmt_old_file = $conn->prepare("SELECT archivo_vias FROM expedientes_habilitaciones WHERE id = ?");
            $stmt_old_file->bind_param("i", $id_expediente);
            $stmt_old_file->execute();
            $old_file_path = $stmt_old_file->get_result()->fetch_assoc()['archivo_vias'];
            $new_file_path = handleFileUpload($_FILES['archivo_vias'], 'revisiones');
            $final_file_path = $new_file_path ?? $old_file_path;

            $presentacion_str = isset($_POST['presentacion']) ? implode(',', $_POST['presentacion']) : '';
            $usos_str = isset($_POST['usos']) ? implode(',', $_POST['usos']) : '';
            $reconsideracion = isset($_POST['reconsideracion']) ? 1 : 0;
            $apelacion = isset($_POST['apelacion']) ? 1 : 0;

            $stmt_exp = $conn->prepare("UPDATE expedientes_habilitaciones SET modalidad = ?, numero_expediente = ?, fecha_ingreso = ?, presentacion = ?, presentacion_otros = ?, usos = ?, fecha_revision = ?, numero_revision = ?, dictamen = ?, ancho_vias = ?, archivo_vias = ?, recurso_reconsideracion = ?, recurso_apelacion = ?, ubicacion_predio = ?, propietario = ?, area_terreno = ?, proyectista_responsable = ?, cap_proyectista = ? WHERE id = ?");
            $stmt_exp->bind_param("sssssssisdsiissdssi", $_POST['modalidad'], $_POST['numero_expediente'], $_POST['fecha_ingreso'], $presentacion_str, $_POST['presentacion_otros'], $usos_str, $_POST['fecha_revision'], $_POST['numero_revision'], $_POST['dictamen'], $_POST['ancho_vias'], $final_file_path, $reconsideracion, $apelacion, $_POST['ubicacion_predio'], $_POST['propietario'], $_POST['area_terreno'], $_POST['proyectista_responsable'], $_POST['cap_proyectista'], $id_expediente);
            $stmt_exp->execute();
            $redirect_tipo = 'hab';
        }

        // --- C. AÑADIR NUEVOS PAGOS (no se editan los antiguos) ---
        if (isset($_POST['pagos']) && is_array($_POST['pagos'])) {
            $tipo_pago_db = ($tipo_expediente == 'edificacion') ? 'edificacion' : 'habilitacion';
            $stmt_pago = $conn->prepare("INSERT INTO pagos (id_expediente, tipo_expediente, numero_credipago, monto, fecha_pago, comprobante_ruta) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['pagos'] as $index => $pago) {
                if (!empty($pago['credipago'])) {
                    $comprobante_file = ['name' => $_FILES['pagos']['name'][$index]['comprobante'], 'type' => $_FILES['pagos']['type'][$index]['comprobante'], 'tmp_name' => $_FILES['pagos']['tmp_name'][$index]['comprobante'], 'error' => $_FILES['pagos']['error'][$index]['comprobante'], 'size' => $_FILES['pagos']['size'][$index]['comprobante']];
                    $ruta_comprobante = handleFileUpload($comprobante_file, 'comprobantes');
                    if ($ruta_comprobante || !empty($pago['monto'])) {
                         $stmt_pago->bind_param("issdss", $id_expediente, $tipo_pago_db, $pago['credipago'], $pago['monto'], $pago['fecha_pago'], $ruta_comprobante);
                         $stmt_pago->execute();
                    }
                }
            }
        }

        $conn->commit();
        $_SESSION['message'] = "Expediente actualizado con éxito.";
        $_SESSION['message_type'] = "success";
        header("Location: ver_expediente.php?tipo=" . $redirect_tipo . "&id=" . $id_expediente);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error al actualizar el expediente: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
        header("Location: editar_expediente.php?tipo=" . $tipo_expediente . "&id=" . $id_expediente);
        exit();
    }

} else {

    // =======================================================
    // ================= LÓGICA DE CREACIÓN ==================
    // =======================================================
    
    $tipo_expediente = $_POST['tipo_expediente'];
    
    // Aquí va la lógica de creación que me enviaste, la copio y pego íntegra.
    if ($tipo_expediente == 'edificacion') {
        $conn->begin_transaction();
        try {
            $stmt_sesion = $conn->prepare("INSERT INTO sesiones (id_usuario, fecha_sesion, numero_sesion, delegado, cap_delegado, provincia, distrito, tipo_comision) VALUES (?, ?, ?, ?, ?, ?, ?, 'edificaciones')");
            $id_usuario = $_SESSION['user_id'];
            $stmt_sesion->bind_param("issssss", $id_usuario, $_POST['fecha_sesion'], $_POST['numero_sesion'], $_POST['delegado'], $_POST['cap_delegado'], $_POST['provincia'], $_POST['distrito']);
            $stmt_sesion->execute();
            $id_sesion_creada = $conn->insert_id;
            if ($id_sesion_creada == 0) throw new Exception("Error al crear la sesión.");
            $presentacion_str = isset($_POST['presentacion']) ? implode(',', $_POST['presentacion']) : '';
            $tipo_obra_str = isset($_POST['tipo_obra']) ? implode(',', $_POST['tipo_obra']) : '';
            $usos_str = isset($_POST['usos']) ? implode(',', $_POST['usos']) : '';
            $reconsideracion = isset($_POST['reconsideracion']) ? 1 : 0;
            $apelacion = isset($_POST['apelacion']) ? 1 : 0;
            $ruta_archivo_revision = handleFileUpload($_FILES['archivo_revision'], 'revisiones');
            $stmt_expediente = $conn->prepare("INSERT INTO expedientes_edificaciones (id_sesion, modalidad, numero_expediente, fecha_ingreso, presentacion, tipo_obra, usos, fecha_revision, numero_revision, dictamen, recurso_reconsideracion, recurso_apelacion, archivo_revision, area_terreno, area_techada, altura_pisos, altura_metros, administrado, proyectista_responsable, cap_proyectista) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $fecha_revision = !empty($_POST['fecha_revision']) ? $_POST['fecha_revision'] : null;
            $numero_revision = !empty($_POST['numero_revision']) ? $_POST['numero_revision'] : null;
            $dictamen = !empty($_POST['dictamen']) ? $_POST['dictamen'] : null;
            $area_terreno = !empty($_POST['area_terreno']) ? $_POST['area_terreno'] : null;
            $area_techada = !empty($_POST['area_techada']) ? $_POST['area_techada'] : null;
            $altura_pisos = !empty($_POST['altura_pisos']) ? $_POST['altura_pisos'] : null;
            $altura_metros = !empty($_POST['altura_metros']) ? $_POST['altura_metros'] : null;
            $stmt_expediente->bind_param("isssssssisissddddsss", $id_sesion_creada, $_POST['modalidad'], $_POST['numero_expediente'], $_POST['fecha_ingreso'], $presentacion_str, $tipo_obra_str, $usos_str, $fecha_revision, $numero_revision, $dictamen, $reconsideracion, $apelacion, $ruta_archivo_revision, $area_terreno, $area_techada, $altura_pisos, $altura_metros, $_POST['administrado'], $_POST['proyectista_responsable'], $_POST['cap_proyectista']);
            $stmt_expediente->execute();
            $id_expediente_creado = $conn->insert_id;
            if ($id_expediente_creado == 0) throw new Exception("Error al crear el expediente.");
            if (isset($_POST['pagos']) && is_array($_POST['pagos'])) {
                $stmt_pago = $conn->prepare("INSERT INTO pagos (id_expediente, tipo_expediente, numero_credipago, monto, fecha_pago, comprobante_ruta) VALUES (?, 'edificacion', ?, ?, ?, ?)");
                foreach ($_POST['pagos'] as $index => $pago) {
                    if (!empty($pago['credipago'])) {
                        $comprobante_file = ['name' => $_FILES['pagos']['name'][$index]['comprobante'], 'type' => $_FILES['pagos']['type'][$index]['comprobante'], 'tmp_name' => $_FILES['pagos']['tmp_name'][$index]['comprobante'], 'error' => $_FILES['pagos']['error'][$index]['comprobante'], 'size' => $_FILES['pagos']['size'][$index]['comprobante']];
                        $ruta_comprobante = handleFileUpload($comprobante_file, 'comprobantes');
                        $monto_pago = !empty($pago['monto']) ? $pago['monto'] : null;
                        $fecha_pago = !empty($pago['fecha_pago']) ? $pago['fecha_pago'] : null;
                        $stmt_pago->bind_param("isdss", $id_expediente_creado, $pago['credipago'], $monto_pago, $fecha_pago, $ruta_comprobante);
                        $stmt_pago->execute();
                    }
                }
            }
            $conn->commit();
            $_SESSION['message'] = "Expediente de Edificación guardado con éxito.";
            $_SESSION['message_type'] = "success";
            header("Location: dashboard.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error al guardar el expediente: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
            header("Location: nuevo_expediente.php");
            exit();
        }
    } elseif ($tipo_expediente == 'habilitacion') {
        $conn->begin_transaction();
        try {
            $stmt_sesion = $conn->prepare("INSERT INTO sesiones (id_usuario, fecha_sesion, numero_sesion, delegado, cap_delegado, provincia, distrito, tipo_comision) VALUES (?, ?, ?, ?, ?, ?, ?, 'habilitaciones_urbanas')");
            $id_usuario = $_SESSION['user_id'];
            $stmt_sesion->bind_param("issssss", $id_usuario, $_POST['fecha_sesion'], $_POST['numero_sesion'], $_POST['delegado'], $_POST['cap_delegado'], $_POST['provincia'], $_POST['distrito']);
            $stmt_sesion->execute();
            $id_sesion_creada = $conn->insert_id;
            if ($id_sesion_creada == 0) throw new Exception("Error al crear la sesión.");
            $presentacion_str = isset($_POST['presentacion']) ? implode(',', $_POST['presentacion']) : '';
            $usos_str = isset($_POST['usos']) ? implode(',', $_POST['usos']) : '';
            $reconsideracion = isset($_POST['reconsideracion']) ? 1 : 0;
            $apelacion = isset($_POST['apelacion']) ? 1 : 0;
            $ruta_archivo_vias = handleFileUpload($_FILES['archivo_vias'], 'revisiones');
            $stmt_expediente = $conn->prepare("INSERT INTO expedientes_habilitaciones (id_sesion, modalidad, numero_expediente, fecha_ingreso, presentacion, presentacion_otros, usos, fecha_revision, numero_revision, dictamen, ancho_vias, archivo_vias, recurso_reconsideracion, recurso_apelacion, ubicacion_predio, propietario, area_terreno, proyectista_responsable, cap_proyectista) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $fecha_revision = !empty($_POST['fecha_revision']) ? $_POST['fecha_revision'] : null;
            $numero_revision = !empty($_POST['numero_revision']) ? $_POST['numero_revision'] : null;
            $dictamen = !empty($_POST['dictamen']) ? $_POST['dictamen'] : null;
            $ancho_vias = !empty($_POST['ancho_vias']) ? $_POST['ancho_vias'] : null;
            $area_terreno = !empty($_POST['area_terreno']) ? $_POST['area_terreno'] : null;
            $stmt_expediente->bind_param("isssssssisdsiissdss", $id_sesion_creada, $_POST['modalidad'], $_POST['numero_expediente'], $_POST['fecha_ingreso'], $presentacion_str, $_POST['presentacion_otros'], $usos_str, $fecha_revision, $numero_revision, $dictamen, $ancho_vias, $ruta_archivo_vias, $reconsideracion, $apelacion, $_POST['ubicacion_predio'], $_POST['propietario'], $area_terreno, $_POST['proyectista_responsable'], $_POST['cap_proyectista']);
            $stmt_expediente->execute();
            $id_expediente_creado = $conn->insert_id;
            if ($id_expediente_creado == 0) throw new Exception("Error al crear el expediente.");
            if (isset($_POST['pagos']) && is_array($_POST['pagos'])) {
                $stmt_pago = $conn->prepare("INSERT INTO pagos (id_expediente, tipo_expediente, numero_credipago, monto, fecha_pago, comprobante_ruta) VALUES (?, 'habilitacion', ?, ?, ?, ?)");
                foreach ($_POST['pagos'] as $index => $pago) {
                    if (!empty($pago['credipago'])) {
                        $comprobante_file = ['name' => $_FILES['pagos']['name'][$index]['comprobante'], 'type' => $_FILES['pagos']['type'][$index]['comprobante'], 'tmp_name' => $_FILES['pagos']['tmp_name'][$index]['comprobante'], 'error' => $_FILES['pagos']['error'][$index]['comprobante'], 'size' => $_FILES['pagos']['size'][$index]['comprobante']];
                        $ruta_comprobante = handleFileUpload($comprobante_file, 'comprobantes');
                        $monto_pago = !empty($pago['monto']) ? $pago['monto'] : null;
                        $fecha_pago = !empty($pago['fecha_pago']) ? $pago['fecha_pago'] : null;
                        $stmt_pago->bind_param("isdss", $id_expediente_creado, $pago['credipago'], $monto_pago, $fecha_pago, $ruta_comprobante);
                        $stmt_pago->execute();
                    }
                }
            }
            $conn->commit();
            $_SESSION['message'] = "Expediente de Habilitación Urbana guardado con éxito.";
            $_SESSION['message_type'] = "success";
            header("Location: dashboard.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error al guardar el expediente: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
            header("Location: nuevo_expediente.php");
            exit();
        }
    }
}
?>