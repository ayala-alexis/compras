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

    // =========================================================
    // CATÁLOGOS DEL SISTEMA
    // =========================================================

    public function get_empresas_user($id_usuario)
    {
        return $this->findAll(
            "select distinct e.id_empresa keyCode, e.emp_nombre keyValue
            from acc_emp_cc ac
            inner join empresa e on ac.id_empresa = e.id_empresa 
            where id_usuario = :id
            order by e.emp_nombre",
            [':id' => $id_usuario]
        );
    }

    public function get_cc_empresa_user($id, $id_usuario)
    {
        return $this->findAll(
            "select distinct c.id_cc keyCode, c.cc_descripcion keyValue
            from acc_emp_cc ac
            inner join cecosto c on ac.id_empresa = c.id_empresa 
            where ac.id_usuario = :id_usuario and ac.id_empresa = :id_empresa
            order by c.cc_descripcion",
            [':id_usuario' => $id_usuario, ':id_empresa' => $id]
        );
    }

    public function get_categorias_tipo($tipo)
    {
        return $this->findAll(
            "select id keyCode, categoria keyValue, gcia keyGroup
            from cat_gerencias 
            where mod_solicitud = :tipo
            order by gcia,categoria",
            [':tipo' => $tipo]
        );
    }

    public function get_categorias_compras()
    {
        return $this->get_categorias_tipo("Solicitud de compras");
    }

    public function get_productos_cat($id, $search)
    {
        return $this->findAll(
            "select id_producto keyCode, codigo_producto keyValue, descripcion_producto keyDescription
            from fproductos
            where id_categoria = :id and lower(concat(codigo_producto,' ',descripcion_producto)) like lower(concat('%',:search,'%'))
            order by descripcion_producto limit 10",
            [':id' => $id, ':search' => $search]
        );
    }

    public function get_proveedores_as400($id_empresa, $search)
    {
        $qry = "SELECT CPACOD as keyCode, trim(CPANOM) as keyValue
                FROM proveedor_as400
                WHERE id_empresa = :emp 
                AND (CPANOM LIKE concat('%',:search,'%') OR CPACOD LIKE concat('%',:search,'%'))
                LIMIT 15";
        return $this->findAll($qry, [':emp' => $id_empresa, ':search' => $search]);
    }


    // =========================================================
    // TRAZABILIDAD UI (SIMULADOR FRONT-END)
    // =========================================================

    public function traza_compra_creacion($id_usuario, $id_empresa, $id_cc, $id_categoria)
    {
        $traza = [];

        // 1. Adiciona usuario solicitante (Este siempre existe por la sesión)
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
        // 🏗️ CORRECCIÓN ARQUITECTÓNICA: Si la categoría es 0 (pantalla recién cargada), 
        // forzamos a que el nodo nazca para que la UI dibuje la plantilla completa.
        if ($id_categoria == 0 || $this->traza_compra_requiere_aprobador_5k($id_categoria)) {
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
    public function traza_compra_solicitante($id_usuario)
    {
        return $this->find("SELECT 'active' estado, id_usuario, usr_usuario usr_id, usr_nombre usr_name, id_rol FROM usuario WHERE id_usuario = :id_usuario", [':id_usuario' => $id_usuario]);
    }

    // Datos de trazabilidad -> Aprobador de CC
    public function traza_compra_aprobador_cc($id_empresa, $id_cc)
    {
        $res = $this->find(
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

        // 🏗️ CORRECCIÓN ARQUITECTÓNICA: Si no hay resultados (porque id_cc es 0), 
        // no devolvemos un error, sino un "objeto maqueta" para que el frontend dibuje el círculo gris.
        if (!$res) {
            return (object) [
                'estado' => 'pending',
                'id' => 0,
                'usr_id' => 'Depende de Cco.',
                'usr_name' => 'Depende de Cco.',
                'id_rol' => '0'
            ];
        }

        return $res;
    }

    public function traza_compra_aprobador_categoria($id_categoria)
    {
        $tmp = $this->findAll("SELECT 'pending' estado, u.id_usuario id, u.usr_usuario usr_id, u.usr_nombre usr_name, u.id_rol, c.requiere_aprobador_5k FROM cat_gerencias c INNER JOIN usuario u ON u.id_usuario IN (c.user_aproba_1,c.user_aproba_2,c.user_aproba_3) WHERE c.id = :id_categoria AND c.mod_solicitud = 'Solicitud de compras'", [':id_categoria' => $id_categoria]);

        if (empty($tmp)) {
            return (object) ['estado' => 'pending', 'id' => 0, 'usr_id' => 'Depende de monto', 'usr_name' => 'Depende de monto', 'id_rol' => '0', 'requiere_aprobador_5k' => 0];
        } else {
            if (count($tmp) > 1) {
                return (object) ['estado' => 'pending', 'id' => 0, 'usr_id' => 'Depende de monto', 'usr_name' => 'Depende de monto', 'id_rol' => '0', 'requiere_aprobador_5k' => isset($tmp[0]->requiere_aprobador_5k) ? $tmp[0]->requiere_aprobador_5k : 0];
            } else {
                return $tmp[0];
            }
        }
    }

    public function traza_compra_requiere_aprobador_5k($id_categoria)
    {
        $tmp = $this->find("SELECT requiere_aprobador_5k FROM cat_gerencias WHERE id = :id_categoria AND mod_solicitud = 'Solicitud de compras'", [':id_categoria' => $id_categoria]);
        return empty($tmp) ? false : ($tmp->requiere_aprobador_5k == 1);
    }

    // =========================================================
    // CREACIÓN DE TRAZABILIDAD (BASE DE DATOS)
    // =========================================================

    public function create_trazabilidad_compras($id_solicitud, $id_empresa, $id_cc, $id_categoria, $id_usuario, $usr_id, $usr_name)
    {
        // 1. Solicitante
        $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 1, ':estado_descr' => 'Solicitante Cco.', ':descripcion' => 'Solicitado', ':fecha' => date('Y-m-d'), ':hora' => date('H:i:s'), ':orden' => 1, ':id_usuario' => $id_usuario, ':usuario' => $usr_id, ':nom_usuario' => $usr_name, ':active' => 1, ':resolucion' => 'C']);

        // 2. Cotización
        $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 11, ':estado_descr' => 'Cotización', ':descripcion' => 'En proceso', ':fecha' => null, ':hora' => null, ':orden' => 11, ':id_usuario' => 0, ':usuario' => "Analista de Compras", ':nom_usuario' => "Analista de Compras", ':active' => 1, ':resolucion' => 'P']);

        // 3. Autorizador CC
        $rs = $this->traza_compra_aprobador_cc($id_empresa, $id_cc);
        if (!empty($rs)) {
            $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 21, ':estado_descr' => 'Autorizador Cco.', ':descripcion' => 'Pendiente', ':fecha' => null, ':hora' => null, ':orden' => 21, ':id_usuario' => $rs->id, ':usuario' => $rs->usr_id, ':nom_usuario' => $rs->usr_name, ':active' => 1, ':resolucion' => 'P']);
        }

        // 4. Autorizador Categoría
        $rs = $this->traza_compra_aprobador_categoria($id_categoria);
        if (!empty($rs)) {
            $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 31, ':estado_descr' => 'Autorizador Categoría', ':descripcion' => 'Pendiente', ':fecha' => null, ':hora' => null, ':orden' => 31, ':id_usuario' => $rs->id, ':usuario' => $rs->usr_id, ':nom_usuario' => $rs->usr_name, ':active' => 1, ':resolucion' => 'P']);
        }

        // 5. Autorizador 5K (Si aplica)
        if (!empty($rs) && isset($rs->requiere_aprobador_5k) && $rs->requiere_aprobador_5k == 1) {
            $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 41, ':estado_descr' => 'Autorizador >= $5K', ':descripcion' => 'Pendiente', ':fecha' => null, ':hora' => null, ':orden' => 41, ':id_usuario' => $rs->id, ':usuario' => $rs->usr_id, ':nom_usuario' => $rs->usr_name, ':active' => 1, ':resolucion' => 'P']);
        }

        // 6. Orden de Compra
        $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 51, ':estado_descr' => 'Orden de Compra', ':descripcion' => 'Pendiente', ':fecha' => null, ':hora' => null, ':orden' => 51, ':id_usuario' => 0, ':usuario' => "Analista de Compras", ':nom_usuario' => "Analista de Compras", ':active' => 1, ':resolucion' => 'P']);

        // 7. Revisión OC
        $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 61, ':estado_descr' => 'Revisión OC', ':descripcion' => 'Pendiente', ':fecha' => null, ':hora' => null, ':orden' => 61, ':id_usuario' => 0, ':usuario' => "Jefe Compras", ':nom_usuario' => "Jefe Compras", ':active' => 1, ':resolucion' => 'P']);

        // 8. Proveedor
        $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 71, ':estado_descr' => 'OC en Proveedor', ':descripcion' => 'Pendiente', ':fecha' => null, ':hora' => null, ':orden' => 71, ':id_usuario' => 0, ':usuario' => "Proveedor", ':nom_usuario' => "Proveedor", ':active' => 1, ':resolucion' => 'P']);

        // 9. Recepción
        $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 81, ':estado_descr' => 'Recepción', ':descripcion' => 'Pendiente', ':fecha' => null, ':hora' => null, ':orden' => 81, ':id_usuario' => $id_usuario, ':usuario' => $usr_id, ':nom_usuario' => $usr_name, ':active' => 1, ':resolucion' => 'P']);

        // 10. Cerrar OC
        $this->create_trazabilidad_item([':id_sol' => $id_solicitud, ':estado' => 91, ':estado_descr' => 'Cerrar OC', ':descripcion' => 'Pendiente', ':fecha' => null, ':hora' => null, ':orden' => 91, ':id_usuario' => 0, ':usuario' => "Analista de Compras", ':nom_usuario' => "Analista de Compras", ':active' => 1, ':resolucion' => 'P']);
    }

    // 🏗️ Inserción directa en tabla usando el helper create()
    public function create_trazabilidad_item($traza)
    {
        $sql = "INSERT INTO sol_compra_estado
                (id_sol, estado, estado_descr, descripcion, fecha, hora, orden, id_usuario, usuario, nom_usuario, active, resolucion) 
                VALUES
                (:id_sol, :estado, :estado_descr, :descripcion, :fecha, :hora, :orden, :id_usuario, :usuario, :nom_usuario, :active, :resolucion)";

        $res = $this->create($sql, $traza);

        // 🚨 PREVENCIÓN DE FALLO SILENCIOSO: Si MySQL rechaza el Insert, forzamos el colapso controlado
        if (!$res) {
            throw new Exception("La Base de Datos rechazó la inserción del paso: " . json_encode($traza) . ". Verifique si la tabla acepta valores nulos.");
        }
        return $res;
    }

    // =========================================================
    // REGLAS DE NEGOCIO Y APROBADORES
    // =========================================================

    public function get_categoria_by_solicitud($id_solicitud)
    {
        $qry = "SELECT id_categoria FROM compras_enc WHERE id = :id";
        $res = $this->find($qry, [':id' => $id_solicitud]);
        return $res ? (int) $res->id_categoria : 0;
    }

    public function get_autorizador_categoria($id_categoria, $total)
    {
        $campo_aprueba = 'user_aproba_1';
        if ($total > 200 && $total <= 1000)
            $campo_aprueba = 'user_aproba_2';
        elseif ($total > 1000)
            $campo_aprueba = 'user_aproba_3';

        $qry = "SELECT u.id_usuario, u.usr_usuario, u.usr_nombre 
                FROM cat_gerencias cg
                INNER JOIN usuario u ON u.id_usuario = cg.{$campo_aprueba}
                WHERE cg.id = :id_categoria";
        $res = $this->find($qry, [':id_categoria' => $id_categoria]);
        return $res ? $res : null;
    }

    public function get_autorizador_5k($id_categoria)
    {
        $qry = "SELECT u.id_usuario, u.usr_usuario, u.usr_nombre 
                FROM cat_gerencias cg
                INNER JOIN usuario u ON u.id_usuario = cg.id_aprueba_5k
                WHERE cg.id = :id_categoria AND cg.requiere_aprobador_5k = 1";
        $res = $this->find($qry, [':id_categoria' => $id_categoria]);
        return $res ? $res : null;
    }

    // =========================================================
    // CREACIÓN DE SOLICITUD DE COMPRAS
    // =========================================================

    public function get_and_increment_cc_solc($id_empresa, $id_cc)
    {
        try {
            $this->query("START TRANSACTION");
            $row = $this->find("SELECT cc_solc FROM cecosto WHERE id_empresa = :id_empresa AND id_cc = :id_cc FOR UPDATE", [':id_empresa' => $id_empresa, ':id_cc' => $id_cc]);
            $current_val = $row && isset($row->cc_solc) ? (int) $row->cc_solc : 0;
            $new_val = $current_val + 1;
            $this->query("UPDATE cecosto SET cc_solc = :new_val WHERE id_empresa = :id_empresa AND id_cc = :id_cc", [':new_val' => $new_val, ':id_empresa' => $id_empresa, ':id_cc' => $id_cc]);
            $this->query("COMMIT");
            return $new_val;
        } catch (Exception $e) {
            $this->query("ROLLBACK");
            return 1;
        }
    }

    public function create_solicitud_encabezado($d)
    {
        $qry = "INSERT INTO compras_enc (
                    id_empresa, id_cc, id_categoria, numero_solicitud, 
                    id_usuario_crea, usuario_crea, fecha_crea, hora_crea, 
                    observacion_crea, estado
                ) VALUES (
                    :id_empresa, :id_cc, :id_categoria, :numero_solicitud, 
                    :id_usuario_crea, :usuario_crea, :fecha_crea, :hora_crea, 
                    :observacion_crea, :estado
                )";

        return $this->create($qry, [
            ':id_empresa' => $d['id_empresa'],
            ':id_cc' => $d['id_cc'],
            ':id_categoria' => $d['id_categoria'],
            ':numero_solicitud' => $d['numero_solicitud'],
            ':id_usuario_crea' => $d['id_usuario_crea'],
            ':usuario_crea' => $d['usuario_crea'],
            ':fecha_crea' => $d['fecha_crea'],
            ':hora_crea' => $d['hora_crea'],
            ':observacion_crea' => $d['observacion_crea'],
            ':estado' => $d['estado']
        ]);
    }

    public function create_solicitud_detalle($d)
    {
        // 🏗️ Subtotal es VIRTUAL GENERATED, ya no se inserta manualmente
        $qry = "INSERT INTO compras_det (
                    id_enc, id_empresa, id_cc, id_producto, codigo_producto, 
                    descripcion_producto, cantidad, precio_unitario
                ) VALUES (
                    :id_enc, :id_empresa, :id_cc, :id_producto, :codigo_producto, 
                    :descripcion_producto, :cantidad, :precio_unitario
                )";

        return $this->create($qry, [
            ':id_enc' => $d['id_enc'],
            ':id_empresa' => $d['id_empresa'],
            ':id_cc' => $d['id_cc'],
            ':id_producto' => empty($d['id_producto']) ? 0 : $d['id_producto'],
            ':codigo_producto' => $d['codigo_producto'],
            ':descripcion_producto' => $d['descripcion_producto'],
            ':cantidad' => $d['cantidad'],
            ':precio_unitario' => 0.00 // Inicia en 0 hasta que el analista cotice
        ]);
    }

    // =========================================================
    // LECTURA DE SOLICITUDES (CONSULTAS BASE)
    // =========================================================

    public function get_solicitud_cabecera($id_solicitud)
    {
        $qry = "SELECT p.*, e.emp_nombre, e.moneda as moneda_cia, c.cc_descripcion, g.categoria as cat_descripcion
                FROM compras_enc p
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN cecosto c ON p.id_cc = c.id_cc
                LEFT JOIN cat_gerencias g ON p.id_categoria = g.id
                WHERE p.id = :id";
        return $this->find($qry, [':id' => $id_solicitud]);
    }

    public function get_solicitud_detalle($id_solicitud)
    {
        $qry = "SELECT d.*, p.CPANOM as prov_nombre 
                FROM compras_det d
                LEFT JOIN proveedor_as400 p ON d.id_proveedor = p.CPACOD AND d.id_empresa = p.id_empresa
                WHERE d.id_enc = :id 
                ORDER BY d.id ASC";
        return $this->findAll($qry, [':id' => $id_solicitud]);
    }

    // =========================================================
    // BANDEJA: COTIZACIÓN (ESTADO 11)
    // =========================================================

    public function get_solicitudes_cotizacion($filtros, $offset = 0, $limit = 20)
    {
        $where = ["p.estado = 11"];
        $params = [];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(p.fecha_crea) = :mes AND YEAR(p.fecha_crea) = :anio";
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
            $where[] = "p.observacion_crea LIKE concat('%',:observacion,'%')";
            $params[':observacion'] = $filtros['observacion'];
        }

        $whereSql = implode(" AND ", $where);
        $limitSql = "LIMIT " . (int) $offset . ", " . (int) $limit;

        $qry = "SELECT
                    p.*, e.emp_nombre, c.cc_descripcion, cat.categoria as cat_descripcion
                FROM compras_enc p
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN cecosto c ON p.id_cc = c.id_cc
                LEFT JOIN cat_gerencias cat ON p.id_categoria = cat.id
                WHERE $whereSql
                ORDER BY p.fecha_crea DESC, p.hora_crea DESC
                $limitSql";

        return $this->findAll($qry, $params);
    }

    public function count_solicitudes_cotizacion($filtros)
    {
        $where = ["estado = 11"];
        $params = [];
        // Mismos filtros que arriba...
        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(fecha_crea) = :mes AND YEAR(fecha_crea) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        $whereSql = implode(" AND ", $where);
        $qry = "SELECT COUNT(id) as total FROM compras_enc WHERE $whereSql";
        $row = $this->find($qry, $params);
        return $row ? (int) $row->total : 0;
    }

    // =========================================================
    // PROCESAMIENTO DE COTIZACIONES Y AUDITORÍA
    // =========================================================

    public function update_item_cotizacion($id_detalle, $id_proveedor, $precio, $observacion, $fecha, $hora, $id_usuario, $usuario)
    {
        // 🏗️ Subtotal es VIRTUAL GENERATED, ya no se actualiza manualmente
        $qry = "UPDATE compras_det SET 
                    id_proveedor = :id_proveedor,
                    precio_unitario = :precio,
                    observacion_analista = :obs,
                    fecha_mod_analista = :fecha,
                    hora_mod_analista = :hora,
                    id_usuario_mod_analista = :id_usuario,
                    usuario_mod_analista = :usuario
                WHERE id = :id_detalle";
        return $this->query($qry, [
            ':id_proveedor' => $id_proveedor,
            ':precio' => $precio,
            ':obs' => $observacion,
            ':fecha' => $fecha,
            ':hora' => $hora,
            ':id_usuario' => $id_usuario,
            ':usuario' => $usuario,
            ':id_detalle' => $id_detalle
        ]);
    }

    public function update_cotizacion_cabecera($id_solicitud, $d)
    {
        $qry = "UPDATE compras_enc SET 
                    estado = 21,
                    monto_total = :monto,
                    moneda = :moneda,
                    observacion_cotiza = :obs,
                    id_usuario_cotiza = :id_user,
                    usuario_cotiza = :usr,
                    fecha_cotiza = :fecha,
                    hora_cotiza = :hora
                WHERE id = :id";
        return $this->query($qry, [
            ':monto' => $d['total_monto'],
            ':moneda' => $d['moneda'],
            ':obs' => $d['obs_analista'],
            ':id_user' => $d['id_usuario'],
            ':usr' => $d['usuario'],
            ':fecha' => date('Y-m-d'),
            ':hora' => date('H:i:s'),
            ':id' => $id_solicitud
        ]);
    }

    // =========================================================
    // BANDEJA Y PROCESAMIENTO: APROBADOR CCO (ESTADO 21)
    // =========================================================

    public function get_solicitudes_aprobacion_cc($id_usuario, $filtros, $offset = 0, $limit = 20)
    {
        $where = ["p.estado = 21", "acc.id_usuario = :id_usuario"];
        $params = [':id_usuario' => $id_usuario];

        $whereSql = implode(" AND ", $where);
        $limitSql = "LIMIT " . (int) $offset . ", " . (int) $limit;

        $qry = "SELECT p.*, e.emp_nombre, c.cc_descripcion, cat.categoria AS cat_descripcion
                FROM compras_enc p
                INNER JOIN acc_emp_cc acc ON p.id_empresa = acc.id_empresa AND p.id_cc = acc.id_cc
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN cecosto c ON p.id_cc = c.id_cc
                LEFT JOIN cat_gerencias cat ON p.id_categoria = cat.id
                WHERE $whereSql ORDER BY p.fecha_crea ASC, p.hora_crea ASC $limitSql";
        return $this->findAll($qry, $params);
    }

    public function update_cantidad_item($id_detalle, $cantidad)
    {
        $qry = "UPDATE compras_det SET cantidad = :cant, cantidad_auditada = :cant WHERE id = :id";
        return $this->query($qry, [':cant' => $cantidad, ':id' => $id_detalle]);
    }

    public function delete_item_cotizacion($id_detalle)
    {
        return $this->query("DELETE FROM compras_det WHERE id = :id", [':id' => $id_detalle]);
    }

    public function update_gran_total_cabecera($id_solicitud, $gran_total)
    {
        return $this->query("UPDATE compras_enc SET monto_total = :total WHERE id = :id", [':total' => $gran_total, ':id' => $id_solicitud]);
    }

    public function update_estado_y_aprobacion($id_solicitud, $estado_final, $campos_auditoria)
    {
        $set_sql = "estado = :estado";
        $params = [':estado' => $estado_final, ':id' => $id_solicitud];

        foreach ($campos_auditoria as $campo => $valor) {
            $set_sql .= ", {$campo} = :{$campo}";
            $params[":{$campo}"] = $valor;
        }

        $qry = "UPDATE compras_enc SET {$set_sql} WHERE id = :id";
        return $this->query($qry, $params);
    }

    // =========================================================
    // BANDEJA Y GENERACIÓN DE ORDENES DE COMPRA (ESTADO 51)
    // =========================================================

    public function get_solicitudes_pendientes_oc($filtros, $offset = 0, $limit = 20)
    {
        $where = ["p.estado = 51"];
        $params = [];
        $whereSql = implode(" AND ", $where);
        $limitSql = "LIMIT " . (int) $offset . ", " . (int) $limit;

        $qry = "SELECT p.*, e.emp_nombre, c.cc_descripcion, cat.categoria AS cat_descripcion
                FROM compras_enc p
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN cecosto c ON p.id_cc = c.id_cc
                LEFT JOIN cat_gerencias cat ON p.id_categoria = cat.id
                WHERE $whereSql ORDER BY p.fecha_crea ASC, p.hora_crea ASC $limitSql";
        return $this->findAll($qry, $params);
    }

    public function get_and_increment_empresa_oc($id_empresa)
    {
        $row = $this->find("SELECT numero_oc FROM empresa WHERE id_empresa = :id FOR UPDATE", [':id' => $id_empresa]);
        $new_val = ($row && isset($row->numero_oc) ? (int) $row->numero_oc : 0) + 1;
        $this->query("UPDATE empresa SET numero_oc = :new_val WHERE id_empresa = :id", [':new_val' => $new_val, ':id' => $id_empresa]);
        return $new_val;
    }

    public function update_encabezado_generar_oc($id_solicitud, $d)
    {
        $qry = "UPDATE compras_enc SET 
                    estado = :estado,
                    id_usuario_aprobador_oc = :id_user,
                    usuario_aprobador_oc = :usr,
                    fecha_aprobador_oc = :fecha,
                    hora_aprobador_oc = :hora,
                    observacion_oc = :obs
                WHERE id = :id";
        return $this->query($qry, [
            ':estado' => $d['estado'],
            ':id_user' => $d['id_usuario'],
            ':usr' => $d['usuario'],
            ':fecha' => date('Y-m-d'),
            ':hora' => date('H:i:s'),
            ':obs' => $d['obs'],
            ':id' => $id_solicitud
        ]);
    }

    public function update_detalle_oc($id_detalle, $numero_oc)
    {
        $qry = "UPDATE compras_det SET 
                    numero_oc = :num_oc,
                    estado_oc = 'GE' /* OC Generada */
                WHERE id = :id";
        return $this->query($qry, [':num_oc' => $numero_oc, ':id' => $id_detalle]);
    }

    // =========================================================
    // BANDEJA DE ÓRDENES DE COMPRA (ESTADOS DE OC)
    // =========================================================

    public function get_historial_oc($filtros, $offset = 0, $limit = 20)
    {
        // Traemos todas las que ya tienen numero_oc asignado
        $where = ["d.numero_oc IS NOT NULL"];
        $params = [];
        $whereSql = implode(" AND ", $where);
        $limitSql = "LIMIT " . (int) $offset . ", " . (int) $limit;

        $qry = "SELECT
                    p.id as id_solicitud,
                    p.id_empresa, e.emp_nombre,
                    d.numero_oc,
                    d.id_proveedor, prov.CPANOM as prov_nombre,
                    SUM(d.subtotal) as monto_oc,
                    p.moneda,
                    p.usuario_aprobador_oc as analista,
                    p.fecha_aprobador_oc as fecha_oc,
                    p.hora_aprobador_oc as hora_oc,
                    d.estado_oc
                FROM compras_det d
                INNER JOIN compras_enc p ON d.id_enc = p.id
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN proveedor_as400 prov ON d.id_proveedor = prov.CPACOD AND p.id_empresa = prov.id_empresa
                WHERE $whereSql
                GROUP BY p.id, p.id_empresa, e.emp_nombre, d.numero_oc, d.id_proveedor, prov.CPANOM, p.moneda, p.usuario_aprobador_oc, p.fecha_aprobador_oc, p.hora_aprobador_oc, d.estado_oc
                ORDER BY p.fecha_aprobador_oc DESC, p.hora_aprobador_oc DESC
                $limitSql";

        return $this->findAll($qry, $params);
    }

    public function get_oc_cabecera($id_solicitud, $numero_oc)
    {
        $qry = "SELECT
                    p.*, e.emp_nombre, c.cc_descripcion, cat.categoria as cat_descripcion,
                    d.numero_oc, d.id_proveedor, prov.CPANOM as prov_nombre,
                    (SELECT SUM(subtotal) FROM compras_det WHERE id_enc = p.id AND numero_oc = d.numero_oc) as monto_oc,
                    d.estado_oc
                FROM compras_det d
                INNER JOIN compras_enc p ON d.id_enc = p.id
                LEFT JOIN empresa e ON p.id_empresa = e.id_empresa
                LEFT JOIN cecosto c ON p.id_cc = c.id_cc
                LEFT JOIN cat_gerencias cat ON p.id_categoria = cat.id
                LEFT JOIN proveedor_as400 prov ON d.id_proveedor = prov.CPACOD AND p.id_empresa = prov.id_empresa
                WHERE p.id = :id AND d.numero_oc = :oc
                LIMIT 1";

        return $this->find($qry, [':id' => $id_solicitud, ':oc' => $numero_oc]);
    }

    public function get_oc_detalle($id_solicitud, $numero_oc)
    {
        $qry = "SELECT d.*, p.CPANOM as prov_nombre, c.cc_descripcion 
                FROM compras_det d
                LEFT JOIN proveedor_as400 p ON d.id_proveedor = p.CPACOD AND d.id_empresa = p.id_empresa
                LEFT JOIN cecosto c ON d.id_cc = c.id_cc
                WHERE d.id_enc = :id AND d.numero_oc = :oc
                ORDER BY d.id ASC";

        return $this->findAll($qry, [':id' => $id_solicitud, ':oc' => $numero_oc]);
    }
    // =========================================================
    // RESTAURACIÓN DE HELPERS: TRAZABILIDAD DINÁMICA (LECTURA Y ACTUALIZACIÓN)
    // =========================================================

    // Extrae la línea de tiempo guardada para pintar las pantallas
    public function get_trazabilidad_solicitud($id_solicitud)
    {
        // 🏗️ FILTRO VISUAL: Excluimos explícitamente Revisión OC (61), Proveedor (71) y Cerrar OC (91)
        $qry = "SELECT * FROM sol_compra_estado 
                WHERE id_sol = :id AND orden NOT IN (61, 71, 91) 
                ORDER BY orden ASC";
        return $this->findAll($qry, [':id' => $id_solicitud]);
    }

    // Actualiza en vivo quién es el autorizador normal si el precio de cotización cambia
    public function update_trazabilidad_autorizador($id_solicitud, $autorizador)
    {
        $qry = "UPDATE sol_compra_estado SET id_usuario = :idu, usuario = :u, nom_usuario = :nu 
                WHERE id_sol = :id AND orden = 31 AND resolucion != 'C'";
        return $this->query($qry, [
            ':idu' => $autorizador->id_usuario,
            ':u' => $autorizador->usr_usuario,
            ':nu' => $autorizador->usr_nombre,
            ':id' => $id_solicitud
        ]);
    }

    // Actualiza en vivo al Director si el precio pasa de $5K
    public function update_trazabilidad_autorizador_5k($id_solicitud, $autorizador)
    {
        $qry = "UPDATE sol_compra_estado SET id_usuario = :idu, usuario = :u, nom_usuario = :nu 
                WHERE id_sol = :id AND orden = 41 AND resolucion != 'C'";
        return $this->query($qry, [
            ':idu' => $autorizador->id_usuario,
            ':u' => $autorizador->usr_usuario,
            ':nu' => $autorizador->usr_nombre,
            ':id' => $id_solicitud
        ]);
    }

    // Enciende o apaga visualmente el paso de los $5K en la trazabilidad
    public function toggle_trazabilidad_5k($id_solicitud, $is_active)
    {
        return $this->query("UPDATE sol_compra_estado SET active = :act WHERE id_sol = :id AND orden = 41", [
            ':act' => $is_active,
            ':id' => $id_solicitud
        ]);
    }

    // Usado por el Motor de Auto-Aprobación para saber qué paso sigue en la cadena
    public function get_siguiente_paso_activo($id_solicitud, $orden_actual)
    {
        $qry = "SELECT * FROM sol_compra_estado WHERE id_sol = :id AND orden > :ord AND active = 1 ORDER BY orden ASC LIMIT 1";
        return $this->find($qry, [':id' => $id_solicitud, ':ord' => $orden_actual]);
    }

    // =========================================================
    // RESTAURACIÓN DE HELPERS: PAGINACIÓN DE BANDEJAS (COUNT)
    // =========================================================

    public function count_solicitudes_aprobacion_cc($id_usuario, $filtros)
    {
        $where = ["p.estado = 21", "acc.id_usuario = :id_usuario"];
        $params = [':id_usuario' => $id_usuario];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(p.fecha_crea) = :mes AND YEAR(p.fecha_crea) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }

        $whereSql = implode(" AND ", $where);
        $qry = "SELECT COUNT(p.id) as total FROM compras_enc p INNER JOIN acc_emp_cc acc ON p.id_empresa = acc.id_empresa AND p.id_cc = acc.id_cc WHERE $whereSql";

        $row = $this->find($qry, $params);
        return $row ? (int) $row->total : 0;
    }

    public function count_solicitudes_pendientes_oc($filtros)
    {
        $where = ["estado = 51"];
        $params = [];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(fecha_crea) = :mes AND YEAR(fecha_crea) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }

        $whereSql = implode(" AND ", $where);
        $qry = "SELECT COUNT(id) as total FROM compras_enc WHERE $whereSql";

        $row = $this->find($qry, $params);
        return $row ? (int) $row->total : 0;
    }

    public function count_historial_oc($filtros)
    {
        $where = ["d.numero_oc IS NOT NULL"];
        $params = [];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            // Filtramos por fecha de la OC
            $where[] = "MONTH(p.fecha_aprobador_oc) = :mes AND YEAR(p.fecha_aprobador_oc) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "p.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['search'])) {
            $where[] = "d.numero_oc LIKE concat('%',:search,'%')";
            $params[':search'] = $filtros['search'];
        }

        $whereSql = implode(" AND ", $where);
        // Contamos OCs únicas
        $qry = "SELECT COUNT(DISTINCT d.numero_oc) as total FROM compras_det d INNER JOIN compras_enc p ON d.id_enc = p.id WHERE $whereSql";

        $row = $this->find($qry, $params);
        return $row ? (int) $row->total : 0;
    }

    // =========================================================
    // RESTAURACIÓN DE HELPERS: TRAZABILIDAD HIJA (ÓRDENES DE COMPRA)
    // =========================================================

    // Siembra la línea de tiempo secundaria cuando una OC se divide del documento padre
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
            $this->create($qry, [
                ':oc' => $numero_oc,
                ':emp' => $id_empresa,
                ':ord' => $p[0],
                ':des' => $p[1],
                ':res' => $p[2],
                ':desc' => $p[3]
            ]);
        }
    }
    // =========================================================
    // GESTIÓN DE ADJUNTOS (RELACIÓN 1:N)
    // =========================================================

    public function create_adjunto($id_solicitud, $tipo, $nombre_original, $ruta_archivo)
    {
        $qry = "INSERT INTO compras_adjuntos (id_solicitud, adjunto_tipo, nombre_archivo, ruta) 
                VALUES (:id, :tipo, :nom, :ruta)";
        return $this->create($qry, [
            ':id' => $id_solicitud,
            ':tipo' => $tipo,
            ':nom' => $nombre_original,
            ':ruta' => $ruta_archivo
        ]);
    }

    public function get_adjuntos_solicitud($id_solicitud)
    {
        return $this->findAll("SELECT * FROM compras_adjuntos WHERE id_solicitud = :id ORDER BY adjunto_tipo ASC", [':id' => $id_solicitud]);
    }

    // =========================================================
    // BANDEJA: AUTORIZADOR DE CATEGORÍA (PASO 31)
    // =========================================================
    // =========================================================
    // BANDEJA: AUTORIZADOR DE CATEGORÍA (PASO 31)
    // =========================================================
    public function get_solicitudes_aprobacion_categoria($id_usuario, $filtros, $offset = 0, $limit = 20)
    {
        $where = ["c.estado = 31", "traza.orden = 31", "traza.resolucion = 'A'", "traza.id_usuario = :idu"];
        $params = [':idu' => $id_usuario];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(c.fecha_crea) = :mes AND YEAR(c.fecha_crea) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "c.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['id_cc'])) {
            $where[] = "c.id_cc = :id_cc";
            $params[':id_cc'] = $filtros['id_cc'];
        }
        if (!empty($filtros['id_categoria'])) {
            $where[] = "c.id_categoria = :id_categoria";
            $params[':id_categoria'] = $filtros['id_categoria'];
        }
        if (!empty($filtros['observacion'])) {
            $where[] = "c.observacion_crea LIKE concat('%',:observacion,'%')";
            $params[':observacion'] = $filtros['observacion'];
        }

        $whereSql = implode(" AND ", $where);
        $limitSql = "LIMIT " . (int) $offset . ", " . (int) $limit;

        $qry = "SELECT c.*, e.emp_nombre, cc.cc_descripcion, cat.categoria as cat_descripcion
                FROM compras_enc c
                INNER JOIN empresa e ON c.id_empresa = e.id_empresa
                INNER JOIN cecosto cc ON c.id_cc = cc.id_cc
                INNER JOIN cat_gerencias cat ON c.id_categoria = cat.id
                INNER JOIN sol_compra_estado traza ON c.id = traza.id_sol
                WHERE $whereSql
                ORDER BY c.fecha_crea ASC, c.hora_crea ASC
                $limitSql";

        return $this->findAll($qry, $params);
    }

    public function count_solicitudes_aprobacion_categoria($id_usuario, $filtros)
    {
        $where = ["c.estado = 31", "traza.orden = 31", "traza.resolucion = 'A'", "traza.id_usuario = :idu"];
        $params = [':idu' => $id_usuario];

        if (!empty($filtros['mes']) && !empty($filtros['anio'])) {
            $where[] = "MONTH(c.fecha_crea) = :mes AND YEAR(c.fecha_crea) = :anio";
            $params[':mes'] = $filtros['mes'];
            $params[':anio'] = $filtros['anio'];
        }
        if (!empty($filtros['id_empresa'])) {
            $where[] = "c.id_empresa = :id_empresa";
            $params[':id_empresa'] = $filtros['id_empresa'];
        }
        if (!empty($filtros['id_cc'])) {
            $where[] = "c.id_cc = :id_cc";
            $params[':id_cc'] = $filtros['id_cc'];
        }
        if (!empty($filtros['id_categoria'])) {
            $where[] = "c.id_categoria = :id_categoria";
            $params[':id_categoria'] = $filtros['id_categoria'];
        }
        if (!empty($filtros['observacion'])) {
            $where[] = "c.observacion_crea LIKE concat('%',:observacion,'%')";
            $params[':observacion'] = $filtros['observacion'];
        }

        $whereSql = implode(" AND ", $where);
        $qry = "SELECT COUNT(c.id) as total FROM compras_enc c INNER JOIN sol_compra_estado traza ON c.id = traza.id_sol WHERE $whereSql";

        $row = $this->find($qry, $params);
        return $row ? (int) $row->total : 0;
    }
}
?>