<script setup lang="ts">
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Conditions Générales de Vente (CGV) — Maison Indigo</title>
        <meta name="description" content="Conditions générales de vente de la boutique Maison Indigo au Maroc." />
    </Head>

    <StorefrontLayout>
        <div class="mi-container py-12 md:py-20">
            <div class="max-w-3xl">
                <p class="mi-caps text-mi-stone">Informations légales</p>
                <h1 class="mt-4 text-hero">Conditions Générales de Vente</h1>
                <p class="mt-6 text-[15px] leading-relaxed text-mi-charbon/85">
                    Dernière mise à jour : 2026.
                </p>
            </div>

            <hr class="my-10 border-mi-ligne" />

            <div class="max-w-3xl space-y-8 text-[15px] leading-relaxed text-mi-charbon/85">
                <section>
                    <h2 class="text-h3 text-mi-indigo">1. Objet</h2>
                    <p class="mt-2">
                        Les présentes Conditions Générales de Vente régissent l'ensemble des relations entre Maison Indigo et toute personne commandant des articles de denim sur la boutique en ligne.
                    </p>
                </section>

                <section>
                    <h2 class="text-h3 text-mi-indigo">2. Prix & Modalités de paiement</h2>
                    <p class="mt-2">
                        Les prix affichés sont en Dirhams Marocains (MAD) toutes taxes comprises. Le paiement s'effectue à la livraison (règlement en espèces au livreur) ou par virement bancaire. Aucun paiement par carte bancaire n'est requis sur le site.
                    </p>
                </section>

                <section>
                    <h2 class="text-h3 text-mi-indigo">3. Livraison</h2>
                    <p class="mt-2">
                        Les livraisons sont assurées sur l'ensemble du territoire marocain dans un délai habituel de 24 à 48 heures à compter de la confirmation téléphonique ou WhatsApp de la commande.
                    </p>
                </section>

                <section>
                    <h2 class="text-h3 text-mi-indigo">4. Droit de rétractation & Échanges</h2>
                    <p class="mt-2">
                        Le client dispose d'un délai de 14 jours à compter de la réception de son colis pour demander un échange de taille ou de modèle. L'échange de taille est offert sans frais supplémentaires.
                    </p>
                </section>
            </div>
        </div>
    </StorefrontLayout>
</template>
