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
.modal-sm{
	max-width: 800px !important;
	width: 90% !important;
	min-width: auto !important;
}
#myModal table tr th,
#myModal table tr td{
	font-size: 12px;
	padding: 3px 5px;
}
select[name=producto]{
	-webkit-user-select: none; /* Safari */
  -ms-user-select: none; /* IE 10 and IE 11 */
  user-select: none; /* Standard syntax */
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


	$('#myModal').on('shown.bs.modal', function () {
	  $('[name=buscar]').focus();
	});

	$('[name=buscar]').keyup(function(e){
		var keycode = (e.keyCode ? e.keyCode : e.which);
	    //if (keycode == '13' || $(this).val().trim()!=='') {
	        var request = request_json_prod({
                    id: '<?php echo $infohsol['id_empresa']?>',
                    buscar: $('[name=buscar]').val(),
                    action: 'json.php?c=solcheque&a=json_productos',
                    method: 'POST'
            }, function(j){
            	var inp_cc = $('table tbody.productos');
		        inp_cc.html('');
	            if(j.exito){
	                $.each(j.cc, function(i, item){
	                    inp_cc.append("<tr>" + 
	                    				"<td><b>" + item.prod_codigo + "</b></td>" + 
	                    				"<td>" + 
	                    					"<u><b>" + item.sl_descripcion + "</b></u><br/>" +
	                    					item.prod_descripcion + 
	                    				"</td>" + 
	                    				"<td>" + 
	                    					"<button " +
	                    						"class='btn btn-primary btn-sm btn-product' "+ 
		                    					"data-prod='" + item.prod + "' " +
		                    					"data-cod='(" + item.prod_codigo + ") " + item.prod_descripcion + "' " +
	                    					">" +
	                    						"Seleccionar <i class='glyphicon glyphicon-ok'></i>" + 
	                    					"</button>" + 
	                    				"</td>" + 
                    				  "</tr>");	
	                    //inp_cc.append("<option value='" + item.id_cc + "'>" + item.cc_descripcion + "</option>");
	                });
	                    //$('.viewn3').show();
	            }else{
	                inp_cc.append("<tr><td>No hay datos</td></tr>");	
	            }
            });

	    //}
	});

	$(document).on('click','.btn-product',function(){
		var valor = $(this).attr('data-prod');
		var text = $(this).attr('data-cod');

		$('[name=producto]').val(valor);
		$('[name=prod]').val(text);

		$('#myModal').modal('hide');

		$('[name=buscar]').val('');
		$('table tbody.productos').html("<tr><td colspan='3'>Ingresar producto</td></tr>");
	});
});
</script>



<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Búsqueda de producto por código o descripción</h4>
        <div class="row">
	    	<div class="col-xs-12 col-sm-12 col-md-12">
	    		<input type="text" name="buscar" value="" placeholder="Ingresar código o descripción de producto" class="form-control" />
	    	</div>
	    </div>	
      </div>
      <div class="modal-body" style="min-height: 200px; max-height: 400px; overflow-y:auto ;">
        <table class="table table-condensed">
        	<thead>
        		<tr>
        			<th>Cód. Producto</th>
        			<th>Descripción</th>
        		</tr>
        	</thead>
        	<tbody class="productos"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

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
    			<!-- Button trigger modal -->
				
				<h4>Seleccione un Producto</h4>
				<form id="frmAsignaProd" name="frmAsignaProd" action="?c=solc&a=trabajoi&id=5&pd=<?php echo $_REQUEST['pd']; ?>" method="post">
             	<div class="input-group">
                 	<span class="input-group-addon">Producto:</span>
					<input type="hidden" name="producto" id="producto"  required/>
					<input type="text" name="prod" value="" id="prod" class="form-control" title="Clic para buscar producto" data-toggle="modal" data-target="#myModal" readonly/>
					<span class="input-group-btn">
						<button type="button" class="btn btn-default" data-toggle="modal" data-target="#myModal">
						  Buscar <i class='glyphicon glyphicon-search'></i>
						</button>
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
<script type="text/javascript" src="js/js.js?v=<?php echo date('His')?>"></script>
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