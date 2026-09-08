<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentDownload;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Tampilkan daftar LOA yang diunduh
     */
    public function listLoas(Request $request)
    {
        // Ambil data LOA dari tabel intern_loas (database baru)
        $loas = \App\Models\InternLoa::with('intern.user')
            ->latest('created_at')
            ->paginate(10);

        // Transform data agar kompatibel dengan view yang ada
        $loas->getCollection()->transform(function ($loa) {
            $files = glob(storage_path("app/public/documents/loa/LOA-{$loa->intern_id}-*.pdf"));
            
            $loa->user = $loa->intern->user ?? null;
            $loa->downloaded_at = $loa->created_at;
            
            if (!empty($files)) {
                $loa->file_path = 'documents/loa/' . basename(end($files));
                $loa->status = 'success';
            } else {
                $loa->file_path = null;
                $loa->status = 'failed';
            }
            return $loa;
        });

        return view('admin.documents.loas', compact('loas'));
    }

    /**
     * Tampilkan daftar SKL yang diunduh
     */
    public function listSkls(Request $request)
    {
        // Ambil data pemagang yang sudah selesai (completed) untuk mencari SKL
        $skls = \App\Models\InternshipRegistration::where('internship_status', 'completed')
            ->with('user')
            ->latest('updated_at')
            ->paginate(10);

        $skls->getCollection()->transform(function ($reg) {
            $safeName = preg_replace('/[^a-z0-9\-_]+/i', '_', $reg->fullname);
            $files = glob(storage_path("app/public/documents/skl/SKL_{$safeName}_*.pdf"));
            
            $skl = new \stdClass();
            $skl->user = $reg->user;
            $skl->downloaded_at = $reg->updated_at;
            $skl->file_url = null;
            
            if (!empty($files)) {
                $skl->file_path = 'documents/skl/' . basename(end($files));
                $skl->status = 'success';
            } else {
                $skl->file_path = null;
                $skl->status = 'failed';
            }
            return $skl;
        });

        return view('admin.documents.skls', compact('skls'));
    }
}
