@extends('layouts.app')

@section('content')
<div class="flex w-full flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="xl" class="font-display">{{ __('Kelola Pengguna') }}</flux:heading>
        <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Tambah Pengguna') }}</a>
    </div>

    @if (session('status'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Nama') }}</th>
                    <th class="px-4 py-3">{{ __('Email') }}</th>
                    <th class="px-4 py-3">{{ __('Role') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Login Terakhir') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $user->name }} @if ($user->id === auth()->id()) <span class="text-xs text-zinc-400">({{ __('Anda') }})</span> @endif</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $user->role->label() }}</td>
                        <td class="px-4 py-3">
                            @if ($user->isLocked())
                                <span class="text-red-600">{{ __('Terkunci') }}</span>
                            @elseif ($user->is_active)
                                <span class="text-green-600">{{ __('Aktif') }}</span>
                            @else
                                <span class="text-zinc-500">{{ __('Nonaktif') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $user->last_login_at?->diffForHumans() ?? __('Belum pernah') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @if ($user->isLocked())
                                    <form method="POST" action="{{ route('admin.users.unlock', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded border px-3 py-1">{{ __('Buka Kunci') }}</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.users.edit', $user) }}" class="rounded border px-3 py-1">{{ __('Edit') }}</a>
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded border px-3 py-1">{{ $user->is_active ? __('Nonaktifkan') : __('Aktifkan') }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
