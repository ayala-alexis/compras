<?php
session_start ();
$_SESSION ['ie'] = $_GET ['ie'];
?>
<script>
$(document).ready(function(){
    $("#frmAdd").validate({
	    rules: {
			gas_tit_codigo: {
				required: true
			},
			gas_det_codigo:{
				required: true
			},
			gas_descripcion: {
				required: true
			}
	   },
	   messages: {
	     	gas_tit_codigo: {
		     	required : "Digite Gasto"
	     	},
	     	gas_det_codigo: {
		     	required : "Digite Gasto"
	     	},
			gas_descripcion: {
				required: "Digite Descripcion"
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
					$(":submit").attr("disabled","disabled");
				},
				success : function(data){
					if(!$.isNumeric(data)){
				    	$.pnotify({
							title: 'error',
							text: data,
							icon: 'glyphicon glyphicon-ban-circle',
							hide: true,
							type: "error"
						});
					} else {
						if(data == 1 ){
							$.pnotify({
								title: 'error',
								text: data,
								icon: 'glyphicon glyphicon-ban-circle',
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
							pagina(campos["num_pagi"],"tagasto");
						}
					}
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
				    showNotification({
						type : "error",
				        message: "Ha ocurrido un error durante la Adicion.",
				        autoClose: true,
				        duration: 2
					});
				    $(":submit").removeAttr("disabled");
				}
			});
	   }
	});
});
</script>
<script>
function pagina(pagi, tabl){
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionTabGas.php';
	$.ajax({
		type : 'POST',
		url : myUrl,
		data: {
			tab: tabl,
			pag: pagi
		},
		success : function(data){
			$('#contenido').html(data);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$('#alertBoxes').html('<div id="boxError" class="alert alert-error"></div>');
			$('#boxError').html('Ha ocurrido un error durante la paginacion.');
		}
	});
};
pagina(0,'tagasto');
</script>
<div id="contenido" class="row-fluid"></div>
<div id="formDiv" style="display: none; overflow: hidden;">
	<fieldset>
		<legend>adicionar tabla de gasto</legend>
		<form role="form" class="form-horizontal" id="frmAdd" name="frmAdd" method="POST">

			<div class="form-group">
				<label class="control-label col-md-2" for="gas_tit_codigo">Titulo</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="gas_tit_codigo" id="gas_tit_codigo" placeholder="digite titulo" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="gas_det_codigo">Detalle</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="gas_det_codigo" id="gas_det_codigo" placeholder="digite detalle" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2" for="gas_descripcion">Descripcion</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="gas_descripcion" id="gas_descripcion" placeholder="digite descripcion" />
				</div>
			</div>

			<input type="hidden" value="add" id="accion" name="accion">
			<input type="hidden" value="tagasto" id="tabla" name="tabla">
			<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
			<input type="hidden" value="0" id="id_empresa" name="id_empresa">
			<input type="hidden" value="1" id="num_pagi" name="num_pagi">

			<div class="form-group">
				<div class="col-md-offset-2 col-md-10">
					<button class="btn btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>
					<input type="reset" value="" style="display: none;">
				</div>
			</div>

		</form>
	</fieldset>
</div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>