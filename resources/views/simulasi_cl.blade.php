<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slide 2 - Perhitungan Credit Limit & Faktor TS</title>
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
    </style>
</head>
<body class="bg-white text-slate-800 font-sans min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    
    <!-- Decorative background elements -->
    <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-indigo-50 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-emerald-50/50 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-6xl w-full relative z-10">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <span class="px-3 py-1 text-xs font-semibold bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-full uppercase tracking-widest">
                Slide 2 — Plafon Limit
            </span>
            <h1 class="text-3xl font-extrabold font-outfit mt-3 text-slate-900">
                Perhitungan Limit Base &amp; Faktor Trust Score
            </h1>
            <p class="text-slate-500 text-sm mt-1">Menentukan plafon dasar belanja dari histori belanja serta penyesuaian faktor risiko skor</p>
        </div>

        <!-- MAIN LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            <!-- LEFT COLUMN: Inputs for shopping history (Col 5) -->
            <div class="lg:col-span-5 glass-card rounded-3xl p-6 flex flex-col justify-between">
                <div>
                    <h2 class="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-4">1. Data Riwayat Belanja Pelanggan</h2>
                    
                    <div class="space-y-4">
                        <!-- Max Transaction -->
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Transaksi Terbesar (Rp)</label>
                            <input type="text" id="in-max-trans" value="2.000.000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); calculateLimitBase()">
                        </div>

                        <!-- 2nd and 3rd largest -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Terbesar Ke-2 (Rp)</label>
                                <input type="text" id="in-second-max" value="1.500.000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); calculateLimitBase()">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Terbesar Ke-3 (Rp)</label>
                                <input type="text" id="in-third-max" value="1.000.000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); calculateLimitBase()">
                            </div>
                        </div>

                        <!-- Median Shop (anomaly detection) -->
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Median Transaksi Toko (Rp)</label>
                            <input type="text" id="in-median-shop" value="800.000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); calculateLimitBase()">
                            <p class="text-[9px] text-slate-400 mt-1">Bila Terbesar > 3x Median Toko, L1 dianggap Anomali &amp; diabaikan.</p>
                        </div>

                        <!-- Total spending in last 6 months -->
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Total Belanja 6 Bulan Terakhir (Rp)</label>
                            <input type="text" id="in-total-6m" value="12.000.000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 font-mono focus:outline-none focus:bg-white focus:border-indigo-500" oninput="formatNumberInput(this); calculateLimitBase()">
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 bg-slate-50 p-3 rounded-xl border border-slate-150 text-xs">
                    <span class="text-slate-400 block font-semibold">Pemberitahuan Sistem:</span>
                    <p class="text-slate-500 mt-1 leading-relaxed">Limit Base dipilih dari nilai terbesar di antara metode L1, L2, dan L3.</p>
                </div>
            </div>

            <!-- RIGHT COLUMN: L1, L2, L3 visual comparison & TS Factor grid (Col 7) -->
            <div class="lg:col-span-7 flex flex-col justify-between gap-6">
                
                <!-- Limit Base Selection Output Card -->
                <div class="glass-card rounded-3xl p-6 glow-indigo">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Hasil Seleksi Limit Base</h2>
                        <span id="lb-winner-badge" class="px-2.5 py-0.5 text-[10px] font-bold bg-indigo-50 text-indigo-650 border border-indigo-150 rounded uppercase">L3 Terpilih</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- L1 Card -->
                        <div id="card-l1" class="bg-slate-50 p-4 rounded-2xl border border-slate-200 transition-all duration-300">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-slate-400 font-bold text-[10px] uppercase">L1 (1/2 Max)</span>
                                <span id="badge-l1" class="text-[8px] bg-slate-200 text-slate-600 px-1 py-0.2 rounded font-semibold uppercase">Active</span>
                            </div>
                            <div id="val-l1" class="font-extrabold text-slate-700 text-sm font-mono">Rp 0</div>
                            <span id="desc-l1" class="text-[9px] text-slate-400 block mt-1">1/2 dari trans. terbesar</span>
                        </div>

                        <!-- L2 Card -->
                        <div id="card-l2" class="bg-slate-50 p-4 rounded-2xl border border-slate-200 transition-all duration-300">
                            <span class="text-slate-400 font-bold text-[10px] uppercase block mb-2">L2 (1/2 Rata3)</span>
                            <div id="val-l2" class="font-extrabold text-slate-700 text-sm font-mono">Rp 0</div>
                            <span class="text-[9px] text-slate-400 block mt-1">1/2 dari rata-rata 3 terbesar</span>
                        </div>

                        <!-- L3 Card -->
                        <div id="card-l3" class="bg-indigo-50 p-4 rounded-2xl border border-indigo-200 transition-all duration-300">
                            <span class="text-slate-550 font-bold text-[10px] uppercase block mb-2">L3 (30% Spend)</span>
                            <div id="val-l3" class="font-extrabold text-indigo-700 text-sm font-mono">Rp 0</div>
                            <span class="text-[9px] text-indigo-500 block mt-1">30% dari total 6 bulan</span>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-slate-50 border border-slate-200 rounded-xl flex justify-between items-center text-xs">
                        <span class="text-slate-600 font-medium">Limit Base Terhitung:</span>
                        <span id="txt-limit-base" class="font-extrabold text-slate-800 font-mono text-md">Rp 0</span>
                    </div>
                </div>

                <!-- Trust Score Factors Multipliers Card -->
                <div class="glass-card rounded-3xl p-6">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Faktor Multiplier Trust Score (TS)</h2>
                        
                        <!-- Mini TS Selector Switch for demo purposes -->
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-slate-400">Simulasikan Skor:</span>
                            <input type="range" id="ts-multiplier-slider" min="30" max="100" value="95" class="w-20 accent-indigo-600 bg-slate-200 rounded-lg appearance-none h-1.5 cursor-pointer" oninput="calculateLimitBase()">
                            <span id="ts-slider-label" class="font-mono text-xs text-indigo-600 font-bold">95</span>
                        </div>
                    </div>

                    <!-- Multiplier table grids -->
                    <div class="grid grid-cols-5 gap-2 text-center text-xs">
                        
                        <!-- Row 1 -->
                        <div id="m-card-1" class="p-3 rounded-xl border border-slate-205 bg-slate-50">
                            <div class="text-[10px] text-slate-400 mb-0.5">TS &lt; 55</div>
                            <div class="font-bold text-rose-500 font-mono text-sm">0.0x</div>
                        </div>

                        <!-- Row 2 -->
                        <div id="m-card-2" class="p-3 rounded-xl border border-slate-205 bg-slate-50">
                            <div class="text-[10px] text-slate-400 mb-0.5">TS 55-59</div>
                            <div class="font-bold text-slate-500 font-mono text-sm">0.5x</div>
                        </div>

                        <!-- Row 3 -->
                        <div id="m-card-3" class="p-3 rounded-xl border border-slate-205 bg-slate-50">
                            <div class="text-[10px] text-slate-400 mb-0.5">TS 60-74</div>
                            <div class="font-bold text-slate-600 font-mono text-sm">1.0x</div>
                        </div>

                        <!-- Row 4 -->
                        <div id="m-card-4" class="p-3 rounded-xl border border-slate-205 bg-slate-50">
                            <div class="text-[10px] text-slate-400 mb-0.5">TS 75-89</div>
                            <div class="font-bold text-emerald-600 font-mono text-sm">1.3x</div>
                        </div>

                        <!-- Row 5 -->
                        <div id="m-card-5" class="p-3 rounded-xl border border-indigo-200 bg-indigo-50 glow-indigo">
                            <div class="text-[10px] text-indigo-650 mb-0.5 font-semibold">TS &ge; 90</div>
                            <div class="font-bold text-indigo-700 font-mono text-sm">1.5x</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Scripting logic -->
    <script>
        // Thousands formatting helpers
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

        function getRawValue(id) {
            let val = document.getElementById(id).value;
            return parseFloat(val.replace(/\./g, '')) || 0;
        }

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        function calculateLimitBase() {
            // Get raw values
            const maxTrans = getRawValue('in-max-trans');
            const secondMax = getRawValue('in-second-max');
            const thirdMax = getRawValue('in-third-max');
            const medianShop = getRawValue('in-median-shop');
            const total6M = getRawValue('in-total-6m');

            const tsScore = parseInt(document.getElementById('ts-multiplier-slider').value);
            document.getElementById('ts-slider-label').textContent = tsScore;

            // Anomaly Check
            const isAnomaly = maxTrans > (3 * medianShop);
            const L1 = isAnomaly ? 0 : (maxTrans / 2);
            const L2 = ((maxTrans + secondMax + thirdMax) / 3) / 2;
            const L3 = total6M * 0.3;

            // Sync Card Text
            document.getElementById('val-l1').textContent = formatRupiah(L1);
            document.getElementById('val-l2').textContent = formatRupiah(L2);
            document.getElementById('val-l3').textContent = formatRupiah(L3);

            // Anomaly label toggle
            const descL1 = document.getElementById('desc-l1');
            const badgeL1 = document.getElementById('badge-l1');
            const cardL1 = document.getElementById('card-l1');
            
            if(isAnomaly) {
                descL1.innerHTML = "<span class='text-rose-600 font-semibold'>Anomali! (Max > 3x Median)</span>";
                badgeL1.className = "text-[8px] bg-rose-50 text-rose-600 px-1 py-0.2 rounded font-semibold uppercase border border-rose-200";
                badgeL1.textContent = "Ignored";
                cardL1.className = "bg-rose-50 p-4 rounded-2xl border border-rose-200 opacity-60 transition-all duration-300";
            } else {
                descL1.textContent = "1/2 dari trans. terbesar";
                badgeL1.className = "text-[8px] bg-slate-200 text-slate-600 px-1 py-0.2 rounded font-semibold uppercase";
                badgeL1.textContent = "Active";
                cardL1.className = "bg-slate-50 p-4 rounded-2xl border border-slate-200 transition-all duration-300";
            }

            // Determine Winner Limit Base
            const limitBase = Math.max(L1, L2, L3);
            document.getElementById('txt-limit-base').textContent = formatRupiah(limitBase);

            // Highlight Winner Card
            const cardL2 = document.getElementById('card-l2');
            const cardL3 = document.getElementById('card-l3');
            const winnerBadge = document.getElementById('lb-winner-badge');

            // Reset classes to default
            cardL1.className = isAnomaly 
                ? "bg-rose-50 p-4 rounded-2xl border border-rose-200 opacity-60 transition-all duration-300"
                : "bg-slate-50 p-4 rounded-2xl border border-slate-200 transition-all duration-300";
            cardL2.className = "bg-slate-50 p-4 rounded-2xl border border-slate-200 transition-all duration-300";
            cardL3.className = "bg-slate-50 p-4 rounded-2xl border border-slate-200 transition-all duration-300";

            if(limitBase === L1 && !isAnomaly) {
                cardL1.className = "bg-indigo-50 p-4 rounded-2xl border border-indigo-200 glow-indigo transition-all duration-300";
                winnerBadge.textContent = "L1 Terpilih";
            } else if (limitBase === L2) {
                cardL2.className = "bg-indigo-50 p-4 rounded-2xl border border-indigo-200 glow-indigo transition-all duration-300";
                winnerBadge.textContent = "L2 Terpilih";
            } else if (limitBase === L3) {
                cardL3.className = "bg-indigo-50 p-4 rounded-2xl border border-indigo-200 glow-indigo transition-all duration-300";
                winnerBadge.textContent = "L3 Terpilih";
            }

            // Highlight Multiplier Table Row
            let selectedRowId = 1;
            if (tsScore < 55) selectedRowId = 1;
            else if (tsScore >= 55 && tsScore <= 59) selectedRowId = 2;
            else if (tsScore >= 60 && tsScore <= 74) selectedRowId = 3;
            else if (tsScore >= 75 && tsScore <= 89) selectedRowId = 4;
            else selectedRowId = 5;

            // Clear previous highlight
            for(let i = 1; i <= 5; i++) {
                const el = document.getElementById(`m-card-${i}`);
                el.className = "p-3 rounded-xl border border-slate-200 bg-slate-50 transition-all duration-300";
                
                // restore text color colors to standard
                const scoreText = el.children[1];
                if(i===1) scoreText.className = "font-bold text-rose-500 font-mono text-sm";
                else if(i===4) scoreText.className = "font-bold text-emerald-600 font-mono text-sm";
                else if(i===5) scoreText.className = "font-bold text-indigo-600 font-mono text-sm";
                else scoreText.className = "font-bold text-slate-500 font-mono text-sm";
            }

            // Add highlight class
            const activeEl = document.getElementById(`m-card-${selectedRowId}`);
            activeEl.className = "p-3 rounded-xl border border-indigo-200 bg-indigo-50 glow-indigo transition-all duration-300 scale-105";
            activeEl.children[1].className = "font-bold text-indigo-700 font-mono text-md drop-shadow-[0_0_8px_rgba(99,102,241,0.15)]";
        }

        window.addEventListener('DOMContentLoaded', () => {
            calculateLimitBase();
        });
    </script>
</body>
</html>
