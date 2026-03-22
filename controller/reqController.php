<?php
session_start();
/*
 * Inicio de solicitudes
 */
function inicio(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$ccs = listarCcUsuario();
	$reqs = listarReqs();
	$estado = 'hidden';
	$mensaje = '';
	if(!empty($_GET['msg']) && $_GET['msg'] == 'ER001'){
		$estado = '';
		$mensaje = 'No se ha podido enviar la requisicion para autorizacion, informe al administrador en Proveeduria.';
	}
	require_once 'view/req/listado.php';
}
/*
 * Nueva solicitud de compra
 */
function crear(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$permisos = permisosURL();
	if ($_POST) {
		$prereq = creaPreReq($_POST);
		if (!is_numeric($prereq)){
			echo '<div class="alert alert-error">';
			echo $prereq;
			echo '</div>';
			die();
		} else {
			$form = $_POST;
			$url = 'Location: ?c='.$form['c'].'&a='.$form['a'].'&id='.$form['idmod'].'&ps='.$prereq.'&cs='.$form['centrocosto'].'&es='.$form['empresa'];
			$_POST = array();
			header($url);
			die();
		}
	}
	$infohreq = infoPreHReq($_GET['ps'],$_GET['cs'],$_GET['es']);
	$detas = detPreDReq($infohreq[0]['id_prehreq'],$infohreq[0]['id_cc'],$infohreq[0]['id_empresa']);
	$prods = listaProdsReq();

	// Filtra productos de empresas NI/HN
	// Solicitado por Gcia. Sistemas => Fernando Molina, en coordinación con Proveeduría (Helen Vega)
	// 12/Jul/2023
	// Prog. Alexis Ayala.

	if($prods!=null){
		$contador = count($prods);
		$id_empresa = $_GET['es'];
		if($id_empresa=='6' || $id_empresa=='8'){
			$nempresa ='HN ';
			if($id_empresa=='8') $nempresa='NI ';
			for ($i=0; $i < $contador; $i++) { 
				$codigo = strtoupper(trim($prods[$i]['prod_codigo']));
				if(strlen($codigo)>2){
					if(substr($codigo,0,3)!=$nempresa){
						unset($prods[$i]); //se quita producto	
					}
				}else{
					unset($prods[$i]); //se quita producto
				}
			}
		}
	}
	
	//print_r($prods);
	require_once 'view/req/nueva.php';
}
/*
 * Borrar requisicion
*/
function borrar(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Delete From ".$conf->getTbl_prehreq()
		." Where id_prehreq = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	$sql = "Delete From ".$conf->getTbl_predreq()
	." Where id_prehreq = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	$sql = "Delete From ".$conf->getTbl_prehreq_stat()
	." Where id_prehreq = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	echo '<div class="bs-calltoaction bs-calltoaction-success">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">Se ha eliminado la solicitud!</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=req&a=inicio&id=6" class="btn btn-lg btn-block btn-success">Regresar</a>
                        </div>
                     </div>
                </div>';
}
/*
 * Enviar a Autorizar
*/
function auto(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Select count(id_prehreq) as total From ".$conf->getTbl_predreq()." Where id_prehreq = ".$_REQUEST["ps"];
	$run = $db->ejecutar($sql);
	$row = mysqli_fetch_array($run);
	if($row[0] > 0) {
		
		$sql_user = "Select a.id_usuario, u.usr_nombre, u.usr_email, u.usr_ges From acc_emp_cc a"
				." Join usuario u"
				." On u.id_usuario = a.id_usuario"
				." Where a.id_empresa = ".$_REQUEST["es"]
				." and a.id_cc = ".$_REQUEST["cs"]
				." and u.id_rol = 999999995";
		$run_user = $db->ejecutar($sql_user);
		$vacios = '1'; 
		while ($row_user = mysqli_fetch_array($run_user)){
			$row_user[2] = trim($row_user[2]);
			if(!empty($row_user[2]) && $row_user[3] <> '1') {
				$vacios = '0';
			}
		}
		if($vacios == '0') {
			$sql = "Update ".$conf->getTbl_prehreq()
				." Set prehreq_estado = 1"
				." Where id_prehreq = ".$_REQUEST["ps"];
			$db->ejecutar($sql);
			$sql = "Update ".$conf->getTbl_predreq()
				." Set predreq_estado = 1"
				." Where id_prehreq = ".$_REQUEST["ps"];
			$db->ejecutar($sql);
			// Ponemos el estado de creado
			$sql_st = "Insert Into ".$conf->getTbl_prehreq_stat()." Set "
				."id_prehreq = ".$_REQUEST['ps'].", "
				."prehreq_stat = 1, "
				."prehreq_stat_desc = '".$conf->getEstado('1')."', "
				."prehreq_stat_fecha = '".date("Y-m-d")."', "
				."prehreq_stat_hora = '".date("H:i:s")."', "
				."prehreq_stat_usuario = '".$_SESSION['u']."'";
			$db->ejecutar($sql_st);
			echo '<div class="bs-calltoaction bs-calltoaction-primary">
		                    <div class="row">
		                        <div class="col-md-9 cta-contents">
		                            <h1 class="cta-title">Solicitud enviada con exito!</h1>
		                            <div class="cta-desc">
		                                <p></p>
		                            </div>
		                        </div>
		                        <div class="col-md-3 cta-button">
		                            <a href="?c=req&a=inicio&id=6" class="btn btn-lg btn-block btn-primary">Regresar</a>
		                        </div>
		                     </div>
		                </div>';
			header('Location: /sics/class/PHPMailer/sendreq.php?'.$_REQUEST['ps']);
			die();
		} else {
			echo '<div class="bs-calltoaction bs-calltoaction-danger">
	                    <div class="row">
	                        <div class="col-md-9 cta-contents">
	                            <h1 class="cta-title">Lo sentimos, ha ocurrido un error!</h1>
	                            <div class="cta-desc">
	                                <h3>No se ha definido un correo electronico para el usuario autorizador, contactar al administrador en departamento de compras.</h3>
	                            </div>
	                        </div>
	                        <div class="col-md-3 cta-button">
	                            <a href="?c=req&a=inicio&id=6" class="btn btn-lg btn-block btn-danger">Regresar</a>
	                        </div>
	                     </div>
	                </div>';
			header('Location: /sics/class/PHPMailer/sendreqnoauto.php?'.$_REQUEST['ps']);
			die();
		}
	} else {
		echo '<div class="bs-calltoaction bs-calltoaction-danger">
	                    <div class="row">
	                        <div class="col-md-9 cta-contents">
	                            <h1 class="cta-title">Lo sentimos, ha ocurrido un error!</h1>
	                            <div class="cta-desc">
	                                <h3>La solicitud que desea enviar para autorizacion esta vacia.</h3>
	                            </div>
	                        </div>
	                        <div class="col-md-3 cta-button">
	                            <a href="?c=req&a=inicio&id=6" class="btn btn-lg btn-block btn-danger">Regresar</a>
	                        </div>
	                     </div>
	                </div>';
	}
}
/*
 * Muestra detalle de solicitud para autorizar
 */
