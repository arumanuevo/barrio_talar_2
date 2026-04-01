@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Generar Contraseñas') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('generar-contrasenas') }}">
                        @csrf

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="cantidad">Cantidad de Contraseñas a Generar:</label>
                                <input type="number" name="cantidad" id="cantidad" class="form-control" max="500">
                            </div>

                            
                        </div>

                        <button type="submit" id="btnGenerar" class="btn btn-primary" disabled>Generar Contraseñas</button>
                        <!--<a href="{{ route('export-to-excel') }}" class="btn btn-success">Exportar a Excel</a>-->
                    </form>

                    <hr>

                    <h3>Contraseñas Generadas:</h3>
                    <div style="max-height: 300px; overflow-y: scroll;">
                        <!-- Ajusta la altura máxima según tus necesidades -->
                        <table class="table">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>Contraseña</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            // Ordena la colección por la sección y el lote
                            $contraseñasGeneradas = $contraseñasGeneradas->sortBy(['seccion', 'lote']);
                            @endphp
                            @foreach ($contraseñasGeneradas as $contraseñaGenerada)
                            <tr>
                                <td>{{ $contraseñaGenerada->lote }}</td>
                                <td>{{ $contraseñaGenerada->pass }}</td>
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
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        //var seccionSelect = document.getElementById('seccion');
        var submitButton = document.getElementById('btnGenerar');
        let cant = document.getElementById('cantidad');

        cant.addEventListener('change', function () {
            // Habilitar el botón de registro si se selecciona un valor diferente al por defecto
            //submitButton.disabled = seccionSelect.value === 'Defina seccion';
            submitButton.disabled = cant.value === '';
        });
    });
</script>


