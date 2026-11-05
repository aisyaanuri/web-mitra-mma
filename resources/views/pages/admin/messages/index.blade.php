<?php

use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Pesan Kontak Masuk')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public ?int $selectedMessageId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }

    public function openMessage(int $id): void
    {
        $this->selectedMessageId = $id;
        $message = ContactMessage::find($id);

        if ($message && $message->status === ContactMessageStatus::New) {
            $message->update(['status' => ContactMessageStatus::Read->value]);
        }
    }

    public function closeMessage(): void
    {
        $this->selectedMessageId = null;
    }

    public function updateStatus(int $id, string $statusValue): void
    {
        $status = ContactMessageStatus::tryFrom($statusValue);
        if (! $status) {
            return;
        }

        $message = ContactMessage::findOrFail($id);
        $message->update(['status' => $status->value]);

        Flux::toast(variant: 'success', text: __('Status pesan diperbarui ke :status.', ['status' => $status->label()]));
    }

    public function deleteMessage(int $id): void
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        if ($this->selectedMessageId === $id) {
            $this->selectedMessageId = null;
        }

        Flux::toast(variant: 'success', text: __('Pesan kontak berhasil dihapus.'));
    }

    #[Computed]
    public function metrics(): array
    {
        return [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::where('status', ContactMessageStatus::New)->count(),
            'read' => ContactMessage::where('status', ContactMessageStatus::Read)->count(),
            'replied' => ContactMessage::where('status', ContactMessageStatus::Replied)->count(),
        ];
    }

    #[Computed]
    public function selectedMessage(): ?ContactMessage
    {
        if (! $this->selectedMessageId) {
            return null;
        }

        return ContactMessage::find($this->selectedMessageId);
    }

    public function getCleanPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }

    #[Computed]
    public function messages()
    {
        return ContactMessage::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('message', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status !== '', function ($q) {
                $q->where('status', $this->status);
            })
            ->latest()
            ->paginate(10);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-display">{{ __('Pesan Kontak Masuk') }}</flux:heading>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Daftar permohonan kerja sama kemitraan, grosir, dan pertanyaan dari formulir kontak publik PT Mustika Makmur Abadi.') }}
            </p>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <p class="font-display text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->metrics['total'] }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Pesan') }}</p>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 shadow-xs dark:border-amber-900/60 dark:bg-amber-950/30">
            <div class="flex items-center justify-between">
                <p class="font-display text-2xl font-bold text-amber-800 dark:text-amber-200">{{ $this->metrics['new'] }}</p>
                @if ($this->metrics['new'] > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-200/80 px-2 py-0.5 text-[10px] font-bold text-amber-900 dark:bg-amber-900 dark:text-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                        {{ __('Perlu Ditanggapi') }}
                    </span>
                @endif
            </div>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-300">{{ __('Pesan Baru (Unread)') }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <p class="font-display text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $this->metrics['read'] }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Sudah Dibaca') }}</p>
        </div>

        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 shadow-xs dark:border-emerald-900/60 dark:bg-emerald-950/30">
            <p class="font-display text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $this->metrics['replied'] }}</p>
            <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-400">{{ __('Sudah Dibalas') }}</p>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-xs sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-1 items-center gap-3">
            <div class="relative w-full max-w-sm">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Cari nama, email, no HP, pesan...') }}"
                    icon="magnifying-glass"
                />
            </div>

            <div class="w-44">
                <flux:select wire:model.live="status" placeholder="{{ __('Semua Status') }}">
                    <flux:select.option value="">{{ __('Semua Status') }}</flux:select.option>
                    <flux:select.option value="new">{{ __('Baru') }}</flux:select.option>
                    <flux:select.option value="read">{{ __('Sudah Dibaca') }}</flux:select.option>
                    <flux:select.option value="replied">{{ __('Sudah Dibalas') }}</flux:select.option>
                </flux:select>
            </div>
        </div>

        @if ($search || $status !== '')
            <flux:button size="sm" variant="subtle" wire:click="resetFilters">
                {{ __('Reset Filter') }}
            </flux:button>
        @endif
    </div>

    {{-- Messages Table --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/60 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3.5">{{ __('Tanggal') }}</th>
                        <th class="px-5 py-3.5">{{ __('Pengirim') }}</th>
                        <th class="px-5 py-3.5">{{ __('Kontak') }}</th>
                        <th class="px-5 py-3.5">{{ __('Pesan') }}</th>
                        <th class="px-5 py-3.5">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse ($this->messages as $msg)
                        <tr wire:key="msg-{{ $msg->id }}" class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 {{ $msg->status === \App\Enums\ContactMessageStatus::New ? 'bg-amber-50/20 font-medium' : '' }}">
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-zinc-500 dark:text-zinc-400">
                                <div>{{ $msg->created_at->format('d/m/Y') }}</div>
                                <div class="text-[11px] text-zinc-400">{{ $msg->created_at->format('H:i') }} ({{ $msg->created_at->diffForHumans() }})</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-zinc-900 dark:text-white">{{ $msg->name }}</div>
                                <div class="text-[11px] text-zinc-400">IP: {{ $msg->ip_address ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <div class="text-zinc-700 dark:text-zinc-300 font-mono">{{ $msg->email }}</div>
                                @if ($msg->phone)
                                    <div class="text-zinc-500 dark:text-zinc-400 font-mono mt-0.5">{{ $msg->phone }}</div>
                                @else
                                    <span class="text-[11px] text-zinc-400 italic">-</span>
                                @endif
                            </td>
                            <td class="max-w-md px-5 py-4 text-xs text-zinc-600 dark:text-zinc-300">
                                <p class="line-clamp-2">{{ $msg->message }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                @if ($msg->status === \App\Enums\ContactMessageStatus::New)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        {{ __('Baru') }}
                                    </span>
                                @elseif ($msg->status === \App\Enums\ContactMessageStatus::Read)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-950/60 dark:text-blue-300">
                                        {{ __('Sudah Dibaca') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        {{ __('Sudah Dibalas') }}
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" variant="filled" wire:click="openMessage({{ $msg->id }})">
                                        {{ __('Buka Pesan') }}
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Belum ada pesan kontak yang masuk.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->messages->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-800">
                {{ $this->messages->links() }}
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    @if ($this->selectedMessage)
        @php
            $item = $this->selectedMessage;
            $cleanPhone = $this->getCleanPhone($item->phone);
            $waUrl = $cleanPhone ? 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode('Halo ' . $item->name . ', terima kasih telah menghubungi PT Mustika Makmur Abadi. Menanggapi pesan Anda...') : null;
            $mailUrl = 'mailto:' . $item->email . '?subject=' . rawurlencode('Tanggapan PT Mustika Makmur Abadi - ' . $item->name);
        @endphp

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-xl rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800">
                    <div>
                        <h3 class="font-display text-base font-bold text-zinc-900 dark:text-white">
                            {{ __('Detail Pesan Kontak') }}
                        </h3>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            {{ __('Diterima:') }} {{ $item->created_at->format('d F Y, H:i') }} WIB ({{ $item->created_at->diffForHumans() }})
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeMessage"
                        class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4 text-xs">
                    {{-- Sender Info Box --}}
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40 space-y-2">
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                            <span class="text-zinc-500">{{ __('Nama Pengirim:') }}</span>
                            <span class="font-bold text-zinc-900 dark:text-white">{{ $item->name }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                            <span class="text-zinc-500">{{ __('Email:') }}</span>
                            <a href="{{ $mailUrl }}" class="font-mono text-brand-600 hover:underline dark:text-brand-400">{{ $item->email }}</a>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                            <span class="text-zinc-500">{{ __('Nomor Telepon / WA:') }}</span>
                            @if ($item->phone)
                                <span class="font-mono font-semibold text-zinc-800 dark:text-zinc-200">{{ $item->phone }}</span>
                            @else
                                <span class="italic text-zinc-400">{{ __('Tidak dicantumkan') }}</span>
                            @endif
                        </div>
                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1">
                            <span class="text-zinc-500">{{ __('Alamat IP Pengirim:') }}</span>
                            <span class="font-mono text-zinc-400">{{ $item->ip_address ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Message Body Box --}}
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            {{ __('Isi Pesan:') }}
                        </label>
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 text-xs leading-relaxed text-zinc-800 whitespace-pre-line dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                            {{ $item->message }}
                        </div>
                    </div>

                    {{-- Status Switcher --}}
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            {{ __('Ubah Status Pesan:') }}
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                wire:click="updateStatus({{ $item->id }}, 'new')"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $item->status === \App\Enums\ContactMessageStatus::New ? 'bg-amber-500 text-white shadow-xs' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                            >
                                {{ __('Tandai Baru (Unread)') }}
                            </button>
                            <button
                                type="button"
                                wire:click="updateStatus({{ $item->id }}, 'read')"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $item->status === \App\Enums\ContactMessageStatus::Read ? 'bg-blue-600 text-white shadow-xs' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                            >
                                {{ __('Tandai Sudah Dibaca') }}
                            </button>
                            <button
                                type="button"
                                wire:click="updateStatus({{ $item->id }}, 'replied')"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $item->status === \App\Enums\ContactMessageStatus::Replied ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                            >
                                {{ __('Tandai Sudah Dibalas') }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer with Direct Actions --}}
                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                    <button
                        type="button"
                        wire:click="deleteMessage({{ $item->id }})"
                        wire:confirm="{{ __('Apakah Anda yakin ingin menghapus pesan kontak ini?') }}"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400"
                    >
                        {{ __('Hapus Pesan') }}
                    </button>

                    <div class="flex items-center gap-2">
                        @if ($waUrl)
                            <a
                                href="{{ $waUrl }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-green-600 px-3.5 py-2 text-xs font-bold text-white transition hover:bg-green-700 shadow-xs"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.12-2.9-6.99A9.82 9.82 0 0 0 12.04 2zm5.8 14.07c-.24.68-1.4 1.3-1.93 1.36-.5.06-1 .27-3.36-.7-2.84-1.17-4.66-4.05-4.8-4.24-.14-.19-1.15-1.53-1.15-2.92s.72-2.06.98-2.35c.26-.29.56-.36.75-.36h.54c.17 0 .4-.03.62.48.24.56.8 1.95.87 2.09.07.14.11.3.02.49-.09.19-.14.3-.28.46-.14.16-.29.36-.42.48-.14.13-.29.28-.12.55.17.28.75 1.24 1.61 2.01 1.11 1 2.04 1.31 2.32 1.46.28.14.44.12.6-.07.17-.19.72-.84.91-1.13.19-.28.38-.23.63-.14.26.09 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg>
                                {{ __('Balas via WhatsApp') }}
                            </a>
                        @endif

                        <a
                            href="{{ $mailUrl }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white transition hover:bg-brand-700 shadow-xs"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                            {{ __('Balas Email') }}
                        </a>

                        <flux:button size="sm" variant="subtle" wire:click="closeMessage">
                            {{ __('Tutup') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

