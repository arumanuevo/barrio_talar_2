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

        .custom-select-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .custom-select-wrapper select {
            display: block;
            width: 100%;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            appearance: auto;
            -webkit-appearance: auto;
            -moz-appearance: auto;
            cursor: pointer;
        }

        .custom-select-wrapper select:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .custom-select-wrapper select:disabled {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }

        .option-counter {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        /* Estilo para debugging */
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
                                <div class="custom-select-wrapper">
                                    <select name="lote" id="selectorLotes" class="form-control" required>
                                        <option value="" selected disabled>Seleccione un lote</option>
                                        @foreach($lotes as $lote)
                                            @if($lote->lote != '0')
                                                <option value="{{ $lote->lote }}" data-tiene-medicion="{{ $lote->tiene_medicion ? 'true' : 'false' }}">{{ $lote->lote }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
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
                
                // También al console
                console.log(`[DEBUG] ${message}`, data || '');
            }

            // Toggle debug panel
            document.getElementById('btnToggleDebug').addEventListener('click', function() {
                debugPanel.classList.toggle('visible');
                log('Debug panel toggled');
            });

            log('🚀 Iniciando aplicación de medición provisoria');
            log('📋 DOM completamente cargado');

            // ============================================
            // REFERENCIAS A ELEMENTOS
            // ============================================
            const selectElement = document.getElementById('selectorLotes');
            const medidorInput = document.getElementById('medidor');
            const medidorStatus = document.getElementById('medidorStatus');
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');
            const mostrarSoloSinMedicionCheckbox = document.getElementById('mostrarSoloSinMedicion');
            const downloadPhotoLink = document.getElementById('download-photo');
            const contadorLotes = document.getElementById('contadorLotes');
            
            log('📌 Elementos encontrados:', {
                select: !!selectElement,
                medidor: !!medidorInput,
                checkbox: !!mostrarSoloSinMedicionCheckbox
            });

            if (!selectElement) {
                log('❌ ERROR CRÍTICO: No se encuentra el elemento select', 'error');
                return;
            }

            let stream;
            let currentPhotoData = null;
            let currentPhotoName = null;

            // ============================================
            // FUNCIÓN PARA CARGAR MEDIDOR (CON DEBUG)
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
            // EVENTO CHANGE DEL SELECT (CON DEBUG)
            // ============================================
            log('📌 Configurando evento change del select');

            // Eliminar eventos anteriores (por si acaso)
            const newSelect = selectElement.cloneNode(true);
            selectElement.parentNode.replaceChild(newSelect, selectElement);
            const finalSelect = document.getElementById('selectorLotes');
            
            finalSelect.addEventListener('change', function(e) {
                const selectedValue = this.value;
                const selectedText = this.options[this.selectedIndex]?.text || 'N/A';
                const tieneMedicion = this.options[this.selectedIndex]?.dataset?.tieneMedicion || 'N/A';
                
                log('🔄 Evento CHANGE disparado', 'info', {
                    value: selectedValue,
                    text: selectedText,
                    tieneMedicion: tieneMedicion,
                    selectedIndex: this.selectedIndex
                });

                if (selectedValue && selectedValue !== '') {
                    log(`📌 Lote seleccionado: ${selectedValue} (${selectedText})`);
                    cargarMedidor(selectedValue);
                } else {
                    log('⚠️ Selección vacía o deseleccionada', 'warning');
                    medidorInput.value = '';
                    medidorStatus.textContent = '⚠️ Seleccione un lote';
                    medidorStatus.style.color = '#ffc107';
                }
            });

            // También escuchar el evento 'click' para debugging
            finalSelect.addEventListener('click', function(e) {
                log('🖱️ Click en el select', 'info', {
                    currentValue: this.value,
                    selectedIndex: this.selectedIndex
                });
            });

            // ============================================
            // FILTRO DE OPCIONES
            // ============================================
            function filtrarOpciones() {
                const mostrarSoloSinMedicion = mostrarSoloSinMedicionCheckbox.checked;
                const options = finalSelect.options;
                let opcionesVisibles = 0;
                let opcionesTotales = 0;

                log(`🔍 Aplicando filtro: mostrarSoloSinMedicion = ${mostrarSoloSinMedicion}`, 'info');

                for (let i = 0; i < options.length; i++) {
                    const option = options[i];
                    if (option.value === '') continue;
                    
                    opcionesTotales++;
                    const tieneMedicion = option.dataset.tieneMedicion === 'true';
                    const debeMostrar = !mostrarSoloSinMedicion || !tieneMedicion;
                    
                    option.style.display = debeMostrar ? '' : 'none';
                    if (debeMostrar) opcionesVisibles++;
                }

                log(`📊 Filtro aplicado: ${opcionesVisibles} de ${opcionesTotales} lotes visibles`, 'info');
                contadorLotes.textContent = `Mostrando ${opcionesVisibles} lotes disponibles`;

                // Si la opción seleccionada está oculta, resetear
                if (finalSelect.selectedIndex > 0) {
                    const selectedOption = finalSelect.options[finalSelect.selectedIndex];
                    if (selectedOption.style.display === 'none') {
                        log('⚠️ Opción seleccionada oculta, reseteando', 'warning');
                        finalSelect.value = '';
                        medidorInput.value = '';
                        medidorStatus.textContent = '⚠️ Seleccione un lote';
                        medidorStatus.style.color = '#ffc107';
                    }
                }
            }

            // ============================================
            // EVENTO DEL CHECKBOX (CON DEBUG)
            // ============================================
            mostrarSoloSinMedicionCheckbox.addEventListener('change', function() {
                log(`🔄 Checkbox cambiado: ${this.checked ? '✓ Marcado' : '✗ Desmarcado'}`, 'info');
                filtrarOpciones();
            });

            // ============================================
            // INICIALIZACIÓN
            // ============================================
            log('🔧 Inicializando aplicación...');
            
            // Verificar opciones del select
            const totalOptions = finalSelect.options.length;
            log(`📋 Select tiene ${totalOptions} opciones totales`);
            
            // Mostrar las primeras 5 opciones para debug
            const sampleOptions = [];
            for (let i = 0; i < Math.min(5, totalOptions); i++) {
                const opt = finalSelect.options[i];
                sampleOptions.push({
                    value: opt.value,
                    text: opt.text,
                    tieneMedicion: opt.dataset.tieneMedicion
                });
            }
            log('📋 Muestra de opciones:', 'info', sampleOptions);

            // Aplicar filtro inicial
            filtrarOpciones();
            
            // Verificar el estado inicial del medidor
            log('📊 Estado inicial del medidor:', {
                value: medidorInput.value,
                status: medidorStatus.textContent
            });

            log('✅ Aplicación inicializada correctamente');
            log('💡 Ahora selecciona un lote para ver el debug en acción');

            // ============================================
            // EL RESTO DEL CÓDIGO (CÁMARA, FOTO, GUARDADO)
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
                        // Fallback sin la restricción exacta
                        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                            .then(s => {
                                log('✅ Cámara iniciada en modo fallback', 'success');
                                stream = s;
                                video.srcObject = stream;
                                video.play();
                                cameraStarted();
                            })
                            .catch(error => {
                                log('❌ No se pudo acceder a la cámara en ningún modo:', 'error', error);
                                displayError(error);
                            });
                    });
            }

            // Función para detener la cámara
            function stopCamera() {
                log('📷 Deteniendo cámara', 'info');
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                cameraStopped();
            }

            // Función que se llama cuando la cámara inicia correctamente
            function cameraStarted() {
                log('📷 Cámara activa y reproduciendo', 'success');
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

            // Función que se llama cuando se detiene la cámara
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

                // Generar el nombre de la foto
                let codLote = document.getElementById('selectorLotes').value;
                let fechaToma = document.getElementById('fecha_medicion').value;
                let fechaFormateada = fechaToma.replace(/-/g, '');
                currentPhotoName = `talar2_${codLote}_${fechaFormateada}.png`;
                
                log(`📸 Foto capturada: ${currentPhotoName}`, 'success', { lote: codLote, fecha: fechaToma });

                document.getElementById('foto').value = currentPhotoData;
                afterTakePhoto();
            });

            // Función para descargar la foto
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

            // Función que se llama antes de capturar la foto
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

            // Función que se llama después de capturar la foto
            function afterTakePhoto() {
                log('📸 Captura completada, mostrando vista previa', 'success');
                video.pause();
                $('#canvas').removeClass('d-none');
                $('#take-photo').addClass('d-none');
                $('#exit-app').removeClass('d-none');
                $('#download-photo').removeClass('d-none');
                $('#resume-camera').removeClass('d-none');
                $('#cameraControls').removeClass('d-none');
            }

            // Salir de la cámara
            document.getElementById('exit-app').addEventListener('click', function() {
                log('🚪 Saliendo de la cámara', 'info');
                stopCamera();
                removeCapture();
            });

            // Función para resetear la vista de captura
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

            // Reanudar cámara
            document.getElementById('resume-camera').addEventListener('click', function() {
                log('▶️ Reanudando cámara', 'info');
                $('#canvas').addClass('d-none');
                $('#take-photo').removeClass('d-none');
                $('#exit-app').addClass('d-none');
                $('#download-photo').addClass('d-none');
                $('#resume-camera').addClass('d-none');
                video.play();
            });

            // Función para mostrar errores
            function displayError(err = '') {
                log('❌ Mostrando error:', 'error', err);
                if (err !== '') {
                    $("#errorMsg").html(err);
                }
                $("#errorMsg").removeClass("d-none");
            }

            // Guardar medición
            document.getElementById('btnGuardarMedicion').addEventListener('click', function() {
                log('💾 Intentando guardar medición', 'info');
                const form = document.getElementById('medicionProvisoriaForm');
                const formData = new FormData(form);

                // Log de los datos del formulario
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
                    });
            });

            log('🎯 Aplicación lista. Selecciona un lote y observa el panel de debug.');
        });
    </script>
@stop