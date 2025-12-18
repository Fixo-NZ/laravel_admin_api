<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use App\Models\Homeowner;

class HomeownerProfile extends Page
{
	// Do not show this page in navigation
	protected static ?string $navigationLabel = null;

	// Register route under a safe path that won't collide with the public controller route
	protected static ?string $route = 'homeowners/profile/{record}';

	protected static string $view = 'filament.admin.pages.homeowner-profile-page';

	public Homeowner $homeowner;

	public function mount($record = null): void
	{
		$id = $record ?? request('record');

		if (! $id) {
			abort(404);
		}

		$this->homeowner = Homeowner::with('jobs')->findOrFail($id);
	}
}

