<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BuyBox;
use App\Models\DealBox;
use App\Models\User;
use App\Models\WatchlistItem;
use App\Services\Listings\BuyBoxMatcher;
use App\Services\Listings\DealBoxCriteria;
use App\Services\Listings\ListingFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * The dashboard pages for signed-in accounts: real, published listings only,
 * matched against the account's own buy box. (Guests get the sample preview
 * from DashboardController.)
 */
class RealDashboardController extends Controller
{
    public function __construct(
        private readonly ListingFeed $feed,
        private readonly BuyBoxMatcher $matcher,
    ) {}

    public function index(User $user, BuyBox $box): View
    {
        $published = $this->feed->for($user)->count();
        $matches = $this->rankedMatches($user, $box);

        $asking = $matches->pluck('listing.asking_price')->filter()->sort()->values();
        $multiples = $matches->map(fn ($m) => $m['listing']->metrics()->priceToEarnings())->filter()->sort()->values();

        $metrics = [
            ['label' => 'Published listings', 'value' => number_format($published), 'icon' => 'briefcase', 'type' => 'emerald'],
            ['label' => $this->matcher->hasCriteria($box) ? 'Match your buy box' : 'Match your buy box (not set up)', 'value' => number_format($this->matchCount($user, $box)), 'icon' => 'chart', 'type' => 'emerald'],
            ['label' => 'Median asking price (top matches)', 'value' => $asking->isEmpty() ? '—' : Business::formatCompactCurrency($asking->median()), 'icon' => 'dollar', 'type' => 'gold'],
            ['label' => 'Median price / earnings (top matches)', 'value' => $multiples->isEmpty() ? '—' : number_format($multiples->median(), 1).'x', 'icon' => 'star', 'type' => 'emerald'],
        ];

        return view('dashboard.real.index', [
            'userName' => $user->name,
            'userEmail' => $user->email,
            'metrics' => $metrics,
            'published' => $published,
            'criteria' => $this->matcher->criteria($box),
            'matchCount' => $this->matchCount($user, $box),
            'cards' => $matches->take(6)->map(fn ($m) => $m['listing']->toRealCardArray($m['match']))->values()->all(),
            'savedIds' => $this->savedIds($user),
        ]);
    }

