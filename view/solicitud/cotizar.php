<?php
$moneda_empresa = $hs->moneda_cia ?? '$';
$es_moneda_dual = ($moneda_empresa === 'L' || $moneda_empresa === 'C$');

// 🏗️ MAPEO NUEVA TABLA: 'estado'
$estado_sol = (int) ($hs->estado ?? 0);
$es_solo_lectura = ($estado_sol >= 21);

// 🏗️ MAPEO NUEVA TABLA: auditoría
$analista_nombre = !empty($hs->usuario_cotiza) ? htmlspecialchars($hs->usuario_cotiza) : htmlspecialchars($_SESSION['n'] ?? $_SESSION['u'] ?? 'Analista en sesión');
$fecha_analista = !empty($hs->fecha_cotiza) ? date('d/m/Y h:i A', strtotime($hs->fecha_cotiza . ' ' . ($hs->hora_cotiza ?? '00:00:00'))) : date('d/m/Y h:i A');
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

        <h2 class="titulo-moderno mb-15" style="width: 100%; display: flex; align-items: center;">
            <div class="icono-titulo" style="flex-shrink: 0;"><i class="fas fa-file-invoice-dollar"></i></div>
            <span style="flex-shrink: 0;">
                Cotizar Solicitud <span class="text-primary-blue ml-5">#
                    <?= htmlspecialchars($hs->numero_solicitud ?? '') ?>
                </span>
                <?php if ($es_solo_lectura): ?>
                    <span class="badge ml-10"
                        style="background: #ef4444; font-size: 11px; padding: 4px 8px; vertical-align: middle;"><i
                            class="fas fa-lock mr-3"></i> Solo Lectura</span>
                <?php endif; ?>
            </span>

            <?php if (!$es_solo_lectura): ?>
                <div
                    style="margin-left: auto; text-align: right; display: flex; flex-direction: column; justify-content: center; line-height: 1.2;">
                    <span style="font-size: 12px; color: #475569; font-weight: 600;"><i
                            class="fas fa-user-edit mr-3 text-primary-blue"></i>Analista: <span class="text-dark">
                            <?= $analista_nombre ?>
                        </span></span>
                    <span style="font-size: 10px; color: #94a3b8; font-weight: 600; margin-top: 3px;"><i
                            class="far fa-clock mr-3"></i>Act:
                        <?= $fecha_analista ?>
                    </span>
                </div>
            <?php endif; ?>
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
                        <div class="text-dark fs-12 text-bold">
                            <?= htmlspecialchars($hs->emp_nombre ?? '') ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-home mr-4"></i> C.
                            COSTOS</label>
                        <div class="text-dark fs-12 text-bold">
                            <?= htmlspecialchars($hs->cc_descripcion ?? '') ?>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="text-muted-lighter fs-11 text-bold m-0"><i class="fas fa-tag mr-4"></i>
                            CATEGORÍA</label>
                        <div><span class="badge-cat" style="font-size: 10px; padding: 2px 6px;">
                                <?= htmlspecialchars($hs->cat_descripcion ?? '') ?>
                            </span></div>
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

        <form id="formCotizacion" novalidate>
            <input type="hidden" id="id_prehsol" name="id_prehsol" value="<?= htmlspecialchars($hs->id ?? '') ?>">
            <input type="hidden" id="id_empresa_solicitud" value="<?= htmlspecialchars($hs->id_empresa ?? '') ?>">
            <input type="hidden" id="id_categoria_solicitud" value="<?= htmlspecialchars($hs->id_categoria ?? '') ?>">

            <div class="row mb-15 align-items-end">
                <?php
                // 🏗️ MAPEO A LA NUEVA TABLA RELACIONAL DE ADJUNTOS
                $adjuntos = ['S1' => null, 'S2' => null, 'S3' => null, 'CC' => null];
                if (!empty($adjuntos_db)) {
                    foreach ($adjuntos_db as $adj) {
                        $adjuntos[$adj->adjunto_tipo] = [
                            'ruta' => $adj->ruta,
                            'nombre' => $adj->nombre_archivo
                        ];
                    }
                }

                // 1. Dibujamos los 3 adjuntos del Solicitante (S1, S2, S3)
                for ($i = 1; $i <= 3; $i++):
                    $tipo = 'S' . $i;
                    $tiene_adjunto = !empty($adjuntos[$tipo]);
                    ?>
                    <div class="col-md-3">
                        <div class="form-group m-0">
                            <label class="fs-11 text-bold"><i class="fas fa-paperclip mr-4"></i> Adjunto
                                <?= $i ?> (Solicitante)
                            </label>
                            <div class="file-card"
                                style="height: 38px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0 10px; border-radius: 8px;">
                                <?php if ($tiene_adjunto):
                                    $ext = pathinfo($adjuntos[$tipo]['ruta'], PATHINFO_EXTENSION);
                                    $icono = (strtolower($ext) == 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-excel text-success';
                                    ?>
                                    <div class="file-info"
                                        style="display: flex; width: 100%; align-items: center; justify-content: flex-start; gap: 8px;">
                                        <i class="fas <?= $icono ?> main-icon" style="font-size: 14px; flex-shrink: 0;"></i>
                                        <a href="uploads/compras/<?= htmlspecialchars($adjuntos[$tipo]['ruta']) ?>"
                                            target="_blank" class="file-name nombre-archivo-moderno texto-truncado"
                                            style="color: #1e293b; text-decoration: none; font-size: 11px; flex-grow: 1;"
                                            title="<?= htmlspecialchars($adjuntos[$tipo]['nombre']) ?>">
                                            <?= htmlspecialchars($adjuntos[$tipo]['nombre']) ?>
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
                                class="fas fa-file-excel mr-4 text-success"></i> Cuadro Comparativo (Analista
                            compras)</label>

                        <?php if (!$es_solo_lectura): ?>
                            <input type="file" id="adj_comp" name="adj_comp" class="file-input-hidden"
                                accept=".pdf, .xlsx, .xls">
                            <label for="adj_comp" class="file-card has-dropzone"
                                style="display: flex; width: 100%; height: 38px; border: 2px dashed #cbd5e1; background-color: #f8fafc; border-radius: 8px; cursor: pointer; align-items: center; padding: 0 10px;">
                                <div class="placeholder-info"
                                    style="width: 100%; display: flex; align-items: center; justify-content: center;">
                                    <p class="text-title fs-11 m-0"><i class="fas fa-cloud-upload-alt mr-4"></i> Subir
                                        adjunto</p>
                                </div>
                                <div class="file-info"
                                    style="display: none; width: 100%; align-items: center; justify-content: space-between; gap: 8px;">
                                    <i class="fas fa-file-excel text-success main-icon"
                                        style="font-size:14px; flex-shrink: 0;"></i>
                                    <span class="nombre-archivo-moderno texto-truncado"
                                        style="font-size: 11px; flex-grow: 1;">...</span>
                                    <button type="button" class="remove-file quitar-archivo-moderno"
                                        style="padding:0; flex-shrink: 0; background: none; border: none; font-size: 16px; color: #64748b;">&times;</button>
                                </div>
                            </label>
                        <?php else: ?>
                            <div class="file-card"
                                style="height: 38px; display: flex; width: 100%; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0 10px; border-radius: 8px;">
                                <?php if (!empty($adjuntos['CC'])):
                                    $ext4 = pathinfo($adjuntos['CC']['ruta'], PATHINFO_EXTENSION);
                                    $icono4 = (strtolower($ext4) == 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-excel text-success';
                                    ?>
                                    <div class="file-info"
                                        style="display: flex; width: 100%; align-items: center; justify-content: flex-start; gap: 8px;">
                                        <i class="fas <?= $icono4 ?> main-icon" style="font-size: 14px; flex-shrink: 0;"></i>
                                        <a href="uploads/compras/<?= htmlspecialchars($adjuntos['CC']['ruta']) ?>"
                                            target="_blank" class="file-name nombre-archivo-moderno texto-truncado"
                                            style="color: #1e293b; text-decoration: none; font-size: 11px; flex-grow: 1;"
                                            title="<?= htmlspecialchars($adjuntos['CC']['nombre']) ?>">
                                            <?= htmlspecialchars($adjuntos['CC']['nombre']) ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted-lighter fs-11">No requerido / No subido</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="detalleProductosSection">
                <div class="panel panel-default border-none shadow-sm">
                    <div class="panel-heading"
                        style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
                        <strong><i class="fas fa-list-ul mr-4"></i> Detalle de cotización</strong>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <label class="fs-11 text-bold m-0" style="color: #475569;">Moneda:</label>
                            <?php if ($es_moneda_dual && !$es_solo_lectura): ?>
                                <select name="moneda" id="moneda_cot" class="form-control"
                                    style="height: 28px; padding: 2px 8px; font-size: 12px; width: 80px;">
                                    <option value="<?= $moneda_empresa ?>" <?= (($hs->moneda ?? '') == $moneda_empresa) ? 'selected' : '' ?>>
                                        <?= $moneda_empresa ?>
                                    </option>
                                    <option value="$" <?= (($hs->moneda ?? '') == '$') ? 'selected' : '' ?>>$ USD</option>
                                </select>
                            <?php else: ?>
                                <input type="text" name="moneda" class="form-control text-center text-bold input-bloqueado"
                                    value="<?= !empty($hs->moneda) ? htmlspecialchars($hs->moneda) : '$' ?>" readonly
                                    style="height: 28px; font-size: 12px; width: 60px; padding: 2px;">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="panel-body p-0">
                        <div class="table-responsive m-0" style="overflow: visible;">
                            <table class="table-moderna m-0" style="width: 100%;">
                                <thead style="background: #f8fafc;">
                                    <tr>
                                        <th class="text-center" width="5%">Cant</th>
                                        <th width="20%">Producto</th>
                                        <th width="25%">Proveedor *</th>
                                        <th width="10%">Precio *</th>
                                        <th width="20%">Observación</th>
                                        <th class="text-right" width="10%">Subtotal</th>
                                        <th class="text-center" width="10%">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ds as $i => $d):
                                        // 🏗️ MAPEO A NUEVAS COLUMNAS (compras_det)
                                        $precio_val = (($d->precio_unitario ?? 0) > 0) ? number_format($d->precio_unitario, 2, '.', '') : '';
                                        $subtotal_val = (($d->subtotal ?? 0) > 0) ? number_format($d->subtotal, 2, '.', '') : '0.00';
                                        $prov_id_val = !empty($d->id_proveedor) ? $d->id_proveedor : '';
                                        $prov_nombre_val = !empty($d->prov_nombre) ? htmlspecialchars($d->prov_nombre) : '';
                                        $obs_val = !empty($d->observacion_analista) ? htmlspecialchars($d->observacion_analista) : '';
                                        ?>
                                        <tr class="fila-cotizacion" data-id="<?= htmlspecialchars($d->id ?? '') ?>"
                                            data-cantidad="<?= htmlspecialchars($d->cantidad ?? '') ?>">
                                            <td class="text-center text-bold fs-13" style="vertical-align: middle;">
                                                <?= htmlspecialchars($d->cantidad ?? '') ?>
                                            </td>
                                            <td class="fs-11 text-muted-dark" style="vertical-align: middle;">
                                                <strong class="text-dark">
                                                    <?= htmlspecialchars($d->codigo_producto ?? '') ?>
                                                </strong><br>
                                                <?= htmlspecialchars($d->descripcion_producto ?? '') ?>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <div style="position: relative; width: 100%;">
                                                    <input type="text"
                                                        class="form-control proveedor-search fs-11 <?= $es_solo_lectura ? 'input-bloqueado' : '' ?>"
                                                        placeholder="<?= $es_solo_lectura ? 'N/A' : 'Buscar en AS400...' ?>"
                                                        <?= $es_solo_lectura ? 'readonly' : 'required' ?> autocomplete="off"
                                                        value="<?= $prov_nombre_val ?>">
                                                    <input type="hidden" name="item[<?= $i ?>][prov_cod]"
                                                        class="proveedor-id-hidden" <?= $es_solo_lectura ? '' : 'required' ?>
                                                        value="<?= $prov_id_val ?>">
                                                    <input type="hidden" name="item[<?= $i ?>][id_predsol]"
                                                        value="<?= htmlspecialchars($d->id ?? '') ?>">
                                                    <?php if (!$es_solo_lectura): ?>
                                                        <ul class="list-group product-results contenedor-resultados"></ul>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <input type="number" name="item[<?= $i ?>][precio]"
                                                    class="form-control precio-input text-right <?= $es_solo_lectura ? 'input-bloqueado' : '' ?>"
                                                    step="0.01" min="0.01" placeholder="0.00" <?= $es_solo_lectura ? 'readonly' : 'required' ?> 
                                                    value="<?= $precio_val ?>">
                                            </td>
                                            <td style="vertical-align: middle;">
                                                <input type="text" name="item[<?= $i ?>][observacion]"
                                                    class="form-control obs-item fs-11 <?= $es_solo_lectura ? 'input-bloqueado' : '' ?>"
                                                    placeholder="<?= $es_solo_lectura ? '' : 'Opcional...' ?>"
                                                    <?= $es_solo_lectura ? 'readonly' : '' ?> value="
                                            <?= $obs_val ?>">
                                            </td>
                                            <td class="text-right text-bold text-primary-blue fs-13"
                                                style="vertical-align: middle;">
                                                <span class="simb-mon">
                                                    <?= !empty($hs->moneda) ? htmlspecialchars($hs->moneda) : $moneda_empresa ?>
                                                </span>
                                                <span class="sub-txt">
                                                    <?= $subtotal_val ?>
                                                </span>
                                            </td>
                                            <td class="text-center" style="vertical-align: middle;">
                                                <?php if (!$es_solo_lectura): ?>
                                                    <button type="button" class="btn btn-primary btn-sm btn-guardar-item"
                                                        title="Actualizar ítem" style="border-radius:6px; padding: 4px 10px;"><i
                                                            class="fas fa-sync-alt"></i> Guardar</button>
                                                <?php else: ?>
                                                    <i class="fas fa-lock text-muted-lighter" title="Bloqueado por sistema"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background: #f8fafc;">
                                        <td colspan="5" class="text-right text-bold fs-13">TOTAL COTIZADO:</td>
                                        <td class="text-right text-extrabold text-primary-blue fs-15"><span
                                                class="simb-mon">
                                                <?= !empty($hs->moneda) ? htmlspecialchars($hs->moneda) : $moneda_empresa ?>
                                            </span>
                                            <span id="totalGlobal">0.00</span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-15">
                <div class="col-md-12">
                    <div class="form-group m-0">
                        <label for="observacion_analista"><i class="fas fa-edit mr-4"></i> Observación general del
                            Analista *</label>
                        <textarea id="observacion_analista" name="obs_analista"
                            class="form-control <?= $es_solo_lectura ? 'input-bloqueado' : '' ?>" rows="2"
                            placeholder="<?= $es_solo_lectura ? '' : 'Ingrese justificación general...' ?>"
                            <?= $es_solo_lectura ? 'readonly' : 'required' ?>><?= htmlspecialchars($hs->observacion_cotiza ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="row barra-acciones">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-default btn-moderno btn-moderno-defecto"
                        onclick="location.href='?c=solicitud&a=consulta_cotizacion'">
                        <i class="fas fa-arrow-left mr-4"></i> Regresar
                    </button>
                    <?php if (!$es_solo_lectura): ?>
                        <button type="button" class="btn btn-danger btn-moderno ml-5"
                            style="background:#ef4444; color:white; border:none;">
                            <i class="fas fa-ban mr-4"></i> Desistir
                        </button>
                        <button type="submit" class="btn btn-primary btn-moderno btn-moderno-primario ml-5">
                            <i class="fas fa-paper-plane mr-4"></i> Enviar Aprobación
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>