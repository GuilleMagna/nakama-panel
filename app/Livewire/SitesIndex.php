<?php

namespace App\Livewire;

use App\Jobs\CheckSite;
use App\Models\Client;
use App\Models\Site;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SitesIndex extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $status = '';
    #[Url] public string $client = '';
    #[Url] public string $ownership = '';
    public string $checkMessage = '';

    private const CHECK_LABELS = ['availability' => 'Disponibilidad', 'monitor' => 'Actualizaciones'];

    public function startChecks(string $kind): void
    {
        abort_unless(auth()->check(), 403);
        abort_unless(isset(self::CHECK_LABELS[$kind]), 422);

        $message = Cache::lock('enqueue-site-checks:'.$kind, 30)->get(function () use ($kind) {
            $batch = $this->checkBatch($kind);
            if ($batch && $batch->pendingJobs > $batch->failedJobs && ! $batch->cancelled()) {
                return 'Ya hay una consulta general en curso.';
            }

            $jobs = Site::where('status', 'active')
                ->when($kind === 'monitor', fn ($query) => $query->whereNotNull('monitor_token'))
                ->pluck('id')->map(fn (int $id) => new CheckSite($id, $kind));

            if ($jobs->isEmpty()) {
                return $kind === 'monitor' ? 'No hay sitios activos con token de Nakama Monitor.' : 'No hay sitios activos para verificar.';
            }

            $batch = Bus::batch($jobs)->name(self::CHECK_LABELS[$kind])->allowFailures()
                ->onConnection('database')->onQueue('site-checks')->dispatch();
            Cache::put('site-check-batch:'.$kind, $batch->id, now()->addDay());

            return 'Chequeos encolados para '.$jobs->count().' sitio(s).';
        });

        $this->checkMessage = $message ?: 'Ya se está preparando una consulta general.';
    }

    private function checkBatch(string $kind): ?Batch
    {
        $id = Cache::get('site-check-batch:'.$kind);

        return $id ? Bus::findBatch($id) : null;
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'client', 'ownership'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $sites = Site::query()->with('client')
            ->when(in_array($this->ownership, ['own', 'client']), fn ($q) => $q->where('ownership', $this->ownership))
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.mb_substr($this->search, 0, 150).'%')->orWhere('url', 'like', '%'.mb_substr($this->search, 0, 150).'%')))
            ->when(in_array($this->status, ['active', 'paused', 'archived']), fn ($q) => $q->where('status', $this->status))
            ->when(ctype_digit($this->client), fn ($q) => $q->where('client_id', $this->client))
            ->orderBy('name')->paginate(15);

        $checkBatches = [];
        foreach (self::CHECK_LABELS as $kind => $label) {
            $batch = $this->checkBatch($kind);
            if ($batch) {
                $checkBatches[$kind] = [
                    'label' => $label,
                    'running' => $batch->pendingJobs > $batch->failedJobs && ! $batch->cancelled(),
                    'completed' => $batch->processedJobs() + $batch->failedJobs,
                    'total' => $batch->totalJobs,
                    'failed' => $batch->failedJobs,
                ];
            }
        }

        return view('livewire.sites-index', [
            'sites' => $sites,
            'clients' => Client::orderBy('name')->get(),
            'checkBatches' => $checkBatches,
        ]);
    }
}
