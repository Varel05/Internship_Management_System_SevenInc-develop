@props([
    'membercard' => null,
    'brand'      => null,
    'name'       => null,
    'code'       => null,
    'angkatan'   => null,
    'instansi'   => null,
    'divisi'     => null,
    'width'      => '380px',
    'height'     => '240px',
    'forPdf'     => false,
])

@php
    // Resolusi Brand
    $brandObj = $brand ?? $membercard?->intern?->brandRel;
    if (!$brandObj && $membercard?->intern?->brand) {
        $brandObj = \App\Models\Brand::whereRaw('LOWER(name) = ?', [strtolower(trim($membercard->intern->brand))])
            ->orWhere('code', $membercard->intern->brand)
            ->first();
    }

    $theme = $brandObj?->membercard_theme ?? [
        'gradient'      => 'linear-gradient(135deg, #052317 0%, #0c432f 50%, #03170e 100%)',
        'accent'        => '#E6CA65',
        'accent_light'  => '#FDF6D8',
        'accent_border' => 'rgba(230, 202, 101, 0.35)',
        'pill_bg'       => 'rgba(230, 202, 101, 0.12)',
        'pill_border'   => 'rgba(230, 202, 101, 0.3)',
        'chip'          => 'gold',
        'badge_text'    => 'SEVEN INC ALUMNI',
    ];

    $brandName = $brandObj?->name ?? $membercard?->intern?->brand ?? 'Seven Inc';
    $brandLogo = $brandObj?->logo ? \Illuminate\Support\Facades\Storage::url($brandObj->logo) : null;
    $customBg  = $brandObj?->membercard_bg ? \Illuminate\Support\Facades\Storage::url($brandObj->membercard_bg) : null;

    // Untuk PDF rendering via Browsershot: gunakan file path lokal base64 jika ada
    if ($forPdf && $brandObj?->membercard_bg && file_exists(storage_path('app/public/' . $brandObj->membercard_bg))) {
        $customBg = 'data:image/' . pathinfo($brandObj->membercard_bg, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $brandObj->membercard_bg)));
    }
    if ($forPdf && $brandObj?->logo && file_exists(storage_path('app/public/' . $brandObj->logo))) {
        $brandLogo = 'data:image/' . pathinfo($brandObj->logo, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $brandObj->logo)));
    }

    // Resolusi Data Alumni
    $fullName = $name ?? $membercard?->intern?->fullname ?? 'NAMA ALUMNI';
    $rawCode  = $code ?? $membercard?->member_code ?? 'SI24001';
    $batchYear= $angkatan ?? $membercard?->batch_year ?? date('Y');
    $campus   = $instansi ?? $membercard?->intern?->institution_name ?? 'Universitas / Sekolah';
    $division = $divisi ?? $membercard?->intern?->division?->name ?? $membercard?->intern?->internship_interest ?? null;

    // Format tampilan nomor kartu (tiap 4 karakter dipisah spasi)
    $formattedCode = trim(chunk_split($rawCode, 4, ' '));

    // Chip metallic colors
    $isGoldChip = ($theme['chip'] ?? 'gold') === 'gold';
    $chipGradient = $isGoldChip
        ? 'linear-gradient(135deg, #e6ca65 0%, #b8972e 50%, #f7e7a9 100%)'
        : 'linear-gradient(135deg, #e2e8f0 0%, #94a3b8 50%, #f8fafc 100%)';
    $chipBorder = $isGoldChip ? '#927318' : '#64748b';

    // Scaling untuk mode PDF vs Web
    $cardPadding  = $forPdf ? '11px 14px' : '18px 20px';
    $nameFontSize = $forPdf ? '13.5px' : '16px';
    $codeFontSize = $forPdf ? '11.5px' : '13.5px';
    $pillFontSize = $forPdf ? '7.5px' : '8.5px';
    $chipW        = $forPdf ? '32px' : '38px';
    $chipH        = $forPdf ? '24px' : '29px';
    $logoMaxH     = $forPdf ? '20px' : '26px';
@endphp

