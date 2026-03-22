<?php
session_start();

// Muestra formulario de ingreso de O.C.
function inicio() {
	require_once 'view/frmIngreso.php';
}

// Desiste O.C. generada
function desiste(){
	require_once 'view/frmDesiste.php';
}

// Muestra la O.C para revisar e ingresar
function revisar() {
	require_once 'model/invModel.php';
	$Det_Orden = detallePreOC();
	require_once 'view/revisarIngreso.php';
}

// Muestra la O.C para revisar e ingresar
function revisarD() {
	require_once 'model/invModel.php';
	$Det_Orden = detalleOC();
	require_once 'view/revisarIngresoD.php';
}

// Muestra formulario de ingreso de O.C.
function inicios() {
	require_once 'view/frmIngresos.php';
}
// Muestra la O.C para revisar e ingresar
function revisars() {
	require_once 'model/invModel.php';
	$Det_Orden = detallePreOCS();
	require_once 'view/revisarIngresos.php';
}

// Manda listado de bodegas segun empresa seleccionada
function selectBodegas() {
	$idempresa = $_POST ['idempresa'];
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select bod_codigo, bod_descripcion from " . $conf->getTbl_bodega () . " Where id_empresa='" . $idempresa . "'";
		try {
			$query = $db->ejecutar ( $sql );
			while ( $row = mysqli_fetch_assoc ( $query ) ) {
				$array [] = $row;
			}
			echo json_encode ( $array );
		} catch ( Exception $e1 ) {
			echo json_encode ( $e1->getMessage () );
		}
	} catch ( Exception $e ) {
		echo json_encode ( $e->getMessage () );
	}
}
/*
 * Verificaciones generales antes de procesar ingreso de O.C.
 */
