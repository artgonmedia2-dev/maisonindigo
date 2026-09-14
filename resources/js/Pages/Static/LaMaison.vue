<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import { useRoute } from '@/composables/useRoute';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { Head } from '@inertiajs/vue3';

const route = useRoute();
</script>

<template>
    <Head>
        <title>La Maison — Maison Indigo</title>
        <meta name="description" content="Découvrez l'histoire de Maison Indigo, maison marocaine de denim d'exception. Toile 12-14 oz et coupes artisanales." />
    </Head>

    <StorefrontLayout>
        <div class="mi-container py-12 md:py-20">
            <div class="max-w-3xl">
                <p class="mi-caps text-mi-stone">Notre Histoire & Savoir-Faire</p>
                <h1 class="mt-4 text-hero">Le bleu, bien coupé.</h1>
                <p class="mt-6 text-[18px] italic text-mi-stone">
                    « Créer le jean que vous gardez. Une toile noble, une coupe irréprochable et un savoir-faire artisanal ancré au Maroc. »
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-12 md:grid-cols-12 md:gap-16">
                <div class="space-y-6 text-[16px] leading-relaxed text-mi-charbon/85 md:col-span-7">
                    <p>
                        Maison Indigo est née d'une conviction simple : le jean idéal n'est pas un produit jetable. C'est un vêtement de caractère qui vous accompagne au quotidien et se patine avec le temps.
                    </p>
                    <p>
                        Nous sélectionnons des toiles denim rigoureusement choisies entre 12 et 14 oz, tissées avec du coton de haute qualité. Chaque modèle est conçu avec une attention méticuleuse portée à la coupe, aux coutures en fil ocre traditionnel et aux finitions de l'atelier.
                    </p>
                    <h2 class="pt-4 text-h2 text-mi-indigo">Dix-sept tailles, trois longueurs</h2>
                    <p>
                        Parce qu'aucune silhouette ne ressemble à une autre, nous refusons les compromis du prêt-à-porter standardisé. Nos collections proposent dix-sept déclinaisons de tailles associées à trois longueurs de jambes (30, 32 et 34).
                    </p>
                    <h2 class="pt-4 text-h2 text-mi-indigo">Un engagement local et responsable</h2>
                    <p>
                        Toutes nos pièces sont assemblées au Maroc par des artisans passionnés. En privilégiant des séries limitées et une fabrication raisonnée, nous garantissons la qualité irréprochable de chaque pièce qui sort de notre atelier.
                    </p>
                </div>

                <div class="md:col-span-5">
                    <div class="border border-mi-ligne bg-mi-blanc p-8">
                        <h3 class="font-display text-h3 text-mi-indigo">Les Engagements Maison Indigo</h3>
                        <ul class="mt-6 space-y-4 text-small text-mi-charbon">
                            <li class="flex items-start gap-3">
                                <span class="font-semibold text-mi-stone">•</span>
                                <span><strong>Toile Denim 12–14 oz :</strong> Grammage d'exception offrant tenue et longévité.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="font-semibold text-mi-stone">•</span>
                                <span><strong>Paiement à la livraison :</strong> Commandez en toute confiance, vous ne réglez qu'à la réception.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="font-semibold text-mi-stone">•</span>
                                <span><strong>Échange 14 jours offert :</strong> Essayez sereinement chez vous, l'échange de taille est gratuit.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="font-semibold text-mi-stone">•</span>
                                <span><strong>Livraison Express :</strong> Expédition en 24h à 48h dans toutes les grandes villes du Maroc.</span>
                            </li>
                        </ul>

                        <div class="mt-8 pt-6 border-t border-mi-ligne">
                            <MiButton variant="primary" :href="route('collections.new')" arrow class="w-full justify-center">
                                Découvrir la collection
                            </MiButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
