<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Todas las respuestas son en formato json
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__FILE__) . '/../model/model.php';

define('USR_ID', $_SESSION['i'] ?? null);


function crear()
{
    requireMethod('POST');
    $arr = [];
    $id_empresa = (isset($_POST['id_empresa']) && is_numeric($_POST['id_empresa'])) ? $_POST['id_empresa'] : null;
    $id_cc = (isset($_POST['id_cc']) && is_numeric($_POST['id_cc'])) ? $_POST['id_cc'] : null;
    $id_categoria = (isset($_POST['id_categoria']) && is_numeric($_POST['id_categoria'])) ? $_POST['id_categoria'] : null;

    $db = new ModelDB();
    $data = $db->traza_compra_creacion(USR_ID, $id_empresa, $id_cc, $id_categoria);

    if (!empty($data)) {
        $arr = [
            'exito' => true,
            'trazabilidad' => $data
        ];
    } else {
        $arr = [
            'exito' => false,
            'msj' => 'No existen datos disponibles para mostrar.'
        ];
    }
    echo json_encode($arr);
}

function requireMethod($method)
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method ?? '')) {
        jsonError('Método no permitido. Se esperaba: ' . strtoupper($method ?? ''), 405);
    }
}

function jsonError($message, $code = 400, $errors = [])
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    $out = ['exito' => false, 'msj' => $message];
    if (!empty($errors))
        $out['errores'] = $errors;
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

function obtener_por_solicitud()
{
    requireMethod('POST');
    $id_sol = $_POST['id_sol'] ?? null;
    if (!$id_sol) {
        jsonError('ID de solicitud requerido.');
    }

    $db = new ModelDB();
    $data = $db->get_trazabilidad_solicitud($id_sol);

    if (!empty($data)) {
        echo json_encode(['exito' => true, 'trazabilidad' => $data]);
    } else {
        echo json_encode(['exito' => false, 'msj' => 'No hay trazabilidad.']);
    }
}