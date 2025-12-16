<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Profile' }} - {{ config('app.name', 'Fixo') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 fixed h-full">
            <div class="p-6">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2 mb-8">
                    <div class="h-10 w-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-md">F</div>
                    <span class="text-xl font-bold text-gray-900">{{ config('app.name', 'Fixo') }}</span>
                </a>

                <!-- Navigation -->
                <nav class="space-y-1">
                    <a href="/" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>

                    @if(isset($profileType) && $profileType === 'homeowner')
                    <a href="{{ request()->fullUrl() }}" class="flex items-center gap-3 px-3 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-md font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Homeowner Profile
                    </a>
                    @endif

                    <!-- User Overview Group -->
                    <div class="pt-4">
                        <button class="flex items-center justify-between w-full px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>User Overview</span>
                            </div>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="ml-8 mt-1 space-y-1">
                            <a href="#" class="block px-3 py-2 text-sm text-gray-600 rounded-md hover:bg-gray-100">
                                Admin
                            </a>
                            <a href="#" class="block px-3 py-2 text-sm text-gray-600 rounded-md hover:bg-gray-100">
                                Homeowners
                            </a>
                            <a href="#" class="block px-3 py-2 text-sm text-gray-600 rounded-md hover:bg-gray-100">
                                Tradies
                            </a>
                        </div>
                    </div>
                </nav>
            </div>

            <!-- User Menu at Bottom -->
            <div class="absolute bottom-0 w-64 p-4 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-gray-800 flex items-center justify-center text-white text-xs font-bold">
                        AB
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">Admin User</p>
                        <p class="text-xs text-gray-500 truncate">admin@example.com</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <div class="py-8 px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <button onclick="history.back()" class="inline-flex items-center px-3 py-2 rounded-md bg-white border text-sm text-gray-600 hover:bg-gray-50">
                            ← Back
                        </button>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $name }}</h1>
                            <p class="text-sm text-gray-500">{{ $profileLabel }} — ID: {{ $id }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="/" class="text-sm text-indigo-600 hover:underline">Return to Dashboard</a>
                    </div>
                </div>

                <!-- Content -->
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
