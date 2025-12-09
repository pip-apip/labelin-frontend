<?php

use Livewire\Volt\Component;


new class extends Component {

    public $moveLocation;
    public $moveDate;
    public $moveNote;

    public $items = [];

    public function mount($items)
    {
        $this->items = $items;
    }

    public function submit()
    {

        $selectedData = [];

        foreach ($this->items as $item) {
            $selectedData[] = [
                'barang_item_detail_id' => $item['id'],
                'from_location' => $item['current_location'],
            ];
        }

        $data = [
            'items' => $selectedData,
            'to_location' => $this->moveLocation,
            'sent_at' => $this->moveDate,
            'sent_by' => session('user.name'),
            'note' => $this->moveNote,
        ];

        $this->dispatch('submitedFormDetail', $data);
    }
}; ?>


<div>
    <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
        <div>
            <flux:label>Lokasi Tujuan</flux:label>
            <input type="text" class="border rounded-lg w-full p-2" placeholder="Lokasi tujuan barang"
                wire:model="moveLocation" />
        </div>
        <div>
            <flux:label>Tanggal Pindah</flux:label>
            <input type="date" class="border rounded-lg w-full p-2" wire:model="moveDate" />
        </div>
        <div class="md:col-span-3">
            <flux:label>Keterangan</flux:label>
            <textarea class="border rounded-lg w-full p-2" placeholder="Keterangan perpindahan..." wire:model="moveNote"></textarea>
        </div>
    </div>

    <flux:button class="mt-4" wire:click="submit">
        Submit
    </flux:button>
</div>