    public function discover(Request $request, User $user, BuyBox $box): View
    {
        $base = $this->feed->for($user);
        $query = clone $base;

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sub_industry', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }
        $industry = (string) $request->query('industry', '');
        if ($industry !== '') {
            $query->where('industry', $industry);
        }
        $state = strtoupper((string) $request->query('state', ''));
        if ($state !== '') {
            $query->where('state', $state);
        }
        if ($request->boolean('matches') && $this->matcher->hasCriteria($box)) {
            $this->matcher->apply($query, $box);
        }

        $sort = (string) $request->query('sort', 'newest');
        match ($sort) {
            'price_low' => $query->orderByRaw('asking_price is null')->orderBy('asking_price'),
            'price_high' => $query->orderByRaw('asking_price is null')->orderByDesc('asking_price'),
            'revenue_high' => $query->orderByRaw('annual_revenue is null')->orderByDesc('annual_revenue'),
            'earnings_high' => $query->orderByRaw('earnings is null')->orderByDesc('earnings'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };

        $page = $query->paginate(20)->withQueryString();
        $hasCriteria = $this->matcher->hasCriteria($box);
        $cards = collect($page->items())->map(fn (Business $listing) => $listing->toRealCardArray(
            $hasCriteria ? $this->matcher->evaluate($listing, $box) : null
        ))->all();

        return view('dashboard.real.discover', [
            'userName' => $user->name,
            'userEmail' => $user->email,
            'page' => $page,
            'cards' => $cards,
            'total' => (clone $base)->count(),
            'industries' => (clone $base)->distinct()->orderBy('industry')->pluck('industry')->filter()->values()->all(),
            'states' => (clone $base)->distinct()->orderBy('state')->pluck('state')->filter()->values()->all(),
            'filters' => ['q' => $search, 'industry' => $industry, 'state' => $state, 'sort' => $sort, 'matches' => $request->boolean('matches')],
            'hasCriteria' => $hasCriteria,
            'savedIds' => $this->savedIds($user),
        ]);
    }

    public function show(User $user, BuyBox $box, int|string|null $id): View
    {
        $listing = $this->feed->for($user)->findOrFail($id);
        $metrics = $listing->metrics();
        $money = fn (?int $v) => $v === null ? 'Not disclosed' : Business::formatCompactCurrency($v);
        $ratio = fn (?float $v) => $v === null ? 'Not disclosed' : number_format($v, 1).'x';

        $facts = array_filter([
            'Listing type' => $listing->listing_type,
            'Industry' => trim($listing->industry.($listing->sub_industry ? ' · '.$listing->sub_industry : '')),
            'Franchise / brand' => $listing->brand,
            'Established' => $listing->established_year,
            'Employees' => $listing->employees,
            'Square feet' => $listing->square_feet ? number_format($listing->square_feet) : null,
            'Real estate' => $listing->real_estate && $listing->real_estate !== 'Unknown' ? $listing->real_estate : null,
            'Real estate in price' => $listing->real_estate_in_price && $listing->real_estate_in_price !== 'Unknown' ? $listing->real_estate_in_price : null,
            'Monthly rent' => $listing->monthly_rent ? '$'.number_format($listing->monthly_rent) : null,
            'Equipment (FF&E)' => $listing->ffe_value ? $money($listing->ffe_value).($listing->ffe_in_price ? ' (in price: '.$listing->ffe_in_price.')' : '') : null,
            'Inventory' => $listing->inventory_value ? $money($listing->inventory_value).($listing->inventory_in_price ? ' (in price: '.$listing->inventory_in_price.')' : '') : null,
            'Seller financing' => $listing->seller_financing ? $listing->seller_financing.($listing->seller_financing_percent ? ' ('.rtrim(rtrim(number_format($listing->seller_financing_percent, 1), '0'), '.').'%)' : '') : null,
            'SBA mentioned' => $listing->sba_mentioned ? 'Yes' : null,
            'Reason for sale' => $listing->reason_for_sale,
            'Broker' => $listing->broker_name,
        ], fn ($v) => $v !== null && $v !== '');

        return view('dashboard.real.listing', [
            'userName' => $user->name,
            'userEmail' => $user->email,
            'listing' => $listing,
            'card' => $listing->toRealCardArray($this->matcher->hasCriteria($box) ? $this->matcher->evaluate($listing, $box) : null),
            'figures' => [
                ['label' => 'Asking price', 'value' => $money($listing->asking_price)],
                ['label' => 'Revenue', 'value' => $money($listing->annual_revenue)],
                ['label' => $listing->earningsLabel(), 'value' => $money($listing->earnings)],
                ['label' => 'Price / earnings', 'value' => $ratio($metrics->priceToEarnings())],
                ['label' => 'Earnings margin', 'value' => $listing->margin_label],
                ['label' => 'Price / revenue', 'value' => $ratio($metrics->priceToRevenue())],
            ],
            'facts' => $facts,
            'cashNeeded' => $metrics->cashNeeded(),
            'inWatchlist' => WatchlistItem::where('user_id', $user->id)->where('business_id', $listing->id)->exists(),
        ]);
    }

    public function buyBox(User $user, BuyBox $box): View
    {
        $base = $this->feed->for($user);
        $results = $this->dealBoxResults($user, $box);
        $pairs = (clone $base)->whereNotNull('county')->whereNotNull('state')
            ->distinct()->orderBy('state')->orderBy('county')->get(['state', 'county']);

        return view('dashboard.real.buy-box', [
            'userName' => $user->name,
            'userEmail' => $user->email,
            ...$results,
            'dealBox' => [
                'criteria' => DealBoxCriteria::toArray($box),
                'defaults' => DealBoxCriteria::DEFAULTS,
                'boxes' => $this->savedBoxes($user),
                'options' => [
                    'industries' => BuyBoxMatcher::INDUSTRIES,
                    'states' => collect((clone $base)->distinct()->pluck('state'))->merge($box->states ?? [])->filter()->unique()->sort()->values()->all(),
                    'counties' => $pairs->map(fn ($p) => ['state' => $p->state, 'county' => $p->county])
                        ->merge(collect($box->counties ?? [])->map(fn ($c) => ['state' => null, 'county' => $c]))
                        ->unique('county')->values()->all(),
                    'sources' => collect(DealBoxCriteria::SOURCES)->merge((clone $base)->distinct()->pluck('source'))->filter()->unique()->values()->all(),
                ],
                'urls' => [
                    'results' => route('dealbox.results'),
                    'save' => route('dealbox.store'),
                    'delete' => url('/deal-boxes'),
                ],
            ],
        ]);
    }

    /**
     * The deal box list: listings the criteria don't rule out, best fit first
     * (or newest first when no criteria are set).
     *
     * @return array{matchCount: int, cards: list<array>, criteria: array<string, string>, savedIds: list<int>}
     */
    public function dealBoxResults(User $user, BuyBox $box, int $take = 20): array
    {
        $hasCriteria = $this->matcher->hasCriteria($box);
        $listings = $this->matcher->apply($this->feed->for($user), $box)
            ->orderByDesc('published_at')->orderByDesc('id')
            ->limit($hasCriteria ? 500 : $take)
            ->get();

        $ranked = $listings
            ->map(fn (Business $listing) => ['listing' => $listing, 'match' => $hasCriteria ? $this->matcher->evaluate($listing, $box) : null])
            ->sortByDesc(fn ($m) => [
                $m['match']['score'] ?? 0,
                count(array_filter([$m['listing']->asking_price, $m['listing']->annual_revenue, $m['listing']->earnings], fn ($v) => $v !== null)),
                $m['listing']->published_at?->timestamp ?? 0,
            ])
            ->values();

        return [
            'matchCount' => $this->matcher->apply($this->feed->for($user), $box)->count(),
            'cards' => $ranked->take($take)->map(fn ($m) => $m['listing']->toRealCardArray($m['match']))->values()->all(),
            'criteria' => $this->matcher->criteria($box),
            'savedIds' => $this->savedIds($user),
        ];
    }

    /** @return list<array{id: int, name: string, criteria: array}> */
    public function savedBoxes(User $user): array
    {
        return DealBox::where('user_id', $user->id)->orderBy('name')->get()
            ->map(fn (DealBox $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'criteria' => array_merge(DealBoxCriteria::DEFAULTS, array_intersect_key($b->criteria ?? [], DealBoxCriteria::DEFAULTS)),
            ])->all();
    }

    public function watchlist(User $user, BuyBox $box): View
    {
        $hasCriteria = $this->matcher->hasCriteria($box);
        $listings = $this->feed->for($user)
            ->whereIn('id', WatchlistItem::where('user_id', $user->id)->select('business_id'))
            ->orderBy('name')
            ->get();
        $cards = $listings->map(fn (Business $l) => $l->toRealCardArray($hasCriteria ? $this->matcher->evaluate($l, $box) : null))->all();
        $asking = $listings->pluck('asking_price')->filter();

        return view('dashboard.real.watchlist', [
            'userName' => $user->name,
            'userEmail' => $user->email,
            'cards' => $cards,
            'totalAsking' => $asking->isEmpty() ? '—' : Business::formatCompactCurrency($asking->sum()),
            'undisclosed' => $listings->count() - $asking->count(),
        ]);
    }

    /**
     * Listings the buy box doesn't rule out, best fit first, then newest.
     *
     * @return Collection<int, array{listing: Business, match: array}>
     */
    private function rankedMatches(User $user, BuyBox $box): Collection
    {
        if (! $this->matcher->hasCriteria($box)) {
            return collect();
        }

        return $this->matcher->apply($this->feed->for($user), $box)
            ->orderByDesc('published_at')
            ->limit(500)
            ->get()
            ->map(fn (Business $listing) => ['listing' => $listing, 'match' => $this->matcher->evaluate($listing, $box)])
            // Best fit first; among equals, listings that disclose more figures, then newest.
            ->sortByDesc(fn ($m) => [
                $m['match']['score'],
                count(array_filter([$m['listing']->asking_price, $m['listing']->annual_revenue, $m['listing']->earnings], fn ($v) => $v !== null)),
                $m['listing']->published_at?->timestamp ?? 0,
            ])
            ->values();
    }

    /** The location line stored on the buy box (shown by older views). */
    public function criteriaLocation(BuyBox $box): string
    {
        return $this->matcher->criteria($box)['location'] ?? '';
    }

    public function matchCount(User $user, BuyBox $box): int
    {
        return $this->matcher->hasCriteria($box) ? $this->matcher->apply($this->feed->for($user), $box)->count() : 0;
    }

    /** @return list<int> */
    private function savedIds(User $user): array
    {
        return WatchlistItem::where('user_id', $user->id)->pluck('business_id')->all();
    }
}
