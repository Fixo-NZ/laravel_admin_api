<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Models\Tradie;

class TradieProfile extends Page
{
    // Do not show this page in navigation
    protected static ?string $navigationLabel = null;

    // Register route under a safe path that won't collide with the public controller route
    protected static ?string $route = 'tradies/profile/{record}';

    protected static string $view = 'filament.admin.pages.tradie-profile-page';

    public Tradie $tradie;

    public function mount($record = null): void
    {
        $id = $record ?? request('record');

        if (! $id) {
            abort(404);
        }

        $this->tradie = Tradie::with('jobs')->findOrFail($id);
    }
}
