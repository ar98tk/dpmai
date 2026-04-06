<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WhatsAppInstance extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_instances';

    protected $fillable = [
        'business_id',
        'name',
        'instance_key',
        'phone_number',
        'status',
        'webhook_secret',
        'ai_enabled',
    ];

    protected function casts(): array
    {
        return [
            'ai_enabled' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function aiSetting(): HasOne
    {
        return $this->hasOne(AiSetting::class, 'instance_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'instance_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'instance_id');
    }
}
