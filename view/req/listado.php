<script>
//Llenamos la lista de bodegas para la empresa seleccionada
function CheckAjaxCall(str) {
	//var str = $('#empresa').val();
	myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=req&a=listarCcUsuarioC';
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
					opcion += '<option value="'+item.id_cc+'">( '+item.cc_codigo+') '+item.cc_descripcion+'</option>';
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
	//$("#myModal").modal();
	$('.openBtn').on('click',function(){
		$url = $(this).attr('data-url');
	    $('.modal-body').load($url,function(){
	        $('#myModal').modal({show:true});
	    });
	});
	$("#frmCrearReq").validate({
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
	 * Nueva solicitud
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
	 $("#alerta2").fadeOut(5000);

	 $('#btnEnviarAutorizacion').on('click', function(e){
			$('body').removeClass('loaded');
			return;
			e.preventDefault();
		});
});
</script>
<!-- Start Page Loading -->
    <div id="loader-wrapper">
    	<h1>Espere...</h1>
        <div id="loader">
        </div>        
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
    </div>
    <!-- End Page Loading -->
<h4 class="text-blue">Listado de Requisiciones Propias</h4>
<?php if ($permisos[0]['acc_add'] == '1') { ?>
<form role="form" id="frmCrearReq" name="frmCrearReq" class="form-inline" method="post" action="?c=req&a=crear&id=<?php echo $_GET['id']; ?>">
	<table class="table table-condensed">
		<thead>
			<tr>
	  			<td bgcolor="#f5f5f5" colspan="3"><b>Creaci&oacute;n de Requisici&oacute;n</b></td>
			</tr>
		</thead>
		<tbody>
			<tr>
			  	<td>Empresa</td>
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
				<td>Centro de Costo	</td>
				<td>
					<div class="form-group">
						<label id="centros" class="control-label">
							<select class="form-control input-sm" id="dummySelect" name="dummySelect">
								<option> -- Centros de Costo -- </option>
							</select>
						</label>
						<input type="hidden" id="c" name="c" value="req" />
						<input type="hidden" id="a" name="a" value="crear" />
						<input type="hidden" id="idmod" name="idmod" value="<?php echo $_GET['id']; ?>" />
					</div>
				</td>
				<td>
					<button class="btn btn-sm btn-primary" type="submit"><i class="glyphicon glyphicon-plus"></i>&nbsp;Crear</button>
					<?php if ($permisos[0]['acc_aut'] == '1') { ?>
						<a class="btn btn-sm btn-success" href="?c=req&a=autoriza&id=6">
							<i class="glyphicon glyphicon-list-alt"></i>&nbsp;Listas para Autorizar
						</a>
					<?php }?>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<?php } ?>
<div id="alerta" class="alert alert-block alert-danger hidden fade in">
	<h4 class="alert-heading">seleccione empresa</h4>
</div>
<div id="alerta2" class="alert alert-block alert-danger <?php echo $estado; ?> fade in">
	<h4 class="alert-heading"><?php echo $mensaje; ?></h4>
</div>
<table class="table table-condensed table-hover">
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
		<?php foreach($reqs as $req) { ?>
		<tr>
			<td><?php echo $req['prehreq_numero']; ?></td>
			<td> <?php echo '('.$req['id_cc'].')'.$req['cc_descripcion']; ?></td>
			<td><?php echo $req['prehreq_fecha']; ?></td>
			<td>
				<a href="#myModal" class="btn btn-success openBtn" data-url="json.php?c=req&a=ver&id=<?php echo $req['id_prehreq'];?>" data-toggle="modal" >
					<i class="glyphicon glyphicon-search"></i>
				</a>
				<a href="#" class="btn btn-default" data-placement="left" rel="popover" title="estado de solicitud" data-html="true" data-content="<?php echo $req['estados']; ?>">
					<i class="glyphicon glyphicon-eye-open"></i>&nbsp;<?php echo $conf->getEstado($req['prehreq_estado']); ?>
				</a>
				<?php if ($req['prehreq_estado'] == 0) { ?>
					<a class="btn btn-default" href="?c=req&a=crear&id=6&ps=<?php echo $req['prehreq_numero']; ?>&cs=<?php echo $req['id_cc']; ?>&es=<?php echo $req['id_empresa']; ?>">
						<i class="glyphicon glyphicon-pencil"></i>&nbsp;Editar
					</a>
					<a class="btn btn-danger" href="?c=req&a=borrar&id=6&ps=<?php echo $req['id_prehreq']; ?>&cs=<?php echo $req['id_cc']; ?>&es=<?php echo $req['id_empresa']; ?>">
						<i class="glyphicon glyphicon-remove"></i>&nbsp;Eliminar
					</a>
					<a id="btnEnviarAutorizacion" name="btnEnviarAutorizacion" class="btn btn-success" href="?c=req&a=auto&id=6&ps=<?php echo $req['id_prehreq']; ?>&cs=<?php echo $req['id_cc']; ?>&es=<?php echo $req['id_empresa']; ?>">
						<i class="glyphicon glyphicon-ok"></i>&nbsp;Enviar para Autorizacion
					</a>
				<?php } ?>
				<?php if ($permisos[0]['acc_aut'] == '1' && $req['prehreq_estado'] == 1) { ?>
					<a class="btn btn-default" href="?c=req&a=crear&id=6&ps=<?php echo $req['prehreq_numero']; ?>&cs=<?php echo $req['id_cc']; ?>&es=<?php echo $req['id_empresa']; ?>">
						<i class="glyphicon glyphicon-pencil"></i>&nbsp;Autorizar
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
				<?php echo $cuantos.' '; ?>  requisicion(es) pendiente(s)
			</th>
		</tr>
	</tfoot>
</table>
<!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          	<button type="button" class="close" data-dismiss="modal">&times;</button>
          	<h4><span class="glyphicon glyphicon-search"></span> Detalle de Requisicion</h4>
        </div>
        <div class="modal-body">
          
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-default btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cerrar</button>
        </div>
      </div>
    </div>
  </div>