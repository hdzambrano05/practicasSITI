<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 */
class DbHandler
{

    private $conn;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect('nova_sisa');
    }


    /**
        Get municipios postgres sql
     **/
    public function getMunicipios()
    {

        $query = "SELECT id,nombre from plan_municipio where plan_departamento_id = '22' ORDER BY nombre ASC";

        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());

        return $result;
    }
    /**
        Get sujetoverificaciontipo ->NICOLAS
     **/
    public function getsujetoverificaciontipo()
    {

        $query = "SELECT * FROM core_sujetoverificaciontipo;";

        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());

        return $result;
    }
    /**
        Get sujetoverificaciontipo individual ->NICOLAS
     **/
    public function getsujetoverificacionpor_id($id)
    {
        $id = pg_escape_string($id);


        $query_tabla = "SELECT tabla FROM core_sujetoverificaciontipo WHERE id = '$id';";
        $result_tabla = pg_query($query_tabla) or die('La consulta fallo: ' . pg_last_error());
        $row = pg_fetch_assoc($result_tabla);

        if (!$row) {
            return false;
        }

        $tabla = $row['tabla'];


        $query = "
            SELECT 
                t.*,
                muni.nombre AS nom_municipio,
                c.nom_cat AS tit_cat
            FROM core_sujetoverificacion s 
            INNER JOIN $tabla t ON s.id = t.id
            INNER JOIN core_sujetocategoria c ON s.core_sujetocategoria_id = c.id
            INNER JOIN plan_municipio muni ON muni.id = s.plan_municipio_id
            ORDER BY c.nom_cat
        ";

        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());

        return $result;
    }

    /**
        Get categorias con hijos recursivos ->NICOLAS
     **/
    public function getCategoriasCenso($padre = 0)
    {
        $padre = pg_escape_string($padre);

        $q = "SELECT * 
            FROM core_sujetocategoria 
            WHERE pad_cat = '$padre'
            ORDER BY nom_cat";

        $result = pg_query($q);
        $data = array();

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {

                // Buscar hijos recursivamente
                $hijos = $this->getCategoriasCenso($row['id']);

                if (!empty($hijos)) {
                    $row['hijos'] = $hijos;
                }

                $data[] = $row;
            }
        }

        return $data;
    }

    /**
        Get cabecera para el censo de los tipos de sujetos ->NICOLAS
     **/
    public function getcabeceraspor_id($id)
    {
        $id = pg_escape_string($id);

        $query_tabla = "SELECT tabla FROM core_sujetoverificaciontipo WHERE id = '$id';";
        $result_tabla = pg_query($query_tabla) or die('La consulta fallo: ' . pg_last_error());
        $row = pg_fetch_assoc($result_tabla);

        if (!$row) {
            return false;
        }

        $tabla = $row['tabla'];

        $query = "SELECT nombre, campo_datosdin 
                FROM plan_atributo 
                WHERE en_grid='s' 
                AND tabla='core_sujetoverificacion' 
                AND tabla_datosdin='$tabla'
                ORDER BY id";

        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());

        return $result;
    }

    /**
        Get traersujeto ->NICOLAS
     **/

    public function getSujeto($id)
    {
        $id = pg_escape_string($id);


        $query = "
            SELECT 
                s.*,
                muni.nombre as nom_municipio,
                c.nom_cat as tit_cat,
                t.tabla as tabla_tipo
            FROM core_sujetoverificacion s
            INNER JOIN plan_municipio muni ON muni.id = s.plan_municipio_id
            INNER JOIN core_sujetocategoria c ON c.id = s.core_sujetocategoria_id
            INNER JOIN core_sujetoverificaciontipo t ON t.id = s.core_sujetoverificaciontipo_id
            WHERE s.id = '$id'
        ";

        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
        $sujeto = pg_fetch_assoc($result);

        if (!$sujeto) return false;

        $tabla_dinamica = $sujeto['tabla_tipo'];
        $query2 = "SELECT * FROM " . $tabla_dinamica . " WHERE id = '$id'";
        $result2 = pg_query($query2) or die('La consulta fallo: ' . pg_last_error());
        $datos_dinamicos = pg_fetch_assoc($result2);

        if ($datos_dinamicos) {
            $sujeto = array_merge($sujeto, $datos_dinamicos);
        }

        return $sujeto;
    }

    /**
        Actualizar sujeto del censo ->NICOLAS
     **/
    public function updateSujeto($id, $datos)
    {
        $id = pg_escape_string($id);
        $query_tipo = "
            SELECT t.tabla 
            FROM core_sujetoverificacion s
            INNER JOIN core_sujetoverificaciontipo t ON t.id = s.core_sujetoverificaciontipo_id
            WHERE s.id = '$id'
        ";
        $result_tipo = pg_query($query_tipo) or die('La consulta fallo: ' . pg_last_error());
        $row_tipo = pg_fetch_assoc($result_tipo);

        if (!$row_tipo) return false;

        $tabla_dinamica = $row_tipo['tabla'];

        $query_cols_base = "
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'core_sujetoverificacion'
        ";
        $result_cols_base = pg_query($query_cols_base) or die('La consulta fallo: ' . pg_last_error());
        $cols_base = [];
        while ($row = pg_fetch_assoc($result_cols_base)) {
            $cols_base[] = $row['column_name'];
        }

        $tabla_din_escaped = pg_escape_string($tabla_dinamica);
        $query_cols_din = "
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = '$tabla_din_escaped'
        ";
        $result_cols_din = pg_query($query_cols_din) or die('La consulta fallo: ' . pg_last_error());
        $cols_dinamica = [];
        while ($row = pg_fetch_assoc($result_cols_din)) {
            $cols_dinamica[] = $row['column_name'];
        }

        $sets_base = [];
        foreach ($datos as $campo => $valor) {
            if (in_array($campo, $cols_base) && $campo !== 'id') {
                $valor_escaped = pg_escape_string($valor);
                $sets_base[] = "$campo = '$valor_escaped'";
            }
        }

        if (!empty($sets_base)) {
            $sql_base = "UPDATE core_sujetoverificacion SET "
                . implode(', ', $sets_base)
                . " WHERE id = '$id'";
            pg_query($sql_base) or die('Error base: ' . pg_last_error());
        }

        $sets_din = [];
        foreach ($datos as $campo => $valor) {
            if (in_array($campo, $cols_dinamica) && $campo !== 'id') {
                $valor_escaped = pg_escape_string($valor);
                $sets_din[] = "$campo = '$valor_escaped'";
            }
        }

        if (!empty($sets_din)) {
            $sql_din = "UPDATE $tabla_dinamica SET "
                . implode(', ', $sets_din)
                . " WHERE id = '$id'";
            pg_query($sql_din) or die('Error dinamica: ' . pg_last_error());
        }

        return true;
    }

    /**
        Get municipio,departamento,pais (CENSO) -> NICOLAS
     **/
    public function getMunicipioCenso()
    {
        $q = "SELECT a.id,
        a.nombre||', '||b.nombre||', '||c.nombre as nombre
        FROM plan_municipio a
        INNER JOIN plan_departamento b ON b.id = a.plan_departamento_id
        INNER JOIN plan_pais c ON c.id = b.pais_id
        ORDER BY a.nombre, b.nombre, c.nombre";
        $r = pg_query($q);
        return $r;
    }

    /**
        Crear Sujeto de Verificacion -> NICOLAS
     **/
    public function addSujeto($datos, $tipo_id)
    {
        $tipo_id = pg_escape_string($tipo_id);


        $query_tipo = "SELECT tabla FROM core_sujetoverificaciontipo WHERE id = '$tipo_id'";
        $result_tipo = pg_query($query_tipo) or die('La consulta fallo: ' . pg_last_error());
        $row_tipo = pg_fetch_assoc($result_tipo);
        if (!$row_tipo) return false;
        $tabla_dinamica = $row_tipo['tabla'];


        $cols_base = [];
        $r = pg_query("SELECT column_name FROM information_schema.columns WHERE table_name = 'core_sujetoverificacion'");
        while ($row = pg_fetch_assoc($r)) $cols_base[] = $row['column_name'];

        $tabla_din_escaped = pg_escape_string($tabla_dinamica);
        $cols_dinamica = [];
        $r2 = pg_query("SELECT column_name FROM information_schema.columns WHERE table_name = '$tabla_din_escaped'");
        while ($row = pg_fetch_assoc($r2)) $cols_dinamica[] = $row['column_name'];


        foreach ($datos as $campo => $valor) {
            if (in_array($campo, $cols_base) && $campo !== 'id') {
                $campos_insert[] = $campo;
                $valores_insert[] = "'" . pg_escape_string($valor) . "'";
            }
        }
        // Agregar el tipo SOLO si no viene ya en $datos
        if (!in_array('core_sujetoverificaciontipo_id', $campos_insert)) {
            $campos_insert[] = 'core_sujetoverificaciontipo_id';
            $valores_insert[] = "'$tipo_id'";
        }

        $sql_base = "INSERT INTO core_sujetoverificacion (" . implode(', ', $campos_insert) . ") 
                    VALUES (" . implode(', ', $valores_insert) . ") 
                    RETURNING id";

        $result_base = pg_query($sql_base) or die('Error insert base: ' . pg_last_error());
        $nuevo = pg_fetch_assoc($result_base);
        if (!$nuevo) return false;

        $nuevo_id = $nuevo['id'];


        $campos_din = ['id'];
        $valores_din = ["'$nuevo_id'"];
        foreach ($datos as $campo => $valor) {
            if (in_array($campo, $cols_dinamica) && $campo !== 'id') {
                $campos_din[] = $campo;
                $valores_din[] = "'" . pg_escape_string($valor) . "'";
            }
        }

        $sql_din = "INSERT INTO $tabla_dinamica (" . implode(', ', $campos_din) . ") 
                    VALUES (" . implode(', ', $valores_din) . ")";

        pg_query($sql_din) or die('Error insert dinamica: ' . pg_last_error());

        return $nuevo_id;
    }



    /**
     select wfsv campo -> Cristhian
     */
    public function getCampoWfsv($data)
    {
        $query = "SELECT id, nombre, tabla_datosdin AS tabla, campo_datosdin AS campo, mayusculas 
        FROM plan_atributo pa WHERE id = '{$data['id']}'";
        $result = pg_query($query) or die("error en la consulta" . preg_last_error());
        $rows = array();
        while ($row = pg_fetch_assoc($result)) {
            $tabla = $row["tabla"];
            $campo = $row["campo"];
            $qry = "SELECT {$campo} 
            FROM {$tabla} 
            WHERE id = '{$data['id_sujeto']}'";
            $res = pg_query($qry) or die("error en la consulta de tabla core_sujeto" . preg_last_error());

            while ($columna = pg_fetch_assoc($res)) {
                $rows[] = $columna;
            }
        }
        return $rows;
    }



    /**
     select plantilla documento -> Cristhian
     */

    public function getPlantillaProceso($proceso_id)
    {
        $query = "SELECT * FROM public.nova_documentosalida WHERE wf_proceso_id = '{$proceso_id}' AND for_docs != 'pdf'";
        $result = pg_query($query) or die("Error en la consulta" . preg_last_error());
        $rows = array();
        while ($row = pg_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     select todos los campos de un proceso -> Cristhian
     */

    public function getCamposProceso($id_proceso)
    {

        $query = "SELECT wp.id as id_proceso, wp.tit_proc, wp.pad_proc, wt.id AS id_tarea, wt.tit_tar, wpa.id AS id_paso, wpa.elem_paso, f.cod_formulario, f.nom_formulario, fb.cod_bloque, b.nom_bloque, c.cod_campo, c.nom_campo, c.cod_tipo_campo    FROM 
                    PUBLIC.wf_proceso wp 
                    INNER JOIN PUBLIC.wf_tarea wt ON wp.id = wt.wf_proceso_id AND wt.est_tar = 'a'
                    INNER JOIN PUBLIC.wf_paso wpa ON wt.id = wpa.wf_tarea_id
                    left JOIN sce.formulario f ON f.cod_formulario = wpa.elem_paso
                    left JOIN sce.formulario_bloque fb ON f.cod_formulario = fb.cod_formulario
                    left JOIN sce.bloque b ON fb.cod_bloque = b.cod_bloque
                    left JOIN sce.bloque_campo bc ON b.cod_bloque = bc.cod_bloque  
                    left JOIN sce.campo c ON bc.cod_campo = c.cod_campo
                    WHERE wp.id = '$id_proceso' order by id_paso";
        $result = pg_query($query) or die("Falló la consulta: " . pg_last_error());
        $data = array();

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                if (empty($data)) {
                    $data = array(
                        "id_proceso" => $row["id_proceso"],
                        "tareas" => array()
                    );
                }

                $id_tarea = $row["id_tarea"];
                $id_paso = $row["id_paso"];
                $cod_form = $row["cod_formulario"];
                $cod_bloque = $row["cod_bloque"];
                $cod_campo = $row["cod_campo"];

                if (!isset($data["tareas"][$id_tarea])) {
                    $data["tareas"][$id_tarea] = array(
                        "id_tarea" => $row["id_tarea"],
                        "tit_tarea" => $row["tit_tar"],
                        "pasos" => array()
                    );
                }

                if (!isset($data["tareas"][$id_tarea]["pasos"][$id_paso])) {
                    $data["tareas"][$id_tarea]["pasos"][$id_paso] = array(
                        "id_paso" => $row["id_paso"],
                        "elem_paso" => $row["elem_paso"],
                        "formularios" => array()
                    );
                }
                if (!isset($data["tareas"][$id_tarea]["pasos"][$id_paso]["formularios"][$cod_form])) {
                    $data["tareas"][$id_tarea]["pasos"][$id_paso]["formularios"][$cod_form] = array(
                        "cod_form" => $row["cod_formulario"],
                        "nom_formulario" => $row["nom_formulario"],
                        "bloques" => array()
                    );
                }

                if ($cod_bloque !== null) {
                    if (!isset($data["tareas"][$id_tarea]["pasos"][$id_paso]["formularios"][$cod_form]["bloques"][$cod_bloque])) {
                        $data["tareas"][$id_tarea]["pasos"][$id_paso]["formularios"][$cod_form]["bloques"][$cod_bloque] = array(
                            "cod_bloque" => $row["cod_bloque"],
                            "nom_bloque" => $row["nom_bloque"],
                            "campos" => array()
                        );
                    }

                    if ($cod_campo !== null) {
                        if (!isset($data["tareas"][$id_tarea]["pasos"][$id_paso]["formularios"][$cod_form]["bloques"][$cod_bloque]["campos"][$cod_campo])) {
                            $grilla = null;
                            if ($row["cod_tipo_campo"] === 'matrixd') {
                                $cod_form_grilla = $this->getFormGrid($cod_campo);
                                if ($cod_form_grilla !== null) {
                                    $columnas = $this->getEstructuraGrilla($cod_form_grilla);
                                    $grilla = array(
                                        "cod_formulario" => $cod_form_grilla,
                                        "columnas" => $columnas
                                    );
                                }
                            }

                            $data["tareas"][$id_tarea]["pasos"][$id_paso]["formularios"][$cod_form]["bloques"][$cod_bloque]["campos"][$cod_campo] = array(
                                "cod_campo" => $row["cod_campo"],
                                "nom_campo" => $row["nom_campo"],
                                "cod_tipo_campo" => $row["cod_tipo_campo"],
                                "grilla" => $grilla
                            );
                        }
                    }
                }
            }

            foreach ($data["tareas"] as &$tarea) {
                foreach ($tarea["pasos"] as &$paso) {
                    foreach ($paso["formularios"] as &$formulario) {
                        foreach ($formulario["bloques"] as &$bloque) {
                            $bloque["campos"] = array_values($bloque["campos"]);
                        }
                        unset($bloque);
                        $formulario["bloques"] = array_values($formulario["bloques"]);
                    }
                    unset($formulario);
                    $paso["formularios"] = array_values($paso["formularios"]);
                }
                unset($paso);
                $tarea["pasos"] = array_values($tarea["pasos"]);
            }
            unset($tarea);
            $data["tareas"] = array_values($data["tareas"]);
        }
        return $data;
    }

    /** 
    Select variables del sistema -> Cristhian 
     **/
    public function getVariableSistema()
    {
        $query = "SELECT * from public.plan_atributo order by nombre";
        $result = pg_query($query) or die("Falló la consulta: " . pg_last_error());
        $rows = [];

        while ($row = pg_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /** 
        Select a tabla tmp -> Cristhian
     **/
    public function getDatosTemp($usuario_id, $cod_form, $id_sujeto)
    {
        $query = " SELECT 
            id_registro,
            usuario_id,
            id_sujeto,
            id_tarea,
            id_proceso,
            id_paso,
            cod_form,
            cod_bloque,
            cod_campo,
            cod_tipo_campo,
            fila,   
            columna,
            cod_campo_hijo,
            cod_form_hijo,
            valor_campo,
            observaciones,
            fecha_registro,
            est_camp
        FROM sce.nova_datos_temp
        WHERE usuario_id = '{$usuario_id}'
          AND cod_form = '{$cod_form}'
          AND id_sujeto = '{$id_sujeto}'
        ORDER BY cod_bloque, cod_campo, fila, columna";

        $result = pg_query($query) or die('La consulta falló: ' . pg_last_error());

        $rows = [];
        while ($row = pg_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /** 
    update documentos salida -> Cristhian
     */
    public function updateDocumentoSalida($datos)
    {
        $query = " UPDATE nova_documentosalida 
        SET pla_docs = '{$datos['pla_docs']}' 
        WHERE id ='{$datos['id']}' ";
        $result = pg_query($query);
        $row = pg_affected_rows($result) or die('El update fallo: ' . pg_last_error());
        return $row;
    }

    /** 
    select documentos salida -> Cristhian
     */
    public function getDocumentosSalida()
    {
        $query = "
        SELECT ds.*, p.tit_proc FROM public.nova_documentosalida ds
        JOIN public.wf_proceso p 
        ON p.id = ds.wf_proceso_id";
        $result = pg_query($query) or die('El select fallo' . pg_last_error());
        $rows = [];
        while ($row = pg_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     update datos tmp -> Cristhian
     */
    public function updateDatosTemp($datos)
    {
        $query = "
                UPDATE sce.nova_datos_temp
                SET valor_campo = '" . pg_escape_string($datos['valor_campo']) . "'
                WHERE id_registro = '{$datos['id_registro']}' 
                AND usuario_id = '{$datos['usuario_id']}'
                AND id_sujeto = '{$datos['id_sujeto']}'
                AND id_tarea = '{$datos['id_tarea']}' 
                AND id_proceso = '{$datos['id_proceso']}'
                AND id_paso = '{$datos['id_paso']}' 
                AND cod_form = '{$datos['cod_form']}'
                AND cod_bloque = '{$datos['cod_bloque']}' 
                AND cod_campo = '{$datos['cod_campo']}'
                ";
        $result = pg_query($query) or die('El update fallo: ' . pg_last_error());
        $row = pg_affected_rows($result);
        return $row;
    }
    /**
        Insert datos a tabla tmp -> NICOLAS
     **/
    public function insertDatosTemp($datos)
    {
        $query = "
            INSERT INTO sce.nova_datos_temp 
            (usuario_id, id_sujeto, id_tarea, id_proceso, id_paso, cod_form, cod_bloque, cod_campo, 
            cod_tipo_campo, fila, columna, cod_campo_hijo, cod_form_hijo, 
            valor_campo, observaciones)
            VALUES (
                '{$datos['usuario_id']}', '{$datos['id_sujeto']}','{$datos['id_tarea']}','{$datos['id_proceso']}','{$datos['id_paso']}',
                '{$datos['cod_form']}', '{$datos['cod_bloque']}', '{$datos['cod_campo']}',
                '{$datos['cod_tipo_campo']}', 
                " . ($datos['fila'] === null ? 'NULL' : "'{$datos['fila']}'") . ",
                " . ($datos['columna'] === null ? 'NULL' : "'{$datos['columna']}'") . ",
                " . ($datos['cod_campo_hijo'] === null ? 'NULL' : "'{$datos['cod_campo_hijo']}'") . ",
                " . ($datos['cod_form_hijo'] === null ? 'NULL' : "'{$datos['cod_form_hijo']}'") . ",
                " . ($datos['valor_campo'] === null ? 'NULL' : "'" . pg_escape_string($datos['valor_campo']) . "'") . ",
                " . ($datos['observaciones'] === null ? 'NULL' : "'" . pg_escape_string($datos['observaciones']) . "'") . "
            )
            RETURNING id_registro
        ";
        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
        $row = pg_fetch_assoc($result);
        return $row['id_registro'];
    }

    /**
        Get campos del formulario por tipo de sujeto ->NICOLAS
     **/
    public function getCamposFormulario($id)
    {
        $id = pg_escape_string($id);

        $query_tabla = "SELECT tabla FROM core_sujetoverificaciontipo WHERE id = '$id'";
        $result_tabla = pg_query($query_tabla) or die('La consulta fallo: ' . pg_last_error());
        $row = pg_fetch_assoc($result_tabla);

        if (!$row) return false;

        $tabla = $row['tabla'];

        // Sin filtro en_grid para traer TODOS los campos del formulario
        $query = "SELECT nombre, campo_datosdin, obligatorio, plan_tipoatributo_id, parametros
                    FROM plan_atributo 
                    WHERE tabla = 'core_sujetoverificacion' 
                    AND tabla_datosdin = '$tabla'
                    ORDER BY id";

        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());

        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }

        return $data;
    }

    /**
        Eliminar sujeto del censo ->NICOLAS
     **/
    public function deleteSujeto($id)
    {
        $id = pg_escape_string($id);

        $query_tipo = "
            SELECT t.tabla 
            FROM core_sujetoverificacion s
            INNER JOIN core_sujetoverificaciontipo t ON t.id = s.core_sujetoverificaciontipo_id
            WHERE s.id = '$id'
        ";
        $result_tipo = pg_query($query_tipo) or die('La consulta fallo: ' . pg_last_error());
        $row_tipo = pg_fetch_assoc($result_tipo);

        if (!$row_tipo) return false;
        $tabla_dinamica = $row_tipo['tabla'];

        $sql_din = "DELETE FROM $tabla_dinamica WHERE id = '$id'";
        pg_query($sql_din) or die('Error eliminando tabla dinamica: ' . pg_last_error());


        $sql_base = "DELETE FROM core_sujetoverificacion WHERE id = '$id'";
        pg_query($sql_base) or die('Error eliminando tabla base: ' . pg_last_error());

        return true;
    }

    /**
        CALENDARIO ->NICOLAS
     **/
    public function getCalendarios($usuario_id)
    {
        $usuario_id = pg_escape_string($usuario_id);
        $query = "(SELECT id, nom_cal FROM calen_calendarios WHERE cal_tod = true
                UNION
                SELECT id, nom_cal FROM calen_calendarios WHERE cal_usu = '$usuario_id')
                ORDER BY id";
        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
        return $result;
    }

    /**
        CALENDARIO ->NICOLAS
     **/
    public function getEventos($usuario_id)
    {
        $usuario_id = pg_escape_string($usuario_id);
        $query = "SELECT calen_calendarios_id, cal_tip_eve, cal_tit,
                        cal_fec_ini, cal_fec_fin, cal_loc, cal_not,
                        cal_url, cal_rec, cal_id,
                        (CASE WHEN cal_dia='f' THEN 'false' ELSE 'true' END) as cal_dia,
                        (CASE WHEN cal_nue='f' THEN 'false' ELSE 'true' END) as cal_nue
                FROM calen_principal
                WHERE cal_usu = '$usuario_id'
                ORDER BY cal_id";
        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
        return $result;
    }

    /**
        CALENDARIO ->NICOLAS
     **/
    public function getTiposEvento()
    {
        $query = "SELECT cal_tip_eve_tit as titulo, cal_tip_eve_col as color 
                FROM calen_tipo_evento WHERE id > 0";
        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());
        return $result;
    }




    /**
        Get establecimientos postgres sql
     **/
    public function loginMovil($user, $pass = '')
    {
        $query = "SELECT u.*,p.nombre,p.apellido 
                FROM core_usuario u 
                INNER JOIN persona p ON u.persona_id = p.id
                WHERE nom_usu='$user' AND con_usu=MD5('$pass')";

        $result = pg_query($query);
        if (!$result) {
            die('Error en la consulta: ' . pg_last_error());
        }

        $fila = pg_fetch_array($result, null, PGSQL_ASSOC);
        if ($fila) {
            $queryVariables = "SELECT * FROM variables";
            $resultVariables = pg_query($queryVariables);

            if (!$resultVariables) {
                die('Error al obtener variables: ' . pg_last_error());
            }
            $variables = array();
            while ($variable = pg_fetch_array($resultVariables, null, PGSQL_ASSOC)) {
                $variables[] = $variable;
            }
            $fila['variables'] = $variables;
        }
        return $fila;
    }

    public function getCore_procesocategoria()
    {
        $q = "SELECT * FROM core_procesocategoria
            ORDER BY 1";
        $result = pg_query($q);
        $data = array();
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getCasosProcesos($ids_procesos, $core_usuario_id = '')
    {
        for ($i = 0; $i < count($ids_procesos); $i++) {
            $id_proceso = $ids_procesos[$i]['id'];
            $qValida = "SELECT 1 AS existe
                FROM information_schema.tables
                WHERE table_schema = 'public'
                AND table_name = 'wf_caso_$id_proceso';";
            $r_valida = pg_query($qValida);
            if (pg_num_rows($r_valida) > 0) {
                $q = "SELECT DISTINCT ON (core_sujetoverificacion_id)
                    id,
                    core_usuario_id,
                    core_sujetoverificacion_id,
                    con_caso,
                    fec_caso,
                    est_caso,
                    $id_proceso AS id_proceso
                    FROM wf_caso_$id_proceso
                    WHERE est_caso = 'tra' AND fec_caso > '2024-12-31' AND core_usuario_id=$core_usuario_id
                    ORDER BY core_sujetoverificacion_id, fec_caso DESC; ";
                $result = pg_query($q);
                while ($row = pg_fetch_assoc($result)) {
                    $data['wf_caso_movil'][] = $row;
                }
            }
        }
        $num1 = count($data['wf_caso_movil']);
        if ($num1 > 0) {
            for ($i = 0; $i < count($data['wf_caso_movil']); $i++) {
                $id_caso = $data['wf_caso_movil'][$i]['id'];
                $id_proceso = $data['wf_caso_movil'][$i]['id_proceso'];
                $q2 = "SELECT *,$id_proceso AS id_proceso FROM wf_caso_{$id_proceso}_seguimiento
                    WHERE wf_caso_{$id_proceso}_id='$id_caso';";
                $result2 = pg_query($q2);
                while ($row = pg_fetch_assoc($result2)) {
                    $data['wf_caso_movil_seguimiento'][] = $row;
                }
            }
        }
        $num2 = count($data['wf_caso_movil_seguimiento']);
        if ($num2 > 0) {
            for ($i = 0; $i < count($data['wf_caso_movil_seguimiento']); $i++) {
                $id_caso_seguimiento = $data['wf_caso_movil_seguimiento'][$i]['id'];
                $id_proceso = $data['wf_caso_movil_seguimiento'][$i]['id_proceso'];
                $q3 = "SELECT *,$id_proceso AS id_proceso 
                    FROM wf_caso_{$id_proceso}_pasos
                    WHERE wf_caso_{$id_proceso}_seguimiento_id = '$id_caso_seguimiento'";
                $result3 = pg_query($q3);
                while ($row = pg_fetch_assoc($result3)) {
                    $data['wf_caso_movil_pasos'][] = $row;
                }
            }
        }
        return $data;
    }

    public function get_wf_proceso()
    {
        $q = "SELECT * FROM wf_proceso
            WHERE est_proc='a' AND pad_proc<>0
            ORDER BY 1";
        $result = pg_query($q);
        $data = array();
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getMenu($core_usuario_id, $padre = 0)
    {

        $q = "SELECT c.*
                FROM nova_componente c
                INNER JOIN nova_usuariocomponente cu 
                    ON c.id = cu.core_componente_id
                WHERE cu.core_usuario_id = $core_usuario_id
                AND c.pad_com = $padre
                ORDER BY c.tit_com";

        $result = pg_query($q);
        $data = array();

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {

                // Buscar hijos del nivel actual
                $hijos = $this->getMenu($core_usuario_id, $row['id']);

                if (!empty($hijos)) {
                    $row['hijos'] = $hijos;
                }

                $data[] = $row;
            }
        }

        return $data;
    }

    /**
	    Harold
     **/
    public function listarReportes()
    {
        $sql = "SELECT 
                    id_rpt,
                    des_rpt,
                    sql_rpt,
                    est_rpt,
                    id_pla,
                    destino
                FROM nova_rpt_reporte
                ORDER BY des_rpt ASC";

        $result = pg_query($sql);

        if (!$result) {
            return [
                "error" => true,
                "message" => pg_last_error()
            ];
        }

        $data = [];

        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }

        return [
            "error" => false,
            "data" => $data
        ];
    }

    /**
	    Harold
     **/

    public function actualizarDestino($id, $destino)
    {
        if (empty($destino)) {
            return [
                "error" => true,
                "message" => "Destino requerido"
            ];
        }

        $sql = "UPDATE nova_rpt_reporte
                SET destino = $1
                WHERE id_rpt = $2";

        $result = pg_query_params($sql, [$destino, $id]);

        if (!$result) {
            return [
                "error" => true,
                "message" => pg_last_error()
            ];
        }

        return [
            "error" => false,
            "message" => "Destino actualizado correctamente"
        ];
    }

    /**
	    Harold
     **/

    public function actualizarEstado($id, $estado)
    {
        if (!in_array($estado, ['a', 'i'])) {
            return [
                "error" => true,
                "message" => "Estado inválido"
            ];
        }

        $sql = "UPDATE nova_rpt_reporte
                SET est_rpt = $1
                WHERE id_rpt = $2";

        $result = pg_query_params($sql, [$estado, $id]);

        if (!$result) {
            return [
                "error" => true,
                "message" => pg_last_error()
            ];
        }

        return [
            "error" => false,
            "message" => "Estado actualizado correctamente"
        ];
    }
    /**
	    Harold
     **/

    public function actualizarReporteNova($datos)
    {
        $sql = "UPDATE nova_rpt_reporte SET
                des_rpt = $1,
                sql_rpt = $2,
                est_rpt = $3,
                destino = $4,
                id_men = $5,
                id_ano = $6,
                id_pla = $7,
                difusion = $8
            WHERE id_rpt = $9";

        $params = [
            $datos['des_rpt'] ?? null,
            $datos['sql_rpt'] ?? null,
            $datos['est_rpt'] ?? 'a',
            $datos['destino'] ?? null,
            $datos['id_men'] ?? null,
            $datos['id_ano'] ?? null,
            $datos['id_pla'] ?? null,
            $datos['difusion'] ?? null,
            $datos['id_rpt']
        ];

        $result = pg_query_params($sql, $params);

        if (!$result) {
            return ["error" => true, "message" => pg_last_error()];
        }

        return ["error" => false, "message" => "Reporte actualizado correctamente"];
    }

    /**
	    Harold
     **/
    public function guardarReporteNova($datos)
    {
        $id_men = !empty($datos['id_men']) ? $datos['id_men'] : null;
        $id_ano = !empty($datos['id_ano']) ? $datos['id_ano'] : null;
        $id_pla = !empty($datos['id_pla']) ? $datos['id_pla'] : null;

        $sql = "INSERT INTO nova_rpt_reporte
            (
                des_rpt,
                sql_rpt,
                est_rpt,
                id_men,
                id_ano,
                difusion,
                id_pla,
                destino
            )
            VALUES
            (
                $1, $2, $3, $4, $5, $6, $7, $8
            )
            RETURNING id_rpt";

        $params = [
            $datos['des_rpt'],
            $datos['sql_rpt'] ?? null,
            $datos['est_rpt'] ?? 'a',
            $id_men,
            $id_ano,
            $datos['difusion'] ?? null,
            $id_pla,
            $datos['destino'] ?? 'i'
        ];

        $result = pg_query_params($sql, $params);

        if (!$result) {
            return [
                "error" => true,
                "message" => pg_last_error()
            ];
        }

        $row = pg_fetch_assoc($result);

        return [
            "error" => false,
            "message" => "Reporte guardado correctamente",
            "id_rpt" => $row['id_rpt']
        ];
    }
    /**
	    Harold
     **/

    public function getParametrosReporteNova($id_rpt)
    {
        $sql = "SELECT 
                id_rpar,
                id_rpt,
                des_rpar,
                tip_rpar,
                est_rpar
            FROM nova_rpt_reporteparametro
            WHERE id_rpt = $1
            ORDER BY id_rpar ASC";

        $result = pg_query_params($sql, [$id_rpt]);

        if (!$result) {
            return ["error" => true, "message" => pg_last_error(), "data" => []];
        }

        $data = [];

        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }

        return ["error" => false, "data" => $data];
    }

    /**
	    Harold
     **/

    public function guardarParametroReporteNova($datos)
    {
        $sql = "INSERT INTO nova_rpt_reporteparametro
                (id_rpt, des_rpar, tip_rpar, est_rpar)
            VALUES
                ($1, $2, $3, $4)
            RETURNING id_rpar";

        $params = [
            $datos['id_rpt'],
            $datos['des_rpar'] ?? null,
            $datos['tip_rpar'] ?? 'text',
            $datos['est_rpar'] ?? 'a'
        ];

        $result = pg_query_params($sql, $params);

        if (!$result) {
            return ["error" => true, "message" => pg_last_error()];
        }

        $row = pg_fetch_assoc($result);

        return [
            "error" => false,
            "message" => "Parámetro guardado correctamente",
            "id_rpar" => $row['id_rpar']
        ];
    }

    /**
	    Harold
     **/

    public function actualizarParametroReporteNova($datos)
    {
        $sql = "UPDATE nova_rpt_reporteparametro SET
                des_rpar = $1,
                tip_rpar = $2,
                est_rpar = $3
            WHERE id_rpar = $4";

        $params = [
            $datos['des_rpar'] ?? null,
            $datos['tip_rpar'] ?? 'text',
            $datos['est_rpar'] ?? 'a',
            $datos['id_rpar']
        ];

        $result = pg_query_params($sql, $params);

        if (!$result) {
            return ["error" => true, "message" => pg_last_error()];
        }

        return ["error" => false, "message" => "Parámetro actualizado correctamente"];
    }

    /**
	    Harold
     **/

    public function eliminarParametroReporteNova($id_rpar)
    {
        $sql = "DELETE FROM nova_rpt_reporteparametro
            WHERE id_rpar = $1";

        $result = pg_query_params($sql, [$id_rpar]);

        if (!$result) {
            return ["error" => true, "message" => pg_last_error()];
        }

        return ["error" => false, "message" => "Parámetro eliminado correctamente"];
    }

    /**
	    Harold
     **/

    /**
    Harold
     **/
    public function getValoresParametroNova($id_rpar)
    {
        $sql = "SELECT 
                id_rpv,
                id_rpar,
                val_rpv,
                tit_rpv,
                var_rpv,
                sql_rpv
            FROM nova_rpt_reporteparametrovalor
            WHERE id_rpar = $1
            ORDER BY id_rpv ASC";

        $result = pg_query_params($sql, [$id_rpar]);

        if (!$result) {
            return ["error" => true, "message" => pg_last_error(), "data" => []];
        }

        $data = [];

        while ($row = pg_fetch_assoc($result)) {

            if (!empty($row['sql_rpv'])) {

                $sqlCombo = trim($row['sql_rpv']);

                if (stripos($sqlCombo, 'select') !== 0) {
                    return [
                        "error" => true,
                        "message" => "El SQL del parámetro solo puede ser SELECT",
                        "data" => []
                    ];
                }

                $resultCombo = pg_query($sqlCombo);

                if (!$resultCombo) {
                    return [
                        "error" => true,
                        "message" => pg_last_error(),
                        "data" => []
                    ];
                }

                while ($item = pg_fetch_assoc($resultCombo)) {
                    $data[] = [
                        "id_rpv" => $item['value'],
                        "val_rpv" => $item['value'],
                        "tit_rpv" => $item['label'],
                        "var_rpv" => $row['var_rpv']
                    ];
                }
            } else {
                $data[] = $row;
            }
        }

        return ["error" => false, "data" => $data];
    }

    /**
	    Harold
     **/

    public function guardarValorParametroNova($datos)
    {
        $sql = "INSERT INTO nova_rpt_reporteparametrovalor
                (id_rpar, val_rpv, tit_rpv, var_rpv, sql_rpv)
            VALUES
                ($1, $2, $3, $4, $5)
            RETURNING id_rpv";

        $params = [
            $datos['id_rpar'],
            $datos['val_rpv'] ?? null,
            $datos['tit_rpv'] ?? null,
            $datos['var_rpv'] ?? null,
            $datos['sql_rpv'] ?? null
        ];

        $result = pg_query_params($sql, $params);

        if (!$result) {
            return ["error" => true, "message" => pg_last_error()];
        }

        $row = pg_fetch_assoc($result);

        return [
            "error" => false,
            "message" => "Valor guardado correctamente",
            "id_rpv" => $row['id_rpv']
        ];
    }

    /**
	    Harold
     **/

    public function actualizarValorParametroNova($datos)
    {
        $sql = "UPDATE nova_rpt_reporteparametrovalor SET
                val_rpv = $1,
                tit_rpv = $2,
                var_rpv = $3,
                sql_rpv = $4
            WHERE id_rpv = $5";

        $params = [
            $datos['val_rpv'] ?? null,
            $datos['tit_rpv'] ?? null,
            $datos['var_rpv'] ?? null,
            $datos['sql_rpv'] ?? null,
            $datos['id_rpv']
        ];

        $result = pg_query_params($sql, $params);

        if (!$result) {
            return ["error" => true, "message" => pg_last_error()];
        }

        return ["error" => false, "message" => "Valor actualizado correctamente"];
    }

    /**
	    Harold
     **/

    public function eliminarValorParametroNova($id_rpv)
    {
        $sql = "DELETE FROM nova_rpt_reporteparametrovalor
            WHERE id_rpv = $1";

        $result = pg_query_params($sql, [$id_rpv]);

        if (!$result) {
            return ["error" => true, "message" => pg_last_error()];
        }

        return ["error" => false, "message" => "Valor eliminado correctamente"];
    }



    /**
    Harold - Generar reporte Nova dinámico
     **/
    public function generarReporteNova($id_rpt, $filtros = [])
    {
        $sqlReporte = "SELECT 
                    id_rpt,
                    des_rpt,
                    sql_rpt,
                    tp_gr,
                    eje_x,
                    eje_y,
                    destino
                FROM nova_rpt_reporte
                WHERE id_rpt = $1
                AND est_rpt = 'a'";

        $resultReporte = pg_query_params($sqlReporte, [$id_rpt]);

        if (!$resultReporte) {
            return [
                "error" => true,
                "message" => pg_last_error(),
                "data" => []
            ];
        }

        $reporte = pg_fetch_assoc($resultReporte);

        if (!$reporte) {
            return [
                "error" => true,
                "message" => "Reporte no encontrado o inactivo",
                "data" => []
            ];
        }

        if (empty($reporte['sql_rpt'])) {
            return [
                "error" => true,
                "message" => "El reporte no tiene SQL configurado",
                "data" => []
            ];
        }

        $sql = $reporte['sql_rpt'];

        // Obtener variables permitidas desde los parámetros configurados
        $sqlVariables = "SELECT DISTINCT v.var_rpv
                    FROM nova_rpt_reporteparametro p
                    INNER JOIN nova_rpt_reporteparametrovalor v 
                        ON v.id_rpar = p.id_rpar
                    WHERE p.id_rpt = $1
                    AND p.est_rpar = 'a'
                    AND v.var_rpv IS NOT NULL
                    AND v.var_rpv <> ''";

        $resultVariables = pg_query_params($sqlVariables, [$id_rpt]);

        if (!$resultVariables) {
            return [
                "error" => true,
                "message" => pg_last_error(),
                "data" => []
            ];
        }

        while ($var = pg_fetch_assoc($resultVariables)) {
            $nombreVariable = $var['var_rpv'];
            $valor = isset($filtros[$nombreVariable]) ? $filtros[$nombreVariable] : null;

            if ($valor === null || $valor === '') {
                $sql = str_replace(':' . $nombreVariable, 'NULL', $sql);
            } else {
                $sql = str_replace(
                    ':' . $nombreVariable,
                    "'" . pg_escape_string($valor) . "'",
                    $sql
                );
            }
        }

        // Seguridad básica: solo permitir SELECT
        $sqlLimpio = trim(strtolower($sql));

        if (substr($sqlLimpio, 0, 6) !== 'select') {
            return [
                "error" => true,
                "message" => "Solo se permiten consultas SELECT en los reportes",
                "data" => []
            ];
        }

        $resultDatos = pg_query($sql);

        if (!$resultDatos) {
            return [
                "error" => true,
                "message" => pg_last_error(),
                "sql" => $sql,
                "data" => []
            ];
        }

        $datos = [];
        while ($row = pg_fetch_assoc($resultDatos)) {
            $datos[] = $row;
        }

        $columnas = [];
        if (!empty($datos)) {
            $columnas = array_keys($datos[0]);
        }

        return [
            "error" => false,
            "message" => "Reporte generado correctamente",
            "reporte" => [
                "id_rpt" => $reporte['id_rpt'],
                "des_rpt" => $reporte['des_rpt'],
                "tp_gr" => $reporte['tp_gr'],
                "eje_x" => $reporte['eje_x'],
                "eje_y" => $reporte['eje_y'],
                "destino" => $reporte['destino']
            ],
            "columnas" => $columnas,
            "data" => $datos
        ];
    }





    public function get_wf_proceso_sujeto($id_sujeto = '')
    {
        $q = "SELECT pc.proceso_id,p.tit_proc
                    FROM core_procesocategoria pc
                    JOIN wf_proceso p ON p.id=pc.proceso_id
                    WHERE est_proc='a' AND categorias LIKE '%'||(
                    SELECT core_sujetocategoria_id:: TEXT
                    FROM core_sujetoverificacion
                    WHERE id=$id_sujeto)||'%'";
        $result = pg_query($q);
        $data = array();
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }


    private function getFormGrid($cod_campo)
    {
        $q = "SELECT pv.valor AS cod_formulario_grilla
            FROM sce.propiedad_valor pv
            JOIN sce.campo c ON pv.cod_campo = c.cod_campo
            WHERE c.cod_campo = " . $cod_campo . " 
            AND pv.cod_propiedad = 'form_grid'
            LIMIT 1;";

        $result = pg_query($q);
        if ($result && pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            return $row["cod_formulario_grilla"];
        }

        return null;
    }

    private function getEstructuraGrilla($cod_formulario)
    {
        $q = "SELECT c.*, ic.valor_item, ic.nom_item
            FROM sce.formulario f
            JOIN sce.formulario_bloque fb ON f.cod_formulario = fb.cod_formulario
            JOIN sce.bloque b             ON fb.cod_bloque = b.cod_bloque
            JOIN sce.bloque_campo bc      ON b.cod_bloque = bc.cod_bloque
            JOIN sce.campo c              ON bc.cod_campo = c.cod_campo
            LEFT JOIN sce.item_campo ic   ON c.cod_campo = ic.cod_campo
            WHERE f.cod_formulario = " . $cod_formulario . "
            ORDER BY bc.orden_campo ASC";

        $result = pg_query($q);
        $columnas = [];

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $cod_campo = $row['cod_campo'];

                if (!isset($columnas[$cod_campo])) {
                    $columnas[$cod_campo] = [
                        "cod_campo"      => $row["cod_campo"],
                        "nom_campo"      => $row["nom_campo"],
                        "cod_tipo_campo" => $row["cod_tipo_campo"],
                        "items"          => []
                    ];
                }

                if (!empty($row["valor_item"])) {
                    $columnas[$cod_campo]["items"][] = [
                        "valor_item" => $row["valor_item"],
                        "nom_item"   => $row["nom_item"]
                    ];
                }
            }
        }

        return array_values($columnas);
    }

    public function getFormularioInicial($proceso_id)
    {

        $q = "SELECT 
                p.id AS id_proceso, t.id AS id_tarea, tit_proc, tit_tar,
                pa.id AS id_paso, pa.elem_paso,
                f.cod_formulario, f.nom_formulario,
                b.nom_bloque, b.cod_bloque,
                c.*,
                ic.valor_item, ic.nom_item,
                tc.nom_tipo_campo, tc.items,
                pv.valor AS tam_campo,
                bc.orden_campo,
                c_hijo.cod_campo  AS cod_campo_hijo,
                c_hijo.nom_campo  AS nom_campo_hijo,
                c_hijo.obs_campo  AS consecutivo_hijo
                FROM public.wf_proceso p
                INNER JOIN public.wf_tarea t        ON t.wf_proceso_id = p.id
                INNER JOIN public.wf_paso pa        ON pa.wf_tarea_id = t.id
                INNER JOIN sce.formulario f         ON f.cod_formulario = pa.elem_paso
                INNER JOIN sce.formulario_bloque fb ON fb.cod_formulario = f.cod_formulario
                INNER JOIN sce.bloque b             ON b.cod_bloque = fb.cod_bloque
                INNER JOIN sce.bloque_campo bc      ON bc.cod_bloque = b.cod_bloque
                INNER JOIN sce.campo c              ON c.cod_campo = bc.cod_campo
                INNER JOIN sce.tipo_campo tc        ON tc.cod_tipo_campo = c.cod_tipo_campo
                LEFT  JOIN sce.propiedad_valor pv   ON pv.cod_campo = c.cod_campo 
                                                    AND pv.cod_propiedad = 'size'
                left JOIN sce.item_campo ic        ON c.cod_campo = ic.cod_campo
                LEFT  JOIN sce.propiedad_valor pv_hijo ON pv_hijo.valor = c.cod_campo  
                                                    AND pv_hijo.cod_propiedad = 'parent'
                LEFT  JOIN sce.campo c_hijo         ON c_hijo.cod_campo = pv_hijo.cod_campo
                WHERE p.id=" . $proceso_id . " AND tip_tar = 'i'
                ORDER BY b.cod_bloque, bc.orden_campo, consecutivo_hijo;";

        $result = pg_query($q);
        $data   = array();

        if ($result) {

            while ($row = pg_fetch_assoc($result)) {


                if (empty($data)) {
                    $data = array(
                        "id_proceso"     => $row["id_proceso"],
                        "tit_proc"       => $row["tit_proc"],
                        "id_tarea"       => $row["id_tarea"],
                        "tit_tar"        => $row["tit_tar"],
                        "id_paso"        => $row["id_paso"],
                        "cod_formulario" => $row["cod_formulario"],
                        "nom_formulario" => $row["nom_formulario"],
                        "bloques"        => array()
                    );
                }

                $cod_bloque = $row["cod_bloque"];
                $cod_campo  = $row["cod_campo"];


                if (!isset($data["bloques"][$cod_bloque])) {
                    $data["bloques"][$cod_bloque] = array(
                        "cod_bloque" => $row["cod_bloque"],
                        "nom_bloque" => $row["nom_bloque"],
                        "campos"     => array()
                    );
                }


                if (!isset($data["bloques"][$cod_bloque]["campos"][$cod_campo])) {
                    $grilla = null;
                    if ($row["cod_tipo_campo"] === 'matrixd') {
                        $cod_form_grilla = $this->getFormGrid($cod_campo);
                        if ($cod_form_grilla !== null) {
                            $columnas = $this->getEstructuraGrilla($cod_form_grilla);
                            $grilla = array(
                                "cod_formulario" => $cod_form_grilla,
                                "columnas"       => $columnas
                            );
                        }
                    }
                    //print_r($grilla);exit;
                    $data["bloques"][$cod_bloque]["campos"][$cod_campo] = array(
                        "cod_campo"      => $row["cod_campo"],
                        "orden_campo"    => $row["orden_campo"],
                        "nom_campo"      => $row["nom_campo"],
                        "nom_tipo_campo" => $row["nom_tipo_campo"],
                        "cod_tipo_campo" => $row["cod_tipo_campo"],
                        "obligatorio"    => $row["obligatorio"],
                        "tam_campo"      => $row["tam_campo"],
                        "items"          => array(),
                        "hijos"          => array(),
                        "grilla"         => $grilla
                    );
                }


                if (!empty($row["valor_item"])) {


                    $ya_existe = false;
                    foreach ($data["bloques"][$cod_bloque]["campos"][$cod_campo]["items"] as $it) {
                        if ($it["valor_item"] === $row["valor_item"]) {
                            $ya_existe = true;
                            break;
                        }
                    }

                    if (!$ya_existe) {
                        $data["bloques"][$cod_bloque]["campos"][$cod_campo]["items"][] = array(
                            "valor_item" => $row["valor_item"],
                            "nom_item"   => $row["nom_item"]
                        );
                    }
                }


                if (!empty($row["cod_campo_hijo"])) {
                    $cod_hijo = $row["cod_campo_hijo"];


                    if (!isset($data["bloques"][$cod_bloque]["campos"][$cod_campo]["hijos"][$cod_hijo])) {
                        $data["bloques"][$cod_bloque]["campos"][$cod_campo]["hijos"][$cod_hijo] = array(
                            "cod_campo"    => $cod_hijo,
                            "nom_campo"    => $row["nom_campo_hijo"],
                            "consecutivo"  => $row["consecutivo_hijo"]
                        );
                    }
                }
            }


            foreach ($data["bloques"] as &$bloque) {
                foreach ($bloque["campos"] as &$campo) {
                    $campo["hijos"] = array_values($campo["hijos"]);
                }
                unset($campo);
                $bloque["campos"] = array_values($bloque["campos"]);
            }
            unset($bloque);

            $data["bloques"] = array_values($data["bloques"]);
        }

        return $data;
    }

    /**
	    NICOLAS
     **/
    /* public function getSiguienteFormulario($paso)
    {
        
        $q = "SELECT 
                p.id AS id_proceso, t.id AS id_tarea, tit_proc, tit_tar,
                pa.id AS id_paso, pa.elem_paso,
                f.cod_formulario, f.nom_formulario,
                b.nom_bloque, b.cod_bloque,
                c.*,
                ic.valor_item, ic.nom_item,
                tc.nom_tipo_campo, tc.items,
                pv.valor AS tam_campo,
                bc.orden_campo,
                c_hijo.cod_campo  AS cod_campo_hijo,
                c_hijo.nom_campo  AS nom_campo_hijo,
                c_hijo.obs_campo  AS consecutivo_hijo
                FROM public.wf_proceso p
                INNER JOIN public.wf_tarea t        ON t.wf_proceso_id = p.id
                INNER JOIN public.wf_paso pa        ON pa.wf_tarea_id = t.id
                INNER JOIN sce.formulario f         ON f.cod_formulario = pa.elem_paso
                INNER JOIN sce.formulario_bloque fb ON fb.cod_formulario = f.cod_formulario
                INNER JOIN sce.bloque b             ON b.cod_bloque = fb.cod_bloque
                INNER JOIN sce.bloque_campo bc      ON bc.cod_bloque = b.cod_bloque
                INNER JOIN sce.campo c              ON c.cod_campo = bc.cod_campo
                INNER JOIN sce.tipo_campo tc        ON tc.cod_tipo_campo = c.cod_tipo_campo
                LEFT  JOIN sce.propiedad_valor pv   ON pv.cod_campo = c.cod_campo 
                                                    AND pv.cod_propiedad = 'size'
                left JOIN sce.item_campo ic        ON c.cod_campo = ic.cod_campo
                LEFT  JOIN sce.propiedad_valor pv_hijo ON pv_hijo.valor = c.cod_campo  
                                                    AND pv_hijo.cod_propiedad = 'parent'
                LEFT  JOIN sce.campo c_hijo         ON c_hijo.cod_campo = pv_hijo.cod_campo
                WHERE p.id=" . $proceso_id . " AND tip_tar = 'i'
                ORDER BY b.cod_bloque, bc.orden_campo, consecutivo_hijo;";

        $result = pg_query($q);
        $data   = array();

        if ($result) {

            while ($row = pg_fetch_assoc($result)) {

            
                if (empty($data)) {
                    $data = array(
                        "id_proceso"     => $row["id_proceso"],
                        "tit_proc"       => $row["tit_proc"],
                        "id_tarea"       => $row["id_tarea"],
                        "tit_tar"        => $row["tit_tar"],
                        "id_paso"        => $row["id_paso"],
                        "cod_formulario" => $row["cod_formulario"],
                        "nom_formulario" => $row["nom_formulario"],
                        "bloques"        => array()
                    );
                }

                $cod_bloque = $row["cod_bloque"];
                $cod_campo  = $row["cod_campo"];

                
                if (!isset($data["bloques"][$cod_bloque])) {
                    $data["bloques"][$cod_bloque] = array(
                        "cod_bloque" => $row["cod_bloque"],
                        "nom_bloque" => $row["nom_bloque"],
                        "campos"     => array()
                    );
                }

                

                
                if (!isset($data["bloques"][$cod_bloque]["campos"][$cod_campo])) {
                    $grilla = null;
                    if ($row["cod_tipo_campo"] === 'matrixd') {
                        $cod_form_grilla = $this->getFormGrid($cod_campo);
                        if ($cod_form_grilla !== null) {
                            $columnas = $this->getEstructuraGrilla($cod_form_grilla);
                            $grilla = array(
                                "cod_formulario" => $cod_form_grilla,
                                "columnas"       => $columnas
                            );
                        }
                    }
                    //print_r($grilla);exit;
                    $data["bloques"][$cod_bloque]["campos"][$cod_campo] = array(
                        "cod_campo"      => $row["cod_campo"],
                        "orden_campo"    => $row["orden_campo"],
                        "nom_campo"      => $row["nom_campo"],
                        "nom_tipo_campo" => $row["nom_tipo_campo"],
                        "cod_tipo_campo" => $row["cod_tipo_campo"],
                        "tam_campo"      => $row["tam_campo"],
                        "items"          => array(),
                        "hijos"          => array(),
                        "grilla"         => $grilla
                    );
                }

                
                if (!empty($row["valor_item"])) {

                    
                    $ya_existe = false;
                    foreach ($data["bloques"][$cod_bloque]["campos"][$cod_campo]["items"] as $it) {
                        if ($it["valor_item"] === $row["valor_item"]) {
                            $ya_existe = true;
                            break;
                        }
                    }

                    if (!$ya_existe) {
                        $data["bloques"][$cod_bloque]["campos"][$cod_campo]["items"][] = array(
                            "valor_item" => $row["valor_item"],
                            "nom_item"   => $row["nom_item"]
                        );
                    }
                }

                
                if (!empty($row["cod_campo_hijo"])) {
                    $cod_hijo = $row["cod_campo_hijo"];

                    
                    if (!isset($data["bloques"][$cod_bloque]["campos"][$cod_campo]["hijos"][$cod_hijo])) {
                        $data["bloques"][$cod_bloque]["campos"][$cod_campo]["hijos"][$cod_hijo] = array(
                            "cod_campo"    => $cod_hijo,
                            "nom_campo"    => $row["nom_campo_hijo"],
                            "consecutivo"  => $row["consecutivo_hijo"]
                        );
                    }
                }
            }

            
            foreach ($data["bloques"] as &$bloque) {
                foreach ($bloque["campos"] as &$campo) {
                    $campo["hijos"] = array_values($campo["hijos"]);  
                }
                unset($campo);
                $bloque["campos"] = array_values($bloque["campos"]);
            }
            unset($bloque);

            $data["bloques"] = array_values($data["bloques"]);
        }

        return $data;
    } */


    /**
	    SIGUIENTE FORMULARIO.
     **/
    public function getSiguienteFormulario($proceso_id, $tarea_id, $cod_formulario_actual, $respuestas)
    {
        $proceso_id          = pg_escape_string($proceso_id);
        $tarea_id            = pg_escape_string($tarea_id);
        $cod_formulario_actual = pg_escape_string($cod_formulario_actual);

        $sql_paso = "
        SELECT
            r.cond_prd AS condicion,
            p2.elem_paso AS cod_formulario_siguiente,
            p2.id AS paso_siguiente_id,
            p2.wf_tarea_id AS tarea_siguiente_id,
            r.ord_prd AS orden
        FROM wf_pasoregladerivacion r
        JOIN wf_paso p1 ON p1.id = r.wf_paso_id
            AND p1.elem_paso = '$cod_formulario_actual'
            AND p1.tipo_paso = 'form'
        JOIN wf_paso p2 ON p2.id = r.wf_paso_id_sig
            AND p2.tipo_paso = 'form'
        WHERE r.est_prd = 'a'
        ORDER BY r.ord_prd ASC
    ";
        $result_paso = pg_query($sql_paso);
        $reglas_paso = [];
        while ($row = pg_fetch_assoc($result_paso)) {
            $reglas_paso[] = $row;
        }

        if (!empty($reglas_paso)) {
            $resultado = $this->evaluarReglas($reglas_paso, $respuestas);
            if ($resultado) return $resultado;
        }

        $sql_tarea = "
        SELECT
            r.cond_trd AS condicion,
            p2.elem_paso AS cod_formulario_siguiente,
            p2.id AS paso_siguiente_id,
            r.wf_tarea_id_sig AS tarea_siguiente_id,
            r.ord_trd AS orden
        FROM wf_tarearegladerivacion r
        JOIN wf_paso p1 ON p1.wf_tarea_id = r.wf_tarea_id
            AND p1.elem_paso = '$cod_formulario_actual'
            AND p1.tipo_paso = 'form'
        JOIN wf_paso p2 ON p2.wf_tarea_id = r.wf_tarea_id_sig
            AND p2.tipo_paso = 'form'
        WHERE r.wf_tarea_id = '$tarea_id'
            AND r.est_trd = 'a'
        ORDER BY r.ord_trd ASC
    ";
        $result_tarea = pg_query($sql_tarea);
        $reglas_tarea = [];
        while ($row = pg_fetch_assoc($result_tarea)) {
            $reglas_tarea[] = $row;
        }

        if (!empty($reglas_tarea)) {
            $resultado = $this->evaluarReglas($reglas_tarea, $respuestas);
            if ($resultado) return $resultado;
        }

        return $this->getSiguientePorTipTar($proceso_id, $tarea_id, $cod_formulario_actual);
    }

    /**
  Obtener formulario completo por código →NICOLAS
     **/
    public function getFormularioCompleto($proceso_id, $cod_formulario)
    {
        $proceso_id     = pg_escape_string($proceso_id);
        $cod_formulario = pg_escape_string($cod_formulario);

        $q = "SELECT 
            p.id AS id_proceso, t.id AS id_tarea, tit_proc, tit_tar,
            pa.id AS id_paso, pa.elem_paso,
            f.cod_formulario, f.nom_formulario,
            b.nom_bloque, b.cod_bloque,
            c.*,
            ic.valor_item, ic.nom_item,
            tc.nom_tipo_campo, tc.items,
            pv.valor AS tam_campo,
            bc.orden_campo,
            c_hijo.cod_campo  AS cod_campo_hijo,
            c_hijo.nom_campo  AS nom_campo_hijo,
            c_hijo.obs_campo  AS consecutivo_hijo
            FROM public.wf_proceso p
            INNER JOIN public.wf_tarea t        ON t.wf_proceso_id = p.id
            INNER JOIN public.wf_paso pa        ON pa.wf_tarea_id = t.id
            INNER JOIN sce.formulario f         ON f.cod_formulario = pa.elem_paso
            INNER JOIN sce.formulario_bloque fb ON fb.cod_formulario = f.cod_formulario
            INNER JOIN sce.bloque b             ON b.cod_bloque = fb.cod_bloque
            INNER JOIN sce.bloque_campo bc      ON bc.cod_bloque = b.cod_bloque
            INNER JOIN sce.campo c              ON c.cod_campo = bc.cod_campo
            INNER JOIN sce.tipo_campo tc        ON tc.cod_tipo_campo = c.cod_tipo_campo
            LEFT  JOIN sce.propiedad_valor pv   ON pv.cod_campo = c.cod_campo 
                                                AND pv.cod_propiedad = 'size'
            LEFT  JOIN sce.item_campo ic        ON c.cod_campo = ic.cod_campo
            LEFT  JOIN sce.propiedad_valor pv_hijo ON pv_hijo.valor = c.cod_campo  
                                                AND pv_hijo.cod_propiedad = 'parent'
            LEFT  JOIN sce.campo c_hijo         ON c_hijo.cod_campo = pv_hijo.cod_campo
            WHERE p.id = $proceso_id
              AND pa.elem_paso = $cod_formulario
              AND pa.tipo_paso = 'form'
            ORDER BY b.cod_bloque, bc.orden_campo, consecutivo_hijo;";

        $result = pg_query($q);
        $data   = array();

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {

                if (empty($data)) {
                    $data = array(
                        "id_proceso"     => $row["id_proceso"],
                        "tit_proc"       => $row["tit_proc"],
                        "id_tarea"       => $row["id_tarea"],
                        "tit_tar"        => $row["tit_tar"],
                        "id_paso"        => $row["id_paso"],
                        "cod_formulario" => $row["cod_formulario"],
                        "nom_formulario" => $row["nom_formulario"],
                        "bloques"        => array()
                    );
                }

                $cod_bloque = $row["cod_bloque"];
                $cod_campo  = $row["cod_campo"];

                if (!isset($data["bloques"][$cod_bloque])) {
                    $data["bloques"][$cod_bloque] = array(
                        "cod_bloque" => $row["cod_bloque"],
                        "nom_bloque" => $row["nom_bloque"],
                        "campos"     => array()
                    );
                }

                if (!isset($data["bloques"][$cod_bloque]["campos"][$cod_campo])) {
                    $grilla = null;
                    if ($row["cod_tipo_campo"] === 'matrixd') {
                        $cod_form_grilla = $this->getFormGrid($cod_campo);
                        if ($cod_form_grilla !== null) {
                            $columnas = $this->getEstructuraGrilla($cod_form_grilla);
                            $grilla = array(
                                "cod_formulario" => $cod_form_grilla,
                                "columnas"       => $columnas
                            );
                        }
                    }

                    $data["bloques"][$cod_bloque]["campos"][$cod_campo] = array(
                        "cod_campo"      => $row["cod_campo"],
                        "orden_campo"    => $row["orden_campo"],
                        "nom_campo"      => $row["nom_campo"],
                        "nom_tipo_campo" => $row["nom_tipo_campo"],
                        "cod_tipo_campo" => $row["cod_tipo_campo"],
                        "tam_campo"      => $row["tam_campo"],
                        "obligatorio"    => $row["obligatorio"],
                        "items"          => array(),
                        "hijos"          => array(),
                        "grilla"         => $grilla
                    );
                }

                if (!empty($row["valor_item"])) {
                    $ya_existe = false;
                    foreach ($data["bloques"][$cod_bloque]["campos"][$cod_campo]["items"] as $it) {
                        if ($it["valor_item"] === $row["valor_item"]) {
                            $ya_existe = true;
                            break;
                        }
                    }
                    if (!$ya_existe) {
                        $data["bloques"][$cod_bloque]["campos"][$cod_campo]["items"][] = array(
                            "valor_item" => $row["valor_item"],
                            "nom_item"   => $row["nom_item"]
                        );
                    }
                }

                if (!empty($row["cod_campo_hijo"])) {
                    $cod_hijo = $row["cod_campo_hijo"];
                    if (!isset($data["bloques"][$cod_bloque]["campos"][$cod_campo]["hijos"][$cod_hijo])) {
                        $data["bloques"][$cod_bloque]["campos"][$cod_campo]["hijos"][$cod_hijo] = array(
                            "cod_campo"   => $cod_hijo,
                            "nom_campo"   => $row["nom_campo_hijo"],
                            "consecutivo" => $row["consecutivo_hijo"]
                        );
                    }
                }
            }

            foreach ($data["bloques"] as &$bloque) {
                foreach ($bloque["campos"] as &$campo) {
                    $campo["hijos"] = array_values($campo["hijos"]);
                }
                unset($campo);
                $bloque["campos"] = array_values($bloque["campos"]);
            }
            unset($bloque);

            $data["bloques"] = array_values($data["bloques"]);
        }

        return $data;
    }


    private function evaluarReglas($reglas, $respuestas)
    {
        $coincidencias = [];

        foreach ($reglas as $regla) {
            $condicion = trim($regla['condicion'] ?? '');

            // Condición true siempre pasa
            if (strtolower($condicion) === 'true') {
                $coincidencias[] = $regla;
                continue;
            }

            // Separar por OR
            $partes_or = preg_split('/\s+OR\s+/i', $condicion);
            $cumple = false;

            foreach ($partes_or as $cond) {
                $cond = trim($cond);
                $partes = explode('==', $cond);
                if (count($partes) !== 2) continue;

                $izquierda = trim($partes[0]);
                $valor_esperado = trim($partes[1]);
                $valor_esperado = trim($valor_esperado, "'\"");

                // El campo es el último segmento: proceso_tarea_paso_form_bloque_CAMPO
                $segmentos = explode('_', $izquierda);
                $cod_campo = end($segmentos);

                // Buscar respuesta en el array recibido
                $valor_guardado = $respuestas[$cod_campo] ?? null;

                if ($valor_guardado !== null) {
                    if (is_numeric($valor_esperado) && is_numeric($valor_guardado)) {
                        $cumple = intval($valor_guardado) === intval($valor_esperado);
                    } else {
                        $cumple = strtoupper(trim($valor_guardado)) === strtoupper($valor_esperado);
                    }
                    if ($cumple) break;
                }
            }

            if ($cumple) $coincidencias[] = $regla;
        }

        if (empty($coincidencias)) return null;

        // Ordenar por orden y retornar el primero
        usort($coincidencias, fn($a, $b) => ($a['orden'] ?? 999) - ($b['orden'] ?? 999));
        return $coincidencias[0];
    }


    private function getSiguientePorTipTar($proceso_id, $tarea_id, $cod_formulario_actual)
    {

        $sql_tip = "
        SELECT t.tip_tar
        FROM wf_tarea t
        WHERE t.id = '$tarea_id'
    ";
        $result_tip = pg_query($sql_tip);
        $row_tip = pg_fetch_assoc($result_tip);
        if (!$row_tip) return null;

        $tip_tar_actual = $row_tip['tip_tar'];


        $sql_sig = "
        SELECT
            p.elem_paso AS cod_formulario_siguiente,
            p.id AS paso_siguiente_id,
            t.id AS tarea_siguiente_id
        FROM wf_tarea t
        JOIN wf_paso p ON p.wf_tarea_id = t.id AND p.tipo_paso = 'form'
        WHERE t.wf_proceso_id = '$proceso_id'
            AND t.tip_tar != '$tip_tar_actual'
            AND t.est_tar = 'a'
            AND p.est_paso = 'a'
        ORDER BY t.id ASC, p.ord_paso ASC
        LIMIT 1
    ";
        $result_sig = pg_query($sql_sig);
        $row_sig = pg_fetch_assoc($result_sig);

        return $row_sig ?: null;
    }



    //Funciones antiguos.

    public function getFormulariosProcesos($core_usuario_id = '')
    {
        $query = "SELECT cs.id AS id_sujeto FROM datos_sujeto_verificacion dsv
            INNER JOIN core_sujetoverificacion cs USING(id)
            INNER JOIN wf_cargoplan_municipio wcm ON wcm.plan_municipio_id = cs.plan_municipio_id
            INNER JOIN wf_cargousuario wc ON wc.wf_cargo_id = wcm.wf_cargo_id
            WHERE wc.core_usuario_id = $core_usuario_id
            GROUP BY 1
            ORDER BY 1";

        $result = pg_query($query);
        $data = array();
        $data2 = array();
        $data3 = array();
        $datamx = array();
        $datamxform = array();
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $id_sujeto = $row['id_sujeto'];
                $q2 = "SELECT pc.proceso_id,p.tit_proc
                    FROM core_procesocategoria pc
                    JOIN wf_proceso p ON p.id=pc.proceso_id
                    WHERE est_proc='a' AND categorias LIKE '%'||(
                    SELECT core_sujetocategoria_id:: TEXT
                    FROM core_sujetoverificacion
                    WHERE id=$id_sujeto)||'%'";

                $result2 = pg_query($q2);
                while ($row2 = pg_fetch_assoc($result2)) {
                    $data2[$row2['proceso_id']] = $row2['proceso_id'];
                }
            }
        }
        $processIds = implode(',', array_keys($data2));
        $q3 = "SELECT * FROM wf_tarea WHERE wf_proceso_id IN ($processIds) ORDER BY id";
        $result3 = pg_query($q3);
        if ($result3) {
            $i = 0;
            while ($row3 = pg_fetch_assoc($result3)) {
                $q4 = "SELECT * FROM wf_paso
                    WHERE tipo_paso = 'form' AND wf_tarea_id = {$row3['id']}
                    ORDER BY 1";
                $data['wf_tarea'][] = $row3;
                $result4 = pg_query($q4);
                while ($row4 = pg_fetch_assoc($result4)) {
                    $data3[$row4['elem_paso']] = $row4['elem_paso'];
                    $data['wf_paso'][] = $row4;
                }
            }
        }

        if (is_array($data3)) {
            foreach ($data3 as $var) {
                $q5 = "SELECT * FROM sce.formulario 
                    WHERE tipo=0 AND cod_formulario = $var";
                $result5 = pg_query($q5);
                while ($row5 = pg_fetch_assoc($result5)) {
                    $data['formulario'][] = $row5;
                }
            }
        }
        if (is_array($data['formulario'])) {
            //foreach($data['formulario'] as $var){
            $q6 = "SELECT *
                    FROM sce.formulario_bloque
                    /*WHERE cod_formulario={$var['cod_formulario']}*/
                    ORDER BY 1";
            $result6 = pg_query($q6);
            while ($row6 = pg_fetch_assoc($result6)) {
                $data['formulario_bloque'][] = $row6;
            }
            //}
        }
        if (is_array($data['formulario_bloque'])) {
            foreach ($data['formulario_bloque'] as $var) {
                $cod_bloque = $var['cod_bloque'];
                $q7 = "SELECT * FROM sce.bloque
                    WHERE cod_bloque=$cod_bloque
                    ORDER BY 1";
                $result7 = pg_query($q7);
                while ($row7 = pg_fetch_assoc($result7)) {
                    $data['bloque'][] = $row7;
                }
            }
        }
        if (is_array($data['bloque'])) {
            foreach ($data['bloque'] as $var) {
                $cod_bloque = $var['cod_bloque'];
                $q8 = "SELECT * FROM sce.bloque_campo
                    WHERE cod_bloque = $cod_bloque
                    ORDER BY 3";
                $result8 = pg_query($q8);
                while ($row8 = pg_fetch_assoc($result8)) {
                    $data['bloque_campo'][] = $row8;
                }
            }
        }
        if (is_array($data['bloque_campo'])) {
            //foreach($data['bloque_campo'] as $var){
            $cod_campo = $var['cod_campo'];
            $q9 = "SELECT * FROM sce.campo
                    -- WHERE cod_campo=$cod_campo
                    ORDER BY 1";
            $result9 = pg_query($q9);
            while ($row9 = pg_fetch_assoc($result9)) {
                $data['campo'][] = $row9;
                if ($row9['cod_tipo_campo'] == 'matrixd') {
                    $datamx['matrixd'][] = $row9['cod_campo'];
                }
            }
            //}
            if (is_array($datamx['matrixd'])) {
                //foreach ($datamx['matrixd'] as $key) {
                $q12 = "SELECT * FROM sce.propiedad_valor WHERE cod_propiedad IN ('form_grid','parent','casing')";
                $result12 = pg_query($q12);
                while ($row12 = pg_fetch_assoc($result12)) {
                    $data['propiedad_valor'][] = $row12;
                    $datamxform['cod_form_mx'][] = $row12['valor'];
                }

                //}
                if (is_array($datamxform['cod_form_mx'])) {
                    foreach ($datamxform['cod_form_mx'] as $key1) {
                        $q13 = "SELECT * FROM sce.formulario 
                            WHERE /*tipo=0 AND*/ cod_formulario = $key1";
                        $result13 = pg_query($q13);
                        while ($row13 = pg_fetch_assoc($result13)) {
                            $data['formulario_grilla'][] = $row13;
                        }
                    }
                }
            }
        }
        if (is_array($data['bloque_campo'])) {
            foreach ($data['bloque_campo'] as $var) {
                $cod_campo = $var['cod_campo'];
                $q10 = "SELECT * FROM sce.item_campo WHERE cod_campo=$cod_campo ORDER BY 1";
                $result10 = pg_query($q10);
                while ($row10 = pg_fetch_assoc($result10)) {
                    $data['item_campo'][] = $row10;
                }
            }
        }
        $q11 = "SELECT * FROM sce.tipo_campo ORDER BY 1";
        $result11 = pg_query($q11);
        while ($row11 = pg_fetch_assoc($result11)) {
            $data['tipo_campo'][] = $row11;
        }
        $q12 = "SELECT * FROM sce.tipo_caracter";
        $result12 = pg_query($q12);
        while ($row12 = pg_fetch_assoc($result12)) {
            $data['tipo_caracter'][] = $row12;
        }
        return $data;
    }
    public function getSujetoPersona($core_usuario_id, $detalle = "", $municipio = "", $zona = "")
    {
        //Inicialización de variables
        $detalle   = isset($detalle) ? trim($detalle) : '';
        $municipio = isset($municipio) ? trim($municipio) : '';
        $zona      = isset($zona) ? trim($zona) : '';

        $filtros = array();

        if ($detalle !== '') {
            $filtros[] = "dsv.detalle ILIKE '%$detalle%'";
        }

        if ($municipio !== '') {
            $filtros[] = "dsv.municipio ILIKE '%$municipio%'";
        }

        if ($zona !== '') {
            $filtros[] = "dsv.zona ILIKE '%$zona%'";
        }

        $whereFiltros = '';
        if (!empty($filtros)) {
            $whereFiltros = ' AND ' . implode(' AND ', $filtros);
        }

        $query = "SELECT dsv.*,cs.core_sujetocategoria_id FROM datos_sujeto_verificacion dsv
                    INNER JOIN core_sujetoverificacion cs USING(id)
                    INNER JOIN wf_cargoplan_municipio wcm ON wcm.plan_municipio_id = cs.plan_municipio_id
                    INNER JOIN wf_cargousuario wc ON wc.wf_cargo_id = wcm.wf_cargo_id
                    WHERE wc.core_usuario_id = $core_usuario_id $whereFiltros
                    ORDER BY dsv.detalle";
        $result = pg_query($query);
        $data = array();
        if ($result) {
            $i = 0;
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getTareaReglaDerivacion()
    {
        $query = "SELECT * FROM wf_tarearegladerivacion
            ORDER BY 1";
        $result = pg_query($query);
        $data = array();
        if ($result) {
            $i = 0;
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
    public function getPasoReglaDerivacion()
    {
        $query = "SELECT * FROM wf_pasoregladerivacion
            ORDER BY 1";
        $result = pg_query($query);
        $data = array();
        if ($result) {
            $i = 0;
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getCanelndarioUsuario($core_usuario_id)
    {
        $year = date("Y");
        $query = "SELECT * FROM calen_principal
            WHERE cal_fec_ini > ' $year-01-01' AND cal_usu='$core_usuario_id'";
        $result = pg_query($query);
        $data = array();
        if ($result) {
            $i = 0;
            while ($row = pg_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getEstablecimientos($municipio, $today, $nombre)
    {


        if ($municipio == "TODOS") {
            $query = "SELECT * FROM (
          SELECT t20009.no_caso,t20002.municipio,t20002.categoria,t20002.tip_suje,t20002.doc_pro,t20002.raz_pro,t20002.dir_pro,t20002.per_con_pro,t20011.fecha_v,t20009.procedimiento, t20011.val_concepto,tel_pro,cel_pro
                FROM (SELECT id,wf_proceso_id,procedimiento,'{\"pro\":\"'||wf_proceso_id||'\",\"cas\":\"'||max(no_caso)||'\"}' AS wf_caso_id, COUNT(no_caso), MAX(no_caso) AS no_caso
                FROM public.casos_por_sujeto
                GROUP BY id,wf_proceso_id,procedimiento) t20009
                JOIN public.vista_core_sujetoverificacionestablecimiento t20002 USING(id)
                JOIN public.v_concepto_caso t20011 USING(wf_caso_id)
                WHERE (fecha_v::DATE >= '2012-12-01' AND fecha_v:: DATE <= '$today')  AND
            TRANSLATE(t20002.raz_pro,
    'áéíóúàèìòùãõâêîôôäëïöüçÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ',
    'aeiouaeiouaoaeiooaeioucAEIOUAEIOUAOAEIOOAEIOUC') ilike  '%$nombre%'

            UNION
           SELECT 
            s201.caso AS no_caso,sujeto.municipio,sujeto.categoria,sujeto.tip_suje,sujeto.doc_pro, sujeto.raz_pro,
            sujeto.dir_pro,sujeto.per_con_pro,s201.fecha_v AS fecha_v,s201.concepto AS procedimiento,
            (CASE
                WHEN s201.des_con = 1 THEN 'FAVORABLE'
                WHEN s201.des_con = 2 THEN 'FAVORABLE CON REQUERIMIENTOS'
                WHEN s201.des_con = 3 THEN 'DESFAVORABLE'
            END) AS val_concepto,
            sujeto.tel_pro,
            sujeto.cel_pro
        FROM sce.t201 s201
        JOIN 
            vista_core_sujetoverificacionestablecimiento sujeto 
            ON sujeto.id = s201.sujeto_verificacion
        JOIN (
            SELECT 
                sujeto_verificacion,
                MAX(fecha_v) AS max_fecha_v
            FROM 
                sce.t201
            WHERE 
                fecha_v::DATE >= '2012-12-01' 
                AND fecha_v::DATE <= '$today'
            GROUP BY 
                sujeto_verificacion
        ) max_fecha 
        ON s201.sujeto_verificacion = max_fecha.sujeto_verificacion
        AND s201.fecha_v = max_fecha.max_fecha_v
        WHERE 
              TRANSLATE(sujeto.raz_pro,
    'áéíóúàèìòùãõâêîôôäëïöüçÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ',
    'aeiouaeiouaoaeiooaeioucAEIOUAEIOUAOAEIOOAEIOUC') ilike  '%$nombre%'

        ) a1
        ORDER BY a1.municipio,a1.categoria,a1.raz_pro";
        } else {
            $query = "SELECT * FROM (
          SELECT t20009.no_caso,t20002.municipio,t20002.categoria,t20002.tip_suje,t20002.doc_pro,t20002.raz_pro,t20002.dir_pro,t20002.per_con_pro,t20011.fecha_v,t20009.procedimiento, t20011.val_concepto,tel_pro,cel_pro
                FROM (SELECT id,wf_proceso_id,procedimiento,'{\"pro\":\"'||wf_proceso_id||'\",\"cas\":\"'||max(no_caso)||'\"}' AS wf_caso_id, COUNT(no_caso), MAX(no_caso) AS no_caso
                FROM public.casos_por_sujeto
                GROUP BY id,wf_proceso_id,procedimiento) t20009
                JOIN public.vista_core_sujetoverificacionestablecimiento t20002 USING(id)
                JOIN public.v_concepto_caso t20011 USING(wf_caso_id)
                WHERE (fecha_v::DATE >= '2012-12-01' AND fecha_v:: DATE <= '$today')  AND municipio = '$municipio'
					 AND TRANSLATE(t20002.raz_pro,
    'áéíóúàèìòùãõâêîôôäëïöüçÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ',
    'aeiouaeiouaoaeiooaeioucAEIOUAEIOUAOAEIOOAEIOUC') ilike  '%$nombre%'

            UNION
           SELECT 
            s201.caso AS no_caso,sujeto.municipio,sujeto.categoria,sujeto.tip_suje,sujeto.doc_pro, sujeto.raz_pro,
            sujeto.dir_pro,sujeto.per_con_pro,s201.fecha_v AS fecha_v,s201.concepto AS procedimiento,
            (CASE
                WHEN s201.des_con = 1 THEN 'FAVORABLE'
                WHEN s201.des_con = 2 THEN 'FAVORABLE CON REQUERIMIENTOS'
                WHEN s201.des_con = 3 THEN 'DESFAVORABLE'
            END) AS val_concepto,
            sujeto.tel_pro,
            sujeto.cel_pro
        FROM sce.t201 s201
        JOIN 
            vista_core_sujetoverificacionestablecimiento sujeto 
            ON sujeto.id = s201.sujeto_verificacion
        JOIN (
            SELECT 
                sujeto_verificacion,
                MAX(fecha_v) AS max_fecha_v
            FROM 
                sce.t201
            WHERE 
                fecha_v::DATE >= '2012-12-01' 
                AND fecha_v::DATE <= '$today'
            GROUP BY 
                sujeto_verificacion
        ) max_fecha 
        ON s201.sujeto_verificacion = max_fecha.sujeto_verificacion
        AND s201.fecha_v = max_fecha.max_fecha_v
        WHERE 
         municipio = '$municipio'  AND  TRANSLATE(sujeto.raz_pro,
    'áéíóúàèìòùãõâêîôôäëïöüçÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ',
    'aeiouaeiouaoaeiooaeioucAEIOUAEIOUAOAEIOOAEIOUC') ilike  '%$nombre%'

        ) a1
        ORDER BY a1.municipio,a1.categoria,a1.raz_pro";
        }




        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());



        return $result;
    }

    public function getEstablecimientosNit($municipio, $today, $nombre)
    {


        if ($municipio == "TODOS") {
            $query = "SELECT * FROM (
            SELECT t20009.no_caso,t20002.municipio,t20002.categoria,t20002.tip_suje,t20002.doc_pro,t20002.raz_pro,t20002.dir_pro,t20002.per_con_pro,t20011.fecha_v,t20009.procedimiento, t20011.val_concepto,tel_pro,cel_pro
            FROM (SELECT id,wf_proceso_id,procedimiento,'{\"pro\":\"'||wf_proceso_id||'\",\"cas\":\"'||max(no_caso)||'\"}' AS wf_caso_id, COUNT(no_caso), MAX(no_caso) AS no_caso
            FROM public.casos_por_sujeto 
            GROUP BY id,wf_proceso_id,procedimiento) t20009
            JOIN public.vista_core_sujetoverificacionestablecimiento t20002 USING(id)
            JOIN public.v_concepto_caso t20011 USING(wf_caso_id)
            WHERE (fecha_v::DATE >= '2012-12-01' AND fecha_v:: DATE <= '$today')  
            AND t20002.doc_pro ILIKE  '%$nombre%'
            UNION
           SELECT 
            s201.caso AS no_caso,sujeto.municipio,sujeto.categoria,sujeto.tip_suje,sujeto.doc_pro, sujeto.raz_pro,
            sujeto.dir_pro,sujeto.per_con_pro,s201.fecha_v AS fecha_v,s201.concepto AS procedimiento,
            (CASE
                WHEN s201.des_con = 1 THEN 'FAVORABLE'
                WHEN s201.des_con = 2 THEN 'FAVORABLE CON REQUERIMIENTOS'
                WHEN s201.des_con = 3 THEN 'DESFAVORABLE'
            END) AS val_concepto,
            sujeto.tel_pro,
            sujeto.cel_pro
        FROM sce.t201 s201
        JOIN 
            vista_core_sujetoverificacionestablecimiento sujeto 
            ON sujeto.id = s201.sujeto_verificacion
        JOIN (
            SELECT 
                sujeto_verificacion,
                MAX(fecha_v) AS max_fecha_v
            FROM 
                sce.t201
            WHERE 
                fecha_v::DATE >= '2012-12-01' 
                AND fecha_v::DATE <= '$today
                sujeto_verificacion
        ) max_fecha 
        ON s201.sujeto_verificacion = max_fecha.sujeto_verificacion
        AND s201.fecha_v = max_fecha.max_fecha_v
        WHERE 
            sujeto.doc_pro ILIKE '%$nombre%'

        ) a1
        ORDER BY a1.municipio,a1.categoria,a1.raz_pro";
        } else {
            $query = "SELECT * FROM (
            SELECT t20009.no_caso,t20002.municipio,t20002.categoria,t20002.tip_suje,t20002.doc_pro,t20002.raz_pro,t20002.dir_pro,t20002.per_con_pro,t20011.fecha_v,t20009.procedimiento, t20011.val_concepto,tel_pro,cel_pro
            FROM (SELECT id,wf_proceso_id,procedimiento,'{\"pro\":\"'||wf_proceso_id||'\",\"cas\":\"'||max(no_caso)||'\"}' AS wf_caso_id, COUNT(no_caso), MAX(no_caso) AS no_caso
            FROM public.casos_por_sujeto 
            GROUP BY id,wf_proceso_id,procedimiento) t20009
            JOIN public.vista_core_sujetoverificacionestablecimiento t20002 USING(id)
            JOIN public.v_concepto_caso t20011 USING(wf_caso_id)
            WHERE (fecha_v::DATE >= '2012-12-01' AND fecha_v:: DATE <= '$today')  AND municipio = '$municipio'
            AND t20002.doc_pro ILIKE  '%$nombre%'
            UNION
            SELECT 
                s201.caso AS no_caso,sujeto.municipio,sujeto.categoria,sujeto.tip_suje,sujeto.doc_pro, sujeto.raz_pro,
                sujeto.dir_pro,sujeto.per_con_pro,s201.fecha_v AS fecha_v,s201.concepto AS procedimiento,
                (CASE
                    WHEN s201.des_con = 1 THEN 'FAVORABLE'
                    WHEN s201.des_con = 2 THEN 'FAVORABLE CON REQUERIMIENTOS'
                    WHEN s201.des_con = 3 THEN 'DESFAVORABLE'
                END) AS val_concepto,
                sujeto.tel_pro,
                sujeto.cel_pro
            FROM sce.t201 s201
            JOIN 
                vista_core_sujetoverificacionestablecimiento sujeto 
                ON sujeto.id = s201.sujeto_verificacion
            JOIN (
                SELECT 
                    sujeto_verificacion,
                    MAX(fecha_v) AS max_fecha_v
                FROM 
                    sce.t201
                WHERE 
                    fecha_v::DATE >= '2012-12-01' 
                    AND fecha_v::DATE <= '$today'
                GROUP BY 
                    sujeto_verificacion
            ) max_fecha 
            ON s201.sujeto_verificacion = max_fecha.sujeto_verificacion
            AND s201.fecha_v = max_fecha.max_fecha_v
            WHERE 
                sujeto.municipio = '$municipio' AND sujeto.doc_pro ILIKE '%$nombre%'

            ) a1
            ORDER BY a1.municipio,a1.categoria,a1.raz_pro";
        }


        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());

        return $result;
    }

    public function getEstablecimientosMuni($municipio, $today)
    {



        $query = "SELECT * FROM (
            SELECT t20009.no_caso,t20002.municipio,t20002.categoria,t20002.tip_suje,t20002.doc_pro,t20002.raz_pro,t20002.dir_pro,t20002.per_con_pro,t20011.fecha_v,t20009.procedimiento, t20011.val_concepto,tel_pro,cel_pro
            FROM (SELECT id,wf_proceso_id,procedimiento,'{\"pro\":\"'||wf_proceso_id||'\",\"cas\":\"'||max(no_caso)||'\"}' AS wf_caso_id, COUNT(no_caso), MAX(no_caso) AS no_caso
            FROM public.casos_por_sujeto 
            GROUP BY id,wf_proceso_id,procedimiento) t20009
            JOIN public.vista_core_sujetoverificacionestablecimiento t20002 USING(id)
            JOIN public.v_concepto_caso t20011 USING(wf_caso_id)
            WHERE (fecha_v::DATE >= '2012-12-01' AND fecha_v:: DATE <= '$today')  AND municipio = '$municipio'
            UNION
           SELECT 
            s201.caso AS no_caso,sujeto.municipio,sujeto.categoria,sujeto.tip_suje,sujeto.doc_pro, sujeto.raz_pro,
            sujeto.dir_pro,sujeto.per_con_pro,s201.fecha_v AS fecha_v,s201.concepto AS procedimiento,
            (CASE
                WHEN s201.des_con = 1 THEN 'FAVORABLE'
                WHEN s201.des_con = 2 THEN 'FAVORABLE CON REQUERIMIENTOS'
                WHEN s201.des_con = 3 THEN 'DESFAVORABLE'
            END) AS val_concepto,
            sujeto.tel_pro,
            sujeto.cel_pro
        FROM sce.t201 s201
        JOIN 
            vista_core_sujetoverificacionestablecimiento sujeto 
            ON sujeto.id = s201.sujeto_verificacion
        JOIN (
            SELECT 
                sujeto_verificacion,
                MAX(fecha_v) AS max_fecha_v
            FROM 
                sce.t201
            WHERE 
                fecha_v::DATE >= '2012-12-01' 
                AND fecha_v::DATE <= '$today'
            GROUP BY 
                sujeto_verificacion
        ) max_fecha 
        ON s201.sujeto_verificacion = max_fecha.sujeto_verificacion
        AND s201.fecha_v = max_fecha.max_fecha_v
        WHERE 
            sujeto.municipio = '$municipio'

        ) a1
        ORDER BY a1.municipio,a1.categoria,a1.raz_pro";


        $result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());

        return $result;
    }




    /**-------------- Aqui iniciar supergas --------------**/
    public function loginSuper($name, $password)
    {

        $stmt = $this->conn->prepare("SELECT * FROM ususis WHERE nom_usu = '$name' AND pas_usu = '$password'");

        if ($stmt->execute()) {

            $res = $stmt->get_result();

            $stmt->close();


            return $res;
        } else {

            $stmt->close();

            return NULL;
        }
    }


    public function nameUser($id_per)
    {

        $stmt = $this->conn->prepare("SELECT nom_per,ape_per FROM persona WHERE id_per ='$id_per'");

        if ($stmt->execute()) {

            $res = $stmt->get_result();

            $stmt->close();

            return $res;
        } else {

            $stmt->close();

            return NULL;
        }
    }

    //ciudad
    public function getNivelCiudad()
    {
        $stmt = $this->conn->prepare("SELECT * FROM nivel");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }

    //zona
    public function getGradoCiudad()
    {
        $stmt = $this->conn->prepare("SELECT * FROM grado");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }

    //barrios
    public function getCursoCiudad()
    {

        $stmt = $this->conn->prepare("SELECT * FROM curso");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }

    //table alumno
    public function getAlumnoSuper()
    {

        $stmt = $this->conn->prepare("SELECT * FROM alumno");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }

    //table consulta con ciudad, zona y barrio
    public function getListarSupergas($cod_niv, $cod_gra)
    {

        $stmt = $this->conn->prepare("SELECT gm.*,a.id_per,a.ape_per,a.nom_per,g.cod_gra,gl.img_glec,
                g.des_gra,c.des_cur,n.des_niv,gl.id_glec,gl.fec_glec, 
                gl.lact_glec,gl.obs_glec,gl.lant_glec 
                FROM gra_medidor gm INNER JOIN alumno a ON gm.id_gmed=a.id_alu 
                INNER JOIN curso c USING(cod_cur) INNER JOIN grado g 
                USING(cod_gra) INNER JOIN nivel n USING(cod_niv) 
                LEFT JOIN ( SELECT * FROM ( SELECT id_gmed, MAX(fec_glec) AS 
                fec_glec FROM gra_lectura GROUP BY id_gmed) a INNER JOIN 
                gra_lectura USING(id_gmed,fec_glec))gl USING(id_gmed) 
                WHERE gm.est_gmed='a' AND n.cod_niv='$cod_niv' AND g.cod_gra='$cod_gra' 
                AND cod_cur ORDER BY /*gm.id_gmed,*/ n.des_niv,g.des_gra,
                c.des_cur,gm.apt_gmed,a.ape_per,a.nom_per");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }





    //table gra_medidor
    public function getGraMedidorSuper()
    {

        $stmt = $this->conn->prepare("SELECT * FROM gra_medidor");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }

    //table gra_lectura
    public function getGraLecturaSuper()
    {

        $stmt = $this->conn->prepare("SELECT * FROM gra_lectura");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }


    //update table upload
    public function setTableUpdate(
        $id_glec,
        $id_gmed,
        $fec_glec,
        $lant_glec,
        $lact_glec,
        $dif_glec,
        $obs_glec,
        $id_per,
        $fec_cre,
        $id_usu_cre,
        $img_glec
    ) {

        $stmt = $this->conn->prepare("UPDATE gra_lectura_app SET 
          fec_glec = '$fec_glec', 
          lant_glec = '$lant_glec',
          lact_glec = '$lact_glec',
          dif_glec = '$dif_glec',
          obs_glec = '$obs_glec',
          id_per =  '$id_per',
          fec_cre = '$fec_cre',
          id_usu_cre = '$id_usu_cre',
          fec_glec_d = '0000-00-00', 
          id_goper = '0',
          img_glec = '$img_glec'
          WHERE id_gmed = '$id_gmed' AND id_glec = '$id_glec'");

        if ($stmt->execute()) {
            $stmt->close();
            return 'true';
        } else {
            $stmt->close();
            return NULL;
        }
    }



    /**-------------- Aqui termina Supergas --------------**/












































    /* ------------- `users` table method ------------------ */

    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($name, $email, $password)
    {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users(name, email, password_hash, api_key, status) values(?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $name, $email, $password_hash, $api_key);

            $result = $stmt->execute();

            $stmt->close();

            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }

        return $response;
    }



    /**
     * Checking user login
     * @param String $nom_usu User login nombre usuario
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLoginAPI($login, $pwd)
    {
        // fetching user by nombre
        $stmt = $this->conn->prepare("SELECT * FROM (
			SELECT 
			u.id_alu as usuario_id,
			u.id_alu as usuario_login,
			i.con_alu as usuario_password,
			5 as usuario_nivel,
			u.id_alu as id_per,
			u.nom_per,
			u.ape_per,
			u.nac_per,
			'e' as bd
			FROM
			alumno u
			INNER JOIN info_adi_alu i USING(id_alu)
			WHERE
			est_alu='h' AND id_alu='$login' AND con_alu='$pwd' 
			AND id_alu NOT IN(
				SELECT id_alu
				FROM web_alumreg r
				LEFT JOIN web_usu w USING(id_usu)
				WHERE est_usu='i'
				UNION
				SELECT id_alu
				FROM alumno
				INNER JOIN web_usu w ON(est_usu='i' AND (id1_acu=ced_per OR id2_acu=ced_per OR id_res=ced_per))
				)
		UNION 
			SELECT 
			u.id_usu as usuario_id,
			u.usu_per as usuario_login,
			u.psw_per as usuario_password,
			u.id_tusu as usuario_nivel,
			u.ced_per as id_per,
			u.nom_per,
			u.ape_per,
			p.nac_per,
			'w' as bd
			FROM
			web_usu u
			left join persona p on u.ced_per=p.id_per
			WHERE
			est_usu='a' AND u.usu_per='$login' AND u.psw_per='$pwd'
		UNION	
			SELECT 
			u.id_usu as usuario_id,
			u.nom_usu as usuario_login,
			u.pas_usu as usuario_password,
			u.tip_usu as usuario_nivel,
			u.id_per,
			nom_per,
			ape_per,
			nac_per,
			's' as bd
			FROM
			ususis u
			INNER JOIN persona p USING(id_per)
			WHERE
			est_usu='a' AND u.nom_usu='$login' AND u.pas_usu='$pwd'
		) a");


        //$num_affected_rows = $stmt->affected_rows;

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            // user not existed with the nombre de usuario ingresado
            return FALSE;
        }
    }

    public function cursoDbEstudiante($id_alumno)
    {
        $stmt = $this->conn->prepare("SELECT des_gra,des_cur_cor FROM alumno 
            JOIN alumcurso USING(id_alu)
            JOIN curso USING(cod_cur)
            JOIN grado USING(cod_gra)
            WHERE id_alu = $id_alumno");


        $stmt->execute();
        $stmt->bind_result($des_gra, $des_cur_cor);
        $stmt->store_result();

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return false;
        }
    }

    public function getContenidosD($cod_cur, $cod_mat, $cod_gra, $pad, $url_sitio, $db_sitio)
    {/*,$db_sitio,$url_sitio*/
        /* REPLACE( des_cdtem,'../../upload/','http://".$url_sitio."/".$db_sitio."/upload/') AS des_cdtem*/
        /* REPLACE( des_cdtem,'../../upload/','http://$url_sitio/$db_sitio."/upload/') AS des_cdtem*/

        $stmt = $this->conn->prepare("   SELECT id_cdtem,tit_cdtem,tip_cdtem,pad_cdtem, REPLACE( des_cdtem,'../../upload/','http://" . $url_sitio . "/" . $db_sitio . "/upload/') AS des_cdtem,cod_gra,cod_mat
	FROM cd_tema t
        INNER JOIN cd_tema_curso USING(id_cdtem)
        LEFT JOIN materia m USING(cod_mat)
        WHERE /*pad_cdtem=$pad  and*/ cod_mat=$cod_mat  and cod_cur in ($cod_cur) and est_cdtemc='a' and est_cdtem='a'
        union
        SELECT id_cdtem,tit_cdtem,tip_cdtem,pad_cdtem,REPLACE( des_cdtem,'../../upload/','http://" . $url_sitio . "/" . $db_sitio . "/upload/') AS des_cdtem,cod_gra,cod_mat
	FROM cd_tema t
        left JOIN cd_tema_curso USING(id_cdtem)
        LEFT JOIN materia m USING(cod_mat)
        WHERE /*pad_cdtem=$pad  and */ cod_mat=$cod_mat /* and cod_cur=$cod_cur and est_cdtemc='a'*/ and est_cdtem='a' and dest_con='p'
        union
        SELECT id_cdtem,tit_cdtem,tip_cdtem,pad_cdtem,REPLACE( des_cdtem,'../../upload/','http://" . $url_sitio . "/" . $db_sitio . "/upload/') AS des_cdtem,cod_gra,cod_mat
	FROM cd_tema t
        left JOIN cd_tema_curso USING(id_cdtem)
        LEFT JOIN materia m USING(cod_mat)
        WHERE /*pad_cdtem=$pad and*/ cod_mat=$cod_mat /*and cod_cur=$cod_cur and est_cdtemc='a'*/ and est_cdtem='a' and dest_con='g' and cod_gra in ($cod_gra)
        UNION
		
		/*------------------------------------------*/
		
       SELECT id_cdtem, des_enl AS tit_cdtem, 'e' AS tip_ctem, id_cdtem as pad_cdtem,url_enl as des_cdtem, cod_gra, cod_mat
        FROM cd_enlace e
        JOIN  (SELECT t.id_cdtem,cod_gra, cod_mat FROM cd_tema t
        INNER JOIN cd_tema_curso USING(id_cdtem)
        LEFT JOIN materia m USING(cod_mat)
        WHERE cod_mat=$cod_mat  and cod_cur in ($cod_cur) and est_cdtemc='a' and est_cdtem='a') t USING(id_cdtem)
        
        UNION  
        SELECT id_cdtem, des_enl AS tit_cdtem, 'e' AS tip_ctem, id_cdtem as pad_cdtem,url_enl as des_cdtem,cod_gra, cod_mat
        FROM cd_enlace e
        JOIN  (SELECT id_cdtem,cod_gra, cod_mat FROM cd_tema t
        left JOIN cd_tema_curso USING(id_cdtem)
        LEFT JOIN materia m USING(cod_mat)
        WHERE  cod_mat=$cod_mat  and est_cdtem='a' and dest_con='p') t USING(id_cdtem)
        UNION  
        SELECT id_cdtem, des_enl AS tit_cdtem, 'e' AS tip_ctem, id_cdtem as pad_cdtem,url_enl as des_cdtem, cod_gra, cod_mat
        FROM cd_enlace e
        JOIN  (SELECT id_cdtem,cod_gra, cod_mat FROM cd_tema t
        left JOIN cd_tema_curso USING(id_cdtem)
        LEFT JOIN materia m USING(cod_mat)
        WHERE  cod_mat=$cod_mat  and est_cdtem='a' and dest_con='g' and cod_gra in ($cod_gra)
        ORDER BY id_cdtem) t USING(id_cdtem)
        UNION
        SELECT id_cdtem, nom_cdadj AS tit_cdtem, 'a' AS tip_ctem, id_cdtem as pad_cdtem,url_cdadj as des_cdtem, cod_gra, cod_mat
        FROM cd_tema_adj
        JOIN  (SELECT t.id_cdtem,cod_gra, cod_mat FROM cd_tema t
        INNER JOIN cd_tema_curso USING(id_cdtem)
        LEFT JOIN materia m USING(cod_mat)
        WHERE cod_mat=$cod_mat  and cod_cur in ($cod_cur) and est_cdtemc='a' and est_cdtem='a') t USING(id_cdtem)
        UNION  
        SELECT id_cdtem, nom_cdadj AS tit_cdtem, 'a' AS tip_ctem, id_cdtem as pad_cdtem,url_cdadj as des_cdtem, cod_gra, cod_mat
        FROM cd_tema_adj
        JOIN   (SELECT id_cdtem,cod_gra, cod_mat FROM cd_tema t
        left JOIN cd_tema_curso USING(id_cdtem)
        LEFT JOIN materia m USING(cod_mat)
        WHERE  cod_mat=$cod_mat  and est_cdtem='a' and dest_con='p')  t USING(id_cdtem)
        UNION  
        SELECT id_cdtem, nom_cdadj AS tit_cdtem, 'a' AS tip_ctem, id_cdtem as pad_cdtem,url_cdadj as des_cdtem, cod_gra, cod_mat
        FROM cd_tema_adj
        JOIN  (SELECT id_cdtem,cod_gra, cod_mat FROM cd_tema t
        left JOIN cd_tema_curso USING(id_cdtem)
        LEFT JOIN materia m USING(cod_mat)
        WHERE  cod_mat=$cod_mat  and est_cdtem='a' and dest_con='g' and cod_gra in ($cod_gra)) t USING(id_cdtem)
   ");

        //des_cdtem
        //id_cdtem

        if ($stmt->execute()) {
            $res = $stmt->get_result();

            $respuesta = array();


            $id_cdte = array();
            $tit_cdte = array();
            $tip_cdte = array();
            $pad_cdte = array();
            $des_cdte = array();
            $cod_ma = array();
            $cod_gr = array();


            while ($f = $res->fetch_assoc()) {
                array_push($id_cdte,  $f['id_cdtem']);
                array_push($tit_cdte, $f['tit_cdtem']);
                array_push($tip_cdte, $f['tip_cdtem']);
                array_push($pad_cdte, $f['pad_cdtem']);
                array_push($des_cdte, $f['des_cdtem']);
                array_push($cod_gr, $f['cod_gra']);
                array_push($cod_ma, $f['cod_mat']);
            }

            $id_cdtem["id_cdtem"] = $id_cdte;
            $tit_cdtem["tit_cdtem"] = $tit_cdte;
            $tip_cdtem["tip_cdtem"] = $tip_cdte;
            $pad_cdtem["pad_cdtem"] = $pad_cdte;
            $des_cdtem["des_cdtem"] = $des_cdte;
            $cod_grado["cod_grado"] = $cod_gr;
            $cod_mata["cod_materia"] = $cod_ma;

            array_push($respuesta, $id_cdtem, $tit_cdtem, $tip_cdtem, $pad_cdtem, $des_cdtem, $cod_grado, $cod_mata);

            $stmt->close();
            return $respuesta;
        } else {
            $stmt->close();
            return false;
        }
    }

    public function materiasAlum($id_alumno)
    {
        $stmt = $this->conn->prepare("SELECT des_mat,cod_mat,cod_gra,cod_cur FROM alumno 
            JOIN alumcurso USING(id_alu)
            JOIN curso USING(cod_cur)
            JOIN matgra USING(cod_cur)
            JOIN materia USING(cod_mat)
            WHERE id_alu = $id_alumno;
        ");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $respuesta = array();

            $j = array();
            $j1 = array();
            $j2 = array();
            $j3 = array();

            while ($f = $res->fetch_assoc()) {
                array_push($j, $f['des_mat']);
                array_push($j1, $f['cod_mat']);
                array_push($j2, $f['cod_gra']);
                array_push($j3, $f['cod_cur']);
                /*
                $j['des_mat'] = $f['des_mat']; 
                $j1['cod_mat'] = $f['cod_mat']; 
                $j2['cod_gra'] = $f['cod_gra']; 
                $j3['cod_cur'] = $f['cod_cur'];
                */
            }
            array_push($respuesta, $j, $j1, $j2, $j3);

            $stmt->close();
            return $respuesta;
        } else {
            $stmt->close();
            return false;
        }
    }


    public function mensajeWellcome()
    {

        $stmt = $this->conn->prepare("SELECT txt_msg FROM mensaje_inicio WHERE feci_msg<=NOW()  AND fecf_msg>=NOW() AND tip_msg LIKE '%s%' ;");

        if ($stmt->execute()) {

            $stmt->bind_result($txt_msg);

            $stmt->fetch();

            $stmt->close();

            return $txt_msg;
        } else {
            $stmt->close();

            return NULL;
        }
    }



    /**
     *return elements foros and tareas
     *
     */

    public function forosAndTareas($id_alu, $cod_cur)
    {
        /*---- SQL ANT---
		SELECT (case when cda.tipo='f' then 'FORO' ELSE 'TRABAJO' END) AS tipo, cda.nom_actividad,  m.des_mat_cor, m.des_mat,
                cda.fecha_fin,cda.hora_fin,'VERDE' AS semaf, cda.cod_gra, m.cod_mat, cda.id_actividad,ca.cod_cur,
                (select if
                ((SELECT COUNT(id_coment) FROM comentarios WHERE id_actividad=cda.id_actividad  and id_alu=$id_alu)>0 OR
                (SELECT COUNT(id_res) FROM respuestas WHERE id_actividad=cda.id_actividad and id_alu=$id_alu)>0,'ENVIADO','PENDIENTE'))
                                AS estado
                from cd_actividad cda  
                join curso_actividad ca on cda.id_actividad = ca.id_actividad
                join materia m on m.cod_mat=ca.cod_mat
                where ca.cod_cur=$cod_cur  order by fecha_fin,hora_fin
           */

        $stmt = $this->conn->prepare("SELECT (case when cda.tipo='f' then 'FORO' ELSE 'TRABAJO' END) AS tipo, cda.nom_actividad,  m.des_mat_cor, m.des_mat,
                cda.fecha_fin,cda.hora_fin,
					 (case 				 
						when ( (- (DATEDIFF(NOW(),cda.fecha_fin))) >= 0  AND  ( - (DATEDIFF(NOW(),cda.fecha_fin))) <= (SELECT SUBSTRING_INDEX(val_var, '/', 1) r  FROM _sapred WHERE nom_var='FORO_SEMAFORO')  ) then 'ROJO'
						when ( (- (DATEDIFF(NOW(),cda.fecha_fin))) >= (SELECT SUBSTRING_INDEX(val_var, '/', 1) r  FROM _sapred WHERE nom_var='FORO_SEMAFORO')  AND  ( - (DATEDIFF(NOW(),cda.fecha_fin))) <= (SELECT SUBSTRING_INDEX(val_var, '/', -1) a  FROM _sapred WHERE nom_var='FORO_SEMAFORO')  ) then 'AMARILLO'
							when ( (- (DATEDIFF(NOW(),cda.fecha_fin))) >= (SELECT SUBSTRING_INDEX(val_var, '/', -1) a  FROM _sapred WHERE nom_var='FORO_SEMAFORO')  ) then 'VERDE'
						ELSE 'AZUL'
					END) 					 
					 AS semaf,
					  cda.cod_gra, m.cod_mat, cda.id_actividad,ca.cod_cur,
                (select if
                ((SELECT COUNT(id_coment) FROM cd_comentarios WHERE id_actividad=cda.id_actividad  and id_alu=$id_alu)>0 OR
                (SELECT COUNT(id_res) FROM cd_respuestas WHERE id_actividad=cda.id_actividad and id_alu=$id_alu)>0,'ENVIADO','PENDIENTE'))
                                AS estado
                from cd_actividad cda  
                join cd_curso_actividad ca on cda.id_actividad = ca.id_actividad
                join materia m on m.cod_mat=ca.cod_mat
                where ca.cod_cur=$cod_cur  order by fecha_fin,hora_fin");


        if ($stmt->execute()) {
            $res = $stmt->get_result();
            /* $respuesta = array();

                $tipo = array();
                $nom_actividad = array();
                $des_mat = array();
                $fecha_fin = array();
                $hora_fin = array();
                $semaf = array();
                $cod_mat = array();
                $id_actividad = array();
                $estado = array();

                while ($f = $res->fetch_assoc()){
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
                array_push($respuesta['recibidos'],$tmp);
                    /*
                    array_push($tipo, $f['tipo']);
                    array_push($nom_actividad, $f['nom_actividad']);
                    array_push($des_mat, $f['des_mat']);
                    array_push($fecha_fin, $f['fecha_fin']);
                    array_push($hora_fin, $f['hora_fin']);
                    array_push($semaf, $f['semaf']);
                    array_push($cod_mat, $f['cod_mat']);
                    array_push($id_actividad, $f['id_actividad']);
                    array_push($estado, $f['estado']);
                    
                }
                */


            //array_push($respuesta,$tipo,$nom_actividad,$des_mat,$fecha_fin,$hora_fin,$semaf,$cod_mat,$id_actividad,$estado);
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return false;
        }
    }


    /** 
     *return sixeMaxAdjunto
     */
    public function sixeMaxAdjunto()
    {
        $stmt = $this->conn->prepare("SELECT val_var FROM _sapred WHERE nom_var='MENSAJERIA_TAMANO_ADJUNTO'");
        if ($stmt->execute()) {
            //$res = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($val_var);
            $stmt->fetch();
            $stmt->close();
            return $val_var;
        } else {
            return null;
        }
    }




    /**
     *return informacion importante archivos
     */
    public function infoImportante($cod_cur, $cod_gra)
    {

        $stmt = $this->conn->prepare("SELECT * FROM apoyodocs_documento
            WHERE cod_gra IN (0,-1,$cod_gra) AND cod_cur IN (0,-1,$cod_cur)  AND 
                 priv_apo IN ('t','e') AND 
            fpub_apo<=NOW()  AND ffin_apo>=NOW()");


        if ($stmt->execute()) {
            //$res = $stmt->get_result()->fetch_assoc();
            $res = $stmt->get_result();

            $stmt->fetch();

            $stmt->close();

            return $res;
        } else {

            return null;
        }
    }





    /** 
     *return sixeMaxAdjunto
     */
    public function formatoAdjMensaje()
    {
        $stmt = $this->conn->prepare("SELECT val_var FROM _sapred WHERE nom_var='MENSAJERIA_TIPO_ADJUNTO'");
        if ($stmt->execute()) {
            //$res = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($val_var);
            $stmt->fetch();
            $stmt->close();
            return $val_var;
        } else {
            return null;
        }
    }




    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password)
    {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($password_hash);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }


    /**
     * Fetching user by nom_user
     * @param String $nom_user User name
     */
    public function getUserByUsername($nom_usu)
    {
        $stmt = $this->conn->prepare("SELECT id_usu,tip_usu, api_key, nom_per FROM ususis WHERE tip_usu = '003' AND nom_usu = ?");
        $stmt->bind_param("s", $nom_usu);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id_usu, $tip_usu, $api_key, $nom_per);
            $stmt->fetch();
            $user = array();
            $user['id_usu'] = $id_usu;
            $user['nombre'] = $nom_per;
            $user["tipo"] = $tip_usu;
            $user["api_key"] = $api_key;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }



    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT name, email, api_key, status, created_at FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($name, $email, $api_key, $status, $created_at);
            $stmt->fetch();
            $user = array();
            $user["name"] = $name;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $user["status"] = $status;
            $user["created_at"] = $created_at;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id)
    {
        $stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // $api_key = $stmt->get_result()->fetch_assoc();
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key)
    {
        $stmt = $this->conn->prepare("SELECT id_per FROM ususis WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($id_per);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $id_per;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUsuId($api_key)
    {
        $stmt = $this->conn->prepare("SELECT ced_per FROM web_usu WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($ced_per);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $ced_per;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getTipoBD($id_per, $api_key)
    {
        $stmt = $this->conn->prepare("SELECT ced_per,id_tusu,est_usu FROM web_usu WHERE ced_per = ? AND api_key = ?");
        $stmt->bind_param("ss", $id_per, $api_key);
        if ($stmt->execute()) {
            //$stmt->bind_result($ced_per,$id_tusu,$est_usu);
            //$res=$stmt->fetch();
            // TODO
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key)
    {
        $stmt = $this->conn->prepare("SELECT id_usu from web_usu WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey()
    {
        return md5(uniqid(rand(), true));
    }
	
	
	/////////////////////////////////////////////////////////////////
    /**
     * Consultar mensajes recibidos
     */
    public function getRecibidos($id_des, $bd)
    {
        //global $bd;
        $tip_des = '';
        if ($bd == 'w') $tip_des = " and tip_des='w' ";
        if ($bd == 's') $tip_des = " and tip_des='s' ";
        $stmt = $this->conn->prepare("SELECT
					w.id_msg,fec_msg,asu_msg,txt_msg,hor_msg, CONCAT(p.ape_per,' ',p.nom_per) AS rem, w.est_msg, CONCAT(a.ape_per,' ',a.nom_per, IF(des_cur IS NOT NULL, CONCAT(' - ',des_cur),' ')) AS nom_est, IF(w.est_msg='i','email.png','page_white.png') AS img,tip_usu,tip_des, w.url_arc, w.url_arc2, w.url_arc3, w.url_arc4, w.url_arc5
			FROM
					(
			SELECT m.id_msg, `id_rem`, `asu_msg`, `txt_msg`, `fec_msg`, `hor_msg`, `ip_msg`, `pri_msg`, `tipe_msg`, `res_msg`,id_des, id_alu, md.est_msg,tip_usu,tip_des,m.url_arc,m.url_arc2,m.url_arc3,m.url_arc4,m.url_arc5
			FROM web_msg m
			INNER JOIN web_msg_des md ON md.id_msg=m.id_msg AND id_des='$id_des' AND md.est_msg <> 'e' $tip_des
			ORDER BY fec_msg DESC, hor_msg DESC) w
			INNER JOIN(
			SELECT DISTINCT *
			FROM (
			SELECT id_per, nom_per,ape_per,'s' tipo
			FROM persona UNION
			SELECT id_usu AS id_per, nom_per,ape_per,'w' tipo
			FROM web_usu UNION
			SELECT id_alu AS id_per, nom_per,ape_per,'e' tipo
			FROM alumno
					) sel
					) p ON id_per = id_rem AND tipo=w.tip_usu
			LEFT JOIN alumno a ON a.id_alu=w.id_alu
			LEFT JOIN alumcurso ac ON ac.id_alu=a.id_alu
			LEFT JOIN curso c ON c.cod_cur=ac.cod_cur
			ORDER BY fec_msg DESC, hor_msg DESC");
        //$stmt->bind_param("i", $id_tra);

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar mensajes enviados
     */
    public function getEnviados($id_rem)
    {
        $stmt = $this->conn->prepare("SELECT
					w.id_msg,fec_msg,asu_msg,txt_msg,hor_msg, CONCAT(a.ape_per,' ',a.nom_per, IF(des_cur IS NOT NULL, CONCAT(' - ',des_cur),' ')) AS nom_est, IF(d.id_des IS NULL,w.res_msg, CONCAT(sel2.ape_per,' ',sel2.nom_per)) AS rem,tip_usu,tip_des,w.url_arc,w.url_arc2,w.url_arc3,w.url_arc4,w.url_arc5
			FROM
					(
			SELECT *
			FROM web_msg m
			WHERE id_rem='$id_rem' AND est_msg <> 'e'
			ORDER BY fec_msg DESC, hor_msg DESC) w
			LEFT JOIN web_msg_des d ON tipe_msg='u' AND d.id_msg=w.id_msg
			LEFT JOIN (
			SELECT DISTINCT *
			FROM (
			SELECT id_alu AS id_des, ape_per, nom_per,'e' tipo
			FROM alumno UNION
			SELECT id_per AS id_des, ape_per, nom_per,'s' tipo
			FROM persona UNION
			SELECT id_usu AS id_des, ape_per, nom_per,'w' tipo
			FROM web_usu 
						)dest	
					)sel2 ON d.id_des = sel2.id_des AND tipo=d.tip_des
			LEFT JOIN alumno a ON a.id_alu=d.id_alu
			LEFT JOIN alumcurso ac ON ac.id_alu=a.id_alu
			LEFT JOIN curso c ON c.cod_cur=ac.cod_cur
			ORDER BY fec_msg DESC, hor_msg DESC");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar mensajes archivados
     */
    public function getArchivados($id_des)
    {
        $stmt = $this->conn->prepare("SELECT
					w.id_msg,fec_msg,asu_msg,txt_msg,hor_msg, CONCAT(p.ape_per,' ',p.nom_per) AS rem, CONCAT(a.ape_per,' ',a.nom_per, IF(des_cur IS NOT NULL, CONCAT(' - ',des_cur),' ')) AS nom_est,tip_usu,tip_des,w.url_arc,w.url_arc2,w.url_arc3,w.url_arc4,w.url_arc5
			FROM
				(
				SELECT m.id_msg, id_rem, asu_msg, txt_msg, DATE_FORMAT(fec_emsg, '%Y-%m-%d') AS fec_msg, DATE_FORMAT(fec_emsg, '%H:%i:%s') AS hor_msg, ip_msg, pri_msg, tipe_msg, res_msg, id_des, id_alu, md.est_msg,tip_usu,tip_des,url_arc,url_arc2,url_arc3,url_arc4,url_arc5
				FROM web_msg m
				INNER JOIN web_msg_des md ON md.id_msg=m.id_msg AND id_des='$id_des' AND md.est_msg = 'e'
				ORDER BY fec_emsg DESC) w
			INNER JOIN(
			SELECT DISTINCT *
			FROM (
			SELECT id_per, nom_per,ape_per,'s' tipo
			FROM persona UNION
			SELECT ced_per AS id_per, nom_per,ape_per,'w' tipo
			FROM web_usu UNION
			SELECT id_alu AS id_per, nom_per,ape_per,'e' tipo
			FROM alumno
					) sel
					) p ON id_per = id_rem AND tipo=w.tip_usu
			LEFT JOIN alumno a ON a.id_alu=w.id_alu
			LEFT JOIN alumcurso ac ON ac.id_alu=a.id_alu
			LEFT JOIN curso c ON c.cod_cur=ac.cod_cur");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }



    /**
     * Consultar actividades por parte del acudiente
     */
    public function getActividadesAcu($id_per, $fec_ini, $fec_fin)
    {
        $id_alu = $id_per;
        $stmt = $this->conn->prepare("SELECT distinct CONCAT(a.ape_per,' ',a.nom_per) AS nom_est, TIME_FORMAT(hor_act,'%h:%i %p') AS hor_act,
                    fec_act,
                    des_tact,
                    des_act,
                    des_mat,
                    prof,
            enlace,
            url_arc,
            arc_act
        FROM web_alumreg w
        INNER JOIN alumno a ON a.id_alu=w.id_alu
        INNER JOIN web_usu u ON u.id_usu=w.id_usu
        INNER JOIN (
        SELECT
                        a2.id_alu,
                        a2.hor_act,
                        a2.fec_act,
                        ta.des_tact,
                        a2.des_act,
                        m.des_mat, CONCAT(p.nom_per,' ', p.ape_per) AS prof,
            a2.enlace,
                        a2.url_arc,
            a2.arc_act
        FROM web_actividades a2
        INNER JOIN web_tipactividad ta ON ta.id_tact=a2.id_tact
        INNER JOIN alumcurso ac ON ac.id_alu=a2.id_alu
        INNER JOIN matgra mg ON mg.cod_cur=ac.cod_cur AND mg.cod_mat=a2.cod_mat
        INNER JOIN materia m ON m.cod_mat=mg.cod_mat
        INNER JOIN persona p ON p.id_per=mg.id_per
        WHERE
                        a2.fec_act BETWEEN '$fec_ini' AND '$fec_fin' 
                                
                            /*  UNION
        SELECT
                        ma.id_alu,
                        '00:00',
                        '',
                        'ACADÉMICA',
                        i.des_ins,
                        m.des_mat, CONCAT(p.nom_per,' ', p.ape_per) AS prof,'','',''
        FROM 
                        
                        inst_mat_cur imc
        INNER JOIN (
        SELECT id_alu,mg.cod_cur,cod_mat
        FROM matgra mg
        INNER JOIN alumcurso ac ON ac.cod_cur= mg.cod_cur
        WHERE esp_mat='n' UNION
        SELECT id_alu,cod_cur,cod_mat
        FROM alum_mat_esp 
                        ) ma ON ma.cod_cur=imc.cod_cur AND ma.cod_mat=imc.cod_mat
        INNER JOIN instancia i ON i.cod_ins=imc.cod_ins
        INNER JOIN materia m ON m.cod_mat=ma.cod_mat
        INNER JOIN matgra mg ON mg.cod_cur=imc.cod_cur AND mg.cod_mat=imc.cod_mat
        INNER JOIN persona p ON p.id_per=mg.id_per
        WHERE
                        fec_rea BETWEEN $fec_ini AND $fec_fin*/
                        
                    ) c ON c.id_alu=w.id_alu
        WHERE
                    w.id_alu='$id_alu'    
        ORDER BY hor_act");

        $res = array();

        if ($stmt->execute()) {

            $res = $stmt->get_result();

            $stmt->close();


            //	$res = $stmt->get_result();
            // $stmt->close(); 
            // print_r($res);
            //$res2 = adjEnl($id_alu,$fec_ini,$fec_fin); 
            //array_push($respuesta,$res,$res2);
            //print_r($res);
            return $res;
        } else {
            return NULL;
        }
    }

    public function getTarOrForos($cod_cur)
    {
        $stmt = $this->conn->prepare("select CONCAT((case when cda.tipo='f' then 'FORO' ELSE 'TRABAJO' END),'-',cda.nom_actividad) AS nom_tarea, cda.fecha_fin,cda.hora_fin, m.des_mat, ca.cod_cur, cda.cod_gra, m.cod_mat,cda.id_usu, ca.id_per, cda.id_actividad
from cd_actividad cda  
join cd_curso_actividad ca on cda.id_actividad = ca.id_actividad
join materia m on m.cod_mat=ca.cod_mat
where ca.cod_cur=$cod_cur 
order by fecha_fin,hora_fin
");


        $res = array();

        if ($stmt->execute()) {

            $res = $stmt->get_result();

            $stmt->close();

            return $res;
        } else {
            return NULL;
        }
    }

    public function adjEnlActividadesEst($id_alu, $fec_ini, $fec_fin)
    {

        $stmt = $this->conn->prepare("
            SELECT distinct CONCAT(a.ape_per,' ',a.nom_per) AS nom_est, TIME_FORMAT(hor_act,'%h:%i %p') AS hor_act,
                    fec_act,
                    des_tact,
                    des_act,
                    des_mat,
                    prof, 
                          enlace,
                          url_arc
        FROM web_alumreg w
        INNER JOIN alumno a ON a.id_alu=w.id_alu
        INNER JOIN web_usu u ON u.id_usu=w.id_usu
        INNER JOIN (
        SELECT
                        a2.id_alu,
                        a2.hor_act,
                        a2.fec_act,
                        ta.des_tact,
                        a2.des_act,
                        m.des_mat, CONCAT(p.nom_per,' ', p.ape_per) AS prof,
                        a2.enlace,
                        a2.url_arc
        FROM web_actividades a2
        INNER JOIN web_tipactividad ta ON ta.id_tact=a2.id_tact
        INNER JOIN alumcurso ac ON ac.id_alu=a2.id_alu
        INNER JOIN matgra mg ON mg.cod_cur=ac.cod_cur AND mg.cod_mat=a2.cod_mat
        INNER JOIN materia m ON m.cod_mat=mg.cod_mat
        INNER JOIN persona p ON p.id_per=mg.id_per
        WHERE
                        a2.fec_act BETWEEN '$fec_ini' AND '$fec_fin' 
            UNION
        SELECT
                        ma.id_alu,
                        '00:00',
                        '',
                        'ACADÃ‰MICA',
                        i.des_ins,
                        m.des_mat, CONCAT(p.nom_per,' ', p.ape_per) AS prof,'',''
        FROM 
                        
                        inst_mat_cur imc
        INNER JOIN (
        SELECT id_alu,mg.cod_cur,cod_mat
        FROM matgra mg
        INNER JOIN alumcurso ac ON ac.cod_cur= mg.cod_cur
        WHERE esp_mat='n' UNION
        SELECT id_alu,cod_cur,cod_mat
        FROM alum_mat_esp 
                        ) ma ON ma.cod_cur=imc.cod_cur AND ma.cod_mat=imc.cod_mat
        INNER JOIN instancia i ON i.cod_ins=imc.cod_ins
        INNER JOIN materia m ON m.cod_mat=ma.cod_mat
        INNER JOIN matgra mg ON mg.cod_cur=imc.cod_cur AND mg.cod_mat=imc.cod_mat
        INNER JOIN persona p ON p.id_per=mg.id_per
        WHERE
                        fec_rea BETWEEN '$fec_ini' AND '$fec_fin'
                        
                    ) c ON c.id_alu=w.id_alu
        WHERE
                    w.id_alu=$id_alu 
        ORDER BY hor_act");


        $res = array();

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            return $res;
        } else {
            return NULL;
        }
    }



    /**
     * Recuperar mensaje para archivarlo
     */
    public function archivarMensaje($id_msg, $id_des, $tip_des)
    {
        $stmt = $this->conn->prepare("UPDATE web_msg_des SET est_msg = 'e' WHERE id_msg = ? AND id_des = ? AND tip_des = ?");
        $stmt->bind_param("iss", $id_msg, $id_des, $tip_des);

        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }


    /**
     * Consultar mensaje a responder
     */
    public function recuperarMensaje($id_msg)
    {
        $stmt = $this->conn->prepare("SELECT * FROM web_msg WHERE id_msg = ?");
        $stmt->bind_param("i", $id_msg);

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar alumno relacionado al mensaje
     */
    public function getAlumnoMSG($id_msg)
    {
        $stmt = $this->conn->prepare("SELECT a.* FROM alumno a INNER JOIN web_msg_des w ON w.id_alu = a.id_alu AND w.id_msg=?");
        $stmt->bind_param("i", $id_msg);

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar alumno relacionado al mensaje
     */
    public function infoEstMSG($id_msg, $id_des, $tip_des)
    {
        $stmt = $this->conn->prepare("SELECT * FROM web_msg_des WHERE id_msg = ? AND id_des = ? AND tip_des = ?");
        $stmt->bind_param("iss", $id_msg, $id_des, $tip_des);

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar lsitado de destinatarios
     */
    public function getDestinatariosMSG($id_msg)
    {
        $stmt = $this->conn->prepare("SELECT nom_per,ape_per,des_tfun,id_msg,est_msg, IF(est_msg='l',fec_lmsg,fec_emsg) AS fecha
			FROM web_msg_des w
			INNER JOIN persona p ON p.id_per=w.id_des
			INNER JOIN ususis u ON u.id_per=p.id_per
			INNER JOIN web_tipofun wt ON wt.id_tfun=u.id_tfun
			WHERE id_msg = $id_msg");
        //$stmt->bind_param("i", $id_msg);

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar remitente del mensaje
     */
    public function getRemitente($tip_des, $id_rem, $ced_per = '')
    {
        if ($tip_des == 'e') {
            $stmt = $this->conn->prepare("SELECT *
			FROM (
			SELECT id_usu, ced_per, nom_per, ape_per, mail_per, tel_per, tel2_per, usu_per, psw_per, est_per, fec_ing, hra_ing, ip_ing, id_tusu,est_usu
			FROM web_usu UNION
			SELECT a.id_alu AS id_usu, a.id_alu AS ced_per, nom_per,	ape_per, mail_alu AS mail_per, tel_con AS tel_per,tel2_con AS tel2_per,	a.id_alu AS usu_per, con_alu AS psw_per,	est_alu AS est_per,	'' AS fec_ing,'' AS hor_ing,'' AS ip_ing,5 AS id_tusu,est_alu AS est_usu
			FROM alumno a
			INNER JOIN info_adi_alu i ON i.id_alu=a.id_alu AND a.est_alu='h' 
							) sel
			WHERE ced_per LIKE '$id_rem' AND id_usu LIKE '$id_rem'");

            if ($stmt->execute()) {
                $res = $stmt->get_result()->fetch_assoc();
                return $res;
                $stmt->close();
            } else {
                return NULL;
            }

            //$rem=$rem[0];
        }
        if ($rem == NULL) {
            if ($id_rem != '')
                $stmt = $this->conn->prepare("SELECT * FROM persona WHERE id_per = '$id_rem'");
            else
                $stmt = $this->conn->prepare("SELECT * FROM persona WHERE ced_per = '$ced_per'");
        }

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * crear un mensaje
     * @param String 
     */
    public function crearWebMsg($id_rem = '', $asu_msg = '', $txt_msg = '', $fec_msg = '', $hor_msg = '', $ip_msg = '', $est_msg = '', $pri_msg = '', $tipe_msg, $res_msg = '', $tip_usu = '', $url_arc = '')
    {
        $stmt = $this->conn->prepare("INSERT INTO web_msg (id_rem, asu_msg, txt_msg, fec_msg, hor_msg, ip_msg, est_msg, pri_msg, tipe_msg, res_msg, tip_usu,url_arc) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssssssss", $id_rem, $asu_msg, $txt_msg, $fec_msg, $hor_msg, $ip_msg, $est_msg, $pri_msg, $tipe_msg, $res_msg, $tip_usu, $url_arc);

        $result = $stmt->execute();
        $id_msg = $this->conn->insert_id;
        $stmt->close();

        if ($result) {
            return $id_msg;
        } else {
            return NULL;
        }
    }


    /**
     * crear un mensaje
     * @param String 
     */
    public function registraDestinatario($id_msg, $id_des, $id_alu, $est_msg, $tip_des)
    {
        $stmt = $this->conn->prepare("INSERT INTO web_msg_des (id_msg,id_des,id_alu,est_msg,tip_des) VALUES(?,?,?,?,?)");
        $stmt->bind_param("isiss", $id_msg, $id_des, $id_alu, $est_msg, $tip_des);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            return false;
        }
    }



    /**
     * Consultar estudiantes relacionados con el acudiente
     */
    public function getEstudiantesAcu($usuario_id)
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT a.id_alu AS id, CONCAT(a.ape_per,' ',a.nom_per) AS label
			FROM alumno a
			INNER JOIN web_alumreg wa ON wa.id_usu = ? AND wa.id_alu=a.id_alu");
        $stmt->bind_param("i", $usuario_id);

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar listado destinatarios profesores
     */
    public function getProfesores($id_alu)
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT a.id_per AS id, CONCAT(ape_per,' ',nom_per,' [',des_mat,']') AS label,
				a.id_per id_usuario, 's' tipo_usuario, nom_per, ape_per,'' cargo, 
				des_mat mats, 
				'' grad, id_alu
			FROM persona a
			INNER JOIN profesor USING(id_per)
			INNER JOIN matgra b USING(id_per)
			INNER JOIN materia d USING(cod_mat)
			INNER JOIN alumcurso c USING(cod_cur)
			WHERE id_alu = $id_alu AND esp_mat='n' UNION
			SELECT a.id_per AS id, CONCAT(ape_per,' ',nom_per,' [',des_mat,']') AS label,
				a.id_per id_usuario, 's' tipo_usuario, nom_per, ape_per,'' cargo, 
				des_mat mats, 
				'' grad, id_alu
			FROM matgra b
			INNER JOIN persona a USING(id_per)
			INNER JOIN profesor USING(id_per)
			INNER JOIN alum_mat_esp e USING(cod_mat)
			INNER JOIN materia d USING(cod_mat)
			WHERE id_alu = $id_alu AND b.esp_mat='s'
			ORDER BY label ASC");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar listado destinatarios funcionarios
     */
    public function getFuncionarios($id_alu)
    {
        $stmt = $this->conn->prepare("SELECT p.id_per AS id, CONCAT(p.ape_per,' ',p.nom_per,' [',des_tfun,']') AS label,
				id_per AS id_usuario, 's' tipo_usuario, nom_per, ape_per,cargo, 
				'' mats, 
				'' grad, '$id_alu' id_alu
			FROM ususis u
			INNER JOIN persona p USING(id_per)
			INNER JOIN profesor USING(id_per)
			INNER JOIN web_tipofun w ON w.id_tfun=u.id_tfun
			WHERE u.id_tfun <> 1 AND u.id_tfun <> 5
			ORDER BY label");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar faltas alumno
     */
    public function getFaltasAlum($id_alu, $fec_ini, $fec_fin)
    {
        $stmt = $this->conn->prepare("SELECT fhb.id_alu,fhb.fec_fal,fhb.tipo_f,fhb.just_fal,fhb.txtj_fal,m.des_mat
			FROM falta_histo_bien fhb
			INNER JOIN materia m ON m.cod_mat=fhb.cod_mat
			WHERE fhb.id_alu = $id_alu AND fhb.fec_fal BETWEEN '$fec_ini' AND '$fec_fin'");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar citaciones alumno
     */
    public function getCitacionesAlum($id_alu, $fec_ini, $fec_fin)
    {
        $stmt = $this->conn->prepare("SELECT cit.id_alu,cit.txt_cit,cit.fec_cit,cit.hor_cit,cit.lug_cit,p.nom_per,p.ape_per,m.des_mat
			FROM citacion cit
			INNER JOIN persona p ON p.id_per=cit.id_per
			INNER JOIN materia m ON m.cod_mat=cit.cod_mat
			WHERE id_alu = $id_alu AND fec_cit BETWEEN '$fec_ini' AND '$fec_fin'");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar salidas programadas alumno
     */
    public function getSalidasAlum($id_alu, $fec_ini, $fec_fin)
    {
        $stmt = $this->conn->prepare("SELECT *
			FROM salida
			WHERE id_alu = $id_alu AND fec_sal BETWEEN '$fec_ini' AND '$fec_fin'");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar el id_cur del estudiante
     */
    public function getCodCur($id_alu)
    {
        $stmt = $this->conn->prepare("SELECT *
			FROM alumcurso
			WHERE id_alu = $id_alu");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $cod_cur = $res['cod_cur'];
            $stmt->close();
            return $cod_cur;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar evaluaciones por estudiante
     */
    public function getEvaluAlumPer($id_alu, $cod_cur, $id_peri)
    {
        $stmt = $this->conn->prepare("SELECT a.*,i.des_ins
			FROM(
			SELECT p.id_peri,m.cod_mat,cod_inst,des_mat,des_mat_cor,nota,fec_cre,fec_mod
			FROM seguimiento s
			INNER JOIN materia m USING(cod_mat)
			INNER JOIN periodo p USING(id_peri)
			WHERE s.id_alu=$id_alu AND p.id_peri=$id_peri
			ORDER BY cod_mat,id_peri,cod_inst) a
			LEFT JOIN instancia i ON a.cod_inst=i.cod_ins");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            /*$regs=array();
			while($f=$res->fetch_assoc($res))
				$regs[$f['id_peri']][$f['cod_mat']][$f['cod_inst']]=$f;*/
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }


    /**
     * Consultar instancias institucionales
     */
    public function getInstInstitucionales()
    {
        $stmt = $this->conn->prepare("SELECT val_var
			FROM _sapred
			WHERE nom_var LIKE '%INSTANCIAS_INSTITUCIONALES%'");

        if ($stmt->execute()) {
            //$stmt->bind_result($val_var);
            $res = $stmt->get_result()->fetch_assoc();
            /*$regs=array();
			while($f=$res->fetch_assoc($res))
				$regs[$f['id_peri']][$f['cod_mat']][$f['cod_inst']]=$f;*/
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }


    /**
     * Consultar materias del estudiante
     */
    public function getMateriasAlum($id_alu)
    {
        $stmt = $this->conn->prepare("SELECT a.id_alu,m.cod_mat,m.des_mat,p.nom_per,p.ape_per
			FROM alumno a
			JOIN alum_grado ag ON a.id_alu=ag.id_alu
			JOIN grado g ON ag.cod_gra= g.cod_gra
			JOIN alumcurso ac ON a.id_alu=ac.id_alu
			JOIN curso c ON ac.cod_cur=c.cod_cur
			join matgra mg on mg.cod_cur=c.cod_cur
			join materia m on m.cod_mat=mg.cod_mat
			join persona p on p.id_per=mg.id_per
			WHERE a.id_alu = $id_alu");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }


    /**
     * Consultar notas definitivas de materias por periodo 
     */
    public function getDefMatPer($id_alu, $cod_cur, $id_peri)
    {
        $stmt = $this->conn->prepare("SELECT p.id_peri,mg.cod_mat,p.des_per,m.des_mat,sf.nota,sf.nota_d,sf.nota_r,a.fj,a.fnj+ IF(b.fnj1>0,b.fnj1,0) AS fnj,
				a.rj,a.rnj
			FROM matgra mg
			INNER JOIN materia m ON m.cod_mat=mg.cod_mat AND mg.cod_cur=$cod_cur
				/*INNER JOIN inst_mat_cur imc ON imc.cod_cur=mg.cod_cur AND imc.cod_mat=mg.cod_mat*/
			LEFT JOIN seg_final sf ON sf.cod_cur=mg.cod_cur AND sf.cod_mat=mg.cod_mat AND id_alu=$id_alu
			INNER JOIN periodo p ON p.id_peri=sf.id_peri AND p.id_peri=$id_peri
			LEFT JOIN 
				(
			SELECT id_peri,cod_mat, COUNT(IF(f.tipo_f='f' AND f.just_fal='s',1, NULL)) AS fj, COUNT(IF(f.tipo_f='f' AND f.just_fal<>'s',1, NULL)) AS fnj, COUNT(IF(f.tipo_f='r' AND f.just_fal='s',1, NULL)) AS rj, COUNT(IF(f.tipo_f='r' AND f.just_fal<>'s',1, NULL)) AS rnj
			FROM falta_histo_bien f
			INNER JOIN periodo p ON (f.fec_fal>p.fec_ini AND f.fec_fal < p.fec_cor_tot)
			WHERE id_alu=$id_alu
			GROUP BY id_peri,cod_mat) a ON sf.cod_mat=a.cod_mat AND sf.id_peri=a.id_peri
			LEFT JOIN 
				 (
			SELECT id_peri,cod_mat, COUNT(*) AS fnj1
			FROM falta_histo_mat fhm
			INNER JOIN periodo p ON (fhm.fec_fal>p.fec_ini AND fhm.fec_fal < p.fec_cor_tot)
			WHERE id_alu=$id_alu
			GROUP BY id_peri,cod_mat) b ON sf.cod_mat=b.cod_mat AND sf.id_peri=b.id_peri
			ORDER BY p.id_peri,mg.cod_mat");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return NULL;
        }
    }

    /**
     * Consultar periodos cerrados y actual
     */
    public function getPeriodos()
    {
        $stmt = $this->conn->prepare("SELECT id_peri,des_per
			FROM periodo
			WHERE per_not_fin='n' AND per_fin='n' AND per_cer='s' AND boletin='s'
			ORDER BY fec_ini");

        /*$stmt = $this->conn->prepare("SELECT DISTINCT p.id_peri,p.des_per
			FROM periodo p
			INNER JOIN seguimiento s USING(id_peri)
			WHERE id_alu=$id_alu
			ORDER BY fec_ini");*/

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar periodos con notas finales
     */
    public function getPeriodosDef($id_alu)
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT p.*
			FROM periodo p
			INNER JOIN seg_final s USING(id_peri)
			WHERE id_alu=$id_alu AND per_cer='s'
			ORDER BY id_peri");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $regs = array();
            while ($f = $res->fetch_assoc())
                $regs[$f['id_peri']] = $f;
            $stmt->close();
            return $regs;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar notas definitivas por cada periodo
     */
    public function getNotasPeri($id_alu)
    {
        $stmt = $this->conn->prepare("SELECT cod_mat,id_peri,des_mat,des_mat_cor,nota_d
			FROM seg_final sf
			INNER JOIN materia m USING(cod_mat)
			INNER JOIN periodo p USING(id_peri)
			WHERE cod_mat<>0 AND sf.id_alu=$id_alu
			ORDER BY cod_mat,id_peri");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $regs = array();
            while ($f = $res->fetch_assoc()) {
                $regs[$f['cod_mat']][$f['id_peri']] = $f;
            }
            $stmt->close();
            return $regs;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar materias del estudiante
     */
    public function getMatsAlu2($id_alu)
    {
        $stmt = $this->conn->prepare("SELECT *
			FROM (
			SELECT m.*,mg.inh_mat AS inh_mat1,mg.sem_mat, CONCAT(p.nom_per,' ',p.ape_per) AS profe
			FROM materia m
			INNER JOIN area a USING(cod_are)
			INNER JOIN matgra mg USING(cod_mat)
			JOIN persona p ON p.id_per=mg.id_per
			INNER JOIN alumcurso ac USING(cod_cur)
			WHERE id_alu=$id_alu AND mg.esp_mat='n' UNION
			SELECT m.*,mg.inh_mat AS inh_mat1,mg.sem_mat, CONCAT(p.nom_per,' ',p.ape_per) AS profe
			FROM materia m
			INNER JOIN area a USING(cod_are)
			INNER JOIN alum_mat_esp am USING(cod_mat)
			INNER JOIN alumcurso ac USING(id_alu,cod_cur)
			INNER JOIN matgra mg USING(cod_mat,cod_cur)
			JOIN persona p ON p.id_per=mg.id_per
			WHERE id_alu=$id_alu)a
			ORDER BY cod_mat");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $regs = array();
            while ($f = $res->fetch_assoc())
                $regs[$f['cod_mat']] = $f;
            $stmt->close();
            return $regs;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar la valoracion de la nota enviada
     */
    public function getValoracion($def)
    {
        $def = round($def, 1);
        $stmt = $this->conn->prepare("SELECT
			`rangos_valora`.`max`,
			`rangos_valora`.`min`,
			`tipvalora`.*
			FROM
						`tipvalora`
			INNER JOIN `rangos_valora` ON `rangos_valora`.`id_val` = `tipvalora`.`id_val`
			ORDER BY MIN");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            for ($i = 0; $filavalo = $res->fetch_assoc(); $i++) {
                $VALORACIONES[$i] = $filavalo;
            }
            foreach ($VALORACIONES as $filavalo) {
                if ($def >= $filavalo['min'] && $def <= $filavalo['max']) return $filavalo;
            }
            return NULL;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar bases de datos
     */
    public function getBasesDatos()
    {
        $stmt = $this->conn->prepare("select distinct table_schema bd from information_schema.columns where table_name like '_sapred'");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar colegio a partir de la bd
     */
    public function getColegio($bd)
    {
        $stmt = $this->conn->prepare("SELECT nom_ins FROM `" . $bd . "`.institucion");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar periodos disponibles para boletin informativo
     */
    public function getPeriodosBoletin()
    {
        $stmt = $this->conn->prepare("SELECT * FROM periodo
			WHERE per_cer = 's' AND per_not_fin = 'n' AND per_fin = 'n' AND boletin = 's'
			ORDER BY fec_ini");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar datos de la seccion del estdiante
     */
    public function getSeccion($id_alu)
    {
        $stmt = $this->conn->prepare("SELECT `curso`.*
		FROM `alumcurso`
		INNER JOIN `curso` USING(`cod_cur`)
		WHERE `id_alu` = $id_alu");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar datos del grado del estdiante
     */
    public function getGrado($cod_gra)
    {
        $stmt = $this->conn->prepare("SELECT *
			FROM grado
			WHERE cod_gra = $cod_gra");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }
	
	
	/*------------------------------------------------------------------------------------------------------------------*/
	/*------------------------------------------------------------------------------------------------------------------*/
	/*------------------------------------------------------------------------------------------------------------------*/
	/*---------------------------------RUTAS PARA API DESDE EL PERFIL PROFESOR------------------------------------------*/
	/*------------------------------------------------------------------------------------------------------------------*/
	/*------------------------------------------------------------------------------------------------------------------*/
	/*------------------------------------------------------------------------------------------------------------------*/

    /**
     * Consultar asignaturas asignadas al profe
     */
    public function cargarAsignaturas($id_per)
    {
        $stmt = $this->conn->prepare("SELECT CONCAT(c.des_cur,', ',s.nom_sed,', ',j.des_jor,' - ',m.des_mat) AS label, CAST(CONCAT(g.cod_niv,',',g.cod_gra,',',c.cod_cur,',',m.cod_mat) AS CHAR) AS id,ord_gra,nor_are
		FROM matgra mg
		INNER JOIN curso c USING(cod_cur)
		INNER JOIN grado g USING(cod_gra)
		INNER JOIN sede s USING(id_sed)
		INNER JOIN jornada j USING(id_jor)
		INNER JOIN materia m USING(cod_mat)
		INNER JOIN area a USING(cod_are)
		WHERE mg.id_per='$id_per'
		ORDER BY ord_gra,label");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar periodos activos para subir notas
     */
    public function cargarPeriodos($id_per, $cod_cur, $cod_mat)
    {
        $stmt = $this->conn->prepare("SELECT id_peri,des_per,fec_ini,fec_cor_pro,fec_cor_tot
		FROM periodo p
		WHERE per_fin='n' AND p.actual='s'
		ORDER BY fec_ini");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar si hay materias especiales
     */
    public function getEsp_mat($cod_cur, $cod_mat)
    {
        $stmt = $this->conn->prepare("SELECT matgra.esp_mat
		FROM anolectivo
		INNER JOIN matgra USING(id_ano)
		WHERE
		anolectivo.actual = 's' AND
		matgra.cod_mat = $cod_mat AND
		matgra.cod_cur = $cod_cur");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res['esp_mat'];
        } else {
            return NULL;
        }
    }




    /**
     * Consultar estudiantes de la materia seleccionada para subir notas
     */
    public function cargarEstudiantes($id_per, $cod_niv, $cod_cur, $cod_mat, $esp, $id_peri, $cod_gra)
    {
        if ($esp == "n") {
            $stmt = $this->conn->prepare("SELECT CONCAT(alumno.ape_per,' ',alumno.nom_per) AS nom, CONCAT(alumno.nom_per, CASE alumno.est_alu WHEN 'n' THEN ' <strong>(R)</strong>' ELSE '' END) AS nom_per,
			alumno.ape_per,
			alumno.id_alu,
			alumno.ufo_alu,
			alumno.est_alu
			FROM alumcurso
			INNER JOIN alumno USING(id_alu)
			INNER JOIN anolectivo USING(id_ano)
			INNER JOIN matgra mtg ON mtg.cod_cur = $cod_cur
			WHERE alumno.est_alu='h' AND 
			alumcurso.cod_cur = $cod_cur AND mtg.cod_mat=$cod_mat AND
			anolectivo.actual = 's'
			ORDER BY nom ASC");
        } else { //materias especiales			
            $stmt = $this->conn->prepare("SELECT DISTINCT
				CONCAT(ape_per,' ',nom_per) as nom,
				id_alu,
				nom_per,
				ape_per,
				ufo_alu,
				est_alu,
				f.injust num_hor,
				f.just
			FROM
				anolectivo
				INNER JOIN alum_mat_esp USING(id_ano)
				INNER JOIN matgra mg USING(cod_cur,cod_mat)
				INNER JOIN curso c USING(cod_cur)
				INNER JOIN alumno USING(id_alu)
				LEFT JOIN (
					SELECT fm.*,p.id_peri,sum(if(jus_fal='just',num_hor,0)) just ,sum(if(jus_fal='injust',num_hor,0)) injust
					FROM falta_histo_mat fm 
					INNER JOIN periodo p on p.id_peri=$id_peri AND fec_fal BETWEEN p.fec_ini AND p.fec_cor_tot 
					AND tip_fal='t' AND cod_mat=$cod_mat
					group by id_alu,cod_cur,cod_mat,fec_fal
				) f USING(id_alu,cod_mat,id_peri)  
			WHERE alumno.est_alu='h' AND 
				actual = 's' AND
				esp_mat = 's' AND
				cod_gra = $cod_gra AND
				cod_mat = $cod_mat AND
				id_peri = $id_peri AND c.cod_cur=$cod_cur " . ($id_per != '' ? ' AND mg.id_per=' . $id_per : '') . "
			ORDER BY nom ASC");
        }

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar instancias de la materia seleccionada para subir notas
     */
    public function cargarInstancias($id_per, $id_peri, $cod_cur, $cod_mat, $des_cor_ins = 'EVA')
    {
        /*if($cod_cur!='%')
			$rest_cur=" AND cod_cur=$cod_cur";
		else
			$rest_cur="";
		if($cod_mat!='%')
			$rest_mat=" AND cod_mat=$cod_mat";
		else
			$rest_mat="";
		
		$qNRM="SELECT * FROM _sapred WHERE nom_var='NIVEL_REGISTRO_MODELO'";//variable tipo de registro instancia de evaluaciÃ³n		
		$qNRE="SELECT * FROM _sapred WHERE nom_var='NIVEL_REGISTRO_EVALUACION'";//variable tipo de registro instancia de evaluaciÃ³n
		$qOGN="SELECT * FROM _sapred WHERE nom_var='ORDEN_GRILLA_NOTAS'";//variable Orden de PresentaciÃ³n Grilla Notas"
		$qCNS="SELECT val_var FROM _sapred WHERE nom_var='COLORES_NIVELES_SEGUIMIENTO'";
		$qCNSi="SELECT val_var FROM _sapred WHERE nom_var='COLORES_NIVELES_SEGUIMIENTO_ITEM'";*/

        try {
            /*$stmt = $this->conn->prepare($qNRE);
			$stmt->execute();
			$fNRE = $stmt->get_result()->fetch_assoc();
			
			$stmt = $this->conn->prepare($qOGN);
			$stmt->execute();
			$fOGN = $stmt->get_result()->fetch_assoc();
			
			$stmt = $this->conn->prepare($qCNS);
			$stmt->execute();
			$fCNS = $stmt->get_result()->fetch_assoc();
			
			$stmt = $this->conn->prepare($qCNSi);
			$stmt->execute();
			$fCNSi = $stmt->get_result()->fetch_assoc();
			
			$stmt = $this->conn->prepare($qNRM);
			$stmt->execute();
			$fNRM = $stmt->get_result()->fetch_assoc();*/


            //$rsv=$con->query("select * from _sapred order by nom_var");
            $stmt = $this->conn->prepare("select * from _sapred order by nom_var");
            $stmt->execute();
            $rsv = $stmt->get_result();
            while ($fvr = $rsv->fetch_assoc()) {
                //while($fvr=$con->fetch($rsv)){
                $obj = json_decode($fvr['val_var']);
                if ($obj) $VARUNICA[$fvr['nom_var']] = $obj;
                else  $VARUNICA[$fvr['nom_var']] = $fvr['val_var'];
            }


            /*$colores=json_decode($fCNS[0],true);
			$coloresItem=$fCNSi['val_var'];
			$nivel_registro_modelo=$fNRM['val_var'];
			$nivel_registro_eval=$fNRE['val_var'];
			$campoOrden=$fOGN['val_var'];
			$cadC="";
			$cadT="";	
			if($nivel_registro_modelo<$nivel_registro_eval){
				
				if($nivel_registro_eval ==2){
					$cadC.=", cod_ind,cod_log, NULL id_niv0";
					$cadT.=" JOIN indicadores_seg using(cod_ind) ";
				}
				else
				{
					if( $nivel_registro_eval==3){
						$cadC.=", cod_ind,cod_log,id_niv0";
						$cadT.=" JOIN indicadores_seg using(cod_ind) ";
						$cadT.=" JOIN log_are_gra using(cod_log)";
					}
					else if($nivel_registro_eval==2){
						$cadC.=", cod_ind,cod_log,id_niv0";
						$cadT.=" JOIN log_are_gra using(cod_log)";
					}
				}
			}else{
				if($nivel_registro_modelo =3){
					$cadC.=",NULL cod_ind, NULL cod_log, NULL id_niv0";
				}
				else if($nivel_registro_modelo =2){
					$cadC.=" ,NULL cod_log, NULL id_niv0";
				}
				
			}
			
			if($nivel_registro_eval==0){
				$q="SELECT id_niv0 AS id_sup,des_niv0 AS des_sup, id_tniv, null cod_tem, null cod_ind, null cod_log, cod_niv0 FROM nivel0 a";
			}elseif($nivel_registro_eval==1){
				$q="SELECT cod_log AS id_sup,des_log AS des_sup, id_tniv, null cod_tem, null cod_ind, cod_log, cod_niv0 FROM log_are_gra a";
			}elseif($nivel_registro_eval==2){
				$q="SELECT cod_ind AS id_sup,des_ind AS des_sup, a.id_tniv, null cod_tem $cadC FROM indicadores_seg a $cadT";
			}elseif($nivel_registro_eval==3){
				$q="SELECT cod_tem AS id_sup,des_tem AS des_sup, a.id_tniv, cod_tem $cadC FROM tematica a $cadT ";
			}*/


            /*$query="SELECT * FROM(
			SELECT DISTINCT
			inst_mat_cur.cod_cur,
			inst_mat_cur.cod_mat,
			instancia.cod_ins,
			instancia.des_ins,
			ti.cod_tip_ins,
			ti.des_tip_ins, 
			GROUP_CONCAT(des_sup) AS des_sup,
			GROUP_CONCAT(des_tniv) AS des_tniv,
			";
			
			if(isset($VARUNICA['TITULO_TIPO_INSTANCIA']) && $VARUNICA['TITULO_TIPO_INSTANCIA']=='s'){
			$query.=" ti.des_tip_ins as des_cor_ins, ";	
			}else{
			$query.=" '' as des_cor_ins, ";
			}
			
			$query.=" ti.color,
			inst_mat_cur.pon_ins,
			t.cod_tem,
			t.cod_ind,
			t.cod_log,
			t.id_niv0
			FROM (SELECT cod_ins, des_ins,cod_tip_ins ,est_int  FROM  instancia )instancia
			INNER JOIN inst_mat_cur USING(cod_ins)
			LEFT JOIN instancia_relacion ir USING(cod_ins)
			LEFT JOIN ($q) t USING(id_sup)
			LEFT JOIN tipo_nivel USING(id_tniv) 
			INNER JOIN tipo_inst ti USING(cod_tip_ins)
			INNER JOIN matgra USING(cod_cur,cod_mat)
			WHERE
				inst_mat_cur.id_peri = $id_peri AND
				instancia.est_int = 'hab' AND matgra.id_per=$id_per
				$rest_cur
				$rest_mat
			GROUP BY
				inst_mat_cur.cod_cur,
				inst_mat_cur.cod_mat,
				instancia.cod_ins,
				instancia.des_ins,
				ti.cod_tip_ins,
				ti.des_tip_ins
			ORDER BY
				cod_cur,
				cod_mat,
				$campoOrden) a
			";
		
			$INST_INST=array();
			
			
			if(isset($VARUNICA['INSTANCIAS_INSTITUCIONALES'])){
				foreach($VARUNICA['INSTANCIAS_INSTITUCIONALES'] as $obj){
					if($obj->estado=='a'){
						$INST_INST[]=$obj;
						$query.="UNION 
						SELECT a.*,ti.color,imc.pon_ins,NULL cod_tem,NULL cod_ind, NULL cod_log, NULL id_niv0 FROM (
						SELECT  cod_cur,cod_mat,'$obj->codigo' as cod_ins,upper('$obj->titulo') as des_ins,
						'$obj->cod_tip_ins' AS cod_tip_ins,'' AS des_tip_ins,'' AS des_sup,'' AS des_tniv,'$obj->tit_cor' as des_cor_ins
						FROM matgra mg where id_per=$id_per   ".($cod_cur != '%' ? ' AND cod_cur='.$cod_cur:'').' '. ($cod_mat != '%' ? ' AND cod_mat='.$cod_mat:'')."
						)a
						
						LEFT JOIN tipo_inst ti USING(cod_tip_ins)
						LEFT JOIN inst_mat_cur imc USING(cod_ins,cod_cur,cod_mat) ";
					}
				}
			}else{ die('No estan configuradas las instancias institucionales ');}
			//echo ($query);*/
            $query = "SELECT *
			FROM(
			SELECT DISTINCT inst_mat_cur.cod_cur, inst_mat_cur.cod_mat, instancia.cod_ins, instancia.des_ins, ti.cod_tip_ins, ti.des_tip_ins
			FROM (
			SELECT cod_ins, des_ins,cod_tip_ins,est_int
			FROM instancia)instancia
			INNER JOIN inst_mat_cur USING(cod_ins)
			INNER JOIN tipo_inst ti USING(cod_tip_ins)
			INNER JOIN matgra USING(cod_cur,cod_mat)
			WHERE inst_mat_cur.id_peri = $id_peri AND instancia.est_int = 'hab' AND matgra.id_per=$id_per AND cod_cur=$cod_cur AND cod_mat=$cod_mat
			GROUP BY inst_mat_cur.cod_cur, inst_mat_cur.cod_mat, instancia.cod_ins, instancia.des_ins, ti.cod_tip_ins, ti.des_tip_ins
			ORDER BY cod_cur, cod_mat) a";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $r = $stmt->get_result();
            //$r=$con->query($query);



            //if($con->num_rows($r)==0)return NULL;
            //if($stmt->num_rows == 0)return NULL;
            //$c=0;
            //$cAnt='';$iCol=-1;
            $regs = array();
            while ($f = $r->fetch_assoc())
            //while($f=$con->fetch($r))
            {    //$c++;
                //if($f['des_cor_ins']=='')$f['des_cor_ins']=$des_cor_ins.' '.$c;
                //if($f['des_tniv']!='')$f['des_sup']=$f['des_tniv'].': '.$f['des_sup'];
                // si los colores son por nivel se cambia 'color',
                // caso contrario se usa los colores de tipo de instancia
                /*if($coloresItem !=''){
					if($cAnt!=$f[$coloresItem] || $f['cod_ins'] < 1){$cAnt=$f[$coloresItem];$iCol++;}// si el valor de item es diferente del anterior
						
					
					$f['color']=$colores[$iCol];//asigna color  dependiendo del valor q tenga en la columna $coloresItem (cod_log o cod_ind o cod_tem ) 
				}*/
                $regs[$f['cod_cur']][$f['cod_mat']][$f['cod_ins']] = $f;
            }
            //die(print_r($regs,true));
            return $regs;
        } catch (Exception $e) {
            throw ($e);
        }
    }


    /**
     * Consultar si hay notas registradas para el alumno en la materia seleccionada
     */
    public function getNotaSG($cod_cur, $cod_mat, $id_peri, $id_alu)
    {
        $stmt = $this->conn->prepare("SELECT
		id_alu, ROUND(s.nota,(
		SELECT val_var
		FROM _sapred
		WHERE nom_var='DECIMALES_SEGUIMIENTO')) AS nota,
		s.cod_inst AS cod_ins
		FROM
		seguimiento s
		INNER JOIN anolectivo a ON s.id_ano = a.id_ano
		WHERE
		s.cod_mat = $cod_mat AND
		s.id_peri = $id_peri AND
		s.cod_cur = $cod_cur AND
		s.id_alu = $id_alu AND
		a.actual = 's'");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar variables para nota max y min
     */
    public function getNotaMAXMIN()
    {
        $stmt = $this->conn->prepare("SELECT * FROM _sapred WHERE nom_var='NOTA_MAXIMA' OR nom_var='NOTA_MINIMA'");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar variable para saber si el colegio califica con letras o nmeros
     */
    public function getTipoEvaluacion()
    {
        $stmt = $this->conn->prepare("SELECT * FROM _sapred WHERE nom_var='TIPO_EVALUACION'");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consulta los tipos de valoraciones con letras
     */
    public function getTipValora()
    {
        $stmt = $this->conn->prepare("SELECT
			`rangos_valora`.`max`,
			`rangos_valora`.`min`,
			`tipvalora`.*
FROM
			`tipvalora`
INNER JOIN `rangos_valora` ON `rangos_valora`.`id_val` = `tipvalora`.`id_val`
ORDER BY MIN");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * actualizar o insertar nota segun los datos enviados
     */
    public function actualizarNota($id_alu, $cod_mat, $cod_cur, $id_peri, $id_usu, $cod_inst, $nota)
    {
        try {
            $stmt = $this->conn->prepare("SELECT id_ano FROM anolectivo WHERE actual = 's'");
            $stmt->execute();
            $res1 = $stmt->get_result()->fetch_assoc();
            $id_ano = $res1['id_ano'];

            $stmt = $this->conn->prepare("SELECT nota FROM `seguimiento` WHERE `id_alu`=$id_alu AND `cod_mat`=$cod_mat AND `cod_cur`=$cod_cur AND `id_peri`=$id_peri AND `cod_inst`=$cod_inst AND `id_ano`=$id_ano");
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                if ($nota != '') {
                    //$fec_mod = date('Y-m-d H:i:s');
                    $query = "UPDATE `seguimiento` SET `nota`=$nota, `id_usu_mod`=$id_usu WHERE `id_alu`=$id_alu AND `cod_mat`=$cod_mat AND `cod_cur`=$cod_cur AND `id_peri`=$id_peri AND `cod_inst`=$cod_inst AND `id_ano`=$id_ano";
                } else {
                    $query = "DELETE FROM `seguimiento` WHERE `id_alu`=$id_alu AND `cod_mat`=$cod_mat AND `cod_cur`=$cod_cur AND `id_peri`=$id_peri AND `cod_inst`=$cod_inst AND `id_ano`=$id_ano";
                }
                $stmt = $this->conn->prepare($query);
                if ($stmt->execute()) {
                    $stmt->close();
                    return true;
                } else {
                    $stmt->close();
                    return false;
                }
            } else {
                if ($nota != '') {
                    $fec_cre = date('Y-m-d H:i:s');
                    $stmt = $this->conn->prepare("INSERT INTO seguimiento (id_alu,cod_mat,cod_cur,id_peri,cod_inst,id_ano,nota,id_usu_cre,fec_cre) values (?,?,?,?,?,?,?,?,?)");
                    if ($stmt === FALSE) {
                        die($this->conn->error);
                    } else {
                        $stmt->bind_param("iiiiiidis", $id_alu, $cod_mat, $cod_cur, $id_peri, $cod_inst, $id_ano, $nota, $id_usu, $fec_cre);
                    }
                    $result = $stmt->execute();
                    $stmt->close();
                    if ($result) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    /**
     * Consultar si hay nota registrada para el alumno en la instancia enviada
     */
    public function getNotaInstSG($cod_cur, $cod_mat, $id_peri, $id_alu, $cod_inst)
    {
        $stmt = $this->conn->prepare("SELECT
		id_alu, ROUND(s.nota,(
		SELECT val_var
		FROM _sapred
		WHERE nom_var='DECIMALES_SEGUIMIENTO')) AS nota,
		s.cod_inst AS cod_ins
		FROM
		seguimiento s
		INNER JOIN anolectivo a ON s.id_ano = a.id_ano
		WHERE
		s.cod_mat = $cod_mat AND
		s.id_peri = $id_peri AND
		s.cod_cur = $cod_cur AND
		s.id_alu = $id_alu AND
		s.cod_inst = $cod_inst AND
		a.actual = 's'");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar las observaciones registradas para el alumno enviado
     */
    public function getObservaciones($id_alu, $cod_cur, $cod_mat)
    {
        $stmt = $this->conn->prepare("SELECT id_alu, noti_obe, id_obe, des_cor_obe, des_obe, id_peri, fec_ini_obe, id_per, des_toe, des_per, fec_ing_obe, cod_mat, m.des_mat_cor, p.ape_per, p.nom_per
		FROM obserestu
		INNER JOIN materia m USING(cod_mat)
		LEFT JOIN tipobsest USING(cod_toe)
		LEFT JOIN hisobsest USING(id_obe)
		LEFT JOIN persona p USING(id_per)
		LEFT JOIN periodo USING(id_peri)
		WHERE `id_alu`=$id_alu AND `cod_cur` =$cod_cur AND `cod_mat`=$cod_mat
		ORDER BY id_obe DESC");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar descripcion de curso y materia para observaciones
     */
    public function getCursoMateria($cod_cur, $cod_mat)
    {
        $stmt = $this->conn->prepare("SELECT c.des_cur,m.des_mat
		FROM matgra mg
		INNER JOIN curso c USING(cod_cur)
		INNER JOIN grado g USING(cod_gra)
		INNER JOIN materia m USING(cod_mat)
		INNER JOIN area a USING(cod_are)
		WHERE c.cod_cur=$cod_cur AND m.cod_mat=$cod_mat
		ORDER BY ord_gra");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar tipos de observaciones
     */
    public function getTiposObs()
    {
        $stmt = $this->conn->prepare("SELECT * FROM tipobsest");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * actualizar o insertar nota segun los datos enviados
     */
    public function registrarObservacion($cod_toe, $des_obe, $fec_ing_obe, $id_alu, $cod_cur, $cod_mat, $id_peri, $cla_obe, $id_per)
    {
        try {
            $stmt = $this->conn->prepare("SELECT id_ano FROM anolectivo WHERE actual = 's'");
            $stmt->execute();
            $res1 = $stmt->get_result()->fetch_assoc();
            $id_ano = $res1['id_ano'];

            $des_cor_obe = "";
            $tip_tab = "mat";

            $stmt = $this->conn->prepare("INSERT INTO obserestu (cod_toe, des_cor_obe, des_obe, id_alu, cod_cur, cod_mat, id_peri, id_ano, tip_tab,cla_obe) values (?,?,?,?,?,?,?,?,?,?)");
            if ($stmt === FALSE) {
                die($this->conn->error);
            } else {
                $stmt->bind_param("issiiiiiss", $cod_toe, $des_cor_obe, $des_obe, $id_alu, $cod_cur, $cod_mat, $id_peri, $id_ano, $tip_tab, $cla_obe);
            }

            $result = $stmt->execute();
            $id_obe = $this->conn->insert_id;
            $stmt->close();
            if ($result) {
                $fec_ini_obe = date("Y-m-d");
                $hor_ini_obe = date("H:i:s");
                $stmt = $this->conn->prepare("INSERT INTO hisobsest (id_obe, fec_ini_obe, hor_ini_obe, fec_ing_obe, hor_ing_obe, id_per) VALUES (?,?,?,?,?,?)");
                if ($stmt === FALSE) {
                    die($this->conn->error);
                } else {
                    $stmt->bind_param("isssss", $id_obe, $fec_ini_obe, $hor_ini_obe, $fec_ing_obe, $hor_ini_obe, $id_per);
                }

                $result = $stmt->execute();
                $stmt->close();
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }


    /**
     * actualizar o insertar falta segun los datos enviados
     */
    public function actualizarFaltaEst($id_alu, $cod_cur, $cod_mat, $fec_fal, $num_hor, $jus_fal, $tip_fal)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM falta_histo_mat WHERE id_alu=$id_alu AND cod_cur=$cod_cur AND cod_mat=$cod_mat AND fec_fal='$fec_fal' AND jus_fal='$jus_fal'");
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                if ($tip_fal != '*') {
                    $query = "UPDATE `falta_histo_mat` SET `num_hor`='$num_hor', `tip_fal`='$tip_fal' WHERE `id_alu`=$id_alu AND `cod_mat`=$cod_mat AND `cod_cur`=$cod_cur AND `fec_fal`='$fec_fal' AND `tip_fal`='$tip_fal'";
                } else {
                    $query = "DELETE FROM `falta_histo_mat` WHERE `id_alu`=$id_alu AND `cod_mat`=$cod_mat AND `cod_cur`=$cod_cur AND `fec_fal`='$fec_fal' AND `jus_fal`='$jus_fal'";
                }
                $stmt = $this->conn->prepare($query);
                if ($stmt->execute()) {
                    $stmt->close();
                    return true;
                } else {
                    $stmt->close();
                    return false;
                }
            } else {
                if ($tip_fal != '*') {
                    $stmt = $this->conn->prepare("INSERT INTO falta_histo_mat (id_alu,cod_cur,cod_mat,fec_fal,num_hor,jus_fal,tip_fal) VALUES (?,?,?,?,?,?,?)");
                    if ($stmt === FALSE) {
                        die($this->conn->error);
                    } else {
                        $stmt->bind_param("iiisiss", $id_alu, $cod_cur, $cod_mat, $fec_fal, $num_hor, $jus_fal, $tip_fal);
                    }
                    $result = $stmt->execute();
                    $stmt->close();
                    if ($result) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    /**
     * Consultar si hay faltas registradas para el curso y la fecha enviada
     */
    public function getFaltasCursoMat($cod_cur, $cod_mat, $fec_fal)
    {
        $stmt = $this->conn->prepare("SELECT * FROM falta_histo_mat WHERE (fec_fal>=(SELECT fec_ini FROM periodo WHERE actual='s') AND fec_fal <=(SELECT fec_cor_tot FROM periodo WHERE actual='s')) AND cod_cur=$cod_cur AND cod_mat=$cod_mat AND tip_fal='t'");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar actividades que el profesor tiene programadas
     */
    public function getActividadesProfesor($id_per, $fec_ini, $fec_fin)
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT a.fec_act, TIME_FORMAT(a.hor_act,'%h:%i %p') AS hor_act, t.des_tact, a.cod_mat, m.des_mat, c.des_cur, c.cod_cur
		FROM web_actividades a, web_tipactividad t, alumcurso al, materia m, curso c
		WHERE m.cod_mat=a.cod_mat AND c.cod_cur=al.cod_cur AND a.id_res=$id_per AND al.id_alu=a.id_alu AND a.id_tact= t.id_tact AND a.fec_act BETWEEN '$fec_ini' AND '$fec_fin'
		ORDER BY fec_act");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar grados que el profesor tiene programados
     */
    public function cargarGrados($id_per)
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT(gra.cod_gra) AS id, gra.des_gra AS label, ord_gra
		FROM curso cur
		INNER JOIN matgra mat ON mat.cod_cur = cur.cod_cur AND mat.id_per='$id_per'
		INNER JOIN grado gra ON gra.cod_gra = cur.cod_gra
		ORDER BY ord_gra ASC");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar funcionarios que el profesor peude enviar mensajes
     */
    public function cargarFuncionariosProf($usuario_id)
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT b.id_per id_usuario, 's' tipo_usuario, nom_per, ape_per,cargo, GROUP_CONCAT(DISTINCT des_mat
		ORDER BY des_mat) mats, GROUP_CONCAT(DISTINCT des_gra
		ORDER BY ord_gra) grad, 0 id_alu
		FROM ususis
		JOIN persona b USING(id_per)
		JOIN profesor USING(id_per)
		LEFT JOIN matgra USING(id_per)
		LEFT JOIN materia USING(cod_mat)
		LEFT JOIN curso c USING(cod_cur)
		LEFT JOIN grado USING(cod_gra)
		WHERE est_usu='a' AND id_usu <> '$usuario_id'
		GROUP BY id_usu
		ORDER BY ape_per, nom_per");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar secciones que el profesor peude enviar mensajes
     */
    public function cargarSecciones($cod_gra, $id_per)
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT(c.cod_cur) AS id, c.des_cur AS label
		FROM (
		SELECT cod_cur, des_cur
		FROM curso
		WHERE cod_gra='$cod_gra') c
		JOIN (
		SELECT cod_cur, id_ano
		FROM matgra
		WHERE id_per='$id_per')m
		JOIN (
		SELECT id_ano
		FROM anolectivo)al ON c.cod_cur=m.cod_cur AND m.id_ano=al.id_ano");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar estudiantes de la seccion enviada a la q el profesor peude enviar mensajes
     */
    public function cargarEstudiantesProfesor($cod_cur)
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT CONCAT(a.ape_per,' ',a.nom_per,'  ',c1.des_cur_cor) AS nom, a.id_alu AS id_alu, a.nom_per,a.ape_per
		FROM
			alumno a
		INNER JOIN (
		SELECT DISTINCT id_alu,c.cod_cur
		FROM alumcurso ac
		INNER JOIN curso c ON c.cod_cur=ac.cod_cur AND c.cod_cur = $cod_cur
		INNER JOIN matgra mg ON mg.cod_cur=ac.cod_cur AND mg.esp_mat='n') s ON a.est_alu = 'h' AND a.id_alu=s.id_alu
		INNER JOIN curso c1 ON c1.cod_cur=s.cod_cur
		ORDER BY nom ASC");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar el id_usu del padre relacionado con el alumno enviado
     */
    public function getIdUsuPadre($id_alu)
    {
        $stmt = $this->conn->prepare("SELECT id_usu, a.nom_per,a.ape_per FROM web_alumreg INNER JOIN alumno a USING(id_alu) WHERE id_alu=$id_alu");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar lsitado de destinatarios estudiantes desde profesor
     */
    public function getDestinatariosEstMSG($id_msg)
    {
        $stmt = $this->conn->prepare("SELECT sel2.id_alu,nom_per,ape_per, c.cod_cur,des_cur_cor,GROUP_CONCAT(IF(sel2.tip_des='a',id_alu, NULL)) AS 'alum', 
		GROUP_CONCAT(IF(sel2.tip_des='p',id_des, NULL)) AS 'padre',
		GROUP_CONCAT(IF(sel2.tip_des='m',id_des, NULL)) AS 'madre'
		FROM(
		SELECT DISTINCT wb.id_alu, wb.id_alu AS id_des,'a' tip_des
		FROM alumno a 
		INNER JOIN web_msg_des wb ON id_msg=$id_msg and wb.id_alu=a.id_alu
		UNION
		SELECT wb.id_alu,p.ced_per AS id_des,'p' tip_des
		FROM alumno a
		INNER JOIN persona p ON p.id_per=a.id1_acu
		INNER JOIN web_msg_des wb ON id_msg=$id_msg and wb.id_alu=a.id_alu
		UNION
		SELECT wb.id_alu,p.ced_per AS id_des,'m' tip_des
		FROM alumno a
		INNER JOIN persona p ON p.id_per=a.id2_acu
		INNER JOIN web_msg_des wb ON id_msg=$id_msg and wb.id_alu=a.id_alu)sel2
		INNER JOIN alumno USING(id_alu)
		INNER JOIN alumcurso ac USING(id_alu)
		INNER JOIN curso c ON c.cod_cur=ac.cod_cur
		GROUP BY id_alu 
		ORDER BY c.cod_cur desc");
        //$stmt->bind_param("i", $id_msg);

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar lsitado de destinatarios estudiantes desde profesor
     */
    public function getDestinatariosAcuMSG($id_msg)
    {
        $stmt = $this->conn->prepare("SELECT nom_per,ape_per,id_msg
		FROM web_msg_des w
		INNER JOIN web_usu wu ON wu.id_usu=w.id_des
		WHERE id_msg=$id_msg AND tip_des='w'");
        //$stmt->bind_param("i", $id_msg);

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar las notas registradas para el curso y la instancia enviada
     */
    public function getNotasCurso($cod_cur, $cod_mat, $id_peri, $cod_ins)
    {
        $stmt = $this->conn->prepare("SELECT * FROM seguimiento WHERE cod_inst=$cod_ins AND cod_cur=$cod_cur AND cod_mat=$cod_mat AND id_peri=$id_peri");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Cambiar estado a leido del mensaje
     */
    public function cambiaEstadoMSG($id_msg, $id_des, $bd)
    {
        $stmt = $this->conn->prepare("UPDATE web_msg_des SET est_msg='l' WHERE id_msg=$id_msg AND id_des='$id_des' AND tip_des='$bd'");

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Consultar el numero del dia segun la fecha
     */
    public function getDiaByFecha($fec_fal)
    {
        $stmt = $this->conn->prepare("SELECT DATE_FORMAT('$fec_fal','%w') s");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar jornadas asignadas para el dia segun la materia y el curso
     */
    public function cargarJornadasDia($id_per, $cod_cur, $cod_mat, $dia)
    {
        $stmt = $this->conn->prepare("SELECT * FROM matgra_hor JOIN jornada_hor USING(id_jhor) WHERE id_per='$id_per' AND cod_cur=$cod_cur AND cod_mat=$cod_mat AND dia=$dia ORDER BY des_jhor");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar jornadas asignadas para el dia segun la materia y el curso
     */
    public function datosFaltas($cod_cur, $cod_mat, $dia_sem)
    {
        $stmt = $this->conn->prepare("SELECT * FROM matgra_hor WHERE id_per='$id_per' AND cod_cur=$cod_cur AND cod_mat=$cod_mat AND dia=$dia");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }


    /**
     * Consultar 
     */
    public function modeloFaltas()
    {
        $stmt = $this->conn->prepare("SELECT * FROM _sapred WHERE nom_var = 'MODELO_DE_ASISTENCIA'");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar si hay faltas registradas para el curso, la materia y la fecha enviada en falta_histo_bien
     */
    public function getFaltasCursoBien($cod_cur, $cod_mat, $fec_fal)
    {
        $stmt = $this->conn->prepare("SELECT * FROM falta_histo_bien WHERE fec_fal='$fec_fal' AND cod_cur=$cod_cur AND cod_mat=$cod_mat");

        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * actualizar o insertar en falta_histo_bien segun los datos enviados
     */
    public function actualizarFaltaBien($id_alu, $cod_cur, $id_jhor, $fec_fal, $tipo_f, $just_fal, $id_usu_cre, $cod_mat, $dia)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM falta_histo_bien WHERE id_alu=$id_alu AND cod_cur=$cod_cur AND id_jhor=$id_jhor AND cod_mat=$cod_mat AND fec_fal='$fec_fal'");
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                if ($tipo_f != '*') {
                    $query = "UPDATE `falta_histo_bien` SET `dia`='$dia', `tipo_f`='$tipo_f', `id_jhor`='$id_jhor' WHERE `id_alu`=$id_alu AND `cod_mat`=$cod_mat AND `cod_cur`=$cod_cur AND `fec_fal`='$fec_fal'";
                } else {
                    $query = "DELETE FROM `falta_histo_bien` WHERE `id_alu`=$id_alu AND `cod_mat`=$cod_mat AND `cod_cur`=$cod_cur AND `id_jhor`=$id_jhor AND `fec_fal`='$fec_fal' ";
                }
                $stmt = $this->conn->prepare($query);
                if ($stmt->execute()) {
                    $stmt->close();
                    return true;
                } else {
                    $stmt->close();
                    return false;
                }
            } else {
                if ($tipo_f != '') {
                    $stmt = $this->conn->prepare("INSERT INTO falta_histo_bien (id_alu,cod_cur,id_jhor,fec_fal,tipo_f,id_usu_cre,cod_mat,dia) VALUES (?,?,?,?,?,?,?,?)");
                    if ($stmt === FALSE) {
                        die($this->conn->error);
                    } else {
                        $stmt->bind_param("iiissiii", $id_alu, $cod_cur, $id_jhor, $fec_fal, $tipo_f, $id_usu_cre, $cod_mat, $dia);
                    }
                    $result = $stmt->execute();
                    $stmt->close();
                    if ($result) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            }
        } catch (Exception $e) {
            throw ($e);
        }
    }

    /**
     * Consultar si el colegio maneja faltas totalizadas
     */
    public function faltasTotalizadas()
    {
        $stmt = $this->conn->prepare("SELECT * FROM _sapred WHERE nom_var = 'FALTAS_TOTALIZADAS'");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }

    /**
     * Consultar si el colegio maneja faltas justificadas y no justificadas
     */
    public function faltasJustificadas()
    {
        $stmt = $this->conn->prepare("SELECT * FROM _sapred WHERE nom_var = 'FALTAS_JUSTIFICADAS'");

        if ($stmt->execute()) {
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $res;
        } else {
            return NULL;
        }
    }
}
