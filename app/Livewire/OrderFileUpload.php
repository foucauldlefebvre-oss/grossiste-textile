<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderFileUpload extends Component
{
    use WithFileUploads;

    public Order $order;
    public array $existingFiles = [];
    public $newFiles = [];
    public $currentFile;
    public ?int $currentLogoIndex = null;

    public function mount(Order $order): void
    {
        $this->order = $order;
        $this->loadExistingFiles();
    }

    private function loadExistingFiles(): void
    {
        $dir = 'orders/' . $this->order->reference;
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        $this->existingFiles = [];
        if ($disk->exists($dir)) {
            foreach ($disk->allFiles($dir) as $file) {
                $this->existingFiles[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $disk->size($file),
                    'url' => $disk->url($file),
                ];
            }
        }
    }

    public function updatedCurrentFile(): void
    {
        if (! $this->currentFile || $this->currentLogoIndex === null) {
            return;
        }

        $this->validate([
            'currentFile' => 'file|max:20480|mimetypes:image/png,image/jpeg,image/svg+xml,image/webp,application/pdf,application/postscript,image/vnd.adobe.photoshop,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/octet-stream',
        ]);

        $logoIndex = $this->currentLogoIndex;

        // Collecter tous les logos de tous les blocs
        $markings = $this->order->quote?->markings ?? [];
        $allLogos = [];
        $blockMap = [];
        $blockIdx = 0;
        foreach ($markings as $blockLogos) {
            if (! is_array($blockLogos) || empty($blockLogos)) { $blockIdx++; continue; }
            if (isset($blockLogos['size'])) $blockLogos = [$blockLogos];
            foreach ($blockLogos as $bl) {
                if (empty($bl) || ! is_array($bl)) continue;
                $blockMap[count($allLogos)] = $blockIdx;
                $allLogos[] = $bl;
            }
            $blockIdx++;
        }

        $logo = $allLogos[$logoIndex] ?? null;
        $block = $blockMap[$logoIndex] ?? 0;
        $isName = ($logo['type'] ?? 'logo') === 'name';

        // Compter le numéro du logo dans son bloc
        $logoNumInBlock = 0;
        for ($i = 0; $i <= $logoIndex; $i++) {
            if (($blockMap[$i] ?? 0) === $block) $logoNumInBlock++;
        }

        $blockPrefix = count($allLogos) > 1 ? 'bloc-' . ($block + 1) . '/' : '';
        $subDir = $isName ? 'prenoms' : 'logo-' . $logoNumInBlock;
        $dir = 'orders/' . $this->order->reference . '/' . $blockPrefix . $subDir;

        $fileName = $this->currentFile->getClientOriginalName();
        $path = $this->currentFile->storeAs($dir, $fileName, 'public');

        // Créer un OrderDocument pour le back-office
        \App\Models\OrderDocument::create([
            'order_id' => $this->order->id,
            'type' => $isName ? 'client_file' : 'client_file',
            'label' => $isName ? 'Prenoms' : 'Logo ' . ($logoIndex + 1),
            'path' => $path,
            'original_name' => $fileName,
            'mime_type' => $this->currentFile->getMimeType(),
            'size' => $this->currentFile->getSize(),
            'visible_to_client' => true,
            'uploaded_by' => 'client',
            'user_id' => auth()->id(),
        ]);

        $this->currentFile = null;
        $this->currentLogoIndex = null;
        $this->loadExistingFiles();

        session()->flash('upload-success', 'Fichier charge pour ' . ($isName ? 'les prenoms' : 'le logo ' . ($logoIndex + 1)) . '.');
    }

    public function deleteLogoFile(string $path): void
    {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        $this->loadExistingFiles();
    }

    public function uploadFiles(): void
    {
        $this->validate([
            'newFiles.*' => 'file|max:20480',
        ]);

        $dir = 'orders/' . $this->order->reference;

        foreach ($this->newFiles as $file) {
            $file->storeAs($dir, $file->getClientOriginalName(), 'public');
        }

        $this->newFiles = [];
        $this->loadExistingFiles();

        session()->flash('upload-success', count($this->existingFiles) . ' fichier(s) charge(s).');
    }

    public function deleteFile(string $path): void
    {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        $this->loadExistingFiles();
    }

    public function render()
    {
        return view('livewire.order-file-upload');
    }
}
