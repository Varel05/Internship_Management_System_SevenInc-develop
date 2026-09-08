<?php

namespace App\Http\Controllers\Pemagang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('pemagang.settings', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'phone_number'    => 'nullable|string|max:20',
            'password'        => 'nullable|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Nama lengkap TIDAK boleh diubah melalui pengaturan
        $user->email = $validated['email'];
        
        $updateReg = false;
        
        if ($user->internshipRegistration) {
            if (isset($validated['phone_number'])) {
                $user->internshipRegistration->phone_number = $validated['phone_number'];
                $updateReg = true;
            }
            if ($request->hasFile('profile_picture')) {
                if ($user->internshipRegistration->profile_photo) {
                    Storage::disk('public')->delete($user->internshipRegistration->profile_photo);
                }
                $user->internshipRegistration->profile_photo = $request->file('profile_picture')
                    ->store('uploads', 'public');
                $updateReg = true;
            }
            
            if ($updateReg) {
                $user->internshipRegistration->save();
            }
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
