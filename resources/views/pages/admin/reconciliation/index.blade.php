<?php

use App\Enums\ReconciliationStatus;
use App\Enums\WarehouseStatus;
use App\Jobs\RunReconciliationJob;
use App\Models\Order;
use App\Models\ReconciliationRecord;
use App\Models\WarehouseProcessLog;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Rekonsiliasi')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $period = '';

    #[Url]
    public string $matchStatus = '';

    #[Url]
    public string $marketplaceStatus = '';

    public ?int $investigatingRecordId = null;

    public ?int $editingNoteId = null;

    public string $noteText = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPeriod(): void
    {
        $this->resetPage();
    }

    public function updatedMatchStatus(): void
    {
        $this->resetPage();
    }

    public function updatedMarketplaceStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->period = '';
        $this->matchStatus = '';
        $this->marketplaceStatus = '';
        $this->resetPage();
    }

    public function runReconciliation(): void
    {
        RunReconciliationJob::dispatchSync();

        unset($this->records, $this->summary);

        Flux::toast(variant: 'success', text: __('Rekonsiliasi selesai dijalankan untuk seluruh pesanan.'));
    }

    public function openInvestigation(int $recordId): void
    {
        $this->investigatingRecordId = $recordId;
        $record = ReconciliationRecord::find($recordId);
        $this->noteText = $record?->investigation_note ?? '';
    }

    public function closeInvestigation(): void
    {
        $this->investigatingRecordId = null;
        $this->noteText = '';
    }

    public function applyNoteTemplate(string $text): void
    {
        $this->noteText = $text;
    }

    public function editNote(int $recordId): void
    {
        $record = ReconciliationRecord::findOrFail($recordId);
        $this->editingNoteId = $recordId;
        $this->noteText = $record->investigation_note ?? '';
    }

    public function saveNote(): void
    {
        $this->validate(['noteText' => ['nullable', 'string', 'max:2000']]);

        $recordId = $this->investigatingRecordId ?? $this->editingNoteId;
        abort_unless($recordId, 400);

        $record = ReconciliationRecord::findOrFail($recordId);
        $record->update([
            'investigation_note' => $this->noteText,
            'checked_by' => Auth::id(),
            'checked_at' => now(),
        ]);

        $this->editingNoteId = null;

        Flux::toast(variant: 'success', text: __('Catatan investigasi berhasil disimpan.'));
    }

    public function cancelNote(): void
    {
        $this->editingNoteId = null;
        $this->noteText = '';
    }

    public function resolveWarehouseStatus(int $recordId, string $statusValue): void
    {
        $record = ReconciliationRecord::with('order')->findOrFail($recordId);
        $order = $record->order;

        abort_unless($order, 404);

        $targetStatus = WarehouseStatus::tryFrom($statusValue);
        abort_unless($targetStatus, 400);

        $oldStatus = $order->warehouse_status;
        $order->update(['warehouse_status' => $targetStatus->value]);

        WarehouseProcessLog::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => $oldStatus?->value,
            'to_status' => $targetStatus->value,
            'note' => 'Penyelarasan status melalui modul Rekonsiliasi oleh ' . Auth::user()->name,
            'ip_address' => request()->ip(),
        ]);

        // Re-evaluate reconciliation status
        $shipped = $targetStatus === WarehouseStatus::Dikirim;
        $newMatch = ReconciliationStatus::BelumDicek;

        if ($order->marketplace_status === 'CANCELLED') {
            $newMatch = $shipped ? ReconciliationStatus::Selisih : ReconciliationStatus::Sesuai;
        } elseif (in_array($order->marketplace_status, ['COMPLETED', 'SHIPPED'], true)) {
            $newMatch = $shipped ? ReconciliationStatus::Sesuai : ReconciliationStatus::Selisih;
        }

        $record->update([
            'warehouse_status_snapshot' => $targetStatus->value,
            'match_status' => $newMatch->value,
            'checked_by' => Auth::id(),
            'checked_at' => now(),
        ]);

        unset($this->records, $this->summary, $this->investigatingRecord);

        Flux::toast(variant: 'success', text: __('Status gudang berhasil diperbarui dan status rekonsiliasi telah disesuaikan.'));
    }

    #[Computed]
    public function investigatingRecord(): ?ReconciliationRecord
    {
        if (! $this->investigatingRecordId) {
            return null;
        }

        return ReconciliationRecord::with([
            'order.items',
            'order.shipments',
            'order.returns',
            'order.processLogs.user',
            'checkedBy',
        ])->find($this->investigatingRecordId);
    }

    private function baseQuery()
    {
        return ReconciliationRecord::query()
            ->when($this->period, function ($q) {
                $q->whereYear('period', substr($this->period, 0, 4))
                    ->whereMonth('period', substr($this->period, 5, 2));
            })
            ->when($this->search, function ($q) {
                $q->whereHas('order', function ($sub) {
                    $sub->where('shopee_order_sn', 'like', '%' . $this->search . '%')
                        ->orWhere('buyer_username', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->marketplaceStatus, fn ($q) => $q->where('marketplace_status_snapshot', $this->marketplaceStatus));
    }

    #[Computed]
    public function summary(): array
    {
        $total = (clone $this->baseQuery())->count();
        $sesuai = (clone $this->baseQuery())->where('match_status', ReconciliationStatus::Sesuai->value)->count();
        $selisih = (clone $this->baseQuery())->where('match_status', ReconciliationStatus::Selisih->value)->count();
        $belumDicek = (clone $this->baseQuery())->where('match_status', ReconciliationStatus::BelumDicek->value)->count();

        // Calculate monetary discrepancy value
        $selisihAmount = Order::whereHas('reconciliationRecord', function ($q) {
            $q->where('match_status', ReconciliationStatus::Selisih->value);
        })->sum('total_amount');

        $accuracy = $total > 0 ? round(($sesuai / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'sesuai' => $sesuai,
            'selisih' => $selisih,
            'belum_dicek' => $belumDicek,
            'accuracy' => $accuracy,
            'selisih_amount' => $selisihAmount,
        ];
    }

    #[Computed]
    public function records()
    {
        return $this->baseQuery()
            ->when($this->matchStatus, fn ($q) => $q->where('match_status', $this->matchStatus))
            ->with(['order.items', 'order.shipments', 'checkedBy'])
            ->orderByDesc('period')
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function getDiscrepancyReason(?ReconciliationRecord $record): array
    {
        if (! $record) {
            return ['type' => 'neutral', 'message' => '-'];
        }

        $mStatus = $record->marketplace_status_snapshot;
        $wStatus = $record->warehouse_status_snapshot;

        if ($record->match_status === ReconciliationStatus::Sesuai) {
            return [
                'type' => 'success',
                'title' => 'Data Selaras',
                'message' => 'Status pesanan di Shopee dan penanganan di gudang telah sesuai.',
            ];
        }

        if ($record->match_status === ReconciliationStatus::Selisih) {
            if ($mStatus === 'CANCELLED' && $wStatus === WarehouseStatus::Dikirim) {
                return [
                    'type' => 'danger',
                    'title' => 'Potensi Kerugian: Barang Terkirim pada Order Batal',
                    'message' => 'Pesanan telah dibatalkan di Shopee, namun gudang mencatat barang telah dikirim. Segera hubungi ekspedisi atau pembeli untuk penarikan barang.',
                ];
            }

            if (in_array($mStatus, ['COMPLETED', 'SHIPPED'], true) && (! $wStatus || $wStatus !== WarehouseStatus::Dikirim)) {
                return [
                    'type' => 'warning',
                    'title' => 'Status Gudang Belum Diperbarui',
                    'message' => 'Shopee mencatat pesanan sudah ' . $mStatus . ', namun sistem gudang masih berstatus "' . ($wStatus?->label() ?? 'Belum Diproses') . '". Kemungkinan barang sudah diserahkan ke kurir tanpa update status.',
                ];
            }

            return [
                'type' => 'warning',
                'title' => 'Ketidaksesuaian Status',
                'message' => 'Terdapat perbedaan status antara data Shopee (' . $mStatus . ') dan gudang (' . ($wStatus?->label() ?? 'Belum Diproses') . ').',
            ];
        }

        return [
            'type' => 'info',
            'title' => 'Dalam Proses Normal',
            'message' => 'Pesanan masih berjalan aktif di Shopee (' . $mStatus . ') dan menunggu penyelesaian pengiriman gudang.',
        ];
    }
}; ?>

<div class="flex w-full flex-col gap-5">
    {{-- Header Section --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-display">{{ __('Rekonsiliasi Marketplace & Gudang') }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Pemeriksaan silang otomatis antara data pesanan Shopee dengan realisasi fisik di gudang PT Mustika Makmur Abadi.') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('admin.reconciliation.export', ['period' => $period, 'matchStatus' => $matchStatus, 'search' => $search])">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ __('Export Excel') }}
            </flux:button>
            <flux:button variant="primary" wire:click="runReconciliation" wire:loading.attr="disabled">
                <svg wire:loading.remove wire:target="runReconciliation" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <svg wire:loading wire:target="runReconciliation" class="h-4 w-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span wire:loading.remove wire:target="runReconciliation">{{ __('Jalankan Rekonsiliasi') }}</span>
                <span wire:loading wire:target="runReconciliation">{{ __('Menganalisis...') }}</span>
            </flux:button>
        </div>
    </div>

    {{-- Metrics Dashboard --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/60 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/40">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">{{ __('Sesuai (Match)') }}</p>
                <span class="rounded-full bg-emerald-200/60 px-2 py-0.5 text-[11px] font-bold text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">
                    {{ $this->summary['accuracy'] }}%
                </span>
            </div>
            <p class="mt-1 font-display text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $this->summary['sesuai'] }}</p>
            <p class="mt-1 text-xs text-emerald-700/80 dark:text-emerald-400">{{ __('Data Shopee & gudang sinkron') }}</p>
        </div>

        <div class="rounded-xl border border-rose-200/80 bg-rose-50/60 p-4 dark:border-rose-900/50 dark:bg-rose-950/40">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-800 dark:text-rose-300">{{ __('Selisih (Discrepancy)') }}</p>
                @if ($this->summary['selisih'] > 0)
                    <span class="rounded-full bg-rose-200/60 px-2 py-0.5 text-[11px] font-bold text-rose-800 dark:bg-rose-900 dark:text-rose-300">
                        {{ __('Perlu Cek') }}
                    </span>
                @endif
            </div>
            <p class="mt-1 font-display text-2xl font-bold text-rose-700 dark:text-rose-300">{{ $this->summary['selisih'] }}</p>
            <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">
                {{ __('Nilai: Rp :amount', ['amount' => number_format($this->summary['selisih_amount'], 0, ',', '.')]) }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Belum Dicek / Proses') }}</p>
            <p class="mt-1 font-display text-2xl font-bold text-zinc-700 dark:text-zinc-200">{{ $this->summary['belum_dicek'] }}</p>
            <p class="mt-1 text-xs text-zinc-400">{{ __('Order aktif dalam berjalan') }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Rekonsiliasi') }}</p>
            <p class="mt-1 font-display text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->summary['total'] }}</p>
            <p class="mt-1 text-xs text-zinc-400">{{ __('Total pesanan dianalisis') }}</p>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="flex flex-col gap-3 rounded-xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3">
            {{-- Search by Order SN or Buyer --}}
            <div class="w-full sm:max-w-xs">
                <flux:input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Cari Order SN atau Pembeli...') }}"
                />
            </div>

            {{-- Month Period Filter --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500">{{ __('Periode:') }}</span>
                <flux:input type="month" wire:model.live="period" class="max-w-[170px]" />
            </div>

            {{-- Marketplace Status Filter --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500">{{ __('Status Shopee:') }}</span>
                <select
                    wire:model.live="marketplaceStatus"
                    class="rounded-lg border border-zinc-200 bg-white py-1.5 px-3 text-xs text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="">{{ __('Semua Status') }}</option>
                    <option value="UNPAID">UNPAID</option>
                    <option value="READY_TO_SHIP">READY_TO_SHIP</option>
                    <option value="PROCESSED">PROCESSED</option>
                    <option value="SHIPPED">SHIPPED</option>
                    <option value="COMPLETED">COMPLETED</option>
                    <option value="CANCELLED">CANCELLED</option>
                </select>
            </div>
        </div>

        {{-- Match Status Pills --}}
        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500">{{ __('Hasil Analisis:') }}</span>
                <button
                    wire:click="$set('matchStatus', '')"
                    class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $matchStatus === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ __('Semua') }} ({{ $this->summary['total'] }})
                </button>
                <button
                    wire:click="$set('matchStatus', '{{ ReconciliationStatus::Sesuai->value }}')"
                    class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $matchStatus === ReconciliationStatus::Sesuai->value ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300' }}"
                >
                    {{ __('Sesuai') }} ({{ $this->summary['sesuai'] }})
                </button>
                <button
                    wire:click="$set('matchStatus', '{{ ReconciliationStatus::Selisih->value }}')"
                    class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $matchStatus === ReconciliationStatus::Selisih->value ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-300' }}"
                >
                    {{ __('Selisih') }} ({{ $this->summary['selisih'] }})
                </button>
                <button
                    wire:click="$set('matchStatus', '{{ ReconciliationStatus::BelumDicek->value }}')"
                    class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $matchStatus === ReconciliationStatus::BelumDicek->value ? 'bg-zinc-800 text-white shadow-xs' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ __('Belum Dicek') }} ({{ $this->summary['belum_dicek'] }})
                </button>
            </div>

            @if ($search || $period || $matchStatus || $marketplaceStatus)
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="text-xs font-semibold text-rose-600 hover:underline dark:text-rose-400"
                >
                    {{ __('Reset Semua Filter') }}
                </button>
            @endif
        </div>
    </div>

    {{-- Main Reconciliation Table --}}
    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Order SN & Pembeli') }}</th>
                    <th class="px-4 py-3">{{ __('Nilai Transaksi') }}</th>
                    <th class="px-4 py-3">{{ __('Status Marketplace') }}</th>
                    <th class="px-4 py-3">{{ __('Status Gudang') }}</th>
                    <th class="px-4 py-3">{{ __('Hasil Analisis') }}</th>
                    <th class="px-4 py-3">{{ __('Terakhir Dicek') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Aksi & Investigasi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->records as $record)
                    @php
                        $ord = $record->order;
                        $diag = $this->getDiscrepancyReason($record);
                    @endphp
                    <tr wire:key="reconciliation-row-{{ $record->id }}" class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-4 py-3 text-xs">
                            <span class="font-mono font-bold text-zinc-900 dark:text-white">{{ $ord?->shopee_order_sn }}</span>
                            <span class="block text-[11px] text-zinc-500 dark:text-zinc-400">{{ $ord?->buyer_username ?? 'Anonim' }}</span>
                        </td>

                        <td class="px-4 py-3 text-xs font-semibold text-zinc-900 dark:text-white whitespace-nowrap">
                            @if ($ord)
                                Rp {{ number_format($ord->total_amount, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $record->marketplace_status_snapshot }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            @if ($record->warehouse_status_snapshot)
                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                    {{ $record->warehouse_status_snapshot->label() }}
                                </span>
                            @else
                                <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ __('Belum Diproses') }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if ($record->match_status === ReconciliationStatus::Sesuai)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('Sesuai') }}
                                </span>
                            @elseif ($record->match_status === ReconciliationStatus::Selisih)
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    {{ __('Selisih') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ __('Belum Dicek') }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                            {{ $record->checked_at?->diffForHumans() ?? '-' }}
                            @if ($record->checkedBy)
                                <span class="block text-[11px] text-zinc-400">{{ __('oleh :name', ['name' => $record->checkedBy->name]) }}</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <flux:button size="sm" variant="primary" wire:click="openInvestigation({{ $record->id }})">
                                    {{ __('Detail & Komparasi') }}
                                </flux:button>
                                <flux:button size="sm" variant="subtle" wire:click="editNote({{ $record->id }})">
                                    {{ $record->investigation_note ? __('Catatan') : __('+ Catatan') }}
                                </flux:button>
                            </div>
                        </td>
                    </tr>

                    {{-- Note Row if exists --}}
                    @if ($record->investigation_note && $editingNoteId !== $record->id)
                        <tr class="bg-zinc-50/50 dark:bg-zinc-800/30">
                            <td colspan="7" class="px-4 py-2 text-xs text-zinc-600 dark:text-zinc-400">
                                <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ __('Catatan Investigasi:') }}</span> {{ $record->investigation_note }}
                            </td>
                        </tr>
                    @endif

                    {{-- Inline Note Form --}}
                    @if ($editingNoteId === $record->id)
                        <tr class="bg-zinc-50 dark:bg-zinc-800/60">
                            <td colspan="7" class="px-4 py-3">
                                <form wire:submit="saveNote" class="flex flex-col gap-2">
                                    <flux:textarea wire:model="noteText" rows="2" placeholder="{{ __('Tuliskan catatan investigasi rekonsiliasi...') }}" />
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" wire:click="cancelNote">{{ __('Batal') }}</flux:button>
                                        <flux:button size="sm" variant="primary" type="submit">{{ __('Simpan Catatan') }}</flux:button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                            <p class="text-sm font-medium">{{ __('Tidak ada data rekonsiliasi yang cocok dengan filter.') }}</p>
                            <p class="mt-1 text-xs">{{ __('Klik "Jalankan Rekonsiliasi" untuk menganalisis data pesanan terkini.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $this->records->links() }}</div>

    {{-- MODAL INVESTIGASI & DETAIL REKONSILIASI LENGKAP --}}
    @if ($this->investigatingRecord)
        @php
            $inv = $this->investigatingRecord;
            $invOrder = $inv->order;
            $invDiag = $this->getDiscrepancyReason($inv);
        @endphp
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="investigation-title"
            x-data
            @keydown.window.escape="$wire.closeInvestigation()"
        >
            <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-xs transition-opacity" wire:click="closeInvestigation"></div>

            <div class="relative max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Header --}}
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-100 bg-white/95 px-6 py-4 backdrop-blur-xs dark:border-zinc-800 dark:bg-zinc-900/95">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-mono font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                Order SN: {{ $invOrder?->shopee_order_sn }}
                            </span>
                            @if ($inv->match_status === ReconciliationStatus::Sesuai)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    {{ __('Status: Sesuai') }}
                                </span>
                            @elseif ($inv->match_status === ReconciliationStatus::Selisih)
                                <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                    {{ __('Status: Selisih') }}
                                </span>
                            @else
                                <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ __('Status: Belum Dicek') }}
                                </span>
                            @endif
                        </div>
                        <h3 id="investigation-title" class="mt-1 font-display text-lg font-bold text-zinc-900 dark:text-white">
                            {{ __('Detail Komparasi & Investigasi Rekonsiliasi') }}
                        </h3>
                    </div>

                    <button
                        type="button"
                        wire:click="closeInvestigation"
                        class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                        aria-label="Tutup"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                {{-- Body Content --}}
                <div class="p-6 space-y-6">
                    {{-- Automated Discrepancy Diagnostics Alert --}}
                    <div class="rounded-xl p-4 text-xs sm:text-sm {{ $invDiag['type'] === 'success' ? 'border border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300' : ($invDiag['type'] === 'danger' ? 'border border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900 dark:bg-rose-950/60 dark:text-rose-300' : 'border border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/60 dark:text-amber-300') }}">
                        <p class="font-bold">{{ $invDiag['title'] }}</p>
                        <p class="mt-1 leading-relaxed">{{ $invDiag['message'] }}</p>
                    </div>

                    {{-- Side-by-Side Comparison Columns --}}
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- Left Column: Shopee Marketplace Data --}}
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-3 dark:border-zinc-700">
                                <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                                <h4 class="font-display text-sm font-bold text-zinc-900 dark:text-white">{{ __('Data Marketplace (Shopee)') }}</h4>
                            </div>

                            <div class="mt-3 space-y-2.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">{{ __('Status Pesanan:') }}</span>
                                    <span class="font-bold text-zinc-900 dark:text-white">{{ $inv->marketplace_status_snapshot }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">{{ __('Total Nilai:') }}</span>
                                    <span class="font-bold text-zinc-900 dark:text-white">Rp {{ number_format($invOrder?->total_amount ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">{{ __('Pembeli:') }}</span>
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $invOrder?->buyer_username ?? 'Anonim' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">{{ __('Tanggal Pesan:') }}</span>
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $invOrder?->order_placed_at?->translatedFormat('d M Y, H:i') ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">{{ __('Status Logistik Shopee:') }}</span>
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $invOrder?->shipments->first()?->shopee_logistics_status ?? 'Tidak ada data' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Warehouse Physical Data --}}
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/50 p-5 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <div class="flex items-center gap-2 border-b border-zinc-200/80 pb-3 dark:border-zinc-700">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                <h4 class="font-display text-sm font-bold text-zinc-900 dark:text-white">{{ __('Data Gudang (PT Mustika)') }}</h4>
                            </div>

                            <div class="mt-3 space-y-2.5 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-500">{{ __('Status Fisik Gudang:') }}</span>
                                    <span class="font-bold text-zinc-900 dark:text-white">
                                        {{ $inv->warehouse_status_snapshot?->label() ?? __('Belum Diproses Gudang') }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">{{ __('Nomor Resi:') }}</span>
                                    <span class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $invOrder?->shipments->first()?->tracking_number ?? 'Belum ada' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">{{ __('Kurir / Ekspedisi:') }}</span>
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $invOrder?->shipments->first()?->carrier ?? 'Belum ditentukan' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">{{ __('Periode Rekonsiliasi:') }}</span>
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $inv->period?->format('F Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">{{ __('Terakhir Dicek:') }}</span>
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $inv->checked_at?->translatedFormat('d M Y, H:i') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Ordered Items Table --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            {{ __('Rincian Barang yang Dipesan:') }}
                        </h4>
                        <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-zinc-50 uppercase text-[10px] font-semibold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-4 py-2.5">{{ __('Nama Produk') }}</th>
                                        <th class="px-4 py-2.5">{{ __('SKU') }}</th>
                                        <th class="px-4 py-2.5 text-center">{{ __('Qty') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Harga Satuan') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Subtotal') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @forelse ($invOrder?->items ?? [] as $item)
                                        <tr>
                                            <td class="px-4 py-2.5 font-medium text-zinc-900 dark:text-white">{{ $item->item_name }}</td>
                                            <td class="px-4 py-2.5 font-mono text-zinc-500">{{ $item->sku ?? '-' }}</td>
                                            <td class="px-4 py-2.5 text-center font-bold text-zinc-900 dark:text-white">{{ $item->quantity }}</td>
                                            <td class="px-4 py-2.5 text-right text-zinc-600 dark:text-zinc-300">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2.5 text-right font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-3 text-center text-zinc-400">{{ __('Tidak ada detail item.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Quick Action: Resolve Warehouse Status --}}
                    @if ($inv->match_status === ReconciliationStatus::Selisih)
                        <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900 dark:bg-amber-950/40">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-200">
                                {{ __('Tindakan Cepat Penyelarasan Status Gudang:') }}
                            </h4>
                            <p class="mt-1 text-xs text-amber-800 dark:text-amber-300">
                                {{ __('Jika barang fisik telah dipastikan keluar atau telah diproses oleh staf gudang, Anda dapat menyelaraskan status gudang secara langsung di sini:') }}
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <flux:button size="sm" variant="primary" wire:click="resolveWarehouseStatus({{ $inv->id }}, 'dikirim')">
                                    {{ __('Tandai Sebagai "Dikirim" (Selesaikan Selisih)') }}
                                </flux:button>
                                <flux:button size="sm" wire:click="resolveWarehouseStatus({{ $inv->id }}, 'dikemas')">
                                    {{ __('Set Status "Dikemas"') }}
                                </flux:button>
                                <flux:button size="sm" wire:click="resolveWarehouseStatus({{ $inv->id }}, 'diterima')">
                                    {{ __('Set Status "Diterima"') }}
                                </flux:button>
                            </div>
                        </div>
                    @endif

                    {{-- Investigation Note Form & Quick Templates --}}
                    <div class="border-t border-zinc-100 pt-5 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                {{ __('Catatan Hasil Investigasi Admin:') }}
                            </h4>
                            @if ($inv->checkedBy)
                                <span class="text-xs text-zinc-400">
                                    {{ __('Pemeriksa: :name pada :time', ['name' => $inv->checkedBy->name, 'time' => $inv->checked_at?->translatedFormat('d M Y H:i') ?? '-']) }}
                                </span>
                            @endif
                        </div>

                        {{-- Quick Note Templates --}}
                        <div class="mt-2.5 flex flex-wrap gap-1.5">
                            <span class="text-[11px] text-zinc-400 py-1">{{ __('Template cepat:') }}</span>
                            <button
                                type="button"
                                wire:click="applyNoteTemplate('Barang telah terverifikasi terkirim via armada logistik sendiri.')"
                                class="rounded-md border border-zinc-200 bg-white px-2 py-1 text-[11px] text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                            >
                                {{ __('Terkirim armada sendiri') }}
                            </button>
                            <button
                                type="button"
                                wire:click="applyNoteTemplate('Pesanan dibatalkan pembeli, fisik barang tetap aman di gudang.')"
                                class="rounded-md border border-zinc-200 bg-white px-2 py-1 text-[11px] text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                            >
                                {{ __('Batal sebelum kirim') }}
                            </button>
                            <button
                                type="button"
                                wire:click="applyNoteTemplate('Menunggu konfirmasi penerimaan fisik dari pihak kurir rekanan.')"
                                class="rounded-md border border-zinc-200 bg-white px-2 py-1 text-[11px] text-zinc-600 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                            >
                                {{ __('Menunggu kurir') }}
                            </button>
                        </div>

                        <div class="mt-3">
                            <flux:textarea
                                wire:model="noteText"
                                rows="3"
                                placeholder="{{ __('Tuliskan hasil investigasi, kendala di lapangan, atau tindak lanjut tim gudang/marketplace...') }}"
                            />
                            <div class="mt-2 flex justify-end">
                                <flux:button size="sm" variant="primary" wire:click="saveNote">
                                    {{ __('Simpan Catatan Investigasi') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="border-t border-zinc-100 bg-zinc-50 px-6 py-3 text-right dark:border-zinc-800 dark:bg-zinc-800/50">
                    <flux:button size="sm" wire:click="closeInvestigation">
                        {{ __('Tutup') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
