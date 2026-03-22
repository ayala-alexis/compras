<script type="text/javascript">
$(document).ready(function(){
	//$("#btnIngresa").attr("disabled","disabled");
	$("#btnVolver").click(function(){
		document.location='?c=inv&a=inicio';
	});
	$("#btnIngresa").click(function(){
		// Deshabilitamos el uso del boton
		$('#btnIngresa').attr("disabled","disabled");
		var empresa_oc = $("#id_empresa_oc").val();
		var oc = $("#oc").val();
		myUrlOC = location.protocol + "//" + location.host + '/sics/json.php?c=inv&a=verificaOCD';
		$.post(myUrlOC, { empresa: empresa_oc, oc: oc },
		function(json) {
			if(json == "OK"){
				myUrlOC2 = location.protocol + "//" + location.host + '/sics/json.php?c=inv&a=marcaOCD';
				$.post(myUrlOC2, { empresa_oc: empresa_oc, oc: oc },
				function(data, textStatus){
					$('#btnIngresa').attr('disabled','disabled');
					alert("Orden "+oc+" desistida con exito");
				});
			} else {
				alert("Ha ocurrido un error, " + json);
				$('#btnIngresa').attr("disabled","");
				$('#btnIngresa').prop("disabled","");
			}
		});
	});
});
</script>
<?php
list ( $empresa_oc, $empresa_sicys ) = split ( '-', $_POST ['empresa'] );
$inconsistencias = 0;
?>
<input type="hidden" id="id_empresa" name="id_empresa" value="<?php echo $empresa_sicys; ?>" />
<input type="hidden" id="id_empresa_oc" name="id_empresa_oc" value="<?php echo $empresa_oc; ?>" />
<input type="hidden" id="bod_codigo" name="bod_codigo" value="<?php echo $_POST['bodega']; ?>" />
<input type="hidden" id="oc" name="oc" value="<?php echo $_POST['oc']; ?>" />
<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
<fieldset>
	<legend>
		<strong><?php echo $_POST['nempresa']; ?></strong>
	</legend>
	<img id="loading" name="loading" src="css/themes/redmond/images/loading.gif" class="pull-right" style="display: none;">
	<div class="btn-group">
		<button class="btn" id="btnVolver" name="btnVolver">CANCELAR</button>
		<button class="btn btn-primary" id="btnIngresa" name="btnIngresa">DESISTIR</button>
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
			<td><b><?php echo $Det_Orden['empresa'][0]['NOMBRE']; ?></b></td>
	<?php
	} else {
	?>
		<tr>
			<td rowspan="4"><img src=images/_totem.jpg></td>
			<td><b><?php echo $Det_Orden['empresa'][0]['NOMBRE']; ?></b></td>
	<?php
	}
	?>
		<td>
			<b class="text-danger">ORDEN DE COMPRA No. <?php echo $_POST['oc']; ?>
	<?php
	if ($Det_Orden ['empresa'] [0] ['ESTADO'] == '*') {
		$inconsistencias = 1;
	?>
			**ANULADA**
	<?php
	}
	?>
			</b>
		</td>
	</tr>
	<tr>
		<td><?php echo $Det_Orden['empresa'][0]['DIRE1']; ?></td>
		<td>Registro No. : <b><?php echo $Det_Orden['empresa'][0]['NREGISTRO']; ?></b></td>
	</tr>
	<tr>
		<td><?php echo $Det_Orden['empresa'][0]['DIRE2']; ?></td>
		<td>Giro : <b><?php echo $Det_Orden['empresa'][0]['GIRO']; ?></b></td>
	</tr>
	<tr>
		<td>Tel.: <b><?php echo $Det_Orden['empresa'][0]['TEL']; ?></b></td>
		<td>Nit : <b><?php echo $Det_Orden['empresa'][0]['NIT']; ?></b></td>
	</tr>
