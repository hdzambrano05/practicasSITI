<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

require_once 'Classes/PHPExcel/IOFactory.php';
require_once 'Classes/PHPExcel/Cell.php';
require_once 'Classes/PHPExcel/Shared/Date.php';

require_once('../../sased/clases/bd/MySQLConex.php');
require_once('../../sased/clases/base/preinscrito.php');
require_once('../../clases/base/alumno.php');

$con = new MySQLConex();
$con->abrir("../../Connections/datos_conex.php");

/* ================= FUNCIONES BASE ================= */

function errorExcel($msg)
{
    echo "<div style='padding:12px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;font-weight:600;margin-bottom:10px;'>$msg</div>";
    exit;
}

function normalizarEncabezadoExcel($texto)
{
    $texto = trim((string)$texto);
    $texto = mb_strtoupper($texto, 'UTF-8');

    $reemplazos = [
        'Á' => 'A',
        'À' => 'A',
        'Ä' => 'A',
        'Â' => 'A',
        'É' => 'E',
        'È' => 'E',
        'Ë' => 'E',
        'Ê' => 'E',
        'Í' => 'I',
        'Ì' => 'I',
        'Ï' => 'I',
        'Î' => 'I',
        'Ó' => 'O',
        'Ò' => 'O',
        'Ö' => 'O',
        'Ô' => 'O',
        'Ú' => 'U',
        'Ù' => 'U',
        'Ü' => 'U',
        'Û' => 'U',
        'Ñ' => 'N'
    ];

    $texto = strtr($texto, $reemplazos);
    $texto = str_replace(['"', "'"], '', $texto);
    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim($texto);
}

function obtenerValorCeldaFormateado($hoja, $columna, $fila, $nombreColumna = '')
{
    $cell = $hoja->getCellByColumnAndRow($columna, $fila);
    $valor = $cell->getValue();

    if ($valor === null) return '';

    if ($nombreColumna === 'Fecha de nacimiento') {
        if ($valor !== '' && is_numeric($valor)) {
            return date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($valor));
        }

        return trim((string)$valor);
    }

    if (PHPExcel_Shared_Date::isDateTime($cell) && $valor !== '' && is_numeric($valor)) {
        return date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($valor));
    }

    return trim((string)$valor);
}

function filaTieneColor($hoja, $fila, $highestCol)
{
    for ($c = 0; $c < $highestCol; $c++) {
        $cell = $hoja->getCellByColumnAndRow($c, $fila);
        $style = $cell->getStyle();

        $fillType = $style->getFill()->getFillType();
        $rgb = strtoupper((string)$style->getFill()->getStartColor()->getRGB());
        $argb = strtoupper((string)$style->getFill()->getStartColor()->getARGB());

        if (
            $fillType !== PHPExcel_Style_Fill::FILL_NONE &&
            $fillType !== '' &&
            $rgb !== '' &&
            $rgb !== 'FFFFFF' &&
            $rgb !== '000000' &&
            $argb !== 'FFFFFFFF' &&
            $argb !== '00000000'
        ) {
            return true;
        }
    }

    return false;
}

function esColumnaAnioExcel($hoja, $columna, $highestRow, $filaInicioDatos, $tieneEncabezado)
{
    if ($tieneEncabezado) {
        $encabezado = trim((string)$hoja->getCellByColumnAndRow($columna, 1)->getValue());
        $encabezado = normalizarEncabezadoExcel($encabezado);

        $encabezadosAnio = [
            'ANO ACADEMICO',
            'AÑO ACADEMICO',
            'ID_ANO',
            'ID ANO',
            'ANO',
            'AÑO'
        ];

        if (in_array($encabezado, $encabezadosAnio)) {
            return true;
        }
    }

    $cantidadAnios = 0;
    $cantidadDatos = 0;

    for ($row = $filaInicioDatos; $row <= min($highestRow, $filaInicioDatos + 5); $row++) {
        $valor = trim((string)$hoja->getCellByColumnAndRow($columna, $row)->getValue());

        if ($valor !== '') {
            $cantidadDatos++;

            if (preg_match('/^(19|20)[0-9]{2}$/', $valor)) {
                $cantidadAnios++;
            }
        }
    }

    return ($cantidadDatos > 0 && $cantidadDatos === $cantidadAnios);
}

