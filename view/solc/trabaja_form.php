<?php
//print_r($detas); 
//print_r($infohsol);
?>
<link rel="stylesheet" type="text/css" href="css/style2.css?v=<?php echo date('His') ?>" />
<style>
	.invoice-title h2,
	.invoice-title h3 {
		display: inline-block;
	}

	.table>tbody>tr>.no-line {
		border-top: none;
	}

	.table>thead>tr>.no-line {
		border-bottom: none;
	}

	.table>tbody>tr>.thick-line {
		border-top: 2px solid;
	}

	#frmEnvia {
		padding: 0;
	}
</style>
<script>
	function GetURLParameter(sParam) {
		var sPageURL = window.location.search.substring(1);
		var sURLVariables = sPageURL.split('&');
		for (var i = 0; i < sURLVariables.length; i++) {
			var sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] == sParam) {
				return sParameterName[1];
			}
		}
	}
	$(function () {

		$('[data-toggle="tooltip"]').tooltip();

		$('#frmEnvia').validate({
			submitHandler: function (form) {
				$('body').removeClass('loaded');
				form.submit();
			}
		});

		$('#frmAuto').submit(function (e) {
			$('body').removeClass('loaded');
			e.preventDefault();
			return;
		});
		$('#frmAuto2').submit(function (e) {
			$('body').removeClass('loaded');
			e.preventDefault();
			return;
		});
		$('#btnAutoriza').live('click', function () {
			var $btn = $(this);
			jConfirm('esta seguro de esa categoria?', 'asignar categoria', function (answer) {
				console.log(answer);
				if (answer) {
					var campos = new Object();
					rtn = 1;
					campos['prehsol_numero'] = GetURLParameter('ps');
					campos['empresa'] = GetURLParameter('es');
					campos['centrocosto'] = GetURLParameter('cs');
					campos['id_prehsol'] = $('#id_prehsol').val();
					campos['tabla'] = 'prehsol';
					campos['accion'] = 'autorizaSend';
					campos['categoria'] = $('#categoria').val();
					campos['obs_cate'] = $('#obs_cate').val();

					if ($("#categoria").val() > 0) {

						campos['input_monto'] = $('#input_monto').val();
						campos['input_moneda'] = $('#input_moneda').val();
						campos['input_proveedor'] = $('#input_proveedor').val();
						campos['input_metodo'] = $('#input_metodo').val();

						if ($('#input_monto').val().trim().length == 0) {
							notie.alert('error', 'Ingrese el monto!', 3);
							return false;
						}

						if (!Match($('#input_monto').val().trim(), "^[0-9]+([,.][0-9]+)?$")) {
							notie.alert('error', 'Existe un error en el monto!', 3);
							return false;
						}
					} else {
						campos['input_monto'] = "";
						campos['input_proveedor'] = "";
						campos['input_metodo'] = "";
						campos['input_moneda'] = "";
					}

					console.log(campos['id_prehsol']);
					console.log(campos['categoria']);

					var items = ($('table#tablaPresol > tbody tr:last').index() + 1);
					//alert(campos["categoria"]);
					if (items <= 0) {
						notie.alert('error', 'la solicitud esta vacia!', 3);
					} else {
						if ($('#categoria').val().trim().length == 0) {
							notie.alert('error', 'seleccione una categoria valida', 3);
						} else {
							if ($('#obs_cate').val().trim().length == 0) {
								notie.alert('error', 'digite una observacion', 3);
							} else {
								// Enviamos a borrar de la tabla
								myUrl = "class/Formulario.php";
								//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
								console.log(myUrl);
								//saveItem(rtn);
								if (rtn = 1) {
									$.ajax({
										type: 'POST',
										url: myUrl,
										data: {
											form: campos
										},
										beforeSend: function () {
											$('input').addClass('disabled').prop('disabled', true);
											notie.alert('info', 'la autorizacion de la solicitud esta en proceso, por favor espere...', 3);
											$btn.addClass('disabled');
											$btn.attr('disabled', 'disabled');
											$btn.prop("disabled", true);
											$('body').addClass('loaded');
										},
										success: function (data) {

											console.log("termino");
											console.log(data);
											console.log("termino");
											console.log($.isNumeric(data));
											if (!isNumeric(data)) {
												$('#loader-wrapper').attr('display', 'hidden');
												notie.alert('warning', 'la pre-solicitud ha sufrido cambios ', 5);
												console.log("Falso");
											} else {
												console.log("Pasa");
												if (data == 1) {
													$('#loader-wrapper').attr('display', 'hidden');
													notie.alert('warning', 'ha ocurrido un error' + data, 5);
													$('input').removeClass('disabled').prop('disabled', false);
												} else {
													$('#loader-wrapper').attr('display', 'block');
													//$('strong').text('AUTORIZADO');
													$btn.remove();
													myUrl = "class/PHPMailer/send1.php?" + $('#id_prehsol').val();
													//myUrl = location.protocol + "//" + location.host + "/sics/class/PHPMailer/send1.php?"+$('#id_prehsol').val();
													window.location.href = myUrl;
												}
											}
										},
										error: function (XMLHttpRequest, textStatus, errorThrown) {
											notie.alert('error', 'ha ocurrido un error!<br>' + textStatus, 3);
											$btn.removeClass('disabled');
											$btn.removeAttr('disabled');
											$btn.removeProp("disabled");
											$('input').removeClass('disabled').prop('disabled', false);
											$('body').removeClass('loaded');
										}
									});
								}
							}
						}
					}
					return false; // prevents default behavior
				} else {
					notie.alert('info', 'aprobacion cancelada!', 3);
				}
			});
		});
		function isNumeric(n) {
			return !isNaN(parseFloat(n)) && isFinite(n);
		}
	});
