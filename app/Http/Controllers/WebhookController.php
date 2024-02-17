<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CallManager;

class WebhookController extends Controller
{
    protected $callManager;

    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function handle(Request $request)
    {
        return $this->callManager->processIncomingCall($request);
    }

    public function callOptions(Request $request)
    {
        return $this->callManager->directCall($request);
    }

    public function handleRecording(Request $request)
    {
        return $this->callManager->processRecording($request);
    }

    public function handleStatus(Request $request)
    {
        return $this->callManager->processStatus($request);
    }
}
