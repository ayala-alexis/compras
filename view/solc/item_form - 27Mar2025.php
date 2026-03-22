<?php
//print_r($detas); 
//print_r($infohsol);
//print_r($prods);
?>
<style>
@media(max-width:991px){
#product-title
{
  text-align:center;
}
}
</style>
<script>
function GetURLParameter(sParam){
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
}
$(function(){

	$("#frmAsignaProd").validate();
	
	$('#frmAuto').submit(function(e){
		$('body').removeClass('loaded');
	    return;
	    e.preventDefault();
	});

	$('#sublinea').change(function(){
		window.location = "?c=solc&a=trabajoi&id=5&pd=<?php echo $_REQUEST['pd']; ?>&s=" + $(this).val();
	});
});
</script>
<div class="container-fluid">
	<!-- Start Page Loading -->
    <div id="loader-wrapper">
    	<h1>Espere...</h1>
        <div id="loader"></div>        
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
    </div>
    <div class="row">
    	<div class="col-xs-12 col-sm-12 col-md-12">
    		<div class="well row">
    			<h4>Seleccione Sublinea</h4>
				<form id="frmAsignaSub" name="frmAsignaSub" action="?c=solc&a=trabajoi&id=5&pd=<?php echo $_REQUEST['pd']; ?>" method="post">
					<div class="input-group">
	                 	<span class="input-group-addon">Sublinea:</span>
						<select name="sublinea" id="sublinea" class="form-control" required>
							<?php
							foreach($subls as $sl) {
								if($sl['selected']==1){
									echo '<option value="'.$sl['sublinea'].'" selected>'.$sl['desc_sublinea'].' ('.$sl['sublinea'].')</option>';
								}else{
									echo '<option value="'.$sl['sublinea'].'">'.$sl['desc_sublinea'].' ('.$sl['sublinea'].')</option>';
								}
							} 
							?>
						</select>
					</div>
				</form>

				<h4>Seleccione un Producto</h4>
				<form id="frmAsignaProd" name="frmAsignaProd" action="?c=solc&a=trabajoi&id=5&pd=<?php echo $_REQUEST['pd']; ?>" method="post">
             	<div class="input-group">
                 	<span class="input-group-addon">Producto:</span>
					<select name="producto" id="producto" class="form-control" required>
						<?php
						foreach($prods as $prod) {
							$datos = array();
							array_push($datos, $prod['prod_codigo']);
							array_push($datos, $prod['prod_descripcion']);
							array_push($datos, $prod['sl_linea']);
							array_push($datos, $prod['sl_sublinea']);
							array_push($datos, $prod['gas_tit_codigo']);
							array_push($datos, $prod['gas_det_codigo']);
							echo '<option value="'.base64_encode(serialize($datos)).'">('.$prod['prod_codigo'].') '.$prod['prod_descripcion'].'</option>';
						} 
						?>
					</select>
					<span class="input-group-btn">
						<?php if(empty($detas['predsol_observacion'])) { ?>
						<input type="hidden" value="<?php echo htmlspecialchars($detas['predsol_descripcion']); ?>" id="observacion" name="observacion">
						<?php } else { ?>
						<input type="hidden" value="<?php echo htmlspecialchars($detas['predsol_observacion']); ?>" id="observacion" name="observacion">
						<?php } ?>
						<input type="hidden" value="producto" id="accion" name="accion">
						<input type="hidden" name="ps" id="ps" value="<?php echo $infohsol['prehsol_numero']; ?>">
						<input type="hidden" name="cs" id="cs" value="<?php echo $infohsol['id_cc']; ?>">
						<input type="hidden" name="es" id="es" value="<?php echo $infohsol['id_empresa']; ?>">
						<button class="btn btn-success" type="submit"><i class="glyphicon glyphicon-pushpin"></i>&nbsp;Asignar Producto</button>
        				<a href="?c=solc&a=trabajoia&id=5&pd=<?php echo $_REQUEST['pd']; ?>" class="btn btn-info"><i class="glyphicon glyphicon-plus"></i>&nbsp;Crear Codigo</a>
        				<a href="?c=solc&a=trabajo&id=5&ps=<?php echo $infohsol['prehsol_numero']; ?>&cs=<?php echo $infohsol['id_cc']; ?>&es=<?php echo $infohsol['id_empresa']; ?>" class="btn btn-default"><i class="glyphicon glyphicon-log-out"></i>&nbsp;Atras</a>
      				</span>
				</div>
				</form>
			</div>
   		</div>
    </div>   
    <div class="row">
		<div class="col-xs-12 col-sm-12 col-md-6">
			<h4>Item</h4>
			<h5 id="product-title">
				<?php 
				if(empty($detas['prod_codigo'])) {
					echo 'No se ha definido un producto';
				} else {
					echo $detas['prod_codigo'];
				}
				?>
			</h5>
			<h4>Descripcion</h4>
			<p><b><?php echo $detas['predsol_descripcion']; ?></b></p>
			<h4>Cantidad solicitada</h4>
			<p><b><?php echo $detas['predsol_cantidad']; ?></b></p>
			<h4>Observacion</h4>
			<p><b><?php echo $detas['predsol_observacion']; ?></b></p>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-6">
			<!-- PRECIO DE ITEM -->
			<div class="row">
				<form id="frmAssignPrecio" name="frmAssignPrecio" action="?c=solc&a=trabajoi&id=5&pd=<?php echo $_REQUEST['pd']; ?>" method="post">
				<div class="col-xs-8">
					<div class="form-group">
						<label for="valor">Precio Unitario</label>
						<div class="input-group">
							<span class="input-group-addon">$</span>
						  	<input type="number" min=0.00 step="any" name="valor" id="valor" class="form-control" aria-label="Valor">
						</div>
					</div>
				</div>
				<div class="col-xs-4">
					<div class="form-group">
						<label for="input-submit">&nbsp;</label>
						<div class="input-group">
						<input type="hidden" value="precio" id="accion" name="accion">
						<input type="hidden" name="cantidad" id="cantidad" value="<?php echo $detas['predsol_cantidad']; ?>">
						<input type="hidden" name="ps" id="ps" value="<?php echo $infohsol['prehsol_numero']; ?>">
						<input type="hidden" name="cs" id="cs" value="<?php echo $infohsol['id_cc']; ?>">
						<input type="hidden" name="es" id="es" value="<?php echo $infohsol['id_empresa']; ?>">
				  		<button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-ok"></i>&nbsp;Asignar Precio</button>
				  		</div>
				  	</div>
				</div>
				</form>
			</div>
			<div class="row">
				<div class="col-xs-6">
					<h4>Precio Unitario</h4>
					<p><b><?php echo $detas['predsol_prec_uni']; ?></b></p>
				</div>
				<div class="col-xs-6">
					<h4>Precio Total</h4>
					<p><b><?php echo $detas['predsol_total']; ?></b></p>
				</div>
			</div>
			<!-- CENTRO DE COSTO DE ITEM -->
			<div class="row">
				<form id="frmAssignCC" name="frmAssignCC" action="?c=solc&a=trabajoi&id=5&pd=<?php echo $_REQUEST['pd']; ?>" method="post">
				<div class="col-xs-8">
					<label for="cc">Centro de Copsto</label>
				    <select id="cc" name="cc" class="form-control input-sm" required>
				    	<option value="0">Quitar Centro de Costo</optoin>
					   	<?php foreach ($ccs as $cc) { ?>
			    		<option value="<?php echo $cc['id_cc']; ?>"><?php echo $cc['cc_descripcion']; ?></option>
			    		<?php } ?>
					</select>
				</div>
				<div class="col-xs-4">
					<div class="form-group">
						<label for="input-submit">&nbsp;</label>
						<div class="input-group">
						<input type="hidden" value="cc" id="accion" name="accion">
						<input type="hidden" name="ps" id="ps" value="<?php echo $infohsol['prehsol_numero']; ?>">
						<input type="hidden" name="cs" id="cs" value="<?php echo $infohsol['id_cc']; ?>">
						<input type="hidden" name="es" id="es" value="<?php echo $infohsol['id_empresa']; ?>">
				  		<button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-ok"></i>&nbsp;Asignar Centro de Costo</button>
				  		</div>
				  	</div>
				</div>
				</form>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<h4>Centro de Costo Actual</h4>
					<p><b><?php echo $ccd[$detas['id_cc']]["cc_descripcion"]; ?></b></p>
				</div>
			</div>
			<!-- PROVEEDOR DE ITEM -->
			<div class="row">
				<form id="frmAssignProvee" name="frmAssignProvee" action="?c=solc&a=trabajoi&id=5&pd=<?php echo $_REQUEST['pd']; ?>" method="post">
				<div class="col-xs-8">
					<label for="proveedor">Proveedor</label>
				    <select id="proveedor" name="proveedor" class="form-control input-sm" required>
				    	<option value="0">Quitar Proveedor</option>
					   	<?php foreach ($provs as $prov) { ?>
			    		<option value="<?php echo $prov['id_proveedor']; ?>"><?php echo $prov['prov_nombre']; ?></option>
			    		<?php } ?>
					</select>
				</div>
				<div class="col-xs-4">
					<div class="form-group">
						<label for="input-submit">&nbsp;</label>
						<div class="input-group">
						<input type="hidden" value="proveedor" id="accion" name="accion">
						<input type="hidden" name="ps" id="ps" value="<?php echo $infohsol['prehsol_numero']; ?>">
						<input type="hidden" name="cs" id="cs" value="<?php echo $infohsol['id_cc']; ?>">
						<input type="hidden" name="es" id="es" value="<?php echo $infohsol['id_empresa']; ?>">
				  		<button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-ok"></i>&nbsp;Asignar Proveedor</button>
				  		</div>
				  	</div>
				</div>
				</form>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<h4>Proveedor Actual</h4>
					<p><b><?php echo $prv[$detas['id_proveedor']]["prov_nombre"]; ?></b></p>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
(function($) {
    $.fn.queued = function() {
        var self = this;
        var func = arguments[0];
        var args = [].slice.call(arguments, 1);
        return this.queue(function() {
            $.fn[func].apply(self, args).dequeue();
        });
    };
}(jQuery));
</script>