<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once '../include/Config.php';
include_once '../include/ConfigFirma.php';
require_once '../include/DbHandler.php';

require_once '../clases/FirmaDigital.php';
require_once '../clases/PdfFirmado.php';
require_once '../clases/DocxFirmado.php';
require_once '../clases/FirmaRepository.php';
require_once '../clases/QrFirma.php';

require '../libs/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

/* =========================================================
   ENDPOINT: POST /firma/html
   Crea y firma un documento generado desde HTML
========================================================= */
$app->post('/firma/html', 'authenticate', function () use ($app) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            throw new Exception('No se recibieron datos JSON válidos');
        }

        $nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
        $correo = isset($data['correo']) ? trim($data['correo']) : '';
        $ciudad = isset($data['ciudad']) ? trim($data['ciudad']) : '';
        $htmlContenido = isset($data['html_contenido']) ? $data['html_contenido'] : '';
        $fotoFirma = isset($data['foto_firma']) ? $data['foto_firma'] : '';
        $nombreDocumentoOriginal = isset($data['nombre_documento']) ? trim($data['nombre_documento']) : 'documento_generado.html';

        if ($nombre === '' || $correo === '' || $htmlContenido === '') {
            throw new Exception('Los campos nombre, correo y html_contenido son obligatorios');
        }

        $firmaDigital = new FirmaDigital();
        $pdfFirmado = new PdfFirmado();
        $repo = new FirmaRepository();
        $qrFirma = new QrFirma();

        $datosFirma = $firmaDigital->prepararDatosFirma(
            $nombre,
            $correo,
            $ciudad,
            $htmlContenido,
            'html',
            $nombreDocumentoOriginal
        );

        while ($repo->existeToken($datosFirma['token'])) {
            $datosFirma['token'] = $firmaDigital->generarToken(16);
        }

        $rutaImagenFirma = '';

        // PRIORIDAD 1 → base64
        if (!empty($fotoFirma)) {
            $rutaImagenFirma = $pdfFirmado->guardarImagenFirmaTemporal($fotoFirma);
        }

        // PRIORIDAD 2 → ruta del servidor
        elseif (!empty($data['ruta_firma'])) {

            if (!file_exists($data['ruta_firma'])) {
                throw new Exception('La ruta de la firma no existe');
            }

            $contenido = file_get_contents($data['ruta_firma']);
            $mime = mime_content_type($data['ruta_firma']);

            if (!$contenido) {
                throw new Exception('No se pudo leer la imagen de firma');
            }

            $base64 = 'data:' . $mime . ';base64,' . base64_encode($contenido);

            // AQUÍ SIEMPRE la convierte a PNG temporal
            $rutaImagenFirma = $pdfFirmado->guardarImagenFirmaTemporal($base64);
        }

        $rutaQr = $qrFirma->generarQr($datosFirma['token']);

        $resultadoPdf = $pdfFirmado->generarDesdeHtml(
            $htmlContenido,
            $datosFirma,
            $rutaQr,
            $rutaImagenFirma
        );

        $guardar = array(
            'token' => $datosFirma['token'],
            'nombre_firmante' => $datosFirma['nombre_firmante'],
            'correo_firmante' => $datosFirma['correo_firmante'],
            'ciudad' => $datosFirma['ciudad'],
            'tipo_documento' => $datosFirma['tipo_documento'],
            'nombre_documento_original' => $datosFirma['nombre_documento_original'],
            'nombre_documento_firmado' => $resultadoPdf['nombre_archivo'],
            'hash_documento' => $datosFirma['hash_documento'],
            'firma_base64' => $datosFirma['firma_base64'],
            'clave_publica' => $datosFirma['clave_publica'],
            'algoritmo' => $datosFirma['algoritmo'],
            'fecha_firma' => $datosFirma['fecha_firma'],
            'ruta_documento_firmado' => $resultadoPdf['ruta_archivo'],
            'estado' => $datosFirma['estado'],
            'observacion' => 'Documento creado y firmado desde HTML'
        );

        $idFirma = $repo->guardarFirma($guardar);
        $repo->guardarAuditoria($idFirma, 'CREAR_FIRMA_HTML', 'Documento generado desde HTML');

        if ($rutaImagenFirma !== '') {
            $pdfFirmado->eliminarTemporal($rutaImagenFirma);
        }

        $response = array();
        $response['error'] = false;
        $response['message'] = 'Documento firmado correctamente desde HTML';
        $response['data'] = array(
            'id_firma' => $idFirma,
            'token' => $datosFirma['token'],
            'hash' => $datosFirma['hash_documento'],
            'fecha_firma' => $datosFirma['fecha_firma'],
            'nombre_archivo' => $resultadoPdf['nombre_archivo'],
            'url_verificacion' => FIRMA_BASE_URL . '/aut/firma.php/firma/verificar/' . $datosFirma['token'],
            'url_descarga' => FIRMA_BASE_URL . '/aut/firma.php/firma/descargar/' . $datosFirma['token']
        );

        echoResponse(200, $response);
    } catch (Exception $e) {
        $response = array(
            'error' => true,
            'message' => $e->getMessage()
        );
        echoResponse(400, $response);
    }
});

