<?php
session_start();

require_once dirname(__FILE__).'/../model/proveedor/ProveedorDB.php';
//echo (isset($_GET['email']) ? "<br/><b>si</b>" : "<br/><b>no</b>");

$is_ufinal_o_autorizador = ((int)$_SESSION['rol_de_usuario']<=2);
if(!$is_ufinal_o_autorizador){
    //header('Location: ?c=login&a=ingreso');
    //die();
}

function cmp($a, $b)
{
    return ($a->fecha_creado.str_pad($a->hora_creado,6,'0',STR_PAD_LEFT) < $b->fecha_creado.str_pad($b->hora_creado,6,'0',STR_PAD_LEFT));
}


function get_controller(){
    return GET('c');
}
function get_action(){
    return (!(GET('a')==null || GET('a')=='') ? GET('a') : 'index');
}


function json_usuarios(){
    $db = new ProveedorDB;
    $usuarios = $db->get_ListaUsuarios(GET('q'));
    $arr = array();
    if(!empty($usuarios)){
        $arr = $usuarios;
    }
    echo json_encode($arr);
}

function json_categoria(){
    $db = new ProveedorDB;
    $cat = $db->get_categoria(GET('cat'));
    $arr = array();
    if(!empty($cat)){
        $arr = $cat;
    }
    echo json_encode($arr);
}

function json_cc_empresa(){
    $db = new EntityDB;
    $perfil = $db->get_usuario();
    $empresa = $db->get_empresa_cc_perfil(POST('id'));
    $arr = array();
    if(!empty($empresa)){
        $arr = $empresa;
    }
    echo json_encode($arr);
}
function json_crear_categoria(){
    $arr = array('exito' => false);
    if(IS_POST()){
        $db = new ProveedorDB;
        $msg = "";
        $ok = $db->crear_categoria($_POST,&$msg);
        if($ok){
            $arr = array(
                'exito' => TRUE,
                'msg'   => "Categoría creada con éxito"
            );
        }else{
            $arr = array(
                'exito' => $ok,
                'msg'   => $msg
            );
        }
    }else{
        $arr = array(
            'exito' => false,
            'msg'   => 'No se han recibido datos'
        );
    }
    echo json_encode($arr);
}
function json_editar_categoria(){
    $arr = array('exito' => false);
    if(IS_POST()){
        $db = new ProveedorDB;
        $msg = "";
        $ok = $db->editar_categoria($_POST,&$msg);
        if($ok){
            $arr = array(
                'exito' => TRUE,
                'msg'   => "Categoría actualizada con éxito"
            );
        }else{
            $arr = array(
                'exito' => $ok,
                'msg'   => $msg
            );
        }
    }else{
        $arr = array(
            'exito' => false,
            'msg'   => 'No se han recibido datos'
        );
    }
    echo json_encode($arr);
}
function json_existe_categoria_cheque(){
    $arr = array('existe' => false);
    if(IS_POST()){
        $db = new ProveedorDB;
        $ok = $db->existe_categoria_cheque($_POST['id']);
        if($ok){
            $arr = array(
                'existe' => TRUE
            );
        }
    }
    echo json_encode($arr);
}
function json_eliminar_categoria_cheque(){
    $arr = array('exito' => false);
    if(IS_POST()){
        $db = new ProveedorDB;
        $ok = $db->eliminar_categoria_cheque($_POST['id']);
        if($ok){
            $arr = array(
                'exito' => TRUE
            );
        }
    }
    echo json_encode($arr);
}
function paginar($contador,$total_elto){
    $pag = GET('pag');
    $pagina = array();
    
    if(!empty($pag)){
        if(!is_numeric($pag)){
            $pag=0;
        }
    }else{
        $pag=0;
    }
    
    $ispag = ($contador>$total_elto);
    

    if($ispag){
        $total_pagina = (int) ($contador/$total_elto);
        
        if($total_pagina<($contador/$total_elto)){
            $total_pagina++;
        }
        $pagina['paginar']=TRUE;
        $pagina['pagina_actual']=$pag;
        $pagina['pagina_total']=$total_pagina;
        $pagina['pagina_item']=$total_elto;
        $pagina['pagina_item_total']=$contador;
    }else{
        $pagina['paginar']=FALSE;
    }
    return $pagina;
}
function index(){

    $db = new ProveedorDB;
    $perfil = $db->get_usuario();
    //$empresa = $db->get_empresa_perfil();

    $gcia = (isset($_GET['gcia']) ? $_GET['gcia'] : "");

    $contador=0;
    $pag = 0;

    $data_prov = array();
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta


    $contador = $db->get_categoria_sics_count($gcia);
        
    $paginador = paginar($contador, $total_elto);
    if($paginador['paginar']){
        $db->set_page_elto($paginador['pagina_actual']);
    }

    $data = $db->get_categoria_sics($gcia);

    $data_gcia = $db->get_proveedor_gcia();
    foreach ($data as $d) {
        $data_prov[]=$d;
    }

    require_once view('index');
}
function autorizarcc(){
    $db = new EntityDB;
    $perfil = $db->get_usuario();
    $empresa = $db->get_empresa_perfil();
    $db->sol_cheque = 1;
    $db->sol_req = 0;
    $db->sol_ci = 0;
    $db->sol_compra = 0;
    $contador=0;
    $pag = 0;
    if(!empty($perfil)){
        $db->sol_req = (int)$perfil->is_req;
        $db->sol_ci = (int)$perfil->is_ci;
        $db->sol_compra = (int)$perfil->is_solc;
    }


    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_tiposol=(isset($_GET['s']) ? $_GET['s'] : "");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : date('Y'));
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : "0");

    if(!is_numeric($id_anio)){
        $id_anio=date('Y');
    }
    if(!is_numeric($id_mes)){
        $id_mes="0";
    }

    $data_sol = array();
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta

    
    $contador = $db->get_solicitud_uautorizadorcc_count($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,null);
    $paginador = paginar($contador, $total_elto);
    if($paginador['paginar']){
        $db->set_page_elto($paginador['pagina_actual']);
    }
    $data = $db->get_solicitud_uautorizadorcc($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,null);
    foreach ($data as $d) {
        $data_sol[]=$d;
    }
    require_once view('autorizarcc');
}

