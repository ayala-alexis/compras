<script>
	//Llenamos la lista de bodegas para la empresa seleccionada
	function CheckAjaxCall(str) {
	  //var str = $('#empresa').val();
	  myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=req&a=listarCcUsuarioCCat';
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
	    	console.log(aData);
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
			$("#centrocosto").val("<?php echo $_POST['centrocosto']?>");
			
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

<div class="row">
	<div class="col-sm-8">
		<h4 class="text-blue">Consulta solicitud de compra por usuario involucrado en trazabilidad</h4>
	</div>
	<div class="col-sm-4">
		<a href='?c=solcheque&a=VerS' class="btn btn-default">Consultar Cheque de Cheque</a>
	</div>
</div>
<form class="form-inline" role="form" id="frmVer"  method="post" action="?c=solc&a=VerS&id=5&page=1">
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
					<?php } else {?>
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
					<select class="form-control input-sm" id="dummySelect" name="dummySelect" >
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
                    <input type="hidden" value="1" name="page" id="page">
					<button class="btn btn-primary btn-sm" type="submit"><i class="glyphicon glyphicon-search"></i>Mostrar</button>
				</td>
			</tr>

		</tbody>
	</table>
</form>


<!-- Listado -->
<div class="row">
	<div class="col-md-12">
		<div class="panel-group" id="accordion">
			<?php if ($_POST && $solcs['registros'] <= 0) { ?>
			<div class="alert alert-info">No se han encontrado registros para mostrar</div>
			<?php } ?>
			<?php foreach($solcs as $solc){  ?>
			<?php if(isset($solc['prehsol_numero'])) { ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<a href="view/solc/PDF.php?id=<?php echo $solc['id_prehsol']; ?>" id="imprimir" target="new" class="pull-left" style="margin-right; 20px;"><i class="glyphicon glyphicon-print"></i> PDF </a>
					<h4 class="panel-title" data-toggle="collapse" data-parent="accordion" href="#collapseSolcDet<?php echo $solc['id_prehsol']; ?>">
						&nbsp;&nbsp;&nbsp;<i class="icon-hand-down">#<?php echo $solc['prehsol_numero_sol']; ?></i>
						| <i class="icon-calendar"></i><?php echo $solc['prehsol_fecha'].' '.$solc['prehsol_hora']; ?>
						| <i class="icon-comment"></i><?php echo $solc['cc_descripcion']; ?>
						| <i class="icon-user"></i><?php echo $solc['prehsol_usuario']; ?>
						| <i class="icon-tags"></i>Estado:<span class="text-info"><?php echo $solc['nestado']; ?></span>
					</h4>
				</div>
				<div id="collapseSolcDet<?php echo $solc['id_prehsol']; ?>" class="panel-collapse collapse">
					<table class="table table-condensed">
						<thead>
							<tr>
								<th>Codigo</th>
								<th>Descripcion</th>
								<th>Cant<br>autorizada</th>
								<th>Cant<br>solicitada</th>
								<th>Observacion<br>autorizada</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($solc['Det'] as $det) { ?>
							<tr>
								<td><?php echo $det['prod_codigo']; ?></td>
								<td><?php echo $det['predsol_descripcion']; ?></td>
								<td><?php echo $det['predsol_cantidad']; ?></td>
								<td><?php echo $det['predsol_cantidad_aut']; ?></td>
								<td><?php echo $det['predsol_cantidad_aut_obs']; ?></td>
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

<!-- Paginación -->
<div class="row">
	<div class="col-md-12">
		<?php $registros = $solcs['registros']; ?>
		<?php if($registros > 10) { ?>
		<?php
			// determine page (based on <_GET>)
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$totalPag = ceil($registros/10);
			?>
		<ul class="pagination">
			<?php if($page > 1 && $page <= $totalPag) { ?>
			<li class="<?php if($page == 1) echo 'disabled'; ?>">
				<a href="?c=solc&a=VerS&id=5&page=<?php echo ($page-1); ?>">&laquo;</a>
			</li>
			<?php } ?>
			<?php
				for( $i=1; $i<=$totalPag; $i++ ) {
				?>
			<li class="<?php if($page == $i) echo 'active'; ?>">
				<a href="?c=solc&a=VerS&id=5&page=<?php echo $i; ?>"><?php echo $i; ?></a>
			</li>
			<?php
				}
				?>
			<?php if($page < $totalPag) { ?>
			<li class="<?php if($page == $totalPag) echo 'disabled'; ?>">
				<a href="?c=solc&a=VerS&id=5&page=<?php echo ($page+1); ?>">&raquo;</a>
			</li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
</div>

<script>

$(document).ready(function() {
	<?php if(!empty($_POST['empresa'])){?>
    	$("#empresa").val("<?php echo (empty($_POST['empresa']) ? '' : $_POST['empresa'])?>");
	<?php } ?>
	<?php if($_POST['empresa'] > 0){ ?>
		CheckAjaxCall("<?php echo $_POST['empresa']?>");
		$("#centrocosto").val("<?php echo $_POST['centrocosto']?>");
	<?php } ?>	
	$("#numero").val("<?php echo $_POST['numero']?>");
});
</script>