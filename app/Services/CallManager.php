<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;
use App\Repositories\CallRepository;

class CallManager
{
    protected $twilio;
    protected $voiceResponse;
    protected $callRepository;
    protected $agentNumber;
    protected $fromNumber;

    public function __construct(CallRepository $callRepository)
    {
        $this->twilio = new Client(config('services.twilio.account_sid'), config('services.twilio.auth_token'));
        $this->voiceResponse = new VoiceResponse();
        $this->callRepository = $callRepository;
        $this->fromNumber = config('services.twilio.from');
        $this->agentNumber = config('services.twilio.agent_number');
    }

    public function processIncomingCall($request)
    {
        $this->callRepository->createCall($request);

        $gather = $this->voiceResponse->gather([
                'numDigits' => 1,
                'action' => route('call-options')
            ]);

        $gather->say('Press 1 to forward call to an agent');
        $gather->say('Press 2 to leave voicemail');

        return response($this->voiceResponse);
    }

    public function directCall($request)
    {
        if ($request->input('Digits') == 1) {
            return $this->forwardCall();
        } elseif ($request->input('Digits') == 2) {
            return $this->recordVoicemail($request);
        } else {
            return $this->finishCall();
        }
    }

    public function finishCall()
    {
        $this->voiceResponse->say('Thank you, goodbye.');

        return response($this->voiceResponse);
    }

    public function forwardCall()
    {
        $this->voiceResponse->dial($this->agentNumber);

        return response($this->voiceResponse);
    }

    public function recordVoicemail($request)
    {
        // To Update Status in-progress
        // $call = $this->callRepository->getCall($request->input('CallSid'));

        // if ($call) {
        //     $status = $this->twilio->calls($call->sid)->fetch()->status;
        //     $this->callRepository->updateStatus($call, $status);
        // }

        $this->voiceResponse->record(['recordingStatusCallback' => route('handle-recording')]);

        return response($this->voiceResponse);
    }

    public function processRecording($request)
    {
        $call = $this->callRepository->getCall($request->input('CallSid'));

        $request->merge(['agent_number' => $this->agentNumber]);

        $call = $this->callRepository->updateCall($call, $request);

        $this->sendAgentText($call);

        return response('OK');
    }

    public function sendAgentText($call)
    {
        $message = "New voicemail from {$this->agentNumber}. Recording URL: {$call->recording_url}";

        $this->twilio->messages->create(
            $this->agentNumber,
            ['from' => $this->fromNumber, 'body' => $message]
        );
    }

    public function processStatus($request)
    {
        $callSid = $request->input('CallSid');

        $call = $this->callRepository->getCall($callSid);

        $callInfo = $this->twilio->calls($call->sid)->fetch()->fetch();

        $status = $callInfo->status;

        $request->merge([
            'call_raw' => $callInfo->toArray(),
            'call_status' => $status,
            'agent_number' => $this->agentNumber
        ]);

        $this->callRepository->updateCall($call, $request);

        return response('OK');
    }

}
