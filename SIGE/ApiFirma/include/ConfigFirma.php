<?php

/*
|--------------------------------------------------------------------------
| CONFIGURACION MODULO FIRMA DIGITAL
|--------------------------------------------------------------------------
| Este archivo centraliza la configuracion del modulo de firma digital
| sin afectar el Config.php general del proyecto.
*/

/* =========================
   BASE DE DATOS
========================= */
define('FIRMA_DB', 'firmaInterna');

/* =========================
   RUTAS PRINCIPALES
========================= */
define('FIRMA_BASE_PATH', realpath(dirname(__FILE__) . '/../'));

/* =========================
   CERTIFICADOS
========================= */
define('FIRMA_CERT_PATH', FIRMA_BASE_PATH . '/certificados/certificado.crt');
define('FIRMA_PRIVATE_KEY_PATH', FIRMA_BASE_PATH . '/certificados/clave_privada.pem');

/*
| Si tu clave privada tiene contraseña, colócala aquí.
| Si no tiene, déjalo vacío.
*/
define('FIRMA_PRIVATE_KEY_PASS', '');

/* =========================
   DIRECTORIOS DE TRABAJO
========================= */
define('FIRMA_TMP_PATH', FIRMA_BASE_PATH . '/tmp');
define('FIRMA_SIGNED_PATH', FIRMA_BASE_PATH . '/docs/firmados');
define('FIRMA_QR_PATH', FIRMA_BASE_PATH . '/docs/qrs');

/* =========================
   URLS DEL MODULO
========================= */
define('FIRMA_BASE_URL', 'http://192.168.10.10/ApiFirma');
define('FIRMA_VERIFY_URL', FIRMA_BASE_URL . '/aut/firma/verificar');

/* =========================
   PARAMETROS DE SEGURIDAD
========================= */
define('FIRMA_MAX_FILE_SIZE', 10485760); // 10 MB
define('FIRMA_ALLOWED_EXTENSIONS', 'pdf');
define('FIRMA_HASH_ALGORITHM', 'sha256');
define('FIRMA_SIGN_ALGORITHM', OPENSSL_ALGO_SHA256);

/* =========================
   ESTADOS
========================= */
define('FIRMA_ESTADO_FIRMADO', 'firmado');
define('FIRMA_ESTADO_ERROR', 'error');
define('FIRMA_ESTADO_REVOCADO', 'revocado');

/* =========================
   CONFIGURACION GENERAL
========================= */
date_default_timezone_set('America/Bogota');
