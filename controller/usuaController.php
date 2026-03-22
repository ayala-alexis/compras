<?php
$_SESSION ['ie'] = $_GET ['ie'];
// Pantalla principal
function inicio() {
	include_once 'view/mttoUsuario.php';
}
// Manejo de seguridad
function seguridad(){
	// Abstraccion de Datos
	include_once 'model/usuaModel.php';
	$roles = listadoRoles(); // Listado de Roles
	$roles_user = listadoUsuariosRoles(); // Listado de Usuario por Rol
	$cc = listadoEmpresaCC(); // Listado de Empresas y sus C.C.
	$mods = listadoModulos(); // Listado de Modulos
	$modulo_lista = listadoAccesoModulo(); // Listado de accesos por modulo
	$datos_usr = datosUsuario();
	// Pantalla de Trabajo
	include_once 'view/seguridad2.php';
}
function seguridad2(){
	// Abstraccion de Datos
	include_once 'model/usuaModel.php';
	$roles = listadoRoles(); // Listado de Roles
	$roles_user = listadoUsuariosRoles(); // Listado de Usuario por Rol
	$cc = listadoEmpresaCC(); // Listado de Empresas y sus C.C.
	$mods = listadoModulos(); // Listado de Modulos
	$modulo_lista = listadoAccesoModulo(); // Listado de accesos por modulo
	$datos_usr = datosUsuario();
	// Pantalla de Trabajo
	include_once 'view/seguridad2.php';
}
// Verifica que usuario digitado no exista en los accesos
function verifica(){
	$form = $_POST['form'];
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		/*
		 * Verificamos que el usuario exista primero
		 */
		$sqlu = "Select count(*) from ".$conf->getTbl_usuario()." Where usr_usuario ='".strtoupper($form['usr_usuario'])."' Group By usr_usuario";
		try {
			$queryu = $db->ejecutar($sqlu);
			$rowu = mysqli_fetch_array($queryu);
			if ($rowu[0] >= 1) {
				$result = "Usuario ya existe.";
			} else {
				$result = "OK";
			}
		} catch ( Exception $e1 ) {
			$result = $e1->getMessage();
		}
	} catch ( Exception $e ) {
		$result = $e->getMessage();
	}
	echo $result;
}

/*
 * Centros de Costo Seleccionados para el perfil
 * Paso 1. Verifica si es rol o Usuario
 * Paso 2. Elimina los permisos actuales
 * Paso 3. Guarda los nuevos permisos
 */

