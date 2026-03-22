<?php
$moneda_empresa = $hs->moneda_cia ?? '$';
$estado_sol = (int) ($hs->prehsol_estado ?? 0);
$es_solo_lectura = ($estado_sol != 21); // Solo editable en paso 21
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
            <div class="icono-titulo" style="background-color: #fef2f2; color: #ef4444;"><i
                    class="fas fa-check-double"></i></div>
            <span>Revisar Cotización <span class="text-primary-blue ml-5">#
                    <?= htmlspecialchars($hs->prehsol_numero) ?>
                </span></span>
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
                        <div class="text-dark fs-12 text-bold"><?= htmlspecialchars($hs->emp_nombre) ?></div>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-home mr-4"></i> C.
                            COSTOS</label>
                        <div class="text-dark fs-12 text-bold"><?= htmlspecialchars($hs->cc_descripcion) ?></div>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-tag mr-4"></i>
                            CATEGORÍA</label>
                        <div><span class="badge-cat"
                                style="font-size: 10px; padding: 2px 6px;"><?= htmlspecialchars($hs->cat_descripcion) ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-comment-dots mr-4"></i>
                            OBSERVACIÓN SOLICITANTE</label>
                        <div
                            style="background: #ffffff; padding: 5px 8px; border-radius: 4px; border: 1px dashed #cbd5e1; font-size: 11px; color: #334155; max-height: 40px; overflow-y: auto;">
                            <?= nl2br(htmlspecialchars($hs->prehsol_obs1)) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-15 align-items-end">
            <?php
            $adjuntos = [
                1 => ['ruta' => $hs->prehsol_coti1, 'nombre' => $hs->prehsol_coti1_name, 'titulo' => 'Adjunto 1 (Solicitante)', 'icono' => 'fa-paperclip'],
                2 => ['ruta' => $hs->prehsol_coti2, 'nombre' => $hs->prehsol_coti2_name, 'titulo' => 'Adjunto 2 (Solicitante)', 'icono' => 'fa-paperclip'],
                3 => ['ruta' => $hs->prehsol_coti3, 'nombre' => $hs->prehsol_coti3_name, 'titulo' => 'Adjunto 3 (Solicitante)', 'icono' => 'fa-paperclip'],
                4 => ['ruta' => $hs->prehsol_coti4, 'nombre' => $hs->prehsol_coti4_name, 'titulo' => 'Cuadro Comparativo (Analista)', 'icono' => 'fa-file-excel text-success']
            ];

            foreach ($adjuntos as $i => $adj):
                ?>
                <div class="col-md-3">
                    <div class="form-group m-0">
                        <label class="fs-11 text-bold"><i class="fas <?= $adj['icono'] ?> mr-4"></i>
                            <?= $adj['titulo'] ?></label>
                        <div class="file-card"
                            style="height: 38px; display: flex; width: 100%; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0 10px; border-radius: 8px;">
                            <?php if (!empty($adj['ruta'])):
                                $ext = pathinfo($adj['ruta'], PATHINFO_EXTENSION);
                                $icono_file = ($ext == 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-excel text-success';
                                $nombre_real = !empty($adj['nombre']) ? $adj['nombre'] : $adj['ruta'];
                                ?>
                                <div class="file-info"
                                    style="display: flex; width: 100%; align-items: center; justify-content: flex-start; gap: 8px;">
                                    <i class="fas <?= $icono_file ?> main-icon" style="font-size: 14px; flex-shrink: 0;"></i>
                                    <a href="uploads/compras/<?= htmlspecialchars($adj['ruta']) ?>" target="_blank"
                                        class="file-name nombre-archivo-moderno texto-truncado"
                                        style="color: #1e293b; text-decoration: none; font-size: 11px; flex-grow: 1;"
                                        title="<?= htmlspecialchars($nombre_real) ?>">
                                        <?= htmlspecialchars($nombre_real) ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <span class="text-muted-lighter fs-11">No subido</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <form id="formAprobarCC" novalidate>
            <input type="hidden" id="id_prehsol" name="id_prehsol" value="<?= htmlspecialchars($hs->id_prehsol) ?>">
            <input type="hidden" id="id_categoria_solicitud" value="<?= htmlspecialchars($hs->id_categoria) ?>">

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
                                    <th width="10%">Cant. Autorizada</th>
                                    <th width="20%">Producto</th>
                                    <th width="25%">Proveedor</th>
                                    <th width="10%">Precio (Fijo)</th>
                                    <th width="15%">Observación</th>
                                    <th class="text-right" width="10%">Subtotal</th>
                                    <th class="text-center" width="10%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="listaProductosCC">
                                <?php foreach ($ds as $d):
                                    $precio_val = $d->predsol_prec_uni;
                                    $subtotal_val = number_format($d->predsol_total, 2, '.', '');
                                    ?>
                                    <tr class="fila-cotizacion" data-id="<?= $d->id_predsol ?>"
                                        data-precio="<?= $precio_val ?>">
                                        <td style="vertical-align: middle;">
                                            <input type="number"
                                                class="form-control cant-input text-center text-bold <?= $es_solo_lectura ? 'input-bloqueado' : '' ?>"
                                                step="1" min="1" value="<?= $d->predsol_cantidad ?>" <?= $es_solo_lectura ? 'readonly' : 'required' ?>>
                                        </td>
                                        <td class="fs-11 text-muted-dark" style="vertical-align: middle;"><strong
                                                class="text-dark">
                                                <?= htmlspecialchars($d->prod_codigo) ?>
                                            </strong><br>
                                            <?= htmlspecialchars($d->predsol_descripcion) ?>
                                        </td>
                                        <td style="vertical-align: middle;"><input type="text"
                                                class="form-control fs-11 input-bloqueado texto-truncado"
                                                title="<?= htmlspecialchars($d->prov_nombre) ?>"
                                                value="<?= htmlspecialchars($d->prov_nombre) ?>" readonly></td>
                                        <td style="vertical-align: middle;"><input type="text"
                                                class="form-control text-right input-bloqueado" value="<?= $precio_val ?>"
                                                readonly></td>
                                        <td style="vertical-align: middle;"><input type="text"
                                                class="form-control fs-11 input-bloqueado texto-truncado"
                                                title="<?= htmlspecialchars($d->predsol_observacion) ?>"
                                                value="<?= htmlspecialchars($d->predsol_observacion) ?>" readonly></td>
                                        <td class="text-right text-bold text-primary-blue fs-13"
                                            style="vertical-align: middle;"><span class="simb-mon">
                                                <?= htmlspecialchars($hs->moneda) ?>
                                            </span> <span class="sub-txt">
                                                <?= $subtotal_val ?>
                                            </span></td>
                                        <td class="text-center" style="vertical-align: middle; white-space: nowrap;">
                                            <?php if (!$es_solo_lectura): ?>
                                                <button type="button" class="btn btn-primary btn-sm btn-actualizar-cant"
                                                    title="Actualizar Cantidad" style="border-radius:6px; padding: 4px 10px;"><i
                                                        class="fas fa-sync-alt"></i></button>
                                                <button type="button" class="btn btn-danger btn-sm btn-eliminar-item ml-3"
                                                    title="Eliminar Producto" style="border-radius:6px; padding: 4px 10px;"><i
                                                        class="fas fa-trash-alt"></i></button>
                                            <?php else: ?>
                                                <i class="fas fa-lock text-muted-lighter"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8fafc;">
                                    <td colspan="5" class="text-right text-bold fs-13">NUEVO TOTAL:</td>
                                    <td class="text-right text-extrabold text-primary-blue fs-15"><span
                                            class="simb-mon">
                                            <?= htmlspecialchars($hs->moneda) ?>
                                        </span> <span id="totalGlobal">0.00</span></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-15">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="text-muted-dark"><i class="fas fa-user-edit mr-3"></i> Nota del Analista de
                            Compras</label>
                        <textarea class="form-control input-bloqueado" rows="3"
                            readonly><?= htmlspecialchars($hs->obs_cate ?? 'Sin observaciones del analista.') ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="text-danger text-bold"><i class="fas fa-user-check mr-3"></i> Observación
                            Autorizador Cco. *</label>
                        <textarea id="observacion_aprobador" name="observacion_aprobador"
                            class="form-control <?= $es_solo_lectura ? 'input-bloqueado' : '' ?>" rows="3"
                            placeholder="<?= $es_solo_lectura ? '' : 'Escriba su justificación de aprobación...' ?>"
                            <?= $es_solo_lectura ? 'readonly' : 'required' ?>><?= htmlspecialchars($hs->prehsol_aprobacion ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row barra-acciones">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-default btn-moderno btn-moderno-defecto"
                        onclick="location.href='?c=solc&a=consulta_aprobacion_cc'">
                        <i class="fas fa-arrow-left mr-4"></i> Regresar
                    </button>
                    <?php if (!$es_solo_lectura): ?>
                        <button type="button" class="btn btn-danger btn-moderno ml-5"
                            style="background:#ef4444; color:white; border:none;">
                            <i class="fas fa-ban mr-4"></i> Desistir
                        </button>
                        <button type="submit" class="btn btn-success btn-moderno ml-5"
                            style="background:#10b981; color:white; border:none;">
                            <i class="fas fa-check-circle mr-4"></i> Aprobar Cotización
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>