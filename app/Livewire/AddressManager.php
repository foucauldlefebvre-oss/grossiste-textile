<?php

namespace App\Livewire;

use App\Models\Address;
use Livewire\Component;

class AddressManager extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $label = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $company = '';
    public string $address_line_1 = '';
    public string $address_line_2 = '';
    public string $postal_code = '';
    public string $city = '';
    public string $country = 'France';
    public string $phone = '';
    public bool $is_default = false;

    protected function rules(): array
    {
        return [
            'label' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'is_default' => 'boolean',
        ];
    }

    public function openForm(?int $id = null): void
    {
        if ($id) {
            $address = auth()->user()->addresses()->findOrFail($id);
            $this->editingId = $id;
            $this->fill($address->only([
                'label', 'first_name', 'last_name', 'company',
                'address_line_1', 'address_line_2', 'postal_code',
                'city', 'country', 'phone', 'is_default',
            ]));
        } else {
            $this->editingId = null;
            $this->reset([
                'label', 'first_name', 'last_name', 'company',
                'address_line_1', 'address_line_2', 'postal_code',
                'city', 'country', 'phone', 'is_default',
            ]);
            $this->country = 'France';
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'label' => $this->label,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'is_default' => $this->is_default,
        ];

        if ($this->is_default) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        if ($this->editingId) {
            auth()->user()->addresses()->findOrFail($this->editingId)->update($data);
        } else {
            auth()->user()->addresses()->create($data);
        }

        $this->showForm = false;
        $this->reset([
            'editingId', 'label', 'first_name', 'last_name', 'company',
            'address_line_1', 'address_line_2', 'postal_code',
            'city', 'country', 'phone', 'is_default',
        ]);
    }

    public function delete(int $id): void
    {
        auth()->user()->addresses()->findOrFail($id)->delete();
    }

    public function cancel(): void
    {
        $this->showForm = false;
    }

    public function render()
    {
        $addresses = auth()->user()->addresses()->latest()->get();

        return view('livewire.address-manager', compact('addresses'));
    }
}
