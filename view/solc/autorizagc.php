<script>
$(function(){
	$("#btnMostrar").live('click', function(){
		var empresa = $("#empresa").val();
		if(empresa != ""){
			window.location.href = "?c=solc&a=gescat&es="+empresa;
		} else {
			jAlert('seleccione empresa', 'Alerta');
		}
	});
	 //$("#btnMostrar").effect( "highlight", {color:"#669966"}, 3000 );
	 $('#btnMostrar').fadeIn(200).fadeOut(200).fadeIn(200).fadeOut(200).fadeIn(200);
});
</script>
<h4 class="text-blue">Listado de Solicitudes Listas para Autorizar por Categoria</h4>
<table class="table table-condensed">
	<tbody>
		<tr>
	  		<td>Categoría</td>
	  		<td>
				<?php $id_categoria = (isset($_GET['es']) ? ($_GET['es']!='' ? $_GET['es'] : 0) : 0);?>
				<select class="form-control input-sm" id="empresa" name="empresa" <?php //if (isset($_GET['es']) && $_GET['es'] != "") echo "disabled"; ?>>
					<?php foreach ($emps as $emp) { ?>
					<option value="<?php echo $emp['id_categoria']; ?>" <?php if (isset($_GET['es']) && $_GET['es'] != "" && $_GET['es'] == $emp['id_categoria']) echo "selected"; ?>><?php echo $emp['nombre_categoria']; ?></option>
					<?php } ?>
				</select>
			</td>
			<td>
				<button id="btnMostrar" name="btnMostrar" class="btn btn-sm btn-primary" type="submit"><i class="glyphicon glyphicon-search"></i> MOSTRAR SOLICITUDES DE CATEGORIA</button>	
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
		<?php $cuantos = 0; 
		?>
		<?php foreach($solcs as $solc) { ?>
		<tr>
			<td><?php echo $solc['prehsol_numero_sol']; ?></td>
			<td><?php echo '( '.$solc['id_cc'].' ) '.$solc['cc_descripcion']; ?></td>
			<td><?php echo $solc['prehsol_usuario']; ?></td>
			<td><?php echo $solc['prehsol_fecha']; ?></td>
			<td><?php echo $solc['prehsol_hora']; ?></td>
			<td>
				<!-- <a class="btn btn-primary btn-sm" href="?c=solc&a=autorizar&id=5&ps=<?php //echo $solc['prehsol_numero']; ?>&cs=<?php //echo $solc['id_cc']; ?>&es=<?php //echo $solc['id_empresa']; ?>">
					autorizar
				</a> -->
				<a class="btn btn-sm btn-warning" href="?c=solc&a=trabajogc&ps=<?php echo $solc['prehsol_numero']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
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