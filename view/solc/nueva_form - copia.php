<style>
<!--
.invoice-title h2, .invoice-title h3 {
    display: inline-block;
}

.table > tbody > tr > .no-line {
    border-top: none;
}

.table > thead > tr > .no-line {
    border-bottom: none;
}

.table > tbody > tr > .thick-line {
    border-top: 2px solid;
}
-->
</style>
<script>
function GetURLParameter(sParam){
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
}
$(function(){
	$('#btnAutoriza').live('click', function(){
		var $btn = $(this);
		var $btn2 = $('#savetem');
		jConfirm('esta seguro de autorizar?', 'autorizar solicitud', function(answer){
			if(answer){
				var campos = new Object();
				var rtn=0;
				campos['prehsol_numero'] = GetURLParameter('ps');
				campos['empresa'] = GetURLParameter('es');
				campos['centrocosto'] = GetURLParameter('cs');
				campos['id_prehsol'] = $('#id_prehsol').val();
				campos['tabla'] = 'prehsol';
				campos['accion'] = 'autoriza';
				// Enviamos a borra de la tabla
				myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
				saveItem(rtn);
				if (rtn = 1) {
				$.ajax({
					type: 'POST',
					url: myUrl,
					data: {
						form: campos
					},
					beforeSend: function(){						
						$('input').addClass('disabled').prop('disabled', true);
						$.pnotify({
							title: 'autorizando..',
							text: 'la autorizacion de la solicitud esta en proceso, por favor espere...',
							type: 'info',
							icon: 'icon-wrench',
							hide: true,
							addclass: "stack-bar-top",
							cornerclass: "",
					        width: "100%",
					        stack: stack_bar_top
						});
						$btn.addClass('disabled');
						$btn.attr('disabled', 'disabled');
						$btn.prop("disabled", true);
						$btn2.addClass('disabled');
						$btn2.attr('disabled', 'disabled');
						$btn2.prop("disabled", true);
					},
					success : function(data){
						if(!$.isNumeric(data)){
							jAlert(data,'la pre-solicitud ha sufrido cambios');
						} else {
							if(data == 1){
								jAlert(data,"ha ocurrido un error");
								$('input').removeClass('disabled').prop('disabled', false);
							} else {
								$('strong').text('AUTORIZADO');
								$('#ok_auth').append('<i class="glyphicon glyphicon-ok"></i>');
								$btn2.remove();
								$btn.remove();
								jAlert("ha sido autorizada con exito!","exito");
							}
						}
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
					    $.pnotify({
							title: 'ha ocurrido un error..',
							text: 'durante la adicion ocurrio lo siguiente :'+textStatus,
							type: 'error',
							icon: 'icon-alert-sign',
							hide: true,
							addclass: "stack-bar-top",
							cornerclass: "",
							width: "100%",
							stack: stack_bar_top
						});
						$btn.removeClass('disabled');
						$btn.removeAttr('disabled');
						$btn.removeProp("disabled");
						$btn2.removeClass('disabled');
						$btn2.removeAttr('disabled');
						$btn2.removeProp("disabled");
						$('input').removeClass('disabled').prop('disabled', false);
					}
				});
				}
			    return false; // prevents default behavior
			} else {
				$.pnotify({
					title: 'accion cancelada.',
					text: 'la autorizacion de la solicitud ha sido cancelada',
					type: 'warning',
					icon: 'icon-stop',
					hide: true,
					addclass: "stack-bar-bottom",
					cornerclass: "",
			        width: "100%",
			        stack: stack_bar_bottom
				});
			}
		});
	});
	/*
	 * Adicionar un item
	 */
	$("#frmAddItem").validate({
		rules:{
			predsol_unidad : {
				required: true
			},
			predsol_descripcion : {
				required : true,
				maxlength : 52
			}
		},
		submitHandler: function(form) {
			var isVisible = $('#btnAutoriza').is(':visible');
			var isHidden = $('#btnAutoriza').is(':hidden');
			var isExist = $('#btnAutoriza').length;
			var isAutoriza = <?php echo $permisos[0]['acc_aut']; ?>;
			var campos=xajax.getFormValues("frmAddItem")
			campos['prehsol_numero'] = GetURLParameter('ps');
			campos['id_empresa'] = GetURLParameter('es');
			campos['id_cc'] = GetURLParameter('cs');
			var descripcion = $.trim(campos['predsol_descripcion']);
			var contar = 0;
			if(descripcion == '') {
				jAlert('digite descripcion',"alerta");
		    } else {
				revisa(campos);
				form.reset();
				$("#divAddItem").modal('hide');
				if(isExist >= 1 && isHidden && isAutoriza == 1) {
					$('#btnAutoriza').show('slow');
				}
				if(isExist <= 0 && isAutoriza == 1) {
					$('#botonera').append('<a id="btnAutoriza" name="btnAutoriza" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-ok"></i> Autorizar</a>').show('slow');
				}
		    }
		}
	});
	$('a').tooltip();
	/*
	 * Adiciona item
	 */
	 $('#addItem').unbind('click').bind('click', function(){
		$("#divAddItem").modal({
			backdrop: true,
			keyboard: false
		});
	});
	/*
	 * Borrar fila
	 */
	$("#delItem").live('click', function() {
		var atras = $(this).closest("tr");
		//alert(atras.find('td').eq(0).text());
		var campos = new Object();
		campos['prehsol_numero'] = GetURLParameter('ps');
		campos['id_empresa'] = GetURLParameter('es');
		campos['id_cc'] = GetURLParameter('cs');
		campos['id_predsol'] = atras.attr("id");
		campos['tabla'] = 'predsol';
		campos['accion'] = 'delete';
		// Enviamos a borra de la tabla
		myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
		$.ajax({
			type: 'POST',
			url: myUrl,
			data: {
				form: campos
			},
			success : function(data){
				if(!$.isNumeric(data)){
					jAlert(data,'la pre-solicitud ha sufrido cambios');
				} else {
					if(data == 1){
						jAlert(data,"error");
					} else {
						atras.find('td').fadeOut('slow', function(){
							// Nos borramos la linea
							atras.remove();
						});
						var cantidades = -1;
						$("#tablaPresol>tbody tr").each(function(i){
							cantidades += 1;
						});
						// Totales en los pies
						$("#tablaPresol>tfoot>tr").each(function(i){
							$(this).find('th').eq(1).text(cantidades);
						});
						if(cantidades <= 0) {
							$('#btnAutoriza').hide('slow');
						}
					}
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
			    $.pnotify({
					title: 'ha ocurrido un error..',
					text: 'durante la adicion ocurrio lo sigueinte :'+textStatus,
					type: 'error',
					icon: 'icon-alert-sign',
					hide: true,
					addclass: "stack-bar-top",
					cornerclass: "",
			        width: "100%",
			        stack: stack_bar_top
				});
			}
		});
	    return false; // prevents default behavior
	});
	/*
	 * Recorre tabla
	 */
	function revisa(campos) {
		var descripcion = $.trim(campos['predsol_descripcion']).toUpperCase();
		// Enviamos a guardar
		myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
		$.ajax({
			type: 'POST',
			url: myUrl,
			data: {
				form: campos
			},
			beforeSend: function() {
				$.pnotify({
					text: 'adicionando item....',
					hide: true
				});
			},
			success : function(data){
				if(!$.isNumeric(data)){
					jAlert(data,'la pre-solicitud ha sufrido cambios');
				} else {
					if(data == 1){
						jAlert(data,'error');
					} else {
						var tds = '<tr id="'+data+'">';
						tds += '<td class="col-md-9">'+descripcion+'</td>';
						tds += '<td class="col-md-1" style="text-align: center">'+campos['predsol_unidad']+"</td>";
						tds += '<td class="col-md-1" style="text-align: center">'+campos['predsol_cantidad']+"</td>";
						tds += '<td class="col-md-1 text-center"><button id="delItem" name="delItem" class="close">&times;</button></td>';
						tds += '</tr>';
						$(tds).hide().appendTo("#tablaPresol>tbody").fadeIn(500).css('display','');
						// Cantidad de items
						var cantidades = 0;
						$("#tablaPresol>tbody tr").each(function(i){
							cantidades += 1;
						});
						// Totales en los pies
						$("#tablaPresol>tfoot>tr").each(function(i){
							//cantidades += 1;
							$(this).find('th').eq(1).text(cantidades);
						});
					}
				}
			},
			error : function(XMLHttpRequest, textStatus, errorThrown) {
				$.pnotify({
					title: 'ha ocurrido un error..',
					text: 'durante la adicion ocurrio lo sigueinte :'+textStatus,
					type: 'error',
					icon: 'icon-alert-sign',
					hide: true,
					addclass: "stack-bar-top",
					cornerclass: "",
			        width: "100%",
			        stack: stack_bar_top
				});
			}
		});
	 };
	 /*
	 *
	 */
	 $('#saveItem').click(function(){
		saveItem('0'); 
	 });
	 /*
	 *
	 */
	 function saveItem(rtn){
		// 
		var rtn;
		// Verificamos cantidades
		 var acumc = 0;
		 for(i=1; i<=9; i++){
			 var id="#c"+i;
			 if($.isNumeric($(id).val())) {
			 	valor = parseInt($(id).val());
			 	acumc = acumc+valor;
			 }
		 }
		 if(acumc <= 0 ){
			 alert("Debe digitar cantidades.");
		 } else {
			 // Verificamos descripciones
			 contd = 0;
			 for(i=1; i<=9; i++){
				 var id="#d"+i;
				 if($.trim($(id).val()) != "") {
				 	contd = contd+1;
				 }
			 }
			 if(contd <= 0) {
				 alert("Debe digitar descripcion.");
			 } else {
				 // Verificamos justificaciones
				 conto = 0;
				 for(i=1; i<=3; i++){
					 var id="#ob"+i;
					 if($.trim($(id).val()) != "") {
					 	conto = conto+1;
					 }
				 }
				 if(conto <= 0) {
					 alert("Digite una justificacion");
				 } else {
					// Procesamos
					campos = xajax.getFormValues("frmAddForm");
					campos['prehsol_numero'] = GetURLParameter('ps');
					campos['id_empresa'] = GetURLParameter('es');
					campos['id_cc'] = GetURLParameter('cs');
					// Enviamos a guardar
					myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
					$.ajax({
						type: 'POST',
						url: myUrl,
						data: {
							form: campos
						},
						beforeSend: function() {
							$("#saveItem").addClass("disabled");
							$('#saveItem').after('<img src="images/FhHRx.gif"></img>');
							$.pnotify({
								text: 'adicionando item....',
								hide: true
							});
							$('input').addClass('disabled').prop('disabled', true);
						},
						success : function(data){
							if(!$.isNumeric(data)){
								jAlert(data,'la pre-solicitud ha sufrido cambios');
							} else {
								if(data == 1){
									jAlert(data,'error');
								} else {
									jAlert('Guardado con exito.');
								}
							}
							$("#saveItem").removeClass("disabled");
							$('input').removeClass('disabled').prop('disabled', false);
							rtn = 1;
						},
						error : function(XMLHttpRequest, textStatus, errorThrown) {
							$.pnotify({
								title: 'ha ocurrido un error..',
								text: 'durante la adicion ocurrio lo sigueinte :'+textStatus+XMLHttpRequest+errorThrown,
								type: 'error',
								icon: 'icon-alert-sign',
								hide: true,
								addclass: "stack-bar-top",
								cornerclass: "",
						        width: "100%",
						        stack: stack_bar_top
							});
							$("#saveItem").removeClass("disabled");
							$('input').removeClass('disabled').prop('disabled', false);
						}
					}).done(function(response, textStatus, jqXHR){
						$('#saveItem').nextAll('img').remove();
						$('input').removeClass('disabled').prop('disabled', false);
					});
				 }
			 }
		 }
	 }
	 
});
</script>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
    		<div class="invoice-title">
    			<h3>Gestion de Compra</h3><h3 class="pull-right">Solicitud # <?php echo $_GET['ps']; ?></h3>
    		</div>
    		<hr>
    		<div class="row">
    			<div class="col-xs-6">
    				<address>
    				<strong>Solicitado por:</strong><br>
    					<?php echo $infohsol[0]['emp_nombre'];?><br>
    					<?php echo $infohsol[0]['cc_descripcion'];?>
    				</address>
    			</div>
    			<div class="col-xs-6 text-right">
    				<address>
        				<strong>Fecha :</strong><br>
    					<?php
    					setlocale(LC_TIME, "");
    					setlocale(LC_TIME, "es_ES");
    					/*echo date('l jS \of F Y H:i:s A', strtotime($infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora']));
    					echo '<br>';*/
    					echo iconv('ISO-8859-1', 'UTF-8', strftime('%A %d de %B, %Y %I:%M:%S %p', strtotime($infohsol[0]['prehsol_fecha'] . ' ' . $infohsol[0]['prehsol_hora'])));
    					?>
    				</address>
    			</div>
    		</div>
    		<div class="row">
    			<div class="col-xs-6">
    			<address>
    				<?php if ($infohsol[0]['prehsol_estado'] == 1) { ?>
					<a id="saveItem" name="saveItem" class="btn btn-sm btn-primary" rel="tooltip" title="guardar cambios en solicitud">
						<i class="glyphicon glyphicon-floppy-save"></i> Guardar Cambios
					</a>
					<?php } else { ?>
						<span class="text-warning pull-right"><?php echo $conf->getEstado($infohsol[0]['prehsol_estado']); ?></span>
					<?php } ?>
					<br>
					</address>
    			</div>
    			<div class="col-xs-6 text-right">
    			<address>
					<div id="botonera" class="btn-group">
						<?php if ($infohsol[0]['prehsol_estado'] == 1 && count($detas) > 0 && $permisos[0]['acc_aut'] == '1') { ?>
							<a id="btnAutoriza" name="btnAutoriza" class="btn btn-sm btn-primary">
							<i class="glyphicon glyphicon-envelope"></i> Autorizar</a>
						<?php } ?>
					</div>
					<br>
					</address>
    			</div>
    		</div>
    	</div>
    </div>
    
    <div class="row">
    	<div class="col-md-12">
    		<div class="panel panel-default">
    			<div class="panel-heading">
    				<h3 class="panel-title"><strong>Detalle de solicitud</strong></h3>
    			</div>
    			<div class="panel-body">
    				<div class="table-responsive">
    					<table class="table table-condensed">
    						<thead>
                                <tr>
        							<td><strong>Item</strong></td>
        							<td class="text-center"><strong>Price</strong></td>
        							<td class="text-center"><strong>Quantity</strong></td>
        							<td class="text-right"><strong>Totals</strong></td>
                                </tr>
    						</thead>
    						<tbody>
    							<!-- foreach ($order->lineItems as $line) or some such thing here -->
    							<tr>
    								<td>BS-200</td>
    								<td class="text-center">$10.99</td>
    								<td class="text-center">1</td>
    								<td class="text-right">$10.99</td>
    							</tr>
                                <tr>
        							<td>BS-400</td>
    								<td class="text-center">$20.00</td>
    								<td class="text-center">3</td>
    								<td class="text-right">$60.00</td>
    							</tr>
                                <tr>
            						<td>BS-1000</td>
    								<td class="text-center">$600.00</td>
    								<td class="text-center">1</td>
    								<td class="text-right">$600.00</td>
    							</tr>
    							<tr>
    								<td class="thick-line"></td>
    								<td class="thick-line"></td>
    								<td class="thick-line text-center"><strong>Subtotal</strong></td>
    								<td class="thick-line text-right">$670.99</td>
    							</tr>
    							<tr>
    								<td class="no-line"></td>
    								<td class="no-line"></td>
    								<td class="no-line text-center"><strong>Shipping</strong></td>
    								<td class="no-line text-right">$15</td>
    							</tr>
    							<tr>
    								<td class="no-line"></td>
    								<td class="no-line"></td>
    								<td class="no-line text-center"><strong>Total</strong></td>
    								<td class="no-line text-right">$685.99</td>
    							</tr>
    						</tbody>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
</div>
<div class="col-md-12">
	<div class="text-center">
		<h3 class="text-blue">Solicitud de Compra</h3>
	</div>
	<form class="form-horizontal" role="form" name="frmAddForm" id="frmAddForm" method="post" action="">
		<table class="table table-condensed table-hovered table-striped">
			<thead>
				<tr>
					<th rowspan="2" class="text-center">CANTIDAD</th>
					<th rowspan="2" class="text-center">UNIDAD</th>
					<th rowspan="2" class="text-center">DESCRIPCION</th>
					<th colspan="3" class="text-center">COTIZACIONES</th>
				</tr>
				<tr>
					<th class="text-center">1</th>
					<th class="text-center">2</th>
					<th class="text-center">3</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="col-md-1"><input id="c1" name="c1" class="input-sm form-control" type="text" value="<?php echo $detas[0]['predsol_cantidad']; ?>"></td>
					<td class="col-md-1"><input id="u1" name="u1" class="input-sm form-control" type="text" value="<?php echo $detas[0]['predsol_unidad']; ?>"></td>
					<td class="col-md-7"><input id="d1" name="d1" class="input-sm form-control" type="text" maxlength="52" value="<?php echo $detas[0]['predsol_descripcion']; ?>"></td>
					<td class="col-md-1"><input id="c1o1" name="c1o1" class="input-sm form-control" type="text" value="<?php echo $detas[0]['predsol_coti1']; ?>"></td>
					<td class="col-md-1"><input id="c2o1" name="c2o1" class="input-sm form-control" type="text" value="<?php echo $detas[0]['predsol_coti2']; ?>"></td>
					<td class="col-md-1"><input id="c3o1" name="c3o1" class="input-sm form-control" type="text" value="<?php echo $detas[0]['predsol_coti3']; ?>"></td>
				</tr>
				<tr>
					<td class="col-md-1"><input id="c2" name="c2" class="input-sm form-control" type="text" value="<?php echo $detas[1]['predsol_cantidad']; ?>"></td>
					<td class="col-md-1"><input id="u2" name="u2" class="input-sm form-control" type="text" value="<?php echo $detas[1]['predsol_unidad']; ?>"></td>
					<td class="col-md-7"><input id="d2" name="d2" class="input-sm form-control" type="text" maxlength="52" value="<?php echo $detas[1]['predsol_descripcion']; ?>"></td>
					<td class="col-md-1"><input id="c1o2" name="c1o2" class="input-sm form-control" type="text" value="<?php echo $detas[1]['predsol_coti1']; ?>"></td>
					<td class="col-md-1"><input id="c2o2" name="c2o2" class="input-sm form-control" type="text" value="<?php echo $detas[1]['predsol_coti2']; ?>"></td>
					<td class="col-md-1"><input id="c3o2" name="c3o2" class="input-sm form-control" type="text" value="<?php echo $detas[1]['predsol_coti3']; ?>"></td>
				</tr>
				<tr>
					<td class="col-md-1"><input id="c3" name="c3" class="input-sm form-control" type="text" value="<?php echo $detas[2]['predsol_cantidad']; ?>"></td>
					<td class="col-md-1"><input id="u3" name="u3" class="input-sm form-control" type="text" value="<?php echo $detas[2]['predsol_unidad']; ?>"></td>
					<td class="col-md-7"><input id="d3" name="d3" class="input-sm form-control" type="text" maxlength="52" value="<?php echo $detas[2]['predsol_descripcion']; ?>"></td>
					<td class="col-md-1"><input id="c1o3" name="c1o3" class="input-sm form-control" type="text" value="<?php echo $detas[2]['predsol_coti1']; ?>"></td>
					<td class="col-md-1"><input id="c2o3" name="c2o3" class="input-sm form-control" type="text" value="<?php echo $detas[2]['predsol_coti2']; ?>"></td>
					<td class="col-md-1"><input id="c3o3" name="c3o3" class="input-sm form-control" type="text" value="<?php echo $detas[2]['predsol_coti3']; ?>"></td>
				</tr>
				<tr>
					<td class="col-md-1"><input id="c4" name="c4" class="input-sm form-control" type="text" value="<?php echo $detas[3]['predsol_cantidad']; ?>"></td>
					<td class="col-md-1"><input id="u4" name="u4" class="input-sm form-control" type="text" value="<?php echo $detas[3]['predsol_unidad']; ?>"></td>
					<td class="col-md-7"><input id="d4" name="d4" class="input-sm form-control" type="text" maxlength="52" value="<?php echo $detas[3]['predsol_descripcion']; ?>"></td>
					<td class="col-md-1"><input id="c1o4" name="c1o4" class="input-sm form-control" type="text" value="<?php echo $detas[3]['predsol_coti1']; ?>"></td>
					<td class="col-md-1"><input id="c2o4" name="c2o4" class="input-sm form-control" type="text" value="<?php echo $detas[3]['predsol_coti2']; ?>"></td>
					<td class="col-md-1"><input id="c3o4" name="c3o4" class="input-sm form-control" type="text" value="<?php echo $detas[3]['predsol_coti3']; ?>"></td>
				</tr>
				<tr>
					<td class="col-md-1"><input id="c5" name="c5" class="input-sm form-control" type="text" value="<?php echo $detas[4]['predsol_cantidad']; ?>"></td>
					<td class="col-md-1"><input id="u5" name="u5" class="input-sm form-control" type="text" value="<?php echo $detas[4]['predsol_unidad']; ?>"></td>
					<td class="col-md-7"><input id="d5" name="d5" class="input-sm form-control" type="text" maxlength="52" value="<?php echo $detas[4]['predsol_descripcion']; ?>"></td>
					<td class="col-md-1"><input id="c1o5" name="c1o5" class="input-sm form-control" type="text" value="<?php echo $detas[4]['predsol_coti1']; ?>"></td>
					<td class="col-md-1"><input id="c2o5" name="c2o5" class="input-sm form-control" type="text" value="<?php echo $detas[4]['predsol_coti2']; ?>"></td>
					<td class="col-md-1"><input id="c3o5" name="c3o5" class="input-sm form-control" type="text" value="<?php echo $detas[4]['predsol_coti3']; ?>"></td>
				</tr>
				<tr>
					<td class="col-md-1"><input id="c6" name="c6" class="input-sm form-control" type="text" value="<?php echo $detas[5]['predsol_cantidad']; ?>"></td>
					<td class="col-md-1"><input id="u6" name="u6" class="input-sm form-control" type="text" value="<?php echo $detas[5]['predsol_unidad']; ?>"></td>
					<td class="col-md-7"><input id="d6" name="d6" class="input-sm form-control" type="text" maxlength="52" value="<?php echo $detas[5]['predsol_descripcion']; ?>"></td>
					<td class="col-md-1"><input id="c1o6" name="c1o6" class="input-sm form-control" type="text" value="<?php echo $detas[5]['predsol_coti1']; ?>"></td>
					<td class="col-md-1"><input id="c2o6" name="c2o6" class="input-sm form-control" type="text" value="<?php echo $detas[5]['predsol_coti2']; ?>"></td>
					<td class="col-md-1"><input id="c3o6" name="c3o6" class="input-sm form-control" type="text" value="<?php echo $detas[5]['predsol_coti3']; ?>"></td>
				</tr>
				<tr>
					<td class="col-md-1"><input id="c7" name="c7" class="input-sm form-control" type="text" value="<?php echo $detas[6]['predsol_cantidad']; ?>"></td>
					<td class="col-md-1"><input id="u7" name="u7" class="input-sm form-control" type="text" value="<?php echo $detas[6]['predsol_unidad']; ?>"></td>
					<td class="col-md-7"><input id="d7" name="d7" class="input-sm form-control" type="text" maxlength="52" value="<?php echo $detas[6]['predsol_descripcion']; ?>"></td>
					<td class="col-md-1"><input id="c1o7" name="c1o7" class="input-sm form-control" type="text" value="<?php echo $detas[6]['predsol_coti1']; ?>"></td>
					<td class="col-md-1"><input id="c2o7" name="c2o7" class="input-sm form-control" type="text" value="<?php echo $detas[6]['predsol_coti2']; ?>"></td>
					<td class="col-md-1"><input id="c3o7" name="c3o7" class="input-sm form-control" type="text" value="<?php echo $detas[6]['predsol_coti3']; ?>"></td>
				</tr>
				<tr>
					<td class="col-md-1"><input id="c8" name="c8" class="input-sm form-control" type="text" value="<?php echo $detas[7]['predsol_cantidad']; ?>"></td>
					<td class="col-md-1"><input id="u8" name="u8" class="input-sm form-control" type="text" value="<?php echo $detas[7]['predsol_unidad']; ?>"></td>
					<td class="col-md-7"><input id="d8" name="d8" class="input-sm form-control" type="text" maxlength="52" value="<?php echo $detas[7]['predsol_descripcion']; ?>"></td>
					<td class="col-md-1"><input id="c1o8" name="c1o8" class="input-sm form-control" type="text" value="<?php echo $detas[7]['predsol_coti1']; ?>"></td>
					<td class="col-md-1"><input id="c2o8" name="c2o8" class="input-sm form-control" type="text" value="<?php echo $detas[7]['predsol_coti2']; ?>"></td>
					<td class="col-md-1"><input id="c3o8" name="c3o8" class="input-sm form-control" type="text" value="<?php echo $detas[7]['predsol_coti3']; ?>"></td>
				</tr>
				<tr>
					<td class="col-md-1"><input id="c9" name="c9" class="input-sm form-control" type="text" value="<?php echo $detas[8]['predsol_cantidad']; ?>"></td>
					<td class="col-md-1"><input id="u9" name="u9" class="input-sm form-control" type="text" value="<?php echo $detas[8]['predsol_unidad']; ?>"></td>
					<td class="col-md-7"><input id="d9" name="d9" class="input-sm form-control" type="text" maxlength="52" value="<?php echo $detas[8]['predsol_descripcion']; ?>"></td>
					<td class="col-md-1"><input id="c1o9" name="c1o9" class="input-sm form-control" type="text" value="<?php echo $detas[8]['predsol_coti1']; ?>"></td>
					<td class="col-md-1"><input id="c2o9" name="c2o9" class="input-sm form-control" type="text" value="<?php echo $detas[8]['predsol_coti2']; ?>"></td>
					<td class="col-md-1"><input id="c3o9" name="c3o9" class="input-sm form-control" type="text" value="<?php echo $detas[8]['predsol_coti3']; ?>"></td>
				</tr>
				<tr>
					<td colspan="6">Justificacion de la inversion o Gasto:</td>
				</tr>
				<tr>
					<td colspan="6"><input id="ob1" name="ob1" type="text" class="input-sm form-control" maxlength="100" value="<?php echo $infohsol[0]['prehsol_obs1'];?>"></td>
				</tr>
				<tr>
					<td colspan="6"><input id="ob2" name="ob2" type="text" class="input-sm form-control" maxlength="100" value="<?php echo $infohsol[0]['prehsol_obs2'];?>"></td>
				</tr>
				<tr>
					<td colspan="6"><input id="ob3" name="ob3" type="text" class="input-sm form-control" maxlength="100" value="<?php echo $infohsol[0]['prehsol_obs3'];?>"></td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="tabla" id="tabla" value="predsol" />
		<input type="hidden" name="accion" id="accion" value="save" />
		<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
		<input type="hidden" name="id_prehsol" id="id_prehsol" value="<?php echo $infohsol[0]['id_prehsol']; ?>" />
		<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $infohsol[0]['id_empresa']; ?>" />
		<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
		<input type="hidden" name="predsol_usuario" id="predsol_usuario" value="<?php echo $_SESSION['u']; ?>" />
	</form>
</div>
<pre>
<?php //print_r($detas); ?>
</pre>
<input type="hidden" value="<?php echo $infohsol[0]['id_prehsol']; ?>" id="id_prehsol" name="id_prehsol">
<div class="modal fade" role="dialog" id="divAddItem" name="divAddItem">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="frmAddItem" name="frmAddItem" class="form-horizontal" role="form">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 id="myModalLabel">Adicionar item</h3>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label col-md-3" for="predsol_cantidad">Cantidad</label>
						<div class="col-md-2">
							<input class="form-control input-sm" type="text" id="predsol_cantidad" name="predsol_cantidad">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3" for="predsol_cantidad">Unidad</label>
						<div class="col-md-3">
							<input class="form-control input-sm" type="text" id="predsol_unidad" name="predsol_unidad">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3" for="predsol_descripcion">Descripcion</label>
						<div class="col-md-7">
							<input type="text" class="form-control input-sm" id="predsol_descripcion" name="predsol_descripcion" maxlength="52" >
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3" for="predsol_descripcion"></label>
						<div class="col-md-7">
							<input type="text" class="form-control input-sm" id="predsol_descripcion" name="predsol_descripcion" maxlength="52" >
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="tabla" id="tabla" value="predsol" />
					<input type="hidden" name="accion" id="accion" value="add" />
					<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
					<input type="hidden" name="id_prehsol" id="id_prehsol" value="<?php echo $infohsol[0]['id_prehsol']; ?>" />
					<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $infohsol[0]['id_empresa']; ?>" />
					<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
					<input type="hidden" name="predsol_usuario" id="predsol_usuario" value="<?php echo $_SESSION['u']; ?>" />
					<button class="btn" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-ban-circle"></span> Cancelar</button>
					<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok-circle"></span> Agregar item</button>
					<input type="reset" style="display: none;">
				</div>
			</form>
		</div>
	</div>
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