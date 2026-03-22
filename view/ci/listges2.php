<style>
.panel > .table-responsive {
    height: 200px;
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
<h4 class="text-blue">Listado de Solicitudes de Consumo Interno Para Gestion</h4>
<div class="panel panel-danger">
    <div class="panel-heading">
    	<h4 class="text-center">NUEVAS SOLICITUDES</h4>
    </div>
    <div class="table-responsive">
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>Numero</th>
					<th>Centro Costo</th>
					<th>Fecha</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php $cuantos = 0; ?>
				<?php 
				foreach($solcs as $solc) {
					if($solc['ci_estado'] == 2 || $solc['ci_estado'] == 1){
				?>
				<tr>
					<td><?php echo $solc['ci_numero']; ?></td>
					<td> <?php echo '( '.$solc['id_cc'].' ) '.$solc['cc_descripcion']; ?></td>
					<td><?php echo $solc['ci_enc_fecha']; ?></td>
					<td>
						<a class="btn btn-sm btn-danger" href="?c=ci&a=deny&id=12&ps=<?php echo $solc['id_ci']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
							<i class="glyphicon glyphicon-remove"></i>&nbsp;Rechazar
						</a>
						<a class="btn btn-sm btn-success" href=/sics/view/ci/PDF.php?id=<?php echo $solc['id_ci']; ?>">
							<i class="glyphicon glyphicon-file"></i>&nbsp; Imprimir Solicitud
						</a>
						<a class="btn btn-sm btn-success" href="?c=ci&a=trabajo&id=12&ps=<?php echo $solc['ci_numero']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
							<i class="glyphicon glyphicon-envelope"></i>&nbsp; Revisar Solicitud
						</a>
					</td>
				</tr>
				<?php $cuantos++; ?>
				<?php } } ?>
			</tbody>
		</table>
	</div>
	<div class="panel-footer"><?php echo $cuantos.' '; ?>  solicitud(es) pendiente(s)</div>
</div>

<div class="panel panel-info">
    <div class="panel-heading">
    	<h4 class="text-center">SOLICITUDES EN PROCESO</h4>
    </div>
    <div class="table-responsive">
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>Numero</th>
					<th>Centro Costo</th>
					<th>Fecha</th>
					<th>Visto</th>
					<!-- <th>En Revision</th> -->
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php $cuantos = 0; ?>
				<?php 
				foreach($solcs as $solc) {
					if($solc['ci_estado'] == 3){
				?>
				<tr>
					<td><?php echo $solc['ci_numero']; ?></td>
					<td> <?php echo '( '.$solc['id_cc'].' ) '.$solc['cc_descripcion']; ?></td>
					<td><?php echo $solc['ci_enc_fecha']; ?></td>
					<td><?php echo $solc['ci_revision_fecha'].' - '.$solc['ci_revision']; ?></td>
					<!-- <td><?php //echo $solc['ci_revisando']; ?></td> -->
					<td>
						<!-- 
						<a class="btn btn-sm btn-danger" href="?c=ci&a=deny&id=12&ps=<?php echo $solc['id_ci']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
							<i class="glyphicon glyphicon-remove"></i>&nbsp;Rechazar
						</a>
						-->
						<a class="btn btn-sm btn-info" href="/sics/view/ci/PDF.php?id=<?php echo $solc['id_ci']; ?>">
							<i class="glyphicon glyphicon-file"></i>&nbsp; Imprimir
						</a>
					</td>
				</tr>
				<?php $cuantos++; ?>
				<?php } } ?>
			</tbody>
		</table>
	</div>
	<div class="panel-footer"><?php echo $cuantos.' '; ?>  solicitud(es) pendiente(s)</div>
</div>