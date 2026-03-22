<script>
$(document).ready(function(){   
	$("#adicionar").validate({
		rules:{

			mod_descripcion: {
				required: true
			}
			
		}
	});
});
</script>
<div id="alertBoxes">
</div>
<!-- CARGANDO -->
	<div id="waiting" style="display: none;">
		<fieldset>
			<legend>procesando peticion, espere por favor...</legend>
			<img src="css/redmond/images/ajax-loader.gif" />
		</fieldset>
	</div>
<fieldset>
	<legend>AGREGAR ACCESO A USUARIO</legend>
	<form name="adicionar" id="adicionar" method="post" action="?c=usua&a=adicionar&id=<?php echo $_GET['id']; ?>">
		
		
		<label>MODULO</label> 
		<select id="comboBox" name="comboBox">
			
			
			<?php 
	
				$db=DB::getInstance();
				$db = DB::getInstance ();
				$conf = Configuracion::getInstance ();
				$sql2 = "select id_modulo , mod_descripcion from ".$conf->getTbl_modulo();
				$run = $db->ejecutar ( $sql2 );
					while($row=mysqli_fetch_array($run)){
						echo '<option value="'.$row["id_modulo"].'"> '.$row["mod_descripcion"].'</option>';
						
					}	
			?>
		</select>		
		<br/>
		<input type="checkbox" id="acc_edit" name="acc_edit" ><span> EDITA?   </span> 	
		
		<input type="checkbox" id="acc_add" name="acc_add" ><span> AGREGA?   </span>
	
		<input type="checkbox" id="acc_del" name="acc_del"><span> ELIMINA?   </span>
	 
		<input type="checkbox" id="acc_xls" name="acc_xls"><span> TRANSFERENCIAS?  </span>
			
		<input type="hidden" id="tabla" name="tabla" value="acc_modulo"> 
		<input type="hidden" id="accion" name="accion" value="add">  
		<input type="hidden" id="mod_descripcion" name="mod_descripcion" value="<?php echo $row['mod_descripcion']; ?>"> 
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
		<br/><br/>						
		<button class="m-btn blue-stripe" type="submit" name="btnCambiar" id="btnCambiar">Adicionar</button>
		
	</form>
</fieldset>