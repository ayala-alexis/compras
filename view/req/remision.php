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
			opcion += '<option value="todos"> -- Mostrar todo -- </option>';
			$.each(aData, function(i, item){
				opcion += '<option value="'+item.id_cc+'">( '+item.cc_codigo+' ) '+item.cc_descripcion+'</option>';
			});
			$("label#centros").after('<select class="form-control input-sm" id="centrocosto" name="centrocosto">'+opcion+'</select>');
		} else {
			$.each(aData, function(i, item){
				$("label#centros").after('<span id="ccs" class="text-info">'+item.cc_descripcion+'</span><input type="hidden" id="centrocosto" name="centrocosto" value="'+item.id_cc+'" />');
			});
		}
		$('label#centros').nextAll('img').remove();
    },
    error: function(XMLHttpRequest, textStatus, errorThrown){
      jAlert(textStatus);
    }
  });
  return false;
}
$(function(){
  	//Capturamos el evento onchange de la empresa para llenar
	//la lista de centros de costo
  	$("#empresa").change(function(){
    	if($(this).val() != "") {
    		$('label#centros').after('<img src="images/ajax-loader.gif"></img>');
      		CheckAjaxCall($(this).val());
    	} else {
      		$('#centrocosto').html('');
      		$('#centrocosto').hide('fast');
      		$('#ccs').html('');
        	$('#ccs').hide('fast');
    	}
  	});
  	$("#frmVer").on("submit", function(event) {
      	event.preventDefault();
  	});
  	// Valida formulario
  	$("#frmVer").validate({
  	  	rules:{
      		empresa : {
        		required: true
      		},
      		centrocosto: {
          		required: true
      		}
    	},
    	submitHandler: function(form) {
	      	form.submit();
    	}
  	});
  	$('a#imprimir').live('click',function(){
  	  	$(this).attr('disabled', 'disabled').html('imprimiendo...');
    	myUrl = location.protocol + "//" + location.host + '/sics/'+$(this).attr('href');
    	window.open(myUrl);
		$(this).remove();
  	});
});
</script>
<h4 class="text-blue">Notas de Remision desde Requisiciones</h4>
<form class="form-inline" role="form" id="frmVer"  method="post" action="?c=req&a=nr&id=6&page=1">
	<table class="table table-condensed table-borderless">
		<thead>
			<tr>
			  <td bgcolor="#f5f5f5" colspan="2">
				Seleccione filtro de consulta
			  </td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Empresa</td>
				<td>
					<?php if(count($emps) > 1) { ?>
					<select class="form-control input-sm" id="empresa" name="empresa">
						<option value="">-- seleccione --</option>
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
					<label id="centros" class="control-label sr-only"></label>
					<select class="form-control input-sm" id="dummySelect" name="dummySelect">
						<option> -- Centros de Costo -- </option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Numero Requisicion</td>
				<td>
					<div class="form-group">
						<input type="text" class="form-control input-sm" id="numero" name="numero">
					</div>
					<input type="hidden" value="1" name="page" id="page">
    				<button class="btn btn-sm btn-primary" type="submit"><i class="glyphicon glyphicon-search"></i>Mostrar</button>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<div class="row">
	<div class="col-md-12">
	  	<div class="panel-group" id="accordion">
	  	<?php if ($_POST && $reqs['registros'] <= 0) { ?>
	  		<div class="alert alert-info">No se han encontrado registros para mostrar</div>
	  	<?php } ?>
	  	<?php foreach($reqs as $req){  ?>
	  		<?php if(isset($req['prehreq_numero'])) { ?>
			  	<div class="panel panel-default">
			  		<div class="panel-heading">
			  			<h4 class="panel-title"  data-toggle="collapse" data-parent="accordion" href="#collapseReqCons<?php echo $req['id_prehreq']; ?>">
							<i class="glyphicon glyphicon-hand-down">Req#<?php echo $req['prehreq_numero_req']; ?></i>
			          		| Rem#<?php echo $req['prehreq_numero_remision']; ?></i>
			          		| <i class="glyphicon glyphicon-calendar"></i> <?php echo $req['prehreq_fecha'].' '.$req['prehreq_hora']; ?>
			          		| <i class="glyphicon glyphicon-comment"></i> <?php echo $req['cc_descripcion']; ?>
			          		| <i class="glyphicon glyphicon-user"></i> <?php echo $req['prehreq_usuario']; ?>
			          		| <i class="glyphicon glyphicon-tags"></i><span class="text-primary"> <?php echo $req['nestado']; ?></span>
			          	</h4>
			  		</div>
			  		<div id="collapseReqCons<?php echo $req['id_prehreq']; ?>" class="panel-collapse collapse">
		  				<table class="table table-condensed">
						    <thead>
						      	<tr>
						    		<th>Codigo</th>
						    		<th>Descripcion</th>
						    		<th>Cantidad a enviar</th>
						    		<!-- <th>Estado</th> -->
						    	</tr>
						    </thead>
						    <tbody>
						<?php $mostrar = 1; ?>
		  				<?php foreach($req['Det'] as $det) { ?>
		  						<tr>
		  							<td><?php echo $det['prod_codigo']; ?></td>
		  							<td><?php echo $det['predreq_descripcion']; ?></td>
		   							<td><?php echo $det['predreq_cantidad_aut']; ?></td>
		   							<!-- <td><span class="label label-default">
		   								<?php //if($det['predreq_estado'] == 5) echo 'INGRESADO'; else echo 'PENDIENTE DE COMPRA'; ?>
		   								</span>
		   							</td>-->
		  						</tr>
		  					<?php if($det['predreq_estado'] == 4) $mostrar = 0;?>
		  				<?php } ?>
		  					</tbody>
		  				</table>
		  				<?php if($mostrar == 1) { ?>
		  				<div class="text-center">
			  				<div class="btn-group">
				  				<a href="view/req/PDF_NR.php?id=<?php echo $req['id_prehreq']; ?>" id="imprimir" target="new" class="pull-left btn btn-success" style="margin-right; 20px;"><i class="glyphicon glyphicon-print"></i> Imprimir Nota de Remision</a>
							</div>
						</div>
						<?php } ?>
			  		</div>
			  	</div>
	  		<?php } ?>
	  	<?php } ?>
	  </div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
    	<?php $registros = $reqs['registros']; ?>
    	<?php if($registros > 10) { ?>
    	<?php
		// determine page (based on <_GET>)
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$totalPag = ceil($registros/10);
		?>
       <nav>
			<ul class="pagination">
				<?php if($page > 1 && $page <= $totalPag) { ?>
				<li class="<?php if($page == 1) echo 'disabled'; ?>">
					<a aria-label="Previous" href="?c=req&a=nr&id=6&page=<?php echo ($page-1); ?>">
						<span aria-hidden="true">&laquo;</span>
					</a>
				</li>
				<?php } ?>
				<?php
				for( $i=1; $i<=$totalPag; $i++ ) {
				?>
					<li class="<?php if($page == $i) echo 'active'; ?>">
						<a href="?c=req&a=nr&id=6&page=<?php echo $i; ?>"><?php echo $i; ?></a>
					</li>
				<?php
				}
				?>
				<?php if($page < $totalPag) { ?>
				<li class="<?php if($page == $totalPag) echo 'disabled'; ?>">
					<a aria-label="Next" href="?c=req&a=nr&id=6&page=<?php echo ($page+1); ?>">
						<span aria-hidden="true">&raquo;</span>
					</a>
				</li>
				<?php } ?>
			</ul>
	   </nav>
	   <?php } ?>
    </div>
</div>