@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Editar Medición') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ url('api/actualizarMedicion/' . $medicion->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="lote">Lote</label>
                            <input type="text" name="lote" class="form-control" value="{{ $medicion->lote }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="medidor">Medidor</label>
                            <input type="text" name="medidor" class="form-control" value="{{ $medicion->medidor }}">
                        </div>

                        <div class="form-group">
                            <label for="periodo">Periodo (días)</label>
                            <input type="text" name="periodo" class="form-control" value="{{ $medicion->periodo }}">
                        </div>

                        <div class="form-group">
                            <label for="fecha">Fecha Medición</label>
                            <input type="text" name="fecha" class="form-control" value="{{ date('d-m-Y', strtotime($medicion->fecha)) }}">
                        </div>

                        <div class="form-group">
                            <label for="vencimiento">Vencimiento</label>
                            <input type="text" name="vencimiento" class="form-control" value="{{ date('d-m-Y', strtotime($medicion->vencimiento)) }}">
                        </div>

                        <div class="form-group">
                            <label for="tomaant">Fecha Anterior</label>
                            <input type="text" name="tomaant" class="form-control" value="{{ date('d-m-Y', strtotime($medicion->tomaant)) }}">
                        </div>

                        <div class="form-group">
                            <label for="medidaant">Medida Anterior</label>
                            <input type="text" name="medidaant" class="form-control" value="{{ $medicion->medidaant }}">
                        </div>

                        <div class="form-group">
                            <label for="valormedido">Valor Medido</label>
                            <input type="text" name="valormedido" class="form-control" value="{{ $medicion->valormedido }}">
                        </div>

                        <div class="form-group">
                            <label for="consumo">Consumo</label>
                            <input type="text" name="consumo" class="form-control" value="{{ $medicion->consumo }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="inspector">Inspector</label>
                            <input type="text" name="inspector" class="form-control" value="{{ $medicion->inspector }}">
                        </div>
                        <input type="hidden" name="id" value="{{ $medicion->id }}">

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
