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
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$ccs = listarCcUsuario();
	$solcs = listarSolcs();
	require_once 'view/solc/listado.php';
}
function catDevolver(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set id_categoria = 0,"
		." gestion_nivel = '0', "
		." prehsol_nuevo_gestion = '0',"
		." prehsol_estado = 4,"
		." obs_cate = NULL "
		." Where id_prehsol = ".$_REQUEST["h"]." and prehsol_estado in (2,4,5) and ifnull(prehsol_aprobacion_categoria,'')='' ";
	$db->ejecutar($sql);
	/*$sql = "Update ".$conf->getTbl_predsol()
		." Set predsol_estado = 10"
		." Where id_prehsol = ".$_REQUEST["h"]." and predsol_estado=5";
	$db->ejecutar($sql);*/
	// Ponemos el estado de devuelto
	$observacion = (
						empty($_REQUEST['prehsol_aprobacion_categoria']) 
						? "" 
						: trim($_REQUEST['prehsol_aprobacion_categoria'])
				   );
	$observacion = str_pad($observacion, 75," ",STR_PAD_RIGHT);
	$observacion = trim(substr($observacion, 0, 75));
	$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
		."id_prehsol = ".$_REQUEST['h'].", "
		."prehsol_stat = 20, "
		."prehsol_stat_desc = '".$conf->getEstadoSC('20')."', "
		."prehsol_stat_fecha = '".date("Y-m-d")."', "
		."prehsol_stat_hora = '".date("H:i:s")."', "
		."prehsol_stat_usuario = '".$_SESSION['u']."',"
		."prehsol_devolver = '".$observacion."'";
	$db->ejecutar($sql_st);
	echo '<div class="bs-calltoaction bs-calltoaction-success">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">La solicitud ha sido devuelta con exito!</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="'.($conf->return_menu()!='' ? $conf->return_menu()  : "?c=solc&a=gescat&es=".$_REQUEST['es']).'" class="btn btn-lg btn-block btn-success">Regresar</a>
                        </div>
                     </div>
                </div>';
}

/*
 * Listado de Solicitudes para Gestor
*/
function gestor(){
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	if(isset($_GET['ps']) && trim($_GET['ps']) != ''){
		$sql = "Update ".$conf->getTbl_prehsol()
			." Set"
			." prehsol_revisando=''"
			." Where id_prehsol = ".$_GET['ps'];
		$db->ejecutar($sql);
	}
	$solcs = listarSolcsGes();
	require_once 'view/solc/listges.php';
}
/*
 * Nueva solicitud de compra
 */
