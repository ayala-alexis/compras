<?php
$moneda_empresa = $hs->moneda_cia ?? '$';
$estado_sol = (int) ($hs->estado ?? 0);
$es_solo_lectura = ($estado_sol != 31); // Solo editable en paso 31
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    crossorigin="anonymous" />

<style>
    .texto-truncado {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .input-bloqueado {
        background-color: #f8fafc !important;
        cursor: not-allowed;
        border-color: #e2e8f0 !important;
        color: #64748b !important;
    }
</style>

<div class="container container-crear w-100">
    <div class="tarjeta-formulario">

        <h2 class="titulo-moderno mb-15">
            <div class="icono-titulo" style="background-color: #ede9fe; color: #8b5cf6;"><i class="fas fa-tags"></i>
            </div>
            <span>Revisar por Categoría <span
                    class="text-primary-blue ml-5">#<?= htmlspecialchars($hs->numero_solicitud ?? '') ?></span></span>
        </h2>

        <div class="panel panel-default border-none mb-15 p-0">
            <div class="panel-body p-0">
                <div class="tracker-wrapper" style="padding: 10px;">
                    <div class="tracker" id="tracker-contenedor"></div>
                </div>
            </div>
        </div>

        <div class="panel panel-default border-none mb-15" style="background-color: #f8fafc; border-radius: 8px;">
            <div class="panel-body" style="padding: 10px 15px;">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-building mr-4"></i>
                            EMPRESA</label>
                        <div class="text-dark fs-12 text-bold"><?= htmlspecialchars($hs->emp_nombre ?? '') ?></div>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-home mr-4"></i> C.
                            COSTOS</label>
                        <div class="text-dark fs-12 text-bold"><?= htmlspecialchars($hs->cc_descripcion ?? '') ?></div>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-tag mr-4"></i>
                            CATEGORÍA</label>
                        <div><span class="badge-cat"
                                style="font-size: 10px; padding: 2px 6px; background: #ede9fe; color: #6d28d9;"><?= htmlspecialchars($hs->cat_descripcion ?? '') ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-comment-dots mr-4"></i>
                            OBSERVACIÓN SOLICITANTE</label>
                        <div
                            style="background: #ffffff; padding: 5px 8px; border-radius: 4px; border: 1px dashed #cbd5e1; font-size: 11px; color: #334155; max-height: 40px; overflow-y: auto;">
                            <?= nl2br(htmlspecialchars($hs->observacion_crea ?? '')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-15 align-items-end">
            <?php
            $adj_list = ['S1' => null, 'S2' => null, 'S3' => null, 'CC' => null];
            if (!empty($adjuntos_db)) {
                foreach ($adjuntos_db as $adj) {
                    $adj_list[$adj->adjunto_tipo] = ['ruta' => $adj->ruta, 'nombre' => $adj->nombre_archivo];
                }
            }

            for ($i = 1; $i <= 3; $i++):
                $tipo = 'S' . $i;
                $tiene_adjunto = !empty($adj_list[$tipo]);
                ?>
                <div class="col-md-3">
                    <div class="form-group m-0">
                        <label class="fs-11 text-bold"><i class="fas fa-paperclip mr-4"></i> Adjunto <?= $i ?>
                            (Sol.)</label>
                        <div class="file-card"
                            style="height: 38px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0 10px; border-radius: 8px;">
                            <?php if ($tiene_adjunto):
                                $ext = pathinfo($adj_list[$tipo]['ruta'], PATHINFO_EXTENSION);
                                $icono = (strtolower($ext) == 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-excel text-success';
                                ?>
                                <div class="file-info"
                                    style="display: flex; width: 100%; align-items: center; justify-content: flex-start; gap: 8px;">
                                    <i class="fas <?= $icono ?> main-icon" style="font-size: 14px; flex-shrink: 0;"></i>
                                    <a href="uploads/compras/<?= htmlspecialchars($adj_list[$tipo]['ruta']) ?>" target="_blank"
                                        class="file-name nombre-archivo-moderno texto-truncado"
                                        style="color: #1e293b; text-decoration: none; font-size: 11px; flex-grow: 1;"
                                        title="<?= htmlspecialchars($adj_list[$tipo]['nombre']) ?>">
                                        <?= htmlspecialchars($adj_list[$tipo]['nombre']) ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="text-muted-lighter fs-11">No subido</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

            <div class="col-md-3">
                <div class="form-group m-0">
                    <label class="fs-11 text-bold" style="display: block; margin-bottom: 4px;"><i
                            class="fas fa-file-excel mr-4 text-success"></i> Cuadro Comparativo</label>
                    <div class="file-card"
                        style="height: 38px; display: flex; width: 100%; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0 10px; border-radius: 8px;">
                        <?php if (!empty($adj_list['CC'])):
                            $ext4 = pathinfo($adj_list['CC']['ruta'], PATHINFO_EXTENSION);
                            $icono4 = (strtolower($ext4) == 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-excel text-success';
                            ?>
                            <div class="file-info"
                                style="display: flex; width: 100%; align-items: center; justify-content: flex-start; gap: 8px;">
                                <i class="fas <?= $icono4 ?> main-icon" style="font-size: 14px; flex-shrink: 0;"></i>
                                <a href="uploads/compras/<?= htmlspecialchars($adj_list['CC']['ruta']) ?>" target="_blank"
                                    class="file-name nombre-archivo-moderno texto-truncado"
                                    style="color: #1e293b; text-decoration: none; font-size: 11px; flex-grow: 1;"
                                    title="<?= htmlspecialchars($adj_list['CC']['nombre']) ?>">
                                    <?= htmlspecialchars($adj_list['CC']['nombre']) ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <span class="text-muted-lighter fs-11">No subido</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <form id="formAprobarCategoria" novalidate>
            <input type="hidden" id="id_prehsol" name="id_prehsol" value="<?= htmlspecialchars($hs->id ?? '') ?>">

            <div class="panel panel-default border-none shadow-sm">
                <div class="panel-heading"
                    style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
                    <strong><i class="fas fa-list-ul mr-4"></i> Detalle de cotización a revisar</strong>
                </div>
                <div class="panel-body p-0">
                    <div class="table-responsive m-0" style="overflow: visible;">
                        <table class="table-moderna m-0" style="width: 100%;">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th width="10%" class="text-center">Cant.</th>
                                    <th width="20%">Producto</th>
                                    <th width="25%">Proveedor</th>
                                    <th width="10%">Precio (Fijo)</th>
                                    <th width="15%">Observación</th>
                                    <th class="text-right" width="10%">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="listaProductosCategoria">
                                <?php foreach ($ds as $d):
                                    $precio_val = (($d->precio_unitario ?? 0) > 0) ? number_format($d->precio_unitario, 2, '.', '') : '0.00';
                                    $subtotal_val = number_format($d->subtotal ?? 0, 2, '.', '');
                                    ?>
                                    <tr class="fila-cotizacion" data-id="<?= htmlspecialchars($d->id ?? '') ?>"
                                        data-precio="<?= htmlspecialchars($precio_val) ?>">
                                        <td style="vertical-align: middle;">
                                            <input type="number"
                                                class="form-control cant-input text-center text-bold input-bloqueado"
                                                value="<?= htmlspecialchars($d->cantidad ?? 1) ?>" readonly>
                                        </td>
                                        <td class="fs-11 text-muted-dark" style="vertical-align: middle;">
                                            <strong
                                                class="text-dark"><?= htmlspecialchars($d->codigo_producto ?? '') ?></strong><br>
                                            <?= htmlspecialchars($d->descripcion_producto ?? '') ?>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <input type="text" class="form-control fs-11 input-bloqueado texto-truncado"
                                                title="<?= htmlspecialchars($d->prov_nombre ?? '') ?>"
                                                value="<?= htmlspecialchars($d->prov_nombre ?? '') ?>" readonly>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <input type="text" class="form-control text-right input-bloqueado"
                                                value="<?= htmlspecialchars($precio_val) ?>" readonly>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <input type="text" class="form-control fs-11 input-bloqueado texto-truncado"
                                                title="<?= htmlspecialchars($d->observacion_analista ?? '') ?>"
                                                value="<?= htmlspecialchars($d->observacion_analista ?? '') ?>" readonly>
                                        </td>
                                        <td class="text-right text-bold text-primary-blue fs-13"
                                            style="vertical-align: middle;">
                                            <span
                                                class="simb-mon"><?= htmlspecialchars($hs->moneda ?? $moneda_empresa) ?></span>
                                            <span class="sub-txt"><?= $subtotal_val ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8fafc;">
                                    <td colspan="5" class="text-right text-bold fs-13">GRAN TOTAL:</td>
                                    <td class="text-right text-extrabold text-primary-blue fs-15"><span
                                            class="simb-mon"><?= htmlspecialchars($hs->moneda ?? $moneda_empresa) ?></span>
                                        <span id="totalGlobal">0.00</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-15">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted-dark fs-11"><i class="fas fa-user-edit mr-3"></i> Nota Analista</label>
                        <textarea class="form-control input-bloqueado fs-11" rows="2"
                            readonly><?= htmlspecialchars($hs->observacion_cotiza ?? 'Sin observaciones.') ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted-dark fs-11"><i class="fas fa-user-check mr-3"></i> Nota Jefe
                            Cco.</label>
                        <textarea class="form-control input-bloqueado fs-11" rows="2"
                            readonly><?= htmlspecialchars($hs->observacion_cco ?? 'Sin observaciones.') ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-primary-blue text-bold fs-11"><i class="fas fa-tags mr-3"></i> Observación
                            Categoría *</label>
                        <textarea id="observacion_categoria" name="observacion_categoria"
                            class="form-control fs-11 <?= $es_solo_lectura ? 'input-bloqueado' : '' ?>" rows="2"
                            placeholder="<?= $es_solo_lectura ? '' : 'Justificación técnica...' ?>" <?= $es_solo_lectura ? 'readonly' : 'required' ?>><?= htmlspecialchars($hs->observacion_categoria ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row barra-acciones">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-default btn-moderno btn-moderno-defecto"
                        onclick="location.href='?c=solicitud&a=consulta_aprobacion_categoria'">
                        <i class="fas fa-arrow-left mr-4"></i> Regresar
                    </button>
                    <?php if (!$es_solo_lectura): ?>
                        <button type="button" class="btn btn-danger btn-moderno ml-5"
                            style="background:#ef4444; color:white; border:none;">
                            <i class="fas fa-ban mr-4"></i> Rechazar
                        </button>
                        <button type="submit" class="btn btn-success btn-moderno ml-5"
                            style="background:#8b5cf6; color:white; border:none;">
                            <i class="fas fa-check-circle mr-4"></i> Aprobar Técnicamente
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>