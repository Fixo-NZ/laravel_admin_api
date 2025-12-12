<div class="p-6 bg-white rounded-lg shadow-md w-full max-w-3xl mx-auto">

    <!-- HEADER -->
    <h2 class="text-xl font-bold text-gray-900 mb-4">
        {{ $offer->title }} — Schedule Details
    </h2>

    <!-- ============================= -->
    <!-- BASIC INFORMATION -->
    <!-- ============================= -->
    <div class="border rounded-lg p-4 space-y-3 bg-gray-50">
        <h3 class="text-lg font-semibold !text-black mb-3">Basic Information</h3>

        <div class="space-y-2 text-gray-700">

            <div class="flex justify-between">
                <span class="font-semibold">Category:</span>
                <span>{{ $offer->category->name ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">Job Type:</span>
                <span>{{ $offer->job_type }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">Homeowner:</span>
                <span>{{ $offer->homeowner->first_name }} {{ $offer->homeowner->last_name }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">Tradie:</span>
                <span>
                    @if ($offer->tradie)
                        {{ $offer->tradie->first_name }} {{ $offer->tradie->last_name }}
                    @else
                        Not Assigned
                    @endif
                </span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">Status:</span>
                <span class="px-3 py-1 rounded-full text-sm
                    {{ $offer->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $offer->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $offer->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $offer->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}">
                    {{ ucfirst($offer->status) }}
                </span>
            </div>

        </div>
    </div>

    <!-- ============================= -->
    <!-- SCHEDULE DETAILS -->
    <!-- ============================= -->
    <div class="mt-6 border rounded-lg p-4 bg-gray-50">
        <h3 class="text-lg font-semibold !text-black mb-3">Schedule Details</h3>

        <div class="space-y-2 text-gray-700">

            <div class="flex justify-between">
                <span class="font-semibold">Preferred Date:</span>
                <span>{{ $offer->preferred_date?->format('M d, Y') }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">Start Time:</span>
                <span>{{ $offer->start_time ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">End Time:</span>
                <span>{{ $offer->end_time ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">Frequency:</span>
                <span>{{ $offer->frequency ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between">
                <span class="font-semibold">Address:</span>
                <span class="text-right">{{ $offer->address }}</span>
            </div>

        </div>
    </div>

    <!-- ============================= -->
    <!-- SERVICES -->
    <!-- ============================= -->
    <div class="mt-6 border rounded-lg p-4 bg-gray-50">
        <h3 class="text-lg font-semibold !text-black mb-3">Services</h3>

        @if ($offer->services->count())
            <ul class="list-disc ml-6 text-gray-700">
                @foreach ($offer->services as $service)
                    <li>{{ $service->name }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500 italic">No services selected.</p>
        @endif
    </div>

    <!-- ============================= -->
    <!-- DESCRIPTION -->
    <!-- ============================= -->
    <div class="mt-6 border rounded-lg p-4 bg-gray-50">
        <h3 class="text-lg font-semibold !text-black mb-3">Description</h3>
        <p class="text-gray-700">{{ $offer->description }}</p>
    </div>

    <!-- ============================= -->
    <!-- PHOTOS -->
    <!-- ============================= -->
    <div class="mt-6 border rounded-lg p-4 bg-gray-50">
        <h3 class="text-lg font-semibold !text-black mb-3">Photos</h3>

        <div class="grid grid-cols-3 gap-4">
            @forelse ($offer->photo_urls as $url)
                <img src="{{ $url }}" class="rounded-lg shadow">
            @empty
                <p class="text-gray-500 italic">No photos uploaded.</p>
            @endforelse
        </div>
    </div>

</div>
