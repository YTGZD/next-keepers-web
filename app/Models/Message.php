<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'message_thread_id',
        'sender',
        'name',
        'time_label',
        'text',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'message_thread_id');
    }

    public function toMessageArray(): array
    {
        $data = [
            'id' => $this->id,
            'sender' => $this->sender,
            'name' => $this->name,
            'time' => $this->time_label,
            'text' => $this->text,
        ];

        if (! empty($this->attachments)) {
            $data['attachments'] = $this->attachments;
        }

        return $data;
    }
}
