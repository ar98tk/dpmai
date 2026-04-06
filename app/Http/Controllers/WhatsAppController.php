<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    public function handle(Request $request, OpenAIService $ai)
    {
        $message = $request->input('data.message.conversation');
        $jid = $request->input('data.key.remoteJid');

        if (!$message || !$jid) return;

        $number = explode('@', $jid)[0];

        // 🔥 Generate AI reply
        $reply = $ai->generateReply($message, $number);

        Http::withHeaders([
            'apikey' => '123456'
        ])->post('http://127.0.0.1:8080/message/sendText/Abdelrahman Tarek', [
            'number' => $number,
            'text' => $reply
        ]);

        return response()->json(['ok' => true]);
    }
}
