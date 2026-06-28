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

        /* Estilos para el buscador con datalist */
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
        }

        .searchable-select-wrapper input[type="text"]:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .searchable-select-wrapper datalist {
            position: absolute;
            background-color: white;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            z-index: 1000;
        }

        .searchable-select-wrapper datalist option {
            padding: 0.375rem 0.75rem;
            cursor: pointer;
        }

        .searchable-select-wrapper datalist option:hover {
            background-color: #0d6efd;
            color: white;
        }

        .searchable-select-wrapper .selected-lote-display {
            margin-top: 0.5rem;
            padding: 0.375rem 0.75rem;
            background-color: #e9ecef;
            border-radius: 0.25rem;
            font-weight: 500;
            display: none;
        }

        .searchable-select-wrapper .selected-lote-display.visible {
            display: block;
        }

        /* Panel de Debug */
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
    <input type="hidden" id="bearerToken" value="{{ $token }}">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card">
                    <div class="card-header">
                        {{ __('Tomar Medición') }}
                        <button type="button" id="btnToggleDebug" class="btn btn-sm btn-secondary float-end">
                            <i class="bi bi-bug"></i> Debug
                        </button>
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <!-- Panel de Debug -->
                        <div id="debugPanel" class="debug-info">
                            <h6>Log de Debug</h6>
                            <div id="debugLog"></div>
                        </div>

                        <div class="loader loader-default" id="spin" data-text="Consultando..."></div>

                        <!-- Toast -->
                        <div class="toast-container" style="position: absolute; bottom: 0; right: 0;">
                            <div class="toast" id="myToast" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="toast-header">
                                    <i class="bi bi-info-square"></i> &nbsp;
                                    <strong class="mr-auto" id="tituloToast">Transacción exitosa!</strong>
                                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <b>
                                    <div class="toast-body" id="subtituloToast">
                                        Hello, world! This is a toast message.
                                    </div>
                                </b>
                            </div>
                        </div>

                        <div id='divCartelAlerta'></div>

                        <form name="ajaxform" id="ajaxform">
                            <div class="form-group">
                                <label>Seleccione Nº de Lote</label>
                                <!-- Buscador con datalist -->
                                <div class="searchable-select-wrapper">
                                    <input type="text" 
                                           id="buscadorLotes" 
                                           class="form-control" 
                                           placeholder="Escriba el número de lote para buscar..." 
                                           autocomplete="off"
                                           list="listaLotes">
                                    <datalist id="listaLotes">
                                        @foreach($lotes as $lote => $idLote)
                                            @if($idLote->lote != '0')
                                                <option value="{{ $idLote->lote }}">Lote {{ $idLote->lote }}</option>
                                            @endif
                                        @endforeach
                                    </datalist>
                                    <div id="selectedLoteDisplay" class="selected-lote-display">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        Lote seleccionado: <span id="selectedLoteText">-</span>
                                    </div>
                                    <input type="hidden" id="selectorLotes" name="lote" value="">
                                </div>
                                <small class="text-muted" id="loteStatus">Escriba un número de lote para buscar</small>
                            </div>

                            <div class="form-group">
                                <label>Código de Medidor</label>
                                <input type="text" disabled name="codMedidor" id="codMedidor" class="form-control" placeholder="N/A" required>
                                <small class="text-muted" id="medidorStatus">Esperando selección...</small>
                            </div>

                            <div class="form-group">
                                <label>Periodo Entre Tomas</label>
                                <input type="number" min="1" name="periodo" id="periodo" class="form-control" placeholder="30" value="30" required>
                            </div>

                            <div class="form-group">
                                <label>Fecha Anterior de Toma</label>
                                <input type="date" disabled name="tomaAnt" id="tomaAnt" required class="form-control" placeholder="N/A">
                            </div>

                            <div class="form-group">
                                <label>Toma Anterior</label>
                                <input type="number" min="0" disabled name="tomaAnterior" id="tomaAnterior" class="form-control" placeholder="N/A" required>
                            </div>

                            <div class="form-group">
                                <label>Fecha Actual de Toma</label>
                                <input type="date" class="form-control" id="fechaToma" name="trip-start" required>
                            </div>

                            <div class="form-group">
                                <label>Vencimiento</label>
                                <input type="date" disabled class="form-control" id="vencimiento" name="trip-start" required>
                            </div>

                            <div class="form-group">
                                <label>Valor Medido</label>
                                <input type="number" min="0" name="valorMedido" id="valorMedido" class="form-control" placeholder="N/A" required>
                            </div>

                            <div class="form-group">
                                <label>Inspector</label>
                                <input type="text" disabled name="inspector" id="inspector" class="form-control" value="{{ Auth::user()->name }}" placeholder="{{ Auth::user()->name }}" required>
                            </div>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col">
                                        <label>Foto</label>
                                        <div class="input-group">
                                            <input type="text" name="foto" id="foto" class="form-control" value="N/A" placeholder="N/A" required>
                                            <button type="button" id="btnSubirFoto" class="btn btn-success" disabled>
                                                <i class="bi bi-cloud-arrow-up"></i> Subir Foto
                                            </button>
                                        </div>
                                        <br>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="sinFoto">
                                            <label class="form-check-label" for="sinFoto">No incluir foto</label>
                                        </div>
                                        <div id="uploadStatus" class="mt-2"></div>
                                    </div>
                                </div>
                                <br>
                                <div class="form-group">
                                    <button type="button" id="btnActivarCamara" disabled class="btn btn-primary d-flex justify-content-center col-md-12">
                                        <i style="height:0px;padding:0px 10px 10px 10px;margin-top:-4px;" class="bi bi-camera"></i>Activar Cámara
                                    </button>
                                </div>
                            </div>

                            <!-- CÁMARA -->
                            <main id="webcam-app">
                                <div class="form-control webcam-start" id="webcam-control" style="display: none;">
                                    <label class="form-switch">
                                        <input type="checkbox" id="webcam-switch" disabled>
                                        <i id="iconoCamara"></i>
                                        <span id="webcam-caption">Activar Cámara</span>
                                    </label>
                                </div>

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
                                            <a href="#" id="download-photo" download="selfie.png" target="_blank" title="Guardar Foto" class="control-wrapper d-none">
                                                <span class="control-label">Guardar Foto</span>
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

                            <hr class="style7">

                            <div class="form-group">
                                <div class="d-flex justify-content-center">
                                    <button id="btnGuardarMedicion" class="btn btn-success save-data" disabled>
                                        <i class="bi bi-cloud-arrow-up"></i>Guardar Medición
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de éxito -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Éxito</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    La medición se almacenó con éxito.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal" id="btnReloadPage">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @parent
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

            log('🚀 Iniciando aplicación de medición con buscador');
            log('📋 DOM completamente cargado');

            // ============================================
            // REFERENCIAS A ELEMENTOS
            // ============================================
            const buscadorInput = document.getElementById('buscadorLotes');
            const hiddenSelect = document.getElementById('selectorLotes');
            const selectedLoteDisplay = document.getElementById('selectedLoteDisplay');
            const selectedLoteText = document.getElementById('selectedLoteText');
            const codMedidorInput = document.getElementById('codMedidor');
            const medidorStatus = document.getElementById('medidorStatus');
            const loteStatus = document.getElementById('loteStatus');
            const fechaTomaInput = document.getElementById('fechaToma');
            const vencimientoInput = document.getElementById('vencimiento');
            const periodoInput = document.getElementById('periodo');
            const tomaAntInput = document.getElementById('tomaAnt');
            const tomaAnteriorInput = document.getElementById('tomaAnterior');
            const valorMedidoInput = document.getElementById('valorMedido');
            const btnGuardar = document.getElementById('btnGuardarMedicion');
            const btnActivarCamara = document.getElementById('btnActivarCamara');
            const fotoInput = document.getElementById('foto');
            const btnSubirFoto = document.getElementById('btnSubirFoto');
            const sinFotoCheckbox = document.getElementById('sinFoto');
            const datalist = document.getElementById('listaLotes');

            log('📌 Elementos encontrados:', {
                buscador: !!buscadorInput,
                hiddenSelect: !!hiddenSelect,
                datalist: !!datalist,
                opcionesDatalist: datalist ? datalist.options.length : 0
            });

            // ============================================
            // FUNCIÓN PARA OBTENER DATOS DEL LOTE
            // ============================================
            function obtenerDatosLote(valor) {
                log(`🔍 Consultando datos para lote: "${valor}"`, 'info');
                loteStatus.textContent = '⏳ Consultando...';
                loteStatus.style.color = '#ffc107';

                if (!valor || valor === '') {
                    log('⚠️ Valor vacío, limpiando campos', 'warning');
                    limpiarCampos();
                    return;
                }

                const token = document.getElementById('bearerToken').value;
                log('🔑 Token obtenido', 'info', { tokenLength: token?.length || 0 });

                const url = `/api/getMedidor?lote=${valor}`;
                log(`🌐 Haciendo petición a: ${url}`, 'info');

                axios.get(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    log('✅ Petición exitosa', 'success', response.data);
                    const data = response.data;
                    
                    // Actualizar campos
                    codMedidorInput.value = data.medidor || 'N/A';
                    tomaAntInput.value = data.fecha_anterior || '';
                    tomaAnteriorInput.value = data.medidor_anterior || 0;
                    
                    // Calcular vencimiento
                    if (fechaTomaInput.value && periodoInput.value) {
                        calcularVencimiento();
                    }
                    
                    medidorStatus.textContent = `✅ Medidor: ${data.medidor || 'N/A'}`;
                    medidorStatus.style.color = '#198754';
                    loteStatus.textContent = `✅ Lote ${valor} cargado`;
                    loteStatus.style.color = '#198754';
                    
                    // Habilitar botones
                    btnActivarCamara.disabled = false;
                    btnGuardar.disabled = false;
                    
                    log('📊 Datos del lote cargados correctamente', 'success', {
                        medidor: data.medidor,
                        fecha_anterior: data.fecha_anterior,
                        medidor_anterior: data.medidor_anterior
                    });
                })
                .catch(error => {
                    log('❌ Error en la petición', 'error', {
                        message: error.message,
                        status: error.response?.status,
                        data: error.response?.data
                    });
                    limpiarCampos();
                    loteStatus.textContent = `❌ Error: ${error.response?.data?.message || error.message}`;
                    loteStatus.style.color = '#dc3545';
                });
            }

            function limpiarCampos() {
                codMedidorInput.value = '';
                tomaAntInput.value = '';
                tomaAnteriorInput.value = '';
                vencimientoInput.value = '';
                medidorStatus.textContent = '⚠️ Seleccione un lote';
                medidorStatus.style.color = '#ffc107';
                loteStatus.textContent = '⚠️ Seleccione un lote';
                loteStatus.style.color = '#ffc107';
                btnActivarCamara.disabled = true;
                btnGuardar.disabled = true;
                selectedLoteDisplay.classList.remove('visible');
                hiddenSelect.value = '';
            }

            // ============================================
            // CALCULAR VENCIMIENTO
            // ============================================
            function calcularVencimiento() {
                const fechaToma = fechaTomaInput.value;
                const periodo = parseInt(periodoInput.value) || 30;
                
                if (fechaToma) {
                    const fecha = new Date(fechaToma);
                    fecha.setDate(fecha.getDate() + periodo);
                    const year = fecha.getFullYear();
                    const month = String(fecha.getMonth() + 1).padStart(2, '0');
                    const day = String(fecha.getDate()).padStart(2, '0');
                    vencimientoInput.value = `${year}-${month}-${day}`;
                    log('📅 Vencimiento calculado:', 'info', { fechaToma, periodo, vencimiento: vencimientoInput.value });
                }
            }

            // ============================================
            // EVENTOS DEL BUSCADOR
            // ============================================
            buscadorInput.addEventListener('input', function(e) {
                const valor = this.value.trim();
                log('🔎 Buscando lote:', 'info', { valor });
                
                // Buscar en el datalist si existe la opción
                let encontrado = false;
                for (let i = 0; i < datalist.options.length; i++) {
                    if (datalist.options[i].value === valor) {
                        encontrado = true;
                        break;
                    }
                }
                
                if (encontrado && valor !== '') {
                    // Seleccionar el lote
                    hiddenSelect.value = valor;
                    selectedLoteText.textContent = valor;
                    selectedLoteDisplay.classList.add('visible');
                    loteStatus.textContent = `✅ Lote ${valor} seleccionado`;
                    loteStatus.style.color = '#198754';
                    
                    log('📌 Lote seleccionado desde buscador:', 'success', { valor });
                    obtenerDatosLote(valor);
                } else if (valor === '') {
                    // Limpiar selección
                    hiddenSelect.value = '';
                    selectedLoteDisplay.classList.remove('visible');
                    limpiarCampos();
                    loteStatus.textContent = 'Escriba un número de lote para buscar';
                    loteStatus.style.color = '#6c757d';
                } else {
                    // Búsqueda en curso pero no coincidencia exacta
                    loteStatus.textContent = `🔍 Buscando "${valor}"...`;
                    loteStatus.style.color = '#ffc107';
                }
            });

            // También permitir selección desde el datalist con click
            buscadorInput.addEventListener('change', function(e) {
                const valor = this.value.trim();
                if (valor) {
                    // Verificar si existe en el datalist
                    let existe = false;
                    for (let i = 0; i < datalist.options.length; i++) {
                        if (datalist.options[i].value === valor) {
                            existe = true;
                            break;
                        }
                    }
                    if (existe) {
                        hiddenSelect.value = valor;
                        selectedLoteText.textContent = valor;
                        selectedLoteDisplay.classList.add('visible');
                        loteStatus.textContent = `✅ Lote ${valor} seleccionado`;
                        loteStatus.style.color = '#198754';
                        log('📌 Lote seleccionado por cambio:', 'success', { valor });
                        obtenerDatosLote(valor);
                    }
                }
            });

            // ============================================
            // EVENTOS DE PERIODO Y FECHA
            // ============================================
            periodoInput.addEventListener('change', function() {
                log('🔄 Periodo cambiado:', 'info', { periodo: this.value });
                calcularVencimiento();
            });

            fechaTomaInput.addEventListener('change', function() {
                log('🔄 Fecha de toma cambiada:', 'info', { fecha: this.value });
                calcularVencimiento();
                setNombreFoto();
            });

            // ============================================
            // EVENTO SIN FOTO
            // ============================================
            sinFotoCheckbox.addEventListener('change', function() {
                log('📸 Sin foto:', 'info', { checked: this.checked });
                if (this.checked) {
                    fotoInput.value = 'N/A';
                    btnSubirFoto.disabled = true;
                } else {
                    btnSubirFoto.disabled = false;
                }
            });

            // ============================================
            // FUNCIONES DE NOMBRE DE FOTO
            // ============================================
            function formatDate2(date, zona) {
                if (!date) return '';
                let dia = new Date(date);
                let diaCorregido = new Date(dia.getTime() - dia.getTimezoneOffset() * -60000);
                var d = new Date(diaCorregido),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                switch (zona) {
                    case 'es': return [day, month, year].join('-');
                    case 'en': return [year, month, day].join('-');
                    default: return [year, month, day].join('-');
                }
            }

            function setNombreFoto() {
                let codLote = hiddenSelect.value;
                let fechaToma = fechaTomaInput.value;
                if (codLote && fechaToma) {
                    let diaCorregido = formatDate2(fechaToma, 'es');
                    fotoInput.value = 'talar2_' + codLote + '_' + diaCorregido + '.png';
                    log('📸 Nombre de foto generado:', 'info', { nombre: fotoInput.value });
                }
            }

            // ============================================
            // INICIALIZACIÓN
            // ============================================
            log('🔧 Inicializando aplicación...');
            
            // Establecer fecha actual
            const hoy = new Date();
            const year = hoy.getFullYear();
            const month = String(hoy.getMonth() + 1).padStart(2, '0');
            const day = String(hoy.getDate()).padStart(2, '0');
            fechaTomaInput.value = `${year}-${month}-${day}`;
            log('📅 Fecha actual establecida:', 'info', { fecha: fechaTomaInput.value });

            // Calcular vencimiento inicial
            calcularVencimiento();

            log('✅ Aplicación inicializada correctamente');
            log('💡 Escribe un número de lote en el buscador para comenzar');

            // ============================================
            // CÓDIGO DE CÁMARA (MANTENIDO)
            // ============================================
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');
            let stream;
            let currentPhotoData = null;

            // Activar cámara
            btnActivarCamara.addEventListener('click', function() {
                log('📷 Activando cámara', 'info');
                $('.md-modal').addClass('md-show');
                startCamera();
            });

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
                document.querySelector('#download-photo').href = currentPhotoData;
                afterTakePhoto();
                btnSubirFoto.disabled = false;
                log('📸 Foto capturada', 'success', { nombre: fotoInput.value });
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
                setNombreFoto();
                fechaTomaInput.disabled = true;
                let nombreFoto = fotoInput.value;
                document.getElementById('download-photo').download = nombreFoto;
            }

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

            document.getElementById('exit-app').addEventListener('click', function() {
                log('🚪 Saliendo de la cámara', 'info');
                stopCamera();
                removeCapture();
                document.getElementById('webcam-switch').checked = false;
            });

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
            // SUBIR FOTO
            // ============================================
            function subirFotoAlServidor() {
                const nombreFoto = fotoInput.value;
                log('📤 Subiendo foto al servidor:', 'info', { nombre: nombreFoto });

                if (nombreFoto === 'N/A' || !currentPhotoData) {
                    log('⚠️ No hay foto para subir', 'warning');
                    return;
                }

                const canvas = document.getElementById('canvas');
                const imageData = canvas.toDataURL('image/png');
                
                const uploadStatus = document.getElementById('uploadStatus');
                uploadStatus.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Subiendo...</span></div> Subiendo foto...';
                
                const formData = new FormData();
                formData.append('foto', dataURLtoFile(imageData, nombreFoto));
                formData.append('_token', csrfToken);
                
                fetch("{{ route('subir_foto_medicion') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    log('📤 Respuesta del servidor:', 'info', data);
                    if (data.estado === 'éxito') {
                        uploadStatus.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Foto subida correctamente</span>';
                        log('✅ Foto subida exitosamente', 'success');
                    } else {
                        uploadStatus.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Error: ${data.mensaje}</span>`;
                        log('❌ Error al subir foto:', 'error', data);
                    }
                })
                .catch(error => {
                    uploadStatus.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Error de conexión</span>';
                    log('❌ Error de conexión:', 'error', error);
                });
            }

            function dataURLtoFile(dataurl, filename) {
                const arr = dataurl.split(',');
                const mime = arr[0].match(/:(.*?);/)[1];
                const bstr = atob(arr[1]);
                let n = bstr.length;
                const u8arr = new Uint8Array(n);
                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }
                return new File([u8arr], filename, { type: mime });
            }

            document.getElementById('btnSubirFoto').addEventListener('click', subirFotoAlServidor);

            // ============================================
            // GUARDAR MEDICIÓN
            // ============================================
            document.getElementById('btnGuardarMedicion').addEventListener('click', function() {
                log('💾 Intentando guardar medición', 'info');
                
                const formData = {
                    lote: hiddenSelect.value,
                    medidor: codMedidorInput.value,
                    periodo: periodoInput.value,
                    fechaAnt: tomaAntInput.value,
                    tomaAnterior: tomaAnteriorInput.value,
                    fechaMedicion: fechaTomaInput.value,
                    vencimiento: vencimientoInput.value,
                    valorMedido: valorMedidoInput.value,
                    inspector: document.getElementById('inspector').value,
                    foto: fotoInput.value
                };

                log('📋 Datos del formulario:', 'info', formData);

                const token = document.getElementById('bearerToken').value;

                axios.post('/api/postMed', formData, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    log('✅ Medición guardada exitosamente', 'success', response.data);
                    $('#successModal').modal('show');
                    document.getElementById('btnReloadPage').addEventListener('click', function() {
                        location.reload();
                    });
                })
                .catch(error => {
                    log('❌ Error al guardar medición:', 'error', {
                        message: error.message,
                        response: error.response?.data
                    });
                    // Mostrar error en toast
                    $('#tituloToast').text('Error');
                    $('#subtituloToast').text(error.response?.data?.message || 'Error al guardar la medición');
                    $('#myToast').toast('show');
                });
            });

            log('🎯 Aplicación lista. Escribe un número de lote para buscar y seleccionar.');
        });
    </script>
@stop