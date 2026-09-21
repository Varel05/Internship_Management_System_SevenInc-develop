@extends('pemagang.layouts.app')

@section('title', 'Membercard Digital')
@section('breadcrumb', 'Membercard')

@section('content')

<div class="max-w-lg mx-auto">

  {{-- Header --}}
  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('pemagang.documents') }}"
       class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:border-green-400 hover:text-green-700 transition">
      <i class="fas fa-arrow-left text-xs"></i>
    </a>
    <div>
      <h2 class="text-lg font-semibold text-gray-800">Membercard Digital</h2>
      <p class="text-sm text-gray-500">Kartu anggota alumni magang InternHub</p>
    </div>
  </div>

  {{-- Preview Kartu Modern --}}
  <div class="mb-6 flex justify-center py-2">
    <x-membercard :membercard="$membercard" width="380px" height="240px" />
  </div>

  {{-- Info & Tombol Download --}}
  <div class="bg-white rounded-xl border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <p class="text-sm font-semibold text-gray-800">{{ $membercard->name }}</p>
        <p class="text-xs text-gray-500 mt-0.5">
          Kode: <span class="font-mono font-semibold text-green-700">{{ $membercard->code }}</span>
        </p>
      </div>
      <span class="text-xs px-2.5 py-1 rounded-full font-semibold
        {{ $membercard->has_downloaded ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
        {{ $membercard->has_downloaded ? '✓ Sudah Diunduh' : 'Belum Diunduh' }}
      </span>
    </div>

    <a href="{{ route('pemagang.membercard.download') }}"
       class="flex items-center justify-center gap-2 w-full py-2.5 text-sm font-semibold text-white rounded-lg transition"
       style="background-color:#1a5c38;">
      <i class="fas fa-download text-xs"></i>
      Unduh Membercard (PDF)
    </a>
  </div>

</div>

@endsection
