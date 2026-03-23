<?php
// ==========================================================================================
// CONTROLADOR MAESTRO DE COMPRAS (SICYS)
// Maneja el ciclo de vida completo: Creación -> Cotización -> Aprobaciones -> OC -> Revisión
// Arquitectura: MVC Puro | Respuestas: JSON Estricto | Motor de BD: Custom PDO (ModelDB)
// ==========================================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forzamos la salida como JSON para evitar errores en el parseo del Frontend (Fetch API)
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__FILE__) . '/../model/model.php';

// -------------------------------------------------------------------------
// MIDDLEWARE DE SEGURIDAD BÁSICO
// Extraemos variables de sesión y validamos que el usuario esté logueado.
// Si no hay sesión, cortamos la ejecución (HTTP 401 Unauthorized).
// -------------------------------------------------------------------------
$id_usuario = (int) ($_SESSION['i'] ?? 0);
$usr_usuario = $_SESSION['u'] ?? '';
$usr_name = $_SESSION['n'] ?? '';

if (!$id_usuario || !$usr_usuario) {
    jsonError('Sesión expirada o inválida. Por favor, inicie sesión nuevamente.', 401);
}

// ==========================================================================================
// FASE 1: CREACIÓN DE SOLICITUD (Rol: Solicitante)
// ==========================================================================================
function enviar()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST'); // Protege el endpoint contra peticiones GET directas

    // 1. Recepción y limpieza de datos (Sanitización básica)
    $id_empresa = trim($_POST['empresa'] ?? '');
    $id_cc = trim($_POST['centroCostos'] ?? '');
    $id_categoria = trim($_POST['categoria'] ?? '');
    $observacion = trim($_POST['observacion'] ?? '');
    $productos = $_POST['productos'] ?? [];

    // 2. Reglas de validación de negocio
    if (empty($id_empresa) || empty($id_cc) || empty($id_categoria) || empty($observacion)) {
        jsonError('Faltan campos obligatorios en el encabezado.');
    }
    if (empty($productos) || !is_array($productos)) {
        jsonError('Debe agregar al menos un producto a la solicitud.');
    }

    // 3. Preparación de File System para adjuntos
    $upload_dir = dirname(__FILE__) . '/../uploads/compras/';
    if (!is_dir($upload_dir))
        mkdir($upload_dir, 0777, true);

    $archivos_validados = [];
    $mapa_adjuntos = ['adjunto1' => 1, 'adjunto2' => 2, 'adjunto3' => 3];

    // Filtramos y validamos peso máximo (5MB) antes de procesar la BD
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
        // 1. Obtener correlativo interno del Centro de Costos
        $numero_correlativo = $db->get_and_increment_cc_solc($id_empresa, $id_cc);

        // 2. Persistencia de Datos (Encabezado) -> Tabla: compras_enc
        // 🏗️ Lo hacemos ANTES de los archivos para obtener el ID Primario
        $id_solicitud = $db->create_solicitud_encabezado([
            'id_empresa' => $id_empresa,
            'id_cc' => $id_cc,
            'id_categoria' => $id_categoria,
            'numero_solicitud' => $numero_correlativo,
            'id_usuario_crea' => $id_usuario,
            'usuario_crea' => $usr_usuario,
            'fecha_crea' => $fecha_sistema,
            'hora_crea' => $hora_sistema,
            'observacion_crea' => $observacion,
            'estado' => 11
        ]);

        // 3. Procesamiento y Guardado de Archivos Adjuntos (Tabla: compras_adjuntos)
        $mapa_adjuntos = ['adjunto1' => 'S1', 'adjunto2' => 'S2', 'adjunto3' => 'S3'];

        // Creamos la carpeta si no existe
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($mapa_adjuntos as $file_key => $tipo_adjunto) {
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$file_key];

                $nombre_original = $file['name'];
                $ext = pathinfo($nombre_original, PATHINFO_EXTENSION);

                // 🏗️ SANITIZACIÓN AGRESIVA: Convertimos cualquier cosa que no sea letra o número en guión bajo
                $nombre_base = pathinfo($nombre_original, PATHINFO_FILENAME);
                $nombre_limpio = preg_replace('/[^a-zA-Z0-9]/', '_', $nombre_base);
                $nombre_limpio = preg_replace('/_+/', '_', $nombre_limpio); // Evita guiones dobles como __

                $new_filename = $id_solicitud . '_' . $tipo_adjunto . '_' . $nombre_limpio . '.' . $ext;
                $destino_final = $upload_dir . $new_filename;

                // 🏗️ EL HACK PARA WINDOWS IIS: Usamos copy() en lugar de move_uploaded_file()
                // Al copiar, el archivo nace nuevo y hereda obligatoriamente los permisos de la carpeta uploads/compras
                if (copy($file['tmp_name'], $destino_final)) {
                    // Borramos el temporal basura a mano
                    unlink($file['tmp_name']);

                    // Guardamos en BD
                    $db->create_adjunto($id_solicitud, $tipo_adjunto, $nombre_original, $new_filename);
                }
            }
        }

        // 4. Persistencia de Datos (Detalle) -> Tabla: compras_det
        foreach ($productos as $item) {
            if (isset($item['cantidad']) && (int) $item['cantidad'] > 0) {
                $db->create_solicitud_detalle([
                    'id_enc' => $id_solicitud,
                    'id_empresa' => $id_empresa,
                    'id_cc' => $id_cc,
                    'id_producto' => !empty($item['id_producto']) ? (int) $item['id_producto'] : 0,
                    'codigo_producto' => trim($item['prod_codigo'] ?? ''),
                    'descripcion_producto' => trim($item['descripcion'] ?? ''),
                    'cantidad' => (float) $item['cantidad']
                ]);
            }
        }

        // 5. Iniciamos el ciclo de vida (Event Sourcing / Trazabilidad)
        $db->create_trazabilidad_compras($id_solicitud, $id_empresa, $id_cc, $id_categoria, $id_usuario, $usr_usuario, $usr_name);

        echo json_encode(['exito' => true, 'msj' => 'Solicitud procesada con éxito.', 'numero_correlativo' => $numero_correlativo]);
    } catch (Exception $e) {
        jsonError("Excepción al guardar datos: " . $e->getMessage(), 500);
    }
}

