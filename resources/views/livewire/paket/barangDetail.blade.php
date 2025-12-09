<?php

use Livewire\Volt\Component;
use Illuminate\Support\Carbon;

new class extends Component {
    public $selectedRows = [];
    public $dataItems = [];
    public $itemId = 0;

    public $moveLocation = '';
    public $moveDate = '';
    public $moveNote = '';

    public function mount()
    {
        $this->itemId = $this->getQueryStringParams();
        $this->getItemDetails($this->itemId);
    }

    public function getQueryStringParams()
    {
        $this->url = request()->url();
        $segments = explode('/', parse_url($this->url, PHP_URL_PATH));
        return end($segments);
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
            \Log::info($e);
            \Log::info('Response: ', $e->response->json());
            \Log::error('Error fetching item details: ' . $e->getMessage());
        }
    }

    public function updateValue($id, $value, $field)
    {
        try {
            $response = Http::withToken(session('token'))
                ->put(env('API_URL_LN') . '/barang/details/' . $id, [
                    $field => $value,
                ])
                ->throw();
            $this->getItemDetails($this->itemId);
        } catch (\Exception $e) {
            \Log::info('Response: ', $e->response->json());
            \Log::error('Error updating item status: ' . $e->getMessage());
        }
    }

    public function submitShipping($selectedRows)
    {
        dd('Submitting SHIPPING for:', $selectedRows);
    }

    public function submitMove($selectedRows)
    {
        $selectedData = array_filter(
            array_map(function ($item) use ($selectedRows) {
                return in_array($item['id'], $selectedRows)
                    ? [
                        'barang_item_detail_id' => $item['id'],
                        'from_location' => $item['current_location'],
                    ]
                    : null;
            }, $this->dataItems),
        );

        $body = [
            'items' => $selectedData,
            'to_location' => $this->moveLocation,
            'sent_at' => $this->moveDate,
            'sent_by' => session('user.name'),
            'note' => $this->moveNote,
        ];

        try {
            $response = Http::withToken(session('token'))
                ->post(env('API_URL_LN') . '/barang/outcomes', $body)
                ->throw();
            dd('Move action submitted successfully:', $response->json());
        } catch (\Exception $e) {
            \Log::info('Response: ', $e->response->json());
            \Log::error('Error submitting move action: ' . $e->getMessage());
        }
    }
}; ?>

