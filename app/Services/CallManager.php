<?php

namespace App\Services;

use App\Exceptions\CallHandlerException;
use App\Repositories\CallRepository;
use App\Services\Interfaces\CallProviderInterface;
use App\Services\Interfaces\CallServiceInterface;
use App\Services\Providers\TwilioProvider;
use Illuminate\Http\Response;

class CallManager implements CallServiceInterface
{
    protected $callProvider;
    protected $callRepository;
    protected $agentNumber;

    public static $providers = [
        'twilio' => TwilioProvider::class,
    ];

    public function __construct(CallProviderInterface $callProvider, CallRepository $callRepository)
    {
        $this->callProvider = $callProvider;
        $this->callRepository = $callRepository;
        $this->agentNumber = config('services.twilio.agent_number');
    }

    public function processIncomingCall($request): Response
    {
        try {
            $this->callRepository->createCall($request);

            $messages = $this->getMessagesForIncomingCall();
            $numDigits = $this->calculateNumDigits($messages);

            return $this->callProvider->processIncomingCall($numDigits, route('call-options'), $messages);
        } catch (\Exception $e) {
            throw new CallHandlerException($e->getMessage(), $e->getCode());
        }
    }

    public function directCall($request): Response
    {
        try {
            if ($request->input('Digits') == 1) {
                return $this->forwardCall($this->agentNumber);
            } elseif ($request->input('Digits') == 2) {
                return $this->recordVoicemail($request);
            } else {
                return $this->finishCall('Thank you, goodbye.');
            }
        } catch (\Exception $e) {
            throw new CallHandlerException($e->getMessage(), $e->getCode());
        }
    }

    public function finishCall(String $message): Response
    {
        try {
            return $this->callProvider->finishCall($message);
        } catch (\Exception $e) {
            throw new CallHandlerException($e->getMessage(), $e->getCode());
        }
    }

    public function forwardCall(String $to): Response
    {
        try {
            return $this->callProvider->forwardCall($to);
        } catch (\Exception $e) {
            throw new CallHandlerException($e->getMessage(), $e->getCode());
        }
    }

    public function recordVoicemail($request): Response
    {
        try {
            return $this->callProvider->recordVoicemail($request);
        } catch (\Exception $e) {
            throw new CallHandlerException($e->getMessage(), $e->getCode());
        }
    }

    public function processRecording($request): Response
    {
        try {
            $call = $this->callRepository->getCall($request->input('CallSid'));

            $request->merge(['agent_number' => $this->agentNumber]);

            $call = $this->callRepository->updateCall($call, $request);

            $message = $this->getRecordingMessage($call->from, $call->recording_url);

            $this->sendAgentText($message);

            return response('OK');
        } catch (\Exception $e) {
            throw new CallHandlerException($e->getMessage(), $e->getCode());
        }
    }

    public function sendAgentText($message): void
    {
        try {
            $this->callProvider->sendSms($this->agentNumber, $message);
        } catch (\Exception $e) {
            throw new CallHandlerException($e->getMessage(), $e->getCode());
        }
    }

    public function processStatus($request): Response
    {
        try {
            $callSid = $request->input('CallSid');

            $call = $this->callRepository->getCall($callSid);

            $callInfo = $this->callProvider->getCallInfo($callSid);

            $status = $callInfo->status;

            $request->merge([
                'call_raw' => $callInfo->toArray(),
                'call_status' => $status,
                'agent_number' => $this->agentNumber
            ]);

            $this->callRepository->updateCall($call, $request);

            return response('OK');
        } catch (\Exception $e) {
            throw new CallHandlerException($e->getMessage(), $e->getCode());
        }
    }

    private function getRecordingMessage(String $from, String $recordingUrl): String
    {
        return "New voicemail from {$from}. Recording URL: {$recordingUrl}";
    }

    private function getMessagesForIncomingCall(): array
    {
        return [
            'Press 1 to speak to an agent.',
            'Press 2 to leave a voicemail.'
        ];
    }

    private function calculateNumDigits($messages): int
    {
        $numDigits = 0;

        foreach ($messages as $message) {
            $numDigits += strlen($message);
        }

        return ceil(log10($numDigits + 1));
    }
}
