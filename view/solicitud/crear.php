<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    crossorigin="anonymous" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<div class="container container-crear">
    <div class="tarjeta-formulario">

        <h2 class="titulo-moderno">
            <div class="icono-titulo"><i class="fas fa-cart-plus"></i></div>
            Adicionar solicitud de compras
        </h2>

        <form id="purchaseRequestForm" novalidate>

            <div class="row mb-10">
                <div class="col-md-4">
                    <div class="form-group m-0">
                        <label for="empresa"><i class="fas fa-building mr-4"></i> Empresa *</label>
                        <select id="empresa" name="empresa" class="form-control select2-busqueda" required>
                            <option value="">Seleccione Empresa</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group m-0">
                        <label for="centroCostos"><i class="fas fa-home mr-4"></i> Centro de Costos *</label>
                        <select id="centroCostos" name="centroCostos" class="form-control select2-busqueda" required
                            disabled>
                            <option value="">Seleccione Centro de Costos</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group m-0">
                        <label for="categoria"><i class="fas fa-tag mr-4"></i> Categoría *</label>
                        <select id="categoria" name="categoria" class="form-control select2-busqueda" required>
                            <option value="">Seleccione Categoría</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-15">
                <div class="col-md-3">
                    <div class="form-group m-0">
                        <label><i class="fas fa-paperclip mr-4"></i> Adjunto 1 (PDF/Excel)</label>
                        <input type="file" id="adjunto1" name="adjunto1" class="file-input-hidden"
                            accept=".pdf, .xlsx, .xls">
                        <label for="adjunto1" class="file-card">
                            <div class="placeholder-info">
                                <i class="fas fa-cloud-upload-alt main-icon"></i>
                                <div>
                                    <p class="text-title">Subir o arrastrar</p>
                                    <p class="text-subtitle">(Máx 5MB)</p>
                                </div>
                            </div>
                            <div class="file-info">
                                <i class="fas fa-file-pdf main-icon"></i>
                                <p class="file-name nombre-archivo-moderno">...</p>
                                <button type="button" class="remove-file quitar-archivo-moderno">&times;</button>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group m-0">
                        <label><i class="fas fa-paperclip mr-4"></i> Adjunto 2</label>
                        <input type="file" id="adjunto2" name="adjunto2" class="file-input-hidden"
                            accept=".pdf, .xlsx, .xls">
                        <label for="adjunto2" class="file-card">
                            <div class="placeholder-info">
                                <i class="fas fa-cloud-upload-alt main-icon"></i>
                                <div>
                                    <p class="text-title">Subir o arrastrar</p>
                                    <p class="text-subtitle">(Máx 5MB)</p>
                                </div>
                            </div>
                            <div class="file-info">
                                <i class="fas fa-file-pdf main-icon"></i>
                                <p class="file-name nombre-archivo-moderno">...</p>
                                <button type="button" class="remove-file quitar-archivo-moderno">&times;</button>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group m-0">
                        <label><i class="fas fa-paperclip mr-4"></i> Adjunto 3</label>
                        <input type="file" id="adjunto3" name="adjunto3" class="file-input-hidden"
                            accept=".pdf, .xlsx, .xls">
                        <label for="adjunto3" class="file-card">
                            <div class="placeholder-info">
                                <i class="fas fa-cloud-upload-alt main-icon"></i>
                                <div>
                                    <p class="text-title">Subir o arrastrar</p>
                                    <p class="text-subtitle">(Máx 5MB)</p>
                                </div>
                            </div>
                            <div class="file-info">
                                <i class="fas fa-file-pdf main-icon"></i>
                                <p class="file-name nombre-archivo-moderno">...</p>
                                <button type="button" class="remove-file quitar-archivo-moderno">&times;</button>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div id="detalleProductosSection">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong><i class="fas fa-list-ul mr-4"></i> Detalle de productos</strong>
                        <span id="contador-productos-txt" class="text-muted fw-normal ml-5">(0 de 100)</span>
                    </div>
                    <div class="panel-body p-0" id="productosContainer">
                    </div>
                </div>
            </div>

            <div class="row mt-20">
                <div class="col-md-12">
                    <div class="form-group m-0">
                        <label for="observacion"><i class="fas fa-comment-dots mr-4"></i> Observación *</label>
                        <textarea id="observacion" name="observacion" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
            </div>

            <div class="panel panel-default border-none mt-15 mb-0">
                <div class="panel-body p-0">
                    <label><i class="fa-solid fa-circle-check mr-4"></i> Trazabilidad</label>
                    <div class="tracker-wrapper">
                        <div class="tracker">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row barra-acciones">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-default btn-moderno btn-moderno-defecto"
                        onclick="window.history.back()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-moderno btn-moderno-primario">
                        <i class="fas fa-paper-plane"></i> Enviar solicitud
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>