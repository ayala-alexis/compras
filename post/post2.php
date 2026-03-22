<?php
/* Script for update record from X-editable. */
/* You will get 'pk', 'name' and 'value' in $_POST array. */
try {
	$pk = $_POST['pk'];
	$cant = $_POST['value']['city'];
	$obs  = $_POST['value']['street'];
	try {
		include_once '../Configuracion.php';
		include_once '../DB.php';
		include_once '../ODBC.php';
		/* Check submitted value */
		if(!empty($cant)) {
			if (!empty($pk)) {
				try {
					$DB = DB::getInstance();
					$conf = Configuracion::getInstance();
					$sql_ver = 'Select predsol_cantidad_aut, predsol_prec_uni, id_prehsol From '.$conf->getTbl_predsol()
						." Where id_predsol = ".$pk;
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
							$sql_ver = "Select prehsol_estado, prehsol_fecha, id_empresa From ".$conf->getTbl_prehsol()
								." Where "
								."id_prehsol = ".$fetch[2];
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
										$d = date_parse_from_format('Y-m-d', $row[1]);
										$mes = $d["month"];
										$year = $d["year"];
										// Datos de empresa
										$sql_emp = "Select * From ".$conf->getTbl_empresa()
										." Where id_empresa = ".$row['id_empresa'];
										$run_emp = $DB->ejecutar($sql_emp);
										$row_emp = mysqli_fetch_array($run_emp);
										// Verificamos presupuesto
										$sql_pre = "Select p.predsol_titgas, p.predsol_detgas, c.cc_codigo, p.predsol_cantidad_aut, p.id_prehsol, c.id_cc From ".$conf->getTbl_predsol()." p"
												." Join ".$conf->getTbl_cecosto()." c"
												." On c.id_cc = p.id_cc"
												." Where p.id_predsol = ".$pk;
										try {
											$run_pre = $DB->ejecutar($sql_pre);
											if (mysqli_num_rows($run_pre) <= 0) {
												$rtn = 1;
												$msg = 'el centro de costo no es valido';
											} else {
												$row_pre = mysqli_fetch_array($run_pre);
												$gastod = str_pad($row_pre[0],2,'0',STR_PAD_LEFT).str_pad($row_pre[1],2,'0',STR_PAD_LEFT);
												// Empresa usa presupuesto
												if ($row_emp['emp_usa_presupuesto'] == '1') {
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
														// Conexiones ODBC
														$o = ODBC::getInstance();
														$cc = $row_pre[2];
														$idcc = $row_pre[5];
														$gas2 = $gastod;
														$month = $mes;
														$Cia = $row_emp['cia_presupuesto'];
														$idprehsol = $row_pre[4];

														$bandera = $o->ocuparaPresupuestoMesesAnteriores($Cia, $row_pre[2], $gastod);
														$o->separaGasto($gas2);

														$S2 = "Select SUM(predsol_cantidad_aut),SUM(predsol_prec_uni),SUM(predsol_total) From ".$conf->getTbl_predsol()
															." Where predsol_estado = 3 "
															." AND id_cc = '".$idcc
															."' AND predsol_titgas = '"
															.$o->gastit."' AND predsol_detgas = '"
															.$o->gasdet."' And "
															."id_predsol <> ".$pk;
														$Q2 = $DB->ejecutar($S2);
														$R2 = mysqli_fetch_array($Q2);

														//$Total_Orden = $row_pre[3]*$precio_unit; // Precion Total del Item
														$Total_Orden = $cant*$precio_unit; // Precion Total del Item

														if ($bandera=='S') {
															$dispo2 = 0.00;
															$o->Dispo2($Cia,$cc,$gas2,$month,'');
															$dispo2 = $o->dispo2-$R2[2];
															if ($dispo2 < $Total_Orden) {
																$rtn = 1;
																$msg = "PRESUPUESTO INSUFICIENTE : $ ".($dispo2)."<br>DE CENTRO DE COSTOS : ".$cc."<br>";
																$msg .= "VALOR ITEM : $ ".$Total_Orden."<br>";
																$msg .= "VALOR EN SOLICITUD : $ ".($R2[2])."<br>";
																$msg .= "PARA CUENTA DE GASTO : ".$gas2."<br>DEL MES : ".$o->Qmes($month);
															}
														} else {
															$monto = $o->MontoPresupuesto($year,$Cia,$gas2,$cc,$month,'2'); // Presupuesto de CC
															$auth = $o->MontoAutorizado($year,$Cia,$gas2,$cc,$month,'2'); // Incrementos
															$decrem = $o->MontoDecremento($year,$Cia,$gas2,$cc,$month); // Decrementos
															$presupuesto_neto = ($monto+$auth)-$decrem; //Presupuesto + Incrementos - Decrementos
															$gastoSalidas = $o->GastoMS($year,$Cia,$gas2,$cc,$month); // Gastos Salidas
															$gastoEntradas = $o->GastoM($year,$Cia,$gas2,$cc,$month); // Gastos Entradas
															$gastoNeto = $gastoSalidas-$gastoEntradas; //Gato Neto = Salidas - Entradas
															$Presupuesto_Disponible = $presupuesto_neto-$gastoNeto-$R2[2]; // Disponible
															$Presupuesto_Disponible = round($Presupuesto_Disponible,2);
															if ($Presupuesto_Disponible < $Total_Orden){
																$dispo2 = $Presupuesto_Disponible;
																if ($dispo2 < $Total_Orden) {
																	$rtn = 1;
																	$msg = "PRESUPUESTO INSUFICIENTE : $ ".($dispo2)."<br>DE CENTRO DE COSTOS : ".$cc."<br>";
																	$msg .= "VALOR ITEM : $ ".$Total_Orden."<br>";
																	$msg .= "VALOR EN SOLICITUD : $ ".($R2[2])."<br>";
																	$msg .= "PARA CUENTA DE GASTO : ".$gas2."<br>DEL MES : ".$o->Qmes($month);
																}
															}
														}
													}
												}
												if($rtn == 0) {
													$sql = "Update ".$conf->getTbl_predsol()." Set "
														."predsol_cantidad_aut = ".$cant.", "
														."predsol_total = predsol_prec_uni*".$cant.", "
														."predsol_cantidad_aut_obs = '".mysqli_real_escape_string(strtoupper($obs))."'"
														." Where id_predsol = ".$pk;
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
							// No verifica presupuesto actualizacmo lo que tengamos
							$sql = "Update ".$conf->getTbl_predsol()." Set "
								."predsol_cantidad_aut = ".$cant.", "
								."predsol_total = predsol_prec_uni*".$cant.", "
								."predsol_cantidad_aut_obs = '".mysqli_real_escape_string(strtoupper($obs))."'"
								." Where id_predsol = ".$pk;
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
			echo json_encode("debe digitar una cantidad!");
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
