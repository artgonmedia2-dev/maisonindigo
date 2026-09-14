<script setup lang="ts">
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Politique de Confidentialité — Maison Indigo</title>
        <meta name="description" content="Protection des données personnelles et politique de confidentialité chez Maison Indigo." />
    </Head>

    <StorefrontLayout>
        <div class="mi-container py-12 md:py-20">
            <div class="max-w-3xl">
                <p class="mi-caps text-mi-stone">Informations légales</p>
                <h1 class="mt-4 text-hero">Politique de Confidentialité</h1>
                <p class="mt-6 text-[15px] leading-relaxed text-mi-charbon/85">
                    Protection de vos données personnelles et respect de votre vie privée.
                </p>
            </div>

            <hr class="my-10 border-mi-ligne" />

            <div class="max-w-3xl space-y-8 text-[15px] leading-relaxed text-mi-charbon/85">
                <section>
                    <h2 class="text-h3 text-mi-indigo">Collecte des données</h2>
                    <p class="mt-2">
                        Les informations collectées (nom, adresse de livraison, téléphone, e-mail) sont uniquement destinées au traitement de votre commande et à la communication du suivi de livraison par WhatsApp ou e-mail.
                    </p>
                </section>

                <section>
                    <h2 class="text-h3 text-mi-indigo">Usage des données</h2>
                    <p class="mt-2">
                        Vos données personnelles ne sont jamais vendues, louées ou cédées à des tiers. Elles restent strictement confidentielles et réservées à l'usage interne de Maison Indigo.
                    </p>
                </section>
            </div>
        </div>
    </StorefrontLayout>
</template>
