<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SiteController;
use App\Models\Client;
use App\Models\PanelSetting;
use App\Models\Site;
use App\Models\SiteEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:15,1');
});
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => view('dashboard', [
        'siteCount' => Site::count(), 'clientCount' => Client::count(),
        'activeCount' => Site::where('status', 'active')->count(),
        'recentEvents' => SiteEvent::orderByDesc('occurred_on')->limit(5)->get(),
    ]))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::resource('clients', ClientController::class)->except(['show', 'destroy']);
    Route::view('/sites', 'sites.index')->name('sites.index');
    Route::resource('sites', SiteController::class)->except(['index', 'destroy']);
    Route::post('/sites/{site}/events', [SiteController::class, 'event'])->name('sites.events.store');
    Route::get('/settings', fn () => view('settings', ['interval' => PanelSetting::currentInterval()]))->name('settings');
    Route::put('/settings', function (Request $request) {
        $data = $request->validate(['monitor_interval_hours' => ['required', 'integer', 'min:1', 'max:720']]);
        PanelSetting::updateOrCreate(['id' => 1], $data);
        return redirect()->route('settings')->with('success', 'Frecuencia general actualizada.');
    })->name('settings.update');
});
