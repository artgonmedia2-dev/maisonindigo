<script setup lang="ts">
import MiButton from '@/Components/mi/MiButton.vue';
import MiInput from '@/Components/mi/MiInput.vue';
import MiProductCard from '@/Components/mi/MiProductCard.vue';
import MiRadioCard from '@/Components/mi/MiRadioCard.vue';
import { useI18n } from '@/composables/useI18n';
import { useRoute } from '@/composables/useRoute';
import StorefrontLayout from '@/Layouts/StorefrontLayout.vue';
import type { SizeQuizPageProps } from '@/types/SizeQuiz';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<SizeQuizPageProps>();

const { t } = useI18n();
const route = useRoute();

const TOTAL_STEPS = 4;
const step = ref(1);

const form = useForm({
    gender: props.answers?.gender ?? '',
    waist_cm: props.answers === null ? '' : String(props.answers.waist_cm),
    height_cm: props.answers === null ? '' : String(props.answers.height_cm),
    hips: props.answers?.hips ?? '',
    fit: props.answers?.fit ?? '',
});

const stepTitle = computed(() => {
    switch (step.value) {
        case 1:
            return t('quiz.stepGender');
        case 2:
            return t('quiz.stepMeasures');
        case 3:
            return t('quiz.stepHips');
        default:
            return t('quiz.stepFit');
    }
});

const canContinue = computed(() => {
    switch (step.value) {
        case 1:
            return form.gender !== '';
        case 2:
            return Number(form.waist_cm) >= 60 && Number(form.height_cm) >= 140;
        case 3:
            return form.hips !== '';
        default:
            return form.fit !== '';
    }
});

const next = (): void => {
    if (step.value < TOTAL_STEPS && canContinue.value) step.value++;
};

const back = (): void => {
    if (step.value > 1) step.value--;
};

const submit = (): void => {
    form.transform((data) => ({ ...data, waist_cm: Number(data.waist_cm), height_cm: Number(data.height_cm) })).post(route('size-quiz.store'));
};

const restart = (): void => {
    router.delete(route('size-quiz.reset'));
};

const collectionRoute = computed(() => (props.result?.gender === 'femme' ? 'collections.women' : 'collections.men'));
</script>

