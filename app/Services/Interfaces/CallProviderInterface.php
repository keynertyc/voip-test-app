<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Response;

interface CallProviderInterface
{
    public function processIncomingCall(Int $numDigits, String $action, array $messages): Response;
    public function finishCall(String $message): Response;
    public function forwardCall(String $to): Response;
    public function recordVoicemail(): Response;
    public function sendSms(String $to, String $message): void;
    public function getCallInfo(String $sid): object;
}
