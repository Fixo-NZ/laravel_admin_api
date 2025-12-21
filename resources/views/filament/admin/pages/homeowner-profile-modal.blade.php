<div class="p-6 bg-white rounded-lg shadow-md w-full max-w-lg mx-auto">
    <!-- Modal Header -->
    <h2 class="text-xl font-bold text-gray-800 mb-4">{{ $homeowner->name }} Profile</h2>

    <!-- Profile Content -->
    <div class="space-y-3 text-gray-700">
        <div class="flex justify-between">
            <span class="font-semibold">Name:</span>
            <span>{{ $homeowner->name }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Email:</span>
            <span class="text-blue-600 hover:underline">{{ $homeowner->email }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Phone:</span>
            <span>{{ $homeowner->phone ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Address:</span>
            <span>{{ $homeowner->address ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">City:</span>
            <span>{{ $homeowner->city ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Postal Code:</span>
            <span>{{ $homeowner->postal_code ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Region:</span>
            <span>{{ $homeowner->region ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Status:</span>
            <span class="px-3 py-1 rounded-full text-sm
                {{ $homeowner->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                {{ $homeowner->status === 'inactive' ? 'bg-red-100 text-red-800' : '' }}
                {{ $homeowner->status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                {{ ucfirst($homeowner->status) }}
            </span>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 📋 Booked Jobs Section -->
    <!-- ========================================================= -->
    <div class="mt-8 border-t pt-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Booked Jobs</h3>

        @if ($homeowner->jobs && $homeowner->jobs->count() > 0)
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach ($homeowner->jobs as $job)
                    <div class="p-3 bg-gray-50 rounded-md border flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-800">{{ $job->title ?? 'Untitled Job' }}</p>
                            <p class="text-sm text-gray-600">{{ $job->description ?? 'No description provided.' }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $job->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $job->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $job->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($job->status ?? 'unknown') }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 italic">No booked jobs found for this homeowner.</p>
        @endif
    </div>
</div>
