<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'name')) {
                $table->string('name')->nullable()->after('instance_id');
            }

            if (! Schema::hasColumn('leads', 'messages_count')) {
                $table->unsignedInteger('messages_count')->default(0)->after('intent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'messages_count')) {
                $table->dropColumn('messages_count');
            }

            if (Schema::hasColumn('leads', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
