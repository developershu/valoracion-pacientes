<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Valoración - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="http://172.22.118.101:81/proyectsImages/favicon_color.png?raw=true" type="image/x-icon">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .navbar {
            background-color: #003764 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .navbar-brand img {
            max-height: 80px;
        }
        .main-content {
            padding-top: 20px;
            padding-bottom: 40px;
        }
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
            border-radius: 10px;
        }
        .table {
            background-color: white;
        }
        
        /* Estilos personalizados para métricas */
        .metric-card {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,.15);
        }
        
        /* Animación para estrellas */
        .star-rating {
            position: relative;
        }
        .star-rating i {
            transition: all 0.3s ease;
        }
        .star-rating:hover i {
            transform: scale(1.1);
        }
        
        /* Círculo de progreso animado */
        .progress-circle {
            transition: stroke-dashoffset 0.6s ease;
        }
        
        /* Colores personalizados */
        .text-orange {
            color: #fd7e14 !important;
        }
        .bg-orange {
            background-color: #fd7e14 !important;
        }
        
        /* Badges mejorados */
        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }
        
        /* Cards de métricas */
        .metric-icon {
            font-size: 1.2rem;
        }
        
        /* Efecto glow para scores altos */
        .score-excellent {
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.3);
        }
        .score-good {
            box-shadow: 0 0 20px rgba(23, 162, 184, 0.3);
        }
        .score-warning {
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.3);
        }
        .score-danger {
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.3);
        }
        
        .btn {
            border-radius: 6px;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('turnos.index') }}">
                <img src="http://172.22.118.101:81/proyectsImages/logo ranking.png" 
                     alt="Logo" class="me-2">
                
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user"></i> {{ Auth::user()->name ?? 'Usuario' }}
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="main-content">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @yield('scripts')
</body>
</html>
