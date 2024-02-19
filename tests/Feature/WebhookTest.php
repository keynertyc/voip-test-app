<?php

use App\Http\Controllers\WebhookController;
use App\Services\CallManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

beforeEach(function () {
    $this->callManager = Mockery::mock(CallManager::class);
    $this->controller = new WebhookController($this->callManager);
    $this->responseMock = Mockery::mock(Response::class);
});

it('handles incoming call', function () {
    $request = new Request();
    $this->callManager->shouldReceive('processIncomingCall')->once()->andReturn($this->responseMock);

    $response = $this->controller->handle($request);

    expect($response)->toBe($this->responseMock);
});

it('handles call options', function () {
    $request = new Request();
    $this->responseMock = Mockery::mock(Response::class);
    $this->callManager->shouldReceive('directCall')->once()->andReturn($this->responseMock);

    $response = $this->controller->callOptions($request);

    expect($response)->toBe($this->responseMock);
});

it('handles recording', function () {
    $request = new Request();
    $this->responseMock = Mockery::mock(Response::class);
    $this->callManager->shouldReceive('processRecording')->once()->andReturn($this->responseMock);

    $response = $this->controller->handleRecording($request);

    expect($response)->toBe($this->responseMock);
});

it('handles status', function () {
    $request = new Request();
    $this->responseMock = Mockery::mock(Response::class);
    $this->callManager->shouldReceive('processStatus')->once()->andReturn($this->responseMock);

    $response = $this->controller->handleStatus($request);

    expect($response)->toBe($this->responseMock);
});
