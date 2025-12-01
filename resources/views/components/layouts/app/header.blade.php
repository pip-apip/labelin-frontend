<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 ">

    <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <a href="{{ route('paket') }}" class="ml-2 mr-5 flex items-center space-x-2 lg:ml-0" wire:navigate>
            <x-app-logo class="size-8" href="#">Label-in</x-app-logo>
        </a>

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="layout-grid" href="{{ route('paket') }}" :current="request()->routeIs('paket') || request()->routeIs('paket.show')" wire:navigate>
                Dashboard
            </flux:navbar.item>
            {{-- <flux:navbar.item icon="layout-grid" href="{{ route('barang-masuk') }}"
            :current="request()->routeIs('barang-masuk')" wire:navigate>
            Barang Masuk
            </flux:navbar.item> --}}
        </flux:navbar>

        <flux:spacer />

        <!-- Desktop User Menu -->
        <flux:dropdown position="top" align="end">
            <flux:profile :name="session('user.name')" :initials="'GS'" icon-trailing="chevrons-up-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ session('user.name') }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ session('user.name') }}</span>
                                <span class="truncate text-xs">{{ session('user.name') }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item href="/settings/appearance" icon="cog" wire:navigate>Settings</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <!-- Mobile Menu -->
    <flux:sidebar stashable sticky class="lg:hidden border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('paket') }}" class="ml-1 flex items-center space-x-2" wire:navigate>
            <x-app-logo class="size-8" href="#"></x-app-logo>
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group heading="Platform">
                <flux:navlist.item icon="layout-grid" href="{{ route('paket') }}" :current="request()->routeIs('paket') || request()->routeIs('paket.show')" wire:navigate>
                    Dashboard
                </flux:navlist.item>
                {{-- <flux:navlist.item icon="layout-grid" href="{{ route('barang-masuk') }}"
                :current="request()->routeIs('paket')" wire:navigate>
                Barang Masuk
                </flux:navlist.item> --}}
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

    </flux:sidebar>
    <x-toaster-hub /> <!-- 👈 -->
    {{ $slot }}

    @fluxScripts
</body>

@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>


    document.addEventListener('livewire:navigated', function(e) {
        // solusi utama
        setTimeout(() => {
            initSelect2Lokasi();
            initSelect2();
        }, 100);
    });

</script>

</html>
