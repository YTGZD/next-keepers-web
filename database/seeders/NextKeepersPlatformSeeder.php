<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessFinancial;
use App\Models\BuyBox;
use App\Models\Industry;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\PipelineDeal;
use App\Models\RegionalMarket;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\WatchlistItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NextKeepersPlatformSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Shared, read-only sample buyer used by the public dashboard preview.
        //    Everything on this account (and the listings, messages and figures
        //    below) is fictional sample data. The password is random and never
        //    shown, so nobody can sign in as this account.
        $user = User::updateOrCreate(
            ['email' => config('nextkeepers.demo_email')],
            [
                'name' => 'Sample Buyer',
                'password' => Hash::make(Str::random(64)),
                'title' => 'Acquisition searcher (sample profile)',
                'company' => 'Sample search fund',
                'location' => 'New York, NY',
                'avatar' => 'images/dashboard/avatar.png',
                'bio' => 'Illustrative profile used to preview the dashboard. Create a free account to build your own buy box and watchlist.',
                'buyer_type' => 'Individual Searcher / Operator',
                'focus_industries' => [
                    'HVAC & Climate Control',
                    'Commercial Restoration',
                    'Healthcare Distribution',
                    'Automotive Services',
                    'Precision Light Manufacturing',
                ],
                'target_deal_size' => '$1.0M – $5.0M',
                'min_sde' => '$300,000 / year',
                'capital_readiness' => 'Sample profile',
                'geographic_preference' => 'Northeast (NY, PA, MA, NJ, CT)',
                'membership_tier' => 'Sample preview',
                'member_since' => 'Sample',
                'nda_status' => 'Sample data (no real NDA)',
                'two_factor_enabled' => false,
                'notify_instant_deals' => true,
                'notify_weekly_intel' => true,
                'notify_messages' => true,
                'notify_marketing' => false,
                'viewed_deals_count' => 42,
            ]
        );

        // 2. Industries
        $industriesData = [
            ['name' => 'Healthcare Services', 'slug' => 'healthcare-services', 'category_key' => 'healthcare', 'icon' => 'plus', 'color' => 'emerald', 'market_share_percent' => 18.7, 'annual_growth_percent' => 8.9, 'avg_multiple' => 4.2, 'is_top_industry' => true, 'sort_order' => 1],
            ['name' => 'Construction', 'slug' => 'construction', 'category_key' => 'construction', 'icon' => 'hardhat', 'color' => 'gold', 'market_share_percent' => 16.1, 'annual_growth_percent' => 7.4, 'avg_multiple' => 3.4, 'is_top_industry' => true, 'sort_order' => 2],
            ['name' => 'Professional Services', 'slug' => 'professional-services', 'category_key' => 'services', 'icon' => 'briefcase', 'color' => 'forest', 'market_share_percent' => 14.3, 'annual_growth_percent' => 6.8, 'avg_multiple' => 3.8, 'is_top_industry' => true, 'sort_order' => 3],
            ['name' => 'Manufacturing', 'slug' => 'manufacturing', 'category_key' => 'manufacturing', 'icon' => 'factory', 'color' => 'teal', 'market_share_percent' => 12.9, 'annual_growth_percent' => 5.9, 'avg_multiple' => 4.5, 'is_top_industry' => true, 'sort_order' => 4],
            ['name' => 'Retail Trade', 'slug' => 'retail-trade', 'category_key' => 'retail', 'icon' => 'cart', 'color' => 'cyan', 'market_share_percent' => 11.8, 'annual_growth_percent' => 4.2, 'avg_multiple' => 2.9, 'is_top_industry' => true, 'sort_order' => 5],
            ['name' => 'HVAC & Restoration', 'slug' => 'hvac-restoration', 'category_key' => 'hvac', 'icon' => 'wrench', 'color' => 'emerald', 'market_share_percent' => 10.4, 'annual_growth_percent' => 9.5, 'avg_multiple' => 3.6, 'is_top_industry' => false, 'sort_order' => 6],
        ];

        $industriesBySlug = [];
        foreach ($industriesData as $ind) {
            $industriesBySlug[$ind['slug']] = Industry::updateOrCreate(['slug' => $ind['slug']], $ind);
        }

        // 3. Regional Markets
        $marketsData = [
            ['city' => 'Rochester', 'state' => 'New York', 'state_code' => 'NY', 'location_label' => 'Rochester, NY', 'region_name' => 'Northeast', 'service_radius_miles' => 50, 'total_businesses' => 3420, 'opportunities_count' => 86, 'population_growth_percent' => 2.1, 'housing_permits_growth_percent' => 12.3, 'industry_growth_percent' => 3.5, 'business_activity_growth_percent' => 6.8, 'sba_lending_growth_percent' => 6.1, 'avg_asking_price' => 1650000],
            ['city' => 'Buffalo', 'state' => 'New York', 'state_code' => 'NY', 'location_label' => 'Buffalo, NY', 'region_name' => 'Northeast', 'service_radius_miles' => 50, 'total_businesses' => 4150, 'opportunities_count' => 94, 'population_growth_percent' => 1.9, 'housing_permits_growth_percent' => 10.8, 'industry_growth_percent' => 4.1, 'business_activity_growth_percent' => 7.2, 'sba_lending_growth_percent' => 6.4, 'avg_asking_price' => 1850000],
            ['city' => 'Albany', 'state' => 'New York', 'state_code' => 'NY', 'location_label' => 'Albany, NY', 'region_name' => 'Northeast', 'service_radius_miles' => 60, 'total_businesses' => 2890, 'opportunities_count' => 64, 'population_growth_percent' => 2.4, 'housing_permits_growth_percent' => 11.5, 'industry_growth_percent' => 3.8, 'business_activity_growth_percent' => 6.5, 'sba_lending_growth_percent' => 5.9, 'avg_asking_price' => 1400000],
            ['city' => 'Syracuse', 'state' => 'New York', 'state_code' => 'NY', 'location_label' => 'Syracuse, NY', 'region_name' => 'Northeast', 'service_radius_miles' => 50, 'total_businesses' => 2430, 'opportunities_count' => 68, 'population_growth_percent' => 2.8, 'housing_permits_growth_percent' => 14.2, 'industry_growth_percent' => 4.6, 'business_activity_growth_percent' => 8.1, 'sba_lending_growth_percent' => 7.0, 'avg_asking_price' => 2100000],
        ];

        $marketsByLabel = [];
        foreach ($marketsData as $mkt) {
            $marketsByLabel[$mkt['location_label']] = RegionalMarket::updateOrCreate(['location_label' => $mkt['location_label']], $mkt);
        }

        // 4. Businesses + Financials
        $businessesData = [
            [
                'slug' => 'rochester-hvac-services-co',
                'name' => 'Rochester HVAC Services Co.',
                'short_name' => 'HVAC Services Company',
                'tagline' => 'Established HVAC and restoration business with strong recurring revenue and growth potential.',
                'description' => 'Established HVAC company with a strong recurring customer base and solid growth history.',
                'overview_description' => 'This well-established HVAC and restoration company has built a strong reputation in the Rochester market for quality service and customer satisfaction. The business benefits from recurring revenue, a loyal customer base, and experienced staff, making it an attractive acquisition opportunity.',
                'industry_id' => $industriesBySlug['hvac-restoration']->id,
                'industry' => 'HVAC & Restoration',
                'category' => 'hvac',
                'regional_market_id' => $marketsByLabel['Rochester, NY']->id,
                'city' => 'Rochester',
                'state' => 'NY',
                'location' => 'Rochester, NY',
                'service_area' => '50-mile radius',
                'established_year' => 2012,
                'employees' => 12,
                'annual_growth_label' => '8% (last 3 years)',
                'annual_revenue' => 1800000,
                'revenue_label' => '$1.8M',
                'revenue_growth_percent' => 8.0,
                'sde' => 420000,
                'sde_label' => '$420K',
                'sde_growth_percent' => 10.0,
                'asking_price' => 1900000,
                'asking_price_label' => '$1.9M',
                'sde_multiple' => 3.2,
                'profit_margin_percent' => 18.0,
                'margin_label' => '18%',
                'is_profitable' => true,
                'is_owner_operated' => true,
                'is_absentee_owner' => false,
                'is_growing_market' => true,
                'is_verified' => false, // fictional listings are never verified
                'is_top_match' => true,
                'status' => 'active',
                'discover_tab' => 'high_fit',
                'fit_score' => 92,
                'hero_image' => 'images/business/hero-hvac.png',
                'dashboard_image' => 'images/dashboard/opp-hvac.png',
                'watchlist_image' => 'images/watchlist/thumb-hvac.png',
                'buybox_image' => 'images/buybox/thumb-hvac.png',
                'facility_image' => 'images/business/facility-video.png',
                'tags' => ['Recurring Revenue', 'Established Brand', 'Strong Fit'],
                'overview_checklist' => ['Recurring revenue model', 'Strong local brand', 'Growth opportunities'],
                'why_this_business' => [
                    'Growing industry with stable demand',
                    'Strong financial performance',
                    'Strategic location in Rochester, NY',
                    'Excellent acquisition fit (92/100)',
                ],
                'criteria_met' => ['Your industry', 'Your location', 'Strong margins'],
                'fit_criteria' => ['Industry Match' => true, 'Financials' => true, 'Location' => true, 'Size' => true],
                'listed_time_ago' => '2h ago',
                'listed_at' => now()->subHours(2),
                'financials' => [
                    ['fiscal_year' => 2022, 'revenue' => 1500000, 'revenue_label' => '$1.5M', 'sde' => 350000, 'sde_label' => '$350K', 'revenue_bar_percent' => 75, 'sde_bar_percent' => 25],
                    ['fiscal_year' => 2023, 'revenue' => 1600000, 'revenue_label' => '$1.6M', 'sde' => 370000, 'sde_label' => '$370K', 'revenue_bar_percent' => 80, 'sde_bar_percent' => 27],
                    ['fiscal_year' => 2024, 'revenue' => 1800000, 'revenue_label' => '$1.8M', 'sde' => 420000, 'sde_label' => '$420K', 'revenue_bar_percent' => 95, 'sde_bar_percent' => 32],
                ],
            ],
            [
                'slug' => 'medical-equipment-distributor-buffalo',
                'name' => 'Medical Equipment Distributor',
                'short_name' => 'Medical Equipment Distributor',
                'tagline' => 'Regional distributor of specialized clinical and diagnostic medical equipment with multi-year hospital contracts.',
                'description' => 'Distributor of medical equipment with long-standing relationships and diversified healthcare accounts.',
                'overview_description' => 'Operating across Western New York, this medical equipment supplier serves hospitals, outpatient clinics, and rehabilitation centers with recurring consumable and maintenance contracts.',
                'industry_id' => $industriesBySlug['healthcare-services']->id,
                'industry' => 'Healthcare',
                'category' => 'healthcare',
                'regional_market_id' => $marketsByLabel['Buffalo, NY']->id,
                'city' => 'Buffalo',
                'state' => 'NY',
                'location' => 'Buffalo, NY',
                'service_area' => '75-mile radius',
                'established_year' => 2010,
                'employees' => 18,
                'annual_growth_label' => '11% (last 3 years)',
                'annual_revenue' => 2400000,
                'revenue_label' => '$2.4M',
                'revenue_growth_percent' => 11.0,
                'sde' => 580000,
                'sde_label' => '$580K',
                'sde_growth_percent' => 14.0,
                'asking_price' => 2600000,
                'asking_price_label' => '$2.6M',
                'sde_multiple' => 4.5,
                'profit_margin_percent' => 24.0,
                'margin_label' => '24%',
                'is_profitable' => true,
                'is_owner_operated' => true,
                'is_absentee_owner' => false,
                'is_growing_market' => true,
                'is_verified' => false, // fictional listings are never verified
                'is_top_match' => false,
                'status' => 'active',
                'discover_tab' => 'new_listings',
                'fit_score' => 89,
                'hero_image' => 'images/business/hero-hvac.png',
                'dashboard_image' => 'images/dashboard/opp-medical.png',
                'watchlist_image' => 'images/watchlist/thumb-medical.png',
                'buybox_image' => 'images/buybox/thumb-van.png',
                'facility_image' => 'images/business/facility-video.png',
                'tags' => ['Healthcare', 'Stable Growth', 'High Margins'],
                'overview_checklist' => ['Long-term clinical contracts', '24% SDE margin profile', 'Experienced sales & logistics team'],
                'why_this_business' => [
                    'High-margin healthcare distribution with sticky institutional clients',
                    'Consistent 11% annual top-line growth',
                    'Seller open to 20% seller financing for qualified operator',
                    'Strong acquisition fit (89/100)',
                ],
                'criteria_met' => ['Healthcare', 'Your region', 'Profitable'],
                'fit_criteria' => ['Industry Match' => true, 'Financials' => true, 'Location' => true, 'Size' => true],
                'listed_time_ago' => '3h ago',
                'listed_at' => now()->subHours(3),
                'financials' => [
                    ['fiscal_year' => 2022, 'revenue' => 1950000, 'revenue_label' => '$1.95M', 'sde' => 460000, 'sde_label' => '$460K', 'revenue_bar_percent' => 72, 'sde_bar_percent' => 28],
                    ['fiscal_year' => 2023, 'revenue' => 2180000, 'revenue_label' => '$2.18M', 'sde' => 520000, 'sde_label' => '$520K', 'revenue_bar_percent' => 82, 'sde_bar_percent' => 31],
                    ['fiscal_year' => 2024, 'revenue' => 2400000, 'revenue_label' => '$2.4M', 'sde' => 580000, 'sde_label' => '$580K', 'revenue_bar_percent' => 95, 'sde_bar_percent' => 36],
                ],
            ],
            [
                'slug' => 'auto-repair-and-service-albany',
                'name' => 'Auto Repair & Service',
                'short_name' => 'Auto Repair & Service',
                'tagline' => 'Turnkey 6-bay commercial and residential automotive repair center with fleet maintenance contracts.',
                'description' => 'Profitable auto repair shop with loyal customers and modern diagnostic equipment.',
                'overview_description' => 'Equipped with 6 full-service bays, Hunter alignment systems, and computerized diagnostics, this automotive center serves both consumer vehicles and municipal/commercial fleets.',
                'industry_id' => $industriesBySlug['retail-trade']->id,
                'industry' => 'Automotive',
                'category' => 'retail',
                'regional_market_id' => $marketsByLabel['Albany, NY']->id,
                'city' => 'Albany',
                'state' => 'NY',
                'location' => 'Albany, NY',
                'service_area' => '35-mile radius',
                'established_year' => 2015,
                'employees' => 8,
                'annual_growth_label' => '7% (last 3 years)',
                'annual_revenue' => 1100000,
                'revenue_label' => '$1.1M',
                'revenue_growth_percent' => 7.0,
                'sde' => 310000,
                'sde_label' => '$310K',
                'sde_growth_percent' => 9.0,
                'asking_price' => 1200000,
                'asking_price_label' => '$1.2M',
                'sde_multiple' => 3.9,
                'profit_margin_percent' => 28.0,
                'margin_label' => '28%',
                'is_profitable' => true,
                'is_owner_operated' => true,
                'is_absentee_owner' => false,
                'is_growing_market' => true,
                'is_verified' => false, // fictional listings are never verified
                'is_top_match' => false,
                'status' => 'active',
                'discover_tab' => 'high_fit',
                'fit_score' => 87,
                'hero_image' => 'images/business/hero-hvac.png',
                'dashboard_image' => 'images/dashboard/opp-auto.png',
                'watchlist_image' => 'images/watchlist/thumb-auto.png',
                'buybox_image' => 'images/buybox/thumb-auto.png',
                'facility_image' => 'images/business/facility-video.png',
                'tags' => ['Owner Operated', 'Growth Potential', 'Strong Fit'],
                'overview_checklist' => ['6 fully equipped service bays', 'ASE-certified lead technicians', '7-year secure lease with renewal options'],
                'why_this_business' => [
                    'Industry-leading 28% SDE margin',
                    'Modern diagnostic & alignment equipment included',
                    'Growing fleet maintenance accounts in the Capital Region',
                    'Strong acquisition fit (87/100)',
                ],
                'criteria_met' => ['Growing market', 'Your region', 'Owner operated'],
                'fit_criteria' => ['Industry Match' => true, 'Financials' => true, 'Location' => true, 'Size' => true],
                'listed_time_ago' => '4h ago',
                'listed_at' => now()->subHours(4),
                'financials' => [
                    ['fiscal_year' => 2022, 'revenue' => 940000, 'revenue_label' => '$940K', 'sde' => 260000, 'sde_label' => '$260K', 'revenue_bar_percent' => 74, 'sde_bar_percent' => 26],
                    ['fiscal_year' => 2023, 'revenue' => 1020000, 'revenue_label' => '$1.02M', 'sde' => 285000, 'sde_label' => '$285K', 'revenue_bar_percent' => 83, 'sde_bar_percent' => 29],
                    ['fiscal_year' => 2024, 'revenue' => 1100000, 'revenue_label' => '$1.1M', 'sde' => 310000, 'sde_label' => '$310K', 'revenue_bar_percent' => 94, 'sde_bar_percent' => 33],
                ],
            ],
            [
                'slug' => 'precision-manufacturing-syracuse',
                'name' => 'Precision Manufacturing',
                'short_name' => 'Precision Manufacturing',
                'tagline' => 'ISO-certified precision CNC machining and custom industrial fabrication facility.',
                'description' => 'Well-established manufacturer with steady demand and long-term OEM contracts.',
                'overview_description' => 'Specializing in tight-tolerance CNC milling, turning, and light assembly for aerospace, medical, and clean-tech OEMs across the Northeast.',
                'industry_id' => $industriesBySlug['manufacturing']->id,
                'industry' => 'Manufacturing',
                'category' => 'manufacturing',
                'regional_market_id' => $marketsByLabel['Syracuse, NY']->id,
                'city' => 'Syracuse',
                'state' => 'NY',
                'location' => 'Syracuse, NY',
                'service_area' => 'Northeast Corridor',
                'established_year' => 2012,
                'employees' => 24,
                'annual_growth_label' => '9% (last 3 years)',
                'annual_revenue' => 3200000,
                'revenue_label' => '$3.2M',
                'revenue_growth_percent' => 9.0,
                'sde' => 720000,
                'sde_label' => '$720K',
                'sde_growth_percent' => 12.0,
                'asking_price' => 3500000,
                'asking_price_label' => '$3.5M',
                'sde_multiple' => 4.9,
                'profit_margin_percent' => 22.5,
                'margin_label' => '23%',
                'is_profitable' => true,
                'is_owner_operated' => false,
                'is_absentee_owner' => true,
                'is_growing_market' => true,
                'is_verified' => false, // fictional listings are never verified
                'is_top_match' => false,
                'status' => 'active',
                'discover_tab' => 'high_fit',
                'fit_score' => 85,
                'hero_image' => 'images/business/hero-hvac.png',
                'dashboard_image' => 'images/dashboard/opp-mfg.png',
                'watchlist_image' => 'images/watchlist/thumb-mfg.png',
                'buybox_image' => 'images/watchlist/thumb-mfg.png',
                'facility_image' => 'images/business/facility-video.png',
                'tags' => ['Profitable', 'Established 2012', 'Equipment Included'],
                'overview_checklist' => ['$1.4M in CNC machinery included', 'Plant manager in place', 'Multi-year OEM backlog'],
                'why_this_business' => [
                    'High barriers to entry with ISO certification & specialized CNC fleet',
                    'Seasoned shop floor leadership enables semi-absentee ownership',
                    'Located in Syracuse reshoring & semiconductor growth corridor',
                    'Solid acquisition fit (85/100)',
                ],
                'criteria_met' => ['Manufacturing', 'Your region', 'Strong margins'],
                'fit_criteria' => ['Industry Match' => true, 'Financials' => true, 'Location' => true, 'Size' => true],
                'listed_time_ago' => '1d ago',
                'listed_at' => now()->subDay(),
                'financials' => [
                    ['fiscal_year' => 2022, 'revenue' => 2700000, 'revenue_label' => '$2.7M', 'sde' => 590000, 'sde_label' => '$590K', 'revenue_bar_percent' => 76, 'sde_bar_percent' => 25],
                    ['fiscal_year' => 2023, 'revenue' => 2950000, 'revenue_label' => '$2.95M', 'sde' => 650000, 'sde_label' => '$650K', 'revenue_bar_percent' => 85, 'sde_bar_percent' => 29],
                    ['fiscal_year' => 2024, 'revenue' => 3200000, 'revenue_label' => '$3.2M', 'sde' => 720000, 'sde_label' => '$720K', 'revenue_bar_percent' => 96, 'sde_bar_percent' => 34],
                ],
            ],
            [
                'slug' => 'lawn-care-services-rochester',
                'name' => 'Lawn Care Services',
                'short_name' => 'Lawn Care Services',
                'tagline' => 'Four-season commercial grounds maintenance, landscaping, and snow management business.',
                'description' => 'Growing lawn care business with recurring contracts and commercial HOA accounts.',
                'overview_description' => 'Serving commercial office parks, retail plazas, and residential HOAs across Monroe County with year-round grounds care and winter snow/ice management contracts.',
                'industry_id' => $industriesBySlug['professional-services']->id,
                'industry' => 'Home Services',
                'category' => 'services',
                'regional_market_id' => $marketsByLabel['Rochester, NY']->id,
                'city' => 'Rochester',
                'state' => 'NY',
                'location' => 'Rochester, NY',
                'service_area' => '40-mile radius',
                'established_year' => 2016,
                'employees' => 6,
                'annual_growth_label' => '10% (last 3 years)',
                'annual_revenue' => 750000,
                'revenue_label' => '$750K',
                'revenue_growth_percent' => 10.0,
                'sde' => 180000,
                'sde_label' => '$180K',
                'sde_growth_percent' => 12.0,
                'asking_price' => 980000,
                'asking_price_label' => '$980K',
                'sde_multiple' => 3.5,
                'profit_margin_percent' => 24.0,
                'margin_label' => '24%',
                'is_profitable' => true,
                'is_owner_operated' => true,
                'is_absentee_owner' => false,
                'is_growing_market' => true,
                'is_verified' => false, // fictional listings are never verified
                'is_top_match' => false,
                'status' => 'active',
                'discover_tab' => 'all',
                'fit_score' => 82,
                'hero_image' => 'images/business/hero-hvac.png',
                'dashboard_image' => 'images/watchlist/thumb-lawn.png',
                'watchlist_image' => 'images/watchlist/thumb-lawn.png',
                'buybox_image' => 'images/watchlist/thumb-lawn.png',
                'facility_image' => 'images/business/facility-video.png',
                'tags' => ['High Margin', 'Commercial Accounts', 'Turnkey'],
                'overview_checklist' => ['85% recurring annual contracts', 'Full fleet of trucks & zero-turn mowers', 'Strong bolt-on acquisition candidate'],
                'why_this_business' => [
                    'Predictable year-round cash flow from HOA & commercial retainers',
                    'Well-maintained fleet and crew leaders in place',
                    'Ideal tuck-in or starter platform in Rochester, NY',
                    'Good acquisition fit (82/100)',
                ],
                'criteria_met' => ['Your location', 'Strong margins', 'Profitable'],
                'fit_criteria' => ['Industry Match' => false, 'Financials' => true, 'Location' => true, 'Size' => true],
                'listed_time_ago' => '2d ago',
                'listed_at' => now()->subDays(2),
                'financials' => [
                    ['fiscal_year' => 2022, 'revenue' => 620000, 'revenue_label' => '$620K', 'sde' => 145000, 'sde_label' => '$145K', 'revenue_bar_percent' => 70, 'sde_bar_percent' => 24],
                    ['fiscal_year' => 2023, 'revenue' => 685000, 'revenue_label' => '$685K', 'sde' => 162000, 'sde_label' => '$162K', 'revenue_bar_percent' => 82, 'sde_bar_percent' => 28],
                    ['fiscal_year' => 2024, 'revenue' => 750000, 'revenue_label' => '$750K', 'sde' => 180000, 'sde_label' => '$180K', 'revenue_bar_percent' => 92, 'sde_bar_percent' => 32],
                ],
            ],
        ];

        $businessesById = [];
        foreach ($businessesData as $bizData) {
            $financials = $bizData['financials'];
            unset($bizData['financials']);
            // Every seeded listing is fictional preview data.
            $bizData['is_sample'] = true;

            $biz = Business::updateOrCreate(['slug' => $bizData['slug']], $bizData);
            $businessesById[$biz->id] = $biz;

            foreach ($financials as $fin) {
                BusinessFinancial::updateOrCreate(
                    ['business_id' => $biz->id, 'fiscal_year' => $fin['fiscal_year']],
                    $fin
                );
            }
        }

        // 5. User Buy Box
        BuyBox::updateOrCreate(
            ['user_id' => $user->id],
            [
                'industries' => ['HVAC', 'Restoration', 'Healthcare'],
                'location' => 'New York, NY + 100 miles',
                'radius_miles' => 100,
                'min_revenue' => 500000,
                'max_revenue' => 3000000,
                'revenue_range' => '$500K – $3M',
                'min_sde' => 200000,
                'max_sde' => 1500000,
                'sde_range' => '$200K – $1.5M',
                'min_asking_price' => 500000,
                'max_asking_price' => 3000000,
                'asking_price' => '$500K – $3M',
                'min_profit_margin' => 15.0,
                'profit_margin' => '≥ 15%',
                'min_employees' => 5,
                'max_employees' => 50,
                'employees' => '5 – 50',
                'profitable_only' => true,
                'owner_operated' => false,
                'growing_market' => true,
                'absentee_owner_ok' => false,
            ]
        );

        // 6. Watchlist Items
        $allBusinesses = Business::orderBy('id')->get()->keyBy('id');
        $watchlistEntries = [
            ['business_id' => 1, 'tag' => 'High Fit', 'tag_type' => 'green', 'tag_icon' => 'star', 'category' => 'high_fit', 'added_time_label' => 'Added 2 days ago', 'fit_score' => 94],
            ['business_id' => 2, 'tag' => 'New', 'tag_type' => 'amber', 'tag_icon' => 'bolt', 'category' => 'recently_added', 'added_time_label' => 'Added 3 days ago', 'fit_score' => 89],
            ['business_id' => 3, 'tag' => 'Watchlist', 'tag_type' => 'teal', 'tag_icon' => 'bookmark', 'category' => 'all', 'added_time_label' => 'Added 1 week ago', 'fit_score' => 87],
            ['business_id' => 4, 'tag' => 'High Fit', 'tag_type' => 'green', 'tag_icon' => 'star', 'category' => 'high_fit', 'added_time_label' => 'Added 1 week ago', 'fit_score' => 85],
            ['business_id' => 5, 'tag' => 'Alert', 'tag_type' => 'orange', 'tag_icon' => 'bell', 'category' => 'alerts', 'added_time_label' => 'Added 2 weeks ago', 'fit_score' => 82],
        ];

        foreach ($watchlistEntries as $w) {
            if ($allBusinesses->has($w['business_id'])) {
                WatchlistItem::updateOrCreate(
                    ['user_id' => $user->id, 'business_id' => $w['business_id']],
                    $w
                );
            }
        }

        // 7. Pipeline Deals
        foreach ([1 => 'cim_review', 2 => 'nda_signed', 4 => 'nda_signed'] as $bizId => $stage) {
            if ($allBusinesses->has($bizId)) {
                PipelineDeal::updateOrCreate(
                    ['user_id' => $user->id, 'business_id' => $bizId],
                    ['stage' => $stage, 'nda_signed' => true]
                );
            }
        }

        // 8. Message Threads & Messages
        $threadsSeed = [
            [
                'business_id' => 1,
                'contact_name' => 'David Miller',
                'contact_role' => 'Principal Owner',
                'contact_avatar' => 'DM',
                'business_name' => 'Rochester HVAC Services Co.',
                'business_revenue' => '$1.8M',
                'business_sde' => '$420K',
                'business_asking' => '$1.9M',
                'business_fit' => 94,
                'last_message' => 'I have uploaded the confidential information memorandum (CIM) and the trailing 36-month P&L statements. Let me know when you would like to jump on a quick call.',
                'time_ago' => '10:24 AM',
                'unread' => true,
                'category' => 'opportunities',
                'online' => true,
                'nda_signed' => true,
                'call_scheduled' => false,
                'messages' => [
                    [
                        'sender' => 'them',
                        'name' => 'David Miller',
                        'time_label' => 'Yesterday 2:15 PM',
                        'text' => 'Hi, thanks for your interest in Rochester HVAC Services Co. We have been operating in Monroe County for 12 years with strong commercial contracts and recurring maintenance accounts.',
                        'attachments' => null,
                    ],
                    [
                        'sender' => 'me',
                        'name' => 'Sample Buyer',
                        'time_label' => 'Yesterday 3:30 PM',
                        'text' => 'Hi David, wonderful to connect. Your recurring revenue profile and strong local reputation align well with our acquisition thesis. Could you share the confidential memorandum and recent financial summaries?',
                        'attachments' => null,
                    ],
                    [
                        'sender' => 'them',
                        'name' => 'David Miller',
                        'time_label' => 'Today 10:24 AM',
                        'text' => 'I have uploaded the confidential information memorandum (CIM) and the trailing 36-month P&L statements. Let me know when you would like to jump on a quick call.',
                        'attachments' => [
                            ['name' => 'CIM_Rochester_HVAC_2024.pdf', 'size' => '4.2 MB', 'type' => 'pdf'],
                            ['name' => 'Financial_Summary_36M.xlsx', 'size' => '1.1 MB', 'type' => 'excel'],
                        ],
                    ],
                ],
            ],
            [
                'business_id' => 2,
                'contact_name' => 'Sarah Jenkins',
                'contact_role' => 'Senior M&A Broker',
                'contact_avatar' => 'SJ',
                'business_name' => 'Medical Equipment Distributor',
                'business_revenue' => '$2.4M',
                'business_sde' => '$580K',
                'business_asking' => '$2.6M',
                'business_fit' => 89,
                'last_message' => 'The seller is open to a 20% seller financing structure for qualified buyers with healthcare industry background.',
                'time_ago' => 'Yesterday',
                'unread' => true,
                'category' => 'brokers',
                'online' => false,
                'nda_signed' => true,
                'call_scheduled' => false,
                'messages' => [
                    [
                        'sender' => 'them',
                        'name' => 'Sarah Jenkins',
                        'time_label' => 'Yesterday 11:00 AM',
                        'text' => 'Hello, following up on your inquiry for the Medical Equipment Distributor in Buffalo. The seller is open to a 20% seller financing structure for qualified buyers with healthcare industry background.',
                        'attachments' => null,
                    ],
                ],
            ],
            [
                'business_id' => 3,
                'contact_name' => 'Marcus Vance',
                'contact_role' => 'Managing Partner',
                'contact_avatar' => 'MV',
                'business_name' => 'Auto Repair & Service',
                'business_revenue' => '$1.1M',
                'business_sde' => '$310K',
                'business_asking' => '$1.2M',
                'business_fit' => 87,
                'last_message' => 'All 6 service bays are fully equipped with Hunter alignment systems and computerized diagnostics.',
                'time_ago' => '2 days ago',
                'unread' => true,
                'category' => 'opportunities',
                'online' => false,
                'nda_signed' => false,
                'call_scheduled' => false,
                'messages' => [
                    [
                        'sender' => 'them',
                        'name' => 'Marcus Vance',
                        'time_label' => '2 days ago',
                        'text' => 'All 6 service bays are fully equipped with Hunter alignment systems and computerized diagnostics. The lease is secure for another 7 years with renewal options.',
                        'attachments' => null,
                    ],
                ],
            ],
            [
                'business_id' => 4,
                'contact_name' => 'Robert Sterling',
                'contact_role' => 'Managing Director, Industrial Capital',
                'contact_avatar' => 'RS',
                'business_name' => 'Precision Manufacturing',
                'business_revenue' => '$3.2M',
                'business_sde' => '$720K',
                'business_asking' => '$3.5M',
                'business_fit' => 85,
                'last_message' => 'We have received your executed NDA. Access to the digital data room will be granted within 24 hours.',
                'time_ago' => '3 days ago',
                'unread' => false,
                'category' => 'brokers',
                'online' => true,
                'nda_signed' => true,
                'call_scheduled' => false,
                'messages' => [
                    [
                        'sender' => 'them',
                        'name' => 'Robert Sterling',
                        'time_label' => '3 days ago',
                        'text' => 'We have received your executed NDA. Access to the digital data room will be granted within 24 hours.',
                        'attachments' => null,
                    ],
                ],
            ],
            [
                'business_id' => 5,
                'contact_name' => 'Elena Rostova',
                'contact_role' => 'Intermediary Advisor',
                'contact_avatar' => 'ER',
                'business_name' => 'Lawn Care Services',
                'business_revenue' => '$750K',
                'business_sde' => '$180K',
                'business_asking' => '$980K',
                'business_fit' => 82,
                'last_message' => 'Confirmed for our introductory video call on Thursday at 2:00 PM EST.',
                'time_ago' => '1 week ago',
                'unread' => false,
                'category' => 'opportunities',
                'online' => false,
                'nda_signed' => true,
                'call_scheduled' => true,
                'messages' => [
                    [
                        'sender' => 'them',
                        'name' => 'Elena Rostova',
                        'time_label' => '1 week ago',
                        'text' => 'Confirmed for our introductory video call on Thursday at 2:00 PM EST. Looking forward to reviewing the commercial contracts with you.',
                        'attachments' => null,
                    ],
                ],
            ],
        ];

        foreach ($threadsSeed as $tData) {
            $msgs = $tData['messages'];
            unset($tData['messages']);
            $tData['user_id'] = $user->id;

            $thread = MessageThread::updateOrCreate(
                ['user_id' => $user->id, 'contact_name' => $tData['contact_name']],
                $tData
            );

            if ($thread->messages()->count() === 0) {
                foreach ($msgs as $m) {
                    $thread->messages()->create($m);
                }
            }
        }

        // 9. Notifications
        $notifications = [
            ['business_id' => 1, 'title' => 'New matching listing in Rochester, NY', 'body' => 'HVAC Services Co. matching your 90+ fit criteria.', 'type' => 'match', 'is_read' => false],
            ['business_id' => null, 'title' => 'Market activity update', 'body' => 'Western NY region SBA lending increased by 6.1%.', 'type' => 'market', 'is_read' => false],
            ['business_id' => 1, 'title' => 'New CIM document shared', 'body' => 'David Miller uploaded CIM_Rochester_HVAC_2024.pdf.', 'type' => 'message', 'is_read' => false],
        ];

        foreach ($notifications as $n) {
            UserNotification::updateOrCreate(
                ['user_id' => $user->id, 'title' => $n['title']],
                $n
            );
        }
    }
}
