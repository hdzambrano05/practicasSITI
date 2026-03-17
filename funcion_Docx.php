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


$vs_rtf           = varsige::getByDescripcion($con, 'rtf_correspondencia_externa');
$dir_empr         = varsige::getByDescripcion($con, 'direccion_empresa');
$tel_empr         = varsige::getByDescripcion($con, 'telefonos_empresa');
$url_empr         = varsige::getByDescripcion($con, 'url_empresa');
$F_D_M_I          = varsige::getByDescripcion($con, 'FORMATO_DESTINATARIO_MASIVO_INTERNO');
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

$GLOBALS['img_up'] = $img_up;

$dim = 30;
$_POST['t_e'] = $_POST['t_e'] ?? 'masivo';

$lista_des  = $_REQUEST['list_des_html'] ?? '';
$t_ds       = $_POST['tip_doc'] ?? '';
$envigen    = $_POST['docgenenvi'] ?? '';
$texto      = $_POST['texto_doc'] ?? '';
$id_ds_post = $_POST['id_ds'] ?? '';
$titulo_per = $_POST['titulo_per'] ?? '';

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
    die("No se ha generado un radicado");
}

$lista_des  = $_REQUEST['list_des_html'] ?? '';
$t_ds       = $_POST['tip_doc'] ?? '';
$envigen    = $_POST['docgenenvi'] ?? '';
$texto      = $_POST['texto_doc'] ?? '';
$id_ds_post = $_POST['id_ds'] ?? '';
$titulo_per = $_POST['titulo_per'] ?? '';

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
    die("No se ha generado un radicado");
}

if (count($arch_vec_mas) > 0) {
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
        exit;
    }
}

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
    $dim
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
    $plantillaDocx = '';

    if ($t_ds === 'i') {
        $t_dsp = 'si';
    } elseif ($t_ds === 'e') {
        $t_dsp = 'se';
        $rta_a_doc = registrorespuesta::getRadiacadoRespuesta($con, $id_ds);
    } else {
        return ['success' => false, 'data' => [], 'plantilla' => ''];
    }

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
            if ($cantidadPara <= 1) {
                $nom_para .= $funDest->getLabel() . "\n" . $funDest->getCargo() . ", ";
            } else {
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

                    if ($tituloTmp !== '') {
                        $nom_para .= $tituloTmp . ': ';
                    }
                }

                $nom_para .= $funDest->getLabel();

                if ($O_F_D_M_I->mostrar_cargo === 'si') {
                    $nom_para .= " (" . $funDest->getCargo() . ")";
                }

                $nom_para .= $O_F_D_M_I->separador . ' ';
            }
        }
    }

    $usu = usuario::getWhere($con, $_SESSION['id_usu']);
    if (!$usu || !isset($usu[0])) {
        return ['success' => false, 'data' => [], 'plantilla' => ''];
    }

    $id_fun = $usu[0]->getId_fun();
    $fun = funcionario::getWhere($con, $id_fun);

    if (!$fun || !isset($fun[0])) {
        return ['success' => false, 'data' => [], 'plantilla' => ''];
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
            return ['success' => false, 'data' => [], 'plantilla' => ''];
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
            return ['success' => false, 'data' => [], 'plantilla' => ''];
        }

        $id_ser = $obj->getId_ser();
        $odes = current(destinatario::getWhere($con, $obj->getId_des()));
        if (!$odes) {
            return ['success' => false, 'data' => [], 'plantilla' => ''];
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

    $objFormato = controlformatos::getWhere($con, '%', $id_ser);
    if (count($objFormato) == 0) {
        $objFormato = controlformatos::getWhere($con, '%', '%', 'PLANTILLA_GENERAL');
    }

    if (count($objFormato) > 0) {
        $otro_frm = json_decode($objFormato[0]->getOtro_frm());
        if ($otro_frm && !empty($otro_frm->codigo)) {
            $plantillaDocx = $otro_frm->codigo;
        }
    }

    if (!$plantillaDocx || !file_exists($plantillaDocx)) {
        return ['success' => false, 'data' => [], 'plantilla' => ''];
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

    if (count($cc_para) > 1 && trim($nom_cc) !== '') {
        $destino = rtrim($nom_para, ", ");
        $cargo = '';
        $nom_cc = rtrim($nom_cc, ", ");
    } else {
        $nom_cc = trim($nom_cc);
    }

    $titulo = normalizarTitulo($titulo, $t_ds, $con);

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
    $data['cuerpo']        = limpiarTextoDocx($texto);
    $data['firma']         = $img_rut;
    $data['remitente_de']  = $remitente;
    $data['remitente']     = $remitente;
    $data['cargor']        = $carr;
    $data['anexos']        = 'Anexo: ' . $obsa;
    $data['proyecto']      = $proyecto;
    $data['dependencia']   = $depr;
    $data['copiapara']     = trim($nom_cc) !== '' ? 'Copia: ' . trim($nom_cc) : '';
    $data['folios']        = 'Folio: ' . $nfol;
    $data['fechasis']      = date("Y/m/d H:i A");
    $data['direccion']     = trim($dir_emp . " " . $tel_emp);
    $data['url_empresa']   = $url_emp;
    $data['elaboro']       = $elaboro;
    $data['respuesta_a']   = ($rta_a_doc !== '' ? 'En respuesta a documento: ' . $rta_a_doc : '');

    return [
        'success'   => true,
        'data'      => $data,
        'plantilla' => $plantillaDocx
    ];
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
    $template->setValue('destinatario', (string)($data['destinatario'] ?? ''));
    $template->setValue('cargo', str_replace("\n", '</w:t><w:br/><w:t>', (string)($data['cargo'] ?? '')));
    $template->setValue('dir', str_replace("\n", '</w:t><w:br/><w:t>', (string)($data['dir'] ?? '')));
    $template->setValue('entidad', (string)($data['entidad'] ?? ''));
    $template->setValue('ciudad', (string)($data['ciudad'] ?? ''));
    $template->setValue('asunto', (string)($data['asunto'] ?? ''));
    $template->setValue('cuerpo', str_replace("\n", '</w:t><w:br/><w:t>', (string)($data['cuerpo'] ?? '')));
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

    if (!empty($data['firma'])) {
        $rutaFirma = rtrim($GLOBALS['img_up'] ?? '', '/') . '/' . ltrim($data['firma'], '/');

        if (file_exists($rutaFirma)) {
            $template->setImageValue('firma', [
                'path'   => $rutaFirma,
                'width'  => 120,
                'height' => 50,
                'ratio'  => true
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
function limpiarTextoDocx($texto)
{
    $texto = str_replace(["\r\n", "\r"], "\n", $texto);
    return trim($texto);
}

function escaparValorDocx($valor)
{
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

function get_mes($mes)
{
    switch ((int)$mes) {
        case 1: return "Enero";
        case 2: return "Febrero";
        case 3: return "Marzo";
        case 4: return "Abril";
        case 5: return "Mayo";
        case 6: return "Junio";
        case 7: return "Julio";
        case 8: return "Agosto";
        case 9: return "Septiembre";
        case 10: return "Octubre";
        case 11: return "Noviembre";
        default: return "Diciembre";
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