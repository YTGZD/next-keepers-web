<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageThread extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'contact_name',
        'contact_role',
        'contact_avatar',
        'business_name',
        'business_revenue',
        'business_sde',
        'business_asking',
        'business_fit',
        'last_message',
        'time_ago',
        'unread',
        'category',
        'online',
        'nda_signed',
        'call_scheduled',
    ];

    protected function casts(): array
    {
        return [
            'business_fit' => 'integer',
            'unread' => 'boolean',
            'online' => 'boolean',
            'nda_signed' => 'boolean',
            'call_scheduled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('id');
    }

    public function toThreadArray(): array
    {
        return [
            'id' => $this->id,
            'contact_name' => $this->contact_name,
            'contact_role' => $this->contact_role,
            'contact_avatar' => $this->contact_avatar,
            'business_name' => $this->business_name,
            'business_id' => $this->business_id ?? 1,
            'business_revenue' => $this->business_revenue,
            'business_sde' => $this->business_sde,
            'business_asking' => $this->business_asking,
            'business_fit' => $this->business_fit,
            'last_message' => $this->last_message,
            'time_ago' => $this->time_ago,
            'unread' => (bool) $this->unread,
            'category' => $this->category,
            'online' => (bool) $this->online,
            'nda_signed' => (bool) $this->nda_signed,
            'call_scheduled' => (bool) $this->call_scheduled,
            'messages' => $this->messages->map(fn (Message $m) => $m->toMessageArray())->values()->all(),
        ];
    }
}
