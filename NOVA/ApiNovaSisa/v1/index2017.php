<?php

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require_once '../include/class.datetime.php';
require '.././libs/Slim/Slim.php';
	
include_once '../include/ConfigAPI.php';
	
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$id_dat = NULL;
$id_usu = NULL;
$id_per = NULL;
$bd = NULL;

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        //$db = new DbHandler('8140037423');

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!($api_key == API_KEY) /*!$db->isValidApiKey($api_key)*/) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
			global $id_per,$bd;
            // get user primary key id
			//$id_per = $db->getUsuId($api_key);
			//global bd
			//$user = $db->getTipoBD($id_per,$api_key);
			//if($user['id_tusu'] == 4) $bd = 'w';
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}


/**
 * User Login
 * url - /login
 * method - POST
 * params - nom_usu, password
 */
$app->post('/login', function() use ($app) {
	// check for required params
	verifyRequiredParams(array('nom_usu', 'password'));

	// reading post params
	$nom_usu = $app->request()->post('nom_usu');
	$password = $app->request()->post('password');
	$db_name = $app->request()->post('db_name');
	$response = array();
	
	$password = md5($password);

	$db = new DbHandler($db_name);
	// check for correct nom_usu and password
	$du=$db->checkLoginAPI($nom_usu, $password);
	
	if ($du != NULL) {//realiza el login del usuario con usuario y contraseña
		if($du['bd'] == "w"){
			// get the user by username
			$response["error"] = false;
			$response["db_name"] = $db_name;
			$response['usuario_id'] = $du['usuario_id'];
			$response['usuario_nivel'] = $du['usuario_nivel'];
			$response['nom_per'] = $du['nom_per'];
			$response['ape_per'] = $du['ape_per'];
			$response['usuario_login'] = $du['usuario_login'];
			//$response['usuario_password'] = $du['usuario_password'];
			$response['apiKey'] = API_KEY; //$du['apikey'];
			$response['id_per'] = $du['id_per'];
			$response['bd'] = $du['bd'];
			$response['nac_per'] = $du['nac_per'];
			//$response['api'] = $api;
		}else{
			$response['error'] = true;
			$response['message'] = 'La aplicación solo es para padres de familia';
		}
	} else {
		// user credentials are wrong
		$response['error'] = true;
		$response['message'] = 'Error: Credenciales incorrectas';
	}

	echoRespnse(200, $response);
});




/**
 * mensajes recibidos
 * url - /mensajes/recibidos/:
 * method - get
 * params id_des, bd
 */
