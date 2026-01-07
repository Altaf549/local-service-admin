<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    @stack('styles')
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: #adb5bd;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
        .sidebar .nav-link[data-bs-toggle="collapse"] {
            cursor: pointer;
        }
        .sidebar .nav-link[data-bs-toggle="collapse"] .fa-chevron-down {
            transition: transform 0.3s;
        }
        .sidebar .nav-link[data-bs-toggle="collapse"][aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }
        .sidebar .nav.flex-column.ms-3 .nav-link {
            padding-left: 2rem;
            font-size: 0.9rem;
        }
        /* Toggle Switch Styles */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        .toggle-switch input:checked + .toggle-slider {
            background-color: #28a745;
        }
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        .toggle-switch input:disabled + .toggle-slider {
            opacity: 0.6;
            cursor: not-allowed;
        }
        /* Settings Sub-sidebar Styles */
        .nav-pills .nav-link {
            border-radius: 0.375rem;
            color: #495057;
            padding: 0.5rem 1rem;
        }
        .nav-pills .nav-link:hover {
            background-color: #f8f9fa;
            color: #212529;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
        }
        .card-title {
            font-weight: 600;
            color: #212529;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text text-white me-3">{{ Auth::user()->name }}</span>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.service-categories.*') || request()->routeIs('admin.services.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#serviceSubmenu" role="button" aria-expanded="{{ request()->routeIs('admin.service-categories.*') || request()->routeIs('admin.services.*') ? 'true' : 'false' }}" aria-controls="serviceSubmenu">
                                <i class="fas fa-tools me-2"></i> Service
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('admin.service-categories.*') || request()->routeIs('admin.services.*') ? 'show' : '' }}" id="serviceSubmenu">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}" href="{{ route('admin.service-categories.index') }}">
                                            <i class="fas fa-list me-2"></i> Service Categories
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">
                                            <i class="fas fa-tools me-2"></i> Services
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.puja-types.*') || request()->routeIs('admin.pujas.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#pujaSubmenu" role="button" aria-expanded="{{ request()->routeIs('admin.puja-types.*') || request()->routeIs('admin.pujas.*') ? 'true' : 'false' }}" aria-controls="pujaSubmenu">
                                <i class="fas fa-praying-hands me-2"></i> Puja
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('admin.puja-types.*') || request()->routeIs('admin.pujas.*') ? 'show' : '' }}" id="pujaSubmenu">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.puja-types.*') ? 'active' : '' }}" href="{{ route('admin.puja-types.index') }}">
                                            <i class="fas fa-list me-2"></i> Puja Types
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.pujas.*') ? 'active' : '' }}" href="{{ route('admin.pujas.index') }}">
                                            <i class="fas fa-praying-hands me-2"></i> Pujas
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users me-2"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.servicemen.*') || request()->routeIs('admin.serviceman-service-prices.*') || request()->routeIs('admin.serviceman-experiences.*') || request()->routeIs('admin.serviceman-achievements.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#servicemanSubmenu" role="button" aria-expanded="{{ request()->routeIs('admin.servicemen.*') || request()->routeIs('admin.serviceman-service-prices.*') || request()->routeIs('admin.serviceman-experiences.*') || request()->routeIs('admin.serviceman-achievements.*') ? 'true' : 'false' }}" aria-controls="servicemanSubmenu">
                                <i class="fas fa-user-tie me-2"></i> Serviceman Data
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('admin.servicemen.*') || request()->routeIs('admin.serviceman-service-prices.*') || request()->routeIs('admin.serviceman-experiences.*') || request()->routeIs('admin.serviceman-achievements.*') ? 'show' : '' }}" id="servicemanSubmenu">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.servicemen.*') ? 'active' : '' }}" href="{{ route('admin.servicemen.index') }}">
                                            <i class="fas fa-list me-2"></i> Servicemen List
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.serviceman-service-prices.*') ? 'active' : '' }}" href="{{ route('admin.serviceman-service-prices.index') }}">
                                            <i class="fas fa-dollar-sign me-2"></i> Service Prices
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.serviceman-experiences.*') ? 'active' : '' }}" href="{{ route('admin.serviceman-experiences.index') }}">
                                            <i class="fas fa-briefcase me-2"></i> Experiences
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.serviceman-achievements.*') ? 'active' : '' }}" href="{{ route('admin.serviceman-achievements.index') }}">
                                            <i class="fas fa-trophy me-2"></i> Achievements
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.brahmans.*') || request()->routeIs('admin.brahman-puja-prices.*') || request()->routeIs('admin.brahman-experiences.*') || request()->routeIs('admin.brahman-achievements.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#brahmanSubmenu" role="button" aria-expanded="{{ request()->routeIs('admin.brahmans.*') || request()->routeIs('admin.brahman-puja-prices.*') || request()->routeIs('admin.brahman-experiences.*') || request()->routeIs('admin.brahman-achievements.*') ? 'true' : 'false' }}" aria-controls="brahmanSubmenu">
                                <i class="fas fa-user-graduate me-2"></i> Brahman Data
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('admin.brahmans.*') || request()->routeIs('admin.brahman-puja-prices.*') || request()->routeIs('admin.brahman-experiences.*') || request()->routeIs('admin.brahman-achievements.*') ? 'show' : '' }}" id="brahmanSubmenu">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.brahmans.*') ? 'active' : '' }}" href="{{ route('admin.brahmans.index') }}">
                                            <i class="fas fa-list me-2"></i> Brahmans List
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.brahman-puja-prices.*') ? 'active' : '' }}" href="{{ route('admin.brahman-puja-prices.index') }}">
                                            <i class="fas fa-rupee-sign me-2"></i> Puja Prices
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.brahman-experiences.*') ? 'active' : '' }}" href="{{ route('admin.brahman-experiences.index') }}">
                                            <i class="fas fa-briefcase me-2"></i> Experiences
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.brahman-achievements.*') ? 'active' : '' }}" href="{{ route('admin.brahman-achievements.index') }}">
                                            <i class="fas fa-trophy me-2"></i> Achievements
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#settingsSubmenu" role="button" aria-expanded="{{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }}" aria-controls="settingsSubmenu">
                                <i class="fas fa-cog me-2"></i> Settings
                                <i class="fas fa-chevron-down ms-auto"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('admin.settings.*') ? 'show' : '' }}" id="settingsSubmenu">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.banner') ? 'active' : '' }}" href="{{ route('admin.settings.banner') }}">
                                            <i class="fas fa-image me-2"></i> Banner Management
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.terms') ? 'active' : '' }}" href="{{ route('admin.settings.terms') }}">
                                            <i class="fas fa-file-contract me-2"></i> Terms & Conditions
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.privacy') ? 'active' : '' }}" href="{{ route('admin.settings.privacy') }}">
                                            <i class="fas fa-shield-alt me-2"></i> Privacy Policy
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.settings.about') ? 'active' : '' }}" href="{{ route('admin.settings.about') }}">
                                            <i class="fas fa-info-circle me-2"></i> About Us
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('title', 'Dashboard')</h1>
                </div>

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

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    @stack('scripts')
</body>
</html>