function consulta_categoria_copia(){
    $db = new EntityDB;
    $perfil = $db->get_usuario();
    //depurar($perfil);
    $empresa = $db->get_empresa_perfil_all();
    $db->sol_cheque = 1;
    $db->sol_req = 0;
    $db->sol_ci = 0;
    $db->sol_compra = 0;
    $contador=0;
    $pag = 0;
    if(!empty($perfil)){
        $db->sol_req = (int)$perfil->is_req;
        $db->sol_ci = (int)$perfil->is_ci;
        $db->sol_compra = 1;
    }


    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_tiposol=(isset($_GET['s']) ? $_GET['s'] : "");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : date('Y'));
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : "0");

    if(!is_numeric($id_anio)){
        $id_anio=date('Y');
    }
    if(!is_numeric($id_mes)){
        $id_mes="0";
    }

    $data_sol = array();
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta

    
    $contador = $db->get_solicitud_uautorizadorcat_count($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$perfil->categoria->id);
    $paginador = paginar($contador, $total_elto);
    if($paginador['paginar']){
        $db->set_page_elto($paginador['pagina_actual']);
    }
    $data = $db->get_solicitud_uautorizadorcat($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$perfil->categoria->id);
    foreach ($data as $d) {
        $data_sol[]=$d;
    }
    require_once view('consulta_categoria');
}

function consulta_categoria(){
    $db = new EntityDB;
    $perfil = $db->get_usuario();
    //depurar($perfil);
    $empresa = $db->get_empresa_perfil_all();
    $db->sol_cheque = 1;
    $db->sol_req = 0;
    $db->sol_ci = 0;
    $db->sol_compra = 0;
    $contador=0;
    $pag = 0;
    if(!empty($perfil)){
        $db->sol_req = (int)$perfil->is_req;
        $db->sol_ci = (int)$perfil->is_ci;
        $db->sol_compra = 1;
    }


    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_tiposol=(isset($_GET['s']) ? $_GET['s'] : "");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : date('Y'));
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : "0");
    $estado=(isset($_GET['st']) ? $_GET['st'] : "P");
    $proveedor = (isset($_GET['pv']) ? $_GET['pv'] : "");

    if(!is_numeric($id_anio)){
        $id_anio=date('Y');
    }
    if(!is_numeric($id_mes)){
        $id_mes="0";
    }

    $data_sol = array();
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta

    
    $contador = $db->get_solicitud_uautorizadorcat_count($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$perfil->categoria->id,$estado,$proveedor);
    $paginador = paginar($contador, $total_elto);
    if($paginador['paginar']){
        $db->set_page_elto($paginador['pagina_actual']);
    }
    $data = $db->get_solicitud_uautorizadorcat($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$perfil->categoria->id,$estado,$proveedor);

    $data_prov = array();
    if($id_tiposol=='' || $id_tiposol=='cheque'){
        $data_prov = $db->get_solicitud_uautorizadorcat_proveedor($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$perfil->categoria->id,$estado);
    }

    foreach ($data as $d) {
        $data_sol[]=$d;
    }
    require_once view('consulta_categoria');
}


/************************************************************************************************
*	FUNCIONES DE UTILIDADES																		*
************************************************************************************************/

function GET($url){
	if(isset($_GET[$url])){
        $isdata = $_GET[$url];
	    if(!empty($isdata)){
	        return trim($isdata);
	    }
	}
    return null;
}

function view($view_name){
    $_SESSION['menu_return']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    return 'view/provas/'.$view_name.'.php';
}
function IS_POST(){
    return (!empty($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST');
}
function IS_GET(){
    return (!empty($_GET) && $_SERVER['REQUEST_METHOD'] == 'GET');
}
function POST($url){
    if(!empty($_POST[$url])){
        return trim($_POST[$url]);
    }
    return null;
}
function depurar($obj){
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
}

/************************************************************************************************
*	FIN FUNCIONES DE UTILIDADES	  																*
************************************************************************************************/