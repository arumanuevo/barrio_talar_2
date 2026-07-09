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
        max-height: 500px;
        overflow-y: auto;
    }
    .preview-table table {
        font-size: 0.85rem;
    }
    .preview-table .table-danger {
        background-color: #f8d7da !important;
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
    .badge { display: inline-block; padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 0.25rem; }
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
                    <div id="alertContainer"></div>

                    <!-- PASO 1: SUBIR ARCHIVO -->
                    <div id="step1" class="step">
                        <h5><i class="bi bi-upload"></i> Paso 1: Subir archivo Excel</h5>
                        <p class="text-muted">Selecciona el archivo <strong>.xlsx</strong> o <strong>.xls</strong> con las mediciones.</p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Formato esperado:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Columna con <strong>LOTE</strong> (número de lote)</li>
                                <li>Columna con <strong>MEDIDOR</strong> (código del medidor)</li>
                                <li>Columnas siguientes: fechas con los valores de medición</li>
                            </ul>
                        </div>

                        <div class="drop-zone" id="dropZone">
                            <i class="bi bi-file-earmark-excel"></i>
                            <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
                            <p class="text-muted small">(Solo archivos .xlsx, .xls o .csv)</p>
                            <input type="file" id="fileInput" accept=".xlsx,.xls,.csv">
                        </div>

                        <div id="fileInfo" class="file-info">
                            <i class="bi bi-file-earmark-excel text-success"></i>
                            <span id="fileName"></span>
                            <span class="badge bg-secondary ms-2" id="fileSize"></span>
                            <button class="btn btn-danger btn-sm float-end" id="btnRemoveFile">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>

                        <div id="sheetSelection" class="mt-3" style="display:none;">
                            <label for="sheetSelect" class="form-label">Selecciona la hoja del archivo:</label>
                            <select id="sheetSelect" class="form-select"></select>
                            <button class="btn btn-primary mt-2" id="btnAnalyze">
                                <i class="bi bi-search"></i> Analizar archivo
                            </button>
                        </div>
                    </div>

                    <!-- PASO 2: MAPEO DE COLUMNAS -->
                    <div id="step2" class="step" style="display:none;">
                        <hr>
                        <h5><i class="bi bi-arrow-left-right"></i> Paso 2: Verificar columnas</h5>
                        <p class="text-muted">Confirma que las columnas se hayan detectado correctamente.</p>

                        <div id="mappingContainer" class="row g-3"></div>

                        <div class="mt-3">
                            <button class="btn btn-secondary" id="btnBackStep1">
                                <i class="bi bi-arrow-left"></i> Volver
                            </button>
                            <button class="btn btn-primary" id="btnPreview">
                                <i class="bi bi-eye"></i> Previsualizar datos
                            </button>
                        </div>
                    </div>

                    <!-- PASO 3: PREVISUALIZACIÓN -->
                    <div id="step3" class="step" style="display:none;">
                        <hr>
                        <h5><i class="bi bi-eye"></i> Paso 3: Previsualización y validación</h5>
                        <p class="text-muted">Revisa los datos antes de importar. Las filas con errores se marcarán en rojo.</p>

                        <div id="previewSummary" class="summary-card"></div>

                        <div class="table-responsive preview-table">
                            <table class="table table-bordered table-striped" id="previewTable">
                                <thead id="previewHead"></thead>
                                <tbody id="previewBody"></tbody>
                            </table>
                        </div>

                        <div id="importErrors" class="alert alert-danger mt-3" style="display:none;"></div>

                        <div class="mt-3">
                            <button class="btn btn-secondary" id="btnBackStep2">
                                <i class="bi bi-arrow-left"></i> Volver
                            </button>
                            <button class="btn btn-success" id="btnImport">
                                <i class="bi bi-check-circle"></i> Confirmar e Importar
                            </button>
                        </div>
                    </div>

                    <!-- PROGRESO -->
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
@endsection

@section('js')
<!-- SheetJS CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js">
</script>

<script>
// ✅ Usamos una función auto-ejecutable para evitar conflictos de variables
(function() {
    'use strict';

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
    // VARIABLES GLOBALES (dentro del closure)
    // ============================================
    let currentFile = null;
    let workbookData = null;
    let sheetNames = [];
    let currentSheetIndex = 0;
    let headers = [];
    let rows = [];
    let previewData = [];
    let importData = [];

    // ============================================
    // ELEMENTOS DOM
    // ============================================
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const sheetSelection = document.getElementById('sheetSelection');
    const sheetSelect = document.getElementById('sheetSelect');
    const alertContainer = document.getElementById('alertContainer');

    // ============================================
    // EVENTOS DE DROP ZONE
    // ============================================
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

    // ============================================
    // MANEJAR SELECCIÓN DE ARCHIVO
    // ============================================
    function handleFileSelect() {
        const file = fileInput.files[0];
        if (!file) return;

        currentFile = file;
        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
        fileInfo.classList.add('visible');

        showAlert('Archivo seleccionado: ' + file.name, 'success');

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                workbookData = XLSX.read(data, { type: 'array', cellDates: true });

                sheetNames = workbookData.SheetNames;
                if (sheetNames.length === 0) {
                    showAlert('El archivo no contiene hojas.', 'danger');
                    return;
                }

                sheetSelect.innerHTML = '';
                sheetNames.forEach((name, idx) => {
                    const opt = document.createElement('option');
                    opt.value = idx;
                    opt.textContent = name;
                    sheetSelect.appendChild(opt);
                });
                sheetSelection.style.display = 'block';

                let defaultSheet = 0;
                sheetNames.forEach((name, idx) => {
                    if (name.toUpperCase().includes('MEDICION')) {
                        defaultSheet = idx;
                    }
                });
                sheetSelect.value = defaultSheet;

                showAlert('Archivo cargado. ' + sheetNames.length + ' hoja(s) encontrada(s).', 'info');
                analyzeSheet();

            } catch (error) {
                showAlert('Error al leer el archivo: ' + error.message, 'danger');
                console.error(error);
            }
        };

        reader.onerror = function() {
            showAlert('Error al leer el archivo.', 'danger');
        };

        reader.readAsArrayBuffer(file);
    }

    // ============================================
    // ANALIZAR HOJA
    // ============================================
    function analyzeSheet() {
        if (!workbookData) return;

        currentSheetIndex = parseInt(sheetSelect.value);
        const sheetName = sheetNames[currentSheetIndex];
        const worksheet = workbookData.Sheets[sheetName];

        const rawData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });

        if (rawData.length < 2) {
            showAlert('La hoja no contiene suficientes datos.', 'danger');
            return;
        }

        headers = rawData[0].map(h => String(h || '').trim());
        rows = rawData.slice(1).filter(row => row.some(cell => cell !== '' && cell !== null && cell !== undefined));

        detectColumns();

        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        document.getElementById('step3').style.display = 'none';

        showAlert('Hoja "' + sheetName + '" analizada. ' + rows.length + ' filas de datos.', 'success');
    }

    // ============================================
    // DETECTAR COLUMNAS
    // ============================================
    function detectColumns() {
        const container = document.getElementById('mappingContainer');
        container.innerHTML = '';

        let loteCol = -1,
            medidorCol = -1,
            nombreCol = -1;
        let fechaCols = [];

        headers.forEach((header, idx) => {
            const h = header.toLowerCase();
            if (h.includes('lote') || h === 'lote') {
                loteCol = idx;
            } else if (h.includes('medidor')) {
                medidorCol = idx;
            } else if (h.includes('nombre') || h.includes('propietario') || h.includes('titular')) {
                nombreCol = idx;
            } else if (h.includes('/') || h.match(/\d{1,2}\/\d{1,2}/)) {
                fechaCols.push(idx);
            }
        });

        // Si no se encontró lote, buscar por posición
        if (loteCol === -1 && headers.length > 1) {
            for (let col = 0; col < Math.min(headers.length, 5); col++) {
                const sample = rows.slice(0, 5).map(row => row[col]).filter(v => v !== '');
                if (sample.length > 0 && sample.every(v => !isNaN(v) && v !== '')) {
                    loteCol = col;
                    break;
                }
            }
        }

        // Si no se encontró medidor
        if (medidorCol === -1) {
            for (let col = 0; col < Math.min(headers.length, 5); col++) {
                const sample = rows.slice(0, 5).map(row => row[col]).filter(v => v !== '');
                if (sample.length > 0 && sample.every(v => typeof v === 'string' && v.length > 3)) {
                    medidorCol = col;
                    break;
                }
            }
        }

        // Si no se encontraron fechas
        if (fechaCols.length === 0) {
            for (let col = 0; col < headers.length; col++) {
                if (col === loteCol || col === medidorCol || col === nombreCol) continue;
                const sample = rows.slice(0, 5).map(row => parseFloat(row[col])).filter(v => !isNaN(v) && v > 0);
                if (sample.length > 0) {
                    fechaCols.push(col);
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
            select.className = 'form-select mapping-select';
            select.dataset.key = field.key;
            select.innerHTML = `<option value="">-- No usar --</option>`;
            headers.forEach((h, idx) => {
                const selected = (idx === field.detected) ? 'selected' : '';
                const display = h || 'Columna ' + (idx + 1);
                select.innerHTML += `<option value="${idx}" ${selected}>${display}</option>`;
            });
            div.appendChild(select);
            container.appendChild(div);
        });

        // Columnas de fechas
        const fechaDiv = document.createElement('div');
        fechaDiv.className = 'col-12 mt-3';
        fechaDiv.innerHTML = `<hr><h6>Columnas de medición</h6>
            <p class="text-muted small">Estas columnas contienen los valores de medición para cada fecha.</p>`;
        container.appendChild(fechaDiv);

        if (fechaCols.length === 0) {
            const alert = document.createElement('div');
            alert.className = 'col-12 alert alert-warning';
            alert.textContent = 'No se detectaron columnas de medición.';
            container.appendChild(alert);
        } else {
            fechaCols.forEach((colIdx) => {
                const div = document.createElement('div');
                div.className = 'col-md-3 mb-2';
                const headerText = headers[colIdx] || 'Fecha ' + (colIdx + 1);
                const sampleVal = rows.length > 0 ? rows[0][colIdx] : 'N/A';
                div.innerHTML = `
                    <label class="mapping-label">${headerText}</label>
                    <span class="mapping-hint">Ej: ${sampleVal}</span>
                    <select class="form-select mapping-select" data-fecha="${colIdx}">
                        <option value="${colIdx}" selected>${headerText}</option>
                    </select>
                `;
                container.appendChild(div);
            });
        }
    }

    // ============================================
    // PARSEAR FECHA DESDE ENCABEZADO
    // ============================================
    function parseDateFromHeader(header) {
        if (!header) return null;
        let match = header.match(/(\d{1,2})\/(\d{1,2})(?:\/(\d{4}))?/);
        if (match) {
            const day = parseInt(match[1]);
            const month = parseInt(match[2]);
            const year = match[3] ? parseInt(match[3]) : null;
            if (day >= 1 && day <= 31 && month >= 1 && month <= 12) {
                let y = year;
                if (!y) {
                    const currentYear = new Date().getFullYear();
                    if (month >= 11) y = currentYear - 1;
                    else y = currentYear;
                }
                return new Date(y, month - 1, day);
            }
        }
        try {
            const d = new Date(header);
            if (!isNaN(d.getTime())) return d;
        } catch (e) {}
        return null;
    }

    // ============================================
    // PREVISUALIZAR DATOS
    // ============================================
    function previewData() {
        const mapping = {
            lote: parseInt(document.querySelector('#mappingContainer select[data-key="lote"]').value),
            medidor: parseInt(document.querySelector('#mappingContainer select[data-key="medidor"]').value),
            nombre: parseInt(document.querySelector('#mappingContainer select[data-key="nombre"]').value),
            fechas: []
        };

        document.querySelectorAll('#mappingContainer select[data-fecha]').forEach(function(sel) {
            const val = sel.value;
            if (val !== '') mapping.fechas.push(parseInt(val));
        });

        if (isNaN(mapping.lote) || mapping.lote < 0) {
            showAlert('Debes seleccionar la columna de LOTE.', 'warning');
            return;
        }
        if (isNaN(mapping.medidor) || mapping.medidor < 0) {
            showAlert('Debes seleccionar la columna de MEDIDOR.', 'warning');
            return;
        }
        if (mapping.fechas.length === 0) {
            showAlert('Debes seleccionar al menos una columna de medición.', 'warning');
            return;
        }

        previewData = [];
        const errors = [];

        rows.forEach((row, rowIdx) => {
            const lote = String(row[mapping.lote] || '').trim();
            const medidor = String(row[mapping.medidor] || '').trim();
            const nombre = mapping.nombre >= 0 ? String(row[mapping.nombre] || '').trim() : '';

            if (!lote) {
                errors.push('Fila ' + (rowIdx + 2) + ': Lote vacío.');
                return;
            }

            const mediciones = [];
            mapping.fechas.forEach(colIdx => {
                const valor = parseFloat(row[colIdx]);
                if (!isNaN(valor) && valor >= 0) {
                    const fechaHeader = headers[colIdx] || '';
                    const fecha = parseDateFromHeader(fechaHeader);
                    mediciones.push({ fecha: fecha, valor: valor, header: fechaHeader });
                }
            });

            if (mediciones.length === 0) {
                errors.push('Fila ' + (rowIdx + 2) + ': No se encontraron valores.');
                return;
            }

            mediciones.sort((a, b) => {
                if (a.fecha && b.fecha) return a.fecha - b.fecha;
                return 0;
            });

            previewData.push({
                lote: lote,
                medidor: medidor,
                nombre: nombre,
                mediciones: mediciones,
                rowIndex: rowIdx + 2,
                valid: true
            });
        });

        renderPreview(previewData, errors);
    }

    // ============================================
    // RENDERIZAR PREVIEW
    // ============================================
    function renderPreview(data, errors) {
        const tbody = document.getElementById('previewBody');
        const thead = document.getElementById('previewHead');
        tbody.innerHTML = '';

        let headerHtml = '<tr><th>#</th><th>Lote</th><th>Medidor</th><th>Nombre</th>';
        const allDates = new Set();
        data.forEach(item => {
            item.mediciones.forEach(m => {
                if (m.fecha) {
                    allDates.add(m.fecha.toISOString().split('T')[0]);
                } else if (m.header) {
                    allDates.add(m.header);
                }
            });
        });
        const sortedDates = Array.from(allDates).sort();
        sortedDates.forEach(date => {
            headerHtml += '<th>' + date + '</th>';
        });
        headerHtml += '<th>Estado</th></tr>';
        thead.innerHTML = headerHtml;

        let validCount = 0;
        let errorCount = 0;
        let totalMediciones = 0;

        data.forEach((item, idx) => {
            const isValid = item.mediciones.length > 0;
            if (isValid) validCount++;
            else errorCount++;
            totalMediciones += item.mediciones.length;

            let rowHtml = '<tr class="' + (isValid ? '' : 'table-danger') + '">';
            rowHtml += '<td>' + item.rowIndex + '</td>';
            rowHtml += '<td><strong>' + item.lote + '</strong></td>';
            rowHtml += '<td>' + item.medidor + '</td>';
            rowHtml += '<td>' + (item.nombre || '-') + '</td>';

            const valuesByDate = {};
            item.mediciones.forEach(m => {
                const key = m.fecha ? m.fecha.toISOString().split('T')[0] : m.header;
                valuesByDate[key] = m.valor;
            });

            sortedDates.forEach(date => {
                const val = valuesByDate[date];
                rowHtml += '<td>' + (val !== undefined ? val : '-') + '</td>';
            });

            const statusBadge = isValid ?
                '<span class="badge bg-success">Válido</span>' :
                '<span class="badge bg-danger">Error</span>';
            rowHtml += '<td>' + statusBadge + '</td></tr>';

            tbody.innerHTML += rowHtml;
        });

        document.getElementById('previewSummary').innerHTML = `
            <div class="row">
                <div class="col-md-3"><div class="text-center"><div class="number">${data.length}</div><div class="label">Total filas</div></div></div>
                <div class="col-md-3"><div class="text-center text-success"><div class="number">${validCount}</div><div class="label">Válidas</div></div></div>
                <div class="col-md-3"><div class="text-center text-danger"><div class="number">${errorCount}</div><div class="label">Con errores</div></div></div>
                <div class="col-md-3"><div class="text-center text-primary"><div class="number">${totalMediciones}</div><div class="label">Mediciones</div></div></div>
            </div>
        `;

        const importErrors = document.getElementById('importErrors');
        if (errors.length > 0) {
            importErrors.style.display = 'block';
            importErrors.innerHTML = '<strong>Errores:</strong><ul>' + errors.map(e => '<li>' + e + '</li>').join('') + '</ul>';
        } else {
            importErrors.style.display = 'none';
        }

        importData = data;
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step3').style.display = 'block';
    }

    // ============================================
    // IMPORTAR DATOS
    // ============================================
    function importDataToSystem() {
        if (importData.length === 0) {
            showAlert('No hay datos para importar.', 'warning');
            return;
        }

        const validData = importData.filter(item => item.mediciones.length > 0);
        if (validData.length === 0) {
            showAlert('No hay datos válidos para importar.', 'warning');
            return;
        }

        document.getElementById('progressContainer').style.display = 'block';
        document.getElementById('btnImport').disabled = true;
        document.getElementById('progressBar').style.width = '10%';
        document.getElementById('progressBar').textContent = '10%';
        document.getElementById('progressText').textContent = 'Preparando datos...';

        const payload = validData.map(item => ({
            lote: item.lote,
            medidor: item.medidor,
            nombre: item.nombre,
            mediciones: item.mediciones.map(m => ({
                fecha: m.fecha ? m.fecha.toISOString().split('T')[0] : null,
                valor: m.valor,
                header: m.header || null
            }))
        }));

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
            document.getElementById('progressBar').style.width = '100%';
            document.getElementById('progressBar').textContent = '100%';
            document.getElementById('progressText').textContent = 'Importación completada.';

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

                document.getElementById('btnReloadPage').onclick = function() {
                    window.location.href = '{{ route("getTodasMedVista") }}';
                };

                showAlert(response.message, 'success');
            } else {
                showAlert(response.message || 'Error en la importación', 'danger');
            }
        })
        .catch(error => {
            const msg = error.message || 'Error de conexión';
            showAlert('Error en la importación: ' + msg, 'danger');
            console.error(error);
        })
        .finally(function() {
            document.getElementById('btnImport').disabled = false;
        });
    }

    // ============================================
    // EVENTOS
    // ============================================
    document.getElementById('btnAnalyze').addEventListener('click', analyzeSheet);
    document.getElementById('btnPreview').addEventListener('click', previewData);
    document.getElementById('btnImport').addEventListener('click', importDataToSystem);

    document.getElementById('btnBackStep1').addEventListener('click', function() {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
    });

    document.getElementById('btnBackStep2').addEventListener('click', function() {
        document.getElementById('step3').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
    });

    document.getElementById('btnRemoveFile').addEventListener('click', function() {
        currentFile = null;
        workbookData = null;
        fileInfo.classList.remove('visible');
        fileInput.value = '';
        sheetSelection.style.display = 'none';
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step3').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
        showAlert('Archivo removido.', 'info');
    });

    // ============================================
    // ALERTAS
    // ============================================
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

})(); // Fin de la función auto-ejecutable
</script>
@endsection