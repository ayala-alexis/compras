<style>
ul.dynatree-container li {
	font-size: 8pt;
}
.row {
	background-color: white;
}
</style>
<script type="text/javascript">
  // --- Implement Cut/Copy/Paste --------------------------------------------
  var clipboardNode = null;
  var pasteMode = null;

  function copyPaste(action, node) {
    switch( action ) {
    case "cut":
    case "copy":
      clipboardNode = node;
      pasteMode = action;
      break;
    case "paste":
      if( !clipboardNode ) {
        alert("Clipoard is empty.");
        break;
      }
      if( pasteMode == "cut" ) {
        // Cut mode: check for recursion and remove source
        var isRecursive = false;
        var cb = clipboardNode.toDict(true, function(dict){
          // If one of the source nodes is the target, we must not move
          if( dict.key == node.data.key )
            isRecursive = true;
        });
        if( isRecursive ) {
          alert("Cannot move a node to a sub node.");
          return;
        }
        node.addChild(cb);
        clipboardNode.remove();
      } else {
        // Copy mode: prevent duplicate keys:
        var cb = clipboardNode.toDict(true, function(dict){
          dict.title = "Copy of " + dict.title;
          delete dict.key; // Remove key, so a new one will be created
        });
        node.addChild(cb);
      }
      clipboardNode = pasteMode = null;
      break;
    default:
      alert("Unhandled clipboard action '" + action + "'");
    }
  };

  // --- Contextmenu helper --------------------------------------------------
  function bindContextMenu(span) {
    // Add context menu to this node:
    $(span).contextMenu({menu: "myMenu"}, function(action, el, pos) {
      // The event was bound to the <span> tag, but the node object
      // is stored in the parent <li> tag
      var node = $.ui.dynatree.getNode(el);
      switch( action ) {
      case "cut":
      case "copy":
      case "paste":
        copyPaste(action, node);
        break;
      case "edit":
    	  	if( !node.data.isFolder ) {
    	  		$('#btnUserDelete').addClass('disabled');
				$('#btnUserDelete').attr('disabled', 'disabled');
				$('#btnUserAdd').addClass('disabled');
				$('#btnUserAdd').attr('disabled', 'disabled');
				$('#btnUserSave').addClass('disabled');
				$('#btnUserSave').attr('disabled', 'disabled');
				$('#btnUserPass').addClass('disabled');
				$('#btnUserPass').attr('disabled', 'disabled');
				$('#btnUserEmail').addClass('disabled');
				$('#btnUserEmail').attr('disabled', 'disabled');
                $('#btnUserRol').addClass('disabled');
				$('#btnUserRol').attr('disabled', 'disabled');
    	  		$.pnotify({
					title: 'espere..',
					text: 'accesando usuario, por favor espere...',
					type: 'success',
					icon: 'icon-edit',
					hide: false
				});
				$("#spanSelect").html('<i class="glyphicon glyphicon-eye-open"></i>'+node.data.title);
				Url = location.protocol + "//" + location.host + '/sics/admin.php?c=usua&a=seguridad&id=4&idprofile=' + node.data.key + '&profile=' + node.data.title + "&s=active&tipo=" + node.data.tipo;
				window.location = Url;
			} else {
				$("#spanSelect").html('<i class="glyphicon glyphicon-play"></i>');
			}
			break;
      case "delete":
    	  if( !node.data.isFolder ) {
				$("#spanSelect").html('<i class="glyphicon glyphicon-eye-open"></i>'+node.data.title);
				$('#btnUserDelete').addClass('disabled');
				$('#btnUserDelete').attr('disabled', 'disabled');
				$('#btnUserAdd').addClass('disabled');
				$('#btnUserAdd').attr('disabled', 'disabled');
				$('#btnUserSave').addClass('disabled');
				$('#btnUserSave').attr('disabled', 'disabled');
				$('#btnUserPass').addClass('disabled');
				$('#btnUserPass').attr('disabled', 'disabled');
				$('#btnUserEmail').addClass('disabled');
				$('#btnUserEmail').attr('disabled', 'disabled');
				var datos = new Object();
				datos['profile'] = node.data.title;
		        datos['idprofile'] = node.data.key;
		        datos['tipo'] = node.data.tipo;
		        datos['tabla'] = 'usuario';
		        datos['accion'] = 'delete';
		        myUrl = location.protocol + "//" + location.host + '/sics/class/Formulario.php';
				$.ajax({
					type: 'POST',
					url: myUrl,
		            data: {
		                form: datos
					},
					beforeSend: function(){
						$.pnotify({
							title: 'espere..',
							text: 'eliminando usuario, por favor espere...',
							type: 'success',
							icon: 'icon-delete',
							hide: false
						});
						$('#btnUserDelete').after('<img src="img/FhHRx.gif"></img>');
					},
					success: function(aData){
						CancelUrl = location.protocol + "//" + location.host + '/sics/admin.php?c=usua&a=seguridad&id=4';
						window.location = CancelUrl;
					},
					error: function(XMLHttpRequest, textStatus, errorThrown){
						alert(textStatus);
					}
				}).done(function(response, textStatus, jqXHR){
					$('#btnUserDelete').nextAll('img').remove();
				});
			} else {
				$("#spanSelect").html('<i class="glyphicon glyphicon-play"></i>');
			}
          break;
	  case "quit":
		  CancelUrl = location.protocol + "//" + location.host + '/sics/admin.php?c=usua&a=seguridad&id=4';
		  window.location = CancelUrl;
	      break;
      default:
        alert("Todo: appply action '" + action + "' to node " + node);
      }
    });
  };
