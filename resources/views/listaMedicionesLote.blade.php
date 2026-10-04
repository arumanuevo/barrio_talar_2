@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card">
                <div class="card-header">{{ __('Lista Completa de Mediciones') }}</div>

                <div class="card-body">
                    <div class="card">
                        <div class="card-header">{{ __('Consumos por medición') }}</div>
                        <div class="card-body">
                            <canvas id="graficoConsumos" style="width: 100%; height: 350px;"></canvas>
                        </div>
                    </div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        var ctx = document.getElementById('graficoConsumos').getContext('2d');
        var graficoData = {!! $grafico !!};
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: graficoData.labels,
                datasets: [{
                    label: 'Consumo',
                    data: graficoData.consumos,
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 14 } } },
                    x: { ticks: { maxRotation: 45, minRotation: 45, font: { size: 9 } } }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>
@endsection