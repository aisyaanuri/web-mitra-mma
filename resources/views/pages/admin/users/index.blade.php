<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kelola Pengguna')] class extends Component
{
    public function toggleActive(User $user): void
    {
        abort_if($user->id === Auth::id(), 403, __('Tidak dapat menonaktifkan akun sendiri.'));

        $user->update(['is_active' => ! $user->is_active]);

        Flux::toast(variant: 'success', text: $user->is_active ? __('Akun diaktifkan.') : __('Akun dinonaktifkan.'));
    }

    public function unlock(User $user): void
    {
        $user->forceFill(['failed_login_attempts' => 0, 'locked_until' => null])->save();

        Flux::toast(variant: 'success', text: __('Akun dibuka kembali.'));
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get();
    }
}; ?>

<div class="flex w-full flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="xl" class="font-display">{{ __('Kelola Pengguna') }}</flux:heading>
        <flux:button variant="primary" :href="route('admin.users.create')" wire:navigate>
            {{ __('Tambah Pengguna') }}
        </flux:button>
    </div>

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
                @foreach ($this->users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="text-xs text-zinc-400">({{ __('Anda') }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $user->role->label() }}</td>
                        <td class="px-4 py-3">
                            @if ($user->isLocked())
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-400">
                                    {{ __('Terkunci') }}
                                </span>
                            @elseif ($user->is_active)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-400">
                                    {{ __('Aktif') }}
                                </span>
                            @else
                                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ __('Nonaktif') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">
                            {{ $user->last_login_at?->diffForHumans() ?? __('Belum pernah') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @if ($user->isLocked())
                                    <flux:button size="sm" wire:click="unlock({{ $user->id }})">
                                        {{ __('Buka Kunci') }}
                                    </flux:button>
                                @endif

                                <flux:button size="sm" :href="route('admin.users.edit', $user)" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>

                                @if ($user->id !== auth()->id())
                                    <flux:button
                                        size="sm"
                                        :variant="$user->is_active ? 'danger' : 'primary'"
                                        wire:click="toggleActive({{ $user->id }})"
                                        wire:confirm="{{ $user->is_active ? __('Nonaktifkan akun :name?', ['name' => $user->name]) : __('Aktifkan kembali akun :name?', ['name' => $user->name]) }}"
                                    >
                                        {{ $user->is_active ? __('Nonaktifkan') : __('Aktifkan') }}
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
