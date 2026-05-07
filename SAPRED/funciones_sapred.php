function getDoc($id_pre='',$id_ano='', $ano_texto=''){
    $xres = new xajaxResponse();
    ob_start();
    include ('../../bibliotecas/valida 1.0/biblio.php');
    global $con;
?>
    <!-- MODAL CONTENEDOR DEL EXCEL -->
    <div id="modal_excel_preview" style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.7);
        z-index:999999;
    ">
        <div id="modal_excel_box" style="
            background:#fff;
            width:96%;
            max-width:1100px;
            margin:1.5% auto;
            padding:16px;
            border-radius:12px;
            position:relative;
            box-sizing:border-box;
        ">

            <h3 style="margin:0 0 10px 0; color:#1e3a8a; font-size:18px;">
                Vista previa del Excel
            </h3>

            <div id="contenido_excel_modal"
                 style="max-height:72vh; overflow:auto; border:1px solid #e5e7eb; border-radius:8px; padding:8px; background:#fafafa;">
            </div>

            <button id="cerrar_excel_preview"
                style="
                    position:absolute;
                    top:10px;
                    right:10px;
                    padding:6px 12px;
                    background:#ef4444;
                    color:#fff;
                    border:none;
                    border-radius:6px;
                    font-weight:600;
                    cursor:pointer;
                ">
                X
            </button>
        </div>
    </div>

    <div id="capa_nivel0_doc" style="background:#f4f6f9; padding:12px; box-sizing:border-box;">
        <div style="width:98%; margin:0 auto; font-family:'Segoe UI', Arial, sans-serif; color:#333;">

            <!-- INFO AÑO -->
            <div style="
                background:#e9f5ff;
                border:1px solid #b8daff;
                color:#004085;
                padding:8px 10px;
                margin:0 auto 8px auto;
                max-width:260px;
                border-radius:8px;
                text-align:center;
                font-size:12px;
                font-weight:600;
                line-height:1.35;
            ">
                El a&ntilde;o que se va a subir es:<br>
                <span style="font-size:14px; color:#0056b3;">
                    <?php echo htmlspecialchars($ano_texto); ?>
                </span>
            </div>

            <!-- FORMULARIO -->
            <form id="form_excel" enctype="multipart/form-data"
                  style="
                    max-width:320px;
                    margin:8px auto 10px auto;
                    background:#fff;
                    padding:10px;
                    border-radius:8px;
                    border:1px solid #ddd;
                    text-align:center;
                    box-shadow:0 1px 4px rgba(0,0,0,0.05);
                    font-family:Arial, sans-serif;
                  ">

                <label for="archivo_excel" style="
                    display:block;
                    font-weight:600;
                    margin-bottom:5px;
                    font-size:12px;
                    color:#333;
                ">
                    Selecciona tu archivo Excel
                </label>

                <input type="file" id="archivo_excel" accept=".xls,.xlsx"
                       style="
                            padding:5px;
                            border-radius:4px;
                            border:1px solid #ccc;
                            width:100%;
                            cursor:pointer;
                            font-size:12px;
                            transition:all 0.2s ease;
                            box-sizing:border-box;
                       "
                       onmouseover="this.style.borderColor='#28a745';"
                       onmouseout="this.style.borderColor='#ccc';">

                <button type="button" id="btn_subir_excel"
                        style="
                            margin-top:7px;
                            padding:6px 14px;
                            background:#28a745;
                            color:#fff;
                            border:none;
                            border-radius:5px;
                            font-size:12px;
                            cursor:pointer;
                            transition:all 0.3s ease;
                            box-shadow:0 1px 2px rgba(0,0,0,0.1);
                        ">
                    Subir y Cargar
                </button>
            </form>

            <input type="hidden" id="id_ano_excel" value="<?php echo htmlspecialchars($id_ano); ?>">

            <script>
                const btn = document.getElementById('btn_subir_excel');
                if(btn){
                    btn.addEventListener('mouseover', () => btn.style.background = '#218838');
                    btn.addEventListener('mouseout', () => btn.style.background = '#28a745');
                }
            </script>

            <div id="resultado_excel" style="
                margin:6px 0 8px 0;
                text-align:center;
                font-weight:600;
                font-size:12px;
                color:#555;
                min-height:18px;
            "></div>

            <!-- CONTENEDOR TARJETAS -->
            <div style="
                display:grid;
                grid-template-columns:2fr 1fr;
                gap:12px;
                margin-bottom:10px;
                align-items:start;
            ">

                <!-- TARJETA PRINCIPAL -->
                <div style="
                    border:1px solid #d8dee6;
                    border-radius:10px;
                    background:#fafafa;
                    font-family:sans-serif;
                    font-size:11px;
                    overflow:hidden;
                    box-shadow:0 1px 4px rgba(0,0,0,0.04);
                ">

                    <!-- HEADER -->
                    <div style="
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        gap:8px;
                        padding:8px 10px;
                        background:#f7fbfd;
                        border-bottom:1px solid #d9edf7;
                    ">
                        <div style="min-width:0;">
                            <h3 style="
                                margin:0;
                                color:#17a2b8;
                                font-size:13px;
                                line-height:1.2;
                            ">
                                Cargar archivo Excel
                            </h3>
                            <div style="font-size:10px; color:#6b7280; margin-top:2px;">
                                Encabezados requeridos
                            </div>
                        </div>

                        <button type="button" id="toggle_diccionario"
                            style="
                                padding:4px 10px;
                                border:1px solid #17a2b8;
                                background:#fff;
                                color:#17a2b8;
                                border-radius:5px;
                                font-size:11px;
                                font-weight:600;
                                cursor:pointer;
                                white-space:nowrap;
                            ">
                            Ocultar
                        </button>
                    </div>

                    <!-- BODY -->
                    <div id="body_diccionario" style="padding:8px;">
                        <p style="margin:0 0 5px 0; color:#555; line-height:1.35;">
                            El archivo Excel debe contener los encabezados indicados a continuaci&oacute;n.
                        </p>

                        <p style="margin:0 0 7px 0; color:#555; line-height:1.35;">
                            La informaci&oacute;n se divide en dos grupos:
                            <strong>datos del estudiante</strong> y
                            <strong>datos de padres, madre y acudiente</strong>.
                        </p>

                        <div style="
                            display:flex;
                            justify-content:flex-end;
                            margin-bottom:6px;
                        ">
                            <button type="button" id="toggle_detalle_campos"
                                style="
                                    padding:3px 8px;
                                    border:1px solid #cbd5e1;
                                    background:#fff;
                                    color:#475569;
                                    border-radius:5px;
                                    font-size:10px;
                                    font-weight:600;
                                    cursor:pointer;
                                ">
                                Compactar campos
                            </button>
                        </div>

                        <div id="detalle_campos_excel" style="
                            display:grid;
                            grid-template-columns:1fr 1fr;
                            gap:8px;
                        ">

                            <!-- ESTUDIANTE -->
                            <div style="
                                background:#fff;
                                padding:8px;
                                border-radius:6px;
                                border:1px solid #d6d6d6;
                            ">
                                <div style="
                                    font-weight:700;
                                    color:#17a2b8;
                                    margin-bottom:6px;
                                    font-size:11px;
                                ">
                                    Datos del estudiante
                                </div>

                                <div style="
                                    display:grid;
                                    grid-template-columns:1fr 1fr;
                                    gap:4px 10px;
                                    font-size:10.5px;
                                    color:#374151;
                                ">
                                    <div>N&uacute;mero de documento</div>
                                    <div>Tipo de documento</div>
                                    <div>Fecha de nacimiento</div>
                                    <div>Ciudad de nacimiento</div>
                                    <div>A&ntilde;o acad&eacute;mico</div>
                                    <div>Sede educativa</div>
                                    <div>Grado o curso</div>
                                    <div>Jornada</div>
                                    <div>Direcci&oacute;n</div>
                                    <div>Barrio o localidad</div>
                                    <div>Tel&eacute;fono fijo</div>
                                    <div>Tel&eacute;fono celular</div>
                                    <div>Correo electr&oacute;nico</div>
                                    <div>Nombres</div>
                                    <div>Apellidos</div>
                                    <div>Sexo</div>
                                    <div>Grupo sangu&iacute;neo</div>
                                    <div>EPS</div>
                                    <div>SISBEN</div>
                                    <div>Colegio anterior</div>
                                </div>
                            </div>

                            <!-- PADRES / MADRE / ACUDIENTE -->
                            <div style="
                                background:#fff;
                                padding:8px;
                                border-radius:6px;
                                border:1px solid #d6d6d6;
                            ">
                                <div style="
                                    font-weight:700;
                                    color:#6c757d;
                                    margin-bottom:6px;
                                    font-size:11px;
                                ">
                                    Padres / Madre / Acudiente
                                </div>

                                <div style="
                                    display:grid;
                                    grid-template-columns:1fr 1fr 1fr;
                                    gap:6px 8px;
                                    font-size:10.5px;
                                    color:#374151;
                                ">
                                    <div style="font-weight:700; color:#007bff;">Padre</div>
                                    <div style="font-weight:700; color:#e83e8c;">Madre</div>
                                    <div style="font-weight:700; color:#28a745;">Acudiente</div>

                                    <div>
                                        Documento<br>
                                        Tipo documento<br>
                                        Nombre<br>
                                        Apellido<br>
                                        Direcci&oacute;n<br>
                                        Tel&eacute;fono<br>
                                        Profesi&oacute;n
                                    </div>

                                    <div>
                                        Documento<br>
                                        Tipo documento<br>
                                        Nombre<br>
                                        Apellido<br>
                                        Direcci&oacute;n<br>
                                        Tel&eacute;fono<br>
                                        Profesi&oacute;n
                                    </div>

                                    <div>
                                        Documento<br>
                                        Tipo documento<br>
                                        Nombre<br>
                                        Apellido<br>
                                        Direcci&oacute;n<br>
                                        Tel&eacute;fono<br>
                                        Profesi&oacute;n
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p style="margin:6px 0 0 0; color:#777; font-size:10px;">
                            Nota: El encabezado debe existir aunque el dato est&eacute; vac&iacute;o.
                        </p>
                    </div>
                </div>

                <!-- TARJETA ADICIONALES -->
                <div style="
                    border:1px dashed #bbb;
                    border-radius:10px;
                    background:#fcfcfc;
                    overflow:hidden;
                    box-shadow:0 1px 4px rgba(0,0,0,0.03);
                ">
                    <div style="
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        gap:8px;
                        padding:8px 10px;
                        background:#f8f9fa;
                        border-bottom:1px solid #e5e5e5;
                    ">
                        <div style="min-width:0;">
                            <h3 style="
                                margin:0;
                                color:#6c757d;
                                font-size:13px;
                                line-height:1.2;
                            ">
                                Datos Adicionales
                            </h3>
                            <div style="font-size:10px; color:#6b7280; margin-top:2px;">
                                Columnas opcionales
                            </div>
                        </div>

                        <button type="button" id="toggle_adicionales"
                            style="
                                padding:4px 10px;
                                border:1px solid #6c757d;
                                background:#fff;
                                color:#6c757d;
                                border-radius:5px;
                                font-size:11px;
                                font-weight:600;
                                cursor:pointer;
                                white-space:nowrap;
                            ">
                            Ocultar
                        </button>
                    </div>

                    <div id="body_adicionales" style="padding:10px;">
                        <p style="font-size:11px; color:#666; margin:0 0 7px 0; line-height:1.35;">
                            Estas columnas no son obligatorias.
                        </p>

                        <div style="
                            display:grid;
                            grid-template-columns:1fr;
                            gap:5px;
                            font-size:11px;
                            background:#fff;
                            padding:8px;
                            border-radius:6px;
                            border:1px solid #e0e0e0;
                            color:#374151;
                        ">
                            <div>Lugar de expedici&oacute;n</div>
                            <div>Departamento de expedici&oacute;n</div>
                            <div>Paga por n&oacute;mina</div>
                            <div>Porcentaje padre</div>
                            <div>Porcentaje madre</div>
                            <div>Porcentaje acudiente</div>
                        </div>

                        <p style="font-size:10px; color:#777; margin:7px 0 0 0;">
                            Nota: Puedes omitir estas columnas si no aplican.
                        </p>
                    </div>
                </div>
            </div>

            <!-- MODAL OK -->
            <div id="modal_excel_ok" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:99999;">
                <div style="
                    background:#fff;
                    width:360px;
                    max-width:92%;
                    margin:12% auto;
                    padding:20px;
                    border-radius:10px;
                    text-align:center;
                    box-sizing:border-box;
                ">
                    <h3 style="color:#28a745; margin-top:0;">Archivo cargado</h3>
                    <p>El archivo Excel se carg&oacute; correctamente.</p>
                    <button id="cerrar_modal_excel"
                        style="padding:7px 18px; background:#007BFF; color:white; border:none; border-radius:6px; cursor:pointer;">
                        Aceptar
                    </button>
                </div>
            </div>

            <!-- MODAL ERROR -->
            <div id="modal_excel_error" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:99999;">
                <div style="
                    background:#fff;
                    width:380px;
                    max-width:92%;
                    margin:12% auto;
                    padding:20px;
                    border-radius:10px;
                    text-align:center;
                    box-sizing:border-box;
                ">
                    <h3 style="color:#dc3545; margin-top:0;">Error en el Excel</h3>
                    <p id="msg_excel_error"></p>
                    <button id="cerrar_modal_excel_error"
                        style="padding:7px 18px; background:#dc3545; color:white; border:none; border-radius:6px; cursor:pointer;">
                        Aceptar
                    </button>
                </div>
            </div>

        </div>
    </div>