</script>
<script type="text/javascript">
$(document).ready(function(){
	/*
	 * Agregamos nuevo atributo para guardar ahi
	 * el hijo del modulo
	 */
	$.metadata.setType("attr", "hijo");
	$.metadata.setType("attr", "mod-url");
	$.metadata.setType("attr", "id-mod");
	$.metadata.setType("attr", "id-mod-acc");

	// DYNATREE
	alto_doc = ($(document).height()-175);
	alto_col_md4 = (alto_doc / 2);
	ancho = $('.col-md-4').width();
	$('.col-md-4.table-bordered').css({
		'height' : alto_col_md4
	});
	// Roles
	$("#roles_tree").dynatree({
		imagePath: "css/skin-custom/",
		onCreate: function(node, span){
	        bindContextMenu(span);
	    },
		onClick: function(node, event) {
	        // Close menu on click
	        if( $(".contextMenu:visible").length > 0 ){
	          $(".contextMenu").hide();
	        }
	    },
		onActivate: function(node) {
			if( !node.data.isFolder ) {
				$.pnotify({
					title: 'espere..',
					text: 'accesando usuario, por favor espere...',
					type: 'success',
					icon: 'icon-edit',
					hide: false
				});
				$("#spanSelect").html('<i class="glyphicon glyphicon-eye-open"></i>'+node.data.title);
				Url = location.protocol + "//" + location.host + '/sics/admin.php?c=usua&a=seguridad&id=4&idprofile=' + node.data.key + '&profile=' + node.data.title + "&s=active&tipo=" + node.data.tipo;
				window.location = Url;
			} else {
				$("#spanSelect").html('<i class="glyphicon glyphicon-play"></i>');
			}
		},
		onDeactivate: function(node) {
			if( !node.data.isFolder ) {
				$("#spanSelect").html('<i class="glyphicon glyphicon-play"></i>');
				Url = location.protocol + "//" + location.host + '/sics/admin.php?c=usua&a=seguridad&id=4';
				window.location = Url;
			}
	    },
		onKeydown: function(node, event) {
			// Eat keyboard events, when a menu is open
	        if( $(".contextMenu:visible").length > 0 )
	          return false;

	        switch( event.which ) {

	        // Open context menu on [Space] key (simulate right click)
	        case 32: // [Space]
	          $(node.span).trigger("mousedown", {
	            preventDefault: true,
	            button: 2
	            })
	          .trigger("mouseup", {
	            preventDefault: true,
	            pageX: node.span.offsetLeft,
	            pageY: node.span.offsetTop,
	            button: 2
	            });
	          return false;

	        // Handle Ctrl-C, -X and -V
	        case 67:
	          if( event.ctrlKey ) { // Ctrl-C
	            copyPaste("copy", node);
	            return false;
	          }
	          break;
	        case 86:
	          if( event.ctrlKey ) { // Ctrl-V
	            copyPaste("paste", node);
	            return false;
	          }
	          break;
	        case 88:
	          if( event.ctrlKey ) { // Ctrl-X
	            copyPaste("cut", node);
	            return false;
	          }
	          break;
	        }
		},
		onQuerySelect: function(select, node) {
	        if( node.data.isFolder )
	          return false;
		},
		cookieId: "dynatree-Cb1",
		idPrefix: "dynatree-Cb1-"
	});
	// Empresas
	$("#jqxTree").dynatree({
		checkbox: true,
		selectMode: 3,
		imagePath: "css/skin-custom/",
		onKeydown: function(node, event) {
			if( event.which == 32 ) {
					node.toggleSelect();
					return false;
				}
		},
		onDblClick: function(node, event){
			//alert(node.data.key);
			return false;
		},
		cookieId: "dynatree-Cb2",
		idPrefix: "dynatree-Cb2-"
	});
	// Modulos
	$("#modulo_tree").dynatree({
		checkbox: true,
		selectMode: 3,
		imagePath: "css/skin-custom/",
		onKeydown: function(node, event) {
			if( event.which == 32 ) {
				node.toggleSelect();
				return false;
			}
		},
		onDblClick: function(node, event){
				/*alert(node.data.url);
				alert(node.data.key);
				alert(node.data.hijo);*/
		},
		onActivate: function(node) {
			var hijo = $("#tabla_" + node.data.hijo);
			//alert("Hijo a mover : "+hijo.attr("id"));
			var padre = $("#tablas");
			//alert("Padre de donde se movera : "+padre.attr("id"));
			var newParent = $("#accesos");
			//alert("Nuevo Padre : "+newParent.attr("id"));
			var oldHijo = newParent.children();
			//alert("Hijo actual en el padre : "+oldHijo.attr("id"));
			oldHijo.detach();
			padre.append(oldHijo);
			hijo.detach();
			newParent.append(hijo);
			$("#accesos").mCustomScrollbar({
				verticalScroll: true,
				scrollButtons:{
					enable: true
				},
				scrollEasing: "easeOutQuint",
				autoDraggerLength: false,
				advanced:{
					autoExpandHorizontalScroll: true
				}
			});
		},
		onSelect: function( flag, node ) {
			if ( flag ) {
				var hijo = $("#tabla_" + node.data.hijo);
				//alert("Hijo a mover : "+hijo.attr("id"));
				var padre = $("#tablas");
				//alert("Padre de donde se movera : "+padre.attr("id"));
				var newParent = $("#accesos");
				//alert("Nuevo Padre : "+newParent.attr("id"));
				var oldHijo = newParent.children();
				//alert("Hijo actual en el padre : "+oldHijo.attr("id"));
				oldHijo.detach();
				padre.append(oldHijo);
				hijo.detach();
				newParent.append(hijo);
				$("#accesos").mCustomScrollbar({
					verticalScroll: true,
					scrollButtons:{
						enable: true
					},
					scrollEasing: "easeOutQuint",
					autoDraggerLength: false,
					advanced:{
						autoExpandHorizontalScroll: true
					}
				});
			}
		},
		cookieId: "dynatree-Cb3",
		idPrefix: "dynatree-Cb3-"
	});
	// Valores
	$("#valores_tree").dynatree({
		checkbox: true,
		selectMode: 3,
		imagePath: "css/skin-custom/",
		onKeydown: function(node, event) {
			if( event.which == 32 ) {
				node.toggleSelect();
				return false;
			}
		},
		cookieId: "dynatree-Cb4",
		idPrefix: "dynatree-Cb4-"
	});
	// Valores
	$("#funciones_tree").dynatree({
		checkbox: true,
		selectMode: 3,
		imagePath: "css/skin-custom/",
		onKeydown: function(node, event) {
			if( event.which == 32 ) {
				node.toggleSelect();
				return false;
			}
		},
		cookieId: "dynatree-Cb5",
		idPrefix: "dynatree-Cb5-"
	});


	$('#btnUserAdd').tooltip();
	$('#btnUserDelete').tooltip();
	$('#btnUserSave').tooltip();
	$('#btnUserPass').tooltip();
	$('#btnUserEmail').tooltip();
	$('a').tooltip();
	$('#btnUserDelete').click(function(){
		if($(this).is(':enabled')){
			$(this).addClass('disabled');
			$(this).attr('disabled', 'disabled');
			var datos = new Object();
	        datos['profile'] = <?php echo "'".$_GET['profile']."'"; ?>;
	        datos['idprofile'] = <?php echo "'".$_GET['idprofile']."'"; ?>;
	        datos['tipo'] = <?php echo "'".$_GET['tipo']."'"; ?>;
	        datos['tabla'] = 'usuario';
	        datos['accion'] = 'delete';
	        myUrl = location.protocol + "//" + location.host + '/sics/class/Formulario.php';
			$.ajax({
				type: 'POST',
				url: myUrl,
	            data: {
	                form: datos
				},
				beforeSend: function(){
					$.pnotify({
						title: 'espere..',
						text: 'eliminando usuario, por favor espere...',
						type: 'success',
						icon: 'icon-delete',
						hide: false
					});
					$('#btnUserDelete').after('<img src="img/FhHRx.gif"></img>');
				},
				success: function(aData){
					CancelUrl = location.protocol + "//" + location.host + '/sics/admin.php?c=usua&a=seguridad&id=4';
					window.location = CancelUrl;
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					alert(textStatus);
				}
			}).done(function(response, textStatus, jqXHR){
				$('#btnUserDelete').nextAll('img').remove();
			});
		}
	});
	// BOTON SALVAR AAYALA
	$('#btnUserSave').click(function(){
		estado = $(this).attr('class');
		if($(this).is(':enabled')){
			$(this).addClass('disabled');
			$(this).attr('disabled', 'disabled');
			$('#btnUserDelete').addClass('disabled');
			$('#btnUserDelete').attr('disabled', 'disabled');
			$('#btnUserAdd').addClass('disabled');
			$('#btnUserAdd').attr('disabled', 'disabled');
			$('#btnUserPass').addClass('disabled');
			$('#btnUserPass').attr('disabled', 'disabled');
			$('#btnUserEmail').addClass('disabled');
			$('#btnUserEmail').attr('disabled', 'disabled');
			// ARBOL DE FUNCIONES
			var treeFunciones = $("#funciones_tree").dynatree("getTree");
			var selFuncionesNodes = treeFunciones.getSelectedNodes();
			var funciones = new Object();
			$.map(selFuncionesNodes, function(node){
				funciones[node.data.key] = node.data.key;
			});
			// ARBOL DE UNIDADES Y VALORES
			var treeUndVls = $("#valores_tree").dynatree("getTree");
			var selUndVlsNodes = treeUndVls.getSelectedNodes();
			var undvls = new Object();
			$.map(selUndVlsNodes, function(node){
				undvls[node.data.key] = node.data.key;
			});
			// ARBOL DE MODULOS
			var treeModulo = $("#modulo_tree").dynatree("getTree");
			var selModuloNodes = treeModulo.getSelectedNodes();
			var accesos = new Object();
			var camposModulo = new Object();
			// lo metemos a un arrelo title/key array
	        $.map(selModuloNodes, function(node){
	             camposModulo[node.data.url] = node.data.key;
	             // Metemos los accesos seleccionados de la tabla
	             $("#tabla_"+node.data.hijo+" tbody tr").each(function(j){
		             var that = $(this);
		             acceso = that.find('td').eq(0).text();
		             if( acceso != "") {
		               	var string_acceso = that.find('td').eq(0).text()+
		 			   					'~' + that.find('td').eq(0).attr('mod-url')+
			 			   				'~' + that.find('td').eq(0).attr('id-mod-acc')+
		 			   					'~' + that.find('td').eq(1).find('input[type=checkbox]').is(':checked')+
		 			   					'~' + that.find('td').eq(2).find('input[type=checkbox]').is(':checked')+
		 			   					'~' + that.find('td').eq(3).find('input[type=checkbox]').is(':checked')+
		 			   					'~' + that.find('td').eq(4).find('input[type=checkbox]').is(':checked')+
		 			   					'~' + that.find('td').eq(5).find('input[type=checkbox]').is(':checked');
		               	accesos[that.find('td').eq(0).text()+'~'+that.find('td').eq(0).attr('id-mod')] = string_acceso;
		             }
				});
	        });
			// ARBOL DE EMPRESAS
			var tree = $("#jqxTree").dynatree("getTree");
			// lo seleccionado lo ponemos en esta variable para procesarla
		    var selNodes = tree.getSelectedNodes();
	        var campos = new Object();
	        var datos = new Object();
	        datos['profile'] = <?php echo "'".$_GET['profile']."'"; ?>;
	        datos['idprofile'] = <?php echo "'".$_GET['idprofile']."'"; ?>;
	        datos['tipo'] = <?php echo "'".$_GET['tipo']."'"; ?>;
	        datos['usr_crea'] = $('#usr_crea').val();
	        // lo metemos a un arrelo title/key array
	        $.map(selNodes, function(node){
	             campos[node.data.key] = node.data.title;
	        });
	        myUrl = location.protocol + "//" + location.host + '/sics/json.php?c=usua&a=ccSeleccionados';
			$.ajax({
				type: 'POST',
				url: myUrl,
                data: {
                    form: campos,
                    datos: datos,
                    modulo: camposModulo,
                    accesos: accesos,
                    funciones: funciones,
                    undvls: undvls
				},
				beforeSend: function(){
					$.pnotify({
						title: 'espere..',
						text: 'procesando la informacion, por favor espere...',
						type: 'success',
						icon: 'ui-icon ui-icon-signal-diag',
						hide: false
					});
					$('#btnUserSave').after('<img src="img/FhHRx.gif"></img>');
				},
				success: function(aData){
					CancelUrl = location.protocol + "//" + location.host + '/sics/admin.php?c=usua&a=seguridad&id=4';
					window.location = CancelUrl;
					//$("#seleccion").text(aData);
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					alert(textStatus);
					alert(errorThrown);
				}
			}).done(function(response, textStatus, jqXHR){
				$('#btnUserSave').nextAll('img').remove();
			});
			return false;
		}
		return false;
	});

	// Validacion de Formulario de Adicion de Usuario a Rol
	var aform = $("#frmAddUser2Rol").validate({
		rules: {
			usr_usuario: {
				required: true
			},
			usr_nombre:{
				required: true
			},
	     	usr_password: {
	       		required: true
	     	},
		    usr_password2: {
			    required: true,
			    equalTo: "#usr_password"
		    },
		    id_rol: {
			    required: true
		    }
	   },
	   messages: {
	     	usr_usuario: {
		     	required : "Digite usuario"
	     	},
			usr_nombre: {
				required: "Digite nombre"
			},
	     	usr_password: {
	       		required: "Digite contrase&ntilde;a"
	     	},
	     	usr_password2: {
		     	required: "Digite contrase&ntilde;a",
		     	equalTo: "Contrase&ntilde;as no son iguales"
	     	},
	     	id_rol: {
		     	required: "Seleccion Rol"
	     	}
		},
		submitHandler: function(form) {
		    var campos=xajax.getFormValues("frmAddUser2Rol");
			$(".ui-dialog-buttonset button").prop("disabled", true);
			$('.ui-dialog-buttonset button').addClass('disabled');
			$('.ui-dialog-buttonset button').attr('disabled', 'disabled');
			myUrlOC = location.protocol + "//" + location.host + '/sics/json.php?c=usua&a=verifica';
			$.post(myUrlOC,
				{
					form: campos
				},
				function(json) {
					if(json == "OK"){
						myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
						$.ajax({
							type : "POST",
							url : myUrl,
							data: {
								form: campos
							},
							beforeSend: function(){
								$(".ui-dialog-buttonset button").prop("disabled",true);
								$('.ui-dialog-buttonset button').addClass('disabled');
								$('.ui-dialog-buttonset button').attr('disabled', 'disabled');
								$.pnotify({
									title: 'adicionando...',
									text: 'ejecutando adicion de usuario, por favor espere.',
									type: 'success',
									icon: 'ui-icon ui-icon-alert',
									hide: true
								});
							},
							success : function(data){
								if(data == 1 ){
									$.pnotify({
										title: 'error',
										text: 'ha ocurrido un error al adicionar. '+ data,
										type: 'error',
										icon: 'ui-icon ui-icon-alert',
										hide: true
									});
									$(".ui-dialog-buttonset button").removeProp("disabled");
									$('.ui-dialog-buttonset button').removeClass('disabled');
									$('.ui-dialog-buttonset button').removeAttr('disabled', '');
								} else {
									location.reload();
								}
							},
							error : function(XMLHttpRequest, textStatus, errorThrown) {
								$.pnotify({
									title: 'error',
									text: 'ha ocurrido un error en la ejecucion.',
									type: 'error',
									icon: 'ui-icon ui-icon-alert',
									hide: true
								});
								$(".ui-dialog-buttonset button").removeProp("disabled");
								$('.ui-dialog-buttonset button').removeClass('disabled');
								$('.ui-dialog-buttonset button').removeAttr('disabled', '');
							}
						});
					} else {
						$.pnotify({
							title: 'error',
							text: 'ha ocurrido un error en la ejecucion : '+json,
							type: 'error',
							icon: 'ui-icon ui-icon-alert',
							hide: true
						});
						$(".ui-dialog-buttonset button").removeProp("disabled");
						$('.ui-dialog-buttonset button').removeClass('disabled');
						$('.ui-dialog-buttonset button').removeAttr('disabled', '');
					}
			 	}
			);
	     }
	});
	// our modal dialog setup
	var amodal = $("#formAdicion").dialog({
	   bgiframe: true,
	   autoOpen: false,
       resizable: false,
       modal: true,
       width: 600,
		buttons: [
			{
				text: 'Agregar',
				'class':'btn btn-primary',
				click: function(){
					$("#frmAddUser2Rol").submit();
				}
			},
			{
				text: 'Cancelar',
				'class': 'btn',
				click: function(){
					$(this).dialog('close');
				    aform.resetForm();
				    $('#frmAddUser2Rol').each (function(){
				    	  this.reset();
				    });
				}
			}
		],
		close : function(){
			$(this).dialog('close');
		    aform.resetForm();
		    $('#frmAddUser2Rol').each (function(){
		    	  this.reset();
		    });
		}
	});
	// Formulario cambia contrase�a
	var amodalpass = $("#formAdicionPass").dialog({
	   bgiframe: true,
	   autoOpen: false,
       resizable: false,
       modal: true,
       width: 600,
		buttons: [
			{
				text: 'Confirmar',
				'class':'btn btn-primary',
				click: function(){
					console.log("enviando formulario");
					$("#frmAddUser2Rol2Pass").submit();
				}
			},
			{
				text: 'Cancelar',
				'class': 'btn',
				click: function(){
					$(this).dialog('close');
				    aformpass.resetForm();
				    $('#frmAddUser2Rol2Pass').each (function(){
				    	  this.reset();
				    });
				}
			}
		],
		close : function(){
			$(this).dialog('close');
		    aformpass.resetForm();
		    $('#frmAddUser2Rol2Pass').each (function(){
		    	  this.reset();
		    });
		}
	});
	// Validacion de Formulario de Cambio de Password
	var aformpass = $("#frmAddUser2Rol2Pass").validate({
		rules: {
	     	usr_pasword: {
	       		required: true
	     	},
		    usr_pasword2: {
			    required: true,
			    equalTo: "#usr_pasword"
		    }
	   },
	   messages: {
	     	usr_pasword: {
	       		required: "Digite contrase&ntilde;a"
	     	},
	     	usr_pasword2: {
		     	required: "Digite contrase&ntilde;a",
		     	equalTo: "Contrase&ntilde;as no son iguales"
	     	}
		},
		submitHandler: function(form) {
		    var campos=xajax.getFormValues("frmAddUser2Rol2Pass");

			$(".ui-dialog-buttonset button").prop("disabled", true);
			$('.ui-dialog-buttonset button').addClass('disabled');
			$('.ui-dialog-buttonset button').attr('disabled', 'disabled');
			myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
			$.ajax({
				type : "POST",
				url : myUrl,
				data: {
					form: campos
				},
				beforeSend: function(){
					$(".ui-dialog-buttonset button").prop("disabled",true);
					$('.ui-dialog-buttonset button').addClass('disabled');
					$('.ui-dialog-buttonset button').attr('disabled', 'disabled');
					$.pnotify({
						title: 'adicionando...',
						text: 'ejecutando adicion de usuario, por favor espere.',
						type: 'success',
						icon: 'ui-icon ui-icon-alert',
						hide: true
					});
				},
				success : function(data){
					//alert(data);
					if(data == 1 ){
						$.pnotify({
							title: 'error',
							text: 'ha ocurrido un error al adicionar. '+ data,
							type: 'error',
							icon: 'ui-icon ui-icon-alert',
							hide: true
						});
						$(".ui-dialog-buttonset button").removeProp("disabled");
						$('.ui-dialog-buttonset button').removeClass('disabled');
						$('.ui-dialog-buttonset button').removeAttr('disabled', '');
					} else {
						location.reload();
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: 'error',
						text: 'ha ocurrido un error en la ejecucion.',
						type: 'error',
						icon: 'ui-icon ui-icon-alert',
						hide: true
					});
					$(".ui-dialog-buttonset button").removeProp("disabled");
					$('.ui-dialog-buttonset button').removeClass('disabled');
					$('.ui-dialog-buttonset button').removeAttr('disabled', '');
				}
			}); 
	     }
	});
	
	$('.ui-dialog-titlebar').show();
	var abutton = $('#btnUserAdd').click(function() {
	    $('#formAdicion').dialog('open');
	});
	var abuttonpass = $('#btnUserPass').click(function() {
	    $('#formAdicionPass').dialog('open');
	});
	var abuttonmail = $('#btnUserEmail').click(function() {
	    $('#formAdicionEmail').dialog('open');
	});
    var abuttonrol = $('#btnUserRol').click(function() {
	    $('#formEdicionRol').dialog('open');
	});

    

	// Formulario cambia contrase�a
	var amodalpass = $("#formAdicionEmail").dialog({
	   bgiframe: true,
	   autoOpen: false,
       resizable: false,
       modal: true,
       width: 600,
		buttons: [
			{
				text: 'Confirmar',
				'class':'btn btn-primary',
				click: function(){
					console.log("enviando formulario");
					$("#frmAddUser2Rol3Pass").submit();
				}
			},
			{
				text: 'Cancelar',
				'class': 'btn',
				click: function(){
					$(this).dialog('close');
				    aformmail.resetForm();
				    $('#frmAddUser2Rol3Pass').each (function(){
				    	  this.reset();
				    });
				}
			}
		],
		close : function(){
			$(this).dialog('close');
		    aformmail.resetForm();
		    $('#frmAddUser2Rol3Pass').each (function(){
		    	  this.reset();
		    });
		}
	});

    // Formulario cambia rol
	var amodalrol = $("#formEdicionRol").dialog({
	   bgiframe: true,
	   autoOpen: false,
       resizable: false,
       modal: true,
       width: 600,
		buttons: [
			{
				text: 'Confirmar',
				'class':'btn btn-primary',
				click: function(){
					console.log("enviando formulario");
					$("#frmEditUserRol").submit();
				}
			},
			{
				text: 'Cancelar',
				'class': 'btn',
				click: function(){
					$(this).dialog('close');
				    aformmail.resetForm();
				    $('#frmEditUserRol').each (function(){
				    	  this.reset();
				    });
				}
			}
		],
		close : function(){
			$(this).dialog('close');
		    aformmail.resetForm();
		    $('#frmEditUserRol').each (function(){
		    	  this.reset();
		    });
		}
	});

    
	// Validacion de Formulario de Cambio de Password
	var aformmail = $("#frmAddUser2Rol3Pass").validate({
		rules: {
	     	correo: {
	       		required: true
	     	}
	   },
	   messages: {
	     	correo: {
	       		required: "Digite direccion de correo"
	     	}
		},
		submitHandler: function(form) {
		    var campos=xajax.getFormValues("frmAddUser2Rol3Pass");
			$(".ui-dialog-buttonset button").prop("disabled", true);
			$('.ui-dialog-buttonset button').addClass('disabled');
			$('.ui-dialog-buttonset button').attr('disabled', 'disabled');
			myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
			$.ajax({
				type : "POST",
				url : myUrl,
				data: {
					form: campos
				},
				beforeSend: function(){
					$(".ui-dialog-buttonset button").prop("disabled",true);
					$('.ui-dialog-buttonset button').addClass('disabled');
					$('.ui-dialog-buttonset button').attr('disabled', 'disabled');
					$.pnotify({
						title: 'actualizando...',
						text: 'ejecutando actualizacion de usuario, por favor espere.',
						type: 'success',
						icon: 'ui-icon ui-icon-alert',
						hide: true
					});
				},
				success : function(data){
					if(data == 1 ){
						$.pnotify({
							title: 'error',
							text: 'ha ocurrido un error al actualizar. '+ data,
							type: 'error',
							icon: 'ui-icon ui-icon-alert',
							hide: true
						});
						$(".ui-dialog-buttonset button").removeProp("disabled");
						$('.ui-dialog-buttonset button').removeClass('disabled');
						$('.ui-dialog-buttonset button').removeAttr('disabled', '');
					} else {
						location.reload();
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: 'error',
						text: 'ha ocurrido un error en la ejecucion.',
						type: 'error',
						icon: 'ui-icon ui-icon-alert',
						hide: true
					});
					$(".ui-dialog-buttonset button").removeProp("disabled");
					$('.ui-dialog-buttonset button').removeClass('disabled');
					$('.ui-dialog-buttonset button').removeAttr('disabled', '');
				}
			}); 
	     }
	});

    //AAYALA
    // Validacion de Formulario de Cambio de Password
	var aformrol = $("#frmEditUserRol").validate({
		rules: {
	     	rol_user: {
	       		required: true
	     	}
	   },
	   messages: {
	     	correo: {
	       		required: "Seleccione rol de usuario"
	     	}
		},
		submitHandler: function(form) {
		    var campos=xajax.getFormValues("frmEditUserRol");
			$(".ui-dialog-buttonset button").prop("disabled", true);
			$('.ui-dialog-buttonset button').addClass('disabled');
			$('.ui-dialog-buttonset button').attr('disabled', 'disabled');
			myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
			$.ajax({
				type : "POST",
				url : myUrl,
				data: {
					form: campos
				},
				beforeSend: function(){
					$(".ui-dialog-buttonset button").prop("disabled",true);
					$('.ui-dialog-buttonset button').addClass('disabled');
					$('.ui-dialog-buttonset button').attr('disabled', 'disabled');
					$.pnotify({
						title: 'actualizando...',
						text: 'ejecutando actualizacion de usuario, por favor espere.',
						type: 'success',
						icon: 'ui-icon ui-icon-alert',
						hide: true
					});
				},
				success : function(data){
					if(data == 1 ){
						$.pnotify({
							title: 'error',
							text: 'ha ocurrido un error al actualizar. '+ data,
							type: 'error',
							icon: 'ui-icon ui-icon-alert',
							hide: true
						});
						$(".ui-dialog-buttonset button").removeProp("disabled");
						$('.ui-dialog-buttonset button').removeClass('disabled');
						$('.ui-dialog-buttonset button').removeAttr('disabled', '');
					} else {
						location.reload();
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: 'error',
						text: 'ha ocurrido un error en la ejecucion.',
						type: 'error',
						icon: 'ui-icon ui-icon-alert',
						hide: true
					});
					$(".ui-dialog-buttonset button").removeProp("disabled");
					$('.ui-dialog-buttonset button').removeClass('disabled');
					$('.ui-dialog-buttonset button').removeAttr('disabled', '');
				}
			}); 
	     }
	});

 
});
</script>

