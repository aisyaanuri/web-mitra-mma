<x-layouts::auth :title="__('Masuk ke Portal')">
    <div class="flex flex-col gap-6">
        {{-- Brand Logo & Name --}}
        <div>
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-3">
                <img
                    src="{{ asset('images/logo-mustika-square.webp') }}"
                    alt="{{ config('app.name') }}"
                    class="h-10 w-10 rounded-xl object-contain bg-white shadow-xs border border-slate-100 p-0.5"
                >
                <div>
                    <span class="font-display text-xl font-extrabold tracking-tight text-slate-900 dark:text-white block leading-none">
                        Mustika.
                    </span>
                    <span class="text-[11px] font-medium text-slate-400 dark:text-zinc-400">PT Mustika Makmur Abadi</span>
                </div>
            </a>
        </div>

        {{-- Greeting --}}
        <div>
            <h2 class="font-display text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                Hello there !
            </h2>
            <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                Silakan masukkan email dan kata sandi untuk mengakses portal distribusi.
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
            @csrf

            <!-- Email Address -->
            <div>
                <flux:input
                    name="email"
                    label="Email"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@example.com"
                />
            </div>

            <!-- Password -->
            <div>
                <flux:input
                    name="password"
                    label="Password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    viewable
                />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-xs pt-1">
                <flux:checkbox name="remember" label="Remember me" :checked="old('remember')" />

                @if (Route::has('password.request'))
                    <flux:link class="text-xs font-semibold text-[#1065e2] hover:underline dark:text-blue-400" :href="route('password.request')" wire:navigate>
                        Forgot your password?
                    </flux:link>
                @endif
            </div>

            <div class="pt-2">
                <flux:button variant="primary" type="submit" class="w-full !bg-[#1065e2] hover:!bg-[#0c58c9] !py-3 font-semibold text-white !rounded-xl shadow-md shadow-blue-500/25 transition-all transform active:scale-[0.99]" data-test="login-button">
                    Sign in
                </flux:button>
            </div>
        </form>

        <x-passkey-verify
            label="Sign in with a passkey"
            separator="OR CONTINUE WITH EMAIL"
        />

        @if ($whatsapp = \App\Models\SiteSetting::get('whatsapp_number'))
            <div class="text-center text-xs text-slate-400 dark:text-zinc-500 pt-1">
                Mengalami kendala?
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="text-[#1065e2] hover:underline font-semibold dark:text-blue-400">
                    Hubungi Admin
                </a>
            </div>
        @endif
    </div>
</x-layouts::auth>
