<style>
.nobd, .nobd th, .nobd td {
	border: none;
	border-bottom: none;
	border-top: none;
	border-right: none;
	border-left: none;
}
</style>
<?php
if ($_SESSION['req'] == '0') {
	echo '<label class="alert alert-error">lo sentimos se ha revocado el acceso a esta opcion, consulte al administrador.</label>';
} else {
?>
<script>
$(document).ready(function(){

	 $('[id="total_preoc"]').priceFormat();
	 
	// asignacion de proveedor y precio
	$("#prov_form").validate({
	    rules: {
			proveedor: {
				required: true
			}
	   },
	   messages: {
	     	proveedor: {
		     	required : "Seleccione proveedor y precio"
	     	}
	   },
		submitHandler: function(form) {

			// campos en pantalla
			var id_Toggle = $('#modP_CC').text()+'-'+$('#modP_Solc').text()+'-'+$('#modP_Item').text()+'-PROV-'+$('#modP_Prehreq').text();//'+$('#modP_Codigo').text()+'-
			var btnToggle = $('#'+id_Toggle);
			var lblPU = $('#'+'PU'+$('#modP_CC').text()+$('#modP_Prehreq').text()+$('#modP_Item').text());
			var lblTOT = $('#'+'TOT'+$('#modP_CC').text()+$('#modP_Prehreq').text()+$('#modP_Item').text());
			var cant = 	$('#modP_Cantidad').text();
			// envio de formulario
			var campos=xajax.getFormValues("prov_form");
			campos["id_predreq"] = $("#modP_Item").text();
			campos["id_prehreq"] = $("#modP_Prehreq").text();
			myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
			$.ajax({
				type : "POST",
				url : myUrl,
				data: {
					form: campos
				},
				beforeSend: function(){
					$(":submit").attr("disabled","disabled");
					$('#overlay').show();
				},
				success : function(data){
					$('#overlay').hide();
				    if(!$.isNumeric(data)){
					    jAlert(data);
					} else {
					    $.pnotify({
							title: 'ok',
        					text: 'proveedor asignado a solicitud',
        					type: 'success',
        					hide: true,
							addclass: "stack-bar-bottom",
							stack: stack_bar_bottom,
							cornerclass: '',
							width: '100%'
        				});
					    var codigo = $('#proveedor option:selected').text();
						var arrCod = codigo.split('-');
						btnToggle.html(arrCod[2]);
						lblPU.html(arrCod[0]);
						var num1 = parseFloat(arrCod[0]);
						var num2 = parseInt(cant);
						num1.toFixed(2);
						num1.toFixed(2);
						lblTOT.html((num1*num2).toFixed(2));
					    $("#prov_form input[type=reset]").click();
						$("#provModal").modal('toggle');
					}
					$(":submit").removeAttr("disabled");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
				    $.pnotify({
						title: 'Ha ocurrido un error.!',
        				text: 'Ha ocurrido un error durante la ejecucion.'+textStatus,
        				type: 'error',
        				hide: true,
						addclass: "stack-bar-top",
						stack: stack_bar_top,
						cornerclass: '',
						width: '100%'
        				});
				    $(":submit").removeAttr("disabled");
				}
			});
	   }
	});
	// Accionamos el boton
	$('.btn.toggle2').live('click', function() {
		var label = $(this).parent().parent().find('td').eq(0).find('.btn.toggle').text();
		var cant = $(this).parent().parent().find('td').eq(3).find('a#address').text();
		if(label !== 'asignar codigo') {
			var arrID = $(this).attr('id').split('-');
			$('#modP_CC').html(arrID[0]);
			$('#modP_Solc').html(arrID[1]);
			$('#modP_Item').html(arrID[2]);
			$('#modP_Codigo').html(label);
			$('#modP_Prehreq').html(arrID[4]);
			$('#modP_Cantidad').html(cant);
			$('#proveedor').html('');
			$('#proveedor').remove();
			myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=solc&a=listaPrecio';
			$.ajax({
				type: 'POST',
				url: myUrl,
				dataType: 'json',
		       		data: {
						producto: label
				},
				success: function(aData){
					var opcion='';
					$.each(aData, function(i, item){
						opcion += '<option value="'+item.id_proveedor+'-'+item.lis_precio+'-'+item.lis_empaque+'">'+item.lis_precio+'-'+item.lis_empaque+'-'+item.prov_nombre+'</option>';
					});
					$("label#lblproveedor").after('<div class="col-md-9"><select class="form-control input-sm" id="proveedor" name="proveedor">'+opcion+'</select></div>');
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					alert(textStatus);
				}
			});
		} else {
			jAlert('debe asignar un codigo');
			$('#provModal').modal('hide');
		}
	});
	/*
	 * Edicion inline
	*/
	$('#address').live('click', function(){
		var label = $(this).parent().parent().find('td').eq(0).find('.btn.toggle').text();
		var idlabel = $(this).parent().parent().find('td').eq(0).find('.btn.toggle').attr('id');
		var arrID = idlabel.split('-');
		var lblTOT = $('#TOT'+arrID[0]+arrID[3]+arrID[2]);
		var lblPU = $('#PU'+arrID[0]+arrID[3]+arrID[2]);
		var num2 = parseFloat(lblPU.text());
		num2.toFixed(2);
		if(label !== 'asignar codigo') {
			//modify buttons style
			$.fn.editableform.buttons =
			  '<button type="submit" class="btn btn-success editable-submit btn-sm"><span class="glyphicon glyphicon-ok"></span></button>' +
			 '<button type="button" class="btn btn-default editable-cancel btn-sm"><span class="glyphicon glyphicon-remove"></span></button>';
			 $(this).editable({
				validate: function(value) {
					if($.trim(value.city) == '')	return 'digite cantidad';
					if(!$.isNumeric(value.city))	return 'digite numero valido';
					if(value.city < 0)	return 'digite cantidad';
					if($.trim(value.street) == '')	return 'digite observacion';
					var len = value.street.length;
					if(len > 75) return '# de caracteres permitidos es 75, usted digito : '+len;
				},
				ajaxOptions: {
					dataType: 'json'
				},
				success: function(response, newValue) {
					if(!response) {
						return "Unknown error! "+response;
					}
					if(response.success === false) {
						 return response.msg;
					}
					var num1 = parseInt(newValue.city);
					num1.toFixed(2);
					lblTOT.html(num1*num2);
				}
			 });
			$(this).editable('show');
		} else {
			jAlert('Asigne un codigo primero');
		}
	});

	// Accionamos el boton
	$('.btn-info').live('click', function() {
	});
});
</script>
<h4 class="text-blue">Trabajar Pre Ordenes</h4>
<form class="form-inline" action="?c=req&a=ocpre&id=6" method="post" role="form">
	<table class="table table-condensed">
		<tbody>
			<tr>
		  		<td>Empresa</td>
		  		<td>
		  			<div class="col-md-5">
					<select id="empresa" name="empresa" class="form-control input-sm">
						<?php foreach ($emps as $emp) { ?>
						<?php
						if(isset($_POST['empresa']) && $_POST['empresa'] == $emp['id_empresa']) {
						  	$empresa = $emp['emp_nombre'];
						  	$id_empresa = $emp['id_empresa'];
						}
						?>
						<option value="<?php echo $emp['id_empresa']; ?>" <?php if(isset($_POST['empresa']) && $_POST['empresa'] == $emp['id_empresa']) echo "selected"; ?>><?php echo $emp['emp_nombre']; ?></option>
						<?php } ?>
					</select>
					</div>
					<button type="submit" class="btn btn-sm btn-default col-md-2"><span class="glyphicon glyphicon-align-justify"></span> Listar Pre Ordenes</button>
					<div class="col-md-5">&nbsp;</div>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<?php
if(isset($trabajar) && !is_array($trabajar)) {
	echo '<div class="alert alert-warning">';
	echo $trabajar;
	echo '</div>';
}
?>
<?php if(isset($trabajar) && count($trabajar) <= 0) echo '<span class="alert alert-warning">no existen registros para trabajar</span>'; ?>
<?php if(isset($trabajar) && count($trabajar) > 0 ) { ?>
<table class="table table-condensed">
	<thead>
  		<tr>
    		<th>Proveedor</th>
    		<th>Pre Orden</th>
    		<th>Imprmir</th>
  		</tr>
  	</thead>
  	<tbody>
<?php } ?>
<?php foreach($trabajar as $colecta){ ?>
	<tr>
  		<td  class="accordion-toggle" data-toggle="collapse" href="#collapseTraReq<?php echo $colecta['predreq_numero_oc']; ?>">
			<label class="text-info">
				<span class="glyphicon glyphicon-chevron-down"></span> <?php echo '(<span id="proveedor">'.$colecta['id_proveedor'].'</span>)'.$colecta['prov_nombre']; ?>
				<?php
				$sum = 0;
				foreach ($colecta['Det'] as $item) {
					$sum += number_format($item['predreq_total'], 2);
				} 
				echo '<h6 id="total_preoc">'.number_format($sum, 2).'</h6>'; ?>
			</label>
		</td>
  		<td>
			<label class="text-info">
				<?php echo $colecta['predreq_numero_oc']; ?>
			</label>
		</td>
		<td>
			<a href="json.php?c=req&a=ocpre_pdf&id=6&poc=<?php echo $colecta['predreq_numero_oc']; ?>&p=<?php echo $colecta['id_proveedor']; ?>&cia=<?php echo $colecta['id_empresa']; ?>&r=1" class="btn btn-info">
				<span class="glyphicon glyphicon-print"> Imprimir</span>
			</a>
		</td>
    </tr>
    <tr>
    	<td colspan="3">
    	<div id="collapseTraReq<?php echo $colecta['predreq_numero_oc']; ?>" class="accordion-body collapse">
    	<table class="table table-condensed accordion-inner nobd">
    	<tr>
    		<th>Codigo</th>
    		<th>Descripcion</th>
			<th>Cantidad</th>
			<th>Precio</th>
			<th>Total</th>
			<th>Item</th>
    	</tr>
    <?php
    $contador = 0;
    $total_preoc = 0; 
    foreach ($colecta['Det'] as $det) {
		$total_preoc = $total_preoc +  $det['predreq_total'];
	?>
    	<tr>
   			<td><?php echo $det['prod_codigo']; ?></td>
   			<td><?php echo $det['predreq_descripcion']; ?></td>
   			<td><?php echo $det['predreq_cantidad_aut']; ?></td>
			<td><?php echo $det['predreq_prec_uni']; ?></td>
			<td><?php echo $det['predreq_total']; ?></td>
			<td><?php echo ++$contador; ?></td>
  		</tr>
  	<?php } ?>
  		<tr>
  			<td colspan="4"><h4>Total</h4></td>
  			<td><h4 id="total_preoc"><?php echo number_format($total_preoc, 2); ?></h4></td>
  			<td>&nbsp;</td>
  		</table>
  		</div>
  		</td>
  	</tr>
<?php } ?>
	</tbody>
</table>
<!-- aisgnacion de proveedor aitem -->
<div class="modal fade" id="provModal">
	<div class="modal-dialog">
	<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">✕</button>
        <h3>asignacion de proveedor</h3>
		<p>
			<i class="glyphicon glyphicon-home"></i>&nbsp;Centro de Costo : <label class="label label-info" id="modP_CC"></label>&nbsp;&nbsp;&nbsp;
			<i class="glyphicon glyphicon-shopping-cart"></i>&nbsp;Numero de Solicitud : <label class="label label-info" id="modP_Solc"></label>&nbsp;&nbsp;&nbsp;
			<i class="glyphicon glyphicon-check"></i>&nbsp;Item : <label class="label label-info" id="modP_Item"></label>&nbsp;&nbsp;
			<i class="glyphicon glyphicon-cog"></i>&nbsp;Codigo : <label class="label label-info" id="modP_Codigo"></label>&nbsp;&nbsp;
			<i class="glyphicon glyphicon-cog"></i>&nbsp;ID : <label class="label label-info" id="modP_Prehreq"></label>
		</p>
    </div>
	<div class="modal-body">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div id="modalTab">
                    <div class="tab-content">
                        <div class="tab-pane active" id="login">
                            <form class="form-horizontal" role="form" method="post" id="prov_form" name="prov_form">
                            	<div class="form-group">
                            		<label class="control-label col-md-3">Cantidad</label>
                            		<div class="col-md-9">
                            			<label class="form-control-static text-info" id="modP_Cantidad"></label>
                            		</div>
                            	</div>
                                <div class="form-group">
                                	<label class="control-label col-md-3" id="lblproveedor">Proveedor</label>
                                </div>
                                <input type="hidden" value="assign_prov_req" id="accion" name="accion">
								<input type="hidden" value="predreq" id="tabla" name="tabla">
								<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
								<div class="form-group">
    								<div class="col-md-offset-3 col-md-9">
										<input type="reset" class="hidden" />
										<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span>  Asignar Proveedor y Precio</button>&nbsp;&nbsp;
									</div>
								</div>
                            </form>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</div>
<br><br><br>
<script type="text/javascript">
window.onload=function(){
	$('.accordion-body.collapse').hover(
		function () {
			$(this).css('overflow','visible');
		},
		function () {
			$(this).css('overflow','hidden');
		}
	);
};
</script>
<?php
}
?>