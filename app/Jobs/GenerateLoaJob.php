<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InternshipRegistration as IR;
use App\Models\InternLoa;
use App\Models\Brand;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GenerateLoaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $internId;
    protected $brandId;

    /**
     * Create a new job instance.
     */
    public function __construct($internId, $brandId = null)
    {
        $this->internId = $internId;
        $this->brandId = $brandId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $intern = IR::findOrFail($this->internId);

            // Tentukan Brand
            $brandData = null;
            if ($this->brandId) {
                $brandData = Brand::find($this->brandId);
            } elseif ($intern->brand_id) {
                $brandData = Brand::find($intern->brand_id);
            } elseif ($intern->brand) {
                $brandData = Brand::whereRaw('LOWER(name) = ?', [strtolower(trim($intern->brand))])->first();
            }

            $companyName = $brandData->name ?? $intern->brand ?? 'Seven Inc';
            $signatoryName = $brandData->signatory_name ?? 'Ari Setia Husbana';
            $signatoryPosition = $brandData->signatory_position ?? 'HRD';

            $logoData = $this->resolveBase64Image($brandData?->logo, 'images/logos/logo_seveninc.png');
            $stampData = $this->resolveBase64Image($brandData?->signature, 'images/signature/ttd_arisetiahusbana.png');

            $dir = 'documents/loa';
            $this->ensurePublicDir($dir);

            $rows = $this->buildRows([$intern]);

            // Dummy user untuk view (jika view membutuhkan object user)
            $user = $intern->user;

            $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
            $romanMonth = $roman[(int)date('n')];
            $year = date('Y');

            $last = InternLoa::where('loa_number', 'LIKE', "%/LOA/%/{$romanMonth}/{$year}")
                ->orderByDesc('id')->first();
            $seq = 1;
            if ($last && preg_match('/^(\d{3})\/LOA\//', $last->loa_number, $m)) {
                $seq = (int)$m[1] + 1;
            }
            
            $education = $intern->education ?? 'S1';
            $typeString = strtoupper($education) . '-Magang';
            
            $interest = $intern->internship_interest ?? '';
            
            $dbDivision = \App\Models\Division::where('name', $interest)
                ->orWhere('slug', Str::slug($interest, '-'))
                ->first();
                
            $division = $dbDivision?->code ?? 'UMUM';
            $brandStr = strtoupper($brandData?->code ?? 'SI');

            $seqStr = str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
            $loaNumber = "{$seqStr}/LOA/{$division}/SEVEN.{$brandStr}/{$romanMonth}/{$year}";
            $safeLoaNumber = str_replace('/', '-', $loaNumber);

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

            // Insert snapshot to intern_loas
            InternLoa::updateOrCreate(
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

            Log::info("Berhasil generate LOA secara background untuk intern_id: {$intern->id}");
        } catch (\Throwable $e) {
            Log::error('Gagal generate LOA secara background', [
                'err' => $e->getMessage(),
                'intern_id' => $this->internId,
                'trace' => $e->getTraceAsString()
            ]);
        }
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

    protected function ensurePublicDir(string $dir): void
    {
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
    }
}
