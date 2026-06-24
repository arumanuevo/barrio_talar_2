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
                                                       data-foto="{{ asset($medicion->foto) }}" 
                                                       data-lote="{{ $medicion->lote }}"
                                                       data-toggle="modal" 
                                                       data-target="#modalFoto">
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

    <!-- Modal único para todas las fotos -->
    <div class="modal fade" id="modalFoto" tabindex="-1" role="dialog" aria-labelledby="modalFotoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFotoLabel">Foto de Medición</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="fotoModalImg" src="" alt="Foto de medición" style="max-width: 100%;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
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

            // Al hacer clic en el botón, cargar la imagen en el modal
            $('.btn-ver-foto').on('click', function() {
                var fotoUrl = $(this).data('foto');
                var lote = $(this).data('lote');
                $('#fotoModalImg').attr('src', fotoUrl);
                $('#modalFotoLabel').text('Foto de Medición - Lote ' + lote);
            });

            // Limpiar la imagen al cerrar el modal para liberar memoria
            $('#modalFoto').on('hidden.bs.modal', function() {
                $('#fotoModalImg').attr('src', '');
            });
        });
    </script>
@stop