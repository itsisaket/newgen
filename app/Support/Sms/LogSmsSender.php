<?php

namespace App\Support\Sms;

use App\Contracts\SmsSender;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder SmsSender - NOT a real gateway. Writes to the dedicated
 * 'sms' log channel (storage/logs/sms.log, see config/logging.php) instead
 * of actually sending anything, so field staff/developers can still see
 * exactly what WOULD have been texted to a farmer while a real provider
 * is procured. Swap the App\Contracts\SmsSender binding in
 * AppServiceProvider::register() for a real implementation (Twilio, a
 * Thai SMS gateway, etc.) when one is selected - nothing else in the
 * codebase needs to change.
 */
class LogSmsSender implements SmsSender
{
    public function send(string $phone, string $message): void
    {
        Log::channel('sms')->info('[STUB - ไม่มี SMS gateway จริง] ข้อความที่ควรส่งถึงเบอร์ '.$phone, [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
