<script setup lang="ts">
import MiIcon from '@/Components/mi/MiIcon.vue';
import { useI18n } from '@/composables/useI18n';
import type { Pagination } from '@/types/Catalog';
import { Link } from '@inertiajs/vue3';

defineProps<{
    pagination: Pagination;
}>();

const { t } = useI18n();
</script>

<template>
    <nav v-if="pagination.last_page > 1" class="flex items-center justify-between border-t border-mi-ligne pt-6" aria-label="Pagination">
        <Link
            v-if="pagination.prev_url"
            :href="pagination.prev_url"
            class="inline-flex items-center gap-2 text-[15px] font-medium text-mi-indigo"
            preserve-scroll
        >
            <MiIcon name="arrow-back" :size="18" /> {{ t('collection.previous') }}
        </Link>
        <span v-else></span>

        <p class="text-small text-mi-fil">{{ t('collection.page', { current: pagination.current_page, last: pagination.last_page }) }}</p>

        <Link
            v-if="pagination.next_url"
            :href="pagination.next_url"
            class="inline-flex items-center gap-2 text-[15px] font-medium text-mi-indigo"
            preserve-scroll
        >
            {{ t('collection.next') }} <MiIcon name="arrow" :size="18" />
        </Link>
        <span v-else></span>
    </nav>
</template>
