@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Editar Lote') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('actualizarLote', ['id' => $lote->id]) }}">
                        @csrf
                        @method('PUT') <!-- Mantén esto en tu formulario -->
                        <div class="form-group">
                            <label for="lote">Lote:</label>
                            <input type="text" name="lote" class="form-control" value="{{ $lote->lote }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="medidor">Medidor:</label>
                            <input type="text" name="medidor" id="medidor" class="form-control" value="{{ $lote->medidor }}">
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ $lote->email }}">
                        </div>

                        <div class="form-group">
                            <label for="telefono">Telefono:</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" value="{{ $lote->telefono }}">
                        </div>

                        <div class="form-group">
                            <label for="ocupacion">Ocupación:</label>
                            <input type="text" name="ocupacion" id="ocupacion" class="form-control" value="{{ $lote->ocupacion }}">
                        </div>

                        <div class="form-group">
                            <label for="name">Nombre:</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $lote->name }}">
                        </div>

                        <div class="text-center">
                            <button type="submit" id="guardarEdicionLoteUsuario" class="btn btn-primary">Guardar Cambios</button>
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // var user = {!! auth()->user()->toJson() !!};

    window.objMedidor.display();
    // window.mapaOld.display();
</script>
@endsection

