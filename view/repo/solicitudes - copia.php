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
    	$('#centrocosto').html('');
    	$('#centrocosto').hide();
		if(aData.length > 1) {
			var opcion='';
			opcion += '<option value="00">TODOS LOS CENTROS DE COSTO</option>';
			$.each(aData, function(i, item){
				opcion += '<option value="'+item.id_cc+'">( '+item.cc_codigo+' ) '+item.cc_descripcion+'</option>';
			});
			$("label#centros").after('<select class="form-control input-sm" id="centrocosto" name="centrocosto">'+opcion+'</select>');
		} else {
			$.each(aData, function(i, item){
				$("label#centros").after('<span class="form-control-static text-info">'+item.cc_descripcion+'</span><input type="hidden" id="centrocosto" name="centrocosto" value="'+item.id_cc+'" />');
			});
		}
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
      		CheckAjaxCall($(this).val());
    	} else {
      		$('#centrocosto').html('');
      		$('#centrocosto').hide();
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
  	$('a.pull-right.btn-link').live('click',function(){
  	  	jAlert($(this).text());
  	});
});
</script>
<h4 class="text-blue">Listado de Solicitudes</h4>
<form class="form-horizontal" role="form" id="frmVer"  method="post" action="?c=repo&a=solc&id=7&page=1">
<div class="form-group">
    <label for="inputEmail1" class="col-md-3 control-label">Empresa</label>
    <div class="col-md-9">
      	<?php if(count($emps) > 1) { ?>
		<select class="form-control input-sm" id="empresa" name="empresa">
			<option value="">-- Seleccione Empresa --</option>
			<?php foreach ($emps as $emp) { ?>
			<?php
		    if(isset($_POST['empresa']) && $_POST['empresa'] == $emp['id_empresa']) {
		    	$empresa = $emp['emp_nombre'];
		    }
		    ?>
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
    </div>
  </div>
  <div class="form-group">
    <label for="inputPassword1" class="col-md-3 control-label">Centro de Costo</label>
    <div class="col-md-7">
      	<label id="centros" class="control-label sr-only"></label>
    </div>
    <div class="col-md-2">
    	<button type="submit" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-search"></span>Mostrar</button>
    </div>
  </div>
  	<input type="hidden" id="c" name="c" value="solc" />
	<input type="hidden" id="a" name="a" value="crear" />
	<input type="hidden" id="idmod" name="idmod" value="<?php echo $_GET['id']; ?>" />
</form>
<?php
$registros = $solcs['registros'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalPag = ceil($registros/10);
?>
<div class="row">
	<div class="col-md-12">
		<?php if ($registros > 0) { ?>
		<span class="text-info"><?php echo '(<span id="empresa_span">'.$_POST['empresa'].'</span>)'.$empresa; ?></span>
		<?php } ?>
		<table class="table table-bordered table-condensed">
		  	<thead>
		  		<tr>
		  			<th>Numero</th>
		  			<th>Fecha</th>
		  			<th>Hora</th>
		  			<th>Usuario</th>
		  			<th>Estado</th>
		  		</tr>
		  	</thead>
		  	<tbody>
		  	<?php if ($registros > 0) {	?>
			<tr class="gridgray">
				<td colspan="5">Se encontraron <?php echo $registros; ?> registros, Actualmente mostrando p&aacute;gina <?php echo $page; ?> de <?php echo $totalPag; ?></td>
			</tr>
			<?php } ?>
		  	<?php foreach($solcs as $solc){  ?>  				
		  		<?php if(isset($solc['prehsol_numero'])) { ?>
		  		<?php
  				unset($esta);
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
		    	foreach ($solc['estados'] as $est) {
					//print_r($est);
					$esta[$est['prehsol_stat']]['fecha'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$est['prehsol_stat_fecha']; 
					$esta[$est['prehsol_stat']]['hora'] = $est['prehsol_stat_hora'];
					$esta[$est['prehsol_stat']]['usuario'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$est['prehsol_stat_usuario'];
					$esta[$est['prehsol_stat']]['estado'] = 'active';
					switch ($est['prehsol_stat']) {
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
		    	?>
	  			<tr>
	  				<td><?php echo $solc['prehsol_numero_sol']; ?></td>
	  				<td><?php echo $solc['prehsol_fecha']; ?></td>
	  				<td><?php echo $solc['prehsol_hora']; ?></td>
	  				<td><?php echo $solc['prehsol_usuario']; ?></td>
	  				<td>
		  				<?php echo $solc['nestado']; ?>
		  				<i class="pull-right glyphicon glyphicon-list"></i>&nbsp;&nbsp;&nbsp;
		  				<a href="#myModal<?php echo $solc['id_prehsol']; ?>" role="button" class="pull-right" data-toggle="modal">VER</a>
		  				<?php if ($permisos[0]['acc_xls'] == '1') { ?>
          					<i class="pull-right icon-ms-excel icon-large"></i><a class="pull-right btn-link">&nbsp;&nbsp;&nbsp;XLS&nbsp;</a>
          					<i class="pull-right icon-adobe-pdf icon-large"></i><a class="pull-right btn-link">&nbsp;PDF&nbsp;</a>
          				<?php }?>
	  				</td>
				</tr>
				<div id="myModal<?php echo $solc['id_prehsol']; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
								<h3 id="myModalLabel">Detalle para Solicitud <?php echo $solc['prehsol_numero_solc']; ?></h3>
							</div>
							<div class="modal-body">
								<div class="row">
									<div class="col-md-12">
										<div class="col-md-4"><span class="text-info">Codigo</span></div>
										<div class="col-md-4"><span class="text-info">Descripcion</span></div>
										<div class="col-md-4"><span class="text-info">Cantidad</span></div>
									</div>
								</div>
								<?php foreach($solc['Det'] as $det) { ?>
									<div class="row">
										<div class="col-md-12">
											<div class="col-md-4"><?php echo $det['prod_codigo']; ?></div>
											<div class="col-md-4"><?php echo $det['predsol_descripcion']; ?></div>
											<div class="col-md-4"><?php echo $det['predsol_cantidad']; ?></div>
										</div>
									</div>
								<?php } ?>
							</div>
							<div class="modal-footer">
								<br /><br />
								<div class="row shop-tracking-status">
				  						<div class="order-status">
				  						<?php print_r($esta); ?>
		
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
									<button class="btn" data-dismiss="modal" aria-hidden="true">CERRAR</button>
								</div>
							</div>
						</div>
					</div>
		  		<?php } ?>
		  	<?php } ?>
		  	</tbody>
		</table>
	</div>
</div>
<div class="row">
    <div class="col-md-12">
    	<?php $registros = $solc['registros']; ?>
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
					<a aria-label="Previous" href="?c=repo&a=solc&id=7&page=<?php echo ($page-1); ?>">
					<span aria-hidden="true">&laquo;</span>
					</a>
				</li>
				<?php } ?>
				<?php
				for( $i=1; $i<=$totalPag; $i++ ) {
				?>
					<li class="<?php if($page == $i) echo 'active'; ?>">
						<a href="?c=repo&a=solc&id=7&page=<?php echo $i; ?>"><?php echo $i; ?></a>
					</li>
				<?php
				}
				?>
				<?php if($page < $totalPag) { ?>
				<li class="<?php if($page == $totalPag) echo 'disabled'; ?>">
					<a aria-label="Next" href="?c=repo&a=solc&id=7&page=<?php echo ($page+1); ?>">
					<span aria-hidden="true">&raquo;</span>
					</a>
				</li>
				<?php } ?>
			</ul>
	   </nav>
	   <?php } ?>
    </div>
</div>