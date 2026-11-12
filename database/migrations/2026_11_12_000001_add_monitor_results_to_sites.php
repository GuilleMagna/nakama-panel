<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->json('monitor_snapshot')->nullable();
            $table->timestamp('monitor_checked_at')->nullable();
            $table->timestamp('monitor_attempted_at')->nullable();
            $table->string('monitor_error')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sites', fn (Blueprint $table) => $table->dropColumn([
            'monitor_snapshot', 'monitor_checked_at', 'monitor_attempted_at', 'monitor_error',
        ]));
    }
};
