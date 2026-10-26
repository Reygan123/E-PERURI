<?php

declare(strict_types=1);

namespace App\Modules\Platform\Services\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppChannel
{
    /**
     * Send WhatsApp message to target recipient.
     *
     * @param  string  $phone  Recipient phone number
     * @param  string  $message  Text message formatted in WhatsApp Markdown
     * @return bool True if successfully dispatched or logged
     */
    public function send(string $phone, string $message): bool
    {
        $normalizedPhone = $this->normalizePhoneNumber($phone);
        if (empty($normalizedPhone)) {
            Log::warning("WhatsApp dispatch failed: invalid or empty phone number [{$phone}].");

            return false;
        }

        $driver = config('services.whatsapp.driver', 'log');

        return match (strtolower((string) $driver)) {
            'fonnte' => $this->sendViaFonnte($normalizedPhone, $message),
            'qontak' => $this->sendViaQontak($normalizedPhone, $message),
            'wablas' => $this->sendViaWablas($normalizedPhone, $message),
            'array' => true, // for unit testing
            default => $this->sendViaLog($normalizedPhone, $message),
        };
    }

    /**
     * Normalize Indonesian phone number to international format (628xxx).
     */
    public function normalizePhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (empty($cleaned)) {
            return '';
        }

        if (str_starts_with($cleaned, '08')) {
            return '628'.substr($cleaned, 2);
        }

        if (str_starts_with($cleaned, '8')) {
            return '628'.substr($cleaned, 1);
        }

        return $cleaned;
    }

    protected function sendViaLog(string $phone, string $message): bool
    {
        Log::channel('single')->info("WhatsApp [LOG DRIVER] sent to {$phone}:\n{$message}");

        return true;
    }

    protected function sendViaFonnte(string $phone, string $message): bool
    {
        $url = config('services.whatsapp.api_url', 'https://api.fonnte.com/send');
        $token = config('services.whatsapp.api_token', '');

        if (empty($token)) {
            Log::warning('Fonnte WhatsApp dispatch aborted: WHATSAPP_API_TOKEN is empty.');

            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->timeout(10)
                ->post($url, [
                    'target' => $phone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info("Fonnte WhatsApp successfully delivered to {$phone}.");

                return true;
            }

            Log::error("Fonnte WhatsApp delivery error to {$phone}: HTTP {$response->status()} - {$response->body()}");

            return false;
        } catch (Throwable $e) {
            Log::error("Fonnte WhatsApp exception to {$phone}: {$e->getMessage()}");

            return false;
        }
    }

    protected function sendViaQontak(string $phone, string $message): bool
    {
        $url = config('services.whatsapp.api_url', 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct');
        $token = config('services.whatsapp.qontak_token', config('services.whatsapp.api_token', ''));

        if (empty($token)) {
            Log::warning('Qontak WhatsApp dispatch aborted: WHATSAPP_API_TOKEN is empty.');

            return false;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->post($url, [
                    'to_number' => $phone,
                    'to_name' => 'Pejabat PTKP',
                    'message' => $message,
                ]);

            return $response->successful();
        } catch (Throwable $e) {
            Log::error("Qontak WhatsApp exception to {$phone}: {$e->getMessage()}");

            return false;
        }
    }

    protected function sendViaWablas(string $phone, string $message): bool
    {
        $url = config('services.whatsapp.wablas_url', config('services.whatsapp.api_url', 'https://api.wablas.com/api/send-message'));
        $token = config('services.whatsapp.wablas_token', config('services.whatsapp.api_token', ''));

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->timeout(10)
                ->post($url, [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            return $response->successful();
        } catch (Throwable $e) {
            Log::error("Wablas WhatsApp exception to {$phone}: {$e->getMessage()}");

            return false;
        }
    }
}
