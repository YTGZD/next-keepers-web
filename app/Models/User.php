<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'title',
        'company',
        'location',
        'avatar',
        'bio',
        'buyer_type',
        'focus_industries',
        'target_deal_size',
        'min_sde',
        'capital_readiness',
        'geographic_preference',
        'membership_tier',
        'member_since',
        'nda_status',
        'nda_signed_at',
        'linkedin_url',
        'website_url',
        'two_factor_enabled',
        'notify_instant_deals',
        'notify_weekly_intel',
        'notify_messages',
        'notify_marketing',
        'viewed_deals_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'nda_signed_at' => 'datetime',
            'password' => 'hashed',
            'focus_industries' => 'array',
            'two_factor_enabled' => 'boolean',
            'notify_instant_deals' => 'boolean',
            'notify_weekly_intel' => 'boolean',
            'notify_messages' => 'boolean',
            'notify_marketing' => 'boolean',
            'viewed_deals_count' => 'integer',
        ];
    }

    /** Admins manage listings and imports. The role is never mass-assignable; see `php artisan nk:admin`. */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Only admins may sign in to the admin panel. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function buyBox(): HasOne
    {
        return $this->hasOne(BuyBox::class);
    }

    public function dealBoxes(): HasMany
    {
        return $this->hasMany(DealBox::class);
    }

    public function watchlistItems(): HasMany
    {
        return $this->hasMany(WatchlistItem::class);
    }

    public function pipelineDeals(): HasMany
    {
        return $this->hasMany(PipelineDeal::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function messageThreads(): HasMany
    {
        return $this->hasMany(MessageThread::class);
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Profile data for the settings screen. Blank fields stay blank: no identity,
     * verification or NDA status is ever invented for a real account.
     */
    public function toProfileArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?? '',
            'title' => $this->title ?? '',
            'company' => $this->company ?? '',
            'location' => $this->location ?? '',
            'avatar' => $this->avatar ?? 'images/dashboard/avatar.png',
            'bio' => $this->bio ?? '',
            'buyer_type' => $this->buyer_type ?? '',
            'focus_industries' => $this->focus_industries ?? [],
            'target_deal_size' => $this->target_deal_size ?? '',
            'min_sde' => $this->min_sde ?? '',
            'capital_readiness' => $this->capital_readiness ?? '',
            'geographic_preference' => $this->geographic_preference ?? '',
            'membership' => $this->membership_tier ?? 'Free account',
            'member_since' => $this->member_since ?? ($this->created_at?->format('F Y') ?? now()->format('F Y')),
            'nda_status' => $this->nda_status ?? 'No NDA on file',
            'linkedin' => $this->linkedin_url ?? '',
            'website' => $this->website_url ?? '',
            'two_factor_enabled' => (bool) ($this->two_factor_enabled ?? false),
            'notifications' => [
                'instantDeals' => (bool) ($this->notify_instant_deals ?? true),
                'weeklyIntel' => (bool) ($this->notify_weekly_intel ?? true),
                'messages' => (bool) ($this->notify_messages ?? true),
                'marketing' => (bool) ($this->notify_marketing ?? false),
            ],
        ];
    }

    /** The shared, read-only sample account used for the public dashboard preview. */
    public function isDemo(): bool
    {
        return $this->email === config('nextkeepers.demo_email');
    }
}
