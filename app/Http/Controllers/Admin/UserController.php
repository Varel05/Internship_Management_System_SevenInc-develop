<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InternshipRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Menampilkan semua pengguna
    public function index(Request $request)
    {
        $query = User::query()
            ->select('users.*', 'internship_registrations.fullname as name')
            ->leftJoin('internship_registrations', 'users.id', '=', 'internship_registrations.user_id');

        // filter per kolom
        if ($name = $request->get('name')) {
            $query->where('internship_registrations.fullname', 'like', "%$name%");
        }

        if ($email = $request->get('email')) {
            $query->where('users.email', 'like', "%$email%");
        }

        if ($role = $request->get('role')) {
            $query->where('users.role', $role);
        }

        // Filter berdasarkan status ban
        if ($status = $request->get('status')) {
            if ($status === 'banned') {
                $query->where('users.is_banned', true);
            } elseif ($status === 'active') {
                $query->where('users.is_banned', false);
            }
        }

        // sorting
        switch ($request->get('sort')) {
            case 'name_asc':   $query->orderBy('internship_registrations.fullname', 'asc'); break;
            case 'name_desc':  $query->orderBy('internship_registrations.fullname', 'desc'); break;
            case 'email_asc':  $query->orderBy('users.email', 'asc'); break;
            case 'email_desc': $query->orderBy('users.email', 'desc'); break;
            case 'role_asc':   $query->orderBy('users.role', 'asc'); break;
            case 'role_desc':  $query->orderBy('users.role', 'desc'); break;
            case 'status_asc': $query->orderBy('users.is_online', 'asc'); break;
            case 'status_desc':$query->orderBy('users.is_online', 'desc'); break;
            default:
                $query->orderBy('users.is_online', 'desc')->orderBy('internship_registrations.fullname', 'asc');
        }

        $users = $query->paginate(10)->appends($request->query());

        return view('admin.users.index', compact('users'));
    }

    // Menampilkan form tambah pengguna
    public function create()
    {
        return view('admin.users.create');
    }

    // Menyimpan pengguna baru
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('admin.dashboard.index')->with('error', 'Unauthorized access');
        }

        $username = $request->input('name') ?? $request->input('username');
        $request->merge([
            'name' => $username,
            'role' => 'admin',
        ]);

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:150', 'unique:users,name'],
            'email'                 => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'role'                  => ['nullable', 'string', 'in:admin'],
        ], [
            'name.required'         => 'Username wajib diisi.',
            'name.unique'           => 'Username sudah digunakan, silakan pilih username lain.',
            'name.max'              => 'Username maksimal 150 karakter.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah terdaftar.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'admin',
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Admin baru {$user->name} berhasil ditambahkan.");
    }

    // Menampilkan detail pengguna
    public function show($id)
    {
        $user = User::findOrFail($id);
        $internship = $user->internshipRegistration;
        return view('admin.users.show', compact('user', 'internship'));
    }

    // Mengedit data pengguna lain (hanya role)
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->user()->role !== 'admin') {
            return redirect()->route('admin.dashboard')->with('error', 'Unauthorized access');
        }

        $validated = $request->validate([
            'role'     => 'required|string|in:admin,user,pemagang',
            'fullname' => 'nullable|string|max:150',
        ]);

        $user->role = $validated['role'];
        $user->save();

        // Jika user punya data registrasi, update nama lengkapnya
        if (!empty($validated['fullname']) && $user->internshipRegistration) {
            $user->internshipRegistration->fullname = $validated['fullname'];
            $user->internshipRegistration->save();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    // ─── Edit Profil Sendiri (admin yang sedang login) ───────────────────────

    public function editProfile()
    {
        $user = auth()->user();
        return view('admin.profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $username = $request->input('username') ?? $request->input('fullname');
        $request->merge(['username' => $username]);

        $validated = $request->validate([
            'username'              => 'required|string|max:150|unique:users,name,' . $user->id,
            'email'                 => 'required|email|max:150|unique:users,email,' . $user->id,
            'current_password'      => 'nullable|string',
            'password'              => 'nullable|string|min:8|confirmed',
        ], [
            'username.required'     => 'Username wajib diisi.',
            'username.unique'       => 'Username sudah digunakan, silakan pilih username lain.',
            'username.max'          => 'Username maksimal 150 karakter.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.unique'          => 'Email sudah terdaftar.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        // Validasi password lama jika ingin ganti password
        if (!empty($validated['password'])) {
            if (empty($validated['current_password'])) {
                return back()
                    ->withErrors(['current_password' => 'Password saat ini wajib diisi untuk mengganti password.'])
                    ->withInput();
            }

            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                    ->withInput();
            }

            $user->password = Hash::make($validated['password']);
        }

        $user->name = $validated['username'];
        $user->email = $validated['email'];

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    // Menghapus data pengguna
    public function destroy($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
        }
        return redirect()->route('admin.users.index')->with('error', 'User not found.');
    }

    // Ban / nonaktifkan akun
    public function ban(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menonaktifkan akun admin.');
        }

        $validated = $request->validate([
            'ban_reason' => 'nullable|string|max:255',
        ]);

        $user->update([
            'is_banned'  => true,
            'banned_at'  => now(),
            'ban_reason' => $validated['ban_reason'] ?? 'Dinonaktifkan oleh admin.',
        ]);

        if ($user->is_online) {
            $user->update(['is_online' => false]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => "Akun {$user->name} berhasil dinonaktifkan."]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dinonaktifkan.");
    }

    // Unban / aktifkan kembali akun
    public function unban($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'is_banned'  => false,
            'banned_at'  => null,
            'ban_reason' => null,
        ]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true, 'message' => "Akun {$user->name} berhasil diaktifkan kembali."]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil diaktifkan kembali.");
    }
}
