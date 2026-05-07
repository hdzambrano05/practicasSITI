<?php 
//siti
header("Cache-Control: no-cache, must-revalidate");
require_once ('../../sased/clases/bd/MySQLConex.php');
require_once ('../../sased/clases/base/preinscrito.php');
require_once ('../../clases/base/alumno.php');
$con = new MySQLConex();
$con->abrir("../../Connections/datos_conex.php");


function ingresoPreinscrito($form){
	$xres = new xajaxResponse();
	$con = new MySQLConex();
	$con->abrir('../../Connections/datos_conex.php');
	$nom1=''; $nom2=''; $nom3='';
	if($form['id_per_pre1'] != NULL){
		$id_per_pre = $form['id_per_pre1'];
		$per1 = per_pre::recuperar_doc($con, $id_per_pre);
		if($per1!=NULL){
			$nom1=$per1->nom_per." ".$per1->ape_per;			
		}else{
			$xres->addAlert("Debe ingresar los datos del padre.");
			//return $xres->getXML();
		}
	}
	
	if($form['id_per_pre2'] != NULL){
		$id_per_pre = $form['id_per_pre2'];
		$per2 = per_pre::recuperar_doc($con, $id_per_pre);
		if($per2!=NULL){
			$nom2=$per2->nom_per." ".$per2->ape_per;
		}else{
			$xres->addAlert("Debe ingresar los datos de la madre.");
			//return $xres->getXML();
		}
	}
	
	if($form['id_per_pre3'] != NULL){
		$id_per_pre = $form['id_per_pre3'];
		$per3 = per_pre::recuperar_doc($con, $id_per_pre);
		if($per3!=NULL){
			$nom3=$per3->nom_per." ".$per3->ape_per;
		}else{
			$xres->addAlert("Debe ingresar los datos del acudiente.");
			//return $xres->getXML();
		}
	}	
	if($nom1!='' && $nom2!='' && $nom3 !='')			
		$xres->addScript("asignar('".$nom1."','".$nom2."','".$nom3."');");
	return $xres->getXML();
	
}

function asignarPreinscrito($form,$band=0){
	$xres = new xajaxResponse();
	try{
		$con = new MySQLConex();
		$con->abrir('../../Connections/datos_conex.php');
		$con->query("SET NAMES 'utf8'");
		$pen=modulos::recuperar($con,4);
		$fBloq=$con->fetch($con->query("SELECT * FROM lista_negra WHERE ced_per='{$form['doc_pre']}' AND est_per='a'"));
		if($fBloq['ced_per']){
			$xres->addScript("alert('EL documento de identidad ingresado se encuentra bloqueado: ".($fBloq['obs_per'])."');");
			return ($xres->getXML());
		}
		$id_pre = trim(utf8_decode($form['id_pre']));		
		if($band==0)
			if($id_pre!='')$ver= preinscrito::exist($con, $id_pre);
			else $ver='false';
		else $ver='false';
		if ($ver=='true'){ 
			$xres->addScript("confirmar_ing();");
		}else{
			$alu=NULL;
			if($form['id_pre']!='')$alu =alumno::recuperar($con,$form['id_pre']);
			if($alu){
				$qAC="SELECT * FROM alumcurso WHERE id_alu={$alu->id_alu}";
				$fAC=$con->fetch($con->query($qAC));
			}
			if(!$fAC['id_alu']){
				if($alu){
					$con->query("DELETE FROM alum_grado WHERE id_alu={$alu->id_alu}");
					$alu->setConex($con);
					$alu->eliminar();
				}
				$documento = $form['doc_pre'];
				$cod_tid = $form['des_tid'];				
				$id_per_pre1 = $form['id_per_pre1'];
				$id_per_pre2 = $form['id_per_pre2'];
				$id_per_pre3 = $form['id_per_pre3'];
				if(isset($id_per_pre1))$id_per1 = $con->result($con->query("SELECT id_per_pre FROM per_pre WHERE documento = '$id_per_pre1'"));
				if(isset($id_per_pre2))$id_per2 = $con->result($con->query("SELECT id_per_pre FROM per_pre WHERE documento = '$id_per_pre2'"));
				if(isset($id_per_pre3))$id_per3 = $con->result($con->query("SELECT id_per_pre FROM per_pre WHERE documento = '$id_per_pre3'"));
				
				$query = "SELECT dir_per, tel_per FROM per_pre WHERE documento = $id_per_pre3;";
				$resPer = $con->query($query);
				$filaPer = $con->fetchAssoc($resPer);
				$cod_gra = $form['cod_gra'];
				$id_ano = $form['id_ano'];
				//$ape_pre = (trim(mb_strtoupper(($form['ape_pre']),'UTF-8')));
				$ape_pre = (trim(strtoupper(($form['ape_pre']))));
				$nom_pre = (trim(strtoupper(($form['nom_pre']))));
				$fec_nac = $form['fec_nac'];			
				$fec_ins = date('Y-m-d');
				$cod_ciu = $form['cna_per'];
				$col_pro = strtoupper(trim(($form['col_pro']." ")));
				$dir_pre = strtoupper(trim(($form['dir_cor'])));
				$bar_pre = strtoupper(trim(($form['cod_bar'])));
				$tel_pre = strtoupper(trim(($form['tel_con'])));
				$sis_pre = trim(($form['sisben']));
				$id_jor = trim(($form['id_jor']));
				$id_sed = $form['id_sed'];
				$sex_pre=$form['sex_pre'];
				$gs_pre=$form['gs_pre'];
				$eps_pre=$form['eps_pre'];
				$tel2_pre=$form['tel2_pre'];
				$mai_pre=$form['mai_pre'];
				$ufo_alu=$form['ufo_alu'];
				
				if($id_pre!=''){
					$preins= new preinscrito($id_pre, $cod_gra, $cod_tid, $id_ano, $ape_pre, $nom_pre, $fec_nac, $documento, $fec_ins, $cod_ciu, $col_pro, $dir_pre, $tel_pre, $bar_pre, $sis_pre, '','','',$id_jor,$id_sed,$sex_pre,$gs_pre,$eps_pre,$tel2_pre,$mai_pre);		
					$preins->setConex($con);
					$preins->eliminar();
				}				
				$preins = preinscrito::crear($con,$cod_gra,$cod_tid,$id_ano,$ape_pre,$nom_pre,$fec_nac, $documento,$fec_ins,$cod_ciu,$col_pro,$dir_pre,$tel_pre,$id_pre,$bar_pre,$sis_pre,$id_jor,$id_sed,$sex_pre,$gs_pre,$eps_pre,$tel2_pre,$mai_pre);	
				$id_pre=$preins->id_pre;
				if($ufo_alu){
					$ext=explode('.',$ufo_alu);
					$ext=end($ext);
					$ufo_alu_n="{$_SESSION['DIRECTORIO_ FOTOS_ ESTUDIANTES']}{$id_pre}_pre.{$ext}";
					@rename("../$ufo_alu","../../{$ufo_alu_n}");
					$preins->ufo_alu="../{$ufo_alu_n}";
					$preins->setConex($con);
					$preins->actualizar();
				}
				/*print_r($id_pre);
				print_r('-----');
				print_r(trim(utf8_decode($form['id_pre'])));
				exit;*/
				$id_preinsc=trim(utf8_decode($form['id_pre']));
				if($id_preinsc){
					$id_preinsc=trim(utf8_decode($form['id_pre']));
				}else{
					$id_preinsc=$preins->id_pre;
				}
				preinscrito::setPerPre($con, $id_per1, $id_per2, $id_per3, $id_preinsc);
				
				$xqueryx="select est_mod from modulos where des_mod like 'SASED MATRICULAS'";
				$xxresxx = $con->query($xqueryx);
				$zzfilazz = $con->fetch($xxresxx);
				$xxxmensaje="El estudiante ha sido ingresado";
				if ($zzfilazz){
					$query = "SELECT actual from  anolectivo where id_ano= ".$id_ano;
					$anio_es_actual = $con->result($con->query($query),0,0);
					if ($zzfilazz['est_mod']=='n' &&  $anio_es_actual=='s'){
						$xxxmensaje="El estudiante ha sido ingresado y activado";
						$preins->setConex($con);
						$preins->activar(1,$pen);
					}
				}			
				$xres->addAlert($xxxmensaje);
				//$xres->addScript("form1.reset();");	
				$xres->addScript("xajax_listar(xajax.getFormValues('formF'));");
				$xres->addScript('$("#dialog:ui-dialog").dialog("destroy");$("#capa_nivel0").remove();');
				//$xres->addAlert($id_pre);
				//$xres->addScript("xajax_getForm('".$id_pre."');");
			}else{
				$xres->addAlert("El estudiante: ".$alu->ape_per.' '.$alu->nom_per." se encuentra asignado a un curso. No es posible realizar cambios.");	
			}
		}	
	}catch(Exception $e){
		throw($e);
		return NULL;
	}
	if($preins->id_pre)$xres->addScript("$('#capa_nivel0').remove( );$('#dialog:ui-dialog').dialog('destroy');xajax_getForm('".$preins->id_pre."');");
	return $xres->getXML();
}

