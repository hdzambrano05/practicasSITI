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
//esta db name se tendra que cambiar si se modifica el servidor actual


/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */

function authenticate(\Slim\Route $route)
{
	$headers = apache_request_headers();
	$headers = array_change_key_case($headers, CASE_LOWER);

	$response = array();
	$app = \Slim\Slim::getInstance();

	// Verificando Authorization Header
	if (isset($headers['authorization'])) {
		$api_key = $headers['authorization'];

		if ($api_key != API_KEY) {
			$response["error"] = true;
			$response["message"] = "Access Denied. Invalid Api key";
			echoRespnse(401, $response);
			$app->stop();
		} else {
			global $id_per, $bd;
		}
	} else {
		$response["error"] = true;
		$response["message"] = "Api key is missing";
		echoRespnse(400, $response);
		$app->stop();
	}
}

/**
	CRISTHIAN
 **/
$app->post('/getCampoWfsv', 'authenticate', function () {
	$response  = array();
	$data = json_decode(file_get_contents('php://input'), true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$dataAnswer = $db->getCampoWfsv($data);
		$response['error'] = $bandera;
		$response['data'] = $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Se deben enviar id's";
	}
	echoRespnse(200, $response);
});

/**
	CRISTHIAN
 **/
$app->post('/getPlantillaProceso', 'authenticate', function () {
	$response = array();
	$data = json_decode(file_get_contents('php://input'), true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$proceso_id = $data['proceso_id'];
		$dataAnswer = $db->getPlantillaProceso($proceso_id);
		$response['error'] = $bandera;
		$response['data'] = $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Se debe enviar un id de proceso";
	}
	echoRespnse(200, $response);
});


/**
	CRISTHIAN
 **/
$app->post('/updateDocumentoSalida', 'authenticate', function () {
	$response = array();
	$body = json_decode(file_get_contents('php://input'), true);
	$bandera = false;
	$dataAnswer = '';
	if ($body) {
		$db = new DbHandler();
		$dataAnswer = $db->updateDocumentoSalida($body);
		$response['error'] = $bandera;
		$response['data'] = "Filas afectadas" . $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Debe enviar datos validos";
	}
	echoRespnse(200, $response);
});

/**
	CRISTHIAN
 **/
$app->post('/getVariableSistema', 'authenticate', function () {
	$response = array();
	$bandera = false;
	$dataAnswer = '';
	if (true) {
		$db = new DbHandler();
		$dataAnswer = $db->getVariableSistema();
		$response['error'] = $bandera;
		$response['data'] = $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = bandera;
		$response['data'] = $dataAnswer;
	}

	echoRespnse(200, $response);
});

/**
	CRISTHIAN
 **/
$app->post('/getCamposProceso', 'authenticate', function () {
	$response = array();
	$body = json_decode(file_get_contents('php://input'), true);

	$bandera = false;
	$dataAnswer = '';

	if ($body) {
		$db = new DbHandler();
		$id_proceso = $body['id_proceso'];
		$dataAnswer = $db->getCamposProceso($id_proceso);
		$response['error'] = $bandera;
		$response['data'] = $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Debe enviar un id de proceso";
	}
	echoRespnse(200, $response);
});


/**
	CRISTHIAN
 **/
$app->post('/updateDatosTemp', 'authenticate', function () {
	$response = array();
	$body = json_decode(file_get_contents('php://input'), true);
	$bandera = false;
	$dataAnswer = '';
	if ($body) {
		$db = new DbHandler();
		$dataAnswer = $db->updateDatosTemp($body);
		$response['error'] = $bandera;
		$response['data'] = "Filas afectadas: " . $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Debe credenciales válidas";
	}
	echoRespnse(200, $response);
});

/** 
	CRISTHIAN
 **/
$app->post('/getDocumentosSalida', 'authenticate', function () {
	$response = array();
	$bandera = false;
	$dataAnswer = '';
	if (true) {
		$db = new DbHandler();
		$dataAnswer = $db->getDocumentosSalida();
		$response['error'] = $bandera;
		$response['data'] = $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Error en traer documentos de salida";
	}

	echoRespnse(200, $response);
});

/**
	CRISTHIAN
 **/
$app->post('/getDatosTemp', 'authenticate', function () {
	$response = array();
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$usuario_id = $data['usuario_id'];
		$cod_form = $data['cod_form'];
		$id_sujeto = $data['id_sujeto'];
		$dataAnswer = $db->getDatosTemp($usuario_id, $cod_form, $id_sujeto);
		$response['error'] = $bandera;
		$response['data'] = $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Debe enviar un ID de usuario ";
	}

	echoRespnse(200, $response);
});

$app->post('/getCasosProcesos', 'authenticate', function () {
	$response = array();
	//JSON REQUEST
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$ids_procesos = $data['ids_procesos'];
		$core_usuario_id = $data['core_usuario_id'];
		$num = count($ids_procesos);
		if ($num > 0) {
			$dataAnswer =  $db->getCasosProcesos($ids_procesos, $core_usuario_id);
			if (!$dataAnswer) {
				$dataAnswer = "No hay datos";
				$bandera = false;
			}
		} else {
			$dataAnswer = "Ids procesos Incompletos";
			$bandera = true;
		}
	}
	$response['error'] = $bandera;
	$response['data'] = $dataAnswer;
	echoRespnse(200, $response);
});

$app->post('/getSujetoPersona', 'authenticate', function () {
	$response = array();
	//JSON REQUEST
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$core_usuario_id = $data['core_usuario_id'];
		if ($core_usuario_id != '') {
			$detalle = $data['detalle'];
			$municipio = $data['municipio'];
			$zona = $data['zona'];
			$dataAnswer = $db->getSujetoPersona($core_usuario_id, $detalle, $municipio, $zona);
		} else {
			$dataAnswer = "Datos Incompletos";
			$bandera = true;
		}
	}
	$response['error'] = $bandera;
	$response['data'] = $dataAnswer;
	echoRespnse(200, $response);
});

$app->post('/getTareaReglaDerivacion', 'authenticate', function () {
	$response = array();
	//JSON REQUEST
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$token = $data['token'];
		if ($token != '' && $token == '5154n1c0l4s') {
			$dataAnswer = $db->getTareaReglaDerivacion();
		} else {
			$dataAnswer = "Token Invalido";
			$bandera = true;
		}
	}
	$response['error'] = $bandera;
	$response['data'] = $dataAnswer;
	echoRespnse(200, $response);
});

$app->post('/getPasoReglaDerivacion', 'authenticate', function () {
	$response = array();
	//JSON REQUEST
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$token = $data['token'];
		if ($token != '' && $token == '5154n1c0l4s') {
			$dataAnswer = $db->getPasoReglaDerivacion();
		} else {
			$dataAnswer = "Token Invalido";
			$bandera = true;
		}
	}
	$response['error'] = $bandera;
	$response['data'] = $dataAnswer;
	echoRespnse(200, $response);
});

$app->post('/getCanelndarioUsuario', 'authenticate', function () {
	$response = array();
	//JSON REQUEST
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$usuario = $data['core_usuario_id'];
		if ($usuario != '' && $usuario != '') {
			$dataAnswer = $db->getCanelndarioUsuario($usuario);
		} else {
			$dataAnswer = "Por favor enviar el id de usuario";
			$bandera = true;
		}
	}
	$response['error'] = $bandera;
	$response['data'] = $dataAnswer;
	echoRespnse(200, $response);
});

$app->get('/getProcesoCategoria', 'authenticate', function () {
	$response = array();
	$bandera = false;
	$dataAnswer = '';
	if (true) {
		$db = new DbHandler();
		$dataAnswer = $db->getCore_procesocategoria();
		$bandera = true;
	}
	$response['error'] = $bandera;
	$response['data'] = $dataAnswer;
	echoRespnse(200, $response);
});

/* $app->get('/getsujetoverificaciontipo','authenticate', function () {
    $response = array();
	$bandera=false;
	$dataAnswer='';
	if(true){
		$db = new DbHandler();
		$dataAnswer=$db->getsujetoverificaciontipo();
		$bandera=true;
	}
	$response['error'] = $bandera;
    $response['data'] = $dataAnswer;
    echoRespnse(200, $response);
}); */


/**
	Harold-Reportes
 **/
$app->post('/getReportesNova', 'authenticate', function () {

	$response = array();

	$db = new DbHandler();

	$resultado = $db->listarReportes();

	$response['error'] = false;
	$response['data'] = $resultado['data'];

	echoRespnse(200, $response);
});

$app->post('/actualizarDestinoReporte', 'authenticate', function () {

	$response = array();

	$input = file_get_contents('php://input');
	$body = json_decode($input, true);

	$db = new DbHandler();

	$resultado = $db->actualizarDestino(
		$body['id_rpt'],
		$body['destino']
	);

	$response = $resultado;

	echoRespnse(200, $response);
});

$app->post('/actualizarEstadoReporte', 'authenticate', function () {

	$response = array();

	$input = file_get_contents('php://input');
	$body = json_decode($input, true);

	$db = new DbHandler();

	$resultado = $db->actualizarEstado(
		$body['id_rpt'],
		$body['est_rpt']
	);

	$response = $resultado;

	echoRespnse(200, $response);
});

/**
 * Harold - Configuración de parámetros Nova SISA
 */

$app->post('/actualizarReporteNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpt'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar el id del reporte"
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->actualizarReporteNova($body);

	echoRespnse(200, $resultado);
});

/**
 * Harold - Configuración de parámetros Nova SISA
 */

$app->post('/guardarReporteNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['des_rpt'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar la descripción del reporte"
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->guardarReporteNova($body);

	echoRespnse(200, $resultado);
});

$app->post('/getParametrosReporteNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpt'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar id_rpt",
			"data" => []
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->getParametrosReporteNova($body['id_rpt']);

	echoRespnse(200, $resultado);
});

$app->post('/guardarParametroReporteNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpt'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar id_rpt"
		]);
		return;
	}

	if (empty($body['des_rpar'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe ingresar la descripción del parámetro"
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->guardarParametroReporteNova($body);

	echoRespnse(200, $resultado);
});

$app->post('/actualizarParametroReporteNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpar'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar id_rpar"
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->actualizarParametroReporteNova($body);

	echoRespnse(200, $resultado);
});

$app->post('/eliminarParametroReporteNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpar'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar id_rpar"
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->eliminarParametroReporteNova($body['id_rpar']);

	echoRespnse(200, $resultado);
});

$app->post('/getValoresParametroNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpar'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar id_rpar",
			"data" => []
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->getValoresParametroNova($body['id_rpar']);

	echoRespnse(200, $resultado);
});

$app->post('/guardarValorParametroNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpar'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar id_rpar"
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->guardarValorParametroNova($body);

	echoRespnse(200, $resultado);
});

$app->post('/actualizarValorParametroNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpv'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar id_rpv"
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->actualizarValorParametroNova($body);

	echoRespnse(200, $resultado);
});

$app->post('/eliminarValorParametroNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpv'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar id_rpv"
		]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->eliminarValorParametroNova($body['id_rpv']);

	echoRespnse(200, $resultado);
});


$app->post('/generarReporteNova', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body || empty($body['id_rpt'])) {
		echoRespnse(200, [
			"error" => true,
			"message" => "Debe enviar id_rpt",
			"data" => []
		]);
		return;
	}

	$filtros = isset($body['filtros']) ? $body['filtros'] : [];

	$db = new DbHandler();
	$resultado = $db->generarReporteNova($body['id_rpt'], $filtros);

	echoRespnse(200, $resultado);
});



/**
	NICOLAS
 **/
$app->get('/getsujetoverificaciontipo', 'authenticate', function () {

	$db = new DbHandler();

	$variable = $db->getsujetoverificaciontipo();

	$result['data'] = array();
	$result['error'] = array();

	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		$tmp = array();

		$tmp = $line;
		array_push($result['data'], $tmp);
	}

	$result["error"] = false;
	echoRespnse(200, $result);
});
/**
	NICOLAS
 **/
$app->get('/getsujetoverificaciontipo/:id', 'authenticate', function ($id) {

	$db = new DbHandler();
	$variable = $db->getsujetoverificacionpor_id($id);

	$result['data']  = array();
	$result['error'] = array();

	if ($variable === false) {
		$result["error"]   = true;
		$result["message"] = "ID no encontrado.";
		echoRespnse(400, $result);
		return;
	}

	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		array_push($result['data'], $line);
	}

	$result["error"] = false;
	echoRespnse(200, $result);
});
/**
    NICOLAS
 **/
$app->get('/getCategoriasCenso', 'authenticate', function () {

	$db = new DbHandler();
	$categorias = $db->getCategoriasCenso();

	$result = array();

	if (empty($categorias)) {
		$result["error"]   = true;
		$result["message"] = "No se encontraron categorías.";
		echoRespnse(404, $result);
		return;
	}

	$result["error"] = false;
	$result["data"]  = $categorias;
	echoRespnse(200, $result);
});
/**
	NICOLAS
 **/
