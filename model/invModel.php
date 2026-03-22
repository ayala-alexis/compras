<?php
function detalleOC() {
	$conf = Configuracion::getInstance ();
	list ( $empresa_oc, $empresa_sicys ) = split ( "-", $_POST ['empresa'] );
	/* Nos vamos a la O.C. */
	$host_oc = $conf->getHostDB();
	$user_oc = $conf->getUserDB();
	$pass_oc = $conf->getPassDB();
	$bd_oc = $conf->getDbprov();
	//
	$tbl_det_oc = $conf->getTmovs () . $empresa_oc;
	$tbl_enc_oc = $conf->getTorden () . $empresa_oc;
	$tbl_prov_oc = $conf->getTprovee () . $empresa_oc;
	$tbl_catp_oc = $conf->getTcatego () . $empresa_oc;
	$tbl_cc_oc = $conf->getTccosto () . $empresa_oc;
	$tbl_obs_oc = $conf->getTobser () . $empresa_oc;

	$link = mysqli_connect ( $host_oc, $user_oc, $pass_oc, true );
	mysqli_select_db ( $bd_oc, $link ) or die ( mysqli_error () );

	/* Detalle de la empresa */
	$sql_empresa = "Select * From empresas Where CODIGO = '" . $empresa_oc . "'";
	$run_empresa = mysqli_query ( $sql_empresa, $link );
	$row_empresa = mysqli_fetch_array ( $run_empresa );
	$array ['empresa'] [] = $row_empresa;

	/* Nos traemos el detalle */
	$sql_detalle = "Select * From " . $tbl_det_oc . " Where NORDEN = '" . $_POST ['oc'] . "'";
	$run = mysqli_query ( $sql_detalle, $link ) or die ( mysqli_error () );
	while ( $row = mysqli_fetch_array ( $run ) ) {
		$array ['detalle'] [] = $row;
	}
	// Extrae informacion de la Orden
	$_Sql = "Select * From " . $tbl_enc_oc . " Where NORDEN = '" . $_POST ['oc'] . "'";
	$_Qry = mysqli_query ( $_Sql, $link ) or die ( "Info Orden : " . mysqli_error () );
	$_Row = mysqli_fetch_array ( $_Qry );
	$array ['encabezado'] [] = $_Row;

	// Codigo de proveedor para buscar nombre de categoria
	$_Sql1 = "Select * From " . $tbl_prov_oc . " Where CODIGO = '" . $_Row [1] . "'";
	$_Qry1 = mysqli_query ( $_Sql1, $link ) or die ( "Codigo de Proveedor : " . mysqli_error () );
	$_Row1 = mysqli_fetch_array ( $_Qry1 );
	$array ['proveedor'] [] = $_Row1;

	// Saca Nombre de Categoria
	$_Sql2 = "Select * From " . $tbl_catp_oc . " Where CODGRUPO = '" . $_Row1 [1] . "'";
	$_Qry2 = mysqli_query ( $_Sql2, $link ) or die ( "Categoria : " . mysqli_error () );
	$_Row2 = mysqli_fetch_array ( $_Qry2 );
	$array ['categoria'] [] = $_Row2;

	// Muesta centro de costo
	$_Sql3 = "Select * From " . $tbl_cc_oc . " Where FCODC = '" . $_Row [9] . "'";
	$_Qry3 = mysqli_query ( $_Sql3, $link ) or die ( "CC : " . mysqli_error () );
	$_Row3 = mysqli_fetch_array ( $_Qry3 );
	$array ['cc'] [] = $_Row3;

	// Observacion de Orden
	$_Sql4 = "Select * From " . $tbl_obs_oc . " Where OBORDEN = '" . $_POST ['oc'] . "'";
	$_Qry4 = mysqli_query ( $_Sql4, $link ) or die ( "Observacion : " . mysqli_error () );
	$_Row4 = mysqli_fetch_array ( $_Qry4 );
	$array ['observacion'] [] = $_Row4;

	/* */
	mysqli_close ( $link ) or die ( mysqli_error () );

	return $array;
}

function detallePreOC() {
	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	list ( $empresa_oc, $empresa_sicys ) = split ( "-", $_POST ['empresa'] );

	/* Detalle de la empresa */
	$sql_empresa = "Select * From ".$conf->getTbl_empresa()." Where id_empresa = '" . $empresa_sicys . "'";
	$run_empresa = $db->ejecutar($sql_empresa);
	$row_empresa = mysqli_fetch_array ( $run_empresa );
	$array ['empresa'] [] = $row_empresa;

	/* Nos traemos el detalle */
	$sql_detalle = "Select * From " . $conf->getTbl_predreq() 
		. " Where predreq_numero_oc = '" . $_POST ['oc'] . "'"
		. " And predreq_cantidad_aut > 0";
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

	return $array;
}

function detallePreOCS() {
	$conf = Configuracion::getInstance ();
	$db = DB::getInstance();
	list ( $empresa_oc, $empresa_sicys ) = split ( "-", $_POST ['empresa'] );

	/* Detalle de la empresa */
	$sql_empresa = "Select * From ".$conf->getTbl_empresa()." Where id_empresa = '" . $empresa_sicys . "'";
	$run_empresa = $db->ejecutar($sql_empresa);
	$row_empresa = mysqli_fetch_array ( $run_empresa );
	$array ['empresa'] [] = $row_empresa;
	
	/* Nos traemos el detalle */
	$sql_h1 = "Select id_prehsol From " . $conf->getTbl_predsol()
		. " Where predsol_numero_oc = '" . $_POST ['oc'] . "' Group by id_prehsol";
	$res_h1 = $db->ejecutar($sql_h1);
	$row_h1 = mysqli_fetch_array($res_h1);
	
	$sql_h = "Select * From ".$conf->getTbl_prehsol()
		." Where id_prehsol=".$row_h1[0];
	$res_h = $db->ejecutar($sql_h);
	$row_h = mysqli_fetch_array($res_h);

	$array['header'][] = $row_h;

	/* Nos traemos el detalle */
	$sql_detalle = "Select * From " . $conf->getTbl_predsol()
		. " Where predsol_numero_oc = '" . $_POST ['oc'] . "'"
		. " And predsol_cantidad_aut > 0";
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

	return $array;
}

/*
 * Listado de productos para analisis
*/
function listarAnal($id) {
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select p.id_producto, p.prod_codigo, p.prod_descripcion,"
		." p.prod_prov01, p.prod_prov_pre01, "
		." p.prod_prov02, p.prod_prov_pre02, "
		." p.prod_prov03, p.prod_prov_pre03 "
		."From " . $conf->getTbl_producto()." p "
		." Where p.sl_linea = '".substr($id, 0, 2)."' And "
		."p.sl_sublinea = '".substr($id, 2, 2)."' Order by p.prod_codigo";
	$run = $db->ejecutar ( $sql );
	while ( $row = mysqli_fetch_array ( $run ) ) {
		$array [] = $row;
	}
	return $array;
}
/*
 * echo $_POST['empresa']; echo "<br />"; echo $_POST['oc']; echo "<br />"; echo
 * $_POST['bodega']; echo "<br />";
 */
?>