<?php

if(isset($_GET['profile']) && $_GET['profile'] != ""){
?>
	<script type="text/javascript">
	$(function(){
		$('#btnUserSave').removeClass('disabled');
		$('#btnUserSave').removeAttr('disabled');
		$("#btnUserSave").removeProp("disabled");
		/* */
		$("#btnUserPass").removeProp("disabled");
		$('#btnUserPass').removeAttr('disabled');
		$("#btnUserPass").removeProp("disabled");
		/* */
		$("#btnUserEmail").removeProp("disabled");
		$('#btnUserEmail').removeAttr('disabled');
		$("#btnUserEmail").removeProp("disabled");
		/* */
		$('div.col-md-4.table-bordered').unblock();
	});
	</script>
<?php
	if (isset($_GET['tipo']) && $_GET['tipo'] != "") {
		if ($_GET['tipo'] == "user") {
			?>
			<script type="text/javascript">
			$(function(){
				$('#btnUserDelete').removeClass('disabled');
				$('#btnUserDelete').removeAttr('disabled');
				$("#btnUserDelete").removeProp("disabled");
				/* */
				$("#btnUserPass").removeProp("disabled");
				$('#btnUserPass').removeAttr('disabled');
				$("#btnUserPass").removeProp("disabled");
				/* */
				$("#btnUserEmail").removeProp("disabled");
				$('#btnUserEmail').removeAttr('disabled');
				$("#btnUserEmail").removeProp("disabled");
				
				$('div.col-md-4.table-bordered').unblock();
			});
			</script>
			<?php
		} else {
		?>
		<script type="text/javascript">
		$(function(){
			$('#btnUserDelete').addClass('disabled');
			$('#btnUserDelete').attr('disabled', 'disabled');
			$("#btnUserDelete").prop("disabled", true);
			$('div.col-md-4.table-bordered').unblock();
		});
		</script>
		<?php
		}
	}
} else {
?>
	<script type="text/javascript">
	$(function(){
		$('#btnUserSave').addClass('disabled');
		$('#btnUserSave').attr('disabled', 'disabled');
		$("#btnUserSave").prop("disabled", true);
		$('#btnUserDelete').addClass('disabled');
		$('#btnUserDelete').attr('disabled', 'disabled');
		$("#btnUserDelete").prop("disabled", true);
		$('#btnUserPass').addClass('disabled');
		$('#btnUserPass').attr('disabled', 'disabled');
		$("#btnUserPass").prop("disabled", true);
		/* */
		$('#btnUserEmail').addClass('disabled');
		$('#btnUserEmail').attr('disabled', 'disabled');
		$("#btnUserEmail").prop("disabled", true);
	
		$('div.col-md-4.table-bordered').not('#roles').block({
			message: null,
			// styles for the overlay
			overlayCSS:  {
				backgroundColor: '#FFF',
				opacity: 1,
				cursor: 'none'
			}
		});

	});
	</script>
<?php
}
?>

