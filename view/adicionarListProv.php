<script>
$(document).ready(function(){   
	$("#adicionar").validate({
		rules:{
			prod_codigo: {
				required: true
			},
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
				number: true
			},
			lis_fin_vigencia: {
				required: true
			}
		}
	});
});
</script>
<div id="alertBoxes"></div>
<!-- CARGANDO -->
	<div id="waiting" style="display: none;">
		<fieldset>
			<legend>procesando peticion, espere por favor...</legend>
			<img src="css/redmond/images/ajax-loader.gif" />
		</fieldset>
	</div>
<fieldset>
	<legend>Adicionar Producto a Proveedor</legend>
	<form class="form-horizontal" name="adicionar" id="adicionar" method="post" action="?c=prov&a=adicionar">
	<?php foreach ($row2 as $row3) {?>
		<p>
			<strong>PROVEEDOR:</strong> 
			<?php echo $row3['id_proveedor'].'-'.$row3['prov_nombre']; ?>
		</p>
		
		<label>CODIGO DEL PRODUCTO</label> 
		<input type="text" id="prod_codigo" name="prod_codigo" >

		<label>CANTIDAD </label> 
		<input type="text" id="lis_cant" name="lis_cant" >

		<label>EMPAQUE </label> 
		<input type="text" id="lis_empaque" name="lis_empaque" >

		<label>PRECIO </label>
		<input type="text" id="lis_precio" name="lis_precio" >

		<label>VIGENCIA </label> 
		<input type="text" id="lis_fin_vigencia" name="lis_fin_vigencia">

		<input type="hidden" id="id_proveedor" name="id_proveedor" value="<?php echo $row3['id_proveedor'];?>"> 
		<input type="hidden" id="tabla" name="tabla" value="lista"> 
		<input type="hidden" id="accion" name="accion" value="add"> 
		<input type="hidden" id="id_lista" name="id_lista" value="<?php echo $row3['id_lista']; ?>">
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
	<?php }?>	
		<br/>				
		<button class="m-btn blue-stripe" type="submit" value="Adicionar" name="btnCambiar" id="btnCambiar">Adicionar</button>
		
		
	</form>
</fieldset>