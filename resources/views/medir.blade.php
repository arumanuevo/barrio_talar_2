@extends('adminlte::page')

@section('css')
    <!--<link rel="stylesheet" href="/css/admin_custom.css">-->
    <!--<link href="{{ asset('css/app.css') }}" rel="stylesheet">-->
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel='stylesheet' type='text/css' media='screen' href="{{ asset('../js/assets/style/webcam-demo.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href="{{ asset('css/css-loader.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet"></link>
    <style>
        .container {
            max-height: calc(100vh - 100px); /* Ajustar según el diseño */
            overflow-y: auto;
        }

        #cameraControls {
            display: flex;
            justify-content: center;
            gap: 20px; /* separa horizontalmente cada botón */
        }

        .control-wrapper {
            text-align: center;
            display: inline-block;
        }

        .control-label {
            display: block;
            background: rgba(0, 0, 0, 0.5); /* fondo semitransparente */
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            margin-bottom: 4px;
            font-size: 0.9em;
        }
    </style>

@stop

@section('content')

<input type="hidden" id="bearerToken" value="{{ $token }}">

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
                    <div class="loader loader-default " id="spin" data-text="Consultando..."></div>
                   
<!------------------------------TOAST--------------------------------------------------------->

                    <div class="toast-container" style="position: absolute; bottom: 0; right: 0;">
                        <div class="toast  " id = "myToast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                        <i class="bi bi-info-square"></i> &nbsp;
                            <strong class="mr-auto" id="tituloToast"> Transacción exitosa!</strong>
                            <!--<small class="text-muted">11 mins ago</small>-->
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
<!-----------------------------------------FIN DEL TOAST------------------------------------>
                    
                    <div id='divCartelAlerta'></div>
                  
                        
            <form name="ajaxform"  id="ajaxform">

                <div class="form-group">
                    <label>Seleccione Nº de Lote</label>
                    <select x-model="selectedLote"  class="selectpicker form-control" id="selectorLotes" data-live-search="true" placeholder="Ingrese Numero Lote" required="" >
                        <option value="N/A" selected= true>N/A</option>
                        @foreach($lotes as $lote => $idLote)
                            @if($idLote->lote != '0')
                                <option value="{{ $idLote->lote }}">{{ $idLote->lote }}</option>
                            @endif
                        @endforeach
                        
                    </select>
                </div>


                <div class="form-group">
                    <label>Código de Medidor</label>
                    <input type="text" disabled name="codMedidor" id="codMedidor" class="form-control" placeholder="N/A" required="">
                </div>
                <div class="form-group">
                    <label>Periodo Entre Tomas</label>
                    <input type="number"  min="1" name="periodo" id="periodo" class="form-control" placeholder="30" value="30" required="">
                </div>
                <div class="form-group">
                    <label>Fecha Anterior de Toma</label>
                    <input type="date" disabled name="tomaAnt" id="tomaAnt" required="" class="form-control" placeholder="N/A" >
                </div>
                <div class="form-group">
                    <label>Toma Anterior</label>
                    <input type="number" min="0" step="0.01" disabled name="tomaAnterior" id="tomaAnterior" class="form-control" placeholder="N/A" required="">
                </div>
                <div class="form-group">
                    <label>Fecha Actual de Toma</label>
                    <input type="date"  class="form-control" id="fechaToma"  name="trip-start" required="" >
        
                </div>
                <div class="form-group">
                    <label>Vencimiento</label>
                    <input type="date" disabled class="form-control" id="vencimiento" name="trip-start" required="" >
        
                </div>
            
                <div class="form-group">
                    <label>Valor Medido</label>
                    <input type="number" step="0.01" min="0" name="valorMedido" id="valorMedido" class="form-control" placeholder="N/A" required="">
                </div>
            
                <div class="form-group">
                    <label>Inspector</label>
                    <input type="text" disabled name="inspector" id="inspector" class="form-control" value = "{{ Auth::user()->name }}" placeholder="{{ Auth::user()->name }}" required="">
               
                </div>

            <div class="form-group">
                <!--<div class="row">
                    <div class="col">
                        <label>Foto</label>
                            <input type="text"  name="foto" id="foto"  class="form-control" value = "N/A" placeholder="N/A" required="">
                           
                        <br>
                            <div class="form-check">
                                    
                                <input type="checkbox" class="form-check-input" id="sinFoto">
                                <label class="form-check-label" for="sinFoto">No incluir foto</label>
                            </div>
                         
                            <button type="button" id="btnSubirFoto" class="btn btn-primary"><i class="bi bi-cloud-arrow-up"></i> Subir Foto</button>
                    </div>
                    
                </div>-->
                <div class="row">
                    <div class="col">
                        <label>Foto</label>
                        <div class="input-group">
                            <input type="text" name="foto" id="foto" class="form-control" value="N/A" placeholder="N/A" required="">
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
                    <button type="button" id= "btnActivarCamara" disabled class="btn btn-primary d-flex justify-content-center col-md-12"><i style="height:0px;padding:0px 10px 10px 10px;margin-top:-4px;" class="bi bi-camera"></i>Activar Camara</button>                                 
                </div>
               
                 <!--------------------------------------------CAMARA--------------------------------------------->

                 <main id="webcam-app">
                        <div class="form-control webcam-start " id="webcam-control" style="display: none;">
                                <label class="form-switch">
                                <input type="checkbox" id="webcam-switch" disabled>
                                <i id="iconoCamara"></i> 
                                <span id="webcam-caption">Activar Cámara</span>
                                </label>      
                                                
                        </div>
                        
                        <div id="errorMsg" class="col-12 col-md-6 alert-danger d-none">
                            Fallo al inicializar la camara, habilite el permiso para capturar fotos. <br/>
                            <button id="closeError" class="btn btn-primary ml-3">OK</button>
                        </div>
                        <div class="md-modal md-effect-12">
                            <div id="app-panel" class="app-panel md-content row p-0 m-0">     
                                <div id="webcam-container" class="webcam-container col-12 d-none p-0 m-0">
                                    <video id="webcam" autoplay playsinline width="640" height="480"></video>
                                    <canvas id="canvas" class="d-none"></canvas>
                                    <div class="flash"></div>
                                    <audio id="snapSound" src="{{ asset('../js/assets/audio/snap.wav') }}" preload = "auto"></audio>
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
                    <!--------------------------------------------FIN CAMARA----------------------------------------->
            </div>
     
            <hr class="style7">

            <div class="form-group">
               <!-- <button class="btn btn-success save-data">Save</button>-->
                <div class="d-flex justify-content-center">
                    <button  id="btnGuardarMedicion" class="btn btn-success save-data" disabled><i class="bi bi-cloud-arrow-up"></i>Guardar Medición</button>                  
                </div>
            </div>
            
        </form>
    </div>
                  
            </div>
        </div>
    </div>

    
</div>
<!-- Modal personalizado -->
<div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="confirmModalLabel" >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Aviso Importante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Transaccion exitosa! El almacenamiento se realizó.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    
                </div>
            </div>
        </div>
</div>


@endsection
@vite([ 'resources/js/app.js'])
@section('js')
    @parent


    <!--<script src="{{ asset('js/app.js') }}" ></script>-->

    <!--<script src="{{ asset('js/medidor.js') }}" defer></script>-->
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!--<script type="text/javascript" src="https://unpkg.com/webcam-easy/dist/webcam-easy.min.js"></script>-->
    <!--<script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>-->
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.js"></script>
    <script type="text/javascript"> 
        //window.objMedidor.display();
       // window.objCamara.display();
       // Función para ordenamiento natural
        function naturalSort(a, b) {
            // Extraer números de los strings
            const aNum = parseInt(a.text.replace(/[^\d]/g, ''));
            const bNum = parseInt(b.text.replace(/[^\d]/g, ''));

            return aNum - bNum;
        }

        // Inicializar SlimSelect con ordenamiento personalizado
        new SlimSelect({
            select: '#selectorLotes',
            sort: naturalSort, // Usar nuestra función de ordenamiento personalizada
            placeholder: 'Ingrese Número Lote',
            allowDeselect: true
        });
        $(document).ready(function() {
           
            let fechaMedicion = $( "#fechaToma" ).val();
            console.log('-------',fechaMedicion);
            window.objMedidor();
           // window.objCamara(); 
            //displayCalculos(); // Esto debería funcionar si app.js se carga correctamente
            $(document).on('change', '#periodo', function() {
                let periodo = $(this).val(); // Obtiene el valor del input
                let fechaMedicion = $( "#fechaToma" ).val();
                let fechaResultante = calcularFechaSumandoPeriodo(periodo, fechaMedicion);
                console.log('El nuevo valor del periodo es:', fechaResultante);
                $( "#vencimiento" ).val(fechaResultante);
                // Realiza cualquier acción adicional aquí
            });

           // Función para subir la foto al servidor
            function subirFotoAlServidor() {
                const fotoInput = document.getElementById('foto');
                const nombreFoto = fotoInput.value;
                console.log(nombreFoto);
                // Verificar si hay una foto para subir
                if (nombreFoto === 'N/A') {
                    console.log('no hay foto para subir');
                   // mostrarToast('Error', 'No hay foto para subir', 'error');
                    return;
                }
                
                // Obtener la imagen del canvas
                const canvas = document.getElementById('canvas');
                const imageData = canvas.toDataURL('image/png');
                
                // Mostrar indicador de carga
                const uploadStatus = document.getElementById('uploadStatus');
                uploadStatus.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Subiendo...</span></div> Subiendo foto...';
                
                // Crear FormData y agregar la imagen
                const formData = new FormData();
                formData.append('foto', dataURLtoFile(imageData, nombreFoto));
                formData.append('_token', csrfToken);
                
                // Enviar la foto al servidor
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
                       // mostrarToast('Éxito', 'Foto subida correctamente al servidor', 'success');
                    } else {
                        uploadStatus.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Error: ${data.mensaje}</span>`;
                        console.log('error')
                       // mostrarToast('Error', `Error al subir foto: ${data.mensaje}`, 'error');
                    }
                })
                .catch(error => {
                    uploadStatus.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Error de conexión</span>';
                    //mostrarToast('Error', 'Error de conexión al intentar subir la foto', 'error');
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

            // --- MÓDULO DE CÁMARA CON GETUSERMEDIA ---

            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');
            let stream; // Variable global para almacenar el stream

            // Función para iniciar la cámara (intentando la cámara trasera)
            function startCamera() {
                console.log('comienso de camara');
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

            // Activar cámara al hacer click en el botón
            $(document).on('click', '#btnActivarCamara', function() {
                $('.md-modal').addClass('md-show');
                startCamera();
            });

            // Tomar foto
            $(document).on('click', '#take-photo', function() {
                beforeTakePhoto();
                // Configurar dimensiones del canvas según el video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                let ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                let picture = canvas.toDataURL('image/png');
                document.querySelector('#download-photo').href = picture;
                afterTakePhoto();
                $('#btnSubirFoto').prop('disabled', false);
            });


            $(document).on('click', '#exit-app', function() {
                stopCamera();
                removeCapture();
                $('#webcam-switch').prop("checked", false).trigger('change');
            });

            $(document).on('click', '#resume-camera', function() {
                // Ocultar el canvas de la foto para volver a la vista de la cámara
                $('#canvas').addClass('d-none');
                // Volver a mostrar el botón de tomar foto
                $('#take-photo').removeClass('d-none');
                // Ocultar controles de descarga y reanudar
                $('#exit-app').addClass('d-none');
                $('#download-photo').addClass('d-none');
                $('#resume-camera').addClass('d-none');
                // Reanudar la reproducción del video
                video.play();
            });


            // Función para mostrar errores
            function displayError(err = '') {
                if (err !== '') {
                    $("#errorMsg").html(err);
                }
                $("#errorMsg").removeClass("d-none");
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
                // En lugar de detener la cámara, pausamos el video
                video.pause();
                // Mostramos el canvas con la imagen capturada
                $('#canvas').removeClass('d-none');
                // Ocultamos el botón de tomar foto y mostramos los controles de salida, descarga y reanudar
                $('#take-photo').addClass('d-none');
                $('#exit-app').removeClass('d-none');
                $('#download-photo').removeClass('d-none');
                $('#resume-camera').removeClass('d-none');
                $('#cameraControls').removeClass('d-none');
                setNombreFoto();
                $("#fechaToma").attr("disabled", true);
                let nombreFoto = $("#foto").val();
                $('#download-photo').attr("download", nombreFoto);
            }


            // Función para resetear la vista de captura
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

                if (month.length < 2)
                    month = '0' + month;
                if (day.length < 2)
                    day = '0' + day;

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
                //let seccion = $("#selectorSecciones option:selected").val();
                //seccion = seccion.replace(/\s/g, '_').replace(/\./g, '_');
                let fechaToma = $("#fechaToma").val();
                let diaCorregido = formatDate2(fechaToma, 'es');
                $("#foto").val('talar1_'+codLote + '_' + diaCorregido + '.png');
            }
           
        });

        
    </script>
    <script>     
 
     
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
  
        /*$(document).ready(function() {
            
            toastr.info('Se pueden editar y borrar usuarios, solo puede asignarse un rol a la vez.')
            toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-bottom-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
                }

            //listaUsuariosDisplay();  
            
        });*/
    </script>
    
    
    
    
    
@stop