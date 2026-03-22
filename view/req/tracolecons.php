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
/*
echo $_REQUEST['accion'].'<br />';
echo $_REQUEST['nvo_precio'].'<br />';
list($nvo_prec, $nvo_prov) = explode("~", $_REQUEST['nvo_precio']);
echo $nvo_prec.'<br />';
echo $nvo_prov.'<br />';
echo $_REQUEST['codigo'].'<br />';
echo $_REQUEST['prov_anterior'].'<br />';
*/
?>
<script>
$(document).ready(function(){
	// asignacion de proveedor y precio
	$("#prov_form").validate({
	    rules: {
			proveedor: {
				required: true
			},
			precio: {
				required: true
			}
	   },
	   messages: {
	     	proveedor: {
		     	required : "Seleccione proveedor"
	     	},
	     	precio: {
		     	required: "Digite precio"
	     	}
	   },
		submitHandler: function(form) {
			// campos en pantalla
			var prod_codigo = $('#modP_Codigo').text();
			var id_proveedor = $('#modP_Solc').text();
			var fecha_col = $('#modP_CC').text();
			var id_empresa = $('#modP_Prehreq').text();
			var precio_ant = 	$('#modP_Precio').text();

			var id_col = fecha_col.replace(/-/g, '');
			var id_cod = prod_codigo.replace(/-/g, '');
			var id_Toggle = id_cod+$('#modP_Solc').text()+id_col;
			var btnToggle = $('#PR'+id_Toggle); // Etiquera de Proveedor
			// Precio Unitario
			var lblPU = $('#'+'PU'+id_cod+$('#modP_Solc').text()+id_col);
			// envio de formulario
			var campos=xajax.getFormValues("prov_form");
			campos["prod_codigo"] = prod_codigo;
			campos["id_proveedor"] = id_proveedor;
			campos["fecha_col"] = fecha_col;
			campos["id_empresa"] = id_empresa;
			campos["precio_ant"] = precio_ant;
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
					    var codigo = $('#proveedor option:selected').val();
						var arrCod = codigo.split('~');
						btnToggle.html(arrCod[0]+' '+arrCod[1]);
						lblPU.html($('#precio').val());
						/*var num1 = parseFloat(arrCod[0]);
						var num2 = parseInt(cant);
						num1.toFixed(2);
						num1.toFixed(2);
						lblTOT.html((num1*num2).toFixed(2));*/
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
		//var label = $(this).parent().parent().find('td').eq(0).find('.btn.toggle').text();
		//var cant = $(this).parent().parent().find('td').eq(3).find('a#address').text();
		/*
		if(label !== 'asignar codigo') {
		*/
			var arrID = $(this).attr('id').split('~');
			$('#modP_CC').html(arrID[2]); // Fecha Coleccion
			$('#modP_Solc').html(arrID[1]); // ID Proveedor
			$('#modP_Codigo').html(arrID[0]); //Codigo
			$('#modP_Prehreq').html(arrID[3]); // ID Empresa
			$('#modP_Precio').html(arrID[4]); // Precio Actual
			$('#modP_NProv').html(arrID[5]); // Nombre Proveedor
			$('#proveedor').html('');
			$('#proveedor').remove();
			myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=solc&a=listaProveedor';
			$.ajax({
				type: 'POST',
				url: myUrl,
				dataType: 'json',
		       		data: {
						producto: arrID[0]
				},
				success: function(aData){
					var opcion='';
					$.each(aData, function(i, item){
						opcion += '<option value="'+item.id_proveedor+'~'+item.prov_nombre+'">'+item.prov_nombre+'</option>';
					});
					$("label#lblproveedor").after('<div class="col-md-9"><select class="form-control input-sm" id="proveedor" name="proveedor">'+opcion+'</select></div>');
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					alert(textStatus);
				}
			});
			/*
		} else {
			jAlert('debe asignar un codigo');
			$('#provModal').modal('hide');
		}
		*/
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
			  '<button type="submit" class="btn btn-success editable-submit btn-sm"><i class="glyphicon glyphicon-ok icon-white"></i></button>' +
			 '<button type="button" class="btn editable-cancel btn-sm"><i class="glyphicon glyphicon-remove"></i></button>';
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
});
</script>
<h4 class="text-blue">Trabajar Requisiciones Colectadas</h4>
<form class="form-inline" action="?c=req&a=tracolecons&id=6" method="post" role="form">
	<table class="table table-condensed">
		<tbody>
			<tr>
		  		<td>Empresa</td>
		  		<td>
		  			<div class="col-md-8">
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
					<button type="submit" class="btn btn-sm btn-primary col-md-4"><i class="glyphicon glyphicon-align-justify"></i> Listar Consolidado</button>
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
<?php 
	if(isset($trabajar) && count($trabajar) > 0 && is_array($trabajar) ) {
?>
<table class="table table-condensed">
	<thead>
  		<tr>
    		<th>CODIGO</th>
    		<th>CANTIDAD</th>
    		<th>PRECIO<br />UNITARIO</th>
    		<th>PROVEEDOR</th>
  		</tr>
  	</thead>
  	<tbody>
	<?php
	$fecha_flag = ''; 
	foreach($trabajar as $colecta){
		 if ($fecha_flag <> $colecta['predreq_fecha_col']) {
	?>
		<tr>
			<td colspan="4">
				<h4><?php echo $colecta['predreq_fecha_col']; ?></h4>
			</td>
		</tr>
	<?php
		} 
	?>
	<tr>
  		<td>
			<?php echo $colecta['prod_codigo']; ?>
		</td>
  		<td>
			<?php echo $colecta['cantidad']; ?>
		</td>
		<td>
			<h5>
			<label class="label label-default" id="PU<?php echo str_replace("-", "",$colecta['prod_codigo']).$colecta['id_proveedor'].str_replace("-","",$colecta['predreq_fecha_col']); ?>" name="PU<?php echo str_replace("-", "",$colecta['prod_codigo']).$colecta['id_proveedor'].str_replace("-","",$colecta['predreq_fecha_col']); ?>">
				<?php echo $colecta['predreq_prec_uni']; ?>
			</label>
			</h5>
		</td>
    	<td>
    	<?php
    	$sql_ver_precio_mejor = "Select l.id_proveedor, p.prov_nombre, l.lis_prec_may,"
			." l.lis_min_may, l.lis_fin_vigencia"
			." from ".$conf->getTbl_lista()." l"
			." Join ".$conf->getTbl_proveedor()." p"
			." On p.id_proveedor = l.id_proveedor"
    		." Where l.prod_codigo='".$colecta['prod_codigo']."' And"
			." l.lis_prec_may > 0 And"
			." l.lis_fin_vigencia >= ".date('Y-m-d')." And"
			." l.lis_min_may <=".$colecta['cantidad'];
    	$run_ver_precio_mejor = $db->ejecutar($sql_ver_precio_mejor);
    	$row_ver_precio_mejor = mysqli_fetch_array($run_ver_precio_mejor);
    	//echo $sql_ver_precio_mejor.'<br>';
    	//print_r($row_ver_precio_mejor);
    	//echo $row_ver_precio_mejor;
    	 ?>
			<div class="col-md-10 col-lg-10">
			<?php
			if(count($row_ver_precio_mejor) > 0 && isset($row_ver_precio_mejor) && is_array($row_ver_precio_mejor))
			{
				echo '<form class="form-inline action="./?c=req&a=tracolecons&id=6" method="POST">';
				echo '<div class="form-group">
    					<label for="exampleInputName2" id="PR'.str_replace("-", "",$colecta['prod_codigo']).$colecta['id_proveedor'].str_replace("-","",$colecta['predreq_fecha_col']).'">
							'.$colecta['id_proveedor'].' '.$colecta['prov_nombre'].'
						</label>
						<span data-toggle="tooltip" data-placement="left" title="Puede utilizar precio de mayoreo!" class="glyphicon glyphicon-exclamation-sign text-danger red-tooltip" aria-hidden="true"></span>
					  </div>';
				?>
  					<div class="form-group">
    					<label for="exampleInputName2"></label>
						<select class="form-control" id="nvo_precio" name="nvo_precio">
							<?php
							$run_ver_precio_mejor = $db->ejecutar($sql_ver_precio_mejor);
							while($mejorp = mysqli_fetch_array($run_ver_precio_mejor)){
								echo '<option value="'.$mejorp['lis_prec_may'].'~'.$mejorp['id_proveedor'].'">('.$mejorp['lis_min_may'].') '.$mejorp['lis_prec_may'].' '.$mejorp['prov_nombre'].'</option>';
							}
							?>
						</select>
					</div>
					<input type="hidden" id="empresa" name="empresa" value="<?php echo $_REQUEST['empresa']; ?>" />
					<input type="hidden" id="codigo" name="codigo" value="<?php echo $colecta['prod_codigo']; ?>" />
					<input type="hidden" id="prov_anterior" name="prov_anterior" value="<?php echo $colecta['id_proveedor']; ?>" />
					<input type="hidden" id="fecha_col" name="fecha_col" value="<?php echo $colecta['predreq_fecha_col']; ?>" />
					<input type="hidden" id="accion" name="accion" value="asignar_nvo_prv" />
					<button type="submit" class="btn btn-info">Asignar</button>
				</form>
				<?php 
			}  else {
				echo '<label id="PR'.str_replace("-", "",$colecta['prod_codigo']).$colecta['id_proveedor'].str_replace("-","",$colecta['predreq_fecha_col']).'">'.$colecta['id_proveedor'].' '.$colecta['prov_nombre'].'</label>';
			}		
			?>
			</div>
			<div class="col-md-2 col-lg-2">
				<a data-backdrop="static" data-toggle="modal" href="#provModal" class="btn toggle2 btn-default btn-sm" id="<?php echo $colecta['prod_codigo'].'~'.$colecta['id_proveedor'].'~'.$colecta['predreq_fecha_col'].'~'.$_REQUEST['empresa'].'~'.$colecta['predreq_prec_uni'].'~'.$colecta['prov_nombre']; ?>">
					<span class="glyphicon glyphicon-question-sign"></span> Nuevo Precio
				</a>
			</div>
		</td>
    </tr>    
	<?php
		$fecha_flag = $colecta['predreq_fecha_col']; 
	} 
	?>
	</tbody>
</table>
<?php } ?>
<!-- aisgnacion de proveedor aitem -->
<div class="modal fade" id="provModal">
	<div class="modal-dialog">
	<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">✕</button>
        <h3>asignacion de precio especial</h3>
		<p>
			<i class="glyphicon glyphicon-calendar"></i>&nbsp;Fecha Coleccion : <label class="label label-info" id="modP_CC"></label>&nbsp;&nbsp;&nbsp;
			<i class="glyphicon glyphicon-shopping-cart"></i>&nbsp;Proveedor : <label class="label label-info" id="modP_Solc"></label><label class="label label-info" id="modP_NProv"></label>&nbsp;&nbsp;&nbsp;
			<i class="glyphicon glyphicon-cog"></i>&nbsp;Codigo : <label class="label label-info" id="modP_Codigo"></label>&nbsp;&nbsp;
			<i class="glyphicon glyphicon-usd"></i>&nbsp;Precio : <label class="label label-info" id="modP_Precio"></label>&nbsp;&nbsp;
			<i class="glyphicon glyphicon-home"></i>&nbsp;Empresa : <label class="label label-info" id="modP_Prehreq"></label>
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
                            		<label class="control-label col-md-3">Precio</label>
                            		<div class="col-md-9">
                            			<input type="text" class="form-control" id="precio" name="precio" />
                            		</div>
                            	</div>
                                <div class="form-group">
                                	<label class="control-label col-md-3" id="lblproveedor">Proveedor</label>
                                </div>
                                <input type="hidden" value="assign_prov_prec_req" id="accion" name="accion">
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
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>