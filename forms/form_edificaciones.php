<?php
// Si la variable $expediente existe (viene de editar_expediente.php), estamos en modo "Edición".
$edit_mode = isset($expediente);
?>

<form action="procesar_expediente.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
    
    <!-- Campos ocultos que le dicen al procesador que es una ACTUALIZACIÓN -->
    <?php if ($edit_mode): ?>
        <input type="hidden" name="id_expediente" value="<?php echo htmlspecialchars($expediente['id']); ?>">
        <input type="hidden" name="id_sesion" value="<?php echo htmlspecialchars($sesion['id']); ?>">
    <?php endif; ?>
    <input type="hidden" name="tipo_expediente" value="edificacion">

    <h1 class="mt-4"><?php echo $edit_mode ? 'Editando Expediente de Edificación' : 'Nuevo Expediente de Edificación'; ?></h1>
    <p><?php echo $edit_mode ? 'Modifique los datos necesarios y guarde los cambios.' : 'Rellena los datos de la sesión y añade los expedientes correspondientes.'; ?></p>
    
    <!-- Sección 1: Datos de la Sesión -->
    <div class="card mb-4">
        <div class="card-header fw-bold">1. Datos de la Sesión</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3"><label for="fecha_sesion" class="form-label">Fecha</label><input type="date" class="form-control" id="fecha_sesion" name="fecha_sesion" value="<?php echo $edit_mode ? htmlspecialchars($sesion['fecha_sesion']) : ''; ?>" required></div>
                <div class="col-md-3 mb-3"><label for="numero_sesion" class="form-label">Número de Sesión</label><input type="text" class="form-control" id="numero_sesion" name="numero_sesion" value="<?php echo $edit_mode ? htmlspecialchars($sesion['numero_sesion']) : ''; ?>" required></div>
                <div class="col-md-3 mb-3"><label for="delegado" class="form-label">Delegado</label><input type="text" class="form-control" id="delegado" name="delegado" value="<?php echo $edit_mode ? htmlspecialchars($sesion['delegado']) : ''; ?>" required></div>
                <div class="col-md-3 mb-3"><label for="cap_delegado" class="form-label">Nº CAP</label><input type="text" class="form-control" id="cap_delegado" name="cap_delegado" value="<?php echo $edit_mode ? htmlspecialchars($sesion['cap_delegado']) : ''; ?>" required></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="provincia" class="form-label">Provincia</label>
                    <select class="form-select" id="provincia" name="provincia" required>
                        <option value="" disabled <?php echo !$edit_mode ? 'selected' : ''; ?>>Seleccione una provincia</option>
                        <option value="Chanchamayo" <?php echo ($edit_mode && $sesion['provincia'] == 'Chanchamayo') ? 'selected' : ''; ?>>Chanchamayo</option>
                        <option value="Chupaca" <?php echo ($edit_mode && $sesion['provincia'] == 'Chupaca') ? 'selected' : ''; ?>>Chupaca</option>
                        <option value="Concepción" <?php echo ($edit_mode && $sesion['provincia'] == 'Concepción') ? 'selected' : ''; ?>>Concepción</option>
                        <option value="Huancayo" <?php echo ($edit_mode && $sesion['provincia'] == 'Huancayo') ? 'selected' : ''; ?>>Huancayo</option>
                        <option value="Jauja" <?php echo ($edit_mode && $sesion['provincia'] == 'Jauja') ? 'selected' : ''; ?>>Jauja</option>
                        <option value="Junín" <?php echo ($edit_mode && $sesion['provincia'] == 'Junín') ? 'selected' : ''; ?>>Junín</option>
                        <option value="Satipo" <?php echo ($edit_mode && $sesion['provincia'] == 'Satipo') ? 'selected' : ''; ?>>Satipo</option>
                        <option value="Tarma" <?php echo ($edit_mode && $sesion['provincia'] == 'Tarma') ? 'selected' : ''; ?>>Tarma</option>
                        <option value="Yauli" <?php echo ($edit_mode && $sesion['provincia'] == 'Yauli') ? 'selected' : ''; ?>>Yauli</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="distrito" class="form-label">Distrito</label>
                    <select class="form-select" id="distrito" name="distrito" data-distrito-seleccionado="<?php echo $edit_mode ? htmlspecialchars($sesion['distrito']) : ''; ?>" required <?php echo !$edit_mode ? 'disabled' : ''; ?>>
                        <option value="" disabled selected>Seleccione una provincia primero</option>
                    </select>
                </div>
                 <div class="col-md-4 mb-3"><label class="form-label">Comisión</label><input type="text" class="form-control bg-light" value="Edificaciones" readonly></div>
            </div>
        </div>
    </div>
    
    <!-- Sección 2: Datos del Expediente -->
    <div class="card mb-4">
        <div class="card-header fw-bold">2. Datos del Expediente</div>
        <div class="card-body">
             <div class="row">
                <div class="col-md-4 mb-3"><label for="modalidad" class="form-label">Modalidad</label><select class="form-select" id="modalidad" name="modalidad" required><option value="Modalidad C" <?php echo ($edit_mode && $expediente['modalidad'] == 'Modalidad C') ? 'selected' : ''; ?>>Modalidad C</option><option value="Modalidad D" <?php echo ($edit_mode && $expediente['modalidad'] == 'Modalidad D') ? 'selected' : ''; ?>>Modalidad D</option></select></div>
                <div class="col-md-4 mb-3"><label for="numero_expediente" class="form-label">Número de Expediente</label><input type="text" class="form-control" id="numero_expediente" name="numero_expediente" value="<?php echo $edit_mode ? htmlspecialchars($expediente['numero_expediente']) : ''; ?>" required></div>
                <div class="col-md-4 mb-3"><label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label><input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo $edit_mode ? htmlspecialchars($expediente['fecha_ingreso']) : ''; ?>" required></div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block fw-semibold">Presentación (Seleccione uno o más)</label>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="AT" id="p_at" <?php echo ($edit_mode && in_array('AT', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_at">AT - Anteproyecto</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="LE" id="p_le" <?php echo ($edit_mode && in_array('LE', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_le">LE - Licencia de Edificación</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="RL" id="p_rl" <?php echo ($edit_mode && in_array('RL', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_rl">RL - Regularización Licencia</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="RVL" id="p_rvl" <?php echo ($edit_mode && in_array('RVL', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_rvl">RVL - Revalidación Licencia</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="MP" id="p_mp" <?php echo ($edit_mode && in_array('MP', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_mp">MP - Modificación de Proyecto</label></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block fw-semibold">Tipo de Obra (Seleccione uno o más)</label>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_obra[]" value="EN" id="to_en" <?php echo ($edit_mode && in_array('EN', $tipo_obra_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="to_en">EN - Edificación Nueva</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_obra[]" value="A" id="to_a" <?php echo ($edit_mode && in_array('A', $tipo_obra_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="to_a">A - Ampliación</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_obra[]" value="R" id="to_r" <?php echo ($edit_mode && in_array('R', $tipo_obra_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="to_r">R - Remodelación</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_obra[]" value="DT" id="to_dt" <?php echo ($edit_mode && in_array('DT', $tipo_obra_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="to_dt">DT - Demolición Total</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_obra[]" value="DP" id="to_dp" <?php echo ($edit_mode && in_array('DP', $tipo_obra_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="to_dp">DP - Demolición Parcial</label></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_obra[]" value="C" id="to_c" <?php echo ($edit_mode && in_array('C', $tipo_obra_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="to_c">C - Cercado</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_obra[]" value="AC" id="to_ac" <?php echo ($edit_mode && in_array('AC', $tipo_obra_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="to_ac">AC - Acondicionamiento</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_obra[]" value="RF" id="to_rf" <?php echo ($edit_mode && in_array('RF', $tipo_obra_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="to_rf">RF - Refacción</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="tipo_obra[]" value="PVHM" id="to_pvhm" <?php echo ($edit_mode && in_array('PVHM', $tipo_obra_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="to_pvhm">PVHM - Puesta en Valor Histórico</label></div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div>
                <label class="form-label d-block fw-semibold">Usos (Seleccione uno o más)</label>
                <div class="row">
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="1" id="u_1" <?php echo ($edit_mode && in_array('1', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_1">1 - Vivienda Unifamiliar</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="2" id="u_2" <?php echo ($edit_mode && in_array('2', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_2">2 - Vivienda Multifamiliar</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="3" id="u_3" <?php echo ($edit_mode && in_array('3', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_3">3 - V. con Usos Comp.</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="4" id="u_4" <?php echo ($edit_mode && in_array('4', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_4">4 - Comercio</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="5" id="u_5" <?php echo ($edit_mode && in_array('5', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_5">5 - Oficina</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="6" id="u_6" <?php echo ($edit_mode && in_array('6', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_6">6 - Industria</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="7" id="u_7" <?php echo ($edit_mode && in_array('7', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_7">7 - Salud</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="8" id="u_8" <?php echo ($edit_mode && in_array('8', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_8">8 - Educación</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="9" id="u_9" <?php echo ($edit_mode && in_array('9', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_9">9 - Hospedaje</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="10" id="u_10" <?php echo ($edit_mode && in_array('10', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_10">10 - Serv. Comunales</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="11" id="u_11" <?php echo ($edit_mode && in_array('11', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_11">11 - Recreación y Deporte</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="12" id="u_12" <?php echo ($edit_mode && in_array('12', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_12">12 - Transp. y Com.</label></div></div>
                    <div class="col-lg-3 col-md-4 col-sm-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="13" id="u_13" <?php echo ($edit_mode && in_array('13', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_13">13 - Otros</label></div></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sección 3: Datos de Revisión -->
    <div class="card mb-4">
        <div class="card-header fw-bold">3. Datos de Revisión</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3"><label for="fecha_revision" class="form-label">Fecha de Revisión</label><input type="date" class="form-control" id="fecha_revision" name="fecha_revision" value="<?php echo $edit_mode ? htmlspecialchars($expediente['fecha_revision']) : ''; ?>"></div>
                <div class="col-md-4 mb-3"><label for="numero_revision" class="form-label">N° de Revisión</label><input type="number" class="form-control" id="numero_revision" name="numero_revision" min="1" value="<?php echo $edit_mode ? htmlspecialchars($expediente['numero_revision']) : ''; ?>"></div>
                <div class="col-md-4 mb-3"><label for="dictamen" class="form-label">Dictamen</label><select class="form-select" id="dictamen" name="dictamen"><option value="" <?php echo ($edit_mode && empty($expediente['dictamen'])) ? 'selected' : ''; ?>>Seleccionar dictamen...</option><option value="conforme" <?php echo ($edit_mode && $expediente['dictamen'] == 'conforme') ? 'selected' : ''; ?>>Conforme</option><option value="no conforme" <?php echo ($edit_mode && $expediente['dictamen'] == 'no conforme') ? 'selected' : ''; ?>>No Conforme</option><option value="conforme con observaciones" <?php echo ($edit_mode && $expediente['dictamen'] == 'conforme con observaciones') ? 'selected' : ''; ?>>Conforme con Observaciones</option></select></div>
            </div>
            <div class="row align-items-end">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Recursos (si aplica)</label>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="reconsideracion" value="1" id="rec_rec" <?php echo ($edit_mode && $expediente['recurso_reconsideracion'] == 1) ? 'checked' : ''; ?>><label class="form-check-label" for="rec_rec">Recurso de Reconsideración</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="apelacion" value="1" id="rec_ape" <?php echo ($edit_mode && $expediente['recurso_apelacion'] == 1) ? 'checked' : ''; ?>><label class="form-check-label" for="rec_ape">Recurso de Apelación</label></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="archivo_revision" class="form-label">Anexar Archivo de Revisión (PDF, JPG)</label>
                    <input class="form-control" type="file" id="archivo_revision" name="archivo_revision" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if ($edit_mode && !empty($expediente['archivo_revision'])): ?>
                        <div class="form-text">Archivo actual: <a href="<?php echo htmlspecialchars($expediente['archivo_revision']); ?>" target="_blank">Ver Archivo</a>. Deje este campo vacío para no cambiarlo.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección 4: Datos del Inmueble -->
    <div class="card mb-4"><div class="card-header fw-bold">4. Datos del Inmueble</div><div class="card-body"><div class="row"><div class="col-md-3 mb-3"><label for="area_terreno" class="form-label">Área Terreno (m²)</label><input type="number" step="0.01" class="form-control" id="area_terreno" name="area_terreno" value="<?php echo $edit_mode ? htmlspecialchars($expediente['area_terreno']) : ''; ?>"></div><div class="col-md-3 mb-3"><label for="area_techada" class="form-label">Área Techada (m²)</label><input type="number" step="0.01" class="form-control" id="area_techada" name="area_techada" value="<?php echo $edit_mode ? htmlspecialchars($expediente['area_techada']) : ''; ?>"></div><div class="col-md-3 mb-3"><label for="altura_pisos" class="form-label">Altura (Nº Pisos)</label><input type="number" class="form-control" id="altura_pisos" name="altura_pisos" value="<?php echo $edit_mode ? htmlspecialchars($expediente['altura_pisos']) : ''; ?>"></div><div class="col-md-3 mb-3"><label for="altura_metros" class="form-label">Altura (metros)</label><input type="number" step="0.01" class="form-control" id="altura_metros" name="altura_metros" value="<?php echo $edit_mode ? htmlspecialchars($expediente['altura_metros']) : ''; ?>"></div></div><div class="mb-3"><label for="administrado" class="form-label">Administrado</label><input type="text" class="form-control" id="administrado" name="administrado" value="<?php echo $edit_mode ? htmlspecialchars($expediente['administrado']) : ''; ?>"></div></div></div>
    
    <!-- Sección 5: Datos del Proyectista -->
    <div class="card mb-4"><div class="card-header fw-bold">5. Datos del Proyectista</div><div class="card-body"><div class="row"><div class="col-md-8 mb-3"><label for="proyectista_responsable" class="form-label">Profesional Responsable</label><input type="text" class="form-control" id="proyectista_responsable" name="proyectista_responsable" value="<?php echo $edit_mode ? htmlspecialchars($expediente['proyectista_responsable']) : ''; ?>" required></div><div class="col-md-4 mb-3"><label for="cap_proyectista" class="form-label">Nº CAP</label><input type="text" class="form-control" id="cap_proyectista" name="cap_proyectista" value="<?php echo $edit_mode ? htmlspecialchars($expediente['cap_proyectista']) : ''; ?>" required></div></div></div></div>
    
    <!-- Sección 6: Datos de Pago (No se editan, solo se añaden nuevos) -->
    <div class="card mb-4">
        <div class="card-header fw-bold">6. Datos de Pago</div>
        <div class="card-body" id="seccion_pagos">
            <?php if ($edit_mode): ?>
                <p class="form-text">Los pagos existentes no se pueden editar. Puede añadir nuevos comprobantes si es necesario.</p>
            <?php endif; ?>
            <div class="pago-item mb-3 p-3 border rounded">
                <div class="row">
                    <div class="col-md-4 mb-3"><label for="credipago_0" class="form-label">Nº Credipago</label><input type="text" class="form-control" id="credipago_0" name="pagos[0][credipago]"></div>
                    <div class="col-md-4 mb-3"><label for="monto_0" class="form-label">Monto (S/.)</label><input type="number" step="0.01" class="form-control" id="monto_0" name="pagos[0][monto]"></div>
                    <div class="col-md-4 mb-3"><label for="fecha_pago_0" class="form-label">Fecha de Pago</label><input type="date" class="form-control" id="fecha_pago_0" name="pagos[0][fecha_pago]"></div>
                </div>
                <div class="mb-3"><label for="comprobante_0" class="form-label">Anexar comprobante (PDF, JPG)</label><input class="form-control" type="file" id="comprobante_0" name="pagos[0][comprobante]" accept=".pdf,.jpg,.jpeg,.png"></div>
            </div>
            <div id="pagos_adicionales"></div>
        </div>
        <div class="card-footer text-end"><button type="button" class="btn btn-success" id="btn_add_pago">Añadir Otro Comprobante</button></div>
    </div>
    
    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-lg px-5"><?php echo $edit_mode ? 'Guardar Cambios' : 'Guardar Expediente'; ?></button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const distritosPorProvincia = {'Chanchamayo': ['Chanchamayo', 'Perené', 'Pichanaqui', 'San Luis de Shuaro', 'San Ramón', 'Vítoc'],'Chupaca': ['Chupaca', 'Ahuac', 'Chongos Bajo', 'Huachac', 'Huamancaca Chico', 'San Juan de Iscos', 'San Juan de Jarpa', 'Tres de Diciembre', 'Yanacancha'],'Concepción': ['Concepción', 'Aco', 'Andamarca', 'Chambara', 'Cochas', 'Comas', 'Heroínas Toledo', 'Manzanares', 'Mariscal Castilla', 'Matahuasi', 'Mito', 'Nueve de Julio', 'Orcotuna', 'San José de Quero', 'Santa Rosa de Ocopa'],'Huancayo': ['Huancayo', 'Carhuacallanga', 'Chacapampa', 'Chicche', 'Chilca', 'Chongos Alto', 'Chupuro', 'Colca', 'Cullhuas', 'El Tambo', 'Huacrapuquio', 'Hualhuas', 'Huancán', 'Huasicancha', 'Huayucachi', 'Ingenio', 'Pariahuanca', 'Pilcomayo', 'Pucará', 'Quichuay', 'Quilcas', 'San Agustín de Cajas', 'San Jerónimo de Tunán', 'Saño', 'Sapallanga', 'Sicaya', 'Viques'],'Jauja': ['Jauja', 'Acolla', 'Apata', 'Ataura', 'Canchayllo', 'Curicaca', 'El Mantaro', 'Huamalí', 'Huaripampa', 'Huertas', 'Janjaillo', 'Julcán', 'Leonor Ordóñez', 'Llocllapampa', 'Marco', 'Masma', 'Masma Chicche', 'Molinos', 'Monobamba', 'Muqui', 'Muquiyauyo', 'Paca', 'Paccha', 'Pancán', 'Parco', 'Pomacancha', 'Ricrán', 'San Lorenzo', 'San Pedro de Chunán', 'Sausa', 'Sincos', 'Tunanmarca', 'Yauli', 'Yauyos'],'Junín': ['Junín', 'Carhuamayo', 'Ondores', 'Ulcumayo'],'Satipo': ['Satipo', 'Coviriali', 'Llaylla', 'Mazamari', 'Pampa Hermosa', 'Pangoa', 'Río Negro', 'Río Tambo', 'Vizcatán del Ene'],'Tarma': ['Tarma', 'Acobamba', 'Huaricolca', 'Huasahuasi', 'La Unión', 'Palca', 'Palcamayo', 'San Pedro de Cajas', 'Tapo'],'Yauli': ['La Oroya', 'Chacapalpa', 'Huay-Huay', 'Marcapomacocha', 'Morococha', 'Paccha', 'Santa Bárbara de Carhuacayán', 'Santa Rosa de Sacco', 'Suitucancha', 'Yauli']};
    const provinciaSelect = document.getElementById('provincia');
    const distritoSelect = document.getElementById('distrito');

    function populateDistritos() {
        const provinciaSeleccionada = provinciaSelect.value;
        const distritoGuardado = distritoSelect.getAttribute('data-distrito-seleccionado');
        distritoSelect.innerHTML = '<option value="" disabled selected>Cargando...</option>';
        if (provinciaSeleccionada && distritosPorProvincia[provinciaSeleccionada]) {
            distritoSelect.disabled = false;
            distritoSelect.innerHTML = '<option value="" disabled selected>Seleccione un distrito</option>';
            distritosPorProvincia[provinciaSeleccionada].forEach(function(distrito) {
                const option = document.createElement('option');
                option.value = distrito;
                option.textContent = distrito;
                if (distrito === distritoGuardado) {
                    option.selected = true;
                }
                distritoSelect.appendChild(option);
            });
        } else {
            distritoSelect.disabled = true;
            distritoSelect.innerHTML = '<option value="" disabled selected>Seleccione una provincia primero</option>';
        }
    }
    provinciaSelect.addEventListener('change', populateDistritos);
    if (provinciaSelect.value) { populateDistritos(); }

    let pagoIndex = 1;
    document.getElementById('btn_add_pago').addEventListener('click', function() { const divAdicionales = document.getElementById('pagos_adicionales'); const nuevoPagoHTML = `<div class="pago-item mb-3 p-3 border rounded position-relative"><button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" aria-label="Close" onclick="this.parentElement.remove()"></button><hr><h6>Comprobante Adicional</h6><div class="row"><div class="col-md-4 mb-3"><label for="credipago_${pagoIndex}" class="form-label">Nº Credipago</label><input type="text" class="form-control" id="credipago_${pagoIndex}" name="pagos[${pagoIndex}][credipago]"></div><div class="col-md-4 mb-3"><label for="monto_${pagoIndex}" class="form-label">Monto (S/.)</label><input type="number" step="0.01" class="form-control" id="monto_${pagoIndex}" name="pagos[${pagoIndex}][monto]"></div><div class="col-md-4 mb-3"><label for="fecha_pago_${pagoIndex}" class="form-label">Fecha de Pago</label><input type="date" class="form-control" id="fecha_pago_${pagoIndex}" name="pagos[${pagoIndex}][fecha_pago]"></div></div><div class="mb-3"><label for="comprobante_${pagoIndex}" class="form-label">Anexar comprobante (PDF, JPG)</label><input class="form-control" type="file" id="comprobante_${pagoIndex}" name="pagos[${pagoIndex}][comprobante]" accept=".pdf,.jpg,.jpeg,.png"></div></div>`; divAdicionales.insertAdjacentHTML('beforeend', nuevoPagoHTML); pagoIndex++; });
});
</script>