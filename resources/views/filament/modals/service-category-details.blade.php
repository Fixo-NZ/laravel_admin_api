<div class="space-y-4 p-4 bg-white rounded-lg shadow-sm">
    {{-- Category Details --}}
    <div class="flex items-center gap-3">
        <strong class="text-gray-700 w-32">Name:</strong>
        <span>{{ $category->name }}</span>
    </div>

    <div class="flex items-start gap-3">
        <strong class="text-gray-700 w-32">Description:</strong>
        <span>{{ $category->description ?? 'No description provided' }}</span>
    </div>

    <div class="flex items-center gap-3">
        <strong class="text-gray-700 w-32">Status:</strong>
        <span 
            class="px-3 py-1 rounded-full font-semibold">
            {{ ucfirst($category->status ?? 'N/A') }}
        </span>
    </div>

    <div class="flex items-center gap-3">
        <strong class="text-gray-700 w-32">Icon:</strong>
        @if(!empty($category->icon))
            <img src="{{ asset('storage/icons/' . $category->icon . '.svg') }}" 
                 alt="Category Icon" class="w-10 h-10">
        @else
            <span class="text-gray-400 italic">No Icon</span>
        @endif
    </div>
</div>
