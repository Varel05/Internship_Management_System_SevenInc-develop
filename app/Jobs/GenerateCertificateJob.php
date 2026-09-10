<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\InternCertificate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\View;

class GenerateCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $internId;

    public function __construct($internId)
    {
        $this->internId = $internId;
    }

    public function handle(): void
    {
        try {
            $certificate = InternCertificate::where('intern_id', $this->internId)->first();

            if (!$certificate) {
                Log::error("InternCertificate tidak ditemukan untuk intern_id: {$this->internId}");
                return;
            }

            $nameSlug  = Str::slug($certificate->intern->fullname ?? 'cert', '-');
            $pdfTitle  = "Sertifikat-{$nameSlug}";
            $fileName  = "{$pdfTitle}.pdf";
            
            $relPath  = "storage/documents/certificates/{$fileName}";
            $fullDir  = public_path('storage/documents/certificates');
            $fullPath = public_path($relPath);

            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0777, true);
            }

            $html = View::make('certificates.pdf', [
                'certificate' => $certificate,
                'pdfTitle'    => $pdfTitle,
            ])->render();

            Browsershot::html($html)
                ->format('A4')
                ->landscape()
                ->margins(0, 0, 0, 0)
                ->timeout(180)
                ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
                ->emulateMedia('screen')
                ->showBackground()
                ->setOption('printBackground', true)
                ->savePdf($fullPath);

            Log::info("Berhasil generate Sertifikat secara background untuk intern_id: {$this->internId}");
        } catch (\Throwable $e) {
            Log::error('Gagal generate Sertifikat secara background', [
                'err' => $e->getMessage(),
                'intern_id' => $this->internId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
