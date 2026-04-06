<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'instance_id',
        'name',
        'phone',
        'intent',
        'messages_count',
        'interest',
        'status',
        'last_interaction_at',
    ];

    protected function casts(): array
    {
        return [
            'messages_count' => 'integer',
            'last_interaction_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function whatsappInstance(): BelongsTo
    {
        return $this->belongsTo(WhatsAppInstance::class, 'instance_id');
    }
}
