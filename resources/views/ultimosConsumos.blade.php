@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card" style="width: 100%; margin: auto;">
                <div class="card-header">{{ __('Ultimos Consumos') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                   
                    <div class="d-flex justify-content-end align-items-center mb-3">
                        <span class="badge bg-secondary">Total de registros: {{ $ultimasMediciones->total() }}</span>
                    </div>

                    <div class="table-responsive">
                        <table id="tblUltimosConsumos" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead class="thead-dark">
                                <tr class="text-center">
                                    <th>Lote</th>
                                    <th>Medidor</th>
                                    <th>Periodo (dias)</th>
                                    <th>Fecha Medición</th>
                                    <th>Vencimiento</th>
                                    <th>Fecha Anterior</th>
                                    <th>Medicion Anterior</th>
                                    <th>Valor Medido</th>
                                    <th>Consumo</th>
                                    <th>Ocupacion</th>
                                    <th>Inspector</th>
                                    <th>Foto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($ultimasMediciones->isNotEmpty())
                                    @foreach ($ultimasMediciones as $medicion)
                                        <tr>
                                            <td>{{ $medicion['lote'] }}</td>
                                            <td>{{ $medicion['medidor'] }}</td>
                                            <td>{{ $medicion['periodo'] }}</td>
                                            <td>{{ $medicion['fecha'] }}</td>
                                            <td>{{ $medicion['vencimiento'] }}</td>
                                            <td>{{ $medicion['tomaant'] }}</td>
                                            <td>{{ $medicion['medidaant'] }}</td>
                                            <td>{{ $medicion['valormedido'] }}</td>
                                            <td class="bg-info"><b>{{ $medicion['consumo'] }}</b></td>
                                            <td>{{ $medicion['ocupacion'] }}</td>
                                            <td>{{ $medicion['inspector'] }}</td>
                                            <td> 
                                                <div class="parent-container">
                                                    @if ($medicion['foto'] == "Sin foto")
                                                        <a>Sin Foto</a>
                                                    @else
                                                        <a class="fotoMedidor" target="_blank" href="{{ asset('images/'.$medicion['foto'].'.png') }}">Foto</a>                            
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="12">No hay mediciones disponibles.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $ultimasMediciones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    //window.objListaMediciones.display();   
</script>
@endsection

