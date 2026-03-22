<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__FILE__) . '/../model/model.php';

// Definición segura de variables de sesión
$id_usuario = (int) ($_SESSION['i'] ?? 0);
$usr_usuario = $_SESSION['u'] ?? '';
$usr_name = $_SESSION['n'] ?? '';

if (!$id_usuario || !$usr_usuario) {
    jsonError('Sesión expirada o inválida. Por favor, inicie sesión nuevamente.', 401);
}

// =====================================================
// FUNCIÓN PRINCIPAL PARA CREAR SOLICITUD (El que ya tenías)
// =====================================================
function enviar()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');

    $id_empresa = trim($_POST['empresa'] ?? '');
    $id_cc = trim($_POST['centroCostos'] ?? '');
    $id_categoria = trim($_POST['categoria'] ?? '');
    $observacion = trim($_POST['observacion'] ?? '');
    $productos = $_POST['productos'] ?? [];

    if (empty($id_empresa) || empty($id_cc) || empty($id_categoria) || empty($observacion)) {
        jsonError('Faltan campos obligatorios en el encabezado.');
    }
    if (empty($productos) || !is_array($productos)) {
        jsonError('Debe agregar al menos un producto a la solicitud.');
    }

    $upload_dir = dirname(__FILE__) . '/../uploads/compras/';
    if (!is_dir($upload_dir))
        mkdir($upload_dir, 0777, true);

    $archivos_validados = [];
    $mapa_adjuntos = ['adjunto1' => 1, 'adjunto2' => 2, 'adjunto3' => 3];

    foreach ($mapa_adjuntos as $file_key => $numero_adjunto) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$file_key];
            if ($file['size'] > 5242880)
                jsonError("El archivo $file_key excede 5MB.");
            $archivos_validados[$file_key] = ['tmp_name' => $file['tmp_name'], 'name' => $file['name'], 'numero' => $numero_adjunto];
        }
    }

    $db = new ModelDB();
    $fecha_sistema = date('Y-m-d');
    $hora_sistema = date('H:i:s');

    try {
        $numero_correlativo = $db->get_and_increment_cc_solc($id_empresa, $id_cc);
        $adjuntos_nombres = ['adjunto1' => '', 'adjunto2' => '', 'adjunto3' => ''];
        $adjuntos_originales = ['adjunto1' => '', 'adjunto2' => '', 'adjunto3' => ''];

        foreach ($archivos_validados as $file_key => $file_data) {
            $ext = pathinfo($file_data['name'], PATHINFO_EXTENSION);
            $nombre_limpio = preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\-_]/', '', pathinfo($file_data['name'], PATHINFO_FILENAME));
            $new_filename = $numero_correlativo . ' - ' . trim($nombre_limpio) . ' ' . $file_data['numero'] . '.' . $ext;

            if (move_uploaded_file($file_data['tmp_name'], $upload_dir . $new_filename)) {
                $adjuntos_nombres[$file_key] = $new_filename;
                $adjuntos_originales[$file_key] = $file_data['name'];
            }
        }

        $id_prehsol = $db->create_solicitud_encabezado([
            'id_empresa' => $id_empresa,
            'id_cc' => $id_cc,
            'id_usuario' => $id_usuario,
            'prehsol_numero' => $numero_correlativo,
            'prehsol_fecha' => $fecha_sistema,
            'prehsol_hora' => $hora_sistema,
            'prehsol_usuario' => $usr_usuario,
            'prehsol_estado' => 11,
            'prehsol_numero_sol' => $numero_correlativo,
            'prehsol_obs1' => $observacion,
            'prehsol_coti1' => $adjuntos_nombres['adjunto1'],
            'prehsol_coti2' => $adjuntos_nombres['adjunto2'],
            'prehsol_coti3' => $adjuntos_nombres['adjunto3'],
            'prehsol_coti1_name' => $adjuntos_originales['adjunto1'],
            'prehsol_coti2_name' => $adjuntos_originales['adjunto2'],
            'prehsol_coti3_name' => $adjuntos_originales['adjunto3'],
            'id_categoria' => $id_categoria
        ]);

        foreach ($productos as $item) {
            if (isset($item['cantidad']) && (int) $item['cantidad'] > 0) {
                $db->create_solicitud_detalle([
                    'id_prehsol' => $id_prehsol,
                    'id_usuario' => $id_usuario,
                    'prod_codigo' => trim($item['prod_codigo'] ?? ''),
                    'predsol_cantidad' => (int) $item['cantidad'],
                    'predsol_fecha' => $fecha_sistema,
                    'predsol_hora' => $hora_sistema,
                    'predsol_usuario' => $usr_usuario,
                    'predsol_descripcion' => trim($item['descripcion'] ?? ''),
                    'id_empresa' => $id_empresa,
                    'id_cc' => $id_cc,
                    'predsol_estado' => 11,
                    'id_producto' => !empty($item['id_producto']) ? (int) $item['id_producto'] : 0
                ]);
            }
        }

        $db->create_trazabilidad_compras($id_prehsol, $id_empresa, $id_cc, $id_categoria, $id_usuario, $usr_usuario, $usr_name);

        echo json_encode(['exito' => true, 'msj' => 'Solicitud procesada con éxito.', 'numero_correlativo' => $numero_correlativo]);
    } catch (Exception $e) {
        jsonError("Excepción al guardar datos: " . $e->getMessage(), 500);
    }
}

