@extends('adminlte::page')
@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('css/css-loader.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Cálculo Consumos') }}</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="loader loader-default" id="spin" data-text="Consultando..."></div>
                    <div id='divCartelAlerta'></div>
                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label>Codigo de Arqueo</label>
                                <input type="text" min="1" name="facturaAysa" id="facturaAysa" class="form-control" title="Aviso Importante: Si el codigo ya existe se sobreescribira la factura" required>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label>Consumo en boca de pozo</label>
                                <input type="number" title="Ingrese medicion en boca de pozo si es que cuenta con ella" min="0" name="bocapozo" id="bocapozo" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label>Valor x M³</label>
                                <input type="number" title="El valor por metro cubico basico" min="1" name="valorxMetro" id="valorxMetro" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label>Fijo Variable</label>
                                <input type="number" title="Ingrese el valor de compensacion, se sumara por igual al consumo de cada lote individual" min="0" name="fijovariable" id="fijovariable" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label>Vencimiento</label>
                                <input type="date" name="vencimientoAysa" id="vencimientoAysa" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label style="font-size:13px;">Consumo Total Medido</label>
                                <input type="number" min="1" name="consumototalmedido" id="consumototalmedido" disabled class="form-control" required>
                        </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label style="font-size:13px;">Diferencial</label>
                                <input type="number" min="1" name="diferencial" id="diferencial" class="form-control" disabled required>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label style="font-size:13px;">Cant. Lotes</label>
                                <input type="number" min="1" max="571" name="cantLotes" id="cantLotes" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label style="font-size:13px;">Monto a Pagar</label>
                                <input type="number" min="1" name="montoAPagar" id="montoAPagar" class="form-control" disabled required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label>Desde</label>
                                <input type="date" name="diaDesde" id="diaDesde" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" name="diaHasta" id="diaHasta" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex justify-content-center">
                            <button id="btnCalcular" data-toggle="modal" data-target="#confirmationModal" class="btn btn-success save-data">
                                <i class="bi bi-calculator"></i> Calcular
                            </button>
                            <div style="padding-left:4px">
                                <button id="btnCSV" class="btn btn-success save-data">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Descargar CSV
                                </button>
                                <button id="btnExcel" class="btn btn-success save-data">
                                    <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                                </button>
                                <button id="btnFacturacion" disabled class="btn btn-danger save-data">
                                    <i class="bi bi-receipt"></i> Emitir Facturas
                                </button>
                            </div>
                        </div>
                    </div>
                    <table id="tblMedicionesDesdeHasta" class="table display table-striped table-bordered" cellspacing="0" width="100%">
                        <thead class="thead-dark">
                            <tr class="text-center">
                                <th>Lote</th>
                                <th>Medidor</th>
                                <th>Periodo (dias)</th>
                                <th>Fecha Medición</th>
                                <th>Vencimiento</th>
                                <th>Fecha Anterior</th>
                                <th>Medida Anterior</th>
                                <th>Valor Medido</th>
                                <th>Consumo</th>
                                <th>Sumario</th>
                                <th>Monto-Periodo</th>
                                <th>Inspector</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody id="tblBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmación -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Atención</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                La operación puede demorar varios minutos dependiendo de la cantidad de mediciones consultadas.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmButton" data-dismiss="modal">Confirmar</button>
            </div>
        </div>
    </div>

