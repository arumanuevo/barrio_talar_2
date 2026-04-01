@extends('adminlte::page')

@section('content')
<!--<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                   <video id="webcam" autoplay playsinline width="640" height="480"></video>
                    <canvas id="canvas" class="d-none"></canvas>
                                    
                    <audio id="snapSound" src="{{ asset('../js/assets/audio/snap.wav') }}" preload = "auto"></audio>
                    


                </div>
            </div>
        </div>
    </div>
</div>-->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
            
                <div class="card-header">
                    {!! ' <span class="seccion-negrita"></span> </strong>' . ' Facturación Lote: ' . Auth::user()->lote !!}
                </div>


                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <table id="tblFacturacionInfo" class="table display table-striped table-bordered " cellspacing="0" width="100%">
                    <thead class="thead-dark">
                      <tr class="text-center">                      
                        <th>Codigo de Arqueo</th>
                        <th>Vencimiento</th>
                        
                        <th>Fecha Desde</th>
                        <th>Fecha Hasta</th>
                        <th>Periodo (dias)</th>
                        <!--<th>Monto Basico</th>
                        <th>Fijo Variable Prorrateado</th>
                        <th>Monto Total</th>-->
                        
                        <th>Consumo del Periodo</th>
                        <th>Consumo Diario Promedio</th>
                        <th>Medidor</th>
                      
                  
                      </tr>
                    </thead>
                    <tbody>
                    @foreach ($facturasLote as $factura)
                        <tr>
                            <td>{{ $factura->codaysa }}</td>   
                            <td>{{ $factura->venaysa }}</td>                
                            
                            <td>{{ $factura->fdesde }}</td>
                            <td>{{ $factura->fhasta }}</td>
                            <td>{{ $factura->periodo }}</td>
                            
                            
                            <td>{{ $factura->sumario }}</td>
                            <td>{{ $factura->conxdia }}</td>
                            <td>{{ $factura->medidor }}</td>
                        </tr>
                    @endforeach
                   
                    </tbody>
                    
                  </table>

            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    //window.objListaMediciones.display();   
   // window.mapaOld.display();
</script>
@endsection