<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    crossorigin="anonymous" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<div class="container w-100">
    <div class="tarjeta-consulta"
        style="position: relative; display: flex; flex-direction: column; min-height: calc(100vh - 90px);">

        <div id="loader"
            style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; background: rgba(255, 255, 255, 0.85) !important; z-index: 999999 !important; display: none; align-items: center; justify-content: center;">
            <div
                style="background: #ffffff !important; padding: 30px 45px !important; border-radius: 16px !important; box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important; text-align: center !important; display: flex !important; flex-direction: column !important; align-items: center !important; transform: translateY(-10%) !important; animation: none !important; border: 1px solid #f1f5f9 !important; margin: 0 !important;">
                <svg xmlns="http://www.w3.org/2000/svg"
                    style="width: 50px; height: 50px; margin-bottom: 15px; animation: sics-giro-seguro 1s linear infinite;"
                    viewBox="0 0 50 50">
                    <style>
                        @keyframes sics-giro-seguro {
                            100% {
                                transform: rotate(360deg);
                            }
                        }
                    </style>
                    <circle cx="25" cy="25" r="20" fill="none" stroke="#e2e8f0" stroke-width="4"></circle>
                    <circle cx="25" cy="25" r="20" fill="none" stroke="#0056b3" stroke-width="4"
                        stroke-dasharray="35 100" stroke-linecap="round"></circle>
                </svg>
                <span
                    style="font-weight: 600 !important; color: #0056b3 !important; font-size: 15px !important; font-family: 'Inter', sans-serif !important;">Procesando...</span>
            </div>
        </div>

        <form id="formFiltros" style="flex-shrink: 0;">
            <div class="header-consulta">
                <h2 class="titulo-moderno">
                    <div class="icono-titulo"><i class="fas fa-file-invoice-dollar"></i></div>
                    Solicitud(es) de compra(s) pendiente(s) de cotizar
                </h2>

                <div class="buscador-observacion">
                    <i class="fas fa-search icono-search"></i>
                    <input type="text" name="observacion" id="observacion" placeholder="Buscar por observación..."
                        autocomplete="off">
                </div>
            </div>

            <div class="filtro-panel">
                <div class="fila-filtros">
                    <div class="filtro-grupo flex-1" title="Período">
                        <div class="filtro-icono"><i class="far fa-calendar-alt"></i></div>
                        <div class="filtro-control">
                            <select name="periodo" id="periodo" class="form-control select2-consulta"></select>
                        </div>
                    </div>

                    <div class="filtro-grupo flex-1-5" title="Filtro de Empresa">
                        <div class="filtro-icono"><i class="fas fa-building"></i></div>
                        <div class="filtro-control">
                            <select name="empresa" id="empresa" class="form-control select2-consulta">
                                <option value="">Todas las Empresas</option>
                            </select>
                        </div>
                    </div>

                    <div class="filtro-grupo flex-1-5" title="Filtro de Centro de Costos">
                        <div class="filtro-icono"><i class="fas fa-home"></i></div>
                        <div class="filtro-control">
                            <select name="centroCostos" id="centroCostos" class="form-control select2-consulta"
                                disabled>
                                <option value="">Todos los CC</option>
                            </select>
                        </div>
                    </div>

                    <div class="filtro-grupo flex-1-5" title="Filtro de Categoría">
                        <div class="filtro-icono"><i class="fas fa-tags"></i></div>
                        <div class="filtro-control">
                            <select name="categoria" id="categoria" class="form-control select2-consulta">
                                <option value="">Todas las Categorías</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary btn-buscar-compacto">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="table-responsive contenedor-tabla">
            <table class="table-moderna">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Empresa / Cco</th>
                        <th>Categoría / Obs.</th>
                        <th>Creación</th>
                        <th>SLA</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Adjuntos</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                </tbody>
            </table>
        </div>

        <div class="paginacion-moderna" style="flex-shrink: 0;">
            <div id="paginacionInfo" class="text-bold fw-normal">Mostrando 0 registros</div>
            <div class="pag-botones" id="paginacionBotones"></div>
        </div>

    </div>
</div>