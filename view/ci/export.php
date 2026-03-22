<script>
$(document).ready(function () {
    // Handler for .ready() called.
	$(document).on('click', '#btnExcel', function(){
		window.setTimeout(function () {
	        location.href = "?c=ci&a=xls&id=12&via=1";
	    }, 5000);
	}); 
});
</script>
<?php 
if($_REQUEST['via'] == '1') { 
?>
<h4 class="text-blue">Generar Periodo a Excel</h4>
<form class="form-horizontal" method="post" action="?c=ci&a=xls&id=12&via=<?php echo '2';//$_REQUEST["via"]; ?>">

	<div class="form-group">
    	<label for="empresa" class="col-sm-3 control-label">Empresa :</label>
    	<div class="col-sm-9">
			<select class="form-control" id="empresa" name="empresa">
				<?php foreach ($emps as $emp) { ?>
				<option value="<?php echo $emp['id_empresa'].'~'.$emp['emp_nombre']; ?>"><?php echo $emp['emp_nombre']; ?></option>
				<?php } ?>
			</select>
		</div>
	</div>

  <div class="form-group">
    <label for="fefin" class="col-sm-3 control-label">Fecha de Hoy :</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" id="fefin" name="fefin" placeholder="Fin">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
    	<input type="hidden" value="<?php echo '2';//$_REQUEST["via"]; ?>" name="via" id="via" />
      	<button type="submit" class="btn btn-success btn-lg">Generar Reporte</button>
    </div>
  </div>
</form>
<?php 
} else {
?>
<div class="panel panel-info">
    <div class="panel-heading">
    	<h5 class="text-center">CONSUMOS A GENERARA HASTA FECHA <?php echo date("d M, Y", strtotime($_REQUEST['fefin']));  ?></h5>
    </div>
    <div class="table-responsive">
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>Numero</th>
					<th>Centro Costo</th>
					<th>Cantidad</th>
					<th>Codigo</th>
					<th>Descripcion</th>
					<th>Fecha Solicitado</th>
				</tr>
			</thead>
			<tbody>
<?php  
	foreach ($datos as $dato){
	?>
	<tr>
					<td><?php echo $dato['ci_numero']; ?></td>
					<td> <?php echo '( '.$dato['id_cc'].' ) '.$dato['cc_descripcion']; ?></td>
					<td><?php echo $dato['ci_det_cantidad']; ?></td>
					<td><?php echo $dato['prod_codigo']; ?></td>
					<td><?php echo $dato['prod_descripcion']; ?></td>
					<td><?php echo $dato['ci_enc_fecha']; ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="panel-footer">
		<form class="form-horizontal" method="post" action="?c=ci&a=xls&id=12&via=1">
			<input type="hidden" value="<?php echo $_REQUEST["empresa"]; ?>" name="empresa" id="empresa" />
			<input type="hidden" value="<?php echo $_REQUEST["fefin"]; ?>" name="fefin" id="fefin" />
			<input type="hidden" value="1" name="via" id="via" />
      		<button type="submit" class="btn btn-success btn-lg">Generar Periodo</button>
      		<a class="btn btn-lg btn-default" href="?c=ci&a=xls&id=12&via=1"> REGRESAR</a>
      	</form>
	</div>
</div>
<?php } ?>
<script type="text/javascript">
    $(function(){
		var d = new Date();
		var fecha = d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate();
		$('#fefin').daterangepicker({
			autoUpdateInput: true,
			"maxDate": fecha.toString(),
		    "singleDatePicker": true,
		    "showDropdowns": true,
		    "locale": {
		        "format": "YYYY-MM-DD",
		        "separator": " - ",
		        "applyLabel": "Cambiar",
		        "cancelLabel": "Cancelar",
		        "fromLabel": "Desde",
		        "toLabel": "Hasta",
		        "customRangeLabel": "Custom",
		        "weekLabel": "W",
		        "daysOfWeek": [
		            "Do",
		            "Lu",
		            "Ma",
		            "Mi",
		            "Ju",
		            "Vi",
		            "Sa"
		        ],
		        "monthNames": [
		            "Enero",
		            "Febrero",
		            "Marzo",
		            "Abril",
		            "Mayo",
		            "Junio",
		            "Julio",
		            "Agosto",
		            "Septiembre",
		            "Octubre",
		            "Noviembre",
		            "Diciembre"
		        ],
		        "firstDay": 1
		    },
		    "alwaysShowCalendars": true
		}, function(start, end, label) {
		  console.log("New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')");
		});
    });
</script>