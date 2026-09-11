<?php

namespace App\Http\Controllers;

use App\Models\SKLSetting;
use App\Models\User;
use App\Models\InternshipRegistration as IR;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use App\Models\DocumentDownload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SKLController extends Controller
{
    /**
     * Tampilkan form pengaturan SKL
     */

    /**
     * GET /admin/skl/interns-by-brand?brand=XXX
     * API: ambil pemagang completed yang belum punya SKL untuk brand tertentu
     */
    public function getInternsByBrand(Request $request)
    {
        $brand = $request->query('brand');

        if (!$brand) {
            return response()->json(['interns' => []]);
        }

        $alreadyHasSKL = []; // Disable filter since table is removed
        
        $interns = IR::query()
            ->where('internship_status', IR::STATUS_COMPLETED)
            ->where('brand', $brand)
            ->select('id', 'fullname', 'student_id', 'study_program', 'institution_name', 'start_date', 'end_date', 'internship_interest')
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
                'internship_interest' => $r->internship_interest ?? '',
            ]);

        return response()->json(['interns' => $interns]);
    }

    /**
     * POST /admin/skl/generate-brand
     * Generate SKL untuk setiap pemagang yang dipilih (1 PDF per pemagang), dikemas ZIP jika lebih dari 1
     */
    public function generateForBrand(Request $request)
    {
        $validated = $request->validate([
            'intern_ids'              => ['required', 'array', 'min:1'],
            'intern_ids.*'            => ['integer', 'exists:internship_registrations,id'],
            'company_name'            => ['required', 'string', 'max:100'],
            'company_address'         => ['required', 'string', 'max:500'],
            'company_city'            => ['required', 'string', 'max:100'],
            'leader_name'             => ['required', 'string', 'max:150'],
            'leader_title'            => ['required', 'string', 'max:100'],
            'activity_description'    => ['nullable', 'string', 'max:2000'],
            'participant_achievement' => ['nullable', 'string', 'max:2000'],
            'logo'                    => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'stamp'                   => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $interns = IR::whereIn('id', $validated['intern_ids'])
            ->where('internship_status', IR::STATUS_COMPLETED)
            ->get();

        if ($interns->isEmpty()) {
            return back()->with('error', 'Data pemagang tidak ditemukan atau belum berstatus Selesai.');
        }

        // Simpan config ke database agar perubahan tersimpan
        $config = SKLSetting::first() ?? new SKLSetting();
        $config->company_name            = $validated['company_name'];
        $config->company_address         = $validated['company_address'];
        $config->company_city            = $validated['company_city'];
        $config->leader_name             = $validated['leader_name'];
        $config->leader_title            = $validated['leader_title'];
        $config->activity_description    = $validated['activity_description'] ?? '';
        $config->participant_achievement = $validated['participant_achievement'] ?? '';

        if ($request->hasFile('logo')) {
            $config->logo_path = $request->file('logo')->storeAs('images/logos', 'logo_' . Str::slug($validated['company_name']) . '.png', 'public');
        }
        if ($request->hasFile('stamp')) {
            $config->stamp_path = $request->file('stamp')->storeAs('images/signature', 'ttd_' . Str::slug($validated['company_name']) . '.png', 'public');
        }
        $config->save();

        // Resolve aset visual (logo & stempel) ke base64
        $logoData  = $this->resolveBase64Image($request, 'logo',  $config->logo_path,  'images/logos/logo_seveninc.png');
        $stampData = $this->resolveBase64Image($request, 'stamp', $config->stamp_path, 'images/signature/ttd_arisetiahusbana.png');

        $fullDir = public_path('storage/documents/skl');
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0777, true);
        }

        $generatedFiles = [];
        $errors         = [];

        Carbon::setLocale('id');
        
        $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        $romanMonth = $roman[(int)date('n')];
        $year = date('Y');

        $lastSkl = \App\Models\SklDocument::where('skl_number', 'LIKE', "%/SKL/%/{$romanMonth}/{$year}")
            ->orderByDesc('id')->first();
        $seq = 1;
        if ($lastSkl && preg_match('/^(\d{3})\/SKL\//', $lastSkl->skl_number, $m)) {
            $seq = (int)$m[1] + 1;
        }

        foreach ($interns as $intern) {
            try {
                $participantName      = $intern->fullname ?? '-';
                $participantId        = $intern->student_id ?? '-';
                $participantMajor     = $intern->study_program ?? '-';
                $participantInstitute = $intern->institution_name ?? '-';
                
                $interest = $intern->internship_interest ?? '';
                $dbDivision = \App\Models\Division::where('name', $interest)
                    ->orWhere('slug', \Illuminate\Support\Str::slug($interest, '-'))
                    ->first();
                $divisionName = $dbDivision?->code ?? 'UMUM';

                // Gunakan brand pemagang sebagai nama perusahaan di surat
                $brandData = \App\Models\Brand::whereRaw('LOWER(name) = ?', [strtolower(trim($intern->brand))])->first();
                $companyName = $intern->brand ?: $config->company_name;
                $companyAddress = $brandData?->company_address ?? $config->company_address;

                $startStr      = $intern->start_date ? Carbon::parse($intern->start_date)->isoFormat('D MMMM Y') : '-';
                $endStr        = $intern->end_date   ? Carbon::parse($intern->end_date)->isoFormat('D MMMM Y')   : '-';
                $letterDateStr = now()->translatedFormat('d F Y');
                
                $brandCodeStr = strtoupper($intern->brand ?? 'SI');
                $seqStr = str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
                $letterNumber = "{$seqStr}/SKL/{$divisionName}/SEVEN.{$brandCodeStr}/{$romanMonth}/{$year}";
                $seq++;

                $data = [
                    'companyName'            => $companyName,
                    'companyAddress'         => $companyAddress,
                    'companyCity'            => $config->company_city,
                    'leaderName'             => $config->leader_name,
                    'leaderTitle'            => $config->leader_title,
                    'letterNumber'           => $letterNumber,
                    'logoData'               => $logoData,
                    'stampData'              => $stampData,
                    'participantName'        => $participantName,
                    'participantId'          => $participantId,
                    'participantMajor'       => $participantMajor,
                    'participantInstitute'   => $participantInstitute,
                    'divisionName'           => $divisionName,
                    'startStr'               => $startStr,
                    'endStr'                 => $endStr,
                    'letterDateStr'          => $letterDateStr,
                    'activityDescription'    => $config->activity_description ?? '',
                    'participantAchievement' => $config->participant_achievement ?? '',
                ];

                $html     = view('user.skl', $data)->render();
                $safeName = preg_replace('/[^a-z0-9\-_]+/i', '_', $participantName);
                $fileName = "SKL_{$safeName}_" . now()->format('Ymd_His') . ".pdf";
                $relPath  = "storage/documents/skl/{$fileName}";
                $fullPath = public_path($relPath);

                Browsershot::html($html)
                    ->setOption('no-sandbox', true)
                    ->emulateMedia('print')
                    ->format('A4')
                    ->margins(10, 10, 10, 10)
                    ->showBackground()
                    ->waitUntilNetworkIdle()
                    ->timeout(180)
                    ->savePdf($fullPath);

                // DocumentDownload removed due to database changes

                $generatedFiles[$intern->id] = [
                    'fullname' => $participantName,
                    'path'     => $fullPath,
                    'filename' => $fileName,
                ];

            } catch (\Throwable $e) {
                Log::error('Gagal generate SKL (brand)', ['err' => $e->getMessage(), 'intern_id' => $intern->id]);
                $errors[] = $intern->fullname;
            }
        }

        if (empty($generatedFiles)) {
            $errorMsg = 'Gagal membuat SKL untuk semua pemagang.';
            if (!empty($errors)) {
                $errorMsg .= ' Gagal: ' . implode(', ', $errors);
            }
            // Cek apakah AJAX request
            if (request()->header('X-Requested-With') === 'XMLHttpRequest' || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return back()->with('error', $errorMsg);
        }

        $successCount = count($generatedFiles);
        $names = collect($generatedFiles)->pluck('fullname')->toArray();
        $successMsg = "✅ SKL untuk {$successCount} pemagang berhasil dibuat dan tersedia untuk diunduh.";
        $details = array_map(fn($n) => "SKL {$n} berhasil dibuat", $names);

        if (!empty($errors)) {
            $details = array_merge($details, array_map(fn($n) => "❌ Gagal: {$n}", $errors));
        }

        // Cek apakah AJAX request → return JSON (tidak download)
        if (request()->header('X-Requested-With') === 'XMLHttpRequest' || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'details' => $details,
                'count'   => $successCount,
            ]);
        }

        // Fallback non-AJAX (tidak harusnya tercapai dari UI baru): redirect dengan notif
        return redirect()->route('admin.skl.editor')->with('success', $successMsg);
    }

    /**
     * Resolve gambar: dari upload baru → dari path tersimpan → dari fallback default
     */
    protected function resolveBase64Image(Request $request, string $inputName, ?string $savedPath, string $fallbackRelative): ?string
    {
        if ($request->hasFile($inputName) && $request->file($inputName)->isValid()) {
            $file = $request->file($inputName);
            $mime = $file->getMimeType();
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

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

    /**
     * Update data SKL
     */

    /**
     * Preview SKL berdasarkan data dari database
     */
    public function preview(Request $request)
    {
        $config = SKLSetting::first() ?? new SKLSetting([
            'company_name'    => 'Seven Inc',
            'company_address' => 'Jl. Raya Janti Gg. Harjuna No.59, Jaranan, Karangjambe, Kec. Banguntapan, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55198',
            'company_city'    => 'Yogyakarta',
            'leader_name'     => 'Nama Pimpinan / HRD',
            'leader_title'    => 'Manajer HRD',
            'logo_path'       => 'storage/images/logos/logo_seveninc.png',
            'stamp_path'      => 'storage/images/signature/ttd_rekariodanny.png',
        ]);

        // Company block (boleh override dari query agar realtime di iframe)
        $companyName    = $request->get('company_name',    $config->company_name);
        $companyAddress = $request->get('company_address', $config->company_address);
        $companyCity    = $request->get('company_city',    $config->company_city);
        $leaderName     = $request->get('leader_name',     $config->leader_name);
        $leaderTitle    = $request->get('leader_title',    $config->leader_title);

        // Dummy peserta untuk preview
        $participantName      = $request->get('participant_name', 'Nama Pemagang (Preview)');
        $participantId        = $request->get('participant_id', '1234567890 (Preview)');
        $participantMajor     = $request->get('participant_major', 'Teknik Informatika (Preview)');
        $participantInstitute = $request->get('participant_institute', 'Universitas Contoh (Preview)');
        $divisionName         = $request->get('division_name', 'Divisi Teknologi (Preview)');

        // Periode (boleh override)
        $startAt  = $request->get('start_date', Carbon::now()->subMonths(1)->format('Y-m-d'));
        $endAt    = $request->get('end_date',   Carbon::now()->format('Y-m-d'));
        $startStr = Carbon::parse($startAt)->isoFormat('D MMMM Y');
        $endStr   = Carbon::parse($endAt)->isoFormat('D MMMM Y');

        // Letter meta
        $letterDateStr = Carbon::parse($endAt)->isoFormat('D MMMM Y');
        $letterNumber  = 'SKL/'.Carbon::parse($endAt)->format('Y').'/DEMO';

        // Assets
        $logoFile  = public_path('storage/images/logos/logo_seveninc.png');
        $stampFile = public_path('storage/images/signature/ttd_arisetiahusbana.png');

        // Fallback stamp ke file TTD lain yang ada
        if (!file_exists($stampFile)) {
            $candidates = glob(public_path('storage/images/signature/*.{png,jpg,jpeg}'), GLOB_BRACE);
            $stampFile  = !empty($candidates) ? $candidates[0] : null;
        }

        $logoPath  = $logoFile;
        $stampPath = $stampFile;

        // Mendapatkan data dari request atau menggunakan default value
        $activityDescription = $request->get('activity_description', $config->activity_description);
        $participantAchievement = $request->get('participant_achievement', $config->participant_achievement);


        return view('user.skl', compact(
            'companyName','companyAddress','companyCity','leaderName','leaderTitle',
            'letterNumber','logoPath','stampPath',
            'participantName','participantId','participantMajor','participantInstitute','divisionName',
            'startStr','endStr','letterDateStr','activityDescription', 'participantAchievement'
        ));
    }

    /**
     * GET /admin/skl/generate/{intern}
     * Form review sebelum generate SKL
     */
    /**
     * POST /admin/skl/generate/{intern}
     * Proses generate & download SKL dari form
     */

    /**
     * GET /admin/skl/download/{user} — download SKL dari Data SKL (tetap ada)
     */
    public function download(Request $request, $userId = null)
    {
        $authUser = auth()->user();
        $targetUser = $authUser;

        // Support route param {user} dari admin route
        $resolvedUserId = $userId ?? $request->get('user_id');

        if ($resolvedUserId) {
            if (!in_array($authUser->role, ['admin','staff','hrd'])) {
                abort(403, 'Hanya admin/staff yang dapat mengunduh SKL untuk user lain.');
            }
            $targetUser = User::findOrFail($resolvedUserId);
        }

        // Ambil data magang
        $ir = IR::where('user_id', $targetUser->id)->latest()->first();
        if (!$ir || $ir->internship_status !== 'completed') {
            abort(403, 'SKL hanya dapat diunduh setelah status magang completed.');
        }

        // Cari rekam SKL di database
        $sklRecord = \App\Models\SklDocument::where('intern_id', $ir->id)->latest()->first();
        if (!$sklRecord) {
            return back()->with('error', 'SKL belum tersedia. Hubungi admin untuk mendapatkan SKL Anda.');
        }

        $safeName = preg_replace('/[^a-z0-9\-_]+/i', '_', $ir->fullname ?? $targetUser->name);
        $fileName = "SKL_{$safeName}.pdf";
        $fullPath = public_path("storage/documents/skl/{$fileName}");
        
        if (!file_exists($fullPath)) {
            return back()->with('error', 'File fisik SKL tidak ditemukan. Hubungi admin.');
        }

        $downloadName = "SKL_{$safeName}.pdf";

        return response()->download($fullPath, $downloadName, ['Content-Type' => 'application/pdf']);
    }
}
