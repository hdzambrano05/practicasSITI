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


/* ================= ERROR VISUAL ================= */
function errorExcel($msg)
{
    echo "
    <div style='padding:12px;background:#fee2e2;color:#991b1b;
    border:1px solid #fecaca;border-radius:6px;font-weight:600;margin-bottom:10px;'>
        $msg
    </div>";
    exit;
}

/* ================= NORMALIZAR ENCABEZADOS ================= */
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

    // quitar comillas dobles o simples sobrantes
    $texto = str_replace(['"', "'"], '', $texto);

    // normalizar espacios
    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim($texto);
}

function construirMapaEncabezadosNormalizados($mapaBD)
{
    $salida = [];

    foreach ($mapaBD as $encabezado => $campoBD) {
        $salida[normalizarEncabezadoExcel($encabezado)] = $encabezado;
    }

    return $salida;
}

/* ================= FORMATEAR VALOR CELDA ================= */
function obtenerValorCeldaFormateado($hoja, $columna, $fila, $nombreColumna = '')
{
    $cell = $hoja->getCellByColumnAndRow($columna, $fila);
    $valor = $cell->getValue();

    if ($valor === null) {
        return '';
    }

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

/* ================= VALIDACIONES ================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') errorExcel('Petición inválida');
if (empty($_FILES['archivo_excel'])) errorExcel('No se recibió el archivo');
if ($_FILES['archivo_excel']['error'] != 0) errorExcel('Error al subir el archivo');

if (!isset($_POST['id_ano']) || intval($_POST['id_ano']) <= 0) {
    errorExcel('No se recibió el año lectivo');
}

$id_ano_form = intval($_POST['id_ano']);

$ext = strtolower(pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION));
if ($ext !== 'xls') errorExcel('Solo se permiten archivos .xls');

$archivoTmp = $_FILES['archivo_excel']['tmp_name'];

/* ================= OBTENER AÑO LECTIVO ================= */
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

$columnasExcel = array_keys($mapaBD);
$mapaEncabezadosNormalizados = construirMapaEncabezadosNormalizados($mapaBD);
$columnasExcelNormalizadas = array_keys($mapaEncabezadosNormalizados);

/* ================= BUSCAR ENCABEZADOS ================= */
$filaEncabezado = null;
$mapa = [];

for ($row = 1; $row <= $highestRow; $row++) {
    $tmp = [];

    for ($c = 0; $c < $highestCol; $c++) {
        $valorOriginal = trim((string)$hoja->getCellByColumnAndRow($c, $row)->getValue());

        if ($valorOriginal === '') {
            continue;
        }

        $valorNormalizado = normalizarEncabezadoExcel($valorOriginal);

        if (isset($mapaEncabezadosNormalizados[$valorNormalizado])) {
            $encabezadoCanonico = $mapaEncabezadosNormalizados[$valorNormalizado];
            $tmp[$encabezadoCanonico] = $c;
        }
    }

    if (count(array_intersect(array_keys($tmp), $columnasExcel)) >= ceil(count($columnasExcel) * 0.6)) {
        $filaEncabezado = $row;
        $mapa = $tmp;
        break;
    }
}

if (!$filaEncabezado) errorExcel('No se encontraron encabezados válidos');

/* ================= CSS ================= */
echo "
<style>
#tabla_excel_wrapper{
    max-height:55vh;
    overflow:auto;
    border:1px solid #e5e7eb;
    border-radius:8px;
}
#tabla_excel_preview{
    width:max-content;
    border-collapse:collapse;
    font-size:12px;
}
#tabla_excel_preview th,
#tabla_excel_preview td{
    min-width:120px;
    padding:6px;
    border:1px solid #e5e7eb;
    white-space:nowrap;
}
#tabla_excel_preview thead th{
    position:sticky;
    top:0;
    background:#1e3a8a;
    color:white;
    z-index:5;
}
#barra_guardar{
    position:sticky;
    bottom:0;
    background:#fff;
    padding:12px;
    border-top:1px solid #e5e7eb;
    text-align:right;
    z-index:10;
}
</style>
";

/* ================= TITULO AÑO ================= */
echo "
<div style='margin-bottom:10px;font-weight:600;color:#1e3a8a'>
    Año lectivo seleccionado: <b>" . htmlspecialchars($ano_txt) . "</b>
</div>
";

/* ================= TABLA ================= */
echo "<div id='tabla_excel_wrapper'><table id='tabla_excel_preview'><thead><tr>";
foreach ($columnasExcel as $c) {
    echo "<th>" . htmlspecialchars($c) . "</th>";
}
echo "</tr></thead><tbody>";

for ($row = $filaEncabezado + 1; $row <= $highestRow; $row++) {

    $tieneDatos = false;

    foreach ($columnasExcel as $c) {
        if ($c === 'Año académico') continue;

        $v = '';

        if (isset($mapa[$c])) {
            $v = obtenerValorCeldaFormateado($hoja, $mapa[$c], $row, $c);
        }

        if ($v !== '') {
            $tieneDatos = true;
            break;
        }
    }

    if (!$tieneDatos) continue;

    echo "<tr>";

    foreach ($columnasExcel as $c) {

        if ($c === 'Año académico') {
            $v = $ano_txt;
        } else {
            $v = isset($mapa[$c])
                ? obtenerValorCeldaFormateado($hoja, $mapa[$c], $row, $c)
                : '';
        }

        echo "<td>" . htmlspecialchars($v) . "</td>";
    }

    echo "</tr>";
}

echo "</tbody></table></div>";

/* ================= BOTÓN ================= */
echo "
<div id='barra_guardar'>
    <button id='btn_guardar_excel'
        style='padding:10px 22px;background:#2563eb;color:white;
        border:none;border-radius:8px;font-weight:600;cursor:pointer'>
        Guardar Excel
    </button>
</div>
";

/* ================= SCRIPT ================= */
echo "
<script>
var mapaBD = " . json_encode($mapaBD) . ";

$(document).off('click','#btn_guardar_excel').on('click','#btn_guardar_excel',function(){

    if(!confirm('¿Desea guardar los registros del Excel?')) return;

    var datos = [];

    $('#tabla_excel_preview tbody tr').each(function(){
        var fila = {};

        $(this).find('td').each(function(i){
            var col = $('#tabla_excel_preview thead th').eq(i).text().trim();
            fila[ mapaBD[col] || col ] = $(this).text().trim();
        });

        datos.push(fila);
    });

    console.log('Datos enviados:', datos);
    xajax_guardarExcelPreinscritosNuevo(datos);
});
</script>
";
