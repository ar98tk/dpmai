<?php

return [
    'base_url' => env('EVOLUTION_BASE_URL', ''),
    'api_key' => env('EVOLUTION_API_KEY', ''),
    'token' => env('EVOLUTION_TOKEN', env('EVOLUTION_API_KEY', '')),
    'integration' => env('EVOLUTION_INTEGRATION', 'WHATSAPP-BAILEYS'),
    'webhook_base_url' => env('EVOLUTION_WEBHOOK_BASE_URL', env('APP_URL', '')),
    'webhook_path' => env('EVOLUTION_WEBHOOK_PATH', '/webhook/whatsapp/{instance_key}'),
];
