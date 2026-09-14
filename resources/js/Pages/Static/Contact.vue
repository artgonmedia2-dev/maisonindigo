<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiIcon from '@/Components/mi/MiIcon.vue';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const maison = computed(() => page.props.maison);
const whatsappHref = computed(() => `https://wa.me/${maison.value.contact.whatsapp.replace(/[^\d]/g, '')}`);
</script>

<template>
    <Head>
        <title>Contact — Maison Indigo</title>
        <meta name="description" content="Contactez la Maison Indigo. Support client par WhatsApp et e-mail à Casablanca." />
    </Head>

    <StorefrontLayout>
        <div class="mi-container py-12 md:py-20">
            <div class="max-w-3xl">
                <p class="mi-caps text-mi-stone">Service Client & Atelier</p>
                <h1 class="mt-4 text-hero">Nous joindre</h1>
                <p class="mt-6 text-[17px] leading-relaxed text-mi-charbon/85">
                    Une question sur une coupe, un échange de taille ou le suivi de votre commande ? Notre équipe vous répond rapidement du lundi au samedi.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-2">
                <!-- WhatsApp direct -->
                <div class="border border-mi-ligne bg-mi-blanc p-8">
                    <div class="flex h-12 w-12 items-center justify-center bg-mi-indigo/5 text-mi-indigo">
                        <MiIcon name="chat" :size="24" />
                    </div>
                    <h2 class="mt-4 text-h3">WhatsApp Direct</h2>
                    <p class="mt-2 text-small text-mi-charbon/80">
                        Pour une réponse instantanée ou pour échanger votre taille en 1 clic.
                    </p>
                    <p class="mt-4 font-semibold text-mi-indigo">{{ maison.contact.whatsapp }}</p>
                    <div class="mt-6">
                        <a
                            :href="whatsappHref"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-2 bg-mi-indigo px-5 py-3 text-small font-semibold text-mi-ecru transition-colors hover:bg-mi-indigo-deep"
                        >
                            Écrire sur WhatsApp →
                        </a>
                    </div>
                </div>

                <!-- E-mail -->
                <div class="border border-mi-ligne bg-mi-blanc p-8">
                    <div class="flex h-12 w-12 items-center justify-center bg-mi-indigo/5 text-mi-indigo">
                        <MiIcon name="mail" :size="24" />
                    </div>
                    <h2 class="mt-4 text-h3">Adresse E-mail</h2>
                    <p class="mt-2 text-small text-mi-charbon/80">
                        Pour les demandes d'information ou les conseils personnalisés.
                    </p>
                    <p class="mt-4 font-semibold text-mi-indigo">{{ maison.contact.email }}</p>
                    <div class="mt-6">
                        <a
                            :href="`mailto:${maison.contact.email}`"
                            class="inline-flex items-center gap-2 border border-mi-indigo px-5 py-3 text-small font-semibold text-mi-indigo transition-colors hover:bg-mi-indigo hover:text-mi-ecru"
                        >
                            Envoyer un e-mail →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Atelier -->
            <div class="mt-12 border border-mi-ligne bg-mi-ecru/60 p-8">
                <h3 class="font-display text-h3 text-mi-indigo">Maison & Atelier</h3>
                <p class="mt-2 text-[15px] text-mi-charbon/85">
                    {{ maison.contact.city }} · Maroc. Expédition dans toutes les villes du Royaume.
                </p>
            </div>
        </div>
    </StorefrontLayout>
</template>
