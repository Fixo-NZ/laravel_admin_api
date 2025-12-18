<x-filament::widget>
    <div class="grid grid-cols-4 gap-4">
            {{-- Total Homeowners --}}
            <a href="{{ $this->getStats()['total']['url'] }}" class="flex flex-col gap-2 p-5 transition bg-white rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Homeowners</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->getStats()['total']['value']) }}</div>
                
                @if(isset($this->getStats()['total']['change']) && $this->getStats()['total']['change'])
                    @php $change = $this->getStats()['total']['change']; @endphp
                    <div class="flex items-center gap-1 text-sm font-medium {{ $change['increase'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        <span>{{ $change['value'] }}% {{ $change['increase'] ? 'increase' : 'decrease' }}</span>
                        @if($change['increase'])
                            <x-heroicon-m-arrow-trending-up class="w-4 h-4" />
                        @else
                            <x-heroicon-m-arrow-trending-down class="w-4 h-4" />
                        @endif
                    </div>
                @endif

                @if(isset($this->getStats()['total']['chart']))
                    @php
                        $chart = $this->getStats()['total']['chart'];
                        $max = max($chart) ?: 1;
                        $points = [];
                        $width = 100;
                        $height = 30;
                        $count = count($chart);
                        
                        foreach ($chart as $index => $value) {
                            $x = ($index / max(1, $count - 1)) * $width;
                            $y = $height - (($value / $max) * $height);
                            $points[] = "{$x},{$y}";
                        }
                        $pointsStr = implode(' ', $points);
                        $isIncrease = isset($change) && $change['increase'];
                    @endphp
                    <div class="w-full h-12 mt-1">
                        <svg viewBox="0 0 100 30" class="w-full h-full {{ $isIncrease ? 'stroke-green-500' : 'stroke-red-500' }} fill-none" preserveAspectRatio="none">
                            <polyline points="{{ $pointsStr }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </a>

            {{-- Active Homeowners --}}
            <a href="{{ $this->getStats()['active']['url'] }}" class="flex flex-col gap-2 p-5 transition bg-white rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Homeowners</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->getStats()['active']['value']) }}</div>
                
                @if(isset($this->getStats()['active']['change']) && $this->getStats()['active']['change'])
                    @php $change = $this->getStats()['active']['change']; @endphp
                    <div class="flex items-center gap-1 text-sm font-medium {{ $change['increase'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        <span>{{ $change['value'] }}% {{ $change['increase'] ? 'increase' : 'decrease' }}</span>
                        @if($change['increase'])
                            <x-heroicon-m-arrow-trending-up class="w-4 h-4" />
                        @else
                            <x-heroicon-m-arrow-trending-down class="w-4 h-4" />
                        @endif
                    </div>
                @endif

                @if(isset($this->getStats()['active']['chart']))
                    @php
                        $chart = $this->getStats()['active']['chart'];
                        $max = max($chart) ?: 1;
                        $points = [];
                        $width = 100;
                        $height = 30;
                        $count = count($chart);
                        
                        foreach ($chart as $index => $value) {
                            $x = ($index / max(1, $count - 1)) * $width;
                            $y = $height - (($value / $max) * $height);
                            $points[] = "{$x},{$y}";
                        }
                        $pointsStr = implode(' ', $points);
                        $isIncrease = isset($change) && $change['increase'];
                    @endphp
                    <div class="w-full h-12 mt-1">
                        <svg viewBox="0 0 100 30" class="w-full h-full {{ $isIncrease ? 'stroke-green-500' : 'stroke-red-500' }} fill-none" preserveAspectRatio="none">
                            <polyline points="{{ $pointsStr }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </a>

            {{-- Inactive Homeowners --}}
            <a href="{{ $this->getStats()['inactive']['url'] }}" class="flex flex-col gap-2 p-5 transition bg-white rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Inactive Homeowners</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->getStats()['inactive']['value']) }}</div>
                
                @if(isset($this->getStats()['inactive']['change']) && $this->getStats()['inactive']['change'])
                    @php $change = $this->getStats()['inactive']['change']; @endphp
                    <div class="flex items-center gap-1 text-sm font-medium {{ $change['increase'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        <span>{{ $change['value'] }}% {{ $change['increase'] ? 'increase' : 'decrease' }}</span>
                        @if($change['increase'])
                            <x-heroicon-m-arrow-trending-up class="w-4 h-4" />
                        @else
                            <x-heroicon-m-arrow-trending-down class="w-4 h-4" />
                        @endif
                    </div>
                @endif

                @if(isset($this->getStats()['inactive']['chart']))
                    @php
                        $chart = $this->getStats()['inactive']['chart'];
                        $max = max($chart) ?: 1;
                        $points = [];
                        $width = 100;
                        $height = 30;
                        $count = count($chart);
                        
                        foreach ($chart as $index => $value) {
                            $x = ($index / max(1, $count - 1)) * $width;
                            $y = $height - (($value / $max) * $height);
                            $points[] = "{$x},{$y}";
                        }
                        $pointsStr = implode(' ', $points);
                        $isIncrease = isset($change) && $change['increase'];
                    @endphp
                    <div class="w-full h-12 mt-1">
                        <svg viewBox="0 0 100 30" class="w-full h-full {{ $isIncrease ? 'stroke-green-500' : 'stroke-red-500' }} fill-none" preserveAspectRatio="none">
                            <polyline points="{{ $pointsStr }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </a>

            {{-- Suspended Homeowners --}}
            <a href="{{ $this->getStats()['suspended']['url'] }}" class="flex flex-col gap-2 p-5 transition bg-white rounded-lg shadow-sm dark:bg-gray-800 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Suspended Homeowners</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($this->getStats()['suspended']['value']) }}</div>
                
                @if(isset($this->getStats()['suspended']['change']) && $this->getStats()['suspended']['change'])
                    @php $change = $this->getStats()['suspended']['change']; @endphp
                    <div class="flex items-center gap-1 text-sm font-medium {{ $change['increase'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        <span>{{ $change['value'] }}% {{ $change['increase'] ? 'increase' : 'decrease' }}</span>
                        @if($change['increase'])
                            <x-heroicon-m-arrow-trending-up class="w-4 h-4" />
                        @else
                            <x-heroicon-m-arrow-trending-down class="w-4 h-4" />
                        @endif
                    </div>
                @endif

                @if(isset($this->getStats()['suspended']['chart']))
                    @php
                        $chart = $this->getStats()['suspended']['chart'];
                        $max = max($chart) ?: 1;
                        $points = [];
                        $width = 100;
                        $height = 30;
                        $count = count($chart);
                        
                        foreach ($chart as $index => $value) {
                            $x = ($index / max(1, $count - 1)) * $width;
                            $y = $height - (($value / $max) * $height);
                            $points[] = "{$x},{$y}";
                        }
                        $pointsStr = implode(' ', $points);
                        $isIncrease = isset($change) && $change['increase'];
                    @endphp
                    <div class="w-full h-12 mt-1">
                        <svg viewBox="0 0 100 30" class="w-full h-full {{ $isIncrease ? 'stroke-green-500' : 'stroke-red-500' }} fill-none" preserveAspectRatio="none">
                            <polyline points="{{ $pointsStr }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                @endif
            </a>
    </div>
</x-filament::widget>
