<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('plan_id')
                ->nullable()
                ->after('id')
                ->constrained('plans')
                ->nullOnDelete();
            $table->unsignedBigInteger('daily_tokens_used')->default(0)->after('status');
            $table->unsignedBigInteger('monthly_tokens_used')->default(0)->after('daily_tokens_used');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn(['daily_tokens_used', 'monthly_tokens_used']);
        });
    }
};
