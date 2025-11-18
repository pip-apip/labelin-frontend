<?php

use Livewire\Volt\Component;

new class extends Component {
    public $projects = [];
    public $projectSelected = [];
    public $specTechs = [];
    public $barangItems = [];
    // public $specTechs = [
    //     [
    //         'id' => 1,
    //         'name' => 'Mobil',
    //         'children' => [['name' => 'Ban'], ['name' => 'Lampu']],
    //     ],
    //     [
    //         'id' => 2,
    //         'name' => 'Motor',
    //         'children' => [['name' => 'Knalpot'], ['name' => 'Rantai']],
    //     ],
    // ];
    // =======================================================================
    public $jenisBarangData = [['name' => 'Elektronik', 'quantity_total' => 100], ['name' => 'Furniture', 'quantity_total' => 50], ['name' => 'Alat Tulis Kantor', 'quantity_total' => 200]];

    // Form input
    public $specTechId = '';
    public $jenisName = '';
    public $quantity = '';
    public $quantityTotal = '';
    public $diterimaKe = '';
    public $receivedDate = '';
    public $tracking_number = '';

    // Array hasil Add
    public $barangDiterima = [];

    public function updatedJenisName($value)
    {
        $selected = collect($this->jenisBarangData)->firstWhere('name', $value);

        if ($selected) {
            $this->quantityTotal = $selected['quantity_total'];
        } else {
            $this->quantityTotal = '';
        }
    }

    public function addBarang()
    {
        if (!$this->jenisName) {
            return;
        }

        $this->barangDiterima[] = [
            'barang_id' => $this->specTechId,
            'item_detail_name' => $this->jenisName,
            'quantity_unit' => $this->quantity ?: 0,
            'quantity' => $this->quantityTotal ?: 0,
            'status' => $this->diterimaKe === 'Gudang' ? 'at_vendor' : 'ready',
            'current_location' => $this->diterimaKe,
        ];

        // Reset form
        $this->reset(['specTechId', 'jenisName', 'quantity', 'quantityTotal', 'diterimaKe']);
    }

    public function deleteBarang($index)
    {
        unset($this->barangDiterima[$index]);
        $this->barangDiterima = array_values($this->barangDiterima);
    }

    // =======================================================================

    public function mount()
    {
        $this->getProjects();
        $this->projectSelected = [];
    }

    public function getProjects()
    {
        try {
            $response = null;
            if (session('user.role') == 'SUPERADMIN') {
                $response = Http::withToken(session('token'))->get(env('API_URL_PM') . '/projects/search', [
                    'limit' => 1000,
                ]);
            } else {
                $project_id = '';
                for ($i = 0; $i < count(session('user.project_id')); $i++) {
                    if ($i == 0) {
                        $project_id = session('user.project_id')[$i];
                    } else {
                        $project_id .= ',' . session('user.project_id')[$i];
                    }
                }
                $response = Http::withToken(session('token'))->get(env('API_URL_PM') . '/projects/search', [
                    'id' => $project_id,
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
        $params = ['limit' => 1000];

        if (session('user.project_leader')) {
            $projectIds = session('user.project_id');
            $params['project_id'] = is_array($projectIds) ? implode(',', $projectIds) : $projectIds;
        }

        // Fetch spectech
        try {
            $response = Http::withToken(session('token'))
                ->get(env('API_URL_PM') . '/activity-categories/search', $params)
                ->throw();

            $this->specTechs = $response->json('data') ?? [];
        } catch (\Throwable $e) {
            \Log::error('Error fetching spec tech', ['message' => $e->getMessage()]);
            $this->specTechs = [];
        }

        // Fetch barang items
        try {
            $projectId = $this->projectSelected['id'] ?? null;

            $response = Http::withToken(session('token'))
                ->get(env('API_URL_LN') . '/barang/items', [
                    'project_id' => $projectId,
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

    public function submit()
    {
        $body = [
            'items' => $this->barangDiterima,
            'tracking_number' => $this->tracking_number,
            'received_by' => session('user.name'),
            'received_date' => $this->receivedDate,
            'project_id' => $this->projectSelected['id'] ?? null,
        ];

        try {
            $response = Http::withToken(session('token'))
                ->post(env('API_URL_LN') . '/barang/items', $body)
                ->throw();
            $data = $response->json();
            dd('Items fetched successfully: ', $data);
        } catch (\Exception $e) {
            \Log::error('Data to submit: ' . json_encode($body));
            \Log::error('Error fetching spec tech: ' . $e->getMessage());
            \Log::error($e->response->json());
        }
    }
}; ?>

<div>
    <div class="grid auto-rows-min gap-4 md:grid-cols-3 mb-6">
        @for ($i = 0; $i < 3; $i++)
            <div
                class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern
                    class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        @endfor
    </div>

    <!-- Select Paket -->
    <div
        class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl hover:shadow-2xl transition">
        <div class="flex justify-between items-center">
            <flux:label class="text-lg font-medium text-gray-900 dark:text-gray-300 min-w-[100px]">
                Pilih Paket
            </flux:label>
            <flux:select placeholder="Pilih Paket" class="min-w-[100px]" wire:change="getSpectech($event.target.value)">
                @foreach ($this->projects as $project)
                    <flux:select.option value="{{ $project['id'] }}">{{ $project['name'] }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <!-- Loading State -->
    <div class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl mx-auto" wire:loading
        wire:target="getSpectech">
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
        <div
            class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl hover:shadow-2xl transition">
            <div>
                <flux:heading size="xl" level="1">Paket {{ $this->projectSelected['code'] }}</flux:heading>
                <flux:subheading size="lg">{{ $this->projectSelected['name'] }}</flux:subheading>
            </div>
        </div>
    @endif

    <!-- Paket Table & Controls -->
    @if ($this->projectSelected)
        <div class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl">
            <div class="flex justify-between items-center mb-6 gap-4">
                <flux:input type="text" icon="magnifying-glass" placeholder="Search Paket"
                    wire:model.live.debounce.350ms="searchQuery" class="min-w-[300px]" />

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
                                <td class="py-3 px-2">100</td>
                                <td class="py-3 px-3 text-right">
                                    <flux:button variant="ghost" size="sm" wire:navigate
                                        href="{{ route('paket.show', $item['id']) }}">
                                        Detail
                                    </flux:button>
                                </td>
                            </tr>

                            @if (!empty($item['children']))
                                @foreach ($item['children'] as $child)
                                    <tr class="border-b border-neutral-200 bg-neutral-50">
                                        <td class="py-2 px-4"></td>
                                        <td class="py-2 px-6 flex items-center gap-2 text-neutral-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-4 text-neutral-400"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M2 12h16M18 12l-4-4m4 4l-4 4" />
                                            </svg>
                                            <span>{{ $child['name'] }}</span>
                                        </td>
                                        <td class="py-2 px-2 text-neutral-500">{{ $child['quantity'] }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Add Barang Modal -->
    <flux:modal name="add-barang-modal" class="w-full max-w-4xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Form Barang Diterima</flux:heading>
                <flux:text class="mt-2"> Tambah informasi barang diterima pada paket
                    {{ $this->projectSelected['code'] ?? '' }} </flux:text>
            </div>
            <flux:separator></flux:separator>
            <div class="flex justify-between items-center gap-4">
                <div class="grid grid-cols-4 gap-4 w-full">
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300"> Jenis Barang </flux:label>
                    <flux:select wire:model='specTechId' placeholder="Pilih Barang" class="min-w-[100px]">
                        @foreach ($this->specTechs as $item)
                            <flux:select.option value="{{ $item['id'] }}">{{ $item['name'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:label>Nama Barang</flux:label>
                    {{-- <div class="relative col-span-1"> <input type="text"
                            class="w-full border rounded-lg block disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm py-2 h-10 leading-[1.375rem] ps-3 pe-3 bg-white dark:bg-white/10 dark:disabled:bg-white/[7%] text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5"
                            placeholder="Ketik nama barang..." x-model="search" wire:model.lazy="jenisName"
                            @input="filterList" @keydown.arrow-down.prevent="moveDown"
                            @keydown.arrow-up.prevent="moveUp" @keydown.enter.prevent="selectItem" />
                        <div x-show="filtered.length > 0"
                            class="absolute bg-white border w-full mt-1 z-10 max-h-40 overflow-y-auto rounded shadow text-base sm:text-sm">
                            <template x-for="(item, index) in filtered" :key="item.name">
                                <div class="px-2 py-1 cursor-pointer"
                                    :class="index === highlightedIndex ? 'bg-gray-200' : ''"
                                    @mouseenter="highlightedIndex = index" @click="choose(item)"> <span
                                        x-text="item.name"></span> </div>
                            </template> </div>
                    </div> --}}

                    {{-- Quantity --}}
                    <flux:label>Quantity</flux:label>
                    <flux:input wire:model="quantity" placeholder="Quantity" /> {{-- Diterima Ke --}} <flux:label>
                        Diterima Ke</flux:label>
                    <flux:input wire:model="diterimaKe" placeholder="Lokasi/Tempat" /> {{-- Quantity Total --}}
                    <flux:label>Quantity Total</flux:label>
                    <flux:input wire:model="quantityTotal" placeholder="Total"
                        :disabled="collect($jenisBarangData)->pluck('name')->contains($jenisName)" />
                </div>
                <div class="flex justify-end">
                    <flux:button wire:click="addBarang" variant="primary">Add</flux:button>
                </div>
            </div>
            <table class="w-full text-sm shadow-lg mt-4">
                <thead class="bg-zinc-200 dark:bg-zinc-900 border-b border-zinc-800">
                    <tr class="text-left border-b border-zinc-800 uppercase">
                        <th class="py-4 px-4">No</th>
                        <th class="py-4 px-2">Name</th>
                        <th class="py-4 px-2">Quantity</th>
                        <th class="py-4 px-2">Quantity Total</th>
                        <th class="py-4 px-2">Diterima Ke</th>
                        <th class="py-4 px-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangDiterima as $index => $item)
                        <tr
                            class="border-b border-zinc-800 dark:border-zinc-900 hover:bg-zinc-200 dark:hover:bg-zinc-900/50">
                            <td class="py-4 px-4">{{ $index + 1 }}</td>
                            <td class="py-4 px-2">{{ $item['item_detail_name'] }}</td>
                            <td class="py-4 px-2">{{ $item['quantity_unit'] }}</td>
                            <td class="py-4 px-2">{{ $item['quantity'] }}</td>
                            <td class="py-4 px-2">{{ $item['current_location'] }}</td>
                            <td class="py-4 px-2 justify-end flex gap-2">
                                <flux:button variant="ghost" size="sm"
                                    wire:click="deleteBarang({{ $index }})">
                                    <flux:icon name="x-circle" />
                                </flux:button>
                            </td>
                    </tr> @empty <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="flex justify-between items-center w-full gap-4"> {{-- Tanggal Diterima --}} <flux:label>Tanggal
                    Diterima</flux:label>
                <flux:input type="date" wire:model="receivedDate" class="min-w-[150px]" /> {{-- Nomor Resi --}}
                <flux:label>Nomor Resi</flux:label>
                <flux:input wire:model="tracking_number" placeholder="Nomor Resi" />
            </div>
            <flux:separator></flux:separator>
            <div class="flex justify-end gap-4">
                <flux:button variant="primary" wire:click="submit">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Alpine.js -->
    <script>
        function barangSelect(items) {
            return {
                all: items,
                search: '',
                filtered: [],
                highlightedIndex: -1,
                init() {
                    this.filtered = [];
                },
                filterList() {
                    const keyword = this.search.toLowerCase().trim();
                    this.filtered = keyword ? this.all.filter(i => i.name.toLowerCase().includes(keyword)) : [];
                    this.highlightedIndex = this.filtered.length ? 0 : -1;
                },
                moveDown() {
                    this.highlightedIndex = (this.highlightedIndex + 1) % this.filtered.length;
                },
                moveUp() {
                    this.highlightedIndex = (this.highlightedIndex - 1 + this.filtered.length) % this.filtered.length;
                },
                selectItem() {
                    if (this.filtered.length && this.highlightedIndex >= 0) this.choose(this.filtered[this
                        .highlightedIndex]);
                    else {
                        this.$wire.set('jenisName', this.search);
                        this.filtered = [];
                        this.highlightedIndex = -1;
                    }
                },
                choose(item) {
                    this.search = item.name;
                    this.$wire.set('jenisName', item.name);
                    this.filtered = [];
                    this.highlightedIndex = -1;
                },
            }
        }
    </script>

</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2({
                tags: true,
            });
        });
    </script>
@endpush