// ==========================================================================================
// FASE 2: COTIZACIÓN (Rol: Analista de Compras)
// ==========================================================================================

// Obtiene la lista de solicitudes pendientes de cotizar (Estado 11)
// =====================================================
// CONSULTA DE SOLICITUDES PARA COTIZAR
// =====================================================

// Actualiza un solo ítem desde la vista de cotización en tiempo real (AJAX)
function guardar_item_cotizacion()
{
    global $usr_usuario;
    requireMethod('POST');

    $id_solicitud = (int) ($_POST['id_prehsol'] ?? 0);
    $id_detalle = (int) ($_POST['id_predsol'] ?? 0);
    $prov_cod = (int) ($_POST['prov_cod'] ?? 0);
    $precio = (float) ($_POST['precio'] ?? 0);
    $observacion = trim($_POST['observacion'] ?? '');

    if (!$id_detalle || !$prov_cod || $precio <= 0) {
        jsonError("Faltan datos o el precio es inválido.");
    }

    $fecha_sistema = date('Y-m-d');
    $hora_sistema = date('H:i:s');
    $db = new ModelDB();

    // 1. Guardamos proveedor y precio. La BD (VIRTUAL COLUMN) recalcula el subtotal sola.
    $db->update_item_cotizacion($id_detalle, $prov_cod, $precio, $observacion, $fecha_sistema, $hora_sistema, $_SESSION['i'], $usr_usuario);

    // 2. Auditoría en la cabecera (Dejamos huella de quién está "tocando" la cotización actualmente)
    if ($id_solicitud > 0 && !empty($usr_usuario)) {
        $db->query("UPDATE compras_enc SET id_usuario_cotizando = :id_u, usuario_cotizando = :u, fecha_cotizando = :f, hora_cotizando = :h WHERE id = :id", [
            ':id_u' => $_SESSION['i'],
            ':u' => $usr_usuario,
            ':f' => $fecha_sistema,
            ':h' => $hora_sistema,
            ':id' => $id_solicitud
        ]);
    }

    // 3. Recalculamos dinámicamente los autorizadores en la Trazabilidad (porque el monto cambió)
    $ds = $db->get_solicitud_detalle($id_solicitud);
    $total_monto = 0;
    foreach ($ds as $d) {
        $total_monto += (float) $d->subtotal;
    }

    $id_categoria = $db->get_categoria_by_solicitud($id_solicitud);

    // Actualizamos al Autorizador de Categoría según escala de montos ($200, $1000, etc.)
    $autorizador = $db->get_autorizador_categoria($id_categoria, $total_monto);
    if ($autorizador) {
        $db->update_trazabilidad_autorizador($id_solicitud, $autorizador);
    }

    // Evaluamos el umbral de Dirección (>=$5000)
    $autorizador_5k = $db->get_autorizador_5k($id_categoria);
    if ($autorizador_5k) {
        $db->update_trazabilidad_autorizador_5k($id_solicitud, $autorizador_5k);
        $is_active_5k = ($total_monto >= 5000) ? 1 : 0;
        $db->toggle_trazabilidad_5k($id_solicitud, $is_active_5k); // Enciende o apaga el paso en el UI
    } else {
        $db->toggle_trazabilidad_5k($id_solicitud, 0);
    }

    echo json_encode(['exito' => true, 'msj' => 'Ítem actualizado correctamente.']);
}