<?php
    $mod = ob_get_clean();
    $xres->addAssign('modales','innerHTML',utf8_encode($mod));
    $xres->addScript("$('#capa_nivel0_doc').scrollTop(0);");

    $xres->addScript("
        $('#capa_nivel0_doc').dialog({
            title:'Cargar Excel de Preinscritos',
            height:560,
            width:900,
            modal:false,
            resizable:true,
            close:function(){
                $(this).dialog('destroy').remove();
            },
            open:function(){
                $(this).scrollTop(0);
                $('.ui-dialog').css('max-width','96vw');
            }
        });

        function mostrarModalExcelError(msg){
            $('#msg_excel_error').html(msg);
            $('#modal_excel_error').fadeIn(200);
        }

        let excelPreviewHTML = '';

        $(document).off('click','#btn_subir_excel').on('click','#btn_subir_excel',function(){

            let file = $('#archivo_excel')[0].files[0];
            if(!file){
                mostrarModalExcelError('Debe seleccionar un archivo Excel.');
                return;
            }

            let idAno = $('#id_ano_excel').val();
            if(!idAno){
                mostrarModalExcelError('No se recibi&oacute; el a&ntilde;o lectivo.');
                return;
            }

            let fd = new FormData();
            fd.append('archivo_excel', file);
            fd.append('id_pre', '".addslashes($id_pre)."');
            fd.append('id_ano', idAno);

            $.ajax({
                url:'cargar_excel.php',
                type:'POST',
                data:fd,
                processData:false,
                contentType:false,
                beforeSend:function(){
                    $('#resultado_excel').html('Procesando archivo...');
                    $('#btn_subir_excel').prop('disabled', true).css({
                        opacity:'0.7',
                        cursor:'not-allowed'
                    });
                },
                success:function(resp){
                    $('#resultado_excel').html('');
                    $('#btn_subir_excel').prop('disabled', false).css({
                        opacity:'1',
                        cursor:'pointer'
                    });

                    if(resp.indexOf('ERROR:') !== -1){
                        mostrarModalExcelError(resp.replace('ERROR:',''));
                        return;
                    }

                    excelPreviewHTML = resp;
                    $('#modal_excel_ok').fadeIn(200);
                },
                error:function(){
                    $('#resultado_excel').html('');
                    $('#btn_subir_excel').prop('disabled', false).css({
                        opacity:'1',
                        cursor:'pointer'
                    });
                    mostrarModalExcelError('Error al subir el archivo.');
                }
            });
        });

        $(document).off('click','#cerrar_modal_excel').on('click','#cerrar_modal_excel',function(){
            $('#modal_excel_ok').fadeOut(200);
            $('#contenido_excel_modal').html(excelPreviewHTML);
            $('#modal_excel_preview').fadeIn(200);
        });

        $(document).off('click','#cerrar_modal_excel_error').on('click','#cerrar_modal_excel_error',function(){
            $('#modal_excel_error').fadeOut(200);
        });

        $(document).off('click','#modal_excel_preview').on('click','#modal_excel_preview',function(){
            $('#modal_excel_preview').fadeOut(200);
        });

        $(document).off('click','#modal_excel_box').on('click','#modal_excel_box',function(e){
            e.stopPropagation();
        });

        $(document).off('click','#cerrar_excel_preview').on('click','#cerrar_excel_preview',function(){
            $('#modal_excel_preview').fadeOut(200);
        });

        $(document).off('click','#toggle_diccionario').on('click','#toggle_diccionario',function(){
            $('#body_diccionario').stop(true,true).slideToggle(180);
            $(this).text($(this).text() === 'Ocultar' ? 'Mostrar' : 'Ocultar');
        });

        $(document).off('click','#toggle_adicionales').on('click','#toggle_adicionales',function(){
            $('#body_adicionales').stop(true,true).slideToggle(180);
            $(this).text($(this).text() === 'Ocultar' ? 'Mostrar' : 'Ocultar');
        });

        $(document).off('click','#toggle_detalle_campos').on('click','#toggle_detalle_campos',function(){
            $('#detalle_campos_excel').stop(true,true).slideToggle(180);
            $(this).text($(this).text() === 'Compactar campos' ? 'Mostrar campos' : 'Compactar campos');
        });
    ");

    return $xres->getXML();
}

