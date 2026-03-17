<?php
session_start();

/**
 * ============================================================
 * MÓDULO BUZÓN - FUNCIONES PRINCIPALES
 * ============================================================
 */

require_once("../../../libraries/valida/biblio.php");
require_once('../../../scripts/adoConex/adoConex.php');

if (!class_exists('xajaxResponse')) {
    require_once('../../../libraries/xajax/xajax_core/xajax.inc.php');
}

/* ============================================================
 * 1. FUNCIÓN PRINCIPAL ACTUAL
 * ============================================================ */
function envmas_inicio(
    $id_doc_par = NULL,
    $td_par = NULL,
    $res_doc = NULL,
    $ppal = NULL,
    $capaDestino = 'mainCenter',
    $es_modal = 'no'
) {
    global $con;

    unset($_SESSION['objeto']);

    $xres = new xajaxResponse();

    try {
        $id_ser_sal_int = serie::getSerieSalida($con, 'i');
        $id_ser_sal_ext = serie::getSerieSalida($con, 'e');
        $id_ser_nota_int = serie::getSerieSalida($con, 'n');
        $id_sub_ser_nota_int = '-1';

        if ($id_ser_nota_int) {
            $seriNI = current(serie::getWhere($con, $id_ser_nota_int));
            if ($seriNI && $seriNI->getPad_ser() > 0) {
                $id_sub_ser_nota_int = $id_ser_nota_int;
                $id_ser_nota_int = $seriNI->getPad_ser();
            }
        }

        $usu = usuario::getWhere($con, $_SESSION['id_usu']);
        $usu = isset($usu[0]) ? $usu[0] : null;

        if (!$usu) {
            $xres->alert('No se pudo obtener la información del usuario actual.');
            return $xres;
        }

        $id_fun = $usu->getId_fun();
        $nom_fun = method_exists($usu, 'getNom_fun') ? $usu->getNom_fun() : '';
        $nom_usu = method_exists($usu, 'getNom_usu') ? $usu->getNom_usu() : '';

        ob_start();
        ?>
        <div class="gm-envio-wrapper">
            <div class="gm-envio-header">
                <div>
                    <h2 class="gm-envio-title">Radicación / Envío de Documento</h2>
                    <p class="gm-envio-subtitle">Gestione el envío de comunicaciones internas, externas y notas</p>
                </div>
                <div class="gm-envio-user">
                    <strong>Usuario:</strong> <?php echo htmlspecialchars($nom_usu); ?><br>
                    <strong>Funcionario:</strong> <?php echo htmlspecialchars($nom_fun); ?>
                </div>
            </div>

            <form id="frmEnvMasivo" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_doc_par" id="id_doc_par" value="<?php echo htmlspecialchars($id_doc_par); ?>">
                <input type="hidden" name="td_par" id="td_par" value="<?php echo htmlspecialchars($td_par); ?>">
                <input type="hidden" name="res_doc" id="res_doc" value="<?php echo htmlspecialchars($res_doc); ?>">
                <input type="hidden" name="ppal" id="ppal" value="<?php echo htmlspecialchars($ppal); ?>">
                <input type="hidden" name="id_fun" id="id_fun" value="<?php echo htmlspecialchars($id_fun); ?>">

                <div class="gm-card">
                    <div class="gm-card-title">1. Tipo de Documento</div>
                    <div class="gm-grid gm-grid-3">
                        <label class="gm-radio">
                            <input type="radio" name="tip_doc" value="i" checked onclick="gmCambiarTipoDocumento('i')">
                            <span>Interno</span>
                        </label>
                        <label class="gm-radio">
                            <input type="radio" name="tip_doc" value="e" onclick="gmCambiarTipoDocumento('e')">
                            <span>Externo</span>
                        </label>
                        <label class="gm-radio">
                            <input type="radio" name="tip_doc" value="n" onclick="gmCambiarTipoDocumento('n')">
                            <span>Nota Interna</span>
                        </label>
                    </div>
                </div>

                <div class="gm-card">
                    <div class="gm-card-title">2. Series Documentales</div>
                    <div class="gm-grid gm-grid-2">
                        <div class="gm-field">
                            <label>Serie salida interna</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($id_ser_sal_int); ?>" readonly>
                        </div>
                        <div class="gm-field">
                            <label>Serie salida externa</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($id_ser_sal_ext); ?>" readonly>
                        </div>
                        <div class="gm-field">
                            <label>Serie nota interna</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($id_ser_nota_int); ?>" readonly>
                        </div>
                        <div class="gm-field">
                            <label>Subserie nota interna</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($id_sub_ser_nota_int); ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="gm-card">
                    <div class="gm-card-title">3. Información del Documento</div>
                    <div class="gm-grid gm-grid-2">
                        <div class="gm-field">
                            <label>Asunto</label>
                            <input type="text" name="asunto" id="asunto" class="form-control">
                        </div>
                        <div class="gm-field">
                            <label>Fecha</label>
                            <input type="date" name="fecha_doc" id="fecha_doc" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="gm-field gm-col-full">
                            <label>Descripción</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="gm-field">
                            <label>Adjunto</label>
                            <input type="file" name="archivo_adjunto" id="archivo_adjunto" class="form-control">
                        </div>
                        <div class="gm-field">
                            <label>Destinatario</label>
                            <input type="text" name="destinatario" id="destinatario" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="gm-actions">
                    <button type="button" class="btn btn-secondary" onclick="xajax_buzon_inicio('entrada');">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarEnvioDocumento();">Guardar / Enviar</button>
                </div>
            </form>
        </div>

        <script>
        function gmCambiarTipoDocumento(tipo) {
            console.log('Tipo documento:', tipo);
        }

        function guardarEnvioDocumento() {
            alert('Aquí debes conectar la función xajax que guarda el documento.');
        }
        </script>
        <?php
        $html = ob_get_clean();

        if ($es_modal === 'si') {
            $xres->assign($capaDestino, 'innerHTML', $html);
        } else {
            $xres->assign($capaDestino, 'innerHTML', $html);
        }

    } catch (Exception $e) {
        $xres->alert('Error en envmas_inicio: ' . $e->getMessage());
    }

    return $xres;
}


