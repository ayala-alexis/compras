
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
	<form name="buscar" id="buscar" method="post" action="?c=cc&a=buscar">
		
		
		<label>Busqueda por Descripcion</label> 
		<select id="combo" name="combo">
			
			
			<?php 
	
				$db=DB::getInstance();
				$conf= Configuracion::getInstance ();
				$sql= "select id_cc,id_empresa,cc_descripcion from " . $conf->getTbl_cecosto(). " where id_empresa = 2" ;
				$run = $db->ejecutar($sql);
				while($row=mysqli_fetch_array($run)){
					echo '<option value="'.$row["id_cc"].'"> '.$row["cc_descripcion"].'</option>';
				}

				
			?>
		</select>		
		
			
		<input type="hidden" id="tabla" name="tabla" value="cecosto"> 
		<input type="hidden" id="accion" name="accion" value="add">   
		<input type="hidden" id="cc_descripcion" name="cc_descripcion" value="edit">   
		<input type="hidden" value="<?php echo $_SESSION['u']; ?>" id="usr_crea" name="usr_crea">
		<br/><br/>						
		<button class="m-btn blue-stripe" type="submit" name="btnCambiar" id="btnCambiar">Ver</button>
	</form>
</fieldset>

<table class="table table-condensed">
		<thead>
			<tr>
				<th>Codigo</th>
				<th>Descripcion</th>
				
			</tr>
		</thead>
		<tbody>
		<?php
		include 'model/empQryCentroCosto.php';
		$row= buscarCC();
		foreach ( $row as $value ) {
			?>
			<tr>
				<td><?php echo $value['cc_codigo']; ?></td>
				<td><?php echo $value['cc_descripcion']; ?></td>

				<td>
					<a class="m-btn btn-sm btn-link" href="?c=emp&a=cc&ie=">X</a>
				</td>
			</tr>
		<?php }?>
		</tbody>
	</table>
