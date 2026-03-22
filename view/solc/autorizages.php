<h4 class="text-blue">Listado de Solicitudes Listas para Autorizar por Convergencia</h4>
<table class="table table-condensed">
	<thead>
		<tr>
			<th>numero</th>
			<th>cc</th>
			<th>usuario</th>
			<th>fecha</th>
			<th>hora</th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php $cuantos = 0; ?>
		<?php foreach($solcs as $solc) { ?>
		<tr>
			<td><?php echo $solc['prehsol_numero']; ?></td>
			<td><?php echo '( '.$solc['id_cc'].' ) '.$solc['cc_descripcion']; ?></td>
			<td><?php echo $solc['prehsol_usuario']; ?></td>
			<td><?php echo $solc['prehsol_fecha']; ?></td>
			<td><?php echo $solc['prehsol_hora']; ?></td>
			<td>
				<!-- <a class="btn btn-primary btn-sm" href="?c=solc&a=autorizar&id=5&ps=<?php //echo $solc['prehsol_numero']; ?>&cs=<?php //echo $solc['id_cc']; ?>&es=<?php //echo $solc['id_empresa']; ?>">
					autorizar
				</a> -->
				<a class="btn btn-sm btn-warning" href="?c=solc&a=trabajoges&ps=<?php echo $solc['prehsol_numero']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
					<i class="glyphicon glyphicon-edit"></i> REVISAR Y AUTORIZAR
				</a>
			</td>
		</tr>
		<?php $cuantos++; ?>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="6">
				<?php echo $cuantos.' '; ?>  solicitud(es) pendiente(s)
			</th>
		</tr>
	</tfoot>
</table>