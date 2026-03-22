<?php
/*
 * return @array
 * listado de empresas a las que tiene permiso el usuario
 */
function listarEmpUsuario(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select a.id_empresa, b.emp_nombre From "
			. $conf->getTbl_acc_emp_cc()." a"
			." Join ".$conf->getTbl_empresa()." b On"
			." b.id_empresa = a.id_empresa"
			. " Where a.id_usuario =".$_SESSION['i']
			." Group By a.id_empresa ";
	$run = $db->ejecutar ( $sql );
	if (mysqli_num_rows($run) > 0) {
		while ( $row = mysqli_fetch_array ($run) ) {
			$array[] = $row;
		}
	} else {
		$array[] = "";
	}
	return $array;
}
/*
 * return @array
 * Lista todos los centros de costo que tiene
 * asignado el usuario para una empresa
 */
function listarCcUsuario(){
	if (isset($_POST['id_empresa']) && $_POST['id_empresa'] != "") {
		try {
			$idEmpresa = $_POST['id_empresa'];
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sql = "select a.id_cc, b.cc_codigo, b.cc_descripcion from " . $conf->getTbl_acc_emp_cc()." a"
					." Join ".$conf->getTbl_cecosto()." b On"
					." b.id_cc = a.id_cc And "
					." b.id_empresa = a.id_empresa"
					." Where a.id_empresa = ".$idEmpresa
					." And a.id_usuario =".$_SESSION['i']
					." order by b.cc_descripcion";
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ( $run ) ) {
					$array [] = $row;
				}
			} else {
				$array[] = "";
			}
		} catch (Exception $e) {
			$array = $e->getMessage();
		}
	} else {
		$array[] = "";
	}
	return $array;
}
/*
 * Creamos el encabezado de la Solicitud de Consumo Interno
 */
