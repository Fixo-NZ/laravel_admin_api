<div class="space-y-3 mt-6 bg-white p-4 rounded-lg shadow-sm">
    <h2 class="text-lg font-semibold text-gray-800">{{ $service->name }}</h2>
    <p class="text-gray-600">{{ $service->description ?? 'No description provided.' }}</p>

    <div class="mt-3 space-y-1 text-sm">
        <p><strong>Category:</strong> {{ $service->category->name ?? '—' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($service->status ?? 'N/A') }}</p>
        <p><strong>Created At:</strong> {{ $service->created_at?->format('M d, Y h:i A') ?? '—' }}</p>
    </div>
</div>