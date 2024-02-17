<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $guarded = [];

    protected $casts = [
        'call_raw' => 'array',
        'recording_raw' => 'array'
    ];

    public function getCallRawAttribute($value)
    {
        return $value ?: $this->attributes['call_raw'];
    }

    public function getRecordingRawAttribute($value)
    {
        return $value ?: $this->attributes['recording_raw'];
    }
}
