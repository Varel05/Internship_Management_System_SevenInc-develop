<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = DB::table('feedback')
            ->join('internship_registrations', 'feedback.intern_id', '=', 'internship_registrations.id')
            ->select('feedback.id', 'feedback.feedback', 'internship_registrations.fullname as name', 'feedback.created_at')
            ->orderByDesc('feedback.created_at')
            ->get()
            ->map(function ($row) {
                $row->created_at = $row->created_at
                    ? \Carbon\Carbon::parse($row->created_at)
                    : null;
                return $row;
            });

        return view('admin.feedback.index', compact('feedbacks'));
    }

    public function edit($id)
    {
        $feedback = DB::table('feedback')->where('id', $id)->first();
        return view('admin.feedback.edit', compact('feedback'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'feedback' => 'required|string|min:5|max:1000',
        ]);

        DB::table('feedback')->where('id', $id)->update([
            'feedback' => $request->feedback,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.feedback.index')->with('success', 'Feedback berhasil diperbarui!');
    }

    public function destroy($id)
    {
        DB::table('feedback')->where('id', $id)->delete();
        return redirect()->route('admin.feedback.index')->with('success', 'Feedback berhasil dihapus!');
    }



    public function submit(Request $request)
    {
        $request->validate([
            'feedback' => 'required|string|min:5|max:1000',
        ]);

        $user = Auth::user();

        // Cek status magang — hanya bisa submit setelah completed
        $reg = \App\Models\InternshipRegistration::where('user_id', $user->id)->latest()->first();
        if (!$reg || $reg->internship_status !== 'completed') {
            return back()->with('error', 'Feedback hanya bisa dikirim setelah masa magang selesai.');
        }

        // Cek apakah sudah pernah submit — 1 internship registration 1 kali
        if (DB::table('feedback')->where('intern_id', $reg->id)->exists()) {
            return back()->with('error', 'Kamu sudah pernah mengirim feedback untuk magang ini. Setiap periode magang hanya bisa mengirim 1 kali.');
        }

        DB::table('feedback')->insert([
            'intern_id'  => $reg->id,
            'feedback'   => $request->feedback,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Terima kasih atas umpan balik Anda! Feedback telah berhasil dikirim.');
    }
}