function validarDocumento($doc, $tid){
	$xres = new xajaxResponse();
	try{
		$con = new MySQLConex();
		$con->abrir('../../Connections/datos_conex.php');
		$pre= preinscrito::recuperarConDoc($con, $doc,$tid);
		if($pre != NULL){
			//$xres->addAlert('Este documento ya está registrado en los estudiantes inscritos');
			$xres->addScript("document.getElementById('id_pre').value='".$pre->id_pre."'");
			$xres->addScript("document.getElementById('id_ano').value='".$pre->id_ano."'");
			$xres->addScript("document.getElementById('cod_gra').value='".$pre->cod_gra."'");
			$xres->addScript("document.getElementById('doc_pre').value='".$pre->documento."'");
			$xres->addScript("document.getElementById('des_tid').value='".$pre->cod_tid."'");
			$xres->addScript("document.getElementById('nom_pre').value='".($pre->nom_pre)."'");
			$xres->addScript("document.getElementById('ape_pre').value='".($pre->ape_pre)."'");
			$xres->addScript("document.getElementById('fec_nac').value='".$pre->fec_nac."'");
			$arrciu= explode(':',$pre->cod_ciu);
			$xres->addScript("document.getElementById('nom_ciu').value='".$arrciu[0]."'");
			$xres->addScript("document.getElementById('cna_per').value='".$arrciu[1]."'");
			$xres->addScript("document.getElementById('dir_cor').value='".($pre->dir_pre)."'");
			$xres->addScript("document.getElementById('cod_bar').value='".($pre->bar_pre)."'");
			$xres->addScript("document.getElementById('tel_con').value='".$pre->tel_pre."'");
			$xres->addScript("document.getElementById('sisben').value='".$pre->sisben_pre."'");
			$xres->addScript("document.getElementById('col_pro').value='".$pre->col_pro."'");
			$xres->addScript("document.getElementById('id_per_pre1').value='".$pre->pad."'");
			$xres->addScript("document.getElementById('id_per_pre2').value='".$pre->mad."'");
			$xres->addScript("document.getElementById('id_per_pre3').value='".$pre->acu."'");
			$xres->addScript("document.getElementById('id_jor').value='".$pre->id_jor."'");
			$xres->addScript("document.getElementById('cod_gra').value='".$pre->cod_gra."'");
			$xres->addScript("document.getElementById('id_sed').value='".$pre->id_sed."'");
			$codigo=preg_replace("/[A-�]/", "", $doc);
			$xres->addScript("if(document.getElementById('id_pre').value=='') document.getElementById('id_pre').value='".$codigo."'");
		}
		else
		{
			$codigo=preg_replace("/[A-�]/", "", $doc);
			$xres->addScript("if(document.getElementById('id_pre').value=='') document.getElementById('id_pre').value='".$codigo."'");
		}
		$con->cerrar();	
	}catch(Exception $e){
		throw($e);
		return NULL;
	}
	return ($xres->getXML());
}

