<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slide 3 - Plafon Kredit Akhir &amp; Ketentuan</title>
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
        .glow-indigo { box-shadow: 0 0 35px -10px rgba(99, 102, 241, 0.4); }
        .glow-red { box-shadow: 0 0 35px -10px rgba(239, 68, 68, 0.4); }
        .glass-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    
    <!-- Decorative background elements -->
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-emerald-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-6xl w-full relative z-10">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <span class="px-3 py-1 text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-full uppercase tracking-widest">
                Slide 3 — Plafon Kredit Final
            </span>
            <h1 class="text-3xl font-extrabold font-outfit mt-3 bg-gradient-to-r from-white via-slate-100 to-indigo-300 bg-clip-text text-transparent">
                Plafon Kredit Akhir &amp; Ketentuan Sistem
            </h1>
            <p class="text-slate-400 text-sm mt-1">Keputusan akhir plafon belanja setelah proses pembulatan, batas minimal, dan cek tunggakan</p>
        </div>

        <!-- MAIN LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            <!-- LEFT COLUMN: The Virtual Credit Card UI (Col 5) -->
            <div class="lg:col-span-5 flex flex-col justify-between glass-card rounded-3xl p-8 glow-indigo relative overflow-hidden" id="card-outer-glow">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-indigo-500/10 to-transparent rounded-bl-full pointer-events-none"></div>
                
                <div>
                    <span class="text-xs uppercase font-semibold text-slate-400 tracking-wider">Hasil Kalkulasi Plafon</span>
                    
                    <!-- Virtual Glass Credit Card -->
                    <div id="credit-card" class="relative overflow-hidden w-full h-48 rounded-2xl p-6 flex flex-col justify-between text-white border border-white/10 transition-all duration-500 shadow-2xl mt-6 bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900">
                        <div class="absolute top-0 right-0 w-36 h-36 bg-white/5 rounded-full -mr-8 -mt-8 pointer-events-none"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-indigo-500/10 rounded-full -ml-8 -mb-8 pointer-events-none"></div>
                        
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[9px] uppercase font-bold tracking-widest text-white/70">SBS PAYLATER ENGINE</p>
                                <h3 class="text-sm font-extrabold font-outfit text-white/90">CREDIT LIMIT PLATINUM</h3>
                            </div>
                            <!-- NFC Icon or Chip visual -->
                            <svg class="w-8 h-8 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 11a13.917 13.917 0 00-3.138-8.441l-.053-.09m1.782 14.54a14.05 14.05 0 002.327-2.316m-2.327-10.224A14.048 14.048 0 009 6.22M15 11c0 2.22-.544 4.312-1.503 6.157M15 11c0-2.22-.544-4.312-1.503-6.157M18 11A11.97 11.97 0 0012 1.644M18 11a11.97 11.97 0 00-6 9.356"></path></svg>
                        </div>
                        
                        <div class="my-auto">
                            <div class="text-[9px] text-white/60 uppercase tracking-widest mb-1.5 font-semibold">Maksimum Limit Belanja</div>
                            <div id="cl-display" class="text-3xl font-extrabold font-outfit tracking-wide text-white transition-all duration-300">Rp 5.400.000</div>
                        </div>

                        <div class="flex justify-between items-center text-[10px] text-white/60">
                            <div>
                                <span>Status: </span>
                                <span id="cl-status-txt" class="font-extrabold text-white uppercase tracking-wider bg-white/10 px-2 py-0.5 rounded">AKTIF</span>
                            </div>
                            <span class="font-bold">v2.0 SECURE</span>
                        </div>
                    </div>
                </div>

                <!-- Equation Math Recap -->
                <div class="bg-slate-950/60 rounded-2xl p-4 border border-slate-900 mt-6 text-xs space-y-1.5">
                    <span class="text-slate-500 font-bold text-[10px] uppercase block mb-1">Perkalian Dasar:</span>
                    <div id="math-recap" class="font-mono text-slate-300">
                        Rp 3.600.000 (Limit Base) × 1.5 (Faktor TS) = Rp 5.400.000
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Rules Engine checklist & interactive selectors (Col 7) -->
            <div class="lg:col-span-7 glass-card rounded-3xl p-8 flex flex-col justify-between">
                <div>
                    <h2 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-6">2. Aturan Plafon &amp; Input Simulator</h2>
                    
                    <!-- Simulating slider inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                        <!-- Limit Base Input -->
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Simulasikan Limit Base (Rp)</label>
                            <input type="text" id="in-limit-base" value="3.600.000" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-200 font-mono focus:outline-none focus:border-indigo-500" oninput="formatNumberInput(this); processRules()">
                        </div>

                        <!-- TS Multiplier Selector -->
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Faktor TS (Multiplier)</label>
                            <select id="in-ts-factor" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-indigo-500" onchange="processRules()">
                                <option value="0">0.0x (TS < 55) - Ditolak</option>
                                <option value="0.5">0.5x (TS 55-59) - Terbatas</option>
                                <option value="1.0">1.0x (TS 60-74) - Layak Standar</option>
                                <option value="1.3">1.3x (TS 75-89) - Layak Baik</option>
                                <option value="1.5" selected>1.5x (TS >= 90) - Limit Maksimal</option>
                            </select>
                        </div>

                        <!-- Arrears Toggle -->
                        <div class="md:col-span-2 flex items-center justify-between p-4 rounded-xl bg-rose-950/10 border border-rose-950/20">
                            <div>
                                <span class="block text-xs font-semibold text-rose-400">Tunggakan Aktif (Active Arrears)</span>
                                <span class="text-[9px] text-slate-500">Bila diaktifkan, plafon otomatis dipaksa menjadi Rp0.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="in-arrears" class="sr-only peer" onchange="processRules()">
                                <div class="w-10 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:border-slate-350 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600/35 peer-checked:after:bg-rose-500"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Audit Trail / Rules checklist -->
                    <div class="space-y-3.5">
                        <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Alur Validasi Ketentuan</h3>
                        
                        <!-- Checkpoint 1: Perhitungan -->
                        <div class="flex items-start gap-3 text-xs">
                            <div id="check-icon-calc" class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <span class="font-semibold text-slate-200">1. Hasil Perkalian Faktor TS</span>
                                <p id="check-desc-calc" class="text-[10px] text-slate-500 mt-0.5">3.600.000 &times; 1.5 = 5.400.000</p>
                            </div>
                        </div>

                        <!-- Checkpoint 2: Pembulatan -->
                        <div class="flex items-start gap-3 text-xs">
                            <div id="check-icon-round" class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <span class="font-semibold text-slate-200">2. Pembulatan ke Ribuan Terdekat</span>
                                <p id="check-desc-round" class="text-[10px] text-slate-500 mt-0.5">Dibulatkan ke ribuan terdekat</p>
                            </div>
                        </div>

                        <!-- Checkpoint 3: Minimum Limit -->
                        <div class="flex items-start gap-3 text-xs">
                            <div id="check-icon-min" class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <span class="font-semibold text-slate-200">3. Validasi Batas Minimum Limit (Rp100.000)</span>
                                <p id="check-desc-min" class="text-[10px] text-slate-500 mt-0.5">Memenuhi batas minimum Rp100.000</p>
                            </div>
                        </div>

                        <!-- Checkpoint 4: Tunggakan Check -->
                        <div class="flex items-start gap-3 text-xs">
                            <div id="check-icon-arrears" class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <span class="font-semibold text-slate-200">4. Pengecekan Tunggakan Aktif</span>
                                <p id="check-desc-arrears" class="text-[10px] text-slate-500 mt-0.5">Bebas tunggakan aktif</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-[10px] text-slate-500 italic text-center">
                    * Aturan override: Tunggakan aktif otomatis memblokir plafon belanja (Limit = Rp0) tanpa menghitung variabel lainnya.
                </div>
            </div>

        </div>

    </div>

    <!-- Scripting logic -->
    <script>
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

        function processRules() {
            // Inputs
            const limitBase = getRawValue('in-limit-base');
            const tsFactor = parseFloat(document.getElementById('in-ts-factor').value);
            const hasArrears = document.getElementById('in-arrears').checked;

            // Step 1: Base multiply
            let rawLimit = limitBase * tsFactor;
            document.getElementById('check-desc-calc').textContent = `${formatRupiah(limitBase)} × ${tsFactor.toFixed(1)} = ${formatRupiah(rawLimit)}`;

            // Step 2: Rounding to nearest thousand
            let roundedLimit = Math.round(rawLimit / 1000) * 1000;
            const diffRound = Math.abs(roundedLimit - rawLimit);
            document.getElementById('check-desc-round').textContent = `Dibulatkan ke ribuan terdekat: ${formatRupiah(roundedLimit)} ${diffRound > 0 ? '(Penyesuaian ' + formatRupiah(diffRound) + ')' : '(Sudah Bulat)'}`;

            // Step 3: Minimum limit validation
            let minCorrectedLimit = roundedLimit;
            let minRuleApplied = false;
            if(roundedLimit > 0 && roundedLimit < 100000) {
                minCorrectedLimit = 100000;
                minRuleApplied = true;
                document.getElementById('check-desc-min').innerHTML = `<span class="text-amber-400 font-semibold">Limit di bawah Rp100.000 &rarr; Dibulatkan naik ke Rp100.000</span>`;
                document.getElementById('check-icon-min').className = "w-5 h-5 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center border border-amber-500/20 mt-0.5";
            } else {
                document.getElementById('check-desc-min').textContent = roundedLimit === 0 ? "Limit Rp0 (Tidak berlaku batas minimum)" : "Memenuhi batas minimum Rp100.000";
                document.getElementById('check-icon-min').className = "w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 mt-0.5";
            }

            // Step 4: Arrears checking
            let finalLimit = minCorrectedLimit;
            
            const checkIconArr = document.getElementById('check-icon-arrears');
            const checkDescArr = document.getElementById('check-desc-arrears');
            const cc = document.getElementById('credit-card');
            const outerGlow = document.getElementById('card-outer-glow');
            const clStatusTxt = document.getElementById('cl-status-txt');

            if(hasArrears) {
                finalLimit = 0;
                
                checkIconArr.className = "w-5 h-5 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center border border-rose-500/25 mt-0.5";
                checkIconArr.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>`;
                checkDescArr.innerHTML = `<span class="text-rose-400 font-semibold">Terdeteksi Tunggakan Aktif! Plafon langsung diblokir (Limit = Rp0)</span>`;

                // Set CC styling to Blocked
                cc.className = "relative overflow-hidden w-full h-48 rounded-2xl p-6 flex flex-col justify-between text-white border border-rose-500/20 transition-all duration-500 shadow-2xl mt-6 bg-gradient-to-br from-rose-950 via-slate-900 to-slate-950";
                clStatusTxt.textContent = "BLOCKED";
                clStatusTxt.className = "font-extrabold text-rose-400 uppercase tracking-wider bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20";
                
                outerGlow.className = "lg:col-span-5 flex flex-col justify-between glass-card rounded-3xl p-8 glow-red relative overflow-hidden";
            } else {
                checkIconArr.className = "w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20 mt-0.5";
                checkIconArr.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>`;
                checkDescArr.textContent = "Bebas tunggakan aktif";

                // Set CC styling to normal/active
                if (tsFactor === 0) {
                    cc.className = "relative overflow-hidden w-full h-48 rounded-2xl p-6 flex flex-col justify-between text-white border border-rose-500/20 transition-all duration-500 shadow-2xl mt-6 bg-gradient-to-br from-rose-950 via-slate-900 to-slate-950";
                    clStatusTxt.textContent = "DITOLAK";
                    clStatusTxt.className = "font-extrabold text-rose-400 uppercase tracking-wider bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20";
                    outerGlow.className = "lg:col-span-5 flex flex-col justify-between glass-card rounded-3xl p-8 glow-red relative overflow-hidden";
                } else if (tsFactor === 0.5) {
                    cc.className = "relative overflow-hidden w-full h-48 rounded-2xl p-6 flex flex-col justify-between text-white border border-amber-500/20 transition-all duration-500 shadow-2xl mt-6 bg-gradient-to-br from-amber-950 via-slate-900 to-slate-900";
                    clStatusTxt.textContent = "TERBATAS";
                    clStatusTxt.className = "font-extrabold text-amber-400 uppercase tracking-wider bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20";
                    outerGlow.className = "lg:col-span-5 flex flex-col justify-between glass-card rounded-3xl p-8 glow-yellow relative overflow-hidden";
                } else {
                    cc.className = "relative overflow-hidden w-full h-48 rounded-2xl p-6 flex flex-col justify-between text-white border border-white/10 transition-all duration-500 shadow-2xl mt-6 bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900";
                    clStatusTxt.textContent = "AKTIF";
                    clStatusTxt.className = "font-extrabold text-emerald-400 uppercase tracking-wider bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20";
                    outerGlow.className = "lg:col-span-5 flex flex-col justify-between glass-card rounded-3xl p-8 glow-indigo relative overflow-hidden";
                }
            }

            // Sync Display Plafon limit
            document.getElementById('cl-display').textContent = formatRupiah(finalLimit);

            // Sync math recap box
            const recapBox = document.getElementById('math-recap');
            if(hasArrears) {
                recapBox.innerHTML = `<span class="text-rose-400 font-bold uppercase tracking-wider">Arrears Override (Tunggakan Aktif) &rarr; Plafon Kredit: Rp0</span>`;
            } else if (tsFactor === 0) {
                recapBox.innerHTML = `<span class="text-rose-400 font-bold uppercase tracking-wider">TS &lt; 55 (Faktor 0.0x) &rarr; Plafon Kredit: Rp0</span>`;
            } else {
                let text = `${formatRupiah(limitBase)} × ${tsFactor.toFixed(1)} = ${formatRupiah(limitBase * tsFactor)}`;
                if(minRuleApplied) {
                    text += ` <span class="text-amber-400">(Dibulatkan ke Min. Rp100.000)</span>`;
                } else {
                    text += ` <span class="text-slate-500">(Dibulatkan ke ribuan terdekat)</span>`;
                }
                recapBox.innerHTML = text;
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            processRules();
        });
    </script>
</body>
</html>
