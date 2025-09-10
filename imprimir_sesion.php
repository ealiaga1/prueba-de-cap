<?php
// --- 1. CONFIGURACIÓN INICIAL Y CONEXIÓN ---
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

session_start();
require_once 'includes/db_connection.php';
require_once 'fpdf/rotation.php';

// --- 2. FUNCIÓN AUXILIAR PARA LA CODIFICACIÓN DE TEXTO ---
function fix_text($text) {
    $text = $text ?? '';
    return utf8_decode($text);
}

// --- 3. CLASE PDF COMPLETA (BASADA EN TU DISEÑO ORIGINAL) ---
class PDF_Reporte_Oficial extends PDF_Rotate 
{
    private $headerData;
    function __construct($orientation='L', $unit='mm', $size='A4', $data = []) {
        parent::__construct($orientation, $unit, $size);
        $this->headerData = $data;
    }
    function RotatedText($x, $y, $txt, $angle){
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }
    function Header() { 
        if (file_exists('assets/images/logo.png')) { $this->Image('assets/images/logo.png', 10, 8, 16); }
        $this->SetFont('Arial', 'B', 10); 
        $titulo = 'COMISION TECNICA CALIFICADORA DE PROYECTOS DE ' . strtoupper(fix_text($this->headerData['tipo_comision']));
        $titulo .= ' - DISTRITO ' . strtoupper(fix_text($this->headerData['distrito']));
        $this->Cell(0, 8, $titulo, 0, 1, 'C');
        $this->Ln(9);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(30, 6, fix_text('DELEGADO CAP:'), 0, 0); $this->SetFont('Arial', '', 10); $this->Cell(70, 6, fix_text($this->headerData['delegado']), 0, 0);
        $this->SetFont('Arial', 'B', 9); $this->Cell(15, 6, fix_text('CAP:'), 0, 0); $this->SetFont('Arial', '', 10); $this->Cell(45, 6, fix_text($this->headerData['cap_delegado']), 0, 0);
        $this->SetFont('Arial', 'B', 9); $this->Cell(15, 6, fix_text('FECHA:'), 0, 0); $this->SetFont('Arial', '', 10); $this->Cell(45, 6, date("d/m/Y", strtotime($this->headerData['fecha_sesion'])), 0, 0);
        $this->SetFont('Arial', 'B', 9); $this->Cell(25, 6, fix_text('SESION N°:'), 0, 0); $this->SetFont('Arial', 'B', 18); $this->Cell(0, 6, fix_text($this->headerData['numero_sesion']), 0, 1);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(30, 6, fix_text('DELEGADO CAP:'), 0, 0); $this->SetFont('Arial', '', 10); $this->Cell(70, 6, '................................................................', 0, 0);
        $this->SetFont('Arial', 'B', 9); $this->Cell(15, 6, fix_text('CAP:'), 0, 0); $this->SetFont('Arial', '', 10); $this->Cell(0, 6, '...................', 0, 1);
        $this->Ln(4); 
    }
    function Footer() {
        $this->SetY(-25);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(45, 4, fix_text('MODALIDAD (1)'), 0, 0, 'L');
        $this->Cell(50, 4, fix_text('PRESENTACION (2)'), 0, 0, 'L');
        $this->Cell(50, 4, fix_text('TIPOS (3)'), 0, 0, 'L');
        $this->Cell(55, 4, fix_text('DICTAMEN (5)'), 0, 0, 'L');
        $this->Cell(0, 4, fix_text('USOS (4)'), 0, 1, 'L');
        $this->SetFont('Arial', '', 6.5);
        $y1 = $this->GetY();
        $this->MultiCell(45, 3, fix_text("C: Modalidad C\nD: Modalidad D"), 0, 'L');
        $this->SetXY(55, $y1); $this->MultiCell(50, 3, fix_text("AT: Anteproyecto\nLE: Lic. Edificacion\nRL: Reg. Licencia\n...etc"), 0, 'L');
        $this->SetXY(105, $y1); $this->MultiCell(50, 3, fix_text("EN: Edif. Nueva\nA: Ampliación\nR: Remodelación\n...etc"), 0, 'L');
        $this->SetXY(155, $y1); $this->MultiCell(55, 3, fix_text("C: Conforme\nCO: Con Obs.\nNC: No Conforme\n...etc"), 0, 'L');
        $usos1 = "1: V.Unifamiliar\n2: V.Multifamiliar\n3: V.c/Usos Comp.\n4: Comercio\n5: Oficina";
        $usos2 = "6: Industria\n7: Salud\n8: Educación\n9: Hospedaje\n10: Serv.Comunales";
        $usos3 = "11: Rec. y Dep.\n12: Transp. y Com.\n13: Otros";
        $this->SetXY(210, $y1); $this->MultiCell(30, 3, fix_text($usos1), 0, 'L');
        $this->SetXY(240, $y1); $this->MultiCell(30, 3, fix_text($usos2), 0, 'L');
        $this->SetXY(270, $y1); $this->MultiCell(0, 3, fix_text($usos3), 0, 'L');
        $this->SetY(-10);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, fix_text('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    function FancyTable($data) {
        $this->SetLineWidth(.3); $this->SetDrawColor(0);
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230,230,230);
        $titulo_cuadro = 'CUADRO DE RESUMEN DE COMISIONES TECNICAS DE ' . strtoupper(fix_text($this->headerData['tipo_comision']));
        $this->Cell(277, 7, fix_text($titulo_cuadro), 1, 1, 'C', true);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(255, 228, 225); $this->Cell(127, 6, 'DATOS DEL EXPEDIENTE', 1, 0, 'C', true);
        $this->SetFillColor(220, 230, 241); $this->Cell(96, 6, 'DATOS DEL PROFESIONAL', 1, 0, 'C', true);
        $this->SetFillColor(220,220,220); $this->Cell(54, 6, 'PAGO DERECHO REVISION', 1, 1, 'C', true);
        $header_height = 15;
        $y_start_header = $this->GetY();
        $x_start_header = $this->GetX();
        $w = [8, 15, 12, 8, 8, 8, 12, 8, 12, 14, 14, 8, 38, 15, 43, 30, 12, 12];
        $header_titles = ['MOD.(1)',"Nº DE EXP.","FECHA\nINGRESO",'PRES.(2)','TIPO(3)','USOS(4)',"FECHA DE\nREV.","Nº DE\nREV.","DICTAMEN\n(5)","ÁREA\nTERRENO","ÁREA\nCONSTR.",'ALT.PISOS','PROPIETARIO / ADMINISTRADO','Nº CAP',"PROFESIONAL\nRESPONSABLE","Nº DE\nCREDIPAGO","MONTO\n(S/.)","FECHA DE\nPAGO"];
        $this->SetFont('Arial', 'B', 6.5);
        for ($i = 0; $i < count($w); $i++) { $this->Rect($x_start_header + array_sum(array_slice($w, 0, $i)), $y_start_header, $w[$i], $header_height); }
        $current_x = $x_start_header;
        foreach ($w as $i => $width) {
            $is_rotated = in_array($i, [0, 3, 4, 5, 11]);
            $this->SetXY($current_x, $y_start_header + 2);
            if ($is_rotated) { $this->RotatedText($current_x + ($width / 2) - 0.5, $y_start_header + 14, fix_text($header_titles[$i]), 90); } 
            else { $this->MultiCell($width, 3.5, fix_text($header_titles[$i]), 0, 'C'); }
            $current_x += $width;
        }
        $this->SetY($y_start_header + $header_height);
        $this->SetFont('Arial', '', 7);
        $total_monto = 0;
        foreach($data as $row) {
            if ($this->GetY() > 185) { $this->AddPage('L', 'A4'); }
            $this->Cell($w[0], 5, fix_text(substr($row['modalidad'], -1)), 1, 0, 'C');
            $this->Cell($w[1], 5, fix_text($row['numero_expediente']), 1, 0, 'L');
            $this->Cell($w[2], 5, $row['fecha_ingreso'] ? date('d/m/y', strtotime($row['fecha_ingreso'])) : '', 1, 0, 'C');
            $this->Cell($w[3], 5, fix_text($row['presentacion']), 1, 0, 'C');
            $this->Cell($w[4], 5, fix_text($row['tipo_obra']), 1, 0, 'C');
            $this->Cell($w[5], 5, fix_text($row['usos']), 1, 0, 'C');
            $this->Cell($w[6], 5, $row['fecha_revision'] ? date('d/m/y', strtotime($row['fecha_revision'])) : '', 1, 0, 'C');
            $this->Cell($w[7], 5, fix_text($row['numero_revision']), 1, 0, 'C');
            $this->Cell($w[8], 5, fix_text($row['dictamen']), 1, 0, 'C');
            $this->Cell($w[9], 5, $row['area_terreno'] ? number_format($row['area_terreno'], 2) : '', 1, 0, 'R');
            $this->Cell($w[10], 5, $row['area_techada'] ? number_format($row['area_techada'], 2) : '', 1, 0, 'R');
            $this->Cell($w[11], 5, fix_text($row['altura_pisos']), 1, 0, 'C');
            $this->Cell($w[12], 5, fix_text($row['propietario_o_administrado']), 1, 0, 'L');
            $this->Cell($w[13], 5, fix_text($row['cap_proyectista']), 1, 0, 'C');
            $this->Cell($w[14], 5, fix_text($row['proyectista_responsable']), 1, 0, 'L');
            $this->Cell($w[15], 5, fix_text($row['credipago_numero']), 1, 0, 'L');
            $this->Cell($w[16], 5, $row['credipago_monto'] ? number_format($row['credipago_monto'], 2) : '', 1, 0, 'R');
            $this->Cell($w[17], 5, $row['credipago_fecha'] ? date('d/m/y', strtotime($row['credipago_fecha'])) : '', 1, 1, 'C');
            $total_monto += (float)($row['credipago_monto'] ?? 0);
        }
        $this->SetFont('Arial', 'B', 6);
        $this->Cell(array_sum(array_slice($w, 0, 15)), 6, 'NUMERO TOTAL DE EXPEDIENTES:', 'T', 0, 'R');
        $this->Cell($w[15], 6, count($data), 1, 0, 'C');
        $this->Cell($w[16], 6, 'TOTAL (S/.)', 1, 0, 'C');
        $this->Cell($w[17], 6, number_format($total_monto, 2), 1, 1, 'R');
        $this->Ln(20);
        $this->SetFont('Arial', '', 9);
        $y_firmas = $this->GetY();
        if ($y_firmas > 170) { $this->AddPage('L', 'A4'); $y_firmas = 40; }
        $ancho_firma = 70; $espacio_entre_firmas = 18;
        $x1 = $this->GetX() + 10;
        $x2 = $x1 + $ancho_firma + $espacio_entre_firmas;
        $x3 = $x2 + $ancho_firma + $espacio_entre_firmas;
        $this->Line($x1, $y_firmas, $x1 + $ancho_firma, $y_firmas);
        $this->Line($x2, $y_firmas, $x2 + $ancho_firma, $y_firmas);
        $this->Line($x3, $y_firmas, $x3 + $ancho_firma, $y_firmas);
        $this->SetY($y_firmas + 2);
        $this->SetX($x1); $this->MultiCell($ancho_firma, 4, fix_text($this->headerData['delegado'] . "\nNº CAP: " . $this->headerData['cap_delegado']), 0, 'C');
        $this->SetXY($x2, $y_firmas + 2); $this->MultiCell($ancho_firma, 4, fix_text("........................................................\nDelegado CAP \nNº CAP: ........................."), 0, 'C');
        $this->SetXY($x3, $y_firmas + 2); $this->MultiCell($ancho_firma, 4, fix_text("........................................................\nPresidente de la Comisión"), 0, 'C');
    }
}