// Finaliza la cotización y la envía al Autorizador de Centro de Costos
// Finaliza la cotización y la envía al Autorizador de Centro de Costos
function guardar_cotizacion()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');

    $id_solicitud = (int) ($_POST['id_prehsol'] ?? 0);
    $moneda = trim($_POST['moneda'] ?? '$');
    $obs_analista = trim($_POST['obs_analista'] ?? '');
    $items = $_POST['item'] ?? [];

    if (!$id_solicitud || empty($obs_analista) || empty($items)) {
        jsonError('Faltan datos obligatorios para procesar la cotización.');
    }

    $db = new ModelDB();
    $total_monto = 0;

    try {
        $fecha_sistema = date('Y-m-d');
        $hora_sistema = date('H:i:s');

        // 1. Barremos todos los ítems para asegurar que la info esté en BD
        foreach ($items as $item) {
            $id_detalle = (int) $item['id_predsol'];
            $precio = (float) $item['precio'];
            $prov_cod = (int) trim($item['prov_cod']);
            $observacion = trim($item['observacion'] ?? '');
            $db->update_item_cotizacion($id_detalle, $prov_cod, $precio, $observacion, $fecha_sistema, $hora_sistema, $id_usuario, $usr_usuario);
        }

        // 2. Extraemos el total definitivo calculado por MySQL
        $ds = $db->get_solicitud_detalle($id_solicitud);
        foreach ($ds as $d) {
            $total_monto += (float) $d->subtotal;
        }

        // 3. 🏗️ PROCESAMIENTO DEL CUADRO COMPARATIVO (ADJUNTO 'CC')
        $upload_dir = dirname(__FILE__) . '/../uploads/compras/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (isset($_FILES['adj_comp']) && $_FILES['adj_comp']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['adj_comp'];
            $nombre_original = $file['name'];
            $ext = pathinfo($nombre_original, PATHINFO_EXTENSION);

            // Sanitización estricta para IIS
            $nombre_base = pathinfo($nombre_original, PATHINFO_FILENAME);
            $nombre_limpio = preg_replace('/[^a-zA-Z0-9]/', '_', $nombre_base);
            $nombre_limpio = preg_replace('/_+/', '_', $nombre_limpio);

            $new_filename = $id_solicitud . '_CC_' . $nombre_limpio . '.' . $ext;
            $destino_final = $upload_dir . $new_filename;

            // Hack Windows IIS
            if (copy($file['tmp_name'], $destino_final)) {
                unlink($file['tmp_name']);
                $db->create_adjunto($id_solicitud, 'CC', $nombre_original, $new_filename);
            }
        }

        // 4. Estampamos la cabecera (Pasa a Estado 21 -> Autorizador Cco)
        $db->update_cotizacion_cabecera($id_solicitud, [
            'obs_analista' => $obs_analista,
            'total_monto' => $total_monto,
            'moneda' => $moneda,
            'id_usuario' => $id_usuario,
            'usuario' => $usr_usuario
        ]);

        // 5. Marcamos el paso 11 como Completado en la Bitácora de Trazabilidad
        $qryTraza = "UPDATE sol_compra_estado 
                     SET descripcion = 'Completado', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu 
                     WHERE id_sol = :id AND orden = 11";
        $db->query($qryTraza, [':f' => $fecha_sistema, ':h' => $hora_sistema, ':idu' => $id_usuario, ':u' => $usr_usuario, ':nu' => $usr_name ?: 'Analista', ':id' => $id_solicitud]);

        // Abrimos el paso 21 para el Aprobador
        $db->query("UPDATE sol_compra_estado SET descripcion = 'En proceso', resolucion = 'A' WHERE id_sol = :id AND orden = 21", [':id' => $id_solicitud]);

        // 6. (Seguridad) Reevaluación final de autorizadores basada en el Gran Total
        $id_categoria = $db->get_categoria_by_solicitud($id_solicitud);
        $autorizador = $db->get_autorizador_categoria($id_categoria, $total_monto);
        if ($autorizador)
            $db->update_trazabilidad_autorizador($id_solicitud, $autorizador);

        $autorizador_5k = $db->get_autorizador_5k($id_categoria);
        if ($autorizador_5k) {
            $db->update_trazabilidad_autorizador_5k($id_solicitud, $autorizador_5k);
            $db->toggle_trazabilidad_5k($id_solicitud, ($total_monto >= 5000) ? 1 : 0);
        } else {
            $db->toggle_trazabilidad_5k($id_solicitud, 0);
        }

        echo json_encode(['exito' => true, 'msj' => 'Cotización enviada a aprobación correctamente.']);

    } catch (Exception $e) {
        jsonError("Error en Base de Datos: " . $e->getMessage(), 500);
    }
}

