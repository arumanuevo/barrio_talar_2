{{-- resources/views/import-mediciones.blade.php --}}
@extends('layouts.app')

@section('title', 'Importar Mediciones Masivas')

@section('content')
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .drop-zone {
        border: 2px dashed #6c757d;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #f8f9fa;
        position: relative;
        z-index: 10;
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
        max-height: 600px;
        overflow-y: auto;
    }
    .preview-table table {
        font-size: 0.8rem;
    }
    .preview-table .table-danger {
        background-color: #f8d7da !important;
    }
    .preview-table .table-success {
        background-color: #d1e7dd !important;
    }
    .preview-table thead th {
        position: sticky;
        top: 0;
        background: #343a40;
        color: white;
        z-index: 10;
        white-space: nowrap;
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
        font-size: 1.5rem;
        font-weight: bold;
    }
    .summary-card .label {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .mapping-label {
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        display: block;
    }
    .mapping-hint {
        font-size: 0.75rem;
        color: #6c757d;
        display: block;
        margin-top: 0.25rem;
    }
    .alert {
        padding: 0.75rem 1.25rem;
        border-radius: 0.25rem;
        margin-bottom: 1rem;
    }
    .alert-success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
    .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffecb5; }
    .alert-info { background: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; }
    .btn {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        font-size: 0.9rem;
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
    .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
    .btn:disabled { opacity: 0.65; cursor: not-allowed; }
    .form-select {
        display: block;
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 0.9rem;
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
        background: white;
    }
    .form-label {
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        display: block;
    }
    .text-muted { color: #6c757d; }
    .mt-1 { margin-top: 0.25rem; }
    .mt-2 { margin-top: 0.5rem; }
    .mt-3 { margin-top: 1rem; }
    .mt-4 { margin-top: 1.5rem; }
    .mb-0 { margin-bottom: 0; }
    .mb-1 { margin-bottom: 0.25rem; }
    .mb-2 { margin-bottom: 0.5rem; }
    .mb-3 { margin-bottom: 1rem; }
    .ms-2 { margin-left: 0.5rem; }
    .float-end { float: right; }
    .d-flex { display: flex; }
    .gap-2 { gap: 0.5rem; }
    .flex-wrap { flex-wrap: wrap; }
    .row { display: flex; flex-wrap: wrap; margin: -0.5rem; }
    .col-md-3 { flex: 0 0 25%; padding: 0.5rem; }
    .col-md-4 { flex: 0 0 33.333%; padding: 0.5rem; }
    .col-md-6 { flex: 0 0 50%; padding: 0.5rem; }
    .col-md-11 { flex: 0 0 91.666%; padding: 0.5rem; }
    .col-12 { flex: 0 0 100%; padding: 0.5rem; }
    .text-center { text-align: center; }
    .text-success { color: #198754; }
    .text-danger { color: #dc3545; }
    .text-primary { color: #0d6efd; }
    .badge { display: inline-block; padding: 0.2rem 0.4rem; font-size: 0.7rem; border-radius: 0.25rem; }
    .bg-success { background: #198754; color: white; }
    .bg-danger { background: #dc3545; color: white; }
    .bg-secondary { background: #6c757d; color: white; }
    .bg-warning { background: #ffc107; color: #212529; }
    .bg-primary { background: #0d6efd; color: white; }
    .progress { height: 1.5rem; background: #e9ecef; border-radius: 0.25rem; overflow: hidden; }
    .progress-bar { height: 100%; background: #0d6efd; color: white; text-align: center; line-height: 1.5rem; transition: width 0.3s; }
    .progress-bar-striped { background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent); }
    .progress-bar-animated { animation: progress-bar-stripes 1s linear infinite; }
    @keyframes progress-bar-stripes { 0% { background-position: 1rem 0; } 100% { background-position: 0 0; } }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 0.3rem 0.2rem; border: 1px solid #dee2e6; }
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
    .sample-notice {
        background: #fff3cd;
        border: 1px solid #ffecb5;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 15px;
        color: #856404;
        font-size: 0.9rem;
    }
    .sample-notice strong {
        color: #664d03;
    }
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-file-earmark-excel"></i> Importar Mediciones desde Excel</h4>
                    <a href="{{ route('getTodasMedVista') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
                <div class="card-body">
                    <div id="importAlertContainer"></div>

                    <!-- PASO 1: SUBIR ARCHIVO -->
                    <div id="importStep1" class="step">
                        <h5><i class="bi bi-upload"></i> Paso 1: Subir archivo Excel</h5>
                        <p class="text-muted">Selecciona el archivo <strong>.xlsx</strong> o <strong>.xls</strong> con las mediciones.</p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Formato esperado:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Columna con <strong>LOTE</strong> (número de lote)</li>
                                <li>Columna con <strong>MEDIDOR</strong> (código del medidor)</li>
                                <li>Columnas siguientes: valores de medición en orden cronológico</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Importante:</strong> Selecciona la hoja <strong>"MEDICION FEB - MARZO - 10 ABRIL"</strong>
                            <br>
                            <small>Esta hoja contiene los valores crudos de medición.</small>
                        </div>

                        <div class="drop-zone" id="importDropZone">
                            <i class="bi bi-file-earmark-excel"></i>
                            <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
                            <p class="text-muted small">(Solo archivos .xlsx, .xls o .csv)</p>
                            <input type="file" id="importFileInput" accept=".xlsx,.xls,.csv">
                        </div>

                        <div id="importFileInfo" class="file-info">
                            <i class="bi bi-file-earmark-excel text-success"></i>
                            <span id="importFileName"></span>
                            <span class="badge bg-secondary ms-2" id="importFileSize"></span>
                            <button class="btn btn-danger btn-sm float-end" id="importRemoveFile">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>

                        <div id="importSheetSelection" class="mt-3" style="display:none;">
                            <label for="importSheetSelect" class="form-label">Selecciona la hoja del archivo:</label>
                            <select id="importSheetSelect" class="form-select"></select>
                            <button class="btn btn-primary mt-2" id="importAnalyzeBtn">
                                <i class="bi bi-search"></i> Analizar archivo
                            </button>
                        </div>
                    </div>

                    <!-- PASO 2: MAPEO DE COLUMNAS -->
                    <div id="importStep2" class="step" style="display:none;">
                        <hr>
                        <h5><i class="bi bi-arrow-left-right"></i> Paso 2: Verificar columnas</h5>
                        <p class="text-muted">Confirma que las columnas se hayan detectado correctamente.</p>

                        <div id="importMappingContainer" class="row g-3"></div>

                        <div class="mt-3">
                            <button class="btn btn-secondary" id="importBackStep1">
                                <i class="bi bi-arrow-left"></i> Volver
                            </button>
                            <button class="btn btn-primary" id="importPreviewBtn">
                                <i class="bi bi-eye"></i> Previsualizar datos
                            </button>
                        </div>
                    </div>

                    <!-- PASO 3: PREVISUALIZACIÓN -->
                    <div id="importStep3" class="step" style="display:none;">
                        <hr>
                        <h5><i class="bi bi-eye"></i> Paso 3: Previsualización</h5>
                        <p class="text-muted">Revisa cómo quedarán los registros en la tabla del sistema.</p>

                        <div id="previewLoading" class="preview-loading">
                            <div class="spinner"></div>
                            <div class="progress-text">
                                <strong>Procesando datos...</strong>
                                <br>
                                <span id="previewProgressText">Preparando archivo...</span>
                            </div>
                        </div>

                        <div id="previewSummary" class="summary-card" style="display:none;"></div>

                        <div class="sample-notice" id="sampleNotice" style="display:none;">
                            <i class="bi bi-info-circle"></i>
                            <strong>Mostrando muestra de 10 filas</strong> — 
                            El archivo contiene <span id="totalRowsCount">0</span> filas. 
                            La importación procesará todas las filas.
                        </div>

                        <div class="table-responsive preview-table" id="previewTableContainer" style="display:none;">
                            <table class="table table-bordered table-striped" id="importPreviewTable">
                                <thead id="importPreviewHead"></thead>
                                <tbody id="importPreviewBody"></tbody>
                            </table>
                        </div>

                        <div id="importErrors" class="alert alert-danger mt-3" style="display:none;"></div>

                        <div class="mt-3" id="previewActions" style="display:none;">
                            <button class="btn btn-secondary" id="importBackStep2">
                                <i class="bi bi-arrow-left"></i> Volver
                            </button>
                            <button class="btn btn-success" id="importConfirmBtn">
                                <i class="bi bi-check-circle"></i> Confirmar e Importar (TODAS las filas)
                            </button>
                        </div>
                    </div>

                    <!-- PROGRESO DE IMPORTACIÓN -->
                    <div id="importProgressContainer" style="display:none;" class="mt-3">
                        <div class="progress">
                            <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <p id="importProgressText" class="mt-2 text-muted">Procesando...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de éxito -->
<div class="modal" id="importSuccessModal" tabindex="-1" style="display:none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Importación completada</h5>
                <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('importSuccessModal').style.display='none'">&times;</button>
            </div>
            <div class="modal-body" id="importSuccessModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="importReloadPage">Ir a Mediciones</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- SheetJS CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js">
</script>

<script>
(function() {
    'use strict';

    // ============================================
    // ✅ FUNCIÓN PARA LIMPIAR LOTE (ELIMINAR CEROS A LA IZQUIERDA)
    // ============================================
    function cleanLote(lote) {
        if (!lote) return '';
        
        // Eliminar espacios
        lote = lote.trim();
        
        // Si es un número (incluyendo con ceros a la izquierda), convertir a entero y luego a string
        if (!isNaN(lote) && lote.length > 0) {
            // Convertir a número entero (elimina ceros a la izquierda automáticamente)
            var num = parseInt(lote, 10);
            // Convertir de vuelta a string
            lote = num.toString();
        }
        
        return lote;
    }

    // ============================================
    // PREVENIR DRAG & DROP A NIVEL GLOBAL
    // ============================================
    document.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });
    document.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    // ============================================
    // VARIABLES
    // ============================================
    let importCurrentFile = null;
    let importWorkbookData = null;
    let importSheetNames = [];
    let importCurrentSheetIndex = 0;
    let importHeaders = [];
    let importRows = [];
    let importPreviewData = [];
    let importDataToSend = [];

    // Fechas fijas para la hoja "MEDICION FEB - MARZO - 10 ABRIL"
    const FECHAS_MEDICION = [
        '2025-11-12',
        '2025-12-12',
        '2026-01-12',
        '2026-02-12',
        '2026-03-12',
        '2026-04-12',
        '2026-05-12'
    ];

    // ============================================
    // ELEMENTOS DOM
    // ============================================
    const importDropZone = document.getElementById('importDropZone');
    const importFileInput = document.getElementById('importFileInput');
    const importFileInfo = document.getElementById('importFileInfo');
    const importFileName = document.getElementById('importFileName');
    const importFileSize = document.getElementById('importFileSize');
    const importSheetSelection = document.getElementById('importSheetSelection');
    const importSheetSelect = document.getElementById('importSheetSelect');
    const importAlertContainer = document.getElementById('importAlertContainer');

    const previewLoading = document.getElementById('previewLoading');
    const previewProgressText = document.getElementById('previewProgressText');
    const previewSummary = document.getElementById('previewSummary');
    const previewTableContainer = document.getElementById('previewTableContainer');
    const previewActions = document.getElementById('previewActions');
    const sampleNotice = document.getElementById('sampleNotice');
    const totalRowsCount = document.getElementById('totalRowsCount');

    // ============================================
    // FUNCIONES DE PROGRESO
    // ============================================
    function showPreviewLoading(text) {
        previewLoading.classList.add('show');
        previewSummary.style.display = 'none';
        previewTableContainer.style.display = 'none';
        previewActions.style.display = 'none';
        sampleNotice.style.display = 'none';
        if (text) previewProgressText.textContent = text;
    }

    function hidePreviewLoading() {
        previewLoading.classList.remove('show');
        previewSummary.style.display = 'block';
        previewTableContainer.style.display = 'block';
        previewActions.style.display = 'block';
    }

    // ============================================
    // EVENTOS DE DROP ZONE
    // ============================================
    importDropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('dragover');
    });

    importDropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
    });

    importDropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            importFileInput.files = files;
            handleImportFileSelect();
        }
    });

    importDropZone.addEventListener('click', function() {
        importFileInput.click();
    });

    importFileInput.addEventListener('change', handleImportFileSelect);

    // ============================================
    // MANEJAR SELECCIÓN DE ARCHIVO
    // ============================================
    function handleImportFileSelect() {
        const file = importFileInput.files[0];
        if (!file) return;

        importCurrentFile = file;
        importFileName.textContent = file.name;
        importFileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
        importFileInfo.classList.add('visible');

        showImportAlert('Archivo seleccionado: ' + file.name, 'success');

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                importWorkbookData = XLSX.read(data, { type: 'array', cellDates: true });

                importSheetNames = importWorkbookData.SheetNames;
                if (importSheetNames.length === 0) {
                    showImportAlert('El archivo no contiene hojas.', 'danger');
                    return;
                }

                importSheetSelect.innerHTML = '';
                importSheetNames.forEach((name, idx) => {
                    const opt = document.createElement('option');
                    opt.value = idx;
                    opt.textContent = name;
                    importSheetSelect.appendChild(opt);
                });
                importSheetSelection.style.display = 'block';

                // Seleccionar hoja "MEDICION FEB - MARZO - 10 ABRIL" por defecto
                let defaultSheet = 0;
                importSheetNames.forEach((name, idx) => {
                    if (name.includes('MEDICION FEB - MARZO')) {
                        defaultSheet = idx;
                    }
                });
                importSheetSelect.value = defaultSheet;

                showImportAlert('Archivo cargado. ' + importSheetNames.length + ' hoja(s) encontrada(s).', 'info');
                analyzeImportSheet();

            } catch (error) {
                showImportAlert('Error al leer el archivo: ' + error.message, 'danger');
                console.error(error);
            }
        };

        reader.onerror = function() {
            showImportAlert('Error al leer el archivo.', 'danger');
        };

        reader.readAsArrayBuffer(file);
    }

    // ============================================
    // ANALIZAR HOJA
    // ============================================
    function analyzeImportSheet() {
        if (!importWorkbookData) return;

        importCurrentSheetIndex = parseInt(importSheetSelect.value);
        const sheetName = importSheetNames[importCurrentSheetIndex];
        const worksheet = importWorkbookData.Sheets[sheetName];

        const rawData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });

        if (rawData.length < 2) {
            showImportAlert('La hoja no contiene suficientes datos.', 'danger');
            return;
        }

        importHeaders = rawData[0].map(h => String(h || '').trim());
        importRows = rawData.slice(1).filter(row => row.some(cell => cell !== '' && cell !== null && cell !== undefined));

        detectImportColumns();

        document.getElementById('importStep1').style.display = 'none';
        document.getElementById('importStep2').style.display = 'block';
        document.getElementById('importStep3').style.display = 'none';

        showImportAlert('Hoja "' + sheetName + '" analizada. ' + importRows.length + ' filas de datos.', 'success');
    }

    // ============================================
    // DETECTAR COLUMNAS
    // ============================================
    function detectImportColumns() {
        const container = document.getElementById('importMappingContainer');
        container.innerHTML = '';

        let loteCol = -1,
            medidorCol = -1,
            nombreCol = -1;
        let fechaCols = [];

        importHeaders.forEach((header, idx) => {
            const h = header.toLowerCase();
            if (h.includes('lote') || h === 'lote') {
                loteCol = idx;
            } else if (h.includes('medidor')) {
                medidorCol = idx;
            } else if (h.includes('nombre') || h.includes('propietario') || h.includes('titular')) {
                nombreCol = idx;
            } else {
                // Las columnas restantes son las de medición
                const sample = importRows.slice(0, 5).map(row => parseFloat(row[idx])).filter(v => !isNaN(v) && v > 0);
                if (sample.length > 0) {
                    fechaCols.push(idx);
                }
            }
        });

        if (loteCol === -1 && importHeaders.length > 1) {
            for (let col = 0; col < Math.min(importHeaders.length, 5); col++) {
                const sample = importRows.slice(0, 5).map(row => row[col]).filter(v => v !== '');
                if (sample.length > 0 && sample.every(v => !isNaN(v) && v !== '')) {
                    loteCol = col;
                    break;
                }
            }
        }

        if (medidorCol === -1) {
            for (let col = 0; col < Math.min(importHeaders.length, 5); col++) {
                const sample = importRows.slice(0, 5).map(row => row[col]).filter(v => v !== '');
                if (sample.length > 0 && sample.every(v => typeof v === 'string' && v.length > 3)) {
                    medidorCol = col;
                    break;
                }
            }
        }

        // Mostrar columnas detectadas
        const fields = [
            { key: 'lote', label: 'Lote *', detected: loteCol },
            { key: 'medidor', label: 'Medidor *', detected: medidorCol },
            { key: 'nombre', label: 'Nombre (opcional)', detected: nombreCol }
        ];

        fields.forEach(field => {
            const div = document.createElement('div');
            div.className = 'col-md-4 mb-3';
            div.innerHTML = `<label class="mapping-label">${field.label}</label>`;
            const select = document.createElement('select');
            select.className = 'form-select import-mapping-select';
            select.dataset.key = field.key;
            select.innerHTML = `<option value="">-- No usar --</option>`;
            importHeaders.forEach((h, idx) => {
                const selected = (idx === field.detected) ? 'selected' : '';
                const display = h || 'Columna ' + (idx + 1);
                select.innerHTML += `<option value="${idx}" ${selected}>${display}</option>`;
            });
            div.appendChild(select);
            container.appendChild(div);
        });

        // Columnas de medición
        const fechaDiv = document.createElement('div');
        fechaDiv.className = 'col-12 mt-3';
        fechaDiv.innerHTML = `<hr><h6>Columnas de medición (valores en orden cronológico)</h6>
            <p class="text-muted small">Estas columnas contienen los valores de medición. El orden debe ser: Nov 2025, Dic 2025, Ene 2026, Feb 2026, Mar 2026, Abr 2026, May 2026</p>`;
        container.appendChild(fechaDiv);

        if (fechaCols.length === 0) {
            const alert = document.createElement('div');
            alert.className = 'col-12 alert alert-warning';
            alert.textContent = 'No se detectaron columnas de medición.';
            container.appendChild(alert);
        } else {
            fechaCols.forEach((colIdx, index) => {
                const div = document.createElement('div');
                div.className = 'col-md-3 mb-2';
                const fechaLabel = FECHAS_MEDICION[index] || 'Fecha ' + (index + 1);
                const sampleVal = importRows.length > 0 ? importRows[0][colIdx] : 'N/A';
                div.innerHTML = `
                    <label class="mapping-label">${fechaLabel}</label>
                    <span class="mapping-hint">Ej: ${sampleVal}</span>
                    <select class="form-select import-mapping-select" data-fecha="${colIdx}">
                        <option value="${colIdx}" selected>${fechaLabel}</option>
                    </select>
                `;
                container.appendChild(div);
            });
        }
    }

    // ============================================
    // PREVISUALIZAR DATOS (SOLO 10 FILAS)
    // ============================================
    function previewImportData() {
        showPreviewLoading('Preparando muestra de datos...');

        setTimeout(function() {
            try {
                const mapping = {
                    lote: parseInt(document.querySelector('#importMappingContainer select[data-key="lote"]').value),
                    medidor: parseInt(document.querySelector('#importMappingContainer select[data-key="medidor"]').value),
                    nombre: parseInt(document.querySelector('#importMappingContainer select[data-key="nombre"]').value),
                    fechas: []
                };

                document.querySelectorAll('#importMappingContainer select[data-fecha]').forEach(function(sel) {
                    const val = sel.value;
                    if (val !== '') mapping.fechas.push(parseInt(val));
                });

                if (isNaN(mapping.lote) || mapping.lote < 0) {
                    showImportAlert('Debes seleccionar la columna de LOTE.', 'warning');
                    hidePreviewLoading();
                    return;
                }
                if (isNaN(mapping.medidor) || mapping.medidor < 0) {
                    showImportAlert('Debes seleccionar la columna de MEDIDOR.', 'warning');
                    hidePreviewLoading();
                    return;
                }
                if (mapping.fechas.length === 0) {
                    showImportAlert('Debes seleccionar al menos una columna de medición.', 'warning');
                    hidePreviewLoading();
                    return;
                }

                // Procesar SOLO las primeras 10 filas para el preview
                const MAX_PREVIEW_ROWS = 10;
                const rowsToProcess = importRows.slice(0, MAX_PREVIEW_ROWS);
                const totalRows = importRows.length;
                const previewData = [];
                const errors = [];

                showPreviewLoading(`Procesando muestra de ${Math.min(MAX_PREVIEW_ROWS, totalRows)} filas...`);

                rowsToProcess.forEach((row, rowIdx) => {
                    // ✅ APLICAR cleanLote() para eliminar ceros a la izquierda
                    const loteOriginal = String(row[mapping.lote] || '').trim();
                    const lote = cleanLote(loteOriginal);
                    const medidor = String(row[mapping.medidor] || '').trim();
                    const nombre = mapping.nombre >= 0 ? String(row[mapping.nombre] || '').trim() : '';

                    if (!lote) {
                        errors.push('Fila ' + (rowIdx + 2) + ': Lote vacío (original: "' + loteOriginal + '").');
                        return;
                    }

                    // Usar fechas fijas para cada columna de medición
                    const mediciones = [];
                    mapping.fechas.forEach((colIdx, index) => {
                        const valor = parseFloat(row[colIdx]);
                        if (!isNaN(valor) && valor >= 0) {
                            const fechaStr = FECHAS_MEDICION[index] || null;
                            mediciones.push({ 
                                fecha: fechaStr ? new Date(fechaStr) : null, 
                                valor: valor,
                                header: fechaStr || 'Fecha ' + (index + 1)
                            });
                        }
                    });

                    if (mediciones.length === 0) {
                        errors.push('Fila ' + (rowIdx + 2) + ': No se encontraron valores.');
                        return;
                    }

                    // Ordenar por fecha
                    mediciones.sort((a, b) => {
                        if (a.fecha && b.fecha) return a.fecha - b.fecha;
                        return 0;
                    });

                    let valorAnterior = 0;
                    let fechaAnterior = null;
                    let indice = 1;

                    mediciones.forEach((med, idx) => {
                        const esPrimera = (idx === 0);
                        const consumo = esPrimera ? 0 : med.valor - valorAnterior;
                        
                        const vencimiento = new Date(med.fecha);
                        vencimiento.setDate(vencimiento.getDate() + 30);

                        previewData.push({
                            lote: lote,
                            loteOriginal: loteOriginal,
                            medidor: medidor,
                            nombre: nombre,
                            indice: indice,
                            fecha: med.fecha,
                            vencimiento: vencimiento,
                            tomaant: fechaAnterior,
                            medidaant: esPrimera ? 0 : valorAnterior,
                            valormedido: med.valor,
                            consumo: consumo,
                            periodo: 30,
                            inspector: 'admin',
                            foto: 'Sin foto',
                            pagado: 'NO',
                            rowIndex: rowIdx + 2,
                            esPrimera: esPrimera,
                            valid: true
                        });

                        valorAnterior = med.valor;
                        fechaAnterior = med.fecha;
                        indice++;
                    });
                });

                // Guardar TODOS los datos para la importación
                importDataToSend = previewData;

                // Mostrar el preview con la muestra
                renderImportPreview(previewData, errors, totalRows);

            } catch (error) {
                console.error('Error en preview:', error);
                showImportAlert('Error al procesar los datos: ' + error.message, 'danger');
                hidePreviewLoading();
            }
        }, 100);
    }

    // ============================================
    // RENDERIZAR PREVIEW
    // ============================================
    function renderImportPreview(data, errors, totalRows) {
        const tbody = document.getElementById('importPreviewBody');
        const thead = document.getElementById('importPreviewHead');
        tbody.innerHTML = '';

        if (totalRows > 0) {
            totalRowsCount.textContent = totalRows;
            sampleNotice.style.display = 'block';
        }

        const headers = [
            '#', 'Lote', 'Medidor', 'Nombre', 'Índice', 'Fecha', 'Vencimiento',
            'Toma Ant.', 'Medida Ant.', 'Valor Medido', 'Consumo', 'Periodo',
            'Inspector', 'Estado'
        ];

        let headerHtml = '<tr>';
        headers.forEach(h => {
            headerHtml += `<th>${h}</th>`;
        });
        headerHtml += '</tr>';
        thead.innerHTML = headerHtml;

        let validCount = 0;
        let errorCount = 0;

        data.forEach((item, idx) => {
            const isValid = item.valid !== false;
            if (isValid) validCount++;
            else errorCount++;

            const fechaStr = item.fecha ? item.fecha.toISOString().split('T')[0] : 'N/A';
            const vencimientoStr = item.vencimiento ? item.vencimiento.toISOString().split('T')[0] : 'N/A';
            const tomaantStr = item.tomaant ? item.tomaant.toISOString().split('T')[0] : 'NULL';

            let rowHtml = `<tr class="${isValid ? '' : 'table-danger'}">`;
            rowHtml += `<td>${idx + 1}</td>`;
            rowHtml += `<td><strong>${item.lote}</strong></td>`;
            rowHtml += `<td>${item.medidor}</td>`;
            rowHtml += `<td>${item.nombre || '-'}</td>`;
            rowHtml += `<td>${item.indice}</td>`;
            rowHtml += `<td>${fechaStr}</td>`;
            rowHtml += `<td>${vencimientoStr}</td>`;
            rowHtml += `<td>${tomaantStr}</td>`;
            rowHtml += `<td>${item.medidaant}</td>`;
            rowHtml += `<td>${item.valormedido}</td>`;
            
            const consumoClass = item.consumo === 0 ? 'text-success' : 'text-primary';
            rowHtml += `<td class="${consumoClass} fw-bold">${item.consumo}</td>`;
            
            rowHtml += `<td>${item.periodo}</td>`;
            rowHtml += `<td>${item.inspector}</td>`;
            
            const statusBadge = isValid ?
                '<span class="badge bg-success">OK</span>' :
                '<span class="badge bg-danger">Error</span>';
            rowHtml += `<td>${statusBadge}</td></tr>`;

            tbody.innerHTML += rowHtml;
        });

        document.getElementById('previewSummary').innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="number">${data.length}</div>
                        <div class="label">Filas mostradas (muestra)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center text-success">
                        <div class="number">${validCount}</div>
                        <div class="label">Válidas</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center text-danger">
                        <div class="number">${errorCount}</div>
                        <div class="label">Con errores</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center text-primary">
                        <div class="number">${totalRows}</div>
                        <div class="label">Total filas a importar</div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-center text-muted small">
                    <span class="text-success">■ Consumo = 0</span>
                    <span class="text-primary ms-2">■ Consumo > 0</span>
                    <span class="ms-2">| Primera medición de cada lote tiene consumo = 0</span>
                </div>
            </div>
        `;

        const importErrors = document.getElementById('importErrors');
        if (errors.length > 0) {
            importErrors.style.display = 'block';
            importErrors.innerHTML = '<strong>Errores encontrados en la muestra:</strong><ul>' + errors.map(e => '<li>' + e + '</li>').join('') + '</ul>';
        } else {
            importErrors.style.display = 'none';
        }

        hidePreviewLoading();
        document.getElementById('importStep2').style.display = 'none';
        document.getElementById('importStep3').style.display = 'block';
    }

    // ============================================
    // IMPORTAR DATOS (TODAS LAS FILAS)
    // ============================================
    function importDataToSystem() {
        if (importDataToSend.length === 0) {
            showImportAlert('No hay datos para importar.', 'warning');
            return;
        }

        // Reconstruir todos los datos desde importRows
        const mapping = {
            lote: parseInt(document.querySelector('#importMappingContainer select[data-key="lote"]').value),
            medidor: parseInt(document.querySelector('#importMappingContainer select[data-key="medidor"]').value),
            nombre: parseInt(document.querySelector('#importMappingContainer select[data-key="nombre"]').value),
            fechas: []
        };

        document.querySelectorAll('#importMappingContainer select[data-fecha]').forEach(function(sel) {
            const val = sel.value;
            if (val !== '') mapping.fechas.push(parseInt(val));
        });

        const allData = [];
        const errors = [];

        importRows.forEach((row, rowIdx) => {
            // ✅ APLICAR cleanLote() para eliminar ceros a la izquierda
            const loteOriginal = String(row[mapping.lote] || '').trim();
            const lote = cleanLote(loteOriginal);
            const medidor = String(row[mapping.medidor] || '').trim();
            const nombre = mapping.nombre >= 0 ? String(row[mapping.nombre] || '').trim() : '';

            if (!lote) {
                errors.push('Fila ' + (rowIdx + 2) + ': Lote vacío (original: "' + loteOriginal + '").');
                return;
            }

            const mediciones = [];
            mapping.fechas.forEach((colIdx, index) => {
                const valor = parseFloat(row[colIdx]);
                if (!isNaN(valor) && valor >= 0) {
                    const fechaStr = FECHAS_MEDICION[index] || null;
                    mediciones.push({ 
                        fecha: fechaStr,
                        valor: valor
                    });
                }
            });

            if (mediciones.length === 0) {
                errors.push('Fila ' + (rowIdx + 2) + ': No se encontraron valores.');
                return;
            }

            // Ordenar por fecha
            mediciones.sort((a, b) => {
                if (a.fecha && b.fecha) return a.fecha.localeCompare(b.fecha);
                return 0;
            });

            let valorAnterior = 0;

            const medicionesData = mediciones.map((med, idx) => {
                const esPrimera = (idx === 0);
                const consumo = esPrimera ? 0 : med.valor - valorAnterior;
                const result = {
                    fecha: med.fecha,
                    valor: med.valor,
                    consumo: consumo
                };
                valorAnterior = med.valor;
                return result;
            });

            allData.push({
                lote: lote,
                medidor: medidor,
                nombre: nombre,
                mediciones: medicionesData
            });
        });

        if (allData.length === 0) {
            showImportAlert('No hay datos válidos para importar.', 'warning');
            return;
        }

        document.getElementById('importProgressContainer').style.display = 'block';
        document.getElementById('importConfirmBtn').disabled = true;
        document.getElementById('importProgressBar').style.width = '10%';
        document.getElementById('importProgressBar').textContent = '10%';
        document.getElementById('importProgressText').textContent = 'Preparando datos...';

        const payload = allData;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        fetch('/api/import-mediciones/import', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ data: payload })
        })
        .then(response => response.json())
        .then(response => {
            document.getElementById('importProgressBar').style.width = '100%';
            document.getElementById('importProgressBar').textContent = '100%';
            document.getElementById('importProgressText').textContent = 'Importación completada.';

            if (response.success) {
                document.getElementById('importSuccessModalBody').innerHTML = `
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
                document.getElementById('importSuccessModal').style.display = 'block';

                document.getElementById('importReloadPage').onclick = function() {
                    window.location.href = '{{ route("getTodasMedVista") }}';
                };

                showImportAlert(response.message, 'success');
            } else {
                showImportAlert(response.message || 'Error en la importación', 'danger');
            }
        })
        .catch(error => {
            const msg = error.message || 'Error de conexión';
            showImportAlert('Error en la importación: ' + msg, 'danger');
            console.error(error);
        })
        .finally(function() {
            document.getElementById('importConfirmBtn').disabled = false;
        });
    }

    // ============================================
    // EVENTOS
    // ============================================
    document.getElementById('importAnalyzeBtn').addEventListener('click', analyzeImportSheet);
    document.getElementById('importPreviewBtn').addEventListener('click', previewImportData);
    document.getElementById('importConfirmBtn').addEventListener('click', importDataToSystem);

    document.getElementById('importBackStep1').addEventListener('click', function() {
        document.getElementById('importStep2').style.display = 'none';
        document.getElementById('importStep1').style.display = 'block';
    });

    document.getElementById('importBackStep2').addEventListener('click', function() {
        document.getElementById('importStep3').style.display = 'none';
        document.getElementById('importStep2').style.display = 'block';
    });

    document.getElementById('importRemoveFile').addEventListener('click', function() {
        importCurrentFile = null;
        importWorkbookData = null;
        importFileInfo.classList.remove('visible');
        importFileInput.value = '';
        importSheetSelection.style.display = 'none';
        document.getElementById('importStep2').style.display = 'none';
        document.getElementById('importStep3').style.display = 'none';
        document.getElementById('importStep1').style.display = 'block';
        showImportAlert('Archivo removido.', 'info');
    });

    // ============================================
    // ALERTAS
    // ============================================
    function showImportAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()" aria-label="Close"></button>
            </div>
        `;
        importAlertContainer.innerHTML += alertHtml;
        setTimeout(() => {
            const alerts = importAlertContainer.querySelectorAll('.alert');
            if (alerts.length > 0) {
                alerts[alerts.length - 1].remove();
            }
        }, 8000);
    }

})();
</script>
@endsection