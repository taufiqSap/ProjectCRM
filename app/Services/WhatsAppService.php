<?php

namespace App\Services;

class WhatsappService
{
    public static function send(string $phone, string $message)
    {
        // isi dengan API WhatsApp yang ingin kita buat
        $url = ''; // url api whatsapp disini
        $token = env(''); // token pi masukkan di .env, lalu diketik di dalam itu

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->post($url, [
            'target' => $phone,
            'message' => $message,
        ]);

        return $response->json();
    }
}

?>