// Endpoint consumido por JS para pre-visualizar en UI quién va a autorizar la orden según el monto tipeado
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
    $autorizador_5k = $db->get_autorizador_5k($id_categoria);

    echo json_encode(['exito' => true, 'autorizador' => $autorizador, 'autorizador_5k' => $autorizador_5k]);
}

// ==========================================================================================
// FASE 3: APROBACIONES GERENCIALES (Rol: Aprobador CCo / Categoría / Director)
// ==========================================================================================

// Bandeja del Aprobador de CCo (Cruza los permisos del usuario con acc_emp_cc)
// =====================================================
// CONSULTA DE SOLICITUDES PARA AUTORIZADOR CCO
// =====================================================

// Helper Interno: Recalcula rutas si el Aprobador modifica cantidades
function _recalcular_trazabilidad_dinamica($id_solicitud, $db)
{
    $ds = $db->get_solicitud_detalle($id_solicitud);
    $total_monto = 0;
    foreach ($ds as $d) {
        $total_monto += (float) $d->subtotal;
    }

    $db->update_gran_total_cabecera($id_solicitud, $total_monto);
    $id_categoria = $db->get_categoria_by_solicitud($id_solicitud);

    $autorizador = $db->get_autorizador_categoria($id_categoria, $total_monto);
    if ($autorizador)
        $db->update_trazabilidad_autorizador($id_solicitud, $autorizador);

    $autorizador_5k = $db->get_autorizador_5k($id_categoria);
    if ($autorizador_5k) {
        $db->update_trazabilidad_autorizador_5k($id_solicitud, $autorizador_5k);
        $db->toggle_trazabilidad_5k($id_solicitud, ($total_monto >= 5000 ? 1 : 0));
    } else {
        $db->toggle_trazabilidad_5k($id_solicitud, 0);
    }
}

function actualizar_cantidad_item()
{
    requireMethod('POST');
    $id_solicitud = (int) ($_POST['id_prehsol'] ?? 0);
    $id_detalle = (int) ($_POST['id_predsol'] ?? 0);
    $cantidad = (float) ($_POST['cantidad'] ?? 0);

    if (!$id_detalle || $cantidad <= 0)
        jsonError("Datos inválidos.");

    $db = new ModelDB();
    $db->update_cantidad_item($id_detalle, $cantidad);
    _recalcular_trazabilidad_dinamica($id_solicitud, $db); // Recalcula si aplica a $5K tras modificar

    echo json_encode(['exito' => true, 'msj' => 'Cantidad actualizada y recalculada.']);
}

