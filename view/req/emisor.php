<?php
if ($_SESSION['req'] == '0') {
	echo '<label class="alert alert-error">lo sentimos se ha revocado el acceso a esta opcion, consulte al administrador.</label>';
} else {
?>
<script>
//Llenamos la lista de bodegas para la empresa seleccionada
function CheckAjaxCall(str) {
  //var str = $('#empresa').val();
  myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=req&a=listarCcUsuarioC';
  $.ajax({
    type: 'POST',
    url: myUrl,
    dataType: 'json',
           data: {
               id_empresa: str
    },
    success: function(aData){
    	//$('#centrocosto').after('<img src="images/FhHRx.gif"></img>');
    	$('#centrocosto').hide('');
      	$('#centrocosto').html('');
      	$('#centrocosto').append('<option value="0">-- todos --</option>');
      	$.each(aData, function(i, item){
        	opcion = '<option value="'+item.id_cc+'">( '+item.cc_codigo+' ) '+item.cc_descripcion+'</option>';
        	$('#centrocosto').append(opcion);
      	});
    },
    error: function(XMLHttpRequest, textStatus, errorThrown){
      jAlert(textStatus);
    }
  });
  return false;
}
$(function(){
  	//Capturamos el evento onchange de la empresa para llenar
	//la lista de centros de costo
  	$("#empresa").change(function(){
    	if($(this).val() != "") {
      		CheckAjaxCall($(this).val());
    	} else {
      		$('#centrocosto').html('');
      		$('#centrocosto').hide('');
    	}
  	});
  	$("#frmVer").on("submit", function(event) {
      	event.preventDefault();
  	});
  	// Valida formulario
  	$("#frmVer").validate({
  	  	rules:{
      		empresa : {
        		required: true
      		}
    	},
    	submitHandler: function(form) {
	      	var campos=xajax.getFormValues("frmVer");
	      	myUrl = location.protocol + "//" + location.host + '/sics/class/paginacion.php';
	      	$.ajax({
	        	type: 'POST',
	        	url: myUrl,
	        	dataType: 'json',
	               	data: {
	                   	datos: campos
	        	},
	        	beforeSend: function ( xhr ) {
	        		$.pnotify({
	        			title: 'ejecutando consulta..',
						text: 'consultando datos, por favor espere...',
						type: 'info',
						hide: true,
						icon: 'icon-alert-sign',
						addclass: "stack-bottomright",
						stack: stack_bottomright
					});
	        	},
	        	success: function(aData){
	          		$.each(aData, function(i, item){
		          		opcion = '<tr>';
	            		opcion += '<td>'+item.id_prehreq+'</td>';
	            		opcion += '<td>'+item.prehreq_numero_req+'</td>';
	            		opcion += '<td>('+item.id_cc+'';
	            		opcion += ') '+item.cc_descripcion+'</td>';
	            		opcion += '<td>'+item.prehreq_fecha+'</td>';
	            		opcion += '<td>'+item.prehreq_hora+'</td>';
	            		opcion += '<td>'+item.prehreq_usuario+'</td>';
	            		opcion += '<td>'+item.nestado+'</td>';
	            		opcion += '<td><i class="icon-file"></i></td>';
	            		opcion += '</tr>';
	            		$('table > tbody').append(opcion);
	          		});
	        	},
	        	error: function(XMLHttpRequest, textStatus, errorThrown){
	          		jAlert(textStatus);
	        	}
	      	});
	      	return false;
    	}
  	});
});
</script>
<div class="well well-sm">
<div class="row">
    <div class="col-md-6" style="margin-top:0 ;">
       <H3>consulta de requisiciones</H3>
    </div>
    <div class="col-md-6" id="paginas">
       <div class="pagination pagination-right" style="margin-top:0;">
			<ul>
				<li><a href="#">Prev</a></li>
				<li><a href="#">1</a></li>
				<li><a href="#">2</a></li>
				<li><a href="#">3</a></li>
				<li><a href="#">4</a></li>
				<li><a href="#">Next</a></li>
			</ul>
	   </div>
    </div>
</div>
<?php print_r($permisos); ?>
  <form class="form form-inline" id="frmVer"  method="post" action="#">
    <label>empresa</label>
    <select id="empresa" name="empresa">
      <option value="">-- seleccione --</option>
      <?php foreach ($emps as $emp) { ?>
      <option value="<?php echo $emp['id_empresa']; ?>"><?php echo $emp['emp_nombre']; ?></option>
      <?php } ?>
    </select>
    <label>centro de costo</label>
    <select id="centrocosto" name="centrocosto">
    </select>
    <label>estado</label>
    <select id="estado" name="estado">
      <option value="0">-- todos --</option>
    <?php foreach($estados as $k => $v) { ?>
      <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
    <?php } ?>
    </select>
    <input type="hidden" value="<?php echo $conf->getTbl_prehreq(); ?>" name="t" id="t">
    <button class="btn btn-primary" type="submit">mostrar</button>
  </form>
  <table class="table table-condensed tablesorter">
    <thead>
      <tr>
        <th width="7%">pre-req</th>
        <th width="8%">requisicion</th>
        <th>centro de costo</th>
        <th width="8%">fecha</th>
        <th width="7%">hora</th>
        <th width="20%">usuario</th>
        <th width="20%">estado</th>
        <th width="5%">ver</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
      <tr>
      </tr>
    </tfoot>
  </table>
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
    }
}(jQuery));
</script>
<?php } ?>