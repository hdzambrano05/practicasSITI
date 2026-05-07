<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();

require_once("../../../libraries/adodb/adodb.inc.php");
require_once("../../../class/base/funcionario.php");
require_once("../../../class/base/cargo.php");
require_once("../../../class/base/estructuraorganizacional.php");
require_once("../../../class/base/empresa.php");
require_once("../../../class/base/sedeempresa.php");
require_once("../../../class/base/documentosalidaint.php");
require_once("../../../class/base/documentosalidaext.php");
require_once("../../../class/base/destinatario.php");
require_once("../../../class/base/remitente.php");
require_once("../../../class/base/datosdest.php");
require_once("../../../class/base/ciudad.php");
require_once("../../../class/base/departamento.php");
require_once("../../../class/base/documentacionanio.php");
require_once("../../../class/base/cargod.php");
require_once("../../../class/base/asignastiker.php");
require_once("../../../class/base/historialdocumentosalidaint.php");
require_once("../../../class/base/historialdocumentosalidaext.php");
require_once("../../../class/base/areadoc.php");
require_once("../../../class/base/serie.php");
require_once("../../../class/base/sede.php");
require_once("../../../class/base/imagen.php");
require_once("../../../class/base/varsige.php");
require_once("../../../class/base/controlformatos.php");
require_once("../../../class/base/registrorespuesta.php");
require_once("../../../class/base/usuario.php");
require_once("../../../class/base/estadodocumento.php");

include "../../common/files/file_functions.php";
include("../../../scripts/adoConex/adoConex.php");

/* PHPWord */
$autoload = __DIR__ . '/docx/libreria/PHPWord-master/src/PhpWord/Autoloader.php';
if (!file_exists($autoload)) {
    die("No se encontró el Autoloader de PHPWord");
}
require_once $autoload;
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Element\TextRun;


$vs_rtf         = varsige::getByDescripcion($con, 'rtf_correspondencia_externa');
$dir_empr       = varsige::getByDescripcion($con, 'direccion_empresa');
$tel_empr       = varsige::getByDescripcion($con, 'telefonos_empresa');
$url_empr       = varsige::getByDescripcion($con, 'url_empresa');
$F_D_M_I        = varsige::getByDescripcion($con, 'FORMATO_DESTINATARIO_MASIVO_INTERNO');
$URL_DESCARGA_ADJ = varsige::getByDescripcion($con, 'URL_DESCARGA_ADJ');

if (!$F_D_M_I) {
    $F_D_M_I = new varsige(
        'FORMATO_DESTINATARIO_MASIVO_INTERNO',
        '{"mostrar_cargo":"si","mostrar_titulo":"no","separador":","}'
    );
}
$O_F_D_M_I = json_decode($F_D_M_I->getVal_var());

$dir_emp = $dir_empr ? $dir_empr->getVal_var() : '';
$tel_emp = $tel_empr ? $tel_empr->getVal_var() : '';
$url_emp = $url_empr ? $url_empr->getVal_var() : '';

$var_en = current(varsige::getWhere($con, 16));
$dir_envi = $var_en ? $var_en->getVal_var() : '/tmp/';

$up_img = current(varsige::getWhere($con, 108));
$img_up = $up_img ? $up_img->getVal_var() : '';

$rutaFirmaFormulario = obtenerRutaFirmaFormulario();

$dim = 30;
$_POST['t_e'] = $_POST['t_e'] ?? 'masivo';


$lista_des = $_REQUEST['list_des_html'] ?? '';

$t_ds      = $_POST['tip_doc'] ?? '';
$envigen   = $_POST['docgenenvi'] ?? '';
$texto     = $_POST['texto_doc'] ?? '';
$id_ds_post = $_POST['id_ds'] ?? '';
$titulo_per = $_POST['titulo_per'] ?? '';
$id_ser = $_POST['id_ser'] ?? '';



/* Plantilla DOCX */
$plantillaDocx = '';

$obj = controlformatos::getWhere($con, '%', $id_ser);
if (count($obj) == 0) {
    $obj = controlformatos::getWhere($con, '%', '%', 'PLANTILLA_GENERAL');
}

if (count($obj) > 0) {
    $otroFrm = $obj[0]->getOtro_frm();
    $jsonPlantilla = json_decode($otroFrm, true);

    if (is_array($jsonPlantilla) && !empty($jsonPlantilla['codigo'])) {
        $rutaRelativa = trim($jsonPlantilla['codigo']);

        if (file_exists($rutaRelativa)) {
            $plantillaDocx = $rutaRelativa;
        } else {
            $rutaDesdeBd = realpath(__DIR__ . '/' . ltrim($rutaRelativa, '/'));
            if ($rutaDesdeBd && file_exists($rutaDesdeBd)) {
                $plantillaDocx = $rutaDesdeBd;
            }
        }
    }
}

$matriz = [];

if (isset($_POST['rad_cargar']) && $_POST['rad_cargar'] === 'n') {
    $matriz = $_SESSION['objeto'] ?? [];
} else {
    $lis_docs = explode(',', $_POST['list_doc'] ?? '');
    $lis_docs = array_values(array_filter(array_unique($lis_docs)));
    foreach ($lis_docs as $docId) {
        $matriz[] = ['id_ds' => $docId];
    }
}

$cc_para_tmp = [];
$arr_keys = array_keys($_REQUEST);
$arr_ced = [];

$esCircular = ((int)$id_ser === 58);
$esComunicacion = ((int)$id_ser === 66);

foreach ($arr_keys as $key) {
    if (substr($key, 0, 7) === 'tit_des') {
        $ced = substr($key, 7);
        $arr_ced[] = $ced;

        if (!empty($_REQUEST['para_copia' . $ced])) {
            $cc_para_tmp[] = $_REQUEST['para_copia' . $ced];
        }
    }
}