<!-- Definition of context menu -->
  <ul id="myMenu" class="contextMenu">
    <li class="edit"><a href="#edit">Editar</a></li>
    <!-- <li class="cut separator"><a href="#cut">Cut</a></li>
    <li class="copy"><a href="#copy">Copy</a></li>
    <li class="paste"><a href="#paste">Paste</a></li> -->
    <li class="delete"><a href="#delete">Delete</a></li>
	<?php if(isset($_GET['profile']) && $_GET['profile'] != ""){ ?>
    <li class="quit separator"><a href="#quit">Abandonar</a></li>
	<?php } ?>
  </ul>

<div class="btn-toolbar">
	<div class="btn-group">
		<button  id="btnUserAdd" name="btnUserAdd" class="btn btn-sm btn-default" data-placement="right" rel="tooltip" title="Adicionar usuario a rol">
			<i class="glyphicon glyphicon-user"></i>
			Adicionar Usuario
		</button >
		<button  id="btnUserDelete" name="btnUserDelete" class="btn btn-sm btn-default" data-placement="right" rel="tooltip" title="Elimina usuario seleccionado">
			<i class="glyphicon glyphicon-trash"></i>
			Eliminar Usuario
		</button >
		<button  id="btnUserSave" name="btnUserSave" class="btn btn-sm btn-default" data-placement="right" rel="tooltip" title="Guardar cambios realizados">
			<i class="glyphicon glyphicon-hdd"></i>
			Guardar Cambios
		</button >
		<button  id="btnUserPass" name="btnUserPass" class="btn btn-sm btn-default" data-placement="right" rel="tooltip" title="Cambiar Password">
			<i class="glyphicon glyphicon-lock"></i>
			Cambiar Contrase&ntilde;a
		</button >
		<button  id="btnUserEmail" name="btnUserEmail" class="btn btn-sm btn-default" data-placement="right" rel="tooltip" title="Cambiar Correo">
			<i class="glyphicon glyphicon-envelope"></i>
			Cambiar Correo
		</button >
        <button  id="btnUserRol" name="btnUserRol" class="btn btn-sm btn-default" data-placement="right" rel="tooltip" title="Cambiar Rol">
			<i class="glyphicon glyphicon-user"></i>
			Cambiar Rol
		</button >
		<button  id="spanSelect" class="btn btn-sm btn-info" data-toggle="chardinjs">
		<?php if(isset($_GET['profile']) && $_GET['profile'] != ""){ ?>
			<i class="glyphicon glyphicon-eye-open"></i>
			<?php echo $_GET['profile']; ?>
		<?php } else { ?>
			<i class="glyphicon glyphicon-play"></i>
		<?php } ?>
		</button >
	</div>
