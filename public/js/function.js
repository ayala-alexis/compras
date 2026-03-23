// ==========================================================================================
// SISTEMA NERVIOSO FRONT-END (SICYS - Módulo de Compras)
// Arquitectura: Vanilla JavaScript (ES6+) con Fetch API para peticiones asíncronas.
// Objetivo: Desacoplar la UI de la recarga de páginas, ofreciendo una experiencia tipo SPA 
// (Single Page Application) fluida, con validaciones estrictas antes de tocar la Base de Datos.
// ==========================================================================================

// =====================================================================
// 1. UTILIDADES GLOBALES (Reutilizables en cualquier vista)
// =====================================================================

window.mostrarAlerta = function (mensaje, tipo = 'error') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast-msg ${tipo}`;
    let icon = tipo === 'error' ? 'fa-exclamation-circle' : (tipo === 'success' ? 'fa-check-circle' : 'fa-info-circle');
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${mensaje}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
};

window.fetchData = async function (url, element, textoPorDefecto = "Seleccione una opción") {
    try {
        const res = await fetch(url);
        const data = await res.json();
        element.innerHTML = `<option value="">${textoPorDefecto}</option>`;
        const optgroups = {};

        if (data.exito && data.catalogo) {
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
                } else { element.appendChild(opt); }
            });
            if (window.jQuery && $(element).hasClass("select2-hidden-accessible")) {
                $(element).trigger('change.select2');
            }
        }
    } catch (err) { console.error("Error cargando datos:", err); }
};

window.autoResizeTextarea = function (element) {
    element.style.height = 'auto';
    element.style.height = (element.scrollHeight + 2) + 'px';
};

window.mostrarModalExito = function (numeroSolicitud, titulo = '¡Solicitud Creada!', mensaje = 'Se ha generado exitosamente la solicitud con el número:') {
    const modalHtml = `
    <div id="modalExitoCompra" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(3px); font-family: 'Inter', sans-serif;">
        <div style="background: white; padding: 40px 30px; border-radius: 16px; text-align: center; max-width: 450px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
            <i class="fas fa-check-circle" style="font-size: 64px; color: #28a745; margin-bottom: 20px;"></i>
            <h3 style="margin-top: 0; color: #333; font-weight: 700; font-size: 24px;">${titulo}</h3>
            <p style="color: #555; font-size: 16px; margin-bottom: 30px; line-height: 1.5;">
                ${mensaje}<br>
                <span style="display: inline-block; margin-top: 10px; font-size: 32px; color: #0056b3; font-weight: 800; letter-spacing: 1px;">#${numeroSolicitud}</span>
            </p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <button type="button" onclick="window.history.back()" style="padding: 12px 20px; border: 1px solid #ddd; background: #f8f9fa; color: #444; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s;">
                    <i class="fas fa-arrow-left"></i> Regresar a consulta
                </button>
                <button type="button" onclick="window.location.reload()" style="padding: 12px 20px; border: none; background: #0056b3; color: white; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,86,179,0.2);">
                    <i class="fas fa-plus"></i> Crear otra
                </button>
            </div>
        </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
};

window.inicializarPeriodos = function (selectId, incluirTodos = false) {
    const selectPeriodo = document.getElementById(selectId);
    if (!selectPeriodo) return;

    const fechaActual = new Date();
    const anioActual = fechaActual.getFullYear();
    const mesActual = fechaActual.getMonth() + 1;
    const meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    if (incluirTodos) {
        const optTodos = document.createElement('option');
        optTodos.value = 'todos';
        optTodos.textContent = 'Todos los meses';
        selectPeriodo.appendChild(optTodos);
    }

    for (let a = anioActual; a >= 2026; a--) {
        let mesInicio = (a === anioActual) ? mesActual : 12;
        for (let m = mesInicio; m >= 1; m--) {
            const option = document.createElement('option');
            option.value = `${a}-${m}`;
            option.textContent = `${meses[m]} / ${a}`;
            if (a === anioActual && m === mesActual && !incluirTodos) option.selected = true;
            selectPeriodo.appendChild(option);
        }
    }
};

window.obtenerTiempoTranscurrido = function (fecha, hora) {
    if (!fecha || !hora) return '<span class="sla-pill" style="color: #64748b; background: #f1f5f9;"><i class="fas fa-stopwatch mr-3"></i> N/A</span>';

    const fechaCreacion = new Date(`${fecha}T${hora}`);
    const ahora = new Date();
    let diffMs = ahora - fechaCreacion;
    if (diffMs < 0) diffMs = 0;

    const diffMinsTotal = Math.floor(diffMs / 60000);
    const diffDays = Math.floor(diffMinsTotal / 1440);
    const diffHrs = Math.floor((diffMinsTotal % 1440) / 60);
    const diffMins = diffMinsTotal % 60;

    let resultado = '';
    let colorUX = '';

    if (diffDays > 0) { resultado = `${diffDays}D ${diffHrs}H ${diffMins}m`; }
    else if (diffHrs > 0) { resultado = `${diffHrs}H ${diffMins}m`; }
    else { resultado = `${diffMins}m`; }

    if (diffDays >= 14) { colorUX = '#ef4444'; }
    else if (diffDays > 7) { colorUX = '#f59e0b'; }
    else { colorUX = '#10b981'; }

    return `<span class="sla-pill" style="color: ${colorUX}; background: ${colorUX}15;">
                <i class="fas fa-stopwatch mr-3"></i> ${resultado}
            </span>`;
};

// --- MÓDULO GLOBAL DE TRAZABILIDAD ---
window.CONFIGURACION_PASOS_TRAZA = [
    { id: 'solicitante', titulo: 'Solicitante Cco.', icono: 'fa-solid fa-user' },
    { id: 'cotizacion', titulo: 'Cotización', icono: 'fa-solid fa-file-invoice-dollar' },
    { id: 'aprobador_cc', titulo: 'Autorizador Cco.', icono: 'fa-solid fa-user-check' },
    { id: 'aprobador_categoria', titulo: 'Autorizador Categoría', icono: 'fa-solid fa-user-tag' },
    { id: 'aprobador_5k', titulo: 'Autorizador >= $5K', icono: 'fa-solid fa-user-shield' },
    { id: 'compra', titulo: 'Orden de Compra', icono: 'fa-solid fa-shopping-cart' },
    { id: 'recepcion', titulo: 'Recepción', icono: 'fa-solid fa-box-open' } // 🏗️ Mantenemos la recepción
];

window.cargarTrazabilidad = async function (idEmpresa, idCc, idCategoria) {
    // 🏗️ CORRECCIÓN ARQUITECTÓNICA: Eliminamos el bloqueo anterior.
    // Ahora enviamos 0, 0, 0 al Backend de forma intencional para obtener 
    // la "plantilla por defecto" del diagrama de firmas.
    const url = 'json.php?c=trazabilidad&a=crear';
    const formData = new FormData();
    formData.append('id_empresa', idEmpresa || 0);
    formData.append('id_cc', idCc || 0);
    formData.append('id_categoria', idCategoria || 0);

    try {
        const response = await fetch(url, { method: 'POST', body: formData });
        const data = await response.json();

        if (data.exito && data.trazabilidad) {
            window.renderizarHTMLTrazabilidad(data.trazabilidad);
        } else {
            document.querySelector('.tracker').innerHTML = `<p class="text-danger text-center w-100 p-10"><i class="fas fa-exclamation-triangle mr-2"></i> ${data.msj || 'Error de datos'}</p>`;
        }
    } catch (error) {
        console.error('Error trazabilidad:', error);
        document.querySelector('.tracker').innerHTML = `<p class="text-danger text-center w-100 p-10"><i class="fas fa-exclamation-triangle mr-2"></i> Error de red conectando con la trazabilidad.</p>`;
    }
};

window.renderizarHTMLTrazabilidad = function (datosTrazabilidad) {
    const contenedor = document.querySelector('.tracker');
    if (!contenedor) return;
    const pasosValidos = [];
    window.CONFIGURACION_PASOS_TRAZA.forEach((config) => {
        if (datosTrazabilidad[config.id]) pasosValidos.push({ config: config, data: datosTrazabilidad[config.id] });
    });

    let htmlNodos = '';
    let pasoActivoIndex = -1;
    let secuenciaBloqueada = false;

    pasosValidos.forEach((paso, index) => {
        let estadoBackend = paso.data.estado;
        if (secuenciaBloqueada) estadoBackend = 'pending';
        else if (estadoBackend === 'active' || estadoBackend === 'pending') {
            secuenciaBloqueada = true;
            if (estadoBackend === 'active') pasoActivoIndex = index;
        }

        let claseEstado = '', iconoEstado = '', textoEstado = '', badgeHtml = '';
        if (estadoBackend === 'done') {
            claseEstado = 'done'; iconoEstado = '<i class="fas fa-check"></i>'; textoEstado = 'Completado';
            badgeHtml = `<span class="step-badge step-badge-done">${textoEstado}</span>`;
        } else if (estadoBackend === 'active') {
            claseEstado = 'active'; iconoEstado = '<i class="fas fa-clock"></i>'; textoEstado = 'En Proceso';
            badgeHtml = `<span class="step-badge step-badge-active">${textoEstado}</span>`;
        } else {
            claseEstado = 'pending'; textoEstado = 'Pendiente';
            badgeHtml = `<span class="step-badge step-badge-pending">${textoEstado}</span>`;
        }

        const nombreUsuario = paso.data.usr_name || paso.data.usr_nombre || paso.data.usr_id || 'N/A';
        htmlNodos += `<div class="step-node ${claseEstado}">
            <div class="step-dot"><i class="${paso.config.icono}"></i><div class="status-overlay">${iconoEstado}</div></div>
            <div class="step-label"><h4>${paso.config.titulo}</h4><p class="m-0 text-muted-dark text-bold">${nombreUsuario}<br>${badgeHtml}</p></div>
        </div>`;
    });

    let porcentajeProgreso = 0, colorFondo = '';
    if (pasosValidos.length > 0) {
        if (pasoActivoIndex === -1 && !secuenciaBloqueada) { porcentajeProgreso = 100; colorFondo = '#28a745'; }
        else if (pasoActivoIndex >= 0) { porcentajeProgreso = ((pasoActivoIndex + 0.5) / pasosValidos.length) * 100; colorFondo = (pasoActivoIndex === 0) ? '#ffc107' : '#28a745'; }
    }
    contenedor.innerHTML = `<div class="tracker-progress" style="width: ${porcentajeProgreso}%; background-color: ${colorFondo} !important;"></div>${htmlNodos}`;
};

window.procesarArchivoUI = function (file, inputElement, cardElement) {
    if (file) {
        if (file.size > (5 * 1024 * 1024) || !['application/pdf', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'].includes(file.type)) {
            mostrarAlerta('Archivo no válido. Solo PDF/Excel hasta 5MB.', 'error');
            window.resetFilaArchivo(inputElement, cardElement);
            return;
        }
        const iconElement = cardElement.querySelector('.file-info .main-icon');
        if (iconElement) {
            iconElement.classList.remove('fa-file-pdf', 'fa-file-excel');
            if (file.type === 'application/pdf') {
                iconElement.classList.add('fa-file-pdf'); iconElement.style.color = '#e2574c';
            } else {
                iconElement.classList.add('fa-file-excel'); iconElement.style.color = '#207245';
            }
        }
        cardElement.classList.add('has-file');
        const nameEl = cardElement.querySelector('.nombre-archivo-moderno');
        if (nameEl) nameEl.textContent = file.name;

        // 🏗️ Forzamos el cambio visual ocultando el placeholder y mostrando la info del archivo
        const placeholder = cardElement.querySelector('.placeholder-info');
        const fileInfo = cardElement.querySelector('.file-info');
        if (placeholder) placeholder.style.display = 'none';
        if (fileInfo) fileInfo.style.display = 'flex';
    } else {
        window.resetFilaArchivo(inputElement, cardElement);
    }
};

window.resetFilaArchivo = function (input, card) {
    input.value = '';
    card.classList.remove('has-file');
    const nameEl = card.querySelector('.nombre-archivo-moderno');
    if (nameEl) nameEl.textContent = '...';

    // 🏗️ Restauramos la vista por defecto (Botón de subida normal)
    const placeholder = card.querySelector('.placeholder-info');
    const fileInfo = card.querySelector('.file-info');
    if (placeholder) placeholder.style.display = 'flex';
    if (fileInfo) fileInfo.style.display = 'none';
};

window.inicializarDragAndDrop = function () {
    document.querySelectorAll('.file-input-hidden').forEach(input => {
        const clone = input.cloneNode(true);
        input.parentNode.replaceChild(clone, input);
        clone.addEventListener('change', function () {
            window.procesarArchivoUI(this.files[0], this, document.querySelector(`label.file-card[for="${this.id}"]`));
        });
    });

    document.querySelectorAll('.file-card').forEach(card => {
        const clone = card.cloneNode(true);
        card.parentNode.replaceChild(clone, card);
        clone.addEventListener('dragover', (e) => { e.preventDefault(); clone.classList.add('drag-over'); });
        clone.addEventListener('dragleave', (e) => { e.preventDefault(); clone.classList.remove('drag-over'); });
        clone.addEventListener('drop', (e) => {
            e.preventDefault(); clone.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                const input = document.getElementById(clone.getAttribute('for'));
                input.files = e.dataTransfer.files;
                window.procesarArchivoUI(input.files[0], input, clone);
            }
        });

        const btnQuitar = clone.querySelector('.quitar-archivo-moderno');
        if (btnQuitar) {
            btnQuitar.addEventListener('click', function (e) {
                e.preventDefault();
                window.resetFilaArchivo(document.getElementById(clone.getAttribute('for')), clone);
            });
        }
    });
};


