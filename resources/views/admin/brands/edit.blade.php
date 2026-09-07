@extends('layouts.dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">
    <div class="mb-6">
        <a href="{{ route('admin.brands.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-[#4B5F5A] hover:text-[#2D8659] transition mb-3">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali
        </a>
        <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34]">Edit Brand: {{ $brand->name }}</h1>
    </div>

    <div class="rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm p-6 max-w-4xl">
        <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Kode Brand <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $brand->code) }}" required placeholder="Contoh: svn, listmagang"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-4 py-2.5 text-sm text-[#1B3A34] outline-none focus:border-[#2D8659] focus:ring-1 focus:ring-[#2D8659] transition">
                    @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Nama Brand <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $brand->name) }}" required placeholder="Contoh: Seveninc"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-4 py-2.5 text-sm text-[#1B3A34] outline-none focus:border-[#2D8659] focus:ring-1 focus:ring-[#2D8659] transition">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Alamat Perusahaan</label>
                <textarea name="company_address" rows="3" placeholder="Alamat lengkap perusahaan"
                    class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-4 py-2.5 text-sm text-[#1B3A34] outline-none focus:border-[#2D8659] focus:ring-1 focus:ring-[#2D8659] transition resize-none">{{ old('company_address', $brand->company_address) }}</textarea>
                @error('company_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <hr class="border-[#DCE7E1]">
            <h3 class="text-md font-bold text-[#1B3A34]">Penandatangan Dokumen</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Nama</label>
                    <input type="text" name="signatory_name" value="{{ old('signatory_name', $brand->signatory_name) }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-4 py-2.5 text-sm text-[#1B3A34] outline-none focus:border-[#2D8659] focus:ring-1 focus:ring-[#2D8659] transition">
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Email</label>
                    <input type="email" name="signatory_email" value="{{ old('signatory_email', $brand->signatory_email) }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-4 py-2.5 text-sm text-[#1B3A34] outline-none focus:border-[#2D8659] focus:ring-1 focus:ring-[#2D8659] transition">
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Jabatan</label>
                    <input type="text" name="signatory_position" value="{{ old('signatory_position', $brand->signatory_position) }}"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-4 py-2.5 text-sm text-[#1B3A34] outline-none focus:border-[#2D8659] focus:ring-1 focus:ring-[#2D8659] transition">
                </div>
            </div>

            <hr class="border-[#DCE7E1]">
            <h3 class="text-md font-bold text-[#1B3A34]">Aset Visual</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Logo Brand</label>
                    @if($brand->logo)
                        <div class="mb-2"><img src="{{ asset('storage/' . $brand->logo) }}" alt="Logo" class="h-12 object-contain"></div>
                    @endif
                    <input type="file" name="logo" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-[8px] file:border-0 file:text-sm file:font-semibold file:bg-[#E8F5E9] file:text-[#2D8659] hover:file:bg-[#D4E8D6] transition">
                    <p class="text-[11px] text-[#4B5F5A] mt-1">Biarkan kosong jika tidak ingin mengubah.</p>
                    @error('logo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Tanda Tangan</label>
                    @if($brand->signature)
                        <div class="mb-2"><img src="{{ asset('storage/' . $brand->signature) }}" alt="Signature" class="h-12 object-contain bg-gray-50 border p-1 rounded"></div>
                    @endif
                    <input type="file" name="signature" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-[8px] file:border-0 file:text-sm file:font-semibold file:bg-[#E8F5E9] file:text-[#2D8659] hover:file:bg-[#D4E8D6] transition">
                    <p class="text-[11px] text-[#4B5F5A] mt-1">Biarkan kosong jika tidak ingin mengubah.</p>
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Background Sertifikat Magang</label>
                    @if($brand->internship_certificate_bg)
                        <div class="mb-2"><img src="{{ asset('storage/' . $brand->internship_certificate_bg) }}" alt="BG Magang" class="h-12 object-contain bg-gray-50 border p-1 rounded"></div>
                    @endif
                    <input type="file" name="internship_certificate_bg" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-[8px] file:border-0 file:text-sm file:font-semibold file:bg-[#E8F5E9] file:text-[#2D8659] hover:file:bg-[#D4E8D6] transition">
                </div>
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-[#1B3A34]">Background Sertifikat Webinar</label>
                    @if($brand->webinar_certificate_bg)
                        <div class="mb-2"><img src="{{ asset('storage/' . $brand->webinar_certificate_bg) }}" alt="BG Webinar" class="h-12 object-contain bg-gray-50 border p-1 rounded"></div>
                    @endif
                    <input type="file" name="webinar_certificate_bg" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-[8px] file:border-0 file:text-sm file:font-semibold file:bg-[#E8F5E9] file:text-[#2D8659] hover:file:bg-[#D4E8D6] transition">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#DCE7E1]">
                <a href="{{ route('admin.brands.index') }}" class="rounded-[9px] border border-[#DCE7E1] px-5 py-2.5 text-sm font-medium text-[#4B5F5A] hover:bg-[#F4F8F6] transition">Batal</a>
                <button type="submit" class="rounded-[9px] bg-[#2D8659] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#1F5F3F] transition shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