function verificaOC() {
	$conf = Configuracion::getInstance ();
	$result = "NO";
	/* Nos vamos a la O.C. */
	list ( $empresa_oc, $empresa_sicys ) = split ( "-", $_POST ['empresa'] );
	$host_oc = $conf->getHostDB();
	$user_oc = $conf->getUserDB();
	$pass_oc =$conf->getPassDB();
	$bd_oc = $conf->getDbprov();
	//
	$tbl_oc = "orden" . $empresa_oc;
	$tbl_movs_oc = "movs" . $empresa_oc;
	$link = mysqli_connect ( $host_oc, $user_oc, $pass_oc, true );
	mysqli_select_db ( $bd_oc, $link );
	$sql_empresa = "Select * From empresas Where CODIGO = '" . $empresa_oc . "'";
	$run_empresa = mysqli_query ( $sql_empresa, $link );
	$num_empresa = mysqli_num_rows ( $run_empresa );
	if ($num_empresa <= 0) {
		$result = "Empresa no tiene ordenes de compra.";
	} else {
		$sql_oc = "Select count(*), PRT From " . $tbl_oc . " Where NORDEN = '" . $_POST ['oc'] . "'";
		$run_oc = mysqli_query ( $sql_oc, $link );
		$num_oc = mysqli_fetch_array ( $run_oc );
		if ($num_oc ['0'] <= 0) {
			$result = "No existe orden de compra: ".$_POST['oc']." en ".$tbl_oc.$sql_oc;
		} else {
			if ($num_oc ['1'] == '0') {
				$result = "Orden aun no ha sido finalizada";
			} else {
				if ($num_oc ['2'] == 1) {
					$result = "Orden ya ha sido ingresada";
				} else {
					$sql_ocd = "Select count(*) From " . $tbl_movs_oc . " Where NORDEN = '" . $_POST ['oc'] . "'";
					$run_ocd = mysqli_query ( $sql_ocd, $link );
					$num_ocd = mysqli_fetch_array ( $run_ocd );
					if ($num_ocd ['0'] <= 0) {
						$result = "Orden esta vacia.";
					} else {
						$result = "OK";
					}
				}
			}
		}
	}
	mysqli_close ( $link );
	echo $result;
}
/*
 * Verificaciones generales antes de procesar ingreso de O.C.
*/
function verificaOCD() {
	$conf = Configuracion::getInstance ();
	$result = "NO";
	/* Nos vamos a la O.C. */
	$empresa_oc = $_REQUEST ['empresa'];
	$host_oc = $conf->getHostDB();
	$user_oc = $conf->getUserDB();
	$pass_oc =$conf->getPassDB();
	$bd_oc = $conf->getDbprov();
	//
	$tbl_oc = "orden" . $empresa_oc;
	$tbl_movs_oc = "movs" . $empresa_oc;
	$link = mysqli_connect ( $host_oc, $user_oc, $pass_oc, true );
	mysqli_select_db ( $bd_oc, $link );
	$sql_empresa = "Select * From empresas Where CODIGO = '" . $empresa_oc . "'";
	$run_empresa = mysqli_query ( $sql_empresa, $link );
	$num_empresa = mysqli_num_rows ( $run_empresa );
	if ($num_empresa <= 0) {
		$result = "Empresa no tiene ordenes de compra.";
	} else {
		$sql_oc = "Select count(*), PRT, ORIGEN From " . $tbl_oc . " Where NORDEN = '" . $_REQUEST ['oc'] . "'";
		$run_oc = mysqli_query ( $sql_oc, $link );
		$num_oc = mysqli_fetch_array ( $run_oc );
		if ($num_oc ['0'] <= 0) {
			$result = "No existe orden de compra: ".$_REQUEST ['oc']." en ".$tbl_oc.$sql_oc;
		} else {
			if ($num_oc ['1'] == '1') {
				$result = "Orden ya se ha finalizado";
			} else {
				if ($num_oc ['2'] != 'SICS') {
					$result = "Orden no ha sido enviada desde el SICS";
				} else {
					$sql_ocd = "Select count(*) From " . $tbl_movs_oc . " Where NORDEN = '" . $_REQUEST ['oc'] . "'";
					$run_ocd = mysqli_query ( $sql_ocd, $link );
					$num_ocd = mysqli_fetch_array ( $run_ocd );
					if ($num_ocd ['0'] <= 0) {
						$result = "Orden esta vacia.";
					} else {
						$result = "OK";
					}
				}
			}
		}
	}
	mysqli_close ( $link );
	echo $result;
}
/*
 * Pone marca en O.C. de ingresada
 */
