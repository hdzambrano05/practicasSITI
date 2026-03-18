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
                        <button type="button" class="gm-btn-icon" onclick="xajax_buzon_inicio('<?php echo $accion; ?>');" title="Actualizar">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>

                        <button type="button" class="gm-btn-icon gm-btn-primary" onclick="xajax_envmas_inicio();" title="Redactar">
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
                        <button type="button" class="gm-btn-icon" onclick="ejecutarBusquedaGeneral()" title="Buscar">
                            <i class="bi bi-search"></i>
                        </button>

                        <button type="button" class="gm-btn-icon" onclick="limpiarBusquedaGeneral()" title="Limpiar">
                            <i class="bi bi-x-lg"></i>
                        </button>

                        <button type="button" class="gm-btn-icon" title="Seleccionar todo" onclick="
                            var chk = document.getElementsByClassName('chk_carp');
                            var todosMarcados = true;
                            for(var i = 0; i < chk.length; i++){
                                if(!chk[i].checked){
                                    todosMarcados = false;
                                    break;
                                }
                            }
                            for(var i = 0; i < chk.length; i++){
                                chk[i].checked = !todosMarcados;
                            }

                            var checkTodos = document.getElementById('checkTodos');
                            if(checkTodos){
                                checkTodos.checked = !todosMarcados && chk.length > 0;
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

                <form id="idFormulario">
                    <div class="table-responsive">
                        <table class="buzon-table">
                            <thead>
                                <tr>
                                    <th width="45">
                                        <input type="checkbox" id="checkTodos" onclick="
                                            var chk = document.getElementsByClassName('chk_carp');
                                            for(var i = 0; i < chk.length; i++){
                                                chk[i].checked = this.checked;
                                            }
                                            if (typeof actualizarAccionesMasivas === 'function') {
                                                actualizarAccionesMasivas();
                                            }
                                        ">
                                    </th>
                                    <th>Radicado</th>
                                    <th>Asunto</th>
                                    <th>Remitente</th>
                                    <th>Tipo</th>
                                    <th>Origen / Destino</th>
                                    <th width="150">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyBuzon"></tbody>
                        </table>
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

        window.mostrarCargandoBuzon = function (mensaje) {
            var tbody = document.getElementById('tbodyBuzon');
            var pag = document.getElementById('paginadorBuzon');
            var texto = mensaje || 'Cargando documentos...';

            if (tbody) {
                tbody.innerHTML =
                    '<tr class=\"buzon-loading-row\">' +
                        '<td colspan=\"7\">' +
                            '<div class=\"buzon-loading-box\">' +
                                '<div class=\"buzon-spinner-wrap\">' +
                                    '<div class=\"buzon-spinner\"></div>' +
                                '</div>' +
                                '<div class=\"buzon-loading-text\">' + texto + '</div>' +
                                '<div class=\"buzon-loading-subtext\">Espera un momento mientras se actualiza la información</div>' +
                            '</div>' +
                        '</td>' +
                    '</tr>';
            }

            if (pag) {
                pag.innerHTML = '';
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

        $esNuevo   = ((int)$row['id_estd'] < 5 || (int)$row['id_estd'] == 10);
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

        if (
            ($row['tip_doc'] == 'si' || $row['tip_doc'] == 'ss' || $row['tip_doc'] == 'se') &&
            ($_SESSION['id_fun'] == $id_rem || $_SESSION['id_fun'] == $id_ori)
        ) {
            $mostrarResponder = true;
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
                            onclick="try{ parent.xajax_envmas_inicio('<?php echo $row['id_doc']; ?>','<?php echo $row['tip_doc']; ?>'); }catch(e){alert(e);}">
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
    ?>
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 0; $i < $totalPaginas; $i++) { ?>
                <li class="page-item <?php if (($par['startIndex'] - 1) == $i) echo 'active'; ?>">
                    <span
                        class="page-link"
                        onclick='
							get("startIndex").value=<?php echo $i + 1; ?>;
							get("paginadorBuzon").innerHTML="";
							if(typeof mostrarCargandoBuzon === "function"){
								mostrarCargandoBuzon("Cargando documentos...");
							}
							xajax_getListaDocumentos(xajax.getFormValues("idFormulario"));
						'>
                        <?php echo $i + 1; ?>
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
       RENDER
    ========================================================= */
    ob_start();
?>
    <div class="gm-doc-modal">
        <div class="gm-doc-header-card">
            <div class="gm-doc-header-main">
                <div class="gm-doc-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>

                <div class="gm-doc-title-wrap">
                    <h3 class="gm-doc-title">Información del documento</h3>
                    <div class="gm-doc-subtitle">Consulta detallada, recorrido y adjuntos del documento</div>
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
            <div class="gm-doc-section">
                <div class="gm-doc-section-head">
                    <h4><i class="bi bi-lightning-charge"></i> Acciones disponibles</h4>
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

                <div class="gm-doc-card">
                    <div class="gm-doc-card-head">
                        <h4><i class="bi bi-paperclip"></i> Adjuntos</h4>
                    </div>

                    <div class="gm-doc-adjunto-box">
                        <?php
                        $filename_nom = '';
                        $extension = '';

                        if ($arch != '') {
                            $zip = new ZipArchive();
                            $var_cons = $arch;
                            $filename = '../../../imgs_arch/' . $arch;

                            if ($zip->open($filename) == true) {
                                $zip->extractTo('../../tmp');
                                $filename_nom = $zip->getNameIndex(0);

                                if ($filename_nom != '' && pathinfo($filename_nom, PATHINFO_EXTENSION) != '') {
                                    $extension = strtolower(pathinfo($filename_nom, PATHINFO_EXTENSION));

                                    if ($extension == 'pdf') {
                                        $icono_dw = "<i class='bi bi-file-earmark-pdf-fill gm-file-pdf'></i>";
                                    } elseif ($extension == 'rtf' || $extension == 'docx' || $extension == 'doc') {
                                        $icono_dw = "<i class='bi bi-file-earmark-word-fill gm-file-word'></i>";
                                    } else {
                                        $icono_dw = "<i class='bi bi-file-earmark-fill gm-file-generic'></i>";
                                    }

                                    echo '<div class="gm-doc-download">';
                                    echo '<span class="gm-doc-download-label">Descargar adjunto</span>';
                                    echo "<a class='gm-doc-download-link' target='_blank' href='../../../build/common/files/download_file.php?id_var=16&file=$var_cons' title='Descargar Archivo'>" . $icono_dw . "<span>Descargar archivo</span></a>";
                                    echo '</div>';

                                    $zip->close();
                                } else {
                                    echo '<div class="gm-doc-empty">No se encontró ningún archivo o el documento se encuentra dañado.</div>';
                                }
                            } else {
                                echo '<div class="gm-doc-empty">El archivo comprimido no pudo ser extraído satisfactoriamente.</div>';
                            }

                            if ($filename_nom != '') {
                                $rutaLocal = '../../tmp/' . $filename_nom;

                                // Esta ruta debe ser accesible desde navegador
                                $rutaWeb = '../../tmp/' . rawurlencode($filename_nom);
                        ?>
                                <div class="gm-doc-preview">
                                    <?php if ($extension == 'pdf') { ?>
                                        <object class="gm-doc-pdfview" type="application/pdf" data="<?php echo $rutaWeb; ?>"></object>
                                    <?php } elseif ($extension == 'docx' || $extension == 'doc') { ?>
                                        <iframe
                                            class="gm-doc-pdfview"
                                            src="https://view.officeapps.live.com/op/embed.aspx?src=<?php echo urlencode((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $rutaWeb); ?>">
                                        </iframe>
                                    <?php } else { ?>
                                        <div class="gm-doc-empty">Vista previa no disponible para este tipo de archivo.</div>
                                    <?php } ?>
                                </div>
                        <?php
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
                modalYui.style.overflowY = 'auto';
                modalYui.style.overflowX = 'hidden';
                modalYui.style.maxHeight = '85vh';
                modalYui.style.height = '85vh';
            }

            if(modalCont){
                modalCont.style.overflow = 'visible';
            }
        }catch(e){}
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
                                            <input name="tip_doc" id="tip_doc_n" type="radio" class="inputRadioCss" value="n" onclick="envmasCambiarTipoDocumento('n')" />
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
                                <td class="envmas-label">Mensaje</td>
                                <td>
                                    <div id="div_mensaje"></div>
                                </td>
                            </tr>

                            <tr id="dg_doc" <?php if ($opt_env['descripciong']['visible'] && $opt_env['descripciong']['visible'] == 'n') echo 'style="display:none;"'; ?>>
                                <td class="envmas-label">Descripci&oacute;n general</td>
                                <td>
                                    <textarea name="obs" rows="3" class="textareaCss" id="obs" style="width:400px;"
                                        onblur="this.value=trim(this.value);"
                                        onkeypress="<?php echo ($expr['num_car']); ?>"></textarea>
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
                                    <input name="es_rta" type="checkbox" id="es_rta" value="s" <?php if (isset($cod) && $cod != '') echo 'checked="checked"'; ?> />
                                    <span class="etiqueta">N&uacute;mero</span>
                                    <input name="numdoc" type="text" id="numdoc" class="inputTextCss"
                                        onblur="this.value=trim(this.value);"
                                        onkeypress="<?php echo ($expr['num_car']); ?>"
                                        value="<?php echo isset($cod) ? $cod : ''; ?>">
                                    <input name="btn_bdoc" type="button" id="btn_bdoc" value="Buscar" class="inputButtonCss"
                                        onclick="xajax_solenv_documentosrecibidos('i',get('asu').value)" />
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>

                <div class="envmas-card envmas-card--full">
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
                            <td class="envmas-label">Tiene anexos en físico</td>
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
                                <textarea name="nota_dse" rows="4" class="inputTextCss" id="nota_dse" style="width:500px"
                                    onblur="this.value=trim(this.value);"
                                    onkeypress="<?php echo ($expr['descripcion']); ?>"></textarea>
                            </td>
                        </tr>

                        <tr id="titulo_radicado">
                            <td colspan="2" style="text-align:center;">
                                <span class="grupo">Generar radicado</span>
                            </td>
                        </tr>

                        <tr>
                            <td></td>
                            <td>
                                <input type="button" class="inputButtonCss" value="Obtener Radicado del Documento" id="gen_rad_btn"
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

                <div class="envmas-card envmas-card--full">
                    <div class="envmas-title">5. Plantilla y adjuntos</div>

                    <table class="envmas-table">
                        <tr id="plantilla">
                            <td class="envmas-label">Plantilla</td>
                            <td>
                                <input id="btn_plantilla" type="button" class="inputButtonCss" value="Descargar plantilla"
                                    onclick="envmasDescargarPlantilla();" />
                            </td>
                        </tr>

                        <tr id="adjunto_label">
                            <td class="envmas-label">Adjunto</td>
                            <td></td>
                        </tr>

                        <tr id="adjunto">
                            <td class="envmas-label">Archivo</td>
                            <td>
                                <table cellspacing="1" cellpadding="2" id="tb_mas_arch">
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th>
                                            <input type="file" name="file_a0" id="file_a0" />
                                            <input type="hidden" name="id_ds" value="<?php echo $id_ds ?>" />
                                            <input type="button" id="btn_mas_arch" name="btn_mas_arch" value="+"
                                                title="Ingresar un nuevo archivo" class="inputButtonCss"
                                                onclick="xajax_add_mas_arch()" />
                                        </th>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="envmas-card envmas-card--full">
                    <div class="envmas-actions">
                        <input type="button" id="btn_finalizar" class="inputButtonCss" value="Finalizar" onclick="envmasFinalizar(this);" />
                        <input type="button" id="btn_continuar_tarde" style="display:none" name="btnlist_dest"
                            value="Continuar más tarde" class="inputButtonCss"
                            onclick="envmasGuardarBorrador(this);" />
                    </div>
                </div>

            </div>

            <div id="envmas_modal_espera" class="envmas-modal-espera">
                <div class="envmas-modal-espera-box">
                    <div class="envmas-modal-espera-loader"></div>
                    <div class="envmas-modal-espera-title">Espere...</div>
                    <div class="envmas-modal-espera-text">Procesando la información del documento.</div>
                </div>
            </div>

        </form>
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
    (function(){

    window.get = window.get || function(id){ return document.getElementById(id); };

    window.mostrar = window.mostrar || function(id){
    var el = get(id);
    if(el){
    el.style.display = '';
    el.hidden = false;
    }
    };

    window.ocultar = window.ocultar || function(id){
    var el = get(id);
    if(el){
    el.style.display = 'none';
    }
    };

    window.envmasExiste = function(id){
    return !!get(id);
    };

    window.envmasMostrarEspera = function(texto){
    var modal = get('envmas_modal_espera');
    if(modal){
    var textos = modal.getElementsByTagName('div');
    for(var i = 0; i < textos.length; i++){
        if(textos[i].className && textos[i].className.indexOf('envmas-modal-espera-text') !==-1){
        if(texto){
        textos[i].innerHTML=texto;
        }
        break;
        }
        }
        modal.style.display='flex' ;
        }
        };

        window.envmasOcultarEspera=function(){
        var modal=get('envmas_modal_espera');
        if(modal){
        modal.style.display='none' ;
        }
        };

        window.envmasBloquearBotones=function(){
        try{
        if(get('btn_finalizar')) get('btn_finalizar').disabled=true;
        if(get('btn_continuar_tarde')) get('btn_continuar_tarde').disabled=true;
        if(get('gen_rad_btn')) get('gen_rad_btn').disabled=true;
        if(get('btn_plantilla')) get('btn_plantilla').disabled=true;
        }catch(e){}
        };

        window.envmasDesbloquearBotones=function(){
        try{
        if(get('btn_finalizar')) get('btn_finalizar').disabled=false;
        if(get('btn_continuar_tarde')) get('btn_continuar_tarde').disabled=false;
        if(get('gen_rad_btn')) get('gen_rad_btn').disabled=false;
        if(get('btn_plantilla')) get('btn_plantilla').disabled=(get('rad_g') && get('rad_g').value !=='s' );
        }catch(e){}
        };

        window.envmasResetNotaInterna=function(){
        try{
        if (typeof CKEDITOR !=='undefined' && CKEDITOR.instances) {
        for (var instancia in CKEDITOR.instances) {
        if (CKEDITOR.instances.hasOwnProperty(instancia)) {
        try {
        CKEDITOR.instances[instancia].destroy(true);
        } catch(err){}
        }
        }
        }
        }catch(e){}

        if (get('div_mensaje')) {
        get('div_mensaje').innerHTML='' ;
        }

        if (get('area_mensaje')) {
        get('area_mensaje').value='' ;
        }

        ocultar('filmsn');
        };

        window.envmasLimpiarCamposDependientes=function(){
        if(get('myInput')) get('myInput').value='' ;
        if(get('myInput5')) get('myInput5').value='' ;
        if(get('myInputCedula')) get('myInputCedula').value='' ;
        if(get('myInputCedula5')) get('myInputCedula5').value='' ;
        if(get('myInputTipo5')) get('myInputTipo5').value='' ;
        if(get('id_fun2')) get('id_fun2').value='' ;
        if(get('ct_des')) get('ct_des').value='' ;
        if(get('radicados_all')) get('radicados_all').innerHTML='' ;
        if(get('radicados_single')) get('radicados_single').innerHTML='' ;
        if(get('destinatarios')) get('destinatarios').innerHTML='' ;
        if(get('rad_g')) get('rad_g').value='n' ;
        };

        window.envmasCambiarTipoDocumento=function(tipo){
        envmasLimpiarCamposDependientes();

        // Limpiar editor si venimos de nota interna
        envmasResetNotaInterna();

        if (tipo==='i' ) {
        mostrar('interno');
        ocultar('externo');

        mostrar('adjunto_label');
        mostrar('adjunto');
        mostrar('des_anex');
        mostrar('tr_folio');
        mostrar('nota_titulo');
        mostrar('plantilla');
        mostrar('gen_rad_btn');
        mostrar('filSerie');
        mostrar('filSubserie');
        mostrar('titulo_radicado');
        mostrar('doc_corr');
        mostrar('anx_fisico');
        mostrar('rad_sin');

        ocultar('rad_mas');
        ocultar('btn_continuar_tarde');

        mostrar('numdoc22');
        ocultar('numdoc2');
        mostrar('div_img');
        mostrar('cc_para_th');

        <?php if (!$opt_env['descripciong']['visible'] || $opt_env['descripciong']['visible'] != 'n') { ?>
        mostrar('dg_doc');
        <?php } ?>

        if (document.getElementsByName('radicado')[1]) {
        document.getElementsByName('radicado')[1].checked=true;
        }

        if(get('nradicado2')) get('nradicado2').hidden=false;
        if(get('rad_sin')) get('rad_sin').hidden=false;
        if(get('rad_mas')) get('rad_mas').hidden=true;

        <?php if ($id_ser_sal_int != '' && $id_ser_sal_int != NULL) { ?>
        if(get('id_ser')) get('id_ser').value='<?php echo $id_ser_sal_int; ?>' ;
        xajax_solenv_cargarSerie('', '<?php echo $id_ser_sal_int; ?>' );
        xajax_solenv_cargaSubserie('<?php echo $id_ser_sal_int; ?>');
        <?php } ?>

        xajax_solenv_destino_i('m');
        }

        if (tipo==='e' ) {
        mostrar('externo');
        ocultar('interno');

        mostrar('adjunto_label');
        mostrar('adjunto');
        mostrar('des_anex');
        mostrar('tr_folio');
        mostrar('nota_titulo');
        mostrar('plantilla');
        mostrar('gen_rad_btn');
        mostrar('filSerie');
        mostrar('filSubserie');
        mostrar('titulo_radicado');
        mostrar('doc_corr');
        mostrar('anx_fisico');

        ocultar('filmsn');
        ocultar('btn_continuar_tarde');

        mostrar('numdoc2');
        ocultar('numdoc22');
        ocultar('div_img');
        ocultar('cc_para_th');

        <?php if (!$opt_env['descripciong']['visible'] || $opt_env['descripciong']['visible'] != 'n') { ?>
        mostrar('dg_doc');
        <?php } ?>

        if (document.getElementsByName('radicado')[0]) {
        document.getElementsByName('radicado')[0].checked=true;
        }

        if(get('nradicado2')) get('nradicado2').hidden=true;
        if(get('rad_sin')) get('rad_sin').hidden=true;
        if(get('rad_mas')) get('rad_mas').hidden=false;

        <?php if ($id_ser_sal_ext != '' && $id_ser_sal_ext != NULL) { ?>
        if(get('id_ser')) get('id_ser').value='<?php echo $id_ser_sal_ext; ?>' ;
        xajax_solenv_cargaSubserie('<?php echo $id_ser_sal_ext; ?>');
        <?php } ?>

        xajax_envmas_destino_e();
        }

        if (tipo==='n' ) {
        ocultar('externo');
        mostrar('interno');

        ocultar('adjunto_label');
        ocultar('adjunto');
        ocultar('des_anex');
        ocultar('dg_doc');
        ocultar('tr_folio');
        ocultar('div_img');
        ocultar('numdoc2');
        ocultar('numdoc22');
        ocultar('cc_para_th');
        ocultar('anx_fisico');
        ocultar('doc_corr');

        mostrar('filmsn');
        mostrar('btn_continuar_tarde');
        mostrar('titulo_radicado');
        mostrar('rad_sin');

        if(get('nradicado2')) get('nradicado2').hidden=true;
        if(get('rad_mas')) get('rad_mas').hidden=true;

        ocultar('filSerie');
        ocultar('filSubserie');

        xajax_ocultar_campos('nota interna');
        xajax_cargar_editor();

        <?php if ($id_ser_nota_int != '' && $id_ser_nota_int != NULL) { ?>
        if(get('id_ser')) get('id_ser').value='<?php echo $id_ser_nota_int; ?>' ;
        xajax_solenv_cargaSubserie('<?php echo $id_ser_nota_int; ?>', '<?php echo $id_sub_ser_nota_int; ?>' );
        <?php } ?>

        xajax_solenv_destino_i('m');
        }

        envmasActualizarEstado();
        };

        window.envmasAgregarDestinatario=function(){
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

        if(get('myInput')) get('myInput').value='' ;
        if(get('myInput5')) get('myInput5').value='' ;

        setTimeout(function(){
        envmasActualizarEstado();
        }, 300);
        };

        window.envmasConstruirListaDestinatarios=function(){
        var lis0=get('destinatarios').getElementsByTagName('table');
        get('ct_des').value='' ;

        if (lis0 !=null && lis0.length> 0) {
        for (var i7 = 0; i7 < lis0.length; i7++) {
            if (lis0[i7].id && lis0[i7].id.length> 4) {
            get('ct_des').value += lis0[i7].id.substr(4) + ',';
            }
            }
            }

            return get('destinatarios').innerHTML;
            };

            window.envmasValidarFormulario = function(validarMensajeNota){
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

            var lis0 = get('destinatarios').getElementsByTagName('table');
            if (lis0 == null || lis0.length === 0) {
            mrcCrearDialogoInfo('La lista de destinatarios está vacía', '');
            return false;
            }

            if (validarMensajeNota && radiosTipo[2].checked) {
            try{
            if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances) {
            for (var instancia in CKEDITOR.instances) {
            if (CKEDITOR.instances.hasOwnProperty(instancia)) {
            get('area_mensaje').value = CKEDITOR.instances[instancia].getData();
            break;
            }
            }
            }
            }catch(e){}

            if (get('area_mensaje').value === '') {
            mrcCrearDialogoInfo('No has ingresado un mensaje electrónico', '');
            return false;
            }
            }

            return true;
            };

            window.envmasGenerarRadicado = function(){
            if (!envmasValidarFormulario(false)) return false;

            var radi_unico = 'n';
            if (document.getElementsByName('radicado')[0] && document.getElementsByName('radicado')[0].checked) {
            radi_unico = 's';
            }

            var lista_des = envmasConstruirListaDestinatarios();

            xajax_envmas_solicitar(xajax.getFormValues('frm_envmas'), 'n', radi_unico, lista_des);

            get('rad_g').value = 's';

            var ppa = '<?php echo $ppal; ?>';
            if (ppa === 'ppal' && document.getElementsByName('tip_doc')[2].checked === true) {
            mostrar('imp_stiker_btn');
            }

            ocultar('gen_rad_btn');
            envmasActualizarEstado();
            };

            window.envmasDescargarPlantilla = function(){
            if (get('rad_g').value !== 's') {
            mrcCrearDialogoInfo('Para descargar una plantilla primero debe generar un radicado', '');
            return false;
            }

            var lista_des = get('destinatarios').innerHTML;
            get('list_des_html').value = lista_des;
            get('docgenenvi').value = 'noenvio';
            get('btn_cerr').value = 'n';

            get('frm_envmas').action = '../../../build/documentacion/envio_docs/reprtf.php';
            get('frm_envmas').target = 'ventana';
            get('frm_envmas').submit();
            };

            window.envmasFinalizar = function(){
            get('accion_nota').value = 'f';
            get('btn_cerr').value = 'n';

            try{
            get('titulo_per').value = get('tit_per_').value;
            get('docgenenvi').value = 'envigen';
            }catch(e){}

            get('frm_envmas').target = 'ventana';
            get('frm_envmas').enctype = 'multipart/form-data';
            get('frm_envmas').action = '../../../build/documentacion/envio_docs/envio_directo_copy.php';

            if (!envmasValidarFormulario(true)) return false;

            var radiosTipo = document.getElementsByName('tip_doc');

            if (radiosTipo[2].checked) {
            try{
            if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances) {
            for (var instancia in CKEDITOR.instances) {
            if (CKEDITOR.instances.hasOwnProperty(instancia)) {
            get('area_mensaje').value = CKEDITOR.instances[instancia].getData();
            break;
            }
            }
            }
            }catch(e){}

            if (get('area_mensaje').value === '') {
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
            envmasMostrarEspera('Procesando la información del documento...');
            envmasBloquearBotones();
            get('frm_envmas').submit();
            };

            window.envmasGuardarBorrador = function(){
            get('accion_nota').value = 'c';
            get('frm_envmas').target = 'ventana';
            get('frm_envmas').enctype = 'multipart/form-data';
            get('frm_envmas').action = '../../../build/documentacion/envio_docs/envio_directo.php';

            if (!envmasValidarFormulario(true)) return false;

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

            window.envmasActualizarEstado = function(){
            try{
            var asuntoOk = get('asu') && get('asu').value.trim() !== '';
            var remitenteOk = get('id_fun1') && get('id_fun1').value.trim() !== '';
            var destinatariosOk = get('destinatarios') && get('destinatarios').getElementsByTagName('table').length > 0;

            if (get('gen_rad_btn')) {
            get('gen_rad_btn').disabled = !(asuntoOk && remitenteOk && destinatariosOk);
            }

            if (get('btn_plantilla')) {
            get('btn_plantilla').disabled = (get('rad_g').value !== 's');
            }
            }catch(e){}
            };

            try{
            YAHOO.example.BasicRemoteInterno = function() {
            var oDS = new YAHOO.util.XHRDataSource("../../../build/documentacion/envio_docs/traerdatos.php");
            oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
            oDS.responseSchema = { resultsList : "datos", fields : ["nombre","id_fun"] };
            oDS.maxCacheEntries = 5;

            var oAC = new YAHOO.widget.AutoComplete("myInput", "myContainer", oDS);
            oAC.prehighlightClassName = "yui-ac-prehighlight";
            oAC.useShadow = true;
            oAC.queryDelay = .5;

            var myHandler = function(sType, aArgs) {
            var myAC = aArgs[0];
            var oData = aArgs[2];
            get("myInputCedula").value = oData[1];
            myAC.getInputEl().value = oData[0];
            };

            oAC.itemSelectEvent.subscribe(myHandler);

            return {oDS:oDS, oAC:oAC};
            }();
            }catch(e){ alert(e); }

            try{
            YAHOO.example.BasicRemoteRemitente = function() {
            var oDS2 = new YAHOO.util.XHRDataSource("../../../build/documentacion/envio_docs/traerdatos.php");
            oDS2.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
            oDS2.responseSchema = { resultsList : "datos", fields : ["nombre","id_fun"] };
            oDS2.maxCacheEntries = 5;

            var oAC2 = new YAHOO.widget.AutoComplete("myInput2", "myContainer2", oDS2);
            oAC2.prehighlightClassName = "yui-ac-prehighlight";
            oAC2.useShadow = true;
            oAC2.queryDelay = .5;

            var myHandler2 = function(sType, aArgs) {
            var myAC = aArgs[0];
            var oData = aArgs[2];
            get("id_fun1").value = oData[1];
            myAC.getInputEl().value = oData[0];
            envmasActualizarEstado();
            };

            oAC2.itemSelectEvent.subscribe(myHandler2);

            return {oDS2:oDS2, oAC2:oAC2};
            }();
            }catch(e){ alert(e); }

            try{
            YAHOO.example.BasicRemoteExterno = function() {
            var oDS5 = new YAHOO.util.XHRDataSource("../../../build/documentacion/envio_docs/traerdatos_ext.php");
            oDS5.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
            oDS5.responseSchema = { resultsList : "datos", fields : ["nombre","id_fun","tip_des"] };
            oDS5.maxCacheEntries = 5;

            var oAC5 = new YAHOO.widget.AutoComplete("myInput5", "myContainer5", oDS5);
            oAC5.prehighlightClassName = "yui-ac-prehighlight";
            oAC5.useShadow = true;
            oAC5.queryDelay = .5;

            var myHandler5 = function(sType, aArgs) {
            var myAC = aArgs[0];
            var oData = aArgs[2];
            get("myInputCedula5").value = oData[1];
            get("myInputTipo5").value = oData[2];
            myAC.getInputEl().value = oData[0];
            };

            oAC5.itemSelectEvent.subscribe(myHandler5);

            return {oDS5:oDS5, oAC5:oAC5};
            }();
            }catch(e){ alert(e); }

            envmasActualizarEstado();

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
