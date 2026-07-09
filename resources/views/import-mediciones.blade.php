{{-- resources/views/import-mediciones.blade.php --}}
@extends('layouts.app')

@section('title', 'Importar Mediciones Masivas')

@section('content')
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
    }
    .drop-zone:hover {
        border-color: #0d6efd;
        background: #e7f1ff;
    }
    .drop-zone.active {
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
    .preview-table {
        max-height: 400px;
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
    .badge-status {
        font-size: 0.7rem;
        padding: 3px 8px;
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
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4><i class="bi bi-file-earmark-excel"></i> Importar Mediciones desde Excel</h4>
                    <a href="{{ route('getTodasMedVista') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>

                    <!-- ========================================== -->
                    <!-- PASO 1: SUBIR ARCHIVO                       -->
                    <!-- ========================================== -->
                    <div id="step1" class="step">
                        <h5><i class="bi bi-upload"></i> Paso 1: Subir archivo Excel</h5>
                        <p class="text-muted">Selecciona el archivo <strong>.xlsx</strong> o <strong>.xls</strong> con las mediciones.</p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Formato esperado:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Primera columna: <strong>NOMBRE</strong> o <strong>LOTE</strong> (nombre del propietario)</li>
                                <li>Segunda columna: <strong>LOTE</strong> (número de lote)</li>
                                <li>Tercera columna: <strong>MEDIDOR</strong> (código del medidor)</li>
                                <li>Columnas siguientes: fechas con los valores de medición</li>
                            </ul>
                        </div>

                        <div class="drop-zone" id="dropZone">
                            <i class="bi bi-file-earmark-excel"></i>
                            <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
                            <p class="text-muted small">(Solo archivos .xlsx, .xls o .csv)</p>
                            <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" style="display: none;">
                        </div>

                        <div id="fileInfo" class="file-info">
                            <i class="bi bi-file-earmark-excel text-success"></i>
                            <span id="fileName"></span>
                            <span class="badge bg-secondary ms-2" id="fileSize"></span>
                        </div>

                        <div id="sheetSelection" class="mt-3" style="display:none;">
                            <label for="sheetSelect" class="form-label">Selecciona la hoja del archivo:</label>
                            <select id="sheetSelect" class="form-select"></select>
                            <button class="btn btn-primary mt-2" id="btnAnalyze">
                                <i class="bi bi-search"></i> Analizar archivo
                            </button>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- PASO 2: MAPEO DE COLUMNAS                   -->
                    <!-- ========================================== -->
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

                    <!-- ========================================== -->
                    <!-- PASO 3: PREVISUALIZACIÓN                   -->
                    <!-- ========================================== -->
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

                    <!-- ========================================== -->
                    <!-- PROGRESO                                    -->
                    <!-- ========================================== -->
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
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Importación completada</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="successModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnReloadPage">Ir a Mediciones</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- SheetJS CDN para leer Excel -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
    $(document).ready(function() {
        // ============================================
        // VARIABLES GLOBALES
        // ============================================
        let currentFile = null;
        let workbookData = null;
        let sheetNames = [];
        let currentSheetIndex = 0;
        let headers = [];
        let rows = [];
        let previewData = [];
        let importData = [];
        let mappedColumns = {};

        // ============================================
        // ELEMENTOS DOM
        // ============================================
        const dropZone = $('#dropZone');
        const fileInput = $('#fileInput');
        const fileInfo = $('#fileInfo');
        const fileName = $('#fileName');
        const fileSize = $('#fileSize');
        const sheetSelection = $('#sheetSelection');
        const sheetSelect = $('#sheetSelect');
        const alertContainer = $('#alertContainer');

        // ============================================
        // EVENTOS DE DRAG & DROP (CORREGIDOS)
        // ============================================
        // Prevenir comportamiento por defecto del navegador
        $(document).on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

        $(document).on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });

        dropZone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        dropZone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        dropZone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');

            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                fileInput[0].files = files;
                handleFileSelect();
            }
        });

        dropZone.on('click', function() {
            fileInput.click();
        });

        fileInput.on('change', handleFileSelect);

        // ============================================
        // MANEJAR SELECCIÓN DE ARCHIVO
        // ============================================
        function handleFileSelect() {
            const file = fileInput[0].files[0];
            if (!file) return;

            currentFile = file;
            fileName.text(file.name);
            fileSize.text((file.size / 1024).toFixed(1) + ' KB');
            fileInfo.addClass('visible');

            showAlert('Archivo seleccionado: ' + file.name, 'success');

            // Leer el archivo con SheetJS
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

                    // Llenar selector de hojas
                    sheetSelect.empty();
                    sheetNames.forEach((name, idx) => {
                        sheetSelect.append(`<option value="${idx}">${name}</option>`);
                    });
                    sheetSelection.show();

                    // Seleccionar la hoja que parece tener datos (priorizar las que contienen "MEDICION")
                    let defaultSheet = 0;
                    sheetNames.forEach((name, idx) => {
                        if (name.toUpperCase().includes('MEDICION')) {
                            defaultSheet = idx;
                        }
                    });
                    sheetSelect.val(defaultSheet);

                    showAlert('Archivo cargado correctamente. ' + sheetNames.length + ' hoja(s) encontrada(s).', 'info');

                    // Analizar automáticamente la hoja seleccionada
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
        // ANALIZAR HOJA SELECCIONADA
        // ============================================
        function analyzeSheet() {
            if (!workbookData) return;

            currentSheetIndex = parseInt(sheetSelect.val());
            const sheetName = sheetNames[currentSheetIndex];
            const worksheet = workbookData.Sheets[sheetName];

            // Convertir a JSON (array de arrays)
            const rawData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });

            if (rawData.length < 2) {
                showAlert('La hoja no contiene suficientes datos.', 'danger');
                return;
            }

            // Encabezados (primera fila)
            headers = rawData[0].map(h => String(h || '').trim());
            rows = rawData.slice(1).filter(row => row.some(cell => cell !== '' && cell !== null && cell !== undefined));

            // Detectar columnas automáticamente
            detectColumns();

            // Mostrar paso 2
            $('#step1').hide();
            $('#step2').show();
            $('#step3').hide();

            showAlert('Hoja "' + sheetName + '" analizada. ' + rows.length + ' filas de datos encontradas.', 'success');
        }

        // ============================================
        // DETECTAR COLUMNAS AUTOMÁTICAMENTE
        // ============================================
        function detectColumns() {
            const container = $('#mappingContainer');
            container.empty();

            // Buscar columnas por nombre
            let loteCol = -1, medidorCol = -1, nombreCol = -1;
            let fechaCols = [];

            headers.forEach((header, idx) => {
                const h = header.toLowerCase();
                if (h.includes('lote') || h === 'lote' || h.includes('lote')) {
                    loteCol = idx;
                } else if (h.includes('medidor') || h.includes('medidor')) {
                    medidorCol = idx;
                } else if (h.includes('nombre') || h.includes('propietario') || h.includes('titular')) {
                    nombreCol = idx;
                } else if (h.includes('/') || h.match(/\d{1,2}\/\d{1,2}/)) {
                    fechaCols.push(idx);
                }
            });

            // Si no se encontró lote, buscar por posición (asumimos columna 1 o 2)
            if (loteCol === -1 && headers.length > 1) {
                // Buscar columna que tenga valores numéricos (lotes)
                let candidate = -1;
                for (let col = 0; col < Math.min(headers.length, 5); col++) {
                    const sample = rows.slice(0, 5).map(row => row[col]).filter(v => v !== '');
                    if (sample.length > 0 && sample.every(v => !isNaN(v) && v !== '')) {
                        candidate = col;
                        break;
                    }
                }
                if (candidate !== -1) loteCol = candidate;
            }

            // Si no se encontró medidor, buscar columna que parezca código
            if (medidorCol === -1) {
                for (let col = 0; col < Math.min(headers.length, 5); col++) {
                    const sample = rows.slice(0, 5).map(row => row[col]).filter(v => v !== '');
                    if (sample.length > 0 && sample.every(v => typeof v === 'string' && v.length > 5 && /[A-Z0-9]/.test(v))) {
                        medidorCol = col;
                        break;
                    }
                }
            }

            // Si no se encontraron fechas, buscar columnas con números grandes
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
                const div = $('<div class="col-md-4"></div>');
                div.append(`<label class="form-label">${field.label}</label>`);
                const select = $(`<select class="form-select" data-key="${field.key}"></select>`);
                select.append(`<option value="">-- No usar --</option>`);
                headers.forEach((h, idx) => {
                    const selected = (idx === field.detected) ? 'selected' : '';
                    select.append(`<option value="${idx}" ${selected}>${h || 'Columna ' + (idx+1)}</option>`);
                });
                div.append(select);
                container.append(div);
            });

            // Columnas de fechas (valores de medición)
            const fechaDiv = $(`<div class="col-12 mt-3"><hr><h6>Columnas de medición (fechas y valores)</h6>
                <p class="text-muted small">Estas columnas contienen los valores de medición para cada fecha.</p></div>`);
            container.append(fechaDiv);

            if (fechaCols.length === 0) {
                container.append(`<div class="col-12 alert alert-warning">No se detectaron columnas de medición. Asegúrate de que el archivo tenga valores numéricos.</div>`);
            } else {
                fechaCols.forEach((colIdx, idx) => {
                    const div = $('<div class="col-md-3"></div>');
                    const headerText = headers[colIdx] || 'Fecha ' + (idx + 1);
                    div.append(`<label class="form-label">${headerText}</label>`);
                    // Mostrar el valor de ejemplo
                    const sampleVal = rows.length > 0 ? rows[0][colIdx] : 'N/A';
                    div.append(`<small class="text-muted d-block mb-1">Ejemplo: ${sampleVal}</small>`);
                    const select = $(`<select class="form-select" data-fecha="${colIdx}" data-header="${headerText}"></select>`);
                    select.append(`<option value="${colIdx}" selected>${headerText}</option>`);
                    // Agregar opción para no usar
                    select.append(`<option value="">-- No usar --</option>`);
                    div.append(select);
                    container.append(div);
                });
            }

            // Guardar referencia de columnas de fechas
            mappedColumns = { lote: loteCol, medidor: medidorCol, nombre: nombreCol, fechas: fechaCols };
        }

        // ============================================
        // PREVISUALIZAR DATOS
        // ============================================
        function previewData() {
            // Obtener mapeo seleccionado
            const mapping = {
                lote: parseInt($('#mappingContainer select[data-key="lote"]').val()),
                medidor: parseInt($('#mappingContainer select[data-key="medidor"]').val()),
                nombre: parseInt($('#mappingContainer select[data-key="nombre"]').val()),
                fechas: []
            };

            $('#mappingContainer select[data-fecha]').each(function() {
                const val = $(this).val();
                if (val !== '') {
                    mapping.fechas.push(parseInt(val));
                }
            });

            // Validar que haya lote y medidor
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

            // Construir datos de preview
            previewData = [];
            const errors = [];

            rows.forEach((row, rowIdx) => {
                const lote = String(row[mapping.lote] || '').trim();
                const medidor = String(row[mapping.medidor] || '').trim();
                const nombre = mapping.nombre >= 0 ? String(row[mapping.nombre] || '').trim() : '';

                if (!lote) {
                    errors.push(`Fila ${rowIdx + 2}: Lote vacío.`);
                    return;
                }

                // Obtener mediciones de las columnas de fecha
                const mediciones = [];
                mapping.fechas.forEach(colIdx => {
                    const valor = parseFloat(row[colIdx]);
                    if (!isNaN(valor) && valor >= 0) {
                        // Intentar obtener la fecha del encabezado
                        const fechaHeader = headers[colIdx] || '';
                        const fecha = parseDateFromHeader(fechaHeader);
                        if (fecha) {
                            mediciones.push({ fecha: fecha, valor: valor });
                        } else {
                            // Si no se pudo parsear la fecha, usar un índice
                            mediciones.push({ fecha: null, valor: valor, header: fechaHeader });
                        }
                    }
                });

                if (mediciones.length === 0) {
                    errors.push(`Fila ${rowIdx + 2}: No se encontraron valores de medición.`);
                    return;
                }

                // Ordenar por fecha (si es posible)
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
                    valid: true,
                    errors: []
                });
            });

            renderPreview(previewData, errors);
        }

        // ============================================
        // PARSEAR FECHA DESDE ENCABEZADO
        // ============================================
        function parseDateFromHeader(header) {
            if (!header) return null;

            // Buscar patrones de fecha: "03/12", "02/01/2026", "NOVIEMBRE 03/12"
            let match = header.match(/(\d{1,2})\/(\d{1,2})(?:\/(\d{4}))?/);
            if (match) {
                const day = parseInt(match[1]);
                const month = parseInt(match[2]);
                const year = match[3] ? parseInt(match[3]) : null;

                if (day >= 1 && day <= 31 && month >= 1 && month <= 12) {
                    let y = year;
                    if (!y) {
                        // Asumir año basado en el mes (si es nov/dic => 2025, sino 2026)
                        const currentYear = new Date().getFullYear();
                        if (month >= 11) {
                            y = currentYear - 1;
                        } else {
                            y = currentYear;
                        }
                    }
                    return new Date(y, month - 1, day);
                }
            }

            // Intentar parsear como fecha directamente
            try {
                const d = new Date(header);
                if (!isNaN(d.getTime())) return d;
            } catch(e) {}

            return null;
        }

        // ============================================
        // RENDERIZAR PREVIEW
        // ============================================
        function renderPreview(data, errors) {
            const tbody = $('#previewBody');
            const thead = $('#previewHead');
            tbody.empty();

            // Cabeceras
            let headerHtml = '<tr><th>#</th><th>Lote</th><th>Medidor</th><th>Nombre</th>';
            // Obtener todas las fechas únicas
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
                headerHtml += `<th>${date}</th>`;
            });
            headerHtml += '<th>Estado</th></tr>';
            thead.html(headerHtml);

            // Filas
            let validCount = 0;
            let errorCount = 0;
            let totalMediciones = 0;

            data.forEach((item, idx) => {
                // Validar que el lote exista en el sistema (llamada al backend)
                // Por ahora, marcamos como válido si tiene datos
                const isValid = item.mediciones.length > 0;
                if (isValid) validCount++;
                else errorCount++;
                totalMediciones += item.mediciones.length;

                let rowHtml = `<tr class="${isValid ? '' : 'table-danger'}">
                    <td>${item.rowIndex}</td>
                    <td><strong>${item.lote}</strong></td>
                    <td>${item.medidor}</td>
                    <td>${item.nombre || '-'}</td>`;

                // Valores por fecha
                const valuesByDate = {};
                item.mediciones.forEach(m => {
                    const key = m.fecha ? m.fecha.toISOString().split('T')[0] : m.header;
                    valuesByDate[key] = m.valor;
                });

                sortedDates.forEach(date => {
                    const val = valuesByDate[date];
                    rowHtml += `<td>${val !== undefined ? val : '-'}</td>`;
                });

                const statusBadge = isValid ?
                    '<span class="badge bg-success">Válido</span>' :
                    '<span class="badge bg-danger">Error</span>';
                rowHtml += `<td>${statusBadge}</td></tr>`;

                tbody.append(rowHtml);
            });

            // Resumen
            const summary = $('#previewSummary');
            summary.html(`
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="number">${data.length}</div>
                            <div class="label">Total de filas</div>
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
                            <div class="number">${totalMediciones}</div>
                            <div class="label">Mediciones totales</div>
                        </div>
                    </div>
                </div>
            `);

            // Errores
            if (errors.length > 0) {
                $('#importErrors').show().html(`<strong>Errores encontrados:</strong><ul>${errors.map(e => `<li>${e}</li>`).join('')}</ul>`);
            } else {
                $('#importErrors').hide();
            }

            // Guardar datos para importar
            importData = data;

            $('#step2').hide();
            $('#step3').show();
        }

        // ============================================
        // IMPORTAR DATOS
        // ============================================
        function importDataToSystem() {
            if (importData.length === 0) {
                showAlert('No hay datos para importar.', 'warning');
                return;
            }

            // Filtrar solo datos válidos
            const validData = importData.filter(item => item.mediciones.length > 0);

            if (validData.length === 0) {
                showAlert('No hay datos válidos para importar.', 'warning');
                return;
            }

            $('#progressContainer').show();
            $('#btnImport').prop('disabled', true);
            $('#progressBar').css('width', '10%').text('10%');
            $('#progressText').text('Preparando datos...');

            // Preparar datos para el backend
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

            const token = $('#bearerToken').val() || localStorage.getItem('token');

            $.ajax({
                url: '/api/import-mediciones/import',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify({ data: payload }),
                beforeSend: function() {
                    $('#progressBar').css('width', '40%').text('40%');
                    $('#progressText').text('Enviando datos al servidor...');
                },
                success: function(response) {
                    $('#progressBar').css('width', '100%').text('100%');
                    $('#progressText').text('Importación completada.');

                    if (response.success) {
                        $('#successModalBody').html(`
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
                        `);
                        $('#successModal').modal('show');

                        $('#btnReloadPage').off('click').on('click', function() {
                            window.location.href = '{{ route("getTodasMedVista") }}';
                        });

                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message || 'Error en la importación', 'danger');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || xhr.statusText;
                    showAlert('Error en la importación: ' + msg, 'danger');
                    console.error(xhr);
                },
                complete: function() {
                    $('#btnImport').prop('disabled', false);
                }
            });
        }

        // ============================================
        // EVENTOS DE NAVEGACIÓN
        // ============================================
        $('#btnAnalyze').on('click', analyzeSheet);

        $('#btnPreview').on('click', previewData);

        $('#btnImport').on('click', importDataToSystem);

        $('#btnBackStep1').on('click', function() {
            $('#step2').hide();
            $('#step1').show();
        });

        $('#btnBackStep2').on('click', function() {
            $('#step3').hide();
            $('#step2').show();
        });

        $('#sheetSelect').on('change', function() {
            // No analizar automáticamente, esperar clic en "Analizar"
        });

        // ============================================
        // FUNCIÓN PARA MOSTRAR ALERTAS
        // ============================================
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            alertContainer.append(alertHtml);
            // Auto-cerrar después de 5 segundos
            setTimeout(() => {
                alertContainer.find('.alert:last').alert('close');
            }, 8000);
        }

        // ============================================
        // TOKEN PARA LAS PETICIONES
        // ============================================
        // Si no existe el token en el DOM, intentar obtenerlo de localStorage
        if (!$('#bearerToken').length) {
            $('body').append(`<input type="hidden" id="bearerToken" value="${localStorage.getItem('token') || ''}">`);
        }
    });
</script>
@endpush