@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.brands.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-[#4B5F5A] hover:text-[#2D8659] transition mb-3">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
            <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34]">Detail Brand: {{ $brand->name }}</h1>
        </div>
        <a href="{{ route('admin.brands.edit', $brand->id) }}" class="flex items-center gap-2 rounded-[9px] bg-[#2D8659] px-4 py-2 text-[13px] font-semibold text-white hover:bg-[#1F5F3F] transition shadow-sm">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Edit Brand
        </a>
    </div>

    <div class="rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm overflow-hidden max-w-4xl">
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-1 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Kode Brand</label>
                    <p class="text-sm font-semibold text-[#1B3A34]">{{ $brand->code }}</p>
                </div>
                <div>
                    <label class="block mb-1 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Nama Brand</label>
                    <p class="text-sm font-semibold text-[#1B3A34]">{{ $brand->name }}</p>
                </div>
            </div>

            <div>
                <label class="block mb-1 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Alamat Perusahaan</label>
                <p class="text-sm text-[#1B3A34]">{{ $brand->company_address ?: '-' }}</p>
            </div>

            <hr class="border-[#DCE7E1]">
            <h3 class="text-md font-bold text-[#1B3A34]">Penandatangan Dokumen</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block mb-1 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Nama</label>
                    <p class="text-sm font-semibold text-[#1B3A34]">{{ $brand->signatory_name ?: '-' }}</p>
                </div>
                <div>
                    <label class="block mb-1 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Email</label>
                    <p class="text-sm text-[#1B3A34]">{{ $brand->signatory_email ?: '-' }}</p>
                </div>
                <div>
                    <label class="block mb-1 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Jabatan</label>
                    <p class="text-sm text-[#1B3A34]">{{ $brand->signatory_position ?: '-' }}</p>
                </div>
            </div>

            <hr class="border-[#DCE7E1]">
            <h3 class="text-md font-bold text-[#1B3A34]">Aset Visual</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Logo Brand</label>
                    @if($brand->logo)
                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="Logo" class="h-20 object-contain rounded border border-[#DCE7E1] p-2 bg-gray-50">
                    @else
                        <p class="text-sm text-[#4B5F5A] italic">Belum ada logo</p>
                    @endif
                </div>
                <div>
                    <label class="block mb-2 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Tanda Tangan</label>
                    @if($brand->signature)
                        <img src="{{ asset('storage/' . $brand->signature) }}" alt="Signature" class="h-20 object-contain rounded border border-[#DCE7E1] p-2 bg-gray-50">
                    @else
                        <p class="text-sm text-[#4B5F5A] italic">Belum ada tanda tangan</p>
                    @endif
                </div>
                <div>
                    <label class="block mb-2 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Background Sertifikat Magang</label>
                    @if($brand->internship_certificate_bg)
                        <img src="{{ asset('storage/' . $brand->internship_certificate_bg) }}" alt="BG Magang" class="h-32 object-contain rounded border border-[#DCE7E1] p-2 bg-gray-50">
                    @else
                        <p class="text-sm text-[#4B5F5A] italic">Belum ada background</p>
                    @endif
                </div>
                <div>
                    <label class="block mb-2 text-[11px] font-bold uppercase tracking-[0.06em] text-[#4B5F5A]">Background Sertifikat Webinar</label>
                    @if($brand->webinar_certificate_bg)
                        <img src="{{ asset('storage/' . $brand->webinar_certificate_bg) }}" alt="BG Webinar" class="h-32 object-contain rounded border border-[#DCE7E1] p-2 bg-gray-50">
                    @else
                        <p class="text-sm text-[#4B5F5A] italic">Belum ada background</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
