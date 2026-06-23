<?php $__env->startSection('content'); ?>
<section class="relative py-24 overflow-hidden" x-data="vehicleComparator()">
    
    <div class="absolute inset-0 -z-20">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-night dark:via-night-800 dark:to-night-900"></div>
        <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] rounded-full bg-astra/4 blur-[100px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[450px] h-[450px] rounded-full bg-spark/4 blur-[90px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-astra/20 bg-astra/5 text-xs font-semibold text-astra uppercase tracking-wider mb-4">
                Outil Cantonal & Technique
            </span>
            <h1 class="font-display font-bold text-4xl sm:text-5xl text-slate-900 dark:text-white tracking-tight leading-none mb-4">
                Comparateur de Véhicules
            </h1>
            <p class="text-lg text-slate-500 dark:text-slate-400">
                Comparez côte-à-côte les caractéristiques techniques, dimensions, émissions et taxes cantonales estimées.
            </p>
        </div>

        
        <div class="mb-10 flex flex-wrap gap-3 items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Sélectionner des modèles de test :</span>
                <template x-for="mock in mockVehicles" :key="mock.slug">
                    <button
                        x-on:click="toggleVehicle(mock)"
                        x-bind:class="isCompared(mock) ? 'bg-astra text-white' : 'bg-white dark:bg-white/5 border border-slate-200 dark:border-white/5 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/10'"
                        class="px-4 py-2 rounded-xl text-xs font-semibold transition-all duration-200"
                    >
                        <span x-text="isCompared(mock) ? '✓ ' : '+ '"></span>
                        <span x-text="mock.name"></span>
                    </button>
                </template>
            </div>

            <div class="text-xs text-slate-400">
                Comparaison de <span class="font-bold text-astra" x-text="compared.length"></span> / 3 véhicules maximum
            </div>
        </div>

        
        <div class="bg-white dark:bg-marine-800/30 border border-slate-200 dark:border-white/5 rounded-3xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left min-w-[700px]">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-white/[0.02]">
                            <th class="p-6 text-sm font-bold text-slate-500 dark:text-slate-400 w-1/4">Spécifications</th>
                            <template x-for="veh in compared" :key="veh.slug">
                                <th class="p-6 text-sm font-bold text-slate-900 dark:text-white w-1/4 relative">
                                    <div class="flex items-center justify-between">
                                        <span class="font-display text-base" x-text="veh.name"></span>
                                        <button
                                            x-on:click="removeVehicle(veh)"
                                            class="p-1 rounded-lg hover:bg-red-500/10 text-slate-400 hover:text-red-500 transition-colors"
                                            title="Retirer de la comparaison"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-2xs text-slate-400 mt-1 font-mono" x-text="'TG: ' + veh.tg"></div>
                                </th>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <th class="p-6 text-sm text-slate-300 dark:text-slate-700 w-1/4 border-dashed border-l border-slate-100 dark:border-white/[0.02]">
                                    <div class="flex flex-col items-center justify-center py-4">
                                        <div class="w-8 h-8 rounded-full border border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center text-slate-400 mb-2">
                                            +
                                        </div>
                                        <span class="text-xs">Emplacement libre</span>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
                        
                        <tr>
                            <td class="p-5 text-sm font-semibold text-slate-600 dark:text-slate-400">Marque</td>
                            <template x-for="veh in compared" :key="veh.slug">
                                <td class="p-5 text-sm text-slate-900 dark:text-white font-medium" x-text="veh.brand"></td>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <td class="p-5 border-l border-slate-100 dark:border-white/[0.02]"></td>
                            </template>
                        </tr>
                        
                        <tr>
                            <td class="p-5 text-sm font-semibold text-slate-600 dark:text-slate-400">Carburant / Énergie</td>
                            <template x-for="veh in compared" :key="veh.slug">
                                <td class="p-5 text-sm text-slate-900 dark:text-white font-medium">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                          x-bind:class="veh.energy === 'Électricité' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300'"
                                          x-text="veh.energy"></span>
                                </td>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <td class="p-5 border-l border-slate-100 dark:border-white/[0.02]"></td>
                            </template>
                        </tr>
                        
                        <tr>
                            <td class="p-5 text-sm font-semibold text-slate-600 dark:text-slate-400">Puissance</td>
                            <template x-for="veh in compared" :key="veh.slug">
                                <td class="p-5 text-sm text-slate-900 dark:text-white" x-text="veh.power + ' kW (' + veh.hp + ' CV)'"></td>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <td class="p-5 border-l border-slate-100 dark:border-white/[0.02]"></td>
                            </template>
                        </tr>
                        
                        <tr>
                            <td class="p-5 text-sm font-semibold text-slate-600 dark:text-slate-400">Cylindrée</td>
                            <template x-for="veh in compared" :key="veh.slug">
                                <td class="p-5 text-sm text-slate-900 dark:text-white font-mono" x-text="veh.displacement ? veh.displacement + ' cc' : '—'"></td>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <td class="p-5 border-l border-slate-100 dark:border-white/[0.02]"></td>
                            </template>
                        </tr>
                        
                        <tr>
                            <td class="p-5 text-sm font-semibold text-slate-600 dark:text-slate-400">Poids à vide</td>
                            <template x-for="veh in compared" :key="veh.slug">
                                <td class="p-5 text-sm text-slate-900 dark:text-white" x-text="veh.weight + ' kg'"></td>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <td class="p-5 border-l border-slate-100 dark:border-white/[0.02]"></td>
                            </template>
                        </tr>
                        
                        <tr>
                            <td class="p-5 text-sm font-semibold text-slate-600 dark:text-slate-400">Émissions CO₂</td>
                            <template x-for="veh in compared" :key="veh.slug">
                                <td class="p-5 text-sm text-slate-900 dark:text-white" x-text="veh.co2 + ' g/km'"></td>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <td class="p-5 border-l border-slate-100 dark:border-white/[0.02]"></td>
                            </template>
                        </tr>
                        
                        <tr class="bg-blue-500/2 dark:bg-astra/5">
                            <td class="p-5 text-sm font-bold text-slate-700 dark:text-slate-300">Impôt estimé (VD)</td>
                            <template x-for="veh in compared" :key="veh.slug">
                                <td class="p-5 text-sm font-bold text-slate-900 dark:text-white" x-text="veh.tax_vd + ' CHF/an'"></td>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <td class="p-5 border-l border-slate-100 dark:border-white/[0.02]"></td>
                            </template>
                        </tr>
                        
                        <tr class="bg-blue-500/2 dark:bg-astra/5">
                            <td class="p-5 text-sm font-bold text-slate-700 dark:text-slate-300">Impôt estimé (GE)</td>
                            <template x-for="veh in compared" :key="veh.slug">
                                <td class="p-5 text-sm font-bold text-slate-900 dark:text-white" x-text="veh.tax_ge + ' CHF/an'"></td>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <td class="p-5 border-l border-slate-100 dark:border-white/[0.02]"></td>
                            </template>
                        </tr>
                        
                        <tr class="bg-blue-500/2 dark:bg-astra/5">
                            <td class="p-5 text-sm font-bold text-slate-700 dark:text-slate-300">Impôt estimé (ZH)</td>
                            <template x-for="veh in compared" :key="veh.slug">
                                <td class="p-5 text-sm font-bold text-slate-900 dark:text-white" x-text="veh.tax_zh + ' CHF/an'"></td>
                            </template>
                            <template x-for="i in Array(Math.max(0, 3 - compared.length))" :key="i">
                                <td class="p-5 border-l border-slate-100 dark:border-white/[0.02]"></td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        
        <p class="mt-4 text-xs text-slate-400 text-center">
            * Les impôts cantonaux sont calculés à titre purement indicatif sur la base des grilles fiscales 2024 de chaque canton (VD: selon puissance + CO₂, GE: selon puissance fisc, ZH: selon poids total + cylindrée/puissance).
        </p>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function vehicleComparator() {
    return {
        compared: [],
        mockVehicles: [
            {
                name: 'VW Golf 2.0 TDI',
                slug: 'vw-golf-20-tdi',
                brand: 'Volkswagen',
                tg: '1VA234',
                energy: 'Diesel',
                power: 110,
                hp: 150,
                displacement: 1968,
                weight: 1465,
                co2: 118,
                tax_vd: 320,
                tax_ge: 410,
                tax_zh: 280
            },
            {
                name: 'Tesla Model 3 Dual Motor',
                slug: 'tesla-model-3-dual-motor',
                brand: 'Tesla',
                tg: '1TA567',
                energy: 'Électricité',
                power: 366,
                hp: 498,
                displacement: 0,
                weight: 1847,
                co2: 0,
                tax_vd: 120, // Discounted rates for pure electric
                tax_ge: 0,   // Exemption
                tax_zh: 150
            },
            {
                name: 'BMW 330i xDrive',
                slug: 'bmw-330i-xdrive',
                brand: 'BMW',
                tg: '1BA890',
                energy: 'Essence',
                power: 180,
                hp: 245,
                displacement: 1998,
                weight: 1670,
                co2: 154,
                tax_vd: 480,
                tax_ge: 630,
                tax_zh: 390
            }
        ],
        init() {
            // Pre-select first two vehicles
            this.compared = [this.mockVehicles[0], this.mockVehicles[1]];
        },
        isCompared(vehicle) {
            return this.compared.some(v => v.slug === vehicle.slug);
        },
        toggleVehicle(vehicle) {
            if (this.isCompared(vehicle)) {
                this.removeVehicle(vehicle);
            } else {
                if (this.compared.length >= 3) {
                    alert("Vous pouvez comparer un maximum de 3 véhicules simultanément.");
                    return;
                }
                this.compared.push(vehicle);
            }
        },
        removeVehicle(vehicle) {
            this.compared = this.compared.filter(v => v.slug !== vehicle.slug);
        }
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\DiskFiles\Projects\Web\upwork_Invoice_Ninja\reception-par-type\resources\views/pages/compare.blade.php ENDPATH**/ ?>