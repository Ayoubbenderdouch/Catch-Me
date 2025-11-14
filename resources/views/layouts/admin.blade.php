<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Catch Me</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-indigo-600 via-purple-600 to-pink-600 text-white flex-shrink-0 shadow-2xl">
            <!-- Logo Section -->
            <div class="p-6 border-b border-white/20">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 backdrop-blur-lg p-2 rounded-xl">
                        <x-heroicon-o-heart class="w-8 h-8 text-white"/>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Catch Me</h1>
                        <p class="text-xs text-white/80">Admin Panel</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="mt-6 px-3 space-y-2 overflow-y-auto h-[calc(100vh-220px)]">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-chart-bar class="w-5 h-5"/>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-users class="w-5 h-5"/>
                    <span class="font-medium">Users</span>
                </a>
                <a href="{{ route('admin.likes.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.likes.*') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-heart class="w-5 h-5"/>
                    <span class="font-medium">Likes & Matches</span>
                </a>
                <a href="{{ route('admin.messages.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.messages.*') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-chat-bubble-left-right class="w-5 h-5"/>
                    <span class="font-medium">Chats</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-flag class="w-5 h-5"/>
                    <span class="font-medium">Reports</span>
                </a>
                <a href="{{ route('admin.security.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.security.*') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-shield-check class="w-5 h-5"/>
                    <span class="font-medium">Security</span>
                </a>
                <a href="{{ route('admin.map.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.map.*') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-map class="w-5 h-5"/>
                    <span class="font-medium">Live Map</span>
                </a>
                <a href="{{ route('admin.notifications.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.notifications.*') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-bell class="w-5 h-5"/>
                    <span class="font-medium">Notifications</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-cog-6-tooth class="w-5 h-5"/>
                    <span class="font-medium">Settings</span>
                </a>
                <a href="{{ route('admin.admins.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.admins.*') ? 'bg-white/20 shadow-lg backdrop-blur-lg' : 'hover:bg-white/10' }}">
                    <x-heroicon-o-user-circle class="w-5 h-5"/>
                    <span class="font-medium">Admin Users</span>
                </a>
            </nav>

            <!-- User Section -->
            <div class="absolute bottom-0 w-64 p-4 border-t border-white/20 bg-white/10 backdrop-blur-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-white/20 p-2 rounded-lg">
                            <x-heroicon-o-user class="w-5 h-5"/>
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ auth('admin')->user()->name }}</p>
                            <p class="text-xs text-white/70">{{ auth('admin')->user()->role }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="p-2 hover:bg-white/20 rounded-lg transition-all" title="Logout">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5"/>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-8 py-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                        <p class="text-sm text-gray-500 mt-1">@yield('page-description', 'Welcome back to your dashboard')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-700">{{ date('l, F j, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ date('g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-8 bg-gray-50">
                @if(session('success'))
                    <div class="mb-6 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-lg shadow-sm flex items-center space-x-3">
                        <x-heroicon-o-check-circle class="w-6 h-6 text-green-600"/>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-lg shadow-sm flex items-center space-x-3">
                        <x-heroicon-o-exclamation-circle class="w-6 h-6 text-red-600"/>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
