<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Site;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SitesIndex extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $status = '';
    #[Url] public string $client = '';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'client'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $sites = Site::query()->with('client')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.mb_substr($this->search, 0, 150).'%')->orWhere('url', 'like', '%'.mb_substr($this->search, 0, 150).'%')))
            ->when(in_array($this->status, ['active', 'paused', 'archived']), fn ($q) => $q->where('status', $this->status))
            ->when(ctype_digit($this->client), fn ($q) => $q->where('client_id', $this->client))
            ->orderBy('name')->paginate(15);

        return view('livewire.sites-index', ['sites' => $sites, 'clients' => Client::orderBy('name')->get()]);
    }
}
