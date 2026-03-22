<?php

date_default_timezone_set('America/El_Salvador');
require_once dirname(__FILE__) . '/cheque/DBSics.php';
class ModelDB
{
    private $db;

    public function __construct()
    {
        $this->db = new DBSics();
    }
    // Ejecuta una consulta y retorna un solo registro
    public function find($qry, $params = array())
    {
        return $this->db->sql_select_one($qry, $params);
    }
    // Ejecuta una consulta y retorna todos los registros
    public function findAll($qry, $params = array())
    {
        return $this->db->sql_select_all($qry, $params);
    }
    // Crea un registro en la base de datos y retorna el id insertado
    public function create($qry, $params = array())
    {
        return $this->db->sql_save_id($qry, $params);
    }
    // Ejecuta un query y retorna el resultado true o false
    public function query($qry, $params = array())
    {
        return $this->db->sql_query($qry, $params);
    }

    //Listado de catalogos

    //Listado de empresas por usuario
    public function get_empresas_user($id_usuario)
    {
        return $this->findAll(
            "select
                distinct
                e.id_empresa keyCode,
                e.emp_nombre keyValue
            from acc_emp_cc ac
            inner join empresa e 
            on ac.id_empresa = e.id_empresa 
            where id_usuario = :id
            order by e.emp_nombre ",
            [':id' => $id_usuario]
        );
    }
    //Listado de cc por empresa (segun usuario)
    public function get_cc_empresa_user($id, $id_usuario)
    {
        return $this->findAll(
            "select
                distinct
                c.id_cc keyCode,
                c.cc_descripcion keyValue
            from acc_emp_cc ac
            inner join cecosto c  
            on ac.id_empresa = c.id_empresa 
            where ac.id_usuario = :id_usuario and ac.id_empresa = :id_empresa
            order by c.cc_descripcion ",
            [':id_usuario' => $id_usuario, ':id_empresa' => $id]
        );
    }

    //Listado de categorias por tipo (compras | cheques)
    public function get_categorias_tipo($tipo)
    {
        return $this->findAll(
            "select
                id keyCode,
                categoria keyValue,
                gcia keyGroup
            from cat_gerencias 
            where mod_solicitud = :tipo
            order by gcia,categoria ",
            [':tipo' => $tipo]
        );
    }
    //Listado de categorias por compras
    public function get_categorias_compras()
    {
        return $this->get_categorias_tipo("Solicitud de compras");
    }

    //Busqueda de productos para adicionar detalle
    public function get_productos_cat($id, $search)
    {
        return $this->findAll(
            "select
                id_producto keyCode,
                codigo_producto keyValue,
                descripcion_producto keyDescription
            from fproductos
            where id_categoria = :id and lower(concat(codigo_producto,' ',descripcion_producto)) like lower(concat('%',:search,'%'))
            order by descripcion_producto
            limit 10 ",
            [
                ':id' => $id,
                ':search' => $search
            ]
        );
    }

    // Trazabilidad compras -> Creación

    public function traza_compra_creacion($id_usuario, $id_empresa, $id_cc, $id_categoria)
    {
        $traza = [];

        // 1. Adiciona usuario solicitante
        $usr = $this->traza_compra_solicitante($id_usuario);
        $traza['solicitante'] = $usr;

        // 2. Adición usuario genera cotización
        $traza['cotizacion'] = (object) [
            'estado' => 'pending',
            'id_usuario' => $id_usuario,
            'usr_usuario' => 'Analista de Compras',
            'usr_nombre' => 'Analista de Compras',
            'id_rol' => '0'
        ];

        // 3. Aprobador de CC
        $traza['aprobador_cc'] = $this->traza_compra_aprobador_cc($id_empresa, $id_cc);

        // 4. Aprobador de categoria
        $traza['aprobador_categoria'] = $this->traza_compra_aprobador_categoria($id_categoria);

        // 5. Aprobador de monto >= $5K
        if ($this->traza_compra_requiere_aprobador_5k($id_categoria)) {
            $traza['aprobador_5k'] = (object) [
                'estado' => 'pending',
                'id_usuario' => 0,
                'usr_usuario' => 'Depende de monto',
                'usr_nombre' => 'Depende de monto',
                'id_rol' => '0'
            ];
        }

        // 6. Adición usuario genera orden compra
        $traza['compra'] = (object) [
            'estado' => 'pending',
            'id_usuario' => 0,
            'usr_usuario' => 'Analista de Compras',
            'usr_nombre' => 'Analista de Compras',
            'id_rol' => '0'
        ];

        // 7. Recepción de solicitud
        $traza['recepcion'] = clone $usr;
        $traza['recepcion']->estado = "pending";

        return $traza;
    }
    // Datos de trazabilidad -> Usuario solicitante
    public function traza_compra_solicitante($id_usuario)
    {
        return $this->find(
            "select
                'active' estado,
                id_usuario,
                usr_usuario usr_id,
                usr_nombre usr_name,
                id_rol 
            from usuario
            where id_usuario = :id_usuario",
            [
                ':id_usuario' => $id_usuario
            ]
        );
    }

    // Datos de trazabilidad -> Aprobador de CC
    public function traza_compra_aprobador_cc($id_empresa, $id_cc)
    {
        return $this->find(
            "select
                'pending' estado,
                u.id_usuario id,
                u.usr_usuario usr_id,
                u.usr_nombre usr_name,
                u.id_rol id_rol
            from acc_emp_cc a
            inner join usuario u 
            on u.id_usuario = a.id_usuario and u.id_rol = 999999995 and u.id_usuario != 288
            where a.id_empresa = :id_empresa and a.id_cc = :id_cc
            limit 1",
            [
                ':id_empresa' => $id_empresa,
                ':id_cc' => $id_cc
            ]
        );
    }

