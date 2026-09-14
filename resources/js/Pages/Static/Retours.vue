<script setup lang="ts">
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Retours & Échanges — Maison Indigo</title>
        <meta name="description" content="Politique de retours et échanges gratuits sous 14 jours chez Maison Indigo." />
    </Head>

    <StorefrontLayout>
        <div class="mi-container py-12 md:py-20">
            <div class="max-w-3xl">
                <p class="mi-caps text-mi-stone">Service Client</p>
                <h1 class="mt-4 text-hero">Retours & Échanges</h1>
                <p class="mt-6 text-[17px] leading-relaxed text-mi-charbon/85">
                    Votre satisfaction est notre priorité. Si la taille ne convient pas parfaitement, nous vous échangeons votre jean gratuitement sous 14 jours.
                </p>
            </div>

            <hr class="my-10 border-mi-ligne" />

            <div class="max-w-3xl space-y-8 text-[15px] leading-relaxed text-mi-charbon/85">
                <section class="border border-mi-ligne bg-mi-blanc p-8">
                    <h2 class="text-h3 text-mi-indigo">Échange de taille 100% Offert</h2>
                    <p class="mt-3">
                        Si votre jean est trop grand ou trop petit, contactez-nous simplement sur WhatsApp avec votre numéro de commande. Nous préparons la nouvelle taille et le livreur repasse chez vous pour effectuer l'échange sans aucun frais supplémentaire.
                    </p>
                </section>

                <section>
                    <h2 class="text-h3 text-mi-indigo">Conditions pour effectuer un retour</h2>
                    <ul class="mt-3 space-y-2 list-disc list-inside text-mi-charbon/80">
                        <li>Le jean doit être neuf, non porté et non lavé.</li>
                        <li>Toutes les étiquettes de marque d'origine doivent être conservées.</li>
                        <li>La demande doit être effectuée dans les 14 jours suivant la réception du colis.</li>
                    </ul>
                </section>
            </div>
        </div>
    </StorefrontLayout>
</template>