$app->get('/getcabeceraspor_id/:id', 'authenticate', function ($id) {

	$db = new DbHandler();
	$variable = $db->getcabeceraspor_id($id);

	$result['data']  = array();
	$result['error'] = array();

	if ($variable === false) {
		$result["error"]   = true;
		$result["message"] = "ID no encontrado.";
		echoRespnse(400, $result);
		return;
	}

	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		array_push($result['data'], $line);
	}

	$result["error"] = false;
	echoRespnse(200, $result);
});
/**
	NICOLAS
 **/
$app->get('/getSujeto/:id', 'authenticate', function ($id) {

	$db = new DbHandler();
	$sujeto = $db->getSujeto($id);

	$result = array();

	if ($sujeto === false) {
		$result["error"]   = true;
		$result["message"] = "Sujeto no encontrado.";
		echoRespnse(404, $result);
		return;
	}

	$result["error"] = false;
	$result["data"]  = $sujeto;
	echoRespnse(200, $result);
});
/**
	NICOLAS
 **/
$app->post('/updateSujeto/:id', 'authenticate', function ($id) {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!$body) {
		echoRespnse(400, ["error" => true, "message" => "No se recibieron datos"]);
		return;
	}

	$db = new DbHandler();
	$resultado = $db->updateSujeto($id, $body);

	if ($resultado === false) {
		echoRespnse(400, ["error" => true, "message" => "Sujeto no encontrado o error al actualizar"]);
		return;
	}

	echoRespnse(200, ["error" => false, "message" => "Sujeto actualizado correctamente"]);
});

/**
	NICOLAS-CALENDARIO
 **/
$app->post('/getCalendarios', 'authenticate', function () {
	$body = json_decode(file_get_contents('php://input'), true);
	$db = new DbHandler();
	$variable = $db->getCalendarios($body['usuario_id']);
	$result = ["error" => false, "data" => []];
	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		array_push($result['data'], $line);
	}
	$result['data'][] = ["id" => -1, "nom_cal" => "[TODOS]"];
	echoRespnse(200, $result);
});

/**
	NICOLAS-CALENDARIO
 **/
$app->post('/getEventos', 'authenticate', function () {
	$body = json_decode(file_get_contents('php://input'), true);
	$db = new DbHandler();
	$variable = $db->getEventos($body['usuario_id']);
	$result = ["error" => false, "data" => []];
	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		array_push($result['data'], $line);
	}
	echoRespnse(200, $result);
});

/**
	NICOLAS-CALENDARIO
 **/
$app->post('/getTiposEvento', 'authenticate', function () {
	$db = new DbHandler();
	$variable = $db->getTiposEvento();
	$result = ["error" => false, "data" => []];
	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		array_push($result['data'], $line);
	}
	echoRespnse(200, $result);
});


$app->post('/get_wf_proceso', 'authenticate', function () {
	$response = array();
	$bandera = false;
	$dataAnswer = '';
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	if ($data['id_sujeto'] != '') {
		$db = new DbHandler();
		$dataAnswer = $db->get_wf_proceso_sujeto($data['id_sujeto']);
		$response['error'] = $bandera;
		$response['data'] = $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Debe enviar un ID de sujeto.";
	}

	echoRespnse(200, $response);
});

$app->post('/get-menu', 'authenticate', function () {
	$response = array();
	$bandera = false;
	$dataAnswer = '';
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	if ($data['usuario_id'] != '') {
		$db = new DbHandler();
		$dataAnswer = $db->getMenu($data['usuario_id']);
		$response['error'] = $bandera;
		$response['data'] = $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Debe enviar un ID de usuario.";
	}

	echoRespnse(200, $response);
});

/**
	NICOLAS
 **/
$app->get('/getMunicipioCenso', 'authenticate', function () {
	$db = new DbHandler();
	$variable = $db->getMunicipioCenso();

	$result['data'] = array();
	$result['error'] = array();

	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		$tmp = array();
		$tmp = $line;
		array_push($result['data'], $tmp);
	}
	$result["error"] = false;
	echoRespnse(200, $result);
});

/**
	Insert datos a tabla tmp -> NICOLAS
 **/
$app->post('/insertDatosTemp', 'authenticate', function () {
	$body = json_decode(file_get_contents('php://input'), true);
	if (!$body) {
		echoRespnse(400, ["error" => true, "message" => "No se recibieron datos"]);
		return;
	}
	$db = new DbHandler();
	$id = $db->insertDatosTemp($body);
	if (!$id) {
		echoRespnse(500, ["error" => true, "message" => "Error al insertar"]);
		return;
	}
	echoRespnse(200, ["error" => false, "id_registro" => $id]);
});


/**
Crear Sujeto de Verificacion -> NICOLAS
 **/
$app->post('/addSujeto', 'authenticate', function () {

	$body = json_decode(file_get_contents('php://input'), true);

	if (!isset($body['core_sujetoverificaciontipo_id'])) {
		echoRespnse(400, ["error" => true, "message" => "Falta el tipo de sujeto"]);
		return;
	}

	$db = new DbHandler();
	$nuevo_id = $db->addSujeto($body, $body['core_sujetoverificaciontipo_id']);

	if ($nuevo_id === false) {
		echoRespnse(400, ["error" => true, "message" => "Error al guardar el sujeto"]);
		return;
	}

	echoRespnse(200, [
		"error"   => false,
		"message" => "Sujeto creado correctamente",
		"id"      => $nuevo_id
	]);
});

/**
	Traer campos para agregar sujeto -> NICOLAS
 **/
$app->get('/getCamposFormulario/:id', 'authenticate', function ($id) {

	$db = new DbHandler();
	$campos = $db->getCamposFormulario($id);

	if ($campos === false) {
		echoRespnse(404, ["error" => true, "message" => "Tipo no encontrado"]);
		return;
	}

	echoRespnse(200, ["error" => false, "data" => $campos]);
});

/**
	eliminar sujeto -> NICOLAS
 **/
$app->delete('/deleteSujeto/:id', 'authenticate', function ($id) {

	$db = new DbHandler();
	$resultado = $db->deleteSujeto($id);

	if ($resultado === false) {
		echoRespnse(404, ["error" => true, "message" => "Sujeto no encontrado"]);
		return;
	}

	echoRespnse(200, ["error" => false, "message" => "Sujeto eliminado correctamente"]);
});


/**
	SIGUIENTE FORMULARIO -> NICOLAS
 **/

$app->post('/getSiguienteFormulario', 'authenticate', function () use ($app) {
	$body = json_decode($app->request->getBody(), true);

	$proceso_id            = $body['proceso_id'] ?? null;
	$tarea_id              = $body['tarea_id'] ?? null;
	$cod_formulario_actual = $body['cod_formulario_actual'] ?? null;
	$respuestas            = $body['respuestas'] ?? [];  // ["cod_campo" => "valor"]

	if (!$proceso_id || !$tarea_id || !$cod_formulario_actual) {
		echoRespnse(400, ["error" => true, "message" => "Faltan parámetros requeridos"]);
		return;
	}

	$db = new DbHandler();
	$siguiente = $db->getSiguienteFormulario(
		$proceso_id,
		$tarea_id,
		$cod_formulario_actual,
		$respuestas
	);

	if ($siguiente) {
		echoRespnse(200, [
			"error"                   => false,
			"hay_siguiente"           => true,
			"cod_formulario_siguiente" => $siguiente['cod_formulario_siguiente'],
			"paso_siguiente_id"       => $siguiente['paso_siguiente_id'],
			"tarea_siguiente_id"      => $siguiente['tarea_siguiente_id'],
		]);
	} else {
		echoRespnse(200, [
			"error"         => false,
			"hay_siguiente" => false,
			"message"       => "Fin del flujo de formularios"
		]);
	}
});


$app->post('/getFormularioCompleto', 'authenticate', function () use ($app) {
	$body = json_decode($app->request->getBody(), true);

	$proceso_id     = $body['proceso_id'] ?? null;
	$cod_formulario = $body['cod_formulario'] ?? null;

	if (!$proceso_id || !$cod_formulario) {
		echoRespnse(400, ["error" => true, "message" => "Faltan parámetros"]);
		return;
	}

	$db   = new DbHandler();
	$data = $db->getFormularioCompleto($proceso_id, $cod_formulario);

	if (empty($data)) {
		echoRespnse(404, ["error" => true, "message" => "Formulario no encontrado"]);
		return;
	}

	echoRespnse(200, ["error" => false, "data" => $data]);
});


$app->post('/form-inicial', 'authenticate', function () {
	$response = array();
	$bandera = false;
	$dataAnswer = '';
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	if ($data) {
		$db = new DbHandler();
		$dataAnswer = $db->getFormularioInicial($data['proceso_id']);
		$response['error'] = $bandera;
		$response['data'] = $dataAnswer;
	} else {
		$bandera = true;
		$response['error'] = $bandera;
		$response['data'] = "Debe enviar un ID de Proceso.";
	}

	echoRespnse(200, $response);
});

//Apis Antiguos

$app->post('/get-formularios-proecesos', 'authenticate', function () {
	$response = array();
	//JSON REQUEST
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$core_usuario_id = $data['core_usuario_id'];
		if ($core_usuario_id != '') {
			$dataAnswer = $db->getFormulariosProcesos($core_usuario_id);
		} else {
			$dataAnswer = "Datos Incompletos";
			$bandera = true;
		}
	}
	$response['error'] = $bandera;
	$response['data'] = $dataAnswer;
	echoRespnse(200, $response);
});

$app->post('/login-sisa', 'authenticate', function () {
	$response = array();
	//JSON REQUEST
	$input = file_get_contents('php://input');
	$data = json_decode($input, true);
	$bandera = false;
	$dataAnswer = '';
	if ($data) {
		$db = new DbHandler();
		$user = $data['user'];
		$pass = $data['pass'];
		if ($user == '' || $pass == '') {
			$dataAnswer = "Datos incompletos";
			$bandera = true;
		} else {
			$answer = $db->loginMovil($user, $pass);
			if ($answer['nom_usu'] != '') {
				$dataAnswer = $answer;
				$bandera = false;
			} else {
				$dataAnswer = "Usuario y contraseña incorrectos";
				$bandera = true;
			}
		}
	}
	$response['error'] = $bandera;
	$response['data'] = $dataAnswer;
	echoRespnse(200, $response);
});

/**
 *Retorna todos los municipios sisa
 **/
$app->get('/municipios/sisa', 'authenticate', function () {

	$db = new DbHandler();

	$variable = $db->getMunicipios();

	$result['data'] = array();
	$result['error'] = array();

	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		$tmp = array();

		$tmp = $line;
		array_push($result['data'], $tmp);
	}

	$result["error"] = false;
	echoRespnse(200, $result);
});


/**
 *Retorna todos los municipios sisa
 **/
$app->get('/establecimientos/sisa/:municipio/:nombre/:nit', 'authenticate', function ($municipio, $nombre, $nit) {

	$db = new DbHandler();



	$today = date("Y-m-d");

	if ($nit == '0') {
		$variable = $db->getEstablecimientos($municipio, $today, $nombre);
	} else {
		$variable = $db->getEstablecimientosNit($municipio, $today, $nombre);
	}


	$result['respuesta'] = array();
	$result['error'] = array();


	//echo "<table>\n";		
	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		//echo "\t<tr>\n";
		$tmp = array();

		$tmp = $line;

		/*foreach ($line as $col_value) {
              		
              		$tmp['id'] = $col_value;	  

              		//print_r($col_value);
                  //echo "\t\t<td>$col_value</td>\n";
              }*/
		array_push($result['respuesta'], $tmp);
		//echo "\t</tr>\n";
	}

	$result["error"] = false;
	echoRespnse(200, $result);
});

/**
 *Retorna todos los municipios sisa
 **/
$app->get('/establecimientos/sisa/:municipio/', 'authenticate', function ($municipio) {

	$db = new DbHandler();



	$today = date("Y-m-d");

	$variable = $db->getEstablecimientosMuni($municipio, $today);


	$result['respuesta'] = array();
	$result['error'] = array();


	//echo "<table>\n";		
	while ($line = pg_fetch_array($variable, null, PGSQL_ASSOC)) {
		//echo "\t<tr>\n";
		$tmp = array();
		$tmp = $line;

		/*foreach ($line as $col_value) {
              		
              		$tmp['id'] = $col_value;	  

              		//print_r($col_value);
                  //echo "\t\t<td>$col_value</td>\n";
              }*/
		array_push($result['respuesta'], $tmp);
		//echo "\t</tr>\n";
	}

	$result["error"] = false;
	echoRespnse(200, $result);
});



$app->get('/hora/sisa/', 'authenticate', function () {
	$today = date("Y-m-d");
	print_r($today);
});












