<?php

ini_set('display_errors', 0);

/**
 * @author wserpas
 *
 */
session_start();
require '../DB.php';
require '../Configuracion.php';
$db = DB::getInstance();
$conf = Configuracion::getInstance();
// Retorno
$rtn = 0;
$form = $_POST['form'];
$_SESSION['p'] = isset($form['num_pagi']) ? $form['num_pagi'] : 0;
// Veamos de que tabla
switch ($form['tabla']) {
	// TABLA USUARIO
	case 'usuario':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				$sql_ver = "Select * From " . $conf->getTbl_usuario() . " Where usr_usuario='" . strtoupper($form['usr_usuario']) . "'";
				$run_sql = $db->ejecutar($sql_ver);
				$cnt_sql = mysqli_num_rows($run_sql);
				if ($cnt_sql > 0) {
					$rtn = 1;
					$msg = "Usuario " . strtoupper($form['usr_usuario']) . " ya existe, verifique.";
				} else {
					$sql_user = "INSERT INTO " . $conf->getTbl_usuario() . " SET "
						. "usr_estado='A', " . "id_rol='" . $form['id_rol'] . "', "
						. "usr_usuario='" . strtoupper($form['usr_usuario']) . "', "
						. "usr_password='" . strtoupper($form['usr_password']) . "', "
						. "usr_usuario_c='" . strtoupper($form['usr_crea']) . "', "
						. "usr_fecha='" . date('Y-m-d') . "', "
						. "usr_hora='" . date('H:i:s') . "', "
						. "usr_req=0, "
						. "usr_sol=0, "
						. "usr_oc=0, "
						. "usr_nombre='" . strtoupper($form['usr_nombre']) . "'";
					$db->ejecutar($sql_user);
					/*
					 * Lo metemos a los accesos
					 */
					$sql_ver2 = "Select * From " . $conf->getTbl_usuario() . " Where usr_usuario='" . strtoupper($form['usr_usuario']) . "'";
					$run_sql2 = $db->ejecutar($sql_ver2);
					$row_sql2 = mysqli_fetch_array($run_sql2);
					$sql = "INSERT INTO " . $conf->getTbl_rol_user() . " SET "
						. "id_rol=" . $form['id_rol'] . ", "
						. "id_usuario=" . $row_sql2['id_usuario'] . ", "
						. "rol_user_fecha='" . date('Y-m-d') . "', "
						. "rol_user_hora='" . date('H:i:s') . "', "
						. "rol_user_usuario='" . strtoupper($form['usr_crea']) . "'";
				}
				break;
			// Adicion
			case 'chgpass':
				//$rtn=1;
				$sql = "UPDATE " . $conf->getTbl_usuario()
					. " SET "
					. "usr_password='" . strtoupper($form['usr_pasword'])
					. "' Where usr_usuario = '" . $form['usr_usuar'] . "'";
				break;
			case 'chgrol':
				$sql = "UPDATE rol_user "
					. " SET "
					. "id_rol='" . strtoupper($form['rol_user'])
					. "' Where id_usuario = '" . $form['usr_id'] . "'";
				$db->ejecutar($sql);
				//$rtn=1;
				$sql = "UPDATE " . $conf->getTbl_usuario()
					. " SET "
					. "id_rol='" . strtoupper($form['rol_user'])
					. "' Where usr_usuario = '" . $form['usr_usuar'] . "'";
				break;
			// Adicion
			case 'chgmail':
				$sql = "UPDATE " . $conf->getTbl_usuario()
					. " SET "
					. "usr_email='" . trim($form['correo'])
					. "' Where usr_usuario = '" . $form['usr_usuar'] . "'";
				break;
				break;
			// Modificacion
			case 'edit':
				$sql = "UPDATE " . $conf->getTbl_usuario()
					. " SET "
					. "id_rol='" . $form['id_rol'] . "', "
					. "usr_usuario_c='" . strtoupper($form['usr_crea']) . "', "
					. "usr_fecha='" . date('Y-m-d') . "', "
					. "usr_hora='" . date('H:i:s') . "', "
					. "usr_nombre='" . strtoupper($form['usr_nombre']) . "'"
					. "Where id_usuario = " . $form['id_usuario'];
				break;
			case 'delete':
				$sql1 = "DELETE FROM " . $conf->getTbl_usuario()
					. " Where id_usuario = " . $form['idprofile'];
				$db->ejecutar($sql1);
				$sql2 = "DELETE FROM " . $conf->getTbl_acc_emp_cc()
					. " Where id_usuario = " . $form['idprofile'];
				$db->ejecutar($sql2);
				$sql = "DELETE FROM " . $conf->getTbl_acc_modulo()
					. " Where id_usuario = " . $form['idprofile'];
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA EMPRESA
	case 'empresa':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				$sql = "INSERT INTO " . $conf->getTbl_empresa() . " SET "
					. "emp_razon='" . strtoupper($form['emp_razon']) . "', "
					. "emp_direccion='" . strtoupper($form['emp_direccion']) . "', "
					. "emp_nit='" . $form['emp_nit'] . "', "
					. "emp_registro='" . $form['emp_registro'] . "', "
					. "emp_usa_presupuesto=" . $form['emp_usa_presupuesto'] . ", "
					. "emp_origen_presupuesto='" . $form['emp_origen_presupuesto'] . "', "
					. "emp_observaciones='" . strtoupper($form['emp_observaciones']) . "', "
					. "emp_origen_cc='" . $form['emp_origen_cc'] . "', "
					. "emp_telefono='" . $form['emp_telefono'] . "', "
					. "emp_usuario='" . strtoupper($form['usr_crea']) . "', "
					. "emp_fecha='" . date('Y-m-d') . "', "
					. "emp_hora='" . date('H:i:s') . "', "
					. "emp_grande = '" . $form['emp_grande'] . "', "
					. "emp_nombre='" . strtoupper($form['emp_nombre']) . "'";
				break;
			// Modificacion
			case 'edit':
				$sql = "UPDATE " . $conf->getTbl_empresa() . " SET "
					. "emp_razon='" . strtoupper($form['emp_razon']) . "', "
					. "emp_direccion='" . strtoupper($form['emp_direccion']) . "', "
					. "emp_nit='" . $form['emp_nit'] . "', " . "emp_registro='" . $form['emp_registro'] . "', "
					. "emp_usa_presupuesto=" . $form['emp_usa_presupuesto'] . ", "
					. "emp_origen_presupuesto='" . $form['emp_origen_presupuesto'] . "', "
					. "emp_observaciones='" . strtoupper($form['emp_observaciones']) . "', "
					. "emp_origen_cc='" . $form['emp_origen_cc'] . "', " . "emp_telefono='" . $form['emp_telefono'] . "', "
					. "emp_usuario='" . strtoupper($form['usr_crea']) . "', "
					. "emp_fecha='" . date('Y-m-d') . "', "
					. "emp_hora='" . date('H:i:s') . "', "
					. "emp_grande = '" . $form['emp_grande'] . "', "
					. "emp_nombre='" . strtoupper($form['emp_nombre']) . "' "
					. "Where id_empresa = " . $form['id_empresa'];
				break;
			case 'delete':
				$sql = "DELETE FROM " . $conf->getTbl_empresa() . " Where id_empresa = " . $form['id_empresa'];
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA CENTROS DE COSTO
	case 'cecosto':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				$sql_ver1 = "Select * From " . $conf->getTbl_cecosto()
					. " Where id_empresa='" . $form['id_empresa']
					. "' And cc_codigo='" . $form['cc_codigo'] . "'";
				try {
					$run_sql1 = $db->ejecutar($sql_ver1);
					$cnt_sql1 = mysqli_num_rows($run_sql1);
				} catch (Exception $e) {
					$rtn = 1;
					$msg = $e->getMessage();
					break;
				}
				if ($cnt_sql1 > 0) {
					$rtn = 1;
					$msg = "Centro de Costo : " . $form['cc_codigo'] . " ya existe, verifique.";
				} else {
					$sql = "INSERT INTO " . $conf->getTbl_cecosto() . " SET "
						. "cc_codigo='" . $form['cc_codigo'] . "', "
						. "id_empresa='" . $form['id_empresa'] . "', "
						. "cc_usuario='" . strtoupper($form['usr_crea']) . "', "
						. "cc_fecha='" . date('Y-m-d') . "', "
						. "cc_hora='" . date('H:i:s') . "', "
						. "cc_presolc=1, "
						. "cc_solc=1, "
						. "cc_prereq=1, "
						. "cc_req=1, "
						. "cc_descripcion='" . strtoupper($form['cc_descripcion']) . "'";
				}
				break;
			// Modificacion
			case 'edit':
				$sql = "UPDATE " . $conf->getTbl_cecosto() . " SET "
					. "cc_codigo='" . $form['cc_codigo'] . "', "
					. "id_empresa='" . $form['id_empresa'] . "', "
					. "cc_usuario='" . strtoupper($form['usr_crea']) . "', "
					. "cc_fecha='" . date('Y-m-d') . "', "
					. "cc_hora='" . date('H:i:s') . "', "
					. "cc_descripcion='" . strtoupper($form['cc_descripcion']) . "' "
					. "Where id_empresa = " . $form['id_empresa']
					. " And id_cc = " . $form['id_cc'];
				break;
			case 'delete':
				$sql = "DELETE FROM " . $conf->getTbl_cecosto()
					. " Where id_cc = '" . $form['id_cc'] . "'";
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA PRESUPUESTO
	case 'presupuesto':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				list($tit, $det) = split(",", $form['gas_codigo']);
				$suma = $form['pres_pre01'] + $form['pres_pre02'] + $form['pres_pre03'] + $form['pres_pre04'] + $form['pres_pre05'] + $form['pres_pre06'] + $form['pres_pre07'] + $form['pres_pre08'] + $form['pres_pre09'] + $form['pres_pre10'] + $form['pres_pre11'] + $form['pres_pre12'];
				if ($suma > 0) {
					$sql = "INSERT INTO " . $conf->getTbl_presupuesto() . " SET " . "cc_codigo = " . $form['cc_codigo'] . ", " . "id_empresa = " . $form['id_empresa'] . ", " . "pres_anyo = " . $form['pres_anyo'] . ", " . "pres_usuario = '" . strtoupper($form['usr_crea']) . "', " . "pres_fecha = '" . date('Y-m-d') . "', " . "pres_hora = '" . date('H:i:s') . "', " . "gas_tit_codigo = '" . $tit . "', " . "pres_pre01 = '" . round($form['pres_pre01'], 2) . "', " . "pres_pre02 = '" . round($form['pres_pre02'], 2) . "', " . "pres_pre03 = '" . round($form['pres_pre03'], 2) . "', " . "pres_pre04 = '" . round($form['pres_pre04'], 2) . "', " . "pres_pre05 = '" . round($form['pres_pre05'], 2) . "', " . "pres_pre06 = '" . round($form['pres_pre06'], 2) . "', " . "pres_pre07 = '" . round($form['pres_pre07'], 2) . "', " . "pres_pre08 = '" . round($form['pres_pre08'], 2) . "', " . "pres_pre09 = '" . round($form['pres_pre09'], 2) . "', " . "pres_pre10 = '" . round($form['pres_pre10'], 2) . "', " . "pres_pre11 = '" . round($form['pres_pre11'], 2) . "', " . "pres_pre12 = '" . round($form['pres_pre12'], 2) . "', " . "gas_det_codigo='" . $det . "'";
				} else {
					$rtn = 1;
					$msg = "No se ha encontrado presupuesto para adicionar.";
				}
				break;
			// Eliminar
			case 'delete':
				$sql = "DELETE FROM " . $conf->getTbl_presupuesto() . " Where id_presupuesto = '" . $form['id_presupuesto'] . "'";
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA PRESUPUESTO
	case 'autorizacion':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				$sql = "INSERT INTO " . $conf->getTbl_autorizacion()
					. " SET "
					. "id_empresa = " . $form['id_empresa'] . ", "
					. "id_cc = " . $form['id_cc'] . ", "
					. "id_tagasto = " . $form["id_tagasto"] . ", "
					. "aut_anyo = " . $form['aut_anyo'] . ", "
					. "aut_mes = " . $form['aut_mes'] . ", "
					. "aut_usuario = '" . strtoupper($form['usr_crea']) . "', "
					. "aut_fecha = '" . date('Y-m-d') . "', "
					. "aut_hora = '" . date('H:i:s') . "', "
					. "aut_signo = '" . $form["aut_signo"] . "', "
					. "aut_valor = '" . round($form['aut_valor'], 2) . "'";
				break;
			// Eliminar
			case 'delete':
				$sql = "DELETE FROM " . $conf->getTbl_autorizacion() . " Where id_autorizacion = " . $form['id_autorizacion'];
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA CUENTAS DE GASTO
	case 'tagasto':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				$sql = "INSERT INTO " . $conf->getTbl_tagasto() . " SET " . "gas_tit_codigo='" . $form['gas_tit_codigo'] . "', " . "gas_det_codigo='" . $form['gas_det_codigo'] . "', " . "id_empresa='" . $form['id_empresa'] . "', " . "gas_usuario='" . strtoupper($form['usr_crea']) . "', " . "gas_fecha='" . date('Y-m-d') . "', " . "gas_hora='" . date('H:i:s') . "', " . "gas_descripcion='" . strtoupper($form['gas_descripcion']) . "'";
				break;
			// Modificacion
			case 'edit':
				$sql = "UPDATE " . $conf->getTbl_tagasto() . " SET " . "gas_tit_codigo='" . $form['gas_tit_codigo'] . "', " . "gas_det_codigo='" . $form['gas_det_codigo'] . "', " . "id_empresa='" . $form['id_empresa'] . "', " . "gas_usuario='" . strtoupper($form['usr_crea']) . "', " . "gas_fecha='" . date('Y-m-d') . "', " . "gas_hora='" . date('H:i:s') . "', " . "gas_descripcion='" . strtoupper($form['gas_descripcion']) . "' " . "Where id_empresa = " . $form['id_empresa'] . " And id_tagasto = " . $form['id_tagasto'];
				break;
			case 'delete':
				$sql = "DELETE FROM " . $conf->getTbl_tagasto() . " Where id_tagasto = '" . $form['id_tagasto'] . "'";
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA CATEGORIA DE PROVEEDORES
	case 'categoria':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				$sql = "INSERT INTO " . $conf->getTbl_categoria() . " SET " . "cat_usuario='" . strtoupper($form['usr_crea']) . "', " . "cat_fecha='" . date('Y-m-d') . "', " . "cat_hora='" . date('H:i:s') . "', " . "cat_descripcon='" . mysqli_real_escape_string(strtoupper($form['cat_descripcon'])) . "'";
				break;
			// Modificacion
			case 'edit':
				$sql = "UPDATE " . $conf->getTbl_categoria() . " SET " . "cat_usuario ='" . strtoupper($form['usr_crea']) . "', " . "cat_fecha ='" . date('Y-m-d') . "', " . "cat_hora ='" . date('H:i:s') . "', " . "cat_descripcon ='" . mysqli_real_escape_string(strtoupper($form['cat_descripcon'])) . "' " . "Where id_categoria = " . $form['id_categoria'];
				break;
			case 'delete':
				$sql = "DELETE FROM " . $conf->getTbl_categoria() . " Where id_categoria = '" . $form['id_categoria'] . "'";
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA PROVEEDORES
	case 'proveedor':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				$SQL_COD = "Select MAX(prov_nvocod) From " . $conf->getTbl_proveedor();
				$RUN_COD = $db->ejecutar($SQL_COD);
				$FET_COD = mysqli_fetch_array($RUN_COD);
				$Nuevo = $FET_COD[0] + 1;
				$sql = "INSERT INTO " . $conf->getTbl_proveedor() . " SET "
					. "prov_usuario = '" . strtoupper($form['usr_crea']) . "', "
					. "prov_razon = '" . mysqli_real_escape_string(strtoupper($form['prov_razon'])) . "', "
					. "prov_direccion1 = '" . mysqli_real_escape_string(strtoupper($form['prov_direccion1'])) . "', "
					. "id_categoria = " . $form['id_categoria'] . ", "
					. "prov_dias = " . $form['prov_dias'] . ", "
					. "prov_telefono1 = '" . $form['prov_telefono1'] . "', "
					. "prov_telefono2 = '" . $form['prov_telefono2'] . "', "
					. "prov_fax = '" . $form['prov_fax'] . "', "
					. "prov_registro = '" . $form['prov_registro'] . "', "
					. "prov_email = '" . $form['prov_email'] . "', "
					. "prov_nit = '" . $form['prov_nit'] . "', "
					. "prov_contacto1 = '" . strtoupper($form['prov_contacto1']) . "', "
					. "prov_fecha = '" . date('Y-m-d') . "', "
					. "prov_hora = '" . date('H:i:s') . "', "
					. "prov_nvocod = " . $Nuevo . ", "
					. "prov_giro = '" . mysqli_real_escape_string(strtoupper($form['prov_giro'])) . "',"
					. "prov_tamanio = '" . mysqli_real_escape_string(strtoupper($form['prov_tamanio'])) . "',"
					. "prov_nombre = '" . mysqli_real_escape_string(strtoupper($form['prov_nombre'])) . "'";
				// O.C.
				$y = 1;
				$db->conecta_OC();
				do {
					$sql_OC = "INSERT INTO provee0" . $y . " SET "
						. "CODIGO='" . $Nuevo . "', "
						. "CODGRUPO='" . $form['id_categoria'] . "', "
						. "PROVEEDOR='" . strtoupper(safe_utf_decode($form['prov_nombre'])) . "', "
						. "CONTACTO='" . strtoupper(safe_utf_decode($form['prov_contacto1'])) . "', "
						. "TELEFO1='" . $form['prov_telefono1'] . "', "
						. "TELEFO2='" . $form['prov_telefono2'] . "', "
						. "FAX='" . $form['prov_fax'] . "', "
						. "DIRECC='" . strtoupper(safe_utf_decode($form['prov_direccion1'])) . "', "
						. "NREGIS='" . $form['prov_registro'] . "', "
						. "NIT='" . $form['prov_nit'] . "', "
						. "EMAIL='" . $form['prov_email'] . "', "
						. "NDIAS='" . trim($form['prov_dias']) . "', "
						. "FECHACREA='" . date('Y-m-d') . "', "
						. "USRCREA='" . $form['usr_crea'] . "', "
						. "Fecha_YMD='" . date("Y-m-d") . "'";
					$db->ejecuta_OC($sql_OC);
					$y++;
				} while ($y <= 7);
				$db->desconecta_OC();
				break;
			// Modificacion
			case 'edit':
				$sql = "UPDATE " . $conf->getTbl_proveedor() . " SET "
					. "prov_usuario='" . strtoupper($form['usr_crea']) . "', "
					. "prov_razon='" . mysqli_real_escape_string(strtoupper($form['prov_razon'])) . "', "
					. "prov_direccion1='" . mysqli_real_escape_string(strtoupper($form['prov_direccion1'])) . "', "
					. "id_categoria=" . $form['id_categoria'] . ", "
					. "prov_dias = " . $form['prov_dias'] . ", "
					. "prov_telefono1='" . $form['prov_telefono1'] . "', "
					. "prov_telefono2 = '" . $form['prov_telefono2'] . "', "
					. "prov_fax = '" . $form['prov_fax'] . "', "
					. "prov_registro = '" . $form['prov_registro'] . "', "
					. "prov_email = '" . $form['prov_email'] . "', "
					. "prov_nit='" . $form['prov_nit'] . "', "
					. "prov_contacto1='" . strtoupper($form['prov_contacto1']) . "', "
					. "prov_nombre='" . strtoupper($form['prov_nombre']) . "', "
					. "prov_fecha='" . date('Y-m-d') . "', "
					. "prov_giro = '" . mysqli_real_escape_string(strtoupper($form['prov_giro'])) . "',"
					. "prov_tamanio = '" . mysqli_real_escape_string(strtoupper($form['prov_tamanio'])) . "',"
					. "prov_hora='" . date('H:i:s') . "' "
					. "Where id_proveedor = " . $form['id_proveedor'];
				// O.C.
				$y = 1;
				$db->conecta_OC();
				do {
					$sql_OC = "UPDATE provee0" . $y . " SET "
						//."CODIGO='".$Nuevo."', "
						. "CODGRUPO='" . $form['id_categoria'] . "', "
						. "PROVEEDOR='" . strtoupper(safe_utf_decode($form['prov_nombre'])) . "', "
						. "CONTACTO='" . strtoupper(safe_utf_decode($form['prov_contacto1'])) . "', "
						. "TELEFO1='" . $form['prov_telefono1'] . "', "
						. "TELEFO2='" . $form['prov_telefono2'] . "', "
						. "FAX='" . $form['prov_fax'] . "', "
						. "DIRECC='" . strtoupper(safe_utf_decode($form['prov_direccion1'])) . "', "
						. "NREGIS='" . $form['prov_registro'] . "', "
						. "NIT='" . $form['prov_nit'] . "', "
						. "EMAIL='" . $form['prov_email'] . "', "
						. "NDIAS='" . trim($form['prov_dias']) . "', "
						. "FECHACREA='" . date('Y-m-d') . "', "
						. "USRCREA='" . $form['usr_crea'] . "', "
						. "Fecha_YMD='" . date("Y-m-d") . "'"
						. " WHERE CODIGO = '" . $form['prov_nvocod'] . "'";
					$db->ejecuta_OC($sql_OC);
					$y++;
				} while ($y <= 7);
				$db->desconecta_OC();
				break;
			case 'delete':
				//$sql = "DELETE FROM " . $conf->getTbl_proveedor () . " Where id_proveedor = '" . $form ['id_proveedor'] . "'";
				$rtn = 1;
				$msg = "Lo sentimos esta opcion se encuentra en mantenimiento, favor intentelo mas tarde.";
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA SUBLINEA
	case 'sublinea':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				$sql = "INSERT INTO " . $conf->getTbl_sublinea() . " SET ";
				$sql_empi = "Select id_empresa From " . $conf->getTbl_empresa();
				try {
					$run_empi = $db->ejecutar($sql_empi);
					$tablas = array();
					while ($row_empi = mysqli_fetch_array($run_empi)) {
						$tablas[] = $row_empi;
					}
					foreach ($tablas as $tabgas) {
						$sql .= 'id_tabgas' . $tabgas[0] . '=' . $form['id_tabgas' . $tabgas[0]] . ', ';
					}
				} catch (Exception $e) {
					$rtn = 1;
					$msg = $e->getMessage();
					break;
				}
				$sql .= "sl_usuario='" . strtoupper($form['usr_crea']) . "', "
					. "sl_sublinea='" . $form['sl_sublinea'] . "', "
					//. "id_tagasto=" . $form ['id_tagasto'] . ", "
					. "sl_linea='" . $form['sl_linea'] . "', "
					. "sl_fecha='" . date('Y-m-d') . "', "
					. "sl_hora='" . date('H:i:s') . "', "
					. "sl_descripcion='" . mysqli_real_escape_string(strtoupper($form['sl_descripcion'])) . "'";
				break;
			// Modificacion
			case 'edit':
				$sql = "UPDATE " . $conf->getTbl_sublinea() . " SET ";
				$sql_empi = "Select id_empresa From " . $conf->getTbl_empresa();
				try {
					$run_empi = $db->ejecutar($sql_empi);
					$tablas = array();
					while ($row_empi = mysqli_fetch_array($run_empi)) {
						$tablas[] = $row_empi;
					}
					foreach ($tablas as $tabgas) {
						$sql .= 'id_tabgas' . $tabgas[0] . '=' . $form['id_tabgas' . $tabgas[0]] . ', ';
					}
				} catch (Exception $e) {
					$rtn = 1;
					$msg = $e->getMessage();
					break;
				}
				$sql .= "sl_usuario='" . strtoupper($form['usr_crea']) . "', "
					. "sl_descripcion='" . mysqli_real_escape_string(strtoupper($form['sl_descripcion'])) . "', "
					. "sl_linea='" . $form['sl_linea'] . "', "
					. "sl_sublinea='" . $form['sl_sublinea'] . "', "
					//. "id_tagasto=" . $form ['id_tagasto'] . ", "
					. "sl_fecha='" . date('Y-m-d') . "', "
					. "sl_hora='" . date('H:i:s') . "' "
					. "Where id_sublinea = " . $form['id_sublinea'];
				break;
			case 'delete':
				$sql = "DELETE FROM " . $conf->getTbl_sublinea() . " Where id_sublinea = '" . $form['id_sublinea'] . "'";
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA PRODUCTO
	case 'producto':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Analisis
			case 'analisis':
				list($prov1, $prec1) = split(",", $form['primera'][0]);
				list($prov2, $prec2) = split(",", $form['segunda'][0]);
				//list ( $prov3, $prec3 ) = split ( ",", $form ['tercera'] );
				if (empty($prov2)) {
					$prov2 = 0;
				}
				if (empty($prec2)) {
					$prec2 = 0;
				}
				$sql = "UPDATE " . $conf->getTbl_producto() . " SET "
					. "prod_prov01='" . $prov1 . "', "
					. "prod_prov_pre01='" . $prec1 . "', "
					. "prod_prov02='" . $prov2 . "', "
					. "prod_prov_pre02='" . $prec2 . "' "
					/*. "prod_prov03='" . $prov3 . "', "
					. "prod_prov_pre03='" . $prec3 . "' "*/
					. "Where prod_codigo = '" . trim($form['prod_codigo']) . "'";
				break;
			// Adicion
			case 'add':
				list($linea, $sublinea) = split(",", $form['prod_slinea']);
				$sql_ver1 = "Select * From " . $conf->getTbl_producto()
					. " Where prod_codigo='" . strtoupper($form['prod_codigo']) . "'";
				try {
					$run_sql1 = $db->ejecutar($sql_ver1);
					$cnt_sql1 = mysqli_num_rows($run_sql1);
				} catch (Exception $e) {
					$rtn = 1;
					$msg = $e->getMessage();
					break;
				}
				if ($cnt_sql1 > 0) {
					$rtn = 1;
					$msg = "Producto : " . $form['prod_codigo'] . " ya existe, verifique.";
				} else {
					$sql = "INSERT INTO " . $conf->getTbl_producto() . " SET "
						. "prod_usuario='" . strtoupper($form['usr_crea']) . "', "
						. "prod_codigo='" . strtoupper($form['prod_codigo']) . "', "
						. "sl_sublinea='" . $sublinea . "', "
						. "sl_linea='" . $linea . "', "
						. "prod_fecha='" . date('Y-m-d') . "', "
						. "prod_solc='" . ($form['prod_solc'] == "on" ? '1' : '0') . "', "
						. "prod_req='" . ($form['prod_req'] == "on" ? '1' : '0') . "', "
						. "prod_hora='" . date('H:i:s') . "', "
						. "prod_descripcion='" . mysqli_real_escape_string(strtoupper($form['prod_descripcion'])) . "',"
						. "prod_observacion='" . mysqli_real_escape_string(strtoupper($form['prod_observacion'])) . "'";
				}
				break;
			// Creacion desde trabajo en solicitud de compra
			case 'add_assign':
				list($linea, $sublinea, $gastit, $gasdet) = split(",", $form['prod_slinea']);
				$sql_ver1 = "Select * From " . $conf->getTbl_producto()
					. " Where prod_codigo='" . strtoupper($form['prod_codigo']) . "'";
				try {
					$run_sql1 = $db->ejecutar($sql_ver1);
					$cnt_sql1 = mysqli_num_rows($run_sql1);
				} catch (Exception $e) {
					$rtn = 1;
					$msg = $e->getMessage();
					break;
				}
				if ($cnt_sql1 > 0) {
					$rtn = 1;
					$msg = "Producto : " . $form['prod_codigo'] . " ya existe, verifique.";
				} else {
					$sql = "INSERT INTO " . $conf->getTbl_producto() . " SET "
						. "prod_usuario='" . strtoupper($form['usr_crea']) . "', "
						. "prod_codigo='" . strtoupper($form['prod_codigo']) . "', "
						. "sl_sublinea='" . $sublinea . "', "
						. "sl_linea='" . $linea . "', "
						. "prod_fecha='" . date('Y-m-d') . "', "
						. "prod_solc='" . $form['prod_solc'] . "', "
						. "prod_req='" . $form['prod_req'] . "', "
						. "prod_hora='" . date('H:i:s') . "', "
						. "prod_descripcion='" . mysqli_real_escape_string(strtoupper($form['prod_descripcion'])) . "',"
						. "prod_observacion='" . mysqli_real_escape_string(strtoupper($form['prod_observacion'])) . "'";
					// Asigna en predsol
					$SQL_ass = "Update " . $conf->getTbl_predsol() . " Set "
						. "prod_codigo = '" . strtoupper($form['prod_codigo']) . "',"
						. "sl_sublinea='" . $sublinea . "', "
						. "sl_linea='" . $linea . "', "
						. "predsol_titgas=" . $gastit . ", "
						. "predsol_prec_uni=0, "
						. "predsol_total=0, "
						. "id_proveedor=0, "
						. "predsol_detgas=" . $gasdet
						. " Where id_predsol = " . $form['id_predsol'];
					try {
						$db->ejecutar($SQL_ass);
					} catch (Exception $e_ass) {
						$rtn = 1;
						$msg = $e_ass->getMessage();
						break;
					}
				}
				break;
			// Modificacion
			case 'edit':
				list($linea, $sublinea) = split(",", $form['prod_slinea']);
				$sql = "UPDATE " . $conf->getTbl_producto() . " SET "
					. "prod_usuario='" . strtoupper($form['usr_crea']) . "', "
					. "sl_sublinea='" . $sublinea . "', "
					. "sl_linea='" . $linea . "', "
					. "prod_solc='" . ($form['prod_solc'] == "on" ? '1' : '0') . "', "
					. "prod_req='" . ($form['prod_req'] == "on" ? '1' : '0') . "', "
					. "prod_fecha='" . date('Y-m-d') . "', "
					. "prod_hora='" . date('H:i:s') . "', "
					. "prod_descripcion='" . mysqli_real_escape_string(strtoupper($form['prod_descripcion'])) . "',"
					. "prod_observacion='" . mysqli_real_escape_string(strtoupper($form['prod_observacion'])) . "' "
					. "Where id_producto = " . $form['id_producto'];
				break;
			case 'delete':
				$sql = "DELETE FROM " . $conf->getTbl_producto() . " Where id_producto = '" . $form['id_producto'] . "'";
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// TABLA PRODUCTO CONSUMO INTERNO
	case 'ci_producto':
		// Veamos que accion en tabla
		break;
	// TABLA BODEGA
	case 'bodega':
		// Veamos que accion en tabla
		switch ($form['accion']) {
			// Adicion
			case 'add':
				$SQL_bod = "Select bod_codigo From " . $conf->getTbl_bodega() . " Where id_empresa = " . $form['id_empresa'] . " Order By bod_codigo";
				$EXEC_bod = $db->ejecutar($SQL_bod);
				$i = 1;
				$a = array();
				while ($FETCH_bod = mysqli_fetch_array($EXEC_bod)) {
					$a[] = $FETCH_bod[0];
				}
				foreach ($a as $k) {
					if ($i != $k) {
						break;
					} else {
						$i++;
					}
				}
				$bod_code = $i;
				$sql = "INSERT INTO " . $conf->getTbl_bodega() . " SET " . "id_empresa='" . $form['id_empresa'] . "', " . "bod_usuario='" . strtoupper($form['usr_crea']) . "', " . "bod_fecha='" . date('Y-m-d') . "', " . "bod_hora='" . date('H:i:s') . "', " . "bod_default='0', " . "bod_codigo=" . $bod_code . ", " . "bod_descripcion='" . strtoupper($form['bod_descripcion']) . "'";
				break;
			// Modificacion
			case 'edit':
				$sql = "UPDATE " . $conf->getTbl_bodega() . " SET " . "id_empresa='" . $form['id_empresa'] . "', " . "bod_usuario='" . strtoupper($form['usr_crea']) . "', " . "bod_fecha='" . date('Y-m-d') . "', " . "bod_hora='" . date('H:i:s') . "', " . "bod_default='0', " . "bod_descripcion='" . strtoupper($form['bod_descripcion']) . "' " . "Where id_empresa = " . $form['id_empresa'] . " And id_bodega = " . $form['id_bodega'];
				break;
			case 'delete':
				$sql = "DELETE FROM " . $conf->getTbl_bodega() . " Where id_bodega = '" . $form['id_bodega'] . "'";
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// Tabla Lista de precios de proveedor
	case 'lista':
		switch ($form['accion']) {
			case 'add':
				list($l, $sl) = explode(',', $form['sl']);
				//
				$campoOrden = 'prod_prov' . $form['prov_orden'];
				$campoPreOrden = 'prod_prov_pre' . $form['prov_orden'];
				//
				/*$sql_ver = "Select prod_prov01, prod_prov02, prod_prov03, prod_prov04, prod_prov05 From ".$conf->getTbl_producto()
					." Where prod_codigo = '" . $form ['producto']."'";
				$runVer = $db->ejecutar($sql_ver);
				$rowVer = mysqli_fetch_array($runVer);
				if ($rowVer["$campoOrden"] > 0) {
					$rtn = 1;
					$msg = 'Producto ya tiene un proveedor en esa posicion : '.$rowVer["$campoOrden"];
					break;
				}
				$sqlUpd = "UPDATE " . $conf->getTbl_producto() . " SET "
					.$campoOrden ."=". $form ['id_proveedor'] . ", "
					.$campoPreOrden ."=". $form ['precio']
					." Where prod_codigo = '" . $form ['producto']."'";
				try {
					$db->ejecutar($sqlUpd);
				}catch (Exception $e_list){
					$rtn = 1;
					$msg = $e_list->getMessage ();
					break;
				}*/

				if ($form["precio"] <= 0) {
					$rtn = 1;
					$msg = "Precio no valido, verifique!";
				} elseif ($form["lis_prec_may"] > 0 && $form["lis_min_may"] <= 0) {
					$rtn = 1;
					$msg = "Digite minimo de mayoreo!";
				} elseif ($form["lis_prec_may"] <= 0 && $form["lis_min_may"] > 0) {
					$rtn = 1;
					$msg = "Digite precio de mayoreo!";
				} else {

					/*$sqlUpd = "UPDATE " . $conf->getTbl_producto() . " SET "
							."prod_prov01 = ".$form['id_proveedor'].", "
							."prod_prov02 = ".$form['id_proveedor'].", "
							."prod_prov_pre01 = " . $form['precio'] . ", "
							."prod_prov_pre02 = " . $form['lis_prec_may']
							." Where prod_codigo = '" . $form ['producto']."'";
					try {
						$db->ejecutar($sqlUpd);
					}catch (Exception $e_list){
						$rtn = 1;
						$msg = $e_list->getMessage ();
						break;
					}*/

					$sql = "INSERT INTO " . $conf->getTbl_lista() . " SET "
						. "id_proveedor = " . $form['id_proveedor'] . ","
						. "prod_codigo = '" . $form['producto'] . "', "
						. "prod_descripcion = '" . $form['descripcion'] . "', "
						. "lis_cant = 1, "
						. "sl_linea = '" . $l . "', "
						. "sl_sublinea = '" . $sl . "', "
						. "lis_empaque = 'UNIDAD', "
						. "lis_precio = " . $form['precio'] . ", "
						. "lis_prec_may = " . $form['lis_prec_may'] . ", "
						. "lis_min_may = " . $form['lis_min_may'] . ", "
						. "lis_fin_vigencia = '" . $form['vigencia'] . "', "
						. "lis_fecha = '" . date('Y-m-d') . "', "
						. "lis_usuario = '" . $form['lis_usuario'] . "', "
						. "prov_orden = '" . $form['prov_orden'] . "', "
						. "lis_hora = '" . date('H:i:s') . "' ";
				}
				break;
			case 'edit':
				$campoOrden = 'prod_prov' . $form['prov_orden'];
				$campoPreOrden = 'prod_prov_pre' . $form['prov_orden'];
				$campoOrden2 = 'prod_prov' . $form['prov_orden2'];
				$campoPreOrden2 = 'prod_prov_pre' . $form['prov_orden2'];
				//
				/*$sql_ver = "Select prod_prov01, prod_prov02, prod_prov03, prod_prov04, prod_prov05 From ".$conf->getTbl_producto()
				." Where prod_codigo = '" . $form ['producto']."'";
				$runVer = $db->ejecutar($sql_ver);
				$rowVer = mysqli_fetch_array($runVer);
				if ($rowVer["$campoOrden2"] > 0 && $form['prov_orden'] <> $form['prov_orden2']) {
					$rtn = 1;
					$msg = 'Producto ya tiene un proveedor en esa posicion : '.$rowVer["$campoOrden2"];
					break;
				}
				$sqlUpd = "UPDATE " . $conf->getTbl_producto() . " SET "
						.$campoOrden ."=0, "
						.$campoOrden2 ."=".$form['id_proveedor'].", "
						.$campoPreOrden2 ."=" . $form ['precio2'] . ", "
						.$campoPreOrden ."=0"
						." Where prod_codigo = '" . $form ['producto']."'";
				try {
					$db->ejecutar($sqlUpd);
				}catch (Exception $e_list){
					$rtn = 1;
					$msg = $e_list->getMessage ();
					break;
				}*/
				if ($form["precio2"] <= 0) {
					$rtn = 1;
					$msg = "Precio no valido, verifique!";
				} elseif ($form["precio3"] > 0 && $form["min_precio3"] <= 0) {
					$rtn = 1;
					$msg = "Digite minimo de mayoreo!";
				} elseif ($form["precio3"] <= 0 && $form["min_precio3"] > 0) {
					$rtn = 1;
					$msg = "Digite precio de mayoreo!";
				} else {

					/*try {
						$sqlUpd = "UPDATE " . $conf->getTbl_producto() . " SET "
							."prod_prov01 = ".$form['id_proveedor'].", "
							."prod_prov02 = ".$form['id_proveedor'].", "
							."prod_prov_pre01 = " . $form['precio2'] . ", "
							."prod_prov_pre02 = " . $form['precio3']
							." Where prod_codigo = '" . $form ['producto']."'";
						$db->ejecutar($sqlUpd);
					}catch (Exception $e_list){
						$rtn = 1;
						$msg = $e_list->getMessage ();
						break;
					}*/
					$sql = "UPDATE " . $conf->getTbl_lista() . " SET "
						. "lis_precio=" . $form['precio2'] . ", "
						. "lis_prec_may=" . $form['precio3'] . ", "
						. "lis_min_may=" . $form['min_precio3'] . ", "
						. "lis_fin_vigencia='" . $form['vigencia2'] . "', "
						. "lis_fecha='" . date('Y-m-d') . "', "
						//. "prov_orden='" .$form['prov_orden2'] . "', "
						. "lis_hora='" . date('H:i:s') . "' "
						. "Where id_lista = " . $form['id_lista'];
				}
				break;
			case 'delete':
				$campoOrden = 'prod_prov' . $form['prov_orden'];
				$campoPreOrden = 'prod_prov_pre' . $form['prov_orden'];
				/*$sqlUpd = "UPDATE " . $conf->getTbl_producto() . " SET "
					.$campoOrden ."=0, "
					.$campoPreOrden ."=0"
					." Where prod_codigo = '" . $form ['producto']."'";
				try {
					$db->ejecutar($sqlUpd);
				}catch (Exception $e_list){
					$rtn = 1;
					$msg = $e_list->getMessage ();
					break;
				}*/
				$sql = "DELETE FROM " . $conf->getTbl_lista()
					. " Where id_lista = " . $form['id_lista'];
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// Tabla de inventario
	case 'inventario':
		switch ($form['accion']) {
			case 'add':
				// Adicion, verifiacamos primero si no existe el codigo
				// caso contrario lo adicionamos, sino sumamos
				$costo_unitario = $form['inv_costo_compra'];
				$unidades_compradas = $form['inv_existencia'];
				$costo_movil_actual = 0.00;
				$existencia_actual = 0;
				$fecha_prom_actual = date('Y-m-d');
				try {
					$sql_ver = "Select prod_codigo, inv_existencia, inv_prom_movil, inv_prom_movil_fecha "
						. " From " . $conf->getTbl_inventario()
						. " Where id_empresa = " . $form['id_empresa']
						. " And bod_codigo = " . $form['bod_codigo']
						. " And cc_codigo = " . $form['cc_codigo']
						. " And prod_codigo = '" . $form['prod_codigo'] . "'";
					$run_ver = $db->ejecutar($sql_ver);
				} catch (Exception $e) {
					$rtn = 1;
					$msg = $e->getMessage();
				}
				// Ya existe, actualizamos
				if (mysqli_num_rows($run_ver) > 0) {
					$row = mysqli_fetch_array($run_ver);
					$costo_movil_actual = $row['inv_prom_movil'];
					$existencia_actual = $row['inv_existencia'];
					$fecha_prom_actual = $row['inv_prom_movil_fecha'];
					// Calculamos Costo Movil Nuevo
					$CMN = (($unidades_compradas * $costo_unitario) + ($existencia_actual * $costo_movil_actual)) / ($unidades_compradas + $existencia_actual);
					$CMN = round($CMN, 2);
					$sql = "Update " . $conf->getTbl_inventario()
						. " Set "
						. " inv_existencia = " . ($existencia_actual + $unidades_compradas) . ", "
						. " inv_costo_compra = " . $costo_unitario . ", "
						. " inv_prom_movil = " . $CMN . ", "
						. " inv_unidades = " . $unidades_compradas . ", "
						. " inv_existe_ant = " . $existencia_actual . ", "
						. " inv_prom_movil_fecha = '" . $row['inv_prom_movil_fecha'] . "' "
						. " Where id_empresa = " . $form['id_empresa']
						. " And bod_codigo = " . $form['bod_codigo']
						. " And cc_codigo = " . $form['cc_codigo']
						. " And prod_codigo = '" . $form['prod_codigo'] . "'";
					// Adicionamos
				} else {
					// Calculamos Costo Movil Nuevo
					$CMN = (($unidades_compradas * $costo_unitario) + ($existencia_actual * $costo_movil_actual)) / ($unidades_compradas + $existencia_actual);
					$CMN = round($CMN, 2);
					$sql = "INSERT INTO " . $conf->getTbl_inventario() . " SET "
						. "id_empresa=" . $form['id_empresa'] . ", "
						. "bod_codigo=" . $form['bod_codigo'] . ", "
						. "cc_codigo=" . $form['cc_codigo'] . ", "
						. "prod_codigo='" . $form['prod_codigo'] . "', "
						. "inv_existencia=" . $form['inv_existencia'] . ", "
						. "inv_costo_compra=" . $form['inv_costo_compra'] . ", "
						. "id_empresa_oc='" . $form['id_empresa_oc'] . "', "
						. "inv_prom_movil = " . $CMN . ", "
						. "inv_prom_movil_fecha='" . date('Y-m-d') . "', "
						. "inv_unidades = " . $unidades_compradas . ", "
						. "inv_fecha='" . date('Y-m-d') . "', "
						. "inv_hora='" . date('H:i:s') . "', "
						. "inv_usuario='" . strtoupper($form['inv_usuario']) . "'";
				}
				// El cualquiera de los dos casos debemos actualizar el costo promedio en el maestro
				try {
					$sql_up_cmn = "Update " . $conf->getTbl_producto()
						. " Set prod_prom_movil = " . $CMN . ", "
						. " prod_prom_movil_fecha = '" . $fecha_prom_actual . "' "
						. " Where prod_codigo = '" . $form['prod_codigo'] . "'";
					$db->ejecutar($sql_up_cmn);
				} catch (Exception $e) {
					$rtn = 1;
					$msg = $e->getMessage();
				}

				try {
					// Ahora lo llevamos al Kardex de Ingresos
					$sql_ing = "Insert Into ingreso Set"
						. " prod_codigo = '" . $form['prod_codigo'] . "', "
						. " prov_codigo = '" . $form['codprovee'] . "', "
						. " ing_fecha='" . date('Y-m-d') . "', "
						. " ing_hora='" . date('H:i:s') . "', "
						. " ing_usuario='" . strtoupper($form['inv_usuario']) . "', "
						. " ing_compte='" . $form['oc'] . "', "
						. " ing_cantidad = " . $unidades_compradas . ", "
						. " ing_prec_uni = " . $costo_unitario . ", "
						. " ing_exi_actual = " . $existencia_actual . ", "
						. " ing_prom_movil = " . $CMN . ", "
						. " ing_prom_movil_actual = " . $costo_movil_actual . ", "
						. " ing_fecha_prom_actual = '" . $fecha_prom_actual . "'";
					$db->ejecutar($sql_ing);
				} catch (Exception $e) {
					$rtn = 1;
					$msg = $e->getMessage();
				}
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// Tabla Kardex
	case 'kardex':
		switch ($form['accion']) {
			case 'add':
				$costo_unitario = $form['costo'];
				$unidades_compradas = $form['cantidad'];
				$costo_movil_actual = 0.00;
				$existencia_actual = 0;
				try {
					$sql_ver = "Select prod_codigo, inv_existencia, inv_prom_movil, inv_prom_movil_fecha "
						. " From " . $conf->getTbl_inventario()
						. " Where id_empresa = " . $form['id_empresa']
						. " And bod_codigo = " . $form['bod_codigo']
						. " And cc_codigo = " . $form['cc_codigo']
						. " And prod_codigo = '" . $form['prod_codigo'] . "'";
					$run_ver = $db->ejecutar($sql_ver);
				} catch (Exception $e) {
					$rtn = 1;
					$msg = $e->getMessage();
				}
				// Ya existe, calculemos el costo movil
				if (mysqli_num_rows($run_ver) > 0) {
					$row = mysqli_fetch_array($run_ver);
					$costo_movil_actual = $row['inv_prom_movil'];
					$existencia_actual = $row['inv_existencia'];
				}
				// Calculamos Costo Movil Nuevo
				$CMN = (($unidades_compradas * $costo_unitario) + ($existencia_actual * $costo_movil_actual)) / ($unidades_compradas + $existencia_actual);
				$CMN = round($CMN, 2);
				// La metemos al Kardex
				$sql = "INSERT INTO " . $conf->getTbl_kardex() . " SET "
					. "id_empresa=" . $form['id_empresa'] . ", "
					. "bod_codigo=" . $form['bod_codigo'] . ", "
					. "cc_codigo=" . $form['cc_codigo'] . ", "
					. "prod_codigo='" . $form['prod_codigo'] . "', "
					. "tipo_trans=" . $form['tipo_trans'] . ", "
					. "fecha_trans='" . date('Y-m-d') . "', "
					. "hora_trans='" . date('H:i:s') . "', "
					. "cantidad=" . $form['cantidad'] . ", "
					. "fecha_ref='" . date('Y-m-d') . "', "
					. "id_empresa_oc='" . $form['id_empresa_oc'] . "', "
					. "referencia_oc=" . $form['referencia_oc'] . ", "
					. "usuario_trans='" . strtoupper($form['usuario_trans']) . "', "
					. "costo = " . $form['costo'] . ", "
					. "costo_prom_movil = " . $CMN . ", "
					. "id_prehreq = " . $form['id_prehreq'] . ", "
					. "id_prehsol = " . $form['id_prehsol'] . ", "
					. "costo_prom_movil_actual = " . $costo_movil_actual;
				break;
			default:
				$rtn = 1;
				$msg = "Accion :" . $form['accion'] . ". para tabla :" . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// Tabla detalle de solicitud
	case 'predsol':
		switch ($form['accion']) {
			case 'add':
				$sql_ver = "Select prehsol_estado From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_empresa = " . $form['id_empresa'] . " And "
					. "id_cc = " . $form['id_cc'] . " And "
					. "prehsol_numero = " . $form['prehsol_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						$rtn = 1;

						$sql = "INSERT INTO " . $conf->getTbl_predsol() . " SET "
							. "id_prehsol = " . $form['id_prehsol'] . ", "
							. "id_usuario = " . $form['id_usuario'] . ", "
							. "id_empresa = " . $form['id_empresa'] . ", "
							. "id_cc = " . $form['id_cc'] . ", "
							. "predsol_cantidad = " . $form['predsol_cantidad'] . ", "
							. "predsol_unidad = '" . strtoupper($form['predsol_unidad']) . "', "
							. "predsol_cantidad_aut = " . $form['predsol_cantidad'] . ", "
							. "predsol_descripcion = '" . strtoupper($form['predsol_descripcion']) . "', "
							. "predsol_hora = '" . date('H:i:s') . "', "
							. "predsol_fecha = '" . date('Y-m-d') . "', "
							. "predsol_usuario = '" . strtoupper($form['predsol_usuario']) . "'";
					}
				}
				break;
			case 'save':
				$sql_ver = "Select prehsol_estado From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_empresa = " . $form['id_empresa'] . " And "
					. "id_cc = " . $form['id_cc'] . " And "
					. "prehsol_numero = " . $form['prehsol_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						// Limpiamos primero lo que se haya digitado
						$sql_limpia = "Delete From " . $conf->getTbl_predsol()
							. " Where id_prehsol = " . $form['id_prehsol'];
						$db->ejecutar($sql_limpia);
						// Metemos
						//$rtn = 1;
						//$msg = print_r($form, true);
						$moduloData = array();
						for ($i = 1; $i <= 9; $i++) {
							$moduloData[] = "(" . $form['id_prehsol'] . ", "
								. $form['id_usuario'] . ", "
								. $form['id_empresa'] . ", "
								. $form['id_cc'] . ", "
								. (int) $form["c$i"] . ", "
								. "'" . strtoupper($form["u$i"]) . "', "
								. (int) $form["c$i"] . ", "
								. "'" . strtoupper($form["d$i"]) . "', "
								. (double) $form["c1o$i"] . ", "
								. (double) $form["c2o$i"] . ", "
								. (double) $form["c3o$i"] . ", "
								. "'" . date('H:i:s') . "', "
								. "'" . date('Y-m-d') . "', "
								. "'" . strtoupper($form['predsol_usuario']) . "')";

						}
						$sqld = "INSERT INTO " . $conf->getTbl_predsol() . " ( "
							. "id_prehsol, "
							. "id_usuario, "
							. "id_empresa, "
							. "id_cc, "
							. "predsol_cantidad, "
							. "predsol_unidad, "
							. "predsol_cantidad_aut, "
							. "predsol_descripcion, "
							. "predsol_coti1, "
							. "predsol_coti2, "
							. "predsol_coti3, "
							. "predsol_hora, "
							. "predsol_fecha, "
							. "predsol_usuario ) Values " . implode(',', $moduloData);
						try {
							$db->ejecutar($sqld);
						} catch (Exception $e) {
							$rtn = 1;
							$msg = $e->getMessage();
						}
						// Todo OK Ponemos observaciones
						if ($rtn == 0) {
							$sql = "Update " . $conf->getTbl_prehsol()
								. " Set "
								. " prehsol_obs1 = '" . strtoupper($form['ob1']) . "', "
								. " prehsol_obs2 = '" . strtoupper($form['ob2']) . "', "
								. " prehsol_obs3 = '" . strtoupper($form['ob3']) . "'"
								. " Where id_prehsol = " . $form['id_prehsol'];
						}
					}
				}
				break;
			case 'assign':
				list($producto, $linea, $sublinea, $gastit, $gasdet) = split(',', $form['producto']);
				$sql_ver = "Select prehsol_estado From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_prehsol = " . $form['id_prehsol'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					if ($row[0] > 3) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						$sql_ver = "Select prod_codigo From " . $conf->getTbl_predsol()
							. " Where "
							. "prod_codigo = '" . $producto . "' "
							. "And id_prehsol = " . $form['id_prehsol'];
						$run_sql = $db->ejecutar($sql_ver);
						if (mysqli_num_rows($run_sql) <= 0) {
							$sql = "Update " . $conf->getTbl_predsol() . " Set "
								. "sl_sublinea='" . $sublinea . "', "
								. "sl_linea='" . $linea . "', "
								. "predsol_titgas=" . $gastit . ", "
								. "predsol_detgas=" . $gasdet . ", "
								. "prod_codigo = '" . strtoupper($producto) . "', "
								. "predsol_prec_uni = 0, "
								. "predsol_total = 0, "
								. "id_proveedor = 0"
								. " Where id_predsol = " . $form['id_predsol'];
						} else {
							$rtn = 1;
							$msg = $producto . ', ya ha sido asignado a esta solicitud.';
						}
					}
				}
				break;
			case 'assign_prov':
				list($idproveedor, $precio_unit, $empaque) = split('-', $form['proveedor']);
				$sql_ver = "Select prehsol_estado, prehsol_fecha, id_empresa From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_prehsol = " . $form['id_prehsol'];
				break;
			case 'edit':
				$sql_ver = "Select prehsol_estado From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_empresa = " . $form['id_empresa'] . " And "
					. "id_cc = " . $form['id_cc'] . " And "
					. "prehsol_numero = " . $form['prehsol_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						$sql = "UPDATE " . $conf->getTbl_predsol() . " SET "
							. "id_prehsol = " . $form['id_prehsol'] . ", "
							. "id_usuario = " . $form['id_usuario'] . ", "
							. "prod_codigo = '" . $form['prod_codigo'] . "', "
							. "predsol_cantidad = " . $form['predsol_cantidad'] . ", "
							. "predsol_cantidad_aut = " . $form['predsol_cantidad'] . ", "
							. "predsol_prec_uni = " . $form['predsol_prec_uni'] . ", "
							. "predsol_unidad = '" . strtoupper($form['predsol_unidad']) . "', "
							. "predsol_total = " . ($form['predsol_cantidad'] * $form['predsol_prec_uni']) . ", "
							. "predsol_hora = '" . date('H:i:s') . "', "
							. "predsol_fecha = '" . date('Y-m-d') . "', "
							. "predsol_usuario = '" . strtoupper($form['predsol_usuario']) . "' "
							. " Where id_predsol = " . $form['id_predsol'];
					}
				}
				break;
			case 'delete':
				$sql_ver = "Select prehsol_estado From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_empresa = " . $form['id_empresa'] . " And "
					. "id_cc = " . $form['id_cc'] . " And "
					. "prehsol_numero = " . $form['prehsol_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						$currentdir = 'C:/inetpub/wwwroot/sics/';
						$sql_del_attachment = "Select * From " . $conf->getTbl_predsol()
							. " Where id_predsol = " . $form['id_predsol'];
						$run_del_attachment = $db->ejecutar($sql_del_attachment);
						while ($row_del_attachment = mysqli_fetch_array($run_del_attachment)) {
							try {
								$filename1 = $currentdir . $row_del_attachment["predsol_coti1_file"];
								if (is_file($filename1))
									unlink($filename1);
								$filename2 = $currentdir . $row_del_attachment["predsol_coti2_file"];
								if (is_file($filename2))
									unlink($filename2);
								$filename3 = $currentdir . $row_del_attachment["predsol_coti3_file"];
								if (is_file($filename3))
									unlink($filename3);
								//$rtn = 1;
								//$msg = $filename1." - ".$filename2. " - ".$filename3;
							} catch (Exception $e) {
								$rtn = 1;
								$msg = $e->getMessage();
								break;
							}
						}
						// Borrasmo el item
						$sql = "DELETE FROM " . $conf->getTbl_predsol()
							. " Where id_predsol = " . $form['id_predsol'];
					}
				}
				break;
			default:
				$rtn = 1;
				$msg = "Accion : " . $form['accion'] . ". para tabla : " . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// Tabla Detalle Consumo Interno
	case 'ci_det':
		switch ($form['accion']) {
			case 'add':
				$sql_ver = "Select ci_estado From ci_enc"
					. " Where "
					. "id_empresa = " . $form['id_empresa'] . " And "
					. "id_cc = " . $form['id_cc'] . " And "
					. "ci_numero = " . $form['ci_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'El estado ha cambiado, presione F5 para actualizar.';
					} else {
						list($id_prod, $prod_codigo, $prod_descripcion) = explode('~', $form['prod_codigo']);
						$sql = "INSERT INTO ci_det SET "
							. "id_ci = " . $form['id_ci'] . ", "
							. "id_usuario = " . $form['id_usuario'] . ", "
							. "id_empresa = " . $form['id_empresa'] . ", "
							. "id_cc = " . $form['id_cc'] . ", "
							. "id_prod = " . $id_prod . ", "
							. "ci_det_cantidad = " . $form['ci_det_cantidad'] . ", "
							. "prod_codigo = '" . $prod_codigo . "', "
							. "ci_numero = " . $form['ci_numero'] . ", "
							. "prod_descripcion = '" . $prod_descripcion . "', "
							. "ci_det_hora = '" . date('H:i:s') . "', "
							. "ci_det_fecha = '" . date('Y-m-d') . "', "
							. "ci_det_usuario = '" . strtoupper($form['ci_det_usuario']) . "'";
					}
				}
				break;
			case 'edit':
				$sql_ver = "Select prehsol_estado From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_empresa = " . $form['id_empresa'] . " And "
					. "id_cc = " . $form['id_cc'] . " And "
					. "prehsol_numero = " . $form['prehsol_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						$sql = "UPDATE " . $conf->getTbl_predsol() . " SET "
							. "id_prehsol = " . $form['id_prehsol'] . ", "
							. "id_usuario = " . $form['id_usuario'] . ", "
							. "prod_codigo = '" . $form['prod_codigo'] . "', "
							. "predsol_cantidad = " . $form['predsol_cantidad'] . ", "
							. "predsol_cantidad_aut = " . $form['predsol_cantidad'] . ", "
							. "predsol_prec_uni = " . $form['predsol_prec_uni'] . ", "
							. "predsol_unidad = '" . strtoupper($form['predsol_unidad']) . "', "
							. "predsol_total = " . ($form['predsol_cantidad'] * $form['predsol_prec_uni']) . ", "
							. "predsol_hora = '" . date('H:i:s') . "', "
							. "predsol_fecha = '" . date('Y-m-d') . "', "
							. "predsol_usuario = '" . strtoupper($form['predsol_usuario']) . "' "
							. " Where id_predsol = " . $form['id_predsol'];
					}
				}
				break;
			case 'delete':
				$sql_ver = "Select ci_estado From ci_enc"
					. " Where "
					. "id_empresa = " . $form['id_empresa'] . " And "
					. "id_cc = " . $form['id_cc'] . " And "
					. "ci_numero = " . $form['ci_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La solicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'El estado ha cambiado, presione F5 para actualizar.';
					} else {
						// Borrasmo el item
						$sql = "DELETE FROM ci_det"
							. " Where id_ci_det = " . $form['id_ci_det'];
					}
				}
				break;
			default:
				$rtn = 1;
				$msg = "Accion : " . $form['accion'] . ". para tabla : " . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// Tabla de encabezado de solicitud
	case 'prehsol':
		switch ($form['accion']) {
			case 'autoriza':
				$sql_ver = "Select prehsol_estado, id_usuario,id_categoria From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_empresa = " . $form['empresa'] . " And "
					. "id_cc = " . $form['centrocosto'] . " And "
					. "prehsol_numero = " . $form['prehsol_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					$id_usuario = $row[1];
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						// Revisemos el detalle de la solicitud
						$sql_det = "Select id_predsol From " . $conf->getTbl_predsol()
							. " Where id_prehsol = " . $form['id_prehsol'];
						$run_det = $db->ejecutar($sql_det);
						if (mysqli_num_rows($run_det) <= 0) {
							$rtn = 1;
							$msg = 'la solicitud esta vacia, verifique pulse F5, para actualizar';
						} else {
							try {
								$sql_solc = "Select cc_solc From " . $conf->getTbl_cecosto()
									. " Where id_empresa = " . $form['empresa']
									. " And id_cc = " . $form['centrocosto'];
								$run_solc = $db->ejecutar($sql_solc);
								/*
								 * Si el centro de costo existe
								 */
								if (mysqli_num_rows($run_solc) > 0) {
									// Obtenemos el numero de solicitud
									$row = mysqli_fetch_array($run_solc);
									$result = $row[0];

									// Directo a compras
									$esta_solc = 4;
									// Lleva una categoria
									if ($form['categoria'] > 0) {
										// Buscamos si tiene autorizacion por categoria
										$sql_estado_cat = "Select id_auto_categoria From "
											. $conf->getTbl_gestion_categorias()
											. " Where id_categoria = " . $form['categoria'];
										$run_estado_cat = $db->ejecutar($sql_estado_cat);
										if (mysqli_num_rows($run_estado_cat) > 0) {
											$esta_solc = 2;
										}
									}
									if ($esta_solc == 4) {
										/*
										 * si no se define una autorizacion por categoria
										 * verificamos si tiene definido un gestor  
										 */
										$sql_estado = "Select id_cc From " . $conf->getTbl_gestores()
											. " Where id_cc = " . $form['centrocosto'];
										$run_estado = $db->ejecutar($sql_estado);
										if (mysqli_num_rows($run_estado) > 0) {
											$esta_solc = 3;
										}
									}
									$gestion = "";
									if ($esta_solc == 4) {
										$gestion = "prehsol_nuevo_gestion = 0,";
										$gestion .= "prehsol_nuevo_gestionado = 1,";
									}

									// Preparamos ejecucion de autorizacion
									/*
									$sql = "Update " . $conf->getTbl_prehsol()." Set "
											."prehsol_estado = ".$esta_solc.", "
											."prehsol_numero_sol = ".$result.", "
											."id_categoria = ".$form['categoria'].","
											."prehsol_ingreso_compra = '".date('Y-m-d H:i:s')."', "
											."prehsol_aprobacion = '".mysqli_real_escape_string($form['aprueba_sol'])."',"
											."prehsol_aprobacion_usuario = '".$_SESSION['u']."'"
											." Where id_prehsol = ".$form['id_prehsol'];

									*/
									/*
									Cambio: solicitado por Ing. Villalta
									Fecha:  19/02/2020 17:00
									Descr: Para hacer unico el correlativo de pre-solicitud/solicitud,
										   el numero de solicitud es igual al de pre-solicitud

									*/

									$esta_solc = 5; //Se envia a dueño de categoria

									$sql = "Update " . $conf->getTbl_prehsol() . " Set "
										. "prehsol_estado = " . $esta_solc . ", "
										. "prehsol_numero_sol = prehsol_numero, "
										//."id_categoria = ".$form['categoria'].","
										. "prehsol_ingreso_compra = '" . date('Y-m-d H:i:s') . "', "
										. "prehsol_aprobacion = '" . mysqli_real_escape_string($form['aprueba_sol']) . "',"
										. "prehsol_aprobacion_usuario = '" . $_SESSION['u'] . "'"
										. " Where id_prehsol = " . $form['id_prehsol'];

									// Actualizamos el detalle
									$sql_D = "Update " . $conf->getTbl_predsol() . " Set "
										. "predsol_estado = " . $esta_solc
										. " Where id_prehsol = " . $form['id_prehsol'];
									$db->ejecutar($sql_D);
									// Pone las estadisticas
									$sql_E = "INSERT INTO " . $conf->getTbl_estadistica()
										. " (id_estadisticas, id_usuario, mes, anio, cantidad, presol, solicitudes, prereq, requisiciones) "
										. "VALUES "
										. "(0, " . $id_usuario . ", " . date('m') . ", " . date('Y') . ", 1, 0, 1, 0, 0 ) "
										. "ON DUPLICATE KEY UPDATE solicitudes = solicitudes + 1, cantidad = cantidad + 1";
									$db->ejecutar($sql_E);
									// Ponemos el estado de creado
									$sql_st = "Insert Into " . $conf->getTbl_prehsol_stat() . " Set "
										. "id_prehsol = " . $form['id_prehsol'] . ", "
										. "prehsol_stat = " . $esta_solc . ", "
										. "prehsol_stat_desc = '" . $conf->getEstadoSC($esta_solc) . "', "
										. "prehsol_stat_fecha = '" . date("Y-m-d") . "', "
										. "prehsol_stat_hora = '" . date("H:i:s") . "', "
										. "prehsol_stat_usuario = '" . $_SESSION['u'] . "'";
									$db->ejecutar($sql_st);
									$sql_u = "Update " . $conf->getTbl_cecosto() . " Set "
										. "cc_solc = " . ($result + 1) . " "
										. "Where id_empresa = " . $form['empresa']
										. " And id_cc = " . $form['centrocosto'];
									$db->ejecutar($sql_u);

									//Aprobación automatica de categoria

									$sql_ver = "Select prehsol_estado, id_usuario,id_categoria From " . $conf->getTbl_prehsol()
										. " Where "
										. "id_empresa = " . $form['empresa'] . " And "
										. "id_cc = " . $form['centrocosto'] . " And "
										. "prehsol_numero = " . $form['prehsol_numero'];
									$run_sql = $db->ejecutar($sql_ver);
									if (mysqli_num_rows($run_sql) <= 0) {
										$row = mysqli_fetch_array($run_sql);
										$id_usuario = $row[1];
									}

								} else {
									$rtn = 1;
									$msg = 'parece que el centro de costo no ha sido definido';
								}
							} catch (Exception $e) {
								$rtn = 1;
								$msg = $e->getMessage();
							}
						}
					}
				}
				break;
			case 'autorizaSend':
				$sql_ver = "Select prehsol_estado, id_usuario From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_empresa = " . $form['empresa'] . " And "
					. "id_cc = " . $form['centrocosto'] . " And "
					. "prehsol_numero = " . $form['prehsol_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					$id_usuario = $row[1];
					if ($row[0] > 5) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						// Revisemos el detalle de la solicitud
						$sql_det = "Select id_predsol From " . $conf->getTbl_predsol()
							. " Where id_prehsol = " . $form['id_prehsol'];
						$run_det = $db->ejecutar($sql_det);
						if (mysqli_num_rows($run_det) <= 0) {
							$rtn = 1;
							$msg = 'la solicitud esta vacia, verifique pulse F5, para actualizar';
						} else {
							try {


								// Directo a compras
								$esta_solc = 4;
								// Lleva una categoria
								if ($form['categoria'] > 0) {
									// Buscamos si tiene autorizacion por categoria
									$sql_estado_cat = "Select id_auto_categoria From "
										. $conf->getTbl_gestion_categorias()
										. " Where id_categoria = " . $form['categoria'];
									$run_estado_cat = $db->ejecutar($sql_estado_cat);
									if (mysqli_num_rows($run_estado_cat) > 0) {
										$esta_solc = 4;
									}
								}
								if ($esta_solc == 4) {
									/*
									 * si no se define una autorizacion por categoria
									 * verificamos si tiene definido un gestor
									 */
									$sql_estado = "Select id_cc From " . $conf->getTbl_gestores()
										. " Where id_cc = " . $form['centrocosto'];
									$run_estado = $db->ejecutar($sql_estado);
									if (mysqli_num_rows($run_estado) > 0) {
										$esta_solc = 3;
									}
								}

								$esta_solc = 1; //Se envía a usuario autorizador cc

								$nuevo_gestionado = 0;
								$nuevo_gestion = 1;
								if ($form['categoria'] == 0) {
									$nuevo_gestion = 0;
									$nuevo_gestionado = 1;
								}
								// Preparamos ejecucion de autorizacion
								$sql = "Update " . $conf->getTbl_prehsol() . " Set "
									. "prehsol_nuevo_gestion = " . $nuevo_gestion . ", "
									. "prehsol_nuevo_gestionado = " . $nuevo_gestionado . ", "
									. "prehsol_estado = " . $esta_solc . ", "
									. "obs_cate = '" . trim($form['obs_cate']) . "' "
									. " Where id_prehsol = " . $form['id_prehsol'];

								if (!empty($form['input_monto'])) {
									// Preparamos ejecucion de autorizacion
									$sql = "Update " . $conf->getTbl_prehsol() . " Set "
										. "prehsol_nuevo_gestion = " . $nuevo_gestion . ", "
										. "prehsol_nuevo_gestionado = " . $nuevo_gestionado . ", "
										. "prehsol_estado = " . $esta_solc . ", "
										. "obs_cate = '" . trim($form['obs_cate']) . "',"
										. "prehsol_monto = '" . trim($form['input_monto']) . "',"
										. "prehsol_metodopago = '" . trim($form['input_metodo']) . "',"
										. "prehsol_proveedor = '" . trim($form['input_proveedor']) . "',"
										. "moneda = '" . trim($form['input_moneda']) . "' "
										. " Where id_prehsol = " . $form['id_prehsol'];
								}

								// Actualizamos el detalle
								$sql_D = "Update " . $conf->getTbl_predsol() . " Set "
									. "predsol_estado = " . $esta_solc
									. " Where id_prehsol = " . $form['id_prehsol'];
								$db->ejecutar($sql_D);

							} catch (Exception $e) {
								$rtn = 1;
								$msg = $e->getMessage();
							}
						}
					}
				}
				break;
			case 'negar':
				$sql_ver = "Select prehsol_estado, id_usuario From " . $conf->getTbl_prehsol()
					. " Where "
					. "id_empresa = " . $form['empresa'] . " And "
					. "id_cc = " . $form['centrocosto'] . " And "
					. "prehsol_numero = " . $form['prehsol_numero'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La presolicitud ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					$id_usuario = $row[1];
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						// Revisemos el detalle de la solicitud
						$sql_det = "Select id_predsol From " . $conf->getTbl_predsol()
							. " Where "
							. "id_empresa = " . $form['empresa'] . " And "
							. "id_cc = " . $form['centrocosto'] . " And "
							. "id_prehsol = " . $form['id_prehsol'];
						$run_det = $db->ejecutar($sql_det);
						if (mysqli_num_rows($run_det) <= 0) {
							$rtn = 1;
							$msg = 'la solicitud esta vacia, verifique pulse F5, para actualizar';
						} else {
							try {
								$sql_solc = "Select cc_solc From " . $conf->getTbl_cecosto()
									. " Where id_empresa = " . $form['empresa']
									. " And id_cc = " . $form['centrocosto'];
								$run_solc = $db->ejecutar($sql_solc);
								/*
								 * Si el centro de costo existe
								 */
								if (mysqli_num_rows($run_solc) > 0) {
									// Preparamos ejecucion de negacion
									$sql = "Update " . $conf->getTbl_prehsol() . " Set "
										. "prehsol_estado = 8"
										. " Where "
										. "id_empresa = " . $form['empresa'] . " And "
										. "id_cc = " . $form['centrocosto'] . " And "
										. "prehsol_numero = " . $form['prehsol_numero'];
									// Ponemos el estado de creado
									$sql_st = "Insert Into " . $conf->getTbl_prehsol_stat() . " Set "
										. "id_prehsol = " . $form['id_prehsol'] . ", "
										. "prehsol_stat = 8, "
										. "prehsol_stat_desc = '" . $conf->getEstado('8') . "', "
										. "prehsol_stat_fecha = '" . date("Y-m-d") . "', "
										. "prehsol_stat_hora = '" . date("H:i:s") . "', "
										. "prehsol_stat_usuario = '" . $_SESSION['u'] . "'";
									$db->ejecutar($sql_st);
								} else {
									$rtn = 1;
									$msg = 'parece que el centro de costo no ha sido definido';
								}
							} catch (Exception $e) {
								$rtn = 1;
								$msg = $e->getMessage();
							}
						}
					}
				}
				break;
			case 'colecta':
				$formData = array();
				$fechaForm = date('Y-m-d');
				$horaForm = date('H:i:s');
				$estado = $conf->getEstado('3');
				$usuario = $_SESSION['u'];
				foreach ($form['numero'] as $numero) {
					list($numero, $id_cc, $id_preh) = explode('~', $numero);
					// Preparamos ejecucion de negacion
					$sqlStat = "Update " . $conf->getTbl_prehsol() . " Set "
						. "prehsol_estado = 3, "
						. "prehsol_fecha_col = '" . $fechaForm . "', "
						. "prehsol_hora_col = '" . $horaForm . "', "
						. "prehsol_usuario_col = '" . $usuario . "'"
						. " Where "
						. "id_empresa = " . $form['empresa'] . " And "
						. "id_cc = " . $id_cc . " And "
						. "prehsol_numero_sol = " . $numero;
					$db->ejecutar($sqlStat);
					$sqlStatDet = "Update " . $conf->getTbl_predsol() . " Set "
						. "predsol_estado = 3,"
						. "predsol_fecha_col = '" . $fechaForm . "', "
						. "predsol_hora_col = '" . $horaForm . "', "
						. "predsol_usuario_col = '" . $usuario . "'"
						. " Where "
						. "id_prehsol = " . $id_preh;
					$db->ejecutar($sqlStatDet);
					$formData[] = "( "
						. $id_preh . ", "
						. "3, '"
						. $estado . "', '"
						. $fechaForm . "', '"
						. $horaForm . "', '"
						. strtoupper($usuario) . "' )";
				}
				$sql = "INSERT INTO " . $conf->getTbl_prehsol_stat() . " ( "
					. "id_prehsol , "
					. "prehsol_stat, "
					. "prehsol_stat_desc, "
					. "prehsol_stat_fecha, "
					. "prehsol_stat_hora, "
					. "prehsol_stat_usuario ) VALUES " . implode(',', $formData);
				break;
			default:
				$rtn = 1;
				$msg = "Accion : " . $form['accion'] . ". para tabla : " . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	case 'predreq':
		break;
	// Tabla de encabezado de requisicion
	case 'prehreq':
		switch ($form['accion']) {
			case 'autoriza':
				$sql_ver = "Select prehreq_estado, id_usuario From " . $conf->getTbl_prehreq()
					. " Where "
					. "id_empresa = " . $form['empresa'] . " And "
					. "id_cc = " . $form['centrocosto'] . " And "
					. "prehreq_numero = " . $form['prehreq_numero'];
				//." Where id_prehreq = ".$form['id_prehreq'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La requisicion ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					$id_usuario = $row[1];
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						// Revisemos el detalle de la solicitud
						$sql_det = "Select id_predreq From " . $conf->getTbl_predreq()
							. " Where id_prehreq = " . $form['id_prehreq'];
						$run_det = $db->ejecutar($sql_det);
						if (mysqli_num_rows($run_det) <= 0) {
							$rtn = 1;
							$msg = 'la requisicion esta vacia, verifique pulse F5, para actualizar';
						} else {
							try {
								$sql_solc = "Select cc_req From " . $conf->getTbl_cecosto()
									. " Where id_empresa = " . $form['empresa']
									. " And id_cc = " . $form['centrocosto'];
								$run_solc = $db->ejecutar($sql_solc);
								/*
								 * Si el centro de costo existe
								 */
								if (mysqli_num_rows($run_solc) > 0) {
									// Obtenemos el numero de solicitud
									$row = mysqli_fetch_array($run_solc);
									$result = $row[0];
									// Preparamos ejecucion de autorizacion
									$sql = "Update " . $conf->getTbl_prehreq() . " Set "
										. "prehreq_estado = 2, "
										. "prehreq_numero_req = " . $result
										. " Where id_prehreq = " . $form['id_prehreq'];
									// Pone las estadisticas
									$sql_E = "INSERT INTO " . $conf->getTbl_estadistica()
										. " (id_estadisticas, id_usuario, mes, anio, cantidad, presol, solicitudes, prereq, requisiciones) "
										. "VALUES "
										. "(0, " . $id_usuario . ", " . date('m') . ", " . date('Y') . ", 1, 0, 0, 0, 1 ) "
										. "ON DUPLICATE KEY UPDATE requisiciones = requisiciones + 1, cantidad = cantidad + 1";
									$db->ejecutar($sql_E);
									// Ponemos el estado de creado
									$sql_st = "Insert Into " . $conf->getTbl_prehreq_stat() . " Set "
										. "id_prehreq = " . $form['id_prehreq'] . ", "
										. "prehreq_stat = 2, "
										. "prehreq_stat_desc = '" . $conf->getEstado('2') . "', "
										. "prehreq_stat_fecha = '" . date("Y-m-d") . "', "
										. "prehreq_stat_hora = '" . date("H:i:s") . "', "
										. "prehreq_stat_usuario = '" . $_SESSION['u'] . "'";
									$db->ejecutar($sql_st);
									// Ponemos nuevo estado en detalle
									$sql_st_det = "Update " . $conf->getTbl_predreq() . " Set "
										. "predreq_estado = 2 "
										. " Where id_prehreq = " . $form['id_prehreq'];
									$db->ejecutar($sql_st_det);
									$sql_u = "Update " . $conf->getTbl_cecosto() . " Set "
										. "cc_req = " . ($result + 1) . " "
										. "Where id_empresa = " . $form['empresa']
										. " And id_cc = " . $form['centrocosto'];
									$db->ejecutar($sql_u);
								} else {
									$rtn = 1;
									$msg = 'parece que el centro de costo no ha sido definido';
								}
							} catch (Exception $e) {
								$rtn = 1;
								$msg = $e->getMessage();
							}
						}
					}
				}
				break;
			case 'negar':
				$sql_ver = "Select prehreq_estado, id_usuario From " . $conf->getTbl_prehreq()
					. " Where "
					. "id_empresa = " . $form['empresa'] . " And "
					. "id_cc = " . $form['centrocosto'] . " And "
					. "prehreq_numero = " . $form['prehreq_numero'];
				//." Where id_prehreq = ".$form['id_prehreq'];
				$run_sql = $db->ejecutar($sql_ver);
				if (mysqli_num_rows($run_sql) <= 0) {
					$rtn = 1;
					$msg = 'La requisicion ha dejado de existir';
				} else {
					$row = mysqli_fetch_array($run_sql);
					$id_usuario = $row[1];
					if ($row[0] > 1) {
						$rtn = 1;
						$msg = 'su estado ha sido <b>' . $conf->getEstado($row[0]) . '</b>, presione F5 para actualizar.';
					} else {
						// Revisemos el detalle de la solicitud
						$sql_det = "Select id_predreq From " . $conf->getTbl_predreq()
							. " Where id_prehreq = " . $form['id_prehreq'];
						$run_det = $db->ejecutar($sql_det);
						if (mysqli_num_rows($run_det) <= 0) {
							$rtn = 1;
							$msg = 'la requisicion esta vacia, verifique pulse F5, para actualizar';
						} else {
							try {
								$sql_solc = "Select cc_solc From " . $conf->getTbl_cecosto()
									. " Where id_empresa = " . $form['empresa']
									. " And id_cc = " . $form['centrocosto'];
								$run_solc = $db->ejecutar($sql_solc);
								/*
								 * Si el centro de costo existe
								 */
								if (mysqli_num_rows($run_solc) > 0) {
									// Preparamos ejecucion de negacion
									$sql = "Update " . $conf->getTbl_prehreq() . " Set "
										. "prehreq_estado = 8"
										. " Where id_prehreq = " . $form['id_prehreq'];
									// Ponemos el estado de creado
									$sql_st = "Insert Into " . $conf->getTbl_prehreq_stat() . " Set "
										. "id_prehreq = " . $form['id_prehreq'] . ", "
										. "prehreq_stat = 8, "
										. "prehreq_stat_desc = '" . $conf->getEstado('8') . "', "
										. "prehreq_stat_fecha = '" . date("Y-m-d") . "', "
										. "prehreq_stat_hora = '" . date("H:i:s") . "', "
										. "prehreq_stat_usuario = '" . $_SESSION['u'] . "'";
									$db->ejecutar($sql_st);
								} else {
									$rtn = 1;
									$msg = 'parece que el centro de costo no ha sido definido';
								}
							} catch (Exception $e) {
								$rtn = 1;
								$msg = $e->getMessage();
							}
						}
					}
				}
				break;
			case 'colecta':
				$formData = array();
				$fechaForm = date('Y-m-d');
				$horaForm = date('H:i:s');
				$estado = $conf->getEstado('3');
				$usuario = $_SESSION['u'];
				foreach ($form['numero'] as $numero) {
					list($numero, $id_cc, $id_preh) = explode('~', $numero);
					// Preparamos ejecucion de negacion
					$sqlStat = "Update " . $conf->getTbl_prehreq() . " Set "
						. "prehreq_estado = 3, "
						. "prehreq_fecha_col = '" . $fechaForm . "', "
						. "prehreq_hora_col = '" . $horaForm . "', "
						. "prehreq_usuario_col = '" . $usuario . "'"
						. " Where "
						. "id_empresa = " . $form['empresa'] . " And "
						. "id_cc = " . $id_cc . " And "
						. "prehreq_numero_req = " . $numero;
					$db->ejecutar($sqlStat);
					$sqlStatDet = "Update " . $conf->getTbl_predreq() . " Set "
						. "predreq_estado = 3,"
						. "predreq_fecha_col = '" . $fechaForm . "', "
						. "predreq_hora_col = '" . $horaForm . "', "
						. "predreq_usuario_col = '" . $usuario . "'"
						. " Where "
						. "id_prehreq = " . $id_preh;
					$db->ejecutar($sqlStatDet);
					$formData[0] = "( "
						. $id_preh . ", "
						. "3, '"
						. $estado . "', '"
						. $fechaForm . "', '"
						. $horaForm . "', '"
						. strtoupper($usuario) . "' )";
				}
				$sql = "INSERT INTO " . $conf->getTbl_prehreq_stat() . " ( "
					. "id_prehreq , "
					. "prehreq_stat, "
					. "prehreq_stat_desc, "
					. "prehreq_stat_fecha, "
					. "prehreq_stat_hora, "
					. "prehreq_stat_usuario ) VALUES " . implode(',', $formData);
				break;
			default:
				$rtn = 1;
				$msg = "Accion : " . $form['accion'] . ". para tabla : " . $form['tabla'] . ", no definida.";
				break;
		}
		break;
	// La tabla no se maneja, no existe??
	default:
		$rtn = 1;
		$msg = "Ha ocurrido un error, favor informe a Sistemas, CODIGO: OUT|TBL[" . $form['tabla'] . "]";
		break;
}
if ($rtn == 1) {
	echo $msg;
} else {
	try {
		//echo $sql;
		$db->ejecutar($sql);
		echo $db->_last_insert_id;
	} catch (Exception $e) {
		echo $sql . "<br/>";
		echo $e->getMessage();//.$sql;
	}
}
/*
 * foreach ($_POST['form'] as $k=>$v){ echo $k."=>".$v."<br>"; }
 */
?>