</tbody>
</table>
<table id="encabezado2" class="table table-condensed">
	<tr>
		<td colspan="4">PROVEEDOR : <b><?php echo $Det_Orden['proveedor'][0]['PROVEEDOR']; ?></b></td>
	</tr>
	<tr>
		<td colspan="2">FECHA : <b><?php echo $Det_Orden['encabezado'][0]['FECHAPED']; ?></b></td>
		<td rowspan="2" colspan="2">FAX : <b><?php echo $Det_Orden['proveedor'][0]['FAX']; ?></b>&nbsp;&nbsp;&nbsp;PBX
			: <b><?php echo $Det_Orden['proveedor'][0]['PBX']; ?></b>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;USUARIO
			CREO : <b><?php echo $Det_Orden['encabezado'][0]['US_CREO']; ?></b>
			<br>ATENCION : <b><?php echo $Det_Orden['proveedor'][0]['CONTACTO']; ?></b>
			&nbsp;&nbsp;&nbsp;E-MAIL : <b><?php echo $Det_Orden['proveedor'][0]['EMAIL']; ?></b>
		</td>
	</tr>
	<tr>
		<td colspan="2">FECHA DE ENTREGA : <b><?php echo $Det_Orden['encabezado'][0]['FECHAENTRE']; ?></b></td>
	</tr>
	<tr>
		<td>PEDIDO POR</td>
		<td>COTIZACION</td>
		<td>CONDICIONES DE PAGO</td>
		<td>SOLICITUD DE COMPRA</td>
	</tr>
	<tr>
		<td><b><?php echo $Det_Orden['encabezado'][0]['PEDIDOPOR']; ?></b></td>
		<td><b><?php echo $Det_Orden['encabezado'][0]['COTIZACION']; ?></b></td>
		<td><b><?php echo $Det_Orden['encabezado'][0]['CONDIPAGO']; ?></b></td>
		<td><b><?php echo $Det_Orden['encabezado'][0]['SOLICITCOM']; ?></b></td>
	</tr>
	<?php
	if ($_Row ['ESTADO'] == '*') {
	?>
	<tr>
		<td colspan="4">Anulacion : <b><?php echo $Det_Orden['encabezado'][0]['OBS']; ?></b></td>
	</tr>
	<?php
	}
	?>
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
		</tr>
	</thead>
	<tbody>
	<?php
	try {
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		foreach ( $Det_Orden ['detalle'] as $value ) {
			$suma = $suma + $value ['TOTAL'];
			if (! empty ( $value ['CODAS400'] )) {
	?>
				<?php
					try {
						$sql = "Select count(*) From " . $conf->getTbl_producto () . " Where prod_codigo = '" . $value ['CODAS400'] . "'";
						$run = $db->ejecutar ( $sql );
						try {
							$row = $db->obtener ( $run, 0 );
							if ($row ['0'] <= 0) {
								$inconsistencias = $inconsistencias + 1;
				?>
							<tr class="error">
								<td><?php echo $value['CODAS400']; ?></td>
								<td><?php echo $value['CANT']; ?></td>
								<td>
									<?php echo $value['DESCRIP']; ?>
									<small>* no existe en inventario</small>
								</td>
								<td><?php echo $value['CC']; ?></td>
								<td><?php echo number_format($value['PRECUNIT_D'], 2); ?></td>
								<td><?php echo number_format($value['TOTAL'], 2); ?></td>
							</tr>
							<?php
							} else {
							?>
							<tr>
								<td><?php echo $value['CODAS400']; ?></td>
								<td><?php echo $value['CANT']; ?></td>
								<td><?php echo $value['DESCRIP']; ?></td>
								<td><?php echo $value['CC']; ?></td>
								<td><?php echo number_format($value['PRECUNIT_D'], 2); ?></td>
								<td><?php echo number_format($value['TOTAL'], 2); ?></td>
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
				<tr class="error">
					<td>
						<?php echo $value['CODAS400']; ?>
						<!-- *** N/E *** -->
					</td>
					<td><?php echo $value['CANT']; ?></td>
					<td>
							<?php echo $value['DESCRIP']; ?>
							<!-- <small>* no existe en inventario</small> -->
					</td>
					<td><?php echo $value['CC']; ?></td>
					<td><?php echo number_format($value['PRECUNIT_D'], 2); ?></td>
					<td><?php echo number_format($value['TOTAL'], 2); ?></td>
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
	<script>
		$("#btnIngresa").removeAttr('disabled');
		$('#btnIngresa').attr("disabled","");
	</script>
<?php
}
?>
<tfoot>
	<tr>
		<td colspan="5">VALOR TOTAL DE LA ORDEN :</td>
		<td><?php echo number_format($suma, 2); ?></td>
	</tr>
<tfoot>
</table>
<br><br><br><br>