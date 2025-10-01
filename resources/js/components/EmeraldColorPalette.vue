<script lang="ts" setup>
// Component untuk mendemonstrasikan beautiful emerald color palette
</script>

<template>
    <div class="p-6 bg-emerald-gradient-soft rounded-lg border border-emerald-200 shadow-emerald">
        <h3 class="text-lg font-semibold text-emerald-800 mb-4">Beautiful Emerald Color Palette</h3>
        
        <!-- Primary Gradients -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="text-center">
                <div class="w-full h-20 bg-emerald-gradient-primary rounded-lg shadow-emerald mb-2 emerald-hover-scale cursor-pointer"></div>
                <p class="text-sm text-emerald-700 font-medium">Primary Gradient</p>
                <p class="text-xs text-emerald-500">Primary → Secondary</p>
            </div>
            
            <div class="text-center">
                <div class="w-full h-20 bg-emerald-gradient-secondary rounded-lg shadow-emerald mb-2 emerald-hover-scale cursor-pointer"></div>
                <p class="text-sm text-emerald-700 font-medium">Secondary Gradient</p>
                <p class="text-xs text-emerald-500">Secondary → Accent</p>
            </div>
            
            <div class="text-center">
                <div class="w-full h-20 bg-emerald-gradient-accent rounded-lg shadow-emerald mb-2 emerald-hover-scale cursor-pointer"></div>
                <p class="text-sm text-emerald-700 font-medium">Accent Gradient</p>
                <p class="text-xs text-emerald-500">Accent → Light</p>
            </div>
            
            <div class="text-center">
                <div class="w-full h-20 bg-emerald-gradient-hero rounded-lg shadow-emerald mb-2 emerald-hover-scale cursor-pointer"></div>
                <p class="text-sm text-emerald-700 font-medium">Hero Gradient</p>
                <p class="text-xs text-emerald-500">Triple blend</p>
            </div>
        </div>

        <!-- Emerald Color Scale -->
        <div class="space-y-3">
            <h4 class="text-md font-medium text-emerald-700">Emerald Color Scale</h4>
            <div class="grid grid-cols-5 md:grid-cols-10 gap-2">
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-25 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-emerald-600">25</p>
                </div>
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-50 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-emerald-600">50</p>
                </div>
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-100 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-emerald-600">100</p>
                </div>
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-200 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-emerald-600">200</p>
                </div>
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-300 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-emerald-600">300</p>
                </div>
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-400 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-white">400</p>
                </div>
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-500 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-white">500</p>
                </div>
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-600 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-white">600</p>
                </div>
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-700 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-white">700</p>
                </div>
                <div class="text-center">
                    <div class="w-full h-12 bg-emerald-800 rounded border border-emerald-200 mb-1 emerald-hover-scale cursor-pointer"></div>
                    <p class="text-xs text-white">800</p>
                </div>
            </div>
        </div>

        <!-- Interactive Elements -->
        <div class="mt-6 space-y-4">
            <h4 class="text-md font-medium text-emerald-700">Interactive Elements</h4>
            <div class="flex flex-wrap gap-3">
                <button class="btn-emerald-primary emerald-hover-glow">
                    Primary Button
                </button>
                <button class="btn-emerald-secondary emerald-transition">
                    Secondary Button
                </button>
                <button class="px-4 py-2 border border-emerald-400 text-emerald-600 rounded-lg hover:bg-emerald-50 emerald-transition">
                    Outline Button
                </button>
                <button class="px-4 py-2 text-emerald-600 rounded-lg hover:bg-emerald-100 emerald-transition">
                    Text Button
                </button>
            </div>
        </div>

        <!-- Cards Showcase -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card-emerald">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-emerald-gradient-primary rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <h5 class="font-semibold text-emerald-800">Stats Card</h5>
                        <p class="text-emerald-600 text-sm">Beautiful emerald styling</p>
                    </div>
                </div>
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-2xl font-bold text-emerald-800">1,234</p>
                        <p class="text-emerald-500 text-sm">+12% dari kemarin</p>
                    </div>
                    <div class="text-emerald-400">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-emerald-gradient-accent p-4 rounded-lg text-white shadow-emerald-lg emerald-hover-scale cursor-pointer">
                <h5 class="font-semibold mb-2">Gradient Card</h5>
                <p class="text-emerald-50 text-sm mb-3">Showcase beautiful gradients with smooth transitions.</p>
                <button class="bg-white bg-opacity-20 px-3 py-1 rounded text-xs font-medium">
                    Action
                </button>
            </div>
            
            <div class="bg-white p-4 rounded-lg border-l-4 border-emerald-400 shadow-emerald emerald-hover-scale cursor-pointer">
                <h5 class="font-semibold text-emerald-800 mb-2">Accent Border</h5>
                <p class="text-emerald-600 text-sm">Clean design with emerald accent border for emphasis.</p>
                <div class="mt-3 flex gap-2">
                    <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs">Tag 1</span>
                    <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs">Tag 2</span>
                </div>
            </div>
        </div>

        <!-- Usage Examples -->
        <div class="mt-6 p-4 bg-emerald-50 rounded-lg border border-emerald-200">
            <h4 class="text-md font-medium text-emerald-800 mb-2">Usage Examples</h4>
            <div class="text-sm text-emerald-700 space-y-1">
                <p><code class="bg-emerald-100 px-1 rounded">btn-emerald-primary</code> - Primary action buttons</p>
                <p><code class="bg-emerald-100 px-1 rounded">card-emerald</code> - Beautiful cards with emerald accents</p>
                <p><code class="bg-emerald-100 px-1 rounded">emerald-transition</code> - Smooth animations</p>
                <p><code class="bg-emerald-100 px-1 rounded">emerald-hover-glow</code> - Elegant hover effects</p>
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