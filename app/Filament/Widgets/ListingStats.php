<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\EarlyAccessSignup;
use App\Models\Inquiry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListingStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $real = fn () => Business::real();

        return [
            Stat::make('Drafts to review', $real()->where('status', 'draft')->count())
                ->description($real()->where('status', 'draft')->where('needs_review', true)->count().' flagged by QA'),
            Stat::make('Published listings', $real()->whereIn('status', Business::VISIBLE_STATUSES)->count())
                ->description(Business::sample()->count().' sample rows power the public preview'),
            Stat::make('Open inquiries', Inquiry::where('status', 'received')->count()),
            Stat::make('Early-access signups', EarlyAccessSignup::count())
                ->description(EarlyAccessSignup::whereNull('invited_at')->count().' not invited yet'),
        ];
    }
}
