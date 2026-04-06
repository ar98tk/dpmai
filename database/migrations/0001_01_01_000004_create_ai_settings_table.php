<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')->unique()->constrained('whatsapp_instances')->cascadeOnDelete();
            $table->longText('system_prompt');
            $table->longText('rules')->nullable();
            $table->longText('restrictions')->nullable();
            $table->string('model')->default('gpt-4o-mini');
            $table->float('temperature')->default(0.7);
            $table->integer('max_tokens')->default(500);
            $table->integer('context_limit')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
