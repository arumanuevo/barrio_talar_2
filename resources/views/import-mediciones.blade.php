{{-- resources/views/import-mediciones.blade.php --}}
@extends('layouts.app')

@section('title', 'Importar Mediciones Masivas')

@section('content')
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* ... estilos anteriores ... */
    
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
    .preview-loading .progress-text {
        font-size: 0.95rem;
        color: #6c757d;
    }
    .preview-loading .progress-text strong {
        color: #0d6efd;
    }
    .preview-loading .progress-bar-container {
        max-width: 400px;
        margin: 10px auto 0;
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }
    .preview-loading .progress-bar-container .bar {
        height: 100%;
        width: 0%;
        background: #0d6efd;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* ... resto de estilos ... */
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
                                <li>Columnas siguientes: fechas con los valores de medición</li>
                            </ul>
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
                                <strong id="previewStatusTitle">Procesando datos...</strong>
                                <br>
                                <span id="previewProgressText">Preparando archivo...</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="bar" id="previewProgressBar"></div>
                            </div>
                        </div>

                        <div id="previewSummary" class="summary-card" style="display:none;"></div>

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
                                <i class="bi bi-check-circle"></i> Confirmar e Importar
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
    const previewProgressBar = document.getElementById('previewProgressBar');
    const previewStatusTitle = document.getElementById('previewStatusTitle');
    const previewSummary = document.getElementById('previewSummary');
    const previewTableContainer = document.getElementById('previewTableContainer');
    const previewActions = document.getElementById('previewActions');

    // ============================================
    // FUNCIONES DE PROGRESO DE PREVIEW
    // ============================================
    function updatePreviewProgress(percent, text, title) {
        previewProgressBar.style.width = percent + '%';
        previewProgressText.textContent = text || '';
        if (title) {
            previewStatusTitle.textContent = title;
        }
    }

    function showPreviewLoading() {
        previewLoading.classList.add('show');
        previewSummary.style.display = 'none';
        previewTableContainer.style.display = 'none';
        previewActions.style.display = 'none';
        updatePreviewProgress(0, 'Iniciando análisis...', 'Procesando datos...');
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

                let defaultSheet = 0;
                importSheetNames.forEach((name, idx) => {
                    if (name.includes('MEDICION CONTRA FACTURA')) {
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
            } else if (h.includes('/') || h.match(/\d{1,2}\/\d{1,2}/)) {
                fechaCols.push(idx);
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

        if (fechaCols.length === 0) {
            for (let col = 0; col < importHeaders.length; col++) {
                if (col === loteCol || col === medidorCol || col === nombreCol) continue;
                const sample = importRows.slice(0, 5).map(row => parseFloat(row[col])).filter(v => !isNaN(v) && v > 0);
                if (sample.length > 0) {
                    fechaCols.push(col);
                }
            }
        }

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
                const headerText = importHeaders[colIdx] || 'Fecha ' + (colIdx + 1);
                const sampleVal = importRows.length > 0 ? importRows[0][colIdx] : 'N/A';
                div.innerHTML = `
                    <label class="mapping-label">${headerText}</label>
                    <span class="mapping-hint">Ej: ${sampleVal}</span>
                    <select class="form-select import-mapping-select" data-fecha="${colIdx}">
                        <option value="${colIdx}" selected>${headerText}</option>
                    </select>
                `;
                container.appendChild(div);
            });
        }
    }

    // ============================================
    // PARSEAR FECHA
    // ============================================
    function parseImportDateFromHeader(header) {
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
    // PREVISUALIZAR DATOS (ASÍNCRONO)
    // ============================================
    function previewImportData() {
        showPreviewLoading();
        updatePreviewProgress(5, 'Obteniendo configuración...', 'Preparando previsualización...');

        // ✅ Usar setTimeout para no bloquear la UI
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

                updatePreviewProgress(10, 'Procesando filas del archivo...', 'Analizando datos...');

                // ✅ Procesar en bloques para no bloquear la UI
                const totalRows = importRows.length;
                const CHUNK_SIZE = 10;
                let processedRows = 0;
                let allPreviewData = [];
                let allErrors = [];

                function processChunk() {
                    const start = processedRows;
                    const end = Math.min(start + CHUNK_SIZE, totalRows);
                    
                    for (let i = start; i < end; i++) {
                        const row = importRows[i];
                        const rowIdx = i;
                        
                        const lote = String(row[mapping.lote] || '').trim();
                        const medidor = String(row[mapping.medidor] || '').trim();
                        const nombre = mapping.nombre >= 0 ? String(row[mapping.nombre] || '').trim() : '';

                        if (!lote) {
                            allErrors.push('Fila ' + (rowIdx + 2) + ': Lote vacío.');
                            continue;
                        }

                        const mediciones = [];
                        mapping.fechas.forEach(colIdx => {
                            const valor = parseFloat(row[colIdx]);
                            if (!isNaN(valor) && valor >= 0) {
                                const fechaHeader = importHeaders[colIdx] || '';
                                const fecha = parseImportDateFromHeader(fechaHeader);
                                mediciones.push({ fecha: fecha, valor: valor, header: fechaHeader });
                            }
                        });

                        if (mediciones.length === 0) {
                            allErrors.push('Fila ' + (rowIdx + 2) + ': No se encontraron valores.');
                            continue;
                        }

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

                            allPreviewData.push({
                                lote: lote,
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
                    }

                    processedRows = end;
                    
                    // ✅ Actualizar progreso (10% a 90%)
                    const progress = 10 + (processedRows / totalRows) * 80;
                    updatePreviewProgress(progress, `Procesando fila ${processedRows} de ${totalRows}...`);

                    // ✅ Si hay más filas, procesar el siguiente bloque
                    if (processedRows < totalRows) {
                        setTimeout(processChunk, 50);
                    } else {
                        // ✅ Terminado, renderizar preview
                        importPreviewData = allPreviewData;
                        renderImportPreview(allPreviewData, allErrors);
                        updatePreviewProgress(100, '¡Previsualización completada!', '¡Listo!');
                        setTimeout(hidePreviewLoading, 500);
                    }
                }

                // ✅ Iniciar procesamiento por bloques
                processChunk();

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
    function renderImportPreview(data, errors) {
        const tbody = document.getElementById('importPreviewBody');
        const thead = document.getElementById('importPreviewHead');
        tbody.innerHTML = '';

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
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="number">${data.length}</div>
                        <div class="label">Total registros a insertar</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center text-success">
                        <div class="number">${validCount}</div>
                        <div class="label">Válidos</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center text-danger">
                        <div class="number">${errorCount}</div>
                        <div class="label">Con errores</div>
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
            importErrors.innerHTML = '<strong>Errores:</strong><ul>' + errors.map(e => '<li>' + e + '</li>').join('') + '</ul>';
        } else {
            importErrors.style.display = 'none';
        }

        importDataToSend = data;
        document.getElementById('importStep2').style.display = 'none';
        document.getElementById('importStep3').style.display = 'block';
    }

    // ============================================
    // IMPORTAR DATOS
    // ============================================
    function importDataToSystem() {
        if (importDataToSend.length === 0) {
            showImportAlert('No hay datos para importar.', 'warning');
            return;
        }

        const validData = importDataToSend.filter(item => item.valid !== false);
        if (validData.length === 0) {
            showImportAlert('No hay datos válidos para importar.', 'warning');
            return;
        }

        document.getElementById('importProgressContainer').style.display = 'block';
        document.getElementById('importConfirmBtn').disabled = true;
        document.getElementById('importProgressBar').style.width = '10%';
        document.getElementById('importProgressBar').textContent = '10%';
        document.getElementById('importProgressText').textContent = 'Preparando datos...';

        const groupedByLote = {};
        validData.forEach(item => {
            if (!groupedByLote[item.lote]) {
                groupedByLote[item.lote] = {
                    lote: item.lote,
                    medidor: item.medidor,
                    nombre: item.nombre,
                    mediciones: []
                };
            }
            groupedByLote[item.lote].mediciones.push({
                fecha: item.fecha ? item.fecha.toISOString().split('T')[0] : null,
                valor: item.valormedido,
                consumo: item.consumo
            });
        });

        const payload = Object.values(groupedByLote);
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