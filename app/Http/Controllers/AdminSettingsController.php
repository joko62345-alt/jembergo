<?php

namespace App\Http\Controllers;

use App\Models\AdminPariwisata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        $admin = AdminPariwisata::with('destinasi')->findOrFail(session('jg_user_id'));
        $destination = $admin->destinasi;

        return view('admin.settings', compact('admin', 'destination'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = AdminPariwisata::findOrFail(session('jg_user_id'));
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $admin->forceFill(['password' => Hash::make($data['new_password'])])->save();

        return back()->with('password_success', 'Password berhasil diperbarui.');
    }
}
