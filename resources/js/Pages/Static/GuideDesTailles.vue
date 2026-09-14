<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiIcon from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { Head } from '@inertiajs/vue3';

const { t } = useI18n();
const route = useRoute();

const womenSizes = [
    { size: '26 (34)', waist: '64-67 cm', hips: '88-91 cm', fr: '34' },
    { size: '27 (36)', waist: '68-71 cm', hips: '92-95 cm', fr: '36' },
    { size: '28 (38)', waist: '72-75 cm', hips: '96-99 cm', fr: '38' },
    { size: '29 (40)', waist: '76-79 cm', hips: '100-103 cm', fr: '40' },
    { size: '30 (42)', waist: '80-83 cm', hips: '104-107 cm', fr: '42' },
    { size: '31 (44)', waist: '84-87 cm', hips: '108-111 cm', fr: '44' },
];

const menSizes = [
    { size: '29 (38)', waist: '74-77 cm', hips: '90-93 cm', fr: '38' },
    { size: '30 (40)', waist: '78-81 cm', hips: '94-97 cm', fr: '40' },
    { size: '31 (42)', waist: '82-85 cm', hips: '98-101 cm', fr: '42' },
    { size: '32 (44)', waist: '86-89 cm', hips: '102-105 cm', fr: '44' },
    { size: '33 (46)', waist: '90-93 cm', hips: '106-109 cm', fr: '46' },
    { size: '34 (48)', waist: '94-97 cm', hips: '110-113 cm', fr: '48' },
    { size: '36 (50)', waist: '98-103 cm', hips: '114-118 cm', fr: '50' },
];
</script>

<template>
    <Head>
        <title>Guide des Tailles — Maison Indigo</title>
        <meta name="description" content="Guide des mesures et équivalences de tailles pour jeans Maison Indigo. Femme et Homme." />
    </Head>

    <StorefrontLayout>
        <div class="mi-container py-12 md:py-20">
            <!-- Hero -->
            <div class="max-w-3xl">
                <p class="mi-caps text-mi-stone">Aide & Recommandations</p>
                <h1 class="mt-4 text-hero">Guide des Tailles</h1>
                <p class="mt-6 text-[17px] leading-relaxed text-mi-charbon/85">
                    Trouvez la coupe et la taille idéales. Nos jeans sont coupés selon les standards artisanaux dans une toile de 12 à 14 oz. Entre deux tailles, choisissez la plus petite : la toile denim se détend légèrement au porté.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <MiButton variant="primary" :href="route('size-quiz')" arrow>
                        Faire le Quiz Taille (2 min)
                    </MiButton>
                </div>
            </div>

            <hr class="my-12 border-mi-ligne" />

            <!-- Comment mesurer -->
            <section class="mb-16">
                <h2 class="text-h2">Comment prendre vos mesures</h2>
                <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div class="border border-mi-ligne bg-mi-blanc p-6">
                        <div class="font-display text-h3 text-mi-stone">01</div>
                        <h3 class="mt-2 font-semibold text-mi-indigo">Tour de taille</h3>
                        <p class="mt-2 text-small text-mi-charbon/80">
                            Mesurez horizontalement à l'endroit le plus étroit de votre buste (généralement au-dessus du nombril), sans serrer le mètre ruban.
                        </p>
                    </div>

                    <div class="border border-mi-ligne bg-mi-blanc p-6">
                        <div class="font-display text-h3 text-mi-stone">02</div>
                        <h3 class="mt-2 font-semibold text-mi-indigo">Tour de hanches</h3>
                        <p class="mt-2 text-small text-mi-charbon/80">
                            Mesurez horizontalement à l'endroit le plus fort de votre bassin et des fessiers.
                        </p>
                    </div>

                    <div class="border border-mi-ligne bg-mi-blanc p-6">
                        <div class="font-display text-h3 text-mi-stone">03</div>
                        <h3 class="mt-2 font-semibold text-mi-indigo">Longueur d'entrejambe</h3>
                        <p class="mt-2 text-small text-mi-charbon/80">
                            Mesurez le long de l'intérieur de la me de la cuisse jusqu'à la cheville. Nous proposons les longueurs 30, 32 et 34.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Tableau Femme -->
            <section class="mb-16">
                <h2 class="text-h2">Guide des tailles — Collection Femme</h2>
                <div class="mt-6 overflow-x-auto border border-mi-ligne bg-mi-blanc">
                    <table class="w-full text-left text-small">
                        <thead class="border-b border-mi-ligne bg-mi-ecru/50 font-semibold text-mi-indigo">
                            <tr>
                                <th class="px-6 py-4">Taille US (US / FR)</th>
                                <th class="px-6 py-4">Équivalence FR</th>
                                <th class="px-6 py-4">Tour de taille</th>
                                <th class="px-6 py-4">Tour de hanches</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-mi-ligne text-mi-charbon">
                            <tr v-for="item in womenSizes" :key="item.size" class="hover:bg-mi-ecru/20">
                                <td class="px-6 py-3.5 font-semibold text-mi-indigo">{{ item.size }}</td>
                                <td class="px-6 py-3.5">{{ item.fr }}</td>
                                <td class="px-6 py-3.5">{{ item.waist }}</td>
                                <td class="px-6 py-3.5">{{ item.hips }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Tableau Homme -->
            <section class="mb-16">
                <h2 class="text-h2">Guide des tailles — Collection Homme</h2>
                <div class="mt-6 overflow-x-auto border border-mi-ligne bg-mi-blanc">
                    <table class="w-full text-left text-small">
                        <thead class="border-b border-mi-ligne bg-mi-ecru/50 font-semibold text-mi-indigo">
                            <tr>
                                <th class="px-6 py-4">Taille US</th>
                                <th class="px-6 py-4">Équivalence FR</th>
                                <th class="px-6 py-4">Tour de taille</th>
                                <th class="px-6 py-4">Tour de hanches</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-mi-ligne text-mi-charbon">
                            <tr v-for="item in menSizes" :key="item.size" class="hover:bg-mi-ecru/20">
                                <td class="px-6 py-3.5 font-semibold text-mi-indigo">{{ item.size }}</td>
                                <td class="px-6 py-3.5">{{ item.fr }}</td>
                                <td class="px-6 py-3.5">{{ item.waist }}</td>
                                <td class="px-6 py-3.5">{{ item.hips }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Échange offert -->
            <div class="border border-mi-indigo/20 bg-mi-indigo-deep p-8 text-mi-ecru">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="font-display text-h3 text-mi-ecru">Un doute sur votre taille ?</h3>
                        <p class="mt-2 text-small text-mi-ciel">
                            L'échange de taille est 100% gratuit sous 14 jours. Nous vous livrons la nouvelle taille à domicile et récupérons l'ancienne sans frais.
                        </p>
                    </div>
                    <MiButton variant="outline" tone="ecru" :href="route('size-quiz')" class="shrink-0">
                        Trouver ma taille
                    </MiButton>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