    // Datos de trazabilidad -> Aprobador de categoria
    // Datos de trazabilidad -> Aprobador de categoria
    public function traza_compra_aprobador_categoria($id_categoria)
    {
        $tmp = $this->findAll(
            "select
                'pending' estado,
                u.id_usuario id,
                u.usr_usuario usr_id,
                u.usr_nombre usr_name,
                u.id_rol,
                c.requiere_aprobador_5k
            from cat_gerencias c
            inner join usuario u 
            on u.id_usuario in (c.user_aproba_1,c.user_aproba_2,c.user_aproba_3)
            where c.id = :id_categoria and c.mod_solicitud = 'Solicitud de compras'",
            [
                ':id_categoria' => $id_categoria
            ]
        );

        // 🏗️ CORRECCIÓN: Alineamos las propiedades del objeto simulado (id, usr_id, usr_name, requiere_aprobador_5k)
        if (empty($tmp)) {
            return (object) [
                'estado' => 'pending',
                'id' => 0,
                'usr_id' => 'Depende de monto',
                'usr_name' => 'Depende de monto',
                'id_rol' => '0',
                'requiere_aprobador_5k' => 0
            ];
        } else {
            if (count($tmp) > 1) {
                return (object) [
                    'estado' => 'pending',
                    'id' => 0,
                    'usr_id' => 'Depende de monto',
                    'usr_name' => 'Depende de monto',
                    'id_rol' => '0',
                    'requiere_aprobador_5k' => isset($tmp[0]->requiere_aprobador_5k) ? $tmp[0]->requiere_aprobador_5k : 0
                ];
            } else {
                return $tmp[0];
            }
        }
    }
    // Datos de trazabilidad -> Requiere aprobación $5K
    public function traza_compra_requiere_aprobador_5k($id_categoria)
    {
        $tmp = $this->find(
            "select
                requiere_aprobador_5k
            from cat_gerencias 
            where id = :id_categoria and mod_solicitud = 'Solicitud de compras'",
            [
                ':id_categoria' => $id_categoria
            ]
        );

        if (empty($tmp)) {
            return false;
        } else {
            return ($tmp->requiere_aprobador_5k == 1);
        }
    }

    // =========================================================
    // CREACIÓN DE SOLICITUD DE COMPRAS (ENCABEZADO Y DETALLE)
    // =========================================================

    // 1. Obtiene el correlativo actual del Centro de Costo y le suma 1
    public function get_and_increment_cc_solc($id_empresa, $id_cc)
    {
        try {
            // 1. Iniciamos la transacción explícitamente
            $this->query("START TRANSACTION");

            // 2. Leemos el correlativo y BLOQUEAMOS LA FILA con 'FOR UPDATE'
            // Ningún otro proceso podrá tocar esta fila del Centro de Costos hasta el COMMIT.
            $row = $this->find(
                "SELECT cc_solc FROM cecosto WHERE id_empresa = :id_empresa AND id_cc = :id_cc FOR UPDATE",
                [
                    ':id_empresa' => $id_empresa,
                    ':id_cc' => $id_cc
                ]
            );

            // Obtenemos el valor actual (si es nulo, iniciamos en 0)
            $current_val = $row && isset($row->cc_solc) ? (int) $row->cc_solc : 0;
            $new_val = $current_val + 1;

            // 3. Escribimos el nuevo valor
            $this->query(
                "UPDATE cecosto SET cc_solc = :new_val WHERE id_empresa = :id_empresa AND id_cc = :id_cc",
                [
                    ':new_val' => $new_val,
                    ':id_empresa' => $id_empresa,
                    ':id_cc' => $id_cc
                ]
            );

            // 4. Confirmamos los cambios y LIBERAMOS EL CANDADO
            $this->query("COMMIT");

            return $new_val;

        } catch (Exception $e) {
            // Si ocurre algún error, deshacemos y soltamos el candado por seguridad
            $this->query("ROLLBACK");

            // Retornamos 1 por defecto o puedes lanzar la excepción
            // throw $e; 
            return 1;
        }
    }

    // 2. Inserta el Encabezado (prehsol)
    public function create_solicitud_encabezado($d)
    {
        $qry = "INSERT INTO prehsol (
                    id_empresa, id_cc, id_usuario, prehsol_numero, prehsol_fecha, 
                    prehsol_hora, prehsol_usuario, prehsol_estado, prehsol_numero_sol, 
                    prehsol_obs1, prehsol_coti1, prehsol_coti2, prehsol_coti3, 
                    prehsol_coti1_name, prehsol_coti2_name, prehsol_coti3_name, id_categoria
                ) VALUES (
                    :id_empresa, :id_cc, :id_usuario, :prehsol_numero, :prehsol_fecha, 
                    :prehsol_hora, :prehsol_usuario, :prehsol_estado, :prehsol_numero_sol, 
                    :prehsol_obs1, :prehsol_coti1, :prehsol_coti2, :prehsol_coti3, 
                    :prehsol_coti1_name, :prehsol_coti2_name, :prehsol_coti3_name, :id_categoria
                )";

