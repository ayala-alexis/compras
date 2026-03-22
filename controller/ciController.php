<?php
session_start();
/*error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);*/
/*
 * Inicio de solicitudes
 */
function inicio(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/ciModel.php';
	$conf = Configuracion::getInstance();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$ccs = listarCcUsuario();
	$solcs = listarSolcs();
	$tips = listarTipcs();
	require_once 'view/ci/listado.php';
}

// Mantenimiento de Proveedores
function prod() {
	include_once 'view/ci/mttoProducto.php';
}
/*
 * Listado de Solicitudes para Gestor
*/
function gestor(){
	require_once 'model/ciModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	if(isset($_GET['ps']) && trim($_GET['ps']) != ''){
		$sql = "Update ci_enc"
			." Set"
			." ci_revisando=''"
			." Where id_ci = ".$_GET['ps'];
		$db->ejecutar($sql);
	}
	$solcs = listarSolcsGes();
	//print_r($solcs);
	require_once 'view/ci/listges.php';
}
/*
 * Nueva solicitud de compra
 */
function crear(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/ciModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$permisos = permisosURL();
	if ($_POST) {
		if(isset($_POST['ci_numero']) && !empty($_POST['ci_numero']) ) {
			$sql_ver = "Select ci_estado From ci_enc"
				." Where "
				."id_empresa = ".$_POST['id_empresa']." And "
				."id_cc = ".$_POST['id_cc']." And "
				."ci_numero = ".$_POST['ci_numero'];
			$run_sql = $db->ejecutar($sql_ver);
			if (mysqli_num_rows($run_sql) <= 0) {
				$rtn = 1;
				$msg = 'La presolicitud ha dejado de existir';
			} else {
				$row = mysqli_fetch_array($run_sql);
				if ($row[0] > 1) {
					$rtn = 1;
					$msg = 'El estado ha cambiado, presione F5 para actualizar.';
				} else  {
					list($id_prod, $prod_codigo, $prod_descripcion) = explode('~', $_POST ['prod_codigo']);
					$sql = "INSERT INTO ci_det SET "
						. "id_ci = " . $_POST ['id_ci'] . ", "
						. "id_usuario = " . $_POST ['id_usuario'] . ", "
						. "id_empresa = " . $_POST ['id_empresa'] . ", "
						. "id_cc = " . $_POST ['id_cc'] . ", "
						. "id_prod = " . $id_prod . ", "
						. "ci_det_cantidad = " . $_POST ['ci_det_cantidad'] . ", "
						. "prod_codigo = '" . $prod_codigo . "', "
						. "ci_numero = " . $_POST ['ci_numero'] . ", "
						. "prod_descripcion = '" . $prod_descripcion . "', "
						. "ci_det_hora = '" . date ( 'H:i:s' ) . "', "
						. "ci_det_fecha = '" . date ( 'Y-m-d' ) . "', "
						. "ci_det_usuario = '" . strtoupper ( $_POST ['ci_det_usuario'] ) . "'";
					$db->ejecutar($sql);
					$_POST = array();
					$url = 'Location : ?'.$_SERVER["QUERY_STRING"];
					header($url); die();
				}
			}
		} else {
			$presol = creaPreSolc($_POST);
			if (!is_numeric($presol)){
				echo '<div class="alert alert-error">';
				echo $presol;
				echo '</div>';
				die();
			} else {
				$form = $_POST;
				$url = 'Location: ?c='.$form['c'].'&a='.$form['a'].'&id='.$form['idmod'].'&ps='.$presol.'&cs='.$form['centrocosto'].'&es='.$form['empresa'];
				$_POST = array();
				header($url); die();
			}
		}
	}
	$infohsol = infoPreHSolc($_GET['ps'],$_GET['cs'],$_GET['es']);
	//echo $infohsol[0]['id_prehsol'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa'];
	$detas = detPreDSolc($infohsol[0]['id_ci'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa']);
	$tipsp = listarTipcsP();
	//$cats = listarCategorias();
	require_once 'view/ci/nueva_form.php';
}
/*
 * Nueva solicitud de compra
*/
function trabajo(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/ciModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$permisos = permisosURL();
	/*if ($_POST) {
		print_r($_POST);
	}*/
	$infohsol = infoPreHSolc($_GET['ps'],$_GET['cs'],$_GET['es']);
	$detas = detPreDSolc($infohsol[0]['id_ci'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa']);
	
	if($infohsol[0]['ci_estado'] == 1){
		$sql = "Update ci_enc"
			." Set ci_estado = 2,"
			." ci_revision='".$_SESSION['u']."', "
			." ci_revision_fecha='".date('Y-m-d H:i:s')."', "
			." ci_revisando='".$_SESSION['u']."'"
			." Where id_ci = ".$infohsol[0]['id_ci'];
		$db->ejecutar($sql);
	}
	
	$sql = "Update ci_enc"
		." Set"
		." ci_revisando='".$_SESSION['u']."'"
		." Where id_ci = ".$infohsol[0]['id_ci'];
	$db->ejecutar($sql);
	
	require_once 'view/ci/trabaja_form.php';
}
function trabajoe(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/ciModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$permisos = permisosURL();
	/* Marcamos de Impresa */
	$sql_up = "Update ci_enc "
			."Set ci_estado=3"
			." Where id_ci=".$_REQUEST['ps'];
	$db->ejecutar($sql_up);
	$sql_upd = "Update ci_det "
			."Set ci_det_estado=3"
			." Where id_ci=".$_REQUEST['ps'];
	$db->ejecutar($sql_upd);

	header('Location: ?c=ci&a=gestor&id=12'); die();
}
/*
 * Borrar requisicion
*/
function borrar(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Delete From ci_enc"
		." Where id_ci = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	$sql = "Delete From ci_det"
		." Where id_ci = ".$_REQUEST["ps"];
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
                            <a href="?c=ci&a=inicio&id=12" class="btn btn-lg btn-block btn-success">Regresar</a>
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
	$sql = "Select count(id_ci_det) as total From ci_det Where id_ci = ".$_REQUEST["id_ci"];
	$run = $db->ejecutar($sql);
	$row = mysqli_fetch_array($run);
	// Tiene items para procesar
	if($row[0] > 0) {
			
		// marcamos de enviada la solicitud
		$sql = "Update ci_enc"
			." Set ci_estado = 1,"
			." ci_autorizado = '".$_SESSION['u']."',"
			." ci_autorizado_fecha_hora = '".date('Y-m-d H:i:s')."', "
			." ci_observacion = '".strtoupper(trim($_REQUEST["observa_sol"]))."'"
			." Where id_ci = ".$_REQUEST["id_ci"];
		$db->ejecutar($sql);
		// Actualizamos el estado a enviado para autorizacion
		$sql = "Update ci_det"
			." Set ci_det_estado = 1"
			." Where id_ci = ".$_REQUEST["id_ci"];
		$db->ejecutar($sql);
		// Ponemos el estado de enviado autorizacion
		/*$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
			."id_prehsol = ".$_REQUEST['ps'].", "
			."prehsol_stat = 1, "
			."prehsol_stat_desc = '".$conf->getEstadoSC('1')."', "
			."prehsol_stat_fecha = '".date("Y-m-d")."', "
			."prehsol_stat_hora = '".date("H:i:s")."', "
			."prehsol_stat_usuario = '".$_SESSION['u']."'";
		$db->ejecutar($sql_st);*/
		echo '<div class="bs-calltoaction bs-calltoaction-primary">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">SOLICITUD DE CONSUMO INTERNO ENVIADA CON EXITO.</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=ci&a=inicio&id=12" class="btn btn-lg btn-block btn-primary">IR AL INICIO</a>
                        </div>
                     </div>
                </div>';
		/*
		 * Imprimimos Formulario
		 */
		//header('Location: /sics/view/ci/PDF.php?id='.$_REQUEST['ps']);
		//header('Location: http://192.168.40.4/sics/?c=ci&a=inicio&id=12');
		//crear&id=12&ps='.$_REQUEST['ps'].'&cs='.$_REQUEST['id_c'].'&es='.$_REQUEST['id_empresa']
		header('Location: /sics/?c=ci&a=crear&id=12&ps='.$_REQUEST['ci_numero'].'&cs='.$_REQUEST['id_cc'].'&es='.$_REQUEST['id_empresa']);
		//header('Location: /sics/class/PHPMailer/sendCI.php?'.$_REQUEST['ps']);
		die();
	} else {
		echo '<div class="bs-calltoaction bs-calltoaction-danger">
	                    <div class="row">
	                        <div class="col-md-9 cta-contents">
	                            <h1 class="cta-title">Lo sentimos, ha ocurrido un error!</h1>
	                            <div class="cta-desc">
	                                <h4>LA SOLICITUD DE CONSUMO INTERNO QUE DESEA ENVIAR ESTA VACIA.</h4>
	                            </div>
	                        </div>
	                        <div class="col-md-3 cta-button">
	                            <a href="?c=ci&a=inicio&id=12" class="btn btn-lg btn-block btn-danger">REGRESAR</a>
	                        </div>
	                     </div>
	                </div>';
	}
}
/*
 * Borrar requisicion
*/
function deny(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Update ci_enc"
		." Set ci_estado = 10"
		." Where id_ci = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	$sql = "Update ci_det"
		." Set ci_det_estado = 10"
		." Where id_ci = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	// Ponemos el estado de negado
	/*$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
		."id_prehsol = ".$_REQUEST['ps'].", "
		."prehsol_stat = 10, "
		."prehsol_stat_desc = '".$conf->getEstadoSC('10')."', "
		."prehsol_stat_fecha = '".date("Y-m-d")."', "
		."prehsol_stat_hora = '".date("H:i:s")."', "
		."prehsol_stat_usuario = '".$_SESSION['u']."'";
	$db->ejecutar($sql_st);*/
	echo '<div class="bs-calltoaction bs-calltoaction-success">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">LA SOLICITUD HA SIDO DESISTIDA CON EXITO.</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=ci&a=inicio&id=12" class="btn btn-lg btn-block btn-success">IR AL INICIO</a>
                        </div>
                     </div>
                </div>';
}
/*
 * Borrar requisicion
*/
function autoges(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set prehsol_estado = 3"
		." Where id_prehsol = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	$sql = "Update ".$conf->getTbl_predsol()
		." Set predsol_estado = 3"
		." Where id_prehsol = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	// Ponemos el estado de negado
	$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
		."id_prehsol = ".$_REQUEST['ps'].", "
		."prehsol_stat = 3, "
		."prehsol_stat_desc = '".$conf->getEstadoSC('3')."', "
		."prehsol_stat_fecha = '".date("Y-m-d")."', "
		."prehsol_stat_hora = '".date("H:i:s")."', "
		."prehsol_stat_usuario = '".$_SESSION['u']."'";
	$db->ejecutar($sql_st);
	echo '<div class="bs-calltoaction bs-calltoaction-success">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">La solicitud ha sido autorizada y enviada al area de compras con exito!</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=solc&a=gestor&id=5" class="btn btn-lg btn-block btn-success">Regresar</a>
                        </div>
                     </div>
                </div>';
	//header('Location: /sics/class/PHPMailer/send2.php?'.$_REQUEST['ps']);
}
/*
 * Muestra detalle de solicitud para autorizar
 */
function autorizar(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$infohsol = infoPreHSolc($_GET['ps'],$_GET['cs'],$_GET['es']);
	$detas = detPreDSolc($infohsol[0]['id_prehsol'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa']);
	include_once 'view/solc/autorizar.php';
}
/*
 * listarCcUsuario
 */
function listarCcUsuarioC(){
	require_once 'model/ciModel.php';
	echo json_encode(listarCcUsuario());
}
/*
 * Autorizar presolicitud
 */
function autoriza(){
	require_once 'model/solcModel.php';
	$emps = listarEmpUsuario();
	if(isset($_GET['es']) && $_GET['es'] != ""){
		$solcs = listarSolcsAuth($_GET['es']);
	} else {
		$solcs = array();
	}
	include_once('view/solc/autoriza.php');
}

/*
 * Consulta de requisiones por emisor
*/
function emisor(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/ciModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstadosCI();
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
		//print_r($_POST);
	}
	$solcs = Consulta($page, $_SESSION[$key_sess]);
	//Print_r($solcs);
	require_once 'view/ci/consulta.php';
}

/*
 * Recolectar requisiciones
*/
function colectar(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstadosSC();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	if ($_POST) {
		$colectas = listasColectar($_POST['empresa'],$_POST['inicio'],$_POST['fin']);
	}
	require 'view/solc/colectar.php';
}
/*
 *
*/
function tracole(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstadosSC();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$prods = itemsSolc();
	if($_POST){
		$trabajar = listasTrabajar($_POST['empresa']);
		$infoEmpresa = datosCia($_POST['empresa']);
	}
	// incluimos la vista
	require 'view/solc/tracole.php';
}
/*
 * listarPrecio
*/
function listaPrecio(){
	require_once 'model/solcModel.php';
	echo json_encode(listarPrecio());
}
/*
 * listarItems
*/
function listarItems(){
	require_once 'model/solcModel.php';
	echo json_encode(listaItems());
}

/*
 * lista de proveedores
*/
function listaProveedor(){
	require_once 'model/solcModel.php';
	echo json_encode(listarProveedor());
}

/*
 * crear oc, todos los items de requisiciones trabajadas
*/
function crearoc() {
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$emps = listarEmpUsuario();
	if($_POST){
		$trabajar = listasOC($_POST['empresa']);
		$infoEmpresa = datosCia($_POST['empresa']);
	}
	require_once 'view/solc/crearoc.php';
}
/*
 *
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
	$sql = "Select cod_proveedor From provint Where "
		."id_empresa = ".$empresa." And "
		."id_proveedor = ".$proveedor;
	$run = $db->ejecutar($sql);
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
			$dets[] = "Update ".$conf->getTbl_predsol()
				." Set predsol_numero_oc=".$orden.", "
				." Set predsol_fecha_oc='".date('y-m-d')."', "
				." Set predsol_hora_oc='".date('H:i:s')."', "
				." Set predsol_usuario_oc='".$_SESSION['u']."', "
				." predsol_estado = 6"
				." Where id_predsol=".$field['id_predsol'];
			$heads[$i][] = "Select Count(predreq_estado) From ".$conf->getTbl_predreq()
				." Where id_prehreq = ".$field['id_prehreq']." And"
				." predreq_estado = 5";
			$heads[$i][] = "Update ".$conf->getTbl_prehreq()." Set"
				." prehreq_estado = 6"
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
			.$_SESSION['u']."', OCTITG ='00', OCDETG ='00', OCCUOTA=0, OCGESTOR='', OCSTAT='', ITEMS=".$i.", "
			."OCCIA ='".$infE[0]['cia_presupuesto']."', OCVALOR ='".$valor."', PRT = '0', ORIGEN='SICYS', CODPROVEE='".$prov_oc."', "
			."ATENCION='".$infP[0]['prov_contacto1']."', CONDIPAGO='".$infP[0]['prov_dias']."'";
		$db->ejecuta_OC($_SqlOr);
		// Observaciones
		$_SqlOrde = "Insert Into ".$conf->getTobser().$infE[0]['id_empresa_oc']." Set OBORDEN = '".$orden."'";
		$db->ejecuta_OC($_SqlOrde);
		// Llenamos el datalle
		$runsql = $db->ejecuta_OC($sql);
		$db->desconecta_OC();
		/*
		 * Marca la solicitud de enviada a O.C.
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
 * Administrar usuarios con acceso de gestor
 */
function ges(){
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	// Agregar
	if(isset($_GET['add']) && $_POST){
		$sql = "Insert into ".$conf->getTbl_gestores()." Set "
			." id_usuario = ".$_POST['usuario']
			.", id_cc = 0";
		try {
			$db->ejecutar($sql);
		} catch (Exception $e) {
			echo '<p class="alert alert-danger">'.$e->getMessage().'</p>';
		}
	}
	// Quitar
	if(isset($_GET['del']) && isset($_GET['usr'])){
		$sql = "Delete From ".$conf->getTbl_gestores()." Where"
				." id_usuario = ".$_GET['usr'];
		try {
			$db->ejecutar($sql);
		} catch (Exception $e) {
			echo '<p class="alert alert-danger">'.$e->getMessage().'</p>';
		}
	}
	$ges = listarGestores();
	$usr = listarUsuarios();
	require_once 'view/solc/gestores.php';
	
}

/*
 * Administrar de accesos por usuario gestor
*/
function gesc(){
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	// Agregar
	if(isset($_GET['add']) && $_POST){
		$sql = "Insert into ".$conf->getTbl_gestores()." Set "
				." id_usuario = ".$_GET['usr']
				.", id_cc = ".$_POST['usuario'];
		try {
			$db->ejecutar($sql);
		} catch (Exception $e) {
			echo '<p class="alert alert-danger">'.$e->getMessage().'</p>';
		}
	}
	// Quitar
	if(isset($_GET['del']) && isset($_GET['usr'])){
		$sql = "Delete From ".$conf->getTbl_gestores()." Where"
				." id_usuario = ".$_GET['usr']
				." and id_cc = ".$_GET['cc'];
		try {
			$db->ejecutar($sql);
		} catch (Exception $e) {
			echo '<p class="alert alert-danger">'.$e->getMessage().'</p>';
		}
	}
	$sql_u = "Select usr_nombre From ".$conf->getTbl_usuario()." Where id_usuario=".$_GET['usr'];
	$run = $db->ejecutar($sql_u);
	$row = mysqli_fetch_array($run);
	
	$ges = listarAccesosId($_GET['usr']);
	$ccs = listarCentros();
	require_once 'view/solc/gestoresc.php';

}

/*
 * Trabajar Item de Solicitud
*/
function trabajoi(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	if ($_POST) {
		if($_POST['accion'] == 'producto') {
			$postvalue = unserialize(base64_decode($_POST['producto'])); 
	    	$sql = "Update " . $conf->getTbl_predsol() . " Set "
	    		."prod_codigo = '".$postvalue[0]."', "
	    		."predsol_descripcion = '".$postvalue[1]."', "
	    		."predsol_observacion = '".$_POST['observacion']."', "
	    		."sl_linea = '".$postvalue[2]."', "
	    		."sl_sublinea = '".$postvalue[3]."', "
	    		."predsol_titgas = '".$postvalue[4]."', "
	    		."predsol_detgas = '".$postvalue[5]."' "
	    		."Where id_predsol = ".$_GET['pd'];
	    	$db->ejecutar($sql);
		}
		if($_POST['accion'] == 'precio') {
			$postvalue = unserialize(base64_decode($_POST['producto']));
			$sql = "Update " . $conf->getTbl_predsol() . " Set "
					."predsol_prec_uni = '".round($_POST['valor'],2)."', "
			    	."predsol_total = '".round(($_POST['cantidad']*$_POST['valor']),2)."' "
			    	."Where id_predsol = ".$_GET['pd'];
			$db->ejecutar($sql);
		}
    	header('Location: ?c=solc&a=trabajoi&id=5&pd='.$_REQUEST['pd']); die();
    	//header('Location: ?c=solc&a=trabajo&id=5&ps='.$_POST['ps'].'&cs='.$_POST['cs'].'&es='.$_POST['es']);
	}
	// Detalle
	$sql_d = "Select * from ".$conf->getTbl_predsol()." Where id_predsol=".$_REQUEST['pd'];
	$res_d = $db->ejecutar($sql_d);
	$row_d = mysqli_fetch_array($res_d);
	$detas = $row_d;
	// Encabezado
	$sql_h = "Select * From ".$conf->getTbl_prehsol(). " Where id_prehsol = ".$row_d['id_prehsol'];
	$res_h = $db->ejecutar($sql_h);
	$row_h = mysqli_fetch_array($res_h);
	$infohsol = $row_h;
	// Productos
	$prods = array();
	$sql_p = "Select p.id_producto, p.prod_codigo, p.prod_descripcion, p.sl_linea, p.sl_sublinea, t.gas_tit_codigo, t.gas_det_codigo From ".$conf->getTbl_producto(). " p "
		."Join ".$conf->getTbl_sublinea()." s "
		."On s.sl_linea = p.sl_linea And "
		."s.sl_sublinea = p.sl_sublinea "
		."Join ".$conf->getTbl_tagasto()." t "
		."On t.id_tagasto = s.id_tagasto "
		."Where p.prod_solc = 1 Order By p.prod_descripcion";
	$res_p = $db->ejecutar($sql_p);
	while($row_p = mysqli_fetch_array($res_p)) {
		$prods[] = $row_p;
	}
	require_once 'view/solc/item_form.php';
}

/*
 * Agregar item
*/
function trabajoia(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	if ($_POST) {
		list($li, $sl, $tit, $det) = explode('~', $_POST['sublinea']);
		$sql_prod = "Insert Into ".$conf->getTbl_producto()." Set "
			."prod_codigo = '".strtoupper(trim($_POST['codigo']))."', "
			."prod_descripcion = '".strtoupper(trim($_POST['descripcion']))."', "
			."prod_observacion = '".strtoupper(trim($_POST['observacion']))."', "
			."sl_linea = '".$li."', "
			."prod_solc = 1,"
			."sl_sublinea = '".$sl."'";
		try {
			$db->ejecutar($sql_prod);
			$sql = "Update " . $conf->getTbl_predsol() . " Set "
				."prod_codigo = '".strtoupper(trim($_POST['codigo']))."', "
				."predsol_descripcion = '".strtoupper(trim($_POST['descripcion']))."', "
				."predsol_observacion = '".strtoupper(trim($_POST['observacion']))."', "
				."predsol_titgas = '".$tit."', "
				."predsol_detgas = '".$det."', "
				."sl_linea = '".$li."', "
				."sl_sublinea = '".$sl."' "
				."Where id_predsol = ".$_GET['pd'];
			$db->ejecutar($sql);
			//header('Location: ?c=solc&a=trabajo&id=5&ps='.$_POST['ps'].'&cs='.$_POST['cs'].'&es='.$_POST['es']);
			//header('Location: ?c=solc&a=trabajoi&id=5&pd='.$_GET['pd']);
			header('Location: ?c=solc&a=trabajoi&id=5&pd='.$_REQUEST['pd']); die();
		} catch (Exception $e) {
			echo '<div class="alert alert-danger" role="alert">';
			echo $e->getMessage();
			echo '</div>';
		}
	}
	// Detalle
	$sql_d = "Select * from ".$conf->getTbl_predsol()." Where id_predsol=".$_REQUEST['pd'];
	$res_d = $db->ejecutar($sql_d);
	$row_d = mysqli_fetch_array($res_d);
	$detas = $row_d;
	// Encabezado
	$sql_h = "Select * From ".$conf->getTbl_prehsol(). " Where id_prehsol = ".$row_d['id_prehsol'];
	$res_h = $db->ejecutar($sql_h);
	$row_h = mysqli_fetch_array($res_h);
	$infohsol = $row_h;
	// Productos
	$prods = array();
	$sql_p = "Select s.id_sublinea, s.sl_linea, s.sl_sublinea, s.sl_descripcion, t.gas_tit_codigo, t.gas_det_codigo From ".$conf->getTbl_sublinea()." s"
		." Join ".$conf->getTbl_tagasto()." t On"
		." t.id_tagasto = s.id_tagasto"
		. " Order By s.sl_descripcion";
	$res_p = $db->ejecutar($sql_p);
	while($row_p = mysqli_fetch_array($res_p)) {
		$prods[] = $row_p;
	}
	require_once 'view/solc/itema_form.php';
}

/*
 * 
 */
function revisar(){
	include_once 'ODBC.php';
	include_once 'ODBCIT.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	//print_r($_REQUEST);
	$sql_d = "Select p.*, c.cc_codigo From ".$conf->getTbl_predsol()." p "
		."Join ".$conf->getTbl_cecosto()." c "
		."On c.id_cc = p.id_cc " 
		."Where p.id_prehsol = ".$_REQUEST['ps'];
	$res_d = $db->ejecutar($sql_d);
	// Detalle
	$ds = array();
	$stop1 = 0;
	while($row_d = mysqli_fetch_array($res_d)){
		$ds[] = $row_d;
		if(empty($row_d['prod_codigo']) || $row_d['predsol_prec_uni'] <= 0) {
			$stop1 = 1;
		}
	}
	// Encabezado
	$sql_h = "Select * From ".$conf->getTbl_prehsol(). " Where id_prehsol = ".$_REQUEST['ps'];
	$res_h = $db->ejecutar($sql_h);
	$row_h = mysqli_fetch_array($res_h);
	$hs = $row_h;
	
	// Presupuesto
	if($_REQUEST['tipogasto'] == 2 & $stop1 == 0) {
		
		$fecha_prehreq = $hs['prehsol_fecha'];
		$d = date_parse_from_format('Y-m-d', $hs['prehsol_fecha']);
		$mes = $d["month"];
		$year = $d["year"];
		// Datos de empresa
		$sql_emp = "Select * From ".$conf->getTbl_empresa()
			." Where id_empresa = ".$hs['id_empresa'];
		$run_emp = $db->ejecutar($sql_emp);
		$row_emp = mysqli_fetch_array($run_emp);
		$stop2 = 0;
		$res_d = $db->ejecutar($sql_d);
		$msg1 = '';
		while($row_d = mysqli_fetch_array($res_d)){
			$gastod = str_pad($row_d['predsol_titgas'],2,'0',STR_PAD_LEFT).str_pad($row_d['predsol_detgas'],2,'0',STR_PAD_LEFT);
			//echo $gastod.'<br>';
			// Empresa debe poseer presupuesto
			if ($row_emp['emp_usa_presupuesto'] == '1') {
				//Busca la cuenta en la tablas de restricciones de presupuesto
				$db->conecta_OC();
				$sql_oc = "Select * From ".$conf->getTbl_restric()
					." Where resgas = '".$gastod."' And rescia = '".$row_emp['cia_presupuesto']."'";
				$runsql = $db->ejecuta_OC($sql_oc);
				$rowsql = mysqli_num_rows($runsql);
				$db->desconecta_OC();
				// La cuenta debe verificar su disponibilidad
				if ($rowsql > 0) {
					//ahora nos la vamos a jugar con el codigo de la compa�ia
					//para crear nuestro controlador de AS/400
					//1 = I.R.
					//2 = I.T.
					// Conexiones ODBC
					if ($row_emp['id_empresa'] == 1){
						$o = ODBC::getInstance();
					} else {
						$o = ODBCIT::getInstance();
					}
					$cc = $row_d['cc_codigo'];
					$idcc = $row_d['id_cc'];
					$gas2 = $gastod;
					$month = $mes;
					$Cia = $row_emp['cia_presupuesto'];
					$idprehreq = $row_d['id_prehsol'];
	
					$bandera = $o->ocuparaPresupuestoMesesAnteriores($Cia, $row_d['cc_codigo'], $gastod);
					$o->separaGasto($gas2);
					
					//echo '<p>'.$bandera.'</p>';
	
					$S2 = "Select SUM(predsol_cantidad_aut) cantidad, SUM(predsol_prec_uni) precio, SUM(predsol_total) total From ".$conf->getTbl_predsol()
						." Where predsol_estado = 4 "
						." AND id_cc = '".$idcc
						."' AND predsol_titgas = '"
						.$o->gastit."' AND predsol_detgas = '"
						.$o->gasdet."' And "
						."id_predsol <> ".$row_d['id_predsol'];
					$Q2 = $db->ejecutar($S2);
					$R2 = mysqli_fetch_array($Q2);
					//print_r($R2);
					$Total_Orden = round(($row_d['predsol_cantidad']*$row_d['predsol_prec_uni']),2); // Precion Total del Item
	
					if ($bandera=='S') {
						$dispo2 = 0.00;
						$o->Dispo2($Cia,$cc,$gas2,$month,$fecha_prehreq);
						$dispo2 = $o->dispo2-$R2[2];
						if ($dispo2 < $Total_Orden) {
							if($dispo2 < 0){
								$dispo2 = 0;
							}
							$rtn = 1;
							$msg1 .= '<p class="list-group-item-text">Presupuesto disponible : $'.($dispo2).', para Centro de Costo : '.$cc.'<br>';
							$msg1 .= "Valor del item : $".$Total_Orden.', ';
							$msg1 .= "para cuenta de gasto : ".$gas2;
							//."<br>DEL MES : ".$o->Qmes($month)
							$msg1 .= '</p><br>';
							//$msg .= $o->dispo2D;
							$stop2 = 1;
						}
					} else {
						$monto = $o->MontoPresupuesto($year,$Cia,$gas2,$cc,$month,'2'); // Presupuesto de CC
						$auth = $o->MontoAutorizado($year,$Cia,$gas2,$cc,$month,'2'); // Incrementos
						$decrem = $o->MontoDecremento($year,$Cia,$gas2,$cc,$month); // Decrementos
						$presupuesto_neto = ($monto+$auth)-$decrem; //Presupuesto + Incrementos - Decrementos
						$gastoSalidas = $o->GastoMS($year,$Cia,$gas2,$cc,$month); // Gastos Salidas
						$gastoEntradas = $o->GastoM($year,$Cia,$gas2,$cc,$month); // Gastos Entradas
						$gastoNeto = $gastoSalidas-$gastoEntradas; //Gato Neto = Salidas - Entradas
						$Presupuesto_Disponible = $presupuesto_neto-$gastoNeto-$R2[2]; // Disponible
						$Presupuesto_Disponible = round($Presupuesto_Disponible,2);
						if ($Presupuesto_Disponible < $Total_Orden){
							$dispo2 = $Presupuesto_Disponible;
							if ($dispo2 < $Total_Orden) {
								if($dispo2 < 0){
									$dispo2 = 0;
								}
								$rtn = 1;
								$msg1 .= '<p class="list-group-item-text">Presupuesto disponible : $ '.($dispo2).', para Centro de Costo : '.$cc.'<br>';
								$msg1 .= "Valor del item : $".$Total_Orden.", ";
								//$msg1 .= "VALOR EN SOLICITUD : $ ".($R2[2])."<br>";
								$msg1 .= "para cuenta de gasto : ".$gas2;//."<br>DEL MES : ".$o->Qmes($month);
								$msg1 .= '</p><br>';
								//$msg .= $o->dispo2D;
								$stop2 = 1;
							}
						}
					}
				}
			}	
		}
		if($stop2 == 1) {
			$sql_pone_string = "Update ".$conf->getTbl_prehsol()
				." Set prehsol_verificacion = '".trim($msg1)."', "
				." prehsol_verificacion_usuario = '".$_SESSION['u']."'"
				." Where id_prehsol=".$hs['id_prehsol'];
			$db->ejecutar($sql_pone_string);
		}
		// todo Ok, pasamos 
		if($rtn == 0) {
			//header('Location: /sics/class/PHPMailer/send3.php?'.$hs['id_prehsol']);
		}
		
	}

	include_once 'view/solc/revisa_form.php';
}

/*
 *
*/
function creao() {
	require_once 'model/SQLgenerales.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	
	$fecha_hoy =  date('Y-m-d');
	$hora_hoy = date('H:i:s');

	$infE = datosCia($_REQUEST['es']);
	//print_r($infE);
	$infP = datosProveedor($_REQUEST['pr']);
	//print_r($infP);
	//print_r($row);
	$prov_oc = $infP[0]['prov_nvocod'];
	/*   */
	$valor = $_REQUEST['tg'];
	//print_r($_REQUEST);
	try {
		// Nos conectamos a la base de datos de proveeduria O.C.
		$db->conecta_OC2();
		//Creamos primero en encabezado de la O.C.
		$sql = "Select NORDEN From empresas Where CODIGO = '".$infE[0]['id_empresa_oc']."'";
		$result = $db->ejecuta_OC2($sql);
		$row = mysqli_fetch_array($result);
		// Numero de orden a crear
		$Numero = $row[0]+1;
		$orden = $Numero;
		// Actualizamos el correlativo para la empresa
		$_SqlO = "Update empresas Set NORDEN = '".$Numero."' Where CODIGO ='".$infE[0]['id_empresa_oc']."'";
		$db->ejecuta_OC2($_SqlO);
		//Esto es el detalle de la O.C.
		$sql_h = "Select * From ".$conf->getTbl_prehsol()
				." Where id_prehsol=".$_REQUEST['ps'];
		$res_h = $db->ejecutar($sql_h);
		$row_h = mysqli_fetch_array($res_h);
		//Esto es el detalle de la O.C.
		$sql_d = "Select p.*, c.cc_codigo From ".$conf->getTbl_predsol()." p "
			."Join ".$conf->getTbl_cecosto()." c "
			."On c.id_cc = p.id_cc "
			."Where p.id_prehsol=".$_REQUEST['ps'];
		$res_d = $db->ejecutar($sql_d);
		$i=0;
		while ($row_d = mysqli_fetch_array($res_d)) {
			++$i;
			$dets[] = "Update ".$conf->getTbl_predsol()
				." Set predsol_numero_oc=".$orden.", "
				." predsol_fecha_oc='".$fecha_hoy."', "
				." predsol_hora_oc='".$hora_hoy."', "
				." predsol_usuario_oc='".$_SESSION['u']."', "
				." id_proveedor = ".$_REQUEST['pr'].", "
				." predsol_estado = 6" 
				." Where id_predsol=".$row_d['id_predsol'];
			$heads[$i][] = "Select Count(predsol_estado) From ".$conf->getTbl_predsol()
				." Where id_prehsol = ".$row_d['id_prehsol']." And"
				." predsol_estado = 5";
			$heads[$i][] = "Update ".$conf->getTbl_prehsol()." Set"
				." prehsol_estado = 6,"
				." prehsol_coti1 ='', "
				." prehsol_coti2 ='', "
				." prehsol_coti3 ='', "
				." prehsol_verificacion = '',"
				." prehsol_verificacion_usuario = ''"
				." Where id_prehsol = ".$row_d['id_prehsol'];
			$movs[] = "( "
				."'".$row_d['prod_codigo']."', "
				."'".$row_d['predsol_titgas']."', "
				."'".$row_d['predsol_detgas']."', "
				.$row_d['predsol_cantidad'].", "
				."'".$row_d['predsol_unidad']."', "
				."'".$row_d['predsol_descripcion']."', "
				."'".$row_d['cc_codigo']."', "
				.$row_d['predsol_prec_uni'].", "
				.$infE[0]['cia_presupuesto'].", "
				."0, "
				."'".$valor."', "
				.$i.", "
				."'".$orden."', "
				."'".$prov_oc."', "
				."8.75, "
				."0.13, "
				."'".date('Y/m/d')."', "
				."'".date('d/m/Y')."', "
				.$row_d['predsol_total']
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
			."Fecha_YMD, "
			."FECHA, "
			."TOTAL ) VALUES ".implode(',', $movs) ;
		// Escribimos el encabezado de la O.C.
		$_SqlOr = "Insert Into ".$conf->getTorden().$infE[0]['id_empresa_oc']." Set "
			."NORDEN = '".$orden."', " 
			."SOLICITCOM = '".$row_h['prehsol_numero_sol']."', "
			."FECHAPED ='".date("d/m/Y")."', " 
			."Fecha_YMD ='".date("Y/m/d")."', "
			."CCOSTO ='99', "
			."PEDIDOPOR = 'VARIOS CENTROS DE COSTO', "
			."US_CREO ='".$_SESSION['u']."', "
			."OCTITG ='00', "
			."OCDETG ='00', "
			."OCCUOTA=0, "
			."OCGESTOR='".$_SESSION['u']."', "
			."OCSTAT='', "
			."ITEMS=".$i.", "
			."OCCIA ='".$infE[0]['cia_presupuesto']."', "
			."OCVALOR ='".$valor."', "
			."PRT = '0', "
			."ORIGEN='SICYS', "
			."CODPROVEE='".$prov_oc."', "
			."ATENCION='".$infP[0]['prov_contacto1']."', "
			."CONDIPAGO='".$infP[0]['prov_dias']."'";
		$db->ejecuta_OC2($_SqlOr);
		// Observaciones
		$_SqlOrde = "Insert Into ".$conf->getTobser().$infE[0]['id_empresa_oc']." Set OBORDEN = '".$orden."'";
		$db->ejecuta_OC2($_SqlOrde);
		// Llenamos el datalle
		$runsql = $db->ejecuta_OC2($sql);
		$db->desconecta_OC2();
	    //Marca la solicitud de enviada a O.C.
	    try {
	    	foreach($dets as $qry) {
	    		$db->ejecutar($qry);
	    	}	
	    } catch (Exception $e) {
	    	$res = $e->getMessage();
	    }
	    try {
			//Marcamos el encabezado si ya se ha enviado todo el detalle a O.C.
			foreach ($heads as $head) {
				$run = $db->ejecutar($head[0]);
				$row = mysqli_fetch_array($run);
				if($row[0] <= 0) {
					$db->ejecutar($head[1]);
				}
			}
	    } catch (Exception $e) {
	    	$res = $e->getMessage();
	    }
		$res = $orden;
	} catch(Exception $e) {
		$res = $e->getMessage();
	}
	if(is_numeric($res)) {
		// Ponemos el estado de enviado autorizacion
		$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
			."id_prehsol = ".$row_h['id_prehsol'].", "
			."prehsol_stat = 6, "
			."prehsol_stat_desc = '".$conf->getEstadoSC('6')."', "
			."prehsol_stat_fecha = '".date("Y-m-d")."', "
			."prehsol_stat_hora = '".date("H:i:s")."', "
			."prehsol_stat_usuario = '".$_SESSION['u']."'";
		$db->ejecutar($sql_st);
		unlink($row_h['prehsol_coti1']);
		unlink($row_h['prehsol_coti2']);
		unlink($row_h['prehsol_coti3']);
		//header('Location: /sics/class/PHPMailer/send4.php?'.$row_h['id_prehsol'].'&'.$orden);
	} else {
		echo 'Ha ocurrido el siguiente error : '.$res;	
	}
}

/*
 * Administrar usuarios con acceso de gestor
*/
function ges2(){
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	// Agregar
	if(isset($_GET['add']) && $_POST){		
		$sql = "Insert into ".$conf->getTbl_gestion_categorias()." Set "
			." id_categoria = ".$_POST['categoria']
			.", id_usuario = ".$_POST['usuario'];
		try {
			$db->ejecutar($sql);
		} catch (Exception $e) {
			echo '<p class="alert alert-danger">'.$e->getMessage().'</p>';
		}
	}
	// Quitar
	if(isset($_GET['del']) && isset($_GET['usr'])){
		$sql = "Delete From ".$conf->getTbl_gestion_categorias()." Where"
				." id_auto_categoria = ".$_GET['usr'];
		try {
			$db->ejecutar($sql);
		} catch (Exception $e) {
			echo '<p class="alert alert-danger">'.$e->getMessage().'</p>';
		}
	}
	$ges = listarGestores2();
	$cats = listarCategorias();
	$usr = listarUsuarios();
	require_once 'view/solc/gestores2.php';

}

/*
 * Autorizar solicitudes por categoria
*/
function gescat(){
	require_once 'model/solcModel.php';
	$emps = listarCatUsuario();
	if(isset($_GET['es']) && $_GET['es'] != ""){
		$solcs = listarSolcsAuthCat($_GET['es']);
	} else {
		$solcs = array();
	}
	include_once('view/solc/autorizagc.php');
}

/*
 * Nueva solicitud de compra
*/
function trabajogc(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$permisos = permisosURL();
	/*if ($_POST) {
		print_r($_POST);
	}*/
	$infohsol = infoPreHSolc($_GET['ps'],$_GET['cs'],$_GET['es']);
	$detas = detPreDSolc($infohsol[0]['id_prehsol'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa']);
	require_once 'view/solc/trabaja_formgc.php';
}

/*
 * Aprueba categoria
 */
function aprbct(){
	
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	
	$sql_ver = "Select id_cc From ".$conf->getTbl_prehsol()
		." Where "
		."id_prehsol = ".$_REQUEST['id_prehsol'];
	$run_sql = $db->ejecutar($sql_ver);
	$row = mysqli_fetch_array($run_sql);
	
	// Directo a compras
	$esta_solc = 4;
	/*
	 * si no se define una autorizacion por categoria
	* verificamos si tiene definido un gestor
	*/
	if($esta_solc == 4){
		$sql_estado = "Select id_cc From ".$conf->getTbl_gestores()
		." Where id_cc = ".$row['id_cc'];
		$run_estado = $db->ejecutar($sql_estado);
		if(mysqli_num_rows($run_estado) > 0) {
			$esta_solc = 3;
		}
	}
	
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set prehsol_estado = ".$esta_solc.", "
		." prehsol_aprobacion_categoria = '".$_REQUEST['prehsol_aprobacion_categoria']."', "
		." prehsol_aprobacion_categoria_usuario = '".$_SESSION['u']."'"
		." Where id_prehsol = ".$_REQUEST["id_prehsol"];
	$db->ejecutar($sql);
	$sql = "Update ".$conf->getTbl_predsol()
		." Set predsol_estado = ".$esta_solc
		." Where id_prehsol = ".$_REQUEST["id_prehsol"];
	$db->ejecutar($sql);
	// Ponemos el estado de negado
	$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
		."id_prehsol = ".$_REQUEST['id_prehsol'].", "
		."prehsol_stat = ".$esta_solc.", "
		."prehsol_stat_desc = '".$conf->getEstadoSC($esta_solc)."', "
		."prehsol_stat_fecha = '".date("Y-m-d")."', "
		."prehsol_stat_hora = '".date("H:i:s")."', "
		."prehsol_stat_usuario = '".$_SESSION['u']."'";
	$db->ejecutar($sql_st);
	echo '<div class="bs-calltoaction bs-calltoaction-success">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">La solicitud ha sido autorizada y enviada al area de compras con exito!</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=solc&a=gescat&es='.$_REQUEST['es'].'" class="btn btn-lg btn-block btn-success">Regresar</a>
                        </div>
                     </div>
                </div>';
	//header('Location: /sics/class/PHPMailer/sendCat.php?'.$_REQUEST['id_prehsol']);
}

/*
 * NO Aprueba categoria
 */
function naprbct(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set prehsol_estado = 10,"
		." prehsol_aprobacion_categoria = '".$_REQUEST['prehsol_aprobacion_categoria']."', "
		." prehsol_aprobacion_categoria_usuario = '".$_SESSION['u']."'"
		." Where id_prehsol = ".$_REQUEST["h"];
	$db->ejecutar($sql);
	$sql = "Update ".$conf->getTbl_predsol()
		." Set predsol_estado = 10"
		." Where id_prehsol = ".$_REQUEST["h"];
	$db->ejecutar($sql);
	// Ponemos el estado de negado
	$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
		."id_prehsol = ".$_REQUEST['h'].", "
		."prehsol_stat = 10, "
		."prehsol_stat_desc = '".$conf->getEstadoSC('10')."', "
		."prehsol_stat_fecha = '".date("Y-m-d")."', "
		."prehsol_stat_hora = '".date("H:i:s")."', "
		."prehsol_stat_usuario = '".$_SESSION['u']."'";
	$db->ejecutar($sql_st);
	echo '<div class="bs-calltoaction bs-calltoaction-success">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">La solicitud ha sido desistida con exito!</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=solc&a=gescat&es='.$_REQUEST['es'].'" class="btn btn-lg btn-block btn-success">Regresar</a>
                        </div>
                     </div>
                </div>';
}

/*
 * Autorizar solicitudes por categoria
*/
function gest(){
	require_once 'model/solcModel.php';
	$solcs = listarSolcsAuthGest($_GET['es']);
	include_once('view/solc/autorizages.php');
}

/*
 * Nueva solicitud de compra
*/
function trabajoges(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$permisos = permisosURL();
	if ($_POST) {
		print_r($_POST);
	}
	$infohsol = infoPreHSolc($_GET['ps'],$_GET['cs'],$_GET['es']);
	$detas = detPreDSolc($infohsol[0]['id_prehsol'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa']);
	require_once 'view/solc/trabaja_formges.php';
}
/*
 * Aprueba gestor
*/
function aprbge(){

	$conf = Configuracion::getInstance();
	$db = DB::getInstance();

	// Directo a compras
	$esta_solc = 4;
	
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set prehsol_estado = ".$esta_solc.", "
		." prehsol_aprobacion_gestion = '".$_REQUEST['prehsol_aprobacion_gestion']."',"
		." prehsol_aprobacion_gestion_usuario = '".$_SESSION['u']."'"
		." Where id_prehsol = ".$_REQUEST["id_prehsol"];
	$db->ejecutar($sql);
	$sql = "Update ".$conf->getTbl_predsol()
		." Set predsol_estado = ".$esta_solc
		." Where id_prehsol = ".$_REQUEST["id_prehsol"];
	$db->ejecutar($sql);
	// Ponemos el estado de negado
	$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
		."id_prehsol = ".$_REQUEST['id_prehsol'].", "
		."prehsol_stat = ".$esta_solc.", "
		."prehsol_stat_desc = '".$conf->getEstadoSC($esta_solc)."', "
		."prehsol_stat_fecha = '".date("Y-m-d")."', "
		."prehsol_stat_hora = '".date("H:i:s")."', "
		."prehsol_stat_usuario = '".$_SESSION['u']."'";
	$db->ejecutar($sql_st);
	echo '<div class="bs-calltoaction bs-calltoaction-success">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">La solicitud ha sido autorizada y enviada al area de compras con exito!</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=solc&a=gest" class="btn btn-lg btn-block btn-success">Regresar</a>
                        </div>
                     </div>
                </div>';
	//header('Location: /sics/class/PHPMailer/sendGest.php?'.$_REQUEST['id_prehsol']);
}
/*
 * NO Aprueba gestor
*/
function naprbge(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set prehsol_estado = 10,"
		." prehsol_aprobacion_gestion = '".$_REQUEST['prehsol_aprobacion_gestion']."',"
		." prehsol_aprobacion_gestion_usuario = '".$_SESSION['u']."'"
		." Where id_prehsol = ".$_REQUEST["h"];
	$db->ejecutar($sql);
	$sql = "Update ".$conf->getTbl_predsol()
		." Set predsol_estado = 10"
		." Where id_prehsol = ".$_REQUEST["h"];
	$db->ejecutar($sql);
	// Ponemos el estado de negado
	$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
		."id_prehsol = ".$_REQUEST['h'].", "
		."prehsol_stat = 10, "
		."prehsol_stat_desc = '".$conf->getEstadoSC('10')."', "
		."prehsol_stat_fecha = '".date("Y-m-d")."', "
		."prehsol_stat_hora = '".date("H:i:s")."', "
		."prehsol_stat_usuario = '".$_SESSION['u']."'";
	$db->ejecutar($sql_st);
	echo '<div class="bs-calltoaction bs-calltoaction-success">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">La solicitud ha sido desistida con exito!</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=solc&a=gest" class="btn btn-lg btn-block btn-success">Regresar</a>
                        </div>
                     </div>
                </div>';
}

function xls(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/ciModel.php';
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	/** Error reporting */
	/*error_reporting(E_ALL);
	 ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);*/
	include 'class/PHPExcel.php';
	include 'class/PHPExcel/Writer/PDF.php';

	PHPExcel_Settings::CHART_RENDERER_JPGRAPH;
	PHPExcel_Shared_Font::setTrueTypeFontPath('C:/Windows/Fonts/');
	PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_APPROX);
	// A choix, librairie tcPDF, mPDF ou domPDF
	$rendererName = PHPExcel_Settings::PDF_RENDERER_TCPDF;
	$rendererLibrary = 'tcpdf';
	$rendererLibraryPath =  "class/" . $rendererLibrary;
	//  Here's the magic: you __tell__ PHPExcel what rendering engine to use
	//     and where the library is located in your filesystem
	if (!PHPExcel_Settings::setPdfRenderer(
			$rendererName,
			$rendererLibraryPath
	)) {
		die('NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
				'<br />' .
				'at the top of this script as appropriate for your directory structure'
		);
	}
	$db = DB::getInstance();
	$conf = Configuracion::getInstance();
	if($_POST) {
		list($idemp, $nomemp) = explode("~", $_REQUEST["empresa"]);
		$sql_emp = "Select ci_puntero from ".$conf->getTbl_empresa()
			." Where id_empresa = ".$idemp;
		$run_emp = $db->ejecutar($sql_emp);
		$row_emp = mysqli_fetch_array($run_emp);
		$var = trim($row_emp[0]);
    	if(isset($var) === true && $var === '') {
    		echo "<h2>No se ha definido un punto de inicio para ".$nomemp."  <a href='?c=ci&a=xls&id=12&via=1'><< Volver</a></h2>";
    	} else {
    		$fei = $var;
    		$fef = $_REQUEST['fefin'];
    		$fei = str_replace("-", "", $fei);
    		$fef = str_replace("-", "", $fef);
    		//echo $fei.' - '.$fef;
    		if($fef < $fei) {
    			echo "<h2>Ya se ha generado ese rango para ".$nomemp.", verifique. <a href='?c=ci&a=xls&id=12&via=1'><< Volver</a></h2>";
	    	} else {
	    		// Contamos si hay registros para procesar
	    		$sql_res = "select min(id_ci) inicio, max(id_ci) fin, count(id_ci) cantidad from ci_enc "
	    			." WHERE "
	    			." ci_estado In(1, 2) and"
	    			." ci_excel = '0' and"
	    			." ci_enc_fecha BETWEEN '".$var."' and '".$_REQUEST['fefin']."'"
	    			." and id_empresa = ".$idemp;
	    		$run_res = $db->ejecutar($sql_res);
	    		$row_res = mysqli_fetch_array($run_res);
	    		// Fin Contar
	    		//echo $row_res[2];
	    		if($row_res[2] <= 0){
	    			echo "<h2>No se han encontrado consumos para generar de ".$nomemp." o ya se ha generado, verifique. <a href='?c=ci&a=xls&id=12&via=1'><< Volver</a></h2>";
	    		} else {
		    		//die();
					$phpExcel = new PHPExcel();
					switch ($_GET['via']) {
						/*
						 * Listado para autorizacion de consumos internos
						 */
						case 1:
							
							$phpExcel->getProperties()
							->setCreator('Impressa Repuestos')
							->setTitle('Consumos para Autoriacion')
							->setLastModifiedBy('Sistemas de Tecnologia')
							->setDescription('Reporte de consumo interno para autorizacion')
							->setSubject('Consumos Internos para Autorizacion')
							->setKeywords('cnosumo interno uso sics impressa proveeduria')
							->setCategory('reportes');
			
							//$phpExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1,1);
			
							$phpExcel->setActiveSheetIndex(0);
							$phpExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
							$phpExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
							$phpExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
							$phpExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
							$phpExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
							$phpExcel->getActiveSheet()->getSheetView()->setView(PHPExcel_Worksheet_SheetView::SHEETVIEW_PAGE_LAYOUT);
			
							$sql = "SELECT
								d.*,
								a.ci_observacion,
								a.ci_enc_fecha,
								cc.cc_descripcion
								FROM
								ci_enc a
								INNER JOIN cecosto cc ON cc.id_cc = a.id_cc and cc.id_empresa = a.id_empresa
								INNER JOIN ci_det d ON d.id_ci = a.id_ci
								WHERE
								d.ci_det_estado In(1, 2) AND
								d.ci_excel = '0' AND 
								a.ci_enc_fecha >= '".$var."' AND a.ci_enc_fecha <= '".$_REQUEST['fefin']."' AND
								a.id_empresa = ".$idemp."
								ORDER BY
								a.ci_enc_fecha, a.id_cc";
							$run = $db->ejecutar($sql);
							$sql_up = "Update ci_enc set ci_excel = '1' "
									." Where "
									." ci_estado In(1, 2) AND "
									." ci_enc_fecha >= '".$var."' AND ci_enc_fecha <= '".$_REQUEST['fefin']."'";
							$db->ejecutar($sql_up);
							
							$sql_up_d = "update ci_det d"
								." Join ci_enc a On a.id_ci = d.id_ci"
								." Set d.ci_excel = '1'"
								." Where d.ci_det_estado In(1, 2) AND"
								." a.ci_enc_fecha >= '".$var."' AND a.ci_enc_fecha <= '".$_REQUEST['fefin']."' AND"
								." a.id_empresa = ".$idemp;
							$db->ejecutar($sql_up_d);
							
							$phpExcelSheet = $phpExcel->getSheet(0);
							$phpExcelSheet->setTitle("datos");
							$phpExcelSheet->setCellValue("a1", "REPORTE CONSOLIDADO DE CONSUMOS INTERNOS PASADOS A PROVEEDURIA PARA ".$nomemp." DEL ".$var." AL ".$_REQUEST['fefin']);
							$phpExcelSheet->setCellValue("a2", "FECHA");
							$phpExcelSheet->setCellValue("b2", "CENTRO DE COSTO");
							$phpExcelSheet->setCellValue("c2", "N CONSUMO");
							$phpExcelSheet->setCellValue("d2", "CANTIDAD");
							$phpExcelSheet->setCellValue("e2", "CODIGO");
							$phpExcelSheet->setCellValue("f2", "DESCRIPCION");
							$phpExcelSheet->setCellValue("g2", "OBSERVACION");
							//$phpExcelSheet->setCellValue("h2", "PROCEDE");
							
			
							$header = 'a1:g1';
							$phpExcelSheet->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
							$style = array(
									'font' => array('bold' => true,),
									'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
							);
							$phpExcelSheet->getStyle($header)->applyFromArray($style);
			
							$i = 3;
							$sumas = 0;
							while ($registro = mysqli_fetch_object ($run)) {
								$phpExcel->setActiveSheetIndex(0)
								->setCellValue('A'.$i, $registro->ci_enc_fecha)
								->setCellValue('B'.$i, $registro->cc_descripcion)
								->setCellValue('C'.$i, $registro->ci_numero)
								->setCellValue('D'.$i, $registro->ci_det_cantidad)
								->setCellValue('E'.$i, $registro->prod_codigo)
								->setCellValue('F'.$i, $registro->prod_descripcion)
								->setCellValue('G'.$i, $registro->ci_observacion);
								//->setCellValue('H'.$i, $registro->ci_det_procede);
								//$sumas = $sumas + $registro->total;
								$i++;
							}
							//$phpExcel->setActiveSheetIndex(0)->setCellValue('A'.$i, "Total");
							//$phpExcel->setActiveSheetIndex(0)->setCellValue('C'.$i, $sumas);
			
							/*$phpExcel->getActiveSheet()
							->getStyle("C1:C".$i)
							->getNumberFormat()
							->setFormatCode('#,##0.00');*/
			
							// Calculate the column widths
							foreach(range('A', 'G') as $columnID) {
								$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(false);
							}
							$phpExcel->getActiveSheet()->calculateColumnWidths();
			
							$phpExcelSheet->mergeCells("a1:g1");
			
							// Set setAutoSize(false) so that the widths are not recalculated
							foreach(range('A', 'G') as $columnID) {
								$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
							}
							/*
							$i--;
							$dsl=array(
									new PHPExcel_Chart_DataSeriesValues('String', 'datos!$B$2', NULL, 1),
									new PHPExcel_Chart_DataSeriesValues('String', 'datos!$C$2', NULL, 1),
							);
							$xal=array(
									new PHPExcel_Chart_DataSeriesValues('String', 'datos!$B$3:$B$'.$i, NULL, 90),
							);
							$dsv=array(
									new PHPExcel_Chart_DataSeriesValues('Number', 'datos!$C$3:$C$'.$i, NULL, 90),
							);
							$ds=new PHPExcel_Chart_DataSeries(
									PHPExcel_Chart_DataSeries::TYPE_BARCHART,
									PHPExcel_Chart_DataSeries::GROUPING_STANDARD,
									range(0, count($dsv)-1),
									$dsl,
									$xal,
									$dsv
							);
							$pa=new PHPExcel_Chart_PlotArea(NULL, array($ds));
							$legend=new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);
							$title=new PHPExcel_Chart_Title($_REQUEST['feini'] . ' a ' . $_REQUEST['fefin']);
							$chart= new \PHPExcel_Chart(
									'chart1',
									$title,
									$legend,
									$pa,
									true,
									0,
									NULL,
									NULL
							);
							$chart->setTopLeftPosition('E1');
							$chart->setBottomRightPosition('P21');
							$phpExcelSheet->addChart($chart);
							*/
							$writer = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
							//$writer->setIncludeCharts(true);
							$phpExcel->setActiveSheetIndex(0);
							$file = $_SESSION['u'].'_ci.xlsx';
							$writer->save(getcwd().'\\tmp\\'.$file);
							echo '<div class="bs-calltoaction bs-calltoaction-info">
			                    <div class="row">
									<div class="col-md-3 cta-button">
										<a id="btnExcel" name="btnExcel" target="_new" class="btn btn-lg btn-block btn-info" href="tmp/'.$file.'">Descargar archivo</a>
			                        </div>
			                        <div class="col-md-9 cta-contents">
			                            <h1 class="cta-title">Archivo generado.</h1>
			                            <div class="cta-desc">
			                                <p></p>
			                            </div>
			                        </div>
			                     </div>
			                </div>';
							
							$sql1_up2 = "update ci_det d"
									." Join ci_enc a On a.id_ci = d.id_ci"
									." Set d.ci_det_estado = 3"
									." Where a.ci_estado In (1, 2) AND"
									." a.ci_enc_fecha >= '".$var."' AND a.ci_enc_fecha <= '".$_REQUEST['fefin']."' AND"
									." a.id_empresa = ".$idemp;
							$db->ejecutar($sql1_up2);
							
							$sql1_up = "update ci_enc a Set"
									." a.ci_estado = 3"
									." Where a.ci_estado In (1, 2) AND"
									." a.ci_enc_fecha >= '".$var."' AND a.ci_enc_fecha <= '".$_REQUEST['fefin']."' AND"
									." a.id_empresa = ".$idemp;
							$db->ejecutar($sql1_up);
							
							$descripcion = $row_res[2]." consumos internos generados por ".$_SESSION['u']." hasta fecha ".$_REQUEST['fefin'];
							
							$sql_gen = "Insert into ci_generado"
								." Set "
								." per_descripcion = '".$descripcion."',"
								." per_desde = ".$row_res[0].","
								." per_hasta = ".$row_res[1].","
								." per_cantidad = ".$row_res[2].","
								." per_usuario = '".$_SESSION['u']."',"
								." per_inicio = '".$var."',"
								." per_fin = '".$_REQUEST['fefin']."',"
								." id_empresa = ".$idemp.","
								." per_fecha = '".date('Y-m-d H:i:s')."'";
							//echo $sql_gen;
							$db->ejecutar($sql_gen);
							
							$sql_pun = "Update empresa Set "
								." ci_puntero = '".$_REQUEST['fefin']."'"
								." Where id_empresa = ".$idemp;
							$db->ejecutar($sql_pun);
							
							break;
						case 2:
							$sql = "SELECT
									d.*,
									a.ci_observacion,
									a.ci_enc_fecha,
									cc.cc_descripcion
									FROM
									ci_enc a
									INNER JOIN cecosto cc ON cc.id_cc = a.id_cc and cc.id_empresa = a.id_empresa
									INNER JOIN ci_det d ON d.id_ci = a.id_ci
									WHERE
									d.ci_det_estado In(1, 2) AND
									d.ci_excel = '0' AND
									a.ci_enc_fecha >= '".$var."' AND a.ci_enc_fecha <= '".$_REQUEST['fefin']."' AND
									a.id_empresa = ".$idemp."
									ORDER BY
									a.ci_enc_fecha, a.id_cc";
							$run = $db->ejecutar($sql);
							$datos = array();
							while ($registro = mysqli_fetch_array ($run)) {
								$datos[] = $registro;
							}
							//print_r($datos);
							include 'view/ci/export.php';
							break;
						default:
							echo $_GET['via'];
							break;
					}
	    		}
    		}
		}
	} else {
		include 'view/ci/export.php';
	}
}
/*
 * Detalle de consumos incluidos en generacion
 */
function detalle(){
	require_once 'model/ciModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$solcs = listarDetCI();
	require_once 'view/ci/listdet.php';
}
/*
 * Enviar generacion a Excel
 */
function xls_d(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/ciModel.php';
	include 'class/PHPExcel.php';
	include 'class/PHPExcel/Writer/PDF.php';
	PHPExcel_Settings::CHART_RENDERER_JPGRAPH;
	PHPExcel_Shared_Font::setTrueTypeFontPath('C:/Windows/Fonts/');
	PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_APPROX);
	// A choix, librairie tcPDF, mPDF ou domPDF
	$rendererName = PHPExcel_Settings::PDF_RENDERER_TCPDF;
	$rendererLibrary = 'tcpdf';
	$rendererLibraryPath =  "class/" . $rendererLibrary;
	//  Here's the magic: you __tell__ PHPExcel what rendering engine to use
	//     and where the library is located in your filesystem
	if (!PHPExcel_Settings::setPdfRenderer(
			$rendererName,
			$rendererLibraryPath
	)) {
		die('NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
				'<br />' .
				'at the top of this script as appropriate for your directory structure'
		);
	}
	$db = DB::getInstance();
	$conf = Configuracion::getInstance();
	/*
	 * Sacamos los datos del generado
	 */
	$sql_g = "Select g.*, e.emp_nombre from ci_generado g Join empresa e On g.id_empresa = e.id_empresa where g.id_generado=".$_GET['ig'];
	$run_g = $db->ejecutar($sql_g);
	$row_g = mysqli_fetch_array($run_g);
	//
	$phpExcel = new PHPExcel();			
	$phpExcel->getProperties()
		->setCreator('Impressa Repuestos')
		->setTitle('Consumos para Autoriacion')
		->setLastModifiedBy('Sistemas de Tecnologia')
		->setDescription('Reporte de consumo interno para autorizacion')
		->setSubject('Consumos Internos para Autorizacion')
		->setKeywords('cnosumo interno uso sics impressa proveeduria')
		->setCategory('reportes');	
	$phpExcel->setActiveSheetIndex(0);
	$phpExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
	$phpExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
	$phpExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
	$phpExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
	$phpExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
	$phpExcel->getActiveSheet()->getSheetView()->setView(PHPExcel_Worksheet_SheetView::SHEETVIEW_PAGE_LAYOUT);
	$sql = "SELECT
			d.*,
			a.ci_observacion,
			a.ci_enc_fecha,
			cc.cc_descripcion
			FROM
			ci_enc a
			INNER JOIN cecosto cc ON cc.id_cc = a.id_cc and cc.id_empresa = a.id_empresa
			INNER JOIN ci_det d ON d.id_ci = a.id_ci
			WHERE
			a.id_ci >= ".$row_g['per_desde']." and a.id_ci <= ".$row_g['per_hasta']." and
			a.id_empresa = ".$row_g['id_empresa']."
			ORDER BY
			a.ci_enc_fecha, a.id_cc";
	$run = $db->ejecutar($sql);
	$phpExcelSheet = $phpExcel->getSheet(0);
	$phpExcelSheet->setTitle("datos");
	$phpExcelSheet->setCellValue("a1", "REPORTE CONSOLIDADO DE CONSUMOS INTERNOS PASADOS A PROVEEDURIA PARA ".$row_g['emp_nombre']." DEL ".$row_g['per_inicio']." AL ".$row_g['per_fin']);
	$phpExcelSheet->setCellValue("a2", "FECHA");
	$phpExcelSheet->setCellValue("b2", "CENTRO DE COSTO");
	$phpExcelSheet->setCellValue("c2", "N CONSUMO");
	$phpExcelSheet->setCellValue("d2", "CANTIDAD");
	$phpExcelSheet->setCellValue("e2", "CODIGO");
	$phpExcelSheet->setCellValue("f2", "DESCRIPCION");
	$phpExcelSheet->setCellValue("g2", "OBSERVACION");
	$header = 'a1:g1';
	$phpExcelSheet->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
	$style = array(
			'font' => array('bold' => true,),
			'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
	);
	$phpExcelSheet->getStyle($header)->applyFromArray($style);
	$i = 3;
	$sumas = 0;
	while ($registro = mysqli_fetch_object ($run)) {
		$phpExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $registro->ci_enc_fecha)
		->setCellValue('B'.$i, $registro->cc_descripcion)
		->setCellValue('C'.$i, $registro->ci_numero)
		->setCellValue('D'.$i, $registro->ci_det_cantidad)
		->setCellValue('E'.$i, $registro->prod_codigo)
		->setCellValue('F'.$i, $registro->prod_descripcion)
		->setCellValue('G'.$i, $registro->ci_observacion);
		$i++;
	}
	// Calculate the column widths
	foreach(range('A', 'G') as $columnID) {
		$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(false);
	}
	$phpExcel->getActiveSheet()->calculateColumnWidths();
	$phpExcelSheet->mergeCells("a1:g1");
	// Set setAutoSize(false) so that the widths are not recalculated
	foreach(range('A', 'G') as $columnID) {
		$phpExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
	}
	$writer = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
	$phpExcel->setActiveSheetIndex(0);
	$file = $_SESSION['u'].'_ci.xlsx';
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$file.'"');
	header('Cache-Control: max-age=0');
	$writer->save('php://output');
					
}