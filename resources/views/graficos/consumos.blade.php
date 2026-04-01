@extends('adminlte::page')

@section('title', 'Gráfico de Consumos por Lote')

@section('content_header')
    <h1>Evolución de Consumos por Lote</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Seleccione un lote para ver su evolución de consumos</h3>
                <div>
                    <button id="detectarAnomaliasBtn" class="btn btn-warning mr-2">
                        <i class="fas fa-exclamation-triangle"></i> Detectar Anomalías
                    </button>
                    <!--<div class="btn-group" id="exportButtons" style="display: none;">
                        <button id="exportExcel" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                        <button id="exportPDF" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </button>
                    </div>-->
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="loteSelect">Seleccionar Lote</label>
                        <select id="loteSelect" class="form-control">
                            <option value="">Seleccione un lote...</option>
                            @foreach($lotes as $lote)
                                <option value="{{ $lote }}">{{ $lote }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="periodoSelect">Filtrar por Periodo</label>
                        <select id="periodoSelect" class="form-control" disabled>
                            <option value="">Todos los periodos</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pestañas para organizar el contenido -->
            <div class="mt-3">
                <ul class="nav nav-tabs" id="consumoTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="grafico-tab" data-toggle="tab" href="#grafico" role="tab" aria-controls="grafico" aria-selected="true">
                            <i class="fas fa-chart-line"></i> Gráfico
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="anomalias-tab" data-toggle="tab" href="#anomalias" role="tab" aria-controls="anomalias" aria-selected="false">
                            <i class="fas fa-exclamation-triangle"></i> Anomalías
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="consumoTabsContent">
                    <!-- Pestaña del gráfico -->
                    <div class="tab-pane fade show active" id="grafico" role="tabpanel" aria-labelledby="grafico-tab">
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="chart-container" style="position: relative; height:400px; width:100%">
                                    <canvas id="consumoChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pestaña de anomalías -->
                    <div class="tab-pane fade" id="anomalias" role="tabpanel" aria-labelledby="anomalias-tab">
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Lotes con Consumos Anómalos</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="anomaliasTable" class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Lote</th>
                                                        <th>Promedio</th>
                                                        <th>Desviación Estándar</th>
                                                        <th>Fecha Anomalía</th>
                                                        <th>Consumo</th>
                                                        <th>Diferencia</th>
                                                        <th>Porcentaje</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Los datos se cargarán mediante DataTables -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .chart-container {
            margin-top: 20px;
            min-height: 400px;
        }
        .anomalia-highlight {
            background-color: rgba(255, 0, 0, 0.2);
            font-weight: bold;
        }
        .nav-tabs {
            margin-bottom: 15px;
        }
        .tab-content {
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 15px;
            border-radius: 0 0 0.25rem 0.25rem;
        }
        #exportButtons {
            margin-top: -5px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- CSS para DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- CSS para los botones de exportación -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- JS para DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- JS para exportación -->
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script>
        $(document).ready(function() {
            // Variables globales
            let consumoChart = null;
            let periodosDisponibles = [];
            let datosAnomalias = [];
            let anomaliasTable = null;

            // Inicializar el gráfico vacío
            function initChart() {
                const ctx = document.getElementById('consumoChart').getContext('2d');

                consumoChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: 'Consumo (m³)',
                            data: [],
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            fill: true,
                            pointBackgroundColor: function(context) {
                                if (datosAnomalias.length > 0) {
                                    const fecha = context.dataIndex;
                                    const lote = $('#loteSelect').val();

                                    if (datosAnomalias[lote]) {
                                        const anomalia = datosAnomalias[lote].anomalias.find(a =>
                                            a.fecha === context.chart.data.labels[fecha]
                                        );
                                        if (anomalia) {
                                            return 'rgba(255, 0, 0, 1)';
                                        }
                                    }
                                }
                                return 'rgba(54, 162, 235, 1)';
                            },
                            pointRadius: function(context) {
                                if (datosAnomalias.length > 0) {
                                    const fecha = context.dataIndex;
                                    const lote = $('#loteSelect').val();

                                    if (datosAnomalias[lote]) {
                                        const anomalia = datosAnomalias[lote].anomalias.find(a =>
                                            a.fecha === context.chart.data.labels[fecha]
                                        );
                                        if (anomalia) {
                                            return 7;
                                        }
                                    }
                                }
                                return 3;
                            }
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Consumo (m³)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Fecha'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y + ' m³';
                                    },
                                    afterLabel: function(context) {
                                        if (datosAnomalias.length > 0) {
                                            const fecha = context.dataIndex;
                                            const lote = $('#loteSelect').val();

                                            if (datosAnomalias[lote]) {
                                                const anomalia = datosAnomalias[lote].anomalias.find(a =>
                                                    a.fecha === context.chart.data.labels[fecha]
                                                );
                                                if (anomalia) {
                                                    return '¡Anomalía detectada!';
                                                }
                                            }
                                        }
                                        return '';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Función para actualizar el gráfico con nuevos datos
            function updateChart(fechas, consumos) {
                consumoChart.data.labels = fechas;
                consumoChart.data.datasets[0].data = consumos;
                consumoChart.update();
            }

            // Inicializar el gráfico al cargar la página
            initChart();

            // Inicializar DataTable para anomalías
            function initAnomaliasTable() {
                if (anomaliasTable) {
                    anomaliasTable.destroy();
                }

                anomaliasTable = $('#anomaliasTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                    },
                    "columnDefs": [
                        {
                            "targets": [5], // Columna de diferencia
                            "render": function(data, type, row) {
                                if (type === 'display') {
                                    const value = parseFloat(data);
                                    const color = value > 0 ? 'text-danger' : 'text-success';
                                    const sign = value > 0 ? '+' : '';
                                    return `<span class="${color}">${sign}${value.toFixed(2)} m³</span>`;
                                }
                                return data;
                            }
                        },
                        {
                            "targets": [6], // Columna de porcentaje
                            "render": function(data, type, row) {
                                if (type === 'display') {
                                    const value = parseFloat(data);
                                    const color = Math.abs(value) > 100 ? 'text-danger' : 'text-warning';
                                    return `<span class="${color}">${value.toFixed(2)}%</span>`;
                                }
                                return data;
                            }
                        }
                    ],
                    "order": [[6, 'desc']], // Ordenar por porcentaje descendente
                    "dom": 'Bfrtip',
                    "buttons": [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fas fa-file-excel"></i> Excel',
                            className: 'btn btn-success',
                            title: 'Consumos_Anómalos_' + new Date().toISOString().slice(0, 10),
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            className: 'btn btn-danger',
                            title: 'Consumos_Anómalos_' + new Date().toISOString().slice(0, 10),
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            },
                            customize: function(doc) {
                                doc.content[1].table.widths = ['10%', '10%', '15%', '15%', '10%', '15%', '15%'];
                                doc.styles.tableHeader = {
                                    fillColor: '#343a40',
                                    color: '#ffffff',
                                    alignment: 'center'
                                };
                            }
                        }
                    ]
                });
            }

            // Manejar el cambio en la selección de lote
            $('#loteSelect').change(function() {
                const lote = $(this).val();

                if (lote) {
                    // Habilitar el selector de periodo
                    $('#periodoSelect').prop('disabled', false);

                    // Obtener datos del consumo para este lote
                    $.ajax({
                        url: "{{ route('datos.consumo') }}",
                        method: 'GET',
                        data: { lote: lote },
                        success: function(response) {
                            // Actualizar el gráfico
                            updateChart(response.fechas, response.consumos);

                            // Actualizar los periodos disponibles
                            periodosDisponibles = response.periodos;
                            updatePeriodoSelect(periodosDisponibles);

                            // Resaltar anomalías si existen
                            if (datosAnomalias[lote]) {
                                resaltarAnomalias(lote);
                            }
                        },
                        error: function(xhr) {
                            console.error('Error al obtener datos:', xhr.responseText);
                            toastr.error('Error al cargar los datos del lote seleccionado');
                        }
                    });
                } else {
                    // Limpiar el gráfico y deshabilitar el selector de periodo
                    consumoChart.data.labels = [];
                    consumoChart.data.datasets[0].data = [];
                    consumoChart.update();

                    $('#periodoSelect').prop('disabled', true);
                    $('#periodoSelect').html('<option value="">Todos los periodos</option>');
                }
            });

            // Manejar el cambio en la selección de periodo
            $('#periodoSelect').change(function() {
                const lote = $('#loteSelect').val();
                const periodo = $(this).val();

                if (lote) {
                    $.ajax({
                        url: "{{ route('datos.consumo') }}",
                        method: 'GET',
                        data: {
                            lote: lote,
                            periodo: periodo
                        },
                        success: function(response) {
                            updateChart(response.fechas, response.consumos);

                            // Resaltar anomalías si existen
                            if (datosAnomalias[lote]) {
                                resaltarAnomalias(lote);
                            }
                        },
                        error: function(xhr) {
                            console.error('Error al obtener datos:', xhr.responseText);
                            toastr.error('Error al filtrar por periodo');
                        }
                    });
                }
            });

            // Función para actualizar el selector de periodos
            function updatePeriodoSelect(periodos) {
                const select = $('#periodoSelect');
                select.html('<option value="">Todos los periodos</option>');

                periodos.forEach(function(periodo) {
                    select.append(`<option value="${periodo}">${periodo}</option>`);
                });
            }

            // Función para resaltar anomalías en el gráfico
            function resaltarAnomalias(lote) {
                consumoChart.update();
            }

            // Botón para detectar anomalías
            $('#detectarAnomaliasBtn').click(function() {
                $.ajax({
                    url: "{{ route('detectar.anomalias') }}",
                    method: 'GET',
                    beforeSend: function() {
                        $(this).prop('disabled', true);
                        $(this).html('<i class="fas fa-spinner fa-spin"></i> Analizando...');
                        $('#exportButtons').hide();
                    },
                    success: function(response) {
                        // Mostrar resultados
                        if (Object.keys(response).length > 0) {
                            datosAnomalias = response;

                            // Inicializar DataTable
                            initAnomaliasTable();

                            // Limpiar datos existentes
                            anomaliasTable.clear().draw();

                            // Agregar nuevos datos
                            for (const [lote, data] of Object.entries(response)) {
                                data.anomalias.forEach(anomalia => {
                                    const porcentaje = ((anomalia.consumo - data.promedio) / data.promedio * 100);

                                    anomaliasTable.row.add([
                                        lote,
                                        data.promedio.toFixed(2) + ' m³',
                                        data.desviacion_estandar.toFixed(2),
                                        anomalia.fecha,
                                        anomalia.consumo + ' m³',
                                        anomalia.diferencia > 0 ? '+' + anomalia.diferencia.toFixed(2) + ' m³' : anomalia.diferencia.toFixed(2) + ' m³',
                                        porcentaje.toFixed(2) + '%'
                                    ]);
                                });
                            }

                            // Mostrar la pestaña de anomalías
                            $('#anomalias-tab').tab('show');

                            // Mostrar botones de exportación
                            $('#exportButtons').show();

                            // Si hay un lote seleccionado, resaltar sus anomalías
                            const loteSeleccionado = $('#loteSelect').val();
                            if (loteSeleccionado && response[loteSeleccionado]) {
                                resaltarAnomalias(loteSeleccionado);
                            }
                        } else {
                            toastr.info('No se encontraron consumos anómalos');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al detectar anomalías:', xhr.responseText);
                        toastr.error('Error al detectar anomalías');
                    },
                    complete: function() {
                        $(this).prop('disabled', false);
                        $(this).html('<i class="fas fa-exclamation-triangle"></i> Detectar Anomalías');
                    }
                });
            });

            // Inicializar DataTable al cargar la página
            initAnomaliasTable();

            // Inicializar pestañas
            $('#consumoTabs a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });
    </script>
@stop



