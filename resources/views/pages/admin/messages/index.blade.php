@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <flux:heading size="xl" class="font-display">{{ __('Pesan Kontak Masuk') }}</flux:heading>
    @if (session('status'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    <div class="grid gap-3 sm:grid-cols-4">
        @foreach ($metrics as $label => $count)
            <div class="rounded-xl border p-4"><p class="text-xs uppercase text-zinc-500">{{ __(ucfirst($label)) }}</p><p class="mt-1 text-2xl font-bold">{{ $count }}</p></div>
        @endforeach
    </div>
    <form method="GET" action="{{ route('admin.messages.index') }}" class="flex flex-wrap gap-2">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Cari nama, email, atau pesan') }}" class="min-w-60 rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <select name="status" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            <option value="">{{ __('Semua status') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Filter') }}</button>
        <a href="{{ route('admin.messages.index') }}" class="rounded-lg border px-4 py-2 text-sm">{{ __('Reset') }}</a>
    </form>
    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800"><tr><th class="p-3">{{ __('Pengirim') }}</th><th class="p-3">{{ __('Pesan') }}</th><th class="p-3">{{ __('Masuk') }}</th><th class="p-3">{{ __('Status') }}</th><th class="p-3">{{ __('Aksi') }}</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($messages as $message)
                    <tr>
                        <td class="p-3"><strong>{{ $message->name }}</strong><br><a class="text-xs text-blue-600" href="mailto:{{ $message->email }}">{{ $message->email }}</a><br><span class="text-xs text-zinc-500">{{ $message->phone }}</span></td>
                        <td class="max-w-xl whitespace-pre-wrap p-3">{{ $message->message }}</td>
                        <td class="p-3">{{ $message->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="p-3">{{ $message->status->label() }}</td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('admin.messages.update-status', $message) }}" class="flex gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded border p-1 text-xs">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}" @selected($message->status === $status)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button class="rounded border px-2 text-xs">{{ __('Simpan') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('{{ __('Hapus pesan ini?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded border border-red-300 px-2 py-1 text-xs text-red-600">{{ __('Hapus') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-zinc-500">{{ __('Belum ada pesan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $messages->links() }}
</div>
@endsection
