@extends('adminlte::page')

@section('css')
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel='stylesheet' type='text/css' media='screen' href="{{ asset('../js/assets/style/webcam-demo.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href="{{ asset('css/css-loader.css') }}" rel="stylesheet">
    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet">
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

        .material-icons {
            font-size: 40px;
            color: #fff;
            text-shadow: 0 0 2px rgba(0,0,0,0.7);
        }

        .control-wrapper i.material-icons {
            font-size: 40px;
        }

        #take-photo i.material-icons {
            font-size: 40px;
            margin-top: 5px;
        }

        /* Estilo para el loader */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader-content {
            text-align: center;
            color: white;
        }
    </style>
@stop

@section('content')
<input type="hidden" id="bearerToken" value="{{ $token }}">

<!-- Loader overlay -->
<div id="loaderOverlay" class="loader-overlay">
    <div class="loader-content">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Guardando...</span>
        </div>
        <p class="mt-2">Guardando medición, por favor espere...</p>
    </div>
</div>

<div class="container" x-data="{ selectedLote: '' }">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header">{{ __('Tomar Medición') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="loader loader-default" id="spin" data-text="Consultando..."></div>

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
                            <select x-model="selectedLote" class="selectpicker form-control" id="selectorLotes" data-live-search="true" placeholder="Ingrese Numero Lote" required>
                                <option value="N/A" selected>N/A</option>
                                @foreach($lotes as $lote)
                                    @if($lote->lote != '0')
                                        <option value="{{ $lote->lote }}">{{ $lote->lote }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Código de Medidor</label>
                            <input type="text" disabled name="codMedidor" id="codMedidor" class="form-control" placeholder="N/A" required>
                        </div>

                        <div class="form-group">
                            <label>Periodo Entre Tomas</label>
                            <input type="number" min="1" name="periodo" id="periodo" class="form-control" placeholder="30" value="30" required>
                        </div>

                        <div class="form-group">
                            <label>Fecha Anterior de Toma</label>
                            <input type="date" disabled name="tomaAnt" id="tomaAnt" class="form-control" placeholder="N/A">
                        </div>

                        <div class="form-group">
                            <label>Toma Anterior</label>
                            <input type="number" min="0" step="0.01" disabled name="tomaAnterior" id="tomaAnterior" class="form-control" placeholder="N/A">
                        </div>

                        <div class="form-group">
                            <label>Fecha Actual de Toma</label>
                            <input type="date" class="form-control" id="fechaToma" name="trip-start" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Vencimiento</label>
                            <input type="date" disabled class="form-control" id="vencimiento" name="trip-start" required>
                        </div>

                        <div class="form-group">
                            <label>Valor Medido</label>
                            <input type="number" step="0.01" min="0" name="valorMedido" id="valorMedido" class="form-control" placeholder="N/A" required>
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
                                        <input type="text" name="foto" id="foto" class="form-control" value="N/A" placeholder="N/A" readonly>
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
                        </div>
                        <br>
                        <div class="form-group">
                            <button type="button" id="btnActivarCamara" disabled class="btn btn-primary d-flex justify-content-center col-md-12">
                                <i style="height:0px;padding:0px 10px 10px 10px;margin-top:-4px;" class="bi bi-camera"></i>Activar Cámara
                            </button>
                        </div>

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
                                        <a href="#" id="download-photo" download="selfie.png" title="Guardar Foto" class="control-wrapper d-none">
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

<div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="confirmModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Aviso Importante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Transacción exitosa! La medición se guardó correctamente.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
@vite(['resources/js/app.js'])
<script src="//unpkg.com/alpinejs" defer></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://unpkg.com/slim-select@latest/dist/slimselect.js"></script>

<script>
    // Función para ordenamiento natural
    function naturalSort(a, b) {
        const aNum = parseInt(a.text.replace(/[^\d]/g, ''));
        const bNum = parseInt(b.text.replace(/[^\d]/g, ''));
        return aNum - bNum;
    }

    // Función para mostrar/ocultar el loader
    function consultando(mostrar) {
        if (mostrar) {
            $('#loaderOverlay').show();
        } else {
            $('#loaderOverlay').hide();
        }
    }

    // Inicializar SlimSelect con ordenamiento personalizado
    new SlimSelect({
        select: '#selectorLotes',
        sort: naturalSort,
        placeholder: 'Ingrese Número Lote',
        allowDeselect: true
    });

    $(document).ready(function() {
        // Función para activar/desactivar el botón de cámara según el lote seleccionado
        function actualizarEstadoBotonCamara() {
            const loteSeleccionado = $('#selectorLotes').val();
            const btnActivarCamara = $('#btnActivarCamara');

            if (loteSeleccionado && loteSeleccionado !== 'N/A') {
                btnActivarCamara.prop('disabled', false);
            } else {
                btnActivarCamara.prop('disabled', true);
            }
        }

        // Función para verificar si se pueden activar los botones de guardar
        function actualizarEstadoBotonGuardar() {
            const loteSeleccionado = $('#selectorLotes').val();
            const valorMedido = $('#valorMedido').val();
            const btnGuardar = $('#btnGuardarMedicion');

            // Activar el botón solo si hay lote seleccionado y valor medido ingresado
            if (loteSeleccionado && loteSeleccionado !== 'N/A' && valorMedido && valorMedido > 0) {
                btnGuardar.prop('disabled', false);
            } else {
                btnGuardar.prop('disabled', true);
            }
        }

        // Función para cargar el medidor cuando se selecciona un lote
        $('#selectorLotes').on('change', function() {
            const lote = $(this).val();

            // Actualizar estado del botón de cámara
            actualizarEstadoBotonCamara();

            // Actualizar estado del botón guardar
            actualizarEstadoBotonGuardar();

            if (lote && lote !== 'N/A') {
                axios.get(`{{ url('/obtener-medidor') }}/${lote}`)
                    .then(response => {
                        const medidor = response.data.medidor;
                        $('#codMedidor').val(medidor);
                        console.log('Medidor cargado:', medidor);
                    })
                    .catch(error => {
                        console.error('Error al obtener el medidor:', error);
                    });
            } else {
                $('#codMedidor').val('');
            }
        });

        // Escuchar cambios en el valor medido para actualizar el botón guardar
        $('#valorMedido').on('input', actualizarEstadoBotonGuardar);

        // Función para manejar el checkbox de "No incluir foto"
        $('#sinFoto').on('change', function() {
            if ($(this).is(':checked')) {
                $('#foto').val('Sin Foto');
            } else {
                // Si no está marcado y no hay foto tomada, volver a N/A
                if ($('#foto').val() === 'Sin Foto' || $('#foto').val() === 'N/A') {
                    $('#foto').val('N/A');
                }
            }
        });

        // Función para calcular fecha sumando período
        function calcularFechaSumandoPeriodo(periodo, fecha) {
            if (!fecha || !periodo) return;

            const fechaObj = new Date(fecha);
            fechaObj.setDate(fechaObj.getDate() + parseInt(periodo));

            const year = fechaObj.getFullYear();
            const month = String(fechaObj.getMonth() + 1).padStart(2, '0');
            const day = String(fechaObj.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        // Escuchar cambios en el período o fecha de toma para calcular vencimiento
        $('#periodo, #fechaToma').on('change', function() {
            let periodo = $('#periodo').val();
            let fechaToma = $('#fechaToma').val();

            if (periodo && fechaToma) {
                let fechaVencimiento = calcularFechaSumandoPeriodo(periodo, fechaToma);
                $('#vencimiento').val(fechaVencimiento);
            }
        });

        // Calcular vencimiento inicial
        let periodoInicial = $('#periodo').val();
        let fechaTomaInicial = $('#fechaToma').val();
        if (periodoInicial && fechaTomaInicial) {
            let fechaVencimiento = calcularFechaSumandoPeriodo(periodoInicial, fechaTomaInicial);
            $('#vencimiento').val(fechaVencimiento);
        }

        // Función para subir la foto al servidor
        function subirFotoAlServidor() {
            const fotoInput = document.getElementById('foto');
            const nombreFoto = fotoInput.value;

            if (nombreFoto === 'N/A' || nombreFoto === 'Sin Foto') {
                console.log('no hay foto para subir o se seleccionó no incluir foto');
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
                if (data.estado === 'éxito') {
                    uploadStatus.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Foto subida correctamente</span>';
                } else {
                    uploadStatus.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Error: ${data.mensaje}</span>`;
                }
            })
            .catch(error => {
                uploadStatus.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Error de conexión</span>';
                console.error('Error:', error);
            });
        }

        // Función para convertir DataURL a File
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

        $(document).on('click', '#btnSubirFoto', subirFotoAlServidor);

        // Función para preparar los datos del formulario
        function formMedida() {
            let valorMedido = parseFloat($('#valorMedido').val());
            let tomaAnterior = parseFloat($('#tomaAnterior').val());

            if (valorMedido < tomaAnterior) {
                alert('El Valor Medido debe ser mayor que la Toma Anterior.');
                return 'ERROR';
            }

            return {
                lote: $('#selectorLotes').val(),
                medidor: $('#codMedidor').val(),
                periodo: $('#periodo').val(),
                fechaAnt: $('#tomaAnt').val(),
                tomaAnterior: tomaAnterior,
                vencimiento: $('#vencimiento').val(),
                fechaMedicion: $('#fechaToma').val(),
                valorMedido: valorMedido,
                inspector: $('#inspector').val(),
                foto: $('#foto').val()
            };
        }

        // Evento para guardar la medición (usando tu código original)
        $(document).on('click', '#btnGuardarMedicion', function(event) {
            let valorMedido = parseFloat($('#valorMedido').val());
            let tomaAnterior = parseFloat($('#tomaAnterior').val());

            if (valorMedido < tomaAnterior) {
                alert('El Valor Medido debe ser mayor que la Toma Anterior.');
                return false;
            };

            // Obtener datos de configuración (simulando tu variable data)
            let data = {
                default: [{
                    dnsApiAuth: window.location.origin + '/api/',
                    endPoints: {
                        postMed: 'postMed'
                    },
                    access_token: $('#bearerToken').val()
                }]
            };

            let dns = data.default[0].dnsApiAuth;
            let endPoint = data.default[0].endPoints.postMed;
            let urlConsulta = dns + endPoint;
            let token = data.default[0].access_token;

            let resultado = formMedida();

            if (resultado == 'ERROR') {
                consultando(false);
                return;
            }

            let jsonResultado = JSON.stringify(resultado);

            consultando(true);
            console.log('Datos a enviar:', resultado);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Authorization': 'Bearer ' + token
                },
                type: "POST",
                xhrFields: {
                    withCredentials: false
                },
                url: urlConsulta,
                data: jsonResultado,
                success: function(datos) {
                    console.log('Respuesta del servidor:', datos);
                    consultando(false);

                    // Mostrar modal de éxito
                    $('#modalExito').modal('show');

                    // Recargar la página después de 2 segundos
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                },
                error: function(xhr, status, error) {
                    consultando(false);
                    console.error('Error al guardar:', error);
                    alert('Error al guardar la medición. Por favor, intente nuevamente.');
                },
                jsonp: true,
                jsonpCallback: 'getJson'
            });
        });

        // --- MÓDULO DE CÁMARA CON GETUSERMEDIA ---
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        let stream;

        // Función para iniciar la cámara
        async function startCamera() {
            try {
                console.log('Solicitando permiso para acceder a la cámara...');

                // Mostrar mensaje de solicitud de permisos
                $("#errorMsg").removeClass("d-none");
                $("#errorMsg").html('Por favor, permita el acceso a la cámara cuando se le solicite. <br/><button id="closeError" class="btn btn-primary ml-3">OK</button>');

                const constraints = {
                    video: {
                        facingMode: { exact: "environment" },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };

                try {
                    // Intentar con la cámara trasera primero
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                } catch (err) {
                    console.warn("No se pudo acceder a la cámara trasera, intentando con cualquier cámara:", err);

                    // Fallback a cualquier cámara si la trasera no está disponible
                    const fallbackConstraints = {
                        video: {
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        },
                        audio: false
                    };
                    stream = await navigator.mediaDevices.getUserMedia(fallbackConstraints);
                }

                video.srcObject = stream;
                video.play();
                cameraStarted();

                // Ocultar mensaje de solicitud de permisos
                $("#errorMsg").addClass("d-none");

            } catch (error) {
                console.error("Error al acceder a la cámara:", error);
                displayError(error);

                // Mensaje más claro para el usuario
                if (error.name === 'NotAllowedError') {
                    $("#errorMsg").html('Permiso denegado para acceder a la cámara. Por favor, habilite el permiso en la configuración de su navegador y recargue la página. <br/><button id="closeError" class="btn btn-primary ml-3">OK</button>');
                } else if (error.name === 'NotFoundError') {
                    $("#errorMsg").html('No se encontró ninguna cámara. Por favor, conecte un dispositivo de cámara. <br/><button id="closeError" class="btn btn-primary ml-3">OK</button>');
                } else {
                    $("#errorMsg").html('Error al acceder a la cámara: ' + error.message + ' <br/><button id="closeError" class="btn btn-primary ml-3">OK</button>');
                }
                $("#errorMsg").removeClass("d-none");
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            cameraStopped();
        }

        $(document).on('click', '#btnActivarCamara', async function() {
            $('.md-modal').addClass('md-show');
            await startCamera();
            $('#btnActivarCamara').prop('disabled', true);
        });

        $(document).on('click', '#take-photo', function() {
            beforeTakePhoto();
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            let ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            let picture = canvas.toDataURL('image/png');
            document.querySelector('#download-photo').href = picture;
            afterTakePhoto();
            $('#btnSubirFoto').prop('disabled', false);
        });

        $(document).on('click', '#download-photo', function(e) {
            e.preventDefault();
            let codLote = $("#selectorLotes option:selected").val();
            let fechaToma = $("#fechaToma").val();
            let diaCorregido = formatDate2(fechaToma, 'es');
            let nombreFoto = `talar2_${codLote}_${diaCorregido}.png`;

            const canvas = document.getElementById('canvas');
            const imageData = canvas.toDataURL('image/png');

            const link = document.createElement('a');
            link.href = imageData;
            link.download = nombreFoto;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        $(document).on('click', '#exit-app', function() {
            stopCamera();
            removeCapture();
            $('#webcam-switch').prop("checked", false).trigger('change');
            $('#btnActivarCamara').prop('disabled', false);
            actualizarEstadoBotonCamara();
        });

        $(document).on('click', '#resume-camera', function() {
            $('#canvas').addClass('d-none');
            $('#take-photo').removeClass('d-none');
            $('#exit-app').addClass('d-none');
            $('#download-photo').addClass('d-none');
            $('#resume-camera').addClass('d-none');
            video.play();
        });

        function displayError(err = '') {
            if (err !== '') {
                let errorMessage = 'Error al acceder a la cámara: ';
                if (err.name === 'NotAllowedError') {
                    errorMessage += 'Permiso denegado. Por favor, habilite el acceso a la cámara en la configuración de su navegador.';
                } else if (err.name === 'NotFoundError') {
                    errorMessage += 'No se encontró ningún dispositivo de cámara.';
                } else {
                    errorMessage += err.message;
                }
                $("#errorMsg").html(errorMessage + '<br/><button id="closeError" class="btn btn-primary ml-3">OK</button>');
            }
            $("#errorMsg").removeClass("d-none");
        }

        function cameraStarted() {
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
            $("#errorMsg").addClass("d-none");
            $("#wpfront-scroll-top-container").removeClass("d-none");
            $("#webcam-control").removeClass("webcam-on");
            $("#webcam-control").addClass("webcam-off");
            $(".webcam-container").addClass("d-none");
            $("#webcam-caption").html("Click to Start Camera");
            $('.md-modal').removeClass('md-show');
            $("#webcam-control").addClass("d-none");
        }

        function beforeTakePhoto() {
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
            video.pause();
            $('#canvas').removeClass('d-none');
            $('#take-photo').addClass('d-none');
            $('#exit-app').removeClass('d-none');
            $('#download-photo').removeClass('d-none');
            $('#resume-camera').removeClass('d-none');
            $('#cameraControls').removeClass('d-none');
            setNombreFoto();
            $("#fechaToma").attr("disabled", true);
            actualizarEstadoBotonGuardar();
        }

        function removeCapture() {
            $('#canvas').addClass('d-none');
            $('#webcam-control').removeClass('d-none');
            $('#cameraControls').removeClass('d-none');
            $('#take-photo').removeClass('d-none');
            $('#exit-app').addClass('d-none');
            $('#download-photo').addClass('d-none');
            $('#resume-camera').addClass('d-none');
        }

        function formatDate2(date, zona) {
            let dia = new Date(date);
            let diaCorregido = new Date(dia.getTime() - dia.getTimezoneOffset() * -60000);
            var d = new Date(diaCorregido),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            switch (zona) {
                case 'es':
                    return [day, month, year].join('-');
                case 'en':
                    return [year, month, day].join('-');
                default:
                    return [year, month, day].join('-');
            }
        }

        function setNombreFoto() {
            let codLote = $("#selectorLotes option:selected").val();
            let fechaToma = $("#fechaToma").val();
            let diaCorregido = formatDate2(fechaToma, 'es');
            $("#foto").val('talar2_' + codLote + '_' + diaCorregido + '.png');
        }

        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Botón para cerrar el mensaje de error
        $(document).on('click', '#closeError', function() {
            $("#errorMsg").addClass("d-none");
        });

        // Inicializar el estado de los botones al cargar la página
        actualizarEstadoBotonCamara();
        actualizarEstadoBotonGuardar();
    });
</script>
@stop