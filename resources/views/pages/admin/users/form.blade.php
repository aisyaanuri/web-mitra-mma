@extends('layouts.app')

@section('content')
@php($selectedRole = old('role', $user?->role->value ?? \App\Enums\UserRole::AdminGudang->value))
<div class="max-w-xl">
    <flux:heading size="xl" class="font-display">{{ $user ? __('Edit Pengguna') : __('Tambah Pengguna') }}</flux:heading>

    <form method="POST" action="{{ $user ? route('admin.users.update', $user) : route('admin.users.store') }}" class="mt-6 space-y-6">
        @csrf
        @if ($user)
            @method('PUT')
        @endif
        <div>
            <label for="name" class="block text-sm font-medium">{{ __('Nama') }}</label>
            <input id="name" name="name" value="{{ old('name', $user?->name) }}" required class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="role" class="block text-sm font-medium">{{ __('Role') }}</label>
            <select id="role" name="role" @disabled($isSelf) required class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
                @foreach ($roles as $case)
                    <option value="{{ $case->value }}" @selected($selectedRole === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
            @if ($isSelf)
                <input type="hidden" name="role" value="{{ $selectedRole }}">
                <p class="mt-1 text-xs text-zinc-500">{{ __('Anda tidak dapat mengubah role akun sendiri.') }}</p>
            @endif
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        @if (! $isSelf)
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user?->is_active ?? true))> {{ __('Aktif') }}</label>
        @endif
        <div>
            <label for="password" class="block text-sm font-medium">{{ $user ? __('Password Baru (opsional)') : __('Password') }}</label>
            <input id="password" name="password" type="password" @required(! $user) autocomplete="new-password" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium">{{ __('Konfirmasi Password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.users.index') }}" class="rounded-lg border px-4 py-2 text-sm">{{ __('Batal') }}</a>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Simpan') }}</button>
        </div>
    </form>
</div>
@endsection
