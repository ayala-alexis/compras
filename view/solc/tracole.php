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
if ($_SESSION['solc'] == '0') {
	echo '<label class="alert alert-error">lo sentimos se ha revocado el acceso a esta opcion, consulte al administrador.</label>';
} else {
?>
<script>
$(document).ready(function(){
	// Asigancion de producto nuevo
    $("#frmAdd").validate({
	    rules: {
			prod_slinea: {
				required: true
			},
			prod_codigo:{
				required: true
			},
	     	prod_descripcion: {
	       		required: true
	     	}
	   },
	   messages: {
	     	prod_slinea: {
		     	required : "Seleccione sublinea"
	     	},
			prod_codigo: {
				required: "Digite codigo"
			},
	     	prod_descripcion: {
	       		required: "Digite descripcion"
	     	}
	   },
		submitHandler: function(form) {
			// campos en pantalla
			var id_Toggle = $('#mod_CC').text()+'-'+$('#mod_Solc').text()+'-'+$('#mod_Item').text()+'-'+$('#mod_Prehsol').text();
			var btnToggle = $('#'+id_Toggle);
			var id_ToggleProv = $('#mod_CC').text()+'-'+$('#mod_Solc').text()+'-'+$('#mod_Item').text()+'-PROV-'+$('#mod_Prehsol').text();
			var btnToggleProv = $('#'+id_ToggleProv);
			var lblPU = $('#'+'PU'+$('#mod_CC').text()+$('#mod_Prehsol').text()+$('#mod_Item').text());
			var lblTOT = $('#'+'TOT'+$('#mod_CC').text()+$('#mod_Prehsol').text()+$('#mod_Item').text());
			var campos=xajax.getFormValues("frmAdd");
			campos["id_predsol"] = $("#mod_Item").text();
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
				    if(!$.isNumeric(data)){
					    $.pnotify({
							title: 'Ha ocurrido un error.!',
        					text: data,
        					type: 'error',
        					hide: true,
							addclass: "stack-bar-top",
							stack: stack_bar_top,
							cornerclass: '',
							width: '100%'
        				});
					} else {
					    $.pnotify({
							title: 'ok',
        					text: 'producto adicionado y asignado a solicitud',
        					type: 'success',
        					hide: true,
							addclass: "stack-bar-bottom",
							stack: stack_bar_bottom,
							cornerclass: '',
							width: '100%'
        				});
						var codigo = $('#prod_codigo').val();
						btnToggle.html(codigo.toUpperCase());
						lblPU.html('0.00');
						lblTOT.html('0.00');
						btnToggleProv.html('asignar proveedor');
					    $("#frmAdd input[type=reset]").click();
						$("#loginModal").modal('toggle');
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
	// asignacion de producto existente
	$("#prod_form").validate({
	    rules: {
			producto: {
				required: true
			}
	   },
	   messages: {
	     	producto: {
		     	required : "Seleccione producto"
	     	}
	   },
		submitHandler: function(form) {
			// campos en pantalla
			var id_Toggle = $('#mod_CC').text()+'-'+$('#mod_Solc').text()+'-'+$('#mod_Item').text()+'-'+$('#mod_Prehsol').text();
			var btnToggle = $('#'+id_Toggle);
			var id_ToggleProv = $('#mod_CC').text()+'-'+$('#mod_Solc').text()+'-'+$('#mod_Item').text()+'-PROV-'+$('#mod_Prehsol').text();
			var btnToggleProv = $('#'+id_ToggleProv);
			var lblPU = $('#'+'PU'+$('#mod_CC').text()+$('#mod_Prehsol').text()+$('#mod_Item').text());
			var lblTOT = $('#'+'TOT'+$('#mod_CC').text()+$('#mod_Prehsol').text()+$('#mod_Item').text());
			// envio de formulario
			var campos=xajax.getFormValues("prod_form");
			campos["id_predsol"] = $("#mod_Item").text();
			campos["id_prehsol"] = $("#mod_Prehsol").text();
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
				    if(!$.isNumeric(data)){
					    $.pnotify({
							title: 'Ha ocurrido un error.!',
        					text: data,
        					type: 'error',
        					hide: true,
							addclass: "stack-bar-top",
							stack: stack_bar_top,
							cornerclass: '',
							width: '100%'
        				});
					} else {
					    $.pnotify({
							title: 'ok',
        					text: 'producto asignado a solicitud',
        					type: 'success',
        					hide: true,
							addclass: "stack-bar-bottom",
							stack: stack_bar_bottom,
							cornerclass: '',
							width: '100%'
        				});
					    var codigo = $('#producto').val();
						var arrCod = codigo.split(',');
						btnToggle.html(arrCod[0]);
						lblPU.html('0.00');
						lblTOT.html('0.00');
						btnToggleProv.html('asignar proveedor');
					    $("#prod_form input[type=reset]").click();
						$("#loginModal").modal('toggle');
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
			var id_Toggle = $('#modP_CC').text()+'-'+$('#modP_Solc').text()+'-'+$('#modP_Item').text()+'-PROV-'+$('#modP_Prehsol').text();//'+$('#modP_Codigo').text()+'-
			var btnToggle = $('#'+id_Toggle);
			var lblPU = $('#'+'PU'+$('#modP_CC').text()+$('#modP_Prehsol').text()+$('#modP_Item').text());
			var lblTOT = $('#'+'TOT'+$('#modP_CC').text()+$('#modP_Prehsol').text()+$('#modP_Item').text());
			var cant = 	$('#modP_Cantidad').text();
			// envio de formulario
			var campos=xajax.getFormValues("prov_form");
			campos["id_predsol"] = $("#modP_Item").text();
			campos["id_prehsol"] = $("#modP_Prehsol").text();
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
	$('.btn.toggle').live('click', function() {
		var arrID = $(this).attr('id').split('-');
		$('#mod_CC').html(arrID[0]);
		$('#mod_Solc').html(arrID[1]);
		$('#mod_Item').html(arrID[2]);
		$('#mod_Prehsol').html(arrID[3]);
		$('#mod_Codigo').html($(this).text());
		$('#producto').html('');
		$('#producto').remove();
		myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=solc&a=listarItems';
		$('#overlay').show();
		$.ajax({
			type: 'POST',
			url: myUrl,
			dataType: 'json',
			success: function(aData){
				var opcion='';
				$.each(aData, function(i, item){
					opcion += '<option value="'+item.prod_codigo+','+item.sl_linea+','+item.sl_sublinea+','+item.gas_tit_codigo+','+item.gas_det_codigo+'">['+item.prod_codigo+'] '+item.prod_descripcion+'</option>';
				});
				$("label#lblproducto").after('<div class="col-md-10"><select class="form-control input-sm" id="producto" name="producto">'+opcion+'</select></div>');
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert(textStatus);
			}
		});
	});
	$('.btn.toggle2').live('click', function() {
		var label = $(this).parent().parent().find('td').eq(0).find('.btn.toggle').text();
		var cant = $(this).parent().parent().find('td').eq(3).find('a#address').text();
		if(label !== 'asignar codigo') {
			var arrID = $(this).attr('id').split('-');
			$('#modP_CC').html(arrID[0]);
			$('#modP_Solc').html(arrID[1]);
			$('#modP_Item').html(arrID[2]);
			$('#modP_Codigo').html(label);
			$('#modP_Prehsol').html(arrID[4]);
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
			  '<button type="submit" class="btn btn-success editable-submit btn-sm"><i class="icon-ok icon-white"></i></button>' +
			 '<button type="button" class="btn editable-cancel btn-sm"><i class="icon-remove"></i></button>';
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
<h4 class="text-blue">Trabajar Solicitudes Colectadas</h4>
<form class="form-inline" action="?c=solc&a=tracole&id=5" method="post" role="form">
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
					<button type="submit" class="btn btn-sm btn-primary col-md-4"><i class="glyphicon glyphicon-wrench"></i> Trabajar Colectadas</button>
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
    		<th>Centro de Costo</th>
    		<th>Fecha</th>
    		<th>Numero</th>
  		</tr>
  	</thead>
  	<tbody>
<?php } ?>
<?php foreach($trabajar as $colecta){ ?>
	<tr class="accordion-toggle" data-toggle="collapse" href="#collapseTraSolc<?php echo $colecta['prehsol_numero_sol']; ?>">
  		<td>
			<label class="text-info">
				<span class="glyphicon glyphicon-chevron-down"></span> <?php echo '(<span id="cecosto">'.$colecta['cc_codigo'].'</span>)'.$colecta['cc_descripcion']; ?>
			</label>
		</td>
  		<td>
			<label class="text-info">
				<?php echo $colecta['prehsol_fecha']; ?>
			</label>
		</td>
    	<td>
			<label class="text-info">
				<?php echo $colecta['prehsol_numero_sol']; ?>
			</label>
		</td>
    </tr>
    <tr>
    	<td colspan="3">
    	<div id="collapseTraSolc<?php echo $colecta['prehsol_numero_sol']; ?>" class="accordion-body collapse">
    	<table class="table table-condensed accordion-inner nobd">
    	<tr>
    		<th>Codigo</th>
    		<th>Descripcion</th>
    		<th>Cantidad solicitada</th>
			<th>Cantidad a despachar</th>
			<th>Precio unitario</th>
			<th>Precio total</th>
			<th>Proveedor</th>
    	</tr>
    <?php foreach ($colecta['Det'] as $det) { ?>
    	<tr>
   			<td>
				<?php if(empty($det['prod_codigo'])){ ?>
					<a data-backdrop="static" data-toggle="modal" href="#loginModal" class="btn toggle btn-sm btn-default" id="<?php echo $colecta['cc_codigo'].'-'.$colecta['prehsol_numero_sol'].'-'.$det['id_predsol'].'-'.$colecta['id_prehsol']; ?>">
						<span class="glyphicon glyphicon-shopping-cart"></span> Asignar codigo
					</a>
				<?php } else { ?>
					<label data-backdrop="static" class="btn toggle btn-sm btn-default" data-toggle="modal" href="#loginModal" id="<?php echo $colecta['cc_codigo'].'-'.$colecta['prehsol_numero_sol'].'-'.$det['id_predsol'].'-'.$colecta['id_prehsol']; ?>">
						<span class="glyphicon glyphicon-pencil"></span> <?php echo $det['prod_codigo']; ?>
					</label>
				<?php } ?>
			</td>
   			<td><?php echo $det['predsol_descripcion']; ?></td>
   			<td><?php echo $det['predsol_cantidad']; ?></td>
			<td>
				<a href="#" id="address" data-type="address" data-pk="<?php echo $det['id_predsol']; ?>" data-url="post/post2.php" data-original-title="cantidad a autorizar" data-value="{city: '<?php echo $det['predsol_cantidad_aut']; ?>',street: '<?php echo $det['predsol_cantidad_aut_obs']; ?>'}" data-placement="left">
					<?php echo $det['predsol_cantidad_aut']; ?>
				</a>
			</td>
			<td>
				<span class="label" id="<?php echo 'PU'.$colecta['cc_codigo'].$colecta['id_prehsol'].$det['id_predsol']; ?>">
					<?php echo $det['predsol_prec_uni']; ?>
				</span>
			</td>
			<td>
				<span class="label" id="<?php echo 'TOT'.$colecta['cc_codigo'].$colecta['id_prehsol'].$det['id_predsol']; ?>">
					<?php echo $det['predsol_total']; ?>
				</span>
			</td>
			<td>
				<?php if(empty($det['id_proveedor'])){ ?>
					<a data-backdrop="static" data-toggle="modal" href="#provModal" class="btn toggle2 btn-sm btn-default" id="<?php echo $colecta['cc_codigo'].'-'.$colecta['prehsol_numero_sol'].'-'.$det['id_predsol'].'-PROV-'.$colecta['id_prehsol']; ?>">
						<span class="glyphicon glyphicon-question-sign"></span> Asignar proveedor
					</a>
				<?php } else { ?>
					<label data-backdrop="static" class="btn toggle2 btn-sm btn-default" data-toggle="modal" href="#provModal" id="<?php echo $colecta['cc_codigo'].'-'.$colecta['prehsol_numero_sol'].'-'.$det['id_predsol'].'-PROV-'.$colecta['id_prehsol']; ?>">
						<span class="glyphicon glyphicon-pencil"></span> <?php echo nombreProveedor($det['id_proveedor']); ?>
					</label>
				<?php } ?>
			</td>
  		</tr>
  	<?php } ?>
  		</table>
  		</div>
  		</td>
  	</tr>
<?php } ?>
	</tbody>
</table>
<!-- asignacion, creacion de codigo -->
<div class="modal fade" id="loginModal">
	<div class="modal-dialog">
	<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">✕</button>
        <h3>asignacion de producto a solicitud de compra</h3>
		<p>
			<i class="glyphicon glyphicon-home"></i>&nbsp;Centro de Costo : <label class="text-info" id="mod_CC"></label>&nbsp;&nbsp;&nbsp;
			<i class="glyphicon glyphicon-shopping-cart"></i>&nbsp;Numero de Solicitud : <label class="text-info" id="mod_Solc"></label>&nbsp;&nbsp;&nbsp;
			<i class="glyphicon glyphicon-check"></i>&nbsp;Item : <label class="text-info" id="mod_Item"></label>&nbsp;&nbsp;
			<i class="glyphicon glyphicon-check"></i>&nbsp;Codigo : <label class="text-info" id="mod_Codigo"></label>&nbsp;&nbsp;
			<i class="glyphicon glyphicon-cog"></i>&nbsp;ID : <label class="text-info" id="mod_Prehsol"></label>
		</p>
    </div>
	<div class="modal-body">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div id="modalTab">
                    <div class="tab-content">
                        <div class="tab-pane active" id="login">
                            <form class="form-horizontal" role="form" method="post" id="prod_form" name="prod_form">
                           		<div class="form-group">
                           			<label for="producto" class="col-md-2 control-label">Producto</label>
                                	<label id="lblproducto"></label>
                                </div>
								<input type="hidden" value="assign" id="accion" name="accion">
								<input type="hidden" value="predsol" id="tabla" name="tabla">
								<input type="hidden" value="<?php echo $id_empresa; ?>" id="empresa" name="empresa">
								<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
								<input type="reset" class="hidden" />
								<div class="form-group">
								    <div class="col-md-offset-2 col-md-10">
								      	<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"> Asignar</button>&nbsp;&nbsp;
										<a href="#forgotpassword" data-toggle="tab">Crear Producto Nuevo</a>
								    </div>
								</div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="forgotpassword">
                        	<h3 class="text-info">Creacion de Producto Nuevo</h3>
							<form class="form-horizontal" role="form" id="frmAdd" name="frmAdd" method="POST">
								<div class="form-group">
									<label class="control-label col-md-3" for="prod_slinea">Sublinea</label>
										<?php
										try {
											$db = DB::getInstance ();
											$conf = Configuracion::getInstance ();
										} catch ( Exception $e ) {
											echo $e->getMessage ();
											die ();
										}
										try {
											$sql = "Select s.*, g.gas_tit_codigo, g.gas_det_codigo From " . $conf->getTbl_sublinea()." s Join ".$conf->getTbl_tagasto()." g On g.id_tagasto = s.id_tagasto Order By s.sl_linea, s.sl_sublinea";
											$lista = $db->ejecutar ( $sql );
										} catch ( Exception $e ) {
											echo $e->getMessage ();
											die ();
										}
										?>
										<div class="col-md-9">
											<select class="form-control input-sm" name="prod_slinea" id="prod_slinea">
											<?php
											$in20 = 0;
											while ( $fila = mysqli_fetch_array ( $lista ) ) {
												if ($fila [2] == '00') {
													if ($in20 == 1) {
														?>
														</optgroup>
														<?php
													}
													?>
													<optgroup label="<?php echo $fila[3]; ?>">
													<?php
												} else {
													?>
												<option value="<?php echo $fila[1].",".$fila[2].",".$fila[8].",".$fila[9]; ?>">[<?php echo $fila[1].$fila[2]; ?>]<?php echo $fila[3]; ?></option>
											<?php
												}
												$in20 = 1;
											}
											?>
											</select>
										</div>
								</div>

								<div class="form-group">
									<label class="control-label col-md-3" for="prod_codigo">Codigo</label>
									<div class="col-md-9">
										<input class="form-control input-sm" type="text" name="prod_codigo" id="prod_codigo" placeholder="digite codigo" />
									</div>
								</div>

								<div class="form-group">
									<label class="control-label col-md-3" for="prod_descripcion">Descripcion</label>
									<div class="col-md-9">
										<input class="form-control input-sm" type="text" name="prod_descripcion" id="prod_descripcion" placeholder="digite descripcion" />
									</div>
								</div>

								<div class="form-group">
									<label class="control-label col-md-3" for="prod_observacion">Observaciones</label>
									<div class="col-md-9">
										<textarea class="form-control input-sm" rows="5" name="prod_observacion" id="prod_observacion" placeholder="digite observaciones">
										</textarea>
									</div>
								</div>

								<input type="hidden" value="1" id="prod_solc" name="prod_solc">
								<input type="hidden" value="0" id="prod_req" name="prod_req">
								<input type="hidden" value="add_assign" id="accion" name="accion">
								<input type="hidden" value="producto" id="tabla" name="tabla">
								<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
								<div class="form-group">
    								<div class="col-md-offset-3 col-md-9">
										<input type="reset" class="hidden" />
										<button type="submit" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-ok"></span> Crear y Asignar</button>&nbsp;&nbsp;
										<a href="#login" data-toggle="tab">Seleccionar Producto Existente</a>
									</div>
								</div>
							</form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</div>
<!-- aisgnacion de proveedor aitem -->
<div class="modal fade" id="provModal">
	<div class="modal-dialog">
	<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">✕</button>
        <h3>Asignacion de proveedor</h3>
		<p>
			<i class="glyphicon glyphicon-home"></i>&nbsp;Centro de Costo : <label class="label label-info" id="modP_CC"></label>&nbsp;&nbsp;&nbsp;
			<i class="glyphicon glyphicon-shopping-cart"></i>&nbsp;Numero de Solicitud : <label class="label label-info" id="modP_Solc"></label>&nbsp;&nbsp;&nbsp;
			<i class="glyphicon glyphicon-check"></i>&nbsp;Item : <label class="label label-info" id="modP_Item"></label>&nbsp;&nbsp;
			<i class="glyphicon glyphicon-cog"></i>&nbsp;Codigo : <label class="label label-info" id="modP_Codigo"></label>&nbsp;&nbsp;
			<i class="glyphicon glyphicon-cog"></i>&nbsp;ID : <label class="label label-info" id="modP_Prehsol"></label>
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
									<div>
										<label class="form-control-static col-md-9 text-info" id="modP_Cantidad"></label>
									</div>
                            	</div>
                                <div class="form-group">
                                	<label class="control-label col-md-3" id="lblproveedor">Proveedor</label>
                                </div>
								<input type="hidden" value="assign_prov" id="accion" name="accion">
								<input type="hidden" value="predsol" id="tabla" name="tabla">
								<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
								<input type="reset" class="hidden" />
								<div class="form-group">
    								<div class="col-md-offset-3 col-md-9">
										<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span> Asignar Proveedor y Precio</button>&nbsp;&nbsp;
										<!-- <a href="#forgotpassword" data-toggle="tab">Crear Producto Nuevo</a> -->
									</div>
								</div>
                            </form>
                            <br>
                        </div>
                        <div class="tab-pane fade" id="forgotpassword">
							<fieldset>
								<legend>crear producto</legend>
							</fieldset>
							<form class="form form-horizontal" id="frmAddProv" name="frmAddProv" method="POST">
								<div class="control-group">
									<label class="control-label" for="prod_slinea">Sublinea</label>
									<div class="controls">
										<?php
										try {
											$db = DB::getInstance ();
											$conf = Configuracion::getInstance ();
										} catch ( Exception $e ) {
											echo $e->getMessage ();
											die ();
										}
										try {
											$sql = "Select s.*, g.gas_tit_codigo, g.gas_det_codigo From " . $conf->getTbl_sublinea()." s Join ".$conf->getTbl_tagasto()." g On g.id_tagasto = s.id_tagasto Order By s.sl_linea, s.sl_sublinea";
											$lista = $db->ejecutar ( $sql );
										} catch ( Exception $e ) {
											echo $e->getMessage ();
											die ();
										}
										?>
										<select class="input-xlarge" name="prod_slinea" id="prod_slinea">
										<?php
										$in20 = 0;
										while ( $fila = mysqli_fetch_array ( $lista ) ) {
											if ($fila [2] == '00') {
												if ($in20 == 1) {
													?>
													</optgroup>
													<?php
												}
												?>
												<optgroup label="<?php echo $fila[3]; ?>">
												<?php
											} else {
												?>
											<option value="<?php echo $fila[1].",".$fila[2].",".$fila[8].",".$fila[9]; ?>">[<?php echo $fila[1].$fila[2]; ?>]<?php echo $fila[3]; ?></option>
										<?php
											}
											$in20 = 1;
										}
										?>
										</select>
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="prod_codigo">Codigo</label>
									<div class="controls">
										<input class="input-xlarge" type="text" name="prod_codigo" id="prod_codigo" placeholder="digite codigo" />
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="prod_descripcion">Descripcion</label>
									<div class="controls">
										<input class="input-xlarge" type="text" name="prod_descripcion" id="prod_descripcion" placeholder="digite descripcion" />
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="prod_observacion">Observaciones</label>
									<div class="controls">
										<textarea class="input-xlarge" rows="5" name="prod_observacion" id="prod_observacion" placeholder="digite observaciones">
										</textarea>
									</div>
								</div>

								<input type="hidden" value="1" id="prod_solc" name="prod_solc">
								<input type="hidden" value="0" id="prod_req" name="prod_req">
								<input type="hidden" value="add_assign" id="accion" name="accion">
								<input type="hidden" value="producto" id="tabla" name="tabla">
								<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
								<p>
									<input type="reset" class="hidden" />
									<button type="submit" class="btn btn-primary">Crear y Asignar</button>&nbsp;&nbsp;
									<a href="#login" data-toggle="tab">Seleccionar Producto Existente</a>
								</p>
							</form>
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