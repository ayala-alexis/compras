<script>
//Llenamos la lista de bodegas para la empresa seleccionada
function CheckAjaxCall(str) {
	//var str = $('#empresa').val();
	myUrl = 'json.php?c=solc&a=listarCcUsuarioC';
	//myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=solc&a=listarCcUsuarioC';
	$.ajax({
		type: 'POST',
		url: myUrl,
		dataType: 'json',
       		data: {
				id_empresa: str
		},
		success: function(aData){
			$('#dummySelect').remove();
			$('#centrocosto').html('');
			$('#centrocosto').hide('fast');
			$('#ccs').html('');
			$('#ccs').hide('fast');
			if(aData.length > 1) {
				var opcion='';
				$.each(aData, function(i, item){
					opcion += '<option value="'+item.id_cc+'">'+item.cc_descripcion+'</option>';
				});
				$("label#centros").html('<select class="form-control input-sm" id="centrocosto" name="centrocosto">'+opcion+'</select>');
			} else {
				$.each(aData, function(i, item){
					$("label#centros").append('<span id="ccs" class="text-info">'+item.cc_descripcion+'</span><input type="hidden" id="centrocosto" name="centrocosto" value="'+item.id_cc+'" />');
				});
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(textStatus);
		}
	});
	return false;
}
$(function(){
	$("#frmCrearSolc").validate({
		rules:{
			empresa : {
				required: true
			},
			centrocosto : {
				required: true
			}
		},
		submitHandler: function(form) {
			$(":submit").addClass('disabled');
			$(":submit").attr('disabled', true);
			form.submit();
		}
	});
	// Capturamos el evento onchange de la empresa para llenar
	// la lista de centros de costo
	$("#empresa").change(function(){
		if($(this).val() != "") {
			CheckAjaxCall($(this).val());
		} else {
			$('#centrocosto').html('');
			$('#centrocosto').hide('fast');
			$('#ccs').html('');
			$('#ccs').hide('fast');
		}
	});
	/*
	 *
	 */
	$('button').unbind('click').bind('click', function(){
		 if($("#empresa").val() == "" || $("#centrocosto").val() == ""){
			$('#alerta').show('slow');
			$('#alerta').slideUp('slow');
		 } else {
			$("#divAddItem").modal({
				backdrop: true,
				keyboard: false
			});
		}
	});
	 $("a").popover();
});
</script>
<h4 class="text-blue">Listado de Solicitudes Propias</h4>
<?php if ($permisos[0]['acc_add'] == '1') { ?>
<form role="form" id="frmCrearSolc" name="frmCrearSolc"  class="form-inline" method="post" action="?c=solc&a=crear&id=<?php echo $_GET['id']; ?>">
	<table class="table table-condensed">
		<thead>
		<tr>
	  		<td bgcolor="#f5f5f5" colspan="3"><b>Creaci&oacute;n de Solicitud</b></td>
		</tr>
		</thead>
		<tbody>
		<tr>
	  		<td>Seleccione Empresa</td>
	  		<td colspan="2">
				<?php if(count($emps) > 1) { ?>
					<select class="form-control input-sm" id="empresa" name="empresa">
						<option value="">-- Seleccione Empresa --</option>
						<?php foreach ($emps as $emp) { ?>
						<option value="<?php echo $emp['id_empresa']; ?>"><?php echo $emp['emp_nombre']; ?></option>
						<?php } ?>
					</select>
				<?php } else { ?>
					<?php foreach ($emps as $emp) { ?>
						<span class="text-info"><?php echo $emp['emp_nombre']; ?></span>
						<input type="hidden" id="empresa" name="empresa" value="<?php echo $emp['id_empresa']; ?>" />
					<?php } ?>
						<script type="text/javascript">
							$(function(){
								CheckAjaxCall(<?php echo $emps[0]['id_empresa']; ?>);
							});
						</script>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td>Centro de Costo</td>
			<td>
				<div class="form-group">
					<label id="centros" class="control-label">
						<select class="form-control input-sm" id="dummySelect" name="dummySelect">
							<option> -- Centros de Costo -- </option>
						</select>
					</label>
					<input type="hidden" id="c" name="c" value="solc" />
					<input type="hidden" id="a" name="a" value="crear" />
					<input type="hidden" id="idmod" name="idmod" value="<?php echo $_GET['id']; ?>" />
					<button class="btn btn-sm btn-primary" type="submit"><i class="glyphicon glyphicon-plus"></i>&nbsp;Crear</button>
				</div>
			</td>
			<td>
				<?php if ($permisos[0]['acc_aut'] == '1') { ?>
					<a class="btn btn-sm btn-success" href="?c=solc&a=autoriza&id=5">
						<i class="glyphicon glyphicon-list-alt"></i>&nbsp;MOSTRAR TODAS LAS SOLICITUDES LISTAS PARA APROBACION
					</a>
				<?php }?>
			</td>
		</tr>
		</tbody>
	</table>
</form>
<?php } ?>
<div id="alerta" class="alert alert-block alert-error hide fade in">
	<h4 class="alert-heading">seleccione una empresa</h4>
</div>
<table class="table table-condensed tablesorter">
	<thead>
		<tr>
			<th>Numero</th>
			<th>Centro Costo</th>
			<th>Fecha</th>
			<th>Acciones</th>
		</tr>
	</thead>
	<tbody>
		<?php $cuantos = 0; ?>
		<?php foreach($solcs as $solc) { ?>
		<tr>
			<td><?php echo $solc['prehsol_numero']; ?></td>
			<td> <?php echo '( '.$solc['id_cc'].' ) '.$solc['cc_descripcion']; ?></td>
			<td><?php echo $solc['prehsol_fecha']; ?></td>
			<td>
				<a href="#" class="btn btn-sm btn-default" data-placement="left" rel="popover" title="estado de solicitud" data-html="true" data-content="<?php echo $solc['estados']; ?>">
					<i class="glyphicon glyphicon-eye-open"></i>&nbsp;<?php echo $conf->getEstadoSC($solc['prehsol_estado']); ?>
				</a>
				<?php if ($solc['prehsol_estado'] == 0) { ?>
					<a class="btn btn-sm btn-default" href="?c=solc&a=crear&id=5&ps=<?php echo $solc['prehsol_numero']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
						<i class="glyphicon glyphicon-pencil"></i>&nbsp;Editar
					</a>
					<a class="btn btn-sm btn-danger" href="?c=solc&a=borrar&id=5&ps=<?php echo $solc['id_prehsol']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
						<i class="glyphicon glyphicon-remove"></i>&nbsp;Eliminar
					</a>
					<!-- <a class="btn btn-sm btn-success" href="?c=solc&a=auto&id=5&ps=<?php echo $solc['id_prehsol']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
						<i class="glyphicon glyphicon-ok"></i>&nbsp;Enviar para Autorizacion
					</a> -->
				<?php } ?>
				<?php if ($permisos[0]['acc_aut'] == '1' && $solc['prehsol_estado'] == 1) { ?>
					<a class="btn btn-sm btn-default" href="?c=solc&a=crear&id=5&ps=<?php echo $solc['prehsol_numero']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
						<i class="glyphicon glyphicon-envelope"></i>&nbsp;Autorizar
					</a>
				<?php } ?>
			</td>
		</tr>
		<?php $cuantos++; ?>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="8">
				<?php echo $cuantos.' '; ?>  solicitud(es) pendiente(s)
			</th>
		</tr>
	</tfoot>
</table>