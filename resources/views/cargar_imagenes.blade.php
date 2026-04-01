@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header">{{ __('Cargar Imágenes PNG') }}</div>

                <div class="card-body">
                    <!-- Contenedor para mensajes de estado -->
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Contenedor para resultados -->
                    <div id="resultsContainer">
                        @if (session('resultados'))
                            <div class="alert alert-info" role="alert">
                                <h5>Resultados de la subida:</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Imagen</th>
                                            <th>Estado</th>
                                            <th>Mensaje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(session('resultados') as $resultado)
                                            <tr>
                                                <td>{{ $resultado['nombre'] }}</td>
                                                <td>
                                                    @if($resultado['estado'] == 'éxito')
                                                        <span class="badge badge-success">Éxito</span>
                                                    @else
                                                        <span class="badge badge-danger">Error</span>
                                                    @endif
                                                </td>
                                                <td>{{ $resultado['mensaje'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>

                    <!-- Formulario de subida -->
                    <div class="container">
                        <form id="uploadForm" method="POST" action="{{ route('guardar_imagenes') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex justify-content-between mb-3">
                                <label for="imagenesInput" class="btn btn-primary">
                                    <i class="bi bi-cloud-arrow-up"></i> Elegir archivos
                                    <input type="file" name="imagenes[]" accept=".png" multiple id="imagenesInput" style="display: none;">
                                </label>
                                <button type="button" id="cargarImagenesButton" class="btn btn-success santi" disabled>
                                    <i class="bi bi-archive"></i> Subir Fotos
                                </button>
                            </div>
                        </form>

                        <hr>

                        <!-- Vista previa de imágenes seleccionadas -->
                        <div id="previewContainer">
                            <h5>Imágenes Seleccionadas:</h5>
                            <div id="imagePreview" class="image-preview"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de progreso -->
<div id="progressModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Cargando Imágenes...</h3>
        <div class="progress">
            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <p id="progressText">Preparando la carga...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const progressModal = document.getElementById('progressModal');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const cargarImagenesButton = document.getElementById('cargarImagenesButton');
    const inputImagenes = document.getElementById('imagenesInput');
    const uploadForm = document.getElementById('uploadForm');
    const resultsContainer = document.getElementById('resultsContainer');
    const imagePreview = document.getElementById('imagePreview');

    // Habilitar/deshabilitar botón según selección de archivos
    inputImagenes.addEventListener('change', function() {
        cargarImagenesButton.disabled = this.files.length === 0;
        updatePreview();
    });

    // Actualizar vista previa de imágenes
    function updatePreview() {
        imagePreview.innerHTML = '';

        if (inputImagenes.files.length === 0) {
            imagePreview.innerHTML = '<p>No hay imágenes seleccionadas</p>';
            return;
        }

        for (let i = 0; i < inputImagenes.files.length; i++) {
            const file = inputImagenes.files[i];
            const fileElement = document.createElement('div');
            fileElement.className = 'preview-item';

            // Mostrar icono según tipo de archivo
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fileElement.innerHTML = `
                        <div class="preview-thumbnail">
                            <img src="${e.target.result}" alt="${file.name}">
                        </div>
                        <div class="preview-info">
                            <p>${file.name}</p>
                            <p>${(file.size / 1024).toFixed(2)} KB</p>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                fileElement.innerHTML = `
                    <div class="preview-thumbnail">
                        <i class="fas fa-file"></i>
                    </div>
                    <div class="preview-info">
                        <p>${file.name}</p>
                        <p>${(file.size / 1024).toFixed(2)} KB</p>
                    </div>
                `;
            }

            imagePreview.appendChild(fileElement);
        }
    }

    // Manejar el envío del formulario
    cargarImagenesButton.addEventListener('click', function() {
        const formData = new FormData(uploadForm);

        // Mostrar modal de progreso
        progressModal.style.display = 'block';
        progressBar.style.width = '0%';
        progressText.textContent = 'Iniciando carga...';

        // Configurar la solicitud fetch
        const requestOptions = {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        // Simular progreso inicial
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress > 90) {
                progress = 90;
                clearInterval(progressInterval);
            }
            progressBar.style.width = `${progress}%`;
            progressText.textContent = `Cargando... ${Math.round(progress)}%`;
        }, 300);

        // Enviar la solicitud
        fetch(uploadForm.action, requestOptions)
            .then(response => {
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error(`Error del servidor: ${text.substring(0, 100)}...`);
                    });
                }
                return response.json();
            })
            .then(data => {
                // Completar el progreso
                progressBar.style.width = '100%';
                progressText.textContent = 'Carga completada!';

                // Ocultar modal después de un breve retraso
                setTimeout(() => {
                    progressModal.style.display = 'none';

                    // Mostrar resultados
                    if (data.resultados) {
                        let resultsHTML = `
                            <div class="alert alert-info">
                                <h5>Resultados de la subida:</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Imagen</th>
                                                <th class="text-center">Estado</th>
                                                <th>Mensaje</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;

                        data.resultados.forEach(result => {
                            const icon = result.estado === 'éxito'
                                ? '<i class="fas fa-check-circle text-success"></i>'
                                : '<i class="fas fa-times-circle text-danger"></i>';

                            resultsHTML += `
                                <tr>
                                    <td>${result.nombre}</td>
                                    <td class="text-center">${icon}</td>
                                    <td>${result.mensaje}</td>
                                </tr>
                            `;
                        });

                        resultsHTML += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `;

                        resultsContainer.innerHTML = resultsHTML;
                    } else if (data.error) {
                        resultsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> Error:</h5>
                                <p>${data.error}</p>
                            </div>
                        `;
                    }
                }, 500);
            })
            .catch(error => {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                progressBar.classList.add('bg-danger');
                progressText.textContent = 'Error en la carga';

                console.error('Error:', error);

                setTimeout(() => {
                    progressModal.style.display = 'none';
                    resultsContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Error en la subida:</h5>
                            <p>${error.message || 'Ocurrió un error desconocido al subir las imágenes'}</p>
                        </div>
                    `;
                }, 1000);
            });
    });
});
</script>

<style>
/* Estilos para la vista previa de imágenes */
.image-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.preview-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 120px;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    background-color: #f8f9fa;
}

.preview-thumbnail {
    width: 80px;
    height: 80px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 5px;
    overflow: hidden;
}

.preview-thumbnail img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.preview-thumbnail i {
    font-size: 40px;
    color: #6c757d;
}

.preview-info {
    text-align: center;
    font-size: 12px;
}

.preview-info p {
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Estilos para el modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
}

.modal-content {
    background-color: #fff;
    padding: 25px;
    border-radius: 5px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.progress {
    height: 20px;
    margin: 20px 0;
    border-radius: 10px;
    overflow: hidden;
    background-color: #e9ecef;
}

.progress-bar {
    background-color: #28a745;
    height: 100%;
    transition: width 0.3s ease;
}

/* Estilos para el botón */
.santi {
    height: 45px;
}

/* Estilos para la tabla de resultados */
.table-responsive {
    overflow-x: auto;
}

.table {
    margin-bottom: 0;
}

.table th {
    white-space: nowrap;
}

@media (max-width: 768px) {
    .preview-item {
        width: 100px;
    }

    .preview-thumbnail {
        width: 60px;
        height: 60px;
    }

    .preview-info p {
        font-size: 10px;
    }
}
</style>
@endsection
