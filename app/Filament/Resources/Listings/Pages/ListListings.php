<?php

namespace App\Filament\Resources\Listings\Pages;

use App\Filament\Resources\Listings\ListingResource;
use App\Models\Business;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListListings extends ListRecords
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add listing'),
        ];
    }

    public function getTabs(): array
    {
        $count = fn (callable $scope) => $scope(Business::real())->count();

        return [
            'review' => Tab::make('Review queue')
                ->badge($count(fn (Builder $query) => $query->where('status', 'draft')))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', Business::VISIBLE_STATUSES)),
            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived')),
            'all' => Tab::make('All'),
        ];
    }
}
