<h4 class="text-blue">Generar Reportes a Excel</h4>
<form class="form-horizontal" method="post" action="?c=repo&a=xls&id=7&via=<?php echo $_REQUEST["via"]; ?>">
  <div class="form-group">
    <label for="feini" class="col-sm-3 control-label">Fecha de Inicio :</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" id="feini" name="feini" placeholder="Inicio">
    </div>
  </div>
  <div class="form-group">
    <label for="fefin" class="col-sm-3 control-label">Fecha de Fin :</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" id="fefin" name="fefin" placeholder="Fin">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
    	<input type="hidden" value="<?php echo $_REQUEST["via"]; ?>" name="via" id="via" />
      	<button type="submit" class="btn btn-default">Generar Reporte</button>
    </div>
  </div>
</form>
<script type="text/javascript">
    $(function(){
		$('#feini').daterangepicker({
		    "singleDatePicker": true,
		    "showDropdowns": false,
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

		$('#fefin').daterangepicker({
		    "singleDatePicker": true,
		    "showDropdowns": false,
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