/* ================= VALIDACIONES ================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') errorExcel('Petición inválida');
if (empty($_FILES['archivo_excel'])) errorExcel('No se recibió el archivo');
if ($_FILES['archivo_excel']['error'] != 0) errorExcel('Error al subir el archivo');

if (!isset($_POST['id_ano']) || intval($_POST['id_ano']) <= 0) {
    errorExcel('No se recibió el año lectivo');
}

$id_ano_form = intval($_POST['id_ano']);

$ext = strtolower(pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION));

if ($ext !== 'xls') {
    errorExcel('Solo se permiten archivos .xls');
}

$archivoTmp = $_FILES['archivo_excel']['tmp_name'];

/* ================= AÑO LECTIVO ================= */

$sqlAno = "SELECT ano FROM anolectivo WHERE id_ano = $id_ano_form LIMIT 1";
$resAno = $con->query($sqlAno);

if (!$resAno || $resAno->num_rows == 0) {
    errorExcel('El año lectivo seleccionado no existe');
}

$rowAno  = $resAno->fetch_assoc();
$ano_txt = $rowAno['ano'];

/* ================= CARGAR EXCEL ================= */

$reader = PHPExcel_IOFactory::createReader('Excel5');
$excel  = $reader->load($archivoTmp);
$hoja   = $excel->getActiveSheet();

$highestRow = $hoja->getHighestRow();
$highestCol = PHPExcel_Cell::columnIndexFromString($hoja->getHighestColumn());

/* ================= MAPEO ================= */

$mapaBD = [
    'Número de documento' => 'doc_pre',
    'Tipo de documento' => 'tipo_pre',
    'Fecha de nacimiento' => 'fec_nac',
    'Ciudad de nacimiento' => 'ciu_pre',
    'Año académico' => 'id_ano',
    'Sede' => 'id_sed',
    'Grado' => 'cod_gra',
    'Jornada' => 'id_jor',
    'Dirección' => 'dir_cor',
    'Barrio' => 'barrio',
    'Teléfono' => 'tel_con',
    'Celular' => 'tel2_pre',
    'E-mail' => 'mai_pre',
    'Nombres' => 'nom_pre',
    'Apellidos' => 'ape_pre',
    'Sexo' => 'sex_pre',
    'RH' => 'gs_pre',
    'EPS' => 'eps_pre',
    'SISBEN' => 'sisben',
    'Colegio anterior' => 'col_pro',

    'DocumentoP' => 'doc_padre',
    'TipoP' => 'tipo_padre',
    'NombreP' => 'nom_padre',
    'ApellidoP' => 'ape_padre',
    'DirecciónP' => 'dir_padre',
    'TeléfonoP' => 'tel_padre',
    'ProfesiónP' => 'id_pro_p',

    'DocumentoM' => 'doc_madre',
    'TipoM' => 'tipo_madre',
    'NombreM' => 'nom_madre',
    'ApellidoM' => 'ape_madre',
    'DirecciónM' => 'dir_madre',
    'TeléfonoM' => 'tel_madre',
    'ProfesiónM' => 'id_pro_m',

    'DocumentoA' => 'doc_acu',
    'TipoA' => 'tipo_acu',
    'NombreA' => 'nom_acu',
    'ApellidoA' => 'ape_acu',
    'DirecciónA' => 'dir_acu',
    'TeléfonoA' => 'tel_acu',
    'ProfesiónA' => 'id_pro_a'
];

$gruposColumnas = [
    'Información estudiante' => [
        'Número de documento',
        'Tipo de documento',
        'Fecha de nacimiento',
        'Ciudad de nacimiento',
        'Año académico',
        'Sede',
        'Grado',
        'Jornada',
        'Dirección',
        'Barrio',
        'Teléfono',
        'Celular',
        'E-mail',
        'Nombres',
        'Apellidos',
        'Sexo',
        'RH',
        'EPS',
        'SISBEN',
        'Colegio anterior'
    ],
    'Información papá' => [
        'DocumentoP',
        'TipoP',
        'NombreP',
        'ApellidoP',
        'DirecciónP',
        'TeléfonoP',
        'ProfesiónP'
    ],
    'Información mamá' => [
        'DocumentoM',
        'TipoM',
        'NombreM',
        'ApellidoM',
        'DirecciónM',
        'TeléfonoM',
        'ProfesiónM'
    ],
    'Información acudiente' => [
        'DocumentoA',
        'TipoA',
        'NombreA',
        'ApellidoA',
        'DirecciónA',
        'TeléfonoA',
        'ProfesiónA'
    ]
];

