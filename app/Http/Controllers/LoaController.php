<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InternshipRegistration as IR;
use App\Models\InternLoa;
use App\Models\Brand;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoaController extends Controller
{
    public function getInternsByBrand(Request $request)
    {
        $brand = $request->query('brand');

        if (!$brand) {
            return response()->json(['interns' => []]);
        }

        $interns = IR::query()
            ->where('internship_status', IR::STATUS_ACCEPTED)
            ->where('brand', $brand)
            ->select('id', 'fullname', 'student_id', 'study_program', 'institution_name', 'start_date', 'end_date', 'phone_number')
            ->latest('id')
            ->get()
            ->map(fn ($r) => [
                'id'               => $r->id,
                'fullname'         => $r->fullname,
                'student_id'       => $r->student_id ?? '',
                'study_program'    => $r->study_program ?? '',
                'institution_name' => $r->institution_name ?? '',
                'start_date'       => $r->start_date ?? '',
                'end_date'         => $r->end_date ?? '',
                'phone_number'     => $r->phone_number ?? '',
            ]);

        return response()->json(['interns' => $interns]);
    }

    public function generateForBrand(Request $request)
    {
        $validated = $request->validate([
            'intern_ids'         => ['required', 'array', 'min:1'],
            'intern_ids.*'       => ['integer', 'exists:internship_registrations,id'],
        ]);

        $user = $request->user();
        $interns = IR::whereIn('id', $validated['intern_ids'])->get();

        if ($interns->isEmpty()) {
            return back()->with('error', 'Data pemagang tidak ditemukan.');
        }

        $brandCode = $interns->first()->brand;
        $brandData = Brand::whereRaw('LOWER(name) = ?', [strtolower(trim($brandCode))])
            ->orWhere('code', $brandCode)
            ->first();

        $companyName = $brandData->name ?? $brandCode ?? 'Seven Inc';
        $signatoryName = $brandData->signatory_name ?? 'Ari Setia Husbana';
        $signatoryPosition = $brandData->signatory_position ?? 'HRD';
        
        $logoData = $this->resolveBase64Image($brandData?->logo, 'images/logos/logo_seveninc.png');
        $stampData = $this->resolveBase64Image($brandData?->signature, 'images/signature/ttd_arisetiahusbana.png');

        $dir = 'documents/loa';
        $this->ensurePublicDir($dir);

        $generatedFiles = [];
        $errors         = [];

        $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        $romanMonth = $roman[(int)date('n')];
        $year = date('Y');

        $last = \App\Models\InternLoa::where('loa_number', 'LIKE', "%/LOA/%/{$romanMonth}/{$year}")
            ->orderByDesc('id')->first();
        $seq = 1;
        if ($last && preg_match('/^(\d{3})\/LOA\//', $last->loa_number, $m)) {
            $seq = (int)$m[1] + 1;
        }

        foreach ($interns as $intern) {
            try {
                $interest = $intern->internship_interest ?? '';
                
                // Fetch from database
                $dbDivision = \App\Models\Division::where('name', $interest)
                    ->orWhere('slug', Str::slug($interest, '-'))
                    ->first();
                    
                $division = $dbDivision?->code ?? 'UMUM';
                $brandStr = strtoupper($brandCode ?? 'SI');

                $seqStr = str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
                $loaNumber = "{$seqStr}/LOA/{$division}/SEVEN.{$brandStr}/{$romanMonth}/{$year}";
                $safeLoaNumber = str_replace('/', '-', $loaNumber);
                $seq++;

                $rows = $this->buildRows([$intern]);

                $pdf = Pdf::loadView('user.loa', [
                    'intern'          => $intern,
                    'loaNumber'       => $loaNumber,
                    'user'            => $user,
                    'loaSettings'     => (object)[
                        'header_text' => 'Dengan ini kami mengonfirmasi bahwa pendaftar di bawah ini telah diterima untuk mengikuti program magang.',
                        'footer_text' => 'Harap konfirmasi kehadiran Anda melalui email atau telepon yang tertera.',
                        'company_name' => $companyName,
                        'company_address' => $brandData?->company_address,
                        'signatory_name' => $signatoryName,
                        'signatory_position' => $signatoryPosition,
                    ],
                    'rows'            => $rows,
                    'openingGreeting' => 'Dengan ini kami mengonfirmasi bahwa pendaftar di bawah ini telah diterima untuk mengikuti program magang.',
                    'closingGreeting' => 'Harap konfirmasi kehadiran Anda melalui email atau telepon yang tertera.',
                    'logoData'        => $logoData,
                    'stampData'       => $stampData,
                ])->setPaper('A4', 'portrait');

                $pdf->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true]);

                $safeName = Str::slug($intern->fullname ?? 'intern', '-');
                $fileName = $safeLoaNumber . '-' . $safeName . '.pdf';
                $path     = $dir . '/' . $fileName;

                Storage::disk('public')->put($path, $pdf->output());

                // Insert snapshot to intern_loas (if missing or update if needed)
                $internLoa = InternLoa::updateOrCreate(
                    ['intern_id' => $intern->id],
                    [
                        'loa_number' => $loaNumber,
                        'accepted_start_date' => $intern->start_date ?? now(),
                        'accepted_end_date' => $intern->end_date ?? now(),
                        'company_name' => $companyName,
                        'company_logo_path' => $brandData?->logo,
                        'signatory_name' => $signatoryName,
                        'signatory_position' => $signatoryPosition,
                        'signature_image_path' => $brandData?->signature,
                    ]
                );

                $html = view('user.loa', [
                    'intern'          => $intern,
                    'loaNumber'       => $loaNumber,
                    'user'            => $user,
                    'loaSettings'     => (object)[
                        'header_text' => 'Dengan ini kami mengonfirmasi bahwa pendaftar di bawah ini telah diterima untuk mengikuti program magang.',
                        'footer_text' => 'Harap konfirmasi kehadiran Anda melalui email atau telepon yang tertera.',
                        'company_name' => $companyName,
                        'company_address' => $brandData?->company_address,
                        'signatory_name' => $signatoryName,
                        'signatory_position' => $signatoryPosition,
                    ],
                    'rows'            => $rows,
                    'openingGreeting' => 'Dengan ini kami mengonfirmasi bahwa pendaftar di bawah ini telah diterima untuk mengikuti program magang.',
                    'closingGreeting' => 'Harap konfirmasi kehadiran Anda melalui email atau telepon yang tertera.',
                    'logoData'        => $logoData,
                    'stampData'       => $stampData,
                ])->render();

                $safeName = Str::slug($intern->fullname ?? 'intern', '-');
                $fileName = $safeLoaNumber . '-' . $safeName . '.pdf';
                $tmpPath  = storage_path('app/tmp/' . $fileName);
                
                if (!is_dir(dirname($tmpPath))) {
                    mkdir(dirname($tmpPath), 0775, true);
                }

                \Spatie\Browsershot\Browsershot::html($html)
                    ->setOption('no-sandbox', true)
                    ->setOption('args', ['--disable-setuid-sandbox'])
                    ->emulateMedia('print')
                    ->format('A4')
                    ->margins(0, 0, 0, 0)
                    ->showBackground()
                    ->waitUntilNetworkIdle()
                    ->timeout(180)
                    ->savePdf($tmpPath);

                $generatedFiles[$intern->id] = [
                    'fullname' => $intern->fullname,
                    'path'     => $tmpPath,
                    'filename' => $fileName,
                ];

            } catch (\Throwable $e) {
                Log::error('Gagal generate LOA (brand)', ['err' => $e->getMessage(), 'intern_id' => $intern->id]);
                $errors[] = $intern->fullname;
            }
        }

        if (empty($generatedFiles)) {
            return back()->with('error', 'Gagal membuat LOA. Silakan coba lagi.');
        }

        if (count($generatedFiles) === 1) {
            $file = reset($generatedFiles);
            return response()->download($file['path'], $file['filename'])->deleteFileAfterSend(true);
        }

        $zipName = 'LOA-BATCH-' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path("app/tmp/{$zipName}");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file ZIP.');
        }

        foreach ($generatedFiles as $file) {
            if (file_exists($file['path'])) {
                $zip->addFile($file['path'], $file['filename']);
            }
        }
        $zip->close();

        // Delete individual temporary PDFs after zip creation
        foreach ($generatedFiles as $file) {
            if (file_exists($file['path'])) @unlink($file['path']);
        }

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);

    }

    protected function resolveBase64Image(?string $savedPath, string $fallbackRelative): ?string
    {
        if ($savedPath) {
            $fullPath = storage_path('app/public/' . $savedPath);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath);
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }

        $fallbackPath = storage_path('app/public/' . $fallbackRelative);
        if (file_exists($fallbackPath)) {
            $mime = mime_content_type($fallbackPath);
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fallbackPath));
        }

        return null;
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'intern_id' => ['required', 'integer', 'exists:internship_registrations,id'],
        ]);

        $user = $request->user();

        if ($user->role === 'admin') {
            $intern = IR::where('id', $validated['intern_id'])->firstOrFail();
        } else {
            $intern = IR::where('id', $validated['intern_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
            $this->ensureCanAccessCompletedDocs($user, $intern);
        }

        // Delegate to generateForBrand which now generates on the fly
        $request->merge(['intern_ids' => [$intern->id]]);
        return $this->generateForBrand($request);
    }

    public function generateBatch(Request $request)
    {
        return $this->generateForBrand($request);
    }

    public function indexInterns()
    {
        $registrations = IR::latest('id')->paginate(20);
        return view('admin.loa_interns', compact('registrations'));
    }

    protected function buildRows($interns): array
    {
        $rows = [];
        foreach ($interns as $intern) {
            $rows[] = [
                'nama_siswa' => $intern->fullname ?? 'Nama Tidak Diketahui',
                'nim_nis'    => ($intern->student_id ?? $intern->nim_nis ?? $intern->nim ?? null) ?: 'NIM/NIS Tidak Diketahui',
                'jurusan'    => ($intern->study_program ?? $intern->major ?? null) ?: 'Jurusan Tidak Diketahui',
                'instansi'   => $intern->institution_name ?? 'Instansi Tidak Diketahui',
                'periode'    => ($intern->start_date && $intern->end_date)
                    ? Carbon::parse($intern->start_date)->format('d F Y') . ' - ' . Carbon::parse($intern->end_date)->format('d F Y')
                    : 'Periode Tidak Diketahui',
                'kontak'     => ($intern->phone_number ?? $intern->contact_info ?? $intern->email ?? null) ?: 'Kontak Tidak Diketahui',
            ];
        }
        return $rows;
    }

    protected function ensureCanAccessCompletedDocs($user, $intern): void
    {
        $status = strtolower((string)($intern->internship_status ?? ''));

        if ($intern->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak membuat/akses LOA untuk data ini.');
        }

        $allowed = ['accepted', 'active', 'completed'];
        if (!($user->role === 'pemagang' && in_array($status, $allowed))) {
            abort(403, 'LOA hanya tersedia setelah pendaftaran diterima.');
        }
    }

    protected function ensurePublicDir(string $dir): void
    {
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
    }

    protected function maybeToPublicUrlOrAsset(?string $path, string $fallbackAsset): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }
        return asset($fallbackAsset);
    }
}
