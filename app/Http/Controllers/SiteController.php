<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Site;
use App\Models\SiteEvent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    public function create()
    {
        return view('sites.form', ['site' => new Site(['ownership' => 'client', 'status' => 'active']), 'clients' => Client::orderBy('name')->get()]);
    }

    public function show(Site $site)
    {
        $site->load('client');
        $events = $site->events()->orderByDesc('occurred_on')->orderByDesc('id')->paginate(15);

        return view('sites.show', compact('site', 'events'));
    }

    public function edit(Site $site)
    {
        return response()->view('sites.form', ['site' => $site, 'clients' => Client::orderBy('name')->get()])->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $credentials = $data['access'] ?? [];
        unset($data['access']);
        $site = new Site($data);
        $site->access_credentials = $credentials;
        $site->save();

        return redirect()->route('sites.show', $site)->with('success', 'Sitio registrado. Todavía no se realizaron consultas al monitor.');
    }

    public function update(Request $request, Site $site)
    {
        $data = $this->validated($request, $site);
        $credentials = $site->access_credentials ?? [];
        foreach ($data['access'] ?? [] as $kind => $fields) {
            if (($fields['password'] ?? null) === null || $fields['password'] === '') {
                unset($fields['password']);
            }
            $credentials[$kind] = array_replace($credentials[$kind] ?? [], $fields);
        }
        unset($data['access']);
        $site->access_credentials = $credentials;
        if (empty($data['monitor_token'])) {
            unset($data['monitor_token']);
        }
        $site->update($data);

        return redirect()->route('sites.show', $site)->with('success', 'Sitio actualizado.');
    }

    public function event(Request $request, Site $site)
    {
        $data = $request->validate(['category' => ['required', Rule::in(array_keys(SiteEvent::CATEGORIES))], 'title' => ['required', 'string', 'max:255'], 'occurred_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'], 'approximate' => ['sometimes', 'boolean'], 'description' => ['required', 'string', 'max:10000']]);
        $site->events()->create([...$data, 'user_id' => $request->user()->id]);

        return redirect()->route('sites.show', $site)->with('success', 'Evento agregado a la historia del sitio.');
    }

    private function validated(Request $request, ?Site $site = null): array
    {
        $request->merge(['url' => rtrim(trim((string) $request->input('url')), '/')]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:http,https', 'max:500', Rule::unique('sites')->ignore($site?->id)],
            'client_id' => ['nullable', 'required_if:ownership,client', 'integer', 'exists:clients,id'],
            'ownership' => ['required', Rule::in(['own', 'client'])],
            'status' => ['required', Rule::in(['active', 'paused', 'archived'])],
            'admin_url' => ['nullable', 'url:http,https', 'max:500'],
            'monitor_endpoint' => ['nullable', 'url:https', 'max:500'],
            'monitor_token' => ['nullable', 'string', 'max:255'],
            'monitor_interval_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'access' => ['sometimes', 'array:wordpress,cpanel,ftp'],
            'access.wordpress' => ['sometimes', 'array:username,password'],
            'access.cpanel' => ['sometimes', 'array:url,username,password'],
            'access.ftp' => ['sometimes', 'array:host,port,protocol,username,password'],
            'access.*.username' => ['nullable', 'string', 'max:255'],
            'access.*.password' => ['nullable', 'string', 'max:1000'],
            'access.cpanel.url' => ['nullable', 'url:http,https', 'max:500'],
            'access.ftp.host' => ['nullable', 'string', 'max:255'],
            'access.ftp.port' => ['nullable', 'integer', 'between:1,65535'],
            'access.ftp.protocol' => ['nullable', Rule::in(['ftp', 'ftps', 'sftp'])],
        ]);
    }
}
