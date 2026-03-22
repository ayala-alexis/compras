<?php
session_start ();
$_SESSION ['ie'] = $_GET ['ie'];
?>
<script>
$(document).ready(function(){
    $("#frmAdd").validate({
	    rules: {
			bod_descripcion: {
				required: true
			}
	   },
	   messages: {
	     	bod_descripcion: {
		     	required : "Digite Nombre"
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
						$.pnotify({
							title: "adicionado",
							text: "Registro adicionado.",
							icon: "glyphicon glyphicon-ok",
							hide: true,
							type: "success"
						});
					    $("#formDiv input[type=reset]").click();
						$("#formDiv").dialog("close");
						pagina(campos["num_pagi"],"bodega");
					}
					$(":submit").removeClass("disabled");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: "error",
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
});
</script>
<script>
function pagina(pagi, tabl){
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionBodegas.php';
	$.ajax({
		type : 'POST',
		url : myUrl,
		data: {
			tab: tabl,
			pag: pagi
		},
		success : function(data){
			$('#contenido').hide().html(data).show();
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$('#alertBoxes').html('<div id="boxError" class="alert alert-danger"></div>');
			$('#boxError').html('Ha ocurrido un error durante la paginacion.');
		}
	});
};
pagina(0,'bodega');
</script>
<div id="contenido" class="row"></div>
<div id="formDiv" style="display: none; overflow: hidden;">
	<form class="form-horizontal" id="frmAdd" name="frmAdd" method="POST" role="form">
		<div class="form-group">
			<label class="control-label col-md-3" for="bod_descripcion">Nombre de Bodega</label>
			<div class="col-md-9">
				<input class="form-control input-sm" type="text" name="bod_descripcion" id="bod_descripcion" placeholder="digite nombre de bodega" />
			</div>
		</div>
		<input type="hidden" value="add" id="accion" name="accion">
		<input type="hidden" value="bodega" id="tabla" name="tabla">
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
		<input type="hidden" value="<?php echo $_SESSION['ie']; ?>" id="id_empresa" name="id_empresa">
		<input type="hidden" value="1" id="num_pagi" name="num_pagi">
		<div class="form-group">
			<div class="col-md-offset-3 col-md-9">
				<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>
			</div>
		</div>
	</form>
</div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>
<div id="alertBoxes"></div>