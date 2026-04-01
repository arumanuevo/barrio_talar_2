@extends('adminlte::page')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel='stylesheet' type='text/css' media='screen' href="{{ asset('../js/assets/style/webcam-demo.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link href="{{ asset('css/css-loader.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header">{{ __('Lista Completa de Usuarios') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="lotesTable" class="table table-striped table-bordered" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Borrar</th>
                                    <th>Editar</th>
                                    <th>Nombre</th>
                                    <th>Lote</th>
                                    <th>Medidor</th>
                                    <th>Email</th>
                                    <th>Telefono</th>
                                    <th>Ocupacion</th>
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
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Función para ordenamiento natural
    function naturalSort(a, b) {
        // Función auxiliar para extraer partes numéricas
        function chunkify(t) {
            let tz = [], x = 0, y = -1, n = 0, i, j;

            while (i = (j = t.charAt(x++)).charCodeAt(0)) {
                let m = (i == 46 || (i >= 48 && i <= 57));
                if (m !== n) {
                    tz[++y] = "";
                    n = m;
                }
                tz[y] += j;
            }
            return tz;
        }

        let aa = chunkify(a);
        let bb = chunkify(b);

        for (let x = 0; aa[x] && bb[x]; x++) {
            if (aa[x] !== bb[x]) {
                let aNum = parseFloat(aa[x]);
                let bNum = parseFloat(bb[x]);

                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return aNum - bNum;
                } else {
                    return aa[x] > bb[x] ? 1 : -1;
                }
            }
        }

        return aa.length - bb.length;
    }

    // Aplicar el ordenamiento natural a DataTables
    $.fn.dataTable.ext.order['natural-sort'] = function(settings, col) {
        return this.api().column(col, {order: 'index'}).nodes().map(function(td, i) {
            return {
                value: $(td).text(),
                display: $(td).text()
            };
        });
    };

    // Sobrescribir la función de comparación para usar nuestro orden natural
    $.fn.dataTable.ext.order['natural-sort-asc'] = function(a, b) {
        return naturalSort(a.value, b.value);
    };

    $.fn.dataTable.ext.order['natural-sort-desc'] = function(a, b) {
        return naturalSort(b.value, a.value);
    };

    $('#lotesTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('lotes.data') }}",
            dataSrc: 'data'
        },
        columns: [
            {
                data: 'id',
                render: function(data, type, row) {
                    return `<a href="#" class="delete-lote" data-lote-id="${data}"><i class="bi bi-trash"></i></a>`;
                },
                orderable: false
            },
            {
                data: 'id',
                render: function(data, type, row) {
                    return `<a href="${window.location.origin}/editarLoteMedidor/${data}"><i class="bi bi-pencil"></i></a>`;
                },
                orderable: false
            },
            { data: 'name' },
            {
                data: 'lote',
                type: 'natural-sort' // Usar nuestro tipo de orden natural
            },
            { data: 'medidor' },
            { data: 'email' },
            { data: 'telefono' },
            { data: 'ocupacion' }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        responsive: true
    });
});


</script>
@stop