<div class="membercard-wrapper relative select-none"
     style="width: {{ $width }}; height: {{ $height }}; border-radius: {{ $forPdf ? '0' : '16px' }}; overflow: hidden; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; box-shadow: {{ $forPdf ? 'none' : '0 16px 36px -8px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.18)' }};">

    {{-- Layer Background --}}
    @if($customBg)
        <div style="position: absolute; inset: 0; background-image: url('{{ $customBg }}'); background-size: cover; background-position: center; z-index: 1;"></div>
        {{-- Elegant Dark Overlay agar teks & elemen selalu jelas dan berkelas --}}
        <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(8, 14, 18, 0.78) 0%, rgba(10, 20, 26, 0.58) 50%, rgba(5, 10, 14, 0.85) 100%); z-index: 2;"></div>
    @else
        <div style="position: absolute; inset: 0; background: {{ $theme['gradient'] }}; z-index: 1;"></div>
        {{-- Luxury geometric glow effects --}}
        <div style="position: absolute; top: -30%; right: -20%; width: 260px; height: 260px; border-radius: 50%; background: radial-gradient(circle, {{ $theme['accent'] }}22 0%, transparent 70%); z-index: 2;"></div>
        <div style="position: absolute; bottom: -30%; left: -20%; width: 240px; height: 240px; border-radius: 50%; background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%); z-index: 2;"></div>
    @endif

    {{-- Refleksi Kilau Permukaan (Subtle Gloss / Sheen) --}}
    <div style="position: absolute; inset: 0; background: linear-gradient(115deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.03) 35%, transparent 60%); z-index: 3; pointer-events: none;"></div>

    {{-- Inner Border Foil --}}
    <div style="position: absolute; inset: {{ $forPdf ? '4px' : '7px' }}; border: 1px solid rgba(255, 255, 255, 0.14); border-radius: {{ $forPdf ? '6px' : '11px' }}; z-index: 4; pointer-events: none;"></div>

    {{-- Content Container --}}
    <div style="position: relative; z-index: 5; height: 100%; display: flex; flex-direction: column; justify-content: space-between; padding: {{ $cardPadding }}; box-sizing: border-box;">

        {{-- Baris Atas: Chip + Brand Identity --}}
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
            {{-- EMV Chip + NFC --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                {{-- Metallic Smart Chip SVG --}}
                <div style="width: {{ $chipW }}; height: {{ $chipH }}; border-radius: 4px; background: {{ $chipGradient }}; border: 1px solid {{ $chipBorder }}; position: relative; overflow: hidden; box-shadow: inset 0 1px 2px rgba(255,255,255,0.4), 0 2px 4px rgba(0,0,0,0.35);">
                    <div style="position: absolute; top: 32%; left: 0; right: 0; height: 1px; background: {{ $chipBorder }};"></div>
                    <div style="position: absolute; bottom: 32%; left: 0; right: 0; height: 1px; background: {{ $chipBorder }};"></div>
                    <div style="position: absolute; top: 0; bottom: 0; left: 36%; width: 1px; background: {{ $chipBorder }};"></div>
                    <div style="position: absolute; top: 0; bottom: 0; right: 36%; width: 1px; background: {{ $chipBorder }};"></div>
                    <div style="position: absolute; top: 32%; bottom: 32%; left: 28%; right: 28%; border: 1px solid {{ $chipBorder }}; border-radius: 2px;"></div>
                </div>

                {{-- Contactless / NFC Wave SVG --}}
                <svg style="width: {{ $forPdf ? '13px' : '16px' }}; height: {{ $forPdf ? '13px' : '16px' }}; color: {{ $theme['accent'] }}; opacity: 0.9;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <path d="M8.5 16.5a5 5 0 0 1 0-9"/>
                    <path d="M12 19a8.5 8.5 0 0 0 0-14"/>
                    <path d="M15.5 21.5a12 12 0 0 0 0-19"/>
                </svg>
            </div>

            {{-- Brand Logo & Alumni Tag --}}
            <div style="text-align: right; max-width: 170px;">
                @if($brandLogo)
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}"
                         style="max-height: {{ $logoMaxH }}; max-width: 130px; object-fit: contain; margin-left: auto; margin-bottom: 2px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.4));">
                @else
                    <div style="font-size: {{ $forPdf ? '11px' : '13px' }}; font-weight: 800; color: #FFFFFF; letter-spacing: 0.08em; text-transform: uppercase; line-height: 1.2; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                        {{ $brandName }}
                    </div>
                @endif
                <div style="display: inline-flex; align-items: center; gap: 3px; font-size: 7px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: {{ $theme['accent'] }}; padding: 1px 5px; border-radius: 999px; background: {{ $theme['pill_bg'] }}; border: 1px solid {{ $theme['pill_border'] }}; margin-top: 2px;">
                    <span style="width: 3px; height: 3px; border-radius: 50%; background: {{ $theme['accent'] }};"></span>
                    <span>ALUMNI MEMBER</span>
                </div>
            </div>
        </div>

        {{-- Baris Tengah: Nama Alumni + Divider --}}
        <div style="margin-top: auto; margin-bottom: auto; padding-top: 4px; padding-bottom: 4px;">
            <div style="font-size: 7.5px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: rgba(255, 255, 255, 0.75); margin-bottom: 1px;">
                Member Name
            </div>
            <div style="font-size: {{ $nameFontSize }}; font-weight: 800; letter-spacing: 0.04em; color: #FFFFFF; text-transform: uppercase; line-height: 1.2; text-shadow: 0 2px 6px rgba(0,0,0,0.6); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $fullName }}
            </div>
            <div style="height: 1.5px; width: 100%; margin-top: 4px; background: linear-gradient(90deg, {{ $theme['accent'] }} 0%, rgba(255,255,255,0.25) 60%, transparent 100%);"></div>
        </div>

        {{-- Baris Bawah: Nomor Kartu & Metadata --}}
        <div>
            {{-- Member Code --}}
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: {{ $forPdf ? '4px' : '7px' }};">
                <div>
                    <span style="font-size: 7px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255, 255, 255, 0.70); display: block; margin-bottom: 1px;">
                        Member ID
                    </span>
                    <span style="font-family: 'Courier New', Courier, monospace; font-size: {{ $codeFontSize }}; font-weight: 800; letter-spacing: 0.18em; color: {{ $theme['accent_light'] }}; text-shadow: 0 1px 3px rgba(0,0,0,0.7);">
                        {{ $formattedCode }}
                    </span>
                </div>

                {{-- Security Hologram / Verification Stamp --}}
                <div style="display: flex; align-items: center; gap: 3px; padding: 1.5px 6px; border-radius: 4px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.18);">
                    <svg style="width: 9px; height: 9px; color: {{ $theme['accent'] }};" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    <span style="font-size: 7.5px; font-weight: 700; letter-spacing: 0.08em; color: #FFFFFF; text-transform: uppercase;">VERIFIED</span>
                </div>
            </div>

            {{-- Metadata Chips --}}
            <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                <span style="display: inline-flex; align-items: center; gap: 3px; padding: 1.5px 6px; border-radius: 4px; background: {{ $theme['pill_bg'] }}; border: 1px solid {{ $theme['pill_border'] }}; font-size: {{ $pillFontSize }}; font-weight: 600; color: #FFFFFF;">
                    <span style="color: {{ $theme['accent'] }}; font-weight: 700;">Angkatan:</span> {{ $batchYear }}
                </span>
                <span style="display: inline-flex; align-items: center; gap: 3px; padding: 1.5px 6px; border-radius: 4px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.16); font-size: {{ $pillFontSize }}; font-weight: 500; color: #FFFFFF; max-width: 170px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                      title="{{ $campus }}">
                    <span style="color: {{ $theme['accent'] }}; font-weight: 700;">Instansi:</span> {{ $campus }}
                </span>
                @if($division)
                    <span style="display: inline-flex; align-items: center; gap: 3px; padding: 1.5px 6px; border-radius: 4px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.16); font-size: {{ $pillFontSize }}; font-weight: 500; color: #FFFFFF; max-width: 110px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                          title="{{ $division }}">
                        {{ $division }}
                    </span>
                @endif
            </div>
        </div>

    </div>
</div>