function verifPadre($id_per_pre){
	$xres = new xajaxResponse();
	if($id_per_pre!=''){
		try{
			$con = new MySQLConex();
			$con->abrir('../../Connections/datos_conex.php');
			//Compruebo existencia de padres
			$per = per_pre::recuperar_doc($con, $id_per_pre);
			if($per != NULL){
				if($id_per_pre=='0') $nom="Este item ya está registrado en el sistema";				
				else $nom="Los datos de ".$per->nom_per." ".$per->ape_per." ya estan en el sistema.";
				$xres->addAlert($nom);
			}
			else{
				$xres->addScript("showPopWin('modal_per_pre.php?id_per_pre=$id_per_pre',450,250,nada);");	
			}
			$con->cerrar();		
		}catch(Exception $e){
			throw($e);
			return NULL;
		}
	}else{
		$xres->addAlert("Debe ingresar un valor");
	}	
	return $xres->getXML();
}
?><?php
function cambiaPais($cod_pai) {
	$xres = new xajaxResponse();
	if($cod_pai==-1){
		$xres->addScript("get('cod_dep').options.length = 0;");		
		$xres->addScript("get('cod_dep').options[0]=new Option('[SELECCIONE UN PAÍS]','-1');");
		$xres->addScript("get('cod_ciu').options.length = 0;");		
		$xres->addScript("get('cod_ciu').options[0]=new Option('[SELECCIONE UN PAÍS]','-1');");		
	}else{
		$xres->addScript("get('cod_dep').options.length = 0;");		
		$xres->addScript("get('cod_dep').options[0]=new Option('[SELECCIONE UN DEPARTAMENTO]','-1');");
				
		include ("../../Connections/sapred_db.php");
		mysqli_select_db($sapred_db,$database_sapred_db);
		$query = "SELECT deptos.cod_dep, deptos.nom_dep FROM deptos WHERE deptos.cod_pai = '$cod_pai'";
		$res = mysqli_query($sapred_db,$query);
		$xres->addScript("var opts = get('cod_dep').options;");
		while($fila = mysqli_fetch_array($res)){
			$xres->addScript("opts[opts.length]=new Option('".$fila['nom_dep']."','".$fila['cod_dep']."');");
		}		
		$xres->addScript("get('cod_ciu').options.length = 0;");		
		$xres->addScript("get('cod_ciu').options[0]=new Option('[SELECCIONE UN DEPARTAMENTO]','-1');");		
	}
	$xres->addAssign("SomeElementId","innerHTML", $newContent);
	return $xres->getXML();
} 
?><?php
function cambiaDepto($cod_dep) {
	$xres = new xajaxResponse();
	if($cod_dep==-1){
		$xres->addScript("get('cod_ciu').options.length = 0;");		
		$xres->addScript("get('cod_ciu').options[0]=new Option('[SELECCIONE UN DEPARTAMENTO]','-1');");		
	}else{
		$xres->addScript("get('cod_ciu').options.length = 0;");		
		$xres->addScript("get('cod_ciu').options[0]=new Option('[SELECCIONE UNA CIUDAD]','-1');");
				
		include ("../../Connections/sapred_db.php");
		mysqli_select_db($sapred_db,$database_sapred_db);
		$query = "SELECT ciudad.cod_ciu, ciudad.nom_ciu FROM ciudad WHERE cod_dep = '$cod_dep' ORDER BY ciudad.nom_ciu ASC";
		$res = mysqli_query($sapred_db,$query);
		$xres->addScript("var opts = get('cod_ciu').options;");
		while($fila = mysqli_fetch_array($res)){
			$xres->addScript("opts[opts.length]=new Option('".$fila['nom_ciu']."','".$fila['cod_ciu']."');");
		}		
	}
	return $xres->getXML();
} 
function listar($form,$pag=1){
	$xres=new xajaxResponse();
	ob_start();
	global $con;
	$con->query("SET NAMES 'utf8'");
	$id_ano=$form['id_ano'];
	$id_sed=$form['id_sed'];
	$id_jor=$form['id_jor'];
	$cod_gra=$form['cod_gra'];
	$documento=$form['documento'];
	$nombres=$form['nombres'];
	$limit=$form['limit'];
	$fil_gen='';$sep='WHERE';
	if($id_ano!=-1 && $id_ano!=''){$fil_gen.="$sep p.id_ano=$id_ano";$sep=" AND ";}
	if($id_sed!=-1 && $id_sed!=''){$fil_gen.="$sep s.id_sed=$id_sed";$sep=" AND ";}
	if($id_jor!=-1 && $id_jor!=''){$fil_gen.="$sep j.id_jor=$id_jor";$sep=" AND ";}
	if($cod_gra!=-1 && $cod_gra!=''){$fil_gen.="$sep g.cod_gra=$cod_gra";$sep=" AND ";}
	if($documento!=''){$fil_gen.="$sep p.documento LIKE '%$documento%'";$sep=" AND ";}
	if($nombres!=''){$fil_gen.="$sep CONCAT(p.ape_pre,' ',p.nom_pre) LIKE '%$nombres%'";$sep=" AND ";}
		if($fil_gen==''){
		$xres->addAssign("div_lista","innerHTML",'<input value="M&aacute;s"');
		return $xres;
	}
	//if($fil_gen==''){
		//$xres->addAssign("div_lista","innerHTML",'<input value="M&aacute;s" /*onclick="xajax_getForm();" class="boton_mas"*/ />');
		//return $xres;
	//}

	$qCont="SELECT COUNT(*)AS tot FROM(
	SELECT DISTINCT p.*
	FROM preinscrito p
	LEFT JOIN grado g USING(cod_gra)
	LEFT JOIN sede s USING(id_sed)
	LEFT JOIN jornada j USING(id_jor)
	LEFT JOIN alumno a ON p.id_pre=a.id_alu AND a.est_alu='h'
	LEFT JOIN alumcurso ac USING(id_alu)
	LEFT JOIN curso c USING(cod_cur)
	$fil_gen
	)c";
	//ORDER BY s.nom_sed,j.des_jor,g.ord_gra,p.ape_pre,p.nom_pre
	$r=$con->query($qCont);
	$f=$con->fetch($r);
	$offset=($pag*$limit)-$limit;
	$nReg=$f['tot'];
	$nPag=ceil($nReg/$limit);
	//echo nl2br($qCont);
	
	$query="SELECT DISTINCT p.*,s.nom_sed,j.des_jor,g.des_gra,c.des_cur,a.id_alu
	FROM preinscrito p
	LEFT JOIN grado g USING(cod_gra)
	LEFT JOIN sede s USING(id_sed)
	LEFT JOIN jornada j USING(id_jor)
	LEFT JOIN alumno a ON p.id_pre=a.id_alu AND a.est_alu='h'
	LEFT JOIN alumcurso ac USING(id_alu)
	LEFT JOIN curso c USING(cod_cur)
	$fil_gen
	LIMIT $limit OFFSET $offset";
	//echo nl2br($query);
	//ORDER BY s.nom_sed,j.des_jor,g.ord_gra,p.ape_pre,p.nom_pre
	$r=$con->query($query);
	?>
    <table width="100%" class="tabla-comp-row-intercal-default" border="1">
    <thead>
     <tr class="fila_tit_n2"><th colspan="14"></th></tr>
	 <!--<tr class="fila_tit_n2"><th colspan="14"><input value="Más" onclick="xajax_getForm();" class="boton_mas" /></th></tr>-->
     <tr class="fila_tit_n3">
      <th>No.</th> <th>SEDE</th> <th>JORNADA</th> <th>GRADO</th> <th>C&Oacute;DIGO</th> <th>No. IDENTIFICACI&Oacute;N</th> <th>APELLIDOS</th> <th>NOMBRES</th> 
      <th>DIRECCI&Oacute;N</th> <th>TEL&Eacute;FONO</th> <th>CURSO</th> <th>ESTADO</th> <th>ACCIONES</th>
     </tr>
    </thead>
    <tbody class="tbody-cont resalt-fila-hover">
    <?php
	$cont=$offset+1;
	while($f=$con->fetch($r)){
	?>  
     <tr>
     	<td align="center"><?php echo $cont++; ?></td>
        <td><?php echo $f['nom_sed']; ?></td>
        <td><?php echo $f['des_jor']; ?></td>
        <td><?php echo $f['des_gra']; ?></td>
        <td><?php echo $f['id_pre']; ?></td>
        <td><?php echo $f['documento']; ?></td>
        <td><?php echo $f['ape_pre']; ?></td>
        <td><?php echo $f['nom_pre']; ?></td>
        <td><?php echo $f['dir_pre']; ?></td>
        <td><?php echo "{$f['tel_pre']}"; ?></td>
        <td><?php echo ($f['des_cur'])?utf8_decode($f['des_cur']):'N/A'; ?></td>
        <td><?php echo ($f['id_alu'])?'ACTIVO':'PREINSCRITO'; ?></td>
        <td>
        	<input type="button" value="M" onclick="$('#capa_nivel0').remove( );$('#dialog:ui-dialog').dialog('destroy');xajax_getForm('<?php echo $f['id_pre']; ?>');" class="boton_m" />
            <input type="button" value="-" onclick="if(confirm('¿Está seguro de que desea eliminar este Estudiante?'))xajax_eliEstu('<?php echo $f['id_pre'];?>');" class="boton_menos" />
            
        </td>
        <td>
         <input type="button" class="boton" value="Tarjeta" onClick="javascript:xajax_tarjeta('<?php echo $f['id_pre'];?>');">
        </td>
     </tr>
	<?php }?>
    </tbody>
    </table>	
    <div id="div_npag"><?php for($i=1;$i<=$nPag;$i++){?><span <?php if($i!=$pag){?> onClick="xajax_listar(xajax.getFormValues('formF'),<?php echo "'$i','$limit'"; ?>);" <?php }?> style="cursor:pointer; padding-left:5px;<?php if($i!=$pag){?> text-decoration:underline;<?php }?>" class="pag-out" onmouseover="this.className='pag-hover';" onmouseout="this.className='pag-out';" title="Página <?php echo $i; ?>"><?php echo $i; ?></span><?php }?></div>
    <?php
	$html=ob_get_clean();
	$xres->addAssign("div_lista","innerHTML",$html);
	return $xres->getXML();
}

function getGra($id_sed='',$id_jor=''){
	$xres=new xajaxResponse();
	ob_start();
	global $con;
	$con->query("SET NAMES 'utf8'");
	$fil='';$sep='WHERE';
	if($id_sed!='' && $id_sed!=-1){$fil.="$sep id_sed=$id_sed";$sep='AND';}
	if($id_jor!='' && $id_jor!=-1){$fil.="$sep id_jor=$id_jor";$sep='AND';}	
	$query="SELECT DISTINCT g.cod_gra,g.des_gra FROM grado g 
	INNER JOIN preinscrito p USING(cod_gra) $fil";
	$r=$con->query($query);
	?>
    	<option value="-1">Seleccione una opci&oacute;n</option>
    <?php while($f=$con->fetch($r)){?>
		<option value="<?php echo $f['cod_gra'];?>"><?php echo $f['des_gra'];?></option>	
<?php 
	}
	$html=ob_get_clean();
	$xres->addAssign("cod_gra","innerHTML",$html);
	return $xres->getXML();
}
function getJor($id_sed){
	$xres=new xajaxResponse();
	ob_start();
	global $con;
	$con->query("SET NAMES 'utf8'");
	$fil='';$sep='WHERE';
	if($id_sed!='' && $id_sed!=-1){$fil.="$sep id_sed=$id_sed";$sep='AND';}
	$query="SELECT DISTINCT j.id_jor,j.des_jor FROM jornada j 
	INNER JOIN preinscrito p USING(id_jor) $fil";
	$r=$con->query($query);
	?>
    	<option value="-1">Seleccione una opci&oacute;n</option>
    <?php while($f=$con->fetch($r)){?>
		<option value="<?php echo $f['id_jor'];?>"><?php echo $f['des_jor'];?></option>	
<?php 
	}
	$html=ob_get_clean();
	$xres->addAssign("id_jor","innerHTML",$html);
	return $xres->getXML();
}



