<div class="space-y-4 bg-white p-5 rounded-lg shadow-sm">

    {{-- Title --}}
    <h2 class="text-xl font-semibold text-gray-900">
        {{ $service->name }}
    </h2>

    {{-- Description --}}
    <p class="text-gray-700 leading-relaxed">
        {{ $service->description ?: 'No description provided.' }}
    </p>

    {{-- Details --}}
    <div class="pt-3 border-t space-y-2 text-sm text-gray-700">

        <p>
            <span class="font-semibold text-gray-800">Category:</span>
            {{ $service->category->name ?? '—' }}
        </p>

        <p>
            <span class="font-semibold text-gray-800">Status:</span>
            <span
                class="
                px-2 py-1 rounded-md text-white text-xs font-semibold"
            >
                {{ ucfirst($service->status ?? 'N/A') }}
            </span>
        </p>

        <p>
            <span class="font-semibold text-gray-800">Created:</span>
            {{ $service->created_at?->format('F d, Y g:i A') ?? '—' }}
        </p>
    </div>

</div>