// --- 4. LÓGICA PRINCIPAL DE GENERACIÓN DEL REPORTE ---
if (!isset($_GET['numero_sesion']) || !isset($_GET['provincia']) || !isset($_GET['distrito'])) { 
    exit("Parámetros del grupo no válidos."); 
}
$numero_sesion = $_GET['numero_sesion'];
$provincia = $_GET['provincia'];
$distrito = $_GET['distrito'];

try {
    // A. Obtener datos de UNA sesión del grupo para el encabezado
    $stmt_sesion = $conn->prepare("SELECT * FROM sesiones WHERE numero_sesion = ? AND provincia = ? AND distrito = ? LIMIT 1");
    $stmt_sesion->bind_param("sss", $numero_sesion, $provincia, $distrito);
    $stmt_sesion->execute();
    $sesion_data = $stmt_sesion->get_result()->fetch_assoc();
    if (!$sesion_data) { exit("No se encontró ninguna sesión para este grupo."); }

    // =======================================================
    //          CONSULTA SQL DEFINITIVA Y EXPLÍCITA
    // =======================================================
    // PASO A: Obtener todos los expedientes del grupo con columnas explícitas.
    $sql_expedientes = "
        SELECT 
            'edif' as tipo, e.id as expediente_id,
            e.modalidad, e.numero_expediente, e.fecha_ingreso, e.presentacion, e.tipo_obra, e.usos,
            e.fecha_revision, e.numero_revision, e.dictamen, e.area_terreno, e.area_techada,
            e.altura_pisos, e.administrado as propietario_o_administrado, e.cap_proyectista,
            e.proyectista_responsable, e.recurso_reconsideracion, e.recurso_apelacion, e.archivo_revision,
            NULL as presentacion_otros, NULL as ancho_vias, NULL as archivo_vias, NULL as ubicacion_predio, NULL as propietario
        FROM expedientes_edificaciones e
        JOIN sesiones s ON e.id_sesion = s.id
        WHERE s.numero_sesion = ? AND s.provincia = ? AND s.distrito = ?
        UNION ALL
        SELECT 
            'hab' as tipo, e.id as expediente_id,
            e.modalidad, e.numero_expediente, e.fecha_ingreso, e.presentacion, '' AS tipo_obra, e.usos,
            e.fecha_revision, e.numero_revision, e.dictamen, e.area_terreno, NULL AS area_techada,
            NULL AS altura_pisos, e.propietario as propietario_o_administrado, e.cap_proyectista,
            e.proyectista_responsable, e.recurso_reconsideracion, e.recurso_apelacion, NULL as archivo_revision,
            e.presentacion_otros, e.ancho_vias, e.archivo_vias, e.ubicacion_predio, e.propietario
        FROM expedientes_habilitaciones e
        JOIN sesiones s ON e.id_sesion = s.id
        WHERE s.numero_sesion = ? AND s.provincia = ? AND s.distrito = ?
    ";
    
    $stmt_exp = $conn->prepare($sql_expedientes);
    $stmt_exp->bind_param("ssssss", $numero_sesion, $provincia, $distrito, $numero_sesion, $provincia, $distrito);
    $stmt_exp->execute();
    $expedientes_result = $stmt_exp->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // PASO B: Obtener todos los pagos de esos expedientes
    $expediente_ids = array_map(function($exp) { return $exp['expediente_id']; }, $expedientes_result);
    $pagos_map = [];
    if (!empty($expediente_ids)) {
        $ids_placeholder = implode(',', array_fill(0, count($expediente_ids), '?'));
        $sql_pagos = "SELECT p.id_expediente, p.numero_credipago, p.monto, p.fecha_pago FROM pagos p WHERE p.id_expediente IN ($ids_placeholder) ORDER BY p.id ASC";
        $stmt_pagos = $conn->prepare($sql_pagos);
        $types = str_repeat('i', count($expediente_ids));
        $stmt_pagos->bind_param($types, ...$expediente_ids);
        $stmt_pagos->execute();
        $pagos_result = $stmt_pagos->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($pagos_result as $pago) {
            if (!isset($pagos_map[$pago['id_expediente']])) {
                $pagos_map[$pago['id_expediente']] = $pago;
            }
        }
    }

    // PASO C: Unir los datos en PHP.
    $expedientes_data = [];
    foreach ($expedientes_result as $expediente) {
        $pago = $pagos_map[$expediente['expediente_id']] ?? null;
        $expediente['credipago_numero'] = $pago['numero_credipago'] ?? '';
        $expediente['credipago_monto'] = $pago['monto'] ?? 0;
        $expediente['credipago_fecha'] = $pago['fecha_pago'] ?? null;
        $expedientes_data[] = $expediente;
    }
    
    $header_data = ['tipo_comision' => $sesion_data['tipo_comision'] == 'edificaciones' ? 'LICENCIA DE EDIFICACION' : 'HABILITACIONES URBANAS', 'delegado' => $sesion_data['delegado'], 'cap_delegado' => $sesion_data['cap_delegado'], 'fecha_sesion' => $sesion_data['fecha_sesion'], 'numero_sesion' => $sesion_data['numero_sesion'], 'provincia' => $sesion_data['provincia'], 'distrito' => $sesion_data['distrito']];

    // --- 5. GENERACIÓN Y SALIDA DEL PDF ---
    $pdf = new PDF_Reporte_Oficial('L', 'mm', 'A4', $header_data);
    $pdf->AliasNbPages();
    $pdf->AddPage('L', 'A4');
    $pdf->FancyTable($expedientes_data);
    
    if (ob_get_length()) ob_end_clean(); 
    $pdf->Output('I', 'Reporte_Grupo.pdf');
    exit;

} catch (Exception $e) {
    exit("Error al generar el PDF: " . $e->getMessage());
}
?>