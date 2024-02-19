<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Response;

interface CallProviderInterface
{
    public function processIncomingCall(Int $numDigits, String $action, array $messages);
    public function finishCall(String $message);
    public function forwardCall(String $to);
    public function recordVoicemail(String $callbackUrl);
    public function sendSms(String $to, String $message): void;
    public function getCallInfo(String $sid): object;
}