function ccSeleccionados() {
	$form = $_POST['form'];
	$datos = $_POST['datos'];
	$modulo = $_POST['modulo'];
	$accesos = $_POST['accesos'];
	$funciones = $_POST['funciones'];
	$undvls = $_POST['undvls'];
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		// Borraremos primero los permisos que tienen
		//$fp = fopen("logos.txt","a");
		//fwrite($fp, 'Inicion de Ejecucion : '.date('l jS \of F Y h:i:s A').PHP_EOL);
		if($datos['tipo'] == 'rol') {
			/*
			 * Se ha seleccionado el Rol
			 * Paso 1. obtenemos todos los usuario que tiene asignado el rol
			 * Paso 2. borramos todos los acceso que tenga cada usuario
			 * Paso 3. metemos los acceso del rol
			 * Paso 4. metemos los acceso por cada usuario del rol
			 */
			// Borramos el acceso del rol principal y a los modulos
			$sql_user = "DELETE FROM " .$conf->getTbl_acc_emp_cc()." Where id_usuario = ".$datos['idprofile'];
			$db->ejecutar($sql_user);
			$sql_user = "DELETE FROM " .$conf->getTbl_acc_modulo()." Where id_usuario = ".$datos['idprofile'];
			$db->ejecutar($sql_user);
			// Metemos el acceso al rol principal
			foreach ($form as $k => $v) {
				// Sacamos la empresa y el c.c.
				list($cia, $cc) = split('~', $k);
				// 'X' identifica la compa�ia
				if ($cc != 'X') {
					$sql = "INSERT INTO " . $conf->getTbl_acc_emp_cc() . " SET "
						. "id_empresa = " . $cia . ", "
						. "id_cc = " . $cc . ", "
						. "id_usuario = " . $datos['idprofile'] . ", "
						. "acc_emp_cc_fecha = '" . date ( 'Y-m-d' ) . "', "
						. "acc_emp_cc_hora = '" . date ( 'H:i:s' ) . "', "
						. "acc_emp_cc_usuario = '" . strtoupper ( $datos ['usr_crea'] ) . "'";
					$db->ejecutar($sql);
				}
			}
			// Metemos el acceso a los modulos
			foreach ($modulo as $k => $v) {
				// Sacamos url y target del indice Url
				list($url, $target) = split('~', $k);
				// Sacamos idmodulo, descripcion y categoria del indice Key
				list($idmodulo, $descripcion, $categoria) = split('~', $v);
				$moduloData = array();
				foreach ($accesos as $k_acceso => $v_acceso) {
					// Sacamos de la llave descripcion de opcion y id de modulo
					list($descripcion1, $idmodulo1) = split('~', $k_acceso);
					// Sacamos del valor descripcion de opcion,
					// url, crea, modifica, elimina, exporta y autoriza
					list($descripcion2, $url2, $id_acc_mod, $crea, $modifica, $elimina, $exporta, $autoriza) = split('~', $v_acceso);
					/*
					 * SOLO PARA EL MODULO QUE ESTAMOS PROCESANDO
					*/
					if ($idmodulo == $idmodulo1) {
						$moduloData['idprofile'] = "(" . $idmodulo1 . ", '"
										.$descripcion1 . "', '"
										.$categoria . "', '"
										.$descripcion . "', "
										.$datos['idprofile'] . ", '"
										.$url2 . "', '"
										.$target . "', "
										.$crea . ", "
										.$modifica . ", "
										.$elimina . ", "
										.$exporta . ", "
										.$autoriza . ", "
										.$datos['idprofile'] . ", '"
										.date ( 'Y-m-d' ) . "', '"
										.date ( 'H:i:s' ) . "', "
										.$id_acc_mod . ", '"
										.strtoupper ( $datos ['usr_crea'] ) . "')";
					}
				}
				$sql = 'INSERT INTO '.$conf->getTbl_acc_modulo() . " ( "
					. "id_modulo, "
					. "mod_descripcion, "
					. "mod_categoria, "
					. "mod_categoria2, "
					. "id_usuario, "
					. "mod_url, "
					. "mod_target, "
					. "acc_add, "
					. "acc_edit, "
					. "acc_del, "
					. "acc_xls, "
					. "acc_aut, "
					. "id_rol, "
					. "acc_fecha, "
					. "acc_hora, "
					. "id_acc_modulo_lista, "
					. 'acc_usuario ) Values '.implode(',', $moduloData);
				$db->ejecutar($sql);
			}
			// Ahora nos traemos todo los usuarios asignados al rol
			$sql_rol_user = "SELECT * FROM ".$conf->getTbl_rol_user()." WHERE id_rol = ".$datos['idprofile'];
			$run_rol_user = $db->ejecutar($sql_rol_user);
			while ($row_rol_user = mysqli_fetch_array($run_rol_user)) {
				// Borramos los acceso por usuario
				$sql_rol = "DELETE FROM ".$conf->getTbl_acc_emp_cc()." WHERE id_usuario = ".$row_rol_user['id_usuario'];
				$db->ejecutar($sql_rol);
				// Borramos los acceso por usuario
				$sql_rol = "DELETE FROM ".$conf->getTbl_acc_modulo()." WHERE id_usuario = ".$row_rol_user['id_usuario'];
				$db->ejecutar($sql_rol);
				// Metemos el acceso para cada usuario del rol
				foreach ($form as $k => $v) {
					// Sacamos la empresa y el c.c.
					list($cia, $cc) = split('~', $k);
					// 'X' identifica la compa�ia
					if ($cc != 'X') {
						$sql = "INSERT INTO " . $conf->getTbl_acc_emp_cc() . " SET "
							. "id_empresa = " . $cia . ", "
							. "id_cc = " . $cc . ", "
							. "id_usuario = " . $row_rol_user['id_usuario'] . ", "
							. "acc_emp_cc_fecha = '" . date ( 'Y-m-d' ) . "', "
							. "acc_emp_cc_hora = '" . date ( 'H:i:s' ) . "', "
							. "acc_emp_cc_usuario = '" . strtoupper ( $datos ['usr_crea'] ) . "'";
						$db->ejecutar($sql);
					}
				}
				// Metemos el acceso a los modulos
				foreach ($modulo as $k => $v) {
					// Sacamos url y target del indice Url
					list($url, $target) = split('~', $k);
					// Sacamos idmodulo, descripcion y categoria del indice Key
					list($idmodulo, $descripcion, $categoria) = split('~', $v);
					foreach ($accesos as $k_acceso => $v_acceso) {
						// Sacamos de la llave descripcion de opcion y id de modulo
						list($descripcion1, $idmodulo1) = split('~', $k_acceso);
						// Sacamos del valor descripcion de opcion,
						// url, crea, modifica, elimina, exporta y autoriza
						list($descripcion2, $url2, $id_acc_mod, $crea, $modifica, $elimina, $exporta, $autoriza) = split('~', $v_acceso);
						/*
						 * SOLO PARA EL MODULO QUE ESTAMOS PROCESANDO
						*/
						if ($idmodulo == $idmodulo1) {
							$sql = "INSERT INTO " . $conf->getTbl_acc_modulo() . " SET "
								. "id_modulo = " . $idmodulo1 . ", "
								. "mod_descripcion =' " . $descripcion1 . "', "
								. "mod_categoria = '" . $categoria . "', "
								. "mod_categoria2 = '" . $descripcion . "', "
								. "id_usuario = " . $row_rol_user['id_usuario'] . ", "
								. "mod_url = '" . $url2 . "', "
								. "mod_target = '" . $target . "', "
								. "acc_add = " . $crea . ", "
								. "acc_edit = " . $modifica . ", "
								. "acc_del = " . $elimina . ", "
								. "acc_xls = " . $exporta . ", "
								. "acc_aut = " . $autoriza . ", "
								. "id_rol = " . $row_rol_user['id_rol'] . ", "
								. "acc_fecha = '" . date ( 'Y-m-d' ) . "', "
								. "acc_hora = '" . date ( 'H:i:s' ) . "', "
								. "id_acc_modulo_lista = " . $id_acc_mod . ", "
								. "acc_usuario = '". strtoupper ( $datos ['usr_crea'] ) . "'";
							$db->ejecutar($sql);
						}
					}
				}
			}
		} else {
			// UN SOLO USUARIO
			try {
				// Borramos unidades y valores
				$sql_user = "DELETE FROM " .$conf->getTbl_und_vls()." Where id_usuario = ".$datos['idprofile'];
				$db->ejecutar($sql_user);
				// Ponemos las funciones
				if(isset($undvls['und']) && $undvls['und']=='und') { $und=1; } else { $und=0; }
				if(isset($undvls['vls']) && $undvls['vls']=='vls') { $vls=1; } else { $vls=0; }
				$sql_acc = "INSERT INTO ".$conf->getTbl_acc_und_vls()." SET "
						. "id_usuario = ".$datos['idprofile'].", "
						. "acc_und = ".$und.", "
						. "acc_vls = ".$vls.", "
						. "acc_und_vls_fecha = '".date('Y-m-d')."', "
						. "acc_und_vls_hora = '".date('H:i:s')."', "
						. "acc_und_vls_usuario = '".strtoupper($datos['usr_crea'])."'";
				$db->ejecutar($sql_acc);
				// Ponemos las funciones
				if(isset($funciones['req']) && $funciones['req']=='req') { $req=1; } else { $req=0; }
				if(isset($funciones['sol']) && $funciones['sol']=='sol') { $sol=1; } else { $sol=0; }
				if(isset($funciones['oc']) && $funciones['oc']=='oc') { $oc=1; } else { $oc=0; }
				$sql_user = "Update ".$conf->getTbl_usuario()." Set usr_req = ".$req.", "
						."usr_sol = ".$sol.", usr_oc = ".$oc." "
						."Where id_usuario = ".$datos['idprofile'];
				$db->ejecutar($sql_user);

				// Borramos acceso a centros de costo
				$sql_user = "DELETE FROM " .$conf->getTbl_acc_emp_cc()." Where id_usuario = ".$datos['idprofile'];
				$db->ejecutar($sql_user);
				// Borramos accesos a modulos
				$sql_user = "DELETE FROM " .$conf->getTbl_acc_modulo()." Where id_usuario = ".$datos['idprofile'];
				$db->ejecutar($sql_user);

				// Metemos el acceso
				//$tiempo = microtime(true);
				$formData = array();

				//fwrite($fp, var_export($form,true).PHP_EOL);

				$fechaForm = date('Y-m-d');
				$horaForm = date('H:i:s');

				foreach ($form as $k => $v) {
					// Sacamos la empresa y el c.c.
					list($cia, $cc) = explode('~', $k);
					// 'X' identifica la compa�ia
					if ($cc != 'X') {
						$formData[] = "( "
								.$cia.", "
								.$cc.", "
								.$datos['idprofile'].", '"
								.$fechaForm."', '"
								.$horaForm."', '"
								.strtoupper($datos['usr_crea'])."' )";
					}
				}

				/*$tiempo_final = microtime(true);
				$tiempo_foreach = $tiempo_final-$tiempo;
				fwrite($fp, number_format($tiempo_foreach, 3, '.', '')." segundos - foreach1".PHP_EOL);*/

				$sql = "INSERT INTO " . $conf->getTbl_acc_emp_cc() . " ( "
						. "id_empresa , "
						. "id_cc, "
						. "id_usuario, "
						. "acc_emp_cc_fecha, "
						. "acc_emp_cc_hora, "
						. "acc_emp_cc_usuario ) VALUES ".implode(',', $formData) ;

				$db->ejecutar($sql);


				// Metemos el acceso a los modulos
				$idmoduloData = array();
				//$tiempo = microtime(true);

				$fechaModulo = date('Y-m-d');
				$horaModulo = date('H:i:s');

				foreach ($modulo as $k => $v) {
					// Sacamos url y target del indice Url
					list($url, $target) = explode('~', $k);
					// Sacamos idmodulo, descripcion y categoria del indice Key
					list($idmodulo, $descripcion, $categoria) = explode('~', $v);
					foreach ($accesos as $k_acceso => $v_acceso) {
						// Sacamos de la llave descripcion de opcion y id de modulo
						list($descripcion1, $idmodulo1) = explode('~', $k_acceso);
						// Sacamos del valor descripcion de opcion,
						// url, crea, modifica, elimina, exporta y autoriza
						list($descripcion2, $url2, $id_acc_mod, $crea, $modifica, $elimina, $exporta, $autoriza) = explode('~', $v_acceso);
						/*
						 * SOLO PARA EL MODULO QUE ESTAMOS PROCESANDO
						 */
						if ($idmodulo == $idmodulo1) {
							
							$idmoduloData[] = "( "
									.$idmodulo1.", "
									."'".$descripcion1."', "
									."'". $categoria."', "
									."'". $descripcion."', "
									.$datos['idprofile'].", "
									."'".$url2."', "
									."'".$target."', "
									.$crea.", "
									.$modifica.", "
									.$elimina.", "
									.$exporta.", "
									.$autoriza.", "
									.$datos['idprofile'].", "
									."'".$fechaModulo."', "
									."'".$horaModulo."', "
									.$id_acc_mod.", "
									."'".strtoupper($datos['usr_crea'])."' )";	
						}
					}
				}

				/*$tiempo_final = microtime(true);
				$tiempo_foreach = $tiempo_final-$tiempo;
				fwrite($fp, number_format($tiempo_foreach, 3, '.', '')." segundos - foreach2".PHP_EOL);*/

				$sql = "INSERT INTO " . $conf->getTbl_acc_modulo() . " ( "
						."id_modulo, "
						."mod_descripcion, "
						."mod_categoria, "
						."mod_categoria2, "
						."id_usuario, "
						."mod_url, "
						."mod_target, "
						."acc_add, "
						."acc_edit, "
						."acc_del, "
						."acc_xls, "
						."acc_aut, "
						."id_rol, "
						."acc_fecha, "
						."acc_hora, "
						."id_acc_modulo_lista, "
						. "acc_usuario ) Values ".implode(',', $idmoduloData);

				$db->ejecutar($sql);

			} catch (Exception $e1) {
				echo $e1->getMessage();
			}
		}
		//fwrite($fp, 'Fin Archivo : '.date('l jS \of F Y h:i:s A').PHP_EOL);
		//fclose($fp);
	} catch (Exception $e) {
		echo $e->getMessage();
	}
}
/*
 *
 */
function pwd(){
	include_once 'view/Passwd.php';
}