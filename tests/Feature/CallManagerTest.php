<?php

use App\Exceptions\CallHandlerException;
use App\Repositories\CallRepository;
use App\Services\CallManager;
use App\Services\Interfaces\CallProviderInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Twilio\TwiML\VoiceResponse;

it('processes incoming call', function () {
    $callProvider = mock(CallProviderInterface::class);
    $callRepository = mock(CallRepository::class);
    $voiceResponse = Mockery::mock(VoiceResponse::class);

    $request = new Request([
        'some_key' => 'some_value',
        // ...
    ]);

    $callRepository->shouldReceive('createCall')->once();

    $actionUrl = env('APP_URL') . '/api/call-options';

    $callProvider->shouldReceive('processIncomingCall')->once()
        ->with(
            2,
            $actionUrl,
            [0 => 'Press 1 to speak to an agent.', 1 => 'Press 2 to leave a voicemail.']
        )
        ->andReturn($voiceResponse);

    $callManager = new CallManager($callProvider, $callRepository);
    $result = $callManager->processIncomingCall($request);

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getStatusCode())->toBe(200);
});

it('throws CallHandlerException on failure to process incoming call', function () {
    $callProvider = mock(CallProviderInterface::class);
    $callRepository = mock(CallRepository::class);

    $request = new Request([
        'some_key' => 'some_value',
        // ...
    ]);

    $callRepository->shouldReceive('createCall')->andThrow(new \Exception('Some error message'));

    $callManager = new CallManager($callProvider, $callRepository);

    expect(function () use ($callManager, $request) {
        $callManager->processIncomingCall($request);
    })->toThrow(CallHandlerException::class);
});

it('directs call to agent', function () {
    $callProvider = mock(CallProviderInterface::class);
    $callRepository = mock(CallRepository::class);
    $voiceResponse = Mockery::mock(VoiceResponse::class);

    $request = new Request([
        'Digits' => 1,
        // ...
    ]);

    $callManager = new CallManager($callProvider, $callRepository);

    $callProvider->shouldReceive('forwardCall')->once()
        ->with('+51943934785')
        ->andReturn($voiceResponse);

    $result = $callManager->directCall($request);

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getStatusCode())->toBe(200);
});

it('directs call to voicemail', function () {
    $callProvider = mock(CallProviderInterface::class);
    $callRepository = mock(CallRepository::class);
    $voiceResponse = Mockery::mock(VoiceResponse::class);

    $request = new Request([
        'Digits' => 2,
        // ...
    ]);

    $callManager = new CallManager($callProvider, $callRepository);

    $callProvider->shouldReceive('recordVoicemail')->once()
        ->with(route('handle-recording'))
        ->andReturn($voiceResponse);

    $result = $callManager->directCall($request);

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getStatusCode())->toBe(200);
});

it('finishes call', function () {
    $callProvider = mock(CallProviderInterface::class);
    $callRepository = mock(CallRepository::class);
    $voiceResponse = Mockery::mock(VoiceResponse::class);

    $request = new Request([
        'Digits' => 3,
        // ...
    ]);

    $callManager = new CallManager($callProvider, $callRepository);

    $callProvider->shouldReceive('finishCall')->once()
        ->with('Thank you, goodbye.')
        ->andReturn($voiceResponse);

    $result = $callManager->directCall($request);

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getStatusCode())->toBe(200);
});

it('throws CallHandlerException on failure to direct call', function () {
    $callProvider = mock(CallProviderInterface::class);
    $callRepository = mock(CallRepository::class);

    $request = new Request([
        'Digits' => 3,
        // ...
    ]);

    $callManager = new CallManager($callProvider, $callRepository);

    expect(function () use ($callManager, $request) {
        $callManager->directCall($request);
    })->toThrow(CallHandlerException::class);
});

it('processes recording', function () {
    $callProvider = mock(CallProviderInterface::class);
    $callRepository = mock(CallRepository::class);

    $request = new Request([
        'CallSid' => 'CA1234567890',
        'agent_number' => '+51943934785'
    ]);

    $stdClassMock = Mockery::mock(stdClass::class);

    $call = new stdClass;
    $call->from = '+51943934785';
    $call->recording_url = 'http://test-app.test/recordings/CA1234567890.mp3';

    $callProvider->shouldReceive('sendSms')->once()
        ->with('+51943934785', "New voicemail from {$call->from}. Recording URL: {$call->recording_url}");

    $callRepository->shouldReceive('getCall')->once()
        ->with('CA1234567890')
        ->andReturn($stdClassMock);

    $callRepository->shouldReceive('updateCall')->once()
        ->with($stdClassMock, Mockery::on(function ($request) {
            return $request->input('agent_number') === '+51943934785';
        }))
        ->andReturn($call);

    $callManager = new CallManager($callProvider, $callRepository);
    $result = $callManager->processRecording($request);

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getStatusCode())->toBe(200);
});

it('throws CallHandlerException on failure to process recording', function () {
    $callProvider = mock(CallProviderInterface::class);
    $callRepository = mock(CallRepository::class);

    $request = new Request([
        'CallSid' => 'CA1234567890',
        // ...
    ]);

    $callRepository->shouldReceive('getCall')->andThrow(new \Exception('Some error message'));

    $callManager = new CallManager($callProvider, $callRepository);

    expect(function () use ($callManager, $request) {
        $callManager->processRecording($request);
    })->toThrow(CallHandlerException::class);
});
