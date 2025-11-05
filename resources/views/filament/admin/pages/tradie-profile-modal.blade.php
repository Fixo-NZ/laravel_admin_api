<div class="p-6 bg-red-500 rounded-lg shadow-md w-full max-w-lg mx-auto">
    <!-- Modal Header -->
    <h2 class="text-xl font-bold text-gray-800 mb-4">{{ $tradie->name }} Profile</h2>

    <!-- Profile Content -->
    <div class="space-y-3 text-gray-700">
        <div class="flex justify-between">
            <span class="font-semibold">First Name:</span>
            <span>{{ $tradie->first_name }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Middle Name:</span>
            <span>{{ $tradie->middle_name }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Last Name:</span>
            <span>{{ $tradie->last_name }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Email:</span>
            <span class="text-blue-600 hover:underline">{{ $tradie->email }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Phone:</span>
            <span>{{ $tradie->phone ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Business Name:</span>
            <span>{{ $tradie->business_name ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">License Number:</span>
            <span>{{ $tradie->license_number ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Years of Experience:</span>
            <span>{{ $tradie->years_experience ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Hourly Rate:</span>
            <span>{{ $tradie->hourly_rate ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Address:</span>
            <span>{{ $tradie->address ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">City:</span>
            <span>{{ $tradie->city ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Postal Code:</span>
            <span>{{ $tradie->postal_code ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Region:</span>
            <span>{{ $tradie->region ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Service Radius:</span>
            <span>{{ $tradie->service_radius ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Availability Status:</span>
            <span>{{ $tradie->availability_status ?? 'N/A' }}</span>
        </div>

        <div class="flex justify-between">
            <span class="font-semibold">Status:</span>
            <span class="px-3 py-1 rounded-full text-sm
                {{ $tradie->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                {{ $tradie->status === 'inactive' ? 'bg-red-100 text-red-800' : '' }}
                {{ $tradie->status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                {{ ucfirst($tradie->status) }}
            </span>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 📋 Booked Jobs Section -->
    <!-- ========================================================= -->
    <div class="mt-8 border-t pt-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Insurance Details</h3>

        <p>{{ $tradie->insurance_details ?? 'N/A' }}</p>
    </div>
</div>