// =====================================================================
// 2. MÓDULO: VISTA CREAR SOLICITUD
// =====================================================================
function initCrearView() {
    let rowCount = 0;
    const MAX_ITEMS = 100;
    let formModificado = false;

    const form = document.getElementById('purchaseRequestForm');

    // 🏗️ CORRECCIÓN: Faltaba volver a declarar estas 3 variables que usa el validador del Submit
    const selectEmpresa = document.getElementById('empresa');
    const selectCC = document.getElementById('centroCostos');
    const selectCat = document.getElementById('categoria');

    document.addEventListener('input', function (e) {
        if (e.target.tagName.toLowerCase() === 'textarea') autoResizeTextarea(e.target);
    });

    form.addEventListener('input', () => formModificado = true);
    form.addEventListener('change', () => formModificado = true);
    window.addEventListener('beforeunload', (e) => {
        if (formModificado) { e.preventDefault(); e.returnValue = 'Tiene cambios sin guardar.'; }
    });

    // 1. Inicializa Select2 limpiamente
    $('.select2-busqueda').select2({ width: '100%', language: { noResults: () => "No se encontraron resultados" } });

    // 2. Poblamos Empresa y Categoría
    fetchData('json.php?c=catalog&a=catalogo&cat=empresa_user', document.getElementById('empresa'));
    fetchData('json.php?c=catalog&a=catalogo&cat=categoria_compra', document.getElementById('categoria'));

    // 3. 🏗️ SOLUCIÓN AL BUG: Usamos Eventos de jQuery directos para Select2
    $('#empresa').on('change', function () {
        const idEmp = $(this).val() || 0;
        const selectCC = document.getElementById('centroCostos');
        selectCC.innerHTML = '<option value="">Cargando...</option>';

        // Bloquea CC si no hay empresa, o lo habilita
        $(selectCC).prop('disabled', !idEmp).trigger('change.select2');

        if (idEmp > 0) {
            fetchData(`json.php?c=catalog&a=catalogo&cat=cc_user&id=${idEmp}`, selectCC);
        }

        // Llamamos a la traza asíncrona
        const idCc = $('#centroCostos').val() || 0;
        const idCat = $('#categoria').val() || 0;
        window.cargarTrazabilidad(idEmp, idCc, idCat);
    });

    $('#centroCostos').on('change', function () {
        const idEmp = $('#empresa').val() || 0;
        const idCc = $(this).val() || 0;
        const idCat = $('#categoria').val() || 0;
        window.cargarTrazabilidad(idEmp, idCc, idCat);
    });

    $('#categoria').on('change', function () {
        const idEmp = $('#empresa').val() || 0;
        const idCc = $('#centroCostos').val() || 0;
        const idCat = $(this).val() || 0;
        window.cargarTrazabilidad(idEmp, idCc, idCat);

        // Validamos si ya hay productos agregados al cambiar categoría
        const confirmados = document.querySelectorAll('#productosContainer .fila-producto[data-estado="confirmado"]');
        if (confirmados.length > 0) {
            mostrarAlerta('Cambió la categoría pero ya tiene productos agregados. Revise sus ítems.', 'error');
        }
    });

    // Limpia alertas rojas
    document.getElementById('observacion').addEventListener('input', function () { this.classList.remove('input-error'); });
    $('#empresa, #centroCostos, #categoria').on('change', function () { $(this).next('.select2-container').removeClass('input-error'); });

    window.inicializarDragAndDrop();

    window.agregarFilaProducto = function () {
        const container = document.getElementById('productosContainer');
        if (container.children.length >= MAX_ITEMS) return;
        rowCount++; const rowId = `producto_row_${rowCount}`;

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
                    <input type="text" name="productos[${rowCount}][prod_codigo]" class="form-control producto-search" placeholder="Buscar..." autocomplete="off">
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
                    <button type="button" class="btn btn-success btn-accion-texto" onclick="confirmarFila('${rowId}')" title="Agregar a la lista"><i class="fas fa-plus"></i> Agregar</button>
                </div>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
        inicializarBuscadorFila(document.getElementById(rowId));
    };



    window.confirmarFila = function (rowId) {
        const row = document.getElementById(rowId);
        const [inputCantidad, inputProducto, inputIdProducto, inputDescripcion] = [row.querySelector('.cantidad-input'), row.querySelector('.producto-search'), row.querySelector('.producto-id-hidden'), row.querySelector('.descripcion-input')];
        const [cantidad, producto, idProducto, descripcion] = [inputCantidad.value, inputProducto.value.trim(), inputIdProducto.value, inputDescripcion.value.trim()];

        if (!cantidad || Number(cantidad) < 1 || !Number.isInteger(Number(cantidad))) { mostrarAlerta('La cantidad debe ser un entero mayor o igual a 1.', 'error'); inputCantidad.classList.add('input-error'); setTimeout(() => inputCantidad.classList.remove('input-error'), 2500); return; }
        if (producto === '' && descripcion === '') { mostrarAlerta('Debe seleccionar un Producto o escribir Descripción.', 'error'); inputProducto.classList.add('input-error'); inputDescripcion.classList.add('input-error'); setTimeout(() => { inputProducto.classList.remove('input-error'); inputDescripcion.classList.remove('input-error'); }, 2500); return; }
        if (producto !== '' && idProducto === '') { mostrarAlerta('Producto ingresado no existe.', 'error'); inputProducto.classList.add('input-error'); setTimeout(() => inputProducto.classList.remove('input-error'), 2500); return; }

        row.dataset.estado = "confirmado";
        inputCantidad.readOnly = true; inputProducto.readOnly = true; inputDescripcion.readOnly = true;
        row.querySelector('.action-buttons').innerHTML = `<label class="hidden-xs hidden-sm">&nbsp;</label><button type="button" class="btn btn-danger btn-accion-texto" onclick="eliminarFila('${rowId}')" title="Eliminar fila"><i class="fas fa-trash-alt"></i> Quitar</button>`;
        actualizarContadorProductos(); agregarFilaProducto();
    };

    window.eliminarFila = function (rowId) {
        document.getElementById(rowId)?.remove();
        actualizarContadorProductos();
        if (!document.querySelector('#productosContainer [data-estado="pendiente"]') && document.getElementById('productosContainer').children.length < MAX_ITEMS) agregarFilaProducto();
    };

    function actualizarContadorProductos() {
        document.getElementById('contador-productos-txt').innerText = `(${document.querySelectorAll('.fila-producto[data-estado="confirmado"]').length} de 100)`;
    }

    function inicializarBuscadorFila(rowElement) {
        const [inputSearch, resultList, inputHiddenId, inputDesc] = [rowElement.querySelector('.producto-search'), rowElement.querySelector('.product-results'), rowElement.querySelector('.producto-id-hidden'), rowElement.querySelector('.descripcion-input')];
        let timeout = null; let currentFocus = -1;

        inputSearch.addEventListener('input', (e) => {
            clearTimeout(timeout); const query = e.target.value; currentFocus = -1; inputHiddenId.value = '';
            if (query.length < 3) { resultList.style.display = 'none'; return; }

            timeout = setTimeout(() => {
                const idCat = document.getElementById('categoria').value;
                if (!idCat) { mostrarAlerta('Seleccione Categoría primero.', 'error'); $('#categoria').next('.select2-container').addClass('input-error'); setTimeout(() => $('#categoria').next('.select2-container').removeClass('input-error'), 2500); resultList.style.display = 'none'; return; }

                const searchData = new FormData(); searchData.append('search', query);
                fetch(`json.php?c=catalog&a=catalogo_search&cat=productos_cat&id=${idCat}`, { method: 'POST', body: searchData })
                    .then(res => res.json()).then(data => renderizarResultados(data)).catch(err => console.error("Error:", err));
            }, 500);
        });

        inputSearch.addEventListener('keydown', function (e) {
            let items = resultList.getElementsByTagName('li');
            if (resultList.style.display === 'none' || items.length === 0) return;
            if (e.key === "ArrowDown") { currentFocus++; manejarFocoVisible(items); }
            else if (e.key === "ArrowUp") { currentFocus--; manejarFocoVisible(items); }
            else if (e.key === "Enter") { e.preventDefault(); if (currentFocus > -1 && items[currentFocus]) items[currentFocus].click(); }
        });

        function renderizarResultados(data) {
            resultList.innerHTML = '';
            if (data.exito && Array.isArray(data.catalogo) && data.catalogo.length > 0) {
                data.catalogo.forEach((item, index) => {
                    const li = document.createElement('li'); li.className = 'list-group-item';
                    li.innerHTML = `<strong>${item.keyValue}</strong><br/> <span class="text-muted-dark fs-11">${item.keyDescription || ''}</span>`;
                    li.addEventListener('mouseover', () => { Array.from(resultList.children).forEach(c => c.classList.remove("active")); li.classList.add('active'); currentFocus = index; });
                    li.addEventListener('click', () => {
                        inputSearch.value = item.keyValue; inputHiddenId.value = item.keyCode; inputDesc.value = item.keyDescription || '';
                        autoResizeTextarea(inputDesc); resultList.style.display = 'none'; inputSearch.classList.remove('input-error');
                    });
                    resultList.appendChild(li);
                });
                resultList.style.display = 'block'; currentFocus = 0; manejarFocoVisible(resultList.children);
            } else { resultList.innerHTML = '<li class="list-group-item text-muted border-none fs-11" style="padding:10px 15px;">Sin resultados...</li>'; resultList.style.display = 'block'; currentFocus = -1; }
        }

        function manejarFocoVisible(items) {
            Array.from(items).forEach(i => i.classList.remove("active"));
            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (items.length - 1);
            items[currentFocus].classList.add("active");
            items[currentFocus].scrollIntoView({ block: "nearest", behavior: "smooth" });
        }
    }

    document.addEventListener('click', (e) => { if (!e.target.classList.contains('producto-search')) document.querySelectorAll('.contenedor-resultados').forEach(ul => ul.style.display = 'none'); });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (selectEmpresa.value === '') { mostrarAlerta('Seleccione Empresa.', 'error'); $(selectEmpresa).next('.select2-container').addClass('input-error'); return; }
        if (selectCC.value === '') { mostrarAlerta('Seleccione CC.', 'error'); $(selectCC).next('.select2-container').addClass('input-error'); return; }
        if (selectCat.value === '') { mostrarAlerta('Seleccione Categoría.', 'error'); $(selectCat).next('.select2-container').addClass('input-error'); return; }

        const pendiente = document.querySelector('#productosContainer [data-estado="pendiente"]');
        if (document.querySelectorAll('#productosContainer [data-estado="confirmado"]').length === 0) {
            mostrarAlerta('Debe Agregar (+) al menos un producto.', 'error'); if (pendiente) pendiente.querySelector('.btn-success').focus(); return;
        }

        const obs = document.getElementById('observacion');
        if (obs.value.trim() === '') { mostrarAlerta('Observación obligatoria.', 'error'); obs.classList.add('input-error'); obs.focus(); return; }

        if (pendiente) pendiente.querySelectorAll('input, textarea, button').forEach(el => el.disabled = true);
        const formData = new FormData(form);
        if (pendiente) pendiente.querySelectorAll('input, textarea, button').forEach(el => el.disabled = false);

        try {
            const response = await fetch('json.php?c=compras&a=enviar', { method: 'POST', body: formData });
            const data = await response.json();
            if (response.ok && data.exito) { formModificado = false; mostrarModalExito(data.numero_correlativo); }
            else { mostrarAlerta(data.msj || 'Error procesando solicitud.', 'error'); }
        } catch (error) { mostrarAlerta('Error conectando al servidor.', 'error'); }
    });

    // Inyecta la primera fila vacía al cargar la pantalla
    agregarFilaProducto();

    // 🏗️ NUEVO: Dispara la trazabilidad en "Cero" al cargar la pantalla
    // para pintar el esqueleto con el Solicitante Cco. en color naranja.
    window.cargarTrazabilidad(0, 0, 0);
}

// =====================================================================
// 3. MÓDULO: VISTA CONSULTA DE COTIZACIONES
// =====================================================================
let paginaActualConsulta = 1;

