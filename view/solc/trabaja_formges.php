<style>
.invoice-title h2, .invoice-title h3 {
    display: inline-block;
}

.table > tbody > tr > .no-line {
    border-top: none;
}

.table > thead > tr > .no-line {
    border-bottom: none;
}

.table > tbody > tr > .thick-line {
    border-top: 2px solid;
}

#frmEnvia {
	padding: 0;
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

	$('[data-toggle="tooltip"]').tooltip();

	$('#negarCat').live('click', function(event){
		event.preventDefault();
	    var url = $(this).attr('href');
		if( $('#prehsol_aprobacion_gestion').val().trim().length == 0 ) {
			notie.alert('error', 'debe digitar una observacion!', 3);
	    } else {
	    	var confirm_box = confirm('Esta seguro de negar solicitud?');
		    if (confirm_box) {
		    	var input = $("<input>").attr("type", "hidden").attr("name", "prehsol_aprobacion_gestion").val($('#prehsol_aprobacion_gestion').val().trim());
		    	$("#negarForm").attr("action", url); 
		    	$('#negarForm').append($(input));
		    	$('#negarForm').submit();
		       //window.location = url;
		    }
	    }
	});
		
	
	$('#frmApruebaCat').validate({
		rules:{
			prehsol_aprobacion_gestion : {
				required: true
			}
		},
		submitHandler: function(form) {
			if( $('#prehsol_aprobacion_gestion').val().trim().length == 0 ) {
				notie.alert('error', 'debe digitar una observacion!', 3);
		    } else {
				$('body').removeClass('loaded');
				form.submit();
		    }
		}
	});
	
});
</script>
<div class="container-fluid">
	<!-- Start Page Loading -->
    <div id="loader-wrapper">
    	<h1>Espere... procesando peticion.</h1>
        <div id="loader">
        </div>        
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
    </div>
    <!-- End Page Loading -->
    <div class="row">
        <div class="col-xs-12">
    		<div class="invoice-title">
    			<h3>Gestion de Compra por Convergencia</h3><h3 class="pull-right">Solicitud # <?php echo $infohsol[0]['prehsol_numero_sol']; ?></h3>
    		</div>
    		<hr>
    		<div class="row">
    			<div class="col-xs-6">
    				<address>
        				<strong>Fecha de solicitud :</strong><br>
    					<?php
    					setlocale(LC_TIME, "");
    					setlocale(LC_TIME, "es_ES");
    					echo iconv('ISO-8859-1', 'UTF-8', strftime('%A %d de %B, %Y %I:%M:%S %p', strtotime($infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora'])));
    					echo '<br>Observaciones:';
    					echo '<b>'.$infohsol[0]['prehsol_obs1'].'</b>';
    					echo 'Aprobado:';
    					echo '<b><p>'.$infohsol[0]['prehsol_aprobacion_usuario'].'</p>'.$infohsol[0]['prehsol_aprobacion'].'</b>';
    					if(!empty($infohsol[0]['prehsol_aprobacion_categoria'])){
							echo 'Aprobado Categoria:';
							echo '<b><p>'.$infohsol[0]['prehsol_aprobacion_categoria_usuario'].' > '.$infohsol[0]['prehsol_aprobacion_categoria'].'</p></b>';
						}
    					?>
    				</address>
    			</div>
    			<div class="col-xs-6 text-right">
    				<address>
    				<strong>Solicitado por:</strong><br>
    					<?php echo $infohsol[0]['emp_nombre'];?><br>
    					<?php echo $infohsol[0]['cc_descripcion'];?><br>
    					<?php 
    					$cats = qCategoria($infohsol[0]['id_categoria']);
    					echo '<h3>'.$cats[0][0].'</h3>';
    					?>
    				</address>
    			</div>
    		</div>
    	</div>
    </div>
    
    <div class="row">
    	<div class="col-md-12">
    		<form class="form-horizontal" id="frmApruebaCat" name="frmApruebaCat" method="post" action="?c=solc&a=aprbge">
			  <div class="form-group">
			    <label for="prehsol_aprobacion_categoria" class="col-sm-2 control-label">Observaciones</label>
			    <div class="col-sm-10">
			      <textarea class="form-control" id="prehsol_aprobacion_gestion" name="prehsol_aprobacion_gestion"></textarea>
			    </div>
			  </div>
			  <div class="form-group">
			    <div class="col-sm-offset-2 col-sm-10">
					<input type="hidden" value="<?php echo $infohsol[0]['id_prehsol']; ?>" id="id_prehsol" name="id_prehsol">
			      	<button type="submit" class="btn btn-success" id="btnEnviarAutoCat" name="btnEnviarAutoCat">AUTORIZAR<br>COMPRA</button>
			      	<a href="?c=solc&a=naprbge&h=<?php echo $infohsol[0]['id_prehsol']; ?>" class="btn btn-danger" id="negarCat" name="negarCat">DESISTIR<br>COMPRA</a>
			      	<a href="?c=solc&a=gest" class="btn btn-default" id="backCat" name="backCat">VOLVER AL<br>LISTADO</a>
			    </div>
			  </div>
			</form>
    	</div>
    </div>
    
    <div class="row">
    	<div class="col-md-12">
    		<div class="panel panel-default">
    			<div class="panel-heading">
    				<h3 class="panel-title"><strong>Detalle de solicitud</strong></h3>
    			</div>
    			<div class="panel-body">
    				<div class="table-responsive">
    					<table id="tablaPresol" class="table table-condensed">
    						<thead>
                                <tr>
        							<td><strong>Descripcion</strong></td>
        							<td class="text-center"><strong>Unidad</strong></td>
        							<td class="text-center"><strong>Cantidad<br>Solicitada</strong></td>
                                </tr>
    						</thead>
    						<tbody>
    							<?php
    							$sumas = 0; 
    							foreach($detas as $deta) {
									$sumas = ($sumas + $deta['predsol_total']);
    							?>
	    							<tr id="<?php echo $deta[0]; ?>">
	    								<td><?php echo $deta[11];?></td>
	    								<td class="text-center"><?php echo $deta[7];?></td>
	    								<td class="text-center"><?php echo $deta[4];?></td>
	    							</tr>
    							<?php } ?>
    						</tbody>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
    
    <form id="negarForm" name="negarForm" method="post" action="">
    </form>
    
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