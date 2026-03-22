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
<h4 class="text-blue">Listado de Periodos Generados de Solicitudes de Consumo</h4>

<div class="panel panel-info">
    <div class="panel-heading">
    	<h4 class="text-center">PERIODOS GENERADOS</h4>
    </div>
    <div class="table-responsive">
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>Opciones</th>
					<th>Fecha de Generacion</th>
					<th>Descripcion</th>
					<th>Empresa</th>
					<th>Cantidad de Consumos</th>
				</tr>
			</thead>
			<tbody>
				<?php $cuantos = 0; ?>
				<?php 
				foreach($solcs as $solc) {
				?>
				<tr>
					<td> 
						<a class="btn btn-sm btn-info" href="?c=ci&a=detalle&id=12&ig=<?php echo $solc['id_generado']; ?>">
							<i class="glyphicon glyphicon-search"></i>&nbsp;Ver
						</a>
						<a class="btn btn-sm btn-success" href="json.php?c=ci&a=xls_d&id=12&ig=<?php echo $solc['id_generado']; ?>">
							<i class="glyphicon glyphicon-file"></i>&nbsp; Excel
						</a>
						<a class="btn btn-sm btn-warning" href="/sics/view/ci/PDFCI.php?ig=<?php echo $solc['id_generado']; ?>">
							<i class="glyphicon glyphicon-file"></i>&nbsp; PDF
						</a>
					</td>
					<td><?php echo $solc['per_fecha']; ?></td>
					<td><?php echo $solc['per_descripcion']; ?></td>
					<td><?php echo $solc['emp_nombre']; ?></td>
					<td><?php echo $solc['per_cantidad']; ?></td>
				</tr>
				<?php $cuantos++; ?>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="panel-footer"><b><?php echo $cuantos.' '; ?>  periodos generados</b> <a class="btn btn-success" href="?c=ci&a=xls&id=12&via=1">GENERAR NUEVO</a></div>
</div>