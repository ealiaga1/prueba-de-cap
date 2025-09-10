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
    <input type="hidden" name="tipo_expediente" value="habilitacion">

    <h1 class="mt-4"><?php echo $edit_mode ? 'Editando Expediente de Habilitación Urbana' : 'Nuevo Expediente de Habilitación Urbana'; ?></h1>
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
                 <div class="col-md-4 mb-3"><label class="form-label">Comisión</label><input type="text" class="form-control bg-light" value="Habilitaciones Urbanas" readonly></div>
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
                    <label class="form-label fw-semibold">Presentación (Seleccione uno o más)</label>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="HUN" id="p_hun" <?php echo ($edit_mode && in_array('HUN', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_hun">HUN - Habilitación Urbana Nueva</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="RHURO" id="p_rhuro" <?php echo ($edit_mode && in_array('RHURO', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_rhuro">RHURO - Regularización con recepción de Obra</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="RO" id="p_ro" <?php echo ($edit_mode && in_array('RO', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_ro">RO - Recepción de obra</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="MP" id="p_mp" <?php echo ($edit_mode && in_array('MP', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_mp">MP - Modificación de proyecto</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="HUPI" id="p_hupi" <?php echo ($edit_mode && in_array('HUPI', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_hupi">HUPI - Habilitación Urbana y Planeamiento Integral</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="HUMO" id="p_humo" <?php echo ($edit_mode && in_array('HUMO', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_humo">HUMO - Habilitación Urbana y Modificación de Proyecto</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="presentacion[]" value="O" id="p_o" <?php echo ($edit_mode && in_array('O', $presentacion_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="p_o">O - Otros</label></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Usos (Seleccione uno o más)</label>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="14" id="u_14" <?php echo ($edit_mode && in_array('14', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_14">14 - Vivienda</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="15" id="u_15" <?php echo ($edit_mode && in_array('15', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_15">15 - Comercio</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="16" id="u_16" <?php echo ($edit_mode && in_array('16', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_16">16 - Industria</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="17" id="u_17" <?php echo ($edit_mode && in_array('17', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_17">17 - Uso especial</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="18" id="u_18" <?php echo ($edit_mode && in_array('18', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_18">18 - Rivera y laderas</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="19" id="u_19" <?php echo ($edit_mode && in_array('19', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_19">19 - Reurbanización</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="usos[]" value="20" id="u_20" <?php echo ($edit_mode && in_array('20', $usos_array)) ? 'checked' : ''; ?>><label class="form-check-label" for="u_20">20 - Otros</label></div>
                </div>
            </div>
             <div class="mb-3">
                <label for="presentacion_otros" class="form-label">Especificar Presentación (si marcó "Otros")</label>
                <textarea class="form-control" id="presentacion_otros" name="presentacion_otros" rows="2"><?php echo $edit_mode ? htmlspecialchars($expediente['presentacion_otros']) : ''; ?></textarea>
            </div>
        </div>
    </div>

    <!-- Sección 3: Datos de Revisión y Vías -->
    <div class="card mb-4">
        <div class="card-header fw-bold">3. Datos de Revisión y Vías</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3"><label for="fecha_revision" class="form-label">Fecha de Revisión</label><input type="date" class="form-control" id="fecha_revision" name="fecha_revision" value="<?php echo $edit_mode ? htmlspecialchars($expediente['fecha_revision']) : ''; ?>"></div>
                <div class="col-md-3 mb-3"><label for="numero_revision" class="form-label">N° de Revisión</label><input type="number" class="form-control" id="numero_revision" name="numero_revision" min="1" value="<?php echo $edit_mode ? htmlspecialchars($expediente['numero_revision']) : ''; ?>"></div>
                <div class="col-md-3 mb-3"><label for="dictamen" class="form-label">Dictamen</label><select class="form-select" id="dictamen" name="dictamen">
        <option value="" <?php echo ($edit_mode && empty($expediente['dictamen'])) ? 'selected' : ''; ?>>Seleccionar...</option>
        <option value="C" <?php echo ($edit_mode && $expediente['dictamen'] == 'C') ? 'selected' : ''; ?>>C - Conforme</option>
        <option value="CO" <?php echo ($edit_mode && $expediente['dictamen'] == 'CO') ? 'selected' : ''; ?>>CO - Con Observaciones</option>
        <option value="NC" <?php echo ($edit_mode && $expediente['dictamen'] == 'NC') ? 'selected' : ''; ?>>NC - No Conforme</option>
    </select></div>
                <div class="col-md-3 mb-3"><label for="ancho_vias" class="form-label">Ancho de vías (mts)</label><input type="number" step="0.01" class="form-control" id="ancho_vias" name="ancho_vias" value="<?php echo $edit_mode ? htmlspecialchars($expediente['ancho_vias']) : ''; ?>"></div>
            </div>
             <div class="row align-items-end">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Recursos (si aplica)</label>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="reconsideracion" value="1" id="rec_rec_h" <?php echo ($edit_mode && $expediente['recurso_reconsideracion'] == 1) ? 'checked' : ''; ?>><label class="form-check-label" for="rec_rec_h">Recurso de Reconsideración</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="apelacion" value="1" id="rec_ape_h" <?php echo ($edit_mode && $expediente['recurso_apelacion'] == 1) ? 'checked' : ''; ?>><label class="form-check-label" for="rec_ape_h">Recurso de Apelación</label></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="archivo_vias" class="form-label">Anexar Archivo de Vías (PDF, JPG)</label>
                    <input class="form-control" type="file" id="archivo_vias" name="archivo_vias" accept=".pdf,.jpg,.jpeg,.png">
                    <?php if ($edit_mode && !empty($expediente['archivo_vias'])): ?>
                        <div class="form-text">Archivo actual: <a href="<?php echo htmlspecialchars($expediente['archivo_vias']); ?>" target="_blank">Ver Archivo</a>. Deje este campo vacío para no cambiarlo.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección 4: Datos del Terreno -->
    <div class="card mb-4"><div class="card-header fw-bold">4. Datos del Terreno</div><div class="card-body"><div class="row"><div class="col-md-8 mb-3"><label for="ubicacion_predio" class="form-label">Ubicación del Predio</label><input type="text" class="form-control" id="ubicacion_predio" name="ubicacion_predio" value="<?php echo $edit_mode ? htmlspecialchars($expediente['ubicacion_predio']) : ''; ?>"></div><div class="col-md-4 mb-3"><label for="area_terreno" class="form-label">Área del Terreno (m²)</label><input type="number" step="0.01" class="form-control" id="area_terreno" name="area_terreno" value="<?php echo $edit_mode ? htmlspecialchars($expediente['area_terreno']) : ''; ?>"></div></div><div class="mb-3"><label for="propietario" class="form-label">Propietario</label><input type="text" class="form-control" id="propietario" name="propietario" value="<?php echo $edit_mode ? htmlspecialchars($expediente['propietario']) : ''; ?>"></div></div></div>

    <!-- Sección 5: Datos del Proyectista -->
    <div class="card mb-4"><div class="card-header fw-bold">5. Datos del Proyectista</div><div class="card-body"><div class="row"><div class="col-md-8 mb-3"><label for="proyectista_responsable" class="form-label">Profesional Responsable</label><input type="text" class="form-control" id="proyectista_responsable" name="proyectista_responsable" value="<?php echo $edit_mode ? htmlspecialchars($expediente['proyectista_responsable']) : ''; ?>" required></div><div class="col-md-4 mb-3"><label for="cap_proyectista" class="form-label">Nº CAP</label><input type="text" class="form-control" id="cap_proyectista" name="cap_proyectista" value="<?php echo $edit_mode ? htmlspecialchars($expediente['cap_proyectista']) : ''; ?>" required></div></div></div></div>

    <!-- Sección 6: Datos de Pago (No se editan, solo se añaden nuevos) -->
    <div class="card mb-4">
        <div class="card-header fw-bold">6. Datos de Pago</div>
        <div class="card-body" id="seccion_pagos">
            <?php if ($edit_mode): ?>
                <p class="form-text">Los pagos existentes se muestran en la página de "Ver Detalle". Aquí puede añadir nuevos comprobantes si es necesario.</p>
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