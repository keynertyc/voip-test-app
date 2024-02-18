<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface CallServiceInterface
{
    public function processIncomingCall(Request $request): Response;
    public function directCall(Request $request): Response;
    public function finishCall(String $message): Response;
    public function forwardCall(String $to): Response;
    public function recordVoicemail(Request $request): Response;
    public function processRecording(Request $request): Response;
    public function sendAgentText($call): void;
    public function processStatus(Request $request): Response;
}