function getForm($id_pre=''){
	$xres = new xajaxResponse();
	ob_start();
include ('../../bibliotecas/valida 1.0/biblio.php');
	global $con;
	$tipos=tipodocum::getAll($con);
	if($id_pre){
		$dat=preinscrito::recuperar($con,$id_pre);
		$qCiu="SELECT * FROM ciudad WHERE cod_ciu={$dat->cod_ciu}";
	  	$fCiu=$con->fetch($con->query($qCiu));
		$qPad="SELECT * FROM preinscrito_persona pp 
		INNER JOIN per_pre USING(id_per_pre)WHERE id_pre=$id_pre";
		$rPad=$con->query($qPad);
		$datPad=array();
		while($f=$con->fetch($rPad))$datPad[$f['tip_rel']]=$f;
	}
?>
<div id="capa_nivel0">
<?php //echo "DDD $id_pre";print_r($dat); ?>
<div id="tabs">
 <ul>
	<li><a href="#tabs-1">Ingreso</a></li>
	<li><a href="#tabs-2">Datos Adicionales</a></li>    
 </ul>
 <div id="tabs-1">
	<div align="center">			
		<form name="form1" id="form1" method="post" action="">
		  <table width="434" border="0">
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
              <tr>
              <td><div align="right">Documento de Identidad: </div></td>
              <td><input name="doc_pre" type="text" class="cajatxt" id="doc_pre"  maxlength="32" onKeyPress="<?php echo validarCaracteres($expr['observacion'],"","")?>" style="width:165px" onBlur="this.value=trim(this.value); if (this.value!=''){ xajax_validarDocumento(this.value, document.getElementById('des_tid').value); }" value="<?php echo $dat->documento; ?>"></td>
              <td align="right">Fecha de nacimiento:</td>
              <td><input name="fec_nac" type="text" class="cajatxt" id="fec_nac" 
			style=" text-align:center;"
			onBlur="if(this.value!='' && !isDate(this.value)) this.value=''"	 
			onKeyPress=" return kPress(event)"
			onKeyDown=" kDown(event,this)"
			onKeyUp=" kUp(event,this.id)" 
			size="12"
			maxlength="10"
            value="<?php echo $dat->fec_nac; ?>"
			>
               </td>
            </tr>
            <tr>
          <td><div align="right">Tipo de Documento </div></td>
              <td><select id="des_tid" name="des_tid" style="width:165px" onChange="if(document.getElementById('doc_pre').value!=''){xajax_validarDocumento(document.getElementById('doc_pre').value, this.value);}">
                <?php for($x=0; $tipo=$tipos[$x]; $x++){?>
                <option value="<?php echo $tipo->cod_tid ?>" <?php if($tipo->cod_tid==$dat->cod_tid)echo "selected='selected'";?>>
	                <?php echo $tipo->des_tid; ?>
                </option>
                <?php }?>
              </select></td>
              <td align="right">Ciudad de nacimiento: </td>
              <td><table width="278" height="49">
                <tr>
                  <td>
               
                  <input type="text" class="cajatxt" name="nom_ciu" id="nom_ciu" size="32" maxlength="40" onKeyUp="if(this.value.length>3){xajax_buscarCiudad(this.value,'cna_per_l');}else{get('cna_per_l').options.length=0;}" value="<?php echo $fCiu['nom_ciu']?>" onFocus="txt_ciu=this.value" onBlur="if(get('cna_per_l').value==''){this.value=txt_ciu;} setTimeout('ocultar(\'cna_per_l\')','300');" >
                    <input type="hidden" name="cna_per" id="cna_per" value="<?php echo $dat->cod_ciu; ?>"></td>
                  <td rowspan="2"><input type="button" class="boton_mas" onClick="showPopWin('../ingre_rap_ciu.php',350,230,'');" value="+"></td>
                </tr>
                <tr>
                  <td><select style="display:none;position:absolute; z-index:1000" name="cna_per_l" size="5" id="cna_per_l" onFocus="mostrar(this.id);" onChange="get('cna_per').value=this.value; ocultar(this.id); get('nom_ciu').value=this.options[this.selectedIndex].text;">
                  </select></td>
                </tr>
              </table></td>
            </tr>
            <tr>
              <td><div align="right">A&ntilde;o Lectivo:</div></td>
              <td><select name="id_ano" id="id_ano" style="width:165px;">
					<?php
					$sel_ano = new Select();
					$sel_ano->agregarObjeto(ano_lectivo::getActual($con));
					$sel_ano->agregarObjeto(ano_lectivo::getSiguiente($con));
					$sel_ano->seleccionarValue($dat->id_ano);
					echo $sel_ano->getHTML();
					?>
					 </select>
&nbsp;</td>
              <td align="right">Dir. Correspondencia: </td>
              <td><input name="dir_cor" type="text" class="cajatxt" id="dir_cor" onBlur="this.value=cambia(this.value);" value="<?php echo $dat->dir_pre; ?>" size="32" maxlength="100"></td>
            </tr>
            <tr>
            	<td>
                	<div align="right">Sede:</div>
                </td>
                <td>
                	<select name="id_sed" id="id_sed" style="width:165px">
                    	<?php
							$query="SELECT id_sed,nom_sed FROM sede";
							$listaSede=new Select();
							$listaSede->agregarOpcionesSQL($query,$con);
							$listaSede->seleccionarValue($dat->id_sed);
							echo $listaSede->getHTML();
						?>
                    </select>
                </td>
                <td align="right">Barrio</td>
                <td><input name="cod_bar" type="text" class="cajatxt" id="cod_bar" onBlur="this.value=cambia(this.value);" value="<?php echo $dat->bar_pre; ?>" size="32"></td>
            </tr>
            <tr>
              <td><div align="right">Grado:</div></td>
              <td><select name="cod_gra" id="cod_gra" style="width:165px;">
                <?php
					$sel_gra = new Select();
					$sel_gra->agregarObjetos(grado::getAll($con));
					$sel_gra->seleccionarValue($dat->cod_gra);
					echo $sel_gra->getHTML();
					
					 ?>
              </select></td>
			  
              <td align="right">Telf. Contacto: </td>
              <td><input name="tel_con" type="text" class="cajatxt" id="tel_con" value="<?php echo $dat->tel_pre; ?>" size="32" maxlength="12" onKeyPress="<?php echo  validarCaracteres("['0-9']",'','');?>"></td>
            </tr>
			<tr>
              <td><div align="right">Jornada:</div></td>
              <td><select name="id_jor" id="id_jor" style="width:165px;">
                <?php
					$sel_jor = new Select();
					$sel_jor->agregarObjetos(objeto::getFromQuery($con,"select id_jor as id, des_jor as label from jornada order by hor_ini"));
					$sel_jor->seleccionarValue($dat->id_jor);
					echo utf8_decode($sel_jor->getHTML());
				?>
              </select></td>
              <td align="right">SISBEN</td>
              <td><input text name="sisben" id="sisben" onKeyPress="<? echo validarCaracteres('[0-9\\.]','','')  ?>" value="<?php echo $dat->sisben_pre;?>"></td>
            </tr>
          
            <tr>
            <td><div align="right">Nombres:</div></td>
            <td><input name="nom_pre" type="text" class="cajatxt" id="nom_pre"  maxlength="32" onKeyPress="<?php echo validarCaracteres("$letras","","")?>" style="width:165px; text-transform:uppercase" value="<?php echo htmlentities($dat->nom_pre); ?>"></td>
            <td align="right">Instituci&oacute;n de Procedencia: </td>
            <td><input name="col_pro" type="text" class="cajatxt" id="col_pro" value="<?php echo $dat->col_pro; ?>"  maxlength="255" style="width:165px; text-transform:uppercase; " ></td>
          </tr>
          <tr>
            <td><div align="right">Apellidos:</div></td>
            <td><input name="ape_pre" type="text" class="cajatxt" id="ape_pre"  maxlength="32" onKeyPress="<?php echo validarCaracteres("$letras","","")?>" style="width:165px; text-transform:uppercase" value="<?php echo htmlentities($dat->ape_pre);?>"></td>
            <td align="right">Sexo:</td>
            <td>
            <select name="sex_pre" id="sex_pre">
            	<option value="m" <?php if($dat->sex_pre=='m')echo "selected='selected'"; ?>>Masculino</option>
            	<option value="f" <?php if($dat->sex_pre=='f')echo "selected='selected'"; ?>>Femenino</option>
            </select>
           </td>
          </tr>
          <tr>
           <td align="right">Tipo de Sangre:</td>
           <td><input class="cajatxt" name="gs_pre" id="gs_pre" type="text" value="<?php echo $dat->gs_pre;?>" /></td>
           <td align="right">Eps:</td>
           <td>
           <?php $eps=$con->fetchAll($con->query("SELECT * FROM eps"));?>
           <select name="eps_pre" id="eps_pre"><?php foreach($eps as $e){?><option value="<?php echo $e['id_eps'];?>" <?php if($dat->eps_pre==$e['id_eps'])echo "selected='selected'";?>><?php echo $e['des_eps'];?></option><?php }?></select></td>
          </tr>
          <tr>
           <td>Tel&eacute;fono 2</td>
           <td><input class="cajatxt" type="text" name="tel2_pre" id="tel2_pre" value="<?php echo $dat->tel2_pre;?>" /></td>
           <td align="right">E-mail</td>
           <td><input class="cajatxt" type="text" name="mai_pre" id="mai_pre" value="<?php echo $dat->mai_pre;?>" /></td>
          </tr>
          <tr>
            <td><div align="right">Identificaci&oacute;n de el Padre: </div></td>
            <td><input name="id_per_pre1" type="text" class="cajatxt" id="id_per_pre1" onKeyPress="<?php echo validarCaracteres("$num","","")?>" value="<?php echo $datPad['padr']['documento']?>"  maxlength="10" style="width:165px"></td>
            <td><input type="button" name="Submit22" value="Ingresar" onClick="xajax_verifPadre(getElementById('id_per_pre1').value)"></td>
            <td rowspan="4">
               <div class="marco-foto">
                <span>Fotograf&iacute;a</span>
                <div id="div_img">
                <?php
                    $cont="<img height='100' id='img_foto' src='";                   
                    if(file_exists("../".$dat->ufo_alu) && is_file("../".$dat->ufo_alu)){
                        $cont.="../{$dat->ufo_alu}'>";
                    }else {
                        if($dat->sex_pre=='f')
                            $cont.="../../images/mujer.jpg?imd=".rand()."'>";
                        else
                            $cont.="../../images/hombre.jpg?imd=".rand()."'>";
                    }
                    echo $cont;  
                ?> 
                 </div>
                 <br>   
                 <div class="div-file">
                    <input class="file" align="middle" onChange="subirA(event,'file_fot','file_fot_progreso','','../../matricula/ingr_prei/selXajax.php?accion=subir_fot','','ufo_alu','get(\'img_foto\').src=\'../\' + get(\'ufo_alu\').value + \'?\' + Math.random();');" name="file_fot" type="file" id="file_fot" size="15" accept="image/jpeg,image/gif,image/x-png" />
                    <input type="button" class="boton_img_add" value="Fotografía" title="Fotografía" />
                    <input type="hidden" value="<?php echo $dat->ufo_alu;?>" id="ufo_alu" name="ufo_alu">
                 </div>
                 <br>         	
                <progress id="file_fot_progreso" style="display:none" ></progress><span class="porcen" id="file_img_porcen"></span>
               </div>
            </td>
          </tr>
          <tr>
            <td><div align="right">Identificaci&oacute;n de la Madre: </div></td>
            <td><input name="id_per_pre2" type="text" class="cajatxt" id="id_per_pre2"  maxlength="10" onKeyPress="<?php echo validarCaracteres("$num","","")?>" style="width:165px; text-transform:uppercase" value="<?php echo $datPad['madr']['documento']?>"></td>
            <td><input type="button" name="Submit222" value="Ingresar" onClick="xajax_verifPadre(getElementById('id_per_pre2').value)"></td>
          </tr>
          <tr>
            <td><div align="right">Indentificaci&oacute;n  del Acudiente: </div></td>
            <td><input name="id_per_pre3" type="text" class="cajatxt" id="id_per_pre3"  maxlength="10" onKeyPress="<?php echo validarCaracteres("$num","","")?>" style="width:165px" value="<?php echo $datPad['acud']['documento']?>"></td>
            <td><input type="button" name="Submit223" value="Ingresar" onClick="xajax_verifPadre(getElementById('id_per_pre3').value)"></td>
          </tr>
		  <?php 
		  global $mod;
		  if($mod->est_mod!='a'){
		  ?>
		 <tr>
		  <td><div align="right">C&oacute;digo Estudiantil</div></td>
		  <td><input name="id_pre" type="text" class="cajatxt" id="id_pre" maxlength="20"  onKeyPress="<?php echo validarCaracteres("$num","","")?>" style="width:165px" value="<?php echo $dat->id_pre; ?>"></td>
		  <td>&nbsp;</td>
		</tr>
		<?php }elseif($dat->id_pre){?>
        <tr>
         <td><div align="right">C&oacute;digo Estudiantil</div></td>
         <td colspan="2"><input name="id_pre" type="hidden" id="id_pre" value="<?php echo $dat->id_pre; ?>"><?php echo $dat->id_pre;?></td>
        </tr>
        <?php }?>
          <tr>
            <td height="14" colspan="4">&nbsp;</td>
          </tr>
          <tr>
            <td height="14" colspan="4"><div align="center">
              <input type="button" name="Submit" value="Guardar" onClick="ingresar()" class="boton">
            </div></td>
          </tr>
        </table>
        </form>
	    </div>	

 </div>

<?php if($dat){?>
 <div id="tabs-2">
	<iframe src="../../herramientas/docs_estudiante/upload/index.php?id_alu=<?php echo $dat->id_pre;?>" frameborder="0" width="100%" height="400"></iframe>
 </div>
<?php } ?>

	</div>
</div>
    <?php
	$mod=ob_get_clean();
	$xres->addScript('$( "#dialog:ui-dialog" ).dialog( "destroy" );');
	$xres->addAssign('modales','innerHTML',utf8_encode($mod));
	//$xres->addScript("try{window.parent.document.getElementById('modal_gen').innerHTML=get('modales').innerHTML;}catch(e){alert(e);}");
	$xres->addScript('try{
	$( "#capa_nivel0" ).dialog({
			autoOpen: false,
			title:"Información de Preinscritos",
			height: "auto",
			width: "780",
			modal: true,
			buttons: {			
			},
			close: function() {
				$( "#capa_nivel0" ).remove( );
				$( "#dialog:ui-dialog" ).dialog( "destroy" );
			}
		});
		$( "#capa_nivel0" ).dialog( "open" );
		//window.parent.jQuery("#capa_nivel0").dialog( "open" );
	}catch(e){alert(e);}');
	$xres->addScript('$(function(){
		$tabs = $("#tabs").tabs({
			heightStyle: "content",
			activate:function(event,ui){}
		});
	});');
	$xres->addScript("get('id_ano').focus(); xajax_cambiaPais(-1);");
	$xres->addScript(' $(function() {
		$("#fec_nac").datepicker({
			showOn: "button",
			buttonImage: "../../images/icons/fam/calendar.png",
			buttonImageOnly: true,
			dateFormat:"yy-mm-dd"
			});
		});');
	return $xres->getXML();
}

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
                        <div style="
                            background: linear-gradient(135deg, #e0f2fe, #f0f9ff);
                            border: 1px solid #bae6fd;
                            border-left: 4px solid #0ea5e9;
                            padding: 10px 12px;
                            border-radius: 8px;
                            font-size: 11px;
                            color: #0c4a6e;
                            margin-bottom: 8px;
                            line-height: 1.4;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                        ">
                        
                            <div style="margin-bottom:4px;">
                                <strong style="font-size:12px;">Instrucciones para cargar el Excel</strong>
                            </div>
                                                    
                            <div>
                                El archivo Excel debe tener una fila de encabezados claramente identificada con color, 
                                ya que el sistema utiliza ese formato para reconocer cu&aacute;l es la fila principal del archivo.
                                Al cargar el documento, se mostrar&aacute; una vista previa en forma de tabla, donde el usuario 
                                deber&aacute; mapear cada columna del Excel con el campo correspondiente del sistema.
                            </div>

                            <div style="margin-top:6px;">
                                Es importante verificar que cada columna quede asociada correctamente antes de guardar, 
                                por ejemplo: n&uacute;mero de documento, nombres, apellidos, grado, sede, jornada y dem&aacute;s datos 
                                del estudiante, padre, madre o acudiente.
                            </div>
                                                    
                        </div>

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
                                    <div>Grado</div>
                                    <div>Jornada</div>
                                    <div>Direcci&oacute;n</div>
                                    <div>Barrio</div>
                                    <div>Tel&eacute;fono fijo</div>
                                    <div>Celular</div>
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
                                        DocumentoP<br>
                                        Tipo documentoP<br>
                                        NombreP<br>
                                        Apellido<br>
                                        Direcci&oacute;nP<br>
                                        Tel&eacute;fonoP<br>
                                        Profesi&oacute;nP
                                    </div>

                                    <div>
                                        DocumentoM<br>
                                        Tipo documentoM<br>
                                        NombreM<br>
                                        ApellidoM<br>
                                        Direcci&oacute;nM<br>
                                        Tel&eacute;fonoM<br>
                                        Profesi&oacute;nM
                                    </div>

                                    <div>
                                        DocumentoA<br>
                                        Tipo documentoA<br>
                                        NombreA<br>
                                        Apellido<br>
                                        Direcci&oacute;nA<br>
                                        Tel&eacute;fonoA<br>
                                        Profesi&oacute;nA
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
                    <div id="body_diccionario" style="padding:8px;">
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
function obtenerIdProfesionSeguro($con, $nombre)
{
    $nombre = trim((string)$nombre);

    if ($nombre === '') {
        return 1;
    }

    if (is_numeric($nombre)) {
        return intval($nombre);
    }

    $nombre = strtoupper(addslashes($nombre));

    $id = $con->result(
        $con->query("
            SELECT id_pro 
            FROM profesion 
            WHERE UPPER(des_pro) = '$nombre'
               OR UPPER(des_pro) LIKE '%$nombre%'
            LIMIT 1
        ")
    );

    return $id ? intval($id) : 1;
}

function guardarExcelPreinscritosNuevo($datos = [])
{
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
        $detalleErrores = [];

        $idAnoActual = $con->result(
            $con->query("SELECT id_ano FROM anolectivo WHERE actual='s' LIMIT 1")
        );

        foreach ($datos as $i => $fila) {

            $numFila = $i + 1;

            if (empty($fila['doc_pre']) || empty($fila['nom_pre']) || empty($fila['ape_pre'])) {
                $errores++;
                $detalleErrores[] = "Fila $numFila: faltan documento, nombres o apellidos.";
                continue;
            }

            $doc_pre = trim($fila['doc_pre']);

            /* GRADO */
            $valorGrado = isset($fila['cod_gra']) ? trim($fila['cod_gra']) : '';
            $cod_gra = null;

            if ($valorGrado !== '') {
                $mapGrados = [
                    0 => 'PREJARDIN', -1 => 'JARDIN', -2 => 'TRANSICION',
                    1 => 'PRIMERO', 2 => 'SEGUNDO', 3 => 'TERCERO',
                    4 => 'CUARTO', 5 => 'QUINTO', 6 => 'SEXTO',
                    7 => 'SEPTIMO', 8 => 'OCTAVO', 9 => 'NOVENO',
                    10 => 'DECIMO', 11 => 'ONCE'
                ];

                if (is_numeric($valorGrado)) {
                    $numero = intval($valorGrado);
                    if (isset($mapGrados[$numero])) {
                        $textoGrado = addslashes($mapGrados[$numero]);
                        $cod_gra = $con->result(
                            $con->query("SELECT cod_gra FROM grado WHERE UPPER(des_gra) = '$textoGrado' LIMIT 1")
                        );
                    } else {
                        $cod_gra = $numero;
                    }
                } else {
                    $textoGrado = strtoupper(addslashes($valorGrado));
                    $cod_gra = $con->result(
                        $con->query("SELECT cod_gra FROM grado WHERE UPPER(des_gra) LIKE '%$textoGrado%' LIMIT 1")
                    );
                }
            }

            /* JORNADA */
            $valorJornada = isset($fila['id_jor']) ? trim($fila['id_jor']) : '';
            $id_jor = null;

            if ($valorJornada !== '') {
                if (is_numeric($valorJornada)) {
                    $id_jor = intval($valorJornada);
                } else {
                    $textoJornada = strtoupper(addslashes($valorJornada));
                    $id_jor = $con->result(
                        $con->query("SELECT id_jor FROM jornada WHERE UPPER(des_jor) LIKE '%$textoJornada%' LIMIT 1")
                    );
                }
            }

            /* SEDE */
            $valorSede = isset($fila['id_sed']) ? trim($fila['id_sed']) : '';
            $id_sed = null;

            if ($valorSede !== '') {
                if (is_numeric($valorSede)) {
                    $id_sed = intval($valorSede);
                } else {
                    $textoSedeBase = strtoupper(trim($valorSede));

                    if (strpos($textoSedeBase, '-') !== false) {
                        $partesSede = explode('-', $textoSedeBase);
                        $textoSedeBase = trim($partesSede[0]);
                    }

                    $textoSedeBaseSql = addslashes($textoSedeBase);

                    $id_sed = $con->result(
                        $con->query("
                            SELECT id_sed 
                            FROM sede 
                            WHERE UPPER(nom_sed) = '$textoSedeBaseSql'
                               OR UPPER(nom_sed) LIKE '%$textoSedeBaseSql%'
                            LIMIT 1
                        ")
                    );
                }
            }

            /* CIUDAD */
            $valorCiudad = isset($fila['ciu_pre']) ? trim($fila['ciu_pre']) : '';
            $cod_ciu = null;

            if ($valorCiudad !== '') {
                if (is_numeric($valorCiudad)) {
                    $cod_ciu = intval($valorCiudad);
                } else {
                    $textoCiudad = strtoupper(addslashes($valorCiudad));
                    $cod_ciu = $con->result(
                        $con->query("SELECT cod_ciu FROM ciudad WHERE UPPER(nom_ciu) LIKE '%$textoCiudad%' LIMIT 1")
                    );
                }
            }

            if (!$cod_gra || !$id_jor || !$id_sed || !$cod_ciu) {
                $errores++;
                $detalleErrores[] =
                    "Fila $numFila Doc $doc_pre: faltan IDs. " .
                    "Grado={$valorGrado}=>{$cod_gra}, " .
                    "Jornada={$valorJornada}=>{$id_jor}, " .
                    "Sede={$valorSede}=>{$id_sed}, " .
                    "Ciudad={$valorCiudad}=>{$cod_ciu}";
                continue;
            }

            /* TIPO DOCUMENTO */
            $tipo_pre = isset($fila['tipo_pre']) ? strtoupper(trim($fila['tipo_pre'])) : 'CC';

            switch ($tipo_pre) {
                case 'TI': $cod_tid = 2; break;
                case 'RC': $cod_tid = 3; break;
                case 'CE': $cod_tid = 4; break;
                case 'PASAPORTE':
                case 'PA': $cod_tid = 5; break;
                case 'NUIP': $cod_tid = 6; break;
                case 'CC':
                default: $cod_tid = 1; break;
            }

            /* EPS */
            $eps_id = "NULL";

            if (!empty($fila['eps_pre'])) {
                $epsValor = trim($fila['eps_pre']);

                if (is_numeric($epsValor)) {
                    $eps_id = intval($epsValor);
                } else {
                    $epsTexto = strtoupper(addslashes($epsValor));

                    $epsTmp = $con->result(
                        $con->query("
                            SELECT id_eps
                            FROM eps
                            WHERE UPPER(des_eps) = '$epsTexto'
                               OR UPPER(des_eps) LIKE '%$epsTexto%'
                            LIMIT 1
                        ")
                    );

                    if ($epsTmp) {
                        $eps_id = intval($epsTmp);
                    }
                }
            }

            /* AÑO */
            $id_ano = $idAnoActual ? intval($idAnoActual) : 0;

            if (!empty($fila['id_ano'])) {
                $valorAno = intval($fila['id_ano']);

                $idAnoExcel = $con->result(
                    $con->query("
                        SELECT id_ano
                        FROM anolectivo
                        WHERE id_ano = $valorAno
                           OR ano = $valorAno
                        LIMIT 1
                    ")
                );

                if ($idAnoExcel) {
                    $id_ano = intval($idAnoExcel);
                }
            }

            /* DATOS */
            $ape_pre  = strtoupper(addslashes(isset($fila['ape_pre']) ? trim($fila['ape_pre']) : ''));
            $nom_pre  = strtoupper(addslashes(isset($fila['nom_pre']) ? trim($fila['nom_pre']) : ''));
            $fec_nac  = addslashes(isset($fila['fec_nac']) ? trim($fila['fec_nac']) : '');
            $col_pro  = addslashes(isset($fila['col_pro']) ? trim($fila['col_pro']) : '');
            $dir_cor  = addslashes(isset($fila['dir_cor']) ? trim($fila['dir_cor']) : '');
            $tel_con  = addslashes(isset($fila['tel_con']) ? trim($fila['tel_con']) : '');
            $barrio   = addslashes(isset($fila['barrio']) ? trim($fila['barrio']) : '');
            $sisben   = addslashes(isset($fila['sisben']) && trim($fila['sisben']) !== '' ? trim($fila['sisben']) : '0');
            $sex_pre  = addslashes(isset($fila['sex_pre']) ? trim($fila['sex_pre']) : '');
            $gs_pre   = addslashes(isset($fila['gs_pre']) ? trim($fila['gs_pre']) : '');
            $tel2_pre = addslashes(isset($fila['tel2_pre']) ? trim($fila['tel2_pre']) : '');
            $mai_pre  = addslashes(isset($fila['mai_pre']) ? trim($fila['mai_pre']) : '');

            /* RESPONSABLES */
            $id_per1 = 0;
            $id_per2 = 0;
            $id_per3 = 0;

            if (!empty($fila['doc_padre'])) {
                $doc_padre = addslashes(trim($fila['doc_padre']));
                $id_per1 = $con->result($con->query("SELECT id_per_pre FROM per_pre WHERE documento='$doc_padre' LIMIT 1"));

                if (!$id_per1) {
                    $id_pro_p = obtenerIdProfesionSeguro($con, isset($fila['id_pro_p']) ? $fila['id_pro_p'] : '');
                    $padre = per_pre::crear(
                        $con,
                        $cod_tid,
                        strtoupper(isset($fila['nom_padre']) ? $fila['nom_padre'] : ''),
                        strtoupper(isset($fila['ape_padre']) ? $fila['ape_padre'] : ''),
                        isset($fila['dir_padre']) ? $fila['dir_padre'] : '',
                        isset($fila['tel_padre']) ? $fila['tel_padre'] : '',
                        $fila['doc_padre'],
                        $id_pro_p
                    );
                    $id_per1 = $padre->id_per_pre;
                }
            }

            if (!empty($fila['doc_madre'])) {
                $doc_madre = addslashes(trim($fila['doc_madre']));
                $id_per2 = $con->result($con->query("SELECT id_per_pre FROM per_pre WHERE documento='$doc_madre' LIMIT 1"));

                if (!$id_per2) {
                    $id_pro_m = obtenerIdProfesionSeguro($con, isset($fila['id_pro_m']) ? $fila['id_pro_m'] : '');
                    $madre = per_pre::crear(
                        $con,
                        $cod_tid,
                        strtoupper(isset($fila['nom_madre']) ? $fila['nom_madre'] : ''),
                        strtoupper(isset($fila['ape_madre']) ? $fila['ape_madre'] : ''),
                        isset($fila['dir_madre']) ? $fila['dir_madre'] : '',
                        isset($fila['tel_madre']) ? $fila['tel_madre'] : '',
                        $fila['doc_madre'],
                        $id_pro_m
                    );
                    $id_per2 = $madre->id_per_pre;
                }
            }

            if (!empty($fila['doc_acu'])) {
                $doc_acu = addslashes(trim($fila['doc_acu']));
                $id_per3 = $con->result($con->query("SELECT id_per_pre FROM per_pre WHERE documento='$doc_acu' LIMIT 1"));

                if (!$id_per3) {
                    $id_pro_a = obtenerIdProfesionSeguro($con, isset($fila['id_pro_a']) ? $fila['id_pro_a'] : '');
                    $acu = per_pre::crear(
                        $con,
                        $cod_tid,
                        strtoupper(isset($fila['nom_acu']) ? $fila['nom_acu'] : ''),
                        strtoupper(isset($fila['ape_acu']) ? $fila['ape_acu'] : ''),
                        isset($fila['dir_acu']) ? $fila['dir_acu'] : '',
                        isset($fila['tel_acu']) ? $fila['tel_acu'] : '',
                        $fila['doc_acu'],
                        $id_pro_a
                    );
                    $id_per3 = $acu->id_per_pre;
                }
            }

            /* BORRAR EXISTENTE */
            $id_pre_sql = addslashes($doc_pre);

            $existe = $con->result(
                $con->query("SELECT id_pre FROM preinscrito WHERE id_pre='$id_pre_sql' LIMIT 1")
            );

            if ($existe) {
                $con->query("DELETE FROM preinscrito_persona WHERE id_pre='$id_pre_sql'");
                $con->query("DELETE FROM preinscrito WHERE id_pre='$id_pre_sql'");
            }

            /* INSERT DIRECTO PREINSCRITO */
            $sqlPre = "
                INSERT INTO preinscrito SET
                    id_pre = '$id_pre_sql',
                    cod_gra = " . intval($cod_gra) . ",
                    cod_tid = " . intval($cod_tid) . ",
                    id_ano = " . intval($id_ano) . ",
                    ape_pre = '$ape_pre',
                    nom_pre = '$nom_pre',
                    fec_nac = '$fec_nac',
                    documento = '$id_pre_sql',
                    fec_ins = '" . date('Y-m-d') . "',
                    cod_ciu = " . intval($cod_ciu) . ",
                    col_pro = '$col_pro',
                    dir_pre = '$dir_cor',
                    tel_pre = '$tel_con',
                    bar_pre = '$barrio',
                    sisben_pre = '$sisben',
                    id_jor = " . intval($id_jor) . ",
                    id_sed = " . intval($id_sed) . ",
                    sex_pre = '$sex_pre',
                    gs_pre = '$gs_pre',
                    eps_pre = $eps_id,
                    tel2_pre = '$tel2_pre',
                    mai_pre = '$mai_pre',
                    ufo_alu = ''
            ";

            $con->query($sqlPre);

            /* INSERT DIRECTO RELACIONES */
            if (intval($id_per1) > 0) {
                $con->query("INSERT INTO preinscrito_persona (id_per_pre,id_pre,tip_rel) VALUES (" . intval($id_per1) . ", '$id_pre_sql', 'padr')");
            }

            if (intval($id_per2) > 0) {
                $con->query("INSERT INTO preinscrito_persona (id_per_pre,id_pre,tip_rel) VALUES (" . intval($id_per2) . ", '$id_pre_sql', 'madr')");
            }

            if (intval($id_per3) > 0) {
                $con->query("INSERT INTO preinscrito_persona (id_per_pre,id_pre,tip_rel) VALUES (" . intval($id_per3) . ", '$id_pre_sql', 'acud')");
            }

            $creados++;
        }

        $con->query("COMMIT");

        $mensaje = "Proceso finalizado\n\nCreados: $creados\nErrores: $errores";

        if (!empty($detalleErrores)) {
            $mensaje .= "\n\nDetalle errores:\n" . implode("\n", $detalleErrores);
        }

        $xres->addScript("
            setTimeout(function(){

                var modalExcel = $('#modal_excel_preview');

                if (modalExcel.length) {
                    var dialogContent = modalExcel.closest('.ui-dialog-content');

                    if (dialogContent.length && dialogContent.hasClass('ui-dialog-content')) {
                        try {
                            dialogContent.dialog('destroy');
                        } catch(e) {}
                    }

                    modalExcel.closest('.ui-dialog').remove();
                    modalExcel.remove();
                }

                $('.ui-widget-overlay').remove();
                $('body').css('overflow', '');

                if (typeof xajax_listar === 'function') {
                    xajax_listar(xajax.getFormValues('formF'), 1);
                }

            }, 300);
        ");

        $xres->addAlert($mensaje);


    } catch (Exception $e) {
        if (isset($con)) {
            $con->query("ROLLBACK");
        }

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


function eliEstu($id_pre){
	$xres=new xajaxResponse();
	global $con;
	ob_start();
	$alu=NULL;
	$alu =alumno::recuperar($con,$id_pre);
	$qAC="SELECT * FROM alumcurso WHERE id_alu='{$alu->id_alu}'";
	$fAC=$con->fetch($con->query($qAC));
	if(!$fAC['id_alu']){
		$con->query("DELETE FROM alum_grado WHERE id_alu='$id_pre'");
		if($alu){
			$alu->setConex($con);
			$alu->eliminar();
		}
		$preins=preinscrito::recuperar($con,$id_pre);	
		$preins->setConex($con);
		$preins->eliminar();
		$xres->addScript("xajax_listar(xajax.getFormValues('formF'));");
	}else{
		$xres->addAlert("El estudiante {$alu->ape_per} {$alu->nom_per} ya se encuentra asignado a un curso. No es posible eliminar este registro.");
	}
	$html=ob_get_clean();
	return $xres->getXML();
}
$accion=$_REQUEST['accion'];
switch($accion){
	case 'subir_fot':
		if (isset($_REQUEST['nom'])){
			include("../../aut_verifica.inc.php");
			require('../../bibliotecas/class.image-resize.php');
			$dir=$_SESSION['DIRECTORIO_ FOTOS_ ESTUDIANTES'];
			$ext=explode(".",$_REQUEST['nom']);
			$ID=$_REQUEST['ID'];
			$nombre='';
			$ext=end($ext);
			if($ext=='php' || $ext=='exe' || ($ext!='jpg' && $ext!='jpeg' && $ext!='png' && $ext!='gif' && $ext!='bmp')){
				$res=array("succ"=>false,"text"=>"ERROR","js"=>"alert('Imposible subir archivos de este tipo');");
				echo json_encode($res);
				break;
			}
			if(!$_REQUEST['id_pre']){//si no llega el id del preinscrito es un registro nuevo y se valida si la foto ya existe
				$directorio = opendir("../../".$dir); //ruta actual
				$fotos=array();
				while ($archivo = readdir($directorio)){
					if (is_dir($archivo)){}
					else{$fotos[]=$archivo;}
				}
				do{
					$ale=mt_rand();
					$nombre=$ale."_pre.{$ext}";
				}while(in_array($nombre,$fotos));
			}else $nombre=$_REQUEST['id_pre']."_pre.".$ext;
			$obj = new img_opt();
			$obj->max_width(250);
			$obj->max_height(250);
			$a=file_put_contents("../../".$dir.$nombre,file_get_contents('php://input'));
			$obj->image_path("../../".$dir.'/'.$nombre);
			$img_tam=getimagesize("../../".$dir.$nombre);
			if($img_tam[0]>250 || $img_tam[1]>250)$obj->image_resize();
			if($a){
				$res=array("succ"=>true,"text"=>"../{$dir}{$nombre}","js"=>"get('$ID').value='../{$dir}{$nombre}';");
			}else{
				$res=array("succ"=>false,"text"=>"ERROR","js"=>"alert('Error al subir o guardar el archivo');");
			}
			echo json_encode($res);
		}
	break;
}
function tarjeta($id_pre){
	$xres = new xajaxResponse();
	ob_start();
	global $con;
?>
<div id="capa_nivel0">
 <form name="form1" id="form1" method="post" action="../../reportes/tarjeta_matri/html.php" target="_blank">
  <input type="hidden" name="id_alu" value="<?php echo $id_pre;?>">
  <table width="100%">
   <tr><td>Tipo Firmas</td> <td><label><input type="radio" name="tip_fir" value="i" checked>Individual</label> <label><input type="radio" name="tip_fir" value="f">Filas</label></td></tr>
   <tr><td>No. Filas Firmas</td> <td><input type="text" name="fil_adi" value="6"></td></tr>
  </table>
 </form>
</div>
    <?php
	$mod=ob_get_clean();
	$xres->addScript('$( "#dialog:ui-dialog" ).dialog( "destroy" );');
	$xres->addAssign('modales','innerHTML',utf8_encode($mod));
	//$xres->addScript("try{window.parent.document.getElementById('modal_gen').innerHTML=get('modales').innerHTML;}catch(e){alert(e);}");
	$xres->addScript('try{
	$( "#capa_nivel0" ).dialog({
			autoOpen: false,
			title:"Información de Preinscritos",
			height: "auto",
			width: "350",
			modal: true,
			buttons: {
				"Generar":function(){
					get("form1").submit();
				}
			},
			close: function() {
				$( "#capa_nivel0" ).remove( );
				$( "#dialog:ui-dialog" ).dialog( "destroy" );
			}
		});
		$( "#capa_nivel0" ).dialog( "open" );
		//window.parent.jQuery("#capa_nivel0").dialog( "open" );
	}catch(e){alert(e);}');
	return $xres->getXML();
}
?>
