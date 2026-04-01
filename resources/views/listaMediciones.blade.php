@extends('adminlte::page')

@section('css')
    <!--<link rel="stylesheet" href="/css/admin_custom.css">-->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/2.1.4/css/dataTables.bootstrap5.css" rel="stylesheet"></link>
    <link href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.css" rel="stylesheet"></link>
    <link href="https://cdn.datatables.net/select/1.6.0/css/select.dataTables.min.css" rel="stylesheet"></link>
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" rel="stylesheet">

    <style>
        .card {
            height: auto;
            min-height: 700px;
            margin-bottom: 20px;
        }

        .card-body {
            padding: 20px;
            overflow-y: auto;
        }

        .table-responsive {
            max-height: 700px;
            overflow-y: scroll;
        }

        .dataTables_wrapper {
            margin-top: 10px;
        }

        .dataTables_info {
            margin-bottom: 15px;
        }

        .dataTables_paginate {
            float: right;
            margin-bottom: 15px;
        }

        #resultadoMediciones {
            height: 300px; /* Altura fija */
            overflow-y: auto; /* Scroll vertical cuando el contenido sea mayor */
            border: 1px solid #ccc; /* Opcional: para visualizar mejor los bordes */
            padding: 10px; /* Opcional: agregar padding interno */
            background-color: #f9f9f9; /* Opcional: color de fondo */
        }
    </style>
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header">{{ __('Lista Completa de Mediciones') }}</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="table-responsive"> <!-- Habilita el scroll horizontal -->
                        <div>


                        <div class="d-flex flex-wrap align-items-center mb-3">
                            <!-- Contenedor del campo 'Desde' -->
                            <div class="d-flex flex-column align-items-center mr-3">
                                <label for="minDate" style="font-size: 12px;">Desde:</label>
                                <input type="date" id="minDate" name="minDate" class="form-control" style="width: 150px;">
                            </div>

                            <!-- Contenedor del campo 'Hasta' -->
                            <div class="d-flex flex-column align-items-center mr-3">
                                <label for="maxDate" style="font-size: 12px;">Hasta:</label>
                                <input type="date" id="maxDate" name="maxDate" class="form-control" style="width: 150px;">
                            </div>

                            <!-- Botón 'Borrar búsqueda' -->
                            <div class="d-flex align-items-center mr-3" style="margin-top: 22px;">
                                <button id="resetDateFilter" class="btn btn-secondary">Borrar búsqueda</button>
                            </div>

                            <!-- Botón 'Exportar CSV' -->
                            <div class="d-flex align-items-center mr-3" style="margin-top: 22px;">
                                <button class="btn btn-success">
                                    <a href="{{ route('exportarMediciones') }}" style="color: white; text-decoration: none;">Exportar CSV</a>
                                </button>
                            </div>

                            <!-- Botón 'Exportar Excel' -->
                            <div class="d-flex align-items-center mr-3" style="margin-top: 22px;">
                                <button id="exportExcel" class="btn btn-success" style="background-color: #1d7044;">
                                    Exportar Excel
                                </button>
                            </div>
                            <!-- Campo de número de días y botón 'Calcular' -->
                            <div class="d-flex flex-column align-items-center">
                                <label for="diasInput" style="font-size: 12px;">Número de días hacia atrás:</label>
                                <div class="d-flex align-items-center">
                                    <input type="number" id="diasInput" name="diasInput" value="30" class="form-control" style="width: 100px; margin-right: 10px;">
                                    <button id="btnCalcularFaltanMedir" class="btn btn-warning">Calcular</button>
                                </div>
                            </div>
                        </div>



                            
                        </div>
                        <table id="tablaTodasMediciones" class="display nowrap" cellspacing="0" width="100%">
                            <thead class="thead-dark">
                                <tr>
                                    
                                    <th>Lote</th>
                                    <th>Medidor</th>
                                    <th>Periodo</th>
                                    <th>Fecha</th>
                                    <th>Vencimiento</th>
                                    <th>Toma Ant</th>
                                    <th>Medida Ant</th>
                                    <th>Valor Medido</th>
                                    <th>Consumo</th>
                                    <th>Inspector</th>
                                    <th>Foto</th>
                                    <th>Borrar</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!---------------------modal mediciones faltantes------------------>

