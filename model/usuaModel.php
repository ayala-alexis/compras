<?php
/*
 * Retorna todos los datos del usuario que esta conectado
 */
function datosUsuario(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select usr_req, usr_sol, usr_oc, usr_email, id_rol from " . $conf->getTbl_usuario()
			." where id_usuario=".$_GET['idprofile'];
	try{
		$array = array();
		$run = $db->ejecutar($sql);
		$row = mysqli_fetch_array($run);
		if ($row['usr_req'] == '1') $row["estado_req"] = "selected";
		if ($row['usr_sol'] == '1') $row["estado_sol"] = "selected";
		if ($row['usr_oc'] == '1') $row["estado_oc"] = "selected";
		$array [] = $row;
		// unidades y valores
		$sql = "select acc_und, acc_vls from " . $conf->getTbl_acc_und_vls()
				." where id_usuario=".$_GET['idprofile'];
		$run = $db->ejecutar($sql);
		$row = mysqli_fetch_array($run);
		if ($row['acc_und'] == '1') $row["estado_und"] = "selected";
		if ($row['acc_vls'] == '1') $row["estado_vls"] = "selected";
		$array [] = $row;
		// llenamos el arreglo
		$result = $array;
	} catch(Exception $e){
		$result = $e->getMessage()." --ERROR EN INFORMACION DE USUARIO-- ";
	}
	return $result;
}
/*
 * Listado de Accesot segun rol digitado
 */
function listadoAccesos(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select * from " . $conf->getTbl_acc_modulo() . " where id_rol=" . $_GET['id'];
	try{
		$run = $db->ejecutar ( $sql );
		while ( $row = mysqli_fetch_array ( $run ) ) {
			$array [] = $row;
		}
		$result = $array;
	} catch(Exception $e){
		$result = $e->getMessage()." --ERROR EN EL LISTADO DE ACCESOS-- ";
	}
	return $result;
}

// Listado de roles

function listadoRoles(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select * from " . $conf->getTbl_rol()." Where rol_orden > 0 Order By rol_orden";
	try{
		$run = $db->ejecutar ( $sql );
		while ( $row = mysqli_fetch_array ( $run ) ) {
			$array [] = $row;
		}
		$result = $array;
	} catch(Exception $e){
		$result = $e->getMessage()." --ERROR EN EL LISTADO DE ROLES-- ";
	}
	return $result;
}

// Listado de Usuarios en Roles

function listadoUsuariosRoles(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select a.*, b.usr_usuario from ".$conf->getTbl_rol_user()
		." a Join ".$conf->getTbl_usuario()
		." b On a.id_usuario = b.id_usuario Where b.usr_estado = 'A' Order by a.id_rol, b.usr_usuario";
	try{
		$run = $db->ejecutar ( $sql );
		while ( $row = mysqli_fetch_array ( $run ) ) {
			$array [] = $row;
		}
		$result = $array;
	} catch(Exception $e){
		$result = $e->getMessage()." --ERROR EN EL LISTADO USUARIOS DE ROLES-- ";
	}
	return $result;
}

// Listado de Empresas y Centros de Costo

function listadoEmpresaCC(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select a.id_empresa, a.emp_nombre, b.id_cc, b.cc_codigo, b.cc_descripcion from " . $conf->getTbl_empresa()." a Join ".$conf->getTbl_cecosto()." b On a.id_empresa = b.id_empresa Order by a.id_empresa, b.id_cc";
	try{
		$run = $db->ejecutar ( $sql );
		while ( $row = mysqli_fetch_array ( $run ) ) {
			// Si se ha seleccionado profile
			if (isset($_GET['idprofile']) && $_GET['idprofile'] != "") {
				$sqlx = "select Count(*) from " . $conf->getTbl_acc_emp_cc()." Where id_usuario = ".$_GET['idprofile']." And id_empresa = ".$row[0]." And id_cc = ".$row[2]." Group by id_usuario, id_empresa, id_cc";
				$runx = $db->ejecutar ( $sqlx );
				$rowx = mysqli_fetch_array($runx);
				if ($rowx[0] >= 1) {
					$row['estado'] = 'selected';
				} else {
					$row['estado'] = '';
				}
			} else {
				$row['estado'] = '';
			}
			$array[] = $row;
		}
		$result = $array;
	} catch(Exception $e){
		$result = $e->getMessage()." --ERROR EN EL LISTADO DE EMPRESAS Y CC-- ";
	}
	return $result;
}