/* ============================================================
 * 2. FUNCIÓN NUEVA / REFACTORIZADA
 * ============================================================ */
function envmas_inicio_nuevo(
    $id_doc_par = NULL,
    $td_par = NULL,
    $res_doc = NULL,
    $ppal = NULL,
    $capaDestino = 'mainCenter',
    $es_modal = 'no'
) {
    global $con;

    unset($_SESSION['objeto']);

    $xres = new xajaxResponse();

    try {
        $id_ser_sal_int = serie::getSerieSalida($con, 'i');
        $id_ser_sal_ext = serie::getSerieSalida($con, 'e');
        $id_ser_nota_int = serie::getSerieSalida($con, 'n');
        $id_sub_ser_nota_int = '-1';

        if ($id_ser_nota_int) {
            $seriNI = current(serie::getWhere($con, $id_ser_nota_int));
            if ($seriNI && $seriNI->getPad_ser() > 0) {
                $id_sub_ser_nota_int = $id_ser_nota_int;
                $id_ser_nota_int = $seriNI->getPad_ser();
            }
        }

        $usu = usuario::getWhere($con, $_SESSION['id_usu']);
        $usu = isset($usu[0]) ? $usu[0] : null;

        if (!$usu) {
            $xres->alert('No fue posible cargar el usuario actual.');
            return $xres;
        }

        $id_fun = $usu->getId_fun();

        ob_start();
        ?>
        <section class="buzon-unificado">
            <div class="buzon-panel">
                <div class="buzon-panel-top">
                    <div class="buzon-head">
                        <div class="buzon-title-icon">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div class="buzon-title-text">
                            <h4>Nuevo envío</h4>
                            <span>Gestión profesional de comunicaciones y documentos</span>
                        </div>
                    </div>

                    <div class="buzon-head-actions">
                        <button type="button" class="gm-btn-icon" onclick="xajax_envmas_inicio_nuevo();" title="Actualizar">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <button type="button" class="gm-btn-icon gm-btn-primary" onclick="guardarEnvioDocumentoNuevo();" title="Guardar">
                            <i class="bi bi-check2-circle"></i>
                        </button>
                    </div>
                </div>

                <div class="buzon-body">
                    <form id="frmEnvMasivoNuevo">
                        <input type="hidden" name="id_doc_par" value="<?php echo htmlspecialchars($id_doc_par); ?>">
                        <input type="hidden" name="td_par" value="<?php echo htmlspecialchars($td_par); ?>">
                        <input type="hidden" name="res_doc" value="<?php echo htmlspecialchars($res_doc); ?>">
                        <input type="hidden" name="ppal" value="<?php echo htmlspecialchars($ppal); ?>">
                        <input type="hidden" name="id_fun" value="<?php echo htmlspecialchars($id_fun); ?>">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipo documento</label>
                                <select class="form-select" name="tip_doc" id="tip_doc_nuevo">
                                    <option value="i">Interno</option>
                                    <option value="e">Externo</option>
                                    <option value="n">Nota interna</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Serie interna</label>
                                <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($id_ser_sal_int); ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Serie externa</label>
                                <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($id_ser_sal_ext); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Asunto</label>
                                <input type="text" class="form-control" name="asunto" id="asunto_nuevo">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Destinatario</label>
                                <input type="text" class="form-control" name="destinatario" id="destinatario_nuevo">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" rows="5" name="descripcion" id="descripcion_nuevo"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Adjunto</label>
                                <input type="file" class="form-control" name="archivo_adjunto" id="archivo_adjunto_nuevo">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Fecha</label>
                                <input type="date" class="form-control" name="fecha_doc" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <script>
        function guardarEnvioDocumentoNuevo() {
            alert('Conectar aquí la función xajax del guardado nuevo.');
        }
        </script>
        <?php
        $html = ob_get_clean();

        $xres->assign($capaDestino, 'innerHTML', $html);

    } catch (Exception $e) {
        $xres->alert('Error en envmas_inicio_nuevo: ' . $e->getMessage());
    }

    return $xres;
}