function eliminar_item_cotizacion()
{
    requireMethod('POST');
    $id_solicitud = (int) ($_POST['id_prehsol'] ?? 0);
    $id_detalle = (int) ($_POST['id_predsol'] ?? 0);

    if (!$id_detalle)
        jsonError("ID de ítem no válido.");

    $db = new ModelDB();
    $db->delete_item_cotizacion($id_detalle);
    _recalcular_trazabilidad_dinamica($id_solicitud, $db);

    echo json_encode(['exito' => true, 'msj' => 'Ítem eliminado correctamente.']);
}

// Acción de Aprobación. Contiene motor inteligente de Auto-Aprobación
function aprobar_cotizacion_cc()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');

    $id_solicitud = (int) ($_POST['id_prehsol'] ?? 0);
    $observacion = trim($_POST['observacion_aprobador'] ?? '');

    if (!$id_solicitud || empty($observacion))
        jsonError('Debe ingresar una observación para aprobar.');

    $db = new ModelDB();
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');

    _recalcular_trazabilidad_dinamica($id_solicitud, $db);

    // 1. Cerramos el paso 21 (Aprobación CCo) en Trazabilidad
    $db->query("UPDATE sol_compra_estado SET descripcion = 'Aprobado', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu WHERE id_sol = :id AND orden = 21", [
        ':f' => $fecha_actual,
        ':h' => $hora_actual,
        ':idu' => $id_usuario,
        ':u' => $usr_usuario,
        ':nu' => $usr_name,
        ':id' => $id_solicitud
    ]);

    // Campos a auditar en la tabla transaccional compras_enc
    $campos_auditoria = [
        'observacion_cco' => $observacion,
        'usuario_aprobador_cco' => $usr_usuario,
        'id_usuario_aprobador_cco' => $id_usuario,
        'fecha_aprobador_cco' => $fecha_actual,
        'hora_aprobador_cco' => $hora_actual
    ];

    $paso_evaluar = 21;
    $estado_final = 31; // Asumimos por defecto que pasará al Autorizador de Categoría

    // 2. MOTOR DE AUTO-APROBACIÓN (Escalado Jerárquico)
    // Evalúa si la persona que está aprobando el CC es la MISMA persona que debe aprobar
    // las siguientes etapas (Categoría o $5K). Si es el mismo usuario, el sistema avanza automáticamente
    // los pasos para ahorrarle clics innecesarios.
    while (true) {
        $next_step = $db->get_siguiente_paso_activo($id_solicitud, $paso_evaluar);

        if (!$next_step)
            break;
        $estado_final = $next_step->orden;

        if ($next_step->id_usuario == $id_usuario && $estado_final < 51) {
            // Es la misma persona. Auto-aprobamos la etapa en la Trazabilidad
            $db->query("UPDATE sol_compra_estado SET descripcion = 'Aprobado (Auto)', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu WHERE id_sol = :id AND orden = :o", [
                ':f' => $fecha_actual,
                ':h' => $hora_actual,
                ':idu' => $id_usuario,
                ':u' => $usr_usuario,
                ':nu' => $usr_name,
                ':id' => $id_solicitud,
                ':o' => $estado_final
            ]);

            // Duplicamos la huella de auditoría para esa etapa auto-saltada
            if ($estado_final == 31) {
                $campos_auditoria['observacion_categoria'] = 'Aprobado auto. por Autorizador CCo: ' . $observacion;
                $campos_auditoria['usuario_aprobador_categoria'] = $usr_usuario;
                $campos_auditoria['id_usuario_aprobador_categoria'] = $id_usuario;
                $campos_auditoria['fecha_aprobador_categoria'] = $fecha_actual;
                $campos_auditoria['hora_aprobador_categoria'] = $hora_actual;
            } else if ($estado_final == 41) {
                $campos_auditoria['observacion_5k'] = 'Aprobado auto. por Autorizador CCo: ' . $observacion;
                $campos_auditoria['usuario_aprobador_5k'] = $usr_usuario;
                $campos_auditoria['id_usuario_aprobador_5k'] = $id_usuario;
                $campos_auditoria['fecha_aprobador_5k'] = $fecha_actual;
                $campos_auditoria['hora_aprobador_5k'] = $hora_actual;
            }
            $paso_evaluar = $estado_final; // Sigue evaluando el bucle

        } else {
            // Diferente persona, detenemos auto-salto y dejamos la tarea a la espera
            $db->query("UPDATE sol_compra_estado SET descripcion = 'En proceso', resolucion = 'A' WHERE id_sol = :id AND orden = :o", [
                ':id' => $id_solicitud,
                ':o' => $estado_final
            ]);
            break;
        }
    }

    // 3. Impactamos el estado final a donde haya llegado la cadena de evaluación
    $db->update_estado_y_aprobacion($id_solicitud, $estado_final, $campos_auditoria);

    echo json_encode(['exito' => true, 'msj' => 'Cotización aprobada y enrutada correctamente.']);
}

