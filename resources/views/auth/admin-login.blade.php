<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Catch Me</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <!-- Decorative circles -->
        <div class="absolute top-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>

        <div class="relative z-10 w-full max-w-md">
            <!-- Logo Card -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4">
                    <x-heroicon-o-heart class="w-12 h-12 text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-pink-600" style="fill: url(#gradient)"/>
                    <svg width="0" height="0">
                        <defs>
                            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#4F46E5;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#DB2777;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Catch Me</h1>
                <p class="text-white/80 text-lg">Admin Dashboard</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white/95 backdrop-blur-xl p-8 rounded-2xl shadow-2xl border border-white/20">
                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-bold text-gray-800">Welcome Back!</h2>
                    <p class="text-gray-600 text-sm mt-1">Sign in to access your dashboard</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <x-heroicon-o-exclamation-circle class="w-5 h-5 mr-2"/>
                            <span class="text-sm font-medium">{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">
                            <div class="flex items-center">
                                <x-heroicon-o-envelope class="w-4 h-4 mr-2"/>
                                Email Address
                            </div>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 bg-gray-50 hover:bg-white"
                               placeholder="admin@catchme.app">
                    </div>

                    <div>
                        <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">
                            <div class="flex items-center">
                                <x-heroicon-o-lock-closed class="w-4 h-4 mr-2"/>
                                Password
                            </div>
                        </label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200 bg-gray-50 hover:bg-white"
                               placeholder="Enter your password">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                        <span>Sign In</span>
                        <x-heroicon-o-arrow-right class="w-5 h-5"/>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500">
                        <x-heroicon-o-shield-check class="w-4 h-4 inline mr-1"/>
                        Secure Admin Access Only
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-white/60 text-sm">
                    &copy; {{ date('Y') }} Catch Me. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
