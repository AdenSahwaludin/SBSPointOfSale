<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBS Smart System - Trust Score & Credit Limit Dashboard</title>
    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Outfit', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Pulse animations and glowing effects for light mode */
        .glow-green {
            box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.15);
        }
        .glow-yellow {
            box-shadow: 0 10px 30px -10px rgba(245, 158, 11, 0.15);
        }
        .glow-red {
            box-shadow: 0 10px 30px -10px rgba(239, 68, 68, 0.15);
        }
        .glow-indigo {
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.15);
        }
        
        /* Light glass card style */
        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
        }

        /* SVG Circle progress animation */
        .progress-ring__circle {
            transition: stroke-dashoffset 0.4s ease-out;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }

        /* Custom toggle design */
        input:checked ~ .dot {
            transform: translateX(100%);
            background-color: #6366f1;
        }
    </style>
</head>
<body class="bg-white text-slate-800 font-sans min-h-screen selection:bg-indigo-500 selection:text-white pb-12 overflow-x-hidden relative">

    <!-- Background decorative grids/blobs -->
    <div class="absolute top-0 left-0 w-full h-[600px] bg-gradient-to-b from-indigo-50 to-transparent pointer-events-none z-0"></div>
    <div class="absolute top-20 left-[10%] w-[350px] h-[350px] bg-indigo-500/5 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="absolute top-[400px] right-[10%] w-[400px] h-[400px] bg-emerald-500/5 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 pt-8">
        
        <!-- Header -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 mb-8 border-b border-slate-205">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 text-xs font-semibold bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full uppercase tracking-wider">
                        Smart System Engine
                    </span>
                    <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full uppercase tracking-wider">
                        v2.0 Active
                    </span>
                </div>
                <h1 class="text-3xl font-extrabold font-outfit tracking-tight text-slate-900">
                    POS-SBS Credit &amp; Trust Analytics
                </h1>
                <p class="text-slate-500 mt-1 text-sm">
                    Visualisasi Formula Penilaian Risiko Kredit &amp; Plafon Cicilan Otomatis
                </p>
            </div>
            
            <!-- Quick Preset Switcher (Extremely useful for PPT Screenshots) -->
            <div class="bg-slate-50 p-1.5 rounded-xl border border-slate-200 flex gap-2">
                <button onclick="applyPreset('ideal')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 bg-indigo-600 text-white shadow-lg shadow-indigo-600/25" id="btn-preset-ideal">
                    Pelanggan Ideal
                </button>
                <button onclick="applyPreset('average')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700 hover:bg-slate-100" id="btn-preset-average">
                    Sedang
                </button>
                <button onclick="applyPreset('risky')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700 hover:bg-slate-100" id="btn-preset-risky">
                    Berisiko Tinggi
                </button>
            </div>
        </header>

        <!-- MAIN GRID LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- LEFT PANEL: PRESENTATION SCREENSHOT HIGHLIGHTS (Col 7) -->
            <div class="lg:col-span-7 flex flex-col gap-6">
                
                <!-- Trust Score & Limit Overview (Wow Factor Dashboard) -->
                <div class="glass-card rounded-3xl p-6 glow-indigo relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-indigo-500/5 to-transparent rounded-bl-full pointer-events-none"></div>
                    
                    <h2 class="text-lg font-bold font-outfit text-indigo-605 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Hasil Analisis Smart System
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        
                        <!-- Circular Gauge for Trust Score -->
                        <div class="flex flex-col items-center justify-center p-4 bg-slate-50 rounded-2xl border border-slate-150">
                            <span class="text-xs uppercase font-semibold text-slate-400 tracking-wider mb-2">Trust Score (TS)</span>
                            
                            <div class="relative flex items-center justify-center">
                                <!-- SVG Circular Progress -->
                                <svg class="w-40 h-40">
                                    <!-- Background Circle -->
                                    <circle class="text-slate-100" stroke-width="8" stroke="currentColor" fill="transparent" r="70" cx="80" cy="80"/>
                                    <!-- Foreground Progress Circle -->
                                    <circle id="ts-gauge" class="progress-ring__circle transition-all duration-500" stroke-width="10" stroke-linecap="round" stroke="currentColor" fill="transparent" r="70" cx="80" cy="80" stroke-dasharray="439.8" stroke-dashoffset="439.8"/>
                                </svg>
                                <!-- Score Text inside -->
                                <div class="absolute flex flex-col items-center justify-center">
                                    <span id="ts-value-display" class="text-4xl font-extrabold font-outfit text-slate-800">50</span>
                                    <span class="text-[10px] text-slate-400 font-semibold tracking-widest uppercase">Skor</span>
                                </div>
                            </div>
                            
                            <!-- Eligibility Status Badge -->
                            <div id="ts-badge" class="mt-4 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition-all duration-300">
                                <span class="w-2.5 h-2.5 rounded-full animate-pulse bg-current"></span>
                                <span id="ts-status-text">Memproses</span>
                            </div>
                        </div>

                        <!-- Credit Limit Card (Premium Bank Card Mockup) -->
                        <div class="flex flex-col gap-4">
                            <span class="text-xs uppercase font-semibold text-slate-450 tracking-wider">Plafon Kredit Tersedia</span>
                            
                            <!-- Virtual Credit Card Glass -->
                            <div id="credit-card-ui" class="relative overflow-hidden w-full h-44 rounded-2xl p-5 flex flex-col justify-between text-slate-800 transition-all duration-500 shadow-lg border border-slate-200/80 bg-gradient-to-br from-indigo-50 via-indigo-100/70 to-slate-100">
                                <!-- Background card decorations -->
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white/40 rounded-full -mr-8 -mt-8 pointer-events-none"></div>
                                <div class="absolute bottom-0 left-0 w-24 h-24 bg-indigo-500/5 rounded-full -ml-8 -mb-8 pointer-events-none"></div>
                                
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-[10px] uppercase font-semibold tracking-wider text-slate-500">SBS Credit Limit</p>
                                        <h3 class="text-md font-bold font-outfit text-slate-800">PAYLATER ENGINE</h3>
                                    </div>
                                    <svg class="w-8 h-8 text-slate-400" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/>
                                    </svg>
                                </div>
                                
                                <div class="my-auto">
                                    <div class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Maksimum Limit Belanja</div>
                                    <div id="cl-value-display" class="text-2xl md:text-3xl font-extrabold font-outfit tracking-wide text-slate-900 transition-all duration-300">Rp 0</div>
                                </div>

                                <div class="flex justify-between items-center text-[10px] text-slate-500">
                                    <div>
                                        <span>Faktor Multiplier: </span>
                                        <span id="cl-multiplier-display" class="font-bold text-slate-850">1.0x</span>
                                    </div>
                                    <span id="cl-status-badge-inside" class="uppercase tracking-widest font-semibold px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded border border-emerald-200">ACTIVE</span>
                                </div>
                            </div>

                            <!-- Limit Base Summary -->
                            <div class="bg-slate-50 border border-slate-200/80 p-3.5 rounded-xl text-xs flex justify-between items-center">
                                <div>
                                    <span class="text-slate-500">Limit Base Utama (Maksimum L1, L2, L3):</span>
                                    <div id="cl-base-display" class="font-bold text-slate-700 mt-0.5 text-sm">Rp 0</div>
                                </div>
                                <div class="text-right">
                                    <span class="text-slate-500">Tunggakan Aktif:</span>
                                    <div id="cl-arrears-status" class="font-bold text-emerald-600 mt-0.5">TIDAK ADA</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Equation Breakdown & Analysis Flow -->
                <div class="glass-card rounded-3xl p-6 relative">
                    <h2 class="text-lg font-bold font-outfit text-slate-750 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Logika Perhitungan Smart System
                    </h2>

                    <!-- Stepper Flow of calculations -->
                    <div class="space-y-4">
                        
                        <!-- Step 1: Trust Score -->
                        <div class="relative pl-6 border-l-2 border-indigo-100 pb-2">
                            <!-- Bullet -->
                            <div class="absolute -left-[7px] top-1.5 w-3 h-3 bg-indigo-500 rounded-full ring-4 ring-white"></div>
                            
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Tahap 1: Evaluasi Trust Score (TS)</h3>
                                <span id="ts-formula-total" class="text-xs font-bold text-indigo-650 px-2 py-0.5 bg-indigo-50 rounded border border-indigo-150">TS = 50</span>
                            </div>
                            <p class="text-xs text-slate-400 mb-3">
                                Formula: 50 + P<sub>umur</sub> + P<sub>tepat</sub> + P<sub>frekuensi</sub> + P<sub>nilai</sub> - P<sub>telat</sub> - P<sub>gagal</sub> - P<sub>tunggakan</sub>
                            </p>
                            
                            <!-- Grid details of math -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                                <div class="bg-slate-50 p-2 rounded border border-slate-200/80">
                                    <span class="text-slate-450 block">Baseline</span>
                                    <span class="font-mono text-slate-600 font-semibold">+50 Poin</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-slate-200/80">
                                    <span class="text-slate-455 block">Umur Akun (P<sub>umur</sub>)</span>
                                    <span id="math-p-umur" class="font-mono text-emerald-600 font-semibold">+0 Poin</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-slate-200/80">
                                    <span class="text-slate-455 block">Tepat Waktu (P<sub>tepat</sub>)</span>
                                    <span id="math-p-tepat" class="font-mono text-emerald-600 font-semibold">+0 Poin</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-slate-200/80">
                                    <span class="text-slate-455 block">Terlambat (P<sub>telat</sub>)</span>
                                    <span id="math-p-telat" class="font-mono text-rose-600 font-semibold">-0 Poin</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-slate-200/80">
                                    <span class="text-slate-455 block">Gagal Bayar (P<sub>gagal</sub>)</span>
                                    <span id="math-p-gagal" class="font-mono text-rose-600 font-semibold">-0 Poin</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-slate-200/80">
                                    <span class="text-slate-455 block">Frekuensi (P<sub>freq</sub>)</span>
                                    <span id="math-p-freq" class="font-mono text-emerald-600 font-semibold">+0 Poin</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-slate-200/80">
                                    <span class="text-slate-455 block">Nilai Transaksi (P<sub>nilai</sub>)</span>
                                    <span id="math-p-nilai" class="font-mono text-emerald-600 font-semibold">+0 Poin</span>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-slate-200/80">
                                    <span class="text-slate-455 block">Tunggakan (P<sub>tung</sub>)</span>
                                    <span id="math-p-tung" class="font-mono text-rose-600 font-semibold">-0 Poin</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Limit Base -->
                        <div class="relative pl-6 border-l-2 border-indigo-100 pb-2">
                            <!-- Bullet -->
                            <div class="absolute -left-[7px] top-1.5 w-3 h-3 bg-indigo-500 rounded-full ring-4 ring-white"></div>
                            
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Tahap 2: Kalkulasi Limit Base</h3>
                                <span id="limit-base-total" class="text-xs font-bold text-indigo-650 px-2 py-0.5 bg-indigo-50 rounded border border-indigo-150">Max L1, L2, L3</span>
                            </div>
                            <p class="text-xs text-slate-400 mb-3">
                                Dipilih nilai terbesar dari 3 pendekatan belanja pelanggan.
                            </p>
                            
                            <!-- Grid details of L1 L2 L3 -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                <div id="card-l1" class="bg-slate-50 p-3 rounded-lg border border-slate-200 transition-all duration-300">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-slate-500 font-medium">L1 (½ Trans. Max)</span>
                                        <span id="badge-l1" class="text-[9px] px-1.5 py-0.2 bg-slate-200 text-slate-600 rounded uppercase font-semibold">Active</span>
                                    </div>
                                    <div id="val-l1" class="font-bold text-slate-700">Rp 0</div>
                                    <p id="desc-l1" class="text-[10px] text-slate-400 mt-1">1/2 dari transaksi terbesar</p>
                                </div>
                                <div id="card-l2" class="bg-slate-50 p-3 rounded-lg border border-slate-200 transition-all duration-300">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-slate-500 font-medium">L2 (½ Rata3 Max)</span>
                                        <span id="badge-l2" class="text-[9px] px-1.5 py-0.2 bg-slate-200 text-slate-600 rounded uppercase font-semibold">Active</span>
                                    </div>
                                    <div id="val-l2" class="font-bold text-slate-700">Rp 0</div>
                                    <p class="text-[10px] text-slate-400 mt-1">1/2 dari rata-rata 3 trans. terbesar</p>
                                </div>
                                <div id="card-l3" class="bg-slate-50 p-3 rounded-lg border border-slate-200 transition-all duration-300">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-slate-500 font-medium">L3 (30% Spend 6B)</span>
                                        <span id="badge-l3" class="text-[9px] px-1.5 py-0.2 bg-slate-200 text-slate-600 rounded uppercase font-semibold">Active</span>
                                    </div>
                                    <div id="val-l3" class="font-bold text-slate-700">Rp 0</div>
                                    <p class="text-[10px] text-slate-400 mt-1">30% dari total belanja 6 bulan terakhir</p>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Final Limit -->
                        <div class="relative pl-6 pb-2">
                            <!-- Bullet -->
                            <div class="absolute -left-[7px] top-1.5 w-3 h-3 bg-indigo-500 rounded-full ring-4 ring-white"></div>
                            
                            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-1">Tahap 3: Keputusan &amp; Plafon Akhir</h3>
                            <p class="text-xs text-slate-400">
                                Formula Akhir: <strong class="text-slate-700">Limit Base &times; Faktor TS</strong> (Dibulatkan ke ribuan terdekat, Min Rp100.000).
                            </p>
                            <div class="mt-3 p-3 bg-slate-550/50 rounded-xl border border-slate-200 flex items-center justify-between text-xs bg-slate-50">
                                <div class="flex items-center gap-3">
                                    <div class="bg-indigo-50 p-2 rounded-lg text-indigo-600 border border-indigo-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <span class="text-slate-500 block">Kalkulasi Final:</span>
                                        <span id="final-calc-formula" class="font-semibold text-slate-650">Rp 0 &times; 1.0x = Rp 0</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-slate-500 block">Status Limit:</span>
                                    <span id="final-limit-badge" class="font-bold text-emerald-600 uppercase tracking-wider">AKTIF</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- RIGHT PANEL: INTERACTIVE INPUT CONTROLS (Col 5) -->
            <div class="lg:col-span-5 flex flex-col gap-6">
                
                <div class="glass-card rounded-3xl p-6 relative">
                    <h2 class="text-lg font-bold font-outfit text-slate-700 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-650" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Simulator Input Parameter
                    </h2>

                    <!-- Sub-section: Customer Behavior -->
                    <div class="mb-6 pb-6 border-b border-slate-200">
                        <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-4">1. Profil &amp; Perilaku Transaksi</h3>
                        
                        <!-- Account Age -->
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-slate-500 mb-2">Umur Akun Pelanggan (P<sub>umur</sub>)</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="setInput('accountAge', '<30')" id="age-low" class="px-3 py-2 text-xs font-medium rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-650">
                                    &lt; 30 Hari<br><span class="text-[10px] text-slate-400">(+0 poin)</span>
                                </button>
                                <button type="button" onclick="setInput('accountAge', '30-179')" id="age-mid" class="px-3 py-2 text-xs font-medium rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-650">
                                    30-179 Hari<br><span class="text-[10px] text-slate-400">(+10 poin)</span>
                                </button>
                                <button type="button" onclick="setInput('accountAge', '>=180')" id="age-high" class="px-3 py-2 text-xs font-medium rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700">
                                    &ge; 180 Hari<br><span class="text-[10px] text-indigo-500">(+20 poin)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Sliders for payments -->
                        <div class="space-y-4 mt-4">
                            <!-- On-Time Payments -->
                            <div>
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="text-slate-500 font-semibold">Pembayaran Tepat Waktu (P<sub>tepat</sub>)</span>
                                    <span class="font-mono text-emerald-600 font-bold" id="label-on-time">10x (+20 Poin)</span>
                                </div>
                                <input type="range" id="input-on-time" min="0" max="15" value="10" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600" oninput="updateValues()">
                                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                    <span>0x</span>
                                    <span>10x (Max limit +20)</span>
                                    <span>15x</span>
                                </div>
                            </div>

                            <!-- Late Payments -->
                            <div>
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="text-rose-600 font-semibold">Pembayaran Terlambat (P<sub>telat</sub>)</span>
                                    <span class="font-mono text-rose-600 font-bold" id="label-late">0x (-0 Poin)</span>
                                </div>
                                <input type="range" id="input-late" min="0" max="10" value="0" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-rose-650" oninput="updateValues()">
                                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                    <span>0x (-0)</span>
                                    <span>5x (-25)</span>
                                    <span>10x (-50)</span>
                                </div>
                            </div>

                            <!-- Failed Payments -->
                            <div>
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="text-rose-600 font-semibold">Gagal Bayar / Default (P<sub>gagal</sub>)</span>
                                    <span class="font-mono text-rose-600 font-bold" id="label-failed">0x (-0 Poin)</span>
                                </div>
                                <input type="range" id="input-failed" min="0" max="4" value="0" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-rose-650" oninput="updateValues()">
                                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                    <span>0x (-0)</span>
                                    <span>2x (-50)</span>
                                    <span>4x (-100)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Switched/Boolean modifiers -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                            
                            <!-- Freq Switch -->
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700">Frekuensi Belanja</label>
                                    <span class="text-[10px] text-slate-450">&ge;3 Trans/Bln dlm 3 Bln (+5)</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="input-freq" checked class="sr-only peer" onchange="updateValues()">
                                    <div class="w-9 h-5 bg-slate-300 rounded-full peer peer-focus:outline-none peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-650"></div>
                                </label>
                            </div>

                            <!-- Value Switch -->
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700">Nilai Transaksi</label>
                                    <span class="text-[10px] text-slate-450">Rata-rata &gt; Median Toko (+5)</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="input-value-above" checked class="sr-only peer" onchange="updateValues()">
                                    <div class="w-9 h-5 bg-slate-350 rounded-full peer peer-focus:outline-none peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-650"></div>
                                </label>
                            </div>

                            <!-- Arrears Switch -->
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-55/60 border border-rose-200 md:col-span-2 bg-rose-50">
                                <div>
                                    <label class="block text-xs font-semibold text-rose-600">Tunggakan Aktif (P<sub>tunggakan</sub>)</label>
                                    <span class="text-[10px] text-slate-500">Memiliki status tagihan jatuh tempo (Due / Late). Penalti -10 &amp; Limit langsung Rp0.</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="input-arrears" class="sr-only peer" onchange="updateValues()">
                                    <div class="w-9 h-5 bg-slate-350 rounded-full peer peer-focus:outline-none peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-rose-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Sub-section: Spending Data for Credit Limit -->
                    <div>
                        <h3 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-4">2. Riwayat Belanja (Batas Limit Base)</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Max Transaction -->
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Transaksi Terbesar (Rp)</label>
                                <input type="text" id="input-max-trans" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); updateValues()">
                            </div>

                            <!-- Shop Median (to detect anomalies) -->
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Median Transaksi Toko (Rp)</label>
                                <input type="text" id="input-median-shop" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); updateValues()">
                                <p class="text-[9px] text-slate-450 mt-1">Jika Transaksi Max > 3x Median Toko, maka L1 dinonaktifkan (Anomali).</p>
                            </div>

                            <!-- 2nd & 3rd largest (for L2 calculation) -->
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Transaksi Terbesar Ke-2 (Rp)</label>
                                <input type="text" id="input-second-max" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); updateValues()">
                            </div>

                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Transaksi Terbesar Ke-3 (Rp)</label>
                                <input type="text" id="input-third-max" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); updateValues()">
                            </div>

                            <!-- Total spending in last 6 months (for L3) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs text-slate-500 mb-1">Total Belanja 6 Bulan Terakhir (Rp)</label>
                                <input type="text" id="input-total-6m" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); updateValues()">
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- System Matrix Reference Table (Great for slide inclusion) -->
        <footer class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 border border-slate-200 rounded-2xl p-6 text-xs text-slate-500">
            <div>
                <h4 class="font-bold text-slate-700 mb-2 font-outfit uppercase tracking-wider text-[10px]">Tabel Skrining Trust Score</h4>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[10px] text-slate-400 uppercase">
                            <th class="py-1">Interval Skor</th>
                            <th class="py-1">Klasifikasi Kelayakan</th>
                            <th class="py-1">Faktor Pengali (TS Factor)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-slate-200/60">
                            <td class="py-1.5 font-mono text-rose-600">TS &lt; 55</td>
                            <td class="py-1.5">Ditolak / Gagal Layak</td>
                            <td class="py-1.5 font-mono">0.0x</td>
                        </tr>
                        <tr class="border-b border-slate-200/60">
                            <td class="py-1.5 font-mono text-amber-600">TS 55 - 59</td>
                            <td class="py-1.5">Dipertimbangkan (Plafond Terbatas)</td>
                            <td class="py-1.5 font-mono">0.5x</td>
                        </tr>
                        <tr class="border-b border-slate-200/60">
                            <td class="py-1.5 font-mono text-amber-600">TS 60 - 74</td>
                            <td class="py-1.5 font-mono text-slate-600">Dipertimbangkan (Layak Standar)</td>
                            <td class="py-1.5 font-mono">1.0x</td>
                        </tr>
                        <tr class="border-b border-slate-200/60">
                            <td class="py-1.5 font-mono text-emerald-600">TS 75 - 89</td>
                            <td class="py-1.5">Layak (Potensi Naik Limit)</td>
                            <td class="py-1.5 font-mono">1.3x</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 font-mono text-emerald-650">TS &ge; 90</td>
                            <td class="py-1.5">Sangat Layak (Limit Maksimal)</td>
                            <td class="py-1.5 font-mono">1.5x</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <h4 class="font-bold text-slate-700 mb-2 font-outfit uppercase tracking-wider text-[10px]">Ketentuan Perhitungan Limit Base &amp; Pembulatan</h4>
                <ul class="list-disc pl-4 space-y-1.5 text-slate-500">
                    <li><strong class="text-slate-600">Deteksi Anomali L1:</strong> Jika Transaksi Terbesar &gt; 3&times; Median Toko, maka transaksi tersebut dianggap anomali dan L1 dikesampingkan (dihitung Rp0).</li>
                    <li><strong class="text-slate-300">Plafon Minimum:</strong> Jika perhitungan menghasilkan plafon &gt; Rp0 tetapi di bawah Rp100.000, maka akan dibulatkan ke atas menjadi Rp100.000.</li>
                    <li><strong class="text-slate-350">Arrears Override:</strong> Keberadaan tunggakan aktif otomatis memaksa plafon kredit menjadi Rp0 dan mengurangi poin Trust Score secara signifikan.</li>
                    <li><strong class="text-slate-350">Pembulatan Ribuan:</strong> Semua plafon akhir dibulatkan ke ribuan terdekat (e.g. Rp1.234.500 &rarr; Rp1.235.000) demi kebersihan transaksi.</li>
                </ul>
            </div>
        </footer>

    </div>

    <!-- Interactive script -->
    <script>
        // State variables
        let state = {
            accountAge: '>=180', // options: '<30', '30-179', '>=180'
            onTimePayments: 10,
            latePayments: 0,
            failedPayments: 0,
            freqHigh: true,
            valAboveMedian: true,
            hasArrears: false,
            maxTransaction: 2000000,
            secondMaxTransaction: 1500000,
            thirdMaxTransaction: 1000000,
            medianTransaction: 800000,
            totalSpending6M: 12000000
        };

        // Preset configuration data (using round, clean numbers)
        const presets = {
            ideal: {
                accountAge: '>=180',
                onTimePayments: 10,
                latePayments: 0,
                failedPayments: 0,
                freqHigh: true,
                valAboveMedian: true,
                hasArrears: false,
                maxTransaction: 2000000,
                secondMaxTransaction: 1500000,
                thirdMaxTransaction: 1000000,
                medianTransaction: 800000,
                totalSpending6M: 12000000
            },
            average: {
                accountAge: '30-179',
                onTimePayments: 5,
                latePayments: 1,
                failedPayments: 0,
                freqHigh: false,
                valAboveMedian: true,
                hasArrears: false,
                maxTransaction: 1000000,
                secondMaxTransaction: 800000,
                thirdMaxTransaction: 600000,
                medianTransaction: 400000,
                totalSpending6M: 3000000
            },
            risky: {
                accountAge: '<30',
                onTimePayments: 2,
                latePayments: 3,
                failedPayments: 1,
                freqHigh: false,
                valAboveMedian: false,
                hasArrears: true,
                maxTransaction: 500000,
                secondMaxTransaction: 300000,
                thirdMaxTransaction: 200000,
                medianTransaction: 300000,
                totalSpending6M: 1000000
            }
        };

        // Format raw input with dots while typing (maintains cursor pos)
        function formatNumberInput(input) {
            let selectionStart = input.selectionStart;
            let originalLength = input.value.length;
            
            let value = input.value.replace(/\D/g, '');
            if (value === "") {
                input.value = "";
                return;
            }
            let formatted = new Intl.NumberFormat('id-ID').format(parseInt(value));
            input.value = formatted;
            
            let newLength = formatted.length;
            input.selectionStart = selectionStart + (newLength - originalLength);
            input.selectionEnd = selectionStart + (newLength - originalLength);
        }

        // Get raw numeric value from format
        function getRawValue(id) {
            let val = document.getElementById(id).value;
            return parseFloat(val.replace(/\./g, '')) || 0;
        }

        // Programmatically set and format input fields
        function setInputValueFormatted(id, val) {
            const el = document.getElementById(id);
            if (val === 0) {
                el.value = "";
            } else {
                el.value = new Intl.NumberFormat('id-ID').format(val);
            }
        }

        // Set input functions
        function setInput(key, val) {
            state[key] = val;
            
            // Update age button UI classes
            if (key === 'accountAge') {
                const btnLow = document.getElementById('age-low');
                const btnMid = document.getElementById('age-mid');
                const btnHigh = document.getElementById('age-high');
                
                [btnLow, btnMid, btnHigh].forEach(b => {
                    b.className = "px-3 py-2 text-xs font-medium rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-650";
                });
                
                if (val === '<30') {
                    btnLow.className = "px-3 py-2 text-xs font-medium rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700";
                } else if (val === '30-179') {
                    btnMid.className = "px-3 py-2 text-xs font-medium rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700";
                } else if (val === '>=180') {
                    btnHigh.className = "px-3 py-2 text-xs font-medium rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700";
                }
            }
            updateValues();
        }

        // Apply a preset
        function applyPreset(presetKey) {
            // Update state with preset data
            state = { ...presets[presetKey] };
            
            // Sync Input fields (formatted with dots)
            document.getElementById('input-on-time').value = state.onTimePayments;
            document.getElementById('input-late').value = state.latePayments;
            document.getElementById('input-failed').value = state.failedPayments;
            document.getElementById('input-freq').checked = state.freqHigh;
            document.getElementById('input-value-above').checked = state.valAboveMedian;
            document.getElementById('input-arrears').checked = state.hasArrears;
            
            setInputValueFormatted('input-max-trans', state.maxTransaction);
            setInputValueFormatted('input-second-max', state.secondMaxTransaction);
            setInputValueFormatted('input-third-max', state.thirdMaxTransaction);
            setInputValueFormatted('input-median-shop', state.medianTransaction);
            setInputValueFormatted('input-total-6m', state.totalSpending6M);
            
            // Sync Age buttons
            setInput('accountAge', state.accountAge);

            // Change style of selected preset button
            ['ideal', 'average', 'risky'].forEach(p => {
                const btn = document.getElementById(`btn-preset-${p}`);
                if (p === presetKey) {
                    btn.className = "px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 bg-indigo-600 text-white shadow-lg shadow-indigo-600/25";
                } else {
                    btn.className = "px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700 hover:bg-slate-100";
                }
            });
        }

        // Helper formatting Rupiah
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        // Set gauge circle progress
        function setGaugeValue(percent, colorClass) {
            const circle = document.getElementById('ts-gauge');
            const radius = circle.r.baseVal.value;
            const circumference = radius * 2 * Math.PI;
            
            circle.style.strokeDasharray = `${circumference} ${circumference}`;
            const offset = circumference - (percent / 100) * circumference;
            circle.style.strokeDashoffset = offset;
            
            // Update color class of the circle SVG stroke
            circle.className.baseVal = "progress-ring__circle transition-all duration-500 " + colorClass;
        }

        // Update values and recalculate all rules
        function updateValues() {
            // Read inputs from controls (except accountAge which is set via click)
            state.onTimePayments = parseInt(document.getElementById('input-on-time').value);
            state.latePayments = parseInt(document.getElementById('input-late').value);
            state.failedPayments = parseInt(document.getElementById('input-failed').value);
            state.freqHigh = document.getElementById('input-freq').checked;
            state.valAboveMedian = document.getElementById('input-value-above').checked;
            state.hasArrears = document.getElementById('input-arrears').checked;
            
            state.maxTransaction = getRawValue('input-max-trans');
            state.secondMaxTransaction = getRawValue('input-second-max');
            state.thirdMaxTransaction = getRawValue('input-third-max');
            state.medianTransaction = getRawValue('input-median-shop');
            state.totalSpending6M = getRawValue('input-total-6m');

            // 1. Calculate P_umur
            let P_umur = 0;
            if (state.accountAge === '30-179') P_umur = 10;
            else if (state.accountAge === '>=180') P_umur = 20;

            // 2. Calculate P_tepat
            let P_tepat = Math.min(20, 2 * state.onTimePayments);

            // 3. Calculate P_telat
            let P_telat = 5 * state.latePayments;

            // 4. Calculate P_gagal
            let P_gagal = 25 * state.failedPayments;

            // 5. Calculate P_freq
            let P_freq = state.freqHigh ? 5 : 0;

            // 6. Calculate P_nilai
            let P_nilai = state.valAboveMedian ? 5 : 0;

            // 7. Calculate P_tunggakan
            let P_tunggakan = state.hasArrears ? 10 : 0;

            // Calculate final Trust Score
            let trustScore = 50 + P_umur + P_tepat + P_freq + P_nilai - P_telat - P_gagal - P_tunggakan;
            trustScore = Math.max(0, Math.min(100, trustScore));

            // Determine status, multiplier and styling
            let statusText = "";
            let statusBadgeClass = "";
            let cardBgClass = "";
            let gaugeColorClass = "";
            let multiplier = 1.0;

            if (trustScore < 55) {
                statusText = "Ditolak (Rejected)";
                statusBadgeClass = "bg-rose-50 text-rose-600 border border-rose-200";
                cardBgClass = "from-rose-50 via-rose-100/75 to-slate-100 glow-red border-rose-200";
                gaugeColorClass = "text-rose-500";
                multiplier = 0.0;
            } else if (trustScore >= 55 && trustScore <= 69) {
                statusText = "Dipertimbangkan (Considered)";
                statusBadgeClass = "bg-amber-50 text-amber-600 border border-amber-200";
                cardBgClass = "from-amber-50 via-amber-100/75 to-slate-100 glow-yellow border-amber-200";
                gaugeColorClass = "text-amber-500";
                
                // Multiplier ranges
                if (trustScore >= 55 && trustScore <= 59) multiplier = 0.5;
                else multiplier = 1.0; // 60-69 is 1.0
            } else { // TS >= 70
                statusText = "Layak (Eligible)";
                statusBadgeClass = "bg-emerald-50 text-emerald-600 border border-emerald-200";
                cardBgClass = "from-indigo-50 via-indigo-100/75 to-slate-100 glow-indigo border-indigo-200";
                gaugeColorClass = "text-emerald-500";
                
                // Multiplier ranges
                if (trustScore >= 70 && trustScore <= 74) multiplier = 1.0;
                else if (trustScore >= 75 && trustScore <= 89) multiplier = 1.3;
                else multiplier = 1.5; // >= 90
            }

            // Sync Slider Labels
            document.getElementById('label-on-time').textContent = `${state.onTimePayments}x (+${P_tepat} Poin)`;
            document.getElementById('label-late').textContent = `${state.latePayments}x (-${P_telat} Poin)`;
            document.getElementById('label-failed').textContent = `${state.failedPayments}x (-${P_gagal} Poin)`;

            // Sync Equation UI
            document.getElementById('ts-value-display').textContent = trustScore;
            document.getElementById('ts-status-text').textContent = statusText;
            
            const badge = document.getElementById('ts-badge');
            badge.className = "mt-4 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition-all duration-300 " + statusBadgeClass;

            setGaugeValue(trustScore, gaugeColorClass);

            // Sync detailed math panel
            document.getElementById('ts-formula-total').textContent = `Total TS = ${trustScore}`;
            
            const updateMathLabel = (elId, val, isNegative = false) => {
                const el = document.getElementById(elId);
                if (val === 0) {
                    el.textContent = `0 Poin`;
                    el.className = "font-mono text-slate-400 font-semibold";
                } else {
                    el.textContent = `${isNegative ? '-' : '+'}${Math.abs(val)} Poin`;
                    el.className = `font-mono ${isNegative ? 'text-rose-600' : 'text-emerald-600'} font-semibold`;
                }
            };

            updateMathLabel('math-p-umur', P_umur);
            updateMathLabel('math-p-tepat', P_tepat);
            updateMathLabel('math-p-telat', P_telat, true);
            updateMathLabel('math-p-gagal', P_gagal, true);
            updateMathLabel('math-p-freq', P_freq);
            updateMathLabel('math-p-nilai', P_nilai);
            updateMathLabel('math-p-tung', P_tunggakan, true);

            // Limit Base Calculations (L1, L2, L3)
            // L1 = 1/2 of max transaction. Anomaly check: if max > 3 * median transaction -> ignored (L1 = 0)
            const isAnomaly = state.maxTransaction > (3 * state.medianTransaction);
            const L1 = isAnomaly ? 0 : (state.maxTransaction / 2);
            
            // L2 = 1/2 of avg of 3 largest
            const L2 = ((state.maxTransaction + state.secondMaxTransaction + state.thirdMaxTransaction) / 3) / 2;
            
            // L3 = 30% of total spend in 6M
            const L3 = state.totalSpending6M * 0.3;

            // Pick Maximum for Limit Base
            const limitBase = Math.max(L1, L2, L3);

            // Update Limit Base Card UI details
            document.getElementById('val-l1').textContent = formatRupiah(L1);
            document.getElementById('val-l2').textContent = formatRupiah(L2);
            document.getElementById('val-l3').textContent = formatRupiah(L3);
            
            // Display anomaly warning on L1 if exists
            const descL1 = document.getElementById('desc-l1');
            const badgeL1 = document.getElementById('badge-l1');
            const cardL1 = document.getElementById('card-l1');
            if (isAnomaly) {
                descL1.innerHTML = "<span class='text-rose-600 font-semibold'>Anomali terdeteksi! (Max > 3x Median). Transaksi diabaikan.</span>";
                badgeL1.className = "text-[9px] px-1.5 py-0.2 bg-rose-50 text-rose-650 rounded uppercase font-semibold border border-rose-200";
                badgeL1.textContent = "Ignored";
                cardL1.className = "bg-rose-50 p-3 rounded-lg border border-rose-150 transition-all duration-300 opacity-60";
            } else {
                descL1.textContent = "1/2 dari transaksi terbesar";
                badgeL1.className = "text-[9px] px-1.5 py-0.2 bg-slate-200 text-slate-600 rounded uppercase font-semibold";
                badgeL1.textContent = "Active";
                cardL1.className = "bg-slate-50 p-3 rounded-lg border border-slate-200/80 transition-all duration-300";
            }

            // Highlight which Limit Base is chosen as maximum
            const cardL2 = document.getElementById('card-l2');
            const cardL3 = document.getElementById('card-l3');
            
            [cardL1, cardL2, cardL3].forEach(c => {
                if (c.className.includes("border-indigo-200")) {
                    c.className = c.className.replace(" border-indigo-200 bg-indigo-50 shadow-sm", " border-slate-200/80 bg-slate-50");
                }
            });

            if (limitBase === L1 && !isAnomaly) {
                cardL1.className = "bg-indigo-50 p-3 rounded-lg border border-indigo-200 shadow-sm transition-all duration-300";
            } else if (limitBase === L2) {
                cardL2.className = "bg-indigo-50 p-3 rounded-lg border border-indigo-200 shadow-sm transition-all duration-300";
            } else if (limitBase === L3) {
                cardL3.className = "bg-indigo-50 p-3 rounded-lg border border-indigo-200 shadow-sm transition-all duration-300";
            }

            // Sync Limit Base value display
            document.getElementById('cl-base-display').textContent = formatRupiah(limitBase);

            // 3. Calculate Final Credit Limit
            let finalCreditLimit = limitBase * multiplier;

            // Apply rules:
            // - If there are active arrears (tunggakan), final limit is 0
            if (state.hasArrears) {
                finalCreditLimit = 0;
                document.getElementById('cl-arrears-status').textContent = "ADA TUNGGAKAN (BLOCKED)";
                document.getElementById('cl-arrears-status').className = "font-bold text-rose-600 mt-0.5";
                document.getElementById('final-limit-badge').textContent = "BLOCKED";
                document.getElementById('final-limit-badge').className = "font-bold text-rose-500 uppercase tracking-wider";
            } else {
                document.getElementById('cl-arrears-status').textContent = "TIDAK ADA";
                document.getElementById('cl-arrears-status').className = "font-bold text-emerald-600 mt-0.5";
                
                if (trustScore < 55) {
                    document.getElementById('final-limit-badge').textContent = "DITOLAK";
                    document.getElementById('final-limit-badge').className = "font-bold text-rose-600 uppercase tracking-wider";
                } else {
                    document.getElementById('final-limit-badge').textContent = "AKTIF";
                    document.getElementById('final-limit-badge').className = "font-bold text-emerald-600 uppercase tracking-wider";
                }
            }

            // Rounding to nearest thousand
            finalCreditLimit = Math.round(finalCreditLimit / 1000) * 1000;

            // Min limit rule: if final limit > 0 and final limit < 100000 -> set to 100000
            if (finalCreditLimit > 0 && finalCreditLimit < 100000) {
                finalCreditLimit = 100000;
            }

            // Sync Card displays
            document.getElementById('cl-display').textContent = formatRupiah(finalCreditLimit);
            document.getElementById('cl-value-display').textContent = formatRupiah(finalCreditLimit);
            document.getElementById('cl-multiplier-display').textContent = `${multiplier.toFixed(1)}x`;

            const clBadgeInside = document.getElementById('cl-status-badge-inside');
            if (state.hasArrears) {
                clBadgeInside.textContent = "BLOCKED";
                clBadgeInside.className = "uppercase tracking-widest font-semibold px-2 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-200";
            } else if (trustScore < 55) {
                clBadgeInside.textContent = "DITOLAK";
                clBadgeInside.className = "uppercase tracking-widest font-semibold px-2 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-200";
            } else if (trustScore >= 55 && trustScore <= 69) {
                clBadgeInside.textContent = "TERBATAS";
                clBadgeInside.className = "uppercase tracking-widest font-semibold px-2 py-0.5 rounded bg-amber-50 text-amber-600 border border-amber-200";
            } else {
                clBadgeInside.textContent = "ACTIVE";
                clBadgeInside.className = "uppercase tracking-widest font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 border border-emerald-200";
            }

            // Change Credit Card appearance based on eligibility
            const cc = document.getElementById('credit-card-ui');
            
            // Remove previous color gradients
            cc.className = cc.className.replace(/from-\S+ via-\S+ to-\S+/, "");
            cc.className = cc.className.replace(/glow-\S+/, "");
            cc.className = cc.className.replace(/border-\S+/, "");
            
            // Handle if there's arrears override background
            if (state.hasArrears) {
                cc.className += ` bg-gradient-to-br from-rose-50 via-rose-100/75 to-slate-100 border border-rose-200 glow-red`;
            } else {
                cc.className += ` bg-gradient-to-br ${cardBgClass}`;
            }

            // Sync calculation breakdown formula text
            const formulaTextEl = document.getElementById('final-calc-formula');
            if (state.hasArrears) {
                formulaTextEl.innerHTML = `<span class="text-rose-600">Blocked (Tunggakan Aktif) &rarr; Rp0</span>`;
            } else if (multiplier === 0) {
                formulaTextEl.innerHTML = `<span class="text-rose-600">Skor &lt; 55 (Faktor 0.0x) &rarr; Rp0</span>`;
            } else {
                let formulaStr = `${formatRupiah(limitBase)} &times; ${multiplier.toFixed(1)}x = ${formatRupiah(limitBase * multiplier)}`;
                if (limitBase * multiplier < 100000) {
                    formulaStr += ` <span class="text-amber-605">(Dibulatkan ke Min. Rp100.000)</span>`;
                } else {
                    formulaStr += ` <span class="text-slate-400">(Dibulatkan ke ribuan terdekat)</span>`;
                }
                formulaTextEl.innerHTML = formulaStr;
            }
        }

        // Initialize values on load
        window.addEventListener('DOMContentLoaded', () => {
            applyPreset('ideal');
        });
    </script>
</body>
</html>
