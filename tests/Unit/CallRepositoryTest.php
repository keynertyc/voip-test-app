<?php

use App\Models\Call;
use App\Repositories\CallRepository;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->callRepository = new CallRepository();
    $this->call = Mockery::mock('overload:' . Call::class);
});

it('gets a call', function () {
    $data = [
        'sid' => '123',
        'from' => '456',
        'to' => '789',
        'direction' => 'inbound',
        'status' => 'ringing'
    ];

    $this->call->shouldReceive('where->firstOrFail')->andReturn((object) $data);

    $response = $this->callRepository->getCall('123');

    expect($response->sid)->toBe('123');
    expect($response->from)->toBe('456');
    expect($response->to)->toBe('789');
    expect($response->direction)->toBe('inbound');
    expect($response->status)->toBe('ringing');
});

it('creates a call', function () {
    $request = new Request([
        'CallSid' => '123',
        'From' => '456',
        'To' => '789',
        'Direction' => 'inbound',
        'CallStatus' => 'ringing'
    ]);

    $this->call->shouldReceive('create')->andReturnSelf();

    $createdCall = $this->callRepository->createCall($request);

    expect($createdCall)->toBe($this->call);
});

it('updates a call', function () {
    $request = new Request([
        'agent_number' => '123',
        'RecordingUrl' => 'http://example.com'
    ]);

    $this->call->shouldReceive('update')->andReturnSelf();

    $updatedCall = $this->callRepository->updateCall($this->call, $request);

    expect($updatedCall)->toBe($this->call);
});

it('updates call status', function () {
    $this->call->shouldReceive('update')->andReturnSelf();

    $updatedCall = $this->callRepository->updateStatus($this->call, 'completed');

    expect($updatedCall)->toBe($this->call);
});