</script>
<div class="container-fluid">
	<!-- Start Page Loading -->
	<div id="loader-wrapper">
		<h1>Espere...</h1>
		<div id="loader">
		</div>
		<div class="loader-section section-left"></div>
		<div class="loader-section section-right"></div>
	</div>
	<!-- End Page Loading -->
	<div class="row">
		<div class="col-xs-12">
			<div class="invoice-title">
				<h3>Gestion de Compra</h3>
				<h3 class="pull-right">Solicitud # <?php echo $infohsol[0]['prehsol_numero_sol']; ?></h3>
			</div>
			<hr>
			<div class="row">
				<div class="col-xs-4">
					<address>
						<strong>Fecha :</strong><br>
						<?php
						//setlocale(LC_TIME, "");
						//setlocale(LC_TIME, "es_ES");
						//echo iconv('ISO-8859-1', 'UTF-8', strftime('%A %d de %B, %Y %I:%M:%S %p', strtotime($infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora'])));
						
						setlocale(LC_TIME, "");
						setlocale(LC_TIME, "es_ES");
						/*echo date('l jS \of F Y H:i:s A', strtotime($infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora']));
						echo '<br>';*/

						// Crear objeto DateTime
						$fecha_hora = $infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora'];
						$datetime = new DateTime($fecha_hora);

						// Usar IntlDateFormatter para español
						$formatter = new IntlDateFormatter(
							'es_ES', // Locale español
							IntlDateFormatter::FULL, // Formato de fecha completo
							IntlDateFormatter::FULL, // Formato de hora completo
							'America/El_Salvador', // Zona horaria (ajusta según necesites)
							IntlDateFormatter::GREGORIAN,
							"EEEE d 'de' MMMM, yyyy hh:mm:ss a" // Formato personalizado
						);

						echo $formatter->format($datetime);

						echo '<p>';
						echo 'Observacion Usuario : <b>' . $infohsol[0]['prehsol_obs1'] . '</b>';
						echo '</p><p>Observacion Aprobado:';
						echo '<b>' . $infohsol[0]['prehsol_aprobacion_usuario'] . ' - ' . $infohsol[0]['prehsol_aprobacion'] . '</b></p>';
						if (!empty($infohsol[0]['prehsol_aprobacion_categoria'])) {
							echo '<p>Aprobado Categoria:';
							echo '<b>' . $infohsol[0]['prehsol_aprobacion_categoria_usuario'] . ' > ' . $infohsol[0]['prehsol_aprobacion_categoria'] . '</b></p>';
						}
						if (!empty($infohsol[0]['prehsol_aprobacion_gestion'])) {
							echo '<p>Aprobado Convergencia:';
							echo '<b>' . $infohsol[0]['prehsol_aprobacion_gestion_usuario'] . '>' . $infohsol[0]['prehsol_aprobacion_gestion'] . '</b></p>';
						}
						if (!empty($infohsol[0]['prehsol_gestion_observacion'])) {
							echo '<p>Observacion Gestion:' . $infohsol[0]['prehsol_gestion_nivel2_usuario'];
							echo '<b>' . $infohsol[0]['prehsol_gestion_observacion'] . '</b></p>';
						}
						if (!empty($infohsol[0]['obs_cate'])) {
							echo '<p>Observacion Categoria:' . $infohsol[0]['prehsol_aprobacion_categoria_usuario'];
							echo '<b>' . $infohsol[0]['obs_cate'] . '</b></p>';
						}
						?>
					</address>
				</div>

				<!--<div class="col-xs-3">
					<table>
						<tr><td><b>Metodo de pago</b></td></tr>
						<tr><td>Transferencia</td></tr>
						<tr><td>Cheque</td></tr>
						<tr><td>Cheque de gerencia</td></tr>
					</table>
				</div>-->

				<!--<div class="col-xs-2">
					<table>
						<tr><b>Condicion de pago</b></tr>
						<tr>
							<td><b>Anticipo</b></td>
							<td><b>Transferencia</b></td>
						</tr>
						<tr>
							<td>Texto libre %</td>
							<td>0</td>
						</tr>
						<tr>
							<td></td>
							<td>7 días</td>
						</tr>
						<tr>
							<td></td>
							<td>15 días</td>
						</tr>
						<tr>
							<td></td>
							<td>30 días</td>
						</tr>
						<tr>
							<td></td>
							<td>60 días</td>
						</tr>							
					</table>
				</div>-->
				<?php
				if (!empty($infohsol[0]['prehsol_monto'])): ?>

					<div style='font-size:14px;background: #f5f5f5;line-height: 12px;padding: 3px;' class="col-xs-4">
						<P><b>Proveedor:</b> <?php echo $Proveedor ?> </P>
						<P><b>Monto:</b>
							<?php if ($Empressa != 6 && $Empressa != 8) {
								echo "$";
							} else {
								echo $infohsol[0]['moneda'];
							} ?>

							<?php echo $infohsol[0]['prehsol_monto'] ?>
						</P>
						<P><b>Metodo de pago:</b> <?php echo $metodo_pago ?></P>
					</div>
				<?php endif; ?>

				<div class="col-xs-4 text-right">
					<address>
						<strong>Solicitado por:</strong><br>
						<?php echo $infohsol[0]['emp_nombre']; ?><br>
						<?php echo $infohsol[0]['cc_descripcion']; ?><br>
						<?php if ($infohsol[0]['prehsol_nuevo_gestion'] == 1) { ?>
							<a class="btn btn-lg btn-default"
								href="/sics/view/solc/PDF.php?id=<?php echo $infohsol[0]['id_prehsol']; ?>">
								<i class="glyphicon glyphicon-file"></i>&nbsp;Generar PDF
							</a>
							<?php
						}
						$cats = qCategoria($infohsol[0]['id_categoria']);
						echo '<h3>' . $cats[0][0] . '</h3>';
						?>
					</address>
				</div>
			</div>
		</div>
	</div>


	<div class="row traza">
		<h3 class="title_traza">
			<i class="glyphicon glyphicon-check"></i>
			Trazabilidad de solicitud
		</h3>
		<?php
		$estado = 0;
		/*foreach ($infohsol_stat as $state):
			$estado++;
			?>
			<?php
			$sta = (((int) $state['prehsol_stat'] == 10) ? 'stop' : 'ok');
			$sta = (((int) $state['prehsol_stat'] == 20) ? 'pause' : $sta);
			?>
			<div class="col-sm-2 traza-item <?php echo $sta ?>">
				<p>
					<?php echo get_status($state['prehsol_stat'], $state['prehsol_stat_desc']) ?><br />
				</p>
				<label>
					<i class="glyphicon glyphicon-user"></i>
					<?php echo $state['prehsol_stat_usuario'] ?>
				</label>
				<span><?php echo $state['prehsol_stat_fecha'] ?> 	<?php echo $state['prehsol_stat_hora'] ?></span>
				<span class="info-status">

					<?php if ($estado == 1) { ?>
						<i class="glyphicon glyphicon-ok-circle"></i> Solicitado
					<?php } else if ($estado == 3) { ?>
							<i class="glyphicon glyphicon-ok-circle"></i> Cotizado
					<?php } else { ?>
						<?php if ((int) $state['prehsol_stat'] == 10): ?>
								<i class="glyphicon glyphicon-remove"></i> rechazado
						<?php elseif ((int) $state['prehsol_stat'] == 20): ?>
								<i class="glyphicon glyphicon-arrow-left"></i> Devuelto
						<?php else: ?>
								<i class="glyphicon glyphicon-ok-circle"></i> Aprobado
						<?php endif; ?>
					<?php } ?>


				</span>
				<div><?php echo $state['prehsol_devolver'] ?></div>
			</div>
		<?php endforeach;*/ ?>

		<?php
		if ($infohsol[0]["prehsol_estado"] == 4 || $infohsol[0]["prehsol_estado"] == 5): ?>
			<div class="row traza">
				<!-- Paso 1: Usuario solicitante -->
				<?php if ($infohsol[0]["prehsol_estado"] == 0): ?>
					<div class="col-sm-2 traza-item pause">
						<p>
							SOLICITANTE
						</p>
						<label>
							<i class="glyphicon glyphicon-user"></i>
							<?php echo $infohsol[0]['prehsol_usuario'] ?>
						</label>
						<span>
							<i class="glyphicon glyphicon-time"></i>
							<?php echo date('d-m-Y H:i a', strtotime($infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora'])) ?></span>
						<span class="info-status">

							<i class="glyphicon glyphicon-ok-circle"></i> Solicitud creada

						</span>
					</div>
				<?php else: ?>
					<div class="col-sm-2 traza-item ok">
						<p>
							SOLICITANTE
						</p>
						<label>
							<i class="glyphicon glyphicon-user"></i>
							<?php echo $infohsol[0]['prehsol_usuario'] ?>
						</label>
						<span>
							<i class="glyphicon glyphicon-time"></i>
							<?php echo date('d-m-Y H:i a', strtotime($infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora'])) ?></span>
						<span class="info-status">

							<i class="glyphicon glyphicon-ok-circle"></i> Solicitud creada

						</span>
					</div>
				<?php endif; ?>
				<!-- Paso 2: Analista de Compras -->
				<div class="col-sm-2 traza-item <?php echo ($infohsol[0]["prehsol_estado"] == 5 ? "pause" : ""); ?>">
					<p>
						EN COTIZACIÓN
					</p>
					<label>
						<i class="glyphicon glyphicon-user"></i>
						ANALISTA DE COMPRAS
					</label>
					<span class="info-status">

						<i class="glyphicon glyphicon-ok-circle"></i> Pendiente

					</span>
				</div>
				<!-- Paso 3: Aprobación jefe de CC -->
				<?php if (!empty($flujo_aproba)): ?>
					<div class="col-sm-2 traza-item paso3">
						<p>
							APROBADOR DE CC
						</p>
						<label>
							<i class="glyphicon glyphicon-user"></i>
							<?php echo $flujo_aproba["N3"]->usr_usuario; ?>
						</label>
						<span class="info-status">

							<i class="glyphicon glyphicon-ok-circle"></i> Pendiente

						</span>
					</div>
				<?php endif; ?>
				<!-- Paso 4: Aprobación Categoría -->
				<?php if (!empty($flujo_aproba) && !empty($flujo_aproba["N4"])): ?>
					<div class="col-sm-2 traza-item">
						<p>
							APROBADOR DE CATEGORIA
						</p>
						<label>
							<i class="glyphicon glyphicon-user"></i>
							<?php echo $flujo_aproba["N4"]->usr_usuario; ?></br>
							<?php echo "<i class='glyphicon glyphicon-inbox'></i> " . strtoupper($infohsol[0]['desc_cat']); ?>
						</label>
						<span class="info-status">

							<i class="glyphicon glyphicon-ok-circle"></i> Pendiente

						</span>
					</div>
				<?php else: ?>
					<div class="col-sm-2 traza-item">
						<p>
							APROBADOR DE CATEGORIA
						</p>
						<label>
							<i class="glyphicon glyphicon-user"></i>
							PENDIENTE
						</label>

					</div>

				<?php endif; ?>
				<!-- Paso 5: Analista de Compras OC -->
				<div class="col-sm-2 traza-item">
					<p>
						EN ORDEN DE COMPRA
					</p>
					<label>
						<i class="glyphicon glyphicon-user"></i>
						ANALISTA DE COMPRAS
					</label>
					<span class="info-status">

						<i class="glyphicon glyphicon-ok-circle"></i> Pendiente

					</span>
				</div>
			</div>
		<?php endif;
		?>

		<!--<div class="col-sm-2 traza-item pause">
			<p>Aprobado Impresión.</p>
			<label>APROBADO IMPRESION</label>
			<span></span>
			<i class="glyphicon glyphicon-time"></i>
		</div>-->

	</div>

	<div class="row well">
		<?php if ($infohsol[0]['prehsol_nuevo_gestionado'] == 1) { ?>
			<form class="form-inline col-md-12" id="frmEnvia" name="frmEnvia" action="?c=solc&a=revisar&id=5" method="post">
				<div class="form-group">
					<label for="tipogasto">Tipo de Gasto</label>
					<select id="tipogasto" name="tipogasto" class="form-control input-sm" required>
						<option value="2">Presupuesto</option>
						<option value="3">Inventario</option>
						<option value="4">Activo Fijo</option>
					</select>
				</div>

				<?php if ($infohsol[0]['prehsol_Proveedor'] != "") { ?>

					<div class="form-group">
						<label for="proveedor">Proveedor Global</label>
						<select id="proveedor" name="proveedor" class="form-control input-sm" required
							style="min-width: 150px;">
							<?php foreach ($provs as $prov) {
								if ($prov['id_proveedor'] == $infohsol[0]['prehsol_Proveedor']) {
									?>

									<option value="<?php echo $prov['id_proveedor']; ?>" selected><?php echo $prov['prov_nombre']; ?>
									</option>
								<?php }
							} ?>
						</select>
					</div>

				<?php } else { ?>

					<div class="form-group">
						<label for="proveedor">Proveedor Global</label>
						<select id="proveedor" name="proveedor" class="form-control input-sm" required>
							<?php foreach ($provs as $prov) { ?>
								<option value="<?php echo $prov['id_proveedor']; ?>"><?php echo $prov['prov_nombre']; ?></option>
							<?php } ?>
						</select>
					</div>

				<?php } ?>

				<div class="btn-group btn-group-xs" role="group">
					<input type="hidden" id="pe" name="pe" value="<?php echo $infohsol[0]['prehsol_numero']; ?>">
					<input type="hidden" id="ps" name="ps" value="<?php echo $infohsol[0]['id_prehsol']; ?>">
					<input type="hidden" id="cs" name="cs" value="<?php echo $infohsol[0]['id_cc']; ?>">
					<input type="hidden" id="es" name="es" value="<?php echo $infohsol[0]['id_empresa']; ?>">
					<button type="submit" class="btn btn-xs btn-success"><i
							class="glyphicon glyphicon-shopping-cart"></i>&nbsp;Comprar</button>
					<a id="btnRechaza" name="btnRechaza" class="btn btn-xs btn-danger"
						href="?c=solc&a=deny&id=5&ps=<?php echo $infohsol[0]['id_prehsol']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
						<i class="glyphicon glyphicon-ban-circle"></i>Desistir
					</a>
					<a class="btn btn-xs btn-default"
						href="?c=solc&a=gestor&id=5&ps=<?php echo $infohsol[0]['id_prehsol']; ?>">
						<i class="glyphicon glyphicon-log-out"></i>Regresar
					</a>
				</div>
			</form>
		<?php } else { ?>
			<?php if ($infohsol[0]['prehsol_nuevo_gestion'] == 0 && count($detas) > 0 && $infohsol[0]['prehsol_nuevo_gestionado'] == 0) { ?>
				<form class="form-horizontal" id="frmAuto2" name="frmAuto2">
					<div class="form-group">
						<?php if (!empty($infohsol[0]['id_categoria']) && !is_null($infohsol[0]['id_categoria'])) { ?>
							<label for="categoria" class="col-sm-3 control-label">Categoria</label>
							<div class="col-sm-9">
								<input type="text" class="form-control" id="cat" name="cat"
									value="<?php echo $infohsol[0]['desc_cat'] . " (" . $infohsol[0]['usuario_cat'] . ")"; ?>"
									readonly>
								<input type="hidden" class="form-control" id="categoria" name="categoria"
									value="<?php echo $infohsol[0]['id_categoria']; ?>">
							</div>
						<?php } else { ?>
							<label for="categoria" class="col-sm-3 control-label">Seleccione Categoria</label>
							<div class="col-sm-9">
								<select class="form-control" id="categoria" name="categoria" placeholder="Categoria" required
									onchange="MostrarDivMetodos()">
									<option value=""></option>
									<option value="0">Sin Categoria</option>
									<?php
									foreach ($cates as $cat) {
										if ($cat['estado'] == '1') {
											echo '<option value="' . $cat['id_categoria'] . '">' . $cat['nombre_categoria'] . '</option>';
										}
									}
									?>
								</select>
							</div>
						<?php } ?>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label" for="predsol_coti4">
							Adjunto (cuadro comparativo)
						</label>
						<div class="col-sm-5">
							<input type="file" id="predsol_coti4" class="upload-file" data-campoid="prehsol_coti4"
								name="predsol_coti4" accept=".pdf,.xlsx,.doc,.docx" />
						</div>
						<div class="col-sm-3 prehsol_coti4">
							<?php if ($infohsol[0]["prehsol_coti4"] !== null && is_file($infohsol[0]["prehsol_coti4"])): ?>
								<i class='glyphicon glyphicon-ok-sign'></i>
								<span></span> <?php echo basename($infohsol[0]["prehsol_coti4"]); ?>
							<?php endif; ?>
						</div>
					</div>

					<div class="form-group">
						<label for="obs_cate" class="col-sm-3 control-label">Digite Observacion</label>
						<div class="col-sm-9">
							<textarea class="form-control" id="obs_cate" name="obs_cate" placeholder="Observacion"
								required></textarea>
						</div>
					</div>

					<div id="MetodoPago">

						<div class="form-group">
							<label for="input_monto" class="col-sm-3 control-label">Monto</label>
							<div class="col-sm-2">
								<select name="input_moneda" id="input_moneda" class="form-control">
									<option value="$">$</option>
									<?php
									if ($Empressa == 6 || $Empressa == 8) {
										echo "<option value='" . $NewMoneda . "'>" . $NewMoneda . "</option>";
									}
									?>

								</select>
							</div>
							<div class="col-sm-3">
								<input type="text" name="input_monto" id="input_monto" class="form-control" required
									maxlength="9">
							</div>
						</div>

						<div class="form-group">
							<label for="input_proveedor" class="col-sm-3 control-label">Proveedor</label>
							<div class="col-sm-9">
								<select id="input_proveedor" name="input_proveedor" class="form-control" required>
									<?php foreach ($provs as $prov) { ?>
										<option value="<?php echo $prov['id_proveedor']; ?>"><?php echo $prov['prov_nombre']; ?>
										</option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label for="input_metodo" class="col-sm-3 control-label">Método de pago</label>
							<div class="col-sm-9">
								<select id="input_metodo" name="input_metodo" class="form-control" required>
									<?php foreach ($metodos as $metodo) { ?>
										<option value="<?php echo $metodo[0]; ?>"><?php echo $metodo[1]; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>

					</div>


					<div class="col-sm-offset-3 col-sm-9">
						<a id="btnAutoriza" name="btnAutoriza" class="btn btn-lg btn-primary">
							<i class="glyphicon glyphicon-check"></i> SOLICITAR AUTORIZACION
						</a>
					</div>
				</form>
			<?php } ?>
		<?php } ?>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><strong>Detalle de solicitud</strong></h3>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="tablaPresol" class="table table-condensed">
							<thead>
								<tr>
									<td class="text-center"><strong>Cant.<br>Solicitada</strong></td>
									<td><strong>Item</strong></td>
									<td><strong>Descripcion</strong></td>
									<td><strong>Observacion</strong></td>
									<td class="text-center"><strong>Precio<br>Unitario</strong></td>
									<td class="text-center"><strong>Total</strong></td>
									<td class="text-center"><strong>C.C.</strong></td>
									<td class="text-center"><strong>Proveedor por Item</strong></td>
									<td class="text-center"><i class="glyphicon glyphicon-cog"></i></td>
								</tr>
							</thead>
							<tbody>
								<?php
								$sumas = 0;
								foreach ($detas as $deta) {
									$sumas = ($sumas + $deta['predsol_total']);
									?>
									<tr id="<?php echo $deta[0]; ?>">
										<td class="text-center"><?php echo $deta[4]; ?></td>
										<td>
											<span id="ok_auth"></span>
											<?php if (!empty($deta['item'])) {
												echo $deta['cat'] . "<br/>" . $deta['item'];
											} else {
												echo $deta['prod_codigo'];
											} ?>
										</td>
										<td><?php echo $deta[11]; ?></td>
										<td><?php echo $deta['predsol_observacion']; ?></td>
										<td class="text-center"><?php echo number_format($deta['predsol_prec_uni'], 2); ?>
										</td>
										<td class="text-center"><?php echo number_format($deta['predsol_total'], 2); ?></td>
										<td class="text-center"><?php echo $ccs[$deta["id_cc"]]["cc_descripcion"]; ?></td>
										<td class="text-center"><?php echo $prs[$deta["id_proveedor"]]["prov_nombre"]; ?>
										</td>
										<?php if ($infohsol[0]['prehsol_nuevo_gestionado'] == 1 || ($infohsol[0]['id_categoria'] != 0 && $infohsol[0]['prehsol_estado'] == 5)) { ?>
											<td class="text-center">
												<a data-toggle="tooltip" data-placement="left" title="Trabajar item"
													href="?c=solc&a=trabajoi&id=5&pd=<?php echo $deta[0]; ?>" id="delItem"
													name="delItem" class="btn btn-default"><i
														class="glyphicon glyphicon-edit"></i></a>
											</td>
										<?php } ?>
									</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="6">Total</th>
									<th class="text-center"><?php echo number_format($sumas, 2); ?></th>
									<th>&nbsp;</th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
<input type="hidden" value="<?php echo $infohsol[0]['id_prehsol']; ?>" id="id_prehsol" name="id_prehsol">
<script>


	(function ($) {
		$.fn.queued = function () {
			var self = this;
			var func = arguments[0];
			var args = [].slice.call(arguments, 1);
			return this.queue(function () {
				$.fn[func].apply(self, args).dequeue();
			});
		};
	}(jQuery));



	function MostrarDivMetodos() {
		if ($("#categoria").val() > 0) {
			$("#MetodoPago").show();
		} else {
			$("#MetodoPago").hide();
		}
	}

	$("#MetodoPago").hide();

	/*if($("#categoria").val() > 0){
			$("#MetodoPago").show();
	}else{
		$("#MetodoPago").hide();
	}*/

	$('#input_monto').val('<?php echo $sumas; ?>');

	function Match(String, Patron) {
		console.log(String);
		console.log(Patron);

		var Respuesta = String.match(Patron);
		console.log(Respuesta);
		if (Respuesta == null) return false;

		Respuesta = Respuesta[0];
		console.log(String);
		console.log(Respuesta);

		return (String == Respuesta && String.length == Respuesta.length) ? true : false;
	}

	<?php if ($infohsol[0]['prehsol_Proveedor'] != "") { ?>
		$(document).ready(function () {
			$("#proveedor").val(<?php echo $infohsol[0]['prehsol_Proveedor'] ?>);
		});
	<?php } ?>

	// Configuración global
	const MAX_SIZE = 4 * 1024 * 1024; // 4MB
	const ALLOWED_TYPES = [
		'application/pdf',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
		'application/msword', // doc
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' // docx
	];
	const ALLOWED_EXTENSIONS = ['pdf', 'xlsx', 'doc', 'docx'];

	// Función para validar archivo
	function isValidFile(file) {
		if (file.size > MAX_SIZE) {
			alert('El archivo excede el tamaño máximo de 4MB.');
			return false;
		}
		const extension = file.name.split('.').pop().toLowerCase();
		const tipoValido = ALLOWED_TYPES.includes(file.type);
		const extensionValida = ALLOWED_EXTENSIONS.includes(extension);
		if (!tipoValido && !extensionValida) {
			alert('Tipo de archivo no permitido. Solo PDF, XLSX y Word.');
			return false;
		}
		return true;
	}

	// Función para subir archivo
	function uploadFile(file, campoId) {
		const formData = new FormData();
		formData.append('archivo', file);
		formData.append('id', '<?php echo $infohsol[0]['id_prehsol']; ?>');

		const url = `json.php?c=solc&a=json_upload&campo=${campoId}`;

		fetch(url, {
			method: 'POST',
			body: formData
		})
			.then(response => response.json())
			.then(data => {
				if (data.ok) {
					//alert('Archivo subido correctamente.');
					$('.' + campoId).html(
						"<i class='glyphicon glyphicon-ok-sign'></i> " +
						"<span>" + '<?php echo $infohsol[0]['id_prehsol']; ?>-' + file.name + "</span>"
					);
					// Opcional: limpiar el input
				} else {
					alert('Error: ' + (data.msg || 'Error desconocido'));
				}
			})
			.catch(error => {
				console.error('Error en la petición:', error);
				alert('Error de conexión con el servidor.');
			});
	}

	// Asignar evento a todos los inputs con clase 'upload-file'
	document.querySelectorAll('.upload-file').forEach(input => {
		input.addEventListener('change', function (e) {
			const file = e.target.files[0];
			if (!file) return;

			if (!isValidFile(file)) {
				e.target.value = ''; // Limpiar input
				return;
			}

			const campoId = this.dataset.campoid; // Obtener de data-campoid
			uploadFile(file, campoId);
		});
	});

</script>