// Listado de Modulos

function listadoModulos(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select * from " . $conf->getTbl_modulo()." Order by mod_orden";
	try{
		$run = $db->ejecutar ( $sql );
		while ( $row = mysqli_fetch_array ( $run ) ) {
			// Si se ha seleccionado profile
			if (isset($_GET['idprofile']) && $_GET['idprofile'] != "") {
				$sqlx = "SELECT Count(*) FROM " . $conf->getTbl_acc_modulo()." Where id_usuario = ".$_GET['idprofile']." And id_modulo = ".$row['id_modulo']." Group by id_usuario, id_modulo";
				$runx = $db->ejecutar ( $sqlx );
				$rowx = mysqli_fetch_array($runx);
				if ($rowx[0] >= 1) {
					$row['estado'] = 'selected';
				} else {
					$row['estado'] = '';
				}
			} else {
				$row['estado'] = '';
			}
			// Llenamos arreglo todo
			$array [] = $row;
		}
		$result = $array;
	} catch(Exception $e){
		$result = $e->getMessage()." --ERROR EN EL LISTADO DE MODULOS-- ";
	}
	return $result;
}

// Listado de accesos disponibles por modulo

function listadoAccesoModulo(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select * from " . $conf->getTbl_modulo()." Order by mod_orden";
	try{
		$run = $db->ejecutar ( $sql );
		while ( $row = mysqli_fetch_array ( $run ) ) {
			try {
				$sqlx = "SELECT * FROM " . $conf->getTbl_acc_modulo_lista()
					." Where id_modulo = ".$row['id_modulo']
					." ORDER BY id_modulo, acc_modulo_lista_categoria, id_acc_modulo_lista";
				$runx = $db->ejecutar ( $sqlx );
				while($rowx = mysqli_fetch_array($runx)) {
					// Si se ha seleccionado profile
					$rowx['estado1'] = "";
					$rowx['estado2'] = "";
					$rowx['estado3'] = "";
					$rowx['estado4'] = "";
					$rowx['estado5'] = "";
					if (isset($_GET['idprofile']) && $_GET['idprofile'] != "") {
						// Mostramos
						$sqly = "SELECT * FROM ".$conf->getTbl_acc_modulo()
							." Where "
							."id_usuario = ".$_GET['idprofile']." AND "
							."id_acc_modulo_lista = ".$rowx['id_acc_modulo_lista'];
						$runy = $db->ejecutar($sqly);
						$rowy = mysqli_fetch_array($runy);
						if ($rowy['acc_add'] == '1'){ $rowx['estado1'] = "checked";}
						if ($rowy['acc_edit'] == '1'){ $rowx['estado2'] = "checked";}
						if ($rowy['acc_del'] == '1'){ $rowx['estado3'] = "checked";}
						if ($rowy['acc_xls'] == '1'){ $rowx['estado4'] = "checked";}
						if ($rowy['acc_aut'] == '1'){ $rowx['estado5'] = "checked";}
					}
					// Llenamos arreglo todo
					$rowx['acc_modulo_lista_hijo'] = $row['mod_hijo'];
					$array[] = $rowx;
				}
			} catch (Exception $e1) {
				$result = $e1->getMessage()." --ERROR EN EL LISTADO DE MODULOS-- ";
			}
		}
		$result = $array;
	} catch(Exception $e){
		$result = $e->getMessage()." --ERROR EN EL LISTADO DE MODULOS-- ";
	}
	return $result;
}

// Listado de centros de costo a los que el usuario tiene acceso

function listadoUsuarioCC(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "select * from " . $conf->getTbl_acc_emp_cc()." Where id_usuario = ".$_GET['idprofile']." Order by id_empresa, id_cc";
	try{
		$run = $db->ejecutar ( $sql );
		while ( $row = mysqli_fetch_array ( $run ) ) {
			$array [] = $row;
		}
		$result = $array;
	} catch(Exception $e){
		$result = $e->getMessage()." --ERROR EN EL LISTADO DE CC DE USUARIO-- ";
	}
	return $result;
}

?>
