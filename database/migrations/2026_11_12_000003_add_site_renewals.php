<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('hosting_renewal', 10)->nullable();
            $table->string('domain_renewal', 10)->nullable();
            $table->boolean('renewal_alerts_enabled')->default(true);
        });
        Schema::table('panel_settings', function (Blueprint $table) {
            $table->boolean('hosting_renewal_alerts_enabled')->default(true);
            $table->boolean('domain_renewal_alerts_enabled')->default(true);
        });
        Schema::table('health_alerts', function (Blueprint $table) {
            $table->string('renewal_period', 10)->nullable();
            $table->unique(['site_id', 'kind', 'renewal_period']);
        });
    }

    public function down(): void
    {
        Schema::table('health_alerts', function (Blueprint $table) {
            $table->dropUnique(['site_id', 'kind', 'renewal_period']);
            $table->dropColumn('renewal_period');
        });
        Schema::table('panel_settings', fn (Blueprint $table) => $table->dropColumn(['hosting_renewal_alerts_enabled', 'domain_renewal_alerts_enabled']));
        Schema::table('sites', fn (Blueprint $table) => $table->dropColumn(['hosting_renewal', 'domain_renewal', 'renewal_alerts_enabled']));
    }
};
