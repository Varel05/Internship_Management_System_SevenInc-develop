<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\InternshipRegistration as IR;
use App\Models\SklDocument;
use App\Models\Brand;
use App\Models\SKLSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Spatie\Browsershot\Browsershot;

class GenerateSklJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $internId;

    /**
     * Create a new job instance.
     */
    public function __construct($internId)
    {
        $this->internId = $internId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $intern = IR::findOrFail($this->internId);
            $sklDoc = SklDocument::where('intern_id', $this->internId)->first();

            if (!$sklDoc) {
                Log::error("SklDocument tidak ditemukan untuk intern_id: {$this->internId}");
                return;
            }

            // Determine Brand for company address if needed
            $brandData = null;
            if ($intern->brand_id) {
                $brandData = Brand::find($intern->brand_id);
            } elseif ($intern->brand) {
                $brandData = Brand::whereRaw('LOWER(name) = ?', [strtolower(trim($intern->brand))])->first();
            }

            $companyName    = $sklDoc->company_name;
            $companyAddress = $brandData->company_address ?? 'Jl. Raya Janti Gg. Harjuna No.59';
            $companyCity    = 'Yogyakarta';
            $signatoryName  = $sklDoc->signatory_name;
            $signatoryTitle = $sklDoc->signatory_position;
            
            $activityDescription    = '';
            $participantAchievement = '';

            // Resolve images to base64
            $logoData = $this->resolveBase64Image($sklDoc->company_logo_path, 'images/logos/logo_seveninc.png');
            $stampData = $this->resolveBase64Image($sklDoc->signature_image_path, 'images/signature/ttd_arisetiahusbana.png');

            // Set Locale for dates
            Carbon::setLocale('id');

            // Participant Info
            $user                 = $intern->user;
            $participantName      = $intern->fullname ?? ($user?->name ?? '-');
            $participantId        = $intern->student_id ?? '-';
            $participantMajor     = $intern->study_program ?? '-';
            $participantInstitute = $intern->institution_name ?? '-';
            $divisionName         = $intern->internship_interest ?? '-';

            $startStr      = $intern->start_date ? Carbon::parse($intern->start_date)->isoFormat('D MMMM Y') : '-';
            $endStr        = $intern->end_date   ? Carbon::parse($intern->end_date)->isoFormat('D MMMM Y')   : '-';
            $letterDateStr = $intern->end_date   ? Carbon::parse($intern->end_date)->isoFormat('D MMMM Y')   : now()->isoFormat('D MMMM Y');
            
            $running       = str_pad((string) $intern->id, 4, '0', STR_PAD_LEFT);
            $year          = $intern->end_date ? Carbon::parse($intern->end_date)->format('Y') : now()->format('Y');
            $sklNumber     = $sklDoc->skl_number;

            $data = [
                'companyName' => $companyName,
                'companyAddress' => $companyAddress,
                'companyCity' => $companyCity,
                'leaderName' => $signatoryName,
                'leaderTitle' => $signatoryTitle,
                'letterNumber' => $sklNumber,
                'logoData' => $logoData,
                'stampData' => $stampData,
                'participantName' => $participantName,
                'participantId' => $participantId,
                'participantMajor' => $participantMajor,
                'participantInstitute' => $participantInstitute,
                'divisionName' => $divisionName,
                'startStr' => $startStr,
                'endStr' => $endStr,
                'letterDateStr' => $letterDateStr,
                'activityDescription' => $activityDescription,
                'participantAchievement' => $participantAchievement,
            ];

            $html = view('user.skl', $data)->render();

            $safeName = preg_replace('/[^a-z0-9\-_]+/i', '_', $participantName);
            $fileName = "SKL_{$safeName}.pdf";
            $relPath  = "storage/documents/skl/{$fileName}";
            $fullDir  = public_path('storage/documents/skl');
            $fullPath = public_path($relPath);

            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0777, true);
            }

            // Generate PDF via Browsershot
            Browsershot::html($html)
                ->setOption('no-sandbox', true)
                ->emulateMedia('print')
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->timeout(180)
                ->savePdf($fullPath);

            Log::info("Berhasil generate SKL secara background untuk intern_id: {$intern->id}");
        } catch (\Throwable $e) {
            Log::error('Gagal generate SKL secara background', [
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
}
