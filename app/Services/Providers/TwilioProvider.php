<?php

namespace App\Services\Providers;

use App\Services\Interfaces\CallProviderInterface;
use Illuminate\Http\Response;
use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;

class TwilioProvider implements CallProviderInterface
{
    public $twilio;
    protected $voiceResponse;
    protected $agentNumber;
    public $fromNumber;

    // Empty constructor
    public function __construct()
    {
        $this->twilio = new Client(config('services.twilio.account_sid'), config('services.twilio.auth_token'));
        $this->voiceResponse = new VoiceResponse();
        $this->fromNumber = config('services.twilio.from');
        $this->agentNumber = config('services.twilio.agent_number');
    }

    public function processIncomingCall(Int $numDigits, String $action, array $messages)
    {
        $gather = $this->voiceResponse->gather([
                'numDigits' => $numDigits,
                'action' => $action
            ]);

        foreach ($messages as $message) {
            $gather->say($message);
        }

        return $this->voiceResponse;
    }

    public function finishCall(String $message)
    {
        $this->voiceResponse->say($message);

        return $this->voiceResponse;
    }

    public function forwardCall(String $to)
    {
        $this->voiceResponse->dial($to);

        return $this->voiceResponse;
    }

    public function recordVoicemail(String $callbackUrl)
    {
        $this->voiceResponse->record(['recordingStatusCallback' => $callbackUrl]);

        return $this->voiceResponse;
    }

    public function sendSms(String $to, String $message): void
    {
        $this->twilio->messages->create(
            $to,
            ['from' => $this->fromNumber, 'body' => $message]
        );
    }

    public function getCallInfo(String $sid): object
    {
        return $this->twilio->calls($sid)->fetch();
    }
}