function marcaOC() {
	require_once 'ODBC.php';
	require_once 'ODBCIT.php';
	
	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	$result = "OK";
	$rtn = '0';
	
	/* Detalle de la empresa */
	$sql_empresa = "Select * From ".$conf->getTbl_empresa()." Where id_empresa = '" . $_POST['empresa'] . "'";
	$run_empresa = $db->ejecutar($sql_empresa);
	$row_empresa = mysqli_fetch_array ( $run_empresa );
	$array ['empresa'] [] = $row_empresa;
	
	/* Nos traemos el detalle */
	$sql_detalle = "Select d.*, c.cc_codigo From " . $conf->getTbl_predreq()." d"
		." join ".$conf->getTbl_cecosto()." c"
		." On c.id_cc = d.id_cc"
		. " Where d.predreq_numero_oc = '" . $_POST ['oc'] . "'"
		. " And d.predreq_cantidad_aut > 0";
	$run = $db->ejecutar($sql_detalle);
	while ( $row = mysqli_fetch_array ( $run ) ) {
		$array ['detalle'] [] = $row;
	}
	// Extrae informacion de la Orden
	$_Sql = "Select id_proveedor From " . $conf->getTbl_predreq()
	. " Where predreq_numero_oc = '" . $_POST ['oc'] . "'";
	$_Qry = $db->ejecutar($_Sql);
	$_Row = mysqli_fetch_array ( $_Qry );
	
	// Codigo de proveedor para buscar nombre de categoria
	$_Sql1 = "Select * From " . $conf->getTbl_proveedor() . " Where id_proveedor = '" . $_Row [0] . "'";
	$_Qry1 = $db->ejecutar ( $_Sql1 );
	$_Row1 = mysqli_fetch_array ( $_Qry1 );
	$array ['proveedor'] [] = $_Row1;
	
	// Saca Nombre de Categoria
	$_Sql2 = "Select * From " . $conf->getTbl_categoria() . " Where id_categoria = '" . $_Row1 [0] . "'";
	$_Qry2 = $db->ejecutar ( $_Sql2 );
	$_Row2 = mysqli_fetch_array ( $_Qry2 );
	$array ['categoria'] [] = $_Row2;
	$i = 0;
	
	$time_doc = strtotime($_POST['fecha_doc']);
	$newformat = date('Y-m-d',$time_doc);
	
	foreach ($array['detalle'] as $field) {
		++$i;
		$dets[] = "Update ".$conf->getTbl_predreq()." Set"
			." predreq_estado = 4,"
			." predreq_preoc = 1"
			." Where id_predreq = ".$field['id_predreq'];
		$heads[$i][] = "Select Count(*) From ".$conf->getTbl_predreq()
			." Where id_prehreq = ".$field['id_prehreq']." And"
			." predreq_estado = 3";
		$heads[$i][] = "Update ".$conf->getTbl_prehreq()." Set"
			." prehreq_estado = 4"
			." Where id_prehreq = ".$field['id_prehreq'];
		$heads[$i][] = "Insert Into ".$conf->getTbl_prehreq_stat()." Set "
				."id_prehreq = ".$field['id_prehreq'].", "
				."prehreq_stat = 5, "
				."prehreq_stat_desc = '".$conf->getEstado('5')."', "
				."prehreq_stat_fecha = '".date("Y-m-d")."', "
				."prehreq_stat_hora = '".date("H:i:s")."', "
				."prehreq_stat_usuario = '".$_SESSION['u']."'";
		
		$movs[] = "( "
			."'".$field['prod_codigo']."', "
			."'".str_pad($field['predreq_titgas'],2,'0',STR_PAD_LEFT)."', "
			."'".str_pad($field['predreq_detgas'],2,'0',STR_PAD_LEFT)."', "
			.$field['predreq_cantidad_aut'].", "
			."'".$field['predreq_unidad']."', "
			."'".$field['predreq_descripcion']."', "
			."'".$field['cc_codigo']."', "
			.$field['predreq_prec_uni'].", "
			."".$array['empresa'][0]['cia_presupuesto'].", "
			."0, "
			."'2', "
			.$i.", "
			."'".$_POST['oc']."', "
			."'".$array['proveedor'][0]['prov_nvocod']."', "
			."8.75, "
			."0.13, "
			."'".$newformat."', "
			."'".date('Y/m/d')."', "
			.$field['predreq_total']
			." )";
	}
	
	/*
	 * Marca la requisicion de enviada a O.C.
	*/
	foreach($dets as $qry) {
		try {
			$db->ejecutar($qry);
		} catch (Exception $e) {
			$result = $e->getMessage();
			$rtn = '1';
		}
	}
	/*
	 * Marcamos el encabezado si ya se ha enviado todo el detalle a O.C.
	*/
	foreach ($heads as $head) {
		try {
			$run = $db->ejecutar($head[0]);
			$row = mysqli_fetch_array($run);
			if($row[0] <= 0) {
				try {
					$db->ejecutar($head[1]);
					$db->ejecutar($head[2]);
				} catch (Exception $e2) {
					$result = $e2->getMessage();
					$rtn = '1';
				}
			}
		} catch (Exception $e1) {
			$result = $e1->getMessage();
			$rtn = '1';
		}
	}
	
	if($rtn == '0') {
		// Nos conectamos a la base de datos de proveeduria O.C.
		$db->conecta_OC();
		$sql = "INSERT INTO ".$conf->getTmovs().$_POST['empresa_oc']." ( "
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
				."FECHA, "
				."Fecha_YMD, "
				."TOTAL ) VALUES ".implode(',', $movs) ;
		// Escribimos el encabezado de la O.C.
		$_SqlOr = "Insert Into ".$conf->getTorden().$_POST['empresa_oc']." Set "
				."NORDEN = '".$_POST['oc']."', "
				."FECHAPED ='".$_POST['fecha_doc']."', "
				."Fecha_YMD ='".date("Y/m/d")."', "
				."CCOSTO ='99', "
				."estado='I', "
				."PEDIDOPOR = 'SICS', "
				."US_CREO ='".$_SESSION['u']."', "
				."OCTITG ='00', "
				."OCDETG ='00', "
				."OCCUOTA=0, "
				."OCGESTOR='SICS', "
				."OCSTAT='', "
				."ITEMS=".$i.", "
				."OCCIA ='".$array['empresa'][0]['cia_presupuesto']."', "
				."OCVALOR ='2', "
				."PRT = '1', "
				."ORIGEN='SICS', "
				."PREOC=1, "
				."CODPROVEE='".$array['proveedor'][0]['prov_nvocod']."', "
				."ATENCION='".$array['proveedor'][0]['prov_contacto1']."', "
				."CONDIPAGO='".$array['proveedor'][0]['prov_dias']."',"
				."NUMDOC='".$_POST['num_doc']."', "
				."VALDOC=".$_POST['val_doc'].", "
				."TIPODOC='".$_POST['tipo_doc']."', "
				."FECHADOC='".$newformat."'";
		$db->ejecuta_OC($_SqlOr);
		// Observaciones
		$_SqlOrde = "Insert Into ".$conf->getTobser().$_POST['empresa_oc']." Set OBORDEN = '".$_POST['oc']."'";
		$db->ejecuta_OC($_SqlOrde);
		// Llenamos el datalle
		$runsql = $db->ejecuta_OC($sql);
		$db->desconecta_OC();
		
		// si usa presupuesto del AS400, hay que crear el gasto
		if($array['empresa'][0]['emp_usa_presupuesto'] == 1 && $array['empresa'][0]['emp_origen_presupuesto'] == 'AS400') {
			
			$usuario = substr(trim($_SESSION['u']),0,9);
			// Conexiones ODBC
			$tabla_gasto = "CTBLIBDB.CTPREMO";
			if ($array['empresa'][0]['id_empresa'] == 1){
				$o = ODBC::getInstance();
			} else {
				$o = ODBCIT::getInstance();
				$tabla_gasto = "CTBTALDB.TCTPREMO";
			}
			
			$orden_right = str_pad($_POST['oc'],10,' ',STR_PAD_LEFT);
			
			$sql_gas = "Select c.cc_codigo, concat(d.predreq_titgas,d.predreq_detgas) as gasto,"
				." sum(d.predreq_total) as total, d.predreq_titgas, d.predreq_detgas From ".$conf->getTbl_predreq()." d"
				." Join ".$conf->getTbl_cecosto()." c"
				." On c.id_cc = d.id_cc"
				." Where d.predreq_numero_oc = '".$_POST['oc']."'"
				." And d.predreq_estado = 4"
				." Group by c.cc_codigo, concat(d.predreq_titgas,d.predreq_detgas)";
			$run_gas = $db->ejecutar($sql_gas);
			while($row_gas = mysqli_fetch_array($run_gas)) {
				
				$IVA = round($row_gas['total']*0.13,2);
				
				// Segun el tipo de documeto le sumamos o le separamos el iva
				if ($_POST['tipo_doc'] == 'F') {
					$docvalor = $row_gas['total']+$IVA;
					$dociva = 0.00;
				} else {
					$docvalor = $row_gas['total'];
					$dociva = $IVA;
				}
				$gasto = str_pad($row_gas['predreq_titgas'],2,'0',STR_PAD_LEFT).str_pad($row_gas['predreq_detgas'],2,'0',STR_PAD_LEFT);
				$iq = "INSERT INTO ".$tabla_gasto." (PMCIA,PMCCO,PMTABG,PMFECR,PMTIPD,PMNDOC,PMVAL,PMIVA,PMOBS,PMPAGO,PMFECA,PMHORA,PMTIPM,PMUSR,PMSWIC,PMFECC,PMNPDA,PMNSEC,PMORIG,PMCHQ,PMPACU)"
					." VALUES("
					.$array['empresa'][0]['cia_presupuesto'].","
					.$row_gas['cc_codigo'].","
					."'".$gasto."',"
					.str_replace("-","",$_POST['fecha_doc']).","
 					."'OC',"
					."'".$orden_right."',"
					.$docvalor.","
					.$dociva.","
					."'',"
					."'1',"
					.date('Ymd').","
					.date('His').","
					."'S',"
					."'".$usuario."',"
					."'',0,'',0,0,'',0)";
				try {
					$o->ejecutar($iq);
				} catch (Exception $eas) {
					$result = $eas->getMessage();
				}
			}	
		} 
	}
	echo $result;
}