/* =========================================================
   ENDPOINT: POST /firma/pdf
   Recibe un PDF en base64 o por URL y lo firma
========================================================= */
$app->post('/firma/pdf', 'authenticate', function () use ($app) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            throw new Exception('No se recibieron datos JSON válidos');
        }

        $nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
        $correo = isset($data['correo']) ? trim($data['correo']) : '';
        $ciudad = isset($data['ciudad']) ? trim($data['ciudad']) : '';
        $pdfBase64 = isset($data['pdf_base64']) ? trim($data['pdf_base64']) : '';
        $pdfUrl = isset($data['pdf_url']) ? trim($data['pdf_url']) : '';
        $fotoFirma = isset($data['foto_firma']) ? $data['foto_firma'] : '';
        $nombreDocumentoOriginal = isset($data['nombre_documento']) ? trim($data['nombre_documento']) : 'documento_subido.pdf';

        if ($nombre === '' || $correo === '') {
            throw new Exception('Los campos nombre y correo son obligatorios');
        }

        if ($pdfBase64 === '' && $pdfUrl === '') {
            throw new Exception('Debe enviar pdf_base64 o pdf_url');
        }

        $firmaDigital = new FirmaDigital();
        $pdfFirmado = new PdfFirmado();
        $repo = new FirmaRepository();
        $qrFirma = new QrFirma();

        $contenidoPdf = '';

        // PRIORIDAD 1 -> PDF base64
        if ($pdfBase64 !== '') {
            $contenidoPdf = $firmaDigital->decodificarBase64($pdfBase64);
        }
        // PRIORIDAD 2 -> PDF por URL
        else {
            if (!filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
                throw new Exception('La URL del PDF no es válida');
            }

            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'GET',
                    'timeout' => 30,
                    'follow_location' => 1,
                    'ignore_errors' => true,
                    'header' => "User-Agent: ApiFirma/1.0\r\n"
                ),
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false
                )
            ));

            $contenidoPdf = @file_get_contents($pdfUrl, false, $context);

            if ($contenidoPdf === false || empty($contenidoPdf)) {
                throw new Exception('No se pudo descargar el PDF desde la URL enviada');
            }
        }

        $firmaDigital->validarTamanoArchivo($contenidoPdf);

        if (!$firmaDigital->esPdfValido($contenidoPdf)) {
            throw new Exception('El archivo obtenido no es un PDF válido');
        }

        $rutaPdfTemporal = $firmaDigital->guardarTemporal($contenidoPdf, 'pdf_original_');

        $datosFirma = $firmaDigital->prepararDatosFirma(
            $nombre,
            $correo,
            $ciudad,
            $contenidoPdf,
            'pdf',
            $nombreDocumentoOriginal
        );

        while ($repo->existeToken($datosFirma['token'])) {
            $datosFirma['token'] = $firmaDigital->generarToken(16);
        }

        $rutaImagenFirma = '';

        // PRIORIDAD 1 -> base64
        if (!empty($fotoFirma)) {
            $rutaImagenFirma = $pdfFirmado->guardarImagenFirmaTemporal($fotoFirma);
        }
        // PRIORIDAD 2 -> ruta del servidor
        elseif (!empty($data['ruta_firma'])) {

            if (!file_exists($data['ruta_firma'])) {
                throw new Exception('La ruta de la firma no existe');
            }

            $contenido = file_get_contents($data['ruta_firma']);
            $mime = mime_content_type($data['ruta_firma']);

            if (!$contenido) {
                throw new Exception('No se pudo leer la imagen de firma');
            }

            $base64 = 'data:' . $mime . ';base64,' . base64_encode($contenido);
            $rutaImagenFirma = $pdfFirmado->guardarImagenFirmaTemporal($base64);
        }

        $rutaQr = $qrFirma->generarQr($datosFirma['token']);

        $resultadoPdf = $pdfFirmado->firmarPdfExistente(
            $rutaPdfTemporal,
            $datosFirma,
            $rutaQr,
            $rutaImagenFirma
        );

        $guardar = array(
            'token' => $datosFirma['token'],
            'nombre_firmante' => $datosFirma['nombre_firmante'],
            'correo_firmante' => $datosFirma['correo_firmante'],
            'ciudad' => $datosFirma['ciudad'],
            'tipo_documento' => $datosFirma['tipo_documento'],
            'nombre_documento_original' => $datosFirma['nombre_documento_original'],
            'nombre_documento_firmado' => $resultadoPdf['nombre_archivo'],
            'hash_documento' => $datosFirma['hash_documento'],
            'firma_base64' => $datosFirma['firma_base64'],
            'clave_publica' => $datosFirma['clave_publica'],
            'algoritmo' => $datosFirma['algoritmo'],
            'fecha_firma' => $datosFirma['fecha_firma'],
            'ruta_documento_firmado' => $resultadoPdf['ruta_archivo'],
            'estado' => $datosFirma['estado'],
            'observacion' => $pdfBase64 !== '' ? 'Documento PDF firmado desde base64' : 'Documento PDF firmado desde URL'
        );

        $idFirma = $repo->guardarFirma($guardar);
        $repo->guardarAuditoria($idFirma, 'FIRMAR_PDF', $pdfBase64 !== '' ? 'Documento PDF firmado desde base64' : 'Documento PDF firmado desde URL');

        $firmaDigital->eliminarArchivo($rutaPdfTemporal);

        if ($rutaImagenFirma !== '') {
            $pdfFirmado->eliminarTemporal($rutaImagenFirma);
        }

        $response = array();
        $response['error'] = false;
        $response['message'] = 'Documento PDF firmado correctamente';
        $response['data'] = array(
            'id_firma' => $idFirma,
            'token' => $datosFirma['token'],
            'hash' => $datosFirma['hash_documento'],
            'fecha_firma' => $datosFirma['fecha_firma'],
            'nombre_archivo' => $resultadoPdf['nombre_archivo'],
            'url_verificacion' => FIRMA_BASE_URL . '/aut/firma.php/firma/verificar/' . $datosFirma['token'],
            'url_descarga' => FIRMA_BASE_URL . '/aut/firma.php/firma/descargar/' . $datosFirma['token']
        );

        echoResponse(200, $response);
    } catch (Exception $e) {
        $response = array(
            'error' => true,
            'message' => $e->getMessage()
        );
        echoResponse(400, $response);
    }
});

