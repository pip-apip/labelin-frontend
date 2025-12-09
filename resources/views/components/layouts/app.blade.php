<x-layouts.app.header>
    <flux:main class="bg-linear-to-bl from-slate-400 via-slate-200 to-slate-50">
        {{ $slot }}
    </flux:main>
    {{-- <div class="relative bg-linear-to-b from-slate-500 from-50% to-slate-200 to-150% min-h-screen p-6">
        {{ $slot }}
    </div> --}}
</x-layouts.app.header>
