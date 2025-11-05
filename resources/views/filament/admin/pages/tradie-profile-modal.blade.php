<x-filament::card class="max-w-3xl mx-auto">
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">
            {{ $tradie->first_name }} {{ $tradie->last_name }} — Profile Overview
        </h2>
    </x-slot>

    <!-- ==================== Profile Info ==================== -->
    <x-filament::section heading="Personal Details">
        <dl class="divide-y divide-gray-200">
            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">First Name</dt>
                <dd>{{ $tradie->first_name }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Middle Name</dt>
                <dd>{{ $tradie->middle_name }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Last Name</dt>
                <dd>{{ $tradie->last_name }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Email</dt>
                <dd><a href="mailto:{{ $tradie->email }}" class="text-primary-600 hover:underline">{{ $tradie->email }}</a></dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Phone</dt>
                <dd>{{ $tradie->phone ?? 'N/A' }}</dd>
            </div>
        </dl>
    </x-filament::section>

    <!-- ==================== Business Info ==================== -->
    <x-filament::section heading="Business Information">
        <dl class="divide-y divide-gray-200">
            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Business Name</dt>
                <dd>{{ $tradie->business_name ?? 'N/A' }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">License Number</dt>
                <dd>{{ $tradie->license_number ?? 'N/A' }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Years of Experience</dt>
                <dd>{{ $tradie->years_experience ?? 'N/A' }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Hourly Rate</dt>
                <dd class="font-semibold text-success-600">NZD ${{ number_format($tradie->hourly_rate, 2) ?? 'N/A' }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Service Radius</dt>
                <dd>{{ $tradie->service_radius ?? 'N/A' }} km</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Availability</dt>
                <dd>{{ ucfirst($tradie->availability_status) ?? 'N/A' }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Status</dt>
                <dd>
                    <x-filament::badge 
                        color="{{ $tradie->status === 'active' ? 'success' : ($tradie->status === 'suspended' ? 'warning' : 'danger') }}">
                        {{ ucfirst($tradie->status) }}
                    </x-filament::badge>
                </dd>
            </div>
        </dl>
    </x-filament::section>

    <!-- ==================== Location ==================== -->
    <x-filament::section heading="Location Details">
        <dl class="divide-y divide-gray-200">
            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Address</dt>
                <dd>{{ $tradie->address ?? 'N/A' }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">City</dt>
                <dd>{{ $tradie->city ?? 'N/A' }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Postal Code</dt>
                <dd>{{ $tradie->postal_code ?? 'N/A' }}</dd>
            </div>

            <div class="flex justify-between py-2">
                <dt class="font-medium text-gray-700">Region</dt>
                <dd>{{ $tradie->region ?? 'N/A' }}</dd>
            </div>
        </dl>
    </x-filament::section>

    <!-- ==================== Insurance ==================== -->
    <x-filament::section heading="Insurance Details">
        <p>{{ $tradie->insurance_details ?? 'N/A' }}</p>
    </x-filament::section>

    <!-- ==================== Suspension Info ==================== -->
    @if (strtolower($tradie->status) === 'suspended')
        <x-filament::section heading="Suspension Details" icon="heroicon-o-exclamation-triangle" class="border-t pt-4">
            <dl class="divide-y divide-gray-200">
                <div class="flex justify-between py-2">
                    <dt class="font-medium text-gray-700">Reason</dt>
                    <dd>{{ $tradie->suspension_reason ?? 'N/A' }}</dd>
                </div>
                    <dd>
                        {{ $tradie->suspension_start 
                            ? \Carbon\Carbon::parse($tradie->suspension_start)->format('M d, Y') 
                            : 'N/A' }}
                    </dd>
                <dd>
                    {{ $tradie->suspension_end 
                        ? \Carbon\Carbon::parse($tradie->suspension_end)->format('M d, Y') 
                        : 'N/A' }}
                </dd>
            </dl>
        </x-filament::section>
    @endif
</x-filament::card>