<?php

require_once dirname(__FILE__) . '/../include/ConfigFirma.php';

class FirmaDigital
{
    public function __construct()
    {
        $this->crearDirectoriosSiNoExisten();
    }

    /**
     * Crear directorios necesarios si no existen
     */
    private function crearDirectoriosSiNoExisten()
    {
        $directorios = array(
            FIRMA_TMP_PATH,
            FIRMA_SIGNED_PATH,
            FIRMA_QR_PATH
        );

        foreach ($directorios as $dir) {
            if (!file_exists($dir)) {
                @mkdir($dir, 0775, true);
            }
        }
    }

    /**
     * Validar existencia de certificado y clave privada
     */
    public function validarCertificados()
    {
        if (!file_exists(FIRMA_CERT_PATH)) {
            throw new Exception('No existe el certificado en la ruta: ' . FIRMA_CERT_PATH);
        }

        if (!file_exists(FIRMA_PRIVATE_KEY_PATH)) {
            throw new Exception('No existe la clave privada en la ruta: ' . FIRMA_PRIVATE_KEY_PATH);
        }

        if (!is_readable(FIRMA_CERT_PATH)) {
            throw new Exception('El certificado no tiene permisos de lectura');
        }

        if (!is_readable(FIRMA_PRIVATE_KEY_PATH)) {
            throw new Exception('La clave privada no tiene permisos de lectura');
        }

        return true;
    }

    /**
     * Generar token único
     */
    public function generarToken($longitud = 16)
    {
        $caracteres = '0123456789abcdef';
        $token = '';

        for ($i = 0; $i < ($longitud * 2); $i++) {
            $token .= $caracteres[mt_rand(0, 15)];
        }

        return $token;
    }

    /**
     * Obtener fecha actual para registrar la firma
     */
    public function getFechaActual()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Calcular hash SHA-256 de un contenido
     */
    public function calcularHash($contenido)
    {
        if ($contenido === null || $contenido === '') {
            throw new Exception('No se puede calcular hash de un contenido vacío');
        }

        return hash(FIRMA_HASH_ALGORITHM, $contenido);
    }

    /**
     * Firmar un hash con la clave privada
     */
    public function firmarHash($hash)
    {
        $this->validarCertificados();

        $privateKeyContent = file_get_contents(FIRMA_PRIVATE_KEY_PATH);
        if ($privateKeyContent === false) {
            throw new Exception('No se pudo leer la clave privada');
        }

        if (defined('FIRMA_PRIVATE_KEY_PASS') && FIRMA_PRIVATE_KEY_PASS !== '') {
            $privateKey = openssl_pkey_get_private($privateKeyContent, FIRMA_PRIVATE_KEY_PASS);
        } else {
            $privateKey = openssl_pkey_get_private($privateKeyContent);
        }

        if (!$privateKey) {
            throw new Exception('No se pudo cargar la clave privada con OpenSSL');
        }

        $signature = '';
        $resultado = openssl_sign($hash, $signature, $privateKey, FIRMA_SIGN_ALGORITHM);

        if (function_exists('openssl_free_key')) {
            openssl_free_key($privateKey);
        }

        if (!$resultado) {
            throw new Exception('Error al generar la firma digital');
        }

        return array(
            'firma_binaria' => $signature,
            'firma_base64' => base64_encode($signature)
        );
    }

    /**
     * Obtener clave pública desde el certificado
     */
    public function obtenerClavePublica()
    {
        $this->validarCertificados();

        $certContent = file_get_contents(FIRMA_CERT_PATH);
        if ($certContent === false) {
            throw new Exception('No se pudo leer el certificado');
        }

        $publicKeyResource = openssl_pkey_get_public($certContent);
        if (!$publicKeyResource) {
            throw new Exception('No se pudo cargar la clave pública del certificado');
        }

        $details = openssl_pkey_get_details($publicKeyResource);

        if (!isset($details['key'])) {
            throw new Exception('No fue posible obtener los detalles de la clave pública');
        }

        return $details['key'];
    }

    /**
     * Obtener información detallada del certificado
     */
    public function obtenerInfoCertificado()
    {
        $this->validarCertificados();

        $certContent = file_get_contents(FIRMA_CERT_PATH);
        if ($certContent === false) {
            throw new Exception('No se pudo leer el certificado');
        }

        $certResource = openssl_x509_read($certContent);
        if (!$certResource) {
            throw new Exception('No se pudo leer el certificado X509');
        }

        $info = openssl_x509_parse($certResource);
        if (!$info) {
            throw new Exception('No se pudo parsear el certificado');
        }

        return $info;
    }

