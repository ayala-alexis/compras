<?php
//print_r($detas); 
//print_r($infohsol);
?>
<link rel="stylesheet" type="text/css" href="css/style2.css?v=<?php echo date('His') ?>">
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

		/*$('#frmAuto').submit(function(e){
			$('body').removeClass('loaded');
			return;
			e.preventDefault();
		});*/

		$('#frmAuto').validate({
			rules: {
				prehsol_categoria: {
					required: true
				},
				observa_sol: {
					required: true
				}
			},
			submitHandler: function (form) {
				var items = ($('table#tablaPresol > tbody tr:last').index() + 1);
				if (items <= 0) {
					notie.alert('error', 'la solicitud esta vacia!', 3);
				} else {
					if ($('[name=prehsol_categoria]').val() === "") {
						notie.alert('warning', 'Debe seleccionar una categoria!', 3);
						return;
					}
					if ($('#observa_sol').val().trim().length == 0) {
						notie.alert('error', 'debe digitar una observacion!', 3);
					} else {
						$('body').removeClass('loaded');
						form.submit();
					}
				}
			}
		});

		$.validator.setDefaults({
			ignore: ''
		});

		// Variable to store your files
		var files;

		// Add events
		$('input[type=file]').on('change', prepareUpload);

		// Grab the files and set them to our variable
		function prepareUpload(event) {
			files = event.target.files;
		}


		$('#btnAutoriza').live('click', function () {
			var $btn = $(this);
			var $btn2 = $('#addItem');
			var $btn3 = $('#btnRechaza');
			jConfirm('esta seguro de aprobar?', 'aprobar solicitud', function (answer) {
				console.log(answer);
				if (answer) {
					var campos = new Object();
					rtn = 1;
					campos['prehsol_numero'] = GetURLParameter('ps');
					campos['empresa'] = GetURLParameter('es');
					campos['centrocosto'] = GetURLParameter('cs');
					campos['id_prehsol'] = $('#id_prehsol').val();
					campos['tabla'] = 'prehsol';
					campos['accion'] = 'autoriza';
					campos['categoria'] = $('#categoria').val();
					campos['aprueba_sol'] = $.trim($('#aprueba_sol').val());
					var items = ($('table#tablaPresol > tbody tr:last').index() + 1);
					//alert(campos["categoria"]);
					if (items <= 0) {
						notie.alert('error', 'la solicitud esta vacia!', 3);
					} else {
						if ($('#categoria').val().trim().length == 0) {
							notie.alert('error', 'seleccione una categoria valida!', 3);
						} else {
							if ($('#aprueba_sol').val().trim().length == 0) {
								notie.alert('error', 'debe digitar una observacion para aprobar!', 3);
							} else {
								// Enviamos a borrar de la tabla
								myUrl = 'class/Formulario.php';
								//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
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
											$btn2.addClass('disabled');
											$btn2.attr('disabled', 'disabled');
											$btn2.prop("disabled", true);
											$btn3.addClass('disabled');
											$btn3.attr('disabled', 'disabled');
											$btn3.prop("disabled", true);
										},
										success: function (data) {
											if (!$.isNumeric(data)) {
												notie.alert('error', 'la pre-solicitud ha sufrido cambios', 3);
											} else {
												if (data == 1) {
													notie.alert('success', 'ha ocurrido un error', 3);
													$('input').removeClass('disabled').prop('disabled', false);
												} else {
													//$('strong').text('AUTORIZADO');
													$('[id=ok_auth]').append('<i class="glyphicon glyphicon-ok"></i>');
													$btn2.remove();
													$btn.remove();
													$btn3.remove();
													$("[id=delItem]").remove();
													var $progress = $('.progress');
													var $progressBar = $('.progress-bar');
													var $alert = $('.alert');
													//$progress.css('display', 'block');

													$('body').removeClass('loaded');

													//$_REQUEST['ps']
													myUrl = 'class/PHPMailer/send1.php?' + $('#id_prehsol').val();
													window.location.href = myUrl;

													/*setTimeout(function() {
														$progressBar.css('width', '10%');
														setTimeout(function() {
															$progressBar.css('width', '30%');
															setTimeout(function() {
																$progressBar.css('width', '100%');
																setTimeout(function() {
																	$progress.css('display', 'none');
																	$alert.css('display', 'block');
																}, 500); // WAIT 5 milliseconds
															}, 2000); // WAIT 2 seconds
														}, 1000); // WAIT 1 seconds
													}, 1000); // WAIT 1 second*/
													//jAlert("ha sido autorizada con exito!","exito");
												}
											}
										},
										error: function (XMLHttpRequest, textStatus, errorThrown) {
											notie.alert('error', 'ha ocurrido un error!<br>' + textStatus, 3);
											$btn.removeClass('disabled');
											$btn.removeAttr('disabled');
											$btn.removeProp("disabled");
											$btn2.removeClass('disabled');
											$btn2.removeAttr('disabled');
											$btn2.removeProp("disabled");
											$btn3.removeClass('disabled');
											$btn3.removeAttr('disabled');
											$btn3.removeProp("disabled");
											$('input').removeClass('disabled').prop('disabled', false);
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
		/*
		 * Adicionar un item
		 */
		$("#frmAddItem").validate({
			rules: {
				predsol_cantidad: {
					required: true
				},
				predsol_unidad: {
					required: true
				}
			},
			submitHandler: function (form) {
				var isVisible = $('#btnAutoriza').is(':visible');
				var isHidden = $('#btnAutoriza').is(':hidden');
				var isExist = $('#btnAutoriza').length;
				var isAutoriza = <?php echo $permisos[0]['acc_aut']; ?>;
				var campos = xajax.getFormValues("frmAddItem");
				campos['prehsol_numero'] = GetURLParameter('ps');
				campos['id_empresa'] = GetURLParameter('es');
				campos['id_cc'] = GetURLParameter('cs');

				var cat = $.trim(campos['producto_categoria']);
				var prod = $.trim(campos['predsol_producto']);
				if (cat === '' || cat === '0') {
					notie.alert('warning', 'No se ha seleccionado categoría', 2);
					return false;
				}
				if (cat !== '') {
					if (prod == '') {
						notie.alert('warning', 'No se ha seleccionado un producto', 2);
						return false;
					}
				}

				var descripcion = $.trim(campos['predsol_descripcion']);
				if (descripcion === '' && prod === '') {
					notie.alert('warning', 'Digitar descripción', 2);
				} else {
					//revisa(formData);
					form.submit();
					//form.reset();
					$("#divAddItem").modal('hide');
					if (isExist >= 1 && isHidden && isAutoriza == 1) {
						$('#btnAutoriza').show('slow');
					}
					if (isExist <= 0 && isAutoriza == 1) {
						$('#botonera').append('<a id="btnAutoriza" name="btnAutoriza" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-ok"></i> Autorizar</a>').show('slow');
					}
				}
			}
		});
		$('a').tooltip();
		/*
		 * Adiciona item
		 */
		$('#addItem').unbind('click').bind('click', function () {
			$("#divAddItem").modal({
				backdrop: true,
				keyboard: false
			});
		});
		/*
		 * Borrar fila
		 */
		$("#delItem").live('click', function () {
			var atras = $(this).closest("tr");
			//alert(atras.find('td').eq(0).text());
			var campos = new Object();
			campos['prehsol_numero'] = GetURLParameter('ps');
			campos['id_empresa'] = GetURLParameter('es');
			campos['id_cc'] = GetURLParameter('cs');
			campos['id_predsol'] = atras.attr("id");
			campos['tabla'] = 'predsol';
			campos['accion'] = 'delete';
			// Enviamos a borra de la tabla
			myUrl = "class/Formulario.php"; //http://localhost/sics/api/prodcutos/";
			//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
			$.ajax({
				type: 'POST',
				url: myUrl,
				data: {
					form: campos
				},
				success: function (data) {
					if (!$.isNumeric(data)) {
						notie.alert('warning', 'la pre-solicitud ha sufrido cambios!<br>' + data, 3);
					} else {
						if (data == 1) {
							notie.alert('warning', 'ha ocurrido un error!<br>' + data, 3);
						} else {
							atras.find('td').fadeOut('slow', function () {
								// Nos borramos la linea
								atras.remove();
							});
							var cantidades = -1;
							$("#tablaPresol>tbody tr").each(function (i) {
								cantidades += 1;
							});
							// Totales en los pies
							$("#tablaPresol>tfoot>tr").each(function (i) {
								$(this).find('th').eq(1).text(cantidades);
							});
							if (cantidades <= 0) {
								$('#btnAutoriza').hide('slow');
							}
						}
					}
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					console.log(XMLHttpRequest);
					console.log(textStatus);
					console.log(errorThrown);
					notie.alert('warning', 'la pre-solicitud ha sufrido cambios!<br>' + textStatus, 3);
				}
			});
			return false; // prevents default behavior
		});
		/*
		 * Recorre tabla
		 */
		function revisa(campos) {
			var descripcion = $.trim(campos['predsol_descripcion']).toUpperCase();
			// Enviamos a guardar
			//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
			myUrl = "class/Formulario.php";
			$.ajax({
				type: 'POST',
				url: myUrl,
				data: {
					form: campos
				},
				beforeSend: function () {
					$.pnotify({
						text: 'adicionando item....',
						hide: true
					});
				},
				success: function (data) {
					if (!$.isNumeric(data)) {
						jAlert(data, 'la pre-solicitud ha sufrido cambios');
					} else {
						if (data == 1) {
							jAlert(data, 'error');
						} else {
							var tds = '<tr id="' + data + '">';
							tds += '<td>' + descripcion + '</td>';
							tds += '<td style="text-align: center">' + campos['predsol_unidad'] + "</td>";
							tds += '<td style="text-align: center">' + campos['predsol_cantidad'] + "</td>";
							tds += '<td></td>';
							tds += '<td class="text-center"><button id="delItem" name="delItem" class="close">&times;</button></td>';
							tds += '</tr>';
							$(tds).hide().appendTo("#tablaPresol>tbody").fadeIn(500).css('display', '');
							// Cantidad de items
							var cantidades = 0;
							$("#tablaPresol>tbody tr").each(function (i) {
								cantidades += 1;
							});
							// Totales en los pies
							$("#tablaPresol>tfoot>tr").each(function (i) {
								//cantidades += 1;
								$(this).find('th').eq(1).text(cantidades);
							});
						}
					}
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: 'ha ocurrido un error..',
						text: 'durante la adicion ocurrio lo sigueinte :' + textStatus,
						type: 'error',
						icon: 'icon-alert-sign',
						hide: true,
						addclass: "stack-bar-top",
						cornerclass: "",
						width: "100%",
						stack: stack_bar_top
					});
				}
			});
		};
		/*
		*
		*/
		$('#saveItem').click(function () {
			saveItem('0');
		});
		var inp_producto = $('[name=predsol_producto]');
		$('[name=predsol_tipo]').change(function () {
			get_productos($(this).val());
		});

		//Carga inicial
		get_productos($('[name=predsol_tipo]').val());

		let inp_categoria = $('[name=prehsol_categoria]');
		let inp_categoria_prod = $('[name=producto_categoria]');
		inp_categoria.change(function () {
			$('#addItem').hide();
			inp_categoria_prod.val('0');
			if ($(this).val() !== '') {
				$('#addItem').show();
				inp_categoria_prod.val($(this).val());
			}
		});

		<?php if (!empty($infohsol[0]['id_categoria']) && $infohsol[0]['id_categoria'] !== null): ?>
			inp_categoria.val('<?php echo $infohsol[0]['id_categoria'] ?>');
			inp_categoria.trigger('change');
			<?php if (!empty($detas) && count($detas) > 0): ?>
				inp_categoria.attr('disabled', 'disabled');
			<?php endif; ?>
		<?php endif; ?>

		/*
		*
		*/
		function get_productos(str) {
			if (str === "" || str === 0) return;
			var categoria = str;
			if (categoria !== '') {
				var request = request_json_id({
					id: categoria,
					action: 'json.php?c=solc&a=json_productos_categoria',
					method: 'POST'
				});
				if (request !== undefined) {
					inp_producto.html('');
					inp_producto.append("<option value=''>--Seleccionar--</option>");
					$.each(request.productos, function (i, item) {
						is_cate_ = 1;
						inp_producto.append("<option value='" + item.id + "'>" + item.descripcion + "</option>");
					});
					//$('.viewn3').show();
				} else {
					inp_producto.append("<option value=''>--Seleccionar--</option>");
				}
			} else {
				inp_producto.html('');
				inp_producto.append("<option value=''>--Seleccionar--</option>");
			}
			inp_producto.val('');
		}
		function saveItem(rtn) {
			// Procesamos
			rtn = 0;
			campos = xajax.getFormValues("frmAddForm");
			campos['prehsol_numero'] = GetURLParameter('ps');
			campos['id_empresa'] = GetURLParameter('es');
			campos['id_cc'] = GetURLParameter('cs');
			// Enviamos a guardar
			myUrl = "class/Formulario.php";
			//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
			$.ajax({
				type: 'POST',
				url: myUrl,
				data: {
					form: campos
				},
				beforeSend: function () {
					$("#saveItem").addClass("disabled");
					$('#saveItem').after('<img src="images/FhHRx.gif"></img>');
					$.pnotify({
						text: 'adicionando item....',
						hide: true
					});
					$('input').addClass('disabled').prop('disabled', true);
				},
				success: function (data) {
					if (!$.isNumeric(data)) {
						jAlert(data, 'la pre-solicitud ha sufrido cambios');
					} else {
						if (data == 1) {
							jAlert(data, 'error');
						} else {
							jAlert('Guardado con exito.');
						}
					}
					$("#saveItem").removeClass("disabled");
					$('input').removeClass('disabled').prop('disabled', false);
					rtn = 1;
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: 'ha ocurrido un error..',
						text: 'durante la adicion ocurrio lo sigueinte :' + textStatus + XMLHttpRequest + errorThrown,
						type: 'error',
						icon: 'icon-alert-sign',
						hide: true,
						addclass: "stack-bar-top",
						cornerclass: "",
						width: "100%",
						stack: stack_bar_top
					});
					$("#saveItem").removeClass("disabled");
					$('input').removeClass('disabled').prop('disabled', false);
				}
			}).done(function (response, textStatus, jqXHR) {
				$('#saveItem').nextAll('img').remove();
				$('input').removeClass('disabled').prop('disabled', false);
			});
		}
	});
</script>
<?php
// echo "<pre>Categoria: ";
// print_r($infohsol[0]['id_categoria']);
// echo "</pre>";
?>
<div class="container-fluid">
	<!-- Start Page Loading -->
	<div id="loader-wrapper">
		<h1>Espere, completando accion...</h1>
		<div id="loader">
		</div>
		<div class="loader-section section-left"></div>
		<div class="loader-section section-right"></div>
	</div>
	<!-- End Page Loading -->


	<div class="row">
		<div class="col-xs-12">
			<div class="invoice-title">
				<a class="btn btn-default"
					href="?c=menu&a=<?php echo (isset($_GET['return']) ? $_GET['return'] : 'index') ?>">
					<i class="glyphicon glyphicon-arrow-left"></i>
					Regresar
				</a>
				&nbsp; &nbsp; &nbsp;
				<h3>Gestion de Compra</h3>
				<h3 class="pull-right">Solicitud # <?php echo $_GET['ps']; ?></h3>
			</div>
			<hr>
			<div class="row">
				<div class="col-xs-6">
					<address>
						<strong>Solicitado por:</strong><br>
						<?php echo $infohsol[0]['emp_nombre']; ?><br>
						<?php echo $infohsol[0]['cc_descripcion']; ?><br />
						<?php
						if (isset($infohsol[0][7])) {
							echo "<i class='glyphicon glyphicon-user'></i> " . $infohsol[0][7];
						}
						?>
					</address>
					<?php if (!empty($infohsol[0]['desc_cat']) && !is_null($infohsol[0]['desc_cat'])) { ?>
						<address>
							<strong>Categoría:</strong>
							<?php
							echo "<i class='glyphicon glyphicon-inbox'></i> " . $infohsol[0]['desc_cat'] . " (<i class='glyphicon glyphicon-user'></i> " . $infohsol[0]['usuario_cat'] . ")";
							?>
						</address>
					<?php } ?>
				</div>



				<div class="col-xs-6 text-right">
					<address>
						<strong>Fecha :</strong><br>
						<?php
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
						// Resultado: "lunes 15 de enero, 2024 02:30:45 p. m."
						
						//echo iconv('ISO-8859-1', 'UTF-8', strftime('%A %d de %B, %Y %I:%M:%S %p', strtotime($infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora'])));
						echo '<br>';
						echo $infohsol[0]['prehsol_obs1'];
						?>
					</address>
				</div>
			</div>

			<?php

			

			$is_trazabilidad = ($infohsol_stat != null ? (count($infohsol_stat) > 0 ? $infohsol_stat[0] != null : false) : false);
			if ($is_trazabilidad && FALSE): ?>
				<div class="row traza">
					<h3 class="title_traza">
						<i class="glyphicon glyphicon-check"></i>
						Trazabilidad de solicitud
					</h3>
					<?php
					$estado = 0;
					foreach ($infohsol_stat as $state):
						$estado++;
						?>
						<div class="col-sm-2 traza-item <?php echo (((int) $state['prehsol_stat'] == 10) ? 'stop' : 'ok') ?>">
							<p>
								<?php echo get_status($state['prehsol_stat'], $state['prehsol_stat_desc']) ?><br />
							</p>
							<label>
								<i class="glyphicon glyphicon-user"></i>
								<?php echo $state['prehsol_stat_usuario'] ?>
							</label>
							<span><?php echo $state['prehsol_stat_fecha'] ?> 		<?php echo $state['prehsol_stat_hora'] ?></span>
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
						</div>
					<?php endforeach; ?>

					<!--<div class="col-sm-2 traza-item pause">
			<p>Aprobado Impresión.</p>
			<label>APROBADO IMPRESION</label>
			<span></span>
			<i class="glyphicon glyphicon-time"></i>
		</div>-->

				</div>
				<?php
			elseif ($infohsol[0]["prehsol_estado"] == 0 || $infohsol[0]["prehsol_estado"] == 5 || $infohsol[0]["prehsol_estado"] == 1): ?>
				<div class="row traza">
					<h3 class="title_traza">
						<i class="glyphicon glyphicon-check"></i>
						Trazabilidad de solicitud
					</h3>
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
					<?php if ($infohsol[0]["prehsol_estado"] == 4): ?>
						<div class="col-sm-2 traza-item pause">
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
					<?php elseif ($infohsol[0]["prehsol_estado"] == 1): ?>
						<div class="col-sm-2 traza-item ok">
							<p>
								EN COTIZACIÓN
							</p>
							<label>
								<i class="glyphicon glyphicon-user"></i>
								<?php echo $infohsol_stat[1]['prehsol_stat_usuario']; ?>
							</label>
							<span>
								<i class="glyphicon glyphicon-time"></i>
								<?php echo date('d-m-Y H:i a', strtotime($infohsol_stat[1]['prehsol_stat_fecha'] . ' ' . $infohsol_stat[1]['prehsol_stat_hora'])) ?>
							</span>
							<span class="info-status">

								<i class="glyphicon glyphicon-ok-circle"></i> Completo

							</span>
						</div>
					<?php endif; ?>
					<!-- Paso 3: Aprobación jefe de CC -->
					<?php if (!empty($flujo_aproba)): ?>
						<?php if ($infohsol[0]["prehsol_estado"] == 1): ?>
							<div class="col-sm-2 traza-item pause">
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

			<div class="row">
				<div class="col-xs-6">
					<address>

						<?php if ($infohsol[0]['prehsol_estado'] == 1 || $infohsol[0]['prehsol_estado'] == 0) { ?>

						<?php } else { ?>
							<span
								class="text-danger pull-right"><?php echo $conf->getEstadoSC($infohsol[0]['prehsol_estado']); ?></span>
							<?php if ($infohsol[0]['prehsol_estado'] == 3) { ?>
								<a id="btnGestiona" name="btnGestiona" class="btn btn-sm btn-success"
									href="?c=solc&a=autoges&id=5&ps=<?php echo $infohsol[0]['id_prehsol']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
									<i class="glyphicon glyphicon-send"></i> APROBAR GESTION
								</a>
							<?php } ?>
						<?php } ?>
						<br>
					</address>
				</div>
				<div class="col-xs-6 text-right">
					<address>
						<?php if ($infohsol[0]['prehsol_estado'] == 1 && count($detas) > 0 && $permisos[0]['acc_aut'] == '1') { ?>
							<a id="btnRechaza" name="btnRechaza" class="btn btn-sm btn-danger"
								href="?c=solc&a=deny&id=5&ps=<?php echo $infohsol[0]['id_prehsol']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
								<i class="glyphicon glyphicon-ban-circle"></i> RECHAZAR
							</a>
						<?php } ?>
						<br>
					</address>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<?php if ($infohsol[0]['prehsol_estado'] == 0) { ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title"><strong>Categoría y adjuntos</strong></h3>

					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<form class="form form-horizontal" id="frmAuto" name="frmAuto" enctype="multipart/form-data"
									method="post"
									action="?c=solc&a=auto&id=5&ps=<?php echo $infohsol[0]['id_prehsol']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
									<div class="form-group">
										<label class="col-sm-3 control-label" for="prehsol_categoria">Categoría</label>
										<div class="col-sm-6">
											<select class="form-control input-sm" id="prehsol_categoria"
												name="prehsol_categoria" required>
												<?php if (!empty($category)): ?>
													<?php if (count($category) > 1): ?>
														<option value="">-- Seleccione Categoría --</option>
													<?php endif; ?>
													<?php $grupo = ""; ?>
													<?php foreach ($category as $cat): ?>
														<?php if ($grupo != $cat->gcia): ?>
															<?php if ($grupo != ""): ?>
																</optgroup>
															<?php endif; ?>
															<optgroup label="<?php echo $cat->gcia; ?>">
																<?php $grupo = $cat->gcia; ?>
															<?php endif; ?>
															<option value="<?php echo $cat->id; ?>">
																<?php echo $cat->categoria; ?>
															</option>
														<?php endforeach; ?>
													<?php endif; ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label" for="predsol_coti1">
											Cotizacion 1
										</label>
										<div class="col-sm-5">
											<input type="file" id="predsol_coti1" class="upload-file"
												data-campoid="prehsol_coti1" name="predsol_coti1"
												accept=".pdf,.xlsx,.doc,.docx" />
										</div>
										<div class="col-sm-3 prehsol_coti1">
											<?php if ($infohsol[0]["prehsol_coti1"] !== null && is_file($infohsol[0]["prehsol_coti1"])): ?>
												<i class='glyphicon glyphicon-ok-sign'></i>
												<span></span> <?php echo basename($infohsol[0]["prehsol_coti1"]); ?>
											<?php endif; ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label" for="predsol_coti1">Cotizacion 2</label>
										<div class="col-sm-5">
											<input type="file" id="predsol_coti2" class="upload-file"
												data-campoid="prehsol_coti2" name="predsol_coti2"
												accept=".pdf,.xlsx,.doc,.docx" />
										</div>
										<div class="col-sm-3 prehsol_coti2">
											<?php if ($infohsol[0]["prehsol_coti2"] !== null && is_file($infohsol[0]["prehsol_coti2"])): ?>
												<i class='glyphicon glyphicon-ok-sign'></i>
												<span></span> <?php echo basename($infohsol[0]["prehsol_coti2"]); ?>
											<?php endif; ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label" for="predsol_coti1">Cotizacion 3</label>
										<div class="col-sm-5">
											<input type="file" id="predsol_coti3" class="upload-file"
												data-campoid="prehsol_coti3" name="predsol_coti3"
												accept=".pdf,.xlsx,.doc,.docx" />
										</div>
										<div class="col-sm-3 prehsol_coti3">
											<?php if ($infohsol[0]["prehsol_coti3"] !== null && is_file($infohsol[0]["prehsol_coti3"])): ?>
												<i class='glyphicon glyphicon-ok-sign'></i>
												<span></span> <?php echo basename($infohsol[0]["prehsol_coti3"]); ?>
											<?php endif; ?>
										</div>
									</div>
									<div class="col-sm-offset-3 col-sm-9">
										<input type="hidden" name="id_usuario" id="id_usuario"
											value="<?php echo $_SESSION['i']; ?>" />
										<input type="hidden" name="id_prehsol" id="id_prehsol"
											value="<?php echo $infohsol[0]['id_prehsol']; ?>" />
										<input type="hidden" name="id_empresa" id="id_empresa"
											value="<?php echo $infohsol[0]['id_empresa']; ?>" />
										<input type="hidden" name="id_cc" id="id_cc"
											value="<?php echo $infohsol[0]['id_cc']; ?>" />
										<input type="hidden" name="predsol_usuario" id="predsol_usuario"
											value="<?php echo $_SESSION['u']; ?>" />
										<input type="hidden" name="prehsol_numero" id="prehsol_numero"
											value="<?php echo $_GET['ps']; ?>" />
										<input type="hidden" name="id_empresa" id="id_empresa"
											value="<?php echo $_GET['es']; ?>" />
										<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $_GET['cs']; ?>" />
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<strong>Detalle de solicitud</strong>
					</h3>
					<?php if ($infohsol[0]['prehsol_estado'] == 2 || $infohsol[0]['prehsol_estado'] == 0) { ?>
						<a style="margin-top:-20px" id="addItem" name="addItem" class="btn btn-sm btn-success pull-right"
							rel="tooltip" title="agregar item a solicitud">
							<i class="glyphicon glyphicon-plus"></i>AGREGAR ITEM
						</a>
					<?php } ?>
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table id="tablaPresol" name="tablaPresol" class="table table-condensed">
							<thead>
								<tr>
									<td class="text-left"><strong>Cant.<br />Solicitada</strong></td>
									<td><strong>Item</strong></td>
									<td><strong>Descripción</strong></td>
									<td class="text-center"><em class="fa fa-cog"></em></td>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ($detas as $deta) {
									?>
									<tr id="<?php echo $deta[0]; ?>">
										<td><span id="ok_auth"></span><?php echo $deta["predsol_cantidad"]; ?></td>
										<td class="text-left">
											<?php
											if (!empty($deta["item"]) || !is_null($deta["item"])) {
												echo "<b>" . $deta["cat"] . "</b><br/>" . $deta["item"];
											}
											?>
										</td>
										<td class="text-left">
											<?php
											echo $deta["predsol_descripcion"];
											?>
										</td>
										<td class="text-right">
											<?php if ($infohsol[0]['prehsol_estado'] <= 1) { ?>
												<a href="#" id="delItem" name="delItem" class="btn btn-xs btn-danger"><i
														class="glyphicon glyphicon-remove"></i></a>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<?php if ($infohsol[0]['prehsol_estado'] == 0) { ?>
				<form class="form form-horizontal" id="frmAuto" name="frmAuto" enctype="multipart/form-data" method="post"
					action="?c=solc&a=auto&id=5&ps=<?php echo $infohsol[0]['id_prehsol']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">

					<div class="form-group">
						<label class="col-sm-3 control-label" for="observa_sol">Observaciones</label>
						<div class="col-sm-9">
							<textarea max_chars="250" class="form-control" id="observa_sol" name="observa_sol"
								required></textarea>
							<span id="character_count" class="help-block"></span>
						</div>
					</div>
					<div class="col-sm-offset-3 col-sm-9">
						<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
						<input type="hidden" name="id_prehsol" id="id_prehsol"
							value="<?php echo $infohsol[0]['id_prehsol']; ?>" />
						<input type="hidden" name="id_empresa" id="id_empresa"
							value="<?php echo $infohsol[0]['id_empresa']; ?>" />
						<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
						<input type="hidden" name="predsol_usuario" id="predsol_usuario"
							value="<?php echo $_SESSION['u']; ?>" />
						<input type="hidden" name="prehsol_numero" id="prehsol_numero" value="<?php echo $_GET['ps']; ?>" />
						<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $_GET['es']; ?>" />
						<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $_GET['cs']; ?>" />
						<button type="submit" id="btnEnviaAuth" name="btnEnviaAuth" class="btn btn-lg btn-primary">
							<i class="glyphicon glyphicon-send"></i>&nbsp;ENVIAR PARA AUTORIZACION
						</button>
					</div>
				</form>
			<?php } ?>
			<?php if ($infohsol[0]['prehsol_estado'] == 1 && count($detas) > 0 && $permisos[0]['acc_aut'] == '1') { ?>
				<form class="form-horizontal" id="frmAuto2" name="frmAuto2">
					<div class="form-group" style="display: none !important;">
						<label for="categoria" class="col-sm-3 control-label">Seleccione Categoria</label>
						<div class="col-sm-9">
							<select class="form-control" id="categoria" name="categoria" placeholder="Categoria" required>
								<option value=""></option>
								<option value="0" selected>Sin Categoria</option>
								<?php
								foreach ($cats as $cat) {
									echo '<option value="' . $cat['id_categoria'] . '">' . $cat['nombre_categoria'] . '</option>';
								}
								?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label" for="aprueba_sol">Observaciones de aprobacion</label>
						<div class="col-sm-9">
							<textarea max_chars="250" class="form-control" id="aprueba_sol" name="aprueba_sol"
								required></textarea>
							<span id="character_count" class="help-block"></span>
						</div>
					</div>
					<div class="col-sm-offset-3 col-sm-9">
						<a id="btnAutoriza" name="btnAutoriza" class="btn btn-lg btn-primary">
							<i class="glyphicon glyphicon-check"></i> APROBAR
						</a>
					</div>
				</form>
			<?php } ?>
		</div>
	</div>
</div>
<form class="form-horizontal" role="form" name="frmAddForm" id="frmAddForm" method="post" action="">
	<input type="hidden" name="tabla" id="tabla" value="predsol" />
	<input type="hidden" name="accion" id="accion" value="save" />
	<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
	<input type="hidden" name="id_prehsol" id="id_prehsol" value="<?php echo $infohsol[0]['id_prehsol']; ?>" />
	<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $infohsol[0]['id_empresa']; ?>" />
	<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
	<input type="hidden" name="predsol_usuario" id="predsol_usuario" value="<?php echo $_SESSION['u']; ?>" />
</form>
<?php
/*echo '<pre>'; 
echo $_SERVER['REQUEST_URI'].'<br>';
echo $_SERVER['PHP_SELF'].'<br>';
echo $_SERVER['HTTP_HOST'].'<br>';
echo $_SERVER["QUERY_STRING"];
//print_r($detas);
echo '</pre>'; */
?>
<input type="hidden" value="<?php echo $infohsol[0]['id_prehsol']; ?>" id="id_prehsol" name="id_prehsol">
<div class="modal fade" role="dialog" id="divAddItem" name="divAddItem">
	<div class="modal-dialog" style="min-width: auto;max-width: 800px;width: 100%;">
		<div class="modal-content">
			<form id="frmAddItem" name="frmAddItem" enctype="multipart/form-data" method="post"
				action="?<?php echo $_SERVER['QUERY_STRING']; ?>" class="form-horizontal" role="form"
				validate="validate">
				<input class="form-control input-sm" type="hidden" id="predsol_unidad" name="predsol_unidad"
					value="UNIDAD" />
				<input class="form-control input-sm" type="hidden" id="producto_categoria" name="producto_categoria"
					value="<?php echo !empty($infohsol[0]["id_categoria"]) ? $infohsol[0]["id_categoria"] : "0"; ?>" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 id="myModalLabel">Agregar items a solicitud No. <?php echo $_GET['ps']; ?></h3>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label col-md-3" for="predsol_cantidad">Cantidad</label>
						<div class="col-md-5">
							<input class="form-control input-sm" type="text" id="predsol_cantidad"
								name="predsol_cantidad">
						</div>
					</div>
					<div class="form-group" style="display:none">
						<label class="control-label col-md-3" for="predsol_tipo">Cat. Producto:</label>
						<div class="col-md-6">
							<select class="form-control input-sm" id="predsol_tipo" name="predsol_tipo">
								<?php if (!empty($cat_prod)): ?>
									<?php if (count($cat_prod) > 1): ?>
										<option value="">-- Seleccionar --</option>
									<?php endif; ?>
									<?php foreach ($cat_prod as $cat): ?>
										<option value="<?php echo $cat->id_categoria; ?>">
											<?php echo $cat->descripcion_categoria; ?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3" for="predsol_producto">Producto:</label>
						<div class="col-md-6">
							<select class="form-control input-sm" id="predsol_producto" name="predsol_producto">
								<option value="">-- Seleccionar --</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3" for="predsol_descripcion">Descripcion</label>
						<div class="col-md-8">
							<textarea style="width: 100%;" id="predsol_descripcion"
								name="predsol_descripcion"></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="tabla" id="tabla" value="predsol" />
					<input type="hidden" name="accion" id="accion" value="add" />
					<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
					<input type="hidden" name="id_prehsol" id="id_prehsol"
						value="<?php echo $infohsol[0]['id_prehsol']; ?>" />
					<input type="hidden" name="id_empresa" id="id_empresa"
						value="<?php echo $infohsol[0]['id_empresa']; ?>" />
					<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
					<input type="hidden" name="predsol_usuario" id="predsol_usuario"
						value="<?php echo $_SESSION['u']; ?>" />
					<input type="hidden" name="prehsol_numero" id="prehsol_numero" value="<?php echo $_GET['ps']; ?>" />
					<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $_GET['es']; ?>" />
					<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $_GET['cs']; ?>" />
					<button class="btn" data-dismiss="modal" aria-hidden="true"><span
							class="glyphicon glyphicon-ban-circle"></span> Cancelar</button>
					<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok-circle"></span>
						Agregar item</button>
					<input type="reset" style="display: none;">
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript" src="js/js.js?v=<?php echo date('His') ?>"></script>
<script>
	(function ($) {
		$.fn.queued = function () {
			var self = this;
			var func = arguments[0];
			var args = [].slice.call(arguments, 1);
			return this.queue(function () {
				$.fn[func].apply(self, args).dequeue();
			});
		}
	}(jQuery));

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