        return $this->create($qry, [
            ':id_empresa' => $d['id_empresa'],
            ':id_cc' => $d['id_cc'],
            ':id_usuario' => $d['id_usuario'],
            ':prehsol_numero' => $d['prehsol_numero'],
            ':prehsol_fecha' => $d['prehsol_fecha'],
            ':prehsol_hora' => $d['prehsol_hora'],
            ':prehsol_usuario' => $d['prehsol_usuario'],
            ':prehsol_estado' => $d['prehsol_estado'],
            ':prehsol_numero_sol' => $d['prehsol_numero_sol'],
            ':prehsol_obs1' => $d['prehsol_obs1'],
            ':prehsol_coti1' => $d['prehsol_coti1'],
            ':prehsol_coti2' => $d['prehsol_coti2'],
            ':prehsol_coti3' => $d['prehsol_coti3'],
            ':prehsol_coti1_name' => $d['prehsol_coti1_name'],
            ':prehsol_coti2_name' => $d['prehsol_coti2_name'],
            ':prehsol_coti3_name' => $d['prehsol_coti3_name'],
            ':id_categoria' => $d['id_categoria']
        ]);
    }

    // 3. Inserta el Detalle (predsol)
    public function create_solicitud_detalle($d)
    {
        $qry = "INSERT INTO predsol (
                    id_prehsol, id_usuario, prod_codigo, predsol_cantidad, 
                    predsol_fecha, predsol_hora, predsol_usuario, predsol_descripcion, 
                    id_empresa, id_cc, predsol_estado, id_producto
                ) VALUES (
                    :id_prehsol, :id_usuario, :prod_codigo, :predsol_cantidad, 
                    :predsol_fecha, :predsol_hora, :predsol_usuario, :predsol_descripcion, 
                    :id_empresa, :id_cc, :predsol_estado, :id_producto
                )";

        return $this->create($qry, [
            ':id_prehsol' => $d['id_prehsol'],
            ':id_usuario' => $d['id_usuario'],
            ':prod_codigo' => $d['prod_codigo'],
            ':predsol_cantidad' => $d['predsol_cantidad'],
            ':predsol_fecha' => $d['predsol_fecha'],
            ':predsol_hora' => $d['predsol_hora'],
            ':predsol_usuario' => $d['predsol_usuario'],
            ':predsol_descripcion' => $d['predsol_descripcion'],
            ':id_empresa' => $d['id_empresa'],
            ':id_cc' => $d['id_cc'],
            ':predsol_estado' => $d['predsol_estado'],
            ':id_producto' => empty($d['id_producto']) ? 0 : $d['id_producto']
        ]);
    }

    //Crea flujo de aprobación para consultar la trazabilidad
    public function create_trazabilidad_compras($id, $id_empresa, $id_cc, $id_categoria, $id_usuario, $usr_id, $usr_name)
    {
        // Trazabilidad
        // 1. Creación de solicitud
        $this->create_trazabilidad_item([
            'id_sol' => $id,
            'estado' => 1,
            'estado_descr' => 'Solicitante Cco.',
            'descripcion' => 'Solicitado',
            'fecha' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'orden' => 1,
            'id_usuario' => $id_usuario,
            'usuario' => $usr_id,
            'nom_usuario' => $usr_name,
            'active' => 1,
            'resolucion' => 'C'
        ]);

        // 2. En cotización
        $this->create_trazabilidad_item([
            'id_sol' => $id,
            'estado' => 11,
            'estado_descr' => 'Cotización',
            'descripcion' => 'En proceso',
            'fecha' => null,
            'hora' => null,
            'orden' => 11,
            'id_usuario' => 0,
            'usuario' => "Analista de Compras",
            'nom_usuario' => "Analista de Compras",
            'active' => 1,
            'resolucion' => 'P'
        ]);

        // 3. Aprobador Cco.
        $rs = $this->traza_compra_aprobador_cc($id_empresa, $id_cc);
        if (!empty($rs)) {
            $this->create_trazabilidad_item([
                'id_sol' => $id,
                'estado' => 21,
                'estado_descr' => 'Autorizador Cco.',
                'descripcion' => 'Pendiente',
                'fecha' => null,
                'hora' => null,
                'orden' => 21,
                'id_usuario' => $rs->id,
                'usuario' => $rs->usr_id,
                'nom_usuario' => $rs->usr_name,
                'active' => 1,
                'resolucion' => 'P'
            ]);
        }

        // 4. Aprobador Categoria.
        $rs = $this->traza_compra_aprobador_categoria($id_categoria);
        if (!empty($rs)) {
            $this->create_trazabilidad_item([
                'id_sol' => $id,
                'estado' => 31,
                'estado_descr' => 'Autorizador Categoría',
                'descripcion' => 'Pendiente',
                'fecha' => null,
                'hora' => null,
                'orden' => 31,
                'id_usuario' => $rs->id,
                'usuario' => $rs->usr_id,
                'nom_usuario' => $rs->usr_name,
                'active' => 1,
                'resolucion' => 'P'
            ]);
        }
        // 5. Aprobador $5K.
        if (!empty($rs)) {
            if ($rs->requiere_aprobador_5k === 1) {
                $this->create_trazabilidad_item([
                    'id_sol' => $id,
                    'estado' => 41,
                    'estado_descr' => 'Autorizador >= $5K',
                    'descripcion' => 'Pendiente',
                    'fecha' => null,
                    'hora' => null,
                    'orden' => 41,
                    'id_usuario' => $rs->id,
                    'usuario' => $rs->usr_id,
                    'nom_usuario' => $rs->usr_name,
                    'active' => 1,
                    'resolucion' => 'P'
                ]);
            }
        }

        // 6. Orden de compra.
        $this->create_trazabilidad_item([
            'id_sol' => $id,
            'estado' => 51,
            'estado_descr' => 'Orden de Compra',
            'descripcion' => 'Pendiente',
            'fecha' => null,
            'hora' => null,
            'orden' => 51,
            'id_usuario' => 0,
            'usuario' => "Analista de Compras",
            'nom_usuario' => "Analista de Compras",
            'active' => 1,
            'resolucion' => 'P'
        ]);

        // 7. Revision OC.
        $this->create_trazabilidad_item([
            'id_sol' => $id,
            'estado' => 61,
            'estado_descr' => 'Revisión OC',
            'descripcion' => 'Pendiente',
            'fecha' => null,
            'hora' => null,
            'orden' => 61,
            'id_usuario' => 0,
            'usuario' => "Jefe Compras",
            'nom_usuario' => "Jefe Compras",
            'active' => 1,
            'resolucion' => 'P'
        ]);

        // 8. OC en Proveedor.
        $this->create_trazabilidad_item([
            'id_sol' => $id,
            'estado' => 71,
            'estado_descr' => 'OC en Proveedor',
            'descripcion' => 'Pendiente',
            'fecha' => null,
            'hora' => null,
            'orden' => 71,
            'id_usuario' => 0,
            'usuario' => "Proveedor",
            'nom_usuario' => "Proveedor",
            'active' => 1,
            'resolucion' => 'P'
        ]);

        // 9. Recepcionar.
        $this->create_trazabilidad_item([
            'id_sol' => $id,
            'estado' => 81,
            'estado_descr' => 'Recepción',
            'descripcion' => 'Pendiente',
            'fecha' => null,
            'hora' => null,
            'orden' => 81,
            'id_usuario' => $id_usuario,
            'usuario' => $usr_id,
            'nom_usuario' => $usr_name,
            'active' => 1,
            'resolucion' => 'P'
        ]);

        // 9. Cerrar OC.
        $this->create_trazabilidad_item([
            'id_sol' => $id,
            'estado' => 91,
            'estado_descr' => 'Cerrar OC',
            'descripcion' => 'Pendiente',
            'fecha' => null,
            'hora' => null,
            'orden' => 91,
            'id_usuario' => 0,
            'usuario' => "Analista de Compras",
            'nom_usuario' => "Analista de Compras",
            'active' => 1,
            'resolucion' => 'P'
        ]);
    }

    // Crear item de trazabilidad
    public function create_trazabilidad_item($traza)
    {
        $this->create(
            "insert into sol_compra_estado(
                id_sol,
                estado,
                estado_descr,
                descripcion,
                fecha,
                hora,
                orden,
                id_usuario,
                usuario,
                nom_usuario,
                active,
                resolucion
            ) values(
                :id_sol,
                :estado,
                :estado_descr,
                :descripcion,
                :fecha,
                :hora,
                :orden,
                :id_usuario,
                :usuario,
                :nom_usuario,
                :active,
                :resolucion
            )",
            $traza
        );
    }

    // =========================================================
    // CONSULTA DE SOLICITUDES PARA COTIZAR (ESTADO 11)
    // =========================================================

    public function get_solicitudes_cotizacion($filtros, $offset = 0, $limit = 20)
    {
        $where = ["p.prehsol_estado = 11"];
        $params = [];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(p.prehsol_fecha) = :mes AND YEAR(p.prehsol_fecha) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['id_cc'])) {
            $where[] = "p.id_cc = :id_cc";
            $params[':id_cc'] = $filtros['id_cc'];
        }
        if (!empty($filtros['id_categoria'])) {
            $where[] = "p.id_categoria = :id_categoria";
            $params[':id_categoria'] = $filtros['id_categoria'];
        }
        // FILTRO: Búsqueda dinámica en Observación
        if (!empty($filtros['observacion'])) {
            $where[] = "p.prehsol_obs1 LIKE concat('%',:observacion,'%')";
            $params[':observacion'] = $filtros['observacion'];
        }

        $whereSql = implode(" AND ", $where);

        $limitSql = "LIMIT " . (int) $offset . ", " . (int) $limit;

        $qry = "SELECT
                    p.id_prehsol, 
                    p.prehsol_numero id,
                    p.id_empresa, e.emp_nombre,
                    p.id_cc, c.cc_descripcion,
                    p.id_categoria, cat.categoria as cat_descripcion,
                    p.prehsol_fecha, p.prehsol_hora, p.prehsol_usuario, p.prehsol_estado,
                    p.prehsol_obs1, /* 🏗️ AQUI AGREGAMOS LA OBSERVACION */
                    p.prehsol_coti1, p.prehsol_coti1_name,
                    p.prehsol_coti2, p.prehsol_coti2_name,
                    p.prehsol_coti3, p.prehsol_coti3_name
                FROM prehsol p
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN cecosto c ON p.id_cc = c.id_cc
                LEFT JOIN cat_gerencias cat ON p.id_categoria = cat.id
                WHERE $whereSql
                ORDER BY p.prehsol_fecha DESC, p.prehsol_hora DESC
                $limitSql";

        return $this->findAll($qry, $params);
    }

    public function count_solicitudes_cotizacion($filtros)
    {
        $where = ["p.prehsol_estado = 11"];
        $params = [];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(p.prehsol_fecha) = :mes AND YEAR(p.prehsol_fecha) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['id_cc'])) {
            $where[] = "p.id_cc = :id_cc";
            $params[':id_cc'] = $filtros['id_cc'];
        }
        if (!empty($filtros['id_categoria'])) {
            $where[] = "p.id_categoria = :id_categoria";
            $params[':id_categoria'] = $filtros['id_categoria'];
        }
        // FILTRO: Búsqueda dinámica en Observación
        if (!empty($filtros['observacion'])) {
            $where[] = "p.prehsol_obs1 LIKE concat('%',:observacion,'%')";
            $params[':observacion'] = $filtros['observacion'];
        }

        $whereSql = implode(" AND ", $where);

        $qry = "SELECT COUNT(p.id_prehsol) as total FROM prehsol p WHERE $whereSql";
        $row = $this->find($qry, $params);

        return $row && isset($row->total) ? (int) $row->total : 0;
    }

    // En model/model.php

    public function get_solicitud_cabecera($id_prehsol)
    {
        $qry = "SELECT p.*, e.emp_nombre, e.moneda as moneda_cia, c.cc_descripcion, g.categoria as cat_descripcion
            FROM prehsol p
            LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
            LEFT JOIN cecosto c ON p.id_cc = c.id_cc
            LEFT JOIN cat_gerencias g ON p.id_categoria = g.id
            WHERE p.id_prehsol = :id";
        return $this->find($qry, [':id' => $id_prehsol]);
    }

    public function get_solicitud_detalle($id_prehsol)
    {
        $qry = "SELECT d.*, p.CPANOM as prov_nombre 
                FROM predsol d
                LEFT JOIN proveedor_as400 p ON d.id_proveedor = p.CPACOD AND d.id_empresa = p.id_empresa
                WHERE d.id_prehsol = :id 
                ORDER BY d.id_predsol ASC";
        return $this->findAll($qry, [':id' => $id_prehsol]);
    }

    // Búsqueda de proveedores (CORREGIDO EL ERROR DEL PARÉNTESIS FALTANTE)
    public function get_proveedores_as400($id_empresa, $search)
    {
        $qry = "SELECT CPACOD as keyCode, trim(CPANOM) as keyValue
                FROM proveedor_as400
                WHERE id_empresa = :emp 
                AND (CPANOM LIKE concat('%',:search,'%') OR CPACOD LIKE concat('%',:search,'%'))
                LIMIT 15";
        return $this->findAll($qry, [':emp' => $id_empresa, ':search' => $search]);
    }

    // Obtener trazabilidad real de una solicitud existente
    public function get_trazabilidad_solicitud($id_sol)
    {
        $qry = "SELECT * FROM sol_compra_estado WHERE id_sol = :id ORDER BY orden ASC";
        return $this->findAll($qry, [':id' => $id_sol]);
    }

    // Actualizar un ítem individual de cotización
    // Actualizar un ítem individual de cotización (con auditoría a nivel de fila)
    public function update_item_cotizacion($id_predsol, $id_proveedor, $precio, $total, $observacion, $fecha, $hora, $usuario)
    {
        $qry = "UPDATE predsol SET 
                    id_proveedor = :id_proveedor,
                    predsol_prec_uni = :precio,
                    predsol_total = :total,
                    predsol_observacion = :obs,
                    predsol_fecha_col = :fecha,
                    predsol_hora_col = :hora,
                    predsol_usuario_col = :usuario
                WHERE id_predsol = :id_predsol";
        return $this->query($qry, [
            ':id_proveedor' => $id_proveedor,
            ':precio' => $precio,
            ':total' => $total,
            ':obs' => $observacion,
            ':fecha' => $fecha,
            ':hora' => $hora,
            ':usuario' => $usuario,
            ':id_predsol' => $id_predsol
        ]);
    }

    // Actualizar la cabecera al finalizar la cotización
    public function update_cotizacion_cabecera($id_prehsol, $d)
    {
        $qry = "UPDATE prehsol SET 
                    prehsol_estado = :estado,
                    obs_cate = :obs,
                    prehsol_monto = :monto,
                    moneda = :moneda";

        $params = [
            ':estado' => 21,
            ':obs' => $d['obs_analista'],
            ':monto' => $d['total_monto'],
            ':moneda' => $d['moneda'],
            ':id' => $id_prehsol
        ];

        // Si hay archivo, guardamos ruta física Y nombre original
        if (!empty($d['adj_comp'])) {
            $qry .= ", prehsol_coti4 = :adj_comp, prehsol_coti4_name = :adj_comp_name";
            $params[':adj_comp'] = $d['adj_comp'];
            $params[':adj_comp_name'] = $d['adj_comp_name'];
        }

        $qry .= " WHERE id_prehsol = :id";
        return $this->query($qry, $params);
    }

    // Actualizar datos de revisión en el encabezado de la cotización
    public function update_revision_cotizacion($id_prehsol, $usuario, $fecha_hora)
    {
        $qry = "UPDATE prehsol SET 
                    prehsol_revision = :usuario,
                    prehsol_revision_fecha = :fecha
                WHERE id_prehsol = :id";

        return $this->query($qry, [
            ':usuario' => $usuario,
            ':fecha' => $fecha_hora,
            ':id' => $id_prehsol
        ]);
    }

    // 1. Obtener el ID de categoría de una solicitud
    public function get_categoria_by_solicitud($id_prehsol)
    {
        $qry = "SELECT id_categoria FROM prehsol WHERE id_prehsol = :id";
        // 🏗️ CORRECCIÓN: Usar find() en lugar de query()
        $res = $this->find($qry, [':id' => $id_prehsol]);
        // 🏗️ CORRECCIÓN: find() devuelve directamente el objeto, no un array
        return $res ? (int) $res->id_categoria : 0;
    }

    // 2. Regla de Negocio: Obtener autorizador según rangos
    public function get_autorizador_categoria($id_categoria, $total)
    {
        $campo_aprueba = 'user_aproba_1'; // $0 a $200

        if ($total > 200 && $total <= 1000) {
            $campo_aprueba = 'user_aproba_2';
        } elseif ($total > 1000) {
            $campo_aprueba = 'user_aproba_3';
        }

        $qry = "SELECT u.id_usuario, u.usr_usuario, u.usr_nombre 
                FROM cat_gerencias cg
                INNER JOIN usuario u ON u.id_usuario = cg.{$campo_aprueba}
                WHERE cg.id = :id_categoria";

        // 🏗️ CORRECCIÓN: Usar find() en lugar de query()
        $res = $this->find($qry, [':id_categoria' => $id_categoria]);
        // 🏗️ CORRECCIÓN: find() devuelve el objeto directo, quitamos el [0]
        return $res ? $res : null;
    }

    // 3. Modificar el histórico de trazabilidad
    public function update_trazabilidad_autorizador($id_prehsol, $autorizador)
    {
        // Actualizamos estado 31 (Autorizador Categoría) en sol_compra_estado
        $qry = "UPDATE sol_compra_estado 
                SET id_usuario = :idu, 
                    usuario = :u, 
                    nom_usuario = :nu 
                WHERE id_sol = :id AND estado = 31";

        return $this->query($qry, [
            ':idu' => $autorizador->id_usuario,
            ':u' => $autorizador->usr_usuario,
            ':nu' => $autorizador->usr_nombre,
            ':id' => $id_prehsol
        ]);
    }

    // 4. Regla de Negocio: Obtener autorizador para montos >= $5K
    public function get_autorizador_5k($id_categoria)
    {
        $qry = "SELECT u.id_usuario, u.usr_usuario, u.usr_nombre 
                FROM cat_gerencias cg
                INNER JOIN usuario u ON u.id_usuario = cg.id_aprueba_5k
                WHERE cg.id = :id_categoria AND cg.requiere_aprobador_5k = 1";

        $res = $this->find($qry, [':id_categoria' => $id_categoria]);
        return $res ? $res : null;
    }

    // 5. Modificar el histórico de trazabilidad para Autorizador >= $5K
    public function update_trazabilidad_autorizador_5k($id_prehsol, $autorizador)
    {
        // Actualizamos el paso de Autorizador >= $5K (orden = 41)
        $qry = "UPDATE sol_compra_estado 
                SET id_usuario = :idu, 
                    usuario = :u, 
                    nom_usuario = :nu 
                WHERE id_sol = :id AND estado = 41";

        return $this->query($qry, [
            ':idu' => $autorizador->id_usuario,
            ':u' => $autorizador->usr_usuario,
            ':nu' => $autorizador->usr_nombre,
            ':id' => $id_prehsol
        ]);
    }
    // 6. Activar o desactivar dinámicamente el paso de $5K en la trazabilidad
    public function toggle_trazabilidad_5k($id_prehsol, $is_active)
    {
        // 🏗️ Forzamos a entero directo en la consulta para evitar bugs de PDO con columnas bit(1)
        $val = $is_active ? 1 : 0;
        $qry = "UPDATE sol_compra_estado 
                SET active = {$val} 
                WHERE id_sol = :id AND (estado = 41 OR orden = 41)";

        return $this->query($qry, [
            ':id' => $id_prehsol
        ]);
    }

    // =========================================================
    // CONSULTA PARA AUTORIZADOR DE CENTRO DE COSTOS (ESTADO 21)
    // =========================================================
    public function get_solicitudes_aprobacion_cc($id_usuario, $filtros, $offset = 0, $limit = 20)
    {
        $where = ["p.prehsol_estado = 21", "acc.id_usuario = :id_usuario"];
        $params = [':id_usuario' => $id_usuario];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(p.prehsol_fecha) = :mes AND YEAR(p.prehsol_fecha) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['id_cc'])) {
            $where[] = "p.id_cc = :id_cc";
            $params[':id_cc'] = $filtros['id_cc'];
        }
        // 🏗️ NUEVO FILTRO DUAL: Busca en la obs del solicitante o en la del analista
        if (!empty($filtros['observacion'])) {
            $where[] = "(p.prehsol_obs1 LIKE concat('%',:observacion,'%') OR p.obs_cate LIKE concat('%',:observacion,'%'))";
            $params[':observacion'] = $filtros['observacion'];
        }

        $whereSql = implode(" AND ", $where);
        $limitSql = "LIMIT " . (int) $offset . ", " . (int) $limit;

        $qry = "SELECT
                    p.id_prehsol, p.prehsol_numero AS id,
                    p.id_empresa, e.emp_nombre,
                    p.id_cc, c.cc_descripcion,
                    p.id_categoria, cat.categoria AS cat_descripcion,
                    p.prehsol_fecha, p.prehsol_hora, p.prehsol_usuario, 
                    p.prehsol_monto, p.moneda, 
                    p.prehsol_obs1, p.obs_cate,
                    p.prehsol_coti1, p.prehsol_coti1_name,
                    p.prehsol_coti2, p.prehsol_coti2_name,
                    p.prehsol_coti3, p.prehsol_coti3_name,
                    p.prehsol_coti4, p.prehsol_coti4_name,
                    p.prehsol_coti4, p.prehsol_coti4_name,
                    p.prehsol_revision, 
                    p.prehsol_revision_fecha
                FROM prehsol p
                INNER JOIN acc_emp_cc acc ON p.id_empresa = acc.id_empresa AND p.id_cc = acc.id_cc
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN cecosto c ON p.id_cc = c.id_cc
                LEFT JOIN cat_gerencias cat ON p.id_categoria = cat.id
                WHERE $whereSql
                ORDER BY p.prehsol_fecha ASC, p.prehsol_hora ASC
                $limitSql";

        return $this->findAll($qry, $params);
    }

    public function count_solicitudes_aprobacion_cc($id_usuario, $filtros)
    {
        $where = ["p.prehsol_estado = 21", "acc.id_usuario = :id_usuario"];
        $params = [':id_usuario' => $id_usuario];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(p.prehsol_fecha) = :mes AND YEAR(p.prehsol_fecha) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['id_cc'])) {
            $where[] = "p.id_cc = :id_cc";
            $params[':id_cc'] = $filtros['id_cc'];
        }
        // 🏗️ NUEVO FILTRO DUAL PARA EL COUNT
        if (!empty($filtros['observacion'])) {
            $where[] = "(p.prehsol_obs1 LIKE concat('%',:observacion,'%') OR p.obs_cate LIKE concat('%',:observacion,'%'))";
            $params[':observacion'] = $filtros['observacion'];
        }

        $whereSql = implode(" AND ", $where);

        $qry = "SELECT COUNT(p.id_prehsol) as total 
                FROM prehsol p
                INNER JOIN acc_emp_cc acc ON p.id_empresa = acc.id_empresa AND p.id_cc = acc.id_cc
                WHERE $whereSql";
        $row = $this->find($qry, $params);

        return $row && isset($row->total) ? (int) $row->total : 0;
    }
    // =========================================================
    // ACCIONES EXCLUSIVAS: AUTORIZADOR DE CENTRO DE COSTOS
    // =========================================================

    // 1. Actualizar cantidad y recalcular el total de esa fila
    public function update_cantidad_item($id_predsol, $cantidad, $total)
    {
        $qry = "UPDATE predsol SET 
                    predsol_cantidad = :cant,
                    predsol_cantidad_aut = :cant, /* Auditamos que fue modificado en autorización */
                    predsol_total = :total 
                WHERE id_predsol = :id";
        return $this->query($qry, [':cant' => $cantidad, ':total' => $total, ':id' => $id_predsol]);
    }

    // 2. Eliminar un ítem de la cotización
    public function delete_item_cotizacion($id_predsol)
    {
        return $this->query("DELETE FROM predsol WHERE id_predsol = :id", [':id' => $id_predsol]);
    }

    // 3. Aprobar cotización (Función Universal para cualquier nivel)
    public function update_estado_y_aprobacion($id_prehsol, $estado_final, $campos_auditoria)
    {
        $set_sql = "prehsol_estado = :estado";
        $params = [
            ':estado' => $estado_final,
            ':id' => $id_prehsol
        ];

        // 🏗️ Construir dinámicamente los campos a actualizar (CCo, Categoria, o 5K)
        foreach ($campos_auditoria as $campo => $valor) {
            $set_sql .= ", {$campo} = :{$campo}";
            $params[":{$campo}"] = $valor;
        }

        $qry = "UPDATE prehsol SET {$set_sql} WHERE id_prehsol = :id";
        return $this->query($qry, $params);
    }

    // 4. Actualizar el gran total de la cabecera
    public function update_gran_total_cabecera($id_prehsol, $gran_total)
    {
        return $this->query("UPDATE prehsol SET prehsol_monto = :total WHERE id_prehsol = :id", [
            ':total' => $gran_total,
            ':id' => $id_prehsol
        ]);
    }

    // 🏗️ NUEVO: Leer un paso específico de la trazabilidad
    public function get_paso_trazabilidad($id_prehsol, $orden)
    {
        $qry = "SELECT * FROM sol_compra_estado WHERE id_sol = :id AND orden = :o";
        return $this->find($qry, [':id' => $id_prehsol, ':o' => $orden]);
    }

    // 🏗️ NUEVO: Buscar el siguiente paso que esté "encendido" (active = 1)
    public function get_siguiente_paso_activo($id_prehsol, $orden_actual)
    {
        $qry = "SELECT * FROM sol_compra_estado WHERE id_sol = :id AND orden > :o AND active = 1 ORDER BY orden ASC LIMIT 1";
        return $this->find($qry, [':id' => $id_prehsol, ':o' => $orden_actual]);
    }

    // =========================================================
    // CONSULTA PARA GENERACIÓN DE ORDEN DE COMPRA (ESTADO 51)
    // =========================================================
    public function get_solicitudes_pendientes_oc($filtros, $offset = 0, $limit = 20)
    {
        $where = ["p.prehsol_estado = 51"];
        $params = [];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(p.prehsol_fecha) = :mes AND YEAR(p.prehsol_fecha) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['id_cc'])) {
            $where[] = "p.id_cc = :id_cc";
            $params[':id_cc'] = $filtros['id_cc'];
        }
        if (!empty($filtros['observacion'])) {
            $where[] = "(p.prehsol_obs1 LIKE concat('%',:observacion,'%') OR p.obs_cate LIKE concat('%',:observacion,'%'))";
            $params[':observacion'] = $filtros['observacion'];
        }

        $whereSql = implode(" AND ", $where);
        $limitSql = "LIMIT " . (int) $offset . ", " . (int) $limit;

        $qry = "SELECT
                    p.id_prehsol, p.prehsol_numero AS id,
                    p.id_empresa, e.emp_nombre,
                    p.id_cc, c.cc_descripcion,
                    p.id_categoria, cat.categoria AS cat_descripcion,
                    p.prehsol_fecha, p.prehsol_hora, p.prehsol_usuario, 
                    p.prehsol_monto, p.moneda, 
                    p.prehsol_obs1, p.obs_cate,
                    p.prehsol_coti1, p.prehsol_coti1_name,
                    p.prehsol_coti2, p.prehsol_coti2_name,
                    p.prehsol_coti3, p.prehsol_coti3_name,
                    p.prehsol_coti4, p.prehsol_coti4_name,
                    s31.nom_usuario AS aprob_cat_nombre, s31.fecha AS aprob_cat_fecha, s31.hora AS aprob_cat_hora,
                    s41.nom_usuario AS aprob_5k_nombre, s41.fecha AS aprob_5k_fecha, s41.hora AS aprob_5k_hora,
                    s41.resolucion AS aprob_5k_res, s41.active AS aprob_5k_active
                FROM prehsol p
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN cecosto c ON p.id_cc = c.id_cc
                LEFT JOIN cat_gerencias cat ON p.id_categoria = cat.id
                LEFT JOIN sol_compra_estado s31 ON s31.id_sol = p.id_prehsol AND s31.orden = 31
                LEFT JOIN sol_compra_estado s41 ON s41.id_sol = p.id_prehsol AND s41.orden = 41
                WHERE $whereSql
                ORDER BY p.prehsol_fecha ASC, p.prehsol_hora ASC
                $limitSql";

        return $this->findAll($qry, $params);
    }

    public function count_solicitudes_pendientes_oc($filtros)
    {
        $where = ["p.prehsol_estado = 51"];
        $params = [];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(p.prehsol_fecha) = :mes AND YEAR(p.prehsol_fecha) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['id_cc'])) {
            $where[] = "p.id_cc = :id_cc";
            $params[':id_cc'] = $filtros['id_cc'];
        }
        if (!empty($filtros['observacion'])) {
            $where[] = "(p.prehsol_obs1 LIKE concat('%',:observacion,'%') OR p.obs_cate LIKE concat('%',:observacion,'%'))";
            $params[':observacion'] = $filtros['observacion'];
        }

        $whereSql = implode(" AND ", $where);

        $qry = "SELECT COUNT(p.id_prehsol) as total FROM prehsol p WHERE $whereSql";
        $row = $this->find($qry, $params);

        return $row && isset($row->total) ? (int) $row->total : 0;
    }


    // =========================================================
    // MOTOR DE GENERACIÓN DE ORDENES DE COMPRA (ESTADO 51)
    // =========================================================

    // 1. Extrae y aumenta el correlativo de OC por empresa con candado (Evita duplicados)
    public function get_and_increment_empresa_oc($id_empresa)
    {
        // Bloqueamos la fila de la empresa hasta que termine la transacción
        $row = $this->find("SELECT numero_oc FROM empresa WHERE id_empresa = :id FOR UPDATE", [':id' => $id_empresa]);

        $current_val = $row && isset($row->numero_oc) ? (int) $row->numero_oc : 0;
        $new_val = $current_val + 1;

        // Actualizamos el nuevo valor
        $this->query("UPDATE empresa SET numero_oc = :new_val WHERE id_empresa = :id", [
            ':new_val' => $new_val,
            ':id' => $id_empresa
        ]);

        return $new_val;
    }

    // 2. Guarda los datos de auditoría en la cabecera (prehsol)
    public function update_prehsol_generar_oc($id_prehsol, $usuario, $obs, $estado_final)
    {
        $qry = "UPDATE prehsol SET 
                    prehsol_estado = :estado,
                    prehsol_oc_usuario = :usr,
                    prehsol_oc_obs = :obs
                WHERE id_prehsol = :id";

        return $this->query($qry, [
            ':estado' => $estado_final,
            ':usr' => $usuario,
            ':obs' => $obs,
            ':id' => $id_prehsol
        ]);
    }

    // 3. Estampa cada ítem (predsol) con su número de OC respectivo
    public function update_predsol_oc($id_predsol, $numero_oc, $usuario, $fecha, $hora)
    {
        $qry = "UPDATE predsol SET 
                    predsol_numero_oc = :num_oc,
                    predsol_usuario_oc = :usr,
                    predsol_fecha_oc = :f,
                    predsol_hora_oc = :h,
                    predsol_estado_oc = 61 /* 🏗️ ESTA LÍNEA FALTABA: Inicializa la OC en Revisión */
                WHERE id_predsol = :id";

        return $this->query($qry, [
            ':num_oc' => $numero_oc,
            ':usr' => $usuario,
            ':f' => $fecha,
            ':h' => $hora,
            ':id' => $id_predsol
        ]);
    }

    // =========================================================
    // CONSULTA HISTÓRICA DE ÓRDENES DE COMPRA (ESTADOS >= 61)
    // =========================================================

    public function get_historial_oc($filtros, $offset = 0, $limit = 20)
    {
        // 🏗️ CAMBIO: Ahora filtramos por el estado independiente de la OC
        $where = ["d.predsol_estado_oc >= 61", "d.predsol_numero_oc IS NOT NULL", "d.predsol_numero_oc != ''"];
        $params = [];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(d.predsol_fecha_oc) = :mes AND YEAR(d.predsol_fecha_oc) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['search'])) {
            $where[] = "(d.predsol_numero_oc LIKE concat('%',:search,'%') OR prov.CPANOM LIKE concat('%',:search,'%'))";
            $params[':search'] = $filtros['search'];
        }

        $whereSql = implode(" AND ", $where);
        $limitSql = "LIMIT " . (int) $offset . ", " . (int) $limit;

        $qry = "SELECT
                    p.id_prehsol,
                    p.id_empresa, e.emp_nombre,
                    d.predsol_numero_oc,
                    d.id_proveedor, prov.CPANOM as prov_nombre,
                    SUM(d.predsol_total) as monto_oc,
                    p.moneda,
                    d.predsol_usuario_oc as analista,
                    d.predsol_fecha_oc as fecha_oc,
                    d.predsol_hora_oc as hora_oc,
                    /* 🏗️ CAMBIO: Extraemos el estado individual de la OC */
                    d.predsol_estado_oc as estado_oc
                FROM predsol d
                INNER JOIN prehsol p ON d.id_prehsol = p.id_prehsol
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN proveedor_as400 prov ON d.id_proveedor = prov.CPACOD AND p.id_empresa = prov.id_empresa
                WHERE $whereSql
                /* 🏗️ CAMBIO: Agrupamos por el nuevo campo */
                GROUP BY p.id_prehsol, p.id_empresa, e.emp_nombre, d.predsol_numero_oc, d.id_proveedor, prov.CPANOM, p.moneda, d.predsol_usuario_oc, d.predsol_fecha_oc, d.predsol_hora_oc, d.predsol_estado_oc
                ORDER BY d.predsol_fecha_oc DESC, d.predsol_hora_oc DESC
                $limitSql";

        return $this->findAll($qry, $params);
    }

    public function count_historial_oc($filtros)
    {
        // 🏗️ CAMBIO: Ajustamos el filtro aquí también
        $where = ["d.predsol_estado_oc >= 61", "d.predsol_numero_oc IS NOT NULL", "d.predsol_numero_oc != ''"];
        $params = [];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(d.predsol_fecha_oc) = :mes AND YEAR(d.predsol_fecha_oc) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['search'])) {
            $where[] = "(d.predsol_numero_oc LIKE concat('%',:search,'%') OR prov.CPANOM LIKE concat('%',:search,'%'))";
            $params[':search'] = $filtros['search'];
        }

        $whereSql = implode(" AND ", $where);

        $qry = "SELECT COUNT(DISTINCT CONCAT(p.id_empresa, '-', d.predsol_numero_oc, '-', d.id_proveedor)) as total 
                FROM predsol d
                INNER JOIN prehsol p ON d.id_prehsol = p.id_prehsol
                LEFT JOIN proveedor_as400 prov ON d.id_proveedor = prov.CPACOD AND p.id_empresa = prov.id_empresa
                WHERE $whereSql";

        $row = $this->find($qry, $params);
        return $row && isset($row->total) ? (int) $row->total : 0;
    }


    // =========================================================
    // CONSULTAS EXCLUSIVAS PARA UNA ORDEN DE COMPRA (ESTADO 61+)
    // =========================================================

    // 1. Obtener cabecera agrupada con datos del Proveedor y Analista de la OC
    public function get_oc_cabecera($id_prehsol, $numero_oc)
    {
        $qry = "SELECT
                    p.*, e.emp_nombre, c.cc_descripcion, cat.categoria as cat_descripcion,
                    d.predsol_numero_oc, d.id_proveedor, prov.CPANOM as prov_nombre,
                    /* 🏗️ SOLUCIÓN: Subconsulta segura para calcular el total sin usar GROUP BY */
                    (SELECT SUM(predsol_total) FROM predsol WHERE id_prehsol = p.id_prehsol AND predsol_numero_oc = d.predsol_numero_oc) as monto_oc,
                    d.predsol_usuario_oc as analista, 
                    d.predsol_fecha_oc as fecha_oc, 
                    d.predsol_hora_oc as hora_oc,
                    d.predsol_estado_oc as estado_oc,
                    d.predsol_obs_jefe
                FROM predsol d
                INNER JOIN prehsol p ON d.id_prehsol = p.id_prehsol
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN cecosto c ON d.id_cc = c.id_cc
                LEFT JOIN cat_gerencias cat ON p.id_categoria = cat.id
                LEFT JOIN proveedor_as400 prov ON d.id_proveedor = prov.CPACOD AND p.id_empresa = prov.id_empresa
                WHERE p.id_prehsol = :id AND d.predsol_numero_oc = :oc
                LIMIT 1 /* 🏗️ SOLUCIÓN: Aseguramos que solo devuelva la cabecera */";

        return $this->find($qry, [':id' => $id_prehsol, ':oc' => $numero_oc]);
    }

    // 2. Obtener solo los ítems que pertenecen a esta OC
    public function get_oc_detalle($id_prehsol, $numero_oc)
    {
        $qry = "SELECT d.*, p.CPANOM as prov_nombre, c.cc_descripcion 
                FROM predsol d
                LEFT JOIN proveedor_as400 p ON d.id_proveedor = p.CPACOD AND d.id_empresa = p.id_empresa
                LEFT JOIN cecosto c ON d.id_cc = c.id_cc
                WHERE d.id_prehsol = :id AND d.predsol_numero_oc = :oc
                ORDER BY d.id_predsol ASC";

        return $this->findAll($qry, [':id' => $id_prehsol, ':oc' => $numero_oc]);
    }

    // 3. Aprobar OC por Jefe de Compras (Avanza a Estado 71)
    public function approve_oc_jefe($id_prehsol, $observacion, $usuario)
    {
        $qry = "UPDATE prehsol SET 
                    prehsol_estado = 71,
                    prehsol_verificacion = :obs,
                    prehsol_verificacion_usuario = :usr
                WHERE id_prehsol = :id";

        return $this->query($qry, [
            ':obs' => $observacion,
            ':usr' => $usuario,
            ':id' => $id_prehsol
        ]);
    }

    // =========================================================
    // NUEVA TRAZABILIDAD INDEPENDIENTE PARA ÓRDENES DE COMPRA
    // =========================================================

    // 1. Siembra la trazabilidad hija al generar una nueva OC
    public function insert_oc_trazabilidad($numero_oc, $id_empresa)
    {
        $pasos = [
            [61, 'Revisión OC', 'A', 'En proceso'],
            [71, 'OC en Proveedor', 'P', 'Pendiente'],
            [81, 'Recepción', 'P', 'Pendiente'],
            [91, 'Cerrar OC', 'P', 'Pendiente']
        ];

        foreach ($pasos as $p) {
            $qry = "INSERT INTO oc_compra_estado (numero_oc, id_empresa, orden, estado_descr, resolucion, descripcion) 
                    VALUES (:oc, :emp, :ord, :des, :res, :desc)";
            $this->query($qry, [
                ':oc' => $numero_oc,
                ':emp' => $id_empresa,
                ':ord' => $p[0],
                ':des' => $p[1],
                ':res' => $p[2],
                ':desc' => $p[3]
            ]);
        }
    }
}
?>