function creaPreSolc($form){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select cc_ci From ".$conf->getTbl_cecosto()
			." Where id_empresa = ".$form['empresa']
			." And id_cc = ".$form['centrocosto'];
		$run = $db->ejecutar($sql);
		/*
		 * Si el centro de costo existe
		 */
		if(mysqli_num_rows($run) > 0){
			$row = mysqli_fetch_array($run);
			$result = $row[0];
			$fecha = date('Y-m-d');
			$hora = date('H:i:s');
			$sql_u = "Update ".$conf->getTbl_cecosto()." Set "
				."cc_ci = ".($result+1)." "
				."Where id_empresa = ".$form['empresa']
				." And id_cc = ".$form['centrocosto'];
			$db->ejecutar($sql_u);
			// Metemos la pre-solicitud
			$sql_i = "Insert Into ci_enc Set "
				."id_empresa = ".$form['empresa'].", "
				."id_cc = ".$form['centrocosto'].", "
				."id_usuario = ".$_SESSION['i'].", "
				."prod_usuario = '".$_SESSION['u']."', "
				."ci_numero = ".$result.", "
				."ci_enc_fecha = '".$fecha."', "
				."ci_enc_hora = '".$hora."', "
				."ci_enc_procesado = ' '";
				//."id_tipo_consumo = ".$form['tipoconsumo'];
			$db->ejecutar($sql_i);
		} else {
			$result = "Error en Centro de Costo";
		}
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Retorna la informacion del encabezado de la presolicitud de compra
 */
function infoPreHSolc($idprehsol, $idcc, $idemp){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From ci_enc a"
			." Join ".$conf->getTbl_empresa()." b On"
			." b.id_empresa = a.id_empresa"
			." Join ".$conf->getTbl_cecosto()." c On"
			." c.id_cc = a.id_cc"
			." Where a.ci_numero =".$idprehsol." "
			."And a.id_cc = ".$idcc." "
			."And a.id_empresa = ".$idemp;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array[] = "";
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Retorna el detalle de la presolicitud
 */
function detPreDSolc($idprehsol, $idcc, $idemp){
	$result = array();
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From ci_det"
				." Where id_ci =".$idprehsol." "
				."And id_cc = ".$idcc." "
				."And id_empresa = ".$idemp;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Retorna todos los codigos habilitados para solicitud de compra
 */
function itemsSolc(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From "
				.$conf->getTbl_producto()
				." Where prod_solc = 1";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Lista solicitudes de usuario conectado
 */
function listarSolcs(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select a.*, b.* From "
				."ci_enc a"
				." Join ".$conf->getTbl_cecosto()." b"
				." On "
				."a.id_cc = b.id_cc And "
				."a.id_empresa = b.id_empresa"
				." Where a.id_usuario = ".$_SESSION['i']
				." and ci_estado = 0"
				." Order by a.ci_enc_fecha DESC";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Lista solicitudes de usuario conectado
*/
function listarTipcs(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From ci_tipo_consumo Order by id_tipo_consumo";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Lista solicitudes de usuario conectado
*/
function listarTipcsP(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From ci_producto Order by prod_descripcion";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Lista solicitudes de usuario conectado
 * para autorizar
 */
function listarSolcsAuth($idemp){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select b.cc_descripcion, c.* From "
			.$conf->getTbl_acc_emp_cc()." a "
			."Join ".$conf->getTbl_cecosto()." b On "
			."a.id_cc = b.id_cc "
			."Join ".$conf->getTbl_prehsol()." c On "
			."a.id_empresa = c.id_empresa And "
			."b.id_cc = c.id_cc "
			."Where a.id_usuario = ".$_SESSION['i']." And "
			."c.id_empresa = ".$idemp
			." And c.prehsol_estado = 1";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}

/*
 * Ejecuta consulta para la paginacion
*/
function Consulta($page, $form){
	try {
		list($idemp, $nemp) = explode("~", $form["empresa"]);
		$rowsperpage = 15;
		$inicio = ($rowsperpage*$page)-$rowsperpage;
		$limite = $rowsperpage;
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$form['numero'] = (int) $form['numero'];
		$form['status'] = (int) $form['status'];
		$numero = "";
		$cc = "";
		$estado = "";
		if($form['numero'] > 0) {
			$numero = " And ci_numero = ".$form['numero'];
		}
		if($form['centrocosto'] != '9999') {
			$cc = " And a.id_cc = ".$form['centrocosto'];
		}
		if($form['status'] > 0) {
			$estado = " And ci_estado = ".$form['status'];
		}
		$sql = "Select a.*, b.cc_descripcion From ci_enc a"
			." join ".$conf->getTbl_cecosto()." b"
			." On b.id_empresa = a.id_empresa And"
			." b.id_cc = a.id_cc"
			." Where "
			."a.id_empresa = ".$idemp
			.$cc
			.$numero
			.$estado
			." And a.ci_numero > 0"
			." Order By a.ci_enc_fecha Desc";
		//echo $sql;
		$run = $db->ejecutar($sql);
		$registros = mysqli_num_rows($run);
		/*
		 *
		*/
		$sql = "Select a.*, b.cc_descripcion From ci_enc a"
			." join ".$conf->getTbl_cecosto()." b"
			." On b.id_empresa = a.id_empresa And"
			." b.id_cc = a.id_cc"
			." Where "
			."a.id_empresa = ".$idemp
			.$cc
			.$numero
			.$estado
			." And a.ci_numero > 0"
			." Order By a.ci_enc_fecha Desc"
			." Limit ".$inicio.",".$limite;
		$run = $db->ejecutar($sql);
		$registrosFilter = mysqli_num_rows($run);
		$array['registros'] = $registros;
		if ($registrosFilter > 0) {
			while ( $row = mysqli_fetch_array ( $run ) ) {

				$sqlDet = "Select * From ci_det"
				." Where id_ci = ".$row['id_ci'];
				$runDet = $db->ejecutar($sqlDet);
				$rowD = array();
				if(mysqli_num_rows($runDet) <= 0) {
					$rowD[] = "";
				} else {
					while($rowDet = mysqli_fetch_array($runDet)) {
						$rowD[] = $rowDet;
					}
				}
				
				$row['Det'] = $rowD;
				$array[] = $row;
			}
		} else {
			$array[] = "";
			$key_sess = $_SESSION['u'].$_GET['id'].$_GET['c'].$_GET['a'];
			$_SESSION[$key_sess] ="";
			unset($_SESSION[$key_sess]);
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Lista todas las requisiciones listas para colectar
*/
function listasColectar($id_empresa, $inicio, $fin) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select e.id_prehsol, e.id_empresa, e.id_cc, e.prehsol_fecha, e.prehsol_numero_sol, c.cc_codigo, c.cc_descripcion From "
			.$conf->getTbl_prehsol()." e"
			." Join ".$conf->getTbl_cecosto()." c"
			." On c.id_empresa = e.id_empresa And c.id_cc = e.id_cc"
			." Where e.prehsol_estado = 2"
			." And e.id_empresa=".$id_empresa
			//." And e.prehsol_fecha Between '".$inicio."' And '".$fin."'"
			." Order By e.id_cc, e.prehsol_numero_sol";
		//echo $sql;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$sqlDet = "Select * From ".$conf->getTbl_predsol()
				." Where id_prehsol = ".$row['id_prehsol'];
				$runDet = $db->ejecutar($sqlDet);
				$rowD = array();
				if(mysqli_num_rows($runDet) <= 0) {
					$rowD[] = "";
				} else {
					while($rowDet = mysqli_fetch_array($runDet)) {
						$rowD[] = $rowDet;
					}
				}
				$row['Det'] = $rowD;
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Lista todas
 */
function listasTrabajar($id_empresa) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select e.id_prehsol, e.id_empresa, e.id_cc, e.prehsol_fecha, e.prehsol_numero_sol, c.cc_codigo, c.cc_descripcion From "
			.$conf->getTbl_prehsol()." e"
			." Join ".$conf->getTbl_cecosto()." c"
			." On c.id_empresa = e.id_empresa And c.id_cc = e.id_cc"
			." Where e.prehsol_estado = 3"
			." And e.id_empresa=".$id_empresa
			." Order By e.id_cc, e.prehsol_numero_sol";
		//echo $sql;
		try {
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ($run) ) {
					$sqlDet = "Select * From ".$conf->getTbl_predsol()
					." Where id_prehsol = ".$row['id_prehsol'];
					$runDet = $db->ejecutar($sqlDet);
					$rowD = array();
					if(mysqli_num_rows($runDet) <= 0) {
						$rowD[] = "";
					} else {
						while($rowDet = mysqli_fetch_array($runDet)) {
							$rowD[] = $rowDet;
						}
					}
					$row['Det'] = $rowD;
					$array[] = $row;
				}
			} else {
				$array = array();
			}
			$result = $array;
		} catch (Exception $e1){
			$result = $e1->getMessage();
		}
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * return @array
 * Lista precio de producto por proveedor
 */
function listarPrecio(){
	if (isset($_POST['producto']) && $_POST['producto'] != "") {
		try {
			$Producto = trim($_POST['producto']);
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sql = "select a.id_lista, a.id_proveedor, a.lis_precio, b.prov_nombre, a.lis_empaque from " . $conf->getTbl_lista()." a"
					." Join ".$conf->getTbl_proveedor()." b On"
					." b.id_proveedor = a.id_proveedor "
					." Where a.prod_codigo = '".$Producto."'"
					." order by a.id_proveedor";
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ( $run ) ) {
					$array [] = $row;
				}
			} else {
				$array = array();
			}
		} catch (Exception $e) {
			$array[] = $e->getMessage();
		}
	} else {
		$array = array();
	}
	return $array;
}
/*
 * return @array
* Lista Items disponibles para asignar
*/
function listaItems(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select p.prod_codigo, p.prod_descripcion, p.sl_linea, p.sl_sublinea, g.gas_tit_codigo, g.gas_det_codigo From "
				.$conf->getTbl_producto()." p "
				." Join ".$conf->getTbl_sublinea()." s On "
				."s.sl_linea = p.sl_linea And s.sl_sublinea = p.sl_sublinea "
				."Join ".$conf->getTbl_tagasto()." g On "
				."g.id_tagasto = s.id_tagasto"
				." Where p.prod_solc = 1";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Lista todas
*/
function listasOC($id_empresa) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select d.id_proveedor, d.id_predsol, p.prov_nombre "
			."From "
			.$conf->getTbl_predsol()." d"
			." Join "
			.$conf->getTbl_proveedor()." p"
			." On "
			."p.id_proveedor = d.id_proveedor "
			."Where "
			."d.predsol_estado = 3 and "
			."d.predsol_total > 0 and "
			."d.id_empresa = ".$id_empresa
			." Group by "
			."d.id_proveedor";
		try {
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ($run) ) {
					$sqlDet = "Select p.*, c.cc_codigo From ".$conf->getTbl_predsol()." p"
						." Join ".$conf->getTbl_cecosto()." c"
						." On c.id_cc = p.id_cc"
						." Where p.id_proveedor = ".$row['id_proveedor']." And "
						."p.predsol_estado = 3 And "
						."p.predsol_total > 0 And "
						."p.id_empresa = ".$id_empresa;
					$runDet = $db->ejecutar($sqlDet);
					$rowD = array();
					if(mysqli_num_rows($runDet) <= 0) {
						$rowD[] = "";
					} else {
						while($rowDet = mysqli_fetch_array($runDet)) {
							$rowD[] = $rowDet;
						}
					}
					$row['Det'] = $rowD;
					$array[] = $row;
				}
			} else {
				$array = array();
			}
			$result = $array;
		} catch (Exception $e1){
			$result = $e1->getMessage();
		}
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * return @array
* Lista de proveedors
*/
function listarProveedor(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "select id_proveedor, prov_nombre from " . $conf->getTbl_proveedor()
			." Where prov_nombre <> ''"
			." order by prov_nombre";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ( $run ) ) {
				$array [] = $row;
			}
		} else {
			$array = array();
		}
	} catch (Exception $e) {
		$array[] = $e->getMessage();
	}
	return $array;
}
/*
 * Ejecuta consulta para la paginacion
*/
function ConsultaOrdenes($page, $form){
	try {
		$rowsperpage = 15;
		$inicio = ($rowsperpage*$page)-$rowsperpage;
		$limite = $rowsperpage;
		//echo 'Inicio : '.$inicio.' Limite : '.$limite;
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$form['numero'] = (int) $form['numero'];
		$numero = "";
		$cc = "";
		if($form['numero'] > 0) {
			//$numero = " And prehsol_numero_sol = ".$form['numero'];
			$numero = " And a.predreq_numero_oc = '".$form['numero']."'";
		}
		if($form['centrocosto'] != '00') {
			$cc = " And a.id_cc = ".$form['centrocosto'];
		}
		$sql = "Select a.predreq_numero_oc From ".$conf->getTbl_predreq()." a"
			." Where "
			."a.id_empresa = ".$form['empresa']
			." And a.predreq_numero_oc is not null"
			." And predreq_estado > 3"
			.$cc
			.$numero
			." Group by a.predreq_numero_oc";
		$run = $db->ejecutar($sql);
		$registros = mysqli_num_rows($run);
		/*
		 *
		*/
		$sql = "Select a.predreq_numero_oc, a.predreq_fecha_oc, a.predreq_hora_oc, a.predreq_usuario_oc, a.predreq_estado, a.id_empresa, a.id_proveedor From ".$conf->getTbl_predreq()." a"
			." Where "
			."a.id_empresa = ".$form['empresa']
			." And a.predreq_numero_oc is not null"
			." And predreq_estado > 3"
			.$cc
			.$numero
			." Group by a.predreq_numero_oc"
			." Limit ".$inicio.",".$limite;
		$run = $db->ejecutar($sql);
		$registrosFilter = mysqli_num_rows($run);
		$array['registros'] = $registros;
		if ($registrosFilter > 0) {
			while ( $row = mysqli_fetch_array ( $run ) ) {

				$row['nestado'] = $conf->getEstadoSC($row['predreq_estado']);

				$sqlDet = "Select * From ".$conf->getTbl_predreq()
				." Where predreq_numero_oc = '".$row['predreq_numero_oc']."'";
				$runDet = $db->ejecutar($sqlDet);
				$rowD = array();
				if(mysqli_num_rows($runDet) <= 0) {
					$rowD[] = "";
				} else {
					while($rowDet = mysqli_fetch_array($runDet)) {
						$rowD[] = $rowDet;
					}
				}
				$row['Det'] = $rowD;
				$array[] = $row;
			}
		} else {
			$array[] = "";
			$key_sess = $_SESSION['u'].$_GET['id'].$_GET['c'].$_GET['a'];
			$_SESSION[$key_sess] ="";
			unset($_SESSION[$key_sess]);
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}

/*
 * return @array
* listado de empresas a las que tiene permiso el usuario
*/
function listarGestores(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select g.*, u.usr_usuario, u.usr_nombre From "
		. $conf->getTbl_gestores()." g"
		." Join ".$conf->getTbl_usuario()." u On"
		." u.id_usuario = g.id_usuario"
		." Group By g.id_usuario ";
	$run = $db->ejecutar ( $sql );
	$array = array();
	if (mysqli_num_rows($run) > 0) {
		while ( $row = mysqli_fetch_array ($run) ) {
			$array[] = $row;
		}
	}
	return $array;
}
/*
 * Listado de usuarios
 */
function listarUsuarios(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select u.id_usuario, u.usr_usuario, u.usr_nombre From "
		. $conf->getTbl_usuario()." u Order by usr_nombre";
	$run = $db->ejecutar ( $sql );
	$array = array();
	if (mysqli_num_rows($run) > 0) {
		while ( $row = mysqli_fetch_array ($run) ) {
			$array[] = $row;
		}
	}
	return $array;
}

/*
 * return @array
* listado de empresas a las que tiene permiso el usuario
*/
function listarAccesosId($_id){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select g.id_usuario, g.id_cc, c.cc_descripcion, e.emp_nombre From "
		. $conf->getTbl_gestores()." g"
		." Join ".$conf->getTbl_cecosto()." c On"
		." c.id_cc = g.id_cc"
		." Join ".$conf->getTbl_empresa()." e On"
		." e.id_empresa = c.id_empresa"
		." Where g.id_usuario = ".$_GET['usr']
		." Order by c.id_empresa, g.id_cc ";
	$run = $db->ejecutar ( $sql );
	$array = array();
	if (mysqli_num_rows($run) > 0) {
		while ( $row = mysqli_fetch_array ($run) ) {
			$array[] = $row;
		}
	}
	return $array;
}
/*
 * return @array
* listado de empresas a las que tiene permiso el usuario
*/
function listarCentros(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select c.id_cc, c.cc_descripcion, e.id_empresa, e.emp_nombre From "
		. $conf->getTbl_cecosto()." c"
		." Join ".$conf->getTbl_empresa()." e On"
		." e.id_empresa = c.id_empresa"
		." Order by e.id_empresa, c.cc_descripcion ";
	$run = $db->ejecutar ( $sql );
	$array = array();
	if (mysqli_num_rows($run) > 0) {
		while ( $row = mysqli_fetch_array ($run) ) {
			$array[] = $row;
		}
	}
	return $array;
}
/*
 * Lista solicitudes de usuario conectado
*/
function listarSolcsGes(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		/*$sql = "Select * From ci_enc a"
			." Join ".$conf->getTbl_cecosto()." b"
			." On "
			."a.id_cc = b.id_cc And "
			."a.id_empresa = b.id_empresa"
			." Where a.ci_estado IN(1, 2, 3)"
			." Order by a.ci_estado ASC, a.ci_enc_fecha DESC";*/
		$sql = "Select g.*, e.* From ci_generado g"
			." Join ".$conf->getTbl_empresa()." e"
			." On "
			."g.id_empresa = e.id_empresa"
			." Order by per_fecha DESC";
		//echo $sql;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				// Buscamos estatus
				/*$sql_stat = "Select * From ".$conf->getTbl_prehsol_stat()
				." Where id_prehsol = ".$row['id_prehsol'];
				$run_stat = $db->ejecutar($sql_stat);
				$estados = "<b>trazabilidad:</b><br>";
				while ($row_stat = mysqli_fetch_array($run_stat)) {
					$estados .= '<b>'.$row_stat['prehsol_stat_desc'].'</b> <u>'.$row_stat['prehsol_stat_usuario'].'</u> <i>'.$row_stat['prehsol_stat_fecha'].' '.$row_stat['prehsol_stat_hora']."</i><br>";
				}
				$row['estados'] = $estados;*/
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * return @array
* listado de usuarios para autorizar por categoria
*/
function listarGestores2(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select g.*, u.usr_usuario, u.usr_nombre, c.nombre_categoria From "
		. $conf->getTbl_gestion_categorias()." g"
		." Join ".$conf->getTbl_usuario()." u On"
		." u.id_usuario = g.id_usuario"
		." Join ".$conf->getTbl_tipo_categoria()." c On"
		." c.id_categoria = g.id_categoria"
		." Group By g.id_usuario ";
	$run = $db->ejecutar ( $sql );
	$array = array();
	if (mysqli_num_rows($run) > 0) {
		while ( $row = mysqli_fetch_array ($run) ) {
			$array[] = $row;
		}
	}
	return $array;
}
/*
 * return @array
* listado de categorias
*/
function listarCategorias(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select * From "
		. $conf->getTbl_tipo_categoria();
	$run = $db->ejecutar ( $sql );
	$array = array();
	if (mysqli_num_rows($run) > 0) {
		while ( $row = mysqli_fetch_array ($run) ) {
			$array[] = $row;
		}
	}
	return $array;
}
/*
 * return @array
* listado de categorias a las que tiene permiso el usuario
*/
function listarCatUsuario(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select c.id_categoria, c.nombre_categoria From"
		." tipo_categoria c"
		." Join ".$conf->getTbl_gestion_categorias()." gc On"
		." gc.id_categoria = c.id_categoria"
		." Where gc.id_usuario =".$_SESSION['i']
		." Group By gc.id_categoria ";
	$run = $db->ejecutar ( $sql );
	if (mysqli_num_rows($run) > 0) {
		while ( $row = mysqli_fetch_array ($run) ) {
			$array[] = $row;
		}
	} else {
		$array[] = array();
	}
	return $array;
}
/*
 * Lista solicitudes de usuario conectado
* para autorizar
*/
function listarSolcsAuthCat($idemp){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select c.*, b.cc_descripcion From "
			.$conf->getTbl_prehsol()." c "
			."Join ".$conf->getTbl_cecosto()." b On "
			."b.id_cc = c.id_cc "
			."Where "
			."c.prehsol_estado = 2 "
			."And c.id_categoria = ".$idemp;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Lista solicitudes de usuario conectado
* para autorizar
*/
function listarSolcsAuthGest($idemp){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		
		$sql_cc = "Select * From ".$conf->getTbl_gestores()
			." Where id_usuario=".$_SESSION['i']
			." And id_cc > 0";
		$run_cc = $db->ejecutar($sql_cc);
		$ccs = array();
		while($row = mysqli_fetch_array($run_cc)){
			//print_r($row);
			$ccs[] = $row['id_cc'];
		}
		//echo implode(',', $ccs);
		$sql = "Select c.*, b.cc_descripcion From "
			.$conf->getTbl_prehsol()." c "
			."Join ".$conf->getTbl_cecosto()." b On "
			."b.id_cc = c.id_cc "
			."Where "
			."c.prehsol_estado = 3 "
			."And c.id_cc IN(".implode(',', $ccs).")";
		//echo $sql;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}
/*
 * Lista detalle de pedido generado
 */
function listarDetCI(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql_g = "Select * from ci_generado where id_generado=".$_GET['ig'];
		$run_g = $db->ejecutar($sql_g);
		$row_g = mysqli_fetch_array($run_g);
		/*
		 * 
		 */
		$sql = "Select * From ci_enc a"
			." Join ".$conf->getTbl_cecosto()." b"
			." On "
			."a.id_cc = b.id_cc And "
			."a.id_empresa = b.id_empresa"
			." Where a.id_ci >= ".$row_g['per_desde']." and a.id_ci <= ".$row_g['per_hasta']." and"
			." a.id_empresa = ".$row_g['id_empresa']
			." Order by a.id_ci ASC, a.ci_enc_fecha DESC";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
		$result = $array;
	} catch (Exception $e) {
		$result = $e->getMessage();
	}
	return $result;
}