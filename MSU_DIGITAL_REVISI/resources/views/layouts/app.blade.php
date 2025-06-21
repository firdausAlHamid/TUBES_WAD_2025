<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Digital Quran') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    @stack('styles')
    <style>
        body {
            padding-top: 60px; /* Add padding to prevent content from hiding under fixed navbar */
        }
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background-color: #198754;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: white !important;
        }
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
        }
        .nav-link:hover {
            color: white !important;
        }
    </style>
</head>
<body>
    @auth
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="{{ route('home') }}">Masjid Syamsul Ulum</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('bookmarks.index') }}">Bookmarks</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('notes.*') ? 'active' : '' }}" href="{{ route('notes.show') }}">Notes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('juz.*') ? 'active' : '' }}" href="{{ route('juz.index') }}">
                                <i class="bi bi-journal-text"></i> Hafalan Juz
                            </a>
                        </li>
                    </ul>
                    <div class="d-flex">
                        <span class="navbar-text me-3 text-white">
                            Welcome, {{ Auth::user()->name }}
                        </span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <main class="py-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html> 