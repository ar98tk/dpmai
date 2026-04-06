<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'instance_id',
        'system_prompt',
        'rules',
        'restrictions',
        'intents',
        'model',
        'temperature',
        'max_tokens',
        'context_limit',
    ];

    protected function casts(): array
    {
        return [
            'intents' => 'array',
            'temperature' => 'float',
            'max_tokens' => 'integer',
            'context_limit' => 'integer',
        ];
    }

    public function whatsappInstance(): BelongsTo
    {
        return $this->belongsTo(WhatsAppInstance::class, 'instance_id');
    }
}
