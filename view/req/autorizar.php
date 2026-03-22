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
		var $btn2 = $('#btnNegar');
		jConfirm('esta seguro de autorizar?', 'autorizar requisicion', function(answer){
			if(answer){
				var campos = new Object();
				campos['prehreq_numero'] = GetURLParameter('ps');
				campos['empresa'] = GetURLParameter('es');
				campos['centrocosto'] = GetURLParameter('cs');
				campos['id_prehreq'] = $('#id_prehreq').val();
				campos['tabla'] = 'prehreq';
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
							text: 'la autorizacion de la requisicion esta en proceso, por favor espere...',
							type: 'info',
							icon: 'glyphicon glyphicon-wrench',
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
							jAlert(data,'la requisicion ha sufrido cambios');
						} else {
							if(data == 1){
								jAlert(data,"ha ocurrido un error");
							} else {
								$btn.remove();
								$btn2.remove();
								$('strong').text('AUTORIZADO');
								$('#tablaPresol > tbody tr').each(function(i){
									$(this).find('td:last').delay(i*100).queued('prepend', '<i class="icon-ok"></i>');
								});
								jAlert("ha sido autorizada con exito!","exito");
							}
						}
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
						$.pnotify({
							title: 'ha ocurrido un error..',
							text: 'ocurrio lo siguiente : '+textStatus,
							type: 'error',
							icon: 'icon-exclamation-sign',
							hide: true,
							addclass: "stack-bar-bottom",
							cornerclass: "",
					        width: "100%",
					        stack: stack_bar_bottom
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
					text: 'la autorizacion de la requisicion ha sido cancelada',
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
	 * No autorizar la requisicion
	 */
	$('#btnNegar').live('click', function(){
		var $btn = $(this);
		var $btn2 = $('#btnAutoriza');
		jConfirm('esta seguro?, la requisicion no podra utilizarse mas.', 'negar requisicion', function(answer){
			if(answer){
				var campos = new Object();
				campos['id_prehreq'] = GetURLParameter('ps');
				campos['empresa'] = GetURLParameter('es');
				campos['centrocosto'] = GetURLParameter('cs');
				campos['id_prehreq'] = $('#id_prehreq').val();
				campos['tabla'] = 'prehreq';
				campos['accion'] = 'negar';
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
							jAlert(data,'la requisicion ha sufrido cambios');
						} else {
							if(data == 1){
								jAlert(data,"ha ocurrido un error");
							} else {
								$btn.remove();
								$btn2.remove();
								$('strong').text('NEGADO');
								$('#tablaPresol > tbody tr').each(function(i){
									$(this).find('td:last').delay(i*100).queued('prepend', '<i class="icon-remove"></i>');
								});
								jAlert("ha sido rechazado con exito!","exito");
							}
						}
					},
					error : function(XMLHttpRequest, textStatus, errorThrown) {
					    showNotification({
							type : "error",
					        message: "Ha ocurrido un error durante la negacion. "+errorThrown,
					        autoClose: true,
					        duration: 2
						});
					}
				});
			    return false; // prevents default behavior
			} else {
				jAlert('ok, pienselo un poco...','cancelado');
			}
		});
	});
});
</script>
<fieldset>
	<legend>
		<a href="?c=req&a=autoriza&id=6&es=<?php echo $_GET['es']; ?>"><img alt="" src="images/back-black.png"></a>
		pre-requisicion # <b><?php echo $_GET['ps']; ?></b>
		<input type="hidden" value="<?php echo $infohsol[0]['id_prehreq']; ?>" id="id_prehreq" name="id_prehreq">
		<strong><?php echo $conf->getEstado($infohsol[0]['prehreq_estado']); ?></strong>
		<?php if ($infohsol[0]['prehreq_estado'] == 1 && count($detas) > 0) { ?>
			<a id="btnAutoriza" name="btnAutoriza" class="btn btn-sm btn-primary">
			Autorizar
			</a>
			<!--
			<a id="btnNegar" name="btnNegar" class="btn btn-danger">
			no autorizar
			</a> -->
		<?php } ?>
		<small style="padding-left: 25px;"><?php echo $infohsol[0]['emp_nombre'];?> ( <?php echo $infohsol[0]['cc_descripcion'];?> ) </small>
	</legend>
	<?php if(count($detas) <=0 ) { ?>
		<div class="alert alert-warning">la requisicion esta vacia</div>
	<?php } ?>
	<table id="tablaPresol" name="tablaPresol" class="table table-condensed">
		<thead>
			<tr>
				<th>Cantidad</th>
				<th>Descripcion</th>
			</tr>
		</thead>
		<tbody>
		<?php $cantidades = 0; ?>
		<?php foreach ($detas as $deta) { ?>
			<tr id="<?php echo $deta['id_predreq']; ?>">
				<td><?php echo $deta['predreq_cantidad']; ?></td>
				<td><?php echo $deta['predreq_descripcion']; ?></td>
			</tr>
			<?php $cantidades++; ?>
		<?php }?>
		</tbody>
		<tfoot>
			<tr>
				<th>Items</th>
				<th><?php echo $cantidades; ?></th>
			</tr>
		</tfoot>
	</table>
</fieldset>
<!-- CARGANDO -->
<div id="waiting" style="display: none;">
	<fieldset>
		<legend>procesando peticion, espere por favor...</legend>
		<img src="css/redmond/images/ajax-loader.gif" />
	</fieldset>
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