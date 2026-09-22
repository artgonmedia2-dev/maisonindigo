<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';
import type { ProductSibling } from '@/types/Catalog';
import { Link } from '@inertiajs/vue3';

/**
 * « Coloris — même coupe » : les lavages disponibles dans cette coupe.
 *
 * Chaque lavage est un produit à part entière, avec son adresse et son
 * référencement : ce sont donc de vrais liens, pas un état local. Le client
 * change de coloris sans perdre la coupe qu'il a choisie.
 */
defineProps<{
    siblings: ProductSibling[];
}>();

const { t } = useI18n();
</script>

<template>
    <div v-if="siblings.length > 1">
        <p class="mi-caps text-[11px] text-mi-stone">{{ t('product.washes') }}</p>

        <ul class="mt-3 flex flex-wrap gap-2">
            <li v-for="sibling in siblings" :key="sibling.slug">
                <Link
                    :href="sibling.url"
                    class="flex items-center gap-2.5 border-[1.5px] py-1.5 pe-3.5 ps-1.5 text-[14px] font-medium transition-colors duration-150"
                    :class="sibling.current ? 'border-mi-indigo text-mi-indigo' : 'border-mi-ligne text-mi-charbon hover:border-mi-stone'"
                    :aria-current="sibling.current ? 'page' : undefined"
                >
                    <img
                        v-if="sibling.image"
                        :src="sibling.image"
                        :alt="''"
                        class="h-9 w-7 shrink-0 object-cover"
                        loading="lazy"
                        decoding="async"
                    />
                    <span v-else class="mi-twill h-9 w-7 shrink-0 bg-mi-indigo" aria-hidden="true"></span>
                    {{ sibling.label }}
                </Link>
            </li>
        </ul>
    </div>
</template>