/*
 * Pone marca en O.C. de ingresada
*/
function marcaOCD() {
	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	$result = "OK";

	/* Detalle de la empresa */
	$sql_empresa = "Select * From ".$conf->getTbl_empresa()." Where id_empresa = '" . $_POST['empresa'] . "'";
	$run_empresa = $db->ejecutar($sql_empresa);
	$row_empresa = mysqli_fetch_array ( $run_empresa );
	$array ['empresa'] [] = $row_empresa;

	/* Nos traemos el detalle */
	$sql_detalle = "Select d.* From " . $conf->getTbl_predsol()." d"
		. " Where d.predsol_numero_oc = '" . $_POST ['oc'] . "'";
	$run = $db->ejecutar($sql_detalle);
	/* Para cada detalle marcamos el encabezado de desistido */
	while($row = mysqli_fetch_array($run)) {
		/* Encabezado desistido */
		$heads = "Update ".$conf->getTbl_prehsol()." Set"
			." prehsol_estado = 10"
			." Where id_prehsol = ".$row['id_prehsol'];
		$db->ejecutar($heads);
		/* Creamos el log */
		$stats = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
			."id_prehsol = ".$row['id_prehsol'].", "
			."prehsol_stat = 10, "
			."prehsol_stat_desc = '".$conf->getEstadoSC('10')."', "
			."prehsol_stat_fecha = '".date("Y-m-d")."', "
			."prehsol_stat_hora = '".date("H:i:s")."', "
			."prehsol_stat_usuario = '".$_SESSION['u']."'";
		$db->ejecutar($stats);
	}	
	/* Marcamos el detalle desistido */
	$dets = "Update ".$conf->getTbl_predsol()." Set"
			." predsol_estado = 10"
			." Where predsol_numero_oc = '" . $_POST ['oc'] . "'";
	$db->ejecutar($dets);
	// Nos conectamos a las O.C.
	$db->conecta_OC();
	/* Marcamos de anulada la Orden */
	$_SqlOr = "Insert Into ".$conf->getTorden().$_POST['empresa_oc']." Set "
			." ESTADO='*'"
			." Where NORDEN='" . $_POST ['oc'] . "'";
	$db->ejecuta_OC($_SqlOr);
	$db->desconecta_OC();
	echo $result;
}
/*
 * Verificaciones generales antes de procesar ingreso de O.C.
*/
function verificaPreOC() {
	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	$result = "NO";
	/* Nos vamos a la O.C. */
	list ( $empresa_oc, $empresa_sicys ) = split ( "-", $_POST ['empresa'] );
	//
	$sql_existe = "Select * From ".$conf->getTbl_predreq()
		." Where id_empresa = ".$empresa_sicys
		." And predreq_numero_oc = '".$_POST['oc']."'";
		//." And predreq_estado = '3'";
	$run_existe = $db->ejecutar($sql_existe);
	$num_existe = mysqli_num_rows ( $run_existe );
	if ($num_existe <= 0) {
		$result = "No existe orden de compra: ".$_POST['oc'];
	} else {
		$sql_ingreso = "Select * From ".$conf->getTbl_predreq()
			." Where id_empresa = ".$empresa_sicys
			." And predreq_numero_oc = '".$_POST['oc']."'"
			." And predreq_estado > 4 And predreq_cantidad_aut > 0";
		$run_ingreso = $db->ejecutar($sql_ingreso);
		$num_ingreso = mysqli_num_rows($run_ingreso);
		if ($num_ingreso > 0) {
			$result = "Orden de compra: ".$_POST['oc']." ya se ha ingresado.";
		} else {
			$sql_total = "Select sum(predreq_total) as total From ".$conf->getTbl_predreq()
				." Where id_empresa = ".$empresa_sicys
				." And predreq_numero_oc = '".$_POST['oc']."'"
				." And predreq_estado = 3";
			//$result = $sql_total;
			$run_total = $db->ejecutar($sql_total);
			$row_total = mysqli_fetch_array($run_total);
			if ($row_total[0] <> $_POST[val]) {
				$result = "Valor de documento $".$_POST['val']." y orden $".$row_total[0].", no son iguales, verifique.";
				//$result = "Valor de documento y orden, no son iguales, verifique.";
			} else {
				$result = "OK";
				//$result = $sql_total;
			}
		}
	}
	echo $result;
}
/*
 * Verificaciones generales antes de procesar ingreso de O.C.
*/
function verificaPreOCS() {
	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	$result = "NO";
	/* Nos vamos a la O.C. */
	list ( $empresa_oc, $empresa_sicys ) = split ( "-", $_POST ['empresa'] );
	//
	$sql_existe = "Select * From ".$conf->getTbl_predsol()
	." Where id_empresa = ".$empresa_sicys
	." And predsol_numero_oc = '".$_POST['oc']."'";
	//." And predreq_estado = '3'";
	$run_existe = $db->ejecutar($sql_existe);
	$num_existe = mysqli_num_rows ( $run_existe );
	if ($num_existe <= 0) {
		$result = "No existe orden de compra: ".$_POST['oc'];
	} else {
		$sql_ingreso = "Select * From ".$conf->getTbl_predsol()
			." Where id_empresa = ".$empresa_sicys
			." And predsol_numero_oc = '".$_POST['oc']."'"
			." And predsol_estado > 6";
		$run_ingreso = $db->ejecutar($sql_ingreso);
		$num_ingreso = mysqli_num_rows($run_ingreso);
		if ($num_ingreso > 0) {
			$result = "Orden de compra: ".$_POST['oc']." ya se ha ingresado.";
		} else {
			$sql_total = "Select sum(predsol_total) as total From ".$conf->getTbl_predsol()
				." Where id_empresa = ".$empresa_sicys
				." And predsol_numero_oc = '".$_POST['oc']."'"
				." And predsol_estado = 6";
			//$result = $sql_total;
			$run_total = $db->ejecutar($sql_total);
			$row_total = mysqli_fetch_array($run_total);
			if ($row_total[0] <> $_POST[val]) {
				$result = "Valor de documento $".$_POST['val']." y orden $".$row_total[0].", no son iguales, verifique.";
				//$result = "Valor de documento y orden, no son iguales, verifique.";
			} else {
				$result = "OK";
				//$result = $sql_total;
			}
		}
	}
	echo $result;
}
/*
 * Pone marca en O.C. de ingresada
*/
function marcaOCS() {

	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	$result = "OK";
	$rtn = '0';

	/* Detalle de la empresa */
	$sql_empresa = "Select * From ".$conf->getTbl_empresa()." Where id_empresa = '" . $_POST['empresa'] . "'";
	$run_empresa = $db->ejecutar($sql_empresa);
	$row_empresa = mysqli_fetch_array ( $run_empresa );
	$array ['empresa'] [] = $row_empresa;

	/* Nos traemos el detalle */
	$sql_detalle = "Select d.*, c.cc_codigo From " . $conf->getTbl_predsol()." d"
		." join ".$conf->getTbl_cecosto()." c"
		." On c.id_cc = d.id_cc"
		. " Where d.predsol_numero_oc = '" . $_POST ['oc'] . "'"
		. " And d.predsol_cantidad_aut > 0";
	$run = $db->ejecutar($sql_detalle);
	while ( $row = mysqli_fetch_array ( $run ) ) {
		$array ['detalle'] [] = $row;
	}
	// Extrae informacion de la Orden
	$_Sql = "Select id_proveedor From " . $conf->getTbl_predsol()
	. " Where predsol_numero_oc = '" . $_POST ['oc'] . "'";
	$_Qry = $db->ejecutar($_Sql);
	$_Row = mysqli_fetch_array ( $_Qry );

	// Codigo de proveedor para buscar nombre de categoria
	$_Sql1 = "Select * From " . $conf->getTbl_proveedor() . " Where id_proveedor = '" . $_Row [0] . "'";
	$_Qry1 = $db->ejecutar ( $_Sql1 );
	$_Row1 = mysqli_fetch_array ( $_Qry1 );
	$array ['proveedor'] [] = $_Row1;

	// Saca Nombre de Categoria
	$_Sql2 = "Select * From " . $conf->getTbl_categoria() . " Where id_categoria = '" . $_Row1 [0] . "'";
	$_Qry2 = $db->ejecutar ( $_Sql2 );
	$_Row2 = mysqli_fetch_array ( $_Qry2 );
	$array ['categoria'] [] = $_Row2;
	$i = 0;
	foreach ($array['detalle'] as $field) {
		++$i;
		$dets[] = "Update ".$conf->getTbl_predsol()." Set"
			." predsol_estado = 7"
			." Where id_predsol = ".$field['id_predsol'];
		$heads[$i][] = "Select Count(*) From ".$conf->getTbl_predsol()
			." Where id_prehsol = ".$field['id_prehsol']." And"
			." predsol_estado = 6";
		$heads[$i][] = "Update ".$conf->getTbl_prehsol()." Set"
			." prehsol_estado = 7"
			." Where id_prehsol = ".$field['id_prehsol'];
		$heads[$i][] = "Insert Into ".$conf->getTbl_prehsol_stat()." Set "
			."id_prehsol = ".$field['id_prehsol'].", "
			."prehsol_stat = 7, "
			."prehsol_stat_desc = '".$conf->getEstadoSC('7')."', "
			."prehsol_stat_fecha = '".date("Y-m-d")."', "
			."prehsol_stat_hora = '".date("H:i:s")."', "
			."prehsol_stat_usuario = '".$_SESSION['u']."'";
	}

	/*
	 * Marca la requisicion de enviada a O.C.
	*/
	foreach($dets as $qry) {
		try {
			$db->ejecutar($qry);
		} catch (Exception $e) {
			$result = $e->getMessage();
			$rtn = '1';
		}
	}
	/*
	 * Marcamos el encabezado si ya se ha enviado todo el detalle a O.C.
	*/
	foreach ($heads as $head) {
		try {
			$run = $db->ejecutar($head[0]);
			$row = mysqli_fetch_array($run);
			if($row[0] <= 0) {
				try {
					$db->ejecutar($head[1]);
					$db->ejecutar($head[2]);
				} catch (Exception $e2) {
					$result = $e2->getMessage();
					$rtn = '1';
				}
			}
		} catch (Exception $e1) {
			$result = $e1->getMessage();
			$rtn = '1';
		}
	}
	echo $result;
}
?>