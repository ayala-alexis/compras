<fieldset>
	<legend>
		LISTA DE PRECIOS PARA PROVEEDOR <b><?php echo $_GET['id'];?></b>
	</legend>
	
	<form class="form-inline" id="buscar" name="buscar" method="post" action="?c=prov&a=buscar">
	<input type="text" id="busqueda" name="busqueda"> <a class="m-btn blue-stripe" href="?c=prov&a=buscar&id=<?php echo $_GET['id']; ?>" class="button">Buscar</a>
	<input type="hidden" id="tabla" name="tabla" value="lista"> 
	<input type="hidden" id="accion" name="accion" value="edit"> 
	</form>
	<a class="m-btn blue-stripe" href="?c=prov&a=adicionar&id=<?php echo $_GET['id']; ?>" class="button">Adicionar</a>
	<table class="table table-condensed">
		<thead>
			<tr>
				<!-- <th>id_lista</th>
				<th>id_proveedor</th> -->
				<th>Codigo</th>
				<th>Cantidad</th>
				<th>Empaque</th>
				<th>Precio</th>
				<th>Fin Vigencia</th>
				<th>Fecha</th>
				<th>Hora</th>
				<th>Usuario</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $filas as $value ) {
			?>
			<tr>
			<!--	<td><?php //echo $value['id_lista']; ?></td>
				<td><?php //echo $value['id_proveedor']; ?></td> -->
				<td><?php echo $value['prod_codigo']; ?></td>
				<td><?php echo $value['lis_cant']; ?></td>
				<td><?php echo $value['lis_empaque']; ?></td>
				<td><?php echo $value['lis_precio']; ?></td>
				<td><?php echo $value['lis_fin_vigencia']; ?></td>
				<td><?php echo $value['lis_fecha']; ?></td>
				<td><?php echo $value['lis_hora']; ?></td>
				<td><?php echo $value['lis_usuario']; ?></td>
				<td><a class="m-btn btn-sm" href="?c=prov&a=modificar&id=<?php echo $value['id_lista']; ?>" class="button">Editar</a></td>
				<td><a class="m-btn btn-sm" href="?c=prov&a=eliminar&id=<?php echo $value['id_lista']; ?>" class="button">X</a></td>
			</tr>
		<?php }?>
		</tbody>
	</table>
</fieldset>