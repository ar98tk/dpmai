<?php

return [
    'base_url' => env('EVOLUTION_BASE_URL', ''),
    'api_key' => env('EVOLUTION_API_KEY', ''),
    'token' => env('EVOLUTION_TOKEN', env('EVOLUTION_API_KEY', '')),
    'integration' => env('EVOLUTION_INTEGRATION', 'WHATSAPP-BAILEYS'),
];
