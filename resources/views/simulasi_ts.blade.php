<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slide 1 - Evaluasi Trust Score (TS)</title>
    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Outfit', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .glow-indigo { box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.12); }
        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
        }
        .progress-ring__circle {
            transition: stroke-dashoffset 0.35s ease-out;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
    </style>
</head>
<body class="bg-white text-slate-800 font-sans min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    
    <!-- Decorative background elements -->
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-50 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-emerald-50/50 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-6xl w-full relative z-10">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <span class="px-3 py-1 text-xs font-semibold bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full uppercase tracking-widest">
                Slide 1 — Smart Credit Scoring
            </span>
            <h1 class="text-3xl font-extrabold font-outfit mt-3 text-slate-900">
                Sistem Penilaian Kelayakan: Trust Score (TS)
            </h1>
            <p class="text-slate-500 text-sm mt-1">Mengukur tingkat kepercayaan pelanggan berdasarkan histori dan perilaku transaksi</p>
        </div>

        <!-- MAIN LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            <!-- LEFT COLUMN: Trust Score Visual Dashboard (Col 5) -->
            <div class="lg:col-span-5 flex flex-col justify-between glass-card rounded-3xl p-8 glow-indigo relative overflow-hidden">
                
                <div class="text-center py-6">
                    <span class="text-xs uppercase font-semibold text-slate-400 tracking-wider">Hasil Pengukuran</span>
                    
                    <div class="relative flex items-center justify-center my-6">
                        <svg class="w-56 h-56">
                            <!-- Background Circle -->
                            <circle class="text-slate-100" stroke-width="12" stroke="currentColor" fill="transparent" r="95" cx="112" cy="112"/>
                            <!-- Foreground circle -->
                            <circle id="ts-gauge" class="progress-ring__circle transition-all duration-300" stroke-width="14" stroke-linecap="round" stroke="currentColor" fill="transparent" r="95" cx="112" cy="112" stroke-dasharray="596.9" stroke-dashoffset="596.9"/>
                        </svg>
                        
                        <div class="absolute flex flex-col items-center">
                            <span id="ts-value" class="text-6xl font-extrabold font-outfit text-slate-900">50</span>
                            <span class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Trust Score</span>
                        </div>
                    </div>

                    <!-- Eligibility Status Badge -->
                    <div id="ts-status-badge" class="inline-flex px-6 py-2 rounded-full text-sm font-extrabold uppercase tracking-wider items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-current animate-pulse"></span>
                        <span id="ts-status-text">Memproses</span>
                    </div>
                </div>

                <!-- Classification Matrix Reference -->
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mt-4 text-xs space-y-2.5">
                    <div class="flex justify-between items-center text-slate-650">
                        <span class="font-medium text-slate-600">TS &ge; 70</span>
                        <span class="font-bold text-emerald-600 uppercase">Layak / Disetujui</span>
                    </div>
                    <div class="h-[1px] bg-slate-200/60"></div>
                    <div class="flex justify-between items-center text-slate-650">
                        <span class="font-medium text-slate-600">TS 55 - 69</span>
                        <span class="font-bold text-amber-600 uppercase">Dipertimbangkan</span>
                    </div>
                    <div class="h-[1px] bg-slate-200/60"></div>
                    <div class="flex justify-between items-center text-slate-650">
                        <span class="font-medium text-slate-600">TS &lt; 55</span>
                        <span class="font-bold text-rose-600 uppercase">Ditolak / Gagal Layak</span>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Interactive Controllers (Col 7) -->
            <div class="lg:col-span-7 glass-card rounded-3xl p-8 flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold font-outfit text-slate-700 uppercase tracking-wider mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Simulator Parameter Nilai Poin
                    </h2>

                    <div class="space-y-5">
                        
                        <!-- P_umur: Account Age -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-2">Umur Akun Pelanggan (P<sub>umur</sub>)</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="setAge('<30')" id="age-low" class="py-2 text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100">
                                    &lt; 30 Hari (+0)
                                </button>
                                <button type="button" onclick="setAge('30-179')" id="age-mid" class="py-2 text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100">
                                    30-179 Hari (+10)
                                </button>
                                <button type="button" onclick="setAge('>=180')" id="age-high" class="py-2 text-xs font-medium rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700">
                                    &ge; 180 Hari (+20)
                                </button>
                            </div>
                        </div>

                        <!-- Sliders -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- On-Time Payments -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="flex justify-between text-xs mb-2">
                                    <span class="text-slate-600 font-medium">Bayar Tepat Waktu (P<sub>tepat</sub>)</span>
                                    <span class="font-mono text-emerald-600 font-bold" id="lbl-on-time">10x (+20)</span>
                                </div>
                                <input type="range" id="val-on-time" min="0" max="15" value="10" class="w-full accent-indigo-600 bg-slate-200 rounded-lg appearance-none h-1.5 cursor-pointer" oninput="calculateScore()">
                                <span class="text-[9px] text-slate-400 block mt-1">+2 Poin per transaksi (Maks. +20)</span>
                            </div>

                            <!-- Late Payments -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="flex justify-between text-xs mb-2">
                                    <span class="text-rose-600 font-semibold">Bayar Terlambat (P<sub>telat</sub>)</span>
                                    <span class="font-mono text-rose-600 font-bold" id="lbl-late">0x (-0)</span>
                                </div>
                                <input type="range" id="val-late" min="0" max="10" value="0" class="w-full accent-rose-600 bg-slate-200 rounded-lg appearance-none h-1.5 cursor-pointer" oninput="calculateScore()">
                                <span class="text-[9px] text-slate-400 block mt-1">-5 Poin per transaksi terlambat</span>
                            </div>

                            <!-- Failed Payments -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="flex justify-between text-xs mb-2">
                                    <span class="text-rose-600 font-semibold">Gagal Bayar (P<sub>gagal</sub>)</span>
                                    <span class="font-mono text-rose-600 font-bold" id="lbl-failed">0x (-0)</span>
                                </div>
                                <input type="range" id="val-failed" min="0" max="4" value="0" class="w-full accent-rose-600 bg-slate-200 rounded-lg appearance-none h-1.5 cursor-pointer" oninput="calculateScore()">
                                <span class="text-[9px] text-slate-400 block mt-1">-25 Poin per default/gagal bayar</span>
                            </div>

                            <!-- Active Arrears (Tunggakan) -->
                            <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100">
                                <div>
                                    <span class="block text-xs font-semibold text-rose-600">Tunggakan Aktif (P<sub>tunggakan</sub>)</span>
                                    <span class="text-[9px] text-slate-400">Status Due/Late aktif (-10)</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="val-arrears" class="sr-only peer" onchange="calculateScore()">
                                    <div class="w-10 h-6 bg-slate-250 rounded-full peer peer-focus:outline-none dark:bg-slate-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Toggles -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Freq Toggle -->
                            <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                                <div>
                                    <span class="block text-xs font-semibold text-slate-700">Frekuensi Belanja (P<sub>freq</sub>)</span>
                                    <span class="text-[9px] text-slate-400">&ge;3 Trans/Bln dlm 3 Bln (+5)</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="val-freq" checked class="sr-only peer" onchange="calculateScore()">
                                    <div class="w-9 h-5 bg-slate-350 rounded-full peer peer-focus:outline-none dark:bg-slate-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>

                            <!-- Value Above Shop Median Toggle -->
                            <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                                <div>
                                    <span class="block text-xs font-semibold text-slate-700">Nilai Rata-Rata (P<sub>nilai</sub>)</span>
                                    <span class="text-[9px] text-slate-400">Rata-rata &gt; Median Toko (+5)</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="val-value" checked class="sr-only peer" onchange="calculateScore()">
                                    <div class="w-9 h-5 bg-slate-350 rounded-full peer peer-focus:outline-none dark:bg-slate-300 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Live Mathematical Calculation Breakdown -->
                <div class="mt-6 pt-5 border-t border-slate-200 text-xs font-mono text-slate-500 bg-slate-50 p-3.5 rounded-xl border border-slate-150">
                    <span class="text-slate-400 block mb-1 font-semibold">Rincian Evaluasi Formula:</span>
                    <div class="flex flex-wrap items-center gap-1.5 text-[11px] leading-relaxed">
                        <span class="text-indigo-600 font-bold">50</span>
                        <span>+</span>
                        <span id="math-umur" class="text-emerald-600 font-semibold">20 (P<sub>umur</sub>)</span>
                        <span>+</span>
                        <span id="math-tepat" class="text-emerald-600 font-semibold">20 (P<sub>tepat</sub>)</span>
                        <span>+</span>
                        <span id="math-freq" class="text-emerald-600 font-semibold">5 (P<sub>freq</sub>)</span>
                        <span>+</span>
                        <span id="math-nilai" class="text-emerald-600 font-semibold">5 (P<sub>nilai</sub>)</span>
                        <span>-</span>
                        <span id="math-telat" class="text-slate-400 font-medium">0 (P<sub>telat</sub>)</span>
                        <span>-</span>
                        <span id="math-gagal" class="text-slate-400 font-medium">0 (P<sub>gagal</sub>)</span>
                        <span>-</span>
                        <span id="math-tunggakan" class="text-slate-400 font-medium">0 (P<sub>tung</sub>)</span>
                        <span>=</span>
                        <span id="math-total" class="font-extrabold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">100 Poin</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Scripting for live reactive dashboard -->
    <script>
        let currentAge = '>=180';

        function setAge(ageValue) {
            currentAge = ageValue;
            
            const low = document.getElementById('age-low');
            const mid = document.getElementById('age-mid');
            const high = document.getElementById('age-high');
            
            [low, mid, high].forEach(b => {
                b.className = "py-2 text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100";
            });

            if(ageValue === '<30') {
                low.className = "py-2 text-xs font-medium rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700";
            } else if(ageValue === '30-179') {
                mid.className = "py-2 text-xs font-medium rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700";
            } else if(ageValue === '>=180') {
                high.className = "py-2 text-xs font-medium rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700";
            }
            calculateScore();
        }

        function calculateScore() {
            // Get inputs
            const onTime = parseInt(document.getElementById('val-on-time').value);
            const late = parseInt(document.getElementById('val-late').value);
            const failed = parseInt(document.getElementById('val-failed').value);
            const arrears = document.getElementById('val-arrears').checked;
            const freq = document.getElementById('val-freq').checked;
            const valueAbove = document.getElementById('val-value').checked;

            // Individual Point Calculations
            let P_umur = 0;
            if (currentAge === '30-179') P_umur = 10;
            else if (currentAge === '>=180') P_umur = 20;

            const P_tepat = Math.min(20, onTime * 2);
            const P_telat = late * 5;
            const P_gagal = failed * 25;
            const P_tunggakan = arrears ? 10 : 0;
            const P_freq = freq ? 5 : 0;
            const P_nilai = valueAbove ? 5 : 0;

            // Final Summing
            let total = 50 + P_umur + P_tepat + P_freq + P_nilai - P_telat - P_gagal - P_tunggakan;
            total = Math.max(0, Math.min(100, total));

            // Sync Text & Labels
            document.getElementById('lbl-on-time').textContent = `${onTime}x (+${P_tepat})`;
            document.getElementById('lbl-late').textContent = `${late}x (-${P_telat})`;
            document.getElementById('lbl-failed').textContent = `${failed}x (-${P_gagal})`;

            document.getElementById('ts-value').textContent = total;

            // Formula detail syncing
            document.getElementById('math-umur').textContent = `${P_umur} (Pumur)`;
            document.getElementById('math-umur').className = P_umur > 0 ? "text-emerald-600 font-semibold" : "text-slate-400";
            
            document.getElementById('math-tepat').textContent = `${P_tepat} (Ptepat)`;
            document.getElementById('math-tepat').className = P_tepat > 0 ? "text-emerald-600 font-semibold" : "text-slate-400";
            
            document.getElementById('math-freq').textContent = `${P_freq} (Pfreq)`;
            document.getElementById('math-freq').className = P_freq > 0 ? "text-emerald-600 font-semibold" : "text-slate-400";
            
            document.getElementById('math-nilai').textContent = `${P_nilai} (Pnilai)`;
            document.getElementById('math-nilai').className = P_nilai > 0 ? "text-emerald-600 font-semibold" : "text-slate-400";

            document.getElementById('math-telat').textContent = `${P_telat} (Ptelat)`;
            document.getElementById('math-telat').className = P_telat > 0 ? "text-rose-600 font-semibold" : "text-slate-400";
            
            document.getElementById('math-gagal').textContent = `${P_gagal} (Pgagal)`;
            document.getElementById('math-gagal').className = P_gagal > 0 ? "text-rose-600 font-semibold" : "text-slate-400";
            
            document.getElementById('math-tunggakan').textContent = `${P_tunggakan} (Ptung)`;
            document.getElementById('math-tunggakan').className = P_tunggakan > 0 ? "text-rose-600 font-semibold" : "text-slate-400";

            document.getElementById('math-total').textContent = `${total} Poin`;

            // Badge status and color classes
            const badge = document.getElementById('ts-status-badge');
            const statusText = document.getElementById('ts-status-text');
            const gauge = document.getElementById('ts-gauge');
            
            badge.className = badge.className.replace(/bg-\S+/, "").replace(/text-\S+/, "").replace(/border-\S+/, "");
            gauge.className.baseVal = "progress-ring__circle transition-all duration-300 ";

            let colorClass = "";
            let statusStr = "";
            let badgeBgClass = "";

            if (total < 55) {
                statusStr = "DITOLAK (REJECTED)";
                colorClass = "text-rose-500";
                badgeBgClass = "bg-rose-50 text-rose-600 border border-rose-200";
            } else if (total >= 55 && total <= 69) {
                statusStr = "DIPERTIMBANGKAN";
                colorClass = "text-amber-500";
                badgeBgClass = "bg-amber-50 text-amber-600 border border-amber-200";
            } else {
                statusStr = "DISETUJUI / LAYAK";
                colorClass = "text-emerald-500";
                badgeBgClass = "bg-emerald-50 text-emerald-600 border border-emerald-200";
            }

            statusText.textContent = statusStr;
            badge.className += " " + badgeBgClass;
            gauge.className.baseVal += colorClass;

            // Calculate SVG dash-offset
            const radius = gauge.r.baseVal.value;
            const circumference = radius * 2 * Math.PI;
            gauge.style.strokeDasharray = `${circumference} ${circumference}`;
            gauge.style.strokeDashoffset = circumference - (total / 100) * circumference;
        }

        // Initialize dashboard state
        window.addEventListener('DOMContentLoaded', () => {
            calculateScore();
        });
    </script>
</body>
</html>
