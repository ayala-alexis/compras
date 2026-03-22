<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__FILE__).'/../model/cheque/EntityDB.php';
//echo (isset($_GET['email']) ? "<br/><b>si</b>" : "<br/><b>no</b>");

$is_ufinal_o_autorizador = ((int)$_SESSION['rol_de_usuario']<=2);
if(!$is_ufinal_o_autorizador){
    header('Location: ?c=login&a=ingreso');
    die();
}

function cmp($a, $b)
{
    return ($a->fecha_creado.str_pad($a->hora_creado,6,'0',STR_PAD_LEFT) < $b->fecha_creado.str_pad($b->hora_creado,6,'0',STR_PAD_LEFT));
}
function is_cheque_file($corre){
    $file_src = 'public/upload/'.$corre;
    if(is_file($file_src.'.pdf')){
        return $file_src.'.pdf';
    }elseif(is_file($file_src.'.xlsx')){
        return $file_src.'.xlsx';
    }elseif(is_file($file_src.'.xls')){
        return $file_src.'.xls';
    }elseif(is_file($file_src.'.docx')){
        return $file_src.'.docx';
    }elseif(is_file($file_src.'.png')){
        return $file_src.'.png';
    }elseif(is_file($file_src.'.jpeg')){
        return $file_src.'.jpeg';
    }elseif(is_file($file_src.'.jpg')){
        return $file_src.'.jpg';
    }elseif(is_file($file_src.'.zip')){
        return $file_src.'.zip';
    }elseif(is_file($file_src.'.rar')){
        return $file_src.'.rar';
    }elseif(is_file($file_src.'.7zip')){
        return $file_src.'.7zip';
    }elseif(is_file($file_src.'.7z')){
        return $file_src.'.7z';
    }
    return '';
}
function status_solicitud($tipo,$status,$requiere_recepcion=1){
    $descripcion=$status;
    switch ($tipo) {
        case 'ci':
            if($status=='0'){
                $descripcion = 'CREADO';
            }else if($status=='1'){
                $descripcion = 'AUTORIZADO';
            }else if($status=='2'){
                $descripcion = 'EN PROCESO';
            }else if($status=='3'){
                $descripcion = 'IMPRESO';
            }else if($status=='4'){
                $descripcion = 'EN REVISION';
            }else if($status=='10'){
                $descripcion = 'DESISTIDA';
            }
            break;
        case 'sol':
            if($status=='0'){
                $descripcion = 'CREADO';
            }else if($status=='1'){
                $descripcion = 'SOLICITADO';
            }else if($status=='2'){
                $descripcion = 'ENVIADO REVISION';
            }else if($status=='3'){
                $descripcion = 'ENVIADO GESTION';
            }else if($status=='4'){
                $descripcion = 'RECIBIDA EN PROVEEDURIA';
            }else if($status=='5'){
                $descripcion = 'EN ESPERA DE COTIZACION';
            }else if($status=='6'){
                $descripcion = 'EN ORDEN DE COMPRA';
            }else if($status=='7'){
                $descripcion = 'RECIBIDO DE PROVEEDOR';
            }else if($status=='8'){
                $descripcion = 'ENVIADO AL SOLICITANTE';
            }else if($status=='9'){
                $descripcion = 'RECIBIDO SOLICITANTE';
            }else if($status=='10'){
                $descripcion = 'DESISTIDA';
            }else if($status=='11'){
                $descripcion = 'PENDIENTE APROBACION D.E.';
            }else if($status=='12'){
                $descripcion = 'PENDIENTE POR AJUSTE DE PRESUPUESTO';
            }
            break;
        case 'cheque':
            if($status=='N1-C'){
                $descripcion="Creada S/Enviar";
            }else if($status=='N2-R'){
                $descripcion="Proc. Autorizador Cco.";
            }else if($status=='N2-C'){
                $descripcion="Creada S/Enviar";
            }else if($status=='N2-D'){
                $descripcion="Desistido Por Autorizador Cco.";
            }else if($status=='N3-C'){
                $nivel="Creado S/Enviar";
            }else if($status=='N3-R'){
                $descripcion="Proc. Autorizador C. Gasto";
            }else if($status=='N3-D'){
                $descripcion="Desistido Por Autorizador C. Gasto";
            }else if($status=='N3-T'){
                $descripcion="Proc. Autorizador C. Gasto";
            }else if($status=='N3-A'){
                $descripcion="Aprobado Autorizador C. Gasto";
            }else if($status=='N3-Z'){
                $descripcion="Proc. Autorizador D. Ejecutiva";
            }else if($status=='N3-W'){
                $descripcion="Desistido Por D. Ejecutivo";
            }else if($status=='N4-C'){
                $descripcion="Creado S/Enviar";
            }else if($status=='N4-R'){
                $descripcion="Proc. Gestor C. Gasto";
            }else if($status=='N4-D'){
                $descripcion="Desistido Por Gestor C. Gasto";
            }else if($status=='N4-T'){
                $descripcion="Proc. Gestor C. Gasto";
            }else if($status=='N4-A'){
                $descripcion="Aprobado Gestor C. Gasto";
            }else if($status=='N5-C'){
                $descripcion="Creado S/Enviar";
            }else if($status=='N5-D'){
                $descripcion="Desistido Por Contabilidad";
            }else if($status=='N5-R' && $requiere_recepcion==1){
                $descripcion="Pendiente de Recepción";
            }else if($status=='N5-R' && $requiere_recepcion==0){
                $descripcion="Aprobado para impresion";
            }else if($status=='N5-P'){
                $descripcion="Aprobado para impresion";
            }
            break;
        case 'req':
            if($status=='0'){
                $descripcion = 'CREADO';
            }else if($status=='1'){
                $descripcion = 'ENVIO AUTORIZACION';
            }else if($status=='2'){
                $descripcion = 'AUTORIZADO';
            }else if($status=='3'){
                $descripcion = 'RECOLECTADO PROVEEDURIA';
            }else if($status=='4'){
                $descripcion = 'EN ORDEN DE COMPRA';
            }else if($status=='5'){
                $descripcion = 'RECIBIDO DE PROVEEDOR';
            }else if($status=='6'){
                $descripcion = 'ENVIADO AL SOLICITANTE';
            }else if($status=='7'){
                $descripcion = 'RECIBIDO SOLICITANTE';
            }else if($status=='8'){
                $descripcion = 'NEGADO';
            }else if($status=='9'){
                $descripcion = 'PENDIENTE APROBACION D.E.';
            }else if($status=='10'){
                $descripcion = 'SIN PRESUPUESTO';
            }
            break;
    }

    return strtoupper($descripcion);
}
function index(){
	$db = new EntityDB;
	$perfil = $db->get_usuario();
    $db->sol_cheque = 1;
    $db->sol_req = 0;
    $db->sol_ci = 0;
    $db->sol_compra = 0;
    if(!empty($perfil)){
        $db->sol_req = (int)$perfil->is_req;
        $db->sol_ci = (int)$perfil->is_ci;
        $db->sol_compra = (int)$perfil->is_solc;
    }
    $data_sol = array();
    if($perfil->rol==1){
        $data = $db->get_solicitud_ufinal_ult(0,0);
        $data_sol['n1']=array();
        if(!empty($data)){
            foreach ($data as $d) {
                $data_sol['n1'][]=$d;
            }
        }
    }else{
        $data = $db->get_solicitud_ufinal_ult(0,0);
        $data_sol['n1']=array();
        foreach ($data as $d) {
            $data_sol['n1'][]=$d;
        }
        $data = $db->get_solicitud_uautorizador_ult(0,0);
        $contador_pendiente_autorizar = $db->get_solicitud_uautorizador_contador();
        $data_sol['n2']=array();
        foreach ($data as $d) {
            $data_sol['n2'][]=$d;
        }
    }

    require_once view('index');
}

