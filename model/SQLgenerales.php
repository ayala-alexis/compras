<?php
/*
 * return @array
 * Retorna los permisos para la url en uso
 */
function permisosURL(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$url = '?'.$_SERVER['QUERY_STRING'];
		$page = $url;
		$pos1 = stripos($url, 'page=');
		$tamaño = strlen(trim($url));
		if ($pos1 !== false) {
			$page = substr($url, 0, ($pos1-1));
		}
		$request = $_SERVER['QUERY_STRING'];
		$parsed = explode('&', $request);
		$getVars = array();
		foreach($parsed as $argument) {
			list($variable, $value) = explode('=', $argument);
			$getVars[$variable] = urldecode($value);
		}
		if ($getVars['a'] != 'inicio' && $getVars['a'] != 'emisor' && $getVars['a'] != 'tracole') {
			$getVars['a'] = 'inicio';
		}
		$page = '?c='.$getVars['c']."&a=".$getVars['a']."&id=".$getVars['id'];
		//echo $page;
		$sql = "Select acc_edit, acc_add, acc_del, acc_xls, acc_aut From "
				. $conf->getTbl_acc_modulo()
				. " Where id_usuario =".$_SESSION['i']
				." And mod_url = '".$page."'";
		//echo $sql;
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
function listaSublineas(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select sl_linea, sl_sublinea, sl_descripcion From "
			. $conf->getTbl_sublinea()
			." Where sl_sublinea > 0 "
			."Group By sl_linea, sl_sublinea "
			."Order By sl_linea, sl_sublinea";
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
 * Retorna nombre de proveedor desde codigo
 */
function nombreProveedor($id) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select prov_nombre From "
				. $conf->getTbl_proveedor()
				." Where id_proveedor = ".$id;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			$row = mysqli_fetch_array ($run);
			$array = $row[0];
		} else {
			$array = "** NO EXISTE **";
		}
	} catch (Exception $e) {
		$array = $e->getMessage();
	}
	return $array;
}
/*
 * Retorna datos de empresa
*/
function datosCia($idcia) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From "
				. $conf->getTbl_empresa()
				." Where id_empresa = ".$idcia;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
	} catch (Exception $e) {
		$array = $e->getMessage();
	}
	return $array;
}
/*
 * Retorna datos de proveedor
*/
function datosProveedor($idprovee) {
	try {
		if(empty($idprovee)){
			$idprovee = 0;
		}
		$db = DB::getInstance();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From "
				. $conf->getTbl_proveedor()
				." Where id_proveedor = ".$idprovee;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array[] = array(
					'prov_nvocod'=> '0',
					'prov_contacto1'=>'',
					'prov_dias' => '0'
				);
		}
	} catch (Exception $e) {
		$array = $e->getMessage();
	}
	return $array;
}
/*
 * Retorna nombre de categoria de solicitud de compra
*/
function qCategoria($_id) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select nombre_categoria From "
				. $conf->getTbl_tipo_categoria()
				." Where id_categoria = ".$_id;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
	} catch (Exception $e) {
		$array = $e->getMessage();
	}
	return $array;
}

/*
* Retorna los metodos de Pago de la base de O.C
*/

function MetodosPagoOC() {
	try {
		$db = DB::getInstance();
		$conf = Configuracion::getInstance();
		$db->conecta_OC();

		$sql = "Select id,fpago from tfpago order by id asc";
		$run = $db->ejecuta_OC($sql);

		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array();
		}
	} catch (Exception $e) {
		$array = $e->getMessage();
	}
	return $array;
}


/*
*	Retorna el nombre del proveedor
*/

function ReturnProveedor($id) {
	if(empty($id)){
		$id=-1;
	}
	try {
		$db = DB::getInstance();
		$conf = Configuracion::getInstance();

		$sql = "SELECT prov_nombre FROM proveedor where id_proveedor=".$id;
		$run = $db->ejecutar($sql);

		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array(array(''));
		}
	} catch (Exception $e) {
		$array = $e->getMessage();
	}
	return $array[0][0];
}

/*
*	Retorna el nombre del proveedor
*/

function ReturnNombreMetodoPago($id) {
	if(empty($id)){
		$id=-1;
	}
	try {
		$db = DB::getInstance();
		$conf = Configuracion::getInstance();
		$db->conecta_OC();

		$sql = "SELECT fpago FROM tfpago where id=".$id;
		$run = $db->ejecuta_OC($sql);

		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array(array(''));
		}
	} catch (Exception $e) {
		$array = $e->getMessage();
	}
	return $array[0][0];
}

/*
*	Retorna la Moneda de la Empressa
*/

function ReturnMonedaEmpressa($id) {
	if(empty($id)){
		$id=-1;
	}
	try {
		$db = DB::getInstance();
		$conf = Configuracion::getInstance();

		$sql = "select moneda from sicys.empresa where id_empresa=".$id;
		$run = $db->ejecutar($sql);

		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$array[] = $row;
			}
		} else {
			$array = array(array(''));
		}
	} catch (Exception $e) {
		$array = $e->getMessage();
	}
	return $array[0][0];
}