/* ============================================================
 * 3. FUNCIÓN ANTIGUA / LEGACY
 * ============================================================ */
function envmas_inicio_antiguo(
    $id_doc_par = NULL,
    $td_par = NULL,
    $res_doc = NULL,
    $ppal = NULL,
    $capaDestino = 'mainCenter',
    $es_modal = 'no'
) {
    global $con;

    unset($_SESSION['objeto']);

    $xres = new xajaxResponse();

    try {
        $id_ser_sal_int = serie::getSerieSalida($con, 'i');
        $id_ser_sal_ext = serie::getSerieSalida($con, 'e');
        $id_ser_nota_int = serie::getSerieSalida($con, 'n');
        $id_sub_ser_nota_int = '-1';

        if ($id_ser_nota_int) {
            $seriNI = current(serie::getWhere($con, $id_ser_nota_int));
            if ($seriNI->getPad_ser() > 0) {
                $id_sub_ser_nota_int = $id_ser_nota_int;
                $id_ser_nota_int = $seriNI->getPad_ser();
            }
        }

        $usu = usuario::getWhere($con, $_SESSION['id_usu']);
        $id_fun = $usu[0]->getId_fun();

        ob_start();
        ?>
        <table border="0" cellspacing="10" cellpadding="2" width="95%">
            <tr>
                <td colspan="3" align="center">
                    <span class="grupo">Ingrese la información del documento que desea radicar</span>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="text-align:left" class="tblTitulo2">
                    <span class="grupo">1. Información del Documento</span>
                </td>
            </tr>

            <tr id="tip_doc_mod" style="visibility:visible">
                <td class="etiqueta">Tipo de Documento</td>
                <td align="left">
                    <table border="0" cellspacing="0" cellpadding="0" style="margin-top:5px;" id="tbl_tip_doc">
                        <tr>
                            <td width="120">
                                <input name="tip_doc" type="radio" class="inputRadioCss" value="i" checked> Interno
                            </td>
                            <td width="120">
                                <input name="tip_doc" type="radio" class="inputRadioCss" value="e"> Externo
                            </td>
                            <td width="120">
                                <input name="tip_doc" type="radio" class="inputRadioCss" value="n"> Nota interna
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                <td class="etiqueta">Serie interna</td>
                <td><input type="text" value="<?php echo htmlspecialchars($id_ser_sal_int); ?>" readonly></td>
            </tr>

            <tr>
                <td class="etiqueta">Serie externa</td>
                <td><input type="text" value="<?php echo htmlspecialchars($id_ser_sal_ext); ?>" readonly></td>
            </tr>

            <tr>
                <td class="etiqueta">Serie nota</td>
                <td><input type="text" value="<?php echo htmlspecialchars($id_ser_nota_int); ?>" readonly></td>
            </tr>

            <tr>
                <td class="etiqueta">Subserie nota</td>
                <td><input type="text" value="<?php echo htmlspecialchars($id_sub_ser_nota_int); ?>" readonly></td>
            </tr>

            <tr>
                <td class="etiqueta">Asunto</td>
                <td><input type="text" name="asunto" id="asunto" style="width:350px;"></td>
            </tr>

            <tr>
                <td class="etiqueta">Descripción</td>
                <td><textarea name="descripcion" id="descripcion" cols="50" rows="5"></textarea></td>
            </tr>

            <tr>
                <td class="etiqueta">Destinatario</td>
                <td><input type="text" name="destinatario" id="destinatario" style="width:350px;"></td>
            </tr>

            <tr>
                <td class="etiqueta">Adjunto</td>
                <td><input type="file" name="archivo_adjunto" id="archivo_adjunto"></td>
            </tr>

            <tr>
                <td colspan="2" align="center">
                    <input type="button" value="Guardar" onclick="alert('Conectar función de guardado');">
                    <input type="button" value="Cancelar" onclick="xajax_buzon_inicio('entrada');">
                </td>
            </tr>
        </table>
        <?php
        $html = ob_get_clean();

        $xres->assign($capaDestino, 'innerHTML', $html);

    } catch (Exception $e) {
        $xres->alert('Error en envmas_inicio_antiguo: ' . $e->getMessage());
    }

    return $xres;
}


