<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_settings', 'intents')) {
                $table->json('intents')->nullable()->after('restrictions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            if (Schema::hasColumn('ai_settings', 'intents')) {
                $table->dropColumn('intents');
            }
        });
    }
};