function autorizar(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$infohsol = infoPreHReq($_GET['ps'],$_GET['cs'],$_GET['es']);
	$detas = detPreDReq($infohsol[0]['id_prehreq'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa']);
	include_once 'view/req/autorizar.php';
}
/*
 * listarCcUsuario
 */
function listarCcUsuarioC(){
	require_once 'model/reqModel.php';
	echo json_encode(listarCcUsuario());
}
function listarCcUsuarioCCat(){
	require_once 'model/reqModel.php';
	echo json_encode(listarCcUsuarioCat());
}
/*
 *
 */
function autoriza(){
	require_once 'model/reqModel.php';
	$emps = listarEmpUsuario();
	if(isset($_GET['es']) && $_GET['es'] != ""){
		$solcs = listarReqsAuth($_GET['es']);
	} else {
		$solcs = array();
	}
	include_once('view/req/autoriza.php');
}
/*
 *
 *
 */
function listarProds(){
	require_once 'model/reqModel.php';
	$prods = listaProdsReq();
	echo json_encode($prods);
	/*foreach ($prods as $prod){
		echo json_encode($prod);
	}*/
}
/*
 * Consulta de requisiones por emisor
 */
function emisor(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstados();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
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
	$reqs = Consulta($page, $_SESSION[$key_sess]);
	require_once 'view/req/consulta.php';
}

/*
 * Recolectar requisiciones
 */
function colectar(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstados();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	if ($_POST) {
		$colectas = listasColectar($_POST['empresa'],$_POST['inicio'],$_POST['fin']);
	}
	require 'view/req/colectar.php';
}
/*
 * Listas Trabajar item x item
 */
function tracole(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$estados = $conf->getEstados();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	if($_POST){
		$trabajar = listasTrabajar($_POST['empresa']);
		$infoEmpresa = datosCia($_POST['empresa']);
	}
	// incluimos la vista
	require 'view/req/tracole.php';
}
/*
 * Listas Trabajar x item y proveedor
*/
function tracolecons(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance (); 
	$estados = $conf->getEstados();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	if($_POST){
		/*
		 * Si se esta llamando con la accion de asignar el nvo precio
		 * hacemos el cambio antes de llenar el listado
		 */
		if($_REQUEST['accion'] == "asignar_nvo_prv") { 
			list($nvo_prec, $nvo_prov) = explode("~", $_REQUEST['nvo_precio']);
			$append_sql = '';
			if($_REQUEST['prov_anterior'] <> $nvo_prov) {
				$append_sql = ",predreq_preoc = 0, predreq_numero_oc=NULL, predreq_fecha_oc=0, predreq_hora_oc=0, predreq_usuario_oc=''";
			}
			$sql = "Update ".$conf->getTbl_predreq()." Set"
				." predreq_prec_uni = ".$nvo_prec.","
				." predreq_total = Round((predreq_cantidad_aut * predreq_prec_uni),2),"
				." id_proveedor = ".$nvo_prov
				.$append_sql
				." Where prod_codigo = '".$_REQUEST['codigo']."'"
				." And id_proveedor = ".$_REQUEST['prov_anterior']
				." And predreq_fecha_col='".$_REQUEST['fecha_col']."'";
			//echo $sql;
			$db->ejecutar($sql);
			$_REQUEST['accion'] = '';
		}
		/* Obtnemos los datos que estan listos para trabajar
		 * acumulados por codigo y proveedor */
		$trabajar = listasTrabajarConsolidado($_POST['empresa']);
		$infoEmpresa = datosCia($_POST['empresa']);
	}
	// incluimos la vista
	require 'view/req/tracolecons.php';
}
/*
 * crear oc, todos los items de requisiciones trabajadas
 */
function crearoc() {
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$emps = listarEmpUsuario();
	if($_POST){
		$trabajar = listasOC($_POST['empresa']);
		$infoEmpresa = datosCia($_POST['empresa']);
	}
	require_once 'view/req/crearoc.php';
}
/*
 * crear nr, todos los items de requisiciones trabajadas e ingresadas
*/
function crearnr() {
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$emps = listarEmpUsuario();
	if($_POST){
		$trabajar = listasNR($_POST['empresa']);
		$infoEmpresa = datosCia($_POST['empresa']);
	}
	require_once 'view/req/crearnr.php';
}
/*
 * Crear Preorden
 */
function CreaPreOc() {
	require_once 'model/SQLgenerales.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$form = $_POST['form']['items'];
	$empresa = $_POST['form']['empresa'][0];
	$proveedor = $_POST['form']['proveedor'][0];
	$infE = datosCia($empresa);
	$infP = datosProveedor($proveedor);
	$fecha_hoy =  date('Y-m-d');
	$hora_hoy = date('H:i:s');
	$sql = "Select prov_nvocod From proveedor Where "
			."id_proveedor = ".$proveedor;
	//die($sql);
	try {
		$run = $db->ejecutar($sql);
	} catch(Exception $e) {
		die($e->getMessage());
	}
	if(mysqli_num_rows($run) <= 0) {
		die('codigo de proveedor aun no ha sido definido');
	}
	$row = mysqli_fetch_array($run);
	$prov_oc = $row[0];
	/*   */
	$valor = ' ';
	if($infE[0]['emp_usa_presupuesto'] == 1) {
		$valor = '2';
	}
	try {
		// Nos conectamos a la base de datos de proveeduria O.C.
		$db->conecta_OC();
		/*
		 * Creamos primero en encabezado de la O.C.
		 */
		$sql = "Select NORDEN From empresas Where CODIGO = '".$infE[0]['id_empresa_oc']."'";
		$result = $db->ejecuta_OC($sql);
		$row = mysqli_fetch_array($result);
		// Numero de orden a crear
		$Numero = $row[0]+1;
		$orden = $Numero;
		// Actualizamos el correlativo para la empresa
		$_SqlO = "Update empresas Set NORDEN = '".$Numero."' Where CODIGO ='".$infE[0]['id_empresa_oc']."'";
		$db->ejecuta_OC($_SqlO);
		/*
		 * Esto es el detalle de la O.C.
		*/
		$i = 0;
		foreach($form as $field) {
			//$escMessage = mb_convert_encoding($field['descripcion'], "Windows-1252", "UTF-8");
			++$i;
			$detsOC = ""; 
			$detsOC = "Update ".$conf->getTbl_predreq()
				." Set predreq_numero_oc=".$orden.", "
				." predreq_fecha_oc='".$fecha_hoy."', "
				." predreq_hora_oc='".$hora_hoy."', "
				." predreq_usuario_oc='".$_SESSION['u']."', "
				." predreq_estado = 3,"
				." predreq_preoc = 1"
				." Where id_predreq = ".$field['id_predreq'];
			$db->ejecutar($detsOC);
			$heads[$i][] = "Select Count(predreq_estado) From ".$conf->getTbl_predreq()
				." Where id_prehreq = ".$field['id_prehreq']." And"
				." predreq_estado = 3";
			$heads[$i][] = "Update ".$conf->getTbl_prehreq()." Set"
				." prehreq_estado = 3"
				." Where id_prehreq = ".$field['id_prehreq'];
			$heads['stat'][0] = "Insert Into ".$conf->getTbl_prehreq_stat()." Set "
					."id_prehreq = ".$field['id_prehreq'].", "
					."prehreq_stat = 4, "
					."prehreq_stat_desc = '".$conf->getEstado('4')."', "
					."prehreq_stat_fecha = '".date("Y-m-d")."', "
					."prehreq_stat_hora = '".date("H:i:s")."', "
					."prehreq_stat_usuario = '".$_SESSION['u']."'";
		}
	
		$db->desconecta_OC();
		/*
		 * Marca la requisicion de enviada a O.C.
		 */
		/*foreach($dets as $qry) {
			$db->ejecutar($qry);
		}*/
		/*
		 * Marcamos el encabezado si ya se ha enviado todo el detalle a O.C.
		 */
		foreach ($heads as $head) {
			$run = $db->ejecutar($head[0]);
			$row = mysqli_fetch_array($run);
			if($row <= 0) {
				$db->ejecutar($head[1]);
			}
		}
		/*
		 * Ponemos el estado de en orden de compra
		*/
		$db->ejecutar($heads['stat'][0]);
		$res = $orden;
	} catch(Exception $e) {
		$res = $e->getMessage();
	}
	//$res = print_r($infE, true);
	echo $res;
}
/*
 * Crear nota de remision
 */
function CreaNR(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	// Datos de la requisicion
	$sql_datos_header = "Select * From ".$conf->getTbl_prehreq()
		." Where id_prehreq = ".$_REQUEST['id_prehreq'];
	$run_datos_header = $db->ejecutar($sql_datos_header);
	$row_datos_header = mysqli_fetch_array($run_datos_header);
	/*
	 * Ponemos el estado de enviado al solicitante
	 */
	$sql_st = "Insert Into ".$conf->getTbl_prehreq_stat()." Set "
		."id_prehreq = ".$_REQUEST['id_prehreq'].", "
		."prehreq_stat = 6, "
		."prehreq_stat_desc = '".$conf->getEstado('6')."', "
		."prehreq_stat_fecha = '".date("Y-m-d")."', "
		."prehreq_stat_hora = '".date("H:i:s")."', "
		."prehreq_stat_usuario = '".$_SESSION['u']."'";
	$db->ejecutar($sql_st);
	
	// Datos del Centro de Costo
	$sql_datos_cc = "Select cc_remision From ".$conf->getTbl_cecosto()
		." Where id_cc = ".$row_datos_header['id_cc'];
	$run_datos_cc = $db->ejecutar($sql_datos_cc);
	$row_datos_cc = mysqli_fetch_array($run_datos_cc);
	// Asignamos el nuevo numero
	$nota_numero = $row_datos_cc[0]+1;
	$res = $nota_numero;
	// Actualizamos el encabezado con los datos de la remision
	$sql_update_enca = "Update ".$conf->getTbl_prehreq()." Set"
		." prehreq_numero_remision = ".$nota_numero.", "
		." prehreq_fecha_remision = '".date('Y-m-d')."', "
		." prehreq_hora_remision = '".date('H:i:s')."', "
		." prehreq_usuario_remision = '".$_SESSION['u']."', "
		." prehreq_estado = 6"
		." Where id_prehreq = ".$_REQUEST['id_prehreq'];
	try {
		$db->ejecutar($sql_update_enca);
		// Actualizamos detalle
		$sql_update_deta = "Update ".$conf->getTbl_predreq()." Set"
			." predreq_estado = 6"
			." Where id_prehreq = ".$_REQUEST['id_prehreq'];
		try {
			$db->ejecutar($sql_update_deta);
			// Guardamos el correlativo de remisiones
			$sql_update_cc = "Update ".$conf->getTbl_cecosto()." Set"
				." cc_remision = ".$nota_numero
				." Where id_cc = ".$row_datos_header['id_cc'];
			try {
				$db->ejecutar($sql_update_cc);
			} catch (Exception $e2) {
				$res = $e2->getMessage();
			}	
		} catch (Exception $e1) {
			$res = $e1->getMessage();
		}
	} catch (Exception $e) {
		$res = $e->getMessage();
	}
	echo $res;
}
/*
 * Crea Orden de Compra
*/
function CreaOc() {
	require_once 'model/SQLgenerales.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$form = $_POST['form']['items'];
	$empresa = $_POST['form']['empresa'][0];
	$proveedor = $_POST['form']['proveedor'][0];
	$infE = datosCia($empresa);
	$infP = datosProveedor($proveedor);
	$fecha_hoy =  date('Y-m-d');
	$hora_hoy = date('H:i:s');
	$sql = "Select prov_nvocod From proveedor Where "
			."id_proveedor = ".$proveedor;
	try {
		$run = $db->ejecutar($sql);
	} catch(Exception $e) {
		die($e->getMessage());
	}
	if(mysqli_num_rows($run) <= 0) {
		die('codigo de proveedor aun no ha sido definido');
	}
	$row = mysqli_fetch_array($run);
	$prov_oc = $row[0];
	/*   */
	$valor = ' ';
	if($infE[0]['emp_usa_presupuesto'] == 1) {
		$valor = '2';
	}
	try {
		// Nos conectamos a la base de datos de proveeduria O.C.
		$db->conecta_OC();
		/*
		 * Creamos primero en encabezado de la O.C.
		*/
		$sql = "Select NORDEN From empresas Where CODIGO = '".$infE[0]['id_empresa_oc']."'";
		$result = $db->ejecuta_OC($sql);
		$row = mysqli_fetch_array($result);
		// Numero de orden a crear
		$Numero = $row[0]+1;
		$orden = $Numero;
		// Actualizamos el correlativo para la empresa
		$_SqlO = "Update empresas Set NORDEN = '".$Numero."' Where CODIGO ='".$infE[0]['id_empresa_oc']."'";
		$db->ejecuta_OC($_SqlO);
		/*
		 * Esto es el detalle de la O.C.
		*/
		$i = 0;
		foreach($form as $field) {
			//$escMessage = mb_convert_encoding($field['descripcion'], "Windows-1252", "UTF-8");
			++$i;
			$dets[] = "Update ".$conf->getTbl_predreq()
				." Set predreq_numero_oc=".$orden.", "
				." predreq_fecha_oc='".$fecha_hoy."', "
				." predreq_hora_oc='".$hora_hoy."', "
				." predreq_usuario_oc='".$_SESSION['u']."', "
				." predreq_estado = 4"
				." Where id_predreq = ".$field['id_predreq'];
			$heads[$i][] = "Select Count(predreq_estado) From ".$conf->getTbl_predreq()
				." Where id_prehreq = ".$field['id_prehreq']." And"
				." predreq_estado = 3";
			$heads[$i][] = "Update ".$conf->getTbl_prehreq()." Set"
				." prehreq_estado = 4"
				." Where id_prehreq = ".$field['id_prehreq'];
			$movs[] = "( "
				."'".$field['codigo']."', "
				."'".substr($field['gasto'],0,2)."', "
				."'".substr($field['gasto'],2,2)."', "
				.$field['cantidad'].", "
				."'".$field['unidades']."', "
				."'".$field['descripcion']."', "
				."'".$field['cc']."', "
				.$field['prec_uni'].", "
				.$infE[0]['cia_presupuesto'].", "
				."0, "
				."'".$valor."', "
				.$i.", "
				."'".$orden."', "
				."'".$prov_oc."', "
				."8.75, "
				."0.13, "
				.$field['total']
				." )";
		}
		$sql = "INSERT INTO ".$conf->getTmovs().$infE[0]['id_empresa_oc']." ( "
			."CODAS400 , "
			."DCTITG, "
			."DCDETG, "
			."CANT, "
			."UNIDADES, "
			."DESCRIP, "
			."CC, "
			."PRECUNIT_D, "
			."DCCIA, "
			."DCCUOTA, "
			."DCVALOR, "
			."IDORDEN, "
			."NORDEN, "
			."CODPROVEE, "
			."FACTORDOLA, "
			."FACTORIVA, "
			."TOTAL ) VALUES ".implode(',', $movs) ;
		// Escribimos el encabezado de la O.C.
		$_SqlOr = "Insert Into ".$conf->getTorden().$infE[0]['id_empresa_oc']." Set NORDEN = '"
			.$orden."', FECHAPED ='".date("d/m/Y")."', Fecha_YMD ='".date("Y/m/d")
			."', CCOSTO ='99', PEDIDOPOR = 'VARIOS CENTROS DE COSTO', US_CREO ='"
			.$_SESSION['u']."', OCTITG ='00', OCDETG ='00', OCCUOTA=0, OCGESTOR='SICS', OCSTAT='', ITEMS=".$i.", "
			."OCCIA ='".$infE[0]['cia_presupuesto']."', OCVALOR ='".$valor."', PRT = '0', ORIGEN='SICS', PREOC=1, CODPROVEE='".$prov_oc."', "
			."ATENCION='".$infP[0]['prov_contacto1']."', CONDIPAGO='".$infP[0]['prov_dias']."'";
		$db->ejecuta_OC($_SqlOr);
		// Observaciones
		$_SqlOrde = "Insert Into ".$conf->getTobser().$infE[0]['id_empresa_oc']." Set OBORDEN = '".$orden."', OBS = 'Entregar en : Km. 17 1/2 Carret. A Quezaltepeque, Cant�n Joya Galana Hda. El �ngel, Lot. Las Ventanas Pol. 2 #9 Fte. A bodegas de WalMart Apopa.'";
		$db->ejecuta_OC($_SqlOrde);
		// Llenamos el datalle
		$runsql = $db->ejecuta_OC($sql);
		$db->desconecta_OC();
		/*
		 * Marca la requisicion de enviada a O.C.
		*/
		foreach($dets as $qry) {
			$db->ejecutar($qry);
		}
		/*
		 * Marcamos el encabezado si ya se ha enviado todo el detalle a O.C.
		*/
		foreach ($heads as $head) {
			$run = $db->ejecutar($head[0]);
			$row = mysqli_fetch_array($run);
			if($row <= 0) {
				$db->ejecutar($head[1]);
			}
		}
		$res = $orden;
	} catch(Exception $e) {
		$res = $e->getMessage();
	}
	//$res = print_r($infE, true);
	echo $res;
}
/*
 *
 */
function nr() {
	require_once 'model/reqModel.php';
	require_once 'model/SQLgenerales.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstados();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$key_sess = $_SESSION['u'].$_GET['id'].$_GET['c'].$_GET['a'];
	if(!isset($_GET['page'])) {
		$page=1;
		unset($_SESSION[$key_sess]) ;
		$_SESSION[$key_sess] = '';
	} else {
		$page=$_GET['page'];
	}
	$reqs['registros'] = 0;
	if($_POST) {
		$_SESSION[$key_sess] = $_POST;
	}
	$reqs = ConsultaNR($page, $_SESSION[$key_sess]);
	require_once 'view/req/remision.php';
}
/*
 *
*/
function oc_sp(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstados();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
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
		$solcs = ConsultaOC($_SESSION[$key_sess]);
	}
	include 'view/repo/oc_soporte.php';
}

/*
 * 
 */
function ocpre(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/reqModel.php';
	$conf = Configuracion::getInstance();
	$emps = listarEmpUsuario();
	if($_POST){
		$trabajar = listasPreOC($_POST['empresa']);
		$infoEmpresa = datosCia($_POST['empresa']);
	}
	require_once 'view/req/preoc/preoc.php';
}
/*
 * function ocpre pdf
 * 
*/
function ocpre_pdf(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	require_once 'view/req/preoc/PDF.php';
}
function ocpre_pdf_(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	require_once 'view/req/preoc/PDF@.php';
}
/*
 * Reimprime Orden
*
*/
function ocpre_rpdf(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	require_once 'view/req/preoc/RE_PDF.php';
}
/*
 * Reimprime Orden
*
*/
function ocpre_gpdf(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	require_once 'view/req/preoc/GA_PDF.php';
}
/*
 * Marcar Remision de recibida
 */
function m_r(){
	require_once 'view/req/mremision.php';
}
/*
 * Marca de Recibido la nota
 */
function m_rem(){
	//print_r($_POST);
	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	/* Nos vamos a la O.C. */
	list ( $empresa_oc, $empresa_sicys ) = split ( "-", $_POST ['empresa'] );
	//
	$sql_existe = "Select id_prehreq From ".$conf->getTbl_predreq()
		." Where id_empresa = ".$empresa_sicys
		." And id_cc = ".$_POST ['nbodega']
		." And predreq_remision = '".$_POST['oc']."'";
	$run_existe = $db->ejecutar($sql_existe);
	$row_existe = mysqli_fetch_array($run_existe);
	
	$sql_actualiza = "Update ".$conf->getTbl_predreq()
		." Set predreq_estado = 7"
		." Where id_empresa = ".$empresa_sicys
		." And id_cc = ".$_POST ['nbodega']
		." And predreq_remision = '".$_POST['oc']."'";
	$db->ejecutar($sql_actualiza);
	
	$sql_actualiza_h = "Update ".$conf->getTbl_prehreq()
		." Set prehreq_estado = 7"
		." Where id_prehreq = ".$row_existe['id_prehreq'];
	$db->ejecutar($sql_actualiza_h);
	/*
	 * Pone Tracking
	 */
	$sql_tracking = "Insert Into ".$conf->getTbl_prehreq_stat()." Set "
		."id_prehreq = ".$row_existe['id_prehreq'].", "
		."prehreq_stat = 7, "
		."prehreq_stat_desc = '".$conf->getEstado('7')."', "
		."prehreq_stat_fecha = '".date("Y-m-d")."', "
		."prehreq_stat_hora = '".date("H:i:s")."', "
		."prehreq_stat_usuario = '".$_SESSION['u']."'";
	$db->ejecutar($sql_tracking);
	echo '<h4>Remision marcada de recibida con exito</h4>';
}
/*
 * listarCcUsuario
*/
function listaCC(){
	require_once 'model/reqModel.php';
	echo json_encode(listarCC());
}

function listarCc2(){
	require_once 'model/reqModel.php';
	echo json_encode(listarCcUsuario2());
}
/*
 * Verificaciones generales antes de procesar ingreso de O.C.
*/
function verificaRemision() {
	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	$result = "NO";
	/* Nos vamos a la O.C. */
	list ( $empresa_oc, $empresa_sicys ) = split ( "-", $_POST ['empresa'] );
	//
	$sql_existe = "Select * From ".$conf->getTbl_predreq()
		." Where id_empresa = ".$empresa_sicys
		." And id_cc = ".$_POST ['nbodega']
		." And predreq_remision = '".$_POST['oc']."'";
	//." And predreq_estado = '3'";
	$run_existe = $db->ejecutar($sql_existe);
	$num_existe = mysqli_num_rows ( $run_existe );
	if ($num_existe <= 0) {
		$result = "No existe Requisicon no. : ".$_POST['oc']." de Centro de Costo : ".$_POST['nbodega'];
	} else {
		$sql_ingreso = "Select * From ".$conf->getTbl_predreq()
			." Where id_empresa = ".$empresa_sicys
			." And id_cc = ".$_POST ['nbodega']
			." And predreq_remision = '".$_POST['oc']."'"
			." And predreq_estado = '7'";
		$run_ingreso = $db->ejecutar($sql_ingreso);
		$num_ingreso = mysqli_num_rows($run_ingreso);
		if ($num_ingreso > 0) {
			$result = "Remision: ".$_POST['oc']." ya se ha marcado.";
		} else {
			/*$sql_total = "Select * From ".$conf->getTbl_predreq()
				." Where id_empresa = ".$empresa_sicys
				." And id_cc = ".$_POST ['nbodega']
				." And predreq_remision = '".$_POST['oc']."'"
				." And predreq_estado = '6'";
			$run_total = $db->ejecutar($sql_total);
			$row_total = mysqli_fetch_array($run_total);
			if ($run_total) {
				$result = print_r($row_total);
			} else {*/
				$result = "OK";
			//}
		}
	}
	echo $result;
}
/*
 * 
 */
function ver() {
	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	$sql = "Select prod_codigo, predreq_descripcion, predreq_cantidad, predreq_cantidad_aut, predreq_cantidad_aut_obs from ". $conf->getTbl_predreq()." Where id_prehreq = ".$_REQUEST['id'];
	$run = $db->ejecutar($sql);
	echo '<table class="table table-hover">';
	echo '<thead><tr>';
	echo '<th>Codigo</th>';
	echo '<th>Descripcion</th>';
	echo '<th>Cant. Solicitada</th>';
	echo '<th>Cant. Autorizada</th>';
	echo '<th>Observacion Autorizacion</th>';
	echo '</thead></tr><tbody>';
	while($row = mysqli_fetch_array($run)){ 
		echo '<tr>';
		echo '<td>'.$row['prod_codigo'].'</td>';
		echo '<td>'.$row['predreq_descripcion'].'</td>';
		echo '<td>'.$row['predreq_cantidad'].'</td>';
		echo '<td>'.$row['predreq_cantidad_aut'].'</td>';
		echo '<td>'.$row['predreq_cantidad_aut_obs'].'</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
}