@extends('layouts.app')

@section('content')
    {{-- 1. Hero Section (Headline, CTA, Foto Petani & Lokasi) --}}
    @include('partials.hero-section')

    {{-- 2. KPI Summary Cards (4 Kartu Ringkasan Cepat) --}}
    @include('partials.kpi-cards')

    {{-- 3. Peta Lahan Bawang Merah (Peta Interaktif Petak Sawah & Detail Wilayah) --}}
    @include('partials.map-section')

    {{-- 4. Analisis Kondisi Lahan & Tanaman (Sensor NDVI, Kelembapan, Grafik Mingguan, Siklus, Rekomendasi) --}}
    @include('partials.condition-analysis')

    {{-- 5. Prediksi Produktivitas Panen (Highlight Estimasi, Perbandingan Musim, Akurasi Model, Unduh Laporan) --}}
    @include('partials.yield-prediction')

    {{-- 6. Tentang Sistem SI Bawang Merah (4 Pilar Teknologi: Sentinel-2, XGBoost, Validasi, Agronomi) --}}
    @include('partials.about-system')
@endsection
