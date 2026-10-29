<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr((string) $request->query('q', ''), 0, 150);
        $clients = Client::query()->withCount('sites')->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))->orderBy('name')->paginate(15)->withQueryString();

        return view('clients.index', compact('clients', 'search'));
    }

    public function create()
    {
        return view('clients.form', ['client' => new Client]);
    }

    public function edit(Client $client)
    {
        return view('clients.form', compact('client'));
    }

    public function store(Request $request)
    {
        Client::create($this->validated($request));

        return redirect()->route('clients.index')->with('success', 'Cliente registrado.');
    }

    public function update(Request $request, Client $client)
    {
        $client->update($this->validated($request));

        return redirect()->route('clients.index')->with('success', 'Cliente actualizado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:60'], 'notes' => ['nullable', 'string', 'max:10000']]);
    }
}