// ==========================================================================================
// FASE 4: GENERACIÓN DE ORDEN DE COMPRA (Rol: Analista de Compras)
// ==========================================================================================
// =====================================================
// CONSULTA DE SOLICITUDES PARA COTIZAR
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

        // 🏗️ CORRECCIÓN: Como migramos a compras_enc, el campo ahora es 'id', no 'id_prehsol'
        foreach ($data as $item) {
            $item->adjuntos = $db->get_adjuntos_solicitud($item->id);
        }

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
// CONSULTA DE SOLICITUDES PARA AUTORIZADOR CCO
// =====================================================
function consulta_aprobacion_cc()
{
    global $id_usuario;
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
        $data = $db->get_solicitudes_aprobacion_cc($id_usuario, $filtros, $offset, $limit);

        // 🏗️ CORRECCIÓN: Como migramos a compras_enc, el campo ahora es 'id'
        foreach ($data as $item) {
            $item->adjuntos = $db->get_adjuntos_solicitud($item->id);
        }

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

        // 🏗️ CORRECCIÓN: Como migramos a compras_enc, el campo ahora es 'id'
        foreach ($data as $item) {
            $item->adjuntos = $db->get_adjuntos_solicitud($item->id);
        }

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
// Ejecuta el "Split" (División) de la Solicitud Múltiple a OCs individuales por proveedor
function procesar_generacion_oc()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');

    $id_solicitud = (int) ($_POST['id_prehsol'] ?? 0);
    $observacion_oc = trim($_POST['observacion_oc'] ?? '');

    if (!$id_solicitud)
        jsonError('ID de solicitud inválido.');

    $db = new ModelDB();
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');

    try {
        // Bloque Transaccional: O se generan todas las OC, o no se genera ninguna.
        $db->query("START TRANSACTION");

        $cabecera = $db->get_solicitud_cabecera($id_solicitud);
        if (!$cabecera)
            throw new Exception("No se encontró la cabecera de la solicitud.");
        $id_empresa = $cabecera->id_empresa;

        $ds = $db->get_solicitud_detalle($id_solicitud);
        $items_por_proveedor = [];

        // 1. Agrupamiento en memoria por ID Proveedor
        foreach ($ds as $item) {
            $id_prov = $item->id_proveedor;
            if (empty($id_prov)) {
                throw new Exception("El producto '{$item->codigo_producto}' no tiene proveedor asignado. No se puede generar OC.");
            }
            if (!isset($items_por_proveedor[$id_prov])) {
                $items_por_proveedor[$id_prov] = [];
            }
            $items_por_proveedor[$id_prov][] = $item;
        }

        $ocs_generadas = [];

        // 2. Iteración sobre proveedores (Un proveedor = Una Orden de Compra Nueva)
        foreach ($items_por_proveedor as $id_proveedor => $items) {

            // Extraemos correlativo de la tabla empresa bloqueando lecturas simultáneas (FOR UPDATE)
            $numero_oc_nuevo = str_pad($db->get_and_increment_empresa_oc($id_empresa), 6, "0", STR_PAD_LEFT);
            $ocs_generadas[] = $numero_oc_nuevo;

            // Inicializa la línea de tiempo paralela (Trazabilidad Hija) para esta OC
            $db->insert_oc_trazabilidad($numero_oc_nuevo, $id_empresa);

            // "Amarramos" cada ítem de este proveedor al nuevo número de OC
            foreach ($items as $prod) {
                // Setea el estado_oc a 'GE' (Generada)
                $db->update_detalle_oc($prod->id, $numero_oc_nuevo);
            }
        }

        // 3. Clausura de la Solicitud Padre
        $estado_final = 61; // Todo entra a "Revisión OC" para el Jefe
        $db->update_encabezado_generar_oc($id_solicitud, [
            'estado' => $estado_final,
            'id_usuario' => $id_usuario,
            'usuario' => $usr_usuario,
            'obs' => $observacion_oc
        ]);

        $db->query("UPDATE sol_compra_estado SET descripcion = 'Completado', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu WHERE id_sol = :id AND orden = 51", [
            ':f' => $fecha_actual,
            ':h' => $hora_actual,
            ':idu' => $id_usuario,
            ':u' => $usr_usuario,
            ':nu' => $usr_name,
            ':id' => $id_solicitud
        ]);

        $db->query("UPDATE sol_compra_estado SET descripcion = 'En proceso', resolucion = 'A' WHERE id_sol = :id AND orden = 61", [':id' => $id_solicitud]);

        $db->query("COMMIT");

        $texto_ocs = implode(", ", $ocs_generadas);
        echo json_encode(['exito' => true, 'msj' => "Órdenes de Compra generadas correctamente: " . $texto_ocs]);

    } catch (Exception $e) {
        $db->query("ROLLBACK");
        jsonError("Error generando OC: " . $e->getMessage(), 500);
    }
}

