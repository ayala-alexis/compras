<?php
session_start ();
?>
<script>
$(document).ready(function(){
    $("#frmAdd").validate({
		rules: {
			cat_descripcon: {
				required: true
			}
		},
		messages: {
	     	cat_descripcon: {
		     	required : "Digite nombre"
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
					$(":submit").addClass("disabled");
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
								text: "Registro adicionado",
								icon: "glyphicon glyphicon-ok",
								hide: true,
								type: "success"
							});
						    $("#formDiv input[type=reset]").click();
							$("#formDiv").dialog("close");
							pagina(campos["num_pagi"],"categoria");
						}
					}
					$(":submit").removeClass("disabled");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: "error...",
						text: "Error en la ejecucion",
						icon: "glyphicon glyphicon-ban-circle",
						hide: true,
						type: "error"
					});
				    $(":submit").removeClass("disabled");
				}
			});
	   }
	});
	// boton buscar
	$('#btnBuscar').live('click', function(){
		pagina(0, 'categoria');
	});
});
</script>
<script>
function pagina(pagi, tabl){
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionCatProv.php';
	$.ajax({
		type : 'POST',
		url : myUrl,
		data: {
			tab: tabl,
			pag: pagi,
			filtro: $.trim($('#filtro').val())
		},
		success : function(data){
			$('#contenido').hide().html(data).show();
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$('#alertBoxes').html('<div id="boxError" class="alert alert-error"></div>');
			$('#boxError').html('Ha ocurrido un error durante la ejecucion.');
		}
	});
};
pagina(0,'categoria');
</script>
<div id="contenido" class="row-fluid">
</div>
<div id="formDiv" style="display: none; overflow: hidden;">
	<fieldset>
		<legend>Adicionar Categoria</legend>
		<form role="form" id="frmAdd" name="frmAdd" method="POST">

			<div class="form-group">
				<label class="control-label" for="bod_descripcion">Descripcion</label>
				<input class="form-control input-sm" type="text" name="cat_descripcon" id="cat_descripcon" placeholder="digite nombre de categoria" />
			</div>

			<input type="hidden" value="add" id="accion" name="accion">
			<input type="hidden" value="categoria" id="tabla" name="tabla">
			<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
			<input type="hidden" value="1" id="num_pagi" name="num_pagi">
			<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>
			<button type="reset" style="display: none"></button>
		</form>
	</fieldset>
</div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>