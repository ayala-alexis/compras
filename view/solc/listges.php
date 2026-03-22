<script>
	$(function () {
		$("a").popover();
		$('a.btn-danger').on('click', function (e) {
			var link = $(this).attr('href');
			$('<div></div>').appendTo('body')
				.html('<div><h6>Si or No?</h6></div>')
				.dialog({
					modal: true,
					title: 'Negar Solicitud',
					zIndex: 10000,
					autoOpen: true,
					width: 'auto',
					resizable: false,
					buttons: {
						Yes: {
							text: "Rechazar",
							click: function () {
								window.location = link;
							}
						},
						No: function () {
							$(this).dialog("close");
						}
					},
					close: function (event, ui) {
						$(this).remove();
					}
				});
			e.preventDefault();
			return false;
		});

		function doFunctionForYes() {
			alert("Yes");
		}

		function doFunctionForNo() {
			alert("No");
		}
	});
</script>
<h4 class="text-blue">Listado de Solicitudes Para Gestion</h4>
<div class="panel panel-danger">
	<div class="panel-heading">
		<h4 class="text-center">NUEVAS SOLICITUDES</h4>
	</div>
	<div class="table-responsive">
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>Numero</th>
					<th>Centro Costo</th>
					<th>Fecha Creacion</th>
					<th>Fecha Recibe<br>Proveeduria</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php $cuantos = 0; ?>
				<?php
				foreach ($solcs as $solc) {
					if ($solc['prehsol_estado'] == 4) {
						?>
						<tr>
							<td><?php echo $solc['prehsol_numero_sol']; ?></td>
							<td>
								<?php echo '( ' . $solc['id_empresa'] . ' ) ' . $solc['emp_nombre']; ?><br />
								<?php echo '( ' . $solc['cc_codigo'] . ' ) ' . $solc['cc_descripcion']; ?>
							</td>
							<td><?php echo date('d/m/Y h:i:s a', strtotime($solc['prehsol_fecha'] . " " . $solc["prehsol_hora"])); ?>
							</td>
							<td>
								<?php
								if ($solc['prehsol_ingreso_compra'] !== null) {
									echo date('d/m/Y h:i:s a', strtotime($solc['prehsol_ingreso_compra']));
								}
								?>
								<?php
								if (isset($_GET['depurar'])):
									echo "<br/>" . $solc['id_prehsol'];
								endif;
								?>
							</td>
							<td>
								<!-- <a href="#" class="btn btn-sm btn-default" data-placement="left" rel="popover" title="estado de solicitud" data-html="true" data-content="<?php echo $solc['estados']; ?>">
							<i class="glyphicon glyphicon-eye-open"></i>&nbsp;<?php echo $conf->getEstadoSC($solc['prehsol_estado']); ?>
						</a> -->
								<a class="btn btn-sm btn-danger"
									href="?c=solc&a=deny&id=5&ps=<?php echo $solc['id_prehsol']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
									<i class="glyphicon glyphicon-remove"></i>&nbsp;Rechazar
								</a>
								<a class="btn btn-sm btn-success"
									href="?c=solc&a=trabajo&id=5&ps=<?php echo $solc['prehsol_numero']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
									<i class="glyphicon glyphicon-envelope"></i>&nbsp; Gestionar
								</a>
							</td>
							<?php
							echo "<td style='color:orange;font-weight:bold;font-size: 11px;'>" . $solc['devuelto'] . '</td>';

							?>
						</tr>
						<?php $cuantos++; ?>
					<?php }
				} ?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="8">
						<?php echo $cuantos . ' '; ?> solicitud(es) pendiente(s)
					</th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

<div class="panel panel-info">
	<div class="panel-heading">
		<h4 class="text-center">SOLICITUDES EN PROCESO</h4>
	</div>
	<div class="table-responsive">
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>Numero</th>
					<th>Centro Costo</th>
					<!-- <th>Fecha Creacion</th> -->
					<th>Fecha Recibe<br>Proveeduria</th>
					<th>Visto</th>
					<th>En Revision</th>
					<th>Acciones</th>
				</tr>
			</thead>
			<tbody>
				<?php $cuantos = 0; ?>
				<?php
				foreach ($solcs as $solc) {
					if ($solc['prehsol_estado'] == 5 || $solc['prehsol_estado'] == 12) {
						?>
						<tr>
							<td><?php echo $solc['prehsol_numero_sol']; ?></td>
							<td>
								<?php echo '( ' . $solc['id_empresa'] . ' ) ' . $solc['emp_nombre']; ?><br />
								<?php echo '( ' . $solc['cc_codigo'] . ' ) ' . $solc['cc_descripcion']; ?>
							</td>
							<!-- <td><?php //echo date('d/m/Y h:i:s a', strtotime($solc['prehsol_fecha']." ".$solc["prehsol_hora"])); ?></td>-->
							<td>
								<?php
								if ($solc['prehsol_ingreso_compra'] !== null) {
									echo date('d/m/Y h:i:s a', strtotime($solc['prehsol_ingreso_compra']));
								}
								?>
								<?php
								if (isset($_GET['depurar'])):
									echo "<br/>" . $solc['id_prehsol'];
								endif;
								?>
							</td>
							<td><?php
							if ($solc['prehsol_revision_fecha'] !== null) {
								echo $solc['prehsol_revision'] . '<br />' . date('d/m/Y h:i:s a', strtotime($solc['prehsol_revision_fecha']));
							}
							?>
							</td>
							<td><?php echo $solc['prehsol_revisando']; ?></td>
							<td>
								<!-- <a href="#" class="btn btn-sm btn-default" data-placement="left" rel="popover" title="estado de solicitud" data-html="true" data-content="<?php echo $solc['estados']; ?>">
							<i class="glyphicon glyphicon-eye-open"></i>&nbsp;<?php echo $conf->getEstadoSC($solc['prehsol_estado']); ?>
						</a> -->
								<a class="btn btn-sm btn-danger"
									href="?c=solc&a=deny&id=5&ps=<?php echo $solc['id_prehsol']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
									<i class="glyphicon glyphicon-remove"></i>&nbsp;Rechazar
								</a>
								<a class="btn btn-sm btn-success"
									href="?c=solc&a=trabajo&id=5&ps=<?php echo $solc['prehsol_numero']; ?>&cs=<?php echo $solc['id_cc']; ?>&es=<?php echo $solc['id_empresa']; ?>">
									<i class="glyphicon glyphicon-envelope"></i>&nbsp; Gestionar
								</a>
							</td>
							<?php
							echo "<td style='color:orange;font-weight:bold;font-size: 11px;'>" . $solc['devuelto'] . '</td>';
							?>
						</tr>
						<?php $cuantos++; ?>
					<?php }
				} ?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="8">
						<?php echo $cuantos . ' '; ?> solicitud(es) pendiente(s)
					</th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>