<?php
//print_r($detas); 
//print_r($infohsol);
?>
<style>
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
.progress, .alert {
    margin: 0px;
    display: none;
}

.alert {
    display: none;
}
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

	/*$('#frmAuto').submit(function(e){
		$('body').removeClass('loaded');
	    return;
	    e.preventDefault();
	});*/

	$('#frmAuto').validate({
		rules:{
			observa_sol : {
				required: true
			}
		},
		submitHandler: function(form) {
			var items = ($('table#tablaPresol > tbody tr:last').index() + 1);
			if(items <= 0) {
				notie.alert('error', 'la solicitud esta vacia!', 3);
			} else {
				if( $('#observa_sol').val().trim().length == 0 ) {
					notie.alert('error', 'debe digitar una observacion!', 3);
			    } else {
					$('body').removeClass('loaded');
					form.submit();
			    }
			}
		}
	});

	$.validator.setDefaults({
	    ignore: ''
	});

	// Variable to store your files
	var files;

	// Add events
	$('input[type=file]').on('change', prepareUpload);

	// Grab the files and set them to our variable
	function prepareUpload(event)
	{
	  files = event.target.files;
	}
		
	
	$('#btnAutoriza').live('click', function(){
		var $btn = $(this);
		var $btn2 = $('#addItem');
		var $btn3 = $('#btnRechaza');
		jConfirm('esta seguro de aprobar?', 'aprobar solicitud', function(answer){
			console.log(answer);
			if(answer){
				var campos = new Object();
				rtn=1;
				campos['prehsol_numero'] = GetURLParameter('ps');
				campos['empresa'] = GetURLParameter('es');
				campos['centrocosto'] = GetURLParameter('cs');
				campos['id_prehsol'] = $('#id_prehsol').val();
				campos['tabla'] = 'prehsol';
				campos['accion'] = 'autoriza';
				campos['categoria'] = $('#categoria').val();
				campos['aprueba_sol'] = $.trim($('#aprueba_sol').val());
				var items = ($('table#tablaPresol > tbody tr:last').index() + 1);
				//alert(campos["categoria"]);
				if(items <= 0) {
					notie.alert('error', 'la solicitud esta vacia!', 3);
				} else {
					if($('#categoria').val().trim().length == 0) {
						notie.alert('error', 'seleccione una categoria valida!', 3);
					} else {
						if( $('#aprueba_sol').val().trim().length == 0 ) {
							notie.alert('error', 'debe digitar una observacion para aprobar!', 3);
					    } else {
					    	// Enviamos a borrar de la tabla
							myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
							//saveItem(rtn);
							if (rtn = 1) {
							$.ajax({
								type: 'POST',
								url: myUrl,
								data: {
									form: campos
								},
								beforeSend: function(){						
									$('input').addClass('disabled').prop('disabled', true);
									notie.alert('info', 'la autorizacion de la solicitud esta en proceso, por favor espere...', 3);
									$btn.addClass('disabled');
									$btn.attr('disabled', 'disabled');
									$btn.prop("disabled", true);
									$btn2.addClass('disabled');
									$btn2.attr('disabled', 'disabled');
									$btn2.prop("disabled", true);
									$btn3.addClass('disabled');
									$btn3.attr('disabled', 'disabled');
									$btn3.prop("disabled", true);
								},
								success : function(data){
									if(!$.isNumeric(data)){
										notie.alert('error', 'la pre-solicitud ha sufrido cambios', 3);
									} else {
										if(data == 1){
											notie.alert('success', 'ha ocurrido un error', 3);
											$('input').removeClass('disabled').prop('disabled', false);
										} else {
											//$('strong').text('AUTORIZADO');
											$('[id=ok_auth]').append('<i class="glyphicon glyphicon-ok"></i>');
											$btn2.remove();
											$btn.remove();
											$btn3.remove();
											$("[id=delItem]").remove();
											var $progress = $('.progress');
											var $progressBar = $('.progress-bar');
											var $alert = $('.alert');
											//$progress.css('display', 'block');
		
											$('body').removeClass('loaded');
		
											//$_REQUEST['ps']
											myUrl = location.protocol + "//" + location.host + "/sics/class/PHPMailer/send1.php?"+$('#id_prehsol').val();
											window.location.href = myUrl;
											
											/*setTimeout(function() {
											    $progressBar.css('width', '10%');
											    setTimeout(function() {
											        $progressBar.css('width', '30%');
											        setTimeout(function() {
											            $progressBar.css('width', '100%');
											            setTimeout(function() {
											                $progress.css('display', 'none');
											                $alert.css('display', 'block');
											            }, 500); // WAIT 5 milliseconds
											        }, 2000); // WAIT 2 seconds
											    }, 1000); // WAIT 1 seconds
											}, 1000); // WAIT 1 second*/
											//jAlert("ha sido autorizada con exito!","exito");
										}
									}
								},
								error : function(XMLHttpRequest, textStatus, errorThrown) {
								    notie.alert('error', 'ha ocurrido un error!<br>'+textStatus, 3);
									$btn.removeClass('disabled');
									$btn.removeAttr('disabled');
									$btn.removeProp("disabled");
									$btn2.removeClass('disabled');
									$btn2.removeAttr('disabled');
									$btn2.removeProp("disabled");
									$btn3.removeClass('disabled');
									$btn3.removeAttr('disabled');
									$btn3.removeProp("disabled");
									$('input').removeClass('disabled').prop('disabled', false);
								}
							});
							}
					    }
					}
				}
			    return false; // prevents default behavior
			} else {
				notie.alert('info', 'aprobacion cancelada!', 3);
			}
		});
	});
	/*
	 * Adicionar un item
	 */
	$("#frmAddItem").validate({
		rules:{
			predsol_cantidad : {
				required: true
			},
			predsol_unidad : {
				required: true
			},
			predsol_descripcion : {
				required : true
			}
		},
		submitHandler: function(form) {
			var isVisible = $('#btnAutoriza').is(':visible');
			var isHidden = $('#btnAutoriza').is(':hidden');
			var isExist = $('#btnAutoriza').length;
			var isAutoriza = <?php echo $permisos[0]['acc_aut']; ?>;		
			var campos=xajax.getFormValues("frmAddItem");
			campos['prehsol_numero'] = GetURLParameter('ps');
			campos['id_empresa'] = GetURLParameter('es');
			campos['id_cc'] = GetURLParameter('cs');			
			var descripcion = $.trim(campos['predsol_descripcion']);
			if(descripcion == '') {
				jAlert('digite descripcion',"alerta");
		    } else {
				//revisa(formData);
				form.submit();
				//form.reset();
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
					notie.alert('warning', 'la pre-solicitud ha sufrido cambios!<br>'+data, 3);
				} else {
					if(data == 1){
						notie.alert('warning', 'ha ocurrido un error!<br>'+data, 3);
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
			    notie.alert('warning', 'la pre-solicitud ha sufrido cambios!<br>'+textStatus, 3);
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
						tds += '<td>'+descripcion+'</td>';
						tds += '<td style="text-align: center">'+campos['predsol_unidad']+"</td>";
						tds += '<td style="text-align: center">'+campos['predsol_cantidad']+"</td>";
						tds += '<td></td>';
						tds += '<td class="text-center"><button id="delItem" name="delItem" class="close">&times;</button></td>';
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
		// Procesamos
		rtn = 0;
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
});
</script>
<script src="js/tinymce/tinymce.min.js"></script>
<script>
	tinymce.init({ 
		selector:'textarea',
		menubar: false,
		skin: "lightgray",
		statusbar: false,
		theme: 'modern',
		setup: function(ed){
            ed.on("blur", function () {
                $("#" + ed.id).val(tinyMCE.activeEditor.getContent());
            });
            ed.on("KeyDown", function(evt) {
                //if ( $(ed.getBody()).text().length+1 > ed.getParam('max_chars')){
                if ( $(ed.getBody()).text().length+1 > $(tinyMCE.get(tinyMCE.activeEditor.id).getElement()).attr('max_chars')){
                	document.getElementById("character_count").innerHTML = "Maximo de letras permitido: 250";
                	$('#character_count').closest('.form-group').addClass('has-error');
                	$('#character_count').addClass("text-error");
                	if(evt.keyCode != 8 && evt.keyCode != 46) {
                    	evt.preventDefault();
                    	evt.stopPropagation();
                    	return false;
                    }
                } else {
                	document.getElementById("character_count").innerHTML = "Letras : " + $(ed.getBody()).text().length;
                	$('#character_count').addClass("text-success");
                }
            });
        }
	});
</script>
<div class="container-fluid">
	<!-- Start Page Loading -->
    <div id="loader-wrapper">
    	<h1>Espere, completando accion...</h1>
        <div id="loader">
        </div>        
        <div class="loader-section section-left"></div>
        <div class="loader-section section-right"></div>
    </div>
    <!-- End Page Loading -->
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
    					echo '<br>';
    					echo $infohsol[0]['prehsol_obs1'];
    					?>
    				</address>
    			</div>
    		</div>
    		<div class="row">
    			<div class="col-xs-6">
    			<address>
    				<div class="progress">
					    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
					</div>
					<div class="alert alert-success" role="alert">La solicitud ha sido aprobada.</div>
    				<?php if ($infohsol[0]['prehsol_estado'] == 1 || $infohsol[0]['prehsol_estado'] == 0) { ?>
						<a id="addItem" name="addItem" class="btn btn-sm btn-success" rel="tooltip" title="agregar item a solicitud">
							<i class="glyphicon glyphicon-plus"></i>AGREGAR ITEM
						</a>
					<?php } else { ?>
						<span class="text-danger pull-right"><?php echo $conf->getEstadoSC($infohsol[0]['prehsol_estado']); ?></span>
						<?php if ($infohsol[0]['prehsol_estado'] == 3) { ?>
								<a id="btnGestiona" name="btnGestiona"  class="btn btn-sm btn-success" href="?c=solc&a=autoges&id=5&ps=<?php echo $infohsol[0]['id_prehsol']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
									<i class="glyphicon glyphicon-send"></i> APROBAR GESTION
								</a>
						<?php } ?>
					<?php } ?>
					<br>
					</address>
    			</div>
    			<div class="col-xs-6 text-right">
    			<address>
						<?php if ($infohsol[0]['prehsol_estado'] == 1 && count($detas) > 0 && $permisos[0]['acc_aut'] == '1') { ?>
							<a id="btnRechaza" name="btnRechaza"  class="btn btn-sm btn-danger" href="?c=solc&a=deny&id=5&ps=<?php echo $infohsol[0]['id_prehsol']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
								<i class="glyphicon glyphicon-ban-circle"></i> RECHAZAR
							</a>
						<?php } ?>
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
    					<table id="tablaPresol" name="tablaPresol" class="table table-condensed">
    						<thead>
                                <tr>
        							<td><strong>Item</strong></td>
        							<td class="text-center"><strong>Unidad</strong></td>
        							<td class="text-center"><strong>Cantidad</strong></td>
        							<td class="text-center"><em class="fa fa-cog"></em></td>
                                </tr>
    						</thead>
    						<tbody>
    							<?php 
    								foreach($detas as $deta) {
    								//	print_r($deta);
    							?>
    							<tr id="<?php echo $deta[0]; ?>">
    								<td><span id="ok_auth"></span><?php echo $deta[11];?></td>
    								<td class="text-center"><?php echo $deta[7];?></td>
    								<td class="text-center"><?php echo $deta[4];?></td>
    								<td class="text-right">
    									<?php if ($infohsol[0]['prehsol_estado'] <= 1) { ?>
    									<a href="#" id="delItem" name="delItem" class="btn btn-xs btn-danger"><i class="glyphicon glyphicon-remove"></i></a>
    									<?php } ?>
    								</td>
    							</tr>
    							<?php } ?>
    						</tbody>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
    <div class="row">
    	<div class="col-md-12">
    		<?php if ($infohsol[0]['prehsol_estado'] == 0 ) { ?>
				<form class="form form-horizontal" id="frmAuto" name="frmAuto" enctype="multipart/form-data" method="post" action="?c=solc&a=auto&id=5&ps=<?php echo $infohsol[0]['id_prehsol']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
					<div class="form-group">
						<label class="col-sm-3 control-label" for="predsol_coti1">Cotizacion 1</label>
						<div class="col-sm-9">
							<input type="file" id="predsol_coti1" name="predsol_coti1" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label" for="predsol_coti1">Cotizacion 2</label>
						<div class="col-sm-9">
							<input type="file" id="predsol_coti2" name="predsol_coti2" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label" for="predsol_coti1">Cotizacion 3</label>
						<div class="col-sm-9">
							<input type="file" id="predsol_coti3" name="predsol_coti3" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label" for="observa_sol">Observaciones</label>
						<div class="col-sm-9">
							<textarea max_chars="250" rows="20" cols="3" id="observa_sol" name="observa_sol" required></textarea>
							<span id="character_count" class="help-block"></span>
						</div>
					</div>
					<div class="col-sm-offset-3 col-sm-9">
						<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
						<input type="hidden" name="id_prehsol" id="id_prehsol" value="<?php echo $infohsol[0]['id_prehsol']; ?>" />
						<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $infohsol[0]['id_empresa']; ?>" />
						<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
						<input type="hidden" name="predsol_usuario" id="predsol_usuario" value="<?php echo $_SESSION['u']; ?>" />
						<input type="hidden" name="prehsol_numero" id="prehsol_numero" value="<?php echo $_GET['ps']; ?>" />
						<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $_GET['es']; ?>" />
						<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $_GET['cs']; ?>" />
						<button type="submit" id="btnEnviaAuth" name="btnEnviaAuth" class="btn btn-lg btn-primary">
							<i class="glyphicon glyphicon-send"></i>&nbsp;ENVIAR PARA AUTORIZACION
						</button>
					</div>
				</form>
			<?php } ?>
			<?php if ($infohsol[0]['prehsol_estado'] == 1 && count($detas) > 0 && $permisos[0]['acc_aut'] == '1' ) { ?>
				<form class="form-horizontal" id="frmAuto2" name="frmAuto2">
					<div class="form-group"> 
						<label for="categoria" class="col-sm-3 control-label">Seleccione Categoria</label> 
						<div class="col-sm-9"> 
							<select class="form-control" id="categoria" name="categoria" placeholder="Categoria" required>
							<option value=""></option>
								<option value="0">Sin Categoria</option>
								<?php 
								foreach ($cats as $cat){
									echo '<option value="'.$cat['id_categoria'].'">'.$cat['nombre_categoria'].'</option>';
								}
								?>
							</select> 
						</div>  
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label" for="aprueba_sol">Observaciones de aprobacion</label>
						<div class="col-sm-9">
							<textarea max_chars="250" rows="20" cols="3" id="aprueba_sol" name="aprueba_sol" required></textarea>
							<span id="character_count" class="help-block"></span>
						</div>
					</div>
					<div class="col-sm-offset-3 col-sm-9">
						<a id="btnAutoriza" name="btnAutoriza" class="btn btn-lg btn-primary">
							<i class="glyphicon glyphicon-check"></i> APROBAR
						</a>
					</div>
				</form>
			<?php } ?>
    	</div>
    </div>
</div>
<form class="form-horizontal" role="form" name="frmAddForm" id="frmAddForm" method="post" action="">
	<input type="hidden" name="tabla" id="tabla" value="predsol" />
	<input type="hidden" name="accion" id="accion" value="save" />
	<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
	<input type="hidden" name="id_prehsol" id="id_prehsol" value="<?php echo $infohsol[0]['id_prehsol']; ?>" />
	<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $infohsol[0]['id_empresa']; ?>" />
	<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
	<input type="hidden" name="predsol_usuario" id="predsol_usuario" value="<?php echo $_SESSION['u']; ?>" />
</form>
<?php
	/*echo '<pre>'; 
	echo $_SERVER['REQUEST_URI'].'<br>';
	echo $_SERVER['PHP_SELF'].'<br>';
	echo $_SERVER['HTTP_HOST'].'<br>';
	echo $_SERVER["QUERY_STRING"];
    //print_r($detas);
    echo '</pre>'; */
?>
<input type="hidden" value="<?php echo $infohsol[0]['id_prehsol']; ?>" id="id_prehsol" name="id_prehsol">
<div class="modal fade" role="dialog" id="divAddItem" name="divAddItem">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="frmAddItem" name="frmAddItem" enctype="multipart/form-data" method="post" action="?<?php echo $_SERVER['QUERY_STRING']; ?>" class="form-horizontal" role="form">
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
							<textarea style="width: 100%;" id="predsol_descripcion" name="predsol_descripcion"></textarea>
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
					<input type="hidden" name="prehsol_numero" id="prehsol_numero" value="<?php echo $_GET['ps']; ?>" />
					<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $_GET['es']; ?>" />
					<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $_GET['cs']; ?>" />
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