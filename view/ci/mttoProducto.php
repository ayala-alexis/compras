<?php session_start (); ?>
<script>
$(document).ready(function(){
    $("#frmAdd").validate({
	    rules: {
			prod_codigo:{
				required: true
			}
	   },
	   messages: {
			prod_codigo: {
				required: "Digite codigo"
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
						title: 'adicionando..',
						text: 'intentando adicionar registro a tabla, por favor espere...',
						icon: 'glyphicon glyphicon-plus',
						hide: true
					});
					$(":submit").addClass("disabled");
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
								title: 'adicinado',
								text: 'Registro adicionado con exito!.',
								icon: 'glyphicon glyphicon-ok',
								hide: true,
								type: "success"
							});
						    $("#formDiv input[type=reset]").click();
							//$('#myTab li:eq(1) a').tab('show');
							//$("p.form-control-static").text(campos["prod_codigo"].toUpperCase());
							$("#formDiv").dialog("close");
							pagina(campos["num_pagi"],"producto");
						}
					}
					$(":submit").removeClass("disabled");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
					$.pnotify({
						title: 'error',
						text: 'Ocurrio un error durante la ejecucion',
						icon: 'glyphicon glyphicon-ban-circle',
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
		pagina(0, 'producto');
	});
});
</script>
<script>
function pagina(pagi, tabl){
    myUrl = location.protocol + "//" + location.host + '/sics/class/PaginacionProductoCI.php';
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
			$.pnotify({
				title: "error",
				text: "Ocurrio un error durante la ejecucion",
				icon: "glyphicon glyphicon-ban-circle",
				hide: true,
				type: "error"
			});
		}
	});
};
pagina(0,'producto');
</script>
<div id="contenido" class="row"></div>
<div id="alertBoxes"></div>
<div id="dialog-confirm" title="Eliminar Registro?"></div>
<div id="formDiv" style="display: none; overflow: hidden;">
	<ul class="nav nav-tabs" id="myTab">
		<li class="active" id="creaproducto"><a href="#login" data-toggle="tab">Crear</a></li>
	</ul>
	<div id="myTabContent" class="tab-content">
		<div class="tab-pane fade active in" id="login">
			<br><br>
			<form role="form" class="form-horizontal" id="frmAdd" name="frmAdd" method="POST">
	
			<div class="form-group">
				<label class="control-label col-md-2" for="prod_codigo">Codigo</label>
				<div class="col-md-10">
					<input class="form-control input-sm" type="text" name="prod_codigo" id="prod_codigo" placeholder="digite codigo" />
				</div>
			</div>
	
			<input type="hidden" value="add" id="accion" name="accion">
			<input type="hidden" value="ci_producto" id="tabla" name="tabla">
			<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
			<input type="hidden" value="1" id="num_pagi" name="num_pagi">
	
			<div class="form-group">
				<div class="col-md-offset-2 col-md-10">
					<button class="btn btn-sm btn-primary" type="submit" id="btnEnviar" name="btnEnviar"><span class="glyphicon glyphicon-plus"></span> Adicionar</button>
					<button class="btn btn-sm btn-default" type="reset"><span class="glyphicon glyphicon-ban-circle"></span> Limpiar</button>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>