<script>
$(function(){
	$("#btnMostrar").live('click', function(){
		var empresa = $("#empresa").val();
		if(empresa != ""){
			window.location.href = "?c=req&a=autoriza&id=6&es="+empresa;
		} else {
			jAlert('seleccione empresa', 'Alerta');
		}
	});
});
</script>
<h4 class="text-blue">Listado de Requisiciones Listas para Autorizar</h4>
<table class="table table-condensed">
	<tbody>
		<tr>
	  		<td>Empresa</td>
	  		<td>
				<div class="form-group">
					<select class="form-control input-sm" id="empresa" name="empresa" <?php //if (isset($_GET['es']) && $_GET['es'] != "") echo "disabled"; ?>>
						<?php foreach ($emps as $emp) { ?>
						<option value="<?php echo $emp['id_empresa']; ?>" <?php if (isset($_GET['es']) && $_GET['es'] != "" && $_GET['es'] == $emp['id_empresa']) echo "selected"; ?>><?php echo $emp['emp_nombre']; ?></option>
						<?php } ?>
					</select>
				</div>
			</td>
			<td>
				<button id="btnMostrar" name="btnMostrar" class="btn btn-sm btn-primary" type="submit"><i class="glyphicon glyphicon-search"></i> Mostrar</button>
			</td>
		</tr>
	</tbody>
</table>
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
			<td><?php echo $solc['prehreq_numero']; ?></td>
			<td><?php echo '( '.$solc['cc_codigo'].' ) '.$solc['cc_descripcion']; ?></td>
			<td><?php echo $solc['prehreq_usuario']; ?></td>
			<td><?php echo $solc['prehreq_fecha']; ?></td>
			<td><?php echo $solc['prehreq_hora']; ?></td>
			<td>
				<a class="btn btn-sm btn-success" href="?c=req&a=crear&id=6&ps=<?php echo $solc['prehreq_numero']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
					<i class="glyphicon glyphicon-edit"></i>Autorizar
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