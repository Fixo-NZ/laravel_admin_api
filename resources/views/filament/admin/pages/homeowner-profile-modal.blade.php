<div class="w-full max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start gap-4">
            <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-2xl font-bold">{{ strtoupper(substr($homeowner->first_name ?? '',0,1)) }}{{ strtoupper(substr($homeowner->last_name ?? '',0,1)) }}</div>
            <div class="flex-1">
                <h2 class="text-lg font-semibold text-gray-900">{{ ($homeowner->first_name ?? '') . ' ' . ($homeowner->last_name ?? '') }}</h2>
                <p class="text-sm text-gray-500">{{ $homeowner->email }}</p>
                <div class="mt-3 text-sm text-gray-700 grid grid-cols-2 gap-2">
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="mt-1">{{ $homeowner->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <p class="mt-1"><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $homeowner->status === 'active' ? 'bg-green-100 text-green-800' : ($homeowner->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($homeowner->status ?? 'unknown') }}</span></p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500">Address</p>
                        <p class="mt-1">{{ $homeowner->address ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 border-t pt-4">
            <h3 class="text-md font-medium text-gray-900 mb-3">Booked Jobs</h3>

            @if ($homeowner->jobs && $homeowner->jobs->count() > 0)
                <div class="space-y-3 max-h-72 overflow-y-auto">
                    @foreach ($homeowner->jobs as $job)
                        <div class="p-3 bg-gray-50 rounded-md border flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-800">{{ $job->title ?? ($job->service->name ?? 'Untitled Job') }}</p>
                                <p class="text-sm text-gray-600">{{ $job->description ?? ($job->service->description ?? 'No description provided.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-semibold px-2 py-1 rounded-full {{ $job->status === 'completed' ? 'bg-green-100 text-green-800' : ($job->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($job->status ?? 'unknown') }}</p>
                                <p class="text-xs text-gray-500 mt-1">ID: {{ $job->id }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">No booked jobs found for this homeowner.</p>
            @endif
        </div>
    </div>
</div>
