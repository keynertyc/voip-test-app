<?php

use App\Services\Providers\TwilioProvider;
use Twilio\Http\Response;
use Twilio\Rest\Client;
use Twilio\TwiML\Voice\Gather;
use Twilio\TwiML\VoiceResponse;

beforeEach(function () {
    $this->config = Mockery::mock('overload:config');
    $this->config->shouldReceive('get')
        ->with('services.twilio.account_sid', Mockery::any())
        ->andReturn('account_sid');
    $this->config->shouldReceive('get')
        ->with('services.twilio.auth_token', Mockery::any())
        ->andReturn('auth_token');
    $this->config->shouldReceive('get')
        ->with('services.twilio.from', Mockery::any())
        ->andReturn('from_number');
    $this->config->shouldReceive('get')
        ->with('services.twilio.agent_number', Mockery::any())
        ->andReturn('agent_number');

    $this->twilio = Mockery::mock(Client::class);
    $this->voiceResponse = Mockery::mock(VoiceResponse::class);

});

it('processes incoming call', function () {
    $gather = Mockery::mock(Gather::class);
    $gather->shouldReceive('say')->andReturn($gather);

    $this->voiceResponse->shouldReceive('gather')->andReturn($gather);

    $numDigits = 1;
    $action = 'testAction';
    $messages = ['textToVoice1', 'textToVoice2'];

    $twilioProvider = new TwilioProvider();

    $result = $twilioProvider->processIncomingCall($numDigits, $action, $messages);

    expect($result)->toBeInstanceOf(VoiceResponse::class);
});

it('finishes a call', function () {
    $this->voiceResponse = Mockery::mock(VoiceResponse::class);
    $this->voiceResponse->shouldReceive('say')->with('Goodbye');

    $twilioProvider = new TwilioProvider();

    $result = $twilioProvider->finishCall('Goodbye');

    expect($result)->toBeInstanceOf(VoiceResponse::class);
});

it('forwards a call', function () {
    $this->voiceResponse = Mockery::mock(VoiceResponse::class);
    $this->voiceResponse->shouldReceive('dial')->with('+1234567890');

    $twilioProvider = new TwilioProvider();

    $result = $twilioProvider->forwardCall('+1234567890');

    expect($result)->toBeInstanceOf(VoiceResponse::class);
});

it('records a voicemail', function () {
    $this->voiceResponse = Mockery::mock(VoiceResponse::class);
    $this->voiceResponse->shouldReceive('record')->with(['recordingStatusCallback' => 'handle-recording']);

    $twilioProvider = new TwilioProvider();

    $result = $twilioProvider->recordVoicemail('handle-recording');

    expect($result)->toBeInstanceOf(VoiceResponse::class);
});

it('sends an SMS', function () {
    $this->twilio->shouldReceive('getAccountSid')->andReturn('ACxxxxxx');

    $this->twilio->shouldReceive('request')
        ->andReturn(new Response(201, json_encode([
            'sid' => 'SMxxxxxxx',
            'status' => 'queued',
            'from' => '1234',
            'body' => 'Hello'
        ])));

    $this->twilio->messages = Mockery::mock();

    $this->twilio->messages->shouldReceive('create')
        ->once()
        ->with('+123', ['from' => '1234', 'body' => 'Hello']);

    $provider = new TwilioProvider();

    $provider->twilio = $this->twilio;
    $provider->fromNumber = '1234';

    $provider->sendSms('+123', 'Hello');
});

it('gets call info', function () {
    $response = new Response(200, json_encode([
        'sid' => 'CAxxxxxx',
        'status' => 'in-progress',
        'from' => '1234',
        'to' => '5678',
    ]));

    $this->twilio->shouldReceive('calls')
        ->with('CAxxxxxx')
        ->andReturnSelf();

    $this->twilio->shouldReceive('fetch')
        ->once()
        ->andReturn($response);

    $provider = new TwilioProvider();
    $provider->twilio = $this->twilio;

    $result = $provider->getCallInfo('CAxxxxxx');
    $content = (object) $result->getContent();

    expect($result)->toBeObject();
    expect($content->sid)->toBe('CAxxxxxx');
    expect($content->status)->toBe('in-progress');
    expect($content->from)->toBe('1234');
    expect($content->to)->toBe('5678');
});
