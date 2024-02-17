<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->string('sid')->unique();
            $table->string('status');
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
            $table->string('from');
            $table->string('to');
            $table->json('call_raw')->nullable();
            $table->string('agent_number')->nullable();
            $table->text('recording_url')->nullable();
            $table->json('recording_raw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
