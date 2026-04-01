@extends('adminlte::page')
	
	@section('title', 'Dashboard')
	
	@section('content_header')
	    <h1>Panel de administracion de inspector</h1>
	@stop
	
	@section('content')
	    <p>Seleccione "Tomar Medicion" desde el menu izquierdo.</p>
		<h3>Roles del Usuario</h3>
		<ul>
			@foreach(auth()->user()->roles as $role)
				<li>{{ $role->name }}</li>
			@endforeach
		</ul>

		<!-- Mostrar permisos del usuario -->
		<h3>Permisos del Usuario</h3>
		<ul>
			@foreach(auth()->user()->permissions as $permission)
				<li>{{ $permission->name }}</li>
			@endforeach
		</ul>
	@stop
	
	@section('css')
	    <link rel="stylesheet" href="/css/admin_custom.css">
	@stop
	
	@section('js')
	    <script> console.log('Hi!'); </script>
	@stop
