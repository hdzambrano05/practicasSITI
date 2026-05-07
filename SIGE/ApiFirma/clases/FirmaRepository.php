<?php

require_once dirname(__FILE__) . '/../include/ConfigFirma.php';
require_once dirname(__FILE__) . '/../include/DbConnect.php';

class FirmaRepository
{

    private $conn;

    public function __construct()
    {
        $db = new DbConnect();
        $this->conn = $db->connect(FIRMA_DB);

        if (!$this->conn) {
            throw new Exception('No se pudo establecer conexión con la base de datos de firma');
        }
    }

    /**
     * Guardar un documento firmado
     */
    public function guardarFirma($data)
    {
        $sql = "INSERT INTO firma_documentos (
                    token,
                    nombre_firmante,
                    correo_firmante,
                    ciudad,
                    tipo_documento,
                    nombre_documento_original,
                    nombre_documento_firmado,
                    hash_documento,
                    firma_base64,
                    clave_publica,
                    algoritmo,
                    fecha_firma,
                    ruta_documento_firmado,
                    estado,
                    observacion
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Error al preparar INSERT de firma: ' . $this->conn->error);
        }

        $token = isset($data['token']) ? $data['token'] : null;
        $nombre_firmante = isset($data['nombre_firmante']) ? $data['nombre_firmante'] : null;
        $correo_firmante = isset($data['correo_firmante']) ? $data['correo_firmante'] : null;
        $ciudad = isset($data['ciudad']) ? $data['ciudad'] : null;
        $tipo_documento = isset($data['tipo_documento']) ? $data['tipo_documento'] : null;
        $nombre_documento_original = isset($data['nombre_documento_original']) ? $data['nombre_documento_original'] : null;
        $nombre_documento_firmado = isset($data['nombre_documento_firmado']) ? $data['nombre_documento_firmado'] : null;
        $hash_documento = isset($data['hash_documento']) ? $data['hash_documento'] : null;
        $firma_base64 = isset($data['firma_base64']) ? $data['firma_base64'] : null;
        $clave_publica = isset($data['clave_publica']) ? $data['clave_publica'] : null;
        $algoritmo = isset($data['algoritmo']) ? $data['algoritmo'] : 'RSA-SHA256';
        $fecha_firma = isset($data['fecha_firma']) ? $data['fecha_firma'] : date('Y-m-d H:i:s');
        $ruta_documento_firmado = isset($data['ruta_documento_firmado']) ? $data['ruta_documento_firmado'] : null;
        $estado = isset($data['estado']) ? $data['estado'] : FIRMA_ESTADO_FIRMADO;
        $observacion = isset($data['observacion']) ? $data['observacion'] : null;

        $stmt->bind_param(
            "sssssssssssssss",
            $token,
            $nombre_firmante,
            $correo_firmante,
            $ciudad,
            $tipo_documento,
            $nombre_documento_original,
            $nombre_documento_firmado,
            $hash_documento,
            $firma_base64,
            $clave_publica,
            $algoritmo,
            $fecha_firma,
            $ruta_documento_firmado,
            $estado,
            $observacion
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Error al guardar firma: ' . $error);
        }

        $id = $stmt->insert_id;
        $stmt->close();

        return $id;
    }

    /**
     * Buscar un documento firmado por token
     */
    public function obtenerPorToken($token)
    {
        $sql = "SELECT 
                    id_firma,
                    token,
                    nombre_firmante,
                    correo_firmante,
                    ciudad,
                    tipo_documento,
                    nombre_documento_original,
                    nombre_documento_firmado,
                    hash_documento,
                    firma_base64,
                    clave_publica,
                    algoritmo,
                    fecha_firma,
                    ruta_documento_firmado,
                    estado,
                    observacion,
                    created_at,
                    updated_at
                FROM firma_documentos
                WHERE token = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta por token: ' . $this->conn->error);
        }

        $stmt->bind_param("s", $token);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Error al consultar firma por token: ' . $error);
        }

        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();

        return $data ? $data : null;
    }

    /**
     * Obtener ruta del archivo firmado por token
     */
    public function obtenerRutaDocumentoPorToken($token)
    {
        $sql = "SELECT 
                    id_firma,
                    nombre_documento_firmado,
                    ruta_documento_firmado,
                    estado
                FROM firma_documentos
                WHERE token = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta de ruta: ' . $this->conn->error);
        }

        $stmt->bind_param("s", $token);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Error al consultar ruta del documento: ' . $error);
        }

        $res = $stmt->get_result();
        $data = $res->fetch_assoc();
        $stmt->close();

        return $data ? $data : null;
    }

    /**
     * Validar si un token ya existe
     */
    public function existeToken($token)
    {
        $sql = "SELECT id_firma FROM firma_documentos WHERE token = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Error al preparar validación de token: ' . $this->conn->error);
        }

        $stmt->bind_param("s", $token);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Error al validar token: ' . $error);
        }

        $res = $stmt->get_result();
        $existe = ($res->num_rows > 0);
        $stmt->close();

        return $existe;
    }

    /**
     * Registrar auditoría
     */
    public function guardarAuditoria($idFirma, $accion, $detalle = null)
    {
        $sql = "INSERT INTO firma_auditoria (
                    id_firma,
                    accion,
                    detalle,
                    fecha_evento
                ) VALUES (
                    ?, ?, ?, ?
                )";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Error al preparar INSERT de auditoría: ' . $this->conn->error);
        }

        $fechaEvento = date('Y-m-d H:i:s');

        $stmt->bind_param(
            "isss",
            $idFirma,
            $accion,
            $detalle,
            $fechaEvento
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Error al guardar auditoría: ' . $error);
        }

        $id = $stmt->insert_id;
        $stmt->close();

        return $id;
    }

    /**
     * Actualizar estado de una firma
     */
    public function actualizarEstado($token, $estado, $observacion = null)
    {
        $sql = "UPDATE firma_documentos
                SET estado = ?, observacion = ?, updated_at = CURRENT_TIMESTAMP
                WHERE token = ?";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Error al preparar actualización de estado: ' . $this->conn->error);
        }

        $stmt->bind_param("sss", $estado, $observacion, $token);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception('Error al actualizar estado: ' . $error);
        }

        $filas = $stmt->affected_rows;
        $stmt->close();

        return $filas;
    }

    /**
     * Listar firmas recientes
     */
    public function listarFirmas($limite = 20)
    {
        $limite = (int)$limite;
        if ($limite <= 0) {
            $limite = 20;
        }

        $sql = "SELECT 
                    id_firma,
                    token,
                    nombre_firmante,
                    correo_firmante,
                    ciudad,
                    tipo_documento,
                    nombre_documento_original,
                    nombre_documento_firmado,
                    fecha_firma,
                    estado,
                    created_at
                FROM firma_documentos
                ORDER BY id_firma DESC
                LIMIT $limite";

        $res = $this->conn->query($sql);

        if (!$res) {
            throw new Exception('Error al listar firmas: ' . $this->conn->error);
        }

        $arr = array();
        while ($fila = $res->fetch_assoc()) {
            $arr[] = $fila;
        }

        return $arr;
    }
}
