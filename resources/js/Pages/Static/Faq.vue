<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import { useRoute } from '@/composables/useRoute';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const route = useRoute();

interface FaqItem {
    question: string;
    answer: string;
    category: string;
}

const faqs: FaqItem[] = [
    {
        category: 'Commandes & Paiement',
        question: 'Comment fonctionne le paiement à la livraison (COD) ?',
        answer: 'Vous passez votre commande sans aucune carte bancaire. Nous confirmons votre commande par WhatsApp puis nous expédions. Vous réglez directement le montant en espèces auprès du livreur à la réception.',
    },
    {
        category: 'Commandes & Paiement',
        question: 'Quels sont les modes de paiement acceptés ?',
        answer: 'Nous acceptons le paiement en espèces à la livraison (partout au Maroc) ainsi que le paiement par virement bancaire sur notre compte professionnel.',
    },
    {
        category: 'Livraison',
        question: 'Quels sont les délais et tarifs de livraison ?',
        answer: 'La livraison s\'effectue en 24h à 48h dans les grandes villes du Maroc (Casablanca, Rabat, Marrakech, Tanger, Fès, Agadir...). La livraison est offerte à partir de 500 MAD de commande.',
    },
    {
        category: 'Retours & Échanges',
        question: 'Comment échanger ma taille si le jean ne me convient pas ?',
        answer: 'L\'échange de taille est totalement gratuit sous 14 jours. Écrivez-nous sur WhatsApp, nous vous envoyons la nouvelle taille et le livreur récupère l\'ancien modèle en même temps.',
    },
    {
        category: 'Tailles & Coupes',
        question: 'Comment choisir ma taille idéale ?',
        answer: 'Utilisez notre outil "Trouver ma taille" (en 4 questions simples) ou consultez notre Guide des mesures. En cas de doute entre deux tailles, nous vous conseillons de choisir la taille inférieure car le tissu se détend légèrement au porté.',
    },
];

const openIndex = ref<number | null>(0);

const toggle = (index: number) => {
    openIndex.value = openIndex.value === index ? null : index;
};
</script>

<template>
    <Head>
        <title>Foire Aux Questions (FAQ) — Maison Indigo</title>
        <meta name="description" content="Toutes les réponses à vos questions sur les livraisons, le paiement à la livraison et les échanges offerts sous 14 jours." />
    </Head>

    <StorefrontLayout>
        <div class="mi-container py-12 md:py-20">
            <div class="max-w-3xl">
                <p class="mi-caps text-mi-stone">Questions fréquentes</p>
                <h1 class="mt-4 text-hero">Foire Aux Questions</h1>
                <p class="mt-6 text-[17px] leading-relaxed text-mi-charbon/85">
                    Retrouvez ici toutes les informations pratiques sur le fonctionnement des commandes, la livraison au Maroc et les échanges gratuits.
                </p>
            </div>

            <div class="mt-12 max-w-3xl space-y-4">
                <div
                    v-for="(faq, index) in faqs"
                    :key="index"
                    class="border border-mi-ligne bg-mi-blanc transition-colors duration-150"
                >
                    <button
                        type="button"
                        class="flex w-full items-center justify-between p-6 text-left"
                        @click="toggle(index)"
                    >
                        <span class="font-display text-[1.125rem] font-semibold text-mi-indigo">
                            {{ faq.question }}
                        </span>
                        <span class="ms-4 shrink-0 text-h3 text-mi-stone">
                            {{ openIndex === index ? '−' : '+' }}
                        </span>
                    </button>
                    <div
                        v-if="openIndex === index"
                        class="border-t border-mi-ligne/60 px-6 py-5 text-[15px] leading-relaxed text-mi-charbon/85"
                    >
                        {{ faq.answer }}
                    </div>
                </div>
            </div>

            <div class="mt-16 border border-mi-ligne bg-mi-ecru p-8 max-w-3xl">
                <h3 class="font-display text-h3 text-mi-indigo">Vous avez encore une question ?</h3>
                <p class="mt-2 text-small text-mi-charbon/80">
                    Notre équipe est joignable directement par WhatsApp ou e-mail pour vous guider.
                </p>
                <div class="mt-6 flex flex-wrap gap-4">
                    <MiButton variant="primary" :href="route('size-quiz')">
                        Quiz Taille
                    </MiButton>
                    <a
                        href="/contact"
                        class="mi-link inline-flex items-center text-small font-semibold text-mi-indigo"
                    >
                        Nous contacter →
                    </a>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>
