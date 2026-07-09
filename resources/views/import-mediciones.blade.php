{{-- resources/views/import-mediciones.blade.php --}}
@extends('layouts.app') {{-- Ajusta al layout que uses --}}

@section('title', 'Importar Mediciones Masivas')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-file-earmark-excel"></i> Importar Mediciones desde Excel</h4>
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>

                    <!-- Paso 1: Subir archivo -->
                    <div id="step1">
                        <h5>Paso 1: Subir archivo Excel</h5>
                        <p class="text-muted">Selecciona el archivo .xlsx o .csv con las mediciones.</p>
                        <div class="drop-zone" id="dropZone">
                            <i class="bi bi-file-earmark-excel" style="font-size: 3rem;"></i>
                            <p><strong>Arrastra y suelta el archivo aquí</strong> o haz clic para seleccionarlo</p>
                            <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" style="display: none;">
                        </div>
                        <div id="sheetSelection" class="mt-3" style="display:none;">
                            <label for="sheetSelect">Selecciona la hoja:</label>
                            <select id="sheetSelect" class="form-select"></select>
                            <button class="btn btn-primary mt-2" id="btnAnalyze">Analizar archivo</button>
                        </div>
                    </div>

                    <!-- Paso 2: Mapeo de columnas -->
                    <div id="step2" style="display:none;">
                        <hr>
                        <h5>Paso 2: Mapear columnas</h5>
                        <p class="text-muted">Asigna las columnas del archivo a los campos del sistema.</p>
                        <div id="mappingContainer" class="row g-3"></div>
                        <button class="btn btn-primary mt-3" id="btnPreview">Previsualizar</button>
                    </div>

                    <!-- Paso 3: Previsualización y confirmación -->
                    <div id="step3" style="display:none;">
                        <hr>
                        <h5>Paso 3: Previsualización</h5>
                        <div id="previewContainer"></div>
                        <button class="btn btn-success mt-3" id="btnImport">Confirmar e Importar</button>
                    </div>

                    <!-- Barra de progreso -->
                    <div id="progressContainer" style="display:none;" class="mt-3">
                        <div class="progress">
                            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <p id="progressText" class="mt-2">Procesando...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentFile = null;
    let currentSheetIndex = 0;
    let headers = [];
    let previewData = [];
    let importData = [];

    $(document).ready(function() {
        // Drop zone
        const dropZone = $('#dropZone');
        const fileInput = $('#fileInput');

        dropZone.on('click', () => fileInput.click());
        dropZone.on('dragover', (e) => { e.preventDefault(); dropZone.addClass('active'); });
        dropZone.on('dragleave', () => dropZone.removeClass('active'));
        dropZone.on('drop', function(e) {
            e.preventDefault();
            dropZone.removeClass('active');
            if (e.originalEvent.dataTransfer.files.length) {
                fileInput[0].files = e.originalEvent.dataTransfer.files;
                handleFileSelect();
            }
        });
        fileInput.on('change', handleFileSelect);

        $('#btnAnalyze').on('click', analyzeFile);
        $('#btnPreview').on('click', previewImport);
        $('#btnImport').on('click', importData);
    });

    function handleFileSelect() {
        const file = $('#fileInput')[0].files[0];
        if (!file) return;
        currentFile = file;
        showAlert('Archivo seleccionado: ' + file.name, 'success');

        // Mostrar selector de hojas (simulado, ya que no podemos leer hojas desde JS)
        // En lugar de analizar desde el cliente, haremos una primera llamada para obtener las hojas.
        const formData = new FormData();
        formData.append('file', file);

        $.ajax({
            url: '{{ route("api.import.mediciones.analyze") }}',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    const sheetSelect = $('#sheetSelect');
                    sheetSelect.empty();
                    response.sheets.forEach((sheet, idx) => {
                        sheetSelect.append(`<option value="${idx}">${sheet}</option>`);
                    });
                    $('#sheetSelection').show();
                    // Guardar cabeceras de la primera hoja para el mapeo
                    headers = response.headers;
                    generateMapping(headers);
                } else {
                    showAlert(response.message || 'Error al analizar archivo', 'danger');
                }
            },
            error: function(xhr) {
                showAlert('Error al analizar archivo: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
            }
        });
    }

    function generateMapping(headers) {
        const container = $('#mappingContainer');
        container.empty();

        const fields = [
            { key: 'lote', label: 'Lote', required: true },
            { key: 'medidor', label: 'Medidor', required: true },
            // Para fechas, generaremos dinámicamente según los encabezados que parezcan fechas
        ];

        // Detectar columnas de fecha (que contengan "/" o nombres de meses)
        const dateColumns = headers.map((h, idx) => ({ idx, header: h }))
            .filter(item => item.header && (item.header.includes('/') || /enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre/i.test(item.header)));

        // Agregar campos fijos
        fields.forEach(field => {
            const div = $(`<div class="col-md-6"></div>`);
            div.append(`<label>${field.label} *</label>`);
            const select = $(`<select class="form-select" data-key="${field.key}"></select>`);
            select.append(`<option value="">-- Seleccionar --</option>`);
            headers.forEach((h, idx) => {
                select.append(`<option value="${idx}">${h}</option>`);
            });
            div.append(select);
            container.append(div);
        });

        // Agregar columnas de fecha
        const fechaDiv = $(`<div class="col-12"><hr><h6>Columnas de fechas (valores de medición)</h6></div>`);
        container.append(fechaDiv);
        dateColumns.forEach((item, idx) => {
            const div = $(`<div class="col-md-4"></div>`);
            div.append(`<label>${item.header}</label>`);
            const select = $(`<select class="form-select" data-fecha="${idx}" data-header="${item.header}"></select>`);
            select.append(`<option value="${item.idx}">${item.header}</option>`);
            div.append(select);
            container.append(div);
        });

        // Si no se detectaron fechas, mostrar un mensaje
        if (dateColumns.length === 0) {
            container.append(`<div class="col-12 alert alert-warning">No se detectaron columnas de fecha. Asegúrate de que los encabezados contengan fechas.</div>`);
        }
    }

    function analyzeFile() {
        const sheetIndex = $('#sheetSelect').val();
        if (sheetIndex === undefined) {
            showAlert('Selecciona una hoja.', 'warning');
            return;
        }

        currentSheetIndex = sheetIndex;

        const formData = new FormData();
        formData.append('file', currentFile);
        formData.append('sheet_index', sheetIndex);

        // Obtener mapeo de columnas
        const mapping = getMapping();
        formData.append('column_mapping', JSON.stringify(mapping));

        // Llamada a preview
        $.ajax({
            url: '{{ route("api.import.mediciones.preview") }}',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    previewData = response.preview;
                    renderPreview(previewData, response.errors);
                    $('#step3').show();
                    $('#step2').hide();
                } else {
                    showAlert(response.message || 'Error en la previsualización', 'danger');
                }
            },
            error: function(xhr) {
                showAlert('Error: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
            }
        });
    }

    function getMapping() {
        const mapping = {
            lote: null,
            medidor: null,
            fechas: {}
        };

        $('#mappingContainer select[data-key]').each(function() {
            const key = $(this).data('key');
            mapping[key] = $(this).val();
        });

        // Columnas de fecha
        $('#mappingContainer select[data-fecha]').each(function() {
            const idx = $(this).data('fecha');
            const header = $(this).data('header');
            mapping.fechas[$(this).val()] = header;
        });

        return mapping;
    }

    function renderPreview(preview, errors) {
        const container = $('#previewContainer');
        let html = '';

        if (errors.length > 0) {
            html += `<div class="alert alert-danger"><h6>Errores encontrados:</h6><ul>`;
            errors.forEach(err => {
                html += `<li>${err}</li>`;
            });
            html += `</ul></div>`;
        }

        if (preview.length === 0) {
            html += `<div class="alert alert-warning">No se encontraron datos válidos para importar.</div>`;
            container.html(html);
            return;
        }

        // Tabla resumen
        html += `<h6>Resumen de datos a importar</h6>`;
        html += `<table class="table table-bordered table-striped">
            <thead><tr><th>Lote</th><th>Medidor</th><th>Mediciones (fecha -> valor)</th></tr></thead><tbody>`;
        preview.forEach(item => {
            const mediciones = item.mediciones.map(m => `${m.fecha} -> ${m.valor}`).join('<br>');
            html += `<tr><td>${item.lote}</td><td>${item.medidor}</td><td>${mediciones}</td></tr>`;
        });
        html += `</tbody></table>`;

        // Guardar datos para importar
        importData = preview;

        container.html(html);
        $('#step3').show();
    }

    function importData() {
        if (importData.length === 0) {
            showAlert('No hay datos para importar.', 'warning');
            return;
        }

        $('#progressContainer').show();
        $('#btnImport').prop('disabled', true);

        $.ajax({
            url: '{{ route("api.import.mediciones.import") }}',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ data: importData }),
            beforeSend: function() {
                $('#progressBar').css('width', '30%').text('30%');
                $('#progressText').text('Enviando datos...');
            },
            success: function(response) {
                $('#progressBar').css('width', '100%').text('100%');
                $('#progressText').text('Importación completada.');
                if (response.success) {
                    showAlert(response.message, 'success');
                    if (response.errors && response.errors.length > 0) {
                        let errHtml = '<div class="alert alert-warning"><h6>Detalles de errores:</h6><ul>';
                        response.errors.forEach(e => {
                            errHtml += `<li>${e}</li>`;
                        });
                        errHtml += '</ul></div>';
                        $('#alertContainer').append(errHtml);
                    }
                    // Opcional: redirigir después de unos segundos
                    setTimeout(() => {
                        window.location.href = '{{ route("getTodasMedVista") }}';
                    }, 3000);
                } else {
                    showAlert(response.message || 'Error en la importación', 'danger');
                }
            },
            error: function(xhr) {
                showAlert('Error en la importación: ' + (xhr.responseJSON?.message || xhr.statusText), 'danger');
                $('#progressContainer').hide();
                $('#btnImport').prop('disabled', false);
            }
        });
    }

    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alertContainer').append(alertHtml);
    }
</script>
@endpush