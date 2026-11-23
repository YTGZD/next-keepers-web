<?php

namespace App\Http\Controllers;

use App\Models\DealBox;
use App\Services\BuyBoxMatchingService;
use App\Services\Listings\DealBoxCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The deal box panel on /buy-box: live results while the buyer adjusts the
 * criteria, and named boxes they save. Signed-in accounts only; saved boxes
 * are private to their account.
 */
class DealBoxController extends Controller
{
    public function __construct(
        private readonly RealDashboardController $real,
        private readonly BuyBoxMatchingService $boxes,
    ) {}

    /** The listing list for unsaved criteria (nothing is stored). */
    public function results(Request $request): JsonResponse
    {
        $box = DealBoxCriteria::box(DealBoxCriteria::attributes($this->validated($request)));
        $results = $this->real->dealBoxResults($request->user(), $box);

        return response()->json([
            'count' => $results['matchCount'],
            'html' => view('dashboard.real.deal-box-results', $results)->render(),
        ]);
    }

    /** Save the criteria under a name, and make them the account's active buy box. */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $this->validated($request, ['name' => ['required', 'string', 'max:60']]);
        $attributes = DealBoxCriteria::attributes($validated);
        $name = trim($validated['name']);

        $dealBox = DealBox::firstOrNew(['user_id' => $user->id, 'name' => $name]);
        if (! $dealBox->exists && DealBox::where('user_id', $user->id)->count() >= 25) {
            return response()->json(['message' => 'You can keep up to 25 saved boxes. Delete one first.'], 422);
        }
        $dealBox->user_id = $user->id;
        $dealBox->criteria = array_intersect_key($attributes, DealBoxCriteria::DEFAULTS);
        $dealBox->save();

        $active = $this->boxes->getOrCreateBuyBox($user);
        $active->forceFill($attributes);
        $active->location = $this->real->criteriaLocation($active);
        $active->save();

        return response()->json([
            'message' => "Saved \"{$name}\". Your dashboard now matches on it.",
            'id' => $dealBox->id,
            'boxes' => $this->real->savedBoxes($user),
        ]);
    }

    public function destroy(Request $request, int $dealBox): JsonResponse
    {
        $box = DealBox::where('user_id', $request->user()->id)->findOrFail($dealBox);
        $box->delete();

        return response()->json([
            'message' => "Deleted \"{$box->name}\".",
            'boxes' => $this->real->savedBoxes($request->user()),
        ]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, array $extra = []): array
    {
        return $request->validate(DealBoxCriteria::rules() + ['counties.*' => ['string', 'max:80']] + $extra);
    }
}