</div>
<br>
<div class="container">
	<div class="row">
		<div class="col-md-4">
			<div class="well well-small" data-intro="Project title" data-position="down"><b>ROLES</b></div>
		</div>
		<div class="col-md-4">
			<div class="well well-small" data-intro="Project title" data-position="left"><b>EMPRESAS</b></div>
		</div>
		<div class="col-md-4">
			<div class="well well-small" data-intro="Project title" data-position="top"><b>FUNCION PRINCIPAL</b></div>
		</div>
	</div>
	<div class="row">
		<div id="roles" class="col-md-4 table-bordered" style="overflow: auto;">
			<div id="roles_tree" data-toggle="context" data-target="#context-menu" >
				<ul id="treeData" style="display: none;">
				<?php foreach ($roles as $rol) { ?>
					<li data="icon: 'administrator.png', key: '<?php echo $rol[0]; ?>', tipo:'rol'" class="folder <?php if($rol[1] == $_GET['profile']) echo $_GET['s']; ?>">
						<?php echo $rol[1]; ?>
						<ul>
							<?php foreach($roles_user as $rol_user) {
								if($rol_user[1] == $rol[0]){
							?>
								<li data="title: '<?php echo $rol_user[6]; ?>', icon: 'checked-user.png', key: '<?php echo $rol_user[2]; ?>', tipo:'user'" class="<?php if($rol_user[6] == $_GET['profile']) echo $_GET['s']; ?>"><?php echo $rol_user[6]; ?></li>
							<?php
								}
							} ?>
						</ul>
					</li>
				<?php
				}
				?>
				</ul>
			</div>
		</div>
		<div id="empresas" class="col-md-4 table-bordered" style="overflow: auto;">
			<div id="jqxTree">
				<ul id="treeData" style="display: none;">
				<?php
				$in20 = 0;
				foreach ($cc as $cc1) {
					if($emp != $cc1[0]){
						if($in20 == 1) {
						?>
						</ul>
						</li>
						<?php
						}
				?>
					<li data="icon:'home.png', key:'<?php echo $cc1[0].'~X'; ?>'" >
						<strong><?php echo $cc1[1]; ?></strong>
						<ul>
							<li class="<?php echo $cc1['estado']; ?>" data="icon:'sale.png', key:'<?php echo $cc1[0].'~'.$cc1[2]; ?>'">
								<?php echo $cc1[4]; ?>
							</li>
				<?php
					} else {
				?>
					<li class="<?php echo $cc1['estado']; ?>" data="icon:'sale.png',key:'<?php echo $cc1[0].'~'.$cc1[2]; ?>'">
						<?php echo $cc1[4]; ?>
					</li>
				<?php
					}
					$emp = $cc1[0];
					$in20 = 1;
				}
				?>
				</ul>
			</div>
		</div>
		<div class="col-md-4 table-bordered" style="overflow: auto;">
			<div id="funciones_tree">
				<ul id="treeData" style="display: none;">
					<li class="<?php if(isset($datos_usr[0]['estado_oc'])) echo $datos_usr[0]['estado_oc']; ?>" data="icon:'outline.png', key:'oc'">ORDEN DE COMPRA</li>
					<li class="<?php if(isset($datos_usr[0]['estado_sol'])) echo $datos_usr[0]['estado_sol']; ?>" data="icon:'cllipboard.png', key:'sol'">SOLICITUD DE COMPRA</li>
					<li class="<?php if(isset($datos_usr[0]['estado_req'])) echo $datos_usr[0]['estado_req']; ?>" data="icon:'calendar.png', key:'req'">REQUISICION DE SUMINISTRO</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4">
			<div class="well well-small"><b>MODULOS</b></div>
		</div>
		<div class="col-md-4">
			<div class="well well-small"><b>ACCESO DE MODULO</b></div>
		</div>
		<div class="col-md-4">
			<div class="well well-small"><b>VALORES</b></div>
		</div>
	</div>
	<div class="row">
		<div id="modulos" class="col-md-4 table-bordered" style="overflow: auto;">
			<div id="modulo_tree">
				<ul id="treeData" style="display: none;">
					<?php
					foreach ($mods as $mod) {
					?>
					<li class="<?php echo $mod['estado']; ?>" data="hijo: '<?php echo $mod['mod_hijo']; ?>', icon: '<?php echo $mod['mod_icon']; ?>', url: '<?php echo $mod[3].'~'.$mod[7]; ?>', key: '<?php echo $mod[0].'~'.$mod[1].'~'.$mod[2]; ?>'">
						<?php echo $mod[1]; ?>
					</li>
					<?php
					}
					?>
				</ul>
			</div>
		</div>
		<div id="accesos" class="col-md-4 table-bordered" style="overflow: auto;">
			<!-- -->
		</div>
		<div class="col-md-4 table-bordered" style="overflow: hidden;">
			<div id="valores_tree">
				<ul id="treeData" style="display: none;">
					<li class="<?php if(isset($datos_usr[1]['estado_und'])) echo $datos_usr[1]['estado_und']; ?>" data="icon:'scales.png', key:'und'">UNIDADES</li>
					<li class="<?php if(isset($datos_usr[1]['estado_vls'])) echo $datos_usr[1]['estado_vls']; ?>" data="icon:'coins.png', key:'vls'">VALORES</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<!-- FORMULARIO DE ADICION DE USUARIO -->
