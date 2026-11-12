<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteController;
use App\Models\Client;
use App\Models\PanelSetting;
use App\Models\Site;
use App\Models\SiteEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:15,1');
});
Route::middleware('auth')->group(function () {
    Route::get('/settings/deployment-check', \App\Http\Controllers\DeploymentCheckController::class)->middleware('throttle:6,1');
    Route::get('/', fn () => view('dashboard', [
        'siteCount' => Site::count(), 'clientCount' => Client::count(),
        'activeCount' => Site::where('status', 'active')->count(),
        'recentEvents' => SiteEvent::orderByDesc('occurred_on')->limit(5)->get(),
    ]))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::resource('clients', ClientController::class)->except(['show']);
    Route::view('/sites', 'sites.index')->name('sites.index');
    Route::resource('sites', SiteController::class)->except(['index']);
    Route::post('/sites/{site}/events', [SiteController::class, 'event'])->name('sites.events.store');
    Route::post('/sites/{site}/monitor', [SiteController::class, 'checkMonitor'])->middleware('throttle:12,1')->name('sites.monitor.check');
    Route::post('/sites/{site}/availability', [SiteController::class, 'checkAvailability'])->middleware('throttle:12,1')->name('sites.availability.check');
    Route::get('/settings', fn () => view('settings', [
        'settings' => PanelSetting::find(1),
        'alerts' => DB::table('health_alerts')->join('sites', 'sites.id', '=', 'health_alerts.site_id')
            ->select('health_alerts.*', 'sites.name as site_name')->orderByDesc('health_alerts.id')->limit(20)->get(),
        'mailConfigured' => ! in_array(config('mail.mailers.'.config('mail.default').'.transport'), ['log', 'array', null], true),
    ]))->name('settings');
    Route::put('/settings', function (Request $request) {
        $data = $request->validate([
            'monitor_interval_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'availability_interval_minutes' => ['sometimes', 'integer', 'min:5', 'max:1440', 'multiple_of:5'],
            'health_alert_email' => ['nullable', 'email', 'max:255'],
            'availability_alerts_enabled' => ['sometimes', 'boolean'],
            'monitor_alerts_enabled' => ['sometimes', 'boolean'],
            'hosting_renewal_alerts_enabled' => ['sometimes', 'boolean'],
            'domain_renewal_alerts_enabled' => ['sometimes', 'boolean'],
        ]);
        PanelSetting::updateOrCreate(['id' => 1], $data);
        return redirect()->route('settings')->with('success', 'Frecuencia general actualizada.');
    })->name('settings.update');
});
