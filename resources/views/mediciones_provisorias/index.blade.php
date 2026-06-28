@extends('adminlte::page')

@section('css')
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel='stylesheet' type='text/css' media='screen' href="{{ asset('../js/assets/style/webcam-demo.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href="{{ asset('css/css-loader.css') }}" rel="stylesheet">
    <style>
        .container {
            max-height: calc(100vh - 100px);
            overflow-y: auto;
        }

        #cameraControls {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .control-wrapper {
            text-align: center;
            display: inline-block;
        }

        .control-label {
            display: block;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            margin-bottom: 4px;
            font-size: 0.9em;
        }

        .filter-container {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }

        .form-check.filter-switch {
            padding-left: 0;
        }

        .form-check-input.filter-switch-input {
            margin-left: 0;
            transform: scale(1.5);
        }

        .form-check-label.filter-switch-label {
            font-weight: 500;
            margin-left: 10px;
        }

        /* Estilos para el buscador con lista personalizada */
        .searchable-select-wrapper {
            position: relative;
            width: 100%;
        }

        .searchable-select-wrapper input[type="text"] {
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            cursor: text;
        }

        .searchable-select-wrapper input[type="text"]:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Lista de sugerencias personalizada */
        .suggestions-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ced4da;
            border-top: none;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
        }

        .suggestions-list.show {
            display: block;
        }

        .suggestions-list .suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s;
        }

        .suggestions-list .suggestion-item:hover {
            background-color: #0d6efd;
            color: white;
        }

        .suggestions-list .suggestion-item:hover .badge-medido,
        .suggestions-list .suggestion-item:hover .badge-sin-medir {
            background-color: rgba(255,255,255,0.3);
            color: white;
        }

        .suggestions-list .suggestion-item .badge-medido {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 10px;
            background-color: #198754;
            color: white;
            flex-shrink: 0;
        }

        .suggestions-list .suggestion-item .badge-sin-medir {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 10px;
            background-color: #ffc107;
            color: #000;
            flex-shrink: 0;
        }

        .suggestions-list .no-results {
            padding: 12px;
            text-align: center;
            color: #6c757d;
        }

        .selected-lote-display {
            margin-top: 0.5rem;
            padding: 0.375rem 0.75rem;
            background-color: #e9ecef;
            border-radius: 0.25rem;
            font-weight: 500;
            display: none;
        }

        .selected-lote-display.visible {
            display: block;
        }

        /* Scroll personalizado para la lista */
        .suggestions-list::-webkit-scrollbar {
            width: 6px;
        }

        .suggestions-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .suggestions-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .suggestions-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Estilos para debugging */
        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }
        .debug-info.visible {
            display: block;
        }
        .debug-info .log-entry {
            padding: 2px 0;
            border-bottom: 1px solid #eee;
        }
        .debug-info .log-entry.error {
            color: #dc3545;
        }
        .debug-info .log-entry.success {
            color: #198754;
        }
        .debug-info .log-entry.info {
            color: #0d6efd;
        }
        .debug-info .log-entry.warning {
            color: #ffc107;
        }
    </style>
