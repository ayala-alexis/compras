<fieldset>
	<legend>
	ACCESO USUARIOS <b><?php echo $_GET['id'];?></b>
	</legend>
	<a href="?c=usua&a=adicionar&id=<?php echo $_GET['id']?>" class="m-btn blue-stripe">Adicionar</a>
	<table class="table table-condensed">
		<thead>
			<tr>
				<th>Categoria</th>
				<th>Modulo</th>
				<th>Edita?</th>
				<th>Agrega?</th>
				<th>Elimina?</th>
				<th>Transferencias?</th>
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
				<td><?php echo $value['mod_categoria']; ?></td>
				<td><?php echo $value['mod_descripcion']; ?></td>
				<td><?php echo $value['acc_edit']; ?></td>
				<td><?php echo $value['acc_add']; ?></td>
				<td><?php echo $value['acc_del']; ?></td>
				<td><?php echo $value['acc_xls']; ?></td>
				<td><?php echo $value['acc_fecha']; ?></td>
				<td><?php echo $value['acc_hora']; ?></td>
				<td><?php echo $value['acc_usuario']; ?></td>
				<td>
					<a class="m-btn btn-sm btn-link" href="?c=usua&a=eliminar&id=<?php echo $_GET['id']; ?>&id2=<?php echo $value['id_acceso']; ?>">X</a>
				</td>
			</tr>
		<?php }?>
		</tbody>
	</table>