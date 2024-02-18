<?php

namespace App\Repositories;

use App\Models\Call;
use Illuminate\Http\Request;

class CallRepository
{
    // Empty constructor
    public function __construct() {}

    public function getCall($sid)
    {
        return Call::where('sid', $sid)->firstOrFail();
    }

    public function createCall(Request $request)
    {
        $data = [
            'sid' => $request->input('CallSid'),
            'from' => $request->input('From'),
            'to' => $request->input('To'),
            'direction' => $request->input('Direction'),
            'status' => $request->input('CallStatus'),
            'call_raw' => $request->all(),
        ];

        return Call::create($data);
    }

    public function updateCall($call, Request $request)
    {
        $fieldsToUpdate = [
            'agent_number' => 'agent_number',
            'RecordingUrl' => 'recording_url',
            'call_status' => 'status',
            'call_raw' => 'call_raw'
        ];

        foreach ($fieldsToUpdate as $requestField => $callField) {
            if ($request->has($requestField)) {
                $call->{$callField} = $request->input($requestField);
            }
        }

        if ($request->has('RecordingUrl')) {
            $recording_raw = $request->except(['call_raw', 'agent_number', 'call_status']);
            $call->recording_raw = $recording_raw;
        }

        $call->update();

        return $call;
    }

    public function updateStatus($call, $status)
    {
        $call->status = $status;
        $call->update();

        return $call;
    }
}
