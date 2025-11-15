<?php

use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string')]
    public string $username = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        session()->forget('user');
        session()->forget('token');

        $this->validate();

        $this->ensureIsNotRateLimited();

        $response = Http::post(env('API_URL_PM') . '/auth/login', [
            'username' => $this->username,
            'password' => $this->password,
        ]);
        // dd($response->json());

        if (!$response->successful()) {
            throw ValidationException::withMessages([
                'username' => 'Invalid credentials',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        session([
            'user' => [
                'id' => $response->json('data.id'),
                'name' => $response->json('data.name'),
                'username' => $response->json('data.username'),
                'role' => $response->json('data.role'),
            ],
            'token' => $response->json('data.refresh_token'),
        ]);

        $responsePL = Http::withToken($response->json('data.access_token'))->get(env('API_URL_PM') . '/projects/search?project_leader_id=' . $response->json('data.id') . '&limit=1000');

        if ($responsePL->json('data')) {
            $projects = $responsePL->json('data');
            $projectIds = [];
            foreach ($projects as $project) {
                $projectIds[] = $project['id'];
            }
            session(['user.project_leader' => true]);
            session(['user.project_id' => $projectIds]);
        } else {
            session(['user.project_leader' => false]);
            session(['user.project_id' => []]);
        }
        Session::regenerate();

        $this->redirectIntended(default: route('paket', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username) . '|' . request()->ip());
    }
}; ?>

<div class="flex flex-col gap-6">
    {{-- @if (isset(session('user'))) --}}
    {{-- @php
            dd(session()->all());
        @endphp --}}
    {{-- @endif --}}
    <x-auth-header title="Log in to your account" description="Enter your username and password below to log in" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Username Address -->
        <flux:input wire:model="username" label="{{ __('Username address') }}" type="text" name="usenrame" required
            autofocus autocomplete="username" placeholder="Username" />

        <!-- Password -->
        <div class="relative">
            <flux:input wire:model="password" label="{{ __('Password') }}" type="password" name="password" required
                autocomplete="current-password" placeholder="Password" />

            @if (Route::has('password.request'))
                <x-text-link class="absolute right-0 top-0" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </x-text-link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" label="{{ __('Remember me') }}" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Log in') }}</flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        Don't have an account?
        <x-text-link href="{{ route('register') }}">Sign up</x-text-link>
    </div>
</div>
