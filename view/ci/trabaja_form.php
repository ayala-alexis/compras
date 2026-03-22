<?php
//print_r($detas); 
//print_r($infohsol);
?>
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

function redirect (url) {
    var ua        = navigator.userAgent.toLowerCase(),
        isIE      = ua.indexOf('msie') !== -1,
        version   = parseInt(ua.substr(4, 2), 10);

    // Internet Explorer 8 and lower
    if (isIE && version < 9) {
        var link = document.createElement('a');
        link.href = url;
        document.body.appendChild(link);
        link.click();
    }

    // All other browsers can use the standard window.location.href (they don't lose HTTP_REFERER like Internet Explorer 8 & lower does)
    else { 
        window.location.href = url; 
    }
}
$(function(){

	$('[data-toggle="tooltip"]').tooltip();
	
	$('#frmEnvia').validate();
	
	$('#frmAuto').submit(function(e){
		$('body').removeClass('loaded');
	    return;
	    e.preventDefault();
	});

	$('a#btnImprimir').click(function(e) {
		//var href = $(this).attr('href');
	    $(this).attr('target', '_blank');
		window.open($(this).attr('href'));
		setTimeout(function() {
			redirect($("a#btnCancelar").attr('hrefe'));
		}, 2000);
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
    <div class="row">
        <div class="col-xs-12">
    		<div class="invoice-title">
    			<h3>Gestion de Compra</h3><h3 class="pull-right">Solicitud # <?php echo $infohsol[0]['ci_numero']; ?></h3>
    		</div>
    		<hr>
    		<div class="row">
    			<div class="col-xs-6">
    				<address>
        				<strong>Fecha :</strong><br>
    					<?php
    					setlocale(LC_TIME, "");
    					setlocale(LC_TIME, "es_ES");
    					echo iconv('ISO-8859-1', 'UTF-8', strftime('%A %d de %B, %Y %I:%M:%S %p', strtotime($infohsol[0]['ci_enc_fecha'] . ' ' . $infohsol[0]['ci_enc_hora'])));
    					echo '<br>';
    					echo 'Autorizado:';
    					echo '<b><p>'.$infohsol[0]['ci_autorizado'].' - '.$infohsol[0]['ci_autorizado_fecha_hora'].'</p></b>';
    					?>
    				</address>
    			</div>
    			<div class="col-xs-6 text-right">
    				<address>
    				<strong>Solicitado por:</strong><br>
    					<?php echo $infohsol[0]['emp_nombre'];?><br>
    					<?php echo $infohsol[0]['cc_descripcion'];?><br>
    					<a class="btn btn-lg btn-success" id="btnImprimir" name="btnImprimir" href="/sics/view/ci/PDF.php?id=<?php echo $infohsol[0]['id_ci']; ?>">
							<i class="glyphicon glyphicon-file"></i>&nbsp;IMPRIMIR PDF
						</a>
						<a class="btn btn-lg btn-default" id="btnCancelar" name="btnCancelar" href="?c=ci&a=gestor&id=12&ps=<?php echo $infohsol[0]['id_ci']; ?>" hrefe="?c=ci&a=trabajoe&id=12&ps=<?php echo $infohsol[0]['id_ci']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
							<i class="glyphicon glyphicon-log-out"></i> CANCELAR
						</a>
						<a id="btnRechaza" name="btnRechaza"  class="btn btn-lg btn-danger" href="?c=ci&a=deny&id=12&ps=<?php echo $infohsol[0]['id_ci']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
							<i class="glyphicon glyphicon-ban-circle"></i> DESISTIR
						</a>
    				</address>
    			</div>
    		</div>
    	</div>
    </div>
    
    <div class="row">
    	<div class="col-md-12">
    		<div class="panel panel-default">
    			<div class="panel-heading">
    				<h3 class="panel-title"><strong>Detalle de Consumo Interno</strong></h3>
    			</div>
    			<div class="panel-body">
    				<div class="table-responsive">
    					<table id="tablaPresol" class="table table-condensed">
    						<thead>
                                <tr>
        							<td class="text-left"><strong>CODIGO</strong></td>
        							<td class="text-left"><strong>CANTIDAD</strong></td>
        							<td class="text-left"><strong>DESCRIPCION</strong></td>
        							<td class="text-left"><strong>PRECIO UNITARIO</strong></td>
        							<td class="text-left"><strong>VALOR</strong></td>
                                </tr>
    						</thead>
    						<tbody>
    							<?php
    								$sumas = 0; 
    								foreach($detas as $deta) {
										$sumas += 1;
    							?>
    							<tr id="<?php echo $deta[0]; ?>">
    								<td><span id="ok_auth"></span><?php echo $deta['ci_det_cantidad'];?></td>
    								<td><?php echo $deta["prod_codigo"];?></td>
    								<td><?php echo $deta["prod_descripcion"];?></td>
    								<td>&nbsp;</td>
    								<td>&nbsp;</td>
    							</tr>
    							<?php } ?>
    						</tbody>
    						<tfoot>
    							<tr>
    								<th colspan="5">Total items <?php echo number_format($sumas, 0); ?></th>
    							</tr>
    						</tfoot>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
    <div class="row">
    	<div class="col-sm-12">
    		<div class="well">
    			<span>CONCEPTO DEL GASTO</span>
    			<br>
    			<?php
    			echo '<b>'.$infohsol[0]['ci_observacion'].'</b>';
    			?>
    		</div>
    	</div>
    </div>
    
</div>
<input type="hidden" value="<?php echo $infohsol[0]['id_ci']; ?>" id="id_ci" name="id_ci">
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