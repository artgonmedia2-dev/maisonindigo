<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';
import { computed } from 'vue';

/**
 * Badge « patch » : coins coupés en biais, comme une étiquette cousue.
 * Seuls trois badges existent : Nouveau, Édition limitée, Atelier.
 */
type Kind = 'new' | 'limited' | 'atelier';

const props = defineProps<{
    kind: Kind;
}>();

const { t } = useI18n();

const kindClass: Record<Kind, string> = {
    new: 'bg-mi-indigo text-mi-blanc',
    limited: 'bg-mi-ocre text-mi-blanc',
    atelier: 'bg-mi-ecru text-mi-indigo shadow-[inset_0_0_0_1px_var(--color-mi-indigo)]',
};

const label = computed(() => t(`patch.${props.kind}`));
</script>

<template>
    <span
        class="mi-patch-clip inline-block px-2.5 py-1.5 text-xs font-semibold leading-none"
        :class="kindClass[kind]"
    >
        {{ label }}
    </span>
</template>
