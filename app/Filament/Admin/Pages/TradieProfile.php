<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Models\Tradie;

class TradieProfile extends Page
{
    // Do not show this page in navigation
    protected static ?string $navigationLabel = null;

    // Register route under Filament admin (match slug expected by Filament views)
    protected static ?string $route = 'tradie-profile/{record}';

    protected static string $view = 'filament.admin.pages.tradie-profile-page';

    public Tradie $tradie;

    public function mount($record = null): void
    {
        $id = $record ?? request('record');

        if (! $id) {
            abort(404);
        }

        $this->tradie = Tradie::with(['bookings.service'])->findOrFail($id);
    }
}
