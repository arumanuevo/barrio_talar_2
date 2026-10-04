@extends('adminlte::page')

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

                </div>
                <table id="tablaTodasMedicionesLote"  class="table display text-center nowrap compact table-striped table-bordered " cellspacing="0" width="100%">
                    <thead  class="thead-dark">
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
                       
                        <th>Foto</th>
                        
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($mediciones as $medicion)
                        <tr>
                          
                          <td>{{ $medicion->lote }}</td>
                          <td>{{ $medicion->medidor }}</td>
                          <td>{{ $medicion->periodo }}</td>
                          <td>{{ $medicion->fecha }}</td>
                          <td>{{ $medicion->vencimiento }}</td>
                          <td>{{ $medicion->tomaant }}</td>
                          <td>{{ $medicion->medidaant }}</td>
                          <td>{{ $medicion->valormedido }}</td>
                          <td>{{ $medicion->consumo }}</td>
                         
                          <td>
                            <div class="parent-container">
                            @if ($medicion->foto == "Sin foto")
                              <a>Sin Foto</a>
                            @else
                              <a class = "fotoMedidor" href="{{ asset('images/'.$medicion->foto.'.png') }}">Foto</a>
                              
                            @endif
                            </div>
                          </td>
                          
                      </tr>
                      @endforeach
                   
                    </tbody>
                    
                  </table>               
                  {{ $mediciones->links() }}
            </div>
            
        </div>
        
    </div>
   
</div>

<script type="text/javascript">
   // window.objListaMediciones.display();   
   
</script>
@endsection