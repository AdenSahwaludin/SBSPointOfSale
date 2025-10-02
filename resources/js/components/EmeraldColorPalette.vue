<script lang="ts" setup>
// Component untuk mendemonstrasikan beautiful emerald color palette
</script>

<template>
    <div class="bg-emerald-gradient-soft shadow-emerald rounded-lg border border-emerald-200 p-6">
        <h3 class="mb-4 text-lg font-semibold text-emerald-800">Beautiful Emerald Color Palette</h3>

        <!-- Primary Gradients -->
        <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="text-center">
                <div class="bg-emerald-gradient-primary shadow-emerald emerald-hover-scale mb-2 h-20 w-full cursor-pointer rounded-lg"></div>
                <p class="text-sm font-medium text-emerald-700">Primary Gradient</p>
                <p class="text-xs text-emerald-500">Primary → Secondary</p>
            </div>

            <div class="text-center">
                <div class="bg-emerald-gradient-secondary shadow-emerald emerald-hover-scale mb-2 h-20 w-full cursor-pointer rounded-lg"></div>
                <p class="text-sm font-medium text-emerald-700">Secondary Gradient</p>
                <p class="text-xs text-emerald-500">Secondary → Accent</p>
            </div>

            <div class="text-center">
                <div class="bg-emerald-gradient-accent shadow-emerald emerald-hover-scale mb-2 h-20 w-full cursor-pointer rounded-lg"></div>
                <p class="text-sm font-medium text-emerald-700">Accent Gradient</p>
                <p class="text-xs text-emerald-500">Accent → Light</p>
            </div>

            <div class="text-center">
                <div class="bg-emerald-gradient-hero shadow-emerald emerald-hover-scale mb-2 h-20 w-full cursor-pointer rounded-lg"></div>
                <p class="text-sm font-medium text-emerald-700">Hero Gradient</p>
                <p class="text-xs text-emerald-500">Triple blend</p>
            </div>
        </div>

        <!-- Emerald Color Scale -->
        <div class="space-y-3">
            <h4 class="text-md font-medium text-emerald-700">Emerald Color Scale</h4>
            <div class="grid grid-cols-5 gap-2 md:grid-cols-10">
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-25"></div>
                    <p class="text-xs text-emerald-600">25</p>
                </div>
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-50"></div>
                    <p class="text-xs text-emerald-600">50</p>
                </div>
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-100"></div>
                    <p class="text-xs text-emerald-600">100</p>
                </div>
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-200"></div>
                    <p class="text-xs text-emerald-600">200</p>
                </div>
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-300"></div>
                    <p class="text-xs text-emerald-600">300</p>
                </div>
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-400"></div>
                    <p class="text-xs text-white">400</p>
                </div>
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-500"></div>
                    <p class="text-xs text-white">500</p>
                </div>
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-600"></div>
                    <p class="text-xs text-white">600</p>
                </div>
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-700"></div>
                    <p class="text-xs text-white">700</p>
                </div>
                <div class="text-center">
                    <div class="emerald-hover-scale mb-1 h-12 w-full cursor-pointer rounded border border-emerald-200 bg-emerald-800"></div>
                    <p class="text-xs text-white">800</p>
                </div>
            </div>
        </div>

        <!-- Interactive Elements -->
        <div class="mt-6 space-y-4">
            <h4 class="text-md font-medium text-emerald-700">Interactive Elements</h4>
            <div class="flex flex-wrap gap-3">
                <button class="btn-emerald-primary emerald-hover-glow">Primary Button</button>
                <button class="btn-emerald-secondary emerald-transition">Secondary Button</button>
                <button class="emerald-transition rounded-lg border border-emerald-400 px-4 py-2 text-emerald-600 hover:bg-emerald-50">
                    Outline Button
                </button>
                <button class="emerald-transition rounded-lg px-4 py-2 text-emerald-600 hover:bg-emerald-100">Text Button</button>
            </div>
        </div>

        <!-- Cards Showcase -->
        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="card-emerald">
                <div class="mb-3 flex items-center gap-3">
                    <div class="bg-emerald-gradient-primary flex h-10 w-10 items-center justify-center rounded-lg">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <h5 class="font-semibold text-emerald-800">Stats Card</h5>
                        <p class="text-sm text-emerald-600">Beautiful emerald styling</p>
                    </div>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-2xl font-bold text-emerald-800">1,234</p>
                        <p class="text-sm text-emerald-500">+12% dari kemarin</p>
                    </div>
                    <div class="text-emerald-400">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
            </div>

            <div class="bg-emerald-gradient-accent shadow-emerald-lg emerald-hover-scale cursor-pointer rounded-lg p-4 text-white">
                <h5 class="mb-2 font-semibold">Gradient Card</h5>
                <p class="mb-3 text-sm text-emerald-50">Showcase beautiful gradients with smooth transitions.</p>
                <button class="bg-opacity-20 rounded bg-white px-3 py-1 text-xs font-medium">Action</button>
            </div>

            <div class="shadow-emerald emerald-hover-scale cursor-pointer rounded-lg border-l-4 border-emerald-400 bg-white p-4">
                <h5 class="mb-2 font-semibold text-emerald-800">Accent Border</h5>
                <p class="text-sm text-emerald-600">Clean design with emerald accent border for emphasis.</p>
                <div class="mt-3 flex gap-2">
                    <span class="rounded bg-emerald-100 px-2 py-1 text-xs text-emerald-700">Tag 1</span>
                    <span class="rounded bg-emerald-100 px-2 py-1 text-xs text-emerald-700">Tag 2</span>
                </div>
            </div>
        </div>

        <!-- Usage Examples -->
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <h4 class="text-md mb-2 font-medium text-emerald-800">Usage Examples</h4>
            <div class="space-y-1 text-sm text-emerald-700">
                <p><code class="rounded bg-emerald-100 px-1">btn-emerald-primary</code> - Primary action buttons</p>
                <p><code class="rounded bg-emerald-100 px-1">card-emerald</code> - Beautiful cards with emerald accents</p>
                <p><code class="rounded bg-emerald-100 px-1">emerald-transition</code> - Smooth animations</p>
                <p><code class="rounded bg-emerald-100 px-1">emerald-hover-glow</code> - Elegant hover effects</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Additional hover effects for the showcase */
.cursor-pointer:hover {
    cursor: pointer;
}

code {
    font-family: 'Courier New', monospace;
    font-size: 0.85em;
}
</style>
