<?php
session_start ();
$_SESSION ['ie'] = $_GET ['ie'];
?>
<script>
$(document).ready(function(){
	jQuery.fn.reset = function () {
		  $(this).each (function() { this.reset(); });
	};
    $("#frmAdd").validate({
	    rules: {
			cc_codigo: {
				required: true
			},
			cc_descripcion:{
				required: true
			}
	   },
	   messages: {
	     	cc_codigo: {
		     	required : "Digite Codigo"
	     	},
			cc_descripcion: {
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
					$("input[type=submit]").attr("disabled","disabled");
					$.pnotify({
						title: 'adicionando..',
						text: 'intentando adicionar registro a tabla, por favor espere...',
						type: 'info',
						icon: 'icon-plus',
						hide: true,
						addclass: "stack-bottomright",
						stack: stack_bottomright
					});
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
								title: 'adicionado',
								text: 'adicionado con exito',
								type: 'success',
								icon: 'glyphicon glyphicon-ok',
								hide: true
							});
						    $("#formDiv input[type=reset]").click();
							$("#formDiv").dialog("close");
							pagina(campos["num_pagi"],"cecosto");
							$('#frmAdd').reset();
						}
					}
					$(":submit").removeAttr("disabled");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: 'ha ocurrido un error..',
						text: 'ha ocurrido un error durante la adicion.'+data,
						type: 'error',
						icon: 'icon-exclamation-sign',
						hide: true,
						addclass: "stack-bar-bottom",
						cornerclass: "",
				        width: "70%",
				        stack: stack_bar_bottom
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
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionCeCosto.php';
	$.ajax({
		type : 'POST',
		url : myUrl,
		data: {
			tab: tabl,
			pag: pagi
		},
		beforeSend: function(){
			$('input[type="submit"]').attr('disabled','disabled');
		},
		success : function(data){
			$('#contenido').html(data);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			$('#alertBoxes').html('<div id="boxError" class="alert alert-danger"></div>');
			$('#boxError').html('Ha ocurrido un error durante la ejecucion.'+textStatus);
		}
	});
};
pagina(0,'cecosto');
</script>
<div id="contenido" class="row-fluid"></div>
<div id="formDiv" style="display: none; overflow: hidden;">
		<form class="form-horizontal" id="frmAdd" name="frmAdd" method="POST" role="form">

			<div class="form-group">
				<label class="control-label col-md-2">codigo</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="cc_codigo" id="cc_codigo" placeholder="digite codigo de centro de costo" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-md-2">descripcion</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="cc_descripcion" id="cc_descripcion" placeholder="digite nombre" />
				</div>
			</div>

			<div class="form-group">
				<div class="col-md-offset-2 col-md-10">
					<input type="hidden" value="add" id="accion" name="accion">
					<input type="hidden" value="cecosto" id="tabla" name="tabla">
					<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
					<input type="hidden" value="<?php echo $_SESSION['ie']; ?>" id="id_empresa" name="id_empresa">
					<input type="hidden" value="1" id="num_pagi" name="num_pagi">
					<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>
					<button type="reset" style="display: none;"></button>
				</div>
			</div>
		</form>
</div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>