// =====================================================
// FUNCIÓN PARA CONSULTAR SOLICITUDES
// =====================================================
function consulta_cotizacion()
{
    requireMethod('POST');
    $db = new ModelDB();
    $filtros = [
        'mes' => trim($_POST['mes'] ?? date('m')),
        'anio' => trim($_POST['anio'] ?? date('Y')),
        'id_empresa' => trim($_POST['empresa'] ?? ''),
        'id_cc' => trim($_POST['centroCostos'] ?? ''),
        'id_categoria' => trim($_POST['categoria'] ?? ''),
        'observacion' => trim($_POST['observacion'] ?? '')
    ];
    $limit = 8;
    $pagina_actual = max(1, (int) ($_POST['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $limit;

    try {
        $data = $db->get_solicitudes_cotizacion($filtros, $offset, $limit);
        $total_registros = $db->count_solicitudes_cotizacion($filtros);
        echo json_encode([
            'exito' => true,
            'data' => empty($data) ? [] : $data,
            'paginacion' => ['actual' => $pagina_actual, 'total_paginas' => ceil($total_registros / $limit), 'total_registros' => $total_registros]
        ]);
    } catch (Exception $e) {
        jsonError("Error en BD: " . $e->getMessage(), 500);
    }
}

// =====================================================
// GUARDADO INDIVIDUAL DE ÍTEM (ANALISTA)
// =====================================================
function guardar_item_cotizacion()
{
    global $usr_usuario; // Capturamos el usuario en sesión
    requireMethod('POST');

    $id_prehsol = (int) ($_POST['id_prehsol'] ?? 0);
    $id_predsol = (int) ($_POST['id_predsol'] ?? 0);
    $prov_cod = (int) ($_POST['prov_cod'] ?? 0);
    $precio = (float) ($_POST['precio'] ?? 0);
    $cantidad = (float) ($_POST['cantidad'] ?? 0);
    $observacion = trim($_POST['observacion'] ?? '');

    if (!$id_predsol || !$prov_cod || $precio <= 0) {
        jsonError("Faltan datos o el precio es inválido.");
    }

    $total = $cantidad * $precio;
    $fecha_sistema = date('Y-m-d');
    $hora_sistema = date('H:i:s');

    $db = new ModelDB();

    // 1. Actualizamos el detalle inyectando fecha, hora y usuario
    $db->update_item_cotizacion($id_predsol, $prov_cod, $precio, $total, $observacion, $fecha_sistema, $hora_sistema, $usr_usuario);

    // 2. Actualizamos la auditoría en el encabezado
    if ($id_prehsol > 0 && !empty($usr_usuario)) {
        $db->update_revision_cotizacion($id_prehsol, $usr_usuario, $fecha_sistema . ' ' . $hora_sistema);
    }

    // 🏗️ ACTUALIZAR AUTORIZADORES SEGÚN EL TOTAL (Reglas de negocio)
    $ds = $db->get_solicitud_detalle($id_prehsol);
    $total_monto = 0;
    foreach ($ds as $d) {
        $total_monto += (float) $d->predsol_total;
    }

    $id_categoria = $db->get_categoria_by_solicitud($id_prehsol);

    // 1. Actualiza trazabilidad Categoría
    $autorizador = $db->get_autorizador_categoria($id_categoria, $total_monto);
    if ($autorizador) {
        $db->update_trazabilidad_autorizador($id_prehsol, $autorizador);
    }

    // 2. Actualiza trazabilidad >= $5K y activa/desactiva según monto
    $autorizador_5k = $db->get_autorizador_5k($id_categoria);
    if ($autorizador_5k) {
        // Guardamos el usuario designado
        $db->update_trazabilidad_autorizador_5k($id_prehsol, $autorizador_5k);

        // 🏗️ REGLA: Si el monto es >= 5000, encendemos el paso (1), si no, lo apagamos (0)
        $is_active_5k = ($total_monto >= 5000) ? 1 : 0;
        $db->toggle_trazabilidad_5k($id_prehsol, $is_active_5k);
    } else {
        // Si la categoría no requiere aprobador de 5K, nos aseguramos que el paso nazca apagado
        $db->toggle_trazabilidad_5k($id_prehsol, 0);
    }

    echo json_encode(['exito' => true, 'msj' => 'Ítem actualizado correctamente.']);
}

// =====================================================
// GUARDADO FINAL DE LA COTIZACIÓN (ENVIAR APROBACIÓN)
// =====================================================
function guardar_cotizacion()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');

    $id_prehsol = (int) ($_POST['id_prehsol'] ?? 0);
    $moneda = trim($_POST['moneda'] ?? '$');
    $obs_analista = trim($_POST['obs_analista'] ?? '');
    $items = $_POST['item'] ?? [];

    if (!$id_prehsol || empty($obs_analista) || empty($items)) {
        jsonError('Faltan datos obligatorios para procesar la cotización.');
    }

    // Procesamiento del Cuadro Comparativo
    $adj_comp_path = '';
    $adj_comp_name = ''; // 🏗️ Variable para el nombre original

    if (isset($_FILES['adj_comp']) && $_FILES['adj_comp']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['adj_comp'];
        $upload_dir = dirname(__FILE__) . '/../uploads/compras/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $adj_comp_name = $file['name']; // 🏗️ Capturamos el nombre original
        $adj_comp_path = $id_prehsol . '_cuadro_comp_' . time() . '.' . $ext;

        move_uploaded_file($file['tmp_name'], $upload_dir . $adj_comp_path);
    }

    $db = new ModelDB();
    $total_monto = 0;

    try {
        // Obtenemos las cantidades originales para calcular el subtotal sin depender del JS
        $ds = $db->get_solicitud_detalle($id_prehsol);
        $cantidades = [];
        foreach ($ds as $d)
            $cantidades[$d->id_predsol] = (float) $d->predsol_cantidad;

        // 🏗️ Capturamos fecha y hora del sistema para el guardado masivo
        $fecha_sistema = date('Y-m-d');
        $hora_sistema = date('H:i:s');

        foreach ($items as $item) {
            $id_predsol = (int) $item['id_predsol'];
            $precio = (float) $item['precio'];
            $prov_cod = (int) trim($item['prov_cod']);
            $observacion = trim($item['observacion'] ?? '');

            $cantidad = $cantidades[$id_predsol] ?? 1;
            $subtotal = $cantidad * $precio;
            $total_monto += $subtotal;

            // 🏗️ Pasamos los 3 nuevos parámetros de auditoría
            $db->update_item_cotizacion($id_predsol, $prov_cod, $precio, $subtotal, $observacion, $fecha_sistema, $hora_sistema, $usr_usuario);
        }

        // Actualizamos la cabecera enviando AMBOS nombres del archivo
        $db->update_cotizacion_cabecera($id_prehsol, [
            'obs_analista' => $obs_analista,
            'total_monto' => $total_monto,
            'moneda' => $moneda,
            'adj_comp' => $adj_comp_path,
            'adj_comp_name' => $adj_comp_name // Enviamos al modelo
        ]);

        // 5. Sellamos la Trazabilidad actual (Orden 11) como Completada ('C')
        $qryTraza = "UPDATE sol_compra_estado 
                     SET descripcion = 'Completado', 
                         resolucion = 'C',
                         fecha = :f, hora = :h, id_usuario = :idu, 
                         usuario = :u, nom_usuario = :nu 
                     WHERE id_sol = :id AND orden = 11";
        $db->query($qryTraza, [
            ':f' => date('Y-m-d'),
            ':h' => date('H:i:s'),
            ':idu' => $id_usuario,
            ':u' => $usr_usuario,
            ':nu' => $usr_name ?: 'Analista',
            ':id' => $id_prehsol
        ]);

        // 6. Pasamos el siguiente paso (Autorizador Cco, orden 21) a En Proceso ('A')
        $qryTrazaNext = "UPDATE sol_compra_estado 
                         SET descripcion = 'En proceso', resolucion = 'A' 
                         WHERE id_sol = :id AND orden = 21";
        $db->query($qryTrazaNext, [':id' => $id_prehsol]);

        // 🏗️ ACTUALIZAR AUTORIZADORES SEGÚN EL TOTAL (Reglas de negocio)
        $ds = $db->get_solicitud_detalle($id_prehsol);
        $total_monto = 0;
        foreach ($ds as $d) {
            $total_monto += (float) $d->predsol_total;
        }

        $id_categoria = $db->get_categoria_by_solicitud($id_prehsol);

        // 1. Actualiza trazabilidad Categoría
        $autorizador = $db->get_autorizador_categoria($id_categoria, $total_monto);
        if ($autorizador) {
            $db->update_trazabilidad_autorizador($id_prehsol, $autorizador);
        }

        // 2. Actualiza trazabilidad >= $5K y activa/desactiva según monto
        $autorizador_5k = $db->get_autorizador_5k($id_categoria);
        if ($autorizador_5k) {
            // Guardamos el usuario designado
            $db->update_trazabilidad_autorizador_5k($id_prehsol, $autorizador_5k);

            // 🏗️ REGLA: Si el monto es >= 5000, encendemos el paso (1), si no, lo apagamos (0)
            $is_active_5k = ($total_monto >= 5000) ? 1 : 0;
            $db->toggle_trazabilidad_5k($id_prehsol, $is_active_5k);
        } else {
            // Si la categoría no requiere aprobador de 5K, nos aseguramos que el paso nazca apagado
            $db->toggle_trazabilidad_5k($id_prehsol, 0);
        }

        $db->query("COMMIT");
        echo json_encode(['exito' => true, 'msj' => 'Cotización enviada a aprobación correctamente.']);

    } catch (Exception $e) {
        jsonError("Error en Base de Datos: " . $e->getMessage(), 500);
    }
}


// =====================================================
// SERVICIO AJAX: OBTENER AUTORIZADOR EN TIEMPO REAL
// =====================================================
function obtener_autorizador()
{
    requireMethod('POST');
    $id_categoria = (int) ($_POST['id_categoria'] ?? 0);
    $total = (float) ($_POST['total'] ?? 0);

    if (!$id_categoria) {
        echo json_encode(['exito' => false, 'msj' => 'Categoría no válida']);
        return;
    }

    $db = new ModelDB();
    $autorizador = $db->get_autorizador_categoria($id_categoria, $total);
    $autorizador_5k = $db->get_autorizador_5k($id_categoria); // 🏗️ NUEVO: Obtiene el de 5K

    echo json_encode([
        'exito' => true,
        'autorizador' => $autorizador,
        'autorizador_5k' => $autorizador_5k // 🏗️ NUEVO: Se envía al frontend
    ]);
}

// =====================================================
// CONSULTA DE SOLICITUDES PARA AUTORIZADOR CCO
// =====================================================
function consulta_aprobacion_cc()
{
    global $id_usuario; // Extraído de la sesión
    requireMethod('POST');
    $db = new ModelDB();

    $filtros = [
        'mes' => trim($_POST['mes'] ?? date('m')),
        'anio' => trim($_POST['anio'] ?? date('Y')),
        'id_empresa' => trim($_POST['empresa'] ?? ''),
        'id_cc' => trim($_POST['centroCostos'] ?? ''),
        'observacion' => trim($_POST['observacion'] ?? '') // 🏗️ NUEVO: Captura el texto a buscar
    ];

    $limit = 8;
    $pagina_actual = max(1, (int) ($_POST['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $limit;

    try {
        $data = $db->get_solicitudes_aprobacion_cc($id_usuario, $filtros, $offset, $limit);
        $total_registros = $db->count_solicitudes_aprobacion_cc($id_usuario, $filtros);

        echo json_encode([
            'exito' => true,
            'data' => empty($data) ? [] : $data,
            'paginacion' => [
                'actual' => $pagina_actual,
                'total_paginas' => ceil($total_registros / $limit),
                'total_registros' => $total_registros
            ]
        ]);
    } catch (Exception $e) {
        jsonError("Error en BD: " . $e->getMessage(), 500);
    }
}

// =====================================================
// FUNCIONES EXCLUSIVAS: AUTORIZADOR DE CENTRO DE COSTOS
// =====================================================

// Función auxiliar privada para recalcular trazabilidad dinámica
function _recalcular_trazabilidad_dinamica($id_prehsol, $db)
{
    $ds = $db->get_solicitud_detalle($id_prehsol);
    $total_monto = 0;
    foreach ($ds as $d) {
        $total_monto += (float) $d->predsol_total;
    }

    $db->update_gran_total_cabecera($id_prehsol, $total_monto);
    $id_categoria = $db->get_categoria_by_solicitud($id_prehsol);

    // Categoría
    $autorizador = $db->get_autorizador_categoria($id_categoria, $total_monto);
    if ($autorizador)
        $db->update_trazabilidad_autorizador($id_prehsol, $autorizador);

    // Regla $5K
    $autorizador_5k = $db->get_autorizador_5k($id_categoria);
    if ($autorizador_5k) {
        $db->update_trazabilidad_autorizador_5k($id_prehsol, $autorizador_5k);
        $db->toggle_trazabilidad_5k($id_prehsol, ($total_monto >= 5000 ? 1 : 0));
    } else {
        $db->toggle_trazabilidad_5k($id_prehsol, 0);
    }
}

function actualizar_cantidad_item()
{
    requireMethod('POST');
    $id_prehsol = (int) ($_POST['id_prehsol'] ?? 0);
    $id_predsol = (int) ($_POST['id_predsol'] ?? 0);
    $cantidad = (int) ($_POST['cantidad'] ?? 0);
    $precio = (float) ($_POST['precio'] ?? 0);

    if (!$id_predsol || $cantidad <= 0)
        jsonError("Datos inválidos.");

    $db = new ModelDB();
    $total_fila = $cantidad * $precio;

    $db->update_cantidad_item($id_predsol, $cantidad, $total_fila);
    _recalcular_trazabilidad_dinamica($id_prehsol, $db);

    echo json_encode(['exito' => true, 'msj' => 'Cantidad actualizada y recalculada.']);
}

function eliminar_item_cotizacion()
{
    requireMethod('POST');
    $id_prehsol = (int) ($_POST['id_prehsol'] ?? 0);
    $id_predsol = (int) ($_POST['id_predsol'] ?? 0);

    if (!$id_predsol)
        jsonError("ID de ítem no válido.");

    $db = new ModelDB();
    $db->delete_item_cotizacion($id_predsol);
    _recalcular_trazabilidad_dinamica($id_prehsol, $db);

    echo json_encode(['exito' => true, 'msj' => 'Ítem eliminado correctamente.']);
}

function aprobar_cotizacion_cc()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');

    $id_prehsol = (int) ($_POST['id_prehsol'] ?? 0);
    $observacion = trim($_POST['observacion_aprobador'] ?? '');

    if (!$id_prehsol || empty($observacion))
        jsonError('Debe ingresar una observación para aprobar.');

    $db = new ModelDB();
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');

    // 1. 🏗️ RECALCULO MAESTRO: Blindamos la BD asegurando que los "active" estén correctos
    _recalcular_trazabilidad_dinamica($id_prehsol, $db);

    // 2. Cierra el paso actual del Autorizador Cco. (Orden 21)
    $db->query("UPDATE sol_compra_estado SET descripcion = 'Aprobado', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu WHERE id_sol = :id AND orden = 21", [
        ':f' => $fecha_actual,
        ':h' => $hora_actual,
        ':idu' => $id_usuario,
        ':u' => $usr_usuario,
        ':nu' => $usr_name,
        ':id' => $id_prehsol
    ]);

    $campos_auditoria = [
        'prehsol_aprobacion' => $observacion,
        'prehsol_aprobacion_usuario' => $usr_usuario
    ];

    $paso_evaluar = 21;
    $estado_final = 31; // Valor por defecto

    // 3. 🏗️ BUCLE DE CASCADA INTELIGENTE
    // Sube la escalera evaluando el siguiente paso "encendido"
    while (true) {
        $next_step = $db->get_siguiente_paso_activo($id_prehsol, $paso_evaluar);

        if (!$next_step)
            break; // Fin de la trazabilidad

        $estado_final = $next_step->orden;

        // ¿El siguiente paso es del MISMO USUARIO y aún no llegamos a Orden de Compra (51)?
        if ($next_step->id_usuario == $id_usuario && $estado_final < 51) {

            // Auto-aprobar este paso
            $db->query("UPDATE sol_compra_estado SET descripcion = 'Aprobado (Auto)', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu WHERE id_sol = :id AND orden = :o", [
                ':f' => $fecha_actual,
                ':h' => $hora_actual,
                ':idu' => $id_usuario,
                ':u' => $usr_usuario,
                ':nu' => $usr_name,
                ':id' => $id_prehsol,
                ':o' => $estado_final
            ]);

            // Guardar auditoría correspondiente
            if ($estado_final == 31) {
                $campos_auditoria['prehsol_aprobacion_categoria'] = 'Aprobado auto. por Autorizador CCo: ' . $observacion;
                $campos_auditoria['prehsol_aprobacion_categoria_usuario'] = $usr_usuario;
            } else if ($estado_final == 41) {
                $campos_auditoria['prehsol_aprobador_5k_obs'] = 'Aprobado auto. por Autorizador CCo: ' . $observacion;
                $campos_auditoria['prehsol_aprobador_5k_usuario'] = $usr_usuario;
            }

            // Avanzamos el puntero para evaluar el siguiente escalón
            $paso_evaluar = $estado_final;

        } else {
            // Le toca a otra persona o llegamos a la Orden de Compra. Se enciende y se rompe el bucle.
            $db->query("UPDATE sol_compra_estado SET descripcion = 'En proceso', resolucion = 'A' WHERE id_sol = :id AND orden = :o", [
                ':id' => $id_prehsol,
                ':o' => $estado_final
            ]);
            break;
        }
    }

    // 4. Actualizar tabla principal
    $db->update_estado_y_aprobacion($id_prehsol, $estado_final, $campos_auditoria);

    echo json_encode(['exito' => true, 'msj' => 'Cotización aprobada y enrutada correctamente.']);
}

// =====================================================
// CONSULTA DE SOLICITUDES PENDIENTES DE OC (ANALISTA)
// =====================================================
function consulta_pendiente_oc()
{
    requireMethod('POST');
    $db = new ModelDB();

    $filtros = [
        'mes' => trim($_POST['mes'] ?? date('m')),
        'anio' => trim($_POST['anio'] ?? date('Y')),
        'id_empresa' => trim($_POST['empresa'] ?? ''),
        'id_cc' => trim($_POST['centroCostos'] ?? ''),
        'observacion' => trim($_POST['observacion'] ?? '')
    ];

    $limit = 8;
    $pagina_actual = max(1, (int) ($_POST['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $limit;

    try {
        $data = $db->get_solicitudes_pendientes_oc($filtros, $offset, $limit);
        $total_registros = $db->count_solicitudes_pendientes_oc($filtros);

        echo json_encode([
            'exito' => true,
            'data' => empty($data) ? [] : $data,
            'paginacion' => [
                'actual' => $pagina_actual,
                'total_paginas' => ceil($total_registros / $limit),
                'total_registros' => $total_registros
            ]
        ]);
    } catch (Exception $e) {
        jsonError("Error en BD: " . $e->getMessage(), 500);
    }
}

// =====================================================
// PROCESAR GENERACIÓN DE OC (AGRUPACIÓN POR PROVEEDOR)
// =====================================================
function procesar_generacion_oc()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');

    $id_prehsol = (int) ($_POST['id_prehsol'] ?? 0);
    $observacion_oc = trim($_POST['observacion_oc'] ?? '');

    if (!$id_prehsol)
        jsonError('ID de solicitud inválido.');

    $db = new ModelDB();
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');

    try {
        // 1. Iniciamos la transacción para proteger la integridad de los correlativos
        $db->query("START TRANSACTION");

        // 2. Obtenemos el ID de la empresa a la que pertenece la solicitud
        $cabecera = $db->get_solicitud_cabecera($id_prehsol);
        if (!$cabecera)
            throw new Exception("No se encontró la cabecera de la solicitud.");
        $id_empresa = $cabecera->id_empresa;

        // 3. Obtenemos todo el detalle y lo AGRUPAMOS POR PROVEEDOR
        $ds = $db->get_solicitud_detalle($id_prehsol);
        $items_por_proveedor = [];

        foreach ($ds as $item) {
            $id_prov = $item->id_proveedor;
            if (empty($id_prov)) {
                throw new Exception("El producto '{$item->prod_codigo}' no tiene proveedor asignado. No se puede generar OC.");
            }
            if (!isset($items_por_proveedor[$id_prov])) {
                $items_por_proveedor[$id_prov] = [];
            }
            $items_por_proveedor[$id_prov][] = $item;
        }

        // 4. Iteramos sobre cada proveedor para generar una OC independiente
        $ocs_generadas = [];
        foreach ($items_por_proveedor as $id_proveedor => $items) {

            $numero_oc_nuevo = str_pad($db->get_and_increment_empresa_oc($id_empresa), 6, "0", STR_PAD_LEFT);
            $ocs_generadas[] = $numero_oc_nuevo;

            // 🏗️ ¡LA MAGIA OCURRE AQUÍ! Nace la trazabilidad hija para esta OC específica
            $db->insert_oc_trazabilidad($numero_oc_nuevo, $id_empresa);

            foreach ($items as $prod) {
                // Asegúrate de que update_predsol_oc actualice predsol_estado_oc = 61
                $db->update_predsol_oc(
                    $prod->id_predsol,
                    $numero_oc_nuevo,
                    $usr_usuario,
                    $fecha_actual,
                    $hora_actual
                );
            }
        }

        // 5. Actualizamos la cabecera (Prehsol)
        $estado_final = 61; // Avanza a: Revisión OC
        $db->update_prehsol_generar_oc($id_prehsol, $usr_usuario, $observacion_oc, $estado_final);

        // 6. Trazabilidad: Cerramos el paso 51 (Orden de Compra)
        $db->query("UPDATE sol_compra_estado SET descripcion = 'Completado', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu WHERE id_sol = :id AND orden = 51", [
            ':f' => $fecha_actual,
            ':h' => $hora_actual,
            ':idu' => $id_usuario,
            ':u' => $usr_usuario,
            ':nu' => $usr_name,
            ':id' => $id_prehsol
        ]);

        // 7. Trazabilidad: Encendemos el paso 61 (Revisión OC Jefe Compras)
        $db->query("UPDATE sol_compra_estado SET descripcion = 'En proceso', resolucion = 'A' WHERE id_sol = :id AND orden = 61", [
            ':id' => $id_prehsol
        ]);

        // Confirmamos y guardamos todo de golpe
        $db->query("COMMIT");

        $texto_ocs = implode(", ", $ocs_generadas);
        echo json_encode([
            'exito' => true,
            'msj' => "Órdenes de Compra generadas correctamente: " . $texto_ocs
        ]);

    } catch (Exception $e) {
        $db->query("ROLLBACK");
        jsonError("Error generando OC: " . $e->getMessage(), 500);
    }
}

// =====================================================
// CONSULTA DE ÓRDENES DE COMPRA GENERADAS
// =====================================================
function consulta_oc()
{
    requireMethod('POST');
    $db = new ModelDB();

    $filtros = [
        'mes' => trim($_POST['mes'] ?? date('m')),
        'anio' => trim($_POST['anio'] ?? date('Y')),
        'id_empresa' => trim($_POST['empresa'] ?? ''),
        'search' => trim($_POST['search'] ?? '') // Búsqueda por N° OC o Proveedor
    ];

    $limit = 8;
    $pagina_actual = max(1, (int) ($_POST['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $limit;

    try {
        $data = $db->get_historial_oc($filtros, $offset, $limit);
        $total_registros = $db->count_historial_oc($filtros);

        echo json_encode([
            'exito' => true,
            'data' => empty($data) ? [] : $data,
            'paginacion' => [
                'actual' => $pagina_actual,
                'total_paginas' => ceil($total_registros / $limit),
                'total_registros' => $total_registros
            ]
        ]);
    } catch (Exception $e) {
        jsonError("Error en BD: " . $e->getMessage(), 500);
    }
}

// =====================================================
// UTILIDADES
// =====================================================
function requireMethod($method)
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method ?? ''))
        jsonError('Método no permitido.', 405);
}

function jsonError($message, $code = 400, $errors = [])
{
    http_response_code($code);
    $out = ['exito' => false, 'msj' => $message];
    if (!empty($errors))
        $out['errores'] = $errors;
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

function aprobar_oc()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');

    $id_prehsol = (int) ($_POST['id_prehsol'] ?? 0);
    $numero_oc = trim($_POST['numero_oc'] ?? '');
    $observacion = trim($_POST['observacion_jefe'] ?? '');

    if (!$id_prehsol || empty($numero_oc) || empty($observacion)) {
        jsonError('Datos incompletos. Asegúrese de ingresar la observación.');
    }

    $db = new ModelDB();
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');

    try {
        $db->query("START TRANSACTION");

        // 1. Extraemos id_empresa para ubicar la trazabilidad correcta
        $cabecera = $db->get_solicitud_cabecera($id_prehsol);
        $id_empresa = $cabecera->id_empresa;

        // 2. Actualizamos SOLO los items de esta OC a Estado 71
        $db->query("UPDATE predsol SET predsol_estado_oc = 71, predsol_obs_jefe = :obs WHERE id_prehsol = :id AND predsol_numero_oc = :oc", [
            ':obs' => $observacion,
            ':id' => $id_prehsol,
            ':oc' => $numero_oc
        ]);

        // 3. 🏗️ TRAZABILIDAD HIJA: Cerramos paso 61 (Revisión OC) solo para esta OC
        $db->query("UPDATE oc_compra_estado SET descripcion = 'Aprobado', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu WHERE numero_oc = :oc AND id_empresa = :emp AND orden = 61", [
            ':f' => $fecha_actual,
            ':h' => $hora_actual,
            ':idu' => $id_usuario,
            ':u' => $usr_usuario,
            ':nu' => $usr_name,
            ':oc' => $numero_oc,
            ':emp' => $id_empresa
        ]);

        // 4. 🏗️ TRAZABILIDAD HIJA: Encendemos paso 71 (OC en Proveedor)
        $db->query("UPDATE oc_compra_estado SET descripcion = 'En proceso', resolucion = 'A' WHERE numero_oc = :oc AND id_empresa = :emp AND orden = 71", [
            ':oc' => $numero_oc,
            ':emp' => $id_empresa
        ]);

        $db->query("COMMIT");
        echo json_encode(['exito' => true, 'msj' => "Orden de Compra OC-{$numero_oc} aprobada exitosamente."]);

    } catch (Exception $e) {
        $db->query("ROLLBACK");
        jsonError("Error aprobando OC: " . $e->getMessage(), 500);
    }
}
?>