/* =========================================================
   ENDPOINT: GET /firma/verificar/:token
========================================================= */
$app->get('/firma/verificar/:token', function ($token) {
    try {
        $repo = new FirmaRepository();
        $doc = $repo->obtenerPorToken($token);

        if (!$doc) {
            throw new Exception('No existe un documento con ese token');
        }

        $repo->guardarAuditoria($doc['id_firma'], 'VERIFICAR_DOCUMENTO', 'Consulta pública de verificación');

        $response = array(
            'error' => false,
            'message' => 'Documento encontrado',
            'data' => $doc
        );

        echoResponse(200, $response);
    } catch (Exception $e) {
        $response = array(
            'error' => true,
            'message' => $e->getMessage()
        );
        echoResponse(404, $response);
    }
});

/* =========================================================
   ENDPOINT: GET /firma/descargar/:token
========================================================= */
$app->get('/firma/descargar/:token', function ($token) use ($app) {
    try {
        $repo = new FirmaRepository();
        $doc = $repo->obtenerRutaDocumentoPorToken($token);

        if (!$doc) {
            throw new Exception('No existe un documento con ese token');
        }

        if (!file_exists($doc['ruta_documento_firmado'])) {
            throw new Exception('El archivo firmado no existe en el servidor');
        }

        $repo->guardarAuditoria($doc['id_firma'], 'DESCARGAR_DOCUMENTO', 'Descarga de documento firmado');

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $doc['nombre_documento_firmado'] . '"');
        header('Content-Length: ' . filesize($doc['ruta_documento_firmado']));
        readfile($doc['ruta_documento_firmado']);
        exit;
    } catch (Exception $e) {
        $response = array(
            'error' => true,
            'message' => $e->getMessage()
        );
        echoResponse(404, $response);
    }
});


