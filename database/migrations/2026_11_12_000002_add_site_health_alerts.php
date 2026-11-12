<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->timestamp('availability_checked_at')->nullable();
            $table->string('availability_error')->nullable();
            $table->boolean('health_alerts_enabled')->default(true);
        });
        Schema::table('panel_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('availability_interval_minutes')->default(30);
            $table->string('health_alert_email')->nullable();
            $table->boolean('availability_alerts_enabled')->default(true);
            $table->boolean('monitor_alerts_enabled')->default(true);
        });
        Schema::create('health_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('detail');
            $table->timestamp('created_at');
            $table->timestamp('next_attempt_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('delivery_failed')->default(false);
            $table->index(['sent_at', 'cancelled_at', 'next_attempt_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_alerts');
        Schema::table('sites', fn (Blueprint $table) => $table->dropColumn(['availability_checked_at', 'availability_error', 'health_alerts_enabled']));
        Schema::table('panel_settings', fn (Blueprint $table) => $table->dropColumn(['availability_interval_minutes', 'health_alert_email', 'availability_alerts_enabled', 'monitor_alerts_enabled']));
    }
};
