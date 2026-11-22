<?php

namespace App\Support;

use App\Models\Business;

/**
 * Derived deal metrics for a listing, computed from the raw listed figures.
 *
 * These mirror the formula columns of the cleaned listings workbook
 * ("Clean Listings" sheet, columns AK to AV) so the site and the workbook
 * agree. Every method returns null when the inputs needed are missing,
 * which the UI shows as "not disclosed" rather than zero.
 */
class ListingMetrics
{
    public function __construct(private readonly Business $business) {}

    public static function for(Business $business): self
    {
        return new self($business);
    }

    private function isOperating(): bool
    {
        return $this->business->operating_status === null || $this->business->operating_status === 'Yes';
    }

    private static function positive(mixed $value): ?float
    {
        return is_numeric($value) && (float) $value > 0 ? (float) $value : null;
    }

    /** Asking price with real estate taken out, when real estate is included in the price. */
    public function priceExRealEstate(): ?float
    {
        $price = self::positive($this->business->asking_price);
        if ($price === null) {
            return null;
        }
        if ($this->business->real_estate_in_price !== 'Yes') {
            return $price;
        }
        $re = self::positive($this->business->real_estate_value);

        return $re !== null && $price > $re ? $price - $re : null;
    }

    /** Price ex-real estate minus equipment and inventory included in the price. */
    public function impliedGoodwill(): ?float
    {
        $price = $this->priceExRealEstate();
        $ffe = self::positive($this->business->ffe_value);
        $inventory = self::positive($this->business->inventory_value);
        if ($price === null || ($ffe === null && $inventory === null)) {
            return null;
        }

        return $price
            - ($this->business->ffe_in_price === 'Yes' ? (float) $ffe : 0)
            - ($this->business->inventory_in_price === 'Yes' ? (float) $inventory : 0);
    }

    /** Price ex-real estate plus equipment and inventory that are sold on top of the price. */
    public function cashNeeded(): ?float
    {
        $price = $this->priceExRealEstate();
        if ($price === null) {
            return null;
        }

        return $price
            + ($this->business->inventory_in_price === 'No' ? (float) self::positive($this->business->inventory_value) : 0)
            + ($this->business->ffe_in_price === 'No' ? (float) self::positive($this->business->ffe_value) : 0);
    }

    public function priceToEarnings(): ?float
    {
        return $this->ratio(self::positive($this->business->asking_price), $this->business->earnings);
    }

    public function priceExRealEstateToEarnings(): ?float
    {
        return $this->ratio($this->priceExRealEstate(), $this->business->earnings);
    }

    public function cashNeededToEarnings(): ?float
    {
        return $this->ratio($this->cashNeeded(), $this->business->earnings);
    }

    public function priceToRevenue(): ?float
    {
        return $this->ratio(self::positive($this->business->asking_price), $this->business->annual_revenue);
    }

    public function priceExRealEstateToRevenue(): ?float
    {
        return $this->ratio($this->priceExRealEstate(), $this->business->annual_revenue);
    }

    /** Earnings as a share of revenue (0.18 = 18%). */
    public function earningsMargin(): ?float
    {
        $earnings = $this->business->earnings;
        $revenue = self::positive($this->business->annual_revenue);

        return is_numeric($earnings) && $revenue !== null ? (float) $earnings / $revenue : null;
    }

    /** Yearly rent as a share of revenue. */
    public function rentShareOfRevenue(): ?float
    {
        $rent = $this->business->monthly_rent;
        $revenue = self::positive($this->business->annual_revenue);

        return is_numeric($rent) && $revenue !== null ? (float) $rent * 12 / $revenue : null;
    }

    /** @return array<string, float|null> */
    public function toArray(): array
    {
        return [
            'price_ex_real_estate' => $this->priceExRealEstate(),
            'implied_goodwill' => $this->impliedGoodwill(),
            'cash_needed' => $this->cashNeeded(),
            'price_to_earnings' => $this->priceToEarnings(),
            'price_ex_re_to_earnings' => $this->priceExRealEstateToEarnings(),
            'cash_needed_to_earnings' => $this->cashNeededToEarnings(),
            'price_to_revenue' => $this->priceToRevenue(),
            'price_ex_re_to_revenue' => $this->priceExRealEstateToRevenue(),
            'earnings_margin' => $this->earningsMargin(),
            'rent_share_of_revenue' => $this->rentShareOfRevenue(),
        ];
    }

    /** Multiples only apply to operating businesses with positive inputs. */
    private function ratio(?float $numerator, mixed $denominator): ?float
    {
        $denominator = self::positive($denominator);
        if (! $this->isOperating() || $numerator === null || $numerator <= 0 || $denominator === null) {
            return null;
        }

        return $numerator / $denominator;
    }
}
