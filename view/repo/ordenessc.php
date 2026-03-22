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
				$("label#centros").after('<span class="text-info">'+item.cc_descripcion+'</span><input type="hidden" id="centrocosto" name="centrocosto" value="'+item.id_cc+'" />');
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
<h4 class="text-blue">Listado de Ordenes de Compra por Solicitud de Compra</h4>
<form class="form-horizontal" role="form" id="frmVer"  method="post" action="?c=repo&a=ordensc&id=7&page=1">
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
$totalPag = ceil($registros/15);
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
			<?php //print_r($solcs); ?>
		  	<?php foreach($solcs as $solc){  ?>
		  		<?php if(isset($solc['predsol_numero_oc'])) { ?>
		  			<tr>
		  				<td><?php echo $solc['predsol_numero_oc']; ?></td>
		  				<td><?php echo $solc['predsol_fecha_oc']; ?></td>
		  				<td><?php echo $solc['predsol_hora_oc']; ?></td>
		  				<td><?php echo $solc['predsol_usuario_oc']; ?></td>
		  				<td>
			  				<?php echo $solc['nestado']; ?>
			  				<div class="btn-group btn-group-xs pull-right" role="group" aria-label="...">
			  				<a href="#myModal<?php echo $solc['predsol_numero_oc']; ?>" role="button" class="btn btn-default" data-toggle="modal">
			  					<i class="glyphicon glyphicon-list"></i> VER
			  				</a>
			  				<?php 
			  				//if ($permisos[0]['acc_xls'] == '1') { ?>
	          					<a class="btn btn-default" href="json.php?c=solc&a=ocpre_gpdf&id=7&poc=<?php echo $solc['predsol_numero_oc']; ?>&p=<?php echo $solc['id_proveedor']; ?>&cia=<?php echo $solc['id_empresa']; ?>&r=1">
	          						<i class="glyphicon glyphicon-usd"></i> GASTO CC</a>
	          					<!--<a class="btn btn-default" href="json.php?c=req&a=ocpre_rpdf&id=6&poc=<?php //echo $solc['predreq_numero_oc']; ?>&p=<?php //echo $solc['id_proveedor']; ?>&cia=<?php //echo $solc['id_empresa']; ?>&r=1">
	          						<i class="icon-adobe-pdf icon-large"></i> PDF
	          					</a>-->
	          				<?php //}?>
	          				</div>
		  				</td>
					</tr>
					<div id="myModal<?php echo $solc['predsol_numero_oc']; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h3 id="myModalLabel">Detalle para Orden <?php echo $solc['predsol_numero_oc']; ?></h3>
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
											<div class="col-md-4"><?php echo $det['CODAS400']; ?></div>
											<div class="col-md-4"><?php echo $det['DESCRIP']; ?></div>
											<div class="col-md-4"><?php echo $det['CANT']; ?></div>
										</div>
									</div>
									<?php } ?>
								</div>
								<div class="modal-footer">
									<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
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
    	<?php if($registros > 10) { ?>
       <nav>
			<ul class="pagination">
				<?php if($page > 1 && $page <= $totalPag) { ?>
				<li class="<?php if($page == 1) echo 'disabled'; ?>">
					<a aria-label="Previous" href="?c=repo&a=ordensc&id=7&page=<?php echo ($page-1); ?>">
						<span aria-hidden="true">&laquo;</span>
					</a>
				</li>
				<?php } ?>
				<?php
				for( $i=1; $i<=$totalPag; $i++ ) {
				?>
					<li class="<?php if($page == $i) echo 'active'; ?>">
						<a href="?c=repo&a=ordensc&id=7&page=<?php echo $i; ?>"><?php echo $i; ?></a>
					</li>
				<?php
				}
				?>
				<?php if($page < $totalPag) { ?>
				<li class="<?php if($page == $totalPag) echo 'disabled'; ?>">
					<a aria-label="Next" href="?c=repo&a=ordensc&id=7&page=<?php echo ($page+1); ?>">
						<span aria-hidden="true">&raquo;</span>
					</a>
				</li>
				<?php } ?>
			</ul>
	   </nav>
	   <?php } ?>
    </div>
</div>