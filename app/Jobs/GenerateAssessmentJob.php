<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\InternAssessment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Spatie\Browsershot\Browsershot;

class GenerateAssessmentJob implements ShouldQueue
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
            $assessment = InternAssessment::where('intern_id', $this->internId)->with('intern')->first();

            if (!$assessment) {
                Log::info("InternAssessment tidak ditemukan untuk intern_id: {$this->internId}. Melewati generate PDF.");
                return;
            }

            Carbon::setLocale('id');

            $logoFile     = public_path('storage/' . ($assessment->company_logo_path ?? 'images/logos/logo_seveninc.png'));
            $fallbackLogo = public_path('storage/images/logos/logo_seveninc.png');
            $logoSrc      = $this->imageDataUri($logoFile) ?: $this->imageDataUri($fallbackLogo);

            $sigFile          = public_path('storage/' . ($assessment->signature_image_path ?? 'images/signature/ttd_rekariodanny.png'));
            $fallbackSignature = public_path('storage/images/signature/ttd_rekariodanny.png');
            $sigSrc           = $this->imageDataUri($sigFile) ?: $this->imageDataUri($fallbackSignature);

            $empty     = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAn0B9WYaBZMAAAAASUVORK5CYII=';
            $logoSrc   = $logoSrc   ?: $empty;
            $sigSrc    = $sigSrc    ?: $empty;

            $participantName = $assessment->intern->fullname ?? 'peserta';
            // User requested names like: intern_assessment_{nama_peserta}
            $safeName = preg_replace('/[^a-z0-9\-_]+/i', '_', strtolower($participantName));
            $fileName = "intern_assessment_{$safeName}.pdf";

            $relPath  = "storage/documents/assessments/{$fileName}";
            $fullDir  = public_path('storage/documents/assessments');
            $fullPath = public_path($relPath);

            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0777, true);
            }

            $htmlContent = view('admin.interns.pdf_assessment', [
                'assessment' => $assessment,
                'logoSrc'    => $logoSrc,
                'sigSrc'     => $sigSrc,
                'autoPrint'  => false,
            ])->render();

            Browsershot::html($htmlContent)
                ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                ->emulateMedia('print')
                ->format('A4')
                ->landscape()
                ->margins(0, 0, 0, 0)
                ->timeout(180)
                ->savePdf($fullPath);

            Log::info("Berhasil generate Assessment secara background untuk intern_id: {$this->internId}");
        } catch (\Throwable $e) {
            Log::error('Gagal generate Assessment secara background', [
                'err' => $e->getMessage(),
                'intern_id' => $this->internId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function imageDataUri(string $path): ?string
    {
        if (!file_exists($path)) return null;
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
