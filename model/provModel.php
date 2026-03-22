<?php
/*
 * Listado proveedores
 */
function listar($id) {
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select * from " . $conf->getTbl_lista ()
		." Where id_proveedor = ".$id
		. " Order by prod_codigo ";
	$run = $db->ejecutar ( $sql );
	while ( $row = mysqli_fetch_array ( $run ) ) {
		$array [] = $row;
	}
	return $array;
}
// Llena formulario de modificacion de lista de precio
function modifica() {
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select * from " . $conf->getTbl_lista () . " where id_lista=" . $_GET ['id'];
	$run = $db->ejecutar ( $sql );
	while ( $row = mysqli_fetch_array ( $run ) ) {
		$array [] = $row;
	}
	return $array;
}

// llena form con todo y nombre de proveedor
function nomProv() {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql2 = "select * from lista , proveedor  where id_lista=" . $_GET ['id'];
		$run = $db->ejecutar ( $sql2 );
		while ( $row = mysqli_fetch_array ( $run ) ) {
			$array [] = $row;
		}
		$result = $array;
	}catch(Exception $e){
		$result = $e->getMessage();
	}
	return $result;
}
// Adiciona proveedor
function addProv() {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql2 = "insert into ".$conf->getTbl_lista()." (id_lista, id_proveedor, prod_codigo, lis_cant, lis_empaque, lis_precio, lis_fin_vigencia, lis_fecha, lis_hora, lis_usuario) values (0,".$_POST['id_proveedor'].",'".strtoupper($_POST['prod_codigo'])."',".$_POST['lis_cant'].",".$_POST['lis_empaque'].",".$_POST['lis_precio'].",'".$_POST['lis_fin_vigencia']."','".date("Y-m-d")."','".date("H:i:s")."','".strtoupper($_POST['usr_crea'])."')";
		$run = $db->ejecutar ( $sql2 );
	} catch (Exception $e) {
		echo $e->getMessage();
	}
}
//borra item de la bd
function rmProv() {
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql2 = "delete from lista where id_lista=" . $_GET ['id'];
	$run = $db->ejecutar ( $sql2 );
}

function busqueda(){
	$producto=$_POST['busqueda'];
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql2 = "select * from ". $conf->getTbl_lista(). " where prod_codigo=" . $producto;
	$run55 = $db->ejecutar ( $sql2 );
	while ( $row55 = mysqli_fetch_array ( $run55 ) ) {
		$array [] = $row55;
	}
	return $array;
}
function listarProducto(){
	if (isset($_POST['sl']) && $_POST['sl'] != "") {
		try {
			list($l, $sl) = explode(',',$_POST['sl']);
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sql = "select * from " . $conf->getTbl_producto()." a"
				." Where a.sl_linea = ".$l
				." And a.sl_sublinea =".$sl
				." order by a.prod_codigo";
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
 * Lista Proveedores en O.C.
 */
function Proveedores_Cambiar() {
	try {
		$db = DB::getInstance();
		$conf = Configuracion::getInstance();
		$sql = "Select * From nvoprov";
		$run = $db->ejecutar($sql);
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
	return $array;
}
/*
 * Retorna la informacion de lista vigente por producto
 */
function listaVigente(){
	if (isset($_REQUEST['sl']) && $_REQUEST['sl'] != "") {
		try {
			$prod = $_REQUEST['sl'];
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sql = "select l.id_proveedor, p.prov_nombre, l.lis_precio, l.lis_fin_vigencia From " . $conf->getTbl_lista(). " l"
					." Join ".$conf->getTbl_proveedor()." p"
					." On p.id_proveedor = l.id_proveedor"
					." Where l.prod_codigo = '".$prod."'"
					//." And l.lis_fin_vigencia >= '".date('Y-m-d')."'"
					." Order by l.lis_precio";
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ( $run ) ) {
					$row['vigente'] = '';
					$row['mensaje'] = '';
					if($row[3] < date('Y-m-d')){
						$row['vigente'] = 'disabled';
						$row['mensaje'] = 'vencido';
					}
					$array [] = $row;
				}
			} else {
				$array = null;
			}
		} catch (Exception $e) {
			$array = $e->getMessage();
		}
	} else {
		$array = null;
	}
	return $array;
}

/*
 * Retorna la informacion de lista vigente por producto
*/
function listaVigenteMayoreo(){
	if (isset($_REQUEST['sl']) && $_REQUEST['sl'] != "") {
		try {
			$prod = $_REQUEST['sl'];
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sql = "select l.id_proveedor, p.prov_nombre, l.lis_prec_may, l.lis_min_may, l.lis_fin_vigencia From " . $conf->getTbl_lista(). " l"
					." Join ".$conf->getTbl_proveedor()." p"
					." On p.id_proveedor = l.id_proveedor"
					." Where l.prod_codigo = '".$prod."'"
					//." And l.lis_fin_vigencia >= '".date('Y-m-d')."'"
					." And l.lis_prec_may > 0"
					." Order by l.lis_prec_may, l.lis_min_may";
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ( $run ) ) {
					$row['vigente'] = '';
					$row['mensaje'] = '';
					if($row[4] < date('Y-m-d')){
						$row['vigente'] = 'disabled';
						$row['mensaje'] = 'vencido';
					}
					$array [] = $row;
				}
			} else {
				$array = null;
			}
		} catch (Exception $e) {
			$array = $e->getMessage();
		}
	} else {
		$array = null;
	}
	return $array;
}