    /**
     * Limpiar un string base64 con prefijo data:
     */
    public function limpiarBase64($base64)
    {
        if (!$base64) {
            return '';
        }

        $base64 = preg_replace('/^data:application\/pdf;base64,/', '', $base64);
        $base64 = preg_replace('/^data:application\/vnd\.openxmlformats-officedocument\.wordprocessingml\.document;base64,/', '', $base64);
        $base64 = preg_replace('/^data:application\/octet-stream;base64,/', '', $base64);
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);

        return trim($base64);
    }

    /**
     * Decodificar base64 de forma segura
     */
    public function decodificarBase64($base64)
    {
        $base64Limpio = $this->limpiarBase64($base64);
        $contenido = base64_decode($base64Limpio, true);

        if ($contenido === false) {
            throw new Exception('El contenido base64 no es válido');
        }

        return $contenido;
    }

    /**
     * Validar si un contenido binario corresponde a un PDF
     */
    public function esPdfValido($contenidoBinario)
    {
        if (!$contenidoBinario || strlen($contenidoBinario) < 4) {
            return false;
        }

        return (substr($contenidoBinario, 0, 4) === '%PDF');
    }

    /**
     * Validar tamaño máximo de archivo
     */
    public function validarTamanoArchivo($contenidoBinario)
    {
        $tamano = strlen($contenidoBinario);

        if ($tamano > FIRMA_MAX_FILE_SIZE) {
            throw new Exception('El archivo supera el tamaño máximo permitido de ' . FIRMA_MAX_FILE_SIZE . ' bytes');
        }

        return true;
    }

    /**
     * Guardar un archivo temporal PDF
     */
    public function guardarTemporal($contenidoBinario, $prefijo = 'tmp_')
    {
        if (!$contenidoBinario) {
            throw new Exception('No hay contenido para guardar temporalmente');
        }

        $nombre = $prefijo . uniqid() . '.pdf';
        $ruta = rtrim(FIRMA_TMP_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombre;

        $resultado = file_put_contents($ruta, $contenidoBinario);

        if ($resultado === false) {
            throw new Exception('No se pudo guardar el archivo temporal');
        }

        return $ruta;
    }

    /**
     * Eliminar archivo si existe
     */
    public function eliminarArchivo($rutaArchivo)
    {
        if ($rutaArchivo && file_exists($rutaArchivo)) {
            @unlink($rutaArchivo);
        }
    }

    /**
     * Validar tipo de documento permitido
     */
    private function esTipoDocumentoValido($tipoDocumento)
    {
        return in_array($tipoDocumento, array('pdf', 'html', 'docx'), true);
    }

    /**
     * Preparar estructura general de firma
     */
    public function prepararDatosFirma($nombre, $correo, $ciudad, $contenidoFirmar, $tipoDocumento, $nombreDocumentoOriginal)
    {
        if (trim($nombre) === '') {
            throw new Exception('El nombre del firmante es obligatorio');
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El correo electrónico no es válido');
        }

        if (!$this->esTipoDocumentoValido($tipoDocumento)) {
            throw new Exception('El tipo de documento no es válido');
        }

        $hash = $this->calcularHash($contenidoFirmar);
        $firma = $this->firmarHash($hash);
        $clavePublica = $this->obtenerClavePublica();
        $token = $this->generarToken(16);
        $fechaFirma = $this->getFechaActual();

        return array(
            'token' => $token,
            'nombre_firmante' => trim($nombre),
            'correo_firmante' => trim($correo),
            'ciudad' => trim($ciudad),
            'tipo_documento' => $tipoDocumento,
            'nombre_documento_original' => $nombreDocumentoOriginal,
            'hash_documento' => $hash,
            'firma_binaria' => $firma['firma_binaria'],
            'firma_base64' => $firma['firma_base64'],
            'clave_publica' => $clavePublica,
            'algoritmo' => 'RSA-SHA256',
            'fecha_firma' => $fechaFirma,
            'estado' => FIRMA_ESTADO_FIRMADO
        );
    }
}