if (count($matriz) <= 0) {
    limpiarArchivosTemporalesFirma($rutaFirmaFormulario);
    die("No se ha generado un radicado");
}

$contentFiles = [];
$direc = [];
$arch_vec_mas = [];
$ndes = 0;
$interno = 0;

if ($esCircular && count($matriz) > 0) {

    // Para circulares: generar UN SOLO documento,
    // usando el primer id_ds como base
    $id_ds = $matriz[0]['id_ds'];

    $documentData = construirDatosDocumento(
        $con,
        $id_ds,
        $t_ds,
        $texto,
        $lista_des,
        $cc_para_tmp,
        $arr_ced,
        0,
        $O_F_D_M_I,
        $dir_emp,
        $tel_emp,
        $url_emp,
        $img_up,
        $dim,
        true,
        $rutaFirmaFormulario
    );

    if ($documentData['success']) {
        $archivoGenerado = generarDocumentoDocx($plantillaDocx, $documentData['data'], $dir_envi);

        if ($archivoGenerado && file_exists($archivoGenerado)) {

            if ($_POST['t_e'] === 'masivo' && trim($envigen) === 'noenvio') {
                // descarga directa de un solo archivo, no ZIP
                ob_clean();
                header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
                header("Content-Disposition: attachment; filename=" . basename($archivoGenerado));
                header("Content-Length: " . filesize($archivoGenerado));
                readfile($archivoGenerado);
                @unlink($archivoGenerado);
                limpiarArchivosTemporalesFirma($rutaFirmaFormulario);
                exit;
            } else {
                if (is_uploaded_file($_FILES['file_a']['tmp_name'] ?? '')) {
                    $name_img = str_replace(' ', '_', $_FILES['file_a']['name']);
                    copy($_FILES['file_a']['tmp_name'], $dir_envi . $name_img);

                    $arch_vec = [
                        $dir_envi . $name_img => file_get_contents($dir_envi . $name_img),
                        $archivoGenerado      => file_get_contents($archivoGenerado),
                    ];

                    $url = moverArchivo($arch_vec, $dir_envi, '', 'CO');
                    $direc[$id_ds] = $url;
                } else {
                    $url = moverArchivo($archivoGenerado, $dir_envi, '', 'CO');
                    $direc[$id_ds] = $url;
                }

                @unlink($archivoGenerado);
            }
        }
    }
} else {

    // Comunicaciones: comportamiento normal, uno por destinatario
    foreach ($matriz as $fila) {
        $id_ds = $fila['id_ds'];

        $documentData = construirDatosDocumento(
            $con,
            $id_ds,
            $t_ds,
            $texto,
            $lista_des,
            $cc_para_tmp,
            $arr_ced,
            $ndes,
            $O_F_D_M_I,
            $dir_emp,
            $tel_emp,
            $url_emp,
            $img_up,
            $dim,
            false,
            $rutaFirmaFormulario
        );

        $ndes++;

        if (!$documentData['success']) {
            continue;
        }

        $archivoGenerado = generarDocumentoDocx($plantillaDocx, $documentData['data'], $dir_envi);

        if (!$archivoGenerado || !file_exists($archivoGenerado)) {
            continue;
        }

        if ($_POST['t_e'] === 'masivo' && trim($envigen) === 'noenvio') {
            $arch_vec_mas[$archivoGenerado] = file_get_contents($archivoGenerado);
        } else {
            if (is_uploaded_file($_FILES['file_a']['tmp_name'] ?? '')) {
                $name_img = str_replace(' ', '_', $_FILES['file_a']['name']);
                copy($_FILES['file_a']['tmp_name'], $dir_envi . $name_img);

                $arch_vec = [
                    $dir_envi . $name_img => file_get_contents($dir_envi . $name_img),
                    $archivoGenerado      => file_get_contents($archivoGenerado),
                ];

                $url = moverArchivo($arch_vec, $dir_envi, '', 'CO');
                $direc[$id_ds] = $url;
            } else {
                $url = moverArchivo($archivoGenerado, $dir_envi, '', 'CO');
                $direc[$id_ds] = $url;
            }
        }

        @unlink($archivoGenerado);
    }
}


if (!$esCircular && count($arch_vec_mas) > 0) {
    $origen = [];

    foreach ($arch_vec_mas as $ruta => $contenido) {
        $origen[basename($ruta)] = $contenido;
        @unlink($ruta);
    }

    $fichero = rtrim($dir_envi, '/') . '/documento_generado.zip';
    $dataZip = createzip($origen, $fichero);

    if ($dataZip && file_exists($fichero)) {
        ob_clean();
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=" . basename($fichero));
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($fichero));
        readfile($fichero);
        @unlink($fichero);
        limpiarArchivosTemporalesFirma($rutaFirmaFormulario);
        exit;
    }
}

limpiarArchivosTemporalesFirma($rutaFirmaFormulario);

header(
    'Location: ../../../build/documentacion/envio_docs/envio_directo.php?' .
        'id_ds=' . urlencode($id_ds_post ?? '') .
        '&t_ds=' . urlencode($t_ds ?? '') .
        '&titulo_per=' . urlencode($titulo_per ?? '') .
        '&docgenenvi=' . urlencode($envigen ?? '') .
        '&redi=1' .
        '&vec_idds=' . urlencode(json_encode($direc ?? []))
);
exit;


