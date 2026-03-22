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
				myUrl = "class/Formulario.php";
				//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
				$.ajax({
					type: 'POST',
					url: myUrl,
					data: {
						form: campos
					},
					beforeSend: function(){
						$.pnotify({
							title: 'enviando solicitud, por favor espere...',
							text: 'la autorizacion de la requisicion esta en proceso.',
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
					},
					success : function(data){
						if(!$.isNumeric(data)){
							$.pnotify({
              					title: "Error",
              					text: data,
              					icon: "glyphicon glyphicon-ban-circle",
              					type: "error",
              					hide: true
          					});
						} else {
							if(data == 1){
								$.pnotify({
	              					title: "Error",
	              					text: data,
	              					icon: "glyphicon glyphicon-ban-circle",
	              					type: "error",
	              					hide: true
	          					});
							} else {
								$btn.remove();
								$('strong').text('AUTORIZADO');
								$('#tablaPresol > tbody tr').each(function(i){
									$(this).find('td:last').html('').delay(i*100).queued('prepend', '<i class="glyphicon glyphicon-ok"></i>');
									var texto = $(this).find('td:first').text();
									$(this).find('td:first').html('<b>'+texto+'</b>');
								});
								$('.col-md-5').html('<i class="glyphicon glyphicon-close"></i>NO PUEDE ADICONAR MAS ITEMS<i class="glyphicon glyphicon-close"></i>');
								$.pnotify({
									title: 'autorizada',
									text: 'requisicion autorizada con exito.',
									type: 'success',
									icon: 'glyphicon glyphicon-ok',
									hide: true,
									addclass: "stack-bar-bottom",
									cornerclass: "",
							        width: "100%",
							        stack: stack_bar_bottom
								});
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
	 * Edicion inline
	*/
	$('#username').live('click', function(){
		var $tr = $(this).closest('tr');
		//modify buttons style
		$.fn.editableform.buttons =
		  '<button type="submit" class="btn btn-success editable-submit btn-sm"><i class="glyphicon glyphicon-ok"></i></button>' +
		 '<button type="button" class="btn editable-cancel btn-sm"><i class="glyphicon glyphicon-remove"></i></button>';
		$(this).editable({
			validate: function(value) {
				if($.trim(value) == '')	return 'digite cantidad';
				if(!$.isNumeric(value))	return 'digite numero valido';
			},
			params: function(params) {
				params.prehreq_numero = GetURLParameter('ps');
				params.id_empresa = GetURLParameter('es');
				params.id_cc = GetURLParameter('cs');
				return params;
			},
			ajaxOptions: {
				dataType: 'json'
			},
		    success: function(response, newValue) {
			    var valor = parseFloat($tr.find('td').eq(3).text());
			    var total = (newValue*valor);
			    $tr.find('td').eq(4).text(total.toFixed(2));
		        if(!response) {
		            return "Unknown error!"+response;
		        }
		        if(response.success === false) {
		            return response.msg;
		        }
		    }
		});
		$(this).editable('show');
	});
	// Toltip en los enlaces
	/*$('a').tooltip();*/
	/*$('a').popover();*/

	$('.panel-body > p').draggable({
	    cursor: 'move',          // sets the cursor apperance
	    //revert: 'valid',
	    helper:'clone',
	    stack: "tbody",
	    stackOption: "both",
	    appendTo: "body",
		helper: function( event ) {
			return $( "<div class='modal-header ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix'>"+$(this).text()+"</div>" );
		}
	});
	// sets droppable
	$('tbody').droppable({
		tolerance:"touch",
		drop: function(event, ui) {
			// Estado de los botones
			var isVisible = $('#btnAutoriza').is(':visible');
			var isHidden = $('#btnAutoriza').is(':hidden');
			var isExist = $('#btnAutoriza').length;
			var isAutoriza = <?php echo $permisos[0]['acc_aut']; ?>;
			var padre = ui.draggable.parent().attr('id');
			var campos=xajax.getFormValues("frmAddItem");
			campos['prehreq_numero'] = GetURLParameter('ps');
			campos['id_empresa'] = GetURLParameter('es');
			campos['id_cc'] = GetURLParameter('cs');
			ui.draggable.fadeIn('slow');
            ui.draggable.css("font-weight","bold");
            ui.draggable.css("font-weight","normal");
		    ui.draggable.effect('bounce');
		    // Proveedor y Precio en p > 'id'
			var $texto = ui.draggable.attr('id');
			var $propre = $texto.split(",");
			// Codigo y Descripcion
			$texto = ui.draggable.text();
		    var $textos = $texto.split("|");
		    campos['predreq_codigo'] = $.trim($textos[0]);
            campos['predreq_descripcion'] = $.trim($textos[1]);
            var $cantidad = 0;
            var $append = 0;
            var linea = '00';
    		var slinea = '00';
    		var titgas = '00';
    		var detgas = '00';
    		if(padre.length >= 4) {
    			linea = padre.substr(0,2);
    			slinea = padre.substr(2,2);
    			titgas = padre.substr(4,2);
    			detgas = padre.substr(6,2);
    		}
    		campos['linea'] = linea;
    		campos['slinea'] = slinea;
    		campos['predreq_titgas'] = titgas;
    		campos['predreq_detgas'] = detgas;
            $('#tablaPresol > tbody tr').each(function(){
                $cantidad = $(this).find('td').eq(0).text();
                $id = $(this).attr('id');
                rowFound = $(this);
                // Existe le vamos a sumar uno al actual
                if($.trim($textos[0]) == $.trim($(this).find('td').eq(1).text())){
                    ++$cantidad;
                    var $tds = '<a href="#" id="username" data-type="text" data-pk="'+$id+'" data-url="post/post_req_pre.php" data-original-title="'+$texto+'" data-placement="right">'+$cantidad+'</a>';
                    // 3 Precio
                    var precio = $(this).find('td').eq(3).text();
                    campos['precio_uni'] = precio;
                    // 4 Total
                    var total = (parseFloat(precio)*$cantidad);
                    rowFound.fadeIn('slow');
                    rowFound.css("font-weight","bold");
                    rowFound.css("font-weight","normal");
                    campos['predreq_cantidad'] = $cantidad;
                    campos['accion'] = 'edit';
                    campos['id_predreq'] = $id;
                    // Enviamos a guardar
              		myUrl = "class/Formulario.php";
              		//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
              		$.ajax({
              			type: 'POST',
              			url: myUrl,
              			data: {
              				form: campos
              			},
              			beforeSend: function(){
    						$.pnotify({
    							title: 'enviando solicitud, por favor espere...',
    							text: 'adicionando item '+$textos[0],
    							type: 'info',
    							hide: true,
    							addclass: "stack-bottomright",
    							stack: stack_bottomright
    						});
    					},
              			success : function(data){
              				if(!$.isNumeric(data)){
              					$.pnotify({
                  					title: "Error",
                  					text: data,
                  					icon: "glyphicon glyphicon-ban-circle",
                  					type: "error",
                  					hide: true
              					});
              				} else {
              					if(data == 1){
              						$.pnotify({
                      					title: "Error",
                      					text: data,
                      					icon: "glyphicon glyphicon-ban-circle",
                      					type: "error",
                      					hide: true
                  					});
              					} else {
                  					rowFound.effect('highlight');
                  					var cantidades = 0;
              						// Cantidad de items
              						$("#tablaPresol>tbody tr").each(function(i){
              							cantidades += 1;
              						});
              						// Totales en los pies
              						$("#tablaPresol>tfoot>tr").each(function(i){
              							$(this).find('th').eq(1).text(cantidades);
              						});
              						rowFound.find('td').eq(4).html(total.toFixed(2));
              						rowFound.find('td').eq(0).html($tds);
              					}
              				}
              			},
              			error : function(XMLHttpRequest, textStatus, errorThrown) {
              				$.pnotify({
            					title: 'ha ocurrido un error..',
            					text: 'durante la modificacion ocurrio lo sigueinte :'+textStatus,
            					type: 'error',
            					icon: 'icon-alert-sign',
            					hide: true
            				});
              			}
              		});
              		$append = 1;
                    return false;
                }
            });
            // Adicionar o modificar
            if($append == 0){
            	campos['predreq_cantidad'] = 1;
             	// Enviamos a guardar
          		myUrl = "class/Formulario.php";
          		//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
          		$.ajax({
          			type: 'POST',
          			url: myUrl,
          			data: {
          				form: campos
          			},
          			beforeSend: function(){
						$.pnotify({
							title: 'enviando solicitud, por favor espere...',
							text: 'adicionando item '+$textos[0],
							type: 'info',
							hide: true,
							addclass: "stack-bottomright",
							stack: stack_bottomright
						});
					},
          			success : function(data){
          				if(!$.isNumeric(data)){
          					$.pnotify({
              					title: "Error",
              					text: data,
              					icon: "glyphicon glyphicon-ban-circle",
              					type: "error",
              					hide: true
          					});
          				} else {
          					if(data == 1){
          						$.pnotify({
                  					title: "Error",
                  					text: data,
                  					icon: "glyphicon glyphicon-ban-circle",
                  					type: "error",
                  					hide: true
              					});
          					} else {
       							$('<tr id="'+data+'"><td><a href="#" id="username" data-type="text" data-pk="'+data+'" data-url="post/post_req_pre.php" data-original-title="'+$(this).text()+'" data-placement="right">1</a></td><td>'+$textos[0]+'</td><td>'+$textos[1]+'</td><td>'+$propre[1]+'</td><td>'+$propre[1]+'</td><td><button id="delItem" name="delItem" class="close">&times;</button></td></tr>').appendTo('#tablaPresol > tbody').effect('highlight');
       							var cantidades = 0;
          						// Cantidad de items
          						$("#tablaPresol>tbody tr").each(function(i){
          							cantidades += 1;
          						});
          						// Totales en los pies
          						$("#tablaPresol>tfoot>tr").each(function(i){
          							$(this).find('th').eq(1).text(cantidades);
          						});
								if(isExist >= 1 && isHidden && isAutoriza == 1) {
									$('#btnAutoriza').show('slow');
								}
								if(isExist <= 0 && isAutoriza == 1) {
									$('#botonera').append('<a id="btnAutoriza" name="btnAutoriza" class="btn btn-sm btn-primary">Autorizar</a>').show('slow');
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
        					hide: true
        				});
          			}
          		});
            }
	    }
	  });

	/*
	 * Borrar fila
	 */
	$('#delItem').live('click', function() {
		var $this = $(this);
	    var tableRow = $this.closest('tr');
		 // Mandamos los datos para eliminar
        var campos = new Object();
        campos['prehreq_numero'] = GetURLParameter('ps');
		campos['id_empresa'] = GetURLParameter('es');
		campos['id_cc'] = GetURLParameter('cs');
		campos['id_predreq'] = tableRow.attr("id");
		campos['tabla'] = 'predreq';
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
			beforeSend: function() {
				$.pnotify({
  					text: 'eliminando item....',
  					hide: true
				});
			},
			success : function(data){
				if(!$.isNumeric(data)){
					$.pnotify({
      					title: "Error",
      					text: data,
      					icon: "glyphicon glyphicon-ban-circle",
      					type: "error",
      					hide: true
  					});
				} else {
					if(data == 1){
						$.pnotify({
          					title: "Error",
          					text: data,
          					icon: "glyphicon glyphicon-ban-circle",
          					type: "error",
          					hide: true
      					});
					} else {
						tableRow.find('td').fadeOut('fast', function(){
							// Nos borramos la linea
							tableRow.remove();
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
					text: 'durante la elminacion ocurrio lo sigueinte :'+textStatus,
					type: 'error',
					icon: 'icon-alert-sign',
					hide: true
				});
			}
		});
	    return false; // prevents default behavior
	});
	/*
	 * Adicionar producto a requisicion
	 */
	$("div.panel-body > p").live('click', function () {
		var $this = $(this);
		// Proveedor y Precio en p > 'id'
		var $texto = $this.attr("id");
		var $propre = $texto.split(",");
		// Estado de los botones
		var isVisible = $('#btnAutoriza').is(':visible');
		var isHidden = $('#btnAutoriza').is(':hidden');
		var isExist = $('#btnAutoriza').length;
		var isAutoriza = <?php echo $permisos[0]['acc_aut']; ?>;
		var campos=xajax.getFormValues("frmAddItem");
		campos['prehreq_numero'] = GetURLParameter('ps');
		campos['id_empresa'] = GetURLParameter('es');
		campos['id_cc'] = GetURLParameter('cs');
		var padre = $(this).parent().attr('id');
		var linea = '00';
		var slinea = '00';
		var titgas = '00';
		var detgas = '00';
		if(padre.length >= 4) {
			linea = padre.substr(0,2);
			slinea = padre.substr(2,2);
			titgas = padre.substr(4,2);
			detgas = padre.substr(6,2);
		}
		campos['linea'] = linea;
		campos['slinea'] = slinea;
		campos['predreq_titgas'] = titgas;
		campos['predreq_detgas'] = detgas;
		var transferHelper = $this.clone();
		$(this).effect("transfer",{ clone: transferHelper, to: $("#tablaPresol>thead") }, 400, function(){
        	elementClick = $('#ancla');
            destination = $(elementClick).offset().top-150;
            $("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination}, 1100 );
            var $texto = $(this).text();
            $textos = $texto.split("|");
            campos['predreq_codigo'] = $.trim($textos[0]);
            campos['predreq_descripcion'] = $.trim($textos[1]);
            var $cantidad = 0;
            var $append = 0;
            $('#tablaPresol > tbody tr').each(function(){
                $cantidad = $(this).find('td').eq(0).text();
                $id = $(this).attr('id');
                rowFound = $(this);
                // Existe le vamos a sumar uno al actual
                if($.trim($textos[0]) == $.trim($(this).find('td').eq(1).text())){
                    ++$cantidad;
                    var $tds = '<a href="#" id="username" data-type="text" data-pk="'+$id+'" data-url="post/post_req_pre.php" data-original-title="'+$texto+'" data-placement="right">'+$cantidad+'</a>';
                    // 3 Precio
                    var precio = $(this).find('td').eq(3).text();
                    // 4 Total
                    var total = (parseFloat(precio)*$cantidad);
                    rowFound.fadeIn();
                    rowFound.css("font-weight","bold");
                    rowFound.css("font-weight","normal");
                    campos['predreq_cantidad'] = $cantidad;
                    campos['precio_uni'] = precio;
                    campos['accion'] = 'edit';
                    campos['id_predreq'] = $id;
                    // Enviamos a guardar
              		myUrl = "class/Formulario.php";
              		//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
              		$.ajax({
              			type: 'POST',
              			url: myUrl,
              			data: {
              				form: campos
              			},
              			beforeSend: function(){
    						$.pnotify({
    							title: 'enviando solicitud, por favor espere...',
    							text: 'adicionando item '+$textos[0],
    							type: 'info',
    							hide: true,
    							addclass: "stack-bottomright",
    							stack: stack_bottomright
    						});
    					},
              			success : function(data){
              				if(!$.isNumeric(data)){
              					$.pnotify({
                  					title: "Error",
                  					text: data,
                  					icon: "glyphicon glyphicon-ban-circle",
                  					type: "error",
                  					hide: true
              					});
              				} else {
              					if(data == 1){
              						$.pnotify({
                      					title: "Error",
                      					text: data,
                      					icon: "glyphicon glyphicon-ban-circle",
                      					type: "error",
                      					hide: true
                  					});
              					} else {
              						rowFound.effect('highlight');
                  					var cantidades = 0;
              						// Cantidad de items
              						$("#tablaPresol>tbody tr").each(function(i){
              							cantidades += 1;
              						});
              						// Totales en los pies
              						$("#tablaPresol>tfoot>tr").each(function(i){
              							$(this).find('th').eq(1).text(cantidades);
              						});
              						rowFound.find('td').eq(4).html(total.toFixed(2));
              						rowFound.find('td').eq(0).html($tds);
              					}
              				}
              			},
              			error : function(XMLHttpRequest, textStatus, errorThrown) {
              				$.pnotify({
            					title: 'ha ocurrido un error..',
            					text: 'durante la modificacion ocurrio lo sigueinte :'+textStatus,
            					type: 'error',
            					icon: 'icon-alert-sign',
            					hide: true
            				});
              			}
              		});
              		$append = 1;
                    return false;
                }
            });
            // Adicionar o modificar
            if($append == 0){
            	campos['predreq_cantidad'] = 1;
             	// Enviamos a guardar
          		myUrl = "class/Formulario.php";
          		//myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
          		$.ajax({
          			type: 'POST',
          			url: myUrl,
          			data: {
          				form: campos
          			},
          			beforeSend: function(){
						$.pnotify({
							title: 'enviando solicitud, por favor espere...',
							text: 'adicionando item '+$textos[0],
							type: 'info',
							hide: true,
							addclass: "stack-bottomright",
							stack: stack_bottomright
						});
					},
          			success : function(data){
          				if(!$.isNumeric(data)){
          					$.pnotify({
              					title: "Error",
              					text: data,
              					icon: "glyphicon glyphicon-ban-circle",
              					type: "error",
              					hide: true
          					});
          				} else {
          					if(data == 1){
              					$.pnotify({
                  					title: "Error",
                  					text: data,
                  					icon: "glyphicon glyphicon-ban-circle",
                  					type: "error",
                  					hide: true
              					});
          					} else {
       							$('#tablaPresol > tbody').append('<tr id="'+data+'"><td><a href="#" id="username" data-type="text" data-pk="'+data+'" data-url="post/post_req_pre.php" data-original-title="'+$(this).text()+'" data-placement="right">1</a></td><td>'+$textos[0]+'</td><td>'+$textos[1]+'</td><td>'+$propre[1]+'</td><td>'+$propre[1]+'</td><td><button id="delItem" name="delItem" class="close">&times;</button></td></tr>');
       							var cantidades = 0;
          						// Cantidad de items
          						$("#tablaPresol>tbody tr").each(function(i){
          							cantidades += 1;
          						});
          						// Totales en los pies
          						$("#tablaPresol>tfoot>tr").each(function(i){
          							$(this).find('th').eq(1).text(cantidades);
          						});
								if(isExist >= 1 && isHidden && isAutoriza == 1) {
									$('#btnAutoriza').show('slow');
								}
								if(isExist <= 0 && isAutoriza == 1) {
									$('#botonera').append('<a id="btnAutoriza" name="btnAutoriza" class="btn btn-sm btn-primary">Autorizar</a>').show('slow');
								}
          					}
          				}
          			},
          			error : function(XMLHttpRequest, textStatus, errorThrown) {
          				$.pnotify({
        					title: 'ha ocurrido un error..',
        					text: 'durante la adicion ocurrio lo sigueinte :'+textStatus,
        					type: 'error',
        					icon: 'glyphicon glyphicon-ban-circle',
        					hide: true
        				});
          			}
          		});
            }
		});
	});
	/*
	Envia para autorizacion 
	*/
	/*
	$('#btnDispo').on('click', function(e){
		$('body').removeClass('loaded');
		return;
		e.preventDefault();
	});
	*/
});
</script>
	<h4 class="text-blue">Editar Contenido de Requisicion</h4>
	<table class="table table-condensed">
		<thead>
			<tr>
			  <td bgcolor="#f5f5f5">
			  <!-- ?c=req&a=inicio&id=6 -->
				<a href="<?php echo $_SERVER['HTTP_REFERER']; ?>"><img alt="" src="images/back-black.png"></a>
					pre-requisicion <b><?php echo $_GET['ps']; ?></b>
					<input type="hidden" value="<?php echo $infohreq[0]['id_prehreq']; ?>" id="id_prehreq" name="id_prehreq">
					<?php if ($infohreq[0]['prehreq_estado'] == 0 || $infohreq[0]['prehreq_estado'] == 1) { ?>
						<strong><?php echo $conf->getEstado($infohreq[0]['prehreq_estado']); ?></strong>
					<?php } else { ?>
						<strong><?php echo $conf->getEstado($infohreq[0]['prehreq_estado']); ?></strong>
					<?php } ?>
					<small><?php echo $infohreq[0]['emp_nombre'];?>(<?php echo $infohreq[0]['cc_descripcion'];?>)</small>
					<div id="botonera" class="btn-group">
						<?php if ($infohreq[0]['prehreq_estado'] == 1 && count($detas) > 0 && $permisos[0]['acc_aut'] == '1') { ?>
							<a id="btnAutoriza" name="btnAutoriza" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-envelope"></i> Autorizar</a>
						<?php } ?>
					</div>
			  </td>
			</tr>
		</thead>
	</table>
	<br>
	<?php
	//echo $_SERVER['QUERY_STRING'];
	$str = parse_url($_SERVER['QUERY_STRING']);
	//print_r($str);
	//print_r($permisos);
	?>
<div class="well well-sm">
	<div class="row">
		<div class="col-md-7">
		<a id="ancla" href="#"></a>
			<table id="tablaPresol" class="table table-condensed table-bordered table-striped table-hover">
				<thead>
					<tr>
						<th>cant</th>
						<th>codigo</th>
						<th>descripcion</th>
						<th>prec.uni</th>
						<th>total</th>
						<th><?php if($infohsol[0]['prehsol_estado'] == 1) echo 'elim.'; else echo ''; ?></th>
					</tr>
				</thead>
				<tbody>
				<?php $cantidades = 0; ?>
				<?php foreach ($detas as $deta) { ?>
					<tr id="<?php echo $deta['id_predreq']; ?>">
						<td>
							<?php if ($infohreq[0]['prehreq_estado'] == 0 || $infohreq[0]['prehreq_estado'] == 1) { ?>
								<a href="#" id="username" data-type="text" data-pk="<?php echo $deta['id_predreq']; ?>" data-url="post/post_req_pre.php" data-original-title="<?php echo $deta['prod_codigo']; ?>" data-placement="right">
									<?php echo $deta['predreq_cantidad']; ?>
								</a>
							<?php } ?>
							<?php if ($infohreq[0]['prehreq_estado'] == 2) { ?>
								<b><?php echo $deta['predreq_cantidad']; ?></b>
							<?php } ?>
						</td>
						<td><?php echo $deta['prod_codigo']; ?></td>
						<td><?php echo $deta['predreq_descripcion']; ?></td>
						<td><?php echo $deta['predreq_prec_uni']; ?></td>
						<td><?php echo $deta['predreq_total']; ?></td>
						<td>
							<?php if ($infohreq[0]['prehreq_estado'] == 0 || $infohreq[0]['prehreq_estado'] == 1) { ?>
							<button id="delItem" name="delItem" class="close">&times;</button>
							<?php } ?>
							<?php if ($infohreq[0]['prehreq_estado'] == 2) { ?>
							<i class="glyphicon glyph	"></i>
							<?php } ?>
						</td>
					</tr>
					<?php ++$cantidades; ?>
				<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="5">total de items</th>
						<th><?php echo $cantidades; ?></th>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="col-md-5">
			<?php if ($infohreq[0]['prehreq_estado'] == 0 || $infohreq[0]['prehreq_estado'] == 1) { ?>
			<div class="panel-group" id="listado">
			<?php $li = 0; $sli = 0; $x = 0; ?>
			<?php foreach ($prods as $prod) { ?>
				<?php if($li <> $prod['sl_linea'] || $sli <> $prod['sl_sublinea']) { ?>
				<?php if($x == 1) { ?>
 					</div>
 					</div>
					</div>
 				<?php }	?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<a class="panel-title accordion-toggle" data-toggle="collapse" data-parent="#listado" href="<?php echo '#',$prod['sl_linea'],$prod['sl_sublinea'], $prod['gas_tit_codigo'], $prod['gas_det_codigo']; ?>">
							<?php echo '(',$prod['sl_linea'], $prod['sl_sublinea'],') ',$prod['sl_descripcion']; ?>
						</a>
					</div>
					<div id="<?php echo $prod['sl_linea'], $prod['sl_sublinea'], $prod['gas_tit_codigo'], $prod['gas_det_codigo']; ?>" class="panel-collapse collapse">
						<div id="<?php echo $prod['sl_linea'], $prod['sl_sublinea'], $prod['gas_tit_codigo'], $prod['gas_det_codigo']; ?>" class="panel-body">
							<p class="btn btn-labeled" id="<?php echo $prod['proveedor'],',',$prod['precio']; ?>"><?php echo $prod['prod_codigo'],' | ',$prod['prod_descripcion']; ?></p>
				<?php } else { ?>
							<p class="btn btn-labeled" id="<?php echo $prod['proveedor'],',',$prod['precio']; ?>"><?php echo $prod['prod_codigo'],' | ',$prod['prod_descripcion']; ?></p>
				<?php } ?>
				<?php $x = 1; $li = $prod['sl_linea']; $sli = $prod['sl_sublinea']; ?>
			<?php } ?>
			</div>
			</div>
			<?php } ?>

		</div>
	</div>
</div>
<?php if ($infohreq[0]['prehreq_estado'] == 0 || $infohreq[0]['prehreq_estado'] == 1) { ?>
</div>
</div>
<?php } ?>
<form id="frmAddItem" name="frmAddItem">
	<input type="hidden" name="tabla" id="tabla" value="predreq" />
	<input type="hidden" name="accion" id="accion" value="add" />
	<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['i']; ?>" />
	<input type="hidden" name="id_prehreq" id="id_prehreq" value="<?php echo $infohreq[0]['id_prehreq']; ?>" />
	<input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo $infohreq[0]['id_empresa']; ?>" />
	<input type="hidden" name="id_cc" id="id_cc" value="<?php echo $infohreq[0]['id_cc']; ?>" />
	<input type="hidden" name="predreq_usuario" id="predreq_usuario" value="<?php echo $_SESSION['u']; ?>" />
</form>
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