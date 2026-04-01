@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ 'Todas las Facturas Emitidas' }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('getTodasFacturas') }}" method="GET">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="Buscar...">
                        </div>
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        <a href="{{ route('getTodasFacturas') }}" class="btn btn-secondary">Restablecer</a>
                    </form>
</p>
                    <table id="tblFacturacionInfo" class="table display table-striped table-bordered" cellspacing="0" width="100%">
                        <thead class="thead-dark">
                            <tr class="text-center">
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'codaysa', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Codigo de Arqueo
                                    </a>
                                </th>
                                <!--<th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'seccion', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Seccion
                                    </a>
                                </th>-->
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'venaysa', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Vencimiento
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'lote', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Lote
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'medidor', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Medidor
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'fdesde', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Fecha Desde
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'fhasta', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Fecha Hasta
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'periodo', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Periodo
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'total', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Monto
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'fijovariable', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Fijo Variable Prorrateado
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'fijovariabletotal', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Fijo Variable Total
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'sumario', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Consumo del Periodo
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('getTodasFacturas', ['sort' => 'conxdia', 'order' => $order === 'asc' ? 'desc' : 'asc']) }}">
                                        Consumo por Dia
                                    </a>
                                </th>
                                
                                
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($facturasTodas as $factura)
                            <tr>
                                <td>{{ $factura->codaysa }}</td>
                                <td>{{ $factura->venaysa->format('d/m/y') }}</td>
                                <td>{{ $factura->lote }}</td>
                                <td>{{ $factura->medidor }}</td>
                                <td>{{ $factura->fdesde->format('d/m/y') }}</td>
                                <td>{{ $factura->fhasta->format('d/m/y') }}</td>
                                <td>{{ $factura->periodo }}</td>
                                <td>${{ $factura->total }}</td>
                                <td>${{ $factura->fijovariable }}</td>
                                <td>${{ $factura->fijovariabletotal }}</td>
                                <td>{{ $factura->sumario }}</td>
                                <td>{{ $factura->conxdia }}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                    {{ $facturasTodas->appends(['sort' => $sort, 'order' => $order, 'search' => $search])->links() }}
                   
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    // Aquí puedes incluir cualquier script adicional necesario
</script>
@endsection

