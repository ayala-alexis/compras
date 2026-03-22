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
				campos['ci_numero'] = GetURLParameter('ps');
				campos['empresa'] = GetURLParameter('es');
				campos['centrocosto'] = GetURLParameter('cs');
				campos['id_ci'] = $('#id_ci').val();
				campos['tabla'] = 'ci_enc';
				campos['accion'] = 'autoriza';
				campos['aprueba_sol'] = $.trim($('#aprueba_sol').val());
				var items = ($('table#tablaPresol > tbody tr:last').index() + 1);
				//alert(campos["categoria"]);
				if(items <= 0) {
					notie.alert('error', 'la solicitud esta vacia!', 3);
				} else {
					if( $('#aprueba_sol').val().trim().length == 0 ) {
						notie.alert('error', 'debe digitar una observacion para aprobar!', 3);
				    } else {
				    	// Enviamos a borrar de la tabla
						myUrl = "class/Formulario.php";
						//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
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
										myUrl = "class/PHPMailer/send1.php?"+$('#id_prehsol').val();
										//myUrl = location.protocol + "//" + location.host + "/sics/class/PHPMailer/send1.php?"+$('#id_prehsol').val();
										window.location.href = myUrl;
										
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
			prod_codigo : {
				required : true
			}
		},
		submitHandler: function(form) {
			var isVisible = $('#btnAutoriza').is(':visible');
			var isHidden = $('#btnAutoriza').is(':hidden');
			var isExist = $('#btnAutoriza').length;		
			var campos=xajax.getFormValues("frmAddItem");
			campos['ci_numero'] = GetURLParameter('ps');
			campos['id_empresa'] = GetURLParameter('es');
			campos['id_cc'] = GetURLParameter('cs');			
			form.submit();
			$("#divAddItem").modal('hide');
			if(isExist >= 1 && isHidden && isAutoriza == 1) {
				$('#btnAutoriza').show('slow');
			}
			if(isExist <= 0 && isAutoriza == 1) {
				$('#botonera').append('<a id="btnAutoriza" name="btnAutoriza" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-ok"></i> Autorizar</a>').show('slow');
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
		campos['ci_numero'] = GetURLParameter('ps');
		campos['id_empresa'] = GetURLParameter('es');
		campos['id_cc'] = GetURLParameter('cs');
		campos['id_ci_det'] = atras.attr("id");
		campos['tabla'] = 'ci_det';
		campos['accion'] = 'delete';
		// Enviamos a borra de la tabla
		myUrl = "class/Formulario.php";
		//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
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
		campos['ci_numero'] = GetURLParameter('ps');
		campos['id_empresa'] = GetURLParameter('es');
		campos['id_cc'] = GetURLParameter('cs');
		// Enviamos a guardar
		myUrl = "class/Formulario.php";
		//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
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

	 /*$('#frmAuto').submit(function(e){
			$('body').removeClass('loaded');
		    return;
		    e.preventDefault();
		});*/
/*	 $('#btnImprimir').live('click', function(){
			var $btn = $(this);
			var $url = $btn.attr('href');
	 });*/
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
    			<a class="btn btn-default" href="?c=menu&a=<?php echo (isset($_GET['return']) ? $_GET['return'] : 'index')?>">
					<i class="glyphicon glyphicon-arrow-left"></i>
					Regresar
				</a> &nbsp; &nbsp; &nbsp;
    			<h3>SOLICITUD DE CONSUMO INTERNO</h3><h3 class="pull-right">Formulario # <?php echo $_GET['ps']; ?></h3>
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
        				<strong>Fecha  :</strong><br>
    					<?php
    					setlocale(LC_TIME, "");
    					setlocale(LC_TIME, "es_ES");
    					echo iconv('ISO-8859-1', 'UTF-8', strftime('%A %d de %B, %Y %I:%M:%S %p', strtotime($infohsol[0]['ci_enc_fecha'] . ' ' . $infohsol[0]['ci_enc_hora'])));
    					?>
    				</address>
    			</div>
    		</div>
    		<div class="row">
    		<?php if ($infohsol[0]['ci_estado'] == 0) { ?>
    			<div class="col-md-12">
    				
						<a id="addItem" name="addItem" class="btn btn-sm btn-success" rel="tooltip" title="agregar item a solicitud">
							<i class="glyphicon glyphicon-plus"></i>AGREGAR ITEM
						</a>
    			</div>
    			<?php } ?>
    			<?php if ($infohsol[0]['ci_estado'] == 1 && count($detas) > 0 ) { ?>
				<div class="col-md-12">
					<span> <b>La solicitud se ha enviado con exito por favor </b></span>
					<a id="btnImprimir" name="btnImprimir" href="view/ci/PDF.php?id=<?php echo $infohsol[0]['id_ci']; ?>" class="btn btn-primary">
						<i class="glyphicon glyphicon-print"></i> IMPRIMIR
					</a>
					<a id="btnImprimir" name="btnImprimir" href="?c=ci&a=inicio&id=12" class="btn btn-success">
						<i class="glyphicon glyphicon-home"></i> IR AL INICIO
					</a>
				</div>
			<?php } ?>
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
                                	<td class="text-center"><b>Cantidad</b></td>
        							<td><b>Codigo</b></td>
        							<td><b>Descripcion</b></td>
        							<td class="text-center"><i class="glyphicon glyphicon-cog"></i></td>
                                </tr>
    						</thead>
    						<tbody>
    							<?php 
    								foreach($detas as $deta) {
    								//	print_r($deta);
    							?>
    							<tr id="<?php echo $deta[0]; ?>">
    								<td class="text-center"><span id="ok_auth"></span><?php echo $deta['ci_det_cantidad'];?></td>
    								<td class="text-left"><?php echo $deta['prod_codigo'];?></td>
    								<td class="text-left"><?php echo $deta['prod_descripcion'];?></td>
    								<td class="text-center">
    									<?php if ($infohsol[0]['ci_estado'] < 1) { ?>
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
    		<?php if ($infohsol[0]['ci_estado'] == 0 ) { ?>
				<form class="form form-horizontal" id="frmAuto" name="frmAuto" enctype="multipart/form-data" method="post" action="?c=ci&a=auto&id=12&ps=<?php echo $infohsol[0]['id_ci']; ?>&cs=<?php echo $infohsol[0]['id_cc']; ?>&es=<?php echo $infohsol[0]['id_empresa']; ?>">
					<div class="form-group">
						<label class="col-sm-3 control-label" for="observa_sol">Concepto del Gasto</label>
						<div class="col-sm-9">
							<textarea max_chars="250" rows="5" width="100%" style="width: 100%;" id="observa_sol" name="observa_sol" required></textarea>
							<span id="character_count" class="help-block"></span>
						</div>
					</div>
					<div class="col-sm-offset-3 col-sm-9">
						<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
						<input type="hidden" name="id_ci" id="id_ci" value="<?php echo $infohsol[0]['id_ci']; ?>" />
						<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
						<input type="hidden" name="ci_det_usuario" id="ci_det_usuario" value="<?php echo $_SESSION['u']; ?>" />
						<input type="hidden" name="ci_numero" id="ci_numero" value="<?php echo $_GET['ps']; ?>" />
						<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $_GET['es']; ?>" />
						<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $_GET['cs']; ?>" />
						<button type="submit" id="btnEnviaAuth" name="btnEnviaAuth" class="btn btn-lg btn-primary">
							<i class="glyphicon glyphicon-send"></i>&nbsp;ENVIAR SOLICITUD DE CONSUMO INTERNO
						</button>
					</div>
				</form>
			<?php } ?>
			<?php if ($infohsol[0]['ci_estado'] > 0 ) { ?>
				<p><b>Concepto del gasto : </b><?php echo $infohsol[0]['ci_observacion']; ?></p>
			<?php } ?>
    	</div>
    </div>
</div>
<form class="form-horizontal" role="form" name="frmAddForm" id="frmAddForm" method="post" action="">
	<input type="hidden" name="tabla" id="tabla" value="ci_det" />
	<input type="hidden" name="accion" id="accion" value="save" />
	<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
	<input type="hidden" name="id_ci" id="id_ci" value="<?php echo $infohsol[0]['id_ci']; ?>" />
	<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $infohsol[0]['id_empresa']; ?>" />
	<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
	<input type="hidden" name="ci_det_usuario" id="ci_det_usuario" value="<?php echo $_SESSION['u']; ?>" />
</form>

<input type="hidden" value="<?php echo $infohsol[0]['id_ci']; ?>" id="id_ci" name="id_ci">
<div class="modal fade" role="dialog" id="divAddItem" name="divAddItem">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="frmAddItem" name="frmAddItem" enctype="multipart/form-data" method="post" action="?<?php echo $_SERVER['QUERY_STRING']; ?>" class="form-horizontal" role="form">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 id="myModalLabel">Adicionar Codigo</h3>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label col-md-3" for="prod_codigo">Codigo</label>
						<div class="col-md-6">
							<select class="form-control input-sm" id="prod_codigo" name="prod_codigo" required>
								<option value="">-- Seleccione Codigo --</option>
								<?php foreach ($tipsp as $tip) { ?>
								<option value="<?php echo $tip['id_prod'].'~'.$tip['prod_codigo'].'~'.$tip['prod_descripcion']; ?>"><?php echo '(' . $tip['prod_codigo'] . ') -' . $tip['prod_descripcion']; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 " for="di_det_cantidad">Cantidad</label>
						<div class="col-md-6">
							<input class="form-control input-sm" type="text" id="ci_det_cantidad" name="ci_det_cantidad" required>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="tabla" id="tabla" value="ci_det" />
					<input type="hidden" name="accion" id="accion" value="add" />
					<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
					<input type="hidden" name="id_ci" id="id_ci" value="<?php echo $infohsol[0]['id_ci']; ?>" />
					<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $infohsol[0]['id_empresa']; ?>" />
					<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohsol[0]['id_cc']; ?>" />
					<input type="hidden" name="ci_det_usuario" id="ci_det_usuario" value="<?php echo $_SESSION['u']; ?>" />
					<input type="hidden" name="ci_numero" id="ci_numero" value="<?php echo $_GET['ps']; ?>" />
					<button class="btn" data-dismiss="modal" aria-hidden="true"><span class="glyphicon glyphicon-ban-circle"></span> Cancelar</button>
					<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok-circle"></span> Agregar item</button>
					<input type="reset" style="display: none;">
				</div>
			</form>
		</div>
	</div>
</div>
<div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
</div>
<div class="alert alert-success" role="alert">La solicitud ha sido aprobada.</div>
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