<div id="formAdicion" title="Adicion de Usuario a Rol" style="padding: 50px;">
	<!-- Nuestro formulario sin botones los agregaremos con jquery-ui -->
    <form action="#" name="frmAddUser2Rol" id="frmAddUser2Rol" method="POST" class="form-horizontal" role="form">
	    <div class="form-group">
			<label class="control-label col-md-4" for="usr_usuario">Usuario</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="text" name="usr_usuario" id="usr_usuario" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-4" for="usr_nombre">Nombre</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="text" name="usr_nombre" id="usr_nombre" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-4" for="usr_password">Contrase&ntilde;a</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="password" name="usr_password" id="usr_password" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-4" for="usr_password2">Verificar Contrase&ntilde;a</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="password" name="usr_password2"	id="usr_password2" />
			</div>
		</div>

	    <?php
		$db = DB::getInstance ();
		$conf = Configuracion::getInstance ();
		$sqlRol = "Select * From " . $conf->getTbl_rol () . " Where rol_orden > 0 Order by rol_orden";
		$RolExec = $db->ejecutar ( $sqlRol );
		?>
		<div class="form-group">
			<label class="control-label col-md-4" for="id_rol">Rol de Usuario</label>
			<div class="col-md-8">
				<select class="form-control input-sm" name="id_rol" id="id_rol">
					<option value="">Seleccion rol</option>
					<?php
					while ( $row = mysqli_fetch_array ( $RolExec ) ) {
						?>
						<option value="<?php echo $row['id_rol']; ?>"><?php echo $row['rol_descripcion']; ?></option>
						<?php
					}
					?>
				</select>
			</div>
		</div>
		<input type="hidden" value="add" id="accion" name="accion">
		<input type="hidden" value="usuario" id="tabla" name="tabla">
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
    </form>
