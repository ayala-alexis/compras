<?php
$moneda_empresa = $hs->moneda_cia ?? '$';
$estado_sol = (int) ($hs->estado_oc ?? $hs->prehsol_estado ?? 0);
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

<div class="container container-crear w-100" id="vistaVerOC">
    <input type="hidden" id="id_prehsol" value="<?= htmlspecialchars($hs->id_prehsol) ?>">

    <div class="tarjeta-formulario">
        <h2 class="titulo-moderno mb-15" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center;">
                <div class="icono-titulo" style="background-color: #e0f2fe; color: #0284c7; margin-right: 15px;"><i
                        class="fas fa-eye"></i></div>
                <span>Visualización de OC <span
                        class="text-primary-blue ml-5">OC-<?= htmlspecialchars($hs->predsol_numero_oc) ?></span></span>
            </div>
            <div>
                <a href="?c=solc&a=generar_oc&id=<?= htmlspecialchars($hs->id_prehsol) ?>" target="_blank"
                    class="btn btn-default btn-sm"
                    style="border-radius: 6px; border-color: #cbd5e1; color: #475569; font-weight: 600; display: flex; align-items: center;">
                    <i class="fas fa-external-link-alt mr-4" style="color: #3b82f6;"></i> Ref: Sol#
                    <?= htmlspecialchars($hs->id_prehsol) ?>
                </a>
            </div>
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
                    <div class="col-md-3">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-building mr-4"></i>
                            EMPRESA FACTURAR</label>
                        <div class="text-dark fs-12 text-bold"><?= htmlspecialchars($hs->emp_nombre) ?></div>
                    </div>
                    <div class="col-md-5">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-truck mr-4"></i>
                            PROVEEDOR ASIGNADO</label>
                        <div class="text-primary-blue fs-12 text-bold"><?= htmlspecialchars($hs->prov_nombre) ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-comment-dots mr-4"></i>
                            NOTA DE OC (Analista)</label>
                        <div
                            style="background: #ffffff; padding: 5px 8px; border-radius: 4px; border: 1px dashed #cbd5e1; font-size: 11px; color: #334155; max-height: 40px; overflow-y: auto;">
                            <?= nl2br(htmlspecialchars($hs->prehsol_oc_obs ?? 'Sin nota adicional.')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default border-none shadow-sm">
            <div class="panel-heading"
                style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
                <strong><i class="fas fa-list-ul mr-4"></i> Detalle de la Orden de Compra</strong>
            </div>
            <div class="panel-body p-0">
                <div class="table-responsive m-0" style="overflow: visible;">
                    <table class="table-moderna m-0" style="width: 100%;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th width="8%" class="text-center">Cant.</th>
                                <th width="15%">C. Costos</th>
                                <th width="27%">Producto / Descripción</th>
                                <th width="20%">Observación</th>
                                <th class="text-right" width="15%">Precio Unit.</th>
                                <th class="text-right" width="15%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ds as $d):
                                $subtotal_val = number_format($d->predsol_total, 2, '.', ''); ?>
                                <tr>
                                    <td class="text-center text-bold" style="vertical-align: middle;">
                                        <?= $d->predsol_cantidad ?></td>
                                    <td class="fs-11 text-muted-dark" style="vertical-align: middle;">
                                        <?= htmlspecialchars($d->cc_descripcion ?? 'N/A') ?></td>
                                    <td class="fs-11 text-muted-dark" style="vertical-align: middle;">
                                        <strong class="text-dark"><?= htmlspecialchars($d->prod_codigo) ?></strong><br>
                                        <?= htmlspecialchars($d->predsol_descripcion) ?>
                                    </td>
                                    <td class="fs-11 text-muted-dark" style="vertical-align: middle;">
                                        <?= htmlspecialchars($d->predsol_observacion) ?></td>
                                    <td class="text-right fs-12 text-muted-dark" style="vertical-align: middle;">
                                        <span class="simb-mon"><?= htmlspecialchars($hs->moneda) ?></span>
                                        <?= number_format($d->predsol_prec_uni, 2) ?>
                                    </td>
                                    <td class="text-right text-bold text-primary-blue fs-13"
                                        style="vertical-align: middle;">
                                        <span class="simb-mon"><?= htmlspecialchars($hs->moneda) ?></span>
                                        <?= $subtotal_val ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: #fef3c7;">
                                <td colspan="5" class="text-right text-bold fs-13" style="color: #d97706;">TOTAL ORDEN
                                    DE COMPRA:</td>
                                <td class="text-right text-extrabold fs-15" style="color: #b45309;">
                                    <span class="simb-mon"><?= htmlspecialchars($hs->moneda) ?></span>
                                    <?= number_format($hs->monto_oc, 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="row mt-15 align-items-end">
            <div class="col-md-12">
                <div class="form-group m-0">
                    <label class="text-bold fs-12" style="color: #d97706;"><i class="fas fa-clipboard-check mr-3"></i>
                        Observación Jefe de Compras</label>
                    <textarea class="form-control input-bloqueado" rows="2"
                        readonly><?= htmlspecialchars($hs->predsol_obs_jefe ?? 'Aprobada sin observación.') ?></textarea>
                </div>
            </div>
        </div>

        <div class="row barra-acciones mt-15">
            <div class="col-md-12 text-right">
                <button type="button" class="btn btn-default btn-moderno btn-moderno-defecto"
                    onclick="location.href='?c=solc&a=consulta_oc'">
                    <i class="fas fa-arrow-left mr-4"></i> Regresar a Bandeja
                </button>
            </div>
        </div>
    </div>
</div>