// ==========================================================================================
// FASE 5: REVISIÓN DE OC (Rol: Jefe de Compras)
// ==========================================================================================

function consulta_oc()
{
    requireMethod('POST');
    $db = new ModelDB();

    $filtros = [
        'mes' => trim($_POST['mes'] ?? date('m')),
        'anio' => trim($_POST['anio'] ?? date('Y')),
        'id_empresa' => trim($_POST['empresa'] ?? ''),
        'search' => trim($_POST['search'] ?? '')
    ];

    $limit = 8;
    $pagina_actual = max(1, (int) ($_POST['pagina'] ?? 1));
    $offset = ($pagina_actual - 1) * $limit;

    try {
        // Ejecuta query compleja con GROUP BY numero_oc
        $data = $db->get_historial_oc($filtros, $offset, $limit);
        $total_registros = $db->count_historial_oc($filtros);

        echo json_encode([
            'exito' => true,
            'data' => empty($data) ? [] : $data,
            'paginacion' => ['actual' => $pagina_actual, 'total_paginas' => ceil($total_registros / $limit), 'total_registros' => $total_registros]
        ]);
    } catch (Exception $e) {
        jsonError("Error en BD: " . $e->getMessage(), 500);
    }
}

// Aprobación Individual de Orden de Compra por el Jefe
function aprobar_oc()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');

    $id_solicitud = (int) ($_POST['id_prehsol'] ?? 0);
    $numero_oc = trim($_POST['numero_oc'] ?? '');
    $observacion = trim($_POST['observacion_jefe'] ?? '');

    if (!$id_solicitud || empty($numero_oc) || empty($observacion)) {
        jsonError('Datos incompletos. Asegúrese de ingresar la observación.');
    }

    $db = new ModelDB();
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');

    try {
        $db->query("START TRANSACTION");

        $cabecera = $db->get_solicitud_cabecera($id_solicitud);
        $id_empresa = $cabecera->id_empresa;

        // 1. Actualizamos el estado aislado de ESTA Orden en el Detalle ('AP' = Aprobada)
        $db->query("UPDATE compras_det SET estado_oc = 'AP', obs_revision_oc_jefe = :obs, usr_aprueba_oc_jefe = :usr, fecha_aprobacion_oc = :f WHERE id_enc = :id AND numero_oc = :oc", [
            ':obs' => $observacion,
            ':usr' => $usr_usuario,
            ':f' => $fecha_actual . ' ' . $hora_actual,
            ':id' => $id_solicitud,
            ':oc' => $numero_oc
        ]);

        // 2. Avanzamos Trazabilidad Hija (Paso 61 -> 71)
        $db->query("UPDATE oc_compra_estado SET descripcion = 'Aprobado', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu WHERE numero_oc = :oc AND id_empresa = :emp AND orden = 61", [
            ':f' => $fecha_actual,
            ':h' => $hora_actual,
            ':idu' => $id_usuario,
            ':u' => $usr_usuario,
            ':nu' => $usr_name,
            ':oc' => $numero_oc,
            ':emp' => $id_empresa
        ]);

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
// =====================================================
// LISTAR BANDEJA DE CATEGORÍA
// =====================================================
function listar_aprobacion_categoria()
{
    global $id_usuario;
    requireMethod('POST'); // 🏗️ Cambiamos a POST para recibir los formularios complejos
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
        $datos = $db->get_solicitudes_aprobacion_categoria($id_usuario, $filtros, $offset, $limit);

        if ($datos && is_array($datos)) {
            foreach ($datos as &$d) {
                $d->adjuntos = $db->get_adjuntos_solicitud($d->id);
            }
        } else {
            $datos = [];
        }

        $total_registros = $db->count_solicitudes_aprobacion_categoria($id_usuario, $filtros);

        echo json_encode([
            'exito' => true,
            'data' => $datos,
            'paginacion' => [
                'actual' => $pagina_actual,
                'total_paginas' => ceil($total_registros / $limit),
                'total_registros' => $total_registros
            ]
        ]);
    } catch (Exception $e) {
        jsonError("Error cargando bandeja: " . $e->getMessage(), 500);
    }
}