<!-- Modal -->
<div class="modal fade" id="resultadoModal" tabindex="-1" role="dialog" aria-labelledby="resultadoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultadoModalLabel">Resultados de Mediciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="resultadoMediciones" class="mt-3" style="max-height: 300px; overflow-y: auto;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para eliminar medicion -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Eliminar Medicion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar el registro?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmEliminar" class="btn btn-primary">Eliminar</button>
            </div>
        </div>
    </div>
</div>


<!--<script src="{{ asset('js/app.js') }}"></script>-->

<!-- Bootstrap JS -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Carga jQuery -->

<script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script> <!-- Carga jQuery UI -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>



@parent

<script type="text/javascript">
    $(document).ready(function() {
        //window.objListaMediciones.display();
            // Filtro personalizado de DataTables
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                let min = $('#minDate').val();
                let max = $('#maxDate').val();
                let fecha = data[3]; // La columna de fecha en la tabla

                if (min) min = new Date(min);
                if (max) max = new Date(max);
                let fechaTabla = new Date(fecha);

                // Comprobar si la fecha de la tabla está dentro del rango seleccionado
                if (
                    (!min && !max) || // Si no hay fechas seleccionadas, mostrar todos
                    (!min || fechaTabla >= min) && 
                    (!max || fechaTabla <= max)
                ) {
                    return true;
                }
                return false;
            }
        );

        // Cuando cambian los campos de fecha, redibuja la tabla
        $('#minDate, #maxDate').on('change', function() {
            table.draw();
        });


        $('#resetDateFilter').on('click', function() {
            // Limpiar los campos de fecha
            $('#minDate').val('');
            $('#maxDate').val('');
            
            // Redibujar la tabla sin filtros
            table.draw();
        });


        $('#exportExcel').on('click', function() {
    // 1. Obtener datos de DataTables (filtrados)
    const data = table.rows({ search: 'applied' }).data();
    const excelData = [];

    // 2. Encabezados (igual que antes)
    const headers = [
        "Lote", "Medidor", "Periodo", "Fecha", "Vencimiento",
        "Toma Ant", "Medida Ant", "Valor Medido", "Consumo", "Inspector"
    ];
    excelData.push(headers);

    // 3. Añadir datos de DataTables
    data.each(function(row) {
        excelData.push([
            row.lote,
            row.medidor,
            row.periodo,
            row.fecha,
            row.vencimiento,
            row.tomaant,
            row.medidaant,
            row.valormedido,
            row.consumo,
            row.inspector
        ]);
    });

    // 4. Obtener lotes faltantes vía AJAX (síncrono para evitar problemas de timing)
    let lotesFaltantes = [];
    $.ajax({
        url: "{{ route('lotes.faltantes') }}", // Ruta al nuevo endpoint
        method: 'GET',
        async: false, // Importante: esperar a que termine la petición
        success: function(response) {
            lotesFaltantes = response;
        },
        error: function(xhr) {
            console.error('Error al obtener lotes faltantes:', xhr.responseText);
        }
    });

    // 5. Añadir lotes faltantes al Excel (si hay)
    if (lotesFaltantes.length > 0) {
        lotesFaltantes.forEach(function(lote) {
            excelData.push([
                lote.lote,
                lote.medidor,
                lote.periodo,
                lote.fecha,
                lote.vencimiento,
                lote.tomaant,
                lote.medidaant,
                lote.valormedido,
                lote.consumo,
                lote.inspector
            ]);
        });
    }

    // 6. Ordenar todas las filas por "Lote" (opcional, pero recomendado)
    excelData.sort((a, b) => {
        // Saltar el header (índice 0)
        if (a[0] === "Lote") return -1;
        if (b[0] === "Lote") return 1;
        return parseInt(a[0]) - parseInt(b[0]); // Orden numérico por lote
    });

    // 7. Generar el Excel
    const workbook = XLSX.utils.book_new();
    const worksheet = XLSX.utils.aoa_to_sheet(excelData);
    XLSX.utils.book_append_sheet(workbook, worksheet, "Mediciones");
    XLSX.writeFile(workbook, 'mediciones_con_faltantes.xlsx');
});


       const table = $('#tablaTodasMediciones').DataTable({
            "scrollY": "400px", // Altura máxima fija con scroll vertical
            "scrollX": true,    // Activa el scroll horizontal
            "scrollCollapse": true, // Permite que el scroll se colapse si la tabla es pequeña
            "processing": true,
            "serverSide": false,
            "columnDefs": [
                {
                    "targets": 0, // Índice de la columna "Lote"
                    "type": "num" // Define el tipo de ordenación como numérico
                }
            ],
            language: {
                "decimal": "",
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ registros",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": activar para ordenar la columna de manera descendente"
                }
            },
            "ajax": {
                "url": "{{ route('getTodasMed') }}", // Ruta al controlador que devuelve los datos
                "type": "GET"
            },
            "columns": [
                { "data": "lote" },
                { "data": "medidor" },
                { "data": "periodo" },
                { "data": "fecha" },
                { "data": "vencimiento" },
                { "data": "tomaant" },
                { "data": "medidaant" },
                { "data": "valormedido" },
                { "data": "consumo" },
                { "data": "inspector" },
                {
                    "data": "foto",
                    "render": function(data, type, row) {
                        if (data === "Sin foto") {
                            return `<a>Sin Foto</a>`;
                        } else {
                            const tieneExtension = data.toLowerCase().endsWith('.png');
                            const rutaImagen = tieneExtension
                                ? `images/${data}`
                                : `images/${data}.png`;
                            return `<a class="fotoMedidor" target="_blank" href="{{ asset('${rutaImagen}') }}">Foto</a>`;
                        }
                    }
                },
                {
                    "data": "id",
                    "render": function(data, type, row) {
                        return `<button class="btn btn-danger btnBorrarMedicion" value="${data}">
                                    <i class="bi bi-trash"></i>
                                </button>`;
                    }
                },
                {
                    "data": "id",
                    "render": function(data, type, row) {
                        return `<a href="{{ route('editarMedicion') }}?id=${data}" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>`;
                    }
                }
            ],
            dom: 'Bfrtip',
            buttons: [
                'excel'
            ],
            "paging": true, // Activa la paginación si es necesario
            "autoWidth": false // Desactiva el ajuste automático de ancho
        });

        table.on('click', 'tbody tr', function (e) {
            e.currentTarget.classList.toggle('selected');
        });

        $('#minDate, #maxDate').on('change', function() {
        table.draw(); // Redibujar la tabla con los nuevos filtros aplicados
        });

        
        $('#btnCalcularFaltanMedir').on('click', function() {
            const dias = $('#diasInput').val(); // Obtener valor del input de días

            // Realizar la petición al controlador
           $.ajax({
                url: "{{ route('obtenerMedicionesFaltantes') }}",
                method: 'GET',
                data: { dias: dias },
                success: function(response) {
                    const resultado = $('#resultadoMediciones');
                    resultado.empty(); // Limpiar el contenido anterior
            
                    // Verificar si hay lotes sin mediciones recientes
                    const lotesSinMediciones = response.mediciones_faltantes;
            
                    // Obtener todas las claves del objeto (los ids de los lotes)
                    let lotesIds = Object.keys(lotesSinMediciones);
            
                    if (lotesIds.length > 0) {
                        // Ordenar los IDs de los lotes
                        lotesIds.sort(function(a, b) {
                            // Extraer el número del lote, eliminando cualquier sufijo no numérico
                            const numA = parseInt(a, 10);
                            const numB = parseInt(b, 10);
            
                            // Comparar los números
                            return numA - numB;
                        });
            
                        let list = '<ul>';
            
                        // Iterar sobre las claves ordenadas y mostrar los valores
                        lotesIds.forEach(function(loteId) {
                            const loteMedicion = lotesSinMediciones[loteId];
                            list += `<li>Lote ${loteMedicion} no tiene mediciones recientes</li>`;
                        });
            
                        list += '</ul>';
                        resultado.html(list);
                    } else {
                        resultado.html('<p>Todos los lotes tienen mediciones recientes.</p>');
                    }
            
                    // Mostrar el modal con los resultados
                    $('#resultadoModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });

        });


        $('#resultadoModal .close, #resultadoModal .btn-secondary').on('click', function() {
            $('#resultadoModal').modal('hide');
        });


        // Evento para abrir el modal de confirmación
        $(document).on('click', '.btnBorrarMedicion', function() {
            console.log('sksksks');
            var medicionId = $(this).val();
            $('#confirmDeleteModal').data('medicionId', medicionId);
            $('#confirmDeleteModal').modal('show');
        });

        // Evento para confirmar la eliminación
        $('#confirmEliminar').on('click', function() {
            var medicionId = $('#confirmDeleteModal').data('medicionId');
            $.ajax({
                url: "{{ route('postBorrarMedicion') }}",
                type: "POST",
                data: {
                    id: medicionId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $('#confirmDeleteModal').modal('hide');
                    table.ajax.reload();
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        });

    });
</script>
@endsection
