<?php
/*
$name = $_POST['name'];
$pk = $_POST['pk'];
$value = $_POST['value'];
$a = $_POST['a'];
$fp = fopen("ejemplo.txt","a");

fwrite($fp, "Name: $name\tPK: $pk\tValue: $value\tAdicional: $a" . PHP_EOL);
fclose($fp);
*/
/* Script for update record from X-editable. */
/* You will get 'pk', 'name' and 'value' in $_POST array. */
try {
	$pk = $_POST['pk'];
	$name = $_POST['name'];
	$cant = $_POST['value'];
	$prehreq_numero = $_POST['prehreq_numero'];
	$id_empresa = $_POST['id_empresa'];
	$id_cc = $_POST['id_cc'];
try {
		include_once '../Configuracion.php';
		include_once '../DB.php';
		include_once '../ODBC.php';
		include_once '../ODBCIT.php';
		/* Check submitted value */
		if($cant >= 0) {
			if (!empty($pk)) {
				try {
					$DB = DB::getInstance();
					$conf = Configuracion::getInstance();
					$sql_ver = 'Select predreq_cantidad_aut, predreq_prec_uni, id_prehreq From '.$conf->getTbl_predreq()
						." Where id_predreq = ".$pk;
					$runver = $DB->ejecutar($sql_ver);
					$fetch = mysqli_fetch_array($runver);
					if($fetch[0] == $cant) {
						header('HTTP 400 Bad Request', true, 400);
						echo json_encode("no ha realizado ningun cambio!");
					} else {
						// Si tiene precio tenemos que verificar el presupuesto
						if($fetch[1] > 0) {
							$rtn = 0;
							$precio_unit = $fetch[1];
							$sql_ver = "Select prehreq_estado, prehreq_fecha, id_empresa From ".$conf->getTbl_prehreq()
								." Where "
								."id_prehreq = ".$fetch[2];
							try {
								$run_sql = $DB->ejecutar($sql_ver);
								if (mysqli_num_rows($run_sql) <= 0) {
									$rtn = 1;
									$msg = 'La presolicitud ha dejado de existir';
								} else {
									$row = mysqli_fetch_array($run_sql);
									if ($row[0] > 3) {
										$rtn = 1;
										$msg = 'su estado ha sido <b>'.$conf->getEstado($row[0]).'</b>, presione F5 para actualizar.';
									} else  {
										$fecha_prehreq = $row[1];
										$d = date_parse_from_format('Y-m-d', $row[1]);
										$mes = $d["month"];
										$year = $d["year"];
										// Datos de empresa
										$sql_emp = "Select * From ".$conf->getTbl_empresa()
										." Where id_empresa = ".$row['id_empresa'];
										$run_emp = $DB->ejecutar($sql_emp);
										$row_emp = mysqli_fetch_array($run_emp);
										// Verificamos presupuesto
										$sql_pre = "Select p.predreq_titgas, p.predreq_detgas, c.cc_codigo, p.predreq_cantidad_aut, p.id_prehreq, c.id_cc From ".$conf->getTbl_predreq()." p"
												." Join ".$conf->getTbl_cecosto()." c"
												." On c.id_cc = p.id_cc"
												." Where p.id_predreq = ".$pk;
										try {
											$run_pre = $DB->ejecutar($sql_pre);
											if (mysqli_num_rows($run_pre) <= 0) {
												$rtn = 1;
												$msg = 'el centro de costo no es valido';
											} else {
												$row_pre = mysqli_fetch_array($run_pre);
												$gastod = str_pad($row_pre[0],2,'0',STR_PAD_LEFT).str_pad($row_pre[1],2,'0',STR_PAD_LEFT);
												$cuenta_valida_presupuesto = true;
												$presupuesto_tipo_gasto = $gastod;
												$presupuesto_detalle_gasto = $row_pre[1];
												// Empresa debe poseer presupuesto
												if ($row_emp['emp_usa_presupuesto'] == '1' && $cant > 0) {
													// Obtenemos codigo de Centro de Costo
													$sql_cc = "Select cc_codigo From ".$conf->getTbl_cecosto()
													." Where id_cc = ".$id_cc;
													$run_cc = $DB->ejecutar($sql_cc);
													$row_cc = mysqli_fetch_array($run_cc);
													// LOCAL o AS400
													if ($row_emp['emp_origen_presupuesto'] == 'LOCAL') {
														//
														$cc = $row_cc[0];
														$idcc = $id_cc;
														$gas2 = $gastod;
														$month = $mes;
														$Cia = $row_emp['cia_presupuesto'];
														$idpredreq = $pk;
														$prefield = "pres_pre".str_pad($month,2,'0',STR_PAD_LEFT);
														$local_gas_tit = str_pad($row_pre[0],2,'0',STR_PAD_LEFT);
														$local_gas_det = str_pad($row_pre[1],2,'0',STR_PAD_LEFT);

														$feini = $year.$month.'01';
														$fefin = $year.$month.'31';
														// Nos traemos lo ya gastado del mes
														$S2 = "Select SUM(predreq_cantidad_aut),"
															."SUM(predreq_prec_uni),SUM(predreq_total) From "
															.$conf->getTbl_predreq()
															." Where predreq_estado = 3 "
															." AND id_cc = '".$idcc
															."' AND predreq_fecha Between ".$feini." and ".$fefin
															." AND predreq_titgas = '"
															.$local_gas_tit."' AND predreq_detgas = '"
															.$local_gas_det."' ";
														$Q2 = $DB->ejecutar($S2);
														$R2 = mysqli_fetch_array($Q2);
														// Veamos lo que ya tiene la orden de este gasto
														$S3 = "Select SUM(predreq_cantidad_aut),"
																."SUM(predreq_prec_uni),SUM(predreq_total) From "
																.$conf->getTbl_predreq()
																." Where id_prehreq = ".$row_pre[4]
																." AND id_cc = ".$idcc
																." AND id_predreq <> ".$pk
																." AND predreq_titgas = '"
																.$local_gas_tit."' AND predreq_detgas = '"
																.$local_gas_det."' ";
														$Q3 = $DB->ejecutar($S3);
														$R3 = mysqli_fetch_array($Q3);
														// Vamos a traernos el disponible
														$sql_disponible_local = "Select ".$prefield
															." From ".$conf->getTbl_presupuesto()
															." Where id_empresa = ".$row['id_empresa']
															." And cc_codigo=".$row_cc[0]
															." And gas_tit_codigo='".$local_gas_tit."'"
															." And gas_det_codigo='".$local_gas_det."'"
															." And pres_anyo = ".$year;
														$run_disponible_local = $DB->ejecutar($sql_disponible_local);
														$row_disponible_local = mysqli_fetch_array($run_disponible_local);
														// Id del Gasto
														$sql_gasto_local = "Select id_tagasto"
															." From ".$conf->getTbl_tagasto()
															." Where"
															." gas_tit_codigo='".$local_gas_tit."'"
															." And gas_det_codigo='".$local_gas_det."'";
														$run_gasto_local = $DB->ejecutar($sql_gasto_local);
														$row_gasto_local = mysqli_fetch_array($run_gasto_local);
														// Vamos a traernos las autorizaciones
														$auto = 0;
														$sql_aut_local = "Select aut_valor, aut_signo"
															." From ".$conf->getTbl_autorizacion()
															." Where id_empresa = ".$row['id_empresa']
															." And id_cc=".$idcc
															." And id_tagasto=".$row_gasto_local[0]
															." And aut_mes=".$month
															." And aut_anyo = ".$year;
														$run_aut_local = $DB->ejecutar($sql_aut_local);
														while($row_aut_local = mysqli_fetch_array($run_aut_local)){
															if($row_aut_local[1] == '+') {
																$auto += $row_aut_local[0];
															} else {
																$auto -= $row_aut_local[0];
															}
														}
														// Disponible
														$dispo2 = $row_disponible_local[0]-$R2[2]+$auto-$R3[2];
														//$Total_Orden = $row_pre[3]*$precio_unit; // Precion Total del Item
														$Total_Orden = $cant*$precio_unit; // Precion Total del Item
														if ($dispo2 < $Total_Orden) {
															$rtn = 1;
															$msg = "PRESUPUESTO INSUFICIENTE : $ ".($dispo2)."\NDE CENTRO DE COSTOS : ".$row_cc[0]."\N";
															$msg .= "VALOR ITEM : $ ".$Total_Orden."\N";
															$msg .= "VALOR EN SOLICITUD : $ ".($R3[2])."\N";
															$msg .= "PARA CUENTA DE GASTO : ".$local_gas_tit.$local_gas_det."\NDEL MES : ".$mes;
															/*$msg .= '<hr>';
															$msg .= '<br><h4>habilitado modo <sup class="fg-color-red text-warning">beta</sup></h4>';
															$msg .= '<br><b>Informacion Tecnica : </b>'.$sql_disponible_local;*/
														} /*else {
															$rtn = 1;
															$msg = "PRESUPUESTO INSUFICIENTE : $ ".($dispo2)."<br>DE CENTRO DE COSTOS : ".$row_cc[0]."<br>";
															$msg .= "VALOR ITEM : $ ".$Total_Orden."<br>";
															$msg .= "VALOR EN SOLICITUD : $ ".($R2[2])."<br>";
															$msg .= "PARA CUENTA DE GASTO : ".$local_gas_tit.$local_gas_det."<br>DEL MES : ".$mes;
															$msg .= $sql_disponible_local;
														}*/
													} else {
													
														/*
														 * Busca la cuenta en la tablas de restricciones de presupuesto
														*/
														$DB->conecta_OC();
														$sql_oc = "Select * From ".$conf->getTbl_restric()." Where resgas = '".$gastod."' And rescia = '".$row_emp['cia_presupuesto']."'";
														$runsql = $DB->ejecuta_OC($sql_oc);
														$rowsql = mysqli_num_rows($runsql);
														$DB->desconecta_OC();
														/*
														 * La cuenta debe verificar su disponibilidad
														*/
														if ($rowsql > 0) {
															/*
															 * ahora nos la vamos a jugar con el codigo de la compa�ia
															* para crear nuestro controlador de AS/400
															* 1 = I.R.
															* 2 = I.T.
															*/
															// Conexiones ODBC
															if ($row_emp['id_empresa'] == 1){
																$o = ODBC::getInstance();
															} else {
																$o = ODBCIT::getInstance();
															}
															$cc = $row_pre[2];
															$idcc = $row_pre[5];
															$gas2 = $gastod;
															$month = $mes;
															$Cia = $row_emp['cia_presupuesto'];
															$idprehreq = $row_pre[4];
				
															//Validaci�n de cuenta de gasto
															$cuenta_valida_presupuesto = $o->valida_presupuesto_cuenta_gasto($presupuesto_tipo_gasto,$presupuesto_detalle_gasto);

															if($cuenta_valida_presupuesto || TRUE){
																$bandera = $o->ocuparaPresupuestoMesesAnteriores($Cia, $row_pre[2], $gastod);
																$o->separaGasto($gas2);
		
																$S2 = "Select SUM(predreq_cantidad_aut), SUM(predreq_prec_uni), SUM(predreq_total) From ".$conf->getTbl_predreq()
																	." Where predreq_estado = 3 "
																	." AND id_cc = '".$idcc
																	."' AND predreq_titgas = '"
																	.$o->gastit."' AND predreq_detgas = '"
																	.$o->gasdet."' And "
																	."id_predreq <> ".$pk;
																$Q2 = $DB->ejecutar($S2);
																$R2 = mysqli_fetch_array($Q2);
																
																// Veamos lo que ya tiene la orden de este gasto
																$S3 = "Select SUM(predreq_cantidad_aut),"
																		."SUM(predreq_prec_uni),SUM(predreq_total) From "
																		.$conf->getTbl_predreq()
																		." Where id_prehreq = ".$idprehreq
																		." AND id_cc = ".$idcc
																		." AND id_predreq <> ".$pk
																		." AND predreq_titgas = '"
																		.$local_gas_tit."' AND predreq_detgas = '"
																		.$local_gas_det."' ";
																$Q3 = $DB->ejecutar($S3);
																$R3 = mysqli_fetch_array($Q3);
		
																//$Total_Orden = $row_pre[3]*$precio_unit; // Precion Total del Item
																$Total_Orden = $cant*$precio_unit; // Precion Total del Item

																if ($bandera=='S') {
																	$dispo2 = 0.00;
																	$o->Dispo2($Cia,$cc,$gas2,$month,$fecha_prehreq);
																	$dispo2 = $o->dispo2-$R3[2];
																	if ($dispo2 < $Total_Orden) {
																		$rtn = 1;
																		$msg = "PRESUPUESTO INSUFICIENTE : $ ".($dispo2)."<br>DE CENTRO DE COSTOS : ".$cc."<br>";
																		$msg .= "VALOR ITEM : $ ".$Total_Orden."<br>";
																		$msg .= "VALOR EN SOLICITUD : $ ".($R3[2])."<br>";
																		$msg .= "PARA CUENTA DE GASTO : ".$gas2."<br>DEL MES : ".$o->Qmes($month);
																		//$msg .= $o->dispo2D;
																	}
																} else {
																	$monto = $o->MontoPresupuesto($year,$Cia,$gas2,$cc,$month,'2'); // Presupuesto de CC
																	$auth = $o->MontoAutorizado($year,$Cia,$gas2,$cc,$month,'2'); // Incrementos
																	$decrem = $o->MontoDecremento($year,$Cia,$gas2,$cc,$month); // Decrementos
																	$presupuesto_neto = ($monto+$auth)-$decrem; //Presupuesto + Incrementos - Decrementos
																	$gastoSalidas = $o->GastoMS($year,$Cia,$gas2,$cc,$month); // Gastos Salidas
																	$gastoEntradas = $o->GastoM($year,$Cia,$gas2,$cc,$month); // Gastos Entradas
																	$gastoNeto = $gastoSalidas-$gastoEntradas; //Gato Neto = Salidas - Entradas
																	$Presupuesto_Disponible = $presupuesto_neto-$gastoNeto-$R3[2]; // Disponible
																	$Presupuesto_Disponible = round($Presupuesto_Disponible,2);
																	if ($Presupuesto_Disponible < $Total_Orden){
																		$dispo2 = $Presupuesto_Disponible;
																		if ($dispo2 < $Total_Orden) {
																			$rtn = 1;
																			$msg = "PRESUPUESTO INSUFICIENTE : $ ".($dispo2)."<br>DE CENTRO DE COSTOS : ".$cc."<br>";
																			$msg .= "VALOR ITEM : $ ".$Total_Orden."<br>";
																			$msg .= "VALOR EN SOLICITUD : $ ".($R3[2])."<br>";
																			$msg .= "PARA CUENTA DE GASTO : ".$gas2."<br>DEL MES : ".$o->Qmes($month);
																			//$msg .= $o->dispo2D;
																		}
																	}
																}
															}
														}
													}
												}
												if($rtn == 0) {
													$sql = "Update ".$conf->getTbl_predreq()." Set "
														."predreq_cantidad_aut = ".$cant.", "
														."predreq_cantidad = ".$cant.", "
														."predreq_total = predreq_prec_uni*".$cant.", "
														."predreq_cantidad_aut_obs = '".mysqli_real_escape_string(strtoupper($obs))."'"
														." Where id_predreq = ".$pk;
													try {
														$DB->ejecutar($sql);
														echo json_encode('ok');
													} catch (Exception $e3) {
														header('HTTP 400 Bad Request', true, 400);
														echo json_encode($e3->getMessage());
													}

												} else {
													header('HTTP 400 Bad Request', true, 400);
													echo json_encode($msg);
												}
											}
										} catch(Exception $e5) {
											header('HTTP 400 Bad Request', true, 400);
											echo json_encode($e5->getMessage());
										}
									}
								}
							} catch(Exception $e2) {
								header('HTTP 400 Bad Request', true, 400);
								echo json_encode($e2->getMessage());
							}
						} else {
							$sql = "Update ".$conf->getTbl_predreq()." Set "
								."predreq_cantidad_aut = ".$cant.", "
								."predreq_cantidad = ".$cant.", "
								."predreq_total =( predreq_prec_uni*".$cant."), "
								."predreq_cantidad_aut_obs = '".mysqli_real_escape_string(strtoupper($obs))."'"
								." Where id_predreq = ".$pk;
							try {
								$DB->ejecutar($sql);
								echo json_encode('ok');
							} catch (Exception $e3) {
								header('HTTP 400 Bad Request', true, 400);
								echo json_encode($e3->getMessage());
							}

						}
					}
				} catch (Exception $e1) {
					header('HTTP 400 Bad Request', true, 400);
					echo json_encode($e1->getMessage());
				}
			} else {
				header('HTTP 400 Bad Request', true, 400);
				echo json_encode("no se ha podido actualizar");
			}
		} else {
			header('HTTP 400 Bad Request', true, 400);
			echo json_encode("debe digitar una cantidad!, $cant");
		}
	} catch (Exception $e) {
		header('HTTP 400 Bad Request', true, 400);
		echo json_encode($e->getMessage());
	}
} catch (Exception $p) {
	header('HTTP 400 Bad Request', true, 400);
	echo json_encode($p->getMessage());
}
?>
