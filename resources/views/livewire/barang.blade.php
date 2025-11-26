<?php

use Flux\Flux;
use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;

new class extends Component {
    public $projects = [];
    public $projectSelected = [];
    public $specTechs = [];
    public $barangItems = [];
    public $itemToAdd = [];

    // Form input
    public $specTechId = '';
    public $jenisName = '';
    public $quantityUnit = '';
    public $quantity = null;
    public $diterimaKe = 'gudang';
    public $receivedDate = '';
    public $tracking_number = '';
    public $showConfirmModal = false;
    public $lokasi = '';

    // Array hasil Add
    public $barangDiterima = [];

    public function mount()
    {
        $this->projectSelected = [];
        $this->receivedDate = now()->format('Y-m-d');
        $this->getProjects();

    }

    public function rules()
    {
        return [
            'jenisName' => 'required',
            'specTechId' => 'required',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(){
        return [
            'jenisName.required' => 'Nama barang harus diisi.',
            'specTechId.required' => 'Jenis barang harus dipilih.',
            'quantity.required' => 'Jumlah total barang harus diisi.',
            'quantity.integer' => 'Jumlah total barang harus berupa angka.',
            'quantityUnit.required' => 'Jumlah diterima harus diisi.',
            'quantityUnit.max' => 'Jumlah diterima tidak boleh lebih dari quantity total.',
        ];
    }

    public function addItem(){
        $this->validate();

        $this->itemToAdd[] = [
            'barang_id' => $this->specTechId,
            'item_detail_name' => $this->jenisName,
            'description' => null,
            'quantity' => $this->quantity,
        ];


        $this->reset(['specTechId', 'jenisName', 'quantity']);
    }

    public function deleteItem($index)
    {
        unset($this->itemToAdd[$index]);
        $this->itemToAdd = array_values($this->itemToAdd);
    }

    public function getProjects()
    {
        $this->projects = [];
        try {
            $response = null;
            if (session('user.role') == 'SUPERADMIN') {
                $response = Http::withToken(session('token'))->get(env('API_URL_PM') . '/projects/search', [
                    'limit' => 1000,
                ]);
            } else {
                $response = Http::withToken(session('token'))->get(env('API_URL_PM') . '/projects/search', [
                    'project_leader_id' => session('user.id'),
                ]);
            }

            if ($response->json('status') === 200) {
                $this->projects = $response->json('data');
            }

        } catch (\Exception $e) {
            \Log::error('Error fetching projects: ' . $e->getMessage());
        }
    }

    public function getSpectech($id)
    {
        $this->projectSelected = collect($this->projects)->firstWhere('id', $id);

        // Fetch spectech
        try {
            $response = Http::withToken(session('token'))
                ->get(env('API_URL_PM') . '/activity-categories/search?project_id=' . $id . '&limit=100', )
                ->throw();

            $this->specTechs = $response->json('data') ?? [];
        } catch (\Throwable $e) {
            \Log::error('Error fetching spec tech', ['message' => $e->getMessage()]);
            $this->specTechs = [];
        }

        // Fetch barang items
        try {
            $response = Http::withToken(session('token'))
                ->get(env('API_URL_LN') . '/barang/items', [
                    'project_id' => $id,
                ])
                ->throw();

            $this->barangItems = $response->json('data') ?? [];
        } catch (\Throwable $e) {
            \Log::error('Error fetching barang items', ['message' => $e->getMessage()]);
            $this->barangItems = [];
        }

        $barangMap = [];

        foreach ($this->barangItems as $barang) {
            $barangMap[$barang['barang_id']][] = $barang;
        }

        /**
         * Map specTechs without foreach
         */
        $this->specTechs = array_map(function ($item) use ($barangMap) {
            $id = $item['id'];
            $item['children'] = $barangMap[$id] ?? [];
            return $item;
        }, $this->specTechs);
    }

    public function saveItems()
    {

        if($this->itemToAdd == []){
            Toaster::error('Tidak ada item untuk disimpan.');
            return;
        }
        try {
            $response = Http::withToken(session('token'))
                ->post(env('API_URL_LN') . '/barang/items/bulk-store', [
                    'items' => $this->itemToAdd,
                    'project_id' => $this->projectSelected['id'] ?? null,
                ])
                ->throw();
            $data = $response->json();

            Toaster::success($data['message'] ?? 'Items berhasil ditambahkan.');
            $this->getSpectech($this->projectSelected['id'] ?? null);
            $this->reset(['itemToAdd']);

        } catch (\Exception $e) {
            $json = $e->response->json();
            Toaster::error($json['message'] ?? 'Gagal menambahkan items.');
            \Log::error('Error fetching spec tech: ' . $e->getMessage());
        }
    }

    public function destroyItem($id)
    {
        try {
            $response = Http::withToken(session('token'))
                ->delete(env('API_URL_LN') . '/barang/items/' . $id)
                ->throw();

            $data = $response->json();

            Toaster::success($data['message'] ?? 'Item berhasil dihapus.');
            $this->getSpectech($this->projectSelected['id'] ?? null);
        } catch (\Exception $e) {
            \Log::error('Error deleting item: ' . $e->response->json('message') ?? 'Unknown error');
        }
    }
}; ?>

<div>
    <div>
        <div class="grid auto-rows-min gap-4 md:grid-cols-3 mb-6">
            @for ($i = 0; $i < 3; $i++) <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
        @endfor
    </div>

    <!-- Select Paket -->
    <div class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl hover:shadow-2xl transition">
        <div class="flex justify-between items-center" wire:ignore>
            <flux:label class="text-lg font-medium text-gray-900 dark:text-gray-300 min-w-[100px]">
                Pilih Paket
            </flux:label>
            <select id="select2" placeholder="Pilih Paket" class="w-full form-control" wire:change="getSpectech($event.target.value)">
                <option value="">-- Select Project --</option>
                @foreach ($this->projects as $project)

                <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Loading State -->
    <div class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl mx-auto" wire:loading wire:target="getSpectech">
        <div class="flex animate-pulse space-x-4">
            <div class="flex-1 space-y-6 py-1">
                <div class="h-2 rounded w-sm py-2 bg-gray-200"></div>
                <div class="space-y-3">
                    <div class="h-2 rounded py-1.5 w-1/2 bg-gray-200"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl mx-auto hidden">
        <div class="flex justify-between item-center animate-pulse space-x-4 mb-6">
            <div class="flex gap-4 col-span-2">
                <div class="flex justify-between gap-4">
                    <div class="h-2 rounded w-76 py-2 bg-gray-200"></div>
                </div>
            </div>
            <div class="flex justify-between gap-4">
                <div class="h-2 rounded w-36 py-2 bg-gray-200"></div>
                <div class="h-2 rounded w-25 py-2 bg-gray-200"></div>
                <div class="h-2 rounded w-15 py-2 bg-gray-200"></div>
            </div>
        </div>

        <div class="w-full p-4 border border-default divide-y divide-default rounded-lg shadow-xs md:p-4">
            <div class="flex items-center justify-start gap-4">
                <div class="h-2 rounded w-12 py-2 bg-gray-200 md:me-4"></div>
                <div class="h-2 rounded w-12 py-2 bg-gray-200"></div>
                <div class="h-2 rounded w-12 py-2 bg-gray-200"></div>
                <div class="h-2 rounded w-12 py-2 bg-gray-200 "></div>
            </div>
        </div>
    </div>

    <!-- Selected Paket Info -->
    @if ($this->projectSelected)
    <div wire:loading.remove wire:target="getSpectech" class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl hover:shadow-2xl transition">
        <div>
            <flux:heading size="xl" level="1">{{ $this->projectSelected['name'] }} ({{ $this->projectSelected['code'] }})</flux:heading>
            <flux:subheading size="lg">{{ $this->projectSelected['company_name'] }}</flux:subheading>
        </div>
    </div>
    @endif

    <!-- Paket Table & Controls -->
    @if ($this->projectSelected)
    <div class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl" wire:loading.remove wire:target="getSpectech">
        <div class="flex justify-between items-center mb-6 gap-4">
            <flux:input type="text" icon="magnifying-glass" placeholder="Search Paket" wire:model.live.debounce.350ms="searchQuery" class="min-w-[300px]" />

            <div class="flex gap-4">
                <flux:select wire:model.live="filterSort" placeholder="Sort By" class="min-w-[150px]">
                    <flux:select.option value="name">Name</flux:select.option>
                </flux:select>
                <flux:select wire:model.live="filterOrder" placeholder="Order" class="min-w-[100px]">
                    <flux:select.option value="asc">ASC</flux:select.option>
                    <flux:select.option value="desc">DESC</flux:select.option>
                </flux:select>
                <flux:modal.trigger name="add-barang-modal">
                    <flux:button variant="filled">Add</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <!-- Paket Table -->
        <div class="overflow-hidden rounded-lg border border-neutral-300">
            <table class="w-full text-xs shadow-lg">
                <thead class="bg-neutral-200">
                    <tr class="text-left uppercase">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-2 min-w-[500px]">Name</th>
                        <th class="py-3 px-2">Quantity</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->specTechs as $item)
                    <tr class="hover:bg-neutral-100 border-b border-neutral-300 font-semibold">
                        <td class="py-3 px-4">{{ $loop->iteration }}</td>
                        <td class="py-3 px-2">{{ $item['name'] }}</td>
                        <td class="py-3 px-2">{{ count($item['children']) }} Items</td>
                        <td class="py-3 px-3 text-right">
                            <flux:button variant="ghost" size="sm" wire:navigate href="{{ route('paket.show', ['id' => $item['id'], 'nama' => $item['name']]) }}">
                                Detail
                            </flux:button>
                        </td>
                    </tr>

                    @if (!empty($item['children']))
                    @foreach ($item['children'] as $child)
                    <tr class="border-b border-neutral-200 bg-neutral-50">
                        <td class="py-2 px-4"></td>
                        <td class="py-2 px-6 flex items-center gap-2 text-neutral-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-4 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2 12h16M18 12l-4-4m4 4l-4 4" />
                            </svg>
                            <span>{{ $child['name'] }}</span>
                        </td>
                        <td class="py-2 px-2 text-neutral-500">{{ $child['quantity'] }}</td>
                        <td class="text-right px-3 py-3">
                            <flux:button icon="x-mark" size="sm" variant="ghost" wire:click="destroyItem({{ $child['id'] }})"></flux:button>
                            <flux:button icon="inbox-arrow-down" size="sm" variant="ghost"></flux:button>
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <flux:modal name="add-barang-modal" class="w-full max-w-4xl" wire:model="showConfirmModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tambah Item Barang</flux:heading>
                <flux:text class="mt-2"> Item barang spesifikasi teknis pada paket
                    {{ $this->projectSelected['code'] ?? '' }} </flux:text>
            </div>
            <flux:separator></flux:separator>
            <div class="grid grid-cols-1 justify-between items-center gap-6">
                {{-- Jenis Barang --}}
                <flux:field class="w-full">
                    <flux:select placeholder="Pilih Jenis Barang" wire:model="specTechId" class="min-w-[100px]">
                        @foreach ($this->specTechs as $item)
                        <flux:select.option value="{{ $item['id'] }}">{{ $item['name'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="specTechId" class="text-xs" />
                    <p class="text-xs text-gray-500"></p>
                </flux:field>
                <div class="flex items-center w-full gap-5">
                    <div class="flex w-full justify-between items-start gap-4">
                        <flux:field class="flex w-full">
                            <flux:label>Nama Item</flux:label>
                            <flux:input wire:model='jenisName' placeholder="Input nama barang" class="w-full" />
                            <flux:error name="jenisName" class="text-xs" />
                        </flux:field>
                        <div class="flex flex-col gap-2 w-full">
                            <flux:field>
                                <flux:label>Quantity item</flux:label>
                                <flux:input wire:model.live="quantity" placeholder="0" type="number" />
                                <flux:error name="quantity" class="text-xs" />
                            </flux:field>
                            <p class="text-xs text-gray-500">Jumlah total items.</p>
                        </div>
                    </div>
                    <flux:button wire:click="addItem" variant="primary" icon="plus"></flux:button>
                </div>
                <div class="relative overflow-x-auto bg-neutral-50 shadow-xs rounded-base border border-default">
                    <table class="w-full text-sm text-left rtl:text-right text-body">
                        <thead class="text-sm text-body bg-neutral-150 border-b rounded-base border-default">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    No
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 font-medium">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($itemToAdd as $index => $item)
                            <tr class="bg-neutral-primary border-b border-default">
                                <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                    {{ $loop->iteration }}
                                </th>
                                <td class="px-6 py-4">
                                    {{ $item['item_detail_name'] }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item['quantity'] }}
                                </td>
                                <td class="px-6 py-4">
                                    <flux:button variant="ghost" size="sm" wire:click="deleteItem({{ $index }})">
                                        <flux:icon name="x-circle" />
                                    </flux:button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center">
                                    No items added.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <flux:button x-on:click="$flux.modal().close()">
                    Batal
                </flux:button>
                <flux:button variant="primary" wire:click="saveItems" :disabled="$this->itemToAdd == []">
                    Simpan Items
                </flux:button>
            </div>
    </flux:modal>
</div>

<!-- Alpine.js -->
<script>
    function barangSelect(items) {
        return {
            all: items
            , search: ''
            , filtered: []
            , highlightedIndex: -1
            , init() {
                this.filtered = [];
            }
            , filterList() {
                const keyword = this.search.toLowerCase().trim();
                this.filtered = keyword ? this.all.filter(i => i.name.toLowerCase().includes(keyword)) : [];
                this.highlightedIndex = this.filtered.length ? 0 : -1;
            }
            , moveDown() {
                this.highlightedIndex = (this.highlightedIndex + 1) % this.filtered.length;
            }
            , moveUp() {
                this.highlightedIndex = (this.highlightedIndex - 1 + this.filtered.length) % this.filtered.length;
            }
            , selectItem() {
                if (this.filtered.length && this.highlightedIndex >= 0) this.choose(this.filtered[this
                    .highlightedIndex]);
                else {
                    this.$wire.set('jenisName', this.search);
                    this.filtered = [];
                    this.highlightedIndex = -1;
                }
            }
            , choose(item) {
                this.search = item.name;
                this.$wire.set('jenisName', item.name);
                this.filtered = [];
                this.highlightedIndex = -1;
            }
        , }
    }

</script>

<style>
    /* Container */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #d1d5db !important;
        /* gray-300 */
        border-radius: 0.5rem !important;
        /* rounded-lg */
        padding: .5rem .75rem !important;
        display: flex !important;
        align-items: center !important;
        background-color: #fff !important;
    }

    /* Arrow */
    .select2-selection__arrow b {
        border-color: #9ca3af transparent transparent transparent !important;
        /* gray-400 */
    }

    /* Option dropdown modern */
    .select2-dropdown {
        border-radius: 0.75rem !important;
        /* rounded-xl */
        border: 1px solid #e5e7eb !important;
        overflow: hidden;
    }

    /* Search input dalam dropdown */
    .select2-search__field {
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        padding: .45rem .75rem !important;
    }

    /* Hover item */
    .select2-results__option--highlighted {
        background-color: #f3f4f6 !important;
        /* gray-100 */
        color: #111827 !important;
        /* gray-900 */
    }

</style>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        function initSelect2() {
            $('#select2').select2({
                width: '100%'
            });

            // when changed -> notify Livewire
            $('#select2').on('change', function(e) {
                @this.call('getSpectech', $(this).val());
            });
        }

        initSelect2();

        Livewire.hook('message.processed', () => {
            initSelect2();
        });

    });

</script>

@endpush
