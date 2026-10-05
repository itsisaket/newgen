<?php

namespace App\Contracts;

/**
 * Backlog item (Blueprint หัวข้อ 4: "phone + OTP เป็นทางเลือกสำหรับ Farmer/
 * Innovator" - still just a concept, no gateway named). This interface is
 * the seam a real SMS provider (Twilio/Thai gateway/etc.) plugs into
 * later - see App\Support\Sms\LogSmsSender for the current stub
 * implementation and AppServiceProvider::register() for the binding.
 * Nothing in the app should depend on a concrete sender, only this
 * contract.
 */
interface SmsSender
{
    /**
     * Send $message to $phone. Implementations should never throw for an
     * expected delivery failure (no gateway configured, invalid number,
     * etc.) - log and return instead, so an SMS failure never blocks the
     * screen flow that triggered it (e.g. creating a farmer account).
     */
    public function send(string $phone, string $message): void;
}
