<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an OTP SMS to a mobile number.
     *
     * ─────────────────────────────────────────────────────────────────────────
     * TODO: SMS_PROVIDER
     * ─────────────────────────────────────────────────────────────────────────
     * Replace the placeholder block below with your chosen SMS provider.
     * Add the required credentials to .env and config/services.php.
     *
     * ── Fast2SMS ─────────────────────────────────────────────────────────────
     * Sign up at https://www.fast2sms.com  →  get API key  →  add to .env:
     *   FAST2SMS_API_KEY=your_api_key_here
     *
     *   $response = \Illuminate\Support\Facades\Http::withHeaders([
     *       'authorization' => config('services.fast2sms.key'),
     *   ])->post('https://www.fast2sms.com/dev/bulkV2', [
     *       'route'    => 'otp',
     *       'variables_values' => $otp,
     *       'numbers'  => $phone,
     *   ]);
     *   return $response->successful();
     *
     * ── MSG91 ────────────────────────────────────────────────────────────────
     * Sign up at https://msg91.com  →  get auth key + template ID → add to .env:
     *   MSG91_AUTH_KEY=your_auth_key_here
     *   MSG91_TEMPLATE_ID=your_template_id_here
     *
     *   $response = \Illuminate\Support\Facades\Http::withHeaders([
     *       'authkey'      => config('services.msg91.key'),
     *       'content-type' => 'application/json',
     *   ])->post('https://api.msg91.com/api/v5/otp', [
     *       'template_id' => config('services.msg91.template_id'),
     *       'mobile'      => '91' . $phone,
     *       'otp'         => $otp,
     *   ]);
     *   return $response->successful();
     *
     * ── 2Factor ──────────────────────────────────────────────────────────────
     * Sign up at https://2factor.in  →  get API key → add to .env:
     *   TWOFACTOR_API_KEY=your_api_key_here
     *
     *   $response = \Illuminate\Support\Facades\Http::get(
     *       "https://2factor.in/API/V1/" . config('services.twofactor.key') .
     *       "/SMS/+91{$phone}/{$otp}"
     *   );
     *   return $response->successful();
     * ─────────────────────────────────────────────────────────────────────────
     *
     * @param  string $phone  10-digit Indian mobile number (no country code)
     * @param  string $otp    6-digit OTP code
     * @return bool           true on success, false on failure
     */
    public static function sendOtp(string $phone, string $otp): bool
    {
        // ── PLACEHOLDER ───────────────────────────────────────────────────────
        // In development, the OTP is logged instead of sent via SMS.
        // Replace this block with one of the provider examples above.
        // ─────────────────────────────────────────────────────────────────────
        Log::info('[SmsService] DEV MODE — OTP not sent via SMS', [
            'phone' => $phone,
            'otp'   => $otp,
            'note'  => 'Plug in your SMS provider here (see SmsService.php comments).',
        ]);

        // Simulate a successful send — remove this line once a real provider is added
        return true;
        // ─────────────────────────────────────────────────────────────────────
    }
}
