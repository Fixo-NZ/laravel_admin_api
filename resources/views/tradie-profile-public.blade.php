<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tradie Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">
<div class="max-w-4xl mx-auto py-12">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center gap-4">
            <div class="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-3xl font-bold">{{ strtoupper(substr($tradie->first_name ?? '',0,1)) }}{{ strtoupper(substr($tradie->last_name ?? '',0,1)) }}</div>
            <div>
                <h1 class="text-2xl font-semibold">{{ $tradie->first_name }} {{ $tradie->last_name }}</h1>
                <p class="text-sm text-gray-600">{{ $tradie->email }}</p>
                <p class="text-sm text-gray-600">{{ $tradie->phone ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
            <div>
                <p class="text-xs text-gray-500">Address</p>
                <p class="mt-1">{{ $tradie->address ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">City</p>
                <p class="mt-1">{{ $tradie->city ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Region</p>
                <p class="mt-1">{{ $tradie->region ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Postal Code</p>
                <p class="mt-1">{{ $tradie->postal_code ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="mt-6">
            <h2 class="text-lg font-medium">Bookings</h2>
            <div class="mt-3 space-y-3">
                @if ($tradie->bookings && $tradie->bookings->count())
                    @foreach ($tradie->bookings as $booking)
                        <div class="p-3 border rounded-md bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold">{{ $booking->service->name ?? 'Untitled Service' }}</div>
                                    <div class="text-xs text-gray-500">{{ $booking->booking_start }} — {{ $booking->booking_end }}</div>
                                </div>
                                <div class="text-xs text-gray-600">ID: {{ $booking->id }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-gray-500 italic">No bookings found.</div>
                @endif
            </div>
        </div>
    </div>
</div>
</body>
</html>
