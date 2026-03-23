<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/*
 * Nueva solicitud de compra
 */
function crear()
{

    require_once 'view/solicitud/crear.php';
}
// =====================================================
// VISTA: COTIZAR SOLICITUD (ANALISTA)
// =====================================================
function cotizar()
{
    $id_solicitud = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id_solicitud <= 0) {
        header('Location: ?c=solicitud&a=consulta_cotizacion');
        exit;
    }

    require_once dirname(__FILE__) . '/../model/model.php';
    $db = new ModelDB();
    $hs = $db->get_solicitud_cabecera($id_solicitud);
    $ds = $db->get_solicitud_detalle($id_solicitud);

    // 🏗️ NUEVO: Extraemos los adjuntos de la tabla relacional
    $adjuntos_db = $db->get_adjuntos_solicitud($id_solicitud);

    if (!$hs) {
        header('Location: ?c=solicitud&a=consulta_cotizacion');
        exit;
    }

    require_once 'view/solicitud/cotizar.php';
}

/*
 * Nueva solicitud de compra
 */
function consulta_cotizacion()
{

    require_once 'view/solicitud/consulta_cotizacion.php';
}
function consulta_aprobacion_cc()
{

    require_once 'view/solicitud/consulta_aprobacion_cc.php';
}
function consulta_pendiente_oc()
{

    require_once 'view/solicitud/consulta_pendiente_oc.php';
}
function consulta_oc()
{

    require_once 'view/solicitud/consulta_oc.php';
}
// =====================================================
// VISTA: BANDEJA AUTORIZADOR CATEGORÍA (PASO 31)
// =====================================================
function consulta_aprobacion_categoria()
{
    require_once 'view/solicitud/consulta_aprobacion_categoria.php';
}

// =====================================================
// VISTA: REVISAR COTIZACIÓN CATEGORÍA
// =====================================================
function revisar_cotizacion_categoria()
{
    require_once dirname(__FILE__) . '/../model/model.php';
    $db = new ModelDB();

    $id_solicitud = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    if ($id_solicitud <= 0)
        die("ID de solicitud no válido.");

    $hs = $db->get_solicitud_cabecera($id_solicitud);
    $ds = $db->get_solicitud_detalle($id_solicitud);
    $adjuntos_db = $db->get_adjuntos_solicitud($id_solicitud);

    if (!$hs)
        die("La solicitud no existe.");

    require_once 'view/solicitud/revisar_cotizacion_categoria.php';
}
// =====================================================
// VISTA: GENERAR ORDEN DE COMPRA (ANALISTA)
// =====================================================
function generar_oc()
{
    require_once dirname(__FILE__) . '/../model/model.php';
    $db = new ModelDB();

    $id_solicitud = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    if ($id_solicitud <= 0) {
        die("ID de solicitud no válido.");
    }

    $hs = $db->get_solicitud_cabecera($id_solicitud);
    $ds = $db->get_solicitud_detalle($id_solicitud);

    if (!$hs) {
        die("La solicitud no existe o no se pudo cargar.");
    }

    require_once 'view/solicitud/generar_oc.php';
}

// =====================================================
// VISTA: REVISAR ORDEN DE COMPRA (JEFE COMPRAS)
// =====================================================
function revisar_oc()
{
    require_once dirname(__FILE__) . '/../model/model.php';
    $db = new ModelDB();

    $id_solicitud = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $numero_oc = isset($_REQUEST['oc']) ? trim($_REQUEST['oc']) : '';

    if ($id_solicitud <= 0 || empty($numero_oc)) {
        die("Parámetros de Orden de Compra no válidos.");
    }

    $hs = $db->get_oc_cabecera($id_solicitud, $numero_oc);
    $ds = $db->get_oc_detalle($id_solicitud, $numero_oc);

    if (!$hs || empty($ds)) {
        die("La Orden de Compra no existe o no contiene detalles.");
    }

    require_once 'view/solicitud/revisar_oc.php';
}
// =====================================================
// VISTA: VER ORDEN DE COMPRA (SOLO LECTURA)
// =====================================================
function ver_oc()
{
    require_once dirname(__FILE__) . '/../model/model.php';
    $db = new ModelDB();

    $id_solicitud = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $numero_oc = isset($_REQUEST['oc']) ? trim($_REQUEST['oc']) : '';

    if ($id_solicitud <= 0 || empty($numero_oc)) {
        die("Parámetros de Orden de Compra no válidos.");
    }

    $hs = $db->get_oc_cabecera($id_solicitud, $numero_oc);
    $ds = $db->get_oc_detalle($id_solicitud, $numero_oc);

    if (!$hs || empty($ds)) {
        die("La Orden de Compra no existe o no contiene detalles.");
    }

    require_once 'view/solicitud/ver_oc.php';
}
// =====================================================
// VISTA: REVISAR COTIZACIÓN (AUTORIZADOR CCO)
// =====================================================
function revisar_cotizacion_cc()
{
    require_once dirname(__FILE__) . '/../model/model.php';
    $db = new ModelDB();

    $id_solicitud = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

    if ($id_solicitud <= 0) {
        die("ID de solicitud no válido.");
    }

    $hs = $db->get_solicitud_cabecera($id_solicitud);
    $ds = $db->get_solicitud_detalle($id_solicitud);

    // 🏗️ NUEVO: Extraemos los adjuntos de la tabla relacional
    $adjuntos_db = $db->get_adjuntos_solicitud($id_solicitud);

    if (!$hs) {
        die("La solicitud no existe o no se pudo cargar.");
    }

    require_once 'view/solicitud/revisar_cotizacion_cc.php';
}