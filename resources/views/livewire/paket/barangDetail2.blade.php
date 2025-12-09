<?php

use Livewire\Volt\Component;

new class extends Component {
    public $id;
    public $projectData = [];
    public $specTechData = [];
    public $dataItems = [];
    public $selectedItems = [];
    public int $current_page = 1;
    public int $total_pages = 50;
    public $actionMovement = '';
    protected $listeners = [
        'submitedFormDetail' => 'getDataFormDetail',
    ];

    public function mount()
    {
        $this->getItemDetails($this->id);
        $this->getData($this->id);
    }

    public function getDataFormDetail($data)
    {
        dd($data);
    }

    public function toggleSelect($item)
    {
        $id = $item['id'];

        // Remove if already selected
        if (collect($this->selectedItems)->contains('id', $id)) {
            $this->selectedItems = collect($this->selectedItems)->reject(fn($i) => $i['id'] == $id)->values()->toArray();
            return;
        }

        // Add full item
        $this->selectedItems[] = $item;
    }

    public function getData($id)
    {
        try {
            $response = Http::withToken(session('token'))
                ->get(env('API_URL_PM') . '/activity-categories/' . $id)
                ->throw();
            $this->projectData = $this->getProjectData($response->json()['data'][0]['project_id']);
            $this->specTechData = $this->getSpecTech($response->json()['data'][0]['project_id']);
        } catch (\Exception $e) {
            if (method_exists($e, 'response') && $e->response) {
                \Log::info('Response: ', $e->response->json());
            }
            \Log::error('Error fetching project data: ' . $e->getMessage());
        }
    }

    public function getProjectData($id)
    {
        try {
            $response = Http::withToken(session('token'))
                ->get(env('API_URL_PM') . '/projects/' . $id)
                ->throw();
            return $response->json()['data'][0];
        } catch (\Exception $e) {
            \Log::error('Error fetching project data: ' . $e->getMessage());
        }
    }

    public function getSpecTech($id)
    {
        try {
            $response = Http::withToken(session('token'))
                ->get(env('API_URL_PM') . '/activity-categories/search?project_id=' . $id . '&limit=100')
                ->throw();
            return $response->json()['data'];
        } catch (\Exception $e) {
            \Log::error('Error fetching spec tech data: ' . $e->getMessage());
        }
    }

    public function getItemDetails($id)
    {
        try {
            $response = Http::withToken(session('token'))
                ->get(env('API_URL_LN') . '/barang/details', [
                    'barang_id' => $id,
                ])
                ->throw();
            $this->dataItems = $response->json()['data'];
        } catch (\Exception $e) {
            \Log::info('Response: ', $e->response->json());
            \Log::error('Error fetching item details: ' . $e->getMessage());
        }
    }

    public function handleClick($id)
    {
        $this->id = $id;
        $this->getItemDetails($id);
    }

    // Pagination
    public function getStartProperty()
    {
        return max(1, $this->current_page - 1);
    }

    public function getEndProperty()
    {
        return min($this->total_pages, $this->current_page + 1);
    }

    public function goToPage($page)
    {
        if ($page < 1 || $page > $this->total_pages) {
            return;
        }

        $this->current_page = $page;
    }

    public function nextPage()
    {
        if ($this->current_page < $this->total_pages) {
            $this->current_page++;
        }
    }

    public function prevPage()
    {
        if ($this->current_page > 1) {
            $this->current_page--;
        }
    }

    // Add Modal Function
};
?>

