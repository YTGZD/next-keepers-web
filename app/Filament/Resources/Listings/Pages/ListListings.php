<?php

namespace App\Filament\Resources\Listings\Pages;

use App\Filament\Resources\Imports\ListingImportResource;
use App\Filament\Resources\Listings\ListingResource;
use App\Models\Business;
use App\Services\Listings\ListingPublisher;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListListings extends ListRecords
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->publishAllShownAction(),
            ListingImportResource::importAction(),
            CreateAction::make()->label('Add listing'),
        ];
    }

    /**
     * Publish every draft the table currently shows, across all pages: the
     * active tab, filters and search decide which. Filter by Import to publish
     * one file's listings, or by state or industry to publish a slice.
     */
    protected function publishAllShownAction(): Action
    {
        return Action::make('publishAllShown')
            ->label(fn () => 'Publish all '.number_format(ListingPublisher::counts($this->getFilteredTableQuery())['drafts']).' drafts shown')
            ->icon(Heroicon::OutlinedEye)
            ->color('success')
            ->visible(fn () => ListingPublisher::drafts($this->getFilteredTableQuery())->exists())
            ->modalHeading('Publish all drafts shown')
            ->modalDescription(fn () => ListingPublisher::describe(ListingPublisher::counts($this->getFilteredTableQuery()), 'match the current tab, filters and search (on every page, not only this one)'))
            ->modalSubmitActionLabel('Publish')
            ->schema([
                Toggle::make('include_flagged')->label('Include listings flagged "Needs review"')->default(false),
            ])
            ->action(function (array $data): void {
                $count = ListingPublisher::publish($this->getFilteredTableQuery(), (bool) ($data['include_flagged'] ?? false));
                $this->deselectAllTableRecords();
                Notification::make()->success()->title('Published '.number_format($count).' '.($count === 1 ? 'listing' : 'listings'))->send();
            });
    }

    public function getTabs(): array
    {
        $count = fn (callable $scope) => $scope(Business::real())->count();

        return [
            'review' => Tab::make('Review queue')
                // A closure, so the count is taken at render time, after a bulk publish.
                ->badge(fn () => $count(fn (Builder $query) => $query->where('status', 'draft')))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', Business::VISIBLE_STATUSES)),
            'archived' => Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archived')),
            'all' => Tab::make('All'),
        ];
    }
}
