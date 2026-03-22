<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Todas las respuestas son en formato json
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__FILE__) . '/../model/model.php';

define('USR_ID', $_SESSION['i'] ?? null);


function catalogo()
{
    requireMethod('GET');
    $arr = [];

    //Filtrar catalogo
    $id = (isset($_GET['id']) ? $_GET['id'] : null);
    $cat = (isset($_GET['cat']) ? $_GET['cat'] : null);

    $data = filtrar_catalogo($cat, $id);

    if (!empty($data)) {
        $arr = [
            'exito' => true,
            'catalogo' => $data
        ];
    } else {
        $arr = [
            'exito' => false,
            'msj' => 'No existen datos disponibles para mostrar.'
        ];
    }
    echo json_encode($arr);
}

function catalogo_search()
{
    requireMethod('POST');
    $arr = [];

    //Filtrar catalogo
    $id = (isset($_GET['id']) ? $_GET['id'] : null);
    $search = (isset($_POST['search']) ? $_POST['search'] : null);
    $cat = (isset($_GET['cat']) ? $_GET['cat'] : null);

    $data = filtrar_catalogo_search($search, $cat, $id);

    if (!empty($data)) {
        $arr = [
            'exito' => true,
            'catalogo' => $data
        ];
    } else {
        $arr = [
            'exito' => false,
            'msj' => 'No existen datos disponibles para mostrar.'
        ];
    }
    echo json_encode($arr);
}

function filtrar_catalogo($cat, $id)
{
    $db = new ModelDB();
    $cat = trim(strtolower($cat ?? ''));
    $data = null;

    switch ($cat) {
        case 'empresa_user':
            $data = $db->get_empresas_user(USR_ID);
            break;
        case 'cc_user':
            $data = $db->get_cc_empresa_user($id, USR_ID);
            break;
        case 'categoria_compra':
            $data = $db->get_categorias_compras();
            break;
        default:
            break;
    }

    return $data;
}
function filtrar_catalogo_search($search, $cat, $id)
{
    $db = new ModelDB();
    $s = trim(strtolower($search ?? ''));
    $cat = trim(strtolower($cat ?? ''));
    $id_empresa = $_GET['id_emp'] ?? null;
    $data = null;

    switch ($cat) {
        case 'productos_cat':
            $data = $db->get_productos_cat($id, $search);
            break;
        case 'proveedor_as400':
            $data = $db->get_proveedores_as400($id_empresa, $search);
            break;
        default:
            break;
    }

    return $data;
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

