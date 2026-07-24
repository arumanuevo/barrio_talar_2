{{-- resources/views/import-mediciones-csv.blade.php --}}
@extends('layouts.app')

@section('title', 'Importar Mediciones desde CSV')

@section('content')
<style>
    .drop-zone {
        border: 2px dashed #6c757d;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #f8f9fa;
    }
    .drop-zone:hover {
        border-color: #0d6efd;
        background: #e7f1ff;
    }
    .drop-zone.dragover {
        border-color: #198754;
        background: #d1e7dd;
    }
    .drop-zone i {
        font-size: 4rem;
        color: #6c757d;
        display: block;
        margin-bottom: 15px;
    }
    #fileInput {
        display: none;
    }
    .preview-table {
        max-height: 500px;
        overflow-y: auto;
    }
    .preview-table table {
        font-size: 0.85rem;
    }
    .preview-table .table-danger {
        background-color: #f8d7da !important;
    }
    .preview-table .table-warning {
        background-color: #fff3cd !important;
    }
    .preview-table .table-success {
        background-color: #d1e7dd !important;
    }
    .step {
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .file-info {
        background: #e9ecef;
        padding: 10px 15px;
        border-radius: 6px;
        margin-top: 10px;
        display: none;
    }
    .file-info.visible {
        display: block;
    }
    .summary-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }
    .summary-card .number {
        font-size: 1.8rem;
        font-weight: bold;
    }
    .summary-card .label {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .alert {
        padding: 0.75rem 1.25rem;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
    .btn {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        border-radius: 0.25rem;
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s;
    }
    .btn-primary { background: #0d6efd; color: white; border-color: #0d6efd; }
    .btn-primary:hover { background: #0b5ed7; }
    .btn-secondary { background: #6c757d; color: white; border-color: #6c757d; }
    .btn-secondary:hover { background: #5a6268; }
    .btn-success { background: #198754; color: white; border-color: #198754; }
    .btn-success:hover { background: #157347; }
    .btn-danger { background: #dc3545; color: white; border-color: #dc3545; }
    .btn-danger:hover { background: #b02a37; }
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .btn:disabled { opacity: 0.65; cursor: not-allowed; }
    .badge { display: inline-block; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 0.25rem; }
    .bg-success { background: #198754; color: white; }
    .bg-danger { background: #dc3545; color: white; }
    .bg-warning { background: #ffc107; color: #212529; }
    .bg-secondary { background: #6c757d; color: white; }
    .progress { height: 1.5rem; background: #e9ecef; border-radius: 0.25rem; overflow: hidden; }
    .progress-bar { height: 100%; background: #0d6efd; color: white; text-align: center; line-height: 1.5rem; transition: width 0.3s; }
    .progress-bar-striped { background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent); }
    .progress-bar-animated { animation: progress-bar-stripes 1s linear infinite; }
    @keyframes progress-bar-stripes { 0% { background-position: 1rem 0; } 100% { background-position: 0 0; } }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 0.5rem; border: 1px solid #dee2e6; }
    .table-striped tbody tr:nth-of-type(odd) { background: #f8f9fa; }
    .table-bordered { border: 1px solid #dee2e6; }
    .table-responsive { overflow-x: auto; }
    .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
    .card { background: white; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .card-header { padding: 1rem; border-bottom: 1px solid #dee2e6; background: #0d6efd; color: white; border-radius: 0.5rem 0.5rem 0 0; }
    .card-body { padding: 1.25rem; }
    .card-header .btn-light { background: white; color: #212529; border-color: #f8f9fa; }
    .card-header .btn-light:hover { background: #e9ecef; }
    hr { margin: 1.5rem 0; border: 0; border-top: 1px solid #dee2e6; }
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1050; }
    .modal.show { display: block; }
    .modal-dialog { position: relative; max-width: 500px; margin: 1.75rem auto; }
    .modal-content { background: white; border-radius: 0.5rem; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
    .modal-header { padding: 1rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; }
    .modal-header .btn-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; padding: 0; }
    .modal-body { padding: 1rem; }
    .modal-footer { padding: 1rem; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 0.5rem; }
    .btn-close-white { color: white; }
    .fs-5 { font-size: 1.25rem; }
    .p-2 { padding: 0.5rem; }
    .preview-loading {
        display: none;
        text-align: center;
        padding: 40px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 20px 0;
        border: 1px solid #dee2e6;
    }
    .preview-loading.show {
        display: block;
    }
    .preview-loading .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #e9ecef;
        border-top-color: #0d6efd;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 15px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Importar Mediciones desde CSV</h4>
                    <a href="{{ route('getTodasMedVista') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>

                    <!-- PASO 1: SUBIR ARCHIVO -->
                    <div id="step1" class="step">
                        <h5><i class="bi bi-upload"></i> Paso 1: Subir archivo CSV</h5>
                        <p class="text-muted">Selecciona el archivo <strong>.csv</strong> con las mediciones.</p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Formato esperado del CSV:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Columnas: <strong>id,lote,medidor,consumo,foto,fecha_medicion,created_at,updated_at</strong></li>
                                <li>El <strong>consumo</strong> es el valor medido para esa fecha</li>
                                <li>El sistema validará que el lote y medidor existan</li>
                                <li>Las fotos deben ser rutas relativas (ej: images/talar2_1_20260612.png)</li>
                            </ul>
                        </div>

                        <div class="drop-zone" id="dropZone">
                            <i class="bi bi-file-earmark-text"></i>
                            <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
                            <p class="text-muted small">(Solo archivos .csv)</p>
                            <input type="file" id="fileInput" accept=".csv">
                        </div>

                        <div id="fileInfo" class="file-info">
                            <i class="bi bi-file-earmark-text text-success"></i>
                            <span id="fileName"></span>
                            <span class="badge bg-secondary ms-2" id="fileSize"></span>
                            <button class="btn btn-danger btn-sm float-end" id="btnRemoveFile">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                    <!-- PASO 2: PREVISUALIZACIÓN -->
                    <div id="step2" class="step" style="display:none;">
                        <hr>
                        <h5><i class="bi bi-eye"></i> Paso 2: Previsualización y validación</h5>
                        <p class="text-muted">Revisa el informe de la importación antes de confirmar.</p>

                        <div id="previewLoading" class="preview-loading">
                            <div class="spinner"></div>
                            <div class="progress-text">
                                <strong>Procesando datos...</strong>
                                <br>
                                <span id="previewProgressText">Analizando archivo...</span>
                            </div>
                        </div>

                        <div id="previewSummary" class="summary-card" style="display:none;"></div>

                        <div id="reportContainer" style="display:none;">
                            <!-- Errores -->
                            <div id="errorsContainer" style="display:none;" class="alert alert-danger">
                                <h6><i class="bi bi-x-circle"></i> Errores encontrados</h6>
                                <ul id="errorsList"></ul>
                            </div>

                            <!-- Advertencias -->
                            <div id="warningsContainer" style="display:none;" class="alert alert-warning">
                                <h6><i class="bi bi-exclamation-triangle"></i> Advertencias</h6>
                                <ul id="warningsList"></ul>
                            </div>

                            <!-- Usuarios faltantes -->
                            <div id="missingUsersContainer" style="display:none;" class="alert alert-info">
                                <h6><i class="bi bi-people"></i> Usuarios faltantes</h6>
                                <p>Los siguientes lotes no existen en el sistema. Se crearán automáticamente.</p>
                                <ul id="missingUsersList"></ul>
                            </div>

                            <!-- Datos válidos -->
                            <div id="validDataContainer" style="display:none;">
                                <h6><i class="bi bi-check-circle text-success"></i> Datos válidos a importar</h6>
                                <div class="table-responsive preview-table">
                                    <table class="table table-bordered table-striped" id="previewTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Lote</th>
                                                <th>Medidor</th>
                                                <th>Consumo</th>
                                                <th>Valor Final</th>
                                                <th>Fecha</th>
                                                <th>Foto</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3" id="actionButtons" style="display:none;">
                            <button class="btn btn-secondary" id="btnBackStep1">
                                <i class="bi bi-arrow-left"></i> Volver
                            </button>
                            <button class="btn btn-success" id="btnImport">
                                <i class="bi bi-check-circle"></i> Confirmar e Importar
                            </button>
                            <button class="btn btn-info" id="btnDownloadReport">
                                <i class="bi bi-download"></i> Descargar Informe
                            </button>
                        </div>
                    </div>

                    <!-- PROGRESO DE IMPORTACIÓN -->
                    <div id="progressContainer" style="display:none;" class="mt-3">
                        <div class="progress">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <p id="progressText" class="mt-2 text-muted">Procesando...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de éxito -->
<div class="modal" id="successModal" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Importación completada</h5>
                <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('successModal').style.display='none'">&times;</button>
            </div>
            <div class="modal-body" id="successModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnReloadPage">Ir a Mediciones</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Prevenir drag & drop a nivel global
    document.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });
    document.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    // Variables
    let currentFile = null;
    let reportData = null;
    let importData = [];

    // Elementos DOM
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const alertContainer = document.getElementById('alertContainer');

    const previewLoading = document.getElementById('previewLoading');
    const previewProgressText = document.getElementById('previewProgressText');
    const previewSummary = document.getElementById('previewSummary');
    const reportContainer = document.getElementById('reportContainer');
    const actionButtons = document.getElementById('actionButtons');

    const errorsContainer = document.getElementById('errorsContainer');
    const errorsList = document.getElementById('errorsList');
    const warningsContainer = document.getElementById('warningsContainer');
    const warningsList = document.getElementById('warningsList');
    const missingUsersContainer = document.getElementById('missingUsersContainer');
    const missingUsersList = document.getElementById('missingUsersList');
    const validDataContainer = document.getElementById('validDataContainer');
    const previewBody = document.getElementById('previewBody');

    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    // Eventos de drop zone
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect();
        }
    });

    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', handleFileSelect);

    // Botones
    document.getElementById('btnRemoveFile').addEventListener('click', function() {
        currentFile = null;
        fileInfo.classList.remove('visible');
        fileInput.value = '';
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step2').style.display = 'none';
        reportContainer.style.display = 'none';
        actionButtons.style.display = 'none';
        showAlert('Archivo removido.', 'info');
    });

    document.getElementById('btnBackStep1').addEventListener('click', function() {
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step2').style.display = 'none';
    });

    document.getElementById('btnImport').addEventListener('click', importDataToSystem);
    document.getElementById('btnDownloadReport').addEventListener('click', downloadReport);
    document.getElementById('btnReloadPage').addEventListener('click', function() {
        window.location.href = '{{ route("getTodasMedVista") }}';
    });

    function handleFileSelect() {
        const file = fileInput.files[0];
        if (!file) return;

        currentFile = file;
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
        fileInfo.classList.add('visible');

        showAlert('Archivo seleccionado: ' + file.name, 'success');
        analyzeFile(file);
    }

// En la función analyzeFile, agregar más logs en el frontend
function analyzeFile(file) {
    console.log('=== INICIO analyzeFile ===');
    console.log('Archivo:', file);

    previewLoading.classList.add('show');
    previewProgressText.textContent = 'Analizando archivo...';

    const formData = new FormData();
    formData.append('file', file);

    // ✅ Log de FormData
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }

    fetch('/api/import-mediciones-csv/preview', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: formData
    })
    .then(async response => {
        console.log('Response status:', response.status);
        const text = await response.text();
        console.log('Response text:', text);
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Error parsing JSON:', e);
            throw new Error('El servidor devolvió una respuesta inválida: ' + text.substring(0, 100));
        }
    })
    .then(response => {
        console.log('Response:', response);
        previewLoading.classList.remove('show');
        
        if (response.success) {
            reportData = response.data;
            renderReport(response.data);
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            showAlert('Análisis completado. Revisa el informe.', 'info');
        } else {
            showAlert(response.message || 'Error al analizar el archivo', 'danger');
        }
    })
    .catch(error => {
        console.error('Error en analyzeFile:', error);
        previewLoading.classList.remove('show');
        showAlert('Error al analizar el archivo: ' + error.message, 'danger');
    });
}

    function renderReport(data) {
        // Resumen
        previewSummary.style.display = 'block';
        previewSummary.innerHTML = `
            <div class="row">
                <div class="col-md-2">
                    <div class="text-center">
                        <div class="number">${data.summary.total_rows}</div>
                        <div class="label">Total filas</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center text-success">
                        <div class="number">${data.summary.valid_rows}</div>
                        <div class="label">Válidas</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center text-danger">
                        <div class="number">${data.summary.errors_count}</div>
                        <div class="label">Errores</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center text-warning">
                        <div class="number">${data.summary.warnings_count}</div>
                        <div class="label">Advertencias</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center text-info">
                        <div class="number">${data.summary.duplicates_count}</div>
                        <div class="label">Duplicados</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center text-primary">
                        <div class="number">${data.new_measurements}</div>
                        <div class="label">Nuevas mediciones</div>
                    </div>
                </div>
            </div>
        `;

        reportContainer.style.display = 'block';
        actionButtons.style.display = 'block';

        // Errores
        if (data.errors && data.errors.length > 0) {
            errorsContainer.style.display = 'block';
            errorsList.innerHTML = data.errors.map(e => `<li>${e}</li>`).join('');
        } else {
            errorsContainer.style.display = 'none';
        }

        // Advertencias
        if (data.warnings && data.warnings.length > 0) {
            warningsContainer.style.display = 'block';
            warningsList.innerHTML = data.warnings.map(w => `<li>${w}</li>`).join('');
        } else {
            warningsContainer.style.display = 'none';
        }

        // Usuarios faltantes
        if (data.missing_users && data.missing_users.length > 0) {
            missingUsersContainer.style.display = 'block';
            missingUsersList.innerHTML = data.missing_users.map(u => 
                `<li>Lote ${u.lote} (Fila ${u.row}) - Medidor: ${u.medidor}</li>`
            ).join('');
        } else {
            missingUsersContainer.style.display = 'none';
        }

        // Datos válidos
        if (data.valid_data && data.valid_data.length > 0) {
            validDataContainer.style.display = 'block';
            importData = data.valid_data;
            
            previewBody.innerHTML = data.valid_data.map((item, index) => {
                const statusBadge = item.new_value >= item.last_value ? 
                    '<span class="badge bg-success">OK</span>' :
                    '<span class="badge bg-warning">Revisar</span>';
                return `
                    <tr class="${item.new_value >= item.last_value ? '' : 'table-warning'}">
                        <td>${index + 1}</td>
                        <td><strong>${item.lote}</strong></td>
                        <td>${item.medidor}</td>
                        <td>${item.consumo}</td>
                        <td>${item.new_value}</td>
                        <td>${item.fecha}</td>
                        <td>${item.foto || 'Sin foto'}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            }).join('');
        } else {
            validDataContainer.style.display = 'none';
        }
    }

    function importDataToSystem() {
        if (importData.length === 0) {
            showAlert('No hay datos válidos para importar.', 'warning');
            return;
        }

        if (!confirm(`¿Estás seguro de que quieres importar ${importData.length} mediciones?`)) {
            return;
        }

        progressContainer.style.display = 'block';
        document.getElementById('btnImport').disabled = true;
        progressBar.style.width = '10%';
        progressBar.textContent = '10%';
        progressText.textContent = 'Enviando datos...';

        fetch('/api/import-mediciones-csv/import', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ data: importData })
        })
        .then(response => response.json())
        .then(response => {
            progressBar.style.width = '100%';
            progressBar.textContent = '100%';
            progressText.textContent = 'Importación completada.';

            if (response.success) {
                document.getElementById('successModalBody').innerHTML = `
                    <p><strong>${response.message}</strong></p>
                    <div class="row mt-3">
                        <div class="col-6 text-center">
                            <span class="badge bg-success fs-5 p-2">${response.success_count || 0}</span>
                            <p class="text-muted">Mediciones guardadas</p>
                        </div>
                        <div class="col-6 text-center">
                            <span class="badge bg-danger fs-5 p-2">${response.error_count || 0}</span>
                            <p class="text-muted">Errores</p>
                        </div>
                    </div>
                    ${response.errors && response.errors.length > 0 ? `<div class="mt-2"><small class="text-danger">${response.errors.join('<br>')}</small></div>` : ''}
                `;
                document.getElementById('successModal').style.display = 'block';
                showAlert(response.message, 'success');
            } else {
                showAlert(response.message || 'Error en la importación', 'danger');
            }
        })
        .catch(error => {
            showAlert('Error en la importación: ' + error.message, 'danger');
            console.error(error);
        })
        .finally(function() {
            document.getElementById('btnImport').disabled = false;
        });
    }

    function downloadReport() {
        if (!reportData) {
            showAlert('No hay informe disponible para descargar.', 'warning');
            return;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const formData = new FormData();
        formData.append('report_data', JSON.stringify(reportData));

        fetch('/api/import-mediciones-csv/download-report', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token
            },
            body: formData
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `informe_importacion_csv_${new Date().toISOString().slice(0,10)}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        })
        .catch(error => {
            showAlert('Error al descargar el informe: ' + error.message, 'danger');
            console.error(error);
        });
    }

    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()" aria-label="Close"></button>
            </div>
        `;
        alertContainer.innerHTML += alertHtml;
        setTimeout(() => {
            const alerts = alertContainer.querySelectorAll('.alert');
            if (alerts.length > 0) {
                alerts[alerts.length - 1].remove();
            }
        }, 8000);
    }
});
</script>
@endsection