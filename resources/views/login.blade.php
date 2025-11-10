@extends('layouts.app')
@section('content')
    <!-- Estado de la Sesión -->
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Campo de Usuario -->
        <div class="mb-3">
            <label for="username" class="form-label text-white">
                <i class="fas fa-user me-1"></i>Usuario
            </label>
            <input id="username" class="form-control @error('username') is-invalid @enderror" 
                   type="text" name="username" value="{{ old('username') }}" 
                   required autofocus autocomplete="username"
                   placeholder="Ingrese su nombre de usuario">
            @error('username')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
            <small class="form-text text-light opacity-75">
                <i class="fas fa-info-circle me-1"></i>
            </small>
        </div>

        <!-- Campo de Contraseña -->
        <div class="mb-4">
            <label for="password" class="form-label text-white">
                <i class="fas fa-lock me-1"></i>Contraseña
            </label>
            <input id="password" class="form-control @error('password') is-invalid @enderror" 
                   type="password" name="password" required autocomplete="current-password"
                   placeholder="Ingrese su contraseña">
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                Ingresar
            </button>
        </div>
    </form>
@endsection