<!-- Modal Éxito -->
<div class="modal fade" id="modalExito" tabindex="-1" aria-labelledby="confirmModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Aviso Importante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Transacción exitosa! El almacenamiento se realizó.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
@vite(['resources/js/app.js'])
@parent
<script type="text/javascript">
$(document).ready(function() {
    var table = $('#tblMedicionesDesdeHasta').DataTable({
        autoWidth: false,
        scrollX: true,
        responsive: true,
        pageLength: 10,
        deferRender: true,
        language: {
    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
}

    });

    // Función para exportar a CSV (SOLO datos de DataTable)
    function exportTableToCSV(filename) {
        var csv = [];
        var data = table.rows({ search: 'applied' }).data();
        var headers = [];

        // Obtener encabezados
        $('#tblMedicionesDesdeHasta thead th').each(function() {
            headers.push($(this).text().trim());
        });
        csv.push(headers.join(","));

        // Datos de la tabla
        data.each(function(row) {
            var rowData = [];
            headers.forEach(function(header, index) {
                var cellData = row[index];
                cellData = cellData.replace(/"/g, '""');
                if (cellData.includes(',') || cellData.includes('"') || cellData.includes('\n')) {
                    cellData = '"' + cellData + '"';
                }
                rowData.push(cellData);
            });
            csv.push(rowData.join(","));
        });

        // Descargar CSV
        var csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
        var downloadLink = document.createElement("a");
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
        downloadLink.click();
    }

    // Función para exportar a Excel (CON lotes faltantes)
    function exportTableToExcel() {
    if (typeof XLSX === 'undefined') {
        alert("Error: La librería XLSX no está cargada. Recarga la página e intenta nuevamente.");
        return;
    }

    const data = table.rows({ search: 'applied' }).data();
    const excelData = [];

    // Encabezados
    const headers = [
        "Lote", "Medidor", "Periodo (dias)", "Fecha Medición", "Vencimiento",
        "Fecha Anterior", "Medida Anterior", "Valor Medido", "Consumo",
        "Sumario", "Monto-Periodo", "Inspector", "Foto"
    ];
    excelData.push(headers);

    // Datos de DataTable (acceso seguro por índice)
    data.each(function(row) {
        excelData.push([
            row[0] || "",          // Lote
            row[1] || "N/A",      // Medidor
            row[2] || "0",        // Periodo (dias)
            row[3] || "N/A",      // Fecha Medición
            row[4] || "N/A",      // Vencimiento
            row[5] || "N/A",      // Fecha Anterior
            row[6] || "0",        // Medida Anterior
            row[7] || "0",        // Valor Medido
            row[8] || "0",        // Consumo
            row[9] || "0",        // Sumario
            row[10] || "0",       // Monto-Periodo
            row[11] || "N/A",     // Inspector
            row[12] || "Sin foto" // Foto
        ]);
    });

    // Obtener lotes faltantes
    let lotesFaltantes = [];
    $.ajax({
        url: "{{ route('lotes.faltantes') }}",
        method: 'GET',
        async: false,
        success: function(response) {
            lotesFaltantes = response;
        },
        error: function(xhr) {
            console.error('Error al obtener lotes faltantes:', xhr.responseText);
            alert("Error al obtener lotes faltantes. Verifica la consola para más detalles.");
        }
    });

    // Añadir lotes faltantes (con fechas formateadas)
    if (lotesFaltantes.length > 0) {
        let fechaMedicion = $("#diaHasta").val() || "N/A";
        let vencimiento = $("#vencimientoAysa").val() || "N/A";

        lotesFaltantes.forEach(function(lote) {
            excelData.push([
                lote.lote || "",
                lote.medidor || "N/A",  // <-- Aquí se usa el medidor de la base de datos
                "0",
                fechaMedicion,
                vencimiento,
                "Sin fecha anterior",
                "0", "0", "0", "0", "0", "N/A", "Sin foto"
            ]);
        });
    }

    // Ordenar por lote (ignorando el header)
    excelData.sort((a, b) => {
        if (a[0] === "Lote") return -1;
        if (b[0] === "Lote") return 1;
        const numA = parseInt(a[0]) || 0;
        const numB = parseInt(b[0]) || 0;
        return numA - numB;
    });

    // Generar Excel
    try {
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.aoa_to_sheet(excelData);
        XLSX.utils.book_append_sheet(workbook, worksheet, "Consumos");
        XLSX.writeFile(workbook, 'consumos_con_faltantes.xlsx');
    } catch (e) {
        console.error("Error al generar el Excel:", e);
        alert("Error al generar el Excel. Verifica la consola para más detalles.");
    }
}


    // Eventos de los botones
    $('#btnCSV').on('click', function() {
        exportTableToCSV('consumos_exportados.csv');
    });

    $(document).off('click', '#btnExcel').on('click', '#btnExcel', function(e) {
        e.preventDefault();
        exportTableToExcel();
    });



    displayCalculos();
    objGuardarFacturacion();
});
</script>
@stop
