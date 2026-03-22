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
		var $btn2 = $('#addItem');
		jConfirm('esta seguro de autorizar?', 'autorizar solicitud', function(answer){
			if(answer){
				var campos = new Object();
				campos['prehsol_numero'] = GetURLParameter('ps');
				campos['empresa'] = GetURLParameter('es');
				campos['centrocosto'] = GetURLParameter('cs');
				campos['id_prehsol'] = $('#id_prehsol').val();
				campos['tabla'] = 'prehsol';
				campos['accion'] = 'autoriza';
				// Enviamos a borra de la tabla
				myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
				$.ajax({
					type: 'POST',
					url: myUrl,
					data: {
						form: campos
					},
					beforeSend: function(){
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
							} else {
								$('strong').text('AUTORIZADO');
								$('#tablaPresol > tbody tr').each(function(i){
									$(this).find('td:last').html('').delay(i*100).queued('prepend', '<i class="glyphicon glyphicon-ok"></i>');
									var texto = $(this).find('td:first').text();
									$(this).find('td:first').html('<b>'+texto+'</b>');
								});
								$('.span5').html('<i class="glyphicon glyphicon-close"></i>NO PUEDE ADICONAR MAS ITEMS<i class="glyphicon glyphicon-close"></i>');
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
					}
				});
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
	// habilitamos el wysihtml5
	$('#predsol_descripcion').wysihtml5();
	/*
	 * Adicionar un item
	 */
	$("#frmAddItem").validate({
		rules:{
			predsol_cantidad : {
				required: true,
				number: true
			},
			predsol_unidad : {
				required: true
			},
			predsol_descripcion : {
				required : true,
				maxlength : 255
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
			if(descripcion == '') {
				jAlert('digite descripcion',"alerta");
		    } else {
				revisa(campos);
				form.reset();
				$('#predsol_descripcion').val('');
				var frame = $('iframe').get(0);
				var frameDoc = frame.contentDocument || frame.contentWindow.document;
				frameDoc.getElementsByTagName('body')[0].innerHTML = "";
				$('body', frameDoc).html("");
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
});
</script>
<div class="well col-md-12">
	<div class="row">
		<div class="col-md-6">
			<address>
				<!-- <a href="?c=solc&a=inicio&id=5"><img alt="" src="images/back-black.png"></a> -->
				<strong><?php echo $infohsol[0]['emp_nombre'];?></strong>
				<br>
				<?php echo $infohsol[0]['cc_descripcion'];?>
				<br>
				<?php if ($infohsol[0]['prehsol_estado'] == 1) { ?>
					<a id="addItem" name="addItem" class="btn btn-sm btn-primary" rel="tooltip" title="adicionar item a solicitud">
						<i class="glyphicon glyphicon-plus"></i> Adicionar item
					</a>
				<?php } else { ?>
					<span class="text-warning pull-right"><?php echo $conf->getEstado($infohsol[0]['prehsol_estado']); ?></span>
				<?php } ?>
				<div id="botonera" class="btn-group">
					<?php if ($infohsol[0]['prehsol_estado'] == 1 && count($detas) > 0 && $permisos[0]['acc_aut'] == '1') { ?>
						<a id="btnAutoriza" name="btnAutoriza" class="btn btn-sm btn-primary">
						<i class="glyphicon glyphicon-envelope"></i> Autorizar</a>
					<?php } ?>
				</div>
			</address>
		</div>
		<div class="col-md-6 text-right">
			<p><em>Fecha: <?php echo $infohsol[0]['prehsol_fecha']; ?></em></p>
			<p><em>Numero Solicitud #: <?php echo $_GET['ps']; ?></em></p>
		</div>
	</div>
	<div class="row">
		<div class="text-center">
			<h1 class="text-blue">Solicitud de Compra</h1>
		</div>
		<table id="tablaPresol" class="table table-hover">
			<thead>
				<tr>
					<th>Producto</th>
					<th>Unidad</th>
					<th>Cantidad</th>
					<th><?php if($infohsol[0]['prehsol_estado'] == 1) echo 'elim.'; else echo ''; ?></th>
				</tr>
			</thead>
			<tbody>
			<?php $cantidades = 0; ?>
				<?php foreach ($detas as $deta) { ?>
					<tr id="<?php echo $deta['id_predsol']; ?>">
						<td class="col-md-9"><?php echo $deta['predsol_descripcion']; ?></td>
						<td class="col-md-1" style="text-align: center"><?php echo $deta['predsol_unidad']; ?></td>
						<td class="col-md-1" style="text-align: center"><?php echo $deta['predsol_cantidad']; ?></td>
						<td class="col-md-1 text-center">
							<?php if ($infohsol[0]['prehsol_estado'] == 1) { ?>
								<button id="delItem" name="delItem" class="close">&times;</button>
							<?php } ?>
							<?php if ($infohsol[0]['prehsol_estado'] == 2) { ?>
								<i class="icon-ok"></i>
							<?php } ?>
						</td>
					</tr>
					<?php $cantidades++; ?>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="2" class="text-right">
						<p><strong>Total de items: </strong></p>
					</th>
					<th class="text-center">
						<p><strong><?php echo $cantidades; ?></strong></p>
					</th>
					<th></th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

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
						<div class="col-md-9">
							<textarea rows="12" class="form-control input-xxlarge" id="predsol_descripcion" name="predsol_descripcion">
							</textarea>
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