@stop

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card">
                    <div class="card-header">
                        {{ __('Tomar Medición Provisoria') }}
                        <button type="button" id="btnToggleDebug" class="btn btn-sm btn-secondary float-end">
                            <i class="bi bi-bug"></i> Debug
                        </button>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Panel de Debug -->
                        <div id="debugPanel" class="debug-info">
                            <h6>Log de Debug</h6>
                            <div id="debugLog"></div>
                        </div>

                        <div class="filter-container">
                            <div class="form-check filter-switch">
                                <input class="form-check-input filter-switch-input" type="checkbox" id="mostrarSoloSinMedicion" checked>
                                <label class="form-check-label filter-switch-label" for="mostrarSoloSinMedicion">
                                    Mostrar solo lotes sin medición
                                </label>
                            </div>
                            <div id="contadorLotes" class="option-counter mt-2"></div>
                        </div>

                        <form id="medicionProvisoriaForm" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label>Nº de Lote</label>
                                <!-- Buscador con lista personalizada -->
                                <div class="searchable-select-wrapper">
                                    <input type="text" 
                                           id="buscadorLotes" 
                                           class="form-control" 
                                           placeholder="Escriba o haga clic para ver los lotes..." 
                                           autocomplete="off">
                                    <div id="suggestionsList" class="suggestions-list"></div>
                                    <div id="selectedLoteDisplay" class="selected-lote-display">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        Lote seleccionado: <span id="selectedLoteText">-</span>
                                    </div>
                                    <input type="hidden" id="selectorLotes" name="lote" value="">
                                </div>
                                <small class="text-muted" id="loteStatus">Escriba o haga clic para buscar un lote</small>
                            </div>

                            <div class="form-group">
                                <label>Código de Medidor</label>
                                <input type="text" name="medidor" id="medidor" class="form-control" readonly>
                                <small class="text-muted" id="medidorStatus">Esperando selección...</small>
                            </div>

                            <div class="form-group">
                                <label>Valor Medido</label>
                                <input type="number" step="0.01" min="0" name="consumo" id="consumo" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Fecha de Medición</label>
                                <input type="date" name="fecha_medicion" id="fecha_medicion" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="form-group">
                                <label>Foto</label>
                                <div class="input-group">
                                    <input type="text" name="foto" id="foto" class="form-control" value="N/A" placeholder="N/A" readonly>
                                    <button type="button" id="btnActivarCamara" class="btn btn-primary">
                                        <i class="bi bi-camera"></i> Activar Cámara
                                    </button>
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="sinFoto">
                                    <label class="form-check-label" for="sinFoto">No incluir foto</label>
                                </div>
                                <div id="uploadStatus" class="mt-2"></div>
                            </div>

                            <div class="form-group mt-3">
                                <button type="button" id="btnGuardarMedicion" class="btn btn-success">
                                    <i class="bi bi-cloud-arrow-up"></i> Guardar Medición
                                </button>
                            </div>
                        </form>

                        <main id="webcam-app">
                            <div id="errorMsg" class="col-12 col-md-6 alert-danger d-none">
                                Fallo al inicializar la cámara, habilite el permiso para capturar fotos. <br/>
                                <button id="closeError" class="btn btn-primary ml-3">OK</button>
                            </div>
                            <div class="md-modal md-effect-12">
                                <div id="app-panel" class="app-panel md-content row p-0 m-0">
                                    <div id="webcam-container" class="webcam-container col-12 d-none p-0 m-0">
                                        <video id="webcam" autoplay playsinline width="640" height="480"></video>
                                        <canvas id="canvas" class="d-none"></canvas>
                                        <div class="flash"></div>
                                        <audio id="snapSound" src="{{ asset('../js/assets/audio/snap.wav') }}" preload="auto"></audio>
                                    </div>
                                    <div id="cameraControls" class="cameraControls">
                                        <a href="#" id="exit-app" title="Salir" class="control-wrapper d-none">
                                            <span class="control-label">Salir</span>
                                            <i class="material-icons">exit_to_app</i>
                                        </a>
                                        <a href="#" id="take-photo" title="Tomar Foto" class="control-wrapper">
                                            <span class="control-label">Tomar Foto</span>
                                            <i class="material-icons">camera_alt</i>
                                        </a>
                                        <a href="#" id="download-photo" title="Descargar Foto" class="control-wrapper d-none">
                                            <span class="control-label">Descargar Foto</span>
                                            <i class="material-icons">file_download</i>
                                        </a>
                                        <a href="#" id="resume-camera" title="Reanudar Cámara" class="control-wrapper d-none">
                                            <span class="control-label">Reanudar Cámara</span>
                                            <i class="material-icons">autorenew</i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="md-overlay"></div>
                        </main>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de éxito -->
    <div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="confirmModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Aviso Importante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Transacción exitosa! La medición provisoria se registró correctamente.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================
            // CONFIGURACIÓN DE DEBUG
            // ============================================
            const DEBUG = true;
            const debugLog = document.getElementById('debugLog');
            const debugPanel = document.getElementById('debugPanel');
            
            function log(message, type = 'info', data = null) {
                if (!DEBUG) return;
                
                const timestamp = new Date().toLocaleTimeString();
                const entry = document.createElement('div');
                entry.className = `log-entry ${type}`;
                
                let content = `[${timestamp}] ${message}`;
                if (data !== null) {
                    content += `\n  📦 Data: ${JSON.stringify(data, null, 2)}`;
                }
                entry.textContent = content;
                
                debugLog.appendChild(entry);
                debugPanel.scrollTop = debugPanel.scrollHeight;
                console.log(`[DEBUG] ${message}`, data || '');
            }

            // Toggle debug panel
            document.getElementById('btnToggleDebug').addEventListener('click', function() {
                debugPanel.classList.toggle('visible');
                log('Debug panel toggled');
            });

            log('🚀 Iniciando aplicación de medición provisoria con buscador personalizado');
            log('📋 DOM completamente cargado');

            // ============================================
            // DATOS DE LOTES DESDE PHP
            // ============================================
            const lotesData = [
                @foreach($lotes as $lote)
                    @if($lote->lote != '0')
                        {
                            lote: "{{ $lote->lote }}",
                            tieneMedicion: {{ $lote->tiene_medicion ? 'true' : 'false' }}
                        },
                    @endif
                @endforeach
            ];

            log('📊 Datos de lotes cargados:', 'info', { total: lotesData.length });

            // ============================================
            // REFERENCIAS A ELEMENTOS
            // ============================================
            const buscadorInput = document.getElementById('buscadorLotes');
            const suggestionsList = document.getElementById('suggestionsList');
            const hiddenSelect = document.getElementById('selectorLotes');
            const selectedLoteDisplay = document.getElementById('selectedLoteDisplay');
            const selectedLoteText = document.getElementById('selectedLoteText');
            const medidorInput = document.getElementById('medidor');
            const medidorStatus = document.getElementById('medidorStatus');
            const loteStatus = document.getElementById('loteStatus');
            const mostrarSoloSinMedicionCheckbox = document.getElementById('mostrarSoloSinMedicion');
            const contadorLotes = document.getElementById('contadorLotes');
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');
            const downloadPhotoLink = document.getElementById('download-photo');

            log('📌 Elementos encontrados:', {
                buscador: !!buscadorInput,
                suggestionsList: !!suggestionsList,
                hiddenSelect: !!hiddenSelect,
                checkbox: !!mostrarSoloSinMedicionCheckbox
            });

            let stream;
            let currentPhotoData = null;
            let currentPhotoName = null;
            let suggestionsVisible = false;

            // ============================================
            // FUNCIONES DE FILTRO Y BÚSQUEDA
            // ============================================
            function getLotesFiltrados() {
                const soloSinMedicion = mostrarSoloSinMedicionCheckbox.checked;
                return lotesData.filter(lote => {
                    if (soloSinMedicion) {
                        return !lote.tieneMedicion;
                    }
                    return true;
                });
            }

            function buscarLotes(termino) {
                const filtrados = getLotesFiltrados();
                if (!termino || termino.trim() === '') {
                    // SIN LÍMITE - mostrar todos los lotes
                    return filtrados;
                }
                const busqueda = termino.toLowerCase().trim();
                return filtrados.filter(lote => 
                    lote.lote.toLowerCase().includes(busqueda)
                );
            }

            function actualizarContador() {
                const filtrados = getLotesFiltrados();
                contadorLotes.textContent = `Mostrando ${filtrados.length} lotes disponibles`;
                log('📊 Contador actualizado:', 'info', { total: filtrados.length });
            }

            // ============================================
            // MOSTRAR SUGERENCIAS
            // ============================================
            function mostrarSugerencias() {
                const termino = buscadorInput.value;
                const resultados = buscarLotes(termino);
                renderSuggestions(resultados);
                suggestionsVisible = true;
                log('📋 Mostrando sugerencias:', 'info', { count: resultados.length });
            }

            // ============================================
            // FUNCIÓN PARA CARGAR MEDIDOR
            // ============================================
            function cargarMedidor(valor) {
                log(`🔍 Intentando cargar medidor para lote: "${valor}"`, 'info');
                medidorStatus.textContent = '⏳ Cargando...';
                medidorStatus.style.color = '#ffc107';

                if (!valor || valor === '') {
                    log('⚠️ Valor vacío, limpiando campo', 'warning');
                    medidorInput.value = '';
                    medidorStatus.textContent = '⚠️ Seleccione un lote';
                    medidorStatus.style.color = '#ffc107';
                    return;
                }

                const url = `/obtener-medidor/${valor}`;
                log(`🌐 Haciendo petición a: ${url}`, 'info');

                axios.get(url)
                    .then(response => {
                        log('✅ Petición exitosa', 'success', response.data);
                        const medidor = response.data.medidor;
                        medidorInput.value = medidor;
                        medidorStatus.textContent = `✅ Medidor cargado: ${medidor || 'N/A'}`;
                        medidorStatus.style.color = '#198754';
                        log(`📊 Medidor asignado: "${medidor}"`, 'success');
                    })
                    .catch(error => {
                        log('❌ Error en la petición', 'error', {
                            message: error.message,
                            status: error.response?.status,
                            data: error.response?.data
                        });
                        medidorInput.value = '';
                        medidorStatus.textContent = `❌ Error: ${error.message}`;
                        medidorStatus.style.color = '#dc3545';
                    });
            }

            // ============================================
            // RENDERIZAR SUGERENCIAS
            // ============================================
            function renderSuggestions(resultados) {
                suggestionsList.innerHTML = '';
                
                if (resultados.length === 0) {
                    const noResults = document.createElement('div');
                    noResults.className = 'no-results';
                    noResults.textContent = 'No se encontraron lotes';
                    suggestionsList.appendChild(noResults);
                    suggestionsList.classList.add('show');
                    return;
                }

                // Mostrar todos los resultados sin límite
                resultados.forEach(lote => {
                    const item = document.createElement('div');
                    item.className = 'suggestion-item';
                    
                    const textSpan = document.createElement('span');
                    textSpan.textContent = `Lote ${lote.lote}`;
                    
                    const badge = document.createElement('span');
                    if (lote.tieneMedicion) {
                        badge.className = 'badge-medido';
                        badge.textContent = '✓ Medido';
                    } else {
                        badge.className = 'badge-sin-medir';
                        badge.textContent = 'Sin medición';
                    }
                    
                    item.appendChild(textSpan);
                    item.appendChild(badge);
                    
                    item.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        seleccionarLote(lote.lote);
                    });
                    
                    suggestionsList.appendChild(item);
                });
                
                suggestionsList.classList.add('show');
            }

            // ============================================
            // SELECCIONAR LOTE
            // ============================================
            function seleccionarLote(valor) {
                log('📌 Seleccionando lote:', 'info', { valor });
                
                hiddenSelect.value = valor;
                buscadorInput.value = valor;
                selectedLoteText.textContent = valor;
                selectedLoteDisplay.classList.add('visible');
                loteStatus.textContent = `✅ Lote ${valor} seleccionado`;
                loteStatus.style.color = '#198754';
                
                // Ocultar sugerencias
                suggestionsList.classList.remove('show');
                suggestionsVisible = false;
                
                // Cargar medidor
                cargarMedidor(valor);
            }

            // ============================================
            // EVENTOS DEL BUSCADOR
            // ============================================
            
            // Click en el input - mostrar todos los lotes disponibles
            buscadorInput.addEventListener('click', function(e) {
                log('🖱️ Click en buscador', 'info');
                // Si ya hay sugerencias visibles, no hacer nada
                if (suggestionsVisible) {
                    return;
                }
                // Mostrar todos los lotes según el filtro
                mostrarSugerencias();
            });

            // Focus en el input - mostrar sugerencias si no hay
            buscadorInput.addEventListener('focus', function(e) {
                log('🎯 Focus en buscador', 'info');
                if (!suggestionsVisible && !this.value) {
                    mostrarSugerencias();
                }
            });

            // Input - filtrar mientras escribe
            buscadorInput.addEventListener('input', function(e) {
                const termino = this.value;
                log('🔎 Buscando:', 'info', { termino });
                
                if (termino === '') {
                    // Mostrar todos los lotes según filtro
                    mostrarSugerencias();
                    // Limpiar selección si el campo está vacío
                    if (hiddenSelect.value) {
                        hiddenSelect.value = '';
                        selectedLoteDisplay.classList.remove('visible');
                        medidorInput.value = '';
                        medidorStatus.textContent = '⚠️ Seleccione un lote';
                        medidorStatus.style.color = '#ffc107';
                        loteStatus.textContent = 'Escriba o haga clic para buscar un lote';
                        loteStatus.style.color = '#6c757d';
                    }
                    return;
                }
                
                const resultados = buscarLotes(termino);
                log('📊 Resultados encontrados:', 'info', { count: resultados.length });
                
                if (resultados.length > 0) {
                    renderSuggestions(resultados);
                    suggestionsVisible = true;
                } else {
                    renderSuggestions([]);
                    suggestionsVisible = true;
                }
            });

            // Cerrar sugerencias al hacer click fuera
            document.addEventListener('click', function(e) {
                const wrapper = document.querySelector('.searchable-select-wrapper');
                if (!wrapper.contains(e.target)) {
                    suggestionsList.classList.remove('show');
                    suggestionsVisible = false;
                }
            });

            // Seleccionar con Enter
            buscadorInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const termino = this.value.trim();
                    if (termino) {
                        const resultados = buscarLotes(termino);
                        if (resultados.length > 0) {
                            seleccionarLote(resultados[0].lote);
                        }
                    }
                    e.preventDefault();
                }
                // Tecla Escape para cerrar sugerencias
                if (e.key === 'Escape') {
                    suggestionsList.classList.remove('show');
                    suggestionsVisible = false;
                    this.blur();
                }
            });

            // ============================================
            // EVENTO DEL CHECKBOX DE FILTRO
            // ============================================
            mostrarSoloSinMedicionCheckbox.addEventListener('change', function() {
                log(`🔄 Checkbox cambiado: ${this.checked ? '✓ Marcado' : '✗ Desmarcado'}`, 'info');
                actualizarContador();
                
                // Si el buscador está enfocado o hay sugerencias, actualizar
                if (document.activeElement === buscadorInput || suggestionsVisible) {
                    mostrarSugerencias();
                }
            });

            // ============================================
            // INICIALIZACIÓN
            // ============================================
            log('🔧 Inicializando aplicación...');
            actualizarContador();
            
            log('📊 Estado inicial del medidor:', {
                value: medidorInput.value,
                status: medidorStatus.textContent
            });

            log('✅ Aplicación inicializada correctamente');
            log('💡 Haz clic en el buscador para ver TODOS los lotes disponibles');

            // ============================================
            // CÓDIGO DE CÁMARA (MANTENIDO)
            // ============================================

            // Activar cámara al hacer click en el botón
            document.getElementById('btnActivarCamara').addEventListener('click', function() {
                log('📷 Activando cámara', 'info');
                $('.md-modal').addClass('md-show');
                startCamera();
            });

            // Iniciar cámara
            function startCamera() {
                log('📷 Iniciando cámara...', 'info');
                const constraints = { video: { facingMode: { exact: "environment" } }, audio: false };
                navigator.mediaDevices.getUserMedia(constraints)
                    .then(s => {
                        log('✅ Cámara iniciada correctamente', 'success');
                        stream = s;
                        video.srcObject = stream;
                        video.play();
                        cameraStarted();
                    })
                    .catch(err => {
                        log('❌ Error al acceder a la cámara con constraint exacto:', 'error', err);
                        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                            .then(s => {
                                log('✅ Cámara iniciada en modo fallback', 'success');
                                stream = s;
                                video.srcObject = stream;
                                video.play();
                                cameraStarted();
                            })
                            .catch(error => {
                                log('❌ No se pudo acceder a la cámara:', 'error', error);
                                displayError(error);
                            });
                    });
            }

            function stopCamera() {
                log('📷 Deteniendo cámara', 'info');
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                cameraStopped();
            }

            function cameraStarted() {
                log('📷 Cámara activa', 'success');
                $("#errorMsg").addClass("d-none");
                $('.flash').hide();
                $("#webcam-caption").html("Activada");
                $("#webcam-control").removeClass("d-none");
                $("#webcam-control").removeClass("webcam-off");
                $("#webcam-control").addClass("webcam-on");
                $(".webcam-container").removeClass("d-none");
                $("#wpfront-scroll-top-container").addClass("d-none");
                window.scrollTo(0, 0);
            }

            function cameraStopped() {
                log('📷 Cámara detenida', 'info');
                $("#errorMsg").addClass("d-none");
                $("#wpfront-scroll-top-container").removeClass("d-none");
                $("#webcam-control").removeClass("webcam-on");
                $("#webcam-control").addClass("webcam-off");
                $(".webcam-container").addClass("d-none");
                $("#webcam-caption").html("Click to Start Camera");
                $('.md-modal').removeClass('md-show');
                $("#webcam-control").addClass("d-none");
            }

            // Tomar foto
            document.getElementById('take-photo').addEventListener('click', function() {
                log('📸 Tomando foto', 'info');
                beforeTakePhoto();
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                let ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                currentPhotoData = canvas.toDataURL('image/png');

                let codLote = hiddenSelect.value;
                let fechaToma = document.getElementById('fecha_medicion').value;
                let fechaFormateada = fechaToma.replace(/-/g, '');
                currentPhotoName = `talar2_${codLote}_${fechaFormateada}.png`;
                
                log(`📸 Foto capturada: ${currentPhotoName}`, 'success', { lote: codLote, fecha: fechaToma });

                document.getElementById('foto').value = currentPhotoData;
                afterTakePhoto();
            });

            downloadPhotoLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPhotoData) {
                    log('⬇️ Descargando foto', 'info');
                    const link = document.createElement('a');
                    link.href = currentPhotoData;
                    link.download = currentPhotoName;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    log('✅ Foto descargada', 'success');
                } else {
                    log('⚠️ No hay foto para descargar', 'warning');
                }
            });

            function beforeTakePhoto() {
                log('📸 Preparando captura...', 'info');
                $('.flash')
                    .show()
                    .animate({ opacity: 0.3 }, 500)
                    .fadeOut(500)
                    .css({ 'opacity': 0.7 });
                window.scrollTo(0, 0);
                $('#webcam-control').addClass('d-none');
                $('#cameraControls').addClass('d-none');
            }

            function afterTakePhoto() {
                log('📸 Captura completada', 'success');
                video.pause();
                $('#canvas').removeClass('d-none');
                $('#take-photo').addClass('d-none');
                $('#exit-app').removeClass('d-none');
                $('#download-photo').removeClass('d-none');
                $('#resume-camera').removeClass('d-none');
                $('#cameraControls').removeClass('d-none');
            }

            document.getElementById('exit-app').addEventListener('click', function() {
                log('🚪 Saliendo de la cámara', 'info');
                stopCamera();
                removeCapture();
            });

            function removeCapture() {
                log('🔄 Resetear vista de captura', 'info');
                $('#canvas').addClass('d-none');
                $('#webcam-control').removeClass('d-none');
                $('#cameraControls').removeClass('d-none');
                $('#take-photo').removeClass('d-none');
                $('#exit-app').addClass('d-none');
                $('#download-photo').addClass('d-none');
                $('#resume-camera').addClass('d-none');
            }

            document.getElementById('resume-camera').addEventListener('click', function() {
                log('▶️ Reanudando cámara', 'info');
                $('#canvas').addClass('d-none');
                $('#take-photo').removeClass('d-none');
                $('#exit-app').addClass('d-none');
                $('#download-photo').addClass('d-none');
                $('#resume-camera').addClass('d-none');
                video.play();
            });

            function displayError(err = '') {
                log('❌ Mostrando error:', 'error', err);
                if (err !== '') {
                    $("#errorMsg").html(err);
                }
                $("#errorMsg").removeClass("d-none");
            }

            // ============================================
            // GUARDAR MEDICIÓN
            // ============================================
            document.getElementById('btnGuardarMedicion').addEventListener('click', function() {
                log('💾 Intentando guardar medición', 'info');
                
                if (!hiddenSelect.value) {
                    log('⚠️ No hay lote seleccionado', 'warning');
                    medidorStatus.textContent = '⚠️ Seleccione un lote primero';
                    medidorStatus.style.color = '#dc3545';
                    return;
                }

                const form = document.getElementById('medicionProvisoriaForm');
                const formData = new FormData(form);

                const formDataObj = {};
                for (let [key, value] of formData.entries()) {
                    formDataObj[key] = value;
                }
                log('📋 Datos del formulario:', 'info', formDataObj);

                axios.post("{{ route('mediciones_provisorias.store') }}", formData)
                    .then(response => {
                        log('✅ Medición guardada exitosamente', 'success', response.data);
                        $('#modalExito').modal('show');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    })
                    .catch(error => {
                        log('❌ Error al guardar medición:', 'error', {
                            message: error.message,
                            response: error.response?.data
                        });
                        medidorStatus.textContent = `❌ Error: ${error.response?.data?.message || error.message}`;
                        medidorStatus.style.color = '#dc3545';
                    });
            });

            log('🎯 Aplicación lista. Haz clic en el buscador para ver TODOS los lotes disponibles.');
        });
    </script>
@stop