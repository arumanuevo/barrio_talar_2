@extends('adminlte::page')

@section('title', 'Listado de Medidores')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Listado de Medidores con Contraseñas</h1>
        <a href="{{ route('medidores.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Medidor y Usuario
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Medidores registrados</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="medidores-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lote</th>
                            <th>Email Propietario</th>
                            <th>Número de Medidor</th>
                            <th>Contraseña</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($medidores as $medidor)
                            <tr>
                                <td>{{ $medidor->id }}</td>
                                <td>{{ $medidor->lote }}</td>
                                <td>
                                    @if($medidor->user)
                                        {{ $medidor->user->email }}
                                    @else
                                        <span class="badge badge-warning">Sin usuario asignado</span>
                                    @endif
                                </td>
                                <td>{{ $medidor->numero_medidor }}</td>
                                <td>{{ $medidor->password }}</td>
                                <td class="text-nowrap">
                                    <button class="btn btn-sm btn-info copy-password"
                                            data-password="{{ $medidor->password }}"
                                            title="Copiar contraseña">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <a href="{{ route('medidores.edit', $medidor->id) }}"
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0.5em;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .text-nowrap {
            white-space: nowrap;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            var table = $('#medidores-table').DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [5] }
                ],
                "initComplete": function() {
                    $('.dataTables_filter input').attr("placeholder", "Buscar...");
                    $('.dataTables_filter input').addClass('form-control form-control-sm');
                }
            });

            // Copiar contraseña
            $(document).on('click', '.copy-password', function() {
                var password = $(this).data('password');
                navigator.clipboard.writeText(password).then(function() {
                    toastr.success('Contraseña copiada al portapapeles');
                }, function(err) {
                    toastr.error('Error al copiar la contraseña');
                });
            });

            // Cerrar alertas después de 5 segundos
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
@stop