function construirDatosDocumento(
    $con,
    $id_ds,
    $t_ds,
    $texto,
    $lista_des,
    $cc_para_tmp,
    $arr_ced,
    $ndes,
    $O_F_D_M_I,
    $dir_emp,
    $tel_emp,
    $url_emp,
    $img_up,
    $dim,
    $esCircular = false,
    $rutaFirmaFormulario = ''
) {
    $data = [
        'radicado'      => '',
        'dep'           => '',
        'fecha'         => '',
        'titulo'        => '',
        'destinatario'  => '',
        'cargo'         => '',
        'dir'           => '',
        'entidad'       => '',
        'ciudad'        => '',
        'asunto'        => '',
        'cuerpo'        => '',
        'firma'         => '',
        'remitente_de'  => '',
        'remitente'     => '',
        'cargor'        => '',
        'anexos'        => '',
        'proyecto'      => '',
        'dependencia'   => '',
        'copiapara'     => '',
        'folios'        => '',
        'fechasis'      => '',
        'direccion'     => '',
        'url_empresa'   => '',
        'elaboro'       => '',
        'respuesta_a'   => '',
    ];

    $rta_a_doc = '';
    $odes = null;
    $dat_dir = '';
    $destino = '';
    $depr = '';
    $carr = '';
    $nom_cc = '';
    $nom_para = '';
    $nom_ciudad = '';
    $entidad = '';
    $ciudad = '';
    $cargo = '';
    $nfol = 0;
    $obsa = '';
    $obsd = '';
    $id_ser = null;
    $cc_para = [];

    if ($t_ds === 'i') {
        $t_dsp = 'si';
    } elseif ($t_ds === 'e') {
        $t_dsp = 'se';
        $rta_a_doc = registrorespuesta::getRadiacadoRespuesta($con, $id_ds);
    } else {
        return ['success' => false, 'data' => []];
    }

    /* Actualizar destinatarios */
    $est_doc = current(estadodocumento::getWhere($con, '%', 1, $id_ds, $t_dsp));
    if ($est_doc) {
        $deser = unserialize(base64_decode($est_doc->getObs_edoc()));
        if (!$deser) {
            $deser = unserialize($est_doc->getObs_edoc());
        }
        if (!is_array($deser)) {
            $deser = [];
        }

        $deser['destinatarios'] = $lista_des;
        $deser['cc_para'] = $cc_para_tmp;

        $sql = "UPDATE estadodocumento SET obs_edoc = ? WHERE id_doc = ? and id_estd = ?";
        $con->Execute($sql, [base64_encode(serialize($deser)), $id_ds, 1]);
    }

    /* Leer copia/para */
    $est_doc = current(estadodocumento::getWhere($con, '%', 1, $id_ds, $t_dsp));
    if ($est_doc) {
        $deser = unserialize(base64_decode($est_doc->getObs_edoc()));
        if (!$deser) {
            $deser = unserialize($est_doc->getObs_edoc());
        }
        if (is_array($deser) && array_key_exists('cc_para', $deser)) {
            $cc_para = $deser['cc_para'];
        }
    }

    $cantidadPara = 0;
    foreach ($cc_para as $item) {
        $cc_id = explode('_', $item);
        if (trim($cc_id[0]) === 'para') {
            $cantidadPara++;
        }
    }

    $titulo = $_POST['tit_des' . ($arr_ced[$ndes] ?? '')] ?? '';

    foreach ($cc_para as $item) {
        $cc_id = explode('_', $item);
        if (!isset($cc_id[1])) {
            continue;
        }

        $funDest = current(funcionario::getWhere($con, $cc_id[1]));
        if (!$funDest) {
            continue;
        }

        if (trim($cc_id[0]) === 'cc') {
            $nom_cc .= $funDest->getLabel() . " (" . $funDest->getCargo() . "), ";
        }

        if (trim($cc_id[0]) === 'para') {
            $tituloTmp = '';

            if ($O_F_D_M_I->mostrar_titulo === 'si') {
                $tituloReq = $_POST['tit_des' . $cc_id[1]] ?? '';
                $tit = explode('_', $tituloReq);

                if (($tit[0] ?? '') === 'dr')  $tituloTmp = 'Doctor';
                if (($tit[0] ?? '') === 'dra') $tituloTmp = 'Doctora';
                if (($tit[0] ?? '') === 'sr')  $tituloTmp = 'Señor';
                if (($tit[0] ?? '') === 'sra') $tituloTmp = 'Señora';

                if (($tit[0] ?? '') === 'tit') {
                    if ($t_ds === 'i') {
                        $sql = "SELECT initcap(lower(tit_fun)) as tit_fun FROM wf_funcionariocargodependencia WHERE id_fun = ?";
                        $rs = $con->Execute($sql, [$tit[1] ?? '']);
                        $tituloTmp = $rs ? ($rs->fields['tit_fun'] ?? '') : '';
                    } else {
                        $sql = "SELECT initcap(lower(tit_des)) as tit_des FROM datos_destinatarios WHERE id_des = ?";
                        $rs = $con->Execute($sql, [$tit[1] ?? '']);
                        $tituloTmp = $rs ? ($rs->fields['tit_des'] ?? '') : '';
                    }
                }
            }

            if ($esCircular) {
                if ($tituloTmp !== '') {
                    $nom_para .= toUpper($tituloTmp) . ' ';
                }

                $nom_para .= toUpper(trim($funDest->getLabel()));

                if (trim($funDest->getCargo()) !== '') {
                    $nom_para .= ', ' . toUpper(trim($funDest->getCargo()));
                }

                $nom_para .= ",\n                ";
            } else {
                if ($tituloTmp !== '') {
                    $nom_para .= $tituloTmp . ': ';
                }

                $nom_para .= $funDest->getLabel();

                if (trim($funDest->getCargo()) !== '') {
                    $nom_para .= "\n" . $funDest->getCargo();
                }

                $nom_para .= "\n\n";
            }
        }
    }

    /* Usuario remitente */
    $usu = usuario::getWhere($con, $_SESSION['id_usu']);
    if (!$usu || !isset($usu[0])) {
        return ['success' => false, 'data' => []];
    }

    $id_fun = $usu[0]->getId_fun();
    $fun = funcionario::getWhere($con, $id_fun);

    if (!$fun || !isset($fun[0])) {
        return ['success' => false, 'data' => []];
    }

    $img_rut = '';
    if ($fun[0]->getId_img()) {
        $img_r = imagen::getWhere($con, $fun[0]->getId_img());
        if ($img_r && isset($img_r[0])) {
            $img_rut = substr($img_r[0]->getRut_img(), 1);
        }
    }

    if ($t_ds === 'i') {
        $objentidad = varsige::getByDescripcion($con, 'nombre_completo_empresa1');
        $objciudad = varsige::getByDescripcion($con, 'ciudadlocal');

        $entidad = $objentidad ? trim($objentidad->getVal_var()) : '';
        $ciudad = $objciudad ? trim($objciudad->getVal_var()) : '';

        $lista = documentosalidaint::getWhere($con, $id_ds);
        $obj = count($lista) > 0 ? $lista[0] : current(historialdocumentosalidaint::getWhere($con, $id_ds));

        if (!$obj) {
            return ['success' => false, 'data' => []];
        }

        $id_ser = $obj->getId_ser();
        $odestino = $obj->getDestino();
        $destino = trim(toUpper($odestino->getLabel()));

        $cargo = uc_Words($odestino->getCargo());
        if (trim(uc_Words($odestino->getCargo())) !== trim(uc_Words($odestino->getEstructura()->getLabel()))) {
            $cargo .= "\n" . uc_Words($odestino->getEstructura()->getLabel());
        }

        if ($osed = $odestino->getSede()) {
            if (trim(uc_Words($osed->getLabel())) === 'PRINCIPAL') {
                $cargo .= "\n" . uc_Words($osed->getLabel());
            }
        }

        $asunto = $obj->getAsu_dsi();
        $nfol   = $obj->getNfol_dsi();
        $obsd   = $obj->getObs_dsi();
        $obsa   = $obj->getDane_dsi();
    } else {
        $lista = documentosalidaext::getWhere($con, $id_ds);
        $obj = count($lista) > 0 ? $lista[0] : current(historialdocumentosalidaext::getWhere($con, $id_ds));

        if (!$obj) {
            return ['success' => false, 'data' => []];
        }

        $id_ser = $obj->getId_ser();
        $odes = current(destinatario::getWhere($con, $obj->getId_des()));
        if (!$odes) {
            return ['success' => false, 'data' => []];
        }

        $destino = trim($odes->getLabel());

        $carg = $odes->getCargo();
        if ($carg) {
            $cargo = $carg->getLabel();
        }

        if (strripos(toLower($cargo), 'ninguno') !== false || trim($cargo) === '') {
            $cargo = '';
        }

        if ($odes->getTdes_des() === 'e') {
            if (stripos(strtolower($destino), 'definir') === false) {
                $cargo .= ($cargo ? "\n" : "") . $odes->getEmpresa();
            } else {
                $destino = '';
                $cargo = $odes->getEmpresa();
            }
        }

        $datos = $odes->getDatos();
        if (is_object($datos)) {
            $dat_dir = $datos->getDireccion();
            if ($dat_dir === 'ninguna') {
                $dat_dir = '';
            }

            if ($datos->getTelefono() !== '') {
                $cargo .= "\nTel. " . $datos->getTelefono();
            }

            $ciud = $datos->getCiudad();
            if ($ciud) {
                $nom_ciudad = $ciud->getLabel();
                if ($nom_ciudad !== '') {
                    $dat_dir .= "\n" . $nom_ciudad . ' - ' . $ciud->getDepartamento()->getLabel();
                }
                $ciudad = $nom_ciudad;
            }
        }

        $asunto = $obj->getAsu_dse();
        $nfol   = $obj->getNfol_dse();
        $obsd   = $obj->getObs_dse();
        $obsa   = $obj->getDane_dse();
    }

    $codigo = trim($obj->getCodigoDocumentacion());

    $o_ori = $obj->getOrigen();
    $da = $o_ori->getEstructura();
    $dep = $da ? $da->getCod_est() : '';

    $remitente = trim(toUpper($o_ori->getLabel()));
    $cargor = uc_Words($o_ori->getCargo());

    if ($t_ds === 'i') {
        $estructuraOrigen = $o_ori->getEstructura();
        if ($estructuraOrigen) {
            $cargor .= "\n" . uc_Words($estructuraOrigen->getLabel());
        }
    }

    if ($o_ori->getKey() != $id_fun) {
        $img_rut = '';
    }

    /* Separar cargo del remitente y dependencia */
    $cargorPartes = array_values(array_filter(explode("\n", str_replace("\r", '', $cargor)), function ($item) {
        return trim($item) !== '';
    }));

    $carr = $cargorPartes[0] ?? '';
    $depr = $cargorPartes[1] ?? '';

    if ($carr === $depr) {
        $depr = '';
    }

    $proyecto = "Proyectó: " . trim(toUpper($fun[0]->getLabel()));
    $elaboro = trim(toUpper($fun[0]->getLabel()));

    $anexos = '';
    if ($nfol > 0) {
        $anexos = "Folios: $nfol";
        if (trim($obsa) !== '') {
            $anexos .= "\nAnexos: $obsa";
        }
    } elseif (trim($obsa) !== '') {
        $anexos = "Anexos: $obsa";
    }

    if ($esCircular && $cantidadPara > 0) {
        $destino = trim(rtrim($nom_para, ", \n"));
        $cargo = '';
        $nom_cc = trim(rtrim($nom_cc, ", "));
    } else {
        $destino = trim($destino);
        $nom_cc = trim(rtrim($nom_cc, ", "));
    }

    $titulo = normalizarTitulo($titulo, $t_ds, $con);

    /* Normalizar cargo del destinatario */
    $cargoDestinatario = '';
    $cargoNormalizado = str_replace("\r", '', (string)$cargo);
    $partesCargo = array_values(array_filter(explode("\n", $cargoNormalizado), function ($item) {
        return trim($item) !== '';
    }));

    if ($t_ds === 'e') {
        $linea1 = $partesCargo[0] ?? '';
        $linea2 = $partesCargo[1] ?? '';

        if ($linea2 === $linea1) {
            $linea2 = '';
        }

        $cargoDestinatario = $linea1;
        if ($linea2 !== '') {
            $cargoDestinatario .= "\n" . $linea2;
        }
    } else {
        $cargoDestinatario = implode("\n", $partesCargo);
    }

    $data['radicado']      = $codigo;
    $data['dep']           = $dep;
    $data['fecha']         = 'Pasto, ' . date('j') . ' de ' . get_mes(date('m')) . ' de ' . date('Y');
    $data['titulo']        = $titulo;
    $data['destinatario']  = $destino;
    $data['cargo']         = $cargoDestinatario;
    $data['dir']           = $dat_dir;
    $data['entidad']       = $entidad;
    $data['ciudad']        = $ciudad;
    $data['asunto']        = ucfirst($asunto);
    $data['cuerpo'] = $texto;
    $rutaFirmaUsar = '';

    if ($rutaFirmaFormulario !== '' && file_exists($rutaFirmaFormulario)) {
        $rutaFirmaUsar = $rutaFirmaFormulario;
    } elseif (!empty($img_rut)) {
        $rutaFirmaDb = rtrim($img_up, '/') . '/' . ltrim($img_rut, '/');
        if (file_exists($rutaFirmaDb)) {
            $rutaFirmaUsar = $rutaFirmaDb;
        }
    }

    $data['firma'] = $rutaFirmaUsar;
    $data['remitente_de']  = $remitente;
    $data['remitente']     = $remitente;
    $data['cargor']        = $carr;
    $data['anexos']        = 'Anexo: ' . $obsa;
    $data['proyecto']      = $proyecto;
    //$data['dependencia']   = $depr;
    $data['dependencia'] = mb_convert_case(utf8_encode($depr), MB_CASE_TITLE, 'UTF-8');
    $data['copiapara']     = trim($nom_cc) !== '' ? 'Copia: ' . trim($nom_cc) : '';
    $data['folios']        = 'Folio: ' . $nfol;
    $data['fechasis']      = date("Y/m/d H:i A");
    $data['direccion']     = trim($dir_emp . " " . $tel_emp);
    $data['url_empresa']   = $url_emp;
    $data['elaboro']       = $elaboro;
    $data['respuesta_a']   = ($rta_a_doc !== '' ? 'En respuesta a documento: ' . $rta_a_doc : '');

    return ['success' => true, 'data' => $data];
}


