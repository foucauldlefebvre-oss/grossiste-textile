<?php

namespace App\Livewire;

use App\Models\Quote;
use App\Services\BatService;
use Livewire\Component;

class BatApproval extends Component
{
    public Quote $quote;
    public string $rejectionReason = '';
    public bool $showRejectForm = false;
    public bool $responded = false;

    public function mount(string $token): void
    {
        $this->quote = Quote::where('bat_token', $token)->firstOrFail();
        $this->quote->load([
            'user',
            'items.product',
            'items.color',
            'items.size',
            'items.technique',
        ]);

        if (in_array($this->quote->bat_status, ['approved', 'rejected'])) {
            $this->responded = true;
        }
    }

    public function approve(): void
    {
        app(BatService::class)->approve($this->quote);
        $this->responded = true;
        $this->quote->refresh();
    }

    public function showReject(): void
    {
        $this->showRejectForm = true;
    }

    public function cancelReject(): void
    {
        $this->showRejectForm = false;
        $this->rejectionReason = '';
    }

    public function reject(): void
    {
        app(BatService::class)->reject($this->quote, $this->rejectionReason ?: null);
        $this->responded = true;
        $this->quote->refresh();
    }

    public function downloadPdf()
    {
        return app(BatService::class)->download($this->quote);
    }

    public function render()
    {
        return view('livewire.bat-approval');
    }
}
