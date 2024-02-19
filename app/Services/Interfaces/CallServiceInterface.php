<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface CallServiceInterface
{
    public function processIncomingCall(Request $request): Response;
    public function directCall(Request $request): Response;
    public function processRecording(Request $request): Response;
    public function processStatus(Request $request): Response;
}
