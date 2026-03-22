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
  	$('a.pull-right').live('click',function(){
  	  	jAlert($(this).text());
  	});
});
</script>
<h4 class="text-blue">Consulta de Requisiciones</h4>
<form class="form-inline" role="form" id="frmVer"  method="post" action="?c=req&a=emisor&id=6&page=1">
	<table class="table table-condensed table-borderless" width="100%" border="0" cellpadding="2" cellspacing="1">
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
						<label class="sr-only" for="numero">Numero</label>
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
			  			<a href="view/req/PDF.php?id=<?php echo $req['id_prehreq']; ?>" id="imprimir" target="new" class="pull-left" style="margin-right; 20px;"><i class="glyphicon glyphicon-print"></i> PDF </a>
			  			<h4 class="panel-title"  data-toggle="collapse" data-parent="accordion" href="#collapseReqCons<?php echo $req['id_prehreq']; ?>">
							<i class="glyphicon glyphicon-hand-down">#<?php echo $req['prehreq_numero_req']; ?></i>
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
						    		<th>Cant<br>solicitada</th>
						    		<th>Cant<br>autorizada</th>
						    		<th>Observacion<br>autorizada</th>
						    	</tr>
						    	<tr>
						    	<?php
						    	$esta = array(
										'1' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => ''),
						    			'2' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => ''),
										'3' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => ''),
										'4' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => ''),
										'5' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => ''),
										'6' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => ''),
										'7' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => ''),
										'8' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => ''),
										'9' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => ''),
										'10' => array('fecha' => '', 'hora' => '', 'estado' => 'disabled', 'paso' => '')
										); 
								$paso['paso'] = '';
								//print_r($req['estados']);
						    	foreach ($req['estados'] as $est) {
									//print_r($est);
									$esta[$est['prehreq_stat']]['fecha'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$est['prehreq_stat_fecha']; 
									$esta[$est['prehreq_stat']]['hora'] = $est['prehreq_stat_hora'];
									$esta[$est['prehreq_stat']]['usuario'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$est['prehreq_stat_usuario'];
									$esta[$est['prehreq_stat']]['estado'] = 'active';
									switch ($est['prehreq_stat']) {
										case 4:
											$paso['paso'] = 'c1';
											break;
										case 5:
											$paso['paso'] = 'c2';
											break;
										case 6:
											$paso['paso'] = 'c3';
											break;
										case 7:
											$paso['paso'] = 'c4';
											break;
									}
								}
								//print_r($esta);
						    	?>
							    	<div class="row shop-tracking-status">
				  						<div class="order-status">
		
							                <div class="order-status-timeline">
							                    <!-- class names: c0 c1 c2 c3 and c4 -->
							                    <div class="order-status-timeline-completion <?php echo $paso['paso']; ?>"></div>
							                </div>
							
							                <div class="image-order-status image-order-status-new <?php echo $esta['3']['estado']; ?> img-circle">
							                    <span class="status">
							                    	Colectado Proveeduria
							                    	<?php echo '<br />'.$esta['3']['usuario']; ?>
							                    	<?php echo '<br />'.$esta['3']['fecha'].' '.$esta['3']['hora']; ?>
							                    </span>
							                    <div class="icon"></div>
							                </div>
							                <div class="image-order-status image-order-status-active <?php echo $esta['4']['estado']; ?> img-circle">
							                    <span class="status">
							                    	En Orden de Compra
							                    	<?php echo '<br />'.$esta['4']['usuario']; ?>
							                    	<?php echo '<br />'.$esta['4']['fecha'].' '.$esta['5']['hora']; ?>
							                    </span>
							                    <div class="icon"></div>
							                </div>
							                <div class="image-order-status image-order-status-intransit <?php echo $esta['5']['estado']; ?> img-circle">
							                    <span class="status">
							                    	Recibido en Proveeduria
							                    	<?php echo '<br />'.$esta['5']['usuario']; ?>
							                    	<?php echo '<br />'.$esta['5']['fecha'].' '.$esta['5']['hora']; ?>
							                    </span>
							                    <div class="icon"></div>
							                </div>
							                <div class="image-order-status image-order-status-delivered <?php echo $esta['6']['estado']; ?> img-circle">
							                    <span class="status">
							                    	<?php echo $esta['6']['usuario'].'<br />'; ?>
							                    	<?php echo $esta['6']['fecha'].' '.$esta['6']['hora'].'<br />'; ?>
							                    	Enviado al Solicitante
							                    </span>
							                    <div class="icon"></div>
							                </div>
							                <div class="image-order-status image-order-status-completed <?php echo $esta['7']['estado']; ?> img-circle">
							                    <span class="status">							                    	
							                    	<?php echo $esta['7']['usuario'].'<br />'; ?>
							                    	<?php echo $esta['7']['fecha'].' '.$esta['7']['hora'].'<br />'; ?>
							                    	Recibido Solicitante
							                    </span>
							                    <div class="icon"></div>
							                </div>
							
							            </div>
						            </div>
            					</tr>
						    </thead>
						    <tbody>
		  				<?php foreach($req['Det'] as $det) { ?>
		  						<tr>
		  							<td><?php echo $det['prod_codigo']; ?></td>
		  							<td><?php echo $det['predreq_descripcion']; ?></td>
		   							<td><?php echo $det['predreq_cantidad']; ?></td>
		   							<td><?php echo $det['predreq_cantidad_aut']; ?></td>
		   							<td><?php echo $det['predreq_cantidad_aut_obs']; ?></td>
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
    	<?php $registros = $reqs['registros']; ?>
    	<?php if($registros > 10) { ?>
    	<?php
		// determine page (based on <_GET>)
		$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		$totalPag = ceil($registros/10);
		?>
       <div>
			<ul class="pagination">
				<?php if($page > 1 && $page <= $totalPag) { ?>
				<li class="<?php if($page == 1) echo 'disabled'; ?>">
					<a href="?c=req&a=emisor&id=6&page=<?php echo ($page-1); ?>">&laquo;</a>
				</li>
				<?php } ?>
				<?php
				for( $i=1; $i<=$totalPag; $i++ ) {
				?>
					<li class="<?php if($page == $i) echo 'active'; ?>">
						<a href="?c=req&a=emisor&id=6&page=<?php echo $i; ?>"><?php echo $i; ?></a>
					</li>
				<?php
				}
				?>
				<?php if($page < $totalPag) { ?>
				<li class="<?php if($page == $totalPag) echo 'disabled'; ?>">
					<a href="?c=req&a=emisor&id=6&page=<?php echo ($page+1); ?>">&raquo;</a>
				</li>
				<?php } ?>
			</ul>
	   </div>
	   <?php } ?>
    </div>
</div>