/*----------------------------------------------------------- Aqui Inicia Supergas -----------------------------------------------------------*/
//$app->get('/mensajes/recibidos/:id_des/:bd/:db_name', 'authenticate', function($id_des,$bd,$db_name) {		
$app->post('/login/super', 'authenticate', function () use ($app) {

	$db = new DbHandler();

	$name = $app->request()->params('name');
	$password = $app->request()->params('password');

	$password = md5($password);
	$response['usuario'] =  array();

	$res = $db->loginSuper($name, $password);


	if ($res != NULL) {

		if ($res->num_rows > 0) {


			while ($f = $res->fetch_assoc()) {
				$tmp = array();
				$tmp["id_usu"] = $f["id_usu"];
				$tmp["nom_usu"] = $f["nom_usu"];
				$tmp["pas_usu"] = $f["pas_usu"];
				$tmp["tip_usu"] = $f["tip_usu"];
				$tmp["id_per"] = $f["id_per"];
				$tmp["id_tfun"] = $f["id_tfun"];
				$tmp["est_usu"] = $f["est_usu"];

				array_push($response['usuario'], $tmp);
			}
			$response["error"] = false;
			echoRespnse(201, $response);
		} else {
			$response["error"] = true;
			$response["message"] = "Error Usuario o contraseña incorrectos";
			echoRespnse(200, $response);
		}
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});


$app->get('/name/user/:id_per', 'authenticate', function ($id_per) {


	$db = new DbHandler();

	$res = $db->nameUser($id_per);

	$response['respuesta'] = array();



	if ($res != NULL) {

		$row = $res->fetch_assoc();

		$tmp = array();
		$tmp["nom_per"] = $row["nom_per"];
		$tmp["ape_per"] = $row["ape_per"];

		array_push($response["respuesta"], $tmp);
		$response["error"] = false;
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});

/**
 *Funcion que me retorna todas las ciudad existentes en la bd
 */

$app->get('/nivel/ciudad', 'authenticate', function () {

	$db = new DbHandler();
	$res = $db->getNivelCiudad();
	$response['respuesta'] = array();

	if ($res != NULL) {
		while ($row = $res->fetch_assoc()) {

			$tmp = array();
			$tmp["cod_niv"] = $row["cod_niv"];
			$tmp["des_niv"] = $row["des_niv"];
			$tmp["cod_niv_DANE"] = $row["cod_niv_DANE"];
			$tmp["url_seg"] = $row["url_seg"];
			$tmp["url_bol"] = $row["url_bol"];

			array_push($response["respuesta"], $tmp);
		}
		$response["error"] = false;
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});

/**
 *Funcion que me retorna la zona segunr $cod_niv
 */
$app->get('/grado/ciudad', 'authenticate', function () {


	$db = new DbHandler();
	$res = $db->getGradoCiudad();
	$response['respuesta'] = array();

	if ($res != NULL) {
		while ($row = $res->fetch_assoc()) {

			$tmp = array();
			$tmp["cod_gra"] = $row["cod_gra"];
			$tmp["des_gra"] = $row["des_gra"];
			$tmp["cod_niv"] = $row["cod_niv"];
			$tmp["cod_gra_DANE"] = $row["cod_gra_DANE"];
			$tmp["ord_gra"] = $row["ord_gra"];

			array_push($response["respuesta"], $tmp);
		}
		$response["error"] = false;
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});

/**
 *Funcion que retorna barrio segun cod_gra
 */
$app->get('/curso/ciudad', 'authenticate', function () {


	$db = new DbHandler();
	$res = $db->getCursoCiudad();
	$response['respuesta'] = array();

	if ($res != NULL) {
		while ($row = $res->fetch_assoc()) {

			$tmp = array();
			$tmp["cod_cur"] = $row["cod_cur"];
			$tmp["id_per"] = $row["id_per"];
			$tmp["des_cur"] = $row["des_cur"];
			$tmp["des_cur_cor"] = $row["des_cur_cor"];
			$tmp["gru_cur"] = $row["gru_cur"];
			$tmp["id_ano"] = $row["id_ano"];
			$tmp["cod_gra"] = $row["cod_gra"];
			$tmp["id_jor"] = $row["id_jor"];
			$tmp["id_sed"] = $row["id_sed"];


			array_push($response["respuesta"], $tmp);
		}
		$response["error"] = false;
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});


/**
 *Retorna la fecha
 **/

$app->get('/fecha/supergas', 'authenticate', function () {


	$response['message'] = date('Y/m/d H:i:s');
	echoRespnse(200, $response);
});


$app->post('/listar/supergas', 'authenticate', function () use ($app) {

	$db = new DbHandler();

	$cod_niv = $app->request()->params('cod_niv');
	$cod_gra = $app->request()->params('cod_gra');


	$response['respuesta'] = array();

	$res = $db->getListarSupergas($cod_niv, $cod_gra);

	if ($res != null) {

		while ($row = $res->fetch_assoc()) {

			$tmp = array();
			$tmp["id_gmed"] = $row["id_gmed"];
			$tmp["id_alu"] = $row["id_alu"];
			$tmp["fins_gmed"] = $row["fins_gmed"];
			$tmp["ns_gmed"] = $row["ns_gmed"];
			$tmp["marc_gmed"] = $row["marc_gmed"];
			$tmp["est_gmed"] = $row["est_gmed"];
			$tmp["dir_gmed"] = $row["dir_gmed"];
			$tmp["num_cont"] = $row["num_cont"];
			$tmp["cod_cur"] = $row["cod_cur"];
			$tmp["id_per"] = $row["id_per"];
			$tmp["tip_uso"] = $row["tip_uso"];
			$tmp["apt_gmed"] = $row["apt_gmed"];
			$tmp["cen_pob"] = $row["cen_pob"];
			$tmp["tip_ubi"] = $row["tip_ubi"];
			$tmp["eto_dir"] = $row["eto_dir"];
			$tmp["id_per1"] = $row["id_per"];
			$tmp["ape_per"] = $row["ape_per"];
			$tmp["nom_per"] = $row["nom_per"];
			$tmp["cod_gra"] = $row["cod_gra"];
			$tmp["des_gra"] = $row["des_gra"];
			$tmp["des_cur"] = $row["des_cur"];
			$tmp["des_niv"] = $row["des_niv"];
			$tmp["id_glec"] = $row["id_glec"];
			$tmp["fec_glec"] = $row["fec_glec"];
			$tmp["lact_glec"] = $row["lact_glec"];
			$tmp["obs_glec"] = $row["obs_glec"];
			$tmp["lant_glec"] = $row["lant_glec"];
			$tmp["img_glec"] = $row["img_glec"];

			array_push($response["respuesta"], $tmp);
		}
		$response["error"] = false;
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});



/**
 *Retorna tabla alumno
 **/
$app->get('/alumno/supergas', 'authenticate', function () {


	$db = new DbHandler();
	$res = $db->getAlumnoSuper();
	$response['respuesta'] = array();

	if ($res != NULL) {
		while ($row = $res->fetch_assoc()) {

			$tmp = array();
			$tmp["id_alu"] = $row["id_alu"];
			$tmp["id_per"] = $row["id_per"];
			$tmp["cod_tip"] = $row["cod_tip"];
			$tmp["nom_per"] = $row["nom_per"];
			$tmp["ape_per"] = $row["ape_per"];
			$tmp["nac_per"] = $row["nac_per"];
			$tmp["cna_per"] = $row["cna_per"];
			$tmp["sex_per"] = $row["sex_per"];
			$tmp["ufo_alu"] = $row["ufo_alu"];
			$tmp["id1_acu"] = $row["id1_acu"];
			$tmp["id2_acu"] = $row["id2_acu"];
			$tmp["id_res"] = $row["id_res"];
			$tmp["est_alu"] = $row["est_alu"];
			$tmp["new_est"] = $row["new_est"];


			array_push($response["respuesta"], $tmp);
		}
		$response["error"] = false;
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});

/**
 *Retorna tabla gra_medidor
 **/
$app->get('/gra_medidor/supergas', 'authenticate', function () {


	$db = new DbHandler();
	$res = $db->getGraMedidorSuper();
	$response['respuesta'] = array();

	if ($res != NULL) {
		while ($row = $res->fetch_assoc()) {

			$tmp = array();
			$tmp["id_gmed"] = $row["id_gmed"];
			$tmp["id_alu"] = $row["id_alu"];
			$tmp["fins_gmed"] = $row["fins_gmed"];
			$tmp["ns_gmed"] = $row["ns_gmed"];
			$tmp["marc_gmed"] = $row["marc_gmed"];
			$tmp["est_gmed"] = $row["est_gmed"];
			$tmp["dir_gmed"] = $row["dir_gmed"];
			$tmp["num_cont"] = $row["num_cont"];
			$tmp["cod_cur"] = $row["cod_cur"];
			$tmp["id_per"] = $row["id_per"];
			$tmp["tip_uso"] = $row["tip_uso"];
			$tmp["apt_gmed"] = $row["apt_gmed"];
			$tmp["cen_pob"] = $row["cen_pob"];
			$tmp["tip_ubi"] = $row["tip_ubi"];
			$tmp["eto_dir"] = $row["eto_dir"];

			array_push($response["respuesta"], $tmp);
		}
		$response["error"] = false;
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});

/**
 *Retorna tabla gra_medidor
 **/
$app->get('/gra_lectura/supergas', 'authenticate', function () {


	$db = new DbHandler();
	$res = $db->getGraLecturaSuper();
	$response['respuesta'] = array();

	if ($res != NULL) {
		while ($row = $res->fetch_assoc()) {

			$tmp = array();
			$tmp["id_glec"] = $row["id_glec"];
			$tmp["id_gmed"] = $row["id_gmed"];
			$tmp["fec_glec"] = $row["fec_glec"];
			$tmp["lant_glec"] = $row["lant_glec"];
			$tmp["lact_glec"] = $row["lact_glec"];
			$tmp["dif_glec"] = $row["dif_glec"];
			$tmp["obs_glec"] = $row["obs_glec"];
			$tmp["id_per"] = $row["id_per"];
			$tmp["fec_gre"] = $row["fec_gre"];
			$tmp["id_usu_cre"] = $row["id_usu_cre"];
			$tmp["fec_glec_d"] = $row["fec_glec_d"];
			$tmp["id_goper"] = $row["id_goper"];

			array_push($response["respuesta"], $tmp);
		}
		$response["error"] = false;
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});


/**
 * funcion que sube los datos que estaban en sql lite
 */
$app->post('/upload_date/supergas', 'authenticate', function () use ($app) {

	$db = new DbHandler();

	$id_glec = $app->request()->params('id_glec');
	$id_gmed = $app->request()->params('id_gmed');
	$fec_glec = $app->request()->params('fec_glec');
	$lant_glec = $app->request()->params('lant_glec');
	$lact_glec = $app->request()->params('lact_glec');
	$dif_glec = $app->request()->params('dif_glec');
	$obs_glec = $app->request()->params('obs_glec');
	$id_per = $app->request()->params('id_per');
	$fec_cre = $app->request()->params('fec_cre');
	$id_usu_cre = $app->request()->params('id_usu_cre');
	$img_glec = $app->request()->params('img_glec');


	$res = $db->setTableUpdate($id_glec, $id_gmed, $fec_glec, $lant_glec, $lact_glec, $dif_glec, $obs_glec, $id_per, $fec_cre, $id_usu_cre, $img_glec);

	if ($res != null) {
		$response['respuesta'] = array();
		$response['respuesta'] = "Todo salio bien";
		$response["error"] = false;
		echoRespnse(200, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "Ups, Algo salio mal";
		echoRespnse(200, $response);
	}
});


/*
	for($i=0;$i<$tamano;$i++){
		$d[$i] = $app->request->post('faltab'.$i.'');
	}
	
*/



/*----------------------------------------------------------- Aqui Termina Supergas -----------------------------------------------------------*/


/**
 * User Login
 * url - /login
 * method - POST
 * params - nom_usu, password
 */
$app->post('/login', function () use ($app) {

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
	$du = $db->checkLoginAPI($nom_usu, $password);


	if ($du != NULL) { //realiza el login del usuario con usuario y contraseña
		// get the user by username
		if ($du['bd'] == "w") {
			/*
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
			*/
			$response['error'] = true;
			$response['message'] = 'La aplicación solo es para estudiantes';
		} else if ($du['bd'] == "s") {
			/*$response["error"] = false;
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
			*/
			$response['error'] = true;
			$response['message'] = 'La aplicación solo es para estudiantes';
		} else if ($du['bd'] == "e") {
			//$response['error'] = true;
			//$response['message'] = 'La aplicación solo es para padres de familia ó profesores';

			//usuario id(curso)
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

			$curso = $db->cursoDbEstudiante($du['usuario_id']);
			$response['des_gra'] = $curso['des_gra'];
			$response['des_cur_cor'] = $curso['des_cur_cor'];

			$materias = $db->materiasAlum($du['usuario_id']);
			$response['mats'] = $materias;
			//$response['api'] = $api;
		}
	} else {
		// user credentials are cairo_surface_write_to_png(surface, stream)
		$response['error'] = true;
		$response['message'] = 'Error: Credenciales incorrectas';
	}

	echoRespnse(200, $response);
});

$app->post('/contenidos/digitales', 'authenticate', function () use ($app) {
	// reading post params
	$cod_cur = $app->request()->post('cod_cur');
	$db_name = $app->request()->post('db_name');
	$cod_mat = $app->request()->post('cod_mat');
	$cod_gra = $app->request()->post('cod_gra');
	$url_sitio = $app->request()->post('url_sitio');

	$response = array();

	$db = new DbHandler($db_name);
	$du = $db->getContenidosD($cod_cur, $cod_mat, $cod_gra, 0, $url_sitio, $db_name);
	//$response['des_cdtem'] = $du["des_cdtem"];
	//echo "-------------------------------------";
	//print_r($du);
	$response["error"] = false;
	$response["respuesta"] = $du;
	echoRespnse(200, $response);
});


/*tareas y contenidos
	*---------
	*/
$app->post('/tareas/foros', 'authenticate', function () use ($app) {

	$cod_cur = $app->request()->post('cod_cur');
	$id_alu = $app->request()->post('id_alu');
	$db_name = $app->request()->post('db_name');

	$response = array();
	$response["error"] = false;
	$response['recibidos'] = array();
	$response['fecha'] = array();

	$db = new DbHandler($db_name);

	$recibidos = $db->forosAndTareas($id_alu, $cod_cur);


	if ($recibidos != NULL) {
		while ($f = $recibidos->fetch_assoc()) {
			$tmp = array();
			$tmp["tipo"] = $f["tipo"];
			$tmp["nom_actividad"] = $f["nom_actividad"];
			$tmp["des_mat"] = $f["des_mat"];
			$tmp["fecha_fin"] = $f["fecha_fin"];
			$tmp["hora_fin"] = $f["hora_fin"];
			$tmp["semaf"] = $f["semaf"];
			$tmp["cod_mat"] = $f["cod_mat"];
			$tmp["id_actividad"] = $f["id_actividad"];
			$tmp["estado"] = $f["estado"];
			array_push($response['recibidos'], $tmp);
		}
		$response['fecha'] = getdate();
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * mensajes recibidos
 * url - /mensajes/recibidos/:
 * method - get
 * params id_des, bd
 */
$app->get('/mensajes/recibidos/:id_des/:bd/:db_name', 'authenticate', function ($id_des, $bd, $db_name) {
	$response = array();

	//global $id_per;
	$db = new DbHandler($db_name);
	//capturo el id_per
	//global $id_per;
	//global $bd;

	// consultar mensajes recibidos
	$recibidos = $db->getRecibidos($id_des, $bd);

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
			$tmp["est_msg"] = $recibido["est_msg"];
			$tmp["img"] = $recibido["img"];
			$tmp["tip_usu"] = $recibido["tip_usu"];
			$tmp["tip_des"] = $recibido["tip_des"];
			$tmp["url_arc"] = $recibido["url_arc"];
			$tmp["url_arc2"] = $recibido["url_arc2"];
			$tmp["url_arc3"] = $recibido["url_arc3"];
			$tmp["url_arc4"] = $recibido["url_arc4"];
			$tmp["url_arc5"] = $recibido["url_arc5"];
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
$app->get('/mensajes/enviados/:id_rem/:db_name', 'authenticate', function ($id_rem, $db_name) {
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
			$tmp["url_arc"] = $enviado["url_arc"];
			$tmp["url_arc2"] = $enviado["url_arc2"];
			$tmp["url_arc3"] = $enviado["url_arc3"];
			$tmp["url_arc4"] = $enviado["url_arc4"];
			$tmp["url_arc5"] = $enviado["url_arc5"];
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
$app->get('/mensajes/archivados/:id_des/:db_name', 'authenticate', function ($id_des, $db_name) {
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
			$tmp["url_arc"] = $archivado["url_arc"];
			$tmp["url_arc2"] = $archivado["url_arc2"];
			$tmp["url_arc3"] = $archivado["url_arc3"];
			$tmp["url_arc4"] = $archivado["url_arc4"];
			$tmp["url_arc5"] = $archivado["url_arc5"];
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
$app->get('/actividades/acudiente/:id_per/:id_alu/:cod_cur/:db_name', 'authenticate', function ($id_per, $id_alu, $cod_cur, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar actividades acudiente
	$periodo = $db->cargarPeriodos(1, 1, 1);
	// consultar actividades acudiente
	$actividades = $db->getActividadesAcu($id_per, $periodo['fec_ini'], $periodo['fec_cor_tot']);
	//consultar enlaces y adj estudiante
	$enlacesarc = $db->adjEnlActividadesEst($id_per, $periodo['fec_ini'], $periodo['fec_cor_tot']);
	// consultar salidas programadas estudiante
	$tarOrForos = $db->getTarOrForos($cod_cur);

	$salidas = $db->getSalidasAlum($id_alu, $periodo['fec_ini'], $periodo['fec_cor_tot']);

	$faltas = $db->getFaltasAlum($id_alu, $periodo['fec_ini'], $periodo['fec_cor_tot']);
	// consultar citaciones estudiante
	$citaciones = $db->getCitacionesAlum($id_alu, $periodo['fec_ini'], $periodo['fec_cor_tot']);

	$response["error"] = false;
	$response["fec_ini"] = $periodo["fec_ini"];
	$response["fec_fin"] = $periodo["fec_cor_tot"];
	$response['actividades'] = array();
	$response['salidas'] = array();
	$response['citaciones'] = array();
	$response['faltas'] = array();
	$response['enl_arc'] = array();
	$response['for_or_tarea'] = array();

	if ($tarOrForos != NULL) {
		while ($enlarc = $tarOrForos->fetch_assoc()) {
			$tmp = array();
			$tmp["nom_tarea"] = $enlarc["nom_tarea"];
			$tmp["fecha_fin"] = $enlarc["fecha_fin"];
			$tmp["hora_fin"] = $enlarc["hora_fin"];
			$tmp["des_mat"] = $enlarc["des_mat"];
			$tmp["cod_cur"] = $enlarc["cod_cur"];
			$tmp["cod_gra"] = $enlarc["cod_gra"];
			$tmp["cod_mat"] = $enlarc["cod_mat"];
			$tmp["id_usu"] = $enlarc["id_usu"];
			$tmp["id_per"] = $enlarc["id_per"];
			$tmp["id_actividad"] = $enlarc["id_actividad"];
			array_push($response["for_or_tarea"], $tmp);
		}
	}



	if ($enlacesarc != NULL) {
		while ($enlarc = $enlacesarc->fetch_assoc()) {
			$tmp = array();
			$tmp["nom_est"] = $enlarc["nom_est"];
			$tmp["hor_act"] = $enlarc["hor_act"];
			$tmp["fec_act"] = $enlarc["fec_act"];
			$tmp["tip_act"] = $enlarc["des_tact"];
			$tmp["des_act"] = $enlarc["des_act"];
			$tmp["des_mat"] = $enlarc["des_mat"];
			$tmp["prof"] = $enlarc["prof"];
			$tmp["enlace"] = $enlarc["enlace"];
			$tmp["url_arc"] = $enlarc["url_arc"];
			array_push($response["enl_arc"], $tmp);
		}
	}



	if ($actividades != NULL) {
		while ($actividad = $actividades->fetch_assoc()) {
			$tmp = array();
			$tmp["nom_est"] = $actividad["nom_est"];
			$tmp["hor_act"] = $actividad["hor_act"];
			$tmp["fec_act"] = $actividad["fec_act"];
			$tmp["tip_act"] = $actividad["des_tact"];
			$tmp["des_act"] = $actividad["des_act"];
			$tmp["des_mat"] = $actividad["des_mat"];
			$tmp["prof"] = $actividad["prof"];
			$tmp["arc_act"] = $actividad["arc_act"];
			$tmp["enlace"] = $actividad["enlace"];
			$tmp["url_arc"] = $actividad["url_arc"];
			array_push($response["actividades"], $tmp);
		}
		//echoRespnse(201, $response);
	}

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
		//echoRespnse(201, $response);
	}

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
		//echoRespnse(201, $response);
	}

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
		//echoRespnse(201, $response);
	}

	echoRespnse(201, $response);
});







//upload archivo // ide remitente
$app->post('/upload', function () use ($app) {

	/*$db_name = $app->request->post('db_name'); */

	$target_path = basename($_FILES['uploadedfile']['name']);


	$db_name = $app->request->headers('db-name');

	$dia = date('Ymd');
	$hora = time('H:i:s');

	$nombre = $dia . $hora . "-" . $id_rem . $target_path;


	$response = array();
	$response["error"] = false;

	//print_r($ $_FILES['uploadedfile']['$db_name']);

	// print_r($app->request->HTTP_DB_NAME('db_name')); 

	$destino = "../../" . $db_name . "/uploads/buzon/" . $nombre;

	//print_r($destino);

	if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $destino)) {
		$response["error"] = false;
		$response["name_file"] = $nombre;
		echoRespnse(201, $response);
		// echo "Archivo ". $target_path . "subido correctamente";
	} else {
		$response["error"] = true;
		echoRespnse(201, $response);
	}
});



/**
 * archivar mensaje
 * url - /archivar/mensaje/
 * method - get
 * params id_per
 */
$app->post('/archivar/mensaje', 'authenticate', function () use ($app) {
	$response = array();

	$db_name = $app->request->post('db_name');

	$db = new DbHandler($db_name);
	//recuperar variables por post
	$id_msg = $app->request->post('id_msg');
	$id_per = $app->request->post('id_per');
	$tip_des = $app->request->post('tip_des');

	// mensaje a archivar
	$mensaje = $db->archivarMensaje($id_msg, $id_per, $tip_des);

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
$app->get('/cargar/mensaje/:id_msg/:db_name', 'authenticate', function ($id_msg, $db_name) {
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

		$per = $db->getRemitente($mensaje['tip_usu'], $mensaje['id_rem']);
		if ($per != NULL) {
			$response["dirigido_a"] = $per['nom_per'] . " " . $per['ape_per'];
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
 *pesos de los archivos a subir
 *
 */

$app->get('/peso/mensaje/:db_name', 'authenticate', function ($db_name) {

	$db = new DbHandler($db_name);
	$response = array();
	$response["error"] = 'false';
	$response["tamano"] = '0';

	$sizeMax = $db->sixeMaxAdjunto();

	if ($sizeMax != NULL) {
		$response["error"] = 'false';
		$response["tamano"] = $sizeMax;
		echoRespnse(200, $response);
	} else {
		$response["error"] = 'true';
		$response["tamano"] = "0";
		echoRespnse(200, $response);
	}
});


$app->get('/info/importante/:db_name/:cod_cur/:cod_mat', 'authenticate', function ($db_name, $cod_cur, $cod_gra) {

	$db = new DbHandler($db_name);

	$response = array();

	$response["error"] = false;

	$response['recibidos'] = array();

	$response['fecha'] = array();

	$infoImp = $db->infoImportante($cod_cur, $cod_gra);

	if ($infoImp != NULL) {
		while ($f = $infoImp->fetch_assoc()) {
			$tmp = array();
			$tmp["id_apo"] = $f["id_apo"];
			$tmp["des_apo"] = $f["des_apo"];
			$tmp["id_usu"] = $f["id_usu"];
			$tmp["fpub_apo"] = $f["fpub_apo"];
			$tmp["fmod_apo"] = $f["fmod_apo"];
			$tmp["ffin_apo"] = $f["ffin_apo"];
			$tmp["nom_dad"] = $f["nom_dad"];
			$tmp["url_dad"] = $f["url_dad"];
			$tmp["cod_gra"] = $f["cod_gra"];
			$tmp["cod_cur"] = $f["cod_cur"];
			$tmp["cod_mat"] = $f["cod_mat"];
			$tmp["priv_apo"] = $f["priv_apo"];
			$tmp["est_apo"] = $f["est_apo"];
			array_push($response['recibidos'], $tmp);
		}
		$response['fecha'] = getdate();
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});

$app->get('/mensaje/bienvenida/:db_name', 'authenticate', function ($db_name) {


	$db = new DbHandler($db_name);

	$mensajeWell = $db->mensajeWellcome();

	if ($mensajeWell != NULL) {

		$response["error"] = false;

		$response["respuesta"] = $mensajeWell;

		echoRespnse(200, $response);
	} else {

		$response["error"] = true;

		echoRespnse(200, $response);
	}
});


/**
 *formatos de docs a subir
 *
 */

$app->get('/adjunto/tipo/mensaje/peso/:db_name', 'authenticate', function ($db_name) {

	$db = new DbHandler($db_name);

	$sizeMax = $db->formatoAdjMensaje();

	if ($sizeMax != NULL) {

		$response["error"] = false;
		$response["extencion"] = $sizeMax;
		echoRespnse(200, $response);
	} else {
		$response["error"] = false;
		$response["extencion"] = "0";
		echoRespnse(200, $response);
	}
});




/**
 * recuperar mensaje
 * url - /info/estudiante/mensaje/
 * method - get
 * params id_per
 */
$app->get('/info/estudiante/mensaje/:id_msg/:id_per/:bd/:db_name', 'authenticate', function ($id_msg, $id_per, $bd, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	// alumno a recuperar
	$alumno = $db->infoEstMSG($id_msg, $id_per, $bd);

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
$app->get('/recuperar/estudiante/mensaje/:id_msg/:db_name', 'authenticate', function ($id_msg, $db_name) {
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
$app->get('/recuperar/destinatarios/mensaje/:id_msg/:db_name', 'authenticate', function ($id_msg, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	// lisatdo a recuperar
	$listado = $db->getDestinatariosMSG($id_msg);

	$response['error'] = false;
	$response['listado'] = array();

	if ($listado != NULL) {
		while ($list = $listado->fetch_assoc()) {
			$tmp = array();
			$tmp["destinatario"] = $list["nom_per"] . " " . $list["ape_per"] . " - " . $list["des_tfun"];
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
$app->post('/registrar/mensaje', 'authenticate', function () use ($app) {
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
	$tipe_msg = 'u'; //verificar q variable espacios
	$cad = $app->request->post('tipo');



	$cad = 'p' ? 'Profesor(es)' : 'Administrativo(s)';
	$res_msg = 'Mensaje enviado a ' . '0' . ' ' . $cad;
	$fec_msg = date('Y-m-d');
	$hor_msg = date('H:i:s');
	$ip_msg = $_SERVER['REMOTE_ADDR'];
	$est_msg = 'i';
	$pri_msg = 'n';






	// mensaje a REGISTRAR
	$mensaje = $db->crearWebMsg($id_rem, $asu_msg, $txt_msg, $fec_msg, $hor_msg, $ip_msg, $est_msg, $pri_msg, $tipe_msg, $res_msg, '', '', '', '', '', '', $bd);

	if ($id_des != '') {
		$obj = $db->registraDestinatario($mensaje, $id_des, $id_alu, 'i', 's');
		if ($mensaje != '' && $obj) {
			$response["error"] = false;
			$response["message"] = 'El mensaje fué enviado correctamente';
			echoRespnse(201, $response);
		} else {
			$response["error"] = false;
			$response["message"] = 'Error al enviar el mensaje';
			echoRespnse(201, $response);
		}
	} else {
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
$app->get('/estudiantes/acudiente/:usuario_id/:db_name', 'authenticate', function ($usuario_id, $db_name) {
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
$app->get('/listado/destinatarios/:id_list/:id_alu/:db_name', 'authenticate', function ($id_list, $id_alu, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	// consultar estudiantes asociados al acudiente
	if ($id_list == '-3') {
		$destinatarios = $db->getProfesores($id_alu);
	} else if ($id_list = '-4') {
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
$app->post('/registrar/mensaje/masivo', function () use ($app) {
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

	$file_name = $app->request->post('file_name');


	$db = new DbHandler($db_name);

	//**
	for ($i = 0; $i < $tamano; $i++) {
		$d[$i] = $app->request->post('dest' . $i . '');
	}

	$tipe_msg = count($d) > 1 ? 'm' : 'u'; //si el array de destinatarios es > 1 el tipo es 'm'(masivo) sino es 'u'(unico)

	$cad = $tipo == 'p' ? 'Profesor(es)' : 'Administrativo(s)';
	$res_msg = 'Mensaje enviado a ' . count($d) . ' ' . $cad;
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
	$mensaje = $db->crearWebMsg($id_rem, $asu_msg, $txt_msg, $fec_msg, $hor_msg, $ip_msg, $est_msg, $pri_msg, $tipe_msg, $res_msg, $bd, $file_name);

	$ok = true;
	//despues de crear el mensaje se hace un ciclo por cada destinatario y se le envia el mensaje
	if ($mensaje != NULL) {
		foreach ($d as $u) {
			$vec = explode('_', $u);
			if (!$db->registraDestinatario($mensaje, $vec[0], $id_alu, 'i', $vec[1])) {
				//$ok=false;
				//break;
			}
		}
	}
	if ($ok) {
		$response["error"] = false;
		$response["message"] = 'El mensaje fué enviado correctamente';
		echoRespnse(201, $response);
	} else {
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
$app->get('/faltas/estudiante/:id_alu/:fecha/:db_name', 'authenticate', function ($id_alu, $fecha, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	// consultar faltas estudiante
	$faltas = $db->getFaltasAlum($id_alu, $fecha);

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
$app->get('/citaciones/estudiante/:id_alu/:fecha/:db_name', 'authenticate', function ($id_alu, $fecha, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	// consultar citaciones estudiante
	$citaciones = $db->getCitacionesAlum($id_alu, $fecha);

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
$app->get('/salidas/estudiante/:id_alu/:fecha/:db_name', 'authenticate', function ($id_alu, $fecha, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	// consultar salidas programadas estudiante
	$salidas = $db->getSalidasAlum($id_alu, $fecha);

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
$app->get('/estado/evaluativo/:id_alu/:id_peri/:db_name', 'authenticate', function ($id_alu, $id_peri, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar el cod_cur del estudiante
	$cod_cur = $db->getCodCur($id_alu);

	//materias q ve el alumno
	$materias = $db->getMateriasAlum($id_alu);

	//consultar evaluaciones del periodo
	$evaluaciones = $db->getEvaluAlumPer($id_alu, $cod_cur, $id_peri);

	//while($m = $materias->fetch_assoc())  {$mats[] = $m['des_mat'];}
	$instancias = $db->getInstInstitucionales();

	while ($e = $evaluaciones->fetch_assoc()) {
		$data[] = $e;
	}
	//var_dump($id_alu);

	$response["error"] = false;
	$insins = json_decode($instancias['val_var'], true);
	/*foreach($insins as $cod_ins=>$iI){
		if($iI['estado']=='a'){
			$datos .= $iI['codigo'] . "->" . $iI['titulo'] ."\n";
		}
	}*/

	//$response['isntancias'] = $datos;

	if ($materias != NULL && $evaluaciones != NULL) {
		$response["notas"] = array();
		while ($materia = $materias->fetch_assoc()) {

			$cod_mat = $materia['cod_mat'];
			//$response[$materia['des_mat']] = array();

			$tmp = array();
			$tmp["materia"] = $materia['des_mat'];
			$tmp["profesor"] = $materia['nom_per'] . " " . $materia['ape_per'];
			for ($i = 0; $i <= sizeof($data); $i++) {
				if ($data[$i]['cod_mat'] == $cod_mat) {

					foreach ($insins as $cod_ins => $iI) {
						if ($iI['estado'] == 'a') {
							if ($data[$i]['cod_inst'] == $iI['codigo'])
								$tmp['evals'] .= $iI['titulo'] . "Ç";
						}
					}

					$tmp["evals"] .= $data[$i]['des_ins'] . "Ç";
					$tmp["evaluacion"] .= $data[$i]["nota"] . "Ç";
					if ($data[$i]['fec_cre'] != "")
						$tmp["fecha"] .= $data[$i]['fec_cre'] . "Ç";
					else if ($data[$i]['fec_mod'] != "")
						$tmp["fecha"] .= $data[$i]['fec_mod'] . "Ç";
					else
						$tmp["fecha"] .= "No registraÇ";
				}/*else{
					$tmp["evaluacion"] .= "No hay informacion,";
					$tmp["fecha"] .= "No registra,";
				}*/
			}
			if (!isset($tmp["evaluacion"])) {
				$tmp["evals"] = "No hay registrosÇ";
				$tmp["evaluacion"] = "No hay notasÇ";
				$tmp["fecha"] = "No registraÇ";
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
$app->get('/estado/general/:id_alu/:id_peri/:db_name', 'authenticate', function ($id_alu, $id_peri, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar el cod_cur del estudiante
	$cod_cur = $db->getCodCur($id_alu);

	//consultar definitivas del periodo
	$definitivas = $db->getDefMatPer($id_alu, $cod_cur, $id_peri);

	$response["error"] = false;
	$response["periodo"] = array();

	if ($definitivas != NULL) {
		while ($definitiva = $definitivas->fetch_assoc()) {
			$tmp = array();
			$tmp["des_mat"] = $definitiva["des_mat"];
			$tmp['nota_d'] = $definitiva['nota_d'];
			$n = $db->getValoracion($definitiva['nota_d']);
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
$app->get('/estado/pormateria/:id_alu/:db_name', 'authenticate', function ($id_alu, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar el cod_cur del estudiante
	$mats = $db->getMatsAlu2($id_alu);
	$peris = $db->getPeriodosDef($id_alu);
	$dat = $db->getNotasPeri($id_alu);

	$response["error"] = false;

	if ($mats != NULL && $dat != NULL) {
		if (sizeof($peris) > 0) {
			$response["notas"] = array();
			foreach ($mats as $m) {
				$tmp = array();
				$tmp["materia"] = $m['des_mat'];
				$tmp["profesor"] = $m['profe'];
				foreach ($peris as $p) {
					$tmp['definitiva'] .= $dat[$m['cod_mat']][$p['id_peri']]['nota_d'] . "Ç";
					$n = $db->getValoracion($dat[$m['cod_mat']][$p['id_peri']]['nota_d']);
					$tmp['valoracion'] .= $n['let_val'] . "Ç";
				}
				array_push($response["notas"], $tmp);
			}
			echoRespnse(201, $response);
		} else {
			$response["error"] = true;
			$response["message"] = "No hay periodos cerrados para consultar las notas";
			echoRespnse(200, $response);
		}
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible sobre las notas";
		echoRespnse(200, $response);
	}
});





/**
 * periodos cerrados o el actual
 * url - /periodos
 * method - get
 * params 
 */
$app->get('/periodos/:id_alu/:db_name', 'authenticate', function ($id_alu, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar el cod_cur del estudiante
	$periodos = $db->getPeriodos();

	$response["error"] = false;

	if ($periodos != NULL) {
		if ($periodos->num_rows > 0) {
			$response["periodos"] = array();
			while ($periodo = $periodos->fetch_assoc()) {
				$tmp = array();
				$tmp["id"] = $periodo["id_peri"];
				$tmp['label'] = $periodo['des_per'];
				array_push($response["periodos"], $tmp);
			}
			echoRespnse(201, $response);
		} else {
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
$app->get('/colegios', function () {
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
			$pos = strpos($mystring, $findme);
			$pos2 = strpos($mystring, $findme2);
			$pos3 = strpos($mystring, $findme3);
			if ($pos === false && $pos2 === false && $pos3 === false) {
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
$app->get('/periodos/boletin/:db_name', 'authenticate', function ($db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar listado periodos disponibles para boletin informativo
	$periodos = $db->getPeriodosBoletin();

	$response["error"] = false;

	if ($periodos != NULL) {
		if ($periodos->num_rows > 0) {
			$response["periodos"] = array();
			while ($periodo = $periodos->fetch_assoc()) {
				$tmp = array();
				$tmp["id"] = $periodo["id_peri"];
				$tmp['label'] = $periodo['des_per'];
				array_push($response["periodos"], $tmp);
			}
			echoRespnse(201, $response);
		} else {
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
$app->get('/datos/boletin/:id_alu/:db_name', 'authenticate', function ($id_alu, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos de la seccion
	$cur = $db->getSeccion($id_alu);
	$gra = $db->getGrado($cur['cod_gra']);

	if ($cur != NULL && $gra != NULL) {
		$response["error"] = false;
		$response["cod_niv"] = str_pad($gra["cod_niv"], 3, 0, 0);
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








/*------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/
/*---------------------------------RUTAS PARA API DESDE EL PERFIL PROFESOR------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------------------*/

/**
 * Responsabilidad academica del profesor
 * url - /responsablidad/academica/id_per
 * method - get
 * params
 */
$app->get('/responsabilidad/academica/:id_per/:db_name', 'authenticate', function ($id_per, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$asignaturas = $db->cargarAsignaturas($id_per);

	if ($asignaturas != NULL) {
		if ($asignaturas->num_rows > 0) {
			$response['error'] = false;
			$response['asignaturas'] = array();
			while ($asignatura = $asignaturas->fetch_assoc()) {
				$tmp = array();
				$tmp['id'] = $asignatura['id'];
				$tmp['label'] = $asignatura['label'];
				array_push($response['asignaturas'], $tmp);
			}
			echoRespnse(201, $response);
		} else {
			$response['error'] = true;
			$response['message'] = "No tiene asignaturas asignadas";
			echoRespnse(200, $response);
		}
	} else {
		$response['error'] = true;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * Periodos activos para subir notas del profesor
 * url - /periodos/notas/profesor/id_per
 * method - get
 * params
 */
$app->get('/periodos/notas/profesor/:id_per/:cod_cur/:cod_mat/:db_name', 'authenticate', function ($id_per, $cod_cur, $cod_mat, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$periodo = $db->cargarPeriodos($id_per, $cod_cur, $cod_mat);

	if ($periodo != NULL) {
		$response['error'] = false;
		$response['id_peri'] = $periodo['id_peri'];
		$response['des_per'] = $periodo['des_per'];
		$response['fec_ini'] = $periodo['fec_ini'];
		$response['fec_fin'] = $periodo['fec_cor_pro'];
		echoRespnse(201, $response);
	} else {
		$response['error'] = true;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * Estudiantes de la materia seleecionada para subir notas del profesor
 * url - /estudiantes/materia/profesor
 * method - get
 * params
 */
$app->get('/estudiantes/materia/profesor/:id_per/:cod_niv/:cod_cur/:cod_mat/:id_peri/:cod_gra/:db_name', 'authenticate', function ($id_per, $cod_niv, $cod_cur, $cod_mat, $id_peri, $cod_gra, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$esp = $db->getEsp_mat($cod_cur, $cod_mat);
	$estudiantes = $db->cargarEstudiantes($id_per, $cod_niv, $cod_cur, $cod_mat, $esp, $id_peri, $cod_gra);

	if ($estudiantes != NULL) {
		if ($estudiantes->num_rows > 0) {
			$response['error'] = false;
			$response['estudiantes'] = array();
			while ($estudiante = $estudiantes->fetch_assoc()) {
				$tmp = array();
				$tmp['nom'] = $estudiante['nom'];
				$tmp['nom_per'] = $estudiante['nom_per'];
				$tmp['ape_per'] = $estudiante['ape_per'];
				$tmp['id_alu'] = $estudiante['id_alu'];
				$tmp['ufo_alu'] = $estudiante['ufo_alu'];
				$tmp['est_alu'] = $estudiante['est_alu'];
				array_push($response['estudiantes'], $tmp);
			}
			echoRespnse(201, $response);
		} else {
			$response['error'] = true;
			$response['message'] = "No hay estudiantes en el curso seleccionado";
			echoRespnse(200, $response);
		}
	} else {
		$response['error'] = true;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * instancias de la materia seleecionada para subir notas del profesor
 * url - /instancias/materia/profesor
 * method - get
 * params
 */
$app->get('/instancias/materia/profesor/:id_per/:id_peri/:cod_cur/:cod_mat/:db_name', 'authenticate', function ($id_per, $id_peri, $cod_cur, $cod_mat, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$instancias = $db->cargarInstancias($id_per, $id_peri, $cod_cur, $cod_mat);
	$instancias2 = $db->getInstInstitucionales();

	$insins = json_decode($instancias2['val_var'], true);
	$response['error'] = false;
	$response['instancias'] = array(); //$instancias;

	if ($instancias != NULL) {
		$x = -1;
		//$response['query']=$instancias;		
		$response['rows'] = count($instancias[$cod_cur][$cod_mat]);
		foreach ($instancias[$cod_cur][$cod_mat] as $cod_insI => $fila) {
			$x++;
			if ($fila['cod_ins'] != '-1') {
				$txtIns[$x] = $fila['des_ins'] . " (" . $fila['des_tip_ins'] . ')'; //.$fila['fec_rea']." : ".$fila['des_sup']." )";
				$tmp = array();
				$tmp['cod_ins'] = $fila['cod_ins'];
				$tmp['des_ins'] = $txtIns[$x];
				$tmp['cod_tip_ins'] = $fila['cod_tip_ins'];
				array_push($response['instancias'], $tmp);
			}
		}
	}
	if ($insins != NULL) {
		foreach ($insins as $cod_ins => $iI) {
			if ($iI['estado'] == 'a' /*&& $iI['codigo'] != 0*/ && $iI['codigo'] != -1) {
				$tmp = array();
				$tmp['cod_ins'] = $iI['codigo'];
				$tmp['des_ins'] = "Institucional (" . $iI['titulo'] . ")";
				$tmp['cod_tip_ins'] = $iI['cod_tip_ins'];
				array_push($response['instancias'], $tmp);
			}
		}
	}
	echoRespnse(201, $response);
	if ($instancias == NULL && $insins == NULL) {
		$response['error'] = true;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}


	/*if ($instancias != NULL) {
		$x=-1;
		$response['error']=false;
		//$response['query']=$instancias;
		$response['instancias']=array();//$instancias;
		$response['rows']=count($instancias[$cod_cur][$cod_mat]);
		foreach($instancias[$cod_cur][$cod_mat] as $cod_insI=>$fila){ $x++;
			if($fila['cod_ins']!='-1'){
				$txtIns[$x] = $fila['des_ins']." (".$fila['des_tip_ins'].')';//.$fila['fec_rea']." : ".$fila['des_sup']." )";
				$tmp = array();
				$tmp['cod_ins'] = $fila['cod_ins'];
				$tmp['des_ins'] = $txtIns[$x];
				$tmp['cod_tip_ins'] = $fila['cod_tip_ins'];
				array_push($response['instancias'], $tmp);
			}
		}
		echoRespnse(201, $response);
	} else {
		$response['error'] = true;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}*/
});


/**
 * notas registradas de la materia seleecionada por el profesor
 * url - /notas/materia/registradas
 * method - get
 * params
 */
$app->get('/notas/materia/registradas/:cod_cur/:cod_mat/:id_peri/:id_alu/:db_name', 'authenticate', function ($cod_cur, $cod_mat, $id_peri, $id_alu, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$notas_alu = $db->getNotaSG($cod_cur, $cod_mat, $id_peri, $id_alu);

	if ($notas_alu != NULL) {
		$response['error'] = false;
		$response['notas'] = array();
		//$response['rows']=count($notas_alu);
		while ($nota = $notas_alu->fetch_assoc()) {
			$tmp = array();
			$tmp['cod_ins'] = $nota['cod_ins'];
			$tmp['nota'] = $nota['nota'];
			array_push($response['notas'], $tmp);
		}
		echoRespnse(201, $response);
	} else {
		$response['error'] = true;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * variables para nota maxima y minima
 * url - /nota/maxmin
 * method - get
 * params
 */
$app->get('/nota/maxmin/:db_name', 'authenticate', function ($db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$tipo_eval = $db->getTipoEvaluacion();
	$tipvalora = $db->getTipValora();
	$notas = $db->getNotaMAXMIN();

	if ($tipo_eval != NULL) {
		if ($tipo_eval['val_var'] == 'cualitativa') {
			if ($tipvalora != NULL) {
				$response['error'] = false;
				$response['tipo_evaluacion'] = $tipo_eval['val_var'];
				$response['tipvalora'] = array();
				while ($tip = $tipvalora->fetch_assoc()) {
					$tmp = array();
					$tmp['max'] = $tip['max'];
					$tmp['min'] = $tip['min'];
					$tmp['id_val'] = $tip['id_val'];
					$tmp['des_val'] = $tip['des_val'];
					$tmp['let_val'] = $tip['let_val'];
					array_push($response['tipvalora'], $tmp);
				}
				echoRespnse(201, $response);
			} else {
				$response['error'] = true;
				$response['message'] = "No hay informacion disponible";
				echoRespnse(200, $response);
			}
		} else if ($tipo_eval['val_var'] == 'cuantitativa') {
			if ($notas != NULL) {
				$response['error'] = false;
				$response['tipo_evaluacion'] = $tipo_eval['val_var'];
				$response['varnotas'] = array();
				//$response['rows']=count($notas_alu);
				while ($nota = $notas->fetch_assoc()) {
					$tmp = array();
					$tmp['nom_var'] = $nota['nom_var'];
					$tmp['val_var'] = $nota['val_var'];
					array_push($response['varnotas'], $tmp);
				}
				echoRespnse(201, $response);
			} else {
				$response['error'] = true;
				$response['message'] = "No hay informacion disponible";
				echoRespnse(200, $response);
			}
		} else {
			$response['error'] = true;
			$response['message'] = "No esta definido el tipo de evaluacion";
			echoRespnse(200, $response);
		}
	}
});


/**
 * actualizar notas del estudiante enviado
 * url - /actualizar/notas/profesor
 * method - post
 * params 4
 */
$app->post('/actualizar/notas/profesor', function () use ($app) {
	$response = array();

	//recuperar variables por post
	$db_name = $app->request->post('db_name');
	$id_alu = $app->request->post('id_alu');
	$cod_mat = $app->request->post('cod_mat');
	$cod_cur = $app->request->post('cod_cur');
	$id_peri = $app->request->post('id_peri');
	$id_usu = $app->request->post('id_usu');
	$tamano = $app->request->post('tam');

	$db = new DbHandler($db_name);

	for ($i = 0; $i < $tamano; $i++) {
		$d[$i] = $app->request->post('notap' . $i . '');
	}

	$ban = true;
	$co = 0;
	foreach ($d as $u) {
		$vec = explode('_', $u);
		$proceso = $db->actualizarNota($id_alu, $cod_mat, $cod_cur, $id_peri, $id_usu, $vec[1], $vec[0]);
		if (!$proceso) {
			$ban = false;
			break;
		}
		$co++;
	}
	if ($ban) {
		$response['error'] = false;
		$response['message'] = "Notas actualizadas correctamente";
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = 'Error al actualizar las notas';
		$response['contador'] = $co;
		echoRespnse(200, $response);
	}
});


/**
 * actualizar notas del estudiante enviado
 * url - /actualizar/notas/profesor
 * method - post
 * params 4
 */
$app->post('/actualizar/nota/individual/estudiante', function () use ($app) {
	$response = array();

	//recuperar variables por post
	$db_name = $app->request->post('db_name');
	$cod_mat = $app->request->post('cod_mat');
	$cod_cur = $app->request->post('cod_cur');
	$id_peri = $app->request->post('id_peri');
	$id_usu = $app->request->post('id_usu');
	$tamano = $app->request->post('tam');

	$db = new DbHandler($db_name);

	for ($i = 0; $i < $tamano; $i++) {
		$d[$i] = $app->request->post('notae' . $i . '');
	}

	$ban = true;
	foreach ($d as $u) {
		$vec = explode('_', $u);
		$proceso = $db->actualizarNota($vec[0], $cod_mat, $cod_cur, $id_peri, $id_usu, $vec[1], $vec[2]);
		if (!$proceso) {
			$ban = false;
			break;
		}
	}

	if ($ban) {
		$response['error'] = false;
		$response['message'] = "Notas actualizadas correctamente";
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = 'Error al actualizar las notas';
		echoRespnse(200, $response);
	}
});


/**
 * consulta si hay nota registrada en la instancia y estudiante enviado
 * url - /nota/instancia/estudiante
 * method - get
 * params
 */
$app->get('/nota/instancia/estudiante/:cod_cur/:cod_mat/:id_peri/:id_alu/:cod_inst/:db_name', 'authenticate', function ($cod_cur, $cod_mat, $id_peri, $id_alu, $cod_inst, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$nota_alu = $db->getNotaInstSG($cod_cur, $cod_mat, $id_peri, $id_alu, $cod_inst);

	if ($nota_alu != NULL) {
		$nota = $nota_alu->fetch_assoc();
		$response['error'] = false;
		$response['cod_inst'] = $nota['cod_ins'];
		$response['nota'] = $nota['nota'];
		echoRespnse(201, $response);
	} else {
		$response['error'] = true;
		$response['codigo'] = 0;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});




//---------------------- consultas para observador estudiantil---------------------------------------------------------------------------------


/**
 * consulta las observaciones registradas para el estudiante en cuestion
 * url - /observador/registradas/id_alu/cod_cur/cod_mat
 * method - get
 * params
 */
$app->get('/observador/registradas/:id_alu/:cod_cur/:cod_mat/:db_name', 'authenticate', function ($id_alu, $cod_cur, $cod_mat, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$observaciones = $db->getObservaciones($id_alu, $cod_cur, $cod_mat);
	$datos = $db->getCursoMateria($cod_cur, $cod_mat);

	if ($observaciones != NULL) {
		$response['error'] = false;
		$response['curso'] = $datos['des_cur'];
		$response['materia'] = $datos['des_mat'];
		$response['observaciones'] = array();
		//$response['rows']=count($notas_alu);
		while ($obs = $observaciones->fetch_assoc()) {
			$tmp = array();
			$tmp['id_alu'] = $obs['id_alu'];
			$tmp['noti_obe'] = $obs['noti_obe'];
			$tmp['id_obe'] = $obs['id_obe'];
			$tmp['des_cor_obe'] = $obs['des_cor_obe'];
			$tmp['des_obe'] = $obs['des_obe'];
			$tmp['id_peri'] = $obs['id_peri'];
			$tmp['fec_ini_obe'] = $obs['fec_ini_obe'];
			$tmp['id_per'] = $obs['id_per'];
			$tmp['des_toe'] = $obs['des_toe'];
			$tmp['des_per'] = $obs['des_per'];
			$tmp['fec_ing_obe'] = $obs['fec_ing_obe'];
			$tmp['cod_mat'] = $obs['cod_mat'];
			$tmp['des_mat_cor'] = $obs['des_mat_cor'];
			$tmp['ape_per'] = $obs['ape_per'];
			$tmp['nom_per'] = $obs['nom_per'];
			array_push($response['observaciones'], $tmp);
		}
		echoRespnse(201, $response);
	} else {
		$response['error'] = true;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * consulta los tipos de observaciones
 * url - /tipo/observacion/estudiante
 * method - get
 * params
 */
$app->get('/tipo/observacion/estudiante/:db_name', 'authenticate', function ($db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$tipos = $db->getTiposObs();

	if ($tipos != NULL) {
		$response['error'] = false;
		$response['tipos'] = array();
		while ($tipo = $tipos->fetch_assoc()) {
			$tmp['cod_toe'] = $tipo['cod_toe'];
			$tmp['des_toe'] = $tipo['des_toe'];
			$tmp['boletin'] = $tipo['boletin'];
			$tmp['faltas'] = $tipo['faltas'];
			$tmp['categoria'] = $tipo['categoria'];
			array_push($response['tipos'], $tmp);
		}
		echoRespnse(201, $response);
	} else {
		$response['error'] = true;
		$response['codigo'] = 0;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * guardar observacion estudiante
 * url - /registrar/observacion/estudiante
 * method - post
 * params
 */
$app->post('/registrar/observacion/estudiante', function () use ($app) {
	$response = array();

	//recuperar variables por post
	$db_name = $app->request->post('db_name');
	$cod_toe = $app->request->post('cod_toe');
	$des_obe = $app->request->post('des_obe');
	$fec_ing_obe = $app->request->post('fec_ing_obe');
	$id_alu = $app->request->post('id_alu');
	$cod_cur = $app->request->post('cod_cur');
	$cod_mat = $app->request->post('cod_mat');
	$id_peri = $app->request->post('id_peri');
	$cla_obe = $app->request->post('cla_obe');
	$id_per = $app->request->post('id_per');

	$db = new DbHandler($db_name);

	$proceso = $db->registrarObservacion($cod_toe, $des_obe, $fec_ing_obe, $id_alu, $cod_cur, $cod_mat, $id_peri, $cla_obe, $id_per);

	if ($proceso) {
		$response['error'] = false;
		$response['message'] = "Observacion registrada correctamente";
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = 'Error al registrar la observacion';
		echoRespnse(200, $response);
	}
});




//------------------------------------------------------------CONSULTAS PARA FALTAS DESDE PROFESOR------------------------------------------


/**
 * Estudiantes y faltas de la materia seleccionada para llamar lista
 * url - /estudiantes/faltas/materia/profesor
 * method - get
 * params
 */
$app->get('/estudiantes/faltas/materia/profesor/:id_per/:cod_niv/:cod_cur/:cod_mat/:id_peri/:cod_gra/:fec_fal/:db_name', 'authenticate', function ($id_per, $cod_niv, $cod_cur, $cod_mat, $id_peri, $cod_gra, $fec_fal, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar datos
	$esp = $db->getEsp_mat($cod_cur, $cod_mat);
	$estudiantes = $db->cargarEstudiantes($id_per, $cod_niv, $cod_cur, $cod_mat, $esp, $id_peri, $cod_gra);
	$faltasMat = $db->getFaltasCursoMat($cod_cur, $cod_mat, $fec_fal);
	$faltasBien = $db->getFaltasCursoBien($cod_cur, $cod_mat, $fec_fal);

	$dia = $db->getDiaByFecha($fec_fal);
	$jornadasDia = $db->cargarJornadasDia($id_per, $cod_cur, $cod_mat, $dia['s']);
	$modelo = $db->modeloFaltas();

	if ($estudiantes != NULL) {
		if ($estudiantes->num_rows > 0) {
			$response['error'] = false;
			$response['modelo'] = $modelo['val_var'];
			$response['estudiantes'] = array();
			while ($estudiante = $estudiantes->fetch_assoc()) {
				$tmp = array();
				$tmp['nom'] = $estudiante['nom'];
				$tmp['nom_per'] = $estudiante['nom_per'];
				$tmp['ape_per'] = $estudiante['ape_per'];
				$tmp['id_alu'] = $estudiante['id_alu'];
				$tmp['ufo_alu'] = $estudiante['ufo_alu'];
				$tmp['est_alu'] = $estudiante['est_alu'];
				array_push($response['estudiantes'], $tmp);
			}
			if ($faltasMat != NULL) {
				$response['faltasMat'] = array();
				while ($falta = $faltasMat->fetch_assoc()) {
					$tmp = array();
					$tmp['id_alu'] = $falta['id_alu'];
					$tmp['cod_cur'] = $falta['cod_cur'];
					$tmp['cod_mat'] = $falta['cod_mat'];
					$tmp['fec_fal'] = $falta['fec_fal'];
					$tmp['num_hor'] = $falta['num_hor'];
					$tmp['jus_fal'] = $falta['jus_fal'];
					$tmp['tip_fal'] = $falta['tip_fal'];
					array_push($response['faltasMat'], $tmp);
				}
			}
			if ($faltasBien != NULL) {
				$response['faltasBien'] = array();
				while ($falta1 = $faltasBien->fetch_assoc()) {
					$tmp = array();
					$tmp['id_alu'] = $falta1['id_alu'];
					$tmp['cod_cur'] = $falta1['cod_cur'];
					$tmp['id_jhor'] = $falta1['id_jhor'];
					$tmp['fec_fal'] = $falta1['fec_fal'];
					$tmp['tipo_f'] = $falta1['tipo_f'];
					$tmp['just_fal'] = $falta1['just_fal'];
					$tmp['txtj_fal'] = $falta1['txtj_fal'];
					$tmp['id_usu_cre'] = $falta1['id_usu_cre'];
					$tmp['fec_cre'] = $falta1['fec_cre'];
					$tmp['cod_mat'] = $falta1['cod_mat'];
					$tmp['id_falta_tipo'] = $falta1['id_falta_tipo'];
					$tmp['dia'] = $falta1['dia'];
					array_push($response['faltasBien'], $tmp);
				}
			}
			if ($jornadasDia != NULL) {
				$response['jors_dia'] = array();
				while ($jor = $jornadasDia->fetch_assoc()) {
					$tmp = array();
					$tmp['id_mgh'] = $jor['id_mgh'];
					$tmp['id_jhor'] = $jor['id_jhor'];
					$tmp['id_jor'] = $jor['id_jor'];
					$tmp['des_jhor'] = $jor['des_jhor'];
					$tmp['hor_ini'] = $jor['hor_ini'];
					$tmp['hor_fin'] = $jor['hor_fin'];
					$tmp['dia'] = $jor['dia'];
					array_push($response['jors_dia'], $tmp);
				}
			}
			echoRespnse(201, $response);
		} else {
			$response['error'] = true;
			$response['message'] = "No hay estudiantes en el curso seleccionado";
			echoRespnse(200, $response);
		}
	} else {
		$response['error'] = true;
		$response['message'] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * registrar falta del estudiante enviado en falta_histio_mat
 * url - /registro/falta/estudiante
 * method - post
 * params 4
 */
$app->post('/registro/falta/estudiante', 'authenticate', function () use ($app) {
	$response = array();

	//recuperar variables por post
	$db_name = $app->request->post('db_name');
	$cod_mat = $app->request->post('cod_mat');
	$cod_cur = $app->request->post('cod_cur');
	$fec_fal = $app->request->post('fec_fal');
	$tamano = $app->request->post('tam');

	$db = new DbHandler($db_name);

	$periodo = $db->cargarPeriodos(1, 1, 1);

	for ($i = 0; $i < $tamano; $i++) {
		$d[$i] = $app->request->post('faltat' . $i . '');
	}

	$ban = true;
	$co = 0;
	foreach ($d as $u) {
		$vec = explode('_', $u);
		$proceso = $db->actualizarFaltaEst($vec[0], $cod_cur, $cod_mat, $periodo['fec_cor_tot'], $vec[4], $vec[5], $vec[6]);
		if (!$proceso) {
			$ban = false;
			break;
		}
		$co++;
	}

	if ($ban) {
		$response['error'] = false;
		$response['message'] = "Faltas actualizadas correctamente";
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = 'Error al actualizar las faltas';
		echoRespnse(200, $response);
	}
});


/**
 * actividades que tiene programadas el profesor en el curso seleccionado
 * url - /actividades/profesores/:id_per/cod_cur
 * method - get
 * params id_per
 */
$app->get('/actividades/profesor/:id_per/:db_name', 'authenticate', function ($id_per, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar actividades acudiente
	$periodo = $db->cargarPeriodos(1, 1, 1);
	$actividades = $db->getActividadesProfesor($id_per, $periodo['fec_ini'], $periodo['fec_cor_tot']);

	if ($actividades != NULL) {
		$response['error'] = false;
		$response['fec_ini'] = $periodo['fec_ini'];
		$response['fec_fin'] = $periodo['fec_cor_tot'];
		$response['actividades'] = array();
		while ($actividad = $actividades->fetch_assoc()) {
			$tmp = array();
			$tmp["fec_act"] = $actividad["fec_act"];
			$tmp["hor_act"] = $actividad["hor_act"];
			$tmp["des_tact"] = $actividad["des_tact"];
			$tmp["cod_mat"] = $actividad["cod_mat"];
			$tmp["des_mat"] = $actividad["des_mat"];
			$tmp["des_cur"] = $actividad["des_cur"];
			$tmp["cod_cur"] = $actividad["cod_cur"];
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
 * grados asignados al profesor
 * url - /grados/profesor/:id_per
 * method - get
 * params id_per
 */
$app->get('/grados/profesor/:id_per/:usuario_id/:db_name', 'authenticate', function ($id_per, $usuario_id, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar
	$grados = $db->cargarGrados($id_per);
	$funcionarios = $db->cargarFuncionariosProf($usuario_id);

	if ($grados != NULL) {
		$response['error'] = false;
		$response['grados'] = array();
		while ($grado = $grados->fetch_assoc()) {
			$tmp = array();
			$tmp["id"] = $grado["id"];
			$tmp["label"] = $grado["label"];
			$tmp["ord_gra"] = $grado["ord_gra"];
			array_push($response["grados"], $tmp);
		}

		if ($funcionarios != NULL) {
			$response['funcionarios'] = array();
			while ($func = $funcionarios->fetch_assoc()) {
				$tmp = array();
				$tmp["id"] = $func["id_usuario"];
				$tmp["label"] = $func["ape_per"] . " " . $func["nom_per"];
				$tmp["id_usuario"] = $func["id_usuario"];
				$tmp["tipo_usuario"] = $func["tipo_usuario"];
				$tmp["nom_per"] = $func["nom_per"];
				$tmp["ape_per"] = $func["ape_per"];
				$tmp["cargo"] = $func["cargo"];
				$tmp["mats"] = $func["mats"];
				$tmp["grad"] = $func["grad"];
				$tmp["id_alu"] = $func["id_alu"];
				array_push($response["funcionarios"], $tmp);
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
 * secciones asignados al profesor
 * url - /secciones/profesor/:cod_gra/:id_per
 * method - get
 * params id_per
 */
$app->get('/secciones/profesor/:cod_gra/:id_per/:db_name', 'authenticate', function ($cod_gra, $id_per, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar
	$secciones = $db->cargarSecciones($cod_gra, $id_per);

	if ($secciones != NULL) {
		$response['error'] = false;
		$response['secciones'] = array();
		while ($secc = $secciones->fetch_assoc()) {
			$tmp = array();
			$tmp["id"] = $secc["id"];
			$tmp["label"] = $secc["label"];
			array_push($response["secciones"], $tmp);
		}
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * estudaintes de la seccion seleccionada para enviar mensajes del profesor
 * url - /cargar/estudiantes/profesor/:cod_cur/:db_name
 * method - get
 * params id_per
 */
$app->get('/cargar/estudiantes/profesor/:cod_cur/:db_name', 'authenticate', function ($cod_cur, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar
	$estudiantes = $db->cargarEstudiantesProfesor($cod_cur);

	if ($estudiantes != NULL) {
		$response['error'] = false;
		$response['estudiantes'] = array();
		$response['padres'] = array();

		while ($est = $estudiantes->fetch_assoc()) {
			$tmp = array();
			$tmp["nom"] = $est["nom"];
			$tmp["id_alu"] = $est["id_alu"];
			$tmp["nom_per"] = $est["nom_per"];
			$tmp["ape_per"] = $est["ape_per"];
			array_push($response["estudiantes"], $tmp);

			$padre = $db->getIdUsuPadre($est["id_alu"]);
			if ($padre->num_rows > 0) {
				$acu = $padre->fetch_assoc();
				$tmp = array();
				$tmp["id_usu"] = $acu["id_usu"];
				$tmp["tipo_usuario"] = "w";
				$tmp["nom_per"] = $acu["nom_per"];
				$tmp["ape_per"] = $acu["ape_per"];
				$tmp["id_alu"] = $est["id_alu"];
				array_push($response["padres"], $tmp);
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
 * registrar mensaje masivo
 * url - /registrar/mensaje/
 * method - post
 * params 4
 */
$app->post('/registrar/mensaje/masivo/profesor', function () use ($app) {
	$response = array();

	$db_name = $app->request->post('db_name');
	//recuperar variables por post
	$id_rem = $app->request->post('id_rem');
	$asu_msg = $app->request->post('asu_msg');
	$txt_msg = $app->request->post('txt_msg');
	$bd = $app->request->post('bd');
	//array de destinatarios **
	$tamano = $app->request->post('tam');

	$db = new DbHandler($db_name);

	//**
	for ($i = 0; $i < $tamano; $i++) {
		$d[$i] = $app->request->post('dest' . $i . '');
	}

	$tipe_msg = count($d) > 1 ? 'm' : 'u'; //si el array de destinatarios es > 1 el tipo es 'm'(masivo) sino es 'u'(unico)

	//$cad=$tipo=='p'?'Profesor(es)':'Administrativo(s)';
	//$res_msg='Mensaje enviado a '.count($d).' '.$cad;
	$fec_msg = date('Y-m-d');
	$hor_msg = date('H:i:s');
	$ip_msg = $_SERVER['REMOTE_ADDR'];
	$est_msg = 'i';
	$pri_msg = 'n';

	$cp = 0;
	$ce = 0;
	$cf = 0;

	/*$response["error"] = false;
	$response["id_rem"] = $id_rem;
	$response["asu_msg"] = $asu_msg;
	$response["txt_msg"] = $txt_msg;
	$response["bd"] = $bd;
	$response["tam"] = $tamano;
	$response["tipe_msg"] = $tipe_msg;
	$response["res_msg"] = $res_msg;
	$response['destinatarios'] = array();
	$response['db_name'] = $db_name;*/
	foreach ($d as $u) {
		$vec = explode('_', $u);
		if ($vec[2] == "w") $cp++;
		else if ($vec[2] == "s") $cf++;
		else if ($vec[2] == "e") $ce++;
		/*$tmp = array();
		$tmp["id_des"] = $vec[0];
		$tmp["id_alu"] = $vec[1];
		$tmp["tip_des"] = $vec[2];
		array_push($response["destinatarios"], $tmp);*/
	}

	$res_msg = "Mensaje enviado a " . $ce . " estudiante(s), " . $cp . " acudiente(s), " . $cf . " funcionario(s)";

	//echoRespnse(201, $response);

	// se crea el mensaje en web_msg q solo es una vez asi hayan n destinatarios
	$mensaje = $db->crearWebMsg($id_rem, $asu_msg, $txt_msg, $fec_msg, $hor_msg, $ip_msg, $est_msg, $pri_msg, $tipe_msg, $res_msg, $bd);

	$ok = true;
	//despues de crear el mensaje se hace un ciclo por cada destinatario y se le envia el mensaje
	if ($mensaje != NULL) {
		foreach ($d as $u) {
			$vec = explode('_', $u);
			if (!$db->registraDestinatario($mensaje, $vec[0], $vec[1], 'i', $vec[2])) {
				//$ok=false;
				//break;
			}
		}
	}
	if ($ok) {
		$response["error"] = false;
		$response["message"] = 'El mensaje fué enviado correctamente';
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = 'Error al enviar el mensaje';
		echoRespnse(201, $response);
	}
});


/**
 * recuperar destinatarios mensaje enviado desde profesor
 * url - /recuperar/destinatarios/mensaje
 * method - get
 * params id_msg
 */
$app->get('/recuperar/destinatarios/mensaje/profesor/:id_msg/:db_name', 'authenticate', function ($id_msg, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	// lisatdo a recuperar
	$funcionarios = $db->getDestinatariosMSG($id_msg);
	$estudiantes = $db->getDestinatariosEstMSG($id_msg);
	$acudientes = $db->getDestinatariosAcuMSG($id_msg);

	$response['error'] = false;
	$response['listado'] = array();

	if ($funcionarios != NULL) {
		while ($list = $funcionarios->fetch_assoc()) {
			$tmp = array();
			$tmp["destinatario"] = $list["nom_per"] . " " . $list["ape_per"] . " - " . $list["des_tfun"];
			array_push($response["listado"], $tmp);
		}
	}
	if ($estudiantes != NULL) {
		while ($list = $estudiantes->fetch_assoc()) {
			$tmp = array();
			$tmp["destinatario"] = $list["nom_per"] . " " . $list["ape_per"] . " - " . $list["des_cur_cor"];
			array_push($response["listado"], $tmp);
		}
	}

	if ($acudientes != NULL) {
		while ($list = $acudientes->fetch_assoc()) {
			$tmp = array();
			$tmp["destinatario"] = $list["nom_per"] . " " . $list["ape_per"] . " - Acudiente";
			array_push($response["listado"], $tmp);
		}
	}

	echoRespnse(201, $response);
	if ($estudiantes == NULL && $funcionarios == NULL && $acudientes == NULL) {
		$response["error"] = true;
		$response["message"] = "No se encuentra listado de destinatarios";
		echoRespnse(200, $response);
	}
});



/**
 * responder mensaje recibido profesor
 * url - /responder/mensaje/
 * method - post
 * params 4
 */
$app->post('/responder/mensaje/profesor', 'authenticate', function () use ($app) {
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
	$tipe_msg = 'u'; //verificar q variable espacios
	$cad = $app->request->post('tipo');
	$tip_des = $app->request->post('tipo');

	$cad = $cad == 'w' ? 'Acudiente' : $cad == 'e' ? 'Estudiante' : 'Funcionario';
	$res_msg = 'Mensaje enviado a 1 ' . $cad;
	$fec_msg = date('Y-m-d');
	$hor_msg = date('H:i:s');
	$ip_msg = $_SERVER['REMOTE_ADDR'];
	$est_msg = 'i';
	$pri_msg = 'n';

	// mensaje a REGISTRAR
	$mensaje = $db->crearWebMsg($id_rem, $asu_msg, $txt_msg, $fec_msg, $hor_msg, $ip_msg, $est_msg, $pri_msg, $tipe_msg, $res_msg, $bd);

	if ($id_des != '') {
		$obj = $db->registraDestinatario($mensaje, $id_des, $id_alu, 'i', $tip_des);
		if ($mensaje != '' && $obj) {
			$response["error"] = false;
			$response["message"] = 'El mensaje fué enviado correctamente';
			echoRespnse(201, $response);
		} else {
			$response["error"] = false;
			$response["message"] = 'Error al enviar el mensaje';
			echoRespnse(201, $response);
		}
	} else {
		$response["error"] = false;
		$response["datos"] = "El Destinatario esta vacio";
		echoRespnse(201, $response);
	}
});

/**
 * notas del curso registradas cuando va a subir notas por instancia
 * url - /secciones/profesor/:cod_gra/:id_per
 * method - get
 * params id_per
 */
$app->get('/notas/curso/profesor/:cod_cur/:cod_mat/:id_peri/:cod_ins/:db_name', 'authenticate', function ($cod_cur, $cod_mat, $id_peri, $cod_ins, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar
	$notas = $db->getNotasCurso($cod_cur, $cod_mat, $id_peri, $cod_ins);

	if ($notas != NULL) {
		$response['error'] = false;
		$response['notas'] = array();
		while ($secc = $notas->fetch_assoc()) {
			$tmp = array();
			$tmp["id_alu"] = $secc["id_alu"];
			$tmp["cod_ins"] = $secc["cod_inst"];
			$tmp["nota"] = $secc["nota"];
			array_push($response["notas"], $tmp);
		}
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No hay informacion disponible";
		echoRespnse(200, $response);
	}
});


/**
 * cambia el estado a leido del mensaje
 * url - /cambia/estado/mensaje
 * method - get
 * params id_per
 */
$app->get('/cambia/estado/mensaje/:id_msg/:id_des/:bd/:db_name', 'authenticate', function ($id_msg, $id_des, $bd, $db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar
	$estadomsg = $db->cambiaEstadoMSG($id_msg, $id_des, $bd);

	if ($estadomsg == true) {
		$response['error'] = false;
		$response['message'] = 'Estado actualizado';
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "No cambio estado de mensaje";
		echoRespnse(200, $response);
	}
});


/**
 * registrar falta del estudiante enviado en falta_histio_bien
 * url - /registro/faltaBien/estudiante
 * method - post
 * params 4
 */
$app->post('/registro/faltaBien/estudiante', 'authenticate', function () use ($app) {
	$response = array();

	//recuperar variables por post
	$db_name = $app->request->post('db_name');
	$cod_mat = $app->request->post('cod_mat');
	$cod_cur = $app->request->post('cod_cur');
	$fec_fal = $app->request->post('fec_fal');
	$id_usu_cre = $app->request->post('id_usu_cre');
	$tamano = $app->request->post('tam');

	$db = new DbHandler($db_name);

	for ($i = 0; $i < $tamano; $i++) {
		$d[$i] = $app->request->post('faltab' . $i . '');
	}

	$ban = true;
	$co = 0;
	foreach ($d as $u) {
		$vec = explode('_', $u);
		$proceso = $db->actualizarFaltaBien($vec[0], $cod_cur, $vec[1], $fec_fal, $vec[2], $vec[3], $id_usu_cre, $cod_mat, $vec[4]);
		if (!$proceso) {
			$ban = false;
			break;
		}
		$co++;
	}

	if ($ban) {
		$response['error'] = false;
		$response['message'] = "Faltas actualizadas correctamente";
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = 'Error al actualizar las faltas';
		echoRespnse(200, $response);
	}
});







/**
 * consultar variables para el modelo de faltas de la institucion
 * url - /variables/faltas/
 * method - post
 * params 4
 */
$app->get('/variables/faltas/:db_name', 'authenticate', function ($db_name) {
	$response = array();

	$db = new DbHandler($db_name);

	//consultar
	$modelo = $db->modeloFaltas();
	$totalizadas = $db->faltasTotalizadas();
	$justificadas = $db->faltasJustificadas();

	if ($modelo != NULL) {
		$response['error'] = false;
		$response['modelo'] = strtolower($modelo['val_var']);
		$response['totalizadas'] = (isset($totalizadas['val_var'])) ? strtolower($totalizadas['val_var']) : "";
		$response['justificadas'] = (isset($justificadas['val_var'])) ? strtolower($justificadas['val_var']) : "";
		echoRespnse(201, $response);
	} else {
		$response["error"] = true;
		$response["message"] = "La institucion no tiene definido el modelo para asistencia";
		echoRespnse(200, $response);
	}
});











































$app->get('/pruebas', 'authenticate', function () use ($app) {
	$id_dir = 1878;
	$pla_veh = 'SID009';
	$id_fac = 761259;
	$num_fac = 36987;
	global $id_per;
	global $id_usu;
	$db = new DbHandler();
	//$asig=$db->getUltimaAsignacion(108096144341);
	//$r1=$db->actualizarCilinasignados('v',1037205,108096144341);
	//$r2=$db->crearCilinasignados(1156056,108096144341,'l','a','1.00');
	//$ar=$db->crearAsignacion($pla_veh,date("Y-m-d"),date("H:i:s"),'a','s','','','','c',$id_dir,NULL,NULL,$id_per);
	$fec_ped = date("Y") . "-" . date("m") . "-" . date("d");
	//$hra_ped=date("H").":".date("i").":".date("s");
	//$r3=$db->crearCilindrotraza($fec_tra,108096144341,1,'c',$id_dir,'e');
	//$r4=$db->actualizarCilindro($id_dir,108096144341);
	//$r5=$db->actualizarAsignacion(1156056,'v');
	//$r6=$db->actualizarFactura2(1,$pla_veh,$num_fac,108096144341,1,$id_fac);
	//$r7=$db->actualizarVehiculo(13,12,$pla_veh);
	//$r8=$db->actualizarCliente('s',196322);
	//$r9=$db->crearSeguimiento($id_dir,7221055,196322,$fec_ped,$hra_ped,$id_fac,108096144341,$pla_veh,1156056,33000,'a','','v','m','s',NULL,NULL,NULL,NULL,$id_per,4000);
	$val_pro = 33000 - 4000;
	//$r10=$db->crearSeguimiento($id_dir,7221055,196322,$fec_ped,$hra_ped,$id_fac,108096144341,$pla_veh,1156056,$val_pro,'a','','v','c','s',$id_usu,NULL,NULL,NULL,NULL,$id_per,4000);
	//$response['id_asi']=$ae['id_asi'];
	$res = $db->getDatosBarZonLoc(7, 396);
	//$datos = $db->getDatosBarZonLoc($id_zon,$id_bar);
	$datosCliente = $db->crearDatosCliente(196344, 'Pepito', 'Perez Quiñonez', 1024536419, 0, 192342, 'mz 12 casa 3', 'frente a la tienda', 1, $res['nom_var'], $res['nom_zon'], $res['nom_loc'], 7202020, 121212, $pla_veh, $fec_ped, $fec_ped, 1);
	echoRespnse(201, $datosCliente);
});





/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields)
{
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
		if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) { //strlen verifica el tamaño de la cadena y trim quita los espacios en blanco y tabulaciones de la cadena
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
function validateEmail($email)
{
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
function echoRespnse($status_code, $response)
{
	$app = \Slim\Slim::getInstance();
	// Http response code
	$app->status($status_code);

	// setting response content type to json
	$app->contentType('application/json');

	echo json_encode($response);
}

$app->run();
