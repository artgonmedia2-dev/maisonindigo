<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';
import type { BreadcrumbItem } from '@/types/Seo';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Fil d'Ariane : Accueil › Homme › Jean baggy › Baggy Indigo Brut.
 *
 * Les adresses arrivent absolues du serveur, qui s'en sert aussi pour le
 * schema BreadcrumbList. On les rend relatives ici pour que la navigation
 * Inertia reste interne.
 */
const props = defineProps<{
    items: BreadcrumbItem[];
}>();

const { t } = useI18n();

const trail = computed(() =>
    props.items.map((item, index) => ({
        name: item.name,
        href: item.url.replace(/^https?:\/\/[^/]+/, '') || '/',
        last: index === props.items.length - 1,
    })),
);
</script>

<template>
    <nav v-if="trail.length > 1" :aria-label="t('a11y.breadcrumb')" class="mi-container pt-6 md:pt-8">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-small text-mi-fil">
            <li v-for="item in trail" :key="item.href" class="flex items-center gap-x-2">
                <Link v-if="!item.last" :href="item.href" class="mi-link transition-colors duration-150 hover:text-mi-indigo">
                    {{ item.name }}
                </Link>
                <span v-else class="text-mi-charbon" aria-current="page">{{ item.name }}</span>
                <MiIcon v-if="!item.last" name="arrow" :size="12" aria-hidden="true" class="text-mi-ligne" />
            </li>
        </ol>
    </nav>
</template>
