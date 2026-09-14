<script setup lang="ts">
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Entretien du Denim — Maison Indigo</title>
        <meta name="description" content="Conseils d'entretien pour prolonger la vie de vos jeans en denim brut et les laver dans les règles de l'art." />
    </Head>

    <StorefrontLayout>
        <div class="mi-container py-12 md:py-20">
            <div class="max-w-3xl">
                <p class="mi-caps text-mi-stone">Guide de soin</p>
                <h1 class="mt-4 text-hero">Prendre soin de son jean</h1>
                <p class="mt-6 text-[17px] leading-relaxed text-mi-charbon/85">
                    Un bon denim vit avec vous. Plus vous le portez et moins vous le lavez, plus la patine naturelle de l'indigo révèle sa beauté unique.
                </p>
            </div>

            <hr class="my-12 border-mi-ligne" />

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="border border-mi-ligne bg-mi-blanc p-8">
                    <div class="font-display text-h3 text-mi-stone">01</div>
                    <h2 class="mt-3 text-h3">Lavage à l'envers</h2>
                    <p class="mt-3 text-small text-mi-charbon/80 leading-relaxed">
                        Retournez toujours votre jean avant de le mettre en machine pour préserver l'intensité de l'indigo et éviter les marbrures sur la toile.
                    </p>
                </div>

                <div class="border border-mi-ligne bg-mi-blanc p-8">
                    <div class="font-display text-h3 text-mi-stone">02</div>
                    <h2 class="mt-3 text-h3">30°C maximum</h2>
                    <p class="mt-3 text-small text-mi-charbon/80 leading-relaxed">
                        Optez pour un cycle délicat à 30°C sans adoucissant. Un essorage modéré (600 tr/min max) protège la fibre de coton.
                    </p>
                </div>

                <div class="border border-mi-ligne bg-mi-blanc p-8">
                    <div class="font-display text-h3 text-mi-stone">03</div>
                    <h2 class="mt-3 text-h3">Séchage à l'air libre</h2>
                    <p class="mt-3 text-small text-mi-charbon/80 leading-relaxed">
                        Bannissez le sèche-linge. Laissez sécher votre jean suspendu à l'ombre. Le repassage n'est pas nécessaire : la toile reprend sa forme au porté.
                    </p>
                </div>
            </div>

            <div class="mt-12 border border-mi-ligne bg-mi-ecru/60 p-8">
                <h3 class="font-display text-h3 text-mi-indigo">Le conseil de l'Atelier</h3>
                <p class="mt-3 text-[15px] text-mi-charbon/85 leading-relaxed">
                    Pour un premier lavage sur un denim brut, attendez idéalement 3 à 6 mois de porté régulier. Si votre jean a besoin d'être rafraîchi entre deux lavages, aérez-le une nuit en extérieur.
                </p>
            </div>
        </div>
    </StorefrontLayout>
</template>