// =====================================================
// GUARDAR APROBACIÓN DE CATEGORÍA (CON SALTO INTELIGENTE)
// =====================================================
function guardar_aprobacion_categoria()
{
    global $id_usuario, $usr_usuario, $usr_name;
    requireMethod('POST');
    $db = new ModelDB();

    $id_solicitud = (int) ($_POST['id_prehsol'] ?? 0);
    $observacion = trim($_POST['observacion_categoria'] ?? '');

    if ($id_solicitud <= 0 || empty($observacion)) {
        jsonError("Datos incompletos.");
    }

    try {
        $fecha_sistema = date('Y-m-d');
        $hora_sistema = date('H:i:s');

        // 1. Verificamos si existe el paso de 5K (Paso 41) en la trazabilidad
        $paso_5k = $db->find("SELECT id FROM sol_compra_estado WHERE id_sol = :id AND orden = 41", [':id' => $id_solicitud]);

        $nuevo_estado = $paso_5k ? 41 : 51; // Si hay 5K salta a 41, sino salta a 51 (Orden de Compra)

        // 2. Actualizamos la Cabecera
        // Nota: Asegúrate de que tu tabla 'compras_enc' tenga la columna 'observacion_categoria'. 
        // Si se llama diferente, ajusta el nombre aquí.
        $db->query("UPDATE compras_enc SET estado = :ne, observacion_categoria = :obs WHERE id = :id", [
            ':ne' => $nuevo_estado,
            ':obs' => $observacion,
            ':id' => $id_solicitud
        ]);

        // 3. Trazabilidad: Cerramos el paso actual (31)
        $db->query("UPDATE sol_compra_estado SET descripcion = 'Completado', resolucion = 'C', fecha = :f, hora = :h, id_usuario = :idu, usuario = :u, nom_usuario = :nu WHERE id_sol = :id AND orden = 31", [
            ':f' => $fecha_sistema,
            ':h' => $hora_sistema,
            ':idu' => $id_usuario,
            ':u' => $usr_usuario,
            ':nu' => $usr_name,
            ':id' => $id_solicitud
        ]);

        // 4. Trazabilidad: Activamos el siguiente paso dinámico (41 o 51)
        $db->query("UPDATE sol_compra_estado SET descripcion = 'En proceso', resolucion = 'A' WHERE id_sol = :id AND orden = :ne", [
            ':id' => $id_solicitud,
            ':ne' => $nuevo_estado
        ]);

        echo json_encode(['exito' => true, 'msj' => 'Aprobación técnica procesada correctamente.']);
    } catch (Exception $e) {
        jsonError("Error aprobando: " . $e->getMessage(), 500);
    }
}

// ==========================================================================================
// MÉTODOS HELPER GLOBALES (Seguridad y Estandarización de Respuestas)
// ==========================================================================================
function requireMethod($method)
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method ?? ''))
        jsonError('Método HTTP no permitido. Use ' . strtoupper($method), 405);
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
?>