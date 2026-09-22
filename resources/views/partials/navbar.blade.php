<header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-rose-100/70 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-18">
            <!-- Brand Logo & Title -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-brand-800 flex items-center justify-center text-white shadow-sm group-hover:scale-105 transition-transform duration-200">
                    <!-- Shallot Onion SVG Icon -->
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C12 2 8 6.5 8 11.5C8 14.5376 9.79086 17 12 17C14.2091 17 16 14.5376 16 11.5C16 6.5 12 2 12 2Z" fill="currentColor" fill-opacity="0.9"/>
                        <path d="M12 17C9.23858 17 7 14.7614 7 12C7 8.5 10 4 12 2C14 4 17 8.5 17 12C17 14.7614 14.7614 17 12 17Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M12 17V22M9 20H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-base sm:text-lg font-bold tracking-tight text-brand-900 leading-tight">
                        SI BAWANG MERAH
                    </span>
                    <span class="text-[11px] sm:text-xs text-slate-500 font-medium leading-none">
                        Platform Analisis Lahan Pertanian Bawang Merah
                    </span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                <a href="#beranda" 
                   @click="activeSection = 'beranda'"
                   :class="activeSection === 'beranda' ? 'bg-brand-800 text-white font-semibold shadow-xs' : 'text-slate-700 hover:bg-rose-50 hover:text-brand-800 font-medium'"
                   class="px-4 py-1.5 rounded-full text-sm transition-colors duration-150">
                    Beranda
                </a>
                <a href="#peta" 
                   @click="activeSection = 'peta'"
                   :class="activeSection === 'peta' ? 'bg-brand-800 text-white font-semibold shadow-xs' : 'text-slate-700 hover:bg-rose-50 hover:text-brand-800 font-medium'"
                   class="px-4 py-1.5 rounded-full text-sm transition-colors duration-150">
                    Peta
                </a>
                <a href="#produktivitas" 
                   @click="activeSection = 'produktivitas'"
                   :class="activeSection === 'produktivitas' ? 'bg-brand-800 text-white font-semibold shadow-xs' : 'text-slate-700 hover:bg-rose-50 hover:text-brand-800 font-medium'"
                   class="px-4 py-1.5 rounded-full text-sm transition-colors duration-150">
                    Produktivitas
                </a>
                <a href="#tentang" 
                   @click="activeSection = 'tentang'"
                   :class="activeSection === 'tentang' ? 'bg-brand-800 text-white font-semibold shadow-xs' : 'text-slate-700 hover:bg-rose-50 hover:text-brand-800 font-medium'"
                   class="px-4 py-1.5 rounded-full text-sm transition-colors duration-150">
                    Tentang
                </a>
            </nav>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <a href="#analisis" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-brand-800 hover:bg-brand-900 active:bg-brand-950 text-white text-xs sm:text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Mulai Analisis</span>
                </a>

                <!-- User Profile Icon / Avatar -->
                <button type="button" 
                        title="Profil Petani / Penyuluh"
                        class="w-9 h-9 rounded-full bg-rose-100 hover:bg-rose-200 border border-rose-300/80 flex items-center justify-center text-brand-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>
