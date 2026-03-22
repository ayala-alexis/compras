<style>
.panel > .table-responsive {
    height: 350px;
    overflow: auto;
}
</style>
<script>
$(function(){
	 $("a").popover();
	 $('a.btn-danger').on('click', function(e) {
		 var link = $(this).attr('href');
		 $('<div></div>').appendTo('body')
		    .html('<div><h6>ESTA SEGURO QUE QUIERE DESISTIR LA SOLICITUD?</h6></div>')
		    .dialog({
		    modal: true,
		    title: 'DESISTIR SOLICITUD DE CONSUMO INTERNO',
		    zIndex: 10000,
		    autoOpen: true,
		    width: 'auto',
		    resizable: false,
		    buttons: {
		        Yes: {
			        text: "Desistir",
			        click: function () {
		            	window.location = link;
		        	}
		        },
		        No: function () {
		            $(this).dialog("close");
		        }
		    },
		    close: function (event, ui) {
		        $(this).remove();
		    }
		});
		e.preventDefault();
		return false;
	 });

	function doFunctionForYes() {
	    alert("Yes");
	}

	function doFunctionForNo() {
	    alert("No");
	}
});
</script>
<?php
$sql_g = "Select * from ci_generado where id_generado=".$_GET['ig'];
$run_g = $db->ejecutar($sql_g);
$row_g = mysqli_fetch_array($run_g); 
?>
<h4 class="text-blue"><?php echo $row_g['per_descripcion']; ?></h4>

<div class="panel panel-info">
    <div class="panel-heading">
    	<h5 class="text-center">CONSUMOS GENERADOS</h5>
    </div>
    <div class="table-responsive">
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>Opciones</th>
					<th>Numero</th>
					<th>Centro Costo</th>
					<th>Fecha</th>
					<th>Observacion</th>
				</tr>
			</thead>
			<tbody>
				<?php $cuantos = 0; ?>
				<?php 
				foreach($solcs as $solc) {
				?>
				<tr>
					<td> 
						<!-- 
						<a class="btn btn-sm btn-info" href="?c=ci&a=detalle&id=12&ig=<?php //echo $solc['id_generado']; ?>">
							<i class="glyphicon glyphicon-search"></i>&nbsp;Ver
						</a>
						-->
						<a class="btn btn-sm btn-default" href="/sics/view/ci/PDF.php?id=<?php echo $solc['id_ci']; ?>">
							<i class="glyphicon glyphicon-file"></i>&nbsp; Imprimir
						</a>
					</td>
					<td><?php echo $solc['ci_numero']; ?></td>
					<td> <?php echo '( '.$solc['id_cc'].' ) '.$solc['cc_descripcion']; ?></td>
					<td><?php echo $solc['ci_enc_fecha']; ?></td>
					<td><?php echo $solc['ci_observacion']; ?></td>
				</tr>
				<?php $cuantos++; ?>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="panel-footer"><a class="btn btn-success" href="?c=ci&a=gestor&id=12"><< REGRESAR</a></div>
</div>