<script type="text/javascript">
$(document).ready(function(){
	$("#btnIngresa").attr("disabled","disabled");
	$("#btnIngresa").prop("disabled",true);
	$("#btnIngresa").addClass("disabled");
	$("#btnVolver").click(function(){
		document.location='?c=inv&a=inicio';
	});
	$("#btnIngresa").click(function(){
		// Deshabilitamos el uso del boton
		$(this).attr("disabled","disabled");
		$(this).prop("disabled",true);
		$(this).addClass("disabled");
		$("#btnVolver").attr("disabled","disabled");
		$("#btnVolver").prop("disabled",true);
		$("#btnVolver").addClass("disabled");
		var empresa_oc = $("#id_empresa_oc").val();
		var empresa = $("#id_empresa").val();
		var empresas = $("#id_empresa_oc").val()+'-'+$("#id_empresa").val();
		var oc = $("#oc").val();
		var valor = $('#val_doc').val();
		var fecha_doc = $('#fecha_doc').val();
		var tipo_doc = $('#tipo_doc').val();
		var num_doc = $('#num_doc').val();
		// Realizamos verificacion antes de ingresar
		myUrlOC = location.protocol + "//" + location.host + '/sics/json.php?c=inv&a=verificaPreOC';
		$.post(myUrlOC, { empresa: empresas, oc: oc, val: valor },
		function(json) {
			if(json == "OK"){
				// Marcamos la O.C. como INGRESADA
				myUrlOC2 = location.protocol + "//" + location.host + '/sics/json.php?c=inv&a=marcaOC';
				$.post(myUrlOC2, { 
						empresa: empresa,
						empresa_oc: empresa_oc, 
						oc: oc,
						fecha_doc: fecha_doc,
						tipo_doc: tipo_doc,
						num_doc: num_doc,
						val_doc: valor 
					},
				function(data, textStatus){
					// Si todo va bien procedemos
					if(data == 'OK') {
						var i = 0;
						$('#btnIngresa').attr('disabled','disabled');
						$("#btnIngresa").prop("disabled",true);
						$("#btnIngresa").addClass("disabled");
						// Nos vamos al detalle para procesar codigo por codigo
						try {
							elementClick = $('#detalle');
				            destination = $(elementClick).offset().top-150;
				            $("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination}, 1100 );
						}catch(err) {
							alert(err);
							return false;
						}
						// preparamos la URL donde enviaremos los datos
						myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
						//var campos = [];
						var campos = new Object();
						campos['id_empresa'] = $("#id_empresa").val();
						campos['bod_codigo'] = $("#bod_codigo").val();
						campos['inv_usuario'] = $("#usr_crea").val();
						campos['codprovee'] = $("#codprovee").val();
						campos['tabla'] = 'inventario';
						campos['accion'] = 'add';
						campos['id_empresa_oc'] = empresa_oc;
						campos['oc'] = oc;
						var campos2 = new Object();
						campos2['id_empresa'] = $("#id_empresa").val();
						campos2['bod_codigo'] = $("#bod_codigo").val();
						campos2['usuario_trans'] = $("#usr_crea").val();
						campos2['tabla'] = 'kardex';
						campos2['accion'] = 'add';
						campos2['id_empresa_oc'] = empresa_oc;
						campos2['referencia_oc'] = oc;
						// Tomamos codigo por codigo de la tabla html
						$('.btn-group').after('<span class="alert alert-warning">PROCESANDO INGRESO POR FAVOR ESPERE.</span>');
						$('#detalle tbody tr').each(function (j) {
							var that = $(this);
							/* */
							campos['cc_codigo'] = that.find('td').eq(3).text();
							campos['prod_codigo'] = that.find('td').eq(0).text();
							campos['inv_existencia'] = that.find('td').eq(1).text();
							campos['inv_costo_compra'] = that.find('td').eq(4).text();
							campos['inv_costo_total'] = that.find('td').eq(5).text();
							/* */
							campos2['cc_codigo'] = that.find('td').eq(3).text();
							campos2['prod_codigo'] = that.find('td').eq(0).text();
							campos2['cantidad'] = that.find('td').eq(1).text();
							campos2['costo'] = that.find('td').eq(4).text();
							campos2['id_prehreq'] = that.find('td').eq(6).text();
							campos2['id_prehsol'] = '0';
							campos2['tipo_trans'] = '1';
							/* */
							that.find('td').eq(0).append('<img src="images/loading.gif"></img>');
							that.find('td').eq(1).append('<img src="images/loading.gif"></img>');
							/* Comenzamos a llenar tabla INVENTARIO */
							$.ajax({
								type : "POST",
								url : myUrl,
								data: {
									form: campos
								},
								success: function (data) {
									if(!$.isNumeric(data)) {
										$.pnotify({
											title: 'ha ocurrido un error',
											text: 'campos :'+data,
											type: 'error',
											icon: "glyphicon glyphicon-ban-circle",
											hide: true,
											sticker: true
										});
									} else {
										if(data == 1){
											$.pnotify({
												title: 'ha ocurrido un error',
												text: 'campos :'+data,
												type: 'error',
												icon: "glyphicon glyphicon-ban-circle",
												hide: true,
												sticker: true
											});
										} else {
											that.find('td').eq(1).find('img').remove();
											that.find('td').eq(1).append('<i class="glyphicon glyphicon-ok"></i>').hide(0).fadeOut().fadeIn();
										}
									}
								},
								error: function(XMLHttpRequest, textStatus, errorThrown){
									$.pnotify({
										title: 'ha ocurrido un error',
										text: 'error campos :'+textStatus,
										type: 'error',
										icon: "glyphicon glyphicon-ban-circle",
										hide: true
									});
								}
							});
							/* llenamos tabla KARDEX */
							$.ajax({
								type : "POST",
								url : myUrl,
								data: {
									form: campos2
								},
								success: function (data) {
									if(!$.isNumeric(data)) {
										$.pnotify({
											title: 'ha ocurrido un error',
											text: 'campos2 :'+data,
											type: 'error',
											icon: "glyphicon glyphicon-ban-circle",
											hide: true,
											sticker: true
										});
									} else {
										if(data == 1){
											$.pnotify({
												title: 'ha ocurrido un error',
												text: 'campos2 :'+data,
												type: 'error',
												icon: "glyphicon glyphicon-ban-circle",
												hide: true,
												sticker: true
											});
										} else {
											that.find('td').eq(0).find('img').remove();
											that.find('td').eq(0).append('<i class="glyphicon glyphicon-ok"></i>').hide(0).fadeOut().fadeIn();
										}
									}
								},
								error: function(XMLHttpRequest, textStatus, errorThrown){
									$.pnotify({
										title: 'ha ocurrido un error',
										text: 'error campos2 :'+textStatus,
										type: 'error',
										icon: "glyphicon glyphicon-ban-circle",
										hide: true
									});
								}
							});
							i++;
						});
					} else {
						$.pnotify({
							title: 'ha ocurrido un error',
							text: data,
							type: 'error',
							icon: "glyphicon glyphicon-ban-circle",
							hide: true
						});
					}
				});
			} else {
				$.pnotify({
					title: 'ALGO HA PASADO',
					text: json,
					type: 'warning',
					icon: "glyphicon glyphicon-ban-circle",
					hide: false
				});
				$('#btnIngresa').attr("disabled","disabled");
				$("#btnIngresa").prop("disabled",true);
				$("#btnIngresa").addClass("disabled");
			}
		});
	});
});
</script>
<?php
list ( $empresa_oc, $empresa_sicys ) = split ( '-', $_POST ['empresa'] );
?>
<input type="hidden" id="id_empresa" name="id_empresa" value="<?php echo $empresa_sicys; ?>" />
<input type="hidden" id="id_empresa_oc" name="id_empresa_oc" value="<?php echo $empresa_oc; ?>" />
<input type="hidden" id="bod_codigo" name="bod_codigo" value="<?php echo $_POST['bodega']; ?>" />
<input type="hidden" id="oc" name="oc" value="<?php echo $_POST['oc']; ?>" />
<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
<input type="hidden" id="codprovee" name="codprovee" value="<?php echo $Det_Orden['proveedor'][0]['id_proveedor']; ?>" />
<input type="hidden" id="norden" name="norden" value="<?php echo $_POST['oc']; ?>" />
<input type="hidden" id="fecha_doc" name="fecha_doc" value="<?php echo $_POST['fecha_doc']; ?>" />
<input type="hidden" id="tipo_doc" name="tipo_doc" value="<?php echo $_POST['tipo_doc']; ?>" />
<input type="hidden" id="val_doc" name="val_doc" value="<?php echo $_POST['val_doc']; ?>" />
<input type="hidden" id="num_doc" name="num_doc" value="<?php echo $_POST['num_doc']; ?>" />
<fieldset>
	<legend>
		<strong><?php echo $_POST['nempresa']; ?></strong>
	</legend>
	<p>
		BODEGA SELECCIONADA : <strong><?php echo $_POST['nbodega']; ?></strong>
	</p>
	<img id="loading" name="loading" src="css/themes/redmond/images/loading.gif" class="pull-right" style="display: none;">
	<div class="btn-group">
		<button class="btn btn-warning" id="btnVolver" name="btnVolver">CANCELAR</button>
		<button class="btn btn-primary" id="btnIngresa" name="btnIngresa">INGRESAR</button>
	</div>
</fieldset>
<br />
<table id="encabezado1" class="table table-condensed">
	<tbody>
	<?php
	if ($empresa_oc == '02') {
	?>
		<tr>
			<td rowspan="4"><img src=images/MAGICO_.jpg></td>
			<td><b><?php echo $Det_Orden['empresa'][0]['emp_nombre']; ?></b></td>
	<?php
	} else {
	?>
		<tr>
			<td rowspan="4"><img src=images/_totem.jpg></td>
			<td><b><?php echo $Det_Orden['empresa'][0]['emp_nombre']; ?></b></td>
	<?php
	}
	?>
		<td>
			<b>ORDEN DE COMPRA No. <?php echo $_POST['oc']; ?>
	<?php
	/*
	if ($Det_Orden ['empresa'] [0] ['ESTADO'] == '*') {
		echo '**ANULADA**';
	}
	*/
	?>
			</b>
		</td>
	</tr>
	<tr>
		<td><?php echo $Det_Orden['empresa'][0]['emp_direccion']; ?></td>
		<td>Registro No. : <b><?php echo $Det_Orden['empresa'][0]['emp_registro']; ?></b></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Giro : <b><?php echo $Det_Orden['empresa'][0]['emp_giro']; ?></b></td>
	</tr>
	<tr>
		<td>Tel.: <b><?php echo $Det_Orden['empresa'][0]['emp_telefono']; ?></b></td>
		<td>Nit : <b><?php echo $Det_Orden['empresa'][0]['emp_nit']; ?></b></td>
	</tr>
</tbody>
</table>
<table id="encabezado2" class="table table-condensed">
	<tr>
		<td colspan="4">PROVEEDOR : <b><?php echo $Det_Orden['proveedor'][0]['prov_nombre']; ?></b></td>
	</tr>
	<tr>
		<td colspan="2">FECHA : <b><?php echo $_POST['fecha_doc']; ?></b></td>
		<td rowspan="2" colspan="2">FAX : <b><?php echo $Det_Orden['proveedor'][0]['prov_fax']; ?></b>&nbsp;&nbsp;&nbsp;PBX
			: <b><?php echo $Det_Orden['proveedor'][0]['prov_telefono1']; ?></b>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;USUARIO
			CREO : <b><?php echo $_SESSION['u']; ?></b>
			<br>ATENCION : <b><?php echo $Det_Orden['proveedor'][0]['prov_contacto1']; ?></b>
			&nbsp;&nbsp;&nbsp;E-MAIL : <b><?php echo $Det_Orden['proveedor'][0]['prov_email']; ?></b>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td>PEDIDO POR</td>
		<td>COTIZACION</td>
		<td>CONDICIONES DE PAGO</td>
		<td>SOLICITUD DE COMPRA</td>
	</tr>
	<tr>
		<td><b>SICS</b></td>
		<td><b>&nbsp;</b></td>
		<td><b><?php echo $Det_Orden['proveedor'][0]['prov_dias']; ?></b></td>
		<td><b>&nbsp;</b></td>
	</tr>
</table>
<table id="detalle" name="detalle" class="table table-condensed">
	<thead>
		<tr>
			<th>CODIGO</th>
			<th>CANTIDAD</th>
			<th>DESCRIPCION</th>
			<th>CC</th>
			<th>PREC. UNIT</th>
			<th>TOTAL</th>
			<th>ID</th>
		</tr>
	</thead>
	<tbody>
	<?php
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$inconsistencias = 0;
		foreach ( $Det_Orden ['detalle'] as $value ) {
			$suma = $suma + $value ['predreq_total'];
			if (! empty ( $value ['prod_codigo'] )) {
	?>
				<?php
					try {
						$sql = "Select count(*) From " . $conf->getTbl_producto () . " Where prod_codigo = '" . $value ['prod_codigo'] . "'";
						$run = $db->ejecutar ( $sql );
						try {
							$row = $db->obtener ( $run, 0 );
							if ($row ['0'] <= 0) {
								$inconsistencias = $inconsistencias + 1;
				?>
							<tr class="danger">
								<td><?php echo $value['prod_codigo']; ?></td>
								<td><?php echo $value['predreq_cantidad_aut']; ?></td>
								<td>
									<?php echo $value['predreq_descripcion']; ?>
									<small class="text-danger">* no existe en inventario</small>
								</td>
								<td><?php echo $value['ic_cc']; ?></td>
								<td><?php echo number_format($value['predreq_prec_uni'], 2); ?></td>
								<td><?php echo number_format($value['predreq_total'], 2); ?></td>
								<td><?php echo $value['id_prehreq']; ?></td>
							</tr>
							<?php
							} else {
							?>
							<tr>
								<td><?php echo $value['prod_codigo']; ?></td>
								<td><?php echo $value['predreq_cantidad_aut']; ?></td>
								<td><?php echo $value['predreq_descripcion']; ?></td>
								<td><?php echo $value['id_cc']; ?></td>
								<td><?php echo number_format($value['predreq_prec_uni'], 2); ?></td>
								<td><?php echo number_format($value['predreq_total'], 2); ?></td>
								<td><?php echo $value['id_prehreq']; ?></td>
							</tr>
							<?php
							}
						} catch ( Exception $e2 ) {
							echo $e2->getMessage ();
						}
					} catch ( Exception $e ) {
						echo $e->getMessage ();
					}
			} else {
				$inconsistencias = $inconsistencias + 1;
			?>
				<tr class="danger">
					<td>
						<?php echo $value['prod_codigo']; ?>
						*** N/E ***
					</td>
					<td><?php echo $value['predreq_cantidad_aut']; ?></td>
					<td>
							<?php echo $value['predreq_descripcion']; ?>
							<small>* no existe en inventario</small>
					</td>
					<td><?php echo $value['CC']; ?></td>
					<td><?php echo number_format($value['predreq_prec_uni'], 2); ?></td>
					<td><?php echo number_format($value['predreq_total'], 2); ?></td>
					<td><?php echo $value['id_prehreq']; ?></td>
				</tr>
			<?php
			}
		}
		?>
	</tbody>
<?php
	} catch ( Exception $e1 ) {
		echo $e1->getMessage ();
		die ();
	}
	?>
<?php
if ($inconsistencias == 0) {
	?>
	<script type="text/javascript">
		$(function(){
			$("#btnIngresa").attr('disabled', '');
			$("#btnIngresa").prop('disabled', false);
			$("#btnIngresa").removeClass("disabled");
		});
	</script>
<?php } else { ?>

<?php } ?>
<tfoot>
	<tr>
		<td colspan="5"><b>VALOR TOTAL DE LA ORDEN :</b></td>
		<td><b><?php echo number_format($suma, 2); ?></b></td>
	</tr>
<tfoot>
</table>

<br><br><br><br>