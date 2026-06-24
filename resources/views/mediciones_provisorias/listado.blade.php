@extends('adminlte::page')

@section('title', 'Listado de Mediciones Provisorias')

@section('content_header')
    <h1>Listado de Mediciones Provisorias</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Mediciones Provisorias</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaMediciones" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Lote</th>
                                        <th>Medidor</th>
                                        <th>Consumo</th>
                                        <th>Fecha Medición</th>
                                        <th>Foto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mediciones as $medicion)
                                        <tr>
                                            <td>{{ $medicion->id }}</td>
                                            <td>{{ $medicion->lote }}</td>
                                            <td>{{ $medicion->medidor }}</td>
                                            <td>{{ $medicion->consumo }}</td>
                                            <td>{{ $medicion->fecha_medicion }}</td>
                                            <td>
                                                @if($medicion->foto && $medicion->foto != 'N/A')
                                                    <a href="#" class="btn btn-sm btn-primary btn-ver-foto" 
                                                       data-foto="{{ asset('images/' . basename($medicion->foto)) }}"
                                                       data-lote="{{ $medicion->lote }}"
                                                       data-toggle="modal" 
                                                       data-target="#modalFotoUnico">
                                                        <i class="fas fa-eye"></i> Ver foto
                                                    </a>
                                                @else
                                                    <span class="text-muted">Sin foto</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ÚNICO para todas las fotos (se carga solo cuando se abre) -->
    <div class="modal fade" id="modalFotoUnico" tabindex="-1" role="dialog" aria-labelledby="modalFotoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFotoLabel">Foto de Medición</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="fotoModalImagen" src="" alt="Foto de medición" style="max-width: 100%;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tablaMediciones').DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                }
            });

            // Cuando se hace clic en "Ver foto", se carga la imagen en el modal único
            $('.btn-ver-foto').on('click', function() {
                var fotoUrl = $(this).data('foto');
                var lote = $(this).data('lote');
                $('#fotoModalImagen').attr('src', fotoUrl);
                $('#modalFotoLabel').text('Foto de Medición - Lote ' + lote);
            });

            // Limpiar la imagen al cerrar el modal (libera memoria)
            $('#modalFotoUnico').on('hidden.bs.modal', function() {
                $('#fotoModalImagen').attr('src', '');
            });
        });
    </script>
@stop