/* =========================================================
   ENDPOINT: POST /firma/docx
   Recibe un DOCX en base64 o por URL, lo convierte a PDF y lo firma
========================================================= */
$app->post('/firma/docx', 'authenticate', function () use ($app) {
    $rutaDocxTemporal = '';
    $rutaPdfTemporal = '';
    $rutaImagenFirma = '';

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            throw new Exception('No se recibieron datos JSON válidos');
        }

        $nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
        $correo = isset($data['correo']) ? trim($data['correo']) : '';
        $ciudad = isset($data['ciudad']) ? trim($data['ciudad']) : '';
        $docxBase64 = isset($data['docx_base64']) ? trim($data['docx_base64']) : '';
        $docxUrl = isset($data['docx_url']) ? trim($data['docx_url']) : '';
        $fotoFirma = isset($data['foto_firma']) ? $data['foto_firma'] : '';
        $nombreDocumentoOriginal = isset($data['nombre_documento']) ? trim($data['nombre_documento']) : 'documento_subido.docx';

        if ($nombre === '' || $correo === '') {
            throw new Exception('Los campos nombre y correo son obligatorios');
        }

        if ($docxBase64 === '' && $docxUrl === '') {
            throw new Exception('Debe enviar docx_base64 o docx_url');
        }

        $firmaDigital = new FirmaDigital();
        $pdfFirmado = new PdfFirmado();
        $repo = new FirmaRepository();
        $qrFirma = new QrFirma();
        $docxFirmado = new DocxFirmado();

        $contenidoDocx = '';

        // PRIORIDAD 1 -> DOCX base64
        if ($docxBase64 !== '') {
            $contenidoDocx = $docxFirmado->decodificarDocxBase64($docxBase64);
        }
        // PRIORIDAD 2 -> DOCX por URL
        else {
            $contenidoDocx = $docxFirmado->descargarDocxDesdeUrl($docxUrl);
        }

        $firmaDigital->validarTamanoArchivo($contenidoDocx);
        $docxFirmado->validarDocx($contenidoDocx);

        // Guardar DOCX temporal
        $rutaDocxTemporal = $docxFirmado->guardarTemporal($contenidoDocx, 'docx_original_', '.docx');

        // Convertir DOCX -> PDF
        $rutaPdfTemporal = $docxFirmado->convertirDocxAPdf($rutaDocxTemporal);

        if (!file_exists($rutaPdfTemporal)) {
            throw new Exception('No se generó el PDF temporal desde el DOCX');
        }

        $contenidoPdf = @file_get_contents($rutaPdfTemporal);

        if ($contenidoPdf === false || empty($contenidoPdf)) {
            throw new Exception('No se pudo leer el PDF generado desde el DOCX');
        }

        if (!$firmaDigital->esPdfValido($contenidoPdf)) {
            throw new Exception('El PDF generado desde el DOCX no es válido');
        }

        $datosFirma = $firmaDigital->prepararDatosFirma(
            $nombre,
            $correo,
            $ciudad,
            $contenidoPdf,
            'docx',
            $nombreDocumentoOriginal
        );

        while ($repo->existeToken($datosFirma['token'])) {
            $datosFirma['token'] = $firmaDigital->generarToken(16);
        }

        // PRIORIDAD 1 -> firma en base64
        if (!empty($fotoFirma)) {
            $rutaImagenFirma = $pdfFirmado->guardarImagenFirmaTemporal($fotoFirma);
        }
        // PRIORIDAD 2 -> ruta del servidor
        elseif (!empty($data['ruta_firma'])) {
            if (!file_exists($data['ruta_firma'])) {
                throw new Exception('La ruta de la firma no existe');
            }

            $contenido = file_get_contents($data['ruta_firma']);
            $mime = mime_content_type($data['ruta_firma']);

            if (!$contenido) {
                throw new Exception('No se pudo leer la imagen de firma');
            }

            $base64 = 'data:' . $mime . ';base64,' . base64_encode($contenido);
            $rutaImagenFirma = $pdfFirmado->guardarImagenFirmaTemporal($base64);
        }

        $rutaQr = $qrFirma->generarQr($datosFirma['token']);

        // Reutiliza tu misma lógica actual de firmado PDF
        $resultadoPdf = $pdfFirmado->firmarPdfExistente(
            $rutaPdfTemporal,
            $datosFirma,
            $rutaQr,
            $rutaImagenFirma
        );

        $guardar = array(
            'token' => $datosFirma['token'],
            'nombre_firmante' => $datosFirma['nombre_firmante'],
            'correo_firmante' => $datosFirma['correo_firmante'],
            'ciudad' => $datosFirma['ciudad'],
            'tipo_documento' => $datosFirma['tipo_documento'],
            'nombre_documento_original' => $datosFirma['nombre_documento_original'],
            'nombre_documento_firmado' => $resultadoPdf['nombre_archivo'],
            'hash_documento' => $datosFirma['hash_documento'],
            'firma_base64' => $datosFirma['firma_base64'],
            'clave_publica' => $datosFirma['clave_publica'],
            'algoritmo' => $datosFirma['algoritmo'],
            'fecha_firma' => $datosFirma['fecha_firma'],
            'ruta_documento_firmado' => $resultadoPdf['ruta_archivo'],
            'estado' => $datosFirma['estado'],
            'observacion' => $docxBase64 !== ''
                ? 'Documento DOCX convertido a PDF y firmado desde base64'
                : 'Documento DOCX convertido a PDF y firmado desde URL'
        );

        $idFirma = $repo->guardarFirma($guardar);

        $repo->guardarAuditoria(
            $idFirma,
            'FIRMAR_DOCX',
            $docxBase64 !== ''
                ? 'Documento DOCX convertido a PDF y firmado desde base64'
                : 'Documento DOCX convertido a PDF y firmado desde URL'
        );

        // Limpieza
        if ($rutaDocxTemporal !== '') {
            $docxFirmado->eliminarTemporal($rutaDocxTemporal);
        }

        if ($rutaPdfTemporal !== '') {
            $docxFirmado->eliminarTemporal($rutaPdfTemporal);
        }

        if ($rutaImagenFirma !== '') {
            $pdfFirmado->eliminarTemporal($rutaImagenFirma);
        }

        $response = array();
        $response['error'] = false;
        $response['message'] = 'Documento DOCX convertido a PDF y firmado correctamente';
        $response['data'] = array(
            'id_firma' => $idFirma,
            'token' => $datosFirma['token'],
            'hash' => $datosFirma['hash_documento'],
            'fecha_firma' => $datosFirma['fecha_firma'],
            'nombre_archivo' => $resultadoPdf['nombre_archivo'],
            'url_verificacion' => FIRMA_BASE_URL . '/aut/firma.php/firma/verificar/' . $datosFirma['token'],
            'url_descarga' => FIRMA_BASE_URL . '/aut/firma.php/firma/descargar/' . $datosFirma['token']
        );

        echoResponse(200, $response);
    } catch (Exception $e) {

        // Limpieza en error
        if (!empty($rutaDocxTemporal) && file_exists($rutaDocxTemporal)) {
            @unlink($rutaDocxTemporal);
        }

        if (!empty($rutaPdfTemporal) && file_exists($rutaPdfTemporal)) {
            @unlink($rutaPdfTemporal);
        }

        if (!empty($rutaImagenFirma) && file_exists($rutaImagenFirma)) {
            @unlink($rutaImagenFirma);
        }

        $response = array(
            'error' => true,
            'message' => $e->getMessage()
        );

        echoResponse(400, $response);
    }
});

/*********************** FUNCIONES ÚTILES **************************************/

function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;

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

function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json; charset=utf-8');
    echo json_encode($response);
}

function authenticate(\Slim\Route $route)
{
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);

    if (isset($normalizedHeaders['authorization'])) {
        $token = $normalizedHeaders['authorization'];

        if (!($token == API_KEY)) {
            $response["error"] = true;
            $response["message"] = "Acceso denegado. Token inválido";
            echoResponse(401, $response);
            $app->stop();
        }
    } else {
        $response["error"] = true;
        $response["message"] = "Falta token de autorización";
        echoResponse(400, $response);
        $app->stop();
    }
}

$app->run();
