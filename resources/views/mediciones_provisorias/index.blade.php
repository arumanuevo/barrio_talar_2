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
    </style>
@stop

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card">
                    <div class="card-header">{{ __('Tomar Medición Provisoria') }}</div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="filter-container">
                            <div class="form-check filter-switch">
                                <input class="form-check-input filter-switch-input" type="checkbox" id="mostrarSoloSinMedicion" checked>
                                <label class="form-check-label filter-switch-label" for="mostrarSoloSinMedicion">
                                    Mostrar solo lotes sin medición
                                </label>
                            </div>
                        </div>

                        <form id="medicionProvisoriaForm" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label>Nº de Lote</label>
                                <select name="lote" id="selectorLotes" class="form-control" required>
                                    <option value="" selected disabled>Seleccione un lote</option>
                                    @foreach($lotes as $lote)
                                        @if($lote->lote != '0')
                                            <option value="{{ $lote->lote }}" data-tiene-medicion="{{ $lote->tiene_medicion ? 'true' : 'false' }}">{{ $lote->lote }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Código de Medidor</label>
                                <input type="text" name="medidor" id="medidor" class="form-control" readonly>
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
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectElement = document.getElementById('selectorLotes');
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');
            const mostrarSoloSinMedicionCheckbox = document.getElementById('mostrarSoloSinMedicion');
            let stream;
            let selectLotes;

            // Guardar las opciones originales
            const originalOptions = Array.from(selectElement.options).map(option => {
                return {
                    value: option.value,
                    text: option.text,
                    data: {
                        tieneMedicion: option.dataset.tieneMedicion
                    }
                };
            });

            // Función para inicializar SlimSelect con las opciones filtradas
            function initSlimSelect() {
                const options = originalOptions.filter(option => {
                    if (!mostrarSoloSinMedicionCheckbox.checked) {
                        return true;
                    }
                    return option.data.tieneMedicion !== 'true';
                });

                if (selectLotes) {
                    selectLotes.destroy();
                }

                selectLotes = new SlimSelect({
                    select: '#selectorLotes',
                    placeholder: 'Seleccione un lote',
                    allowDeselect: true,
                    data: options
                });
            }

            // Inicializar SlimSelect con el checkbox marcado por defecto
            initSlimSelect();

            // Escuchar cambios en el checkbox de filtrado
            mostrarSoloSinMedicionCheckbox.addEventListener('change', function() {
                initSlimSelect();
            });

            // Escuchar el evento change en el elemento select
            selectElement.addEventListener('change', function() {
                const selectedValue = this.value;
                if (selectedValue) {
                    axios.get(`{{ url('/obtener-medidor') }}/${selectedValue}`)
                        .then(response => {
                            const medidor = response.data.medidor;
                            document.getElementById('medidor').value = medidor;
                            console.log('Medidor cargado:', medidor);
                        })
                        .catch(error => {
                            console.error('Error al obtener el medidor:', error);
                        });
                } else {
                    document.getElementById('medidor').value = '';
                }
            });

            // Activar cámara al hacer click en el botón
            document.getElementById('btnActivarCamara').addEventListener('click', function() {
                $('.md-modal').addClass('md-show');
                startCamera();
            });

            // Iniciar cámara
            function startCamera() {
                console.log('Inicio de cámara');
                const constraints = { video: { facingMode: { exact: "environment" } }, audio: false };
                navigator.mediaDevices.getUserMedia(constraints)
                    .then(s => {
                        stream = s;
                        video.srcObject = stream;
                        video.play();
                        cameraStarted();
                    })
                    .catch(err => {
                        console.error("Error al acceder a la cámara con constraint exacto:", err);
                        // Fallback sin la restricción exacta
                        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                            .then(s => {
                                stream = s;
                                video.srcObject = stream;
                                video.play();
                                cameraStarted();
                            })
                            .catch(error => {
                                console.error("No se pudo acceder a la cámara", error);
                                displayError(error);
                            });
                    });
            }

            // Función para detener la cámara
            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
                cameraStopped();
            }

            // Función que se llama cuando la cámara inicia correctamente
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

            // Función que se llama cuando se detiene la cámara
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

            // Tomar foto
            document.getElementById('take-photo').addEventListener('click', function() {
                beforeTakePhoto();
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                let ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                let picture = canvas.toDataURL('image/png');

                // Generar el nombre de la foto
                let codLote = document.getElementById('selectorLotes').value;
                let fechaToma = document.getElementById('fecha_medicion').value;
                let fechaFormateada = fechaToma.replace(/-/g, '');
                let nombreFoto = `talar2_${codLote}_${fechaFormateada}.png`;

                document.getElementById('foto').value = picture;
                afterTakePhoto();
            });

            // Función que se llama antes de capturar la foto (animación, ocultar controles, etc.)
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

            // Función que se llama después de capturar la foto
            function afterTakePhoto() {
                video.pause();
                $('#canvas').removeClass('d-none');
                $('#take-photo').addClass('d-none');
                $('#exit-app').removeClass('d-none');
                $('#resume-camera').removeClass('d-none');
                $('#cameraControls').removeClass('d-none');
            }

            // Salir de la cámara
            document.getElementById('exit-app').addEventListener('click', function() {
                stopCamera();
                removeCapture();
            });

            // Función para resetear la vista de captura
            function removeCapture() {
                $('#canvas').addClass('d-none');
                $('#webcam-control').removeClass('d-none');
                $('#cameraControls').removeClass('d-none');
                $('#take-photo').removeClass('d-none');
                $('#exit-app').addClass('d-none');
                $('#resume-camera').addClass('d-none');
            }

            // Reanudar cámara
            document.getElementById('resume-camera').addEventListener('click', function() {
                $('#canvas').addClass('d-none');
                $('#take-photo').removeClass('d-none');
                $('#exit-app').addClass('d-none');
                $('#resume-camera').addClass('d-none');
                video.play();
            });

            // Función para mostrar errores
            function displayError(err = '') {
                if (err !== '') {
                    $("#errorMsg").html(err);
                }
                $("#errorMsg").removeClass("d-none");
            }

            // Guardar medición
            document.getElementById('btnGuardarMedicion').addEventListener('click', function() {
                const form = document.getElementById('medicionProvisoriaForm');
                const formData = new FormData(form);

                axios.post("{{ route('mediciones_provisorias.store') }}", formData)
                    .then(response => {
                        $('#modalExito').modal('show');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });
    </script>
@stop