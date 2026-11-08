<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactMessageStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_column(ContactMessageStatus::cases(), 'value'))],
        ]);
        $messages = ContactMessage::query()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.messages.index', [
            'messages' => $messages,
            'filters' => $filters,
            'statuses' => ContactMessageStatus::cases(),
            'metrics' => [
                'total' => ContactMessage::count(),
                'new' => ContactMessage::where('status', ContactMessageStatus::New)->count(),
                'read' => ContactMessage::where('status', ContactMessageStatus::Read)->count(),
                'replied' => ContactMessage::where('status', ContactMessageStatus::Replied)->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, ContactMessage $message): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::enum(ContactMessageStatus::class)]]);
        $message->update(['status' => $validated['status']]);

        return back()->with('status', __('Status pesan diperbarui.'));
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return back()->with('status', __('Pesan kontak berhasil dihapus.'));
    }
}
