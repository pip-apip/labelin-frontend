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
            </div>
        </div>

        {{-- <flux:separator />  --}}

        <div class="overflow-hidden rounded-lg border border-neutral-300">
            <table class="w-full text-xs shadow-lg">
                <thead class="bg-neutral-200">
                    <tr class="text-left uppercase">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-2 min-w-[500px]">Tanggal</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i < 6; $i++)
                        <tr class=" hover:bg-neutral-100 border-b border-neutral-300 ">
                            <td class="py-3 px-4">{{ $i }}</td>
                            <td class="py-3 px-2">10/10/2023</td>
                            <td class="py-3 px-3 text-right">
                                <flux:modal.trigger name="detail-modal">
                                    <flux:button variant="ghost" size="sm">
                                        Detail
                                    </flux:button>
                                </flux:modal.trigger>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    <flux:modal name="detail-modal" class="w-full max-w-4xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Detail Barang Masuk</flux:heading>
                <flux:text class="mt-2">
                    Detail informasi barang masuk
                </flux:text>
            </div>
            <flux:separator></flux:separator>

            <div class="flex justify-between items-center">
                <span>
                    <flux:label class="text-sm font-medium text-gray-900 dark:text-gray-300">Tanggal :
                    </flux:label>
                    <input type="date" class="bg-transparent border-none px-3 py-2 focus:outline-none min-w-[150px]"
                        value="2022-10-11" />
                </span>
                <flux:button variant="filled">Download Resi</flux:button>
            </div>
            <div class="overflow-hidden rounded-lg border border-neutral-300">
                <table class="w-full text-xs shadow-lg">
                    <thead class="bg-neutral-200">
                        <tr class="text-left uppercase">
                            <th class="py-4 px-4">No</th>
                            <th class="py-4 px-2 min-w-[500px]">Name</th>
                            <th class="py-4 px-2">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class=" hover:bg-neutral-100 border-b border-neutral-300 ">
                            <td class="py-4 px-4">1</td>
                            <td class="py-4 px-2">Server</td>
                            <td class="py-4 px-2">50</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </flux:modal>
</div>