/* ================= DETECTAR ENCABEZADOS ================= */

$coincidenciasEncabezado = 0;

for ($c = 0; $c < $highestCol; $c++) {
    $valorPrimeraFila = trim((string)$hoja->getCellByColumnAndRow($c, 1)->getValue());
    $valorNormalizado = normalizarEncabezadoExcel($valorPrimeraFila);

    foreach ($mapaBD as $nombreVisible => $campoBD) {
        if (
            normalizarEncabezadoExcel($nombreVisible) === $valorNormalizado ||
            normalizarEncabezadoExcel($campoBD) === $valorNormalizado
        ) {
            $coincidenciasEncabezado++;
            break;
        }
    }
}

$tieneColorEncabezado = filaTieneColor($hoja, 1, $highestCol);
$tieneEncabezado = ($coincidenciasEncabezado >= 2 || $tieneColorEncabezado);
$filaInicioDatos = $tieneEncabezado ? 2 : 1;

/* ================= ESTILOS COMPACTOS ================= */

echo "
<style>
.excel-importador{
    background:#fff;
    border:1px solid #cbd5e1;
    border-radius:8px;
    overflow:hidden;
    font-family:Arial, sans-serif;
    font-size:10px;
}

.excel-top{
    padding:8px 10px;
    background:#1e88c8;
    color:white;
}

.excel-top h3{
    margin:0;
    font-size:12px;
    font-weight:700;
}

.excel-top p{
    margin:2px 0 0;
    font-size:10px;
}

.excel-info{
    display:flex;
    gap:5px;
    flex-wrap:wrap;
    padding:6px 8px;
    background:#f8fafc;
    border-bottom:1px solid #dbe3ec;
}

.excel-pill{
    padding:3px 7px;
    border-radius:10px;
    background:#e0ecff;
    color:#1e3a8a;
    font-size:10px;
    font-weight:700;
}

.excel-alerta{
    margin:6px 8px;
    padding:6px 8px;
    border-radius:6px;
    background:#fff7ed;
    color:#9a3412;
    border:1px solid #fed7aa;
    font-size:10px;
}

#tabla_excel_wrapper{
    max-height:45vh;
    overflow:auto;
    border-top:1px solid #e5e7eb;
    border-bottom:1px solid #e5e7eb;
}

#tabla_excel_preview{
    width:max-content;
    border-collapse:collapse;
    font-size:10px;
}

#tabla_excel_preview th,
#tabla_excel_preview td{
    min-width:110px;
    max-width:150px;
    padding:4px 5px;
    border:1px solid #e5e7eb;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

#tabla_excel_preview thead th{
    position:sticky;
    top:0;
    background:#f1f5f9;
    color:#111827;
    z-index:5;
    vertical-align:top;
}

#tabla_excel_preview tbody tr:nth-child(even){
    background:#f9fafb;
}

#tabla_excel_preview tbody tr:hover{
    background:#eff6ff;
}

.col-label{
    font-size:9px;
    color:#1e3a8a;
    font-weight:700;
    margin-bottom:2px;
}

.col-head{
    font-size:9px;
    color:#64748b;
    margin-bottom:3px;
    height:11px;
    overflow:hidden;
    text-overflow:ellipsis;
}

.selector-mapeo{
    width:100%;
    height:24px;
    border:1px solid #9ec5fe;
    border-radius:5px;
    background:#fff;
    color:#111827;
    font-size:10px;
    padding:2px 4px;
    outline:none;
    cursor:pointer;
}

.selector-mapeo:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 2px rgba(37,99,235,.15);
}

.selector-mapeo option{
    color:#111827;
    background:white;
}

.selector-mapeo option:disabled{
    color:#9ca3af;
    background:#f3f4f6;
}

.celda-anio{
    background:#ecfdf5 !important;
    color:#065f46;
    font-weight:700;
}

#barra_guardar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:8px;
    padding:8px 10px;
    background:#fff;
}

#resumen_mapeo{
    font-size:10px;
    color:#475569;
}

