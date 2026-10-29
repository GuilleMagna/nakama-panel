<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 60)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('url', 500)->unique();
            $table->string('ownership', 20)->default('client');
            $table->string('status', 20)->default('active');
            $table->string('admin_url', 500)->nullable();
            $table->string('monitor_endpoint', 500)->nullable();
            $table->text('monitor_token')->nullable();
            $table->unsignedInteger('monitor_interval_hours')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('site_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('category', 30);
            $table->string('title');
            $table->date('occurred_on');
            $table->boolean('approximate')->default(false);
            $table->text('description');
            $table->timestamps();
            $table->index(['site_id', 'occurred_on']);
        });
        Schema::create('panel_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('monitor_interval_hours')->default(12);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_settings');
        Schema::dropIfExists('site_events');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('clients');
    }
};
