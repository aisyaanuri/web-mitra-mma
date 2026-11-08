@extends('layouts.app')

@section('content')
<section class="w-full">
    @include('partials.settings-heading')
    <x-pages::settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form method="POST" action="{{ route('settings.password.update') }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="current_password" class="block text-sm font-medium">{{ __('Current password') }}</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium">{{ __('New password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium">{{ __('Confirm password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            </div>
            @if (session('status'))
                <p class="text-sm font-medium text-green-600">{{ session('status') }}</p>
            @endif
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save') }}</button>
        </form>

        @if ($canManageTwoFactor)
            <section class="mt-12 space-y-4">
                <flux:heading>{{ __('Two-factor authentication') }}</flux:heading>
                @if ($twoFactorPending)
                    <p>{{ __('Scan this QR code with your authenticator app, then enter the code to confirm setup.') }}</p>
                    <img src="{{ route('two-factor.qr-code') }}" alt="{{ __('Two-factor QR code') }}" class="h-48 w-48">
                    @error('code') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex gap-2">
                        @csrf
                        <input name="code" required autocomplete="one-time-code" placeholder="{{ __('Authentication code') }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
                        <button class="rounded-lg border px-3 py-2">{{ __('Confirm') }}</button>
                    </form>
                @elseif ($twoFactorEnabled)
                    <p>{{ __('Two-factor authentication is enabled for your account.') }}</p>
                    <form method="POST" action="{{ route('two-factor.disable') }}">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg border border-red-300 px-3 py-2 text-sm text-red-600">{{ __('Disable 2FA') }}</button>
                    </form>
                    @php
                        $recoveryCodes = auth()->user()->two_factor_recovery_codes
                            ? json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true)
                            : [];
                    @endphp
                    @if ($recoveryCodes)
                        <details class="rounded-lg border p-4">
                            <summary class="cursor-pointer font-medium">{{ __('View recovery codes') }}</summary>
                            <div class="mt-3 grid gap-1 font-mono text-sm sm:grid-cols-2">
                                @foreach ($recoveryCodes as $code)
                                    <span>{{ $code }}</span>
                                @endforeach
                            </div>
                        </details>
                    @endif
                    <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                        @csrf
                        <button class="rounded-lg border px-3 py-2 text-sm">{{ __('Regenerate recovery codes') }}</button>
                    </form>
                @else
                    <p>{{ __('Enable two-factor authentication to add another layer of security to your account.') }}</p>
                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        <button class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white">{{ __('Enable 2FA') }}</button>
                    </form>
                @endif
            </section>
        @endif

        @if ($canManagePasskeys)
            <section class="mt-12 space-y-4">
                <flux:heading>{{ __('Passkeys') }}</flux:heading>
                <p class="text-sm text-zinc-500">{{ __('Manage your passkeys for passwordless sign-in') }}</p>
                <x-passkey-registration />
                <div class="divide-y rounded-lg border">
                    @forelse ($passkeys as $passkey)
                        <div class="flex items-center justify-between gap-4 p-4">
                            <div>
                                <p class="font-medium">{{ $passkey->name }}</p>
                                <p class="text-xs text-zinc-500">{{ __('Added :time', ['time' => $passkey->created_at->diffForHumans()]) }}</p>
                            </div>
                            <form method="POST" action="{{ route('passkey.destroy', $passkey) }}" onsubmit="return confirm('{{ __('Delete this passkey?') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm text-red-600">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    @empty
                        <p class="p-4 text-sm text-zinc-500">{{ __('No passkeys yet') }}</p>
                    @endforelse
                </div>
            </section>
        @endif
    </x-pages::settings.layout>
</section>
@endsection
