<script>
//$.fn.editable.defaults.mode = 'popup';
$.fn.editableform.buttons =
	  '<button type="submit" class="btn btn-success editable-submit btn-mini"><i class="icon-ok icon-white"></i></button>' +
	 '<button type="button" class="btn editable-cancel btn-mini"><i class="icon-remove"></i></button>';
$(function(){
	//editables
    $('p > a').editable({
		url: 'post/post.php',
		type: 'text',
		name: $(this).attr('id'),
		title: 'digite '+$(this).attr('id'),
		validate: function(value) {
			if($.trim(value) == '')	return 'digite cantidad';
		}
    });
	$('input[type="button"]').live('click', function(){
		empresa = $('select option:selected').val();
		if(empresa != ""){
			window.location.href = "admin.php?c=emp&a=inicio&id=3&es="+empresa;
		} else {
			jAlert('seleccione empresa', 'alerta');
		}
	});
	//
	$('#myTab a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	});
	// Borra Centro de Costo
	$('#delCC').live('click', function(){
		$row = $(this).closest('tr');
		$row.find('td').fadeOut('slow', function(){
			// Nos borramos la linea
			$row.remove();
		});
	});
	// Borra Bodega
	$('#delBodega').live('click', function(){
		$row = $(this).closest('tr');
		$row.find('td').fadeOut('slow', function(){
			// Nos borramos la linea
			$row.remove();
		});
	});
});
</script>
<div class="tabbable">
	<div class="well well-small">
		<form action="" class="form form-inline">
			<select>
				<option value="">-- seleccione empresa --</option>
				<?php foreach ($emps as $emp){ ?>
				<option value="<?php echo $emp['id_empresa']; ?>" <?php if($_GET['es'] == $emp['id_empresa']){ echo 'selected'; }?>><?php echo $emp['emp_nombre']; ?></option>
				<?php } ?>
			</select>
			<input type="button" class="btn btn-primary" value="mostrar configuracion">
		</form>
	</div>

	<ul class="nav nav-tabs" id="myTab">
		<li class="active"><a href="#informacion">Informacion</a></li>
		<li><a href="#cc">Centros de Costo</a></li>
		<li><a href="#bodega">Bodegas</a></li>
		<li><a href="#presupuesto">Presupuesto</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane fade in active" id="informacion">
			<div class="span12 well">
				<div class="row">
					<div class="span2">
						<a href="#" class="thumbnail"><img src="img/_totem.jpg" alt=""></a>
					</div>
					<div class="span10">
						<p>razon : <a href="#" id="emp_razon" data-pk="<?php echo $infos[0]['id_empresa']; ?>"><?php echo $infos[0]['emp_razon']; ?></a></p>
			          	<p>nombre : <a href="#" id="emp_razon" data-pk="<?php echo $infos[0]['id_empresa']; ?>"><strong><?php echo $infos[0]['emp_nombre']; ?></strong></a></p>
			          	<p>direccion : <?php echo $infos[0]['emp_direccion']; ?></p>
			          	<p>fecha y hora creacion : <?php echo $infos[0]['emp_fecha'],' - ',$infos[0]['emp_hora']; ?></p>
			          	<p>usuario creo : <?php echo $infos[0]['emp_usuario']; ?></p>
						<span class=" badge badge-warning">nit : <?php echo $infos[0]['emp_nit']; ?></span>
						<span class=" badge badge-info">registro : <?php echo $infos[0]['emp_registro']; ?></span>
					</div>
				</div>
			</div>
		</div>
		<div class="tab-pane fade" id="cc">
			<table class="table table-condensed">
				<thead>
					<tr>
						<th width="10%">codigo</th>
						<th>nombre</th>
						<th><a href="#" class="btn btn-mini btn-success"><i class="icon-plus"></i></a></th>
					</tr>
				</thead>
				<tbody>
			<?php foreach ($ccs as $cc) {?>
					<tr>
						<td><?php echo $cc['cc_codigo']; ?></td>
						<td><?php echo $cc['cc_descripcion']; ?></td>
						<td>
							<a href="#" id="delCC">
								<i class="close">&times;</i>
							</a>
						</td>
					</tr>
			<?php } ?>
				</tbody>
			</table>
		</div>
		<div class="tab-pane fade" id="bodega">
			<table class="table table-condensed">
				<thead>
					<tr>
						<th width="10%">codigo</th>
						<th>descripcion</th>
						<th><a href="#" class="btn btn-mini btn-success"><i class="icon-plus"></i></a></th>
					</tr>
				</thead>
				<tbody>
			<?php foreach ($bodegas as $bodega) {?>
					<tr>
						<td><?php echo $bodega['id_bodega']; ?></td>
						<td><?php echo $bodega['bod_descripcion']; ?></td>
						<td>
							<a href="#" id="delBodega">
								<i class="close">&times;</i>
							</a>
						</td>
					</tr>
			<?php } ?>
				</tbody>
			</table>
		</div>
		<div class="tab-pane fade" id="presupuesto">
			<?php print_r($presupuestos); ?>
		</div>
	</div>
</div>