<?php
session_start ();
?>
<script>
$(document).ready(function(){
    $("#frmAdd").validate({
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
	       		required: "Digite contrase�a"
	     	},
	     	usr_password2: {
		     	required: "Digite contrase�a",
		     	equalTo: "Contrase�as no son iguales"
	     	},
	     	id_rol: {
		     	required: "Seleccion Rol"
	     	}
	   },
		submitHandler: function(form) {
			$('.ui-dialog-titlebar').hide();
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
					$("input[type=submit]").attr("disabled","disabled");
				},
				success : function(data){
					if(data == 1){
					    showNotification({
							type : "error",
					        message: data,
					        autoClose: true,
					        duration: 2
					} else {
					    showNotification({
							type : "success",
					        message: "Registro Adicionado.",
					        autoClose: true,
					        duration: 2
						});
					    $("#formDiv input[type=reset]").click();
						$("#formDiv").dialog("close");
						pagina(campos["num_pagi"],"usuario");
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
				    showNotification({
						type : "error",
				        message: "Ha ocurrido un error durante la ejecucion.",
				        autoClose: true,
				        duration: 2
					});
				}
			});
	   }
	});
});
</script>
<script>
function pagina(pagi, tabl){
	$('.ui-dialog-titlebar').hide();
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionUsuario.php';
	$.ajax({
		type : 'POST',
		url : myUrl,
		data: {
			tab: tabl,
			pag: pagi
		},
		beforeSend: function(){
			$('input[type="submit"]').attr('disabled','disabled');
			$('.ui-dialog-titlebar').hide();
		},
		success : function(data){
			$('#contenido').html(data);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$('#alertBoxes').html('<div id="boxError" class="alert alert-error"></div>');
			$('#boxError').html('Ha ocurrido un error durante la ejecucion.');
		}
	});
};
pagina(0,'usuario');
</script>
<div id="contenido" class="row-fluid"></div>
<div id="formDiv" style="display: none;">
	<fieldset>
		<legend>Adicionar Usuario</legend>

		<form class="form-horizontal" id="frmAdd" name="frmAdd" method="POST">

			<div class="control-group">
				<label class="control-label" for="usr_usuario">Usuario</label>
				<div class="controls">
					<input type="text" name="usr_usuario" id="usr_usuario" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="usr_nombre">Nombre</label>
				<div class="controls">
					<input type="text" name="usr_nombre" id="usr_nombre" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="usr_password">Contrase�a</label>
				<div class="controls">
					<input type="password" name="usr_password" id="usr_password" />
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="usr_password2">Verificar Contrase�a</label>
				<div class="controls">
					<input type="password" name="usr_password2"	id="usr_password2" />
				</div>
			</div>
			<?php
			$db = DB::getInstance ();
			$conf = Configuracion::getInstance ();
			$sqlRol = "Select * From " . $conf->getTbl_rol () . " Where id_rol > 1";
			$RolExec = $db->ejecutar ( $sqlRol );
			?>
			<div class="control-group">
				<label class="control-label" for="id_rol">Rol de Usuario</label>
				<div class="controls">
					<select name="id_rol" id="id_rol">
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
			<input type="hidden" value="1" id="num_pagi" name="num_pagi">

			<div class="control-group">
				<div class="controls">
					<button class="m-btn blue-stripe" type="submit" id="btnEnviar" name="btnEnviar">Adicionar</button>
					<input type="reset" value="" style="display: none;">
				</div>
			</div>
		</form>
	</fieldset>
</div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>