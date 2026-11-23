<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BuyBox;
use App\Models\Industry;
use App\Models\Inquiry;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\NewsletterSubscription;
use App\Models\PipelineDeal;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\WatchlistItem;
use App\Services\BuyBoxMatchingService;
use App\Services\Listings\BuyBoxMatcher;
use App\Services\Listings\DealBoxCriteria;
use App\Services\Listings\ListingFeed;
use App\Support\IndustryBenchmarks;
use App\Support\RegionalInsights;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BuyBoxMatchingService $matchingService,
        private readonly ListingFeed $feed,
        private readonly BuyBoxMatcher $matcher,
        private readonly RealDashboardController $real,
    ) {}

    /** Signed-in accounts see real listings; guests see the sample preview. */
    private function showsReal(User $user): bool
    {
        return $this->feed->showsRealListings($user);
    }

    public function index(Request $request): View
    {
        $user = $this->matchingService->resolveActiveUser();
        $buyBox = $this->matchingService->getOrCreateBuyBox($user);
        if ($this->showsReal($user)) {
            return $this->real->index($user, $buyBox);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        $businesses = $this->feed->for($user)
            ->orderByDesc('fit_score')
            ->get();

        $matchCount = $businesses->count();
        $avgFit = (int) round($businesses->avg('fit_score') ?: 0);
        $totalRevenue = (float) $businesses->sum('annual_revenue');

        $metrics = [
            ['label' => 'Businesses in this view', 'value' => number_format($businesses->count()), 'change' => null, 'icon' => 'briefcase', 'type' => 'emerald'],
            ['label' => 'High Fit Matches (80+)', 'value' => number_format($businesses->where('fit_score', '>=', 80)->count()), 'change' => null, 'icon' => 'chart', 'type' => 'emerald'],
            ['label' => 'Avg. Fit Score', 'value' => (string) $avgFit, 'change' => null, 'icon' => 'star', 'type' => 'gold'],
            ['label' => 'Combined Revenue (Est.)', 'value' => $this->shortMoney($totalRevenue), 'change' => null, 'icon' => 'dollar', 'type' => 'emerald'],
        ];

        $categories = [
            'all' => 'All',
            'hvac' => 'HVAC & Restoration',
            'healthcare' => 'Healthcare',
            'manufacturing' => 'Manufacturing',
            'services' => 'Business Services',
            'retail' => 'Retail',
        ];

        $opportunities = $businesses->take(4)->map(function (Business $biz) use ($buyBox) {
            $eval = $this->matchingService->evaluate($biz, $buyBox);

            return $biz->toDashboardOpportunityArray($biz->fit_score ?: $eval['score']);
        })->values()->all();

        $keyInsights = [
            [
                'metric' => '+11.2%',
                'title' => 'Business Acquisition Activity',
                'subtitle' => 'illustrative sample figure',
                'theme' => 'emerald',
            ],
            [
                'metric' => '+8.7%',
                'title' => 'Housing Permits',
                'subtitle' => 'illustrative sample figure',
                'theme' => 'gold',
            ],
            [
                'metric' => '+6.1%',
                'title' => 'SBA Lending',
                'subtitle' => 'illustrative sample figure',
                'theme' => 'emerald',
            ],
        ];

        $mapFile = resource_path('data/us-map.json');
        $mapData = file_exists($mapFile) ? json_decode(file_get_contents($mapFile), true) : null;

        return view('dashboard.index', compact(
            'userName',
            'userEmail',
            'metrics',
            'categories',
            'opportunities',
            'keyInsights',
            'mapData',
            'matchCount'
        ));
    }

    public function showBusiness(Request $request, $id = 1): View
    {
        $user = $this->matchingService->resolveActiveUser();
        $buyBox = $this->matchingService->getOrCreateBuyBox($user);
        if ($this->showsReal($user)) {
            return $this->real->show($user, $buyBox, $id);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        // The preview only ever shows published sample rows, even by direct id.
        $businessModel = $this->feed->for($user)->with(['financials', 'regionalMarket'])->find($id)
            ?? $this->feed->for($user)->with(['financials', 'regionalMarket'])->firstOrFail();

        $business = $businessModel->toDetailArray();

        $inWatchlist = WatchlistItem::where('user_id', $user->id)
            ->where('business_id', $businessModel->id)
            ->exists();

        $inPipeline = PipelineDeal::where('user_id', $user->id)
            ->where('business_id', $businessModel->id)
            ->exists();

        return view('dashboard.business-detail', compact(
            'userName',
            'userEmail',
            'business',
            'inWatchlist',
            'inPipeline'
        ));
    }

    public function buyBox(Request $request): View
    {
        $user = $this->matchingService->resolveActiveUser();
        $buyBox = $this->matchingService->getOrCreateBuyBox($user);
        if ($this->showsReal($user)) {
            return $this->real->buyBox($user, $buyBox);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        $criteria = $buyBox->toCriteriaArray();

        $businesses = $this->feed->for($user)
            ->orderByDesc('fit_score')
            ->get();

        $matches = $businesses->take(3)->map(function (Business $biz) use ($buyBox) {
            $eval = $this->matchingService->evaluate($biz, $buyBox);

            return $biz->toBuyBoxMatchArray($biz->fit_score ?: $eval['score'], $biz->criteria_met ?? $eval['criteria_met']);
        })->values()->all();

        $savedIds = WatchlistItem::where('user_id', $user->id)
            ->pluck('business_id')
            ->all();

        $analytics = [
            'total_matches' => $businesses->count(),
            'avg_fit_score' => (int) round($businesses->avg('fit_score') ?: 0),
            'high_fit' => $businesses->where('fit_score', '>=', 80)->count(),
            'avg_asking_price' => $businesses->count() ? $this->shortMoney((float) $businesses->avg('asking_price')) : '—',
            'new_this_month' => $businesses->filter(fn ($b) => $b->created_at && $b->created_at->gte(now()->startOfMonth()))->count(),
            'top_region' => $businesses->groupBy('state')->sortByDesc(fn ($g) => $g->count())->keys()->first() ?: '—',
        ];

        return view('dashboard.buy-box', compact(
            'userName',
            'userEmail',
            'criteria',
            'analytics',
            'matches',
            'savedIds'
        ));
    }

    public function updateBuyBox(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();
        $buyBox = $this->matchingService->getOrCreateBuyBox($user);
        if ($this->showsReal($user)) {
            return $this->updateRealBuyBox($request, $buyBox);
        }

        $validated = $request->validate([
            'industries' => ['nullable', 'array'],
            'industries.*' => ['string', 'max:80'],
            'location' => ['nullable', 'string', 'max:140'],
            'revenue_range' => ['nullable', 'string', 'max:60'],
            'sde_range' => ['nullable', 'string', 'max:60'],
            'asking_price' => ['nullable', 'string', 'max:60'],
            'profit_margin' => ['nullable', 'string', 'max:40'],
            'employees' => ['nullable', 'string', 'max:40'],
            'profitable_only' => ['nullable', 'boolean'],
            'owner_operated' => ['nullable', 'boolean'],
            'growing_market' => ['nullable', 'boolean'],
            'absentee_owner_ok' => ['nullable', 'boolean'],
        ]);

        $buyBox->fill(array_filter($validated, fn ($v) => $v !== null));
        $buyBox->save();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Buy Box criteria updated.',
                'criteria' => $buyBox->toCriteriaArray(),
            ]);
        }

        return redirect()->route('buybox')->with('status', 'Buy Box criteria updated.');
    }

    /** Save the criteria real listings are matched on (see BuyBoxMatcher). */
    private function updateRealBuyBox(Request $request, BuyBox $buyBox): JsonResponse|RedirectResponse
    {
        $validated = $request->validate(DealBoxCriteria::rules() + ['counties.*' => ['string', 'max:80']]);

        $buyBox->forceFill(DealBoxCriteria::attributes($validated));
        $buyBox->location = $this->matcher->criteria($buyBox)['location'] ?? '';
        $buyBox->save();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Buy box saved.', 'criteria' => $this->matcher->criteria($buyBox)]);
        }

        return redirect()->route('buybox')->with('status', 'Buy box saved.');
    }

    public function marketIntelligence(Request $request): View
    {
        $user = $this->matchingService->resolveActiveUser();
        $userName = $user->name;
        $userEmail = $user->email;

        $kpis = [
            [
                'value' => '4.8M',
                'label' => 'Total Businesses in the U.S.',
                'change' => '+1.2%',
                'icon' => 'store',
            ],
            [
                'value' => '6.7%',
                'label' => 'Business Growth Rate (last 12 months)',
                'change' => '+0.4%',
                'icon' => 'chart',
            ],
            [
                'value' => '$1.3M',
                'label' => 'Avg. Business Value (Asking Price)',
                'change' => '+6.1%',
                'icon' => 'briefcase',
            ],
            [
                'value' => '15.2%',
                'label' => 'Acquisition Activity (Seekers)',
                'change' => '+2.3%',
                'icon' => 'handshake',
            ],
        ];

        $topIndustries = Industry::where('is_top_industry', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Industry $ind) => [
                'name' => $ind->name,
                'share' => number_format($ind->market_share_percent, 1).'%',
                'icon' => $ind->icon,
                'color' => $ind->color,
            ])
            ->values()
            ->all();

        $keyInsights = [
            'Business activity is up 6.7% nationwide, with strong demand in healthcare and professional services.',
            'The Northeast shows the highest growth in acquisition opportunities (+12.4%).',
            'Technology-enabled businesses continue to attract premium valuations.',
        ];

        $mapFile = resource_path('data/us-map.json');
        $mapData = file_exists($mapFile) ? json_decode(file_get_contents($mapFile), true) : null;

        $benchmarks = (new IndustryBenchmarks)->toArray();
        // ?industry=<BizBuySell subsector> opens the benchmark chart on that industry (linked from listings).
        $requested = (string) $request->query('industry', '');
        if ($requested !== '' && in_array($requested, array_column($benchmarks['rows'], 'subsector'), true)) {
            $benchmarks['defaultIndustry'] = $requested;
        }
        $regional = (new RegionalInsights)->toArray();

        return view('dashboard.market-intelligence', compact(
            'userName',
            'userEmail',
            'kpis',
            'topIndustries',
            'keyInsights',
            'mapData',
            'benchmarks',
            'regional'
        ));
    }

    public function watchlist(Request $request): View
    {
        $user = $this->matchingService->resolveActiveUser();
        if ($this->showsReal($user)) {
            return $this->real->watchlist($user, $this->matchingService->getOrCreateBuyBox($user));
        }
        $userName = $user->name;
        $userEmail = $user->email;

        $filter = $request->query('filter', 'all');
        $sortBy = $request->query('sort', 'recently_added');

        $watchlistModels = WatchlistItem::with('business')
            ->where('user_id', $user->id)
            ->get();

        $items = $watchlistModels->map(fn (WatchlistItem $item) => $item->toWatchlistArray())->values()->all();

        $totalAsking = $watchlistModels->sum(fn (WatchlistItem $item) => $item->business?->asking_price ?? 0);
        $avgFit = (int) round($watchlistModels->avg('fit_score') ?: 89);

        $metrics = [
            'saved_businesses' => max(count($items), 7),
            'avg_fit_score' => $avgFit.'%',
            'total_value' => $totalAsking > 0 ? Business::formatCompactCurrency($totalAsking) : '$8.7M',
            'growth_rate' => '+12%',
        ];

        return view('dashboard.watchlist', compact(
            'userName',
            'userEmail',
            'metrics',
            'items',
            'filter',
            'sortBy'
        ));
    }

    public function toggleWatchlist(Request $request, int $businessId): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();
        $business = $this->feed->for($user)->findOrFail($businessId);

        $existing = WatchlistItem::where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $inWatchlist = false;
        } else {
            WatchlistItem::create([
                'user_id' => $user->id,
                'business_id' => $business->id,
                'tag' => $business->fit_score >= 90 ? 'High Fit' : 'Watchlist',
                'tag_type' => $business->fit_score >= 90 ? 'green' : 'teal',
                'tag_icon' => $business->fit_score >= 90 ? 'star' : 'bookmark',
                'category' => $business->fit_score >= 90 ? 'high_fit' : 'recently_added',
                'added_time_label' => 'Added just now',
                'fit_score' => $business->fit_score,
            ]);
            $inWatchlist = true;
        }

        $count = WatchlistItem::where('user_id', $user->id)->count();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'in_watchlist' => $inWatchlist,
                'business_id' => $business->id,
                'count' => $count,
            ]);
        }

        return back()->with('status', $inWatchlist ? 'Added to Watchlist.' : 'Removed from Watchlist.');
    }

    public function removeWatchlistItem(Request $request, int $businessId): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();

        WatchlistItem::where('user_id', $user->id)
            ->where('business_id', $businessId)
            ->delete();

        $count = WatchlistItem::where('user_id', $user->id)->count();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'removed_business_id' => $businessId,
                'count' => $count,
            ]);
        }

        return back()->with('status', 'Removed from Watchlist.');
    }

    public function togglePipeline(Request $request, int $businessId): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();
        $business = $this->feed->for($user)->findOrFail($businessId);

        $existing = PipelineDeal::where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $inPipeline = false;
        } else {
            PipelineDeal::create([
                'user_id' => $user->id,
                'business_id' => $business->id,
                'stage' => 'saved',
                'nda_signed' => true,
            ]);
            $inPipeline = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'in_pipeline' => $inPipeline,
                'business_id' => $business->id,
            ]);
        }

        return back()->with('status', $inPipeline ? 'Added to Acquisition Pipeline.' : 'Removed from Pipeline.');
    }

    public function submitInquiry(Request $request, int $businessId): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();
        $business = $this->feed->for($user)->findOrFail($businessId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'buyer_entity' => ['nullable', 'string', 'max:160'],
            'type' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $inquiry = Inquiry::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'type' => $validated['type'] ?? 'dossier',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'buyer_entity' => $validated['buyer_entity'] ?? $user->buyer_type,
            'message' => $validated['message'] ?? 'Requested Confidential Information Memorandum (CIM) and NDA dossier.',
            'status' => 'received',
        ]);

        PipelineDeal::updateOrCreate(
            ['user_id' => $user->id, 'business_id' => $business->id],
            ['stage' => 'inquiry_sent', 'nda_signed' => false]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "Your inquiry for {$business->name} has been received.",
                'inquiry_id' => $inquiry->id,
            ], 201);
        }

        return back()->with('status', "Your inquiry for {$business->name} has been received.");
    }

    public function discover(Request $request): View
    {
        $user = $this->matchingService->resolveActiveUser();
        if ($this->showsReal($user)) {
            return $this->real->discover($request, $user, $this->matchingService->getOrCreateBuyBox($user));
        }
        $userName = $user->name;
        $userEmail = $user->email;

        $active = $this->feed->for($user);
        $totalOpportunities = (clone $active)->count();
        $highFitCount = (clone $active)->where('fit_score', '>=', 80)->count();
        $avgFitScore = $totalOpportunities ? (int) round((clone $active)->avg('fit_score')).'' : '0';

        $filters = [
            'industries' => ['Healthcare', 'HVAC', 'Restoration'],
            'location' => 'Within 50 miles',
            'revenue_range' => '$500K - $5M',
            'sde_range' => '$200K - $1.5M',
            'asking_price' => '$500K - $5M',
            'profit_margin' => '15%+',
            'employees' => '5+',
            'profitable_only' => true,
            'owner_operated' => false,
            'search' => (string) $request->query('q', ''),
        ];

        $query = $this->feed->for($user);

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('industry', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($industry = trim((string) $request->query('industry', ''))) {
            if (strtolower($industry) !== 'all' && ! str_contains(strtolower($industry), 'all industries')) {
                $query->where(function ($q) use ($industry) {
                    $q->where('industry', 'like', "%{$industry}%")
                        ->orWhere('category', 'like', "%{$industry}%");
                });
            }
        }

        if ($request->boolean('owner_operated')) {
            $query->where('is_owner_operated', true);
        }

        $sort = $request->query('sort', 'best_match');
        match ($sort) {
            'highest_revenue' => $query->orderByDesc('annual_revenue'),
            'price_low' => $query->orderBy('asking_price'),
            default => $query->orderByDesc('fit_score'),
        };

        $businesses = $query->get()
            ->map(fn (Business $biz) => $biz->toDiscoverArray())
            ->values()
            ->all();

        $savedIds = WatchlistItem::where('user_id', $user->id)
            ->pluck('business_id')
            ->all();

        return view('dashboard.discover', compact(
            'userName',
            'userEmail',
            'totalOpportunities',
            'highFitCount',
            'avgFitScore',
            'filters',
            'businesses',
            'savedIds'
        ));
    }

    public function settings(Request $request): View
    {
        $user = $this->matchingService->resolveActiveUser();
        $userName = $user->name;
        $userEmail = $user->email;

        $profile = $user->toProfileArray();

        $stats = [
            'active_buy_box_matches' => $this->showsReal($user)
                ? $this->real->matchCount($user, $this->matchingService->getOrCreateBuyBox($user))
                : $this->feed->for($user)->count(),
            'saved_opportunities' => WatchlistItem::where('user_id', $user->id)->count(),
            'viewed_deals' => (int) $user->viewed_deals_count,
            'inquiry_requests' => Inquiry::where('user_id', $user->id)->count(),
        ];

        return view('dashboard.settings', compact('userName', 'userEmail', 'profile', 'stats'));
    }

    public function updateProfile(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:140'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
            'location' => $validated['location'] ?? $user->location,
            'title' => $validated['title'] ?? $user->title,
            'company' => $validated['company'] ?? $user->company,
            'bio' => $validated['bio'] ?? $user->bio,
            'linkedin_url' => $validated['linkedin'] ?? $user->linkedin_url,
            'website_url' => $validated['website'] ?? $user->website_url,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Profile changes saved successfully.',
                'profile' => $user->fresh()->toProfileArray(),
            ]);
        }

        return redirect()->route('settings')->with('status', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();

        if ($request->has('two_factor_enabled') && ! $request->filled('new_password')) {
            $user->update([
                'two_factor_enabled' => $request->boolean('two_factor_enabled'),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'two_factor_enabled' => $user->two_factor_enabled,
                ]);
            }

            return redirect()->route('settings')->with('status', 'Security settings updated.');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'max:200', 'different:current_password'],
        ]);

        $user->update([
            'password' => $validated['new_password'], // hashed by the model cast
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated securely.',
            ]);
        }

        return redirect()->route('settings')->with('status', 'Password updated successfully.');
    }

    public function updateCriteria(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();

        $validated = $request->validate([
            'buyer_type' => ['nullable', 'string', 'max:100'],
            'target_deal_size' => ['nullable', 'string', 'max:80'],
            'min_sde' => ['nullable', 'string', 'max:80'],
            'capital_readiness' => ['nullable', 'string', 'max:160'],
            'focus_industries' => ['nullable', 'array'],
            'focus_industries.*' => ['string', 'max:80'],
        ]);

        $user->update(array_filter($validated, fn ($v) => $v !== null));

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Acquisition criteria updated.',
            ]);
        }

        return redirect()->route('settings')->with('status', 'Acquisition criteria updated.');
    }

    public function updateNotifications(Request $request): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();

        $user->update([
            'notify_instant_deals' => $request->boolean('instantDeals', true),
            'notify_weekly_intel' => $request->boolean('weeklyIntel', true),
            'notify_messages' => $request->boolean('messages', true),
            'notify_marketing' => $request->boolean('marketing', false),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Notification preferences saved.',
            ]);
        }

        return redirect()->route('settings')->with('status', 'Notification preferences saved.');
    }

    public function messages(Request $request): View
    {
        $user = $this->matchingService->resolveActiveUser();
        $userName = $user->name;
        $userEmail = $user->email;

        $threads = MessageThread::with('messages')
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get()
            ->map(fn (MessageThread $t) => $t->toThreadArray())
            ->values()
            ->all();

        return view('dashboard.messages', compact('userName', 'userEmail', 'threads'));
    }

    public function sendMessage(Request $request, int $threadId): JsonResponse|RedirectResponse
    {
        $user = $this->matchingService->resolveActiveUser();
        $thread = MessageThread::where('user_id', $user->id)->findOrFail($threadId);

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:4000'],
        ]);

        $timeLabel = 'Today '.now()->format('g:i A');

        $msg = Message::create([
            'message_thread_id' => $thread->id,
            'sender' => 'me',
            'name' => $user->name,
            'time_label' => $timeLabel,
            'text' => trim($validated['text']),
        ]);

        $thread->update([
            'last_message' => $msg->text,
            'time_ago' => 'Just now',
            'unread' => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg->toMessageArray(),
                'thread_id' => $thread->id,
            ], 201);
        }

        return redirect()->route('messages');
    }

    public function markThreadRead(Request $request, int $threadId): JsonResponse
    {
        $user = $this->matchingService->resolveActiveUser();
        $thread = MessageThread::where('user_id', $user->id)->findOrFail($threadId);
        $thread->update(['unread' => false]);

        return response()->json(['status' => 'success']);
    }

    public function markNotificationsRead(Request $request): JsonResponse
    {
        $user = $this->matchingService->resolveActiveUser();
        UserNotification::where('user_id', $user->id)->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }

    public function subscribeNewsletter(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:160'],
        ]);

        $user = $this->matchingService->resolveActiveUser();

        NewsletterSubscription::updateOrCreate(
            ['email' => strtolower(trim($validated['email']))],
            ['user_id' => Auth::id(), 'source' => 'dashboard_widget', 'is_active' => true]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Thank you for subscribing!',
            ], 201);
        }

        return back()->with('status', 'Thank you for subscribing!');
    }

    private function shortMoney(float $n): string
    {
        return match (true) {
            $n >= 1_000_000_000 => '$'.round($n / 1_000_000_000, 1).'B',
            $n >= 1_000_000 => '$'.round($n / 1_000_000, 1).'M',
            $n >= 1_000 => '$'.round($n / 1_000).'K',
            default => '$'.round($n),
        };
    }
}
