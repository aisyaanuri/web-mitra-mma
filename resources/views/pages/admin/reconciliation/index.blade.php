<?php

use App\Enums\ReconciliationStatus;
use App\Jobs\RunReconciliationJob;
use App\Models\ReconciliationRecord;
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
    public string $period = '';

    #[Url]
    public string $matchStatus = '';

    public ?int $editingNoteId = null;

    public string $noteText = '';

    public function updatedPeriod(): void
    {
        $this->resetPage();
    }

    public function updatedMatchStatus(): void
    {
        $this->resetPage();
    }

    public function runReconciliation(): void
    {
        RunReconciliationJob::dispatchSync();

        unset($this->records, $this->summary);

        Flux::toast(variant: 'success', text: __('Rekonsiliasi selesai dijalankan.'));
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

        $record = ReconciliationRecord::findOrFail($this->editingNoteId);
        $record->update([
            'investigation_note' => $this->noteText,
            'checked_by' => Auth::id(),
            'checked_at' => now(),
        ]);

        $this->editingNoteId = null;
        $this->noteText = '';

        Flux::toast(variant: 'success', text: __('Catatan investigasi disimpan.'));
    }

    public function cancelNote(): void
    {
        $this->editingNoteId = null;
        $this->noteText = '';
    }

    private function baseQuery()
    {
        return ReconciliationRecord::query()
            ->when($this->period, function ($q) {
                $q->whereYear('period', substr($this->period, 0, 4))
                    ->whereMonth('period', substr($this->period, 5, 2));
            });
    }

    #[Computed]
    public function summary(): array
    {
        return [
            'sesuai' => (clone $this->baseQuery())->where('match_status', ReconciliationStatus::Sesuai->value)->count(),
            'selisih' => (clone $this->baseQuery())->where('match_status', ReconciliationStatus::Selisih->value)->count(),
            'belum_dicek' => (clone $this->baseQuery())->where('match_status', ReconciliationStatus::BelumDicek->value)->count(),
        ];
    }

    #[Computed]
    public function records()
    {
        return $this->baseQuery()
            ->when($this->matchStatus, fn ($q) => $q->where('match_status', $this->matchStatus))
            ->with(['order', 'checkedBy'])
            ->orderByDesc('period')
            ->orderByDesc('id')
            ->paginate(10);
    }
}; ?>

<div class="flex w-full flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="xl">{{ __('Rekonsiliasi') }}</flux:heading>
        <div class="flex gap-2">
            <flux:button :href="route('admin.reconciliation.export', ['period' => $period, 'matchStatus' => $matchStatus])">
                {{ __('Export Excel') }}
            </flux:button>
            <flux:button variant="primary" wire:click="runReconciliation" wire:loading.attr="disabled">
                {{ __('Jalankan Rekonsiliasi Sekarang') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950">
            <p class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $this->summary['sesuai'] }}</p>
            <p class="text-sm text-green-700 dark:text-green-400">{{ __('Sesuai') }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950">
            <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $this->summary['selisih'] }}</p>
            <p class="text-sm text-red-700 dark:text-red-400">{{ __('Selisih') }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-2xl font-bold text-zinc-600 dark:text-zinc-300">{{ $this->summary['belum_dicek'] }}</p>
            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Belum Dicek') }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <flux:input type="month" wire:model.live="period" class="max-w-[180px]" />

        <div class="flex flex-wrap gap-2">
            <button
                wire:click="$set('matchStatus', '')"
                class="rounded-full px-3 py-1.5 text-sm font-medium {{ $matchStatus === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'border border-zinc-300 text-zinc-600 dark:border-zinc-700 dark:text-zinc-400' }}"
            >
                {{ __('Semua') }}
            </button>
            @foreach (ReconciliationStatus::cases() as $case)
                <button
                    wire:click="$set('matchStatus', '{{ $case->value }}')"
                    class="rounded-full px-3 py-1.5 text-sm font-medium {{ $matchStatus === $case->value ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'border border-zinc-300 text-zinc-600 dark:border-zinc-700 dark:text-zinc-400' }}"
                >
                    {{ $case->label() }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Order SN') }}</th>
                    <th class="px-4 py-3">{{ __('Status Marketplace') }}</th>
                    <th class="px-4 py-3">{{ __('Status Gudang') }}</th>
                    <th class="px-4 py-3">{{ __('Hasil') }}</th>
                    <th class="px-4 py-3">{{ __('Terakhir Dicek') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->records as $record)
                    <tr wire:key="reconciliation-{{ $record->id }}">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $record->order?->shopee_order_sn }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $record->marketplace_status_snapshot }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $record->warehouse_status_snapshot?->label() ?? __('Belum Diproses') }}</td>
                        <td class="px-4 py-3">
                            @if ($record->match_status === ReconciliationStatus::Sesuai)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-400">{{ __('Sesuai') }}</span>
                            @elseif ($record->match_status === ReconciliationStatus::Selisih)
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-950 dark:text-red-400">{{ __('Selisih') }}</span>
                            @else
                                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">{{ __('Belum Dicek') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">
                            {{ $record->checked_at?->diffForHumans() ?? '—' }}
                            @if ($record->checkedBy)
                                <span class="block text-xs text-zinc-400">{{ __('oleh :name', ['name' => $record->checkedBy->name]) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($record->match_status === ReconciliationStatus::Selisih)
                                <flux:button size="sm" wire:click="editNote({{ $record->id }})">
                                    {{ $record->investigation_note ? __('Edit Catatan') : __('Tambah Catatan') }}
                                </flux:button>
                            @endif
                        </td>
                    </tr>
                    @if ($record->investigation_note && $editingNoteId !== $record->id)
                        <tr>
                            <td colspan="6" class="bg-zinc-50 px-4 py-2 text-sm text-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-400">
                                <span class="font-medium">{{ __('Catatan:') }}</span> {{ $record->investigation_note }}
                            </td>
                        </tr>
                    @endif
                    @if ($editingNoteId === $record->id)
                        <tr>
                            <td colspan="6" class="bg-zinc-50 px-4 py-3 dark:bg-zinc-800/50">
                                <form wire:submit="saveNote" class="flex flex-col gap-2">
                                    <flux:textarea wire:model="noteText" rows="3" placeholder="{{ __('Catatan investigasi...') }}" />
                                    <div class="flex justify-end gap-2">
                                        <flux:button size="sm" wire:click="cancelNote">{{ __('Batal') }}</flux:button>
                                        <flux:button size="sm" variant="primary" type="submit">{{ __('Simpan') }}</flux:button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('Belum ada data rekonsiliasi. Klik "Jalankan Rekonsiliasi Sekarang" untuk memulai.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $this->records->links() }}</div>
</div>
