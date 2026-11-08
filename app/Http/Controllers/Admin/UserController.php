<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.users.index', ['users' => User::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('pages.admin.users.form', [
            'user' => null,
            'isSelf' => false,
            'roles' => UserRole::cases(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('pages.admin.users.form', [
            'user' => $user,
            'isSelf' => $user->id === Auth::id(),
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->saveUser($request, new User);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        return $this->saveUser($request, $user);
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_if($user->id === Auth::id(), 403, __('Tidak dapat menonaktifkan akun sendiri.'));
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active ? __('Akun diaktifkan.') : __('Akun dinonaktifkan.'));
    }

    public function unlock(User $user): RedirectResponse
    {
        $user->forceFill(['failed_login_attempts' => 0, 'locked_until' => null])->save();

        return back()->with('status', __('Akun dibuka kembali.'));
    }

    private function saveUser(Request $request, User $user): RedirectResponse
    {
        $isSelf = $user->exists && $user->id === Auth::id();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['sometimes', 'boolean'],
            'password' => [$user->exists ? 'nullable' : 'required', 'confirmed', Password::default()],
        ];
        $validated = $request->validate($rules);
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! $isSelf) {
            $user->role = $validated['role'];
            $user->is_active = $request->boolean('is_active');
        }

        if (filled($validated['password'] ?? null)) {
            $user->password = $validated['password'];
        }

        if (! $user->exists) {
            $user->email_verified_at = now();
        }

        $user->save();

        return to_route('admin.users.index')->with('status', __('Pengguna disimpan.'));
    }
}
