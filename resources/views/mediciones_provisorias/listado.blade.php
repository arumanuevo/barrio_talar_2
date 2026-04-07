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
                                                    <a href="#" data-toggle="modal" data-target="#modalFoto{{ $medicion->id }}">
                                                        <img src="{{ asset('images/' . basename($medicion->foto)) }}" alt="Foto de medición" style="max-width: 100px; max-height: 100px;">
                                                    </a>
                                                    <!-- Modal para mostrar la foto en grande -->
                                                    <div class="modal fade" id="modalFoto{{ $medicion->id }}" tabindex="-1" role="dialog" aria-labelledby="modalFotoLabel{{ $medicion->id }}" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalFotoLabel{{ $medicion->id }}">Foto de Medición - Lote {{ $medicion->lote }}</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <img src="{{ asset('images/' . basename($medicion->foto)) }}" alt="Foto de medición" style="max-width: 100%;">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    Sin foto
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
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
        });
    </script>
@stop