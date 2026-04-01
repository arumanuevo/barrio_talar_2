@extends('adminlte::page')

@section('title', 'Verificación de Fotos Faltantes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-search mr-2"></i>Verificación de Fotografías Faltantes</h1>
        <a href="#" class="btn btn-purple btn-sm" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Actualizar
        </a>
    </div>
@stop

@section('content')
    <div class="card card-purple card-outline">
        <div class="card-header bg-white">
            <h3 class="card-title">
                <i class="fas fa-list-ol mr-2"></i>
                Resultados: 
                <span class="badge badge-danger">{{ $contadorFaltantes }}</span> faltantes de 
                <span class="badge badge-purple">{{ $totalRegistros }}</span> registros
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        
        <div class="card-body p-0">
            @if($contadorFaltantes > 0)
                <div class="table-responsive p-2">
                    <table id="fotosTable" class="table table-hover table-striped table-sm compact">
                        <thead class="bg-lightblue">
                            <tr>
                                <th class="col-10">Nombre del Archivo</th>
                                <th class="col-2 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($faltantes as $foto)
                            <tr>
                                <td class="text-monospace small">{{ $foto }}</td>
                                <td class="text-center">
                                    <span class="badge badge-danger">
                                        <i class="fas fa-exclamation-circle fa-fw"></i> FALTANTE
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-success m-4">
                    <h4 class="mb-0"><i class="fas fa-check-circle mr-2"></i>¡Perfecto! Todas las fotos están presentes</h4>
                    <hr>
                    <p class="mb-0">
                        <i class="fas fa-database mr-2"></i>Registros verificados: {{ $totalRegistros }}
                    </p>
                </div>
            @endif
        </div>
        
        @if($contadorFaltantes > 0)
        <div class="card-footer bg-white">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-2"></i>
                        Última verificación: {{ now()->format('d/m/Y H:i:s') }}
                    </small>
                </div>
                <div class="col-md-6 text-right">
                    <small class="text-muted">
                        Mostrando {{ $contadorFaltantes }} registros
                    </small>
                </div>
            </div>
        </div>
        @endif
    </div>
@stop

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.13.6/datatables.min.css"/>
    <style>
        .text-monospace { font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; }
        .card-purple { border-color: #6f42c1; }
        .card-purple .card-header { border-bottom-color: #6f42c1; }
        .bg-lightblue { background-color: #e9f2ff; }
        .compact thead th { padding-top: 0.4rem; padding-bottom: 0.4rem; }
        #fotosTable_wrapper .dataTables_length select { 
            border-radius: 4px; 
            padding: 0.2rem 1.5rem 0.2rem 0.5rem;
        }
    </style>
@stop

@section('js')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.13.6/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#fotosTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[0, 'asc']],
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                columnDefs: [
                    { orderable: false, targets: 1 },
                    { className: 'dt-body-center', targets: 1 }
                ]
            });
            
            $('.dataTables_filter input').attr('placeholder', 'Buscar...');
        });
    </script>
@stop