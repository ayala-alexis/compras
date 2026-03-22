<script type="text/javascript">
$('.btn-sm').live('click', function(){
	var btn = $(this);
	var $id = $(this).attr('id');
	// Enviamos a guardar
	myUrl = location.protocol + "//" + location.host + "/sics/json.php?c=req&a=CreaNR";
	$.ajax({
		type: 'POST',
		url: myUrl,
		data: {
			id_prehreq: $id
		},
		beforeSend: function(){
			$.pnotify({
				text: 'Creando Nota de Remision para Requisicion : '+$id+', por favor espere...',
				type: 'info',
				hide: true,
				addclass: "stack-bottomright",
				stack: stack_bottomright
			});
		},
		success : function(data){
			if(!$.isNumeric(data)){
				$.pnotify({
					title: 'ha ocurrido un error..',
					text: data,
					type: 'error',
					icon: 'icon-alert-sign',
					hide: true
				});
			} else {
				$.pnotify({
					title: 'Pre Orden de Compra creada con exito!',
					text: data,
					type: 'success',
					icon: 'picon picon-flag-green',
					hide: true
				});
				btn.removeClass('btn-primary');
				btn.addClass('btn-success');
				btn.text('NOTA DE REMISION CREADA : '+data);
				btn.prop('disabled', 'disabled');
				window.open("http://192.168.40.4/sics/view/req/PDF_NR.php?id="+$id);
			}
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$.pnotify({
				title: 'ha ocurrido un error..',
				text: 'ocurrio lo sigueinte :'+textStatus,
				type: 'error',
				icon: 'icon-alert-sign',
				hide: true
			});
		}
	});
	/*
    $.pnotify({
        title: 'Codigo',
        text: str[0],
        type: 'info'
    });
    $.pnotify({
        title: 'Nombre',
        text: str[1],
        type: 'info'
	});
	*/
});
</script>
<form class="form-inline" action="?c=req&a=crearnr&id=6" method="post">
	<div class="form-group">
		<label class="sr-only" for="empresa">Requisiciones para</label>
		<select id="empresa" name="empresa" class="form-control input-sm">
		<?php foreach ($emps as $emp) { ?>
		<?php
		if(isset($_POST['empresa']) && $_POST['empresa'] == $emp['id_empresa']) {
		  	$empresa = $emp['emp_nombre'];
		  	$id_empresa = $emp['id_empresa'];
		}
		?>
		<option value="<?php echo $emp['id_empresa']; ?>" <?php if(isset($_POST['empresa']) && $_POST['empresa'] == $emp['id_empresa']) echo "selected"; ?>><?php echo $emp['emp_nombre']; ?></option>
		<?php } ?>
		</select>
	</div>
	<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Mostrar lista para Notas de Remision</button>
</form>
<br>
<?php
if(isset($trabajar) && !is_array($trabajar)) {
	echo '<div class="alert alert-warning">';
	echo $trabajar;
	echo '</div>';
}
?>
<?php if(isset($trabajar) && count($trabajar) <= 0) echo '<span class="alert alert-warning">no existen registros para trabajar</span>'; ?>
<?php if(isset($trabajar) && count($trabajar) > 0 ) { ?>
	<?php foreach($trabajar as $colecta){ ?>
	<div class="well well-sm">
		<h4>Numero de Requisicion : <?php echo $colecta['prehreq_numero_req']; ?>  Centro de Costo: <?php echo $colecta['cc_descripcion']; ?>
		<button id="<?php echo $colecta['id_prehreq']; ?>" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-road"></span> Generar Nota de Remision</button>
		</h4>
		<table id="tbl<?php echo $colecta['id_prehreq']; ?>" class="table table-condensed">
			<thead>
				<tr>
					<th>CODIGO</th>
					<th>GASTO</th>
					<th>CANT</th>
					<th>UNDS</th>
					<th>DESCRIPCION</th>
					<th>CC</th>
					<th>PREC.UNI</th>
					<th>TOTAL</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($colecta['Det'] as $det) { ?>
					<tr>
						<td id="<?php echo $det['id_predreq']."|".$det['id_prehreq']; ?>">
							<?php echo $det['prod_codigo']; ?>
						</td>
						<td><?php echo str_pad($det['predreq_titgas'],2,'0',STR_PAD_LEFT).str_pad($det['predreq_detgas'],2,'0',STR_PAD_LEFT); ?></td>
						<td><?php echo $det['predreq_cantidad_aut']; ?></td>
						<td><?php echo $det['predreq_empaque']; ?></td>
						<td><?php echo $det['predreq_descripcion']; ?></td>
						<td><?php echo $det['cc_codigo']; ?></td>
						<td><?php echo $det['predreq_prec_uni']; ?></td>
						<td><?php echo $det['predreq_total']; ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } ?>
<?php } ?>
<br><br><br>