@vite(['resources/css/app.css', 'resources/js/app.js'])
<div class="py-8 px-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <button onclick="history.back()" class="inline-flex items-center px-3 py-2 rounded-md bg-white border text-sm text-gray-600 hover:bg-gray-50">
                ← Back
            </button>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $tradie->first_name }} {{ $tradie->last_name }}</h1>
                <p class="text-sm text-gray-500">Tradie profile — ID: {{ $tradie->id }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="/" class="text-sm text-indigo-600 hover:underline">Return to Dashboard</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Summary Card -->
        <div class="bg-white border rounded-lg p-6 shadow-sm">
            <div class="flex flex-col items-center text-center">
                <div class="h-24 w-24 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-3xl font-bold">
                    {{ strtoupper(substr($tradie->first_name ?? '',0,1)) }}{{ strtoupper(substr($tradie->last_name ?? '',0,1)) }}
                </div>
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
                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $tradie->status === 'active' ? 'bg-green-100 text-green-800' : ($tradie->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($tradie->status ?? 'unknown') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Right: Details & Bookings -->
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
                    <h3 class="text-lg font-medium text-gray-900">Bookings</h3>
                    <span class="text-sm text-gray-500">{{ $tradie->bookings ? $tradie->bookings->count() : 0 }} bookings</span>
                </div>

                @if ($tradie->bookings && $tradie->bookings->count() > 0)
                    <div class="space-y-4">
                        @foreach ($tradie->bookings as $booking)
                            <div class="p-4 border rounded-md hover:shadow transition">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-800">{{ $booking->service->name ?? 'Untitled Service' }}</p>
                                        <p class="text-sm text-gray-600 mt-1">{{ $booking->service->description ?? 'No description provided.' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">Booking Period: {{ $booking->booking_start }} - {{ $booking->booking_end }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-semibold px-2 py-1 rounded-full inline-block {{ $booking->status === 'completed' ? 'bg-green-100 text-green-800' : ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($booking->status ?? 'unknown') }}
                                        </p>
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


