<script>
$(document).ready(function(){  
	$("#waiting").dialog(); 
	$("#editar").validate({
		rules:{
			lis_cant: {
				required: true,
				digits: true
			},
			lis_empaque: {
				required: true,
				digits: true
			},
			lis_precio: {
				required: true,
				digits: true
			},
			lis_fin_vigencia: {
				required: true
			},
			lis_fecha: {
				required: true
			},
			lis_hora: {
				required: true
			}
		},
		submitHandler: function(form) {
			timeSlide=300;
			$('#waiting').show().dialog({
				modal: true,
				width: 'auto',
				height: 'auto',
				closeOnEscape: false 
			});
		    var campos=xajax.getFormValues("editar");			
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
				    $('#waiting').dialog('close');
					if(data == 1){
					    showNotification({
							type : "error",
					        message: data,
					        autoClose: true,
					        duration: 8
						});
					} else {
					    showNotification({
							type : "success",
					        message: "Registro Adicionado.",
					        autoClose: true,
					        duration: 4
						});
					    document.location='?c=prov&a=lista&id='+$("#prov_nombre").val();
					}
					$(":submit").removeAttr("disabled");
				},
				error : function(XMLHttpRequest, textStatus, errorThrown) {
				    $('#waiting').dialog('close');
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
<fieldset>
	<legend>Modificar Item Lista de Proveedor</legend>
	<form class="form-horizontal" name="editar" id="editar" method="post">
	<?php foreach ($row2 as $row3) {?>
		<div class="input-control text">
							
			<label>NOMBRE DEL PROVEEDOR</label> 
			<input type="text" id="prov_nombre" name="prov_nombre" value="<?php echo $row3['prov_nombre']; ?>" disabled>
			
			<label>CODIGO DEL PRODUCTO</label> 
			<input type="text" id="prod_codigo" name="prod_codigo" value="<?php echo $row3['prod_codigo']; ?>" disabled>
			
			<label>CANTIDAD </label> 
			<input type="text" id="lis_cant" name="lis_cant" value="<?php echo $row3['lis_cant']; ?>">
			
			<label>EMPAQUE </label> 
			<input type="text" id="lis_empaque" name="lis_empaque" value="<?php echo $row3['lis_empaque']; ?>">
				
			<label>PRECIO </label> 
			<input type="text" id="lis_precio" name="lis_precio" value="<?php echo $row3['lis_precio']; ?>">
			
			<label>VIGENCIA *</label> 
			<input type="text" id="lis_fin_vigencia" name="lis_fin_vigencia" value="<?php echo $row3['lis_fin_vigencia']; ?>">
				
			<input type="hidden" id="tabla" name="tabla" value="lista"> 
			<input type="hidden" id="accion" name="accion" value="edit"> 
			<input type="hidden" id="id_lista" name="id_lista" value="<?php echo $row3['id_lista']; ?>">
		<?php }?>
		<button class="m-btn blue-stripe" type="submit" id="cambiar">Cambiar</button>
	</form>
</fieldset>
<div id="alertBoxes"></div>
<!-- CARGANDO -->
<div id="waiting" style="display: none;">
	<fieldset>
		<legend>procesando peticion, espere por favor...</legend>
		<img src="css/redmond/images/ajax-loader.gif" />
	</fieldset>
</div>