function crear(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$permisos = permisosURL();
	if ($_POST) {
		if(isset($_POST['prehsol_numero']) && !empty($_POST['prehsol_numero']) ) {
			//echo '<pre>';
			//print_r($_POST);
			//print_r($_FILES);
			//echo '</pre>';
			$sql_ver = "Select prehsol_estado From ".$conf->getTbl_prehsol()
				." Where "
				."id_empresa = ".$_POST['id_empresa']." And "
				."id_cc = ".$_POST['id_cc']." And "
				."prehsol_numero = ".$_POST['prehsol_numero'];
			$run_sql = $db->ejecutar($sql_ver);
			if (mysqli_num_rows($run_sql) <= 0) {
				$rtn = 1;
				$msg = 'La presolicitud ha dejado de existir';
			} else {
				$row = mysqli_fetch_array($run_sql);
				if ($row[0] > 1) {
					$rtn = 1;
					$msg = 'su estado ha sido <b>'.$conf->getEstadoSC($row[0]).'</b>, presione F5 para actualizar.';
				} else  {
					$valid_file = true;
					$currentdir = getcwd();
					$msg = '';
					$new_file_name1 = '';
					$new_file_name2 = '';
					$new_file_name3 = '';
					$target1 = '';
					$target2 = '';
					$target3 = '';
					/*
					// Archivo 1
					if($_FILES['predsol_coti1']['name']){
						if(!$_FILES['predsol_coti1']['error']){
							$new_file_name1 = 'uploads/' . $_POST ['id_prehsol'].basename(str_replace(" ", "", $_FILES['predsol_coti1']['name']));
							$target1 = $currentdir .'/uploads/' . $_POST ['id_prehsol'].basename(str_replace(" ", "", $_FILES['predsol_coti1']['name']));
							if($_FILES['predsol_coti1']['size'] > (3072000)){
								$valid_file = false;
								$msg = 'Oops!  Your file\'s size is to large.';
							}
						}
					}
					// Archivo 2
					if($_FILES['predsol_coti2']['name']){
						if(!$_FILES['predsol_coti2']['error']){
							$new_file_name2 = 'uploads/' . $_POST ['id_prehsol'].basename(str_replace(" ", "", $_FILES['predsol_coti2']['name']));
							$target2 = $currentdir .'/uploads/' . $_POST ['id_prehsol'].basename(str_replace(" ", "", $_FILES['predsol_coti2']['name']));
							if($_FILES['predsol_coti2']['size'] > (3072000)){
								$valid_file = false;
								$msg = 'Oops!  Your file\'s size is to large.';
							}
						}
					}
					// Archivo 3
					if($_FILES['predsol_coti3']['name']){
						if(!$_FILES['predsol_coti3']['error']){
							$new_file_name3 = 'uploads/' . $_POST ['id_prehsol'].basename(str_replace(" ", "", $_FILES['predsol_coti3']['name']));
							$target3 = $currentdir .'/uploads/' . $_POST ['id_prehsol'].basename(str_replace(" ", "", $_FILES['predsol_coti3']['name']));
							if($_FILES['predsol_coti3']['size'] > (3072000)){
								$valid_file = false;
								$msg = 'Oops!  Your file\'s size is to large.';
							}
						}
					}*/
					//if the file has passed the test
					if($valid_file){
						//move it to where we want it to be
						//move_uploaded_file($_FILES['predsol_coti1']['tmp_name'], $target1);
						//move_uploaded_file($_FILES['predsol_coti2']['tmp_name'], $target2);
						//move_uploaded_file($_FILES['predsol_coti3']['tmp_name'], $target3);
						$desc_sol = strtoupper($_POST ['predsol_descripcion']);
						$desc_sol = str_replace("\\", "\\\\", $desc_sol);
						$desc_sol = str_replace("'", "''", $desc_sol);
						$sql = "INSERT INTO " . $conf->getTbl_predsol() . " SET "
							. "id_prehsol = " . $_POST ['id_prehsol'] . ", "
							. "id_usuario = " . $_POST ['id_usuario'] . ", "
							. "id_empresa = " . $_POST ['id_empresa'] . ", "
							. "id_cc = " . $_POST ['id_cc'] . ", "
							. "predsol_cantidad = " . $_POST ['predsol_cantidad'] . ", "
							. "predsol_unidad = '" . strtoupper($_POST ['predsol_unidad']) . "', "
							. "predsol_coti1 = '" . $target1 . "', "
							. "predsol_coti2 = '" . $target2 . "', "
							. "predsol_coti3 = '" . $target3 . "', "
							. "predsol_coti1_file = '" . $new_file_name1 . "', "
							. "predsol_coti2_file = '" . $new_file_name2 . "', "
							. "predsol_coti3_file = '" . $new_file_name3 . "', "
							. "predsol_cantidad_aut = " . $_POST ['predsol_cantidad'] . ", "
							. "predsol_descripcion = '" . $desc_sol . "', "
							. "predsol_hora = '" . date ( 'H:i:s' ) . "', "
							. "predsol_fecha = '" . date ( 'Y-m-d' ) . "', "
							. "predsol_usuario = '" . strtoupper ( $_POST ['predsol_usuario'] ) . "'";
						//echo $sql;
						$db->ejecutar($sql);
						$_POST = array();
						$url = 'Location : ?'.$_SERVER["QUERY_STRING"];
						header($url);
					} else {
						echo '<div class="alert alert-error">';
						echo $msg;
						echo '</div>';
					}
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
				header($url);
			}
		}
	}
	$infohsol = infoPreHSolc($_GET['ps'],$_GET['cs'],$_GET['es']);
	$infohsol_stat = infoPreHSolc_stat($infohsol[0]['id_prehsol']);

	//echo $infohsol[0]['id_prehsol'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa'];
	$detas = detPreDSolc($infohsol[0]['id_prehsol'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa']);
	$cats = listarCategorias1();
	require_once 'view/solc/nueva_form.php';
}
function depurar($obj){
	echo "<pre>";
	print_r($obj);
	echo "</pre>";
}
function get_status($estado,$descr){
	$estado = (int)$estado;
	if($estado==1){
		$descr = "Solicitante";
	}else if($estado==4){
		$descr = "Enviado a Proveeduria";
	}
	return strtoupper($descr);
}
/*
 * Nueva solicitud de compra
*/
function trabajo(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$permisos = permisosURL();
	/*if ($_POST) {
		print_r($_POST);
	}*/	
	$infohsol = infoPreHSolc($_GET['ps'],$_GET['cs'],$_GET['es']);
	$Empressa = $infohsol[0]['id_empresa'];
	$detas = detPreDSolc($infohsol[0]['id_prehsol'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa']);
	$sql_pr = "Select id_proveedor, prov_nombre From ".$conf->getTbl_proveedor()." Order by prov_nombre";
	$res_pr = $db->ejecutar($sql_pr);
	$provs = array();
	$prs = array();
	while ($row_pr = mysqli_fetch_array($res_pr)){
		if(!empty($row_pr[1])) {
			$provs[] = $row_pr;
			$prs[$row_pr[0]] = $row_pr;
		}
	}
	$sql_cc = "Select id_cc, cc_codigo, cc_descripcion From ".$conf->getTbl_cecosto()." Order by cc_descripcion";
	$res_cc = $db->ejecutar($sql_cc);
	$ccs = array();
	while ($row_cc = mysqli_fetch_array($res_cc)){
		$ccs[$row_cc[0]] = $row_cc;
	}
	if($infohsol[0]['prehsol_estado'] == 4){
		$sql = "Update ".$conf->getTbl_prehsol()
			." Set prehsol_estado = 5,"
			." prehsol_revision='".$_SESSION['u']."', "
			." prehsol_revision_fecha='".date('Y-m-d H:i:s')."', "
			." prehsol_revisando='".$_SESSION['u']."'"
			." Where id_prehsol = ".$infohsol[0]['id_prehsol'];
		$db->ejecutar($sql);
		// Ponemos el estado de negado
		$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
			."id_prehsol = ".$infohsol[0]['id_prehsol'].", "
			."prehsol_stat = 5, "
			."prehsol_stat_desc = '".$conf->getEstadoSC('5')."', "
			."prehsol_stat_fecha = '".date("Y-m-d")."', "
			."prehsol_stat_hora = '".date("H:i:s")."', "
			."prehsol_stat_usuario = '".$_SESSION['u']."'";
		$db->ejecutar($sql_st);
	}
	
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set"
		." prehsol_revisando='".$_SESSION['u']."'"
		." Where id_prehsol = ".$infohsol[0]['id_prehsol'];
	$db->ejecutar($sql);	

	$metodos = MetodosPagoOC();
	$cates = listarCategorias();
	$infohsol_stat = infoPreHSolc_stat($infohsol[0]['id_prehsol']);

	$Proveedor = ReturnProveedor($infohsol[0]['prehsol_Proveedor']);
	$metodo_pago = ReturnNombreMetodoPago($infohsol[0]['prehsol_MetodoPago']);

	if($Empressa == 6 || $Empressa== 8){
		$NewMoneda=ReturnMonedaEmpressa($Empressa);
	}

	require_once 'view/solc/trabaja_form.php';
}
/*
 * Borrar requisicion
*/
function borrar(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Delete From ".$conf->getTbl_prehsol()
	." Where id_prehsol = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	$sql = "Delete From ".$conf->getTbl_predsol()
	." Where id_prehsol = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	$sql = "Delete From ".$conf->getTbl_prehsol_stat()
	." Where id_prehsol = ".$_REQUEST["ps"];
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
                            <a href="?c=solc&a=inicio&id=5" class="btn btn-lg btn-block btn-success">Regresar</a>
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
	$sql = "Select count(id_prehsol) as total From ".$conf->getTbl_predsol()." Where id_prehsol = ".$_REQUEST["ps"];
	$run = $db->ejecutar($sql);
	$row = mysqli_fetch_array($run);
	if($row[0] > 0) {
		$valid_file = true;
		$valid1 = true;
		$valid2 = true;
		$valid3 = true;
		$new_file_name1= '';
		$new_file_name2= '';
		$new_file_name3= '';
		$target1 ='';
		$target2 ='';
		$target3 ='';
		/*print_r($_REQUEST);
		print_r($_FILES);*/
		$currentdir = getcwd();
		$msg= '';
		if($_FILES['predsol_coti1']['name']){ // Cotizacion 1
			//if no errors...
			if(!$_FILES['predsol_coti1']['error']){
				//now is the time to modify the future file name and validate the file
				$new_file_name1 = strtolower($_FILES['predsol_coti1']['tmp_name']); //rename file
				$target1 = $currentdir ."\\uploads\\" . $_REQUEST['ps'] . basename($_FILES['predsol_coti1']['name']);
				if($_FILES['predsol_coti1']['size'] > (2048000)) { //can't be larger than 2 MB
					$valid_file = false;
					$valid1 = false;
					$msg = 'Oops!  El archivo es demasiado grande. '.basename($_FILES['predsol_coti1']['name']);
				}
			} else { //set that to be the returned message
				$valid_file = false;
				$valid1 = false;
				$msg = 'Ooops!  Ha ocurrido el siguiente error al subir el archivo:  '.$_FILES['predsol_coti1']['error'];
			}
		} else {
			$valid1 = false;
		}
		if($_FILES['predsol_coti2']['name']){ // Cotizacion 2
			//if no errors...
			if(!$_FILES['predsol_coti2']['error']){
				//now is the time to modify the future file name and validate the file
				$new_file_name2 = strtolower($_FILES['predsol_coti2']['tmp_name']); //rename file
				$target2 = $currentdir ."\\uploads\\" . $_REQUEST['ps'] . basename($_FILES['predsol_coti2']['name']);
				if($_FILES['predsol_coti2']['size'] > (2048000)) { //can't be larger than 2 MB
					$valid_file = false;
					$valid2 = false;
					$msg = 'Oops!  El archivo es demasiado grande. '.basename($_FILES['predsol_coti2']['name']);
				}
			} else { //set that to be the returned message
				$valid_file = false;
				$valid2 = false;
				$msg = 'Ooops!  Ha ocurrido el siguiente error al subir el archivo:  '.$_FILES['predsol_coti2']['error'];
			}
		} else {
			$valid2 = false;
		}
		if($_FILES['predsol_coti3']['name']){ // Cotizacion 3
			//if no errors...
			if(!$_FILES['predsol_coti3']['error']){
				//now is the time to modify the future file name and validate the file
				$new_file_name3 = strtolower($_FILES['predsol_coti3']['tmp_name']); //rename file
				$target3 = $currentdir ."\\uploads\\" . $_REQUEST['ps'] . basename($_FILES['predsol_coti3']['name']);
				if($_FILES['predsol_coti3']['size'] > (2048000)) { //can't be larger than 2 MB
					$valid_file = false;
					$valid3 = false;
					$msg = 'Oops!  El archivo es demasiado grande. '.basename($_FILES['predsol_coti3']['name']);
				}
			} else { //set that to be the returned message
				$valid_file = false;
				$valid3 = false;
				$msg = 'Ooops!  Ha ocurrido el siguiente error al subir el archivo:  '.$_FILES['predsol_coti3']['error'];
			}
		} else {
			$valid3 = false;
		}
		
		// si lleva adjunto y todos son validos, pasa!
		if($valid_file){
			// Movemos los archivos adjuntos a una carpeta temporal para ser procesados luego
			if($valid1) {
				move_uploaded_file($_FILES['predsol_coti1']['tmp_name'], $target1);
			}
			if($valid2) {
				move_uploaded_file($_FILES['predsol_coti2']['tmp_name'], $target2);
			}
			if($valid3){
				move_uploaded_file($_FILES['predsol_coti3']['tmp_name'], $target3);
			}
			
			
			$sql_user = "Select a.id_usuario, u.usr_nombre, u.usr_email From ".$conf->getTbl_acc_emp_cc()." a"
				." Join ".$conf->getTbl_usuario()." u"
				." On u.id_usuario = a.id_usuario"
				." Where a.id_empresa = ".$_REQUEST['es']
				." and a.id_cc = ".$_REQUEST['cs']
				." and u.id_rol = 999999995";
			$run_user = $db->ejecutar($sql_user);
			$conta_email = 0;
			while ($row_user = mysqli_fetch_array($run_user)){
				if(!empty($row_user[2])) {
					$conta_email++;
				}
				//print ($row_user[0] .'=>'. $row_user[1].'=>'. $row_user[2].'<br>');
			}
			// si tiene email asignado para enviar autorizacion
			if($conta_email <= 0) {
				echo '<div class="bs-calltoaction bs-calltoaction-danger">
					<div class="row">
	                   <div class="col-md-9 cta-contents">
	                      <h1 class="cta-title">Lo sentimos, ha ocurrido un error!</h1>
	                      <div class="cta-desc">
	                        <h4>No se ha definido correo de notificacion para aprobar, favor informe a Proveeduria</h4>
	                      </div>
	                   </div>
	                   <div class="col-md-3 cta-button">
	                      <a href="?c=solc&a=inicio&id=5" class="btn btn-lg btn-block btn-danger">Regresar</a>
	                   </div>
	                 </div>
	              </div>';
			} else {
				// Llenamos campos temporales para manejar las cotizaciones adjuntas
				$sql = "Update ".$conf->getTbl_prehsol()
					." Set prehsol_estado = 1,"
					." prehsol_coti1 = '".mysqli_real_escape_string($target1)."',"
					." prehsol_coti2 = '".mysqli_real_escape_string($target2)."',"
					." prehsol_coti3 = '".mysqli_real_escape_string($target3)."',"		
					." prehsol_obs1 = '".mysqli_real_escape_string($_REQUEST['observa_sol'])."'"
					." Where id_prehsol = ".$_REQUEST["ps"];
				$db->ejecutar($sql);
				// Actualizamos el estado a enviado para autorizacion
				$sql = "Update ".$conf->getTbl_predsol()
					." Set predsol_estado = 1"
					." Where id_prehsol = ".$_REQUEST["ps"];
				$db->ejecutar($sql);
				// Ponemos el estado de enviado autorizacion
				$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
					."id_prehsol = ".$_REQUEST['ps'].", "
					."prehsol_stat = 1, "
					."prehsol_stat_desc = '".$conf->getEstadoSC('1')."', "
					."prehsol_stat_fecha = '".date("Y-m-d")."', "
					."prehsol_stat_hora = '".date("H:i:s")."', "
					."prehsol_stat_usuario = '".$_SESSION['u']."'";
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
		                            <a href="?c=solc&a=inicio&id=5" class="btn btn-lg btn-block btn-primary">Regresar</a>
		                        </div>
		                     </div>
		                </div>';
				header('Location: class/PHPMailer/send.php?'.$_REQUEST['ps']);
				//header('Location: /sics/class/PHPMailer/send.php?'.$_REQUEST['ps']);
			}
		} else {
			echo '<div class="bs-calltoaction bs-calltoaction-danger">
					<div class="row">
	                   <div class="col-md-9 cta-contents">
	                      <h1 class="cta-title">Lo sentimos, ha ocurrido un error!</h1>
	                      <div class="cta-desc">
	                        <h4>'.$msg.'</h4>
	                      </div>
	                   </div>
	                   <div class="col-md-3 cta-button">
	                      <a href="?c=solc&a=inicio&id=5" class="btn btn-lg btn-block btn-danger">Regresar</a>
	                   </div>
	                 </div>
	              </div>';
			//header('Location: /sics@/class/PHPMailer/example.php');
			/*try {
				$mail = new PHPMailer(true);
			} catch (phpmailerException $e) {
				echo $e->errorMessage();
			} catch (Exception $e){
				echo $e->getMessage();
			}
			$mail->SMTPDebug = 0;
			$mail->isSMTP();
			$mail->Host = '192.168.43.130';
			$mail->setFrom('from@example.com', 'Mailer');
			$mail->addAddress('wserpas@impressa.com', 'Joe User');     // Add a recipient
			$mail->addReplyTo('info@example.com', 'Information');
			$mail->addAttachment($target1);         // Add attachments
			$mail->addAttachment($target2);    // Optional name
			$mail->addAttachment($target3);    // Optional name
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = 'Here is the subject';
			$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
			if(!$mail->send()) {
				echo 'Message could not be sent.';
				echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
				echo 'Message has been sent';
			}*/
		}
	} else {
		echo '<div class="bs-calltoaction bs-calltoaction-danger">
	                    <div class="row">
	                        <div class="col-md-9 cta-contents">
	                            <h1 class="cta-title">Lo sentimos, ha ocurrido un error!</h1>
	                            <div class="cta-desc">
	                                <h4>La solicitud que desea enviar para autorizacion esta vacia.</h4>
	                            </div>
	                        </div>
	                        <div class="col-md-3 cta-button">
	                            <a href="?c=solc&a=inicio&id=5" class="btn btn-lg btn-block btn-danger">Regresar</a>
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
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set prehsol_estado = 10"
		." Where id_prehsol = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	$sql = "Update ".$conf->getTbl_predsol()
		." Set predsol_estado = 10"
		." Where id_prehsol = ".$_REQUEST["ps"];
	$db->ejecutar($sql);
	// Ponemos el estado de negado
	$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
		."id_prehsol = ".$_REQUEST['ps'].", "
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
                            <a href="'.($conf->return_menu()!='' ? $conf->return_menu() : "?c=solc&a=inicio&id=5").'" class="btn btn-lg btn-block btn-success">Regresar</a>
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
	header('Location: /sics/class/PHPMailer/send2.php?'.$_REQUEST['ps']);
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
	require_once 'model/solcModel.php';
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
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstadosSC();
	$permisos = permisosURL();
	$emps = listarEmpUsuario();
	$id_empresa = (!empty($_POST) ? $_POST['empresa'] : '');
	$id_cc = (!empty($_POST) ? $_POST['centrocosto'] : '');
	$no_solicitud = (!empty($_POST) ? $_POST['numero'] : '');
	$no_estado = (!empty($_POST) ? $_POST['status'] : '');
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
	require_once 'view/solc/consulta.php';
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
		if($_POST['accion'] == 'cc') {
			$ccupd = $_POST['cc'];
			if($_POST['cc'] == '0') {
				$ccupd = $_POST['cs'];
			}
			$sql = "Update " . $conf->getTbl_predsol() . " Set "
				."id_cc = ".$ccupd
				." Where id_predsol = ".$_GET['pd'];
			//echo $sql;
			$db->ejecutar($sql);
		}
		if($_POST['accion'] == 'proveedor') {
			$sql = "Update " . $conf->getTbl_predsol() . " Set "
					."id_proveedor = ".$_POST['proveedor']
					." Where id_predsol = ".$_GET['pd'];
			//echo $sql;
			$db->ejecutar($sql);
		}
    	header('Location: ?c=solc&a=trabajoi&id=5&pd='.$_REQUEST['pd']);
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
	$campogasto = 's.id_tabgas'.$infohsol['id_empresa'];
	$sql_p = "Select p.id_producto, p.prod_codigo, p.prod_descripcion, p.sl_linea, p.sl_sublinea, t.gas_tit_codigo, t.gas_det_codigo From ".$conf->getTbl_producto(). " p "
		."Join ".$conf->getTbl_sublinea()." s "
		."On s.sl_linea = p.sl_linea And "
		."s.sl_sublinea = p.sl_sublinea "
		."Join ".$conf->getTbl_tagasto()." t "
		."On t.id_tagasto = ".$campogasto
		." Where p.prod_solc = 1 Order By p.prod_descripcion";
	$res_p = $db->ejecutar($sql_p);
	while($row_p = mysqli_fetch_array($res_p)) {
		$prods[] = $row_p;
	}
	
	$sql_pr = "Select id_proveedor, prov_nombre From ".$conf->getTbl_proveedor()." Order by prov_nombre";
	$res_pr = $db->ejecutar($sql_pr);
	$provs = array();
	$prv = array();
	while ($row_pr = mysqli_fetch_array($res_pr)){
		if(!empty($row_pr[1])) {
			$provs[] = $row_pr;
			$prv[$row_pr[0]] = $row_pr;
		}
	}
	$sql_cc = "Select id_cc, cc_codigo, cc_descripcion From ".$conf->getTbl_cecosto()
		." Where id_empresa = ".$infohsol['id_empresa']
		." Order by cc_descripcion";
	$res_cc = $db->ejecutar($sql_cc);
	$ccs = array();
	$ccd = array();
	while ($row_cc = mysqli_fetch_array($res_cc)){
		$ccs[] = $row_cc;
		$ccd[$row_cc[0]] = $row_cc;
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
			header('Location: ?c=solc&a=trabajoi&id=5&pd='.$_REQUEST['pd']);
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
	$campogasto = 's.id_tabgas'.$infohsol['id_empresa'];
	$sql_p = "Select s.id_sublinea, s.sl_linea, s.sl_sublinea, s.sl_descripcion, t.gas_tit_codigo, t.gas_det_codigo From ".$conf->getTbl_sublinea()." s"
		." Join ".$conf->getTbl_tagasto()." t On"
		." t.id_tagasto = ".$campogasto
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
	
	// Encabezado
	$sql_h = "Select * From ".$conf->getTbl_prehsol(). " Where id_prehsol = ".$_REQUEST['ps'];
	$res_h = $db->ejecutar($sql_h);
	$row_h = mysqli_fetch_array($res_h);
	$hs = $row_h;
	
	// Datos de empresa
	$sql_emp = "Select * From ".$conf->getTbl_empresa()
	." Where id_empresa = ".$hs['id_empresa'];
	$run_emp = $db->ejecutar($sql_emp);
	$row_emp = mysqli_fetch_array($run_emp);
	
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
		$row_d['emp_usa_presupuesto'] = $row_emp['emp_usa_presupuesto'];
		$ds[] = $row_d;
		if(empty($row_d['prod_codigo']) || $row_d['predsol_prec_uni'] <= 0) {
			$stop1 = 1;
		}
		$gastod = str_pad($row_d['predsol_titgas'],2,'0',STR_PAD_LEFT).str_pad($row_d['predsol_detgas'],2,'0',STR_PAD_LEFT);
		if ($row_emp['emp_usa_presupuesto'] == '1' && $gastod == "0000") {
			$stop1 = 1;
		}
	}
	
	// Presupuesto
	if($_REQUEST['tipogasto'] == 2 & $stop1 == 0) {
		
		$fecha_prehreq = $hs['prehsol_fecha'];
		$d = date_parse_from_format('Y-m-d', $hs['prehsol_fecha']);
		$mes = $d["month"];
		$year = $d["year"];
		
		$stop2 = 0;
		$res_d = $db->ejecutar($sql_d);
		$msg1 = '';
		while($row_d = mysqli_fetch_array($res_d)){
			$gastod = str_pad($row_d['predsol_titgas'],2,'0',STR_PAD_LEFT).str_pad($row_d['predsol_detgas'],2,'0',STR_PAD_LEFT);
			//echo $gastod.'<br>';
			if ($row_emp['emp_usa_presupuesto'] == '1' && $gastod == "0000") {
				$rtn=1;
				$msg = "Producto No tiene asigancion de gasto, contacte a Proveduria";
				$msg1 = "Producto No tiene asigancion de gasto, contacte a Proveduria";
			} else {
			// Empresa debe poseer presupuesto
			if ($row_emp['emp_usa_presupuesto'] == '1' && $row_emp['emp_origen_presupuesto'] == "AS400") {
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
							$msg1 .= '<p class="list-group-item-text">1.-Presupuesto disponible : $'.($dispo2).', para Centro de Costo : '.$cc.'<br>';
							$msg1 .= "Valor del item : $".$Total_Orden.', ';
							$msg1 .= "para cuenta de gasto : ".$gas2;
							$msg1 .= $o->dispo2D;
							$msg1 .= '</p>';
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
								$msg1 .= '<p class="list-group-item-text">2.-Presupuesto disponible : $ '.($dispo2).', para Centro de Costo : '.$cc.'<br>';
								$msg1 .= "Valor del item : $".$Total_Orden.", ";
								$msg1 .= "para cuenta de gasto : ".$gas2;
								$msg1 .= $o->dispo2;
								$msg1 .= '</p>';
								$stop2 = 1;
							}
						}
					}
				}
			}
		}	
		}
		if($stop2 == 1) {
			
			/*
			 * Debemos generar un resumen de los gastos para autorizacion
			 */
			$sql_d = "Select p.id_prehsol, p.id_cc, c.cc_codigo, p.predsol_titgas, p.predsol_detgas, "
				."sum(p.predsol_cantidad_aut*p.predsol_prec_uni) as valor_total "
				."From ".$conf->getTbl_predsol()." p "
				."Join ".$conf->getTbl_cecosto()." c "
				."On c.id_cc = p.id_cc " 
				."Where p.id_prehsol = ".$_REQUEST['ps']
				." Group by p.id_prehsol, p.id_cc, c.cc_codigo, p.predsol_titgas, p.predsol_detgas";
			$res_d = $db->ejecutar($sql_d);
			$msg2 = "";
			while($row_d = mysqli_fetch_array($res_d)){
				$gastod = str_pad($row_d['predsol_titgas'],2,'0',STR_PAD_LEFT).str_pad($row_d['predsol_detgas'],2,'0',STR_PAD_LEFT);
				//echo $gastod.'<br>';
				// Empresa debe poseer presupuesto
				if ($row_emp['emp_usa_presupuesto'] == '1' && $row_emp['emp_origen_presupuesto'] == "AS400") {
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

						$Total_Orden = round(($row_d['valor_total']),2); // Precion Total del Item
			
						if ($bandera=='S') {
							$dispo2 = 0.00;
							$o->Dispo2($Cia,$cc,$gas2,$month,$fecha_prehreq);
							$dispo2 = $o->dispo2;
							if ($dispo2 < $Total_Orden) {
								if($dispo2 < 0){
									$dispo2 = 0;
								}
								$msg2 .= '<p class="list-group-item-text">3.-Presupuesto disponible : $'.($dispo2).', para Centro de Costo : '.$cc.'<br>';
								$msg2 .= "Valor del item : $".$Total_Orden.', ';
								$msg2 .= "para cuenta de gasto : ".$gas2;
								$msg2 .= $o->dispo2D;
								$msg2 .= '</p>';
							}
						} else {
							$monto = $o->MontoPresupuesto($year,$Cia,$gas2,$cc,$month,'2'); // Presupuesto de CC
							$auth = $o->MontoAutorizado($year,$Cia,$gas2,$cc,$month,'2'); // Incrementos
							$decrem = $o->MontoDecremento($year,$Cia,$gas2,$cc,$month); // Decrementos
							$presupuesto_neto = ($monto+$auth)-$decrem; //Presupuesto + Incrementos - Decrementos
							$gastoSalidas = $o->GastoMS($year,$Cia,$gas2,$cc,$month); // Gastos Salidas
							$gastoEntradas = $o->GastoM($year,$Cia,$gas2,$cc,$month); // Gastos Entradas
							$gastoNeto = $gastoSalidas-$gastoEntradas; //Gato Neto = Salidas - Entradas
							$Presupuesto_Disponible = $presupuesto_neto-$gastoNeto; // Disponible
							$Presupuesto_Disponible = round($Presupuesto_Disponible,2);
							if ($Presupuesto_Disponible < $Total_Orden){
								$dispo2 = $Presupuesto_Disponible;
								if ($dispo2 < $Total_Orden) {
									if($dispo2 < 0){
										$dispo2 = 0;
									}
									$msg2 .= '<p class="list-group-item-text">4.-Presupuesto disponible : $ '.($dispo2).', para Centro de Costo : '.$cc.'<br>';
									$msg2 .= "Valor del item : $".$Total_Orden.", ";
									$msg2 .= "para cuenta de gasto : ".$gas2;
									$msg2 .= $o->dispo2D;
									$msg2 .= '</p>';
								}
							}
						}
					}
				}
			}
			
			
			$sql_pone_string = "Update ".$conf->getTbl_prehsol()
				." Set prehsol_verificacion = '".trim($msg1)."', "
				." prehsol_verificacion2 = '".trim($msg2)."',"
				." prehsol_verificacion_usuario = '".$_SESSION['u']."'"
				." Where id_prehsol=".$hs['id_prehsol'];
			$db->ejecutar($sql_pone_string);
		}
		// todo Ok, pasamos 
		if($rtn == 0) {
			//header('Location: /sics@/class/PHPMailer/send3.php?'.$hs['id_prehsol']);
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
		$db->conecta_OC();
		//Creamos primero en encabezado de la O.C.
		$sql = "Select NORDEN From empresas Where CODIGO = '".$infE[0]['id_empresa_oc']."'";
		$result = $db->ejecuta_OC($sql);
		$row = mysqli_fetch_array($result);
		// Numero de orden a crear
		$Numero = $row[0]+1;
		$orden = $Numero;
		// Actualizamos el correlativo para la empresa
		$_SqlO = "Update empresas Set NORDEN = '".$Numero."' Where CODIGO ='".$infE[0]['id_empresa_oc']."'";
		$db->ejecuta_OC($_SqlO);
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
			$descdet = $row_d['predsol_descripcion'];
			$descdet = 	str_replace(",", "", $descdet);	
			$descdet = 	str_replace("'", "''", $descdet);
			$descdet = 	str_replace("\\", "\\\\", $descdet);
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
				."'".str_pad($row_d['predsol_titgas'], 2, "0", STR_PAD_LEFT)."', "
				."'".str_pad($row_d['predsol_detgas'], 2, "0", STR_PAD_LEFT)."', "
				.$row_d['predsol_cantidad'].", "
				."'".$row_d['predsol_unidad']."', "
				."'".$descdet."', "
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
			$tfpago_str = '';
			if(!empty($row_h['prehsol_MetodoPago'])){
				if((int)$row_h['prehsol_MetodoPago']==1){
					$tfpago_str = 'TRANSFERENCIA';
				}
				if((int)$row_h['prehsol_MetodoPago']==2){
					$tfpago_str = 'CHEQUE';
				}
				if((int)$row_h['prehsol_MetodoPago']==3){
					$tfpago_str = 'CHEQUE DE GERENCIA';
				}
			}

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
			."CONDIPAGO='".$infP[0]['prov_dias']."',"
			."MONEDA='".(empty($row_h['moneda']) ? '' : $row_h['moneda'])."', "
			."fpago='".$tfpago_str."', "
			."prehsol_monto='".(empty($row_h['prehsol_monto']) ? 0 : $row_h['prehsol_monto'])."', "
			."prehsol_Proveedor='".(empty($row_h['prehsol_Proveedor']) ? 0 : $row_h['prehsol_Proveedor'])."', "
			."prehsol_MetodoPago='".(empty($row_h['prehsol_MetodoPago']) ? 0 : $row_h['prehsol_MetodoPago'])."', "
			."moneda_metodopago='".(empty($row_h['moneda']) ? '' : $row_h['moneda'])."'";
		$db->ejecuta_OC($_SqlOr);
		// Observaciones
		$_SqlOrde = "Insert Into ".$conf->getTobser().$infE[0]['id_empresa_oc']." Set OBORDEN = '".$orden."'";
		$db->ejecuta_OC($_SqlOrde);
		// Llenamos el datalle
		$db->ejecuta_OC($sql);
		$db->desconecta_OC();
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
		header('Location: /sics/class/PHPMailer/send4.php?'.$row_h['id_prehsol'].'&'.$orden);
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
			.", id_usuario = ".$_POST['usuario']
			.", gestion_nivel = ".$_POST['nivel'];
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
	$Empressa = $infohsol[0]['id_empresa'];
	$infohsol_stat = infoPreHSolc_stat($infohsol[0]['id_prehsol']);
	$detas = detPreDSolc($infohsol[0]['id_prehsol'],$infohsol[0]['id_cc'],$infohsol[0]['id_empresa']);

	$Proveedor = ReturnProveedor($infohsol[0]['prehsol_Proveedor']);
	$metodo_pago = ReturnNombreMetodoPago($infohsol[0]['prehsol_MetodoPago']);

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
	$esta_solc = 5;
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
	
	$fecha_ingreso_compra = '0000-00-00 00:00:00';
	if($esta_solc == 4) {
		$fecha_ingreso_compra = date("Y-m-d H:i:s");
	}
	
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set prehsol_estado = ".$esta_solc.", "
		." prehsol_aprobacion_categoria = '".$_REQUEST['prehsol_aprobacion_categoria']." - ".date('d-m-Y H:i')."', "
		." prehsol_ingreso_compra = '".$fecha_ingreso_compra."', "
		." prehsol_nuevo_gestion = 0,"
		." prehsol_nuevo_gestionado = 1,"
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
	header('Location: /sics/class/PHPMailer/sendCat.php?'.$_REQUEST['id_prehsol']);
}

/*
 * NO Aprueba categoria
 */
function naprbct(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$sql = "Update ".$conf->getTbl_prehsol()
		." Set prehsol_estado = 10,"
		." prehsol_aprobacion_categoria = '".$_REQUEST['prehsol_aprobacion_categoria']." - ".date('d-M-Y H:i')."', "
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
                            <a href="'.($conf->return_menu()!='' ? $conf->return_menu()  : "?c=solc&a=gescat&es=".$_REQUEST['es']).'" class="btn btn-lg btn-block btn-success">Regresar</a>
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
		." prehsol_ingreso_compra = '".date('Y-m-d H:i:s')."', "
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
	header('Location: /sics/class/PHPMailer/sendGest.php?'.$_REQUEST['id_prehsol']);
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

function gesniv1(){
	
	//print_r($_REQUEST);
	
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	/* 
	 * Datos de Encabezado
	 */
	$sql_h = "Select id_categoria From ".$conf->getTbl_prehsol()
		." Where id_prehsol = ".$_REQUEST['h'];
	$run_h = $db->ejecutar($sql_h);
	$row_h = mysqli_fetch_array($run_h);
	// Buscamos el autoriador de categoria de primer nivel
	$sql_estado_cat = "Select gi.id_auto_categoria, gi.id_usuario, u.usr_email, u.usr_nombre From"
			." gestion_categorias gi Join "
			." usuario u On"
			." u.id_usuario = gi.id_usuario"
			." Where id_categoria = ".$row_h['id_categoria']
			." and gestion_nivel=1";
	$run_estado_cat = $db->ejecutar($sql_estado_cat);
	/*
	 * TIENE GESTOR DE CATEGORIA 1er. NIVEL
	*/
	if(mysqli_num_rows($run_estado_cat) > 0) {
	
		// lo mandamos al nivel superior
		$sql = "Update ".$conf->getTbl_prehsol()
			." Set gestion_nivel = 1, "
			." prehsol_gestion_observacion = '".$_REQUEST['prehsol_aprobacion_categoria']."', "
			." prehsol_gestion_nivel2_usuario = '".$_SESSION['u']."'"
			." Where id_prehsol = ".$_REQUEST["h"];
		$db->ejecutar($sql);
		
		echo '<div class="bs-calltoaction bs-calltoaction-success">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">La solicitud ha sido enviada al gestor de nivel inmediato.</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=solc&a=gescat&es='.$row_h['id_categoria'].'" class="btn btn-lg btn-block btn-success">Regresar</a>
                        </div>
                     </div>
                </div>';
		header('Location: /sics/class/PHPMailer/sendniv1.php?'.$_REQUEST['h']);
	} else {
		echo '<div class="bs-calltoaction bs-calltoaction-danger">
                    <div class="row">
                        <div class="col-md-9 cta-contents">
                            <h1 class="cta-title">No se ha definido un autorizador de primer nivel para la categoria.</h1>
                            <div class="cta-desc">
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-3 cta-button">
                            <a href="?c=solc&a=gescat&es='.$row_h['id_categoria'].'" class="btn btn-lg btn-block btn-danger">Regresar</a>
                        </div>
                     </div>
                </div>';
	}
}
/*
 * Modificar Item de solicitud de compra por gestor Nivel 2
 */
function niv1e(){
	//print_r($_REQUEST);
	
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	$infohsol = infoPreHSolc($_GET['ps'],$_GET['cs'],$_GET['es']);
	$detas = detPreDSolcI($_REQUEST['id']);
	//var_dump($detas);
	require_once 'view/solc/itemb_form.php';
}
/*
 * 
 */
function trabajogcia(){
	print_r($_REQUEST);
	
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	
	$sql_d = "Select * From ".$conf->getTbl_predsol()
		." Where id_predsol = ".$_REQUEST['pd'];
	$run_d = $db->ejecutar($sql_d);
	$row_d = mysqli_fetch_array($run_d);
	
	$vowels = array("<P>", "</P>", "&NBSP;");
	$descr = str_replace($vowels, "", $_REQUEST['des']);
	
	$uno = "";
	if($row_d['predsol_cantidad'] != $_REQUEST['cantidad']) {
		if($row_d["predsol_cantidad2"] == 0){
			$uno = "predsol_cantidad2=".$_REQUEST['can'].", ";
		}
	}
	$dos = "";
	if($descr != $_REQUEST['descripcion']) {
		if(trim($row_d["predsol_descripcion2"]) == ""){
			$dos = "predsol_descripcion2='".$_REQUEST['des']."', ";
		}
	}
	
	$sql_u = "Update ".$conf->getTbl_predsol()." Set "
		.$uno
		.$dos
		."predsol_cantidad = ".$_REQUEST['cantidad'].","
		."predsol_descripcion = '".strtoupper($_REQUEST['descripcion'])."'"
		." Where id_predsol = ".$_REQUEST["pd"];
	//echo $sql_u;
	$db->ejecutar($sql_u);
	header('Location: ?c=solc&a=trabajogc&ps='.$_REQUEST['ps'].'&cs='.$_REQUEST['cs'].'&es='.$_REQUEST['es']);
}
/*
 * 
 */
function creao2(){
	require_once 'model/SQLgenerales.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	
	$fecha_hoy =  date('Y-m-d');
	$hora_hoy = date('H:i:s');
	
	$infE = datosCia($_REQUEST['es']);
	//print_r($infE);
	
	$sqlxprv = "Select id_proveedor From ".$conf->getTbl_predsol()
		." Where id_prehsol = ".$_REQUEST["ps"]
		." Group by id_proveedor";

	$runxprv = $db->ejecutar($sqlxprv);

	$sqlNueva= "select prehsol_monto, prehsol_Proveedor, prehsol_MetodoPago, moneda from prehsol where id_prehsol = ".$_REQUEST["ps"];
	$runNueva = $db->ejecutar($sqlNueva);
	$rowNueva = mysqli_fetch_array($runNueva);

	$msg = "Se realizaron las siguientes transacciones:<br>";
	while ($rowxprv = mysqli_fetch_array($runxprv)){
		$infP = datosProveedor($rowxprv[0]);
		//print_r($infP);
		$prov_oc = $infP[0]['prov_nvocod'];
		/*   */
		$valor = $_REQUEST['tg'];
		//print_r($_REQUEST);
		try {
			
			
			
			// Nos conectamos a la base de datos de proveeduria O.C.
			$db->conecta_OC();
			//Creamos primero en encabezado de la O.C.
			$sql = "Select NORDEN From empresas Where CODIGO = '".$infE[0]['id_empresa_oc']."'";
			$result = $db->ejecuta_OC($sql);
			$row = mysqli_fetch_array($result);
			// Numero de orden a crear
			$Numero = $row[0]+1;
			$orden = $Numero;
			
			$msg .= "Orden # ".$orden." para : ".$rowxprv[0].' '.$infP[0]['prov_nombre']."<br/>";
			
			// Actualizamos el correlativo para la empresa
			$_SqlO = "Update empresas Set NORDEN = '".$Numero."' Where CODIGO ='".$infE[0]['id_empresa_oc']."'";
			$db->ejecuta_OC($_SqlO);
			//Esto es el detalle de la O.C. por proveedor
			$sql_h = "Select * From ".$conf->getTbl_prehsol()
				." Where id_prehsol=".$_REQUEST['ps'];
			$res_h = $db->ejecutar($sql_h);
			$row_h = mysqli_fetch_array($res_h);
			//Esto es el detalle de la O.C.
			$sql_d = "Select p.*, c.cc_codigo From ".$conf->getTbl_predsol()." p "
					."Join ".$conf->getTbl_cecosto()." c "
					."On c.id_cc = p.id_cc "
					."Where p.id_prehsol=".$_REQUEST['ps']
					." And id_proveedor = ".$rowxprv[0];
			$res_d = $db->ejecutar($sql_d);
			$i=0;
			$dets = array();
			$heads = array();
			$movs = array();
			while ($row_d = mysqli_fetch_array($res_d)) {
				++$i;
				$dets[] = "Update ".$conf->getTbl_predsol()
					." Set predsol_numero_oc=".$orden.", "
					." predsol_fecha_oc='".$fecha_hoy."', "
					." predsol_hora_oc='".$hora_hoy."', "
					." predsol_usuario_oc='".$_SESSION['u']."', "
					//." id_proveedor = ".$_REQUEST['pr'].", "
					." predsol_estado = 6"
					." Where id_predsol=".$row_d['id_predsol']
					." And id_proveedor = ".$rowxprv[0];
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
				if(!empty($row_d['predsol_observacion'])) {
					$descdet = 	$row_d['predsol_observacion'];	
				} else {
					$descdet = $row_d['predsol_descripcion'];
				}
				$descdet = 	str_replace(",", "", $descdet);	
				$descdet = 	str_replace("'", "''", $descdet);
				$movs[] = "( "
					."'".$row_d['prod_codigo']."', "
					."'".str_pad($row_d['predsol_titgas'], 2, "0", STR_PAD_LEFT)."', "
					."'".str_pad($row_d['predsol_detgas'], 2, "0", STR_PAD_LEFT)."', "
					.$row_d['predsol_cantidad'].", "
					."'".$row_d['predsol_unidad']."', "
					//."'".$row_d['predsol_descripcion']."', "
					."'".$descdet."', "
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
					.$row_d['predsol_total'].", "
					."'SICYS'"
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
					."TOTAL,origen) VALUES ".implode(',', $movs) ;

			$tfpago_str = '';
			if(!empty($row_h['prehsol_MetodoPago'])){
				if((int)$row_h['prehsol_MetodoPago']==1){
					$tfpago_str = 'TRANSFERENCIA';
				}
				if((int)$row_h['prehsol_MetodoPago']==2){
					$tfpago_str = 'CHEQUE';
				}
				if((int)$row_h['prehsol_MetodoPago']==3){
					$tfpago_str = 'CHEQUE DE GERENCIA';
				}
			}
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
					."CONDIPAGO='".$infP[0]['prov_dias']."', "
					."MONEDA='".(empty($row_h['moneda']) ? '' : $row_h['moneda'])."', "
					."fpago='".$tfpago_str."', "
					."prehsol_monto='".(empty($row_h['prehsol_monto']) ? 0 : $row_h['prehsol_monto'])."', "
					."prehsol_Proveedor='".(empty($row_h['prehsol_Proveedor']) ? 0 : $row_h['prehsol_Proveedor'])."', "
					."prehsol_MetodoPago='".(empty($row_h['prehsol_MetodoPago']) ? 0 : $row_h['prehsol_MetodoPago'])."', "
					."moneda_metodopago='".(empty($row_h['moneda']) ? 0 : $row_h['moneda'])."' ";

			$db->ejecuta_OC($_SqlOr);
			// Observaciones
			$_SqlOrde = "Insert Into ".$conf->getTobser().$infE[0]['id_empresa_oc']." Set OBORDEN = '".$orden."'";
			$db->ejecuta_OC($_SqlOrde);
			// Llenamos el datalle
			$runsql = $db->ejecuta_OC($sql);
			$db->desconecta_OC();
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
			
			
		} catch (Exception $e){
			$res = $e->getMessage();
		}
	}
	
	header('Location: /sics/class/PHPMailer/send4a.php?'.$row_h['id_prehsol'].'&'.$orden);
	
	//echo $msg;
	
	
	//print_r($_REQUEST);
}

function creao_copia(){
	require_once 'model/SQLgenerales.php';
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	
	$fecha_hoy =  date('Y-m-d');
	$hora_hoy = date('H:i:s');
	
	$infE = datosCia($_REQUEST['es']);
	//print_r($infE);
	
	$sqlxprv = "Select id_proveedor From ".$conf->getTbl_predsol()
		." Where id_prehsol = ".$_REQUEST["ps"]
		." Group by id_proveedor";

	$runxprv = $db->ejecutar($sqlxprv);

	$sqlNueva= "select prehsol_monto, prehsol_Proveedor, prehsol_MetodoPago, moneda from prehsol where id_prehsol = ".$_REQUEST["ps"];
	$runNueva = $db->ejecutar($sqlNueva);
	$rowNueva = mysqli_fetch_array($runNueva);

	$msg = "Se realizaron las siguientes transacciones:<br>";
	while ($rowxprv = mysqli_fetch_array($runxprv)){
		$infP = datosProveedor($rowxprv[0]);
		//print_r($infP);
		$prov_oc = $infP[0]['prov_nvocod'];
		/*   */
		$valor = $_REQUEST['tg'];
		//print_r($_REQUEST);
		try {
			
			
			
			// Nos conectamos a la base de datos de proveeduria O.C.
			$db->conecta_OC();
			//Creamos primero en encabezado de la O.C.
			$sql = "Select NORDEN From empresas Where CODIGO = '".$infE[0]['id_empresa_oc']."'";
			$result = $db->ejecuta_OC($sql);
			$row = mysqli_fetch_array($result);
			// Numero de orden a crear
			$Numero = $row[0]+1;
			$orden = $Numero;

			$orden = "0";
			
			$msg .= "Orden # ".$orden." para : ".$rowxprv[0].' '.$infP[0]['prov_nombre']."<br/>";
			
			// Actualizamos el correlativo para la empresa
			//$_SqlO = "Update empresas Set NORDEN = '".$Numero."' Where CODIGO ='".$infE[0]['id_empresa_oc']."'";
			//$db->ejecuta_OC($_SqlO);
			//Esto es el detalle de la O.C. por proveedor
			$sql_h = "Select * From ".$conf->getTbl_prehsol()
				." Where id_prehsol=".$_REQUEST['ps'];
			$res_h = $db->ejecutar($sql_h);
			$row_h = mysqli_fetch_array($res_h);
			//Esto es el detalle de la O.C.
			$sql_d = "Select p.*, c.cc_codigo From ".$conf->getTbl_predsol()." p "
					."Join ".$conf->getTbl_cecosto()." c "
					."On c.id_cc = p.id_cc "
					."Where p.id_prehsol=".$_REQUEST['ps']
					." And id_proveedor = ".$rowxprv[0];
			$res_d = $db->ejecutar($sql_d);
			$i=0;
			$dets = array();
			$heads = array();
			$movs = array();
			while ($row_d = mysqli_fetch_array($res_d)) {
				++$i;
				$dets[] = "Update ".$conf->getTbl_predsol()
					." Set predsol_numero_oc=".$orden.", "
					." predsol_fecha_oc='".$fecha_hoy."', "
					." predsol_hora_oc='".$hora_hoy."', "
					." predsol_usuario_oc='".$_SESSION['u']."', "
					//." id_proveedor = ".$_REQUEST['pr'].", "
					." predsol_estado = 6"
					." Where id_predsol=".$row_d['id_predsol']
					." And id_proveedor = ".$rowxprv[0];
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
				if(!empty($row_d['predsol_observacion'])) {
					$descdet = 	$row_d['predsol_observacion'];	
				} else {
					$descdet = $row_d['predsol_descripcion'];
				}
				$movs[] = "( "
					."'".$row_d['prod_codigo']."', "
					."'".str_pad($row_d['predsol_titgas'], 2, "0", STR_PAD_LEFT)."', "
					."'".str_pad($row_d['predsol_detgas'], 2, "0", STR_PAD_LEFT)."', "
					.$row_d['predsol_cantidad'].", "
					."'".$row_d['predsol_unidad']."', "
					//."'".$row_d['predsol_descripcion']."', "
					."'".$descdet."', "
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
					.$row_d['predsol_total'].", "
					."'SICYS'"
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
					."TOTAL,origen) VALUES ".implode(',', $movs) ;
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
					."CONDIPAGO='".$infP[0]['prov_dias']."', "
					."fpago='".(empty($row_h['prehsol_monto']) ? 0 : $row_h['prehsol_monto'])."', "
					."prehsol_monto='".(empty($row_h['prehsol_monto']) ? 0 : $row_h['prehsol_monto'])."', "
					."prehsol_Proveedor='".(empty($row_h['prehsol_Proveedor']) ? 0 : $row_h['prehsol_Proveedor'])."', "
					."prehsol_MetodoPago='".(empty($row_h['prehsol_MetodoPago']) ? 0 : $row_h['prehsol_MetodoPago'])."', "
					."moneda_metodopago='".(empty($row_h['moneda']) ? 0 : $row_h['moneda'])."' ";

			$db->ejecuta_OC($_SqlOr);
			// Observaciones
			$_SqlOrde = "Insert Into ".$conf->getTobser().$infE[0]['id_empresa_oc']." Set OBORDEN = '".$orden."'";
			$db->ejecuta_OC($_SqlOrde);
			// Llenamos el datalle
			$runsql = $db->ejecuta_OC($sql);
			$db->desconecta_OC();
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
					//$run = $db->ejecutar($head[0]);
					$row = mysqli_fetch_array($run);
					if($row[0] <= 0) {
						//$db->ejecutar($head[1]);
					}
				}
			} catch (Exception $e) {
				$res = $e->getMessage();
			}
			$res = $orden;
			
			
		} catch (Exception $e){
			$res = $e->getMessage();
		}
	}
	
	//header('Location: /sics/class/PHPMailer/send4a.php?'.$row_h['id_prehsol'].'&'.$orden);
	
	//echo $msg;
	
	
	//print_r($_REQUEST);
}

/*
 * function ocpre pdf
*
*/
function ocpre_pdf(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	require_once 'view/solc/preoc/PDF.php';
}
/*
 * Reimprime Orden
*
*/
function ocpre_gpdf(){
	$conf = Configuracion::getInstance();
	$db = DB::getInstance();
	require_once 'view/solc/preoc/GA_PDF.php';
}

function VerS(){
	require_once 'model/SQLgenerales.php';
	require_once 'model/solcModel.php';
	$conf = Configuracion::getInstance();
	$estados = $conf->getEstadosSC(); 	//Contiene el listado de los estados de la solicitud	
	$permisos = permisosURL();  		//Contiene el listado de los permisos
	$emps = listarEmpUsuario(); 		//Contiene el listado de las Empresas

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

	$solcs = Consulta2($page, $_SESSION[$key_sess],$_SESSION['u']);

	require_once 'view/solc/ver_solc.php';
}