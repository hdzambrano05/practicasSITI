<?php 
//print_r('si');exit;
//header("Access-Control-Allow-Origin: Content-Type");
include_once '../include/Config.php';
require_once '../include/DbHandler.php';

/* Puedes utilizar este file para conectar con base de datos incluido en este demo;
* si lo usas debes eliminar el include_once del file Config ya que le mismo está incluido en DBHandler
**/
//require_once '../include/DbHandler.php';

require '../libs/Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();


$app->post('/prueba', function () {
    $data = json_decode(file_get_contents('php://input'),true);
    $response = array();
    $db_name="firmaInterna";
    $db = new DbHandler($db_name);
    $bandera=false;
    if($data['id_user']!=''){
        $id_user=$data['id_user'];
        $res=$db->getUser($id_user);
        while ($f=$res->fetch_assoc()) {
            $arr[]=$f;
        }
    }else{
        $arr="Falta ID usuario";
        $bandera=true;
    }

    
   
    $response['error'] = $bandera;
    $response['data'] = $arr;
    echoResponse(200, $response);
});


$app->get('/prueba2', function () {
    $data = json_decode(file_get_contents('php://input'),true);
    $response = array();
    $db_name="firmaInterna";
    $db = new DbHandler($db_name);
    $bandera=false;
    if($data['id_user']!=''){
        $id_user=$data['id_user'];
        $res=$db->getUser($id_user);
        while ($f=$res->fetch_assoc()) {
            $arr[]=$f;
        }
    }else{
        $arr="Falta ID usuario";
        $bandera=true;
    }

    
   
    $response['error'] = $bandera;
    $response['data'] = $arr;
    echoResponse(200, $response);
});

/*********************** USEFULL FUNCTIONS **************************************/

/**
* Verificando los parametros requeridos en el metodo o endpoint
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
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(201, $response);

        $app->stop();
    }
}

/**
* Mostrando la respuesta en formato json al cliente o navegador
* @param String $status_code Http response code
* @param Int $response Json response
*/
function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json; charset=utf-8');
    echo json_encode($response);
}

/**
* Agregando un leyer intermedio e autenticación para uno o todos los metodos, usar segun necesidad
* Revisa si la consulta contiene un Header "Authorization" para validar
*/
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);

    if (isset($normalizedHeaders['authorization'])) {

    $token = $normalizedHeaders['authorization'];
        if (!($token == API_KEY)) { //API_KEY declarada en Config.php
            $response["error"] = true;
            $response["message"] = "Acceso denegado. Token inválido";
            echoResponse(401, $response);
            $app->stop(); 

        } else {
        //procede utilizar el recurso o metodo del llamado
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Falta token de autorización";
        echoResponse(400, $response);

        $app->stop();
    }
}

$app->run();
?> 