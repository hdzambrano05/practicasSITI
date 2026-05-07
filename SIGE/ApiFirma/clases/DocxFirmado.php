<?php

class DocxFirmado
{
    private $tmpDir;

    public function __construct()
    {
        $this->tmpDir = dirname(__DIR__) . '/tmp';

        if (!is_dir($this->tmpDir)) {
            @mkdir($this->tmpDir, 0775, true);
        }

        if (!is_dir($this->tmpDir)) {
            throw new Exception('No se pudo crear la carpeta temporal');
        }

        if (!is_writable($this->tmpDir)) {
            throw new Exception('La carpeta temporal no tiene permisos de escritura');
        }
    }

    /**
     * Decodifica un DOCX base64
     */
    public function decodificarDocxBase64($docxBase64)
    {
        if (empty($docxBase64)) {
            throw new Exception('El contenido DOCX en base64 está vacío');
        }

        if (strpos($docxBase64, 'base64,') !== false) {
            $partes = explode('base64,', $docxBase64, 2);
            $docxBase64 = $partes[1];
        }

        $contenido = base64_decode($docxBase64, true);

        if ($contenido === false || empty($contenido)) {
            throw new Exception('No se pudo decodificar el DOCX en base64');
        }

        return $contenido;
    }

    /**
     * Descarga un DOCX desde URL
     */
    public function descargarDocxDesdeUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('La URL del DOCX no es válida');
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

        $contenido = @file_get_contents($url, false, $context);

        if ($contenido === false || empty($contenido)) {
            throw new Exception('No se pudo descargar el DOCX desde la URL enviada');
        }

        return $contenido;
    }

    /**
     * Valida que el archivo parezca DOCX
     */
    public function validarDocx($contenido)
    {
        if (empty($contenido)) {
            throw new Exception('El archivo DOCX está vacío');
        }

        // DOCX es ZIP, normalmente inicia con PK
        if (substr($contenido, 0, 2) !== 'PK') {
            throw new Exception('El archivo recibido no parece ser un DOCX válido');
        }

        $zipPath = $this->guardarTemporal($contenido, 'validar_docx_', '.docx');

        $zip = new ZipArchive();
        $abierto = $zip->open($zipPath);

        if ($abierto !== true) {
            @unlink($zipPath);
            throw new Exception('No se pudo abrir el DOCX como archivo comprimido');
        }

        $tieneDocumentXml = ($zip->locateName('word/document.xml') !== false);
        $zip->close();

        @unlink($zipPath);

        if (!$tieneDocumentXml) {
            throw new Exception('El archivo no contiene la estructura interna válida de un DOCX');
        }

        return true;
    }

    /**
     * Guarda un archivo temporal con prefijo y extensión
     */
    public function guardarTemporal($contenido, $prefijo = 'docx_', $extension = '.docx')
    {
        $nombre = $prefijo . uniqid('', true) . $extension;
        $ruta = $this->tmpDir . '/' . $nombre;

        $ok = @file_put_contents($ruta, $contenido);

        if ($ok === false) {
            throw new Exception('No se pudo guardar el archivo temporal');
        }

        return $ruta;
    }

    /**
     * Elimina un archivo temporal
     */
    public function eliminarTemporal($ruta)
    {
        if (!empty($ruta) && file_exists($ruta) && is_file($ruta)) {
            @unlink($ruta);
        }
    }

    /**
     * Convierte DOCX a PDF usando LibreOffice en modo headless
     * Recibe solo el path del DOCX y genera el PDF en la misma carpeta
     */
    public function convertirDocxAPdf($docxPath)
    {
        if (empty($docxPath) || !file_exists($docxPath)) {
            throw new Exception('El archivo DOCX temporal no existe');
        }

        $outputDir = dirname($docxPath);
        $binario = $this->obtenerBinarioLibreOffice();

        if (!$binario) {
            throw new Exception('LibreOffice no está instalado o no está disponible en el PATH del sistema.');
        }

        $tmpBase = $this->tmpDir . '/libreoffice_' . uniqid('', true);
        $perfilLibreOffice = $tmpBase . '/perfil';
        $homeTmp = $tmpBase . '/home';

        if (!is_dir($tmpBase) && !@mkdir($tmpBase, 0777, true)) {
            throw new Exception('No se pudo crear la carpeta temporal base para LibreOffice.');
        }

        if (!is_dir($perfilLibreOffice) && !@mkdir($perfilLibreOffice, 0777, true)) {
            $this->eliminarDirectorio($tmpBase);
            throw new Exception('No se pudo crear el perfil temporal de LibreOffice.');
        }

        if (!is_dir($homeTmp) && !@mkdir($homeTmp, 0777, true)) {
            $this->eliminarDirectorio($tmpBase);
            throw new Exception('No se pudo crear el HOME temporal de LibreOffice.');
        }

        $perfilUrl = 'file://' . $perfilLibreOffice;

        $cmd =
            'env HOME=' . escapeshellarg($homeTmp) . ' ' .
            escapeshellcmd($binario) .
            ' --headless --nologo --nofirststartwizard --nodefault --nolockcheck --norestore' .
            ' -env:UserInstallation=' . escapeshellarg($perfilUrl) .
            ' --convert-to pdf --outdir ' . escapeshellarg($outputDir) . ' ' .
            escapeshellarg($docxPath) . ' 2>&1';

        $salida = shell_exec($cmd);

        $pdfPath = $outputDir . '/' . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        if (!file_exists($pdfPath)) {
            $detalle = trim((string)$salida);
            $this->eliminarDirectorio($tmpBase);
            throw new Exception('No se pudo convertir el DOCX a PDF con LibreOffice. Detalle: ' . $detalle);
        }

        $this->eliminarDirectorio($tmpBase);

        return $pdfPath;
    }

    /**
     * Busca el binario de LibreOffice o soffice
     */
    private function obtenerBinarioLibreOffice()
    {
        $candidatos = array(
            '/usr/bin/libreoffice',
            '/usr/bin/soffice',
            '/bin/libreoffice',
            '/bin/soffice',
            'libreoffice',
            'soffice'
        );

        foreach ($candidatos as $binario) {
            if (strpos($binario, '/') === 0 && file_exists($binario)) {
                return $binario;
            }

            $resultado = trim((string) shell_exec('which ' . escapeshellarg($binario) . ' 2>/dev/null'));

            if ($resultado !== '') {
                return $resultado;
            }
        }

        return null;
    }

    /**
     * Elimina directorio de forma recursiva
     */
    private function eliminarDirectorio($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);

        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->eliminarDirectorio($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
