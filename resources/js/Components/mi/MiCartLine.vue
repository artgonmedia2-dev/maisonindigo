<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import MiPrice from '@/Components/mi/MiPrice.vue';
import MiQuantity from '@/Components/mi/MiQuantity.vue';
import { useCart } from '@/composables/useCart';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import type { CartLine } from '@/types/Cart';
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

/**
 * Ligne de panier : vignette 4:5, titre, taille / longueur, quantité, prix, retrait.
 */
const props = withDefaults(
    defineProps<{
        line: CartLine;
        compact?: boolean;
    }>(),
    { compact: false },
);

const { t } = useI18n();
const route = useRoute();
const { update, remove, busy } = useCart();

const qty = ref(props.line.qty);

watch(
    () => props.line.qty,
    (value) => {
        qty.value = value;
    },
);
</script>

<template>
    <div class="flex gap-4" :class="compact ? 'py-4' : 'py-6'">
        <Link :href="route('product.show', line.slug)" class="block shrink-0 overflow-hidden bg-mi-ecru" :class="compact ? 'w-16' : 'w-24'">
            <img
                v-if="line.image"
                :src="line.image.src"
                :srcset="line.image.srcset"
                sizes="96px"
                :alt="line.image.alt"
                class="aspect-[4/5] w-full object-cover"
                loading="lazy"
                decoding="async"
            />
            <div v-else class="mi-twill aspect-[4/5] w-full bg-mi-indigo" aria-hidden="true"></div>
        </Link>

        <div class="flex min-w-0 flex-1 flex-col gap-2">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <Link :href="route('product.show', line.slug)" class="mi-link font-display text-[1.125rem] font-semibold leading-tight text-mi-indigo">
                        {{ line.title }}
                    </Link>
                    <p class="mt-0.5 text-small text-mi-fil">{{ line.gender }} · {{ line.size }} / {{ line.length }}</p>
                </div>
                <MiPrice :amount="line.total" :size="compact ? 'sm' : 'md'" class="shrink-0" />
            </div>

            <div class="mt-auto flex items-center justify-between gap-4">
                <MiQuantity v-model="qty" :max="Math.min(5, line.stock)" :disabled="busy" :compact="compact" @change="update(line.id, $event)" />
                <button
                    type="button"
                    class="mi-link inline-flex items-center gap-1.5 text-small text-mi-fil transition-colors duration-150 hover:text-mi-indigo"
                    :disabled="busy"
                    @click="remove(line.id)"
                >
                    <MiIcon name="close" :size="14" /> {{ t('cart.remove') }}
                </button>
            </div>
        </div>
    </div>
</template>
