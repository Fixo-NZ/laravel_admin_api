<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Homeowner;
use App\Models\Tradie;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

use App\Filament\Admin\Pages\HomeownerPage;
use App\Filament\Admin\Pages\TradiePage;
use App\Filament\Admin\Pages\AdminPage;

class DashboardUserSummaryStatsWidget extends BaseWidget
{
    // Make this widget full-width, and let it render multiple Stat cards in a grid
    protected int|string|array $columnSpan = 12;

    protected function getColumns(): int
    {
        // 3 cards across (Homeowners / Tradies / Admins)
        return 3;
    }

    protected function getStats(): array
    {
        $homeStatus = $this->getCounts(Homeowner::query(), 'status');

        $tradieStatus = $this->getCounts(Tradie::query(), 'status');
        $tradieAvail  = $this->getCounts(Tradie::query(), 'availability_status');

        $adminStatus = $this->getCounts(User::query()->where('role', 'admin'), 'status');

        return [
            // Homeowners card
            Stat::make('Homeowners', number_format(array_sum($homeStatus)))
                ->icon('heroicon-m-user-group')
                ->color('info')

                ->description(new HtmlString(
                    "<div class='mt-2 flex flex-wrap gap-2'>
                        {$this->pillLink('Active', $homeStatus['active'] ?? 0, 'active', HomeownerPage::getUrl(), 'status')}
                        {$this->pillLink('Inactive', $homeStatus['inactive'] ?? 0, 'inactive', HomeownerPage::getUrl(), 'status')}
                        {$this->pillLink('Suspended', $homeStatus['suspended'] ?? 0, 'suspended', HomeownerPage::getUrl(), 'status')}
                    </div>"
                )),

            // Tradies card (split)
            Stat::make('Tradies', number_format(array_sum($tradieStatus)))
                ->icon('heroicon-m-wrench-screwdriver')
                ->color('warning')

                ->description(new HtmlString(
                    "<div class='mt-2 space-y-3'>

                        <div>
                            <div class='text-[11px] font-semibold text-gray-500 mb-1'>Status</div>
                            <div class='flex flex-wrap gap-2'>
                                {$this->pillLink('Active', $tradieStatus['active'] ?? 0, 'active', TradiePage::getUrl(), 'status')}
                                {$this->pillLink('Inactive', $tradieStatus['inactive'] ?? 0, 'inactive', TradiePage::getUrl(), 'status')}
                                {$this->pillLink('Suspended', $tradieStatus['suspended'] ?? 0, 'suspended', TradiePage::getUrl(), 'status')}
                                {$this->pillLink('Pending', $tradieStatus['pending'] ?? 0, 'pending', TradiePage::getUrl(), 'status')}
                            </div>
                        </div>

                        <div>
                            <div class='text-[11px] font-semibold text-gray-500 mb-1'>Availability</div>
                            <div class='flex flex-wrap gap-2'>
                                {$this->pillLink('Available', $tradieAvail['available'] ?? 0, 'available', TradiePage::getUrl(), 'availability_status')}
                                {$this->pillLink('Busy', $tradieAvail['busy'] ?? 0, 'busy', TradiePage::getUrl(), 'availability_status')}
                                {$this->pillLink('Unavailable', $tradieAvail['unavailable'] ?? 0, 'unavailable', TradiePage::getUrl(), 'availability_status')}
                            </div>
                        </div>

                    </div>"
                )),

            // Admins card (admins only)
            Stat::make('Admins', number_format(array_sum($adminStatus)))
                ->icon('heroicon-m-shield-check')
                ->color('success')

                ->description(new HtmlString(
                    "<div class='mt-2 flex flex-wrap gap-2'>
                        {$this->pillLink('Active', $adminStatus['active'] ?? 0, 'active', AdminPage::getUrl(), 'status')}
                        {$this->pillLink('Inactive', $adminStatus['inactive'] ?? 0, 'inactive', AdminPage::getUrl(), 'status')}
                        {$this->pillLink('Suspended', $adminStatus['suspended'] ?? 0, 'suspended', AdminPage::getUrl(), 'status')}
                    </div>"
                )),
        ];
    }

    private function pillLink(
        string $label,
        int $value,
        string $state,
        string $baseUrl,
        string $param = 'status'
    ): string {
        $map = [
            // Status
            'active'      => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'inactive'    => 'bg-gray-50 text-gray-700 ring-gray-200',
            'suspended'   => 'bg-rose-50 text-rose-700 ring-rose-200',
            'pending'     => 'bg-amber-50 text-amber-800 ring-amber-200',

            // Availability
            'available'   => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'busy'        => 'bg-amber-50 text-amber-800 ring-amber-200',
            'unavailable' => 'bg-rose-50 text-rose-700 ring-rose-200',
        ];

        $tone = $map[$state] ?? $map['inactive'];
        $disabled = $value <= 0;

        $href = $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . $param . '=' . urlencode($state);

        $wrapClass = $disabled
            ? "opacity-50 cursor-not-allowed pointer-events-none"
            : "hover:ring-2 hover:ring-offset-1 hover:ring-gray-300 transition";

        return "
            <a href='{$href}' title='Filter ".e($label)."' class='inline-flex {$wrapClass}' aria-disabled='".($disabled ? 'true' : 'false')."'>
                <span class='inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium ring-1 ring-inset {$tone}'>
                    <span>".e($label)."</span>
                    <span class='mx-1 opacity-60'>:</span>
                    <span class='font-semibold tabular-nums'>".number_format($value)."</span>
                </span>
            </a>
        ";
    }

    private function getCounts($query, string $column): array
    {
        return $query
            ->selectRaw("{$column} as k, COUNT(*) as total")
            ->groupBy('k')
            ->pluck('total', 'k')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }
}
