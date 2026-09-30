@extends('layouts.app')

@section('content')
    {{-- 1. Hero Section (Headline, CTA, Foto Petani & Lokasi) --}}
    @include('partials.hero-section')

    {{-- Cakupan data mengikuti kecamatan yang dipilih pada peta. --}}
    <div class="max-w-7xl mx-auto px-4 py-4">
        @foreach($districtReports as $districtKey => $districtReport)
            <p x-show="reportDistrict === '{{ $districtKey }}'" @if($districtKey !== $activeDistrict) style="display:none" @endif>
                Data ditampilkan: <strong>{{ $districtReport['scope'] }}</strong>. Pilih kecamatan pada peta untuk mengganti ringkasan dan laporan.
            </p>
        @endforeach
    </div>

    {{-- 3. Peta Lahan Bawang Merah (Peta Interaktif Petak Sawah & Detail Wilayah) --}}
    @include('partials.map-section')

    {{-- Ringkasan evaluasi mengikuti versi model pembuat peta. --}}


    {{-- Tabel bersumber dari ekspor dataset dan hasil validasi Colab. --}}
    @include('partials.district-data')
    @include('partials.land-analysis-charts')
    <details class="max-w-7xl mx-auto px-4 py-5">
        <summary class="cursor-pointer font-semibold text-brand-800">Penjelasan teknis dan hasil pengujian model</summary>
        @include('partials.research-results')
    </details>


    {{-- 6. Tentang Sistem ShallotWatch (4 Pilar Teknologi: Sentinel-2, XGBoost, Validasi, Agronomi) --}}
    @include('partials.about-system')
@endsection
