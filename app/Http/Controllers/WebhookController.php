<?php

namespace App\Http\Controllers;

use App\Exceptions\CallHandlerException;
use App\Services\CallManager;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $callManager;

    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function handle(Request $request)
    {
        try {
            return $this->callManager->processIncomingCall($request);
        } catch (CallHandlerException $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function callOptions(Request $request)
    {
        try {
            return $this->callManager->directCall($request);
        } catch (CallHandlerException $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function handleRecording(Request $request)
    {
        try {
            return $this->callManager->processRecording($request);
        } catch (CallHandlerException $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function handleStatus(Request $request)
    {
        try {
            return $this->callManager->processStatus($request);
        } catch (CallHandlerException $e) {
            report($e);
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