function get_controller(){
    return GET('c');
}
function get_action(){
    return (!(GET('a')==null || GET('a')=='') ? GET('a') : 'index');
}

function get_type_sol($tipo){
    switch ($tipo) {
        case 'ci':
            return 'Consumo Interno';
            break;
        case 'sol':
            return 'Solicitud de Compra';
            break;
        case 'cheque':
            return 'Solicitud de Cheque';
            break;
        case 'req':
            return 'Requisición de Suministro';
            break;
    }
    return $tipo;
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
function json_cc_empresa_all(){
    $db = new EntityDB;
    $empresa = $db->get_empresa_cc_perfil_all(POST('id'));
    $arr = array();
    if(!empty($empresa)){
        $arr = $empresa;
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
function consulta(){
    $db = new EntityDB;
    $db_ = new Entity();
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
    $proyecto=(isset($_GET['p']) ? $_GET['p'] : "");
    $categoria=(isset($_GET['ct']) ? $_GET['ct'] : "0");
    $proveedor=(isset($_GET['pv']) ? $_GET['pv'] : "");

    if(!is_numeric($id_anio)){
        $id_anio=date('Y');
    }
    if(!is_numeric($id_mes)){
        $id_mes="0";
    }

   

    $data_sol = array();
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta

    if($perfil->rol==1){
        $category=$db->get_solicitud_ufinal_categoria($id_empresa,$id_cc,$id_anio,$id_mes,$proyecto,$proveedor);

        $contador = $db->get_solicitud_ufinal_count($id_empresa,$id_cc,null,$id_anio,$id_mes,$id_tiposol,$categoria,$proyecto,$proveedor);
        $paginador = paginar($contador, $total_elto);
        if($paginador['paginar']){
            $db->set_page_elto($paginador['pagina_actual']);
        }
        $data = $db->get_solicitud_ufinal($id_empresa,$id_cc,null,$id_anio,$id_mes,$id_tiposol,$categoria,$proyecto,$proveedor);
        foreach ($data as $d) {
            $data_sol[]=$d;
        }
    }else{
        $category=$db->get_solicitud_uautorizador_categoria($id_empresa,$id_cc,$id_anio,$id_mes,$proyecto,$proveedor);

        $contador = $db->get_solicitud_uautorizador_count($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$categoria,$proveedor,$proyecto,null);
        $paginador = paginar($contador, $total_elto);
        if($paginador['paginar']){
            $db->set_page_elto($paginador['pagina_actual']);
        }
        $data = $db->get_solicitud_uautorizador($id_empresa,$id_cc,$id_anio,$id_mes,$id_tiposol,$categoria,$proveedor,$proyecto,null);
        foreach ($data as $d) {
            $data_sol[]=$d;
        }
    }
    require_once view('consulta');
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
    return 'view/menu/'.$view_name.'.php';
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