<?php
/*
 * Retorna listado de empresas
 */
function ListaEmpresas(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = 'Select id_empresa, emp_nombre From '.$conf->getTbl_empresa();
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
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
 * retorna toda la informacion de una empresa
 */
function infoEmpresa($empresa){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = 'Select * From '.$conf->getTbl_empresa()
			." Where id_empresa = ".$empresa;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
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
 * retorna los centros de costo de una empresa
 */
function centroscosto($empresa){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = 'Select * From '.$conf->getTbl_cecosto()
		." Where id_empresa = ".$empresa;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
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
 * retorna un listado de bodegas
 */
function bodegas($empresa){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = 'Select * From '.$conf->getTbl_bodega()
		." Where id_empresa = ".$empresa;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
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
 *
 */
/*
 * retorna un listado de bodegas
*/
function presupuestos($empresa){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = 'Select * From '.$conf->getTbl_presupuesto()
			." Where id_empresa = ".$empresa;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
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
 * return @array
* Lista todos los centros de costo que tiene
* asignado el usuario para una empresa
*/
function listaAuth(){
	if (isset($_POST['id_empresa']) && $_POST['id_empresa'] != "") {
		try {
			$idEmpresa = $_POST['id_empresa'];
			$idCc = $_POST['id_cc'];
			$idTaGasto = $_POST['id_tagasto'];
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sql = "select p.aut_anyo, p.aut_mes, p.aut_signo, p.aut_valor, p.aut_fecha, p.aut_hora, p.aut_usuario from " . $conf->getTbl_autorizacion()." p"
					." Where p.id_empresa = ".$idEmpresa
					." And p.id_cc =".$idCc
					." And p.id_tagasto =".$idTaGasto
					." order by p.aut_anyo desc, p.aut_mes desc";
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
?>