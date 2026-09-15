<?php

use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\WarehouseProcessLog;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Proses Gudang')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function canUpdate(): bool
    {
        return Auth::user()->role === UserRole::AdminGudang;
    }

    public function markReceived(Order $order): void
    {
        abort_unless($this->canUpdate, 403);
        abort_if($order->warehouse_status !== null, 403);

        $this->recordTransition($order, null, WarehouseStatus::Diterima);
    }

    public function advance(Order $order): void
    {
        abort_unless($this->canUpdate, 403);

        $next = $order->warehouse_status?->next();

        abort_if($next === null, 403);

        $this->recordTransition($order, $order->warehouse_status, $next);
    }

    private function recordTransition(Order $order, ?WarehouseStatus $from, WarehouseStatus $to): void
    {
        $order->update(['warehouse_status' => $to->value]);

        WarehouseProcessLog::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'ip_address' => request()->ip(),
        ]);

        Flux::toast(variant: 'success', text: __('Status pesanan :sn diperbarui ke :status.', [
            'sn' => $order->shopee_order_sn,
            'status' => $to->label(),
        ]));
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->when($this->status, fn ($q) => $q->where('warehouse_status', $this->status))
            ->when($this->search, fn ($q) => $q->where(function ($sub) {
                $sub->where('shopee_order_sn', 'like', "%{$this->search}%")
                    ->orWhereHas('shipments', fn ($sq) => $sq->where('tracking_number', 'like', "%{$this->search}%"));
            }))
            ->with(['items', 'shipments'])
            ->latest('order_placed_at')
            ->paginate(10);
    }
}; ?>

<div class="flex w-full flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="xl">{{ __('Proses Gudang') }}</flux:heading>
        <flux:button :href="route('admin.warehouse.export', ['status' => $status, 'search' => $search])">
            {{ __('Export Excel') }}
        </flux:button>
    </div>

    <div class="flex flex-wrap gap-2">
        <button
            wire:click="$set('status', '')"
            class="rounded-full px-3 py-1.5 text-sm font-medium {{ $status === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'border border-zinc-300 text-zinc-600 dark:border-zinc-700 dark:text-zinc-400' }}"
        >
            {{ __('Semua') }}
        </button>
        @foreach (WarehouseStatus::cases() as $case)
            <button
                wire:click="$set('status', '{{ $case->value }}')"
                class="rounded-full px-3 py-1.5 text-sm font-medium {{ $status === $case->value ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'border border-zinc-300 text-zinc-600 dark:border-zinc-700 dark:text-zinc-400' }}"
            >
                {{ $case->label() }}
            </button>
        @endforeach
    </div>

    <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari order SN atau nomor resi...') }}" class="max-w-sm" />

    @unless ($this->canUpdate)
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Anda dapat melihat status pesanan, namun hanya Admin Gudang yang dapat memperbarui status.') }}</p>
    @endunless

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Order SN') }}</th>
                    <th class="px-4 py-3">{{ __('Resi') }}</th>
                    <th class="px-4 py-3">{{ __('Item') }}</th>
                    <th class="px-4 py-3">{{ __('Status Marketplace') }}</th>
                    <th class="px-4 py-3">{{ __('Status Gudang') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->orders as $order)
                    <tr wire:key="order-{{ $order->id }}">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $order->shopee_order_sn }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $order->shipments->first()?->tracking_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $order->items->count() }} item</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $order->marketplace_status }}</td>
                        <td class="px-4 py-3">
                            @if ($order->warehouse_status)
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-400">
                                    {{ $order->warehouse_status->label() }}
                                </span>
                            @else
                                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ __('Belum Diproses') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($this->canUpdate)
                                @if (! $order->warehouse_status)
                                    <flux:button size="sm" variant="primary" wire:click="markReceived({{ $order->id }})">
                                        {{ __('Tandai Diterima') }}
                                    </flux:button>
                                @elseif ($next = $order->warehouse_status->next())
                                    <flux:button size="sm" variant="primary" wire:click="advance({{ $order->id }})">
                                        {{ __('Lanjutkan ke :status', ['status' => $next->label()]) }}
                                    </flux:button>
                                @else
                                    <span class="text-xs text-zinc-400">{{ __('Selesai') }}</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('Tidak ada pesanan yang cocok.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $this->orders->links() }}</div>
</div>
