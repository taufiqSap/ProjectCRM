<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsappService
{
    public static function send(string $phone, string $message)
    {
        $url = env('WHATSAPP_API_URL');
        $token = env('  N');

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post($url, [
            'target' => $phone,
            'message' => $message,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Gagal mengirim pesan WhatsApp: ' . $response->body());
        }

        return $response->json();
    }
}
