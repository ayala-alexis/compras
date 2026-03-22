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
 *
 */
function creaPreSolc($form){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select cc_presolc From ".$conf->getTbl_cecosto()
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
				."cc_presolc = ".($result+1)." "
				."Where id_empresa = ".$form['empresa']
				." And id_cc = ".$form['centrocosto'];
			$db->ejecutar($sql_u);
			// Pone las estadisticas
			$sql_E = "INSERT INTO ".$conf->getTbl_estadistica()
				." (id_estadisticas, id_usuario, mes, anio, cantidad, presol, solicitudes, prereq, requisiciones) "
				."VALUES "
				."(0, ".$_SESSION['i'].", ".date('m').", ".date('Y').", 1, 1, 0, 0, 0 ) "
				."ON DUPLICATE KEY UPDATE presol = presol + 1, cantidad = cantidad + 1";
			$db->ejecutar($sql_E);
			// Metemos la pre-solicitud
			$sql_i = "Insert Into ".$conf->getTbl_prehsol()." Set "
				."id_empresa = ".$form['empresa'].", "
				."id_cc = ".$form['centrocosto'].", "
				."id_usuario = ".$_SESSION['i'].", "
				."prehsol_numero = ".$result.", "
				."prehsol_fecha = '".$fecha."', "
				."prehsol_hora = '".$hora."', "
				."prehsol_estado = 0, "
				."prehsol_usuario = '".$_SESSION['u']."'";
			$db->ejecutar($sql_i);
			// Ponemos el estado de creado
			$sql_st = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
				."id_prehsol = ".$db->_last_insert_id.", "
				."prehsol_stat = 0, "
				."prehsol_stat_desc = '".$conf->getEstadoSC('0')."', "
				."prehsol_stat_fecha = '".$fecha."', "
				."prehsol_stat_hora = '".$hora."', "
				."prehsol_stat_usuario = '".$_SESSION['u']."'";
			$db->ejecutar($sql_st);
		} else {
			$result = 0;
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
		$sql = "Select * From "
			.$conf->getTbl_prehsol()." a"
			." Join ".$conf->getTbl_empresa()." b On"
			." b.id_empresa = a.id_empresa"
			." Join ".$conf->getTbl_cecosto()." c On"
			." c.id_cc = a.id_cc"
			." Where a.prehsol_numero =".$idprehsol." "
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
function infoPreHSolc_stat($idprehsol){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select 
				id_prehsol,
				prehsol_stat,
				prehsol_stat_desc,
				max(prehsol_stat_fecha) prehsol_stat_fecha,
				max(prehsol_stat_hora) prehsol_stat_hora,
				prehsol_stat_usuario,
				prehsol_devolver 
				From ".$conf->getTbl_prehsol_stat()." 
				Where id_prehsol =".$idprehsol." and prehsol_stat>0 
				group by id_prehsol,
				prehsol_stat,
				prehsol_stat_desc,
				prehsol_stat_usuario,
				prehsol_devolver
				order by prehsol_stat_fecha,prehsol_stat_hora ";
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
		$sql = "Select * From "
				.$conf->getTbl_predsol()
				." Where id_prehsol =".$idprehsol." "
				/*."And id_cc = ".$idcc." "
				."And id_empresa = ".$idemp*/;
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
		$sql = "Select * From "
				.$conf->getTbl_prehsol()." a"
				." Join ".$conf->getTbl_cecosto()." b"
				." On "
				."a.id_cc = b.id_cc And "
				."a.id_empresa = b.id_empresa"
				." Where id_usuario = ".$_SESSION['i']
				." And a.prehsol_estado < 10"
				." Order by prehsol_fecha DESC";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				// Buscamos estatus
				$sql_stat = "Select * From ".$conf->getTbl_prehsol_stat()
					." Where id_prehsol = ".$row['id_prehsol'];
				$run_stat = $db->ejecutar($sql_stat);
				$estados = "<b>trazabilidad:</b><br>";
				while ($row_stat = mysqli_fetch_array($run_stat)) {
					$estados .= '<b>'.$row_stat['prehsol_stat_desc'].'</b> <u>'.$row_stat['prehsol_stat_usuario'].'</u> <i>'.$row_stat['prehsol_stat_fecha'].' '.$row_stat['prehsol_stat_hora']."</i><br>";
				}
				$row['estados'] = $estados;
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
		$rowsperpage = 15;
		$inicio = ($rowsperpage*$page)-$rowsperpage;
		$limite = $rowsperpage;
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		//var_dump($form);
		$form['numero'] = (int) $form['numero'];
		$form['status'] = (int) $form['status'];
		$numero = "";
		$cc = "";
		$estado = "";

		if($form['numero'] > 0) {
			$numero = " And prehsol_numero_sol = ".$form['numero'];
		}
		if($form['centrocosto'] != '9999') {
			$cc = " And a.id_cc = ".$form['centrocosto'];
		}
		if($form['status'] > 0) {
			$estado = " And prehsol_estado = ".$form['status'];
		}
		$sql = "Select a.*, b.cc_descripcion From ".$conf->getTbl_prehsol()." a"
			." join ".$conf->getTbl_cecosto()." b"
			." On b.id_empresa = a.id_empresa And"
			." b.id_cc = a.id_cc"
			." Where "
			."a.id_empresa = ".$form['empresa']
			.$cc
			.$numero
			.$estado
			." And a.prehsol_numero_sol > 0"
			." Order By prehsol_fecha Desc";

		$run = $db->ejecutar($sql);
		$registros = mysqli_num_rows($run);
		/*
		 *
		*/
		$sql = "Select a.*, b.cc_descripcion From ".$conf->getTbl_prehsol()." a"
			." join ".$conf->getTbl_cecosto()." b"
			." On b.id_empresa = a.id_empresa And"
			." b.id_cc = a.id_cc"
			." Where "
			."a.id_empresa = ".$form['empresa']
			.$cc
			.$numero
			.$estado
			." And a.prehsol_numero_sol > 0"
			." Order By prehsol_fecha Desc"
			." Limit ".$inicio.",".$limite;
		
		$run = $db->ejecutar($sql);
		$registrosFilter = mysqli_num_rows($run);
		$array['registros'] = $registros;
		if ($registrosFilter > 0) {
			while ( $row = mysqli_fetch_array ( $run ) ) {

				$row['nestado'] = $conf->getEstadoSC($row['prehsol_estado']);
				
				$sql_estados = "Select * From ".$conf->getTbl_prehsol_stat()
					." Where id_prehsol = ".$row['id_prehsol']
					." Order by prehsol_stat";
				$run_estados = $db->ejecutar($sql_estados);
				
				$rowE = array();
				while ($row_estados = mysqli_fetch_array($run_estados)) {
					$rowE[] = $row_estados;
				}
				$row['estados'] = $rowE;

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

				$row['nestado'] = $conf->getEstado($row['predreq_estado']);

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
* Ejecuta consulta para la paginacion
*/
function ConsultaOrdenesSC($page, $form){
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
		
		$sql_emp = "Select * From ".$conf->getTbl_empresa()
			." Where id_empresa = ".$form['empresa'];
		$run_emp = $db->ejecutar($sql_emp);
		$row_emp = mysqli_fetch_array($run_emp);
		
		if($form['numero'] > 0) {
			//$numero = " And prehsol_numero_sol = ".$form['numero'];
			$numero = " And a.predsol_numero_oc = '".$form['numero']."'";
		}
		if($form['centrocosto'] != '00') {
			$cc = " And a.id_cc = ".$form['centrocosto'];
		}
		$sql = "Select a.predsol_numero_oc From ".$conf->getTbl_predsol()." a"
				." Where "
				."a.id_empresa = ".$form['empresa']
				." And a.predsol_numero_oc is not null"
				." And predsol_estado > 3"
				.$cc
				.$numero
				." Group by a.predsol_numero_oc";
		//echo $sql;
		$run = $db->ejecutar($sql);
		$registros = mysqli_num_rows($run);
		/*
		 *
		*/
		$sql = "Select a.predsol_numero_oc, a.predsol_fecha_oc, a.predsol_hora_oc, a.predsol_usuario_oc, a.predsol_estado, a.id_empresa, a.id_proveedor From ".$conf->getTbl_predsol()." a"
			." Where "
			."a.id_empresa = ".$form['empresa']
			." And a.predsol_numero_oc is not null"
			." And predsol_estado > 3"
			.$cc
			.$numero
			." Group by a.predsol_numero_oc"
			." Limit ".$inicio.",".$limite;
		//echo $sql;
		$run = $db->ejecutar($sql);
		$registrosFilter = mysqli_num_rows($run);
		$array['registros'] = $registros;
		if ($registrosFilter > 0) {
			// 
			$db->conecta_OC();
			while ( $row = mysqli_fetch_array ( $run ) ) {

				$row['nestado'] = $conf->getEstadoSC($row['predsol_estado']);

				/*$sqlDet = "Select * From ".$conf->getTbl_predsol()
					." Where predsol_numero_oc = '".$row['predsol_numero_oc']."'";*/
				$sqlDet = "Select * From ".$conf->getTmovs().$row_emp["id_empresa_oc"]
					." Where NORDEN = '".$row['predsol_numero_oc']."'";
				$runDet = $db->ejecuta_OC($sqlDet);
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
			//
			$db->desconecta_OC();
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
		$sql = "Select * From "
			.$conf->getTbl_prehsol()." a"
			." Join ".$conf->getTbl_cecosto()." b"
			." On "
			."a.id_cc = b.id_cc And "
			."a.id_empresa = b.id_empresa"
			." Join ".$conf->getTbl_empresa()." e "
			." On "
			." e.id_empresa=a.id_empresa "
			." Where a.prehsol_estado IN(4,5,12)"
			." And prehsol_nuevo_gestion = 0"
			." Order by a.prehsol_estado, a.prehsol_fecha, a.prehsol_hora ASC";
		//echo $sql;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				// Buscamos estatus
				$sql_stat = "Select * From ".$conf->getTbl_prehsol_stat()
				." Where id_prehsol = ".$row['id_prehsol'];
				$run_stat = $db->ejecutar($sql_stat);
				$estados = "<b>trazabilidad:</b><br>";
				$devuelta = '';
				while ($row_stat = mysqli_fetch_array($run_stat)) {
					$estados .= '<b>'.$row_stat['prehsol_stat_desc'].'</b> <u>'.$row_stat['prehsol_stat_usuario'].'</u> <i>'.$row_stat['prehsol_stat_fecha'].' '.$row_stat['prehsol_stat_hora']."</i><br>";
					if(empty($devuelta) && $row_stat['prehsol_stat']==20){
						$devuelta = 'DEVUELTO POR CATEGORIA';
					}
				}
				$row['devuelto']=$devuelta;
				$row['estados'] = $estados;
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
		." Group By g.id_categoria, g.gestion_nivel ";
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
* listado de categorias
*/
function listarCategorias1(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select * From "
			. $conf->getTbl_tipo_categoria()." Where id_categoria=1";
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
		
		$sql_niv = "Select gestion_nivel From gestion_categorias where id_usuario = ".$_SESSION["i"];
		$run_niv = $db->ejecutar($sql_niv);
		$row_niv = mysqli_fetch_array($run_niv);
		
		if($idemp == '1') {
			$sql = "Select c.*, b.cc_descripcion From "
			.$conf->getTbl_prehsol()." c "
			."Join ".$conf->getTbl_cecosto()." b On "
			."b.id_cc = c.id_cc "
			."Where "
			."c.prehsol_estado In (2, 3, 4)  "
			."And c.id_categoria = ".$idemp;
		} else {
			$sql = "Select c.*, b.cc_descripcion From "
			.$conf->getTbl_prehsol()." c "
			."Join ".$conf->getTbl_cecosto()." b On "
			."b.id_cc = c.id_cc "
			."Where "
			."c.prehsol_estado In (3, 4)  "
			//."And c.gestion_nivel = ".$row_niv[0]." " // PARA QUE SE LE MUESTREN TODAS AL NIVEL SUPERIOR RV
			."And c.id_categoria = ".$idemp;
		}
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
 * Retorna Item de Solicitud
*/
function detPreDSolcI($id){
	$result = array();
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From "
				.$conf->getTbl_predsol()
				." Where id_predsol =".$id;
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

function Consulta2($page, $form,$usuario){
	try {
		$rowsperpage = 15;
		$inicio = ($rowsperpage*$page)-$rowsperpage;
		$limite = $rowsperpage;
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();

		$form['numero'] = (int) $form['numero'];
		$numero = "";
		$cc = "";

		if($form['numero'] > 0) {
			$numero = " And prehsol_numero_sol = ".$form['numero'];
		}
		if($form['centrocosto'] != '9999') {
			$cc = " And a.id_cc = ".$form['centrocosto'];
		}

		$sql = 	"Select a.*, ". 
				"b.cc_descripcion, ".
				"c.* ".				
				"From ".$conf->getTbl_prehsol()." a ".
				"join ".$conf->getTbl_cecosto()." b ".				
				"On b.id_empresa = a.id_empresa And ".
				"b.id_cc = a.id_cc ".
				"JOIN prehsol_stat C ON A.ID_PREHSOL = C.ID_PREHSOL ".
				"Where ".
				"a.id_empresa = ".$form['empresa']." ".
				$cc.
				$numero.
				"And a.prehsol_numero_sol > 0 ".
				"AND c.prehsol_stat_usuario = '".$usuario."' ".
				"Order By prehsol_fecha Desc";

		echo $sql;
		echo "<br>";
		exit();

		$run = $db->ejecutar($sql);
		$registros = mysqli_num_rows($run);
		/*
		 *
		*/
		$sql = "Select a.*, b.cc_descripcion From ".$conf->getTbl_prehsol()." a"
			." join ".$conf->getTbl_cecosto()." b"
			." On b.id_empresa = a.id_empresa And"
			." b.id_cc = a.id_cc"
			." Where "
			."a.id_empresa = ".$form['empresa']
			.$cc
			.$numero
			." And a.prehsol_numero_sol > 0"
			." AND A.PREHSOL_USUARIO = '".$usuario."'"
			." Order By prehsol_fecha Desc"
			." Limit ".$inicio.",".$limite;
		
		$run = $db->ejecutar($sql);
		$registrosFilter = mysqli_num_rows($run);
		$array['registros'] = $registros;
		if ($registrosFilter > 0) {
			while ( $row = mysqli_fetch_array ( $run ) ) {

				$row['nestado'] = $conf->getEstadoSC($row['prehsol_estado']);
				
				$sql_estados = "Select * From ".$conf->getTbl_prehsol_stat()
					." Where id_prehsol = ".$row['id_prehsol']
					." Order by prehsol_stat";
				$run_estados = $db->ejecutar($sql_estados);
				
				$rowE = array();
				while ($row_estados = mysqli_fetch_array($run_estados)) {
					$rowE[] = $row_estados;
				}
				$row['estados'] = $rowE;

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