</div>

<!-- FORMULARIO DE CAMBIO DE CONTRASE�A -->
<div id="formAdicionPass" title="Cambiar Password de Usuario" style="padding: 50px;">
	<!-- Nuestro formulario sin botones los agregaremos con jquery-ui -->
    <form action="#" name="frmAddUser2Rol2Pass" id="frmAddUser2Rol2Pass" method="POST" class="form-horizontal" role="form">
	    <div class="form-group">
			<label class="control-label col-md-4" for="usr_usuario">Usuario</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="text" value="<?php  echo $_GET['profile']; ?>" name="lbl_usuario" id="lbl_usuario" disabled/>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-4" for="usr_password">Nueva Contrase&ntilde;a</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="password" name="usr_pasword" id="usr_pasword" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-4" for="usr_password2">Confirmar Contrase&ntilde;a</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="password" name="usr_pasword2"	id="usr_pasword2" />
			</div>
		</div>
		<input type="hidden" value="chgpass" id="accion" name="accion">
		<input type="hidden" value="usuario" id="tabla" name="tabla">
		<input type="hidden" value="<?php echo $_GET['profile']; ?>" id="usr_usuar" name="usr_usuar">
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
    </form>
</div>

<!-- FORMULARIO DE CORREO -->
<div id="formAdicionEmail" title="Asignar/Cambiar Correo de Usuario" style="padding: 50px;">
	<!-- Nuestro formulario sin botones los agregaremos con jquery-ui -->
    <form action="#" name="frmAddUser2Rol3Pass" id="frmAddUser2Rol3Pass" method="POST" class="form-horizontal" role="form">
	    <div class="form-group">
			<label class="control-label col-md-4" for="usr_usuario">Usuario</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="text" value="<?php  echo $_GET['profile']; ?>" name="lbl_usuario" id="lbl_usuario" disabled/>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-4" for="correo">Nuevo Correo</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="email" name="correo" id="correo" value="<?php if(isset($datos_usr[0]['usr_email'])) echo $datos_usr[0]['usr_email']; ?>" />
			</div>
		</div>

		<input type="hidden" value="chgmail" id="accion" name="accion">
		<input type="hidden" value="usuario" id="tabla" name="tabla">
		<input type="hidden" value="<?php echo $_GET['profile']; ?>" id="usr_usuar" name="usr_usuar">
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
    </form>
