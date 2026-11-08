@extends('layouts.app')

@section('content')
<section class="w-full">
    @include('partials.settings-heading')
    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form method="POST" action="{{ route('profile.update') }}" class="my-6 w-full space-y-6">
            @csrf
            @method('PATCH')
            <div>
                <label for="name" class="block text-sm font-medium">{{ __('Name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <p class="mt-3 text-sm text-zinc-600">{{ __('Your email address is unverified.') }}</p>
                @endif
            </div>
            @if (session('status') === 'verification-link-sent')
                <p class="text-sm font-medium text-green-600">{{ __('A new verification link has been sent to your email address.') }}</p>
            @elseif (session('status'))
                <p class="text-sm font-medium text-green-600">{{ session('status') }}</p>
            @endif
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Save') }}</button>
        </form>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <form method="POST" action="{{ route('verification.send') }}" class="mt-2">
                @csrf
                <button class="text-sm text-blue-600 underline">{{ __('Click here to re-send the verification email.') }}</button>
            </form>
        @endif

        @if (! $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail || $user->hasVerifiedEmail())
            <div class="mt-10 border-t pt-6">
                <h3 class="font-semibold">{{ __('Delete account') }}</h3>
                <p class="mt-1 text-sm text-zinc-500">{{ __('This action permanently deletes your account.') }}</p>
                <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4 space-y-3">
                    @csrf
                    @method('DELETE')
                    <div>
                        <label for="delete-password" class="block text-sm font-medium">{{ __('Confirm your password') }}</label>
                        <input id="delete-password" name="password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" onclick="return confirm('{{ __('Are you sure you want to delete your account?') }}')" class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-600">{{ __('Delete account') }}</button>
                </form>
            </div>
        @endif
    </x-pages::settings.layout>
</section>
@endsection
