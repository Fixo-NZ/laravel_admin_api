<x-filament::widget>
    <x-filament::section>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            {{-- Total Homeowners --}}
            <a href="{{ $this->getStats()['total']['url'] }}" class="flex items-center gap-4 p-4 transition rounded-lg hover:bg-gray-50 dark:hover:bg-white/5">
                <div class="p-3 rounded-full bg-primary-50 dark:bg-primary-900/20">
                    <x-heroicon-m-user-group class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Homeowners</span>
                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $this->getStats()['total']['value'] }}</span>
                    
                </div>
            </a>

            {{-- Active Homeowners --}}
            <a href="{{ $this->getStats()['active']['url'] }}" class="flex items-center gap-4 p-4 transition rounded-lg hover:bg-gray-50 dark:hover:bg-white/5">
                 <div class="p-3 rounded-full bg-success-50 dark:bg-success-900/20">
                    <x-heroicon-m-check-circle class="w-8 h-8 text-success-600 dark:text-success-400" />
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Homeowners</span>
                    <span class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $this->getStats()['active']['value'] }}</span>
                    
                </div>
            </a>

            {{-- Inactive Homeowners --}}
            <a href="{{ $this->getStats()['inactive']['url'] }}" class="flex items-center gap-4 p-4 transition rounded-lg hover:bg-gray-50 dark:hover:bg-white/5">
                <div class="p-3 rounded-full bg-danger-50 dark:bg-danger-900/20">
                    <x-heroicon-m-minus-circle class="w-8 h-8 text-danger-600 dark:text-danger-400" />
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Inactive Homeowners</span>
                    <span class="text-2xl font-bold text-danger-600 dark:text-danger-400">{{ $this->getStats()['inactive']['value'] }}</span>
                    
                </div>
            </a>

            {{-- Suspended Homeowners --}}
            <a href="{{ $this->getStats()['suspended']['url'] }}" class="flex items-center gap-4 p-4 transition rounded-lg hover:bg-gray-50 dark:hover:bg-white/5">
                <div class="p-3 rounded-full bg-warning-50 dark:bg-warning-900/20">
                    <x-heroicon-m-x-circle class="w-8 h-8 text-warning-600 dark:text-warning-400" />
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Suspended Homeowners</span>
                    <span class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $this->getStats()['suspended']['value'] }}</span>
                    
                </div>
            </a>
        </div>
    </x-filament::section>
</x-filament::widget>
