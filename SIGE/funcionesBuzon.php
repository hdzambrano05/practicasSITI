<?php

//BUZON INICIO
function buzon_inicio_nuevo($accion = 'R', $id_edoc = NULL)
{
    global $con;
    $xres = new xajaxResponse();

    require_once('../../../class/base/componentesistema.php');

    ob_start();

    $ver = '';
    $archivados = 'n';
    $titulo = 'Buzón';
    $icono = 'bi-inbox-fill';
    $botonesMasivos = '';

    switch ($accion) {
        case 'R':
            $ver = 'pi,pe';
            $archivados = 'n';
            $titulo = 'Recibidos';
            $icono = 'bi-inbox-fill';
            $botonesMasivos = '
                <button type="button" class="gm-btn" onclick="try{ newConfirmarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-check-square-fill"></i> Confirmar
                </button>
                <button type="button" class="gm-btn" onclick="try{ newTrasladarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-folder-x"></i> Trasladar
                </button>
                <button type="button" class="gm-btn" onclick="try{ newRemitirMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-send-check-fill"></i> Remitir
                </button>
                <button type="button" class="gm-btn" onclick="try{ newArchivarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-trash"></i> Archivar
                </button>
                <button type="button" class="gm-btn" onclick="try{ newImprimirPlanilla() }catch(e){alert(e);}">
                    <i class="bi bi-file-spreadsheet"></i> Planilla
                </button>';
            break;

        case 'E':
            $ver = 'ps';
            $archivados = 'n';
            $titulo = 'Enviados';
            $icono = 'bi-send-fill';
            $botonesMasivos = '
                <button type="button" class="gm-btn" onclick="try{ newImprimirPlanilla() }catch(e){alert(e);}">
                    <i class="bi bi-file-spreadsheet"></i> Planilla
                </button>';
            break;

        case 'B':
            $ver = 'borradores';
            $archivados = 'n';
            $titulo = 'Borradores';
            $icono = 'bi-file-earmark-text-fill';
            $botonesMasivos = '
                <button type="button" class="gm-btn" onclick="try{ newImprimirPlanilla() }catch(e){alert(e);}">
                    <i class="bi bi-file-spreadsheet"></i> Planilla
                </button>';
            break;

        case 'A':
            $ver = 't';
            $archivados = 's';
            $titulo = 'Archivados';
            $icono = 'bi-archive-fill';
            $botonesMasivos = '
                <button type="button" class="gm-btn" onclick="try{ newdesarchivarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-folder-symlink"></i> Desarchivar
                </button>
                <button type="button" class="gm-btn" onclick="try{ newImprimirPlanilla() }catch(e){alert(e);}">
                    <i class="bi bi-file-spreadsheet"></i> Planilla
                </button>';
            break;

        case 'CT':
            $ver = 'a';
            $archivados = 'n';
            $titulo = 'Contactos';
            $icono = 'bi-people-fill';
            $botonesMasivos = '
                <button type="button" class="gm-btn" onclick="try{ newConfirmarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-check-square-fill"></i> Confirmar
                </button>
                <button type="button" class="gm-btn" onclick="try{ newTrasladarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-folder-x"></i> Trasladar
                </button>
                <button type="button" class="gm-btn" onclick="try{ newArchivarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-trash"></i> Archivar
                </button>';
            break;

        case 'AT':
            $ver = 'a';
            $archivados = 's';
            $titulo = 'Adjuntos';
            $icono = 'bi-folder-fill';
            $botonesMasivos = '
                <button type="button" class="gm-btn" onclick="try{ newdesarchivarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-folder-symlink"></i> Desarchivar
                </button>';
            break;

        default:
            $accion = 'R';
            $ver = 'pi,pe';
            $archivados = 'n';
            $titulo = 'Recibidos';
            $icono = 'bi-inbox-fill';
            $botonesMasivos = '
                <button type="button" class="gm-btn" onclick="try{ newConfirmarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-check-square-fill"></i> Confirmar
                </button>
                <button type="button" class="gm-btn" onclick="try{ newTrasladarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-folder-x"></i> Trasladar
                </button>
                <button type="button" class="gm-btn" onclick="try{ newRemitirMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-send-check-fill"></i> Remitir
                </button>
                <button type="button" class="gm-btn" onclick="try{ newArchivarMasivo() }catch(e){alert(e);}">
                    <i class="bi bi-trash"></i> Archivar
                </button>
                <button type="button" class="gm-btn" onclick="try{ newImprimirPlanilla() }catch(e){alert(e);}">
                    <i class="bi bi-file-spreadsheet"></i> Planilla
                </button>';
            break;
    }
?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <div class="buzon-gmail">

        <aside class="buzon-sidebar" id="buzonSidebar">
            <div class="buzon-logo">
                <i class="bi bi-envelope-fill"></i>
            </div>

            <ul class="buzon-menu">
                <li class="<?php if ($accion == 'R') echo 'active'; ?>" onclick="xajax_buzon_inicio('R')">
                    <i class="bi bi-inbox-fill"></i>
                    <span class="menu-text">Recibidos</span>
                </li>

                <li class="<?php if ($accion == 'E') echo 'active'; ?>" onclick="xajax_buzon_inicio('E')">
                    <i class="bi bi-send-fill"></i>
                    <span class="menu-text">Enviados</span>
                </li>

                <li class="<?php if ($accion == 'B') echo 'active'; ?>" onclick="xajax_buzon_inicio('B')">
                    <i class="bi bi-file-earmark-text-fill"></i>
                    <span class="menu-text">Borradores</span>
                </li>

                <li class="<?php if ($accion == 'A') echo 'active'; ?>" onclick="xajax_buzon_inicio('A')">
                    <i class="bi bi-archive-fill"></i>
                    <span class="menu-text">Archivados</span>
                </li>

                <li class="<?php if ($accion == 'CT') echo 'active'; ?>" onclick="xajax_buzon_inicio('CT')">
                    <i class="bi bi-people-fill"></i>
                    <span class="menu-text">Contactos</span>
                </li>

                <li class="<?php if ($accion == 'AT') echo 'active'; ?>" onclick="xajax_buzon_inicio('AT')">
                    <i class="bi bi-folder-fill"></i>
                    <span class="menu-text">Adjuntos</span>
                </li>
            </ul>
        </aside>

        <section class="buzon-unificado">
            <div class="buzon-panel">

                <!-- CABECERA FIJA -->
                <div class="buzon-panel-fijo">

                    <div class="buzon-panel-top">
                        <div class="buzon-head">
                            <div class="buzon-title-icon">
                                <i class="bi <?php echo $icono; ?>"></i>
                            </div>

                            <div class="buzon-title-text">
                                <h4><?php echo $titulo; ?></h4>
                                <span>Gestión profesional de comunicaciones y documentos</span>
                            </div>
                        </div>

                        <div class="buzon-head-actions">
                            <button
                                type="button"
                                class="gm-btn-icon"
                                onclick="xajax_buzon_inicio('<?php echo $accion; ?>');"
                                title="Actualizar">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>

                            <button
                                type="button"
                                class="gm-btn-icon gm-btn-primary"
                                onclick="xajax_envmas_inicio();"
                                title="Redactar">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </div>
                    </div>

                    <div class="buzon-barra-unica">
                        <div class="barra-busqueda">
                            <i class="bi bi-search"></i>
                            <input
                                type="text"
                                id="busquedaGeneral"
                                class="barra-input"
                                placeholder="Buscar en <?php echo strtolower($titulo); ?>"
                                oninput="programarBusquedaGeneral()">
                        </div>

                        <div class="barra-botones">
                            <button
                                type="button"
                                class="gm-btn-icon"
                                onclick="ejecutarBusquedaGeneral()"
                                title="Buscar">
                                <i class="bi bi-search"></i>
                            </button>

                            <button
                                type="button"
                                class="gm-btn-icon"
                                onclick="limpiarBusquedaGeneral()"
                                title="Limpiar">
                                <i class="bi bi-x-lg"></i>
                            </button>

                            <button
                                type="button"
                                class="gm-btn-icon"
                                title="Seleccionar todo"
                                onclick="
                            var chk = document.getElementsByClassName('chk_carp');
                            var todosMarcados = true;

                            for (var i = 0; i < chk.length; i++) {
                                if (!chk[i].checked) {
                                    todosMarcados = false;
                                    break;
                                }
                            }

                            for (var j = 0; j < chk.length; j++) {
                                chk[j].checked = !todosMarcados;
                            }

                            var checkTodos = document.getElementById('checkTodos');
                            if (checkTodos) {
                                checkTodos.checked = (!todosMarcados && chk.length > 0);
                            }

                            if (typeof actualizarAccionesMasivas === 'function') {
                                actualizarAccionesMasivas();
                            }
                        ">
                                <i class="bi bi-check2-square"></i>
                            </button>
                        </div>
                    </div>

                    <div id="accionesMasivasBox" class="buzon-masivas-unico">
                        <div class="buzon-masivas-info">
                            <i class="bi bi-stack"></i>
                            <span id="cantidadSeleccionados">0</span> documentos seleccionados
                        </div>

                        <div class="buzon-masivas-botones">
                            <?php echo $botonesMasivos; ?>
                        </div>
                    </div>

                </div>
                <!-- FIN CABECERA FIJA -->

                <form id="idFormulario" class="buzon-formulario">

                    <!-- SOLO ESTA PARTE HACE SCROLL -->
                    <div class="buzon-tabla-scroll">
                        <div class="table-responsive buzon-table-responsive">
                            <table class="buzon-table">
                                <thead>
                                    <tr>
                                        <th width="45" class="th-check">
                                            <label class="check-header-wrap">
                                                <input
                                                    type="checkbox"
                                                    id="checkTodos"
                                                    onclick="
                                    var chk = document.getElementsByClassName('chk_carp');
                                    for (var i = 0; i < chk.length; i++) {
                                        chk[i].checked = this.checked;
                                    }

                                    if (typeof actualizarAccionesMasivas === 'function') {
                                        actualizarAccionesMasivas();
                                    }
                                ">
                                            </label>
                                        </th>
                                        <th class="th-radicado">Radicado</th>
                                        <th class="th-asunto">Asunto</th>
                                        <th class="th-remitente">Remitente</th>
                                        <th class="th-tipo">Tipo</th>
                                        <th class="th-origen">Origen / Destino</th>
                                        <th width="150" class="th-acciones">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyBuzon"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="buzon-footer">
                        <div id="paginadorBuzon"></div>
                    </div>

                    <div id="mrcDivModalDialogCont" style="display:none;"></div>

                    <input type="hidden" name="ver" id="ver" value="<?php echo $ver; ?>">
                    <input type="hidden" name="archivados" id="archivados" value="<?php echo $archivados; ?>">
                    <input type="hidden" name="startIndex" id="startIndex" value="1">
                    <input type="hidden" name="pageSize" id="pageSize" value="7">
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion; ?>">
                    <input type="hidden" name="id_edoc" id="id_edoc" value="<?php echo $id_edoc; ?>">
                    <input type="hidden" name="busquedaGeneral" id="busquedaGeneralHidden" value="">

                </form>

                <div id="buzonLoadingModal" class="buzon-loading-modal-global" style="display:none;">
                    <div class="buzon-loading-modal-box">
                        <div class="buzon-loading-modal-spinner"></div>
                        <div class="buzon-loading-modal-title">Cargando documentos</div>
                        <div class="buzon-loading-modal-text">
                            Espera un momento mientras se actualiza la información
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php

    $html = ob_get_clean();
    $xres->addAssign('mainCenter', 'innerHTML', $html);

    $xres->addScript("
        window.timerBusquedaGeneral = null;

        window.actualizarAccionesMasivas = function () {
            var checks = document.getElementsByClassName('chk_carp');
            var seleccionados = 0;

            for (var i = 0; i < checks.length; i++) {
                if (checks[i].checked) {
                    seleccionados++;
                }
            }

            var box = document.getElementById('accionesMasivasBox');
            var cantidad = document.getElementById('cantidadSeleccionados');
            var checkTodos = document.getElementById('checkTodos');

            if (cantidad) {
                cantidad.innerHTML = seleccionados;
            }

            if (box) {
                if (seleccionados >= 1) {
                    box.classList.add('show');
                } else {
                    box.classList.remove('show');
                }
            }

            if (checkTodos) {
                checkTodos.checked = (checks.length > 0 && seleccionados === checks.length);
            }
        };

        window.mostrarCargandoBuzon = function (mensaje, subtitulo) {
    var modal = document.getElementById('buzonLoadingModal');

    var titulo = document.querySelector('.buzon-loading-modal-title');
    var texto = document.querySelector('.buzon-loading-modal-text');

    if (titulo) {
        titulo.innerHTML = mensaje || 'Cargando documentos';
    }

    if (texto) {
        texto.innerHTML = subtitulo || 'Espera un momento mientras se actualiza la información';
    }

    if (modal) {
        modal.style.display = 'flex';
    }
};

        window.programarBusquedaGeneral = function () {
            if (window.timerBusquedaGeneral) {
                clearTimeout(window.timerBusquedaGeneral);
            }

            window.timerBusquedaGeneral = setTimeout(function () {
                ejecutarBusquedaGeneral();
            }, 350);
        };

        window.ejecutarBusquedaGeneral = function () {
            var input = document.getElementById('busquedaGeneral');
            var hidden = document.getElementById('busquedaGeneralHidden');
            var startIndex = document.getElementById('startIndex');

            if (input && hidden) {
                hidden.value = input.value.trim();
            }

            if (startIndex) {
                startIndex.value = 1;
            }

            mostrarCargandoBuzon('Buscando documentos...');
            xajax_getListaDocumentos(xajax.getFormValues('idFormulario'));
        };

        window.limpiarBusquedaGeneral = function () {
            var input = document.getElementById('busquedaGeneral');
            var hidden = document.getElementById('busquedaGeneralHidden');
            var startIndex = document.getElementById('startIndex');

            if (window.timerBusquedaGeneral) {
                clearTimeout(window.timerBusquedaGeneral);
            }

            if (input) input.value = '';
            if (hidden) hidden.value = '';
            if (startIndex) startIndex.value = 1;

            mostrarCargandoBuzon('Cargando documentos...');
            xajax_getListaDocumentos(xajax.getFormValues('idFormulario'));
        };

        mostrarCargandoBuzon('Cargando documentos...');
        xajax_getListaDocumentos(xajax.getFormValues('idFormulario'));

        setTimeout(function () {
            actualizarAccionesMasivas();
        }, 300);
    ");

    $xres->addScript("
        if (typeof mostrarCargandoBuzon === 'function') {
            mostrarCargandoBuzon('Cargando documentos...');
        }
        xajax_getListaDocumentos(xajax.getFormValues('idFormulario'));
    ");

    $xres->addScript("
        setTimeout(function(){
            if (typeof actualizarAccionesMasivas === 'function') {
                actualizarAccionesMasivas();
            }
        }, 300);
    ");

    return $xres->getXML();
}

// LISTAR DOCUMENTOS
function getListaDocumentos_nuevo($par)
{
    global $con;

    $xres = new xajaxResponse();

    $usu = current(usuario::getwhere($con, $_SESSION['id_usu']));

    $par['id_fun']     = $usu->getId_fun();
    $buzon             = new buzon_documentos();
    $par['pageSize']   = !empty($par['results']) ? (int)$par['results'] : 25;
    $par['startIndex'] = !empty($par['startIndex']) ? (int)$par['startIndex'] : 1;
    $par['archivados'] = !empty($par['archivados']) ? $par['archivados'] : 'n';

    if (empty($par['ver'])) {
        $par['ver'] = empty($par['ver_n']) ? 't' : $par['ver_n'];
    }

    $busquedaGeneral = '';
    if (!empty($par['busquedaGeneral'])) {
        $busquedaGeneral = trim($par['busquedaGeneral']);
    }

    $total      = $buzon->contar($con, $par);
    $documentos = $buzon->listado($con, $par);

    if ($busquedaGeneral !== '') {
        $textoBuscar = mb_strtolower($busquedaGeneral, 'UTF-8');

        $documentos = array_filter($documentos, function ($row) use ($textoBuscar) {
            $rad  = isset($row['cod']) ? mb_strtolower($row['cod'], 'UTF-8') : '';
            $asu  = isset($row['asu']) ? mb_strtolower($row['asu'], 'UTF-8') : '';
            $rem  = isset($row['rem']) ? mb_strtolower($row['rem'], 'UTF-8') : '';
            $dest = isset($row['dest']) ? mb_strtolower($row['dest'], 'UTF-8') : '';
            $tipo = isset($row['tip_doc_m']) ? mb_strtolower($row['tip_doc_m'], 'UTF-8') : '';
            $fec  = isset($row['fec']) ? mb_strtolower($row['fec'], 'UTF-8') : '';

            return (
                strpos($rad, $textoBuscar) !== false ||
                strpos($asu, $textoBuscar) !== false ||
                strpos($rem, $textoBuscar) !== false ||
                strpos($dest, $textoBuscar) !== false ||
                strpos($tipo, $textoBuscar) !== false ||
                strpos($fec, $textoBuscar) !== false
            );
        });

        $documentos = array_values($documentos);
        $total = count($documentos);
    }

    ob_start();

    $contadorColapsar = 0;
    $arrayRadicado = "arrayRadi={};";

    foreach ($documentos as $row) {

        $contadorColapsar++;
        $arrayRadicado .= "arrayRadi.fila" . $contadorColapsar . "='" . addslashes($row['cod']) . "';";

        $esNuevo = false;

        /* solo marcar como no leído en RECIBIDOS */
        if (isset($par['accion']) && $par['accion'] == 'R') {
            $esNuevo = ((int)$row['id_estd'] < 5 || (int)$row['id_estd'] == 10);
        }

        $claseFila = $esNuevo ? 'buzon-row fila-no-leida' : 'buzon-row fila-leida';

        $claseSemaforo = 'semaforoNeutro';
        if ($row['tip_doc'] == 'de') {
            $claseSemaforo = documentoentrada::getSemaforoDocumento($con, $row['id_doc']);
        }

        if ($row['id_edoc'] > 0) {
            $estadoDocumento = current(estadodocumento::getWhere($con, $row['id_edoc']));
            $id_estd = $estadoDocumento ? $estadoDocumento->getId_estd() : 4;
        } else {
            $id_estd = 4;
        }

        $propietario = buzon_documentos::consultaPropietario($con, $row['id_doc'], $row['tip_doc'], $row['id_edoc']);
        $estados     = buzon_documentos::consultar_estadod_pasos($con, $row['tip_doc'], $id_estd, $propietario);

        $onclickDocumento = "try{modaldocumento({tip_doc:'" . $row['tip_doc'] . "',cod:'" . addslashes($row['cod']) . "',id_doc:'" . $row['id_doc'] . "',id_edoc:'" . $row['id_edoc'] . "'},'mrcDivModalDialogCont','si');}catch(e){alert(e);}";

        $id_rem = '';
        $id_ori = '';
        $mostrarResponder = false;

        if ($row['tip_doc'] == 'de') {
            $doc = documentoentrada::getWhere($con, $row['id_doc']);
            $doc = isset($doc[0]) ? $doc[0] : null;
        } elseif ($row['tip_doc'] == 'se') {
            $doc = documentosalidaext::getWhere($con, $row['id_doc']);
            $doc = isset($doc[0]) ? $doc[0] : null;
            if ($doc != null) {
                $id_rem = $doc->getRem_dse();
                $id_ori = $doc->getOri_dse();
            }
        } else {
            $doc = documentosalidaint::getWhere($con, $row['id_doc']);
            $doc = isset($doc[0]) ? $doc[0] : null;
            if ($doc != null) {
                $id_rem = $doc->getRem_dsi();
                $id_ori = $doc->getOri_dsi();
            }
        }

        // Mostrar Responder solo en Recibidos
        $mostrarResponder = false;

        // SOLO mostrar responder en RECIBIDOS
        if (
            isset($par['accion']) &&
            $par['accion'] == 'R' &&
            ($row['tip_doc'] == 'si' || $row['tip_doc'] == 'ss' || $row['tip_doc'] == 'se')
        ) {

            // 👇 SOLO si ya NO está en estado de confirmar
            $tieneConfirmar = false;

            if (!empty($estados)) {
                foreach ($estados as $paso) {
                    if ($paso == 6) {
                        $tieneConfirmar = true;
                        break;
                    }
                }
            }

            // 👉 SOLO si ya confirmó
            if (!$tieneConfirmar) {
                $mostrarResponder = true;
            }
        }
    ?>
        <tr class="<?php echo $claseFila; ?>">

            <td width="40" class="td-check">
                <div class="form-check">
                    <input
                        type="checkbox"
                        class="form-check-input checkbox-item chk_carp"
                        value="<?php echo $row['id_doc']; ?>,<?php echo $row['tip_doc']; ?>,<?php echo $row['id_edoc']; ?>,<?php echo $row['id_estd']; ?>"
                        onclick="actualizarAccionesMasivas(); event.stopPropagation();">
                </div>
            </td>

            <td width="170" onclick="<?php echo $onclickDocumento; ?>">
                <div class="mail-meta">
                    <span class="<?php echo $claseSemaforo; ?>"></span>

                    <div class="mail-codigo-wrap">
                        <span class="buzon-codigo <?php echo $esNuevo ? 'texto-no-leido' : 'texto-leido'; ?>">
                            <?php echo $row['cod']; ?>
                        </span>
                    </div>

                    <div class="buzon-fecha <?php echo $esNuevo ? 'fecha-no-leida' : ''; ?>">
                        <i class="bi bi-calendar3"></i>
                        <?php echo $row['fec']; ?>
                    </div>
                </div>
            </td>

            <td onclick="<?php echo $onclickDocumento; ?>">
                <div class="mail-asunto <?php echo $esNuevo ? 'texto-no-leido' : 'texto-leido'; ?>">
                    <?php echo $row['asu']; ?>
                </div>
            </td>

            <td onclick="<?php echo $onclickDocumento; ?>">
                <div class="mail-remitente <?php echo $esNuevo ? 'texto-no-leido' : 'texto-leido'; ?>">
                    <i class="bi bi-person"></i>
                    <?php echo $row['rem']; ?>
                </div>
            </td>

            <td>
                <div class="mail-tipo-wrap">
                    <span class="buzon-badge badge-<?php echo $row['tip_doc']; ?>">
                        <?php echo $row['tip_doc_m']; ?>
                    </span>
                    <?php if (!empty($row['tipo'])) { ?>
                        <div class="mail-secundario <?php echo $esNuevo ? 'texto-no-leido-sec' : ''; ?>">
                            <?php echo $row['tipo']; ?>
                        </div>
                    <?php } ?>
                </div>
            </td>

            <td onclick="<?php echo $onclickDocumento; ?>">
                <div class="mail-destino <?php echo $esNuevo ? 'texto-no-leido' : 'texto-leido'; ?>">
                    <i class="bi bi-people"></i>
                    <?php echo $row['dest']; ?>
                </div>
            </td>

            <td>
                <div class="buzon-actions">
                    <?php
                    if (!empty($estados)) {
                        foreach ($estados as $paso) {
                            switch ($paso) {
                                case 6:
                                    $mostrar = true;
                                    if (($row['tip_doc'] == 'si' && $row['propio'] == 's' && $par['accion'] == 'R')) {
                                        $mostrar = false;
                                    }

                                    if ($mostrar) {
                    ?>
                                        <button
                                            type="button"
                                            class="btn btn-outline-success mi-boton mi-boton-mini"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            data-bs-custom-class="tooltip-buzon"
                                            title="Confirmar"
                                            aria-label="Confirmar"
                                            onclick="try{ parent.xajax_buzon_observacion(<?php echo $row['id_doc']; ?>,'<?php echo $row['tip_doc']; ?>','conf','<?php echo $row['id_edoc']; ?>'); }catch(e){alert(e);}">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    <?php
                                    }
                                    break;

                                case 9:
                                    ?>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary mi-boton mi-boton-mini"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-custom-class="tooltip-buzon"
                                        title="Remitir"
                                        aria-label="Remitir"
                                        onclick="try{ parent.xajax_buzon_observacion(<?php echo $row['id_doc']; ?>,'<?php echo $row['tip_doc']; ?>','remi','<?php echo $row['id_edoc']; ?>'); }catch(e){alert(e);}">
                                        <i class="bi bi-send"></i>
                                    </button>
                                <?php
                                    break;

                                case 10:
                                ?>
                                    <button
                                        type="button"
                                        class="btn btn-outline-warning mi-boton mi-boton-mini"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-custom-class="tooltip-buzon"
                                        title="Trasladar"
                                        aria-label="Trasladar"
                                        onclick="try{ parent.xajax_buzon_observacion(<?php echo $row['id_doc']; ?>,'<?php echo $row['tip_doc']; ?>','tras','<?php echo $row['id_edoc']; ?>'); }catch(e){alert(e);}">
                                        <i class="bi bi-folder"></i>
                                    </button>
                                <?php
                                    break;

                                case 11:
                                ?>
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger mi-boton mi-boton-mini"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-custom-class="tooltip-buzon"
                                        title="Archivar"
                                        aria-label="Archivar"
                                        onclick="try{ parent.menuArchivar(<?php echo $row['id_doc']; ?>,'<?php echo $row['tip_doc']; ?>','<?php echo $row['id_edoc']; ?>'); }catch(e){alert(e);}">
                                        <i class="bi bi-archive"></i>
                                    </button>
                        <?php
                                    break;
                            }
                        }
                    }

                    if ($mostrarResponder) {
                        ?>
                        <button
                            type="button"
                            class="btn btn-outline-secondary mi-boton mi-boton-mini"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-custom-class="tooltip-buzon"
                            title="Responder"
                            aria-label="Responder"
                            onclick='try{
                                            parent.xajax_envmas_inicio(
                                                "<?php echo $row['id_doc']; ?>",
                                                "<?php echo $row['tip_doc']; ?>",
                                                "{\"id_doc\":\"<?php echo $row['id_doc']; ?>\",\"tip_doc\":\"<?php echo $row['tip_doc']; ?>\",\"cod\":\"<?php echo addslashes($row['cod']); ?>\"}"
                                            );
                                        }catch(e){alert(e);}'>
                            <i class="bi bi-reply-fill"></i>
                        </button>
                    <?php
                    }

                    if (isset($par['accion']) && $par['accion'] == 'A') {
                    ?>
                        <button
                            type="button"
                            class="btn btn-outline-dark mi-boton mi-boton-mini"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-custom-class="tooltip-buzon"
                            title="Desarchivar"
                            aria-label="Desarchivar"
                            onclick="try{ parent.xajax_buzon_desarchivar('<?php echo $row['id_doc']; ?>','<?php echo $row['tip_doc']; ?>'); }catch(e){alert(e);}">
                            <i class="bi bi-folder-symlink"></i>
                        </button>
                    <?php
                    }
                    ?>
                </div>
            </td>
        </tr>
    <?php
    }

    $htmlTabla = ob_get_clean();
    $xres->addAssign('tbodyBuzon', 'innerHTML', $htmlTabla);

    ob_start();
    $totalPaginas = ($par['pageSize'] > 0) ? ceil($total / $par['pageSize']) : 1;
    if ($totalPaginas < 1) {
        $totalPaginas = 1;
    }

    $startIndexActual = isset($par['startIndex']) ? (int)$par['startIndex'] : 1;

    if ($startIndexActual < 1) {
        $startIndexActual = 1;
    }

    $paginaActual = (int)ceil($startIndexActual / $par['pageSize']);

    if ($paginaActual < 1) {
        $paginaActual = 1;
    }

    if ($paginaActual > $totalPaginas) {
        $paginaActual = $totalPaginas;
    }

    $inicio = max(1, $paginaActual - 2);
    $fin    = min($totalPaginas, $paginaActual + 2);
    ?>
    <nav>
        <ul class="pagination justify-content-center">

            <?php if ($paginaActual > 1) { ?>
                <li class="page-item">
                    <span
                        class="page-link"
                        onclick='
                        get("startIndex").value=1;
                        get("paginadorBuzon").innerHTML="";
                        if(typeof mostrarCargandoBuzon === "function"){
                            mostrarCargandoBuzon("Cargando documentos...");
                        }
                        xajax_getListaDocumentos(xajax.getFormValues("idFormulario"));
                    '>
                        Primero
                    </span>
                </li>

                <li class="page-item">
                    <span
                        class="page-link"
                        onclick='
                        get("startIndex").value=<?php echo (($paginaActual - 2) * $par['pageSize']) + 1; ?>;
                        get("paginadorBuzon").innerHTML="";
                        if(typeof mostrarCargandoBuzon === "function"){
                            mostrarCargandoBuzon("Cargando documentos...");
                        }
                        xajax_getListaDocumentos(xajax.getFormValues("idFormulario"));
                    '>
                        &laquo;
                    </span>
                </li>
            <?php } ?>

            <?php if ($inicio > 1) { ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php } ?>

            <?php for ($i = $inicio; $i <= $fin; $i++) { ?>
                <li class="page-item <?php echo ($paginaActual == $i) ? 'active' : ''; ?>">
                    <span
                        class="page-link"
                        onclick='
                        get("startIndex").value=<?php echo (($i - 1) * $par['pageSize']) + 1; ?>;
                        get("paginadorBuzon").innerHTML="";
                        if(typeof mostrarCargandoBuzon === "function"){
                            mostrarCargandoBuzon("Cargando documentos...");
                        }
                        xajax_getListaDocumentos(xajax.getFormValues("idFormulario"));
                    '>
                        <?php echo $i; ?>
                    </span>
                </li>
            <?php } ?>

            <?php if ($fin < $totalPaginas) { ?>
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            <?php } ?>

            <?php if ($paginaActual < $totalPaginas) { ?>
                <li class="page-item">
                    <span
                        class="page-link"
                        onclick='
                        get("startIndex").value=<?php echo ($paginaActual * $par['pageSize']) + 1; ?>;
                        get("paginadorBuzon").innerHTML="";
                        if(typeof mostrarCargandoBuzon === "function"){
                            mostrarCargandoBuzon("Cargando documentos...");
                        }
                        xajax_getListaDocumentos(xajax.getFormValues("idFormulario"));
                    '>
                        &raquo;
                    </span>
                </li>

                <li class="page-item">
                    <span
                        class="page-link"
                        onclick='
                        get("startIndex").value=<?php echo (($totalPaginas - 1) * $par['pageSize']) + 1; ?>;
                        get("paginadorBuzon").innerHTML="";
                        if(typeof mostrarCargandoBuzon === "function"){
                            mostrarCargandoBuzon("Cargando documentos...");
                        }
                        xajax_getListaDocumentos(xajax.getFormValues("idFormulario"));
                    '>
                        &Uacute;ltimo
                    </span>
                </li>
            <?php } ?>

        </ul>
    </nav>
<?php

    $htmlPaginador = ob_get_clean();
    $xres->addAssign('paginadorBuzon', 'innerHTML', $htmlPaginador);

    $xres->addScript("try{ $arrayRadicado }catch(e){alert(e);}");

    $xres->addScript("
		if (typeof actualizarAccionesMasivas === 'function') {
			actualizarAccionesMasivas();
		}
	");

    $xres->addScript("
		try{
			if (get('agruparTodos')) {
				if (get('agruparTodos').className == 'agrupar') {
					get('agruparTodos').className = 'agrupar2';
				} else {
					get('agruparTodos').className = 'agrupar';
				}
				get('agruparTodos').click();
			}
		}catch(e){}
	");

    $xres->addScript("
		try{
			if (get('accionesMasivas')) {
				get('accionesMasivas').options[0].selected = true;
			}
		}catch(e){}
	");

    $xres->addScript("
		try{
			var tooltipElements = [].slice.call(document.querySelectorAll('[data-bs-toggle=\"tooltip\"]'));

			tooltipElements.forEach(function(el){
				var instancia = bootstrap.Tooltip.getInstance(el);
				if (instancia) {
					instancia.dispose();
				}

				new bootstrap.Tooltip(el, {
					trigger: 'hover focus',
					container: 'body',
					boundary: 'window'
				});
			});
		}catch(e){}
	");

    $xres->addScript("
    try{
        var modal = document.getElementById('buzonLoadingModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }catch(e){}
");

    return utf8_encode($xres->getXML());
}


//MODAL 
function mostrar_modal2_nuevo($id_doc, $tip_doc, $id_edoc = '', $accion = '', $propio = '')
{
    require("../../../libraries/valida/biblio.php");
    require('../../../scripts/adoConex/adoConex.php');

    global $con;
    $xres = new xajaxResponse();

    $buzon = new buzon_documentos();

    $id_rem     = '';
    $id_ori     = '';
    $proyecto   = '';
    $arch       = '';
    $tipo       = '';
    $asunto     = '';
    $nfol       = '';
    $cod        = '';
    $fec        = '';
    $rem        = '';
    $des        = '';
    $desc_gen   = '';
    $docRes     = array();
    $gen_doc    = 'n';
    $con_copia  = '';
    $id_estd    = 4;

    /* =========================================================
       OBTENER DATOS PRINCIPALES DEL DOCUMENTO
    ========================================================= */
    if ($tip_doc == 'de') {
        $doc = documentoentrada::getWhere($con, $id_doc);
        $doc = isset($doc[0]) ? $doc[0] : null;

        if ($doc) {
            $asti = current(asignastiker::getWhere($con, $doc->getId_asti()));

            $cod    = $asti ? $asti->getNum_asti() : '';
            $fec    = $asti ? $asti->getFec_asti() : '';
            $rem    = $doc->getOrigen();
            $des    = $doc->getDestino();
            $nfol   = $doc->getNfol_dent();
            $tipo   = $doc->getClaseDocumento() ? $doc->getClaseDocumento()->getLabel() : '';
            $asunto = $doc->getAsu_dent();
            $arch   = $doc->getImg_dent();

            $docRes = documentosalidaext::getRadicadosRespuesta($con, $cod);
        }
    } elseif ($tip_doc == 'se') {
        $doc = documentosalidaext::getWhere($con, $id_doc);
        $doc = isset($doc[0]) ? $doc[0] : null;

        if ($doc) {
            $id_rem = $doc->getRem_dse();
            $id_ori = $doc->getOri_dse();

            $fun = current(funcionario::getWhere($con, $doc->getOri_dse()));
            $proyecto = $fun ? $fun->getLabel() : '';

            $cod      = $doc->getCon_ds();
            $fec      = $doc->getFec_dse() . ' ' . $doc->getHor_dse();
            $rem      = $doc->getOrigen();
            $des      = $doc->getDestino();
            $nfol     = $doc->getNfol_dse();
            $desc_gen = $doc->getObs_dse();
            $asunto   = $doc->getAsu_dse();

            if ($doc->getId_ser()) {
                $serieTmp = current(serie::getWhere($con, $doc->getId_ser()));
                $tipo = $serieTmp ? $serieTmp->getLabel() : '';
            }

            $dsd = documentosalidadig::getWhere($con, '%', $id_doc, 'e');
            $dsd = isset($dsd[0]) ? $dsd[0] : null;
            if ($dsd) {
                $arch = $dsd->getDoc_dsd();
            }

            $gen_doc = 's';
            $docRes = documentosalidaext::getRadicadosRespuesta($con, $cod);
        }
    } else {
        $doc = documentosalidaint::getWhere($con, $id_doc);
        $doc = isset($doc[0]) ? $doc[0] : null;

        if ($doc) {
            $id_rem = $doc->getRem_dsi();
            $id_ori = $doc->getOri_dsi();

            $ti_doc = '';
            $est = 1;

            if ($tip_doc == 'si') {
                $ti_doc = 'si';
            } elseif ($tip_doc == 'ss') {
                $ti_doc = 'ss';
                $estd_doc = current(estadodocumento::getWhere($con, '%', '%', $id_doc, $ti_doc, '%', '%', '%', '%'));
                $est = $estd_doc ? $estd_doc->getId_estd() : 1;
            }

            $estd_doc = current(estadodocumento::getWhere($con, '%', $est, $id_doc, $ti_doc, '%', '%', '%', '%'));

            if ($estd_doc) {
                $obs_d = unserialize(base64_decode($estd_doc->getObs_edoc()));
                $obs_d = isset($obs_d['cc_para']) ? $obs_d['cc_para'] : array();

                if ($obs_d) {
                    if (in_array("cc_" . $doc->getId_fun(), $obs_d) || in_array("cco_" . $doc->getId_fun(), $obs_d)) {
                        $con_copia = '<span class="gm-doc-copy">(Con copia)</span>';
                    }
                }
            }

            $fun = current(funcionario::getWhere($con, $doc->getOri_dsi()));
            $proyecto = $fun ? $fun->getLabel() : '';

            $cod      = $doc->getCon_ds();
            $fec      = $doc->getFec_dsi();
            $rem      = $doc->getOrigen();
            $des      = $doc->getDestino();
            $nfol     = $doc->getNfol_dsi();
            $desc_gen = $doc->getObs_dsi();
            $asunto   = $doc->getAsu_dsi();

            if ($doc->getId_ser()) {
                $serieTmp = current(serie::getWhere($con, $doc->getId_ser()));
                $tipo = $serieTmp ? $serieTmp->getLabel() : '';
            }

            $dsd = documentosalidadig::getWhere($con, '%', $id_doc, ($tip_doc == 'si' ? 'i' : 'n'), 'id_dsd', 1);
            $dsd = isset($dsd[0]) ? $dsd[0] : null;

            if ($dsd) {
                $arch = $dsd->getDoc_dsd();
            }

            if ($tip_doc == 'si') {
                $gen_doc = 's';
            }
        }
    }

    /* =========================================================
       ESTADO Y ACCIONES DISPONIBLES
    ========================================================= */
    if ($id_edoc > 0) {
        $estadoDocumento = current(estadodocumento::getWhere($con, $id_edoc));
        $id_estd = $estadoDocumento ? $estadoDocumento->getId_estd() : 4;
    }

    $propietario = buzon_documentos::consultaPropietario($con, $id_doc, $tip_doc, $id_edoc);
    $estados = buzon_documentos::consultar_estadod_pasos($con, $tip_doc, $id_estd, $propietario);

    /* =========================================================
       PERMISO PARA RESPONDER
    ========================================================= */
    $mostrarResponder = false;

    if ($tip_doc == 'de') {
        $docTmp = documentoentrada::getWhere($con, $id_doc);
        $docTmp = isset($docTmp[0]) ? $docTmp[0] : null;
        if ($docTmp) {
            $rem = $docTmp->getOrigen();
        }
    } elseif ($tip_doc == 'se') {
        $docTmp = documentosalidaext::getWhere($con, $id_doc);
        $docTmp = isset($docTmp[0]) ? $docTmp[0] : null;
        if ($docTmp) {
            $id_rem = $docTmp->getRem_dse();
            $id_ori = $docTmp->getOri_dse();
            $rem = $docTmp->getOrigen();
            $des = $docTmp->getDestino();
        }
    } else {
        $docTmp = documentosalidaint::getWhere($con, $id_doc);
        $docTmp = isset($docTmp[0]) ? $docTmp[0] : null;
        if ($docTmp) {
            $id_rem = $docTmp->getRem_dsi();
            $id_ori = $docTmp->getOri_dsi();
        }
    }

    if (
        ($tip_doc == 'si' || $tip_doc == 'ss' || $tip_doc == 'se') &&
        ($_SESSION['id_fun'] == $id_rem || $_SESSION['id_fun'] == $id_ori)
    ) {
        $mostrarResponder = true;
    }

    /* =========================================================
       PREPARAR ARCHIVO ADJUNTO
    ========================================================= */
    $filename_nom = '';
    $extension = '';
    $icono_dw = "<i class='bi bi-file-earmark-fill gm-file-generic'></i>";
    $rutaWeb = '';
    $viewerId = 'gmDocxViewer_' . intval($id_doc) . '_' . mt_rand(1000, 9999);

    $archivosZip = array();

    if ($arch != '') {
        $zip = new ZipArchive();
        $var_cons = $arch;
        $filename = '../../../imgs_arch/' . $arch;

        if ($zip->open($filename) === true) {
            $tmpAbs = realpath(__DIR__ . '/../../tmp');
            if ($tmpAbs === false) {
                @mkdir(__DIR__ . '/../../tmp', 0777, true);
                $tmpAbs = realpath(__DIR__ . '/../../tmp');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $tmpName = $zip->getNameIndex($i);
                if (!$tmpName) {
                    continue;
                }

                $tmpExt = strtolower(pathinfo($tmpName, PATHINFO_EXTENSION));

                if (in_array($tmpExt, array('pdf', 'docx', 'doc', 'rtf'))) {
                    $contenido = $zip->getFromIndex($i);

                    if ($contenido !== false && $tmpAbs) {
                        $nombreSeguro = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($tmpName));
                        $nombreFinal = 'preview_' . $id_doc . '_' . time() . '_' . mt_rand(1000, 9999) . '_' . $nombreSeguro;
                        $rutaAbsFinal = $tmpAbs . DIRECTORY_SEPARATOR . $nombreFinal;

                        if (@file_put_contents($rutaAbsFinal, $contenido) !== false) {
                            $archivosZip[] = array(
                                'nombre' => basename($tmpName),
                                'extension' => $tmpExt,
                                'rutaWeb' => '../../tmp/' . rawurlencode($nombreFinal)
                            );
                        }
                    }
                }

                if ($tmpExt == 'zip') {
                    $contenidoZipInterno = $zip->getFromIndex($i);

                    if ($contenidoZipInterno !== false && $tmpAbs) {
                        $nombreZipSeguro = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($tmpName));
                        $nombreZipTmp = 'zip_interno_' . $id_doc . '_' . time() . '_' . mt_rand(1000, 9999) . '_' . $nombreZipSeguro;
                        $rutaZipTmp = $tmpAbs . DIRECTORY_SEPARATOR . $nombreZipTmp;

                        if (@file_put_contents($rutaZipTmp, $contenidoZipInterno) !== false) {
                            $zipInterno = new ZipArchive();

                            if ($zipInterno->open($rutaZipTmp) === true) {
                                for ($j = 0; $j < $zipInterno->numFiles; $j++) {
                                    $tmpNameInterno = $zipInterno->getNameIndex($j);
                                    if (!$tmpNameInterno) {
                                        continue;
                                    }

                                    $tmpExtInterno = strtolower(pathinfo($tmpNameInterno, PATHINFO_EXTENSION));

                                    if (in_array($tmpExtInterno, array('pdf', 'docx', 'doc', 'rtf'))) {
                                        $contenidoInterno = $zipInterno->getFromIndex($j);

                                        if ($contenidoInterno !== false && $tmpAbs) {
                                            $nombreSeguro = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($tmpNameInterno));
                                            $nombreFinal = 'preview_inner_' . $id_doc . '_' . time() . '_' . mt_rand(1000, 9999) . '_' . $nombreSeguro;
                                            $rutaAbsFinal = $tmpAbs . DIRECTORY_SEPARATOR . $nombreFinal;

                                            if (@file_put_contents($rutaAbsFinal, $contenidoInterno) !== false) {
                                                $archivosZip[] = array(
                                                    'nombre' => basename($tmpNameInterno),
                                                    'extension' => $tmpExtInterno,
                                                    'rutaWeb' => '../../tmp/' . rawurlencode($nombreFinal)
                                                );
                                            }
                                        }
                                    }
                                }

                                $zipInterno->close();
                            }
                        }
                    }
                }
            }

            $zip->close();

            if (count($archivosZip) > 0) {
                $filename_nom = $archivosZip[0]['nombre'];
                $extension = $archivosZip[0]['extension'];
                $rutaWeb = $archivosZip[0]['rutaWeb'];

                if ($extension == 'pdf') {
                    $icono_dw = "<i class='bi bi-file-earmark-pdf-fill gm-file-pdf'></i>";
                } elseif ($extension == 'rtf' || $extension == 'docx' || $extension == 'doc') {
                    $icono_dw = "<i class='bi bi-file-earmark-word-fill gm-file-word'></i>";
                }
            }
        }
    }

    /* =========================================================
       RENDER
    ========================================================= */
    ob_start();
?>
    <div class="gm-doc-modal">

        <div class="gm-doc-header-card">
            <div class="gm-doc-header-top">
                <div class="gm-doc-header-main">
                    <div class="gm-doc-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div class="gm-doc-title-wrap">
                        <h3 class="gm-doc-title">Información del documento</h3>
                        <div class="gm-doc-subtitle">Consulta detallada, recorrido y adjuntos del documento</div>
                    </div>
                </div>

                <div class="gm-doc-header-actions">
                    <div class="gm-doc-actions-label">
                        <i class="bi bi-lightning-charge"></i>
                        <span>Acciones disponibles</span>
                    </div>

                    <div class="gm-doc-actions">
                        <?php
                        if (is_array($estados) && count($estados) > 0) {
                            foreach ($estados as $paso) {
                                switch ($paso) {
                                    case 6:
                                        $mostrar = true;
                                        if (($tip_doc == 'si' && $propio == 's' && $accion == 'R')) {
                                            $mostrar = false;
                                        }
                                        if ($mostrar) {
                        ?>
                                            <button type="button" class="btn gm-action-btn gm-action-dark"
                                                onclick="try{ parent.xajax_buzon_observacion(<?php echo $id_doc; ?>,'<?php echo $tip_doc; ?>','conf','<?php echo $id_edoc; ?>'); }catch(e){alert(e);}">
                                                <i class="bi bi-check-square-fill"></i> Confirmar
                                            </button>
                                        <?php
                                        }
                                        break;

                                    case 9:
                                        ?>
                                        <button type="button" class="btn gm-action-btn gm-action-blue"
                                            onclick="try{ parent.xajax_buzon_observacion(<?php echo $id_doc; ?>,'<?php echo $tip_doc; ?>','remi','<?php echo $id_edoc; ?>'); }catch(e){alert(e);}">
                                            <i class="bi bi-send-check-fill"></i> Remitir
                                        </button>
                                    <?php
                                        break;

                                    case 10:
                                    ?>
                                        <button type="button" class="btn gm-action-btn gm-action-yellow"
                                            onclick="try{ parent.xajax_buzon_observacion(<?php echo $id_doc; ?>,'<?php echo $tip_doc; ?>','tras','<?php echo $id_edoc; ?>'); }catch(e){alert(e);}">
                                            <i class="bi bi-folder-x"></i> Trasladar
                                        </button>
                                    <?php
                                        break;

                                    case 11:
                                    ?>
                                        <button type="button" class="btn gm-action-btn gm-action-red"
                                            onclick="try{ parent.menuArchivar(<?php echo $id_doc; ?>,'<?php echo $tip_doc; ?>','<?php echo $id_edoc; ?>'); }catch(e){alert(e);}">
                                            <i class="bi bi-trash"></i> Archivar
                                        </button>
                            <?php
                                        break;
                                }
                            }
                        }

                        if ($mostrarResponder) {
                            ?>
                            <button type="button" class="btn gm-action-btn gm-action-gray"
                                onclick="try{ parent.xajax_envmas_inicio('<?php echo $id_doc; ?>','<?php echo $tip_doc; ?>'); }catch(e){alert(e);}">
                                <i class="bi bi-pencil-fill"></i> Responder
                            </button>
                        <?php
                        }

                        if ($accion == 'A') {
                        ?>
                            <button type="button" class="btn gm-action-btn gm-action-soft"
                                onclick="try{ parent.xajax_buzon_desarchivar('<?php echo $id_doc; ?>','<?php echo $tip_doc; ?>'); }catch(e){alert(e);}">
                                <i class="bi bi-folder-symlink"></i> Desarchivar
                            </button>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="gm-doc-chip-group">
                <div class="gm-doc-chip">
                    <span class="gm-doc-chip-label">Tipo</span>
                    <span class="gm-doc-chip-value"><?php echo $tipo != '' ? $tipo : 'No definido'; ?></span>
                </div>

                <div class="gm-doc-chip">
                    <span class="gm-doc-chip-label">Radicado</span>
                    <span class="gm-doc-chip-value"><?php echo $cod; ?></span>
                </div>

                <div class="gm-doc-chip">
                    <span class="gm-doc-chip-label">Fecha</span>
                    <span class="gm-doc-chip-value"><?php echo $fec; ?></span>
                </div>
            </div>
        </div>

        <div class="gm-doc-body">

            <div class="gm-doc-grid">
                <div class="gm-doc-card">
                    <div class="gm-doc-card-head">
                        <h4><i class="bi bi-card-text"></i> Datos de registro</h4>
                    </div>

                    <div class="gm-doc-table-wrap">
                        <table class="table gm-doc-table">
                            <tbody>
                                <tr>
                                    <th>Documento</th>
                                    <td>
                                        <?php
                                        if ($tip_doc == 'si') {
                                            echo 'INTERNO INTERNO';
                                        } elseif ($tip_doc == 'se') {
                                            echo 'INTERNO EXTERNO';
                                        } elseif ($tip_doc == 'de') {
                                            echo 'EXTERNO INTERNO';
                                        } elseif ($tip_doc == 'i') {
                                            echo 'DOCUMENTO INTERNO';
                                        } elseif ($tip_doc == 'ss') {
                                            echo 'NOTA INTERNA';
                                        }
                                        echo ' ' . $con_copia;
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Asunto</th>
                                    <td><?php echo $asunto; ?></td>
                                </tr>
                                <tr>
                                    <th>Folios</th>
                                    <td><?php echo $nfol; ?></td>
                                </tr>
                                <tr>
                                    <th>Remitente</th>
                                    <td><?php $buzon->getDatosRemitente2($con, $id_doc, $tip_doc, 'n'); ?></td>
                                </tr>
                                <tr>
                                    <th>Destinatario</th>
                                    <td><?php $buzon->getDatosDestinatario2($con, $id_doc, $tip_doc, 'n'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="gm-doc-subsection">
                        <div class="gm-doc-subsection-title">
                            <i class="bi bi-diagram-3"></i> Recorrido
                        </div>
                        <div class="gm-doc-traza">
                            <?php $buzon->getTraza($con, $id_doc, $tip_doc, 's'); ?>
                        </div>
                    </div>
                </div>

                <div class="gm-doc-card gm-doc-card-adjuntos">
                    <div class="gm-doc-card-head gm-doc-card-head-compact">
                        <h4><i class="bi bi-paperclip"></i> Adjuntos</h4>

                        <div class="gm-doc-download gm-doc-download-bottom">
                            <a class="gm-doc-download-link"
                                target="_blank"
                                href="../../../build/common/files/download_file.php?id_var=16&file=<?php echo urlencode($arch); ?>"
                                title="Descargar Archivo">
                                <?php echo $icono_dw; ?>
                                <span>Descargar archivo</span>
                            </a>
                        </div>
                    </div>

                    <?php if (isset($archivosZip) && count($archivosZip) > 0) { ?>
                        <div style="margin-bottom:12px; display:flex; flex-direction:column; gap:6px;">
                            <?php foreach ($archivosZip as $idx => $docZip) { ?>

                                <?php
                                $iconoLista = "<i class='bi bi-file-earmark-fill gm-file-generic'></i>";

                                if ($docZip['extension'] == 'pdf') {
                                    $iconoLista = "<i class='bi bi-file-earmark-pdf-fill gm-file-pdf'></i>";
                                } elseif ($docZip['extension'] == 'docx' || $docZip['extension'] == 'doc' || $docZip['extension'] == 'rtf') {
                                    $iconoLista = "<i class='bi bi-file-earmark-word-fill gm-file-word'></i>";
                                }
                                ?>

                                <button type="button"
                                    class="gm-doc-download-link"
                                    style="border:0; cursor:pointer; text-align:left;"
                                    onclick="gmAbrirDocZip('<?php echo $docZip['rutaWeb']; ?>','<?php echo $docZip['extension']; ?>','gmDocPreviewZip_<?php echo $id_doc; ?>')">
                                    <?php echo $iconoLista; ?>
                                    <span><?php echo htmlspecialchars($docZip['nombre']); ?></span>
                                </button>

                            <?php } ?>
                        </div>
                    <?php } ?>

                    <div class="gm-doc-adjunto-box">
                        <?php
                        if ($arch != '') {
                            if ($filename_nom != '' && $rutaWeb != '') {
                        ?>
                                <div id="gmDocPreviewZip_<?php echo $id_doc; ?>" class="gm-doc-preview">
                                    <?php if ($extension == 'pdf') { ?>
                                        <object class="gm-doc-pdfview" type="application/pdf" data="<?php echo $rutaWeb; ?>">
                                            <div class="gm-doc-empty">
                                                No se pudo mostrar el PDF. Usa la opción de descarga.
                                            </div>
                                        </object>

                                    <?php } elseif ($extension == 'docx') { ?>
                                        <div id="<?php echo $viewerId; ?>" class="gm-docx-render-area">
                                            <div class="gm-doc-empty">Cargando documento por páginas...</div>
                                        </div>

                                    <?php } elseif ($extension == 'doc') { ?>
                                        <div class="gm-doc-empty">
                                            Vista previa no disponible para archivos .doc.
                                            <br><br>
                                            Descarga el archivo para visualizarlo.
                                        </div>

                                    <?php } else { ?>
                                        <div class="gm-doc-empty">
                                            Vista previa no disponible para este tipo de archivo.
                                        </div>
                                    <?php } ?>
                                </div>

                                <?php if ($filename_nom != '' && $extension != '') { ?>
                                    <div class="gm-doc-file-badge">
                                        <?php echo $icono_dw; ?>
                                        <span><?php echo htmlspecialchars($filename_nom); ?></span>
                                    </div>
                                <?php } ?>
                        <?php
                            } elseif ($filename_nom != '' && $extension != '') {
                                echo '<div class="gm-doc-empty">El archivo existe, pero no fue posible generar la vista previa.</div>';
                                echo '<div class="gm-doc-download gm-doc-download-bottom">';
                                echo "<a class='gm-doc-download-link' target='_blank' href='../../../build/common/files/download_file.php?id_var=16&file=" . urlencode($arch) . "' title='Descargar Archivo'>" . $icono_dw . "<span>Descargar archivo</span></a>";
                                echo '</div>';
                            } else {
                                echo '<div class="gm-doc-empty">No se encontró ningún archivo o el documento se encuentra dañado.</div>';
                            }
                        } else {
                            echo '<div class="gm-doc-empty">No se encontró ningún archivo adjunto.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php

    $cont = ob_get_clean();

    $xres->addAssign('mrcDivModalDialogCont', 'innerHTML', $cont);
    $xres->addScript("mrcCrearDialogoModal('Información del Documento', '920', '560');");

    $xres->addScript("
        try{
            var modalYui = document.getElementById('mrcDivModalDialogYUI');
            var modalCont = document.getElementById('mrcDivModalDialogCont');

if(modalYui){
    modalYui.classList.add('gm-doc-yui-modal');
    modalYui.style.overflow = 'hidden';
    modalYui.style.maxHeight = '85vh';
    modalYui.style.height = '85vh';
}
    
if(modalCont){
    modalCont.style.overflowY = 'auto';
    modalCont.style.overflowX = 'hidden';
    modalCont.style.height = '100%';
}
            }
        }catch(e){}
    ");

    if ($extension == 'docx' && $rutaWeb != '') {
        $rutaPreviewJs = addslashes($rutaWeb);
        $viewerJsId = addslashes($viewerId);

        $xres->addScript("
            (function(){
                function gmLoadScript(src, callback){
                    var existing = document.querySelector('script[src=\"' + src + '\"]');
                    if(existing){
                        if(callback) callback();
                        return;
                    }
                    var s = document.createElement('script');
                    s.src = src;
                    s.onload = function(){ if(callback) callback(); };
                    s.onerror = function(){
                        var viewer = document.getElementById('{$viewerJsId}');
                        if(viewer){
                            viewer.innerHTML = '<div class=\"gm-doc-empty\">No se pudo cargar la librería del visor DOCX.</div>';
                        }
                    };
                    document.head.appendChild(s);
                }

                function gmRenderDocx(){
                    var viewer = document.getElementById('{$viewerJsId}');
                    if(!viewer){ return; }
                                                
                    fetch('{$rutaPreviewJs}', { cache: 'no-store' })
                        .then(function(res){
                            if(!res.ok){ throw new Error('No se pudo cargar el DOCX'); }
                            return res.blob();
                        })
                        .then(function(blob){
                            viewer.innerHTML = '';
                            return docx.renderAsync(blob, viewer, null, {
                                className: 'docx',
                                inWrapper: true,
                                breakPages: true,
                                ignoreWidth: false,
                                ignoreHeight: false,
                                ignoreFonts: false,
                                experimental: true,
                                useBase64URL: true,
                                renderHeaders: true,
                                renderFooters: true,
                                renderFootnotes: true
                            });
                        })
                        .then(function(){
                            setTimeout(function(){
                                try{
                                    var pages = viewer.querySelectorAll('.docx-wrapper > section.docx');
                                    var fallback = viewer.querySelector('.docx-wrapper .docx');
                                                
                                    if (pages && pages.length > 0) {
                                        pages.forEach(function(page){
                                            page.style.width = '794px';
                                            page.style.maxWidth = '794px';
                                            page.style.minHeight = '1123px';
                                            page.style.margin = '0 auto';
                                            page.style.padding = '70px 60px';
                                            page.style.boxSizing = 'border-box';
                                            page.style.background = '#fff';
                                            page.style.textAlign = 'left';
                                                
                                    var nodes = page.querySelectorAll('*');
                                    nodes.forEach(function(el){
                                        var computed = window.getComputedStyle(el);
                                        var fontSize = parseFloat(computed.fontSize);
                                                                    
                                        if (fontSize && fontSize > 0) {
                                            el.style.fontSize = (fontSize * 0.75) + 'px'; // 🔽 reduce 15%
                                        }
                                    });

                                            var nodes = page.querySelectorAll('p, div, span, td, th, li, h1, h2, h3, h4, h5, h6');
                                            nodes.forEach(function(el){
                                                el.style.textAlign = 'left';
                                                if (el.getAttribute('align') === 'center') {
                                                    el.removeAttribute('align');
                                                }
                                            });
                                        });
                                    } else if (fallback) {
                                        fallback.style.width = '794px';
                                        fallback.style.maxWidth = '794px';
                                        fallback.style.margin = '0 auto';
                                        fallback.style.padding = '70px 60px';
                                        fallback.style.boxSizing = 'border-box';
                                        fallback.style.background = '#fff';
                                        fallback.style.textAlign = 'left';

                                        var nodes = fallback.querySelectorAll('p, div, span, td, th, li, h1, h2, h3, h4, h5, h6');
                                        nodes.forEach(function(el){
                                            el.style.textAlign = 'left';
                                            if (el.getAttribute('align') === 'center') {
                                                el.removeAttribute('align');
                                            }
                                        });
                                    }
                                }catch(e){}
                            }, 200);
                        })
                        .catch(function(err){
                            viewer.innerHTML = '<div class=\"gm-doc-empty\">Error al visualizar el archivo DOCX.</div>';
                            try{ console.error(err); }catch(e){}
                        });
                }

                gmLoadScript('https://unpkg.com/jszip/dist/jszip.min.js', function(){
                    gmLoadScript('https://unpkg.com/docx-preview/dist/docx-preview.js', function(){
                        gmRenderDocx();
                    });
                });
            })();
        ");
    }
    $xres->addScript("
window.gmLoadScriptZip = function(src, callback){
    var existing = document.querySelector('script[src=\"' + src + '\"]');
    if(existing){
        if(callback) callback();
        return;
    }

    var s = document.createElement('script');
    s.src = src;
    s.onload = function(){ if(callback) callback(); };
    document.head.appendChild(s);
};

window.gmAbrirDocZip = function(ruta, extension, contenedorId){
    var contenedor = document.getElementById(contenedorId);
    if(!contenedor){ return; }

    extension = (extension || '').toLowerCase();

    // limpiar completamente visor anterior
    while(contenedor.firstChild){
        contenedor.removeChild(contenedor.firstChild);
    }

    contenedor.scrollTop = 0;

    if(extension === 'pdf'){
        var obj = document.createElement('object');
        obj.className = 'gm-doc-pdfview';
        obj.type = 'application/pdf';
        obj.data = ruta + '?v=' + Date.now();
        obj.innerHTML = '<div class=\"gm-doc-empty\">No se pudo mostrar el PDF.</div>';
        contenedor.appendChild(obj);
        return;
    }

    if(extension === 'docx'){
        var visorId = 'visorDocxZip_' + Date.now() + '_' + Math.floor(Math.random() * 9999);

        var visor = document.createElement('div');
        visor.id = visorId;
        visor.className = 'gm-docx-render-area';
        visor.innerHTML = '<div class=\"gm-doc-empty\">Cargando documento por páginas...</div>';

        contenedor.appendChild(visor);

        window.gmLoadScriptZip('https://unpkg.com/jszip/dist/jszip.min.js', function(){
            window.gmLoadScriptZip('https://unpkg.com/docx-preview/dist/docx-preview.js', function(){
                fetch(ruta + '?v=' + Date.now(), { cache: 'no-store' })
                    .then(function(res){
                        if(!res.ok){ throw new Error('No se pudo cargar el DOCX'); }
                        return res.blob();
                    })
                    .then(function(blob){
                        var viewer = document.getElementById(visorId);
                        if(!viewer){ return; }

                        while(viewer.firstChild){
                            viewer.removeChild(viewer.firstChild);
                        }

                        return docx.renderAsync(blob, viewer, null, {
                            className: 'docx',
                            inWrapper: true,
                            breakPages: true,
                            ignoreWidth: false,
                            ignoreHeight: false,
                            ignoreFonts: false,
                            experimental: true,
                            useBase64URL: true,
                            renderHeaders: true,
                            renderFooters: true,
                            renderFootnotes: true
                        });
                    })
                    .then(function(){
                        setTimeout(function(){
                            var viewer = document.getElementById(visorId);
                            if(!viewer){ return; }

                            var wrappers = viewer.querySelectorAll('.docx-wrapper');

                            for(var i = 1; i < wrappers.length; i++){
                                wrappers[i].remove();
                            }

                            var pages = viewer.querySelectorAll('.docx-wrapper > section.docx');

                            pages.forEach(function(page){
                                page.style.width = '794px';
                                page.style.maxWidth = '794px';
                                page.style.minHeight = '1123px';
                                page.style.margin = '0 auto';
                                page.style.padding = '70px 60px';
                                page.style.boxSizing = 'border-box';
                                page.style.background = '#fff';
                                page.style.textAlign = 'left';
                            });
                        }, 250);
                    })
                    .catch(function(){
                        contenedor.innerHTML = '<div class=\"gm-doc-empty\">Error al visualizar el archivo DOCX.</div>';
                    });
            });
        });

        return;
    }

    contenedor.innerHTML =
        '<div class=\"gm-doc-empty\">Vista previa no disponible para archivos .' + extension + '.</div>';
};
");


    return $xres->getXML();
}

function envmas_inicio_nuevo($id_doc_par = NULL, $td_par = NULL, $res_doc = NULL, $ppal = NULL, $capaDestino = 'mainCenter', $es_modal = 'no')
{
    unset($_SESSION['objeto']);

    require("../../../libraries/valida/biblio.php");
    require('../../../scripts/adoConex/adoConex.php');

    global $con;

    $xres = new xajaxResponse();

    /* =========================================================
       CONFIGURACIÓN INICIAL
    ========================================================= */
    $id_ser_sal_int  = serie::getSerieSalida($con, 'i');
    $id_ser_sal_ext  = serie::getSerieSalida($con, 'e');
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
    $id_fun = $usu[0]->getId_fun();

    $fun = funcionario::getWhere($con, $id_fun);
    $nom_fun = $fun[0]->getNom_fun() . ' ' . $fun[0]->getApe_fun() . ' - ' . $fun[0]->getEstructura()->getLabel();
    $n_fun   = $fun[0]->getNom_fun() . ' ' . $fun[0]->getApe_fun();

    $correo_firma = '';

    $sqlCorreoFirma = "
    SELECT 
        u.id_usu,
        u.nom_usu,
        u.id_fun,
        fc.usr_fun AS correo
    FROM usuario u
    LEFT JOIN funcionariocorreo fc
        ON u.id_fun = fc.id_fun
    WHERE u.id_usu = " . (int)$_SESSION['id_usu'] . "
    LIMIT 1
";

    $rsCorreoFirma = $con->Execute($sqlCorreoFirma);

    if ($rsCorreoFirma && !$rsCorreoFirma->EOF) {
        $correo_firma = trim((string)$rsCorreoFirma->fields['correo']);
    }


    $firma_activa = false;

    $sqlFirmaActiva = "SELECT activo FROM firma_active ORDER BY id LIMIT 1";
    $rsFirmaActiva = $con->Execute($sqlFirmaActiva);

    if ($rsFirmaActiva && !$rsFirmaActiva->EOF) {
        $valorFirma = $rsFirmaActiva->fields['activo'];

        $valorFirma = strtolower(trim((string)$valorFirma));

        $firma_activa = in_array($valorFirma, array('t', 'true', '1', 's', 'si', 'yes'), true);
    }

    $id_ds = $id_doc_par;

    /* =========================================================
       VARIABLES DE CONFIGURACIÓN
    ========================================================= */
    $var  = varsige::getWhere($con, 58);
    $var2 = varsige::getWhere($con, 113);

    $ocul = $var2[0]->getVal_var();

    if ($opt_env = json_decode($var[0]->getVal_var(), true)) {
        $exf = $opt_env['envio_funcionario']['visible'];
    } else {
        if ($var == NULL) $exf = 'n';
        else $exf = $var[0]->getVal_var();
    }

    ob_start();
    ?>
    <?php if ($capaDestino == 'mainCenter') { ?>
        <div class="envmas-header">

            <div class="envmas-header-left"
                onclick="
                var ppal='<?php echo $ppal ?>';
                if(ppal=='ppal'){ xajax_ppald_inicio(); }
                else{ mrcDestruirDialogoModal(); xajax_buzon_inicio('R'); }
            ">

                <button class="envmas-back-btn">
                    <i class="bi bi-arrow-left"></i>
                </button>

                <span class="envmas-back-text">Volver</span>
            </div>

            <div class="envmas-header-title">
                <i class="bi bi-send"></i>
                <span>Solicitud de env&iacute;o masivo de documentos</span>
            </div>

        </div>
    <?php } ?>

    <div class="mrcBordeComp envmas-wrap">
        <iframe id="ventana" name="ventana" style="display:none"></iframe>

        <form name="frm_envmas" id="frm_envmas" method="post" action="" enctype="multipart/form-data">

            <input type="hidden" id="list_doc" name="list_doc" />
            <input type="hidden" id="id_doc_par" name="id_doc_par" value="<?php echo $id_doc_par; ?>" />
            <input type="hidden" id="res_docp" name="res_docp" value="<?php echo $res_doc ? 'respuesta' : '&nbsp;'; ?>" />
            <input type="hidden" id="list_des_html" name="list_des_html" />
            <input type="hidden" id="rad_g" name="rad_g" value="n" />
            <input type="hidden" id="rad_cargar" name="rad_cargar" value="n" />
            <input type="hidden" id="ct_des" name="ct_des" value="" />
            <input type="hidden" id="btn_cerr" name="btn_cerr" value="n" />
            <input type="hidden" id="docgenenvi" name="docgenenvi" value="envigen" />
            <input type="hidden" id="accion_editar" name="accion_editar" value="n" />
            <input type="hidden" id="accion_nota" name="accion_nota" value="f" />
            <input type="hidden" id="id_fun2" name="id_fun2" />
            <input type="hidden" id="myHidden" />
            <input type="hidden" id="myInputCedula" />
            <input type="hidden" id="myInputCedula5" />
            <input type="hidden" id="myInputTipo5" />
            <input type="hidden" id="area_mensaje" name="area_mensaje" />

            <input type="hidden" id="res_id_docp" name="res_id_docp" value="<?php
                                                                            $res_docp = (array)json_decode($res_doc);
                                                                            if (is_array($res_docp)) echo $res_doc ? $res_docp['id_doc'] : '&nbsp;';
                                                                            else echo '&nbsp;';
                                                                            ?>" />
            <!--Firma -->
            <input type="hidden" id="firma_modo" name="firma_modo" value="dibujar" />
            <input type="hidden" id="firma_data" name="firma_data" value="" />
            <input type="hidden" id="firma_limpia" name="firma_limpia" value="s" />

            <input type="hidden" id="firma_activa" name="firma_activa" value="<?php echo $firma_activa ? 's' : 'n'; ?>" />
            <input type="hidden" id="usar_firma_digital" name="usar_firma_digital" value="n" />
            <input type="hidden" id="firma_nombre" name="firma_nombre" value="<?php echo htmlspecialchars($n_fun, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="hidden" id="firma_correo" name="firma_correo" value="<?php echo htmlspecialchars($correo_firma, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="hidden" id="firma_ciudad" name="firma_ciudad" value="Pasto" />

            <div class="envmas-grid">

                <div class="envmas-col">

                    <div class="envmas-card">
                        <div class="envmas-title">1. Tipo de documento</div>

                        <table class="envmas-table">
                            <tr>
                                <td class="envmas-label">Tipo de Documento</td>
                                <td>
                                    <div style="display:flex; gap:14px; flex-wrap:wrap;">
                                        <label class="envmas-chip">
                                            <input name="tip_doc" type="radio" class="inputRadioCss" value="i" onclick="envmasCambiarTipoDocumento('i')" />
                                            Interno
                                        </label>

                                        <label class="envmas-chip">
                                            <input name="tip_doc" type="radio" class="inputRadioCss" value="e" onclick="envmasCambiarTipoDocumento('e')" />
                                            Externo
                                        </label>

                                        <label class="envmas-chip">
                                            <input
                                                name="tip_doc"
                                                id="tip_doc_n"
                                                type="radio"
                                                class="inputRadioCss"
                                                value="n"
                                                onclick="try{ envmasCambiarTipoDocumento('n'); }catch(e){ alert(e.message); console.error(e); }" />
                                            Nota Interna
                                        </label>
                                    </div>
                                </td>
                            </tr>

                            <?php if ($exf == 's') { ?>
                                <tr>
                                    <td class="envmas-label">Tipo Env&iacute;o</td>
                                    <td>
                                        <div class="envmas-small-note">Configuraci&oacute;n visible seg&uacute;n par&aacute;metros del sistema.</div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>

                    <div class="envmas-card">
                        <div class="envmas-title">2. Participantes</div>

                        <table class="envmas-table envmas-participantes-table">
                            <tr>
                                <td class="envmas-label">Remitente</td>
                                <td>
                                    <div id="autoCompleteRemitente" class="yui-ac envmas-searchbox envmas-searchbox-remitente">
                                        <input
                                            id="myInput2"
                                            type="text"
                                            autocomplete="off"
                                            value="<?php echo $n_fun; ?>"
                                            class="inputTextCss envmas-input-compact">
                                        <div id="myContainer2" class="envmas-ac-container" style="z-index:200000;"></div>
                                    </div>
                                    <input id="id_fun1" name="id_fun1" type="hidden" value="<?php echo trim($id_fun); ?>">
                                </td>
                            </tr>

                            <tr>
                                <td class="envmas-label envmas-label-top">Destinatario</td>
                                <td>
                                    <div class="envmas-dest-panel">
                                        <div class="envmas-dest-toolbar">
                                            <div class="envmas-dest-search">
                                                <div id="interno" style="display:none;">
                                                    <div id="autoCompleteInterno" class="yui-ac envmas-searchbox">
                                                        <input
                                                            id="myInput"
                                                            type="text"
                                                            autocomplete="off"
                                                            class="inputTextCss envmas-search-input envmas-input-compact"
                                                            placeholder="Buscar destinatario interno">
                                                        <div id="myContainer" class="envmas-ac-container"></div>
                                                    </div>
                                                </div>
                                                <div id="externo">
                                                    <div id="autoCompleteExterno" class="yui-ac envmas-searchbox">
                                                        <input
                                                            id="myInput5"
                                                            type="text"
                                                            autocomplete="off"
                                                            class="inputTextCss envmas-search-input envmas-input-compact"
                                                            placeholder="Buscar destinatario externo">
                                                        <div id="myContainer5" class="envmas-ac-container"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="envmas-dest-actions">
                                                <input
                                                    name="btn_ad"
                                                    id="btn_ad"
                                                    type="button"
                                                    value="Agregar destinatario"
                                                    class="inputButtonCss"
                                                    onclick="envmasAgregarDestinatario();" />

                                                <input
                                                    name="numdoc2"
                                                    type="button"
                                                    class="inputButtonCss"
                                                    id="numdoc2"
                                                    value="Nuevo externo"
                                                    style="display:none;"
                                                    onclick="xajax_dest_iniAddDestinatario('', '', '', 'xajax_solenv_listaDestinatarios(\'lista_per\',\'<?php echo isset($id_rad) ? $id_rad : ''; ?>\',\'%%%\');','s');" />

                                                <div id="div_img" class="envmas-masivo-tools" style="display:none;">
                                                    <input
                                                        name="numdoc22"
                                                        type="button"
                                                        class="inputButtonCss"
                                                        id="numdoc22"
                                                        value="Carga masiva"
                                                        style="display:none;"
                                                        onclick="xajax_dest_iniAddDestinatariomas('', '', '', 'xajax_solenv_listaDestinatarios(\'lista_per\',\'<?php echo isset($id_rad) ? $id_rad : ''; ?>\',\'%%%\');','s');" />

                                                    <button
                                                        type="button"
                                                        class="envmas-icon-btn"
                                                        title="Descargar plantilla"
                                                        onclick="window.open('../../../build/documentacion/envio_docs/lista_destinatarios.php?l');">
                                                        <img
                                                            src="../../../images/icons/fam/page_white_put.png"
                                                            id="img_desc"
                                                            alt="Descargar">
                                                    </button>

                                                    <button
                                                        type="button"
                                                        class="envmas-icon-btn"
                                                        title="Cargar archivo con los destinatarios"
                                                        onclick="xajax_carga_masiva_dest();">
                                                        <img
                                                            src="../../../images/icons/fam/page_white_get.png"
                                                            id="img_carga"
                                                            alt="Cargar">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="envmas-dest-list-wrap">
                                            <div id="destinatarios_header" class="envmas-list-header">
                                                <table>
                                                    <tr>
                                                        <th width="80">Tipo</th>
                                                        <th width="30%">Nombre</th>
                                                        <th width="30%">Detalle</th>
                                                        <th width="20%">T&iacute;tulo</th>
                                                        <th id="cc_para_th" style="display:none;" width="10%">Para/C.C</th>
                                                        <th width="10%"></th>
                                                    </tr>
                                                </table>
                                            </div>

                                            <div
                                                id="destinatarios"
                                                class="envmas-list-body envmas-destinatarios-body"
                                                onclick="
                                var des_num=this.getElementsByTagName('table').length;
                                if(document.getElementsByName('tip_doc')[1].checked){
                                    document.getElementsByName('radicado')[0].checked=true;
                                }else{
                                    document.getElementById('nradicado2').setAttribute('hidden','true');
                                    document.getElementsByName('radicado')[1].checked=true;
                                    document.getElementById('rad_mas').setAttribute('hidden','true');
                                    document.getElementById('rad_sin').removeAttribute('hidden');
                                }
                            ">
                                            </div>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="envmas-card">
                        <div class="envmas-title">4. Radicaci&oacute;n</div>

                        <table class="envmas-table">
                            <tr id="nradicado2" hidden="true">
                                <td class="envmas-label">Radicado por documento</td>
                                <td>
                                    <label style="margin-right:20px;">
                                        <input name="radicado" type="radio" class="inputRadioCss" value="s"
                                            onclick="document.getElementById('rad_sin').setAttribute('hidden','true');document.getElementById('rad_mas').removeAttribute('hidden');" />
                                        S&iacute;
                                    </label>

                                    <label>
                                        <input name="radicado" type="radio" class="inputRadioCss" value="n" checked="checked"
                                            onclick="document.getElementById('rad_mas').setAttribute('hidden','true');document.getElementById('rad_sin').removeAttribute('hidden');" />
                                        No
                                    </label>
                                </td>
                            </tr>

                            <tr id="anx_fisico">
                                <td class="envmas-label">Tiene anexos en f&iacute;sico</td>
                                <td>
                                    <label style="margin-right:20px;">
                                        <input name="rfis" type="radio" class="inputRadioCss" value="s" />
                                        S&iacute;
                                    </label>
                                    <label>
                                        <input name="rfis" type="radio" class="inputRadioCss" value="n" checked="checked" />
                                        No
                                    </label>
                                </td>
                            </tr>

                            <tr id="doc_corr">
                                <td class="envmas-label">Entrega unidad de correspondencia</td>
                                <td>
                                    <label style="margin-right:20px;">
                                        <input name="tent" type="radio" class="inputRadioCss" value="d" checked="checked"
                                            onclick="ocultar('perso'); mostrar('anx_fisico');" />
                                        S&iacute;
                                    </label>

                                    <label>
                                        <input name="tent" type="radio" class="inputRadioCss" value="p"
                                            onclick="mostrar('perso'); ocultar('anx_fisico'); document.getElementsByName('rfis')[1].checked=true;" />
                                        No
                                    </label>
                                </td>
                            </tr>

                            <tr>
                                <td></td>
                                <td>
                                    <div id="perso" class="envmas-box" style="display:none;">
                                        <p>Si el documento es interno, usted es responsable de la entrega f&iacute;sica y SIGE solo har&aacute; la entrega digital.</p>
                                        <p>Si el documento es externo, el sistema dejar&aacute; como entregado al destinatario el documento.</p>
                                    </div>
                                </td>
                            </tr>

                            <tr id="nota_titulo">
                                <td class="envmas-label">Observaciones correspondencia</td>
                                <td>
                                    <textarea name="nota_dse" rows="4" class="inputTextCss" id="nota_dse" style="width:500px; height: 10px;"
                                        onblur="this.value=trim(this.value);"
                                        onkeypress="<?php echo ($expr['descripcion']); ?>"></textarea>
                                </td>
                            </tr>

                            <tr id="titulo_radicado">
                                <td colspan="2" style="text-align:center;">
                                    <span class="grupo">Generar radicado</span>
                                </td>
                            </tr>

                            <tr id="fila_gen_rad_btn">
                                <td class="envmas-label">Generar nuevo radicado</td>
                                <td>
                                    <input type="button" class="inputButtonCss" value="Generar nuevo radicado" id="gen_rad_btn"
                                        onclick="envmasGenerarRadicado();" />

                                    <?php if ($ppal == 'ppal') { ?>
                                        <input type="button" class="inputButtonCss" value="Imprimir sticker" id="imp_stiker_btn"
                                            title="Imprimir sticker" style="visibility:hidden"
                                            onclick="var vec_st=get('list_doc').value; xajax_doc_ent_ingDocRec2('',vec_st,'e');" />
                                    <?php } ?>
                                </td>
                            </tr>

                            <tr id="rad_sin">
                                <td class="envmas-label">N&uacute;mero de radicado</td>
                                <td><span id="radicados_single" style="font-size:14px;"></span></td>
                            </tr>

                            <tr id="rad_mas" hidden="true">
                                <td class="envmas-label">Radicados</td>
                                <td>
                                    <div class="envmas-rad-table" id="radicados" style="width:100%; height:85px; overflow:auto;">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Destinatario</th>
                                                    <th>Radicado</th>
                                                </tr>
                                            </thead>
                                            <tbody id="radicados_all"></tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>

                <div class="envmas-col">

                    <div class="envmas-card">
                        <div class="envmas-title">3. Informaci&oacute;n del documento</div>

                        <table class="envmas-table">
                            <?php if ($ocul == 'SI') { ?>
                                <tr>
                                    <td class="envmas-label">Cuerpo del documento</td>
                                    <td>
                                        <textarea name="texto_doc" rows="5" id="texto_doc" style="width:380px;"></textarea>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr <?php if ($opt_env['asunto']['visible'] && $opt_env['asunto']['visible'] == 'n') echo 'style="display:none;"'; ?>>
                                <td class="envmas-label">Asunto</td>
                                <td>
                                    <input name="asu" type="text" id="asu" class="inputTextCss" style="width:400px;"
                                        onblur="this.value=trim(this.value); envmasActualizarEstado();"
                                        onkeypress="<?php echo ($expr['num_car']); ?>">
                                </td>
                            </tr>

                            <?php if ($opt_env['observ']['visible'] && $opt_env['observ']['visible'] == 'n') { ?>
                                <input name="id_ser" id="id_ser" type="hidden" value="" />
                                <input name="id_sub" id="id_sub" type="hidden" value="" />
                            <?php } else { ?>
                                <tr id="filSerie">
                                    <td class="envmas-label">Serie</td>
                                    <td>
                                        <select name="id_ser" id="id_ser" class="selectCss" style="width:400px;"
                                            onchange="if(this.value!='-1'){xajax_solenv_cargaSubserie(this.value)}else{get('id_sub').options.length=0;} envmasActualizarEstado();">
                                        </select>
                                    </td>
                                </tr>

                                <tr id="filSubserie">
                                    <td class="envmas-label">Subserie</td>
                                    <td>
                                        <select name="id_sub" id="id_sub" class="selectCss" style="width:400px;" onchange="envmasActualizarEstado();"></select>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr id="tr_folio" <?php if ($opt_env['nfolios']['visible'] && $opt_env['nfolios']['visible'] == 'n') echo 'style="display:none;"'; ?>>
                                <td class="envmas-label">No. Folios</td>
                                <td>
                                    <input name="nfol" type="text" class="inputTextCss" id="nfol" style="width:400px;"
                                        onblur="this.value=trim(this.value); envmasActualizarEstado();"
                                        onkeypress="<?php echo ($expr['numero']); ?>" value="0" maxlength="3">
                                </td>
                            </tr>

                            <tr id="filmsn" style="display:none;">
                                <td colspan="2">
                                    <div class="envmas-label" style="margin-bottom:6px;">Mensaje</div>
                                    <div id="div_mensaje"></div>
                                </td>
                            </tr>

                            <tr id="dg_doc" <?php if ($opt_env['descripciong']['visible'] && $opt_env['descripciong']['visible'] == 'n') echo 'style="display:none;"'; ?>>

                                <!-- TÍTULO ARRIBA -->
                                <td colspan="2" class="envmas-label" style="padding-bottom:5px;">
                                    Descripci&oacute;n general
                                </td>
                            </tr>

                            <tr id="fila_editor_cuerpo">
                                <!-- EDITOR ABAJO -->
                                <td colspan="2">
                                    <div class="envmas-note-box" style="width:100%;">

                                        <div id="toolbar_cuerpo" class="envmas-editor-toolbar">

                                            <span class="ql-formats">
                                                <select class="ql-font"></select>
                                                <select class="ql-size">
                                                    <option value="small"></option>
                                                    <option selected></option>
                                                    <option value="large"></option>
                                                    <option value="huge"></option>
                                                </select>
                                            </span>

                                            <span class="ql-formats">
                                                <select class="ql-header">
                                                    <option value="1"></option>
                                                    <option value="2"></option>
                                                    <option value="3"></option>
                                                    <option value="4"></option>
                                                    <option value="5"></option>
                                                    <option value="6"></option>
                                                    <option selected></option>
                                                </select>
                                            </span>

                                            <span class="ql-formats">
                                                <button class="ql-bold"></button>
                                                <button class="ql-italic"></button>
                                                <button class="ql-underline"></button>
                                                <button class="ql-strike"></button>
                                            </span>

                                            <span class="ql-formats">
                                                <button class="ql-blockquote"></button>
                                                <button class="ql-code-block"></button>
                                            </span>

                                            <span class="ql-formats">
                                                <button class="ql-list" value="ordered"></button>
                                                <button class="ql-list" value="bullet"></button>
                                            </span>

                                            <span class="ql-formats">
                                                <button class="ql-script" value="sub"></button>
                                                <button class="ql-script" value="super"></button>
                                            </span>

                                            <span class="ql-formats">
                                                <button class="ql-indent" value="-1"></button>
                                                <button class="ql-indent" value="+1"></button>
                                            </span>

                                            <span class="ql-formats">
                                                <select class="ql-align"></select>
                                            </span>

                                            <span class="ql-formats">
                                                <select class="ql-color"></select>
                                                <select class="ql-background"></select>
                                            </span>

                                            <span class="ql-formats">
                                                <button class="ql-link"></button>
                                                <button class="ql-image"></button>
                                                <button class="ql-video"></button>
                                            </span>

                                            <span class="ql-formats">
                                                <button class="ql-clean"></button>
                                            </span>

                                        </div>

                                        <div id="editor_cuerpo" class="envmas-editor-content"
                                            style="min-height:220px; background:#fff; border:1px solid #ccc;">
                                        </div>
                                    </div>

                                    <input type="hidden" name="texto_doc" id="texto_doc">
                                    <input type="hidden" name="obs" id="obs">
                                </td>
                            </tr>

                            <tr id="des_anex" <?php if ($opt_env['anexos']['visible'] && $opt_env['anexos']['visible'] == 'n') echo 'style="display:none;"'; ?>>
                                <td class="envmas-label">Descripci&oacute;n de anexos</td>
                                <td>
                                    <textarea name="dane" rows="3" class="textAreaCss" id="dane" style="width:400px;"
                                        onblur="this.value=trim(this.value);"
                                        onkeypress="<?php echo ($expr['num_car']); ?>"></textarea>
                                </td>
                            </tr>

                            <tr>
                                <td class="envmas-label">Respuesta a documento</td>
                                <td>
                                    <input name="es_rta" type="checkbox" id="es_rta" value="s"
                                        <?php if ($res_doc != NULL || (isset($cod) && $cod != '')) echo 'checked="checked"'; ?> />

                                    <span class="etiqueta">Radicado que se responde</span>

                                    <input name="numdoc" type="text" id="numdoc" class="inputTextCss"
                                        style="width:400px;"
                                        placeholder="Digite o busque el radicado que se responde"
                                        onblur="this.value=trim(this.value); if(this.value!=''){ get('es_rta').checked=true; }"
                                        onkeypress="<?php echo ($expr['num_car']); ?>"
                                        value="<?php echo isset($cod) ? $cod : ''; ?>">

                                    <input name="btn_bdoc" type="button" id="btn_bdoc" value="Buscar" class="inputButtonCss"
                                        onclick="xajax_solenv_documentosrecibidos('i',get('asu').value)" />
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>

                <?php if ($firma_activa) { ?>
                    <div class="envmas-card " id="firma_wrap">
                        <div class="envmas-title">5. Firma del usuario</div>

                        <table class="envmas-table">
                            <tr>
                                <td class="envmas-label">Modo de firma</td>
                                <td>
                                    <div style="display:flex; gap:18px; flex-wrap:wrap; align-items:center;">
                                        <label class="envmas-chip">
                                            <input type="radio" name="firma_tipo_selector" value="dibujar" checked
                                                onclick="envmasCambiarModoFirma('dibujar')">
                                            Dibujar firma
                                        </label>

                                        <label class="envmas-chip">
                                            <input type="radio" name="firma_tipo_selector" value="subir"
                                                onclick="envmasCambiarModoFirma('subir')">
                                            Subir imagen
                                        </label>
                                    </div>
                                </td>
                            </tr>

                            <tr id="firma_draw_row">
                                <td class="envmas-label" style="vertical-align:top;">Dibujar firma</td>
                                <td>
                                    <div class="envmas-firma-box">
                                        <canvas id="firma_canvas" width="100" height="100"
                                            style="border:2px dashed #bfc7d1; border-radius:10px; background:#fff; display:block; width:700px; max-width:100%; height:220px; cursor:crosshair;">
                                        </canvas>

                                        <div class="envmas-firma-actions" style="margin-top:10px;">
                                            <input type="button" class="inputButtonCss" value="Limpiar firma"
                                                onclick="envmasLimpiarFirma();" />
                                        </div>

                                        <div class="envmas-small-note" style="margin-top:6px;">
                                            Dibuje su firma dentro del recuadro.
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr id="firma_upload_row" style="display:none;">
                                <td class="envmas-label" style="vertical-align:top;">Subir firma</td>
                                <td>
                                    <div class="envmas-firma-upload-wrap">
                                        <input type="file"
                                            name="firma_archivo"
                                            id="firma_archivo"
                                            accept=".png,.jpg,.jpeg,.webp" />

                                        <div class="envmas-small-note" style="margin-top:8px;">
                                            Formatos permitidos: PNG, JPG, JPEG o WEBP.
                                        </div>

                                        <div id="firma_preview_wrap" style="display:none; margin-top:12px; position:relative; display:inline-block;">

                                            <button type="button"
                                                onclick="envmasEliminarFirmaSubida();"
                                                style="
                                                    position:absolute;
                                                    top:-8px;
                                                    right:-8px;
                                                    background:#ef4444;
                                                    color:#fff;
                                                    border:none;
                                                    border-radius:50%;
                                                    width:24px;
                                                    height:24px;
                                                    cursor:pointer;
                                                    font-weight:bold;
                                                    font-size:14px;
                                                    line-height:20px;
                                                ">
                                                X
                                            </button>

                                            <img id="firma_preview_img"
                                                src=""
                                                alt="Vista previa de firma"
                                                style="max-height:120px; max-width:320px; border:1px solid #dbe3ee; border-radius:10px; padding:8px; background:#fff;">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php } ?>
                <div class="envmas-card ">
                    <div class="envmas-title">
                        <?php echo $firma_activa ? '6. Plantilla y adjuntos' : '5. Plantilla y adjuntos'; ?>
                    </div>

                    <table class="envmas-table">
                        <tr id="plantilla_adjunto_wrap">

                            <!-- LABEL -->
                            <td class="envmas-label" style="vertical-align: top; padding-top: 6px;">
                                Documento base
                            </td>

                            <!-- CONTENIDO -->
                            <td>

                                <!-- BOTÓN -->
                                <div style="margin-bottom:6px;">
                                    <input
                                        id="btn_plantilla"
                                        type="button"
                                        class="inputButtonCss"
                                        value="Descargar plantilla"
                                        onclick="envmasDescargarPlantilla();" />
                                </div>

                                <!-- TABLA UNIFICADA -->
                                <div class="envmas-file-table-wrap"
                                    style="border:1px solid #dcdcdc; border-radius:4px; overflow:hidden;">

                                    <table cellspacing="1" cellpadding="4" id="tb_mas_arch" style="width:100%;">

                                        <!-- HEADER -->
                                        <tr style="background:#f5f5f5;">
                                            <th style="width:30px;">&nbsp;</th>
                                            <th>Archivo adjunto</th>
                                            <th style="width:40px;">&nbsp;</th>
                                        </tr>

                                        <!-- INPUT PRINCIPAL -->
                                        <tr>
                                            <td style="text-align:center;">
                                                <i class="bi bi-paperclip"></i>
                                            </td>

                                            <td>
                                                <input
                                                    type="file"
                                                    name="file_a0"
                                                    id="file_a0"
                                                    style="width:100%;" />

                                                <input
                                                    type="hidden"
                                                    name="id_ds"
                                                    value="<?php echo $id_ds ?>" />
                                            </td>

                                            <td style="text-align:center;">
                                                <input
                                                    type="button"
                                                    id="btn_mas_arch"
                                                    name="btn_mas_arch"
                                                    value="+"
                                                    title="Agregar archivo"
                                                    class="inputButtonCss"
                                                    style="width:28px; height:26px; padding:0;"
                                                    onclick="xajax_add_mas_arch()" />
                                            </td>
                                        </tr>

                                    </table>
                                </div>

                            </td>
                        </tr>
                    </table>


                    <div class="envmas-card envmas-card--full">
                        <div class="envmas-actions">
                            <input type="button" id="btn_finalizar" class="inputButtonCss" value="Finalizar" onclick="envmasFinalizar(this);" />
                            <input type="button" id="btn_continuar_tarde" style="display:none" name="btnlist_dest"
                                value="Continuar m&aacute;s tarde" class="inputButtonCss"
                                onclick="envmasGuardarBorrador(this);" />
                        </div>
                    </div>

                </div>

                <div id="envmas_modal_espera" class="envmas-modal-espera">
                    <div class="envmas-modal-espera-box">
                        <div class="envmas-modal-espera-loader"></div>
                        <div class="envmas-modal-espera-title">Espere...</div>
                        <div class="envmas-modal-espera-text">Procesando la informaci&oacute;n del documento.</div>
                    </div>
                </div>

        </form>

        <?php if ($firma_activa) { ?>
            <div id="envmas_modal_firma" class="envmas-modal-espera" style="display:none;">
                <div class="envmas-modal-espera-box" style="max-width:560px; text-align:left;">
                    <div class="envmas-modal-espera-title">Firma digital</div>

                    <div class="envmas-modal-espera-text" style="margin-bottom:14px;">
                        Quieres enviar este archivo con firma digital?
                    </div>

                    <div style="background:#f8fafc; border:1px solid #dbe3ee; border-radius:10px; padding:14px; margin-bottom:16px;">
                        <div style="margin-bottom:10px;">
                            <strong>Firmante:</strong>
                            <span id="envmas_modal_firma_nombre"><?php echo htmlspecialchars($n_fun, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>

                        <div style="margin-bottom:8px;">
                            <strong>Correo registrado:</strong>
                        </div>

                        <div id="envmas_correo_existente_wrap" style="<?php echo $correo_firma !== '' ? '' : 'display:none;'; ?>">
                            <div style="padding:10px 12px; background:#ffffff; border:1px solid #dbe3ee; border-radius:8px; color:#0f172a;">
                                <span id="envmas_correo_existente"><?php echo htmlspecialchars($correo_firma, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="envmas-small-note" style="margin-top:6px;">
                                Este correo se usar&aacute; para firmar el documento.
                            </div>
                        </div>

                        <div id="envmas_correo_input_wrap" style="<?php echo $correo_firma === '' ? '' : 'display:none;'; ?>">
                            <input
                                type="text"
                                id="envmas_correo_manual"
                                class="inputTextCss"
                                style="width:100%;"
                                placeholder="Digite el correo del firmante"
                                value="<?php echo htmlspecialchars($correo_firma, ENT_QUOTES, 'UTF-8'); ?>" />
                            <div class="envmas-small-note" style="margin-top:6px;">
                                Como este usuario no tiene correo registrado, debes ingresarlo para continuar con la firma digital.
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; justify-content:center;">
                        <input type="button" class="inputButtonCss" value="No" onclick="envmasConfirmarFirmaDigital('n');" />
                        <input type="button" class="inputButtonCss" value="Si" onclick="envmasConfirmarFirmaDigital('s');" />
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php
    $cont = ob_get_clean();

    $xres->addAssign($capaDestino, 'innerHTML', $cont);

    if ($es_modal == 'si') {
        $xres->addScript("mrcCrearDialogoModal('Responder', '900', '500');");
        $xres->addScript("document.getElementById('mrcDivModalDialogYUI').style.overflow='auto';");
    }

    $xres->addScript("xajax_solenv_funcionario('remitente','1','','" . $id_fun . "','" . $nom_fun . "');");
    $xres->addScript("xajax_solenv_cargarSerie();");

    ob_start();
    ?>
    (function () {


    window.envmasEliminarFirmaSubida = function () {

    if (get('firma_archivo')) {
    get('firma_archivo').value = '';
    }

    if (get('firma_preview_img')) {
    get('firma_preview_img').src = '';
    }

    if (get('firma_preview_wrap')) {
    get('firma_preview_wrap').style.display = 'none';
    }

    };

    window.envmasFirmaCanvas = null;
    window.envmasFirmaCtx = null;
    window.envmasFirmaDibujando = false;
    window.envmasFirmaTieneTrazos = false;
    window.envmasUltimoPuntoFirma = null;

    window.envmasCambiarModoFirma = function (modo) {
    if (get('firma_modo')) {
    get('firma_modo').value = modo;
    }

    if (modo === 'dibujar') {
    if (get('firma_archivo')) get('firma_archivo').value = '';
    if (get('firma_preview_img')) get('firma_preview_img').src = '';
    if (get('firma_preview_wrap')) get('firma_preview_wrap').style.display = 'none';

    mostrar('firma_draw_row');
    ocultar('firma_upload_row');

    setTimeout(function () {
    envmasInicializarFirma();
    }, 100);
    } else {
    if (get('firma_data')) get('firma_data').value = '';
    if (get('firma_limpia')) get('firma_limpia').value = 's';

    ocultar('firma_draw_row');
    mostrar('firma_upload_row');
    }
    };


    window.envmasPrepararCanvasFirma = function () {
    var canvas = get('firma_canvas');
    if (!canvas) return false;

    var rect = canvas.getBoundingClientRect();
    var anchoVisual = Math.max(700, Math.round(rect.width || 700));
    var altoVisual = 220;

    canvas.width = anchoVisual;
    canvas.height = altoVisual;

    window.envmasFirmaCanvas = canvas;
    window.envmasFirmaCtx = canvas.getContext('2d');

    if (!window.envmasFirmaCtx) return false;

    window.envmasFirmaCtx.fillStyle = '#ffffff';
    window.envmasFirmaCtx.fillRect(0, 0, canvas.width, canvas.height);
    window.envmasFirmaCtx.lineWidth = 2.5;
    window.envmasFirmaCtx.lineCap = 'round';
    window.envmasFirmaCtx.lineJoin = 'round';
    window.envmasFirmaCtx.strokeStyle = '#111111';

    return true;
    };

    window.envmasLimpiarFirma = function () {
    if (!window.envmasFirmaCanvas || !window.envmasFirmaCtx) {
    if (!envmasPrepararCanvasFirma()) return;
    }

    window.envmasFirmaCtx.clearRect(0, 0, window.envmasFirmaCanvas.width, window.envmasFirmaCanvas.height);
    window.envmasFirmaCtx.fillStyle = '#ffffff';
    window.envmasFirmaCtx.fillRect(0, 0, window.envmasFirmaCanvas.width, window.envmasFirmaCanvas.height);

    window.envmasFirmaTieneTrazos = false;
    window.envmasUltimoPuntoFirma = null;

    if (get('firma_data')) get('firma_data').value = '';
    if (get('firma_limpia')) get('firma_limpia').value = 's';

    if (get('firma_archivo')) get('firma_archivo').value = '';
    if (get('firma_preview_img')) get('firma_preview_img').src = '';
    if (get('firma_preview_wrap')) get('firma_preview_wrap').style.display = 'none';
    };

    window.envmasGuardarFirmaCanvas = function () {
    if (!window.envmasFirmaCanvas) return;

    if (window.envmasFirmaTieneTrazos) {
    if (get('firma_data')) {
    get('firma_data').value = window.envmasFirmaCanvas.toDataURL('image/png');
    }
    if (get('firma_limpia')) {
    get('firma_limpia').value = 'n';
    }
    } else {
    if (get('firma_data')) get('firma_data').value = '';
    if (get('firma_limpia')) get('firma_limpia').value = 's';
    }
    };

    window.envmasObtenerPosicionFirma = function (e) {
    var canvas = window.envmasFirmaCanvas;
    var rect = canvas.getBoundingClientRect();

    var clientX = 0;
    var clientY = 0;

    if (e.touches && e.touches.length > 0) {
    clientX = e.touches[0].clientX;
    clientY = e.touches[0].clientY;
    } else if (e.changedTouches && e.changedTouches.length > 0) {
    clientX = e.changedTouches[0].clientX;
    clientY = e.changedTouches[0].clientY;
    } else {
    clientX = e.clientX;
    clientY = e.clientY;
    }

    return {
    x: (clientX - rect.left) * (canvas.width / rect.width),
    y: (clientY - rect.top) * (canvas.height / rect.height)
    };
    };

    window.envmasIniciarDibujoFirma = function (e) {
    if (!window.envmasFirmaCtx) return;

    e.preventDefault();

    var pos = envmasObtenerPosicionFirma(e);
    window.envmasFirmaDibujando = true;
    window.envmasFirmaTieneTrazos = true;
    window.envmasUltimoPuntoFirma = pos;

    window.envmasFirmaCtx.beginPath();
    window.envmasFirmaCtx.moveTo(pos.x, pos.y);
    };

    window.envmasMoverFirma = function (e) {
    if (!window.envmasFirmaDibujando || !window.envmasFirmaCtx) return;

    e.preventDefault();

    var pos = envmasObtenerPosicionFirma(e);

    window.envmasFirmaCtx.lineTo(pos.x, pos.y);
    window.envmasFirmaCtx.stroke();

    window.envmasUltimoPuntoFirma = pos;
    };

    window.envmasDetenerDibujoFirma = function (e) {
    if (!window.envmasFirmaDibujando) return;

    if (e) e.preventDefault();

    window.envmasFirmaDibujando = false;
    window.envmasUltimoPuntoFirma = null;
    envmasGuardarFirmaCanvas();
    };

    window.envmasInicializarFirma = function () {
    var canvas = get('firma_canvas');
    if (!canvas) return;

    if (!envmasPrepararCanvasFirma()) return;

    canvas.onmousedown = envmasIniciarDibujoFirma;
    canvas.onmousemove = envmasMoverFirma;
    canvas.onmouseup = envmasDetenerDibujoFirma;
    canvas.onmouseleave = envmasDetenerDibujoFirma;

    canvas.ontouchstart = envmasIniciarDibujoFirma;
    canvas.ontouchmove = envmasMoverFirma;
    canvas.ontouchend = envmasDetenerDibujoFirma;

    var inputFirma = get('firma_archivo');
    if (inputFirma) {
    inputFirma.onchange = function () {
    var file = this.files && this.files[0] ? this.files[0] : null;

    if (!file) {
    if (get('firma_preview_wrap')) get('firma_preview_wrap').style.display = 'none';
    if (get('firma_preview_img')) get('firma_preview_img').src = '';
    return;
    }

    var tiposPermitidos = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/bmp'];
    if (tiposPermitidos.indexOf(file.type) === -1) {
    alert('La firma debe ser una imagen PNG, JPG, JPEG o WEBP.');
    this.value = '';
    if (get('firma_preview_wrap')) get('firma_preview_wrap').style.display = 'none';
    if (get('firma_preview_img')) get('firma_preview_img').src = '';
    return;
    }

    var reader = new FileReader();
    reader.onload = function (ev) {
    if (get('firma_preview_img')) get('firma_preview_img').src = ev.target.result;
    if (get('firma_preview_wrap')) get('firma_preview_wrap').style.display = 'block';
    };
    reader.readAsDataURL(file);
    };
    }
    };

    window.envmasValidarFirma = function () {
    var firmaActiva = get('firma_activa') && get('firma_activa').value === 's';

    if (!firmaActiva) {
    return true;
    }

    var modo = get('firma_modo') ? get('firma_modo').value : 'dibujar';

    if (modo === 'dibujar') {
    envmasGuardarFirmaCanvas();

    if (!get('firma_data') || get('firma_data').value === '' || get('firma_limpia').value === 's') {
    mrcCrearDialogoInfo('Debe dibujar la firma del usuario.', '');
    return false;
    }
    } else {
    if (!get('firma_archivo') || !get('firma_archivo').files || get('firma_archivo').files.length === 0) {
    mrcCrearDialogoInfo('Debe subir la imagen de la firma.', '');
    return false;
    }
    }

    return true;
    };


    window.mostrar = window.mostrar || function (id) {
    var el = get(id);
    if (el) {
    if (el.tagName && el.tagName.toLowerCase() === 'tr') {
    el.style.display = 'table-row';
    } else {
    el.style.display = '';
    }
    el.hidden = false;
    }
    };

    window.ocultar = window.ocultar || function (id) {
    var el = get(id);
    if (el) {
    el.style.display = 'none';
    el.hidden = true;
    }
    };

    window.envmasExiste = function (id) {
    return !!get(id);
    };

    window.envmasMostrarEspera = function (texto) {
    var modal = get('envmas_modal_espera');
    if (modal) {
    var textos = modal.getElementsByTagName('div');
    for (var i = 0; i < textos.length; i++) {
        if (textos[i].className && textos[i].className.indexOf('envmas-modal-espera-text') !==-1) {
        if (texto) {
        textos[i].innerHTML=texto;
        }
        break;
        }
        }
        modal.style.display='flex' ;
        }
        };

        window.envmasOcultarEspera=function () {
        var modal=get('envmas_modal_espera');
        if (modal) {
        modal.style.display='none' ;
        }
        };

        window.envmasBloquearBotones=function () {
        try {
        if (get('btn_finalizar')) get('btn_finalizar').disabled=true;
        if (get('btn_continuar_tarde')) get('btn_continuar_tarde').disabled=true;
        if (get('gen_rad_btn')) get('gen_rad_btn').disabled=true;
        if (get('btn_plantilla')) get('btn_plantilla').disabled=true;
        } catch (e) {}
        };

        window.envmasDesbloquearBotones=function () {
        try {
        if (get('btn_finalizar')) get('btn_finalizar').disabled=false;
        if (get('btn_continuar_tarde')) get('btn_continuar_tarde').disabled=false;
        if (get('gen_rad_btn')) get('gen_rad_btn').disabled=false;
        if (get('btn_plantilla')) {
        get('btn_plantilla').disabled=!(get('rad_g') && get('rad_g').value==='s' );
        }
        } catch (e) {}
        };

        window.envmasQuill=null;
        window.envmasQuillCuerpo=null;

        window.envmasCrearEditorCuerpo=function () {
        if (!get('editor_cuerpo')) return;

        try {
        if (typeof Quill==='undefined' ) {
        console.error('Quill no está cargado');
        return;
        }

        if (window.envmasQuillCuerpo) return;

        window.envmasQuillCuerpo=new Quill('#editor_cuerpo', {
        theme: 'snow' ,
        placeholder: 'Escriba aqui la descripcion general o cuerpo del documento...' ,
        modules: {
        toolbar: '#toolbar_cuerpo' ,
        history: {
        delay: 1000,
        maxStack: 500,
        userOnly: true
        },
        clipboard: {
        matchVisual: false
        }
        }
        });

        window.envmasQuillCuerpo.on('text-change', function () {
        envmasGuardarCuerpoDocumento();
        });

        envmasGuardarCuerpoDocumento();

        } catch (e) {
        console.error(e);
        }
        };

        window.envmasGuardarCuerpoDocumento=function () {
        if (window.envmasQuillCuerpo) {
        var contenido='' ;

        if (typeof window.envmasQuillCuerpo.getSemanticHTML==='function' ) {
        contenido=window.envmasQuillCuerpo.getSemanticHTML();
        } else {
        contenido=window.envmasQuillCuerpo.root.innerHTML;
        }

        if (contenido==='<p><br></p>' || contenido.trim()==='' ) {
        contenido='' ;
        }

        if (get('texto_doc')) {
        get('texto_doc').value=contenido;
        }

        if (get('obs')) {
        get('obs').value=contenido;
        }
        }
        };


        window.envmasObtenerMensajeNota=function () {
        try {
        if (window.envmasQuill) {
        return window.envmasQuill.root.innerHTML || '' ;
        }
        } catch (e) {}

        if (get('area_mensaje')) {
        return get('area_mensaje').value || '' ;
        }

        return '' ;
        };

        window.envmasCrearEditorNotaInterna=function () {
        if (!get('div_mensaje')) return;

        var contenidoInicial='' ;
        if (get('area_mensaje') && get('area_mensaje').value) {
        contenidoInicial=get('area_mensaje').value;
        }
        get('div_mensaje').innerHTML=''
        + '<div class="envmas-note-box">'
        + '   <div id="toolbar_mensaje" class="envmas-editor-toolbar">'
        + '       <span class="ql-formats">'
        + '           <select class="ql-font"></select>'
        + '           <select class="ql-size"></select>'
        + '       </span>'
        + '       <span class="ql-formats">'
        + '           <select class="ql-header">'
        + '               <option value="1"></option>'
        + '               <option value="2"></option>'
        + '               <option selected></option>'
        + '           </select>'
        + '       </span>'
        + '       <span class="ql-formats">'
        + '           <button class="ql-bold"></button>'
        + '           <button class="ql-italic"></button>'
        + '           <button class="ql-underline"></button>'
        + '           <button class="ql-strike"></button>'
        + '       </span>'
        + '       <span class="ql-formats">'
        + '           <button class="ql-blockquote"></button>'
        + '           <button class="ql-code-block"></button>'
        + '       </span>'
        + '       <span class="ql-formats">'
        + '           <button class="ql-list" value="ordered"></button>'
        + '           <button class="ql-list" value="bullet"></button>'
        + '           <button class="ql-indent" value="-1"></button>'
        + '           <button class="ql-indent" value="+1"></button>'
        + '       </span>'
        + '       <span class="ql-formats">'
        + '           <select class="ql-align"></select>'
        + '       </span>'
        + '       <span class="ql-formats">'
        + '           <select class="ql-color"></select>'
        + '           <select class="ql-background"></select>'
        + '       </span>'
        + '       <span class="ql-formats">'
        + '           <button class="ql-link"></button>'
        + '           <button class="ql-image"></button>'
        + '           <button class="ql-video"></button>'
        + '       </span>'
        + '       <span class="ql-formats">'
        + '           <button class="ql-clean"></button>'
        + '       </span>'
        + '   </div>'
        + '   <div id="mensaje_editor" class="envmas-editor-content" style="min-height: 110px;"></div>'
        + '</div>' ;

        setTimeout(function () {
        try {
        if (typeof Quill==='undefined' ) {
        console.error('Quill no está cargado');
        return;
        }

        window.envmasQuill=new Quill('#mensaje_editor', {
        theme: 'snow' ,
        placeholder: '' ,
        modules: {
        toolbar: '#toolbar_mensaje'
        }
        });

        if (get('area_mensaje')) {
        get('area_mensaje').value=window.envmasQuill.root.innerHTML;
        }

        window.envmasQuill.on('text-change', function () {
        if (get('area_mensaje')) {
        get('area_mensaje').value=window.envmasQuill.root.innerHTML;
        }
        });
        } catch (e) {
        console.error(e);
        }
        }, 150);
        };

        window.envmasResetNotaInterna=function () {
        try {
        window.envmasQuill=null;
        } catch (e) {}

        if (get('div_mensaje')) {
        get('div_mensaje').innerHTML='' ;
        }

        if (get('area_mensaje')) {
        get('area_mensaje').value='' ;
        }

        ocultar('filmsn');
        };

        window.envmasLimpiarCamposDependientes=function () {
        if (get('myInput')) get('myInput').value='' ;
        if (get('myInput5')) get('myInput5').value='' ;
        if (get('myInputCedula')) get('myInputCedula').value='' ;
        if (get('myInputCedula5')) get('myInputCedula5').value='' ;
        if (get('myInputTipo5')) get('myInputTipo5').value='' ;
        if (get('id_fun2')) get('id_fun2').value='' ;
        if (get('ct_des')) get('ct_des').value='' ;
        if (get('radicados_all')) get('radicados_all').innerHTML='' ;
        if (get('radicados_single')) get('radicados_single').innerHTML='' ;
        if (get('destinatarios')) get('destinatarios').innerHTML='' ;
        if (get('rad_g')) get('rad_g').value='n' ;
        };

        window.envmasCambiarTipoDocumento=function (tipo) {

        envmasLimpiarCamposDependientes();
        envmasResetNotaInterna();

        function safeShow(id) {
        if (get(id)) mostrar(id);
        }

        function safeHide(id) {
        if (get(id)) ocultar(id);
        }

        /*=========================INTERNO=========================*/
        if (tipo==='i' ) {

        safeShow('interno');
        safeHide('externo');

        safeShow('adjunto');
        safeShow('des_anex');
        safeShow('tr_folio');
        safeShow('nota_titulo');
        safeShow('plantilla_adjunto_wrap');
        safeShow('gen_rad_btn');
        safeShow('filSerie');
        safeShow('filSubserie');
        safeShow('titulo_radicado');
        safeShow('doc_corr');
        safeShow('anx_fisico');
        safeShow('rad_sin');
        safeShow('fila_editor_cuerpo');

        safeHide('filmsn');
        safeHide('rad_mas');
        safeHide('btn_continuar_tarde');

        safeShow('numdoc22');
        safeHide('numdoc2');
        safeShow('div_img');
        safeShow('cc_para_th');

        <?php if (!isset($opt_env['descripciong']['visible']) || $opt_env['descripciong']['visible'] != 'n') { ?>
        safeShow('dg_doc');
        <?php } ?>

        if (document.getElementsByName('radicado')[1]) {
        document.getElementsByName('radicado')[1].checked=true;
        }

        if (get('nradicado2')) get('nradicado2').hidden=false;
        if (get('rad_sin')) get('rad_sin').hidden=false;
        if (get('rad_mas')) get('rad_mas').hidden=true;

        <?php if ($id_ser_sal_int) { ?>
        if (get('id_ser')) get('id_ser').value='<?php echo $id_ser_sal_int; ?>' ;
        xajax_solenv_cargarSerie('', '<?php echo $id_ser_sal_int; ?>' );
        xajax_solenv_cargaSubserie('<?php echo $id_ser_sal_int; ?>');
        <?php } ?>

        xajax_solenv_destino_i('m');
        }

        /*=========================EXTERNO=========================*/
        if (tipo==='e' ) {

        safeShow('externo');
        safeHide('interno');

        safeShow('adjunto');
        safeShow('des_anex');
        safeShow('tr_folio');
        safeShow('nota_titulo');
        safeShow('plantilla_adjunto_wrap');
        safeShow('gen_rad_btn');
        safeShow('filSerie');
        safeShow('filSubserie');
        safeShow('titulo_radicado');
        safeShow('doc_corr');
        safeShow('anx_fisico');
        safeShow('fila_editor_cuerpo');

        safeHide('filmsn');
        safeHide('btn_continuar_tarde');

        safeShow('numdoc2');
        safeHide('numdoc22');
        safeHide('div_img');
        safeHide('cc_para_th');

        <?php if (!isset($opt_env['descripciong']['visible']) || $opt_env['descripciong']['visible'] != 'n') { ?>
        safeShow('dg_doc');
        <?php } ?>

        if (document.getElementsByName('radicado')[0]) {
        document.getElementsByName('radicado')[0].checked=true;
        }

        if (get('nradicado2')) get('nradicado2').hidden=true;
        if (get('rad_sin')) get('rad_sin').hidden=true;
        if (get('rad_mas')) get('rad_mas').hidden=false;

        <?php if ($id_ser_sal_ext) { ?>
        if (get('id_ser')) get('id_ser').value='<?php echo $id_ser_sal_ext; ?>' ;
        xajax_solenv_cargaSubserie('<?php echo $id_ser_sal_ext; ?>');
        <?php } ?>

        xajax_envmas_destino_e();
        }

        /*=========================NOTA INTERNA 🔥 (CLAVE)=========================*/
        if (tipo==='n' ) {

        safeHide('externo');
        safeShow('interno');

        safeHide('adjunto');
        safeHide('des_anex');
        safeHide('dg_doc');
        safeHide('fila_editor_cuerpo');
        safeHide('tr_folio');
        safeHide('div_img');
        safeHide('numdoc2');
        safeHide('numdoc22');
        safeHide('cc_para_th');
        safeHide('anx_fisico');
        safeHide('doc_corr');
        safeHide('plantilla_adjunto_wrap');

        /* 🔥 IMPORTANTE */
        safeShow('filmsn'); // muestra el contenedor
        safeShow('btn_continuar_tarde');
        safeShow('titulo_radicado');
        safeShow('rad_sin');
        safeShow('nota_titulo');

        if (get('nradicado2')) get('nradicado2').hidden=true;
        if (get('rad_mas')) get('rad_mas').hidden=true;

        safeHide('filSerie');
        safeHide('filSubserie');

        /* 🔥 CREA EDITOR */
        setTimeout(function () {
        envmasCrearEditorNotaInterna();
        }, 100);

        try {
        xajax_ocultar_campos('nota interna');
        } catch (e) {}

        <?php if ($id_ser_nota_int) { ?>
        if (get('id_ser')) get('id_ser').value='<?php echo $id_ser_nota_int; ?>' ;
        xajax_solenv_cargaSubserie( '<?php echo $id_ser_nota_int; ?>' , '<?php echo $id_sub_ser_nota_int; ?>'
        );
        <?php } ?>

        xajax_solenv_destino_i('m');
        }

        envmasActualizarEstado();
        };

        window.envmasAgregarDestinatario=function () {
        var val='' ;
        var td='i' ;

        if (get('interno') && get('interno').style.display==='' ) {
        if (!get('myInput') || get('myInput').value==='' || !get('myInputCedula') || get('myInputCedula').value==='' ) {
        mrcCrearDialogoInfo('Especifique un destinatario', '' );
        return false;
        }

        get('id_fun2').value=get('myInputCedula').value;
        td='i' ;

        if (get('id_fun2').value !=='-1' ) {
        val=get('id_fun2').value;
        }
        } else {
        if (!get('myInput5') || get('myInput5').value==='' || get('myInput5').value==='-1' ) {
        mrcCrearDialogoInfo('Especifique un destinatario', '' );
        return false;
        }

        get('id_fun2').value=get('myInputCedula5').value;
        td=get('myInputTipo5').value;

        if (get('id_fun2').value !=='-1' ) {
        val=get('id_fun2').value;
        } else {
        val='' ;
        }
        }

        if (val !=='' ) {
        xajax_envmas_addDestinatario(td, val);
        }

        if (get('myInput')) get('myInput').value='' ;
        if (get('myInput5')) get('myInput5').value='' ;

        setTimeout(function () {
        envmasActualizarEstado();
        }, 300);
        };

        window.envmasConstruirListaDestinatarios=function () {
        var contDest=get('destinatarios');
        if (!contDest) return '' ;

        var lis0=contDest.getElementsByTagName('table');
        if (get('ct_des')) get('ct_des').value='' ;

        if (lis0 !=null && lis0.length> 0) {
        for (var i7 = 0; i7 < lis0.length; i7++) {
            if (lis0[i7].id && lis0[i7].id.length> 4) {
            get('ct_des').value += lis0[i7].id.substr(4) + ',';
            }
            }
            }

            return contDest.innerHTML;
            };

            window.envmasValidarFormulario = function (validarMensajeNota) {
            var radiosTipo = document.getElementsByName('tip_doc');
            var radiosTent = document.getElementsByName('tent');

            if (!radiosTipo[0].checked && !radiosTipo[1].checked && !radiosTipo[2].checked) {
            mrcCrearDialogoInfo('Seleccione el tipo de documento', '');
            return false;
            }

            if (!radiosTent[0].checked && !radiosTent[1].checked) {
            mrcCrearDialogoInfo('Seleccione el tipo de envío', '');
            return false;
            }

            if (!get('id_fun1') || get('id_fun1').value === '' || get('id_fun1').value === '-1') {
            mrcCrearDialogoInfo('Especifique un remitente', '');
            return false;
            }

            if (!get('asu') || get('asu').value === '') {
            mrcCrearDialogoInfo('Digite un asunto para el documento', "get('asu').focus();");
            return false;
            }

            if (get('id_ser') && get('filSerie') && get('filSerie').style.display !== 'none') {
            if (get('id_ser').value === '-1' || get('id_ser').value === '') {
            mrcCrearDialogoInfo('Seleccione una serie documental', '');
            return false;
            }
            }

            if (get('id_sub') && get('filSubserie') && get('filSubserie').style.display !== 'none') {
            if (get('id_sub').value === '-1' || get('id_sub').value === '') {
            mrcCrearDialogoInfo('Seleccione una subserie', '');
            return false;
            }
            }

            if (get('tr_folio') && get('tr_folio').style.display !== 'none' && get('nfol') && get('nfol').value === '') {
            mrcCrearDialogoInfo('Digite el Número de folios', "get('nfol').focus();");
            return false;
            }

            var contDest = get('destinatarios');
            var lis0 = contDest ? contDest.getElementsByTagName('table') : [];
            if (lis0 == null || lis0.length === 0) {
            mrcCrearDialogoInfo('La lista de destinatarios está vacía', '');
            return false;
            }

            if (validarMensajeNota && radiosTipo[2].checked) {
            if (get('area_mensaje')) {
            get('area_mensaje').value = envmasObtenerMensajeNota();
            }

            if (!get('area_mensaje') || get('area_mensaje').value === '' || get('area_mensaje').value === '<p><br></p>') {
            mrcCrearDialogoInfo('No has ingresado un mensaje electrónico', '');
            return false;
            }
            }

            return true;
            };

            window.envmasGenerarRadicado = function () {
            if (!envmasValidarFormulario(false)) return false;

            var radi_unico = 'n';
            if (document.getElementsByName('radicado')[0] && document.getElementsByName('radicado')[0].checked) {
            radi_unico = 's';
            }

            var lista_des = envmasConstruirListaDestinatarios();

            xajax_envmas_solicitar(xajax.getFormValues('frm_envmas'), 'n', radi_unico, lista_des);

            if (get('rad_g')) get('rad_g').value = 's';

            var ppa = '<?php echo $ppal; ?>';
            if (ppa === 'ppal' && document.getElementsByName('tip_doc')[2].checked === true) {
            mostrar('imp_stiker_btn');
            }

            ocultar('gen_rad_btn');
            envmasActualizarEstado();
            };

            window.envmasDescargarPlantilla = function () {
            if (get('rad_g').value !== 's') {
            mrcCrearDialogoInfo('Para descargar una plantilla primero debe generar un radicado', '');
            return false;
            }

            var firmaActiva = get('firma_activa') && get('firma_activa').value === 's';

            if (firmaActiva) {
            if (!envmasValidarFirma()) return false;

            if (get('firma_modo') && get('firma_modo').value === 'dibujar') {
            envmasGuardarFirmaCanvas();
            }
            }
            envmasGuardarCuerpoDocumento();

            var lista_des = get('destinatarios').innerHTML;
            get('list_des_html').value = lista_des;
            get('docgenenvi').value = 'noenvio';
            get('btn_cerr').value = 'n';

            get('frm_envmas').enctype = 'multipart/form-data';
            get('frm_envmas').action = '../../../build/documentacion/envio_docs/reprtf.php';
            get('frm_envmas').target = 'ventana';
            get('frm_envmas').submit();
            };
            /*cambios*/

            window.envmasFinalizar = function () {
            get('accion_nota').value = 'f';
            get('btn_cerr').value = 'n';

            try {
            get('titulo_per').value = get('tit_per_').value;
            get('docgenenvi').value = 'envigen';
            } catch (e) {}

            envmasGuardarCuerpoDocumento();

            get('frm_envmas').target = 'ventana';
            get('frm_envmas').enctype = 'multipart/form-data';

            var firmaActiva = get('firma_activa') && get('firma_activa').value === 's';

            get('frm_envmas').action = firmaActiva
            ? '../../../build/documentacion/envio_docs/envio_directo_copy.php'
            : '../../../build/documentacion/envio_docs/envio_directo_copy3.php';

            if (!envmasValidarFormulario(true)) return false;

            if (firmaActiva) {
            if (!envmasValidarFirma()) return false;

            if (get('firma_modo') && get('firma_modo').value === 'dibujar') {
            envmasGuardarFirmaCanvas();
            }
            } else {
            if (get('usar_firma_digital')) {
            get('usar_firma_digital').value = 'n';
            }
            }

            var radiosTipo = document.getElementsByName('tip_doc');

            if (radiosTipo[2] && radiosTipo[2].checked) {
            if (get('area_mensaje')) {
            get('area_mensaje').value = envmasObtenerMensajeNota();
            }

            if (get('area_mensaje').value === '' || get('area_mensaje').value === '<p><br></p>') {
            mrcCrearDialogoInfo('No has ingresado un mensaje electrónico', '');
            return false;
            }

            if (get('accion_editar').value !== 's') {
            var radi_unico = 'n';

            if (document.getElementsByName('radicado')[0] && document.getElementsByName('radicado')[0].checked) {
            radi_unico = 's';
            }

            var lista_des0 = envmasConstruirListaDestinatarios();
            xajax_envmas_solicitar(xajax.getFormValues('frm_envmas'), 'n', radi_unico, lista_des0);
            }
            }

            envmasConstruirListaDestinatarios();

            if (firmaActiva && get('envmas_modal_firma')) {
            get('envmas_modal_firma').style.display = 'flex';
            return false;
            }

            envmasContinuarEnvioFinal();
            return false;
            };

            window.envmasCerrarModalFirmaDigital = function () {
            if (get('envmas_modal_firma')) {
            get('envmas_modal_firma').style.display = 'none';
            }
            };

            window.envmasContinuarEnvioFinal = function () {
            envmasMostrarEspera('Procesando la información del documento...');
            envmasBloquearBotones();
            get('frm_envmas').submit();
            };

            window.envmasEsCorreoValido = function (correo) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(correo);
            };

            window.envmasConfirmarFirmaDigital = function (usarFirma) {
            if (get('usar_firma_digital')) {
            get('usar_firma_digital').value = usarFirma;
            }

            if (usarFirma === 's') {
            var correoFinal = '';

            if (get('envmas_correo_existente_wrap') && get('envmas_correo_existente_wrap').style.display !== 'none') {
            correoFinal = get('envmas_correo_existente') ? get('envmas_correo_existente').innerHTML : '';
            } else {
            correoFinal = get('envmas_correo_manual') ? trim(get('envmas_correo_manual').value) : '';
            }

            if (correoFinal === '') {
            mrcCrearDialogoInfo('Debe ingresar un correo para realizar la firma digital.', '');
            return false;
            }

            if (!envmasEsCorreoValido(correoFinal)) {
            mrcCrearDialogoInfo('El correo ingresado no es válido.', '');
            return false;
            }

            if (get('firma_correo')) {
            get('firma_correo').value = correoFinal;
            }
            }

            envmasCerrarModalFirmaDigital();
            envmasContinuarEnvioFinal();
            };

            window.envmasGuardarBorrador = function () {
            get('accion_nota').value = 'c';
            get('frm_envmas').target = 'ventana';
            get('frm_envmas').enctype = 'multipart/form-data';
            get('frm_envmas').action = '../../../build/documentacion/envio_docs/envio_directo.php';

            if (!envmasValidarFormulario(true)) return false;

            var firmaActiva = get('firma_activa') && get('firma_activa').value === 's';

            if (firmaActiva) {
            if (!envmasValidarFirma()) return false;

            if (get('firma_modo') && get('firma_modo').value === 'dibujar') {
            envmasGuardarFirmaCanvas();
            }
            }

            if (get('area_mensaje')) {
            get('area_mensaje').value = envmasObtenerMensajeNota();
            }

            envmasMostrarEspera('Guardando borrador...');
            envmasBloquearBotones();

            var radi_unico = 'n';

            if (document.getElementsByName('radicado')[0] && document.getElementsByName('radicado')[0].checked) {
            radi_unico = 's';
            }

            if (get('accion_editar').value !== 's') {
            var lista_des = envmasConstruirListaDestinatarios();
            xajax_envmas_solicitar(xajax.getFormValues('frm_envmas'), 'n', radi_unico, lista_des);
            get('rad_g').value = 's';
            ocultar('gen_rad_btn');
            } else {
            envmasOcultarEspera();
            envmasDesbloquearBotones();
            mrcCrearDialogoInfo('La acción ya se realizó', '');
            return false;
            }

            xajax_buzon_inicio();
            };

            window.envmasActualizarEstado = function () {
            try {
            var asuntoOk = get('asu') && get('asu').value.trim() !== '';
            var remitenteOk = get('id_fun1') && get('id_fun1').value.trim() !== '';
            var destinatariosOk = get('destinatarios') && get('destinatarios').getElementsByTagName('table').length > 0;

            if (get('gen_rad_btn')) {
            get('gen_rad_btn').disabled = !(asuntoOk && remitenteOk && destinatariosOk);
            }

            if (get('btn_plantilla')) {
            get('btn_plantilla').disabled = (get('rad_g').value !== 's');
            }
            } catch (e) {}
            };

            try {
            YAHOO.example.BasicRemoteInterno = function () {
            var oDS = new YAHOO.util.XHRDataSource("../../../build/documentacion/envio_docs/traerdatos.php");
            oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
            oDS.responseSchema = { resultsList: "datos", fields: ["nombre", "id_fun"] };
            oDS.maxCacheEntries = 5;

            var oAC = new YAHOO.widget.AutoComplete("myInput", "myContainer", oDS);
            oAC.prehighlightClassName = "yui-ac-prehighlight";
            oAC.useShadow = true;
            oAC.queryDelay = .5;

            var myHandler = function (sType, aArgs) {
            var myAC = aArgs[0];
            var oData = aArgs[2];

            get("myInputCedula").value = oData[1];
            myAC.getInputEl().value = oData[0];
            };

            oAC.itemSelectEvent.subscribe(myHandler);

            // ENTER en interno
            YAHOO.util.Event.addListener("myInput", "keydown", function (e) {
            var key = e.keyCode || e.which;

            if (key === 13) {
            YAHOO.util.Event.preventDefault(e);

            // Si ya hay un destinatario válido seleccionado, agregar
            if (get("myInputCedula") && get("myInputCedula").value !== "") {
            envmasAgregarDestinatario();
            return;
            }

            // Si hay resultados del autocomplete, tomar el primero
            if (oAC._oResultData && oAC._oResultData.length > 0) {
            var oData = oAC._oResultData[0];

            get("myInputCedula").value = oData[1];
            get("myInput").value = oData[0];

            envmasAgregarDestinatario();
            }
            }
            });

            return { oDS: oDS, oAC: oAC };
            }();
            } catch (e) { alert(e); }

            try {
            YAHOO.example.BasicRemoteRemitente = function () {
            var oDS2 = new YAHOO.util.XHRDataSource("../../../build/documentacion/envio_docs/traerdatos.php");
            oDS2.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
            oDS2.responseSchema = { resultsList: "datos", fields: ["nombre", "id_fun"] };
            oDS2.maxCacheEntries = 5;

            var oAC2 = new YAHOO.widget.AutoComplete("myInput2", "myContainer2", oDS2);
            oAC2.prehighlightClassName = "yui-ac-prehighlight";
            oAC2.useShadow = true;
            oAC2.queryDelay = .5;

            var myHandler2 = function (sType, aArgs) {
            var myAC = aArgs[0];
            var oData = aArgs[2];
            get("id_fun1").value = oData[1];
            myAC.getInputEl().value = oData[0];
            envmasActualizarEstado();
            };

            oAC2.itemSelectEvent.subscribe(myHandler2);

            return { oDS2: oDS2, oAC2: oAC2 };
            }();
            } catch (e) { alert(e); }

            try {
            YAHOO.example.BasicRemoteExterno = function () {
            var oDS5 = new YAHOO.util.XHRDataSource("../../../build/documentacion/envio_docs/traerdatos_ext.php");
            oDS5.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
            oDS5.responseSchema = { resultsList: "datos", fields: ["nombre", "id_fun", "tip_des"] };
            oDS5.maxCacheEntries = 5;

            var oAC5 = new YAHOO.widget.AutoComplete("myInput5", "myContainer5", oDS5);
            oAC5.prehighlightClassName = "yui-ac-prehighlight";
            oAC5.useShadow = true;
            oAC5.queryDelay = .5;

            var myHandler5 = function (sType, aArgs) {
            var myAC = aArgs[0];
            var oData = aArgs[2];

            get("myInputCedula5").value = oData[1];
            get("myInputTipo5").value = oData[2];
            myAC.getInputEl().value = oData[0];
            };

            oAC5.itemSelectEvent.subscribe(myHandler5);

            // ENTER en externo
            YAHOO.util.Event.addListener("myInput5", "keydown", function (e) {
            var key = e.keyCode || e.which;

            if (key === 13) {
            YAHOO.util.Event.preventDefault(e);

            // Si ya hay destinatario externo válido seleccionado, agregar
            if (get("myInputCedula5") && get("myInputCedula5").value !== "") {
            envmasAgregarDestinatario();
            return;
            }

            // Si hay resultados del autocomplete, tomar el primero
            if (oAC5._oResultData && oAC5._oResultData.length > 0) {
            var oData = oAC5._oResultData[0];

            get("myInputCedula5").value = oData[1];
            get("myInputTipo5").value = oData[2];
            get("myInput5").value = oData[0];

            envmasAgregarDestinatario();
            }
            }
            });
            return { oDS5: oDS5, oAC5: oAC5 };
            }();
            } catch (e) { alert(e); }

            envmasActualizarEstado();
            setTimeout(function () {
            if (get('firma_activa') && get('firma_activa').value === 's') {
            envmasInicializarFirma();
            envmasCambiarModoFirma('dibujar');
            }

            envmasCrearEditorCuerpo();
            }, 200);

            })();
        <?php
        $xres->addScript(ob_get_clean());

        if ($td_par == 'si') $td_par = 'i';
        if ($td_par == 'se') $td_par = 'e';
        if ($td_par == 'ss') $td_par = 's';

        if ($td_par == 'i') {
            llenardatos_int($id_doc_par, $xres);
        }

        if ($td_par == 'e') {
            llenardatos_ext($id_doc_par, $xres);
        }

        if ($td_par == 's') {
            llenardatos_not($id_doc_par, $xres);
        }

        if ($res_doc != NULL) {
            llenardatos_res($res_doc, $xres);

            $res_docp = (array) json_decode($res_doc);
            $radicado_responde = isset($res_docp['cod']) ? addslashes($res_docp['cod']) : '';

            $xres->addScript("
        setTimeout(function(){

            var radicadoAnterior = '" . $radicado_responde . "';

            if (radicadoAnterior === '' && get('radicados_single')) {
                radicadoAnterior = get('radicados_single').innerHTML;
            }

            if (get('numdoc')) {
                get('numdoc').value = radicadoAnterior;
            }

            if (get('es_rta')) {
                get('es_rta').checked = true;
            }

            if (get('radicados_single')) {
                get('radicados_single').innerHTML = '';
            }

            if (get('radicados_all')) {
                get('radicados_all').innerHTML = '';
            }

            if (get('rad_g')) {
                get('rad_g').value = 'n';
            }

            if (get('gen_rad_btn')) {
                get('gen_rad_btn').style.display = '';
                get('gen_rad_btn').style.visibility = 'visible';
                get('gen_rad_btn').hidden = false;
                get('gen_rad_btn').disabled = false;
            }

            if (get('fila_gen_rad_btn')) {
                get('fila_gen_rad_btn').style.display = '';
                get('fila_gen_rad_btn').hidden = false;
            }

        }, 1000);
    ");
        }

        if ($td_par == 's') {
            $xres->addScript("
            document.getElementsByName('tip_doc')[2].checked = true;
            envmasCambiarTipoDocumento('n');
            xajax_ocultar_campos();
        ");
        }

        if ($id_doc_par != '' || $id_doc_par != null) {
            $xres->addScript("document.getElementById('accion_editar').value='s';");
        }

        return utf8_encode($xres->getXML());
    }
