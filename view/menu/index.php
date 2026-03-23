<?php
$categoria = "";
$subcategoria = "";
$modulo_link = array('?c=req&a=inicio&id=6', '?c=solicitud&a=crear');
$modulo_acc = array();
foreach ($perfil->modulos as $modulo):
	if ($modulo->url == '?c=solc&a=inicio&id=5') {
		$modulo->url = '?c=solicitud&a=crear';
	}
	if (in_array($modulo->url, $modulo_link)):
		$modulo_acc[] = (object) array(
			'name' => ($modulo->url == '?c=req&a=inicio&id=6' ? 'Requisición de Suministro' : ($modulo->url == '?c=ci&a=inicio&id=12' ? 'Consumo Interno' : 'De Compra')),
			'link' => $modulo->url
		);
	endif;
endforeach;
$modulo_acc[] = (object) array(
	'name' => 'De Cheque',
	'link' => '?c=solcheque&a=crear'
);
?>

<link rel="stylesheet" type="text/css" href="css/sics.css?v=<?php echo date('His'); ?>" />
<h4 class="title">
	<i class="glyphicon glyphicon-user"></i> Bienvenido(a) <b><?php echo $perfil->usuario_nombre ?></b>.
</h4>
<div class="row">
	<div class="col-sm-6">
		<a class="btn btn-default btn-lg" href="?c=menu&a=consulta">
			<i class="glyphicon glyphicon-folder-open"></i> &nbsp;
			Consultar Histórico
		</a>
	</div>
	<div class="col-sm-6" style="text-align: center;">
		<?php if ($perfil->rol == 2): ?>
				<a class="btn btn-primary btn-lg" href="?c=menu&a=autorizarcc">
					Pendientes de Autorizar
					<span class="badge"><?php echo $contador_pendiente_autorizar; ?></span>
				</a>
		<?php endif; ?>
		&nbsp;
		<div class="btn-group btn-grupo">
			<button type="button" class="btn btn-success btn-lg dropdown-toggle" data-toggle="dropdown"
				aria-haspopup="true" aria-expanded="false">
				<i class="glyphicon glyphicon-pencil" style="padding: 0;margin:0;font-size: 14px;"></i> Agregar
				Solicitud
			</button>
			<button type="button" class="btn btn-success btn-lg dropdown-toggle" data-toggle="dropdown"
				aria-haspopup="true" aria-expanded="false">
				<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu">
				<?php foreach ($modulo_acc as $mod): ?>
						<li>
							<a href="<?php echo $mod->link ?>">
								<i class="glyphicon glyphicon-plus-sign"></i> &nbsp;
								<?php echo $mod->name ?>
							</a>
						</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
