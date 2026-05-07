<?php

require_once dirname(__FILE__) . '/../include/ConfigFirma.php';

/*
|--------------------------------------------------------------------------
| LIBRERIAS NUEVAS
|--------------------------------------------------------------------------
| DOMPDF para HTML -> PDF
| FPDF + FPDI 2 para importar PDF y agregar evidencia de firma
*/
require_once dirname(__FILE__) . '/../libs/dompdf/autoload.inc.php';
require_once dirname(__FILE__) . '/../libs/FPDF/fpdf.php';
require_once dirname(__FILE__) . '/../libs/FPDI/src/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;

class PdfFirmado
{
    public function __construct()
    {
        $this->crearDirectoriosSiNoExisten();
    }

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

    public function generarNombreArchivoFirmado($token)
    {
        return $token . '.pdf';
    }

    public function obtenerRutaArchivoFirmado($nombreArchivo)
    {
        return rtrim(FIRMA_SIGNED_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombreArchivo;
    }

    /**
     * Genera PDF desde HTML y luego le agrega una página de evidencia
     */
    public function generarDesdeHtml($htmlContenido, $datosFirma, $rutaQr = '', $rutaImagenFirma = '')
    {
        if (trim($htmlContenido) === '') {
            throw new Exception('El contenido HTML está vacío');
        }

        if (empty($datosFirma['token'])) {
            throw new Exception('No existe token de firma');
        }

        $nombreArchivo = $this->generarNombreArchivoFirmado($datosFirma['token']);
        $rutaSalida = $this->obtenerRutaArchivoFirmado($nombreArchivo);

        $tmpPdf = rtrim(FIRMA_TMP_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'html_' . uniqid() . '.pdf';

        $this->crearPdfTemporalDesdeHtml($htmlContenido, $tmpPdf);

        try {
            $resultado = $this->firmarPdfExistente($tmpPdf, $datosFirma, $rutaQr, $rutaImagenFirma, $rutaSalida);
        } finally {
            if (file_exists($tmpPdf)) {
                @unlink($tmpPdf);
            }
        }

        return $resultado;
    }

    /**
     * Importa un PDF existente y agrega la página final de evidencia de firma
     */
    public function firmarPdfExistente($rutaPdfOriginal, $datosFirma, $rutaQr = '', $rutaImagenFirma = '', $rutaSalidaForzada = '')
    {
        if (!file_exists($rutaPdfOriginal)) {
            throw new Exception('El PDF original no existe en la ruta indicada');
        }

        if (empty($datosFirma['token'])) {
            throw new Exception('No existe token de firma');
        }

        $nombreArchivo = $this->generarNombreArchivoFirmado($datosFirma['token']);
        $rutaSalida = $rutaSalidaForzada !== ''
            ? $rutaSalidaForzada
            : $this->obtenerRutaArchivoFirmado($nombreArchivo);

        $pdf = new Fpdi();

        try {
            $pageCount = $pdf->setSourceFile($rutaPdfOriginal);
        } catch (\Throwable $e) {
            throw new Exception(
                'El PDF enviado no es compatible con el motor de firma actual. '
                . 'Por favor vuelva a guardarlo como PDF estándar o imprímalo nuevamente en PDF antes de firmarlo.'
            );
        }

        for ($i = 1; $i <= $pageCount; $i++) {
            $tplIdx = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tplIdx);

            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, array($size['width'], $size['height']));
            $pdf->useTemplate($tplIdx);
        }

        $this->agregarPaginaEvidenciaFirma($pdf, $datosFirma, $rutaQr, $rutaImagenFirma);

        $pdf->Output('F', $rutaSalida);

        if (!file_exists($rutaSalida)) {
            throw new Exception('No se pudo generar el PDF firmado');
        }

        return array(
            'nombre_archivo' => basename($rutaSalida),
            'ruta_archivo'   => $rutaSalida
        );
    }