#btn_guardar_excel,
#btn_limpiar_mapeo{
    padding:5px 12px;
    border-radius:7px;
    font-size:10px;
    font-weight:700;
    cursor:pointer;
}

#btn_guardar_excel{
    background:#2563eb;
    color:white;
    border:none;
}

#btn_guardar_excel:hover{
    background:#1d4ed8;
}

#btn_limpiar_mapeo{
    background:#f1f5f9;
    color:#334155;
    border:1px solid #cbd5e1;
}

#btn_limpiar_mapeo:hover{
    background:#e2e8f0;
}
</style>
";

/* ================= VISTA ================= */

echo "
<div class='excel-importador'>
    <div class='excel-top'>
        <h3>Vista previa y mapeo de columnas</h3>
        <p>Seleccione qué campo corresponde a cada columna antes de guardar los registros.</p>
    </div>

    <div class='excel-info'>
        <div class='excel-pill'>Año lectivo: " . htmlspecialchars($ano_txt) . "</div>
        <div class='excel-pill'>Columnas detectadas: " . intval($highestCol) . "</div>
        <div class='excel-pill'>Filas del archivo: " . intval($highestRow) . "</div>
    </div>
";

if (!$tieneEncabezado) {
    echo "
    <div class='excel-alerta'>
        No se detectaron encabezados válidos. La primera fila se tomará como dato y debe mapear las columnas manualmente.
    </div>";
}

echo "<div id='tabla_excel_wrapper'><table id='tabla_excel_preview'><thead><tr>";

/* ================= CABECERA CON SELECTORES ================= */

for ($c = 0; $c < $highestCol; $c++) {

    $letra = PHPExcel_Cell::stringFromColumnIndex($c);

    $valorEncabezado = $tieneEncabezado
        ? trim((string)$hoja->getCellByColumnAndRow($c, 1)->getValue())
        : '';

    $valorEncabezadoNormalizado = normalizarEncabezadoExcel($valorEncabezado);

    echo "
    <th>
        <div class='col-label'>Columna $letra</div>
        <div class='col-head'>" . htmlspecialchars($tieneEncabezado ? $valorEncabezado : 'Sin encabezado') . "</div>

        <select class='selector-mapeo' data-columna='$c'>
            <option value=''>No usar</option>";

    foreach ($gruposColumnas as $grupo => $campos) {
        echo "<optgroup label='" . htmlspecialchars($grupo) . "'>";

        foreach ($campos as $nombreVisible) {
            if (!isset($mapaBD[$nombreVisible])) continue;

            $campoBD = $mapaBD[$nombreVisible];
            $selected = '';

            if (
                $tieneEncabezado &&
                (
                    normalizarEncabezadoExcel($nombreVisible) === $valorEncabezadoNormalizado ||
                    normalizarEncabezadoExcel($campoBD) === $valorEncabezadoNormalizado
                )
            ) {
                $selected = 'selected';
            }

            echo "<option value='" . htmlspecialchars($campoBD) . "' $selected>" .
                htmlspecialchars($nombreVisible) .
                "</option>";
        }

        echo "</optgroup>";
    }

    echo "
        </select>
    </th>";
}

echo "</tr></thead><tbody>";

/* ================= DATOS ================= */

for ($row = $filaInicioDatos; $row <= $highestRow; $row++) {

    $tieneDatos = false;

    for ($c = 0; $c < $highestCol; $c++) {
        $v = obtenerValorCeldaFormateado($hoja, $c, $row);

        if ($v !== '') {
            $tieneDatos = true;
            break;
        }
    }

    if (!$tieneDatos) continue;

    echo "<tr>";

    for ($c = 0; $c < $highestCol; $c++) {

        $esAnio = esColumnaAnioExcel($hoja, $c, $highestRow, $filaInicioDatos, $tieneEncabezado);

        $v = $esAnio
            ? $ano_txt
            : obtenerValorCeldaFormateado($hoja, $c, $row);

        $clase = $esAnio ? " class='celda-anio'" : "";

        echo "<td data-columna='$c'$clase>" . htmlspecialchars($v) . "</td>";
    }

    echo "</tr>";
}

echo "</tbody></table></div>";

echo "
    <div id='barra_guardar'>
        <div id='resumen_mapeo'>Columnas seleccionadas: <b id='total_mapeadas'>0</b></div>

        <div>
            <button id='btn_limpiar_mapeo' type='button'>Limpiar mapeo</button>
            <button id='btn_guardar_excel' type='button'>Guardar Excel</button>
        </div>
    </div>
