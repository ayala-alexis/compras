<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<style>
                        /* 1. Tipografía y Espaciado Global (COMPACTADO) */
    #purchaseRequestForm {
        font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    }
    /* Reducimos el aire alrededor del título */
    .titulo-moderno {
        font-family: 'Inter', sans-serif;
        font-weight: 800 !important;
        color: #111827 !important;
        font-size: 24px !important; /* Ligeramente más pequeño */
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 10px; /* Antes 20px */
        border-bottom: 2px solid #F3F4F6;
        margin-top: 10px; /* Antes 20px */
        margin-bottom: 15px; /* Antes 30px */
    }
    .titulo-moderno .icono-titulo {
        background-color: #EFF6FF;
        color: #3B82F6;
        width: 42px; /* Antes 50px */
        height: 42px; /* Antes 50px */
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 10px;
        font-size: 18px;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.15);
    }

    /* Reducimos la separación entre todas las filas del formulario */
    #purchaseRequestForm .row {
        margin-bottom: 10px; /* ANTES: 20px. Esto ahorra muchísimo espacio */
    }

    /* 2. Etiquetas Modernas (COMPACTADO) */
    #purchaseRequestForm label {
        font-weight: 600;
        color: #6B7280;
        font-size: 12px; /* Ligeramente más pequeño */
        margin-bottom: 4px; /* Antes 6px */
        display: block; 
    }
    #purchaseRequestForm label i { margin-right: 4px; }

    /* 3. Estética de Inputs (COMPACTADO) */
    #purchaseRequestForm .form-control {
        border-radius: 6px;
        border: 1px solid #D1D5DB;
        height: 36px; /* ANTES: 40px */
        padding: 6px 12px;
        box-shadow: none;
        font-size: 13px;
    }
    #purchaseRequestForm .form-control:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* --- ESTILIZACIÓN COMPLETA DE SELECT2 --- */
    .select2-container .select2-selection--single {
        height: 36px !important; /* ANTES: 40px */
        border: 1px solid #D1D5DB !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        font-family: 'Inter', sans-serif !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #3B82F6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        outline: none !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px !important;
        top: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #374151 !important;
        font-size: 13px !important;
        padding-left: 12px !important;
        line-height: normal !important;
    }
    .select2-dropdown {
        border: 1px solid #D1D5DB !important;
        border-radius: 0 0 8px 8px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        font-family: 'Inter', sans-serif !important;
        overflow: hidden !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #D1D5DB !important;
        border-radius: 6px !important;
        padding: 6px 10px !important;
        outline: none !important;
        font-family: 'Inter', sans-serif !important;
        font-size: 13px !important;
        margin-top: 4px !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #3B82F6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    .select2-container--default .select2-results__option {
        padding: 6px 12px !important;
        font-size: 13px !important;
        color: #374151 !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--default .select2-results__option--highlighted[aria-selected]:hover {
        background-color: #3B82F6 !important;
        color: #FFFFFF !important;
    }

    /* 4. Gestión de Archivos Ocultos */
    .file-input-hidden { display: none !important; }

    /* 5. Tarjetas Compactas de Carga (COMPACTADO) */
    #purchaseRequestForm label.file-card {
        border: 2px dashed #D1D5DB;
        background-color: #F9FAFB;
        border-radius: 8px;
        display: flex !important;
        flex-direction: row; 
        align-items: center;
        justify-content: center;
        height: 42px; /* ANTES: 50px */
        cursor: pointer;
        transition: all 0.2s;
        color: #6B7280;
        padding: 0 12px;
        margin-bottom: 0;
    }
    #purchaseRequestForm label.file-card p,
    #purchaseRequestForm label.file-card i {
        margin: 0 !important;
        line-height: 1 !important;
    }
    .file-card:hover {
        border-color: #3B82F6;
        background-color: #F3F4F6;
    }
    .file-card .placeholder-info {
        display: flex; 
        align-items: center; 
        justify-content: center; 
        width: 100%;
        gap: 6px;
    }
    .file-card i.main-icon { font-size: 16px; }
    .file-card .text-title { font-weight: 700; color: #1F2937; font-size: 12px; }
    .file-card .text-subtitle { color: #6B7280; font-size: 11px; }

    /* Estado con archivo */
    .file-card.has-file { border-style: solid; border-color: #10B981; background-color: #ECFDF5; color: #1F2937; }
    .file-card.has-file i.main-icon { color: #10B981; }
    .file-card .file-info { display: none; }
    .file-card.has-file .placeholder-info { display: none; }
    .file-card.has-file .file-info { display: flex; align-items: center; width: 100%; font-weight: 600; gap: 8px; }
    .file-card.has-file .file-name { flex-grow: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px; }
    .file-card.has-file .remove-file { background: none; border: none; color: #6B7280; font-size: 16px; cursor: pointer; padding: 0 0 0 10px; }
    .file-card.has-file .remove-file:hover { color: #EF4444; }

    /* 6. Panel y Grilla de Productos (COMPACTADO) */
    #detalleProductosSection { margin-top: 10px; }
    #detalleProductosSection .panel { border-radius: 10px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: visible; }
    #detalleProductosSection .panel-heading {
        background-color: #fff;
        color: #1F2937;
        border-bottom: 1px solid #eee;
        font-weight: 700;
        padding: 10px 15px; /* ANTES: 15px */
        font-size: 13px;
    }

    /* Filas de la grilla */
    .fila-producto {
        display: flex;
        align-items: flex-end; 
        flex-wrap: wrap; 
        padding: 10px 15px; /* ANTES: 15px 20px */
        margin: 0;
        border-bottom: 1px solid #E5E7EB;
        transition: background-color 0.2s ease;
    }
    .fila-producto .form-group { margin-bottom: 0 !important; width: 100%; }

    /* Oculta los labels si NO es la primera fila */
    .fila-producto ~ .fila-producto label { display: none !important; }
    /* Reduce muchísimo el espacio superior de las filas sin label */
    .fila-producto ~ .fila-producto { padding-top: 6px; }

    .fila-producto[data-estado="pendiente"] { background-color: #F8FAFC; }
    .fila-producto[data-estado="confirmado"] { background-color: #FFFFFF; }

    /* Botones de acción simétricos */
    .btn-accion-texto {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px; 
        height: 36px; /* Ajustado a la nueva altura de inputs */
        font-weight: 600;
        font-size: 12px;
        border-radius: 6px;
        border: none;
        transition: all 0.2s;
        width: 100%;
    }

    /* Buscador predictivo dropdown */
    .contenedor-resultados {
        position: absolute;
        top: 100%; 
        left: 0; 
        right: 0; 
        width: 100%; 
        z-index: 9999;
        background: white;
        border: 1px solid #D1D5DB;
        border-top: none; 
        border-radius: 0 0 6px 6px; 
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        max-height: 180px;
        overflow-y: auto;
        display: none;
        padding: 0;
        margin: 0;
    }
    .contenedor-resultados .list-group-item {
        cursor: pointer;
        border-left: none;
        border-right: none;
        border-radius: 0;
        margin-bottom: 0;
        color: #374151;
        transition: background-color 0.1s;
        padding: 8px 12px !important;
    }

    .contenedor-resultados .list-group-item:hover,
    .contenedor-resultados .list-group-item.active {
        background-color: #3B82F6 !important; 
        color: #FFFFFF !important; 
    }
    .contenedor-resultados .list-group-item:hover span,
    .contenedor-resultados .list-group-item.active span {
        color: #E0E7FF !important; 
    }

    /* 7. Observaciones */
    #observacion {
        border-radius: 8px;
        border: 1px solid #D1D5DB;
        font-size: 13px;
        height: 60px; /* Hacemos el textarea un poco más compacto */
    }

    /* 8. Botones Finales (COMPACTADO) */
    .barra-acciones {
        margin-top: 15px; /* Antes 30px */
        padding-top: 15px; /* Antes 20px */
        border-top: 1px solid #eee;
    }
    .btn-moderno {
        border-radius: 6px;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
    }
    .btn-moderno i { margin-right: 6px; }
    .btn-moderno-defecto { background-color: #fff; color: #6B7280; border: 1px solid #D1D5DB; }
    .btn-moderno-defecto:hover { background-color: #F9FAFB; border-color: #D1D5DB; color: #1F2937; }
    .btn-moderno-primario { background-color: #3B82F6; color: #fff; border: 1px solid #3B82F6; }
    .btn-moderno-primario:hover { background-color: #2563EB; border-color: #2563EB; }

    /* --- SISTEMA DE NOTIFICACIONES TOAST --- */
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none; /* Para que no bloquee clics debajo */
    }

    .toast-msg {
        min-width: 280px;
        background-color: #ffffff;
        color: #1F2937;
        padding: 14px 18px;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        border-left: 4px solid #3B82F6; /* Azul por defecto */
        animation: slideInRight 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .toast-msg.error { border-left-color: #EF4444; }
    .toast-msg.error i { color: #EF4444; font-size: 18px; }

    .toast-msg.success { border-left-color: #10B981; }
    .toast-msg.success i { color: #10B981; font-size: 18px; }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Clase de ayuda para enmarcar en rojo el input con error */
    .input-error {
        border-color: #EF4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-md-12">
        <h2 class="titulo-moderno">
            <div class="icono-titulo">
                <i class="fas fa-cart-plus"></i>
            </div>
            Adicionar solicitud de compras
        </h2>
            
            <form id="purchaseRequestForm">
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="empresa"><i class="fas fa-building"></i> Empresa *</label>
                            <select id="empresa" class="form-control select2-busqueda" required>
                                <option value="">Seleccione Empresa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="centroCostos"><i class="fas fa-home"></i> Centro de Costos *</label>
                            <select id="centroCostos" class="form-control select2-busqueda" required disabled>
                                <option value="">Seleccione Centro de Costos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="categoria"><i class="fas fa-tag"></i> Categoría *</label>
                            <select id="categoria" class="form-control select2-busqueda" required>
                                <option value="">Seleccione Categoría</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><i class="fas fa-paperclip"></i> Adjunto 1 (PDF/Excel)</label>
                            <input type="file" id="adjunto1" class="file-input-hidden" accept=".pdf, .xlsx, .xls">
                            <label for="adjunto1" class="file-card">
                                <div class="placeholder-info">
                                    <i class="fas fa-cloud-upload-alt main-icon"></i>
                                    <p class="text-title">Subir PDF o Excel</p>
                                    <p class="text-subtitle">(Máx 5MB)</p>
                                </div>
                                <div class="file-info">
                                    <i class="fas fa-file-pdf main-icon"></i>
                                    <p class="file-name nombre-archivo-moderno">...</p>
                                    <button type="button" class="remove-file quitar-archivo-moderno">&times;</button>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><i class="fas fa-paperclip"></i> Adjunto 2</label>
                            <input type="file" id="adjunto2" class="file-input-hidden" accept=".pdf, .xlsx, .xls">
                            <label for="adjunto2" class="file-card">
                                <div class="placeholder-info">
                                    <i class="fas fa-cloud-upload-alt main-icon"></i>
                                    <p class="text-title">Subir PDF o Excel</p>
                                    <p class="text-subtitle">(Máx 5MB)</p>
                                </div>
                                <div class="file-info">
                                    <i class="fas fa-file-pdf main-icon"></i>
                                    <p class="file-name nombre-archivo-moderno">...</p>
                                    <button type="button" class="remove-file quitar-archivo-moderno">&times;</button>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label><i class="fas fa-paperclip"></i> Adjunto 3</label>
                            <input type="file" id="adjunto3" class="file-input-hidden" accept=".pdf, .xlsx, .xls">
                            <label for="adjunto3" class="file-card">
                                <div class="placeholder-info">
                                    <i class="fas fa-cloud-upload-alt main-icon"></i>
                                    <p class="text-title">Subir PDF o Excel</p>
                                    <p class="text-subtitle">(Máx 5MB)</p>
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

                <div id="detalleProductosSection" style="display: none;">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong><i class="fas fa-list-ul"></i> Detalle de productos</strong> (Máximo 100)
                        </div>
                        <div class="panel-body" id="productosContainer" style="padding: 0;">
                            </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="observacion"><i class="fas fa-comment-dots"></i> Observación *</label>
                            <textarea id="observacion" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="row barra-acciones">
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-default btn-moderno btn-moderno-defecto" onclick="window.history.back()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary btn-moderno btn-moderno-primario">
                            <i class="fas fa-paper-plane"></i> Enviar solicitud a aprobación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

// =====================================================================
// 0. INICIALIZACIÓN DE SELECT2 (Buscadores en Dropdowns)
// =====================================================================
$(document).ready(function() {
    // Inicializar la librería en los elementos con clase .select2-busqueda
    $('.select2-busqueda').select2({
        width: '100%',
        language: {
            noResults: function() { return "No se encontraron resultados"; }
        }
    });

    // Como Select2 usa jQuery, redirigimos el evento 'change' nativo 
    // para que la lógica en cascada que ya programamos siga funcionando igual.
    $('#empresa, #categoria').on('select2:select', function (e) {
        this.dispatchEvent(new Event('change'));
    });

    // ¡NUEVA VALIDACIÓN ESTRICTA DE CATEGORÍA!
    $('#categoria').on('select2:selecting', function (e) {
        // Buscamos si existe al menos una fila con estado "confirmado" en la grilla
        const productosConfirmados = document.querySelectorAll('#productosContainer .fila-producto[data-estado="confirmado"]');
        
        if (productosConfirmados.length > 0) {
            // 1. Detenemos en seco el cambio de Select2 (mantiene el valor original)
            e.preventDefault(); 
            
            // 2. Le mostramos al usuario exactamente por qué no puede cambiarlo
            mostrarAlerta('No puede cambiar la categoría si ya hay productos agregados. Quítelos primero.', 'error');
            
            // 3. (Opcional) Hacemos parpadear el select para dar feedback visual
            const selectContainer = document.querySelector('[aria-labelledby="select2-categoria-container"]').parentElement;
            selectContainer.classList.add('input-error');
            setTimeout(() => selectContainer.classList.remove('input-error'), 2500);
        }
    });
});
// =====================================================================
// SISTEMA DE NOTIFICACIONES TOAST
// =====================================================================
function mostrarAlerta(mensaje, tipo = 'error') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast-msg ${tipo}`;
    
    let icon = 'fa-info-circle';
    if (tipo === 'error') icon = 'fa-exclamation-circle';
    if (tipo === 'success') icon = 'fa-check-circle';

    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${mensaje}</span>`;
    
    container.appendChild(toast);

    // Animación de salida y destrucción del elemento
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// =====================================================================
// 1. MANEJADORES DE TARJETAS DE CARGA DE ARCHIVOS
// =====================================================================
document.querySelectorAll('.file-input-hidden').forEach(input => {
    input.addEventListener('change', function(e) {
        const file = this.files[0];
        const cardId = this.id;
        const card = document.querySelector(`label.file-card[for="${cardId}"]`);
        
        if (file) {
            const maxSize = 5 * 1024 * 1024;
            const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
            
            if (file.size > maxSize || !allowedTypes.includes(file.type)) {
                mostrarAlerta('Archivo no válido. Solo PDF/Excel hasta 5MB.', 'error');
                resetFilaArchivo(this, card);
                return;
            }
            card.classList.add('has-file');
            card.querySelector('.nombre-archivo-moderno').textContent = file.name;
        } else {
            resetFilaArchivo(this, card);
        }
    });
});

document.querySelectorAll('.quitar-archivo-moderno').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault(); 
        const card = this.closest('.file-card');
        const inputId = card.getAttribute('for');
        const input = document.getElementById(inputId);
        resetFilaArchivo(input, card);
    });
});

function resetFilaArchivo(input, card) {
    input.value = '';
    card.classList.remove('has-file');
    card.querySelector('.nombre-archivo-moderno').textContent = '...';
}

// =====================================================================
// 2. FUNCIONES CORE, SELECTORES Y SUBMIT
// =====================================================================
const form = document.getElementById('purchaseRequestForm');
const selectEmpresa = document.getElementById('empresa');
const selectCC = document.getElementById('centroCostos');
const selectCat = document.getElementById('categoria');
const detalleSection = document.getElementById('detalleProductosSection');

fetchData('json.php?c=catalog&a=catalogo&cat=empresa_user', selectEmpresa);
fetchData('json.php?c=catalog&a=catalogo&cat=categoria_compra', selectCat);

selectEmpresa.addEventListener('change', (e) => {
    const id = e.target.value;
    selectCC.innerHTML = '<option value="">Cargando...</option>';
    // Actualizamos Select2 después de vaciar
    $(selectCC).prop('disabled', !id).trigger('change.select2'); 
    
    if (id) fetchData(`json.php?c=catalog&a=catalogo&cat=cc_user&id=${id}`, selectCC);
});

selectCat.addEventListener('change', (e) => {
    const container = document.getElementById('productosContainer');
    if (e.target.value) {
        detalleSection.style.display = 'block';
        container.innerHTML = ''; 
        agregarFilaProducto();
    } else {
        detalleSection.style.display = 'none';
        container.innerHTML = ''; 
    }
});

async function fetchData(url, element) {
    try {
        const res = await fetch(url);
        const data = await res.json();
        element.innerHTML = '<option value="">Seleccione una opción</option>';
        const optgroups = {};
        if(data.exito && data.catalogo) {
            data.catalogo.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.keyCode;
                opt.textContent = item.keyValue;
                if (item.keyGroup) {
                    if (!optgroups[item.keyGroup]) {
                        const groupEl = document.createElement('optgroup');
                        groupEl.label = item.keyGroup;
                        element.appendChild(groupEl);
                        optgroups[item.keyGroup] = groupEl;
                    }
                    optgroups[item.keyGroup].appendChild(opt);
                } else {
                    element.appendChild(opt);
                }
            });
            // Si el elemento es un Select2, forzamos actualización de la UI
            if (window.jQuery && $(element).hasClass("select2-hidden-accessible")) {
                $(element).trigger('change.select2');
            }
        }
    } catch (err) {
        console.error("Error cargando datos:", err);
    }
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const container = document.getElementById('productosContainer');
    const confirmados = container.querySelectorAll('[data-estado="confirmado"]');
    const pendiente = container.querySelector('[data-estado="pendiente"]');
    
    if (selectCat.value && confirmados.length === 0) {
        mostrarAlerta('Debe completar y Agregar (+) al menos un producto.', 'error');
        pendiente.querySelector('.btn-success').focus();
        return;
    }

    if (pendiente) {
        pendiente.querySelectorAll('input, textarea, button').forEach(el => el.disabled = true);
    }

    const formData = new FormData(form);
    
    if (pendiente) {
        pendiente.querySelectorAll('input, textarea, button').forEach(el => el.disabled = false);
    }
    
    try {
        const response = await fetch('/api/solicitudes/enviar', {
            method: 'POST',
            body: formData
        });
        if (response.ok) {
            mostrarAlerta('¡Solicitud enviada con éxito!', 'success');
            window.location.reload();
        }
    } catch (error) {
        mostrarAlerta('Error al enviar la solicitud. Intente de nuevo.', 'error');
        console.error('Error al enviar:', error);
    }
});

// =====================================================================
// 3. LÓGICA DE GRILLA INLINE 
// =====================================================================

let rowCount = 0;
const MAX_ITEMS = 100;

function agregarFilaProducto() {
    const container = document.getElementById('productosContainer');
    if (container.children.length >= MAX_ITEMS) return; 

    rowCount++;
    const rowId = `producto_row_${rowCount}`;
    const idCategoria = selectCat.value; 
    
    const html = `
        <div class="row fila-producto" id="${rowId}" data-estado="pendiente">
            <div class="col-md-2">
                <div class="form-group">
                    <label><i class="fas fa-hashtag"></i> Cant.</label>
                    <input type="number" name="productos[${rowCount}][cantidad]" class="form-control cantidad-input" min="1" step="1" value="1">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" style="position: relative;">
                    <label><i class="fas fa-box"></i> Producto (Búsqueda)</label>
                    <input type="text" class="form-control producto-search" placeholder="Buscar..." autocomplete="off">
                    <input type="hidden" name="productos[${rowCount}][id_producto]" class="producto-id-hidden">
                    <ul class="list-group product-results contenedor-resultados"></ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Descripción</label>
                    <textarea name="productos[${rowCount}][descripcion]" class="form-control descripcion-input" rows="1" placeholder="Detalles del producto..."></textarea>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group action-buttons">
                    <label class="hidden-xs hidden-sm">&nbsp;</label>
                    <button type="button" class="btn btn-success btn-accion-texto btn-accion" onclick="confirmarFila('${rowId}')" title="Agregar a la lista">
                        <i class="fas fa-plus"></i> Agregar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    inicializarBuscadorFila(document.getElementById(rowId), idCategoria);
}

window.confirmarFila = function(rowId) {
    const row = document.getElementById(rowId);
    const inputCantidad = row.querySelector('.cantidad-input');
    const inputProducto = row.querySelector('.producto-search');
    // Capturamos también el ID oculto
    const inputIdProducto = row.querySelector('.producto-id-hidden'); 
    const inputDescripcion = row.querySelector('.descripcion-input');

    const cantidad = inputCantidad.value;
    const producto = inputProducto.value;
    const idProducto = inputIdProducto.value; // Extraemos el valor del ID
    const descripcion = inputDescripcion.value;

    // 1. Flujo validación Cantidad
    if (!cantidad || Number(cantidad) < 1 || !Number.isInteger(Number(cantidad))) {
        mostrarAlerta('La cantidad debe ser un número entero mayor o igual a 1.', 'error');
        inputCantidad.classList.add('input-error');
        inputCantidad.focus();
        setTimeout(() => inputCantidad.classList.remove('input-error'), 2500);
        return;
    }

    // 2. ¡NUEVO! Validación estricta de Producto (Debe provenir de la API)
    if (producto.trim() !== '' && idProducto === '') {
        mostrarAlerta('Producto ingresado no existe. Seleccione uno de la lista.', 'error');
        inputProducto.classList.add('input-error');
        inputProducto.focus();
        setTimeout(() => inputProducto.classList.remove('input-error'), 2500);
        return;
    }

    // 3. Validación de vacíos (No pueden ir ambos vacíos)
    if (producto.trim() === '' && descripcion.trim() === '') {
        mostrarAlerta('Debe seleccionar un Producto o escribir una Descripción.', 'error');
        inputProducto.classList.add('input-error');
        inputDescripcion.classList.add('input-error');
        inputProducto.focus();
        setTimeout(() => {
            inputProducto.classList.remove('input-error');
            inputDescripcion.classList.remove('input-error');
        }, 2500);
        return;
    }

    // Transformación de estado
    row.dataset.estado = "confirmado";

    // Transformamos botón Verde a Rojo (Quitar)
    const actionContainer = row.querySelector('.action-buttons');
    actionContainer.innerHTML = `
        <label class="hidden-xs hidden-sm">&nbsp;</label>
        <button type="button" class="btn btn-danger btn-accion-texto btn-accion" onclick="eliminarFila('${rowId}')" title="Eliminar fila">
            <i class="fas fa-trash-alt"></i> Quitar
        </button>
    `;

    // Flujo de ReadOnly locking
    inputCantidad.readOnly = true;
    inputProducto.readOnly = true;
    inputDescripcion.readOnly = true;

    // Flujo de creación de nueva cajetilla vacía en cascada
    agregarFilaProducto();
};

window.eliminarFila = function(rowId) {
    const row = document.getElementById(rowId);
    if (row) row.remove();
    
    const container = document.getElementById('productosContainer');
    const pendingRow = container.querySelector('[data-estado="pendiente"]');
    
    if (!pendingRow && container.children.length < MAX_ITEMS) {
        agregarFilaProducto();
    }
};

function inicializarBuscadorFila(rowElement, idCategoria) {
    const inputSearch = rowElement.querySelector('.producto-search');
    const resultList = rowElement.querySelector('.product-results');
    const inputHiddenId = rowElement.querySelector('.producto-id-hidden');
    const inputDesc = rowElement.querySelector('.descripcion-input');
    
    let timeout = null;
    let currentFocus = -1;

    inputSearch.addEventListener('input', (e) => {
        clearTimeout(timeout);
        const query = e.target.value;
        currentFocus = -1;

        // ¡NUEVO!: Borramos el ID oculto apenas el usuario escriba algo.
        // Esto lo obliga a tener que seleccionar una opción válida de la lista.
        inputHiddenId.value = '';
        
        if (query.length < 3) {
            resultList.style.display = 'none';
            return;
        }

        timeout = setTimeout(() => {
            const searchData = new FormData();
            searchData.append('search', query);

            fetch(`json.php?c=catalog&a=catalogo_search&cat=productos_cat&id=${idCategoria}`, {
                method: 'POST',
                body: searchData
            })
            .then(res => res.json())
            .then(data => renderizarResultados(data))
            .catch(err => console.error("Error buscando:", err));
        }, 500);
    });

    inputSearch.addEventListener('keydown', function(e) {
        let items = resultList.getElementsByTagName('li');
        if (resultList.style.display === 'none' || items.length === 0) return;

        if (e.key === "ArrowDown") {
            currentFocus++;
            manejarFocoVisible(items);
        } else if (e.key === "ArrowUp") {
            currentFocus--;
            manejarFocoVisible(items);
        } else if (e.key === "Enter") {
            e.preventDefault(); 
            if (currentFocus > -1 && items[currentFocus]) {
                items[currentFocus].click();
            }
        }
    });

    function renderizarResultados(data) {
        resultList.innerHTML = '';

        if (data.exito && Array.isArray(data.catalogo) && data.catalogo.length > 0) {
            // Nota que agregamos el 'index' en el forEach
            data.catalogo.forEach((item, index) => {
                const li = document.createElement('li');
                li.className = 'list-group-item'; 
                
                li.innerHTML = `<strong>${item.keyValue}</strong><br/> <span style="color: #6B7280; font-size: 11px;">${item.keyDescription || ''}</span>`;

                // Sincronizamos el hover del mouse con el teclado
                li.addEventListener('mouseover', () => {
                    let items = resultList.getElementsByTagName('li');
                    for (let i = 0; i < items.length; i++) {
                        items[i].classList.remove("active");
                    }
                    li.classList.add('active');
                    currentFocus = index; // Le decimos al sistema en qué posición está el ratón
                });

                li.addEventListener('click', () => {
                    inputSearch.value = item.keyValue;
                    inputHiddenId.value = item.keyCode; 
                    inputDesc.value = item.keyDescription ? item.keyDescription : '';
                    resultList.style.display = 'none';
                });

                resultList.appendChild(li);
            });
            resultList.style.display = 'block';

            // --- NUEVO: Foco automático en el primer elemento ---
            currentFocus = 0; 
            manejarFocoVisible(resultList.getElementsByTagName('li'));

        } else {
            resultList.innerHTML = '<li class="list-group-item text-muted" style="border: none; font-size: 11px; padding: 10px 15px;">Sin resultados...</li>';
            resultList.style.display = 'block';
            currentFocus = -1; // Reseteamos si no hay resultados válidos
        }
    }

    function manejarFocoVisible(items) {
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove("active");
        }
        if (currentFocus >= items.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (items.length - 1);
        
        items[currentFocus].classList.add("active");
        items[currentFocus].scrollIntoView({ block: "nearest", behavior: "smooth" });
    }
}

document.addEventListener('click', (e) => {
    if (!e.target.classList.contains('producto-search')) {
        document.querySelectorAll('.contenedor-resultados').forEach(ul => ul.style.display = 'none');
    }
});

// =====================================================================
// 4. PERSONALIZACIÓN DE MENSAJES DE VALIDACIÓN HTML5
// =====================================================================
document.addEventListener("DOMContentLoaded", function() {
    // Seleccionamos todos los elementos requeridos del formulario
    const elementosRequeridos = document.querySelectorAll('[required]');

    elementosRequeridos.forEach(elemento => {
        // 1. Interceptamos el evento 'invalid' (cuando el usuario intenta enviar y falla)
        elemento.addEventListener('invalid', function(e) {
            e.target.setCustomValidity(''); // Limpiamos cualquier error previo
            
            if (!e.target.validity.valid) {
                // Mensaje personalizado dependiendo del tipo de campo
                if (e.target.tagName === 'SELECT') {
                    e.target.setCustomValidity('Por favor, seleccione una opción de la lista.');
                } else {
                    e.target.setCustomValidity('Por favor, complete este campo obligatorio.');
                }
            }
        });

        // 2. Limpiamos el error en el momento en que el usuario empieza a corregirlo
        // Para inputs y textareas
        elemento.addEventListener('input', function(e) {
            e.target.setCustomValidity('');
        });
        
        // Para selects (especialmente importante por Select2)
        elemento.addEventListener('change', function(e) {
            e.target.setCustomValidity('');
        });
    });
});

</script>