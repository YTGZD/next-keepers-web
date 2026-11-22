<?php

namespace App\Services\Listings;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Which listings a dashboard viewer sees.
 *
 * Guests browse the public preview (the shared sample account), which shows
 * only the fictional sample rows. Signed-in accounts see only real listings
 * an admin has published. The two never mix.
 */
class ListingFeed
{
    public function showsRealListings(User $viewer): bool
    {
        return $viewer->email !== config('nextkeepers.demo_email');
    }

    /** @return Builder<Business> */
    public function for(User $viewer): Builder
    {
        return Business::visible()->where('is_sample', ! $this->showsRealListings($viewer));
    }
}
