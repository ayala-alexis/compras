<script type="text/javascript">
$('.btn-sm').live('click', function(){
	var btn = $(this);
	var $id = $(this).attr('id');
	var str = $id.split('|');
	var elem = $('#tbl'+str[0]+' > tbody > tr');
	var empresa = $('#empresa option:selected').val();
	var param = { items: [], empresa: [], proveedor: [] };
	param.empresa.push(empresa);
	param.proveedor.push(str[0]);
	elem.each(function(i) {
		var ids = $(this).find('td').eq(0).attr('id').split('|');
		param.items.push({
			'codigo' : $(this).find('td').eq(0).text(),
			'gasto' : $(this).find('td').eq(1).text(),
			'cantidad' : $(this).find('td').eq(2).text(),
			'unidades' : $(this).find('td').eq(3).text(),
			'descripcion' : $(this).find('td').eq(4).text(),
			'cc' : $(this).find('td').eq(5).text(),
			'prec_uni' : $(this).find('td').eq(6).text(),
			'total' : $(this).find('td').eq(7).text(),
			'id_predsol' : ids[0],
			'id_prehsol' : ids[1]
		});
	});
	// Enviamos a guardar
	myUrl = location.protocol + "//" + location.host + "/sics/json.php?c=solc&a=CreaOc";
	$.ajax({
		type: 'POST',
		url: myUrl,
		data: {
			form: param
		},
		beforeSend: function(){
			$.pnotify({
				text: 'Creando Orden de Compra para Proveedor : '+str[1]+', por favor espere...',
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
					title: 'Orden de Compra creada con exito!',
					text: data,
					type: 'success',
					icon: 'picon picon-flag-green',
					hide: true
				});
				btn.removeClass('btn-primary');
				btn.addClass('btn-success');
				btn.text('ORDEN CREADA : '+data);
				btn.prop('disabled', 'disabled');
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
	var $id = $(this).attr('id');
	var str = $id.split('|');
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
<form role="form" class="form-inline" action="?c=solc&a=crearoc&id=5" method="post">
	<div class="form-group">
		<label class="sr-only" for="empresa">Requisiciones para </label>
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
	<button type="submit" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-search"></span> Mostrar lista para enviar a O.C.</button>
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
	<div class="well well-small">
		<h4>Proveedor: <?php echo $colecta['id_proveedor'].' '.$colecta['prov_nombre']; ?>
		<button id="<?php echo $colecta['id_proveedor'].'|'.$colecta['prov_nombre']; ?>" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-shopping-cart"></span> Crear OC</button>
		</h4>
		<table class="table table-condensed">
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
						<td id="<?php echo $det['id_predsol']."|".$det['id_prehsol']; ?>">
							<?php echo $det['prod_codigo']; ?>
						</td>
						<td><?php echo str_pad($det['predsol_titgas'],2,'0',STR_PAD_LEFT).str_pad($det['predsol_detgas'],2,'0',STR_PAD_LEFT); ?></td>
						<td><?php echo $det['predsol_cantidad_aut']; ?></td>
						<td><?php echo $det['predsol_empaque']; ?></td>
						<td><?php echo $det['predsol_descripcion']; ?></td>
						<td><?php echo $det['cc_codigo']; ?></td>
						<td><?php echo $det['predsol_prec_uni']; ?></td>
						<td><?php echo $det['predsol_total']; ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } ?>
<?php } ?>
<br><br><br>