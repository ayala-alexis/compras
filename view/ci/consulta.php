<script>
//Llenamos la lista de bodegas para la empresa seleccionada
function CheckAjaxCall(str) {
    var str = $('#empresa').val();
	var res = str.split('~');
  myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=ci&a=listarCcUsuarioC';
  $.ajax({
    type: 'POST',
    url: myUrl,
    dataType: 'json',
           data: {
               id_empresa: res[0]
    },
    success: function(aData){
        //console.log(aData);
        $('#dummySelect').remove();
    	$('#centrocosto').html('');
    	$('#centrocosto').hide('fast');
    	$('#ccs').html('');
    	$('#ccs').hide('fast');
		if(aData.length > 1) {
			var opcion='';
			opcion += '<option value="9999">Todos</option>';
			$.each(aData, function(i, item){
				opcion += '<option value="'+item.id_cc+'">'+item.cc_descripcion+'</option>';
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
  	$('a.pull-right').live('click',function(){
  	  	jAlert($(this).text());
  	});
});
</script>
<h4 class="text-blue">Consulta de Solicitudes</h4>
<form class="form-inline" role="form" id="frmVer"  method="post" action="?c=ci&a=emisor&id=12&page=1">
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
						<option value="<?php echo $emp['id_empresa'].'~'.$emp['emp_nombre']; ?>"><?php echo $emp['emp_nombre']; ?></option>
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
			    	<select class="form-control input-sm" id="dummySelect" name="dummySelect" required>
						<option> -- Centros de Costo -- </option>
					</select>
			    </td>
			</tr>
			<tr>
				<td>Numero</td>
				<td>
					<div class="form-group">
						<label class="sr-only" for="numero">Numero</label>
						<input type="text" class="form-control input-sm"  id="numero" name="numero">
					</div>
				</td>
			</tr>
			<tr>
				<td>Status</td>
				<td>
					<div class="form-group">
						<label class="sr-only" for="numero">Status</label>
						<select class="form-control input-sm" id="status" name="status">
							<option value="0">-- seleccione --</option>
							<option value="1">Autorizado</option>
							<option value="2">En Proceso</option>
							<option value="3">Impreso</option>
							<option value="4">Autorizado</option>
							<option value="10">Desistida</option>
						</select>
					</div>
			  		<input type="hidden" value="1" name="page" id="page">
			  		<button class="btn btn-primary btn-sm" type="submit"><i class="glyphicon glyphicon-search"></i>Mostrar</button>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<div class="row">
	<div class="col-md-12">
		<div class="panel-group" id="accordion">
		<?php if ($_POST && $solcs['registros'] <= 0) { ?>
	  		<div class="alert alert-info">No se han encontrado registros para mostrar</div>
	  	<?php } ?>
		<?php foreach($solcs as $solc){  ?>
		  	<?php if(isset($solc['ci_numero'])) { ?>
			  	<div class="panel panel-default">
			  		<div class="panel-heading">
			  			<a class="panel-title" style="float: left;" href="view/ci/PDF.php?id=<?php echo $solc['id_ci']; ?>"><i class="icon-print"></i></a>
			  			<h4 class="panel-title" data-toggle="collapse" data-parent="accordion" href="#collapseSolcDet<?php echo $solc['id_ci']; ?>">
							&nbsp;&nbsp;&nbsp;<i class="icon-hand-down">#<?php echo $solc['ci_numero']; ?></i>
			          		| <i class="icon-calendar"></i><?php echo $solc['ci_enc_fecha'].' '.$solc['ci_enc_hora']; ?>
			          		| <i class="icon-comment"></i><?php echo $solc['cc_descripcion']; ?>
			          		| <i class="icon-user"></i><?php echo $solc['prod_usuario']; ?>
			          	</h4>
			  		</div>
			  		<div id="collapseSolcDet<?php echo $solc['id_ci']; ?>" class="panel-collapse collapse">
		  				<table class="table table-condensed">
						    <thead>
						      	<tr>
						    		<th>Codigo</th>
						    		<th>Descripcion</th>
						    		<th>Cant<br>solicitada</th>
						    	</tr>
						    </thead>
						    <tbody>
		  				<?php foreach($solc['Det'] as $det) { ?>
		  						<tr>
		  							<td><?php echo $det['prod_codigo']; ?></td>
		  							<td><?php echo $det['prod_descripcion']; ?></td>
		   							<td><?php echo $det['ci_cantidad']; ?></td>
	  							</tr>
	  					<?php } ?>
	  						</tbody>
	  					</table>
		  			</div>
		  		</div>
		  	<?php } ?>
		<?php } ?>
	  </div>
	</div>
</div>
<div class="row">
    <div class="col-md-12">
    	<?php $registros = $solcs['registros']; ?>
    	<?php if($registros > 10) { ?>
    	<?php
		// determine page (based on <_GET>)
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$totalPag = ceil($registros/10);
		?>
       <div class="pagination">
			<ul class="pagination">
				<?php if($page > 1 && $page <= $totalPag) { ?>
				<li class="<?php if($page == 1) echo 'disabled'; ?>">
					<a href="?c=ci&a=emisor&id=12&page=<?php echo ($page-1); ?>">&laquo;</a>
				</li>
				<?php } ?>
				<?php
				for( $i=1; $i<=$totalPag; $i++ ) {
				?>
					<li class="<?php if($page == $i) echo 'active'; ?>">
						<a href="?c=ci&a=emisor&id=12&page=<?php echo $i; ?>"><?php echo $i; ?></a>
					</li>
				<?php
				}
				?>
				<?php if($page < $totalPag) { ?>
				<li class="<?php if($page == $totalPag) echo 'disabled'; ?>">
					<a href="?c=ci&a=emisor&id=12&page=<?php echo ($page+1); ?>">&raquo;</a>
				</li>
				<?php } ?>
			</ul>
	   </div>
	   <?php } ?>
    </div>
</div>