function generarDocumentoDocx($plantillaDocx, array $data, $dirDestino)
{
    if (!file_exists($plantillaDocx)) {
        return false;
    }

    if (!file_exists($dirDestino)) {
        mkdir($dirDestino, 0777, true);
    }

    $template = new TemplateProcessor($plantillaDocx);

    $template->setValue('radicado', (string)($data['radicado'] ?? ''));
    $template->setValue('dep', (string)($data['dep'] ?? ''));
    $template->setValue('fecha', (string)($data['fecha'] ?? ''));
    $template->setValue('titulo', (string)($data['titulo'] ?? ''));
    $template->setValue(
        'destinatario',
        str_replace(
            ["\r\n", "\n", "\r"],
            ['</w:t><w:br/><w:t xml:space="preserve">', '</w:t><w:br/><w:t xml:space="preserve">', '</w:t><w:br/><w:t xml:space="preserve">'],
            (string)($data['destinatario'] ?? '')
        )
    );
    $template->setValue('cargo', str_replace("\n", '</w:t><w:br/><w:t>', (string)($data['cargo'] ?? '')));
    $template->setValue('dir', str_replace("\n", '</w:t><w:br/><w:t>', (string)($data['dir'] ?? '')));
    $template->setValue('entidad', (string)($data['entidad'] ?? ''));
    $template->setValue('ciudad', (string)($data['ciudad'] ?? ''));
    $template->setValue('asunto', (string)($data['asunto'] ?? ''));
    $bloqueCuerpo = convertirHtmlQuillATextRun($data['cuerpo'] ?? '');
    $template->setComplexBlock('cuerpo', $bloqueCuerpo);
    $template->setValue('remitente_de', (string)($data['remitente_de'] ?? ''));
    $template->setValue('remitente', (string)($data['remitente'] ?? ''));
    $template->setValue('cargor', (string)($data['cargor'] ?? ''));
    $template->setValue('anexos', str_replace("\n", '</w:t><w:br/><w:t>', (string)($data['anexos'] ?? '')));
    $template->setValue('proyecto', (string)($data['proyecto'] ?? ''));
    $template->setValue('dependencia', (string)($data['dependencia'] ?? ''));
    $template->setValue('copiapara', (string)($data['copiapara'] ?? ''));
    $template->setValue('folios', (string)($data['folios'] ?? ''));
    $template->setValue('fechasis', (string)($data['fechasis'] ?? ''));
    $template->setValue('direccion', (string)($data['direccion'] ?? ''));
    $template->setValue('url_empresa', (string)($data['url_empresa'] ?? ''));
    $template->setValue('elaboro', (string)($data['elaboro'] ?? ''));
    $template->setValue('respuesta_a', (string)($data['respuesta_a'] ?? ''));

    if (!empty($data['firma']) && file_exists($data['firma'])) {
        $rutaFirmaProcesada = prepararFirmaParaDocx($data['firma']);

        if ($rutaFirmaProcesada && file_exists($rutaFirmaProcesada)) {
            $dimFirma = obtenerDimensionesFirmaDocx($rutaFirmaProcesada, 150, 42);

            $template->setImageValue('firma', [
                'path'   => $rutaFirmaProcesada,
                'width'  => $dimFirma['width'],
                'height' => $dimFirma['height'],
                'ratio'  => false
            ]);
        } else {
            $template->setValue('firma', '');
        }
    } else {
        $template->setValue('firma', '');
    }

    $archivoFinal = rtrim($dirDestino, '/') . '/oficio_' . md5(microtime()) . '.docx';
    $template->saveAs($archivoFinal);

    return file_exists($archivoFinal) ? $archivoFinal : false;
}


