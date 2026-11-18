<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('dashboard.profile', ['user' => $request->user()]);
    }

    public function edit(Request $request): View
    {
        return view('dashboard.edit-profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'domisili' => ['required', 'string', 'max:255', Rule::in(config('domisili.kecamatan'))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->domisili = $validated['domisili'];

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $request->session()->regenerate();

        return redirect()->route('dashboard.profile')
            ->with('status', 'Profil berhasil diperbarui.');
    }
}
