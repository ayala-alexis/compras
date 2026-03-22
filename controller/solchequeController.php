<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once dirname(__FILE__).'/../model/cheque/Entity.php';
require_once dirname(__FILE__).'/../model/cheque/Entity2.php';
//echo (isset($_GET['email']) ? "<br/><b>si</b>" : "<br/><b>no</b>");
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
    return 'view/solcheque/'.$view_name.'.php';
}
function IS_POST(){
    return (!empty($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST');
}
function reportesol(){
    $db=new Entity();
    $perfil=$db->findPerfil(get_id_usuario()); //
    require_once view('reportesol');
}
function reportesoc(){
    $db=new Entity();
    $perfil=$db->findPerfil(get_id_usuario()); //
    require_once view('reportesoc');
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
function status_descripcion($avance,$status){
    $nivel="";
    switch ($avance) {
        case 'N1':
            if($status=='C'){
                $nivel="Creada S/Enviar";
            }
            break;
        case 'N2':
            if($status=='R'){
                $nivel="Proc. Autorizador Cco.";
            }else if($status=='C'){
                $nivel="Creada S/Enviar";
            }else if($status=='D'){
                $nivel="Desistido Por Autorizador Cco.";
            }
            break;
        case 'N3':
            if($status=='C'){
                $nivel="Creado S/Enviar";
            }else if($status=='R'){
                $nivel="Proc. Autorizador C. Gasto";
            }else if($status=='D'){
                $nivel="Desistido Por Autorizador C. Gasto";
            }else if($status=='T'){
                $nivel="Proc. Autorizador C. Gasto";
            }else if($status=='A'){
                $nivel="Aprobado Autorizador C. Gasto";
            }else if($status=='Z'){
                $nivel="Proc. Autorizador D. Ejecutiva";
            }else if($status=='W'){
                $nivel="Desistido Por D. Ejecutivo";
            }
            break;
        case 'N4':
            if($status=='C'){
                $nivel="Creado S/Enviar";
            }else if($status=='R'){
                $nivel="Proc. Gestor C. Gasto";
            }else if($status=='D'){
                $nivel="Desistido Por Gestor C. Gasto";
            }else if($status=='T'){
                $nivel="Proc. Gestor C. Gasto";
            }else if($status=='A'){
                $nivel="Aprobado Gestor C. Gasto";
            }
            break;
        case 'N5':
            if($status=='C'){
                $nivel="Creado S/Enviar";
            }else if($status=='D'){
                $nivel="Desistido Por Contabilidad";
            }else if($status=='R'){
                $nivel="Aprobado para impresi&oacute;n";
            }else if($status=='P'){
                $nivel="Aprobado para impresi&oacute;n";
            }
            break;
    }

    return $nivel;
}
function reporteoc(){
    $db=new Entity();
    $perfil=$db->findEmpresaOC(); //
    require_once view('reporteoc');
}
function convert_fecha_integer($fecha){
    if(strlen($fecha)==10){
        return substr($fecha, 6,4).substr($fecha, 3,2).substr($fecha, 0,2);
    }
    return 0;
}
function convert_integer_fecha($fecha){
    if($fecha>0){
        $fec=(string)$fecha;
        return substr($fec, 6,2)."/".substr($fec,4,2)."/".substr($fec,0,4);
    }
    return 0;
}
function convert_integer_hora($hora){
    if($hora>0){
        $hor=(string)$hora;
        return substr($hor, 0,2).":".substr($hor,2,2);
    }
    return 0;
}
function get_id_usuario(){
	if(isset($_SESSION['i'])){
		if(is_numeric($_SESSION['i'])){
			return (int)$_SESSION['i'];
		}
	}
	return 0;
}
function crear(){
    $db=new Entity();
    $perfil=$db->findPerfil(get_id_usuario(), get_user_multiple()); //
    $category=$db->findCategoriasUsuario_v2("Solicitud de cheque",0,0); //
    $gcias = $db->get_usuario_gcia();
    require_once view('crear');
}
function editar(){
    if(GET('s')!=null){
        $db=new Entity(get_id_usuario());
        $perfil=$db->findPerfil(get_id_usuario(), get_user_multiple()); //
        $solc=$db->findSolicitud(GET('s'));
        require_once view('editar');
    }
}
function detalle(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        
        //Para usuario con dos perfiles Autorizador Cco y C. Gasto
        $rolini=$perfil->rol;
        if(get_user_multiple()){ //usuario tiene 2 roles
            $db->setRolUsuario('N2');
            $perfil->rol='N2';
        }
        
        $solc=$db->findSolicitud(GET('s'));

        $s = $db->findSolicitudDetail(GET('s'));
        if(!empty($s)){
            $id_empresa = $s->id_empresa;
            $id_cc = $s->id_cc;
            $monto = $s->valor_cheque;
            $id_categoria = $s->id_categoria_gasto;
            $id_usuario = $s->id_usuario;
            $moneda = $s->moneda;
            $gerencia = $s->id_aprueba_categoria;
            if(!isset($gerencia)) $gerencia = 0;
            if(is_null($gerencia)) $gerencia = 0;
            if(empty($gerencia)) $gerencia = 0;

            $flujo=$db->flujo_aprobacion_user_cc_v2($id_empresa,$id_cc,$monto,$id_categoria,$id_usuario,$moneda,$gerencia);

            //$db->depurar($flujo);
        }

        //Se re-establece rol
        if($solc->avance!='N2' && get_user_multiple()){
            $db->setRolUsuario($rolini);
            $perfil->rol=$rolini;
        }
        require_once view('detalle');
    }
}
function json_detalle(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $solc=$db->findSolicitud(GET('s'));
        $arr=$solc;
        echo json_encode($arr);
    }
}
function prints(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $solc=$db->findSolicitud(GET('s'));
        require_once view('prints');
    }
}
function desistir(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $solc=$db->findSolicitud(GET('s'));
        require_once view('desistir');
    }
}
function desistirde(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $solc=$db->findSolicitud(GET('s'));
        require_once view('desistirde');
    }
}
function autorizar(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $solc=$db->findSolicitud(GET('s'));
        require_once view('autorizar');
    }
}
function devolver(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $solc=$db->findSolicitud(GET('s'));
        require_once view('devolver');
    }
}
function autorizarde(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $solc=$db->findSolicitud(GET('s'));
        require_once view('autorizarde');
    }
}
function reporte(){
    $db=new Entity();
    $perfil=$db->findPerfil(get_id_usuario()); //
    require_once view('reporte');
}
function debugs($obj){
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
}
function gcategorizar(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $category=$db->findCategoriaList();
        $solc=$db->findSolicitud(GET('s'));
        require_once view('gcategorizar');
    }
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
    }else{
        $pagina['paginar']=FALSE;
    }
    return $pagina;
}
function consultar(){
    $db=new Entity();

    //solicitudes con errores de autorización
    $db->corregir_solicitud_cheque();

    $perfil=$db->findPerfil(get_id_usuario(),get_user_multiple());


    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_estado=(isset($_GET['s']) ? $_GET['s'] : "");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : "");
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : date('m'));

    if(empty($id_anio)){
        $id_anio=date('Y');
    }
    if($id_mes==""){
        $id_mes="0";
    }

    $filter=array(
        'rol'       =>  $perfil->rol,
        'id_usuario'=> get_id_usuario(),
        'consultar'   => true
    );
    if($perfil->rol!='N1'){
        $filter['id_usuario_rol']=get_id_usuario();
        $filter['no_status']='R';   //Solicitud que ya se han tratado
    }
    if(!empty($id_anio)){
        if(is_numeric($id_anio)){
            $filter['f_id_anio']=$id_anio;
        }
    }
    if(!empty($id_mes)){
        if(is_numeric($id_mes)){
            $filter['f_id_mes']=$id_mes;
        }
    }
    if(!empty($id_empresa)){
        if(is_numeric($id_empresa)){
            $filter['f_id_empresa']=$id_empresa;
        }
    }
    if(!empty($id_cc)){
        if(is_numeric($id_cc)){
            $filter['f_id_cc']=$id_cc;
        }
    }
    if(!empty($id_estado)){
        $filter['f_id_estado']=$id_estado;
    }
    
    //Paginador de eltos
    $contador = $db->findSolicitudListCount($filter); //contador total de items de la consulta sin paginador
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta
    $paginador = paginar($contador, $total_elto);
    
    if($paginador['paginar']){
        $filter['pag']=$paginador['pagina_actual'];
    }
    $data=$db->findSolicitudList($filter);
    require_once view('consultar');
}
function consultar2(){
    $db=new Entity();

    //solicitudes con errores de autorización
    $db->corregir_solicitud_cheque();

    $perfil=$db->findPerfil(get_id_usuario(),get_user_multiple());


    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_estado=(isset($_GET['s']) ? $_GET['s'] : "");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : "");
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : (int)date('m'));

    if(empty($id_anio)){
        $id_anio=date('Y');
    }
    if($id_mes==""){
        $id_mes="0";
    }

    $filter=array(
        'rol'       =>  $perfil->rol,
        'id_usuario'=> get_id_usuario(),
        'consultar'   => true
    );
    if($perfil->rol!='N1'){
        $filter['id_usuario_rol']=get_id_usuario();
        $filter['no_status']='R';   //Solicitud que ya se han tratado
    }
    if(!empty($id_anio)){
        if(is_numeric($id_anio)){
            $filter['f_id_anio']=$id_anio;
        }
    }
    if(!empty($id_mes)){
        if(is_numeric($id_mes)){
            $filter['f_id_mes']=$id_mes;
        }
    }
    if(!empty($id_empresa)){
        if(is_numeric($id_empresa)){
            $filter['f_id_empresa']=$id_empresa;
        }
    }
    if(!empty($id_cc)){
        if(is_numeric($id_cc)){
            $filter['f_id_cc']=$id_cc;
        }
    }
    if(!empty($id_estado)){
        $filter['f_id_estado']=$id_estado;
    }
    
    echo "<pre>";
    print_r($filter);
    echo "</pre>";

    //Paginador de eltos
    $contador = $db->findSolicitudListCount($filter); //contador total de items de la consulta sin paginador
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta
    $paginador = paginar($contador, $total_elto);

    echo "<pre>";
    print_r($contador);
    print_r($total_elto);
    echo "</pre>";
    
    if($paginador['paginar']){
        $filter['pag']=$paginador['pagina_actual'];
    }
    echo "<pre>";
    print_r($filter);
    echo "</pre>";
    $data=$db->findSolicitudList($filter);
    require_once view('consultar');
}
function consultarde(){
    $db=new Entity();
    $perfil=$db->findPerfil(get_id_usuario(),get_user_multiple());
    
    $perfil_empresa = $db->findEmpresaAll();

    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_estado=(isset($_GET['s']) ? $_GET['s'] : "start");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : "");
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : "");

    if(empty($id_anio)){
        $id_anio=date('Y');
    }
    if(empty($id_mes)){
        $id_mes="0";
    }

    $filter=array(
        'rol'       =>  'N3',
        'id_usuario'=> get_id_usuario(),
        'consultarde'   => true
    );
    if(!empty($id_anio)){
        if(is_numeric($id_anio)){
            $filter['f_id_anio']=$id_anio;
        }
    }
    if(!empty($id_mes)){
        if(is_numeric($id_mes)){
            $filter['f_id_mes']=$id_mes;
        }
    }
    if(!empty($id_empresa)){
        if(is_numeric($id_empresa)){
            $filter['f_id_empresa']=$id_empresa;
        }
    }
    if(!empty($id_cc)){
        if(is_numeric($id_cc)){
            $filter['f_id_cc']=$id_cc;
        }
    }
    if(!empty($id_estado)){
        $filter['f_id_estado']=$id_estado;
    }
    
    //Paginador de eltos
    $contador = $db->findSolicitudListCount__($filter); //contador total de items de la consulta sin paginador
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta
    $paginador = paginar($contador, $total_elto);
    
    if($paginador['paginar']){
        $filter['pag']=$paginador['pagina_actual'];
    }
    $data=$db->findSolicitudList($filter);

    require_once view('consultarde');
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
function get_controller(){
    return GET('c');
}
function get_action(){
    return (!(GET('a')==null || GET('a')=='') ? GET('a') : 'index');
}
function consultarde_cc(){
    $db=new Entity2();
    $perfil=$db->findPerfil(get_id_usuario(),get_user_multiple());

    $perfil_empresa = $db->findEmpresaAll();

    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_estado=(isset($_GET['s']) ? $_GET['s'] : "start");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : "");
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : "");

    if(empty($id_anio)){
        $id_anio=date('Y');
    }
    if($id_mes==""){
        $id_mes="0";
    }

    $filter=array(
        'rol'       =>  'N3',
        'id_usuario'=> get_id_usuario(),
        'consultarde'   => true
    );
    if(!empty($id_anio)){
        if(is_numeric($id_anio)){
            $filter['f_id_anio']=$id_anio;
        }
    }
    if(!empty($id_mes)){
        if(is_numeric($id_mes)){
            $filter['f_id_mes']=$id_mes;
        }
    }
    if(!empty($id_empresa)){
        if(is_numeric($id_empresa)){
            $filter['f_id_empresa']=$id_empresa;
        }
    }
    if(!empty($id_cc)){
        if(is_numeric($id_cc)){
            $filter['f_id_cc']=$id_cc;
        }
    }
    if(!empty($id_estado)){
        $filter['f_id_estado']=$id_estado;
    }
    
    //Paginador de eltos
    $contador = $db->findSolicitudListCount__($filter); //contador total de items de la consulta sin paginador

    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta
    $paginador = paginar($contador, $total_elto);
    
    if($paginador['paginar']){
        $filter['pag']=$paginador['pagina_actual'];
    }
    $data=$db->findSolicitudList($filter);
    require_once view('consultarde');
}
function formatDateNumber($date){
    if(strlen($date)==10){
        return substr($date,6,4).substr($date,3,2).substr($date,0,2);
    }
    return $date;
}
function reporte_request(){

    $arr=array();
    if (IS_POST()){
        $db=new Entity();
        $filter=array();
        $incluir_desistido = (isset($POST['desistidas']) ? ($POST['desistidas']=='1' ? true : false) : false);
        if($incluir_desistido){
            $filter[':incluir_desistida']=true;
        }
        if(POST('id_empresa')!='0'){
            $filter[':id_empresa']=(int)POST('id_empresa');
        }
        $filter[':fecha_inicial']=formatDateNumber(POST('fecha_inicial'));
        $filter[':fecha_final']=formatDateNumber(POST('fecha_final'));
        $data=$db->findSolicitudReportCount($filter);
        if(!empty($data)){
            $arr=array(
                'exito' => true
            );
        }else{
            $arr=array(
                'exito' => false,
                'msj'   => 'No existen datos disponibles para exportar.'
            );
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}

function reporte_request_excel(){
    $arr=array();
    $db=new Entity();
    $filter=array();
    $incluir_desistido = ((int)GET('desistida')==1 ? true : false);
    if($incluir_desistido){
        $filter[':incluir_desistida']=true;
    }
    if(GET('id_empresa')!='0'){
        $filter[':id_empresa']=(int)GET('id_empresa');
    }
    $filter[':fecha_inicial']=(int)GET('fecha_inicial');
    $filter[':fecha_final']=(int)GET('fecha_final');
    $data=$db->findSolicitudReport($filter);
    $arr = $data;
    echo json_encode($arr);
}
function reporte_request_excelcc(){
    $arr=array();
    $db=new Entity();
    $filter=array();
    $incluir_desistido = ((int)GET('desistida')==1 ? true : false);
    if($incluir_desistido){
        $filter[':incluir_desistida']=true;
    }
    if(GET('id_empresa')!='0'){
        $filter[':id_empresa']=(int)GET('id_empresa');
    }
    $filter[':fecha_inicial']=(int)GET('fecha_inicial');
    $filter[':fecha_final']=(int)GET('fecha_final');
    $data=$db->findSolicitudReportCC($filter);
    $arr = $data;
    echo json_encode($arr);
}
function reporte_request_excelcc_v2(){
    $arr=array();
    $db=new Entity();
    $filter=array();
    $incluir_desistido = ((int)GET('desistida')==1 ? true : false);
    if($incluir_desistido){
        $filter[':incluir_desistida']=true;
    }
    if(GET('id_empresa')!='0'){
        $filter[':id_empresa']=(int)GET('id_empresa');
    }
    $filter[':fecha_inicial']=(int)GET('fecha_inicial');
    $filter[':fecha_final']=(int)GET('fecha_final');
    $data=$db->findSolicitudReportCC_v2($filter);
    $arr = $data;
    echo json_encode($arr);
}
function get_user_multiple(){
    return (isset($_SESSION['N2N4']) ? (!empty($_SESSION['N2N4']) ? is_bool($_SESSION['N2N4']) : FALSE) : FALSE);
}
function consultar_autorizar(){
    $db=new Entity();
    $perfil=$db->findPerfil(get_id_usuario());
    $rolini=$perfil->rol;
    if(get_user_multiple()){ //usuario tiene 2 roles
        $db->setRolUsuario('N2');
        $perfil->rol='N2';
    }

    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_estado=(isset($_GET['s']) ? $_GET['s'] : "start");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : "");
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : "");

    if(empty($id_anio)){
        $id_anio=date('Y');
    }
    if($id_mes==""){
        $id_mes="0";
    }

    /*$filter=array(
        'rol'           =>  $perfil->rol,
        'id_usuario_rol'=>  get_id_usuario(),
        'status'        =>  'R',    //Recibido
        'avance'        =>  $perfil->rol
    );*/

    $filter=array(
        'rol'           =>  $perfil->rol,
        'id_usuario_rol'=>  get_id_usuario(),
        'autorizas_cc'  => true
    );

    if(!empty($id_anio)){
        if(is_numeric($id_anio)){
            $filter['f_id_anio']=$id_anio;
        }
    }
    if(!empty($id_mes)){
        if(is_numeric($id_mes)){
            $filter['f_id_mes']=$id_mes;
        }
    }
    if(!empty($id_empresa)){
        if(is_numeric($id_empresa)){
            $filter['f_id_empresa']=$id_empresa;
        }
    }
    if(!empty($id_cc)){
        if(is_numeric($id_cc)){
            $filter['f_id_cc']=$id_cc;
        }
    }
    if(!empty($id_estado)){
        $filter['f_id_estado']=$id_estado;
    }
    
    //Paginador de eltos
    $contador = $db->findSolicitudListCount($filter); //contador total de items de la consulta sin paginador
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta
    $paginador = paginar($contador, $total_elto);
    
    if($paginador['paginar']){
        $filter['pag']=$paginador['pagina_actual'];
    }
    
    $data=$db->findSolicitudList($filter);
    require_once view('consultar_autorizar');
}
function consultar_impresion(){
    $db=new Entity();
    $perfil=$db->findPerfil(get_id_usuario());

    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_estado=(isset($_GET['s']) ? $_GET['s'] : "");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : "");
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : date('m'));

    if(empty($id_anio)){
        $id_anio=date('Y');
    }
    if($id_mes==""){
        $id_mes="0";
    }

    $filter=array(
        'rol'           =>  $perfil->rol,
        'id_usuario_rol'=>  get_id_usuario(),
        'avance'        =>  $perfil->rol
    );
    
    if($perfil->rol!='N1'){
        $filter['id_usuario_rol']=get_id_usuario();
    }
    if(!empty($id_anio)){
        if(is_numeric($id_anio)){
            $filter['f_id_anio']=$id_anio;
        }
    }
    if(!empty($id_mes)){
        if(is_numeric($id_mes)){
            $filter['f_id_mes']=$id_mes;
        }
    }
    if(!empty($id_empresa)){
        if(is_numeric($id_empresa)){
            $filter['f_id_empresa']=$id_empresa;
        }
    }
    if(!empty($id_cc)){
        if(is_numeric($id_cc)){
            $filter['f_id_cc']=$id_cc;
        }
    }
    if(!empty($id_estado)){
        $filter['f_id_estado']=$id_estado;
    }
    //Paginador de eltos
    $contador = $db->findSolicitudListCount($filter); //contador total de items de la consulta sin paginador
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta
    $paginador = paginar($contador, $total_elto);
    
    if($paginador['paginar']){
        $filter['pag']=$paginador['pagina_actual'];
    }
    
    $data=$db->findSolicitudList($filter);
    require_once view('consultar_impresion');
}
function consultar_impresion_export(){
    $db=new Entity();
    $perfil=$db->findPerfilN3OrN5Email(GET('iu'));
    $filter=array(
        'rol'           =>  $perfil->rol,
        'avance'        =>  $perfil->rol
    );
    
    //Paginador de eltos
    $contador = $db->findSolicitudListCount($filter); //contador total de items de la consulta sin paginador
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta
    $paginador = paginar($contador, $total_elto);
    
    if($paginador['paginar']){
        $filter['pag']=$paginador['pagina_actual'];
    }
    
    $data=$db->findSolicitudList($filter);
    echo json_encode($data);
}
function admin(){
    $db=new Entity;
    $data=$db->admin_user();
    require_once view('admin');
}
function admindet(){
    $db=new Entity;
    $data=$db->admin_user_id(GET('id'));
    $data['empresa_all'] = $db->admin_empresas();
    require_once view('admindet');
}
function createuser(){
    $db=new Entity;
    $data = array();
    $data['empresa_all'] = $db->admin_empresas();
    require_once view('createuser');
}
function quitar_empresa_id(){
    $arr=array();
    if(IS_POST()){
        $db = new Entity;
        $result = $db->admin_borrar_empresa_id(POST('id'));
        if(!empty($result)){
            $arr=array(
                'exito' => TRUE
            );
        }else{
            $arr=array(
                'exito' => false,
                'msj'   => 'No es posible quitar empresa a usuario.'
            );
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function add_empresa_id(){
    $arr=array();
    if(IS_POST()){
        $db = new Entity;
        $result = $db->admin_agregar_empresa(POST('id_usuario'),POST('id_empresa'));
        if(!empty($result)){
            $arr=array(
                'exito' => TRUE
            );
        }else{
            $arr=array(
                'exito' => false,
                'msj'   => 'No es posible agregar empresa a usuario.'
            );
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function guardar_usuario(){
    $arr=array();
    if(IS_POST()){
        $db = new Entity;
        $result = $db->admin_agregar_usuario(POST('id_usuario'),POST('usuario'),POST('nombre'),POST('correo'),POST('rol'));
        if(!empty($result)){
            $arr=array(
                'exito' => TRUE
            );
        }else{
            $arr=array(
                'exito' => false,
                'msj'   => 'No es posible actualizar usuario.'
            );
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function agregar_usuario(){
    $arr=array();
    if(IS_POST()){
        $db = new Entity;
        $result = $db->admin_existe_usuario(POST('usuario'));
        if(!empty($result)){
            $arr=array(
                    'exito' => false,
                    'msj'   => 'Error: usuario ya existe.'
                );
        }else{
            $result = $db->admin_crear_usuario(POST('usuario'),POST('password'),POST('nombre'),POST('correo'),POST('rol'));
            if(!empty($result)){
                $arr=array(
                    'exito' => TRUE,
                    'id'    => $result
                );
            }else{
                $arr=array(
                    'exito' => false,
                    'msj'   => 'No es posible agregar usuario.'
                );
            }
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function cambiar_password(){
    $arr=array();
    if(IS_POST()){
        $db = new Entity;
        $result = $db->admin_cambiar_pwd(POST('id_usuario'),POST('contra'));
        if(!empty($result)){
            $arr=array(
                'exito' => TRUE
            );
        }else{
            $arr=array(
                'exito' => false,
                'msj'   => 'No es posible actualizar contraseña de usuario.'
            );
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function borrar_usuario(){
    $arr=array();
    if(IS_POST()){
        $db = new Entity;
        $result = $db->admin_borrar_usuario(POST('id'));
        if(!empty($result)){
            $arr=array(
                'exito' => TRUE
            );
        }else{
            $arr=array(
                'exito' => false,
                'msj'   => 'No es posible borrar usuario.'
            );
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function consultar_revision(){
    $db=new Entity();
    $perfil=$db->findPerfil(get_id_usuario());

    $id_estado=(isset($_GET['s']) ? $_GET['s'] : "start");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : "");
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : "");

    if(empty($id_anio)){
        $id_anio=date('Y');
    }
    if($id_mes==""){
        $id_mes="0";
    }

    $filter=array(
        'rol'           =>  $perfil->rol,
        'id_usuario_rol'=>  get_id_usuario(),
        //'status'        =>  'R',    //Recibido
        //'avance'        =>  $perfil->rol,
        'id_categoria'  =>  $perfil->categoria[0]->id,
        'autorizar_categoria'   => true
    );

    if(!empty($id_anio)){
        if(is_numeric($id_anio)){
            $filter['f_id_anio']=$id_anio;
        }
    }
    if(!empty($id_mes)){
        if(is_numeric($id_mes)){
            $filter['f_id_mes']=$id_mes;
        }
    }
    if(!empty($id_estado)){
        $filter['f_id_estado']=$id_estado;
    }
    
    //Paginador de eltos
    $contador = $db->findSolicitudListCount($filter); //contador total de items de la consulta sin paginador
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta
    $paginador = paginar($contador, $total_elto);
    
    if($paginador['paginar']){
        $filter['pag']=$paginador['pagina_actual'];
    }
    
    $data=$db->findSolicitudList($filter);
    require_once view('consultar_revision');
}
function categorizar(){
    $db=new Entity();
    $perfil=$db->findPerfil(get_id_usuario());
    
    $id_empresa=(isset($_GET['e']) ? $_GET['e'] : "");
    $id_cc=(isset($_GET['cc']) ? $_GET['cc'] : "");
    $id_estado=(isset($_GET['s']) ? $_GET['s'] : "start");
    $id_anio=(isset($_GET['anio']) ? $_GET['anio'] : "");
    $id_mes=(isset($_GET['mes']) ? $_GET['mes'] : "");

    if(empty($id_anio)){
        $id_anio=date('Y');
    }
    if($id_mes==""){
        $id_mes="0";
    }

    $filter=array(
        'rol'           =>  $perfil->rol,
        'id_usuario_rol'=>  get_id_usuario(),
        'categorizar'   =>  true
        //'status'        =>  'R',    //Recibido
        //'avance'        =>  $perfil->rol
    );

    if(!empty($id_anio)){
        if(is_numeric($id_anio)){
            $filter['f_id_anio']=$id_anio;
        }
    }
    if(!empty($id_mes)){
        if(is_numeric($id_mes)){
            $filter['f_id_mes']=$id_mes;
        }
    }
    if(!empty($id_empresa)){
        if(is_numeric($id_empresa)){
            $filter['f_id_empresa']=$id_empresa;
        }
    }
    if(!empty($id_cc)){
        if(is_numeric($id_cc)){
            $filter['f_id_cc']=$id_cc;
        }
    }
    if(!empty($id_estado)){
        $filter['f_id_estado']=$id_estado;
    }

    //Paginador de eltos
    $contador = $db->findSolicitudListCount($filter); //contador total de items de la consulta sin paginador
    $total_elto = $db->get_page_elto(); //eltos con paginador por consulta
    $paginador = paginar($contador, $total_elto);
    
    if($paginador['paginar']){
        $filter['pag']=$paginador['pagina_actual'];
    }
    
    $data=$db->findSolicitudList($filter);
    require_once view('categorizar');
}
function sendmail_cheque(){
    if (IS_GET()){
        $db=new Entity();
        $status=GET('status'); //niveles de autorizaci�n
        echo "\n Estatus: ".$status."\n";
        $perfil=$db->findPerfilEmail(GET('id_user'),$status);
        echo var_dump($perfil);
        $db->setPermisos($perfil->id,$status,$perfil->nombre);
        $email=null;
        $state=GET('state'); //send: aprobado, fail: desistido
        if($state=='send'){
            $filtro = array();
            if($status=='N1'){
                $filtro['id_solicitud']=GET('id');
            }else if($status=='N2'){
                $filtro['id_empresa']=GET('id_empresa');
                $filtro['id_empresa']=GET('id_empresa');
            }else if($status=='N3'){
                $filtro['id_categoria']=GET('id_categoria');
            }else if($status=='N4'){
                $filtro['id_empresa']=GET('id_empresa');
            }
            $email = $db->to_sendmail_ok($filtro);
            echo var_dump($email);
        }else if($state=='fail'){
            $email = $db->to_sendmail_fail(array( 'id_solicitud' => GET('id')));
        }
        if(!empty($email)){
        	require_once dirname(__FILE__).'/../model/cheque/EmailCheque.php';
            $cl = new EmailCheque;
            $cl->notificar_solicitud($email,$state,$status,GET('id'),$perfil);
        }
    }
}
function sendmail_cheque5k(){
    if (IS_GET()){
        $db=new Entity();
        $status=GET('status'); //niveles de autorizaci�n
        $perfil=$db->findPerfilEmail(GET('id_user'),$status);
        echo var_dump($perfil);
        $db->setPermisos($perfil->id,$status,$perfil->nombre);
        $email=null;
        $state=GET('state'); //send: aprobado, fail: desistido
        if($state=='send'){
            $filtro = array();
            $filtro['id_solicitud']=GET('id');
            $filtro['id_empresa']=GET('id_empresa');
            $email = $db->to_sendmail_5k_ok($filtro);
            echo var_dump($email);
        }else if($state=='fail'){
            $email = $db->to_sendmail_fail(array( 'id_solicitud' => GET('id')));
        }
        if(!empty($email)){
            require_once dirname(__FILE__).'/../model/cheque/EmailCheque.php';
            $cl = new EmailCheque;
            $cl->notificar_solicitud_5k($email,$state,$status,GET('id'),$perfil);
        }
    }
}
function agregar_cheque(){
    $arr=array();
    if (IS_POST()){
        $db=new Entity();
        $create=$db->create_solicitud($_POST,$_FILES['file']);
        if(is_array($create)){
            $arr=$create;
        }else{
            $arr=array(
                'exito' => false,
                'msj'   => 'No es posible realizar peticion.'
            );
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Peticion incorrecta.'
        );
    }
    echo json_encode($arr);
}
function editar_cheque(){
    $arr=array();
    if (IS_POST()){
        $db=new Entity();
        $create=$db->save_solicitud($_POST,$_FILES['file']);
        if(is_array($create)){
            $arr=$create;
        }else{
            $arr=array(
                'exito' => false,
                'msj'   => 'No es posible realizar petici�n.'
            );
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petici�n incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_solicitud_send_n1(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->send_solicitud(array(
            'id'        =>  POST('id'),
            'avance'    =>  'N2'
        ));
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible enviar solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Peticion incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_solicitud_autorizar(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->autorizar_solicitud($_POST);
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible autorizar solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Peticion incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_solicitud_devolver(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->devolver_solicitud($_POST);
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible devolver solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_solicitud_autorizarde(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->autorizarde_solicitud($_POST);
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible autorizar solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Peticion incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_email_send(){
    $arr=array();
    if(IS_GET()){
        $id_solicitud=GET('id');
        $db = new Entity();
        $correo=$db->info_correo($id_solicitud);
        //$db->depurar($correo);
        //echo "\nEnviando correo: \n";

        require_once dirname(__FILE__).'/../model/cheque/EmailCheque.php';
        $cl = new EmailCheque;
        $cl->send_email($correo);
    }
    return $arr;
}
function json_email_send_x(){
    $arr=array();
    if(IS_GET()){
        $id_solicitud=GET('id');
        $db = new Entity();
        $correo=$db->info_correo($id_solicitud);
        $db->depurar($correo);
        echo "\nEnviando correo: \n";



        require_once dirname(__FILE__).'/../model/cheque/EmailCheque.php';
        $cl = new EmailCheque;
        $cl->send_email($correo);
    }
    return $arr;
}
function json_solicitud_categorizar(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->categorizar_solicitud($_POST);
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible categorizar solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Petici�n incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_solicitud_desistir(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->desistir_solicitud($_POST);
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible desistir solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Petici�n incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_solicitud_desistirde(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->desistirde_solicitud($_POST);
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible desistir solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Petici�n incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_solicitud_send_n2(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->send_solicitud(array(
            'id'        =>  POST('id'),
            'avance'    =>  'N3'
        ));
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible enviar solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Petici�n incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_solicitud_borrar(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->borrar_solicitud(POST('id'));
        if($result){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible borrar solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Petici�n incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_cc_empresa(){
    $arr=array();
    if (IS_POST()){
        $db=new Entity();
        $data = $db->findEmpresaCC($_POST['id']);
        if($data!=null){
            $arr=array(
                'exito' => true,
                'cc'    => $data
            );
        }else{
            $arr=array(
                'exito' => false, 
                'msj' => 'Empresa no tiene Centro de Costo asignados.'
            );
        }
    }else{
        $arr=array(
            'exito' => false, 
            'msj' => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_productos(){
    $arr=array();
    if (IS_POST()){
        $db=new Entity();
        $data = $db->findProductos($_POST['id'],$_POST['buscar']);
        if($data!=null){
            foreach ($data as $det) {
                $p = array();
                array_push($p, $det->prod_codigo);
                array_push($p, $det->prod_descripcion);
                array_push($p, $det->sl_linea);
                array_push($p, $det->sl_sublinea);
                array_push($p, $det->gas_tit_codigo);
                array_push($p, $det->gas_det_codigo);

                $det->prod = base64_encode(serialize($p));

            }
            $arr=array(
                'exito' => true,
                'cc'    => $data
            );
        }else{
            $arr=array(
                'exito' => false, 
                'msj' => 'Empresa no tiene Centro de Costo asignados.'
            );
        }
    }else{
        $arr=array(
            'exito' => false, 
            'msj' => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_cc_empresa_all(){
    $arr=array();
    if (IS_POST()){
        $db=new Entity();
        $data = $db->findEmpresaCCAll($_POST['id']);
        if($data!=null){
            $arr=array(
                'exito' => true,
                'cc'    => $data
            );
        }else{
            $arr=array(
                'exito' => false, 
                'msj' => 'Empresa no tiene Centro de Costo asignados.'
            );
        }
    }else{
        $arr=array(
            'exito' => false, 
            'msj' => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_cc_solicitud(){
    $arr=array();
    if (IS_POST()){
        $db=new Entity();
        $data = $db->findSolicitud($_POST['id']);
        if($data!=null){
            $arr=array(
                'exito' => true,
                'data'    => $data
            );
        }else{
            $arr=array(
                'exito' => false
            );
        }
    }else{
        $arr=array(
            'exito' => false, 
            'msj' => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}

function VerS(){
    require_once 'model/SQLgenerales.php';
    require_once 'model/solcModel.php';
    $conf = Configuracion::getInstance();
    $estados = $conf->getEstadosSC();   //Contiene el listado de los estados de la solicitud    
    $permisos = permisosURL();          //Contiene el listado de los permisos
    $emps = listarEmp();        //Contiene el listado de las Empresas

    $key_sess = $_SESSION['u'].$_GET['id'].$_GET['c'].$_GET['a'];

    if(!isset($_GET['page'])) {
        $page=1;
        unset($_SESSION[$key_sess]) ;
        $_SESSION[$key_sess] = '';
    } else {
        $page=$_GET['page'];
    }
    if($_POST) {
        $_SESSION[$key_sess] = $_POST;
    }

    $solcs = ConsultaCheque($page, $_SESSION[$key_sess],$_SESSION['u']);
   

    require_once 'view/solcheque/ver_solc.php';
}

function json_buscar_proveedor(){
    $arr=array();
    if (IS_POST()){
        $db=new Entity();
        $data = $db->findProveedor($_POST['id_empresa'],$_POST['proveedor']);
        if($data!=null){
            $arr=array(
                'exito' => true,
                'proveedores'    => $data
            );
        }else{
            $arr=array(
                'exito' => false, 
                'msj' => 'No se ha encontrado proveedor.'
            );
        }
    }else{
        $arr=array(
            'exito' => false, 
            'msj' => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}


function json_categoria_cc(){
    $arr=array();
    if (IS_POST()){
        $db=new Entity();

        $id_empresa = (isset($_POST['id_empresa']) ? $_POST['id_empresa'] : 0);
        $id_cc = (isset($_POST['id_cc']) ? $_POST['id_cc'] : 0);
        $category=$db->findCategoriasUsuario_cc("Solicitud de cheque",$id_empresa,$id_cc); //

        if(!empty($category)){
            $arr=$category;
        }else{
            $arr=array();
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function json_flujo_cheque_pdf(){
    $arr=array();
    if(GET('s')!=null){
        $db=new Entity();

        $s = $db->findSolicitudDetail(GET('s'));
        if(!empty($s)){
            $id_empresa = $s->id_empresa;
            $id_cc = $s->id_cc;
            $monto = $s->valor_cheque;
            $id_categoria = $s->id_categoria_gasto;
            $id_usuario = $s->id_usuario;

            $arr=$db->flujo_aprobacion_user_cc_pdf($id_empresa,$id_cc,$monto,$id_categoria,$id_usuario);
        }
        echo json_encode($arr);
    }else{
        echo json_encode($arr);
    }
}
function json_flujo_cheque(){
    $arr=array();
    if (IS_POST()){
        $db=new Entity();

        $id_empresa = (isset($_POST['id_empresa']) ? $_POST['id_empresa'] : 0);
        $id_cc = (isset($_POST['id_cc']) ? $_POST['id_cc'] : 0);
        $monto = (isset($_POST['monto']) ? $_POST['monto'] : 0);
        $id_categoria = (isset($_POST['id_categoria']) ? $_POST['id_categoria'] : 0);
        $id_usuario = (isset($_POST['id_usuario']) ? $_POST['id_usuario'] : 0);
        $moneda = (isset($_POST['moneda']) ? $_POST['moneda'] : "$");
        $gerencia = (isset($_POST['gerencia']) ? $_POST['gerencia'] : "0");

        $create=$db->flujo_aprobacion_user_cc($id_empresa,$id_cc,$monto,$id_categoria,$id_usuario,$moneda,$gerencia);
        if(is_array($create)){
            $arr=$create;
        }else{
            $arr=array(
                'exito' => false
            );
        }
    }else{
        $arr=array(
            'exito' => false,
            'msj'   => 'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}
function recepcionar(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $solc=$db->findSolicitud(GET('s'));
        require_once view('recepcionar');
    }
}
function json_recepcionar(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->recepcionar_solicitud($_POST);
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible recepcionar solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}

function montos(){
    if(GET('s')!=null){
        $db=new Entity();
        $perfil=$db->findPerfil(get_id_usuario()); //
        $solc=$db->findSolicitud(GET('s'));
        require_once view('montos');
    }
}
function json_solicitud_monto(){
    $arr=array();
    if(IS_POST()){
        $db=new Entity();
        $result=$db->monto_solicitud($_POST);
        if(!empty($result)){
            $arr=array(
                'exito' =>  true
            );
        }else{
            $arr=array(
                'exito' =>  false,
                'msj'   =>  'No es posible actualizar solicitud'
            );
        }
    }else{
        $arr=array(
            'exito' =>  false,
            'msj'   =>  'Petición incorrecta.'
        );
    }
    echo json_encode($arr);
}