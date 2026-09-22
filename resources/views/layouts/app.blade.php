<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SI Bawang Merah') }} - Platform Monitoring & Analisis Pertanian</title>

    <!-- Meta Description & SEO -->
    <meta name="description" content="Platform Pemantauan Kondisi Lahan, Analisis Tanaman, Prediksi Hasil Panen, dan Rekomendasi Pertanian Bawang Merah Terpadu Berbasis Satelit dan AI.">
    <meta name="theme-color" content="#7A1B28">

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN dengan konfigurasi custom brand colors -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#FDF4F5',
                            100: '#FBE8E9',
                            200: '#F6D5D8',
                            300: '#EEB1B7',
                            400: '#DE808A',
                            500: '#C75160',
                            600: '#AA3544',
                            700: '#8E2533',
                            800: '#7A1B28', // Primary Shallot Maroon
                            900: '#641923',
                            950: '#3A0A10',
                        },
                        field: {
                            green: '#2E7D32',
                            lightgreen: '#E8F5E9',
                            amber: '#D97706',
                            lightamber: '#FEF3C7',
                            rose: '#E11D48',
                            lightrose: '#FFE4E6',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js for interactive UI components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Core Custom Styles -->
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF6F4;
            color: #1F2937;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #F1ECE8;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBB9B3;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #7A1B28;
        }

        /* Printable Report Styles */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
        }
    </style>
</head>
<body class="bg-[#FAF6F4] text-slate-800 antialiased min-h-screen flex flex-col selection:bg-brand-700 selection:text-white"
      x-data="{
          activeSection: 'beranda',
          selectedDistrict: 'Sukomoro',
          selectedParcelId: 'sukomoro',
          isReportModalOpen: false,
          activeLayers: {
              candirejo: true,
              seba: true,
              bagorwetan: true,
              jatirejo: true,
              sumberurip: true
          }
      }">

    <!-- Top Navigation Bar -->
    @include('partials.navbar')

    <!-- Main Content Area -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Bottom Footer -->
    @include('partials.footer')

    <!-- Report Preview & Download Modal -->
    @include('partials.report-modal')

</body>
</html>
