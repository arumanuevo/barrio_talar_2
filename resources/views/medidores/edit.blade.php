@extends('adminlte::page')

@section('title', 'Editar Medidor')

@section('content_header')
    <h1>Editar Medidor</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar información del medidor y usuario</h3>
        </div>

        <form method="POST" action="{{ route('medidores.update', $medidor->id) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="user_id" value="{{ $medidor->user->id ?? '' }}">

            <div class="card-body">
                <div class="form-group">
                    <label for="lote">Lote</label>
                    <input type="text" class="form-control" id="lote" value="{{ $medidor->lote }}" disabled>
                </div>

                <div class="form-group">
                    <label for="email">Email Propietario</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email"
                           value="{{ old('email', $medidor->user->email ?? '') }}"
                           {{ $medidor->user ? '' : 'disabled' }}>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="numero_medidor">Número de Medidor</label>
                    <input type="text" class="form-control @error('numero_medidor') is-invalid @enderror"
                           id="numero_medidor" name="numero_medidor"
                           value="{{ old('numero_medidor', $medidor->numero_medidor) }}" required>
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
                               id="password" name="password"
                               value="{{ old('password', $medidor->password) }}" required>
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
                <button type="submit" class="btn btn-primary">Actualizar</button>
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
        });
    </script>
@stop