<br />
<div class="row">

	<?php if ((int) $perfil->rol == 2): ?>
			<table class="table tbl-kpi">
				<thead>
					<tr>
						<th colspan="7"><b style="font-size: 14px;"><u>Últimas solicitudes pendientes de autorizar de
									Cco.</u></b></th>
					</tr>
					<tr>
						<th style="width: 60px">No. <br />Solicitud</th>
						<th style="min-width: 180px">Cco.</th>
						<th style="min-width: 280px">Descrición</th>
						<th style="min-width: 150px;">Estado</th>
						<th style="min-width: 110px;">Creación</th>
						<th colspan="2" style="width: 350px;">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($data_sol['n2'])):
						$actu = "";
						$color1 = "tsol1";
						$color2 = "tsol2";
						$color = "tsol1";
						foreach ($data_sol['n2'] as $sol):
							if ($actu != $sol->tipo):
								if ($color == 'tsol1'):
									$color = 'tsol2';
								else:
									$color = 'tsol1';
								endif;
							endif;
							$actu = $sol->tipo;
							?>
									<tr class="tsol <?php echo $sol->tipo ?> <?php echo $color ?>">
										<td>
											<?php echo str_pad($sol->correlativo, 6, '0', STR_PAD_LEFT); ?>
										</td>
										<td style="font-size:8px;">
											(<span class="ccs"><?php echo str_pad($sol->cc_codigo, 2, ' ', STR_PAD_LEFT); ?></span>)
											<?php echo $sol->cc_descripcion; ?><br />
											(<span class="ccs"><?php echo str_pad($sol->id_empresa, 2, ' ', STR_PAD_LEFT); ?></span>)
											<?php echo $sol->nombre_empresa; ?>
										</td>
										<td>
											<?php echo $sol->observacion; ?>
											<br />
											<b style='min-width:160px;display:inline-block;'>
												<?php if ($sol->tipo == 'cheque'): ?>
														&nbsp;<span
															style="color: #222;background-color: #b5ceea;padding: 2px;font-size: 11px;border-radius: 2px;border: 1px #7db3f1 solid;"><?php echo $sol->moneda . number_format($sol->monto, 2, '.', ',') ?></span>
												<?php endif ?>
											</b>
										</td>
										<td>
											<u><b><?php echo strtoupper(get_type_sol($sol->tipo)) ?>:</b></u> <br />
											<?php echo status_solicitud($sol->tipo, $sol->estado, $sol->requiere_recepcion) ?>
										</td>
										<td>
											<b><i class="glyphicon glyphicon-user"></i> <?php echo $sol->usuario ?></b> <br />
											<?php
											echo Help::formatDateN($sol->fecha_creado) . " " . Help::formatTimeShortN($sol->hora_creado);
											?>
										</td>
										<td>
											<?php
											$pdf = "http://intranet.impressa.com/report/?s=" . $sol->id;
											$link_det = "?c=solcheque&a=detalle&s=" . $sol->id . "&return=index";
											$is_cheque_upload = '';
											if ($sol->tipo == 'ci' || $sol->tipo == 'req'):
												$pdf = "view/" . $sol->tipo . "/pdf.php?id=" . $sol->id;
												$link_det = "?c=" . $sol->tipo . "&a=crear&id=" . ($sol->tipo == 'ci' ? 12 : 6) . "&ps=" . $sol->correlativo . "&cs=" . $sol->id_cc . "&es=" . $sol->id_empresa;
											elseif ($sol->tipo == 'sol'):
												$pdf = "view/solc/pdf.php?id=" . $sol->id;
												$link_det = "?c=solc&a=crear&id=5&ps=" . $sol->correlativo . "&cs=" . $sol->id_cc . "&es=" . $sol->id_empresa;
											else:
												$is_cheque_upload = is_cheque_file($sol->id);
											endif;
											?>
											<?php if ($sol->tipo == 'cheque'): ?>
													<a href="<?php echo $pdf; ?>" class="btn btn-default" target="_blank">
														<i class="icon-print"></i>
													</a>
											<?php endif; ?>
										</td>
										<td>
											<a href="<?php echo $link_det; ?>&return=<?php echo get_action(); ?>" class="btn btn-sm btn-warning"
												title="Visualizar solicitud">
												<i class="glyphicon glyphicon-pencil" style="padding: 0;margin:0;font-size: 14px;"></i>
												Revisar
											</a>
											<?php if ($is_cheque_upload != ''): ?>
													<a target="_blank" href="<?php echo $is_cheque_upload ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
											<?php if (is_file(safe_utf_decode($sol->file_1))): ?>
													<a target="_blank" href="uploads/<?php echo basename($sol->file_1) ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
											<?php if (is_file(safe_utf_decode($sol->file_2))): ?>
													<a target="_blank" href="uploads/<?php echo basename($sol->file_2) ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
											<?php if (is_file(safe_utf_decode($sol->file_3))): ?>
													<a target="_blank" href="uploads/<?php echo basename($sol->file_3) ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
											<?php if (is_file(safe_utf_decode($sol->file_4))): ?>
													<a target="_blank" href="uploads/<?php echo basename($sol->file_4) ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
										</td>
									</tr>
								<?php
						endforeach;
					else:
						echo "<tr><td colspan=7><h3 style='font-size:13px;text-align:center;'>No hay solicitud(es) para mostrar.</h3></td></tr>";
					endif; ?>
				</tbody>
			</table>
	<?php endif; ?>

	<?php if ((int) $perfil->rol == 1 || (int) $perfil->rol == 2): ?>
			<table class="table tbl-kpi">
				<thead>
					<tr>
						<th colspan="7"><b style="font-size: 14px;"><u>Últimas solicitudes creadas</u></b></th>
					</tr>
					<tr>
						<th style="width: 60px">No. <br />Solicitud</th>
						<th style="min-width: 180px">Cco.</th>
						<th style="min-width: 350px">Descrición</th>
						<th style="min-width: 140px;">Estado</th>
						<th style="min-width: 110px;">Creación</th>
						<th colspan="2" style="width: 110px;">Acciones</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($data_sol['n1'])):
						$actu = "";
						$color1 = "tsol1";
						$color2 = "tsol2";
						$color = "tsol1";
						foreach ($data_sol['n1'] as $sol):
							if ($actu != $sol->tipo):
								if ($color == 'tsol1'):
									$color = 'tsol2';
								else:
									$color = 'tsol1';
								endif;
							endif;
							$actu = $sol->tipo;
							?>
									<tr class="tsol <?php echo $sol->tipo ?> <?php echo $color ?>">
										<td>
											<?php echo str_pad($sol->correlativo, 6, '0', STR_PAD_LEFT); ?>
										</td>
										<td>
											(<span class="ccs"><?php echo str_pad($sol->cc_codigo, 2, ' ', STR_PAD_LEFT); ?></span>)
											<?php echo $sol->cc_descripcion; ?><br />
											(<span class="ccs"><?php echo str_pad($sol->id_empresa, 2, ' ', STR_PAD_LEFT); ?></span>)
											<?php echo $sol->nombre_empresa; ?>
										</td>
										<td>
											<?php echo $sol->observacion; ?>
											<br />
											<b style='min-width:160px;display:inline-block;'>
												<?php if ($sol->tipo == 'cheque'): ?>
														&nbsp;<span
															style="color: #222;background-color: #b5ceea;padding: 2px;font-size: 11px;border-radius: 2px;border: 1px #7db3f1 solid;"><?php echo $sol->moneda . number_format($sol->monto, 2, '.', ',') ?></span>
												<?php endif ?>
											</b>
										</td>
										<td>
											<b><u><?php echo strtoupper(get_type_sol($sol->tipo)) ?>: </u></b><br />
											<?php if ($sol->tipo == 'cheque' && $sol->estado == 'N5-E'): ?>
													RECIBIDO
											<?php else:
												echo status_solicitud($sol->tipo, $sol->estado, $sol->requiere_recepcion);
											endif; ?>
										</td>
										<td>
											<b><i class="glyphicon glyphicon-user"></i> <?php echo $sol->usuario ?></b>
											<br />
											<i class="glyphicon glyphicon-time"></i>
											<?php
											echo Help::formatDateN($sol->fecha_creado) . " " . Help::formatTimeShortN($sol->hora_creado);
											?>
										</td>
										<td>
											<?php
											$pdf = "http://intranet.impressa.com/report/?s=" . $sol->id;
											$link_det = "?c=solcheque&a=detalle&s=" . $sol->id . "&return=index";
											$is_cheque_upload = '';
											if ($sol->tipo == 'ci' || $sol->tipo == 'req'):
												$pdf = "view/" . $sol->tipo . "/pdf.php?id=" . $sol->id;
												$link_det = "?c=" . $sol->tipo . "&a=crear&id=" . ($sol->tipo == 'ci' ? 12 : 6) . "&ps=" . $sol->correlativo . "&cs=" . $sol->id_cc . "&es=" . $sol->id_empresa;
											elseif ($sol->tipo == 'sol'):
												$link_det = "?c=solc&a=crear&id=5&ps=" . $sol->correlativo . "&cs=" . $sol->id_cc . "&es=" . $sol->id_empresa;
												$pdf = "view/solc/pdf.php?id=" . $sol->id;
											else:
												$is_cheque_upload = is_cheque_file($sol->id);
											endif;
											?>
											<a href="<?php echo $pdf; ?>" class="btn btn-default" target="_blank">
												<i class="icon-print"></i>
											</a>
										</td>
										<td>
											<a href="<?php echo $link_det; ?>&return=<?php echo get_action(); ?>" class="btn btn-sm btn-default"
												title="Visualizar solicitud">
												<i class="glyphicon glyphicon-search"></i>
											</a>
											<?php if ($is_cheque_upload != ''): ?>
													<a target="_blank" href="<?php echo $is_cheque_upload ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
											<?php if (is_file(safe_utf_decode($sol->file_1))): ?>
													<a target="_blank" href="uploads/<?php echo basename($sol->file_1) ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
											<?php if (is_file(safe_utf_decode($sol->file_2))): ?>
													<a target="_blank" href="uploads/<?php echo basename($sol->file_2) ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
											<?php if (is_file(safe_utf_decode($sol->file_3))): ?>
													<a target="_blank" href="uploads/<?php echo basename($sol->file_3) ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
											<?php if (is_file(safe_utf_decode($sol->file_4))): ?>
													<a target="_blank" href="uploads/<?php echo basename($sol->file_4) ?>" class="btn btn-sm btn-default"
														title="Adjunto">
														<i class="glyphicon glyphicon-file"></i>
													</a>
											<?php endif; ?>
										</td>
									</tr>
								<?php
						endforeach;
					else:
						echo "<tr><td colspan=7><h3 style='font-size:13px;text-align:center;'>No hay solicitud(es) para mostrar.</h3></td></tr>";
					endif; ?>
				</tbody>
			</table>
	<?php endif; ?>

</div>