window.cambiarPaginaConsulta = function (nuevaPag) {
    paginaActualConsulta = nuevaPag;
    cargarDatosConsulta();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.iniciarCotizacion = function (id) {
    window.location.href = `?c=solicitud&a=cotizar&id=${id}`;
};

async function cargarDatosConsulta() {
    const loader = document.getElementById('loader');
    if (loader) loader.style.display = 'flex';

    const form = document.getElementById('formFiltros');
    const formData = new FormData(form);
    formData.append('pagina', paginaActualConsulta);

    const periodo = formData.get('periodo');
    if (periodo === 'todos') { formData.append('anio', ''); formData.append('mes', ''); }
    else if (periodo) { const [anio, mes] = periodo.split('-'); formData.append('anio', anio); formData.append('mes', mes); }
    formData.delete('periodo');

    try {
        const [res] = await Promise.all([
            fetch('json.php?c=compras&a=consulta_cotizacion', { method: 'POST', body: formData }),
            new Promise(resolve => setTimeout(resolve, 350))
        ]);
        const json = await res.json();
        if (json.exito) {
            renderizarTablaConsulta(json.data);
            renderizarPaginacionConsulta(json.paginacion);
        } else { mostrarAlerta(json.msj || 'Error obteniendo datos', 'error'); }
    } catch (err) { console.error(err); mostrarAlerta('Error de red al consultar.', 'error'); }
    finally { if (loader) loader.style.display = 'none'; }
}

function renderizarTablaConsulta(data) {
    const tbody = document.getElementById('tablaBody');

    if (!document.getElementById('estilo-adjuntos-dropdown')) {
        const style = document.createElement('style'); style.id = 'estilo-adjuntos-dropdown';
        style.innerHTML = `.table-moderna td.text-center .btn-group { position: relative; } .adjuntos-dropdown { right: 0 !important; left: auto !important; } .adjuntos-dropdown a:hover { background-color: #f1f5f9 !important; color: #0f172a !important; }`;
        document.head.appendChild(style);
    }

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted-light" style="padding: 40px 0;"><i class="fas fa-inbox empty-state-icon"></i><br><span class="fs-15 text-bold">No hay cotizaciones pendientes</span><br><span class="fs-13">Ajusta los filtros.</span></td></tr>`;
        return;
    }

    let filasHtml = '';

    data.forEach(item => {
        // 🏗️ RENDERIZADO DINÁMICO DE ADJUNTOS (Agrupado por Origen)
        let htmlAdjuntos = '<span class="text-muted-lighter fs-11 text-bold">N/A</span>';
        if (item.adjuntos && item.adjuntos.length > 0) {
            // Filtramos los adjuntos por su tipo
            let adjSolicitante = item.adjuntos.filter(a => ['S1', 'S2', 'S3'].includes(a.adjunto_tipo));
            let adjAnalista = item.adjuntos.filter(a => a.adjunto_tipo === 'CC');

            htmlAdjuntos = `<div class="btn-group">
                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 4px; border: 1px solid #cbd5e1; background: white; color: #475569; font-size: 11px; padding: 4px 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <i class="fas fa-paperclip mr-3 text-primary-blue"></i> ${item.adjuntos.length} <span class="caret" style="margin-left: 4px;"></span>
                </button>
                <ul class="dropdown-menu adjuntos-dropdown shadow-sm" style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 0; min-width: 230px; z-index: 1050;">`;

            // Función Helper para dibujar cada ítem (DRY - Don't Repeat Yourself)
            const renderItemAdjunto = (adj) => {
                let icon = (adj.ruta && adj.ruta.toLowerCase().endsWith('pdf')) ? 'fa-file-pdf text-danger' : 'fa-file-excel text-success';
                return `<li>
                    <a href="uploads/compras/${adj.ruta}" target="_blank" title="${adj.nombre_archivo}" style="display: flex; align-items: center; padding: 6px 15px; font-size: 11px; color: #334155; text-decoration: none;">
                        <i class="fas ${icon}" style="width: 16px; text-align: center; margin-right: 8px; font-size: 13px;"></i>
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-grow: 1;">${adj.nombre_archivo}</span>
                    </a>
                </li>`;
            };

            // 1. Grupo: Solicitante
            if (adjSolicitante.length > 0) {
                htmlAdjuntos += `<li class="dropdown-header text-bold text-muted-dark fs-10 text-uppercase" style="padding: 4px 15px; letter-spacing: 0.5px;"><i class="fas fa-user mr-2"></i> Adjuntos Solicitante</li>
                                 <li role="separator" class="divider" style="margin: 4px 0; background-color: #e2e8f0;"></li>`;
                adjSolicitante.forEach(adj => { htmlAdjuntos += renderItemAdjunto(adj); });
            }

            // 2. Grupo: Analista de Compras
            if (adjAnalista.length > 0) {
                // Si ya dibujamos los del solicitante, agregamos un separador más grueso
                if (adjSolicitante.length > 0) {
                    htmlAdjuntos += `<li role="separator" class="divider" style="margin: 8px 0; background-color: #cbd5e1;"></li>`;
                }
                htmlAdjuntos += `<li class="dropdown-header text-bold fs-10 text-uppercase" style="padding: 4px 15px; letter-spacing: 0.5px; color: #0284c7;"><i class="fas fa-user-edit mr-2"></i> Analista de Compras</li>
                                 <li role="separator" class="divider" style="margin: 4px 0; background-color: #e2e8f0;"></li>`;
                adjAnalista.forEach(adj => { htmlAdjuntos += renderItemAdjunto(adj); });
            }

            htmlAdjuntos += `</ul></div>`;
        }

        let htmlObservacion = '';
        if (item.observacion_crea && item.observacion_crea.trim() !== '') {
            const obsLimpia = item.observacion_crea.replace(/"/g, '&quot;');
            htmlObservacion = `<div class="obs-box" title="${obsLimpia}"><i class="fas fa-comment-dots text-muted-lighter mr-3"></i>${item.observacion_crea}</div>`;
        }

        let fechaFormateada = item.fecha_crea;
        if (item.fecha_crea) { const p = item.fecha_crea.split('-'); if (p.length === 3) fechaFormateada = `${p[2]}/${p[1]}/${p[0]}`; }

        let horaFormateada = item.hora_crea;
        if (item.hora_crea) { const p = item.hora_crea.split(':'); if (p.length >= 2) horaFormateada = `${p[0]}:${p[1]}`; }

        filasHtml += `
        <tr>
            <td><strong class="text-muted-light fs-11">#${item.id}</strong></td>
            <td>
                <div class="text-bold text-dark fs-13">${item.emp_nombre}</div>
                <div class="text-muted-light fs-11 mt-2">${item.cc_descripcion}</div>
            </td>
            <td><span class="badge-cat">${item.cat_descripcion}</span>${htmlObservacion}</td>
            <td>
                <div class="text-bold text-dark fs-12 white-space-nowrap">
                    <i class="far fa-calendar-alt text-muted-light mr-4"></i>${fechaFormateada}
                    <span class="text-muted-lighter ml-5 mr-4">|</span>
                    <i class="far fa-clock text-muted-lighter mr-4"></i>${horaFormateada}
                </div>
                <div class="text-muted-light fs-11 mt-4"><i class="fas fa-user text-muted-lighter mr-4"></i>${item.usuario_crea}</div>
            </td>
            <td>${obtenerTiempoTranscurrido(item.fecha_crea, item.hora_crea)}</td>
            <td><span class="step-badge step-badge-pending fs-11">Por Cotizar</span></td>
            <td class="text-center white-space-nowrap">${htmlAdjuntos}</td>
            <td class="text-center">
                <button type="button" class="btn-cotizar" onclick="iniciarCotizacion(${item.id})">
                    <i class="fas fa-file-invoice-dollar mr-4"></i> Cotizar
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = filasHtml;
}

function renderizarPaginacionConsulta(pag) {
    document.getElementById('paginacionInfo').innerHTML = `Total de registros: <strong>${pag.total_registros}</strong>`;
    const divBotones = document.getElementById('paginacionBotones'); divBotones.innerHTML = '';
    if (pag.total_paginas <= 1) return;

    divBotones.innerHTML += `<button onclick="cambiarPaginaConsulta(${pag.actual - 1})" ${pag.actual === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
    for (let i = 1; i <= pag.total_paginas; i++) {
        if (i === 1 || i === pag.total_paginas || (i >= pag.actual - 2 && i <= pag.actual + 2)) {
            divBotones.innerHTML += `<button class="${i === pag.actual ? 'active' : ''}" onclick="cambiarPaginaConsulta(${i})">${i}</button>`;
        } else if (i === pag.actual - 3 || i === pag.actual + 3) {
            divBotones.innerHTML += `<button disabled>...</button>`;
        }
    }
    divBotones.innerHTML += `<button onclick="cambiarPaginaConsulta(${pag.actual + 1})" ${pag.actual === pag.total_paginas ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
}

function initConsultaView() {
    inicializarPeriodos('periodo', true);
    $('.select2-consulta').select2({ width: '100%' });

    if (typeof fetchData === 'function') {
        fetchData('json.php?c=catalog&a=catalogo&cat=empresa_user', document.getElementById('empresa'), 'Todas las Empresas');
        fetchData('json.php?c=catalog&a=catalogo&cat=categoria_compra', document.getElementById('categoria'), 'Todas las Categorías');
    }

    $('.select2-consulta').on('select2:select', function () { this.dispatchEvent(new Event('change', { bubbles: true })); });

    document.getElementById('empresa').addEventListener('change', (e) => {
        const selectCC = document.getElementById('centroCostos');
        selectCC.innerHTML = '<option value="">Todos los CC</option>';
        if (e.target.value) {
            $(selectCC).prop('disabled', false);
            if (typeof fetchData === 'function') fetchData(`json.php?c=catalog&a=catalogo&cat=cc_user&id=${e.target.value}`, selectCC, 'Todos los CC');
        } else { $(selectCC).prop('disabled', true).trigger('change.select2'); }
    });

    document.getElementById('formFiltros').addEventListener('submit', (e) => {
        e.preventDefault(); paginaActualConsulta = 1; cargarDatosConsulta();
    });

    cargarDatosConsulta();
}

// =====================================================================
// 4. MÓDULO: VISTA COTIZAR (ANALISTA)
// =====================================================================
function initCotizarView() {
    const idSol = document.getElementById('id_prehsol').value;
    const idEmp = document.getElementById('id_empresa_solicitud').value;
    const monedaSel = document.getElementById('moneda_cot');

    // 🏗️ ENCENDEMOS EL MOTOR DE DRAG & DROP PARA EL CUADRO COMPARATIVO
    window.inicializarDragAndDrop();

    async function cargarTrazabilidadReal() {
        const formData = new FormData();
        formData.append('id_sol', idSol);
        try {
            const res = await fetch('json.php?c=trazabilidad&a=obtener_por_solicitud', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.exito && data.trazabilidad) {
                renderizarTraza(data.trazabilidad);
            }
        } catch (e) { console.error("Error traza:", e); }
    }

    function actualizarLineasProgreso() {
        const nodos = Array.from(document.querySelectorAll('.step-node')).filter(n => n.style.display !== 'none');
        const totalVisible = nodos.length;
        if (totalVisible === 0) return;

        let ultimoCompletado = -1;
        let activo = -1;

        nodos.forEach((nodo, i) => {
            if (nodo.classList.contains('done')) ultimoCompletado = i;
            if (nodo.classList.contains('active')) activo = i;
        });

        let progresoVerde = 0;
        let progresoNaranja = 0;

        if (ultimoCompletado >= 0) { progresoVerde = ultimoCompletado === totalVisible - 1 ? 100 : ((ultimoCompletado + 1) / totalVisible) * 100; }
        if (activo >= 0) {
            let finNaranja = activo === totalVisible - 1 ? 100 : ((activo + 1) / totalVisible) * 100;
            progresoNaranja = finNaranja - progresoVerde;
            if (progresoNaranja < 0) progresoNaranja = 0;
        }

        const lineaVerde = document.getElementById('linea-verde-progreso');
        const lineaNaranja = document.getElementById('linea-naranja-progreso');

        if (lineaVerde) lineaVerde.style.width = `${progresoVerde}%`;
        if (lineaNaranja) {
            lineaNaranja.style.left = `${progresoVerde}%`;
            lineaNaranja.style.width = `${progresoNaranja}%`;
        }
    }

    function renderizarTraza(trazas) {
        const contenedor = document.getElementById('tracker-contenedor');
        if (!contenedor) return;

        let html = '';
        const iconMap = {
            'Solicitante Cco.': 'fa-solid fa-user', 'Cotización': 'fa-solid fa-file-invoice-dollar',
            'Autorizador Cco.': 'fa-solid fa-user-check', 'Autorizador Categoría': 'fa-solid fa-user-tag',
            'Autorizador >= $5K': 'fa-solid fa-user-shield', 'Orden de Compra': 'fa-solid fa-shopping-cart',
            'Revisión OC': 'fa-solid fa-clipboard-check', 'OC en Proveedor': 'fa-solid fa-truck',
            'Recepción': 'fa-solid fa-box-open', 'Cerrar OC': 'fa-solid fa-lock'
        };

        trazas.forEach((paso) => {
            let clase = 'pending', iconStatus = '', badgeClass = 'step-badge-pending', fechaInfo = '';

            if (paso.resolucion === 'C' || paso.descripcion === 'Completado' || paso.descripcion === 'Solicitado') {
                clase = 'done'; iconStatus = '<i class="fas fa-check"></i>'; badgeClass = 'step-badge-done';
                if (paso.fecha && paso.hora) fechaInfo = `<br><span style="font-size:9px; color:#64748b;"><i class="far fa-calendar-alt"></i> ${paso.fecha} ${paso.hora}</span>`;
            } else if (paso.resolucion === 'A' || paso.descripcion === 'En proceso' || paso.descripcion === 'En Proceso') {
                clase = 'active'; iconStatus = '<i class="fas fa-clock"></i>'; badgeClass = 'step-badge-active';
            }

            const faIcon = iconMap[paso.estado_descr] || 'fa-solid fa-circle';
            let isHidden = (paso.active == 0 || paso.active == '0') ? 'display: none;' : '';
            let is5K = (paso.estado == 41 || paso.orden == 41);
            let extraId = is5K ? 'id="step-5k"' : '';
            let dataActive = `data-active="${paso.active}"`;

            html += `<div class="step-node ${clase}" ${extraId} ${dataActive} style="padding: 0 5px; ${isHidden}">
                <div class="step-dot" style="width: 36px; height: 36px; font-size: 16px; margin: 0 auto 6px auto;"><i class="${faIcon}"></i><div class="status-overlay" style="width: 14px; height: 14px; font-size: 8px; bottom: -2px; right: -2px;">${iconStatus}</div></div>
                <div class="step-label">
                    <h4 style="font-size: 11px; margin: 0 0 2px 0;">${paso.estado_descr}</h4>
                    <p class="m-0" style="font-size: 10px; line-height: 1.2;">
                        <span class="text-muted-dark text-bold">${paso.nom_usuario}</span><br>
                        <span class="step-badge ${badgeClass}" style="padding: 2px 6px; font-size: 9px; margin-top:2px;">${paso.descripcion}</span>
                        ${fechaInfo}
                    </p>
                </div>
            </div>`;
        });

        contenedor.style.cssText = "align-items: flex-start; position: relative;";
        contenedor.innerHTML = `
            <style>.tracker::before { top: 17px !important; }</style>
            <div id="linea-verde-progreso" style="position: absolute; top: 17px; left: 0; height: 4px; z-index: 1; width: 0%; background-color: #28a745 !important; transition: width 0.5s ease;"></div>
            <div id="linea-naranja-progreso" style="position: absolute; top: 17px; left: 0%; height: 4px; z-index: 1; width: 0%; background-color: #ffc107 !important; transition: width 0.5s ease, left 0.5s ease;"></div>
            ${html}
        `;

        actualizarLineasProgreso();
    }

    cargarTrazabilidadReal();

    let timeoutCalculo = null;
    const idCategoriaInput = document.getElementById('id_categoria_solicitud');

    const calcular = () => {
        let total = 0;
        document.querySelectorAll('.fila-cotizacion').forEach(f => {
            let c = parseFloat(f.dataset.cantidad) || 0;
            let p = parseFloat(f.querySelector('.precio-input').value) || 0;
            let sub = c * p;
            f.querySelector('.sub-txt').textContent = sub.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            total += sub;
        });
        document.getElementById('totalGlobal').textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const step5k = document.getElementById('step-5k');
        if (step5k) {
            let isParametrized = step5k.getAttribute('data-active') == '1';
            if (total >= 5000 && isParametrized) { step5k.style.display = ''; } else { step5k.style.display = 'none'; }
            actualizarLineasProgreso();
        }

        if (idCategoriaInput && total >= 0) {
            clearTimeout(timeoutCalculo);
            timeoutCalculo = setTimeout(async () => {
                const fd = new FormData(); fd.append('id_categoria', idCategoriaInput.value); fd.append('total', total);
                try {
                    const res = await fetch('json.php?c=compras&a=obtener_autorizador', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.exito) {
                        document.querySelectorAll('.step-label').forEach(label => {
                            const tituloNode = label.querySelector('h4');
                            if (tituloNode) {
                                const tituloTexto = tituloNode.textContent.trim();
                                const spanNombre = label.querySelector('.text-muted-dark');

                                if (data.autorizador && (tituloTexto === 'Autorizador Categoría' || tituloTexto === 'Autorizador Categoria')) {
                                    if (spanNombre && spanNombre.textContent !== data.autorizador.usr_nombre) {
                                        spanNombre.style.opacity = '0';
                                        setTimeout(() => { spanNombre.textContent = data.autorizador.usr_nombre; spanNombre.style.color = '#0056b3'; spanNombre.style.opacity = '1'; setTimeout(() => spanNombre.style.color = '', 1500); }, 200);
                                    }
                                }

                                if (tituloTexto === 'Autorizador >= $5K' || tituloTexto === 'Autorizador >= $5k') {
                                    if (data.autorizador_5k && total >= 5000) {
                                        if (step5k) step5k.style.display = '';
                                        if (spanNombre && spanNombre.textContent !== data.autorizador_5k.usr_nombre) {
                                            spanNombre.style.opacity = '0';
                                            setTimeout(() => { spanNombre.textContent = data.autorizador_5k.usr_nombre; spanNombre.style.color = '#ef4444'; spanNombre.style.opacity = '1'; setTimeout(() => spanNombre.style.color = '', 1500); }, 200);
                                        }
                                    } else { if (step5k) step5k.style.display = 'none'; }
                                }
                            }
                        });
                        actualizarLineasProgreso();
                    }
                } catch (err) { console.error("Error obteniendo autorizador:", err); }
            }, 600);
        }
    };

    document.querySelectorAll('.precio-input').forEach(i => i.addEventListener('input', calcular));
    if (monedaSel) monedaSel.addEventListener('change', (e) => { document.querySelectorAll('.simb-mon').forEach(s => s.textContent = e.target.value); });

    calcular();

    document.querySelectorAll('.fila-cotizacion').forEach(fila => {
        const input = fila.querySelector('.proveedor-search');
        const results = fila.querySelector('.contenedor-resultados');
        const hidden = fila.querySelector('.proveedor-id-hidden');

        if (!input.readOnly && results) {
            let timer = null; let currentFocus = -1;
            input.addEventListener('input', (e) => {
                clearTimeout(timer); const val = e.target.value; currentFocus = -1; hidden.value = '';
                if (val.length < 3) { results.style.display = 'none'; return; }
                timer = setTimeout(() => {
                    const searchData = new FormData(); searchData.append('search', val);
                    fetch(`json.php?c=catalog&a=catalogo_search&cat=proveedor_as400&id_emp=${idEmp}`, { method: 'POST', body: searchData })
                        .then(r => r.json())
                        .then(data => {
                            results.innerHTML = '';
                            if (data.exito && Array.isArray(data.catalogo) && data.catalogo.length > 0) {
                                data.catalogo.forEach((item, index) => {
                                    const li = document.createElement('li'); li.className = 'list-group-item';
                                    li.innerHTML = `<strong>${item.keyValue}</strong><br/> <span class="text-muted-dark fs-11">Código AS400: ${item.keyCode}</span>`;
                                    li.addEventListener('mouseover', () => { Array.from(results.children).forEach(c => c.classList.remove("active")); li.classList.add('active'); currentFocus = index; });
                                    li.onclick = () => { input.value = item.keyValue; hidden.value = item.keyCode; results.style.display = 'none'; input.classList.remove('input-error'); };
                                    results.appendChild(li);
                                });
                                results.style.display = 'block'; currentFocus = 0; manejarFocoVisible(results.children);
                            } else { results.innerHTML = '<li class="list-group-item text-muted border-none fs-11" style="padding:10px 15px;">Sin resultados...</li>'; results.style.display = 'block'; currentFocus = -1; }
                        }).catch(err => console.error(err));
                }, 400);
            });

            input.addEventListener('keydown', function (e) {
                let items = results.getElementsByTagName('li');
                if (results.style.display === 'none' || items.length === 0) return;
                if (e.key === "ArrowDown") { currentFocus++; manejarFocoVisible(items); }
                else if (e.key === "ArrowUp") { currentFocus--; manejarFocoVisible(items); }
                else if (e.key === "Enter") { e.preventDefault(); if (currentFocus > -1 && items[currentFocus]) items[currentFocus].click(); }
            });

            function manejarFocoVisible(items) {
                Array.from(items).forEach(i => i.classList.remove("active"));
                if (currentFocus >= items.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = (items.length - 1);
                items[currentFocus].classList.add("active");
                items[currentFocus].scrollIntoView({ block: "nearest", behavior: "smooth" });
            }

            document.addEventListener('click', (e) => { if (e.target !== input) results.style.display = 'none'; });
        }

        const btnGuardar = fila.querySelector('.btn-guardar-item');
        if (btnGuardar) {
            btnGuardar.addEventListener('click', async () => {
                const precioInput = fila.querySelector('.precio-input');
                const precio = precioInput.value;
                const prov_cod = hidden.value;
                const observacion = fila.querySelector('.obs-item').value;
                const id_predsol = fila.getAttribute('data-id');
                const cant = fila.getAttribute('data-cantidad');
                const id_prehsol = document.getElementById('id_prehsol').value;
                const textoProv = input.value.replace(/\s+/g, '');

                input.classList.remove('input-error'); precioInput.classList.remove('input-error');

                if (textoProv === '' || !prov_cod) { mostrarAlerta('Debe seleccionar un proveedor válido del listado.', 'error'); input.classList.add('input-error'); return; }
                if (!precio || parseFloat(precio) <= 0) { mostrarAlerta('Debe ingresar un precio mayor a 0.', 'error'); precioInput.classList.add('input-error'); return; }

                btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; btnGuardar.disabled = true;

                const fd = new FormData(); fd.append('id_prehsol', id_prehsol); fd.append('id_predsol', id_predsol); fd.append('prov_cod', prov_cod); fd.append('precio', precio); fd.append('cantidad', cant); fd.append('observacion', observacion);

                try {
                    const res = await fetch('json.php?c=compras&a=guardar_item_cotizacion', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.exito) {
                        mostrarAlerta(data.msj, 'success');
                        btnGuardar.classList.replace('btn-primary', 'btn-success'); btnGuardar.innerHTML = '<i class="fas fa-check"></i>';
                        setTimeout(() => { btnGuardar.classList.replace('btn-success', 'btn-primary'); btnGuardar.innerHTML = '<i class="fas fa-sync-alt"></i>'; btnGuardar.disabled = false; }, 2000);
                    } else throw new Error(data.msj);
                } catch (error) {
                    mostrarAlerta(error.message || 'Error guardando ítem', 'error');
                    btnGuardar.innerHTML = '<i class="fas fa-sync-alt"></i>'; btnGuardar.disabled = false;
                }
            });
        }
    });

    const formCotizacion = document.getElementById('formCotizacion');
    if (formCotizacion) {
        formCotizacion.addEventListener('submit', async function (e) {
            e.preventDefault();

            const btnSubmit = e.target.querySelector('button[type="submit"]');
            if (!btnSubmit) return;

            let itemsValidos = true, obsValida = true;

            document.querySelectorAll('.fila-cotizacion').forEach(fila => {
                const hidden = fila.querySelector('.proveedor-id-hidden');
                const search = fila.querySelector('.proveedor-search');
                const precio = fila.querySelector('.precio-input');
                const provText = search.value.replace(/\s+/g, '');

                if (!hidden.value || provText === '') { search.classList.add('input-error'); itemsValidos = false; }
                if (!precio.value || parseFloat(precio.value) <= 0) { precio.classList.add('input-error'); itemsValidos = false; }
            });

            const obs = document.getElementById('observacion_analista');
            if (obs.value.trim() === '') { obs.classList.add('input-error'); obsValida = false; }

            if (!itemsValidos && !obsValida) { mostrarAlerta('Completar proveedor y precio de c/u de los items.', 'error'); return; }
            else if (!itemsValidos) { mostrarAlerta('Completar proveedor y precio de c/u de los items.', 'error'); return; }
            else if (!obsValida) { mostrarAlerta('Completar observación general de analista.', 'error'); return; }

            try {
                btnSubmit.disabled = true; btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-4"></i> Procesando...';
                const formData = new FormData(this);
                const response = await fetch('json.php?c=compras&a=guardar_cotizacion', { method: 'POST', body: formData });
                const textResponse = await response.text();
                let data;

                try { data = JSON.parse(textResponse); } catch (jsonError) { throw new Error("Error interno del servidor."); }

                if (response.ok && data.exito) {
                    mostrarAlerta(data.msj, 'success');
                    setTimeout(() => window.location.href = '?c=solicitud&a=consulta_cotizacion', 1500);
                } else throw new Error(data.msj);
            } catch (error) {
                mostrarAlerta(error.message || 'Error de conexión', 'error');
                btnSubmit.disabled = false; btnSubmit.innerHTML = '<i class="fas fa-paper-plane mr-4"></i> Enviar Aprobación';
            }
        });
    }
}

// =====================================================================
// 5. MÓDULO: VISTA BANDEJA DE APROBACIÓN CCO
// =====================================================================
let paginaActualAprobCC = 1;

window.cambiarPaginaAprobCC = function (nuevaPag) {
    paginaActualAprobCC = nuevaPag;
    cargarDatosAprobCC();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.iniciarAprobacionCC = function (id) {
    window.location.href = `?c=solicitud&a=revisar_cotizacion_cc&id=${id}`;
};

async function cargarDatosAprobCC() {
    const loader = document.getElementById('loader');
    if (loader) loader.style.display = 'flex';

    const form = document.getElementById('formFiltrosAprobCC');
    const formData = new FormData(form);
    formData.append('pagina', paginaActualAprobCC);

    const periodo = formData.get('periodo');
    if (periodo === 'todos') { formData.append('anio', ''); formData.append('mes', ''); }
    else if (periodo) { const [anio, mes] = periodo.split('-'); formData.append('anio', anio); formData.append('mes', mes); }
    formData.delete('periodo');

    try {
        const [res] = await Promise.all([
            fetch('json.php?c=compras&a=consulta_aprobacion_cc', { method: 'POST', body: formData }),
            new Promise(resolve => setTimeout(resolve, 350))
        ]);
        const json = await res.json();
        if (json.exito) {
            renderizarTablaAprobCC(json.data);
            renderizarPaginacionConsulta(json.paginacion);
        } else { mostrarAlerta(json.msj || 'Error obteniendo datos', 'error'); }
    } catch (err) { console.error(err); mostrarAlerta('Error de red al consultar.', 'error'); }
    finally { if (loader) loader.style.display = 'none'; }
}

function renderizarTablaAprobCC(data) {
    const tbody = document.getElementById('tablaBodyAprobCC');

    if (!document.getElementById('estilo-adjuntos-dropdown')) {
        const style = document.createElement('style'); style.id = 'estilo-adjuntos-dropdown';
        style.innerHTML = `.table-moderna td.text-center .btn-group { position: relative; } .adjuntos-dropdown { right: 0 !important; left: auto !important; } .adjuntos-dropdown a { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px; text-align: left !important; color: #334155 !important; text-decoration: none; padding: 6px 15px !important; font-size: 11px; transition: all 0.2s ease; } .adjuntos-dropdown a:hover { background-color: #e0f2fe !important; color: #0284c7 !important; } .adjuntos-dropdown .dropdown-header { text-align: left !important; padding: 4px 15px; }`;
        document.head.appendChild(style);
    }

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted-light" style="padding: 40px 0;"><i class="fas fa-inbox empty-state-icon"></i><br><span class="fs-15 text-bold">Tu bandeja está al día</span><br><span class="fs-13">No tienes solicitudes pendientes de autorizar.</span></td></tr>`;
        return;
    }

    let filasHtml = '';

    data.forEach(item => {
        let htmlObservacion = '';
        if (item.observacion_crea && item.observacion_crea.trim() !== '') {
            const obsLimpia1 = item.observacion_crea.replace(/"/g, '&quot;');
            htmlObservacion += `<div class="obs-box" title="${obsLimpia1}"><i class="fas fa-comment-dots text-muted-lighter mr-3"></i>${item.observacion_crea}</div>`;
        }
        if (item.observacion_cotiza && item.observacion_cotiza.trim() !== '') {
            const obsLimpia2 = item.observacion_cotiza.replace(/"/g, '&quot;');
            htmlObservacion += `<div class="obs-box" title="${obsLimpia2}" style="margin-top: 2px;"><i class="fas fa-user-edit text-muted-lighter mr-3"></i>${item.observacion_cotiza}</div>`;
        }

        let fechaFormateada = item.fecha_crea ? item.fecha_crea.split('-').reverse().join('/') : '';
        let horaFormateada = item.hora_crea ? item.hora_crea.split(':').slice(0, 2).join(':') : '';

        let fechaAnalista = item.fecha_cotiza ? item.fecha_cotiza.split('-').reverse().join('/') : '';
        let horaAnalista = item.hora_cotiza ? item.hora_cotiza.split(':').slice(0, 2).join(':') : '';
        const analistaNombre = item.usuario_cotiza || 'Analista';

        const montoFormateado = parseFloat(item.monto_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const moneda = item.moneda || '$';

        // 🏗️ RENDERIZADO DINÁMICO DE ADJUNTOS (Agrupado por Origen)
        let htmlAdjuntos = '<span class="text-muted-lighter fs-11 text-bold">N/A</span>';
        if (item.adjuntos && item.adjuntos.length > 0) {
            // Filtramos los adjuntos por su tipo
            let adjSolicitante = item.adjuntos.filter(a => ['S1', 'S2', 'S3'].includes(a.adjunto_tipo));
            let adjAnalista = item.adjuntos.filter(a => a.adjunto_tipo === 'CC');

            htmlAdjuntos = `<div class="btn-group">
                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 4px; border: 1px solid #cbd5e1; background: white; color: #475569; font-size: 11px; padding: 4px 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <i class="fas fa-paperclip mr-3 text-primary-blue"></i> ${item.adjuntos.length} <span class="caret" style="margin-left: 4px;"></span>
                </button>
                <ul class="dropdown-menu adjuntos-dropdown shadow-sm" style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 0; min-width: 230px; z-index: 1050;">`;

            // Función Helper para dibujar cada ítem (DRY - Don't Repeat Yourself)
            const renderItemAdjunto = (adj) => {
                let icon = (adj.ruta && adj.ruta.toLowerCase().endsWith('pdf')) ? 'fa-file-pdf text-danger' : 'fa-file-excel text-success';
                return `<li>
                    <a href="uploads/compras/${adj.ruta}" target="_blank" title="${adj.nombre_archivo}" style="display: flex; align-items: center; padding: 6px 15px; font-size: 11px; color: #334155; text-decoration: none;">
                        <i class="fas ${icon}" style="width: 16px; text-align: center; margin-right: 8px; font-size: 13px;"></i>
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-grow: 1;">${adj.nombre_archivo}</span>
                    </a>
                </li>`;
            };

            // 1. Grupo: Solicitante
            if (adjSolicitante.length > 0) {
                htmlAdjuntos += `<li class="dropdown-header text-bold text-muted-dark fs-10 text-uppercase" style="padding: 4px 15px; letter-spacing: 0.5px;"><i class="fas fa-user mr-2"></i> Adjuntos Solicitante</li>
                                 <li role="separator" class="divider" style="margin: 4px 0; background-color: #e2e8f0;"></li>`;
                adjSolicitante.forEach(adj => { htmlAdjuntos += renderItemAdjunto(adj); });
            }

            // 2. Grupo: Analista de Compras
            if (adjAnalista.length > 0) {
                // Si ya dibujamos los del solicitante, agregamos un separador más grueso
                if (adjSolicitante.length > 0) {
                    htmlAdjuntos += `<li role="separator" class="divider" style="margin: 8px 0; background-color: #cbd5e1;"></li>`;
                }
                htmlAdjuntos += `<li class="dropdown-header text-bold fs-10 text-uppercase" style="padding: 4px 15px; letter-spacing: 0.5px; color: #0284c7;"><i class="fas fa-user-edit mr-2"></i> Analista de Compras</li>
                                 <li role="separator" class="divider" style="margin: 4px 0; background-color: #e2e8f0;"></li>`;
                adjAnalista.forEach(adj => { htmlAdjuntos += renderItemAdjunto(adj); });
            }

            htmlAdjuntos += `</ul></div>`;
        }

        filasHtml += `
        <tr>
            <td><strong class="text-muted-light fs-11">#${item.id}</strong></td>
            <td>
                <div class="text-bold text-dark fs-13">${item.emp_nombre}</div>
                <div class="text-muted-light fs-11 mt-2">${item.cc_descripcion}</div>
            </td>
            <td><span class="badge-cat">${item.cat_descripcion}</span>${htmlObservacion}</td>
            <td>
                <div class="text-extrabold text-primary-blue fs-12">${moneda} ${montoFormateado}</div>
            </td>
            <td>
                <div class="text-muted-light fs-11"><i class="fas fa-user text-muted-lighter mr-4"></i>${item.usuario_crea}</div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-calendar-alt text-muted-light mr-4"></i>${fechaFormateada}
                </div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-clock text-muted-lighter mr-4"></i>${horaFormateada}
                </div>
            </td>
            <td>
                <div class="text-muted-light fs-11"><i class="fas fa-user-edit text-primary-blue mr-4"></i>${analistaNombre}</div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-calendar-check text-muted-light mr-4"></i>${fechaAnalista}
                </div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-clock text-muted-lighter mr-4"></i>${horaAnalista}
                </div>
            </td>
            <td>${obtenerTiempoTranscurrido(item.fecha_crea, item.hora_crea)}</td>
            <td class="text-center">${htmlAdjuntos}</td>
            <td class="text-center">
                <button type="button" class="btn-cotizar" style="background-color: #ef4444;" onclick="iniciarAprobacionCC(${item.id})">
                    <i class="fas fa-check-double mr-4"></i> Revisar
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = filasHtml;
}

function initAprobacionCCView() {
    inicializarPeriodos('periodo', true);
    $('.select2-consulta').select2({ width: '100%' });

    if (typeof fetchData === 'function') { fetchData('json.php?c=catalog&a=catalogo&cat=empresa_user', document.getElementById('empresa'), 'Todas las Empresas'); }

    $('.select2-consulta').on('select2:select', function () { this.dispatchEvent(new Event('change', { bubbles: true })); });

    document.getElementById('empresa').addEventListener('change', (e) => {
        const selectCC = document.getElementById('centroCostos');
        selectCC.innerHTML = '<option value="">Todos los CC</option>';
        if (e.target.value) {
            $(selectCC).prop('disabled', false);
            if (typeof fetchData === 'function') fetchData(`json.php?c=catalog&a=catalogo&cat=cc_user&id=${e.target.value}`, selectCC, 'Todos los CC');
        } else { $(selectCC).prop('disabled', true).trigger('change.select2'); }
    });

    document.getElementById('formFiltrosAprobCC').addEventListener('submit', (e) => { e.preventDefault(); paginaActualAprobCC = 1; cargarDatosAprobCC(); });

    cargarDatosAprobCC();
}

// =====================================================================
// 6. MÓDULO: VISTA REVISIÓN AUTORIZADOR CC
// =====================================================================
function initRevisarCotizacionCCView() {
    const idSol = document.getElementById('id_prehsol').value;
    const idCategoriaInput = document.getElementById('id_categoria_solicitud');

    async function cargarTrazabilidadReal() {
        const formData = new FormData(); formData.append('id_sol', idSol);
        try {
            const res = await fetch('json.php?c=trazabilidad&a=obtener_por_solicitud', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.exito && data.trazabilidad) renderizarTraza(data.trazabilidad);
        } catch (e) { console.error(e); }
    }

    function actualizarLineasProgreso() {
        const nodos = Array.from(document.querySelectorAll('.step-node')).filter(n => n.style.display !== 'none');
        const totalVisible = nodos.length;
        if (totalVisible === 0) return;
        let ultimoCompletado = -1, activo = -1;
        nodos.forEach((nodo, i) => {
            if (nodo.classList.contains('done')) ultimoCompletado = i;
            if (nodo.classList.contains('active')) activo = i;
        });
        let progresoVerde = ultimoCompletado >= 0 ? (ultimoCompletado === totalVisible - 1 ? 100 : ((ultimoCompletado + 1) / totalVisible) * 100) : 0;
        let progresoNaranja = activo >= 0 ? ((activo === totalVisible - 1 ? 100 : ((activo + 1) / totalVisible) * 100) - progresoVerde) : 0;
        if (progresoNaranja < 0) progresoNaranja = 0;

        const lineaVerde = document.getElementById('linea-verde-progreso');
        const lineaNaranja = document.getElementById('linea-naranja-progreso');
        if (lineaVerde) lineaVerde.style.width = `${progresoVerde}%`;
        if (lineaNaranja) { lineaNaranja.style.left = `${progresoVerde}%`; lineaNaranja.style.width = `${progresoNaranja}%`; }
    }

    function renderizarTraza(trazas) {
        const contenedor = document.getElementById('tracker-contenedor');
        if (!contenedor) return;
        let html = '';
        const iconMap = { 'Solicitante Cco.': 'fa-solid fa-user', 'Cotización': 'fa-solid fa-file-invoice-dollar', 'Autorizador Cco.': 'fa-solid fa-user-check', 'Autorizador Categoría': 'fa-solid fa-user-tag', 'Autorizador >= $5K': 'fa-solid fa-user-shield', 'Orden de Compra': 'fa-solid fa-shopping-cart', 'Revisión OC': 'fa-solid fa-clipboard-check', 'OC en Proveedor': 'fa-solid fa-truck', 'Recepción': 'fa-solid fa-box-open', 'Cerrar OC': 'fa-solid fa-lock' };

        trazas.forEach((paso) => {
            let clase = 'pending', iconStatus = '', badgeClass = 'step-badge-pending', fechaInfo = '';
            if (paso.resolucion === 'C' || paso.descripcion === 'Completado' || paso.descripcion === 'Solicitado') {
                clase = 'done'; iconStatus = '<i class="fas fa-check"></i>'; badgeClass = 'step-badge-done';
                if (paso.fecha && paso.hora) fechaInfo = `<br><span style="font-size:9px; color:#64748b;"><i class="far fa-calendar-alt"></i> ${paso.fecha} ${paso.hora}</span>`;
            } else if (paso.resolucion === 'A' || paso.descripcion === 'En proceso' || paso.descripcion === 'En Proceso') { clase = 'active'; iconStatus = '<i class="fas fa-clock"></i>'; badgeClass = 'step-badge-active'; }

            const faIcon = iconMap[paso.estado_descr] || 'fa-solid fa-circle';
            let isHidden = (paso.active == 0 || paso.active == '0') ? 'display: none;' : '';
            let is5K = (paso.estado == 41 || paso.orden == 41);
            let extraId = is5K ? 'id="step-5k"' : '';

            html += `<div class="step-node ${clase}" ${extraId} data-active="${paso.active}" style="padding: 0 5px; ${isHidden}">
                <div class="step-dot" style="width: 36px; height: 36px; font-size: 16px; margin: 0 auto 6px auto;"><i class="${faIcon}"></i><div class="status-overlay" style="width: 14px; height: 14px; font-size: 8px; bottom: -2px; right: -2px;">${iconStatus}</div></div>
                <div class="step-label">
                    <h4 style="font-size: 11px; margin: 0 0 2px 0;">${paso.estado_descr}</h4>
                    <p class="m-0" style="font-size: 10px; line-height: 1.2;">
                        <span class="text-muted-dark text-bold">${paso.nom_usuario}</span><br>
                        <span class="step-badge ${badgeClass}" style="padding: 2px 6px; font-size: 9px; margin-top:2px;">${paso.descripcion}</span>
                        ${fechaInfo}
                    </p>
                </div>
            </div>`;
        });
        contenedor.style.cssText = "align-items: flex-start; position: relative;";
        contenedor.innerHTML = `<style>.tracker::before { top: 17px !important; }</style><div id="linea-verde-progreso" style="position: absolute; top: 17px; left: 0; height: 4px; z-index: 1; width: 0%; background-color: #28a745 !important; transition: width 0.5s ease;"></div><div id="linea-naranja-progreso" style="position: absolute; top: 17px; left: 0%; height: 4px; z-index: 1; width: 0%; background-color: #ffc107 !important; transition: width 0.5s ease, left 0.5s ease;"></div>${html}`;
        actualizarLineasProgreso();
    }
    cargarTrazabilidadReal();

    let timeoutCalculo = null;
    const calcular = () => {
        let total = 0;
        document.querySelectorAll('.fila-cotizacion').forEach(f => {
            let c = parseFloat(f.querySelector('.cant-input').value) || 0;
            let p = parseFloat(f.dataset.precio) || 0;
            let sub = c * p;
            f.querySelector('.sub-txt').textContent = sub.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            total += sub;
        });
        document.getElementById('totalGlobal').textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const step5k = document.getElementById('step-5k');
        if (step5k) {
            let isParametrized = step5k.getAttribute('data-active') == '1';
            step5k.style.display = (total >= 5000 && isParametrized) ? '' : 'none';
            actualizarLineasProgreso();
        }

        if (idCategoriaInput && total >= 0) {
            clearTimeout(timeoutCalculo);
            timeoutCalculo = setTimeout(async () => {
                const fd = new FormData(); fd.append('id_categoria', idCategoriaInput.value); fd.append('total', total);
                try {
                    const res = await fetch('json.php?c=compras&a=obtener_autorizador', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.exito) {
                        document.querySelectorAll('.step-label').forEach(label => {
                            const tituloNode = label.querySelector('h4');
                            if (!tituloNode) return;
                            const tituloTexto = tituloNode.textContent.trim();
                            const spanNombre = label.querySelector('.text-muted-dark');

                            if (data.autorizador && (tituloTexto === 'Autorizador Categoría' || tituloTexto === 'Autorizador Categoria')) {
                                if (spanNombre && spanNombre.textContent !== data.autorizador.usr_nombre) { spanNombre.style.opacity = '0'; setTimeout(() => { spanNombre.textContent = data.autorizador.usr_nombre; spanNombre.style.color = '#0056b3'; spanNombre.style.opacity = '1'; setTimeout(() => spanNombre.style.color = '', 1500); }, 200); }
                            }
                            if (tituloTexto === 'Autorizador >= $5K' || tituloTexto === 'Autorizador >= $5k') {
                                if (data.autorizador_5k && total >= 5000) {
                                    if (step5k) step5k.style.display = '';
                                    if (spanNombre && spanNombre.textContent !== data.autorizador_5k.usr_nombre) { spanNombre.style.opacity = '0'; setTimeout(() => { spanNombre.textContent = data.autorizador_5k.usr_nombre; spanNombre.style.color = '#ef4444'; spanNombre.style.opacity = '1'; setTimeout(() => spanNombre.style.color = '', 1500); }, 200); }
                                } else { if (step5k) step5k.style.display = 'none'; }
                            }
                        });
                        actualizarLineasProgreso();
                    }
                } catch (err) { console.error(err); }
            }, 500);
        }
    };

    document.querySelectorAll('.cant-input').forEach(i => i.addEventListener('input', calcular));
    calcular();

    document.getElementById('listaProductosCC').addEventListener('click', async (e) => {
        const btnAct = e.target.closest('.btn-actualizar-cant');
        const btnElim = e.target.closest('.btn-eliminar-item');

        if (btnAct) {
            const fila = btnAct.closest('tr');
            const id_predsol = fila.dataset.id;
            const cantInput = fila.querySelector('.cant-input');
            const cant = parseFloat(cantInput.value);
            const precio = parseFloat(fila.dataset.precio);

            if (!cant || cant < 1 || !Number.isInteger(cant)) { mostrarAlerta('Cantidad inválida.', 'error'); cantInput.classList.add('input-error'); return; }

            btnAct.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; btnAct.disabled = true;
            const fd = new FormData(); fd.append('id_prehsol', idSol); fd.append('id_predsol', id_predsol); fd.append('cantidad', cant); fd.append('precio', precio);

            try {
                const res = await fetch('json.php?c=compras&a=actualizar_cantidad_item', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.exito) {
                    mostrarAlerta(data.msj, 'success');
                    btnAct.classList.replace('btn-primary', 'btn-success'); btnAct.innerHTML = '<i class="fas fa-check"></i>';
                    setTimeout(() => { btnAct.classList.replace('btn-success', 'btn-primary'); btnAct.innerHTML = '<i class="fas fa-sync-alt"></i>'; btnAct.disabled = false; }, 2000);
                    calcular();
                } else throw new Error(data.msj);
            } catch (err) { mostrarAlerta(err.message, 'error'); btnAct.innerHTML = '<i class="fas fa-sync-alt"></i>'; btnAct.disabled = false; }
        }

        if (btnElim) {
            const fila = btnElim.closest('tr');
            const totalFilas = document.querySelectorAll('.fila-cotizacion').length;

            if (totalFilas <= 1) { mostrarAlerta('No puedes eliminar el único producto. Debes "Desistir" de la solicitud.', 'error'); return; }
            if (!confirm('¿Seguro que deseas eliminar este producto de la cotización?')) return;

            const id_predsol = fila.dataset.id;
            btnElim.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; btnElim.disabled = true;
            const fd = new FormData(); fd.append('id_prehsol', idSol); fd.append('id_predsol', id_predsol);

            try {
                const res = await fetch('json.php?c=compras&a=eliminar_item_cotizacion', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.exito) { mostrarAlerta(data.msj, 'success'); fila.remove(); calcular(); }
                else throw new Error(data.msj);
            } catch (err) { mostrarAlerta(err.message, 'error'); btnElim.innerHTML = '<i class="fas fa-trash-alt"></i>'; btnElim.disabled = false; }
        }
    });

    const formAprobar = document.getElementById('formAprobarCC');
    if (formAprobar) {
        formAprobar.addEventListener('submit', async (e) => {
            e.preventDefault();
            const obs = document.getElementById('observacion_aprobador');
            if (obs.value.trim() === '') { mostrarAlerta('Debe ingresar su observación de aprobación.', 'error'); obs.classList.add('input-error'); return; }

            const btnSubmit = e.target.querySelector('button[type="submit"]');
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-4"></i> Procesando...'; btnSubmit.disabled = true;

            const fd = new FormData(formAprobar);
            try {
                const res = await fetch('json.php?c=compras&a=aprobar_cotizacion_cc', { method: 'POST', body: fd });
                const data = await res.json();
                if (res.ok && data.exito) {
                    mostrarAlerta(data.msj, 'success');
                    setTimeout(() => window.location.href = '?c=solicitud&a=consulta_aprobacion_cc', 1500);
                } else throw new Error(data.msj);
            } catch (err) { mostrarAlerta(err.message || 'Error', 'error'); btnSubmit.innerHTML = '<i class="fas fa-check-circle mr-4"></i> Aprobar Cotización'; btnSubmit.disabled = false; }
        });
    }
}
// =====================================================================
// 7. MÓDULO: VISTA BANDEJA PENDIENTE OC
// =====================================================================
let paginaActualPendienteOC = 1;

window.cambiarPaginaPendienteOC = function (nuevaPag) {
    paginaActualPendienteOC = nuevaPag;
    cargarDatosPendienteOC();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.iniciarGeneracionOC = function (id) {
    window.location.href = `?c=solicitud&a=generar_oc&id=${id}`;
};

async function cargarDatosPendienteOC() {
    const loader = document.getElementById('loader');
    if (loader) loader.style.display = 'flex';

    const form = document.getElementById('formFiltrosPendienteOC');
    const formData = new FormData(form);
    formData.append('pagina', paginaActualPendienteOC);

    const periodo = formData.get('periodo');
    if (periodo === 'todos') { formData.append('anio', ''); formData.append('mes', ''); }
    else if (periodo) { const [anio, mes] = periodo.split('-'); formData.append('anio', anio); formData.append('mes', mes); }
    formData.delete('periodo');

    try {
        const [res] = await Promise.all([
            fetch('json.php?c=compras&a=consulta_pendiente_oc', { method: 'POST', body: formData }),
            new Promise(resolve => setTimeout(resolve, 350))
        ]);
        const json = await res.json();
        if (json.exito) {
            renderizarTablaPendienteOC(json.data);
            renderizarPaginacionConsulta(json.paginacion);
        } else { mostrarAlerta(json.msj || 'Error obteniendo datos', 'error'); }
    } catch (err) { console.error(err); mostrarAlerta('Error de red al consultar.', 'error'); }
    finally { if (loader) loader.style.display = 'none'; }
}

function renderizarTablaPendienteOC(data) {
    const tbody = document.getElementById('tablaBodyPendienteOC');

    if (!document.getElementById('estilo-adjuntos-dropdown')) {
        const style = document.createElement('style'); style.id = 'estilo-adjuntos-dropdown';
        style.innerHTML = `.table-moderna td.text-center .btn-group { position: relative; } .adjuntos-dropdown { right: 0 !important; left: auto !important; } .adjuntos-dropdown a { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px; text-align: left !important; color: #334155 !important; text-decoration: none; padding: 6px 15px !important; font-size: 11px; transition: all 0.2s ease; } .adjuntos-dropdown a:hover { background-color: #e0f2fe !important; color: #0284c7 !important; } .adjuntos-dropdown .dropdown-header { text-align: left !important; padding: 4px 15px; }`;
        document.head.appendChild(style);
    }

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted-light" style="padding: 40px 0;"><i class="fas fa-inbox empty-state-icon"></i><br><span class="fs-15 text-bold">No hay Órdenes Pendientes</span><br><span class="fs-13">Todas las solicitudes aprobadas ya fueron procesadas.</span></td></tr>`;
        return;
    }

    let filasHtml = '';

    data.forEach(item => {
        let htmlObservacion = '';
        if (item.observacion_crea && item.observacion_crea.trim() !== '') {
            const obsLimpia1 = item.observacion_crea.replace(/"/g, '&quot;');
            htmlObservacion += `<div class="obs-box" title="${obsLimpia1}"><i class="fas fa-comment-dots text-muted-lighter mr-3"></i>Sol: ${item.observacion_crea}</div>`;
        }
        if (item.observacion_cotiza && item.observacion_cotiza.trim() !== '') {
            const obsLimpia2 = item.observacion_cotiza.replace(/"/g, '&quot;');
            htmlObservacion += `<div class="obs-box" title="${obsLimpia2}" style="margin-top: 2px;"><i class="fas fa-user-edit text-muted-lighter mr-3"></i>Ana: ${item.observacion_cotiza}</div>`;
        }

        let fechaFormateada = item.fecha_crea ? item.fecha_crea.split('-').reverse().join('/') : '';
        let horaFormateada = item.hora_crea ? item.hora_crea.split(':').slice(0, 2).join(':') : '';

        const montoFormateado = parseFloat(item.monto_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const moneda = item.moneda || '$';

        let aprobNombre = '', aprobFecha = '', aprobHora = '', aprobEtiqueta = '';

        if (item.aprob_5k_active == '1' && item.aprob_5k_res === 'C') {
            aprobNombre = item.aprob_5k_nombre || 'Autorizador 5K';
            aprobFecha = item.aprob_5k_fecha ? item.aprob_5k_fecha.split('-').reverse().join('/') : '';
            aprobHora = item.aprob_5k_hora ? item.aprob_5k_hora.split(':').slice(0, 2).join(':') : '';
            aprobEtiqueta = 'Autorizador >= $5K';
        } else {
            aprobNombre = item.aprob_cat_nombre || 'Autorizador Categoría';
            aprobFecha = item.aprob_cat_fecha ? item.aprob_cat_fecha.split('-').reverse().join('/') : '';
            aprobHora = item.aprob_cat_hora ? item.aprob_cat_hora.split(':').slice(0, 2).join(':') : '';
            aprobEtiqueta = 'Autorizador Categoría';
        }

        let htmlAprobador = `
            <div class="text-muted-light fs-11" title="${aprobEtiqueta}"><i class="fas fa-user-check text-primary-blue mr-4"></i>${aprobNombre}</div>
            <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                <i class="far fa-calendar-check text-muted-light mr-4"></i>${aprobFecha} | <i class="far fa-clock text-muted-lighter mr-4"></i>${aprobHora}
            </div>
        `;

        // 🏗️ RENDERIZADO DINÁMICO DE ADJUNTOS (Agrupado por Origen)
        let htmlAdjuntos = '<span class="text-muted-lighter fs-11 text-bold">N/A</span>';
        if (item.adjuntos && item.adjuntos.length > 0) {
            // Filtramos los adjuntos por su tipo
            let adjSolicitante = item.adjuntos.filter(a => ['S1', 'S2', 'S3'].includes(a.adjunto_tipo));
            let adjAnalista = item.adjuntos.filter(a => a.adjunto_tipo === 'CC');

            htmlAdjuntos = `<div class="btn-group">
                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 4px; border: 1px solid #cbd5e1; background: white; color: #475569; font-size: 11px; padding: 4px 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <i class="fas fa-paperclip mr-3 text-primary-blue"></i> ${item.adjuntos.length} <span class="caret" style="margin-left: 4px;"></span>
                </button>
                <ul class="dropdown-menu adjuntos-dropdown shadow-sm" style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 0; min-width: 230px; z-index: 1050;">`;

            // Función Helper para dibujar cada ítem (DRY - Don't Repeat Yourself)
            const renderItemAdjunto = (adj) => {
                let icon = (adj.ruta && adj.ruta.toLowerCase().endsWith('pdf')) ? 'fa-file-pdf text-danger' : 'fa-file-excel text-success';
                return `<li>
                    <a href="uploads/compras/${adj.ruta}" target="_blank" title="${adj.nombre_archivo}" style="display: flex; align-items: center; padding: 6px 15px; font-size: 11px; color: #334155; text-decoration: none;">
                        <i class="fas ${icon}" style="width: 16px; text-align: center; margin-right: 8px; font-size: 13px;"></i>
                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-grow: 1;">${adj.nombre_archivo}</span>
                    </a>
                </li>`;
            };

            // 1. Grupo: Solicitante
            if (adjSolicitante.length > 0) {
                htmlAdjuntos += `<li class="dropdown-header text-bold text-muted-dark fs-10 text-uppercase" style="padding: 4px 15px; letter-spacing: 0.5px;"><i class="fas fa-user mr-2"></i> Adjuntos Solicitante</li>
                                 <li role="separator" class="divider" style="margin: 4px 0; background-color: #e2e8f0;"></li>`;
                adjSolicitante.forEach(adj => { htmlAdjuntos += renderItemAdjunto(adj); });
            }

            // 2. Grupo: Analista de Compras
            if (adjAnalista.length > 0) {
                // Si ya dibujamos los del solicitante, agregamos un separador más grueso
                if (adjSolicitante.length > 0) {
                    htmlAdjuntos += `<li role="separator" class="divider" style="margin: 8px 0; background-color: #cbd5e1;"></li>`;
                }
                htmlAdjuntos += `<li class="dropdown-header text-bold fs-10 text-uppercase" style="padding: 4px 15px; letter-spacing: 0.5px; color: #0284c7;"><i class="fas fa-user-edit mr-2"></i> Analista de Compras</li>
                                 <li role="separator" class="divider" style="margin: 4px 0; background-color: #e2e8f0;"></li>`;
                adjAnalista.forEach(adj => { htmlAdjuntos += renderItemAdjunto(adj); });
            }

            htmlAdjuntos += `</ul></div>`;
        }

        filasHtml += `
        <tr>
            <td><strong class="text-muted-light fs-11">#${item.id}</strong></td>
            <td>
                <div class="text-bold text-dark fs-13">${item.emp_nombre}</div>
                <div class="text-muted-light fs-11 mt-2">${item.cc_descripcion}</div>
            </td>
            <td><span class="badge-cat">${item.cat_descripcion}</span>${htmlObservacion}</td>
            <td>
                <div class="text-extrabold text-primary-blue fs-11">${moneda} ${montoFormateado}</div>
            </td>
            <td>
                <div class="text-muted-light fs-11"><i class="fas fa-user text-muted-lighter mr-4"></i>${item.usuario_crea}</div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-calendar-alt text-muted-light mr-4"></i>${fechaFormateada} | <i class="far fa-clock text-muted-lighter mr-4"></i>${horaFormateada}
                </div>
            </td>
            <td>
                ${htmlAprobador}
            </td>
            <td>${obtenerTiempoTranscurrido(item.fecha_crea, item.hora_crea)}</td>
            <td class="text-center">${htmlAdjuntos}</td>
            <td class="text-center">
                <button type="button" class="btn-cotizar" style="background-color: #10b981;" onclick="iniciarGeneracionOC(${item.id})">
                    <i class="fas fa-file-invoice mr-4"></i> OC
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = filasHtml;
}

function initPendienteOCView() {
    inicializarPeriodos('periodo', true);
    $('.select2-consulta').select2({ width: '100%' });

    if (typeof fetchData === 'function') { fetchData('json.php?c=catalog&a=catalogo&cat=empresa_user', document.getElementById('empresa'), 'Todas las Empresas'); }

    $('.select2-consulta').on('select2:select', function () { this.dispatchEvent(new Event('change', { bubbles: true })); });

    document.getElementById('empresa').addEventListener('change', (e) => {
        const selectCC = document.getElementById('centroCostos');
        selectCC.innerHTML = '<option value="">Todos los CC</option>';
        if (e.target.value) {
            $(selectCC).prop('disabled', false);
            if (typeof fetchData === 'function') fetchData(`json.php?c=catalog&a=catalogo&cat=cc_user&id=${e.target.value}`, selectCC, 'Todos los CC');
        } else { $(selectCC).prop('disabled', true).trigger('change.select2'); }
    });

    document.getElementById('formFiltrosPendienteOC').addEventListener('submit', (e) => { e.preventDefault(); paginaActualPendienteOC = 1; cargarDatosPendienteOC(); });

    cargarDatosPendienteOC();
}

// =====================================================================
// 8. MÓDULO: VISTA GENERAR ORDEN DE COMPRA (ANALISTA)
// =====================================================================
function initGenerarOCView() {
    const idSol = document.getElementById('id_prehsol').value;

    async function cargarTrazabilidadReal() {
        const formData = new FormData(); formData.append('id_sol', idSol);
        try {
            const res = await fetch('json.php?c=trazabilidad&a=obtener_por_solicitud', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.exito && data.trazabilidad) renderizarTraza(data.trazabilidad);
        } catch (e) { console.error(e); }
    }

    function actualizarLineasProgreso() {
        const nodos = Array.from(document.querySelectorAll('.step-node')).filter(n => n.style.display !== 'none');
        const totalVisible = nodos.length;
        if (totalVisible === 0) return;
        let ultimoCompletado = -1, activo = -1;
        nodos.forEach((nodo, i) => {
            if (nodo.classList.contains('done')) ultimoCompletado = i;
            if (nodo.classList.contains('active')) activo = i;
        });
        let progresoVerde = ultimoCompletado >= 0 ? (ultimoCompletado === totalVisible - 1 ? 100 : ((ultimoCompletado + 1) / totalVisible) * 100) : 0;
        let progresoNaranja = activo >= 0 ? ((activo === totalVisible - 1 ? 100 : ((activo + 1) / totalVisible) * 100) - progresoVerde) : 0;
        if (progresoNaranja < 0) progresoNaranja = 0;

        const lineaVerde = document.getElementById('linea-verde-progreso');
        const lineaNaranja = document.getElementById('linea-naranja-progreso');
        if (lineaVerde) lineaVerde.style.width = `${progresoVerde}%`;
        if (lineaNaranja) { lineaNaranja.style.left = `${progresoVerde}%`; lineaNaranja.style.width = `${progresoNaranja}%`; }
    }

    function renderizarTraza(trazas) {
        const contenedor = document.getElementById('tracker-contenedor');
        if (!contenedor) return;
        let html = '';
        const iconMap = { 'Solicitante Cco.': 'fa-solid fa-user', 'Cotización': 'fa-solid fa-file-invoice-dollar', 'Autorizador Cco.': 'fa-solid fa-user-check', 'Autorizador Categoría': 'fa-solid fa-user-tag', 'Autorizador >= $5K': 'fa-solid fa-user-shield', 'Orden de Compra': 'fa-solid fa-shopping-cart', 'Revisión OC': 'fa-solid fa-clipboard-check', 'OC en Proveedor': 'fa-solid fa-truck', 'Recepción': 'fa-solid fa-box-open', 'Cerrar OC': 'fa-solid fa-lock' };

        trazas.forEach((paso) => {
            let clase = 'pending', iconStatus = '', badgeClass = 'step-badge-pending', fechaInfo = '';
            if (paso.resolucion === 'C' || paso.descripcion === 'Completado' || paso.descripcion === 'Solicitado' || paso.descripcion === 'Aprobado (Auto)' || paso.descripcion === 'Aprobado') {
                clase = 'done'; iconStatus = '<i class="fas fa-check"></i>'; badgeClass = 'step-badge-done';
                if (paso.fecha && paso.hora) fechaInfo = `<br><span style="font-size:9px; color:#64748b;"><i class="far fa-calendar-alt"></i> ${paso.fecha} ${paso.hora}</span>`;
            } else if (paso.resolucion === 'A' || paso.descripcion === 'En proceso' || paso.descripcion === 'En Proceso') { clase = 'active'; iconStatus = '<i class="fas fa-clock"></i>'; badgeClass = 'step-badge-active'; }

            const faIcon = iconMap[paso.estado_descr] || 'fa-solid fa-circle';
            let isHidden = (paso.active == 0 || paso.active == '0') ? 'display: none;' : '';

            html += `<div class="step-node ${clase}" data-active="${paso.active}" style="padding: 0 5px; ${isHidden}">
                <div class="step-dot" style="width: 36px; height: 36px; font-size: 16px; margin: 0 auto 6px auto;"><i class="${faIcon}"></i><div class="status-overlay" style="width: 14px; height: 14px; font-size: 8px; bottom: -2px; right: -2px;">${iconStatus}</div></div>
                <div class="step-label">
                    <h4 style="font-size: 11px; margin: 0 0 2px 0;">${paso.estado_descr}</h4>
                    <p class="m-0" style="font-size: 10px; line-height: 1.2;">
                        <span class="text-muted-dark text-bold">${paso.nom_usuario}</span><br>
                        <span class="step-badge ${badgeClass}" style="padding: 2px 6px; font-size: 9px; margin-top:2px;">${paso.descripcion}</span>
                        ${fechaInfo}
                    </p>
                </div>
            </div>`;
        });
        contenedor.style.cssText = "align-items: flex-start; position: relative;";
        contenedor.innerHTML = `<style>.tracker::before { top: 17px !important; }</style><div id="linea-verde-progreso" style="position: absolute; top: 17px; left: 0; height: 4px; z-index: 1; width: 0%; background-color: #28a745 !important; transition: width 0.5s ease;"></div><div id="linea-naranja-progreso" style="position: absolute; top: 17px; left: 0%; height: 4px; z-index: 1; width: 0%; background-color: #ffc107 !important; transition: width 0.5s ease, left 0.5s ease;"></div>${html}`;
        actualizarLineasProgreso();
    }
    cargarTrazabilidadReal();

    const formGenerar = document.getElementById('formGenerarOC');
    if (formGenerar) {
        formGenerar.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btnSubmit = e.target.querySelector('button[type="submit"]');
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-4"></i> Procesando OC...';
            btnSubmit.disabled = true;

            const fd = new FormData(formGenerar);

            try {
                const res = await fetch('json.php?c=compras&a=procesar_generacion_oc', { method: 'POST', body: fd });
                const data = await res.json();
                if (res.ok && data.exito) {
                    mostrarAlerta(data.msj, 'success');
                    setTimeout(() => window.location.href = '?c=solicitud&a=consulta_pendiente_oc', 1500);
                } else throw new Error(data.msj);
            } catch (err) {
                mostrarAlerta('Error en la Generación de OC.', 'error');
                btnSubmit.innerHTML = '<i class="fas fa-file-invoice mr-4"></i> Generar OC';
                btnSubmit.disabled = false;
            }
        });
    }
}

// =====================================================================
// 9. MÓDULO: VISTA CONSULTA DE ÓRDENES DE COMPRA (HISTORIAL)
// =====================================================================
let paginaActualOC = 1;

window.cambiarPaginaOC = function (nuevaPag) {
    paginaActualOC = nuevaPag;
    cargarDatosOC();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.accionBotonOC = function (idSol, numOc, estado) {
    if (estado == 61) { window.location.href = `?c=solicitud&a=revisar_oc&id=${idSol}&oc=${numOc}`; }
    else { window.location.href = `?c=solicitud&a=ver_oc&id=${idSol}&oc=${numOc}`; }
};

async function cargarDatosOC() {
    const loader = document.getElementById('loader');
    if (loader) loader.style.display = 'flex';

    const form = document.getElementById('formFiltrosOC');
    const formData = new FormData(form);
    formData.append('pagina', paginaActualOC);

    const periodo = formData.get('periodo');
    if (periodo === 'todos') { formData.append('anio', ''); formData.append('mes', ''); }
    else if (periodo) { const [anio, mes] = periodo.split('-'); formData.append('anio', anio); formData.append('mes', mes); }
    formData.delete('periodo');

    try {
        const [res] = await Promise.all([
            fetch('json.php?c=compras&a=consulta_oc', { method: 'POST', body: formData }),
            new Promise(resolve => setTimeout(resolve, 350))
        ]);
        const json = await res.json();
        if (json.exito) {
            renderizarTablaOC(json.data);
            renderizarPaginacionConsulta(json.paginacion);
        } else { mostrarAlerta(json.msj || 'Error obteniendo datos', 'error'); }
    } catch (err) { console.error(err); mostrarAlerta('Error de red al consultar.', 'error'); }
    finally { if (loader) loader.style.display = 'none'; }
}

function renderizarTablaOC(data) {
    const tbody = document.getElementById('tablaBodyOC');

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted-light" style="padding: 40px 0;"><i class="fas fa-file-invoice empty-state-icon"></i><br><span class="fs-15 text-bold">Sin registros</span><br><span class="fs-13">No hay órdenes de compra generadas en este período.</span></td></tr>`;
        return;
    }

    let filasHtml = '';

    data.forEach(item => {
        let fechaFormateada = item.fecha_oc ? item.fecha_oc.split('-').reverse().join('/') : '';
        let horaFormateada = item.hora_oc ? item.hora_oc.split(':').slice(0, 2).join(':') : '';

        const montoFormateado = parseFloat(item.monto_oc || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const moneda = item.moneda || '$';

        let estadoStr = 'Desconocido';
        let badgeColor = 'bg-secondary';
        let colorBoton = '#6366f1';
        let textoBoton = '<i class="fas fa-eye mr-4"></i> Ver OC';

        const estadoNum = item.estado_oc;

        if (estadoNum == 61 || estadoNum === 'GE') {
            estadoStr = 'Revisión OC';
            badgeColor = 'background: #fef3c7; color: #d97706;';
            colorBoton = '#f59e0b';
            textoBoton = '<i class="fas fa-search-dollar mr-4"></i> Revisar OC';
        } else if (estadoNum == 71 || estadoNum === 'AP' || estadoNum === 'PR') {
            estadoStr = 'OC en Proveedor';
            badgeColor = 'background: #e0f2fe; color: #0284c7;';
        } else if (estadoNum == 81 || estadoNum === 'RU') {
            estadoStr = 'Recepción';
            badgeColor = 'background: #eef2ff; color: #4f46e5;';
        } else if (estadoNum >= 91 || estadoNum === 'CR') {
            estadoStr = 'Cerrar OC / Finalizado';
            badgeColor = 'background: #dcfce7; color: #16a34a;';
        }

        const estadoAccion = (estadoNum === 'GE' || estadoNum == 61) ? 61 : 71;

        filasHtml += `
        <tr>
            <td style="vertical-align: middle;">
                <div class="text-extrabold text-dark fs-14">OC-${item.numero_oc}</div>
                <div class="text-muted-lighter fs-10 mt-2">Ref: Sol#${item.id_solicitud}</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="text-bold text-dark fs-12">${item.emp_nombre}</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="text-dark fs-12 texto-truncado" style="max-width: 250px;" title="${item.prov_nombre}">${item.prov_nombre || 'N/A'}</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="text-extrabold text-primary-blue fs-14">${moneda} ${montoFormateado}</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="text-muted-light fs-11"><i class="fas fa-user-edit text-muted-lighter mr-4"></i>${item.analista}</div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-calendar-alt text-muted-light mr-4"></i>${fechaFormateada} | <i class="far fa-clock text-muted-lighter mr-4"></i>${horaFormateada}
                </div>
            </td>
            <td class="text-center" style="vertical-align: middle;">
                <span class="step-badge fs-11" style="${badgeColor} padding: 4px 10px;">${estadoStr}</span>
            </td>
            <td class="text-center" style="vertical-align: middle;">
                <button type="button" class="btn-cotizar" style="background-color: ${colorBoton};" onclick="accionBotonOC('${item.id_solicitud}', '${item.numero_oc}', ${estadoAccion})">
                    ${textoBoton}
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = filasHtml;
}

function initConsultaOCView() {
    inicializarPeriodos('periodo', true);
    $('.select2-consulta').select2({ width: '100%' });

    if (typeof fetchData === 'function') { fetchData('json.php?c=catalog&a=catalogo&cat=empresa_user', document.getElementById('empresa'), 'Todas las Empresas'); }

    $('.select2-consulta').on('select2:select', function () { this.dispatchEvent(new Event('change', { bubbles: true })); });

    document.getElementById('formFiltrosOC').addEventListener('submit', (e) => { e.preventDefault(); paginaActualOC = 1; cargarDatosOC(); });

    cargarDatosOC();
}

// =====================================================================
// 10. MÓDULO: VISTA REVISIÓN DE OC (JEFE COMPRAS)
// =====================================================================
function initRevisarOCView() {
    const idSol = document.getElementById('id_prehsol').value;

    async function cargarTrazabilidadReal() {
        const formData = new FormData(); formData.append('id_sol', idSol);
        try {
            const res = await fetch('json.php?c=trazabilidad&a=obtener_por_solicitud', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.exito && data.trazabilidad) renderizarTraza(data.trazabilidad);
        } catch (e) { console.error(e); }
    }

    function actualizarLineasProgreso() {
        const nodos = Array.from(document.querySelectorAll('.step-node')).filter(n => n.style.display !== 'none');
        if (nodos.length === 0) return;
        let ultimoCompletado = -1, activo = -1;
        nodos.forEach((nodo, i) => {
            if (nodo.classList.contains('done')) ultimoCompletado = i;
            if (nodo.classList.contains('active')) activo = i;
        });
        let progresoVerde = ultimoCompletado >= 0 ? (ultimoCompletado === nodos.length - 1 ? 100 : ((ultimoCompletado + 1) / nodos.length) * 100) : 0;
        let progresoNaranja = activo >= 0 ? ((activo === nodos.length - 1 ? 100 : ((activo + 1) / nodos.length) * 100) - progresoVerde) : 0;

        const lV = document.getElementById('linea-verde-progreso');
        const lN = document.getElementById('linea-naranja-progreso');
        if (lV) lV.style.width = `${progresoVerde}%`;
        if (lN) { lN.style.left = `${progresoVerde}%`; lN.style.width = `${Math.max(0, progresoNaranja)}%`; }
    }

    function renderizarTraza(trazas) {
        const contenedor = document.getElementById('tracker-contenedor');
        if (!contenedor) return;
        let html = '';
        const iconMap = { 'Solicitante Cco.': 'fa-solid fa-user', 'Cotización': 'fa-solid fa-file-invoice-dollar', 'Autorizador Cco.': 'fa-solid fa-user-check', 'Autorizador Categoría': 'fa-solid fa-user-tag', 'Autorizador >= $5K': 'fa-solid fa-user-shield', 'Orden de Compra': 'fa-solid fa-shopping-cart', 'Revisión OC': 'fa-solid fa-clipboard-check', 'OC en Proveedor': 'fa-solid fa-truck', 'Recepción': 'fa-solid fa-box-open', 'Cerrar OC': 'fa-solid fa-lock' };

        trazas.forEach((paso) => {
            let clase = 'pending', iconStatus = '', badgeClass = 'step-badge-pending', fechaInfo = '';
            if (['C', 'Aprobado (Auto)', 'Aprobado', 'Completado', 'Solicitado'].includes(paso.resolucion) || ['C', 'Aprobado (Auto)', 'Aprobado', 'Completado', 'Solicitado'].includes(paso.descripcion)) {
                clase = 'done'; iconStatus = '<i class="fas fa-check"></i>'; badgeClass = 'step-badge-done';
                if (paso.fecha && paso.hora) fechaInfo = `<br><span style="font-size:9px; color:#64748b;"><i class="far fa-calendar-alt"></i> ${paso.fecha} ${paso.hora}</span>`;
            } else if (paso.resolucion === 'A' || paso.descripcion === 'En proceso' || paso.descripcion === 'En Proceso') { clase = 'active'; iconStatus = '<i class="fas fa-clock"></i>'; badgeClass = 'step-badge-active'; }

            const faIcon = iconMap[paso.estado_descr] || 'fa-solid fa-circle';
            let isHidden = (paso.active == 0 || paso.active == '0') ? 'display: none;' : '';

            html += `<div class="step-node ${clase}" data-active="${paso.active}" style="padding: 0 5px; ${isHidden}">
                <div class="step-dot" style="width: 36px; height: 36px; font-size: 16px; margin: 0 auto 6px auto;"><i class="${faIcon}"></i><div class="status-overlay" style="width: 14px; height: 14px; font-size: 8px; bottom: -2px; right: -2px;">${iconStatus}</div></div>
                <div class="step-label">
                    <h4 style="font-size: 11px; margin: 0 0 2px 0;">${paso.estado_descr}</h4>
                    <p class="m-0" style="font-size: 10px; line-height: 1.2;">
                        <span class="text-muted-dark text-bold">${paso.nom_usuario}</span><br>
                        <span class="step-badge ${badgeClass}" style="padding: 2px 6px; font-size: 9px; margin-top:2px;">${paso.descripcion}</span>
                        ${fechaInfo}
                    </p>
                </div>
            </div>`;
        });
        contenedor.style.cssText = "align-items: flex-start; position: relative;";
        contenedor.innerHTML = `<style>.tracker::before { top: 17px !important; }</style><div id="linea-verde-progreso" style="position: absolute; top: 17px; left: 0; height: 4px; z-index: 1; width: 0%; background-color: #28a745 !important; transition: width 0.5s ease;"></div><div id="linea-naranja-progreso" style="position: absolute; top: 17px; left: 0%; height: 4px; z-index: 1; width: 0%; background-color: #ffc107 !important; transition: width 0.5s ease, left 0.5s ease;"></div>${html}`;
        actualizarLineasProgreso();
    }
    cargarTrazabilidadReal();

    const formRevisar = document.getElementById('formRevisarOC');
    if (formRevisar) {
        formRevisar.addEventListener('submit', async (e) => {
            e.preventDefault();

            const obs = document.getElementById('observacion_jefe');
            if (obs.value.trim() === '') { mostrarAlerta('Debe ingresar su observación de revisión.', 'error'); obs.classList.add('input-error'); return; }

            const btnSubmit = e.target.querySelector('button[type="submit"]');
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-4"></i> Procesando...'; btnSubmit.disabled = true;

            const fd = new FormData(formRevisar);

            try {
                const res = await fetch('json.php?c=compras&a=aprobar_oc', { method: 'POST', body: fd });
                const data = await res.json();
                if (res.ok && data.exito) {
                    mostrarAlerta(data.msj, 'success');
                    setTimeout(() => window.location.href = '?c=solicitud&a=consulta_oc', 1500);
                } else { throw new Error(data.msj); }
            } catch (err) {
                mostrarAlerta(err.message || 'Error de conexión', 'error');
                btnSubmit.innerHTML = '<i class="fas fa-check-double mr-4"></i> Aprobar OC'; btnSubmit.disabled = false;
            }
        });
    }
}
// =====================================================================
// 11. MÓDULO: VISTA SOLO LECTURA DE OC (VER OC)
// =====================================================================
function initVerOCView() {
    const idSol = document.getElementById('id_prehsol').value;

    async function cargarTrazabilidadReal() {
        const formData = new FormData(); formData.append('id_sol', idSol);
        try {
            const res = await fetch('json.php?c=trazabilidad&a=obtener_por_solicitud', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.exito && data.trazabilidad) renderizarTraza(data.trazabilidad);
        } catch (e) { console.error(e); }
    }

    function actualizarLineasProgreso() {
        const nodos = Array.from(document.querySelectorAll('.step-node')).filter(n => n.style.display !== 'none');
        if (nodos.length === 0) return;
        let ultimoCompletado = -1, activo = -1;
        nodos.forEach((nodo, i) => {
            if (nodo.classList.contains('done')) ultimoCompletado = i;
            if (nodo.classList.contains('active')) activo = i;
        });
        let progresoVerde = ultimoCompletado >= 0 ? (ultimoCompletado === nodos.length - 1 ? 100 : ((ultimoCompletado + 1) / nodos.length) * 100) : 0;
        let progresoNaranja = activo >= 0 ? ((activo === nodos.length - 1 ? 100 : ((activo + 1) / nodos.length) * 100) - progresoVerde) : 0;

        const lV = document.getElementById('linea-verde-progreso');
        const lN = document.getElementById('linea-naranja-progreso');
        if (lV) lV.style.width = `${progresoVerde}%`;
        if (lN) { lN.style.left = `${progresoVerde}%`; lN.style.width = `${Math.max(0, progresoNaranja)}%`; }
    }

    function renderizarTraza(trazas) {
        const contenedor = document.getElementById('tracker-contenedor');
        if (!contenedor) return;
        let html = '';
        const iconMap = { 'Solicitante Cco.': 'fa-solid fa-user', 'Cotización': 'fa-solid fa-file-invoice-dollar', 'Autorizador Cco.': 'fa-solid fa-user-check', 'Autorizador Categoría': 'fa-solid fa-user-tag', 'Autorizador >= $5K': 'fa-solid fa-user-shield', 'Orden de Compra': 'fa-solid fa-shopping-cart', 'Revisión OC': 'fa-solid fa-clipboard-check', 'OC en Proveedor': 'fa-solid fa-truck', 'Recepción': 'fa-solid fa-box-open', 'Cerrar OC': 'fa-solid fa-lock' };

        trazas.forEach((paso) => {
            let clase = 'pending', iconStatus = '', badgeClass = 'step-badge-pending', fechaInfo = '';
            if (['C', 'Aprobado (Auto)', 'Aprobado', 'Completado', 'Solicitado'].includes(paso.resolucion) || ['C', 'Aprobado (Auto)', 'Aprobado', 'Completado', 'Solicitado'].includes(paso.descripcion)) {
                clase = 'done'; iconStatus = '<i class="fas fa-check"></i>'; badgeClass = 'step-badge-done';
                if (paso.fecha && paso.hora) fechaInfo = `<br><span style="font-size:9px; color:#64748b;"><i class="far fa-calendar-alt"></i> ${paso.fecha} ${paso.hora}</span>`;
            } else if (paso.resolucion === 'A' || paso.descripcion === 'En proceso' || paso.descripcion === 'En Proceso') { clase = 'active'; iconStatus = '<i class="fas fa-clock"></i>'; badgeClass = 'step-badge-active'; }

            const faIcon = iconMap[paso.estado_descr] || 'fa-solid fa-circle';
            let isHidden = (paso.active == 0 || paso.active == '0') ? 'display: none;' : '';

            html += `<div class="step-node ${clase}" data-active="${paso.active}" style="padding: 0 5px; ${isHidden}">
                <div class="step-dot" style="width: 36px; height: 36px; font-size: 16px; margin: 0 auto 6px auto;"><i class="${faIcon}"></i><div class="status-overlay" style="width: 14px; height: 14px; font-size: 8px; bottom: -2px; right: -2px;">${iconStatus}</div></div>
                <div class="step-label">
                    <h4 style="font-size: 11px; margin: 0 0 2px 0;">${paso.estado_descr}</h4>
                    <p class="m-0" style="font-size: 10px; line-height: 1.2;">
                        <span class="text-muted-dark text-bold">${paso.nom_usuario}</span><br>
                        <span class="step-badge ${badgeClass}" style="padding: 2px 6px; font-size: 9px; margin-top:2px;">${paso.descripcion}</span>
                        ${fechaInfo}
                    </p>
                </div>
            </div>`;
        });
        contenedor.style.cssText = "align-items: flex-start; position: relative;";
        contenedor.innerHTML = `<style>.tracker::before { top: 17px !important; }</style><div id="linea-verde-progreso" style="position: absolute; top: 17px; left: 0; height: 4px; z-index: 1; width: 0%; background-color: #28a745 !important; transition: width 0.5s ease;"></div><div id="linea-naranja-progreso" style="position: absolute; top: 17px; left: 0%; height: 4px; z-index: 1; width: 0%; background-color: #ffc107 !important; transition: width 0.5s ease, left 0.5s ease;"></div>${html}`;
        actualizarLineasProgreso();
    }
    cargarTrazabilidadReal();
}
// =====================================================================
// MÓDULO: BANDEJA AUTORIZADOR CATEGORÍA
// =====================================================================
let paginaActualAprobCat = 1;

window.cambiarPaginaAprobCat = function (nuevaPag) {
    paginaActualAprobCat = nuevaPag;
    cargarDatosAprobCategoria();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.iniciarAprobacionCategoria = function (id) {
    window.location.href = `?c=solicitud&a=revisar_cotizacion_categoria&id=${id}`;
};

async function cargarDatosAprobCategoria() {
    const loader = document.getElementById('loaderCat');
    if (loader) loader.style.display = 'flex';

    const form = document.getElementById('formFiltrosAprobCategoria');
    const formData = new FormData(form);
    formData.append('pagina', paginaActualAprobCat);

    const periodo = formData.get('periodo');
    if (periodo === 'todos') { formData.append('anio', ''); formData.append('mes', ''); }
    else if (periodo) { const [anio, mes] = periodo.split('-'); formData.append('anio', anio); formData.append('mes', mes); }
    formData.delete('periodo');

    try {
        const [res] = await Promise.all([
            fetch('json.php?c=compras&a=listar_aprobacion_categoria', { method: 'POST', body: formData }),
            new Promise(resolve => setTimeout(resolve, 350))
        ]);
        const json = await res.json();
        if (json.exito) {
            renderizarTablaAprobCategoria(json.data);

            // Reutilizamos el renderizador de paginación
            document.getElementById('paginacionInfoCat').innerHTML = `Total de registros: <strong>${json.paginacion.total_registros}</strong>`;
            const divBotones = document.getElementById('paginacionBotonesCat'); divBotones.innerHTML = '';
            if (json.paginacion.total_paginas > 1) {
                divBotones.innerHTML += `<button onclick="cambiarPaginaAprobCat(${json.paginacion.actual - 1})" ${json.paginacion.actual === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                for (let i = 1; i <= json.paginacion.total_paginas; i++) {
                    if (i === 1 || i === json.paginacion.total_paginas || (i >= json.paginacion.actual - 2 && i <= json.paginacion.actual + 2)) {
                        divBotones.innerHTML += `<button class="${i === json.paginacion.actual ? 'active' : ''}" onclick="cambiarPaginaAprobCat(${i})">${i}</button>`;
                    } else if (i === json.paginacion.actual - 3 || i === json.paginacion.actual + 3) {
                        divBotones.innerHTML += `<button disabled>...</button>`;
                    }
                }
                divBotones.innerHTML += `<button onclick="cambiarPaginaAprobCat(${json.paginacion.actual + 1})" ${json.paginacion.actual === json.paginacion.total_paginas ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
            }
        } else { window.mostrarAlerta(json.msj || 'Error obteniendo datos', 'error'); }
    } catch (err) { console.error(err); window.mostrarAlerta('Error de red al consultar.', 'error'); }
    finally { if (loader) loader.style.display = 'none'; }
}

function renderizarTablaAprobCategoria(data) {
    const tbody = document.getElementById('tablaBodyAprobCategoria');

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted-light" style="padding: 40px 0;"><i class="fas fa-tags empty-state-icon"></i><br><span class="fs-15 text-bold">Tu bandeja está al día</span><br><span class="fs-13">No tienes solicitudes pendientes de autorizar.</span></td></tr>`;
        return;
    }

    let filasHtml = '';

    data.forEach(item => {
        let htmlObservacion = '';
        if (item.observacion_crea && item.observacion_crea.trim() !== '') {
            const obsLimpia1 = item.observacion_crea.replace(/"/g, '&quot;');
            htmlObservacion += `<div class="obs-box" title="${obsLimpia1}"><i class="fas fa-comment-dots text-muted-lighter mr-3"></i>${item.observacion_crea}</div>`;
        }
        if (item.observacion_cotiza && item.observacion_cotiza.trim() !== '') {
            const obsLimpia2 = item.observacion_cotiza.replace(/"/g, '&quot;');
            htmlObservacion += `<div class="obs-box" title="${obsLimpia2}" style="margin-top: 2px;"><i class="fas fa-user-edit text-muted-lighter mr-3"></i>${item.observacion_cotiza}</div>`;
        }

        let fechaFormateada = item.fecha_crea ? item.fecha_crea.split('-').reverse().join('/') : '';
        let horaFormateada = item.hora_crea ? item.hora_crea.split(':').slice(0, 2).join(':') : '';

        let fechaAnalista = item.fecha_cotiza ? item.fecha_cotiza.split('-').reverse().join('/') : '';
        let horaAnalista = item.hora_cotiza ? item.hora_cotiza.split(':').slice(0, 2).join(':') : '';
        const analistaNombre = item.usuario_cotiza || 'Analista';

        const montoFormateado = parseFloat(item.monto_total || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const moneda = item.moneda || '$';

        let htmlAdjuntos = '<span class="text-muted-lighter fs-11 text-bold">N/A</span>';
        if (item.adjuntos && item.adjuntos.length > 0) {
            let adjSolicitante = item.adjuntos.filter(a => ['S1', 'S2', 'S3'].includes(a.adjunto_tipo));
            let adjAnalista = item.adjuntos.filter(a => a.adjunto_tipo === 'CC');

            htmlAdjuntos = `<div class="btn-group">
                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 4px; border: 1px solid #cbd5e1; background: white; color: #475569; font-size: 11px; padding: 4px 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <i class="fas fa-paperclip mr-3 text-primary-blue"></i> ${item.adjuntos.length} <span class="caret" style="margin-left: 4px;"></span>
                </button>
                <ul class="dropdown-menu adjuntos-dropdown shadow-sm" style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 0; min-width: 230px; z-index: 1050;">`;

            const renderItemAdjunto = (adj) => {
                let icon = (adj.ruta && adj.ruta.toLowerCase().endsWith('pdf')) ? 'fa-file-pdf text-danger' : 'fa-file-excel text-success';
                return `<li><a href="uploads/compras/${adj.ruta}" target="_blank" title="${adj.nombre_archivo}" style="display: flex; align-items: center; padding: 6px 15px; font-size: 11px; color: #334155; text-decoration: none;"><i class="fas ${icon}" style="width: 16px; text-align: center; margin-right: 8px; font-size: 13px;"></i><span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-grow: 1;">${adj.nombre_archivo}</span></a></li>`;
            };

            if (adjSolicitante.length > 0) {
                htmlAdjuntos += `<li class="dropdown-header text-bold text-muted-dark fs-10 text-uppercase" style="padding: 4px 15px; letter-spacing: 0.5px;"><i class="fas fa-user mr-2"></i> Adjuntos Solicitante</li><li role="separator" class="divider" style="margin: 4px 0; background-color: #e2e8f0;"></li>`;
                adjSolicitante.forEach(adj => { htmlAdjuntos += renderItemAdjunto(adj); });
            }
            if (adjAnalista.length > 0) {
                if (adjSolicitante.length > 0) htmlAdjuntos += `<li role="separator" class="divider" style="margin: 8px 0; background-color: #cbd5e1;"></li>`;
                htmlAdjuntos += `<li class="dropdown-header text-bold fs-10 text-uppercase" style="padding: 4px 15px; letter-spacing: 0.5px; color: #0284c7;"><i class="fas fa-user-edit mr-2"></i> Analista de Compras</li><li role="separator" class="divider" style="margin: 4px 0; background-color: #e2e8f0;"></li>`;
                adjAnalista.forEach(adj => { htmlAdjuntos += renderItemAdjunto(adj); });
            }
            htmlAdjuntos += `</ul></div>`;
        }

        filasHtml += `
        <tr>
            <td><strong class="text-muted-light fs-11">#${item.id}</strong></td>
            <td>
                <div class="text-bold text-dark fs-13">${item.emp_nombre}</div>
                <div class="text-muted-light fs-11 mt-2">${item.cc_descripcion}</div>
            </td>
            <td><span class="badge-cat" style="background: #ede9fe; color: #6d28d9;">${item.cat_descripcion}</span>${htmlObservacion}</td>
            <td>
                <div class="text-extrabold text-primary-blue fs-12">${moneda} ${montoFormateado}</div>
            </td>
            <td>
                <div class="text-muted-light fs-11"><i class="fas fa-user text-muted-lighter mr-4"></i>${item.usuario_crea}</div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-calendar-alt text-muted-light mr-4"></i>${fechaFormateada}
                </div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-clock text-muted-lighter mr-4"></i>${horaFormateada}
                </div>
            </td>
            <td>
                <div class="text-muted-light fs-11"><i class="fas fa-user-edit text-primary-blue mr-4"></i>${analistaNombre}</div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-calendar-check text-muted-light mr-4"></i>${fechaAnalista}
                </div>
                <div class="text-bold text-dark fs-11 mt-4 white-space-nowrap">
                    <i class="far fa-clock text-muted-lighter mr-4"></i>${horaAnalista}
                </div>
            </td>
            <td>${window.obtenerTiempoTranscurrido(item.fecha_crea, item.hora_crea)}</td>
            <td class="text-center">${htmlAdjuntos}</td>
            <td class="text-center">
                <button type="button" class="btn-cotizar" style="background-color: #8b5cf6;" onclick="iniciarAprobacionCategoria(${item.id})">
                    <i class="fas fa-tags mr-4"></i> Revisar
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = filasHtml;
}

function initAprobacionCategoriaView() {
    window.inicializarPeriodos('periodo_cat', true);
    $('.select2-consulta').select2({ width: '100%' });

    if (typeof window.fetchData === 'function') {
        window.fetchData('json.php?c=catalog&a=catalogo&cat=empresa_user', document.getElementById('empresa_cat'), 'Todas las Empresas');
        window.fetchData('json.php?c=catalog&a=catalogo&cat=categoria_compra', document.getElementById('categoria_filtro_cat'), 'Todas las Categorías');
    }

    $('.select2-consulta').on('select2:select', function () { this.dispatchEvent(new Event('change', { bubbles: true })); });

    document.getElementById('empresa_cat').addEventListener('change', (e) => {
        const selectCC = document.getElementById('centroCostos_cat');
        selectCC.innerHTML = '<option value="">Todos los CC</option>';
        if (e.target.value) {
            $(selectCC).prop('disabled', false);
            if (typeof window.fetchData === 'function') window.fetchData(`json.php?c=catalog&a=catalogo&cat=cc_user&id=${e.target.value}`, selectCC, 'Todos los CC');
        } else { $(selectCC).prop('disabled', true).trigger('change.select2'); }
    });

    document.getElementById('formFiltrosAprobCategoria').addEventListener('submit', (e) => { e.preventDefault(); paginaActualAprobCat = 1; cargarDatosAprobCategoria(); });

    cargarDatosAprobCategoria();
}

function initRevisarCotizacionCategoriaView() {
    const idSol = document.getElementById('id_prehsol').value;

    // 1. Motor de Trazabilidad
    async function cargarTrazabilidadReal() {
        const formData = new FormData(); formData.append('id_sol', idSol);
        try {
            const res = await fetch('json.php?c=trazabilidad&a=obtener_por_solicitud', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.exito && data.trazabilidad) renderizarTraza(data.trazabilidad);
        } catch (e) { console.error(e); }
    }

    function actualizarLineasProgreso() {
        const nodos = Array.from(document.querySelectorAll('.step-node')).filter(n => n.style.display !== 'none');
        if (nodos.length === 0) return;
        let ultimoCompletado = -1, activo = -1;
        nodos.forEach((nodo, i) => {
            if (nodo.classList.contains('done')) ultimoCompletado = i;
            if (nodo.classList.contains('active')) activo = i;
        });
        let progresoVerde = ultimoCompletado >= 0 ? (ultimoCompletado === nodos.length - 1 ? 100 : ((ultimoCompletado + 1) / nodos.length) * 100) : 0;
        let progresoNaranja = activo >= 0 ? ((activo === nodos.length - 1 ? 100 : ((activo + 1) / nodos.length) * 100) - progresoVerde) : 0;
        if (progresoNaranja < 0) progresoNaranja = 0;

        const lineaVerde = document.getElementById('linea-verde-progreso');
        const lineaNaranja = document.getElementById('linea-naranja-progreso');
        if (lineaVerde) lineaVerde.style.width = `${progresoVerde}%`;
        if (lineaNaranja) { lineaNaranja.style.left = `${progresoVerde}%`; lineaNaranja.style.width = `${progresoNaranja}%`; }
    }

    function renderizarTraza(trazas) {
        const contenedor = document.getElementById('tracker-contenedor');
        if (!contenedor) return;
        let html = '';
        const iconMap = { 'Solicitante Cco.': 'fa-solid fa-user', 'Cotización': 'fa-solid fa-file-invoice-dollar', 'Autorizador Cco.': 'fa-solid fa-user-check', 'Autorizador Categoría': 'fa-solid fa-user-tag', 'Autorizador >= $5K': 'fa-solid fa-user-shield', 'Orden de Compra': 'fa-solid fa-shopping-cart', 'Revisión OC': 'fa-solid fa-clipboard-check', 'OC en Proveedor': 'fa-solid fa-truck', 'Recepción': 'fa-solid fa-box-open', 'Cerrar OC': 'fa-solid fa-lock' };

        trazas.forEach((paso) => {
            let clase = 'pending', iconStatus = '', badgeClass = 'step-badge-pending', fechaInfo = '';
            if (['C', 'Aprobado (Auto)', 'Aprobado', 'Completado', 'Solicitado'].includes(paso.resolucion) || ['C', 'Aprobado (Auto)', 'Aprobado', 'Completado', 'Solicitado'].includes(paso.descripcion)) {
                clase = 'done'; iconStatus = '<i class="fas fa-check"></i>'; badgeClass = 'step-badge-done';
                if (paso.fecha && paso.hora) fechaInfo = `<br><span style="font-size:9px; color:#64748b;"><i class="far fa-calendar-alt"></i> ${paso.fecha} ${paso.hora}</span>`;
            } else if (paso.resolucion === 'A' || paso.descripcion === 'En proceso' || paso.descripcion === 'En Proceso') { clase = 'active'; iconStatus = '<i class="fas fa-clock"></i>'; badgeClass = 'step-badge-active'; }

            const faIcon = iconMap[paso.estado_descr] || 'fa-solid fa-circle';
            let isHidden = (paso.active == 0 || paso.active == '0') ? 'display: none;' : '';

            html += `<div class="step-node ${clase}" data-active="${paso.active}" style="padding: 0 5px; ${isHidden}">
                <div class="step-dot" style="width: 36px; height: 36px; font-size: 16px; margin: 0 auto 6px auto;"><i class="${faIcon}"></i><div class="status-overlay" style="width: 14px; height: 14px; font-size: 8px; bottom: -2px; right: -2px;">${iconStatus}</div></div>
                <div class="step-label">
                    <h4 style="font-size: 11px; margin: 0 0 2px 0;">${paso.estado_descr}</h4>
                    <p class="m-0" style="font-size: 10px; line-height: 1.2;">
                        <span class="text-muted-dark text-bold">${paso.nom_usuario}</span><br>
                        <span class="step-badge ${badgeClass}" style="padding: 2px 6px; font-size: 9px; margin-top:2px;">${paso.descripcion}</span>
                        ${fechaInfo}
                    </p>
                </div>
            </div>`;
        });
        contenedor.style.cssText = "align-items: flex-start; position: relative;";
        contenedor.innerHTML = `<style>.tracker::before { top: 17px !important; }</style><div id="linea-verde-progreso" style="position: absolute; top: 17px; left: 0; height: 4px; z-index: 1; width: 0%; background-color: #28a745 !important; transition: width 0.5s ease;"></div><div id="linea-naranja-progreso" style="position: absolute; top: 17px; left: 0%; height: 4px; z-index: 1; width: 0%; background-color: #ffc107 !important; transition: width 0.5s ease, left 0.5s ease;"></div>${html}`;
        actualizarLineasProgreso();
    }

    cargarTrazabilidadReal();

    // 2. Motor de Cálculos (Solo Lectura)
    const calcular = () => {
        let total = 0;
        document.querySelectorAll('.fila-cotizacion').forEach(f => {
            let c = parseFloat(f.querySelector('.cant-input').value) || 0;
            let p = parseFloat(f.dataset.precio) || 0;
            let sub = c * p;
            total += sub;
        });
        const totalGlobal = document.getElementById('totalGlobal');
        if (totalGlobal) totalGlobal.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    calcular();

    // 3. Envío del formulario
    const form = document.getElementById('formAprobarCategoria');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!form.checkValidity()) { form.reportValidity(); return; }

            if (confirm('¿Está seguro de Aprobar esta solicitud por Categoría?')) {
                const formData = new FormData(form);

                const btnSubmit = e.target.querySelector('button[type="submit"]');
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-4"></i> Procesando...';
                btnSubmit.disabled = true;

                try {
                    const res = await fetch('json.php?c=compras&a=guardar_aprobacion_categoria', { method: 'POST', body: formData });
                    const data = await res.json();
                    if (data.exito) {
                        window.mostrarAlerta(data.msj, 'success');
                        setTimeout(() => window.location.href = '?c=solicitud&a=consulta_aprobacion_categoria', 1500);
                    } else {
                        window.mostrarAlerta(data.msj, 'error');
                        btnSubmit.innerHTML = '<i class="fas fa-check-circle mr-4"></i> Aprobar Técnicamente';
                        btnSubmit.disabled = false;
                    }
                } catch (error) {
                    window.mostrarAlerta('Error de red al procesar.', 'error');
                    btnSubmit.innerHTML = '<i class="fas fa-check-circle mr-4"></i> Aprobar Técnicamente';
                    btnSubmit.disabled = false;
                }
            }
        });
    }
}

// =====================================================================
// INICIALIZADOR MAESTRO DE VISTAS (Router Lado Cliente)
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('purchaseRequestForm')) initCrearView();
    if (document.getElementById('formFiltros')) initConsultaView();
    if (document.getElementById('formCotizacion')) initCotizarView();
    if (document.getElementById('formFiltrosAprobCC')) initAprobacionCCView();
    if (document.getElementById('formAprobarCC')) initRevisarCotizacionCCView();
    if (document.getElementById('formFiltrosPendienteOC')) initPendienteOCView();
    if (document.getElementById('formGenerarOC')) initGenerarOCView();
    if (document.getElementById('formFiltrosOC')) initConsultaOCView();
    if (document.getElementById('formRevisarOC')) initRevisarOCView();
    if (document.getElementById('vistaVerOC')) initVerOCView();

    if (document.getElementById('formFiltrosAprobCategoria')) initAprobacionCategoriaView();
    if (document.getElementById('formAprobarCategoria')) initRevisarCotizacionCategoriaView();
});