<?php

use App\Enums\SyncStatus;
use App\Jobs\SyncShopeeLogisticsJob;
use App\Jobs\SyncShopeeOrdersJob;
use App\Jobs\SyncShopeeReturnsJob;
use App\Models\SyncLog;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Status Sinkronisasi')] class extends Component
{
    use WithPagination;

    public function syncNow(): void
    {
        SyncShopeeOrdersJob::dispatchSync();
        SyncShopeeLogisticsJob::dispatchSync();
        SyncShopeeReturnsJob::dispatchSync();

        unset($this->logs);

        Flux::toast(variant: 'success', text: __('Sinkronisasi Shopee selesai dijalankan.'));
    }

    #[Computed]
    public function mode(): string
    {
        return config('shopee.mode');
    }

    #[Computed]
    public function logs()
    {
        return SyncLog::query()->latest('created_at')->paginate(15);
    }
}; ?>

<div class="flex w-full flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-display">{{ __('Status Sinkronisasi Shopee') }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Mode saat ini:') }}
                <span class="font-semibold {{ $this->mode === 'live' ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                    {{ $this->mode === 'live' ? __('Live (Shopee Asli)') : __('Fake (Data Simulasi)') }}
                </span>
            </p>
        </div>
        <flux:button variant="primary" wire:click="syncNow" wire:loading.attr="disabled">
            {{ __('Sinkronkan Sekarang') }}
        </flux:button>
    </div>

    @if ($this->mode !== 'live')
        <p class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:bg-amber-950 dark:text-amber-400">
            {{ __('Sistem masih berjalan dengan data simulasi karena akun Shopee Partner/ISV klien belum tersedia. Pesanan yang tersinkron di bawah ini adalah data contoh, bukan pesanan Shopee sungguhan.') }}
        </p>
    @endif

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Waktu') }}</th>
                    <th class="px-4 py-3">{{ __('Jenis') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Keterangan') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->logs as $log)
                    <tr wire:key="sync-log-{{ $log->id }}">
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $log->created_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-zinc-900 dark:text-white">{{ $log->job_type }}</td>
                        <td class="px-4 py-3">
                            @if ($log->status === SyncStatus::Success)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-400">{{ __('Berhasil') }}</span>
                            @else
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-400">{{ __('Gagal') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $log->message }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('Belum ada riwayat sinkronisasi. Klik "Sinkronkan Sekarang" untuk memulai.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $this->logs->links() }}</div>
</div>
