<script>
$(function(){
	$('#btnDispo').on('click', function(e){
		$('body').removeClass('loaded');
		return;
		e.preventDefault();
	});
	$('#btnEnviar').on('click', function(e){
		$('body').removeClass('loaded');
		return;
		e.preventDefault();
	});
});
</script>
<div class="container-fluid">
	<!-- Start Page Loading -->
    <div id="loader-wrapper">
    	<h1>Espere...</h1>
        <div id="loader">
        </div>        
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
    </div>
    <!-- End Page Loading -->
	<?php
	$stop = 0;
	$msg = '';
	$prvxite = 0;
	$prvtodos = 0;
	$prvxprov = 0;
	foreach ($ds as $d) {
		$prvtodos += 1;
		if($d['id_proveedor'] <= 0) {
			$prvxite += 1;
		}
		if($d['id_proveedor'] > 0) {
			$prvxprov += 1;
		}
		if (empty($d['prod_codigo'])) {
			$stop = 1;
			$msg .= '<p class="list-group-item-text">No se definido Codigo para Item :<br>'.$d['predsol_descripcion'].'</p>';
		}
		if($d['predsol_prec_uni'] <= 0) {
			$stop = 1;
			$msg .= '<p class="list-group-item-text">No se ha definido Precio($0.00) para Item :<br>'.$d['predsol_descripcion'].'</p>';
		}
		$gastod = str_pad($d['predsol_titgas'],2,'0',STR_PAD_LEFT).str_pad($d['predsol_detgas'],2,'0',STR_PAD_LEFT);
		if ($d['emp_usa_presupuesto'] == '1' && $gastod == "0000") {
			$stop = 1;
			$msg .= '<p class="list-group-item-text">Producto No tiene asignacion de gasto, contacte a Proveeduria :<br>'.$d['predsol_descripcion'].'</p>';
		}
	}
	if($prvxite != $prvtodos && $prvxite > 0){
		$stop = 1;
		$stop1 = 1;
		$msg .= '<h4 class="list-group-item-text"><b>No se ha defindo un proveedor para todos los items</b></h4>';
	} 
	?>
    <div class="row">
    
        <div class="col-xs-12">
        
		  <div class="list-group">
		    <div class="list-group-item clearfix">
		    	<span class="pull-right">
			        <?php if($stop==1) {?>
				        <a href="?c=solc&a=trabajo&id=5&ps=<?php echo $hs['prehsol_numero']; ?>&cs=<?php echo $hs['id_cc']; ?>&es=<?php echo $hs['id_empresa']; ?>" class="btn btn-info">
					        <i class="glyphicon glyphicon-edit"></i>
					        &nbsp;Editar Solicitud
				        </a>
				        <button class="btn btn-warning">
				          <span class="glyphicon glyphicon-warning-sign"></span>
				        </button>
			        <?php } else {?>
				        <button class="btn btn-lg btn-success">
				          <span class="glyphicon glyphicon-ok"></span>
				        </button>
			        <?php } ?>
		      	</span>
		      <h4 class="list-group-item-heading">Verificacion de items trabajados</h4>
		      <p><?php echo $msg; ?></p>
		    </div>
		    
		    <div class="list-group-item clearfix">
		    	<span class="pull-right">
			        <?php if($stop2 == 1) {?>
				        <a id="btnDispo" name="btnDispo" href="/sics/class/PHPMailer/send3.php?<?php echo $hs['id_prehsol']; ?>" class="btn btn-info">
					        <i class="glyphicon glyphicon-envelope"></i>
					        &nbsp;Enviar Disponibilidad a Solicitante
				        </a>
				        <button class="btn btn-warning">
				          <span class="glyphicon glyphicon-warning-sign"></span>
				        </button>
			        <?php } else {?>
				        <button class="btn btn-lg btn-success">
				          <span class="glyphicon glyphicon-ok"></span>
				        </button>
			        <?php } ?>
		      	</span>
		      <h4 class="list-group-item-heading">Verifica disponibilidad de gasto</h4>
		      <p><?php echo $msg1; ?></p>
		    </div>
		  </div>
		  
		  <?php 
		  if($stop1==0 && $stop2==0){
			$tipos[2] = "Presupuesto";
			$tipos[3] = "Inventario";
			$tipos[4] = "Activo Fijo";
		  ?>
		  	<div class="list-group-item clearfix">
				<h4 class="list-group-item-heading">Solicitud # <?php echo $_REQUEST['ps']; ?></h4>
				<?php if($prvxprov <= 0) { ?>
			    <p>Proveedor : <?php echo $_REQUEST['proveedor']; ?></p>
			    <?php } else { ?>
			    <p>Proveedor : Proveedor por Item</p>
			    <?php } ?>
			    <p>Tipo      : <?php echo $tipos[$_REQUEST['tipogasto']]; ?></p>
			    
			    <span class="pull-right">
					<button class="btn btn-lg btn-success">
				    	<span class="glyphicon glyphicon-ok"></span>
				    </button>
			    </span>
			    <h4 class="list-group-item-heading">Todo listo.</h4>
			    <?php if($prvxprov <= 0) { ?>
				    <a id="btnEnviar" name="btnEnviar" href="?c=solc&a=creao&id=5&ps=<?php echo $_REQUEST['ps']; ?>&pr=<?php echo $_REQUEST['proveedor']; ?>&es=<?php echo $_REQUEST['es']; ?>&tg=<?php echo $_REQUEST['tipogasto']; ?>" class="btn btn-success">
						<span class="glyphicon glyphicon-send"></span>&nbsp;Crear Orden de Compra
					</a>
				<?php } else { ?>
					<a id="btnEnviar" name="btnEnviar" href="?c=solc&a=creao2&id=5&ps=<?php echo $_REQUEST['ps']; ?>&pr=<?php echo $_REQUEST['proveedor']; ?>&es=<?php echo $_REQUEST['es']; ?>&tg=<?php echo $_REQUEST['tipogasto']; ?>" class="btn btn-success">
						<span class="glyphicon glyphicon-send"></span>&nbsp;Crear Orden de Compra segun Item
					</a>
				<?php } ?>
				<a href="?c=solc&a=trabajo&id=5&ps=<?php echo $_REQUEST['pe']; ?>&cs=<?php echo $_REQUEST['cs']; ?>&es=<?php echo $_REQUEST['es']; ?>" class="btn btn-default">
					<span class="glyphicon glyphicon-log-out"></span>&nbsp;Cancelar
				</a>
			</div>
		  <?php } ?>
  		</div>
  	</div>
  </div>