</div>
";

/* ================= SCRIPT ================= */

echo "
<script>
var ANO_TXT = " . json_encode($ano_txt) . ";
var ID_ANO_FORM = " . intval($id_ano_form) . ";

function actualizarResumenMapeo(){
    var total = 0;

    $('.selector-mapeo').each(function(){
        if($(this).val() !== ''){
            total++;
        }
    });

    $('#total_mapeadas').text(total);
}

function actualizarOpcionesMapeo(){

    var usados = [];

    $('.selector-mapeo').each(function(){
        var valor = $(this).val();

        if(valor !== ''){
            usados.push(valor);
        }
    });

    $('.selector-mapeo').each(function(){

        var selectActual = $(this);
        var valorActual = selectActual.val();

        selectActual.find('option').each(function(){

            var opcion = $(this);
            var valorOpcion = opcion.val();

            if(valorOpcion === ''){
                opcion.prop('disabled', false);
                return;
            }

            var usadoEnOtraColumna = usados.indexOf(valorOpcion) !== -1 && valorOpcion !== valorActual;
            opcion.prop('disabled', usadoEnOtraColumna);
        });
    });

    actualizarResumenMapeo();
}

function actualizarVisualizacionAnio(){

    $('#tabla_excel_preview thead th').each(function(i){

        var campoBD = $(this).find('select').val();
        var textoHeader = $(this).find('.col-head').text().trim().toLowerCase();

        var esAnio = (
            campoBD === 'id_ano' ||
            textoHeader === 'id_ano' ||
            textoHeader === 'año académico' ||
            textoHeader === 'ano academico' ||
            textoHeader === 'año' ||
            textoHeader === 'ano'
        );

        if(!esAnio){
            var totalDatos = 0;
            var totalAnios = 0;

            $('#tabla_excel_preview tbody tr').each(function(){
                var valor = $(this).find('td').eq(i).text().trim();

                if(valor !== ''){
                    totalDatos++;

                    if(/^(19|20)[0-9]{2}$/.test(valor)){
                        totalAnios++;
                    }
                }
            });

            esAnio = totalDatos > 0 && totalDatos === totalAnios;
        }

        $('#tabla_excel_preview tbody tr').each(function(){
            var celda = $(this).find('td').eq(i);

            if(esAnio){
                celda.text(ANO_TXT);
                celda.addClass('celda-anio');
            }else{
                celda.removeClass('celda-anio');
            }
        });
    });
}

function obtenerDatosTabla(){

    var datos = [];

    $('#tabla_excel_preview tbody tr').each(function(){

        var fila = {};

        $(this).find('td').each(function(i){

            var campoBD = $('#tabla_excel_preview thead th').eq(i).find('select').val();

            if(!campoBD) return;

            if(campoBD === 'id_ano'){
                fila.id_ano = ID_ANO_FORM;
            }else{
                fila[campoBD] = $(this).text().trim();
            }
        });

        fila.id_ano = ID_ANO_FORM;

        if(Object.keys(fila).length > 1){
            datos.push(fila);
        }
    });

    return datos;
}

$(function(){

    actualizarOpcionesMapeo();
    actualizarVisualizacionAnio();

    $(document).off('change.excelNuevo', '.selector-mapeo')
        .on('change.excelNuevo', '.selector-mapeo', function(){
            actualizarOpcionesMapeo();
            actualizarVisualizacionAnio();
        });

    $(document).off('click.excelNuevo', '#btn_limpiar_mapeo')
        .on('click.excelNuevo', '#btn_limpiar_mapeo', function(){
            $('.selector-mapeo').val('');
            actualizarOpcionesMapeo();
            actualizarVisualizacionAnio();
        });

    $(document).off('click.excelNuevo', '#btn_guardar_excel')
        .on('click.excelNuevo', '#btn_guardar_excel', function(){

            var datos = obtenerDatosTabla();

            if(datos.length === 0){
                alert('Debe seleccionar al menos una columna para guardar.');
                return;
            }

            if(!confirm('¿Desea guardar los registros del Excel?')) return;

            console.log('Datos enviados:', datos);

            xajax_guardarExcelPreinscritosNuevo(datos);
        });
});
</script>
";
