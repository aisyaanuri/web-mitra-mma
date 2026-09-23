<?php

use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Form Pengguna')] class extends Component
{
    public ?User $user = null;

    public bool $isSelf = false;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public bool $is_active = true;

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(?User $user = null): void
    {
        if ($user?->exists) {
            $this->user = $user;
            $this->isSelf = $user->id === Auth::id();
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role->value;
            $this->is_active = $user->is_active;
        } else {
            $this->role = UserRole::AdminGudang->value;
        }
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
        ];

        if (! $this->user || filled($this->password)) {
            $rules['password'] = ['required', 'confirmed', Password::default()];
        }

        $validated = $this->validate($rules);

        $user = $this->user ?? new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Owner cannot change their own role or lock themselves out.
        if (! $this->isSelf) {
            $user->role = $validated['role'];
            $user->is_active = $validated['is_active'];
        }

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        if (! $user->exists) {
            $user->email_verified_at = now();
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Pengguna disimpan.'));

        $this->redirectRoute('admin.users.index', navigate: true);
    }
}; ?>

<div class="max-w-xl">
    <flux:heading size="xl" class="font-display">{{ $user ? __('Edit Pengguna') : __('Tambah Pengguna') }}</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-6">
        <flux:input wire:model="name" label="{{ __('Nama') }}" required />
        <flux:input wire:model="email" label="{{ __('Email') }}" type="email" required />

        <flux:select wire:model="role" label="{{ __('Role') }}" required :disabled="$isSelf">
            @foreach (UserRole::cases() as $case)
                <flux:select.option value="{{ $case->value }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>
        @if ($isSelf)
            <p class="-mt-4 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Anda tidak dapat mengubah role akun sendiri.') }}</p>
        @endif

        @if (! $isSelf)
            <flux:checkbox wire:model="is_active" label="{{ __('Aktif') }}" />
        @endif

        <flux:input wire:model="password" label="{{ $user ? __('Password Baru (opsional)') : __('Password') }}" type="password" viewable />
        <flux:input wire:model="password_confirmation" label="{{ __('Konfirmasi Password') }}" type="password" viewable />

        <div class="flex justify-end gap-2">
            <flux:button :href="route('admin.users.index')" wire:navigate>{{ __('Batal') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
        </div>
    </form>
</div>