</div>

<!-- FORMULARIO DE ROL AAYALA -->
<div id="formEdicionRol" title="Asignar/Cambiar Rol de Usuario" style="padding: 50px;">
	<!-- Nuestro formulario sin botones los agregaremos con jquery-ui -->
    <form action="#" name="frmEditUserRol" id="frmEditUserRol" method="POST" class="form-horizontal" role="form">
	    <div class="form-group">
			<label class="control-label col-md-4" for="usr_usuario">Usuario</label>
			<div class="col-md-8">
				<input class="form-control input-sm" type="text" value="<?php  echo $_GET['profile']; ?>" name="lbl_usuario" id="lbl_usuario" disabled/>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-4" for="rol_user">Rol</label>
			<div class="col-md-8">
                <select id="rol_user" name="rol_user" class="form-control">
                    <option value="999999996" <?php if(isset($datos_usr[0]['usr_email'])) echo ($datos_usr[0]['id_rol']==999999996 ? "selected" : ""); ?>>ADMINISTRADOR COMPRAS Y SUMINISTROS</option>
                    <option value="999999998" <?php if(isset($datos_usr[0]['usr_email'])) echo ($datos_usr[0]['id_rol']==999999998 ? "selected" : ""); ?>>ADMINISTRADOR GENERAL</option>
                    <option value="999999995" <?php if(isset($datos_usr[0]['usr_email'])) echo ($datos_usr[0]['id_rol']==999999995 ? "selected" : ""); ?> >AUTORIZADOR USUARIO FINAL</option>
                    <option value="999999997" <?php if(isset($datos_usr[0]['usr_email'])) echo ($datos_usr[0]['id_rol']==999999997 ? "selected" : ""); ?>>JEFE DE COMPRAS</option>
                    <option value="999999994" <?php if(isset($datos_usr[0]['usr_email'])) echo ($datos_usr[0]['id_rol']==999999994 ? "selected" : ""); ?>>USUARIO FINAL</option>
                </select>
			</div>
		</div>

		<input type="hidden" value="chgrol" id="accion" name="accion">
		<input type="hidden" value="usuario" id="tabla" name="tabla">
		<input type="hidden" value="<?php echo $_GET['profile']; ?>" id="usr_usuar" name="usr_usuar">
        <input type="hidden" value="<?php echo $_GET['idprofile']; ?>" id="usr_id" name="usr_id">
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
    </form>
</div>

<div id="tablas" class="hidden">
	<?php
	$in20 = '0';
	$modulo_categoria = 0;
	$categoria_modulo = "";
	foreach ($modulo_lista as $mod_lista) {
		if ($mod_lista['id_modulo'] != $modulo_categoria) {
			if($in20 == '1'){
			?>
				</tbody>
				</table>
			<?php
			}
			?>
			<table id="tabla_<?php echo $mod_lista['acc_modulo_lista_hijo']; ?>" class="table table-condensed">
				<thead>
					<tr>
						<th></th>
						<th><a href="#" rel="tooltip" data-placement="bottom" title="Crear o Visualizar"><i aria-hidden="true" class="glyphicon glyphicon-plus"></i></a></th>
						<th><a href="#" rel="tooltip" data-placement="bottom" title="Modificar"><i aria-hidden="true" class="glyphicon glyphicon-pencil"></i></a></th>
						<th><a href="#" rel="tooltip" data-placement="bottom" title="Eliminar"><i aria-hidden="true" class="glyphicon glyphicon-minus"></i></a></th>
						<th><a href="#" rel="tooltip" data-placement="bottom" title="Exporta Excel"><i aria-hidden="true" class="glyphicon glyphicon-list"></i></a></th>
						<th><a href="#" rel="tooltip" data-placement="bottom" title="Autorizar"><i aria-hidden="true" class="glyphicon glyphicon-ok-sign"></i></a></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if ($mod_lista['acc_modulo_lista_categoria'] != $categoria_modulo) {
					?>
						<tr>
							<th colspan="6">
								<?php echo $mod_lista['acc_modulo_lista_categoria']; ?>
							</th>
						</tr>
					<?php
						}
					?>
					<tr>
						<td id-mod-acc="<?php echo $mod_lista['id_acc_modulo_lista']; ?>" mod-url="<?php echo $mod_lista['acc_modulo_lista_url']; ?>" id-mod="<?php echo $mod_lista['id_modulo']; ?>"><?php echo $mod_lista['acc_modulo_lista_descripcion']; ?></td>
						<td><input type="checkbox" <?php echo $mod_lista['estado1']; ?>></td>
						<td><input type="checkbox" <?php echo $mod_lista['estado2']; ?>></td>
						<td><input type="checkbox" <?php echo $mod_lista['estado3']; ?>></td>
						<td><input type="checkbox" <?php echo $mod_lista['estado4']; ?>></td>
						<td><input type="checkbox" <?php echo $mod_lista['estado5']; ?>></td>
					</tr>
			<?php
		} else {
		?>
			<tr>
				<td id-mod-acc="<?php echo $mod_lista['id_acc_modulo_lista']; ?>" mod-url="<?php echo $mod_lista['acc_modulo_lista_url']; ?>" id-mod="<?php echo $mod_lista['id_modulo']; ?>"><?php echo $mod_lista['acc_modulo_lista_descripcion']; ?></td>
				<td><input type="checkbox" <?php echo $mod_lista['estado1']; ?>></td>
				<td><input type="checkbox" <?php echo $mod_lista['estado2']; ?>></td>
				<td><input type="checkbox" <?php echo $mod_lista['estado3']; ?>></td>
				<td><input type="checkbox" <?php echo $mod_lista['estado4']; ?>></td>
				<td><input type="checkbox" <?php echo $mod_lista['estado5']; ?>></td>
			</tr>
		<?php
		}
		$modulo_categoria = $mod_lista['id_modulo'];
		$categoria_modulo = $mod_lista['acc_modulo_lista_categoria'];
		$in20 = '1';
	}
	?>
</div>
<div id="seleccion"></div>
<div id="ejecucion"></div>
<script type="text/javascript">
	(function($){
		$('body').chardinJs();
		$('button[data-toggle="chardinjs"]').on('click', function(e) {
		      e.preventDefault();
			$('body').data('chardinJs').toggle();
		});
	})(jQuery);
</script>