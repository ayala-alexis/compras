<?php
// ==========================================================================================
// CONTROLADOR DE TRAZABILIDAD (Event Sourcing UI)
// Se encarga exclusivamente de alimentar la barra de progreso (timeline) del Front-End.
// Retorna JSON estricto para que la UI pinte los estados (done, active, pending).
// ==========================================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forzamos salida JSON para evitar que el navegador malinterprete la respuesta si hay un warning de PHP
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__FILE__) . '/../model/model.php';

// Capturamos el ID del usuario en sesión de forma segura
define('USR_ID', (int) ($_SESSION['i'] ?? 0));

/**
 * Endpoint: crear()
 * Genera la simulación "En Vivo" de quiénes serán los firmantes de una solicitud
 * basándose en la Empresa, Centro de Costos y Categoría seleccionados en el formulario.
 */
function crear()
{
    requireMethod('POST');

    // 1. 🏗️ SANITIZACIÓN ESTRICTA (Solución al Error de UI)
    // Casteamos a (int) para evitar fallos de PDO con nulos.
    // Si el frontend envía un string vacío "" (ej. cuando aún no selecciona categoría), se convertirá en 0.
    $id_empresa = (int) ($_POST['id_empresa'] ?? 0);
    $id_cc = (int) ($_POST['id_cc'] ?? 0);
    $id_categoria = (int) ($_POST['id_categoria'] ?? 0);

    try {
        $db = new ModelDB();

        // 2. Invocamos al motor de trazabilidad en el modelo
        $data = $db->traza_compra_creacion(USR_ID, $id_empresa, $id_cc, $id_categoria);

        if (!empty($data)) {
            echo json_encode([
                'exito' => true,
                'trazabilidad' => $data
            ]);
        } else {
            echo json_encode([
                'exito' => false,
                'msj' => 'No existen datos de trazabilidad disponibles.'
            ]);
        }
    } catch (Exception $e) {
        // 3. 🏗️ MANEJO DE ERRORES CRÍTICOS: Si la BD falla, devolvemos JSON válido en lugar de romper el Front-End
        jsonError("Error procesando trazabilidad: " . $e->getMessage(), 500);
    }
}

/**
 * Endpoint: obtener_por_solicitud()
 * Extrae el historial REAL de firmas de una solicitud que ya fue guardada en BD.
 * Lee directamente de la tabla sol_compra_estado.
 */
function obtener_por_solicitud()
{
    requireMethod('POST');

    // Sanitización estricta del ID
    $id_sol = (int) ($_POST['id_sol'] ?? 0);

    if ($id_sol <= 0) {
        jsonError('ID de solicitud requerido o inválido.');
    }

    try {
        $db = new ModelDB();
        $data = $db->get_trazabilidad_solicitud($id_sol);

        if (!empty($data)) {
            echo json_encode(['exito' => true, 'trazabilidad' => $data]);
        } else {
            echo json_encode(['exito' => false, 'msj' => 'No hay registros de trazabilidad para esta solicitud.']);
        }
    } catch (Exception $e) {
        jsonError("Error procesando trazabilidad histórica: " . $e->getMessage(), 500);
    }
}

// =====================================================================
// UTILIDADES DE SEGURIDAD GLOBAL
// =====================================================================

/**
 * Fuerza a que el endpoint solo responda si el verbo HTTP coincide (ej. POST).
 * Previene ataques de acceso directo por URL.
 */
function requireMethod($method)
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method ?? '')) {
        jsonError('Método HTTP no permitido. Se esperaba: ' . strtoupper($method ?? ''), 405);
    }
}

/**
 * Estructura estándar para devolver errores al Front-End.
 */
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