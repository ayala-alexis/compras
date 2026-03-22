<?php
session_start ();
?>
<script>
$(document).ready(function(){
	/*
	$('input.filter').live('keyup', function() {
	    var rex = new RegExp($(this).val(), 'i');
	    $('.searchable tr').hide();
	        $('.searchable tr').filter(function() {
	            return rex.test($(this).text());
	        }).show();
	    });
*/
    $("#frmAdd").validate({
	    rules: {
			id_categoria: {
				required: true
			},
			prov_nombre:{
				required: true
			},
	     	prov_razon: {
	       		required: true
	     	},
			prov_nit: {
				required: true
			},
		    prov_telefono1: {
			    required: true
		    },
		    prov_contacto1: {
			    required: true
		    },
			prov_direccion1: {
				required: true
			}
	   },
	   messages: {
	     	id_categoria: {
		     	required : "Seleccione categoria"
	     	},
			prov_nombre: {
				required: "Digite nombre"
			},
	     	prov_razon: {
	       		required: "Digite razon"
	     	},
			prov_nit: {
				required: "Digite NIT"
			},
	     	prov_telefono1: {
		     	required: "Digite telefono"
	     	},
	     	prov_contacto1: {
		     	required: "Digite contacto"
	     	},
			prov_direccion1: {
				required: "Digite direccion"
			}
	   },
		submitHandler: function(form) {
		    var campos=xajax.getFormValues("frmAdd");
			campos["num_pagi"] = $("#num_pag").val();
			myUrl = location.protocol + "//" + location.host + "/sics/class/Formulario.php";
			$.ajax({
				type : "POST",
				url : myUrl,
				data: {
					form: campos
				},
				beforeSend: function(){
					$.pnotify({
						title: "realizando peticion...",
						text: "intentando adicionar registro, por favor espere...",
						icon: "glyphicon glyphicon-search",
						hide: true
					});
					//$(":submit").addClass("disabled");
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
						if(data == 1 ){
							$.pnotify({
								title: "error",
								text: data,
								icon: "glyphicon glyphicon-ban-circle",
								hide: true,
								type: "error"
							});
						} else {
							$.pnotify({
								title: "adicionado",
								text: "Registro adicionado.",
								icon: "glyphicon glyphicon-ok",
								hide: true,
								type: "success"
							});
						    $("#formDiv input[type=reset]").click();
							$("#formDiv").dialog("close");
							pagina(campos["num_pagi"],"proveedor");
						}
					}
					$(":submit").removeAttr("disabled");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: "error...",
						text: "Error en la ejecucion.",
						icon: "glyphicon glyphicon-ban-circle",
						hide: true,
						type: "error"
					});
				    $(":submit").removeClass("disabled");
				}
			});
	   }
	});
	// Filtramos
	$('#btnBuscar').live('click', function(){
		pagina(0, 'proveedor');
	});
});
</script>
<script>
function pagina(pagi, tabl){
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionProveedor.php';
	var $filnom = $.trim($('#filtronombre').val());
	var $filcat = $.trim($('#filtrocategoria').val());
	if($('#filtrocategoria').length <= 0) {
		$filcat = 'todos';
	}
	$.ajax({
		type : 'POST',
		url : myUrl,
		data: {
			tab: tabl,
			pag: pagi,
			filtronombre: $filnom,
			filtrocategoria: $filcat
		},
		success : function(data){
			$('#contenido').html(data);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
		    $('#waiting').dialog('close');
			$('#alertBoxes').html('<div id="boxError" class="alert alert-danger"></div>');
			$('#boxError').html('Ha ocurrido un error durante la ejecucion.');
		}
	});
};
pagina(0,'proveedor');
</script>
<div id="contenido" class="row"></div>
<div id="formDiv" style="display: none; overflow: hidden;">
	<fieldset>
		<legend>Adicionar Proveedor</legend>
	</fieldset>
	<form role="form" class="form-horizontal" id="frmAdd" name="frmAdd" method="POST">

		<div class="form-group">
			<label class="control-label col-md-2" for="id_categoria">Categoria</label>
			<div class="col-md-10">
				<?php
				try {
					$db = DB::getInstance ();
					$conf = Configuracion::getInstance ();
				} catch ( Exception $e ) {
					echo $e->getMessage ();
					die ();
				}
				try {
					$sql = "Select * From " . $conf->getTbl_categoria () . " Order By cat_descripcon";
					$lista = $db->ejecutar ( $sql );
				} catch ( Exception $e ) {
					echo $e->getMessage ();
					die ();
				}
				?>
				<select class="form-control input-sm" name="id_categoria" id="id_categoria">
				<?php
				while ( $fila = mysqli_fetch_array ( $lista ) ) {
					?>
						<option value="<?php echo $fila[0]; ?>"><?php echo $fila[1]; ?></option>
				<?php
				}
				?>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="prov_nombre">Nombre</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_nombre" id="prov_nombre" placeholder="digite nombre" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="prov_razon">Razon</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_razon" id="prov_razon" placeholder="digite razon" />
			</div>
		</div>
		
		<div class="form-group">
			<label class="control-label col-md-2" for="prov_tamanio">Tama&ntilde;o</label>
			<div class="col-md-10">
				<select class="form-control input-sm" name="prov_tamanio" id="prov_tamanio">
					<option value="PEQUE&Ntilde;A">PEQUE&Ntilde;A</option>
					<option value="MEDIANA">MEDIANA</option>
					<option value="GRANDE">GRANDE</option>
				</select>
			</div>
		</div>
		
		<div class="form-group">
			<label class="control-label col-md-2" for="prov_giro">Giro</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_giro" id="prov_giro" placeholder="digite giro de proveedor" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="prov_email">E-mail</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="email" name="prov_email" id="prov_email" placeholder="digite correo electronico" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="prov_nit">NIT</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_nit" id="prov_nit" placeholder="digite NIT" />
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2" for="prov_registro">No. Registro</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_registro" id="prov_registro" placeholder="digite numero de registro" />
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2" for="prov_dias">Dias Credito</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_dias" id="prov_dias" placeholder="dias credito" value="0" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="prov_telefono1">Telefono 1</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_telefono1" id="prov_telefono1" placeholder="digite telefono" />
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2" for="prov_telefono2">Telefono 2</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_telefono2" id="prov_telefono2" placeholder="digite telefono" />
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-md-2" for="prov_fax">FAX</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_fax" id="prov_fax" placeholder="digite fax" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="id_categoria">Contacto</label>
			<div class="col-md-10">
				<input class="form-control input-sm" type="text" name="prov_contacto1" id="prov_contacto1" placeholder="digite contacto" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-md-2" for="id_categoria">Direccion</label>
			<div class="col-md-10">
				<textarea class="form-control input-sm" rows="" cols="" id="prov_direccion1" name="prov_direccion1" placeholder="digite direccion"></textarea>
			</div>
		</div>
		<input type="hidden" value="add" id="accion" name="accion">
		<input type="hidden" value="proveedor" id="tabla" name="tabla">
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
		<input type="hidden" value="1" id="num_pagi" name="num_pagi">
		<div class="form-group">
			<div class="col-md-offset-2 col-md-10">
				<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>
				<input type="reset" value="" style="display: none;">
			</div>
		</div>

	</form>
</div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>