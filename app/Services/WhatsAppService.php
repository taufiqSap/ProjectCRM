<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsappService
{
    public static function send(string $phone, string $message)
    {
        $url = 'https://app.saungwa.com/api/create-message';

        $response = Http::asMultipart()->post($url, [
            ['name' => 'appkey', 'contents' => env('SAUNGWA_APP_KEY')],
            ['name' => 'authkey', 'contents' => env('SAUNGWA_AUTH_KEY')],
            ['name' => 'to', 'contents' => $phone],
            ['name' => 'message', 'contents' => $message],
            ['name' => 'sandbox', 'contents' => 'false'],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Gagal mengirim pesan WhatsApp: ' . $response->body());
        }

        return $response->json();
    }
}
