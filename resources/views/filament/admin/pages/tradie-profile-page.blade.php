
<x-filament-panels::page>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r flex flex-col justify-between">
            <div class="p-6">
                <a href="/" class="flex items-center gap-2 mb-8">
                    <img src="/logo.png" alt="Logo" class="h-8 w-8">
                    <span class="font-bold text-lg text-indigo-700">Dashboard</span>
                </a>
                <nav class="space-y-2">
                    <a href="/admin/homeowners" class="block px-4 py-2 rounded hover:bg-indigo-50 text-gray-700">Homeowners</a>
                    <a href="/admin/tradies" class="block px-4 py-2 rounded hover:bg-indigo-50 text-gray-700">Tradies</a>
                    <a href="/admin/bookings" class="block px-4 py-2 rounded hover:bg-indigo-50 text-gray-700">Bookings</a>
                </nav>
            </div>
            <footer class="p-6 border-t">
                <a href="/" class="flex items-center gap-2">
                    <img src="/logo.png" alt="Logo" class="h-6 w-6">
                    <span class="text-sm text-indigo-700 font-semibold">Back to Dashboard</span>
                </a>
            </footer>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 bg-gray-50 py-8 px-8">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <button onclick="history.back()" class="inline-flex items-center px-3 py-2 rounded-md bg-white border text-sm text-gray-600 hover:bg-gray-50">
                            &larr; Back
                        </button>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $tradie->first_name }} {{ $tradie->last_name }}</h1>
                            <p class="text-sm text-gray-500">Tradie profile — ID: {{ $tradie->id }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Summary Card -->
                    <div class="bg-white border rounded-lg p-6 shadow-sm">
                        <div class="flex flex-col items-center text-center">
                            <div class="h-24 w-24 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-3xl font-bold">{{ strtoupper(substr($tradie->first_name ?? '',0,1)) }}{{ strtoupper(substr($tradie->last_name ?? '',0,1)) }}</div>
                            <h2 class="mt-4 text-lg font-medium text-gray-900">{{ $tradie->first_name }} {{ $tradie->last_name }}</h2>
                            <p class="text-sm text-gray-500">{{ $tradie->email }}</p>
                        </div>

                        <div class="mt-6 space-y-3">
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <span class="font-medium">Phone</span>
                                <span>{{ $tradie->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <span class="font-medium">Location</span>
                                <span>{{ $tradie->city ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <span class="font-medium">Region</span>
                                <span>{{ $tradie->region ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <span class="font-medium">Status</span>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $tradie->status === 'active' ? 'bg-green-100 text-green-800' : ($tradie->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($tradie->status ?? 'unknown') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Details & Bookings (span two columns on lg) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white border rounded-lg p-6 shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Contact & Address</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="mt-1">{{ $tradie->email }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Phone</p>
                                    <p class="mt-1">{{ $tradie->phone ?? 'N/A' }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-xs text-gray-500">Address</p>
                                    <p class="mt-1">{{ $tradie->address ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">City</p>
                                    <p class="mt-1">{{ $tradie->city ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Postal Code</p>
                                    <p class="mt-1">{{ $tradie->postal_code ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border rounded-lg p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Booked Jobs</h3>
                                <span class="text-sm text-gray-500">{{ $tradie->bookings ? $tradie->bookings->count() : 0 }} bookings</span>
                            </div>

                            @if ($tradie->bookings && $tradie->bookings->count() > 0)
                                <div class="space-y-4">
                                    @foreach ($tradie->bookings as $booking)
                                        <div class="p-4 border rounded-md hover:shadow transition">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="font-semibold text-gray-800">{{ $booking->service->name ?? 'Untitled Service' }}</p>
                                                    <p class="text-sm text-gray-600 mt-1">{{ \Illuminate\Support\Str::limit($booking->service->description ?? 'No description provided.', 120) }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">Booking Period: {{ $booking->booking_start }} - {{ $booking->booking_end }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs font-semibold px-2 py-1 rounded-full {{ $booking->status === 'completed' ? 'bg-green-100 text-green-800' : ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($booking->status ?? 'unknown') }}</p>
                                                    <p class="text-xs text-gray-500 mt-2">ID: {{ $booking->id }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 italic">No bookings found for this tradie.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-filament-panels::page>
