@extends('adminlte::page')

@section('title', 'Crear Nuevo Medidor y Usuario')

@section('content_header')
    <h1>Crear Nuevo Medidor y Usuario</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ingrese los datos del nuevo medidor y usuario</h3>
        </div>

        <form method="POST" action="{{ route('medidores.store') }}">
            @csrf

            <div class="card-body">
                <div class="form-group">
                    <label for="lote">Lote</label>
                    <input type="text" class="form-control @error('lote') is-invalid @enderror"
                           id="lote" name="lote" value="{{ old('lote') }}" required>
                    @error('lote')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="numero_medidor">Número de Medidor</label>
                    <input type="text" class="form-control @error('numero_medidor') is-invalid @enderror"
                           id="numero_medidor" name="numero_medidor" value="{{ old('numero_medidor') }}" required>
                    @error('numero_medidor')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" value="{{ old('password') }}" required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="generate-password">
                                <i class="fas fa-sync-alt"></i> Generar
                            </button>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres.</small>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('medidores.index') }}" class="btn btn-default">Cancelar</a>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Generar contraseña aleatoria
            $('#generate-password').click(function() {
                var length = 8;
                var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                var password = "";
                for (var i = 0, n = charset.length; i < length; ++i) {
                    password += charset.charAt(Math.floor(Math.random() * n));
                }
                $('#password').val(password);
            });

            // Generar email automáticamente basado en el lote
            $('#lote').on('input', function() {
                var lote = $(this).val();
                if (lote) {
                    $('#email').val(lote + '@gmail.com');
                }
            });
        });
    </script>
@stop
