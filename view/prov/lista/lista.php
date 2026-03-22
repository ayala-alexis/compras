<?php
session_start ();
?>
<script>
//Llenamos la lista de productos para la sublinea seleccionada
function CheckAjaxCall(str) {
	//var str = $('#empresa').val();
	myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=prov&a=listarProductoC';
	$.ajax({
		type: 'POST',
		url: myUrl,
		dataType: 'json',
           data: {
               sl: str
		},
		beforeSend: function(xhr) {
			$('#producto').html('');
			$('#producto').after('<img src="images/FhHRx.gif"></img>');
			$('#producto').hide();
		},
		success: function(aData){
			$.each(aData, function(i, item){
				opcion = '<option value="'+item.prod_codigo+'">'+item.prod_descripcion+'</option>';
				$('#producto').append(opcion);
			});
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(textStatus);
		}
	}).done(function(){
		$('#producto').show();
		$('#producto').nextAll('img').remove();
	});
	return false;
}
$(function(){
	
	$('a').tooltip();

	var oTable = $('table').DataTable();

	$('.two-digits').keyup(function(){
        if($(this).val().indexOf('.')!=-1){         
            if($(this).val().split(".")[1].length > 3){                
                if( isNaN( parseFloat( this.value ) ) ) return;
                this.value = parseFloat(this.value).toFixed(3);
            }  
         }            
         return this; //for chaining
    });
	/*
	 * Adicionar un item
	 */
	$("#frmAdd").validate({
		rules:{
			sl : {
				required: true
			},
			producto : {
				required: true
			},
			prov_orden : {
				required : true
			},
			precio : {
				required: true,
				number: true
			},
			lis_prec_may : {
				required: function(element){
		            return ($("#lis_min_may").val().length > 0 || ($("#lis_min_may").val() != '0'));
		        },
				number: true
			},
			lis_min_may : {
				required: function(element){
		            return ($("#lis_prec_may").val().length > 0 || ($("#lis_prec_may").val() != '0'));
		        },
				number: true
			},
			vigencia : {
				required: true
			}
		},
		submitHandler: function(form) {
			var campos = xajax.getFormValues("frmAdd");
			campos['descripcion'] = $('#producto :selected').text();
			Guardar(campos);
			//$('#frmAdd')[0].reset();
			//$("#divAdd").modal('hide');
			return false;
		}
	});
	/*
	 * Manda el formulari a guardar
	 */
	function Guardar(campos){
		// Enviamos a guardar
		myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
		$.ajax({
			type: 'POST',
			url: myUrl,
			data: {
				form: campos
			},
			beforeSend: function(){
				$('#waiting').show().dialog({
					modal: true,
					width: 'auto',
					height: 'auto',
					closeOnEscape: false
				});
			},
			success : function(data){
				if(!$.isNumeric(data)){
					$.pnotify({
						title: "error",
						text: data,
						icon: "glyphicon glyphicon-ban-circle",
						hide: true,
						type: "error"
					});
				} else {
					if(data == 1){
						$.pnotify({
							title: "error",
							text: data,
							icon: "glyphicon glyphicon-ban-circle",
							hide: true,
							type: "error"
						});
					} else {
						$('#frmAdd')[0].reset();
						$("#divAdd").modal('hide');
						var tds = '<tr>';
						tds += "<td>"+campos['producto']+"</td>";
						tds += "<td>"+campos['descripcion']+"</td>";
						tds += "<td>"+campos['precio']+"</td>";
						tds += "<td>"+campos['lis_prec_may']+"</td>";
						tds += "<td>"+campos['lis_min_may']+"</td>";
						tds += "<td>"+campos['vigencia']+"</td>";
						tds += '<td><a id="'+data+'" href="#" rel="tooltip" title="modificar"><i class="glyphicon glyphicon-pencil"></i></a></td>';
						tds += '<td><a id="'+data+'" href="#" rel="tooltip" title="eliminar"><i class="glyphicon glyphicon-remove"></i></a></td>';
						tds += "</tr>";
						var modifica = '<a id="'+data+'" href="#" rel="tooltip" title="modificar"><i class="glyphicon glyphicon-pencil"></i></a>';
						var elimina = '<a id="'+data+'" href="#" rel="tooltip" title="eliminar"><i class="glyphicon glyphicon-remove"></i></a>';
						oTable.row.add([
							campos['producto'],
							campos['descripcion'],
							campos['precio'],
							campos['lis_prec_may'],
							campos['lis_min_may'],
							campos['vigencia'],
							modifica,
							elimina ]).draw(false);
						//$(tds).hide().appendTo("table > tbody").fadeIn(1500).css('display','');
					}
				}
				$("#waiting").dialog("close");
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
			    $.pnotify({
					type : "error",
			        text: "Ha ocurrido un error durante la ejecucion.",
			        hide: true
				});
			}
		});
	}
	/*
	 * Adiciona item
	 */
	 $('#addItem').unbind('click').bind('click', function(){
		$("#divAdd").modal({
			backdrop: true,
			keyboard: false
		});
	});
	// Capturamos el evento onchange de la empresa para llenar
	// la lista de centros de costo
	$("#sl").change(function(){
		if($(this).val() != "") {
			CheckAjaxCall($(this).val());
		} else {
			$('#producto').html('');
		}
	});
	/*
	 * Procesamos la tabla
	 */
	$('table').delegate('[rel="tooltip"]', 'click', function(event) {
		$row = $(this).closest('tr').find('td');
		var $tr = $(this).closest('tr')[0];
		if($row.length) {
			var id = $(this).attr('id');
			var campos = xajax.getFormValues("frmAdd");
			var accion = $(this).attr('data-original-title');
			if(typeof(accion) === 'undefined'){
				accion = $(this).attr('title');
			}
			if(accion == 'modificar') {
				$('p#producto').text($row[0].innerHTML);
				//$('#prov_orden2').val($row[2].innerHTML);
				//$('input[name=prov_orden]').val($row[2].innerHTML);
				$('input[name=id_lista]').val(id);
				$('#precio2').val(parseFloat($row[2].innerHTML));
				$('#precio3').val(parseFloat($row[3].innerHTML));
				$('#min_precio3').val(parseFloat($row[4].innerHTML));
				$('#vigencia2').val($row[5].innerHTML);
				$('#dp32').attr('data-date',$row[5].innerHTML);
				$("#divEdit").modal({
					backdrop: true,
					keyboard: false
				});
			} else {
				if(accion == 'eliminar'){
					campos['accion'] = 'delete';
					campos['id_lista'] = id;
					//campos['prov_orden'] = $row[2].innerHTML;
					campos['precio2'] = parseInt($row[2].innerHTML);
					myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
					$.ajax({
						type: 'POST',
						url: myUrl,
						data: {
							form: campos
						},
						beforeSend: function(){
							$('#waiting').show().dialog({
								modal: true,
								width: 'auto',
								height: 'auto',
								closeOnEscape: false
							});
						},
						success : function(data){
							if(!$.isNumeric(data)){
								jAlert(data,'ha ocurrido un error');
							} else {
								if(data == 1){
									jAlert(data,'error');
								} else {
									oTable.row($tr).remove().draw(false);
								}
							}
							$("#waiting").dialog("close");
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							jAlert(data,'ha ocurrido un error');
						}
					});
				} else {
					jAlert('ha ocurrido un error');
				}
			}
		} else {
			jAlert('error en seleccion');
		}
	});
	/*
	 * Modificar Item
	 */
	$("#frmEdit").validate({
		rules:{
			precio2 : {
				required: true,
				number: true
			},
			precio3 : {
				number: true,
				required: function(element) {
					return $("#min_precio3").val() > 0;
				}
			},
			min_precio3 : {
				required: function(element){
		            return $("#precio3").val() > 0;
		        },
				number: true
			},
			vigencia2 : {
				required: true
			}
		},
		submitHandler: function(form) {
			var campos = xajax.getFormValues("frmEdit");
			campos['producto'] = $.trim($('p#producto').text());
			Actualiza(campos);
			//$('#frmEdit')[0].reset();
			//$("#divEdit").modal('hide');
			return false;
		}
	});
	/*
	 * Manda el formulari a guardar
	 */
	function Actualiza(campos){
		// Enviamos a guardar
		myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
		$.ajax({
			type: 'POST',
			url: myUrl,
			data: {
				form: campos
			},
			beforeSend: function(){
				$('#waiting').show().dialog({
					modal: true,
					width: 'auto',
					height: 'auto',
					closeOnEscape: false
				});
			},
			success : function(data){
				if(!$.isNumeric(data)){
					$.pnotify({
						title: "error",
						text: data,
						icon: "glyphicon glyphicon-ban-circle",
						hide: true,
						type: "error"
					});
				} else {
					if(data == 1){
						$.pnotify({
							title: "error",
							text: data,
							icon: "glyphicon glyphicon-ban-circle",
							hide: true,
							type: "error"
						});
					} else {
						$.pnotify({
							title: "actualizado",
							text: "registro actualizado",
							icon: "glyphicon glyphicon-ban-circle",
							hide: true,
							type: "success"
						});
						$('#frmEdit')[0].reset();
						$("#divEdit").modal('hide');
						location.reload();
					}
				}
				$("#waiting").dialog("close");
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
			    $.pnotify({
					type : "error",
			        text: "Ha ocurrido un error durante la ejecucion.",
			        hide: true
				});
			}
		});
	}
});
</script>
<?php
// Conexion a la BD
try {
	$db = DB::getInstance ();
	$conf = Configuracion::getInstance ();
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
?>
<?php
try {
	$sql = "Select a.*, b.cat_descripcon From " . $conf->getTbl_proveedor()." a "
		." Join ".$conf->getTbl_categoria()." b "
		." On b.id_categoria = b.id_categoria"
		." Where a.id_proveedor = '".$_GET['id']."'";
	$run = $db->ejecutar ( $sql );
} catch ( Exception $e ) {
	echo $e->getMessage ();
	die ();
}
$row = mysqli_fetch_array ( $run );
?>
<blockquote class="text-blue">
	<p><?php echo $row['prov_nombre']; ?></p>
	<small><?php echo $row['cat_descripcon']; ?></small>
</blockquote>
<p>
	<a id="addItem" class="btn btn-sm btn-primary">
		<span class="glyphicon glyphicon-plus"></span> Adicionar Producto
	</a>
</p>
<table class="table table-condensed">
	<thead>
		<tr>
			<th>Codigo</th>
			<th>Descripcion</th>
			<th>Precio<br />Detalle</th>
			<th>Precio<br />Mayoreo</th>
			<th>Minimo<br />Mayoreo</th>
			<th>Vigencia</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($filas as $fila) { ?>
		<tr class="<?php if ($fila['lis_fin_vigencia'] < date('Y-m-d')) { echo 'danger'; } ?>">
			<td><?php echo $fila['prod_codigo']; ?></td>
			<td><?php echo $fila['prod_descripcion']; ?></td>
			<td><?php echo $fila['lis_precio']; ?></td>
			<td><?php echo $fila['lis_prec_may'];	?></td>
			<td><?php echo $fila['lis_min_may'];	?></td>
			<td><?php echo $fila['lis_fin_vigencia']; ?></td>
			<td>
				<a id="<?php echo $fila['id_lista']; ?>" href="#" rel="tooltip" title="modificar">
					<i class="glyphicon glyphicon-pencil"></i>
				</a>
			</td>
			<td>
				<a id="<?php echo $fila['id_lista']; ?>" href="#" rel="tooltip" title="eliminar">
					<i class="glyphicon glyphicon-remove"></i>
				</a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<div class="modal fade" role="dialog"  id="divAdd" name="divAdd">
	<form id="frmAdd" name="frmAdd" class="form-horizontal" role="form">
		<div class="modal-dialog">
		<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="myModalLabel"><?php echo $row['prov_nombre']; ?></h3>
		</div>
		<div class="modal-body">
<!-- 			<div class="form-group"> -->
<!-- 				<label class="control-label col-md-3">Seleccione Orden</label> -->
<!-- 				<div class="col-md-9"> -->
<!-- 					<select class="form-control input-sm" name="prov_orden" id="prov_orden"> -->
<!-- 						<option value="01">Primero</option> -->
<!-- 						<option value="02">Segundo</option> -->
<!-- 						<option value="03">Tercero</option> -->
<!-- 						<option value="04">Cuarto</option> -->
<!-- 						<option value="05">Quinto</option> -->
<!-- 					</select> -->
<!-- 				</div> -->
<!-- 			</div> -->
			<div class="form-group">
				<label class="control-label col-md-3">Seleccione Linea</label>
				<div class="col-md-9">
					<select class="form-control input-sm" name="sl" id="sl">
						<option value="0">-- seleccion una --</option>
					<?php foreach ($sls as $sl) { ?>
						<option value="<?php echo $sl['sl_linea'].','.$sl['sl_sublinea']; ?>">(<?php echo $sl['sl_linea'].$sl['sl_sublinea']; ?>) <?php echo $sl['sl_descripcion']; ?></option>
					<?php }?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">Seleccione Producto</label>
				<div class="col-md-9">
					<select name="producto" id="producto" class="form-control input-sm"></select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">Precio Unitario</label>
				<div class="col-md-9">
					<input class="two-digits form-control input-sm" type="text" name="precio" id="precio">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">Precio Mayoreo</label>
				<div class="col-md-9">
					<input class="two-digits form-control input-sm" type="text" name="lis_prec_may" id="lis_prec_may" value="0">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">Minimo Mayoreo</label>
				<div class="col-md-9">
					<input class="two-digits form-control input-sm" type="text" name="lis_min_may" id="lis_min_may" value="0">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">Fin de Vigencia</label>
				<div class="col-md-9">
					<div class="input-group date input-group-sm" id="dp32" data-date="<?php echo date('Y-m-d'); ?>" data-date-format="yyyy-mm-dd">
						<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
						<input name="vigencia" id="vigencia" class="form-control input-sm" size="16" type="text" value="<?php echo date('Y-m-d'); ?>" readonly>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<input type="hidden" name="tabla" id="tabla" value="lista" />
			<input type="hidden" name="accion" id="accion" value="add" />
			<input type="hidden" name="lis_usuario" id="lis_usuario" value="<?php echo $_SESSION['u']; ?>" />
			<input type="hidden" name="id_proveedor" id="id_proveedor" value="<?php echo $_GET['id']; ?>" />
			<input type="hidden" name="prov_order" id="prov_orden" value="00" />
			<button class="btn btn-defatul btn-sm" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-ban-circle"></span> Cancelar</button>
			<button type="submit" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-plus"></span> Adicionar producto</button>
			<input type="reset" style="display: none;">
		</div>
	</div>
	</div>
	</form>
</div>
<div class="modal fade" role="dialog"  id="divEdit" name="divEdit">
	<form id="frmEdit" name="frmEdit" class="form-horizontal" role="form">
		<div class="modal-dialog">
		<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="myModalLabel"><?php echo $row['prov_nombre']; ?></h3>
		</div>
		<div class="modal-body">
<!-- 			<div class="form-group"> -->
<!-- 				<label class="control-label col-md-3">Seleccione Orden</label> -->
<!-- 				<div class="col-md-9"> -->
<!-- 					<select class="form-control input-sm" name="prov_orden2" id="prov_orden2"> -->
<!-- 						<option value="01">Primero</option> -->
<!-- 						<option value="02">Segundo</option> -->
<!-- 						<option value="03">Tercero</option> -->
<!-- 						<option value="04">Cuarto</option> -->
<!-- 						<option value="05">Quinto</option> -->
<!-- 					</select> -->
<!-- 				</div> -->
<!-- 			</div> -->
			<div class="form-group">
				<label class="control-label col-md-3">Producto</label>
				<div class="col-md-9">
					<p class="form-control-static text-info" id="producto"></p>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">Precio Detalle</label>
				<div class="col-md-9">
					<input class="two-digits form-control input-sm" type="text" name="precio2" id="precio2">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">Precio Mayoreo</label>
				<div class="col-md-9">
					<input class="two-digits form-control input-sm" type="text" name="precio3" id="precio3">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">Minimo Mayoreo</label>
				<div class="col-md-9">
					<input class="form-control input-sm" type="text" name="min_precio3" id="min_precio3">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-3">Fin de vigencia</label>
				<div class="col-md-9">
					<div class="input-group input-group-sm date" id="dp3" data-date="" data-date-format="yyyy-mm-dd">
						<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
						<input name="vigencia2" id="vigencia2" class="form-control input-sm" size="16" type="text" value="" readonly>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<input type="hidden" name="prov_orden" id="prov_orden" value="" />
			<input type="hidden" name="id_lista" id="id_lista" value="" />
			<input type="hidden" name="tabla" id="tabla" value="lista" />
			<input type="hidden" name="accion" id="accion" value="edit" />
			<input type="hidden" name="prov_order2" id="prov_orden2" value="00" />
			<input type="hidden" name="lis_usuario" id="lis_usuario" value="<?php echo $_SESSION['u']; ?>" />
			<input type="hidden" name="id_proveedor" id="id_proveedor" value="<?php echo $_GET['id']; ?>" />
			<button class="btn btn-sm btn-default" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-ban-circle"></span> Cancelar</button>
			<button type="submit" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-pencil"></span> Modificar producto</button>
			<input type="reset" style="display: none;">
		</div>
		</div>
		</div>
	</form>
</div>
<script>
$(function(){
	// Tabla
	var oTable = $('table').DataTable();
	$('#dp3').datepicker();
	$('#dp32').datepicker();
	var startDate = new Date(2012,1,20);
	var endDate = new Date(2012,1,25);
	$('#dp4').datepicker()
		.on('changeDate', function(ev){
			if (ev.date.valueOf() > endDate.valueOf()){
				$('#alert').show().find('strong').text('The start date can not be greater then the end date');
			} else {
				$('#alert').hide();
				startDate = new Date(ev.date);
				$('#startDate').text($('#dp4').data('date'));
			}
			$('#dp4').datepicker('hide');
		});
	$('#dp5').datepicker()
		.on('changeDate', function(ev){
			if (ev.date.valueOf() < startDate.valueOf()){
				$('#alert').show().find('strong').text('The end date can not be less then the start date');
			} else {
				$('#alert').hide();
				endDate = new Date(ev.date);
				$('#endDate').text($('#dp5').data('date'));
			}
			$('#dp5').datepicker('hide');
		});
});
</script>
<div class="well hidden">
	<div class="alert alert-error" id="alert">
		<strong>Oh snap!</strong>
	</div>
	<table class="table">
		<thead>
			<tr>
				<th>Start date<a href="#" class="btn small" id="dp4" data-date-format="yyyy-mm-dd" data-date="2012-02-20">Change</a></th>
				<th>End date<a href="#" class="btn small" id="dp5" data-date-format="yyyy-mm-dd" data-date="2012-02-25">Change</a></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id="startDate">2012-02-20</td>
				<td id="endDate">2012-02-25</td>
			</tr>
		</tbody>
	</table>
</div>
<!-- CARGANDO -->
	<div id="waiting" style="display: none;">
		<fieldset>
			<legend>procesando peticion, espere por favor...</legend>
			<img src="css/redmond/images/ajax-loader.gif" />
		</fieldset>
	</div>