function getDoc1($id_pre='',$id_ano='', $ano_texto=''){
    $xres = new xajaxResponse();
    ob_start();
    include ('../../bibliotecas/valida 1.0/biblio.php');
    global $con;
	?>
	
		<!-- MODAL CONTENEDOR DEL EXCEL -->
	<div id="modal_excel_preview" style="
	    display:none;
	    position:fixed;
	    inset:0;
	    background:rgba(0,0,0,.7);
	    z-index:999999;
	">
	    <div id="modal_excel_box" style="
	        background:#fff;
	        width:95%;
	        max-width:1200px;
	        margin:2% auto;
	        padding:20px;
	        border-radius:12px;
	        position:relative;
	    ">
	
	        <h3 style="margin-top:0;color:#1e3a8a;">
	            Vista previa del Excel
	        </h3>
	
	        <div id="contenido_excel_modal"
	             style="max-height:70vh; overflow:auto;">
	        </div>
	
	        <div style="text-align:right; margin-top:15px;">
				<button id="cerrar_excel_preview"
				    style="
				        position:absolute;
				        top:12px;
				        right:12px;
				        padding:6px 12px;
				        background:#ef4444;
				        color:#fff;
				        border:none;
				        border-radius:6px;
				        font-weight:600;
				        cursor:pointer;
				    ">
				    x
				</button>
	        </div>
	
	    </div>
	</div>
	
	<div id="capa_nivel0_doc" style="background:#f4f6f9; padding:15px;">
   		<div style="width:95%; margin:0 auto; font-family:'Segoe UI', Arial, sans-serif; color:#333;">
			<!-- INFO AÑO SELECCIONADO -->
			<div style="
			    background:#e9f5ff;
			    border:1px solid #b8daff;
			    color:#004085;
			    padding:10px;
			    margin:0 auto 10px auto;
			    max-width:250px;
			    border-radius:6px;
			    text-align:center;
			    font-size:13px;
			    font-weight:600;
			">
			    El a&ntilde;o que se va a subir es:<br>
			    <span style="font-size:15px; color:#0056b3;">
			        <?php echo htmlspecialchars($ano_texto); ?>
			    </span>
			</div>
			<!-- FORMULARIO DE SUBIDA -->
			<form id="form_excel" enctype="multipart/form-data"
			      style="max-width:250px; margin:10px auto; background:#fff; padding:8px; 
			             border-radius:6px; border:1px solid #ddd; text-align:center; 
			             box-shadow:0 1px 4px rgba(0,0,0,0.05); font-family:Arial, sans-serif;">
			
			    <label for="archivo_excel" style="display:block; font-weight:600; margin-bottom:4px; 
			                                      font-size:12px; color:#333;">
			        Selecciona tu archivo Excel
			    </label>
			
			    <input type="file" id="archivo_excel" accept=".xls"
			           style="padding:4px; border-radius:3px; border:1px solid #ccc; width:100%; 
			                  cursor:pointer; font-size:12px; transition: all 0.2s ease;"
			           onmouseover="this.style.borderColor='#28a745';"
			           onmouseout="this.style.borderColor='#ccc';">
			
			    <button type="button" id="btn_subir_excel"
			            style="margin-top:6px; padding:5px 15px; background:#28a745; color:#fff; 
			                   border:none; border-radius:4px; font-size:12px; cursor:pointer; 
			                   transition: all 0.3s ease; box-shadow:0 1px 2px rgba(0,0,0,0.1);">
			        Subir y Cargar
			    </button>
			
			</form>

			<input type="hidden" id="id_ano_excel" value="<?php echo htmlspecialchars($id_ano); ?>">
			
			<script>
			    const btn = document.getElementById('btn_subir_excel');
			    btn.addEventListener('mouseover', () => btn.style.background = '#218838');
			    btn.addEventListener('mouseout', () => btn.style.background = '#28a745');
			</script>
			
			<div id="resultado_excel" style="margin-top:6px; text-align:center; font-weight:600; font-size:13px; color:#555;"></div>
    

    		<div style="display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap;">
				<!-- DICCIONARIO DE COLUMNAS COMPACTO -->
				<div style="margin-top:10px; border:1px solid #ccc; padding:8px; border-radius:8px; background:#fafafa; font-family:sans-serif; font-size:11px;">
    				<h3 style="margin:0 0 6px 0; border-bottom:1px solid #17a2b8; padding-bottom:4px; color:#17a2b8; font-size:13px;">
        				Cargar archivo Excel
    				</h3>
    				<p style="margin:0 0 6px 0; color:#555;">
        				El archivo Excel debe contener los encabezados indicados a continuaci&oacute;n. 
        				Cada encabezado <strong>debe escribirse exactamente igual</strong> (sin espacios ni tildes).
    				</p>
    				<p style="margin:0 0 8px 0; color:#555;">
    				    La informaci&oacute;n se divide en dos grupos: 
    				    <strong>datos del estudiante</strong> y <strong>datos de padres madres y acudiente</strong>.
    				</p>
					<div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; font-size:11px;">
											
					  <!-- ESTUDIANTE -->
						<div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:4px; background:#fff; padding:3px; border-radius:4px; border:1px solid #ccc; font-size:11px;">
    					<div style="grid-column:1/-1; font-weight:600; color:#17a2b8; margin-bottom:2px;">Datos del estudiante</div>

    					<div><strong>docE</strong>: N&uacute;mero de documento</div>
    					<div><strong>tipoE</strong>: Tipo de documento</div>
    					<div><strong>fecN</strong>: Fecha de nacimiento</div>
    					<div><strong>ciuN</strong>: Ciudad de nacimiento</div>
    					<div><strong>anio</strong>: A&ntilde;o acad&eacute;mico</div>
    					<div><strong>sede</strong>: Sede educativa</div>
    					<div><strong>gra</strong>: Grado o curso</div>
    					<div><strong>jor</strong>: Jornada </div>
    					<div><strong>dirE</strong>: Direcci&oacute;n de residencia</div>
    					<div><strong>barrE</strong>: Barrio o localidad</div>
    					<div><strong>tel</strong>: Tel&eacute;fono fijo</div>
    					<div><strong>cel</strong>: Tel&eacute;fono celular</div>
    					<div><strong>mail</strong>: Correo electr&oacute;nico</div>
    					<div><strong>nomE</strong>: Nombres del estudiante</div>
    					<div><strong>apeE</strong>: Apellidos del estudiante</div>
    					<div><strong>sexo</strong>: Sexo (M/F)</div>
    					<div><strong>gs</strong>: Grupo sanguneo</div>
    					<div><strong>eps</strong>: EPS o seguro de salud</div>
    					<div><strong>sis</strong>: N&uacute;mero de SISBEN</div>
    					<div><strong>colP</strong>: Colegio o instituci&oacute;n anterior</div>
					</div>
											
					  <!-- PADRES/ACUDIENTE -->
						<div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:2px; background:#fff; padding:3px; border-radius:4px; border:1px solid #ccc;">

						    <!-- TITULO GENERAL -->
						    <div style="grid-column:1/-1; font-weight:600; color:#6c757d; margin-bottom:2px;">Padres / Madre / Acudiente</div>

						    <!-- PADRE -->
						    <div style="grid-column:1/-1; font-weight:600; color:#007bff; margin-bottom:2px;">Padre</div>
						    <div><strong>docP</strong></div><div>Documento</div>
						    <div><strong>tipoP</strong></div><div>Tipo Doc</div>
						    <div><strong>nomP</strong></div><div>Nombre</div>
						    <div><strong>apeP</strong></div><div>Apellido</div>
						    <div><strong>dirP</strong></div><div>Direcci&oacute;n</div>
						    <div><strong>telP</strong></div><div>Telefono</div>
						    <div><strong>profP</strong></div><div>Profesion</div>

						    <!-- MADRE -->
						    <div style="grid-column:1/-1; font-weight:600; color:#e83e8c; margin-bottom:2px;">Madre</div>
						    <div><strong>docM</strong></div><div>Documento</div>
						    <div><strong>tipoM</strong></div><div>Tipo Doc</div>
						    <div><strong>nomM</strong></div><div>Nombre</div>
						    <div><strong>apeM</strong></div><div>Apellido</div>
						    <div><strong>dirM</strong></div><div>Direcci&oacute;n</div>
						    <div><strong>telM</strong></div><div>Tel&eacute;fono</div>
						    <div><strong>profM</strong></div><div>Profesi&oacute;n</div>

						    <!-- ACUDIENTE -->
						    <div style="grid-column:1/-1; font-weight:600; color:#28a745; margin-bottom:2px;">Acudiente</div>
						    <div><strong>docA</strong></div><div>Documento</div>
						    <div><strong>tipoA</strong></div><div>Tipo Doc</div>
						    <div><strong>nomA</strong></div><div>Nombre</div>
						    <div><strong>apeA</strong></div><div>Apellido</div>
						    <div><strong>dirA</strong></div><div>Direcci&oacute;n</div>
						    <div><strong>telA</strong></div><div>Tel&eacute;fono</div>
						    <div><strong>profA</strong></div><div>Profesi&oacute;n</div>

						</div>
											
					  <p style="margin-top:3px; color:#777; font-size:10px;">Nota: El encabezado debe existir aunque el dato est&eacute; vac&iacute;o.</p>
					</div>

        		<!-- DATOS ADICIONALES (OPCIONAL) -->
				<div style="flex:1; min-width:300px; border:1px dashed #bbb; padding:12px; border-radius:10px; background:#fcfcfc;">

				    <h3 style="margin-top:0; border-bottom:1px solid #6c757d; padding-bottom:5px; color:#6c757d;">
				        Datos Adicionales (Opcional)
				    </h3>

				    <p style="font-size:12px; color:#666; margin-bottom:8px;">
				        Estas columnas <strong>no son obligatorias</strong>. Si se incluyen en el Excel,
				        deben escribirse exactamente como se muestra.
				    </p>

				    <div style="
				        display:grid;
				        grid-template-columns:100px 1fr;
				        gap:6px 12px;
				        font-size:12px;
				        background:#fff;
				        padding:10px;
				        border-radius:6px;
				        border:1px solid #e0e0e0;
				    ">
				        <div><strong>lugExp</strong></div><div>Lugar de expedicion del documento</div>
				        <div><strong>depExp</strong></div><div>Departamento de expedicion del documento</div>
				        <div><strong>nomina</strong></div><div>Paga por nomina (SI / NO)</div>
				        <div><strong>porcP</strong></div><div>Porcentaje financiero del padre</div>
				        <div><strong>porcM</strong></div><div>Porcentaje financiero de la madre</div>
				        <div><strong>porcA</strong></div><div>Porcentaje financiero del acudiente</div>
				    </div>

				    <p style="font-size:11px; color:#777; margin-top:8px;">
				        Nota: Puede omitir estas columnas si no aplican.
				    </p>		
				</div>
			</div>	
		</div>					

    <!-- MODAL OK -->
    <div id="modal_excel_ok" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:99999;">
        <div style="background:#fff; width:360px; margin:15% auto; padding:20px; border-radius:10px; text-align:center;">
            <h3 style="color:#28a745;">Archivo cargado</h3>
            <p>El archivo Excel se carg&oacute; correctamente.</p>
            <button id="cerrar_modal_excel"
                style="padding:7px 18px; background:#007BFF; color:white; border:none; border-radius:6px;">
                Aceptar
            </button>
        </div>
    </div>
    <!-- MODAL ERROR -->
    <div id="modal_excel_error" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:99999;">
        <div style="background:#fff; width:380px; margin:15% auto; padding:20px; border-radius:10px; text-align:center;">
            <h3 style="color:#dc3545;">Error en el Excel</h3>
            <p id="msg_excel_error"></p>
            <button id="cerrar_modal_excel_error"
                style="padding:7px 18px; background:#dc3545; color:white; border:none; border-radius:6px;">
                Aceptar
            </button>
        </div>
    </div>
</div>
	<?php
    	$mod = ob_get_clean();
    	$xres->addAssign('modales','innerHTML',utf8_encode($mod));
		$xres->addScript("$('#capa_nivel0_doc').scrollTop(0);");

    	$xres->addScript("

		 $('#capa_nivel0_doc').dialog({
    	title:'Cargar Excel de Preinscritos',
    	height:600,   // no tan grande, suficiente para ver contenido
    	width:750,    // un poco más estrecho
    	modal:false,
    	close:function(){
    	    $(this).dialog('destroy').remove();
    	},
    	open: function() {
    	    // Que siempre empiece al inicio
    	    $(this).scrollTop(0);
    	}
			});

        function mostrarModalExcelError(msg){
            $('#msg_excel_error').html(msg);
            $('#modal_excel_error').fadeIn(200);
        }
		
		let excelPreviewHTML = '';
        // SUBIR EXCEL
        $(document).off('click','#btn_subir_excel').on('click','#btn_subir_excel',function(){

		    let file = $('#archivo_excel')[0].files[0];
		    if(!file){
		        mostrarModalExcelError('Debe seleccionar un archivo Excel.');
		        return;
		    }

		    let idAno = $('#id_ano_excel').val();
		    if(!idAno){
		        mostrarModalExcelError('No se recibió el año lectivo.');
		        return;
		    }

		    let fd = new FormData();
		    fd.append('archivo_excel', file);
		    fd.append('id_pre', '<?php echo $id_pre ?>');
		    fd.append('id_ano', idAno);

		    $.ajax({
		        url:'cargar_excel.php',
		        type:'POST',
		        data:fd,
		        processData:false,
		        contentType:false,
		        beforeSend:function(){
		            $('#resultado_excel').html('Procesando archivo...');
		        },
		        success:function(resp){

		            $('#resultado_excel').html('');

		            if(resp.indexOf('ERROR:') !== -1){
		                mostrarModalExcelError(resp.replace('ERROR:',''));
		                return;
		            }

		            excelPreviewHTML = resp;
		            $('#modal_excel_ok').fadeIn(200);
		        },
		        error:function(){
		            mostrarModalExcelError('Error al subir el archivo.');
		        }
		    });
		});

		$(document).on('click','#cerrar_modal_excel',function(){

    		// Cerrar modal OK
    		$('#modal_excel_ok').fadeOut(200);

    		// Inyectar la tabla
    		$('#contenido_excel_modal').html(excelPreviewHTML);

    		// Mostrar modal de vista previa
    		$('#modal_excel_preview').fadeIn(200);
		});					

        $(document).on('click','#cerrar_modal_excel_error',function(){
            $('#modal_excel_error').fadeOut(200);
        });

		// Click en fondo oscuro → cerrar
		$(document).off('click', '#modal_excel_preview')
		.on('click', '#modal_excel_preview', function () {
		    $('#modal_excel_preview').fadeOut(200);
		});

		// Click dentro de la caja → NO cerrar
		$(document).off('click', '#modal_excel_box')
		.on('click', '#modal_excel_box', function (e) {
		    e.stopPropagation();
		});

		// Botón ✕
		$(document).on('click','#cerrar_excel_preview',function(){
		    $('#modal_excel_preview').fadeOut(200);
		});
    ");

    return $xres->getXML();
}


// ok
function obtenerIdProfesionSeguro($con, $nombre) {

    $nombre = trim($nombre);

    if ($nombre === '') {
        return "NULL";
    }

    $nombre = strtoupper($nombre);

    $id = $con->result(
        $con->query("SELECT id_pro FROM profesion WHERE UPPER(des_pro) = '$nombre' LIMIT 1")
    );

    if (!$id) {
        return "NULL";
    }

    return intval($id);
}


function guardarExcelPreinscritosNuevo($datos = []) {
    $xres = new xajaxResponse();

    if (empty($datos)) {
        $xres->addAlert("No se recibieron datos");
        return $xres->getXML();
    }

    try {
        $con = new MySQLConex();
        $con->abrir('../../Connections/datos_conex.php');
        $con->query("SET NAMES 'utf8'");
        $con->query("START TRANSACTION");

        $creados = 0;
        $errores = 0;

        // Obtener el id del año lectivo actual
        $idAnoActual = $con->result(
            $con->query("SELECT id_ano FROM anolectivo WHERE actual='s' LIMIT 1")
        );

        foreach ($datos as $fila) {
            // Validar campos obligatorios
            if (empty($fila['doc_pre']) || empty($fila['nom_pre']) || empty($fila['ape_pre'])) {
                $errores++;
                continue;
            }

            /* ================= CONVERTIR A IDs ================= */
            $valorGrado = trim($fila['cod_gra']);
            $cod_gra = null;

            if ($valorGrado !== '') {
                $mapGrados = [
                    0  => 'PREJARDIN', -1 => 'JARDIN', -2 => 'TRANSICION',
                    1  => 'PRIMERO', 2 => 'SEGUNDO', 3 => 'TERCERO',
                    4  => 'CUARTO', 5 => 'QUINTO', 6 => 'SEXTO',
                    7  => 'SEPTIMO', 8 => 'OCTAVO', 9 => 'NOVENO',
                    10 => 'DECIMO', 11 => 'ONCE'
                ];

                if (is_numeric($valorGrado)) {
                    $numero = intval($valorGrado);
                    if (isset($mapGrados[$numero])) {
                        $texto = $mapGrados[$numero];
                        $cod_gra = $con->result(
                            $con->query("SELECT cod_gra FROM grado WHERE UPPER(des_gra) = '$texto' LIMIT 1")
                        );
                    }
                } else {
                    $texto = strtoupper($valorGrado);
                    $cod_gra = $con->result(
                        $con->query("SELECT cod_gra FROM grado WHERE UPPER(des_gra) = '$texto' LIMIT 1")
                    );
                }
            }

            $id_jor = $con->result(
                $con->query("SELECT id_jor FROM jornada WHERE UPPER(des_jor) LIKE '%".strtoupper(trim($fila['id_jor']))."%' LIMIT 1")
            );

            $id_sed = $con->result(
                $con->query("SELECT id_sed FROM sede WHERE UPPER(nom_sed) LIKE '%".strtoupper(trim($fila['id_sed']))."%' LIMIT 1")
            );

            $cod_ciu = $con->result(
                $con->query("SELECT cod_ciu FROM ciudad WHERE UPPER(nom_ciu)='".strtoupper(trim($fila['ciu_pre']))."'")
            );

            // Tipo documento
            switch (strtoupper(trim($fila['tipo_pre']))) {
                case 'CC': $cod_tid = 1; break;
                case 'TI': $cod_tid = 2; break;
                case 'RC': $cod_tid = 3; break;
                case 'CE': $cod_tid = 4; break;
                case 'PASAPORTE':
                case 'PA': $cod_tid = 5; break;
                case 'NUIP': $cod_tid = 6; break;
                default: $cod_tid = 1;
            }

            /* ================= EPS ================= */
            $eps_id = null;
            if (!empty($fila['eps_pre'])) {
                $eps_id = $con->result(
                    $con->query("SELECT id_eps FROM eps WHERE UPPER(des_eps)='".strtoupper(trim($fila['eps_pre']))."'")
                );
            }

            if (!$cod_gra || !$id_jor || !$id_sed || !$cod_ciu) {
                $errores++;
                continue;
            }

            /* ================= DETERMINAR EL ID DEL AÑO ================= */
            $id_ano = $idAnoActual; // por defecto año actual
            if (!empty($fila['id_ano'])) {
                $idAnoExcel = $con->result(
                    $con->query("SELECT id_ano FROM anolectivo WHERE ano='".intval($fila['id_ano'])."' LIMIT 1")
                );
                if ($idAnoExcel) $id_ano = $idAnoExcel;
            }

            /* ================= CREAR PADRE ================= */
            $id_per1 = null;
            if (!empty($fila['doc_padre'])) {
              $id_pro_p = obtenerIdProfesionSeguro($con, $fila['id_pro_p']);
                $id_per1 = $con->result(
                    $con->query("SELECT id_per_pre FROM per_pre WHERE documento='".addslashes($fila['doc_padre'])."'")
                );

                if (!$id_per1) {
                    $padre = per_pre::crear(
                        $con,
                        $cod_tid,
                        strtoupper($fila['nom_padre']),
                        strtoupper($fila['ape_padre']),
                        $fila['dir_padre'],
                        $fila['tel_padre'],
                        $fila['doc_padre'],
                        $id_pro_p
                    );
                    $id_per1 = $padre->id_per_pre;
                }
            }

            /* ================= CREAR MADRE ================= */
            $id_per2 = null;
            if (!empty($fila['doc_madre'])) {
				$id_pro_m = obtenerIdProfesionSeguro($con, $fila['id_pro_m']);
                $id_per2 = $con->result(
                    $con->query("SELECT id_per_pre FROM per_pre WHERE documento='".addslashes($fila['doc_madre'])."'")
                );

                if (!$id_per2) {
                    $madre = per_pre::crear(
                        $con,
                        $cod_tid,
                        strtoupper($fila['nom_madre']),
                        strtoupper($fila['ape_madre']),
                        $fila['dir_madre'],
                        $fila['tel_madre'],
                        $fila['doc_madre'],
                        $id_pro_m
                    );
                    $id_per2 = $madre->id_per_pre;
                }
            }

            /* ================= CREAR ACUDIENTE ================= */
            $id_per3 = null;
            if (!empty($fila['doc_acu'])) {
               $id_pro_a = obtenerIdProfesionSeguro($con, $fila['id_pro_a']);
                $id_per3 = $con->result(
                    $con->query("SELECT id_per_pre FROM per_pre WHERE documento='".addslashes($fila['doc_acu'])."'")
                );

                if (!$id_per3) {
                    $acu = per_pre::crear(
                        $con,
                        $cod_tid,
                        strtoupper($fila['nom_acu']),
                        strtoupper($fila['ape_acu']),
                        $fila['dir_acu'],
                        $fila['tel_acu'],
                        $fila['doc_acu'],
                        $id_pro_a
                    );
                    $id_per3 = $acu->id_per_pre;
                }
            }

            /* ================= DEFINIR ID PREINSCRITO ================= */
            $id_pre = trim($fila['doc_pre']); // <-- ID será el documento

            // Eliminar preinscrito existente con ese ID
            $existe = $con->result($con->query("SELECT id_pre FROM preinscrito WHERE id_pre='$id_pre'"));
            if ($existe) {
                $preins_old = new preinscrito($id_pre);
                $preins_old->setConex($con);
                $preins_old->eliminar();
            }

            /* ================= CREAR PREINSCRITO ================= */
            $preins = preinscrito::crear(
                $con,
                $cod_gra,
                $cod_tid,
                $id_ano,
                strtoupper($fila['ape_pre']),
                strtoupper($fila['nom_pre']),
                $fila['fec_nac'],
                $fila['doc_pre'],
                date('Y-m-d'),
                $cod_ciu,
                $fila['col_pro'],
                $fila['dir_cor'],
                $fila['tel_con'],
                $fila['doc_pre'],
                $fila['barrio'],
                $fila['sisben'],
                $id_jor,
                $id_sed,
                $fila['sex_pre'],
                $fila['gs_pre'],
                $eps_id,
                $fila['tel2_pre'],
                $fila['mai_pre'],
                '',
            );

            preinscrito::setPerPre($con, $id_per1, $id_per2, $id_per3, $id_pre);

            $creados++;
        }

        $con->query("COMMIT");

        /* ================= CERRAR MODAL DEL EXCEL ================= */
        $xres->addScript("
            if ($('#capa_nivel0_doc').length) {
                $('#capa_nivel0_doc').dialog('close');
            }
        ");

        /* ================= FORZAR AÑO 2024 ================= */
        $xres->addScript("
            if ($('#id_ano').length) {
                $('#id_ano').val('9').trigger('change');
            }
        ");

        /* ================= LISTAR AUTOMÁTICAMENTE ================= */
        $xres->addScript("
            xajax_listar(xajax.getFormValues('formF'), 1);
        ");

        /* ================= MENSAJE FINAL ================= */
        $xres->addAlert("Proceso finalizado\n\nCreados: $creados\nErrores: $errores");

    } catch (Exception $e) {
        $con->query("ROLLBACK");
        $xres->addAlert("Error: " . $e->getMessage());
    }

    return $xres->getXML();
}


function guardarExcelPreinscritosNuevo1($datos=[]) {
    $xres = new xajaxResponse();

    if(empty($datos)){
        $xres->addAlert("No se recibieron datos");
        return $xres->getXML();
    }

    // Convertir los datos a texto legible
    $contenido = print_r($datos, true);

    // Crear un archivo temporal para descargar
    $nombreArchivo = "debug_guardar_" . date("Ymd_His") . ".txt";

    // Generar script para forzar descarga en el navegador
    $js = <<<JS
    var blob = new Blob([`$contenido`], { type: 'text/plain' });
    var link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = '$nombreArchivo';
    link.click();
JS;

    $xres->addScript($js);
    $xres->addAlert("✅ Datos listos para descargar. Revisa tu carpeta de descargas.");

    return $xres->getXML();
}

//
