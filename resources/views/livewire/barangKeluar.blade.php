<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <div
        class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl hover:shadow-2xl transition">
        <div class="flex justify-between text-bottom items-center">
            <flux:label class="text-lg font-medium text-gray-900 dark:text-gray-300 min-w-[100px]">Pilih Paket
            </flux:label>
            <flux:select placeholder="Pilih Paket" class="min-w-[100px]">
                <flux:select.option value="asc">ASC</flux:select.option>
                <flux:select.option value="desc">DESC</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div
        class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl hover:shadow-2xl transition">
        <div class="flex justify-between text-bottom items-center">
            <span>
                <flux:heading size="xl" level="1">Paket</flux:heading>
                <flux:subheading size="lg" class="">Manage your Paket List</flux:subheading>
            </span>
        </div>
    </div>

    <div class="relative mb-6 w-full border border-zinc-100 p-6 rounded-lg bg-zinc-50 shadow-2xl">
        <div class="flex justify-between text-bottom items-center mb-6">
            <div class="flex gap-4 col-span-2">
                <div class="flex justify-between gap-4">
                    {{-- <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Search</flux:label> --}}
                    <flux:input type="text" icon="magnifying-glass" placeholder="Search Paket"
                        wire:model.live.debounce.350ms="searchQuery" class="min-w-[300px]" />
                </div>
            </div>
            <div class="flex justify-between gap-4">
                {{-- <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Sort</flux:label> --}}
                <flux:select wire:model.live="filterSort" placeholder="Sort By" class="min-w-[150px]">
                    <flux:select.option value="name">Name</flux:select.option>
                    {{-- <flux:select.option value="email">Email</flux:select.option>
                    <flux:select.option value="organization">Organization</flux:select.option> --}}
                </flux:select>
                <flux:select wire:model.live="filterOrder" placeholder="Order" class="min-w-[100px]">
                    <flux:select.option value="asc">ASC</flux:select.option>
                    <flux:select.option value="desc">DESC</flux:select.option>
                </flux:select>
                <flux:modal.trigger name="add-barang-modal">
                    <flux:button variant="filled">Add </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        {{-- <flux:separator />  --}}

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
                    <tr
                        class=" hover:bg-neutral-100 border-b border-neutral-300 ">
                        <td class="py-3 px-4">1</td>
                        <td class="py-3 px-2">Server</td>
                        <td class="py-3 px-2">50/100</td>
                        <td class="py-3 px-3 text-right">
                            <flux:button variant="ghost" size="sm" wire:navigate href="{{ route('paket.show') }}">
                                Detail
                            </flux:button>
                        </td>
                    </tr>
                    <tr
                        class=" hover:bg-neutral-100">
                        <td class="py-3 px-4">1</td>
                        <td class="py-3 px-2">Server</td>
                        <td class="py-3 px-2">50/100</td>
                        <td class="py-3 px-3 text-right">
                            <flux:button variant="ghost" size="sm" wire:navigate href="{{ route('paket.show') }}">
                                Detail
                            </flux:button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <flux:modal name="add-barang-modal" class="w-full max-w-4xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Form Barang Masuk</flux:heading>
                <flux:text class="mt-2">
                    Isi form berikut untuk menambahkan data barang masuk ke dalam sistem.
                </flux:text>
            </div>
            <flux:separator></flux:separator>
            <div class="flex justify-between items-center gap-4">
                <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300 min-w-[80px]">Pilih Barang
                </flux:label>
                <flux:select placeholder="Pilih Barang" class="min-w-[100px]">
                    <flux:select.option value="asc">Server</flux:select.option>
                    <flux:select.option value="desc">Laptop</flux:select.option>
                </flux:select>
                <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300 min-w-[80px]">Jumlah Pcs
                </flux:label>
                <flux:input wire:model="name" placeholder="Jumlah Pcs" />
                <flux:button variant="primary">Add</flux:button>
            </div>

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
                <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Tanggal</flux:label>
                <input type="date"
                    class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-800 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent min-w-[150px]" />
                <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Resi</flux:label>
                <input type="file"
                    class="bg-zinc-50 dark:bg-zinc-700 border border-zinc-300 dark:border-zinc-800 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent min-w-[150px]" />
            </div>
            <flux:separator></flux:separator>
            <div class="flex justify-end gap-4">
                <flux:button variant="primary">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