<div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 mb-6">
        <!-- Card -->
        <div
            class="bg-white border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-md hover:shadow-md transition flex items-center gap-4">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 text-center sm:text-left">

                <!-- Icon Section -->
                <div class="flex-shrink-0 p-4 rounded-2xl flex items-center justify-center">
                    <flux:icon name="users" class="w-12 h-12 text-blue-500" />
                </div>

                <!-- Text Section -->
                <div class="flex flex-col justify-center">
                    <flux:heading size="xl" level="1" class="font-semibold mb-1">
                        Paket 10
                    </flux:heading>
                    <flux:subheading size="md" class="leading-snug text-sm sm:text-base">
                        "PENGADAAN SISTEM IDENTIFIKASI DAN PENCEGAHAN PERANGKAT PEMANTAU IDENTITAS SELULER UNTUK
                        PERLINDUNGAN PRIVASI PADA JARINGAN SELULER KEJAKSAAN RI TAHUN ANGGARAN 2025"
                    </flux:subheading>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-2 gap-3 h-fit">
            <div
                class="bg-white border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-md hover:shadow-md transition flex items-center gap-4">
                <div class="flex-shrink-0 p-3  dark:bg-zinc-800 rounded-xl">
                    <flux:icon name="users" class="w-8 h-8 text-blue-500" />
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs tracking-wide text-gray-500 font-medium">
                        Total Guests
                    </span>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                        10
                    </p>
                </div>
            </div>

            {{-- Date Widget --}}
            <div
                class="bg-white border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-md hover:shadow-md transition flex items-center gap-4">
                <div class="flex-shrink-0 p-3  dark:bg-zinc-800 rounded-xl">
                    <flux:icon name="check-badge" class="w-8 h-8 text-blue-500" />
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs tracking-wide text-gray-500 font-medium">
                        Total Confirmed
                    </span>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                        10 / 100
                    </p>
                </div>
            </div>

            {{-- Time Widget --}}
            <div
                class="bg-white border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-md hover:shadow-md transition flex items-center gap-4">
                <div class="flex-shrink-0 p-3  dark:bg-zinc-800 rounded-xl">
                    <flux:icon name="check" class="w-8 h-8 text-blue-500" />
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs tracking-wide text-gray-500 font-medium">
                        Checked In Today
                    </span>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                        0
                    </p>
                </div>
            </div>

            <div
                class="bg-white border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-md hover:shadow-md transition flex items-center gap-4">
                <div class="flex-shrink-0 p-3  dark:bg-zinc-800 rounded-xl">
                    <flux:icon name="users" class="w-8 h-8 text-blue-500" />
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs tracking-wide text-gray-500 font-medium">
                        Not Checked In Today
                    </span>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">
                        0
                    </p>
                </div>
            </div>

        </div>
    </div>

    <ul class="text-sm max-w-5xl mx-auto font-medium text-center text-gray-500 flex sm:justify-center gap-4">
        <li class="flex-1">
            <button
                class="w-full transition-colors duration-300 px-4 py-2 border border-gray-200 text-black cursor-pointer hover:bg--400 hover: rounded-2xl shadow-2xl hover:shadow-md">
                Kategori 1
            </button>
        </li>
        <li class="flex-1">
            <button
                class="w-full transition-colors duration-300 px-4 py-2 border border-gray-200 text-black cursor-pointer rounded-2xl shadow-2xl hover:shadow-md">
                Kategori 2
            </button>
        </li>
        <li class="flex-1">
            <button
                class="w-full transition-colors duration-300 px-4 py-2 border border-gray-200 text-black cursor-pointer rounded-2xl shadow-2xlhover:shadow-md">
                Kategori 3
            </button>
        </li>
    </ul>

    <div class="relative my-6 bg-white border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-2xl gap-4"
        x-data="{
            selectedIds: @entangle('selectedRows').live,

            toggleRow(id) {
                if (this.selectedIds.includes(id)) {
                    this.selectedIds = this.selectedIds.filter(x => x !== id);
                } else {
                    this.selectedIds.push(id);
                }
            },

            isSelected(id) {
                return this.selectedIds.includes(id);
            },
        }">

        <div class="flex justify-between text-bottom items-center mb-6">
            <div class="flex gap-4 col-span-2">
                <div class="flex justify-between gap-4">
                    <flux:input type="search" icon="magnifying-glass" placeholder="Search" class="min-w-[300px]" />
                </div>
            </div>
            <div class="flex justify-between gap-4">
                <flux:select wire:model.live="filterSort" placeholder="Sort By" class="min-w-[150px]">
                    <flux:select.option value="name">Name</flux:select.option>
                </flux:select>

                <flux:select wire:model.live="filterOrder" placeholder="Order" class="min-w-[100px]">
                    <flux:select.option value="asc">ASC</flux:select.option>
                    <flux:select.option value="desc">DESC</flux:select.option>
                </flux:select>

                <flux:modal.trigger name="add-modal">
                    <flux:button variant="filled">Add</flux:button>
                </flux:modal.trigger>

                <flux:button variant="filled">Export</flux:button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-neutral-300">
            <table class="w-full text-xs shadow-lg">
                <thead class="bg-neutral-200">
                    <tr class="text-left uppercase">
                        <th class="py-3 px-2"></th>
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-2">Jenis Barang</th>
                        <th class="py-3 px-2">Serial Number</th>
                        <th class="py-3 px-2">Tujuan</th>
                        <th class="py-3 px-2">Lokasi Sekarang</th>
                        <th class="py-3 px-2">Tanggal Masuk</th>
                        <th class="py-3 px-2">Tanggal Keluar</th>
                        <th class="py-3 px-4 text-center w-[250px]">Status</th>
                        <th class="py-3 px-4 text-right">Show QR</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @foreach ($this->dataItems as $item)
                        <tr class="hover:bg-neutral-100 border-b border-neutral-300" wire:key="{{ $item['id'] }}"
                            :class="{ 'bg-blue-100': isSelected({{ $item['id'] }}) }">

                            <td class="py-4 px-2 text-center">
                                <input type="checkbox" @change="toggleRow({{ $item['id'] }})"
                                    :checked="isSelected({{ $item['id'] }})">
                            </td>

                            <td class="py-4 px-4">{{ $loop->iteration }}</td>
                            <td class="py-4 px-2">{{ $item['barang_item_name'] }}</td>
                            <td class="py-4 px-2">
                                <input type="text" value="{{ $item['SN'] ?? '-' }}"
                                    class="focus:outline-none focus:bg-neutral-200 bg-transparent border-0 py-2 px-2 rounded-md"
                                    wire:keydown.enter="updateValue({{ $item['id'] }}, $event.target.value, 'SN')"
                                    @keydown.enter="
                                        $event.target.blur();
                                    ">
                            </td>
                            <td class="py-4 px-2">
                                <input type="text" value="{{ $item['goals'] ?? '-' }}"
                                    class="focus:outline-none focus:bg-neutral-200 w-max-fit bg-transparent border-0 py-2 px-2 rounded-md"
                                    wire:keydown.enter="updateValue({{ $item['id'] }}, $event.target.value, 'goals')"
                                    @keydown.enter="
                                        $event.target.blur();
                                    ">
                            </td>
                            <td class="py-4 px-2">{{ $item['current_location'] }}</td>
                            <td class="py-4 px-2">
                                {{ \Carbon\Carbon::parse($item['last_barang_income'])->format('Y-m-d') }}</td>
                            {{-- <td class="py-4 px-2">{{ \Carbon\Carbon::parse($item['sent_barang_outcome'])->format('Y-m-d') ?? '-' }}</td> --}}
                            <td class="py-4 px-2">
                                {{ $item['sent_barang_outcome'] === null ? '-' : \Carbon\Carbon::parse($item['sent_barang_outcome'])->format('Y-m-d') }}
                            </td>

                            <td class="text-center">
                                {{-- <flux:select class="text-center"
                                    wire:change="updateValue({{ $item['id'] }}, $event.target.value, 'status')">
                                    <flux:select.option value="ready" :selected="$item['status'] === 'ready'">
                                        READY
                                    </flux:select.option>
                                    <flux:select.option value="in_transit"
                                        :selected="$item['status'] === 'in_transit'">IN TRANSIT</flux:select.option>
                                    <flux:select.option value="at_vendor" :selected="$item['status'] === 'at_vendor'">
                                        AT VENDOR</flux:select.option>
                                    <flux:select.option value="allocated" :selected="$item['status'] === 'allocated'">
                                        ALLOCATED</flux:select.option>
                                    <flux:select.option value="shipped" :selected="$item['status'] === 'shipped'">
                                        SHIPPED</flux:select.option>
                                    <flux:select.option value="returned_to_warehouse"
                                        :selected="$item['status'] === 'returned_to_warehouse'">RETURNED TO WAREHOUSE
                                    </flux:select.option>
                                    <flux:select.option value="send_to_user"
                                        :selected="$item['status'] === 'sent_to_user'">SENT TO USER
                                    </flux:select.option>
                                    <flux:select.option value="installed" :selected="$item['status'] === 'installed'">
                                        INSTALLED</flux:select.option>
                                    <flux:select.option value="damage" :selected="$item['status'] === 'damage'">
                                        DAMAGE</flux:select.option>
                                    <flux:select.option value="lost" :selected="$item['status'] === 'lost'">
                                        LOST</flux:select.option>
                                </flux:select> --}}
                                <flux:badge {{-- color="{
                                        'ready': 'success',
                                        'in_transit': 'warning',
                                        'at_vendor': 'info',
                                        'allocated': 'primary',
                                        'shipped': 'purple',
                                        'returned_to_warehouse': 'dark',
                                        'sent_to_user': 'indigo',
                                        'installed': 'teal',
                                        'damage': 'danger',
                                        'lost': 'neutral'
                                    }" --}}>
                                    {{ strtoupper(str_replace('_', ' ', $item['status'])) }}
                                </flux:badge>
                            </td>

                            <td class="py-4 px-4 text-right">
                                <flux:modal.trigger name="qr-modal">
                                    <flux:button variant="ghost" size="sm">Show</flux:button>
                                </flux:modal.trigger>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Action Button -->
        <div class="flex justify-end mt-4" x-show="selectedIds.length > 0" x-transition>
            <flux:modal.trigger name="action-modal">
                <flux:button variant="primary">
                    Action Select Data (<span x-text="selectedIds.length"></span>)
                </flux:button>
            </flux:modal.trigger>
        </div>

    </div>


    <!-- Modal untuk menampilkan struk dari data terpilih -->
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
                        <input type="text" class="border rounded-lg w-full p-2" placeholder="Lokasi tujuan barang" wire:model="moveLocation" />
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

    <flux:modal name="qr-modal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">QR Code for .... </flux:heading>
                <flux:description class="mt-2">Scan this QR code at the </flux:description>
            </div>
            <div class="flex items-center justify-center mb-4">
                {{-- {!! $modal['qr_generated'] !!} --}}
            </div>
            <flux:button variant="primary">Download QR</flux:button>
        </div>
    </flux:modal>
</div>
