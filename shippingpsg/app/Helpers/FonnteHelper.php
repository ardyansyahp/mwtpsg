<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteHelper
{
    /**
     * Kirim pesan WhatsApp menggunakan Fonnte API
     *
     * @param string $target Nomor WhatsApp tujuan (bisa comma separated)
     * @param string $message Isi pesan
     * @param string|null $countryCode Kode negara (default 62)
     * @return array
     */
    public static function send($target, $message, $countryCode = '62')
    {
        $token = env('FONNTE_TOKEN'); // Pastikan Anda menambahkan FONNTE_TOKEN di .env

        if (empty($token)) {
            Log::warning('Fonnte Token belum disetting di .env');
            return ['status' => false, 'reason' => 'Token not set'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => $countryCode, 
            ]);

            $result = $response->json();
            
            if (!$response->successful()) {
                Log::error('Fonnte API Error: ' . $response->body());
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Fonnte Exception: ' . $e->getMessage());
            return ['status' => false, 'reason' => $e->getMessage()];
        }
    }
}
