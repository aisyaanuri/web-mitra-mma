<?php

use App\Models\OrderReturn;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Manajemen Retur & Komplain')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $shopeeStatus = '';

    #[Url]
    public string $physicalStatus = '';

    public ?int $selectedReturnId = null;

    public string $verifyPhysicalStatus = 'diterima_baik';

    public string $verifyPhysicalNote = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedShopeeStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPhysicalStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->shopeeStatus = '';
        $this->physicalStatus = '';
        $this->resetPage();
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->search !== '' || $this->shopeeStatus !== '' || $this->physicalStatus !== '';
    }

    public function openVerifyModal(int $returnId): void
    {
        $this->selectedReturnId = $returnId;
        $return = OrderReturn::findOrFail($returnId);
        $this->verifyPhysicalStatus = $return->physical_status !== 'menunggu_barang' ? $return->physical_status : 'diterima_baik';
        $this->verifyPhysicalNote = $return->physical_note ?? '';
    }

    public function closeVerifyModal(): void
    {
        $this->selectedReturnId = null;
        $this->verifyPhysicalNote = '';
    }

    public function saveVerification(): void
    {
        if (! $this->selectedReturnId) {
            return;
        }

        $return = OrderReturn::findOrFail($this->selectedReturnId);

        $return->update([
            'physical_status' => $this->verifyPhysicalStatus,
            'physical_note' => $this->verifyPhysicalNote,
            'verified_by' => Auth::id(),
            'received_at' => now(),
        ]);

        $this->closeVerifyModal();

        Flux::toast(variant: 'success', text: __('Verifikasi fisik barang retur :sn berhasil disimpan.', [
            'sn' => $return->shopee_return_sn,
        ]));
    }

    #[Computed]
    public function metrics(): array
    {
        return [
            'total_returns' => OrderReturn::count(),
            'waiting_physical' => OrderReturn::where('physical_status', 'menunggu_barang')->count(),
            'received_count' => OrderReturn::whereIn('physical_status', ['diterima_baik', 'diterima_rusak'])->count(),
            'total_refund_amount' => OrderReturn::sum('refund_amount'),
        ];
    }

    #[Computed]
    public function selectedReturn(): ?OrderReturn
    {
        if (! $this->selectedReturnId) {
            return null;
        }

        return OrderReturn::with(['order.items', 'verifiedBy'])->find($this->selectedReturnId);
    }

    #[Computed]
    public function returns()
    {
        return OrderReturn::query()
            ->with(['order.items', 'verifiedBy'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('shopee_return_sn', 'like', "%{$this->search}%")
                        ->orWhere('reason', 'like', "%{$this->search}%")
                        ->orWhereHas('order', function ($oq) {
                            $oq->where('shopee_order_sn', 'like', "%{$this->search}%")
                                ->orWhere('buyer_username', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->shopeeStatus, fn ($q) => $q->where('status', $this->shopeeStatus))
            ->when($this->physicalStatus, fn ($q) => $q->where('physical_status', $this->physicalStatus))
            ->latest('id')
            ->paginate(10);
    }
}; ?>

<div class="flex w-full flex-col gap-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl" class="font-display">{{ __('Manajemen Retur dan Komplain') }}</flux:heading>
                <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                    {{ $this->metrics['total_returns'] }} {{ __('Pengajuan') }}
                </span>
            </div>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Pusat koordinasi Admin Marketplace dan Gudang untuk memverifikasi kedatangan fisik barang retur serta status refund Shopee.') }}
            </p>
        </div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Komplain Retur') }}</p>
            <p class="mt-2 text-2xl font-bold font-display text-zinc-900 dark:text-white">{{ $this->metrics['total_returns'] }}</p>
            <p class="mt-0.5 text-[11px] text-zinc-400">{{ __('Tercatat dari marketplace') }}</p>
        </div>

        <div class="rounded-xl border border-amber-200/80 bg-amber-50/60 p-4 shadow-xs dark:border-amber-900/60 dark:bg-amber-950/30">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">{{ __('Menunggu Barang Fisik') }}</p>
            <p class="mt-2 text-2xl font-bold font-display text-amber-900 dark:text-amber-200">{{ $this->metrics['waiting_physical'] }}</p>
            <p class="mt-0.5 text-[11px] text-amber-700/80 dark:text-amber-400/80">{{ __('Belum diterima staf gudang') }}</p>
        </div>

        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/60 p-4 shadow-xs dark:border-emerald-900/60 dark:bg-emerald-950/30">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">{{ __('Fisik Diterima Gudang') }}</p>
            <p class="mt-2 text-2xl font-bold font-display text-emerald-900 dark:text-emerald-200">{{ $this->metrics['received_count'] }}</p>
            <p class="mt-0.5 text-[11px] text-emerald-700/80 dark:text-emerald-400/80">{{ __('Sudah diverifikasi QC') }}</p>
        </div>

        <div class="rounded-xl border border-rose-200/80 bg-rose-50/60 p-4 shadow-xs dark:border-rose-900/60 dark:bg-rose-950/30">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">{{ __('Total Nilai Refund') }}</p>
            <p class="mt-2 text-xl font-bold font-display text-rose-900 dark:text-rose-200">
                Rp {{ number_format($this->metrics['total_refund_amount'], 0, ',', '.') }}
            </p>
            <p class="mt-0.5 text-[11px] text-rose-700/80 dark:text-rose-400/80">{{ __('Potensi dana kembali') }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-zinc-200 bg-zinc-50/60 p-3.5 dark:border-zinc-700 dark:bg-zinc-900/50">
        <div class="flex flex-1 flex-wrap items-center gap-3">
            <div class="w-full sm:w-64">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Cari No. Retur, Order SN, atau pembeli...') }}"
                    icon="magnifying-glass"
                />
            </div>

            <div class="w-full sm:w-48">
                <select
                    wire:model.live="shopeeStatus"
                    class="h-9 w-full rounded-lg border border-zinc-200 bg-white px-3 text-xs text-zinc-700 focus:border-brand-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="">{{ __('Semua Status Shopee') }}</option>
                    <option value="REQUESTED">REQUESTED (Diajukan)</option>
                    <option value="ACCEPTED">ACCEPTED (Disetujui)</option>
                    <option value="COMPLETED">COMPLETED (Selesai)</option>
                    <option value="REJECTED">REJECTED (Ditolak)</option>
                </select>
            </div>

            <div class="w-full sm:w-48">
                <select
                    wire:model.live="physicalStatus"
                    class="h-9 w-full rounded-lg border border-zinc-200 bg-white px-3 text-xs text-zinc-700 focus:border-brand-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="">{{ __('Semua Status Fisik Gudang') }}</option>
                    <option value="menunggu_barang">{{ __('Menunggu Barang') }}</option>
                    <option value="diterima_baik">{{ __('Diterima Baik') }}</option>
                    <option value="diterima_rusak">{{ __('Diterima Rusak') }}</option>
                    <option value="barang_hilang">{{ __('Barang Hilang') }}</option>
                </select>
            </div>

            @if ($this->hasActiveFilters)
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-400"
                >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    {{ __('Reset') }}
                </button>
            @endif
        </div>
    </div>

    {{-- Returns Table --}}
    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3">{{ __('No. Retur & Pesanan') }}</th>
                    <th class="px-4 py-3">{{ __('Pembeli') }}</th>
                    <th class="px-4 py-3">{{ __('Alasan Komplain') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Nominal Refund') }}</th>
                    <th class="px-4 py-3">{{ __('Status Shopee') }}</th>
                    <th class="px-4 py-3">{{ __('Verifikasi Fisik Gudang') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->returns as $ret)
                    <tr wire:key="return-{{ $ret->id }}" class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono font-bold text-xs text-brand-700 dark:text-brand-300">{{ $ret->shopee_return_sn }}</span>
                            <span class="block text-[11px] text-zinc-400 mt-0.5">Order: {{ $ret->order?->shopee_order_sn ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-700 dark:text-zinc-300">
                            {{ $ret->order?->buyer_username ?? 'Customer Shopee' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-400 max-w-xs truncate">
                            {{ $ret->reason ?? 'Tidak disebutkan' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-xs text-rose-600 dark:text-rose-400 whitespace-nowrap">
                            Rp {{ number_format($ret->refund_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $ret->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($ret->physical_status === 'diterima_baik')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('Diterima Baik') }}
                                </span>
                            @elseif ($ret->physical_status === 'diterima_rusak')
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    {{ __('Diterima Rusak') }}
                                </span>
                            @elseif ($ret->physical_status === 'barang_hilang')
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                    {{ __('Barang Hilang') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ __('Menunggu Barang') }}
                                </span>
                            @endif
                            @if ($ret->verifiedBy)
                                <p class="text-[10px] text-zinc-400 mt-0.5">
                                    {{ __('oleh :name', ['name' => $ret->verifiedBy->name]) }}
                                </p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button size="sm" wire:click="openVerifyModal({{ $ret->id }})">
                                {{ __('Verifikasi Fisik') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                            <p class="text-sm font-medium">{{ __('Tidak ada data pengajuan retur barang.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $this->returns->links() }}</div>

    {{-- Modal Verifikasi Fisik Retur --}}
    @if ($this->selectedReturn)
        @php
            $item = $this->selectedReturn;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800">
                    <div>
                        <h3 class="font-display text-base font-bold text-zinc-900 dark:text-white">
                            {{ __('Verifikasi Fisik Barang Retur') }}
                        </h3>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            {{ __('No. Retur:') }} <span class="font-mono font-bold">{{ $item->shopee_return_sn }}</span>
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeVerifyModal"
                        class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4 text-xs">
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/40 space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Order Asal:') }}</span>
                            <span class="font-mono font-bold">{{ $item->order?->shopee_order_sn }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Alasan Shopee:') }}</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $item->reason ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-500">{{ __('Nominal Refund:') }}</span>
                            <span class="font-bold text-rose-600">Rp {{ number_format($item->refund_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Status Fisik Radio / Select --}}
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            {{ __('Kondisi Fisik Paket yang Diterima:') }}
                        </label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-lg border p-2.5 cursor-pointer transition {{ $verifyPhysicalStatus === 'diterima_baik' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                                <input type="radio" wire:model.live="verifyPhysicalStatus" value="diterima_baik" class="text-emerald-600" />
                                <div>
                                    <p class="font-bold text-zinc-900 dark:text-white">{{ __('Diterima Baik') }}</p>
                                    <p class="text-[10px] text-zinc-400">{{ __('Kemasan utuh, layak restock') }}</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-2 rounded-lg border p-2.5 cursor-pointer transition {{ $verifyPhysicalStatus === 'diterima_rusak' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                                <input type="radio" wire:model.live="verifyPhysicalStatus" value="diterima_rusak" class="text-amber-600" />
                                <div>
                                    <p class="font-bold text-zinc-900 dark:text-white">{{ __('Diterima Rusak') }}</p>
                                    <p class="text-[10px] text-zinc-400">{{ __('Bocor / sobek / kadaluarsa') }}</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-2 rounded-lg border p-2.5 cursor-pointer transition {{ $verifyPhysicalStatus === 'menunggu_barang' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                                <input type="radio" wire:model.live="verifyPhysicalStatus" value="menunggu_barang" class="text-blue-600" />
                                <div>
                                    <p class="font-bold text-zinc-900 dark:text-white">{{ __('Menunggu Barang') }}</p>
                                    <p class="text-[10px] text-zinc-400">{{ __('Paket belum tiba') }}</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-2 rounded-lg border p-2.5 cursor-pointer transition {{ $verifyPhysicalStatus === 'barang_hilang' ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-950/20' : 'border-zinc-200 dark:border-zinc-700' }}">
                                <input type="radio" wire:model.live="verifyPhysicalStatus" value="barang_hilang" class="text-rose-600" />
                                <div>
                                    <p class="font-bold text-zinc-900 dark:text-white">{{ __('Barang Hilang') }}</p>
                                    <p class="text-[10px] text-zinc-400">{{ __('Klaim hilang ekspedisi') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Catatan Fisik --}}
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            {{ __('Catatan Kondisi Fisik Kemasan & Barang:') }}
                        </label>
                        <textarea
                            wire:model="verifyPhysicalNote"
                            rows="3"
                            placeholder="{{ __('Contoh: Paket diterima melalui kurir J&T, kemasan luar basah namun seal beras 5kg masih utuh...') }}"
                            class="w-full rounded-lg border border-zinc-200 p-2.5 text-xs text-zinc-900 placeholder:text-zinc-400 focus:border-brand-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        ></textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button wire:click="closeVerifyModal">
                            {{ __('Batal') }}
                        </flux:button>
                        <flux:button variant="primary" wire:click="saveVerification">
                            {{ __('Simpan Hasil Verifikasi') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
