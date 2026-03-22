<?php
/*
 * return @array
 * listado de empresas a las que tiene permiso el usuario
 */
function listarEmpUsuario(){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	$sql = "Select a.id_empresa, b.emp_nombre, b.id_empresa_oc From "
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
//			echo $sql;
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

function listarCcUsuario2(){
	if (isset($_POST['id_empresa']) && $_POST['id_empresa'] != "") {
		try {
			$idEmpresa = $_POST['id_empresa'];
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sql = 	"select ". 
					"c.id_cc, ".
					"c.cc_codigo, ".
					"c.cc_descripcion ". 
					"from cecosto c ".
					"inner join ( ".
					"	( ".
					"	select ". 
					"	cc.id_cc ".
					"	from acc_emp_cc ac ".
					"	inner join cecosto cc ".
					"	on ac.id_empresa = cc.id_empresa and ac.id_cc = cc.id_cc ". 
					"	where ac.id_usuario = ".$_SESSION['i']." and cc.id_empresa = ".$idEmpresa." ".
					"	) ". 
					"	union ".
					"	( ".
					"	select  ".
					"	distinct ". 
					"	s.id_cc  ".
					"	from gestion_categorias c ".
					"	inner join prehsol s ".
					"	on s.id_categoria = c.id_categoria ".
					"	where c.id_usuario = ".$_SESSION['i']." and s.id_empresa = ".$idEmpresa." ".
					"	) ".
					") c1 ".
					"on c.id_cc=c1.id_cc and c.id_empresa=".$idEmpresa." ";
//			echo $sql;
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



function listarCcUsuarioCat(){
	if (isset($_POST['id_empresa']) && $_POST['id_empresa'] != "") {
		try {
			$id_empresa = $_POST['id_empresa'];
			$id_usuario = $_SESSION['i'];
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sql = "select 
					c.id_cc,
					c.cc_codigo,
					c.cc_descripcion 
					from cecosto c
					inner join (
						(
						select 
						cc.id_cc
						from acc_emp_cc ac
						inner join cecosto cc
						on ac.id_empresa = cc.id_empresa and ac.id_cc = cc.id_cc 
						where ac.id_usuario = $id_usuario and cc.id_empresa = $id_empresa
						) 
						union 
						(
						select 
						distinct 
						s.id_cc 
						from gestion_categorias c
						inner join prehsol s
						on s.id_categoria = c.id_categoria
						where c.id_usuario = $id_usuario and s.id_empresa = $id_empresa
						)
					) c1
					on c.id_cc=c1.id_cc and c.id_empresa=$id_empresa
					order by c.cc_descripcion
					";
//			echo $sql;
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
function creaPreReq($form){
	$result = 0;
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select cc_prereq From ".$conf->getTbl_cecosto()
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
				."cc_prereq = ".($result+1)." "
				."Where id_empresa = ".$form['empresa']
				." And id_cc = ".$form['centrocosto'];
			$db->ejecutar($sql_u);
			// Pone las estadisticas
			$sql_E = "INSERT INTO ".$conf->getTbl_estadistica()
				." (id_estadisticas, id_usuario, mes, anio, cantidad, presol, solicitudes, prereq, requisiciones) "
				."VALUES "
				."(0, ".$_SESSION['i'].", ".date('m').", ".date('Y').", 1, 0, 0, 1, 0 ) "
				."ON DUPLICATE KEY UPDATE prereq = prereq + 1, cantidad = cantidad + 1";
			$db->ejecutar($sql_E);
			// Metemos la pre-solicitud
			$sql_i = "Insert Into ".$conf->getTbl_prehreq()." Set "
				."id_empresa = ".$form['empresa'].", "
				."id_cc = ".$form['centrocosto'].", "
				."id_usuario = ".$_SESSION['i'].", "
				."prehreq_numero = ".$result.", "
				."prehreq_fecha = '".$fecha."', "
				."prehreq_hora = '".$hora."', "
				."prehreq_estado = 0, "
				."prehreq_usuario = '".$_SESSION['u']."'";
			$db->ejecutar($sql_i);
			// Ponemos el estado de creado
			$sql_st = "Insert Into ".$conf->getTbl_prehreq_stat()." Set "
				."id_prehreq = ".$db->_last_insert_id.", "
				."prehreq_stat = 0, "
				."prehreq_stat_desc = '".$conf->getEstado('0')."', "
				."prehreq_stat_fecha = '".$fecha."', "
				."prehreq_stat_hora = '".$hora."', "
				."prehreq_stat_usuario = '".$_SESSION['u']."'";
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
function infoPreHReq($idprehreq, $idcc, $idemp){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From "
			.$conf->getTbl_prehreq()." a"
			." Join ".$conf->getTbl_empresa()." b On"
			." b.id_empresa = a.id_empresa"
			." Join ".$conf->getTbl_cecosto()." c On"
			." c.id_cc = a.id_cc"
			." Where a.prehreq_numero =".$idprehreq." "
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
function detPreDReq($idprehreq, $idcc, $idemp){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From "
				.$conf->getTbl_predreq()
				." Where id_prehreq =".$idprehreq." "
				."And id_cc = ".$idcc." "
				."And id_empresa = ".$idemp;
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
function listarReqs(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select * From "
				.$conf->getTbl_prehreq()." a"
				." Join ".$conf->getTbl_cecosto()." b"
				." On "
				."a.id_cc = b.id_cc And "
				."a.id_empresa = b.id_empresa"
				." Where id_usuario = ".$_SESSION['i']
				." And prehreq_estado < 8"
				." Order by prehreq_fecha DESC";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				// Buscamos estatus
				$sql_stat = "Select * From ".$conf->getTbl_prehreq_stat()
					." Where id_prehreq = ".$row['id_prehreq'];
				$run_stat = $db->ejecutar($sql_stat);
				$estados = "<b>trazabilidad:</b><br>";
				while ($row_stat = mysqli_fetch_array($run_stat)) {
					$estados .= '<b>'.$row_stat['prehreq_stat_desc'].'</b> <u>'.$row_stat['prehreq_stat_usuario'].'</u> <i>'.$row_stat['prehreq_stat_fecha'].' '.$row_stat['prehreq_stat_hora']."</i><br>";
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
function listarReqsAuth($idemp){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select b.cc_descripcion, b.cc_codigo, c.* From "
			.$conf->getTbl_acc_emp_cc()." a "
			."Join ".$conf->getTbl_cecosto()." b On "
			."a.id_cc = b.id_cc "
			."Join ".$conf->getTbl_prehreq()." c On "
			."a.id_empresa = c.id_empresa And "
			."b.id_cc = c.id_cc "
			."Where a.id_usuario = ".$_SESSION['i']." And "
			."c.id_empresa = ".$idemp
			." And c.prehreq_estado = 1";
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
 * Retorna listado de productos para requisiones
 */
function listaProdsReq(){
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$campogasto = "b.id_tabgas".$_GET['es'];
		$sql = "Select a.*, b.sl_descripcion, g.gas_tit_codigo, g.gas_det_codigo From "
			.$conf->getTbl_producto()." a "
			." Join ".$conf->getTbl_sublinea()." b "
			." On a.sl_linea = b.sl_linea And a.sl_sublinea = b.sl_sublinea"
			." Left Join ".$conf->getTbl_tagasto()." g "
			." On g.id_tagasto = ".$campogasto
			." Where a.prod_req = 1 ORDER BY b.sl_linea, b.sl_sublinea";
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$precio = 0;
				$proveedor = 0;
				// Barremos los 5 puestos de precio, 1 al 5
				for ($i = 1; $i <= 5; $i++) {
					$campoProv = "prod_prov0".$i;
					$campoProvPre = "prod_prov_pre0".$i;
					// primero verificamos que tenga definido un proveedor y precio para este
					if ($row["$campoProv"] > 0 && $row["$campoProvPre"] > 0) {
						// Debemos verificar si esta en vigencia el precio
						$sql_vigencia = "Select lis_fin_vigencia From ".$conf->getTbl_lista()
						." Where id_proveedor = ".$row["$campoProv"]
						." And prod_codigo = '".$row['prod_codigo']."'";
						$run_vigencia = $db->ejecutar($sql_vigencia);
						$row_vigencia = mysqli_fetch_array($run_vigencia);
						// Hoy es menor a fecha de vigencia
						if (date('Y-m-d') <= $row_vigencia[0]) {
							$precio = $row["$campoProvPre"];
							$proveedor = $row["$campoProv"];
							break;
						}
					}
				}
				$row['precio'] = $precio;
				$row['proveedor'] = $proveedor;
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
 * Lista todas las requisiciones listas para colectar
 */
function listasColectar($id_empresa, $inicio, $fin) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select e.id_prehreq, e.id_empresa, e.id_cc, e.prehreq_fecha, e.prehreq_numero_req, c.cc_codigo, c.cc_descripcion From "
				.$conf->getTbl_prehreq()." e"
				." Join ".$conf->getTbl_cecosto()." c"
				." On c.id_empresa = e.id_empresa And c.id_cc = e.id_cc"
				." Where e.prehreq_estado = 2"
				." And e.id_empresa=".$id_empresa
				//." And e.prehreq_fecha Between '".$inicio."' And '".$fin."'"
				." Order By e.id_cc, e.prehreq_numero_req";
		//echo $sql;
		$run = $db->ejecutar ( $sql );
		if (mysqli_num_rows($run) > 0) {
			while ( $row = mysqli_fetch_array ($run) ) {
				$sqlDet = "Select * From ".$conf->getTbl_predreq()
					." Where id_prehreq = ".$row['id_prehreq'];
				$runDet = $db->ejecutar($sqlDet);
				unset($rowD);
				while($rowDet = mysqli_fetch_array($runDet)) {
					$rowD[] = $rowDet;
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
 * Ejecuta consulta para la paginacion
 */
function Consulta($page, $form){
	try {
		$rowsperpage = 10;
		$inicio = ($rowsperpage*$page)-$rowsperpage;
		$limite = $rowsperpage;
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$form['numero'] = (int) $form['numero'];
		$numero = "";
		$cc = "";
		if($form['numero'] > 0) {
			$numero = " And prehreq_numero_req = ".$form['numero'];
		}
		if($form['centrocosto'] != '00') {
			$cc = " And a.id_cc = ".$form['centrocosto'];
		}
		$sql = "Select a.*, b.cc_descripcion From ".$conf->getTbl_prehreq()." a"
			." join ".$conf->getTbl_cecosto()." b"
			." On b.id_empresa = a.id_empresa And"
			." b.id_cc = a.id_cc"
			." Where "
			."a.id_empresa = ".$form['empresa']
			.$cc
			.$numero
			." And a.prehreq_numero_req > 0"
			." Order By prehreq_fecha Desc";
		$run = $db->ejecutar($sql);
		$registros = mysqli_num_rows($run);
		/*
		 *
		 */
		$sql = "Select a.*, b.cc_descripcion From ".$conf->getTbl_prehreq()." a"
			." join ".$conf->getTbl_cecosto()." b"
			." On b.id_empresa = a.id_empresa And"
			." b.id_cc = a.id_cc"
			." Where "
			."a.id_empresa = ".$form['empresa']
			.$cc
			.$numero
			." And a.prehreq_numero_req > 0"
			." Order By prehreq_fecha Desc"
			." Limit ".$inicio.",".$limite;
		$run = $db->ejecutar($sql);
		$registrosFilter = mysqli_num_rows($run);
		$array['registros'] = $registros;
		if ($registrosFilter > 0) {
			while ( $row = mysqli_fetch_array ( $run ) ) {

				$row['nestado'] = $conf->getEstado($row['prehreq_estado']);
									
				$sql_estados = "Select * From ".$conf->getTbl_prehreq_stat()
					." Where id_prehreq = ".$row['id_prehreq']
					." Order by prehreq_stat";
				$run_estados = $db->ejecutar($sql_estados);
				
				$rowE = array();
				while ($row_estados = mysqli_fetch_array($run_estados)) {
					$rowE[] = $row_estados;
				}
				$row['estados'] = $rowE;
				
				$sqlDet = "Select * From ".$conf->getTbl_predreq()
				." Where id_prehreq = ".$row['id_prehreq'];
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
 * Lista todas
*/
function listasOC($id_empresa) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select d.id_proveedor, d.id_predreq, p.prov_nombre, d.predreq_fecha_col "
				."From "
				.$conf->getTbl_predreq()." d"
				." Join "
				.$conf->getTbl_proveedor()." p"
				." On "
				."p.id_proveedor = d.id_proveedor "
				."Where "
				."d.predreq_estado = 3 and "
				."d.predreq_total > 0 and "
				."d.id_empresa = ".$id_empresa
				." and isNull(d.predreq_numero_oc)"
				." Group by "
				."d.predreq_fecha_col, d.id_proveedor";
		try {
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ($run) ) {
					$sqlDet = "Select p.*, c.cc_codigo From ".$conf->getTbl_predreq()." p "
						."Join ".$conf->getTbl_cecosto()." c"
						." On c.id_cc = p.id_cc"
						." Where p.id_proveedor = ".$row['id_proveedor']." And "
						."p.predreq_estado = 3 And "
						."p.predreq_total > 0 And "
						."p.predreq_fecha_col = '".$row['predreq_fecha_col']."' And "
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
 * Lista todas
*/
function listasNR($id_empresa) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select h.*, c.cc_descripcion "
			."From "
			.$conf->getTbl_prehreq()." h"
			." Join ".$conf->getTbl_cecosto()." c"
			." On c.id_cc = h.id_cc"
			." Where "
			." h.prehreq_estado = 4"
			." And h.id_empresa = ".$id_empresa;
		try {
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ($run) ) {
					$sqlDet = "Select p.*, c.cc_codigo From ".$conf->getTbl_predreq()." p "
						."Join ".$conf->getTbl_cecosto()." c"
						." On c.id_cc = p.id_cc"
						." Where p.id_prehreq = ".$row['id_prehreq']." And "
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
 * Ejecuta consulta para la paginacion
*/
function ConsultaNR($page, $form){
	try {
		$rowsperpage = 10;
		$inicio = ($rowsperpage*$page)-$rowsperpage;
		$limite = $rowsperpage;
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		// Obtenemos datos de empresa
		$sql_emp = "Select id_empresa_oc From ".$conf->getTbl_empresa()
			." Where id_empresa = ".$form['empresa'];
		$res_emp = $db->ejecutar($sql_emp);
		$row_emp = mysqli_fetch_array($res_emp);
		//
		$form['numero'] = (int) $form['numero'];
		$numero = "";
		$cc = "";
		if($form['numero'] > 0) {
			$numero = " And prehreq_numero_req = ".$form['numero'];
		}
		if($form['centrocosto'] != '00' && $form['centrocosto'] != 'todos') {
			$cc = " And a.id_cc = ".$form['centrocosto'];
		}
		// contamos el total de registros devueltos por la consulta, para la paginacion
		$sql = "Select a.*, b.cc_descripcion From ".$conf->getTbl_prehreq()." a"
			." join ".$conf->getTbl_cecosto()." b"
			." On b.id_empresa = a.id_empresa And"
			." b.id_cc = a.id_cc"
			." Where "
			."a.id_empresa = ".$form['empresa']
			.$cc
			.$numero
			." And a.prehreq_numero_req > 0"
			." And a.prehreq_estado > 4"
			." Order By prehreq_fecha Desc";
		$run = $db->ejecutar($sql);
		$registros = mysqli_num_rows($run);
		/*
		 * Seleccionamos los datos del encabezado para la pagina seleccionada
		*/
		$sql = "Select a.*, b.cc_descripcion From ".$conf->getTbl_prehreq()." a"
			." join ".$conf->getTbl_cecosto()." b"
			." On b.id_empresa = a.id_empresa And"
			." b.id_cc = a.id_cc"
			." Where "
			."a.id_empresa = ".$form['empresa']
			.$cc
			.$numero
			." And a.prehreq_numero_req > 0"
			." And a.prehreq_estado > 4"
			." Order By prehreq_fecha Desc"
			." Limit ".$inicio.",".$limite;
		$run = $db->ejecutar($sql);
		$registrosFilter = mysqli_num_rows($run);
		// Total de resitros encontrados
		$array['registros'] = $registros;
		if ($registrosFilter > 0) {
			// Leemos todos los encabezados
			while ( $row = mysqli_fetch_array ( $run ) ) {
				$row['nestado'] = $conf->getEstado($row['prehreq_estado']);
				// Detalle
				$sqlDet = "Select * From ".$conf->getTbl_predreq()
				." Where id_prehreq = ".$row['id_prehreq'];
				$runDet = $db->ejecutar($sqlDet);
				$rowD = array();
				if(mysqli_num_rows($runDet) <= 0) {
					$rowD[] = "";
				} else {
					// Leemos el detalle de la requisicion
					while($rowDet = mysqli_fetch_array($runDet)) {
						// Aqui nos vamos a ver el estado de la O.C. si ya esta ingresada
						if($rowDet['predreq_estado'] == 4 && $rowDet['predreq_numero_oc'] != '') {
							try {
								$db->conecta_OC();
								$sql_oc = "Select INGRESO From ".$conf->getTorden().$row_emp['id_empresa_oc']
									." Where NORDEN = '".$rowDet['predreq_numero_oc']."'";
								$res_oc = $db->ejecuta_OC($sql_oc);
								$row_oc = mysqli_fetch_array($res_oc);
								$db->desconecta_OC();
							} catch (Exception $e) {
								$result = $e->getMessage();
							}
							// Verificamos que ya este ingresada
							if($row_oc['INGRESO'] == 1){
								/*
								 * Marcamos todo el detalle para
								 * hacer la validacion solo una vez
								 */
								try {
									$sql_up = "Update ".$conf->getTbl_predreq()
										." Set "
										." predreq_estado = 5"
										." Where id_prehreq = ".$row['id_prehreq']
										." And predreq_numero_oc = '".$rowDet['predreq_numero_oc']."'";
									$db->ejecutar($sql_up);
									// Lo ponemos tambien en la coleccion que vamos a retornar
									$rowDet['predreq_estado'] = 5;
								} catch (Exception $e) {
									$result = $e->getMessage();
								}
							}
						}
						$rowD[] = $rowDet;
					}
				}
				// Guardamos el detalle
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
 *
 */
function ConsultaOC($form){
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
	list($emp, $emp_oc) = explode('-', $form['empresa']);
	// Obtenemos datos de empresa
	$sql = "Select * From ".$conf->getTbl_predreq()
		." Where id_empresa = ".$emp
		." And predreq_numero_oc = '".$form['oc']."'";
	$res = $db->ejecutar($sql);
	$result = array();
	while ($row = mysqli_fetch_array($res)){
		$result[] = $row;
	}
	return $result;
}

/*
 * Lista todas para trabajar
*/
function listasTrabajar($id_empresa) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sql = "Select e.id_prehreq, e.id_empresa, e.id_cc, e.prehreq_fecha, "
			."e.prehreq_numero_req, e.prehreq_usuario, c.cc_codigo, c.cc_descripcion From "
			.$conf->getTbl_prehreq()." e"
			." Join ".$conf->getTbl_cecosto()." c"
			." On c.id_empresa = e.id_empresa And c.id_cc = e.id_cc"
			." Where e.prehreq_estado = 3"
			." And e.id_empresa=".$id_empresa
			." Order By e.id_cc, e.prehreq_numero_req";
		//echo $sql;
		try {
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ($run) ) {
					$sqlDet = "Select * From ".$conf->getTbl_predreq()
					." Where id_prehreq = ".$row['id_prehreq']
					." Order by prod_codigo";
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
 * Lista todas para trabajar
*/
function listasTrabajarConsolidado($id_empresa) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		
		$sql = "Select d.predreq_fecha_col, d.prod_codigo, d.id_proveedor, SUM(d.predreq_cantidad_aut) as cantidad,"
				." d.predreq_prec_uni, p.prov_nombre"
				." From ".$conf->getTbl_predreq()." d"
				." Join proveedor p On p.id_proveedor = d.id_proveedor"
				." Where d.predreq_estado = 3"
				." and d.id_empresa = ".$id_empresa
				." and IsNull(d.predreq_numero_oc)"
				." GROUP BY d.predreq_fecha_col, d.prod_codigo, d.id_proveedor";
		
		//echo $sql;
		try {
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ($run) ) {
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
 * Lista todas para trabajar
*/
function listasPreOC($id_empresa) {
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();

		$sql = "Select d.predreq_numero_oc, d.id_proveedor, p.prov_nombre, d.id_empresa "
			." From ".$conf->getTbl_predreq()." d"
			." Join ".$conf->getTbl_proveedor()." p"
			." On p.id_proveedor = d.id_proveedor"
			." Where d.predreq_estado = 3"
			." And d.id_empresa = ".$id_empresa
			." And d.predreq_numero_oc <> ''"
			." GROUP BY d.predreq_numero_oc";

		//echo $sql;
		try {
			$run = $db->ejecutar ( $sql );
			if (mysqli_num_rows($run) > 0) {
				while ( $row = mysqli_fetch_array ($run) ) {
					$sql_deta = "Select * From ".$conf->getTbl_predreq()
						." Where predreq_estado = 3"
						." And id_empresa = ".$id_empresa
						." And predreq_numero_oc = '".$row['predreq_numero_oc']."'"
						." Order By prod_codigo";
					$runDet = $db->ejecutar($sql_deta);
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
* Lista todos los centros de costo que tiene
* asignado el usuario para una empresa
*/
function listarCC(){
	if (isset($_POST['id_empresa']) && $_POST['id_empresa'] != "") {
		try {
			$idEmpresa = $_POST['id_empresa'];
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sql = "select id_cc, cc_codigo, cc_descripcion from " .$conf->getTbl_cecosto()
				." Where id_empresa = ".$idEmpresa
				." order by cc_descripcion";
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