    /**
     * Convierte HTML en PDF temporal con Dompdf
     */
    private function crearPdfTemporalDesdeHtml($htmlContenido, $rutaSalida)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContenido, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        if (file_put_contents($rutaSalida, $output) === false) {
            throw new Exception('No se pudo guardar el PDF temporal generado desde HTML');
        }
    }

    /**
     * Página final de evidencia de firma
     * Diseño profesional, limpio y organizado
     */
    private function agregarPaginaEvidenciaFirma($pdf, $datosFirma, $rutaQr = '', $rutaImagenFirma = '')
    {
        $pdf->AddPage('P', 'A4');

        // Configuración base
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, 0, 210, 297, 'F');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(false);

        // Datos
        $token     = isset($datosFirma['token']) ? trim($datosFirma['token']) : '';
        $hash      = isset($datosFirma['hash_documento']) ? trim($datosFirma['hash_documento']) : '';
        $algoritmo = isset($datosFirma['algoritmo']) ? trim($datosFirma['algoritmo']) : 'RSA-SHA256';
        $fecha     = isset($datosFirma['fecha_firma']) ? trim($datosFirma['fecha_firma']) : '';
        $firmante  = isset($datosFirma['nombre_firmante']) ? trim($datosFirma['nombre_firmante']) : '';
        $correo    = isset($datosFirma['correo_firmante']) ? trim($datosFirma['correo_firmante']) : '';
        $ciudad    = isset($datosFirma['ciudad']) ? trim($datosFirma['ciudad']) : '';
        $tipoDoc   = isset($datosFirma['tipo_documento']) ? strtoupper(trim($datosFirma['tipo_documento'])) : '';
        $original  = isset($datosFirma['nombre_documento_original']) ? trim($datosFirma['nombre_documento_original']) : '';

        // Versiones cortas para no romper el diseño
        $tokenCorto    = $this->acortarTexto($token, 48);
        $hashCorto     = $this->acortarTexto($hash, 68);
        $firmanteCorto = $this->acortarTexto($firmante, 40);
        $correoCorto   = $this->acortarTexto($correo, 40);
        $originalCorto = $this->acortarTexto($original, 40);

        // ===== ENCABEZADO SUPERIOR =====
        $pdf->SetDrawColor(70, 70, 70);
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(15, 15, 180, 24);

        $pdf->SetXY(20, 20);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(170, 6, utf8_decode('CONSTANCIA DE FIRMA DIGITAL'), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 8.8);
        $pdf->SetX(20);
        $pdf->Cell(170, 5, utf8_decode('Documento firmado digitalmente con evidencia visual y mecanismo de verificación'), 0, 1, 'C');

        // Línea separadora elegante
        $pdf->SetDrawColor(110, 110, 110);
        $pdf->Line(15, 47, 195, 47);

        // ===== BLOQUE ESTADO =====
        $pdf->SetXY(15, 53);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(180, 5, utf8_decode('Estado del documento'), 0, 1, 'L');

        $pdf->SetDrawColor(120, 120, 120);
        $pdf->Rect(15, 60, 180, 12);

        $pdf->SetXY(15, 64);
        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->Cell(180, 4, utf8_decode('DOCUMENTO FIRMADO DIGITALMENTE Y VERIFICABLE'), 0, 1, 'C');

        // ===== BLOQUE FIRMA VISIBLE =====
        $pdf->SetXY(15, 80);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(180, 5, utf8_decode('Firma visible del firmante'), 0, 1, 'L');

        $pdf->Rect(15, 87, 180, 40);

        if ($rutaImagenFirma !== '' && file_exists($rutaImagenFirma)) {
            $pdf->Image($rutaImagenFirma, 53, 97, 104, 15, 'PNG');
            $pdf->Line(48, 113, 162, 113);

            $pdf->SetXY(15, 116);
            $pdf->SetFont('Arial', '', 9.2);
            $pdf->Cell(180, 4, utf8_decode($firmante), 0, 1, 'C');
        } else {
            $pdf->SetXY(15, 104);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(180, 4, utf8_decode('No se adjuntó firma visible'), 0, 1, 'C');
        }

        // ===== BLOQUE DE VALIDACIÓN =====
        $pdf->SetXY(15, 136);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(180, 5, utf8_decode('Información de validación'), 0, 1, 'L');

        $panelY = 143;
        $panelH = 88;

        $pdf->Rect(15, $panelY, 180, $panelH);

        // división QR / contenido
        $pdf->Line(58, $panelY, 58, $panelY + $panelH);

        // QR
        $pdf->SetXY(18, $panelY + 5);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(36, 4, utf8_decode('CÓDIGO QR'), 0, 1, 'C');

        if ($rutaQr !== '' && file_exists($rutaQr)) {
            $pdf->Image($rutaQr, 20, $panelY + 12, 32, 32, 'PNG');
        } else {
            $pdf->Rect(20, $panelY + 12, 32, 32);
            $pdf->SetXY(20, $panelY + 26);
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(32, 4, utf8_decode('SIN QR'), 0, 1, 'C');
        }

        $pdf->SetXY(18, $panelY + 48);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->MultiCell(36, 3.4, utf8_decode('Escanee este código para realizar la validación del documento'), 0, 'C');

        // contenido derecho
        $xLabel = 63;
        $xValue = 96;
        $y      = $panelY + 7;
        $lineH  = 5.3;

        $this->dibujarFilaDato($pdf, $xLabel, $xValue, $y, 'Código:', $tokenCorto, $lineH, 92);
        $y += $lineH;
        $this->dibujarFilaDato($pdf, $xLabel, $xValue, $y, 'Hash:', $hashCorto, $lineH, 92);
        $y += $lineH;
        $this->dibujarFilaDato($pdf, $xLabel, $xValue, $y, 'Firmante:', $firmanteCorto, $lineH, 92);
        $y += $lineH;
        $this->dibujarFilaDato($pdf, $xLabel, $xValue, $y, 'Correo:', $correoCorto, $lineH, 92);
        $y += $lineH;
        $this->dibujarFilaDato($pdf, $xLabel, $xValue, $y, 'Ciudad:', $ciudad, $lineH, 92);
        $y += $lineH;
        $this->dibujarFilaDato($pdf, $xLabel, $xValue, $y, 'Fecha:', $fecha, $lineH, 92);
        $y += $lineH;
        $this->dibujarFilaDato($pdf, $xLabel, $xValue, $y, 'Algoritmo:', $algoritmo, $lineH, 92);
        $y += $lineH;
        $this->dibujarFilaDato($pdf, $xLabel, $xValue, $y, 'Tipo:', $tipoDoc, $lineH, 92);
        $y += $lineH;
        $this->dibujarFilaDato($pdf, $xLabel, $xValue, $y, 'Original:', $originalCorto, $lineH, 92);

        // área inferior de texto
        $pdf->Line(58, $panelY + 66, 195, $panelY + 66);

        $pdf->SetXY(63, $panelY + 70);
        $pdf->SetFont('Arial', '', 7);
        $pdf->MultiCell(
            126,
            3.7,
            utf8_decode('La autenticidad de este documento debe comprobarse mediante el código QR o el token de verificación asociado al proceso de firma digital.'),
            0,
            'L'
        );

        // banda de seguridad visual
        $barX = 63;
        $barY = $panelY + 81;
        $barW = 125;
        $barH = 5.5;

        $pdf->Rect($barX, $barY, $barW, $barH);

        $currentX = $barX + 2;
        $maxX = $barX + $barW - 2;
        $seed = md5($token . $hash . $firmante . $fecha);

        for ($i = 0; $i < strlen($seed) && $currentX < $maxX; $i++) {
            $n = hexdec($seed[$i]);
            $w = ($n % 3) + 1;
            $gap = 1;

            if (($currentX + $w) <= $maxX) {
                $pdf->Rect($currentX, $barY + 0.5, $w, $barH - 1, 'F');
            }

            $currentX += ($w + $gap);
        }

        // ===== NOTA LEGAL =====
        $pdf->SetXY(15, 240);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->MultiCell(
            180,
            4.5,
            utf8_decode('La firma visible incluida en esta constancia tiene fines de presentación. La validez oficial del documento debe verificarse mediante el mecanismo digital de comprobación asociado al código QR y al token de verificación.'),
            0,
            'J'
        );

        // pie sutil
        $pdf->Line(15, 272, 195, 272);
        $pdf->SetXY(15, 276);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell(180, 4, utf8_decode('Constancia generada automáticamente por el sistema de firma digital'), 0, 1, 'C');
    }

    /**
     * Dibuja una fila de dato
     */
    private function dibujarFilaDato($pdf, $xLabel, $xValue, $y, $label, $value, $alto = 5, $anchoValor = 92)
    {
        $pdf->SetXY($xLabel, $y);
        $pdf->SetFont('Arial', 'B', 7.6);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(31, $alto, utf8_decode($label), 0, 0, 'L');

        $pdf->SetFont('Arial', '', 7.6);
        $pdf->SetXY($xValue, $y);
        $pdf->Cell($anchoValor, $alto, utf8_decode($value), 0, 0, 'L');
    }

    /**
     * Acorta texto sin romper diseño
     */
    private function acortarTexto($texto, $max)
    {
        $texto = (string)$texto;

        if (strlen($texto) <= $max) {
            return $texto;
        }

        return substr($texto, 0, $max) . '...';
    }

    public function guardarImagenFirmaTemporal($imagenBase64, $prefijo = 'firma_')
    {
        if (!$imagenBase64) {
            return '';
        }

        if (!extension_loaded('gd')) {
            throw new Exception('La extensión GD no está habilitada en PHP');
        }

        if (preg_match('/^data:image\/\w+;base64,/', $imagenBase64)) {
            $imagenBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $imagenBase64);
        }

        $contenido = base64_decode($imagenBase64, true);

        if ($contenido === false) {
            throw new Exception('La imagen de firma en base64 no es válida');
        }

        $source = imagecreatefromstring($contenido);

        if (!$source) {
            throw new Exception('No se pudo procesar la imagen de la firma');
        }

        $srcWidth  = imagesx($source);
        $srcHeight = imagesy($source);

        if ($srcWidth <= 0 || $srcHeight <= 0) {
            imagedestroy($source);
            throw new Exception('Dimensiones inválidas en la imagen de firma');
        }

        $work = imagecreatetruecolor($srcWidth, $srcHeight);
        imagealphablending($work, false);
        imagesavealpha($work, true);

        $transparent = imagecolorallocatealpha($work, 255, 255, 255, 127);
        imagefill($work, 0, 0, $transparent);
        imagecopy($work, $source, 0, 0, 0, 0, $srcWidth, $srcHeight);

        imagefilter($work, IMG_FILTER_GRAYSCALE);
        imagefilter($work, IMG_FILTER_CONTRAST, -35);
        imagefilter($work, IMG_FILTER_BRIGHTNESS, 10);

        $bbox = $this->limpiarYDetectarFirma($work);

        if (!$bbox) {
            imagedestroy($source);
            imagedestroy($work);
            throw new Exception('No se detectó un trazo de firma válido en la imagen');
        }

        list($minX, $minY, $maxX, $maxY) = $bbox;

        $padding = 12;
        $minX = max(0, $minX - $padding);
        $minY = max(0, $minY - $padding);
        $maxX = min($srcWidth - 1, $maxX + $padding);
        $maxY = min($srcHeight - 1, $maxY + $padding);

        $cropWidth  = $maxX - $minX + 1;
        $cropHeight = $maxY - $minY + 1;

        $cropped = imagecreatetruecolor($cropWidth, $cropHeight);
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        $transparentCrop = imagecolorallocatealpha($cropped, 255, 255, 255, 127);
        imagefill($cropped, 0, 0, $transparentCrop);

        imagecopy($cropped, $work, 0, 0, $minX, $minY, $cropWidth, $cropHeight);

        $finalWidth  = 900;
        $finalHeight = 260;

        $final = imagecreatetruecolor($finalWidth, $finalHeight);
        imagealphablending($final, false);
        imagesavealpha($final, true);
        $transparentFinal = imagecolorallocatealpha($final, 255, 255, 255, 127);
        imagefill($final, 0, 0, $transparentFinal);

        $ratio = min($finalWidth / $cropWidth, $finalHeight / $cropHeight);
        $newWidth  = max(1, (int) round($cropWidth * $ratio));
        $newHeight = max(1, (int) round($cropHeight * $ratio));

        $dstX = (int) floor(($finalWidth - $newWidth) / 2);
        $dstY = (int) floor(($finalHeight - $newHeight) / 2);

        imagecopyresampled(
            $final,
            $cropped,
            $dstX,
            $dstY,
            0,
            0,
            $newWidth,
            $newHeight,
            $cropWidth,
            $cropHeight
        );

        $nombre = $prefijo . uniqid() . '.png';
        $ruta = rtrim(FIRMA_TMP_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombre;

        $ok = imagepng($final, $ruta);

        imagedestroy($source);
        imagedestroy($work);
        imagedestroy($cropped);
        imagedestroy($final);

        if ($ok === false || !file_exists($ruta)) {
            throw new Exception('No se pudo guardar la imagen temporal de firma');
        }

        return $ruta;
    }

    private function limpiarYDetectarFirma(&$img)
    {
        $width  = imagesx($img);
        $height = imagesy($img);

        $minX = $width;
        $minY = $height;
        $maxX = 0;
        $maxY = 0;

        $found = false;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($img, $x, $y);

                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                $gray = (int)(($r + $g + $b) / 3);

                if ($gray < 185) {
                    $found = true;

                    if ($x < $minX) $minX = $x;
                    if ($y < $minY) $minY = $y;
                    if ($x > $maxX) $maxX = $x;
                    if ($y > $maxY) $maxY = $y;

                    $dark = imagecolorallocatealpha($img, 20, 20, 20, 0);
                    imagesetpixel($img, $x, $y, $dark);
                } else {
                    $transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
                    imagesetpixel($img, $x, $y, $transparent);
                }
            }
        }

        if (!$found) {
            return false;
        }

        return array($minX, $minY, $maxX, $maxY);
    }

    public function eliminarTemporal($rutaArchivo)
    {
        if ($rutaArchivo && file_exists($rutaArchivo)) {
            @unlink($rutaArchivo);
        }
    }
}