/* ============================================================
 * 4. LISTADO DE DOCUMENTOS DEL BUZÓN
 * ============================================================ */
function getListaDocumentos($par)
{
    global $con;

    $xres = new xajaxResponse();

    try {
        $usu = current(usuario::getWhere($con, $_SESSION['id_usu']));
        if (!$usu) {
            $xres->alert('No se encontró el usuario actual.');
            return $xres;
        }

        $par['id_fun'] = $usu->getId_fun();
        $buzon = new buzon_documentos();

        $par['pageSize']   = !empty($par['results']) ? $par['results'] : 25;
        $par['startIndex'] = !empty($par['startIndex']) ? $par['startIndex'] : 1;
        $par['archivados'] = !empty($par['archivados']) ? $par['archivados'] : 'n';

        if (empty($par['ver'])) {
            $par['ver'] = empty($par['ver_n']) ? 't' : $par['ver_n'];
        }

        $busquedaGeneral = '';
        if (!empty($par['busquedaGeneral'])) {
            $busquedaGeneral = trim($par['busquedaGeneral']);
        }

        $total = $buzon->contar($con, $par);
        $documentos = $buzon->listado($con, $par);

        ob_start();
        ?>
        <div class="buzon-listado-wrapper">
            <div class="buzon-toolbar">
                <div class="buzon-toolbar-left">
                    <input
                        type="text"
                        id="busquedaGeneral"
                        class="form-control"
                        placeholder="Buscar documentos..."
                        value="<?php echo htmlspecialchars($busquedaGeneral); ?>"
                    >
                </div>
                <div class="buzon-toolbar-right">
                    <button class="btn btn-outline-primary" onclick="buscarDocumentosBuzon();">Buscar</button>
                    <button class="btn btn-primary" onclick="xajax_envmas_inicio();">Redactar</button>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Radicado</th>
                            <th>Asunto</th>
                            <th>Remitente / Destinatario</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($documentos)) { ?>
                            <?php foreach ($documentos as $i => $doc) { ?>
                                <tr>
                                    <td><?php echo ($par['startIndex'] + $i); ?></td>
                                    <td><?php echo htmlspecialchars(method_exists($doc, 'getNum_rad') ? $doc->getNum_rad() : ''); ?></td>
                                    <td><?php echo htmlspecialchars(method_exists($doc, 'getAsu_doc') ? $doc->getAsu_doc() : ''); ?></td>
                                    <td><?php echo htmlspecialchars(method_exists($doc, 'getNom_ter') ? $doc->getNom_ter() : ''); ?></td>
                                    <td><?php echo htmlspecialchars(method_exists($doc, 'getFec_doc') ? $doc->getFec_doc() : ''); ?></td>
                                    <td><?php echo htmlspecialchars(method_exists($doc, 'getEst_doc') ? $doc->getEst_doc() : ''); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" title="Ver">Ver</button>
                                        <button class="btn btn-sm btn-outline-primary" title="Editar">Editar</button>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No se encontraron documentos.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="buzon-paginacion mt-3">
                <small>Total registros: <?php echo (int)$total; ?></small>
            </div>
        </div>

        <script>
        function buscarDocumentosBuzon() {
            var busqueda = document.getElementById('busquedaGeneral').value;
            xajax_getListaDocumentos({
                busquedaGeneral: busqueda,
                startIndex: 1,
                results: 25
            });
        }
        </script>
        <?php
        $html = ob_get_clean();

        $xres->assign('contenidoListadoDocumentos', 'innerHTML', $html);

    } catch (Exception $e) {
        $xres->alert('Error en getListaDocumentos: ' . $e->getMessage());
    }

    return $xres;
}


/* ============================================================
 * 5. CARGA DE HOJAS / COMPONENTES DEL SISTEMA
 * ============================================================ */
function mrcCargarHojas($id_com, $id_usu = NULL, $id_com_sel = '')
{
    global $con;

    $xres = new xajaxResponse();

    try {
        if (!$id_usu) {
            $id_usu = $_SESSION['id_usu'];
        }

        require_once('../../../class/base/componentesistema.php');
        require_once('../../../class/base/componentesvisitados.php');
        require_once('../../../class/base/usuario.php');
        require_once('../../../libraries/adodb/adodb.inc.php');
        require_once('../../../scripts/adoConex/adoConex.php');

        $usus = usuario::getWhere($con, $id_usu);
        $usu  = isset($usus[0]) ? $usus[0] : null;
        unset($usus);

        if (!$usu) {
            $xres->alert('No se encontró el usuario para cargar los componentes.');
            return $xres;
        }

        if ($id_com > 0) {
            $hijos = $usu->getComponentesByPadre($id_com, true);
        } else {
            $hijos = componentesistema::getHijos($con, $id_com, 'a');
        }

        ob_start();
        ?>
        <div class="mrc-hojas-wrapper">
            <ul class="mrc-hojas-lista">
                <?php if (!empty($hijos)) { ?>
                    <?php foreach ($hijos as $hijo) { ?>
                        <?php
                        $idHijo = method_exists($hijo, 'getId_com') ? $hijo->getId_com() : '';
                        $nomHijo = method_exists($hijo, 'getNom_com') ? $hijo->getNom_com() : 'Componente';
                        $urlHijo = method_exists($hijo, 'getUrl_com') ? $hijo->getUrl_com() : '#';
                        $activo = ($id_com_sel == $idHijo) ? 'activo' : '';
                        ?>
                        <li class="mrc-hoja-item <?php echo $activo; ?>">
                            <a href="javascript:void(0);" onclick="cargarComponente('<?php echo $idHijo; ?>', '<?php echo $urlHijo; ?>');">
                                <?php echo htmlspecialchars($nomHijo); ?>
                            </a>
                        </li>
                    <?php } ?>
                <?php } else { ?>
                    <li class="mrc-hoja-item vacio">No hay componentes disponibles.</li>
                <?php } ?>
            </ul>
        </div>

        <script>
        function cargarComponente(idComponente, urlComponente) {
            console.log('Cargar componente:', idComponente, urlComponente);
            // Aquí conectas la llamada real del sistema.
        }
        </script>
        <?php
        $html = ob_get_clean();

        $xres->assign('mainLeft', 'innerHTML', $html);

    } catch (Exception $e) {
        $xres->alert('Error en mrcCargarHojas: ' . $e->getMessage());
    }

    return $xres;
}
?>