<div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-y-6 lg:gap-x-6">
        <div class="col-span-2 space-y-6">
            <div class="grid auto-rows-min gap-4 grid-cols-2 md:grid-cols-4">
                <div class="flex flex-col aspect-video overflow-hidden rounded-xl border bg-slate-100 shadow-xl p-4">
                    <div
                        class="bg-slate-600 w-9 h-10 flex items-center justify-center rounded-full text-slate-100 me-4 text-lg font-semibold">
                        <flux:icon name="home" class="size-5 " />
                    </div>
                    <p class="text-sm text-slate-600 font-light mt-1">Barang didalam Gudang</p>
                    <p class="text-2xl text-slate-600 font-extrabold mt-1">100</p>
                </div>
                <div class="flex flex-col aspect-video overflow-hidden rounded-xl border bg-slate-100 shadow-xl p-4">
                    <div
                        class="bg-slate-600 w-9 h-10 flex items-center justify-center rounded-full text-slate-100 me-4 text-lg font-semibold">
                        <flux:icon name="home" class="size-5 " />
                    </div>
                    <p class="text-sm text-slate-600 font-light mt-1">Barang diluar Gudang</p>
                    <p class="text-2xl text-slate-600 font-extrabold mt-1">100</p>
                </div>
                <div class="flex flex-col aspect-video overflow-hidden rounded-xl border bg-slate-100 shadow-xl p-4">
                    <div
                        class="bg-slate-600 w-9 h-10 flex items-center justify-center rounded-full text-slate-100 me-4 text-lg font-semibold">
                        <flux:icon name="home" class="size-5 " />
                    </div>
                    <p class="text-sm text-slate-600 font-light mt-1">Barang yang belum diterima</p>
                    <p class="text-2xl text-slate-600 font-extrabold mt-1">100</p>
                </div>
                <div class="flex flex-col aspect-video overflow-hidden rounded-xl border bg-slate-100 shadow-xl p-4">
                    <div
                        class="bg-slate-600 w-9 h-10 flex items-center justify-center rounded-full text-slate-100 me-4 text-lg font-semibold">
                        <flux:icon name="home" class="size-5 " />
                    </div>
                    <p class="text-sm text-slate-600 font-light mt-1">Barang di Gudang</p>
                    <p class="text-2xl text-slate-600 font-extrabold mt-1">100</p>
                </div>
            </div>
            <div
                class="justify-start items-center bg-slate-600 rounded-2xl lg:rounded-s-2xl py-0 px-4  text-slate-100 w-full h-20 lg:hidden flex">
                <div
                    class="bg-neutral-100 w-18 h-15 flex items-center justify-center rounded-full text-neutral-600 me-4 text-lg font-semibold">
                    {{ $this->projectData['code'] }}
                </div>
                <marquee class="text-3xl font-medium py-2 nowrap">"{{ $this->projectData['name'] }}"</marquee>
            </div>
            <div class="flex justify-start items-center">
                <div class="bg-slate-600 p-4 rounded-[100%] text-slate-100">
                    <flux:icon name="cube" />
                </div>
                <div class="overflow-x-auto ms-2 hide-scrollbar w-full">
                    <ul class="flex justify-start">
                        {{-- @for ($i = 0; $i < 10; $i++)
                            <li class="flex-shrink-0">
                                <a href=""
                                    class="block text-decoration-none text-nowrap border-b-[3px] border-transparent hover:border-slate-600 px-4 py-1 text-slate-500/50 hover:text-slate-600 font-medium">
                                    Barang Detail Item {{ $i + 1 }}</a>
                            </li>
                        @endfor --}}
                        @foreach ($this->specTechData as $specTech)
                            <li class="flex-shrink-0">
                                <a wire:click="handleClick({{ $specTech['id'] }})"
                                    class="relative block text-decoration-none text-nowrap px-4 py-1 font-medium {{ $specTech['id'] == $this->id ? 'text-slate-600' : 'text-slate-500/50 hover:text-slate-600' }} cursor-pointer">

                                    {{ $specTech['name'] }}

                                    @if ($specTech['id'] == $this->id)
                                        <span
                                            class="absolute bottom-0 left-1/4 w-1/2 h-[3px] bg-slate-600 rounded"></span>
                                    @endif
                                </a>

                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div
                class="relative w-full overflow-hidden rounded-xl border bg-slate-100 shadow-xl
                {{-- border-slate-600 --}}
                ">
                <div class="flex flex-col lg:flex-row lg:justify-between gap-4 p-4">
                    <div class="flex gap-4 col-span-2">
                        <div class="flex justify-between gap-4">
                            <flux:input type="search" icon="magnifying-glass" placeholder="Search"
                                class="w-full lg:min-w-[300px]" />
                        </div>
                    </div>
                    <div class="flex justify-between gap-4">
                        <flux:select wire:model.live="filterSort" placeholder="Sort By" class="w-full lg:min-w-[150px]">
                            <flux:select.option value="name">Name</flux:select.option>
                        </flux:select>

                        <flux:select wire:model.live="filterOrder" placeholder="Order"
                            class="w-full lg:min-w-[100px] bg-slate-100">
                            <flux:select.option value="asc">ASC</flux:select.option>
                            <flux:select.option value="desc">DESC</flux:select.option>
                        </flux:select>

                        <flux:modal.trigger name="add-modal">
                            <flux:button>Add</flux:button>
                        </flux:modal.trigger>

                        <flux:button>Export</flux:button>
                    </div>
                </div>
                <div class="max-h-[395px] overflow-y-auto">
                    <table class="text-xs w-full">
                        <thead class="bg-slate-600 sticky top-0 z-10 text-slate-100">
                            <tr>
                                <th class="py-3 px-2 w-[5%]"></th>
                                <th class="py-3 px-4 w-[5%]">No</th>
                                <th class="py-3 px-2 w-[5%]">Jenis Barang</th>
                                <th class="py-3 px-2 w-[10%]">Serial Number</th>
                                <th class="py-3 px-2 w-[10%]">Tujuan</th>
                                <th class="py-3 px-2 w-[10%]">Lokasi Sekarang</th>
                                <th class="py-3 px-2 w-[10%]">Tanggal Masuk</th>
                                <th class="py-3 px-2 w-[10%]">Tanggal Keluar</th>
                                <th class="py-3 px-4 w-[10%] text-center">Status</th>
                                <th class="py-3 px-4 w-[10%] text-right">Show QR</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @if (count($this->dataItems) == 0)
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-slate-600">
                                        No data available.
                                    </td>
                                </tr>
                            @else
                                @foreach ($this->dataItems as $item)
                                    <tr
                                        class="text-left bg-slate-100 hover:bg-slate-200 border-b text-slate-600{{ collect($selectedItems)->contains('id', $item['id']) ? 'bg-blue-100' : '' }}">
                                        <td class="py-4 ps-4">
                                            <label class="inline-flex items-center cursor-pointer group">
                                                <input type="checkbox" class="hidden"
                                                    wire:click="toggleSelect({{ json_encode($item) }})"
                                                    @checked(collect($selectedItems)->contains('id', $item['id'])) />
                                                <span
                                                    class="w-5 h-5 flex items-center justify-center rounded border border-slate-400 transition group-has-[input:checked]:bg-slate-600 group-focus:ring-2 ring-slate-600 ring-offset-1">
                                                    <flux:icon name="check"
                                                        class="w-3 h-3 text-slate-100 opacity-0 group-has-[input:checked]:opacity-100 transition" />
                                                </span>
                                            </label>
                                        </td>
                                        <td class="py-4 px-4 text-center">{{ $loop->iteration }}</td>
                                        <td class="py-4 px-2 text-center">{{ $item['barang_item_name'] }}</td>
                                        <td class="py-4 px-2 text-center">
                                            <input type="text" value="{{ $item['SN'] ?? '-' }}"
                                                class="focus:outline-none focus:bg-neutral-200 bg-transparent border-0 py-2 px-2 rounded-md w-25 text-center"
                                                wire:keydown.enter="updateValue({{ $item['id'] }}, $event.target.value, 'SN')"
                                                @keydown.enter="
                                                $event.target.blur();
                                            ">
                                        </td>
                                        <td class="py-4 px-2 text-center">
                                            <input type="text" value="{{ $item['goals'] ?? '-' }}"
                                                class="focus:outline-none focus:bg-neutral-200 w-max-fit bg-transparent border-0 py-2 px-2 rounded-md w-25 text-center"
                                                wire:keydown.enter="updateValue({{ $item['id'] }}, $event.target.value, 'goals')"
                                                @keydown.enter="
                                                $event.target.blur();
                                            ">
                                        </td>
                                        <td class="py-4 px-2 text-center">{{ $item['current_location'] }}</td>
                                        <td class="py-4 px-2 text-center">
                                            {{ \Carbon\Carbon::parse($item['last_barang_income'])->format('Y-m-d') }}
                                        </td>
                                        {{-- <td class="py-4 px-2">{{ \Carbon\Carbon::parse($item['sent_barang_outcome'])->format('Y-m-d') ?? '-' }}</td> --}}
                                        <td class="py-4 px-2 text-center">
                                            {{ $item['sent_barang_outcome'] === null ? '-' : \Carbon\Carbon::parse($item['sent_barang_outcome'])->format('Y-m-d') }}
                                        </td>
                                        <td class="py-4 px-2 text-center">
                                            <flux:badge>
                                                {{ strtoupper(str_replace('_', ' ', $item['status'])) }}
                                            </flux:badge>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <flux:button size="sm">Show QR</flux:button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="justify-between items-center p-4 bg-slate-600 hidden lg:grid lg:grid-cols-3">
                    <p class="text-sm text-slate-100">
                        Page {{ $current_page }} of {{ $total_pages }}
                    </p>

                    <ul class="flex gap-0.5 text-sm justify-center">

                        {{-- Prev --}}
                        <li>
                            <button wire:click="prevPage"
                                class="block px-2 py-0.5 text-slate-100 rounded cursor-pointer hover:bg-slate-700">
                                <flux:icon name="chevron-left" />
                            </button>
                        </li>

                        {{-- First Page --}}
                        @if ($this->start > 1)
                            <li>
                                <button wire:click="goToPage(1)"
                                    class="block px-2 py-0.5 text-slate-100 hover:bg-slate-700 rounded cursor-pointer">1</button>
                            </li>

                            @if ($this->start > 2)
                                <li class="px-2 py-0.5 text-slate-300">...</li>
                            @endif
                        @endif

                        {{-- Middle Pages --}}
                        @for ($i = $this->start; $i <= $this->end; $i++)
                            <li>
                                <button wire:click="goToPage({{ $i }})"
                                    class="block px-2 py-0.5 rounded cursor-pointer
                                    {{ $i == $current_page ? 'bg-slate-700 text-slate-100' : 'text-slate-100 hover:bg-slate-700' }}">
                                    {{ $i }}
                                </button>
                            </li>
                        @endfor

                        {{-- Last Page --}}
                        @if ($this->end < $total_pages)
                            @if ($this->end < $total_pages - 1)
                                <li class="px-2 py-0.5 text-slate-300">...</li>
                            @endif

                            <li>
                                <button wire:click="goToPage({{ $total_pages }})"
                                    class="block px-2 py-0.5 text-slate-100 hover:bg-slate-700 rounded cursor-pointer">
                                    {{ $total_pages }}
                                </button>
                            </li>
                        @endif

                        {{-- Next --}}
                        <li>
                            <button wire:click="nextPage"
                                class="block px-2 py-0.5 text-slate-100 rounded cursor-pointer hover:bg-slate-700">
                                <flux:icon name="chevron-right" />
                            </button>
                        </li>
                    </ul>
                    <div class="flex justify-end">
                        @if (count($selectedItems) > 0)
                            <flux:modal.trigger name="action-modal-2">
                                <flux:button>
                                    Action Select Data ({{ count($selectedItems) }})
                                </flux:button>
                            </flux:modal.trigger>
                        @endif
                    </div>
                </div>

                <div class="justify-between items-center p-4 bg-slate-600 flex flex-col lg:hidden gap-2">

                    <ul class="flex gap-0.5 text-sm">
                        {{-- Prev --}}
                        <li>
                            <button wire:click="prevPage"
                                class="block px-2 py-0.5 text-slate-100 rounded cursor-pointer hover:bg-slate-700">
                                <flux:icon name="chevron-left" />
                            </button>
                        </li>

                        {{-- First Page --}}
                        @if ($this->start > 1)
                            <li>
                                <button wire:click="goToPage(1)"
                                    class="block px-2 py-0.5 text-slate-100 hover:bg-slate-700 rounded cursor-pointer">1</button>
                            </li>

                            @if ($this->start > 2)
                                <li class="px-2 py-0.5 text-slate-300">...</li>
                            @endif
                        @endif

                        {{-- Middle Pages --}}
                        @for ($i = $this->start; $i <= $this->end; $i++)
                            <li>
                                <button wire:click="goToPage({{ $i }})"
                                    class="block px-2 py-0.5 rounded cursor-pointer
                                    {{ $i == $current_page ? 'bg-slate-700 text-slate-100' : 'text-slate-100 hover:bg-slate-700' }}">
                                    {{ $i }}
                                </button>
                            </li>
                        @endfor

                        {{-- Last Page --}}
                        @if ($this->end < $total_pages)
                            @if ($this->end < $total_pages - 1)
                                <li class="px-2 py-0.5 text-slate-300">...</li>
                            @endif

                            <li>
                                <button wire:click="goToPage({{ $total_pages }})"
                                    class="block px-2 py-0.5 text-slate-100 hover:bg-slate-700 rounded cursor-pointer">
                                    {{ $total_pages }}
                                </button>
                            </li>
                        @endif

                        {{-- Next --}}
                        <li>
                            <button wire:click="nextPage"
                                class="block px-2 py-0.5 text-slate-100 rounded cursor-pointer hover:bg-slate-700">
                                <flux:icon name="chevron-right" />
                            </button>
                        </li>

                    </ul>
                    <p class="text-sm text-slate-100">
                        Page {{ $current_page }} of {{ $total_pages }}
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div
                class="justify-start items-center bg-slate-600 rounded-2xl lg:rounded-s-2xl py-0 px-4  text-slate-100 w-full h-20 lg:flex hidden">
                <div
                    class="bg-neutral-100 w-18 h-15 flex items-center justify-center rounded-full text-neutral-600 me-4 text-lg font-semibold">
                    {{ $this->projectData['code'] }}
                </div>
                <marquee class="text-3xl font-medium py-2 nowrap">"{{ $this->projectData['name'] }}"</marquee>
            </div>
            <div class=" p-4 rounded-xl lg:rounded-s-xl border bg-slate-200 shadow-xl ">
                <div class="flex justify-between items-center mb-4">
                    <p class="text-3xl font-medium text-slate-700 mb-1">Tracking Barang</p>
                    <a href="" class="text-sm text-decoration-none text-slate-500">View All</a>
                </div>

                <div class="flex flex-col justify-between px-4 gap-y-4 max-h-[567px] overflow-y-auto">
                    @for ($i = 0; $i < 10; $i++)
                        <div class="flex gap-8">
                            <div class="flex flex-col text-xs font-medium text-slate-600 items-center gap-y-2">
                                <p class="text-nowrap">20-10-2022</p>
                                <div class="h-full w-1 bg-slate-400 rounded-2xl"></div>
                            </div>
                            <div class="flex w-full mt-4">
                                <div class="bg-slate-500 w-1.5 h-full"></div>
                                <div
                                    class="flex flex-col text-slate-200 items-start bg-slate-500/50 w-full p-2 rounded-e-lg">
                                    <p class="text-lg font-bold">20 Unit Keluar</p>
                                    <p class="text-xs">Ke Karoseri</p>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <flux:modal name="action-modal-2" class="w-full max-w-6xl">
            <div class="space-y-6">

                <div class="text-center">
                    <flux:heading size="lg">Data Barang Terpilih</flux:heading>
                    <flux:separator class="my-3" />
                </div>

                {{-- SELECTED ITEMS TABLE --}}
                <table class="text-xs w-full">
                    <thead class="bg-slate-600 sticky top-0 z-10 text-slate-100">
                        <tr>
                            <th class="py-3 px-2 text-left">No</th>
                            <th class="py-3 px-2 text-left">Jenis Barang</th>
                            <th class="py-3 px-2 text-left">Serial Number</th>
                            <th class="py-3 px-2 text-left">Lokasi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600 divide-y">
                        @forelse ($selectedItems as $index => $item)
                            <tr class="bg-slate-100 hover:bg-slate-200 border-b">
                                <td class="py-3 px-2">{{ $index + 1 }}</td>
                                <td class="py-3 px-2">{{ $item['barang_item_name'] ?? $item['name'] }}</td>
                                <td class="py-3 px-2">{{ $item['SN'] }}</td>
                                <td class="py-3 px-2">{{ $item['current_location'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-500">
                                    No selected items.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- MOVEMENT SELECT --}}
                <flux:select wire:model.live="actionMovement" placeholder="Pilih Movement" class="w-full ">
                    <flux:select.option value="shipping">SHIPPING</flux:select.option>
                    <flux:select.option value="move">MOVE</flux:select.option>
                    <flux:select.option value="return">RETURN</flux:select.option>
                </flux:select>

                <flux:separator />

                {{-- CHILD COMPONENTS --}}
                @if ($actionMovement === 'shipping')
                    <livewire:formDetail.shipping :items="$selectedItems" />
                @elseif ($actionMovement === 'move')
                    <livewire:formDetail.move :items="$selectedItems" />
                @elseif ($actionMovement === 'return')
                    <livewire:formDetail.return :items="$selectedItems" />
                @endif

            </div>
        </flux:modal>


        {{-- Modal --}}
        <flux:modal name="action-modal" class="w-full max-w-6xl">
            <div class="space-y-6" x-data="{
                selectedIds: @entangle('selectedRows').live,
                allItems: @js($this->dataItems),
                actionType: '',
                get selectedItems() {
                    return this.selectedIds
                        .map(id => this.allItems.find(i => i.id == id))
                        .filter(Boolean)
                }
            }">

                <!-- Header -->
                <div class="text-center">
                    <flux:heading size="lg">Data Barang Terpilih</flux:heading>
                    <flux:separator class="my-3" />
                </div>

                <!-- List Barang yang dipilih -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <template x-for="item in selectedItems" :key="item.id">
                        <div class="bg-gray-50 border rounded-lg p-4 shadow-inner">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-700">Jenis Barang:</span>
                                <span x-text="item.name"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-700">Serial:</span>
                                <span x-text="item.SN"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-700">Lokasi:</span>
                                <span x-text="item.current_location"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Action Buttons (below the card list) -->
                <div class="flex space-x-4 justify-center mt-6">
                    <button x-on:click="actionType = 'SHIPPING'"
                        :class="{
                            'bg-blue-500 text-white': actionType === 'SHIPPING',
                            'hover:bg-blue-200': actionType !== 'SHIPPING',
                            'text-blue-600': actionType !== 'SHIPPING'
                        }"
                        class="text-center px-6 py-2 rounded-lg transition duration-300 cursor-pointer">
                        Form SHIPPING
                    </button>
                    <button x-on:click="actionType = 'MOVE'"
                        :class="{
                            'bg-green-500 text-white': actionType === 'MOVE',
                            'hover:bg-green-200': actionType !== 'MOVE',
                            'text-green-600': actionType !== 'MOVE'
                        }"
                        class="text-center px-6 py-2 rounded-lg transition duration-300 cursor-pointer">
                        Form MOVE
                    </button>
                    <button x-on:click="actionType = 'RETURNED'"
                        :class="{
                            'bg-red-500 text-white': actionType === 'RETURNED',
                            'hover:bg-red-200': actionType !== 'RETURNED',
                            'text-red-600': actionType !== 'RETURNED'
                        }"
                        class="text-center px-6 py-2 rounded-lg transition duration-300 cursor-pointer">
                        Form RETURNED TO WAREHOUSE
                    </button>
                </div>

                <!-- Form Content (will show below button when selected) -->

                <!-- Form SHIPPING -->
                <div x-show="actionType === 'SHIPPING'" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200 absolute inset-0"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="mt-6 border rounded-lg p-4 bg-blue-50 shadow-inner relative">
                    <flux:heading size="md" class="mb-3 text-blue-600">Form SHIPPING</flux:heading>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <flux:label>Nama Penerima</flux:label>
                            <flux:input placeholder="Nama penerima barang" />
                        </div>
                        <div>
                            <flux:label>Alamat Tujuan</flux:label>
                            <flux:input placeholder="Alamat tujuan pengiriman" />
                        </div>
                        <div>
                            <flux:label>No. Resi</flux:label>
                            <flux:input placeholder="Masukkan nomor resi" />
                        </div>
                        <div>
                            <flux:label>Tanggal Kirim</flux:label>
                            <input type="date" class="border rounded-lg w-full p-2" />
                        </div>
                        <div>
                            <flux:label>Kurir / Vendor</flux:label>
                            <flux:input placeholder="Nama ekspedisi atau vendor" />
                        </div>
                    </div>
                </div>

                <!-- Form MOVE -->
                <div x-show="actionType === 'MOVE'" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200 absolute inset-0"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="mt-6 border rounded-lg p-4 bg-green-50 shadow-inner relative">
                    <flux:heading size="md" class="mb-3 text-green-600">Form MOVE</flux:heading>

                    <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
                        <div>
                            <flux:label>Lokasi Tujuan</flux:label>
                            <input type="text" class="border rounded-lg w-full p-2"
                                placeholder="Lokasi tujuan barang" wire:model="moveLocation" />
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
                </div>

                <!-- Form RETURNED -->
                <div x-show="actionType === 'RETURNED'" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200 absolute inset-0"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="mt-6 border rounded-lg p-4 bg-red-50 shadow-inner relative">
                    <flux:heading size="md" class="mb-3 text-red-600">Form RETURNED TO WAREHOUSE</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                        <div>
                            <flux:label>Tanggal Pindah</flux:label>
                            <input type="date" class="border rounded-lg w-full p-2" wire:model="moveDate" />
                        </div>
                        <div class="md:col-span-3">
                            <flux:label>Keterangan</flux:label>
                            <textarea class="border rounded-lg w-full p-2" placeholder="Keterangan perpindahan..." wire:model="moveNote"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <flux:separator class="my-3" />
                <div class="flex justify-end mt-6">
                    <flux:button variant="primary" class="px-6 py-2"
                        x-on:click="
                    if (actionType === 'SHIPPING') {
                        $wire.call('submitShipping', selectedIds);
                    } else if (actionType === 'MOVE') {
                        $wire.call('submitMove', selectedIds);
                    } else if (actionType === 'RETURNED') {
                        $wire.call('submitReturned', selectedIds);
                    } else {
                        alert('Silakan pilih jenis aksi terlebih dahulu.');
                    }
                ">
                        Submit
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="add-modal" class="w-full max-w-4xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Form Barang Masuk</flux:heading>
                    <flux:text class="mt-2">
                        Isi form berikut untuk menambahkan data barang masuk ke dalam sistem.
                    </flux:text>
                </div>
                <flux:separator></flux:separator>
                <table class="w-full text-sm shadow-lg">
                    <thead class="bg-zinc-200 dark:bg-zinc-900 border-b border-zinc-800">
                        <tr class="text-left border-b border-zinc-800 uppercase">
                            <th class="py-4 px-4">No</th>
                            <th class="py-4 px-2 min-w-[500px]">Name</th>
                            <th class="py-4 px-2">Quantity</th>
                            <th class="py-4 px-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            class="border-b border-zinc-800 dark:border-zinc-900 hover:bg-zinc-200 dark:hover:bg-zinc-900/50">
                            <td class="py-4 px-4">1</td>
                            <td class="py-4 px-2">Server</td>
                            <td class="py-4 px-2">50</td>
                            <td class="py-4 px-2 justify-end flex gap-2">
                                <flux:button variant="ghost" size="sm">
                                    <flux:icon name="x-circle" />
                                </flux:button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex justify-between items-center gap-4">
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300 min-w-[80px]">Pilih Barang
                    </flux:label>
                    <flux:select placeholder="Pilih Barang" class="min-w-[100px]">

                    </flux:select>
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300 min-w-[80px]">Jumlah Pcs
                    </flux:label>
                    <flux:input wire:model="name" placeholder="Jumlah Pcs" />
                    <flux:button variant="primary">Add</flux:button>
                </div>


                <div class="flex justify-between items-center gap-4">
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Tanggal</flux:label>
                    <input type="date"
                        class="border border-zinc-300 z rounded-lg px-3 py-2 focus:outline-none min-w-[150px]" />
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Resi</flux:label>
                    {{-- <input type="file"
                    class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-800 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent min-w-[150px]" /> --}}
                    <flux:input placeholder="Resi" />
                </div>
                <flux:separator></flux:separator>
                <div class="flex justify-end gap-4">
                    <flux:button variant="primary">Simpan</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let project = localStorage.getItem('projectSelected');

            if (project) {
                Livewire.dispatch('receiveProject', {
                    project: JSON.parse(project)
                });
                console.log('Project data received from localStorage:', project);

                // Optional: hapus supaya tidak persistent
                localStorage.removeItem('projectSelected');
            }
        });
    </script>
</div>