$app->get('/mensajes/recibidos/:id_des/:bd/:db_name', 'authenticate', function($id_des,$bd,$db_name) {		
	$response = array();

	//global $id_per;
	$db = new DbHandler($db_name);
	//capturo el id_per
	//global $id_per;
	//global $bd;

	// consultar mensajes recibidos
	$recibidos = $db->getRecibidos($id_des,$bd);

	$response["error"] = false;	
	$response['recibidos'] = array();
	
	if ($recibidos != NULL) {			
		while ($recibido = $recibidos->fetch_assoc()) {
			$tmp = array();
			$tmp["id_msg"] = $recibido["id_msg"];
			$tmp["asu_msg"] = $recibido["asu_msg"];
			$tmp["txt_msg"] = $recibido["txt_msg"];
			$tmp["fec_msg"] = $recibido["fec_msg"];
			$tmp["hor_msg"] = $recibido["hor_msg"];
			$tmp["rem"] = $recibido["rem"];
			$tmp["nom_est"] = $recibido["nom_est"];
			$tmp["img"] = $recibido["img"];
			$tmp["tip_usu"] = $recibido["tip_usu"];
			$tmp["tip_des"] = $recibido["tip_des"];
			array_push($response['recibidos'], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});



/**
 * mensajes enviados
 * url - /mensajes/enviados/:
 * method - get
 * params id_rem
 */
$app->get('/mensajes/enviados/:id_rem/:db_name', 'authenticate', function($id_rem,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// consultar mensajes enviados
	$enviados = $db->getEnviados($id_rem);

	$response["error"] = false;	
	$response['enviados'] = array();
	
	if ($enviados != NULL) {			
		while ($enviado = $enviados->fetch_assoc()) {
			$tmp = array();
			$tmp["id_msg"] = $enviado["id_msg"];
			$tmp["asu_msg"] = $enviado["asu_msg"];
			$tmp["txt_msg"] = $enviado["txt_msg"];
			$tmp["fec_msg"] = $enviado["fec_msg"];
			$tmp["hor_msg"] = $enviado["hor_msg"];
			$tmp["rem"] = $enviado["rem"];
			$tmp["nom_est"] = $enviado["nom_est"];
			$tmp["tip_usu"] = $enviado["tip_usu"];
			$tmp["tip_des"] = $enviado["tip_des"];
			array_push($response["enviados"], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * mensajes archivados
 * url - /mensajes/archivados/:id_des
 * method - get
 * params id_des
 */
$app->get('/mensajes/archivados/:id_des/:db_name', 'authenticate', function($id_des,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// consultar mensajes archivados
	$archivados = $db->getArchivados($id_des);

	$response["error"] = false;	
	$response['archivados'] = array();
	
	if ($archivados != NULL) {			
		while ($archivado = $archivados->fetch_assoc()) {
			$tmp = array();
			$tmp["id_msg"] = $archivado["id_msg"];
			$tmp["asu_msg"] = $archivado["asu_msg"];
			$tmp["txt_msg"] = $archivado["txt_msg"];
			$tmp["fec_msg"] = $archivado["fec_msg"];
			$tmp["hor_msg"] = $archivado["hor_msg"];
			$tmp["rem"] = $archivado["rem"];
			$tmp["nom_est"] = $archivado["nom_est"];
			$tmp["tip_usu"] = $archivado["tip_usu"];
			$tmp["tip_des"] = $archivado["tip_des"];
			array_push($response["archivados"], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * actividades
 * url - /actividades/acudiente/:id_per
 * method - get
 * params id_per
 */
$app->get('/actividades/acudiente/:id_per/:id_alu/:fecha/:db_name', 'authenticate', function($id_per,$id_alu,$fecha,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// consultar actividades acudiente
	$actividades = $db->getActividadesAcu($id_per,$id_alu,$fecha);

	$response["error"] = false;	
	$response['actividades'] = array();
	
	if ($actividades != NULL) {			
		while ($actividad = $actividades->fetch_assoc()) {
			$tmp = array();
			$tmp["nom_est"] = $actividad["nom_est"];
			$tmp["hor_act"] = $actividad["hor_act"];
			$tmp["tip_act"] = $actividad["des_tact"];
			$tmp["des_act"] = $actividad["des_act"];
			$tmp["des_mat"] = $actividad["des_mat"];
			$tmp["prof"] = $actividad["prof"];
			array_push($response["actividades"], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * actividades por fecha del estudiante
 * url - /actividadesporfecha/:id_alu/:fecha
 * method - get
 */
$app->get('/actividadesporfecha/:id_alu/:fecha/:db_name', 'authenticate', function($id_alu,$fecha,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);
	
	$cod_cur=$db->getCodCur($id_alu);

	// consultar actividades acudiente
	$actividades = $db->getActividadesByFecha($id_alu,$cod_cur,$fecha);

	$response["error"] = false;	
	$response['actividades'] = array();
	
	if ($actividades != NULL) {			
		while ($actividad = $actividades->fetch_assoc()) {
			$tmp = array();
			//$tmp["nom_est"] = $actividad["nom_est"];
			$tmp["hor_act"] = $actividad["hor_act"];
			$tmp["tip_act"] = $actividad["des_tact"];
			$tmp["des_act"] = $actividad["des_act"];
			$tmp["des_mat"] = $actividad["des_mat"];
			$tmp["prof"] = $actividad["prof"];
			array_push($response["actividades"], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});



/**
 * archivar mensaje
 * url - /archivar/mensaje/
 * method - get
 * params id_per
 */
$app->post('/archivar/mensaje', 'authenticate', function() use ($app) {		
	$response = array();
	
	$db_name = $app->request->post('db_name');

	$db = new DbHandler($db_name);
	//recuperar variables por post
	$id_msg = $app->request->post('id_msg');
	$id_per = $app->request->post('id_per');
	$tip_des = $app->request->post('tip_des');

	// mensaje a archivar
	$mensaje = $db->archivarMensaje($id_msg,$id_per,$tip_des);
	
	if ($mensaje) {
		$response["error"] = false;
		$response["message"] = "El mensaje fue archivado, puede consultarlo en la sección archivados";
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No se encuentra el mensaje";
		echoRespnse(200, $response);
	}
});



/**
 * recuperar mensaje
 * url - /cargar/mensaje/
 * method - get
 * params id_per
 */
$app->get('/cargar/mensaje/:id_msg/:db_name', 'authenticate', function($id_msg,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);		

	// mensaje a recuperar
	$mensaje = $db->recuperarMensaje($id_msg);
	
	if ($mensaje != NULL) {
		$response["error"] = false;
		$response["id_msg"] = $mensaje['id_msg'];
		$response["id_rem"] = $mensaje['id_rem'];
		$response["asu_msg"] = $mensaje['asu_msg'];
		$response["txt_msg"] = $mensaje['txt_msg'];
		$response["fec_msg"] = $mensaje['fec_msg'];
		$response["hor_msg"] = $mensaje['hor_msg'];
		$response["ip_msg"] = $mensaje['ip_msg'];
		$response["est_msg"] = $mensaje['est_msg'];
		$response["pri_msg"] = $mensaje['pri_msg'];
		$response["tipe_msg"] = $mensaje['tipe_msg'];
		$response["res_msg"] = $mensaje['res_msg'];
		$response["url_arc"] = $mensaje['url_arc'];
		$response["id_list"] = $mensaje['id_list'];
		$response["url_arc2"] = $mensaje['url_arc2'];
		$response["url_arc3"] = $mensaje['url_arc3'];
		$response["url_arc4"] = $mensaje['url_arc4'];
		$response["url_arc5"] = $mensaje['url_arc5'];
		$response["tip_usu"] = $mensaje['tip_usu'];
		
		$per = $db->getRemitente($mensaje['tip_usu'],$mensaje['id_rem']);
		if($per != NULL){
			$response["dirigido_a"] = $per['nom_per']." ".$per['ape_per'] ;
		}
		$response["id_des"] = $mensaje['id_rem'];
	
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No se encuentra el mensaje";
		echoRespnse(200, $response);
	}
});


/**
 * recuperar mensaje
 * url - /info/estudiante/mensaje/
 * method - get
 * params id_per
 */
$app->get('/info/estudiante/mensaje/:id_msg/:id_per/:bd/:db_name', 'authenticate', function($id_msg,$id_per,$bd,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);		

	// alumno a recuperar
	$alumno = $db->infoEstMSG($id_msg,$id_per,$bd);
	
	if ($alumno != NULL) {
		$response["error"] = false;
		$response["id_alu"] = $alumno['id_alu'];	
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No se encuentra el alumno";
		echoRespnse(200, $response);
	}
});



/**
 * recuperar estudiante relacionado con del mensaje
 * url - /recuperar/estudiante/mensaje
 * method - get
 * params id_msg
 */
$app->get('/recuperar/estudiante/mensaje/:id_msg/:db_name', 'authenticate', function($id_msg,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// alumno a recuperar
	$alumno = $db->getAlumnoMSG($id_msg);
	
	if ($alumno != NULL) {
		$response["error"] = false;
		$response["id_alu"] = $alumno['id_alu'];
		$response["id_per"] = $alumno['id_per'];
		$response["nom_per"] = $alumno['nom_per'];
		$response["ape_per"] = $alumno['ape_per'];
	
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No se encuentra el alumno del mensaje";
		echoRespnse(200, $response);
	}
});

/**
 * recuperar destinatarios si el mensaje enviado es a mas de 1 persona
 * url - /recuperar/destinatarios/mensaje
 * method - get
 * params id_msg
 */
$app->get('/recuperar/destinatarios/mensaje/:id_msg/:db_name', 'authenticate', function($id_msg,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// lisatdo a recuperar
	$listado = $db->getDestinatariosMSG($id_msg);
	
	$response['error'] = false;
	$response['listado'] = array();
	
	if ($listado != NULL) {
		while ($list = $listado->fetch_assoc()) {
			$tmp = array();
			$tmp["destinatario"] = $list["nom_per"]." ".$list["ape_per"]." - ".$list["des_tfun"];
			array_push($response["listado"], $tmp);
		}
	
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No se encuentra listado de destinatarios";
		echoRespnse(200, $response);
	}
});



/**
 * registrar mensaje
 * url - /registrar/mensaje/
 * method - post
 * params 4
 */
$app->post('/registrar/mensaje', 'authenticate', function() use ($app) {		
	$response = array();
	
	$db_name = $app->request->post('db_name');

	$db = new DbHandler($db_name);
	//recuperar variables por post
	$id_rem = $app->request->post('id_rem');
	$id_des = $app->request->post('id_des');
	$id_alu = $app->request->post('id_alu');
	$asu_msg = $app->request->post('asu_msg');
	$txt_msg = $app->request->post('txt_msg');
	$bd = $app->request->post('bd');
	$tipe_msg='u';//verificar q variable espacios
	$cad=$app->request->post('tipo');
	
	$cad='p'?'Profesor(es)':'Administrativo(s)';
	$res_msg='Mensaje enviado a '.'0'.' '.$cad;
	$fec_msg = date('Y-m-d');
	$hor_msg = date('H:i:s');
	$ip_msg = $_SERVER['REMOTE_ADDR'];
	$est_msg = 'i';
	$pri_msg = 'n';

	// mensaje a REGISTRAR
	$mensaje = $db->crearWebMsg($id_rem, $asu_msg, $txt_msg, $fec_msg, $hor_msg, $ip_msg, $est_msg, $pri_msg, $tipe_msg, $res_msg, '', '', '', '', '', '' ,$bd);
	
	if($id_des!=''){
		$obj = $db->registraDestinatario($mensaje,$id_des,$id_alu,'i','s');
		if($mensaje!='' && $obj){
			$response["error"] = false;
			$response["message"] = 'El mensaje fué enviado correctamente';
			echoRespnse(201, $response);
		}
		else {
			$response["error"] = false;
			$response["message"] = 'Error al enviar el mensaje';
			echoRespnse(201, $response);
		}
	}else{
		$response["error"] = false;
		$response["datos"] = "El Destinatario esta vacio";
		echoRespnse(201, $response);
	}	
});



/**
 * estudiantes
 * url - /estudiantes/acudiente/:usuario_id
 * method - get
 * params usuario_id
 */
$app->get('/estudiantes/acudiente/:usuario_id/:db_name', 'authenticate', function($usuario_id,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// consultar estudiantes asociados al acudiente
	$estudiantes = $db->getEstudiantesAcu($usuario_id);

	$response["error"] = false;	
	$response['estudiantes'] = array();
	
	if ($estudiantes != NULL) {			
		while ($estudiante = $estudiantes->fetch_assoc()) {
			$tmp = array();
			$tmp["id"] = $estudiante["id"];
			$tmp["label"] = $estudiante["label"];
			array_push($response["estudiantes"], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay información disponible";
		echoRespnse(200, $response);
	}
});



/**
 * listado de destinatarios
 * url - /listado/destinatarios/:id_list/:id_alu
 * method - get
 * params usuario_id
 */
$app->get('/listado/destinatarios/:id_list/:id_alu/:db_name', 'authenticate', function($id_list,$id_alu,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// consultar estudiantes asociados al acudiente
	if($id_list == '-3'){
		$destinatarios = $db->getProfesores($id_alu);
	}else if($id_list = '-4'){
		$destinatarios = $db->getFuncionarios($id_alu);
	}

	$response["error"] = false;	
	$response['destinatarios'] = array();
	
	if ($destinatarios != NULL) {			
		while ($destinatario = $destinatarios->fetch_assoc()) {
			$tmp = array();
			$tmp["id"] = $destinatario["id"];
			$tmp["label"] = $destinatario["label"];
			$tmp["id_usuario"] = $destinatario["id_usuario"];
			$tmp["tipo_usuario"] = $destinatario["tipo_usuario"];
			$tmp["nom_per"] = $destinatario["nom_per"];
			$tmp["ape_per"] = $destinatario["ape_per"];
			$tmp["cargo"] = $destinatario["cargo"];
			$tmp["mats"] = $destinatario["mats"];
			$tmp["grad"] = $destinatario["grad"];
			$tmp["id_alu"] = $destinatario["id_alu"];
			array_push($response["destinatarios"], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay información disponible";
		echoRespnse(200, $response);
	}
});



/**
 * registrar mensaje masivo
 * url - /registrar/mensaje/
 * method - post
 * params 4
 */
$app->post('/registrar/mensaje/masivo', function() use($app) {		
	$response = array();
	
	$db_name = $app->request->post('db_name');
	//recuperar variables por post
	$id_rem = $app->request->post('id_rem');
	$asu_msg = $app->request->post('asu_msg');
	$txt_msg = $app->request->post('txt_msg');
	$tipo = $app->request->post('tipo');
	$bd = $app->request->post('bd');
	//array de destinatarios **
	$tamano = $app->request->post('tam');
	$id_alu = $app->request->post('id_alu');
	
	$db = new DbHandler($db_name);
	
	//**
	for($i=0;$i<$tamano;$i++){
		$d[$i] = $app->request->post('dest'.$i.'');
	}	
	
	$tipe_msg=count($d)>1?'m':'u';//si el array de destinatarios es > 1 el tipo es 'm'(masivo) sino es 'u'(unico)
	
	$cad=$tipo=='p'?'Profesor(es)':'Administrativo(s)';
	$res_msg='Mensaje enviado a '.count($d).' '.$cad;
	$fec_msg = date('Y-m-d');
	$hor_msg = date('H:i:s');
	$ip_msg = $_SERVER['REMOTE_ADDR'];
	$est_msg = 'i';
	$pri_msg = 'n';

	
	/*$response["error"] = false;
	$response["id_rem"] = $id_rem;
	$response["asu_msg"] = $asu_msg;
	$response["txt_msg"] = $txt_msg;
	$response["tipo"] = $tipo;
	$response["bd"] = $bd;
	$response["tam"] = $tamano;
	$response["id_alu"] = $id_alu;
	$response["tipe_msg"] = $tipe_msg;
	$response["res_msg"] = $res_msg;
	$response['destinatarios'] = array();
	$response['db_name'] = $db_name;
	foreach($d as $u){			
		$vec=explode('_',$u);
		$tmp = array();
		$tmp["id_des"] = $vec[0];
		$tmp["tip_des"] = $vec[1];
		array_push($response["destinatarios"], $tmp);
	}
	
	echoRespnse(201, $response);*/
	
	// se crea el mensaje en web_msg q solo es una vez asi hayan n destinatarios
	$mensaje = $db->crearWebMsg($id_rem, $asu_msg, $txt_msg, $fec_msg, $hor_msg, $ip_msg, $est_msg, $pri_msg, $tipe_msg, $res_msg, '', '', '', '', '', '' ,$bd);
	
	$ok=true;
	//despues de crear el mensaje se hace un ciclo por cada destinatario y se le envia el mensaje
	if($mensaje != NULL){
		foreach($d as $u){			
			$vec=explode('_',$u);
			if(!$db->registraDestinatario($mensaje,$vec[0],$id_alu,'i',$vec[1])){
				//$ok=false;
				//break;
			}
		}
	}
	if($ok){
		$response["error"] = false;
		$response["message"] = 'El mensaje fué enviado correctamente';
		echoRespnse(201, $response);
	}
	else{
		$response["error"] = true;
		$response["message"] = 'Error al enviar el mensaje';
		echoRespnse(201, $response);
	}
	
});




/**
 * faltas estudiante
 * url - /faltas/estudiante/:id_alu
 * method - get
 * params id_per
 */
$app->get('/faltas/estudiante/:id_alu/:fecha/:db_name', 'authenticate', function($id_alu,$fecha,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// consultar faltas estudiante
	$faltas = $db->getFaltasAlum($id_alu,$fecha);

	$response["error"] = false;	
	$response['faltas'] = array();
	
	if ($faltas != NULL) {			
		while ($falta = $faltas->fetch_assoc()) {
			$tmp = array();
			$tmp["id_alu"] = $falta["id_alu"];
			$tmp["fec_fal"] = $falta["fec_fal"];
			$tmp["tipo_f"] = $falta["tipo_f"];
			$tmp["just_fal"] = $falta["just_fal"];
			$tmp["txtj_fal"] = $falta["txtj_fal"];
			$tmp["des_mat"] = $falta["des_mat"];
			array_push($response["faltas"], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * citaciones estudiante
 * url - /citaciones/estudiante/:id_alu
 * method - get
 * params id_alu
 */
$app->get('/citaciones/estudiante/:id_alu/:fecha/:db_name', 'authenticate', function($id_alu,$fecha,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// consultar citaciones estudiante
	$citaciones = $db->getCitacionesAlum($id_alu,$fecha);

	$response["error"] = false;	
	$response['citaciones'] = array();
	
	if ($citaciones != NULL) {			
		while ($citacion = $citaciones->fetch_assoc()) {
			$tmp = array();
			$tmp["id_alu"] = $citacion["id_alu"];
			$tmp["txt_cit"] = $citacion["txt_cit"];
			$tmp["fec_cit"] = $citacion["fec_cit"];
			$tmp["hor_cit"] = $citacion["hor_cit"];
			$tmp["lug_cit"] = $citacion["lug_cit"];
			$tmp["nom_per"] = $citacion["nom_per"];
			$tmp["ape_per"] = $citacion["ape_per"];
			$tmp["des_mat"] = $citacion["des_mat"];
			array_push($response["citaciones"], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});



/**
 * salidas programadas estudiante
 * url - /salidas/estudiante/:id_alu
 * method - get
 * params id_alu
 */
$app->get('/salidas/estudiante/:id_alu/:fecha/:db_name', 'authenticate', function($id_alu,$fecha,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);

	// consultar salidas programadas estudiante
	$salidas = $db->getSalidasAlum($id_alu,$fecha);

	$response["error"] = false;	
	$response['salidas'] = array();
	
	if ($salidas != NULL) {			
		while ($salida = $salidas->fetch_assoc()) {
			$tmp = array();
			$tmp["id_alu"] = $salida["id_alu"];
			$tmp["fec_sal"] = $salida["fec_sal"];
			$tmp["hor_sal"] = $salida["hor_sal"];
			$tmp["hor_ent"] = $salida["hor_ent"];
			$tmp["est_sal"] = $salida["est_sal"];
			$tmp["res_sal"] = $salida["res_sal"];
			$tmp["obs_sal"] = $salida["obs_sal"];
			array_push($response["salidas"], $tmp);
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});




/**
 * estado evaluativo estudiante
 * url - /estado/evaluativo/:id_alu
 * method - get
 * params id_alu
 */
$app->get('/estado/evaluativo/:id_alu/:id_peri/:db_name', 'authenticate', function($id_alu,$id_peri,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);
	
	//consultar el cod_cur del estudiante
	$cod_cur = $db->getCodCur($id_alu);
	
	//materias q ve el alumno
	$materias = $db->getMateriasAlum($id_alu);

	//consultar evaluaciones del periodo
	$evaluaciones = $db->getEvaluAlumPer($id_alu,$cod_cur,$id_peri);
	
	//while($m = $materias->fetch_assoc())  {$mats[] = $m['des_mat'];}
	$instancias = $db->getInstInstitucionales();
	
	while($e = $evaluaciones->fetch_assoc())  {$data[] = $e;}
	//var_dump($id_alu);

	$response["error"] = false;
	//$response['materias'] = array();
	$insins = json_decode($instancias['val_var'],true);
	foreach($insins as $cod_ins=>$iI){
		if($iI['estado']=='a'){
			$datos .= $iI['codigo'] . "->" . $iI['titulo'] ."\n";
		}
	}
	
	$response['instancias'] = $datos;
	
	if ($materias != NULL && $evaluaciones != NULL) {
		$response["notas"] = array();
		while ($materia = $materias->fetch_assoc()) {
			
			$cod_mat = $materia['cod_mat'];
			//$response[$materia['des_mat']] = array();
			
			$tmp = array();
			$tmp["materia"] = $materia['des_mat'];
			$tmp["profesor"] = $materia['nom_per'] ." ". $materia['ape_per'];
			for($i=0;$i<=sizeof($data);$i++){	
				if($data[$i]['cod_mat'] == $cod_mat){
					
					foreach($insins as $cod_ins=>$iI){
						if($iI['estado']=='a'){
							if($data[$i]['cod_inst'] == $iI['codigo'])
								$tmp['evals'] .= $iI['titulo'];
						}
					}
					
					$tmp["evals"] .= $data[$i]['des_ins'] . "Ç";					
					$tmp["evaluacion"] .= $data[$i]["nota"] . "Ç";
					if($data[$i]['fec_cre'] != "")
						$tmp["fecha"] .= $data[$i]['fec_cre'] . "Ç";
					else if($data[$i]['fec_mod'] != "")
						$tmp["fecha"] .= $data[$i]['fec_mod'] . "Ç";
					else
						$tmp["fecha"] .= "No registraÇ";
				}/*else{
					$tmp["evaluacion"] .= "No hay informacion,";
					$tmp["fecha"] .= "No registra,";
				}*/
			}
			if(!isset($tmp["evaluacion"])){
				$tmp["evals"] = "No hay registros";
				$tmp["evaluacion"] = "No hay notas";
				$tmp["fecha"] = "No registra";
			}
			array_push($response["notas"], $tmp);	
			
			/*while ($evaluacion = $evaluaciones->fetch_assoc()){
				if($evaluacion['cod_mat'] == $cod_mat){
					$tmp = array();
					$tmp["e1"] = $evaluacion["nota"];
					$tmp['fecha'] = $evaluacion['fec_rea'];
					array_push($response[$materia['des_mat']], $tmp);					
				}
			}*/
			//array_push($response["materias"], $materia['des_mat']);			
		}
		//$response['materias'] = $m;
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});




/**
 * estado general estudiante
 * url - /estado/general/:id_alu
 * method - get
 * params id_alu
 */
$app->get('/estado/general/:id_alu/:id_peri/:db_name', 'authenticate', function($id_alu,$id_peri,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);
	
	//consultar el cod_cur del estudiante
	$cod_cur = $db->getCodCur($id_alu);

	//consultar definitivas del periodo
	$definitivas = $db->getDefMatPer($id_alu,$cod_cur,$id_peri);

	$response["error"] = false;
	$response["periodo"]=array();
	
	if ($definitivas != NULL) {			
		while ($definitiva = $definitivas->fetch_assoc()){			
			$tmp = array();
			$tmp["des_mat"] = $definitiva["des_mat"];			
			$tmp['nota_d'] = $definitiva['nota_d'];
			$n=$db->getValoracion($definitiva['nota_d']);
			$tmp['valoracion'] = $n['let_val'];
			
			array_push($response["periodo"], $tmp);			
		}		
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});





/**
 * estado por materia estudiante
 * url - /estado/pormateria/:id_alu
 * method - get
 * params id_alu
 */
$app->get('/estado/pormateria/:id_alu/:db_name', 'authenticate', function($id_alu,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);
	
	//consultar el cod_cur del estudiante
	$mats = $db->getMatsAlu2($id_alu);
	$peris = $db->getPeriodosDef($id_alu);
	$dat = $db->getNotasPeri($id_alu);

	$response["error"] = false;
	
	if ($mats != NULL && $dat != NULL) {
		if(sizeof($peris) > 0){
			$response["notas"] = array();
			foreach($mats as $m){
				$tmp = array();
				$tmp["materia"] = $m['des_mat'];
				$tmp["profesor"] = $m['profe'];
				foreach($peris as $p){
					$tmp['definitiva'] .= $dat[$m['cod_mat']][$p['id_peri']]['nota_d'] ."Ç";
					$n=$db->getValoracion($dat[$m['cod_mat']][$p['id_peri']]['nota_d']);
					$tmp['valoracion'] .= $n['let_val'] ."Ç";
				}
				array_push($response["notas"], $tmp);
			}				
			echoRespnse(201, $response);
		}else{
			$response["error"] = true;
			$response["message"] = "No hay periodos cerrados para consultar las notas";
			echoRespnse(200, $response);
		}
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});





/**
 * periodos cerrados o el actual
 * url - /periodos
 * method - get
 * params 
 */
$app->get('/periodos/:id_alu/:db_name', 'authenticate', function($id_alu,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);
	
	//consultar el cod_cur del estudiante
	$periodos = $db->getPeriodos();

	$response["error"] = false;
	
	if ($periodos != NULL) {
		if($periodos->num_rows > 0){
			$response["periodos"]=array();		
			while ($periodo = $periodos->fetch_assoc()){			
				$tmp = array();
				$tmp["id"] = $periodo["id_peri"];
				$tmp['label'] = $periodo['des_per'];
				array_push($response["periodos"], $tmp);			
			}		
			echoRespnse(201, $response);
		}else{
			$response["error"] = true;
			$response["message"] = "No hay periodos cerrados para consultar las notas";
			echoRespnse(200, $response);
		}
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
	
});



/**
 * Consulta para traer los colegios disponibles
 */
$app->get('/colegios', function() {		
	$response = array();

	$db = new DbHandler('');

	// consultar
	$bds = $db->getBasesDatos();

	$response["error"] = false;	
	$response['colegios'] = array();
	
	if ($bds != NULL) {			
		while ($bd = $bds->fetch_assoc()) {
			$mystring = $bd['bd'];
			$findme = 'bk';
			$findme2 = 'demo';
			$findme3 = 'tmp';
			$findme4 = 'cap';
			$pos = strpos($mystring, $findme);
			$pos2 = strpos($mystring, $findme2);
			$pos3 = strpos($mystring, $findme3);
			$pos4 = strpos($mystring, $findme4);
			if($pos === false && $pos2 === false && $pos3 === false && $pos4 === false){
				$fila2 = $db->getColegio($bd['bd']);
				$tmp = array();
				$tmp["bd"] = $bd["bd"];
				$tmp["label"] = $fila2["nom_ins"];
				array_push($response["colegios"], $tmp);
			}
		}			
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});




/**
 * periodos para boletin informativo
 * url - /periodos
 * method - get
 * params 
 */
$app->get('/periodos/boletin/:db_name', 'authenticate', function($db_name) {		
	$response = array();

	$db = new DbHandler($db_name);
	
	//consultar listado periodos disponibles para boletin informativo
	$periodos = $db->getPeriodosBoletin();

	$response["error"] = false;
	
	if ($periodos != NULL) {
		if($periodos->num_rows > 0){
			$response["periodos"]=array();
			while ($periodo = $periodos->fetch_assoc()){
				$tmp = array();
				$tmp["id"] = $periodo["id_peri"];
				$tmp['label'] = $periodo['des_per'];
				array_push($response["periodos"], $tmp);
			}
			echoRespnse(201, $response);
		}else{
			$response["error"] = true;
			$response["message"] = "No hay periodos cerrados para consultar las notas";
			echoRespnse(200, $response);
		}
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
	
});


/**
 * datos para boletin informativo
 * url - /datos/boeltin
 * method - get
 * params 
 */
$app->get('/datos/boletin/:id_alu/:db_name', 'authenticate', function($id_alu,$db_name) {		
	$response = array();

	$db = new DbHandler($db_name);
	
	//consultar datos de la seccion
	$cur = $db->getSeccion($id_alu);
	$gra = $db->getGrado($cur['cod_gra']);

	if ($cur != NULL && $gra != NULL) {
		$response["error"] = false;
		$response["cod_niv"] = str_pad($gra["cod_niv"],3,0,0);
		$response['cod_gra'] = $gra['cod_gra'];
		$response['cod_cur'] = $cur['cod_cur'];
		$response['id_sed'] = $cur['id_sed'];
		$response['id_jor'] = $cur['id_jor'];
		echoRespnse(201, $response);			
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}	
});
































$app->get('/pruebas', 'authenticate', function() use ($app) {
	$id_dir=1878;
	$pla_veh='SID009';
	$id_fac=761259;
	$num_fac=36987;
	global $id_per;
	global $id_usu;
	$db = new DbHandler();
	//$asig=$db->getUltimaAsignacion(108096144341);
	//$r1=$db->actualizarCilinasignados('v',1037205,108096144341);
	//$r2=$db->crearCilinasignados(1156056,108096144341,'l','a','1.00');
	//$ar=$db->crearAsignacion($pla_veh,date("Y-m-d"),date("H:i:s"),'a','s','','','','c',$id_dir,NULL,NULL,$id_per);
	$fec_ped=date("Y")."-".date("m")."-".date("d");
	//$hra_ped=date("H").":".date("i").":".date("s");
	//$r3=$db->crearCilindrotraza($fec_tra,108096144341,1,'c',$id_dir,'e');
	//$r4=$db->actualizarCilindro($id_dir,108096144341);
	//$r5=$db->actualizarAsignacion(1156056,'v');
	//$r6=$db->actualizarFactura2(1,$pla_veh,$num_fac,108096144341,1,$id_fac);
	//$r7=$db->actualizarVehiculo(13,12,$pla_veh);
	//$r8=$db->actualizarCliente('s',196322);
	//$r9=$db->crearSeguimiento($id_dir,7221055,196322,$fec_ped,$hra_ped,$id_fac,108096144341,$pla_veh,1156056,33000,'a','','v','m','s',NULL,NULL,NULL,NULL,$id_per,4000);
	$val_pro=33000-4000;
	//$r10=$db->crearSeguimiento($id_dir,7221055,196322,$fec_ped,$hra_ped,$id_fac,108096144341,$pla_veh,1156056,$val_pro,'a','','v','c','s',$id_usu,NULL,NULL,NULL,NULL,$id_per,4000);
	//$response['id_asi']=$ae['id_asi'];
	$res = $db->getDatosBarZonLoc(7,396);
	//$datos = $db->getDatosBarZonLoc($id_zon,$id_bar);
	$datosCliente = $db->crearDatosCliente(196344, 'Pepito', 'Perez Quiñonez', 1024536419, 0, 192342, 'mz 12 casa 3', 'frente a la tienda', 1, $res['nom_var'], $res['nom_zon'], $res['nom_loc'], 7202020, 121212, $pla_veh, $fec_ped, $fec_ped, 1);
	echoRespnse(201, $datosCliente);
});


		


/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
	$response = array();
	//echo 'campos '.$request_params;
    $num = 1;
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {//strlen verifica el tamaño de la cadena y trim quita los espacios en blanco y tabulaciones de la cadena
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
		//$response["aja"] = $app->request()->getBody();
		//$response["sad"] = trim($request_params['status']);
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>