<template>
    <Head>
        <title>{{ meta.title ?? '' }}</title>
        <meta v-if="meta.description" name="description" :content="meta.description" />
    </Head>

    <StorefrontLayout>
        <!-- Résultat -->
        <section v-if="result" class="mi-container py-14 md:py-20">
            <p class="mi-caps text-mi-stone">{{ t('quiz.resultKicker') }}</p>
            <h1 class="mt-4 max-w-3xl text-h1 md:text-[3.5rem] md:leading-[1.05]">
                {{ t('quiz.resultTitle', { cut: result.cut_label, size: result.label }) }}
            </h1>

            <dl class="mt-10 grid max-w-2xl grid-cols-3 divide-x divide-mi-ligne border-y border-mi-ligne">
                <div class="px-4 py-5 first:ps-0">
                    <dt class="mi-caps text-mi-fil">{{ t('quiz.resultCut') }}</dt>
                    <dd class="mt-2 font-display text-h2 text-mi-indigo">{{ result.cut_label }}</dd>
                </div>
                <div class="px-4 py-5">
                    <dt class="mi-caps text-mi-fil">{{ t('quiz.resultSize') }}</dt>
                    <dd class="mt-2 font-display text-h2 tabular-nums text-mi-indigo">{{ result.size }}</dd>
                </div>
                <div class="px-4 py-5">
                    <dt class="mi-caps text-mi-fil">{{ t('quiz.resultLength') }}</dt>
                    <dd class="mt-2 font-display text-h2 tabular-nums text-mi-indigo">{{ result.length }}</dd>
                </div>
            </dl>

            <p class="mt-6 max-w-2xl text-[17px] leading-relaxed text-mi-charbon/85">{{ result.advice }}</p>
            <p class="mt-2 max-w-2xl text-[15px] text-mi-fil">{{ t('quiz.resultAlternative', { cut: result.alternative_cut_label }) }}</p>

            <div class="mt-8 flex flex-wrap items-center gap-4">
                <MiButton variant="outline" :href="route(collectionRoute)" arrow>{{ result.gender_label }}</MiButton>
                <button type="button" class="mi-link text-[15px] font-medium text-mi-stone" @click="restart">{{ t('quiz.restart') }}</button>
            </div>

            <div class="mt-16">
                <h2 class="text-h2">{{ products.length > 0 ? t('quiz.resultProducts') : t('quiz.resultEmpty') }}</h2>
                <div v-if="products.length > 0" class="mt-8 grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 md:gap-x-8">
                    <MiProductCard v-for="product in products" :key="product.id" :product="product" />
                </div>
                <MiButton v-else variant="primary" :href="route(collectionRoute)" class="mt-6" arrow>{{ result.gender_label }}</MiButton>
            </div>
        </section>

        <!-- Quiz -->
        <section v-else class="mi-container grid grid-cols-1 gap-12 py-14 md:grid-cols-12 md:py-20">
            <div class="md:col-span-5">
                <p class="mi-caps text-mi-stone">{{ t('quiz.kicker') }}</p>
                <h1 class="mt-4 text-h1">{{ t('quiz.title') }}</h1>
                <p class="mt-5 text-[17px] leading-relaxed text-mi-charbon/85">{{ t('quiz.lead') }}</p>
            </div>

            <form class="md:col-span-7" novalidate @submit.prevent="submit">
                <div class="mi-card p-7 md:p-9">
                    <div class="flex items-center justify-between">
                        <p class="mi-caps text-mi-fil">{{ t('quiz.step', { current: step, total: TOTAL_STEPS }) }}</p>
                        <ol class="flex gap-1.5" aria-hidden="true">
                            <li v-for="n in TOTAL_STEPS" :key="n" class="h-0.5 w-8 transition-colors duration-150" :class="n <= step ? 'bg-mi-ocre' : 'bg-mi-ligne'"></li>
                        </ol>
                    </div>
                    <h2 class="mt-3 text-h2">{{ stepTitle }}</h2>

                    <div v-if="step === 1" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <MiRadioCard v-for="option in options.genders" :key="option.value" v-model="form.gender" name="gender" :value="option.value" :label="option.label" />
                    </div>

                    <div v-else-if="step === 2" class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <MiInput v-model="form.waist_cm" type="number" name="waist_cm" :label="`${t('quiz.waist')} (${t('quiz.cm')})`" :hint="t('quiz.waistHint')" :error="form.errors.waist_cm" required autofocus />
                        <MiInput v-model="form.height_cm" type="number" name="height_cm" :label="`${t('quiz.height')} (${t('quiz.cm')})`" :hint="t('quiz.heightHint')" :error="form.errors.height_cm" required />
                    </div>

                    <div v-else-if="step === 3" class="mt-6">
                        <p class="text-[15px] text-mi-charbon/85">{{ t('quiz.hipsHint') }}</p>
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <MiRadioCard v-for="option in options.hips" :key="option.value" v-model="form.hips" name="hips" :value="option.value" :label="option.label" />
                        </div>
                    </div>

                    <div v-else class="mt-6">
                        <p class="text-[15px] text-mi-charbon/85">{{ t('quiz.fitHint') }}</p>
                        <div class="mt-4 grid grid-cols-1 gap-3">
                            <MiRadioCard v-for="option in options.fits" :key="option.value" v-model="form.fit" name="fit" :value="option.value" :label="option.label" :description="option.description" />
                        </div>
                        <p v-if="form.errors.gender || form.errors.hips || form.errors.fit" class="mt-3 text-small text-mi-erreur" role="alert">
                            {{ form.errors.gender ?? form.errors.hips ?? form.errors.fit }}
                        </p>
                    </div>

                    <div class="mt-8 flex items-center justify-between gap-4">
                        <MiButton v-if="step > 1" variant="ghost" @click="back">{{ t('quiz.back') }}</MiButton>
                        <span v-else></span>
                        <MiButton v-if="step < TOTAL_STEPS" variant="primary" :disabled="!canContinue" arrow @click="next">{{ t('quiz.next') }}</MiButton>
                        <MiButton v-else type="submit" variant="primary" :disabled="!canContinue" :loading="form.processing" arrow>
                            {{ form.processing ? t('quiz.submitting') : t('quiz.submit') }}
                        </MiButton>
                    </div>
                </div>

                <p class="mt-5 text-small text-mi-fil">
                    <Link :href="route('collections.new')" class="mi-link">{{ t('home.ctaNew') }}</Link>
                </p>
            </form>
        </section>
    </StorefrontLayout>
</template>
