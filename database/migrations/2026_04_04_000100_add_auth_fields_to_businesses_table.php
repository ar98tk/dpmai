<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('name');
            $table->string('password')->nullable()->after('email');
            $table->string('phone')->nullable()->after('password');
            $table->timestamp('email_verified_at')->nullable()->after('phone');
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn([
                'email',
                'password',
                'phone',
                'email_verified_at',
                'remember_token',
            ]);
        });
    }
};
