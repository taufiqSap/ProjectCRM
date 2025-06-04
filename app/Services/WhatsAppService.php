<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public static function send(string $phone, string $message): bool
    {
        $apiUrl  = config('services.whatsapp.url');
        $appKey  = config('services.whatsapp.appkey');
        $authKey = config('services.whatsapp.authkey');

        // Debug log untuk cek nilai config
        Log::info('WA Config Debug', [
            'apiUrl'  => $apiUrl,
            'appKey'  => $appKey,
            'authKey' => $authKey,
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'appkey'  => $appKey,
                'authkey' => $authKey,
                'to'      => $phone,
                'message' => $message,
                'sandbox' => 'false',
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            Log::error('WA API Curl Error: ' . $error);
            return false;
        }

        Log::info('WA API Response: ' . $response);

        $data = json_decode($response, true);

        return !empty($data['success']) && $data['success'] === true;
    }
}