function limpiarHtmlQuillDocx($html)
{
    $html = html_entity_decode((string)$html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Quitar etiquetas peligrosas
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

    // Quill a veces manda vacío así
    if (trim($html) === '' || trim($html) === '<p><br></p>') {
        return '';
    }

    return trim($html);
}

function convertirHtmlQuillATextoDocx($html)
{
    $html = html_entity_decode((string)$html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

    $html = preg_replace('/<li[^>]*>/i', '• ', $html);
    $html = preg_replace('/<\/li>/i', "\n", $html);

    $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
    $html = preg_replace('/<\/p>/i', "\n", $html);
    $html = preg_replace('/<\/div>/i', "\n", $html);

    $texto = strip_tags($html);
    $texto = str_replace(["\r\n", "\r"], "\n", $texto);
    $texto = preg_replace("/\n{3,}/", "\n\n", $texto);
    $texto = trim($texto);

    $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');

    return str_replace(
        "\n",
        '</w:t><w:br/><w:t xml:space="preserve">',
        $texto
    );
}

function convertirHtmlQuillATextRun($html)
{
    $textRun = new TextRun([
        'alignment' => 'left',
        'spaceAfter' => 0
    ]);

    $html = html_entity_decode((string)$html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

    if (trim($html) === '' || trim($html) === '<p><br></p>') {
        $textRun->addText('');
        return $textRun;
    }

    libxml_use_internal_errors(true);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML(
        '<?xml encoding="UTF-8"><body>' . $html . '</body>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    libxml_clear_errors();

    $body = $dom->getElementsByTagName('body')->item(0);

    if (!$body) {
        $textRun->addText(strip_tags($html));
        return $textRun;
    }

    procesarNodosQuillDocx($body, $textRun, [], 0, '');

    return $textRun;
}


function procesarNodosQuillDocx($node, TextRun $textRun, $style = [], $nivelLista = 0, $tipoLista = '')
{
    foreach ($node->childNodes as $child) {
        if ($child->nodeType === XML_TEXT_NODE) {
            $txt = preg_replace('/\s+/', ' ', $child->nodeValue);
            if ($txt !== '') {
                $textRun->addText($txt, normalizarEstiloQuillDocx($style));
            }
            continue;
        }

        if ($child->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }

        $tag = strtolower($child->nodeName);
        $nuevoStyle = $style;

        if (in_array($tag, ['strong', 'b'])) {
            $nuevoStyle['bold'] = true;
        }

        if (in_array($tag, ['em', 'i'])) {
            $nuevoStyle['italic'] = true;
        }

        if ($tag === 'u') {
            $nuevoStyle['underline'] = 'single';
        }

        if ($tag === 's' || $tag === 'strike') {
            $nuevoStyle['strikethrough'] = true;
        }

        if (in_array($tag, ['h1', 'h2', 'h3'])) {
            $nuevoStyle['bold'] = true;
            $nuevoStyle['size'] = $tag === 'h1' ? 16 : ($tag === 'h2' ? 14 : 12);
        }

        $styleAttr = $child->attributes && $child->attributes->getNamedItem('style')
            ? $child->attributes->getNamedItem('style')->nodeValue
            : '';

        if ($styleAttr !== '') {
            if (preg_match('/color\s*:\s*([^;]+)/i', $styleAttr, $m)) {
                $color = trim($m[1]);
                $hex = convertirColorQuillAHex($color);
                if ($hex !== '') {
                    $nuevoStyle['color'] = $hex;
                }
            }

            if (preg_match('/background-color\s*:\s*([^;]+)/i', $styleAttr, $m)) {
                $bg = trim($m[1]);
                $hexBg = convertirColorQuillAHex($bg);
                if ($hexBg !== '') {
                    $nuevoStyle['bgColor'] = $hexBg;
                }
            }

            if (preg_match('/font-size\s*:\s*([0-9]+)px/i', $styleAttr, $m)) {
                $nuevoStyle['size'] = max(8, min(30, round(((int)$m[1]) * 0.75)));
            }
        }

        if ($tag === 'br') {
            $textRun->addTextBreak();
            continue;
        }

        if (in_array($tag, ['p', 'div', 'h1', 'h2', 'h3'])) {
            procesarNodosQuillDocx($child, $textRun, $nuevoStyle, $nivelLista);
            $textRun->addTextBreak();
            continue;
        }

        if (in_array($tag, ['ol', 'ul'])) {
            $contador = 1;

            foreach ($child->childNodes as $li) {
                if ($li->nodeType === XML_ELEMENT_NODE && strtolower($li->nodeName) === 'li') {
                    $prefijo = $tag === 'ol'
                        ? str_repeat('    ', max(0, $nivelLista)) . $contador . '. '
                        : str_repeat('    ', max(0, $nivelLista)) . '• ';

                    $textRun->addText($prefijo, ['name' => 'Arial', 'size' => 11]);
                    procesarNodosQuillDocx($li, $textRun, $nuevoStyle, $nivelLista + 1, $tag);
                    $textRun->addTextBreak();

                    $contador++;
                }
            }

            continue;
        }

        if ($tag === 'li') {
            procesarNodosQuillDocx($child, $textRun, $nuevoStyle, $nivelLista, $tipoLista);
            continue;
        }

        if ($tag === 'blockquote') {
            $textRun->addText('“', ['name' => 'Arial', 'size' => 11, 'italic' => true]);
            procesarNodosQuillDocx($child, $textRun, array_merge($nuevoStyle, ['italic' => true]), $nivelLista);
            $textRun->addText('”', ['name' => 'Arial', 'size' => 11, 'italic' => true]);
            $textRun->addTextBreak();
            continue;
        }

        if ($tag === 'img') {
            $src = $child->getAttribute('src');
            agregarImagenQuillDocx($src, $textRun);
            continue;
        }

        procesarNodosQuillDocx($child, $textRun, $nuevoStyle, $nivelLista);
    }
}

function normalizarEstiloQuillDocx($style)
{
    $base = [
        'name' => 'Arial',
        'size' => 11
    ];

    foreach ($style as $k => $v) {
        if ($v !== '' && $v !== null) {
            $base[$k] = $v;
        }
    }

    return $base;
}

function convertirColorQuillAHex($color)
{
    $color = trim($color);

    if (preg_match('/^#([a-f0-9]{6})$/i', $color, $m)) {
        return strtoupper($m[1]);
    }

    if (preg_match('/rgb\s*\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*\)/i', $color, $m)) {
        return sprintf('%02X%02X%02X', (int)$m[1], (int)$m[2], (int)$m[3]);
    }

    return '';
}

function agregarImagenQuillDocx($src, TextRun $textRun)
{
    if (strpos($src, 'data:image') !== 0) {
        return;
    }

    $ext = 'png';

    if (strpos($src, 'image/jpeg') !== false || strpos($src, 'image/jpg') !== false) {
        $ext = 'jpg';
    }

    $base64 = preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $src);
    $bin = base64_decode($base64);

    if ($bin === false) {
        return;
    }

    $rutaImg = sys_get_temp_dir() . '/quill_img_' . uniqid() . '.' . $ext;
    file_put_contents($rutaImg, $bin);

    if (file_exists($rutaImg)) {
        $textRun->addTextBreak();
        $textRun->addImage($rutaImg, [
            'width' => 260,
            'ratio' => true
        ]);
        $textRun->addTextBreak();
    }
}

function escaparValorDocx($valor)
{
    return htmlspecialchars((string)$valor, ENT_COMPAT, 'UTF-8');
    return (string)$valor;
}

function normalizarTitulo($titulo, $t_ds, $con)
{
    $tit = explode('_', $titulo);

    if (($tit[0] ?? '') === 'dr')  return 'Doctor';
    if (($tit[0] ?? '') === 'dra') return 'Doctora';
    if (($tit[0] ?? '') === 'sr')  return 'Señor';
    if (($tit[0] ?? '') === 'sra') return 'Señora';

    if (($tit[0] ?? '') === 'tit') {
        if ($t_ds === 'i') {
            $sql = "SELECT initcap(lower(tit_fun)) as tit_fun FROM wf_funcionariocargodependencia WHERE id_fun = ?";
            $rs = $con->Execute($sql, [$tit[1] ?? '']);
            return $rs ? ($rs->fields['tit_fun'] ?? '') : '';
        } else {
            $sql = "SELECT initcap(lower(tit_des)) as tit_des FROM datos_destinatarios WHERE id_des = ?";
            $rs = $con->Execute($sql, [$tit[1] ?? '']);
            return $rs ? ($rs->fields['tit_des'] ?? '') : '';
        }
    }

    return $titulo;
}


function obtenerRutaFirmaFormulario()
{
    $firmaModo   = $_POST['firma_modo'] ?? '';
    $firmaData   = $_POST['firma_data'] ?? '';
    $firmaLimpia = $_POST['firma_limpia'] ?? 's';

    if ($firmaModo === 'dibujar' && $firmaLimpia === 'n' && $firmaData !== '') {
        if (preg_match('/^data:image\/png;base64,/', $firmaData)) {
            $firmaData = preg_replace('/^data:image\/png;base64,/', '', $firmaData);
            $firmaBinaria = base64_decode($firmaData);

            if ($firmaBinaria !== false && strlen($firmaBinaria) > 0) {
                $rutaTmp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'firma_canvas_' . uniqid('', true) . '.png';
                file_put_contents($rutaTmp, $firmaBinaria);

                if (file_exists($rutaTmp)) {
                    return $rutaTmp;
                }
            }
        }
    }

    if ($firmaModo === 'subir' && isset($_FILES['firma_archivo']) && ($_FILES['firma_archivo']['error'] ?? 1) === 0) {
        $tmpName = $_FILES['firma_archivo']['tmp_name'];
        $nombre  = $_FILES['firma_archivo']['name'] ?? '';
        $ext     = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
            $rutaTmp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'firma_upload_' . uniqid('', true) . '.' . $ext;

            if (@move_uploaded_file($tmpName, $rutaTmp)) {
                return $rutaTmp;
            }

            if (@copy($tmpName, $rutaTmp)) {
                return $rutaTmp;
            }
        }
    }

    return '';
}

function limpiarArchivosTemporalesFirma($rutaFirmaFormulario)
{
    if ($rutaFirmaFormulario && file_exists($rutaFirmaFormulario)) {
        @unlink($rutaFirmaFormulario);
    }

    foreach (glob(rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'firma_docx_*.png') as $tmpFirmaDocx) {
        @unlink($tmpFirmaDocx);
    }
}

function prepararFirmaParaDocx($rutaOriginal)
{
    if (!$rutaOriginal || !file_exists($rutaOriginal)) {
        return '';
    }

    if (!function_exists('imagecreatetruecolor')) {
        return $rutaOriginal;
    }

    $info = @getimagesize($rutaOriginal);
    if (!$info || !isset($info[2])) {
        return $rutaOriginal;
    }

    $tipo = $info[2];
    $img = null;

    switch ($tipo) {
        case IMAGETYPE_PNG:
            $img = @imagecreatefrompng($rutaOriginal);
            break;
        case IMAGETYPE_JPEG:
            $img = @imagecreatefromjpeg($rutaOriginal);
            break;
        case IMAGETYPE_WEBP:
            if (function_exists('imagecreatefromwebp')) {
                $img = @imagecreatefromwebp($rutaOriginal);
            }
            break;
        default:
            return $rutaOriginal;
    }

    if (!$img) {
        return $rutaOriginal;
    }

    $ancho = imagesx($img);
    $alto  = imagesy($img);

    $procesada = imagecreatetruecolor($ancho, $alto);
    imagealphablending($procesada, false);
    imagesavealpha($procesada, true);

    $transparente = imagecolorallocatealpha($procesada, 255, 255, 255, 127);
    imagefill($procesada, 0, 0, $transparente);

    $minX = $ancho;
    $minY = $alto;
    $maxX = -1;
    $maxY = -1;

    for ($y = 0; $y < $alto; $y++) {
        for ($x = 0; $x < $ancho; $x++) {
            $rgba = imagecolorat($img, $x, $y);

            $a = ($rgba & 0x7F000000) >> 24;
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;

            $prom = ($r + $g + $b) / 3;

            $esTransparente = ($a >= 120);

            // detecta fondo blanco o casi blanco
            $esFondoBlanco =
                ($r >= 235 && $g >= 235 && $b >= 235) ||
                ($prom >= 238);

            // deja solo trazos oscuros / medios
            $esTinta = (!$esTransparente && !$esFondoBlanco);

            if ($esTinta) {
                $gris = (int)$prom;

                // entre más oscuro, más opaco
                $alphaNuevo = 0;
                if ($gris > 220) {
                    $alphaNuevo = 110;
                } elseif ($gris > 200) {
                    $alphaNuevo = 85;
                } elseif ($gris > 180) {
                    $alphaNuevo = 60;
                } elseif ($gris > 160) {
                    $alphaNuevo = 35;
                } else {
                    $alphaNuevo = 0;
                }

                $color = imagecolorallocatealpha($procesada, 0, 0, 0, $alphaNuevo);
                imagesetpixel($procesada, $x, $y, $color);

                if ($x < $minX) $minX = $x;
                if ($y < $minY) $minY = $y;
                if ($x > $maxX) $maxX = $x;
                if ($y > $maxY) $maxY = $y;
            } else {
                imagesetpixel($procesada, $x, $y, $transparente);
            }
        }
    }

    if ($maxX < 0 || $maxY < 0) {
        imagedestroy($img);
        imagedestroy($procesada);
        return $rutaOriginal;
    }

    $paddingX = 12;
    $paddingY = 8;

    $minX = max(0, $minX - $paddingX);
    $minY = max(0, $minY - $paddingY);
    $maxX = min($ancho - 1, $maxX + $paddingX);
    $maxY = min($alto - 1, $maxY + $paddingY);

    $nuevoAncho = ($maxX - $minX) + 1;
    $nuevoAlto  = ($maxY - $minY) + 1;

    $recorte = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
    imagealphablending($recorte, false);
    imagesavealpha($recorte, true);

    $transparente2 = imagecolorallocatealpha($recorte, 255, 255, 255, 127);
    imagefill($recorte, 0, 0, $transparente2);

    imagecopy(
        $recorte,
        $procesada,
        0,
        0,
        $minX,
        $minY,
        $nuevoAncho,
        $nuevoAlto
    );

    $rutaSalida = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'firma_docx_' . uniqid('', true) . '.png';
    imagepng($recorte, $rutaSalida);

    imagedestroy($img);
    imagedestroy($procesada);
    imagedestroy($recorte);

    return file_exists($rutaSalida) ? $rutaSalida : $rutaOriginal;
}

function obtenerDimensionesFirmaDocx($rutaFirma, $maxAncho = 150, $maxAlto = 42)
{
    $dim = @getimagesize($rutaFirma);

    if (!$dim) {
        return [
            'width' => $maxAncho,
            'height' => $maxAlto
        ];
    }

    $anchoOriginal = (int)$dim[0];
    $altoOriginal  = (int)$dim[1];

    if ($anchoOriginal <= 0 || $altoOriginal <= 0) {
        return [
            'width' => $maxAncho,
            'height' => $maxAlto
        ];
    }

    $ratioAncho = $maxAncho / $anchoOriginal;
    $ratioAlto  = $maxAlto / $altoOriginal;
    $ratio = min($ratioAncho, $ratioAlto);

    $nuevoAncho = max(40, (int)round($anchoOriginal * $ratio));
    $nuevoAlto  = max(18, (int)round($altoOriginal * $ratio));

    return [
        'width' => $nuevoAncho,
        'height' => $nuevoAlto
    ];
}

function get_mes($mes)
{
    switch ((int)$mes) {
        case 1:
            return "Enero";
        case 2:
            return "Febrero";
        case 3:
            return "Marzo";
        case 4:
            return "Abril";
        case 5:
            return "Mayo";
        case 6:
            return "Junio";
        case 7:
            return "Julio";
        case 8:
            return "Agosto";
        case 9:
            return "Septiembre";
        case 10:
            return "Octubre";
        case 11:
            return "Noviembre";
        default:
            return "Diciembre";
    }
}

function toUpper($cad)
{
    $cad = strtoupper($cad);
    $cad = str_replace('á', 'Á', $cad);
    $cad = str_replace('é', 'É', $cad);
    $cad = str_replace('í', 'Í', $cad);
    $cad = str_replace('ó', 'Ó', $cad);
    $cad = str_replace('ú', 'Ú', $cad);
    $cad = str_replace('ñ', 'Ñ', $cad);
    return trim($cad);
}

function toLower($cad)
{
    $cad = strtolower($cad);
    $cad = str_replace('Á', 'á', $cad);
    $cad = str_replace('É', 'é', $cad);
    $cad = str_replace('Í', 'í', $cad);
    $cad = str_replace('Ó', 'ó', $cad);
    $cad = str_replace('Ú', 'ú', $cad);
    $cad = str_replace('Ñ', 'ñ', $cad);
    return trim($cad);
}

function uc_Words($cad)
{
    $cad = ucwords(toLower($cad));
    $cad = str_replace(' iii', ' III', $cad);
    $cad = str_replace(' ii', ' II', $cad);
    return $cad;
}
