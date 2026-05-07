<?php

require_once dirname(__FILE__) . '/../include/ConfigFirma.php';
require_once dirname(__FILE__) . '/../libs/qrcode/php/qrcode.php';

class QrFirma
{

    public function generarQr($token)
    {
        if (trim($token) === '') {
            throw new Exception('Token requerido');
        }

        $url = FIRMA_BASE_URL . '/aut/firma.php/firma/verificar/' . $token;

        $nombre = $token . '.png';
        $ruta = rtrim(FIRMA_QR_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombre;

        if (!extension_loaded('gd')) {
            throw new Exception('La extensión GD no está habilitada en PHP');
        }

        if (!file_exists(FIRMA_QR_PATH)) {
            @mkdir(FIRMA_QR_PATH, 0775, true);
        }

        // Esta línea calcula automáticamente el tamaño correcto del QR
        $qr = QRCode::getMinimumQRCode($url, QR_ERROR_CORRECT_LEVEL_L);

        $image = $qr->createImage(6, 2);

        if (!$image) {
            throw new Exception('No se pudo generar la imagen QR');
        }

        imagepng($image, $ruta);
        imagedestroy($image);

        clearstatcache();

        if (!file_exists($ruta)) {
            throw new Exception('No se guardó el QR');
        }

        return $ruta;
    }
}
